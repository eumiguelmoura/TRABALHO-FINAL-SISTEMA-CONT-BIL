<?php
namespace App\Services;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class InvoiceService {
    public function __construct(private InvoiceRepository $repository,private MoneyCalculator $math) {}
    public function list(int $userId,bool $pendingOnly=false) {return $this->repository->listForUser($userId,$pendingOnly);}
    public function find(int $userId,int $id): Invoice {return $this->repository->findForUser($userId,$id);}
    public function create(int $userId,array $data): Invoice {
        $calculated=$this->math->calculate($data['items'], (bool)($data['deduct_icms'] ?? false), (bool)($data['add_ipi_to_total'] ?? true));
        foreach($calculated['totals'] as $field=>$expected) {
            if ($this->math->parse($data[$field]) !== $this->math->parse($expected)) {
                throw ValidationException::withMessages([$field=>"Total divergente: informado {$data[$field]}, calculado {$expected} pelos itens."]);
            }
        }
        if (Invoice::query()->where('user_id',$userId)->where('supplier',$data['supplier'])->where('number',$data['number'])->exists()) {
            throw ValidationException::withMessages(['number'=>'Esta nota já está cadastrada para esse fornecedor.']);
        }
        return DB::transaction(function()use($userId,$data,$calculated) {
            $header=collect($data)->only(['issue_date','supplier','number','city','state','icms_base','icms_total','ipi_total','gross_total','net_total','deduct_icms','add_ipi_to_total'])->all();
            $header['state']=strtoupper($header['state']);
            $header['deduct_icms']=(bool)($data['deduct_icms'] ?? false);
            $header['add_ipi_to_total']=(bool)($data['add_ipi_to_total'] ?? true);
            $header['user_id']=$userId;
            $invoice=Invoice::create($header);
            $invoice->items()->createMany($calculated['items']);
            return $invoice->load('items');
        });
    }
    public function update(int $userId,int $id,array $data): Invoice {
        return DB::transaction(function()use($userId,$id,$data){
            $invoice=Invoice::query()->where('user_id',$userId)->lockForUpdate()->findOrFail($id);
            if ($invoice->assessments()->exists()) throw ValidationException::withMessages(['invoice'=>'Nota já apurada: não é permitido alterar.']);
            $calculated=$this->math->calculate($data['items'], (bool)($data['deduct_icms'] ?? false), (bool)($data['add_ipi_to_total'] ?? true));
            foreach($calculated['totals'] as $field=>$expected){
                if($this->math->parse($data[$field])!==$this->math->parse($expected)) throw ValidationException::withMessages([$field=>'Total divergente: calculado '.$expected]);
            }
            if(Invoice::query()->where('user_id',$userId)->where('supplier',$data['supplier'])->where('number',$data['number'])->whereKeyNot($id)->exists()) throw ValidationException::withMessages(['number'=>'Nota duplicada.']);
            $header=collect($data)->only(['issue_date','supplier','number','city','state','icms_base','icms_total','ipi_total','gross_total','net_total'])->all();
            $header['state']=strtoupper($header['state']);
            $header['deduct_icms']=(bool)($data['deduct_icms'] ?? false);
            $header['add_ipi_to_total']=(bool)($data['add_ipi_to_total'] ?? true);
            $invoice->update($header);
            $invoice->items()->delete();$invoice->items()->createMany($calculated['items']);
            return $invoice->load('items');
        });
    }
    public function delete(int $userId,int $id): void {
        DB::transaction(function()use($userId,$id){
            $invoice=Invoice::query()->where('user_id',$userId)->lockForUpdate()->findOrFail($id);
            if($invoice->assessments()->exists()) throw ValidationException::withMessages(['invoice'=>'Nota já apurada: exclusão bloqueada.']);
            $invoice->delete();
        });
    }

}
