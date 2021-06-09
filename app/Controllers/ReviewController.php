<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\Review;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class ReviewController
{
    protected $review;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->review = new Review();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    
    public function GetClassReviews(Request $request, Response $response)
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
        $reviews = $this->classReviews($topic_id);
        return $this->customResponse->is200Response($response,$reviews);
    }

    public function GetGeneralReviews(Request $request, Response $response)
    {
        $reviews = $this->generalReviews();
        return $this->customResponse->is200Response($response,$reviews);
    }


    public function GetTeacherReviews(Request $request, Response $response)
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
        $reviews = $this->teacherReviews($teacher_id);
        return $this->customResponse->is200Response($response,$reviews);
    }


    public function NewReview(Request $request, Response $response){

        $this->validator->validate($request,[
            "topic_id"=>v::notEmpty(),
            "teacher_id"=>v::notEmpty(),
            "client_id"=>v::notEmpty(),
            "review"=>v::notEmpty()
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
        $obj->review = $this->getRequest($request,'review');
        
        $create = $this->saveReview($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function saveReview($data){
        //$check  = $this->review->where(["class_sub_id"=>$data->class_sub_id,"client_id"=>$data->client_id])->count();
        try {
            $this->review->create([
                "topic_id"=>$data->topic_id,
                "teacher_id"=>$data->teacher_id,
                "client_id"=>$data->client_id,
                "review"=>$data->review
            ]);
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Review saved successfully";
            return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Unable to save review";
            return $obj;
        }
    }

    public function teacherReviews($teacher_id){
        $reviews = $this->review::selectRaw('*')->where(["teacher_id"=>$teacher_id])->join('users', 'users.id', '=', 'reviews.client_id')->get();
        return $reviews;
    }

    public function teacherReviewsCount($teacher_id){
        $reviews = $this->review->where(["teacher_id"=>$teacher_id])->count();
        return $reviews;
    }

    public function classReviews($topic_id){
        //$reviews = $this->review->where(["class_sub_id"=>$class_sub_id])->get();
        $reviews = $this->review::selectRaw('*')->where(["topic_id"=>$topic_id])->join('users', 'users.id', '=', 'reviews.client_id')->get();
        return $reviews;
    }
    
    
    public function generalReviews(){
        //$reviews = $this->review->where(["status"=>1])->inRandomOrder()->limit(25)->get();
        $reviews = $this->review::selectRaw('*')->join('users', 'users.id', '=', 'reviews.client_id')->inRandomOrder()->limit(25)->get();
        return $reviews;
    }

}