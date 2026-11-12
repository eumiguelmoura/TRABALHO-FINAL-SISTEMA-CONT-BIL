<?php
namespace App\Repositories;
use App\Models\TaxAssessment;
use Illuminate\Database\Eloquent\Collection;
class TaxAssessmentRepository {
    public function listForUser(int $userId): Collection {
        return TaxAssessment::query()->where('user_id',$userId)->orderByDesc('id')->get();
    }
    public function findForUser(int $userId,int $id): TaxAssessment {
        return TaxAssessment::query()->where('user_id',$userId)->with('invoices:id,issue_date,supplier,number,icms_total,ipi_total,gross_total')->findOrFail($id);
    }
}
