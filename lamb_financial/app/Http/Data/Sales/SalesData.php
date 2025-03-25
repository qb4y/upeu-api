<?php

namespace App\Http\Data\Sales;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use App\Http\Data\Sales\ComunData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Exception;

class SalesData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function addDireccionPersona($request)
    { 
         $direccion = $request->direccion;
         $tipozona = $request->tipozona;
         $esLegal = $request->esLegal;
         $idTipoDIrecion = $request->idTipoDIrecion;
         $idTipoVia = $request->idTipoVia;
         $idTipoZona = $request->idTipoZona;
         $idUbigeo = $request->idUbigeo;
         $idPersona = $request->idPersona;
         $esActivo = $request->esActivo;

        DB::table('MOISES.persona_direccion')->insert(
            array(
                'DIRECCION' => $direccion ,
                'TIPOZONA' => $tipozona ,
                'ESLEGAL' => $esLegal ,
                'ID_TIPODIRECCION' => $idTipoDIrecion ,
                'ID_TIPOVIA' => $idTipoVia ,
                'ID_TIPOZONA' => $idTipoZona ,
                'ID_UBIGEO' => $idUbigeo ,
                'ID_PERSONA' => $idPersona ,
                'ES_ACTIVO' => $esActivo ,
            )
        );
    }
    public static function getDireccionPersona($idPersona)
    {
        $query = "SELECT DIRECCION FROM MOISES.persona_direccion WHERE id_persona = '".$idPersona."' "; 
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getTipoDireccion()
    {
        return DB::select("SELECT * FROM MOISES.tipo_direccion ");
    }
    public static function getTipoZona()
    {
        return DB::select("SELECT * FROM MOISES.tipo_via ");
    }
    public static function getTipoVia()
    {
        return DB::select("SELECT * FROM MOISES.tipo_zona ");
    }
    public static function listNaturalPerson($dato, $id_almacen)
    {
        $query = "SELECT
                        ID_PERSONA,
                        NOM_PERSONA,
                        NUM_DOCUMENTO,
                        ID_TIPODOCUMENTO,
                        0 CANT,
                        -- PKG_SALES.FC_CREDITO_PERSONAL(ID_PERSONA) CREDITO
                        PKG_SALES.FC_CREDITO_PERSONAL_POLITIC(ID_PERSONA, " . $id_almacen . ") CREDITO
                FROM MOISES.VW_PERSONA_NATURAL
                WHERE NUM_DOCUMENTO LIKE '%" . $dato . "%' OR UPPER(NOM_PERSONA) LIKE UPPER('%" . $dato . "%')
                AND ROWNUM <= 25
                ORDER BY NOM_PERSONA
                 ";
        // dd($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function showNaturalPerson($id_persona)
    {
        $query = "SELECT
                        ID_PERSONA,
                        NOM_PERSONA,
                        NUM_DOCUMENTO,
                        ID_TIPODOCUMENTO,
                        FC_GTH_OBTENER_EMAIL(ID_PERSONA) AS EMAIL,
                        PKG_SALES.FC_CREDITO_PERSONAL(ID_PERSONA) CREDITO
                FROM MOISES.VW_PERSONA_NATURAL
                WHERE ID_PERSONA=" . $id_persona;
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listLegalPerson($dato, $id_almacen)
    {
        $query = "SELECT
                        A.ID_PERSONA,
                        A.NOMBRE AS NOM_PERSONA,
                        A.ID_RUC NUM_DOCUMENTO,
                        A.SIGLAS,
                        A.ID_TIPOCONDICION,
                        PKG_SALES.FC_CREDITO_PERSONAL_POLITIC(A.ID_PERSONA, :id_almacen_p) CREDITO,
                        (SELECT COUNT(X.ID_RUC)FROM VW_CONTA_ENTIDAD X WHERE X.ID_RUC = A.ID_RUC) CANT,
                        (SELECT NVL(MAX(X.DIRECCION),' ') AS L_DIRECCION
                        FROM MOISES.PERSONA_DIRECCION X
                        WHERE X.ID_PERSONA = A.ID_PERSONA
                        AND X.ID_TIPODIRECCION = 5) AS DIRECCION
                FROM MOISES.VW_PERSONA_JURIDICA A
                WHERE A.ID_RUC LIKE '%" . $dato . "%'  OR UPPER(A.NOMBRE) LIKE UPPER('%" . $dato . "%') AND ROWNUM <= 20
                UNION ALL
                SELECT
                        A.ID_PERSONA,
                        FC_NOMBRE_PERSONA(A.ID_PERSONA) AS NOM_PERSONA,
                        B.NUM_DOCUMENTO AS NUM_DOCUMENTO,
                        C.CODIGO,
                        1 AS ID_TIPOCONDICION,
                        PKG_SALES.FC_CREDITO_PERSONAL_POLITIC(A.ID_PERSONA, :id_almacen_p) CREDITO,
                        0 CANT,
                        (SELECT NVL(MAX(X.DIRECCION),' ') AS L_DIRECCION
                        FROM MOISES.PERSONA_DIRECCION X
                        WHERE X.ID_PERSONA = A.ID_PERSONA
                        AND X.ID_TIPODIRECCION = 5) AS DIRECCION
                FROM MOISES.PERSONA A
                INNER JOIN MOISES.PERSONA_NATURAL B ON A.ID_PERSONA = B.ID_PERSONA
                LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO C ON B.ID_PERSONA = C.ID_PERSONA
                WHERE
                NVL(UPPER(B.NUM_DOCUMENTO),'')||
                NVL(UPPER(C.CODIGO),'')||
                NVL(UPPER(A.NOMBRE),'')||
                NVL(UPPER(A.PATERNO),'')||
                NVL(UPPER(A.MATERNO),'')||
                NVL(UPPER(CONCAT(A.PATERNO, A.MATERNO)),'')||
                NVL(UPPER(CONCAT(A.NOMBRE, A.PATERNO)),'')
                LIKE UPPER(REPLACE('%" . $dato . "%', CHR(32), ''))
                AND B.ID_TIPODOCUMENTO = 6
                AND ROWNUM <= 20
                ORDER BY NOM_PERSONA ";
        return DB::select($query, ['id_almacen_p' => $id_almacen]);
    }
    public static function listPerson($dato, $id_almacen)
    {
        $palabras = explode(" ", $dato);
        $contador = 0;

        $query = "SELECT
                        ID_PERSONA,
                        NOM_PERSONA,
                        'DNI:'||NUM_DOCUMENTO||' COD.:'||CODIGO  NUM_DOCUMENTO,
                        PKG_SALES.FC_CREDITO_PERSONAL_POLITIC(ID_PERSONA, " . $id_almacen . ") AS CREDITO,
                        '0' as TIPO
                FROM MOISES.VW_PERSONA_NATURAL_ALUMNO
                WHERE ";

        foreach ($palabras as $palabra) {
            $contador++;
            if ($contador > 1) {
                $query .= " AND ";
            }
            $query .= " NUM_DOCUMENTO||'-'||CODIGO||'-'||UPPER(NOM_PERSONA) LIKE UPPER('%" . $palabra . "%') ";
        }

        $query .= " UNION ALL
                SELECT
                        A.ID_PERSONA,
                        A.NOMBRE AS NOM_PERSONA,
                        'RUC:'||A.ID_RUC NUM_DOCUMENTO,
                        'N' AS CREDITO,
                        '1' as TIPO
                FROM MOISES.VW_PERSONA_JURIDICA A
                WHERE ";

        $contador = 0;
        foreach ($palabras as $palabra) {
            $contador++;
            if ($contador > 1) {
                $query .= " AND ";
            }
            $query .= " A.ID_RUC||'-'||UPPER(A.NOMBRE) LIKE UPPER('%" . $palabra . "%') ";
        }
        $query .= " UNION ALL
                SELECT
                        ID_PERSONA,
                        NOM_PERSONA,
                        'DNI:'||NUM_DOCUMENTO NUM_DOCUMENTO,
                        PKG_SALES.FC_CREDITO_PERSONAL_POLITIC(ID_PERSONA, " . $id_almacen . ") AS CREDITO,
                        '0' as TIPO
                FROM MOISES.VW_PERSONA_NATURAL
                WHERE ID_PERSONA NOT IN (SELECT ID_PERSONA FROM MOISES.VW_PERSONA_NATURAL_ALUMNO) ";

        foreach ($palabras as $palabra) {
            $query .= " AND NUM_DOCUMENTO||'-'||UPPER(NOM_PERSONA) LIKE UPPER('%" . $palabra . "%') ";
        }

        $contador .= " ORDER BY NOM_PERSONA ";
        //dd($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listLegalPersonSucursal($id_persona)
    {
        $query = "SELECT
                B.ID_PERSONA,A.ID_PERSONA ID_PARENT,C.ID_PERSONA||'-'||C.NOMBRE PERSONA,A.ID_EMPRESA,A.ID_RUC,A.NOM_DENOMINACIONAL, B.ID_ENTIDAD,B.NOMBRE,B.NOM_ENTIDAD,B.NOM_TIPOENTIDAD
                FROM VW_CONTA_EMPRESA A, VW_CONTA_ENTIDAD B, MOISES.PERSONA C
                WHERE A.ID_EMPRESA = B.ID_EMPRESA
                AND B.ID_PERSONA = C.ID_PERSONA
                AND A.ID_PERSONA = " . $id_persona . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function actualizarTipcoCambio($anho, $mes, $adia, $acompra, $aventa, $diasact, $moneda_main, $moneda, $multi)
    {
        if ($multi) {
            $j = 0;
            $anterior = 0;
            $compra = 0;
            $venta = 0;
            $fecha = "";
            $id_moneda = $moneda;
            foreach ($diasact as $dia) {

                $fecha = $anho . "/" . $mes . "/" . $dia;
                if (in_array($dia, $adia)) {
                    $compra = $acompra[$dia];
                    $venta = $aventa[$dia];
                } else {
                    if ($anterior == 0) {
                        $query = "select cos_compra,cos_venta from tipo_cambio
                            where  id_moneda='" . $id_moneda . "' and id_moneda_main='" . $moneda_main . "'
                            and fecha in(
                                select max(fecha) from tipo_cambio
                                where fecha < cast('" . $fecha . "' as date)
                                    and id_moneda='" . $id_moneda . "' and id_moneda_main='" . $moneda_main . "'
                                ) ";
                        $oQuery = DB::select($query);
                        foreach ($oQuery as $row) {
                            $compra = $row->cos_compra;
                            $venta  = $row->cos_venta;
                        }
                    }
                }
                if ($compra > 0 and $venta > 0) {
                    SalesData::actualizarTC($fecha, $compra, $venta, $moneda_main, $id_moneda);
                }


                $anterior = $dia;
                $j++;
            }
        } else {
            $fecha = $anho . "/" . $mes . "/" . $adia;
            SalesData::actualizarTC($fecha, $acompra, $aventa, $moneda_main, $moneda);
        }
    }
    // public static function actualizarTipcoCambio($fecha, $compra, $venta, $moneda_main, $id_moneda) {
    //     SalesData::actualizarTC($fecha, $compra, $venta, $moneda_main, $id_moneda);
    // }
    public static function actualizarTC($fecha, $compra, $venta, $moneda_main, $id_moneda)
    {
        $query = "select cos_compra,cos_venta from tipo_cambio
                        where fecha = cast('" . $fecha . "' as date)
                         and id_moneda='" . $id_moneda . "' and id_moneda_main='" . $moneda_main . "'";
        $oQuery = DB::select($query);

        if (count($oQuery) == 0) {
            $data = DB::table('TIPO_CAMBIO')->insert(
                array(
                    'ID_MONEDA' => $id_moneda,
                    'FECHA' => $fecha,
                    'COS_VENTA' => $venta,
                    'COS_DENOMINACIONAL' => 0,
                    'COS_COMPRA' => $compra,
                    'ID_MONEDA_MAIN' => $moneda_main
                )
            );
        } else {

            $date = date_create($fecha);
            $fec = date_format($date, "Y/m/d");
            $actual = date("Y/m/d");

            //if($fec==$actual){

            $query = "UPDATE TIPO_CAMBIO SET  COS_VENTA = " . $venta . ",COS_COMPRA=" . $compra . "
                         WHERE ID_MONEDA = " . $id_moneda . " AND ID_MONEDA_MAIN = " . $moneda_main . "
                         AND  fecha = cast('" . $fecha . "' as date) ";
            DB::update($query);
            //}

        }
    }
    public static function listTypeSales()
    {
        $query = "SELECT
                ID_TIPOTRANSACCION AS ID_TIPOVENTA,NOMBRE
                FROM TIPO_TRANSACCION
                WHERE TIPO = 'V'
                AND ESTADO = '1'
                ORDER BY ID_TIPOTRANSACCION ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listSalesDetails($id_venta)
    {
        $query = "SELECT
                            A.ID_VDETALLE,A.ID_VENTA,A.ID_TIPOIGV,A.ID_ARTICULO,A.ID_ALMACEN,A.ID_DINAMICA,A.DETALLE,A.CANTIDAD,A.PRECIO,A.BASE,A.IGV,A.DESCUENTO,A.IMPORTE,A.IMPORTE_ME
                    FROM VENTA_DETALLE A, VENTA B
                    WHERE A.ID_VENTA = B.ID_VENTA
                    AND A.ID_VENTA = " . $id_venta . "
                    AND  B.ESTADO = 0
                    ORDER BY A.ID_VDETALLE DESC";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listSaleDetails($id_venta)
    {
        return DB::table('VENTA_DETALLE')->where('ID_VENTA', $id_venta)->get();
    }
    public static function showSale($id_venta)
    {
        return DB::table('VENTA A')
            ->select(
                'A.*',
                DB::raw("
                C.NOMBRE || ' ' || C.PATERNO || ' ' || C.MATERNO vendedor,
                B.NOMBRE || ' ' || B.PATERNO || ' ' || B.MATERNO cliente
                ")
            )
            ->where('A.ID_VENTA', $id_venta)
            ->leftjoin('MOISES.PERSONA B', 'B.ID_PERSONA', '=', 'A.ID_CLIENTE')
            ->leftjoin('MOISES.PERSONA C', 'C.ID_PERSONA', '=', 'A.ID_PERSONA')
            ->first();
    }
    public static function listSalesDetailsTotal($id_venta)
    {
        $query = "SELECT
                COALESCE(GRAVADA,0) AS GRAVADA,COALESCE(INAFECTA,0) AS INAFECTA,COALESCE(EXONERADA,0) AS EXONERADA,
                COALESCE(GRATUITA,0) AS GRATUITA,COALESCE(DESCUENTO,0) AS DESCUENTO,COALESCE(IGV,0) AS IGV,
                COALESCE(TOTAL,0) AS TOTAL,
                COALESCE(TOTAL_ME,0) AS TOTAL_ME
                FROM VENTA
                WHERE ID_VENTA = " . $id_venta . " AND ESTADO = 0 ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listSalesDetailsResume($id_venta)
    {
        $query = "SELECT sum(coalesce(IMPORTE, 0)) AS importe
                    FROM VENTA_DETALLE
                    WHERE ID_VENTA =  " . $id_venta . " ";
        $oQuery = collect(DB::select($query))->first();
        return $oQuery;
    }


    // public static function listSalesDetailsTotalVentaFinalizada($id_venta){
    //     $query = "SELECT
    //             COALESCE(GRAVADA,0) AS GRAVADA,COALESCE(INAFECTA,0) AS INAFECTA,COALESCE(EXONERADA,0) AS EXONERADA,
    //             COALESCE(GRATUITA,0) AS GRATUITA,COALESCE(DESCUENTO,0) AS DESCUENTO,COALESCE(IGV,0) AS IGV,
    //             COALESCE(TOTAL,0) AS TOTAL
    //             FROM VENTA
    //             WHERE ID_VENTA = ".$id_venta." AND ESTADO = 1 ";
    //     $oQuery = DB::select($query);
    //     return $oQuery;
    // }

    public static function listSalesDispatchs()
    {
        $query = "SELECT A.ID_VDESPACHO, A.ID_CLIENTE, A.ID_VOUCHER
        ,E.NUMERO AS VOUCHER_NUMERO
        ,E.ID_TIPOASIENTO AS VOUCHER_ID_TIPOASIENTO
        ,TO_CHAR(E.FECHA, 'DD/MM/YYYY') AS VOUCHER_FECHA

        , B.NOMBRE as MOTIVO_NOMBRE, TO_CHAR(A.FECHA_EMISION, 'DD/MM/YYYY') AS FECHA_EMISION, A.NUMERO,
        C.SERIE AS VENTA_SERIE, C.NUMERO AS VENTA_NUMERO, D.EMAIL AS USER_CREATED
        FROM ELISEO.VENTA_DESPACHO A
        INNER JOIN ELISEO.TIPO_MOTIVO_TRASLADO B ON A.ID_MOTIVOTRASLADO =B.ID_MOTIVOTRASLADO
        INNER JOIN ELISEO.VENTA C ON A.ID_VENTA = C.ID_VENTA
        INNER JOIN ELISEO.USERS D ON A.ID_PERSONA=D.ID
        LEFT JOIN ELISEO.CONTA_VOUCHER E ON A.ID_VOUCHER=E.ID_VOUCHER
        ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    /*public static function deleteSalesDetails($id_venta,$id_vdetalle){
        DB::table('VENTA_DETALLE')->where('ID_VENTA', '=', $id_venta)->where('ID_VDETALLE', '=', $id_vdetalle)->delete();
    }*/
    public static function showSales($id_venta)
    {
        $query = "SELECT
                ID_VENTA,ID_PARENT,ID_VOUCHER,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MES,ID_CLIENTE,ID_SUCURSAL,
                ID_COMPROBANTE,ID_TIPONOTA,ID_IGV,ID_MONEDA,TIPOCAMBIO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,
                AGRUPADO,ESTADO,
                FC_NOMBRE_CLIENTE(ID_CLIENTE) as NOMBRE_CLIENTE, GLOSA, ID_TIPOVENTA,
                FC_NOMBRE_CLIENTE(ID_CLIENTE_LEGAL) as RASONSOCIAL, ID_CLIENTE_LEGAL,
                (select ID_RUC from moises.PERSONA_JURIDICA where ID_PERSONA=ID_CLIENTE_LEGAL and ROWNUM=1) AS RUC, ID_SUCURSAL,
                (select count(*) from conta_documento cd 
                inner join conta_documento_ip cdi on cdi.id_documento = cd.id_documento
                inner join conta_documento_ip_user cdiu on cdiu.id_docip = cdi.id_docip
                where cd.serie = 'F150'
                and cdiu.id = venta.id_persona) serie_cm
                FROM VENTA
                WHERE ID_VENTA = $id_venta
                AND ESTADO = 0";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function salesBalancesAdvances($id_entidad, $id_depto, $id_anho, $id_cliente)
    {
        $query = "SELECT
                    ID_ENTIDAD,ID_DEPTO,ID_ANHO,0 AS ID_VENTA,ID_CLIENTE,0 AS ID_SUCURSAL,' ' AS SERIE, ' ' AS NUMERO,SUM(IMPORTE) AS TOTAL,0 AS TOTAL_ME
                    FROM VW_SALES_ADVANCES
                    WHERE ID_ENTIDAD = $id_entidad
                    AND ID_DEPTO = '$id_depto'
                    AND ID_ANHO = $id_anho
                    AND ID_CLIENTE = $id_cliente
                    HAVING SUM(IMPORTE) > 0
                    GROUP BY ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_CLIENTE";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function salesBalances($id_entidad, $id_depto, $id_anho, $id_cliente)
    {
        $query = "SELECT
                ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_VENTA,ID_CLIENTE,ID_SUCURSAL,SERIE,NUMERO,TOTAL,TOTAL_ME
                FROM VW_SALES_SALDO
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '" . $id_depto . "'
                AND ID_ANHO = $id_anho
                AND ID_CLIENTE = $id_cliente
                and (TOTAL+TOTAL_ME) <> 0 ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function salesBalancesMov($id_entidad, $id_depto, $id_anho, $id_cliente)
    {
        // dd($id_cliente);
        $query = "SELECT
                    ID_ENTIDAD,
                    ID_DEPTO,
                    ID_ANHO,
                    ID_VENTA,
                    ID_CLIENTE,
                    ID_SUCURSAL,
                    SERIE,
                    NUMERO,
                    ID_MONEDA,
                    ID_COMPROBANTE,
                    SUM (TOTAL) TOTAL,
                    SUM (TOTAL_ME) TOTAL_ME,
                  /*  (SELECT SUM(X.TOTAL) FROM VENTA X WHERE X.ID_CLIENTE =  A.ID_CLIENTE AND X.ID_VENTA = A.ID_VENTA) -
                    (SELECT SUM(X.TOTAL) FROM VENTA X WHERE X.ID_CLIENTE =  A.ID_CLIENTE AND X.ID_PARENT = A.ID_VENTA) AS SALDO_VENTA*/
                    nvl(((SELECT SUM(X.TOTAL) FROM VENTA X WHERE X.ID_CLIENTE =  A.ID_CLIENTE AND X.ID_VENTA = A.ID_VENTA)  -
                    (SELECT SUM(X.TOTAL) FROM VENTA X WHERE X.ID_CLIENTE =  A.ID_CLIENTE AND X.ID_PARENT = A.ID_VENTA)),SUM (TOTAL) ) AS SALDO_VENTA
                FROM VW_SALES_MOV A
                WHERE A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_CLIENTE = $id_cliente
                HAVING NVL(SUM(A.TOTAL),0)+NVL(SUM(A.TOTAL_ME),0) > 0
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_VENTA,A.ID_CLIENTE,A.ID_SUCURSAL,A.SERIE,A.NUMERO,A.ID_MONEDA, A.ID_COMPROBANTE";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function salesBalancesMovAlumns($id_entidad, $id_depto, $id_anho, $id_cliente, $id_tipoventa)
    {
        // dd($id_cliente);
        $query = "SELECT
                    ID_ENTIDAD,
                    ID_DEPTO,
                    ID_ANHO,
                    ID_VENTA,
                    ID_TIPOVENTA,
                    ID_CLIENTE,
                    ID_SUCURSAL,
                    SERIE,
                    NUMERO,
                    ID_MONEDA,
                    -- ID_TIPOTRANSACCION,/*le agregue y se duplico revisar*/
                    ID_COMPROBANTE,
                    SUM (TOTAL) TOTAL,
                    SUM (TOTAL_ME) TOTAL_ME
                FROM VW_SALES_MOV
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '" . $id_depto . "'
                AND ID_ANHO = $id_anho
                AND ID_CLIENTE = $id_cliente
                AND ID_TIPOVENTA = $id_tipoventa
                HAVING NVL(SUM(TOTAL),0)+NVL(SUM(TOTAL_ME),0) <> 0
                GROUP BY ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_VENTA,ID_TIPOVENTA,ID_CLIENTE,ID_SUCURSAL,SERIE,NUMERO,ID_MONEDA, ID_COMPROBANTE";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listTypeTransaccion($tipo, $id_modulo, $id_entidad, $id_anho, $id_depto)
    {
        if ($id_modulo != "") {
            $dato = "AND A.ID_MODULO = " . $id_modulo . " ";
        } else {
            $dato = "";
        }
        if ($tipo == "T" || $tipo == "") {
            $data = "";
        } else {
            $z = "','";
            $x = "'" . str_replace(',', $z, $tipo) . "'";
            //$data = "AND C.CODIGO LIKE '".$tipo."'";
            $data = "AND C.CODIGO IN (" . $x . ") ";
        }
        $query = "SELECT
                        A.ID_TIPOTRANSACCION AS ID_TIPOVENTA,
                        A.NOMBRE,
                        DECODE((SELECT COUNT(*) FROM CONTA_DINAMICA X
                        WHERE X.ID_ENTIDAD = B.ID_ENTIDAD
                        AND X.ID_TIPOTRANSACCION = A.ID_TIPOTRANSACCION
                        AND X.ID_ANHO = " . $id_anho . "
                        AND X.ID_DEPTO = '" . $id_depto . "'
                        AND X.ACTIVO = 'S'),0,'N','S') AS DISPONIBLE,
                        A.ID_MODULO
                FROM TIPO_TRANSACCION A
                    INNER JOIN CONTA_ENTIDAD_TRANSACCION B ON A.ID_TIPOTRANSACCION=B.ID_TIPOTRANSACCION
                    INNER JOIN TIPO_GRUPO_CONTA C ON A.ID_TIPOGRUPOCONTA=C.ID_TIPOGRUPOCONTA
                WHERE A.ESTADO = '1'
                AND B.ESTADO = '1'
                AND B.ID_ENTIDAD = " . $id_entidad . "
                " . $data . "
                " . $dato . "
                ORDER BY A.ID_TIPOTRANSACCION ";
        $oQuery = DB::select($query);
        return $oQuery;
    }


    public static function listTipesSales()
    {
        return DB::table('TIPO_VENTA')->get();
    }

    public static function listTipeMotivoTraslados()
    {
        $query = "SELECT ID_MOTIVOTRASLADO, NOMBRE FROM TIPO_MOTIVO_TRASLADO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listTypeTransaccionByEntidad($id_entidad)
    {
        $query = "SELECT
                    B.ID_TIPOTRANSACCION,
                    B.ID_PARENT,
                    B.ID_MODULO,
                    B.NOMBRE,
                    D.CODIGO AS TIPO,
                    D.NOMBRE AS NOMBRE_TIPO,
                    B.MODO,
                    B.ESTADO,
                    A.ID_ENTIDAD,
                    C.NOMBRE as NOMBRE_MODULO
            FROM CONTA_ENTIDAD_TRANSACCION A
            INNER JOIN TIPO_TRANSACCION B ON A.ID_TIPOTRANSACCION=B.ID_TIPOTRANSACCION
            INNER JOIN LAMB_MODULO C ON B.ID_MODULO=C.ID_MODULO
            INNER JOIN TIPO_GRUPO_CONTA D ON B.ID_TIPOGRUPOCONTA=D.ID_TIPOGRUPOCONTA
            WHERE A.ID_ENTIDAD = $id_entidad
            ORDER BY B.ID_MODULO, D.CODIGO
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getTypeTransaccionById($idtipotransaccion)
    {
        $query = "SELECT
                    B.ID_TIPOTRANSACCION,
                    B.ID_PARENT,
                    B.ID_MODULO,
                    B.NOMBRE,
                    B.ID_TIPOGRUPOCONTA,
                    B.MODO,
                    B.ESTADO
            FROM TIPO_TRANSACCION B
            WHERE B.ID_TIPOTRANSACCION = $idtipotransaccion
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listAllTypeTransaccion()
    {
        $query = "SELECT
                        B.ID_TIPOTRANSACCION,
                        B.ID_PARENT,
                        B.ID_MODULO,
                        B.NOMBRE,
                        D.CODIGO AS TIPO,
                        D.NOMBRE AS NOMBRE_TIPO,
                        B.MODO,
                        B.ESTADO,
                        C.NOMBRE as NOMBRE_MODULO
                FROM TIPO_TRANSACCION B
                INNER JOIN LAMB_MODULO C ON B.ID_MODULO=C.ID_MODULO
                INNER JOIN TIPO_GRUPO_CONTA D ON B.ID_TIPOGRUPOCONTA=D.ID_TIPOGRUPOCONTA
                ORDER BY B.ID_MODULO, D.CODIGO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function salesRecord($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher)
    {
        if ($id_depto == 0) {
            $depto = "";
        } else {
            $depto = "AND A.ID_DEPTO = '" . $id_depto . "' ";
        }
        if ($id_voucher == 0) {
            $voucher = "";
        } else {
            $voucher = "AND A.ID_VOUCHER = " . $id_voucher . " ";
        }
        // consultar si el max no afecta en nada
        $query = "SELECT
                    A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_COMPROBANTE, A.OTROS_CARGOS AS ICBPER,
                    DECODE(A.ID_COMPROBANTE,'01',6,(SELECT MAX(X.ID_TIPODOCUMENTO) FROM MOISES.PERSONA_DOCUMENTO X WHERE X.ID_PERSONA = B.ID_PERSONA AND X.ID_TIPODOCUMENTO in (1,6))) AS ID_TIPODOCUMENTO,
                    DECODE(A.ID_COMPROBANTE,'01',(SELECT MAX(X.ID_RUC) FROM MOISES.PERSONA_JURIDICA X WHERE X.ID_PERSONA = B.ID_PERSONA),(SELECT MAX(X.NUM_DOCUMENTO) FROM MOISES.PERSONA_DOCUMENTO X WHERE X.ID_PERSONA = B.ID_PERSONA AND X.ID_TIPODOCUMENTO in (1,6))) AS DOCUMENTO,
                    DECODE(A.ID_COMPROBANTE,'01',B.NOMBRE,B.NOMBRE||' '||NVL(B.PATERNO,'')||' '||NVL(B.MATERNO,'')) CLIENTE,
                    C.LOTE AS LOTE,
                    A.ID_VOUCHER,
                    TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.SERIE,A.NUMERO,
                    A.GRAVADA*DECODE(A.ID_COMPROBANTE,'07',-1,1) AS GRAVADA,
                    A.INAFECTA*DECODE(A.ID_COMPROBANTE,'07',-1,1) AS INAFECTA,
                    A.EXONERADA*DECODE(A.ID_COMPROBANTE,'07',-1,1) AS EXONERADA,
                    A.GRATUITA*DECODE(A.ID_COMPROBANTE,'07',-1,1) AS GRATUITA,
                    A.DESCUENTO*DECODE(A.ID_COMPROBANTE,'07',-1,1) AS DESCUENTO,
                    A.IGV*DECODE(A.ID_COMPROBANTE,'07',-1,1) AS IGV,
                    A.TOTAL*DECODE(A.ID_COMPROBANTE,'07',-1,1) AS TOTAL,
                    NVL((SELECT TO_CHAR(X.FECHA,'DD/MM/YYYY') FROM VENTA X WHERE X.ID_VENTA = A.ID_PARENT),TO_CHAR(A.FECHA_REF,'DD/MM/YYYY')) AS FECHA_V,
                    NVL(FC_COMPROBANTE_VENTA(A.ID_PARENT),A.ID_COMPROBANTE_REF)  AS TIPO_V,
                    NVL(FC_SERIE_VENTA(A.ID_PARENT),A.SERIE_REF)  AS SERIE_V,
                    NVL(FC_NUMERO_VENTA(A.ID_PARENT),A.NUMERO_REF)  AS NUMERO_V
                    FROM VENTA A, MOISES.PERSONA B, CONTA_VOUCHER C
                    WHERE A.ID_CLIENTE = B.ID_PERSONA
                    AND A.ID_VOUCHER = C.ID_VOUCHER
                    AND A.ID_ENTIDAD = $id_entidad
                    " . $depto . "
                    AND A.ID_ANHO = $id_anho
                    AND A.ID_MES = $id_mes
                    " . $voucher . "
                    AND A.ESTADO = 1
                    ORDER BY A.ID_COMPROBANTE,A.SERIE,A.NUMERO,FECHA ";
        // dd($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function salesAcountingEntry($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_empresa)
    {
        $query = "SELECT A.ID_VENTA,A.ID_MONEDA,
                        C.ID_ASIENTO,C.CUENTA,C.CUENTA_CTE,C.FONDO,C.DEPTO,C.RESTRICCION,C.IMPORTE,0 IMPORTE_ME,C.DESCRIPCION,
                        (SELECT X.ID_CUENTAEMPRESARIAL FROM CONTA_EMPRESA_CTA X WHERE X.ID_CUENTAAASI = C.CUENTA AND X.ID_RESTRICCION = C.RESTRICCION
                        AND X.ID_TIPOPLAN = 1 AND X.ID_ANHO = " . $id_anho . " AND X.ID_EMPRESA = " . $id_empresa . ") AS CTA_EMPRESARIAL
                FROM VENTA A, VENTA_DETALLE B, CONTA_ASIENTO C
                WHERE A.ID_VENTA = B.ID_VENTA
                AND B.ID_VDETALLE = C.ID_ORIGEN
                AND A.ID_ENTIDAD = $id_entidad
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = $id_anho
                AND A.ID_MES = $id_mes
                AND A.ID_VOUCHER = $id_voucher
                AND A.ESTADO = 1
                AND C.ID_TIPOORIGEN = 1
                ORDER BY A.ID_VENTA,C.ID_ASIENTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function salesDetails($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher)
    {
        if ($id_depto == 0) {
            $depto = "";
        } else {
            $depto = "AND A.ID_DEPTO = '" . $id_depto . "' ";
        }
        if ($id_voucher == 0) {
            $voucher = "";
        } else {
            $voucher = "AND A.ID_VOUCHER = " . $id_voucher . " ";
        }
        $query = "SELECT  A.ID_VENTA,C.ID_VDETALLE,
                            A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_COMPROBANTE,
                            DECODE(A.ID_COMPROBANTE,'01',6,(SELECT X.ID_TIPODOCUMENTO FROM MOISES.PERSONA_DOCUMENTO X WHERE X.ID_PERSONA = B.ID_PERSONA)) AS ID_TIPODOCUMENTO,
                            DECODE(A.ID_COMPROBANTE,'01',(SELECT X.ID_RUC FROM MOISES.PERSONA_JURIDICA X WHERE X.ID_PERSONA = B.ID_PERSONA),(SELECT X.NUM_DOCUMENTO FROM MOISES.PERSONA_DOCUMENTO X WHERE X.ID_PERSONA = B.ID_PERSONA)) AS DOCUMENTO,
                            DECODE(A.ID_COMPROBANTE,'01',B.NOMBRE,B.NOMBRE||' '||NVL(B.PATERNO,'')||' '||NVL(B.MATERNO,'')) CLIENTE,
                            A.ID_VOUCHER,
                            TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.SERIE,A.NUMERO,A.GRAVADA,A.INAFECTA,A.EXONERADA,A.GRATUITA,A.DESCUENTO,A.IGV,A.TOTAL,
                            C.DETALLE,C.IMPORTE AS IMP_DETALLE,
                            D.CUENTA,D.DEPTO,D.DESCRIPCION,D.IMPORTE AS IMP_ASIENTO
                    FROM VENTA A, MOISES.PERSONA B, VENTA_DETALLE C, CONTA_ASIENTO D
                    WHERE A.ID_CLIENTE = B.ID_PERSONA
                    AND A.ID_VENTA = C.ID_VENTA
                    AND A.ID_VENTA = D.ID_ORIGEN
                    AND A.ID_ENTIDAD = $id_entidad
                    " . $depto . "
                    AND A.ID_ANHO = $id_anho
                    AND A.ID_MES = $id_mes
                    " . $voucher . "
                    AND A.ESTADO = 1
                    AND D.ID_TIPOORIGEN = 1
                    ORDER BY A.ID_COMPROBANTE,A.ID_VENTA,C.ID_VDETALLE,D.ID_ASIENTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function salesDetailsCab($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher)
    {
        if ($id_depto == 0) {
            $depto = "";
        } else {
            $depto = "AND A.ID_DEPTO = '" . $id_depto . "' ";
        }
        if ($id_voucher == 0) {
            $voucher = "";
        } else {
            $voucher = "AND A.ID_VOUCHER = " . $id_voucher . " ";
        }
        $query = "SELECT  A.ID_VENTA,
                            A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_MES,A.ID_COMPROBANTE,
                            DECODE(A.ID_COMPROBANTE,'01',6,(SELECT X.ID_TIPODOCUMENTO FROM MOISES.PERSONA_DOCUMENTO X WHERE X.ID_PERSONA = B.ID_PERSONA and x.id_tipodocumento not in (96, 97,99, 98))) AS ID_TIPODOCUMENTO,
                            DECODE(A.ID_COMPROBANTE,'01',(SELECT X.ID_RUC FROM MOISES.PERSONA_JURIDICA X WHERE X.ID_PERSONA = B.ID_PERSONA),(SELECT X.NUM_DOCUMENTO FROM MOISES.PERSONA_DOCUMENTO X WHERE X.ID_PERSONA = B.ID_PERSONA and x.id_tipodocumento not in (96, 97,99, 98))) AS DOCUMENTO,
                            DECODE(A.ID_COMPROBANTE,'01',B.NOMBRE,B.NOMBRE||' '||NVL(B.PATERNO,'')||' '||NVL(B.MATERNO,'')) CLIENTE,
                            A.ID_VOUCHER,
                            TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.SERIE,A.NUMERO,A.GRAVADA,A.INAFECTA,A.EXONERADA,A.GRATUITA,A.DESCUENTO,A.IGV,A.TOTAL
                    FROM VENTA A, MOISES.PERSONA B
                    WHERE A.ID_CLIENTE = B.ID_PERSONA
                    AND A.ID_ENTIDAD = $id_entidad
                    " . $depto . "
                    AND A.ID_ANHO = $id_anho
                    AND A.ID_MES = $id_mes
                    " . $voucher . "
                    AND A.ESTADO = 1
                    ORDER BY A.ID_COMPROBANTE,A.ID_VENTA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function salesDetailsDet($id_venta)
    {
        $query = "SELECT  A.ID_VENTA,C.ID_VDETALLE,C.DETALLE,C.IMPORTE AS IMP_DETALLE
                    FROM VENTA A,VENTA_DETALLE C
                    WHERE A.ID_VENTA = C.ID_VENTA
                    AND A.ID_VENTA = " . $id_venta . "
                    AND A.ESTADO = 1
                    ORDER BY A.ID_COMPROBANTE,A.ID_VENTA,C.ID_VDETALLE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function salesDetailsAsiento($id_venta, $id_empresa, $id_anho)
    {
        /*$query = "SELECT  A.ID_VENTA,D.ID_ASIENTO,D.CUENTA,D.DEPTO,D.DESCRIPCION,D.IMPORTE AS IMP_ASIENTO
                    FROM VENTA A, CONTA_ASIENTO D
                    WHERE A.ID_VENTA = D.ID_ORIGEN
                    AND A.ID_VENTA = ".$id_venta."
                    AND A.ESTADO = 1
                    AND D.ID_TIPOORIGEN = 1
                    ORDER BY A.ID_COMPROBANTE,A.ID_VENTA,D.ID_ASIENTO ";
        $oQuery = DB::select($query);*/
        $query = "SELECT
                            CUENTA||' - '||CTA_EMPRESARIAL AS CUENTA,CUENTA_CTE,FONDO,DEPTO,RESTRICCION,
                            IMPORTE AS IMP_ASIENTO,
                            DESCRIPCION,
                            MEMO,
                            CTA_EMPRESARIAL
                    FROM(
                            SELECT
                                    A.ID_ORIGEN,
                                    A.CUENTA,A.CUENTA_CTE,A.FONDO,A.DEPTO,A.RESTRICCION,
                                    A.IMPORTE,
                                    A.DESCRIPCION,
                                    A.MEMO,
                                    (SELECT X.ID_CUENTAEMPRESARIAL
                                       FROM CONTA_EMPRESA_CTA X
                                      WHERE     X.ID_CUENTAAASI = A.CUENTA
                                            AND X.ID_RESTRICCION = A.RESTRICCION
                                            AND X.ID_TIPOPLAN = 1
                                            AND X.ID_ANHO = " . $id_anho . "
                                            AND X.ID_EMPRESA = " . $id_empresa . " AND ROWNUM=1) AS CTA_EMPRESARIAL
                            FROM CONTA_ASIENTO A JOIN VENTA_DETALLE B
                            ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                            AND A.ID_ORIGEN = B.ID_VDETALLE
                            WHERE B.ID_VENTA = " . $id_venta . "
                            AND A.AGRUPA <> 'S'
                            UNION ALL
                            SELECT
                                    MAX(A.ID_ORIGEN) AS ID_ORIGEN,
                                    A.CUENTA,A.CUENTA_CTE,
                                    A.FONDO,
                                    A.DEPTO,
                                    A.RESTRICCION,
                                    SUM(A.IMPORTE ) AS IMPORTE,
                                    A.DESCRIPCION,
                                    MAX(A.MEMO) AS MEMO,
                                    (SELECT X.ID_CUENTAEMPRESARIAL
                                       FROM CONTA_EMPRESA_CTA X
                                      WHERE     X.ID_CUENTAAASI = A.CUENTA
                                            AND X.ID_RESTRICCION = A.RESTRICCION
                                            AND X.ID_TIPOPLAN = 1
                                            AND X.ID_ANHO = " . $id_anho . "
                                            AND X.ID_EMPRESA = " . $id_empresa . " AND ROWNUM=1) AS CTA_EMPRESARIAL
                            FROM CONTA_ASIENTO A JOIN VENTA_DETALLE B
                            ON A.ID_TIPOORIGEN = B.ID_TIPOORIGEN
                            AND A.ID_ORIGEN = B.ID_VDETALLE
                            WHERE B.ID_VENTA = " . $id_venta . "
                            AND AGRUPA='S'
                            GROUP BY A.CUENTA, A.CUENTA_CTE, A.FONDO, A.DEPTO, A.RESTRICCION, A.DESCRIPCION
                    ) A
                    ORDER BY ID_ORIGEN, IMPORTE DESC ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listSalesProducts($id_almacen, $id_anho, $producto)
    {
        /*$query = "SELECT
                            A.ID_ARTICULO,A.NOMBRE,A.CODIGO,A.COSTO,A.BI,A.DESCUENTO,A.PRECIO,A.STOCK_ACTUAL
                   FROM (
                           SELECT
                                   B.ID_ARTICULO,A.NOMBRE,B.CODIGO,B.COSTO,C.COSTO AS BI,C.DESCUENTO,C.PRECIO,B.STOCK_ACTUAL
                           FROM INVENTARIO_ARTICULO A ,INVENTARIO_ALMACEN_ARTICULO B, VENTA_PRECIO C
                           WHERE A.ID_ARTICULO = B.ID_ARTICULO
                           AND B.ID_ALMACEN = C.ID_ALMACEN
                           AND B.ID_ARTICULO = C.ID_ARTICULO
                           AND B.ID_ANHO = C.ID_ANHO
                           AND B.ID_ALMACEN = ".$id_almacen."
                           AND B.ID_ANHO = ".$id_anho."
                           AND B.STOCK_ACTUAL > 0
                   ) A LEFT JOIN INVENTARIO_ARTICULO_CODIGO B
                   ON A.ID_ARTICULO = B.ID_ARTICULO
                   WHERE UPPER(A.NOMBRE) LIKE UPPER('%".$producto."%') OR UPPER(B.CODIGO) LIKE UPPER('%".$producto."%')
                   ORDER BY A.NOMBRE ";*/
        $query = "SELECT
                        DISTINCT B.ID_ARTICULO,A.NOMBRE,B.CODIGO,B.COSTO,C.COSTO AS BI,C.DESCUENTO,C.PRECIO,B.STOCK_ACTUAL
                 FROM INVENTARIO_ARTICULO A
                 JOIN INVENTARIO_ALMACEN_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO
                 AND B.ID_ALMACEN = " . $id_almacen . "
                 AND B.ID_ANHO = " . $id_anho . "
                 AND B.STOCK_ACTUAL > 0
                 JOIN VENTA_PRECIO C
                 ON B.ID_ALMACEN = C.ID_ALMACEN
                 AND B.ID_ARTICULO = C.ID_ARTICULO
                 AND B.ID_ANHO = C.ID_ANHO
                 LEFT JOIN INVENTARIO_ARTICULO_CODIGO X
                 ON B.ID_ARTICULO = X.ID_ARTICULO
                 WHERE (UPPER(A.NOMBRE) LIKE UPPER('%" . $producto . "%')
                 OR UPPER(X.CODIGO) LIKE UPPER('%" . $producto . "%'))
                 AND ROWNUM <= 50
                 ORDER BY NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listSalesProductsWithoutStock($id_almacen, $id_anho, $producto)
    {
        $query = "SELECT DISTINCT B.ID_ARTICULO,A.NOMBRE,B.CODIGO,B.COSTO,C.COSTO AS BI,C.DESCUENTO,C.PRECIO,B.STOCK_ACTUAL
                 FROM INVENTARIO_ARTICULO A
                 INNER JOIN INVENTARIO_ALMACEN_ARTICULO B ON A.ID_ARTICULO = B.ID_ARTICULO
                                                        AND B.ID_ALMACEN = $id_almacen
                                                        AND B.ID_ANHO = $id_anho
                                                        --AND B.STOCK_ACTUAL <= 0
                 INNER JOIN VENTA_PRECIO C ON B.ID_ALMACEN = C.ID_ALMACEN
                                        AND B.ID_ARTICULO = C.ID_ARTICULO
                                        AND B.ID_ANHO = C.ID_ANHO
                 LEFT JOIN INVENTARIO_ARTICULO_CODIGO X ON B.ID_ARTICULO = X.ID_ARTICULO
                 WHERE (UPPER(A.NOMBRE) LIKE UPPER('%" . $producto . "%')
                 OR UPPER(X.CODIGO) LIKE UPPER('%" . $producto . "%'))
                 AND ROWNUM <= 50
                 ORDER BY NOMBRE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listSalesProductsPolitics($id_politica, $id_anho, $producto)
    {
        $query = "SELECT
                            A.ID_ARTICULO,A.NOMBRE,A.CODIGO,A.COSTO,A.BI,A.DESCUENTO,A.PRECIO,A.STOCK_ACTUAL
                   FROM (
                           SELECT
                                   B.ID_ARTICULO,A.NOMBRE,B.CODIGO,B.COSTO,C.COSTO AS BI,C.DESCUENTO,C.PRECIO,B.STOCK_ACTUAL
                           FROM INVENTARIO_ARTICULO A ,INVENTARIO_ALMACEN_ARTICULO B, VENTA_POLITICA_ARTICULO C
                           WHERE A.ID_ARTICULO = B.ID_ARTICULO
                           AND B.ID_ALMACEN = C.ID_ALMACEN
                           AND B.ID_ARTICULO = C.ID_ARTICULO
                           AND B.ID_ANHO = C.ID_ANHO
                           AND C.ID_POLITICA = " . $id_politica . "
                           AND B.ID_ANHO = " . $id_anho . "
                           AND B.STOCK_ACTUAL > 0
                           ORDER BY PRECIO,NOMBRE
                   ) A LEFT JOIN INVENTARIO_ARTICULO_CODIGO B
                   ON A.ID_ARTICULO = B.ID_ARTICULO
                   WHERE UPPER(A.NOMBRE) LIKE UPPER('%" . $producto . "%') OR UPPER(B.CODIGO) LIKE UPPER('%" . $producto . "%') ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showTransfers($id_transferencia)
    {
        $query = "SELECT
                ID_ENTIDAD,ID_DINAMICA,ESTADO
                FROM VENTA_TRANSFERENCIA
                WHERE ID_TRANSFERENCIA = " . $id_transferencia . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTransfersDetails($id_transferencia)
    {
        $query = "SELECT
                    A.ID_TDETALLE,A.DC,A.IMPORTE,B.SERIE,B.NUMERO
                    FROM VENTA_TRANSFERENCIA_DETALLE A, VW_SALES_SALDO B
                    WHERE A.ID_VENTA = B.ID_VENTA
                    AND A.ID_TRANSFERENCIA = " . $id_transferencia . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTransfersDetailsTotal($id_transferencia)
    {
        $query = "SELECT
                    SUM(A.IMPORTE) TOTAL
                    FROM VENTA_TRANSFERENCIA_DETALLE A, VW_SALES_SALDO B
                    WHERE A.ID_VENTA = B.ID_VENTA
                    AND A.ID_TRANSFERENCIA = " . $id_transferencia . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTransfersEntry($id_transferencia)
    {
        $query = "SELECT
                ID_TASIENTO,ID_TRANSFERENCIA,ID_ASIENTO,ID_DEPTO,ID_CTACTE,PORCENTAJE
                FROM VENTA_TRANSFERENCIA_ASIENTO
                WHERE ID_TRANSFERENCIA = " . $id_transferencia . "
                ORDER BY ID_TASIENTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listTransfers($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher)
    {
        $query = "SELECT
                A.ID_TRANSFERENCIA,FC_NOMBRE_CLIENTE(A.ID_CLIENTE) ALUMNO,FC_CODIGO_ALUMNO(A.ID_CLIENTE) CODIGO,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.IMPORTE
                FROM VENTA_TRANSFERENCIA A, VENTA_TRANSFERENCIA_DETALLE B
                WHERE A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES = " . $id_mes . "
                AND A.ESTADO = " . $id_voucher . " ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMyTransfers($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_user)
    {
        $query = "SELECT
                A.ID_TRANSFERENCIA,FC_NOMBRE_CLIENTE(A.ID_CLIENTE) ALUMNO,FC_CODIGO_ALUMNO(A.ID_CLIENTE) CODIGO,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.IMPORTE,
                (SELECT X.ACTIVO FROM CONTA_VOUCHER X WHERE X.ID_VOUCHER = A.ID_VOUCHER) AS EDIT,
                NVL((SELECT DECODE(ESTADO,'1','S','N') FROM ARREGLO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO
                AND X.ID_ORIGEN = A.ID_TRANSFERENCIA AND X.ESTADO = '1' GROUP BY X.ESTADO),'N') SOLICITADO
                FROM VENTA_TRANSFERENCIA A, VENTA_TRANSFERENCIA_DETALLE B
                WHERE A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                AND A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES = " . $id_mes . "
                AND A.ID_VOUCHER = " . $id_voucher . "
                AND A.ID_PERSONA = " . $id_user . "
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_TRANSFERENCIA, A.ID_CLIENTE,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.IMPORTE
                ORDER BY A.ID_TRANSFERENCIA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteTransfersImports($id_entidad, $id_depto, $id_anho, $id_user)
    {
        $sql = "DELETE VENTA_TRANSFERENCIA_DETALLE WHERE ID_TRANSFERENCIA IN
                (SELECT ID_TRANSFERENCIA FROM VENTA_TRANSFERENCIA
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = " . $id_depto . "
                AND ID_PERSONA = " . $id_user . "
                AND ESTADO = '0') ";
        DB::delete($sql);
        $sql = "DELETE VENTA_TRANSFERENCIA_ASIENTO WHERE ID_TRANSFERENCIA IN (
                SELECT ID_TRANSFERENCIA FROM VENTA_TRANSFERENCIA
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = " . $id_depto . "
                AND ID_PERSONA = " . $id_user . "
                AND ESTADO = '0') ";
        DB::delete($sql);
        DB::table('VENTA_TRANSFERENCIA')
            ->where('ID_ENTIDAD', '=', $id_entidad)
            ->where('ID_DEPTO', '=', $id_depto)
            ->where('ID_ANHO', '=', $id_anho)
            ->where('ID_PERSONA', '=', $id_user)
            ->where('ESTADO', '=', '0')->delete();
    }
    public static function listTransfersImports($id_entidad, $id_depto, $id_anho, $id_mes, $id_user)
    {
        $query = "SELECT
                A.ID_TRANSFERENCIA,FC_NOMBRE_CLIENTE(A.ID_CLIENTE) ALUMNO,FC_CODIGO_ALUMNO(A.ID_CLIENTE) CODIGO,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.IMPORTE
                FROM VENTA_TRANSFERENCIA A
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES = " . $id_mes . "
                AND A.ID_PERSONA = " . $id_user . "
                AND A.ESTADO = '0' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMySales($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_user, $admin)
    {

        $rol = "AND A.ID_PERSONA = " . $id_user . "";
        if ($admin == 'true') {
            $rol = "";
        }
        $query = "SELECT
                A.ID_VENTA,FC_NOMBRE_CLIENTE(A.ID_CLIENTE) ALUMNO,FC_CODIGO_ALUMNO(A.ID_CLIENTE) CODIGO,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.TOTAL,
                (SELECT X.ACTIVO FROM CONTA_VOUCHER X WHERE X.ID_VOUCHER = A.ID_VOUCHER) AS EDIT,
                NVL((SELECT DECODE(X.ESTADO,'1','S','2','OK','N') FROM ARREGLO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO
                AND X.ID_ORIGEN = A.ID_VENTA AND X.ID_TIPOORIGEN = 1 GROUP BY X.ESTADO),'N') SOLICITADO
                FROM VENTA A
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES = " . $id_mes . "
                AND A.ID_VOUCHER = " . $id_voucher . "
                AND (A.ES_AUTOENTREGA IS NULL OR A.ES_AUTOENTREGA = 1)
                $rol
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_VENTA, A.ID_CLIENTE,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.TOTAL
                ORDER BY A.ID_VENTA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listTypesNotes($id_comprobante)
    {
        $query = "SELECT
                        ID_TIPONOTA,NOMBRE, VIGENCIA
                FROM TIPO_NOTA_DC
                WHERE ID_COMPROBANTE = '" . $id_comprobante . "'
                ORDER BY ID_TIPONOTA ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listMyNotes($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher, $id_user)
    {
        $query = "SELECT
              A.ID_COMPROBANTE,
              A.ID_VENTA,
              FC_NOMBRE_CLIENTE(A.ID_CLIENTE)                                 CLIENTE,
              FC_CODIGO_ALUMNO(A.ID_CLIENTE)                                  CODIGO,
              A.ID_VOUCHER,
              A.SERIE,
              A.NUMERO,
              A.FECHA,
              A.GLOSA,
              A.TOTAL,
              (SELECT X.ACTIVO
               FROM CONTA_VOUCHER X
               WHERE X.ID_VOUCHER = A.ID_VOUCHER)                          AS EDIT,
              NVL((SELECT DECODE(ESTADO, '1', 'S', 'N')
                   FROM ARREGLO X
                   WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = A.ID_DEPTO
                         AND X.ID_ORIGEN = A.ID_VENTA AND X.ESTADO = '1'
                   GROUP BY X.ESTADO), 'N')                                   SOLICITADO,
              NVL(FC_COMPROBANTE_VENTA(A.ID_PARENT), A.ID_COMPROBANTE_REF) AS ID_COMPROBANTE_REF,
              NVL(FC_SERIE_VENTA(A.ID_PARENT), A.SERIE_REF)                AS SERIE_REF,
              NVL(FC_NUMERO_VENTA(A.ID_PARENT), A.NUMERO_REF)              AS NUMERO_REF
            FROM VENTA A
            WHERE
                   A.ID_ENTIDAD = " . $id_entidad . "
                   AND A.ID_DEPTO = '" . $id_depto . "'
                   AND A.ID_ANHO = " . $id_anho . "
                   AND A.ID_MES = " . $id_mes . "
                   AND A.ID_COMPROBANTE IN ('07', '08')
                   AND A.ID_VOUCHER = " . $id_voucher . "
                   AND A.ID_PERSONA = " . $id_user . "
            GROUP BY A.ID_COMPROBANTE, A.ID_ENTIDAD, A.ID_DEPTO, A.ID_VENTA, A.ID_CLIENTE,
            A.ID_VOUCHER, A.SERIE, A.NUMERO, A.FECHA, A.GLOSA, A.TOTAL, A.ID_PARENT,
            A.ID_COMPROBANTE_REF, A.SERIE_REF, A.NUMERO_REF
            ORDER BY A.ID_VENTA";
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
    public static function addSalesParametersHead($id_venta, $id_user, $id_documento)
    {
        $query = "SELECT DECODE(ID_COMPROBANTE,'01','       FACTURA ELECTRONICA',' BOLETA DE VENTA ELECTRONICA') nombre,
                        'NUMERO   : '||SERIE||'-'||NUMERO AS NUMERO,
                        'FECHA    : '||TO_CHAR(FECHA,'DD/MM/YYYY')||' '||TO_CHAR(FECHA,'HH24:MI:SS') AS FECHA,
                        'CLIENTE  : '||FC_NOMBRE_PERSONA(ID_CLIENTE) AS CLIENTE,
                        DECODE(ID_COMPROBANTE,'01','RUC      : ','DNI      : ')||FC_DOCUMENTO_CLIENTE(ID_CLIENTE) AS DOCUMENTO,
                        'DIRECCION: '|| pkg_sales.FC_CLIENTE_DIRECCION(ID_CLIENTE)  AS DIRECCION,
                        '----------------------------------------' AS LINE
                FROM VENTA
                WHERE ID_VENTA = " . $id_venta . "
                AND ESTADO = 1 ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "NOMBRE" => $item->nombre,
                "NUMERO" => $item->numero,
                "FECHA" => $item->fecha,
                "CLIENTE" => $item->cliente,
                "DOCUMENTO" => $item->documento,
                "DIRECCION" => $item->direccion,
                "LINEA1" => $item->line,
                "LINEA2" => $item->line
            );
        }

        foreach ($params as $clave => $valor) {
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',0)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);
        }
    }
    public static function addSalesParametersBody($id_venta, $id_user, $id_documento)
    {
        $cont = 0;
        $query = "SELECT
                        B.ITEM,
                        PKG_INVENTORIES.FC_ARTICULO(B.ID_ARTICULO) AS DETALLE,
                        B.CANTIDAD,
                        LPAD(TRIM(TO_CHAR(B.IMPORTE,'999,999,999.99')),6,' ') AS IMPORTE
                FROM VENTA A JOIN VENTA_DETALLE B
                ON A.ID_VENTA = B.ID_VENTA
                AND A.ID_VENTA = " . $id_venta . "
                AND A.ESTADO = 1
                ORDER BY B.ITEM ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "CANT" => $item->cantidad,
                "GLOSA" => $item->detalle,
                "IMPORTE" => $item->importe
            );
            foreach ($params as $clave => $valor) {
                $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',$cont)
                        WHERE ID_PERSONA = $id_user ";
                DB::update($sql);
            }
            $cont++;
            $params = [];
        }
        return $cont;
    }
    public static function addSalesParametersFoot($id_venta, $id_user, $id_documento, $cont)
    {
        $query = "SELECT
                        lpad('OP.GRAVADA     S/. ',18,' ')||lpad(trim(to_char(A.GRAVADA,'999,999,990.99')),20,' ') AS GRAVADA,
                        lpad('OP.INAFECTA    S/. ',18,' ')||lpad(trim(to_char(A.INAFECTA,'999,999,990.99')),20,' ') AS INAFECTA,
                        lpad('OP.EXONERADA   S/. ',18,' ')||lpad(trim(to_char(A.EXONERADA,'999,999,990.99')),20,' ') AS EXONERADA,
                        lpad('OP.GRATUITAS   S/. ',18,' ')||lpad(trim(to_char(A.GRATUITA,'999,999,990.99')),20,' ') AS GRATUITA,
                        lpad('DESCUENTOS     S/. ',18,' ')||lpad(trim(to_char(A.DESCUENTO,'999,999,990.99')),20,' ') AS DESCUENTO,
                        lpad('I.G.V.         S/. ',18,' ')||lpad(trim(to_char(A.IGV,'999,999,990.99')),20,' ') AS IGV,
                        lpad('ICBPER         S/. ',18,' ')||lpad(trim(to_char(A.OTROS_CARGOS,'999,999,990.99')),20,' ') ICBPER,
                        lpad('PRECIO VENTA   S/. ',18,' ')||lpad(trim(to_char(A.TOTAL,'999,999,990.99')),20,' ') AS TOTAL,
                        'Son: '||FC_NUMERO_TEXTO(A.TOTAL)||' Soles' AS NUMTXT,
                        'Cajero : '||FC_NOMBRE_PERSONA(A.ID_PERSONA) AS CAJERO,
                        DECODE(A.ID_TIPOVENTA,1,'',DECODE(ID_CREDITO,1,'               TIPO PAGO',2,'             VENTA AL CREDITO')) AS CANCELADO,
                        NVL(VRESUMEN,' ') AS RESUMEN,
                        NVL((SELECT 'EFECTIVO:S/.'||trim(to_char(X.IMPORTE,'999,999.99'))||' - Dep:'||X.SERIE||'-'||X.NUMERO
                        FROM CAJA_DEPOSITO X JOIN CAJA_DEPOSITO_DETALLE Y ON X.ID_DEPOSITO = Y.ID_DEPOSITO AND X.ID_MEDIOPAGO = '008' WHERE Y.ID_VENTA = A.ID_VENTA ),' ') AS P_EFECTIVO,
                        NVL((SELECT 'TARJETA:S/.'||trim(to_char(X.IMPORTE,'999,999.99'))||' - Dep:'||X.SERIE||'-'||X.NUMERO
                        FROM CAJA_DEPOSITO X JOIN CAJA_DEPOSITO_DETALLE Y ON X.ID_DEPOSITO = Y.ID_DEPOSITO AND X.ID_MEDIOPAGO = '006' WHERE Y.ID_VENTA = A.ID_VENTA AND ROWNUM = 1),' ') AS P_TARJETA,
                        NVL((SELECT 'CREDITO:S/.'||trim(to_char(X.IMPORTE,'999,999.99'))||' - Dep:'||X.SERIE||'-'||X.NUMERO
                        FROM CAJA_DEPOSITO X JOIN CAJA_DEPOSITO_DETALLE Y ON X.ID_DEPOSITO = Y.ID_DEPOSITO AND X.ID_MEDIOPAGO = '999' WHERE Y.ID_VENTA = A.ID_VENTA ),' ') AS P_CREDITO,
                        '----------------------------------------' AS LINE
                FROM VENTA A
                WHERE A.ID_VENTA = " . $id_venta . "
                AND A.ESTADO = 1 ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "LINEA3" => $item->line,
                "OPG" => $item->gravada,
                "OPI" => $item->inafecta,
                "OPE" => $item->exonerada,
                "OPD" => $item->gratuita,
                "DSCTO" => $item->descuento,
                "IGV" => $item->igv,
                "ICBPER" => $item->icbper,
                "TOTAL" => $item->total,
                "LINEA4" => $item->line,
                "NUMTXT" => $item->numtxt,
                "CAJERO" => $item->cajero,
                "LINEA5" => $item->line,
                "CANCELADO" => $item->cancelado,
                "RESUMEN" => $item->resumen,
                "EFECTIVO" => $item->p_efectivo,
                "CREDITO" => $item->p_credito,
                "TARJETA" => $item->p_tarjeta
            );
        }
        foreach ($params as $clave => $valor) {
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',$cont)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);
        }
    }

    ///// print to transferencias
    // cambiar el 99 que esta estatico = id_comprobante
    public static function addTransfParametersHead($id_transferencia, $id_user, $id_documento)
    {
        $query = "SELECT DECODE(99,'01','       FACTURA ELECTRONICA',' TRANSFERENCIA ') nombre,
                        'NUMERO   : '||SERIE||'-'||NUMERO AS NUMERO,
                        'FECHA    : '||TO_CHAR(FECHA,'DD/MM/YYYY')||' '||TO_CHAR(FECHA,'HH24:MI:SS') AS FECHA,
                        'CLIENTE  : '||FC_NOMBRE_PERSONA(ID_CLIENTE) AS CLIENTE,
                        DECODE(99,'01','RUC      : ','DNI      : ')||FC_DOCUMENTO_CLIENTE(ID_CLIENTE) AS DOCUMENTO,
                        'DIRECCION: '|| pkg_sales.FC_CLIENTE_DIRECCION(ID_CLIENTE)  AS DIRECCION,
                        '----------------------------------------' AS LINE
                FROM  ELISEO.VENTA_TRANSFERENCIA
                WHERE ID_TRANSFERENCIA = " . $id_transferencia . "
                AND ESTADO = 1 ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "NOMBRE" => $item->nombre,
                "NUMERO" => $item->numero,
                "FECHA" => $item->fecha,
                "CLIENTE" => $item->cliente,
                "DOCUMENTO" => $item->documento,
                "DIRECCION" => $item->direccion,
                "LINEA1" => $item->line,
                "LINEA2" => $item->line
            );
        }

        foreach ($params as $clave => $valor) {
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',0)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);
        }
    }

    public static function addTransfParametersBody($id_transferencia, $id_user, $id_documento)
    {
        $cont = 0;
        $query = "SELECT
                SUBSTR(A.GLOSA||' '||NVL(DECODE(NVL(C.SERIE,'X'),'X',D.SERIE||'-'||D.NUMERO,C.SERIE||'-'||C.NUMERO),A.SERIE||'-'||A.NUMERO),1,30) AS GLOSA,
                A.SERIE||'-'||A.NUMERO AS GLOSA1,
                LPAD(TRIM(TO_CHAR(B.IMPORTE,'999,999,999.99')),8,' ') AS IMPORTE
            FROM VENTA_TRANSFERENCIA A JOIN VENTA_TRANSFERENCIA_DETALLE B
            ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
            LEFT JOIN VENTA C
            ON A.ID_CLIENTE = C.ID_CLIENTE
            AND B.ID_VENTA = C.ID_VENTA
            LEFT JOIN VENTA_SALDO D
            ON A.ID_CLIENTE = D.ID_CLIENTE
            AND B.ID_SALDO = D.ID_SALDO
            WHERE A.ID_TRANSFERENCIA = " . $id_transferencia . "
            AND A.ESTADO = 1
            ORDER BY B.ID_TDETALLE";

        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "GLOSA" => $item->glosa,
                "GLOSA1" => $item->glosa1,
                "IMPORTE" => $item->importe
            );
            foreach ($params as $clave => $valor) {
                $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',$cont)
                        WHERE ID_PERSONA = $id_user ";
                DB::update($sql);
            }
            $cont++;
            $params = [];
        }
        return $cont;
    }

    public static function addTransfParametersFoot($id_transferencia, $id_user, $id_documento, $cont)
    {
        $query = "SELECT
                lpad('TOTAL   S/. ',18,' ')||lpad(trim(to_char(A.IMPORTE,'999,999,990.99')),20,' ') AS TOTAL,
                'Son: '||FC_NUMERO_TEXTO(A.IMPORTE)||' Soles' AS NUMTXT,
                'Cajero : '||FC_NOMBRE_PERSONA(A.ID_PERSONA) AS CAJERO,
                '----------------------------------------' AS LINE
        FROM  ELISEO.VENTA_TRANSFERENCIA A
        WHERE A.ID_TRANSFERENCIA = " . $id_transferencia . "
        AND A.ESTADO = 1 ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "LINEA3" => $item->line,
                "TOTAL" => $item->total,
                "LINEA4" => $item->line,
                "NUMTXT" => $item->numtxt,
                "CAJERO" => $item->cajero,
                "LINEA5" => $item->line,
            );
        }
        foreach ($params as $clave => $valor) {
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',$cont)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);
        }
    }

    //// fin de print to transferencias

    public static function ShowSalesToCredit($id_venta)
    {
        $sql = "SELECT
                        COUNT(A.ID_DEPOSITO) AS CANTIDAD
            FROM CAJA_DEPOSITO A JOIN CAJA_DEPOSITO_DETALLE B
            ON A.ID_DEPOSITO = B.ID_DEPOSITO
            WHERE ID_VENTA = " . $id_venta . "
            AND A.ID_MEDIOPAGO = '999' ";
        $query = DB::select($sql);
        foreach ($query as $item) {
            $cantidad = $item->cantidad;
        }
        return $cantidad;
    }
    public static function listMySalesArrangements($id_entidad, $id_depto, $id_anho, $id_mes, $id_voucher)
    {
        if ($id_voucher == 0) {
            $voucher = "";
        } else {
            $voucher = "AND A.ID_VOUCHER = " . $id_voucher . " ";
        }
        $query = "SELECT
                        A.ID_VENTA,B.ID_TIPOORIGEN,FC_NOMBRE_CLIENTE(A.ID_CLIENTE) ALUMNO,FC_CODIGO_ALUMNO(A.ID_CLIENTE) CODIGO,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.TOTAL,
                        B.MOTIVO,A.FECHA,FC_NOMBRE_CLIENTE(B.ID_PERSONA) USUARIO, B.ID_ARREGLO, B.ID_TIPOARREGLO, B.ID_ORIGEN
                FROM VENTA A JOIN ARREGLO B
                ON A.ID_VENTA = B.ID_ORIGEN
                WHERE B.ID_ENTIDAD = " . $id_entidad . "
                AND B.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES = " . $id_mes . "
                " . $voucher . "
                AND B.ID_TIPOORIGEN = 1
                AND B.ESTADO = '1'
                UNION ALL
                SELECT
                        A.ID_TRANSFERENCIA,B.ID_TIPOORIGEN,FC_NOMBRE_CLIENTE(A.ID_CLIENTE) ALUMNO,FC_CODIGO_ALUMNO(A.ID_CLIENTE) CODIGO,A.ID_VOUCHER,A.SERIE,A.NUMERO,A.FECHA,A.GLOSA,A.IMPORTE AS TOTAL,
                        B.MOTIVO,A.FECHA,FC_NOMBRE_CLIENTE(B.ID_PERSONA) USUARIO, B.ID_ARREGLO, B.ID_TIPOARREGLO, B.ID_ORIGEN
                FROM VENTA_TRANSFERENCIA A JOIN ARREGLO B
                ON A.ID_TRANSFERENCIA = B.ID_ORIGEN
                WHERE B.ID_ENTIDAD = " . $id_entidad . "
                AND B.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES = " . $id_mes . "
                " . $voucher . "
                AND B.ID_TIPOORIGEN = 2
                AND B.ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function spCancelSales($id_venta)
    {
        $error      = 0;
        $msg_error  = '';
        $obReturn  = [];
        try {
            for ($x = 1; $x <= 200; $x++) {
                $msg_error .= "0";
            }
            $pdo    = DB::getPdo();
            $stmt   = $pdo->prepare("begin PKG_SALES.SP_ANULAR_VENTAS(:P_ID_VENTA,:P_ERROR,:P_MSN);end;");
            $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSN', $msg_error, PDO::PARAM_STR);
            $stmt->execute();
            $oreturn['error']     = $error;
            $oreturn['message']   = $msg_error;
            return $oreturn;
        } catch (Exception $e) {
            $oreturn['error']     = 1;
            $oreturn['message']   = $e->getMessage();
            return $oreturn;
        }
    }
    public static function UpdateSalesHash($id_venta)
    {
        $hash = "";
        $query = "SELECT NVL(HASH,'X') as L_HASH
                FROM UPEU_COMPROBANTE@DBL_ARON_APP
                WHERE ORIGENID = '" . $id_venta . "' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $row) {
            $hash = $row->l_hash;
        }
        $sql = "UPDATE VENTA_ELECTRONICA SET HASH = '" . $hash . "'
                WHERE ORIGENID = " . $id_venta . " ";
        DB::update($sql);

        $sql = "UPDATE VENTA SET VRESUMEN = '" . $hash . "'
                WHERE ID_VENTA = " . $id_venta . " ";
        DB::update($sql);
    }
    public static function UpdateSalesHashAces($id_venta)
    {
        $hash = "";
        $query = "SELECT NVL(HASH,'X') as L_HASH
                FROM VENTA_ELECTRONICA_ACES
                WHERE ORIGENID = '" . $id_venta . "' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $row) {
            $hash = $row->l_hash;
        }

        $sql = "UPDATE VENTA SET VRESUMEN = '" . $hash . "'
                WHERE ID_VENTA = " . $id_venta . " ";
        DB::update($sql);
    }
    public static function UpdateSalesHashDEV($id_venta)
    {
        $hash = "";
        $query = "SELECT NVL(HASH,'X') as L_HASH
                FROM UPEU_COMPROBANTE@DBL_ARON
                WHERE ORIGENID = '" . $id_venta . "' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $row) {
            $hash = $row->l_hash;
        }
        $sql = "UPDATE VENTA_ELECTRONICA SET HASH = '" . $hash . "'
                WHERE ORIGENID = " . $id_venta . " ";
        DB::update($sql);

        $sql = "UPDATE VENTA SET VRESUMEN = '" . $hash . "'
                WHERE ID_VENTA = " . $id_venta . " ";
        DB::update($sql);
    }

    public static function UpdateSalesHashUPN($id_venta)
    {
        $hash = "";

        $query = "SELECT NVL(HASH,'X') as L_HASH
                FROM VENTA_ELECTRONICA_NUBE
                WHERE ORIGENID = '" . $id_venta . "' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $row) {
            $hash = $row->l_hash;
        }
        $sql = "UPDATE VENTA SET VRESUMEN = '" . $hash . "'
                WHERE ID_VENTA = " . $id_venta . " ";
        DB::update($sql);
    }

    public static function creditPersonal($id_depto, $id_voucher)
    {
        $sql = DB::table('caja_deposito as a')
            ->join('caja_deposito_detalle as b', 'a.id_deposito', '=', 'b.id_deposito')
            ->join('venta as c', 'b.id_venta', '=', 'c.id_venta')
            ->leftjoin('aps_trabajador as d', 'a.id_cliente', '=', 'd.id_persona')
            ->where('a.id_voucher', $id_voucher)
            ->where('a.id_mediopago', '=', '999')
            ->whereraw("SUBSTR(D.ID_DEPTO,1,1) = " . $id_depto . "")
            ->select(
                'a.id_deposito',
                'a.id_voucher',
                DB::raw("TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA"),
                'a.serie',
                'a.numero',
                'a.glosa',
                DB::raw("FC_NOMBRE_PERSONA(A.ID_CLIENTE) AS CLIENTE"),
                DB::raw("FC_DOCUMENTO_CLIENTE(A.ID_CLIENTE) AS DNI"),
                DB::raw("TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE"),
                DB::raw("TO_CHAR(B.IMPORTE,'999,999,999.99') AS IMP_DET"),
                DB::raw("C.SERIE||'-'||C.NUMERO AS COMPROBANTE"),
                DB::raw("FC_NOMBRE_PERSONA(A.ID_PERSONA) AS CAJERO"),
                DB::raw("SUBSTR(D.ID_DEPTO,1,1) AS DEPTO")
            )
            ->orderBy('a.id_deposito')
            ->get();
        return $sql;
    }
    /// Familias mas vendidas
    public static function familiasVendidas($id_almacen, $fecha_de, $fecha_a)
    {
        // dd($id_almacen, $fecha_de, $fecha_a);
        $sqal = DB::table('venta as a')
            ->join('venta_detalle as b', 'a.id_venta', '=', 'b.id_venta')
            ->join('inventario_articulo as c', 'b.id_articulo', '=', 'c.id_articulo')
            ->where('b.id_almacen', $id_almacen)
            ->where('a.estado', '1')
            // ->whereBetween('a.fecha', [$fecha_de, $fecha_a])
            ->whereraw("to_char( A.FECHA, 'YYYY-MM-DD') BETWEEN '" . $fecha_de . "' AND '" . $fecha_a . "'")
            ->select(
                DB::raw("pkg_inventories.fc_articulo(c.id_parent) as familia"),
                DB::raw("sum(b.cantidad) as cantidad"),
                DB::raw("sum(b.importe) as importe")
            )
            ->groupBy('c.id_parent')
            ->orderBy('cantidad', 'desc', 'importe')
            ->get();
        // dd($sqal);
        return $sqal;
    }
    /// Productos mas vendidos
    public static function productosFamiliasVendidas($id_almacen, $fecha_de, $fecha_a)
    {

        $sqal = DB::table('venta as a')
            ->join('venta_detalle as b', 'a.id_venta', '=', 'b.id_venta')
            ->join('inventario_articulo as c', 'b.id_articulo', '=', 'c.id_articulo')
            ->where('b.id_almacen', $id_almacen)
            ->where('a.estado', '1')
            // ->whereBetween('A.FECHA', ($fecha_de,))
            ->whereraw("to_char( A.FECHA, 'YYYY-MM-DD') BETWEEN '" . $fecha_de . "' AND '" . $fecha_a . "'")
            ->select(
                DB::raw("pkg_inventories.fc_articulo(c.id_parent) as familia"),
                'c.nombre',
                DB::raw("sum(b.cantidad) as cantidad"),
                DB::raw("sum(b.importe) as importe")
            )
            ->groupBy('c.id_parent', 'c.nombre')
            ->orderBy('cantidad', 'desc', 'importe')
            ->get();
        return $sqal;
    }
    //////////////////////////////// Voucher Dinamico
    public static function voucheDinamico($id_entidad, $id_anho, $id_mes, $id_depto)
    {

        $sqal = DB::table('conta_voucher as a')
            ->join('caja_deposito as b', 'a.id_voucher', '=', 'b.id_voucher')
            ->join('aps_trabajador as c', 'b.id_cliente', '=', 'c.id_persona')
            ->where('a.id_entidad', $id_entidad)
            ->where('a.id_anho', $id_anho)
            ->where('a.id_mes', $id_mes)
            // ->whereBetween('A.FECHA', ($fecha_de,))
            ->whereraw("SUBSTR(C.ID_DEPTO,1,1) = '" . $id_depto . "'")
            ->select('a.id_voucher', DB::raw("to_char(a.fecha, 'YYYY-MM-DD') as fecha"), 'a.numero', 'a.activo')
            ->orderBy('a.numero')
            ->distinct()
            ->get();
        return $sqal;
    }
    public static function listMyDepartmentSales($id_entidad, $id_persona)
    {


        $sqal = DB::table('conta_entidad_depto as a')
            ->join('lamb_users_depto as b', 'a.id_entidad', '=', DB::raw("b.id_entidad and a.id_depto = b.id_depto"))
            ->where('b.id_entidad', $id_entidad)
            ->where('b.id', $id_persona)
            ->where('es_empresa', '=', '1')
            ->where('b.activo', '=', '1')
            ->select('a.id_depto', 'a.nombre', 'b.id as id_persona', 'b.estado', 'a.es_empresa')
            ->orderBy('a.id_depto')
            ->get();
        return $sqal;
        // $query = "SELECT
        //         A.ID_DEPTO, A.NOMBRE,B.ID AS ID_PERSONA,B.ESTADO, A.ES_EMPRESA
        //         FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B
        //         WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
        //         AND A.ID_DEPTO = B.ID_DEPTO
        //         AND B.ID_ENTIDAD = ".$id_entidad."
        //         AND B.ID = ".$id_persona."
        //         AND ES_EMPRESA = '1'
        //         AND B.ACTIVO = '1'
        //         ORDER BY A.ID_DEPTO ";
        // $oQuery = DB::select($query);
        // return $oQuery;
    }
    public static function mesConMasVentas($id_anho, $id_almacen)
    {
        $sqal = DB::table('conta_mes as a')
            ->leftjoin('venta as b', 'a.id_mes', '=', DB::raw("b.id_mes and b.estado = '1' and b.id_anho=" . $id_anho))
            ->leftjoin('venta_detalle as c', 'b.id_venta', '=', DB::raw("c.id_venta and c.id_almacen = " . $id_almacen))
            ->select('a.nombre', DB::raw("coalesce(sum(c.cantidad), 0) as cantidad"), DB::raw("coalesce(sum(c.importe), 0) as importe"))
            ->groupBy('a.nombre', 'a.id_mes')
            ->orderBy('a.id_mes', 'asc', 'cantidad', 'importe')
            ->get();
        return $sqal;
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public static function cajeroTop($id_entidad, $id_almacen, $fecha_de, $fecha_a)
    {
        // dd($id_entidad, $id_almacen, $fecha_de, $fecha_a);
        $sql = DB::table('venta as a')
            ->join('venta_detalle as b', 'a.id_venta', '=', 'b.id_venta')
            ->join('moises.persona as c', 'a.id_persona', '=', 'c.id_persona')
            ->where('a.id_entidad', $id_entidad)
            ->where('b.id_almacen', $id_almacen)
            ->where('a.estado', '=', '1')
            ->whereraw("to_char( A.FECHA, 'YYYY-MM-DD') BETWEEN '" . $fecha_de . "' AND '" . $fecha_a . "'")
            ->select('a.id_persona', 'c.nombre', 'c.paterno', 'c.materno', DB::raw("sum(b.importe) as importe"))
            ->groupBy('a.id_persona', 'c.nombre', 'c.paterno', 'c.materno')
            ->orderBy('importe', 'desc')
            ->get();
        return $sql;
    }
    public static function clienteTop($id_entidad, $id_almacen, $fecha_de, $fecha_a)
    {
        //   dd($id_entidad, $id_almacen, $fecha_de, $fecha_a);
        $sql = DB::table('venta as a')
            ->join('venta_detalle as b', 'a.id_venta', '=', 'b.id_venta')
            ->join('moises.persona as c', 'a.id_cliente', '=', 'c.id_persona')
            ->where('a.id_entidad', $id_entidad)
            ->where('b.id_almacen', $id_almacen)
            ->where('a.estado', '=', '1')
            ->whereraw("to_char(A.FECHA, 'YYYY-MM-DD') BETWEEN '" . $fecha_de . "' AND '" . $fecha_a . "'")
            ->select('a.id_cliente', 'c.nombre', DB::raw("sum(b.importe) as importe"))
            ->groupBy('a.id_cliente', 'c.nombre', 'c.paterno')
            ->orderBy('importe', 'desc')
            ->get();
        return $sql;
    }

    public static function listInventoriesCosts($id_voucher)
    {
        $query = "SELECT
                        A.ID_VENTA,A.SERIE,A.NUMERO,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,B.DETALLE,B.CANTIDAD AS CANT,B.PRECIO_ALM,ABS(C.CANTIDAD) AS CANTIDAD,C.COSTO_UNITARIO,ABS(C.COSTO_TOTAL) AS COSTO_TOTAL,C.TIPO,B.PRECIO_ALM-ABS(C.COSTO_UNITARIO)AS DIF
                    FROM VENTA A JOIN VENTA_DETALLE B
                    ON A.ID_VENTA = B.ID_VENTA
                    JOIN INVENTARIO_KARDEX C
                    ON B.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                    AND B.ID_VDETALLE = C.ID_ORIGEN
                    WHERE A.ID_VOUCHER = " . $id_voucher . "
                    ORDER BY A.ID_COMPROBANTE,A.SERIE,A.NUMERO  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listInventoriesCostsOutputs($id_voucher)
    {
        $query = "SELECT
                        A.ID_MOVIMIENTO,A.SERIE,A.NUMERO,A.FECHA,X.NOMBRE AS DETALLE,
                        B.CANTIDAD AS CANT,B.COSTO,ABS(C.CANTIDAD) AS CANTIDAD,C.COSTO_UNITARIO,ABS(C.COSTO_TOTAL) AS COSTO_TOTAL,C.TIPO,B.COSTO-ABS(C.COSTO_UNITARIO)AS DIF
                FROM INVENTARIO_MOVIMIENTO A JOIN INVENTARIO_DETALLE B
                ON A.ID_MOVIMIENTO = B.ID_MOVIMIENTO
                JOIN INVENTARIO_KARDEX C
                ON A.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND B.ID_MOVDETALLE = C.ID_ORIGEN
                JOIN INVENTARIO_ARTICULO X
                ON C.ID_ARTICULO = X.ID_ARTICULO
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                AND A.TIPO = 'S'
                ORDER BY A.ID_MOVIMIENTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function lisSalesSummary($id_voucher)
    {
        $query = "SELECT
                        FC_CUENTA_DENOMINACIONAL(C.CUENTA) AS CUENTA_N,
                        C.CUENTA,
                        FC_DEPARTAMENTO(C.DEPTO) AS DEPTO_N,C.DEPTO,
                        SUM(CASE  WHEN C.IMPORTE > 0 THEN C.IMPORTE ELSE 0 END) DEBITO,
                        ABS(SUM(CASE  WHEN C.IMPORTE < 0 THEN C.IMPORTE ELSE 0 END)) AS CREDITO
                FROM VENTA A JOIN VENTA_DETALLE B
                ON A.ID_VENTA = B.ID_VENTA
                JOIN CONTA_ASIENTO C
                ON B.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND B.ID_VDETALLE = C.ID_ORIGEN
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                AND A.ESTADO = '1'
                AND (C.CUENTA  LIKE '113%' OR C.CUENTA  LIKE '213%' OR C.CUENTA  LIKE '315%' OR C.CUENTA  LIKE '316%' OR C.CUENTA  LIKE '318%' OR C.CUENTA  LIKE '314%' OR C.CUENTA  LIKE '412%' OR C.CUENTA  LIKE '216%' OR C.CUENTA  LIKE '421%' OR C.CUENTA  LIKE '411%' OR C.CUENTA  LIKE '414%')
                GROUP BY C.CUENTA,C.DEPTO
                ORDER BY CUENTA,DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function lisSalesSummaryTotal($id_voucher)
    {
        $query = "SELECT
                        SUM(CASE  WHEN C.IMPORTE > 0 THEN C.IMPORTE ELSE 0 END) DEBITO,
                        ABS(SUM(CASE  WHEN C.IMPORTE < 0 THEN C.IMPORTE ELSE 0 END)) AS CREDITO
                FROM VENTA A JOIN VENTA_DETALLE B
                ON A.ID_VENTA = B.ID_VENTA
                JOIN CONTA_ASIENTO C
                ON B.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND B.ID_VDETALLE = C.ID_ORIGEN
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                AND (C.CUENTA  LIKE '113%' OR C.CUENTA  LIKE '213%' OR C.CUENTA  LIKE '315%' OR C.CUENTA  LIKE '316%' OR C.CUENTA  LIKE '318%' OR C.CUENTA  LIKE '314%' OR C.CUENTA  LIKE '412%' OR C.CUENTA  LIKE '216%' OR C.CUENTA  LIKE '421%' OR C.CUENTA  LIKE '411%' OR C.CUENTA  LIKE '414%')
                AND A.ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function lisSalesDetails($id_voucher)
    {
        $query = "SELECT  A.ID_VENTA,A.ID_COMPROBANTE,A.SERIE,A.NUMERO,
                        D.NOMBRE||' '||D.PATERNO||' '||D.MATERNO AS CLIENTE,
                        X.EMAIL,
                        A.TOTAL,
                        C.CUENTA,
                        C.DEPTO,
                        C.DESCRIPCION,
                        MIN(C.ID_ASIENTO) ID_ASIENTO,
                        SUM(CASE  WHEN C.IMPORTE > 0 THEN C.IMPORTE ELSE 0 END) DEBITO,
                        ABS(SUM(CASE  WHEN C.IMPORTE < 0 THEN C.IMPORTE ELSE 0 END)) AS CREDITO,
                        VF.NOMBRE AS NOMBRE_FILE,
                        VF.URL,
                        VF.TIPO, VF.FORMATO
                FROM VENTA A JOIN VENTA_DETALLE B
                ON A.ID_VENTA = B.ID_VENTA
                JOIN CONTA_ASIENTO C
                ON B.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND B.ID_VDETALLE = C.ID_ORIGEN
                JOIN MOISES.PERSONA D
                ON A.ID_CLIENTE = D.ID_PERSONA
                JOIN USERS X
                ON A.ID_PERSONA = X.ID
                LEFT JOIN ELISEO.VENTA_FILE VF ON VF.ID_VENTA = A.ID_VENTA
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                AND A.ESTADO = '1'
                GROUP BY  A.ID_VENTA,A.ID_COMPROBANTE,A.SERIE,A.NUMERO,D.NOMBRE,D.PATERNO,D.MATERNO,X.EMAIL,A.TOTAL,C.CUENTA,C.DEPTO,C.DESCRIPCION,VF.NOMBRE,
                VF.TIPO,VF.URL, VF.FORMATO
                ORDER BY X.EMAIL,A.ID_VENTA,ID_ASIENTO,C.CUENTA,C.DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    // ========

    public static function lisSalesSummaryCost($id_voucher)
    {
        $query = "SELECT
                        FC_CUENTA_DENOMINACIONAL(C.CUENTA) AS CUENTA_N,
                        C.CUENTA,
                        FC_DEPARTAMENTO(C.DEPTO) AS DEPTO_N,C.DEPTO,
                        SUM(CASE  WHEN C.IMPORTE > 0 THEN C.IMPORTE ELSE 0 END) DEBITO,
                        ABS(SUM(CASE  WHEN C.IMPORTE < 0 THEN C.IMPORTE ELSE 0 END)) AS CREDITO
                FROM VENTA A JOIN VENTA_DETALLE B
                ON A.ID_VENTA = B.ID_VENTA
                JOIN CONTA_ASIENTO C
                ON B.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND B.ID_VDETALLE = C.ID_ORIGEN
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                AND A.ESTADO = '1'
                AND (C.CUENTA LIKE '114%' OR C.CUENTA LIKE '317%')
                GROUP BY C.CUENTA,C.DEPTO
                ORDER BY CUENTA,DEPTO ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function lisSalesSummaryTotalcost($id_voucher)
    {
        $query = "SELECT
                        SUM(CASE  WHEN C.IMPORTE > 0 THEN C.IMPORTE ELSE 0 END) DEBITO,
                        ABS(SUM(CASE  WHEN C.IMPORTE < 0 THEN C.IMPORTE ELSE 0 END)) AS CREDITO
                FROM VENTA A JOIN VENTA_DETALLE B
                ON A.ID_VENTA = B.ID_VENTA
                JOIN CONTA_ASIENTO C
                ON B.ID_TIPOORIGEN = C.ID_TIPOORIGEN
                AND B.ID_VDETALLE = C.ID_ORIGEN
                WHERE A.ID_VOUCHER = " . $id_voucher . "
                AND (C.CUENTA LIKE '114%' OR C.CUENTA LIKE '317%')
                AND A.ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function searchSerie($id_entidadSession, $search)
    {
        // dd($search, 'ddd');
        $sql = DB::table('venta as a')
            // ->join('')
            ->where('a.id_entidad', $id_entidadSession)
            // ->where('a.id_almacen', $id_almacen)
            ->whereraw("concat(a.serie ||'-', a.numero)  like UPPER('%" . $search . "%')")
            ->select(
                'a.id_venta',
                'a.id_parent',
                'a.id_entidad',
                'a.id_depto',
                'a.id_anho',
                'a.id_mes',
                'a.id_persona',
                'a.id_cliente',
                'a.id_sucursal',
                'a.id_voucher',
                'a.id_comprobante',
                'a.id_tiponota',
                'a.id_tipotransaccion',
                'a.serie',
                'a.numero',
                'a.fecha',
                'a.glosa',
                'a.igv',
                'a.total',
                'a.estado',
                'a.descuento_global',
                'a.id_comprobante_ref',
                'a.serie_ref',
                'a.numero_ref',
                DB::raw("to_char(a.fecha, 'DD/MM/YYYY') as fecha_venta"),
                DB::raw("fc_nombre_cliente(a.id_cliente) as cliente")
            )
            ->orderBy('a.serie', 'asc')
            // ->distinct()
            ->get();
        return $sql;
    }
    public static function searchSerieNumero($serie, $search)
    {
        $sql = DB::table('venta as a')
            ->where('a.serie', $serie)
            ->whereraw(ComunData::fnBuscar('a.numero') . ' like ' . ComunData::fnBuscar("'%" . $search . "%'"))
            ->select('a.numero', 'a.serie', 'a.id_venta')
            ->orderBy('a.numero', 'asc')
            ->get();
        return $sql;
    }
    public static  function listDetalleVenta($id_venta)
    {
        // dd('xcfdf', $id_venta);
        $query = DB::table('venta_detalle as a')
            // ->join('inventario_almacen_articulo as b', 'a.id_almacen', '=', 'b.id_almacen')
            // ->join('inventario_articulo as c', 'b.id_articulo', '=', 'c.id_articulo')
            ->where("a.id_venta", '=', $id_venta)
            ->select(
                'a.id_vdetalle',
                'a.id_almacen',
                'a.id_articulo',
                // 'c.nombre',
                'a.id_dinamica',
                'a.detalle',
                'a.cantidad',
                'a.precio',
                'a.base',
                'a.igv',
                'a.importe',
                DB::raw("(select coalesce(sum(c.cantidad), 0) from VENTA v , VENTA_DETALLE c where v.ID_VENTA = c.ID_VENTA  and v.ID_PARENT = a.ID_VENTA and c.ID_VDETALLE_ori = a.ID_VDETALLE and v.estado = 1 ) cantidad_nota")
            )
            ->get();
        //   dd('xcfdf', $query);
        return $query;
    }
    public static function showSaleEfac($tipo_documento, $numero_legal, $estado)
    {
        if ($estado == "T") {
            $estado_val = "";
        } else {
            $estado_val = "and STATUS = '" . $estado . "' ";
        }
        $query = "SELECT
                        TIPO_DOCUMENTO AS ID_COMPROBANTE,TO_CHAR(FECHA_EMISION,'DD/MM/YYYY') AS FECHA,NUMERO_LEGAL,TO_CHAR(IMPORTE_TOTALVENTA,'999,999,999.99') AS TOTAL,STATUS AS ESTADO
                FROM IV_DOC_HDR
                WHERE NUMERO_LEGAL = '" . $numero_legal . "'
                AND TIPO_DOCUMENTO = '" . $tipo_documento . "' " . $estado_val . " ";
        $oQuery = DB::connection('efacapp')->select($query);
        return $oQuery;
    }
    public static function listSalesSeries($id_voucher)
    {
        $query = "SELECT DISTINCT ID_COMPROBANTE,SERIE
                FROM VENTA
                WHERE ID_VOUCHER = " . $id_voucher . "
                AND ESTADO = '1'
                ORDER BY ID_COMPROBANTE,SERIE ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listSalesEfac($id_voucher, $serie)
    {
        if ($serie == "T") {
            $serie_val = "";
        } else {
            $serie_val = "AND SERIE = '" . $serie . "' ";
        }
        $query = "SELECT
                            ID_VENTA,ID_COMPROBANTE,TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,SERIE||'-'||NUMERO AS NUMERO_LEGAL,SERIE,NUMERO,TOTAL,'VACIO' AS ESTADO
                FROM VENTA
                WHERE ID_VOUCHER = " . $id_voucher . "
                " . $serie_val . "
                AND ESTADO = '1'
                ORDER BY NUMERO ";
        // dd($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function sendSalesEfac($id_venta, $id_comprobante)
    {
        $error      = 0;
        $msg_error  = '';
        $obReturn  = [];
        try {
            $pdo    = DB::getPdo();
            $stmt   = $pdo->prepare("begin PKG_SALES.SP_VENTA_ELECTRONICA(:P_ID_VENTA,:P_ID_COMPROBANTE);end;");
            $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
            $stmt->execute();
            $oreturn['error']     = 0;
            $oreturn['message']   = "ok";
            return $oreturn;
        } catch (Exception $e) {
            $oreturn['error']     = 1;
            $oreturn['message']   = $e->getMessage();
            return $oreturn;
        }
    }
    public static function showSalesStatus($id_venta)
    {
        $query = "SELECT
                ID_VENTA,ID_PARENT,ID_VOUCHER,ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_MES,ID_CLIENTE,ID_SUCURSAL,
                ID_COMPROBANTE,ID_TIPONOTA,ID_IGV,ID_MONEDA,TIPOCAMBIO,ID_TIPOTRANSACCION AS ID_TIPOVENTA,
                SERIE||'-'||NUMERO AS NUMERO_LEGAL,
                AGRUPADO,ESTADO
                FROM VENTA
                WHERE ID_VENTA = $id_venta ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateSaleEfac($tipo_documento, $numero_legal, $estado)
    {
        $query = "UPDATE IV_DOC_HDR SET STATUS = 'AN', ANULADO = TRUE
                    WHERE numero_legal = '" . $numero_legal . "'
                    AND tipo_documento = '" . $tipo_documento . "'
                    AND STATUS = '" . $estado . "' ";
        $oQuery = DB::connection('efacapp')->update($query);
        return $oQuery;
    }
    public static function listAccountStatus($id_entidad, $id_depto, $id_anho, $id_mesDe, $id_mesA, $id_persona)
    {
        // dd($id_mesDe, $id_mesA);
        $query = "SELECT ID_TIPOVENTA, (CASE WHEN ID_TIPOVENTA = 1 THEN 'mov_academico' WHEN ID_TIPOVENTA = 2 THEN 'mov_ingles'
                        WHEN ID_TIPOVENTA = 3 THEN 'mov_musica' WHEN ID_TIPOVENTA = 4 THEN 'mov_cepre' ELSE 'otros' END) AS  TIPO_VENTA,
                        TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                        VOUCHER,LOTE,
                        SERIE||'-'||NUMERO AS DOCUMENTO,
                        MOV,
                        GLOSA,
                        (CASE WHEN TOTAL > 0 THEN TOTAL ELSE 0 END) AS CREDITO,
                        ABS(CASE WHEN TOTAL < 0 THEN TOTAL ELSE 0 END) AS DEBITO,
                        DENSE_RANK() OVER (ORDER BY ID_VENTA) AS CONTADOR,
                        ID_VENTA,
                        TIPO_DOCUMENTO,
                        NUMERO_LEGAL
                FROM VW_SALES_MOV A
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES BETWEEN  " . $id_mesDe . " AND " . $id_mesA . "
                AND A.ID_CLIENTE = " . $id_persona . "
                AND A.ID_TIPOVENTA IN (1,2,3,4)
                ORDER BY A.ID_VENTA,TO_CHAR(A.FECHA,'YYYY/MM/DD')  ASC, A.TOTAL DESC";
        //ORDER BY TO_CHAR(TO_DATE(FECHA),'YYYMMDD') ASC,ID_VENTA, TOTAL DESC ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listAccountStatusTotal($id_entidad, $id_depto, $id_anho, $id_mesDe, $id_mesA, $id_persona)
    {
        $query = "SELECT
                        (CASE WHEN ID_TIPOVENTA = 1 THEN 'mov_academico' WHEN ID_TIPOVENTA = 2 THEN 'mov_ingles'
                        WHEN ID_TIPOVENTA = 3 THEN 'mov_musica' WHEN ID_TIPOVENTA = 4 THEN 'mov_cepre' ELSE 'otros' END) AS  TIPO_VENTA,
                        SUM(CASE WHEN TOTAL > 0 THEN TOTAL ELSE 0 END) AS CREDITO,
                        SUM(ABS(CASE WHEN TOTAL < 0 THEN TOTAL ELSE 0 END)) AS DEBITO
                FROM VW_SALES_MOV
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = '" . $id_depto . "'
                AND ID_ANHO = " . $id_anho . "
                AND ID_MES BETWEEN  " . $id_mesDe . " AND " . $id_mesA . "
                AND ID_CLIENTE = " . $id_persona . "
                AND ID_TIPOVENTA IN (1,2,3,4)
                GROUP BY ID_TIPOVENTA";
        $oQuery = DB::select($query);
        return $oQuery;
    }


    public static function advancesStaff($anho, $id_mes)
    {

        $query = "SELECT
                    TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                        A.SERIE, A.NUMERO, A.GLOSA,
                        FC_NOMBRE_PERSONA(A.ID_CLIENTE) AS CLIENTE,
                        FC_DOCUMENTO_CLIENTE(A.ID_CLIENTE) AS DNI,
                        TO_CHAR(A.IMPORTE,'999,999,999.99') AS IMPORTE,
                        TO_CHAR(B.IMPORTE,'999,999,999.99') AS IMP_DET,
                        C.SERIE||'-'||C.NUMERO AS COMPROBANTE,
                        FC_NOMBRE_PERSONA(A.ID_PERSONA) AS CAJERO,
                        SUBSTR(D.ID_DEPTO,1,1) AS DEPTO
                    FROM CAJA_DEPOSITO A JOIN CAJA_DEPOSITO_DETALLE B
                    ON A.ID_DEPOSITO = B.ID_DEPOSITO
                    JOIN VENTA C
                    ON B.ID_VENTA = C.ID_VENTA
                    JOIN APS_TRABAJADOR D
                    ON A.ID_CLIENTE = D.ID_PERSONA
                    WHERE A.ID_ANHO = " . $anho . "
                    AND A.ID_MES = " . $id_mes . "
                    AND A.ID_MEDIOPAGO = '999'
                    AND A.ID_DEPOSITO IN (
                    SELECT ID_ORIGEN FROM CONTA_ASIENTO
                    WHERE ID_TIPOORIGEN = 7
                    AND CUENTA  = 1139090
                    AND IMPORTE > 0
                    )
                    ORDER BY A.ID_DEPOSITO";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    // or ".ComunData::fnBuscar("nom_persona")."  like ".ComunData::fnBuscar("'%".$nombre."%'")."
    public static function studenAcademic($nombre)
    {
        $query = DB::table('moises.VW_PERSONA_NATURAL_ALUMNO')
            ->whereraw("(codigo like '%" . $nombre . "%' or num_documento like '%" . $nombre . "%'
        or upper(nombre|| ' '  || paterno|| ' ' ||materno) like upper('%" . $nombre . "%')
        or upper(paterno|| ' ' ||materno|| ' ' ||nombre) like upper('%" . $nombre . "%'))")
            ->select('id_persona', 'codigo', 'nom_persona', 'num_documento')
            ->get();
        return $query;
    }
    public static function insertNotesStudent($request, $persona_reg, $entidad, $depto, $id_anho, $id_mes)
    {
        $nerror = 0;
        $msgerror = "";
        $id_nota = "";

        for ($x = 1; $x <= 200; $x++) {
            $msgerror .= "0";
        }

        $detalle            = $request->detail;
        foreach ($detalle as $items) {
            $datos = (object)$items;
        }
        $fecha_reg          = isset($request->fecha_reg)? Carbon::parse($request->fecha_reg)->format('Y-m-d'): null;

        $id_entidad         = $entidad;
        $id_depto           = $depto;
        $id_anho            = intval(date('Y'));
        $id_mes             = intval(date('m'));
        $id_cliente         = $request->id_cliente;
        $id_persona         = $persona_reg;
        $id_venta           = $datos->id_venta;
        $id_comprobante     = $request->id_comprobante;
        $id_moneda          = $datos->id_moneda;
        $id_tipotransaccion = null;
        $id_comprobante_ref = $datos->id_comprobante;
        $serie_ref          = $datos->serie;
        $numero_ref         = $datos->numero;
        $glosa              = $request->glosa;
        $importe            = $datos->total;
        $id_tiponota        = $request->id_tiponota;
        $id_dinamica        = $request->id_dinamica;

        // dd($id_entidad     ,
        // $id_depto          ,
        // $id_anho           ,
        // $id_mes            ,
        // $id_cliente        ,
        // $id_persona        ,
        // $id_venta          ,
        // $id_comprobante    ,
        // $id_moneda         ,
        // $id_tipotransaccion,
        // $id_comprobante_ref,
        // $serie_ref         ,
        // $numero_ref        ,
        // $glosa             ,
        // $importe           ,
        // $id_tiponota       ,
        // $id_dinamica       );
        if (empty($id_dinamica)) {
            $id_dinamica = null;
        }
        DB::beginTransaction();

        try {

            $stmt = DB::getPdo()->prepare("BEGIN PKG_SALES.SP_CREAR_NOTA_CD_ALUMNOS(
                                        :P_ID_ENTIDAD,
                                        :P_ID_DEPTO,
                                        :P_ID_ANHO,
                                        :P_ID_MES,
                                        :P_ID_CLIENTE,
                                        :P_ID_PERSONA,
                                        :P_ID_VENTA,
                                        :P_ID_COMPROBANTE,
                                        :P_ID_MONEDA,
                                        :P_ID_TIPOTRANSACCION,
                                        :P_ID_COMPROBANTE_REF,
                                        :P_SERIE_REF,
                                        :P_NUMERO_REF,
                                        :P_GLOSA,
                                        :P_IMPORTE,
                                        :P_ID_TIPONOTA,
                                        :P_ID_DINAMICA,
                                        :P_ERROR,
                                        :P_MSGERROR,
                                        :P_ID_NOTA
                                     ); END;");
            $stmt->bindParam(':P_ID_ENTIDAD',         $id_entidad,         PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO',           $id_depto,           PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO',            $id_anho,            PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES',             $id_mes,             PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CLIENTE',         $id_cliente,         PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA',         $id_persona,         PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_VENTA',           $id_venta,           PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPROBANTE',     $id_comprobante,     PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_MONEDA',          $id_moneda,          PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_TIPOTRANSACCION', $id_tipotransaccion, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_COMPROBANTE_REF', $id_comprobante_ref, PDO::PARAM_STR);
            $stmt->bindParam(':P_SERIE_REF',          $serie_ref,          PDO::PARAM_STR);
            $stmt->bindParam(':P_NUMERO_REF',         $numero_ref,         PDO::PARAM_STR);
            $stmt->bindParam(':P_GLOSA',              $glosa,              PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE',            $importe,            PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPONOTA',        $id_tiponota,        PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_DINAMICA',        $id_dinamica,        PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR',              $nerror,             PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR',           $msgerror,           PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_NOTA',            $id_nota,            PDO::PARAM_INT);
            $stmt->execute();

            if ($nerror == 0) {
                if (!is_null($fecha_reg)) {
                    DB::table('ELISEO.VENTA')->where('ID_VENTA', $id_nota)->update(['FECHA_REF' => $fecha_reg]);
                }
                DB::commit();
            } else {
                $nerror = 1;
                $msgerror = $msgerror;
                DB::rollBack();
            }
        } catch (\PDOException $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        } catch (Exception $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        }
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_nota' => $id_nota
        ];

        return $return;
    }
    public static function insertAsiento($request)
    {
        $nerror = 0;
        $msgerror = "";
        $id_nota = "";

        for ($x = 1; $x <= 200; $x++) {
            $msgerror .= "0";
        }

        $id_venta                = $request->id_venta;
        $id_dinamica             = $request->id_dinamica;
        $id_cuentaaasi           = $request->id_cuentaaasi;
        $id_restriccion          = $request->id_restriccion;
        $id_ctacte               = $request->id_ctacte;
        $id_fondo                = $request->id_fondo;
        $id_depto                = $request->id_depto;
        $importe                 = $request->importe;
        $importe_me              = $request->importe_me;
        $descripcion             = $request->descripcion;
        $editable                = $request->editable;
        $dc                      = $request->dc;
        $agrupa                  = $request->agrupa;
        $modo                    = $request->modo;

        // if ($dc == 'C') {
        //     $importe = '-'.$importe;
        // }

        DB::beginTransaction();

        try {

            $stmt = DB::getPdo()->prepare("BEGIN PKG_SALES.SP_CREAR_ASIENTO_VENTA(
                                        :P_ID_VENTA,
                                        :P_ID_DINAMICA,
                                        :P_ID_CUENTAAASI,
                                        :P_ID_RESTRICCION,
                                        :P_ID_CTACTE,
                                        :P_ID_FONDO,
                                        :P_ID_DEPTO,
                                        :P_IMPORTE,
                                        :P_IMPORTE_ME,
                                        :P_DESCRIPCION,
                                        :P_EDITABLE,
                                        :P_DC,
                                        :P_AGRUPA,
                                        :P_MODO,
                                        :P_ERROR,
                                        :P_MSN
                                     ); END;");
            $stmt->bindParam(':P_ID_VENTA',                 $id_venta,              PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DINAMICA',              $id_dinamica,           PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CUENTAAASI',            $id_cuentaaasi,         PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_RESTRICCION',           $id_restriccion,        PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_CTACTE',                $id_ctacte,             PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_FONDO',                 $id_fondo,              PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO',                 $id_depto,              PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE',                  $importe,               PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_ME',               $importe_me,            PDO::PARAM_INT);
            $stmt->bindParam(':P_DESCRIPCION',              $descripcion,           PDO::PARAM_STR);
            $stmt->bindParam(':P_EDITABLE',                 $editable,              PDO::PARAM_STR);
            $stmt->bindParam(':P_DC',                       $dc,                    PDO::PARAM_STR);
            $stmt->bindParam(':P_AGRUPA',                   $agrupa,                PDO::PARAM_STR);
            $stmt->bindParam(':P_MODO',                     $modo,                  PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR',                    $nerror,                PDO::PARAM_INT);
            $stmt->bindParam(':P_MSN',                      $msgerror,              PDO::PARAM_STR);
            $stmt->execute();

            if ($nerror == 0) {
                DB::commit();
            } else {
                $nerror = 1;
                $msgerror = $msgerror;
                DB::rollBack();
            }
        } catch (\PDOException $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        } catch (Exception $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        }
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_nota' => $id_nota
        ];

        return $return;
    }
    public static function getAsientoAlumnoNotas($id_venta)
    {
        $query = DB::table('eliseo.VENTA_ASIENTO')
            // ->where('id_venta', 20430)
            ->where('id_venta', $id_venta)
            ->orderBy('dc', 'desc')
            ->get();
        return $query;
    }

    public static function finalizarDCNotasAlumnos($request)
    {
        $nerror = 0;
        $msgerror = "";
        $id_nota = "";

        for ($x = 1; $x <= 200; $x++) {
            $msgerror .= "0";
        }
        $id_venta                = $request->id_venta;

        //DB::beginTransaction();

        //try {

            $stmt = DB::getPdo()->prepare("BEGIN PKG_SALES.SP_FINALIZAR_NOTA_CD_ALUMNOS(
                                        :P_ID_VENTA,
                                        :P_ERROR,
                                        :P_MSGERROR
                                     ); END;");
            $stmt->bindParam(':P_ID_VENTA',                 $id_venta,              PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR',                    $nerror,                PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR',                 $msgerror,              PDO::PARAM_STR);
            $stmt->execute();

            
            if ($nerror == 0) {
                //DB::commit();
            } else {
                $nerror = 1;
                $msgerror = $msgerror;
                //DB::rollBack();
            }
        /*} catch (\PDOException $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        } catch (Exception $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        }*/
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_nota' => $id_nota
        ];

        return $return;
    }
    public static function deleteAsientoAlumnoNotas($id_vasiento)
    {
        $query = DB::table('eliseo.VENTA_ASIENTO')
            ->where('id_vasiento', $id_vasiento)
            ->delete();
        return $query;
    }
    public static function updateAsientoAlumnoNotas($id_vasiento, $request)
    {

        $id_cuentaaasi           = $request->id_cuentaaasi;
        $id_restriccion          = $request->id_restriccion;
        $id_ctacte               = $request->id_ctacte;
        $id_fondo                = $request->id_fondo;
        $id_depto                = $request->id_depto;
        $importe                 = $request->importe;
        $descripcion             = $request->descripcion;
        $dc                      = $request->dc;

        // dd($id_vasiento, $request);
        $query = DB::table('eliseo.VENTA_ASIENTO')
            ->where('id_vasiento', $id_vasiento)
            ->update([
                'id_cuentaaasi'     => $id_cuentaaasi,
                'id_restriccion'    => $id_restriccion,
                'id_ctacte'         => $id_ctacte,
                'id_fondo'          => $id_fondo,
                'id_depto'          => $id_depto,
                'importe'           => $importe,
                'descripcion'       => $descripcion,
                'dc'                => $dc,
            ]);
        return $query;
    }
    public static function updateSaleSeat($id_vasiento, $request)
    {

        $id_cuentaaasi           = $request->id_cuentaaasi;
        $id_restriccion          = $request->id_restriccion;
        $id_ctacte               = $request->id_ctacte;
        $id_fondo                = $request->id_fondo;
        $id_depto                = $request->id_depto;
        $importe                 = $request->importe;
        $descripcion             = $request->descripcion;
        $dc                      = $request->dc;

        //         dd($id_vasiento, $request);
        $query = DB::table('eliseo.VENTA_ASIENTO')
            ->where('id_vasiento', $id_vasiento)
            ->update([
                'id_cuentaaasi'     => $id_cuentaaasi,
                'id_restriccion'    => $id_restriccion,
                'id_ctacte'         => $id_ctacte,
                'id_fondo'          => $id_fondo,
                'id_depto'          => $id_depto,
                'importe'           => $importe,
                'descripcion'       => $descripcion,
                'dc'                => $dc,
            ]);
        return $query;
    }
    public static function insertAsientoExcel($listaValid)
    {
        $nerror = 0;
        $msgerror = "";
        $id_nota = "";


        // if ($dc == 'C') {
        //     $importe = '-'.$importe;
        // }
        //  dd($listaValid);
        foreach ($listaValid as $datos) {
            $items = (object) $datos;

            if ($items->dc == 'C') {
                $items->importe = '-' . $items->importe;
            }


            for ($x = 1; $x <= 200; $x++) {
                $msgerror .= "0";
            }
            // dd($items);
            DB::beginTransaction();

            try {

                $stmt = DB::getPdo()->prepare("BEGIN PKG_SALES.SP_CREAR_ASIENTO_VENTA(
                                        :P_ID_VENTA,
                                        :P_ID_DINAMICA,
                                        :P_ID_CUENTAAASI,
                                        :P_ID_RESTRICCION,
                                        :P_ID_CTACTE,
                                        :P_ID_FONDO,
                                        :P_ID_DEPTO,
                                        :P_IMPORTE,
                                        :P_IMPORTE_ME,
                                        :P_DESCRIPCION,
                                        :P_EDITABLE,
                                        :P_DC,
                                        :P_AGRUPA,
                                        :P_MODO,
                                        :P_ERROR,
                                        :P_MSN
                                     ); END;");
                $stmt->bindParam(':P_ID_VENTA',                 $items->id_venta,              PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA',              $items->id_dinamica,           PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CUENTAAASI',            $items->id_cuentaaasi,         PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RESTRICCION',           $items->id_restriccion,        PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE',                $items->id_ctacte,             PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_FONDO',                 $items->id_fondo,              PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO',                 $items->id_depto,              PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE',                  $items->importe,               PDO::PARAM_INT);
                $stmt->bindParam(':P_IMPORTE_ME',               $items->importe_me,            PDO::PARAM_INT);
                $stmt->bindParam(':P_DESCRIPCION',              $items->descripcion,           PDO::PARAM_STR);
                $stmt->bindParam(':P_EDITABLE',                 $items->editable,              PDO::PARAM_STR);
                $stmt->bindParam(':P_DC',                       $items->dc,                    PDO::PARAM_STR);
                $stmt->bindParam(':P_AGRUPA',                   $items->agrupa,                PDO::PARAM_STR);
                $stmt->bindParam(':P_MODO',                     $items->modo,                  PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR',                    $nerror,                       PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN',                      $msgerror,                     PDO::PARAM_STR);
                $stmt->execute();

                if ($nerror == 0) {
                    DB::commit();
                } else {
                    $nerror = 1;
                    $msgerror = $msgerror;
                    DB::rollBack();
                }
            } catch (\PDOException $e) {
                $nerror = 1;
                $msgerror = $e->getMessage();
                DB::rollBack();
            } catch (Exception $e) {
                $nerror = 1;
                $msgerror = $e->getMessage();
                DB::rollBack();
            }
            $return = [
                'nerror' => $nerror,
                'msgerror' => $msgerror,
                'id_nota' => $id_nota
            ];
        }

        return $return;
    }
    public static function insertAsientoVenta($params)
    {
        $nerror = 0;
        $msgerror = "";
        $id = "";

        for ($x = 1; $x <= 200; $x++) {
            $msgerror .= "0";
        }
        $id_dinamica = '';
        $importe_me = '';
        $editable = 'S';
        $agrupa = 'N';
        $modo = '2';
        $id_venta = $params->id_venta;
        $id_cuentaaasi = $params->id_cuentaaasi;
        $id_restriccion = $params->id_restriccion;
        $id_ctacte = $params->id_ctacte;
        $id_fondo = $params->id_fondo;
        $id_depto = $params->id_depto;
        $importe = $params->importe;
        $glosa = $params->glosa;
        $dc = $params->dc;
        DB::beginTransaction();

        try {

            $stmt = DB::getPdo()->prepare("begin PKG_SALES.SP_CREAR_ASIENTO_VNT(:P_ID_VENTA,
                            :P_ID_DINAMICA,
                            :P_ID_CUENTAAASI,
                            :P_ID_RESTRICCION,
                            :P_ID_CTACTE,
                            :P_ID_FONDO,
                            :P_ID_DEPTO,
                            :P_IMPORTE,
                            :P_IMPORTE_ME,
                            :P_DESCRIPCION,
                            :P_EDITABLE,
                            :P_DC,
                            :P_AGRUPA,
                            :P_MODO,
                            :P_ERROR,
                            :P_MSN); end;");
            $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_CUENTAAASI', $id_cuentaaasi, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_CTACTE', $id_ctacte, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_FONDO', $id_fondo, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE_ME', $importe_me, PDO::PARAM_STR);
            $stmt->bindParam(':P_DESCRIPCION', $glosa, PDO::PARAM_STR);
            $stmt->bindParam(':P_EDITABLE', $editable, PDO::PARAM_STR);
            $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
            $stmt->bindParam(':P_AGRUPA', $agrupa, PDO::PARAM_STR);
            $stmt->bindParam(':P_MODO', $modo, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSN', $msgerror, PDO::PARAM_STR);
            $stmt->execute();

            if ($nerror == 0) {
                DB::commit();
            } else {
                $nerror = 1;
                $msgerror = $msgerror;
                DB::rollBack();
            }
        } catch (\PDOException $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        } catch (Exception $e) {
            $nerror = 1;
            $msgerror = $e->getMessage();
            DB::rollBack();
        }
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_vasiento' => $id
        ];

        return $return;
    }
    public static function sumAsientoD($id_venta)
    {
        $query = DB::table('eliseo.VENTA_ASIENTO')
            ->where('id_venta', $id_venta)
            ->where('dc', '=', 'D')
            ->get();
        return $query;
    }
    public static function sumAsientoC($id_venta)
    {
        $query = DB::table('eliseo.VENTA_ASIENTO')
            ->where('id_venta', $id_venta)
            ->where('dc', '=', 'C')
            ->get();
        return $query;
    }
    public static function addNotasParametersHead($id_venta, $id_user, $id_documento)
    {
        $query = "SELECT ' NOTA DE CREDITO ELECTRONICA' AS nombre,
        'NUMERO  : '||SERIE||'-'||NUMERO AS NUMERO,
        'FECHA   : '||TO_CHAR(FECHA,'DD/MM/YYYY')||' '||TO_CHAR(FECHA,'HH24:MI:SS') AS FECHA,
        'CLIENTE : '||FC_NOMBRE_PERSONA(ID_CLIENTE) AS CLIENTE,
        DECODE(ID_COMPROBANTE,'01','RUC   : ','DNI     : ')||FC_DOCUMENTO_CLIENTE(ID_CLIENTE)|| ' - CODIGO: ' || FC_CODIGO_ALUMNO(ID_CLIENTE) AS DOCUMENTO,
        'DIRECCION: '|| pkg_sales.FC_CLIENTE_DIRECCION(ID_CLIENTE) AS DIRECCION,
        'DOC. REF.: '||SERIE_REF||'-'||NUMERO_REF AS DOCREF,
        '----------------------------------------' AS LINE
                FROM VENTA
                WHERE ID_VENTA = " . $id_venta . "
                AND ESTADO = 1 ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "NOMBRE" => $item->nombre,
                "NUMERO" => $item->numero,
                "FECHA" => $item->fecha,
                "CLIENTE" => $item->cliente,
                "DOCUMENTO" => $item->documento,
                "DIRECCION" => $item->direccion,
                "DOCREF" => $item->docref,
                "LINEA1" => $item->line,
                "LINEA2" => $item->line
            );
        }
        foreach ($params as $clave => $valor) {
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',0)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);
        }
    }
    public static function addNotasParametersFoot($id_venta, $id_user, $id_documento, $cont)
    {
        $query = "SELECT
    lpad('SUBTOTAL    S/. ',18,' ')||lpad(trim(to_char(ABS(A.INAFECTA),'999,999,990.99')),20,' ') AS SUBTOTAL,
    lpad('I.G.V.     S/. ',18,' ')||lpad(trim(to_char(ABS(A.IGV),'999,999,990.99')),20,' ') AS IGV,
    lpad('PRECIO VENTA  S/. ',18,' ')||lpad(trim(to_char(ABS(A.TOTAL),'999,999,990.99')),20,' ') AS TOTAL,
    'Son: '||FC_NUMERO_TEXTO(ABS(A.TOTAL))||' Soles' AS NUMTXT,
    'Cajero : '||FC_NOMBRE_PERSONA(A.ID_PERSONA) AS CAJERO,
    NVL(VRESUMEN,' ') AS RESUMEN,
    '----------------------------------------' AS LINE
            FROM VENTA A
            WHERE A.ID_VENTA = " . $id_venta . "
            AND A.ESTADO = 1 ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "LINEA3" => $item->line,
                "SUBTOTAL" => $item->subtotal,
                "IGV" => $item->igv,
                "TOTAL" => $item->total,
                "LINEA4" => $item->line,
                "NUMTXT" => $item->numtxt,
                "CAJERO" => $item->cajero,
                "LINEA5" => $item->line,
                "RESUMEN" => $item->resumen
            );
        }
        foreach ($params as $clave => $valor) {
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',$cont)
                WHERE ID_PERSONA = $id_user ";
            DB::update($sql);
        }
    }
    public static function addNotasParametersBody($id_venta, $id_user, $id_documento)
    {
        $cont = 0;
        $query = "SELECT
                    B.ITEM,
                    B.DETALLE,
                    B.CANTIDAD,
                    LPAD(TRIM(TO_CHAR(B.IMPORTE,'999,999,999.99')),6,' ') AS IMPORTE
            FROM VENTA A JOIN VENTA_DETALLE B
            ON A.ID_VENTA = B.ID_VENTA
            AND A.ID_VENTA = " . $id_venta . "
            AND A.ESTADO = 1
            ORDER BY B.ITEM ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item) {
            $params = array(
                "CANT" => $item->cantidad,
                "GLOSA" => $item->detalle,
                "IMPORTE" => $item->importe
            );
            foreach ($params as $clave => $valor) {
                $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'" . $clave . "','" . $valor . "',$cont)
                    WHERE ID_PERSONA = $id_user ";
                DB::update($sql);
            }
            $cont++;
            $params = [];
        }
        return $cont;
    }
    public static function statusSaldoFinalAlumno($id_entidad, $id_depto, $id_anho, $id_mesDe, $id_mesA, $id_persona)
    {
        $query = "SELECT
    CASE WHEN SUM(TOTAL) < 0 THEN ABS(SUM(TOTAL)) ELSE 0 END AS CREDITO,
    CASE WHEN SUM(TOTAL) > 0 THEN (SUM(TOTAL)) ELSE 0 END AS DEBITO
    FROM (
    SELECT
    TOTAL
    FROM VW_SALES_MOV
    WHERE ID_ENTIDAD = " . $id_entidad . "
    AND ID_DEPTO = '" . $id_depto . "'
    AND ID_ANHO = " . $id_anho . "
    AND ID_MES BETWEEN  " . $id_mesDe . " AND " . $id_mesA . "
    AND ID_CLIENTE = " . $id_persona . "
    AND ID_TIPOVENTA IN (1,2,3,4)
    UNION ALL
    SELECT
    SUM(IMPORTE)*DECODE(SIGN(SUM(IMPORTE)),1,-1,0) AS TOTAL
    FROM VW_SALES_ADVANCES
    WHERE ID_ENTIDAD =  " . $id_entidad . "
    AND ID_DEPTO = '" . $id_depto . "'
    AND ID_ANHO = " . $id_anho . "
    AND ID_MES BETWEEN " . $id_mesDe . " AND " . $id_mesA . "
    AND ID_CLIENTE = " . $id_persona . "
    )";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showMyDeposits($id_entidad, $id_depto, $id_anho, $id_mesDe, $id_mesA, $id_persona)
    {
        $query = "SELECT
    DECODE(ID_MEDIOPAGO,'008','EFECTIVO','001','DEPOSITO EN CUENTA','005','TARJETA','006','TARJETA','OTROS') AS MEDIO_PAGO,SERIE,NUMERO,TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,GLOSA,TO_CHAR(FECHA_OPERACION,'DD/MM/YYYY') AS FECHA_OPERACION,NRO_OPERACION,
    nvl(IMPORTE, 0) as importe, nvl(IMPORTE_ME, 0) as importe_me
    FROM CAJA_DEPOSITO
    WHERE ID_ENTIDAD = " . $id_entidad . "
    AND ID_DEPTO = '" . $id_depto . "'
    AND ID_ANHO = " . $id_anho . "
    AND ID_MES BETWEEN " . $id_mesDe . " AND " . $id_mesA . "
    AND ID_CLIENTE = " . $id_persona . "
    AND ESTADO = '1'
    AND ID_TIPODEPOSITO = 1
    AND ID_DEPOSITO NOT IN (SELECT ID_DEPOSITO FROM CAJA_DEPOSITO_DETALLE WHERE ID_VENTA IN (SELECT ID_VENTA FROM VENTA WHERE ID_TIPOVENTA = 6))
    ORDER BY ID_DEPOSITO,FECHA";
        $oQuery = DB::select($query);
        return $oQuery;
    }


    public static function insertAsientoExcelTranf($listaValid)
    {
        $nerror = 0;
        $msgerror = "";
        $id_nota = "";
        foreach ($listaValid as $datos) {
            $items = (object) $datos;
            if ($items->dc == 'C') {
                $items->importe = '-' . $items->importe;
            }
            for ($x = 1; $x <= 200; $x++) {
                $msgerror .= "0";
            }
            DB::beginTransaction();
            try {
                $stmt = DB::getPdo()->prepare("begin PKG_SALES.SP_CREAR_TRANSFERENCIA_ASIENTO(:P_ID_TRANSFERENCIA, :P_ID_DINAMICA, :P_ID_CUENTAAASI, :P_ID_RESTRICCION, :P_ID_CTACTE, :P_ID_FONDO, :P_ID_DEPTO, :P_IMPORTE, :P_IMPORTE_ME, :P_DESCRIPCION, :P_EDITABLE, :P_DC, :P_AGRUPA, :P_MODO, :P_ERROR, :P_MSN); end;");
                $stmt->bindParam(':P_ID_TRANSFERENCIA', $items->id_transferencia, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DINAMICA', $items->id_dinamica, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_CUENTAAASI', $items->id_cuentaaasi, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_RESTRICCION', $items->id_restriccion, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_CTACTE', $items->id_ctacte, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_FONDO', $items->id_fondo, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $items->id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE', $items->importe, PDO::PARAM_STR);
                $stmt->bindParam(':P_IMPORTE_ME', $items->importe_me, PDO::PARAM_STR);
                $stmt->bindParam(':P_DESCRIPCION', $items->descripcion, PDO::PARAM_STR);
                $stmt->bindParam(':P_EDITABLE', $items->editable, PDO::PARAM_STR);
                $stmt->bindParam(':P_DC', $items->dc, PDO::PARAM_STR);
                $stmt->bindParam(':P_AGRUPA', $items->agrupa, PDO::PARAM_STR);
                $stmt->bindParam(':P_MODO', $items->modo, PDO::PARAM_STR);
                $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
                $stmt->bindParam(':P_MSN', $msgerror, PDO::PARAM_STR);
                $stmt->execute();
                if ($nerror == 0) {
                    $msgerror = 'Registro Correctamente';
                    DB::commit();
                } else {
                    $nerror = 1;
                    $msgerror = $msgerror;
                    DB::rollBack();
                }
            } catch (\PDOException $e) {
                $nerror = 1;
                $msgerror = $e->getMessage();
                DB::rollBack();
            } catch (Exception $e) {
                $nerror = 1;
                $msgerror = $e->getMessage();
                DB::rollBack();
            }
            $return = [
                'nerror' => $nerror,
                'msgerror' => $msgerror,
                'id_nota' => $id_nota
            ];
        }

        return $return;
    }

    public static function listTransfersEntryAs($id_transferencia)
    {
        $query = "SELECT
            id_tasiento, ID_FONDO,ID_CUENTAAASI,ID_RESTRICCION,ID_CTACTE,ID_DEPTO,DESCRIPCION,IMPORTE,IMPORTE_ME,DC, editable
            FROM VENTA_TRANSFERENCIA_ASIENTO
            WHERE ID_TRANSFERENCIA = " . $id_transferencia . "
            ORDER BY DC DESC";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function saleSeats($idSale)
    {
        /*$query = "select
                       ID_CUENTAAASI,
                       ID_RESTRICCION,
                       ID_CTACTE,
                       ID_FONDO,
                       ID_DEPTO,
                       DESCRIPCION,
                       dc,
                       editable,
                       sum(IMPORTE) AS IMPORTE
                from VENTA_ASIENTO
                where ID_VENTA = ".$idSale."
                  and AGRUPA <> 'G'
                group by  ID_CUENTAAASI, ID_RESTRICCION, ID_CTACTE, ID_FONDO, ID_DEPTO, DESCRIPCION, EDITABLE, DC
                order by dc desc";*/
        $oQuery = DB::table('eliseo.venta_asiento as a')
        ->join('eliseo.venta as v', 'v.id_venta', '=', 'a.ID_VENTA')
        ->leftJoin('eliseo.conta_entidad_depto as d', function($join) {
            $join->on('d.id_entidad', '=', 'v.id_entidad')
                 ->on('d.id_depto', '=', 'a.id_depto');
        })
        ->where('a.ID_VENTA', $idSale)
        ->where('a.AGRUPA', '!=', 'G')
        ->select('a.*', 'd.nombre as depto')
        ->orderBy('a.DC', 'DESC')
        ->get();
        return $oQuery;
    }

    public static function sumAsientoDEntryAs($id_transferencia)
    {
        $query = DB::table('eliseo.VENTA_TRANSFERENCIA_ASIENTO')
            ->where('id_transferencia', $id_transferencia)
            ->where('dc', '=', 'D')
            ->get();
        return $query;
    }
    public static function sumAsientoCEntryAs($id_transferencia)
    {
        $query = DB::table('eliseo.VENTA_TRANSFERENCIA_ASIENTO')
            ->where('id_transferencia', $id_transferencia)
            ->where('dc', '=', 'C')
            ->get();
        return $query;
    }
    public static function detailSale($idSail)
    {
        $query = DB::table('ELISEO.VENTA_DETALLE')
            ->where('ID_VENTA', $idSail)
            ->get();
        return $query;
    }


    public static function deleteAsientoTranf($id_vasiento)
    {
        return DB::table('eliseo.VENTA_TRANSFERENCIA_ASIENTO')
            ->where('id_tasiento', $id_vasiento)
            ->delete();
    }
    public static function deleteAsientoTranfVnt($id_vasiento)
    {
        return DB::table('eliseo.VENTA_ASIENTO')
            ->where('ID_VASIENTO', $id_vasiento)
            ->delete();
    }

    public static function updateAsientoTranf($id_vasiento, $request)
    {

        $id_cuentaaasi           = $request->id_cuentaaasi;
        $id_restriccion          = $request->id_restriccion;
        $id_ctacte               = $request->id_ctacte;
        $id_fondo                = $request->id_fondo;
        $id_depto                = $request->id_depto;
        $importe                 = $request->importe;
        $descripcion             = $request->descripcion;
        $dc                      = $request->dc;

        // dd($id_vasiento, $request);
        $query = DB::table('eliseo.VENTA_TRANSFERENCIA_ASIENTO')
            ->where('id_tasiento', $id_vasiento)
            ->update([
                'id_cuentaaasi'     => $id_cuentaaasi,
                'id_restriccion'    => $id_restriccion,
                'id_ctacte'         => $id_ctacte,
                'id_fondo'          => $id_fondo,
                'id_depto'          => $id_depto,
                'importe'           => $importe,
                'descripcion'       => $descripcion,
                'dc'                => $dc,
            ]);
        return $query;
    }

    public static function searchNaturalPerson($param, $id_almacen)
    {
        $query = "select a.ID_PERSONA, FC_NOMBRE_PERSONA(a.ID_PERSONA) as NOM_PERSONA, b.NUM_DOCUMENTO as NUM_DOCUMENTO, c.CODIGO,
        PKG_SALES.FC_CREDITO_PERSONAL_POLITIC(a.ID_PERSONA, :id_almacen_p) CREDITO,
        0 CANT
        from MOISES.PERSONA a
        inner join MOISES.PERSONA_NATURAL b ON a.ID_PERSONA = b.ID_PERSONA
        left join MOISES.PERSONA_NATURAL_ALUMNO c on b.ID_PERSONA = c.ID_PERSONA
        where
              NVL(upper(b.NUM_DOCUMENTO),'')||
              NVL(upper(c.CODIGO),'')||
              NVL(upper(a.NOMBRE),'')||
              NVL(upper(a.PATERNO),'')||
              NVL(upper(a.MATERNO),'')||
              NVL(upper(concat(a.PATERNO, a.MATERNO)),'')||
              NVL(upper(concat(a.NOMBRE, a.PATERNO)),'')
                  like upper(replace('%$param%', chr(32), '')) AND ROWNUM <= 20";
        return DB::select($query, ['id_almacen_p' => $id_almacen]);
    }

    public static function salesMovOtros($id_entidad, $id_depto, $id_anho, $id_cliente)
    {
        // dd($id_cliente);
        $query = "SELECT
                    ID_ENTIDAD,
                    ID_DEPTO,
                    ID_ANHO,
                    ID_VENTA,
                    ID_CLIENTE,
                    ID_SUCURSAL,
                    SERIE,
                    NUMERO,
                    ID_MONEDA,
                    ID_COMPROBANTE,
                    SUM (TOTAL) TOTAL,
                    SUM (TOTAL_ME) TOTAL_ME
                FROM VW_SALES_MOV
                WHERE ID_ENTIDAD = $id_entidad
                AND ID_DEPTO = '" . $id_depto . "'
                AND ID_ANHO = $id_anho
                AND ID_CLIENTE = $id_cliente
                AND TIPO = 'V'
                HAVING NVL(SUM(TOTAL),0)+NVL(SUM(TOTAL_ME),0) = 0
                GROUP BY ID_ENTIDAD,ID_DEPTO,ID_ANHO,ID_VENTA,ID_CLIENTE,ID_SUCURSAL,SERIE,NUMERO,ID_MONEDA, ID_COMPROBANTE";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function anexoXML($anexo)
    {
        // dd($id_cliente);
        $query = "SELECT
                    XMLELEMENT(NAME \"Response\", xmlelement(name \"PreAnswer\",
                        XMLFOREST('sip:'||'" . $anexo . "'||'@voip.upeu.edu.pe' AS \"SIPTransfer\"))
                    ) AS DATA
                FROM DUAL  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }


    static function getDireccionVenta($id_venta)
    {
        $data = DB::select(
            "SELECT (CASE WHEN ID_COMPROBANTE = '01' AND ID_CLIENTE_LEGAL IS NULL THEN NVL(ID_SUCURSAL,ID_CLIENTE)
    WHEN ID_COMPROBANTE = '01' AND ID_CLIENTE_LEGAL IS NOT NULL THEN NVL(ID_SUCURSAL,ID_CLIENTE_LEGAL) ELSE ID_CLIENTE END) AS keyAs,
        ELISEO.PKG_SALES.FC_CLIENTE_DIRECCION((CASE WHEN ID_COMPROBANTE = '01' AND ID_CLIENTE_LEGAL IS NULL THEN NVL(ID_SUCURSAL,ID_CLIENTE)
    WHEN ID_COMPROBANTE = '01' AND ID_CLIENTE_LEGAL IS NOT NULL THEN NVL(ID_SUCURSAL,ID_CLIENTE_LEGAL) ELSE ID_CLIENTE END)) as DIRECCION
        FROM ELISEO.VENTA A
        WHERE ID_VENTA = :id_venta_p",
            ['id_venta_p' => $id_venta]
        );
        /*
        $data = DB::select("SELECT (CASE WHEN ID_CLIENTE_LEGAL IS NULL THEN ID_CLIENTE ELSE NVL(ID_SUCURSAL,ID_CLIENTE_LEGAL) END ) AS keyAs,
        ELISEO.PKG_SALES.FC_CLIENTE_DIRECCION((CASE WHEN ID_CLIENTE_LEGAL IS NULL THEN ID_CLIENTE ELSE NVL(ID_SUCURSAL,ID_CLIENTE_LEGAL) END )) as DIRECCION
        FROM ELISEO.VENTA A
        WHERE ID_VENTA = :id_venta_p",
            ['id_venta_p' => $id_venta]);
        */
        // $data = DB::select("SELECT NVL(A.ID_SUCURSAL, A.ID_CLIENTE) AS keyAs,
        // ELISEO.PKG_SALES.FC_CLIENTE_DIRECCION(NVL(A.ID_SUCURSAL, A.ID_CLIENTE)) as DIRECCION
        // FROM ELISEO.VENTA A
        // WHERE ID_VENTA = :id_venta_p",
        //     ['id_venta_p' => $id_venta]);

        return collect($data)->first();
    }
    public static function addSeatGlobalSales($request)
    {

        $id_venta           = $request->id_venta;
        $id_vdetalle        = $request->id_vdetalle;
        $id_dinamica        = $request->id_dinamica;
        $importe = 0;
        $detalle            = $request->detalle;
        // $descuento          = $request->descuento;
        $importe_me         = $request->importe_me;
        // $dc                 = $request->dc;
        $editable           = 'S';
        $record = 0;
        $asientos = AccountingData::listAccountingRecursivoEntryDetails($id_dinamica, 0);
        DB::beginTransaction();
        if (count($asientos) > 0) {
            $asient = $asientos[0];

            $count = DB::table('eliseo.VENTA_ASIENTO')->where('id_venta', $id_venta)->count();
            if ($count > 0) {
                $delete = DB::table('eliseo.VENTA_ASIENTO')->where('id_venta', $id_venta)->delete();
            }

            foreach ($asient['children'] as $ite) {
                $items = (object) $ite;

                if ($items->indicador == 'IMPORTE') {
                    $importe           = $request->importe;
                } else if ($items->indicador == 'BASE') {
                    $importe           = ($request->importe) - ($request->igv);
                } else if ($items->indicador == 'IGV') {
                    $importe           = $request->igv;
                } else if ($items->indicador == 'DESCUENTO') {
                    $importe           = $request->descuento;
                } else {
                    $importe           = $request->importe;
                }
                $id_vasiento = ComunData::correlativo('eliseo.VENTA_ASIENTO', 'id_vasiento');
                if ($id_vasiento > 0) {
                    $diasact = DB::table('eliseo.VENTA_ASIENTO')
                        ->insert([
                            'id_vasiento'       => $id_vasiento,
                            'id_venta'          => $id_venta,
                            'id_cuentaaasi'     => $items->id_cuentaaasi,
                            'id_restriccion'    => $items->id_restriccion,
                            'id_ctacte'         => $items->ctacte,
                            'id_fondo'          => '10',
                            'id_depto'          => $items->depto,
                            'importe'           => $importe,
                            'importe_me'        => $importe_me,
                            'descripcion'       => $detalle,
                            'editable'          => $editable,
                            'dc'                => $items->dc,
                            'agrupa'            => $items->agrupa,
                            'id_vdetalle'       => $id_vdetalle,
                        ]);
                    if ($diasact) {
                        $record++;
                    }
                } else {
                    $result = [
                        'success' => false,
                        'message' => 'No se pudo insertar correlativo',
                        'data' => $record,
                    ];
                    DB::rollBack();
                }
            }
            if ($record > 0) {
                $result = [
                    'success' => true,
                    'message' => 'Se inserto satisfactoriamente',
                    'data' => $record,
                ];
                DB::commit();
            } else {
                $result = [
                    'success' => false,
                    'message' => 'No se pudo insertar',
                    'data' => $record,
                ];
                DB::rollBack();
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'No se encontraron asientos guardados, con la dinamica',
                'data' => $record,
            ];
            DB::rollBack();
        }

        return $result;
    }
    public static function duplicarSeatGlobalSales($request)
    {

        $id_vasiento = ComunData::correlativo('eliseo.VENTA_ASIENTO', 'id_vasiento');
        $diasact = DB::table('eliseo.VENTA_ASIENTO')
            ->insert([
                'id_vasiento'       => $id_vasiento,
                'id_venta'          => $request->id_venta,
                'id_cuentaaasi'     => $request->id_cuentaaasi,
                'id_restriccion'    => $request->id_restriccion,
                'id_ctacte'         => $request->ctacte,
                'id_fondo'          => '10',
                'id_depto'          => $request->depto,
                'importe'           => $request->importePositivo,
                'importe_me'        => $request->importe_me,
                'descripcion'       => $request->descripcion,
                'editable'          => $request->editable,
                'dc'                => $request->dc,
                'agrupa'            => $request->agrupa,
                'id_vdetalle'       => $request->id_vdetalle,
            ]);


        if ($diasact) {
            $result = [
                'success' => true,
                'message' => 'Se inserto satisfactoriamente',
                'data' => '',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se pudo insertar',
                'data' => '',
            ];
        }

        return $result;
    }
    public static function listSeatsGlobal($request)
    {

        $id_venta   = $request->id_venta;
        $query = DB::table('eliseo.VENTA_ASIENTO')
            ->where('id_venta', $id_venta)
            ->select('*')
            ->orderBy('id_vasiento')
            ->get();
        return $query;
    }
    public static function deleteSeatsGlobal($id_vasiento)
    {

        $res = DB::table('eliseo.VENTA_ASIENTO')
            ->where('id_vasiento', $id_vasiento)
            ->delete();
        if ($res) {
            $result = [
                'success' => true,
                'message' => 'Eliminado',
                'data' => '',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se puede eliminar',
                'data' => '',
            ];
        }
        return $result;
    }
    public static function updateSeatsGlobal($id_vasiento, $request)
    {

        $id_cuentaaasi           = $request->id_cuentaaasi;
        $id_restriccion          = $request->id_restriccion;
        $id_ctacte               = $request->id_ctacte;
        $id_fondo                = $request->id_fondo;
        $id_depto                = $request->id_depto;
        $importe                 = $request->importe;
        $descripcion             = $request->descripcion;
        $dc                      = $request->dc;

        $res = DB::table('eliseo.VENTA_ASIENTO')
            ->where('id_vasiento', $id_vasiento)
            ->update([
                'id_cuentaaasi'     => $id_cuentaaasi,
                'id_restriccion'    => $id_restriccion,
                'id_ctacte'         => $id_ctacte,
                'id_fondo'          => $id_fondo,
                'id_depto'          => $id_depto,
                'importe'           => $importe,
                'descripcion'       => $descripcion,
                'dc'                => $dc,
            ]);
        if ($res) {
            $result = [
                'success' => true,
                'message' => 'Eliminado',
                'data' => '',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se puede eliminar',
                'data' => '',
            ];
        }
        return $result;
    }
    public static function statusAccouentClients($id_entidad, $id_depto, $id_anho, $id_persona)
    {
        // dd($id_mesDe, $id_mesA);
        $query = "SELECT ID_TIPOVENTA, (CASE WHEN ID_TIPOVENTA = 1 THEN 'mov_academico' WHEN ID_TIPOVENTA = 2 THEN 'mov_ingles'
                        WHEN ID_TIPOVENTA = 3 THEN 'mov_musica' WHEN ID_TIPOVENTA = 4 THEN 'mov_cepre' ELSE 'ventas_diversas' END) AS  TIPO_VENTA,
                        TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                        VOUCHER,LOTE,
                        SERIE||'-'||NUMERO AS DOCUMENTO,
                        MOV,
                        GLOSA,
                        (CASE WHEN TOTAL > 0 THEN TOTAL ELSE 0 END) AS CREDITO,
                        ABS(CASE WHEN TOTAL < 0 THEN TOTAL ELSE 0 END) AS DEBITO,
                        DENSE_RANK() OVER (ORDER BY ID_VENTA) AS CONTADOR,
                        ID_VENTA,
                        TIPO_DOCUMENTO,
                        NUMERO_LEGAL
                FROM VW_SALES_MOV A
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_CLIENTE = " . $id_persona . "
                AND A.ID_TIPOVENTA NOT IN (1,2,3,4)
                ORDER BY A.ID_VENTA,TO_CHAR(A.FECHA,'YYYY/MM/DD')  ASC, A.TOTAL DESC";
        //ORDER BY TO_CHAR(TO_DATE(FECHA),'YYYMMDD') ASC,ID_VENTA, TOTAL DESC ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function statusAccouentClientsTotal($id_entidad, $id_depto, $id_anho, $id_persona)
    {
        $query = "SELECT
                        (CASE WHEN ID_TIPOVENTA = 1 THEN 'mov_academico' WHEN ID_TIPOVENTA = 2 THEN 'mov_ingles'
                        WHEN ID_TIPOVENTA = 3 THEN 'mov_musica' WHEN ID_TIPOVENTA = 4 THEN 'mov_cepre' ELSE 'ventas_diversas' END) AS  TIPO_VENTA,
                        SUM(CASE WHEN TOTAL > 0 THEN TOTAL ELSE 0 END) AS CREDITO,
                        SUM(ABS(CASE WHEN TOTAL < 0 THEN TOTAL ELSE 0 END)) AS DEBITO
                FROM VW_SALES_MOV
                WHERE ID_ENTIDAD = " . $id_entidad . "
                AND ID_DEPTO = '" . $id_depto . "'
                AND ID_ANHO = " . $id_anho . "
                AND ID_CLIENTE = " . $id_persona . "
                AND ID_TIPOVENTA NOT IN (1,2,3,4)
                GROUP BY ID_TIPOVENTA";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function statusAccouentClientsSaldoFinal($id_entidad, $id_depto, $id_anho, $id_persona)
    {
        $query = "SELECT
    CASE WHEN SUM(TOTAL) < 0 THEN ABS(SUM(TOTAL)) ELSE 0 END AS CREDITO,
    CASE WHEN SUM(TOTAL) > 0 THEN (SUM(TOTAL)) ELSE 0 END AS DEBITO
    FROM (
    SELECT
    TOTAL
    FROM VW_SALES_MOV
    WHERE ID_ENTIDAD = " . $id_entidad . "
    AND ID_DEPTO = '" . $id_depto . "'
    AND ID_ANHO = " . $id_anho . "
    AND ID_CLIENTE = " . $id_persona . "
    AND ID_TIPOVENTA NOT IN (1,2,3,4))";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function seatStatus($request)
    {
        $id_venta = $request->id_venta;
        $query = "SELECT
        A.CUENTA,B.NOMBRE,A.CUENTA_CTE,A.RESTRICCION,A.DEPTO,A.DESCRIPCION,
        (CASE WHEN A.IMPORTE > 0 THEN A.IMPORTE ELSE 0 END) AS DEBITO,
        (ABS(CASE WHEN A.IMPORTE < 0 THEN A.IMPORTE ELSE 0 END)) AS CREDITO, A.IMPORTE
        FROM CONTA_ASIENTO A LEFT JOIN CONTA_CTA_DENOMINACIONAL B ON A.CUENTA = B.ID_CUENTAAASI AND B.ID_TIPOPLAN = 1
        WHERE A.ID_TIPOORIGEN = 1
        AND A.ID_ORIGEN IN (SELECT ID_VDETALLE FROM VENTA_DETALLE WHERE ID_VENTA = " . $id_venta . ")
        ORDER BY ID_ASIENTO";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function seatStatusTipoCero($request)
    {
        $voucher = $request->voucher;
        $id_deposito = $request->id_deposito;
        $query = "SELECT
        A.CUENTA,B.NOMBRE,A.CUENTA_CTE,A.RESTRICCION,A.DEPTO,A.DESCRIPCION, A.VOUCHER, A.ID_ORIGEN,
        (CASE WHEN A.IMPORTE > 0 THEN A.IMPORTE ELSE 0 END) AS DEBITO,
        (CASE WHEN A.IMPORTE < 0 THEN A.IMPORTE ELSE 0 END) AS CREDITO, A.IMPORTE
        FROM CONTA_ASIENTO A LEFT JOIN CONTA_CTA_DENOMINACIONAL B ON A.CUENTA = B.ID_CUENTAAASI AND B.ID_TIPOPLAN = 1
        WHERE A.ID_TIPOORIGEN = 7
        AND A.ID_ORIGEN IN (SELECT ID_DEPOSITO FROM CAJA_DEPOSITO WHERE ID_DEPOSITO = " . $id_deposito . ")
        AND A.VOUCHER = " . $voucher . "";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showMySalesArrangements($id_venta, $id_tipoorigen)
    {
        if ($id_tipoorigen == 1) {
            $query = "SELECT
                        ID_VENTA, FC_NOMBRE_CLIENTE(ID_CLIENTE) AS ALUMNO,FC_CODIGO_ALUMNO(ID_CLIENTE) AS CODIGO,
                        SERIE||'-'||NUMERO AS SERIE_NUMERO,FECHA,GLOSA,TOTAL
                FROM VENTA WHERE ID_VENTA = " . $id_venta . " ";
        } else {
            $query = "SELECT
                        ID_TRANSFERENCIA, FC_NOMBRE_CLIENTE(ID_CLIENTE) AS ALUMNO,FC_CODIGO_ALUMNO(ID_CLIENTE) AS CODIGO,
                        SERIE||'-'||NUMERO AS SERIE_NUMERO,FECHA,GLOSA,IMPORTE AS TOTAL
                FROM VENTA_TRANSFERENCIA WHERE ID_TRANSFERENCIA = " . $id_venta . " ";
        }

        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showMySeatSalesArrangements($id_venta, $id_tipoorigen)
    {
        if ($id_tipoorigen == 1) {
            $query = "SELECT
                        ID_ASIENTO,ID_TIPOORIGEN,ID_ORIGEN,FONDO,DEPTO,CUENTA,CUENTA_CTE,RESTRICCION,IMPORTE,DESCRIPCION,IMPORTE,IMPORTE_ME,VOUCHER AS ID_VOUCHER
                FROM CONTA_ASIENTO
                WHERE ID_TIPOORIGEN = 1
                AND ID_ORIGEN IN (SELECT ID_VDETALLE FROM VENTA_DETALLE WHERE ID_VENTA = " . $id_venta . ") ORDER BY ID_ASIENTO ";
        } else {
            $query = "SELECT
                        ID_ASIENTO,ID_TIPOORIGEN,ID_ORIGEN,FONDO,DEPTO,CUENTA,CUENTA_CTE,RESTRICCION,IMPORTE,DESCRIPCION,IMPORTE,IMPORTE_ME,VOUCHER AS ID_VOUCHER
                FROM CONTA_ASIENTO
                WHERE ID_TIPOORIGEN = 2
                AND ID_ORIGEN IN (SELECT ID_TRANSFERENCIA FROM VENTA_TRANSFERENCIA WHERE ID_TRANSFERENCIA = " . $id_venta . ") ORDER BY ID_ASIENTO ";
        }

        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getSedeByDepto($iddepto)
    {
        $query = "SELECT ID_SEDE, NOMBRE, CODIGO, ID_DEPTO
            FROM ORG_SEDE
            WHERE ID_DEPTO = $iddepto ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function expCodBankBBVA($inicio, $fin, $sede)
    {
        $query = "select
                        '01'||
                        '20138122256'||
                        '739'||
                        'PEN'||
                        to_char(sysdate,'yyyymmdd')||
                        '000'||
                        rpad(' ',330,' ') data
                    from moises.persona a join moises.persona_natural_alumno b on a.ID_PERSONA=b.ID_PERSONA
                    inner join ELISEO.VW_MAT_MATRICULADOS c on a.id_persona=c.ID_PERSONA
                    WHERE  b.CODIGO not like'A%'
                    and b.CODIGO not like'I%'
                    and b.CODIGO not like'L%'
                    and b.CODIGO not like'M%'
                    and b.CODIGO not like'S%'
                    AND B.CODIGO NOT IN('220140169','647000481','555555550')
                    AND C.ID_SEDE=" . $sede . "
                    and SUBSTR( C.SEMESTRE,1,4) BETWEEN '" . $inicio . "' AND '" . $fin . "'
                    group by 1
                    UNION ALL
                    SELECT DATA FROM(
                    select
                        '02'||
                        rpad(upper(substr(DAVID.sintilde(replace(replace(replace(replace(replace(a.paterno,'.',' '),'-',''),',',''),'',' '),'?','N'))||' '||DAVID.sintilde(replace(replace(replace(replace(replace(a.materno,'.',' '),'-',''),',',''),'',' '),'?','N'))||' '||Translate(DAVID.sintilde(replace(replace(replace(replace(replace(a.nombre,'.',' '),'-',''),',',''),'',' '),'?','N')),'ÁáÉéÍíÓóÚú','AaEeIiOoUunN'),1,30)),30,' ')|| 						lpad(b.codigo,9,'0')||rpad(upper(substr(DAVID.sintilde(replace(replace(replace(replace(replace(a.paterno,'.',' '),'-',''),',',''),'',' '),'?','N'))||' '||Translate(DAVID.sintilde(replace(replace(replace(replace(replace(a.materno,'.',' '),'-',''),',',''),'',' '),'?','N')),'ÁáÉéÍíÓóÚú','AaEeIiOoUunN')||' '||DAVID.sintilde(replace(replace(replace(replace(replace(a.nombre,'.',' '),'-',''),',',''),'',' '),'?','N')),1,39)),39,' ')||
                        '20211231'||
                        '20211231'||
                        rpad(' ',2,' ')||
                        rpad(' ',15,' ')||
                        rpad(' ',15,' ')||
                        rpad(' ',32,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',20,' ')||
                        'L'||
                        rpad(' ',15,' ')||
                        rpad(' ',36,' ') data
                    from moises.persona a join moises.persona_natural_alumno b on a.ID_PERSONA=b.ID_PERSONA
                    inner join ELISEO.VW_MAT_MATRICULADOS c on a.id_persona=c.ID_PERSONA
                    WHERE  b.CODIGO not like'A%'
                    and b.CODIGO not like'I%'
                    and b.CODIGO not like'L%'
                    and b.CODIGO not like'M%'
                    and b.CODIGO not like'S%'
                    AND B.CODIGO NOT IN('220140169','647000481','555555550')
                    AND C.ID_SEDE=" . $sede . "
                    and SUBSTR( C.SEMESTRE,1,4) BETWEEN '" . $inicio . "' AND '" . $fin . "'
                    GROUP BY A.PATERNO,A.MATERNO,A.NOMBRE,b.CODIGO
                    order by b.CODIGO ASC
                    )
                    UNION ALL
                    select
                            '03'||
                            trim(to_char(count(a.ID_PERSONA),'000000000'))||
                            rpad('0',18,'0')||
                            rpad('0',18,'0')||
                            rpad('0',18,'0')||
                            rpad(' ',295,' ') data
                    from moises.persona a inner join moises.persona_natural_alumno b on a.ID_PERSONA=b.ID_PERSONA
                    inner join (SELECT ID_SEDE, ID_PERSONA,SUBSTR( semestre,1,4) semestre FROM ELISEO.VW_MAT_MATRICULADOS GROUP BY ID_PERSONA,ID_SEDE,semestre) c on a.id_persona=c.ID_PERSONA
                    WHERE  b.CODIGO not like'A%'
                    and b.CODIGO not like'I%'
                    and b.CODIGO not like'L%'
                    and b.CODIGO not like'M%'
                    and b.CODIGO not like'S%'
                    AND B.CODIGO NOT IN('220140169','647000481','555555550')
                    AND C.ID_SEDE=" . $sede . "
                    and SUBSTR( C.SEMESTRE,1,4) BETWEEN '" . $inicio . "' AND '" . $fin . "' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function expCodBankBCP($inicio, $fin, $sede, $cuenta_corriente)
    {
        $query = " select
                    'CC'||
                    '" . $cuenta_corriente . "'||
                    'P'||
                    'UNIVERSIDAD PERUANA UNION'||
                  rpad(' ',15,' ')||
                    to_char(sysdate,'yyyymmdd')||
                    LPAD(COUNT(a.ID_PERSONA),9,0)||
                    rpad(' ',15,' ')||
                  'A'||
                  rpad(' ',163,' ')data
                from moises.persona a join moises.persona_natural_alumno b on a.ID_PERSONA=b.ID_PERSONA
                inner join (SELECT ID_SEDE, ID_PERSONA,SUBSTR( semestre,1,4) semestre FROM ELISEO.VW_MAT_MATRICULADOS GROUP BY ID_PERSONA,ID_SEDE,semestre) c on a.id_persona=c.ID_PERSONA
                WHERE  b.CODIGO not like'A%'
                and b.CODIGO not like'I%'
                and b.CODIGO not like'L%'
                and b.CODIGO not like'M%'
                and b.CODIGO not like'S%'
                AND B.CODIGO NOT IN('220140169','647000481','555555550')
                AND C.ID_SEDE=" . $sede . "
                and SUBSTR( C.SEMESTRE,1,4) BETWEEN '" . $inicio . "' AND '" . $fin . "'
                UNION ALL
                SELECT DATA FROM(
                select
                    'DD'||
                  '" . $cuenta_corriente . "'||
                  LPAD(b.CODIGO,14,0)||
                    rpad(upper(substr(DAVID.sintilde(replace(replace(replace(replace(replace(a.paterno,'.',' '),'-',''),',',''),'',' '),'?','N'))||' '||DAVID.sintilde(replace(replace(replace(replace(replace(a.materno,'.',' '),'-',''),',',''),'',' '),'?','N'))||' '||Translate(DAVID.sintilde(replace(replace(replace(replace(replace(a.nombre,'.',' '),'-',''),',',''),'',' '),'?','N')),'ÁáÉéÍíÓóÚú','AaEeIiOoUunN'),1,40)),40,' ')||
                    rpad(' ',3,' ')||
                    rpad(' ',14,' ')||
                    rpad(' ',2,' ')||
                    rpad(' ',14,' ')||
                    rpad(' ',2,' ')||
                    rpad(' ',14,' ')||
                    rpad(' ',2,' ')||
                    rpad(' ',14,' ')||
                    rpad(' ',20,' ')||
                    'A'||
                    rpad(' ',33,' ')||
                    rpad(' ',64,' ') data
                from moises.persona a join moises.persona_natural_alumno b on a.ID_PERSONA=b.ID_PERSONA
                inner join ELISEO.VW_MAT_MATRICULADOS c on a.id_persona=c.ID_PERSONA
                WHERE  b.CODIGO not like'A%'
                and b.CODIGO not like'I%'
                and b.CODIGO not like'L%'
                and b.CODIGO not like'M%'
                and b.CODIGO not like'S%'
                AND B.CODIGO NOT IN('220140169','647000481','555555550')
                AND C.ID_SEDE=" . $sede . "
                and SUBSTR( C.SEMESTRE,1,4) BETWEEN '" . $inicio . "' AND '" . $fin . "'
                GROUP BY A.PATERNO,A.MATERNO,A.NOMBRE,b.CODIGO
                order by b.CODIGO ASC) ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function expCodBankScotiabank($inicio, $fin, $sede, $cuenta_corriente)
    {
        $query = "select
                        'CC'||
                        '" . $cuenta_corriente . "'||
                        'P'||
                        'UNIVERSIDAD PERUANA UNION'||
                        rpad(' ',15,' ')||
                        to_char(sysdate,'yyyymmdd')||
                        LPAD(COUNT(a.ID_PERSONA),9,0)||
                        rpad(' ',15,' ')||
                      'A'||
                      rpad(' ',113,' ')data
                    from moises.persona a join moises.persona_natural_alumno b on a.ID_PERSONA=b.ID_PERSONA
                    inner join (SELECT ID_SEDE, ID_PERSONA,SUBSTR( semestre,1,4) semestre FROM ELISEO.VW_MAT_MATRICULADOS GROUP BY ID_PERSONA,ID_SEDE,semestre) c on a.id_persona=c.ID_PERSONA
                    WHERE  b.CODIGO not like'A%'
                    and b.CODIGO not like'I%'
                    and b.CODIGO not like'L%'
                    and b.CODIGO not like'M%'
                    and b.CODIGO not like'S%'
                    AND B.CODIGO NOT IN('220140169','647000481','555555550')
                    AND C.ID_SEDE=" . $sede . "
                    and SUBSTR( C.SEMESTRE,1,4) BETWEEN '" . $inicio . "' AND '" . $fin . "'
                    UNION ALL
                    SELECT DATA FROM(
                    select
                        'DD'||
                      '" . $cuenta_corriente . "'||
                      LPAD(b.CODIGO,9,0)||
                      rpad(' ',5,' ')||
                        rpad(upper(substr(DAVID.sintilde(replace(replace(replace(replace(replace(a.paterno,'.',' '),'-',''),',',''),'',' '),'?','N'))||' '||DAVID.sintilde(replace(replace(replace(replace(replace(a.materno,'.',' '),'-',''),',',''),'',' '),'?','N'))||' '||DAVID.sintilde(replace(replace(replace(replace(replace(a.nombre,'.',' '),'-',''),',',''),'',' '),'?','N')),1,40)),40,' ')||
                        rpad(' ',3,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',2,' ')||
                        rpad(' ',14,' ')||
                        rpad(' ',20,' ')||
                        'A'||
                        rpad(' ',33,' ')||
                        rpad(' ',14,' ') data
                    from moises.persona a join moises.persona_natural_alumno b on a.ID_PERSONA=b.ID_PERSONA
                    inner join ELISEO.VW_MAT_MATRICULADOS c on a.id_persona=c.ID_PERSONA
                    WHERE  b.CODIGO not like'A%'
                    and b.CODIGO not like'I%'
                    and b.CODIGO not like'L%'
                    and b.CODIGO not like'M%'
                    and b.CODIGO not like'S%'
                    AND B.CODIGO NOT IN('220140169','647000481','555555550')
                    AND C.ID_SEDE=" . $sede . "
                    and SUBSTR( c.semestre,1,4) BETWEEN '" . $inicio . "' AND '" . $fin . "'
                    GROUP BY A.PATERNO,A.MATERNO,A.NOMBRE,b.CODIGO
                    order by b.CODIGO ASC ) ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function AccountsReceivable($id_entidad, $id_depto, $id_anho, $id_mes, $tipo)
    {
        if ($tipo == 'A') {
            $dato = "AND A.ID_TIPOVENTA IN (1, 2, 3, 4) ";
        } else {
            $dato = "AND A.ID_TIPOVENTA NOT IN (1, 2, 3, 4) ";
        }
        $sql = "SELECT
                    ID_ENTIDAD,
                    ID_DEPTO,
                    ID_ANHO,
                    ID_CLIENTE,
                    NVL(NVL((SELECT X.NOMBRE FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_CLIENTE AND ROWNUM = 1),
                    (SELECT X.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_CLIENTE AND ID_TIPODOCUMENTO = 1 AND ROWNUM = 1)),
                    (SELECT X.NOM_PERSONA FROM MOISES.VW_PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_CLIENTE AND ID_TIPODOCUMENTO = 6 AND ROWNUM = 1)) AS CLIENTE,
                    --(SELECT X.ID_RUC FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_CLIENTE UNION ALL
                    --SELECT X.NUM_DOCUMENTO FROM MOISES.VW_PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_CLIENTE AND ID_TIPODOCUMENTO = 1 AND ROWNUM = 1) AS DOCUMENTO,
                    (
                        CASE WHEN (SELECT COUNT(X.ID_RUC) FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_CLIENTE) > 0 
                        THEN (SELECT X.ID_RUC FROM MOISES.VW_PERSONA_JURIDICA X WHERE X.ID_PERSONA = A.ID_CLIENTE) ELSE 
                        (SELECT X.NUM_DOCUMENTO FROM MOISES.VW_PERSONA_NATURAL X WHERE X.ID_PERSONA = A.ID_CLIENTE AND ID_TIPODOCUMENTO = 1) END 
                    ) AS DOCUMENTO,
                    (SELECT X.CODIGO FROM MOISES.VW_PERSONA_NATURAL_ALUMNO X WHERE X.ID_PERSONA = A.ID_CLIENTE) AS CODIGO,
       SUM (A.TOTAL) TOTAL,
    SUM (A.TOTAL_ME) TOTAL_ME

                FROM VW_SALES_MOV A
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES <= " . $id_mes . "
                " . $dato . "
                HAVING NVL(SUM(A.TOTAL),0)+NVL(SUM(A.TOTAL_ME),0) > 0
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_CLIENTE
                ORDER BY CLIENTE ";
        $query = DB::select($sql);
        return $query;
    }
    public static function lisAccountsReceivable($id_entidad, $id_depto, $id_anho, $id_mes, $id_cliente, $tipo)
    {
        if ($tipo == 'A') {
            $dato = "AND A.ID_TIPOVENTA IN (1, 2, 3, 4) ";
        } else {
            $dato = "AND A.ID_TIPOVENTA NOT IN (1, 2, 3, 4) ";
        }
        $sql = "SELECT
                        A.ID_ENTIDAD,
                        A.ID_DEPTO,
                        A.ID_ANHO,
                        A.ID_CLIENTE,
                        A.ID_VENTA,
                        A.SERIE,
                        A.NUMERO,
                        (SELECT TO_CHAR(X.FECHA,'DD/MM/YYYY') FROM VENTA X WHERE X.ID_VENTA = A.ID_VENTA)AS FECHA,
                        A.ID_MONEDA,
                        A.ID_COMPROBANTE,
                        SUM (A.TOTAL) TOTAL,
                        SUM (A.TOTAL_ME) TOTAL_ME
                FROM VW_SALES_MOV A
                WHERE A.ID_ENTIDAD = " . $id_entidad . "
                AND A.ID_DEPTO = '" . $id_depto . "'
                AND A.ID_ANHO = " . $id_anho . "
                AND A.ID_MES <= " . $id_mes . "
                AND A.ID_CLIENTE = " . $id_cliente . "
                " . $dato . "
                HAVING NVL(SUM(A.TOTAL),0)+NVL(SUM(A.TOTAL_ME),0) > 0
                GROUP BY A.ID_ENTIDAD,A.ID_DEPTO,A.ID_ANHO,A.ID_CLIENTE,A.ID_VENTA,A.SERIE,A.NUMERO,A.ID_MONEDA,A.ID_COMPROBANTE
                ORDER BY FECHA ";
        $query = DB::select($sql);
        return $query;
    }
    public static function getAnticipos($id_entidad, $id_depto, $id_anho, $tipo)
    {
        if ($tipo == 'A') {
            $dato = "AND A.ID_TIPOVENTA IN (1, 2, 3, 4) ";
        } else {
            $dato = "AND A.ID_TIPOVENTA NOT IN (1, 2, 3, 4) ";
        }
        $sql = "SELECT A.ID_ENTIDAD,
                       A.ID_DEPTO,
                       A.ID_ANHO,
                       A.ID_CLIENTE,
                       B.NOM_PERSONA,
                       B.NUM_DOCUMENTO,
                       C.CODIGO,
                       SUM(A.IMPORTE) * DECODE(SIGN(SUM(A.IMPORTE)), 1, -1, 0)
                           AS TOTAL,
                       SUM(A.IMPORTE_ME) * DECODE(SIGN(SUM(A.IMPORTE_ME)), 1, -1, 0)
                           AS TOTAL_ME
                FROM VW_SALES_ADVANCES A
                         LEFT JOIN MOISES.VW_PERSONA_NATURAL B ON A.ID_CLIENTE = B.ID_PERSONA
                         LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO C ON A.ID_CLIENTE = C.ID_PERSONA
                WHERE A.ID_ENTIDAD = ?
                  AND A.ID_DEPTO = ?
                  AND A.ID_ANHO = ?

                GROUP BY A.ID_ENTIDAD, A.ID_DEPTO, A.ID_ANHO, A.ID_CLIENTE, B.NOM_PERSONA, B.NUM_DOCUMENTO, C.CODIGO
                HAVING (SUM(A.IMPORTE) * DECODE(SIGN(SUM(A.IMPORTE)), 1, -1, 0)) <> 0
                ORDER BY TOTAL";
        $query = DB::select($sql, [$id_entidad, $id_depto, $id_anho]);
        return $query;
    }
}
