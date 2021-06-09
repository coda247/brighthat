<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\User;
use App\Models\ParentStudent;
use App\Controllers\UserController;
use App\Controllers\StudentController;
use App\Controllers\AuthController;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class ParentController
{
    protected $user;
    protected $parent;
    protected $userController;
    protected $auth;
    protected $student;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->user = new User();
        $this->parent = new ParentStudent();
        $this->userController = new UserController();
        $this->auth = new AuthController();
        $this->student = new StudentController();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    public function GetParents(Request $request, Response $response)
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
        $parents = $this->parents($status,$limit,$offset);
        return $this->customResponse->is200Response($response,$parents);
    }

    public function GetParentChildren(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "parent_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $parent_id = $this->getRequest($request,'parent_id');
        $children = $this->parentChildren($parent_id);
        return $this->customResponse->is200Response($response,$children);
    }

    public function GetParentOverview(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "parent_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $parent_id = $this->getRequest($request,'parent_id');
        $children = $this->parentOverview($parent_id);
        return $this->customResponse->is200Response($response,$children);
    }

    public function GetChildOverView(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "child_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
       
        $child_id = $this->getRequest($request,'child_id');
        $classes = $this->student->studentOverview($child_id);
        return $this->customResponse->is200Response($response,$classes);
    }

    public function RegisterChild(Request $request, Response $response){

        $this->validator->validate($request,[
            "first_name"=>v::notEmpty(),
            "last_name"=>v::notEmpty(),
             "email"=>v::notEmpty()->email(),
             "birth_day"=>v::notEmpty(),
             "birth_month"=>v::notEmpty(),
             "birth_year"=>v::notEmpty(),
             "username"=>v::notEmpty(),
             "parent_id"=>v::notEmpty(),
             "password"=>v::notEmpty()
         ]);
  
         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }
  
         if($this->auth->EmailExist($this->getRequest($request,"email")))
         {
             $responseMessage = "Email already exist";
             $obj = new stdClass();
             $obj->status = 'error';
             $obj->message = $responseMessage;
             return $this->customResponse->is400Response($response,$obj);
         }
         $obj = new stdClass();
         $password = $this->getRequest($request,'password');
         $obj->first_name = $this->getRequest($request,'first_name');
         $obj->last_name = $this->getRequest($request,'last_name');
         $obj->email = $this->getRequest($request,'email');
         $obj->birth_day = $this->getRequest($request,'birth_day');
         $obj->birth_month = $this->getRequest($request,'birth_month');
         $obj->birth_year = $this->getRequest($request,'birth_year');
         $obj->username = $this->getRequest($request,'username');
         $obj->parent_id = $this->getRequest($request,'parent_id');

         $passwordHash = $this->auth->hashPassword($password);
         $obj->password = $passwordHash;
        
        $addChild = $this->addChild($obj);
        return $this->customResponse->is200Response($response,$addChild);
    }


    public function parentChildren($parent_id){
        $all = array();
        $childQuery = $this->parent->where(["parent_id"=>$parent_id])->get(); 
        foreach ($childQuery as $key => $child) {
           $childData = $this->userController->UserInfo($child->student_id);
           $all[] = $childData;
        }
        return $all;
    }

    public function childrenClasses($parent_id)
    {
        $active = 0;$completed = 0;$total = 0;
        $children = $this->parentChildren($parent_id);
        foreach ($children as $key => $child) {
            $info = $this->student->studentOverview($child->id);
            $active += $info->active_classes;
            $completed += $info->completed_classes;
            $total += $info->total_classes_enrolled;
        }
        $obj = new stdClass();
        $obj->status = 'success';
        $obj->active = $active;
        $obj->completed = $completed;
        $obj->total_classess_enrolled = $total;
        return $obj;
    }

    public function parentOverview($parent_id)
    {
        $childClassInfo = $this->childrenClasses($parent_id);
        $children = $this->parent->where(["parent_id"=>$parent_id])->count();
        $obj = new stdClass();
        $obj->status = 'success';
        $obj->parent_id = $parent_id;
        $obj->active_classes = $childClassInfo->active;
        $obj->completed_classes = $childClassInfo->completed;
        $obj->total_classes_enrolled = $childClassInfo->total_classess_enrolled;
        $obj->children = $children;
        return $obj;
    }

    public function parents($status,$limit,$offset){
        if ($status == 11) {
            $students = $this->user->where(["account_type"=>4])->offset($offset)->limit($limit)->get(); 
        }else{
            $students = $this->user->where(["account_type"=>4,"status"=>$status])->offset($offset)->limit($limit)->get();
        }
        return $students;
    }

    public function addChild($data){
        $usernameCheck = $this->user->where(["username"=>$data->username])->count(); 
        if ($usernameCheck > 0) {
            $obj = new stdClass();
            $obj->status = "error"; 
            $obj->message = "Username already exist"; 
            return $obj;
        }else{
            try {
                $childId = $this->user->create([
                    "email"=>$data->email,
                    "username"=>$data->username,
                    "first_name"=>$data->first_name,
                    "last_name"=>$data->last_name,
                    "birth_day"=>$data->birth_day,
                    "birth_month"=>$data->birth_month,
                    "birth_year"=>$data->birth_year,
                    "password"=>$data->password
                ])->id;
                $this->parent->create([
                    "parent_id"=>$data->parent_id,
                    "student_id"=>$childId
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->student_id = $childId;
                $obj->message = "Child registered successfully";
                return $obj;
            } catch (\Throwable $th) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to complete registration, try again later";
                //$obj->raw_message = $th->getMessage();
                return $obj;
            }
        }
    }

}