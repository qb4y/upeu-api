<?php
/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Data;

use App\Http\Controllers\Controller;
use App\Models\Procedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use Carbon\Carbon;
// use Illuminate\Support\Facades\Redis;
// use Illuminate\Support\Facades\Cache;

class SetupData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public static function superProc($procedureName,$bindings)
    {
        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }
    
    public static function user_data($person_id)
    {
        $query = " select   id_persona as id, 
                            num_documento as doc_number, 
                            nom_persona as name, 
                            id_entidad as entity
                    from 
                  (select id_persona, num_documento, nom_persona, id_entidad
                  from VW_APS_EMPLEADO
                  where id_persona = $person_id
                  order by fec_inicio desc
                  ) a
                   where ROWNUM <= 1
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    
    public static function rol($name,$state){
        DB::table('LAMB_ROL')->insert(
            array('NOMBRE' => $name, 'ESTADO' => $state)
        );
        return "Rol Registrado";
    }

    public static function year()
    {
        $getYear = DB::table('CONTA_ANHO')->select('ID_ANHO')->orderBy('ID_ANHO', 'DESC')->get();

        return $getYear;
    }

    public static function getMonthById($id_mes)
    {
        $query = "SELECT * FROM CONTA_MES WHERE ID_MES= $id_mes";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function month()
    {
        // $keyName = 'month_v1';

        // $cacheMonth = Redis::get($keyName);

        // if(isset($cacheMonth)) {
        //     $getMonth = json_decode($cacheMonth, FALSE);
        //     foreach ($getMonth as $value) { // SOLO PARA PROBAR DESPUES QUITAR EL FOR
        //         $item = (Object)$value;
        //         $item->origin = 'REDIS';
        //     }
        // }else {
        //     $getMonth = DB::table('CONTA_MES')->select('ID_MES', 'NOMBRE', 'SIGLAS')->orderBy('ID_MES')->get();
        //     foreach ($getMonth as $value) { // SOLO PARA PROBAR DESPUES QUITAR EL FOR
        //         $item = (Object)$value;
        //         $item->origin = 'DB';
        //     }
        //     Redis::set($keyName, json_encode($data), 'EX', 86400);
        // }
        $getMonth = DB::table('CONTA_MES')->select('ID_MES', 'NOMBRE', 'SIGLAS')->orderBy('ID_MES')->get();
        return $getMonth;
    }
    public static function monthEntity($id_entidad,$id_anho){
        $getMonth = DB::table('CONTA_ENTIDAD_MES_CONFIG A')
                    ->join('CONTA_MES B', 'A.ID_MES', '=', 'B.ID_MES')
                    ->where('A.ID_ENTIDAD', $id_entidad)
                    ->where('A.ID_ANHO', $id_anho)
                    ->where('A.ESTADO', '1')
                    ->select('A.ID_MES', 'B.NOMBRE', 'B.SIGLAS')
                    ->orderBy('A.ID_MES')->get();        
        return $getMonth;
    }

    public static function fund()
    {
        $getFund = DB::table('CONTA_FONDO')->select('ID_FONDO', DB::raw("ID_FONDO||' - '|| NOMBRE AS NOMBRE"))->where('ES_GRUPO', 0)->orderBy('ID_FONDO')->get();

        return $getFund;
    }

    public static function accountingAccount()
    {
        $getAccountingAccount = DB::table('CONTA_CTA_DENOMINACIONAL')->select('ID_CUENTAAASI', 'ID_RESTRICCION', 'ID_TIPOPLAN','ID_TIPOCTACTE', 'NOMBRE')->where('ES_GRUPO', 0)->get();
        
        return $getAccountingAccount;
    }

    public static function currentAccount()
    {
        $getCurrentAccount = DB::table('VW_CONTA_CTACTE')->select('ID_ENTIDAD', 'ID_TIPOCTACTE as TIPO_CTACTE', 'ID_CTACTE', 'NOMBRE')->where('ID_ENTIDAD', 17112)->get();

        return $getCurrentAccount;
    }

    public static function company()
    {
        $getCompany = DB::table('VW_CONTA_EMPRESA')
            ->select('ID_CORPORACION AS CORPORACION',
                'ID_TIPOEMPRESA AS TIPO_EMPRESA',
                'ID_EMPRESA',
                'ID_PERSONA',
                'ID_RUC AS RUC',
                'NOM_DENOMINACIONAL AS NOMBRE',
                'NOM_LEGAL AS NOMBRE_LEGAL')
            ->get();

        return $getCompany;
    }
    public static function entityVoucherDetail($id_voucher){
        $query = "
        SELECT 
            t.NOMBRE as nombre_tipo_voucher,
            v.ID_TIPOASIENTO, v.NUMERO, v.LOTE, to_char(v.FECHA,'dd/mm/yyyy') AS fecha,
            v.ID_PERSONA, 
            FC_USERNAME(v.ID_PERSONA) AS usuario, 
            d.ID_TIPOASIENTO as id_tipoasiento_aasinet, d.COD_AASI,
            COALESCE(to_char(d.FEC_ASIENTO,'dd/mm/yyyy hh24:mi:ss'),'') AS fecha_asiento,
            COALESCE(to_char(d.FEC_DIGITADO,'dd/mm/yyyy hh24:mi:ss'),'') AS fecha_digitado,
            COALESCE(to_char(d.FEC_CONTABILIZADO,'dd/mm/yyyy hh24:mi:ss'),'') AS fecha_contabilizado,
            COALESCE(d.NOM_DIGITADOR,'') AS nom_digitador, 
            COALESCE(d.NOM_CONTADOR,'') AS nom_contador,
            COALESCE(d.COMENTARIO,'') AS comentario
            FROM TIPO_VOUCHER t INNER JOIN CONTA_VOUCHER v ON (t.ID_TIPOVOUCHER = v.ID_TIPOVOUCHER) LEFT OUTER JOIN 
            CONTA_DIARIO d ON ( 
            replace(v.LOTE,'RC ') = d.COD_AASI
            AND v.ID_ENTIDAD = d.ID_ENTIDAD
            AND v.ID_MES = d.ID_MES
            AND v.ID_TIPOASIENTO = d.ID_TIPOASIENTO
            AND v.ID_ANHO = d.ID_ANHO
            )
            WHERE ID_VOUCHER = $id_voucher
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }  

    public static function entityDeptoDetail($id_entidad, $id_depto){
        $query = "
        SELECT NOMBRE, ES_GRUPO FROM CONTA_ENTIDAD_DEPTO
        WHERE ID_ENTIDAD = $id_entidad
        AND ID_DEPTO = '$id_depto'
        AND ES_ACTIVO = '1'
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }  
    public static function entityDetail($id_entidad){
        $query = "
        SELECT ID_EMPRESA, ID_PERSONA, ID_TIPOENTIDAD, NOMBRE FROM CONTA_ENTIDAD
        WHERE ID_ENTIDAD = $id_entidad
        AND ES_ACTIVO = 1
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function entityDetailArray($id_entidad){
        $query = "
        SELECT ID_EMPRESA, ID_PERSONA, ID_TIPOENTIDAD, NOMBRE FROM CONTA_ENTIDAD
        WHERE ID_ENTIDAD IN ($id_entidad)
        AND ES_ACTIVO = 1
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    
    public static function entityDetailView($id_entidad){
        $query = "SELECT 
                    ID_EMPRESA, 
                    ID_PERSONA, 
                    ID_TIPOENTIDAD, 
                    NOMBRE, 
                    NOM_ENTIDAD,
                    ID_RUC AS RUC,
                    NOM_EMPRESA 
                    FROM VW_CONTA_ENTIDAD
                WHERE ID_ENTIDAD = $id_entidad
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function enterpriseByIdEnterprise($id_empresa)
    {
        // $query = "SELECT distinct em.ID_CORPORACION AS CORPORACION,
        //                 em.ID_TIPOEMPRESA AS TIPO_EMPRESA,
        //                 em.ID_EMPRESA,
        //                 em.ID_PERSONA,
        //                 em.ID_RUC AS RUC,
        //                 em.NOM_DENOMINACIONAL AS NOMBRE,
        //                 em.EMP_NOM_2 AS NOMBRE_LEGAL,
        //                 em.LOGO AS LOGO
        //                 FROM VW_CONTA_EMPRESA em
        //                 WHERE em.ID_EMPRESA = $id_empresa";
        // $oQuery = DB::select($query);
        // return $oQuery;
        // Verifica si $id_empresa es válido
        if (empty($id_empresa)) {
            return response()->json(['error' => 'El ID de empresa es nulo o inválido.'], 400);
        }

        // Define la consulta usando un parámetro enlazado
        $query = "SELECT DISTINCT em.ID_CORPORACION AS CORPORACION,
                        em.ID_TIPOEMPRESA AS TIPO_EMPRESA,
                        em.ID_EMPRESA,
                        em.ID_PERSONA,
                        em.ID_RUC AS RUC,
                        em.NOM_DENOMINACIONAL AS NOMBRE,
                        em.EMP_NOM_2 AS NOMBRE_LEGAL,
                        em.LOGO AS LOGO
                FROM VW_CONTA_EMPRESA em
                WHERE em.ID_EMPRESA = :id_empresa";

        // Ejecuta la consulta con el parámetro enlazado
        $oQuery = DB::select($query, ['id_empresa' => $id_empresa]);

        return $oQuery;
    }

    public static function enterpriseByIdEntity($id_entidad)
    {
        $query = "SELECT distinct em.ID_CORPORACION AS CORPORACION,
                        em.ID_TIPOEMPRESA AS TIPO_EMPRESA,
                        em.ID_EMPRESA,
                        em.ID_PERSONA,
                        em.ID_RUC AS RUC,
                        em.NOM_DENOMINACIONAL AS NOMBRE,
                        em.NOM_LEGAL AS NOMBRE_LEGAL,
                        em.LOGO
                        FROM VW_CONTA_EMPRESA em
                        WHERE em.ID_EMPRESA = (SELECT id_empresa FROM CONTA_ENTIDAD WHERE id_entidad = $id_entidad)";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function printerFooterDetail($id_persona)
    {
        $query = "  SELECT 
                    to_char(SYSDATE,'dd/mm/yyyy hh24:mi:ss') AS fechahora,
                    FC_USERNAME($id_persona) AS username
                    FROM dual";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    
    public static function companyByUser($id_persona)
    {
        
        // em.NOM_DENOMINACIONAL AS NOMBRE,
        $query = "SELECT distinct em.ID_CORPORACION AS CORPORACION,
                        em.ID_TIPOEMPRESA AS TIPO_EMPRESA,
                        em.ID_EMPRESA,
                        em.ID_PERSONA,
                        em.ID_RUC AS RUC,
                        em.EMP_NOM_2 AS NOMBRE,
                        em.NOM_LEGAL AS NOMBRE_LEGAL,
                        us.ESTADO as ESTADO 
                        FROM VW_CONTA_EMPRESA em, CONTA_ENTIDAD en, CONTA_ENTIDAD_USUARIO us
                        WHERE em.ID_EMPRESA = en.ID_EMPRESA
                        AND en.ID_ENTIDAD = us.ID_ENTIDAD
                        AND us.ID_PERSONA = $id_persona ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function entity()
    {
        $getEntity = DB::table('VW_CONTA_ENTIDAD')->select('ID_EMPRESA', 'ID_RUC as RUC', 'ID_ENTIDAD', 'ID_PERSONA', 'ID_TIPOENTIDAD as TIPO_ENTIDAD', 'NOMBRE as SIGLA', 'NOM_ENTIDAD as NOMBRE')
            ->orderBy('ID_EMPRESA', 'ASC')
            ->get();
        return $getEntity;
    }

    public static function entityByType()
    {
        $getEntity2 = DB::table('conta_entidad e')
            ->join('tipo_entidad t', 'e.ID_TIPOENTIDAD', '=', 't.ID_TIPOENTIDAD')
            ->select('t.nombre as tipo', 'e.id_entidad as id', 'e.nombre as name','e.ID_TIPOENTIDAD','e.id_empresa')
            ->orderBy('t.orden')->get();

        return $getEntity2;
    }
    public static function listEntitiesEnterprise($id_empresa){
        $getEntity2 = DB::table('conta_entidad e')
            ->join('tipo_entidad t', 'e.ID_TIPOENTIDAD', '=', 't.ID_TIPOENTIDAD')
            ->select('t.nombre as tipo', 'e.id_entidad as id', 'e.nombre as name','e.ID_TIPOENTIDAD')
            ->where('e.id_empresa', $id_empresa)
            ->orderBy('t.orden')->get();

        return $getEntity2;
    }


    public static function listOnlyAllDeptosEntitiesByUser(){
        $query = "SELECT '*' AS id_depto, ' Todos' AS Nombre, 1 as ESTADO FROM dual ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listDeptosEntitiesByUser($id_entidad, $id_persona, $withAllOoption){

        $query = "SELECT '*' AS id_depto, ' Todos' AS Nombre, 0 as ESTADO FROM dual
        WHERE (SELECT count(*) FROM CONTA_ENTIDAD_DEPTO WHERE ID_ENTIDAD = $id_entidad AND ES_EMPRESA = '1' )=
            (SELECT count(*)
                    FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B 
                    WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                    AND A.ID_DEPTO = B.ID_DEPTO
                    AND B.ID_ENTIDAD = $id_entidad
                    AND B.ID = $id_persona
                    AND ES_EMPRESA = '1' 
                    AND B.ACTIVO = '1')
            AND (SELECT count(*) FROM CONTA_ENTIDAD_DEPTO WHERE ID_ENTIDAD = $id_entidad AND ES_EMPRESA = '1' ) >1
            and 1 = $withAllOoption
    UNION ALL
    SELECT 
                    A.ID_DEPTO, A.NOMBRE, B.ESTADO
                    FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B 
                    WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                    AND A.ID_DEPTO = B.ID_DEPTO
                    AND B.ID_ENTIDAD = $id_entidad
                    AND B.ID = $id_persona
                    AND ES_EMPRESA = '1'
                    AND B.ACTIVO = '1'
                    ORDER BY ID_DEPTO, nombre ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listOnlyAllDeptosEntitiesByUserVerifyAllDeptos(){
        $query = "
            SELECT 'S' as all_deptos  FROM dual
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }  
    public static function listDeptosEntitiesByUserVerifyAllDeptos($id_entidad, $id_persona){
        $query = "
        select coalesce(max(all_deptos), 'N') as all_deptos from (
            SELECT 'S' as all_deptos  FROM dual
            WHERE (SELECT count(*) FROM CONTA_ENTIDAD_DEPTO WHERE ID_ENTIDAD = $id_entidad AND ES_EMPRESA = '1' )=
            (SELECT count(*)
                    FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B 
                    WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                    AND A.ID_DEPTO = B.ID_DEPTO
                    AND B.ID_ENTIDAD = $id_entidad
                    AND B.ID = $id_persona
                    AND ES_EMPRESA = '1'
                    AND B.ACTIVO = '1')
            ) a
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    

    public static function listEntitiesEnterpriseByUser($id_empresa, $id_persona, $withAllOoption){

        $query = "SELECT 0 AS id, 'Todos' AS name, 0 as estado FROM dual
        WHERE (SELECT count(*) FROM CONTA_ENTIDAD
        WHERE ID_EMPRESA = $id_empresa) = (SELECT count(*) FROM CONTA_ENTIDAD_USUARIO u, CONTA_ENTIDAD e
        WHERE u.ID_ENTIDAD = e.ID_ENTIDAD
        AND e.ID_EMPRESA = $id_empresa
        AND u.ID_PERSONA = $id_persona)
        AND (SELECT count(*) FROM CONTA_ENTIDAD WHERE ID_EMPRESA = $id_empresa) > 1
        and 1 = $withAllOoption
        UNION ALL
        SELECT e.id_entidad as id, e.nombre as name, u.estado
        FROM conta_entidad e 
        INNER JOIN CONTA_ENTIDAD_USUARIO u
        ON e.ID_ENTIDAD = u.ID_ENTIDAD
        WHERE e.ID_EMPRESA = $id_empresa
        AND u.ID_PERSONA = $id_persona";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listEntitiesEnterpriseVerifyAllEntities($id_empresa, $id_persona){
        $query = "
        select coalesce(max(all_entities), 'N') as all_entities from (
            SELECT 'S' as all_entities  FROM dual
            WHERE (SELECT count(1) FROM CONTA_ENTIDAD
            WHERE ID_EMPRESA = $id_empresa AND ID_ENTIDAD <> 9119) = (SELECT count(1) FROM CONTA_ENTIDAD_USUARIO u, CONTA_ENTIDAD e
            WHERE u.ID_ENTIDAD = e.ID_ENTIDAD
            AND e.ID_EMPRESA = $id_empresa
            AND u.ID_PERSONA = $id_persona)
            ) a
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    
    
    public static function entityByType_new($id_persona)
    {
        /*$getEntity2 = DB::table('conta_entidad e')
            ->join('tipo_entidad t', 'e.ID_TIPOENTIDAD', '=', 't.ID_TIPOENTIDAD')
            ->select('t.nombre as tipo', 'e.id_entidad as id', 'e.nombre as name','e.ID_TIPOENTIDAD')
            ->orderBy('t.orden')->get();

        return $getEntity2;*/
        $query = "SELECT 
                B.NOMBRE TIPO, A.ID_ENTIDAD ID, A.NOMBRE NAME, A.ID_TIPOENTIDAD , 
                DECODE(NVL((SELECT ID_ENTIDAD FROM VW_APS_PLANILLA X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_PERSONA = C.ID_PERSONA GROUP BY ID_ENTIDAD),''),'',0,1) DEFAULT_SELECT
                FROM conta_entidad A, tipo_entidad B, CONTA_ENTIDAD_USUARIO C
                WHERE A. ID_TIPOENTIDAD = B.ID_TIPOENTIDAD
                AND A.ID_ENTIDAD = C.ID_ENTIDAD
                AND C.ID_PERSONA = $id_persona 
                AND NOT (
                    -- SELECT max(lur.ID_ROL)  FROM LAMB_USUARIO_ROL lur 
                    -- INNER JOIN LAMB_ROL_MODULO lrm ON lur.ID_ROL = lrm.ID_ROL 
                    -- WHERE lur.ID_PERSONA = C.ID_PERSONA
                    -- AND lur.ID_ENTIDAD = C.ID_ENTIDAD
                    -- AND EXISTS  (
                    --     SELECT lm.ID_MODULO
                    --     FROM   LAMB_MODULO lm
                    --     WHERE lm.ID_MODULO = lrm.ID_MODULO 
                    --     START WITH lm.ID_MODULO = 2 -- FIJO es el id del super proyecto Lamb Financial
                    --     CONNECT BY lm.id_padre = prior lm.ID_MODULO
                    -- ) 
                    WITH Hierarchy AS (
                        SELECT lm.ID_MODULO 
                        FROM LAMB_MODULO lm
                        START WITH lm.ID_MODULO = 2
                        CONNECT BY lm.id_padre = PRIOR lm.ID_MODULO
                    )
                    SELECT 
                        MAX(lur.ID_ROL)
                    FROM 
                        LAMB_USUARIO_ROL lur
                    INNER JOIN 
                        LAMB_ROL_MODULO lrm ON lur.ID_ROL = lrm.ID_ROL
                    WHERE 
                        lur.ID_PERSONA = C.ID_PERSONA
                        AND lur.ID_ENTIDAD = C.ID_ENTIDAD
                        AND lrm.ID_MODULO IN (SELECT ID_MODULO FROM Hierarchy)
                ) IS NULL 
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function department($entity)
    {
        $getDepartment = DB::table('VW_CONTA_ENTIDAD_DEPTO A')
            ->where('A.ID_ENTIDAD', $entity)
            ->select('A.ID_ENTIDAD', 'A.ID_DEPTO', 'A.NOM_DEPARTAMENTO AS NOMBRE')->orderBy('A.ID_DEPTO', 'ASC')
            ->get();

        return $getDepartment;
    }
    public static function restrictions($queryAccounts)
    {
        $query = "SELECT ID_RESTRICCION as id,
                NOMBRE AS name
            FROM CONTA_RESTRICCION
            $queryAccounts
            ORDER BY ID_RESTRICCION";

        $oQuery = DB::select($query);

        return $oQuery;
    }    
    public static function type_current_accounts($queryAccounts)
    {

        $query = "SELECT ID_TIPOCTACTE as id,
                NOMBRE AS name
            FROM TIPO_CTA_CORRIENTE
            $queryAccounts
            ORDER BY ID_TIPOCTACTE";

        $oQuery = DB::select($query);

        return $oQuery;

    }
    public static function current_accounts($entity, $queryTypeCurrentAccounts,$queryAccounts)
    {
                $query = "SELECT ID_TIPOCTACTE,
                ID_CTACTE, NOMBRE
            FROM CONTA_ENTIDAD_CTA_CTE
            WHERE ID_ENTIDAD = $entity
            $queryTypeCurrentAccounts
            $queryAccounts
            ORDER BY ID_TIPOCTACTE,ID_CTACTE";
        $oQuery = DB::select($query);

        return $oQuery;
    }
    public static function get_rol($id_rol)
    {
        $getRol = DB::table('LAMB_ROL')->select('ID_ROL','NOMBRE','ESTADO')->where('ID_ROL', $id_rol)->get();        
        return $getRol;
    }
    public static function list_rol()
    {
        $listRol = DB::table('LAMB_ROL')->select('ID_ROL','NOMBRE','ESTADO')->orderBy('ID_ROL')->get();        
        return $listRol;
    }
    public static function yearActivo($entity){
        // dd($entity);
        $getYear = DB::table('CONTA_ENTIDAD_ANHO_CONFIG')->select('ID_ANHO')->where('ID_ENTIDAD',$entity)->where('ACTIVO','1')->orderBy('ID_ANHO', 'DESC')->get();

        return $getYear;
    }

    // function to proceses documentary representative    / DB::raw('CONCAT(P.PATERNO,", ",P.MATERNO) as lastName'),

    public static function getListTipoDocRepre() {
        $getTipoDocRe = DB::table('TIPO_REPRESENTANTE_DOC')->select('ID_TIPOREDOC', 'NOMBRE')->where('ACTIVO','1')->orderBy('ID_TIPOREDOC', 'ASC')->get();
        return $getTipoDocRe;
    }

    public static function listDocRepresentativeNew($request) {
        $entity = $request->query('entity');
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');
        //print('entity'.$entity);

        $activo='Activo';
        $inactivo='Inactivo';
        if($entity !='' or $entity != NULL ){
            $getTipoDocRe = DB::table('CONTA_ENTIDAD_DEPTO_LEGAL as CEDL')
            ->select(
            'CEDL.*',
            DB::raw('FC_OBT_NAME_ENTIDAD_DEPTO(CEDL.ID_ENTIDAD, CEDL.ID_DEPTO) as NAME_ENTI_DEP'),
            'P.PATERNO', 
            'P.MATERNO',
            'P.NOMBRE as name',
            'TRD.NOMBRE',
            DB::raw("(CASE WHEN CEDL.ES_ACTIVO = '1'  THEN '".$activo."' ELSE '".$inactivo."' END) as state"))
            ->join('TIPO_REPRESENTANTE_DOC as TRD', 'CEDL.ID_TIPOREDOC', '=', 'TRD.ID_TIPOREDOC')
            ->join('MOISES.PERSONA as P', 'CEDL.ID_PERSONA', '=', 'P.ID_PERSONA')
            ->where('CEDL.ID_ENTIDAD','=',$entity)
            ->where('CEDL.ES_ACTIVO','=','1')
            ->paginate($pageSize);
        }else{
            $getTipoDocRe = DB::table('CONTA_ENTIDAD_DEPTO_LEGAL as CEDL')
            ->select(
            'CEDL.*',
            DB::raw('FC_OBT_NAME_ENTIDAD_DEPTO(CEDL.ID_ENTIDAD, CEDL.ID_DEPTO) as NAME_ENTI_DEP'),
            'P.PATERNO', 
            'P.MATERNO',
            'P.NOMBRE as name',
            'TRD.NOMBRE',
            DB::raw("(CASE WHEN CEDL.ES_ACTIVO = '1'  THEN '".$activo."' ELSE '".$inactivo."' END) as state"))
            ->join('TIPO_REPRESENTANTE_DOC as TRD', 'CEDL.ID_TIPOREDOC', '=', 'TRD.ID_TIPOREDOC')
            ->join('MOISES.PERSONA as P', 'CEDL.ID_PERSONA', '=', 'P.ID_PERSONA')
            ->where('CEDL.ES_ACTIVO','=','1')
            ->paginate($pageSize);
        }
        return $getTipoDocRe;
    }

    public static function listDocRepresentativeFilter($request) {
        $getTipoDocRe = DB::table('CONTA_ENTIDAD_DEPTO_LEGAL AS CEDL')
        ->select('CEDL.*', DB::raw('FC_OBT_NAME_ENTIDAD_DEPTO(CEDL.ID_ENTIDAD, CEDL.ID_DEPTO) as NAME_ENTI_DEP'), 'TRD.NOMBRE')
        ->join('TIPO_REPRESENTANTE_DOC as TRD', 'CEDL.ID_TIPOREDOC', '=', 'TRD.ID_TIPOREDOC')
        ->paginate(10);
        return $getTipoDocRe;
    }

    public static function listDeptoData($request) {
        $entity = $request->query('id_entity');
        $search = $request->query('dato');
        $listDepto = DB::table('CONTA_ENTIDAD_DEPTO')
        ->where("ID_ENTIDAD", "=",$entity)
        ->where("ES_ACTIVO", "=",1)
        ->whereRaw("UPPER(NOMBRE) LIKE UPPER('%".$search."%')")
        ->select('*')
        ->get();
        return $listDepto;
    }

    public static function addDocRepresentative($request) {
        $ret='OK';
        $entity = $request->id_entity;
        $id_depto = $request->id_depto;
        $address = $request->address;
        $city = $request->city;
        $telephone = $request->telephone;
        $id_person = $request->id_person;
        $id_tiporedoc = $request->id_tiporedoc;
        $state = $request->state;

        if($id_depto === NULL or $id_depto ==='' ){
            $id_depto = 0;
        }

        $sql = "SELECT  ID_ENTIDEPLEGAL From CONTA_ENTIDAD_DEPTO_LEGAL 
        where ID_ENTIDAD = ".$entity." AND ID_DEPTO = ".$id_depto."
        AND ID_PERSONA = ".$id_person." AND ID_TIPOREDOC = ".$id_tiporedoc." AND FEC_FIN IS NULL
        AND ES_ACTIVO = '1'";
        
        $oQuery = DB::select($sql);
        $contar = 0;
        foreach ($oQuery as $row){
            $contar++;
        }
        if($contar == 0){
            DB::table('CONTA_ENTIDAD_DEPTO_LEGAL')->insert(
                array('ID_ENTIDAD' => $entity,
                    'ID_DEPTO' => $id_depto, 
                    'DIRECCION_LEGAL' => $address,
                    'NOM_CIUDAD' => $city,
                    'TELEPHONE' => $telephone,
                    'ID_PERSONA' => $id_person,
                    'ID_TIPOREDOC' => $id_tiporedoc,
                    'FEC_INICIO' => Carbon::now(),
                    'ES_ACTIVO' => $state,
                    )
            );
        }else{
            $ret="El responsable con esa entidad o departamento ya esta registrado"; 
        }

        return $ret;

    }

    public static function showDocRepresentativeId($id_entideplegal){
        
        $sql = "SELECT  
                    a.ID_ENTIDEPLEGAL, 
                    a.ID_ENTIDAD,
                    a.ID_DEPTO,
                    FC_OBT_NAME_ENTIDAD_DEPTO(a.ID_ENTIDAD, a.ID_DEPTO) as NAME_ENTI_DEP, 
                    a.DIRECCION_LEGAL,
                    a.nom_ciudad,
                    a.telephone,
                    a.ID_PERSONA,
                    p.paterno||' '||p.materno||' '||p.nombre as representante,
                    a.ID_TIPOREDOC,
                    a.ES_ACTIVO
        From CONTA_ENTIDAD_DEPTO_LEGAL a, MOISES.PERSONA p
        where a.ID_PERSONA=p.ID_PERSONA AND a.ID_ENTIDEPLEGAL = ".$id_entideplegal;
        $query = DB::select($sql);
        return $query;
    }

    public static function editDocRepresentative($id_entideplegal,$request){
        $ret='OK';
        $entity = $request->id_entity;
        $id_depto = $request->id_depto;
        $address = $request->address;
        $city = $request->city;
        $telephone = $request->telephone;
        $id_person = $request->id_person;
        $id_tiporedoc = $request->id_tiporedoc;
        $state = $request->state;
        $dates = Carbon::now();
        $fecha_sys=date("Y-m-d H:i:s");

        if($state == '1'){
            $query = "UPDATE CONTA_ENTIDAD_DEPTO_LEGAL SET 
            ID_ENTIDAD = ".$entity.",
            ID_DEPTO = '".$id_depto."', 
            DIRECCION_LEGAL = '".$address."',
            NOM_CIUDAD = '".$city."',
            TELEPHONE = '".$telephone."',
            ID_PERSONA = ".$id_person.",
            ID_TIPOREDOC = ".$id_tiporedoc.",
            ES_ACTIVO = '".$state."'
            WHERE ID_ENTIDEPLEGAL = ".$id_entideplegal;
            DB::update($query);
        }else{

            DB::table('CONTA_ENTIDAD_DEPTO_LEGAL')->where('ID_ENTIDEPLEGAL', $id_entideplegal)->update(
                array('ID_ENTIDAD' => $entity,
                    'ID_DEPTO' => $id_depto, 
                    'DIRECCION_LEGAL' => $address,
                    'NOM_CIUDAD' => $city,
                    'TELEPHONE' => $telephone,
                    'ID_PERSONA' => $id_person,
                    'ID_TIPOREDOC' => $id_tiporedoc,
                    'FEC_FIN' => Carbon::now(),
                    'ES_ACTIVO' => $state,
                    )
            );
        }
        return $ret;
    }

    public static function deleteDocRepresentative($id_entideplegal){
        $query = "DELETE FROM CONTA_ENTIDAD_DEPTO_LEGAL
                WHERE ID_ENTIDEPLEGAL = ".$id_entideplegal;
        DB::delete($query);    
    }

    public static function DocRepresentativeFilters($request) {
        $entity = $request->query('id_entidad');
        $person = $request->query('representative');
        $id_tiporedoc = $request->query('id_tiporedoc');
        $condSql = '';
        if($person != NULL or $person != ''){
            $condSql = "AND a.ID_PERSONA = ".$person;
        }
        

        $sql = "SELECT  a.ID_ENTIDEPLEGAL, a.ID_ENTIDAD, a.ID_DEPTO, 
                        FC_OBT_NAME_ENTIDAD_DEPTO(a.ID_ENTIDAD, a.ID_DEPTO) as NAM_ENTIDAD,
                        a.DIRECCION_LEGAL,
                        a.nom_ciudad,
                        a.telephone,
                        a.ID_PERSONA,
                        p.paterno||' '||p.materno||' '||p.nombre as representante,
                        p.ID_TIPODOCUMENTO,
                        p.num_documento as documento,
                        a.ID_TIPOREDOC,
                        a.ES_ACTIVO
                From CONTA_ENTIDAD_DEPTO_LEGAL a, MOISES.VW_PERSONA_NATURAL_LIGHT p
                where a.ID_PERSONA=p.ID_PERSONA 
                AND a.ID_ENTIDAD =  ".$entity."  AND a.ID_TIPOREDOC = ".$id_tiporedoc." AND p.ID_TIPODOCUMENTO = '1' AND a.ES_ACTIVO = '1'".$condSql;

        $oQuery = DB::select($sql);
        return $oQuery;
    }
    public static function yearActivoAll($entity){
        // dd($entity);
        $getYear = DB::table('CONTA_ENTIDAD_ANHO_CONFIG')->select('ID_ANHO')->where('ID_ENTIDAD',$entity)->orderBy('ID_ANHO', 'DESC')->get();

        return $getYear;
    }
    public static function getProcedure(){
        return Procedure::select(
            'id_procedure',
            'nombre',
            'descripcion',
            'estado',
            'dc'
        )->get();
    }
}