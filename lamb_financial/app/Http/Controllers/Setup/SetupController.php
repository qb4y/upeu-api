<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 14/06/2017
 * Time: 8:18 PM
 */

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\SetupData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Inventories\WarehousesData;
use App\Http\Data\Setup\PersonData;
//use Request;

class SetupController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function user_data()
    {
        $jResponse = [
            'success' => true,
            'message' => 'none - User Data',
	    'data' => []
        ];
        $params = json_decode(file_get_contents("php://input"));

        //$api_key = Request::header('Content-Type');
        //$jResponse['xmessage'] = $api_key;
        
        $person_id = $params->data->person_id;
        
        $bindings = [
            'p_id_persona' => $person_id
        ];

        $list = SetupData::superProc('spc_mobile_user_data', $bindings);
        
        if(count($list)==1){
            $jResponse['data'] = ['user' => $list[0]];
            $jResponse['message'] = 'OK2';
        }else{
            $jResponse = [
                'success' => false,
                'message' => 'No Data',
                'data' => []
            ];            
        }
        return response()->json($jResponse);
    }
     public function userData(){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        if($valida=='SI'){
            $jResponse=[];
            try{  
                $oPerson = DB::table('MOISES.PERSONA')->where('id_persona', $id_user)->first();
                $name = $oPerson->nombre;
                $first_name = $oPerson->paterno;
                $last_name = $oPerson->materno;
                $user_name = $name . ' ' . $first_name . ' ' . $last_name;
                $warehouse_name = "";
                $sexo = "1";
                $imagen_url = "";
                $sql = "SELECT 
                        A.NOMBRE, A.ID_EMPRESA, B.NOMBRE AS DEPTO 
                        FROM CONTA_ENTIDAD A,CONTA_ENTIDAD_DEPTO B
                        WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                        AND A.ID_ENTIDAD = $id_entidad
                        AND B.ID_DEPTO = '".$id_depto."' ";
                $data = DB::select($sql);
                foreach ($data as $key => $item){
                    $entity_name = $item->nombre;
                    $id_empresa = $item->id_empresa;
                    $departament_name = $item->depto;
                }
                $warehouse = WarehousesData::showWarehousesUsersAssign($id_entidad,$id_user);
                foreach ($warehouse as $key => $item){
                    $warehouse_name = $item->nombre_almacen;
                }
                $dir = secure_url('');//Prod
                //$dir = url('');//Dev
                $person = PersonData::showPersonNatural($id_user,$dir);
                foreach ($person as $key => $item){
                    $imagen_url = $item->imagen_url;
                    $sexo = $item->sexo;
                }  
                $data = [                       
                    'user_name' =>$user_name,
                    'entity_name' =>$entity_name,
                    'departament_name' =>$departament_name,
                    'warehouse_name' =>$warehouse_name,
                    'entity_id' =>$id_entidad,
                    'company_id' =>$id_empresa,
                    'departament_id' =>$id_depto,
                    'sexo' =>$sexo,
                    'image' =>$imagen_url,
                    'user_id' =>$id_user
                ];
                // dd($oPerson);
                $jResponse['success'] = true;

                if(!empty($oPerson)){
                    $jResponse['message'] = "Success 2025";
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }
        }        
        return response()->json($jResponse,$code);
        /*
        $jResponse = [
            'success' => true,
            'message' => 'none - User Data',
	    'data' => []
        ];
        $params = json_decode(file_get_contents("php://input"));

        //$api_key = Request::header('Content-Type');
        //$jResponse['xmessage'] = $api_key;
        
        $person_id = $params->data->person_id;
        
        $bindings = [
            'p_id_persona' => $person_id
        ];

        $list = SetupData::superProc('spc_mobile_user_data', $bindings);
        
        if(count($list)==1){
            $jResponse['data'] = ['user' => $list[0]];
            $jResponse['message'] = 'OK2';
        }else{
            $jResponse = [
                'success' => false,
                'message' => 'No Data',
                'data' => []
            ];            
        }
        return response()->json($jResponse);*/
    }
    
    public function addRol(){                
         $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];

        $params = json_decode(file_get_contents("php://input"));       
        $name = $params->data->name; 
        $state = $params->data->state; 
        $nameRol = SetupData::rol($name,$state);
        if ($nameRol) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $nameRol];
        }

        return response()->json($jResponse);
    }

    public function getYear()
    {

        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];

        $lstYear = SetupData::year();

        if ($lstYear) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstYear->toArray()];
        }

        return response()->json($jResponse);
    }
    public function getYearActivo($entity){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];
            try{     
                $data = SetupData::yearActivo($entity);
                $jResponse['success'] = true;                    
                $jResponse['message'] = "Success";                    
                $jResponse['data'] = ['items' => $data];
                $code = "200"; 
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }
        return response()->json($jResponse,$code);

    }

    public function getMonth()
    {

        $jResponse = [
            'success' => false,
            'message' => 'Resource no Authorizated',
	    'data' => []
        ];

        $token = $this->request->header('Authorization');

        if ($token) {
            	session_id($token);
            	session_start();

            	$bindings = [
            	 'p_token' => $token
            	];
            	$result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);

            	$valida = $result[0];
	
		if( $valida->active == 'SI'  ){

        		$lstMonth = SetupData::month();

        		if ($lstMonth) {
            			$jResponse['success'] = true;
            			$jResponse['message'] = 'OK-MONTHS';
            			$jResponse['data'] = ['items' => $lstMonth->toArray()];
        		}
		}
	}

        return response()->json($jResponse);
    }
    public function getMonthEntity(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $entidad_id = $jResponse["id_entidad"];
        $current_year = date('Y');
        if($valida=='SI'){
            $jResponse=[];
            $id_entidad = $request->query('id_entidad') ? $request->query('id_entidad') : $entidad_id;
            $id_anho   = $request->query('id_anho') ? $request->query('id_anho') : $current_year;

            $lstMonth = SetupData::monthEntity($id_entidad,$id_anho);
            if ($lstMonth) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $lstMonth->toArray()];
                $code = "200"; 
            }else{
                $jResponse['success'] = false;                    
                $jResponse['message'] = "";
                $jResponse['data'] = [];
                $code = "200";
            }		
	}
        return response()->json($jResponse);
    }
    public function getCompany()
    {

        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];

        $lstCompany = SetupData::company();

        if ($lstCompany) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstCompany->toArray()];
        }

        return response()->json($jResponse);
    }
    public function getCompanyByUser()
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
                
                    $id_user = $valida->id_user;
                    $lstData = SetupData::companyByUser($valida->id_user);
                    
                    if ($lstData) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = [
                            'items' => $lstData
                        ];
                    }
                }
            }
    
            return response()->json($jResponse);
        }

    public function getEntity()
    {

        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];

        $lstEntity = SetupData::entity();

        if ($lstEntity) {

            $value1 = "1";
            $value2 = "1";
            $companies = [];
            $entities = [];

            foreach ($lstEntity as $key => $value) {
                $value2 = $value->id_empresa;

                if ($value1 != $value2 && $value1 != "1") {
                    $companies[] = ['id_empresa' => $value1, 'entidades' => $entities];
                    $entities = [];
                }
                $entities[] = $value;
                $value1 = $value2;
            }

            if ($companies) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $companies];
            }
        }

        return response()->json($jResponse);
    }
    public function listEntitiesEnterprise(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        if($valida=='SI'){
            $jResponse=[];
            try{     
                $id_empresa   = $request->query('id_empresa');
                $data = SetupData::listEntitiesEnterprise($id_empresa);
                $jResponse['message'] = "SUCCES";
                $jResponse['success'] = true;
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }
        return response()->json($jResponse,$code);
    }

    public function listEntitiesEnterpriseByUser(Request $request, $id_empresa){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        $id_user = $jResponse["id_user"];       
        if($valida=='SI'){
            $jResponse=[];
            try{     
                $withAllOoption   = $request->query('withAllOoption') ? $request->query('withAllOoption') : '1';

                $data = SetupData::listEntitiesEnterpriseByUser($id_empresa, $id_user, $withAllOoption);
                $jResponse['message'] = "SUCCESS";
                $jResponse['success'] = true;
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }
        return response()->json($jResponse,$code);
    }
    public function listEntitiesEnterpriseVerifyAllEntities($id_empresa){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        $id_user = $jResponse["id_user"];       
        if($valida=='SI'){
            $jResponse=[];
            try{     
                //$id_empresa   = $request->query('id_empresa');
                $data = SetupData::listEntitiesEnterpriseVerifyAllEntities($id_empresa, $id_user);
                $jResponse['message'] = "SUCCESS";
                $jResponse['success'] = true;
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }
        return response()->json($jResponse,$code);
    }
    

    public function listDeptosEntitiesByUser(Request $request, $id_empresa, $id_entidad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        $id_user = $jResponse["id_user"];       
        if($valida=='SI'){
            $jResponse=[];
            try{
                $withAllOoption   = $request->query('withAllOoption') ? $request->query('withAllOoption') : '1';

                if($id_entidad=='0'){
                    $data = SetupData::listOnlyAllDeptosEntitiesByUser();
                }else{
                    $data = SetupData::listDeptosEntitiesByUser($id_entidad, $id_user, $withAllOoption);
                }
                $jResponse['message'] = "SUCCESS";
                $jResponse['success'] = true;
                $jResponse['data'] = $data;
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }
        return response()->json($jResponse,$code);
    }
    public function listDeptosEntitiesByUserVerifyAll($id_empresa, $id_entidad){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];       
        $id_user = $jResponse["id_user"];       
        if($valida=='SI'){
            $jResponse=[];
            try{     
                if($id_entidad=='0'){
                    $data = SetupData::listOnlyAllDeptosEntitiesByUserVerifyAllDeptos();
                }else{
                    $data = SetupData::listDeptosEntitiesByUserVerifyAllDeptos($id_entidad, $id_user);
                }
                $jResponse['message'] = "SUCCESS";
                $jResponse['success'] = true;
                $jResponse['data'] = $data[0];
                $code = "200";
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }
        return response()->json($jResponse,$code);
    }

    public function getEntityByType()
    {

        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];

        $lstEntity = SetupData::entityByType();

        //dd($lstEntity);

        if ($lstEntity) {

            $value1 = "1";
            $value2 = "1";
            $companies = [];
            $entities = [];

            foreach ($lstEntity as $key => $value) {
                $value2 = $value->tipo;

                if ($value1 != $value2 && $value1 != "1") {
                    $companies[] = ['id' => $value->id_tipoentidad, 'name' => $value1, 'entidades' => $entities];
                    $entities = [];
                }
                $entities[] = $value;
                $value1 = $value2;
            }
            $companies[] = ['id' => $value->id_tipoentidad, 'name' => $value1, 'entidades' => $entities];

            if ($companies) {
                $jResponse['success'] = true;
                $jResponse['message'] = 'OK';
                $jResponse['data'] = ['items' => $companies];
            }
        }

        return response()->json($jResponse);
    }


    public function getEntityByType_new()
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
            
                $id_user = $valida->id_user;
                $lstEntity = SetupData::entityByType_new($id_user);

                //dd($lstEntity);

                if ($lstEntity) {

                    $value1 = "1";
                    $value2 = "1";
                    $companies = [];
                    $entities = [];

                    foreach ($lstEntity as $key => $value) {
                        $value2 = $value->tipo;

                        if ($value1 != $value2 && $value1 != "1") {
                            $companies[] = ['id' => $value->id_tipoentidad, 'name' => $value1,'entidades' => $entities];
                            $entities = [];
                        }
                        $entities[] = $value;
                        $value1 = $value2;
                    }
                    $companies[] = ['id' => $value->id_tipoentidad, 'name' => $value1, 'default_select'=> $value->default_select, 'entidades' => $entities];

                    if ($companies) {
                        $jResponse['success'] = true;
                        $jResponse['message'] = 'OK';
                        $jResponse['data'] = ['items' => $companies];
                    }
                }
            }
        }

        return response()->json($jResponse);
    }

    public function getFund()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];

        $lstYear = SetupData::fund();

        if ($lstYear) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstYear->toArray()];
        }

        return response()->json($jResponse);
    }

    public function getAccountingAccount()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];

        $lstAccountingAccount = SetupData::accountingAccount();
        if ($lstAccountingAccount) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstAccountingAccount->toArray()];
        }

        return response()->json($jResponse);
    }

    public function getCurrentAccount()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];

        $lstCurrentAccount = SetupData::currentAccount();

        if ($lstCurrentAccount) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstCurrentAccount->toArray()];
        }

        return response()->json($jResponse);
    }

    public function getDepartment()
    {
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];

        $params = json_decode(file_get_contents("php://input"));
        $entity = $params->data->entity;

        $lstDepartment = SetupData::department($entity);

        if ($lstDepartment) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';

            $jResponse['data'] = ['items' => $lstDepartment->toArray()];
        }

        return response()->json($jResponse);
    }

    public function getRestrictions()
    {
        $cond_accounts = "";
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];
        $params = json_decode(file_get_contents("php://input"));
        print_r($params);
        $account_all = $params->data->account_all;
        $queryAccounts = "  ";
        if ($account_all != 1) {
            $accounts = $params->data->accounts;
            $accounts = "'" . implode("','", $accounts) . "'";
            $queryAccounts = "
                WHERE ID_RESTRICCION IN (
                SELECT ID_RESTRICCION FROM CONTA_CTA_DENOMINACIONAL
                WHERE id_cuentaaasi IN (" . $accounts . ")
                )
                ";
        }
        $lstData = SetupData::restrictions($queryAccounts);

        if ($lstData) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstData];
        }

        return response()->json($jResponse);
    }

    public function getTypeCurrentAccounts()
    {
        $cond_accounts = "";
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];
        $params = json_decode(file_get_contents("php://input"));

        $account_all = $params->data->account_all;
        $queryAccounts = "  ";
        if ($account_all != 1) {
            $accounts = $params->data->accounts;
            $accounts = "'" . implode("','", $accounts) . "'";
            $queryAccounts = "
                WHERE ID_TIPOCTACTE IN (
                SELECT ID_TIPOCTACTE FROM CONTA_CTA_DENOMINACIONAL
                WHERE id_cuentaaasi IN (" . $accounts . ")
                )
                ";
        }
        $lstData = SetupData::type_current_accounts($queryAccounts);

        if ($lstData) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $lstData];
        }

        return response()->json($jResponse);
    }

    public function getMultipleCurrentAccounts()
    {
        $cond_types = "";
        $jResponse = [
            'success' => false,
            'message' => 'ERROR'
        ];
        $params = json_decode(file_get_contents("php://input"));
        $entity = $params->data->entity;

        $account_all = $params->data->account_all;

        $queryAccounts = "  ";
        if (!$account_all) {
            $accounts = $params->data->accounts;
            $implodeAccounts = "'" . implode("','", $accounts) . "'";
            $queryAccounts = "
                AND ID_TIPOCTACTE IN (
                SELECT ID_TIPOCTACTE FROM CONTA_CTA_DENOMINACIONAL
                WHERE id_cuentaaasi IN (" . $implodeAccounts . ")
                )
                ";
        }

        $type_current_account_all = $params->data->type_current_account_all;
        $queryTypeCurrentAccounts = "  ";
        if (!$type_current_account_all) {
            $type_current_accounts = $params->data->type_current_accounts;
            $typeCurrentAccounts = "'" . implode("','", $type_current_accounts) . "'";
            $queryTypeCurrentAccounts = "
                AND ID_TIPOCTACTE IN ($typeCurrentAccounts)
                ";
        }


        $lstData = SetupData::current_accounts($entity, $queryTypeCurrentAccounts, $queryAccounts);
        //dd($lstData);
        $value1 = "1";
        $value2 = "1";
        $array_types = [];
        $array_current_account = [];
        $unico = true;
        foreach ($lstData as $key => $value) {
            $value2 = $value->id_tipoctacte;

            if ($value1 != $value2 && $value1 != "1") {
                $array_types[] = ['id' => $value1, 'name' => $value1, 'items' => $array_current_account];
                $array_current_account = [];
            }
            $array_current_account[] = $value;
            $value1 = $value2;
        }
        $array_types[] = ['id' => $value1, 'name' => $value1, 'items' => $array_current_account];


        if ($lstData) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => $array_types];
        }
        if (count($lstData) == 0) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['items' => []];
        }

        return response()->json($jResponse);
    }

    public function listTipoDocRepre(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SetupData::getListTipoDocRepre();
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
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

    public function listDocRepresentative(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SetupData::listDocRepresentativeNew($this->request);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
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

/*     public function filterDocRepresentative(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SetupData::listDocRepresentativeFilter($request);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }	

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "500";
            }

	}
    return response()->json($jResponse,$code);
    } */

    public function listDepto(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SetupData::listDeptoData($this->request);
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
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


    public function addDocRepresentative(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SetupData::addDocRepresentative($this->request);
                if($data=="OK"){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $jResponse['data'] = [];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $data;                        
                    $jResponse['data'] = [];
                    $code = "202";
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

    public function showDocRepresentative($id_entideplegal){
        
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = SetupData::showDocRepresentativeId($id_entideplegal);                                
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function editDocRepresentative($id_entideplegal,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        
        if($valida=='SI'){
            $jResponse=[];
            try{
                    $ok = SetupData::editDocRepresentative($id_entideplegal,$this->request);
                    if($ok=="OK"){
                        $jResponse['success'] = true;
                        $jResponse['message'] = "The item was updated successfully";
                        $jResponse['data'] = [];
                        $code = "200";
                    }else{
                        $jResponse['success'] = false;
                        $jResponse['message'] = $ok;
                        $jResponse['data'] = [];
                        $code = "202";
                    }

            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getFile().' - '.$e->getLine().' - '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }

    
    public  function deleteDocRepresentative($id_entideplegal){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];        
        if($valida=='SI'){
            $jResponse=[];            
            try{
                SetupData::deleteDocRepresentative($id_entideplegal);                
                $jResponse['success'] = true;
                $jResponse['message'] = "he item was deleted successfully";                    
                $jResponse['data'] = [];
                $code = "200";                  
                
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getFile().' - '.$e->getLine().' - '.$e->getMessage();
                $jResponse['data'] = [];
                $code = "202"; 
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function DocRepresentativeFilters(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{     
                $data = SetupData::DocRepresentativeFilters($this->request);                                
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function getYearActivoAll(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $entity = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SetupData::yearActivoAll($entity);
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function getYearActivoAllByEntity($entity){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        //$entity = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $data = SetupData::yearActivoAll($entity);
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function getYearActivoByIdEntidadUserSession(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $entity = $jResponse["id_entidad"];
        if($valida=='SI'){
            $jResponse=[];                        
            try{  
                $data = SetupData::yearActivo($entity);                               
                if(count($data)>0){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Success";                    
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }

    public function getProcedure(Request $request){
        $response = GlobalMethods::authorizationLamb($this->request);
        if($response["valida"]=='SI'){
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = SetupData::getProcedure();
        }
        return response()->json($response, $response["code"]);
    }

}
