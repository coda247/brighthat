<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model {
    protected $table = 'reviews';
    protected $fillable = ['id','teacher_id', 'client_id', 'client_type', 'class_sub_id', 'review','status','updated_at','created_at'];
}