<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 14/06/2017
 * Time: 8:18 PM
 */

namespace App\Http\Controllers\Sale\Operation;

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

    public function createOperation()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];

        $params = json_decode(file_get_contents("php://input"));
        $id = $params->data->id;
        $name = $params->data->name;
        $age = $params->data->age;

        $insert = DB::table('test')->insert(
            [
                'id' => $id,
                'name' => $name,
                'age' => $age
            ]
        );

        if($insert){
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        }

        return response()->json($jResponse);
    }

    public function updateOperation()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];

        $params = json_decode(file_get_contents("php://input"));
        $id = $params->data->id;
        $name = $params->data->name;
        $age = $params->data->age;

        $update = DB::table('test')
            ->where('id', $id)
            ->update([
                'name' => $name
            ]);

        if($update){
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        }

        return response()->json($jResponse);
    }

    public function deleteOperation()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];

        $params = json_decode(file_get_contents("php://input"));
        $id = $params->data->id;

        $delete = DB::table('test')->where('id', '=', $id)->delete();

        if ($delete) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        }

        return response()->json($jResponse);
    }

    public function runProcedure()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];

        $result1 = DB::executeProcedure('iudp_Fondo(:P_Modo,:P_ID_FONDO,:P_ID_PARENT,:P_NOMBRE)', [':P_Modo' => 0,':P_ID_FONDO' => '7',':P_ID_PARENT' => '0',':P_NOMBRE' => 'Fondo New']);
        dd($result1);

        //$query = $this->getPdo()->prepare('begin :result := ' . $sql . '; end;');

        //$query = "exec iudp_Fondo (0, 7, 0, 'Fondo New')";

        //$oQuery = DB::select($query);

//        $procedureName = 'iudp_Fondo';
//
//        $bindings = [
//            'P_Modo' => 0,
//            'P_ID_FONDO' => '9',
//            'P_ID_PARENT' => '0',
//            'P_NOMBRE' => 'Fondo New'
//        ];
//
//        $result = DB::executeProcedure($procedureName, $bindings);

//        $pdo = DB::getPdo();
//
//        $stmt = $pdo->prepare("begin iudp_Fondo(:P_Modo, :P_ID_FONDO, :P_ID_PARENT, :P_NOMBRE); end;");
//        $stmt->bindParam(':P_Modo', 0, \PDO::PARAM_INT);
//        $stmt->bindParam(':P_ID_FONDO', 7, \PDO::PARAM_INT);
//        $stmt->bindParam(':P_ID_PARENT', 0, \PDO::PARAM_INT);
//        $stmt->bindParam(':P_NOMBRE', 'Fondo New');
//        $stmt->execute();

        //dd($result);
        $result=true;

        if ($result) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = '';
        }

        return response()->json($jResponse);
    }
}