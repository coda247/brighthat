<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['id','email', 'password','channel', 'first_name', 'last_name', 'other_name','wallet','account_type','gender','two_fa','break_period','status','updated_at','created_at','rating','username','birth_day','birth_month','birth_year'];
}