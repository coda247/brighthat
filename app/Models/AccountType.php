<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountType extends Model {
    protected $table = 'account_types';
    protected $fillable = ['id', 'description', 'status', 'updated_at', 'created_at'];
}