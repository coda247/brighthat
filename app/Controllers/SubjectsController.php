<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\ClassSubject;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class SubjectsController
{
    protected $subject;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->subject = new ClassSubject();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    public function GetSubjects(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "class_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $class_id = $this->getRequest($request,'class_id');
        $subjects = $this->subjects($class_id);
        return $this->customResponse->is200Response($response,$subjects);
    }

    public function GetSubjectInfo(Request $request, Response $response)
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
        $info = $this->subjectInfo($subject_id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function NewSubject(Request $request, Response $response){

        $this->validator->validate($request,[
            "class_grade_id"=>v::notEmpty(),
             "subject_name"=>v::notEmpty(),
             "subject_alias"=>v::notEmpty(),
             "duration"=>v::notEmpty(),
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->class_grade_id = $this->getRequest($request,'class_grade_id');
        $obj->subject_name = $this->getRequest($request,'subject_name');
        $obj->subject_alias = $this->getRequest($request,'subject_alias');
        $obj->duration = $this->getRequest($request,'duration');
        
        $create = $this->createSubject($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function EditSubject(Request $request, Response $response){

        $this->validator->validate($request,[
             "class_grade_id"=>v::notEmpty(),
             "subject_name"=>v::notEmpty(),
             "subject_alias"=>v::notEmpty(),
             "duration"=>v::notEmpty(),
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->id = $this->getRequest($request,'subject_id');
        $obj->class_grade_id = $this->getRequest($request,'class_grade_id');
        $obj->subject_name = $this->getRequest($request,'subject_name');
        $obj->subject_alias = $this->getRequest($request,'subject_alias');
        $obj->duration = $this->getRequest($request,'duration');
        
        $update = $this->updateSubject($obj);
        return $this->customResponse->is200Response($response,$update);
    }

    public function DeleteASubject(Request $request, Response $response){

        $this->validator->validate($request,[
             "subject_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $subject_id = $this->getRequest($request,'subject_id');
        
        $update = $this->deleteSubject($subject_id);
        return $this->customResponse->is200Response($response,$update);
    }

    public function createSubject($data){
        try {
            $this->subject->create([
                "class_grade_id"=>$data->class_grade_id,
                "subject_name"=>$data->subject_name,
                "subject_alias"=>$data->subject_alias,
                "duration"=>$data->duration,
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "New subject created successfully";
             return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to create subject";
            return $obj;
        }
    }

    public function subjectInfo($subject_id){
        $info = $this->subject->where(["id"=>$subject_id])->get()[0];
        return $info;
    }

    public function subjects($class_id){
        if ($class_id == 'all') {
            $subjects = $this->subject->all(); 
        }else{
            $subjects = $this->subject->where(["class_grade_id"=>$class_id])->get();
        }
        return $subjects;
    }

    public function updateSubject($data){
        try {
            $this->subject->find($data->id)->update([
                "class_grade_id"=>$data->class_grade_id,
                "subject_name"=>$data->subject_name,
                "subject_alias"=>$data->subject_alias,
                "duration"=>$data->duration,
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "Subject updated successfully";
             return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to update subject";
            return $obj;
        }
        
    }

    public function deleteSubject($id){
        try {
            $this->subject->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Subject deleted successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to delete subject";
            return $obj;
        }
        
    }
}