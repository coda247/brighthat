<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPack extends Model {
    protected $table = 'credit_packs';
    protected $fillable = ['id', 'package', 'credit', 'validity', 'is_transferable','is_all_subjects','exams','status','updated_at','created_at'];
}
