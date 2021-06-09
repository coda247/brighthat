<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {
    protected $table = 'subscriptions';
    protected $fillable = ['id', 'user_id', 'package_id', 'due_date', 'credit','status','updated_at','created_at'];
}
