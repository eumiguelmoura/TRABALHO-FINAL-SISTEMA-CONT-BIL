<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\TaxAssessmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogProductController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\InvoiceItemController;

Route::get('health',fn()=>response()->json(['status'=>'ok']));
Route::post('auth/login',[AuthController::class,'login'])->middleware('throttle:10,1');
Route::middleware('auth:sanctum')->group(function(){
    Route::get('auth/me',[AuthController::class,'me']);
    Route::post('auth/logout',[AuthController::class,'logout']);
    Route::get('invoices',[InvoiceController::class,'index']);
    Route::get('invoices/{id}',[InvoiceController::class,'show']);
    Route::post('invoices',[InvoiceController::class,'store']);
    Route::put('invoices/{id}',[InvoiceController::class,'update']);
    Route::delete('invoices/{id}',[InvoiceController::class,'destroy']);
    Route::apiResource('suppliers',SupplierController::class);
    Route::apiResource('catalog-products',CatalogProductController::class);
    Route::get('invoice-items',[InvoiceItemController::class,'index']);
    Route::get('invoice-items/{id}',[InvoiceItemController::class,'show']);
    Route::get('tax-assessments',[TaxAssessmentController::class,'index']);
    Route::get('tax-assessments/{id}',[TaxAssessmentController::class,'show']);
    Route::post('tax-assessments',[TaxAssessmentController::class,'store']);
    Route::delete('tax-assessments/{id}',[TaxAssessmentController::class,'destroy']);
});
