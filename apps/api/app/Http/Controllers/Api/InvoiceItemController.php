<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
class InvoiceItemController extends Controller {
 public function index(Request $r){return response()->json(InvoiceItem::whereHas('invoice',fn($q)=>$q->where('user_id',$r->user()->id))->orderByDesc('id')->get());}
 public function show(Request $r,int $id){return response()->json(InvoiceItem::whereHas('invoice',fn($q)=>$q->where('user_id',$r->user()->id))->findOrFail($id));}
}
