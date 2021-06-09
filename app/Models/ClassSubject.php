<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model {
    protected $table = 'class_subjects';
    protected $fillable = ['id', 'class_grade_id', 'subject_name', 'subject_alias', 'duration','total_session', 'status', 'updated_at', 'created_at'];
}
