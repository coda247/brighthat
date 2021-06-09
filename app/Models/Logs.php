<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model {
    protected $table = 'logs';
    protected $fillable = ['id', 'action', 'player_id', 'player', 'player_role','updated_at','created_at'];
}
