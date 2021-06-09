<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\TopicOutline;
use App\Controllers\TopicController;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class TopicOutlineController
{
    protected $outline;
    protected $topicController;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->outline = new TopicOutline();
        $this->topicController = new TopicController();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    public function GetTopicOutlines(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "topic_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $topic_id = $this->getRequest($request,'topic_id');
        $outlines = $this->topicOutlines($topic_id);
        return $this->customResponse->is200Response($response,$outlines);
    }

    public function GetOutlineInfo(Request $request, Response $response)
    {
        $id = $this->getRequest($request,'id');
        $info = $this->outlineInfo($id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function NewOutline(Request $request, Response $response){

        $this->validator->validate($request,[
            "subject_topic_id"=>v::notEmpty(),
            "topic_outline_title"=>v::notEmpty(),
            "topic_outline_description"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->subject_topic_id = $this->getRequest($request,'subject_topic_id');
        $obj->topic_outline_title = $this->getRequest($request,'topic_outline_title');
        $obj->topic_outline_description = $this->getRequest($request,'topic_outline_description');
        
        $create = $this->createNewOutline($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function EditOutline(Request $request, Response $response){

        $this->validator->validate($request,[
             "id"=>v::notEmpty(),
             "topic_outline_title"=>v::notEmpty(),
             "topic_outline_description"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->id = $this->getRequest($request,'id');
        $obj->topic_outline_title = $this->getRequest($request,'topic_outline_title');
        $obj->topic_outline_description = $this->getRequest($request,'topic_outline_description');
        
        $update = $this->updateOutline($obj);
        return $this->customResponse->is200Response($response,$update);
    }

    public function RemoveOutline(Request $request, Response $response){

        $this->validator->validate($request,[
             "id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $id = $this->getRequest($request,'id');
        
        $update = $this->deleteOutline($id);
        return $this->customResponse->is200Response($response,$update);
    }

    public function createNewOutline($data){
        try {
            $this->outline->create([
                "subject_topic_id"=>$data->subject_topic_id,
                "topic_outline_title"=>$data->topic_outline_title,
                "topic_outline_description"=>$data->topic_outline_description
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "New outline created successfully";
             return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to create outline";
            return $obj;
        }
    }

    public function outlineInfo($category_id){
        $query = $this->outline->where(["id"=>$category_id]);
        $obj = new stdClass();
        if ($query->count() > 0) {
            $obj->info = $query->get()[0];
            $obj->topic_info = $this->topicController->topicInfo($obj->info->subject_topic_id)->topic_title;
        }
        return $obj;
    }

    public function topicOutlines($topic_id){
        $categories = $this->outline->where(["subject_topic_id"=>$topic_id])->get(); 
        return $categories;
    }
    
    public function topicOutlinesCount($topic_id){
        $categories = $this->outline->where(["subject_topic_id"=>$topic_id])->count(); 
        return $categories;
    }

    public function updateOutline($data){
        try {
            $this->outline->find($data->id)->update([
                "topic_outline_title"=>$data->topic_outline_title,
                "topic_outline_description"=>$data->topic_outline_description
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "Outline updated successfully";
             return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to update outline";
            return $obj;
        }
        
    }

    public function deleteOutline($id){
        try {
            $this->outline->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Outline deleted successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to delete outline";
            return $obj;
        }
        
    }

}