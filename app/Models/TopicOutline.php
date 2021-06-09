<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicOutline extends Model {
    protected $table = 'topic_outlines';
    protected $fillable = ['id', 'subject_topic_id', 'topic_outline_title', 'topic_outline_description',
     'status', 'updated_at', 'created_at'];
}
