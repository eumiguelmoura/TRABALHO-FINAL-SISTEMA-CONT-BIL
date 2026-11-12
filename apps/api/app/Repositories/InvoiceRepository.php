<?php
namespace App\Repositories;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
class InvoiceRepository {
    public function listForUser(int $userId, bool $pendingOnly=false): Collection {
        return Invoice::query()->where('user_id',$userId)->with('items')->withCount('assessments')
            ->when($pendingOnly,fn($q)=>$q->whereDoesntHave('assessments'))
            ->orderByDesc('issue_date')->orderByDesc('id')->get();
    }
    public function findForUser(int $userId, int $id): Invoice {
        return Invoice::query()->where('user_id',$userId)->with(['items','assessments:id,name'])->findOrFail($id);
    }
}
