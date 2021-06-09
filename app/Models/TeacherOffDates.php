<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherOffDates extends Model {
    protected $table = 'teacher_off_dates';
    protected $fillable = ['id', 'teacher_id', 'off_date', 'status','updated_at','created_at'];
}
