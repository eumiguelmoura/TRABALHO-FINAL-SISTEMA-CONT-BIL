<?php
namespace App\Services;
use App\Models\Invoice;
use App\Models\TaxAssessment;
use App\Repositories\TaxAssessmentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class TaxAssessmentService {
    public function __construct(private TaxAssessmentRepository $repository, private MoneyCalculator $money) {}
    public function list(int $userId) { return $this->repository->listForUser($userId); }
    public function find(int $userId,int $id) { return $this->repository->findForUser($userId,$id); }
    public function create(int $userId,array $data): TaxAssessment {
        return DB::transaction(function()use($userId,$data) {
            // Bloqueia linhas antes de examinar a relação com apurações, reduzindo condições de corrida.
            $invoices=Invoice::query()->where('user_id',$userId)->whereIn('id',$data['invoice_ids'])->lockForUpdate()->get();
            if($invoices->count() !== count($data['invoice_ids'])) {
                throw ValidationException::withMessages(['invoice_ids'=>'Uma ou mais notas não foram encontradas para o usuário.']);
            }
            if(DB::table('assessment_invoice')->whereIn('invoice_id',$data['invoice_ids'])->exists()) {
                throw ValidationException::withMessages(['invoice_ids'=>'Há notas já apuradas. Retire essas notas antes de continuar.']);
            }
            $icms=0;$ipi=0;
            foreach($invoices as $invoice){$icms+=$this->money->parse($invoice->icms_total);$ipi+=$this->money->parse($invoice->ipi_total);}
            $assessment=TaxAssessment::create([
                'user_id'=>$userId,'name'=>$data['name'],'total_icms'=>$this->money->money($icms),
                'total_ipi'=>$this->money->money($ipi),'invoice_count'=>$invoices->count()
            ]);
            $assessment->invoices()->attach($data['invoice_ids']);
            return $assessment->load('invoices');
        });
    }
    public function delete(int $userId,int $id): void {
        DB::transaction(function()use($userId,$id){
            $assessment=TaxAssessment::query()->where('user_id',$userId)->lockForUpdate()->findOrFail($id);
            $assessment->invoices()->detach();$assessment->delete();
        });
    }

}
