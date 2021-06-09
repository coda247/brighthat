<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherExamAnswer extends Model {
    protected $table = 'teacher_exam_answers';
    protected $fillable = ['id', 'exam_id', 'teacher_id', 'question_id', 'answer','is_correct','status','updated_at','created_at'];
}
