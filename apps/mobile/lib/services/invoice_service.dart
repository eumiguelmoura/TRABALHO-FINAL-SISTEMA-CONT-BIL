import '../models/invoice.dart';import 'api_client.dart';
class InvoiceService {final ApiClient api;InvoiceService(this.api);
 Future<List<Invoice>> list({bool pendingOnly=false}) async {final data=await api.send('GET','invoices${pendingOnly?'?pending_only=1':''}');return (data as List).map((x)=>Invoice.fromJson(Map<String,dynamic>.from(x as Map))).toList();}
 Future<Invoice> find(int id)async=>Invoice.fromJson(Map<String,dynamic>.from(await api.send('GET','invoices/$id') as Map));
 Future<Invoice> create(Invoice value)async=>Invoice.fromJson(Map<String,dynamic>.from(await api.send('POST','invoices',body:value.toJson()) as Map));
 Future<Invoice> update(int id,Invoice value)async=>Invoice.fromJson(Map<String,dynamic>.from(await api.send('PUT','invoices/$id',body:value.toJson()) as Map));
 Future<void> delete(int id)async{await api.send('DELETE','invoices/$id');}
}
