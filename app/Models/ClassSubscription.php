<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubscription extends Model {
    protected $table = 'class_subscriptions';
    protected $fillable = ['id', 'topic_id', 'student_id', 'teacher_id', 'sessions','current_session','subscription_type', 'status', 'updated_at', 'created_at'];
}
