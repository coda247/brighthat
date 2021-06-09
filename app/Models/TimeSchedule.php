<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSchedule extends Model {
    protected $table = 'time_schedules';
    protected $fillable = ['id','subscription_id', 'outline_id', 'student_id', 'teacher_id', 'scheduled_time','schedule_type','live_session','scheduled_date','status','updated_at','created_at','completed'];
}