<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassGrade extends Model {
    protected $table = 'class_grades';
    protected $fillable = ['id', 'category_id', 'grade_name', 'status', 'updated_at', 'created_at'];
}
