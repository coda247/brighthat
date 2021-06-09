<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\MockQuestion;
use App\Models\MockAnswer;
use App\Models\MockExamSession;
use App\Models\MockExam;
use App\Controllers\CreditPackController;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class MockExamController
{
    protected $question;
    protected $answer;
    protected $examSession;
    protected $creditController;
    protected $exam;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->question = new MockQuestion();
        $this->answer = new MockAnswer();
        $this->examSession = new MockExamSession();
        $this->exam = new MockExam();
        $this->creditController = new CreditPackController();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    
    public function GetExams(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "subject_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $subject_id = $this->getRequest($request,'subject_id');
        $exams = $this->Exams($subject_id);
        return $this->customResponse->is200Response($response,$exams);
    }

    public function GetExamQuestions(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "exam_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $exam_id = $this->getRequest($request,'exam_id');
        $questions = $this->examQuestions($exam_id);
        return $this->customResponse->is200Response($response,$questions);
    }

    public function EndExamSesseion(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "session_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $session_id = $this->getRequest($request,'session_id');
        $end = $this->closeSession($session_id);
        return $this->customResponse->is200Response($response,$end);
    }

    public function GetExamSessionInfo(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "session_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $session_id = $this->getRequest($request,'session_id');
        $info = $this->sessionInfo($session_id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function CreateExamSession(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "exam_id"=>v::notEmpty(),
            "user_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $exam_id = $this->getRequest($request,'exam_id');
        $user_id = $this->getRequest($request,'user_id');
        $create = $this->startExamSession($exam_id,$user_id);
        return $this->customResponse->is200Response($response,$create);
    }

    public function GetExamAnswers(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "session_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $session_id = $this->getRequest($request,'session_id');
        $teachers = $this->examAnswers($session_id);
        return $this->customResponse->is200Response($response,$teachers);
    }


    public function GetNextQuestion(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "exam_id"=>v::notEmpty(),
            "session_id"=>v::notEmpty(),
            "offset"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $exam_id = $this->getRequest($request,'exam_id');
        $session_id = $this->getRequest($request,'session_id');
        $offset = $this->getRequest($request,'offset');
        $score = $this->studentScore($session_id,$exam_id);
        $question = $this->nextQuestion($exam_id,$offset);
        if ($score->total_answered >= 21) {
            if ($score->percentage >= 75) {
                $obj = new stdClass();
                $obj->status = 'complete';
                $obj->message = 'Congratulations, you have completed this assessment and smashed it, try yourself with another assessment';
                return $this->customResponse->is200Response($response,$obj);
            }else{
                $obj = new stdClass();
                $obj->status = 'advanced';
                $obj->message = 'Good performance, try buying an advanced package';
                return $this->customResponse->is200Response($response,$obj);
            }
        }elseif ($score->total_answered >= 14) {
            if ($score->percentage >= 78) {
                $obj = new stdClass();
                $obj->status = 'success';
                $obj->data = $question;
                return $this->customResponse->is200Response($response,$obj);
            }else{
                $obj = new stdClass();
                $obj->status = 'intermediate';
                $obj->message = 'Poor performance, try buying an intermediate package';
                return $this->customResponse->is200Response($response,$obj);
            }
        }elseif($score->total_answered >= 7) {
            if ($score->percentage >= 85) {
                $obj = new stdClass();
                $obj->status = 'success';
                $obj->data = $question;
                return $this->customResponse->is200Response($response,$obj);
            }else{
                $obj = new stdClass();
                $obj->status = 'basic';
                $obj->message = 'Poor performance, try buying a basic package';
                return $this->customResponse->is200Response($response,$obj);
            }
        }else{
            $obj = new stdClass();
            $obj->status = 'success';
            $obj->data = $question;
            return $this->customResponse->is200Response($response,$obj);
        }
        
    }

    public function CreateExam(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "subject_id"=>v::notEmpty(),
            "title"=>v::notEmpty(),
            "duration"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->subject_id = $this->getRequest($request,'subject_id');
        $obj->title = $this->getRequest($request,'title');
        $obj->duration = $this->getRequest($request,'duration');
        $save = $this->saveExam($obj);
        return $this->customResponse->is200Response($response,$save);
    }

    public function EditExam(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "id"=>v::notEmpty(),
            "title"=>v::notEmpty(),
            "duration"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->id = $this->getRequest($request,'id');
        $obj->title = $this->getRequest($request,'title');
        $obj->duration = $this->getRequest($request,'duration');
        $update = $this->updateExam($obj);
        return $this->customResponse->is200Response($response,$update);
    }

    public function GetStudentScore(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "exam_id"=>v::notEmpty(),
            "session_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $exam_id = $this->getRequest($request,'exam_id');
        $session_id = $this->getRequest($request,'session_id');
        $score = $this->studentScore($session_id,$exam_id);
        return $this->customResponse->is200Response($response,$score);
    }

    public function DeleteExam(Request $request, Response $response){

        $this->validator->validate($request,[
            "id"=>v::notEmpty()
         ]);

         if($this->validator->failed()){
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $id = $this->getRequest($request,'id');
        
        $delete = $this->removeExam($id);
        return $this->customResponse->is200Response($response,$delete);
    }

    public function AddAnswer(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "exam_id"=>v::notEmpty(),
            "exam_session_id"=>v::notEmpty(),
            "student_id"=>v::notEmpty(),
            "question_id"=>v::notEmpty(),
            "answer"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->exam_id = $this->getRequest($request,'exam_id');
        $obj->exam_session_id = $this->getRequest($request,'exam_session_id');
        $obj->student_id = $this->getRequest($request,'student_id');
        $obj->question_id = $this->getRequest($request,'question_id');
        $obj->answer = $this->getRequest($request,'answer');

        $update = $this->saveAnswer($obj);
        return $this->customResponse->is200Response($response,$update);
    }

    

    public function AddQuestion(Request $request, Response $response){

        $this->validator->validate($request,[
            "exam_id"=>v::notEmpty(),
            "class_question"=>v::notEmpty(),
            "class_answer"=>v::notEmpty(),
            "class_option_1"=>v::notEmpty(),
            "class_option_2"=>v::notEmpty(),
            "class_option_3"=>v::notEmpty(),
            "class_option_4"=>v::notEmpty(),
            "subject_id"=>v::notEmpty(),
         ]);

         if($this->validator->failed()){
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        $obj = new stdClass();
        $obj->exam_id = $this->getRequest($request,'exam_id');
        $obj->class_question = $this->getRequest($request,'class_question');
        $obj->class_answer = $this->getRequest($request,'class_answer');
        $obj->class_option_1 = $this->getRequest($request,'class_option_1');
        $obj->class_option_2 = $this->getRequest($request,'class_option_2');
        $obj->class_option_3 = $this->getRequest($request,'class_option_3');
        $obj->class_option_4 = $this->getRequest($request,'class_option_4');
        $obj->subject_id = $this->getRequest($request,'subject_id');
        
        $save = $this->saveQuestion($obj);
        return $this->customResponse->is200Response($response,$save);
    }

    public function EditQuestion(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "id"=>v::notEmpty(),
            "class_question"=>v::notEmpty(),
            "class_answer"=>v::notEmpty(),
            "class_option_1"=>v::notEmpty(),
            "class_option_2"=>v::notEmpty(),
            "class_option_3"=>v::notEmpty(),
            "class_option_4"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->id = $this->getRequest($request,'id');
        $obj->class_question = $this->getRequest($request,'class_question');
        $obj->class_answer = $this->getRequest($request,'class_answer');
        $obj->class_option_1 = $this->getRequest($request,'class_option_1');
        $obj->class_option_2 = $this->getRequest($request,'class_option_2');
        $obj->class_option_3 = $this->getRequest($request,'class_option_3');
        $obj->class_option_4 = $this->getRequest($request,'class_option_4');

        $update = $this->updateQuestion($obj);
        return $this->customResponse->is200Response($response,$update);
    }

    public function DeleteQuestion(Request $request, Response $response){

        $this->validator->validate($request,[
            "id"=>v::notEmpty()
         ]);

         if($this->validator->failed()){
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $id = $this->getRequest($request,'id');
        
        $delete = $this->removeQuestion($id);
        return $this->customResponse->is200Response($response,$delete);
    }

    public function saveAnswer($data){
        $is_correct = $this->checkAnswer($data->question_id,$data->answer);
        $check = $this->answer->where(["question_id"=>$data->question_id])->count();
        if ($check < 1) {
            $this->answer->create([
                "exam_id"=>$data->exam_id,
                "exam_session_id"=>$data->exam_session_id,
                "student_id"=>$data->student_id,
                "question_id"=>$data->question_id,
                "answer"=>$data->answer,
                "is_correct"=>$is_correct
            ]);
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Answer saved successfully";
            return $obj;
        }else{
            try {
                $this->answer->where("question_id",$data->question_id)->update([
                    "answer"=>$data->answer,
                    "is_correct"=>$is_correct
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Answer updated successfully";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to update answer";
                return $obj;
            } 
        }
    }

    public function checkAnswer($question_id,$answer)
    {
        $info = $this->questionInfo($question_id);
        if ($info->class_answer == $answer) {
            return 1;
        }else{
            return 0;
        }
    }

    public function updateExam($data){
        try {
            $this->exam->find($data->id)->update([
                "title"=>$data->title,
                "duration"=>$data->duration,
            ]);
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Exam updated successfully";
            return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to update exam";
            return $obj;
        } 
    }

    public function updateQuestion($data){
        try {
            $this->question->find($data->id)->update([
                "class_question"=>$data->class_question,
                "class_answer"=>$data->class_answer,
                "class_option_1"=>$data->class_option_1,
                "class_option_2"=>$data->class_option_2,
                "class_option_3"=>$data->class_option_3,
                "class_option_4"=>$data->class_option_4
            ]);
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Question updated successfully";
            return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to update question";
            return $obj;
        } 
    }
    
    public function saveQuestion($data){
        $check  = $this->question->where(["class_question"=>$data->class_question,"exam_id"=>$data->exam_id])->count();
        if ($check < 1) {
            try {
                $this->question->create([
                    "exam_id"=>$data->exam_id,
                    "class_question"=>$data->class_question,
                    "class_answer"=>$data->class_answer,
                    "class_option_1"=>$data->class_option_1,
                    "class_option_2"=>$data->class_option_2,
                    "class_option_3"=>$data->class_option_3,
                    "class_option_4"=>$data->class_option_4,
                    "subject_id"=>$data->subject_id
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Question saved successfully successfully";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to save question";
                return $obj;
            }   
        }else{
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Question added already";
            return $obj;
        }
    }

    public function closeSession($session_id)
    {
        $sessionInfo = $this->sessionInfo($session_id);
        $student_id = $sessionInfo->data->student_id;
        $exam_id = $sessionInfo->data->exam_id;
        $studentScore = $this->studentScore($exam_id,$student_id);
        $examInfo = $this->examInfo($exam_id);
        if ($examInfo->status == 'error') {
            $obj = new stdClass();
            $obj->status = 'error';
            $obj->message = 'Invalid exam';
            return $obj;
        }else{
            if ($studentScore->total_question == 0) {
                $score = 0;
            }else{
                $score = ($studentScore->score / $studentScore->total_question) * 100;
            }
            $this->examSession->where(["session_id"=>$session_id])->update([
                "status"=>1
            ]);
            $obj = new stdClass();
            $obj->status = 'success';
            $obj->score = $score;
            return $obj;
        }


    }

    public function sessionInfo($session_id)
    {
        $obj = new stdClass();
        $check = $this->examSession->where(["session_id"=>$session_id]);
        if ($check->count() < 1) {
            $obj->status = 'error';
            $obj->message = 'Invalid session';
            return $obj;
        }else{
            $obj->status = 'success';
            $obj->data = $check->get()[0];
            return $obj;
        }
    }

    public function startExamSession($exam_id,$user_id){
            $obj = new stdClass();$exam_session_id = uniqid();
            $check = $this->checkForExamSession($user_id);
            if ($check->status == 'success') {
                try {
                    $this->examSession->create([
                        "exam_id"=>$exam_id,
                        "user_id"=>$user_id,
                        "session_id"=>$exam_session_id
                    ]);
                    $obj->status = 'success';
                    $obj->session_id = $exam_session_id;
                    $obj->message = "Session started successfully";
                    return $obj;
                } catch (\Throwable $e) {
                    //return $e->getMessage();
                    $obj->status = 'error';
                    $obj->message = "Unable to start session";
                    return $obj;
                }   
            }else{
                return $check;
            } 
    }

    public function checkForExamSession($user_id)
    {
       $query = $this->examSession->where(["user_id"=>$user_id,"status"=>0]);
       if ($query->count() > 0) {
           $row = $query->get()[0];
           $obj = new stdClass();
           $obj->status = 'active';
           $obj->data = $row;
           $obj->message = 'You have an active mock exam, complete it before you can take on another one';
           return $obj;
        }else{
            $obj = new stdClass();
            $obj->status = 'success';
            $obj->message = 'No active mock exam session found';
            return $obj;
       }
    }

    public function saveExam($data){
        $check  = $this->exam->where(["title"=>$data->title])->count();
        if ($check < 1) {
            try {
                $this->exam->create([
                    "title"=>$data->title,
                    "subject_id"=>$data->subject_id,
                    "duration"=>$data->duration,
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Exam saved successfully";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to save exam";
                //$obj->raw_message = $e->getMessage();
                return $obj;
            } 
        }else{
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Exam created already";
            return $obj;
        }
    }

    public function Exams($subject_id){
        $exams = $this->exam->where(["subject_id"=>$subject_id])->get();
        return $exams;
    }

    public function examInfo($id)
    {
        $obj = new stdClass();
        $check = $this->exam->where(["id"=>$id]);
        if ($check->count() < 1) {
            $obj->status = 'error';
            $obj->message = 'Invalid session';
            return $obj;
        }else{
            $obj->status = 'success';
            $obj->data = $check->get()[0];
            return $obj;
        }
    }
    
    public function examQuestions($exam_id){
        $questions = $this->question->where(["exam_id"=>$exam_id])->get();
        return $questions;
    }

    public function examAnswers($session_id)
    {
        $answers = $this->answer->where(["exam_session_id"=>$session_id])->get();
        return $answers;
    }

    public function nextQuestion($exam_id,$offset)
    {
        $question = $this->question->where(["exam_id"=>$exam_id])->offset($offset)->limit(1)->get()[0];
        return $question;
    }

    public function questionInfo($id)
    {
        $info = $this->question->where(["id"=>$id])->get()[0];
        return $info;
    }

    public function studentScore($session_id,$exam_id){
        $correct = $this->answer->where(["exam_session_id"=>$session_id,"is_correct"=>1])->count();
        $total_answered = $this->answer->where(["exam_session_id"=>$session_id])->count();
        $total_questions = $this->question->where(["exam_id"=>$exam_id])->count();
        $obj = new stdClass();
        $obj->score = $correct;
        $obj->total_questions = $total_questions;
        $obj->total_answered = $total_answered;
        if ($total_answered == 0) {
            $obj->percentage = 0;
        }else{
            $obj->percentage = ($obj->score / $total_answered) * 100;
        }
        return $obj;
    }

    public function removeExam($id){
        try {
            $this->exam->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Exam deleted successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to delete exam";
            return $obj;
        }
    }

    public function removeQuestion($id){
        try {
            $this->question->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Question deleted successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to delete question";
            return $obj;
        }
    }

}