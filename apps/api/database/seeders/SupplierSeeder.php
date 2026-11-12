<?php
namespace Database\Seeders;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
class SupplierSeeder extends Seeder {public function run():void { $user=User::first();if($user) Supplier::firstOrCreate(['user_id'=>$user->id,'name'=>'Fornecedor Exemplo Ltda'],['city'=>'Guarapuava','state'=>'PR']);}}
