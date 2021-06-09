<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOTP extends Model {
    protected $table = 'email_otp';
    protected $fillable = ['id', 'email', 'otp', 'status', 'updated_at','created_at'];
}