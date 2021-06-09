<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransfer extends Model {
    protected $table = 'credit_transfers';
    protected $fillable = ['id', 'sender_id', 'receiver_id', 'subscription_id', 'credit','status','created_at'];
}