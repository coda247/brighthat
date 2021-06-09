<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model {
    protected $table = 'class_sessions';
    protected $fillable = ['id','subscription_id', 'outline_id', 'subscription_type', 'topic_id', 'session','status','is_scheduled','updated_at','created_at'];
}
