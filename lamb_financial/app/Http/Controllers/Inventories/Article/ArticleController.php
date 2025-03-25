<?php
namespace App\Http\Controllers\Inventory\Article;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Inventory\Article\ArticleData;
use Illuminate\Http\Request;

class ArticleController extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public function listArticle(){
        $jResponse = [
            'success' => false,
            'message' => 'ERROR - no data',
            'data' => array()
        ];        
        try{            
            $data = ArticleController::recursiveArticle("A");
            $jResponse['message'] = "SUCCES";
            $jResponse['success'] = true;
            $jResponse['data'] = ['items' => $data];
        }catch(Exception $e){
            dd($e);
        } 
        return response()->json($jResponse);
    }
    public function recursiveArticle($id_parent){
        $parent = [];            
        $data = ArticleData::listArticle($id_parent);
        foreach ($data as $key => $value){                                          
            $row = $this->recursiveArticle($value->id_articulo);         
            $parent[] = ['id' => $value->id_articulo, 'name' => $value->nombre,'children'=>$row];            
        }
        return $parent;
    }
    public function showArticle(){
        $params = json_decode(file_get_contents("php://input"));        
        $id_articulo = $params->data->id_articulo;
        $jResponse = [
            'success' => false,
            'message' => 'ERROR - no data',
            'data' => array()
        ];        
        try{            
            $data = ArticleData::showArticle($id_articulo);
            $jResponse['message'] = "SUCCES";
            $jResponse['success'] = true;
            $jResponse['data'] = ['items' => $data];
        }catch(Exception $e){
            dd($e);
        } 
        return response()->json($jResponse);
    }
}