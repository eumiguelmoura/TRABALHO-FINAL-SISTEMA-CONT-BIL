import 'package:flutter_test/flutter_test.dart';
import 'package:sistema_contabil/models/invoice.dart';

void main() {
  test('ICMS apenas destacado, IPI por fora e opções de total', () {
    final item = InvoiceItem(
      sku: 'A', description: 'Produto', cfop: '1102', quantity: 3,
      unitPrice: 10, icmsBase: 30, icmsRate: 12, ipiRate: 5,
    );
    expect(item.gross, 30);
    expect(item.icms, 3.60);
    expect(item.ipi, 1.50);
    final invoice = Invoice.fromItems(
      date: DateTime(2026, 11, 12), supplier: 'Empresa',
      number: '1', city: 'Guarapuava', state: 'PR', items: [item],
    );
    expect(invoice.netTotal, 31.50);
    expect(invoice.toJson()['deduct_icms'], false);
    expect(invoice.toJson()['items'], isNotEmpty);
    final withoutIpi = Invoice.fromItems(
      date: DateTime(2026, 11, 12), supplier: 'Empresa',
      number: '1', city: 'Guarapuava', state: 'PR', items: [item],
      addIpiToTotal: false,
    );
    expect(withoutIpi.netTotal, 30);
    expect(item.netWith(deductIcms: true, addIpiToTotal: true), 27.90);
  });
}
