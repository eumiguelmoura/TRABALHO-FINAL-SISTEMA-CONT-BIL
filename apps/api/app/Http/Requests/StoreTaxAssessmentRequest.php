<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreTaxAssessmentRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return [
        'name'=>['required','string','max:120'],
        'invoice_ids'=>['required','array','min:1','max:500'],
        'invoice_ids.*'=>['required','integer','distinct','min:1'],
    ]; }
}
