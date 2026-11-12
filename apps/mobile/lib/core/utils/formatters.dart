import 'package:intl/intl.dart';
class Formatters {static String money(num value)=>NumberFormat.currency(locale:'pt_BR',symbol:'R\$').format(value);static String date(DateTime date)=>DateFormat('dd/MM/yyyy').format(date);}
