<?php

namespace App\Http\Data\Accounting\Setup;

use App\Http\Controllers\Controller;
use App\Models\AccountingVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use App\Http\Data\Setup\Organization\ManagerData;
use PDO;
use Exception;
use Illuminate\Support\Facades\Log;

class AccountingData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function listConfigVoucher($entity, $year, $opc)
    {
        $query = "SELECT
                        A.ID_ENTIDAD,A.ID_ANHO,A.ID_DEPTO, A.ID_TIPOASIENTO,B.NOMBRE AS ASIENTO,A.ID_MODULO,C.NOMBRE AS MODULO,A.ID_ANHO,A.FECHA,A.AUTOMATICO,A.NOMBRE
                FROM CONTA_VOUCHER_CONFIG A, TIPO_ASIENTO B, LAMB_MODULO C
                WHERE A.ID_TIPOASIENTO = B.ID_TIPOASIENTO
                AND A.ID_MODULO = C.ID_MODULO
                AND A.ID_ENTIDAD = $entity
                AND A.ID_ANHO = $year
                $opc
                AND C.NIVEL = 1
                ORDER BY A.ID_TIPOASIENTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showConfigVoucher($entity, $year, $id_depto, $id_tipoasiento)
    {
        $query = "SELECT ID_ENTIDAD,ID_DEPTO,ID_TIPOASIENTO,ID_ANHO,ID_MODULO,FECHA,AUTOMATICO,NOMBRE, ID_TIPOVOUCHER,
                COALESCE(ID_TIPOASIENTO_PARENT, '0') ID_TIPOASIENTO_PARENT, ID_SISTEMAEXTERNO
                FROM CONTA_VOUCHER_CONFIG
                WHERE ID_ENTIDAD = $entity
                AND ID_DEPTO = '$id_depto'
                AND ID_TIPOASIENTO = '$id_tipoasiento'
                AND ID_ANHO = $year ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function addConfigVoucher(
        $id_tipoasiento,
        $entity,
        $id_depto,
        $id_modulo,
        $id_tipovoucher,
        $year,
        $automatico,
        $nombre,
        $id_sistemaexterno,
        $id_tipoasiento_parent
    ) {
        DB::table('CONTA_VOUCHER_CONFIG')->insert(
            array(
                'ID_ENTIDAD' => $entity,
                'ID_DEPTO' => $id_depto,
                'ID_TIPOASIENTO' => $id_tipoasiento,
                'ID_ANHO' => $year,
                'ID_MODULO' => $id_modulo,
                /*'FECHA'=> 'sysdate',*/
                'AUTOMATICO' => $automatico,
                'NOMBRE' => $nombre,
                'ID_TIPOVOUCHER' => $id_tipovoucher,
                'ID_SISTEMAEXTERNO' => $id_sistemaexterno,
                'ID_TIPOASIENTO_PARENT' => $id_tipoasiento_parent,
            )
        );
    }
    public static function updateConfigVoucher($id_tipoasiento, $entity, $id_depto, $id_modulo, $id_tipovoucher, $year, $automatico, $nombre, $id_sistemaexterno, $id_tipoasiento_parent)
    {
        if ($id_tipoasiento_parent === '0' || $id_tipoasiento_parent === 0) {
            $id_tipoasiento_parent = null;
        }

        $query = "UPDATE CONTA_VOUCHER_CONFIG SET ID_MODULO = $id_modulo,
                  AUTOMATICO = '$automatico', NOMBRE = '$nombre',
                  -- ID_TIPOVOUCHER = $id_tipovoucher,
                   ID_SISTEMAEXTERNO  = $id_sistemaexterno,
                  ID_TIPOASIENTO_PARENT = '$id_tipoasiento_parent'
                  WHERE ID_ENTIDAD = $entity
                  AND ID_DEPTO = '$id_depto'
                  AND ID_TIPOASIENTO = '$id_tipoasiento'
                  AND ID_ANHO = $year ";
        DB::update($query);
    }
    public static function deleteConfigVoucher($entity, $depto, $year, $id_tipoasiento)
    {
        $query = "DELETE
                FROM CONTA_VOUCHER_CONFIG
                WHERE ID_ENTIDAD = $entity
                AND ID_DEPTO = '$depto'
                AND ID_TIPOASIENTO = '$id_tipoasiento'
                AND ID_ANHO = $year ";
        $oQuery = DB::delete($query);
        return $oQuery;
    }
    public static function listDeptoParent($entity)
    {
        $query = "SELECT
                        ID_DEPTO,NOMBRE
                FROM CONTA_ENTIDAD_DEPTO
                WHERE ID_ENTIDAD = $entity
                AND ES_GRUPO = 1
                AND LENGTH(ID_DEPTO) = 1
                ORDER BY ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTipoAsiento()
    {
        $query = "SELECT
                        ID_TIPOASIENTO,
                        NOMBRE
                FROM TIPO_ASIENTO
                ORDER BY ID_TIPOASIENTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTipoAsientoEntidad($entity, $year, $depto)
    {
        /*
        $query = "SELECT
                        A.ID_TIPOASIENTO||'-'||C.ID_TIPOVOUCHER as ID_TIPOASIENTO,
                        --A.NOMBRE
                        --A.ID_TIPOASIENTO,id
                        A.NOMBRE||' - '||C.NOMBRE AS NOMBRE,
                        B.ID_TIPOASIENTO_PARENT
                FROM TIPO_ASIENTO A, CONTA_VOUCHER_CONFIG B, TIPO_VOUCHER C
                WHERE A.ID_TIPOASIENTO = B.ID_TIPOASIENTO
                AND B.ID_TIPOVOUCHER = C.ID_TIPOVOUCHER
                AND B.ID_ENTIDAD = $entity
                AND B.ID_ANHO = $year
                AND B.ID_DEPTO = '".$depto."'
                ORDER BY A.ID_TIPOASIENTO ";
        */
        $query = "
            SELECT
                        A.ID_TIPOASIENTO||'-'||C.ID_TIPOVOUCHER as ID_TIPOASIENTO,
                        A.NOMBRE||' - '||C.NOMBRE AS NOMBRE,
                        B.ID_TIPOASIENTO_PARENT

                        ,(CASE
                            WHEN B.ID_TIPOASIENTO_PARENT IS NOT NULL
                                THEN
                                --'hOLA'
                               ( SELECT X_A.ID_TIPOASIENTO || '-' || X_C.ID_TIPOVOUCHER as ID_TIPOASIENTO
                                FROM TIPO_ASIENTO X_A, CONTA_VOUCHER_CONFIG X_B, TIPO_VOUCHER X_C
                                WHERE X_A.ID_TIPOASIENTO = X_B.ID_TIPOASIENTO
                                AND X_B.ID_TIPOVOUCHER = X_C.ID_TIPOVOUCHER
                                AND X_B.ID_ENTIDAD = $entity
                                AND X_B.ID_ANHO = $year
                                AND X_B.ID_DEPTO = '" . $depto . "'
                                AND X_B.ID_TIPOASIENTO = B.ID_TIPOASIENTO_PARENT)
                            ELSE ''
                        END)
                        AS ID_TIPOASIENTO_PARENT

                        ,(CASE
                            WHEN B.ID_TIPOASIENTO_PARENT IS NOT NULL
                                THEN
                               (SELECT Y_A.NOMBRE||' - ' || Y_C.NOMBRE AS NOMBRE
                                FROM TIPO_ASIENTO Y_A, CONTA_VOUCHER_CONFIG Y_B, TIPO_VOUCHER Y_C
                                WHERE Y_A.ID_TIPOASIENTO = Y_B.ID_TIPOASIENTO
                                AND Y_B.ID_TIPOVOUCHER = Y_C.ID_TIPOVOUCHER
                                AND Y_B.ID_ENTIDAD = $entity
                                AND Y_B.ID_ANHO = $year
                                AND Y_B.ID_DEPTO = '" . $depto . "'
                                AND Y_B.ID_TIPOASIENTO = B.ID_TIPOASIENTO_PARENT)
                            ELSE ''
                        END)
                        AS NOMBRE_PARENT

                FROM TIPO_ASIENTO A, CONTA_VOUCHER_CONFIG B, TIPO_VOUCHER C
                WHERE A.ID_TIPOASIENTO = B.ID_TIPOASIENTO
                AND B.ID_TIPOVOUCHER = C.ID_TIPOVOUCHER
                AND B.ID_ENTIDAD = $entity
                AND B.ID_ANHO = $year
                AND B.ID_DEPTO = '" . $depto . "'
                ORDER BY A.ID_TIPOASIENTO";

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTipoComprobante($tipo)
    {
        if ($tipo == "v") { //VENTAS
            $dato = "WHERE TIPO = 'T' ";
        } elseif ($tipo == "n") { //NOTAS DE CREDITO Y DEBITO
            $dato = "WHERE TIPO = 'N' ";
        } elseif ($tipo == "c") { //COMPRAS
            $dato = "WHERE TIPO IN ('T','C',N')";
        } else {
            $dato = "WHERE TIPO = '" . $tipo . "' ";
        }
        $query = "SELECT
                        ID_COMPROBANTE,NOMBRE,DECODE(ID_COMPROBANTE,'03','1','0') DEFAULT_SELECTED, (CASE WHEN ID_COMPROBANTE = '07' THEN 'C' WHEN ID_COMPROBANTE = '87' THEN 'C' ELSE '' END) AS COMPROBANTE
                FROM TIPO_COMPROBANTE
                " . $dato . "
                ORDER BY ID_COMPROBANTE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTipoComprobanteAll()
    {
        $query = "SELECT
                        ID_COMPROBANTE,NOMBRE
                FROM TIPO_COMPROBANTE
                ORDER BY ID_COMPROBANTE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listVoucher($id_entidad, $id_depto, $id_anho, $id_mes, $id_tipoasiento, $id_tipovoucher, $id_user)
    {
        $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.LOTE,A.ACTIVO,A.NUMERO,
                        A.ID_ENTIDAD, A.ID_DEPTO,
                        DECODE(NVL((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1'),0),0,0,1) ASIGNADO,

                        TA.NOMBRE || ' ' || T.NOMBRE AS  TIPO_ASIENTO_NOMBRE,
                        A.ID_VOUCHER_PARENT,
                        (SELECT email FROM users u WHERE u.id = A.ID_PERSONA) AS USER_CREATED,

                        (CASE WHEN A.ID_VOUCHER_PARENT IS NOT NULL THEN
                        (SELECT ( X_A.NUMERO || '-' || TO_CHAR(X_A.FECHA,'DD/MM/YYYY') || ' ' || X_TA.NOMBRE || ' ' || X_T.NOMBRE ) AS NNNN
                        FROM CONTA_VOUCHER X_A
                         INNER JOIN TIPO_VOUCHER X_T ON X_A.ID_TIPOVOUCHER = X_T.ID_TIPOVOUCHER
                         INNER JOIN TIPO_ASIENTO X_TA ON X_A.ID_TIPOASIENTO = X_TA.ID_TIPOASIENTO
                        WHERE X_A.ID_VOUCHER = A.ID_VOUCHER_PARENT )
                        ELSE '' END) AS TIPO_ASIENTO_NOMBRE_PARENT

                FROM CONTA_VOUCHER A INNER JOIN TIPO_VOUCHER T ON A.ID_TIPOVOUCHER = T.ID_TIPOVOUCHER
                 INNER JOIN TIPO_ASIENTO TA ON A.ID_TIPOASIENTO = TA.ID_TIPOASIENTO
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '$id_depto'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOASIENTO = '$id_tipoasiento'
                AND A.ID_TIPOVOUCHER = $id_tipovoucher";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteVoucher($idVoucher)
    {
        //1 | 0 : 0 =>  no eliminado , 1 => eliminado;
        return DB::table("ELISEO.CONTA_VOUCHER")
            ->where("ID_VOUCHER", $idVoucher) // por deeecto comparacion =
            ->delete();
    }

    public static function listMyVoucher($id_entidad, $id_depto, $id_anho, $id_mes, $id_tipoasiento, $id_tipovoucher, $id_user, $all_voucher)
    {
        $dato = "";
        if (!$all_voucher) {
            $dato = "
            AND A.ID_DEPTO = '$id_depto'
            AND A.ID_PERSONA = $id_user";
        }
        $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.LOTE,A.ACTIVO,A.NUMERO,
                        A.ID_ENTIDAD, A.ID_DEPTO,
                        DECODE(NVL((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1'),0),0,0,1) ASIGNADO,

                        T.NOMBRE AS  TIPO_ASIENTO_NOMBRE,
                        TA.ID_TIPOASIENTO AS  TIPO_ASIENTO_TIPO,
                        A.ID_VOUCHER_PARENT,
                        (SELECT email FROM users u WHERE u.id = A.ID_PERSONA) AS USER_CREATED,

                        (CASE WHEN A.ID_VOUCHER_PARENT IS NOT NULL THEN
                        (SELECT ( X_A.NUMERO || '-' || TO_CHAR(X_A.FECHA,'DD/MM/YYYY') || ' ' || X_T.NOMBRE ) AS NNNN
                        FROM CONTA_VOUCHER X_A
                         INNER JOIN TIPO_VOUCHER X_T ON X_A.ID_TIPOVOUCHER = X_T.ID_TIPOVOUCHER
                         INNER JOIN TIPO_ASIENTO X_TA ON X_A.ID_TIPOASIENTO = X_TA.ID_TIPOASIENTO
                        WHERE X_A.ID_VOUCHER = A.ID_VOUCHER_PARENT )
                        ELSE '' END) AS TIPO_ASIENTO_NOMBRE_PARENT,

                        (SELECT COUNT(*) FROM COMPRA CO WHERE CO.ID_VOUCHER=A.ID_VOUCHER) AS CANTIDAD_COMPRA,
                        (SELECT COUNT(*) FROM CAJA_PAGO CP WHERE CP.ID_VOUCHER=A.ID_VOUCHER) AS CANTIDAD_PAGO,
                        (SELECT COUNT(*) FROM CAJA_RETENCION CR WHERE CR.ID_VOUCHER=A.ID_VOUCHER) AS CANTIDAD_RETENCION,
                        (SELECT COUNT(*) FROM CAJA_DETRACCION CD WHERE CD.ID_VOUCHER=A.ID_VOUCHER) AS CANTIDAD_DETRACCION

                FROM CONTA_VOUCHER A INNER JOIN TIPO_VOUCHER T ON A.ID_TIPOVOUCHER = T.ID_TIPOVOUCHER
                 INNER JOIN TIPO_ASIENTO TA ON A.ID_TIPOASIENTO = TA.ID_TIPOASIENTO
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOASIENTO = '$id_tipoasiento'
                $dato
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                ORDER BY A.ID_VOUCHER DESC";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showVoucher($id_voucher)
    {
        $query = "SELECT
                       ID_VOUCHER,ID_ENTIDAD,ID_DEPTO,ID_TIPOASIENTO,ID_TIPOVOUCHER,ID_ANHO,ID_MES,NUMERO,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,NVL(LOTE,' ') LOTE,ACTIVO,TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO
               FROM CONTA_VOUCHER
               WHERE ID_VOUCHER = " . $id_voucher . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getInicialesNombreUsuario($id_user)
    {
        $query = "SELECT  COALESCE(SUBSTR(NOMBRE,1,1),'')||
                        COALESCE(SUBSTR(PATERNO,1,1),'')||
                        COALESCE(SUBSTR(MATERNO,1,1),'') AS INICIALES FROM MOISES.VW_PERSONA_NATURAL
                WHERE id_persona = $id_user ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getMonthById($id_mes)
    {
        $mes = DB::table('CONTA_MES')->where('ID_MES', '=', $id_mes);
        return $mes->first();
    }

    public static function getVoucherById($id_voucher)
    {
        $query = " SELECT
            ID_VOUCHER, ID_ENTIDAD, ID_DEPTO, ID_ANHO,
            ID_MES, ID_TIPOASIENTO, ID_TIPOVOUCHER, NUMERO,
            LOTE, FECHA, ACTIVO, ID_VOUCHER_PARENT, ID_PERSONA
            FROM CONTA_VOUCHER
            WHERE ID_VOUCHER=" . $id_voucher;
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getVoucherChildFirstByIdParent($id_voucher)
    {
        $query = "SELECT
            ID_VOUCHER, ID_ENTIDAD, ID_DEPTO, ID_ANHO,
            ID_MES, ID_TIPOASIENTO, ID_TIPOVOUCHER, NUMERO,
            LOTE, FECHA, ACTIVO, ID_VOUCHER_PARENT, ID_PERSONA
            FROM CONTA_VOUCHER
            WHERE ID_VOUCHER_PARENT=" . $id_voucher . "
            AND ROWNUM = 1";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getRazonSocialEmpresaByEntidadId($id_entidad)
    {
        $query = "SELECT FC_EMPRESA_NOMBRE($id_entidad, '1') AS RAZON_SOCIAL FROM DUAL";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function voucherAutomatico($id_entidad, $id_depto, $id_tipoasiento, $id_tipovoucher, $id_anho)
    {
        $query = "SELECT
                        AUTOMATICO
                FROM CONTA_VOUCHER_CONFIG
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '" . $id_depto . "'
                AND ID_TIPOASIENTO = '$id_tipoasiento'
                AND ID_TIPOVOUCHER = $id_tipovoucher
                AND ID_ANHO = $id_anho ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showVoucherConfig($id_entidad, $depto, $id_tipovoucher, $year)
    {
        $query = "SELECT
                        ID_TIPOASIENTO
                FROM CONTA_VOUCHER_CONFIG
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '" . $depto . "'
                AND ID_TIPOVOUCHER = $id_tipovoucher
                AND ID_ANHO = $year ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function validaVoucherDia($id_voucher)
    {
        $query = "SELECT
                TRUNC((FECHA - SYSDATE)) DIAS
                FROM CONTA_VOUCHER
                WHERE ID_VOUCHER = $id_voucher ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateVoucher($id_voucher, $activo)
    {
        $query = "UPDATE CONTA_VOUCHER SET  ACTIVO = '" . $activo . "'
                    WHERE ID_VOUCHER = $id_voucher  ";
        DB::update($query);
    }
    public static function editVoucher($idVoucher, $params)
    {
        DB::table("CONTA_VOUCHER")
            ->where('id_voucher', $idVoucher)
            ->update(
                array(
                    'lote' => $params['lote'],
                    'fecha' => $params['fecha'],
                )
            );

        return AccountingVoucher::where('id_voucher', $idVoucher)
            ->first();
    }
    public static function updateVoucherLote($id_voucher, $lote, $activo)
    {
        $query = "UPDATE CONTA_VOUCHER SET  LOTE = '" . $lote . "', ACTIVO = '" . $activo . "'
                    WHERE ID_VOUCHER = $id_voucher  ";
        DB::update($query);
    }
    public static function listDocumentoImpresion($id_entidad, $depto)
    {
        $query = "SELECT
                        A.ID_DOCUMENTO,B.ID_ENTIDAD,B.ID_DEPTO,C.ID_COMPROBANTE||'-'||C.NOMBRE AS COMPROBANTE, A.NOMBRE,A.SERIE,A.CONTADOR,A.ACTIVO
                FROM CONTA_DOCUMENTO A, CONTA_ENTIDAD_DEPTO B, TIPO_COMPROBANTE C
                WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                AND A.ID_DEPTO = B.ID_DEPTO
                AND A.ID_COMPROBANTE = C.ID_COMPROBANTE
                AND A.ID_ENTIDAD = '$id_entidad'
                AND A.ID_DEPTO = '$depto'
                ORDER BY A.SERIE,A.NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showDocumentoImpresion($id_documento)
    {
        $query = "SELECT
                        ID_DOCUMENTO,
                        ID_COMPROBANTE,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        NOMBRE,
                        PUERTO,
                        NUMLINE,
                        NUMCOL,
                        SERIE,
                        CONTADOR,
                        ID_COMPROBANTE_AFECTO
                FROM CONTA_DOCUMENTO
                WHERE ID_DOCUMENTO = $id_documento ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showDocumentoImpresionSerie($entity, $serie)
    {
        $query = "SELECT
                        ID_DOCUMENTO,
                        ID_COMPROBANTE,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        NOMBRE,
                        PUERTO,
                        NUMLINE,
                        NUMCOL,
                        SERIE,
                        CONTADOR,
                        ID_COMPROBANTE_AFECTO
                FROM CONTA_DOCUMENTO
                WHERE ID_ENTIDAD = $entity
                AND SERIE = '$serie' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addDocumentoImpresion($id_comprobante, $entity, $depto, $nombre, $puerto, $linea, $columna, $serie, $contador, $id_comprobante_afecto)
    {
        $data = DB::table('CONTA_DOCUMENTO')->insert(
            array(
                'ID_COMPROBANTE' => $id_comprobante,
                'ID_ENTIDAD' => $entity,
                'ID_DEPTO' => $depto,
                'NOMBRE' => $nombre,
                'PUERTO' => $puerto,
                'NUMLINE' => $linea,
                'NUMCOL' => $columna,
                'SERIE' => $serie,
                'CONTADOR' => $contador,
                'ID_COMPROBANTE_AFECTO' => $id_comprobante_afecto
            )
        );
        $query = "SELECT ID_DOCUMENTO,
                        ID_COMPROBANTE,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        NOMBRE,
                        PUERTO,
                        NUMLINE,
                        NUMCOL,
                        SERIE,
                        CONTADOR,
                        ID_COMPROBANTE_AFECTO
                FROM CONTA_DOCUMENTO
                WHERE ID_ENTIDAD = $entity
                AND ID_DEPTO = $depto
                AND SERIE = '$serie' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateDocumentoImpresion($id_documento, $id_comprobante, $nombre, $puerto, $linea, $columna, $serie, $contador, $id_comprobante_afecto)
    {
        $query = "UPDATE CONTA_DOCUMENTO SET ID_COMPROBANTE = '$id_comprobante',
                                                NOMBRE = '$nombre',
                                                PUERTO = '$puerto',
                                                NUMLINE = $linea,
                                                NUMCOL = $columna,
                                                SERIE = '$serie',
                                                CONTADOR = $contador,
                                                ID_COMPROBANTE_AFECTO = '$id_comprobante_afecto'
                    WHERE ID_DOCUMENTO = $id_documento ";
        DB::update($query);

        $query = "SELECT ID_DOCUMENTO,
                        ID_COMPROBANTE,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        NOMBRE,
                        PUERTO,
                        NUMLINE,
                        NUMCOL,
                        SERIE,
                        CONTADOR,
                        ID_COMPROBANTE_AFECTO
                FROM CONTA_DOCUMENTO
                WHERE ID_DOCUMENTO = $id_documento ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateDocumentoImpresionAtrr($id_documento, $clave, $valor)
    {
        $query = "UPDATE CONTA_DOCUMENTO SET $clave = '$valor'
                    WHERE ID_DOCUMENTO = $id_documento ";
        DB::update($query);
    }
    public static function listDocumentoImpresionDetails($id_documento)
    {
        $query = "SELECT
                        ID_DOCDETALLE,
                        ID_DOCUMENTO,
                        CONTENIDO,
                        MODO,
                        TIPO,
                        POS_X,
                        POS_Y
                FROM CONTA_DOCUMENTO_DETALLE A
                WHERE A.ID_DOCUMENTO = $id_documento
                ORDER BY POS_Y,POS_X,CONTENIDO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showDocumentoImpresionDetails($id_docdetalle)
    {
        $query = "SELECT
                        ID_DOCDETALLE,
                        ID_DOCUMENTO,
                        CONTENIDO,
                        MODO,
                        TIPO,
                        POS_X,
                        POS_Y
                FROM CONTA_DOCUMENTO_DETALLE A
                WHERE A.ID_DOCDETALLE = $id_docdetalle ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateDocumentoImpresionDetails($id_docdetalle, $id_documento, $contenido, $modo, $tipo, $pos_x, $pos_y)
    {
        $query = "UPDATE CONTA_DOCUMENTO_DETALLE SET    ID_DOCUMENTO = $id_documento,
                                                        CONTENIDO = '$contenido',
                                                        MODO = '$modo',
                                                        TIPO = '$tipo',
                                                        POS_X = $pos_x,
                                                        POS_Y = $pos_y
                WHERE ID_DOCDETALLE = $id_docdetalle ";
        DB::update($query);

        $query = "SELECT
                        ID_DOCDETALLE,
                        ID_DOCUMENTO,
                        CONTENIDO,
                        MODO,
                        TIPO,
                        POS_X,
                        POS_Y
                FROM CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCUMENTO = $id_documento
                AND ID_DOCDETALLE = $id_docdetalle ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteDocumentoImpresionDetails($id_docdetalle)
    {
        $query = "DELETE CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCDETALLE = $id_docdetalle ";
        DB::delete($query);
    }
    public static function deleteDocumentoImpresionDetailsByIdDocumento($id_documento)
    {
        $query = "DELETE CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCUMENTO = $id_documento ";
        DB::delete($query);
    }
    public static function deleteDocumentoImpresion($id_documento)
    {
        $query = "DELETE CONTA_DOCUMENTO
                WHERE ID_DOCUMENTO = $id_documento ";
        DB::delete($query);
    }
    public static function addDocumentoImpresionDetails($id_documento, $contenido, $modo, $tipo, $pos_x, $pos_y)
    {
        $data = DB::table('CONTA_DOCUMENTO_DETALLE')->insert(
            array(
                'ID_DOCUMENTO' => $id_documento,
                'CONTENIDO' => $contenido,
                'MODO' => $modo,
                'TIPO' => $tipo,
                'POS_X' => $pos_x,
                'POS_Y' => $pos_y
            )
        );
        $query = "SELECT
                        MAX(ID_DOCDETALLE) id_detalle
                FROM CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCUMENTO = $id_documento ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id = $item->id_detalle;
        }
        $query = "SELECT
                        ID_DOCDETALLE,
                        ID_DOCUMENTO,
                        CONTENIDO,
                        MODO,
                        TIPO,
                        POS_X,
                        POS_Y
                FROM CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCUMENTO = $id_documento
                AND ID_DOCDETALLE = $id ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPeriodos($entity, $year)
    {
        $query = "SELECT
                        ID_ENTIDAD,ID_ANHO
                FROM CONTA_ENTIDAD_ANHO_CONFIG
                WHERE ID_ENTIDAD = $entity
                AND ID_ANHO = $year  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPeriodosActivos($entity)
    {
        $query = "SELECT
                        ID_ENTIDAD,ID_ANHO
                FROM CONTA_ENTIDAD_ANHO_CONFIG
                WHERE ID_ENTIDAD = $entity
                AND ACTIVO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPeriodosMeses($entity, $anho)
    {
        $query = "SELECT
                    A.ID_ENTIDAD,A.ID_ANHO,A.ID_MES,B.NOMBRE,
                    B.SIGLAS,A.FECHA_INICIO,A.FECHA_FIN,A.FECHA_RE_INICIO,A.ESTADO,
                    (SELECT email FROM users u WHERE u.id = a.ID_USER_INICIO) AS USER_INICIO,
                    (SELECT email FROM users u WHERE u.id = a.ID_USER_FIN) AS USER_FIN,
                    (SELECT email FROM users u WHERE u.id = a.ID_USER_RE_INICIO) AS USER_RE_INICIO
                    FROM CONTA_ENTIDAD_MES_CONFIG A, CONTA_MES B
                    WHERE A.ID_MES = B.ID_MES
                    AND A.ID_ENTIDAD = $entity
                    AND A.ID_ANHO = $anho
                    ORDER BY A.ID_MES ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showMesStatus($entity, $anho, $mes)
    {
        $query = "SELECT
                    ID_MES,ESTADO
                    FROM CONTA_ENTIDAD_MES_CONFIG
                    WHERE ID_ENTIDAD = $entity
                    AND ID_ANHO = $anho
                    AND ID_MES = $mes ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showMesStatusOpen($entity, $anho, $estado)
    {
        $query = "SELECT
                    ID_MES,ESTADO
                    FROM CONTA_ENTIDAD_MES_CONFIG
                    WHERE ID_ENTIDAD = $entity
                    AND ID_ANHO = $anho
                    AND ESTADO = '$estado' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updatePeriodoMes($entity, $anho, $mes, $status, $id_user)
    {
        if ($status == "2") {
            $query = "UPDATE CONTA_ENTIDAD_MES_CONFIG
                        SET FECHA_FIN = SYSDATE,
                            ESTADO = '$status',
                            ID_USER_FIN = $id_user
                        WHERE ID_ENTIDAD = $entity
                        AND ID_ANHO = $anho
                        AND ID_MES = $mes ";
            DB::update($query);
        } else {
            $query = "UPDATE CONTA_ENTIDAD_MES_CONFIG
                        SET FECHA_INICIO = case when ESTADO = '0' then SYSDATE else FECHA_INICIO end,
                            FECHA_RE_INICIO = case when ESTADO = '2' then SYSDATE else FECHA_RE_INICIO end,
                            ESTADO = '$status',
                            ID_USER_INICIO = case when ESTADO = '0' then $id_user else ID_USER_INICIO end,
                            ID_USER_RE_INICIO = case when ESTADO = '2' then $id_user else ID_USER_RE_INICIO end
                        WHERE ID_ENTIDAD = $entity
                        AND ID_ANHO = $anho
                        AND ID_MES = $mes ";
            DB::update($query);
        }
    }
    public static function showPeriodoStatus($entity, $anho)
    {
        $query = "SELECT A.ID_ENTIDAD, A.ID_ANHO, B.NOMBRE AS TIPOPLAN_NOMBRE, A.NOMBRE, TO_CHAR(A.FECHA_INICIO,'DD/MM/YYYY') AS FECHA_INICIO,
                    TO_CHAR(A.FECHA_FIN,'DD/MM/YYYY') AS FECHA_FIN, A.ACTIVO
                    FROM CONTA_ENTIDAD_ANHO_CONFIG A INNER JOIN TIPO_PLAN B ON A.ID_TIPOPLAN=B.ID_TIPOPLAN
                    WHERE A.ID_ENTIDAD = $entity

                    AND A.ID_ANHO = $anho ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function showPeriodosActivos($entity, $anho)
    {
        $query = "SELECT A.ID_ENTIDAD, A.ID_ANHO, B.NOMBRE AS TIPOPLAN_NOMBRE, A.NOMBRE, TO_CHAR(A.FECHA_INICIO,'DD/MM/YYYY') AS FECHA_INICIO,
                    TO_CHAR(A.FECHA_FIN,'DD/MM/YYYY') AS FECHA_FIN, A.ACTIVO
                    FROM CONTA_ENTIDAD_ANHO_CONFIG A INNER JOIN TIPO_PLAN B ON A.ID_TIPOPLAN=B.ID_TIPOPLAN
                    WHERE A.ID_ENTIDAD = $entity
                    AND A.ID_ANHO = $anho
                    AND A.ACTIVO = 1
                     ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function updatePeriodo($entity, $anho, $activo)
    {
        $query = "UPDATE CONTA_ENTIDAD_ANHO_CONFIG SET ACTIVO = $activo
                    WHERE ID_ENTIDAD = $entity
                    AND ID_ANHO = $anho ";
        DB::update($query);
    }
    public static function listTipoPlan()
    {
        $query = "SELECT ID_TIPOPLAN,NOMBRE
                FROM TIPO_PLAN
                ORDER BY ID_TIPOPLAN ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function planCtaEnterprise($parent)
    {
        if ($parent == "A") {
            $dato = "WHERE ID_PARENT IS NULL";
        } else {
            $dato = "WHERE ID_PARENT = $parent";
        }
        $query = "SELECT
                        ID_CUENTAEMPRESARIAL,
                        ID_PARENT,
                        NOMBRE
                FROM CONTA_CTA_EMPRESARIAL
                $dato
                ORDER BY ID_CUENTAEMPRESARIAL ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function planCtaEnterprisePcgeV2($textSearch, $anho, $id_empresa)
    {
        $ntext_search = str_replace(' ', '', ManagerData::stripAccents($textSearch));
        $utext_search = strtoupper($ntext_search);

        $query = "
            SELECT
                a.ID_CUENTAEMPRESARIAL, a.ID_PARENT, a.NOMBRE,
                b.ID_ANHO, b.ID_EMPRESA, b.ID_CUENTAAASI, c.ID_RESTRICCION, b.ID_MONEDA, b.ID_TIPOPLAN, c.NOMBRE AS NOMBRE_CUENTAAASI, d.RAZON_SOCIAL AS EMPRESA_NOMBRE_2
            FROM ELISEO.CONTA_CTA_EMPRESARIAL a
                LEFT JOIN ELISEO.CONTA_EMPRESA_CTA b ON a.ID_CUENTAEMPRESARIAL = b.ID_CUENTAEMPRESARIAL AND b.ID_EMPRESA = $id_empresa AND b.ID_ANHO = $anho
                LEFT JOIN ELISEO.CONTA_CTA_DENOMINACIONAL c ON b.ID_CUENTAAASI = c.ID_CUENTAAASI AND c.ID_TIPOPLAN = 1
                LEFT JOIN ELISEO.CONTA_EMPRESA d ON b.ID_EMPRESA = d.ID_EMPRESA
            WHERE 1 = 1
                AND CONVERT(replace(upper(coalesce(a.ID_CUENTAEMPRESARIAL,'')||coalesce(a.ID_PARENT,'')||coalesce(a.NOMBRE,'')), ' ', ''), 'US7ASCII') like '%$utext_search%'
            ORDER BY ID_CUENTAEMPRESARIAL
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function planCtaEnterprisePcdV2($textSearch, $anho, $id_empresa)
    {
        $ntext_search = str_replace(' ', '', ManagerData::stripAccents($textSearch));
        $utext_search = strtoupper($ntext_search);

        $query = "
            SELECT
                c.ID_CUENTAAASI, c.ID_PARENT, c.NOMBRE, c.ID_TIPOCTACTE, tcc.NOMBRE AS NOMBRE_TIPOCTACTE, c.ES_GRUPO, c.ES_ACTIVA, c.ES_ACREEDORA, b.ID_TIPOPLAN,
                b.ID_ANHO, b.ID_EMPRESA, c.ID_RESTRICCION, b.ID_TIPOPLAN, a.NOMBRE AS NOMBRE_EMPRESARIAL, b.ID_CUENTAEMPRESARIAL, d.RAZON_SOCIAL AS EMPRESA_NOMBRE_2
            FROM ELISEO.CONTA_CTA_DENOMINACIONAL c
                LEFT JOIN ELISEO.CONTA_EMPRESA_CTA b ON c.ID_CUENTAAASI = b.ID_CUENTAAASI AND b.ID_EMPRESA = $id_empresa AND b.ID_ANHO = $anho
                LEFT JOIN ELISEO.CONTA_CTA_EMPRESARIAL a ON b.ID_CUENTAEMPRESARIAL = a.ID_CUENTAEMPRESARIAL
                LEFT JOIN ELISEO.CONTA_EMPRESA d ON b.ID_EMPRESA = d.ID_EMPRESA
                LEFT JOIN ELISEO.TIPO_CTA_CORRIENTE tcc ON c.ID_TIPOCTACTE = tcc.ID_TIPOCTACTE
            WHERE 1 = 1
                AND c.ID_TIPOPLAN = 1
                AND CONVERT(replace(upper(coalesce(c.ID_CUENTAAASI,'')||coalesce(c.ID_PARENT,'')||coalesce(c.NOMBRE,'')), ' ', ''), 'US7ASCII') like '%$utext_search%'
            ORDER BY ID_CUENTAAASI
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function showPlanAccountingEnterprise($id_cuentaempresarial)
    {
        $query = "SELECT
                        A.ID_CUENTAEMPRESARIAL,
                        A.ID_PARENT,
                        A.NOMBRE,
                        NVL((SELECT X.NOMBRE FROM CONTA_CTA_EMPRESARIAL X WHERE X.ID_CUENTAEMPRESARIAL = A.ID_PARENT ),' ') NAME_PARENT
                FROM CONTA_CTA_EMPRESARIAL A
                WHERE A.ID_CUENTAEMPRESARIAL = $id_cuentaempresarial ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showPlanAccountingEnterpriseParent($id_cuentaempresarial)
    {
        $query = "SELECT
                        ID_CUENTAEMPRESARIAL,
                        ID_PARENT,
                        NOMBRE
                FROM CONTA_CTA_EMPRESARIAL
                WHERE ID_PARENT = $id_cuentaempresarial ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addPlanAccountingEnterprise($id_parent, $nombre)
    {
        $query = "SELECT --NVL(MAX(ID_CUENTAEMPRESARIAL),0)+1 id
                            decode(NVL(MAX(ID_CUENTAEMPRESARIAL),0)+1,1,'" . $id_parent . "'||NVL(MAX(ID_CUENTAEMPRESARIAL),0)+1,NVL(MAX(ID_CUENTAEMPRESARIAL),0)+1) id
                FROM CONTA_CTA_EMPRESARIAL
                WHERE ID_PARENT = '$id_parent' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_cuenta = $item->id;
        }

        $data = DB::table('CONTA_CTA_EMPRESARIAL')->insert(
            array(
                'ID_CUENTAEMPRESARIAL' => $id_cuenta,
                'ID_PARENT' => $id_parent,
                'NOMBRE' => $nombre
            )
        );
        $query = "SELECT ID_CUENTAEMPRESARIAL,
                        ID_PARENT,
                        NOMBRE
                FROM CONTA_CTA_EMPRESARIAL
                WHERE ID_CUENTAEMPRESARIAL = '$id_cuenta' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updatePlanAccountingEnterprise($id_cuentaempresarial, $nombre)
    {
        $query = "UPDATE CONTA_CTA_EMPRESARIAL SET    NOMBRE = '$nombre'
                    WHERE ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial' ";
        DB::update($query);

        $query = "SELECT ID_CUENTAEMPRESARIAL,
                        ID_PARENT,
                        NOMBRE
                FROM CONTA_CTA_EMPRESARIAL
                WHERE ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deletePlanAccountingEnterprise($id_cuentaempresarial)
    {
        $query = "DELETE CONTA_CTA_EMPRESARIAL
                WHERE ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial' ";
        DB::delete($query);
    }
    public static function listTypeMoney()
    {
        $query = "SELECT
                        ID_MONEDA,SIMBOLO,SIGLAS,NOMBRE,COD_SUNAT,DECODE(ID_MONEDA,7,1,0) DEFAULT_SELECTED,COD_INTERNO
                FROM CONTA_MONEDA
                WHERE COD_SUNAT IS NOT NULL
                AND ESTADO = '1'
                ORDER BY ID_MONEDA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTypeRestriction()
    {
        $query = "SELECT
                ID_RESTRICCION,NOMBRE
                FROM CONTA_RESTRICCION ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPlanAccountingDenominationalSearchV2($q, $id_tipoplan, $perPage, $page)
    {
        $query =  DB::table('CONTA_CTA_DENOMINACIONAL')
            ->select('ID_TIPOPLAN', 'ID_CUENTAAASI', 'ID_RESTRICCION', 'NOMBRE', 'ES_GRUPO', 'ES_ACTIVA')
            ->where(function ($query) use ($q) {
                $query->where('ID_CUENTAAASI', 'like', '%' . $q . '%')
                    ->orWhere(DB::raw('UPPER(NOMBRE)'), 'like', '%' . strtoupper($q) . '%');
            })
            ->where('ID_TIPOPLAN', $id_tipoplan)
            ->where('ES_GRUPO', '0')
            ->where('ES_ACTIVA', '1')
            ->orderBy('ID_CUENTAAASI')
            ->paginate($perPage, ['*'], 'page', $page);
        return $query;
    }

    public static function listPlanAccountingDenominationalSearch($id_cuentaaasi, $id_tipoplan)
    {
        $query = "SELECT
                            ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,NOMBRE
                FROM CONTA_CTA_DENOMINACIONAL
                WHERE ( ID_CUENTAAASI LIKE '%" . $id_cuentaaasi . "%' OR UPPER(NOMBRE) LIKE UPPER('%" . $id_cuentaaasi . "%'))
                AND ID_TIPOPLAN = $id_tipoplan
                AND ES_GRUPO = '0'
                AND ES_ACTIVA = '1'
                ORDER BY ID_CUENTAAASI ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listDenominationalAccount($params)
    {
        $data = DB::table("CONTA_CTA_DENOMINACIONAL")
            ->select('ID_CUENTAAASI', 'NOMBRE', 'ID_RESTRICCION', 'ID_TIPOPLAN', 'ID_TIPOCTACTE')
            ->where("ES_ACTIVA", '1')
            ->where("ES_GRUPO", '0');
        if (isset($params['id_tipoplan']) and $params['id_tipoplan']) {
            $data = $data
                ->where("ID_TIPOPLAN", $params['id_tipoplan']);
        }
        if (isset($params['text_search']) and $params['text_search']) {
            $txt = $params['text_search'];
            $data = $data
                ->whereraw("upper(NOMBRE ||' '|| ID_CUENTAAASI) like  upper('%" . $txt . "%')");
        }
        $data = $data->OrderBy('ID_CUENTAAASI');
        if (isset($params['page_size']) and $params['page_size'] > 0) {
            $data = $data->paginate($params['page_size']);
        } else {
            $data = $data->get();
        }

        return $data;
    }
    public static function listPlanAccountingDenominational($id_tipoplan)
    {
        $query = "SELECT
                            ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,NOMBRE
                FROM CONTA_CTA_DENOMINACIONAL
                WHERE ID_TIPOPLAN = $id_tipoplan
                --AND ES_GRUPO = '0'
                --AND ES_ACTIVA = 'true'
                ORDER BY ID_CUENTAAASI ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showPlanAccountingEquivalent($id_entidad, $id_anho, $id_cuentaempresarial)
    {
        $query = "SELECT id_empresa
                    FROM CONTA_ENTIDAD
                    WHERE ID_ENTIDAD = $id_entidad  ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_empresa = $item->id_empresa;
        }
        $query = "SELECT
                            A.ID_ANHO,A.ID_EMPRESA,A.ID_CUENTAEMPRESARIAL,A.ID_CUENTAAASI,A.ID_RESTRICCION,A.ID_MONEDA,A.ID_TIPOPLAN,
                            B.NOMBRE NOMBRE_CUENTAAASI
                FROM CONTA_EMPRESA_CTA A
                INNER JOIN CONTA_CTA_DENOMINACIONAL B ON A.ID_CUENTAAASI=B.ID_CUENTAAASI
                WHERE A.ID_ANHO =  $id_anho
                AND A.ID_EMPRESA = $id_empresa
                AND A.ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addPlanAccountingEquivalent($id_entidad, $id_anho, $id_cuentaempresarial, $id_tipoplan, $id_cuentaaasi, $id_restriccion)
    {
        $query = "SELECT id_empresa
                    FROM CONTA_ENTIDAD
                    WHERE ID_ENTIDAD = $id_entidad  ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_empresa = $item->id_empresa;
        }
        /*$query = "SELECT
                        id_restriccion
                FROM CONTA_CTA_DENOMINACIONAL
                WHERE ID_CUENTAAASI = '$id_cuentaaasi'
                AND ID_TIPOPLAN = $id_tipoplan ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item){
            $id_restriccion = $item->id_restriccion;
        }  */
        $query = "SELECT
                            ID_ANHO,ID_EMPRESA,ID_CUENTAEMPRESARIAL,ID_CUENTAAASI,ID_RESTRICCION,ID_MONEDA,ID_TIPOPLAN
                FROM CONTA_EMPRESA_CTA
                WHERE ID_ANHO =  $id_anho
                AND ID_EMPRESA = $id_empresa
                AND ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial' ";
        $oQuery = DB::select($query);
        if (count($oQuery) > 0) {
            $query = "UPDATE CONTA_EMPRESA_CTA SET    ID_CUENTAAASI = '$id_cuentaaasi',ID_RESTRICCION = '$id_restriccion'
                        WHERE ID_ANHO = '$id_anho'
                        AND ID_EMPRESA = $id_empresa
                        AND ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial'
                        AND ID_TIPOPLAN = $id_tipoplan ";
            DB::update($query);
        } else {
            $data = DB::table('CONTA_EMPRESA_CTA')->insert(
                array(
                    'ID_ANHO' => $id_anho,
                    'ID_EMPRESA' => $id_empresa,
                    'ID_CUENTAEMPRESARIAL' => $id_cuentaempresarial,
                    'ID_CUENTAAASI' => $id_cuentaaasi,
                    'ID_RESTRICCION' => $id_restriccion,
                    'ID_MONEDA' => 7,
                    'ID_TIPOPLAN' => $id_tipoplan
                )
            );
        }
        $query = "SELECT
                            ID_ANHO,ID_EMPRESA,ID_CUENTAEMPRESARIAL,ID_CUENTAAASI,ID_RESTRICCION,ID_MONEDA,ID_TIPOPLAN
                FROM CONTA_EMPRESA_CTA
                WHERE ID_ANHO =  $id_anho
                AND ID_EMPRESA = $id_empresa
                AND ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updatePlanAccountingEquivalent($id_entidad, $id_anho, $id_cuentaempresarial, $id_tipoplan, $id_cuentaaasi, $id_restriccion)
    {
        $query = "SELECT id_empresa
                    FROM CONTA_ENTIDAD
                    WHERE ID_ENTIDAD = $id_entidad  ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_empresa = $item->id_empresa;
        }
        /*$query = "SELECT
                        id_restriccion
                FROM CONTA_CTA_DENOMINACIONAL
                WHERE ID_CUENTAAASI = '$id_cuentaaasi'
                AND ID_TIPOPLAN = $id_tipoplan ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item){
            $id_restriccion = $item->id_restriccion;
        }*/
        $query = "SELECT
                            ID_ANHO,ID_EMPRESA,ID_CUENTAEMPRESARIAL,ID_CUENTAAASI,ID_RESTRICCION,ID_MONEDA,ID_TIPOPLAN
                FROM CONTA_EMPRESA_CTA
                WHERE ID_ANHO =  $id_anho
                AND ID_EMPRESA = $id_empresa
                AND ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial' ";
        $oQuery = DB::select($query);
        if (count($oQuery) > 0) {
            $query = "UPDATE CONTA_EMPRESA_CTA
                            SET ID_CUENTAAASI = '$id_cuentaaasi',
                            ID_RESTRICCION = '$id_restriccion'
                        WHERE ID_ANHO = '$id_anho'
                        AND ID_EMPRESA = $id_empresa
                        AND ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial'
                        AND ID_TIPOPLAN = $id_tipoplan ";
            DB::update($query);
        } else {
            $data = DB::table('CONTA_EMPRESA_CTA')->insert(
                array(
                    'ID_ANHO' => $id_anho,
                    'ID_EMPRESA' => $id_empresa,
                    'ID_CUENTAEMPRESARIAL' => $id_cuentaempresarial,
                    'ID_CUENTAAASI' => $id_cuentaaasi,
                    'ID_RESTRICCION' => $id_restriccion,
                    'ID_MONEDA' => 7,
                    'ID_TIPOPLAN' => $id_tipoplan
                )
            );
        }
        /*
        $query = "UPDATE CONTA_EMPRESA_CTA
                        SET ID_CUENTAAASI = '$id_cuentaaasi',
                            ID_RESTRICCION = '$id_restriccion'
                    WHERE ID_ANHO = '$id_anho'
                    AND ID_EMPRESA = $id_empresa
                    AND ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial'
                    AND ID_TIPOPLAN = $id_tipoplan ";
        DB::update($query); */

        $query = "SELECT
                            ID_ANHO,ID_EMPRESA,ID_CUENTAEMPRESARIAL,ID_CUENTAAASI,ID_RESTRICCION,ID_MONEDA,ID_TIPOPLAN
                FROM CONTA_EMPRESA_CTA
                WHERE ID_ANHO =  $id_anho
                AND ID_EMPRESA = $id_empresa
                AND ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listAccountingEntry($id_entidad, $id_depto, $id_anho, $id_modulo)
    {
        $query = "SELECT
                        ID_DINAMICA,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MODULO,ID_TIPOIGV,NOMBRE,IMPORTE,FECHA,ACTIVO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,COMENTARIO,ID_PARENT,ID_ALMACEN
                FROM CONTA_DINAMICA
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = $id_depto
                AND ID_ANHO = $id_anho
                AND ID_MODULO = $id_modulo
                ORDER BY ID_DINAMICA  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listAccountingEntryModule($id_entidad, $id_depto, $id_anho, $id_modulo, $id_tipoventa, $codigo = null, $id_almacen = null)
    {
        if ($id_tipoventa == "" || $id_tipoventa == null) {
            $venta = "";
        } else {
            $venta = "AND A.ID_TIPOTRANSACCION = $id_tipoventa ";
        }
        if ($codigo) {
            $text = "AND ID_MODULO IN (SELECT MAX(ID_MODULO) FROM LAMB_MODULO WHERE CODIGO = '$codigo') ";
        } else {
            $text = "AND A.ID_MODULO = $id_modulo ";
        }
        if ($id_almacen) {
            $almacen = "AND A.ID_ALMACEN = $id_almacen";
        } else {
            $almacen = "";
        }
        $query = "SELECT
                       ID_DINAMICA,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MODULO,ID_TIPOIGV,NOMBRE,IMPORTE,FECHA,ACTIVO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,COMENTARIO,ID_PARENT,
                       (
                           SELECT
                           COUNT(C.ID_DEPTO) DEPTOS
                           FROM CONTA_DINAMICA_ASIENTO B, CONTA_DINAMICA_DEPTO C
                           WHERE B.ID_ASIENTO = C.ID_ASIENTO
                           AND B.ID_DINAMICA = A.ID_DINAMICA
                           AND B.UNICO = 'N'
                       )CANT
               FROM CONTA_DINAMICA A
               WHERE A.ID_ENTIDAD = $id_entidad
               AND A.ID_DEPTO IN (PKG_ACCOUNTING.FC_CONTA_ENTIDAD_DEPTO_SHARED($id_entidad), '$id_depto')
               AND A.ID_ANHO = $id_anho
               AND (A.CODIGO IS NULL OR A.CODIGO NOT IN ('INVEST')) --CÓDIGO PARA EXCLUIR LAMB-RESEARCH YA QUE EL PAGO ES POR PASARELLA DE PAGO
               " . $text . "
               " . $venta . "
               " . $almacen . "
               AND A.ACTIVO = 'S'
               ORDER BY A.NOMBRE  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listAccountingEntryModuleResidence($id_entidad, $id_depto, $id_anho, $id_modulo, $codigo)
    {
        $query = "SELECT A.ID_DINAMICA,A.ID_TIPOTRANSACCION
        FROM ELISEO.CONTA_DINAMICA A JOIN ELISEO.TIPO_TRANSACCION B
        ON A.ID_TIPOTRANSACCION = B.ID_TIPOTRANSACCION
        JOIN ELISEO.TIPO_GRUPO_CONTA C
        ON B.ID_TIPOGRUPOCONTA = C.ID_TIPOGRUPOCONTA
        WHERE A.ID_ENTIDAD = $id_entidad
        AND A.ID_DEPTO = '$id_depto'
        AND A.ID_ANHO = $id_anho
        AND A.ID_MODULO = $id_modulo
        AND C.CODIGO = '$codigo'
        AND A.ACTIVO = 'S'";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    // como jugando data
    public static function listAccountingEntryModuleAlmacen($id_entidad, $id_depto, $id_anho, $id_modulo, $id_tipoventa, $codigo = null, $id_almacen)
    {
        if ($id_tipoventa == "" || $id_tipoventa == null) {
            $venta = "";
        } else {
            $venta = "AND A.ID_TIPOTRANSACCION = $id_tipoventa ";
        }
        if ($codigo) {
            $text = "AND ID_MODULO IN (SELECT MAX(ID_MODULO) FROM LAMB_MODULO WHERE CODIGO = '$codigo') ";
        } else {
            $text = "AND A.ID_MODULO = $id_modulo ";
        }
        $query = "SELECT
                       ID_DINAMICA,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MODULO,ID_TIPOIGV,NOMBRE,IMPORTE,FECHA,ACTIVO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,COMENTARIO,ID_PARENT,
                       (
                           SELECT
                           COUNT(C.ID_DEPTO) DEPTOS
                           FROM CONTA_DINAMICA_ASIENTO B, CONTA_DINAMICA_DEPTO C
                           WHERE B.ID_ASIENTO = C.ID_ASIENTO
                           AND B.ID_DINAMICA = A.ID_DINAMICA
                           AND B.UNICO = 'N'
                       )CANT
               FROM CONTA_DINAMICA A
               WHERE A.ID_ENTIDAD = $id_entidad
               AND A.ID_DEPTO IN (PKG_ACCOUNTING.FC_CONTA_ENTIDAD_DEPTO_SHARED($id_entidad), '$id_depto')
               AND A.ID_ANHO = $id_anho
               " . $text . "
               " . $venta . "
               AND A.ACTIVO = 'S'
               AND A.ID_ALMACEN = $id_almacen
               ORDER BY A.NOMBRE  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listDepositoAccountingEntry($id_entidad, $id_depto, $id_anho, $id_modulo, $id_dinamica)
    {

        $query = "SELECT
                        a.ID_DINAMICA,a.NOMBRE,
                        (SELECT count(*) FROM CONTA_DINAMICA x where x.ID_DINAMICA=$id_dinamica and x.ID_PARENT= a.ID_DINAMICA) as ASIGNADO
                FROM CONTA_DINAMICA a
                WHERE a.ID_ENTIDAD = $id_entidad
                AND a.ID_DEPTO = $id_depto
                AND a.ID_ANHO = $id_anho
               -- AND a.ID_MODULO = $id_modulo
                AND a.ID_DINAMICA<>$id_dinamica
                AND a.ID_MODULO NOT IN(
                  SELECT ID_MODULO FROM CONTA_DINAMICA x where x.ID_DINAMICA=$id_dinamica
                )
                ORDER BY a.ID_DINAMICA  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function updateRecursivoAccountingEntry($id_dinamica, $id_parent)
    {
        $query = "UPDATE CONTA_DINAMICA SET
                        ID_PARENT = null
                    WHERE ID_DINAMICA = $id_dinamica ";
        DB::update($query);
        if ($id_parent != "") {
            $query = "UPDATE CONTA_DINAMICA SET
                        ID_PARENT =$id_parent
                    WHERE ID_DINAMICA = $id_dinamica ";
            DB::update($query);
        }
        /*foreach($id_parents as $id_parent){
            $query = "UPDATE CONTA_DINAMICA SET
                        ID_PARENT =$id_parent
                    WHERE ID_DINAMICA = $id_dinamica ";
            DB::update($query);
        }*/

        $query = "SELECT
                            ID_DINAMICA,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MODULO,NOMBRE,IMPORTE,FECHA,ACTIVO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,COMENTARIO,ID_PARENT
                FROM CONTA_DINAMICA
                WHERE ID_DINAMICA = $id_dinamica ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listRecursivaAccountingEntry($id_entidad, $id_depto, $id_anho, $id_modulo, $id_parent)
    {


        $modulo = " AND ID_MODULO = $id_modulo ";
        $parent = " ";
        if ($id_parent > 0) {
            $modulo = "";
            $parent = " AND ID_DINAMICA=$id_parent ";
        }

        $query = "SELECT
                        A.ID_DINAMICA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MODULO,A.ID_TIPOIGV,A.NOMBRE,A.IMPORTE,A.FECHA,A.ACTIVO,A.ID_TIPOTRANSACCION AS ID_TIPOVENTA,A.COMENTARIO,A.ID_PARENT,B.NOMBRE AS OPERACION,
                        FC_TIPO_IGV(A.ID_TIPOIGV) TIPO_IGV
                  FROM CONTA_DINAMICA A, TIPO_TRANSACCION B
                  WHERE A.ID_TIPOTRANSACCION = B.ID_TIPOTRANSACCION
                  AND A.ID_ENTIDAD = " . $id_entidad . "
                  AND A.ID_DEPTO = '" . $id_depto . "'
                  AND A.ID_ANHO = " . $id_anho . "
                  AND A.ID_MODULO = " . $id_modulo . "
                  ORDER BY A.ID_DINAMICA ";
        $oQuery = DB::select($query);
        $parent = [];
        foreach ($oQuery as $row) {
            $id_dinamica = $row->id_dinamica;
            $id_parent = $row->id_parent;
            if ($id_parent == "") {
                $id_parent = 0;
            }

            $query = "SELECT
                        A.ID_DINAMICA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MODULO,A.ID_TIPOIGV,A.NOMBRE,A.IMPORTE,A.FECHA,A.ACTIVO,A.ID_TIPOTRANSACCION AS ID_TIPOVENTA,A.COMENTARIO,A.ID_PARENT,B.NOMBRE AS OPERACION,
                        FC_TIPO_IGV(A.ID_TIPOIGV) TIPO_IGV
                 FROM CONTA_DINAMICA A, TIPO_TRANSACCION B
                 WHERE A.ID_TIPOTRANSACCION = B.ID_TIPOTRANSACCION
                 AND A.ID_ENTIDAD = $id_entidad
                 AND A.ID_DEPTO = '" . $id_depto . "'
                 AND A.ID_ANHO = $id_anho
                 AND A.ID_PARENT = $id_dinamica
                UNION
                SELECT
                        A.ID_DINAMICA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MODULO,A.ID_TIPOIGV,A.NOMBRE,A.IMPORTE,A.FECHA,A.ACTIVO,A.ID_TIPOTRANSACCION AS ID_TIPOVENTA,A.COMENTARIO,A.ID_PARENT,B.NOMBRE AS OPERACION,
                        FC_TIPO_IGV(A.ID_TIPOIGV) TIPO_IGV
                 FROM CONTA_DINAMICA A, TIPO_TRANSACCION B
                 WHERE A.ID_TIPOTRANSACCION = B.ID_TIPOTRANSACCION
                 AND A.ID_ENTIDAD = $id_entidad
                 AND A.ID_DEPTO = '" . $id_depto . "'
                 AND A.ID_ANHO = $id_anho
                 AND A.ID_DINAMICA = $id_parent
                 ORDER BY ID_DINAMICA ";
            $que1 = DB::select($query);
            $rowhijo = $que1;

            $parent[] = [
                'id_dinamica' => $row->id_dinamica,
                'id_entidad' => $row->id_entidad,
                'id_depto' => $row->id_depto,
                'id_anho' => $row->id_anho,
                'id_modulo' => $row->id_modulo,
                'id_tipoigv' => $row->id_tipoigv,
                'nombre' => $row->nombre,
                'importe' => $row->importe,
                'fecha' => $row->fecha,
                'activo' => $row->activo,
                'id_tipoventa' => $row->id_tipoventa,
                'comentario' => $row->comentario,
                'id_parent' => $row->id_parent,
                'operacion' => $row->operacion,
                'tipo_igv' => $row->tipo_igv,
                'children' => $rowhijo
            ];
        }
        return $parent;
    }

    public static function showAccountingEntry($id_dinamica)
    {
        $query = "SELECT
                        A.ID_DINAMICA,A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MODULO,A.ID_TIPOIGV,A.NOMBRE,
                        A.IMPORTE,A.FECHA,A.ACTIVO,A.ID_TIPOTRANSACCION AS ID_TIPOVENTA,A.COMENTARIO,
                        A.ID_PARENT,A.ID_ALMACEN
                FROM CONTA_DINAMICA A
                WHERE ID_DINAMICA = $id_dinamica";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addAccountingEntry(
        $id_entidad,
        $id_depto,
        $id_anho,
        $id_modulo,
        $id_tipoigv,
        $nombre,
        $importe,
        $activo,
        $id_user,
        $ip,
        $id_tipoventa,
        $comentario,
        $id_almacen
    ) {
        $data = DB::table('CONTA_DINAMICA')->insert(
            array(
                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO' => $id_depto,
                'ID_ANHO' => $id_anho,
                'ID_MODULO' => $id_modulo,
                'ID_TIPOIGV' => $id_tipoigv,
                'NOMBRE' => $nombre,
                'IMPORTE' => $importe,
                'ACTIVO' => $activo,
                'ID_PERSONA' => $id_user,
                'IP' => $ip,
                'ID_TIPOTRANSACCION' => $id_tipoventa,
                'COMENTARIO' => $comentario,
                'ID_ALMACEN' => $id_almacen
            )
        );
        $query = "SELECT
                        max(ID_DINAMICA) id_dinamica
                FROM CONTA_DINAMICA
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = $id_depto ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_dinamica = $item->id_dinamica;
        }
        $query = "SELECT
                            ID_DINAMICA,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MODULO,NOMBRE,IMPORTE,FECHA,ACTIVO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,COMENTARIO,ID_PARENT,ID_ALMACEN

                FROM CONTA_DINAMICA
                WHERE ID_DINAMICA = $id_dinamica ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateAccountingEntry(
        $id_dinamica,
        $nombre,
        $importe,
        $id_user,
        $ip,
        $id_tipoventa,
        $comentario
    ) {

        $query = "UPDATE CONTA_DINAMICA SET NOMBRE = '$nombre',
                    IMPORTE = $importe,ID_PERSONA = $id_user,IP = '$ip',
                    ID_TIPOTRANSACCION=$id_tipoventa,
                    COMENTARIO='" . $comentario . "'
                    WHERE ID_DINAMICA = $id_dinamica ";
        DB::update($query);

        $query = "SELECT
                            ID_DINAMICA,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MODULO,NOMBRE,IMPORTE,FECHA,ACTIVO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,COMENTARIO,ID_PARENT,ID_ALMACEN
                FROM CONTA_DINAMICA
                WHERE ID_DINAMICA = $id_dinamica ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function AccountingEntry($id_dinamica, $activo)
    {
        $query = "UPDATE CONTA_DINAMICA SET ACTIVO = '$activo'
                    WHERE ID_DINAMICA = $id_dinamica ";
        DB::update($query);
        $query = "SELECT
                        ID_DINAMICA,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MODULO,NOMBRE,IMPORTE,FECHA,ACTIVO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,COMENTARIO,ID_PARENT,ID_ALMACEN
                FROM CONTA_DINAMICA
                WHERE ID_DINAMICA = $id_dinamica ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function AccountingEntryValida($id_dinamica)
    {
        $valida_dc = 1;
        $query = "SELECT
                            NVL(SUM(DC),1) VALIDA
                FROM (
                        SELECT
                        DECODE(DC,'D',1,'C',-1) DC
                        FROM CONTA_DINAMICA_ASIENTO
                        WHERE ID_DINAMICA = $id_dinamica
                        GROUP BY DC
                ) ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $valida_dc = $item->valida;
        }
        return $valida_dc;
    }
    public static function listAccountingEntryDetails($id_dinamica)
    {
        $query = "SELECT
                        case when coalesce(id_parent,0)=0 then id_asiento||'.0' else id_parent||'.'||id_asiento end as orden,
                        ID_ASIENTO,coalesce(id_parent,0) as ID_PARENT ,ID_DINAMICA,ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,ID_CUENTAEMPRESARIAL,
                        NOMBRE,DC,ACTIVO,PORCENTAJE,NRO_ASIENTO,DESTINO,ID_INDICADOR AS INDICADOR,UNICO,UNICO_CTACTE,AGRUPA
                FROM CONTA_DINAMICA_ASIENTO
                WHERE ID_DINAMICA = $id_dinamica
                ORDER BY NRO_ASIENTO,DC DESC";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    // public static function listDynamicArticles($text_search){
    //     $query = "SELECT ID_ARTICULO,
    //         ID_PARENT, ID_UNIDADMEDIDA, ID_MARCA, ID_CODCUBSO,
    //         ID_CLASE, NOMBRE, CODIGO, ESTADO
    //     FROM INVENTARIO_ARTICULO
    //     WHERE ESTADO=1
    //     AND UPPER(NOMBRE) LIKE UPPER('%$text_search%') OR UPPER(CODIGO) LIKE UPPER('%$text_search%')
    //     AND ROWNUM <= 50
    //     ";
    //     $oQuery = DB::select($query);
    //     return $oQuery;
    // }

    public static function listAccountingRecursivoEntryDetails($id_dinamica, $id_asiento)
    {
        $query = "SELECT
                        case when coalesce(id_parent,0)=0 then id_asiento||'.0' else id_parent||'.'||id_asiento end as orden,
                        ID_ASIENTO,coalesce(id_parent,0) as ID_PARENT ,ID_DINAMICA,ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,ID_CUENTAEMPRESARIAL,
                        NOMBRE,DC,ACTIVO,PORCENTAJE,NRO_ASIENTO,DESTINO,ID_INDICADOR AS INDICADOR,UNICO,UNICO_CTACTE,AGRUPA,
                        FC_DINAMICA_ASIENTO_DEPTO(ID_ASIENTO) DEPTO,FC_DINAMICA_ASIENTO_CTACTE(ID_ASIENTO) CTACTE
                FROM CONTA_DINAMICA_ASIENTO
                WHERE ID_DINAMICA = $id_dinamica
                AND coalesce(id_parent,0)=$id_asiento
                ORDER BY NRO_ASIENTO,DC DESC";
        $oQuery = DB::select($query);


        $parent = [];

        $nro_asiento = 0;
        $nr_asi = 0;
        $j = 0;
        $hijo = [];
        foreach ($oQuery as  $value) {

            if ($value->nro_asiento != $nro_asiento) {
                if ($j > 0) {
                    $parent[] = [
                        'nro_asiento' => $nro_asiento,
                        'children' => $hijo
                    ];
                    $hijo = [];
                }
            }


            $row = AccountingData::listAccountingRecursivoEntryDetails($id_dinamica, $value->id_asiento);
            $hijo[] = [
                'orden' => $value->orden,
                'id_asiento' => $value->id_asiento,
                'id_parent' => $value->id_parent,
                'id_dinamica' => $value->id_dinamica,
                'id_tipoplan' => $value->id_tipoplan,
                'id_cuentaaasi' => $value->id_cuentaaasi,
                'id_restriccion' => $value->id_restriccion,
                'id_cuentaempresarial' => $value->id_cuentaempresarial,
                'nombre' => $value->nombre,
                'dc' => $value->dc,
                'activo' => $value->activo,
                'porcentaje' => $value->porcentaje,
                'nro_asiento' => $value->nro_asiento,
                'destino' => $value->destino,
                'indicador' => $value->indicador,
                'unico' => $value->unico,
                'unico_ctacte' => $value->unico_ctacte,
                'agrupa' => $value->agrupa,
                'depto' => $value->depto,
                'ctacte' => $value->ctacte,
                'children' => $row
            ];


            $nro_asiento = $value->nro_asiento;
            $j++;
        }
        if ($j > 0) {
            $parent[] = [
                'nro_asiento' => $nro_asiento,
                'children' => $hijo
            ];
        }
        return $parent;
    }
    /*public static function listRecursivoAccountingEntryDetails($id_dinamica,$id_parent){
        $query = "SELECT
                        case when coalesce(id_parent,0)=0 then id_asiento||'.0' else id_parent||'.'||id_asiento end as orden,
                        ID_ASIENTO,coalesce(id_parent,0) as ID_PARENT ,ID_DINAMICA,ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,ID_CUENTAEMPRESARIAL,
                        NOMBRE,DC,ACTIVO,PORCENTAJE,NRO_ASIENTO,DESTINO,INDICADOR,UNICO,UNICO_CTACTE,AGRUPA
                FROM CONTA_DINAMICA_ASIENTO
                WHERE ID_DINAMICA = $id_dinamica
                AND COALESCE(ID_PARENT,0) = $id_parent
                ORDER BY NRO_ASIENTO,ID_ASIENTO";
        $oQuery = DB::select($query);
        return $oQuery;
    }*/
    public static function deleteAccountingEntry($id_dinamica)
    {
        $query = "DELETE CONTA_DINAMICA
                WHERE ID_DINAMICA = '$id_dinamica' ";
        DB::delete($query);
    }

    public static function deleteContaVoucherUser($id_voucher)
    {
        $query = "DELETE CONTA_VOUCHER_PERSONA
                WHERE ID_VOUCHER = $id_voucher
                 ";
        DB::delete($query);
    }

    public static function deleteContaVoucher($id_voucher)
    {
        $query = "DELETE CONTA_VOUCHER
                WHERE ID_VOUCHER = $id_voucher";
        DB::delete($query);
    }

    public static function deleteContaAsiento($id_tipoorigen, $id_origen)
    {
        $query = "DELETE CONTA_ASIENTO
                WHERE ID_TIPOORIGEN = $id_tipoorigen
                AND ID_ORIGEN = $id_origen
                ";
        DB::delete($query);
    }


    public static function listTypeIGV()
    {
        $query = "SELECT
                        ID_TIPOIGV,DESCRIPCION
                FROM TIPO_IGV
                ORDER BY ID_TIPOIGV ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showAccountingEntryDetails($id_asiento)
    {
        $query = "SELECT
                        A.ID_ASIENTO,A.ID_DINAMICA,A.ID_TIPOPLAN,A.ID_CUENTAAASI,
                        (SELECT X.NOMBRE FROM CONTA_CTA_DENOMINACIONAL X WHERE X.ID_TIPOPLAN = A.ID_TIPOPLAN AND X.ID_CUENTAAASI = A.ID_CUENTAAASI) NOMBRE_AASI,
                        A.ID_RESTRICCION,
                        A.ID_CUENTAEMPRESARIAL,
                        (SELECT X.NOMBRE FROM CONTA_CTA_EMPRESARIAL X WHERE X.ID_CUENTAEMPRESARIAL = A.ID_CUENTAEMPRESARIAL) NOMBRES_EMPRESA,
                        A.NOMBRE,A.DC,A.ACTIVO,A.PORCENTAJE,A.NRO_ASIENTO,A.DESTINO,A.ID_INDICADOR AS INDICADOR,A.UNICO,A.UNICO_CTACTE,A.AGRUPA,A.ID_FONDO,A.PRIMARIO
                FROM CONTA_DINAMICA_ASIENTO A
                WHERE ID_ASIENTO = $id_asiento ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addAccountingEntryDetails($id_dinamica, $id_tipoplan, $id_cuentaaasi, $id_restriccion, $id_cuentaempresarial, $nombre, $dc, $activo, $id_entidad, $depto, $porcentaje, $unico, $nro_asiento, $destino, $indicador, $unico_ctacte, $agrupa, $ctacte, $id_parent, $id_fondo, $primario)
    {
        $data = DB::table('CONTA_DINAMICA_ASIENTO')->insert(
            array(
                'ID_DINAMICA' => $id_dinamica,
                'ID_TIPOPLAN' => $id_tipoplan,
                'ID_CUENTAAASI' => $id_cuentaaasi,
                'ID_RESTRICCION' => $id_restriccion,
                'ID_CUENTAEMPRESARIAL' => $id_cuentaempresarial,
                'NOMBRE' => $nombre,
                'DC' => $dc,
                'ACTIVO' => $activo,
                'PORCENTAJE' => $porcentaje,
                'UNICO' => $unico,
                'NRO_ASIENTO' => $nro_asiento,
                'DESTINO' => $destino,
                'ID_INDICADOR' => $indicador,
                'UNICO_CTACTE' => $unico_ctacte,
                'AGRUPA' => $agrupa,
                'ID_PARENT' => $id_parent,
                'ID_FONDO' => $id_fondo,
                'PRIMARIO' => $primario,
            )
        );


        $query = "SELECT
                        max(ID_ASIENTO) id_asiento
                FROM CONTA_DINAMICA_ASIENTO
                WHERE ID_DINAMICA = $id_dinamica ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_asiento = $item->id_asiento;
        }
        foreach ($depto as $id_depto) {
            DB::table('CONTA_DINAMICA_DEPTO')->insert(
                array(
                    'ID_ASIENTO' => $id_asiento,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto
                )
            );
        }

        foreach ($ctacte as $id_ctacte) {
            DB::table('CONTA_DINAMICA_CTA_CTE')->insert(
                array(
                    'ID_ASIENTO' => $id_asiento,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_CTACTE' => $id_ctacte
                )
            );
        }

        $query = "SELECT
                            ID_ASIENTO,ID_DINAMICA,ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,ID_CUENTAEMPRESARIAL,NOMBRE,DC,ACTIVO,
                            PORCENTAJE,NRO_ASIENTO,DESTINO,ID_INDICADOR AS INDICADOR,UNICO,UNICO_CTACTE,AGRUPA, PRIMARIO
                FROM CONTA_DINAMICA_ASIENTO
                WHERE ID_ASIENTO = $id_asiento ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateAccountingEntryDetails($id_asiento, $id_cuentaaasi, $id_restriccion, $id_cuentaempresarial, $nombre, $dc, $id_entidad, $depto, $porcentaje, $unico, $nro_asiento, $destino, $indicador, $unico_ctacte, $agrupa, $ctacte, $id_fondo, $primario)
    {
        $query = "UPDATE CONTA_DINAMICA_ASIENTO SET ID_CUENTAAASI = '$id_cuentaaasi',
                                                    ID_RESTRICCION = '$id_restriccion',
                                                    ID_CUENTAEMPRESARIAL = '$id_cuentaempresarial',
                                                    NOMBRE = '$nombre',
                                                    DC = '$dc',
                                                    PORCENTAJE = $porcentaje,
                                                    UNICO = '" . $unico . "',
                                                    NRO_ASIENTO='" . $nro_asiento . "',
                                                    DESTINO='" . $destino . "',
                                                    ID_INDICADOR='" . $indicador . "',
                                                    UNICO_CTACTE='" . $unico_ctacte . "',
                                                    ID_FONDO=" . $id_fondo . ",
                                                    AGRUPA='" . $agrupa . "',
                                                    PRIMARIO='" . $primario . "'
                    WHERE ID_ASIENTO = $id_asiento ";
        DB::update($query);
        $sql = "DELETE FROM CONTA_DINAMICA_DEPTO WHERE ID_ASIENTO = ? ";
        DB::delete($sql, array($id_asiento));
        foreach ($depto as $id_depto) {
            DB::table('CONTA_DINAMICA_DEPTO')->insert(
                array(
                    'ID_ASIENTO' => $id_asiento,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto
                )
            );
        }

        $sql = "DELETE FROM CONTA_DINAMICA_CTA_CTE WHERE ID_ASIENTO = ? ";
        DB::delete($sql, array($id_asiento));
        foreach ($ctacte as $id_ctacte) {
            DB::table('CONTA_DINAMICA_CTA_CTE')->insert(
                array(
                    'ID_ASIENTO' => $id_asiento,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_CTACTE' => $id_ctacte
                )
            );
        }


        $query = "SELECT
                            ID_ASIENTO,ID_DINAMICA,ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,ID_CUENTAEMPRESARIAL,NOMBRE,DC,ACTIVO,
                            PORCENTAJE,NRO_ASIENTO,DESTINO,ID_INDICADOR AS INDICADOR,UNICO,UNICO_CTACTE,AGRUPA, PRIMARIO
                FROM CONTA_DINAMICA_ASIENTO
                WHERE ID_ASIENTO = $id_asiento ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function AccountingEntryDetails($id_asiento, $activo)
    {
        $query = "UPDATE CONTA_DINAMICA_ASIENTO SET ACTIVO = '$activo'
                    WHERE ID_ASIENTO = $id_asiento ";
        DB::update($query);
        $query = "SELECT
                            ID_ASIENTO,ID_DINAMICA,ID_TIPOPLAN,ID_CUENTAAASI,ID_RESTRICCION,ID_CUENTAEMPRESARIAL,NOMBRE,DC,ACTIVO,
                            PORCENTAJE,NRO_ASIENTO,DESTINO,ID_INDICADOR AS INDICADOR,UNICO,UNICO_CTACTE,AGRUPA
                FROM CONTA_DINAMICA_ASIENTO
                WHERE ID_ASIENTO = $id_asiento ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteAccountingEntryDetails($id_asiento)
    {
        $query = "DELETE CONTA_DINAMICA_DEPTO
                WHERE ID_ASIENTO = '$id_asiento' ";
        DB::delete($query);
        $query = "DELETE CONTA_DINAMICA_CTA_CTE
                WHERE ID_ASIENTO = '$id_asiento' ";
        DB::delete($query);
        $query = "DELETE CONTA_DINAMICA_ASIENTO
                WHERE ID_ASIENTO = '$id_asiento' ";
        DB::delete($query);
    }

    public static function listDeptoAsientoAccounting($id_asiento)
    {
        $query = "SELECT
                        a.ID_ASIENTO,a.ID_ENTIDAD,a.ID_DEPTO,b.NOMBRE
                    FROM CONTA_DINAMICA_DEPTO a,CONTA_ENTIDAD_DEPTO b
                    WHERE a.ID_ENTIDAD=b.ID_ENTIDAD
                    AND a.ID_DEPTO=b.ID_DEPTO
                    AND a.ID_ASIENTO=$id_asiento
                    ORDER BY a.ID_DEPTO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listCtaCteAsientoAccounting($id_asiento)
    {

        $query = "SELECT
                b.ID_TIPOCTACTE
                FROM CONTA_DINAMICA_ASIENTO a, CONTA_CTA_DENOMINACIONAL b
                WHERE a.ID_TIPOPLAN=b.ID_TIPOPLAN
                AND a.ID_CUENTAAASI=b.ID_CUENTAAASI
                AND a.ID_RESTRICCION=b.ID_RESTRICCION
                AND a.ID_ASIENTO=$id_asiento";
        $oQuery = DB::select($query);

        $id_tipoctacte = "";
        foreach ($oQuery as $row) {
            $id_tipoctacte = $row->id_tipoctacte;
        }

        if ($id_tipoctacte == "ENTI") {
            $query = "SELECT
                    a.ID_ASIENTO,a.ID_ENTIDAD,a.ID_CTACTE ,b.NOMBRE
                FROM CONTA_DINAMICA_CTA_CTE a,CONTA_ENTIDAD b
                WHERE a.ID_CTACTE=to_char(b.ID_ENTIDAD)
                AND a.ID_ASIENTO=$id_asiento
                ORDER BY a.ID_CTACTE";
        } elseif ($id_tipoctacte == "DEPTO") {
            $query = "SELECT
                    a.ID_ASIENTO,a.ID_ENTIDAD,a.ID_CTACTE ,b.NOMBRE
                FROM CONTA_DINAMICA_CTA_CTE a,CONTA_ENTIDAD_DEPTO b
                WHERE a.ID_ENTIDAD=b.ID_ENTIDAD
                AND a.ID_CTACTE=to_char(b.ID_DEPTO)
                AND a.ID_ASIENTO=$id_asiento
                ORDER BY a.ID_CTACTE";
        } else {
            $query = "SELECT
                    a.ID_ASIENTO,a.ID_ENTIDAD,a.ID_CTACTE ,b.NOMBRE
                FROM CONTA_DINAMICA_CTA_CTE a,CONTA_ENTIDAD_CTA_CTE b
                WHERE a.ID_ENTIDAD=b.ID_ENTIDAD
                AND a.ID_CTACTE=b.ID_CTACTE
                AND b.ID_TIPOCTACTE='" . $id_tipoctacte . "'
                AND a.ID_ASIENTO=$id_asiento
                ORDER BY a.ID_CTACTE";
        }

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listRestriccionAccounting($id_plan, $id_cuentaaasi)
    {
        // $query = "SELECT
        //                 A.ID_RESTRICCION,B.NOMBRE
        //         FROM CONTA_CTA_DENOMINACIONAL A, CONTA_RESTRICCION B
        //         WHERE A.ID_RESTRICCION = B.ID_RESTRICCION
        //         AND A.ID_TIPOPLAN = $id_plan
        //         AND A.ID_CUENTAAASI = '$id_cuentaaasi' ";
        // $oQuery = DB::select($query);
        // return $oQuery;
        $query = "SELECT ccr.ID_RESTRICCION, cr.NOMBRE 
                FROM CONTA_CTA_RESTRICCION ccr 
                INNER JOIN CONTA_CTA_DENOMINACIONAL ccd ON ccr.ID_CUENTAAASI=ccd.ID_CUENTAAASI 
                INNER JOIN CONTA_RESTRICCION cr ON ccr.ID_RESTRICCION=cr.ID_RESTRICCION 
                AND ccd.ID_TIPOPLAN = $id_plan
                AND ccd.ES_ACTIVA = '1'  
                AND ccd.ID_CUENTAAASI = '$id_cuentaaasi'";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPlanAccountingEnterpriseSearch($id_anho, $id_entidad, $id_tipoplan, $id_cuentaaasi)
    {
        $query = "SELECT id_empresa
                    FROM CONTA_ENTIDAD
                    WHERE ID_ENTIDAD = $id_entidad  ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_empresa = $item->id_empresa;
        }
        /*$query = "SELECT
                        A.ID_CUENTAEMPRESARIAL,B.NOMBRE
                FROM CONTA_EMPRESA_CTA A, CONTA_CTA_EMPRESARIAL B
                WHERE A.ID_CUENTAEMPRESARIAL = B.ID_CUENTAEMPRESARIAL
                AND A.ID_ANHO = $id_anho
                AND A.ID_EMPRESA = $id_empresa
                AND A.ID_TIPOPLAN = $id_tipoplan
                AND A.ID_CUENTAAASI LIKE '%$id_cuentaaasi%' OR UPPER(B.NOMBRE) LIKE UPPER('%".$id_cuentaaasi."%') ";
        $oQuery = DB::select($query);  */
        /*$query = "SELECT
                            ID_CUENTAEMPRESARIAL,NOMBRE
                   FROM CONTA_CTA_EMPRESARIAL
                   WHERE ID_CUENTAEMPRESARIAL IN (
                   SELECT ID_CUENTAEMPRESARIAL
                   FROM CONTA_EMPRESA_CTA A
                   WHERE A.ID_ANHO = $id_anho
                   AND A.ID_EMPRESA = $id_empresa
                   AND A.ID_TIPOPLAN = $id_tipoplan
                   )
                   AND ID_CUENTAEMPRESARIAL LIKE '%".$id_cuentaaasi."%' OR UPPER(NOMBRE) LIKE UPPER('%".$id_cuentaaasi."%')
                   ORDER BY ID_CUENTAEMPRESARIAL ";*/
        $query = "SELECT A.ID_CUENTAEMPRESARIAL, B.NOMBRE
                FROM CONTA_EMPRESA_CTA A, CONTA_CTA_EMPRESARIAL B
                WHERE A.ID_CUENTAEMPRESARIAL = B.ID_CUENTAEMPRESARIAL
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_EMPRESA = " . $id_empresa . "
                AND A.ID_CUENTAAASI = '" . $id_cuentaaasi . "' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPlanAccountingEnterpriseSearchV2($q, $perPage, $page)
    {
        $query =  DB::table('CONTA_CTA_EMPRESARIAL')
            ->select('ID_CUENTAEMPRESARIAL', 'NOMBRE')
            ->where(function ($query) use ($q) {
                $query->where('ID_CUENTAEMPRESARIAL', 'like', '%' . $q . '%')
                    ->orWhere(DB::raw('UPPER(NOMBRE)'), 'like', '%' . strtoupper($q) . '%');
            })
            ->orderBy('ID_CUENTAEMPRESARIAL')
            ->paginate($perPage, ['*'], 'page', $page);
        return $query;
    }
    public static function showPlanAccountingEnterpriseMax($id_parent)
    {
        $query = "SELECT --NVL(MAX(ID_CUENTAEMPRESARIAL),0)+1 id
                            decode(NVL(MAX(ID_CUENTAEMPRESARIAL),0)+1,1,'" . $id_parent . "'||NVL(MAX(ID_CUENTAEMPRESARIAL),0)+1,NVL(MAX(ID_CUENTAEMPRESARIAL),0)+1) id
                FROM CONTA_CTA_EMPRESARIAL
                WHERE ID_PARENT = '$id_parent' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showPeriodoVoucher($id_voucher)
    {
        $query = "SELECT
                MAX(ID_ANHO) ID_ANHO, TO_CHAR(SYSDATE,'YYYY') ID_ANHO_ACTUAL
                    FROM CONTA_VOUCHER cv2
                    WHERE id_voucher = $id_voucher ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showPeriodoActivo($id_entidad)
    {
        $query = "SELECT
                        MAX(ID_ANHO) ID_ANHO, TO_CHAR(SYSDATE,'YYYY') ID_ANHO_ACTUAL
                FROM CONTA_ENTIDAD_ANHO_CONFIG
                WHERE ID_ENTIDAD = $id_entidad
                AND ACTIVO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showMesActivo($id_entidad, $id_anho)
    {
        $query = "SELECT
                            MAX(ID_MES) ID_MES, TO_NUMBER(TO_CHAR(SYSDATE,'MM')) ID_MES_ACTUAL
                FROM CONTA_ENTIDAD_MES_CONFIG
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_ANHO = $id_anho
                AND ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMoneda()
    {
        $query = "SELECT * FROM CONTA_MONEDA";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTipoCambio($id_anho, $id_mes, $id_moneda_main, $id_moneda)
    {
        $query = "SELECT
                    ID_MONEDA,ID_MONEDA_MAIN,FECHA,COALESCE(COS_COMPRA,0) AS COS_COMPRA,COALESCE(COS_VENTA,0) AS COS_VENTA,COALESCE(COS_DENOMINACIONAL,0) COS_DENOMINACIONAL
                    FROM TIPO_CAMBIO
                    WHERE ID_MONEDA=$id_moneda AND ID_MONEDA_MAIN=$id_moneda_main
                    AND TO_CHAR(FECHA,'YYYY')='" . $id_anho . "'
                    AND CAST(TO_CHAR(FECHA,'MM') AS NUMBER)='" . $id_mes . "'
                    ORDER BY FECHA";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showTipoCambio($fecha)
    {
        $query = "SELECT
                           ID_MONEDA,FECHA,COALESCE(COS_COMPRA,0) AS COS_COMPRA,COALESCE(COS_VENTA,0) AS COS_VENTA,COALESCE(COS_DENOMINACIONAL,0) COS_DENOMINACIONAL
                   FROM TIPO_CAMBIO
                   WHERE ID_MONEDA=9
                   AND TO_CHAR(FECHA,'YYYY-MM-DD') = '" . $fecha . "'
                   ORDER BY FECHA";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateTipoCambio($fecha, $compra, $venta, $denominacional, $id_moneda_main, $id_moneda)
    {

        // $query = "UPDATE TIPO_CAMBIO SET COS_COMPRA = $compra,COS_VENTA = $venta,COS_DENOMINACIONAL = $denominacional
        //             WHERE ID_MONEDA=$id_moneda AND ID_MONEDA_MAIN=$id_moneda_main
        //             AND FECHA='".$fecha."'";
        // DB::update($query);
        DB::table('eliseo.TIPO_CAMBIO')
            ->where('ID_MONEDA', $id_moneda)
            ->where('ID_MONEDA_MAIN', $id_moneda_main)
            ->where('FECHA', $fecha)
            ->update([
                "COS_COMPRA" => $compra,
                "COS_VENTA" => $venta,
                "COS_DENOMINACIONAL" => $denominacional
            ]);

        return DB::table('eliseo.TIPO_CAMBIO')
            ->select(
                DB::raw('ID_MONEDA'),
                DB::raw('ID_MONEDA_MAIN'),
                DB::raw('FECHA'),
                DB::raw('COALESCE(COS_COMPRA,0) AS COS_COMPRA'),
                DB::raw('COALESCE(COS_VENTA,0) AS COS_VENTA'),
                DB::raw('COALESCE(COS_DENOMINACIONAL,0) COS_DENOMINACIONAL')
            )
            ->where('ID_MONEDA', $id_moneda)
            ->where('ID_MONEDA_MAIN', $id_moneda_main)
            ->where('FECHA', $fecha)
            ->first();

        // $query = "SELECT
        //                     ID_MONEDA,FECHA,COALESCE(COS_COMPRA,0) AS COS_COMPRA,COALESCE(COS_VENTA,0) AS COS_VENTA,COALESCE(COS_DENOMINACIONAL,0) COS_DENOMINACIONAL
        //             FROM TIPO_CAMBIO
        //             WHERE ID_MONEDA=$id_moneda AND ID_MONEDA_MAIN=$id_moneda_main
        //             AND FECHA='".$fecha."'";
        // $oQuery = DB::select($query);
        // return $oQuery;
    }

    public static function addTipoCambio($fecha, $compra, $venta, $denominacional, $moneda_main, $moneda)
    {
        //dd($fecha,$compra,$venta,$denominacional);
        DB::table("ELISEO.TIPO_CAMBIO")
            ->insert([
                'id_moneda' => $moneda, // 9 Dólares
                'fecha' => $fecha,
                'cos_venta' => $venta,
                'cos_compra' => $compra,
                'cos_denominacional' => $denominacional,
                'id_moneda_main' => $moneda_main
            ]);

        // TIPO_CAMBIO SET COS_COMPRA = $compra,COS_VENTA = $venta,COS_DENOMINACIONAL = $denominacional
        return self::getTypeChange($fecha, $moneda_main, $moneda);
    }
    public static function getTypeChange($fecha, $moneda_main, $moneda)
    {
        return DB::table("TIPO_CAMBIO")
            ->where('fecha', $fecha)
            ->where('id_moneda_main', $moneda_main)
            ->where('id_moneda', $moneda)
            ->first(); //get, pagination,
    }
    public static function listCtaCteAccountingV2($id_entidad, $id_tipoplan, $id_cuentaaasi, $id_restriccion, $search_term, $per_page, $page, $all)
    {
        $query = "SELECT
                    ID_TIPOCTACTE
                FROM CONTA_CTA_DENOMINACIONAL
                WHERE ID_TIPOPLAN='" . $id_tipoplan . "'
                AND ID_CUENTAAASI='" . $id_cuentaaasi . "'
                AND ID_RESTRICCION='" . $id_restriccion . "'";
        $oQuery = DB::select($query);
        $id_tipoctacte = "-";
        foreach ($oQuery as $row) {
            $id_tipoctacte = $row->id_tipoctacte;
        }

        Log::info("============================================================>");
        Log::info($id_tipoctacte);
        if ($id_tipoctacte == "ENTI") {
            if ($all == true) {
                $query = DB::table('CONTA_ENTIDAD')->select('ID_ENTIDAD as ID_CTACTE', 'NOMBRE')->where('es_activo', 1)->orderby('ID_ENTIDAD');
            } else {
                $query = DB::table('CONTA_ENTIDAD')
                    ->select('ID_ENTIDAD AS ID_CTACTE', 'NOMBRE')
                    ->where(DB::raw('UPPER(NOMBRE)'), 'LIKE', '%' . strtoupper($search_term) . '%')
                    ->where('es_activo', 1)
                    ->orderBy('ID_ENTIDAD')
                    ->paginate($per_page, ['*'], 'page', $page);
            }
        } elseif ($id_tipoctacte == "DEPTO") {
            if ($all == true) {
                $query = DB::table('CONTA_ENTIDAD_DEPTO')->select('ID_DEPTO as ID_CTACTE', 'NOMBRE')->where('ID_ENTIDAD', $id_entidad)->where('ES_GRUPO', 0)->orderby('ID_DEPTO');
            } else {
                $query = DB::table('CONTA_ENTIDAD_DEPTO')
                    ->select('ID_DEPTO AS ID_CTACTE', 'NOMBRE')
                    ->where('ID_ENTIDAD', $id_entidad)
                    ->where('ES_GRUPO', 0)
                    ->where(DB::raw('UPPER(NOMBRE)'), 'LIKE', '%' . strtoupper($search_term) . '%')
                    ->orderBy('ID_DEPTO')
                    ->paginate($per_page, ['*'], 'page', $page);
            }
        } else {
            if ($all == true) {
                $query = DB::table('CONTA_ENTIDAD_CTA_CTE')->select('ID_CTACTE', 'NOMBRE')->where('ID_ENTIDAD', $id_entidad)->where('ID_TIPOCTACTE', $id_tipoctacte)->orderby('ID_CTACTE');
            } else {
                $query = DB::table('CONTA_ENTIDAD_CTA_CTE')
                    ->select('ID_CTACTE', 'NOMBRE')
                    ->where('ID_ENTIDAD', $id_entidad)
                    ->where('ID_TIPOCTACTE', $id_tipoctacte)
                    ->where(DB::raw('UPPER(NOMBRE)'), 'like', '%' . strtoupper($search_term) . '%')
                    ->orderby('ID_CTACTE')
                    ->paginate($per_page, ['*'], 'page', $page);
            }
        }
        return $query;
    }

    public static function listCtaCteAccounting($id_entidad, $id_tipoplan, $id_cuentaaasi, $id_restriccion, $nombre, $all)
    {

        /*$query = "SELECT
                    ID_ENTIDAD
                FROM CONTA_DINAMICA
                WHERE ID_DINAMICA='".$id_dinamica."'";
        $oQuery = DB::select($query);
        $id_entidad="-";
        foreach($oQuery as $row){
            $id_entidad=$row->id_entidad;
        }*/

        $query = "SELECT
                    ID_TIPOCTACTE
                FROM CONTA_CTA_DENOMINACIONAL
                WHERE ID_TIPOPLAN='" . $id_tipoplan . "'
                AND ID_CUENTAAASI='" . $id_cuentaaasi . "'
                AND ID_RESTRICCION='" . $id_restriccion . "'";
        $oQuery = DB::select($query);
        $id_tipoctacte = "-";
        foreach ($oQuery as $row) {
            $id_tipoctacte = $row->id_tipoctacte;
        }

        if ($id_tipoctacte == "ENTI") {
            /*$query = "SELECT
                    to_char(ID_ENTIDAD) as ID_CTACTE,NOMBRE
                FROM CONTA_ENTIDAD b
                WHERE es_activo=1
                ORDER BY ID_ENTIDAD";*/


            if ($all == true) {
                $query = DB::table('CONTA_ENTIDAD')->select('ID_ENTIDAD as ID_CTACTE', 'NOMBRE')->where('es_activo', 1)->orderby('ID_ENTIDAD');
            } else {
                $query = DB::table('CONTA_ENTIDAD')->select('ID_ENTIDAD as ID_CTACTE', 'NOMBRE')->where('es_activo', 1)->orderby('ID_ENTIDAD')->paginate(100);
            }
        } elseif ($id_tipoctacte == "DEPTO") {
            /*$query = "SELECT
                    to_char(ID_DEPTO) as ID_CTACTE ,NOMBRE
                FROM CONTA_ENTIDAD_DEPTO b
                WHERE ID_ENTIDAD=".$id_entidad."
                AND ES_ACTIVO='1'
                AND ES_GRUPO = '0'
                ORDER BY ID_DEPTO";*/
            if ($all == true) {

                $query = DB::table('CONTA_ENTIDAD_DEPTO')->select('ID_DEPTO as ID_CTACTE', 'NOMBRE')->where('ID_ENTIDAD', $id_entidad)->where('ES_GRUPO', 0)->orderby('ID_DEPTO');
            } else {
                $query = DB::table('CONTA_ENTIDAD_DEPTO')->select('ID_DEPTO as ID_CTACTE', 'NOMBRE')->where('ID_ENTIDAD', $id_entidad)->where('ES_GRUPO', 0)->orderby('ID_DEPTO')->paginate(100);
            }
        } else {
            /*$query = "SELECT
                    ID_CTACTE ,NOMBRE
                FROM CONTA_ENTIDAD_CTA_CTE
                WHERE ID_ENTIDAD=".$id_entidad."
                AND ID_TIPOCTACTE='".$id_tipoctacte."'
                ORDER BY ID_CTACTE";*/
            if ($all == true) {
                $query = DB::table('CONTA_ENTIDAD_CTA_CTE')->select('ID_CTACTE', 'NOMBRE')->where('ID_ENTIDAD', $id_entidad)->where('ID_TIPOCTACTE', $id_tipoctacte)->orderby('ID_CTACTE');
            } else {
                $query = DB::table('CONTA_ENTIDAD_CTA_CTE')->select('ID_CTACTE', 'NOMBRE')->where('ID_ENTIDAD', $id_entidad)->where('ID_TIPOCTACTE', $id_tipoctacte)->where(DB::raw('UPPER(NOMBRE)'), 'like', '%' . strtoupper($nombre) . '%')->orderby('ID_CTACTE')->paginate(100);
            }
        }
        return $query;
    }
    //    public static function listCheckingAccount($id_entidad,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$nombre,$all){
    public static function listCheckingAccount($params)
    {

        if (isset($params['id_tipoctacte']) and $params['id_tipoctacte'] and $params['id_tipoctacte'] == 'ENTI') {
            $query = DB::table('CONTA_ENTIDAD')
                ->select('ID_ENTIDAD as ID_CTACTE', 'NOMBRE')
                ->where('es_activo', 1)
                ->orderby('ID_ENTIDAD');
        } else if (isset($params['id_tipoctacte']) and $params['id_tipoctacte'] and $params['id_tipoctacte'] == 'DEPTO') {
            $query = DB::table('CONTA_ENTIDAD_DEPTO')
                ->select('ID_DEPTO as ID_CTACTE', 'NOMBRE')
                ->where('ID_ENTIDAD', $params['id_entidad'])
                ->where('ES_GRUPO', 0);
            if (isset($params['text_search']) and $params['text_search']) {
                $txt = $params['text_search'];
                $query = $query
                    ->whereraw("UPPER(ID_ENTIDAD || ' ' || ID_DEPTO || ' ' || NOMBRE) LIKE UPPER('%" . $txt . "%')");
            }
            $query = $query
                ->orderby('ID_DEPTO');
        } else {
            $query = DB::table('CONTA_ENTIDAD_CTA_CTE')
                ->select('ID_CTACTE', 'NOMBRE')
                ->where('ID_ENTIDAD', $params['id_entidad']);

            if (isset($params['id_tipoctacte']) and $params['id_tipoctacte']) {
                $query = $query
                    ->where('ID_TIPOCTACTE', $params['id_tipoctacte']);
            }
            if (isset($params['text_search']) and $params['text_search']) {
                $txt = $params['text_search'];
                $query = $query
                    ->whereraw("UPPER(ID_CTACTE || ' ' || NOMBRE) LIKE UPPER('%" . $txt . "%')");
            }
            $query = $query->orderby('ID_CTACTE');
        }
        if (isset($params['page_size']) and $params['page_size'] > 0) {
            $query = $query->paginate($params['page_size']);
        } else {
            $query = $query->get();
        }

        return $query;
    }
    public static function listCtaCteAccountingSearch($id_entidad, $id_tipoplan, $id_cuentaaasi, $id_restriccion, $nombre)
    {
        // $query = "SELECT
        //             ID_TIPOCTACTE
        //         FROM CONTA_CTA_DENOMINACIONAL
        //         WHERE ID_TIPOPLAN='" . $id_tipoplan . "'
        //         AND ID_CUENTAAASI='" . $id_cuentaaasi . "'
        //         AND ID_RESTRICCION='" . $id_restriccion . "'";
        $query = "SELECT
                ID_TIPOCTACTE
            FROM CONTA_CTA_DENOMINACIONAL
            WHERE ID_TIPOPLAN='" . $id_tipoplan . "'
            AND ID_CUENTAAASI='" . $id_cuentaaasi . "'";
        $oQuery = DB::select($query);
        $id_tipoctacte = "-";
        foreach ($oQuery as $row) {
            $id_tipoctacte = $row->id_tipoctacte;
        }

        if ($id_tipoctacte == "ENTI") {
            $query = "SELECT
                    to_char(ID_ENTIDAD) as ID_CTACTE,NOMBRE
                FROM CONTA_ENTIDAD
                WHERE es_activo=1
                AND (ID_ENTIDAD LIKE UPPER('%" . $nombre . "%') OR UPPER(NOMBRE) LIKE UPPER('%" . $nombre . "%'))
                ORDER BY ID_ENTIDAD";
            //$query = DB::table('CONTA_ENTIDAD')->select('ID_ENTIDAD as ID_CTACTE','NOMBRE')->where('es_activo', 1)->where(DB::raw('UPPER(NOMBRE)'), 'like','%'.strtoupper($nombre).'%')->orderby('ID_ENTIDAD');
        } elseif ($id_tipoctacte == "DEPTO") {
            $query = "SELECT
                    to_char(ID_DEPTO) as ID_CTACTE ,NOMBRE
                FROM CONTA_ENTIDAD_DEPTO b
                WHERE ID_ENTIDAD=" . $id_entidad . "
                AND (ID_DEPTO LIKE  UPPER('%" . $nombre . "%') OR UPPER(NOMBRE) LIKE UPPER('%" . $nombre . "%'))
                AND ES_ACTIVO='1'
                AND ES_GRUPO = '0'
                ORDER BY ID_DEPTO";
            //$query = DB::table('CONTA_ENTIDAD_DEPTO')->select('ID_DEPTO as ID_CTACTE','NOMBRE')->where('ID_ENTIDAD', $id_entidad)->where('ES_GRUPO', 0)->where(DB::raw('UPPER(NOMBRE)'), 'like','%'.strtoupper($nombre).'%')->orderby('ID_DEPTO');
        } else {
            $query = "SELECT
                    ID_CTACTE ,NOMBRE
                FROM CONTA_ENTIDAD_CTA_CTE
                WHERE ID_ENTIDAD=" . $id_entidad . "
                AND ID_TIPOCTACTE='" . $id_tipoctacte . "'
                AND (ID_CTACTE LIKE UPPER('%" . $nombre . "%') OR UPPER(NOMBRE) LIKE UPPER('%" . $nombre . "%'))
                AND ROWNUM <= 31
                ORDER BY ID_CTACTE ";
            //$query = DB::table('CONTA_ENTIDAD_CTA_CTE')->select('ID_CTACTE','NOMBRE')->where('ID_ENTIDAD', $id_entidad)->where('ID_TIPOCTACTE', $id_tipoctacte)->where(DB::raw('UPPER(NOMBRE)'), 'like','%'.strtoupper($nombre).'%')->orderby('ID_CTACTE');
        }
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listIndicador()
    {
        $query = "SELECT
                ID_INDICADOR AS ID, ID_INDICADOR AS NOMBRE,COMENTARIO
                FROM CONTA_DINAMICA_INDICADOR ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listDeptoUnico()
    {
        $query = "SELECT
                UNICO, COMENTARIO
                FROM CONTA_DINAMICA_DEPTOUNICO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listCtaCteUnico()
    {
        $query = "SELECT
                UNICO_CTACTE, COMENTARIO
                FROM CONTA_DINAMICA_CTACTEUNICO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listMyVoucherModules($id_user, $id_entidad, $id_depto, $id_anho, $id_mes, $id_tipovoucher, $user_created, $all_vouchers)
    {

        $dato = "";
        if (!$all_vouchers) {
            $dato = "AND A.ID_DEPTO = '$id_depto'
                    AND A.ID_PERSONA = $user_created";
        }
        /*
        if($id_tipovoucher == 1 || $id_tipovoucher == 2){
            $query = "SELECT 0 ID_VOUCHER,TO_CHAR(SYSDATE,'DD/MM/YYYY') AS FECHA,'TODOS' AS NUMERO,'' AS LOTE,'S' AS ACTIVO, '' ASIGNADO
                FROM DUAL
                UNION ALL
                SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.NUMERO) AS NUMERO,A.LOTE,A.ACTIVO,
                        TO_CHAR((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1')) ASIGNADO
                FROM CONTA_VOUCHER A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                AND ACTIVO = 'S'
                $dato
                ORDER BY NUMERO,FECHA ";
        }else{
            */
        $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.NUMERO) AS NUMERO,A.LOTE,A.ACTIVO,
                        TO_CHAR((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1')) ASIGNADO,
                        B.ID_CVOUCHER,

                        COALESCE ((SELECT email FROM users u WHERE u.id = A.ID_PERSONA),'') AS USER_CREATED,

                        DECODE(NVL(B.NUMERO,1),1,'',B.NUMERO||' - S/. ')||B.IMPORTE AS CHEQUE
                FROM CONTA_VOUCHER A LEFT JOIN ( SELECT X.ID_VOUCHER,X.ID_CVOUCHER, Y.NUMERO,Y.IMPORTE FROM CAJA_CHEQUERA_VOUCHER X, CAJA_CHEQUERA Y WHERE X.ID_CHEQUERA = Y.ID_CHEQUERA ) B
                ON A.ID_VOUCHER = B.ID_VOUCHER
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                AND ACTIVO = 'S'
                $dato
                ORDER BY NUMERO,FECHA ";
        //}
        $oQuery = DB::select($query);
        return $oQuery;
    }


    public static function listVoucherModules($id_user, $id_entidad, $id_depto, $id_anho, $id_mes, $id_tipovoucher)
    {
        /*
        $filterUserCreated = "";
        if($user_created) {
            $filterUserCreated = "AND A.ID_PERSONA = $user_created";
        }
        $dato = "";
        if(!$all_vouchers){
            $dato = "AND A.ID_DEPTO = '$id_depto'
                    $filterUserCreated";
        } else {
            $dato = $filterUserCreated;
        }
        */
        if ($id_tipovoucher == 1 || $id_tipovoucher == 2) {
            $query = "SELECT 0 ID_VOUCHER,TO_CHAR(SYSDATE,'DD/MM/YYYY') AS FECHA,'TODOS' AS NUMERO,'' AS LOTE,'S' AS ACTIVO, '' ASIGNADO
                FROM DUAL
                UNION ALL
                SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.NUMERO) AS NUMERO,A.LOTE,A.ACTIVO,
                        TO_CHAR((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1')) ASIGNADO
                FROM CONTA_VOUCHER A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                AND ACTIVO = 'S'
                ORDER BY NUMERO,FECHA ";
        } else {
            $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.NUMERO) AS NUMERO,A.LOTE,A.ACTIVO,
                        TO_CHAR((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1')) ASIGNADO,
                        B.ID_CVOUCHER,
                        DECODE(NVL(B.NUMERO,1),1,'',B.NUMERO||' - S/. ')||B.IMPORTE AS CHEQUE
                FROM CONTA_VOUCHER A LEFT JOIN ( SELECT X.ID_VOUCHER,X.ID_CVOUCHER, Y.NUMERO,Y.IMPORTE FROM CAJA_CHEQUERA_VOUCHER X, CAJA_CHEQUERA Y WHERE X.ID_CHEQUERA = Y.ID_CHEQUERA ) B
                ON A.ID_VOUCHER = B.ID_VOUCHER
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                AND ACTIVO = 'S'
                ORDER BY NUMERO,FECHA ";
            // dd($query);
        }
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listVoucherModulesAasinet($id_user, $id_entidad, $id_depto, $id_anho, $id_mes, $id_tipovoucher)
    {

        if ($id_tipovoucher == 1 || $id_tipovoucher == 2 || $id_tipovoucher == 9) {
            $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.NUMERO) AS NUMERO,A.LOTE,A.ACTIVO, A.ID_TIPOASIENTO,

                        (SELECT email FROM users u WHERE u.id = A.ID_PERSONA) AS USER_CREATED,

                        TO_CHAR((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1')) ASIGNADO
                FROM CONTA_VOUCHER A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                AND ACTIVO = 'S'
                ORDER BY FECHA DESC ,NUMERO ";
        } else {
            $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.NUMERO) AS NUMERO,A.LOTE,A.ACTIVO, A.ID_TIPOASIENTO,

                        (SELECT email FROM users u WHERE u.id = A.ID_PERSONA) AS USER_CREATED,

                        TO_CHAR((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = 20145 AND X.ESTADO = '1')) ASIGNADO,
                        B.ID_CVOUCHER,
                        DECODE(NVL(B.NUMERO,1),1,'',B.NUMERO||' - S/. ')||B.IMPORTE AS CHEQUE
                FROM CONTA_VOUCHER A LEFT JOIN ( SELECT X.ID_VOUCHER,X.ID_CVOUCHER, Y.NUMERO,Y.IMPORTE FROM CAJA_CHEQUERA_VOUCHER X, CAJA_CHEQUERA Y WHERE X.ID_CHEQUERA = Y.ID_CHEQUERA ) B
                ON A.ID_VOUCHER = B.ID_VOUCHER
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                AND ACTIVO = 'S'
                ORDER BY FECHA DESC, NUMERO ";
        }
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listVoucherNotContabilizados(
        // $id_user,
        $id_entidad,
        $id_depto,
        $id_anho,
        $id_mes,
        $id_tipovoucher
        // , $user_created
    ) {
        // DECODE(NVL((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1'),0),0,0,1) ASIGNADO,
        $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.LOTE,A.ACTIVO,A.NUMERO,

                        T.NOMBRE AS  TIPO_ASIENTO_NOMBRE,
                        TA.ID_TIPOASIENTO AS  TIPO_ASIENTO_TIPO,
                        A.ID_VOUCHER_PARENT,
                        (SELECT email FROM users u WHERE u.id = A.ID_PERSONA) AS USER_CREATED,

                        (CASE WHEN A.ID_VOUCHER_PARENT IS NOT NULL THEN
                        (SELECT ( X_A.NUMERO || '-' || TO_CHAR(X_A.FECHA,'DD/MM/YYYY') || ' ' || X_T.NOMBRE ) AS NNNN
                        FROM CONTA_VOUCHER X_A
                         INNER JOIN TIPO_VOUCHER X_T ON X_A.ID_TIPOVOUCHER = X_T.ID_TIPOVOUCHER
                         INNER JOIN TIPO_ASIENTO X_TA ON X_A.ID_TIPOASIENTO = X_TA.ID_TIPOASIENTO
                        WHERE X_A.ID_VOUCHER = A.ID_VOUCHER_PARENT )
                        ELSE '' END) AS TIPO_ASIENTO_NOMBRE_PARENT

                FROM CONTA_VOUCHER A INNER JOIN TIPO_VOUCHER T ON A.ID_TIPOVOUCHER = T.ID_TIPOVOUCHER
                 INNER JOIN TIPO_ASIENTO TA ON A.ID_TIPOASIENTO = TA.ID_TIPOASIENTO
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '$id_depto'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ACTIVO = 'S'
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                ORDER BY A.ID_VOUCHER DESC";
        // AND A.ID_PERSONA = $id_user
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listVoucherContabilizados(
        // $id_user,
        $id_entidad,
        $id_depto,
        $id_anho,
        $id_mes,
        $id_tipovoucher
        // ,$user_created
    ) {

        // DECODE(NVL((SELECT X.ID_PERSONA FROM CONTA_VOUCHER_PERSONA X WHERE X.ID_VOUCHER = A.ID_VOUCHER AND X.ID_PERSONA = $id_user AND X.ESTADO = '1'),0),0,0,1) ASIGNADO,

        // (SELECT COUNT(*) FROM COMPRA CO WHERE CO.ID_VOUCHER=A.ID_VOUCHER) AS CANTIDAD_COMPRA,
        // (SELECT COUNT(*) FROM CAJA_PAGO CP WHERE CP.ID_VOUCHER=A.ID_VOUCHER) AS CANTIDAD_PAGO,
        // (SELECT COUNT(*) FROM CAJA_RETENCION CR WHERE CR.ID_VOUCHER=A.ID_VOUCHER) AS CANTIDAD_RETENCION,
        // (SELECT COUNT(*) FROM CAJA_DETRACCION CD WHERE CD.ID_VOUCHER=A.ID_VOUCHER) AS CANTIDAD_DETRACCION
        $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.LOTE,A.ACTIVO,A.NUMERO,

                        T.NOMBRE AS  TIPO_ASIENTO_NOMBRE,
                        TA.ID_TIPOASIENTO AS  TIPO_ASIENTO_TIPO,
                        A.ID_VOUCHER_PARENT,
                        (SELECT email FROM users u WHERE u.id = A.ID_PERSONA) AS USER_CREATED,

                        (CASE WHEN A.ID_VOUCHER_PARENT IS NOT NULL THEN
                        (SELECT ( X_A.NUMERO || '-' || TO_CHAR(X_A.FECHA,'DD/MM/YYYY') || ' ' || X_T.NOMBRE ) AS NNNN
                        FROM CONTA_VOUCHER X_A
                         INNER JOIN TIPO_VOUCHER X_T ON X_A.ID_TIPOVOUCHER = X_T.ID_TIPOVOUCHER
                         INNER JOIN TIPO_ASIENTO X_TA ON X_A.ID_TIPOASIENTO = X_TA.ID_TIPOASIENTO
                        WHERE X_A.ID_VOUCHER = A.ID_VOUCHER_PARENT )
                        ELSE '' END) AS TIPO_ASIENTO_NOMBRE_PARENT,
                        C.ACTIVO AS ANHO_ACTIVO

                FROM CONTA_VOUCHER A INNER JOIN TIPO_VOUCHER T ON A.ID_TIPOVOUCHER = T.ID_TIPOVOUCHER
                 INNER JOIN TIPO_ASIENTO TA ON A.ID_TIPOASIENTO = TA.ID_TIPOASIENTO
                 INNER JOIN CONTA_ENTIDAD_ANHO_CONFIG C ON A.ID_ANHO=C.ID_ANHO
                                                        AND A.ID_ENTIDAD=C.ID_ENTIDAD
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '$id_depto'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                AND A.ACTIVO = 'N'
                ORDER BY A.ID_VOUCHER DESC";
        // AND A.ID_PERSONA = $id_user
        // AND A.ID_TIPOASIENTO = '$id_tipoasiento'
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTypeVoucher()
    {
        $query = "SELECT
                ID_TIPOVOUCHER,NOMBRE
                FROM TIPO_VOUCHER ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function assignVouchers($id_voucher, $id_user, $id_cvoucher)
    {
        $sql = "SELECT ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_TIPOVOUCHER FROM CONTA_VOUCHER WHERE ID_VOUCHER = $id_voucher ";
        $data = DB::select($sql);
        foreach ($data as $row) {
            $id_entidad = $row->id_entidad;
            $id_depto = $row->id_depto;
            // $id_anho = $row->id_anho;
            $id_tipovoucher = $row->id_tipovoucher;
        }
        $sql = "UPDATE CONTA_VOUCHER_PERSONA SET ESTADO = '0'
                WHERE ID_PERSONA = $id_user
                AND ID_VOUCHER IN (
                SELECT ID_VOUCHER FROM CONTA_VOUCHER
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '" . $id_depto . "'
                AND ID_TIPOVOUCHER = $id_tipovoucher
                ) ";
        // AND ID_ANHO = $id_anho
        DB::update($sql);

        $sql = "SELECT ID_VOUCHER
                FROM CONTA_VOUCHER_PERSONA
                WHERE ID_VOUCHER = $id_voucher
                AND ID_PERSONA = $id_user ";
        $data = DB::select($sql);

        if (count($data) > 0) {
            DB::table('CONTA_VOUCHER_PERSONA')
                ->where('ID_VOUCHER', $id_voucher)
                ->where('ID_PERSONA', $id_user)
                ->update([
                    'FECHA' => DB::raw('SYSDATE'),
                    'ESTADO' => '1'
                ]);
        } else {
            DB::table('CONTA_VOUCHER_PERSONA')->insert(
                array(
                    'ID_VOUCHER' => $id_voucher,
                    'ID_PERSONA' => $id_user,
                    'ID_CVOUCHER' => $id_cvoucher,
                    'FECHA' => DB::raw('SYSDATE'),
                    'ESTADO' => '1'
                )
            );
        }
    }

    public static function desassignVoucher($id_voucher, $id_user)
    {
        DB::table('CONTA_VOUCHER_PERSONA')
            ->where('ID_VOUCHER', $id_voucher)
            ->where('ID_PERSONA', $id_user)
            ->update([
                'FECHA' => DB::raw('SYSDATE'),
                'ESTADO' => '0'
            ]);
    }

    public static function listAssignVouchers($id_voucher, $id_user)
    {
        $query = "SELECT
                ID_VOUCHER,ID_PERSONA
                FROM CONTA_VOUCHER_PERSONA
                WHERE ID_VOUCHER = $id_voucher
                AND ID_PERSONA = $id_user
                AND ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showVoucherModules($id_user, $id_entidad, $id_depto, $id_tipovoucher)
    {
        /*
        $query = "SELECT A.ID_VOUCHER,A.ID_ANHO,A.ID_MES,A.ID_TIPOASIENTO,A.NUMERO,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.ACTIVO
                    FROM CONTA_VOUCHER A, CONTA_VOUCHER_PERSONA B
                    WHERE A.ID_VOUCHER = B.ID_VOUCHER
                    AND A.ID_ENTIDAD = ".$id_entidad."
                    AND A.ID_DEPTO = '".$id_depto."'
                    AND A.ID_ANHO = ".$id_anho."
                    AND B.ID_PERSONA = ".$id_user."
                    AND A.ID_TIPOVOUCHER = ".$id_tipovoucher."
                    AND B.ESTADO = '1' ";
        */
        $has_id_tipovoucher = $id_tipovoucher ? " AND A.ID_TIPOVOUCHER = $id_tipovoucher" : "";
        $query = "SELECT A.ID_VOUCHER,A.ID_ANHO,A.ID_MES,A.ID_TIPOASIENTO,A.NUMERO,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.ACTIVO,

                    (SELECT X.ID_VOUCHER||'|'||X.ID_TIPOASIENTO||' - '||X.NUMERO||' - '||TO_CHAR(X.FECHA,'DD/MM/YYYY') FROM CONTA_VOUCHER X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO AND X.ID_VOUCHER = A.ID_VOUCHER_PARENT) PARENT,

                    A.ID_VOUCHER_PARENT, C.NOMBRE TIPO_VOUCHER

                    FROM CONTA_VOUCHER A, CONTA_VOUCHER_PERSONA B, TIPO_VOUCHER C
                    WHERE A.ID_VOUCHER = B.ID_VOUCHER
                    AND A.ID_TIPOVOUCHER = C.ID_TIPOVOUCHER
                    AND A.ID_ENTIDAD = $id_entidad
                    AND A.ID_DEPTO = '$id_depto'
                    AND B.ID_PERSONA = $id_user
                    $has_id_tipovoucher
                    AND B.ESTADO = '1'";
        // AND A.ID_ANHO = $id_anho

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listCurrentAccounts($id_entidad, $id_anho, $id_mes, $id_cuentaaasi, $id_ctacte)
    {
        $query = "SELECT
                        ID_ANHO AS ANHO,
                        ID_MES AS MES,
                        '' AS FECHA_ASIENTO,'' AS FECHA_DIGITADO,'' AS FECHA_CONTABILIZADO,
                        ''AS DPTO,''AS CTA,''AS CTA_CORRENTE,''AS TIPO,0 AS LOTE,''AS ITEM,
                        SUM(CASE
                            WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                            ELSE 0
                        END) AS DEBITO,
                        SUM(CASE
                            WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                            ELSE 0
                        END) AS CREDITO,
                        SUM(CAST(COS_VALOR AS DECIMAL(9,2))) AS VALOR,
                        ''AS HISTORICO
                FROM VW_CONTA_DIARIO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_ANHO = " . $id_anho . "
                AND ID_MES BETWEEN NVL(1,0) AND NVL(" . $id_mes . "-1,0)
                AND ID_CUENTAAASI like ('" . $id_cuentaaasi . "%')
                AND ID_CTACTE = '" . $id_ctacte . "'
                GROUP BY ID_ANHO,ID_MES
                HAVING SUM(CAST(COS_VALOR AS DECIMAL(9,2))) <> 0
                UNION ALL
                SELECT
                        ID_ANHO AS ANHO,
                        ID_MES AS MES,
                        TO_CHAR(FEC_ASIENTO,'DD/MM/YYYY') AS FECHA_ASIENTO,
                        TO_CHAR(FEC_DIGITADO,'DD/MM/YYYY') AS FECHA_DIGITADO,
                        TO_CHAR(FEC_CONTABILIZADO,'DD/MM/YYYY') AS FECHA_CONTABILIZADO,
                        ID_DEPTO AS DPTO,
                        ID_CUENTAAASI AS CTA,
                        ID_CTACTE AS CTA_CORRENTE,
                        ID_TIPOASIENTO AS TIPO,
                        COD_AASI AS LOTE,
                        NUM_AASI AS ITEM,
                        CASE
                            WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                            ELSE 0
                        END AS DEBITO,
                        CASE
                            WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                            ELSE 0
                        END AS CREDITO,
                        CAST(COS_VALOR AS DECIMAL(9,2)) AS VALOR,
                        COMENTARIO AS HISTORICO
                FROM VW_CONTA_DIARIO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_ANHO = " . $id_anho . "
                AND ID_MES = NVL(" . $id_mes . ",0)
                AND ID_CUENTAAASI like ('" . $id_cuentaaasi . "%')
                AND ID_CTACTE = '" . $id_ctacte . "'
                ORDER BY FECHA_DIGITADO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listCurrentAccountsTotal($id_entidad, $id_anho, $id_mes, $id_cuentaaasi, $id_ctacte)
    {
        $query = "SELECT
                        SUM(CASE
                            WHEN COS_VALOR > 0 THEN ABS( CAST(-COS_VALOR AS DECIMAL( 9, 2 )))
                            ELSE 0
                        END) AS DEBITO,
                        SUM(CASE
                            WHEN COS_VALOR < 0 THEN ABS( CAST(COS_VALOR AS DECIMAL( 9, 2 )))
                            ELSE 0
                        END) AS CREDITO,
                        SUM(CAST(COS_VALOR AS DECIMAL(9,2))) AS VALOR
                FROM VW_CONTA_DIARIO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_ANHO = " . $id_anho . "
                AND ID_MES BETWEEN NVL(1,0) AND NVL(" . $id_mes . ",0)
                and ID_CUENTAAASI like ('" . $id_cuentaaasi . "%')
                AND ID_CTACTE = '" . $id_ctacte . "' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listGroupLevel($id_entidad, $id_anho, $id_tiporeporte, $id_parent)
    {
        if ($id_parent == "A") {
            $dato = "WHERE ID_PARENT IS NULL";
        } else {
            $dato = "WHERE ID_PARENT = $id_parent";
        }
        $sql = "SELECT
                        ID_NIVEL,
                        ID_PARENT,
                        ID_ENTIDAD,
                        ID_TIPOREPORTE,
                        NOMBRE,
                        NIVEL
                FROM CONTA_NIVEL
                " . $dato . "
                AND ID_ENTIDAD = " . $id_entidad . "
                AND ID_TIPOREPORTE = " . $id_tiporeporte . "
                ORDER BY ID_NIVEL ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showGroupLevel($id_nivel)
    {
        $sql = "SELECT
                        ID_NIVEL,
                        ID_PARENT,
                        ID_ENTIDAD,
                        ID_TIPOREPORTE,
                        NOMBRE,
                        NIVEL
                FROM CONTA_NIVEL
                WHERE ID_NIVEL = " . $id_nivel . " ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addGroupLevel($id_parent, $id_entidad, $id_tiporeporte, $nombre, $nivel)
    {
        DB::table('CONTA_NIVEL')->insert(
            array(
                'ID_PARENT' => $id_parent,
                'ID_ENTIDAD' => $id_entidad,
                'ID_TIPOREPORTE' => $id_tiporeporte,
                'NOMBRE' => $nombre,
                'NIVEL' => $nivel
            )
        );
        $query = "SELECT
                        MAX(ID_NIVEL) ID_NIVEL
                FROM CONTA_NIVEL ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_nivel = $id->id_nivel;
        }
        $sql = AccountingData::showGroupLevel($id_nivel);
        return $sql;
    }
    public static function updateGroupLevel($id_nivel, $id_parent, $id_entidad, $id_tiporeporte, $nombre, $nivel)
    {
        DB::table('CONTA_NIVEL')
            ->where('ID_NIVEL', $id_nivel)
            ->update([
                'NOMBRE' => $nombre,
                'NIVEL' => $nivel
            ]);
        $sql = AccountingData::showGroupLevel($id_nivel);
        return $sql;
    }
    public static function deleteGroupLevel($id_nivel)
    {
        DB::table('CONTA_NIVEL')->where('ID_NIVEL', '=', $id_nivel)->delete();
    }
    public static function listGroupLevelDetails($id_nivel, $id_anho, $id_entidad)
    {
        $sql = "SELECT
                        ID_NDETALLE,
                        ID_NIVEL,
                        ID_ANHO,
                        ID_ENTIDAD,
                        ID_DEPTO,
                        FC_NAMESDEPTO(ID_ENTIDAD,ID_DEPTO) AS NOMBRE
                FROM CONTA_NIVEL_DETALLE
                WHERE ID_NIVEL = " . $id_nivel . "
                AND ID_ANHO = " . $id_anho . "
                AND ID_ENTIDAD = " . $id_entidad . "
                ORDER BY ID_DEPTO ";
        $query = DB::select($sql);
        return $query;
    }
    public static function addGroupLevelDetails($id_nivel, $id_anho, $id_entidad, $id_depto)
    {
        DB::table('CONTA_NIVEL_DETALLE')->insert(
            array(
                'ID_NIVEL' => $id_nivel,
                'ID_ANHO' => $id_anho,
                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO' => $id_depto
            )
        );
    }
    public static function deleteGroupLevelDetails($id_nivel, $id_anho)
    {
        DB::table('CONTA_NIVEL_DETALLE')->where('ID_NIVEL', '=', $id_nivel)->where('ID_ANHO', '=', $id_anho)->delete();
    }
    public static function LevelDepto($id_entidad, $parent, $id_nivel, $id_anho)
    {
        if ($parent == "A") {
            $dato = "AND A.ID_PARENT IS NULL";
        } else {
            $dato = "AND A.ID_PARENT = '$parent' ";
        }
        $query = "SELECT
                        A.ID_DEPTO,
                        A.ID_PARENT,
                        A.NOMBRE,
                        A.ES_GRUPO,
                        (SELECT COUNT(ID_DEPTO) FROM CONTA_NIVEL_DETALLE B WHERE  B.ID_ENTIDAD = A.ID_ENTIDAD AND B.ID_DEPTO = A.ID_DEPTO AND B.ID_NIVEL = $id_nivel AND B.ID_ANHO = $id_anho) AS ASIGNADO
                FROM CONTA_ENTIDAD_DEPTO A
                WHERE A.ID_ENTIDAD = $id_entidad
                $dato
                ORDER BY A.ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listGroupAccount($id_entidad, $id_anho, $id_tiporeporte, $id_parent)
    {
        if ($id_parent == "A") {
            $dato = "WHERE ID_PARENT IS NULL";
        } else {
            $dato = "WHERE ID_PARENT = $id_parent";
        }
        $sql = "SELECT
                        ID_CUENTA,
                        ID_PARENT,
                        ID_ENTIDAD,
                        ID_TIPOREPORTE,
                        NOMBRE,
                        ORDEN,
                        DC
                FROM CONTA_CUENTA
                " . $dato . "
                AND ID_ENTIDAD = " . $id_entidad . "
                AND ID_TIPOREPORTE = " . $id_tiporeporte . "
                ORDER BY ORDEN ";
        $query = DB::select($sql);
        return $query;
    }
    public static function deleteGroupAccountV2($id_cuenta)
    {
        // $ctaDetalle = DB::table('CONTA_CUENTA_DETALLE')
        //     ->where('ID_CUENTA', '=', $id_cuenta)
        //     ->get();
        // if ($ctaDetalle->count() > 0) {
        //     throw new \Exception('!Alto tiene cuentas');
        // }
        DB::table('CONTA_CUENTA')->where('ID_CUENTA', '=', $id_cuenta)->delete();
    }
    public static function addGroupAccount($id_parent, $id_entidad, $id_tiporeporte, $nombre, $orden, $dc)
    {
        DB::table('CONTA_CUENTA')->insert(
            array(
                'ID_PARENT' => $id_parent,
                'ID_ENTIDAD' => $id_entidad,
                'ID_TIPOREPORTE' => $id_tiporeporte,
                'NOMBRE' => $nombre,
                'ORDEN' => $orden,
                'DC' => $dc
            )
        );
        $query = "SELECT
                        MAX(ID_CUENTA) ID_CUENTA
                FROM CONTA_CUENTA ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_cuenta = $id->id_cuenta;
        }
        $sql = AccountingData::showGroupLevel($id_cuenta);
        return $sql;
    }
    public static function addGroupAccountDetailsV2($id_cuenta, $id_anho, $id_cuentaaasi)
    {
        DB::table('CONTA_CUENTA_DETALLE')->insert(
            array(
                'ID_CUENTA' => $id_cuenta,
                'ID_ANHO' => $id_anho,
                'ID_CUENTAAASI' => $id_cuentaaasi
            )
        );


        // $query = "SELECT
        //                 MAX(ID_CDETALLE) ID_CDETALLE
        //         FROM CONTA_CUENTA_DETALLE ";

        // $oQuery = DB::select($query);
        // foreach($oQuery as $id){
        //     $id_cdetalle = $id->id_cdetalle;
        // }
        // if(count($cta_cte) > 0){
        //     foreach ($cta_cte as $id_cta_cte){
        // AccountingData::addGroupAccountDetailsCte($id_cdetalle,$id_cta_cte);
        //     }
        // }
    }
    public static function addGroupAccountDetails($id_cuenta, $id_anho, $id_cuentaaasi, $cta_cte)
    {
        DB::table('CONTA_CUENTA_DETALLE')->insert(
            array(
                'ID_CUENTA' => $id_cuenta,
                'ID_ANHO' => $id_anho,
                'ID_CUENTAAASI' => $id_cuentaaasi
            )
        );
        $query = "SELECT
                        MAX(ID_CDETALLE) ID_CDETALLE
                FROM CONTA_CUENTA_DETALLE ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_cdetalle = $id->id_cdetalle;
        }
        if (count($cta_cte) > 0) {
            foreach ($cta_cte as $id_cta_cte) {
                AccountingData::addGroupAccountDetailsCte($id_cdetalle, $id_cta_cte);
            }
        }
    }
    public static function deleteGroupAccountDetails($id_cdetalle)
    {
        DB::table('CONTA_CUENTA_DETALLE')->where('ID_CDETALLE', '=', $id_cdetalle)->delete();
    }
    public static function addGroupAccountDetailsCte($id_cdetalle, $ctacte)
    {
        DB::table('CONTA_CUENTA_CTE')->insert(
            array(
                'ID_CDETALLE' => $id_cdetalle,
                'CTA_CTE' => $ctacte
            )
        );
    }
    public static function deleteGroupAccountDetailsCte($id_cdetalle)
    {
        DB::table('CONTA_CUENTA_CTE')->where('ID_CDETALLE', '=', $id_cdetalle)->delete();
    }
    public static function deleteCteByIdV2($id_cta_cte)
    {
        DB::table('CONTA_CUENTA_CTE')->where('ID_CTACTE', '=', $id_cta_cte)->delete();
    }
    public static function listGroupAccountDetailsV2($id_cuenta, $id_anho)
    {
        $sql = "SELECT
                        A.ID_CDETALLE,A.ID_CUENTA,A.ID_CUENTAAASI,B.NOMBRE, B.ID_RESTRICCION, B.ID_TIPOPLAN
                FROM CONTA_CUENTA_DETALLE A, CONTA_CTA_DENOMINACIONAL B
                WHERE A.ID_CUENTAAASI = B.ID_CUENTAAASI
                AND A.ID_CUENTA = " . $id_cuenta . "
                AND A.ID_ANHO = " . $id_anho . "
                AND B.ID_TIPOPLAN = 1 ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listGroupAccountDetails($id_cuenta, $id_anho)
    {
        $sql = "SELECT
                        A.ID_CDETALLE,A.ID_CUENTA,A.ID_CUENTAAASI,B.NOMBRE
                FROM CONTA_CUENTA_DETALLE A, CONTA_CTA_DENOMINACIONAL B
                WHERE A.ID_CUENTAAASI = B.ID_CUENTAAASI
                AND A.ID_CUENTA = " . $id_cuenta . "
                AND A.ID_ANHO = " . $id_anho . "
                AND B.ID_TIPOPLAN = 1 ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listGroupAccountCteV2($id_cuentaaasi, $id_anho, $id_entidad)
    {
        $sql = "SELECT
            B.ID_CTACTE AS ID_CTA_CTE, B.ID_CDETALLE,A.ID_CUENTAAASI, A.ID_CUENTA, B.CTA_CTE AS ID_CTACTE, C.NOMBRE
        FROM CONTA_CUENTA_DETALLE A, CONTA_CUENTA_CTE B, CONTA_ENTIDAD_DEPTO C
        WHERE A.ID_CDETALLE = B.ID_CDETALLE
        AND B.CTA_CTE = C.ID_DEPTO
        AND A.ID_CUENTAAASI  = $id_cuentaaasi
        AND A.ID_ANHO = $id_anho
        AND C.ID_ENTIDAD = $id_entidad";
        $query = DB::select($sql);
        return $query;
    }
    public static function listGroupAccountCTE($id_cuenta, $id_anho)
    {
        $sql = "SELECT
                        B.ID_CTACTE,B.ID_CDETALLE,A.ID_CUENTAAASI,B.CTA_CTE
                FROM CONTA_CUENTA_DETALLE A, CONTA_CUENTA_CTE B
                WHERE A.ID_CDETALLE = B.ID_CDETALLE
                AND A.ID_CUENTA = " . $id_cuenta . "
                AND A.ID_ANHO = " . $id_anho . " ";
        $query = DB::select($sql);
        return $query;
    }
    public static function showAccountingEntryValidate($id_dinamica)
    {
        $edit = "N";
        $cta = "N";
        $depto = "N";
        $cta_cte = "N";
        $sql = "SELECT ID_ASIENTO
                FROM CONTA_DINAMICA_ASIENTO A
                WHERE ID_DINAMICA = " . $id_dinamica . "
                AND UNICO = 'M' ";
        $query = DB::select($sql);
        foreach ($query as $id) {
            $edit = "S";
            $depto = "S";
        }
        $sql = "SELECT ID_ASIENTO
                FROM CONTA_DINAMICA_ASIENTO A
                WHERE ID_DINAMICA = " . $id_dinamica . "
                AND UNICO_CTACTE = 'M' ";
        $query = DB::select($sql);
        foreach ($query as $id) {
            $edit = "S";
            $cta_cte = "S";
        }
        $query = "SELECT COUNT(A.DC) AS CANT,A.DC,ID_INDICADOR,SUM(PORCENTAJE) TOTAL
                FROM CONTA_DINAMICA_ASIENTO A
                WHERE A.ID_DINAMICA = " . $id_dinamica . "
                GROUP BY A.DC,A.ID_INDICADOR
                HAVING COUNT(A.DC) > 1 AND SUM(PORCENTAJE) > 1 ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $edit = "S";
            $cta = "S";
        }
        $data[] = [
            'edit' => $edit,
            'cta' => $cta,
            'cta_cte' => $cta_cte,
            'depto' => $depto
        ];
        return $data;
    }
    public static function listEntryAccount($id_dinamica)
    {
        /*$query = "SELECT COUNT(A.DC) AS CANT,A.DC,ID_INDICADOR,SUM(PORCENTAJE) TOTAL
                FROM CONTA_DINAMICA_ASIENTO A
                WHERE A.ID_DINAMICA = ".$id_dinamica."
                GROUP BY A.DC,A.ID_INDICADOR
                HAVING COUNT(A.DC) > 1 AND SUM(PORCENTAJE) > 1 ";
        */
        $query = "SELECT COUNT(A.DC) AS CANT,A.DC,ID_INDICADOR,SUM(PORCENTAJE) TOTAL,
                (SELECT COUNT(ID_DEPTO) FROM CONTA_DINAMICA_DEPTO X WHERE X.ID_ASIENTO = A.ID_ASIENTO) DEPTO
                FROM CONTA_DINAMICA_ASIENTO A
                WHERE A.ID_DINAMICA = " . $id_dinamica . "
                GROUP BY A.ID_ASIENTO,A.DC,A.ID_INDICADOR
                HAVING ( COUNT(A.DC) > 1 OR SUM(PORCENTAJE) > 1 OR (SELECT COUNT(ID_DEPTO) FROM CONTA_DINAMICA_DEPTO X WHERE X.ID_ASIENTO = A.ID_ASIENTO) > 1 ) ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listEntryAccountCta($id_dinamica, $id_indicador, $dc)
    {
        $query = "SELECT ID_ASIENTO,ID_CUENTAAASI,NOMBRE,PORCENTAJE
                FROM CONTA_DINAMICA_ASIENTO A
                WHERE ID_DINAMICA = " . $id_dinamica . "
                AND DC = '" . $dc . "'
                AND ID_INDICADOR = '" . $id_indicador . "' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listEntryDepto($id_asiento)
    {
        $query = "SELECT ID_ASIENTO,ID_DEPTO
                    FROM CONTA_DINAMICA_DEPTO
                    WHERE ID_ASIENTO = " . $id_asiento . "
                    ORDER BY ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listEntryCtaCte($id_asiento)
    {
        $query = "SELECT ID_ASIENTO,ID_CTACTE
                FROM CONTA_DINAMICA_CTA_CTE
                WHERE ID_ASIENTO = " . $id_asiento . "
                ORDER BY ID_CTACTE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function AccountingYearMonthTC($id_entidad, $id_moneda, $tiene_params, $params)
    {
        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
        foreach ($data_anho as $item) {
            $id_anho = $item->id_anho;
            $id_anho_actual = $item->id_anho_actual;
        }
        if ($id_anho == $id_anho_actual) {
            $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
            foreach ($data_mes as $item) {
                $id_mes = $item->id_mes;
                $id_mes_actual = $item->id_mes_actual;
            }
            if ($id_mes == $id_mes_actual) {
                $tipcam = GlobalMethods::verificaTipoCambio();
                if ($id_moneda == 7) {
                    $tipcam["tc"] = true;
                }
                if ($tipcam["tc"] == true) {
                    if ($tiene_params == "S") {
                        if ($params != null) {
                            $return = [
                                'nerror' => 0,
                                'msgerror' => "OK",
                                'id_anho' => $id_anho,
                                'id_mes' => $id_mes,
                                'tc' => $tipcam["denominacional"]
                            ];
                        } else {
                            $return = [
                                'nerror' => 1,
                                'msgerror' => "Attention: Check, missing parameters"
                            ];
                        }
                    } else {
                        $return = [
                            'nerror' => 0,
                            'msgerror' => "OK",
                            'id_anho' => $id_anho,
                            'id_mes' => $id_mes,
                            'tc' => $tipcam["denominacional"]
                        ];
                    }
                } else {
                    $return = [
                        'nerror' => 2,
                        'msgerror' => "Actualice TIPO de CAMBIO!!!"
                    ];
                }
            } else {
                $return = [
                    'nerror' => 3,
                    'msgerror' => "No Existe Mes Activo!!!"
                ];
            }
        } else {
            $return = [
                'nerror' => 4,
                'msgerror' => "No Existe Año Activo!!!"
            ];
        }
        return $return;
    }
    public static function AccountingYearMonth($id_entidad)
    {
        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
        foreach ($data_anho as $item) {
            $id_anho = $item->id_anho;
            $id_anho_actual = $item->id_anho_actual;
        }
        if ($id_anho == $id_anho_actual) {
            $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
            foreach ($data_mes as $item) {
                $id_mes = $item->id_mes;
                $id_mes_actual = $item->id_mes_actual;
            }
            if ($id_mes == $id_mes_actual) {
                $return = [
                    'nerror' => 0,
                    'msgerror' => "OK",
                    'id_anho' => $id_anho,
                    'id_mes' => $id_mes,
                ];
            } else {
                $return = [
                    'nerror' => 3,
                    'msgerror' => "No Existe Mes Activo!!!"
                ];
            }
        } else {
            $return = [
                'nerror' => 4,
                'msgerror' => "No Existe Año Activo!!!"
            ];
        }
        return $return;
    }

    public static function AccountingYear($id_entidad)
    {
        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
        foreach ($data_anho as $item) {
            $id_anho = $item->id_anho;
            $id_anho_actual = $item->id_anho_actual;
        }
        if ($id_anho == $id_anho_actual) {
            $return = [
                'nerror' => 0,
                'msgerror' => "OK",
                'id_anho' => $id_anho,
                //'id_mes'=>$id_mes,
            ];
        } else {
            $return = [
                'nerror' => 4,
                'msgerror' => "No Existe Año Activo!!!"
            ];
        }
        return $return;
    }

    public static function listTypeArrangements()
    {
        $query = "SELECT ID_TIPOARREGLO,NOMBRE
                FROM TIPO_ARREGLO
                ORDER BY ID_TIPOARREGLO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addArrangements($id_entidad, $id_depto, $id_user, $id_modulo, $id_tipoarreglo, $id_origen, $motivo, $id_tipoorigen)
    {
        DB::table('ARREGLO')->insert(
            array(
                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO' => $id_depto,
                'ID_TIPOARREGLO' => $id_tipoarreglo,
                'ID_PERSONA' => $id_user,
                'ID_MODULO' => $id_modulo,
                'ID_ORIGEN' => $id_origen,
                'ID_TIPOORIGEN' => $id_tipoorigen,
                'MOTIVO' => $motivo,
                'FECHA' => DB::raw('SYSDATE'),
                'ESTADO' => '1'
            )
        );
        $query = "SELECT
                        MAX(ID_ARREGLO) ID_ARREGLO
                FROM ARREGLO ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item) {
            $id_arreglo = $item->id_arreglo;
        }
        return $id_arreglo;
    }
    public static function showArrangement($id)
    {
        return DB::table('ARREGLO')->where('ID_ARREGLO', $id)->first();
    }
    public static function updateArrangement($id, $content)
    {
        return DB::table('ARREGLO')->where('ID_ARREGLO', $id)->update($content);
    }
    public static function addArrangementsDetails($content)
    {
        DB::table('ARREGLO_DETALLE')->insert($content);
    }
    public static function listExternalSystemSeat($id_entidad, $id_depto, $id_tipoasiento)
    {
        $query = "SELECT
                        ID_SISTEMAEXTERNO,ID_ENTIDAD,ID_DEPTO,ID_TIPOASIENTO,CODIGO,NOMBRE,ESTADO
                FROM SISTEMA_EXTERNO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = '" . $id_depto . "'
                AND ID_TIPOASIENTO = '" . $id_tipoasiento . "'
                ORDER BY ID_SISTEMAEXTERNO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listExternalSystem($id_entidad, $id_depto)
    {
        $query = "SELECT
                        ID_SISTEMAEXTERNO,ID_ENTIDAD,ID_DEPTO,ID_TIPOASIENTO,CODIGO,NOMBRE,ESTADO
                FROM SISTEMA_EXTERNO
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = '" . $id_depto . "'
                ORDER BY ID_SISTEMAEXTERNO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listSeatAaasinet($id_anho, $id_voucher, $id_empresa)
    {
        $query = "SELECT
                            CUENTA,CUENTA_CTE,FONDO,DEPTO,RESTRICCION,
                            IMPORTE,
                            IMPORTE_ME,
                            DESCRIPCION,
                            MEMO,
                            CTA_EMPRESARIAL
                    FROM(
                            SELECT
                                    A.ID_ORIGEN,
                                    A.CUENTA,A.CUENTA_CTE,A.FONDO,A.DEPTO,A.RESTRICCION,
                                    A.IMPORTE,
                                    A.IMPORTE_ME,
                                    A.DESCRIPCION,
                                    A.MEMO,
                                    (SELECT X.ID_CUENTAEMPRESARIAL
                                       FROM ELISEO.CONTA_EMPRESA_CTA X
                                      WHERE     X.ID_CUENTAAASI = A.CUENTA
                                            AND X.ID_RESTRICCION = A.RESTRICCION
                                            AND X.ID_TIPOPLAN = 1
                                            AND X.ID_ANHO = " . $id_anho . "
                                            AND X.ID_EMPRESA = " . $id_empresa . " AND ROWNUM=1) AS CTA_EMPRESARIAL
                            FROM ELISEO.CONTA_ASIENTO A
                            WHERE A.VOUCHER = '" . $id_voucher . "'
                            AND A.AGRUPA<>'S'
                            UNION ALL
                            SELECT
                                    MAX(A.ID_ORIGEN) AS ID_ORIGEN,
                                    A.CUENTA,A.CUENTA_CTE,
                                    A.FONDO,
                                    A.DEPTO,
                                    A.RESTRICCION,
                                    SUM( IMPORTE ) AS IMPORTE,
                                    SUM( IMPORTE_ME ) AS IMPORTE_ME,
                                    A.DESCRIPCION,
                                    MAX(A.MEMO) AS MEMO,
                                    (SELECT X.ID_CUENTAEMPRESARIAL
                                       FROM ELISEO.CONTA_EMPRESA_CTA X
                                      WHERE     X.ID_CUENTAAASI = A.CUENTA
                                            AND X.ID_RESTRICCION = A.RESTRICCION
                                            AND X.ID_TIPOPLAN = 1
                                            AND X.ID_ANHO = " . $id_anho . "
                                            AND X.ID_EMPRESA = " . $id_empresa . " AND ROWNUM=1) AS CTA_EMPRESARIAL
                            FROM ELISEO.CONTA_ASIENTO A
                            WHERE VOUCHER = '" . $id_voucher . "'
                            AND AGRUPA='S'
                            GROUP BY CUENTA, CUENTA_CTE, FONDO, DEPTO, RESTRICCION, DESCRIPCION
                    ) A where (A.IMPORTE <> 0 and upper(A.DESCRIPCION) NOT LIKE upper('%<< Anulado>>%'))
                ORDER BY ID_ORIGEN, IMPORTE DESC";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function uploadSeatAaasinet($id_entidad, $id_depto, $id_anho, $id_tipoorigen, $id_voucher, $numero, $fecha, $codigo, $fecha_aasi, $periodo, $url_aasinet, $descripcion, $certificado = "")
    {
        // IMPORTE_ME as \"CurrencyAmount\",

        $query = "SELECT
                        '" . $url_aasinet . "' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\"," . $id_entidad . "),xmlelement(name \"Certificate\",'" . $certificado . "')) context,
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '" . $codigo . "'))) Parameters,
                        xmlelement(name \"ItemId\",'" . $numero . "')||
                        xmlelement(name \"PostedPeriod\",'" . $periodo . "')||
                        xmlelement(name \"JournalDate\",'" . $fecha_aasi . "')||
                        xmlelement(name \"Description\",'" . $descripcion . "'||'-'||'" . $numero . "'||'-'||'" . $fecha . "') Description,
                        xmlelement(name \"Item\",
                                xmlforest(
                                    rownum as \"ItemId\",
                                    CUENTA as \"AccountCode\",
                                    CUENTA_CTE as \"SubAccountCode\",
                                    FONDO as \"FundCode\",
                                    DEPTO as \"FunctionCode\",
                                    RESTRICCION as \"RestrictionCode\",
                                    IMPORTE as \"EntityValue\",
                                    IMPORTE_ME as \"AccountValue\",
                                    DESCRIPCION as \"Description\",
                                    MEMO as \"Memo\"
                                )
                        ) as items
                    FROM (
                        SELECT
                        CUENTA,CUENTA_CTE,FONDO,DEPTO,RESTRICCION,
                                            IMPORTE,
                                            -- (CASE WHEN IMPORTE_ME = 0 THEN '' ELSE (CASE WHEN CUENTA = '1112025' THEN TO_CHAR(IMPORTE_ME,'99999999.99') ELSE '' END )END) AS IMPORTE_ME,
                                            (CASE " . $id_entidad . "
                                                WHEN 7124 THEN (CASE WHEN IMPORTE_ME = 0 THEN '' ELSE (CASE WHEN CUENTA = '1112025' THEN TO_CHAR(IMPORTE_ME,'99999999.99') ELSE '' END )END)
                                                WHEN 9415 THEN (CASE WHEN IMPORTE_ME = 0 THEN '' ELSE (CASE WHEN CUENTA IN ('1112025','2130101','1131001') THEN TO_CHAR(IMPORTE_ME,'99999999.99') ELSE '' END )END)
                                                ELSE (CASE WHEN IMPORTE_ME = 0 THEN '' ELSE (CASE WHEN CUENTA IN ('1112025','1112030','1111020','1111035') THEN TO_CHAR(IMPORTE_ME,'99999999.99') ELSE '' END )END)
                                            END) AS IMPORTE_ME,
                                            DESCRIPCION,
                                            MEMO
                                            FROM(
                                            SELECT ID_ORIGEN,
                                            CUENTA,CUENTA_CTE,FONDO,DEPTO,RESTRICCION,
                                            NVL(IMPORTE,0) AS IMPORTE,
                                            NVL(IMPORTE_ME,0) AS IMPORTE_ME,
                                            DESCRIPCION,
                                            MEMO
                                            FROM CONTA_ASIENTO
                                            WHERE VOUCHER = '" . $id_voucher . "'
                                            -- AND AGRUPA<>'S'
                                            AND NVL(AGRUPA,'N')<>'S'
                                    UNION ALL
                                    SELECT
                                            MAX(ID_ORIGEN) AS ID_ORIGEN,
                                            CUENTA,CUENTA_CTE,FONDO,DEPTO,RESTRICCION,
                                            NVL(SUM(IMPORTE),0) AS IMPORTE,
                                            NVL(SUM(IMPORTE_ME),0) AS IMPORTE_ME,
                                            DESCRIPCION,
                                            MAX(MEMO) AS MEMO
                                            FROM CONTA_ASIENTO
                                            WHERE VOUCHER = '" . $id_voucher . "'
                                            -- AND AGRUPA='S'
                                            AND NVL(AGRUPA,'N')='S'
                                            GROUP BY
                                            CUENTA, CUENTA_CTE, FONDO, DEPTO, RESTRICCION,
                                            DESCRIPCION
                        ) A WHERE (A.IMPORTE <> 0 and upper(A.DESCRIPCION) NOT LIKE upper('%<< Anulado>>%'))
                    ORDER BY ID_ORIGEN, IMPORTE DESC
                    ) X ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function ExternalSystem($id_entidad, $id_depto, $id_anho, $id_tipoasiento, $id_modulo)
    {
        $query = "SELECT
                    B.CODIGO,
                    (SELECT X.URL_AASINET FROM CONTA_ENTIDAD_CONFIGURACION X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD) as URL_AASINET
                    FROM CONTA_VOUCHER_CONFIG A, SISTEMA_EXTERNO B
                    WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
                    AND A.ID_DEPTO = B.ID_DEPTO
                    AND A.ID_SISTEMAEXTERNO = B.ID_SISTEMAEXTERNO
                    AND A.ID_ENTIDAD = " . $id_entidad . "
                    AND A.ID_DEPTO = '" . $id_depto . "'
                    AND A.ID_ANHO = " . $id_anho . "
                    AND A.ID_TIPOASIENTO = '" . $id_tipoasiento . "'
                    AND A.ID_MODULO = " . $id_modulo . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function EntidadConfiguracion($id_entidad)
    {
        $query = "SELECT * FROM Conta_Entidad_Configuracion
                        WHERE ID_ENTIDAD = $id_entidad ";
        $oQuery = DB::select($query);
        return $oQuery;
    }


    public static function ExternalSystemURL($id_entidad)
    {
        $query = "SELECT URL_AASINET
                    FROM CONTA_ENTIDAD_CONFIGURACION
                    WHERE ID_ENTIDAD = " . $id_entidad . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showExternalSystem($id_sistemaexterno)
    {
        $query = "SELECT
                            ID_SISTEMAEXTERNO,ID_ENTIDAD,ID_DEPTO,ID_TIPOASIENTO,CODIGO,NOMBRE,ESTADO
                    FROM SISTEMA_EXTERNO
                    WHERE ID_SISTEMAEXTERNO = " . $id_sistemaexterno . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addExternalSystem($id_entidad, $id_depto, $id_tipoasiento, $codigo, $nombre, $estado)
    {
        DB::table('SISTEMA_EXTERNO')->insert(
            array('ID_ENTIDAD' => $id_entidad, 'ID_DEPTO' => $id_depto, 'ID_TIPOASIENTO' => $id_tipoasiento, 'CODIGO' => $codigo, 'NOMBRE' => $nombre, 'ESTADO' => $estado)
        );
        $query = "SELECT
                        MAX(ID_SISTEMAEXTERNO) ID_SISTEMAEXTERNO
                FROM SISTEMA_EXTERNO ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_sistemaexterno = $id->id_sistemaexterno;
        }
        $sql = AccountingData::showExternalSystem($id_sistemaexterno);
        return $sql;
    }
    public static function updateExternalSystem($id_sistemaexterno, $codigo, $nombre, $estado)
    {
        DB::table('SISTEMA_EXTERNO')
            ->where('ID_SISTEMAEXTERNO', $id_sistemaexterno)
            ->update([
                'CODIGO' => $codigo,
                'NOMBRE' => $nombre,
                'ESTADO' => $estado
            ]);
        $sql = AccountingData::showExternalSystem($id_sistemaexterno);
        return $sql;
    }
    public static function deleteExternalSystem($id_sistemaexterno)
    {
        DB::table('SISTEMA_EXTERNO')->where('ID_SISTEMAEXTERNO', '=', $id_sistemaexterno)->delete();
    }

    public static function listAllFunding()
    {
        $query = "SELECT ID_FONDO, ID_PARENT, NOMBRE, ES_GRUPO
                  FROM CONTA_FONDO ORDER BY ID_FONDO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listFunding($text_search)
    {
        $query = "SELECT ID_FONDO, ID_PARENT, NOMBRE, ES_GRUPO
                  FROM CONTA_FONDO
                  where (id_fondo like '%" . $text_search . "%' or upper(nombre) like upper('%" . $text_search . "%')) ORDER BY ID_FONDO";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function createCurrentAccountAaasinet($id_entidad, $url_aasinet, $id_ruc, $nombre, $certificado = "")
    {
        $replace_nombre = str_replace("'", "", $nombre);
        $query = "SELECT
                        '" . $url_aasinet . "' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\"," . $id_entidad . "),xmlelement(name \"Certificate\",'" . $certificado . "')) context,
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'SubAccountsTransfer')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"SubAccountsTransferParams\",xmlelement(name \"SubAccountType\",
                        'Vendor'))) Parameters,
                        xmlelement(name \"Item\",
                            xmlelement(name \"ItemId\",rownum),
                            xmlelement(name \"Code\",RUC),
                            xmlelement(name \"Name\",NAMES),
                            xmlelement(name \"Description\",NADA),
                            xmlelement(name \"NationalCode\",RUC),
                            xmlelement(name \"IsEligibleFor1099\",0),
                            xmlelement(name \"IsEnabled\",1),
                            xmlelement(name \"IsDirectDebitAuthorized\",1),
                            xmlelement(name \"IsReceiptByEmailAllowed\",0),
                            xmlelement(name \"Bank\",
                                xmlelement(name \"Code\",NADA),
                                xmlelement(name \"Name\", NADA)
                            ) ,
                            xmlelement(name \"BankInfoType\",
                                    xmlelement(name \"Code\",NADA),
                                    xmlelement(name \"Name\", NADA)
                            ) ,
                            xmlelement(name \"AccountNumber\",NADA),
                            xmlelement(name \"AccountDigit\",NADA),
                            xmlelement(name \"RoutingNumber\",NADA),
                            xmlelement(name \"RoutingDigit\",NADA),
                            xmlelement(name \"Vendor\",
                                xmlelement(name \"AccountIdentifier\",NADA),
                                xmlelement(name \"DoingBusinessAs\",NADA)
                            ),
                            xmlelement(name \"Address\",
                                xmlelement(name \"Line1\",NADA),
                                xmlelement(name \"Line2\",NADA),
                                xmlelement(name \"ZipPostalCode\",NADA),
                                xmlelement(name \"IsMailingAddress\",0),
                                xmlelement(name \"AddressTypeEnum\",0),
                                xmlelement(name \"CountryName\",NADA),
                                xmlelement(name \"LocalityParentName\",NADA),
                                xmlelement(name \"LocalityName\",NADA)
                            ),
                            xmlelement(name \"Phone\",
                                xmlelement(name \"CountryAreaCode\",NADA),
                                xmlelement(name \"CityAreaCode\",NADA),
                                xmlelement(name \"LocalNumber\",NADA),
                                xmlelement(name \"Extension\",NADA),
                                xmlelement(name \"IsDefault\",NADA),
                                xmlelement(name \"PhoneType\",
                                    xmlelement(name \"Code\",NADA),
                                    xmlelement(name \"Name\", NADA)
                                )
                            ),
                            xmlelement(name \"EContact\",
                                xmlelement(name \"Contact\",NADA),
                                xmlelement(name \"DisplayAs\",NADA),
                                xmlelement(name \"ContactTypeEnum\",NADA),
                                xmlelement(name \"IsDefault\", NADA)
                            )
                        ) as items
                        FROM (
                            SELECT " . $id_ruc . " AS RUC,'" . $replace_nombre . "' AS NAMES,'' AS NADA
                            FROM DUAL
                        ) X ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addAccountingSeat($id_asiento, $id_tipoorigen, $id_origen, $id_fondo, $id_depto, $id_cuentaaasi, $id_ctacte, $id_restriccion, $importe, $descripcion, $id_voucher, $importe_me, $tipo)
    {
        $error      = 0;
        $msg_error  = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ACCOUNTING.SP_ADD_CONTA_ASIENTO(:P_ID_ASIENTO,:P_ID_TIPOORIGEN,:P_ID_ORIGEN,:P_FONDO,:P_DEPTO,:P_CUENTA,:P_CUENTA_CTE,:P_RESTRICCION,:P_IMPORTE,:P_DESCRIPCION,:P_VOUCHER,:P_IMPORTE_ME,:P_TIPO); end;");
            $stmt->bindParam(':P_ID_ASIENTO', $id_asiento, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPOORIGEN', $id_tipoorigen, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ORIGEN', $id_origen, PDO::PARAM_INT);
            $stmt->bindParam(':P_FONDO', $id_fondo, PDO::PARAM_STR);
            $stmt->bindParam(':P_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_CUENTA', $id_cuentaaasi, PDO::PARAM_STR);
            $stmt->bindParam(':P_CUENTA_CTE', $id_ctacte, PDO::PARAM_STR);
            $stmt->bindParam(':P_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
            $stmt->bindParam(':P_DESCRIPCION', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':P_VOUCHER', $id_voucher, PDO::PARAM_INT);
            $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
            $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        } catch (Exception $e) {
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function deleteAccountingSeat($id_asiento)
    {
        $error      = 0;
        $msg_error  = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ACCOUNTING.SP_DELETE_CONTA_ASIENTO(:P_ID_ASIENTO); end;");
            $stmt->bindParam(':P_ID_ASIENTO', $id_asiento, PDO::PARAM_INT);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        } catch (Exception $e) {
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function generateAccountingSeat($id_dinamica, $id_tipoorigen, $id_origen, $importe, $descripcion, $id_voucher, $importe_me)
    {
        $error      = 0;
        $msg_error  = '';
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_ACCOUNTING.SP_ASIENTO_CONTABLE(:P_ID_DINAMICA,:P_ID_TIPOORIGEN,:P_ID_ORIGEN,:P_IMPORTE,:P_DESCRIPCION,:P_VOUCHER,:P_IMPORTE_ME,:P_ERROR,:P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPOORIGEN', $id_tipoorigen, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ORIGEN', $id_origen, PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
            $stmt->bindParam(':P_DESCRIPCION', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':P_VOUCHER', $id_voucher, PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_STR);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $objReturn['error']   = $error;
            $objReturn['message'] = $msg_error;
            return $objReturn;
        } catch (Exception $e) {
            $jResponse['error']   = 1;
            $jResponse['message'] = $e->getMessage();
            return $jResponse;
        }
    }
    public static function listAccountingSeat($id_tipoorigen, $id_origen)
    {
        $query = "SELECT
                        ID_ASIENTO,ID_TIPOORIGEN,ID_ORIGEN,FONDO AS ID_FONDO, CUENTA AS ID_CUENTAAASI,DEPTO AS ID_DEPTO,
                        CUENTA_CTE AS ID_CTACTE,RESTRICCION AS ID_RESTRICCION,IMPORTE,IMPORTE_ME,DESCRIPCION,DECODE(SIGN(IMPORTE),-1,'C','D') AS DC
                FROM CONTA_ASIENTO
                WHERE ID_TIPOORIGEN = " . $id_tipoorigen . "
                AND ID_ORIGEN = " . $id_origen . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showAccountingSeat($id_asiento)
    {
        $query = "SELECT
                        ID_ASIENTO,ID_TIPOORIGEN,ID_ORIGEN,FONDO AS ID_FONDO, CUENTA AS ID_CUENTAAASI,DEPTO AS ID_DEPTO,
                        CUENTA_CTE AS ID_CTACTE,RESTRICCION AS ID_RESTRICCION,IMPORTE,IMPORTE_ME,DESCRIPCION,DECODE(SIGN(IMPORTE),-1,'C','D') AS DC
                FROM CONTA_ASIENTO
                WHERE ID_ASIENTO = " . $id_asiento . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showAccountingSeatTotal($id_tipoorigen, $id_origen)
    {
        $query = "SELECT
                        SUM(DECODE(SIGN(IMPORTE),-1,IMPORTE,0)) CREDITO,
                        SUM(DECODE(SIGN(IMPORTE),1,IMPORTE,0)) DEBITO,
                        SUM(DECODE(SIGN(IMPORTE_ME),-1,IMPORTE_ME,0)) CREDITO_ME,
                        SUM(DECODE(SIGN(IMPORTE_ME),1,IMPORTE_ME,0)) DEBITO_ME
                FROM CONTA_ASIENTO
                WHERE ID_TIPOORIGEN = " . $id_tipoorigen . "
                AND ID_ORIGEN = " . $id_origen . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function ShowEntidadEmpresa($id_entidad)
    {
        $sql = "SELECT
                    A.ID_EMPRESA
                FROM CONTA_ENTIDAD A
                WHERE A.ID_ENTIDAD = " . $id_entidad . " ";
        $query = DB::select($sql);
        return $query;
    }
    public static function listSeatAaasinetlamb($id_anho, $id_voucher, $id_empresa)
    {
        // dd($id_anho,$id_voucher,$id_empresa);
        $query = "SELECT
                            ID_ASIENTO,CUENTA,CUENTA_CTE,FONDO,DEPTO,RESTRICCION,
                            IMPORTE,
                            IMPORTE_ME,
                            DESCRIPCION,
                            MEMO,
                            CTA_EMPRESARIAL
                    FROM(
                            SELECT  A.ID_ASIENTO,
                                    A.ID_ORIGEN,
                                    A.CUENTA,A.CUENTA_CTE,A.FONDO,A.DEPTO,A.RESTRICCION,
                                    NVL(A.IMPORTE,0) AS IMPORTE,
                                    NVL(A.IMPORTE_ME,0) AS IMPORTE_ME,
                                    A.DESCRIPCION,
                                    A.MEMO,
                                    (SELECT X.ID_CUENTAEMPRESARIAL
                                       FROM CONTA_EMPRESA_CTA X
                                      WHERE     X.ID_CUENTAAASI = A.CUENTA
                                            AND X.ID_RESTRICCION = A.RESTRICCION
                                            AND X.ID_TIPOPLAN = 1
                                            AND X.ID_ANHO = " . $id_anho . "
                                            AND X.ID_EMPRESA = " . $id_empresa . " AND ROWNUM=1) AS CTA_EMPRESARIAL
                            FROM CONTA_ASIENTO A
                            WHERE A.VOUCHER = " . $id_voucher . "
                            -- AND A.AGRUPA<>'S'
                            AND NVL(A.AGRUPA,'N')<>'S'
                            UNION ALL
                            SELECT  (CASE WHEN NVL(SUM(A.IMPORTE),0) > 0 THEN MIN(A.ID_ASIENTO) ELSE MAX(A.ID_ASIENTO) END),
                                    MAX(A.ID_ORIGEN) AS ID_ORIGEN,
                                    A.CUENTA,A.CUENTA_CTE,
                                    A.FONDO,
                                    A.DEPTO,
                                    A.RESTRICCION,
                                    NVL(SUM(A.IMPORTE),0) AS IMPORTE,
                                    NVL(SUM(A.IMPORTE_ME),0) AS IMPORTE_ME,
                                    A.DESCRIPCION,
                                    MAX(A.MEMO) AS MEMO,
                                    (SELECT X.ID_CUENTAEMPRESARIAL
                                       FROM CONTA_EMPRESA_CTA X
                                      WHERE     X.ID_CUENTAAASI = A.CUENTA
                                            AND X.ID_RESTRICCION = A.RESTRICCION
                                            AND X.ID_TIPOPLAN = 1
                                            AND X.ID_ANHO = " . $id_anho . "
                                            AND X.ID_EMPRESA = " . $id_empresa . " AND ROWNUM=1) AS CTA_EMPRESARIAL
                            FROM CONTA_ASIENTO A
                            WHERE VOUCHER = " . $id_voucher . "
                            --AND AGRUPA='S'
                            AND NVL(AGRUPA,'N')='S'
                            GROUP BY CUENTA, CUENTA_CTE, FONDO, DEPTO, RESTRICCION, DESCRIPCION
                    ) A WHERE (A.IMPORTE <> 0 and upper(A.DESCRIPCION) NOT LIKE upper('%<< Anulado>>%'))
                ORDER BY ID_ORIGEN,ID_ASIENTO, IMPORTE DESC ";
        // dd($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listDeptoParentSesion($entity)
    {
        $query = "SELECT
                        ID_DEPTO,NOMBRE
                FROM CONTA_ENTIDAD_DEPTO
                WHERE ID_ENTIDAD = $entity
                AND ES_GRUPO = 1
                AND LENGTH(ID_DEPTO) = 1
                ORDER BY ID_DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listVoucherModulesAll($id_entidad, $id_depto, $id_anho, $id_mes, $id_tipovoucher)
    {
        $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                        --TO_CHAR(A.NUMERO) AS NUMERO,
                        TO_CHAR(A.NUMERO)||' - '||A.ID_TIPOASIENTO AS NUMERO,
                        A.LOTE,A.ACTIVO
                FROM CONTA_VOUCHER A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                ORDER BY ID_VOUCHER DESC ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listVoucherModulesAllShow($id_entidad, $id_depto, $id_anho, $id_mes, $id_tipovoucher, $id_voucher)
    {
        $query = "SELECT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD-MM-YYYY') AS FECHA,TO_CHAR(A.NUMERO) AS NUMERO,A.LOTE,A.ACTIVO
                FROM CONTA_VOUCHER A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_VOUCHER = $id_voucher
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                ORDER BY ID_VOUCHER DESC ";
        // dd($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listVoucherMoveInventories($id_entidad, $id_depto, $id_anho, $id_mes, $id_tipovoucher, $almacen, $tipo)
    {
        $query = "SELECT DISTINCT
                        A.ID_VOUCHER,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,TO_CHAR(A.NUMERO) AS NUMERO,A.LOTE,A.ACTIVO
                FROM INVENTARIO_MOVIMIENTO IV
                INNER JOIN CONTA_VOUCHER A ON A.id_voucher = iv.id_voucher
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND IV.ID_ANHO = $id_anho
                AND IV.ID_MES = $id_mes
                AND A.ID_TIPOVOUCHER = $id_tipovoucher
                AND IV.ID_ALMACEN = $almacen
                AND IV.TIPO = '" . $tipo . "'
                ORDER BY A.ID_VOUCHER DESC ";
        $oQuery = DB::select($query);
        return $oQuery;
    }


    static function typeChangeToday()
    {

        $response = null;

        $data = DB::select("SELECT ID_MONEDA,
                           FECHA,
                           COALESCE(COS_COMPRA, 0) AS      COS_COMPRA,
                           COALESCE(COS_VENTA, 0)  AS      COS_VENTA,
                           COALESCE(COS_DENOMINACIONAL, 0) COS_DENOMINACIONAL
                    FROM ELISEO.TIPO_CAMBIO
                    WHERE ID_MONEDA = 9
            AND TO_CHAR(FECHA, 'DD/MM/YYYY') = TO_CHAR(SYSDATE, 'DD/MM/YYYY')");

        if ($data) {
            $response = collect($data)->first();
        }

        return $response;
    }

    public static function showValidatSeats($id_voucher, $id_depto)
    {
        $query = "SELECT COUNT(1) AS CANT
                    FROM CONTA_ASIENTO
                    WHERE VOUCHER = '" . $id_voucher . "'
                    AND SUBSTR(DEPTO,1,1) <> '" . $id_depto . "' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $cant = $id->cant;
        }
        return $cant;
    }
    // para Actualizar los arreglos
    public static function updateArrangementDocumet($id_arreglo, $serie, $numero, $fecha)
    {
        $glosa = "";
        $id_tipoorigen = "";
        $sql1 = "";
        /*
        7 = DEPOSITOS
        3 = COMPRAS
        9 = VENTAS
        2 = VENTAS
        3 = VENTAS
        1 = VENTAS*/
        $query = "SELECT ID_ORIGEN,ID_TIPOORIGEN FROM ARREGLO WHERE ID_ARREGLO = " . $id_arreglo . " ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $id) {
            $id_origen = $id->id_origen;
            $id_tipoorigen = $id->id_tipoorigen;
        }
        if ($id_tipoorigen == 3) { //COMPRA
            $query = "SELECT 'SERIE: '||SERIE||', NUMERO: '||NUMERO||', IMPORTE: '||IMPORTE||', FECHA: '||FECHA_DOC||', PERSONA:'||ID_PERSONA AS L_GLOSA
                    FROM COMPRA WHERE ID_COMPRA = " . $id_origen . " ";
            $sql1 = DB::select($query);
            foreach ($sql1 as $id) {
                $glosa = $id->l_glosa;
            }
            $query = "UPDATE COMPRA SET SERIE = '" . $serie . "' ,NUMERO = '" . $numero . "', FECHA_DOC = '" . $fecha . "' WHERE ID_COMPRA = " . $id_origen . " ";
            DB::update($query);
        } elseif ($id_tipoorigen == 1) { //VENTA
            $query = "SELECT ID_CLIENTE,ID_COMPROBANTE,ID_VOUCHER, 'SERIE: '||SERIE||', NUMERO: '||NUMERO||'GRAVADA: '||GRAVADA||', INAFECTA: '||INAFECTA||', EXONERADA: '||EXONERADA||', DESCUENTO: '||DESCUENTO||', IGV: '||IGV||', TOTAL: '||TOTAL
                        AS L_GLOSA
                        FROM VENTA WHERE ID_VENTA = " . $id_origen . " ";
            $sql1 = DB::select($query);
            foreach ($sql1 as $id) {
                $glosa = $id->l_glosa;
            }
        } else {
            $query = "SELECT ID_CLIENTE,ID_VOUCHER, 'SERIE: '||SERIE||', NUMERO: '||NUMERO||', TOTAL: '||IMPORTE
                        AS L_GLOSA
                        FROM VENTA_TRANSFERENCIA WHERE ID_TRANSFERENCIA = " . $id_origen . " ";
            $sql1 = DB::select($query);
            foreach ($sql1 as $id) {
                $glosa = $id->l_glosa;
            }
        }
        $sql = "UPDATE ARREGLO SET INFO_BACKUP = '" . $glosa . "', ESTADO = '2'
                WHERE ID_ARREGLO = " . $id_arreglo . " ";
        DB::update($sql);
        return $sql1;
    }

    public static function dailyBookResumen($empresa, $entidad, $anho, $mes)
    {
        $query = "SELECT VW_CONTA_DIARIO.ID_ENTIDAD,VW_CONTA_DIARIO.ID_ANHO,VW_CONTA_DIARIO.ID_MES,SUBSTR(VW_CONTA_DIARIO.ID_DEPTO,1,1) AS ID_DEPTO,
                    TO_CHAR(SUM(VW_CONTA_DIARIO.DEBE),'999,999,999,999.99') AS DEBE,
                    TO_CHAR(SUM(VW_CONTA_DIARIO.HABER),'999,999,999,999.99') AS HABER
                    FROM VW_CONTA_DIARIO INNER JOIN CONTA_CTA_DENOMINACIONAL ON VW_CONTA_DIARIO.ID_TIPOPLAN = CONTA_CTA_DENOMINACIONAL.ID_TIPOPLAN
                    AND VW_CONTA_DIARIO.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI
                    AND VW_CONTA_DIARIO.ID_RESTRICCION = CONTA_CTA_DENOMINACIONAL.ID_RESTRICCION
                    LEFT JOIN TIPO_CTA_CORRIENTE ON CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE = TIPO_CTA_CORRIENTE.ID_TIPOCTACTE
                    LEFT JOIN CONTA_EMPRESA_CTA ON VW_CONTA_DIARIO.ID_EMPRESA = CONTA_EMPRESA_CTA.ID_EMPRESA
                    AND VW_CONTA_DIARIO.ID_TIPOPLAN = CONTA_EMPRESA_CTA.ID_TIPOPLAN
                    AND VW_CONTA_DIARIO.ID_CUENTAAASI = CONTA_EMPRESA_CTA.ID_CUENTAAASI
                    AND VW_CONTA_DIARIO.ID_RESTRICCION = CONTA_EMPRESA_CTA.ID_RESTRICCION
                    AND VW_CONTA_DIARIO.ID_ANHO = CONTA_EMPRESA_CTA.ID_ANHO
                    WHERE VW_CONTA_DIARIO.ID_EMPRESA = " . $empresa . "
                    AND VW_CONTA_DIARIO.ID_ENTIDAD = " . $entidad . "
                    AND VW_CONTA_DIARIO.ID_ANHO = " . $anho . "
                    AND VW_CONTA_DIARIO.ID_MES = " . $mes . "
                    GROUP BY VW_CONTA_DIARIO.ID_ENTIDAD,VW_CONTA_DIARIO.ID_ANHO,VW_CONTA_DIARIO.ID_MES,SUBSTR(VW_CONTA_DIARIO.ID_DEPTO,1,1)
                    ORDER BY ID_DEPTO ";
        return DB::select($query);;
    }

    public static function dailyBookLotes($params)
    {
        return DB::table("eliseo.CONTA_DIARIO as A")
            ->join('eliseo.CONTA_DIARIO_DETALLE as B', function ($join) {
                $join->on('A.ID_ENTIDAD',  'B.ID_ENTIDAD')
                    ->on('A.ID_DIARIO',  'B.ID_DIARIO');
            })
            ->select(
                // DB::raw("SUBSTR(B.ID_DEPTO,1,1) AS ID_DEPTO,A.ID_TIPOASIENTO,A.COD_AASI,A.FEC_CONTABILIZADO,COUNT(B.ID_DIARIO_DETALLE) ITEMS")
                "A.ID_TIPOASIENTO",
                "A.COD_AASI",
                "A.FEC_CONTABILIZADO",
                "B.ID_DEPTO",
                DB::raw("COUNT(B.ID_DIARIO_DETALLE) ITEMS")
            )
            ->where("A.ID_ENTIDAD", $params['id_entidad'])
            ->where("A.ID_ANHO", $params['id_anho'])
            ->where("A.ID_MES", $params['id_mes'])
            ->groupBy("A.ID_TIPOASIENTO", "A.COD_AASI", "A.FEC_CONTABILIZADO", "B.ID_DEPTO")
            ->orderBy('B.ID_DEPTO')
            ->orderBy('A.ID_TIPOASIENTO')
            ->orderBy('A.COD_AASI')
            ->orderBy('A.FEC_CONTABILIZADO')
            // ->orderBy("B.ID_DEPTO", "A.FEC_CONTABILIZADO", "A.ID_TIPOASIENTO", "A.COD_AASI")
            ->paginate($params['per_page']);

        // return DB::table("CONTA_DIARIO as A")
        //     ->join('CONTA_DIARIO_DETALLE as B', 'A.ID_ENTIDAD', '=', DB::raw("B.ID_ENTIDAD AND A.ID_DIARIO = B.ID_DIARIO"))
        //     ->select(
        //         DB::raw("SUBSTR(B.ID_DEPTO,1,1) AS ID_DEPTO,A.ID_TIPOASIENTO,A.COD_AASI,A.FEC_CONTABILIZADO,COUNT(B.ID_DIARIO_DETALLE) ITEMS")
        //     )
        //     ->where("A.ID_ENTIDAD", $params['id_entidad'])
        //     ->where("A.ID_ANHO", $params['id_anho'])
        //     ->where("A.ID_MES", $params['id_mes'])
        //     ->groupBy(DB::raw("A.ID_TIPOASIENTO,A.COD_AASI,A.FEC_CONTABILIZADO,SUBSTR(B.ID_DEPTO,1,1)"))
        //     ->orderBy(DB::raw("SUBSTR(B.ID_DEPTO,1,1),A.FEC_CONTABILIZADO,A.ID_TIPOASIENTO,A.COD_AASI"))
        //     ->paginate($params['per_page']);
    }

    public static function dailyBookExport($empresa, $entidad, $anho, $mes)
    {
        $query = "SELECT
                    CAST(VW_CONTA_DIARIO.ID_ANHO AS VARCHAR(4)) || SUBSTR('00' || CAST(VW_CONTA_DIARIO.ID_MES AS VARCHAR(2)), 2) || '00' || '|' ||                         -- PERIODO
                    CAST(VW_CONTA_DIARIO.ID_ENTIDAD AS VARCHAR(6)) || '-' || VW_CONTA_DIARIO.ID_TIPOASIENTO || ' ' || CAST(VW_CONTA_DIARIO.COD_AASI AS VARCHAR(10)) || '|' ||       -- CUO
                    CASE VW_CONTA_DIARIO.ID_TIPOASIENTO WHEN 'BB' THEN 'A' WHEN 'EB' THEN 'C' ELSE 'M' END || VW_CONTA_DIARIO.NUM_AASI || '|' ||                             -- CORRELATIVO
                    '01' || '|' ||
                    CONTA_Empresa_Cta.ID_CUENTAEMPRESARIAL|| '|' ||  --VW_CONTA_DIARIO.ID_CUENTAAASI|| '|' ||                                     -- CUENTA
                    CAST(VW_CONTA_DIARIO.ID_DEPTO AS VARCHAR(8))
                    || '|' ||                                                                                                                                     -- 5 Unidad de operaci�n
                    --'|', ||                                                                                                                                     -- 6 Centro de Costos
                    'PEN|' ||                                                                                                                                  -- 7 Moneda
                    '|' ||                                                                                                                                     -- 8 Tipo documento emisor
                    '|' ||                                                                                                                                     -- 9 Documento del emisor
                    '00|' ||                                                                                                                                   -- 10 Tipo de comprobante *
                    '|' ||                                                                                                                                     -- 11 N� Serie
                    '00|' ||                                                                                                                         -- 12 N� comprobante
                    '|' ||                                                                                                                                     -- 13 Fecha contable
                    '|' ||                                                                                                                                     -- 14 Fecha de Vencimiento
                    TO_CHAR(VW_CONTA_DIARIO.FEC_ASIENTO, 'dd/mm/yyyy') || '|' ||                                                                                  -- 15 Fecha de Operaci�n
                    SUBSTR(VW_CONTA_DIARIO.COMENTARIO,1, 100) || '|' ||                                                                                                   -- 16 Glosa
                    SUBSTR(VW_CONTA_DIARIO.COMENTARIO,1, 100) || '|' ||                                                                                                   -- 17 Glosa Referencial
                    --vw_conta_diario.DEBE || '|' ||                                                                                              -- 18 Debe
                    --vw_conta_diario.HABER || '|' ||
                    (CASE  WHEN VW_CONTA_DIARIO.DEBE = 0 THEN '0.00' WHEN VW_CONTA_DIARIO.DEBE BETWEEN 0.01 AND 0.99 THEN REPLACE(TRIM(TO_CHAR(VW_CONTA_DIARIO.DEBE,'90D99')),',','.')
                    ELSE REPLACE(LTRIM(TO_CHAR(VW_CONTA_DIARIO.DEBE,'9999999999D99')),',','.') END)|| '|' ||
                    (CASE  WHEN VW_CONTA_DIARIO.HABER = 0 THEN '0.00' WHEN VW_CONTA_DIARIO.HABER BETWEEN 0.01 AND 0.99 THEN REPLACE(TRIM(TO_CHAR(VW_CONTA_DIARIO.HABER,'90D99')),',','.')
                    ELSE REPLACE(LTRIM(TO_CHAR(VW_CONTA_DIARIO.HABER,'9999999999D99')),',','.') END)|| '|' ||
                    -- 19 Haber
                    '|' ||                                                                                                                                     -- 20 Dato estructurado
                    CASE WHEN VW_CONTA_DIARIO.ID_TIPOASIENTO = 'BB' AND VW_CONTA_DIARIO.COD_AASI > 1 THEN '9' ELSE '1' END || '|' ||                                        -- 21 Estado
                    VW_CONTA_DIARIO.LOTE || '-' || CAST(VW_CONTA_DIARIO.NUM_AASI AS VARCHAR(10)) || '|' DATOS                                                               -- 22 LIBRE
                    FROM VW_CONTA_DIARIO INNER JOIN CONTA_CTA_DENOMINACIONAL ON VW_CONTA_DIARIO.ID_TIPOPLAN = CONTA_CTA_DENOMINACIONAL.ID_TIPOPLAN
                    AND VW_CONTA_DIARIO.ID_CUENTAAASI = CONTA_CTA_DENOMINACIONAL.ID_CUENTAAASI
                    AND VW_CONTA_DIARIO.ID_RESTRICCION = CONTA_CTA_DENOMINACIONAL.ID_RESTRICCION
                    LEFT JOIN TIPO_CTA_CORRIENTE ON CONTA_CTA_DENOMINACIONAL.ID_TIPOCTACTE = TIPO_CTA_CORRIENTE.ID_TIPOCTACTE
                    LEFT JOIN CONTA_EMPRESA_CTA ON VW_CONTA_DIARIO.ID_EMPRESA = CONTA_EMPRESA_CTA.ID_EMPRESA
                    AND VW_CONTA_DIARIO.ID_TIPOPLAN = CONTA_EMPRESA_CTA.ID_TIPOPLAN
                    AND VW_CONTA_DIARIO.ID_CUENTAAASI = CONTA_EMPRESA_CTA.ID_CUENTAAASI
                    AND VW_CONTA_DIARIO.ID_RESTRICCION = CONTA_EMPRESA_CTA.ID_RESTRICCION
                    AND VW_CONTA_DIARIO.ID_ANHO = CONTA_EMPRESA_CTA.ID_ANHO
                    WHERE VW_CONTA_DIARIO.ID_EMPRESA = " . $empresa . "
                    AND " . $entidad . " IN (-1, VW_CONTA_DIARIO.ID_ENTIDAD)
                    AND VW_CONTA_DIARIO.ID_ANHO = " . $anho . "
                    AND VW_CONTA_DIARIO.ID_MES = " . $mes . "
                    ORDER BY VW_CONTA_DIARIO.FEC_ASIENTO, VW_CONTA_DIARIO.ID_TIPOASIENTO ,VW_CONTA_DIARIO.COD_AASI,VW_CONTA_DIARIO.NUM_AASI";
        return DB::select($query);
    }
    public static function show_voucher($id_voucher)
    {
        $query = "SELECT ID_ANHO FROM CONTA_VOUCHER
                WHERE ID_VOUCHER = $id_voucher ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
}
