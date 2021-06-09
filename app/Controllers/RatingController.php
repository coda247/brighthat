<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\Rating;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class RatingController
{
    protected $rating;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->rating = new Rating();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    
    public function GetClassRating(Request $request, Response $response)
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
        $rating = $this->classRating($topic_id);
        return $this->customResponse->is200Response($response,$rating);
    }


    public function GetTeacherRating(Request $request, Response $response)
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
        $rating = $this->teacherRating($teacher_id);
        return $this->customResponse->is200Response($response,$rating);
    }


    public function NewRating(Request $request, Response $response){

        $this->validator->validate($request,[
            "topic_id"=>v::notEmpty(),
            "teacher_id"=>v::notEmpty(),
            "client_id"=>v::notEmpty(),
            "rating"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->topic_id = $this->getRequest($request,'topic_id');
        $obj->teacher_id = $this->getRequest($request,'teacher_id');
        $obj->client_id = $this->getRequest($request,'client_id');
        $obj->rating = $this->getRequest($request,'rating');
        
        $create = $this->saveRating($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function saveRating($data){
        $check  = $this->rating->where(["topic_id"=>$data->topic_id,"client_id"=>$data->client_id])->count();
        if ($check > 0) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Rated already";
            return $obj;
        }else{
            try {
                $this->rating->create([
                    "topic_id"=>$data->topic_id,
                    "teacher_id"=>$data->teacher_id,
                    "client_id"=>$data->client_id,
                    "rating"=>$data->rating
                ]);
                $obj = new stdClass();
                $obj->status = "success";
                $obj->message = "Rating saved successfully";
                return $obj;
            } catch (\Throwable $e) {
                $obj = new stdClass();
                $obj->status = "error";
                $obj->message = "Unable to save rating";
                return $obj;
            }
        } 
    }

    public function teacherRating($teacher_id){
        $ratings = $this->rating::selectRaw('count(*) no_of_rating, AVG(rating) total_rating')->where(["teacher_id"=>$teacher_id])->groupBy("teacher_id")->get()[0];
        return $ratings;
    }

    public function classRating($topic_id){
        $ratings = $this->rating::selectRaw('count(*) no_of_rating, AVG(rating) total_rating')->where(["topic_id"=>$topic_id])->groupBy("topic_id")->get()[0];
        return $ratings;
    }

}