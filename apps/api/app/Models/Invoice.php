<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model {
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['user_id','issue_date','supplier','number','city','state','icms_base','icms_total','ipi_total','gross_total','discount_total','net_total','deduct_icms','add_ipi_to_total'];
    protected function casts(): array { return ['issue_date'=>'date:Y-m-d','deduct_icms'=>'boolean','add_ipi_to_total'=>'boolean']; }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function assessments(): BelongsToMany { return $this->belongsToMany(TaxAssessment::class, 'assessment_invoice'); }
}
