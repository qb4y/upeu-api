<?php

namespace App\Http\Data\HumanTalent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use App\Http\Data\HumanTalent\SignatureData;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class PaymentsData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function anhoPayments($id_entidad, $id_persona)
    {
        /*$sql = "select
                    id_anho
            from aps_planilla_boleta
            where id_entidad=".$id_entidad."
            and id_persona=".$id_persona."
            group by id_anho
            order by id_anho";*/
        $sql = "select
                    id_anho
            from aps_planilla_boleta
            where id_persona=" . $id_persona . "
            group by id_anho
            order by id_anho";
        $query = DB::select($sql);

        return $query;
    }
    public static function listPaymentTicket($id_entidad, $id_anho, $id_persona)
    {

        $url_pdf = PaymentsData::ruta_url(url('humantalent/payments-tickets-worker-display'));
        //$url_dw= PaymentsData::ruta_url(url('humantalent/payments-tickets-worker-download'));
        $url_dw = PaymentsData::ruta_url(url('boletas'));
        //$id_persona= 7708;
        /*$sql = "SELECT
                    A.ID_ANHO,
                    A.ID_MES,
                    A.ID_PERSONA,
                    A.ID_CONTRATO,
                    X.CLAVE,
                    x.ARCHIVO,
                    M.NOMBRE as MES,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FIRMA,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS AVISO,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS REVISADO,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS DESCARGADO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FFIRMA,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS FAVISO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS FREVISADO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS FDESCARGADO,
                    FC_GTH_VALIDAR_VER_BOLETA(X.ID_GESTION,A.ID_PERSONA,A.ID_CONTRATO) AS BOLETA,
                    P.NOMBRE,
                    P.PATERNO,
                    P.MATERNO,
                    FC_GTH_OBTENER_EMAIL(A.ID_PERSONA) AS CORREO,
                    X.CORREO AS CORREO1,
                    FC_GTH_OBTENER_CELULAR(A.ID_PERSONA) AS CELULAR,
                    x.CELULAR AS CELULAR1,
                    '".$url_pdf."' as urls,
                    '".$url_dw."/'||x.ARCHIVO  as urls_dw
                FROM APS_PLANILLA A INNER JOIN CONTA_MES M
                ON A.ID_MES=M.ID_MES
                INNER JOIN MOISES.PERSONA P
                ON A.ID_PERSONA=P.ID_PERSONA
                INNER JOIN APS_PLANILLA_BOLETA X
                ON A.ID_ENTIDAD=X.ID_ENTIDAD
                AND A.ID_ANHO=X.ID_ANHO
                AND A.ID_MES=X.ID_MES
                AND A.ID_PERSONA=X.ID_PERSONA
                AND A.ID_CONTRATO=X.ID_CONTRATO
                WHERE A.ID_ENTIDAD=".$id_entidad."
                AND A.ID_ANHO=".$id_anho."
                AND A.ID_PERSONA= ".$id_persona."
                ORDER BY A.ID_MES DESC,A.ID_ENTIDAD ";*/
        $sql = "SELECT
                    A.ID_ANHO,
                    A.ID_MES,
                    A.ID_PERSONA,
                    A.ID_CONTRATO,
                    X.CLAVE,
                    x.ARCHIVO,
                    M.NOMBRE as MES,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FIRMA,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS AVISO,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS REVISADO,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS DESCARGADO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FFIRMA,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS FAVISO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS FREVISADO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS FDESCARGADO,
                    FC_GTH_VALIDAR_VER_BOLETA(X.ID_GESTION,A.ID_PERSONA,A.ID_CONTRATO) AS BOLETA,
                    P.NOMBRE,
                    P.PATERNO,
                    P.MATERNO,
                    FC_GTH_OBTENER_EMAIL(A.ID_PERSONA) AS CORREO,
                    X.CORREO AS CORREO1,
                    FC_GTH_OBTENER_CELULAR(A.ID_PERSONA) AS CELULAR,
                    x.CELULAR AS CELULAR1,
                    '" . $url_pdf . "' as urls,
                    '" . $url_dw . "/'||x.ARCHIVO  as urls_dw,
                    E.NOMBRE as ENTIDAD
                FROM APS_PLANILLA A INNER JOIN CONTA_MES M
                ON A.ID_MES=M.ID_MES
                INNER JOIN MOISES.PERSONA P
                ON A.ID_PERSONA=P.ID_PERSONA
                INNER JOIN CONTA_ENTIDAD E
                ON E.ID_ENTIDAD=A.ID_ENTIDAD
                INNER JOIN APS_PLANILLA_BOLETA X
                ON A.ID_ENTIDAD=X.ID_ENTIDAD
                AND A.ID_ANHO=X.ID_ANHO
                AND A.ID_MES=X.ID_MES
                AND A.ID_PERSONA=X.ID_PERSONA
                AND A.ID_CONTRATO=X.ID_CONTRATO
                WHERE A.ID_ANHO=" . $id_anho . "
                AND A.ID_PERSONA= " . $id_persona . "
                ORDER BY A.ID_MES DESC,A.ID_ENTIDAD  ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addPaymentTicket($id_entidad, $id_anho, $id_mes, $id_persona, $id_contrato, $id_proceso, $id_depto, $clave, $archivo)
    {


        $query = "SELECT
                        COALESCE(MAX(ID_GESTION),0)+1 ID_GESTION
                FROM APS_PLANILLA_BOLETA ";
        $oQuery = DB::select($query);

        $id_gestion = 0;
        foreach ($oQuery as $id) {
            $id_gestion = $id->id_gestion;
        }

        DB::table('APS_PLANILLA_BOLETA')->insert(
            array(
                'ID_GESTION' => $id_gestion,
                'ID_ENTIDAD' => $id_entidad,
                'ID_ANHO' => $id_anho,
                'ID_MES' => $id_mes,
                'ID_PERSONA' => $id_persona,
                'ID_CONTRATO' => $id_contrato,
                'ID_PROCESO' => $id_proceso,
                'ID_DEPTO' => $id_depto,
                'CLAVE' => $clave,
                'FECHA' => DB::raw('SYSDATE'),
                'ARCHIVO' => $archivo
            )
        );
        if ($id_gestion > 0) {
            $pdo = DB::getPdo();
            $id_proceso = 1;

            $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT.SP_PLANILLA_BOLETA_PROCESO(
                                            :P_ID_PROCESO,
                                            :P_ID_GESTION
                                         ); end;");
            $stmt->bindParam(':P_ID_PROCESO', $id_proceso, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_GESTION', $id_gestion, PDO::PARAM_INT);

            $stmt->execute();
        }
        return $id_gestion;
    }
    public static function listProcessTicket($id_entidad, $id_anho, $id_mes)
    {
        $sql = "SELECT
                W.ID_ENTIDAD,
                W.ID_ANHO,
                W.ID_MES,
                W.ID_DEPTO,
                W.ID_DEPTO||' - '||D.NOMBRE as departamento,
                D.NOMBRE,
                COUNT(W.ID_PERSONA) AS CANTIDAD,
                SUM(FIRMA) AS FIRMA,
                SUM(AVISO) AS AVISO,
                SUM(REVISADO) AS REVISADO,
                SUM(DESCARGADO) AS DESCARGADO,
                SUM(SINCORREO) AS SINCORREO,
                count(distinct(depto_area)) as ndepto
              FROM(
                SELECT
                  A.ID_ENTIDAD,
                  A.ID_PERSONA,
                  A.ID_CONTRATO,
                  A.ID_ANHO,
                  A.ID_MES,
                  SUBSTR(A.ID_DEPTO,1,1) AS ID_DEPTO,A.ID_DEPTO as depto_area,
                  (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FIRMA,
                  (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS AVISO,
                  (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS REVISADO,
                  (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS DESCARGADO,
                  (SELECT DECODE(COUNT(Y.ID_PERSONA),0,1,0) FROM MOISES.PERSONA_VIRTUAL Y  WHERE Y.ID_PERSONA = a.ID_PERSONA ) AS SINCORREO
                FROM APS_PLANILLA A
                LEFT JOIN APS_PLANILLA_BOLETA X
                ON A.ID_ENTIDAD=X.ID_ENTIDAD
                AND A.ID_ANHO=X.ID_ANHO
                AND A.ID_MES=X.ID_MES
                AND A.ID_PERSONA=X.ID_PERSONA
                AND A.ID_CONTRATO=X.ID_CONTRATO
                WHERE A.ID_ENTIDAD=" . $id_entidad . "
                AND A.ID_ANHO=" . $id_anho . "
                AND A.ID_MES=" . $id_mes . "
              )W,CONTA_ENTIDAD_DEPTO D
              WHERE W.ID_ENTIDAD=D.ID_ENTIDAD
              AND W.ID_DEPTO=D.ID_DEPTO
              GROUP BY W.ID_ENTIDAD,
                W.ID_ANHO,
                W.ID_MES,
                W.ID_DEPTO,
                D.NOMBRE
              ORDER BY W.ID_DEPTO";
        $query = DB::select($sql);
        return $query;
    }
    public static function listProcessTicketArea($id_entidad, $id_anho, $id_mes, $id_depto)
    {
        $sql = "SELECT
                W.ID_ENTIDAD,
                W.ID_ANHO,
                W.ID_MES,
                W.ID_DEPTO,
                W.ID_DEPTO||' - '||D.NOMBRE as departamento,
                D.NOMBRE,
                COUNT(W.ID_PERSONA) AS CANTIDAD,
                SUM(FIRMA) AS FIRMA,
                SUM(AVISO) AS AVISO,
                SUM(REVISADO) AS REVISADO,
                SUM(DESCARGADO) AS DESCARGADO,
                SUM(SINCORREO) AS SINCORREO
              FROM(
                SELECT
                  A.ID_ENTIDAD,
                  A.ID_PERSONA,
                  A.ID_CONTRATO,
                  A.ID_ANHO,
                  A.ID_MES,
                   A.ID_DEPTO,
                  (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FIRMA,
                  (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS AVISO,
                  (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS REVISADO,
                  (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS DESCARGADO,
                  (SELECT DECODE(COUNT(Y.ID_PERSONA),0,1,0) FROM MOISES.PERSONA_VIRTUAL Y  WHERE Y.ID_PERSONA = a.ID_PERSONA ) AS SINCORREO
                FROM APS_PLANILLA A
                LEFT JOIN APS_PLANILLA_BOLETA X
                ON A.ID_ENTIDAD=X.ID_ENTIDAD
                AND A.ID_ANHO=X.ID_ANHO
                AND A.ID_MES=X.ID_MES
                AND A.ID_PERSONA=X.ID_PERSONA
                AND A.ID_CONTRATO=X.ID_CONTRATO
                WHERE A.ID_ENTIDAD=" . $id_entidad . "
                AND A.ID_ANHO=" . $id_anho . "
                AND A.ID_MES=" . $id_mes . "
                AND A.ID_DEPTO LIKE '" . $id_depto . "%'
              )W,CONTA_ENTIDAD_DEPTO D
              WHERE W.ID_ENTIDAD=D.ID_ENTIDAD
              AND W.ID_DEPTO=D.ID_DEPTO
              GROUP BY W.ID_ENTIDAD,
                W.ID_ANHO,
                W.ID_MES,
                W.ID_DEPTO,
                D.NOMBRE
              ORDER BY D.NOMBRE";
        $query = DB::select($sql);
        return $query;
    }
    public static function listProcessTicketPerson($id_entidad, $id_anho, $id_mes, $id_depto)
    {
        $q = DB::table('aps_planilla as a');
        $q->join('moises.persona as p', 'p.id_persona', '=', 'a.id_persona');
        $q->leftjoin('aps_planilla_boleta as x', 'x.id_entidad', '=', DB::raw("a.id_entidad and x.id_anho=a.id_anho and x.id_mes=a.id_mes and x.id_persona=a.id_persona and x.id_contrato=a.id_contrato"));
        $q->leftjoin('conta_entidad_depto as d', 'd.id_entidad', '=', DB::raw("a.id_entidad and d.id_depto=SUBSTR(a.id_depto,1,1)"));
        $q->leftjoin('conta_entidad_depto as dd', 'dd.id_entidad', '=', DB::raw("a.id_entidad and dd.id_depto=a.id_depto"));
        $q->leftjoin('moises.persona_documento as pd', 'pd.id_persona', '=', DB::raw("a.id_persona and pd.id_tipodocumento in(1,4)"));
        $q->leftjoin('users as u', 'u.id', '=', 'a.id_persona');
        $q->where('a.id_entidad', $id_entidad);
        $q->where('a.id_anho', $id_anho);
        $q->where('a.id_mes', $id_mes);
        $q->whereraw("a.id_depto like '" . $id_depto . "%'");
        $q->select(
            'p.paterno',
            'p.materno',
            'p.nombre',
            'pd.num_documento',
            'd.nombre as deptopadre',
            'dd.nombre as depto',
            'u.email as usuario',
            'a.id_entidad',
            'a.id_persona',
            'a.id_contrato',
            'a.id_anho',
            'a.id_mes',
            'a.id_depto',
            DB::raw(
                "
                fc_gth_obtener_email(a.id_persona) as email,
                  fc_gth_obtener_celular(a.id_persona) as celular,
                  (select decode(count(y.id_persona),0,'No','Si') from moises.persona_virtual y  where y.id_persona = a.id_persona ) as correo,
                  (select decode(count(x.id_proceso),0,'No','Si') from aps_planilla_boleta_proceso y  where y.id_gestion = x.id_gestion  and y.id_proceso = 1 ) as firma,
                  (select decode(count(x.id_proceso),0,'No','Si') from aps_planilla_boleta_proceso y  where y.id_gestion = x.id_gestion  and y.id_proceso = 2 ) as aviso,
                  (select decode(count(x.id_proceso),0,'No','Si') from aps_planilla_boleta_proceso y  where y.id_gestion = x.id_gestion  and y.id_proceso = 3 ) as revisado,
                  (select decode(count(x.id_proceso),0,'No','Si') from aps_planilla_boleta_proceso y  where y.id_gestion = x.id_gestion  and y.id_proceso = 4 ) as descargado,
                  substr(a.id_depto,1,1) as id_depto_padre
                "
            )
        );
        $q->orderBy('id_depto_padre', 'asc');
        $q->orderBy('a.id_depto', 'asc');
        $q->orderBy('p.paterno', 'asc');
        $q->orderBy('p.materno', 'asc');
        $q->orderBy('p.nombre', 'asc');
        $data = $q->get(); // $q->paginate(20);
        return $data;
    }
    public static function deletePaymentTicket($id_gestion)
    {


        $return = [
            'nerror' => 1,
            'msgerror' => 'No se puede eliminar'
        ];
        $boleta = DB::table('APS_PLANILLA_BOLETA')
            ->where('id_gestion', $id_gestion)
            ->first();

        if (!empty($boleta)) {
            $query = "DELETE FROM APS_PLANILLA_BOLETA_PROCESO
                    WHERE ID_GESTION ='" . $id_gestion . "'";
            DB::delete($query);


            $query = "DELETE FROM APS_PLANILLA_BOLETA
                    WHERE id_gestion = '" . $id_gestion . "'";
            DB::delete($query);

            $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

            $mes = $dmeses[$boleta->id_mes];
            $file = 'boletapago/'.$boleta->id_entidad.'/'.$boleta->id_anho.'/'.$mes.'/'.$boleta->archivo;

            $status = Storage::disk('minio-lamb')->exists($file);
            if ($status) {
                Storage::disk('minio-lamb')->delete($file);
            }
            $return = [
                'nerror' => 0,
                'msgerror' => 'Se ha eliminado correctamente'
            ];
        } else {
            $return = [
                'nerror' => 1,
                'msgerror' => 'No se ha encontrado información'
            ];
        }
        return $return;
    }
    /*
    public static function deletePaymentTicket($id_gestion)
    {


        $return = [
            'nerror' => 1,
            'msgerror' => 'No se puede eliminar'
        ];
        $boleta = DB::table('APS_PLANILLA_BOLETA')
            ->where('id_gestion', $id_gestion)
            ->first();

        if (!empty($boleta)) {
            $query = "DELETE FROM APS_PLANILLA_BOLETA_PROCESO
                    WHERE ID_GESTION ='" . $id_gestion . "'";
            DB::delete($query);


            $query = "DELETE FROM APS_PLANILLA_BOLETA
                    WHERE id_gestion = '" . $id_gestion . "'";
            DB::delete($query);

            $return = PaymentsData::directorioBoleta($boleta->id_entidad, $boleta->id_anho, $boleta->id_mes);

            if ($return['nerror'] == 0) {
                if (strlen($boleta->archivo) > 0) {
                    $archivo = $return['directorio'] . "/" . $boleta->archivo;
                    if (file_exists($archivo)) {
                        unlink($archivo);
                    }
                }
            }
            $return = [
                'nerror' => 0,
                'msgerror' => 'Se ha eliminado correctamente'
            ];
        } else {
            $return = [
                'nerror' => 1,
                'msgerror' => 'No se ha encontrado información'
            ];
        }
        return $return;
    }
    */
    public static function listPaymentTracing($id_entidad, $id_anho, $id_mes, $id_depto, $tipo, $id_proceso, $persona, $opcion = 'W')
    {

        $url_pdf = PaymentsData::ruta_url(url('humantalent/payments-tickets-worker-display'));
        //$url_dw= PaymentsData::ruta_url(url('humantalent/payments-tickets-worker-download'));

        $url_dw = PaymentsData::ruta_url(url('boletas'));

        $query = DB::table("aps_planilla as a");
        $query->join("moises.persona as p", 'P.ID_PERSONA', '=', 'A.ID_PERSONA');
        $query->join("moises.persona_natural as pn", 'pn.ID_PERSONA', '=', 'P.ID_PERSONA');
        $query->join("conta_mes as m", 'm.id_mes', '=', 'A.id_mes');
        $query->join("conta_entidad_depto as d", 'D.ID_ENTIDAD', DB::raw('A.ID_ENTIDAD AND  D.ID_DEPTO=A.ID_DEPTO'));
        $query->leftjoin("aps_planilla_boleta as x", 'X.ID_ENTIDAD', DB::raw('A.ID_ENTIDAD AND  X.ID_ANHO=A.ID_ANHO AND X.ID_MES=A.ID_MES AND X.ID_PERSONA=A.ID_PERSONA AND X.ID_CONTRATO=A.ID_CONTRATO'));

        // FC_GTH_OBTENER_EMAIL(A.ID_PERSONA) AS CORREO
        //FC_GTH_OBTENER_CELULAR(A.ID_PERSONA) AS CELULAR,

        $query->select(
            'A.ID_ENTIDAD',
            'A.ID_PERSONA',
            'A.ID_CONTRATO',
            'A.ID_ANHO',
            'A.ID_MES',
            'P.PATERNO',
            'P.MATERNO',
            'P.NOMBRE',
            'X.CLAVE',
            'x.id_gestion',
            'X.ARCHIVO',
            'A.ID_DEPTO',
            'm.nombre as mes',
            DB::raw("
                    D.NOMBRE AS DEPTO,
                    P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE as empleado,
                    PN.CORREO,
                    X.CORREO AS CORREO1,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 )||' '||X.CORREO as enviado,
                    PN.CELULAR,
                    x.CELULAR AS CELULAR1,
                    SUBSTR(A.ID_DEPTO,1,1) AS ID_DEPTO_PADRE,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FIRMA,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS AVISO,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS REVISADO,
                    (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS DESCARGADO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FFIRMA,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS FAVISO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS FREVISADO,
                    (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS FDESCARGADO,
                    '" . $url_pdf . "' as urls,
                    '" . $url_dw . "/'||X.ARCHIVO as urls_dw
                ")
        );

        $query->where("A.ID_ENTIDAD", $id_entidad);
        $query->where("A.ID_ANHO", $id_anho);
        $query->where("A.ID_MES", $id_mes);


        $query->whereraw("SUBSTR(A.ID_DEPTO,1,1)='" . $id_depto . "'");

        if (strlen($id_proceso) > 0) {
            $cond = "";
            if ($tipo == "N") {
                $cond = " not ";
            }
            $where = "(COALESCE(x.ID_GESTION,0) " . $cond . " IN(
                SELECT Y.ID_GESTION FROM APS_PLANILLA_BOLETA_PROCESO Y , APS_PLANILLA_BOLETA Z
                WHERE  Y.ID_GESTION=Z.ID_GESTION
                AND Z.ID_ENTIDAD=A.ID_ENTIDAD
                AND Z.ID_ANHO=A.ID_ANHO
                AND Z.ID_MES=A.ID_MES
                AND Z.ID_PERSONA=A.ID_PERSONA
                AND Z.ID_CONTRATO=A.ID_CONTRATO
                AND Y.ID_PROCESO = " . $id_proceso . "
              )) ";
            $query->whereraw($where);
        }
        if (strlen(trim($persona)) > 0) {
            $datos = $persona . "%";
            $query->whereRaw("(lower(P.PATERNO) like lower('" . $datos . "') or lower(P.MATERNO) like lower('" . $datos . "') or lower(P.NOMBRE) like lower('" . $datos . "')
                    or lower(P.NOMBRE||' '||P.PATERNO) like lower('" . $datos . "') or lower(P.NOMBRE||' '||P.PATERNO||' '||P.MATERNO) like lower('" . $datos . "')
                    or lower(P.PATERNO||' '||P.MATERNO) like lower('" . $datos . "') or lower(P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE) like lower('" . $datos . "'))");
        }
        $query->orderBy('P.PATERNO', 'asc');
        $query->orderBy('P.MATERNO', 'asc');
        $query->orderBy('P.NOMBRE', 'asc');

        //dd($query);
        if ($opcion == "W") {
            $data = $query->paginate(25);
        } else {
            $data = $query->get();
        }


        return $data;
    }
    public static function listPaymentTracingSUNAFIL($id_entidad, $id_anho, $id_mes, $id_depto, $tipo, $id_proceso, $persona, $opcion = 'W')
    {

        $url_pdf = PaymentsData::ruta_url(url('humantalent/payments-tickets-worker-display'));
        //$url_dw= PaymentsData::ruta_url(url('humantalent/payments-tickets-worker-download'));

        $url_dw = PaymentsData::ruta_url(url('boletas'));

        $query = DB::table("aps_planilla as a");
        $query->join("moises.persona as p", 'P.ID_PERSONA', '=', 'A.ID_PERSONA');
        $query->join("conta_mes as m", 'm.id_mes', '=', 'A.id_mes');
        $query->join("conta_entidad_depto as d", 'D.ID_ENTIDAD', DB::raw('A.ID_ENTIDAD AND  D.ID_DEPTO=A.ID_DEPTO'));
        $query->leftjoin("aps_planilla_boleta as x", 'X.ID_ENTIDAD', DB::raw('A.ID_ENTIDAD AND  X.ID_ANHO=A.ID_ANHO AND X.ID_MES=A.ID_MES AND X.ID_PERSONA=A.ID_PERSONA AND X.ID_CONTRATO=A.ID_CONTRATO'));
        $query->select(
            'A.ID_ENTIDAD',
            'A.ID_PERSONA',
            'A.ID_ANHO',
            'A.ID_MES',
            'X.ARCHIVO',
            'A.ID_DEPTO',
            'm.nombre as mes',
            'X.CLAVE',
            DB::raw("
                    D.NOMBRE AS DEPTO,
                    P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE as empleado,
                    SUBSTR(A.ID_DEPTO,1,1) AS ID_DEPTO_PADRE,
                    '" . $url_pdf . "' as urls,
                    '" . $url_dw . "/'||X.ARCHIVO as urls_dw
                ")
        );


        $query->where("A.ID_ENTIDAD", $id_entidad);
        $query->where("A.ID_ANHO", $id_anho);
        $query->where("A.ID_MES", $id_mes);


        $query->whereraw("SUBSTR(A.ID_DEPTO,1,1)='" . $id_depto . "'");

        if (strlen($id_proceso) > 0) {
            $cond = "";
            if ($tipo == "N") {
                $cond = " not ";
            }
            $where = "(COALESCE(x.ID_GESTION,0) " . $cond . " IN(
                SELECT Y.ID_GESTION FROM APS_PLANILLA_BOLETA_PROCESO Y , APS_PLANILLA_BOLETA Z
                WHERE  Y.ID_GESTION=Z.ID_GESTION
                AND Z.ID_ENTIDAD=A.ID_ENTIDAD
                AND Z.ID_ANHO=A.ID_ANHO
                AND Z.ID_MES=A.ID_MES
                AND Z.ID_PERSONA=A.ID_PERSONA
                AND Z.ID_CONTRATO=A.ID_CONTRATO
                AND Y.ID_PROCESO = " . $id_proceso . "
              )) ";
            $query->whereraw($where);
        }
        if (strlen(trim($persona)) > 0) {
            $datos = $persona . "%";
            $query->whereRaw("(lower(P.PATERNO) like lower('" . $datos . "') or lower(P.MATERNO) like lower('" . $datos . "') or lower(P.NOMBRE) like lower('" . $datos . "')
                    or lower(P.NOMBRE||' '||P.PATERNO) like lower('" . $datos . "') or lower(P.NOMBRE||' '||P.PATERNO||' '||P.MATERNO) like lower('" . $datos . "')
                    or lower(P.PATERNO||' '||P.MATERNO) like lower('" . $datos . "') or lower(P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE) like lower('" . $datos . "'))");
        }
        $query->orderBy('P.PATERNO', 'asc');
        $query->orderBy('P.MATERNO', 'asc');
        $query->orderBy('P.NOMBRE', 'asc');

        //dd($query);
        if ($opcion == "W") {
            $data = $query->paginate(25);
        } else {
            $data = $query->get();
        }


        return $data;
    }
    public static function listPaymentTracingAll($id_entidad, $id_anho, $id_mes, $id_depto, $tipo, $id_proceso, $persona)
    {
        $where = "";

        if (strlen($id_proceso) > 0) {
            $cond = "";
            if ($tipo == "N") {
                $cond = " not ";
            }
            $where = " AND COALESCE(X.ID_GESTION,0) " . $cond . " IN(
                SELECT Y.ID_GESTION FROM APS_PLANILLA_BOLETA_PROCESO Y , APS_PLANILLA_BOLETA Z
                WHERE  Y.ID_GESTION=Z.ID_GESTION
                AND Z.ID_ENTIDAD=A.ID_ENTIDAD
                AND Z.ID_ANHO=A.ID_ANHO
                AND Z.ID_MES=A.ID_MES
                AND Z.ID_PERSONA=A.ID_PERSONA
                AND Z.ID_CONTRATO=A.ID_CONTRATO
                AND Y.ID_PROCESO = " . $id_proceso . "
              ) ";
        }


        $sql = "SELECT
                A.ID_ENTIDAD,
                A.ID_PERSONA,
                A.ID_CONTRATO,
                A.ID_ANHO,
                A.ID_MES,
                P.PATERNO,
                P.MATERNO,
                P.NOMBRE,
                X.CLAVE,
                A.ID_DEPTO,
                D.NOMBRE AS DEPTO,
                COALESCE(X.CORREO,FC_GTH_OBTENER_EMAIL(A.ID_PERSONA)) AS CORREO,
                X.CELULAR,
                SUBSTR(A.ID_DEPTO,1,1) AS ID_DEPTO_PADRE,
                (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FIRMA,
                (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS AVISO,
                (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS REVISADO,
                (SELECT DECODE(COUNT(X.ID_PROCESO),0,0,1) FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS DESCARGADO,
                (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 1 ) AS FFIRMA,
                (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 2 ) AS FAVISO,
                (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 3 ) AS FREVISADO,
                (SELECT TO_CHAR(FECHA,'DD/MM/YYYY HH24:MI:SS') FROM APS_PLANILLA_BOLETA_PROCESO Y  WHERE Y.ID_GESTION = X.ID_GESTION  AND Y.ID_PROCESO = 4 ) AS FDESCARGADO
              FROM APS_PLANILLA A INNER JOIN MOISES.PERSONA P
              ON A.ID_PERSONA=P.ID_PERSONA
              INNER JOIN CONTA_ENTIDAD_DEPTO D
              ON A.ID_ENTIDAD=D.ID_ENTIDAD
              AND A.ID_DEPTO=D.ID_DEPTO
              LEFT JOIN APS_PLANILLA_BOLETA X
              ON A.ID_ENTIDAD=X.ID_ENTIDAD
              AND A.ID_ANHO=X.ID_ANHO
              AND A.ID_MES=X.ID_MES
              AND A.ID_PERSONA=X.ID_PERSONA
              AND A.ID_CONTRATO=X.ID_CONTRATO
              WHERE A.ID_ENTIDAD=" . $id_entidad . "
              AND A.ID_ANHO=" . $id_anho . "
              AND A.ID_MES=" . $id_mes . "
              AND SUBSTR(A.ID_DEPTO,1,1)='" . $id_depto . "'
              " . $where . "
              ORDER BY P.PATERNO,
                P.MATERNO,
                P.NOMBRE";
        $query = DB::select($sql);
        return $query;
    }
    public static function listCertificate()
    {
        $sql = "SELECT
                    ROW_NUMBER() OVER (PARTITION BY A.ID_CERTIFICADO ORDER BY A.ID_CERTIFICADO) AS CONTAR,
                    A.ID_CERTIFICADO,
                    A.DESCRIPCION,
                    A.NOMBRE_ARCHIVO,
                    A.CLAVE,
                    TO_CHAR(A.DESDE,'DD/MM/YYYY') AS DESDE,
                    TO_CHAR(A.HASTA,'DD/MM/YYYY') AS HASTA,
                    A.ID_PERSONA,
                    A.FIRMA,
                    '" . asset('img') . "' as urls,
                    A.UBICACION,
                    A.ESTADO,
                    B.ID_DEPTO,
                    B.ID_ENTIDAD,
                    CASE WHEN ESTADO='1' THEN 'Activo' ELSE 'Inactivo' end ESTADO_DESC,
                    E.NOMBRE||' - '||C.NOMBRE AS NOMBRE,
                    CASE WHEN B.ID_DEPTO IS NULL THEN 0 ELSE 1 END ISDEPTO,
                    P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE AS RESPONSABLE
                FROM APS_CERTIFICADO A
                LEFT JOIN APS_CERTIFICADO_DEPTO B
                ON A.ID_CERTIFICADO=B.ID_CERTIFICADO
                LEFT JOIN CONTA_ENTIDAD_DEPTO C
                ON B.ID_ENTIDAD=C.ID_ENTIDAD
                AND B.ID_DEPTO=C.ID_DEPTO
                LEFT JOIN MOISES.PERSONA P
                ON A.ID_PERSONA=P.ID_PERSONA
                LEFT JOIN CONTA_ENTIDAD E
                ON E.ID_ENTIDAD=C.ID_ENTIDAD
                ORDER BY A.ID_CERTIFICADO DESC,C.ID_ENTIDAD,B.ID_DEPTO";
        $query = DB::select($sql);
        return $query;
    }
    public static function addCertificate($descripcion, $nombre_archivo, $archivo, $dni, $desde, $hasta, $clave, $firma, $ubicacion)
    {
        //addCertificate($descripcion,$nombre_archivo,$archivo,$clave,$desde,$hasta,$id_persona,$firma)
        $ret = 'OK';
        $sql = "SELECT
                ID_PERSONA
              FROM  MOISES.PERSONA_DOCUMENTO
              WHERE NUM_DOCUMENTO='" . $dni . "'
              AND ID_TIPODOCUMENTO=1";

        $query = DB::select($sql);
        $id_persona = 0;
        foreach ($query as $row) {
            $id_persona = $row->id_persona;
        }

        if ($id_persona > 0) {
            $psw = PaymentsData::encriptar($clave, $id_persona);

            $query = "SELECT
                            COALESCE(MAX(ID_CERTIFICADO),0)+1 ID_CERTIFICADO
                    FROM APS_CERTIFICADO ";
            $oQuery = DB::select($query);
            $id = 1;
            foreach ($oQuery as $item) {
                $id = $item->id_certificado;
            }

            $fecha_sys = date("Y-m-d H:i:s");
            $estado = '1';
            $pdo = DB::getPdo();
            $sql = "INSERT INTO APS_CERTIFICADO (
                        ID_CERTIFICADO,
                        DESCRIPCION,
                        NOMBRE_ARCHIVO,
                        ARCHIVO,
                        CLAVE,
                        DESDE,
                        HASTA,
                        ID_PERSONA,
                        FIRMA,
                        UBICACION,
                        ESTADO,
                        FECHA
                        ) VALUES (
                        :ID_CERTIFICADO,
                        :DESCRIPCION,
                        :NOMBRE_ARCHIVO,
                        EMPTY_BLOB(),
                        :CLAVE,
                        :DESDE,
                        :HASTA,
                        :ID_PERSONA,
                        :FIRMA,
                        :UBICACION,
                        :ESTADO,
                        :FECHA
                        )
                RETURNING ARCHIVO INTO :ARCHIVO";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':ID_CERTIFICADO', $id, PDO::PARAM_INT);
            $stmt->bindParam(':DESCRIPCION', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':NOMBRE_ARCHIVO', $nombre_archivo, PDO::PARAM_STR);
            $stmt->bindParam(':ARCHIVO', $archivo, PDO::PARAM_LOB);
            $stmt->bindParam(':CLAVE', $psw, PDO::PARAM_STR);
            $stmt->bindParam(':DESDE', $desde, PDO::PARAM_STR);
            $stmt->bindParam(':HASTA', $hasta, PDO::PARAM_STR);
            $stmt->bindParam(':ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':FIRMA', $firma, PDO::PARAM_STR);
            $stmt->bindParam(':UBICACION', $ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(':ESTADO', $estado, PDO::PARAM_STR);
            $stmt->bindParam(':FECHA', $fecha_sys, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $ret = "Numero DNI Incorrceto no encontrado (" . $dni . ")";
        }
        return $ret;
    }
    /*public static function addCertificate($descripcion,$nombre_archivo,$archivo,$dni,$desde,$hasta,$clave,$firma,$ubicacion){
        //addCertificate($descripcion,$nombre_archivo,$archivo,$clave,$desde,$hasta,$id_persona,$firma)
        $ret='OK';
        $sql="SELECT
                ID_PERSONA
              FROM  MOISES.PERSONA_DOCUMENTO
              WHERE NUM_DOCUMENTO='".$dni."'
              AND ID_TIPODOCUMENTO=1";

        $query = DB::select($sql);
        $id_persona = 0;
        foreach ($query as $row){
            $id_persona = $row->id_persona;
        }

        if($id_persona>0){
            $psw = PaymentsData::encriptar($clave,$id_persona);

            $query = "SELECT
                            COALESCE(MAX(ID_CERTIFICADO),0)+1 ID_CERTIFICADO
                    FROM APS_CERTIFICADO ";
            $oQuery = DB::select($query);
            $id=1;
            foreach ($oQuery as $item){
                $id = $item->id_certificado;
            }

            $fecha_sys=date("Y-m-d H:i:s");
            $estado='1';
            $pdo = DB::getPdo();
            $sql = "INSERT INTO APS_CERTIFICADO (
                        ID_CERTIFICADO,
                        DESCRIPCION,
                        NOMBRE_ARCHIVO,
                        ARCHIVO,
                        CLAVE,
                        DESDE,
                        HASTA,
                        ID_PERSONA,
                        FIRMA,
                        UBICACION,
                        ESTADO,
                        FECHA
                        ) VALUES (
                        :ID_CERTIFICADO,
                        :DESCRIPCION,
                        :NOMBRE_ARCHIVO,
                        EMPTY_BLOB(),
                        :CLAVE,
                        :DESDE,
                        :HASTA,
                        :ID_PERSONA,
                        :FIRMA,
                        :UBICACION,
                        :ESTADO,
                        :FECHA
                        )
                RETURNING ARCHIVO INTO :ARCHIVO";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':ID_CERTIFICADO', $id, PDO::PARAM_INT);
            $stmt->bindParam(':DESCRIPCION', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':NOMBRE_ARCHIVO', $nombre_archivo, PDO::PARAM_STR);
            $stmt->bindParam(':ARCHIVO', $archivo, PDO::PARAM_LOB);
            $stmt->bindParam(':CLAVE', $psw, PDO::PARAM_STR);
            $stmt->bindParam(':DESDE', $desde, PDO::PARAM_STR);
            $stmt->bindParam(':HASTA', $hasta, PDO::PARAM_STR);
            $stmt->bindParam(':ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':FIRMA', $firma, PDO::PARAM_STR);
            $stmt->bindParam(':UBICACION', $ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(':ESTADO', $estado, PDO::PARAM_STR);
            $stmt->bindParam(':FECHA', $fecha_sys, PDO::PARAM_STR);
            $stmt->execute();
        }else{
           $ret="Numero DNI Incorrceto no encontrado (".$dni.")";
        }
        return $ret;
    }*/

    public static function deleteCertificate($id_certificado)
    {

        $query = "DELETE FROM APS_CERTIFICADO_DEPTO
                WHERE ID_CERTIFICADO = " . $id_certificado;
        DB::delete($query);

        $query = "DELETE FROM APS_CERTIFICADO
                WHERE ID_CERTIFICADO = " . $id_certificado;
        DB::delete($query);
    }
    public static function addCertificateDepto($id_certificado, $id_entidad, $id_depto)
    {
        $ret = 0;
        $sql = "SELECT
                    ID_CERTIFICADO
                FROM APS_CERTIFICADO_DEPTO
                WHERE ID_ENTIDAD=" . $id_entidad . "
                AND ID_DEPTO=" . $id_depto . "
                AND ID_CERTIFICADO=" . $id_certificado;
        $query = DB::select($sql);

        if (count($query) == 0) {

            $fecha_sys = date("Y-m-d H:i:s");
            $data = DB::table('APS_CERTIFICADO_DEPTO')->insert(
                array(
                    'ID_CERTIFICADO' => $id_certificado,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto,
                    'FECHA' => $fecha_sys
                )
            );
        } else {
            $ret = 1;
        }
        return  $ret;
    }
    public static function deleteCertificateDepto($id_certificado, $id_entidad, $id_depto)
    {

        $query = "DELETE FROM APS_CERTIFICADO_DEPTO
                WHERE ID_CERTIFICADO = " . $id_certificado . "
                AND ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = '" . $id_depto . "'";
        DB::delete($query);
    }

    private static function encriptar($string, $key)
    {
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }


    public static function showCertificate($id_certificado)
    {
        $sql = "SELECT
                    a.ID_CERTIFICADO,
                    a.DESCRIPCION,
                    a.NOMBRE_ARCHIVO,
                    a.ARCHIVO,
                    a.CLAVE,
                    a.DESDE,
                    a.HASTA,
                    a.ID_PERSONA,
                    a.FIRMA,
                    a.ESTADO,
                    p.paterno||' '||p.materno||' '||p.nombre as representante,
                    pd.NUM_DOCUMENTO,
                    A.UBICACION,
                    A.NUMSERIE,
                    case when a.HASTA<current_date then '1' else '0' end as fincert,
                    to_char(a.HASTA,'DD/MM/YYYY') as FHASTA,
                    logo_boleta,
                    boleta_title_background,
                    boleta_ds_remuneraciones,
                    logo_firma,
                    MAIL_DRIVER,
                    MAIL_HOST,
                    MAIL_PORT,
                    MAIL_USERNAME, 
                    MAIL_PASSWORD,
                    MAIL_ENCRYPTION,
                    MAIL_FROM_NAME,
                    MAIL_BODY,
                    MAIL_FOOTER,
                    SMS_USERNAME,
                    SMS_PASSWORD
                FROM APS_CERTIFICADO a,moises.persona p,moises.persona_documento pd
                WHERE a.id_persona=p.id_persona
                and p.id_persona=pd.id_persona
                and pd.ID_TIPODOCUMENTO=1
                and a.ID_CERTIFICADO=" . $id_certificado;
        $query = DB::select($sql);
        return $query;
    }
    public static function sendEmail($entity, $id_depto, $year, $month, $id_persona, $email, $id_proceso)
    {

        $where = "";

        if ($id_persona > 0) {

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT.SP_CORREO_PERSONA(
                                            :P_ID_ENTIDAD,
                                            :P_ID_PERSONA,
                                            :P_ID_ANHO,
                                            :P_ID_MES,
                                            :P_EMAIL
                                         ); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $entity, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ANHO', $year, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $month, PDO::PARAM_INT);
            $stmt->bindParam(':P_EMAIL', $email, PDO::PARAM_STR);

            $stmt->execute();

            $where = " and A.ID_PERSONA=" . $id_persona . " ";
        }

        $query = DB::table("aps_planilla as a");
        $query->join("moises.persona as b", 'b.ID_PERSONA', '=', 'A.ID_PERSONA');
        $query->join("moises.persona_natural as pn", 'pn.ID_PERSONA', '=', 'b.ID_PERSONA');
        $query->join("APS_PLANILLA_BOLETA as c", 'c.ID_ENTIDAD', DB::raw('A.ID_ENTIDAD AND A.ID_PERSONA=C.ID_PERSONA AND A.ID_ANHO= C.ID_ANHO AND A.ID_MES= C.ID_MES AND A.ID_CONTRATO= C.ID_CONTRATO'));
        $query->join("CONTA_MES as m", 'm.ID_MES', '=', 'a.ID_MES');

        $query->select(
            'A.ID_PERSONA',
            'B.PATERNO',
            'B.MATERNO',
            'B.NOMBRE',
            'A.ID_DEPTO',
            'C.ARCHIVO',
            'C.CLAVE',
            // DB::raw("M.NOMBRE AS MES,FC_GTH_OBTENER_EMAIL(A.ID_PERSONA) AS EMAIL,FC_GTH_OBTENER_CELULAR(A.ID_PERSONA) AS CELULAR"),
            DB::raw("M.NOMBRE AS MES,PN.correo AS EMAIL,pn.CELULAR")
        );

        $query->where("A.ID_ENTIDAD", $entity);
        $query->where("A.ID_ANHO", $year);
        $query->where("A.ID_MES", $month);
        $query->where("C.ID_DEPTO", $id_depto);

        if ($id_proceso == '3') {
            $query->whereraw("A.ID_PERSONA IN(
                        SELECT ID_PERSONA from MOISES.PERSONA_VIRTUAL WHERE ID_TIPOVIRTUAL=1
                )
                AND C.ID_GESTION NOT IN(
                  SELECT G.ID_GESTION FROM APS_PLANILLA_BOLETA G, APS_PLANILLA_BOLETA_PROCESO F
                  WHERE G.ID_GESTION=F.ID_GESTION
                  AND G.ID_ENTIDAD= C.ID_ENTIDAD
                  AND G.ID_PERSONA=C.ID_PERSONA
                  AND G.ID_ANHO= C.ID_ANHO
                  AND G.ID_MES= C.ID_MES
                  AND G.ID_CONTRATO= C.ID_CONTRATO
                  AND F.ID_PROCESO=" . $id_proceso . "
                )
                AND C.ID_GESTION  IN(
                  SELECT G.ID_GESTION FROM APS_PLANILLA_BOLETA G, APS_PLANILLA_BOLETA_PROCESO F
                  WHERE G.ID_GESTION=F.ID_GESTION
                  AND G.ID_ENTIDAD= C.ID_ENTIDAD
                  AND G.ID_PERSONA=C.ID_PERSONA
                  AND G.ID_ANHO= C.ID_ANHO
                  AND G.ID_MES= C.ID_MES
                  AND G.ID_CONTRATO= C.ID_CONTRATO
                  AND F.ID_PROCESO=2
                )");
        } else {
            $query->whereraw("A.ID_PERSONA IN(
                        SELECT ID_PERSONA from MOISES.PERSONA_VIRTUAL WHERE ID_TIPOVIRTUAL=1
                )
                AND C.ID_GESTION NOT IN(
                  SELECT G.ID_GESTION FROM APS_PLANILLA_BOLETA G, APS_PLANILLA_BOLETA_PROCESO F
                  WHERE G.ID_GESTION=F.ID_GESTION
                  AND G.ID_ENTIDAD= C.ID_ENTIDAD
                  AND G.ID_PERSONA=C.ID_PERSONA
                  AND G.ID_ANHO= C.ID_ANHO
                  AND G.ID_MES= C.ID_MES
                  AND G.ID_CONTRATO= C.ID_CONTRATO
                  AND F.ID_PROCESO=" . $id_proceso . "
                )");
        }
        if ($id_persona > 0) {
            $query->where("A.ID_PERSONA", $id_persona);
        }
        $query->orderBy('B.PATERNO', 'asc');
        $query->orderBy('B.MATERNO', 'asc');
        $query->orderBy('B.NOMBRE', 'asc');

        $data = $query->paginate(100);

        return $data;
    }
    /*public static function sendEmail($entity,$id_depto,$year,$month,$id_persona,$email){

        $where="";

        if ($id_persona>0){

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT.SP_CORREO_PERSONA(
                                            :P_ID_ENTIDAD,
                                            :P_ID_PERSONA,
                                            :P_ID_ANHO,
                                            :P_ID_MES,
                                            :P_EMAIL
                                         ); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $entity, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ANHO', $year, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $month, PDO::PARAM_INT);
            $stmt->bindParam(':P_EMAIL', $email, PDO::PARAM_STR);

            $stmt->execute();

            $where=" and A.ID_PERSONA=".$id_persona." ";
        }



        $query = "SELECT
                    A.ID_PERSONA,
                    B.PATERNO,
                    B.MATERNO,
                    B.NOMBRE,
                    A.ID_DEPTO,
                    COALESCE(C.CORREO,FC_GTH_OBTENER_EMAIL(A.ID_PERSONA)) AS EMAIL,
                    C.ARCHIVO,
                    M.NOMBRE AS MES,
                    C.CLAVE,
                    COALESCE(C.CELULAR,PT.NUM_TELEFONO) AS CELULAR
                FROM APS_PLANILLA A
                INNER JOIN MOISES.PERSONA B
                ON A.ID_PERSONA = B.ID_PERSONA
                INNER JOIN APS_PLANILLA_BOLETA C
                ON A.ID_ENTIDAD= C.ID_ENTIDAD
                AND A.ID_PERSONA=C.ID_PERSONA
                AND A.ID_ANHO= C.ID_ANHO
                AND A.ID_MES= C.ID_MES
                AND A.ID_CONTRATO= C.ID_CONTRATO
                INNER JOIN CONTA_MES M
                ON AND A.ID_MES=M.ID_MES
                LEFT JOIN MOISES.PERSONA_TELEFONO PT
                ON PT.ID_PERSONA=B.ID_PERSONA
                AND PT.ID_TIPOTELEFONO=5
                WHERE A.ID_ENTIDAD = ".$entity."
                AND A.ID_ANHO = ".$year."
                AND A.ID_MES = ".$month."
                AND C.ID_DEPTO='".$id_depto."'
                AND A.ID_PERSONA IN(
                  SELECT ID_PERSONA from MOISES.PERSONA_VIRTUAL WHERE ID_TIPOVIRTUAL=1
                )
                AND C.ID_GESTION NOT IN(
                  SELECT G.ID_GESTION FROM APS_PLANILLA_BOLETA G, APS_PLANILLA_BOLETA_PROCESO F
                  WHERE G.ID_PROCESO=F.ID_PROCESO
                  AND G.ID_ENTIDAD= C.ID_ENTIDAD
                  AND G.ID_PERSONA=C.ID_PERSONA
                  AND G.ID_ANHO= C.ID_ANHO
                  AND G.ID_MES= C.ID_MES
                  AND G.ID_CONTRATO= C.ID_CONTRATO
                  AND F.ID_PROCESO=2
                )
                ".$where."
                ORDER BY B.PATERNO,
                B.MATERNO,
                B.NOMBRE ";
        $oQuery = DB::select($query);

        return $oQuery;
    }*/
    public static function validaGenerate($id_entidad, $id_anho, $id_mes, $id_depto, $id_persona, $id_certificado)
    {
        $nerror = 0;

        $return = [
            'nerror' => 0,
            'msgerror' => '',
            'certificado' => 0
        ];

        $msgerror = '';
        for ($i = 1; $i <= 300; $i++) {
            $msgerror .= '0';
        }


        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT.SP_VALID_GENERATE(
                                        :P_ID_ENTIDAD,
                                        :P_ID_ANHO,
                                        :P_ID_MES,
                                        :P_ID_DEPTO,
                                        :P_ID_PERSONA,
                                        :P_ID_CERTIFICADO,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_CERTIFICADO', $id_certificado, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);

        $stmt->execute();

        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'certificado' => $id_certificado
        ];

        if ($nerror == 0) {
            $ret  = SignatureData::validarCertificado($id_certificado);
            if ($ret['nerror'] != 0) {
                $return = [
                    'nerror' => 1,
                    'msgerror' => $ret['mensaje'],
                    'certificado' => $id_certificado
                ];
            }
        }

        return $return;
    }

    public static function obtenerDatosFirma($id_entidad, $id_depto)
    {
        $id_certificado = 0;

        $return = [
            'nerror' => 1,
            'msgerror' => 'No hay datos para firmar',
            'certificado' => 0
        ];

        $sql = "select id_certificado,logo_boleta, firma_ubicacion,firma_razon
              from aps_certificado
              where id_entidad=" . $id_entidad . "
              and id_depto_padre='" . $id_depto . "'";

        $data = DB::select($sql);
        $logo_boleta="";
        $firma_ubicacion="";
        $firma_razon="";
        foreach ($data as $row) {
            $id_certificado = $row->id_certificado;
            $logo_boleta=$row->logo_boleta;
            $firma_ubicacion=$row->firma_ubicacion;
            $firma_razon=$row->firma_razon;
        }
        if ($id_certificado == 0) {
            $return = [
                'nerror' => 1,
                'msgerror' => 'No hay datos de firma',
                'certificado' => 0
            ];
        } else {
            $return = [
                'nerror' => 0,
                'msgerror' => '',
                'certificado' => $id_certificado,
                'logo' => $logo_boleta,
                'ubicacion' => $firma_ubicacion,
                'razon' => $firma_razon
            ];
        }



        return $return;
    }

    public static function showBoletaPDF($clave)
    {
        $sql = "SELECT
                A.ID_ANHO,
                A.ID_MES,
                A.ID_ENTIDAD,
                A.ID_DEPTO,
                A.ARCHIVO,
                M.NOMBRE,
                A.CORREO,
                P.NOMBRE||' '||P.PATERNO||' '||P.MATERNO as PERSONA
                FROM APS_PLANILLA_BOLETA A, CONTA_MES M,MOISES.PERSONA P
                WHERE A.ID_PERSONA=P.ID_PERSONA
                AND A.ID_MES=M.ID_MES
                AND A.CLAVE = '" . $clave . "' ";
        $query = DB::select($sql);
        return $query;
    }

    public static function updateBoletaPDF($clave, $valor, $correo = '', $celular = '')
    {

        if ($valor == 2) {
            $query = "UPDATE APS_PLANILLA_BOLETA SET
                    ID_PROCESO =" . $valor . ",
                    CORREO='" . $correo . "',
                    CELULAR='" . $celular . "'
                WHERE CLAVE = '" . $clave . "' ";
            DB::update($query);
        } else {
            if ($valor == 4) {
                $contar = DB::table('aps_planilla_boleta_proceso')
                    ->where('id_proceso', 3)
                    ->whereraw("id_gestion in(
                                select id_gestion from APS_PLANILLA_BOLETA
                               WHERE CLAVE = '" . $clave . "'
                                )")->count();
                if ($contar == 0) {
                    "UPDATE APS_PLANILLA_BOLETA SET
                    ID_PROCESO =3
                    WHERE CLAVE = '" . $clave . "' ";
                }
            }
            $query = "UPDATE APS_PLANILLA_BOLETA SET
                    ID_PROCESO =" . $valor . "
                WHERE CLAVE = '" . $clave . "' ";
            DB::update($query);
        }
    }
    public static function listProcesos()
    {
        $sql = "SELECT
                    ID_PROCESO,
                    NOMBRE,
                    ORDEN,
                    ESTADO
                FROM APS_PROCESO_BOLETA
                WHERE ESTADO = '1'
                ORDER BY ORDEN";
        $query = DB::select($sql);
        return $query;
    }
    public static function directorioBoleta($id_entidad, $id_anho, $id_mes, $copia='')
    {
        $return = [
            'nerror' => 1,
            'msgerror' => '',
            'directorio' => ''
        ];

        //$directorio = "/u01/vhosts/lamb-financial-dev.upeu.edu.pe/httpdocs/projecto_lamb-data/lamb_files/boletapago"; //prueba
        //$directorio = "/u01/vhosts/lamb-financial-dev.upeu.edu.pe/httpdocs/projecto_lamb-data"; //prueba
        //$directorio = "/u01/vhosts/lambfinancial.upeu.edu.pe/httpdocs/lamb-data"; //producción

        $dat = PaymentsData::listConfigPlanilla("DIR_ARCHIVO");
        $directorio = $dat["valor"];

        $dirgen = $directorio . "/lamb_files";
        if (!file_exists($dirgen)) {

            mkdir($dirgen, 0777);
        }
        if (!file_exists($dirgen)) {
            $return = [
                'nerror' => 1,
                'msgerror' => 'Directorio lamb_files no se puede crear',
                'directorio' => ''
            ];

            return $return;
        }
        $dirgen = $directorio . "/lamb_files/lamb_gth";
        if (!file_exists($dirgen)) {

            mkdir($dirgen, 0777);
        }
        if (!file_exists($dirgen)) {
            $return = [
                'nerror' => 1,
                'msgerror' => 'Directorio lamb_files/lamb_gth no se puede crear',
                'directorio' => ''
            ];

            return $return;
        }
        $dirgen = $directorio . "/lamb_files/lamb_gth/boletapago";
        if (!file_exists($dirgen)) {

            mkdir($dirgen, 0777);
        }
        if (!file_exists($dirgen)) {
            $return = [
                'nerror' => 1,
                'msgerror' => 'Directorio lamb_files/lamb_gth/boletapago no se puede crear',
                'directorio' => ''
            ];

            return $return;
        }

        $directorio = $dirgen;



        $dmeses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'setiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');

        $mes = $dmeses[$id_mes];
        if(!empty($copia)){
            $mes = $dmeses[$id_mes].'-'.$copia;
        }
        try {
            if (!file_exists($directorio)) {
                $return = [
                    'nerror' => 1,
                    'msgerror' => 'Directorio general no existe',
                    'directorio' => ''
                ];

                return $return;
            }
            // Add $id_entidad
            $direntidad = $directorio . "/" . $id_entidad;
            if (!file_exists($direntidad)) {

                mkdir($direntidad, 0777);
            }

            // fin de add $id_entidad

            $diraanho = $directorio . "/" . $id_entidad . "/" . $id_anho;
            if (!file_exists($diraanho)) {

                mkdir($diraanho, 0777);
            }

            $dirmes = $directorio . "/" . $id_entidad . "/" . $id_anho . "/" . $mes;
            if (!file_exists($dirmes)) {

                mkdir($dirmes, 0777);
            }
            $return = [
                'nerror' => 0,
                'msgerror' => '',
                'directorio' => $dirmes
            ];
            return $return;
        } catch (Exception $e) {
            $return = [
                'nerror' => 2,
                'msgerror' => $e->getMessage(),
                'directorio' => ''
            ];
            return $return;
        }
    }

    private static function ruta_url($url)
    {
        $dat = PaymentsData::listConfigPlanilla("HTTP");
        $protocol = $dat["valor"];
        $url_ori = str_replace("http://", $protocol . "://", $url);
        return $url_ori;
    }

    public static function listConfigPlanilla($id_config)
    {
        $sql = "SELECT
                    VALOR,
                    VALOR1
                FROM APS_CONFIG_PLANILLA
                WHERE ID_CONFIG='" . $id_config . "' ";
        $query = DB::select($sql);



        $return["valor"] = '';
        $return["valor1"] = '';

        foreach ($query as $row) {
            $return["valor"] = $row->valor;
            $return["valor1"] = $row->valor1;
        }

        return $return;
    }

    public static function listEmailCelular($id_persona)
    {
        $sql = "SELECT
                    ID_VIRTUAL,
                    ID_PERSONA,
                    DIRECCION,
                    GTH,
                    coalesce(GTH,0) as GTH
                FROM MOISES.PERSONA_VIRTUAL
                WHERE ID_TIPOVIRTUAL=1
                and id_persona=" . $id_persona . "
                ORDER BY ID_VIRTUAL";
        $queryemail = DB::select($sql);

        $sql = "SELECT
                    ID_TELEFONO,
                    ID_PERSONA,
                    NUM_TELEFONO,
                    coalesce(GTH,0) as GTH
                FROM MOISES.PERSONA_TELEFONO
                WHERE ID_TIPOTELEFONO IN (3,5)
                and id_persona=" . $id_persona . "
                ORDER BY ID_TELEFONO";
        $querycel = DB::select($sql);

        $sql = "SELECT
                    a.nombre,
                    a.paterno,
                    a.materno,
                    a.ID_PERSONA,
                    (select x.id_virtual from MOISES.PERSONA_VIRTUAL x where x.id_persona=a.id_persona and x.ID_TIPOVIRTUAL=1 and x.GTH=1) as id_virtual,
                    (select max(x.id_telefono) from MOISES.PERSONA_TELEFONO x where x.id_persona=a.id_persona and x.ID_TIPOTELEFONO IN (3,5) and x.GTH=1) as id_telefono
                FROM MOISES.PERSONA a
                WHERE a.id_persona=" . $id_persona;
        $queryper = DB::select($sql);

        $return["dataemail"] = $queryemail;
        $return["datacelular"] = $querycel;
        $return["datpersona"] = $queryper;
        return $return;
    }

    public static function procEmailCelular($id_persona, $opcion, $tipo, $id_virtual, $id_telefono, $email, $celular)
    {
        $nerror = 0;

        $return = [
            'nerror' => 0,
            'msgerror' => ''
        ];

        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }

        $id_certificado = 00000000;
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT.SP_PROC_EMAIL_CELULAR(
                                        :P_ID_PERSONA,
                                        :P_OPCION,
                                        :P_TIPO,
                                        :P_ID_VIRTUAL,
                                        :P_ID_TELEFONO,
                                        :P_EMAIL,
                                        :P_CELULAR,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_OPCION', $opcion, PDO::PARAM_STR);
        $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_VIRTUAL', $id_virtual, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_TELEFONO', $id_telefono, PDO::PARAM_INT);
        $stmt->bindParam(':P_EMAIL', $email, PDO::PARAM_STR);
        $stmt->bindParam(':P_CELULAR', $celular, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);

        $stmt->execute();

        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror
        ];

        return $return;
    }
    public static function pruebas()
    {

        $sql = "SELECT
                    a.ARCHIVO

                FROM APS_CERTIFICADO a,persona p,persona_documento pd
                WHERE a.id_persona=p.id_persona
                and p.id_persona=pd.id_persona
                and pd.ID_TIPODOCUMENTO=1
                and a.ID_CERTIFICADO=1";
        $query = DB::select($sql);
        $dir = "";
        foreach ($query as $row) {
            $dir = $row->archivo;
        }

        /*if (!$almacén_cert = file_get_contents($dir)) {
            echo "Error: No se puede leer el fichero del certificado\n";
            exit;
        }*/

        if (openssl_pkcs12_read($dir, $info_cert, "demo456*")) {
            echo "Información del certificado\n";
            dd($info_cert["pkey"]);
        } else {
            echo "Error: No se puede leer el almacén de certificados.\n";
            exit;
        }
    }
    public static function listaprevia($id_entidad, $id_anho, $id_mes, $id_depto, $id_persona = 0)
    {

        $where = '';

        if ($id_persona != 0) {
            $where = 'AND A.ID_PERSONA=' . $id_persona . " ";
        }

        $sql = "SELECT
                    A.ID_ANHO,
                    A.ID_MES,
                    A.ID_PERSONA,
                    A.ID_CONTRATO,
                    P.NOMBRE,
                    P.PATERNO,
                    P.MATERNO,
                    P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE as persona
                FROM APS_PLANILLA A
                INNER JOIN MOISES.PERSONA P
                ON A.ID_PERSONA=P.ID_PERSONA
                WHERE A.ID_ENTIDAD= " . $id_entidad . "
                AND SUBSTR(ID_DEPTO,1,1)='" . $id_depto . "'
                " . $where . "
                AND A.ID_ANHO=" . $id_anho . "
                AND A.ID_MES= " . $id_mes . "
                AND A.ID_PERSONA NOT IN(
                    SELECT X.ID_PERSONA FROM APS_PLANILLA_BOLETA X
                    WHERE X.ID_CONTRATO = A.ID_CONTRATO
                    AND X.ID_ENTIDAD = A.ID_ENTIDAD
                    AND X.ID_ANHO=" . $id_anho . "
                    AND X.ID_MES= " . $id_mes . "
                )
                ORDER BY P.PATERNO,P.MATERNO,P.NOMBRE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listaprocesar($id_entidad, $id_anho, $id_mes, $id_depto, $id_persona = 0)
    {

        $where = '';


        $q = DB::table('APS_PLANILLA as a');
        $q->join('MOISES.PERSONA as p', 'p.ID_PERSONA', '=', 'a.ID_PERSONA');
        $q->select(
            'A.ID_ANHO',
            'A.ID_MES',
            'A.ID_PERSONA',
            'A.ID_CONTRATO',
            'P.NOMBRE',
            'P.PATERNO',
            'P.MATERNO',
            DB::raw("P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE as persona ")
        );
        $q->where("A.ID_ENTIDAD", $id_entidad);
        $q->where("A.ID_ANHO", $id_anho);
        $q->where("A.ID_MES", $id_mes);

        if ($id_persona != 0) {
            $q->where("A.ID_PERSONA", $id_persona);
        }
        $q->whereraw("SUBSTR(ID_DEPTO,1,1)='" . $id_depto . "'");

        $q->whereraw("A.ID_PERSONA NOT IN(
                    SELECT X.ID_PERSONA FROM APS_PLANILLA_BOLETA X
                    WHERE X.ID_CONTRATO = A.ID_CONTRATO
                    AND X.ID_ENTIDAD = A.ID_ENTIDAD
                    AND X.ID_ANHO=" . $id_anho . "
                    AND X.ID_MES= " . $id_mes . "
                )");
        $q->orderby('P.PATERNO', 'asc');
        $q->orderby('P.MATERNO', 'asc');
        $q->orderby('P.NOMBRE', 'asc');


        $query = $q->take(500)->get();
        return $query;
    }


    public static function insertlog($text)
    {
        DB::table('AAA')->insert(
            array('TEXT' => $text)
        );
    }
    public static function actualizarArchivo($id_entidad, $id_depto, $id_anho, $id_mes, $id_persona) {

        $q= DB::table('eliseo.aps_planilla_boleta as a');
        $q->join('eliseo.aps_planilla as b','a.id_entidad','=',DB::raw("b.id_entidad and  a.id_anho=b.id_anho and a.id_mes=b.id_mes and a.id_persona=b.id_persona and a.id_contrato=b.id_contrato"));
        $q->where('a.id_entidad',$id_entidad);
        $q->where('a.id_anho',$id_anho);
        $q->where('a.id_mes',$id_mes);
        if(!empty($id_depto)) {
            $q->whereraw("substr(b.id_depto,0,1) = '".$id_depto."'");
        }
        if(!empty($id_persona)) {
            $q->where("a.id_persona",$id_persona);
        }
        $q->select('a.id_gestion','archivo',DB::raw("substr(b.id_depto,0,1) as id_depto_padre"));
        $data=$q->get();

        $retdir      =  PaymentsData::directorioBoleta($id_entidad, $id_anho,$id_mes);
        $carpeta     = $retdir["directorio"];

        $retdir1      =  PaymentsData::directorioBoleta($id_entidad, $id_anho,$id_mes,'copia');
        $carpeta1     = $retdir1["directorio"];

        foreach($data as $row){

            if($retdir["nerror"]==0 and $retdir1["nerror"]==0){

                $archivo     = $row->archivo;
                $archivo_new = $row->id_depto_padre.'-'.$archivo;

                copy($carpeta.'/'.$archivo,$carpeta1.'/'.$archivo_new);

            }
        }
    }

    public static function showDirectory($id_entidad, $id_anho,$id_mes){
        $retdir1     =  PaymentsData::directorioBoleta($id_entidad, $id_anho,$id_mes,'copia');
        $ruta    = $retdir1["directorio"];
        $html = '';
        if($retdir1["nerror"]==0){
            $html = PaymentsData::getDirectoryStructure($ruta);
        }
        return $html;
    }

    private static function getDirectoryStructure($ruta){
        // Se comprueba que realmente sea la ruta de un directorio

        $html = '';
        if (is_dir($ruta)){
            // Abre un gestor de directorios para la ruta indicada
            $gestor = opendir($ruta);
            $html.= "<ul>";

            // Recorre todos los elementos del directorio
            while (($archivo = readdir($gestor)) !== false)  {

                $ruta_completa = $ruta . "/" . $archivo;

                // Se muestran todos los archivos y carpetas excepto "." y ".."
                if ($archivo != "." && $archivo != "..") {
                    // Si es un directorio se recorre recursivamente
                    if (is_dir($ruta_completa)) {
                        $html.=  "<li>" . $archivo . "</li>";
                        $html.= PaymentsData::getDirectoryStructure($ruta_completa);
                    } else {
                        $html.=  "<li>" . $archivo . "</li>";
                    }
                }
            }

            // Cierra el gestor de directorios
            closedir($gestor);
            $html.=  "</ul>";
        } else {
            $html.=  "No es una ruta de directorio valida<br/>";
        }
        return $html;
    }

    public static function saveFilePdf($file, $folder, $filename='')
    {
        if ($filename != null && $filename != "" && $filename != "null") {
            $status = Storage::disk('minio-talent')->exists($folder.'/'.$filename);
            if ($status) {
                Storage::disk('minio-talent')->delete($folder.'/'.$filename);
            }
        } else {

            $filename = uniqid() . '.pdf';
        }
        Storage::disk('minio-talent')->put($folder.'/'.$filename, $file);
        return $filename;
    }
    public static function getUrlByName($fileName)
    {
        $ret = self::checkFileExists($fileName);
        if($ret['nerror']==0){ 
            $data = Storage::disk('minio-talent')->temporaryUrl($fileName, Carbon::now()->addHour(15));
            $ret =['nerror'=>0, 'message'=>'ok','data'=>$data];
        }
        return $ret;

    }
    public static function checkFileExists($fileName)
    {
        $status = Storage::disk('minio-talent')->exists($fileName);
        if (!$status) {
            $ret =['nerror'=>1, 'message'=>'Archivo no encontrado en el servidor de almacenamiento'];
            return $ret;
        }
        $ret =['nerror'=>0, 'message'=>'ok'];
        return $ret;
    }
    public static function saveFile($file, $folder, $filename='')
    {
        if ($filename != null && $filename != "" && $filename != "null") {
            $status = Storage::disk('minio-talent')->exists($folder.'/'.$filename);
            if ($status) {
                Storage::disk('minio-talent')->delete($folder.'/'.$filename);
            }
        } else {
            $ext = $file->getClientOriginalExtension();
            $filename = uniqid() . '.' . $ext;
        }
        Storage::disk('minio-talent')->putFileAs($folder, $file, $filename);
        return $filename;
    }
    public static function responseFile($fileName)
    {
       return Storage::disk('minio-talent')->get($fileName);
    }
}
