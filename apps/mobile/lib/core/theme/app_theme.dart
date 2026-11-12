import 'package:flutter/material.dart';
import 'app_colors.dart';
class AppTheme {static ThemeData get light=>ThemeData(useMaterial3:true,colorScheme:ColorScheme.fromSeed(seedColor:AppColors.primary),scaffoldBackgroundColor:AppColors.background,inputDecorationTheme:const InputDecorationTheme(border:OutlineInputBorder(),contentPadding:EdgeInsets.symmetric(horizontal:12,vertical:12)));}
