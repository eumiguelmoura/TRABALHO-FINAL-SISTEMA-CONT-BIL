<?php
return [
    'paths'=>['api/*','sanctum/csrf-cookie'],
    'allowed_methods'=>['*'],
    // Em produção, defina os hosts específicos em CORS_ALLOWED_ORIGINS.
    'allowed_origins'=>array_filter(array_map('trim',explode(',',env('CORS_ALLOWED_ORIGINS','http://localhost:9010,http://127.0.0.1:9010')))),
    'allowed_origins_patterns'=>[],
    'allowed_headers'=>['*'],
    'exposed_headers'=>[],
    'max_age'=>0,
    'supports_credentials'=>false,
];
