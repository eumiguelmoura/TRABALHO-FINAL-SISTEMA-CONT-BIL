import '../models/tax_assessment.dart';import 'api_client.dart';
class TaxAssessmentService {final ApiClient api;TaxAssessmentService(this.api);
 Future<List<TaxAssessment>> list()async{final data=await api.send('GET','tax-assessments');return (data as List).map((j)=>TaxAssessment.fromJson(Map<String,dynamic>.from(j as Map))).toList();}
 Future<TaxAssessment> create(String name,List<int> ids) async=>TaxAssessment.fromJson(Map<String,dynamic>.from(await api.send('POST','tax-assessments',body:{'name':name,'invoice_ids':ids}) as Map));
 Future<Map<String,dynamic>> find(int id) async=>Map<String,dynamic>.from(await api.send('GET','tax-assessments/$id') as Map);
 Future<void> delete(int id)async{await api.send('DELETE','tax-assessments/$id');}
}
