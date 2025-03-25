<?php


namespace App\Http\Data\Accounting\Setup;


use Illuminate\Support\Facades\DB;

class AccountingEntryData
{

    public static function getAccountingEntryBySale($idSale)
    {
        return DB::table('CONTA_ASIENTO')
            ->select('CONTA_ASIENTO.*')
            ->join('VENTA_DETALLE', "VENTA_DETALLE.ID_VDETALLE", "=", "CONTA_ASIENTO.ID_ORIGEN")
            ->where("VENTA_DETALLE.ID_VENTA", $idSale)
            ->where("CONTA_ASIENTO.ID_TIPOORIGEN", 1)
            ->get();
    }

    public static function updateAccountingEntry($id, $data)
    {
        return DB::table('CONTA_ASIENTO')
            ->where('ID_ASIENTO', $id)
            ->update($data);
    }
    public static function getPlanStudents($id_persona){
        if($id_persona){
            $sql = "SELECT A.ID_SEDE,A.ID_PLAN,A.NOM_ESCUELA,A.ID_NIVEL_ENSENANZA,C.NOMBRE,C.CODIGO
                    FROM DAVID.VW_ALUMNO_PLAN_PROGRAMA A 
                    JOIN DAVID.TIPO_CONTRATO B ON A.ID_TIPO_CONTRATO = B.ID_TIPO_CONTRATO AND A.ID_NIVEL_ENSENANZA = B.ID_NIVEL_ENSENANZA 
                    JOIN DAVID.TIPO_PROGRAMA C ON B.ID_TIPO_PROGRAMA = C.ID_TIPO_PROGRAMA
                    WHERE A.ID_PERSONA = ".$id_persona."  
                    AND A.ESTADO = 1
                    AND A.ID_NIVEL_ENSENANZA IN (1,2)
                    ORDER BY A.ID_NIVEL_ENSENANZA,A.NOMBRE_PLAN DESC ";
            $oQuery = DB::select($sql);
            return $oQuery;
        }else{
            return [];
        }
    }
    public static function thesisPrices($id_entidad,$id_anho,$id_dinamica,$cod_dinamica){
        if($id_dinamica){
            $sql = "SELECT ID_DINAMICA AS ID,NOMBRE, NOMBRE AS GLOSA, IMPORTE as PRECIO,'Soles' as MONEDA,'S/.' AS SIMBOLO
                    FROM CONTA_DINAMICA
                    WHERE ID_ENTIDAD = ".$id_entidad."
                    AND ID_ANHO = ".$id_anho."
                    AND CODIGO = '".$cod_dinamica."' ";
                    // AND ID_DINAMICA = ".$id_dinamica."  ";
            $oQuery = DB::select($sql);
            return $oQuery;
        }else{
            return [];
        }
    }
    public static function accountStatus($id_entidad,$id_anho,$id_persona){
        if($id_persona){
            $sql = "SELECT
                            (CASE WHEN ID_DEPTO = '1' THEN 'SEDE LIMA' WHEN ID_DEPTO = '5' THEN 'SEDE JULIACA' WHEN ID_DEPTO = '6' THEN 'SEDE TARAPOTO' END) AS SEDE,
                            NVL(ABS(SUM(TOTAL)),0) AS TOTAL, SIGN(NVL(SUM(TOTAL),0)) AS SIGNO ,
                            CASE WHEN SUM(TOTAL) < 0 THEN ABS(SUM(TOTAL)) ELSE 0 END AS CREDITO,
                            CASE WHEN SUM(TOTAL) > 0 THEN (SUM(TOTAL)) ELSE 0 END AS DEBITO
                    FROM (
                        SELECT ID_DEPTO, TOTAL
                        FROM VW_SALES_MOV
                        WHERE ID_ENTIDAD = ".$id_entidad." AND ID_ANHO = ".$id_anho." AND ID_CLIENTE = ".$id_persona." AND ID_TIPOVENTA IN (1,2,3)
                        UNION ALL
                        SELECT
                                ID_DEPTO,SUM(IMPORTE)*DECODE(SIGN(SUM(IMPORTE)),1,-1,0) AS TOTAL
                        FROM VW_SALES_ADVANCES
                        WHERE ID_ENTIDAD = ".$id_entidad." AND ID_ANHO = ".$id_anho." AND ID_CLIENTE = ".$id_persona." GROUP BY ID_DEPTO
                    )GROUP BY ID_DEPTO  ";
            $oQuery = DB::select($sql);
            return $oQuery;
        }else{
            return [];
        }
    }
}