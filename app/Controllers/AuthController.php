<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\User;
use App\Interfaces\EmailSecretKeyInterface as Mail;
use App\Template\Builder\Code;
use App\Template\Builder\Builder;
use App\Models\AccountType;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class AuthController implements Mail
{
    protected $user;
    protected $accountType;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->user = new User();
        $this->accountType = new AccountType();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    public function Register(Request $request, Response $response)
    {
       $this->validator->validate($request,[
          "first_name"=>v::notEmpty(),
          "last_name"=>v::notEmpty(),
           "email"=>v::notEmpty()->email(),
           "password"=>v::notEmpty()
       ]);

       if($this->validator->failed())
       {
           $responseMessage =  $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }

       if($this->EmailExist($this->getRequest($request,"email")))
       {
           $responseMessage = "Email already exist";
           return $this->customResponse->is400Response($response,$responseMessage, true);
       }

       $passwordHash = $this->hashPassword($this->getRequest($request,'password'));
       $responseMessage = "";
       try {
        
        $code = Code::generate(90);
       
        $this->regUser(
        $this->getRequest($request,"email"),
        $this->getRequest($request,"first_name"),
        $this->getRequest($request,"last_name"),
        $this->getRequest($request,"type"),
        "REGULAR",
        "MALE",
        $passwordHash,
        "none",
        $this->getRequest($request, "birth_day"),
        $this->getRequest($request, "birth_month"),
        $this->getRequest($request, "birth_year"),
        $code
    );
       

         $emailtemplate = Builder::build(Mail::RegisterVerificationTemplate, ["code"=>$code, "url"=>Mail::SiteUrl, "logo"=>Mail::LogoUrl]);
         $mail = Mailer::sendMail(Mail::Username, Mail::EmailFromName, $this->getRequest($request,"email"), Mail::RegistrationVerificationSubject, $emailtemplate);
         
         $responseMessage = "new user created successfully";

       return $this->customResponse->is200Response($response,$responseMessage);
       } catch (\Throwable $th) {
        $responseMessage = "Internal server error!";
        return $this->customResponse->is400Response($response,$responseMessage, true);
       }
     

       
    }

    public function SocialLogin(Request $request, Response $response){
        try {
            $this->validator->validate($request,[
                "email"=>v::notEmpty()->email(),
                "first_name"=>v::notEmpty(),
                "last_name"=>v::notEmpty(),
                "isLogin"=>v::notEmpty(),
                
                "channel"=>v::notEmpty()
                
            ]);
            $allowedLogins = ["FACEBOOK", "GOOGLE", "LINKEDIN"];
            if($this->validator->failed())
            {
                $responseMessage = $this->validator->errors;
                return $this->customResponse->is400Response($response,$responseMessage);
            }
            $isLogin = $this->getRequest($request,"isLogin");
            $channel = $this->getRequest($request,"channel");
            $user = $this->getUserByEmail($this->getRequest($request,"email"));
            if($isLogin == "true" && $user->email !== null){
                if($user->channel == $channel && in_array($channel, $allowedLogins)){
                    $responseMessage = GenerateTokenController::generateToken(
                        $user->email
                    );
                    return $this->customResponse->is200Response($response,$responseMessage);
         
                }else{
                    $message = $user->channel == "REGULAR" ? "Authentication failed, sign in manually."  : "Please login with ".$user->channel;
                    return $this->customResponse->is400Response($response, $message, true);
                   
                }
            }else if($isLogin == "true" && $user->email == null){
                $message = "You have not signed up, Please sign up and try again";
                return $this->customResponse->is400Response($response, $message, true);
            }
            else if($isLogin == "false" && $user->email == null && in_array($channel, $allowedLogins)){
                
                $code = Code::generate(90);
               
               $create =  $this->regUser($this->getRequest($request, "email"),
                $this->getRequest($request,"first_name"),
                $this->getRequest($request,"last_name"),
                $this->getRequest($request, "type"),
                $this->getRequest($request, "channel")
            );
    
            if($create){
                $responseMessage = GenerateTokenController::generateToken(
                    $this->getRequest($request,"email")
                );
                return $this->customResponse->is200Response($response,$responseMessage);
            }
    
            }else {
                $message = $user->channel == "REGULAR" ? "Registration failed, sign in manually."  : "Please login with ".$user->channel;
                $this->customResponse->is400Response($response, $message, true);
            }
    
    
            return $this->customResponse->is400Response($response,"Authentication failed, sign in manually.", true);
     //code...
        } catch (\Throwable $th) {
            //throw $th;
            $responseMessage = "Internal server error!".$th->getMessage();
            return $this->customResponse->is400Response($response,$responseMessage, true);
        }

    }
    
    public function regUser(
        $email, 
        $first_name,
        $last_name,
        $account_type,
        $channel = "REGULAR",
        $gender = "none",
       
        $password = "none",
        $other_name = "none",
        $birth_day = "none",
        $birth_month = "none",
        $birth_year = "none",
        $verify_code = " ",
        $wallet = 0,
        $two_fa = 0,
        $break_period = 0,
        $status = 0,
        $rating = 0
    ){
         return $this->user->create([
            "first_name"=>$first_name,
            "last_name"=>$last_name,
            "other_name"=>$other_name,
             "email"=>$email,
             "password"=>$password,
             "birth_day"=>$birth_day,
             "birth_month"=>$birth_month,
             "birth_year"=>$birth_year,
             "wallet"=>$wallet,
             "account_type"=>$account_type,
             "gender"=> $gender,
             "two_fa"=> $two_fa,
             "break_period"=> $break_period,
             "status"=> $status,
             "rating"=> $rating,
             "verify_code"=> $verify_code,
             "channel"=>  $channel,
             
         ]);
    }
    // public function regsiterUser(
        
        
    // ){

   
   
    // }
    public function Login(Request $request,Response $response)
        {
            $this->validator->validate($request,[
                "email"=>v::notEmpty()->email(),
                "password"=>v::notEmpty()
            ]);

            if($this->validator->failed())
            {
                $responseMessage = $this->validator->errors;
                return $this->customResponse->is400Response($response,$responseMessage);
            }
            $verifyAccount = $this->verifyAccount(
                $this->getRequest($request,"password"),
                $this->getRequest($request,"email"));
                
            if($verifyAccount==false)
            {
                $responseMessage = "invalid username or password";
                return $this->customResponse->is400Response($response,$responseMessage);
            }
            $responseMessage = GenerateTokenController::generateToken(
                $this->getRequest($request,"email")
            );
            return $this->customResponse->is200Response($response,$responseMessage);
        }

    public function Verify(Request $request, Response $response){
        $this->validator->validate($request,[
            "code"=>v::notEmpty()
        ]);
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $check_code = $this->CodeExist($this->getRequest($request,"code"));

        if($check_code == true){
            $res = $this->user->where(['verify_code'=>$this->getRequest($request,"code")])->update(['status'=> 1]);
           if($res){
            $responseMessage = "Verification complete".$res;
            return $this->customResponse->is200Response($response,$responseMessage);
           }else{
            $responseMessage = "Could not complete your task!";
            return $this->customResponse->is400Response($response,$responseMessage);
           }
            
        }else{
            $responseMessage = "Invalid code!";
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    }
    public function AccountTypes(Request $request,Response $response)
    {
        $accountTypes = $this->getAccountTypes();
        return $this->customResponse->is200Response($response,$accountTypes);
    }

    public function verifyAccount($password,$email)
    {
        $count = $this->user->where(["email"=>$email])->count();
        if($count==0)
        {
            return false;
        }
        $user = $this->user->where(["email"=>$email])->first();
        $hashedPassword = $user->password;
        $verify = password_verify($password,$hashedPassword);
        if($verify==false)
        {
            return false;
        }
        return true;
    }

    public function addSubAdmin($data)
    {
        $obj = new stdClass();
        if($this->EmailExist($data->email))
       {
           $obj->status = 'error';
           $obj->message = "Email already exist";
           return $obj;
       }

       $passwordHash = $this->hashPassword($data->password);

       try {
        $user_id = $this->user->create([
            "first_name"=>$data->first_name,
            "last_name"=>$data->last_name,
            "other_name"=>$data->other_name,
             "email"=>$data->email,
             "password"=>$passwordHash,
             "account_type"=>$data->account_type
         ])->id;
            $responseMessage = "New user created successfully";
            $obj->status = 'success';
            $obj->user_id = $user_id;
            $obj->message = $responseMessage;
            return $obj;
       } catch (\Throwable $th) {
            $obj->status = 'error';
            $obj->message = "Unable to complete acccount registeration";
            return $obj;
       }
    }
    
    

    public function hashPassword($password)
    {
        return password_hash($password,PASSWORD_DEFAULT);
    }

    public function EmailExist($email)
    {
        $count = $this->user->where(['email'=>$email])->count();
        if($count==0)
        {
            return false;
        }
        return true;
    }

    public function getUserByEmail($email)
    {
        return $this->user::select('email', 'channel')->where(['email'=>$email])->first();
    }
    public function alterSubadmin($user_id,$action)
    {
        if ($action == 'suspend') {
            try {
                $this->user->find($user_id)->update([
                    "status"=>3
                ]);
                $obj = new stdClass();
                $obj->status = 'success';
                $obj->message = 'Action taken successfully';
                return $obj;
            } catch (\Throwable $th) {
                $obj = new stdClass();
                $obj->status = 'error';
                $obj->message = 'Unable to complete action';
                return $obj;
            }
        }elseif($action == 'delete'){
            try {
                $this->user->find($user_id)->update([
                    "status"=>9
                ]);
                $obj = new stdClass();
                $obj->status = 'success';
                $obj->message = 'Action taken successfully';
                return $obj;
            } catch (\Throwable $th) {
                $obj = new stdClass();
                $obj->status = 'error';
                $obj->message = 'Unable to complete action';
                return $obj;
            }
        }elseif ($action == 'recall') {
            try {
                $this->user->find($user_id)->update([
                    "status"=>1
                ]);
                $obj = new stdClass();
                $obj->status = 'success';
                $obj->message = 'Action taken successfully';
                return $obj;
            } catch (\Throwable $th) {
                $obj = new stdClass();
                $obj->status = 'error';
                $obj->message = 'Unable to complete action';
                return $obj;
            }
        }else{
            $obj = new stdClass();
            $obj->status = 'error';
            $obj->message = 'Invalid action';
            return $obj;
        }
    }

    public function CodeExist($code)
    {
        $count = $this->user->where(['verify_code'=>$code])->count();
        if($count==0)
        {
            return false;
        }
        return true;
    }

    public function getAccountTypes()
    {
        $accountTypes = $this->accountType->all();
        return $accountTypes;
    }
}