<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Services\SupplierService;
use Illuminate\Http\Request;
class SupplierController extends Controller {
 public function __construct(private SupplierService $service){}
 public function index(Request $r){return response()->json($this->service->index($r->user()->id));}
 public function show(Request $r,int $id){return response()->json($this->service->show($r->user()->id,$id));}
 public function store(StoreSupplierRequest $r){return response()->json($this->service->create($r->user()->id,$r->validated()),201);}
 public function update(StoreSupplierRequest $r,int $id){return response()->json($this->service->update($r->user()->id,$id,$r->validated()));}
 public function destroy(Request $r,int $id){$this->service->delete($r->user()->id,$id);return response()->noContent();}
}
