<?php
declare(strict_types=1);
namespace  App\Controllers;
use App\Models\ClassCategory;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use App\Validation\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface AS Response;
use Respect\Validation\Validator as v;
use stdClass;

class ClassCategoryController
{
    protected $category;
    protected $customResponse;
    protected $validator;

    public function __construct()
    {
        $this->category = new ClassCategory();
        $this->customResponse = new CustomResponse();
        $this->validator = new Validator();
    }

    public function getRequest($request,$field){
        return CustomRequestHandler::getParam($request,$field);
    }

    public function GetClassCategories(Request $request, Response $response)
    {
        $categories = $this->classCategories();
        return $this->customResponse->is200Response($response,$categories);
    }

    public function GetCategoryInfo(Request $request, Response $response)
    {
        $id = $this->getRequest($request,'id');
        $info = $this->categoryInfo($id);
        return $this->customResponse->is200Response($response,$info);
    }

    public function NewClassCategory(Request $request, Response $response){

        $this->validator->validate($request,[
            "category_name"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->category_name = $this->getRequest($request,'category_name');
        
        $create = $this->createNewCategory($obj);
        return $this->customResponse->is200Response($response,$create);
    }

    public function EditCategory(Request $request, Response $response){

        $this->validator->validate($request,[
             "id"=>v::notEmpty(),
             "category_name"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $obj = new stdClass();
        $obj->id = $this->getRequest($request,'id');
        $obj->category_name = $this->getRequest($request,'category_name');
        
        $update = $this->updateCategory($obj);
        return $this->customResponse->is200Response($response,$update);
    }

    public function DeleteClassCategory(Request $request, Response $response){

        $this->validator->validate($request,[
             "id"=>v::notEmpty()
         ]);

         if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }
        $id = $this->getRequest($request,'id');
        
        $update = $this->deleteCategory($id);
        return $this->customResponse->is200Response($response,$update);
    }

    public function createNewCategory($data){
        try {
            $this->category->create([
                "category_name"=>$data->category_name
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "New category created successfully";
             return $obj;
        } catch (\Throwable $e) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to create category";
            return $obj;
        }
    }

    public function categoryInfo($category_id){
        $info = $this->category->where(["id"=>$category_id])->get()[0];
        return $info;
    }

    public function classCategories(){
        $categories = $this->category->all(); 
        return $categories;
    }

    public function updateCategory($data){
        try {
            $this->category->find($data->id)->update([
                "category_name"=>$data->category_name,
             ]);
             $obj = new stdClass();
             $obj->status = "success";
             $obj->message = "Category updated successfully";
             return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to update category";
            return $obj;
        }
        
    }

    public function deleteCategory($id){
        try {
            $this->category->find($id)->delete();
            $obj = new stdClass();
            $obj->status = "success";
            $obj->message = "Category deleted successfully";
            return $obj;
        } catch (\Throwable $th) {
            $obj = new stdClass();
            $obj->status = "error";
            $obj->message = "Failed to delete category";
            return $obj;
        }
        
    }

}