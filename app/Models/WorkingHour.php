<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model {
    protected $table = 'teacher_working_hours';
    protected $fillable = ['id','teacher_id', 'working_hour','status','updated_at','created_at'];
}