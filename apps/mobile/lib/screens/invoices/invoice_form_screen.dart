import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../core/routes/app_routes.dart';
import '../../core/utils/formatters.dart';
import '../../core/utils/validators.dart';
import '../../models/invoice.dart';
import '../../services/invoice_service.dart';
import '../../widgets/error_state.dart';

const validUfs = [
  'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT',
  'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO',
  'RR', 'SC', 'SP', 'SE', 'TO',
];

class InvoiceFormScreen extends StatefulWidget {
  final InvoiceService service;
  final int? id;

  const InvoiceFormScreen({super.key, required this.service, this.id});

  @override
  State<InvoiceFormScreen> createState() => _InvoiceFormScreenState();
}

class _InvoiceFormScreenState extends State<InvoiceFormScreen> {
  final form = GlobalKey<FormState>();
  final supplier = TextEditingController();
  final number = TextEditingController();
  final city = TextEditingController();
  String state = 'PR';
  final List<_ItemDraft> items = [];
  DateTime date = DateTime.now();
  bool deductIcms = false;
  bool addIpiToTotal = true;
  bool saving = false;
  bool loading = false;
  String? error;

  @override
  void initState() {
    super.initState();
    items.add(_ItemDraft());
    if (widget.id != null) load();
  }

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final invoice = await widget.service.find(widget.id!);
      supplier.text = invoice.supplier;
      number.text = invoice.number;
      city.text = invoice.city;
      state = validUfs.contains(invoice.state) ? invoice.state : 'PR';
      date = invoice.issueDate;
      deductIcms = invoice.deductIcms;
      addIpiToTotal = invoice.addIpiToTotal;
      for (final item in items) { item.dispose(); }
      items.clear();
      items.addAll(invoice.items.map(_ItemDraft.fromInvoiceItem));
      if (items.isEmpty) items.add(_ItemDraft());
      if (mounted) setState(() {});
    } catch (exception) {
      if (mounted) setState(() => error = exception.toString());
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  void dispose() {
    supplier.dispose();
    number.dispose();
    city.dispose();
    for (final item in items) { item.dispose(); }
    super.dispose();
  }

  Widget input(TextEditingController controller, String label,
      {bool numeric = false, String? Function(String?)? validate}) =>
      Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          keyboardType: numeric
              ? const TextInputType.numberWithOptions(decimal: true)
              : TextInputType.text,
          decoration: const InputDecoration(border: OutlineInputBorder()),
          onChanged: (_) => setState(() {}),
          validator: validate ?? Validators.required,
        ),
      ]);

  double val(TextEditingController controller) =>
      double.tryParse(controller.text.replaceAll(',', '.')) ?? 0;

  InvoiceItem item(_ItemDraft draft) => InvoiceItem(
        sku: draft.sku.text.trim(),
        description: draft.description.text.trim(),
        cfop: draft.cfop.text.trim(),
        quantity: val(draft.quantity),
        unitPrice: val(draft.unitPrice),
        icmsBase: val(draft.icmsBase),
        icmsRate: val(draft.icmsRate),
        ipiRate: val(draft.ipiRate),
      );

  Future<void> save() async {
    if (!form.currentState!.validate()) return;
    final mapped = items.map(item).toList();
    if (mapped.any((entry) => entry.icmsRate > 100 || entry.ipiRate > 100)) {
      setState(() => error = 'As alíquotas ICMS e IPI devem estar entre 0 e 100%.');
      return;
    }
    if (mapped.any((entry) =>
        entry.netWith(deductIcms: deductIcms, addIpiToTotal: addIpiToTotal) < 0)) {
      setState(() => error = 'O total líquido de um item não pode ser negativo.');
      return;
    }
    final invoice = Invoice.fromItems(
      id: widget.id,
      date: date,
      supplier: supplier.text.trim(),
      number: number.text.trim(),
      city: city.text.trim(),
      state: state,
      items: mapped,
      deductIcms: deductIcms,
      addIpiToTotal: addIpiToTotal,
    );
    setState(() { saving = true; error = null; });
    try {
      if (widget.id == null) {
        await widget.service.create(invoice);
      } else {
        await widget.service.update(widget.id!, invoice);
      }
      if (mounted) context.go(AppRoutes.invoices);
    } catch (exception) {
      if (mounted) setState(() => error = exception.toString());
    } finally {
      if (mounted) setState(() => saving = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          title: Text(widget.id == null ? 'Nova nota' : 'Editar nota'),
          leading: IconButton(
            icon: const Icon(Icons.arrow_back),
            onPressed: () => context.go(AppRoutes.invoices),
          ),
        ),
        body: loading
            ? const Center(child: CircularProgressIndicator())
            : error != null && widget.id != null && items.isEmpty
                ? ErrorState(message: error!, onRetry: load)
                : Form(
                    key: form,
                    child: ListView(
                      padding: const EdgeInsets.all(16),
                      children: [
                        OutlinedButton.icon(
                          onPressed: () async {
                            final picked = await showDatePicker(
                              context: context,
                              initialDate: date,
                              firstDate: DateTime(2000),
                              lastDate: DateTime(2100),
                            );
                            if (picked != null) setState(() => date = picked);
                          },
                          icon: const Icon(Icons.event),
                          label: Text('Data de emissão: ${Formatters.date(date)}'),
                        ),
                        const SizedBox(height: 12),
                        input(supplier, 'Fornecedor *'),
                        const SizedBox(height: 12),
                        input(number, 'Número da nota *'),
                        const SizedBox(height: 12),
                        input(city, 'Cidade *'),
                        const SizedBox(height: 12),
                        const Text('Estado (UF) *', style: TextStyle(fontWeight: FontWeight.w600)),
                        const SizedBox(height: 6),
                        DropdownButtonFormField<String>(
                          value: state,
                          decoration: const InputDecoration(border: OutlineInputBorder()),
                          items: validUfs.map((uf) => DropdownMenuItem(value: uf, child: Text(uf))).toList(),
                          onChanged: (value) { if (value != null) setState(() => state = value); },
                        ),
                        const SizedBox(height: 16),
                        Text('Produtos', style: Theme.of(context).textTheme.titleLarge),
                        ...items.asMap().entries.map((entry) {
                          final index = entry.key;
                          final draft = entry.value;
                          final values = item(draft);
                          return Card(
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Column(children: [
                                Row(children: [
                                  Expanded(child: Text('Produto ${index + 1}')),
                                  IconButton(
                                    onPressed: items.length <= 1 ? null : () {
                                      setState(() => items.removeAt(index));
                                      draft.dispose();
                                    },
                                    icon: const Icon(Icons.delete_outline),
                                  ),
                                ]),
                                input(draft.sku, 'Código/SKU *'),
                                const SizedBox(height: 8),
                                input(draft.description, 'Descrição *'),
                                const SizedBox(height: 8),
                                input(draft.cfop, 'CFOP *', validate: (value) =>
                                    RegExp(r'^\\d{4}$').hasMatch(value ?? '')
                                        ? null : 'CFOP com 4 dígitos'),
                                const SizedBox(height: 8),
                                input(draft.quantity, 'Quantidade *', numeric: true, validate: Validators.positive),
                                const SizedBox(height: 8),
                                input(draft.unitPrice, 'Valor unitário *', numeric: true, validate: Validators.positive),
                                const SizedBox(height: 8),
                                input(draft.icmsBase, 'Base ICMS *', numeric: true, validate: Validators.nonnegative),
                                const SizedBox(height: 8),
                                input(draft.icmsRate, 'Alíquota ICMS (%) *', numeric: true, validate: Validators.nonnegative),
                                const SizedBox(height: 8),
                                input(draft.ipiRate, 'Alíquota IPI (%) *', numeric: true, validate: Validators.nonnegative),
                                const SizedBox(height: 8),
                                Text('Bruto: ${Formatters.money(values.gross)}'),
                                Text('ICMS: ${Formatters.money(values.icms)}'),
                                Text('IPI: ${Formatters.money(values.ipi)}'),
                                Text('Líquido: ${Formatters.money(values.netWith(deductIcms: deductIcms, addIpiToTotal: addIpiToTotal))}'),
                              ]),
                            ),
                          );
                        }),
                        OutlinedButton.icon(
                          onPressed: () => setState(() => items.add(_ItemDraft())),
                          icon: const Icon(Icons.add),
                          label: const Text('Adicionar produto'),
                        ),
                        const SizedBox(height: 16),
                        Text('Composição do total líquido', style: Theme.of(context).textTheme.titleMedium),
                        SwitchListTile(
                          title: const Text('Deduzir ICMS do líquido'),
                          subtitle: const Text('Desativado por padrão nas entradas.'),
                          value: deductIcms,
                          onChanged: (value) => setState(() => deductIcms = value),
                        ),
                        SwitchListTile(
                          title: const Text('Somar IPI por fora'),
                          subtitle: const Text('Desative caso o bruto já inclua o IPI.'),
                          value: addIpiToTotal,
                          onChanged: (value) => setState(() => addIpiToTotal = value),
                        ),
                        Builder(builder: (context) {
                          final invoice = Invoice.fromItems(
                            date: date,
                            supplier: supplier.text,
                            number: number.text,
                            city: city.text,
                            state: state,
                            items: items.map(item).toList(),
                            deductIcms: deductIcms,
                            addIpiToTotal: addIpiToTotal,
                          );
                          return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            Text('BC ICMS: ${Formatters.money(invoice.icmsBase)}'),
                            Text('ICMS: ${Formatters.money(invoice.icmsTotal)}'),
                            Text('IPI: ${Formatters.money(invoice.ipiTotal)}'),
                            Text('Total bruto: ${Formatters.money(invoice.grossTotal)}'),
                            Text('Total líquido: ${Formatters.money(invoice.netTotal)}'),
                          ]);
                        }),
                        if (error != null) Text(error!, style: const TextStyle(color: Colors.red)),
                        const SizedBox(height: 20),
                        FilledButton(
                          onPressed: saving ? null : save,
                          child: saving
                              ? const CircularProgressIndicator()
                              : const Text('Gravar nota'),
                        ),
                      ],
                    ),
                  ),
      );
}

class _ItemDraft {
  final sku = TextEditingController();
  final description = TextEditingController();
  final cfop = TextEditingController(text: '1102');
  final quantity = TextEditingController(text: '1');
  final unitPrice = TextEditingController(text: '0.00');
  final icmsBase = TextEditingController(text: '0.00');
  final icmsRate = TextEditingController(text: '0');
  final ipiRate = TextEditingController(text: '0');

  _ItemDraft();

  factory _ItemDraft.fromInvoiceItem(InvoiceItem item) {
    final draft = _ItemDraft();
    draft.sku.text = item.sku;
    draft.description.text = item.description;
    draft.cfop.text = item.cfop;
    draft.quantity.text = item.quantity.toString();
    draft.unitPrice.text = item.unitPrice.toStringAsFixed(2);
    draft.icmsBase.text = item.icmsBase.toStringAsFixed(2);
    draft.icmsRate.text = item.icmsRate.toString();
    draft.ipiRate.text = item.ipiRate.toString();
    return draft;
  }

  void dispose() {
    for (final controller in [sku, description, cfop, quantity, unitPrice, icmsBase, icmsRate, ipiRate]) {
      controller.dispose();
    }
  }
}
