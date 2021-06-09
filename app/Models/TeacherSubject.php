<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model {
    protected $table = 'teacher_subjects';
    protected $fillable = ['id', 'user_id', 'topic_id', 'status', 'updated_at', 'created_at'];
}
