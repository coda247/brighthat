<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model {
    protected $table = 'rating';
    protected $fillable = ['id','teacher_id', 'client_id', 'client_type', 'class_sub_id', 'rating','status','updated_at','created_at'];
}