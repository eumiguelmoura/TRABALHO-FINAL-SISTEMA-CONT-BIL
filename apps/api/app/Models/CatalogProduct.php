<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogProduct extends Model {
    use HasFactory;
    protected $fillable=['user_id','sku','description','cfop','unit_price','icms_rate','ipi_rate'];
    public function owner(): BelongsTo {return $this->belongsTo(User::class,'user_id');}
}
