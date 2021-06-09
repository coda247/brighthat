<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strike extends Model {
    protected $table = 'strikes';
    protected $fillable = ['id', 'teacher_id', 'schedule_id', 'subscription_id', 'status','updated_at','created_at'];
}
