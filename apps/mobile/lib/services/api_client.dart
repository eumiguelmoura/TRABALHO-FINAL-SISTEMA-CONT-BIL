import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
class ApiException implements Exception {final String message;ApiException(this.message);@override String toString()=>message;}
class ApiClient {
 // Android Emulator: --dart-define=API_BASE_URL=http://10.0.2.2:8182/api
 static const baseUrl=String.fromEnvironment('API_BASE_URL',defaultValue:'http://10.0.2.2:8182/api');
 static const _storage=FlutterSecureStorage();
 Future<String?> get token=>_storage.read(key:'api_token');
 Future<void> setToken(String token)=>_storage.write(key:'api_token',value:token);
 Future<void> clearToken()=>_storage.delete(key:'api_token');
 Future<dynamic> send(String method,String path,{Object? body}) async {
   final t=await token;final uri=Uri.parse('$baseUrl/$path');
   try{
     final request=http.Request(method,uri);
     request.headers.addAll({'Accept':'application/json','Content-Type':'application/json',if(t!=null)'Authorization':'Bearer $t'});
     if(body!=null)request.body=jsonEncode(body);
     final client=http.Client();
     final result=await client.send(request).timeout(const Duration(seconds:20));
     final raw=await result.stream.bytesToString();final data=raw.isEmpty?null:jsonDecode(raw);
     if(result.statusCode<200||result.statusCode>=300){
       final errors=data is Map?data['errors']:null;
       String? detail;
       if(errors is Map&&errors.isNotEmpty){final first=errors.values.first;detail=first is List&&first.isNotEmpty?'${first.first}':'$first';}
       throw ApiException(detail??(data is Map?'${data['message']??'Erro HTTP ${result.statusCode}'}':'Erro HTTP ${result.statusCode}'));
     }
     return data;
   }on ApiException{rethrow;}catch(e){throw ApiException('Falha de rede/API: $e');}
 }
}
