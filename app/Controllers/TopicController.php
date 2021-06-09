<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\ClassSubject;
use App\Models\SubjectTopic;
use App\Controllers\SubjectsController;
use App\Controllers\ClassSessionController;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class TopicController
{
    protected $subject;
    protected $topic;
    protected $subjectController;
    protected $classSessionController;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->subject = new ClassSubject();
        $this->topic = new SubjectTopic();
        $this->classSessionController = new ClassSessionController();
        $this->subjectController = new SubjectsController();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    public function GetTopics(Request $request, Response $response)
    {
        $subject_id = $this->getRequest($request,'subject_id');
        $topics = $this->topics($subject_id);
        return $this->customResponse->is200Response($response,$topics);
    }

    public function GetTopicInfo(Request $request, Response $response)
    {
        $topic_id = $this->getRequest($request,'topic_id');
        $info = $this->topicInfo($topic_id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function NewTopic(Request $request, Response $response){

        $this->validator->validate($request,[
            "class_subject_id"=>v::notEmpty(),
            "topic_title"=>v::notEmpty(),
            "topic_description"=>v::notEmpty(),
            "duration"=>v::notEmpty(),
            "price_per_session"=>v::notEmpty(),
            "discount"=>v::notEmpty(),
            "cover_photo"=>v::noEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->class_subject_id = $this->getRequest($request,'class_subject_id');
        $obj->topic_title = $this->getRequest($request,'topic_title');
        $obj->topic_description = $this->getRequest($request,'topic_description');
        $obj->duration = $this->getRequest($request,'duration');
        $obj->price_per_session = $this->getRequest($request,'price_per_session');
        $obj->discount = $this->getRequest($request,'discount');
        $obj->cover_photo = $this->getRequest($request,'cover_photo');
        
        $create = $this->createTopic($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function EditTopic(Request $request, Response $response){

        $this->validator->validate($request,[
             "topic_id"=>v::notEmpty(),
             "topic_title"=>v::notEmpty(),
             "topic_description"=>v::notEmpty(),
             "duration"=>v::notEmpty(),
             "price_per_session"=>v::notEmpty(),
             "discount"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->id = $this->getRequest($request,'topic_id');
        $obj->topic_title = $this->getRequest($request,'topic_title');
        $obj->topic_description = $this->getRequest($request,'topic_description');
        $obj->duration = $this->getRequest($request,'duration');
        $obj->price_per_session = $this->getRequest($request,'price_per_session');
        $obj->discount = $this->getRequest($request,'discount');
        
        $update = $this->updateTopic($obj);
        return $this->customResponse->is200Response($response,$update);
    }

    public function DeleteATopic(Request $request, Response $response){

        $this->validator->validate($request,[
             "topic_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $topic_id = $this->getRequest($request,'topic_id');
        
        $update = $this->deleteTopic($topic_id);
        return $this->customResponse->is200Response($response,$update);
    }

    public function ChangeCover(Request $request, Response $response){

        $this->validator->validate($request,[
             "topic_id"=>v::notEmpty(),
             "cover_photo"=>v::noEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $topic_id = $this->getRequest($request,'topic_id');
        $cover_photo = $this->getRequest($request,'cover_photo');
        
        $update = $this->updateCoverPhoto($topic_id,$cover_photo);
        return $this->customResponse->is200Response($response,$update);
    }

    public function createTopic($data){
        try {
            $this->topic->create([
                "class_subject_id"=>$data->class_subject_id,
                "topic_title"=>$data->topic_title,
                "topic_description"=>$data->topic_description,
                "duration"=>$data->duration,
                "price_per_session"=>$data->price_per_session,
                "duration"=>$data->duration,
                "cover_photo"=>$data->cover_photo
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "New topic created successfully";
             return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to create topic";
            return $obj;
        }
    }

    public function topicInfo($topic_id){
        $obj = new stdClass();
        $info = $this->topic->where(["id"=>$topic_id])->get()[0];
        $obj->info = $info;
        $obj->sessions = $this->classSessionController->classSessionCount($topic_id);
        $obj->subject_info = $this->subjectController->subjectInfo($info->class_subject_id);
        return $obj;
    }

    public function topics($subject_id){
        $topics = $this->topic->where(["class_subject_id"=>$subject_id])->get();
        return $topics;
    }

    public function updateTopic($data){
        try {
            $this->topic->find($data->id)->update([
                "topic_title"=>$data->topic_title,
                "topic_description"=>$data->topic_description,
                "duration"=>$data->duration,
                "price_per_session"=>$data->price_per_session,
                "duration"=>$data->duration
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "Topic updated successfully";
             return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to update topic";
            return $obj;
        }
        
    }

    public function updateCoverPhoto($id,$cover_photo){
        try {
            $this->topic->find($id)->update([
                "cover_photo"=>$cover_photo
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "Cover photo updated successfully";
             return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to update cover photo";
            return $obj;
        }
        
    }

    public function deleteTopic($id){
        try {
            $this->topic->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Topic deleted successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to delete topic";
            return $obj;
        }
        
    }
}