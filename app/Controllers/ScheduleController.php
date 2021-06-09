<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\TimeSchedule;
use App\Models\TeacherSubject;
use App\Models\TeacherOffDates;
use App\Controllers\TopicOutlineController;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class ScheduleController
{
    protected $schedule;
    protected $teach_sub;
    protected $outlineController;
    protected $offDate;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->schedule = new TimeSchedule();
        $this->teach_sub = new TeacherSubject();
        $this->offDate = new TeacherOffDates();
        $this->outlineController = new TopicOutlineController();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
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
        $schedule = $this->teacherSchedule($teacher_id);
        return $this->customResponse->is200Response($response,$schedule);
    }

    public function GetClassSchedule(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "subscription_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $subscription_id = $this->getRequest($request,'subscription_id');
        $schedule = $this->classSchedule($subscription_id);
        return $this->customResponse->is200Response($response,$schedule);
    }


    public function GetSecondaryTeachers(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "time"=>v::notEmpty(),
            "topic_id"=>v::notEmpty(),
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $time = $this->getRequest($request,'time');
        $topic_id = $this->getRequest($request,'topic_id');
        $teachers = $this->secondaryTeachers($topic_id,$time);
        return $this->customResponse->is200Response($response,$teachers);
    }

    public function SecondaryTeacherCheck(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "time"=>v::notEmpty(),
            "topic_id"=>v::notEmpty(),
            "primary_teacher"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $time = $this->getRequest($request,'time');
        $topic_id = $this->getRequest($request,'topic_id');
        $primary_teacher = $this->getRequest($request,'primary_teacher');
        $teachers = $this->checkForSecondaryTeacher($topic_id,$time,$primary_teacher);
        return $this->customResponse->is200Response($response,$teachers);
    }

    public function GetScheduleInfo(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $id = $this->getRequest($request,'id');
        $info = $this->scheduleInfo($id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function DeleteSchedule(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "scheduled_time"=>v::notEmpty(),
            "subscription_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $scheduled_time = $this->getRequest($request,'scheduled_time');
        $subscription_id = $this->getRequest($request,'subscription_id');
        $delete = $this->removeSchedule($scheduled_time,$subscription_id);
        return $this->customResponse->is200Response($response,$delete);
    }

    public function SaveNewSchedule(Request $request, Response $response){

        $this->validator->validate($request,[
            "subscription_id"=>v::notEmpty(),
            "outline_id"=>v::notEmpty(),
            "student_id"=>v::notEmpty(),
            "teacher_id"=>v::notEmpty(),
            "scheduled_time"=>v::notEmpty(),
            "scheduled_date"=>v::notEmpty(),
            "schedule_type"=>v::notEmpty()
         ]);

         if($this->validator->failed()){
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $obj = new stdClass();
        $obj->subscription_id = $this->getRequest($request,'subscription_id');
        $obj->outline_id = $this->getRequest($request,'outline_id');
        $obj->student_id = $this->getRequest($request,'student_id');
        $obj->teacher_id = $this->getRequest($request,'teacher_id');
        $obj->scheduled_time = $this->getRequest($request,'scheduled_time');
        $obj->scheduled_date = $this->getRequest($request,'scheduled_date');
        $obj->schedule_type = $this->getRequest($request,'schedule_type');
        
        $create = $this->saveSchedule($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function saveSchedule($data){
        $check  = $this->schedule->where(["teacher_id"=>$data->teacher_id,"scheduled_time"=>$data->scheduled_time])->count();
        if ($check < 1) {
            if ($data->schedule_type == 'primary') {
                $live_session = $data->teacher_id;
            }else{
                $live_session = 0;
            }
            try {
                $this->schedule->create([
                    "subscription_id"=>$data->subscription_id,
                    "outline_id"=>$data->outline_id,
                    "student_id"=>$data->student_id,
                    "teacher_id"=>$data->teacher_id,
                    "scheduled_time"=>$data->scheduled_time,
                    "scheduled_date"=>$data->scheduled_date,
                    "schedule_type"=>$data->schedule_type,
                    "live_session"=>$live_session
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Schedule saved successfully";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to save schedule";
                return $obj;
            }   
        }else{
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Schedule saved already";
            return $obj;
        }
    }

    public function teacherSchedule($teacher_id){
        $arr = array();
        $schedule = $this->schedule->where(["teacher_id"=>$teacher_id,"status"=>0])->get();
        foreach ($schedule as $key => $sch) {
            $arr[] = $this->scheduleInfo($sch->id);
        }
        return $arr;
    }


    public function studentSchedule($student_id){
        $arr = array();
        $schedule = $this->schedule->where(["student_id"=>$student_id,"status"=>0])->get();
        foreach ($schedule as $key => $sch) {
            $arr[] = $this->scheduleInfo($sch->id);
        }
        return $arr;
    }


    public function teacherScheduleCount($teacher_id){
        $schedule = $this->schedule->where(["teacher_id"=>$teacher_id,"status"=>0])->count();
        return $schedule;
    }


    public function classSchedule($subscription_id){
        $arr = array();
        $schedule = $this->schedule->where(["subscription_id"=>$subscription_id])->get();
        foreach ($schedule as $key => $sch) {
            $arr[] = $this->scheduleInfo($sch->id);
        }
        return $arr;
    }

    public function scheduleInfo($id)
    {
        $info = $this->schedule->where(["id"=>$id])->get()[0];
        $obj = new stdClass();
        $obj->status = 'success';
        $obj->schedule_id = $id;
        $obj->outlineInfo = $this->outlineController->outlineInfo($info->outline_id);
        return $info;
    }

    public function secondaryTeachers($subscription_id){
        $teachers = $this->schedule->where(["subscription_id"=>$subscription_id,"schedule_type"=>'secondary'])->groupBy("teacher_id")->join("users","users.id","=","time_scedule.teacher_id")->get();
        return $teachers;
    }

    public function checkForSecondaryTeacher($topic_id,$primary_teacher,$scheduled_time)
    {
        $all = $this->teach_sub->where(["topic_id"=>$topic_id])->whereNotIn('teacher_id', [$primary_teacher])->get();
        $data = array();
        foreach ($all as $key => $value) {
            $check = $this->schedule->where(["teacher_id"=>$value->user_id,"scheduled_time"=>$scheduled_time])->count();
            $check2 = $this->offDate->where(["teacher_id"=>$value->user_id])->whereDate("off_date","=",$scheduled_time)->count();
            if ($check > 0 || $check2 > 0) {
                continue;
            }
            $data[] = $value->user_id;
        }
        return $data;
    }

    public function removeSchedule($scheduled_time,$subscription_id){
        $delete = $this->schedule->where(["scheduled_time"=>$scheduled_time,"subscription_id"=>$subscription_id])->delete();
        return $delete;
    }

}