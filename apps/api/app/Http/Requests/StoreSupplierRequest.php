<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreSupplierRequest extends FormRequest {
 public function authorize():bool{return true;}
 public function rules():array{return ['name'=>['required','string','max:180'],'city'=>['required','string','max:100'],'state'=>['required','regex:/^[A-Za-z]{2}$/'],'document'=>['nullable','string','max:20']];}
}
