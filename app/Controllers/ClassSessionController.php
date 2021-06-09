<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\ClassSession;
use App\Models\TimeSchedule;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class ClassSessionController
{
    protected $session;
    protected $timeSchedule;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->session = new ClassSession();
        $this->timeSchedule = new TimeSchedule();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    
    public function GetClassSessions(Request $request, Response $response)
    {
        $topic_id = $this->getRequest($request,'topic_id');
        $rating = $this->classSession($topic_id);
        return $this->customResponse->is200Response($response,$rating);
    }


    public function NewSession(Request $request, Response $response){

        $this->validator->validate($request,[
            "outline_id"=>v::notEmpty(),
            "subscription_id"=>v::notEmpty(),
            "subscription_type"=>v::notEmpty(),
            "topic_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->outline_id = $this->getRequest($request,'outline_id');
        $obj->subscription_id = $this->getRequest($request,'subscription_id');
        $obj->topic_id = $this->getRequest($request,'topic_id');
        $obj->subscription_type = $this->getRequest($request,'subscription_type');
        
        $create = $this->saveSession($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function saveSession($data){
        $check  = $this->session->where(["outline_id"=>$data->outline_id,"topic_id"=>$data->topic_id,"subscription_id"=>$data->subscription_id])->count();
        if ($check > 0) {
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Session created already";
            return $obj;
        }else{
            try {
                $this->session->create([
                    "outline_id"=>$data->outline_id,
                    "subscription_id"=>$data->subscription_id,
                    "subscription_type"=>$data->subscription_type,
                    "topic_id"=>$data->topic_id
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Session saved successfully";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Unable to save session";
                return $obj;
            }
        } 
    }

    public function classSession($subscription_id){
        $sessions = $this->session->where(["subscription_id"=>$subscription_id])->get();
        return $sessions;
    }

    public function classSessionCount($subscription_id){
        $sessions = $this->session->where(["subscription_id"=>$subscription_id])->count();
        return $sessions;
    }

    public function teacherSessionCount($teacher_id){
        $sessions = $this->timeSchedule->where(["live_session"=>$teacher_id])->count();
        return $sessions;
    }

    public function studentSessionCount($student_id){
        $sessions = $this->timeSchedule->where(["student_id"=>$student_id,"status"=>1])->count();
        return $sessions;
    }

}