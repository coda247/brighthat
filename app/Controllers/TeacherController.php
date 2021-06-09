<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\User;
use App\Models\TeacherResume;
use App\Models\TeacherSubject;
use App\Models\ClassSession;
use App\Models\TeacherOffDates;
use App\Models\TimeSchedule;
use App\Models\WorkingHour;
use App\Controllers\SubjectsController;
use App\Controllers\ScheduleController;
use App\Controllers\TopicController;
use App\Controllers\RatingController;
use App\Controllers\ReviewController;
use App\Controllers\ClassSessionController;
use App\Models\ClassSubscription;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class TeacherController
{
    protected $user;
    protected $offDate;
    protected $schedule;
    protected $workingHour;
    protected $scheduleController;
    protected $ratingController;
    protected $reviewController;
    protected $classSessionController;
    protected $teach_sub;
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
        $this->teach_sub = new TeacherSubject();
        $this->resume = new TeacherResume();
        $this->session = new ClassSession();
        $this->offDate = new TeacherOffDates();
        $this->schedule = new TimeSchedule();
        $this->workingHour = new WorkingHour();
        $this->scheduleController = new ScheduleController();
        $this->ratingController = new RatingController();
        $this->reviewController = new ReviewController();
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

    public function GetTeachers(Request $request, Response $response)
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
        $classes = $this->teachers($status,$limit,$offset);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function GetTeacherClasses(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "teacher_id"=>v::notEmpty(),
            "status"=>v::notEmpty(),
             "limit"=>v::notEmpty(),
             "offset"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $teacher_id = $this->getRequest($request,'teacher_id');
        $status = $this->getRequest($request,'status');
        $classes = $this->teacherClasses($teacher_id,$status);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function GetTeacherSessions(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "teacher_id"=>v::notEmpty(),
             "limit"=>v::notEmpty(),
             "offset"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $teacher_id = $this->getRequest($request,'teacher_id');
        $classes = $this->teacherClassSessions($teacher_id);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function GetTeacherTopics(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "teacher_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $teacher_id = $this->getRequest($request,'teacher_id');
        $classes = $this->teacherSubjects($teacher_id);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function GetTeacherSchedule(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "teacher_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $teacher_id = $this->getRequest($request,'teacher_id');
        $classes = $this->scheduleController->teacherSchedule($teacher_id);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function AddNewTopic(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "teacher_id"=>v::notEmpty(),
            "topic_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
       $obj = new stdClass();
       $obj->teacher_id = $this->getRequest($request,'teacher_id');
       $obj->topic_id = $this->getRequest($request,'topic_id');
       
       $create = $this->addSubject($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function AddResume(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "teacher_id"=>v::notEmpty(),
            "file_name"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
       $obj = new stdClass();
       $obj->teacher_id = $this->getRequest($request,'teacher_id');
       $obj->file_name = $this->getRequest($request,'file_name');
       
       $save = $this->saveResume($obj);
        return $this->customResponse->is200Response($response,$save);
    }


    public function RemoveATopic(Request $request, Response $response){

        $this->validator->validate($request,[
             "id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $id = $this->getRequest($request,'id');
        
        $remove = $this->removeTopic($id);
        return $this->customResponse->is200Response($response,$remove);
    }

    public function GetTeacherOverview(Request $request, Response $response){

        $this->validator->validate($request,[
             "teacher_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $teacher_id = $this->getRequest($request,'teacher_id');
        
        $overview = $this->teacherOverview($teacher_id);
        return $this->customResponse->is200Response($response,$overview);
    }

    public function GetTeacherWorkingHours(Request $request, Response $response){

        $this->validator->validate($request,[
             "teacher_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $teacher_id = $this->getRequest($request,'teacher_id');
        
        $overview = $this->techerWorkingHours($teacher_id);
        return $this->customResponse->is200Response($response,$overview);
    }

    public function GetTeacherOffDates(Request $request, Response $response){

        $this->validator->validate($request,[
             "teacher_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $teacher_id = $this->getRequest($request,'teacher_id');
        
        $offDates = $this->teacherOffDates($teacher_id);
        return $this->customResponse->is200Response($response,$offDates);
    }

    public function AddNewWorkingHour(Request $request, Response $response){

        $this->validator->validate($request,[
             "teacher_id"=>v::notEmpty(),
             "working_hour"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $teacher_id = $this->getRequest($request,'teacher_id');
        $working_hour = $this->getRequest($request,'working_hour');
        
        $save = $this->addWorkingHour($teacher_id,$working_hour);
        return $this->customResponse->is200Response($response,$save);
    }

    public function AddNewOffDate(Request $request, Response $response){

        $this->validator->validate($request,[
             "teacher_id"=>v::notEmpty(),
             "off_date"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $teacher_id = $this->getRequest($request,'teacher_id');
        $off_date = $this->getRequest($request,'off_date');
        
        $save = $this->addOffDate($teacher_id,$off_date);
        return $this->customResponse->is200Response($response,$save);
    }

    public function RemoveAWorkingHour(Request $request, Response $response){

        $this->validator->validate($request,[
             "id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $id = $this->getRequest($request,'id');
        
        $remove = $this->removeWorkingHour($id);
        return $this->customResponse->is200Response($response,$remove);
    }

    public function RemoveAnOffDate(Request $request, Response $response){

        $this->validator->validate($request,[
             "id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $id = $this->getRequest($request,'id');
        
        $remove = $this->removeOffDate($id);
        return $this->customResponse->is200Response($response,$remove);
    }

    

    public function teacherClasses($teacher_id,$status){
        if ($status == 11) {
            $subscriptions = $this->class_sub->where(["teacher_id"=>$teacher_id])->get();  
        }else{
            $subscriptions = $this->class_sub->where(["teacher_id"=>$teacher_id,"status"=>$status])->get();
        }
        return $subscriptions;
    }

    public function techerWorkingHours($teacher_id)
    {
        $workingHours = $this->workingHour->where(["teacher_id"=>$teacher_id])->orderBy("working_hour")->get(); 
        return $workingHours;
    }

    public function teacherOffDates($teacher_id)
    {
        $offDates = $this->offDate->where(["teacher_id"=>$teacher_id])->orderBy("off_date")->get(); 
        return $offDates;
    }

    
    public function addWorkingHour($teacher_id,$hour)
    {
        $check = $this->workingHour->where(["teacher_id"=>$teacher_id,"working_hour"=>$hour])->count();
        if ($check < 1) {
            try {
                $this->workingHour->create([
                    "teacher_id"=>$teacher_id,
                    "working_hour"=>$hour
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Working hour added successfully";
                return $obj;
            } catch (\Throwable $th) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to add working hour";
                return $obj;
            }
        }else{
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Working hour already added";
            return $obj;
        }
    }

    public function addOffDate($teacher_id,$off_date)
    {
        $check = $this->offDate->where(["teacher_id"=>$teacher_id,"off_date"=>$off_date])->count();
        if ($check < 1) {
            try {
                $this->offDate->create([
                    "teacher_id"=>$teacher_id,
                    "off_date"=>$off_date
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Date added successfully";
                return $obj;
            } catch (\Throwable $th) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to add date";
                return $obj;
            }
        }else{
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Date already added";
            return $obj;
        }
    }

    public function removeWorkingHour($id)
    {
        try {
            $this->workingHour->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Working hour removed successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to remove working hour";
            return $obj;
        }
    }

    public function removeOffDate($id)
    {
        try {
            $this->offDate->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Date removed successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to remove date";
            return $obj;
        }
    }

    public function teachers($status,$limit,$offset){
        if ($status == 11) {
            $subscriptions = $this->user->where(["account_type"=>3])->offset($offset)->limit($limit)->get(); 
        }else{
            $subscriptions = $this->user->where(["account_type"=>3,"status"=>$status])->offset($offset)->limit($limit)->get();
        }
        return $subscriptions;
    }

    public function teacherSubjects($teacher_id){
        $subjects = $this->teach_sub::selectRaw('*')->where(["user_id"=>$teacher_id])->join('subject_topics', 'subject_topics.id', '=', 'teacher_subjects.topic_id')->get(); 
        return $subjects;
    }

    public function teacherResume($teacher_id){
        $resume = $this->resume->where(["user_id"=>$teacher_id])->get(); 
        return $resume;
    }

    public function saveResume($data){
        try {
            $this->resume->create([
                "user_id"=>$data->user_id,
                "file_name"=>$data->file_name
                ])->get(); 
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Resume saved successfully";
                return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to save resume";
            return $obj;
        }
    }

    public function addSubject($data){
        $check = $this->teach_sub->where(["user_id"=>$data->teacher_id,"topic_id"=>$data->topic_id])->count();
        if ($check > 0) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Subject added already";
            return $obj;
        }else{
            try {
                $this->teach_sub->create([
                    "user_id"=>$data->teacher_id,
                    "topic_id"=>$data->topic_id
                    ]); 
                    $obj = new stdClass();
                    $obj->status = "success";
                    $obj->message = "Topic added successfully";
                    return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Failed to add topic";
                return $obj;
            }
        }
    }

    public function removeTopic($id)
    {
        try {
            $this->teach_sub->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Topic removed successfully";
            return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to remove topic";
            return $obj;
        }
    }

    public function teacherClassSessions($teacher_id){
        $sessions = array();
        $query = $this->schedule->where(["teacher_id"=>$teacher_id])->get(); 
        foreach ($query as $key => $ses) {
            $obj = new stdClass();
            $obj->id = $ses->id;
            $obj->session_data = $ses;
            $obj->topic_info = $this->topicController->topicInfo($ses->topic_id);
            $sessions[] = $obj;
        }
        return $sessions;
    }

    public function teacherOverview($teacher_id){
        $activeClasses = $this->class_sub->where(["teacher_id"=>$teacher_id,"status"=>1])->count();
        $completedClasses = $this->class_sub->where(["teacher_id"=>$teacher_id,"status"=>5])->count();
        $amountEarned = $this->class_sub->where(["teacher_id"=>$teacher_id])->count();
        $rating = $this->ratingController->teacherRating($teacher_id);
        $reviews = $this->reviewController->teacherReviewsCount($teacher_id);
        $topicsTaught = $this->classSessionController->teacherSessionCount($teacher_id);
        
        $obj = new stdClass();
        $obj->teacher_id = $teacher_id;
        $obj->active_classes = $activeClasses;
        $obj->completed_classes = $completedClasses;
        $obj->amount_earned = $amountEarned;
        $obj->rating = $rating;
        $obj->reviews = $reviews;
        $obj->unique_topics_taught = $topicsTaught;
        return $obj;
    }

}