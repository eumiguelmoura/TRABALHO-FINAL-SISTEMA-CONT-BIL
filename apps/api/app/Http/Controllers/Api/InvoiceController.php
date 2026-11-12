<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
class InvoiceController extends Controller {
    public function __construct(private InvoiceService $service) {}
    public function index(Request $request) {return response()->json($this->service->list($request->user()->id,$request->boolean('pending_only')));}
    public function show(Request $request,int $id) {return response()->json($this->service->find($request->user()->id,$id));}
    public function store(StoreInvoiceRequest $request) {
        return response()->json($this->service->create($request->user()->id,$request->validated()),201);
    }
    public function update(StoreInvoiceRequest $request,int $id){return response()->json($this->service->update($request->user()->id,$id,$request->validated()));}
    public function destroy(Request $request,int $id){$this->service->delete($request->user()->id,$id);return response()->noContent();}

}
