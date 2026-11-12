<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxAssessment extends Model {
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['user_id','name','total_icms','total_ipi','invoice_count'];
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function invoices(): BelongsToMany { return $this->belongsToMany(Invoice::class, 'assessment_invoice'); }
}
