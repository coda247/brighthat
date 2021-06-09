<?php
declare(strict_types=1);
namespace  App\Controllers;

use App\Models\User;
use App\Models\TeacherSubject;
use App\Models\ClassSession;
use App\Models\ClassSubscription;
use App\Models\Subscription;

use App\Controllers\SubjectsController;
use App\Controllers\TopicController;
use App\Controllers\RatingController;
use App\Controllers\ReviewController;
use App\Controllers\ClassSessionController;
use App\Controllers\TopicOutlineController;
use App\Controllers\UserController;
use App\Controllers\CreditPackController;
use App\Controllers\ScheduleController;

use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class ClassSubscriptionController
{
    protected $user;
    protected $ratingController;
    protected $subscription;
    protected $scheduleController;
    protected $reviewController;
    protected $userController;
    protected $classSessionController;
    protected $creditController;
    protected $outlineController;
    protected $teach_sub;
    protected $session;
    protected $class_sub;
    protected $subjectController;
    protected $topicController;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->user = new User();
        $this->teach_sub = new TeacherSubject();
        $this->session = new ClassSession();
        $this->subscription = new Subscription();
        $this->scheduleController = new ScheduleController();
        $this->userController = new UserController();
        $this->outlineController = new TopicOutlineController();
        $this->ratingController = new RatingController();
        $this->reviewController = new ReviewController();
        $this->creditController = new CreditPackController();
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

    public function GetSubscriptions(Request $request, Response $response)
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
        $classes = $this->subscriptions($status,$limit,$offset);
        return $this->customResponse->is200Response($response,$classes);
    }


    public function GetClassSessions(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "class_sub_id"=>v::notEmpty(),
             "limit"=>v::notEmpty(),
             "offset"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $class_sub_id = $this->getRequest($request,'class_sub_id');
        $classes = $this->classSessions($class_sub_id);
        return $this->customResponse->is200Response($response,$classes);
    }
 
    public function GetSubsriptionInfo(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "class_sub_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $class_sub_id = $this->getRequest($request,'class_sub_id');
        $info = $this->subscriptionInfo($class_sub_id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function AddNewSubscription(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "topic_id"=>v::notEmpty(),
            "student_id"=>v::notEmpty(),
            "subscription_type"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
       $obj = new stdClass();
       $obj->topic_id = $this->getRequest($request,'topic_id');
       $obj->student_id = $this->getRequest($request,'student_id');
       $obj->subscription_type = $this->getRequest($request,'subscription_type');

       $getTeacher = $this->getAvailableTeacher($obj->topic_id);
       if ($getTeacher->teacher_id == 0) {
           $res = new stdClass();
           $res->status = 'error';
           $res->message = 'Unable to find an available teacher for you';
           return $this->customResponse->is200Response($response,$res);
       }else{
           $obj->teacher_id = $getTeacher->teacher_id;
           $create = $this->addSubsription($obj);
           return $this->customResponse->is200Response($response,$create);
       }
        
    }

    public function getAvailableTeacher($topic_id)
    {
        $teachArr = 0;$scheduleArr = 0;
        $allTeachers = $this->teach_sub->where(["topic_id"=>$topic_id,"status"=>1])->get();
        foreach ($allTeachers as $key => $tch) {
            $teacherStatus = $this->userController->UserInfo($tch->user_id)->status;
            if ($teacherStatus != 1) {
                continue;
            }
            $count = $this->scheduleController->teacherScheduleCount($tch->user_id);
            if ($teachArr == 0) {
                $teachArr = $tch->user_id;
                $scheduleArr = $count; 
            }else{
                if ($count < $scheduleArr) {
                    $teachArr = $tch->user_id;
                    $scheduleArr = $count; 
                }
            }
            $obj = new stdClass();
            $obj->teacher_id = $teachArr;
            $obj->schedule = $scheduleArr;
            return $obj;
        }
    }

    public function subscriptionInfo($subscription_id)
    {
        $info = $this->class_sub->where(["id"=>$subscription_id])->get()[0];
        $obj = new stdClass();
        $obj->info = $info;
        $obj->topic_info = $this->topicController->topicInfo($info->topic_id);
        $obj->course_outline = $this->outlineController->topicOutlines($info->topic_id);
        return $info;
    }

    /** function to check if a user has used his/her free trial */
    public function checkTrial($student_id)
    {
        $check = $this->class_sub->where(["student_id"=>$student_id,"subscription_type"=>"trial"])->count();
        if ($check > 0) {
            return 'invalid';
        }else{
            return 'valid';
        }
    }
    
    public function addSubsription($data){
        $obj = new stdClass();
        $outline_count = $this->outlineController->topicOutlinesCount($data->topic_id);
        $outlines = $this->outlineController->topicOutlines($data->topic_id);
        $checkSub = $this->creditController->checkSub($data->student_id);
        $duplicate = $this->checkDuplicateSubscription($data->student_id,$data->topic_id);
        if ($checkSub->status == 'error') {
            $obj->status = 'error';
            $obj->message = "Sorry, you do not have an active subscription";
            return $obj;
        }elseif ($checkSub->subInfo->credit < $outline_count) {
            $obj->status = 'error';
            $obj->credit_balanace = $checkSub->subInfo->credit;
            $obj->message = "Insufficient credit, purchase new pack to continue";
            return $obj;
        }elseif($duplicate > 0){
            $obj->status = 'error';
            //$obj->outline_count = $checkSub->package_id;
            $obj->message = "Sorry, you have an active class on this topic, select another topic or complete the class";
            return $obj;
        }else{
            try {
                $no_of_sessions = $outline_count;
                $checkTrial = $this->checkTrial($data->student_id);
                if ($data->subscription_type == 'trial' && $checkTrial == 'invalid') {
                    $obj->status = 'error';
                    $obj->message = "Free trial used already";
                    return $obj;
                }else{
                    $this->subscription->find($checkSub->package_id)->update([
                        "credit"=>$checkSub->subInfo->credit - $no_of_sessions
                    ]);
                    //$teacher_info = $this->userController->UserInfo($data->teacher_id);
                    $saveId = $this->class_sub->create([
                        "topic_id"=>$data->topic_id,
                        "student_id"=>$data->student_id,
                        "teacher_id"=>$data->teacher_id,
                        "subscription_type"=>$data->subscription_type,
                        "sessions"=>$no_of_sessions
                        ])->id; 
                    foreach ($outlines as $key => $out) {
                        $sessObj = new stdClass();
                        if ($key > 1 && $data->subscription_type == 'trial') {
                            break;
                        }
                        $sessObj->outline_id = $out->id;
                        $sessObj->subscription_id = $saveId;
                        $sessObj->topic_id = $out->subject_topic_id;
                        $sessObj->subscription_type = $data->subscription_type;
                        $this->classSessionController->saveSession($sessObj);
                    }
                    $obj->status = 'success';
                    $obj->subscription_id = $saveId;
                    $obj->message = "Subscription saved successfully";
                    return $obj;
                }
            } catch (\Throwable $e) {
                $obj->status = 'error';
                $obj->message = "Failed to add subscription";
                $obj->raw_message = $e->getMessage();
                return $obj;
            }
        }
    }

    public function checkDuplicateSubscription($student_id,$topic_id)
    {
        $check = $this->class_sub->where(["student_id"=>$student_id,"topic_id"=>$topic_id,"status"=>0])->count();
        return $check;
    }

    public function subscriptions($status,$limit,$offset)
    {
        $subscriptions = $this->class_sub->where(["status"=>$status])->offset($offset)->limit($limit)->get();
        return $subscriptions;
    }

    public function classSessions($subscription_id){
        $sessions = array();
        $query = $this->session->where(["subscription_id"=>$subscription_id])->get(); 
        foreach ($query as $key => $ses) {
            $obj = new stdClass();
            $obj->id = $ses->id;
            $obj->session_data = $ses;
            //$obj->class_info = $this->teacherClassInfo($ses->class_sub_id);
            $obj->topic_info = $this->topicController->topicInfo($ses->topic_id);
            $sessions[] = $obj;
        }
        return $sessions;
    }


}