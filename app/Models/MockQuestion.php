<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockQuestion extends Model {
    protected $table = 'mock_questions';
    protected $fillable = ['id', 'subject_id', 'class_question','class_answer','class_option_1','class_option_2','class_option_3','class_option_4', 'status', 'updated_at', 'created_at','exam_id'];
}
