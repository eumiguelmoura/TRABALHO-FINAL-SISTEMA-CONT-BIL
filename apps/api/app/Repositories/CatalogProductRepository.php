<?php

namespace App\Repositories;

use App\Models\CatalogProduct;

class CatalogProductRepository {
    public function allForUser(int $userId) {
        return CatalogProduct::query()->where('user_id',$userId)->orderBy('description')->get();
    }
    public function findForUser(int $userId,int $id): CatalogProduct {
        return CatalogProduct::query()->where('user_id',$userId)->findOrFail($id);
    }
}
