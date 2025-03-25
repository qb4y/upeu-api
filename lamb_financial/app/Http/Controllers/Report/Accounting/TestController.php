<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Controllers\Report\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Data\TestData;
use App\LambUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PDF;
use DOMPDF;

class TestController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function balance()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];            
            if( $valida->active == 'SI'  ){   
            
                    $year = $params->data->year;
                    $month = $params->data->month;
                    $entity = $params->data->entity;

                $bindings = [
                    'p_id_entidad' => $entity,
                    'p_id_anho' => $year,
                    'p_id_mes' => $month
                ];

                $lstData_1 = TestData::superProc('spc_balance', $bindings);
                $lstData_2 = TestData::superProc('spc_balance_2', $bindings);
                $lstData_activo = TestData::superProc('spc_balance_activo', $bindings);
                $lstData_res_gastos = TestData::superProc('spc_balance_res_gastos', $bindings);
                $lstData_res_ingresos = TestData::superProc('spc_balance_res_ingresos', $bindings);
                $lstData_resultados = TestData::superProc('spc_balance_resultados', $bindings);
                $lstData_totales = TestData::superProc('spc_balance_totales', $bindings);

                $jResponse['data'] = ['balance_1' => $lstData_1,
                    'balance_2' => $lstData_2,
                    'balance_activo' => $lstData_activo,
                    'balance_res_gastos' => $lstData_res_gastos,
                    'balance_res_ingresos' => $lstData_res_ingresos,
                    'balance_resultados' => $lstData_resultados,
                    'balance_totales' => $lstData_totales
                ];
            }
        }
        return response()->json($jResponse);
    }


    public function balance_1()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');

        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];            
            if( $valida->active == 'SI'  ){   

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $option = $params->data->option;
            
                if ($option == 'EN') {
                    $bindings = [
                        'p_id_entidad' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];
                    $lstData_1 = TestData::superProc('spc_balance', $bindings);

                    $jResponse['data'] = ['items' => $lstData_1];

                    return response()->json($jResponse);
                } else {
                $bindings = [
                    'p_id_empresa' => $entity,
                    'p_id_anho' => $year,
                    'p_id_mes' => $month
                ];
                $lstData_em = TestData::superProc('spc_balance_em', $bindings);

                $jResponse['data'] = ['items' => $lstData_em];

               }
           }
        }
        return response()->json($jResponse);
    }


    public function balance_2()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];            
            if( $valida->active == 'SI'  ){   

                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $option = $params->data->option;
                if ($option == 'EN') {
                $bindings = [
                    'p_id_entidad' => $entity,
                    'p_id_anho' => $year,
                    'p_id_mes' => $month
                ];

                $lstData_1 = TestData::superProc('spc_balance_2', $bindings);

                $jResponse['data'] = ['items' => $lstData_1];
                return response()->json($jResponse);}
                else {

                    $bindings = [
                        'p_id_empresa' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];
                    $lstData_em = TestData::superProc('spc_balance_em', $bindings);

                    $jResponse['data'] = ['items' => $lstData_em];
                }
            }
        }
        return response()->json($jResponse);
    }

    public function balance_activo()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];            
            if( $valida->active == 'SI'  ){   
            
                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $option = $params->data->option;
                if($option=='EN'){
                    $bindings = [
                        'p_id_entidad' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];

                    $lstData_1 = TestData::superProc('spc_balance_activo', $bindings);

                    $jResponse['data'] = ['items' => $lstData_1];
                    return response()->json($jResponse);

                }else{
                    $bindings = [
                        'p_id_empresa' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];

                    $lstData_em = TestData::superProc('spc_balance_activo_em', $bindings);

                    $jResponse['data'] = ['items' => $lstData_em];
                    return response()->json($jResponse);
                }
            }
        }
    }

    public function balance_res_gastos()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];            
            if( $valida->active == 'SI'  ){   
            
                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $option = $params->data->option;
                if($option=='EN'){
                    $bindings = [
                        'p_id_entidad' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];

                    $lstData_em = TestData::superProc('spc_balance_res_gastos_em', $bindings);

                    $jResponse['data'] = ['items' => $lstData_em];
                    return response()->json($jResponse);
                }else{
                    $bindings = [
                        'p_id_empresa' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];

                    $lstData_em = TestData::superProc('spc_balance_res_gastos_em', $bindings);

                    $jResponse['data'] = ['items' => $lstData_em];
                }
            }
        }
    }

    public function balance_res_ingresos()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];            
            if( $valida->active == 'SI'  ){   
            
                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $option = $params->data->option;
                
                if($option=='EN'){
                    $bindings = [
                        'p_id_entidad' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];

                    $lstData_1 = TestData::superProc('spc_balance_res_ingresos', $bindings);

                    $jResponse['data'] = ['items' => $lstData_1];
                    return response()->json($jResponse);
                }else{
                    $bindings = [
                        'p_id_empresa' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];

                    $lstData_em = TestData::superProc('spc_balance_res_ingresos_em', $bindings);

                    $jResponse['data'] = ['items' => $lstData_em];
                }
            }
        }
    }
    //Revisar
    public function balance_resultados()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];            
            if( $valida->active == 'SI'  ){   
            
                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $option = $params->data->option;
        
                if($option=='EN'){
                        $bindings = [
                            'p_id_entidad' => $entity,
                            'p_id_anho' => $year,
                            'p_id_mes' => $month
                        ];

                        $lstData_1 = TestData::superProc('spc_balance_resultados_em', $bindings);

                        $jResponse['data'] = ['items' => $lstData_1];
                    return response()->json($jResponse);
                }else{
                    $bindings = [
                        'p_id_empresa' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];

                    $lstData_em = TestData::superProc('spc_balance_resultados_em', $bindings);

                    $jResponse['data'] = ['items' => $lstData_em];
                }
            }
        }

    }

    public function balance_totales()
    {
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => []
        ];

        $token = $this->request->header('Authorization');
        $params = json_decode(file_get_contents("php://input"));
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
            'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $valida = $result[0];            
            if( $valida->active == 'SI'  ){   
            
                $year = $params->data->year;
                $month = $params->data->month;
                $entity = $params->data->entity;
                $option = $params->data->option;
                if($option=='EN'){
                    $bindings = [
                        'p_id_entidad' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];

                    $lstData_1 = TestData::superProc('spc_balance_totales', $bindings);

                    $jResponse['data'] = ['items' => $lstData_1];
                    return response()->json($jResponse);
                }else{
                    $bindings = [
                        'p_id_empresa' => $entity,
                        'p_id_anho' => $year,
                        'p_id_mes' => $month
                    ];


                    $lstData_em = TestData::superProc('spc_balance_totales_em', $bindings);

                    $jResponse['data'] = ['items' => $lstData_em];

                }
            }
        }
        return response()->json($jResponse);
    }
}
