<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model {
    protected $table = 'exam_answers';
    protected $fillable = ['id', 'exam_session_id','exam_id', 'student_id', 'question_id', 'answer','is_correct','status','updated_at','created_at'];
}
