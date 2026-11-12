class InvoiceItem {
  final int? id;
  final String sku, description, cfop;
  final double quantity, unitPrice, icmsBase, icmsRate, ipiRate;

  InvoiceItem({
    this.id,
    required this.sku,
    required this.description,
    required this.cfop,
    required this.quantity,
    required this.unitPrice,
    required this.icmsBase,
    required this.icmsRate,
    required this.ipiRate,
  });

  factory InvoiceItem.fromJson(Map<String, dynamic> json) => InvoiceItem(
        id: (json['id'] as num?)?.toInt(),
        sku: '${json['sku'] ?? ''}',
        description: '${json['description'] ?? ''}',
        cfop: '${json['cfop'] ?? ''}',
        quantity: _num(json['quantity']),
        unitPrice: _num(json['unit_price']),
        icmsBase: _num(json['icms_base']),
        icmsRate: _num(json['icms_rate']),
        ipiRate: _num(json['ipi_rate']),
      );

  Map<String, dynamic> toJson() => {
        'sku': sku,
        'description': description,
        'cfop': cfop,
        'quantity': quantity.toStringAsFixed(3),
        'unit_price': unitPrice.toStringAsFixed(2),
        'icms_base': icmsBase.toStringAsFixed(2),
        'icms_rate': icmsRate,
        'ipi_rate': ipiRate,
      };

  double get gross => _money(quantity * unitPrice);
  double get icms => _money(icmsBase * icmsRate / 100);
  double get ipi => _money(gross * ipiRate / 100);

  // ICMS próprio não reduz o total por padrão; IPI pode ser por fora ou já incluído.
  double netWith({bool deductIcms = false, bool addIpiToTotal = true}) =>
      _money(gross + (addIpiToTotal ? ipi : 0) - (deductIcms ? icms : 0));

  double get net => netWith();
}

class Invoice {
  final int? id;
  final DateTime issueDate;
  final String supplier, number, city, state;
  final double icmsBase, icmsTotal, ipiTotal, grossTotal, netTotal;
  final bool deductIcms, addIpiToTotal;
  final List<InvoiceItem> items;

  Invoice({
    this.id,
    required this.issueDate,
    required this.supplier,
    required this.number,
    required this.city,
    required this.state,
    required this.icmsBase,
    required this.icmsTotal,
    required this.ipiTotal,
    required this.grossTotal,
    required this.netTotal,
    required this.items,
    this.deductIcms = false,
    this.addIpiToTotal = true,
  });

  factory Invoice.fromJson(Map<String, dynamic> json) => Invoice(
        id: (json['id'] as num?)?.toInt(),
        issueDate: DateTime.parse('${json['issue_date']}'.substring(0, 10)),
        supplier: '${json['supplier'] ?? ''}',
        number: '${json['number'] ?? ''}',
        city: '${json['city'] ?? ''}',
        state: '${json['state'] ?? ''}',
        icmsBase: _num(json['icms_base']),
        icmsTotal: _num(json['icms_total']),
        ipiTotal: _num(json['ipi_total']),
        grossTotal: _num(json['gross_total']),
        netTotal: _num(json['net_total']),
        deductIcms: json['deduct_icms'] == true,
        addIpiToTotal: json['add_ipi_to_total'] != false,
        items: (json['items'] as List<dynamic>? ?? [])
            .map((item) => InvoiceItem.fromJson(Map<String, dynamic>.from(item as Map)))
            .toList(),
      );

  Map<String, dynamic> toJson() => {
        'issue_date': issueDate.toIso8601String().substring(0, 10),
        'supplier': supplier,
        'number': number,
        'city': city,
        'state': state.toUpperCase(),
        'deduct_icms': deductIcms,
        'add_ipi_to_total': addIpiToTotal,
        'icms_base': icmsBase.toStringAsFixed(2),
        'icms_total': icmsTotal.toStringAsFixed(2),
        'ipi_total': ipiTotal.toStringAsFixed(2),
        'gross_total': grossTotal.toStringAsFixed(2),
        'net_total': netTotal.toStringAsFixed(2),
        'items': items.map((item) => item.toJson()).toList(),
      };

  static Invoice fromItems({
    int? id,
    required DateTime date,
    required String supplier,
    required String number,
    required String city,
    required String state,
    required List<InvoiceItem> items,
    bool deductIcms = false,
    bool addIpiToTotal = true,
  }) =>
      Invoice(
        id: id,
        issueDate: date,
        supplier: supplier,
        number: number,
        city: city,
        state: state,
        items: items,
        deductIcms: deductIcms,
        addIpiToTotal: addIpiToTotal,
        icmsBase: _money(items.fold(0.0, (sum, item) => sum + item.icmsBase)),
        icmsTotal: _money(items.fold(0.0, (sum, item) => sum + item.icms)),
        ipiTotal: _money(items.fold(0.0, (sum, item) => sum + item.ipi)),
        grossTotal: _money(items.fold(0.0, (sum, item) => sum + item.gross)),
        netTotal: _money(items.fold(
          0.0,
          (sum, item) => sum + item.netWith(
            deductIcms: deductIcms,
            addIpiToTotal: addIpiToTotal,
          ),
        )),
      );
}

double _num(dynamic value) =>
    value is num ? value.toDouble() : double.tryParse('$value') ?? 0;

double _money(double value) => (value * 100 + 1e-8).round() / 100;
