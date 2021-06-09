<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockExam extends Model {
    protected $table = 'mock_exams';
    protected $fillable = ['id', 'title','subject_id', 'duration', 'status', 'updated_at', 'created_at'];
}
