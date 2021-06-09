<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model {
    protected $table = 'exam_sessions';
    protected $fillable = ['id','exam_type', 'session_id', 'exam_id','user_id','subscription_id','passed','status','created_at'];
}
