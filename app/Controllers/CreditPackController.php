<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\CreditPack;
use App\Models\Subscription;
use App\Models\CreditTransfer;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class CreditPackController
{
    protected $creditPack;
    protected $subscription;
    protected $creditTransfer;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->creditPack = new CreditPack();
        $this->subscription = new Subscription();
        $this->creditTransfer = new CreditTransfer();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }


    
    public function GetCreditPacks(Request $request, Response $response)
    {
        $packs = $this->creditPacks();
        return $this->customResponse->is200Response($response,$packs);
    }

    public function CheckUserSub(Request $request, Response $response)
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
        $info = $this->checkSub($user_id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function CheckDueSubscriptions(Request $request, Response $response)
    {
        $due_subs = $this->dueSubs();
        return $this->customResponse->is200Response($response,$due_subs);
    }
    

    public function GetPackInfo(Request $request, Response $response)
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
        $info = $this->creditPackInfo($id);
        return $this->customResponse->is200Response($response,$info);
    }


    public function CreateCreditPack(Request $request, Response $response){

        $this->validator->validate($request,[
            "package"=>v::notEmpty(),
            "credit"=>v::notEmpty(),
            "validity"=>v::notEmpty(),
            "is_transferable"=>v::notEmpty(),
            "is_all_subjects"=>v::notEmpty(),
            "exams"=>v::notEmpty(),
            "price"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->package = $this->getRequest($request,'package');
        $obj->credit = $this->getRequest($request,'credit');
        $obj->validity = $this->getRequest($request,'validity');
        $obj->is_transferable = $this->getRequest($request,'is_transferable');
        $obj->is_all_subjects = $this->getRequest($request,'is_all_subjects');
        $obj->exams = $this->getRequest($request,'exams');
        $obj->price = $this->getRequest($request,'price');
        
        $create = $this->createPack($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function EditCreditPack(Request $request, Response $response){

        $this->validator->validate($request,[
            "id"=>v::notEmpty(),
            "package"=>v::notEmpty(),
            "credit"=>v::notEmpty(),
            "validity"=>v::notEmpty(),
            "is_transferable"=>v::notEmpty(),
            "is_all_subjects"=>v::notEmpty(),
            "exams"=>v::notEmpty(),
            "price"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->id = $this->getRequest($request,'id');
        $obj->package = $this->getRequest($request,'package');
        $obj->credit = $this->getRequest($request,'credit');
        $obj->validity = $this->getRequest($request,'validity');
        $obj->is_transferable = $this->getRequest($request,'is_transferable');
        $obj->is_all_subjects = $this->getRequest($request,'is_all_subjects');
        $obj->exams = $this->getRequest($request,'exams');
        $obj->price = $this->getRequest($request,'price');
        
        $create = $this->editPack($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function NewSubscription(Request $request, Response $response){

        $this->validator->validate($request,[
            "user_id"=>v::notEmpty(),
            "package_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->user_id = $this->getRequest($request,'user_id');
        $obj->package_id = $this->getRequest($request,'package_id');
        
        $create = $this->saveSubscription($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function DeleteCreditPack(Request $request, Response $response){

        $this->validator->validate($request,[
            "id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $id = $this->getRequest($request,'id');
        
        $create = $this->removePack($id);
        return $this->customResponse->is200Response($response,$create);
    }

    public function TransferUnusedCredit(Request $request, Response $response){

        $this->validator->validate($request,[
            "sender_id"=>v::notEmpty(),
            "receiver_id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $sender_id = $this->getRequest($request,'sender_id');
        $receiver_id = $this->getRequest($request,'receiver_id');
        
        $transfer = $this->transferCredit($sender_id,$receiver_id);
        return $this->customResponse->is200Response($response,$transfer);
    }

    public function createPack($data){
        $check  = $this->creditPack->where(["package"=>$data->package])->count();
        if ($check > 0) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Credit pack already exist";
            return $obj;
        }else{
            try {
                $this->creditPack->create([
                    "package"=>$data->package,
                    "credit"=>$data->credit,
                    "validity"=>$data->validity,
                    "is_transferable"=>$data->is_transferable,
                    "is_all_subjects"=>$data->is_all_subjects,
                    "exams"=>$data->exams,
                    "price"=>$data->price
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Credit pack saved successfully";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to create pack";
                return $obj;
            }
        } 
    }


    public function saveSubscription($data){
        $checkSub = $this->checkSub($data->user_id);
        if ($checkSub->status == 'success') {
            $extraCredit = $this->subscriptionInfo($checkSub->package_id)->credit;
            $this->subscription->find($checkSub->package_id)->update(["status"=>0]);
        }else{
            $extraCredit = 0;
        }
        try {
            $info = $this->creditPackInfo($data->package_id);
            $due_date = strtotime('+'.$info->validity);
            $this->subscription->create([
                "user_id"=>$data->user_id,
                "package_id"=>$data->package_id,
                "due_date"=>$due_date,
                "credit"=>($info->credit+$extraCredit)
            ]);
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Subscription successful";
            return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to save pack";
            return $obj;
        }
    }

    public function editPack($data){
        try {
            $this->creditPack->find($data->id)->update([
                "package"=>$data->package,
                "credit"=>$data->credit,
                "validity"=>$data->validity,
                "is_transferable"=>$data->is_transferable,
                "is_all_subjects"=>$data->is_all_subjects,
                "exams"=>$data->exams,
                "price"=>$data->price
            ]);
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Credit pack updated successfully";
            return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to update pack";
            return $obj;
        }
    }

    public function creditPacks(){
        $packs = $this->creditPack->where(["status"=>1])->get();
        return $packs;
    }

    public function creditPackInfo($id){
        $info = $this->creditPack->where(["id"=>$id])->get()[0];
        return $info;
    }

    public function checkSub($user_id)
    {
        $obj = new stdClass();
        $query = $this->subscription->where(["user_id"=>$user_id,"status"=>1]);
        $check = $query->count();
        if ($check < 1) {
            $obj->status = 'error';
            $obj->message = 'No active subscription';
        }else{
            $data = $query->get()[0];
            $obj->status = 'success';
            $obj->message = 'Active subscritpion found';
            $obj->package_id = $data->id;
            $obj->subInfo = $data;
        }
        return $obj;
    }

    public function subscriptionInfo($id)
    {
        $sub = $this->subscription->where(["id"=>$id])->get()[0];
        $obj = new stdClass();
        $obj->info = $sub;
        $obj->packInfo = $this->creditPackInfo($sub->package_id);
        return $obj;
    }

    public function dueSubs()
    {
        $date = time();
        $expired = $this->subscription->where('due_date', '<=' , $date)->get();
        foreach ($expired as $key => $sub) {
            $this->subscription->find($sub->id)->update(["status"=>0]);
        }
        return "Due subscriptions deactivated";
    }

    public function removePack($id)
    {
        try {
            $this->creditPack->find($id)->update([
                "status"=>0
            ]);
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Credit pack deleted successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to delete pack";
            return $obj;
        }
    }

    public function transferCredit($sender_id,$receiver_id)
    {
        $recSubInfo = $this->checkSub($receiver_id);
        $senSubInfo = $this->checkSub($sender_id);
        if ($senSubInfo->status == 'error') {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Sorry, you do not have an active subscription";
            return $obj;
        }elseif($recSubInfo->status == 'error'){
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Sorry, the recipient does not have an active subscription";
            return $obj;
        }else{
            try {
                $this->subscription->find($senSubInfo->package_id)->update([
                    "credit"=>0,
                    "status"=>0
                ]);
                
                $this->subscription->find($recSubInfo->package_id)->update([
                    "credit"=>$senSubInfo->data->credit + $recSubInfo->data->credit
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Credit transfer successful";
                return $obj;
            } catch (\Throwable $th) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to transfer credit";
                return $obj;
            }
        }
    }


}