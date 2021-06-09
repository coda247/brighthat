<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockExamSession extends Model {
    protected $table = 'mock_exam_sessions';
    protected $fillable = ['id', 'session_id', 'exam_id','user_id','passed','status','created_at'];
}
