<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\PriviledgeAssignment;
use App\Models\UserPrivilege;
use App\Controllers\AuthController;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class PriviledgeController
{
    protected $priviledgeModel;
    protected $assignmentModel;
    protected $authController;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->authController = new AuthController();
        $this->assignmentModel = new PriviledgeAssignment();
        $this->priviledgeModel = new UserPrivilege();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    
    public function GetPriviledges(Request $request, Response $response)
    {
        $exams = $this->privileges();
        return $this->customResponse->is200Response($response,$exams);
    }

    public function CreateSubAdmin(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "first_name"=>v::notEmpty(),
            "last_name"=>v::notEmpty(),
             "email"=>v::notEmpty()->email(),
             "password"=>v::notEmpty()
         ]);
  
         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }
         $obj = new stdClass();
         $obj->first_name = $this->getRequest($request,"first_name");
         $obj->last_name = $this->getRequest($request,"last_name");
         $obj->other_name = $this->getRequest($request,"other_name");
         $obj->email = $this->getRequest($request,"email");
         $obj->password = $this->getRequest($request,"password");
         $obj->privileges = $this->getRequest($request,"privileges");
         $obj->account_type = 2;
         $createAccount = $this->authController->addSubAdmin($obj);
         if ($createAccount->status == 'error') {
            return $this->customResponse->is200Response($response,$createAccount); 
         }else{
            $arr = explode(',', $obj->privileges);
            $user_id = $createAccount->user_id;
            //$sendEmail = $this->email->sendEmail();
            foreach ($arr as $key => $value) {
                $privilege_id = $value;
                $this->assignPrivilege($user_id,$privilege_id,1);
            }
            return $this->customResponse->is200Response($response,$createAccount); 
         }
    }

    public function GetUserPriviledges(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "user_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $user_id = $this->getRequest($request,'user_id');
        $questions = $this->userPrivileges($user_id);
        return $this->customResponse->is200Response($response,$questions);
    }

    public function AssignUserPrivilege(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "user_id"=>v::notEmpty(),
            "privilege_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $user_id = $this->getRequest($request,'user_id');
        $privilege_id = $this->getRequest($request,'privilege_id');
        $questions = $this->assignPrivilege($user_id,$privilege_id,1);
        return $this->customResponse->is200Response($response,$questions);
    }


    public function SubAdminAction(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "user_id"=>v::notEmpty(),
            "action"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $user_id = $this->getRequest($request,'user_id');
        $action = $this->getRequest($request,'action');
        $alter = $this->authController->alterSubadmin($user_id,$action);
        return $this->customResponse->is200Response($response,$alter);
    }


    public function CheckUserPrivilege(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "user_id"=>v::notEmpty(),
            "privilege_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $user_id = $this->getRequest($request,'user_id');
        $privilege_id = $this->getRequest($request,'privilege_id');
        $check = $this->checkPrivilege($user_id,$privilege_id);
        return $this->customResponse->is200Response($response,$check);
    }

    public function UnAssignPrivilege(Request $request, Response $response)
    {
        $this->validator->validate($request,[
            "user_id"=>v::notEmpty(),
            "privilege_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $user_id = $this->getRequest($request,'user_id');
        $privilege_id = $this->getRequest($request,'privilege_id');
        $questions = $this->assignPrivilege($user_id,$privilege_id,0);
        return $this->customResponse->is200Response($response,$questions);

    }

    public function PrivilegeInfo(Request $request, Response $response)
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
        $info = $this->privInfo($id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function privileges(){
        $privileges = $this->priviledgeModel->all();
        return $privileges;
    }

    public function userPrivileges($user_id)
    {
        $arr = array();
        $privs = $this->assignmentModel->where(["user_id"=>$user_id,"status"=>1])->get();
        foreach ($privs as $key => $priv) {
            $obj = new stdClass();
            $obj->id = $priv->id;
            $obj->privilege_id = $priv->privilege_id;
            $obj->user_id = $priv->user_id;
            $obj->priv_data = $this->privInfo($priv->privilege_id);
            $arr[] = $obj;
        }
        return $arr;
    }

    public function privInfo($id)
    {
        $info = $this->priviledgeModel->where(["id"=>$id])->get()[0];
        return $info;
    }

    public function assignPrivilege($user_id,$privilege_id,$status){
        $check =  $this->assignmentModel->where(["privilege_id"=>$privilege_id,"user_id"=>$user_id])->count();
        if ($check < 1) {
            try {
                $this->assignmentModel->create([
                    "user_id"=>$user_id,
                    "privilege_id"=>$privilege_id
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Privilege assigned successfully";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to assign ptivilege";
                return $obj;
            } 
        }else{
            try {
                $this->assignmentModel->where(["user_id"=>$user_id,"privilege_id"=>$privilege_id])->update([
                    "status"=>$status
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Action successfull";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to complete action";
                return $obj;
            } 
        }
        
    }


    public function checkPrivilege($user_id,$privilege_id)
    {
        $obj = new stdClass();
        $check = $this->assignmentModel->where(["user_id"=>$user_id,"privilege_id"=>$privilege_id,"status"=>1])->count();
        if ($check < 1) {
            $obj->status = 'error';
            $obj->message = 'Privilege not found for user';
            $obj->user_id = $user_id;
            $obj->privilege_id = $privilege_id;
            return $obj;
        }else{
            $obj->status = 'success';
            $obj->message = 'Privilege found for user';
            $obj->user_id = $user_id;
            $obj->privilege_id = $privilege_id;
            return $obj;
        }
    }

}