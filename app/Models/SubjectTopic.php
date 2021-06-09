<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectTopic extends Model {
    protected $table = 'subject_topics';
    protected $fillable = ['id', 'class_subject_id', 'topic_title', 'topic_description', 'status', 'updated_at', 'created_at','cover_photo'];
}
