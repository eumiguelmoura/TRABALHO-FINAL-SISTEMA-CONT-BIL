<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreInvoiceRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return [
        'issue_date'=>['required','date_format:Y-m-d'],
        'supplier'=>['required','string','max:180'],
        'number'=>['required','string','max:30'],
        'city'=>['required','string','max:100'],
        'state'=>['required','string',Rule::in(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'])],
        'icms_base'=>['required','numeric','min:0'],
        'icms_total'=>['required','numeric','min:0'],
        'ipi_total'=>['required','numeric','min:0'],
        'gross_total'=>['required','numeric','min:0.01'],
        'net_total'=>['required','numeric','min:0'],
        'deduct_icms'=>['sometimes','boolean'],
        'add_ipi_to_total'=>['sometimes','boolean'],
        'items'=>['required','array','min:1','max:100'],
        'items.*.sku'=>['required','string','max:70'],
        'items.*.description'=>['required','string','max:250'],
        'items.*.cfop'=>['required','regex:/^\d{4}$/'],
        'items.*.quantity'=>['required','numeric','gt:0','max:999999'],
        'items.*.unit_price'=>['required','numeric','gt:0','max:99999999'],
        'items.*.icms_base'=>['required','numeric','min:0'],
        'items.*.icms_rate'=>['required','numeric','between:0,100'],
        'items.*.ipi_rate'=>['required','numeric','between:0,100'],
    ]; }
}
