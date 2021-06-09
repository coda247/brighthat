<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherExam extends Model {
    protected $table = 'teacher_exams';
    protected $fillable = ['id', 'subject_id', 'title', 'duration','pass_mark', 'status','updated_at','created_at'];
}
