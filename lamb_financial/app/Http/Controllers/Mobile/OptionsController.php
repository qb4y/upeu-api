<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Data\MobileData;
use App\LambUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PDF;
use DOMPDF;
use App\Http\Data\GlobalMethods;

class OptionsController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function login()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - login'
        ];

        
        return response()->json($jResponse);
        
    }
    public function version()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - User Data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $bindings = [
            'p_id_app' => 'pe.adventistas.upnfinanciero.app'
        ];
        $list = MobileData::superProc('spc_mobile_version', $bindings);
        
        if(count($list)==1){
            $jResponse['data'] = ['item' => $list[0]];
        }else{
            $jResponse = [
                'success' => false,
                'message' => 'No Data',
                'data' => null
            ];            
        }
        return response()->json($jResponse);
    }

    public function version_validate()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - User Data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }
        $app_id = $params->data->app_id;
        $version = $params->data->version;

        $bindings = [
            'p_id_app' => $app_id,
            'p_version' => $version
        ];
        $list = MobileData::superProc('spc_mobile_version_validate', $bindings);
        
        if(count($list)==1){
            $jResponse['data'] = ['item' => $list[0]];
        }else{
            $jResponse = [
                'success' => false,
                'message' => 'No Data',
                'data' => null
            ];            
        }
        return response()->json($jResponse);
    }                

    public function forget_password()
    {
        $jResponse = [
            'success' => true,
            'message' => 'Su solicitud fue atendida satisfactoriamente, revise su correo electrónico o contáctese con la oficina de GTH.'
        ];
        $jResponse['data'] = ['dato' => "", 'items' => ""];
        return response()->json($jResponse);
    }
    public function user_data()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - User Data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $person_id = $params->data->person_id;
        

        $bindings = [
            'p_id_persona' => $person_id
        ];

        $list = MobileData::superProc('spc_mobile_user_data', $bindings);
        
        if(count($list)==1){
            $jResponse['data'] = ['user' => $list[0]];
        }else{
            $jResponse = [
                'success' => false,
                'message' => 'No Data',
                'user' => null
            ];            
        }
        return response()->json($jResponse);
    }
    public function st_data()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $cta_cte = $params->data->cta_cte;

        $list = MobileData::st_proc('spc_account_statement_data',$entity,$year,$month, $id_persona, $cta_cte); 
        
        if(count($list)==1){
            $jResponse['success'] = true;
            $jResponse['data'] = ['item' => $list[0]];
        }
        return response()->json($jResponse);
    }
    public function st_data_items()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_items'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }
        
        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $cta_cte = $params->data->cta_cte;

        $list = MobileData::st_proc('spc_account_statement_salary',$entity,$year,$month, $id_persona, $cta_cte); 
        
        if(count($list)==1){
            $jResponse['success'] = true;
            $jResponse['data'] = ['item' => $list[0]];
        }
        return response()->json($jResponse);
    }
    public function st_data_details()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }
        
        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_cuentaaasi = $params->data->id_cuentaaasi;
        $cta_cte = $params->data->cta_cte;
        
        $list = MobileData::st_data_details($year, $month, $entity, $id_cuentaaasi,$cta_cte);
        $list_total = MobileData::st_data_details_total($year, $month, $entity, $id_cuentaaasi,$cta_cte);
        $jResponse['data'] = ['items' => $list];
        if(count($list_total)==1){
            $jResponse['data'] = ['items' => $list, 'total' => $list_total[0]];
        }
        return response()->json($jResponse);
    }
    
    
    //  BOleta de pagos
    public function st_data_salary_ingresos()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        
        $list = MobileData::st_proc_data_salary('spc_sta_salary_ingresos',$year, $month, $entity, $id_persona,'');
        $list_total = MobileData::st_proc_data_salary('spc_sta_salary_ingresos_total',$year, $month, $entity, $id_persona,'');
        // $list_a = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo',$year, $month, $entity, $id_persona,'100');
        // $list_a_total = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo_total',$year, $month, $entity, $id_persona,'100');
        // $list_b = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo',$year, $month, $entity, $id_persona,'200');
        // $list_b_total = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo_total',$year, $month, $entity, $id_persona,'200');

        $jResponse['data'] = [
                                'items' => $list, 'total' => $list_total[0],
                                // 'ayudas' => $list_a, 'total_ayudas' => $list_a_total[0],
                                // 'viajes' => $list_b, 'total_viajes' => $list_b_total[0]
                                ];
        return response()->json($jResponse);
    }
    public function st_data_salary_descuentos()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $id_cta_cte = $params->data->id_cta_cte;
        
        $list = MobileData::st_proc_data_salary('spc_sta_salary_desc',$year, $month, $entity, $id_persona,'');
        $list_total = MobileData::st_proc_data_salary('spc_sta_salary_desc_total',$year, $month, $entity, $id_persona,'');
        
        $list_adelantos = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta',$year, $month, $entity, $id_cta_cte, '1135001');
        $list_adelantos_total = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta_total',$year, $month, $entity, $id_cta_cte, '1135001');
        // $list_ayudas = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta',$year, $month, $entity, $id_cta_cte, '1135005');
        // $list_ayudas_total = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta_total',$year, $month, $entity, $id_cta_cte, '1135005');
        // $list_viajes = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta',$year, $month, $entity, $id_cta_cte, '1135010');
        // $list_viajes_total = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta_total',$year, $month, $entity, $id_cta_cte, '1135010');
        $jResponse['data'] = [
                                'items' => $list, 'total' => $list_total[0],
                                'adelantos' => $list_adelantos, 'total_adelantos' => $list_adelantos_total[0],
                                // 'ayudas' => $list_ayudas, 'total_ayudas' => $list_ayudas_total[0],
                                // 'viajes' => $list_viajes, 'total_viajes' => $list_viajes_total[0]
                                ];
        return response()->json($jResponse);
    }  

    // Ayudas
    public function st_data_salary_ingresos_ayudas()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $id_cta_cte = $params->data->id_cta_cte;
        $dc = 'D';
        // $list = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo',$year, $month, $entity, $id_persona,'100');
        // $list_total = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo_total',$year, $month, $entity, $id_persona,'100');
        $list = MobileData::st_proc_data_salary_cta('spc_sta_salary_ing_ctipo',$year, $month, $entity, $id_persona,'100', $id_cta_cte, '1135005', $dc);
        $list_total = MobileData::st_proc_data_salary_cta('spc_sta_salary_ing_ctipo_total',$year, $month, $entity, $id_persona,'100', $id_cta_cte, '1135005', $dc);

        $jResponse['data'] = [
                                'items' => $list, 'total' => $list_total[0],
                                ];
        return response()->json($jResponse);
    }
    public function st_data_salary_descuentos_ayudas()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $id_cta_cte = $params->data->id_cta_cte;
        $dc = 'C';
        // $list = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta',$year, $month, $entity, $id_cta_cte, '1135005');
        // $list_total = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta_total',$year, $month, $entity, $id_cta_cte, '1135005');
        
        $list = MobileData::st_proc_data_salary_cta('spc_sta_salary_ing_ctipo',$year, $month, $entity, $id_persona,'200', $id_cta_cte, '1135005', $dc);
        $list_total = MobileData::st_proc_data_salary_cta('spc_sta_salary_ing_ctipo_total',$year, $month, $entity, $id_persona,'200', $id_cta_cte, '1135005', $dc);

        $jResponse['data'] = [
                                'items' => $list, 'total' => $list_total[0],
                                ];
        return response()->json($jResponse);
    }    
    
    // Viajes
    public function st_data_salary_ingresos_viajes()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $id_cta_cte = $params->data->id_cta_cte;  
        $dc='D';      
        // $list = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo',$year, $month, $entity, $id_persona,'200');
        // $list_total = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo_total',$year, $month, $entity, $id_persona,'200');
        $list = MobileData::st_proc_data_salary_cta('spc_sta_salary_ing_ctipo',$year, $month, $entity, $id_persona,'200', $id_cta_cte, '1135010', $dc);
        $list_total = MobileData::st_proc_data_salary_cta('spc_sta_salary_ing_ctipo_total',$year, $month, $entity, $id_persona,'200', $id_cta_cte, '1135010', $dc);

        $jResponse['data'] = [
                                'items' => $list, 'total' => $list_total[0],
                                ];
        return response()->json($jResponse);
    }
    public function st_data_salary_descuentos_viajes()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $id_cta_cte = $params->data->id_cta_cte;
        $dc='C';
        // $list = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta',$year, $month, $entity, $id_cta_cte, '1135010');
        // $list_total = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta_total',$year, $month, $entity, $id_cta_cte, '1135010');
        
        $list = MobileData::st_proc_data_salary_cta('spc_sta_salary_ing_ctipo',$year, $month, $entity, $id_persona,'200', $id_cta_cte, '1135010', $dc);
        $list_total = MobileData::st_proc_data_salary_cta('spc_sta_salary_ing_ctipo_total',$year, $month, $entity, $id_persona,'200', $id_cta_cte, '1135010', $dc);

        $jResponse['data'] = [
                                'items' => $list, 'total' => $list_total[0],
                                ];
        return response()->json($jResponse);
    }    





    public function dep_data()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - dep_data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $id_persona = $params->data->id_persona;
        $bindings = [
            'p_id_entidad' => $entity,
            'p_id_anho' => $year,
            'p_id_persona' => $id_persona
        ];

        $list = MobileData::superProc('spc_mobile_dep_data', $bindings);
        $jResponse['data'] = ['items' => $list];
        return response()->json($jResponse);
    }
    public function dep_data_items()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - dep_data_items'
        ];
        $jResponse['data'] = ['dato' => "", 'items' => ""];
        return response()->json($jResponse);
    }
    public function dep_data_details()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - dep_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $id_depto = $params->data->id_depto;
        
        //$list = MobileData::dep_data_detail($year, $month, $entity, $id_depto);
        //$list_total = MobileData::dep_data_detail_total($year, $month, $entity, $id_depto);
        $bindings = [
            'p_id_entidad' => $entity,
            'p_id_anho' => $year,
            'p_id_depto' => $id_depto
        ];

        $list = MobileData::superProc('spc_mobile_dep_data_detail', $bindings);
        $list_total = MobileData::superProc('spc_mobile_dep_data_detail_tot', $bindings);
        
        $jResponse['data'] = ['items' => $list];
        if(count($list_total)==1){
            $jResponse['data'] = ['items' => $list, 'total' => $list_total[0]];
        }
        
        return response()->json($jResponse);
    }
    
      
    public function tra_data()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none - tra_data'
        ];
        //dd("aaaa");
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }
        
        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $cta_cte = $params->data->cta_cte;
        
        $list = MobileData::proc('spc_travel_data',$entity,$year,$month, $cta_cte); 
        
        if(count($list)==1){
            $jResponse['success'] = true;
            $jResponse['data'] = ['travel' => $list[0]];
        }
        return response()->json($jResponse);
    }
    public function tra_data_details()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - tra_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $cta_cte = $params->data->cta_cte;
        
        
        //$list = MobileData::travels_detail($year, $month, $entity, $cta_cte);
        //$list_total = MobileData::travels_detail_total($year, $month, $entity, $cta_cte);

        $bindings = [
            'p_id_entidad' => $entity,
            'p_id_anho' => $year,
            'p_id_ctacte' => $cta_cte
        ];

        $list = MobileData::superProc('spc_mobile_tra_data_detail', $bindings);
        $list_total = MobileData::superProc('spc_mobile_tra_data_detail_tot', $bindings);
        
        $jResponse['data'] = ['items' => $list];
        if(count($list_total)==1){
            $jResponse['data'] = ['items' => $list, 'total' => $list_total[0]];
        }
        
        return response()->json($jResponse);
    }
    
    public function profile_worker()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - id_persona'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $id_persona = $params->data->id_persona+0;
        
        
        //$list = MobileData::travels_detail($year, $month, $entity, $cta_cte);
        //$list_total = MobileData::travels_detail_total($year, $month, $entity, $cta_cte);

        $bindings = [
            'p_id_persona' => $id_persona
        ];

        $list = MobileData::superProc('spc_profile_worker', $bindings);
        
        if(count($list)==1){
            $jResponse['data'] = ['item' => $list[0]];
        }
        
        return response()->json($jResponse);
    }
    
    public function persons()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none - tra_data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $list = MobileData::list_personal($entity,$year,$month);
        
        $jResponse['data'] = ['persons' => $list];
        return response()->json($jResponse);
    }    
    public function persons_year()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none - tra_data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }        

        $entity = $params->data->entity;
        $year = $params->data->year;
        $list = MobileData::list_personal_year($entity,$year);
        
        $jResponse['data'] = ['persons' => $list];
        return response()->json($jResponse);
    }

/*     public function persons_year_search()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none - tra_data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->entity;
        $year = $params->year;
        $search = " ";
        if(is_null($params->search) === false) {
            // print("ingrese");
            $search = $params->search;
        }
        
        $list = MobileData::list_personal_year_search($entity,$year, $search);
        if($list) {
            $jResponse = [
                'success' => false,
                'message' => 'Proceso exitoso'
            ];
        }
        $jResponse['data'] = ['persons' => $list];
        return response()->json($jResponse);
    }  */
    
    public function persons_year_search(Request $request)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code = $jResponse["code"];
        $valida = $jResponse["valida"];

        if ($valida == 'SI') {
            $jResponse = [];

            $entity = $request->query('entity');
            $year = $request->query('year');
            $search = $request->query('search');
            $month = $request->query('month');
            $depto = $request->query('depto');
            try {
                $data = MobileData::list_personal_year_search($entity,$year, $month, $search, $depto);
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = $data;
                $code = "200";
            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }

        }
        return response()->json($jResponse, $code);
    }
    

    public function persons_directory()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none - search data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }        

        $entity = $params->data->entity;
        $list = MobileData::list_personal_directory($entity);
        
        $jResponse['data'] = ['items' => $list];
        return response()->json($jResponse);
    }
    public function persons_directory_search()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none - search data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }        

        $text = $params->data->text;
        $list = MobileData::list_personal_directory_search($text);
        
        $jResponse['data'] = ['items' => $list];
        return response()->json($jResponse);
    }
    
    public function financial_graphs_datagraph_options()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none - tra_data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $bindings = [
            'p_id_entidad' => $entity,
            'p_id_anho' => $year,
            'p_id_mes' => 12
        ];
        $l_data = MobileData::superProc('spc_financial_indicator_all', $bindings);
        $fila = $l_data[0];
        
        $list = [
            [
                'id' => 1,
                'name' => 'Capital Operativo',
                'value' => $fila->co
            ],
            [
                'id' => 2,
                'name' => 'Sostenimiento Propio Operativo',
                'value' => $fila->spo
            ],
            [
                'id' => 3,
                'name' => 'Sostenimiento Propio General',
                'value' => $fila->spg
            ],
            [
                'id' => 4,
                'name' => 'Liquidez Inmediata',
                'value' => $fila->li
            ],
            [
                'id' => 5,
                'name' => 'Liquidez Corriente',
                'value' => $fila->lc
            ],
        ];
        
        $nombre_mes = '';
        switch ($fila->id_mes) {
            case 1: $nombre_mes = 'Enero'; break;
            case 2: $nombre_mes = 'Febrero'; break;
            case 3: $nombre_mes = 'Marzo'; break;
            case 4: $nombre_mes = 'Abril'; break;
            case 5: $nombre_mes = 'Mayo'; break;
            case 6: $nombre_mes = 'Junio'; break;
            case 7: $nombre_mes = 'Julio'; break;
            case 8: $nombre_mes = 'Agosto'; break;
            case 9: $nombre_mes = 'Setiembre'; break;
            case 10: $nombre_mes = 'Octubre'; break;
            case 11: $nombre_mes = 'Noviembre'; break;
            case 12: $nombre_mes = 'Diciembre'; break;
        }
        
        $jResponse['data'] = [
            'month' => [
                'id' => $fila->id_mes,
                'name' => $nombre_mes
            ],
            'items' => $list
        ];
        return response()->json($jResponse);
    }     
    public function financial_graphs_datagraph()
    {
        $jResponse = [
            'success' => false,
            'message' => 'none - tra_data'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }
        $entity = $params->data->entity;
        $year = $params->data->year;
        $graph_id = $params->data->graph_id;
        $report_name = "";
        $label_1 = "";
        $label_2 = "";
        
        $bindings = [
            'p_id_entidad' => $entity,
            'p_id_anho' => $year,
            'p_id_mes' => 12
        ];
        
        $procedure_name = 'spc_financial_indicator_c_o';
        $value_ideal = 100;
        if($graph_id == 1){
            $procedure_name = 'spc_financial_indicator_c_o';
            $report_name = 'Capital Operativo';
            $label_1 = 'Ideal';
            $label_2 = 'Real';
            $value_ideal = 100;
        }
        if($graph_id == 2){
            $procedure_name = 'spc_financial_indicator_s_p_o';
            $report_name = 'Sostenimiento Propio Operativo';
            $label_1 = 'Ideal';
            $label_2 = 'Real';
            $value_ideal = 100;
        }
        if($graph_id == 3){
            $procedure_name = 'spc_financial_indicator_s_p_g';
            $report_name = 'Sostenimiento Propio General';
            $label_1 = 'Ideal';
            $label_2 = 'Real';
            $value_ideal = 100;
        }
        if($graph_id == 4){
            $procedure_name = 'spc_financial_indicator_l_i';
            $report_name = 'Liquidez Inmediata';
            $label_1 = 'Ideal';
            $label_2 = 'Real';
            $value_ideal = 1;
        }
        if($graph_id == 5){
            $procedure_name = 'spc_financial_indicator_l_c';
            $report_name = 'Liquidez Corriente';
            $label_1 = 'Ideal';
            $label_2 = 'Real';
            $value_ideal = 2;
        }
        
        $list = MobileData::superProc($procedure_name, $bindings);

            
        $fila = $list[0];
        $array_item = [];
        $array_item[] = $fila->m1;
        $array_item[] = $fila->m2;
        $array_item[] = $fila->m3;
        $array_item[] = $fila->m4;
        $array_item[] = $fila->m5;
        $array_item[] = $fila->m6;
        $array_item[] = $fila->m7;
        $array_item[] = $fila->m8;
        $array_item[] = $fila->m9;
        $array_item[] = $fila->m10;
        $array_item[] = $fila->m11;
        $array_item[] = $fila->m12;

        $dataGraph = $this->line_grahps($label_1, $label_2, $array_item, $report_name,$value_ideal);
        
        $jResponse['data'] = ['graph' => $dataGraph];
        return response()->json($jResponse);
    }  
    
    public function line_grahps($label_1, $label_2, $array_item, $report_name,$value_ideal){
        $dataGraph = [
            'type' => 'line',
            'data' => [            
                'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct','Nov','Dic'],
                'datasets' => [
                    [
                        'label' => $label_1,
                        'fill' => false,
                        'backgroundColor' => 'rgba(68, 114, 196, 1)',
                        'borderColor' => 'rgba(68, 114, 196, 1)',
                        'data' => [
                            $value_ideal,$value_ideal,$value_ideal,$value_ideal,$value_ideal,$value_ideal,
                            $value_ideal,$value_ideal,$value_ideal,$value_ideal,$value_ideal,$value_ideal
                        ]
                    ],
                    [
                        'label' => $label_2,
                        'fill' => false,
                        'backgroundColor' => 'rgba(255, 199, 1, 1)',
                        'borderColor' => 'rgba(255, 199, 1, 1)',
                        //'borderDash' => [5, 5],
                        'data' => $array_item
                    ]                
                ],
            ],
            'options' => [
                //'pan' => [
                //    'enabled' => true,
                //    'mode' => 'x'
                //],
                //'zoom' => [
                //    'enabled' => true,
                //    'mode' => 'x'
                //],
                //'responsive' => true,
                'title' => [
                    'display' => true,
                    'text' => $report_name
                ],
                'tooltips' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'hover' => [
                    'mode' => 'nearest',
                    'intersect' => true
                ],
                'legend' => [ 
                    'display' => true,
                    'boxWidth' => 80, 
                    'fontSize' => 15,
                    'padding' => 0
                 ],
                'scales' => [
                    'xAxes' => [[
                        'display' => true,
                        'scaleLabel' => [
                            'display' => false,
                            'labelString' => 'Meses'
                        ]
                    ]],
                    'yAxes' => [[
                        'display' => true,
                        'scaleLabel' => [
                            'display' => false,
                            'labelString' => 'Valores'
                        ]
                    ]]
                ]
            ]
        
        ];
        return $dataGraph;
    }

    // Reportes Pag Web
    
    public function st_data_salary_ingresos_array()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $id = 1;
        
        $list = MobileData::st_proc_data_salary('spc_sta_salary_ingresos',$year, $month, $entity, $id_persona,'');
        $list_total = MobileData::st_proc_data_salary('spc_sta_salary_ingresos_total',$year, $month, $entity, $id_persona,'');
        $list_a = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo',$year, $month, $entity, $id_persona,'100');
        $list_a_total = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo_total',$year, $month, $entity, $id_persona,'100');
        $list_b = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo',$year, $month, $entity, $id_persona,'200');
        $list_b_total = MobileData::st_proc_data_salary('spc_sta_salary_ing_tipo_total',$year, $month, $entity, $id_persona,'200');


	foreach ($list as $key => $item) {
        $item->id = $id;
        $item->load = false;
        $item->open = false;
        $id = $id +1;
	}
	if(count($list_a)>0){	
		$ayudas = [
                'id' => 51,
				'fecha' => '', 
				'comentario' => 'Ayudas', 
				'valor' => $list_a_total[0]->total,
				'load' => true,
                'open' => false,
				'children' => $list_a
				];
        $list[] = $ayudas;
        $id = $id +1;
	}
	if(count($list_b)>0){	
		$viajes = [
                'id' => 52,
                'fecha' => '',
				'comentario' => 'Viajes',
				'valor' => $list_b_total[0]->total,
				'load' => true,
                'open' => false,
				'children' => $list_b
			];
        $list[] = $viajes;
        $id = $id +1;
	}

        $jResponse['data'] = [
                                'items' => $list, 'total' => $list_total[0]
                                ];
        return response()->json($jResponse);
    }        
    public function st_data_salary_descuentos_array()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - st_data_details'
        ];
        $params = json_decode(file_get_contents("php://input"));
        if (is_null($params)) {
            $params = json_decode(json_encode($_POST), FALSE);
        }

        $entity = $params->data->entity;
        $year = $params->data->year;
        $month = $params->data->month;
        $id_persona = $params->data->id_persona;
        $id_cta_cte = $params->data->id_cta_cte;
        $id = 1;
        
        $list = MobileData::st_proc_data_salary('spc_sta_salary_desc',$year, $month, $entity, $id_persona,'');
        $list_total = MobileData::st_proc_data_salary('spc_sta_salary_desc_total',$year, $month, $entity, $id_persona,'');
        
        $list_adelantos = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta',$year, $month, $entity, $id_cta_cte, '1135001');
        $list_adelantos_total = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta_total',$year, $month, $entity, $id_cta_cte, '1135001');
        $list_ayudas = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta',$year, $month, $entity, $id_cta_cte, '1135005');
        $list_ayudas_total = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta_total',$year, $month, $entity, $id_cta_cte, '1135005');
        $list_viajes = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta',$year, $month, $entity, $id_cta_cte, '1135010');
        $list_viajes_total = MobileData::st_proc_data_salary_desc_cta('spc_sta_salary_desc_cta_total',$year, $month, $entity, $id_cta_cte, '1135010');

	foreach ($list as $key => $item) {
        $item->id = $id;
        $item->load = false;
        $item->open = false;
        $id = $id +1;
	}

	if(count($list_adelantos)>0){	
        
		$adelantos = [
                'id' => 50,
    			'fecha' => '', 
				'comentario' => 'Adelantos', 
				'valor' => $list_adelantos_total[0]->total,
                'load' => true,
                'open' => false,
				'children' => $list_adelantos
				];
		$list[] = $adelantos;
        $id = $id +1;
	}
	if(count($list_ayudas)>0){	
		$ayudas = [
                'id' => 51,
                'fecha' => '',
				'comentario' => 'Ayudas',
				'valor' => $list_ayudas_total[0]->total,
				'load' => true,
                'open' => false,
				'children' => $list_ayudas
			];
		$list[] = $ayudas;
        $id = $id +1;
        }
	if(count($list_viajes)>0){	
		$viajes = [
                'id' => 52,
                'fecha' => '',
				'comentario' => 'Viajes',
				'valor' => $list_viajes_total[0]->total,
				'load' => true,
                'open' => false,
				'children' => $list_viajes
			];
		$list[] = $viajes;
        $id = $id +1;
	}
        
        $jResponse['data'] = [
                                'items' => $list, 'total' => $list_total[0]
                                ];
        return response()->json($jResponse);
    }     
}
