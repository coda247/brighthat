<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicOutline extends Model {
    protected $table = 'transactions';
    protected $fillable = ['id', 'transaction_id', 'class_subscription_id', 'user_id',
     'payment_type', 'amount_paid','payment_description','status','updated_at', 'created_at'];
}
