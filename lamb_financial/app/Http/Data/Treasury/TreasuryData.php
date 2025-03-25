<?php
namespace App\Http\Data\Treasury;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class TreasuryData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listBankAccounts($id_entidad,$id_depto){                
        $query = "SELECT 
                            B.ID_CTABANCARIA,A.NOMBRE AS BANCO,B.NOMBRE,B.CUENTA_CORRIENTE,A.SIGLA,B.ID_MONEDA,B.ID_CUENTAAASI,B.ID_TIPOCTACTE,
                            PKG_ACCOUNTING.FC_MONEDA(B.ID_MONEDA) AS NOMBRE_MONEDA, ID_DEPTO_OPER
                FROM CAJA_ENTIDAD_FINANCIERA A, CAJA_CUENTA_BANCARIA B
                WHERE A.ID_BANCO = B.ID_BANCO
                AND B.ID_ENTIDAD = ".$id_entidad."
                AND B.ID_DEPTO = '".$id_depto."'
                AND B.ESTADO = '1'
                ORDER BY B.ORDEN,A.ID_BANCO,B.ID_CTABANCARIA ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listBankAccountsBanco($id_banco, $id_entidad,$id_depto){                
        $query = "SELECT 
                            B.ID_CTABANCARIA,A.NOMBRE AS BANCO,B.NOMBRE,B.CUENTA_CORRIENTE,A.SIGLA,B.ID_MONEDA,B.ID_CUENTAAASI,B.ID_TIPOCTACTE,
                            PKG_ACCOUNTING.FC_MONEDA(B.ID_MONEDA) AS NOMBRE_MONEDA, ID_DEPTO_OPER
                FROM CAJA_ENTIDAD_FINANCIERA A, CAJA_CUENTA_BANCARIA B
                WHERE A.ID_BANCO = B.ID_BANCO
                AND B.ID_ENTIDAD = ".$id_entidad."
                AND B.ID_DEPTO = '".$id_depto."'
                AND A.ID_BANCO = '".$id_banco."'
                AND B.ESTADO = '1'
                ORDER BY B.ORDEN,A.ID_BANCO,B.ID_CTABANCARIA ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listTypeTarjetas(){                
        $query = "SELECT ID_TIPOTARJETA, NOMBRE, ESTADO FROM CAJA_TIPOTARJETA ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showBankAccounts($id_ctabancaria){
        $query = "SELECT 
                        A.ID_CTABANCARIA,
                        A.ID_BANCO,
                        A.ID_ENTIDAD,
                        A.ID_DEPTO,
                        A.ID_MONEDA,
                        A.ID_TIPOPLAN,
                        A.ID_CUENTAEMPRESARIAL,
                        PKG_ACCOUNTING.FC_CUENTA_EMPRESARIAL(A.ID_CUENTAEMPRESARIAL) AS NOMBRE_CUENTAEMPRESARIAL,
                        A.ID_CUENTAAASI,
                        FC_CUENTA_DENOMINACIONAL(A.ID_CUENTAAASI) AS NOMBRE_CUENTAAASI,
                        A.ID_RESTRICCION,
                        A.ID_TIPOCTACTE,
                        A.NOMBRE,
                        A.CUENTA_CORRIENTE,
                        A.ESTADO,
                        A.ID_DEPTO_OPER,
                        B.NOMBRE AS BANCO
                FROM CAJA_CUENTA_BANCARIA A, CAJA_ENTIDAD_FINANCIERA B
                WHERE A.ID_BANCO = B.ID_BANCO
                AND A.ID_CTABANCARIA = ".$id_ctabancaria." ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function showAccountsByBank($id_entidad, $id_depto, $id_banco){
        $query = "SELECT 
                        A.ID_CTABANCARIA,
                        A.ID_BANCO,
                        A.ID_ENTIDAD,
                        A.ID_DEPTO,
                        A.ID_MONEDA,
                        A.ID_TIPOPLAN,
                        A.ID_CUENTAAASI,
                        A.ID_TIPOCTACTE,
                        A.NOMBRE,
                        A.CUENTA_CORRIENTE,
                        A.ESTADO,
                        B.NOMBRE AS BANCO,
                        B.SIGLA AS SIGLA
                FROM CAJA_CUENTA_BANCARIA A, CAJA_ENTIDAD_FINANCIERA B
                WHERE A.ID_BANCO = B.ID_BANCO
                AND A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = ".$id_depto."
                AND A.ID_BANCO = ".$id_banco."
                AND A.ESTADO = '1' ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function addBankAccounts($id_entidad,$id_depto,$id_banco,$id_moneda,
    $id_cuentaempresarial,$id_tipoplan,$id_cuentaaasi,$id_restriccion,
    $id_ctacte,$nombre,$cuenta_corriente,$id_depto_oper, $estado){
        DB::table('CAJA_CUENTA_BANCARIA')->insert(
                        array('ID_BANCO' => $id_banco,
                            'ID_ENTIDAD' => $id_entidad,
                            'ID_DEPTO' => $id_depto,
                            'ID_MONEDA' => $id_moneda,
                            'ID_CUENTAEMPRESARIAL' => $id_cuentaempresarial,
                            'ID_TIPOPLAN' => $id_tipoplan,
                            'ID_CUENTAAASI' =>$id_cuentaaasi,
                            'ID_RESTRICCION' => $id_restriccion,
                            'ID_TIPOCTACTE' => $id_ctacte,
                            'NOMBRE' =>$nombre,
                            'CUENTA_CORRIENTE' =>$cuenta_corriente,
                            'ID_DEPTO_OPER' =>$id_depto_oper,
                            'ESTADO' =>$estado)
                    );
        $query = "SELECT 
                        MAX(ID_CTABANCARIA) ID_CTABANCARIA
                FROM CAJA_CUENTA_BANCARIA ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item){
            $id_ctabancaria = $item->id_ctabancaria;                
        }
        $sql = TreasuryData::showBankAccounts($id_ctabancaria);
        return $sql;
    }

    public static function updateBankAccounts($id_ctabancaria,$id_banco,$id_moneda,
    $id_cuentaempresarial,$id_cuentaaasi,$nombre,$cuenta_corriente,
    $id_tipoctacte,$id_depto_oper, $estado){
        DB::table('CAJA_CUENTA_BANCARIA')
            ->where('ID_CTABANCARIA', $id_ctabancaria)
            ->update([
                'ID_BANCO' => $id_banco,
                'ID_MONEDA' => $id_moneda,
                'ID_CUENTAEMPRESARIAL' => $id_cuentaempresarial,
                'ID_CUENTAAASI' => $id_cuentaaasi,
                'NOMBRE' => $nombre,
                'CUENTA_CORRIENTE' => $cuenta_corriente,
                'ID_TIPOCTACTE' => $id_tipoctacte,
                'ID_DEPTO_OPER' =>$id_depto_oper,
                'ESTADO' => $estado
            ]);
        $sql = TreasuryData::showBankAccounts($id_ctabancaria);
        return $sql;
    }
    public static function deleteBankAccounts($id_ctabancaria){
        DB::table('CAJA_CUENTA_BANCARIA')->where('ID_CTABANCARIA', '=', $id_ctabancaria)->delete();
    }
    public static function listCheckbooks($id_ctabancaria,$id_anho,$id_mes){                
                            // ,(
                            //     SELECT M.NUMERO FROM CAJA_VOUCHER M WHERE M.ID_VOUCHER = 
                            //                             (SELECT MAX(X.ID_VOUCHER) FROM CAJA_CHEQUERA_VOUCHER X WHERE X.ID_CHEQUERA = A.ID_CHEQUERA)

                            // ) AS NUMERO_VOUCHER
        $query = "SELECT
                            A.ID_CHEQUERA,A.ID_CTABANCARIA,A.ID_ANHO,A.ID_MES,C.SIGLA AS BANCO,B.NOMBRE,A.NUMERO,A.IMPORTE,A.FECHA,A.DETALLE,A.BENEFICIARIO,
                            (SELECT MAX(X.ID_VOUCHER) FROM CAJA_CHEQUERA_VOUCHER X WHERE X.ID_CHEQUERA = A.ID_CHEQUERA) AS ID_VOUCHER
                            ,(
                                SELECT M.ID_TIPOASIENTO || '-' || M.NUMERO || '-' || TO_CHAR(M.FECHA, 'DD/MM/YYY') FROM CONTA_VOUCHER M WHERE M.ID_VOUCHER IN
                                                        (SELECT MAX(XA.ID_VOUCHER) FROM CAJA_CHEQUERA_VOUCHER XA WHERE XA.ID_CHEQUERA = A.ID_CHEQUERA)

                            ) AS NUMERO_VOUCHER
                FROM CAJA_CHEQUERA A, CAJA_CUENTA_BANCARIA B, CAJA_ENTIDAD_FINANCIERA C
                WHERE A.ID_CTABANCARIA = B.ID_CTABANCARIA
                AND B.ID_BANCO = C.ID_BANCO
                AND A.ID_CTABANCARIA = ".$id_ctabancaria."
                AND A.ID_ANHO = ".$id_anho."
                AND A.ID_MES = ".$id_mes." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }

    public static function showOnlyCheckbook($id_chequera){
        $query = "SELECT A.ID_CHEQUERA,A.ID_CTABANCARIA,A.ID_ANHO,A.ID_MES,A.NUMERO,A.IMPORTE, TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,A.DETALLE,A.BENEFICIARIO
                FROM CAJA_CHEQUERA A
                AND A.ID_CHEQUERA = $id_chequera";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function showCheckbooks($id_chequera){
        $query = "SELECT
                            A.ID_CHEQUERA,A.ID_CTABANCARIA,A.ID_ANHO,A.ID_MES,C.SIGLA AS BANCO,B.NOMBRE,A.NUMERO,A.IMPORTE,A.FECHA,A.DETALLE,A.BENEFICIARIO,
                            (SELECT X.ID_VOUCHER FROM CAJA_CHEQUERA_VOUCHER X WHERE X.ID_CHEQUERA = A.ID_CHEQUERA) AS ID_VOUCHER
                FROM CAJA_CHEQUERA A, CAJA_CUENTA_BANCARIA B, CAJA_ENTIDAD_FINANCIERA C
                WHERE A.ID_CTABANCARIA = B.ID_CTABANCARIA
                AND B.ID_BANCO = C.ID_BANCO
                AND A.ID_CHEQUERA = ".$id_chequera."  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showCheckbooksUserAsigned($id_chequera){
        $query = "SELECT A.ID_CHEQUERA 
                    FROM CAJA_CHEQUERA_VOUCHER A, CAJA_CHEQUERA_PERSONA B
                    WHERE A.ID_CVOUCHER = B.ID_CVOUCHER
                    AND A.ID_CHEQUERA =  ".$id_chequera."  ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getChequeExiste($id_ctabancaria, $numero) {
        //
        return DB::table('CAJA_CHEQUERA')->where('ID_CTABANCARIA', '=', $id_ctabancaria)
                 ->where('NUMERO', '=',$numero)
                 ->exists();
    }

    public static function addCheckbooks($id_ctabancaria,$id_anho,$id_mes,$id_voucher,$numero,$importe,$fecha,$detalle,$beneficiario){
        DB::table('CAJA_CHEQUERA')->insert(
                        array('ID_CTABANCARIA' => $id_ctabancaria,
                            'ID_ANHO' => $id_anho,
                            'ID_MES' => $id_mes,
                            'NUMERO' => $numero,
                            'IMPORTE' => $importe,
                            'FECHA' => $fecha,
                            'DETALLE' => $detalle,
                            'BENEFICIARIO' =>$beneficiario)
                    );
        $query = "SELECT 
                        MAX(ID_CHEQUERA) ID_CHEQUERA
                FROM CAJA_CHEQUERA ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $key => $item){
            $id_chequera = $item->id_chequera;                
        }
        if($id_voucher != null){
            DB::table('CAJA_CHEQUERA_VOUCHER')->insert(
                            array('ID_CHEQUERA' => $id_chequera,
                                'ID_VOUCHER' => $id_voucher,
                                'ACTIVO' => '1')
                        );
        }
        $sql = TreasuryData::showCheckbooks($id_chequera);
        return $sql;
    }
    public static function updateCheckbooks($id_chequera,$id_ctabancaria,$id_voucher,$numero,$importe,$fecha,$detalle,$beneficiario){
        DB::table('CAJA_CHEQUERA')
            ->where('ID_CHEQUERA', $id_chequera)
            ->update([
                'ID_CTABANCARIA' => $id_ctabancaria,
                'NUMERO' => $numero,
                'IMPORTE' => $importe,
                'FECHA' => $fecha,
                'DETALLE' => $detalle,
                'BENEFICIARIO' =>$beneficiario
            ]);
        
        DB::table('CAJA_CHEQUERA_VOUCHER')
            ->where('ID_CHEQUERA', $id_chequera)
            ->update([
                'ID_VOUCHER' => $id_voucher
            ]);
        
        $sql = TreasuryData::showCheckbooks($id_chequera);
        return $sql;
    }
    public static function deleteCheckbooks($id_chequera){
        DB::table('CAJA_CHEQUERA')->where('ID_CHEQUERA', '=', $id_chequera)->delete();
    }
    public static function deleteCheckbooksVouchers($id_chequera){
        DB::table('CAJA_CHEQUERA_VOUCHER')->where('ID_CHEQUERA', '=', $id_chequera)->delete();
    }
/*
    public static function deleteCheckbooksPersona($id_chequera){
        DB::table('CAJA_CHEQUERA_PERSONA')->where('ID_CHEQUERA', '=', $id_chequera)->delete();
    }
*/
    public static function listMyBankAccounts($id_voucher){                
        $query = "SELECT 
                    DISTINCT A.ID_CTABANCARIA,A.NOMBRE,A.CUENTA_CORRIENTE
                    FROM CAJA_CUENTA_BANCARIA A, CAJA_CHEQUERA B, CAJA_CHEQUERA_VOUCHER C
                    WHERE A.ID_CTABANCARIA = B.ID_CTABANCARIA
                    AND B.ID_CHEQUERA = C.ID_CHEQUERA
                    AND C.ID_VOUCHER = ".$id_voucher." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listMyCheckbooks($id_voucher){                
        $query = "SELECT A.ID_CTABANCARIA,
                    B.ID_CHEQUERA,A.NOMBRE,A.CUENTA_CORRIENTE,B.NUMERO,B.IMPORTE,TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA
                    FROM CAJA_CUENTA_BANCARIA A, CAJA_CHEQUERA B, CAJA_CHEQUERA_VOUCHER C
                    WHERE A.ID_CTABANCARIA = B.ID_CTABANCARIA
                    AND B.ID_CHEQUERA = C.ID_CHEQUERA
                    AND C.ID_VOUCHER = ".$id_voucher." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }

    public static function actionsByModuleUser($id_persona, $id_entidad, $url){
        $query = "SELECT a.NOMBRE ,a.CLAVE,
        (case when exists(select * from LAMB_ROL_MODULO_ACCION e left join LAMB_USUARIO_ROL u on (e.ID_ROL = u.ID_ROL)
        where u.ID_PERSONA=$id_persona and u.ID_ENTIDAD=$id_entidad AND e.ID_MODULO=a.ID_MODULO AND e.ID_ACCION= a.ID_ACCION) THEN '1' else '0' end) as can_see FROM LAMB_ACCION a left join LAMB_MODULO b ON (a.ID_MODULO = b.ID_MODULO)
        where b.URL = '$url'";
        return collect(DB::select($query))->map(function ($item, $key) {
            $item->can_see = boolval($item->can_see);
            return $item;
        })->pluck('can_see','clave');

    }


    public static function Retentions($id_entidad, $id_voucher){
        $query = "SELECT
                   A.ID_RETENCION,
                   A.ID_VOUCHER,
                   PKG_CAJA.FC_NOMBRE_CTA_BANCARIA(A.ID_CTABANCARIA) CUENTA_BANCARIA,
                   PKG_ACCOUNTING.FC_CUENTA_ASIENTO(A.ID_ENTIDAD,B.ID_TIPOORIGEN, A.ID_RETENCION, A.ID_VOUCHER,'D') CUENTA,
                   PKG_ACCOUNTING.FC_DEPARTAMENTO_ASIENTO(A.ID_ENTIDAD,B.ID_TIPOORIGEN,A.ID_RETENCION, A.ID_VOUCHER,'D') NIVEL,
                   C.NOMBRE,
                   E.ID_RUC,
                   A.NUMERO,
                   A.NRO_RETENCION,
                   TO_CHAR(A.FECHA_EMISION,'DD/MM/YYYY') AS FECHA_EMISION,
                   B.IMPORTE_RET AS IMPORTE,
                   0 AS IMPORTE_ME,
                   F.SIMBOLO AS MONEDA,
                   (A.SERIE||' - '||A.NRO_RETENCION) as NRO_RETENCION_REF,
                   B.DETALLE AS DETALLE_REF
            from CAJA_RETENCION A
                LEFT JOIN CAJA_RETENCION_COMPRA B
                    ON A.ID_RETENCION = B.ID_RETENCION
                LEFT JOIN MOISES.PERSONA C
                    ON A.ID_PROVEEDOR = C.ID_PERSONA
                LEFT JOIN COMPRA D
                    ON B.ID_COMPRA = D.ID_COMPRA
                LEFT JOIN MOISES.VW_PERSONA_JURIDICA E
                    ON A.ID_PROVEEDOR = E.ID_PERSONA
                LEFT JOIN CONTA_MONEDA F
                    ON A.ID_MONEDA = F.ID_MONEDA
            WHERE A.ID_ENTIDAD = $id_entidad
              AND A.ESTADO = '1'
              AND A.ID_VOUCHER = $id_voucher
            ORDER BY A.NUMERO , C.NOMBRE";

        return DB::select($query); 
    }

    public static function listColectionControl($request, $id_entidad, $id_depto){
        $fecha = $request->fecha;
        $valor = explode("-", $fecha);
        $id_anho = $valor[0];
        $id_mes = $valor[1];
        $query = "SELECT
        ID_MES,
        ID_FACULTAD,
        NVL(FACULTAD,'TOTAL GENERAL') AS FACULTAD,
        ID_ESCUELA,
        ESCUELA,
        SUM(IMPORTE) AS IMPORTE
        FROM (
                SELECT A.ID_MES,
                NVL(DAVID.FT_FACULTAD_ALUMNO_ID(A.ID_CLIENTE),0) AS ID_FACULTAD,
                NVL(DAVID.FT_FACULTAD_ALUMNO(A.ID_CLIENTE),'OTROS') AS FACULTAD,
                NVL(DAVID.FT_ESCUELA_ALUMNO_ID(A.ID_CLIENTE),0) AS ID_ESCUELA,
                NVL(DAVID.FT_ESCUELA_ALUMNO(A.ID_CLIENTE),'OTROS') AS ESCUELA,
                A.ID_CLIENTE,B.CODIGO,A.FECHA,A.IMPORTE
                FROM CAJA_DEPOSITO A JOIN MOISES.PERSONA_NATURAL_ALUMNO B ON A.ID_CLIENTE = B.ID_PERSONA
                WHERE A.ID_ENTIDAD = ".$id_entidad."
                AND A.ID_DEPTO = '".$id_depto."'
                AND A.ID_ANHO = ".$id_anho."
                AND A.ID_DEPOSITO NOT IN (SELECT ID_DEPOSITO FROM CAJA_DEPOSITO_DETALLE WHERE ID_VENTA IN (SELECT ID_VENTA FROM VENTA WHERE ID_TIPOVENTA IN(5,6)) )
                AND A.ESTADO = '1'
                AND A.ID_MES = ".$id_mes."
                ORDER BY A.ID_MES,A.FECHA
        )
        GROUP BY ID_MES,ROLLUP(FACULTAD,ID_FACULTAD,ID_ESCUELA,ESCUELA)
        HAVING (CASE WHEN ID_FACULTAD IS NOT NULL AND ID_ESCUELA IS NULL AND ESCUELA IS NULL THEN 'TF' ELSE ESCUELA END) IS NOT NULL
        ORDER BY ID_MES,ID_FACULTAD,ESCUELA";
                $oQuery = DB::select($query);
                return $oQuery;
    }
    public static function listColectionControlDetalle($request, $id_entidad, $id_depto){
        // dd($id_entidad, $id_depto);
        $fecha = $request->fecha;
        $id_escuela = $request->id_escuela;
        $valor = explode("-", $fecha);
        $id_anho = $valor[0];
        $id_mes = $valor[1];
        $query = "SELECT A.ID_MES,
        NVL(DAVID.FT_FACULTAD_ALUMNO_ID(A.ID_CLIENTE),0) AS ID_FACULTAD,
        NVL(DAVID.FT_FACULTAD_ALUMNO(A.ID_CLIENTE),'OTROS') AS FACULTAD,
        NVL(DAVID.FT_ESCUELA_ALUMNO_ID(A.ID_CLIENTE),0) AS ID_ESCUELA,
        NVL(DAVID.FT_ESCUELA_ALUMNO(A.ID_CLIENTE),'OTROS') AS ESCUELA,
        B.NOM_PERSONA,
        A.ID_CLIENTE,B.CODIGO,A.FECHA,A.IMPORTE
        FROM CAJA_DEPOSITO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_CLIENTE = B.ID_PERSONA
        WHERE A.ID_ENTIDAD = ".$id_entidad."
        AND A.ID_DEPTO = '".$id_depto."'
        AND A.ID_ANHO = ".$id_anho."
        AND A.ID_DEPOSITO NOT IN (SELECT ID_DEPOSITO FROM CAJA_DEPOSITO_DETALLE WHERE ID_VENTA IN (SELECT ID_VENTA FROM VENTA WHERE ID_TIPOVENTA IN(5,6)) )
        AND A.ESTADO = '1'
        AND A.ID_MES = ".$id_mes."
        AND NVL(DAVID.FT_ESCUELA_ALUMNO_ID(A.ID_CLIENTE),0) = ".$id_escuela."
        ORDER BY A.ID_MES,A.FECHA";
                $oQuery = DB::select($query);
                return $oQuery;
    }
    public static function generarDatosGrafico($request, $id_user, $id_entidad, $id_depto) {
        $fecha = $request->fecha;
        $valor = explode("-", $fecha);
        $id_anho = $valor[0];
        $id_mes = $valor[1];
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '';
        }
        // dd($id_user, $id_entidad, $id_depto, $id_anho, $id_mes);
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SALES_FINANCES.SP_RECAUDACION(
                                :P_ID_ENTIDAD,
                                :P_ID_DEPTO,
                                :P_ID_ANHO,
                                :P_ID_MES,
                                :P_ID_USER
                              ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
        }

    public static function graficoColectionControl($request, $id_entidad, $id_depto){
    
        $id_escuela = $request->id_escuela;
        $fecha = $request->fecha;
        $valor = explode("-", $fecha);
        $id_anho = $valor[0];
        $id_mes = $valor[1];
        // dd($id_escuela );
        $query = "SELECT
        A.ID_MES,A.DIA,
        B.NOM_ESCUELA,
        B.ID_ESCUELA,
        SUM(A.DEUDA)*-1 AS DEUDA,
        SUM(A.DEPOSITO) AS DEPOSITO
        FROM REP_RECAUDACION A JOIN (
                                      SELECT DISTINCT A.ID_CLIENTE,X.ID_ESCUELA,X.ID_FACULTAD,X.NOM_ESCUELA,X.NOM_FACULTAD
                                      FROM REP_RECAUDACION A 
                                      LEFT JOIN (SELECT ID_PERSONA,ID_ESCUELA,ID_FACULTAD,NOM_ESCUELA,NOM_FACULTAD  
                                                FROM DAVID.VW_ALUMNO_PLAN_PROGRAMA VPP 
                                                WHERE  ID_PLAN_PROGRAMA = (SELECT  ID_PLAN_PROGRAMA
                                                                        FROM (SELECT ID_PLAN_PROGRAMA,ID_PERSONA FROM DAVID.VW_ALUMNO_PLAN_PROGRAMA WHERE ESTADO=1 ORDER BY ID_FACULTAD)
                                                                        WHERE ID_PERSONA=VPP.ID_PERSONA AND ROWNUM=1)) X 
                                                ON X.ID_PERSONA = A.ID_CLIENTE
                                      --WHERE  X.ID_PERSONA = 20145
        ) B ON A.ID_CLIENTE = B.ID_CLIENTE
            WHERE B.ID_ESCUELA = ".$id_escuela."
            AND A.ID_ENTIDAD = ".$id_entidad."
            AND A.ID_DEPTO = '".$id_depto."'
            AND A.ID_ANHO = ".$id_anho."
            AND A.ID_MES = ".$id_mes."
            GROUP BY A.ID_MES,A.DIA,B.NOM_ESCUELA,B.ID_ESCUELA
            ORDER BY A.DIA";
                $oQuery = DB::select($query);
                return $oQuery;
    }
    public static function graficoColectionControlDetalle($request, $id_entidad, $id_depto){
    
        $id_escuela = $request->id_escuela;
        $fecha = $request->fecha;
        $valor = explode("-", $fecha);
        $id_anho = $valor[0];
        $id_mes = $valor[1];
        // dd($id_escuela );
        $query = "SELECT
        A.ID_MES,A.DIA,
        B.NOM_ESCUELA,
        B.ID_ESCUELA,
        A.ID_CLIENTE,
        DAVID.FT_CODIGO_UNIV(A.ID_CLIENTE) AS CODIGO,
        DAVID.NOMBRE_PERSONA(A.ID_CLIENTE) AS ALUMNO,
        SUM(A.DEUDA)*-1 AS DEUDA,
        SUM(A.DEPOSITO) AS DEPOSITO
        FROM REP_RECAUDACION A JOIN (
                                      SELECT DISTINCT A.ID_CLIENTE,X.ID_ESCUELA,X.ID_FACULTAD,X.NOM_ESCUELA,X.NOM_FACULTAD
                                      FROM REP_RECAUDACION A
                                      LEFT JOIN (SELECT ID_PERSONA,ID_ESCUELA,ID_FACULTAD,NOM_ESCUELA,NOM_FACULTAD
                                                FROM DAVID.VW_ALUMNO_PLAN_PROGRAMA VPP
                                                WHERE  ID_PLAN_PROGRAMA = (SELECT  ID_PLAN_PROGRAMA
                                                                        FROM (SELECT ID_PLAN_PROGRAMA,ID_PERSONA FROM DAVID.VW_ALUMNO_PLAN_PROGRAMA WHERE ESTADO=1 ORDER BY ID_FACULTAD)
                                                                        WHERE ID_PERSONA=VPP.ID_PERSONA AND ROWNUM=1)) X
                                                ON X.ID_PERSONA = A.ID_CLIENTE
                                      --WHERE  X.ID_PERSONA = 20145
        ) B ON A.ID_CLIENTE = B.ID_CLIENTE
        WHERE B.ID_ESCUELA = ".$id_escuela."
        AND A.ID_ENTIDAD = ".$id_entidad."
        AND A.ID_DEPTO = '".$id_depto."'
        AND A.ID_ANHO = ".$id_anho."
        AND A.ID_MES = ".$id_mes."
        GROUP BY A.ID_MES,A.DIA,A.ID_CLIENTE,B.NOM_ESCUELA,B.ID_ESCUELA
        ORDER BY A.DIA";
                $oQuery = DB::select($query);
                return $oQuery;
    }
}

