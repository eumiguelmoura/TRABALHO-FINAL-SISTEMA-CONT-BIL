<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model {
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['sku','description','cfop','quantity','unit_price','gross_total','icms_base','icms_rate','icms_amount','ipi_rate','ipi_amount','net_total'];
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
