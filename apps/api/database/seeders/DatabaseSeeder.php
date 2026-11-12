<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        $email=env('SEED_ADMIN_EMAIL');
        $password=env('SEED_ADMIN_PASSWORD');
        if(!$email || !$password || strlen($password)<12 || $password==='DefinaUmaSenhaForteAntesDoSeed') {
            throw new \RuntimeException('Configure SEED_ADMIN_EMAIL e SEED_ADMIN_PASSWORD (mín. 12 caracteres) no .env antes do db:seed.');
        }
        User::updateOrCreate(['email'=>$email],['name'=>'Administrador Contábil','password'=>$password]);
    }
}
