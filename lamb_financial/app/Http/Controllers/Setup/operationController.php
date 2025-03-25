<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 14/06/2017
 * Time: 8:18 PM
 */

namespace App\Http\Controllers\Setup;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\SetupData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class operationController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function showRol($id){
        /*$id_rol = "";
        $nombre = "";
        $estado = "";*/
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];
        $shwRol = SetupData::get_rol($id);
        if ($shwRol) {
            //dd(count($shwRol));
            /*foreach($shwRol as  $key => $row){
                $id_rol = $row->id_rol;
                $nombre = $row->nombre;
                $estado = $row->estado;
            } */           
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $shwRol];
            //$jResponse = { "name":"John", "age":30, "car":null };
           /* $jResponse['data']:{'items':$listRol};
            
            $jResponse['id'] = $id_rol;
            
            
            $jResponse['nombre'] = $nombre;
            $jResponse['estado'] = $estado;*/
        }
        return response()->json($jResponse);
    }
    public function listRol(){
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];     
        $listRol = SetupData::list_rol();
        if ($listRol) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $listRol];
            
        }
        return response()->json($jResponse);
    }

    public function addRol()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        $params = json_decode(file_get_contents("php://input"));
        
        $name = $params->data->name;
        $state = $params->data->state;
        try{
            $insert = DB::table('LAMB_ROL')->insert(
                array('NOMBRE' => $name, 'ESTADO' => $state)
            );            
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ''; 
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = 'ERROR';
            $jResponse['message_error'] = 'DATO EXISTE';
            $jResponse['data'] = '';    
        }
        return response()->json($jResponse);       
    }
    public function updateRol()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR AL EDITAR',
            'data' => array()
        ];

        $params = json_decode(file_get_contents("php://input"));
        $id = $params->data->id;
        $name = $params->data->name;
        $state = $params->data->state;
        try{
            DB::table('LAMB_ROL')
                ->where('ID_ROL', $id)
                ->update([
                    'NOMBRE' => $name,
                    'ESTADO' => $state
                ]);
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = 'ERROR AL EDITAR';
            $jResponse['data'] = ''; 
        }
        return response()->json($jResponse);
    }
    public function deleteRol()
    {
        $jResponse = [
            'success' => false,
            'message' => 'DELETE',
            'data' => array()
        ];

        /*$params = json_decode(file_get_contents("php://input"));
        $id = $params->data->id;

        $delete = DB::table('test')->where('id', '=', $id)->delete();

        if ($delete) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        }*/

        return response()->json($jResponse);
    }

}