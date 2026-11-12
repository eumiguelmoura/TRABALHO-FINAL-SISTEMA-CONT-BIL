<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Supplier extends Model {
    use \Illuminate\Database\Eloquent\Factories\HasFactory;protected $fillable=['user_id','name','city','state','document'];}
