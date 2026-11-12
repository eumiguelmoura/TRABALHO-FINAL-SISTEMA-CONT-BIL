<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCatalogProductRequest;
use App\Services\CatalogProductService;
use Illuminate\Http\Request;

class CatalogProductController extends Controller {
    public function __construct(private CatalogProductService $service) {}
    public function index(Request $request) {return response()->json($this->service->list($request->user()->id));}
    public function store(StoreCatalogProductRequest $request) {return response()->json($this->service->create($request->user()->id,$request->validated()),201);}
    public function show(Request $request,int $id) {return response()->json($this->service->find($request->user()->id,$id));}
    public function update(StoreCatalogProductRequest $request,int $id) {return response()->json($this->service->update($request->user()->id,$id,$request->validated()));}
    public function destroy(Request $request,int $id) {$this->service->delete($request->user()->id,$id);return response()->noContent();}
}
