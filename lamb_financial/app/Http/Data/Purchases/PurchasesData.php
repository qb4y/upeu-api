<?php
namespace App\Http\Data\Purchases;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use PDO;
use App\Http\Data\Orders\ComunData;
use App\Http\Controllers\Storage\StorageController;

class PurchasesData extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function addProject($data)
    {
        DB::table('proyecto')->insert($data);
        return $data;
    }
    
    public static function listTypeProject($id_depto, $id_entidad){
        $query = "select
        ID_TIPOPROYECTO,
        NOMBRE
        from TIPO_PROYECTO";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listProject($id_depto, $id_entidad){
        $query = "select 
        a.ID_PROYECTO,
        a.ID_ENTIDAD,
        a.ID_DEPTO,
        a.ID_TIPOPROYECTO,
        a.NOMBRE,
        a.NRO_ACUERDO,
        a.FECHA_ACUERDO,
        a.PRESUPUESTO,
        a.CUENTA,
        a.MODALIDAD_EJECUCION,
        decode(MODALIDAD_EJECUCION,'1','Contrata','2','Administracion Directa','3','Mixta','') D_MODALIDAD_EJECUCION,
        a.TIPO_FINANCIAMIENTO,
        decode(TIPO_FINANCIAMIENTO,'1','Ingresos Generados propios','2','Endeudamiento','3','Mixto','')D_TIPO_FINANCIAMIENTO,
        a.ESTADO,
        a.REG_CONT,
        decode(REG_CONT,'1','Activo','2','Gasto') D_REG_CONT,
        a.CTA_CTE,
        a.SUSTENTO,
        a.EXTENCION,
        nvl((select sum(presupuesto) from proyecto_acuerdo where id_proyecto = a.id_proyecto),0) presupuesto_total
        from proyecto a
        where a.id_entidad = '".$id_entidad."' and a.id_depto = '".$id_depto."'";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listComprasByProyecto( $idProyecto){
        $query = "SELECT
        p1.nombre,
        b.id_pcompra,
        b.id_compra,
        b.id_proveedor,
        b.serie,
        b.numero,
        c.fecha_doc,
        c.fecha_provision,
        b.importe
    FROM
             proyecto_compra a
        INNER JOIN pedido_compra     b ON b.id_pcompra = a.id_pcompra
        LEFT JOIN compra            c ON c.id_compra = b.id_compra
        LEFT JOIN moises.persona    p1 ON p1.id_persona = b.id_proveedor
    WHERE a.id_proyecto = '".$idProyecto."'
    ORDER BY c.id_compra";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showProject($id_project){
        $query = "select 
        a.ID_PROYECTO,
        a.ID_ENTIDAD,
        a.ID_DEPTO,
        a.ID_TIPOPROYECTO,
        a.NOMBRE,
        a.NRO_ACUERDO,
        a.FECHA_ACUERDO,
        a.PRESUPUESTO,
        a.CUENTA,
        a.MODALIDAD_EJECUCION,
        decode(MODALIDAD_EJECUCION,'1','Contrata','2','Administracion Directa','3','Mixta','') D_MODALIDAD_EJECUCION,
        a.TIPO_FINANCIAMIENTO,
        decode(TIPO_FINANCIAMIENTO,'1','Ingresos Generados propios','2','Endeudamiento','3','Mixto','')D_TIPO_FINANCIAMIENTO,
        a.ESTADO,
        a.REG_CONT,
        decode(REG_CONT,'1','Activo','2','Gasto') D_REG_CONT,
        a.CTA_CTE,
        a.SUSTENTO,
        a.EXTENCION,
        nvl((select sum(presupuesto) from proyecto_acuerdo where id_proyecto = a.id_proyecto),0) presupuesto_total
        from proyecto a
        where a.ID_PROYECTO = '".$id_project."' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateStateProject($idProject)
    { 
        $proj = DB::table('proyecto')->where('id_proyecto', $idProject)->first();
        $obj = DB::table('proyecto')
            ->where('ID_PROYECTO', $idProject)
            ->update(['estado' => (($proj->estado == 1) ? '0' : '1')]);
        return $obj;
    }
    public static function updateProject($data,$idProject)
    {      
        $obj = DB::table('proyecto')
        ->where('ID_PROYECTO', $idProject)
        ->update($data);
        return $obj;
    }
    
    public static function listProjectAcuerdos($idProject){
        $query = "select
        ID_PDETALLE,
        NRO_ACUERDO,
        FECHA_ACUERDO,
        PRESUPUESTO,
        SUSTENTO,
        EXTENCION,
        ID_PROYECTO from proyecto_acuerdo  where id_proyecto= '".$idProject."' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addAcuerdo($data)
    {
        DB::table('proyecto_acuerdo')->insert($data);
        return $data;
    }
    public static function showOrdersView($id_pedido){
        $qry = "SELECT 
                        ID_AREAORIGEN,ID_AREADESTINO ,
                        PKG_ORDERS.FC_NOMBRE_AREA (ID_AREAORIGEN) NOMBRE_AREAORIGEN,
                        PKG_ORDERS.FC_NOMBRE_AREA (ID_AREADESTINO) NOMBRE_AREADESTINO
                FROM PEDIDO_REGISTRO
                WHERE ID_PEDIDO = ".$id_pedido." ";
        $result = DB::connection('oracle')->select($qry);
        return $result;
    }
    public static function zzz($id_depto, $id_entidad, $id_persona) /* borrar -- 2019-01-03 */
    {
        $serie = 'F210';
        $numero = '00005881';
        $id_comprobante  = '01';
        $id_proveedor = '23';
        $id_compra = '';
        $query = DB::table('compra')
            // ->select('id_compra')
            ->where('serie','=',$serie)
            ->where('numero','=',$numero)
            ->where('id_comprobante','=',$id_comprobante)
            ->where('id_proveedor','=',$id_proveedor);
        if($id_compra != "")
        {
            $query->where('id_compra','!=',$id_compra);
        }
        $rows = $query->get();
        dd($rows);
        // **
        $query = DB::table('compra_plantilla')
            ->join('tipo_plantilla', 'compra_plantilla.id_tipoplantilla', '=', 'tipo_plantilla.id_tipoplantilla')
            ->select(
                DB::raw(
                    "
                    compra_plantilla.id_plantilla
                    , compra_plantilla.id_tipoplantilla
                    , compra_plantilla.fecha
                    , compra_plantilla.nombre
                    , tipo_plantilla.nombre as nombre_tipoplantilla
                    , compra_plantilla.id_depto
                    "
                )
            )
            ->where('compra_plantilla.id_entidad', '=', $id_entidad);
            $query->where(function($query2) use ($id_depto, $id_entidad, $id_persona) {
                $query2
                    ->whereExists(function($query3) use ($id_depto) {
                        $query3->select(DB::raw(1))
                          ->from('compra_entidad_depto_plantilla')
                          ->whereRaw('compra_entidad_depto_plantilla.id_plantilla = compra_plantilla.id_plantilla and compra_entidad_depto_plantilla.id_depto = ?',array($id_depto)
                        );
                    })
                    ->orWhere(function($query22) use ($id_depto, $id_entidad, $id_persona) {
                        $query22
                            ->whereExists(function($query33) use ($id_depto, $id_entidad, $id_persona) {
                                $query33->select(DB::raw(1))
                                    ->from('LAMB_USERS_DEPTO')
                                    ->whereRaw('compra_plantilla.id_depto=LAMB_USERS_DEPTO.id_depto and LAMB_USERS_DEPTO.ID_ENTIDAD = ? and LAMB_USERS_DEPTO.ID = ?',array($id_entidad, $id_persona));
                            });
                    });
            })
            ->orderBy('compra_plantilla.id_plantilla');
            $si = $query->get();
        return $si;
        // $query = DB::select("select nextval('sq_statementcont_seq')");
        $results = DB::select(DB::raw("SELECT SQ_PEDIDO_ASIENTO_ID.NEXTVAL ID FROM DUAL"));
        /* $results = DB::select( DB::raw("SELECT 5 hora FROM DUAL WHERE 4 = :somevariable"), array(
            'somevariable' => 4,
          )); */
        dd($results,$results[0]->id);
        $query = DB::table('DUAL')
            ->select(DB::raw("SQ_PEDIDO_ASIENTO_ID.NEXTVAL"))
            ->first();
        dd($query);
        $error = 0;
        $msg_error = '';
        $objReturn = [];
        try {
            $out_conteo = 0;
            $id_pedido = 56;
            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin SP_INVENTORIES_IS_SERVICE(
            :IN_ID_ARTICULO, 
            :OUT_CONTEO, 
            :OUT_CODIGO, 
            :OUT_RESPUESTA
            );
            end;");
            $stmt->bindParam(':IN_ID_ARTICULO', $id_pedido, PDO::PARAM_INT);
            $stmt->bindParam(':OUT_CONTEO', $out_conteo, PDO::PARAM_INT);
            // $stmt->bindParam(':OUT_CODIGO', $out_codigo, PDO::PARAM_STR);
            // $stmt->bindParam(':OUT_RESPUESTA', $out_respuesta, PDO::PARAM_STR);

            $stmt->execute();
            $jResponse = [];
            $jResponse = [
            "out_conteo" => $out_conteo,
            // "out_codigo" => $out_codigo,
            // "out_respuesta" => $out_respuesta
            ];
            RETURN $objReturn;
        } catch(Exception $e){
            $jResponse = [];
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $error = "202";
            RETURN $jResponse;
        }
    }
    /* listMyOperationsPending */
    public static function listMyOperationsPending() /* borrar -- 2019-01-03 */
    {
        $query = "
        select * 
        from ( 
        select 
            pedreg.id_pedido 
            ,pedreg.motivo 
            ,tipped.nombre tipo_nombre 
            ,case 
              -- when 74 then 'Provisionando' 
              when prorun.id_paso_actual=89 then 'iniciado' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 'en proceso' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 'en ejecucion' 
              when prorun.id_paso_actual=74 then 'provisionando' 
              when prorun.id_paso_actual=65 then 'fin' 
              when prorun.id_paso_actual=67 then 'iniciando' 
              when prorun.id_paso_actual=82 then 'en proceso' 
              when prorun.id_paso_actual=83 then 'en tesoreria' 
              when prorun.id_paso_actual=84 then 'en tesoreria' 
              when prorun.id_paso_actual=85 then 'en consejo'  
              else ' ' 
            end estado_nombre 
            ,case 
              -- when prorun.id_paso_actual=89 then ' ' 
              -- when prorun.id_paso_actual=67 then ' ' 
              -- when prorun.id_paso_actual=82 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=68 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=83 then 'fa-chevron-right' -- añadido para abajo
              -- when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              when prorun.id_paso_actual=83 then 'fa-chevron-right' 
              when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else 'none' 
            end accion_icon 
            ,case 
              -- when prorun.id_paso_actual=89 and pedreg.estado=0 then ' ' 
              -- when prorun.id_paso_actual=67 and pedreg.estado=0 then ' ' 
              -- when prorun.id_paso_actual=82 and pedreg.estado=0 then '13' -- '/purchases/operations/request-purchase/step-attach' 
              -- when prorun.id_paso_actual=68 and pedreg.estado=0 then '5' -- '/purchases/operations/receipt/step-preprovision' 
              -- when prorun.id_paso_actual=83 and pedreg.estado=0 then '16' -- '/purchases/operations/pending-vob' -- añadido para abajo 
              -- when prorun.id_paso_actual=84 and pedreg.estado=0 then '17A' -- '/purchases/operations/pending-vob/agreement' 
              -- when prorun.id_paso_actual=85 and pedreg.estado=0 then '19' -- '/purchases/operations/pending-approval' 
              -- when prorun.id_paso_actual=74 and pedreg.estado=0 then '22' -- '/purchases/provisions' 
              when prorun.id_paso_actual=83 and pedreg.estado=0 then '16' -- '/purchases/operations/pending-vob' 
              when prorun.id_paso_actual=84 and pedreg.estado=0 then '17A' -- '/purchases/operations/pending-vob/agreement' 
              when prorun.id_paso_actual=85 and pedreg.estado=0 then '19' -- '/purchases/operations/pending-approval' 
              when prorun.id_paso_actual=74 and pedreg.estado=0 then '22' -- '/purchases/provisions' 
              else ' ' 
            end accion_next 
            ,case 
              when prorun.id_paso_actual=89 then 30 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 60 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 70 
              when prorun.id_paso_actual=74 then 90 
              when prorun.id_paso_actual=65 then 100 
              when prorun.id_paso_actual=67 then 15 
              when prorun.id_paso_actual=82 then 20 
              when prorun.id_paso_actual=83 then 40 
              when prorun.id_paso_actual=84 then 60 
              when prorun.id_paso_actual=85 then 50  
              else 0 
            end porcentaje 
        from 
            pedido_registro pedreg 
            inner join  
            tipo_pedido tipped 
            on pedreg.id_tipopedido=tipped.id_tipopedido 
            inner join 
            process_run prorun 
            on pedreg.id_pedido=prorun.id_operacion 
            and prorun.id_proceso=6 
        where prorun.id_paso_actual!=65  and prorun.estado='0' 
            and prorun.id_paso_actual in (83,84)
        order by pedreg.id_pedido desc 
        ) ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listMyOperations($tipo, $id_user)
    {
        if($tipo == '2')
        {
            $twhere = " and prorun.id_paso_actual!=65  and prorun.estado='0' ";
        }
        else
        {
            $twhere = "";
        }
        $query = "
        select * 
        from ( 
        select 
            pedreg.id_pedido 
            ,pedreg.motivo 
            ,tipped.nombre tipo_nombre 
            ,case 
              -- when 74 then 'Provisionando' 
              when prorun.id_paso_actual=89 then 'iniciado' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 'en proceso' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 'en ejecucion' 
              when prorun.id_paso_actual=74 then 'provisionando' 
              when prorun.id_paso_actual=65 then 'fin' 
              when prorun.id_paso_actual=67 then 'iniciando' 
              when prorun.id_paso_actual=82 then 'en proceso' 
              when prorun.id_paso_actual=83 then 'en tesoreria' 
              when prorun.id_paso_actual=84 then 'en tesoreria' 
              when prorun.id_paso_actual=85 then 'en consejo'  
              else ' ' 
            end estado_nombre 
            ,case 
              when prorun.id_paso_actual=89 then ' ' 
              when prorun.id_paso_actual=67 then ' ' 
              when prorun.id_paso_actual=82 then 'fa-chevron-right' 
              when prorun.id_paso_actual=68 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=83 then 'fa-chevron-right' -- añadido para abajo
              -- when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else 'none' 
            end accion_icon 
            ,case 
              when prorun.id_paso_actual=89 and pedreg.estado=0 then ' ' 
              when prorun.id_paso_actual=67 and pedreg.estado=0 then ' ' 
              when prorun.id_paso_actual=82 and pedreg.estado=0 then '13' -- '/purchases/operations/request-purchase/step-attach' 
              when prorun.id_paso_actual=68 and pedreg.estado=0 then '5' -- '/purchases/operations/receipt/step-preprovision' 
              -- when prorun.id_paso_actual=83 and pedreg.estado=0 then '16' -- '/purchases/operations/pending-vob' -- añadido para abajo 
              -- when prorun.id_paso_actual=84 and pedreg.estado=0 then '17A' -- '/purchases/operations/pending-vob/agreement' 
              -- when prorun.id_paso_actual=85 and pedreg.estado=0 then '19' -- '/purchases/operations/pending-approval' 
              -- when prorun.id_paso_actual=74 and pedreg.estado=0 then '22' -- '/purchases/provisions' 
              else ' ' 
            end accion_next 
            ,case 
              when prorun.id_paso_actual=89 then 30 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 60 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 70 
              when prorun.id_paso_actual=74 then 90 
              when prorun.id_paso_actual=65 then 100 
              when prorun.id_paso_actual=67 then 15 
              when prorun.id_paso_actual=82 then 20 
              when prorun.id_paso_actual=83 then 40 
              when prorun.id_paso_actual=84 then 60 
              when prorun.id_paso_actual=85 then 50  
              else 0 
            end porcentaje 
            ,case -- NUEVO 
              -- when prorun.id_paso_actual=89 then ' ' 
              -- when prorun.id_paso_actual=67 then ' ' 
              -- when prorun.id_paso_actual=82 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=68 then 'fa-chevron-right' 
              when prorun.id_paso_actual=83 then 'fa-chevron-right' -- añadido para abajo
              when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else ' ' 
            end accion_tesoreria_icon 
            ,case -- NUEVO 
              -- when prorun.id_paso_actual=89 then ' ' 
              -- when prorun.id_paso_actual=67 then ' ' 
              -- when prorun.id_paso_actual=82 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=68 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=83 then 'fa-chevron-right' -- añadido para abajo
              -- when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else ' ' 
            end accion_consejo_icon 
        from 
            pedido_registro pedreg 
            inner join  
            tipo_pedido tipped 
            on pedreg.id_tipopedido=tipped.id_tipopedido 
            inner join 
            process_run prorun 
            on pedreg.id_pedido=prorun.id_operacion 
            and prorun.id_proceso=6 
        where id_persona=$id_user 
        ".$twhere." 
        order by pedreg.id_pedido desc 
        ) ";
        if($tipo == '2')
        {
            $query = $query."where ROWNUM <= 5 ";
        }
        $oQuery = DB::select($query);
        return $oQuery;
    }
    // Deprecated
    public static function addPedidoRegistro($data)
    {
        DB::table('pedido_registro')->insert($data);
        return $data;
    }

    public static function addRequestRegistryBK($data)
    {
        try
        {
            $result = DB::table('pedido_registro')->insert($data);
        }
        catch(Exception $e)
        {
            die($e->getMessage());
            $result = false;
        }
        return $result;
    }
    public static function addRequestRegistry($data){
        $error      = 0;
        $msg_error  = '';
        $p_id_pedido       = null;
        try {
            for($x=1 ; $x <= 200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("BEGIN PKG_ORDERS.SP_INSERT_PEDIDO(
                :P_ID_ENTIDAD,
                :P_ID_DEPTO,
                :P_ID_ANHO,
                :P_ID_MES,
                :P_ID_PERSONA,
                :P_ID_TIPOPEDIDO,
                :P_ID_AREAORIGEN,
                :P_ID_AREADESTINO,
                :P_ID_GASTO,
                :P_FECHA_ENTREGA,
                :P_MOTIVO,
                :P_ESTADO,
                :P_ID_PEDIDO,
                :P_ERROR,
                :P_MSGERROR
                );
                END;");
                $stmt->bindParam(':P_ID_ENTIDAD', $data["id_entidad"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $data["id_depto"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ANHO', $data["id_anho"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_MES', $data["id_mes"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_TIPOPEDIDO', $data["id_tipopedido"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_AREAORIGEN', $data["id_areaorigen"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_AREADESTINO', $data["id_areadestino"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_GASTO', $data["id_gasto"], PDO::PARAM_INT);
                $stmt->bindParam(':P_FECHA_ENTREGA', $data["fecha_entrega"], PDO::PARAM_STR);
                $stmt->bindParam(':P_MOTIVO', $data["motivo"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ESTADO', $data["estado"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_PEDIDO', $p_id_pedido, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['id_pedido']   = $p_id_pedido;
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            $objReturn['success'] = true;
            //return $result = true;
        }catch(Exception $e){
             $objReturn['id_pedido']   = null;
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error.", ORA: ".$e->getMessage();
            $objReturn['success'] = false;
            //return false;
        }
        return $objReturn;
    }
    public static function updateRequestRegistry($data, $id_pedido)
    {
        try
        {
            $result = DB::table('pedido_registro')
                ->where('id_pedido', $id_pedido)
                ->update($data);
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }
    public static function deleteRequestRegistry($id_pedido)
    {
        try
        {
            $result = DB::table('pedido_registro')
                ->where('id_pedido', '=', $id_pedido)
                ->delete();
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }
    // deprecated
    public static function addPedidoCompra($data)
    {
        DB::table('pedido_compra')->insert($data);
        return $data;
    }
    public static function getMax($tabla,$campo)
    {
        $valor = DB::table($tabla)->max($campo);
        return $valor;
    }
    /* PEDIDO_ASIENTO */
    /* .PEDIDO_REGISTRO */
    public static function showOrdersRegisters($id_entidad,$id_pedido)
    {
        $query = DB::table('pedido_registro')
            ->select(
                DB::raw(
                    "
                    pedido_registro.id_pedido
                    , pedido_registro.id_tipopedido
                    , (select nombre from tipo_pedido where tipo_pedido.id_tipopedido=pedido_registro.id_tipopedido) as nombre_tipopedido
                    , pedido_registro.id_actividad
                    , (select nombre from psto_actividad where psto_actividad.id_actividad=pedido_registro.id_actividad) as nombre_actividad
                    , pedido_registro.id_areaorigen
                    , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areaorigen) nombre_areaorigen
                    , pedido_registro.id_areadestino
                    , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areadestino) nombre_areadestino
                    , pedido_registro.numero
                    , pedido_registro.acuerdo
                    , TO_CHAR(pedido_registro.fecha_pedido,'YYYY-MM-DD') fecha_pedido
                    , TO_CHAR(pedido_registro.fecha_entrega,'YYYY-MM-DD') fecha_entrega
                    , pedido_registro.motivo
                    , FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'0') as llave
                    "
                )
            )
            ->where("id_entidad",$id_entidad)
            ->where("id_pedido",$id_pedido)
            // ->orderBy('id_pedido',"desc")
            ->first();
        return $query;
    }
    public static function showOrders($id_entidad,$id_pedido)
    {
        $query = DB::table('PEDIDO_REGISTRO')
            ->select(
                DB::raw(
                    "
                    PEDIDO_REGISTRO.id_pedido
                    , PEDIDO_REGISTRO.id_tipopedido
                    , (select nombre from tipo_pedido where tipo_pedido.id_tipopedido=pedido_registro.id_tipopedido) as nombre_tipopedido
                    , PEDIDO_REGISTRO.ID_ACTIVIDAD
                    , (select nombre from psto_actividad where psto_actividad.id_actividad=pedido_registro.id_actividad) as nombre_actividad
                    , PEDIDO_REGISTRO.id_areaorigen
                    , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areaorigen) nombre_areaorigen
                    , PEDIDO_REGISTRO.id_areadestino
                    , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areadestino) nombre_areadestino
                    , PEDIDO_REGISTRO.numero
                    , PEDIDO_REGISTRO.acuerdo
                    , TO_CHAR(PEDIDO_REGISTRO.fecha_pedido,'YYYY-MM-DD') fecha_pedido
                    , TO_CHAR(PEDIDO_REGISTRO.fecha_entrega,'YYYY-MM-DD') fecha_entrega
                    , PEDIDO_REGISTRO.MOTIVO
                    "
                )
            )
            ->where("id_entidad",$id_entidad)
            ->where("id_pedido",$id_pedido)
            ->orderBy('id_pedido',"desc")
            ->first();
        return $query;
    }
    public static function showMyOrders($id_entidad,$id_user,$id_pedido)
    {
        $query = DB::table('pedido_registro')
            ->select(
                DB::raw(
                    "
                    pedido_registro.id_pedido
                    , pedido_registro.id_tipopedido
                    , (select nombre from tipo_pedido where tipo_pedido.id_tipopedido=pedido_registro.id_tipopedido) as nombre_tipopedido
                    , pedido_registro.id_actividad
                    , (select nombre from psto_actividad where psto_actividad.id_actividad=pedido_registro.id_actividad) as nombre_actividad
                    , pedido_registro.id_areaorigen
                    , PKG_ORDERS.FC_NOMBRE_AREA(pedido_registro.id_areaorigen) AS nombre_areaorigen
                    -- , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areaorigen) nombre_areaorigen
                    , pedido_registro.id_areadestino
                    , PKG_ORDERS.FC_NOMBRE_AREA(pedido_registro.id_areadestino) AS nombre_areadestino
                    --, (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areadestino) nombre_areadestino
                    , pedido_registro.numero
                    , pedido_registro.acuerdo
                    , TO_CHAR(pedido_registro.fecha_pedido,'YYYY-MM-DD') fecha_pedido
                    , TO_CHAR(pedido_registro.fecha_entrega,'YYYY-MM-DD') fecha_entrega
                    , pedido_registro.motivo
                    "
                )
            )
            ->where("id_entidad",$id_entidad)
            ->where("id_persona",$id_user)
            ->where("id_pedido",$id_pedido)
            ->orderBy('id_pedido',"desc")
            ->first();
        return $query;
    }
    public static function listOrdersPending($id_entidad,$codigo,$id_persona,$per_page,$ids_paso,$tipo,$id_depto=null,$dato=null, $id_anho=null, $id_mes=null){
        if($tipo == "S"){
            $estado = '1';
        }else if ($tipo == "R") { // Rechazados
            $estado = '3';
        } else {
            $estado = '0';
        }
        $query = DB::table('PEDIDO_REGISTRO')
            ->join('PROCESS_RUN', function ($join) use($id_entidad,$id_depto,$id_persona,$estado,$id_anho,$id_mes) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
                    ->where('PEDIDO_REGISTRO.ID_DEPTO', '=', $id_depto)
                    ->whereraw("PEDIDO_REGISTRO.ID_ANHO = NVL (?, PEDIDO_REGISTRO.ID_ANHO)", [$id_anho])
                    ->whereraw("PEDIDO_REGISTRO.ID_MES = NVL (?, PEDIDO_REGISTRO.ID_MES)", [$id_mes])
                    ->where('PROCESS_RUN.ESTADO', '=', $estado)
                    ->where('PEDIDO_REGISTRO.ESTADO', '=', $estado);
                    //->where('PEDIDO_REGISTRO.ID_PERSONA', '=', $id_persona);
            })
            ->join('PROCESS', function ($join) use($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->join('PROCESS_PASO_ROL', 'PROCESS_RUN.ID_PASO_ACTUAL', '=', 'PROCESS_PASO_ROL.ID_PASO')
            ->join('LAMB_USUARIO_ROL', function ($join) use($id_entidad,$id_persona) {
                $join->on('PROCESS_PASO_ROL.ID_ROL', '=', 'LAMB_USUARIO_ROL.ID_ROL')
                ->where('LAMB_USUARIO_ROL.ID_PERSONA', '=', $id_persona)
                ->where('LAMB_USUARIO_ROL.ID_ENTIDAD', '=', $id_entidad);
            })
            ->leftjoin('PEDIDO_COMPRA', 'PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_COMPRA.ID_PEDIDO');
            //Valida Lista de Mis compras
            if($tipo == "1"){
                $query = $query->where('PEDIDO_REGISTRO.ID_PERSONA', '=', $id_persona);
            }
            if($tipo === "S"){
                $query->join('PEDIDO_COMPRA', 'PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_COMPRA.ID_PEDIDO')
                ->join('COMPRA', function ($join) use($id_entidad,$id_depto,$id_persona) {
                    $join->on('PEDIDO_COMPRA.ID_COMPRA', '=', 'COMPRA.ID_COMPRA')
                    ->where('COMPRA.ID_ENTIDAD', '=', $id_entidad)
                    ->where('COMPRA.ID_DEPTO', '=', $id_depto)
                    ->where('COMPRA.ID_PERSONA', '=', $id_persona)
                    ->where('COMPRA.ESTADO', '=', '1');
                })
                ->leftjoin('ARREGLO', function ($join) use($id_entidad) {
                    $join->on('COMPRA.ID_COMPRA', '=', 'ARREGLO.ID_ORIGEN')
                   ->where('ARREGLO.ID_ENTIDAD', '=', $id_entidad)
                    // ->where('ARREGLO.ID_MODULO', '=', 229); WARNINGGGG
                    ->where('ARREGLO.ID_MODULO', '=', 11); // MODULO DE COMPRAS EN DESARROLO Y PRODUCCIÓN
                });
                $query = $query->select(
                    DB::raw(
                        "
                        pedido_registro.id_pedido
                        , pedido_registro.id_entidad
                        , 'P'||lpad(pedido_registro.id_pedido, 6, '0') pid_pedido
                        , pedido_registro.id_tipopedido
                        , (select nombre from tipo_pedido where tipo_pedido.id_tipopedido=pedido_registro.id_tipopedido) as nombre_tipopedido
                        , pedido_registro.id_actividad
                        , (select nombre from psto_actividad where psto_actividad.id_actividad=pedido_registro.id_actividad) as nombre_actividad
                        , pedido_registro.id_areaorigen
                        --, (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, /* PEDIDO_AREA C, */ CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND /* B.ID_SEDEAREA = C.ID_SEDEAREA AND */ B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND /* C.ACTIVO = '1' AND */ B.ID_SEDEAREA = pedido_registro.id_areaorigen) nombre_areaorigen
                        , pedido_registro.id_areadestino
                        , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areadestino) nombre_areadestino
                        , pedido_registro.numero
                        , pedido_registro.acuerdo
                        , TO_CHAR(pedido_registro.fecha_pedido,'YYYY-MM-DD') fecha_pedido
                        , TO_CHAR(pedido_registro.fecha_entrega,'YYYY-MM-DD') fecha_entrega
                        , pedido_registro.motivo
                        , '40' as estado_porcentaje
                        , FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'0') as accion
                        -- , PROCESS_PASO_ROL.REVISADO
                        -- , '0' REVISADO
                        , (SELECT REVISADO FROM PROCESS_PASO_RUN WHERE PROCESS_RUN.ID_REGISTRO=PROCESS_PASO_RUN.ID_REGISTRO 
                            AND PROCESS_RUN.ID_PASO_ACTUAL=PROCESS_PASO_RUN.ID_PASO AND ESTADO=0) AS REVISADO
                        , FC_LLAVE_PROCESO(PROCESS_RUN.ID_REGISTRO) AS LLAVE
                        , FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) AS LLAVE_PREVIA
                        , PKG_ORDERS.FC_NOMBRE_AREA(PEDIDO_REGISTRO.ID_AREAORIGEN) AS NOMBRE_AREAORIGEN
                        , PKG_ORDERS.FC_DEPTO(PEDIDO_REGISTRO.ID_AREAORIGEN) AS ID_DEPTO
                        , COMPRA.ID_COMPRA
                        , PKG_PURCHASES.FC_RUC(COMPRA.ID_PROVEEDOR) RUC
                        , PKG_PURCHASES.FC_PROVEEDOR(COMPRA.ID_PROVEEDOR) PROVEEDOR
                        , PKG_PURCHASES.FC_FECHA_AUTORIZADO(PROCESS_RUN.ID_REGISTRO) AS FECHA_AUTORIZADO
                        , COMPRA.FECHA_DOC
                        , COMPRA.FECHA_PROVISION 
                        , COMPRA.SERIE||'-'||COMPRA.NUMERO AS SERIE_NUMERO 
                        , COMPRA.IMPORTE
                        , ".$estado." AS ESTADO
                        , ARREGLO.ID_ARREGLO AS ARREGLO
                        , FC_USERNAME(PEDIDO_REGISTRO.ID_PERSONA) AS USUARIO
                        "
                    )
                );
            }else{

                /*if($dato == "PC"){//EVALUA LISTA DE COMPRAS A PROVISIONAR
                    $query->join('PEDIDO_COMPRA', 'PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PEDIDO_COMPRA.ID_PEDIDO');
                }*/
                // PARA PROVISIONAR NO VALIDA EL ACCESO DE AREAS
                //$query->whereraw("PEDIDO_REGISTRO.ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )");
            
                //Para Autorizar las compras
                if($tipo == "x"){
                    //$query->whereraw("PEDIDO_REGISTRO.ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM PEDIDO_AUTORIZA_AREA WHERE ID_ENTIDAD = ".$id_entidad." AND ID_PERSONA = ".$id_persona." AND ID_DEPTO = '".$id_depto."')");
                    $query->whereraw("FC_LLAVE_PROCESO(PROCESS_RUN.ID_REGISTRO) is not null");
                }


                $query = $query->select(
                    DB::raw(
                        "
                        DISTINCT pedido_registro.id_pedido
                        , pedido_registro.id_entidad
                        , 'P'||lpad(pedido_registro.id_pedido, 6, '0') pid_pedido
                        , pedido_registro.id_tipopedido
                        , (select nombre from tipo_pedido where tipo_pedido.id_tipopedido=pedido_registro.id_tipopedido) as nombre_tipopedido
                        , pedido_registro.id_actividad
                        , (select nombre from psto_actividad where psto_actividad.id_actividad=pedido_registro.id_actividad) as nombre_actividad
                        , pedido_registro.id_areaorigen
                        --, (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, /* PEDIDO_AREA C, */ CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND /* B.ID_SEDEAREA = C.ID_SEDEAREA AND */ B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND /* C.ACTIVO = '1' AND */ B.ID_SEDEAREA = pedido_registro.id_areaorigen) nombre_areaorigen
                        , pedido_registro.id_areadestino
                        , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areadestino) nombre_areadestino
                        , pedido_registro.numero
                        , pedido_registro.acuerdo
                        , TO_CHAR(pedido_registro.fecha_pedido,'YYYY-MM-DD') fecha_pedido
                        , TO_CHAR(pedido_registro.fecha_entrega,'YYYY-MM-DD') fecha_entrega
                        , pedido_registro.motivo
                        , '40' as estado_porcentaje
                        , FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'0') as accion
                        -- , PROCESS_PASO_ROL.REVISADO
                        -- , '0' REVISADO
                        , (SELECT REVISADO FROM PROCESS_PASO_RUN WHERE PROCESS_RUN.ID_REGISTRO=PROCESS_PASO_RUN.ID_REGISTRO 
                            AND PROCESS_RUN.ID_PASO_ACTUAL=PROCESS_PASO_RUN.ID_PASO AND ESTADO=0) AS REVISADO
                        , FC_LLAVE_PROCESO(PROCESS_RUN.ID_REGISTRO) AS LLAVE
                        , FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) AS LLAVE_PREVIA
                        , PKG_ORDERS.FC_NOMBRE_AREA(PEDIDO_REGISTRO.ID_AREAORIGEN) AS NOMBRE_AREAORIGEN
                        , PKG_ORDERS.FC_DEPTO(PEDIDO_REGISTRO.ID_AREAORIGEN) AS ID_DEPTO
                        , NULL AS ID_COMPRA
                        , ".$estado." AS ESTADO
                        , null as ARREGLO
                        --, (SELECT SUM(IMPORTE) FROM PEDIDO_DETALLE WHERE PEDIDO_DETALLE.ID_PEDIDO = PEDIDO_REGISTRO.ID_PEDIDO) AS IMPORTE
                        , FC_USERNAME(PEDIDO_REGISTRO.ID_PERSONA) AS USUARIO
                        , PKG_PURCHASES.FC_RUC(PEDIDO_COMPRA.ID_PROVEEDOR) RUC
                        , PKG_PURCHASES.FC_PROVEEDOR(PEDIDO_COMPRA.ID_PROVEEDOR) PROVEEDOR
                        , PEDIDO_COMPRA.SERIE||'-'||PEDIDO_COMPRA.NUMERO AS SERIE_NUMERO 
                        , PEDIDO_COMPRA.IMPORTE
                        , PKG_PURCHASES.FC_AUTORIZADOR(PROCESS_RUN.ID_REGISTRO) AS TESORERO
                        , PKG_PURCHASES.FC_FECHA_AUTORIZADO(PROCESS_RUN.ID_REGISTRO) AS FECHA_AUTORIZADO
                        , PEDIDO_COMPRA.TRAMITE_PAGO
                        , pedido_registro.COMENTARIO
                        , FC_NOMBRE_CLIENTE(PEDIDO_COMPRA.ID_PERSONA) FUNCIONARIO
                        , FC_VALE(PEDIDO_COMPRA.ID_VALE) DATA_VALE
                        , PROCESS_RUN.ID_REGISTRO
                        , (SELECT PROCESS_PASO_RUN.DETALLE FROM PROCESS_PASO_RUN  WHERE PROCESS_PASO_RUN.ID_REGISTRO = PROCESS_RUN.ID_REGISTRO AND PROCESS_PASO_RUN.ESTADO = '0') AS DETALLE_RECHAZADO
                        "
                    )
                );
            }
            if ($tipo != 'R') { // si es diferente de rechazado
                $query = $query->whereIn('PROCESS_RUN.ID_PASO_ACTUAL', $ids_paso);
              }
   
              $query = $query->orderBy('USUARIO');
              $query = $query->orderBy('FECHA_PEDIDO');
              $query = $query->paginate((int)$per_page);

            // $query = $query->whereIn('PROCESS_RUN.ID_PASO_ACTUAL', $ids_paso)
            // ->orderBy('PEDIDO_REGISTRO.id_pedido',"desc")
            // ->paginate((int)$per_page);
        return $query;
    }


    public static function listOrdersPendingSinProceso($id_entidad,$id_depto,$id_persona){
        
        $query = "SELECT A.ID_PEDIDO
                , A.ID_PEDIDO
                , A.ID_ENTIDAD
                , 'P'||lpad(A.ID_PEDIDO, 6, '0') PID_PEDIDO
                , A.id_tipopedido
                , (select x.nombre from tipo_pedido x where x.id_tipopedido=A.id_tipopedido) as nombre_tipopedido
                , A.id_actividad
                , (select x.nombre from psto_actividad x where x.id_actividad=A.id_actividad) as nombre_actividad
                , A.id_areaorigen
                , A.id_areadestino

                , A.numero
                , A.acuerdo
                , TO_CHAR(A.fecha_pedido,'YYYY-MM-DD') fecha_pedido
                , TO_CHAR(A.fecha_entrega,'YYYY-MM-DD') fecha_entrega
                , A.motivo
                , '40' as estado_porcentaje
                , FC_GET_LLAVE_COMPONENTE(B.ID_REGISTRO,B.ID_PASO_ACTUAL,'0') as accion
                , (SELECT REVISADO FROM PROCESS_PASO_RUN X WHERE B.ID_REGISTRO=X.ID_REGISTRO 
                    AND B.ID_PASO_ACTUAL=X.ID_PASO AND ESTADO=0) AS REVISADO

                , FC_LLAVE_PROCESO(B.ID_REGISTRO) AS LLAVE
                , FC_LLAVE_PROCESO_PREVIOUS(B.ID_REGISTRO) AS LLAVE_PREVIA
                , PKG_ORDERS.FC_NOMBRE_AREA(A.ID_AREAORIGEN) AS NOMBRE_AREAORIGEN
                , PKG_ORDERS.FC_DEPTO(A.ID_AREAORIGEN) AS ID_DEPTO
                , NULL AS ID_COMPRA
                , A.ESTADO
                , null as ARREGLO
                , (SELECT SUM(X.IMPORTE) FROM PEDIDO_DETALLE X WHERE X.ID_PEDIDO = A.ID_PEDIDO) AS IMPORTE
                , FC_USERNAME(A.ID_PERSONA) AS USUARIO

                
                FROM PEDIDO_REGISTRO A 
                    INNER JOIN PROCESS_RUN B ON A.ID_PEDIDO=B.ID_OPERACION
                    INNER JOIN PROCESS C ON B.ID_PROCESO=C.ID_PROCESO AND C.CODIGO=7
                WHERE A.ID_ENTIDAD=$id_entidad
                AND A.ID_DEPTO='$id_depto'
                AND A.ID_PERSONA=$id_persona
                AND A.ESTADO='0'
                ORDER BY A.ID_PEDIDO DESC
                ";
                // AND A.ID_AREAORIGEN IN (SELECT ID_SEDEAREA 
                //                         FROM ORG_AREA_RESPONSABLE X WHERE X.ID_PERSONA = $id_persona )
            $oQuery = DB::select($query);
            return $oQuery;
    }

    public static function listProvisionesFinalizadasSinProceso($id_entidad,$id_depto,$id_persona, $request){

        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $filteYear = "";
        $filteMonth = "";
        if (!empty($id_anho)) {
            $filteYear = "and A.id_anho = ".$id_anho."";
        }
        if (!empty($id_mes)) {
            $filteMonth = "and A.id_mes = ".$id_mes."";
        }
            $query = "SELECT A.ID_PEDIDO
                            , A.ID_ENTIDAD
                            , 'P'||lpad(A.id_pedido, 6, '0') pid_pedido
                            , A.id_tipopedido
                            , (select nombre from tipo_pedido where tipo_pedido.id_tipopedido=A.id_tipopedido) as nombre_tipopedido
                            , A.id_actividad
                            , (select nombre from psto_actividad where psto_actividad.id_actividad=A.id_actividad) as nombre_actividad
                            , A.id_areaorigen
                            , A.id_areadestino

                            , A.numero
                            , A.acuerdo
                            , TO_CHAR(A.fecha_pedido,'YYYY-MM-DD') fecha_pedido
                            , TO_CHAR(A.fecha_entrega,'YYYY-MM-DD') fecha_entrega
                            , A.motivo
                            , '100' as estado_porcentaje
                            , FC_GET_LLAVE_COMPONENTE(E.ID_REGISTRO,E.ID_PASO_ACTUAL,'0') as accion
                            , (SELECT REVISADO FROM PROCESS_PASO_RUN WHERE E.ID_REGISTRO=PROCESS_PASO_RUN.ID_REGISTRO 
                                AND E.ID_PASO_ACTUAL=PROCESS_PASO_RUN.ID_PASO AND ESTADO=0) AS REVISADO
                            , FC_LLAVE_PROCESO(E.ID_REGISTRO) AS LLAVE
                            , FC_LLAVE_PROCESO_PREVIOUS(E.ID_REGISTRO) AS LLAVE_PREVIA
                            , PKG_ORDERS.FC_NOMBRE_AREA(A.ID_AREAORIGEN) AS NOMBRE_AREAORIGEN
                            , PKG_ORDERS.FC_DEPTO(A.ID_AREAORIGEN) AS ID_DEPTO
                            , C.ID_COMPRA
                            , A.ESTADO AS ESTADO
                            , D.ID_ARREGLO AS ARREGLO
                            , 0 AS IMPORTE
                            , FC_USERNAME(A.ID_PERSONA) AS USUARIO

                            , C.SERIE AS COMPRA_SERIE
                            , C.NUMERO AS COMPRA_NUMERO
                            , TO_CHAR(C.IMPORTE,'99999999999999999.99') AS COMPRA_IMPORTE
                            , F.NOMBRE_CORTO AS COMPRA_COMP_NAME
                            , TO_CHAR(C.FECHA_DOC,'DD/MM/YYYY') AS COMPRA_FECHA_DOC
                            
                            , PKG_PURCHASES.FC_RUC(C.ID_PROVEEDOR) AS COMPRA_RUC
                            , FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) AS COMPRA_RAZONSOCIAL
                            , C.CORRELATIVO as COMPRA_CORRELATIVO
                            , (G.ID_TIPOASIENTO|| '-' || G.NUMERO || '-' || TO_CHAR(G.FECHA,'DD/MM/YYYY')) as COMPRA_VOUCHER

                            , (SELECT NVL(COUNT(1),0) FROM CAJA_PAGO_COMPRA CPC WHERE CPC.ID_COMPRA=C.ID_COMPRA) AS COMPRA_CANTIDAD_PAGOS
                            , A.ID_ANHO
                            , A.ID_MES

                    FROM PEDIDO_REGISTRO A 
                        INNER JOIN PEDIDO_COMPRA B ON A.ID_PEDIDO=B.ID_PEDIDO
                        INNER JOIN COMPRA C ON B.ID_COMPRA=C.ID_COMPRA
                        LEFT JOIN ARREGLO D ON C.ID_COMPRA=D.ID_ORIGEN AND D.ID_MODULO=11
                        LEFT JOIN PROCESS_RUN E ON A.ID_PEDIDO=E.ID_OPERACION
                        INNER JOIN TIPO_COMPROBANTE F ON C.ID_COMPROBANTE=F.ID_COMPROBANTE
                        INNER JOIN CONTA_VOUCHER G ON C.ID_VOUCHER=G.ID_VOUCHER
                    WHERE A.ID_ENTIDAD=$id_entidad
                    AND C.ID_ENTIDAD=$id_entidad
                    AND A.ID_DEPTO='$id_depto'
                    AND A.ID_PERSONA=$id_persona
                    $filteYear
                    $filteMonth
                    AND A.ESTADO='1'
                    AND C.ESTADO='1'
                    AND G.ACTIVO='S'
                    AND C.ID_COMPROBANTE <> '02'
                    ORDER BY A.ID_PEDIDO DESC
                    ";
                
                $oQuery = DB::select($query);
                return $oQuery;
    }

    public static function listOrdersPendingbk($id_entidad,$id_depto,$codigo,$id_persona,$per_page,$ids_paso){
        $query = DB::table('VW_ORDERS_PENDING')
            ->where('ID_ENTIDAD', '=', $id_entidad)
                ->where('ID_DEPTO', '=', $id_depto)
                //->where('ID_ANHO', '=', $id_entidad)
                //->where('ID_PERSONA', '=', $id_persona)
                ->where('ID_PASO_ACTUAL', '=', $ids_paso)
                ->where('CODIGO', '=', $codigo)
                ->where('ESTADO', '=', '0')
                ->where('ESTADO_RUN', '=', '0');
                $query->select('ID_PEDIDO',
                    'PID_PEDIDO',
                    'ID_TIPOPEDIDO',
                    'NOMBRE_TIPOPEDIDO',
                    'ID_ACTIVIDAD',
                    'NOMBRE_ACTIVIDAD',
                    'ID_AREAORIGEN',
                    'NOMBRE_AREAORIGEN',
                    'ID_AREADESTINO',
                    'NOMBRE_AREADESTINO',
                    'NUMERO',
                    'ACUERDO',
                    'CODIGO',
                    'FECHA_PEDIDO',
                    'FECHA_ENTREGA',
                    'MOTIVO',
                    'ESTADO_PORCENTAJE',
                    'ACCION',
                    'REVISADO',
                    'ID_PASO_ACTUAL',
                    'ESTADO',
                    'ESTADO_RUN')
            ->orderBy('ID_PEDIDO',"desc");
            $rst = $query->paginate((int)$per_page);
        return $rst;
    }
    public static function listIdsProcessSteps($id_entidad,$codigo,$llaves)
    {
        $query = DB::table('PROCESS')
            ->join('PROCESS_PASO', 'PROCESS.ID_PROCESO', '=', 'PROCESS_PASO.ID_PROCESO')
            ->join('PROCESS_COMPONENTE_PASO', 'PROCESS_PASO.ID_PASO', '=', 'PROCESS_COMPONENTE_PASO.ID_PASO')
            ->join('PROCESS_COMPONENTE', 'PROCESS_COMPONENTE_PASO.ID_COMPONENTE', '=', 'PROCESS_COMPONENTE.ID_COMPONENTE')
            ->select(DB::raw("PROCESS_PASO.ID_PASO"))
            ->where("PROCESS.CODIGO", $codigo)
            ->where("PROCESS.ID_ENTIDAD", $id_entidad)
            ->whereIn('PROCESS_COMPONENTE.LLAVE',$llaves)
            ->distinct()
            ->get();
            $lista = [];
            foreach($query as $key => $value)
            {
                $lista[] = $value->id_paso;
            }
            return $lista;
    }
    public static function listMyOrdersbk($id_entidad,$id_persona,$codigo,$per_page)
    {
        $query = DB::table('PEDIDO_REGISTRO')
            ->join('PROCESS_RUN', function ($join) use($id_entidad,$id_persona) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
                    ->where('PEDIDO_REGISTRO.ID_PERSONA', '=', $id_persona)
                    //->where('PROCESS_RUN.ESTADO', '=', '0')
                    //->whereIn('PROCESS_RUN.ESTADO', ["0","1"])
                    ->whereIn('PEDIDO_REGISTRO.ESTADO', ["0","1"]);
            })
            ->join('PROCESS', function ($join) use($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->select(
                DB::raw(
                    "
                    PEDIDO_REGISTRO.id_pedido
                    , PEDIDO_REGISTRO.id_tipopedido
                    , (select nombre from tipo_pedido where tipo_pedido.id_tipopedido=pedido_registro.id_tipopedido) as nombre_tipopedido
                    , PEDIDO_REGISTRO.id_actividad
                    , (select nombre from psto_actividad where psto_actividad.id_actividad=pedido_registro.id_actividad) as nombre_actividad
                    , PEDIDO_REGISTRO.id_areaorigen
                    , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, /* PEDIDO_AREA C, */ CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND /* B.ID_SEDEAREA = C.ID_SEDEAREA AND */ B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND /* C.ACTIVO = '1' AND */ B.ID_SEDEAREA = pedido_registro.id_areaorigen ) nombre_areaorigen
                    , PEDIDO_REGISTRO.id_areadestino
                    , (SELECT D.NOMBRE FROM ORG_AREA A, ORG_SEDE_AREA B, PEDIDO_AREA C, CONTA_ENTIDAD_DEPTO D WHERE A.ID_AREA = B.ID_AREA AND B.ID_SEDEAREA = C.ID_SEDEAREA AND B.ID_ENTIDAD = D.ID_ENTIDAD AND B.ID_DEPTO = D.ID_DEPTO AND B.ID_ENTIDAD = 7124 AND C.ACTIVO = '1' AND B.ID_SEDEAREA = pedido_registro.id_areadestino ) nombre_areadestino
                    , PEDIDO_REGISTRO.numero
                    , PEDIDO_REGISTRO.acuerdo
                    , TO_CHAR(PEDIDO_REGISTRO.fecha_pedido,'YYYY-MM-DD') fecha_pedido
                    , TO_CHAR(PEDIDO_REGISTRO.fecha_entrega,'YYYY-MM-DD') fecha_entrega
                    , PEDIDO_REGISTRO.motivo
                    -- , '40' as estado_porcentaje
                    , CASE FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO)
                        WHEN 'FORP' THEN '25'
                        WHEN 'FOCP' THEN '50'
                        WHEN 'FOAP' THEN '75'
                        ELSE '100'
                    END AS estado_porcentaje
                    -- , FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'1') as accion
                    -- , FC_LLAVE_PROCESO(PROCESS_RUN.ID_REGISTRO) A2
                    -- , FC_LLAVE_PROCESO_PREVIOUS(PROCESS_RUN.ID_REGISTRO) AS A3
                    , CASE FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'0') 
                        WHEN 'FPP3' THEN 'FPP3'
                        WHEN 'FPC' THEN 'FPC'
                        WHEN 'FPP2' THEN 'FPP2'
                        WHEN 'FRA' THEN 'FRA'
                        WHEN 'FPPA' THEN 'FPPA'
                        WHEN 'FOC' THEN 'FOC'
                        WHEN 'FPPP' THEN 'FPPP'
                        WHEN 'FOC2' THEN 'FOC2'
                        WHEN 'FPP' THEN 'FPP'
                        ELSE NULL
                    END AS LLAVE_ACCION
                    , CASE FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'0') 
                        WHEN 'FPP3' THEN 'PRE-PROVISION'
                        WHEN 'FPC' THEN 'PEDIDO-COMPRA'
                        WHEN 'FPP2' THEN 'PRE-PROVISION'
                        WHEN 'FRA' THEN 'REQUERIMIENTO'
                        WHEN 'FPPA' THEN 'PRE-PROVISION-ALMACEN'
                        WHEN 'FOC' THEN 'ORDEN-COMPRA'
                        WHEN 'FPPP' THEN 'PRE-PROVISION-PLANTILLA'
                        WHEN 'FOC2' THEN 'ORDEN-COMPRA'
                        WHEN 'FPP' THEN 'PRE-PROVISION'
                        ELSE NULL
                    END AS LLAVE_NOMBRE
                    , FC_LLAVE_PROCESO(PROCESS_RUN.ID_REGISTRO) AS LLAVE
                    , (
                        SELECT pas.NOMBRE
                        FROM PROCESS_PASO_RUN run, PROCESS_PASO pas
                        WHERE 
                            PROCESS_RUN.ID_REGISTRO = run.ID_REGISTRO
                            AND run.ID_PASO = pas.ID_PASO
                            AND run.ESTADO = '0'
                            AND ROWNUM <= 1
                    ) as estado_pedido
                    "
                )
            )
            ->orderBy('PEDIDO_REGISTRO.id_pedido',"desc")
            ->paginate((int)$per_page);
        return $query;
    }
    public static function listMyOrders($id_entidad,$id_depto,$id_persona,$codigo,$per_page, $searchs, $id_anho=null, $id_mes=null, $estado=null){

        $q_estado = "a.ESTADO = '0'";

        if ($estado != null) {
            $q_estado = "(CASE 
            WHEN a.ACCION = 'FAT' AND a.LLAVE IS NOT NULL AND a.ESTADO = '0' THEN 0
            WHEN a.ACCION = 'FP' AND a.LLAVE IS NULL AND a.ESTADO = '0' THEN 0
            WHEN a.ACCION = 'FP' AND a.LLAVE IS NOT NULL AND a.ESTADO = '0' THEN 2
            WHEN a.ACCION = 'FP' AND a.LLAVE IS NULL AND a.ESTADO = '1' THEN 1
            WHEN a.ACCION = 'FP' AND a.LLAVE IS NOT NULL AND a.ESTADO = '1' THEN 1
            WHEN a.ESTADO = '3' THEN 3
            END) = ".$estado." ";
        }

        
        $query = DB::table('VW_ORDERS_PENDING a')
            ->leftJoin('PEDIDO_COMPRA b', 'a.ID_PEDIDO', '=', 'b.ID_PEDIDO')
            ->where('a.ID_ENTIDAD', '=', $id_entidad)
            ->where('a.ID_DEPTO', '=', $id_depto)
                ->where('a.CODIGO', '=', $codigo)
                ->whereNotNull('a.NUMERO')
                ->whereRaw($q_estado)
                ->whereRaw('a.ID_ANHO = NVL(?, a.ID_ANHO)', [$id_anho])
                ->whereRaw('a.ID_MES = NVL(?, a.ID_MES)', [$id_mes])
                ->whereRaw("(a.NUMERO like '%".$searchs."%')");
                $query->whereRaw("a.ID_AREAORIGEN IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = ".$id_persona." )");
                $query->select('a.ID_ANHO', 'a.ID_MES', 'a.ID_PEDIDO',
                    'a.PID_PEDIDO',
                    'a.ID_TIPOPEDIDO',
                    'a.NOMBRE_TIPOPEDIDO',
                    'a.ID_ACTIVIDAD',
                    'a.NOMBRE_ACTIVIDAD',
                    'a.ID_AREAORIGEN',
                    'a.NOMBRE_AREAORIGEN',
                    'a.ID_AREADESTINO',
                    'a.NOMBRE_AREADESTINO',
                    DB::raw("regexp_substr(a.NOMBRE_AREADESTINO,'[^ - ]+',  1) as id_depto_destino"),
                    'a.NUMERO',
                    'a.ACUERDO',
                    'a.CODIGO',
                    'a.FECHA_PEDIDO',
                    'a.FECHA_ENTREGA',
                    'a.ID_PERSONA',
                    'a.MOTIVO',
                    'a.ESTADO_PORCENTAJE',
                    'a.ACCION',
                    'a.REVISADO',
                    'a.ID_PASO_ACTUAL',
                    'a.ESTADO',
                    'a.ESTADO_RUN',
                    'a.USUARIO',
                    DB::raw("(CASE 
                    WHEN a.ESTADO_PORCENTAJE = '25' THEN 'Registrado' 
                    WHEN a.ESTADO_PORCENTAJE = '50' THEN 'Aprobado' 
                    WHEN a.ESTADO_PORCENTAJE = '75' THEN 'Autorizado' 
                    WHEN (a.ESTADO_PORCENTAJE = '85' AND a.ESTADO = '0') THEN 'En programacion' 
                    WHEN (a.ESTADO_PORCENTAJE = '85' AND a.ESTADO = '1')  THEN 'Ejecutado' 
                    WHEN (a.ESTADO_PORCENTAJE = '100' AND a.ESTADO = '1') THEN 'Ejecutado'  
                    WHEN a.ESTADO = '3' THEN 'Rechazado' 
                    ELSE '' END) as proceso"),
                    DB::raw("ELISEO.PKG_PURCHASES.FC_RUC(b.ID_PROVEEDOR) AS RUC, ELISEO.PKG_PURCHASES.FC_PROVEEDOR(b.ID_PROVEEDOR) AS PROVEEDOR"),
                    'b.serie as serie_proveedor',
                    'b.numero as numero_proveedor',
                    'b.importe',
                    DB::raw("(CASE 
                    WHEN a.ACCION = 'FAT' AND a.LLAVE IS NOT NULL AND a.ESTADO = '0' THEN '33'
                    WHEN a.ACCION = 'FP' AND a.LLAVE IS NULL AND a.ESTADO = '0' THEN '50'
                    WHEN a.ACCION = 'FP' AND a.LLAVE IS NOT NULL AND a.ESTADO = '0' THEN '75'
                    WHEN a.ACCION = 'FP' AND a.LLAVE IS NULL AND a.ESTADO = '1' THEN '100'
                    WHEN a.ACCION = 'FP' AND a.LLAVE IS NOT NULL AND a.ESTADO = '1' THEN '100'
                    WHEN a.ESTADO = '3' THEN '100'
                    END) as GRADO_AVANCE"),
                    DB::raw("(CASE 
                    WHEN a.ACCION = 'FAT' AND a.LLAVE IS NOT NULL AND a.ESTADO = '0' THEN 'REGISTRADO'
                    WHEN a.ACCION = 'FP' AND a.LLAVE IS NULL AND a.ESTADO = '0' THEN 'REGISTRADO'
                    WHEN a.ACCION = 'FP' AND a.LLAVE IS NOT NULL AND a.ESTADO = '0' THEN 'AUTORIZADO'
                    WHEN a.ACCION = 'FP' AND a.LLAVE IS NULL AND a.ESTADO = '1' THEN 'PROVISIONADO'
                    WHEN a.ACCION = 'FP' AND a.LLAVE IS NOT NULL AND a.ESTADO = '1' THEN 'PROVISIONADO'
                    WHEN a.ESTADO = '3' THEN 'RECHAZADO'
                    END) as ESTADO_COMPRA")
                )
            ->orderBy('a.ID_PEDIDO',"desc");
            $rst = $query->paginate((int)$per_page);
        return $rst;
    }
    public static function showOrdersProcessPasoRun($id_entidad,$id_pedido,$codigo)
    {
        $query = DB::table('PEDIDO_REGISTRO')
            ->join('PROCESS_RUN', function ($join) use($id_entidad) {
                $join->on('PEDIDO_REGISTRO.ID_PEDIDO', '=', 'PROCESS_RUN.ID_OPERACION')
                    ->where('PROCESS_RUN.ESTADO', '=', '0')
                    ->where('PEDIDO_REGISTRO.ID_ENTIDAD', '=', $id_entidad)
                    ->where('PEDIDO_REGISTRO.ESTADO', '=', '0');
            })
            ->join('PROCESS', function ($join) use($codigo) {
                $join->on('PROCESS_RUN.ID_PROCESO', '=', 'PROCESS.ID_PROCESO')
                    ->where('PROCESS.CODIGO', '=', $codigo);
            })
            ->join('PROCESS_PASO_RUN', function ($join) {
                $join->on('PROCESS_RUN.ID_REGISTRO', '=', 'PROCESS_PASO_RUN.ID_REGISTRO')
                    ->on('PROCESS_RUN.ID_PASO_ACTUAL', '=', 'PROCESS_PASO_RUN.ID_PASO')
                    ->where('PROCESS_PASO_RUN.ESTADO', '=', "0");
            })
            ->select(
                DB::raw(
                    "
                    PEDIDO_REGISTRO.ID_PEDIDO
                    , PROCESS_RUN.ID_REGISTRO
                    , PROCESS_PASO_RUN.ID_DETALLE
                    "
                )
            )
            ->where("PEDIDO_REGISTRO.ID_PEDIDO",$id_pedido)
            ->first();
        return $query;
    }
    public static function updateOrdersRegisters($data, $id_pedido)
    {
        try
        {
            $result = DB::table('pedido_registro')
                ->where('id_pedido', $id_pedido)
                ->update($data);
            return $result;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    /* PEDIDO_REGISTRO. */
    /* .PEDIDO_DETALLE */
    public static function listOrdersDetailsToDispatches($id_pedido,$id_anho)
    {
        $query = DB::table('PEDIDO_DETALLE')
            ->select(
                DB::raw(
                    "
                    ID_DETALLE
                    , ID_PEDIDO
                    , (SELECT SUM(PEDIDO_DESPACHO.CANTIDAD) FROM PEDIDO_DESPACHO WHERE PEDIDO_DESPACHO.ID_DETALLE = PEDIDO_DETALLE.ID_DETALLE) CANTIDAD_DESPACHO
                    , DETALLE
                    , ID_ARTICULO
                    , CANTIDAD
                    , PRECIO
                    , HORA_INICIO
                    , HORA_FIN
                    , FECHA_INICIO
                    , FECHA_FIN
                    , (select nombre from INVENTARIO_ARTICULO where INVENTARIO_ARTICULO.id_articulo=pedido_detalle.id_articulo) nombre_articulo
                    , (select codigo from INVENTARIO_ARTICULO where INVENTARIO_ARTICULO.id_articulo=pedido_detalle.id_articulo) codigo
                    , PKG_INVENTORIES.FC_ARTICULO_STOCK(ID_ALMACEN,ID_ARTICULO ,$id_anho) AS STOCK
                    , objetivo
                    , publico
                    , tema
                    , id_persona
                    , FC_NOMBRE_PERSONA(id_persona) AS PERSONA_CONTACTO
                    , ponente
                    , celular
                    , plataforma
                    , formato
                    , descripcion
                    , links
                    , (SELECT COUNT(ID_PFILE) FROM ELISEO.PEDIDO_FILE WHERE PEDIDO_FILE.ID_DETALLE=PEDIDO_DETALLE.ID_DETALLE) as cant_files 
                    , (SELECT INVENTARIO_ALMACEN_ARTICULO.CODIGO FROM INVENTARIO_ALMACEN_ARTICULO where INVENTARIO_ALMACEN_ARTICULO.id_articulo=pedido_detalle.id_articulo
                    AND INVENTARIO_ALMACEN_ARTICULO.ID_ALMACEN=pedido_detalle.ID_ALMACEN
                    and INVENTARIO_ALMACEN_ARTICULO.id_anho=".$id_anho.") AS CODIGO_ARTICULO
                    "
                )
                /* 'id_detalle'
                , 'id_pedido'
                , 'detalle'
                , 'cantidad'
                , 'precio' */
                // select * from INVENTARIO_ARTICULO
            )
            // ->where("id_entidad",$id_entidad)
            // ->where("id_persona",$id_user)
            ->where("ID_PEDIDO", $id_pedido)
            ->orderBy('ID_DETALLE',"DESC")
            ->get();
        return $query;
    }
    public static function listOrdersDetails($id_pedido, $id_anho, $id_entidad)
    {
        $query1 = DB::table('pedido_movilidad')
        ->leftJoin('moises.trabajador as pnt', 'pedido_movilidad.id_persona', '=', DB::raw("pnt.id_persona and pnt.id_entidad=".$id_entidad.""))
        ->leftJoin('moises.persona as mp', 'pedido_movilidad.id_persona', '=', 'mp.id_persona')
        ->leftJoin('moises.persona_natural as pn', 'pedido_movilidad.id_persona', '=', 'pn.id_persona')
            ->select(
                DB::raw(
                    "
                    id_movilidad as id_detalle
                    ,(mp.nombre || ' ' || mp.paterno || ' ' || mp.materno) nombres_responsable
                    ,pn.celular celular_responsable
                    ,(CASE WHEN pnt.ID_SITUACION_TRABAJADOR = 'O' THEN 'BAJA' WHEN pnt.ID_SITUACION_TRABAJADOR = '1' THEN 'ACTIVO' WHEN pnt.ID_SITUACION_TRABAJADOR = '2' THEN 'PEND. LIQ.'  WHEN pnt.ID_SITUACION_TRABAJADOR = '3' THEN 'SUSPENSIÓN'ELSE '' END) situacion_laboral
                    ,(CASE WHEN pnt.ID_CONDICION_LABORAL = 'M' THEN 'Misionero' WHEN pnt.ID_CONDICION_LABORAL = 'C' THEN 'Contratado'  WHEN pnt.ID_CONDICION_LABORAL = 'E' THEN 'Empleado' WHEN pnt.ID_CONDICION_LABORAL = 'P' THEN 'Practicante' WHEN PNT.ID_CONDICION_LABORAL = 'MFL' THEN 'Conv. Juv.' ELSE '' END) condicion_laboral
                    , '' as hora_inicio_detalle
                    , '' as hora_fin_detalle
                    , id_pedido
                    , detalle
                    , id_vehiculo
                    , cantidad
                    , TO_CHAR('') as fecha_inicio_detalle
                    , TO_CHAR('') as fecha_fin_detalle
                    , 0 as precio
                    , 'Viaje: '||DECODE(TIPO_VIAJE,'1','Ida','2','Vuelta','Ida y Vuelta')||', De:'||ORIGEN||', A:'||DESTINO||', ('||CANTIDAD||'), Fecha:'||TO_CHAR(FECHA_P,'DD/MM/YYYY')||', Hora:'||HORA_P AS nombre_articulo

                    , DECODE(TIPO_VIAJE,1,'Ida',2,'Vuleta','Ida y Vuelta') TIPO
                    , ORIGEN
                    , DESTINO
                    , TO_CHAR(FECHA_P,'YYYY-MM-DD') AS FECHA
                    , HORA_P AS hora_pedido_movilidad
                    , FC_NOMBRE_PERSONA(ID_CONDUCTOR) AS CONDUCTOR
                    , FC_VEHICULO(ID_VEHICULO) AS VEHICULO
                    , FC_VEHICULO_PRECIO(ID_VEHICULO) AS PRECIO_VEHICULO
                    , (SELECT COUNT(PEDIDO_DESPACHO.ID_DESPACHO) FROM PEDIDO_DESPACHO WHERE PEDIDO_DESPACHO.ID_MOVILIDAD = pedido_movilidad.ID_MOVILIDAD) AS ESTADO_DES
                    , RESPONSABLE
                    , '' as objetivo
                    , '' as publico
                    , '' as tema
                    , TO_NUMBER('')  as id_persona
                    , '' AS PERSONA_CONTACTO
                    , '' as ponente
                    , TO_NUMBER('') as celular
                    , '' as plataforma
                    , '' as formato
                    , '' as descripcion
                    , '' as links
                    , FC_TIPO_VEHICULO(ID_TIPOVEHICULO) AS TIPO_VEHICULO
                    , TO_NUMBER('') as cant_files
                    , '' AS CODIGO_ARTICULO
                    "
                )
            )
     
            ->where("id_pedido", $id_pedido);
        
        $query = DB::table('pedido_detalle')

            ->select(
                DB::raw(
                    "
                    id_detalle
                    , '' as nombres_responsable
                    , '' as celular_responsable
                    , '' as situacion_laboral
                    , '' as condicion_laboral
                    , hora_inicio as hora_inicio_detalle
                    , hora_fin as hora_fin_detalle
                    , id_pedido
                    , nvl(detalle,PKG_INVENTORIES.FC_ARTICULO(pedido_detalle.id_articulo)) AS detalle
                    , id_articulo
                    , cantidad
                    ,TO_CHAR(fecha_inicio,'YYYY-MM-DD') as fecha_inicio_detalle
                    ,TO_CHAR(fecha_fin,'YYYY-MM-DD') as fecha_fin_detalle
                    , precio
                    , PKG_INVENTORIES.FC_ARTICULO(pedido_detalle.id_articulo) as nombre_articulo
                    --, (select nombre from INVENTARIO_ARTICULO where INVENTARIO_ARTICULO.id_articulo=pedido_detalle.id_articulo) nombre_articulo
                    , '' AS TIPO
                    , '' AS ORIGEN
                    , '' AS DESTINO
                    , '' AS FECHA 
                    , '' AS hora_pedido_movilidad
                    , '' AS CONDUCTOR
                    , '' AS VEHICULO
                    , 0 AS PRECIO_VEHICULO
                    , 0 AS ESTADO_DES
                    , '' AS RESPONSABLE
                    , objetivo
                    , publico
                    , tema
                    , id_persona
                    , FC_NOMBRE_PERSONA(id_persona) AS PERSONA_CONTACTO
                    , ponente
                    , celular
                    , plataforma
                    , formato
                    , descripcion
                    , links
                    , '' AS TIPO_VEHICULO
                    , (SELECT COUNT(ID_PFILE) FROM ELISEO.PEDIDO_FILE WHERE PEDIDO_FILE.ID_DETALLE=PEDIDO_DETALLE.ID_DETALLE) as cant_files 
                    , (SELECT INVENTARIO_ALMACEN_ARTICULO.CODIGO FROM INVENTARIO_ALMACEN_ARTICULO where INVENTARIO_ALMACEN_ARTICULO.id_articulo=pedido_detalle.id_articulo
                    AND INVENTARIO_ALMACEN_ARTICULO.ID_ALMACEN=pedido_detalle.ID_ALMACEN
                     and INVENTARIO_ALMACEN_ARTICULO.id_anho=".$id_anho.") AS CODIGO_ARTICULO
                    "
                )
                /* 'id_detalle'
                , 'id_pedido'
                , 'detalle'
                , 'cantidad'
                , 'precio' */
                // select * from INVENTARIO_ARTICULO
            )
            // ->where("id_entidad",$id_entidad)
            // ->where("id_persona",$id_user)
            ->where("id_pedido", $id_pedido)
            ->union($query1)
            ->orderBy('id_detalle',"desc")
            ->get();
        return $query;
    }
    public static function showOrdersDetails($id_detalle)
    {
        $query = DB::table('PEDIDO_DETALLE')
            ->select(
                DB::raw(
                    "
                    ID_DETALLE
                    , ID_PEDIDO
                    , DETALLE
                    , ID_ARTICULO
                    , ID_ALMACEN
                    , CANTIDAD
                    , PRECIO
                    , (SELECT NOMBRE FROM INVENTARIO_ARTICULO WHERE INVENTARIO_ARTICULO.ID_ARTICULO=PEDIDO_DETALLE.ID_ARTICULO) NOMBRE_ARTICULO
                    "
                )
            )
            ->where("ID_DETALLE", $id_detalle)
            ->first();
        return $query;
    }
    public static function xxxaaa()
    {
        // test1 id_test nom
        // $result = App\Test1::updateOrCreate(['id_test' => 1, 'nom' => 'Joe']  ); // , ['nom' => 15]
        // $result = App\Http\Data\Purchases\Test1::updateOrCreate(['id_test' => 1, 'nom' => 'Joe']  ); // , ['nom' => 15]
        // -- $result = factory(Test1::class)->save(['id_test' => 1, 'nom' => 'Joe']);
        $test1 = new Test1();
        /* $test1->id_test = 1;
        $test1->nom = 'FR 456 88'; */
        // $test1->fill(['id_test' => 2, 'nom' => 'Joe']);
        // ['id_test' => 1, 'nom' => 'Joe']
        $result = $test1->updateOrCreate(['nom' => 'Joe8'],['id_test' => 1]);
        return $result;
    }
    /* PEDIDO_DETALLE. */
    /* PEDIDO_COMPRA. */
    public static function listOrdersPurchases($id_pedido)
    {
        $query = DB::table('PEDIDO_COMPRA')
            ->select(
                'PEDIDO_COMPRA.ID_PCOMPRA',
                'PEDIDO_COMPRA.ID_PEDIDO',
                'PEDIDO_COMPRA.ID_COMPRA',
                'PEDIDO_COMPRA.ID_MONEDA',
                'PEDIDO_COMPRA.ID_PROVEEDOR',
                'PEDIDO_COMPRA.IMPORTE',
                'PEDIDO_COMPRA.ESTADO',
                DB::raw(
                    "
                    (SELECT CONTA_MONEDA.NOMBRE FROM CONTA_MONEDA WHERE CONTA_MONEDA.ID_MONEDA = PEDIDO_COMPRA.ID_MONEDA) NOMBRE_MONEDA
                    ,(SELECT MOISES.VW_PERSONA_JURIDICA.NOMBRE FROM MOISES.VW_PERSONA_JURIDICA WHERE MOISES.VW_PERSONA_JURIDICA.ID_PERSONA = PEDIDO_COMPRA.ID_PROVEEDOR AND ROWNUM<=1) NOMBRE_PROVEEDOR
                    ,(SELECT MOISES.VW_PERSONA_JURIDICA.ID_RUC FROM MOISES.VW_PERSONA_JURIDICA WHERE MOISES.VW_PERSONA_JURIDICA.ID_PERSONA = PEDIDO_COMPRA.ID_PROVEEDOR AND ROWNUM<=1) RUC_PROVEEDOR
                    ,(SELECT PERSONA_NATURAL.NUM_DOCUMENTO FROM moises.PERSONA_NATURAL WHERE MOISES.PERSONA_NATURAL.ID_PERSONA = PEDIDO_COMPRA.ID_PROVEEDOR AND ROWNUM<=1) PERSONA_NUM_DOCUMENTO
                    ,(SELECT PERSONA.PATERNO ||' '||PERSONA.MATERNO||', '||PERSONA.NOMBRE  FROM moises.PERSONA WHERE MOISES.PERSONA.ID_PERSONA = PEDIDO_COMPRA.ID_PROVEEDOR AND ROWNUM<=1) PERSONA_NAM_SNAM
                    ,TO_CHAR(PEDIDO_COMPRA.FECHA,'YYYY/MM/DD') FECHA
                    ,NVL((SELECT '1' FROM PEDIDO_FILE WHERE PEDIDO_FILE.ID_PCOMPRA = PEDIDO_COMPRA.ID_PCOMPRA AND PEDIDO_FILE.FORMATO = 'XML' AND ROWNUM <= 1),'0') HAVEXML
                    "
                )
            )
            ->where('PEDIDO_COMPRA.id_pedido', $id_pedido)
            ->orderBy('PEDIDO_COMPRA.ID_PCOMPRA', 'DESC')
            ->get();
        return $query;
    }
    public static function showOrdersPurchases($id_pcompra)
    {
        $query = DB::table('PEDIDO_COMPRA')
            ->select(
                DB::raw(
                    "
                    PEDIDO_COMPRA.ID_PCOMPRA
                    ,PEDIDO_COMPRA.ID_PEDIDO
                    ,PEDIDO_COMPRA.ID_COMPRA
                    ,PEDIDO_COMPRA.ID_MONEDA
                    ,(SELECT CONTA_MONEDA.NOMBRE FROM CONTA_MONEDA WHERE CONTA_MONEDA.ID_MONEDA = PEDIDO_COMPRA.ID_MONEDA) NOMBRE_MONEDA
                    ,PEDIDO_COMPRA.ID_PROVEEDOR
                    ,(SELECT MOISES.VW_PERSONA_JURIDICA.NOMBRE FROM MOISES.VW_PERSONA_JURIDICA WHERE MOISES.VW_PERSONA_JURIDICA.ID_PERSONA = PEDIDO_COMPRA.ID_PROVEEDOR AND ROWNUM<=1) NOMBRE_PROVEEDOR
                    ,(SELECT MOISES.VW_PERSONA_JURIDICA.ID_RUC FROM MOISES.VW_PERSONA_JURIDICA WHERE MOISES.VW_PERSONA_JURIDICA.ID_PERSONA = PEDIDO_COMPRA.ID_PROVEEDOR AND ROWNUM<=1) RUC_PROVEEDOR
                    ,(SELECT PERSONA_NATURAL.NUM_DOCUMENTO FROM moises.PERSONA_NATURAL WHERE MOISES.PERSONA_NATURAL.ID_PERSONA = PEDIDO_COMPRA.ID_PROVEEDOR AND ROWNUM<=1) PERSONA_NUM_DOCUMENTO
                    ,(SELECT PERSONA.PATERNO ||' '||PERSONA.MATERNO||', '||PERSONA.NOMBRE  FROM moises.PERSONA WHERE MOISES.PERSONA.ID_PERSONA = PEDIDO_COMPRA.ID_PROVEEDOR AND ROWNUM<=1) PERSONA_NAM_SNAM
                    ,PEDIDO_COMPRA.IMPORTE
                    ,TO_CHAR(PEDIDO_COMPRA.FECHA,'YYYY/MM/DD') FECHA
                    ,PEDIDO_COMPRA.ESTADO
                    ,PEDIDO_COMPRA.NUMERO
                    ,PEDIDO_COMPRA.SERIE
                    "
                )
            )
            // ->where('PEDIDO_COMPRA.id_pedido', $id_pedido)
            ->where('PEDIDO_COMPRA.ID_PCOMPRA', $id_pcompra)
            // ->orderBy('PEDIDO_COMPRA.ID_PCOMPRA', 'DESC')
            ->first();
        return $query;
    }
    // deprecated
    public static function addOrdersPurchases($data)
    {
        try{
            $result = DB::table('pedido_compra')
                ->insert($data);
        }catch(Exception $e){
            $result = false;
        }
        return $result;
    }
    public static function updateOrdersPurchases($data, $id_pcompra)
    {
        DB::table('PEDIDO_COMPRA')
            ->where('ID_PCOMPRA', $id_pcompra)
            ->update($data);
    }
    public static function deleteOrdersPurchases($id_pcompra)
    {
        try
        {
            $result = DB::table('PEDIDO_COMPRA')
                ->where('ID_PCOMPRA', '=', $id_pcompra)
                ->delete();
            if($result)
            {
                $result = [
                    "success"   => true,
                    "message"   => ""
                ];
            }
            else
            {
                $result = [
                    "success"   => false,
                    "message"   => "Error"
                ];
            }
        }
        catch(Exception $e)
        {
            $result = [
                "success"   => false,
                "message"   => $e->getMessage()
            ];
        }
        return $result;
    }
    /* .PROCESS_RUN */
    public static function addProcessRun($data)
    {
        $result = DB::table('process_run')->insert($data);
        return $result;
    }
    public static function showProcessRun($id_proceso,$id_operacion)
    {
        $query = DB::table('process_run')
            ->select(
                'id_registro'
                , 'id_proceso'
                , 'id_operacion'
                , 'fecha'
                , 'detalle'
                , 'estado'
                , 'id_paso_actual')
            ->where('id_proceso', $id_proceso)
            ->where('id_operacion', $id_operacion)
            ->first();
        return $query;
    }
    public static function showProcessRunByProcesoOperacion($id_proceso,$id_operacion)
    {
        $query = DB::table('PROCESS_RUN')
            ->select(
                DB::raw(
                    "
                    PROCESS_RUN.id_registro
                    , PROCESS_RUN.id_proceso
                    , PROCESS_RUN.id_operacion
                    , PROCESS_RUN.fecha
                    , PROCESS_RUN.detalle
                    , PROCESS_RUN.estado
                    , PROCESS_RUN.id_paso_actual
                    , FC_GET_LLAVE_COMPONENTE(PROCESS_RUN.ID_REGISTRO,PROCESS_RUN.ID_PASO_ACTUAL,'0') as LLAVE
                    "
                )
            )
            ->where('id_proceso', $id_proceso)
            ->where('id_operacion', $id_operacion)
            ->first();
        return $query;
    }
    public static function updateProcessRun($data,$id_registro)
    {
        DB::table('process_run')
            ->where('id_registro', $id_registro)
            ->update($data);
    }
    public static function updateProcessRun2($data,$id_proceso,$id_operacion)
    {
        DB::table('process_run')
            ->where('id_proceso', $id_proceso)
            ->where('id_operacion', $id_operacion)
            ->update($data);
    }
    /* PROCESS_RUN. */
    public static function addCompra($data)
    {
        // Editar aqui
        DB::table('compra')->insert($data);
        return $data;
    }
    public static function addPurchases($data)
    {
        // Editar aqui
        $result = DB::table('compra')->insert($data);
        if($result) return $data;
        else return false;
    }
    public static function storeCompraMain($id_compra, $datosGenerales) {
        //$id_compra = 0;
        $error = 0;
        $msg_error = '';
        $objReturn = [];
        try {

            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_GUARDAR_MAIN(
            :P_TIPO, 
            :P_ES_CREDITO, 
            :P_ID_PROVEEDOR, 
            :P_ID_COMPROBANTE, 
            :P_ES_ELECTRONICA, 
            :P_ES_TRANSPORTE_CARGA, 
            :P_ID_PARENT, 
            :P_SERIE, 
            :P_NUMERO, 
            :P_FECHA_DOC, 
            :P_FECHA_VENCIMIENTO, 
            :P_ID_DINAMICA, 
            :P_ID_TIPOTRANSACCION, 
            :P_ID_MONEDA, 
            :P_IMPORTE, 
            :P_TAXS, 
            :P_BASE_INAFECTA, 
            :P_OTROS, 
            :P_ES_RET_DET,
            :P_ES_RET_AVANZADA,
            :P_TIPOCAMBIO,
            
            :P_ID_VOUCHER,
            :P_ID_ENTIDAD,
            :P_ID_DEPTO,
            :P_ID_PERSONA,
            :P_ID_ANHO,
            :P_ID_MES,

            :P_ERROR,
            :P_ID_COMPRA,
            :P_MSGERROR
            );
            end;");

            $stmt->bindParam(':P_TIPO', $datosGenerales["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_CREDITO', $datosGenerales["es_credito"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PROVEEDOR', $datosGenerales["id_proveedor"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPROBANTE', $datosGenerales["id_comprobante"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_ELECTRONICA', $datosGenerales["es_electronica"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_TRANSPORTE_CARGA', $datosGenerales["es_transporte_carga"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PARENT', $datosGenerales["id_parent"], PDO::PARAM_INT);
            $stmt->bindParam(':P_SERIE', $datosGenerales["serie"], PDO::PARAM_STR);
            $stmt->bindParam(':P_NUMERO', $datosGenerales["numero"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_DOC', $datosGenerales["fecha_doc"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_VENCIMIENTO', $datosGenerales["fecha_vencimiento"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_DINAMICA', $datosGenerales["id_dinamica"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPOTRANSACCION', $datosGenerales["id_tipotransaccion"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MONEDA', $datosGenerales["id_moneda"], PDO::PARAM_INT);
            $stmt->bindParam(':P_IMPORTE', $datosGenerales["importe"], PDO::PARAM_STR);
            $stmt->bindParam(':P_TAXS', $datosGenerales["taxs"], PDO::PARAM_STR);
            $stmt->bindParam(':P_BASE_INAFECTA', $datosGenerales["base_inafecta"], PDO::PARAM_STR);
            $stmt->bindParam(':P_OTROS', $datosGenerales["otros"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_RET_DET', $datosGenerales["es_ret_det"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_RET_AVANZADA', $datosGenerales["es_ret_avanzada"], PDO::PARAM_INT);
            $stmt->bindParam(':P_TIPOCAMBIO', $datosGenerales["tipocambio"], PDO::PARAM_STR);

            $stmt->bindParam(':P_ID_VOUCHER', $datosGenerales["id_voucher"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ENTIDAD', $datosGenerales["id_entidad"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $datosGenerales["id_depto"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $datosGenerales["id_persona"], PDO::PARAM_INT);

            $stmt->bindParam(':P_ID_ANHO', $datosGenerales["id_anho"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $datosGenerales["id_mes"], PDO::PARAM_INT);

            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPRA',$id_compra, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

            $stmt->execute();
            // dd($error);

            $objReturn['error'] = $error;
            $objReturn['data'] = $id_compra;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        } catch(Exception $e){
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $error = "202";
            return $jResponse;
        }
    }

    public static function updateCompraById($id_compra, $datosGenerales) {
        //$id_compra = 0;
        $error = 0;
        $msg_error = '';
        $objReturn = [];
        try {

            for($x=1;$x<=200;$x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_ACTUALIZAR(
            :P_TIPO, 
            :P_ID_PROVEEDOR, 
            :P_ID_COMPROBANTE, 
            :P_ES_ELECTRONICA, 
            :P_ES_TRANSPORTE_CARGA, 
            :P_ID_PARENT, 
            :P_SERIE, 
            :P_NUMERO, 
            :P_FECHA_DOC, 
            :P_ID_MONEDA, 
            :P_IMPORTE, 
            :P_TAXS, 
            :P_BASE_INAFECTA, 
            :P_OTROS, 
            :P_ES_RET_DET,
            :P_TIPOCAMBIO,
            
            :P_ID_VOUCHER,

            :P_ERROR,
            :P_ID_COMPRA,
            :P_MSGERROR
            );
            end;");
            // :P_ID_ENTIDAD,
            // :P_ID_DEPTO,
            // :P_ID_PERSONA,
            // :P_ID_ANHO,
            // :P_ID_MES,

            $stmt->bindParam(':P_TIPO', $datosGenerales["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PROVEEDOR', $datosGenerales["id_proveedor"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPROBANTE', $datosGenerales["id_comprobante"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_ELECTRONICA', $datosGenerales["es_electronica"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_TRANSPORTE_CARGA', $datosGenerales["es_transporte_carga"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PARENT', $datosGenerales["id_parent"], PDO::PARAM_INT);
            $stmt->bindParam(':P_SERIE', $datosGenerales["serie"], PDO::PARAM_STR);
            $stmt->bindParam(':P_NUMERO', $datosGenerales["numero"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_DOC', $datosGenerales["fecha_doc"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_MONEDA', $datosGenerales["id_moneda"], PDO::PARAM_INT);
            $stmt->bindParam(':P_IMPORTE', $datosGenerales["importe"], PDO::PARAM_STR);
            $stmt->bindParam(':P_TAXS', $datosGenerales["taxs"], PDO::PARAM_STR);
            $stmt->bindParam(':P_BASE_INAFECTA', $datosGenerales["base_inafecta"], PDO::PARAM_STR);
            $stmt->bindParam(':P_OTROS', $datosGenerales["otros"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ES_RET_DET', $datosGenerales["es_ret_det"], PDO::PARAM_STR);
            $stmt->bindParam(':P_TIPOCAMBIO', $datosGenerales["tipocambio"], PDO::PARAM_STR);

            $stmt->bindParam(':P_ID_VOUCHER', $datosGenerales["id_voucher"], PDO::PARAM_INT);
            // $stmt->bindParam(':P_ID_ENTIDAD', $datosGenerales["id_entidad"], PDO::PARAM_INT);
            // $stmt->bindParam(':P_ID_DEPTO', $datosGenerales["id_depto"], PDO::PARAM_INT);
            // $stmt->bindParam(':P_ID_PERSONA', $datosGenerales["id_persona"], PDO::PARAM_INT);

            // $stmt->bindParam(':P_ID_ANHO', $datosGenerales["id_anho"], PDO::PARAM_INT);
            // $stmt->bindParam(':P_ID_MES', $datosGenerales["id_mes"], PDO::PARAM_INT);

            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPRA',$id_compra, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);

            $stmt->execute();
            // dd($error);

            $objReturn['error'] = $error;
            $objReturn['data'] = $id_compra;
            $objReturn['message'] = $msg_error;
            RETURN $objReturn;
        } catch(Exception $e){
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $error = "202";
            RETURN $jResponse;
        }
    }

    public static function addCompraDetalle($data)
    {
        DB::table('compra_detalle')->insert($data);
        return $data;
    }

    public static function addPedidoFile($data)
    {
        try
        {
            $result = DB::table('pedido_file')->insert($data);
            if($result)
            {
                $result = [
                    "success"   => true,
                    "data"   => $data["id_pfile"],
                    "message"   => "OK"
                ];
            }
            else
            {
                $result = [
                    "success"   => false,
                    "data"   =>[], 
                    "message"   => "Error."
                ];
            }
        }
        catch(Exception $e)
        {
            $result = [
                "success"   => false,
                "data"   =>[], 
                "message"   => $e->getMessage()
            ];
        }
        return $result;
    }
    public static function updatePedidoFile($data,$id_pfile)
    {
        DB::table('pedido_file')
            ->where('id_pfile', $id_pfile)
            ->update($data);
    }

    public static function deletePedidoFile($id_pfile)
    {
        DB::table('pedido_file')->where('id_pfile', '=', $id_pfile)->delete();
    }
    /* .PROCESS */
    public static function showProcessByCodigo($codigo,$id_entidad)
    {
        $query = DB::table('process')
            ->select(
                'id_proceso'
                , 'id_entidad'
                , 'id_depto'
                , 'id_modulo'
                , 'id_tipotransaccion'
                , 'id_paso_inicio'
                , 'id_paso_fin'
                , 'nombre'
                , 'estado'
                , 'codigo'
            )
            ->where('id_entidad', $id_entidad)
            /* ->where('id_modulo', $id_modulo)
            ->where('estado', "1")
            ->where('id_tipotransaccion', $id_tipotransaccion) */
            // ->where('id_proceso', $id_proceso)
            ->where('codigo', $codigo)
            ->first();
        return $query;
    }
    public static function showProcessByEstadoActivo($id_entidad,$id_modulo,$id_tipotransaccion)
    {
        $query = DB::table('process')
            ->select(
                'id_proceso'
                , 'id_entidad'
                , 'id_depto'
                , 'id_modulo'
                , 'id_tipotransaccion'
                , 'id_paso_inicio'
                , 'id_paso_fin'
                , 'nombre'
                , 'estado'
            )
            ->where('id_entidad', $id_entidad)
            ->where('id_modulo', $id_modulo)
            ->where('estado', "1")
            ->where('id_tipotransaccion', $id_tipotransaccion)
            ->first();
        return $query;
    }
    public static function addProcess($data)
    {
        DB::table('process')->insert($data);
        return $data;
    }
    /* PROCESS. */
    /* PROCESS_FLUJO */
    public static function listFlujoX($id_proceso, $id_paso)
    {
        $query = DB::table('PROCESS_FLUJO')
            ->join('PROCESS_PASO PROCESS_PASO', 'PROCESS_FLUJO.id_paso', '=', 'PROCESS_PASO.id_paso')
            ->leftJoin('PROCESS_PASO PROCESS_PASO2', 'PROCESS_FLUJO.id_paso_next', '=', 'PROCESS_PASO2.id_paso')
            ->leftJoin('process_componente_paso', 'PROCESS_PASO.id_paso', '=', 'process_componente_paso.id_paso')
            ->leftJoin('process_componente', 'process_componente_paso.id_componente', '=', 'process_componente.id_componente')
            /* ->join('compra', 'pedido_compra.id_compra', '=', 'compra.id_compra') */
            ->select(
                DB::raw(
                    "PROCESS_PASO.id_tipopaso
                    ,PROCESS_PASO.nombre
                    ,PROCESS_FLUJO.id_flujo
                    ,PROCESS_FLUJO.id_proceso
                    ,PROCESS_FLUJO.id_paso
                    ,PROCESS_FLUJO.id_paso_next
                    ,PROCESS_FLUJO.tag
                    ,PROCESS_FLUJO.id_componente
                    ,process_componente.nombre as nombre_componente
                    -- ,PROCESS_PASO2.nombre as nombre2
                    ,nvl(process_componente.llave,'0') as llave_componente
                    "
                )
            )
            ->where('PROCESS_FLUJO.id_proceso', $id_proceso)
            ->where('PROCESS_FLUJO.id_paso', $id_paso)
            // ->where('PROCESS_FLUJO.id_paso', $id_paso)
            ->where(function($query2) {
                $query2
                    ->whereIn('PROCESS_PASO2.id_tipopaso', ["1","2","3","4"]);
                    // ->orWhere('PROCESS_PASO2.id_tipopaso', "null");
            })
            ->get();
        return $query;
    }
    public static function showProcessFlujoByProcesoTipoPaso($id_proceso, $id_tipopaso)
    {
        $query = DB::table('PROCESS_FLUJO')
            ->join('PROCESS_PASO', 'PROCESS_FLUJO.id_paso', '=', 'PROCESS_PASO.id_paso')
            ->select(
                DB::raw(
                    "PROCESS_PASO.id_tipopaso
                    ,PROCESS_PASO.nombre
                    ,PROCESS_FLUJO.id_flujo
                    ,PROCESS_FLUJO.id_proceso
                    ,PROCESS_FLUJO.id_paso
                    ,PROCESS_FLUJO.id_paso_next
                    ,PROCESS_FLUJO.tag
                    ,PROCESS_FLUJO.id_componente
                    "
                )
            )
            ->where('PROCESS_FLUJO.id_proceso', $id_proceso)
            ->where('PROCESS_PASO.id_tipopaso', $id_tipopaso)
            ->first();
        return $query;
    }
    public static function showProcessFlujoByProcesoPaso($id_proceso, $id_paso)
    {
        $query = DB::table('PROCESS_FLUJO')
            ->join('PROCESS_PASO', 'PROCESS_FLUJO.id_paso', '=', 'PROCESS_PASO.id_paso')
            ->select(
                DB::raw(
                    "PROCESS_PASO.id_tipopaso
                    ,PROCESS_PASO.nombre
                    ,PROCESS_FLUJO.id_flujo
                    ,PROCESS_FLUJO.id_proceso
                    ,PROCESS_FLUJO.id_paso
                    ,PROCESS_FLUJO.id_paso_next
                    ,PROCESS_FLUJO.tag
                    ,PROCESS_FLUJO.id_componente
                    "
                )
            )
            ->where('PROCESS_FLUJO.id_proceso', $id_proceso)
            ->where('PROCESS_PASO.id_paso', $id_paso)
            ->first(); // dd($query);
        return $query;
    }
    public static function showProcessFlujoByLlave($id_proceso, $llave)
    {
        $query = DB::table('PROCESS_FLUJO')
            ->join('PROCESS_PASO', 'PROCESS_FLUJO.id_paso', '=', 'PROCESS_PASO.id_paso')
            ->join('PROCESS_COMPONENTE_PASO', 'PROCESS_PASO.ID_PASO', '=', 'PROCESS_COMPONENTE_PASO.ID_PASO')
            ->join('PROCESS_COMPONENTE', 'PROCESS_COMPONENTE_PASO.ID_COMPONENTE', '=', 'PROCESS_COMPONENTE.ID_COMPONENTE')
            ->select(
                DB::raw(
                    "PROCESS_PASO.id_tipopaso
                    ,PROCESS_PASO.nombre
                    ,PROCESS_FLUJO.id_flujo
                    ,PROCESS_FLUJO.id_proceso
                    ,PROCESS_FLUJO.id_paso
                    ,PROCESS_FLUJO.id_paso_next
                    ,PROCESS_FLUJO.tag
                    ,PROCESS_FLUJO.id_componente
                    "
                )
            )
            ->where('PROCESS_COMPONENTE.LLAVE', $llave)
            ->where('PROCESS_FLUJO.id_proceso', $id_proceso)
            // ->where('PROCESS_PASO.id_tipopaso', $id_tipopaso)
            ->first();
        return $query;
    }
    /* .TIPO_PEDIDO */
    public static function listTypesOrders($estado)
    {
        $query = DB::table('tipo_pedido')
            ->select(
                "id_tipopedido",
                "nombre"
            )
            ->where('estado','=',$estado)
            ->orderBy('id_tipopedido')
            ->get();
        return $query;
    }
    /* TIPO_PEDIDO. */
    public static function existProvider($id_persona)
    {
        $query = DB::table('MOISES.VW_PERSONA_JURIDICA')
                ->select('id_persona'
                    , 'id_ruc'
                    , 'siglas'
                    , 'nom_comercial'
                    , 'nombre')
                ->where('id_persona', $id_persona)
                ->first();
        return $query;
    }

    public static function updateCompraSeatContaAsiento($data, $id_tipoorigen, $id_origen)
    {
        DB::table('conta_asiento')
            ->where('id_tipoorigen', $id_tipoorigen)
            ->where('id_origen', $id_origen)
            ->update($data);
    }

    public static function updateCompra($data,$id_compra)
    {
        DB::table('compra')
            ->where('id_compra', $id_compra)
            ->update($data);
    }

    public static function updatePurchase($data,$id_compra) /* igual de que arriba */
    {
        DB::table('compra')
            ->where('id_compra', $id_compra)
            ->update($data);
    }
    public static function updateCompra_AllNulls($data,$id_compra)
    {
        DB::table('compra')
            ->where('id_compra', $id_compra)
            ->update($data);
    }
    public static function updatePedidoCompra($data,$id_pcompra)
    {
        DB::table('pedido_compra')
            ->where('id_pcompra', $id_pcompra)
            ->update($data);
    }
    public static function updatePedidoCompra_nulls($data,$id_compra)
    {
        DB::table('pedido_compra')
            ->where('id_compra', $id_compra)
            ->update($data);
    }
    /* public static function updateRequestPurchase($data, $id_pcompra)
    {
        DB::table('pedido_compra')
            ->where('id_pcompra', $id_pcompra)
            ->update($data);
    } */
    public static function updatePedidoRegistro($data,$id_pedido)
    {
        DB::table('pedido_registro')
            ->where('id_pedido', $id_pedido)
            ->update($data);
    }
    /* listado TESORERIA */
    public static function listOperationsPendingVob($tipo)
    {
        if($tipo == '2')
        {
            $twhere = " and prorun.id_paso_actual!=65  and prorun.estado='0' ";
        }
        else
        {
            $twhere = "";
        }
        $query = "
        select * 
        from ( 
        select 
            pedreg.id_pedido 
            ,pedreg.motivo 
            ,tipped.nombre tipo_nombre 
            ,case 
              when prorun.id_paso_actual=89 then 'iniciado' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 'en proceso' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 'en ejecucion' 
              when prorun.id_paso_actual=74 then 'provisionando' 
              when prorun.id_paso_actual=65 then 'fin' 
              when prorun.id_paso_actual=67 then 'iniciando' 
              when prorun.id_paso_actual=82 then 'en proceso' 
              when prorun.id_paso_actual=83 then 'en tesoreria' 
              when prorun.id_paso_actual=84 then 'en tesoreria' 
              when prorun.id_paso_actual=85 then 'en consejo'  
              else '...' 
            end estado_nombre 
            ,case 
              when prorun.id_paso_actual=83 then 'fa-chevron-right' 
              when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              else 'none' 
            end accion_icon 
            ,case 
              when prorun.id_paso_actual=83 and pedreg.estado=0 then '16' 
              when prorun.id_paso_actual=84 and pedreg.estado=0 then '17A' 
              else ' ' 
            end accion_next 
            ,case 
              when prorun.id_paso_actual=89 then 30 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 60 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 70 
              when prorun.id_paso_actual=74 then 90 
              when prorun.id_paso_actual=65 then 100 
              when prorun.id_paso_actual=67 then 15 
              when prorun.id_paso_actual=82 then 20 
              when prorun.id_paso_actual=83 then 40 
              when prorun.id_paso_actual=84 then 60 
              when prorun.id_paso_actual=85 then 50  
              else 0 
            end porcentaje 
            ,case -- NUEVO 
              when prorun.id_paso_actual=83 then 'fa-chevron-right' 
              when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              when prorun.id_paso_actual=85 then 'fa-check' 
              else ' ' 
            end accion_tesoreria_icon 
            ,case -- NUEVO 
              when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              else ' ' 
            end accion_consejo_icon 
        from 
            pedido_registro pedreg 
            inner join  
            tipo_pedido tipped 
            on pedreg.id_tipopedido=tipped.id_tipopedido 
            inner join 
            process_run prorun 
            on pedreg.id_pedido=prorun.id_operacion 
            and prorun.id_proceso=6 
        where prorun.id_paso_actual in (83,84) 
        ".$twhere." 
        order by pedreg.id_pedido desc 
        ) ";
        if($tipo == '2')
        {
            $query = $query."where ROWNUM <= 5 "; 
        }
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    /* listado CONSEJO */
    public static function listOperationsPendingApproval($tipo)
    {
        if($tipo == '2')
        {
            $twhere = " and prorun.id_paso_actual!=65  and prorun.estado='0' ";
        }
        else
        {
            $twhere = "";
        }
        $query = "
        select * 
        from ( 
        select 
            pedreg.id_pedido 
            ,pedreg.motivo 
            ,tipped.nombre tipo_nombre 
            ,case 
              when prorun.id_paso_actual=89 then 'iniciado' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 'en proceso' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 'en ejecucion' 
              when prorun.id_paso_actual=74 then 'provisionando' 
              when prorun.id_paso_actual=65 then 'fin' 
              when prorun.id_paso_actual=67 then 'iniciando' 
              when prorun.id_paso_actual=82 then 'en proceso' 
              when prorun.id_paso_actual=83 then 'en tesoreria' 
              when prorun.id_paso_actual=84 then 'en tesoreria' 
              when prorun.id_paso_actual=85 then 'en consejo'  
              else '...' 
            end estado_nombre 
            ,case 
              when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              else 'none' 
            end accion_icon 
            ,case 
              when prorun.id_paso_actual=85 and pedreg.estado=0 then '19' -- '/purchases/operations/pending-approval' 
              else ' ' 
            end accion_next 
            ,case 
              when prorun.id_paso_actual=89 then 30 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 60 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 70 
              when prorun.id_paso_actual=74 then 90 
              when prorun.id_paso_actual=65 then 100 
              when prorun.id_paso_actual=67 then 15 
              when prorun.id_paso_actual=82 then 20 
              when prorun.id_paso_actual=83 then 40 
              when prorun.id_paso_actual=84 then 60 
              when prorun.id_paso_actual=85 then 50  
              else 0 
            end porcentaje 
            ,case -- NUEVO 
              -- when prorun.id_paso_actual=89 then ' ' 
              -- when prorun.id_paso_actual=67 then ' ' 
              -- when prorun.id_paso_actual=82 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=68 then 'fa-chevron-right' 
              when prorun.id_paso_actual=83 then 'fa-chevron-right' -- añadido para abajo
              when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              when prorun.id_paso_actual=85 then 'fa-check' 
              -- when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else ' ' 
            end accion_tesoreria_icon 
            ,case -- NUEVO 
              -- when prorun.id_paso_actual=89 then ' ' 
              -- when prorun.id_paso_actual=67 then ' ' 
              -- when prorun.id_paso_actual=82 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=68 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=83 then 'fa-chevron-right' -- añadido para abajo
              -- when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else ' ' 
            end accion_consejo_icon 
        from 
            pedido_registro pedreg 
            inner join  
            tipo_pedido tipped 
            on pedreg.id_tipopedido=tipped.id_tipopedido 
            inner join 
            process_run prorun 
            on pedreg.id_pedido=prorun.id_operacion 
            and prorun.id_proceso=6 
        where prorun.id_paso_actual in (85) 
        ".$twhere." 
        order by pedreg.id_pedido desc 
        ) ";
        if($tipo == '2')
        {
            $query = $query."where ROWNUM <= 5 ";
        }
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    /* listado PROVISION */
    public static function listProvisions($tipo)
    {
        if($tipo == '2')
        {
            $twhere = " and prorun.id_paso_actual!=65 and prorun.estado='0' ";
        }
        else
        {
            $twhere = "";
        }
        $query = "
        select * 
        from ( 
        select 
            pedreg.id_pedido 
            ,pedreg.motivo 
            ,tipped.nombre tipo_nombre 
            ,case 
              when prorun.id_paso_actual=89 then 'iniciado' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 'en proceso' 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 'en ejecucion' 
              when prorun.id_paso_actual=74 then 'provisionando' 
              when prorun.id_paso_actual=65 then 'fin' 
              when prorun.id_paso_actual=67 then 'iniciando' 
              when prorun.id_paso_actual=82 then 'en proceso' 
              when prorun.id_paso_actual=83 then 'en tesoreria' 
              when prorun.id_paso_actual=84 then 'en tesoreria' 
              when prorun.id_paso_actual=85 then 'en consejo'  
              else '...' 
            end estado_nombre 
            ,case 
              when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else 'none' 
            end accion_icon 
            ,case 
              when prorun.id_paso_actual=74 and pedreg.estado=0 then '22' -- '/purchases/provisions' 
              else ' ' 
            end accion_next 
            ,case 
              when prorun.id_paso_actual=89 then 30 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201801 then 60 
              when prorun.id_paso_actual=68 and tipped.id_tipopedido=201802 then 70 
              when prorun.id_paso_actual=74 then 90 
              when prorun.id_paso_actual=65 then 100 
              when prorun.id_paso_actual=67 then 15 
              when prorun.id_paso_actual=82 then 20 
              when prorun.id_paso_actual=83 then 40 
              when prorun.id_paso_actual=84 then 60 
              when prorun.id_paso_actual=85 then 50  
              else 0 
            end porcentaje 
            ,case -- NUEVO 
              -- when prorun.id_paso_actual=89 then ' ' 
              -- when prorun.id_paso_actual=67 then ' ' 
              -- when prorun.id_paso_actual=82 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=68 then 'fa-chevron-right' 
              when prorun.id_paso_actual=83 then 'fa-chevron-right' -- añadido para abajo
              when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else ' ' 
            end accion_tesoreria_icon 
            ,case -- NUEVO 
              -- when prorun.id_paso_actual=89 then ' ' 
              -- when prorun.id_paso_actual=67 then ' ' 
              -- when prorun.id_paso_actual=82 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=68 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=83 then 'fa-chevron-right' -- añadido para abajo
              -- when prorun.id_paso_actual=84 then 'fa-chevron-right' 
              when prorun.id_paso_actual=85 then 'fa-chevron-right' 
              -- when prorun.id_paso_actual=74 then 'fa-chevron-right' 
              else ' ' 
            end accion_consejo_icon 
        from 
            pedido_registro pedreg 
            inner join  
            tipo_pedido tipped 
            on pedreg.id_tipopedido=tipped.id_tipopedido 
            inner join 
            process_run prorun 
            on pedreg.id_pedido=prorun.id_operacion 
            and prorun.id_proceso=6 
        where prorun.id_paso_actual=74 
        ".$twhere." 
        order by pedreg.id_pedido desc 
        ) ";
        if($tipo == '2')
        {
            $query = $query."where ROWNUM <= 5 "; 
        }
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showPedidoRegistro($id_pedido)
    {
        $query = DB::table('pedido_registro')
            ->select(
                DB::raw("
                    id_pedido 
                    , id_entidad 
                    , id_depto 
                    , id_tipopedido 
                    , id_gasto 
                    -- , id_deptoorigen 
                    -- , id_deptodestino 
                    -- , id_evento 
                    -- , id_voucher 
                    , ' ' evento_descripcion 
                    , numero 
                    , acuerdo 
                    , TO_CHAR(fecha,'YYYY/MM/DD') fecha 
                    , TO_CHAR(fecha_pedido,'YYYY/MM/DD') fecha_pedido 
                    , motivo 
                    -- , (select nombre from conta_entidad_depto where conta_entidad_depto.id_entidad=pedido_registro.id_entidad and conta_entidad_depto.id_depto=pedido_registro.id_deptoorigen and conta_entidad_depto.es_activo='1') AS departamento_nombre 
                    , estado 
                    "
                )
            )
            ->where('id_pedido', $id_pedido)
            ->first();
        return $query;
    }
    /* .tipo_plantilla */
    public static function listTypesTemplates()
    {
        $query = DB::table('tipo_plantilla')
            ->select(
                'id_tipoplantilla'
                , 'nombre'
            )
            ->orderBy('id_tipoplantilla')
            ->get();
        return $query;
    }
    public static function showTypesTemplates($id_tipoplantilla)
    {
        $query = DB::table('tipo_plantilla')
            ->select(
                DB::raw(
                    "
                    id_tipoplantilla
                    , nombre
                    "
                )
            )
            ->where('id_tipoplantilla', $id_tipoplantilla)
            ->first();
        return $query;
    }
    public static function addTypesTemplates($data)
    {
        DB::table('tipo_plantilla')->insert($data);
        return $data;
    }
    public static function updateTypesTemplates($data,$id_tipoplantilla)
    {
        DB::table('tipo_plantilla')
            ->where('id_tipoplantilla', $id_tipoplantilla)
            ->update($data);
    }
    public static function deleteTypesTemplates($id_tipoplantilla)
    {
        DB::table('tipo_plantilla')
            ->where('id_tipoplantilla', '=', $id_tipoplantilla)
            ->delete();
    }
    /* tipo_plantilla. */
    /* .compra_plantilla */
    public static function listPurchasesTemplates($id_entidad,$id_depto,$id_persona)
    {
        $query = DB::table('compra_plantilla')
            ->join('tipo_plantilla', 'compra_plantilla.id_tipoplantilla', '=', 'tipo_plantilla.id_tipoplantilla')
            ->select(
                DB::raw(
                    "
                    compra_plantilla.id_plantilla
                    , compra_plantilla.id_tipoplantilla
                    , compra_plantilla.fecha
                    , compra_plantilla.nombre
                    , tipo_plantilla.nombre as nombre_tipoplantilla
                    , compra_plantilla.id_depto
                    "
                )
            )
            ->where('compra_plantilla.id_entidad', '=', $id_entidad)
            ->where(function($query2) use ($id_depto, $id_entidad, $id_persona) {
                $query2
                    ->whereExists(function($query3) use ($id_depto) {
                        $query3->select(DB::raw(1))
                          ->from('compra_entidad_depto_plantilla')
                          ->whereRaw('compra_entidad_depto_plantilla.id_plantilla = compra_plantilla.id_plantilla and compra_entidad_depto_plantilla.id_depto = ?',array($id_depto)
                        );
                    })
                    ->orWhere(function($query22) use ($id_depto, $id_entidad, $id_persona) {
                        $query22
                            ->whereExists(function($query33) use ($id_depto, $id_entidad, $id_persona) {
                                $query33->select(DB::raw(1))
                                    ->from('LAMB_USERS_DEPTO')
                                    ->whereRaw('compra_plantilla.id_depto=LAMB_USERS_DEPTO.id_depto and LAMB_USERS_DEPTO.ID_ENTIDAD = ? and LAMB_USERS_DEPTO.ID = ?',array($id_entidad, $id_persona));
                            });
                    });
            })
            ->orderBy('compra_plantilla.id_plantilla')
            ->get();
        return $query;
    }
    public static function showPurchasesTemplates($id_plantilla,$id_entidad,$id_depto,$id_persona)
    {
        $query = DB::table('compra_plantilla')
            ->join('tipo_plantilla', 'compra_plantilla.id_tipoplantilla', '=', 'tipo_plantilla.id_tipoplantilla')
            ->select(
                DB::raw(
                    "
                    compra_plantilla.id_plantilla
                    , compra_plantilla.id_tipoplantilla
                    , compra_plantilla.fecha
                    , compra_plantilla.nombre
                    , tipo_plantilla.nombre as nombre_tipoplantilla
                    , compra_plantilla.id_depto
                    "
                )
            )
            ->where('compra_plantilla.id_entidad', '=', $id_entidad)
            ->where('compra_plantilla.id_plantilla', $id_plantilla)
            /* ->where(function($query2) use ($id_depto) {
                $query2->whereExists(function($query3) use ($id_depto) {
                    $query3->select(DB::raw(1))
                          ->from('compra_entidad_depto_plantilla')
                          ->whereRaw('compra_entidad_depto_plantilla.id_plantilla = compra_plantilla.id_plantilla and compra_entidad_depto_plantilla.id_depto = ?',array($id_depto));
                })
                ->orWhere('compra_plantilla.id_depto', $id_depto);
            }) */
            ->where(function($query2) use ($id_depto, $id_entidad, $id_persona) {
                $query2
                    ->whereExists(function($query3) use ($id_depto) {
                        $query3->select(DB::raw(1))
                          ->from('compra_entidad_depto_plantilla')
                          ->whereRaw('compra_entidad_depto_plantilla.id_plantilla = compra_plantilla.id_plantilla and compra_entidad_depto_plantilla.id_depto = ?',array($id_depto)
                        );
                    })
                    ->orWhere(function($query22) use ($id_depto, $id_entidad, $id_persona) {
                        $query22
                            ->whereExists(function($query33) use ($id_depto, $id_entidad, $id_persona) {
                                $query33->select(DB::raw(1))
                                    ->from('LAMB_USERS_DEPTO')
                                    ->whereRaw('compra_plantilla.id_depto=LAMB_USERS_DEPTO.id_depto and LAMB_USERS_DEPTO.ID_ENTIDAD = ? and LAMB_USERS_DEPTO.ID = ?',array($id_entidad, $id_persona));
                            });
                    });
            })
            ->first();
        return $query;
    }
    public static function addPurchasesTemplates($data)
    {
        DB::table('compra_plantilla')->insert($data);
        return $data;
    }
    public static function updatePurchasesTemplates($data,$id_plantilla)
    {
        DB::table('compra_plantilla')
            ->where('id_plantilla', $id_plantilla)
            ->update($data);
    }
    public static function deletePurchasesTemplates($id_plantilla)
    {
        DB::table('compra_plantilla')
            ->where('id_plantilla', '=', $id_plantilla)
            ->delete();
    }
    /* COMPRA */
    public static function showPurchases($id_entidad,$id_compra)
    {
        $query = DB::table('COMPRA')
            ->select(
                DB::raw(
                    "
                    base,
                    base_gravada,
                    base_inafecta,
                    base_mixta,
                    base_nogravada,
                    base_sincredito,
                    correlativo,
                    es_activo,
                    es_credito,
                    es_electronica,
                    es_ret_det,
                    es_transporte_carga,
                    estado,
                    fecha_almacen,
                    TO_CHAR(FECHA_DOC, 'YYYY/MM/DD') FECHA_DOC,
                    TO_CHAR(FECHA_PROVISION, 'YYYY/MM/DD') FECHA_PROVISION,
                    id_anho,
                    id_compra,
                    id_comprobante,
                    id_depto,
                    id_entidad,
                    id_mes,
                    id_moneda,
                    id_parent,
                    id_persona,
                    id_proveedor,
                    id_tiponota,
                    id_tipoorigen,
                    id_tipotransaccion,
                    id_voucher,
                    igv,
                    id_igv,
                    igv_gravado,
                    igv_mixto,
                    igv_nogravado,
                    igv_sincredito,
                    importe,
                    importe_me,
                    importe_renta,
                    numero,
                    otros,
                    serie,
                    taxs,
                    tiene_kardex,
                    tiene_suspencion,
                    tipocambio
                    ,(SELECT MOISES.VW_PERSONA_JURIDICA.NOMBRE FROM MOISES.VW_PERSONA_JURIDICA WHERE MOISES.VW_PERSONA_JURIDICA.ID_PERSONA = COMPRA.ID_PROVEEDOR AND ROWNUM<=1) NOMBRE_PROVEEDOR
                    ,(SELECT MOISES.VW_PERSONA_JURIDICA.ID_RUC FROM MOISES.VW_PERSONA_JURIDICA WHERE MOISES.VW_PERSONA_JURIDICA.ID_PERSONA = COMPRA.ID_PROVEEDOR AND ROWNUM<=1) RUC_PROVEEDOR
                    "
                )
            )
            ->where('id_entidad', '=', $id_entidad)
            ->where('id_compra', '=', $id_compra)
            ->first();
        $query->requerid = self::getRetDetValidate($id_compra);
        return $query;
    }

    public static function getRetDetValidate($idCompra) {

        return collect(DB::select("SELECT (CASE
            WHEN VALIDA_COMPROBANTE = 1 THEN
                (
                    CASE
                        WHEN VALIDA_BASE = 1 THEN
                            (
                                CASE
                                    WHEN VALIDA_IMPORTE = 1 THEN
                                        (
                                            CASE
                                                WHEN ES_BUEN_CONTRIBUYENTE = 1 THEN
                                                    (
                                                        CASE
                                                            WHEN ES_AGENTE_RETENCION = 1 THEN 3
                                                            ELSE 3 END
                                                        )
                                                ELSE
                                                    (
                                                        CASE
                                                            WHEN ES_AGENTE_RETENCION = 1 THEN 3
                                                            ELSE 1 END
                                                        )
                                                END
                                            )
                                    ELSE 2 END
                                )
                        ELSE 0 END
                    )
            ELSE 0
    END) REQUERID
FROM (
         SELECT A.ID_COMPROBANTE,
                A.IMPORTE,
                (CASE WHEN A.ID_COMPROBANTE IN ('01', '08') THEN 1 ELSE 0 END) AS VALIDA_COMPROBANTE,
                (CASE WHEN A.IMPORTE >= 700 THEN 1 ELSE 0 END)                 AS VALIDA_IMPORTE,
                (SELECT COUNT(1)
                 FROM COMPRA_DETALLE X
                 WHERE X.ID_COMPRA = A.ID_COMPRA
                   AND X.ID_CTIPOIGV IN (1, 2, 3))                             AS VALIDA_BASE,
                (SELECT COUNT(1)
                 FROM MOISES.PERSONA_JURIDICA X
                 WHERE X.ID_PERSONA = A.ID_PROVEEDOR
                   AND X.ES_BUEN_CONTRIBUYENTE = 'S')                          AS ES_BUEN_CONTRIBUYENTE,
                (SELECT COUNT(1)
                 FROM MOISES.PERSONA_JURIDICA X
                 WHERE X.ID_PERSONA = A.ID_PROVEEDOR
                   AND X.ES_AGENTE_RETENCION = 'S')                            AS ES_AGENTE_RETENCION
         FROM COMPRA A
         WHERE A.ID_COMPRA = ?
     )", [$idCompra]))->pluck('requerid')->first();
    }
    public static function execPurchasesEnd($data)
    {
        $error      = 0;
        $msg_error  = '';
        $code       = '';
        // $objReturn  = [];
        try {

            for($x=1 ; $x <= 200 ; $x++)
            {
                $msg_error .= "0";
                $code .= "0";
            }
            $pdo = DB::getPdo();
            // $stmt = $pdo->prepare("BEGIN PKG_PURCHASES.SP_COMPRA_END(
            // :P_ID_PEDIDO,
            // :P_ID_DINAMICA,
            // :P_ERROR,
            // :P_MSGERROR
            // );
            // end;");
            // $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
            // $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            // $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            // $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            // :P_ID_PEDIDO,
            $stmt = $pdo->prepare("BEGIN PKG_PURCHASES.SP_COMPRA_END(
                :P_ID_COMPRA,
                :P_CODIGO,
                :P_ID_PERSONA,
                :P_ID_ENTIDAD,
                :P_DETALLE,
                :P_IP,
                :P_CODE,
                :P_ERROR,
                :P_MSGERROR
                );
                end;");
                $stmt->bindParam(':P_ID_COMPRA', $data["id_compra"], PDO::PARAM_INT);
                $stmt->bindParam(':P_CODIGO', $data["codigo"], PDO::PARAM_INT);
                // $stmt->bindParam(':P_ID_PEDIDO', $data["id_pedido"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $data["id_entidad"], PDO::PARAM_INT);
                $stmt->bindParam(':P_DETALLE', $data["detalle"], PDO::PARAM_STR);
                $stmt->bindParam(':P_IP', $data["ip"], PDO::PARAM_STR);
                $stmt->bindParam(':P_CODE', $code, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['code']   = $code;
            $objReturn['message'] = $msg_error;
            // dd($objReturn);
            return $objReturn;

        }
        catch(Exception $e)
        {
            $jResponse['error']   = 1;
            $jResponse['code']   = "0";
            $jResponse['message'] = $e->getMessage();
            // $jResponse['data']    = [];
            // dd($jResponse);
            return $jResponse;
        }
    }
    /* COMPRA_DETALLE */
    public static function listPurchasesDetails($id_compra,$id_anho)
    {
        
        $query = DB::table('COMPRA_DETALLE')
            ->select(
                'ID_DETALLE',
                'ID_COMPRA',
                'ID_DINAMICA',
                'DETALLE',
                'CANTIDAD',
                DB::raw("TO_CHAR(PRECIO,'FM9999999.90') as PRECIO"),
                // 'PRECIO',
                DB::raw("TO_CHAR(IMPORTE,'FM9999999.90') as IMPORTE"),
                // 'IMPORTE',
                DB::raw("TO_CHAR(IGV,'FM9999999.90') as IGV"),
                // 'IGV',
                DB::raw("TO_CHAR(BASE,'FM9999999.90') as BASE"),
                // 'BASE',
                'ESTADO',
                'ID_ALMACEN',
                'ID_ARTICULO',
                'ES_COSTO_VINCULADO',
                DB::raw(
                    "
                    FC_TIPO_IGV_COMPRA(ID_CTIPOIGV) AS TIPO_IGV,
                    PKG_INVENTORIES.FC_ARTICULO(ID_ARTICULO) AS NOMBRE_ARTICULO,
                    PKG_SALES.FC_PRECIO_VENTA(ID_ALMACEN,ID_ARTICULO,".$id_anho.") AS PRECIO_VENTA,
                    PKG_PURCHASES.FC_TIPO_ALMACEN(ID_ALMACEN) AS ID_EXISTENCIA
                    "
                )
            )
            ->where('ID_COMPRA', $id_compra)
            ->orderBy('ID_DETALLE')
            ->get();
        return $query;
    }
    public static function addPurchasesDetails($data)
    {
        $result = DB::table('COMPRA_DETALLE')->insert($data);
        return $result;
    }
    public static function deletePurchasesDetails($id_detalle)
    {
        $result = DB::table('compra_detalle')
            ->where('id_detalle', '=', $id_detalle)
            ->delete();
        return $result;
    }
    public static function deleteCompraDetalle($id_detalle)
    {
        DB::table('compra_detalle')->where('id_detalle', '=', $id_detalle)->delete();
    }
    public static function deleteCompraDetalleMore($id_compra)
    {
        return DB::table('compra_detalle')->where('id_compra', '=', $id_compra)->delete();
    }
    /* COMPRA_TIPOIGV */
    public static function listPurchasesTypeigv()
    {
        $query = DB::table('COMPRA_TIPOIGV')
            ->select(
                'ID_CTIPOIGV'
                , 'CODIGO'
                , 'NOMBRE'
                , 'DESCRIPCION'
                , 'TIENE_IGV'
            )
            // ->where('ID_COMPRA', $id_compra)
            ->orderBy('ID_CTIPOIGV')
            ->get();
        return $query;
    }
    public static function listIgv()
    {
        $query = DB::table('eliseo.conta_igv')
            ->select('id_igv', 'fecha_ini', 'fecha_fin')
            ->whereraw("(current_date between fecha_ini and coalesce(fecha_fin,current_date))")
            ->orderBy('id_igv')
            ->get();
        return $query;
    }
    /* COMPRA_ASIENTO */
    public static function listPurchasesSeats($id_compra)
    {
        $query = DB::table('COMPRA_ASIENTO')
            /* ->select(
                'id_detalle'
                , 'id_compra'
                , 'id_dinamica'
                , 'detalle'
                , 'cantidad'
                , 'precio'
                , 'importe'
                , 'igv'
                , 'estado') */
            ->where('ID_COMPRA', $id_compra)
            ->orderBy('ID_CASIENTO')
            ->get();
        return $query;
    }
    // DEPRECATED
    public static function addPurchasesSeats($data)
    {
        $id     = self::getMax("COMPRA_ASIENTO","ID_CASIENTO")+1;
        $data   = array_merge(array("id_casiento" => $id), $data);
        $result = DB::table('COMPRA_ASIENTO')->insert($data);
        return $result;
    }
    public static function deletePurchasesSeats($id_casiento)
    {
        $result = DB::table('COMPRA_ASIENTO')
            ->where('ID_CASIENTO', '=', $id_casiento)
            ->delete();
        return $result;
    }

    // DEPRECATED
    public static function addCompraAsiento($data)
    {
        DB::table('compra_asiento')->insert($data);
        return $data;
    }
    public static function updateCompraAsiento($data,$id_casiento)
    {
        DB::table('compra_asiento')
            ->where('id_casiento', $id_casiento)
            ->update($data);
    }
    public static function deleteCompraAsiento($id_casiento)
    {
        DB::table('compra_asiento')->where('id_casiento', '=', $id_casiento)->delete();
    }
    public static function deleteCompraAsiento_children($id_parent)
    {
        DB::table('compra_asiento')->where('id_parent', '=', $id_parent)->delete();
    }
    public static function deleteCompraAsiento_All($id_compra)
    {
        DB::table('compra_asiento')->where('id_compra', '=', $id_compra)->delete();
    }
    public static function deleteCompraAsientoMore($id_compra)
    {
        DB::table('compra_asiento')->where('id_compra', '=', $id_compra)->delete();
    }
    /* COMPRA_ORDEN */
    public static function listPurchasesOrders($id_entidad)
    {
        $query = DB::table('COMPRA_ORDEN')
            ->where('id_entidad', '=', $id_entidad)
            ->orderBy('ID_ORDEN')
            ->get();
        return $query;
    }
    public static function showPurchasesOrders($id_entidad,$id_orden)
    {
        $query = DB::table('COMPRA_ORDEN')
            ->select(
                DB::raw(
                    "
                    ID_ORDEN,
                    ID_PERSONA,
                    ID_PROVEEDOR,
                    ID_PEDIDO,
                    ID_SEDEAREA,
                    ID_MEDIOPAGO,
                    NUMERO,
                    TO_CHAR(FECHA_PEDIDO,'YYYY/MM/DD') as FECHA_PEDIDO,
                    TO_CHAR(FECHA_ENTREGA,'YYYY/MM/DD') as FECHA_ENTREGA,
                    LUGAR_ENTREGA,
                    OBSERVACIONES,
                    CON_IGV,
                    DIAS_CREDITO
                    "
                )
            )
            ->where('ID_ENTIDAD', '=', $id_entidad)
            ->where('ID_ORDEN', '=', $id_orden)
            ->first();
        return $query;
    }
    public static function showPurchasesOrdersByOrders($id_entidad,$id_pedido)
    {
        $query = DB::table('COMPRA_ORDEN')
            ->select(
                DB::raw(
                    "
                    ID_ORDEN,
                    ID_PERSONA,
                    ID_PROVEEDOR,
                    ID_PEDIDO,
                    ID_SEDEAREA,
                    ID_MEDIOPAGO,
                    NUMERO,
                    TO_CHAR(FECHA_PEDIDO,'YYYY/MM/DD') as FECHA_PEDIDO,
                    TO_CHAR(FECHA_ENTREGA,'YYYY/MM/DD') as FECHA_ENTREGA,
                    LUGAR_ENTREGA,
                    OBSERVACIONES,
                    CON_IGV,
                    DIAS_CREDITO,
                    PKG_PURCHASES.FC_RUC(ID_PROVEEDOR) AS RUC,
                    FC_NOMBRE_PERSONA(ID_PROVEEDOR) AS RAZONSOCIAL,
                    PKG_ORDERS.FC_NOMBRE_AREA(ID_SEDEAREA) AS NOMBRE,
                    ID_MONEDA,
                    ES_CREDITO,
                    CUOTAS
                    "
                )
            )
            ->where('ID_ENTIDAD', '=', $id_entidad)
            ->where('ID_PEDIDO', '=', $id_pedido)
            ->first();
        return $query;
    }
    public static function addPurchasesOrders($data)
    {
        return DB::table('COMPRA_ORDEN')->insert($data);
    }
    public static function updatePurchasesOrders($data,$id_orden)
    {
        return DB::table('COMPRA_ORDEN')
            ->where('ID_ORDEN', $id_orden)
            ->update($data);
    }
    public static function deletePurchasesOrders($id_orden)
    {
        return DB::table('COMPRA_ORDEN')
            ->where('ID_ORDEN', '=', $id_orden)
            ->delete();
    }
    /* COMPRA_ORDEN_DETALLE */
    public static function listPurchasesOrdersDetails($id_orden)
    {
        /*$query = DB::table('COMPRA_ORDEN_DETALLE')
            ->where('ID_ORDEN', '=', $id_orden)
            ->orderBy('ID_ODETALLE')
            ->get();
        return $query;*/
        $sql = DB::table('COMPRA_ORDEN_DETALLE')
                ->select('ID_ODETALLE','ID_ORDEN','ID_ARTICULO',DB::raw('PKG_INVENTORIES.FC_ARTICULO(ID_ARTICULO) AS ARTICULO'),'DETALLE','CANTIDAD','PRECIO','TOTAL')
                ->where('ID_ORDEN', $id_orden)
                ->get();
        return $sql;
    }
    public static function showPurchasesOrdersDetails($id_odetalle)
    {
        $query = DB::table('COMPRA_ORDEN_DETALLE')
            ->where('ID_ODETALLE', '=', $id_odetalle)
            ->first();
        return $query;
    }
    public static function addPurchasesOrdersDetails($data)
    {
        return DB::table('COMPRA_ORDEN_DETALLE')->insert($data);
    }
    public static function updatePurchasesOrdersDetails($id_odetalle,$data){
        return DB::table('COMPRA_ORDEN_DETALLE')
            ->where('ID_ODETALLE', $id_odetalle)
            ->update($data);
    }
    public static function deletePurchasesOrdersDetails($id_odetalle)
    {
        return DB::table('COMPRA_ORDEN_DETALLE')
            ->where('ID_ODETALLE', '=', $id_odetalle)
            ->delete();
    }
    public static function addAllPurchasesOrdersDetails($id_orden,$id_pedido)
    {
        // return DB::table('COMPRA_ORDEN_DETALLE')
        //     ->where('ID_ODETALLE', '=', $id_odetalle)
        //     ->delete();
        // $list = self::listPurchasesOrdersDetails($id_orden);
        $listPedido = self::listPedidoDetalleP($id_pedido);
        foreach ($listPedido as $item)
        {
            $id     = self::getMax("COMPRA_ORDEN_DETALLE","ID_ODETALLE")+1;
            // $data   = array_merge(array($column=>$id), $data);
            $data = [
                "ID_ODETALLE"=>$id,
                "ID_ORDEN"=>$id_orden,
                "ID_ARTICULO"=>$item->id_articulo,
                "DETALLE"=>$item->detalle,
                "CANTIDAD"=>$item->cantidad,
                "PRECIO"=>$item->precio,
                "TOTAL"=> ($item->cantidad * $item->precio),
            ];
            $result = self::addPurchasesOrdersDetails($data);
            // $id_compra = $item->id_compra;
            // if($id_compra)
            // {
            //     PurchasesData::updatePurchase($data, $id_compra);
            //     $id_operorigen = 3;
            //     $data2 = array("voucher"=>$id_voucher);
            //     PurchasesData::updateAccountantSeat($data2, $id_operorigen, $id_compra);
            // }
        }
    }
    public static function sumTotalByIdOrden($id_orden)
    {
        $sumTotal = DB::table('COMPRA_ORDEN_DETALLE')
            ->where('COMPRA_ORDEN_DETALLE.ID_ORDEN', '=', $id_orden)
            ->sum('COMPRA_ORDEN_DETALLE.TOTAL');
        return $sumTotal;
    }
    /* compra_plantilla. */
    /* .compra_plantilla_detalle */
    public static function listPurchasesTemplateDetails($id_plantilla)
    {
        $query = DB::table('compra_plantilla_detalle')
            ->join('CONTA_ENTIDAD_DEPTO', 'compra_plantilla_detalle.id_depto', '=', 'CONTA_ENTIDAD_DEPTO.id_depto')
            ->join('TIPO_PLAN', 'compra_plantilla_detalle.id_tipoplan', '=', 'TIPO_PLAN.id_tipoplan')
            ->select(
                DB::raw(
                    "
                    compra_plantilla_detalle.id_pdetalle
                    , compra_plantilla_detalle.id_depto
                    , compra_plantilla_detalle.id_tipoplan
                    , compra_plantilla_detalle.id_cuentaaasi
                    , compra_plantilla_detalle.id_restriccion
                    , compra_plantilla_detalle.detalle
                    , compra_plantilla_detalle.porcentaje
                    , CONTA_ENTIDAD_DEPTO.nombre as nombre_depto
                    , FC_CUENTA(compra_plantilla_detalle.id_cuentaaasi) as nombre_cuentaaasi
                    , TIPO_PLAN.nombre as nombre_tipoplan
                    "
                )
            )
            ->where('id_plantilla', '=', $id_plantilla)
            ->orderBy('id_pdetalle')
            ->get();
        return $query;
    }
    public static function showPurchasesTemplateDetails($id_pdetalle)
    {
        $query = DB::table('compra_plantilla_detalle')
            ->select(
                DB::raw(
                    "
                    id_pdetalle
                    , id_depto
                    , id_tipoplan
                    , id_cuentaaasi
                    , id_restriccion
                    , detalle
                    , porcentaje
                    "
                )
            )
            ->where('id_pdetalle', $id_pdetalle)
            ->first();
        return $query;
    }
    public static function addPurchasesTemplateDetails($data)
    {
        try
        {
            $result = DB::table('compra_plantilla_detalle')->insert($data);
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }
    public static function updatePurchasesTemplateDetails($data,$id_pdetalle)
    {
        DB::table('compra_plantilla_detalle')
            ->where('id_pdetalle', $id_pdetalle)
            ->update($data);
    }
    public static function deletePurchasesTemplateDetails($id_pdetalle)
    {
        DB::table('compra_plantilla_detalle')
            ->where('id_pdetalle', '=', $id_pdetalle)
            ->delete();
    }
    /* compra_plantilla_detalle. */
    /* PLANTILLA_DETALLE_COMPRA */ // NO EXISTE
    // public static function listTemplateDetailsPurchases($id_pedido)
    // {
    //     $query = DB::table('PLANTILLA_DETALLE_COMPRA')
    //         // ->select(
    //         //     'ID_PLANTILLA_DETALLE_COMPRA'
    //         //     , 'detalle'
    //         //     , 'porcentaje'
    //         //     , 'cantidad'
    //         //     , 'precio'
    //         //     , 'importe'
    //         //     , 'importe_me'
    //         //     , 'estado'
    //         // )
    //         ->where("ID_PEDIDO",$id_pedido)
    //         ->orderBy('ID_PDCOMPRA')
    //         ->get();
    //     return $query;
    // }
    // public static function showTemplateDetailsPurchases($id_pdcompra)
    // {
    //     $query = DB::table('PLANTILLA_DETALLE_COMPRA')
    //         ->select(
    //             DB::raw(
    //                 "
    //                 ID_PDCOMPRA
    //                 , DETALLE
    //                 , PORCENTAJE
    //                 , CANTIDAD
    //                 , PRECIO
    //                 , IMPORTE
    //                 , IMPORTE_ME
    //                 , ESTADO
    //                 "
    //             )
    //         )
    //         ->where('ID_PDCOMPRA', $id_pdcompra)
    //         ->first();
    //     return $query;
    // }
    // public static function addTemplateDetailsPurchases($data)
    // {
    //     return DB::table('PLANTILLA_DETALLE_COMPRA')->insert($data);
    // }
    // public static function updateTemplateDetailsPurchases($data,$id_pdcompra)
    // {
    //     return DB::table('PLANTILLA_DETALLE_COMPRA')
    //         ->where('ID_PDCOMPRA', $id_pdcompra)
    //         ->update($data);
    // }
    // public static function deleteTemplateDetailsPurchases($id_pdcompra)
    // {
    //     return DB::table('PLANTILLA_DETALLE_COMPRA')
    //         ->where('ID_PDCOMPRA', '=', $id_pdcompra)
    //         ->delete();
    // }
    /* PEDIDO_PLANTILLA_COMPRA */
    public static function listOrdersTemplatesPurchases($id_pedido)
    {
        $query = DB::table('PEDIDO_PLANTILLA_COMPRA')
            ->select(
                DB::raw(
                    "
                    ID_PPCOMPRA,ID_PEDIDO,ID_ENTIDAD,ID_DEPTO,ID_FONDO,ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,ID_CTACTE,DETALLE,PORCENTAJE,CANTIDAD,IMPORTE,IMPORTE_ME,ESTADO,
                    FC_DEPARTAMENTO(ID_DEPTO) AS NOMBRE_DEPTO
                    "
                )
            )
            ->where("ID_PEDIDO",$id_pedido)
            ->orderBy('ID_PPCOMPRA')
            ->get();
        return $query;
    }
    public static function showOrdersTemplatesPurchases($id_ppcompra)
    {
        $query = DB::table('PEDIDO_PLANTILLA_COMPRA')
            // ->select(
            //     DB::raw(
            //         "
            //         ID_PDCOMPRA
            //         , DETALLE
            //         , PORCENTAJE
            //         , CANTIDAD
            //         , PRECIO
            //         , IMPORTE
            //         , IMPORTE_ME
            //         , ESTADO
            //         "
            //     )
            // )
            ->where('ID_PPCOMPRA', $id_ppcompra)
            ->first();
        return $query;
    }
    public static function addOrdersTemplatesPurchases($data)
    {
        return DB::table('PEDIDO_PLANTILLA_COMPRA')->insert($data);
    }
    public static function deleteOrdersTemplatesPurchases($id_ppcompra)
    {
        return DB::table('PEDIDO_PLANTILLA_COMPRA')
            ->where('ID_PPCOMPRA', '=', $id_ppcompra)
            ->delete();
    }
    public static function sumOrdTemPedImporteByIdPedido($id_pedido)
    {
        $sumImporte = DB::table('PEDIDO_PLANTILLA_COMPRA')
            ->where('PEDIDO_PLANTILLA_COMPRA.ID_PEDIDO', '=', $id_pedido)
            ->sum('PEDIDO_PLANTILLA_COMPRA.IMPORTE');
        return $sumImporte;
    }
    /* .COMPRA_ENTIDAD_DEPTO_PLANTILLA */
    public static function listPurchasesEntityDeptoTemplates($id_plantilla,$id_entidad)
    {
        $query = DB::table('compra_entidad_depto_plantilla')
            ->select(
                'id_edplantilla'
                , 'id_plantilla'
                , 'id_entidad'
                , 'id_depto'
            )
            ->where('id_plantilla', '=', $id_plantilla)
            ->where('id_entidad', '=', $id_entidad)
            ->orderBy('id_edplantilla')
            ->get();
        return $query;
    }
    public static function showPurchasesEntityDeptoTemplates($id_edplantilla,$id_entidad)
    {
        $query = DB::table('compra_entidad_depto_plantilla')
            ->select(
                DB::raw(
                    "
                    id_edplantilla
                    , id_plantilla
                    , id_entidad
                    , id_depto
                    "
                )
            )
            ->where('id_edplantilla', $id_edplantilla)
            ->where('id_entidad', '=', $id_entidad)
            ->first();
        return $query;
    }
    public static function addPurchasesEntityDeptoTemplates($data)
    {
        DB::table('compra_entidad_depto_plantilla')->insert($data);
        return $data;
    }
    public static function updatePurchasesEntityDeptoTemplates($data,$id_edplanilla)
    {
        DB::table('compra_entidad_depto_plantilla')
            ->where('id_edplanilla', $id_edplanilla)
            ->update($data);
    }
    public static function deletePurchasesEntityDeptoTemplates($id_edplantilla)
    {
        DB::table('compra_entidad_depto_plantilla')
            ->where('id_edplantilla', '=', $id_edplantilla)
            ->delete();
    }
    /* COMPRA_ENTIDAD_DEPTO_PLANTILLA. */
    public static function showPedidoRegistro_provision($id_pedido)
    {
        $query = DB::table('pedido_registro')
            ->select(
                DB::raw("
                    id_pedido
                    , id_entidad
                    , id_depto
                    , id_tipopedido
                    , id_gasto
                    , id_deptoorigen
                    , id_deptodestino
                    , id_evento
                    , id_voucher
                    , numero
                    , acuerdo
                    -- , fecha
                    -- , fecha_pedido
                    , TO_CHAR(fecha,'YYYY/MM/DD') fecha 
                    , TO_CHAR(fecha_pedido,'YYYY/MM/DD') fecha_pedido 
                    , motivo
                    , ' ' evento_nombre
                    , 'SI' hay_presupuesto
                    , 'Tarjeta de Credito' tipo_pago_nombre
                    , (select nombre from conta_entidad_depto where conta_entidad_depto.id_entidad=pedido_registro.id_entidad and conta_entidad_depto.id_depto=pedido_registro.id_deptoorigen and conta_entidad_depto.es_activo='1') AS departamento_nombre 
                    "
                )
            )
            ->where('id_pedido', $id_pedido)
            ->first();
        return $query;
    }
    public static function countPendingPedidoCompra($id_pedido)
    {
        $query = DB::table('pedido_compra')
            ->where('id_pedido','=',$id_pedido)
            ->where('estado','=','0')
            ->count();
        return $query;
    }
    public static function showExistsProviderDocumentProv($serie,$numero,$id_comprobante,$id_proveedor){
        if($id_comprobante == "91"){//No Domiciliados
            $and = "AND NUMERO = '".$numero."' ";
        }else{
            $and = "AND TO_NUMBER(NUMERO) = TO_NUMBER(".$numero.") ";
        }
        $query = "SELECT ID_COMPRA,ID_ENTIDAD 
                FROM COMPRA
                WHERE ID_PROVEEDOR = ".$id_proveedor."
                AND ID_COMPROBANTE = '".$id_comprobante."'
                AND SERIE = '".$serie."'
                -- AND TO_NUMBER(NUMERO) = TO_NUMBER(".$numero.")
                ".$and."
                AND ESTADO = '1'  ";
        $oQuery = DB::select($query);  
        return $oQuery;
    }
    public static function existsProviderDocument($serie,$numero,$id_comprobante,$id_proveedor,$id_compra)
    {
        $serie = strtolower($serie);
        $query = DB::table('eliseo.compra')
            ->whereRaw('lower(serie) = ?', [$serie])
            ->where('id_comprobante',$id_comprobante)
            ->where('id_proveedor',$id_proveedor);

        if($id_comprobante == "91" || $id_comprobante == "05"){// No Domiciliados/Boletos aéreos
            $query->whereRaw("NUMERO = '".$numero."'");
        }else{
            $query->whereRaw('to_number(numero) = to_number('.$numero.')');
        }

        if($id_compra !== "" && $id_compra !== 0 && !is_null($id_compra))
        {
            $query->where('ID_COMPRA','!=',$id_compra);
        }

        $voucherInfo = '';
        $exists = $query->exists();
        if($exists) {
            $compra = $query->first();
            if($compra->id_voucher) {
                $voucher = DB::table('eliseo.conta_voucher')->where('id_voucher', $compra->id_voucher)->first();
                $voucherInfo = !is_null($voucher) ? 'Voucher [ '.$voucher->id_entidad.'-'.$voucher->id_depto.' | N°: '.$voucher->numero.' | Fecha: '.$voucher->fecha.' | Lote aasinet: '.$voucher->lote.']' : '';
            }
        }

        $object = new \stdClass;
        $object->exists = $exists;
        $object->info = $voucherInfo;
        return $object;
        // $numero = str_pad($numero,7,"0", STR_PAD_LEFT);
        // $query = DB::table('compra')
        //     ->where('serie','=',$serie)
        //     ->where('numero','=',$numero)
        //     ->where('id_comprobante','=',$id_comprobante)
        //     ->where('id_proveedor','=',$id_proveedor);
        // if($id_compra !== "" && $id_compra !== 0)
        // {
        //     $query->where('ID_COMPRA','!=',$id_compra);
        // }
        // return $query->exists();
    }
    public static function showExistsProviderDocument($serie,$numero,$id_comprobante,$id_proveedor,$id_compra)
    {
        $query = DB::table('compra')
            ->select('id_compra')
            ->where('serie','=',$serie)
            ->where('numero','=',$numero)
            ->where('id_comprobante','=',$id_comprobante)
            ->where('id_proveedor','=',$id_proveedor);
        if($id_compra != "")
        {
            $query->where('id_compra','!=',$id_compra);
        }
        return $query->first();
    }
    public static function listPedidoDetalleP($id_pedido)
    {
        $query = DB::table('pedido_detalle')
            ->select('id_detalle'
                , 'id_pedido'
                , 'id_articulo'
                , 'detalle'
                , 'cantidad'
                , 'precio'
                , 'importe')
            ->where('id_pedido', $id_pedido)
            ->orderBy('id_detalle')
            ->get();
        return $query;
    }
    public static function listPedidoFileP($id_pedido)
    {
        $query = DB::table('pedido_file')
            ->select(
                'id_pfile'
                , 'id_pedido'
                , 'nombre'
                , 'formato'
                , 'url'
                , 'fecha'
                , 'tipo')
            ->where('id_pedido', $id_pedido)
            ->orderBy('id_pfile')
            ->get();
        return $query;
    }
    public static function listPedidoFile_ids($id_pedido,$id_pcompra)
    {
        $query = DB::table('pedido_file')
            ->select(
                DB::raw(
                    "id_pfile
                    , id_pedido
                    , nombre
                    , formato
                    , url
                    , TO_CHAR(fecha,'YYYY/MM/DD') as fecha
                    , tipo"
                )
            )
            ->where('id_pedido', $id_pedido)
            ->where('tipo', "1")
            ->where('id_pcompra', $id_pcompra)
            ->orderBy('id_pfile')
            ->get();
        return $query;
    }
    public static function listPedidoCompraP($id_pedido)
    {
        $query = DB::table('pedido_compra')
            ->select(
                'id_pcompra'
                , 'id_pedido'
                , 'id_compra'
                , 'id_moneda'
                , 'id_proveedor'
                , 'importe'
                , 'fecha'
                , 'estado')
            ->where('id_pedido', $id_pedido)
            ->orderBy('id_pcompra')
            ->get();
        return $query;
    }
    public static function listRequestPurchaseByIdPedido($id_pedido) /* igual que arriba */
    {
        $query = DB::table('pedido_compra')
            ->select(
                'id_pcompra'
                , 'id_pedido'
                , 'id_compra'
                , 'id_moneda'
                , 'id_proveedor'
                , 'importe'
                , 'fecha'
                , 'estado')
            ->where('id_pedido', $id_pedido)
            ->orderBy('id_pcompra')
            ->get();
        return $query;
    }
    public static function showPedidoCompra($id_pcompra)
    {
        $query = DB::table('pedido_compra')
            ->select(
                DB::raw("id_pcompra
                    , id_pedido
                    , id_compra
                    , id_moneda
                    , id_proveedor
                    , (select nombre from MOISES.VW_PERSONA_JURIDICA where MOISES.VW_PERSONA_JURIDICA.id_persona=pedido_compra.id_proveedor and rownum<=1) razonsocial_proveedor
                    , (select id_ruc from MOISES.VW_PERSONA_JURIDICA where MOISES.VW_PERSONA_JURIDICA.id_persona=pedido_compra.id_proveedor and rownum<=1) ruc_proveedor
                    -- , importe
                    -- , 0 importe_me
                    , case 
                        when id_moneda=9 then 0 
                        when id_moneda=7 then importe 
                        else importe 
                      end importe 
                    , case 
                        when id_moneda=9 then importe 
                        when id_moneda=7 then 0 
                        else 0 
                      end importe_me 
                    "
                )
            )
            ->where('id_pcompra', $id_pcompra)
            ->first();
        return $query;
    }
    public static function showPedidoCompra_Especial($id_pcompra)
    {
        $query = DB::table('pedido_compra')
            ->join('pedido_registro', 'pedido_compra.id_pedido', '=', 'pedido_registro.id_pedido')
            ->join('compra', 'pedido_compra.id_compra', '=', 'compra.id_compra')
            ->select(
                DB::raw(
                    "pedido_compra.id_pcompra
                    , pedido_compra.id_pedido
                    , pedido_compra.id_compra
                    -- , pedido_compra.id_moneda
                    -- , pedido_compra.id_proveedor
                    , pedido_registro.id_tipotransaccion
                    , compra.id_comprobante
                    , pedido_compra.importe
                    , pedido_registro.motivo
                    -- , compra.id_voucher voucher_id_voucher
                    , (select numero from conta_voucher where conta_voucher.id_voucher=compra.id_voucher) voucher_descripcion
                    , (select nombre from tipo_pago where tipo_pago.id_tipopago=pedido_registro.id_tipopago) tipo_pago_nombre
                    , TO_CHAR(pedido_compra.fecha,'YYYY/MM/DD') as fecha
                    , (select nombre||' '||paterno||' '||materno from MOISES.PERSONA where MOISES.PERSONA.id_persona=pedido_registro.id_persona) persona_nombre"
                )
            )
            ->where('id_pcompra', $id_pcompra)
            ->first();
        return $query;
    }
    public static function listPedidoCompraJoinProveedor($id_pedido)
    {
        $query = DB::table('pedido_compra')
            ->join('moises.persona_juridica', 'pedido_compra.id_proveedor', '=', 'moises.persona_juridica.id_persona')
            ->select(
                DB::raw(
                    "pedido_compra.id_pcompra
                    , pedido_compra.id_pedido
                    , pedido_compra.id_compra
                    , pedido_compra.id_moneda
                    , pedido_compra.id_proveedor
                    , pedido_compra.importe
                    , TO_CHAR(pedido_compra.fecha,'YYYY/MM/DD') as fecha
                    , pedido_compra.estado
                    , moises.persona_juridica.id_ruc"
                )
            )
            ->where('id_pedido', $id_pedido)
            ->orderBy('id_pcompra', 'desc')
            ->get();
        return $query;
    }
    public static function showCompra($id_compra)
    {
        $query = DB::table('compra')
            ->select(
                DB::raw(
                    "id_compra 
                    , id_entidad
                    , id_anho
                    , id_depto
                    , id_mes
                    , id_persona
                    , id_proveedor
                    , id_comprobante
                    , id_moneda
                    , id_voucher
                    , ' ' voucher_descripcion
                    , id_tiponota
                    , TO_CHAR(fecha_provision,'DD/MM/YYYY') fecha_provision
                    , TO_CHAR(fecha_doc,'DD/MM/YYYY') fecha_doc
                    , serie
                    , numero
                    , importe
                    , igv
                    , base
                    , base_gravada
                    , base_nogravada
                    , base_mixta
                    , base_sincredito
                    , base_inafecta
                    , igv_gravado
                    , igv_nogravado
                    , igv_mixto
                    , igv_sincredito
                    , estado"
                )
            )
            ->where('id_compra', $id_compra)
            ->first();
        return $query;
    }
    /* public static function listPurchase_x($id_proveedor,$id_comprobante,$serie,$numero) */
    public static function listPurchase_x($id_proveedor)
    {
        $query = DB::table('compra')
            ->select(
                DB::raw(
                    "id_compra 
                    -- , id_entidad
                    -- , id_anho
                    -- , id_depto
                    -- , id_mes
                    -- , id_persona
                    -- , id_proveedor
                    , id_comprobante
                    , (select nombre_corto from tipo_comprobante where tipo_comprobante.id_comprobante=compra.id_comprobante) nombrecorto_comprobante
                    -- , id_moneda
                    -- , id_voucher
                    -- , ' ' voucher_descripcion
                    -- , id_tiponota
                    -- , TO_CHAR(fecha_provision,'YYYY/MM/DD') fecha_provision
                    -- , TO_CHAR(fecha_doc,'YYYY/MM/DD') fecha_doc
                    , serie 
                    , numero 
                    , importe 
                    -- , igv
                    -- , base_gravada
                    -- , base_nogravada
                    -- , base_mixta
                    -- , base_sincredito
                    -- , base_inafecta
                    -- , igv_gravado
                    -- , igv_nogravado
                    -- , igv_mixto
                    -- , igv_sincredito
                    -- , estado
                    "
                )
            )
            ->where('id_proveedor', $id_proveedor)
            /* ->where('id_comprobante', $id_comprobante) */
            /* ->where('serie', $serie) */
            /* ->where('numero', 'like', '%'.$numero.'%') */
            ->get();
        return $query;
    }
    public static function showCompra_left_pcompra($id_pcompra)
    {
        $query = DB::table('compra')
            ->leftJoin('conta_voucher', 'compra.id_voucher', '=', 'conta_voucher.id_voucher')
            ->leftJoin(
                'pedido_compra', 'compra.id_compra', '=', 'pedido_compra.id_compra'
            )
            ->select(
                DB::raw("
                    compra.id_compra
                    , compra.id_parent
                    , compra.id_entidad
                    , compra.id_anho
                    , compra.id_depto
                    , compra.id_mes
                    , compra.id_persona
                    , compra.id_proveedor
                    , nvl((select nombre from MOISES.VW_PERSONA_JURIDICA where MOISES.VW_PERSONA_JURIDICA.id_persona=compra.id_proveedor and rownum<=1),'') razonsocial_proveedor
                    , nvl((select id_ruc from MOISES.VW_PERSONA_JURIDICA where MOISES.VW_PERSONA_JURIDICA.id_persona=compra.id_proveedor and rownum<=1),'') ruc_proveedor
                    , compra.id_comprobante
                    , compra.id_moneda
                    , compra.id_voucher
                    , conta_voucher.numero numero_voucher
                    , conta_voucher.fecha fecha_voucher
                    , compra.id_tiponota
                    , compra.fecha_doc -- TO_CHAR(compra.fecha_doc,'DD/MM/YYYY') as fecha_doc
                    , compra.serie
                    , compra.numero
                    , compra.importe
                    , compra.importe_me
                    , compra.igv
                    , compra.base_gravada
                    , compra.base_nogravada
                    , compra.base_mixta
                    , compra.base_sincredito
                    , compra.base_inafecta
                    , compra.igv_gravado
                    , compra.igv_nogravado
                    , compra.igv_mixto
                    , compra.igv_sincredito
                    , compra.otros
                    , compra.estado
                    , compra.es_ret_det
                    , compra.es_activo
                    , compra.tiene_kardex
                    , compra.es_electronica
                    , compra.retencion
                    , compra.tiene_suspencion
                    "
                    )
                    )
                    ->where('pedido_compra.id_pcompra', $id_pcompra)
                    ->first();
                    // , compra.detraccion_numero
                    // , compra.detraccion_fecha
                    // , compra.detraccion_importe
                    // , compra.detraccion_banco
                    // , compra.retencion_importe
                    // , compra.retencion_serie
                    // , compra.retencion_numero
                    // , compra.retencion_fecha
        return $query;
    }
    public static function listCompraDetalleC($id_compra)
    {
        $query = DB::table('compra_detalle')
            ->select(
                'id_detalle'
                , 'id_compra'
                , 'id_dinamica'
                , 'detalle'
                , 'cantidad'
                , 'precio'
                , 'importe'
                , 'igv'
                , 'estado')
            ->where('id_compra', $id_compra)
            ->orderBy('id_detalle')
            ->get();
        return $query;
    }
    public static function listCompraAsientoC($id_compra)
    {
        $query = DB::table('compra_asiento')
            ->select(
                DB::raw(
                    "id_casiento
                    , id_compra
                    , id_cuentaaasi
                    , 'x' nombre_cuentaaasi
                    , id_restriccion
                    , id_ctacte
                    , ' ' nombre_ctacte
                    , id_fondo
                    , (select conta_fondo.nombre from conta_fondo where conta_fondo.id_fondo=compra_asiento.id_fondo) nombre_fondo
                    , id_depto
                    , (select conta_entidad_depto.nombre
                        from compra
                          inner join conta_entidad_depto
                          on -- compra.id_entidad=conta_entidad_depto.id_entidad
                            compra.id_depto=conta_entidad_depto.id_depto
                          where compra_asiento.id_compra=compra.id_compra
                            -- and compra_asiento.id_depto=compra.id_depto
                            and rownum<=1
                      ) nombre_depto 
                    , importe
                    , descripcion
                    , editable
                    , id_parent
                    , id_tiporegistro
                    , dc"
                )
            )
            ->where('id_compra', $id_compra)
            ->orderBy('id_casiento')
            ->get();
        return $query;
    }
    public static function listCompraAsiento_Prepare($id_compra)
    {
        $query = DB::table('compra_asiento')
            ->select(
                DB::raw(
                    "id_casiento
                    , id_compra
                    , id_cuentaaasi
                    , 'x' nombre_cuentaaasi
                    , id_restriccion
                    , id_ctacte
                    , ' ' nombre_ctacte
                    , id_fondo
                    , (select conta_fondo.nombre from conta_fondo where conta_fondo.id_fondo=compra_asiento.id_fondo) nombre_fondo
                    , id_depto
                    , (select conta_entidad_depto.nombre
                        from compra
                          inner join conta_entidad_depto
                          on -- compra.id_entidad=conta_entidad_depto.id_entidad
                            compra.id_depto=conta_entidad_depto.id_depto
                          where compra_asiento.id_compra=compra.id_compra
                            -- and compra_asiento.id_depto=compra.id_depto
                            and rownum<=1
                      ) nombre_depto 
                    , (case when dc='C' then (importe*-1) else importe end) importe
                    , descripcion
                    , editable
                    , id_parent
                    , id_tiporegistro
                    , dc"
                )
            )
            ->where('id_compra', $id_compra)
            ->orderBy('id_casiento')
            ->get();
        return $query;
    }
    public static function listCompraAsiento_Especial($id_compra)
    {
        $query = DB::table('compra_asiento')
            ->select(
                DB::raw(
                    "id_casiento
                    , id_compra
                    , id_cuentaaasi cuentaaasi_id_cuentaaasi
                    , (select nombre from conta_cta_denominacional where conta_cta_denominacional.id_cuentaaasi=compra_asiento.id_cuentaaasi and conta_cta_denominacional.id_restriccion=compra_asiento.id_restriccion) cuentaaasi_nombre
                    , id_restriccion
                    , id_ctacte ctacte_id_ctacte
                    , (select nombre from conta_entidad_cta_cte where conta_entidad_cta_cte.id_ctacte=compra_asiento.id_ctacte and rownum<=1) ctacte_nombre
                    , id_fondo fondo_id_fondo
                    , (select conta_fondo.nombre from conta_fondo where conta_fondo.id_fondo=compra_asiento.id_fondo) fondo_nombre
                    , id_depto depto_id_depto
                    , (select conta_entidad_depto.nombre
                        from compra
                          inner join conta_entidad_depto
                          on -- compra.id_entidad=conta_entidad_depto.id_entidad
                            compra.id_depto=conta_entidad_depto.id_depto
                          where compra_asiento.id_compra=compra.id_compra
                            -- and compra_asiento.id_depto=compra.id_depto
                            and rownum<=1
                      ) depto_nombre 
                    , importe
                    , descripcion
                    , editable
                    , id_parent
                    , id_tiporegistro
                    , dc"
                )
            )
            ->where('id_compra', $id_compra)
            ->orderBy('id_casiento')
            ->get();
        return $query;
    }
    public static function validaImporteDCCompraAsiento($id_compra)
    {
        $query = "
        select total_d 
          , total_c 
          , case when (total_d-total_c)=0 then 'SI' else 'NO' end valida 
        from ( 
          select 
            nvl(
              ( 
                select sum(importe)
                from compra_asiento
                where id_compra=$id_compra
                  and dc='D' 
              ),0) total_d
            , nvl(
              (
                select sum(importe)
                from compra_asiento
                where id_compra=$id_compra
                  and dc='C'
              ),0) total_c
          from dual
        )
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    /* PROCESS_PASO_RUN */
    public static function showProcessPasoRun($id_registro,$id_paso)
    {
        $query = DB::table('process_paso_run')
            ->select(
                'id_detalle'
                ,'id_registro'
                ,'id_paso'
                ,'id_persona'
                ,'fecha'
                ,'detalle'
                ,'numero'
                ,'revisado'
                ,'ip'
                ,'estado'
                ,'id_paso_next')
            ->where('id_registro' , '=', $id_registro)
            ->where('id_paso' , '=', $id_paso)
            /* ->where('estado' , '=', '0') */
            ->first();
        return $query;
    }
    public static function showProcessPasoRunByRegistroPasoEstado($id_registro,$id_paso,$estado)
    {
        $query = DB::table('process_paso_run')
            ->select(
                'id_detalle'
                ,'id_registro'
                ,'id_paso'
                ,'id_persona'
                ,'fecha'
                ,'detalle'
                ,'numero'
                ,'revisado'
                ,'ip'
                ,'estado'
                ,'id_paso_next')
            ->where('id_registro' , '=', $id_registro)
            ->where('id_paso' , '=', $id_paso)
            ->where('estado' , '=', $estado)
            ->first();
        return $query;
    }
    public static function showProcessPasoRun22($id_registro,$id_paso)
    {
        $query = DB::table('process_paso_run')
            ->select('id_detalle'
                ,'id_registro'
                ,'id_paso'
                ,'id_persona'
                ,'fecha'
                ,'detalle'
                ,'numero'
                ,'revisado'
                ,'ip'
                ,'estado'
                ,'id_paso_next')
            ->where('id_registro' , '=', $id_registro)
            ->where('id_paso' , '=', $id_paso)
            ->first();
        return $query;
    }
    // Deprecated
    public static function addProcessPasoRun($data)
    {
        DB::table('process_paso_run')->insert($data);
        return $data;
    }
    public static function updateProcessPasoRun($data,$id_detalle)
    {
        $result = DB::table('PROCESS_PASO_RUN')
            ->where('ID_DETALLE', $id_detalle)
            ->update($data);
        return $result;
    }
    public static function existsProcessPasoRunByLlave($id_registro,$llave)
    {
        $query = DB::table('PROCESS_PASO_RUN')
            ->join('PROCESS_COMPONENTE_PASO', 'PROCESS_PASO_RUN.ID_PASO', '=', 'PROCESS_COMPONENTE_PASO.ID_PASO')
            ->join('PROCESS_COMPONENTE', 'PROCESS_COMPONENTE_PASO.ID_COMPONENTE', '=', 'PROCESS_COMPONENTE.ID_COMPONENTE')
            // ->where('SERIE',$serie)
            // ->where('NUMERO',$numero)
            // ->where('ID_COMPROBANTE',$id_comprobante)
            // ->where('ID_PROVEEDOR',$id_proveedor)
            ->where('PROCESS_COMPONENTE.LLAVE',$llave)
            ->where('PROCESS_PASO_RUN.ID_REGISTRO',$id_registro)
            // ->where('PROCESS_PASO_RUN.ID_PASO',$id_paso)
            ->exists();
        // if($id_compra)
        // {
        //     $query->where('ID_COMPRA','!=',$id_compra);
        // }
        // return $query->exists();
        return $query;
    }
    public static function spProcessStepRunNext($data)
    {
        $error      = 0;
        $msg_error  = '';
        $objReturn  = [];
        try
        {
            for($x = 1 ; $x <= 200 ; $x++)
            {
                $msg_error .= "0";
            }
            $pdo    = DB::getPdo();
            $stmt   = $pdo->prepare("begin PKG_PROCESS.SP_PROCESO_PASO_RUN_NEXT(
            :P_CODIGO, 
            :P_ID_PEDIDO, 
            :P_ID_PERSONA, 
            :P_ID_ENTIDAD, 
            :P_DETALLE, 
            :P_IP, 
            :P_ERROR, 
            :P_MSGERROR,
            :P_LLAVE
            );
            end;");
            $stmt->bindParam(':P_CODIGO', $data["codigo"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PEDIDO', $data["id_pedido"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ENTIDAD', $data["id_entidad"], PDO::PARAM_INT);
            $stmt->bindParam(':P_DETALLE', $data["detalle"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IP', $data["ip"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->bindParam(':P_LLAVE', $data["llave"], PDO::PARAM_STR);
            $stmt->execute();
            $oreturn['id_pedido']     = $data["id_pedido"];
            $oreturn['error']     = $error;
            $oreturn['message']   = $msg_error;
            RETURN $oreturn;
        }
        catch(Exception $e)
        {
            $oreturn['id_pedido']     = null;
            $oreturn['error']     = 1;
            $oreturn['message']   = $e->getMessage();
            RETURN $oreturn;
        }
    }
    /* xxx */
    public static function addOrdersDetailsXX($data){
        $error      = 0;
        $msg_error  = '';
        $p_id_detalle       = null;
        try {
            for($x=1 ; $x <= 200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("BEGIN PKG_ORDERS.SP_INSERT_PEDIDO_DETALLE(
                :P_ID_PEDIDO,
                :P_ID_ALMACEN,
                :P_ID_ARTICULO,
                :P_DETALLE,
                :P_CANTIDAD,
                :P_PRECIO,
                :P_FECHA_INICIO,
                :P_FECHA_FIN,
                :P_HORA_INICIO,
                :P_HORA_FIN,
                :P_ID_DETALLE,
                :P_ERROR,
                :P_MSGERROR
                );
                END;");
                $stmt->bindParam(':P_ID_PEDIDO', $data["id_pedido"], PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ALMACEN', $data["id_almacen"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_ARTICULO', $data["id_articulo"], PDO::PARAM_INT);
                $stmt->bindParam(':P_DETALLE', $data["detalle"], PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $data["cantidad"], PDO::PARAM_STR);
                $stmt->bindParam(':P_PRECIO', $data["precio"], PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_INICIO', $data["fecha_inicio"], PDO::PARAM_STR);
                $stmt->bindParam(':P_FECHA_FIN', $data["fecha_fin"], PDO::PARAM_STR);
                $stmt->bindParam(':P_HORA_INICIO', $data["hora_inicio"], PDO::PARAM_STR);
                $stmt->bindParam(':P_HORA_FIN', $data["hora_fin"], PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DETALLE', $p_id_detalle, PDO::PARAM_INT);
                $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['id_pedido']   = $p_id_detalle;
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            $objReturn['success'] = true;
            //return $result = true;
        }catch(Exception $e){
             $objReturn['id_pedido']   = null;
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error.", ORA: ".$e->getMessage();
            $objReturn['success'] = false;
            //return false;
        }
        return $objReturn;
    }
    public static function addOrdersDetails($data)
    {
        $result = DB::table('pedido_detalle')->insert($data);
        return $result;
    }
    public static function addOrdersMovilidad($data)
    {
        try
        {
            $result = DB::table('pedido_movilidad')->insert($data);
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }
    public static function updateOrdersMovilidad($data,$id_detalle){
        // dd($data);
        try{
            $result = DB::table('pedido_movilidad')
                ->where('id_movilidad', $id_detalle)
                ->update($data);
        }catch(Exception $e){
            $result = false;
        }
        return $result;
    }
    public static function updateOrdersDetails($data,$id_detalle)
    {
        try
        {
            $result = DB::table('pedido_detalle')
                ->where('id_detalle', $id_detalle)
                ->update($data);
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }
    public static function deleteOrdersDetails($id_detalle)
    {
        try
        {
            $count= DB::table('eliseo.pedido_file')->where('id_detalle', '=', $id_detalle)->count();
            if ($count>0) {
                $filePedidoDetalle = DB::table('eliseo.pedido_file')->where('id_detalle', '=', $id_detalle)->delete();
                // $resp = ComunData::deleteFilesDirectorio($carpeta, $fileAdjunto['filename'], 'E');
            }

            $result = DB::table('pedido_detalle')
                ->where('id_detalle', '=', $id_detalle)
                ->delete();
            if ($result) {

            }
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }
    public static function addPedidoDetalle($data)
    {
        DB::table('pedido_detalle')->insert($data);
        return $data;
    }
    public static function listReceiptDetails($id_compra)
    {
        $query = DB::table('compra_detalle')
            ->select(
                'id_detalle'
                ,'id_compra'
                ,'detalle'
                ,'cantidad'
                ,'precio'
                ,'importe'
                ,'orden'
            )
            ->where('id_compra' , '=', $id_compra)
            ->orderBy('orden')
            ->get();
        return $query;
    }
    public static function deleteCompra($id_compra)
    {
        DB::table('compra')->where('id_compra', '=', $id_compra)->delete();
    }
    public static function deleteCompraAsientoCreditoMore($id_compra)
    {
        DB::table('compra_asiento')
            ->where('id_compra', '=', $id_compra)
            ->where('EDITABLE', '=', 'N')
            ->delete();
    }

    public static function updateCompraDetalle($data,$id_detalle)
    {
        DB::table('compra_detalle')
            ->where('id_detalle', $id_detalle)
            ->update($data);
    }
    public static function listFilesReceipt($id_pedido)
    {
        $query = DB::table('pedido_file')
            ->select('id_pfile'
                ,'id_pedido'
                ,'nombre'
                ,'formato'
                ,'url'
                ,'fecha'
                ,'tipo'
                ,'tamanho'
                ,'id_pcompra'
            )
            ->where('id_pedido' , '=', $id_pedido)
            ->orderBy('id_pfile')
            ->get();
        return $query;
    }
    public static function showPedidoFile($id_pfile)
    {
        $query = DB::table('pedido_file')
            ->select(
                'id_pfile'
                ,'id_pedido'
                ,'nombre'
                ,'formato'
                ,'url'
                ,'fecha'
                ,'tipo'
                ,'estado'
                ,'id_pcompra'
            )
            ->where('id_pfile' , '=', $id_pfile)
            ->first();
        return $query;
    }
    public static function getPedidoFile_elejido($id_pedido)
    {
        $query = DB::table('pedido_file')
            ->select(
                'id_pfile'
                ,'id_pedido'
                ,'nombre'
                ,'formato'
                ,'url'
                ,'fecha'
                ,'tipo'
                ,'estado'
                ,'id_pcompra'
            )
            ->where('id_pedido' , '=', $id_pedido)
            ->where('seleccionado' , '=', "S")
            ->first();
        return $query;
    }
    public static function onePedidoFileByIdPcompraTipoFormato($id_pcompra,$tipo,$formato)
    {
        $query = DB::table('pedido_file')
            ->select(
                DB::raw("
                    id_pfile
                    , id_pedido
                    , nombre
                    , formato
                    , url
                    , fecha
                    , tipo
                    , (select id_voucher from pedido_registro where pedido_registro.id_pedido=pedido_file.id_pedido) AS id_voucher 
                    "
                )
            )
            ->where('id_pcompra', $id_pcompra)
            ->where('tipo', $tipo)
            ->where('formato', $formato)
            ->first();
        return $query;
    }
    public static function showOrdersFilesByParams($id_pcompra, $tipo, $formato)
    {
        $query = DB::table('PEDIDO_FILE')
            ->select(
                'ID_PFILE',
                'ID_PEDIDO',
                'NOMBRE',
                'FORMATO',
                'URL',
                'FECHA',
                'TIPO'
            )
            ->where('ID_PCOMPRA', $id_pcompra)
            // ->where('TIPO', $tipo)
            ->where('FORMATO', 'XML')
            ->first();
        return $query;
    }
    public static function getPersonaVirtual_idpersona($id_persona)
    {
        $query = DB::table('moises.persona_virtual')
            ->select(
                'id_virtual'
                ,'id_persona'
                ,'id_tipovirtual'
                ,'direccion'
                ,'comentario'
                ,'gth'
            )
            ->where('id_persona' , '=', $id_persona)
            ->first();
        return $query;
    }
    /* EXTRAS */
    public static function listTypesPay()
    {
        $query = DB::table('tipo_pago')
            ->select(
                'id_tipopago'
                /* ,'num_grupo' */
                ,'nombre'
            )
            ->orderBy('nombre')
            ->get();
        return $query;
    }
    public static function addContaAsiento($data)
    {
        DB::table('conta_asiento')->insert($data);
        return $data;
    }
    public static function updateAccountantSeat($data, $id_tipoorigen, $id_origen)
    {
        DB::table('conta_asiento')
            /* ->where('id_pedido', $id_pedido) */
            ->where('id_tipoorigen', $id_tipoorigen)
            ->where('id_origen', $id_origen)
            ->update($data);
        return $data;
    }
    public static function listTypesReceipts()
    {
        $query = DB::table('tipo_comprobante')
            ->select('id_comprobante'
                /* ,'num_grupo' */
                ,'nombre'
                ,'tipo'
            )
            ->orderBy('id_comprobante')
            ->get();
        return $query;
    }
    public static function lisTypesCurrency()
    {
        $query = DB::table('conta_moneda')
            ->select('id_moneda'
                ,'simbolo'
                ,'siglas'
                ,'nombre'
                ,'cod_sunat'
                ,'cod_interno'
            )
            ->where('estado', '1')
            ->orderBy('nombre')
            ->get();
        return $query;
    }
    public static function showLegalPersonVW($id_persona)
    {
        $query = DB::table('MOISES.VW_PERSONA_JURIDICA')
            ->select(
                'id_ruc'
                ,'id_persona'
            )
            ->where('id_persona' , '=', $id_persona)
            ->first();
        return $query;
    }
    public static function showNaturalPersonVW($id_persona)
    {
        $query = DB::table('MOISES.VW_PERSONA_NATURAL')
            ->select(
                'NUM_DOCUMENTO as id_ruc'
                ,'id_persona'
            )
            ->where('id_persona' , '=', $id_persona)
            ->where('id_tipodocumento' , '=', 6)
            ->first();
        return $query;
    }
    public static function lisBanksAccountBox()
    {
        $query = DB::table('caja_entidad_financiera')
            ->join('caja_cuenta_bancaria', 'caja_entidad_financiera.id_banco', '=', 'caja_cuenta_bancaria.id_banco')
            ->select('caja_cuenta_bancaria.id_ctabancaria'
                ,'caja_entidad_financiera.sigla'
                ,'caja_cuenta_bancaria.nombre'
                ,'caja_cuenta_bancaria.cuenta_corriente'
            )
            ->orderBy('caja_entidad_financiera.sigla')
            ->get();
        return $query;
    }
    public static function listFunds()
    {
        $query = DB::table('conta_fondo')
            /* ->join('caja_cuenta_bancaria', 'caja_entidad_financiera.id_banco', '=', 'caja_cuenta_bancaria.id_banco') */
            ->select(
                'id_fondo'
                ,'nombre'
            )
            ->orderBy('nombre')
            ->get();
        return $query;
    }
    public static function preparaCompraAsiento($id_compra)
    {
        $query = "
        select 
        tabla.* 
        , case 
          when id_indicador='BASE' then 
            case  
              when conta = 1 then base_gravada*porcentaje*dc_val 
              when conta > 1 then base_gravada*(porcentaje/conta)*dc_val 
              else base_gravada*dc_val 
            end 
          when id_indicador='IGV' then 
            igv_gravado*dc_val 
          when id_indicador='IMPORTE' then 
            importe*dc_val 
          else 00.00 
        end grabar_value 
        from ( 
        select 
        6 id_tipoorigen -- proyecto purchases 
        , c.id_compra -- 1 id_compra -- sale de al compra 
        , 10 fondo -- siempre 10 
        , ca.id_depto 
        , cda.id_cuentaempresarial 
        , ca.id_ctacte 
        , cda.id_restriccion 
        , c.base_gravada -- 101.00 base -- importe -- sale de la compra 
        , c.igv_gravado -- 18 igv -- sale de compra 
        , c.importe -- 118 importe -- sale de compra 
        -- , memo -- no usar 
        , ' ' voucher -- jalar de compra 
        -- , parent_id -- no usar 
        -- , ref_id -- no usar 
        -- extras 
        , cda.dc -- C[-]-D 
        , case 
            when cda.dc='C' then -1 
            when cda.dc='D' then 1 
            else 1 
          end dc_val 
        , cda.id_indicador -- IGV[*-]-BASE[*-]-IMPORTE  
        , (select count(*) from compra_asiento ca2 where ca2.id_asiento=ca.id_asiento and ca2.id_compra=ca.id_compra) conta 
        , cda.porcentaje 
        from compra_asiento ca 
        inner join compra c 
        on ca.id_compra=c.id_compra 
        inner join conta_dinamica_asiento cda 
        on ca.id_asiento=cda.id_asiento 
        where ca.id_compra=".$id_compra." 
        ) tabla 
        ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listExchangeRates()
    {
        $query = "
        select * 
        from tipo_cambio tc 
        where tc.id_moneda=9  
        and tc.fecha in (select max(tc2.fecha) from tipo_cambio tc2 where tc2.id_moneda=9 )
        ";
        $oQuery = DB::select(DB::raw($query))[0];        
        return $oQuery;
    }
    public static function listPendingVouchers_tipCompra()
    {
        $query = "
        select id_voucher 
          ,id_anho 
          ,id_mes 
          ,numero 
          ,numero detalle_vou 
          -- ,fecha 
        from conta_voucher 
        -- where lote is not null 
        where id_tipovoucher=2 
        order by id_voucher desc
        ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function getValidarPorcentaje($id_compra)
    {
        $query = "
        select nvl(sum(porcentaje)) porcentaje 
        from( 
        select distinct ca.id_asiento, ca.porcentaje, id_indicador 
        from compra_asiento ca 
        inner join conta_dinamica_asiento cda 
        on ca.id_asiento=cda.id_asiento 
        where ca.id_compra=$id_compra and id_indicador='BASE' 
        ) 
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function chooserAasinet($id_entidad, $id_cuentaaasi)
    {
        $query = "
        SELECT 
        CASE WHEN count(a.ID_CTACTE) >0 THEN 'S' ELSE 'N' END AS requiere_cta_cte 
        FROM VW_CONTA_CTACTE a, CONTA_CTA_DENOMINACIONAL b 
        WHERE a.ID_TIPOCTACTE = b.ID_TIPOCTACTE 
        AND a.ID_ENTIDAD = $id_entidad 
        AND b.ID_CUENTAAASI = $id_cuentaaasi 
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showVWPersonaJuridicaByRuc($id_ruc)
    {
        $query = DB::table('MOISES.VW_PERSONA_JURIDICA')
            ->select(
                'id_ruc'
                ,'id_persona'
            )
            ->where('id_ruc' , '=', $id_ruc)
            ->first();
        return $query;
    }
    public static function listReportPurchases($id_anho, $id_mes, $id_empresa, $id_entidad, $id_depto, $text_search="")
    {
        $query = "
        select * from (
        select 
          CO.ID_ENTIDAD entidad 
          ,co.id_depto 
          ,co.id_mes 
          ,CO.ID_VOUCHER as lote 
          ,(SELECT vv.NUMERO FROM CONTA_VOUCHER vv WHERE vv.ID_VOUCHER = co.ID_VOUCHER) as lote_numero
          ,co.correlativo 
          ,(select p.nombre||' '||p.paterno||' '||p.materno from MOISES.PERSONA p where p.id_persona=co.id_persona) usuario 
          ,(select u.email FROM USERS u WHERE u.id = co.ID_PERSONA) username
          ,null gasto -- 'no se GASTO' 
          ,coalesce(ELISEO.FC_CONTA_CUO_COMPLETO(co.ID_TIPOORIGEN ,CO.ID_COMPRA, CO.ID_ENTIDAD, CO.ID_ANHO, CO.ID_MES),'') as cuo
          ,TO_CHAR(co.fecha_doc,'DD/MM/YYYY') fecha_emision -- co.fecha_doc fecha_emision 
          ,case CO.ID_COMPROBANTE when '14' then  
                coalesce(to_char(CO.FECHA_DOC,'dd/mm/yyyy'),' ')
                else '' end AS fecha_vto -- co.fecha_doc 
          ,co.id_comprobante AS comp_pago_tipo -- (select tc.nombre from tipo_comprobante tc where tc.id_comprobante=co.id_comprobante) comp_pago_tipo 
          ,co.serie AS comp_pago_serie 
          ,extract(year from co.fecha_doc) AS comp_pago_anho_emision 
          ,co.numero comp_pago_nro 
          ,'RUC' infor_proveedor_tipo 
          ,B.ID_RUC as infor_proveedor_numero 
          ,b.NOMBRE AS infor_proveedor_razon_social 
    ,coalesce(CO.BASE_GRAVADA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS compra_gravada_bi
    ,coalesce(CO.IGV_GRAVADO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS compra_gravada_igv  
    ,coalesce(CO.BASE_MIXTA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS exportacion_bi
    ,coalesce(CO.IGV_MIXTO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS exportacion_igv
    ,coalesce(CO.BASE_NOGRAVADA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS sincredito_bi  
    ,coalesce(CO.IGV_NOGRAVADO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS sincredito_igv  
    ,coalesce(CO.BASE_SINCREDITO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS compras_no_grabadas  
          ,null isc 
          ,coalesce(CO.OTROS * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS otros_tributos -- (CASE WHEN co.id_comprobante = '01' THEN to_char(co.otros) ELSE ' ' END) otros_tributos 
          ,co.importe importe_total 
          ,to_char(co.importe,'9999999,999.00') importe_total_text 
          ,null comprob_emit_sujet_no_domi 
          ,coalesce(trim(FC_CAJA_DETRACCION_NUMERO(CO.id_compra)),'') AS const_depsi_detrac_numero      
      ,coalesce(FC_CAJA_DETRACCION_FECHA(CO.id_compra),'') AS const_depsi_detrac_fecha
          ,coalesce(CO.TIPOCAMBIO,0) as tc 
          ,coalesce(to_char(C.FECHA_DOC,'dd/mm/yyyy'),to_char(X.FECHA_DOC,'dd/mm/yyyy')) AS ref_comp_pago_doc_fecha
          ,coalesce(C.ID_COMPROBANTE,X.ID_COMPROBANTE) AS ref_comp_pago_doc_tipo
          ,coalesce(C.SERIE,X.SERIE) AS ref_comp_pago_doc_serie
          ,coalesce(C.NUMERO,X.NUMERO) AS ref_comp_pago_doc_numero         
          ,(CASE WHEN co.id_comprobante = '01' and CO.es_ret_det = 'R' THEN '1' ELSE '' END) retencion 
          ,co.id_entidad cod_entidad 
          -- +++++ 
        FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_NATURAL_LEGAL B, COMPRA CO LEFT JOIN COMPRA c ON (CO.ID_PARENT = c.ID_COMPRA AND CO.ID_PROVEEDOR = C.ID_PROVEEDOR)
        LEFT JOIN COMPRA_SALDO X ON CO.ID_PARENT = X.ID_SALDO AND CO.ID_PROVEEDOR = X.ID_PROVEEDOR
      WHERE CO.ID_PROVEEDOR = b.ID_PERSONA
      AND CO.ID_ENTIDAD = E.ID_ENTIDAD
      AND E.ID_EMPRESA = $id_empresa
      AND CO.id_anho = $id_anho
      AND CO.id_mes = $id_mes
      AND (CO.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
      AND (CO.ID_DEPTO = '$id_depto' OR '*' = '$id_depto' )
      AND CO.ID_COMPROBANTE NOT IN ('02')
      AND CO.Estado = '1'
      ) t
      where upper(infor_proveedor_numero) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(infor_proveedor_razon_social) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(username) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(importe_total_text) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(cuo) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(fecha_emision) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(comp_pago_serie) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(comp_pago_nro) like '%'||upper(replace('$text_search',' ','%'))||'%'
      ORDER BY t.entidad,t.id_depto, t.id_mes ,t.lote_numero ,t.correlativo 
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listReportPurchases_total($id_anho, $id_mes, $id_empresa, $id_entidad, $id_depto)
    {
        $query = "
        select 
    sum(coalesce(CO.BASE_GRAVADA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS compra_gravada_bi
    ,sum(coalesce(CO.IGV_GRAVADO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS compra_gravada_igv  
    ,sum(coalesce(CO.BASE_MIXTA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS exportacion_bi
    ,sum(coalesce(CO.IGV_MIXTO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS exportacion_igv
    ,sum(coalesce(CO.BASE_NOGRAVADA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS sincredito_bi  
    ,sum(coalesce(CO.IGV_NOGRAVADO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS sincredito_igv  
    ,sum(coalesce(CO.BASE_SINCREDITO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS compras_no_grabadas  
          ,0 isc 
          ,sum(coalesce(CO.OTROS * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS otros_tributos -- (CASE WHEN co.id_comprobante = '01' THEN to_char(co.otros) ELSE ' ' END) otros_tributos 
          ,sum(co.importe) importe_total 
          ,sum((CASE WHEN co.id_comprobante = '01' and CO.es_ret_det = 'R' THEN 1 ELSE 0 END)) retencion 
        FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_NATURAL_LEGAL B, COMPRA CO LEFT JOIN COMPRA c ON (CO.ID_PARENT = c.ID_COMPRA )
      WHERE CO.ID_PROVEEDOR = b.ID_PERSONA
      AND CO.ID_ENTIDAD = E.ID_ENTIDAD
      AND E.ID_EMPRESA = $id_empresa
      AND CO.id_anho = $id_anho
      AND CO.id_mes = $id_mes
      AND (CO.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
      AND (CO.ID_DEPTO = '$id_depto' OR '*' = '$id_depto' )
      AND CO.ID_COMPROBANTE NOT IN ('02')
      AND CO.Estado = '1'
      ORDER BY CO.ID_COMPRA
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMyReportPurchases($id_anho, $id_mes, $id_entidad, $text_search="", $id_voucher, $id_persona)
    {
        $voucher = "";
        if($id_voucher){
            $voucher = "AND CO.id_voucher = $id_voucher";
        }

        $persona = "";
        if($id_persona != ""){
            $persona = "AND CO.ID_PERSONA = $id_persona";
        } else {
            $persona = "-- AND CO.ID_PERSONA = $id_persona";
        }
        $query = "
        select * from (
        select 
          CO.ID_ENTIDAD entidad 
          ,co.id_depto 
          ,co.id_compra 
          ,co.id_mes 
          ,CO.ID_VOUCHER as lote 
          ,(SELECT vv.NUMERO FROM CONTA_VOUCHER vv WHERE vv.ID_VOUCHER = co.ID_VOUCHER) as lote_numero
          ,co.correlativo 
          ,(select p.nombre||' '||p.paterno||' '||p.materno from MOISES.PERSONA p where p.id_persona=co.id_persona) usuario 
          ,(select u.email FROM USERS u WHERE u.id = co.ID_PERSONA) username
          ,null gasto -- 'no se GASTO' 
          ,coalesce(ELISEO.FC_CONTA_CUO_COMPLETO(co.ID_TIPOORIGEN ,CO.ID_COMPRA, CO.ID_ENTIDAD, CO.ID_ANHO, CO.ID_MES),'') as cuo
          ,TO_CHAR(co.fecha_doc,'DD/MM/YYYY') fecha_emision -- co.fecha_doc fecha_emision 
          ,case CO.ID_COMPROBANTE when '14' then  
                coalesce(to_char(CO.FECHA_DOC,'dd/mm/yyyy'),' ')
                else '' end AS fecha_vto -- co.fecha_doc 
          ,co.id_comprobante AS comp_pago_tipo -- (select tc.nombre from tipo_comprobante tc where tc.id_comprobante=co.id_comprobante) comp_pago_tipo 
          ,co.serie AS comp_pago_serie 
          ,extract(year from co.fecha_doc) AS comp_pago_anho_emision 
          ,co.numero comp_pago_nro 
          ,'RUC' infor_proveedor_tipo 
          ,B.ID_RUC as infor_proveedor_numero 
          ,b.NOMBRE AS infor_proveedor_razon_social 
    ,coalesce(CO.BASE_GRAVADA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS compra_gravada_bi
    ,coalesce(CO.IGV_GRAVADO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS compra_gravada_igv  
    ,coalesce(CO.BASE_MIXTA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS exportacion_bi
    ,coalesce(CO.IGV_MIXTO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS exportacion_igv
    ,coalesce(CO.BASE_NOGRAVADA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS sincredito_bi  
    ,coalesce(CO.IGV_NOGRAVADO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS sincredito_igv  
    ,coalesce(CO.BASE_SINCREDITO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS compras_no_grabadas  
          ,null isc 
          ,coalesce(CO.OTROS * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS otros_tributos -- (CASE WHEN co.id_comprobante = '01' THEN to_char(co.otros) ELSE ' ' END) otros_tributos 
          -- ,co.importe importe_total 
          ,coalesce(CO.importe * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0) AS importe_total
          ,to_char(co.importe,'9999999,999.00') importe_total_text 
          ,null comprob_emit_sujet_no_domi 
          ,coalesce(trim(FC_CAJA_DETRACCION_NUMERO(CO.id_compra)),'') AS const_depsi_detrac_numero      
      ,coalesce(FC_CAJA_DETRACCION_FECHA(CO.id_compra),'') AS const_depsi_detrac_fecha
          ,coalesce(CO.TIPOCAMBIO,0) as TIPOCAMBIO 
      ,coalesce(to_char(C.FECHA_DOC,'dd/mm/yyyy'),'') AS ref_comp_pago_doc_fecha
      ,coalesce(C.ID_COMPROBANTE,' ') AS ref_comp_pago_doc_tipo
      ,coalesce(C.SERIE,'0') AS ref_comp_pago_doc_serie
      ,coalesce(C.NUMERO,'0') AS ref_comp_pago_doc_numero            
          ,(CASE WHEN co.id_comprobante = '01' and CO.es_ret_det = 'R' THEN '1' ELSE '' END) retencion 
          ,co.id_entidad cod_entidad 
          ,COALESCE(CO.IMPORTE_ME,0) IMPORTE_ME,
          (SELECT X.COD_SUNAT FROM CONTA_MONEDA X WHERE X.ID_MONEDA = CO.ID_MONEDA) AS MONEDA,
          NVL((SELECT MAX(Y.ID_PEDIDO) FROM PEDIDO_COMPRA X JOIN PEDIDO_FILE Y ON X.ID_PEDIDO = Y.ID_PEDIDO WHERE X.ID_COMPRA = CO.ID_COMPRA),NULL) AS ID_PEDIDO, decode(CO.es_ret_det,'R', 'RET', 'D', 'DET', '') as es_ret_det_nam
          -- +++++ 
        FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_NATURAL_LEGAL B, COMPRA CO LEFT JOIN COMPRA c ON (CO.ID_PARENT = c.ID_COMPRA )
      WHERE CO.ID_PROVEEDOR = b.ID_PERSONA
      AND CO.ID_ENTIDAD = E.ID_ENTIDAD
      $persona
      $voucher
      AND CO.id_anho = $id_anho
      AND CO.id_mes = $id_mes
      
      AND (CO.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
      
      AND CO.ID_COMPROBANTE NOT IN ('02')
      AND CO.Estado = '1'
      ) t
      where upper(infor_proveedor_numero) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(infor_proveedor_razon_social) like '%'||upper(replace('$text_search',' ','%'))||'%'
      --or upper(username) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(importe_total_text) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(cuo) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(fecha_emision) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(comp_pago_serie) like '%'||upper(replace('$text_search',' ','%'))||'%'
      or upper(comp_pago_nro) like '%'||upper(replace('$text_search',' ','%'))||'%'
      ORDER BY t.id_compra, t.entidad,t.id_depto, t.id_mes ,t.lote_numero ,t.correlativo 
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMyReportPurchasesTotal($id_anho, $id_mes, $id_entidad, $id_voucher, $id_persona)
    {
        $voucher = "";
        if($id_voucher){
            $voucher = "AND CO.id_voucher = $id_voucher";
        }
        $persona = "";
        if($id_persona != ""){
            $persona = "AND CO.ID_PERSONA = $id_persona";
        } else {
            $persona = "-- AND CO.ID_PERSONA = $id_persona";
        }
        $query = "
        select 
    sum(coalesce(CO.BASE_GRAVADA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS compra_gravada_bi
    ,sum(coalesce(CO.IGV_GRAVADO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS compra_gravada_igv  
    ,sum(coalesce(CO.BASE_MIXTA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS exportacion_bi
    ,sum(coalesce(CO.IGV_MIXTO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS exportacion_igv
    ,sum(coalesce(CO.BASE_NOGRAVADA * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS sincredito_bi  
    ,sum(coalesce(CO.IGV_NOGRAVADO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS sincredito_igv  
    ,sum(coalesce(CO.BASE_SINCREDITO * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS compras_no_grabadas  
          ,0 isc 
          ,sum(coalesce(CO.OTROS * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS otros_tributos -- (CASE WHEN co.id_comprobante = '01' THEN to_char(co.otros) ELSE ' ' END) otros_tributos 
          -- ,sum(co.importe) importe_total 
          ,sum(coalesce(co.importe * case when CO.ID_COMPROBANTE like '07' then -1 when CO.ID_COMPROBANTE like '87' then -1 else 1 end,0)) AS importe_total
          ,sum((CASE WHEN co.id_comprobante = '01' and CO.es_ret_det = 'R' THEN 1 ELSE 0 END)) retencion 
        FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_NATURAL_LEGAL B, COMPRA CO LEFT JOIN COMPRA c ON (CO.ID_PARENT = c.ID_COMPRA )
      WHERE CO.ID_PROVEEDOR = b.ID_PERSONA
      AND CO.ID_ENTIDAD = E.ID_ENTIDAD
      AND CO.id_anho = $id_anho
      AND CO.id_mes = $id_mes
      $persona
      $voucher
      AND (CO.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
      AND CO.ID_COMPROBANTE NOT IN ('02')
      AND CO.Estado = '1'
      ORDER BY CO.ID_COMPRA
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listReportAccountingSeat($id_anho, $id_mes, $id_empresa, $id_entidad, $id_operorigen)
    {
        $query = "
        select 
        (select numero from conta_voucher where conta_voucher.id_voucher=compra.id_voucher) voucher_descripcion 
                        , TO_CHAR(compra.fecha_provision,'DD/MM/YYYY') as fecha_provision 
                        , TO_CHAR(compra.fecha_doc,'DD/MM/YYYY') as fecha_doc 
                        , compra.serie 
                        , compra.numero 
                        , (select tipo_comprobante.nombre from tipo_comprobante where tipo_comprobante.id_comprobante=compra.id_comprobante) comprobante_nombre 
                        -- , (select pedido_registro.motivo from pedido_registro inner join pedido_compra on pedido_registro.id_pedido=pedido_compra.id_pedido and pedido_compra.id_compra=compra.id_compra and rownum<=1) motivo 
                        , pedido_registro.motivo 
                        , compra.importe 
                        , compra.id_compra 
        from 
        conta_empresa 
        inner join conta_entidad 
        on conta_empresa.id_empresa=conta_entidad.id_empresa 
        and conta_empresa.id_empresa=$id_empresa 
        -- and conta_entidad.id_entidad=17112 
        inner join compra 
        on conta_entidad.id_entidad=compra.id_entidad 
        inner join conta_asiento 
        on conta_asiento.id_origen=compra.id_compra 

        inner join pedido_compra 
        on compra.id_compra=pedido_compra.id_compra 
        inner join pedido_registro 
        on pedido_compra.id_pedido=pedido_registro.id_pedido 
        where 
        compra.id_anho=$id_anho 
        and compra.id_mes=$id_mes 
        and compra.id_entidad=$id_entidad 
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listReportProvisions($id_anho, $id_mes, $id_empresa, $id_entidad)
    {
        $query = "
        select 
        (select numero from conta_voucher where conta_voucher.id_voucher=compra.id_voucher) voucher_descripcion 
                        , TO_CHAR(compra.fecha_provision,'DD/MM/YYYY') as fecha_provision 
                        , TO_CHAR(compra.fecha_doc,'DD/MM/YYYY') as fecha_doc 
                        , compra.serie 
                        , compra.numero 
                        , (select tipo_comprobante.nombre from tipo_comprobante where tipo_comprobante.id_comprobante=compra.id_comprobante) comprobante_nombre 
                        -- , (select pedido_registro.motivo from pedido_registro inner join pedido_compra on pedido_registro.id_pedido=pedido_compra.id_pedido and pedido_compra.id_compra=compra.id_compra and rownum<=1) motivo 
                        , pedido_registro.motivo 
                        , compra.importe 
                        , compra.id_compra 
        from 
        conta_empresa 
        inner join conta_entidad 
        on conta_empresa.id_empresa=conta_entidad.id_empresa 
        and conta_empresa.id_empresa=$id_empresa 
        -- and conta_entidad.id_entidad=17112 
        inner join compra 
        on conta_entidad.id_entidad=compra.id_entidad 
        inner join pedido_compra 
        on compra.id_compra=pedido_compra.id_compra 
        inner join pedido_registro 
        on pedido_compra.id_pedido=pedido_registro.id_pedido 
        where 
        compra.id_anho=$id_anho 
        and compra.id_mes=$id_mes 
        and compra.id_entidad=$id_entidad 
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function validateDateFuture($fecha)
    {
        $query = "
        select 
            case when to_date(?)<to_date(sysdate) then 'ME' 
            when to_date(?)>to_date(sysdate) then 'MA' 
            when to_date(?)=to_date(sysdate) then 'IG' 
            else 'OT' 
            end value 
        from dual 
        ";
        $oQuery = DB::select($query, [$fecha,$fecha,$fecha]);
        return $oQuery;
    }
    /* EXISTEN */
    public static function existTypeRequest($id_tipopedido)
    {
        $query = DB::table('tipo_pedido')
            ->where('id_tipopedido','=',$id_tipopedido)
            ->exists();
        return $query;
    }
    public static function existDepto($id_depto)
    {
        $query = DB::table('conta_entidad_depto')
            ->where('id_depto','=',$id_depto)
            ->exists();
        return $query;
    }
    public static function dataRequest_by_Send($id_pedido,$id_entidad)
    {
        $query = DB::table('pedido_registro')
            ->select(
                DB::raw("
                    id_pedido 
                    -- , id_entidad 
                    -- , id_depto 
                    -- , id_tipopedido 
                    -- , id_gasto 
                    -- , id_deptoorigen 
                    -- , id_deptodestino 
                    -- , id_evento 
                    -- , id_voucher 
                    , ' ' evento_descripcion 
                    -- , numero 
                    -- , acuerdo 
                    -- , TO_CHAR(fecha,'YYYY/MM/DD') fecha 
                    , TO_CHAR(fecha_pedido,'YYYY/MM/DD') fecha_pedido 
                    , motivo 
                    , (select nombre 
                        from conta_entidad 
                        where conta_entidad.id_entidad=pedido_registro.id_entidad 
                          -- and conta_entidad_depto.id_depto=pedido_registro.id_deptoorigen 
                          and conta_entidad.es_activo='1'
                      ) AS entidad_nombre 
                    , (select nombre 
                        from conta_entidad_depto 
                        where conta_entidad_depto.id_entidad=pedido_registro.id_entidad 
                          and conta_entidad_depto.id_depto=pedido_registro.id_deptoorigen 
                          and conta_entidad_depto.es_activo='1'
                      ) AS departamento_nombre 
                    /* , (
                        select direccion 
                        from 
                          ( 
                            select 
                              -- max(id_virtual) id_virtual 
                              id_virtual 
                              ,id_persona 
                              ,direccion 
                              ,MAX(id_virtual) OVER (PARTITION BY id_persona) AS last_id_virtual 
                            from moises.persona_virtual where moises.persona_virtual.id_persona=pedido_registro.id_persona 
                          ) tab_persona_virtual
                        where tab_persona_virtual.id_virtual=tab_persona_virtual.last_id_virtual 
                      ) correo_virtual */
                    , (
                        select direccion 
                        from 
                          ( 
                            select 
                              -- max(id_virtual) id_virtual 
                              id_virtual 
                              ,id_persona 
                              ,direccion 
                              ,MAX(id_virtual) OVER (PARTITION BY id_persona) AS last_id_virtual 
                            from moises.persona_virtual 
                            -- where moises.persona_virtual.id_persona=pedido_registro.id_persona 
                          ) tab_persona_virtual
                        where tab_persona_virtual.id_virtual=tab_persona_virtual.last_id_virtual 
                          and tab_persona_virtual.id_persona=pedido_registro.id_persona 
                      ) correo_virtual 
                    -- , estado 
                    , (
                        select nombre || ' ' || paterno || ' ' || materno  
                        from moises.persona 
                        where moises.persona.id_persona=pedido_registro.id_persona 
                      ) nombre_completo 
                    "
                )
            )
            ->where('id_pedido', $id_pedido)
            ->where('id_entidad', $id_entidad)
            ->first();
        return $query;
    }
    /* PUEDO USAR OTROS MODELOS .???? */
    public static function purchasesBalances($id_entidad,$id_depto,$id_anho,$id_proveedor){                
        $query = "SELECT
                ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_COMPRA,ID_PROVEEDOR, SERIE,NUMERO,
                IMPORTE,IMPORTE_ME, 
                (select decode(ES_RET_DET, 'R', 'RET', 'D', 'DET','') from compra where VW_PURCHASES_SALDO.ID_COMPRA = compra.ID_COMPRA) ES_RET_DET_NOM
                FROM VW_PURCHASES_SALDO
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '".$id_depto."'
                AND ID_ANHO = $id_anho
                AND ID_PROVEEDOR = $id_proveedor 
                AND (IMPORTE+NVL(IMPORTE_ME,0)) <> 0 ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function purchasesValesRelacionados($id_entidad,$id_depto,$id_anho,$id_vale){      
        // dd($id_entidad,$id_depto,$id_anho,$id_vale);          
        $query = "SELECT
        ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_COMPRA,ID_PROVEEDOR, PKG_PURCHASES.FC_PROVEEDOR(ID_PROVEEDOR) AS PROVEEDOR,PKG_PURCHASES.FC_RUC(ID_PROVEEDOR) AS RUC,
        SERIE,NUMERO,
        IMPORTE,IMPORTE_ME, 
        (select decode(ES_RET_DET, 'R', 'RET', 'D', 'DET','') from compra where VW_PURCHASES_SALDO.ID_COMPRA = compra.ID_COMPRA) ES_RET_DET_NOM       
        FROM VW_PURCHASES_SALDO
        WHERE ID_ENTIDAD = $id_entidad
        AND ID_DEPTO = '".$id_depto."'
        AND ID_ANHO = $id_anho
        AND (IMPORTE+NVL(IMPORTE_ME,0)) <> 0
        AND ID_COMPRA IN (
        SELECT ID_COMPRA FROM PEDIDO_COMPRA WHERE ID_VALE = $id_vale)";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function purchasesBalancesOfRetencions($id_entidad,$id_depto,$id_anho,$id_proveedor){
        $query = "SELECT
                A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_COMPRA,A.ID_PROVEEDOR,A.SERIE,A.NUMERO,A.IMPORTE,A.IMPORTE_ME,
                FC_DOCUMENTO_CLIENTE(A.ID_PROVEEDOR) AS RUC, 
                FC_NOMBRE_PERSONA(A.ID_PROVEEDOR) AS NOMBRE,
                A.ES_RET_DET
                FROM COMPRA A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '$id_depto'
                AND A.ID_ANHO = $id_anho
                AND A.ID_PROVEEDOR = $id_proveedor 
                AND A.ES_RET_DET = 'R' 
                AND (A.IMPORTE+NVL(A.IMPORTE_ME,0)) <> 0 
                AND NOT EXISTS (SELECT 1 FROM CAJA_RETENCION_COMPRA CRC
                                            WHERE CRC.ID_COMPRA=A.ID_COMPRA)
                AND A.ID_COMPROBANTE='01'
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function purchasesBalancesByIdVoucher($id_entidad,$id_depto,$id_anho, $id_voucher){
        // PS.ID_ENTIDAD = $id_entidad
        //         AND PS.ID_DEPTO = '".$id_depto."'
        //         AND PS.ID_ANHO = $id_anho
        //         AND 
        $query = "SELECT
                PS.ID_ENTIDAD, PS.ID_DEPTO, PS.ID_ANHO, PS.ID_COMPRA, PS.ID_PROVEEDOR, FC_NOMBRE_PERSONA(PS.ID_PROVEEDOR) AS NOMBRE,
                 FC_DOCUMENTO_CLIENTE(PS.ID_PROVEEDOR) AS RUC, 
                PS.SERIE, PS.NUMERO, PS.IMPORTE, PS.IMPORTE_DOC, PS.IMPORTE_ME,
                C.ID_VOUCHER, TO_CHAR(C.FECHA_PROVISION,'DD/MM/YYYY') FECHA_PROVISION, C.CORRELATIVO
                FROM VW_PURCHASES_SALDO PS INNER JOIN COMPRA C on PS.ID_COMPRA = C.ID_COMPRA
                WHERE (PS.IMPORTE+NVL(PS.IMPORTE_ME,0)) <> 0
                AND C.ID_VOUCHER = $id_voucher";
                // AND PS.ID_COMPRA NOT IN (SELECT B.ID_COMPRA FROM CAJA_PAGO A, CAJA_PAGO_COMPRA B
                // WHERE A.ID_PAGO = B.ID_PAGO
                // AND A.ID_ENTIDAD = $id_entidad 
                // AND A.ID_DEPTO = '".$id_depto."'
                // AND A.ESTADO = '0'  )
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function purchasesBalancesOthersVouchersByIdVoucher($id_entidad,$id_depto,$id_anho, $id_user, $id_voucher){
        $query = "SELECT 
                TO_CHAR(NVL(CV.NUMERO,'')) || '-' || NVL(TO_CHAR(CV.FECHA,'DD/MM/YYYY'), '') AS VOUCHER_NAME,
                PS.ID_ENTIDAD, PS.ID_DEPTO, PS.ID_ANHO, PS.ID_COMPRA, PS.ID_PROVEEDOR, FC_NOMBRE_PERSONA(PS.ID_PROVEEDOR) AS NOMBRE,
                 FC_DOCUMENTO_CLIENTE(PS.ID_PROVEEDOR) AS RUC, 
                PS.SERIE, PS.NUMERO, PS.IMPORTE, PS.IMPORTE_DOC, PS.IMPORTE_ME,
                C.ID_VOUCHER, TO_CHAR(C.FECHA_PROVISION,'DD/MM/YYYY') FECHA_PROVISION, C.CORRELATIVO
                FROM VW_PURCHASES_SALDO PS INNER JOIN COMPRA C on PS.ID_COMPRA = C.ID_COMPRA
                INNER JOIN CONTA_VOUCHER CV ON  C.ID_VOUCHER=CV.ID_VOUCHER
                WHERE PS.ID_ENTIDAD = $id_entidad
                AND PS.ID_DEPTO = '".$id_depto."'
                AND PS.ID_ANHO = $id_anho
                AND (PS.IMPORTE+NVL(PS.IMPORTE_ME,0)) <> 0
                AND C.ID_VOUCHER <> $id_voucher
                AND CV.ID_PERSONA = $id_user
                
                AND PS.ID_COMPRA NOT IN (SELECT B.ID_COMPRA FROM CAJA_PAGO A, CAJA_PAGO_COMPRA B
                WHERE A.ID_PAGO = B.ID_PAGO
                AND A.ID_ENTIDAD = $id_entidad 
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ESTADO = '0'  )
                ORDER BY C.ID_VOUCHER 
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function purchasesBalancesByIdVoucherAnIdPago($id_entidad,$id_depto,$id_anho, $id_voucher, $id_pago){
        $query = "SELECT
                PS.ID_ENTIDAD, PS.ID_DEPTO, PS.ID_ANHO, PS.ID_COMPRA, PS.ID_PROVEEDOR, FC_NOMBRE_PERSONA(PS.ID_PROVEEDOR) AS NOMBRE,
                 FC_DOCUMENTO_CLIENTE(PS.ID_PROVEEDOR) AS RUC, 
                PS.SERIE, PS.NUMERO, PS.IMPORTE, PS.IMPORTE_DOC, PS.IMPORTE_ME,
                C.ID_VOUCHER, TO_CHAR(C.FECHA_PROVISION,'DD/MM/YYYY') FECHA_PROVISION, C.CORRELATIVO
                FROM VW_PURCHASES_SALDO PS INNER JOIN COMPRA C on PS.ID_COMPRA = C.ID_COMPRA
                WHERE PS.ID_ENTIDAD = $id_entidad
                AND PS.ID_DEPTO = '".$id_depto."'
                AND PS.ID_ANHO = $id_anho
                AND (PS.IMPORTE+NVL(PS.IMPORTE_ME,0)) <> 0
                --AND C.ID_VOUCHER = $id_voucher
                
                AND PS.ID_COMPRA IN (SELECT BX.ID_COMPRA FROM CAJA_PAGO_COMPRA BX WHERE BX.ID_PAGO = $id_pago)
               
                AND PS.ID_COMPRA NOT IN (SELECT B.ID_COMPRA FROM CAJA_PAGO A, CAJA_PAGO_COMPRA B
                                        WHERE A.ID_PAGO = B.ID_PAGO
                                        AND A.ID_ENTIDAD = $id_entidad 
                                        AND A.ID_DEPTO = '".$id_depto."'
                                        --       AND A.ID_PAGO = $id_pago
                                        AND A.ESTADO = '0'
                                        )
                                        
                                        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    // public static function listPurchasesSeatsAcounting($id_compra,$id_anho,$id_empresa){
    //     $query = "SELECT ID_CASIENTO, ID_COMPRA, ID_CUENTAAASI, ID_RESTRICCION,
    //                 (SELECT X.ID_COMPROBANTE FROM COMPRA X WHERE X.ID_COMPRA = A.ID_COMPRA) AS ID_COMPROBANTE, 
    //                 ID_CTACTE, ID_FONDO, ID_DEPTO, IMPORTE, IMPORTE_ME, DESCRIPCION, EDITABLE, ID_PARENT, ID_TIPOREGISTRO, DC, NRO_ASIENTO,
    //                 (SELECT X.ID_CUENTAEMPRESARIAL FROM CONTA_EMPRESA_CTA X WHERE X.ID_CUENTAAASI = A.ID_CUENTAAASI AND X.ID_RESTRICCION = A.ID_RESTRICCION 
    //                 AND X.ID_TIPOPLAN = 1 AND X.ID_ANHO = ".$id_anho." AND X.ID_EMPRESA = ".$id_empresa.") AS CTA_EMPRESARIAL
    //             FROM COMPRA_ASIENTO A
    //             WHERE ID_COMPRA = $id_compra
    //             ORDER BY ID_CASIENTO ASC ";
    //     $oQuery = DB::select($query);
    //     return $oQuery;
    // }
    // public static function listPurchasesSeatsAcountingTotal($id_compra) {
    //     $query = "
    //             SELECT 
    //             SUM(DECODE(DC,'C',IMPORTE,0)) AS CREDITO,
    //             SUM(DECODE(DC,'D',IMPORTE,0)) AS DEBITO
    //             FROM COMPRA_ASIENTO 
    //             WHERE ID_COMPRA = $id_compra";
    //     $oQuery = DB::select($query);
    //     return $oQuery;
    // }

    public static function getListCompraAsientoByIdCompra($id_compra,$id_anho,$id_empresa)
    {
        $query = "SELECT ID_CASIENTO, ID_COMPRA, ID_CUENTAAASI, ID_RESTRICCION,
            (SELECT X.ID_COMPROBANTE FROM COMPRA X WHERE X.ID_COMPRA = A.ID_COMPRA) AS ID_COMPROBANTE, 
            ID_CTACTE, ID_FONDO, ID_DEPTO, IMPORTE, IMPORTE_ME, DESCRIPCION, EDITABLE, ID_PARENT, ID_TIPOREGISTRO, DC, NRO_ASIENTO,
            (SELECT X.ID_CUENTAEMPRESARIAL FROM CONTA_EMPRESA_CTA X WHERE X.ID_CUENTAAASI = A.ID_CUENTAAASI AND X.ID_RESTRICCION = A.ID_RESTRICCION 
                    AND X.ID_TIPOPLAN = 1 AND X.ID_ANHO = ".$id_anho." AND X.ID_EMPRESA = ".$id_empresa.") AS CTA_EMPRESARIAL
            FROM COMPRA_ASIENTO A
        WHERE ID_COMPRA = $id_compra
        ORDER BY FECHA_ACTUALIZACION DESC
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getListAsientosByIdDinamica($id_dinamica)
    {
        $query = "SELECT 
        ID_ASIENTO,
        UNICO_CTACTE
        FROM CONTA_DINAMICA_ASIENTO WHERE ID_DINAMICA=$id_dinamica
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getCompraById($id_compra) {
        $query = "
                SELECT ID_COMPRA, ID_ENTIDAD, ID_PROVEEDOR, ESTADO, IMPORTE FROM COMPRA
                where id_compra=".$id_compra;
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getCompraChildsById($id_compra) {
        $query = "SELECT ID_COMPRA, ID_ENTIDAD, ID_PROVEEDOR, ESTADO, IMPORTE, NUMERO, SERIE FROM COMPRA
                where ID_PARENT=".$id_compra;
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function validarContrapartidaAsientosCompra($id_compra) {
        $query = "
                SELECT NVL(SUM(IMPORTE),0) TOTALIZAR_IMPORTE, COUNT(*) CANTIDAD_ASIENTOS
                FROM COMPRA_ASIENTO 
                WHERE ID_COMPRA = $id_compra";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function patchCompra($data,$id_compra)
    {
        DB::table('COMPRA')
            ->where('ID_COMPRA', $id_compra)
            ->update($data);
    }

    public static function getCompraAsientoById($id_accounting_seat, $id_entidad)
    {
        $query = "
            SELECT ID_CASIENTO, ID_COMPRA, ID_CUENTAAASI, FC_CUENTA(ID_CUENTAAASI) CUENTAAASI_NOMBRE, ID_RESTRICCION,
            ID_CTACTE,FC_NAMECUENTA(ID_CUENTAAASI,ID_CTACTE, '".$id_entidad."') CTACTE_NOMBRE,
            ID_FONDO, PKG_PURCHASES.FC_NAMEFONDO(ID_FONDO) FONDO_NOMBRE, ID_DEPTO, FC_NAMESDEPTO('".$id_entidad."',ID_DEPTO) DEPTO_NOMBRE,
            IMPORTE, DESCRIPCION, DC
            FROM COMPRA_ASIENTO WHERE ID_CASIENTO = ".$id_accounting_seat;
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getCompras($id_entidad, $id_depto, $id_persona)
    {
        $query = "
            SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, C.ID_VOUCHER, C.ID_PROVEEDOR, PJ.ID_RUC,C.SERIE, C.NUMERO, C.FECHA_DOC, C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO
            FROM COMPRA C
            INNER JOIN MOISES.PERSONA_JURIDICA PJ 
            ON C.ID_PROVEEDOR = PJ.ID_PERSONA
            WHERE C.ID_ENTIDAD = ".$id_entidad." AND C.ID_DEPTO=".$id_depto." AND C.ID_PERSONA=".$id_persona."  AND C.ID_COMPROBANTE <> '02'
            ORDER BY CORRELATIVO
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getComprasByIdProveedor($id_entidad, $id_depto, $id_persona, $id_proveedor)
    {

        $query = "
            SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, C.ID_VOUCHER, C.ID_PROVEEDOR, 
            FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS ID_RUC,
            C.SERIE, C.NUMERO, C.FECHA_DOC, C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO
            FROM COMPRA C
            WHERE C.ID_ENTIDAD = $id_entidad
            AND C.ID_DEPTO=$id_depto
            AND C.ID_COMPROBANTE <> '02'
            AND C.ID_PROVEEDOR = $id_proveedor
            ORDER BY CORRELATIVO
            ";
            // AND C.ID_PERSONA=$id_persona 
        $oQuery = DB::select($query);
        return $oQuery;
    }

  

    public static function getComprasInventarioByIdProveedor($id_entidad, $id_depto, $id_persona, $id_proveedor)
    {

        $query = "
            SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, C.ID_VOUCHER, C.ID_PROVEEDOR, 
            FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS ID_RUC,
            C.SERIE, C.NUMERO, C.FECHA_DOC, C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO
            FROM COMPRA C
            WHERE C.ID_ENTIDAD = $id_entidad
            AND C.ID_DEPTO=$id_depto
            AND C.ID_COMPROBANTE NOT IN ('02','07','08','87','88') 
            AND C.ID_PROVEEDOR = $id_proveedor
            AND C.TIENE_KARDEX = 'S'
            ORDER BY CORRELATIVO
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getComprasByIdProveedorForNotes($id_entidad, $id_depto,$id_anho, $id_proveedor)
    {

        $query = "SELECT ID_COMPRA, SERIE, NUMERO, FECHA_PROVISION, IMPORTE, IMPORTE_ME, ID_COMPROBANTE FROM VW_PURCHASES_MOV
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '$id_depto'
                --AND ID_ANHO = $id_anho
                --AND TO_CHAR(FECHA_PROVISION, 'MM/YYYY') > TO_CHAR(ADD_MONTHS(SYSDATE,-12), 'MM/YYYY')
                AND FECHA_PROVISION > ADD_MONTHS(SYSDATE,-12)
                AND ID_PROVEEDOR = $id_proveedor
                AND SIGN(IMPORTE) = 1
                AND ID_COMPROBANTE NOT IN ('07','08','87')
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getComprasByIdProveedorForNotesUPEU($id_entidad, $id_depto,$id_anho, $id_proveedor)
    {

        $query = "SELECT ID_COMPRA, SERIE, NUMERO, FECHA_PROVISION, IMPORTE, IMPORTE_ME, ID_COMPROBANTE FROM VW_PURCHASES_MOV
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '$id_depto'
                AND ID_ANHO = $id_anho
                --AND TO_CHAR(FECHA_PROVISION, 'MM/YYYY') > TO_CHAR(ADD_MONTHS(SYSDATE,-12), 'MM/YYYY')
                AND ID_PROVEEDOR = $id_proveedor
                AND SIGN(IMPORTE) = 1
                AND ID_COMPROBANTE NOT IN ('07','08','87')
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getComprasByIdVoucher($id_entidad, $id_depto, $id_persona, $id_voucher)
    {
        $query = " SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, 
            C.ID_VOUCHER, CV.NUMERO AS NUMERO_VOUCHER,
            C.ID_PROVEEDOR, 
            C.SERIE, C.NUMERO,
            coalesce(to_char(C.FECHA_DOC,'DD/MM/YYYY'),' ') AS FECHA_DOC,
            C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO, (SELECT NVL(COUNT(*),0) CANTIDAD_PAGOS FROM CAJA_PAGO_COMPRA CPC WHERE CPC.ID_COMPRA=C.ID_COMPRA) AS CANTIDAD_PAGOS,
            FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS RUC_PROVEEDOR,
            TC.NOMBRE_CORTO AS NOMBRE_COMPROBANTE
            FROM COMPRA C INNER JOIN CONTA_VOUCHER CV ON C.ID_VOUCHER = CV.ID_VOUCHER
            INNER JOIN TIPO_COMPROBANTE TC ON C.ID_COMPROBANTE = TC.ID_COMPROBANTE
            WHERE C.ID_ENTIDAD = $id_entidad
            AND C.ID_DEPTO=$id_depto
            AND C.ID_COMPROBANTE <> '02'
            AND C.ID_VOUCHER = $id_voucher
            AND (C.TIENE_KARDEX = 'N' OR C.TIENE_KARDEX IS NULL)
            ORDER BY CORRELATIVO
            ";
            // AND C.ID_PERSONA=$id_persona
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getCompraInventariosByIdVoucher($id_entidad, $id_depto, $id_persona, $id_voucher)
    {
        $query = " SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, 
            C.ID_VOUCHER, CV.NUMERO AS NUMERO_VOUCHER,
            C.ID_PROVEEDOR, 
            C.SERIE, C.NUMERO,
            coalesce(to_char(C.FECHA_DOC,'DD/MM/YYYY'),' ') AS FECHA_DOC,
            C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO, (SELECT NVL(COUNT(*),0) CANTIDAD_PAGOS FROM CAJA_PAGO_COMPRA CPC WHERE CPC.ID_COMPRA=C.ID_COMPRA) AS CANTIDAD_PAGOS,
            FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS RUC_PROVEEDOR,
            TC.NOMBRE_CORTO AS NOMBRE_COMPROBANTE
            FROM COMPRA C INNER JOIN CONTA_VOUCHER CV ON C.ID_VOUCHER = CV.ID_VOUCHER
            INNER JOIN TIPO_COMPROBANTE TC ON C.ID_COMPROBANTE = TC.ID_COMPROBANTE
            WHERE C.ID_ENTIDAD = $id_entidad
            AND C.ID_DEPTO=$id_depto
            AND C.ID_COMPROBANTE NOT IN ('02','07','08','87','88') 
            AND C.ID_VOUCHER = $id_voucher
            AND C.TIENE_KARDEX = 'S'
            ORDER BY CORRELATIVO
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getCompraInventariosNCByIdVoucher($id_entidad, $id_depto, $id_persona, $id_voucher)
    {
        $query = " SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, 
            C.ID_VOUCHER, CV.NUMERO AS NUMERO_VOUCHER,
            C.ID_PROVEEDOR, 
            C.SERIE, C.NUMERO,
            coalesce(to_char(C.FECHA_DOC,'DD/MM/YYYY'),' ') AS FECHA_DOC,
            C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO, (SELECT NVL(COUNT(*),0) CANTIDAD_PAGOS FROM CAJA_PAGO_COMPRA CPC WHERE CPC.ID_COMPRA=C.ID_COMPRA) AS CANTIDAD_PAGOS,
            FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS RUC_PROVEEDOR,
            TC.NOMBRE_CORTO AS NOMBRE_COMPROBANTE
            FROM COMPRA C INNER JOIN CONTA_VOUCHER CV ON C.ID_VOUCHER = CV.ID_VOUCHER
            INNER JOIN TIPO_COMPROBANTE TC ON C.ID_COMPROBANTE = TC.ID_COMPROBANTE
            WHERE C.ID_ENTIDAD = $id_entidad
            AND C.ID_DEPTO=$id_depto
            AND C.ID_COMPROBANTE IN ('02','07','08','87','88') 
            AND C.ID_VOUCHER = $id_voucher
            AND C.TIENE_KARDEX = 'S'
            ORDER BY CORRELATIVO
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

      // public static function getComprasInventarioByIdProveedor($id_entidad, $id_depto, $id_persona, $id_proveedor)
    // {

    //     $query = "
    //         SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, C.ID_VOUCHER, C.ID_PROVEEDOR, 
    //         FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS ID_RUC,
    //         C.SERIE, C.NUMERO, C.FECHA_DOC, C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
    //         C.ID_COMPROBANTE, C.CORRELATIVO
    //         FROM COMPRA C
    //         WHERE C.ID_ENTIDAD = $id_entidad
    //         AND C.ID_DEPTO=$id_depto
    //         AND C.ID_COMPROBANTE NOT IN ('02','07','08','87','88') 
    //         AND C.ID_PROVEEDOR = $id_proveedor
    //         AND C.TIENE_KARDEX = 'S'
    //         ORDER BY CORRELATIVO
    //         ";
    //     $oQuery = DB::select($query);
    //     return $oQuery;
    // }

    public static function getComprasAndReceiptForFeesByIdVoucher($id_voucher)
    {
        $query = "
            SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, 
            C.ID_VOUCHER, CV.NUMERO AS NUMERO_VOUCHER,
            C.ID_PROVEEDOR, 
            C.SERIE, C.NUMERO,
            coalesce(to_char(C.FECHA_DOC,'DD/MM/YYYY'),' ') AS FECHA_DOC,
            C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO, (SELECT NVL(COUNT(*),0) CANTIDAD_PAGOS FROM CAJA_PAGO_COMPRA CPC WHERE CPC.ID_COMPRA=C.ID_COMPRA) AS CANTIDAD_PAGOS,
            FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS RUC_PROVEEDOR,
            TC.NOMBRE_CORTO AS NOMBRE_COMPROBANTE
            FROM COMPRA C INNER JOIN CONTA_VOUCHER CV ON C.ID_VOUCHER = CV.ID_VOUCHER
            INNER JOIN TIPO_COMPROBANTE TC ON C.ID_COMPROBANTE = TC.ID_COMPROBANTE
            WHERE C.ID_VOUCHER = $id_voucher
            ORDER BY CORRELATIVO
            ";
        // AND C.ID_COMPROBANTE <> '02'
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getComprasAndReceiptForFeesByIdVoucherTotal($id_voucher)
    {
        $query = "
            SELECT 
            sum(C.IMPORTE) as importe, 
            sum(C.IMPORTE_ME) as importe_me
            FROM COMPRA C INNER JOIN CONTA_VOUCHER CV ON C.ID_VOUCHER = CV.ID_VOUCHER
            INNER JOIN TIPO_COMPROBANTE TC ON C.ID_COMPROBANTE = TC.ID_COMPROBANTE
            WHERE C.ID_VOUCHER = $id_voucher
            ORDER BY CORRELATIVO
            ";
        // AND C.ID_COMPROBANTE <> '02'
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getRecibosHonorarios($id_entidad, $id_depto, $id_persona)
    {
        $query = "
            SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, C.ID_VOUCHER, C.ID_PROVEEDOR, PJ.ID_RUC,C.SERIE, C.NUMERO, C.FECHA_DOC, C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO
            FROM COMPRA C
            INNER JOIN MOISES.PERSONA_JURIDICA PJ 
            ON C.ID_PROVEEDOR = PJ.ID_PERSONA
            WHERE C.ID_ENTIDAD = ".$id_entidad."
            AND C.ID_DEPTO=".$id_depto."
            AND C.ID_COMPROBANTE = '02'
            ORDER BY C.CORRELATIVO";
            $oQuery = DB::select($query);
            // AND C.ID_PERSONA=".$id_persona." 
        return $oQuery;
    }

    public static function getReciboHonorarioById($id_receipt_for_fees)
    {
        $query = "SELECT C.ID_COMPRA, C.ID_PROVEEDOR AS PROVEEDOR_ID, FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) AS PROVEEDOR_RAZONSOCIAL,
                  FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS PROVEEDOR_RUC, C.ID_COMPROBANTE, c.ES_ELECTRONICA,
                  C.SERIE, C.NUMERO, TO_CHAR(C.FECHA_DOC, 'DD/MM/YYYY') AS FECHA_DOC, coalesce(C.ID_MONEDA,0) ID_MONEDA,
                  coalesce(C.TIPOCAMBIO,0) TIPOCAMBIO,
                  (CASE WHEN C.ID_MONEDA = '9' THEN C.IMPORTE_ME
                  ELSE C.IMPORTE END) AS IMPORTE,
                  coalesce(C.TIENE_SUSPENCION, 'N') TIENE_SUSPENCION
            FROM COMPRA C
            WHERE C.ID_COMPRA = $id_receipt_for_fees";
        $oQuery = DB::select($query);
        return $oQuery;
    }


    public static function getRecibosHonorariosByIdProveedorAndFechaDoc($id_entidad, $id_depto, $id_persona, $id_proveedor, $fecha_doc)
    {
        $query = "
              SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO, C.ID_VOUCHER, C.ID_PROVEEDOR, PJ.ID_RUC,C.SERIE, C.NUMERO, TO_CHAR(C.FECHA_DOC,'DD/MM/YYYY') FECHA_DOC, C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO, C.IMPORTE_RENTA,
            (select ID_RUC FROM CONTA_EMPRESA WHERE ID_EMPRESA = (SELECT ID_EMPRESA FROM CONTA_ENTIDAD WHERE ID_ENTIDAD = $id_entidad)) ID_RUC_EMPRESA
            FROM COMPRA C INNER JOIN MOISES.PERSONA_JURIDICA PJ 
            ON C.ID_PROVEEDOR = PJ.ID_PERSONA
            WHERE C.ID_ENTIDAD IN (SELECT ID_ENTIDAD FROM CONTA_ENTIDAD WHERE ID_EMPRESA = (SELECT ID_EMPRESA FROM CONTA_ENTIDAD WHERE ID_ENTIDAD = $id_entidad))
            AND C.ID_PROVEEDOR=$id_proveedor 
            AND C.ID_COMPROBANTE = '02'
            AND EXTRACT(MONTH FROM C.FECHA_DOC ) = EXTRACT(MONTH FROM (TO_DATE('$fecha_doc', 'YYYY-MM-DD')))
            ORDER BY C.CORRELATIVO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getRecibosHonorariosByIdVoucher($id_entidad, $id_depto, $id_persona, $id_voucher)
    {
        /* $oWhere = "";
        if ($id_voucher !== '0') {
            $oWhere = "AND C.ID_VOUCHER=$id_voucher ";
        } */
        $query = "
            SELECT C.ID_COMPRA, C.ID_ENTIDAD, C.ID_DEPTO,
            C.ID_VOUCHER,
            CV.NUMERO AS NUMERO_VOUCHER,
            C.ID_PROVEEDOR, 
            C.SERIE, C.NUMERO, coalesce(TO_CHAR(C.FECHA_DOC,'DD/MM/YYYY'),' ') FECHA_DOC, C.IMPORTE, C.IMPORTE_ME, C.ESTADO, 
            C.ID_COMPROBANTE, C.CORRELATIVO, C.IMPORTE_RENTA, (SELECT NVL(COUNT(*),0) CANTIDAD_PAGOS FROM CAJA_PAGO_COMPRA CPC WHERE CPC.ID_COMPRA=C.ID_COMPRA) AS CANTIDAD_PAGOS,
            FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) as NOMBRE_PROVEEDOR, FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS RUC_PROVEEDOR,
            TC.NOMBRE_CORTO AS NOMBRE_COMPROBANTE
            FROM COMPRA C INNER JOIN CONTA_VOUCHER CV ON C.ID_VOUCHER = CV.ID_VOUCHER
            INNER JOIN TIPO_COMPROBANTE TC ON C.ID_COMPROBANTE = TC.ID_COMPROBANTE
            WHERE C.ID_ENTIDAD = $id_entidad
            AND C.ID_DEPTO=$id_depto
            AND C.ID_VOUCHER=$id_voucher
            AND C.ID_COMPROBANTE = '02'
            --AND C.TIENE_SUSPENCION = 'N'
            --AND C.IMPORTE_RENTA = 0
            ORDER BY C.CORRELATIVO";
            $oQuery = DB::select($query);
            // AND C.ID_PERSONA=$id_persona 
        return $oQuery;
    }

    public static function deleteContaAsientoByIdCompra($id_compra)
    {
        DB::table('CONTA_ASIENTO')
            ->where('ID_ORIGEN', '=', $id_compra)
            ->where('ID_TIPOORIGEN', '=', '3')
            ->delete();
    }

    public static function deleteContaAsientoRetencionRHByIdCompra($id_compra)
    {
        DB::table('CONTA_ASIENTO')
            ->where('ID_ORIGEN', '=', $id_compra)
            ->where('ID_TIPOORIGEN', '=', '14')
            ->delete();
    }

    public static function deleteContaAsientoCreditoByIdCompra($id_compra)
    {
        DB::table('CONTA_ASIENTO')
            ->where('ID_ORIGEN', '=', $id_compra)
            ->where('ID_TIPOORIGEN', '=', '3')
            ->where('IMPORTE', '<', '0')
            ->delete();
    }

    public static function destroyDetraccionsByIdCompra($id_compra)
    {
        DB::table('CAJA_DETRACCION_COMPRA')->where('ID_COMPRA', '=', $id_compra)->delete();
    }

    public static function destroyDetraccionCabeceraByIdDetraccion($id_detraccion)
    {
        DB::table('CAJA_DETRACCION')->where('ID_DETRACCION', '=', $id_detraccion)->delete();
    }

    public static function destroyContaAsiento($id_tipoorigen, $id_origen)
    {
        DB::table('CONTA_ASIENTO')
            ->where('ID_TIPOORIGEN', '=', $id_tipoorigen)
            ->where('ID_ORIGEN', '=', $id_origen)->delete();
    }

    public static function destroyRetencionCompraByIdCompra($id_compra, $id_retencion)
    {
        DB::table('CAJA_RETENCION_COMPRA')->where('ID_COMPRA', '=', $id_compra)->where('ID_RETENCION', '=', $id_retencion)->delete();
    }

    public static function destroyRetencionCabeceraByIdRetencion($id_retencion)
    {
        DB::table('CAJA_RETENCION')->where('ID_RETENCION', '=', $id_retencion)->delete();
    }

    public static function getRetencionCompra($id_retencion)
    {
        $result = DB::table('CAJA_RETENCION_COMPRA')
            ->where('ID_RETENCION', '=', $id_retencion)->count();
        return $result;
    }

    public static function getFileName81($id_empresa, $id_anho, $id_mes) {

          $query = "
            SELECT 'LE'||ID_RUC||trim(to_char($id_anho,'9999'))||trim(to_char($id_mes,'00'))||'00080100001'||
            CASE WHEN (SELECT count(*)              
            FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_JURIDICA B, COMPRA A LEFT JOIN COMPRA c ON (A.ID_PARENT = c.ID_COMPRA )
                        WHERE A.ID_PROVEEDOR = b.ID_PERSONA
                        AND A.ID_ENTIDAD = E.ID_ENTIDAD
                        AND E.ID_EMPRESA = $id_empresa
                        AND A.id_mes = $id_mes
                        AND A.id_anho = $id_anho
                        AND A.ID_COMPROBANTE NOT IN ('02')
                        AND A.Estado = '1' ) >0 THEN '1' ELSE '0' end
            ||'11.txt' as filename  FROM CONTA_EMPRESA
            WHERE ID_EMPRESA = $id_empresa";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getFileName82($id_empresa, $id_anho, $id_mes) {

        $query = "
            SELECT 
             'LE'||ID_RUC||trim(to_char($id_anho,'9999'))||trim(to_char($id_mes,'00'))||'00080200001011.txt'  AS filename
             FROM CONTA_EMPRESA
            WHERE ID_EMPRESA = $id_empresa";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getComprasResumenDetalle($id_empresa, $id_anho, $id_mes) {

        $query = "
             SELECT 
                    A.ID_ENTIDAD,
					to_char(sum(coalesce(A.IMPORTE * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS importe,
					to_char(sum(coalesce(A.IMPORTE_ME * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS importe_e,    
					to_char(sum(coalesce(A.BASE_GRAVADA * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS baseimp1,  
					to_char(sum(coalesce(A.BASE_MIXTA * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS baseimp2,  
					to_char(sum(coalesce(A.BASE_NOGRAVADA * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS baseimp3,  
					to_char(sum(coalesce(A.BASE_SINCREDITO * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS baseimp4,  
					to_char(sum(coalesce(A.OTROS * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'999999909.99') AS baseimp5,  
					to_char(sum(coalesce(A.IGV_GRAVADO * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS igv1,  
					to_char(sum(coalesce(A.IGV_MIXTO * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS igv2,  
					to_char(sum(coalesce(A.IGV_NOGRAVADO * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS igv3,  
                    to_char(sum(coalesce(A.IGV * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS rec_igv_total,
					sum(CASE A.ES_RET_DET WHEN 'R' THEN 1 ELSE 0 END) AS retenc,
					sum(CASE A.ES_RET_DET WHEN 'D' THEN 1 ELSE 0 END) AS detrac
			FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_NATURAL_LEGAL B, COMPRA A LEFT JOIN COMPRA c ON (A.ID_PARENT = c.ID_COMPRA )
			WHERE A.ID_PROVEEDOR = b.ID_PERSONA
			AND A.ID_ENTIDAD = E.ID_ENTIDAD
			AND E.ID_EMPRESA = $id_empresa
			AND A.ID_ANHO = $id_anho
			AND A.id_mes = $id_mes
            AND A.ID_COMPROBANTE NOT IN ('02')
			AND A.Estado = '1'
			group BY A.id_entidad";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getComprasResumenTotales($id_empresa, $id_anho, $id_mes) {

        $query = "
            SELECT 
					to_char(sum(coalesce(A.IMPORTE * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS importe,
					to_char(sum(coalesce(A.IMPORTE_ME * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS importe_e,    
					to_char(sum(coalesce(A.BASE_GRAVADA * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS baseimp1,  
					to_char(sum(coalesce(A.BASE_MIXTA * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS baseimp2,  
					to_char(sum(coalesce(A.BASE_NOGRAVADA * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS baseimp3,  
					to_char(sum(coalesce(A.BASE_SINCREDITO * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS baseimp4,  
					to_char(sum(coalesce(A.OTROS * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'999999909.99') AS baseimp5,  
					to_char(sum(coalesce(A.IGV_GRAVADO * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS igv1,  
					to_char(sum(coalesce(A.IGV_MIXTO * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS igv2,  
					to_char(sum(coalesce(A.IGV_NOGRAVADO * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS igv3,  
                    to_char(sum(coalesce(A.IGV * case when A.ID_COMPROBANTE like '07' then -1 when A.ID_COMPROBANTE like '87' then -1 else 1 end,0)),'99999990.99') AS rec_igv_total,
					sum(CASE A.ES_RET_DET WHEN 'R' THEN 1 ELSE 0 END) AS retenc,
					sum(CASE A.ES_RET_DET WHEN 'D' THEN 1 ELSE 0 END) AS detrac
			FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_NATURAL_LEGAL B, COMPRA A LEFT JOIN COMPRA c ON (A.ID_PARENT = c.ID_COMPRA )
			WHERE A.ID_PROVEEDOR = b.ID_PERSONA
			AND A.ID_ENTIDAD = E.ID_ENTIDAD
			AND E.ID_EMPRESA = $id_empresa
			AND A.ID_ANHO = $id_anho
			AND A.id_mes = $id_mes
            AND A.ID_COMPROBANTE NOT IN ('02')
			AND A.Estado = '1'";
        $oQuery = DB::select($query);
        return $oQuery;
    }

public static function getCheckOfIssue($id_empresa, $id_entidad, $id_anho, $id_mes) {

        $query = "
            SELECT 
                A.ID_COMPRA, 
                a.id_entidad, 
                a.ID_VOUCHER, 
                d.LOTE, 
                
                F.NUMERO AS VOUCHER_NUMERO,
                TO_CHAR(F.FECHA,'DD/MM/YYYY') AS VOUCHER_FECHA,

                d.NUM_AASI, 
                A.IMPORTE, 
                abs(d.COS_VALOR) AS cos_valor,
                CASE when d.LOTE IS NULL THEN 'No tiene CUO' ELSE 'Cuo OK' END AS valid_lote,
                CASE when d.NUM_AASI IS NULL THEN 'No tiene CUO Correlativo' ELSE 'Cuo Corr OK'  END AS valid_correlativo,
                CASE when A.IMPORTE = abs(d.COS_VALOR) THEN 'Importe OK' ELSE 'Diferencias en el importe'  END AS valid_importe
            FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_JURIDICA B, COMPRA A LEFT OUTER JOIN VW_CONTA_DIARIO d 
                ON (trim(to_char(A.ID_TIPOORIGEN,'9999999999999999999'))||'-'||trim(to_char(A.ID_COMPRA,'9999999999999999999')) = d.OBSERVACION
                AND A.ID_ANHO = d.ID_ANHO
                AND A.ID_MES = d.ID_MES
                AND d.ID_CUENTAAASI in ( 2130101,2130107)
                AND A.ID_ENTIDAD = D.ID_ENTIDAD
                )
                INNER JOIN CONTA_VOUCHER F ON A.ID_VOUCHER=F.ID_VOUCHER
                WHERE A.ID_PROVEEDOR = b.ID_PERSONA
                AND E.ID_ENTIDAD = A.ID_ENTIDAD
                AND E.ID_EMPRESA = $id_empresa
                AND (A.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
                AND A.ID_ANHO = $id_anho
                AND A.id_mes = $id_mes
                        -- AND A.ID_COMPROBANTE NOT IN ('02')
                AND A.Estado = '1'  
                AND (d.lote IS NULL
                    OR d.NUM_AASI IS NULL
                    OR A.IMPORTE <> abs(d.COS_VALOR) )
                UNION ALL

                SELECT 
                A.ID_COMPRA, 
                a.id_entidad, 
                a.ID_VOUCHER, 
                d.LOTE, 
                
                F.NUMERO AS VOUCHER_NUMERO,
                TO_CHAR(F.FECHA,'DD/MM/YYYY') AS VOUCHER_FECHA,

                d.NUM_AASI, 
                A.IMPORTE, 
                abs(d.COS_VALOR) AS cos_valor,
                CASE when d.LOTE IS NULL THEN 'No tiene CUO' ELSE 'Cuo OK' END AS valid_lote,
                CASE when d.NUM_AASI IS NULL THEN 'No tiene CUO Correlativo' ELSE 'Cuo Corr OK'  END AS valid_correlativo,
                CASE when A.IMPORTE = abs(d.COS_VALOR) THEN 'Importe OK' ELSE 'Diferencias en el importe'  END AS valid_importe
            FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_natural B, COMPRA A LEFT OUTER JOIN VW_CONTA_DIARIO d 
                ON (trim(to_char(A.ID_TIPOORIGEN,'9999999999999999999'))||'-'||trim(to_char(A.ID_COMPRA,'9999999999999999999')) = d.OBSERVACION
                AND A.ID_ANHO = d.ID_ANHO
                AND A.ID_MES = d.ID_MES
                AND d.ID_CUENTAAASI in ( 2130101,2130107)
                AND A.ID_ENTIDAD = D.ID_ENTIDAD
                )
                INNER JOIN CONTA_VOUCHER F ON A.ID_VOUCHER=F.ID_VOUCHER
                WHERE A.ID_PROVEEDOR = b.ID_PERSONA
                AND E.ID_ENTIDAD = A.ID_ENTIDAD
                AND E.ID_EMPRESA = $id_empresa
                AND (A.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
                AND A.ID_ANHO = $id_anho
                AND A.id_mes = $id_mes
                        -- AND A.ID_COMPROBANTE NOT IN ('02')
                AND A.Estado = '1'  
                AND (d.lote IS NULL
                    OR d.NUM_AASI IS NULL
                    OR A.IMPORTE <> abs(d.COS_VALOR) )
                    ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getFeesRecord($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes) {

        $query = "SELECT 
        coalesce(to_char(A.FECHA_DOC,'dd/mm/yyyy'),' ') AS fecha_doc,  
        coalesce(A.ID_COMPROBANTE,'') AS tipo_doc,  
        coalesce(A.SERIE,'0') AS serie,  
        coalesce(A.NUMERO,'') AS numdoc, 
        B.NUM_DOCUMENTO AS ruc,
        NVL(b.NOMBRE,'')||' '||NVL(b.PATERNO,'')||' '||NVL(b.MATERNO,'') AS nombre,  
        to_char(coalesce(A.IMPORTE,0),'99999990.99') AS importe,   
        to_char(coalesce(A.IMPORTE_RENTA,0),'99999990.99') AS renta,   
        to_char(coalesce(A.IMPORTE-A.IMPORTE_RENTA,0),'99999990.99') AS neto,  
        (SELECT vv.NUMERO FROM CONTA_VOUCHER vv WHERE vv.ID_VOUCHER = a.ID_VOUCHER) as lote_numero,
        a.correlativo,
        coalesce(ELISEO.FC_CONTA_CUO_COMPLETO(a.ID_TIPOORIGEN ,a.ID_COMPRA, a.ID_ENTIDAD, a.ID_ANHO, a.ID_MES),'') as cuo,
        (select u.email FROM USERS u WHERE u.id = a.ID_PERSONA) username, 
        A.ID_ENTIDAD as entidad,
        A.ID_ENTIDAD AS ID_ENTIDAD,
        A.ID_DEPTO AS ID_DEPTO,
        A.ID_PERSONA as per_user,
        A.ID_VOUCHER as voucher,
        (SELECT COALESCE(max(x.DESCRIPCION),'-')
        	FROM COMPRA_ASIENTO x WHERE x.ID_COMPRA=A.ID_COMPRA AND DC ='D' 
        	AND rownum=1) AS asiento_glosa
        FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_NATURAL B, COMPRA A LEFT JOIN COMPRA c ON (A.ID_PARENT = c.ID_COMPRA )
        WHERE A.ID_PROVEEDOR = b.ID_PERSONA
        AND A.ID_ENTIDAD = E.ID_ENTIDAD
        AND E.ID_EMPRESA = $id_empresa
        AND (A.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
        AND (A.ID_DEPTO = '$id_depto' OR '*' = '$id_depto' )
        AND A.ID_ANHO = $id_anho
        AND A.id_mes = $id_mes
        AND A.ID_COMPROBANTE IN ('02')
        AND A.Estado = '1'  
        AND B.ID_TIPODOCUMENTO = 6  
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    
    public static function getFeesRecordSum($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes) {

        $query = " SELECT 
                    to_char(coalesce(sum(A.IMPORTE),0),'99999990.99') AS importe,   
                    to_char(coalesce(sum(A.IMPORTE_RENTA),0),'99999990.99') AS renta,   
                    to_char(coalesce(sum(A.IMPORTE-A.IMPORTE_RENTA),0),'99999990.99') AS neto   
			FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_NATURAL B, COMPRA A LEFT JOIN COMPRA c ON (A.ID_PARENT = c.ID_COMPRA )
			WHERE A.ID_PROVEEDOR = b.ID_PERSONA
			AND A.ID_ENTIDAD = E.ID_ENTIDAD
            AND E.ID_EMPRESA = $id_empresa
            AND (A.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
            AND (A.ID_DEPTO = '$id_depto' OR '*' = '$id_depto' )
			AND A.ID_ANHO = $id_anho
			AND A.id_mes = $id_mes
            AND A.ID_COMPROBANTE IN ('02')
            AND A.Estado = '1'     
            AND B.ID_TIPODOCUMENTO = 6
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    
    public static function getWithholdingRecord($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes) {

        $query = "SELECT 
        R.ID_ENTIDAD,
        A.id_depto,
        A.id_depto as id_dpto,
        (select u.email FROM USERS u WHERE u.id = a.ID_PERSONA) username,
        ( select y.NOMBRE||'-'||vv.numero from CONTA_VOUCHER vv, tipo_voucher y
        where vv.id_tipovoucher = y.id_tipovoucher
        and vv.id_voucher = r.ID_VOUCHER ) as lote_numero,
        coalesce(ELISEO.FC_CONTA_CUO_COMPLETO(rc.ID_TIPOORIGEN ,rc.ID_RETDETALLE, a.ID_ENTIDAD, a.ID_ANHO, a.ID_MES),'') as cuo,
        B.ID_RUC, B.NOMBRE, R.SERIE AS SERIE_RETENCION, R.NRO_RETENCION, 
        to_char(R.FECHA_EMISION, 'dd/mm/yyyy') AS fecha_retencion, 
        COALESCE(A.IMPORTE,0) - COALESCE(A.BASE_SINCREDITO,0) - COALESCE(A.OTROS,0) AS Importe_pagado,
        COALESCE(RC.IMPORTE_RET,0) AS Importe_retenido, 
        A.ID_COMPROBANTE AS tipo_doc_comprobante,
        A.SERIE AS serie_comprobante,
        A.NUMERO AS nro_comprobante,
        to_char(A.FECHA_DOC, 'dd/mm/yyyy') AS fecha_comprobante
        FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_JURIDICA B, COMPRA A, CAJA_RETENCION_COMPRA RC, CAJA_RETENCION r
                    WHERE A.ID_PROVEEDOR = b.ID_PERSONA
                    AND A.ID_ENTIDAD = E.ID_ENTIDAD
                    AND R.ID_RETENCION = RC.ID_RETENCION
                    AND RC.ID_COMPRA = A.ID_COMPRA
                    AND E.ID_EMPRESA = $id_empresa
                    AND (A.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
                    AND (A.ID_DEPTO = '$id_depto' OR '*' = '$id_depto' )
                    AND R.ID_ANHO = $id_anho
                    AND R.ID_MES = $id_mes
                    AND NOT A.ID_COMPROBANTE IN ('02')
                    --AND A.Estado = '1'
                    AND R.Estado = '1'
                    ";
        $oQuery = DB::select($query);
        return $oQuery;
    }  
    public static function getWithholdingRecordSum($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes) {

        $query = "
        SELECT 
        sum(COALESCE(A.IMPORTE,0) - COALESCE(A.BASE_SINCREDITO,0) - COALESCE(A.OTROS,0)) AS importe_pagado,
        sum(COALESCE(RC.IMPORTE_RET,0)) AS importe_retenido
        FROM CONTA_ENTIDAD E, MOISES.VW_PERSONA_JURIDICA B, COMPRA A, CAJA_RETENCION_COMPRA RC, CAJA_RETENCION r
                    WHERE A.ID_PROVEEDOR = b.ID_PERSONA
                    AND A.ID_ENTIDAD = E.ID_ENTIDAD
                    AND R.ID_RETENCION = RC.ID_RETENCION
                    AND RC.ID_COMPRA = A.ID_COMPRA
                    AND E.ID_EMPRESA = $id_empresa
                    AND (A.ID_ENTIDAD = $id_entidad OR 0 = $id_entidad )
                    AND (A.ID_DEPTO = '$id_depto' OR '*' = '$id_depto' )
                    AND R.ID_ANHO = $id_anho
                    AND R.ID_MES = $id_mes
                    AND NOT A.ID_COMPROBANTE IN ('02')
                    AND A.Estado = '1'
                    ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function existeSuspencionEnElAnho($id_entidad, $id_anho, $id_proveedor)
    {
        $suspension = DB::table('COMPRA_SUSPENCION')
            ->join('CONTA_ENTIDAD', 'COMPRA_SUSPENCION.ID_EMPRESA', '=','CONTA_ENTIDAD.ID_EMPRESA')
            ->where('COMPRA_SUSPENCION.ID_PROVEEDOR', '=', $id_proveedor)
            ->where('CONTA_ENTIDAD.ID_ENTIDAD', '=', $id_entidad)
            ->where('COMPRA_SUSPENCION.ID_ANHO', '=', $id_anho);
        return $suspension->exists();
    }

    // public static function addSuspesionRenta($data)
    // {
    //     try
    //     {
    //         DB::table('ELISEO.COMPRA_SUSPENCION')->insert($data);
    //         return true;
    //     }
    //     catch(Exception $e)
    //     {
    //         return false;
    //     }
    // }

    public static function getSuspesionRenta($id_entidad, $id_proveedor) {
        // $query = "SELECT A.ID_SUSPENSION, A.ID_ANHO, A.ID_PROVEEDOR, A.FECHA_EMISION, 
        //             A.FECHA_PRESENTACION, A.NRO_OPERACION
        //         FROM COMPRA_SUSPENCION A
        //         WHERE A.ID_ANHO = $id_anho
        //             AND A.ID_EMPRESA IN (SELECT B.ID_EMPRESA FROM CONTA_EMPRESA B WHERE B.ID_ENTIDAD=$id_entidad) 
        //             AND ID_PROVEEDOR = $id_proveedor
        //         ";
        $query = "SELECT A.ID_SUSPENSION, A.ID_ANHO, A.ID_PROVEEDOR, A.FECHA_EMISION, 
                A.FECHA_PRESENTACION, A.NRO_OPERACION
                FROM COMPRA_SUSPENCION A
                WHERE A.ID_ANHO IN
                    (
                    SELECT max(X.ID_ANHO) FROM CONTA_ENTIDAD_ANHO_CONFIG X 
                        WHERE X.ID_ENTIDAD=$id_entidad AND X.ACTIVO='1'
                    )
                AND A.ID_EMPRESA IN (SELECT B.ID_EMPRESA FROM CONTA_ENTIDAD B WHERE B.ID_ENTIDAD=$id_entidad) 
                AND ID_PROVEEDOR = $id_proveedor
                ";

        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getRetencionByNroAndSerie($nro_retencion, $serie) {
        $query = "select * from CAJA_RETENCION WHERE NRO_RETENCION='$nro_retencion' AND SERIE='$serie'";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getAccountStatus($id_empresa, $id_anho, $id_persona, $id_entidad, $id_depto) {

	// Este codigo ha sido corregido
	// varios cambios

        $query = "SELECT 
                a.ID_ENTIDAD, 
                a.ID_DEPTO, 
                a.ID_MES, 
                m.siglas as mes_nombre,
                a.id_origen,
                a.id_tipoorigen,
                v.NUMERO AS numero_voucher,
                v.LOTE,
                a.correlativo,
                tio.NOMBRE,
                a.NRO_REFERENCIA, 
                a.ID_COMPROBANTE, 
                c.NOMBRE_CORTO, 
                SERIE, 
                a.NUMERO, 
                a.oper,
                to_char(FECHA_DOC, 'dd/mm/yyyy') AS FECHA_DOC, 
                to_char(FECHA_PROVISION, 'dd/mm/yyyy') AS FECHA_PROVISION, 
                to_char(FECHA_PROVISION, 'yyyymmddhh24miss') AS FECHA_ORDEN, 
                to_char(V.FECHA, 'dd/mm/yyyy')as FECHA_LOTE,
                a.IMPORTE,
                a.IMPORTE_ME,
                a.TIPOCAMBIO,
                (SELECT X.COD_SUNAT FROM CONTA_MONEDA X WHERE X.ID_MONEDA = a.ID_MONEDA) AS MONEDA,
                DECODE(A.ID_TIPOORIGEN,3,(SELECT MAX(Y.ID_PEDIDO) FROM PEDIDO_COMPRA X JOIN PEDIDO_FILE Y ON X.ID_PEDIDO = Y.ID_PEDIDO WHERE X.ID_COMPRA = A.ID_COMPRA),NULL) AS ID_PEDIDO
        FROM VW_PURCHASES_MOV a
        JOIN TIPO_COMPROBANTE c ON a.ID_COMPROBANTE = c.ID_COMPROBANTE
        JOIN CONTA_ENTIDAD e ON a.id_entidad = e.ID_ENTIDAD
        JOIN CONTA_MES m ON a.id_mes = m.id_mes
        LEFT JOIN CONTA_VOUCHER v ON a.ID_VOUCHER = v.ID_VOUCHER
        JOIN TIPO_ORIGEN tio ON a.ID_TIPOORIGEN = tio.ID_TIPOORIGEN
        WHERE e.ID_EMPRESA = $id_empresa AND a.ID_PROVEEDOR = $id_persona AND a.ID_ANHO = $id_anho
        AND a.id_entidad =  $id_entidad AND a.id_depto = $id_depto
        ORDER BY a.id_mes, a.serie, a.numero, FECHA_ORDEN";
        $oQuery = DB::select($query);
        return $oQuery;

    }
    public static function getAccountStatusTotal($id_empresa, $id_anho, $id_persona, $id_entidad, $id_depto) {

        // Este codigo ha sido corregido
        // varios cambios
            $query = "SELECT 
            COALESCE(sum(a.IMPORTE), 0) as IMPORTE
            FROM      VW_PURCHASES_MOV   a
            INNER JOIN  TIPO_COMPROBANTE   c ON a.id_comprobante = c.id_comprobante
            INNER JOIN  CONTA_ENTIDAD      e ON a.id_entidad = e.id_entidad
            INNER JOIN  TIPO_ORIGEN        tio ON a.id_tipoorigen = tio.id_tipoorigen
            LEFT JOIN  CONTA_VOUCHER      v ON a.id_voucher = v.id_voucher 
            WHERE e.ID_EMPRESA = $id_empresa
            AND a.ID_PROVEEDOR = $id_persona
            AND a.ID_ANHO = $id_anho
            AND a.id_entidad = $id_entidad 
            AND a.id_depto =  $id_depto " ;
 
            $oQuery = DB::select($query);
            return $oQuery;
    
        }
    public static function getAccountStatusDetail($id_tipoorigen, $id_origen) {

        $query = "SELECT 
                    CUENTA,CUENTA_CTE,FONDO,DEPTO,RESTRICCION,IMPORTE,DESCRIPCION,MEMO
                    FROM CONTA_ASIENTO
                    WHERE ID_TIPOORIGEN = $id_tipoorigen
                    AND ID_ORIGEN = $id_origen
                    ORDER BY id_asiento
                    ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getPaymentsByCompraId($id_compra) {

        $query = "SELECT A.IMPORTE , A.IMPORTE_ME , A.DETALLE, TO_CHAR(B.FECHA,'DD/MM/YYYY') AS PAGO_FECHA, 
        B.NUMERO AS PAGO_NUMERO, DECODE(ID_MEDIOPAGO,'001','TLC','007','CHQ','008','EFEC') AS PAGO_MEDIOPAGO, 
        COALESCE(PKG_CAJA.FC_CUENTA_BANCARIA(B.ID_CTABANCARIA),'None') AS PAGO_CTABANCARIA,
        TO_CHAR(C.FECHA,'DD/MM/YYYY') AS VOUCHER_FECHA, C.LOTE AS VOUCHER_LOTE, C.NUMERO AS VOUCHER_NUMERO, (SELECT X.EMAIL FROM USERS X WHERE X.ID=C.ID_PERSONA ) AS USER_PAY
        FROM ELISEO.CAJA_PAGO_COMPRA A 
        INNER JOIN ELISEO.CAJA_PAGO B ON A.ID_PAGO =B.ID_PAGO 
        INNER JOIN ELISEO.CONTA_VOUCHER C ON B.ID_VOUCHER =C.ID_VOUCHER 
        WHERE A.ID_COMPRA =$id_compra
                    ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public  static function getCompraFullById($id_compra) {

        $query = "SELECT C.ID_COMPRA, C.ID_PROVEEDOR AS PROVEEDOR_ID, FC_NOMBRE_PERSONA(C.ID_PROVEEDOR) AS PROVEEDOR_RAZONSOCIAL,
                  FC_DOCUMENTO_CLIENTE(C.ID_PROVEEDOR) AS PROVEEDOR_RUC,
                  C.ID_COMPROBANTE,
                  (CASE WHEN C.BASE_GRAVADA > 0 THEN 'G'
                        WHEN C.BASE_NOGRAVADA > 0 THEN 'NG'
                        WHEN C.BASE_MIXTA > 0 THEN 'GNG'
                        WHEN C.BASE_MIXTA > 0 THEN 'GNG'
                        WHEN C.BASE_SINCREDITO > 0 THEN 'ANG'
                        ELSE 'NR' END
                  ) AS TIPO,
                  C.ES_ELECTRONICA,
                  C.ES_CREDITO,
                  C.ESTADO,
                  C.ID_PARENT,
                  coalesce(C.ES_TRANSPORTE_CARGA,'N') ES_TRANSPORTE_CARGA,
                  C.SERIE,
                  C.NUMERO,
                  TO_CHAR(C.FECHA_DOC, 'DD/MM/YYYY') AS FECHA_DOC,
                  TO_CHAR(C.FECHA_VENCIMIENTO, 'DD/MM/YYYY') AS FECHA_VENCIMIENTO,
                  
                  coalesce(C.ID_MONEDA,0) ID_MONEDA,
                  coalesce(C.TIPOCAMBIO,0) TIPOCAMBIO,
                  (CASE WHEN C.ID_MONEDA = '9' THEN C.IMPORTE_ME
                    ELSE C.IMPORTE END) AS IMPORTE,
                  
                  coalesce(C.BASE_SINCREDITO,0) INAFECTA,
                  
                  coalesce(C.OTROS,0) OTROS,
                  coalesce(C.TAXS,0) TAXS,
                  coalesce(C.ES_RET_DET,'0') ES_RET_DET,
                  
                  -- Retención
                  coalesce(CRC.ID_RETDETALLE,NULL) AS RETENCION_DETALLE_ID,
                  coalesce(CR.ID_RETENCION,NULL) AS RETENCION_ID,
                  coalesce(CR.SERIE,'') AS RETENCION_SERIE,
                  coalesce(CR.NRO_RETENCION,'') AS RETENCION_NUMERO,
                  TO_CHAR(CR.FECHA_EMISION, 'DD/MM/YYYY') AS RETENCION_FECHA_EMISION,
                  coalesce(CRC.IMPORTE_TOTAL,0) AS RETENCION_IMPORTE_TOTAL,
                  
                  -- Detracción
                  coalesce(CDC.ID_DETDETALLE,NULL) AS DETRACCION_COMPRA_ID,
                  coalesce(CD.ID_DETRACCION,NULL) AS DETRACCION_ID,
                  coalesce(CD.ID_OPERACION,'') AS DETRACCION_ID_OPERACION,
                  coalesce(CD.ID_TIPOBIENSERVICIO,'') AS DETRACCION_ID_TIPOBIENSERVICIO,
                  coalesce(CD.ID_CTABANCARIA,NULL) AS DETRACCION_ID_CTABANCARIA,
                  coalesce(CD.NRO_CONSTANCIA,'') AS DETRACCION_NRO_CONSTANCIA,
                  coalesce(CD.NRO_OPERACION,'') AS DETRACCION_NRO_OPERACION,
                  coalesce(CDC.IMPORTE,0) AS DETRACCION_COMPRA_IMPORTE,
                  TO_CHAR(CD.FECHA_EMISION, 'DD/MM/YYYY') AS DETRACCION_FECHA_EMISION
                  
                  FROM COMPRA C
                    LEFT JOIN CAJA_RETENCION_COMPRA CRC ON C.ID_COMPRA=CRC.ID_COMPRA
                    LEFT JOIN CAJA_RETENCION CR ON CRC.ID_RETENCION=CR.ID_RETENCION
                    
                    LEFT JOIN CAJA_DETRACCION_COMPRA CDC ON C.ID_COMPRA=CDC.ID_COMPRA
                    LEFT JOIN CAJA_DETRACCION CD ON CDC.ID_DETRACCION=CD.ID_DETRACCION
                  WHERE C.ID_COMPRA = $id_compra";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function getTypeForm($id_pedido) {
        $query = "SELECT 
                        B.ID_FORMULARIO 
                FROM PEDIDO_REGISTRO A, PEDIDO_AREA B
                WHERE A.ID_AREADESTINO = B.ID_SEDEAREA
                AND A.ID_PEDIDO = ".$id_pedido." ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function viewFiles($id_pedido,$url) {
        $storage = new StorageController();
        $fQuery = "SELECT
                ID_PFILE,NOMBRE,FORMATO,--'" . $url . "/'||URL AS URL, 
                URL,
                URL AS PURL,
                TIPO
                FROM PEDIDO_FILE
                WHERE ID_PEDIDO = " . $id_pedido . " ";
        $files = DB::select($fQuery);
        $files = collect($files)->map(function ($item) use($storage) {
            $item->url = $storage->getUrlByName($item->url);
            return $item;
        });
        return $files;
    }
    public static function updateTotalCompra($id_compra) {
        //$id_compra = 0;
        $error = 0;
        $msg_error = '';
        $objReturn = [];
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_ACTUALIZAR_TOTAL_COMPRA(:P_ID_COMPRA);end;");
            $stmt->bindParam(':P_ID_COMPRA',$id_compra, PDO::PARAM_INT);
            $stmt->execute();

            $objReturn['error'] = 0;
            $objReturn['data'] = $id_compra;
            $objReturn['message'] = 'OK';
            return $objReturn;
        } catch(Exception $e){
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $error = "202";
            return $jResponse;
        }
    }

    public static function deleteCompraDetalleOfKardex($id_compra) {
        $error = 0;
        $msg_error = '';
        for($x=1 ; $x<=200 ; $x++){
            $msg_error .= "0";
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_DELETE_COMPRA_KARDEX(:P_ID_COMPRA,:P_ERROR,:P_MSG);end;");
        $stmt->bindParam(':P_ID_COMPRA',$id_compra, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSG', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        if($error ===1) {
            throw new Exception($msg_error, 1);
        }
    }

    public static function updateTotalImporteCompra($id_compra) {
        //$id_compra = 0;
        $error = 0;
        $msg_error = '';
        $objReturn = [];
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_ACTUALIZAR_TOTALIMP_COMPRA(:P_ID_COMPRA);end;");
            $stmt->bindParam(':P_ID_COMPRA',$id_compra, PDO::PARAM_INT);
            $stmt->execute();

            $objReturn['error'] = 0;
            $objReturn['data'] = $id_compra;
            $objReturn['message'] = 'OK';
            return $objReturn;
        } catch(Exception $e){
            $jResponse['error'] = 1;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $error = "202";
            return $jResponse;
        }
    }

    public static function showDetalle($id_detalle) {
        $query = "SELECT ID_COMPRA,ID_ARTICULO,FECHA_VENCIMIENTO
                FROM COMPRA_DETALLE
                WHERE ID_DETALLE = ".$id_detalle." ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addSeatsPurchases($id_compra){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_ASIENTO(:P_ID_COMPRA,:P_ERROR,:P_MSGERROR);end;");
            $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function addSeatsPurchasesInventories($id_compra,$id_dinamica){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_ASIENTO_INVENTARIO(:P_ID_COMPRA,:P_ID_DINAMICA,:P_ERROR,:P_MSGERROR);end;");
            $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }

    public static function addSeatsPurchasesInventoriesUPN($id_compra,$id_dinamica){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_ASIENTO_INVEN_UPN(:P_ID_COMPRA,:P_ID_DINAMICA,:P_ERROR,:P_MSGERROR);end;");
            $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }

    public static function showPurchaseDif($id_compra,$imp_det) {
        $query = "SELECT 
                A.ID_COMPRA,A.IMPORTE,NVL(SUM(B.IMPORTE),0)+".$imp_det." AS IMP_DET,SIGN(A.IMPORTE-(NVL(SUM(B.IMPORTE),0)+".$imp_det.")) SIGNO
                FROM COMPRA A LEFT JOIN COMPRA_DETALLE B
                ON A.ID_COMPRA = B.ID_COMPRA
                WHERE A.ID_COMPRA = ".$id_compra."
                GROUP BY A.ID_COMPRA,A.IMPORTE";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPurchasesExpenses($id_entidad, $id_paso){
        $sql = "SELECT 
                A.ID_GASTO,B.NOMBRE,C.CODIGO 
                FROM PEDIDO_GASTO_PASO A, PEDIDO_GASTO B, TIPO_GASTO C
                WHERE A.ID_CGASTO = B.ID_CGASTO
                AND A.ID_TIPOGASTO = C.ID_TIPOGASTO
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_PASO = ".$id_paso."
                ORDER BY CODIGO,NOMBRE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showTypesPurchasesExpenses($id_pedido){
        $sql = "SELECT 
                C.CODIGO 
                FROM PEDIDO_REGISTRO A, PEDIDO_GASTO_PASO B, TIPO_GASTO C
                WHERE A.ID_GASTO = B.ID_GASTO
                AND B.ID_TIPOGASTO = C.ID_TIPOGASTO
                AND A.ID_PEDIDO = ".$id_pedido." ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listOrdersPurchaseDetails($id_pcompra){
        $sql = "SELECT 
                        ID_CDETALLE,ID_PCOMPRA,ID_ALMACEN,ID_ARTICULO,ID_TIPOIGV,PKG_INVENTORIES.FC_ARTICULO(ID_ARTICULO) AS NOMBRE_ARTICULO,
                        FC_UNIDAD_MEDIDA(ID_ARTICULO) AS UNIDAD_MEDIDA,CANTIDAD,PRECIO,BASE,IGV,IMPORTE
                FROM PEDIDO_COMPRA_DETALLE
                WHERE ID_PCOMPRA = ".$id_pcompra."
                ORDER BY ID_CDETALLE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addOrdersPurchaseDetailsGenerate($id_pcompra,$id_pedido){
        $error      = 0;
        $msg_error  = 'OK';
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_PCOMPRA_DETALLE(:P_ID_PCOMPRA,:P_ID_PEDIDO);end;");
            $stmt->bindParam(':P_ID_PCOMPRA', $id_pcompra, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function addOrdersPurchaseDetails($id_pcompra,$id_almacen,$id_articulo,$id_tipoigv,$cantidad,$precio){
        $error      = 0;
        $msg_error  = 'OK';
        try {
            DB::table('PEDIDO_COMPRA_DETALLE')->insert(
                    array(  'ID_PCOMPRA' => $id_pcompra,
                            'ID_ALMACEN' => $id_almacen,
                            'ID_ARTICULO' => $id_articulo,
                            'ID_TIPOIGV' => $id_tipoigv,
                            'CANTIDAD' => $cantidad, 
                            'PRECIO' => $precio,
                            'BASE'=> DB::raw(($cantidad*$precio)/1.18),
                            'IGV'=> DB::raw((($cantidad*$precio)/1.18)*0.18),
                            'IMPORTE'=> DB::raw($cantidad*$precio)
                    )
            );
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function updateOrdersPurchaseDetails($id_cdetalle,$cantidad,$precio){
        $error      = 0;
        $msg_error  = '';
        try{
            $query = "UPDATE PEDIDO_COMPRA_DETALLE SET  CANTIDAD = $cantidad,
                                                        PRECIO = $precio,
                                                        BASE = ($cantidad*$precio)/1.18,
                                                        IGV = (($cantidad*$precio)/1.18)*0.18,
                                                        IMPORTE = $cantidad*$precio
                         WHERE ID_CDETALLE = ".$id_cdetalle." ";        
            DB::update($query);
            $jResponse['error']   = 0;
            $jResponse['message'] = "OK";
        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
        }
        return $jResponse;
    }
    public static function deleteOrdersPurchaseDetails($id_cdetalle){
        try{
            $result = DB::table('pedido_compra_detalle')
                ->where('id_cdetalle', '=', $id_cdetalle)
                ->delete();
        }catch(Exception $e){
            $result = false;
        }
        return $result;
    }
    public static function deleteOrdersPurchaseDetailsALL($id_pcompra){
        try{
            $result = DB::table('pedido_compra_detalle')
                ->where('id_pcompra', '=', $id_pcompra)
                ->delete();
        }catch(Exception $e){
            $result = false;
        }
        return $result;
    }
    public static function addOrdersPurchaseTemplate($id_pedido,$id_plantilla){
        $error      = 0;
        $msg_error  = 'OK';
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_PCOMPRA_PLANTILLA(:P_ID_PLANTILLA,:P_ID_PEDIDO);end;");
            $stmt->bindParam(':P_ID_PLANTILLA', $id_plantilla, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function addPurchasesDetailsGenerate($id_pcompra,$id_compra){
        $error      = 0;
        $msg_error  = 'OK';
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_DETALLE(:P_ID_PCOMPRA,:P_ID_COMPRA);end;");
            $stmt->bindParam(':P_ID_PCOMPRA', $id_pcompra, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }

    // Ejecutarlo dentro de un try-catch
    public static function addPurchasesInvetarioDetailsUPN($id_compra,$id_almacen, $id_articulo,$cantidad,$importe,$detalle,$id_detalle){
        $error = 0;
        $msg_error = '';
        for($x=1;$x<=200;$x++){
            $msg_error .= "0";
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_DETALLE_IUDP2(
                    :P_ID_COMPRA,
                    :P_ID_ALMACEN,
                    :P_ID_ARTICULO,
                    :P_CANTIDAD,
                    :P_IMPORTE,
                    :P_DETALLE,
                    :P_ID_DETALLE,
                    :P_ERROR,
                    :P_MSN_ERROR
                    );
                    end;");
        $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ALMACEN', $id_almacen, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ARTICULO', $id_articulo, PDO::PARAM_INT);
        $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
        $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
        $stmt->bindParam(':P_DETALLE', $detalle, PDO::PARAM_STR);

        $stmt->bindParam(':P_ID_DETALLE', $id_detalle, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSN_ERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        if ($error === 1) {
            throw new Exception($msg_error, 1);
        }
    }

    public static function addOrdersCotizacion($data){
        DB::table('pedido_cotizacion')->insert($data);
        return $data;
    }
    public static function updateOrdersCotizacion($data, $id_cotizacion){
        try{
            $result = DB::table('pedido_cotizacion')
                ->where('id_cotizacion', $id_cotizacion)
                ->update($data);
            return $result;
        }catch(Exception $e){
            return false;
        }
    }
    public static function listPedidoFileQuotation($id_pedido,$url){
        $sql = "SELECT 
                A.ID_COTIZACION,A.ID_PEDIDO,A.ID_PFILE,A.ID_PROVEEDOR,FC_NOMBRE_CLIENTE(A.ID_PROVEEDOR) AS PROVEEDOR,A.IMPORTE,'".$url."/'||B.URL AS URL
                FROM PEDIDO_COTIZACION A,PEDIDO_FILE B
                WHERE A.ID_PFILE = B.ID_PFILE
                AND A.ID_PEDIDO = ".$id_pedido."
                ORDER BY ID_COTIZACION ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listPedidoFileQuotationSelected($id_pedido){
        $sql = "SELECT 
                ID_PEDIDO,ID_PROVEEDOR,PKG_PURCHASES.FC_RUC(ID_PROVEEDOR) AS RUC,FC_NOMBRE_PERSONA(ID_PROVEEDOR) AS RAZONSOCIAL,IMPORTE 
                FROM PEDIDO_COTIZACION
                WHERE ID_PEDIDO = ".$id_pedido."
                AND ES_ELEGIDO = 'S' ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listPurchasesSeatsAcounting($id_compra,$id_anho,$id_empresa){
        $query = "SELECT ID_CASIENTO, ID_COMPRA, ID_CUENTAAASI, ID_RESTRICCION,
                    (SELECT X.ID_COMPROBANTE FROM COMPRA X WHERE X.ID_COMPRA = A.ID_COMPRA) AS ID_COMPROBANTE, 
                    ID_CTACTE, ID_FONDO, ID_DEPTO, IMPORTE, IMPORTE_ME, DESCRIPCION, EDITABLE, ID_PARENT, ID_TIPOREGISTRO, DC, NRO_ASIENTO,
                    (SELECT X.ID_CUENTAEMPRESARIAL FROM CONTA_EMPRESA_CTA X WHERE X.ID_CUENTAAASI = A.ID_CUENTAAASI AND X.ID_RESTRICCION = A.ID_RESTRICCION 
                    AND X.ID_TIPOPLAN = 1 AND X.ID_ANHO = ".$id_anho." AND X.ID_EMPRESA = ".$id_empresa.") AS CTA_EMPRESARIAL
                FROM COMPRA_ASIENTO A
                WHERE ID_COMPRA = $id_compra
                ORDER BY ID_CASIENTO ASC ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPurchasesSeatsAcountingTotal($id_compra) {
        $query = "
                SELECT 
                SUM(DECODE(DC,'C',IMPORTE,0)) AS CREDITO,
                SUM(DECODE(DC,'D',IMPORTE,0)) AS DEBITO
                FROM COMPRA_ASIENTO 
                WHERE ID_COMPRA = $id_compra";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addSeatsPurchasesDynamic($id_compra,$id_dinamica){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_COMPRA_ASIENTO_SIM(
            :P_ID_COMPRA,
            :P_ID_DINAMICA,
            :P_ERROR,
            :P_MSGERROR
            );
            end;");
            $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }

    public static function getDetailOrderStatusBK($id_pedido)
    {
        /*$pquery = DB::table('PROCESS_RUN')
            ->select(
                DB::raw("id_proceso, id_operacion")
            )->where('id_operacion', $id_pedido)
            ->first();
        $proces = DB::table('PROCESS_RUN')
            ->select(
                DB::raw("id_registro")
            )->where([
                ['id_proceso', '=', $pquery->id_proceso],
                ['id_operacion', '=', $pquery->id_operacion]
            ])
            ->first();
        $query = "SELECT
              A.ID_PASO,
              B.NOMBRE AS PASO_DE,
              A.ID_PASO_NEXT,
              C.NOMBRE AS PASO_AS,
              A.FECHA,
              A.DETALLE,
              A.ID_PERSONA,
              REVISADO
            FROM PROCESS_PASO_RUN A, PROCESS_PASO B, PROCESS_PASO C
            WHERE A.ID_PASO = B.ID_PASO
                  AND A.ID_PASO_NEXT = C.ID_PASO
                  -- AND A.ESTADO <> '1'
                  AND A.ID_REGISTRO = $proces->id_registro
            ORDER BY A.ID_DETALLE";*/


        $nquery = "SELECT
        A.ID_PASO,
          B.NOMBRE AS PASO_DE,
          A.ID_PASO_NEXT,
          C.NOMBRE AS PASO_AS,
          A.FECHA,
          A.DETALLE,
          A.ID_PERSONA,
          (select p.NOMBRE||' '||p.PATERNO||' '||p.MATERNO from MOISES.PERSONA p where p.ID_PERSONA = A.ID_PERSONA) as registrador,
          REVISADO,
          --A.ESTADO,
          DECODE(D.ID_TIPOPASO,4,1,A.ESTADO) AS ESTADO,
          d.NOMBRE
        FROM PROCESS_PASO_RUN A, PROCESS_PASO B, PROCESS_PASO C, PROCESS_TIPOPASO D
        WHERE A.ID_PASO = B.ID_PASO
        AND A.ID_PASO_NEXT = C.ID_PASO
        AND c.ID_TIPOPASO = D.ID_TIPOPASO
        --AND d.ID_TIPOPASO = 2
        AND A.ID_REGISTRO = (SELECT ID_REGISTRO
        FROM PROCESS_RUN r
        WHERE ID_PROCESO = (
          SELECT id_proceso
        FROM PROCESS_RUN
        WHERE
        ID_OPERACION = r.ID_OPERACION)
        AND r.ID_OPERACION = $id_pedido)
        ORDER BY A.ID_DETALLE DESC";

        $oQuery = DB::select($nquery);
        return $oQuery;
    }
    public static function getDetailOrderStatus($id_pedido,$codigo=null){
        $nquery = "SELECT A.ID_PASO,
                            B.NOMBRE AS PASO_DE,
                            A.ID_PASO_NEXT,
                            C.NOMBRE AS PASO_AS,
                            A.FECHA,
                            A.DETALLE,
                            A.ID_PERSONA,
                            (SELECT p.NOMBRE || ' ' || p.PATERNO || ' ' || p.MATERNO
                            FROM MOISES.PERSONA p
                            WHERE p.ID_PERSONA = A.ID_PERSONA)
                            AS registrador,
                            REVISADO,
                            DECODE (D.ID_TIPOPASO, 4, E.ESTADO, A.ESTADO) AS ESTADO,
                            d.NOMBRE
                    FROM PROCESS_PASO_RUN A,PROCESS_PASO B, PROCESS_PASO C,PROCESS_TIPOPASO D, PROCESS_RUN E
                    WHERE A.ID_PASO = B.ID_PASO
                    AND A.ID_PASO_NEXT = C.ID_PASO
                    AND c.ID_TIPOPASO = D.ID_TIPOPASO
                    AND A.ID_REGISTRO = E.ID_REGISTRO
                    AND E.ID_OPERACION = ".$id_pedido."
                    AND E.ID_PROCESO IN (SELECT ID_PROCESO FROM PROCESS WHERE CODIGO = '".$codigo."')
                    ORDER BY A.ID_DETALLE DESC ";
        $oQuery = DB::select($nquery);
        return $oQuery;
    }
    public static function getPurchasesStatus($id_pedido,$codigo=null){
        return DB::select("SELECT A.ID_PASO,
                B.NOMBRE AS PASO_DE,
                A.ID_PASO_NEXT,
                C.NOMBRE AS PASO_AS,
                A.FECHA,
                A.DETALLE,
                A.ID_PERSONA,
                NVL(LTRIM(FC_NOMBRE_PERSONA(A.ID_PERSONA)),
                (CASE WHEN B.NOMBRE = 'Provision' AND A.ID_PERSONA IS NULL 
                THEN 
                (SELECT FC_NOMBRE_PERSONA(Y.ID_PERSONA) FROM PEDIDO_COMPRA X JOIN COMPRA Y ON X.ID_COMPRA = Y.ID_COMPRA WHERE X.ID_PEDIDO = E.ID_OPERACION )
                ELSE NULL END))
                AS REGISTRADOR,
                REVISADO,
                DECODE (D.ID_TIPOPASO, 4, E.ESTADO, A.ESTADO) AS ESTADO,
                d.NOMBRE,
                G.NOMBRE_ACCION
        FROM PROCESS_PASO_RUN A,PROCESS_PASO B, PROCESS_PASO C,PROCESS_TIPOPASO D, PROCESS_RUN E, PROCESS_COMPONENTE_PASO F, PROCESS_COMPONENTE G
        WHERE A.ID_PASO = B.ID_PASO
        AND A.ID_PASO_NEXT = C.ID_PASO
        AND c.ID_TIPOPASO = D.ID_TIPOPASO
        AND A.ID_REGISTRO = E.ID_REGISTRO
        AND B.ID_PASO = F.ID_PASO
        AND F.ID_COMPONENTE = G.ID_COMPONENTE
        AND E.ID_OPERACION = ?
        AND E.ID_PROCESO IN (SELECT ID_PROCESO FROM PROCESS WHERE CODIGO = ?)
        AND G.LLAVE IN ('FPP','FPP3','FAT','FP')
        ORDER BY A.ID_DETALLE DESC", [$id_pedido, $codigo]);
    }
    public static function getAllOrderDetail($id_pedido, $url)
    {
        $storage = new StorageController();

      $pedido = "SELECT
      pedr.id_pedido,
        pedr.id_tipopedido,
        pedr.numero,
        pedr.motivo as detalle,
        pedr.MOTIVO,
        pedr.FECHA_PEDIDO,
        pedr.FECHA_ENTREGA,
        pedr.fecha,
        (SELECT ORG_AREA.NOMBRE
      FROM ORG_AREA
      INNER JOIN ORG_SEDE_AREA ON ORG_AREA.ID_AREA = ORG_SEDE_AREA.ID_AREA
      WHERE ORG_SEDE_AREA.ID_SEDEAREA = pedr.ID_AREAORIGEN)  area_origen,
      (SELECT ORG_AREA.NOMBRE
      FROM ORG_AREA
      INNER JOIN ORG_SEDE_AREA ON ORG_AREA.ID_AREA = ORG_SEDE_AREA.ID_AREA
      WHERE ORG_SEDE_AREA.ID_SEDEAREA = pedr.ID_AREADESTINO) area_destino,
      (SELECT act.NOMBRE
      FROM PSTO_ACTIVIDAD act
      WHERE act.ID_ACTIVIDAD = pedr.ID_ACTIVIDAD) AS         actividad,
      (SELECT NOMBRE FROM TIPO_PEDIDO WHERE ID_TIPOPEDIDO = pedr.id_tipopedido) as tipo_pedido
      FROM PEDIDO_REGISTRO pedr
      WHERE pedr.ID_PEDIDO = $id_pedido";

      $pedido_compra = "SELECT
      pedc.fecha,
        pedc.numero,
        pedc.SERIE,
        pedc.importe,
        (SELECT cm.NOMBRE
      FROM CONTA_MONEDA cm
      WHERE cm.ID_MONEDA = pedc.ID_MONEDA)      AS moneda,
      (SELECT cm.SIMBOLO
      FROM CONTA_MONEDA cm
      WHERE cm.ID_MONEDA = pedc.ID_MONEDA)      AS moneda_simb,
      (SELECT per.NOMBRE
      FROM MOISES.PERSONA per
      WHERE per.ID_PERSONA = pedc.ID_PROVEEDOR) AS proveedor
      FROM PEDIDO_COMPRA pedc
      WHERE pedc.ID_PEDIDO = $id_pedido";
      $pcompra_detalle = "SELECT
      (SELECT art.nombre
      FROM INVENTARIO_ARTICULO art
      WHERE art.ID_ARTICULO = PEDIDO_COMPRA_DETALLE.ID_ARTICULO) nombre,
      PEDIDO_COMPRA_DETALLE.CANTIDAD,
      PEDIDO_COMPRA_DETALLE.PRECIO,
      PEDIDO_COMPRA_DETALLE.BASE,
      PEDIDO_COMPRA_DETALLE.IGV,
      PEDIDO_COMPRA_DETALLE.IMPORTE
      FROM PEDIDO_COMPRA_DETALLE
      INNER JOIN PEDIDO_COMPRA ON PEDIDO_COMPRA_DETALLE.ID_PCOMPRA = PEDIDO_COMPRA.ID_PCOMPRA
      WHERE PEDIDO_COMPRA.ID_PEDIDO = $id_pedido";
      $pedido_detalle = "SELECT
      (SELECT art.nombre
      FROM INVENTARIO_ARTICULO art
      WHERE art.ID_ARTICULO = pd.ID_ARTICULO) articulo,
      pd.cantidad,
      pd.precio
      FROM PEDIDO_DETALLE pd
      WHERE pd.ID_PEDIDO = $id_pedido";

      $fQuery = "SELECT
      ID_PFILE,NOMBRE,FORMATO,--'" . $url . "/'||URL AS URL, 
      URL,
      URL AS PURL,
      TIPO
      FROM PEDIDO_FILE
      WHERE ID_PEDIDO = " . $id_pedido . " ";
      $files = DB::select($fQuery);
      $files = collect($files)->map(function ($item) use($storage) {
        $item->url = $storage->getUrlByName($item->url);
        return $item;
      });


      $pQuery = DB::select($pedido);
      $cQuery = DB::select($pedido_compra);
      $cDQuery = [];
      $pdQuery = [];

      if ($cQuery) {
    //            dd('there is data', $cQuery);
        $cQuery = $cQuery[0];
        $cDQuery = DB::select($pcompra_detalle);
      } else {
    //            dd('THERE IS NOTT!!', $cQuery);
        $cQuery = '';
      }
      if ($pQuery) {
    //            dd('there is data', $cQuery);
        $pQuery = $pQuery[0];
        $pdQuery = DB::select($pedido_detalle);
      } else {
    //            dd('THERE IS NOTT!!', $cQuery);
        $pQuery = '';
      }

      $data = (object)array(
      'pedido' => $pQuery,
      'pcompra' => $cQuery,
      'pcitems' => $cDQuery,
      'pditems' => $pdQuery,
      'files' => $files
    );
      return $data;
    }
    public static function listPurchasesDetailsIGV($id_compra){
        $sql = "SELECT  
                    ID_CTIPOIGV
                FROM COMPRA_DETALLE
                WHERE ID_COMPRA = ".$id_compra."
                GROUP BY ID_CTIPOIGV
                ORDER BY ID_CTIPOIGV ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showTypeActivityEconomic($ciiu){
        $sql = "SELECT ID_TIPOACTIVIDADECONOMICA,NOMBRE,COD_SUNAT 
                FROM TIPO_ACTIVIDAD_ECONOMICA
                WHERE COD_SUNAT = '".$ciiu."' ";
        $query = DB::select($sql);
        return $query;
    }
    public static function ShowCompraEmpresa($id_compra){
        $sql = "SELECT 
                    A.ID_ANHO, B.ID_EMPRESA 
                FROM COMPRA A JOIN CONTA_ENTIDAD B
                ON A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_COMPRA = ".$id_compra." ";
        $query = DB::select($sql);
        return $query;
    }
    public static function importPurchasesBalances($data){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_IMPORTAR_SALDO_INICIAL(
                :P_ID_ENTIDAD,
                :P_ID_DEPTO,
                :P_ID_ANHO,
                :P_ID_COMPRA,
                :P_ID_MONEDA,
                :P_ID_PERSONA,
                :P_RUC,
                :P_ID_COMPROBANTE,
                :P_SERIE,
                :P_NUMERO,
                :P_FECHA_PROVISION,
                :P_FECHA_DOC,
                :P_IMPORTE,
                :P_IMPORTE_ME,
                :P_ERROR,
                :P_MSGERROR
                );
                end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $data["id_entidad"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $data["id_depto"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO', $data["id_anho"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPRA', $data["id_compra"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MONEDA', $data["id_moneda"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $data["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(':P_RUC', $data["ruc"], PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPROBANTE', $data["id_comprobante"], PDO::PARAM_STR);
            $stmt->bindParam(':P_SERIE', $data["serie"], PDO::PARAM_STR);
            $stmt->bindParam(':P_NUMERO', $data["numero"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_PROVISION', $data["fecha_provision"], PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_DOC', $data["fecha_doc"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $data["importe"], PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_ME', $data["importe_me"], PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function deleltePurchasesBalances($id_saldo){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_DELETE_COMPRA_SALDO(
                :P_ID_SALDO,
                :P_ERROR,
                :P_MSG
                );
                end;");
            $stmt->bindParam(':P_ID_SALDO', $id_saldo, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSG', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function listPurchasesBalances($id_entidad,$id_depto,$id_anho){
        $sql = "SELECT A.ID_SALDO,
                    A.ID_COMPRA,
                    A.ID_MONEDA,
                    A.ID_PERSONA,
                    A.ID_PROVEEDOR,
                    FC_NOMBRE_PERSONA (A.ID_PROVEEDOR) AS NOMBRE_PROVEEDOR,
                    PKG_PURCHASES.FC_RUC (A.ID_PROVEEDOR) AS RUC,
                    A.ID_COMPROBANTE,
                    A.SERIE,
                    A.NUMERO,
                    A.FECHA_PROVISION,
                    A.FECHA_DOC,
                    A.IMPORTE,
                    A.IMPORTE_ME
             FROM COMPRA_SALDO A
             WHERE A.ID_ENTIDAD = ".$id_entidad." 
             AND A.ID_DEPTO = '".$id_depto."' 
             AND A.ID_ANHO = ".$id_anho."
             ORDER BY NOMBRE_PROVEEDOR,A.ID_COMPROBANTE,A.FECHA_DOC ";
        $query = DB::select($sql);
        return $query;
    }
    public static function validatedVoucher($id_entidad,$id_depto,$id_anho,$id_tipovoucher,$id_user){
        $id_voucher = 0;
        $numero = 0;
        $fecha = '00/00/0000';
        $error = 0;
        $msg  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ACCOUNTING.SP_VALIDAR_VOUCHER(:P_ID_ENTIDAD,:P_ID_DEPTO,:P_ID_ANHO,:P_ID_TIPOVOUCHER,:P_ID_PERSONA,:P_ID_VOUCHER,:P_NUMERO,:P_FECHA,:P_ERROR,:P_MSN);end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPOVOUCHER', $id_tipovoucher, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
            $stmt->bindParam(':P_NUMERO', $numero, PDO::PARAM_INT);
            $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSN', $msg, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg;
            $objReturn['id_voucher']   = $id_voucher;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function showVoucherAutomatico($id_entidad,$id_depto,$id_anho,$id_tipovoucher){
        $sql = "SELECT 
                        AUTOMATICO,ID_TIPOASIENTO
                FROM CONTA_VOUCHER_CONFIG
                WHERE ID_ENTIDAD = ".$id_entidad."
                AND ID_DEPTO = '".$id_depto."'
                AND ID_ANHO = ".$id_anho."
                AND ID_TIPOVOUCHER = ".$id_tipovoucher." ";
        $query = DB::select($sql);
        return $query;
    }

    // para obtener arreglos
    public static function showArrangement($month){
        $sql = "SELECT 
                ar.id_arreglo,
                ar.id_persona, per.paterno ||' '|| per.materno ||', '|| per.nombre AS PERSONA ,
                ar.id_origen, ar.motivo, AR.FECHA, ta.id_tipoarreglo AS ID_TIPO, ta.nombre AS  TIPO_NAME,
                PKG_PURCHASES.FC_PROVEEDOR(C.ID_PROVEEDOR) PROVEEDOR,PKG_PURCHASES.FC_RUC(C.ID_PROVEEDOR) RCU,C.ID_VOUCHER,C.SERIE,C.NUMERO,C.FECHA_DOC,C.IMPORTE
                FROM ARREGLO AR
                INNER JOIN TIPO_ARREGLO TA ON ar.id_tipoarreglo = ta.id_tipoarreglo
                INNER JOIN MOISES.PERSONA PER ON per.id_persona = ar.id_persona
                JOIN COMPRA C ON C.ID_COMPRA = AR.ID_ORIGEN
                WHERE AR.ESTADO = 1 and  to_char(AR.FECHA,'mm') = ".$month." AND AR.ID_MODULO = 11 ";
        $query = DB::select($sql);
        return $query;
    }

    public static function showArrangementByEntidadyDepto($id_entidad, $id_depto, $month){
        $sql = "SELECT 
                ar.id_arreglo,
                ar.id_persona, per.paterno ||' '|| per.materno ||', '|| per.nombre AS PERSONA ,
                ar.id_origen, ar.motivo, AR.FECHA, ta.id_tipoarreglo AS ID_TIPO, ta.nombre AS  TIPO_NAME
                FROM ARREGLO AR
                INNER JOIN TIPO_ARREGLO TA ON ar.id_tipoarreglo = ta.id_tipoarreglo
                INNER JOIN MOISES.PERSONA PER ON per.id_persona = ar.id_persona
                WHERE ESTADO = 1 
                AND ID_ENTIDAD=$id_entidad
                AND ID_DEPTO='$id_depto'
                and  to_char(AR.FECHA,'mm') = ".$month." ";
        $query = DB::select($sql);
        return $query;
    }

    public static function deleteArrangement($id_arreglo){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_DELETE_COMPRA_PROVISION(
                :P_ID_ARREGLO,
                :P_ERROR,
                :P_MSG
                );
                end;");
            $stmt->bindParam(':P_ID_ARREGLO', $id_arreglo, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSG', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function deleltePurchasesPending($id_pedido){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_DELETE_COMPRA(
                :P_ID_PEDIDO,
                :P_ERROR,
                :P_MSG
                );
                end;");
            $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSG', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function showPurchasesPending($id_pedido){
        $sql = "SELECT ID_PEDIDO,NVL(ID_COMPRA,0) AS ID_COMPRA FROM PEDIDO_COMPRA WHERE ID_PEDIDO = ".$id_pedido." ";
        $query = DB::select($sql);
        return $query;
    }
    public static function updatePurchasesDetails($id_compra,$id_detalle,$cantidad,$importe){
        $error      = 0;
        $msg_error  = '';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_UPDATE_COMPRA_DETALLE(
                :P_ID_COMPRA,
                :P_ID_DETALLE,
                :P_CANTIDAD,
                :P_IMPORTE
                );
                end;");
            $stmt->bindParam(':P_ID_COMPRA', $id_compra, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DETALLE', $id_detalle, PDO::PARAM_INT);
            $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function listMyPurchases($id_compra){
        $sql = "SELECT 
                        A.ID_COMPRA,A.ID_PROVEEDOR,A.ID_COMPROBANTE,A.SERIE,A.NUMERO,TO_CHAR(FECHA_DOC,'DD/MM/YYYY') AS FECHA_DOC,A.IMPORTE,BASE,IGV,
                        B.NOMBRE,PKG_PURCHASES.FC_RUC(A.ID_PROVEEDOR) RUC
                FROM COMPRA A JOIN MOISES.PERSONA B
                ON A.ID_PROVEEDOR = B.ID_PERSONA
                WHERE ID_COMPRA = ".$id_compra."
                AND ESTADO = '1' ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listMyPurchasesDetails($id_compra){
        $sql = "SELECT 
                        A.ID_COMPRA,B.ID_DETALLE,D.NOMBRE,D.CODIGO,B.CANTIDAD,B.PRECIO,B.BASE,B.IGV,B.IMPORTE 
                FROM COMPRA A JOIN COMPRA_DETALLE B
                ON A.ID_COMPRA = B.ID_COMPRA
                JOIN INVENTARIO_ALMACEN_ARTICULO C
                ON B.ID_ALMACEN = C.ID_ALMACEN
                AND B.ID_ARTICULO = C.ID_ARTICULO
                AND A.ID_ANHO = C.ID_ANHO
                JOIN INVENTARIO_ARTICULO D
                ON C.ID_ARTICULO = D.ID_ARTICULO
                WHERE A.ID_COMPRA = ".$id_compra."
                ORDER BY B.ID_DETALLE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listMyPurchasesSeats($id_compra,$id_empresa){
        $sql = "SELECT 
                A.ID_COMPRA,A.ID_PROVEEDOR,A.ID_VOUCHER,B.ID_ASIENTO,B.FONDO,B.DEPTO,B.CUENTA,B.CUENTA_CTE,B.RESTRICCION,B.IMPORTE,B.IMPORTE,B.AGRUPA,
                (SELECT X.ID_CUENTAEMPRESARIAL FROM CONTA_EMPRESA_CTA X WHERE X.ID_CUENTAAASI = B.CUENTA AND X.ID_RESTRICCION = B.RESTRICCION 
                    AND X.ID_TIPOPLAN = 1 AND X.ID_ANHO = A.ID_ANHO AND X.ID_EMPRESA = ".$id_empresa." AND ROWNUM=1 ) AS CTA,
                B.DESCRIPCION
                FROM COMPRA A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_COMPRA = B.ID_ORIGEN
                WHERE A.ID_COMPRA = $id_compra
                ORDER BY B.ID_ASIENTO ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showMyPurchases($id_compra){
        $sql = "SELECT DISTINCT A.ID_COMPRA,A.ID_ANHO,B.ID_ALMACEN 
                FROM COMPRA A LEFT JOIN COMPRA_DETALLE B
                ON A.ID_COMPRA = B.ID_COMPRA
                WHERE A.ID_COMPRA = ".$id_compra." ";
        $query = DB::select($sql);
        return $query;
    }
    public static function lisPurchasesSummary($id_voucher){
        $sql = "SELECT 
                        FC_CUENTA_DENOMINACIONAL(B.CUENTA) AS CUENTA_N,
                        B.CUENTA,
                        FC_DEPARTAMENTO(DEPTO) AS DEPTO_N,B.DEPTO,
                        SUM(A.BASE_GRAVADA) AS BASE1,SUM(A.IGV_GRAVADO) AS IGV1,SUM(A.BASE_MIXTA) AS BASE2,SUM(A.IGV_MIXTO) AS IGV2,SUM(A.BASE_NOGRAVADA) AS BASE3,SUM(A.IGV_NOGRAVADO) AS IGV3,
                        SUM(CASE  WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) DEBITO, ABS(SUM(CASE  WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                FROM COMPRA A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_COMPRA = B.ID_ORIGEN
                WHERE A.ID_VOUCHER = ".$id_voucher."
                AND A.ESTADO = '1'
                --AND B.CUENTA NOT IN (1141001, 5111011)
                AND (B.CUENTA LIKE '21%' OR B.CUENTA LIKE '5111%10' OR B.CUENTA LIKE '5111%8' OR B.CUENTA LIKE '5111%6')
                GROUP BY B.CUENTA,B.DEPTO
                ORDER BY CUENTA,DEPTO ";
        $query = DB::select($sql);
        return $query;
    }

    // OJO PARA OBSERVAR
    public static function lisPurchasesSummaryD($id_voucher){
        $sql = "SELECT 
                        FC_CUENTA_DENOMINACIONAL(B.CUENTA) AS CUENTA_N,
                        B.CUENTA,
                        FC_DEPARTAMENTO(DEPTO) AS DEPTO_N,B.DEPTO,
                        SUM(A.BASE_GRAVADA) AS BASE1,SUM(A.IGV_GRAVADO) AS IGV1,SUM(A.BASE_MIXTA) AS BASE2,SUM(A.IGV_MIXTO) AS IGV2,SUM(A.BASE_NOGRAVADA) AS BASE3,SUM(A.IGV_NOGRAVADO) AS IGV3,
                        SUM(CASE  WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) DEBITO, ABS(SUM(CASE  WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                FROM COMPRA A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_COMPRA = B.ID_ORIGEN
                WHERE A.ID_VOUCHER = ".$id_voucher."
                AND A.ESTADO = '1'
                -- AND (B.CUENTA LIKE '114%' OR B.CUENTA LIKE '5111%11')
                AND (B.CUENTA LIKE '114%' OR B.CUENTA LIKE '5111%11' OR B.CUENTA LIKE '5111%7'  OR B.CUENTA LIKE '5111%9')
                GROUP BY B.CUENTA,B.DEPTO
                ORDER BY CUENTA,DEPTO ";
        $query = DB::select($sql);
        return $query;
    }

    public static function lisPurchasesSummaryTotalD($id_voucher){
        $sql = "SELECT 
                        SUM(CASE  WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) DEBITO, ABS(SUM(CASE  WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                FROM COMPRA A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_COMPRA = B.ID_ORIGEN
                WHERE A.ID_VOUCHER = ".$id_voucher."
                AND A.ESTADO = '1' 
                --AND B.CUENTA IN (1141001, 5111011)
                -- AND (B.CUENTA LIKE '114%' OR B.CUENTA LIKE '5111%11')
                AND (B.CUENTA LIKE '114%' OR B.CUENTA LIKE '5111%11' OR B.CUENTA LIKE '5111%7'  OR B.CUENTA LIKE '5111%9')";
        $query = DB::select($sql);
        return $query;
    }

    /// 

    public static function lisPurchasesSummaryTotal($id_voucher){
        $sql = "SELECT 
                        SUM(CASE  WHEN B.IMPORTE > 0 THEN B.IMPORTE ELSE 0 END) DEBITO, ABS(SUM(CASE  WHEN B.IMPORTE < 0 THEN B.IMPORTE ELSE 0 END)) AS CREDITO
                FROM COMPRA A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_COMPRA = B.ID_ORIGEN
                WHERE A.ID_VOUCHER = ".$id_voucher."
                AND A.ESTADO = '1' 
                --AND B.CUENTA NOT IN (1141001, 5111011)
                AND (B.CUENTA LIKE '21%' OR B.CUENTA LIKE '5111%10' OR B.CUENTA LIKE '5111%8' OR B.CUENTA LIKE '5111%6') ";
        $query = DB::select($sql);
        return $query;
    }

    // GET proveedor
    public static function lisPurchasesDetailsProv($id_voucher){
        $sql = "SELECT 
                      B.DEPTO,
                      ce.nombre as DEPTO_N,                       
                      B.CUENTA,                       
                      cd.NOMBRE as CUENTA_N ,                     
                      b.cuenta_cte               
                FROM COMPRA A JOIN CONTA_ASIENTO B   ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_COMPRA = B.ID_ORIGEN
                JOIN COMPRA_DETALLE C
                ON A.ID_COMPRA = C.ID_COMPRA
                left join conta_entidad_depto ce
                on ce.id_entidad=a.id_entidad
                and ce.id_depto=b.depto
                left join CONTA_CTA_DENOMINACIONAL cd
                on cd.ID_TIPOPLAN =1
                and cd.ID_CUENTAAASI=b.CUENTA
                and cd.ID_RESTRICCION=b.RESTRICCION
                WHERE A.ID_VOUCHER = ".$id_voucher."
                AND A.ESTADO = '1'
                AND B.CUENTA LIKE '51%'
                group by   B.DEPTO,
                      ce.nombre,                       
                      B.CUENTA,                       
                      cd.NOMBRE ,                     
                      b.cuenta_cte
                ORDER BY B.CUENTA,B.DEPTO,B.CUENTA_CTE";
        $query = DB::select($sql);
        return $query;
    }

    public static function lisPurchasesDetails($id_voucher,$depto, $cuenta,$cuenta_cte){
        /*$sql = "SELECT 
                        FC_CUENTA_DENOMINACIONAL(B.CUENTA) AS CUENTA_N,
                        B.CUENTA,
                        FC_DEPARTAMENTO(B.DEPTO) AS DEPTO_N,B.DEPTO,
                        A.SERIE,A.NUMERO,
                        C.DETALLE,
                        PKG_PURCHASES.FC_RUC (A.ID_PROVEEDOR) AS RUC,
                        FC_NOMBRE_PERSONA (A.ID_PROVEEDOR) AS NOMBRE_PROVEEDOR,
                        (CASE C.ID_CTIPOIGV WHEN 1 THEN C.BASE ELSE 0 END) BASE1,
                        (CASE C.ID_CTIPOIGV WHEN 1 THEN C.IGV ELSE 0 END) IGV1,
                        (CASE C.ID_CTIPOIGV WHEN 2 THEN C.BASE ELSE 0 END) BASE2,
                        (CASE C.ID_CTIPOIGV WHEN 2 THEN C.IGV ELSE 0 END)IGV2,
                        (CASE C.ID_CTIPOIGV WHEN 3 THEN C.BASE ELSE 0 END) BASE3,
                        (CASE C.ID_CTIPOIGV WHEN 3 THEN C.BASE ELSE 0 END) IGV3,
                        (CASE C.ID_CTIPOIGV WHEN 4 THEN C.BASE ELSE 0 END) BASE4,
                        (CASE C.ID_CTIPOIGV WHEN 6 THEN C.BASE ELSE 0 END) OTROS,
                        C.IMPORTE,
                        A.IMPORTE AS TOTAL
                FROM COMPRA A JOIN CONTA_ASIENTO B
                ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                AND A.ID_COMPRA = B.ID_ORIGEN
                JOIN COMPRA_DETALLE C
                ON A.ID_COMPRA = C.ID_COMPRA
                WHERE A.ID_VOUCHER = ".$id_voucher."
                AND A.ESTADO = '1'
                AND  B.cuenta  = ".$cuenta."
                ORDER BY B.CUENTA,B.DEPTO ";
        $query = DB::select($sql);*/
        $where =" AND B.CUENTA LIKE '51%' ";
        if(strlen($cuenta)>0){
            $where= " AND  B.cuenta =  '".$cuenta."' ";
        }
        if(strlen($depto)>0){
            $where.= " AND   B.depto =  '".$depto."' ";
        }
        if(strlen($cuenta_cte)>0){
            $where.= " AND  B.cuenta_cte =  '".$cuenta_cte."' ";
        }
        $sql = "SELECT 
                    cd.NOMBRE as CUENTA_N,
                    B.CUENTA,
                    ce.nombre as DEPTO_N,
                    B.DEPTO,
                    A.SERIE,A.NUMERO,
                    C.DETALLE,
                     COALESCE(pj.id_ruc,PN.NUM_DOCUMENTO) as RUC,
                    (CASE C.ID_CTIPOIGV WHEN 1 THEN C.BASE ELSE 0 END) BASE1,
                    (CASE C.ID_CTIPOIGV WHEN 1 THEN C.IGV ELSE 0 END) IGV1,
                    (CASE C.ID_CTIPOIGV WHEN 2 THEN C.BASE ELSE 0 END) BASE2,
                    (CASE C.ID_CTIPOIGV WHEN 2 THEN C.IGV ELSE 0 END)IGV2,
                    (CASE C.ID_CTIPOIGV WHEN 3 THEN C.BASE ELSE 0 END) BASE3,
                    (CASE C.ID_CTIPOIGV WHEN 3 THEN C.IGV ELSE 0 END) IGV3,
                    (CASE C.ID_CTIPOIGV WHEN 4 THEN C.BASE ELSE 0 END) BASE4,
                    (CASE C.ID_CTIPOIGV WHEN 6 THEN C.BASE ELSE 0 END) OTROS,
                    C.IMPORTE,
                    A.IMPORTE AS TOTAL,
                    B.CUENTA_CTE
            FROM COMPRA A JOIN CONTA_ASIENTO B
            ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
            AND A.ID_COMPRA = B.ID_ORIGEN
            JOIN COMPRA_DETALLE C
            ON A.ID_COMPRA = C.ID_COMPRA
            left join CONTA_CTA_DENOMINACIONAL cd
            on cd.ID_TIPOPLAN =1
            and cd.ID_CUENTAAASI=b.CUENTA
            and cd.ID_RESTRICCION=b.RESTRICCION
            left join conta_entidad_depto ce
            on ce.id_entidad=a.id_entidad
            and ce.id_depto=b.depto
            left join MOISES.PERSONA_JURIDICA pj
            on pj.id_persona=a.ID_PROVEEDOR
            left join MOISES.PERSONA_NATURAL PN
            on PN.id_persona=a.ID_PROVEEDOR
            AND PN.ID_TIPODOCUMENTO=6
            WHERE A.ID_VOUCHER = ".$id_voucher."
            AND A.ESTADO = '1'
            ".$where." 
            ORDER BY B.CUENTA,B.DEPTO,B.CUENTA_CTE,a.id_compra ";
        $query = DB::select($sql);
        return $query;
    }

    public static function lisUsersVoucher($id_voucher){
        $sql = "SELECT
                DISTINCT
                A.ID_PERSONA, B.EMAIL
                FROM COMPRA A JOIN USERS B
                ON A.ID_PERSONA = B.ID
                WHERE ID_VOUCHER = ".$id_voucher."";
        $query = DB::select($sql);
        return $query;
    }
    public static function DebtsToPay($id_entidad,$id_depto,$id_anho,$id_mes,$tipo){
        if($tipo == 'P'){
            $dato = "AND A.ID_COMPROBANTE <> '02' ";
        }else{
            $dato = "AND A.ID_COMPROBANTE = '02' ";
        }
        $sql = "SELECT  DISTINCT A.ID_PROVEEDOR, 
                        --(SELECT X.NOMBRE FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_PROVEEDOR) AS PROVEEDOR,
                        --(SELECT X.ID_RUC FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_PROVEEDOR) AS DOCUMENTO
                        (CASE WHEN A.ID_COMPROBANTE = '02' THEN
                            FC_NOMBRE_PERSONA(A.ID_PROVEEDOR)
                            ELSE (SELECT X.NOMBRE FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_PROVEEDOR UNION ALL 
                                SELECT X.NOMBRE FROM MOISES.VW_PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_PROVEEDOR AND ID_TIPODOCUMENTO = 6) 
                        END) AS PROVEEDOR,
                        (CASE WHEN A.ID_COMPROBANTE = '02' THEN
                            PKG_PURCHASES.FC_RUC(A.ID_PROVEEDOR)
                            ELSE (SELECT X.ID_RUC FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_PROVEEDOR UNION ALL 
                                SELECT X.NUM_DOCUMENTO FROM MOISES.VW_PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_PROVEEDOR AND ID_TIPODOCUMENTO = 6)
                        END) AS DOCUMENTO
                FROM VW_PURCHASES_MOV A
                WHERE A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_ANHO = ".$id_anho."
                AND A.ID_MES <= ".$id_mes."
                ".$dato."
                HAVING SUM (A.IMPORTE)+NVL(SUM(A.IMPORTE_ME),0) <> 0
                GROUP BY A.ID_COMPRA,A.ID_PROVEEDOR,A.SERIE,A.NUMERO,A.ID_COMPROBANTE
                ORDER BY PROVEEDOR ";
        $query = DB::select($sql);
        return $query;
    }
    public static function lisDebtsToPay($id_entidad,$id_depto,$id_anho,$id_mes,$id_proveedor,$tipo){
        if($tipo == 'P'){
            $dato = "AND A.ID_COMPROBANTE <> '02' ";
        }else{
            $dato = "AND A.ID_COMPROBANTE = '02' ";
        }
        $sql = "SELECT  
                        A.ID_COMPRA,
                        A.ID_PROVEEDOR,
                        A.ID_COMPROBANTE,
                        nvl((SELECT TO_CHAR(X.FECHA_DOC,'DD/MM/YYYY') FROM COMPRA X WHERE X.ID_COMPRA = A.ID_COMPRA and X.ID_PROVEEDOR=A.ID_PROVEEDOR),
                        (SELECT TO_CHAR(X.FECHA_DOC,'DD/MM/YYYY') FROM COMPRA_SALDO X WHERE X.ID_SALDO = A.ID_COMPRA and X.ID_PROVEEDOR=A.ID_PROVEEDOR) )AS FECHA_DOC,
                        nvl((SELECT TO_CHAR(X.FECHA_PROVISION,'DD/MM/YYYY') FROM COMPRA X WHERE X.ID_COMPRA = A.ID_COMPRA and X.ID_PROVEEDOR=A.ID_PROVEEDOR),
                        (SELECT  TO_CHAR(X.FECHA_PROVISION,'DD/MM/YYYY')  FROM COMPRA_SALDO X WHERE X.ID_SALDO = A.ID_COMPRA and X.ID_PROVEEDOR=A.ID_PROVEEDOR))AS FECHA_PROVISION,
                        A.SERIE,
                        A.NUMERO,
                        (SELECT Y.EMAIL FROM COMPRA X JOIN USERS Y ON X.ID_PERSONA = Y.ID WHERE X.ID_COMPRA = A.ID_COMPRA  AND X.ID_ENTIDAD = ".$id_entidad."  AND X.ID_DEPTO = '".$id_depto."') USEER,
                        SUM (A.IMPORTE) IMPORTE,
                        NVL(SUM (A.IMPORTE_ME),0) IMPORTE_ME,
                        SUM (A.IMPORTE_DOC) IMPORTE_DOC
                FROM VW_PURCHASES_MOV A
                WHERE A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_ANHO = ".$id_anho."
                AND A.ID_MES <= ".$id_mes."
                AND A.ID_PROVEEDOR = ".$id_proveedor."
                ".$dato."
                HAVING SUM (A.IMPORTE)+NVL(SUM(A.IMPORTE_ME),0) <> 0
                GROUP BY A.ID_COMPRA,A.ID_PROVEEDOR,A.ID_COMPROBANTE,A.SERIE,A.NUMERO
                ORDER BY A.ID_COMPRA ";
        $query = DB::select($sql);
        return $query;
    }
    public static function deleltePurchasesPreProvision($id_entidad,$id_depto, $id_pedido){
        $error      = 0;
        $msg_error  = '';
        DB::beginTransaction();
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PURCHASES.SP_DELETE_PRE_PROVISION(
                :P_ID_ENTIDAD,
                :P_ID_DEPTO,
                :P_ID_PEDIDO,
                :P_ERROR,
                :P_MSG
                );
                end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PEDIDO', $id_pedido, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSG', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            if($error === 1){
                throw new Exception($msg_error, 1);
            }
            DB::commit();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        }catch(Exception $e){
            DB::rollback();
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    
    public static function getUrlByDeleteFile($id_pedido) {
        return DB::table('eliseo.pedido_file')
        ->select('url')
        ->where('id_pedido','=', $id_pedido)
        ->get();
    }


    public static function createdVoucher($id_entidad,$id_depto,$id_anho,$id_mes,$fecha,$id_tipoasiento,$id_tipovoucher,$id_user){
        $id_voucher = 0;
        $numero = 0;
        // $fecha = '00/00/0000';
        $error = 0;
        $msg  = '';
        $id_parent = null;
        $activo = 'S';
        try {
            for($x=1 ; $x<=200 ; $x++){
                $msg .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ACCOUNTING.SP_CREAR_VOUCHER(:P_ID_ENTIDAD,:P_ID_DEPTO,:P_ID_ANHO,:P_ID_MES,:P_FECHA,:P_ID_TIPOASIENTO,:P_ID_TIPOVOUCHER,:P_ID_SEAT_PARENT,:P_ACTIVO,:P_ID_PERSONA,:P_ID_VOUCHER);end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
            $stmt->bindParam(':P_FECHA', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPOASIENTO', $id_tipoasiento, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPOVOUCHER', $id_tipovoucher, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_SEAT_PARENT', $id_parent, PDO::PARAM_INT);
            $stmt->bindParam(':P_ACTIVO', $activo, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_VOUCHER', $id_voucher, PDO::PARAM_INT);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg;
            $objReturn['id_voucher']   = $id_voucher;
            return $objReturn;

        }catch(Exception $e){
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function getProyecto($id_entidad, $id_depto)
    {
        
        $sql = DB::table('eliseo.proyecto')
            ->select('id_proyecto', 'nombre', 'estado')
            ->where('id_entidad', $id_entidad)
            ->where('id_depto', $id_depto)
            ->where('estado', "1")
            ->get();
        return $sql;
    }
    public static function getVale($id_entidad, $id_depto, $id_anho, $request)
    {
        // dd($id_entidad, $id_depto, $id_anho);
        $sql = DB::table('eliseo.caja_vale')
            ->where('id_entidad', $id_entidad)
            ->where('id_depto', $id_depto)
            ->where('id_anho', $id_anho)
            ->where('id_empleado', $request->id_persona)
            ->where('id_tipovale', "1")
            ->where('estado', "1")
            ->whereraw("id_voucher is not null ")
            ->whereraw("id_vale not in (SELECT ID_VALE FROM VW_VALES_SALDO WHERE ID_EMPLEADO = $request->id_persona AND IMPORTE <= 0) ")
            ->where('estado', "1")
            ->distinct()
            ->select('id_vale', 'detalle', 'importe',DB::raw("'NRO VALE: '||NRO_VALE||' - S/. '||IMPORTE||' - '||FC_NOMBRE_PERSONA(ID_EMPLEADO)||' - '||TO_CHAR(FECHA,'DD/MM/YYYY') AS DATA_VALE"))
            ->get();
        return $sql;
    }
    public static function addProyectoCompra($data){
        try{
            $result = DB::table('proyecto_compra')->insert($data);
            if($result){
                $result = [
                    "success"   => true,
                    "data"   => $data["id_pcompra"],
                    "message"   => "OK"
                ];
            }else{
                $result = [
                    "success"   => false,
                    "data"   =>[], 
                    "message"   => "Error."
                ];
            }
        }catch(Exception $e){
            $result = [
                "success"   => false,
                "data"   =>[], 
                "message"   => $e->getMessage()
            ];
        }
        return $result;
    }
    public static function getDetalleCompra($id_entidad, $request)
    {
        $id_anho        = $request->id_anho;
        $id_mes         = $request->id_mes;
        $id_voucher     = $request->id_voucher;
        $id_depto       = $request->id_depto;
        $ruc            = $request->ruc;
        $q = DB::table('eliseo.compra a');
            $q->join('moises.persona b', 'a.id_proveedor', '=', 'b.id_persona');
            $q->leftjoin('moises.persona_juridica c', 'a.id_proveedor', '=', 'c.id_persona');
            $q->leftjoin('eliseo.conta_asiento d', 'a.id_compra', '=', DB::raw("d.id_origen and a.id_tipoorigen = 3 and a.importe > 0")); // solo para filtrar
            $q->where('a.id_entidad', $id_entidad);
            // ->where('id_depto', $id_depto)
            $q->where('a.id_anho', $id_anho);
            $q->where('a.id_mes', $id_mes);
            if (!empty($id_voucher)) {
                $q->where('a.id_voucher', $id_voucher);
            }
            if (!empty($id_depto)) {
                $q->whereraw(ComunData::fnBuscar('d.depto').' like '.ComunData::fnBuscar("'%".$id_depto."%'"));
            }
            if (!empty($ruc)) {
                $q->whereraw(ComunData::fnBuscar('c.id_ruc').' like '.ComunData::fnBuscar("'%".$ruc."%'"));
            }
            $q->where('a.estado', 1);
            $q->distinct();
            $q->select('a.id_compra', DB::raw("FC_LOTE_VOUCHER(a.id_voucher) as rc"),DB::raw("FC_LOTE_PAGO(a.id_compra) as mb"),
            db::raw("to_char(a.fecha_provision,'dd/mm/yyyy') as fecha_provision, to_char(a.fecha_doc,'dd/mm/yyyy') as fecha_doc "),
            'a.serie', 'a.numero', 'a.id_proveedor', 'a.id_voucher', 'b.nombre', 'c.id_ruc', 'a.id_depto', 'a.importe', 
            DB::raw("COALESCE(A.BASE_GRAVADA * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS BASE_GRAVADA
            ,COALESCE(A.IGV_GRAVADO * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS IGV_GRAVADA
            ,COALESCE(A.BASE_MIXTA * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS BASE_MIXTA
            ,COALESCE(A.IGV_MIXTO * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS IGV_MIXTO
            ,COALESCE(A.BASE_NOGRAVADA * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS BASE_NOGRAVADA
            ,COALESCE(A.IGV_NOGRAVADO * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS IGV_NOGRAVADO
            ,COALESCE(A.BASE_SINCREDITO * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS COMPRAS_NO_GRABADAS
            ,NULL ISC
            ,COALESCE(A.OTROS * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS OTROS_TRIBUTOS
            ,COALESCE(A.IMPORTE * CASE WHEN A.ID_COMPROBANTE LIKE '07' THEN -1 WHEN A.ID_COMPROBANTE LIKE '87' THEN -1 ELSE 1 END,0) AS IMPORTE_TOTAL"));
            $q->orderby('a.id_compra');
            $sql = $q->get();
            $data = array();
            foreach($sql as $datos) {
                $item = (object)$datos;
                $delta = array();
                $delta['id_compra'] = $item->id_compra;
                $delta['fecha_provision'] = $item->fecha_provision;
                $delta['fecha_doc'] = $item->fecha_doc;
                $delta['serie'] = $item->serie;
                $delta['numero'] = $item->numero;
                $delta['id_proveedor'] = $item->id_proveedor;
                $delta['id_voucher'] = $item->id_voucher;
                $delta['nombre'] = $item->nombre;
                $delta['id_ruc'] = $item->id_ruc;
                $delta['id_depto'] = $item->id_depto;
                $delta['importe'] = $item->importe;
                $delta['base_gravada'] = $item->base_gravada;
                $delta['igv_gravada'] = $item->igv_gravada;
                $delta['base_mixta'] = $item->base_mixta;
                $delta['igv_mixto'] = $item->igv_mixto;
                $delta['base_nogravada'] = $item->base_nogravada;
                $delta['igv_nogravado'] = $item->igv_nogravado;
                $delta['compras_no_grabadas'] = $item->compras_no_grabadas;
                $delta['isc'] = $item->isc;
                $delta['otros_tributos'] = $item->otros_tributos;
                $delta['importe_total'] = $item->importe_total;
                $delta['rc'] = $item->rc;
                $delta['mb'] = $item->mb;
                $delta['detalle'] = PurchasesData::detailsCompra($item->id_compra);
                $delta['cant_detalle'] = count(PurchasesData::detailsCompra($item->id_compra));
                $delta['asiento_compra'] = PurchasesData::asientoC($item->id_compra, $id_entidad, $id_depto);
                array_push($data, $delta);
            }
        return $data;
    }
    private static function detailsCompra($id_compra) {
        $sql = db::table('eliseo.compra_detalle a')
                    ->where('a.id_compra', $id_compra)
                    ->select('*')
                    ->orderBy('a.id_detalle')
                    ->get();
        $count = count($sql);
        if ($count<=0) { //para que en el front pueda renderizar sin ningun problema, cuando el detalle es 0
            $sql = array();
            $dat = [
            'base'=> '-',
            'cantidad'=> '-',
            'costo_vinculado'=> '-',
            'detalle'=> '-',
            'es_costo_vinculado'=> '-',
            'estado'=> '-',
            'fecha_vencimiento'=> '-',
            'id_almacen'=> '-',
            'id_articulo'=> '-',
            'id_compra'=> '-',
            'id_ctipoigv'=> '-',
            'id_detalle'=> '-',
            'id_dinamica'=> '-',
            'id_tipoigv'=> '-',
            'igv'=> '-',
            'importe'=> '-',
            'orden'=> '-',
            'precio'=> '-'
            ];
            array_push($sql, $dat);
        }
        return $sql;
    }
    private static function asientoC($id_compra, $id_entidad, $id_depto) {
        $q = db::table('eliseo.conta_asiento a');
                    $q->where('a.id_origen', $id_compra);
                    $q->where('a.id_tipoorigen', 3);
                    $q->where('a.importe', '>', 0);
                    if (!empty($id_depto)) {
                        $q->whereraw(ComunData::fnBuscar('a.depto').' like '.ComunData::fnBuscar("'%".$id_depto."%'"));
                    }
                    $q->select('a.cuenta','a.cuenta_cte','a.restriccion','a.depto','a.importe', 'a.descripcion',
                    DB::raw("FC_CUENTA(CUENTA) AS NOMBRE_CTA, FC_NAMESDEPTO(".$id_entidad.",DEPTO) AS NOMBRRE_DEPTO"));
                    $q->orderby('a.id_asiento');
           $sql =   $q->get();
        $count = count($sql);
        return $sql;
    }
    public static function asientoCompra($id_compra) {
        $sql = db::table('eliseo.conta_asiento a')
                    ->where('a.id_origen', $id_compra)
                    ->where('a.id_tipoorigen', 3)
                    ->select('a.cuenta','a.cuenta_cte','a.restriccion','a.depto','a.importe', 'a.descripcion',
                    db::raw("(CASE WHEN A.IMPORTE > 0 THEN A.IMPORTE ELSE 0 END) AS DEBITO,
                    (ABS(CASE WHEN A.IMPORTE < 0 THEN A.IMPORTE ELSE 0 END)) AS CREDITO"))
                    ->orderby('a.id_asiento')
                    ->get();
        $count = count($sql);
        return $sql;
    }
    public static function showArrangementPurchases($id_compra){
        $query = "SELECT 
                        ID_COMPRA, PKG_PURCHASES.FC_PROVEEDOR(ID_PROVEEDOR) AS PROVEEDOR,PKG_PURCHASES.FC_RUC(ID_PROVEEDOR) AS RUC,
                        SERIE,NUMERO,FECHA_PROVISION,FECHA_DOC,IMPORTE 
                FROM COMPRA WHERE ID_COMPRA = ".$id_compra." ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showArrangementSeatsPurchases($id_compra){
        $query = "SELECT 
                        ID_ASIENTO,ID_TIPOORIGEN,ID_ORIGEN,FONDO,DEPTO,CUENTA,CUENTA_CTE,RESTRICCION,IMPORTE,DESCRIPCION,IMPORTE,IMPORTE_ME,VOUCHER AS ID_VOUCHER
                FROM CONTA_ASIENTO 
                WHERE ID_TIPOORIGEN = 3 
                AND ID_ORIGEN = ".$id_compra." ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getProviderAccount($params)
    {
        return DB::table("ELISEO.VW_PURCHASES_CUENTA_PROVEEDOR PURCHASES")
            ->select("proveedor", "id_ruc", "operacion", "lote", "numero", "importe","fecha","debito", "credito", "tipo_operacion")
            ->where("PURCHASES.ID_ENTIDAD", $params['id_entidad'])
            ->where("PURCHASES.ID_DEPTO", $params['id_depto'])
            ->where("PURCHASES.ID_ANHO", $params['id_anho'])
            ->where("PURCHASES.ID_MES", $params['id_mes'])
            ->where("PURCHASES.CUENTA", $params['cuenta'])
            ->where("PURCHASES.TIPO_OPERACION", $params['tipo'])
            ->paginate($params['per_page']);
    }

    public static function getProviderAccountExport($params)
    {
        return DB::table("ELISEO.VW_PURCHASES_CUENTA_PROVEEDOR PURCHASES")
            ->select("id_entidad","id_depto","id_anho","id_mes","fecha","cuenta","proveedor", "id_ruc", "operacion", "lote", "numero", "importe","debito", "credito", "tipo_operacion")
            ->where("PURCHASES.ID_ENTIDAD", $params['id_entidad'])
            ->where("PURCHASES.ID_DEPTO", $params['id_depto'])
            ->where("PURCHASES.ID_ANHO", $params['id_anho'])
            ->where("PURCHASES.ID_MES", $params['id_mes'])
            ->where("PURCHASES.CUENTA", $params['cuenta'])
            ->get();
    }

    public static function getOrdenCompra($id_order){
        return DB::select("SELECT DISTINCT p.NOMBRE AS PROVEEDOR,r.ID_RUC AS RUC,pt.NUM_TELEFONO,
        co.LUGAR_ENTREGA,TO_CHAR(co.FECHA_PEDIDO,'DD/MM/YYYY') as FECHA_PEDIDO,TO_CHAR(co.FECHA_ENTREGA,'DD/MM/YYYY') AS FECHA_ENTREGA,oa.NOMBRE AS AREA,
        co.CON_IGV,co.ES_CREDITO,co.CUOTAS ,co.DIAS_CREDITO,
        mp.NOMBRE AS MEDIO_PAGO,co.NUMERO AS SERIE
        FROM COMPRA_ORDEN co 
        JOIN MOISES.PERSONA p ON (co.ID_PROVEEDOR = p.ID_PERSONA) 
        JOIN MOISES.VW_PERSONA_JURIDICA r ON (p.ID_PERSONA = R.ID_PERSONA)
        JOIN PERSONA_TELEFONO pt ON (co.ID_PERSONA = pt.ID_PERSONA)
        JOIN ORG_SEDE_AREA sa ON (co.ID_SEDEAREA = sa.ID_SEDEAREA) 
        JOIN ORG_AREA oa ON (sa.ID_AREA = oa.ID_AREA)
        JOIN MEDIO_PAGO mp ON (co.ID_MEDIOPAGO = mp.ID_MEDIOPAGO)
        where co.ID_ORDEN = $id_order");
    }  
    
    public static function getDetalleOrdenCompra($id_order){
        return DB::select("SELECT * FROM COMPRA_ORDEN_DETALLE cod WHERE ID_ORDEN = $id_order");
    }

    public static function getTotalesDetalleOrdenCompra($id_order){
        return DB::select("SELECT SUM(cod.TOTAL) AS TOTAL,co.CON_IGV  FROM 
        COMPRA_ORDEN co JOIN COMPRA_ORDEN_DETALLE cod
        ON (co.ID_ORDEN = cod.ID_ORDEN)
        WHERE co.ID_ORDEN = $id_order
        GROUP BY co.ID_ORDEN,co.CON_IGV");
    }
}
