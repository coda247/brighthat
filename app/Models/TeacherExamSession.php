<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherExamSession extends Model {
    protected $table = 'teacher_exam_sessions';
    protected $fillable = ['id', 'session_id', 'exam_id','user_id','passed','status','created_at'];
}
