<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogProductRequest extends FormRequest {
    public function authorize(): bool {return true;}
    public function rules(): array {return [
        'sku'=>['required','string','max:70'],
        'description'=>['required','string','max:250'],
        'cfop'=>['required','regex:/^\d{4}$/'],
        'unit_price'=>['required','numeric','min:0.01','max:99999999'],
        'icms_rate'=>['required','numeric','between:0,100'],
        'ipi_rate'=>['required','numeric','between:0,100'],
    ];}
}
