<?php

namespace App\Http\Controllers\Setup\Email;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Setup\Email\EmailData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\Input;

class EmailController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function listEmails(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $id_entidad=$this->request->id_entidad?$this->request->id_entidad:$id_entidad;
            $result = EmailData::listEmails($id_entidad);

            if ($result) {           
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $result;
                $code = "200";
            }else{
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist.';
                $jResponse['data'] = [];
                $code = "202";
            }
        }        
        return response()->json($jResponse,$code);
    }
   
   
    public function showEmail($id_email){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $result = EmailData::showEmail($id_email);
            if ($result) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $result[0];
                $code = "200";
            }else{
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    public function showEmailAlias($id_entidad,$alias){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $result = EmailData::showEmailAlias($id_entidad,$alias);
            if ($result) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $result;
                $code = "200";
            }else{
                $jResponse['success'] = true;
                $jResponse['message'] = 'The item does not exist';
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function addEmail(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            $data = $this->request;
            $id_entidad=$data->id_entidad?$data->id_entidad:$id_entidad;
            $alias=$data->alias?$data->alias:null;
            try{
                $result = EmailData::showEmailAlias($id_entidad,$alias);
                if($result and $result->id_email){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Ya existe un registro con el alias ".$alias." en la entidad ".$id_entidad;
                    $jResponse['data'] = [];
                    $code = "202";
                }else{
                    $data = EmailData::addEmail($data,$id_entidad);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was inserted successfully";
                    $jResponse['data'] = $data;
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function updateEmail($id_email){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            $data = $this->request;
            try{
                $id_entidad=$data->id_entidad?$data->id_entidad:null;
                $alias=$data->alias?$data->alias:null;
                $result = EmailData::existEmailAlias($id_entidad,$alias,$id_email);
                if($result and $result->id_email){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Ya existe un registro con el alias ".$alias." en la entidad ".$id_entidad;
                    $jResponse['data'] = [];
                    $code = "202";
                }else{
                    $data = EmailData::updateEmail($data,$id_email);
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was updated successfully";
                    $jResponse['data'] = $data[0];
                    $code = "200";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function deleteEmail($id_email){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = EmailData::deleteEmail($id_email);
                $jResponse['success'] = true;
                $jResponse['message'] = "The item was deleted successfully";
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }
        return response()->json($jResponse,$code);
    }
}