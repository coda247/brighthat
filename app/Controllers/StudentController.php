<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\User;
use App\Models\ClassSession;
use App\Models\TimeSchedule;
use App\Controllers\SubjectsController;
use App\Controllers\TopicController;
use App\Controllers\ScheduleController;
use App\Controllers\ClassSessionController;
use App\Models\ClassSubscription;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class StudentController
{
    protected $user;
    protected $schedule;
    protected $classSessionController;
    protected $scheduleController;
    protected $session;
    protected $resume;
    protected $class_sub;
    protected $subjectController;
    protected $topicController;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->user = new User();
        $this->session = new ClassSession();
        $this->schedule = new TimeSchedule();
        $this->scheduleController = new ScheduleController();
        $this->classSessionController = new ClassSessionController();
        $this->class_sub = new ClassSubscription();
        $this->subjectController = new SubjectsController();
        $this->topicController = new TopicController();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    public function GetStudents(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "status"=>v::notEmpty(),
             "limit"=>v::notEmpty(),
             "offset"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $status = $this->getRequest($request,'status');
        $limit = $this->getRequest($request,'limit');
        $offset = $this->getRequest($request,'offset')-1;
        $classes = $this->students($status,$limit,$offset);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function GetStudentClasses(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "student_id"=>v::notEmpty(),
            "status"=>v::notEmpty(),
             "limit"=>v::notEmpty(),
             "offset"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $student_id = $this->getRequest($request,'student_id');
        $status = $this->getRequest($request,'status');
        $classes = $this->studentClasses($student_id,$status);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function GetStudentSessions(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "student_id"=>v::notEmpty(),
             "limit"=>v::notEmpty(),
             "offset"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $student_id = $this->getRequest($request,'student_id');
        $classes = $this->studentClassSessions($student_id);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function GetStudentOverview(Request $request, Response $response){

        $this->validator->validate($request,[
             "student_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $student_id = $this->getRequest($request,'student_id');
        
        $overview = $this->studentOverview($student_id);
        return $this->customResponse->is200Response($response,$overview);
    }

    public function GetStudentSchedule(Request $request, Response $response){

        $this->validator->validate($request,[
             "student_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $student_id = $this->getRequest($request,'student_id');
        
        $overview = $this->scheduleController->studentSchedule($student_id);
        return $this->customResponse->is200Response($response,$overview);
    }

    public function studentClasses($student_id,$status){
        if ($status == 11) {
            $subscriptions = $this->class_sub->where(["student_id"=>$student_id])->get();  
        }else{
            $subscriptions = $this->class_sub->where(["student_id"=>$student_id,"status"=>$status])->get();
        }
        return $subscriptions;
    }

    public function students($status,$limit,$offset){
        if ($status == 11) {
            $students = $this->user->where(["account_type"=>5])->offset($offset)->limit($limit)->get(); 
        }else{
            $students = $this->user->where(["account_type"=>5,"status"=>$status])->offset($offset)->limit($limit)->get();
        }
        return $students;
    }

    public function studentClassSessions($student_id){
        $sessions = array();
        $query = $this->schedule->where(["student_id"=>$student_id])->get(); 
        foreach ($query as $key => $ses) {
            $obj = new stdClass();
            $obj->id = $ses->id;
            $obj->session_data = $ses;
            $obj->topic_info = $this->topicController->topicInfo($ses->topic_id);
            $sessions[] = $obj;
        }
        return $sessions;
    }

    public function studentOverview($student_id){
        $activeClasses = $this->class_sub->where(["student_id"=>$student_id,"status"=>1])->count();
        $completedClasses = $this->class_sub->where(["student_id"=>$student_id,"status"=>5])->count();
        $totalClasses = $this->class_sub->where(["student_id"=>$student_id])->count();
        $liveSessions = $this->classSessionController->studentSessionCount($student_id);
        
        $obj = new stdClass();
        $obj->student_id = $student_id;
        $obj->active_classes = $activeClasses;
        $obj->completed_classes = $completedClasses;
        $obj->total_classes_enrolled = $totalClasses;
        $obj->total_live_sessions = $liveSessions;
        return $obj;
    }

}