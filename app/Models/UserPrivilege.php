<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPrivilege extends Model {
    protected $table = 'user_privileges';
    protected $fillable = ['id', 'privilege', 'description', 'url_path', 'status','updated_at','created_at'];
}