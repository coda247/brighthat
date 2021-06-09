<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\ClassGrade;
use App\Controllers\ClassCategoryController;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class ClassGradeController
{
    protected $class_grade;
    protected $customResponse;
    protected $categoryController;
    protected $validator;

    public function __construct()
    {
        $this->class_grade = new ClassGrade();
        $this->categoryController = new ClassCategoryController();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    public function GetClassGrades(Request $request, Response $response)
    {
        $category_id = $this->getRequest($request,'category_id');
        $classes = $this->classGrades($category_id);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function GetClassInfo(Request $request, Response $response)
    {
        $class_id = $this->getRequest($request,'class_id');
        $info = $this->classInfo($class_id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function NewClassGrade(Request $request, Response $response){

        $this->validator->validate($request,[
            "category_id"=>v::notEmpty(),
             "grade_name"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->category_id = $this->getRequest($request,'category_id');
        $obj->grade_name = $this->getRequest($request,'grade_name');
        
        $create = $this->createNewClass($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function EditClass(Request $request, Response $response){

        $this->validator->validate($request,[
             "class_id"=>v::notEmpty(),
             "category_id"=>v::notEmpty(),
             "grade_name"=>v::notEmpty(),
             "status"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->id = $this->getRequest($request,'class_id');
        $obj->category_id = $this->getRequest($request,'category_id');
        $obj->grade_name = $this->getRequest($request,'grade_name');
        $obj->status = $this->getRequest($request,'status');
        
        $update = $this->updateClass($obj);
        return $this->customResponse->is200Response($response,$update);
    }

    public function DeleteClassGrade(Request $request, Response $response){

        $this->validator->validate($request,[
             "class_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $class_id = $this->getRequest($request,'class_id');
        
        $update = $this->deleteClass($class_id);
        return $this->customResponse->is200Response($response,$update);
    }

    public function createNewClass($data){
        try {
            $this->class_grade->create([
                "category_id"=>$data->category_id,
                "grade_name"=>$data->grade_name,
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "New class created successfully";
             return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to create new class";
            return $obj;
        }
    }

    public function classInfo($class_id){
        $query = $this->class_grade->where(["id"=>$class_id]);
        $obj = new stdClass();
        if ($query->count() > 0) {
            $obj->info = $query->get()[0];
            $obj->category = $this->categoryController->categoryInfo($obj->info->category_id)->category_name;
        }
        return $obj;
    }

    public function classGrades($category_id){
        if ($category_id == 'all') {
            $classes = $this->class_grade->all(); 
        }else{
            $classes = $this->class_grade->where(["category_id"=>$category_id])->get();
        }
        return $classes;
    }

    public function updateClass($data){
        try {
            $this->class_grade->find($data->id)->update([
                "category_id"=>$data->category_id,
                "grade_name"=>$data->grade_name,
                "status"=>$data->status
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "Class updated successfully";
             return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to update class";
            return $obj;
        }
        
    }

    public function deleteClass($id){
        try {
            $this->class_grade->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Class deleted successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Failed to delete class";
            return $obj;
        }
        
    }

}