<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicOutline extends Model {
    protected $table = 'trial_requests';
    protected $fillable = ['id', 'user_id', 'topic_id', 'teacher_id',
     'status','updated_at', 'created_at'];
}
