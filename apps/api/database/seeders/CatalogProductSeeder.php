<?php

namespace Database\Seeders;

use App\Models\CatalogProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class CatalogProductSeeder extends Seeder {
    public function run(): void {
        $user=User::query()->first();
        if(!$user)return;
        CatalogProduct::firstOrCreate(['user_id'=>$user->id,'sku'=>'EXEMPLO-01'],[
            'description'=>'Produto exemplo para testes','cfop'=>'1102','unit_price'=>'10.00',
            'icms_rate'=>'12.0000','ipi_rate'=>'5.0000',
        ]);
    }
}
