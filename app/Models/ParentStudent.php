<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentStudent extends Model {
    protected $table = 'parent_student';
    protected $fillable = ['id', 'parent_id', 'student_id','status', 'updated_at', 'created_at'];
}