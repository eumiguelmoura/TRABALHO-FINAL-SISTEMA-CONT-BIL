<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaxAssessmentRequest;
use App\Services\TaxAssessmentService;
use Illuminate\Http\Request;
class TaxAssessmentController extends Controller {
    public function __construct(private TaxAssessmentService $service) {}
    public function index(Request $request){return response()->json($this->service->list($request->user()->id));}
    public function show(Request $request,int $id){return response()->json($this->service->find($request->user()->id,$id));}
    public function store(StoreTaxAssessmentRequest $request){return response()->json($this->service->create($request->user()->id,$request->validated()),201);}
    public function destroy(Request $request,int $id){$this->service->delete($request->user()->id,$id);return response()->noContent();}

}
