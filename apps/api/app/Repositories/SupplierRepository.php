<?php
namespace App\Repositories;
use App\Models\Supplier;
class SupplierRepository {
 public function all(int $userId){return Supplier::where('user_id',$userId)->orderBy('name')->get();}
 public function find(int $userId,int $id):Supplier{return Supplier::where('user_id',$userId)->findOrFail($id);}
}
