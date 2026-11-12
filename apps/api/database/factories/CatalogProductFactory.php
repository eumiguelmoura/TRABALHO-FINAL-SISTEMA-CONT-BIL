<?php

namespace Database\Factories;

use App\Models\CatalogProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CatalogProductFactory extends Factory {
    protected $model=CatalogProduct::class;
    public function definition(): array {
        return ['user_id'=>User::factory(), 'sku'=>fake()->unique()->bothify('SKU-####'),
            'description'=>fake()->words(3,true), 'cfop'=>'1102','unit_price'=>fake()->randomFloat(2,10,1000),
            'icms_rate'=>'12.0000','ipi_rate'=>'5.0000'];
    }
}
