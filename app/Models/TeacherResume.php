<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherResume extends Model {
    protected $table = 'teacher_resumes';
    protected $fillable = ['id', 'user_id', 'file_name', 'status', 'updated_at', 'created_at'];
}
