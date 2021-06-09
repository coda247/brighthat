<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriviledgeAssignment extends Model {
    protected $table = 'user_privilege_assignments';
    protected $fillable = ['id', 'user_id', 'privilege_id', 'status', 'updated_at','created_at'];
}