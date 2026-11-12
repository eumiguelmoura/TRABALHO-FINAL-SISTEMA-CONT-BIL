<?php
namespace App\Services;
use App\Models\Supplier;
use App\Repositories\SupplierRepository;
class SupplierService {
 public function __construct(private SupplierRepository $repository){}
 public function index(int $id){return $this->repository->all($id);}
 public function show(int $userId,int $id){return $this->repository->find($userId,$id);}
 public function create(int $userId,array $data){return Supplier::create(['user_id'=>$userId,...$data]);}
 public function update(int $userId,int $id,array $data){$supplier=$this->show($userId,$id);$supplier->update($data);return $supplier;}
 public function delete(int $userId,int $id){$this->show($userId,$id)->delete();}
}
