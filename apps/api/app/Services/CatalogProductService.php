<?php

namespace App\Services;

use App\Models\CatalogProduct;
use App\Repositories\CatalogProductRepository;
use Illuminate\Validation\ValidationException;

class CatalogProductService {
    public function __construct(private CatalogProductRepository $repository, private MoneyCalculator $money) {}
    public function list(int $userId) {return $this->repository->allForUser($userId);}
    public function find(int $userId,int $id) {return $this->repository->findForUser($userId,$id);}
    private function normalize(array $data): array {
        $data['unit_price']=$this->money->money($this->money->parse($data['unit_price']));
        $data['icms_rate']=$this->money->decimal($this->money->parse($data['icms_rate'],4),4);
        $data['ipi_rate']=$this->money->decimal($this->money->parse($data['ipi_rate'],4),4);
        return $data;
    }
    public function create(int $userId,array $data): CatalogProduct {
        if(CatalogProduct::query()->where('user_id',$userId)->where('sku',$data['sku'])->exists()) {
            throw ValidationException::withMessages(['sku'=>'Já existe um produto com esse SKU.']);
        }
        return CatalogProduct::create(['user_id'=>$userId,...$this->normalize($data)]);
    }
    public function update(int $userId,int $id,array $data): CatalogProduct {
        $product=$this->find($userId,$id);
        if(CatalogProduct::query()->where('user_id',$userId)->where('sku',$data['sku'])->whereKeyNot($id)->exists()) {
            throw ValidationException::withMessages(['sku'=>'Já existe um produto com esse SKU.']);
        }
        $product->update($this->normalize($data));
        return $product;
    }
    public function delete(int $userId,int $id): void {$this->find($userId,$id)->delete();}
}
