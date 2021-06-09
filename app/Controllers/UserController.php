<?php
declare(strict_types=1);
namespace  App\Controllers;

use App\Interfaces\EmailSecretKeyInterface as Mail;
use App\Models\EmailOTP;
use App\Models\User;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Template\Builder\Builder;
use App\Template\Builder\Code;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;


class UserController implements Mail
{
    protected $user;
    protected $customResponse;
    protected $validator;
    protected $authController;
    public function __construct()
    {
        $this->emailOTP = new EmailOTP();
        $this->user = new User();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
        $this->authController = new AuthController();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }
    public function getUserID($request){
        return CustomRequestHandler::getUser($request);
    }
    public function GetUsers(Request $request, Response $response)
    {
       $responseMessage = $this->user->all();

       return $this->customResponse->is200Response($response,$responseMessage);
    }

    public function UserInfo($id){
        $userdata = $this->user->where(["id"=>$id])->get()[0];
        return $userdata;
    }

    public function GetUserInfo(Request $request, Response $response)
    {
        try {
            $email = $this->getUserID($request);
         
            $userdata = $this->user->where(["email"=>$email])->get(['email', 'first_name', 'last_name', 'other_name'
            , 'wallet', 'account_type', 'gender', 'two_fa', 'status', 'updated_at', 'created_at']);
            
            return $this->customResponse->is200Response($response,$userdata);
        
        } catch (\Throwable $th) {
            return $this->customResponse->is400Response($response,$th->getMessage());
         }
        
        }
    public function resetLink(Request $request, Response $response){
        try {
            $this->validator->validate($request,[
                
                 "email"=>v::notEmpty()->email(),
                 
             ]);

            if($this->validator->failed())
            {
                $responseMessage = $this->validator->errors;
                return $this->customResponse->is400Response($response,$responseMessage);
            }
           
            if($this->authController->EmailExist($this->getRequest($request,"email")))
            {
                $responseMessage = "Password resetting failed";
                $code = Code::generate(4);
               
                $emailtemplate = Builder::build(Mail::PasswordResetLink, ["code"=>$code]);
                $mail = Mailer::sendMail(Mail::Username, Mail::EmailFromName, $this->getRequest($request,"email"), Mail::PasswordResetSubject, $emailtemplate);
                
                if($mail && $this->emailOTP->create([
                    "email"=>$this->getRequest($request,"email"),
                    "otp"=>$code,
                    "status"=>1
                ])){
                    $responseMessage = "Password resetting was successful, please check your mail and reset the password!";
                    return $this->customResponse->is200Response($response,$responseMessage);
                }
                return $this->customResponse->is400Response($response,$responseMessage);

               
            }else{
                $responseMessage = "Email does not exist";
                return $this->customResponse->is400Response($response,$responseMessage);
            }
        } catch (\Throwable $th) {
            return $this->customResponse->is400Response($response,$th->getMessage());
        }
    }


    public function resetPassword(Request $request, Response $response){
        try {
            $this->validator->validate($request,[
                
                "code"=>v::notEmpty(),
                "password"=>v::notEmpty()
                
            ]);
            if($this->validator->failed())
            {
                $responseMessage = $this->validator->errors;
                return $this->customResponse->is400Response($response,$responseMessage);
            }


            $code = $this->getRequest($request, 'code');
            $emailOTPData  = $this->emailOTP->where(["otp"=>$code])->first(['email']);
           
           if(!empty($emailOTPData)){
            $password = $this->authController->hashPassword($this->getRequest($request,'password'));
            $update  = $this->user->where(["email"=>$emailOTPData['email']])->update(['password' => $password]);
           
            return $update ? $this->customResponse->is200Response($response,"Password changed successfully") : $this->customResponse->is400Response($response,"Cannot reset your passwrd at this time. Try again later!");
            

           }else{
            
            return $this->customResponse->is400Response($response,"The code you entered is not valid");
           }
            



            //code...
        } catch (\Throwable $th) {
            return $this->customResponse->is400Response($response,$th->getMessage());
        }
    }
       
}