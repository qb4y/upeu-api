<?php
namespace App\Http\Data\Accounting\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
use Exception;

class SeatCWFJData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function showVoucher($id_voucher,$id_depto,$id_anho,$id_tipovoucher){   
        $id_compra = "001-".$id_anho;     
        if($id_tipovoucher == 1){//VENTAS
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       DECODE(ID_NIVEL,'1','RV','2','RVP','3','RVI','4','RVC','5','RVJ','6','RVT','7','RVL') AS ID_TIPOASIENTO,
                       DECODE('".$id_depto."','1','18','2','37','3','38','4','39','5','40','6','41','7','42') as CODIGO
                FROM ARON.VENTAS_VOUCHER@DBL_JUL
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_VENTA = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }elseif($id_tipovoucher == 2){//COMPRAS
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       DECODE(ID_NIVEL,'1','RC','2','RCP','3','RCI','4','RCC','5','RCJ','6','RCT','7','RCL') AS ID_TIPOASIENTO,
                       DECODE('".$id_depto."','1','17','2','31','3','32','4','33','5','34','6','35','7','36') as CODIGO
                FROM ARON.COMPRAS_VOUCHER
                WHERE VOUCHER = '".$id_voucher."'
                AND ID_COMPRAS = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }elseif($id_tipovoucher == 3){//CHEQUE
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       DECODE(ID_NIVEL,'1','MB','2','MBP','3','MBI','4','MBC','5','MBJ','6','MBT','7','MBL') AS ID_TIPOASIENTO,
                       DECODE('".$id_depto."','1','15','2','19','3','21','4','23','5','25','6','27','7','29') as CODIGO
                FROM ARON.CHEQUE_VOUCHER
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_CHEQUE = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }elseif($id_tipovoucher == 4){//TLC
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       DECODE(ID_NIVEL,'1','MB','2','MBP','3','MBI','4','MBC','5','MBJ','6','MBT','7','MBL') AS ID_TIPOASIENTO,
                       DECODE('".$id_depto."','1','15','2','19','3','21','4','23','5','25','6','27','7','29') as CODIGO
                FROM ARON.TLECRD_VOUCHER
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_TLECRD = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }elseif($id_tipovoucher == 5){//INGRESOS
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       DECODE(ID_NIVEL,'1','MB','2','MBP','3','MBI','4','MBC','5','MBJ','6','MBT','7','MBL') AS ID_TIPOASIENTO,
                       DECODE('".$id_depto."','1','15','2','19','3','21','4','23','5','25','6','27','7','29') as CODIGO
                FROM ARON.EGRESOS_VOUCHER
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_COMPRAS = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }elseif($id_tipovoucher == 7){//VENTAS TRANSFER - CU
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       DECODE(ID_NIVEL,'1','MB','2','MBP','3','MBI','4','MBC','5','MBJ','6','MBT','7','MBL') AS ID_TIPOASIENTO,
                       DECODE('".$id_depto."','1','15','2','19','3','21','4','23','5','25','6','27','7','29') as CODIGO
                FROM ARON.EGRESOS_VOUCHER
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_COMPRAS = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }elseif($id_tipovoucher == 14){//VENTAS CU
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       'RVC' AS ID_TIPOASIENTO,
                       '39' as CODIGO
                FROM ARON.VENTAS_VOUCHER
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_VENTA = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }elseif($id_tipovoucher == 15){//VENTAS IMP
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       'RVI' AS ID_TIPOASIENTO,
                       '38' as CODIGO
                FROM ARON.VENTAS_VOUCHER
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_VENTA = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }elseif($id_tipovoucher == 16){//VENTAS OPT
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       'RVI' AS ID_TIPOASIENTO,
                       '38' as CODIGO
                FROM ARON.MKT_VOUCHER
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_VENTA = '".$id_compra."' 
                AND ID_NIVEL like '".$id_depto."%'  ";
        }elseif($id_tipovoucher == 17){//VENTAS TPP
                $query = "SELECT
                       VOUCHER,
                       TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                       TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                       TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                       'RVT' AS ID_TIPOASIENTO,
                       '41' as CODIGO
                FROM ARON.VENTAS_VOUCHER
                WHERE VOUCHER = '".$id_voucher."' 
                AND ID_VENTA = '".$id_compra."' 
                AND ID_NIVEL = '".$id_depto."'  ";
        }     
        



        elseif($id_tipovoucher == 18){//VENTAS CAT
                $query = "SELECT
                VOUCHER,
                TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                DECODE(ID_NIVEL,'1','RV','2','RVP','3','RVI','4','RVC','5','RVJ','6','RVT','7','RVL') AS ID_TIPOASIENTO,
                DECODE('7','1','18','2','37','3','38','4','39','5','40','6','41','7','42') as CODIGO
         FROM ARON.VENTAS_VOUCHER@DBL_JUL
         WHERE VOUCHER = '".$id_voucher."' 
         AND ID_VENTA = '".$id_compra."' 
         AND ID_NIVEL = '7'  ";
        }elseif($id_tipovoucher == 19){//COMPRAS CAT
                $query = "SELECT
                VOUCHER,
                TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                DECODE(ID_NIVEL,'1','RC','2','RCP','3','RCI','4','RCC','5','RCJ','6','RCT','7','RCL') AS ID_TIPOASIENTO,
                DECODE('7','1','17','2','31','3','32','4','33','5','34','6','35','7','36') as CODIGO
         FROM ARON.COMPRAS_VOUCHER@DBL_JUL
         WHERE VOUCHER = '".$id_voucher."'
         AND ID_COMPRAS = '".$id_compra."' 
         AND ID_NIVEL = '7'  ";
        }elseif($id_tipovoucher == 20){//TLC CAT
                $query = "SELECT
                VOUCHER,
                TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                DECODE(ID_NIVEL,'1','MB','2','MBP','3','MBI','4','MBC','5','MBJ','6','MBT','7','MBL') AS ID_TIPOASIENTO,
                DECODE('7','1','15','2','19','3','21','4','23','5','25','6','27','7','29') as CODIGO
         FROM ARON.TLECRD_VOUCHER@DBL_JUL
         WHERE VOUCHER = '".$id_voucher."' 
         AND ID_TLECRD = '".$id_compra."' 
         AND ID_NIVEL = '7'  ";
        }elseif($id_tipovoucher == 21){//CHEQUES CAT
                $query = "SELECT
                VOUCHER,
                TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                DECODE(ID_NIVEL,'1','MB','2','MBP','3','MBI','4','MBC','5','MBJ','6','MBT','7','MBL') AS ID_TIPOASIENTO,
                DECODE('7','1','15','2','19','3','21','4','23','5','25','6','27','7','29') as CODIGO
         FROM ARON.CHEQUE_VOUCHER@DBL_JUL
         WHERE VOUCHER = '".$id_voucher."' 
         AND ID_CHEQUE = '".$id_compra."' 
         AND ID_NIVEL = '7'  ";
        }elseif($id_tipovoucher == 20){//INGRESOS CAT
                $query = "SELECT
                VOUCHER,
                TO_CHAR(FECHA,'DD/MM/YYYY') AS FECHA,
                TO_CHAR(FECHA,'DDMMYYYY') AS FECHA_AASI,
                TO_CHAR(FECHA,'MMYYYY') AS PERIODO,
                DECODE(ID_NIVEL,'1','MB','2','MBP','3','MBI','4','MBC','5','MBJ','6','MBT','7','MBL') AS ID_TIPOASIENTO,
                DECODE('7','1','15','2','19','3','21','4','23','5','25','6','27','7','29') as CODIGO
         FROM ARON.EGRESOS_VOUCHER@DBL_JUL
         WHERE VOUCHER = '".$id_voucher."' 
         AND ID_COMPRAS = '".$id_compra."' 
         AND ID_NIVEL = '7'  ";
        }     
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }
    //Ingresos
    public static function listVoucherCWAasinetIncome($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        voucher, 
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.egresos_voucher 
                WHERE id_compras = '".$id_compra."' 
                AND id_nivel = '".$id_depto."' 
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '05'
                AND ID_NIVEL = '".$id_depto."'
                AND TIPO = 'MB'
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetIncome($id_nivel,$id_anho,$voucher){
        $mai_id = "001-".$id_anho;
        $query = "--QUERY 1
                SELECT         
                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_compras),' ') cuenta,
                        --nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_compras),' ') cta_cte,      	    
                        decode(substr(a.id_nivel,1,1),'3',nvl(ARON.FC_CTA_CTE_IMP(a.id_compras,a.id_mov_efec),ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_compras)),nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_compras),' ')) cta_cte,
                        '10' fondo,				
                        nvl(decode(substr(a.id_nivel,1,1),'1',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'2',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'3',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'4',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','41010101',ARON.equiv_nivel('4',a.id_compras)),'5',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'6',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-11','61010101',ARON.equiv_nivel(a.id_nivel,a.id_compras))),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_compras),' ')  restriccion,     
                        nvl(a.importe,0)*decode(a.dc,'D',1,-1) valor, 
						nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1) imp,
                        NVL((select n.serie||'-'||n.NUMDOC from ARON.IMP_REGVENTAS n join ARON.IMP_INGVNT b
                        on n.ID_MOV_VNT=b.ID_MOV_VNT where n.ID_VENTA=b.ID_VENTA and n.ID_VENTA=a.ID_COMPRAS  
                        and b.ID_MOV_ING=a.ID_MOV_EFEC ),(select n.serie||'-'||n.NUMDOC from ARON.mkt_REGVENTAS n join ARON.IMP_INGVNT b
                        on n.ID_MOV_VNT=b.ID_MOV_VNT where n.ID_VENTA=b.ID_VENTA and n.ID_VENTA=a.ID_COMPRAS  
                        and b.ID_MOV_ING=a.ID_MOV_EFEC ))||' '||decode(a.tipo,'IC',decode(a.tipo2,'XD',substr(a.glosa,1,50),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')) descripcion,
                        a.id_mov_efec memo,
                        '1.1' ORDEN
                FROM ARON.egresos_detalle a, ARON.cont_plan b
                where a.id_compras = b.id_cont 
                and a.id_cuenta_egre = b.id_cuenta 
                and a.id_compras = '".$mai_id."'
                and a.voucher = '".$voucher."' 
                and b.id_cont = '".$mai_id."'
                and a.id_nivel like '".$id_nivel."%' 
                and a.tipo in ('IC','ID')
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_compras),' ') cuenta,
                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_egre,id_compras),' ') cta_cte, 
                        '10' fondo,		
                        nvl(ARON.equiv_nivel('".$id_nivel."',id_compras),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,id_compras),' ')  restriccion,
                        nvl(sum(importe),0)*decode(dc,'C',1,-1) valor, 
						nvl(sum(otro_imp),0)*decode(dc,'C',1,-1) imp,
                        'Total Caja Operativa' descripcion,
                        '' memo,
                        '1' ORDEN
                FROM ARON.egresos_detalle 
                WHERE id_compras = '".$mai_id."'
                AND id_nivel like '".$id_nivel."%'
                AND tipo in ('IC','ID') 
                AND voucher = '".$voucher."'
                GROUP BY id_compras,id_cuenta_egre, dc
                UNION ALL
                --QUERY 2
                SELECT 
                        nvl(ARON.fc_cta_aasinet(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' ') cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' ') CTA_CTE, 
                        '10' fondo,	
                        nvl(ARON.equiv_nivel(a.id_nivel,a.id_compras),' ') departamento,     
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_compras),' ')  restriccion,   
                        nvl(a.importe,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1) valor,  
						nvl(a.otro_imp,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1) imp,
                        decode(a.tipo,'ED',substr(a.glosa,1,50),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')) descripcion,
                        a.id_mov_efec memo,
                        '2' ORDEN
                FROM ARON.egresos_detalle a, ARON.cont_plan b 
                where decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag) = b.id_cuenta 
                and a.id_compras = '".$mai_id."'
                and a.voucher = '".$voucher."' 
                and b.id_cont = '".$mai_id."'
                and a.id_nivel like '".$id_nivel."%'  
                and not a.tipo in ('IC','ID','DC','PC','RT','DCH') 
                --order by cuenta_e, fecha_ord, a.num_operacion, cuenta_p 
                UNION ALL
                SELECT 
                        X.CUENTA,
                        X.CTA_CTE,
                        '10' FONDO,
                        X.departamento,
                        X.RESTRICCION,
                        SUM(X.VALOR) VALOR,
						SUM(X.IMP) IMP,
                        'Total Egresos y Depositos al Banco' descripcion,
                        '' MEMO,
                        '2.1' ORDEN
                FROM (
                        SELECT 
                                nvl(ARON.fc_cta_aasinet(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ') cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ') CTA_CTE, 	
                                nvl(ARON.equiv_nivel('".$id_nivel."',a.id_compras),' ') departamento,     
                                nvl(ARON.fc_restriccion_aasinet(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ')  restriccion,   
                                nvl(sum(a.importe),0)*decode(decode(a.dc,'C','C',a.dc),'D',1,-1) valor,
                                nvl(sum(a.otro_imp),0)*decode(decode(a.dc,'C','C',a.dc),'D',1,-1) imp
                        FROM ARON.egresos_detalle a, ARON.cont_plan b 
                        where decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag) = b.id_cuenta 
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '".$id_nivel."%' 
                        and not a.tipo in ('IC','ID','DC','PC','RT','DCH') 
                        GROUP BY a.id_compras,a.tipo,a.id_cuenta_egre,a.id_cuenta_pag,a.dc
                ) X
                GROUP BY X.CUENTA,X.CTA_CTE,X.departamento,X.RESTRICCION
                --QUERY 5
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_compras),' ') cta_cte, 
                        '10' fondo,					
                        nvl(decode(substr(a.id_nivel,1,1),'1',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'2',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'3',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'4',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','41010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'5',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'6',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','61010101',ARON.equiv_nivel(a.id_nivel,a.id_compras))),' ') departamento,										
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_compras),' ')  restriccion, 
                        nvl(a.importe,0)*decode(a.dc,'D',1,-1) valor,  
						nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1) imp,
                        substr(ARON.prov_ret_xmov(a.id_mov_efec),1,12)||'-'||nvl(a.glosa,' ')||'-'||to_char(a.fecha,'dd/mm/yy') historico,
                        a.id_mov_efec memo,
                        '5' ORDEN
                FROM ARON.egresos_detalle a, ARON.cont_plan b
                where a.id_cuenta_egre = b.id_cuenta  
                and a.id_compras = '".$mai_id."'
                and a.voucher = '".$voucher."' 
                and b.id_cont = '".$mai_id."'
                and a.id_nivel like '".$id_nivel."%' 
                and a.tipo = 'RT' 
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_egre,id_compras),' ') cta_cte, 
                        '10' fondo, 
                        nvl(ARON.equiv_nivel('".$id_nivel."',id_compras),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,id_compras),' ')  restriccion,  
                        sum(nvl(importe,0))*decode(dc,'C',1,-1) importe, 
						sum(nvl(otro_imp,0))*decode(dc,'C',1,-1) imp,
                        'Total Rentenciones' descripcion,
                        '' memo,
                        '5.1' ORDEN
                FROM ARON.egresos_detalle 
                where id_compras = '".$mai_id."'
                and id_nivel like '".$id_nivel."%' 
                and tipo = 'RT' 
                and voucher = '".$voucher."' 
                GROUP BY id_compras,id_cuenta_egre, dc 
                ORDER BY ORDEN,CUENTA,CTA_CTE ";
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetIncome($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    RESTRICCION as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    DESCRIPCION as \"Description\", 
                                    MEMO as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                        --QUERY 1
                        SELECT         
                                nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_compras),' ') cuenta,
                                decode(substr(a.id_nivel,1,1),'3',nvl(ARON.FC_CTA_CTE_IMP(a.id_compras,a.id_mov_efec),ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_compras)),nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_compras),' ')) cta_cte,
                                '10' fondo,				
                                nvl(decode(substr(a.id_nivel,1,1),'1',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'2',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'3',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'4',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','41010101',ARON.equiv_nivel('4',a.id_compras)),'5',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'6',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-11','61010101',ARON.equiv_nivel(a.id_nivel,a.id_compras))),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_compras),' ')  restriccion,     
                                to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') valor,   
                                decode(nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_compras),' '),'1112025',to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99'),'2162001','','') imp,
                                NVL((select n.serie||'-'||n.NUMDOC from ARON.IMP_REGVENTAS n join ARON.IMP_INGVNT b
                                on n.ID_MOV_VNT=b.ID_MOV_VNT where n.ID_VENTA=b.ID_VENTA and n.ID_VENTA=a.ID_COMPRAS  
                                and b.ID_MOV_ING=a.ID_MOV_EFEC ),(select n.serie||'-'||n.NUMDOC from ARON.mkt_REGVENTAS n join ARON.IMP_INGVNT b
                                on n.ID_MOV_VNT=b.ID_MOV_VNT where n.ID_VENTA=b.ID_VENTA and n.ID_VENTA=a.ID_COMPRAS  
                                and b.ID_MOV_ING=a.ID_MOV_EFEC ))||' '||decode(a.tipo,'IC',decode(a.tipo2,'XD',substr(a.glosa,1,50),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')) descripcion,
                                a.id_mov_efec memo,
                                '1.1' ORDEN                                
                        FROM ARON.egresos_detalle a, ARON.cont_plan b
                        where a.id_compras = b.id_cont 
                        and a.id_cuenta_egre = b.id_cuenta 
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '".$id_nivel."%' 
                        and a.tipo in ('IC','ID')
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_compras),' ') cuenta,
                                nvl(ARON.fc_cta_cte_aasinet(id_cuenta_egre,id_compras),' ') cta_cte, 
                                '10' fondo,		
                                nvl(ARON.equiv_nivel('".$id_nivel."',id_compras),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,id_compras),' ')  restriccion,
                                to_char(nvl(sum(importe),0)*decode(dc,'C',1,-1),'999999999.99') valor, 
								decode(nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_compras),' '),'1112025',to_char(nvl(sum(otro_imp),0)*decode(dc,'C',1,-1),'999999999.99'),'2162001',to_char(nvl(sum(otro_imp),0)*decode(dc,'C',1,-1),'999999999.99'),'') imp,
                                'Total Caja Operativa' descripcion,
                                '' memo,
                                '1' ORDEN                                
                        FROM ARON.egresos_detalle 
                        WHERE id_compras = '".$mai_id."'
                        AND id_nivel like '".$id_nivel."%'
                        AND tipo in ('IC','ID') 
                        AND voucher = '".$voucher."'
                        GROUP BY id_compras,id_cuenta_egre, dc
                        UNION ALL
                        --QUERY 2
                        SELECT 
                                nvl(ARON.fc_cta_aasinet(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' ') cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' ') CTA_CTE, 
                                '10' fondo,	
                                nvl(ARON.equiv_nivel(a.id_nivel,a.id_compras),' ') departamento,     
                                nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_compras),' ')  restriccion,   
                                to_char(nvl(a.importe,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1),'999999999.99') valor,								
								decode(a.otro_imp,0,' ',decode(nvl(ARON.fc_cta_aasinet(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' '),'1112025',to_char(nvl(a.otro_imp,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1),'999999999.99'),'2162001',to_char(nvl(a.otro_imp,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1),'999999999.99'),'')) imp,
                                decode(a.tipo,'ED',substr(a.glosa,1,50),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')) descripcion,
                                a.id_mov_efec memo,
                                '2' ORDEN                                
                        FROM ARON.egresos_detalle a, ARON.cont_plan b 
                        where decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag) = b.id_cuenta 
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '".$id_nivel."%'  
                        and not a.tipo in ('IC','ID','DC','PC','RT','DCH') 
                        --order by cuenta_e, fecha_ord, a.num_operacion, cuenta_p 
                        UNION ALL
                        SELECT 
                                X.CUENTA,
                                X.CTA_CTE,
                                '10' FONDO,
                                X.departamento,
                                X.RESTRICCION,
                                to_char(SUM(X.VALOR),'999999999.99') VALOR,
								decode(X.CUENTA,'1112025',to_char(SUM(X.IMP),'999999999.99') ,'2162001',to_char(SUM(X.IMP),'999999999.99') ,'') IMP,
                                'Total Egresos y Depositos al Banco' descripcion,
                                '' MEMO,
                                '2.1' ORDEN
                        FROM (
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ') cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ') CTA_CTE, 	
                                        nvl(ARON.equiv_nivel('".$id_nivel."',a.id_compras),' ') departamento,     
                                        nvl(ARON.fc_restriccion_aasinet(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ')  restriccion,   
                                        nvl(sum(a.importe),0)*decode(decode(a.dc,'C','C',a.dc),'D',1,-1) valor,
										nvl(sum(a.otro_imp),0)*decode(decode(a.dc,'C','C',a.dc),'D',1,-1) imp
                                FROM ARON.egresos_detalle a, ARON.cont_plan b 
                                where decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag) = b.id_cuenta 
                                and a.id_compras = '".$mai_id."'
                                and a.voucher = '".$voucher."' 
                                and b.id_cont = '".$mai_id."'
                                and a.id_nivel like '".$id_nivel."%' 
                                and not a.tipo in ('IC','ID','DC','PC','RT','DCH') 
                                GROUP BY a.id_compras,a.tipo,a.id_cuenta_egre,a.id_cuenta_pag,a.dc
                        ) X
                        GROUP BY X.CUENTA,X.CTA_CTE,X.departamento,X.RESTRICCION
                        --QUERY 5
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_compras),' ') cta_cte, 
                                '10' fondo,					
                                nvl(decode(substr(a.id_nivel,1,1),'1',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'2',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'3',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'4',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','41010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'5',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_compras)),'6',decode(ARON.equiv(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_compras),'1132001-2','61010101',ARON.equiv_nivel(a.id_nivel,a.id_compras))),' ') departamento,										
                                nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_compras),' ')  restriccion, 
                                to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') valor, 
								decode(nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_compras),' '),'1112025',to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99') ,'2162001',to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99') ,'') imp,
                                substr(ARON.prov_ret_xmov(a.id_mov_efec),1,12)||'-'||nvl(a.glosa,' ')||'-'||to_char(a.fecha,'dd/mm/yy') historico,
                                a.id_mov_efec memo,
                                '5' ORDEN                                
                        FROM ARON.egresos_detalle a, ARON.cont_plan b
                        where a.id_cuenta_egre = b.id_cuenta  
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '".$id_nivel."%' 
                        and a.tipo = 'RT' 
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet(id_cuenta_egre,id_compras),' ') cta_cte, 
                                '10' fondo, 
                                nvl(ARON.equiv_nivel('".$id_nivel."',id_compras),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,id_compras),' ')  restriccion,  
                                to_char(sum(nvl(importe,0))*decode(dc,'C',1,-1),'999999999.99') valor,
								decode(nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_compras),' '),'1112025',to_char(sum(nvl(otro_imp,0))*decode(dc,'C',1,-1),'999999999.99') ,'2162001',to_char(sum(nvl(otro_imp,0))*decode(dc,'C',1,-1),'999999999.99'),'') imp,	
                                'Total Rentenciones' descripcion,
                                '' memo,
                                '5.1' ORDEN                                
                        FROM ARON.egresos_detalle 
                        where id_compras = '".$mai_id."'
                        and id_nivel like '".$id_nivel."%' 
                        and tipo = 'RT' 
                        and voucher = '".$voucher."' 
                        GROUP BY id_compras,id_cuenta_egre, dc
                        ORDER BY ORDEN,CUENTA,CTA_CTE
                    ) X ";
        $oQuery = DB::connection('oracleapp')->select($query);
        return $oQuery;
    }
    //Ventas UPeU
    public static function listVoucherCWAasinetSales($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
            error_log("====>> listVoucherCWAasinetSales : ");
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        VOUCHER,
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.VENTAS_VOUCHER@DBL_JUL
                WHERE ID_VENTA = '".$id_compra."'
                AND ID_NIVEL = '".$id_depto."'
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS@DBL_JUL
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '10'
                AND ID_NIVEL = '5'
                AND TIPO = 'RV' --RV: LOTE ASSINET
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracle')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetSales($id_nivel,$id_anho,$voucher){
        $mai_id = "001-".$id_anho;
        $query = "SELECT a.serie,a.numvnt, 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ') cta_cte, 
                                '10' fondo,							
                                nvl(decode(ARON.equiv@DBL_JUL(b.id_cuenta_vnt,b.id_venta)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(b.id_nivel_vnt,b.id_venta)),' ') departamento, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ')  restriccion,
                                to_char((b.importe-nvl(b.igv,0))*decode(b.dc,'D',-1,1),'99999999.99') valor,
                                decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                                a.id_mov_vnt memo,
                                a.docvnt
                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."' 
                AND a.VOUCHER_VNT = '".$voucher."'
                AND b.id_nivel_vnt like '".$id_nivel."%' 
                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                AND a.estado = 'V' 					
                UNION ALL 
                SELECT a.serie,a.numvnt, 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL('40.01.01',b.id_venta),' ') cuenta, 
                        ' ' cta_cte, 
                        '10' fondo,
                        '11010102' departamento, 							
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL('40.01.01',b.id_venta),' ')  restriccion,
                        to_char(nvl(b.igv,0)*decode(b.dc,'D',-1,1),'99999999.99') valor,  
                        decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                        a.id_mov_vnt memo,
                        a.docvnt
                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."' 
                AND a.VOUCHER_VNT = '".$voucher."'
                AND b.id_nivel_vnt like '".$id_nivel."%'  
                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                AND a.estado = 'V' 	
                AND NVL(b.igv,0) <> 0 				
                UNION ALL 
                SELECT a.serie,a.numvnt, 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ') cta_cte,
                        '10' fondo, 							
                        nvl(decode(ARON.equiv@DBL_JUL(a.id_cuenta_cli,a.id_venta)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel_cont,a.id_venta)),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ')  restriccion, 							
                        to_char(a.importe*1,'99999999.99') valor, 
                        decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(a.glosa,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                        a.id_mov_vnt memo,
                        a.docvnt
                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."' 
                AND a.VOUCHER_VNT = '".$voucher."'
                AND b.id_nivel_vnt like '".$id_nivel."%'  
                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                AND a.estado = 'V' 
                GROUP BY a.id_venta,a.id_mov_vnt,a.id_cuenta_cli,a.serie,a.numvnt,a.id_nivel_cont,a.importe,a.tipo_mov,a.id_personal,a.glosa,a.docvnt 
                ORDER BY DOCVNT,SERIE,NUMVNT,VALOR,CUENTA  ";
        $oQuery = DB::connection('oracle')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetSales($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    restriccion as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    descripcion as \"Description\", 
                                    memo as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                                SELECT a.serie,a.numvnt, 
                                        nvl(ARON.fc_cta_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ') cta_cte, 
                                        '10' fondo,							
                                        nvl(decode(ARON.equiv@DBL_JUL(b.id_cuenta_vnt,b.id_venta)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(b.id_nivel_vnt,b.id_venta)),' ') departamento, 
                                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ')  restriccion,							
                                        to_char((b.importe-nvl(b.igv,0))*decode(b.dc,'D',-1,1),'99999999.99') valor,
                                        decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                                        a.id_mov_vnt memo,
                                        a.docvnt
                                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                                WHERE a.id_venta = b.id_venta 
                                AND a.id_mov_vnt = b.id_mov_vnt 
                                AND a.id_venta = '".$mai_id."'  
                                AND a.VOUCHER_VNT = '".$voucher."'
                                AND b.id_nivel_vnt like '".$id_nivel."%' 
                                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                                AND a.estado = 'V' 					
                                UNION ALL 
                                SELECT a.serie,a.numvnt, 
                                                nvl(ARON.fc_cta_aasinet@DBL_JUL('40.01.01',b.id_venta),' ') cuenta, 
                                                ' ' cta_cte, 
                                                '10' fondo,
                                                '11010102' departamento, 							
                                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL('40.01.01',b.id_venta),' ')  restriccion,
                                                to_char(nvl(b.igv,0)*decode(b.dc,'D',-1,1),'99999999.99') valor,  
                                                decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                                                a.id_mov_vnt memo,
                                                a.docvnt
                                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                                WHERE a.id_venta = b.id_venta 
                                AND a.id_mov_vnt = b.id_mov_vnt 
                                AND a.id_venta = '".$mai_id."' 
                                AND a.VOUCHER_VNT = '".$voucher."'
                                AND b.id_nivel_vnt like '".$id_nivel."%'  
                                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                                AND a.estado = 'V' 	
                                AND NVL(b.igv,0) <> 0 				
                                UNION ALL 
                                SELECT a.serie,a.numvnt, 
                                        nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ') cta_cte,
                                        '10' fondo, 							
                                        nvl(decode(ARON.equiv@DBL_JUL(a.id_cuenta_cli,a.id_venta)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel_cont,a.id_venta)),' ') departamento,
                                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ')  restriccion, 							
                                        to_char(a.importe*1,'99999999.99') valor, 
                                        decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(a.glosa,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                                        a.id_mov_vnt memo,a.docvnt
                                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                                WHERE a.id_venta = b.id_venta 
                                AND a.id_mov_vnt = b.id_mov_vnt 
                                AND a.id_venta = '".$mai_id."' 
                                AND a.VOUCHER_VNT = '".$voucher."'
                                AND b.id_nivel_vnt like '".$id_nivel."%'  
                                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                                AND a.estado = 'V' 
                                GROUP BY a.id_venta,a.id_mov_vnt,a.id_cuenta_cli,a.serie,a.numvnt,a.id_nivel_cont,a.importe,a.tipo_mov,a.id_personal,a.glosa,a.docvnt 
                                ORDER BY DOCVNT,SERIE,NUMVNT,VALOR,CUENTA
                    ) X ";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }
    //Ventas Colegio
    public static function listVoucherCWAasinetSalesCU($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        VOUCHER,
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.VENTAS_VOUCHER
                WHERE ID_VENTA = '".$id_compra."'
                AND ID_NIVEL = '4'
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '12'
                AND ID_NIVEL = '4'
                AND TIPO = 'RV' --RV: LOTE ASSINET
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetCU($id_nivel,$id_anho,$voucher){
        $id_nivel = "4";
        $mai_id = "001-".$id_anho;
        $query = "SELECT A.serie_vnt,A.numvnt, 					
                        nvl(ARON.fc_cta_aasinet(b.id_cuenta_vnt,a.id_venta),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(b.id_cuenta_vnt,a.id_venta),' ') cta_cte, 
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet(b.id_cuenta_vnt,a.id_venta),' ')  restriccion,						
                        nvl(ARON.equiv_nivel(decode(ARON.fc_cta_cte_aasinet(b.id_cuenta_vnt,a.id_venta),'1132001','4',b.id_nivel_vnt),a.id_venta),'') departamento, 
                        to_char(b.importe*decode(b.dc,'D',-1,1),'99999999.99') valor,  
                        SUBSTR(decode(a.DOCVNT,'12','VNT','07','NOTA')||': '||'(Doc:'||a.serie_vnt||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,40),1,50) descripcion,A.id_mov_vnt memo 
                FROM ARON.cu_regventas a, ARON.cu_regdetalle b 
                where a.id_venta = b.id_venta 
                and a.id_mov_vnt = b.id_mov_vnt 
                and a.id_venta = '".$mai_id."'
                and a.voucher_vnt = '".$voucher."'
                and b.id_nivel_vnt like '".$id_nivel."%' 
                and a.tipo_mov in ('01','04') 
                and a.estado = 'V' 
                UNION ALL 
                SELECT A.serie_vnt,A.numvnt, 
                        nvl(ARON.fc_cta_aasinet(A.id_cuenta_cli,A.id_venta),' ') cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(A.id_cuenta_cli,A.id_venta),' ') cta_cte,   
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet(A.id_cuenta_cli,A.id_venta),' ')  restriccion,
                        nvl(ARON.equiv_nivel(decode(ARON.fc_cta_cte_aasinet(A.id_cuenta_cli,A.id_venta),'1132001','4',A.id_nivel_cont),A.id_venta),'') departamento,  
                        to_char(abs(A.importe)*decode(a.DOCVNT,'07',-1,1),'99999999.99') valor,
                        SUBSTR(decode(a.DOCVNT,'03','VNT','07','NOTA','08','NOTA')||': '||'(Doc:'||a.serie_vnt||'-'||to_number(a.numvnt)||')-'||substr(A.glosa,1,40),1,50) descripcion, A.id_mov_vnt memo 
                FROM ARON.cu_regventas A, ARON.CU_REGDETALLE B 
                WHERE A.ID_VENTA = B.ID_VENTA 
                AND A.ID_MOV_VNT = B.ID_MOV_VNT 
                AND B.ID_CUENTA_VNT <> '69.01.01' 
                AND A.id_venta = '".$mai_id."'
                AND B.ID_VENTA = '".$mai_id."' 
                AND A.voucher_vnt = '".$voucher."'
                AND A.id_nivel_cont like '".$id_nivel."%' 
                AND A.tipo_mov in ('01','04')  
                AND A.estado = 'V'  
                GROUP BY A.id_venta,A.id_mov_vnt,A.id_cuenta_cli,A.id_nivel_cont,A.serie_vnt,A.numvnt,A.importe,A.DOCVNT,A.glosa 	
                UNION ALL
                SELECT A.serie_vnt,A.numvnt, 
                        nvl(ARON.fc_cta_aasinet('20.01.14',A.id_venta),' ') cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(A.id_cuenta_cli,A.id_venta),' ') cta_cte,
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet('20.01.14',A.id_venta),' ')  restriccion,  
                        nvl(ARON.equiv_nivel(decode(ARON.fc_cta_cte_aasinet(A.id_cuenta_cli,A.id_venta),'1132001','4',A.id_nivel_cont),A.id_venta),'') departamento,  
                        to_char(abs(A.importe),'99999999.99') valor,
                        SUBSTR(decode(a.DOCVNT,'03','VNT','07','NOTA','08','NOTA')||': '||'(Doc:'||a.serie_vnt||'-'||to_number(a.numvnt)||')-'||substr(A.glosa,1,40),1,50) descripcion, A.id_mov_vnt memo 						
                FROM ARON.cu_regventas A, ARON.CU_REGDETALLE B 
                WHERE A.ID_VENTA = B.ID_VENTA 
                AND A.ID_MOV_VNT = B.ID_MOV_VNT 
                AND B.ID_CUENTA_VNT ='69.01.01' 
                AND A.id_venta = '".$mai_id."'
                AND B.ID_VENTA = '".$mai_id."' 
                AND A.voucher_vnt = '".$voucher."'
                AND A.id_nivel_cont like '".$id_nivel."%'                  
                AND A.tipo_mov in ('01','04')  
                AND A.estado = 'V' 
                GROUP BY A.id_venta,A.id_mov_vnt,A.id_cuenta_cli,A.id_nivel_cont,A.serie_vnt,A.numvnt,A.importe,A.DOCVNT,A.glosa 
                ORDER BY SERIE_VNT,NUMVNT,CUENTA ";
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetCU($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    fondo as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    restriccion as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    descripcion as \"Description\", 
                                    memo as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                                SELECT A.serie_vnt,A.numvnt, 					
                                        nvl(ARON.fc_cta_aasinet(b.id_cuenta_vnt,a.id_venta),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(b.id_cuenta_vnt,a.id_venta),' ') cta_cte, 
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet(b.id_cuenta_vnt,a.id_venta),' ')  restriccion,						
                                        nvl(ARON.equiv_nivel(decode(ARON.fc_cta_cte_aasinet(b.id_cuenta_vnt,a.id_venta),'1132001','4',b.id_nivel_vnt),a.id_venta),'') departamento, 
                                        to_char(b.importe*decode(b.dc,'D',-1,1),'99999999.99') valor,  
                                        SUBSTR(decode(a.DOCVNT,'12','VNT','07','NOTA')||': '||'(Doc:'||a.serie_vnt||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,40),1,50) descripcion, 
                                        A.id_mov_vnt memo 
                                FROM ARON.cu_regventas a, ARON.cu_regdetalle b 
                                where a.id_venta = b.id_venta 
                                and a.id_mov_vnt = b.id_mov_vnt 
                                and a.id_venta = '".$mai_id."'
                                and a.voucher_vnt = '".$voucher."'
                                and b.id_nivel_vnt like '".$id_nivel."%' 
                                and a.tipo_mov in ('01','04') 
                                and a.estado = 'V' 
                                UNION ALL 
                                SELECT A.serie_vnt,A.numvnt, 
                                        nvl(ARON.fc_cta_aasinet(A.id_cuenta_cli,A.id_venta),' ') cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(A.id_cuenta_cli,A.id_venta),' ') cta_cte,   
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet(A.id_cuenta_cli,A.id_venta),' ')  restriccion,
                                        nvl(ARON.equiv_nivel(decode(ARON.fc_cta_cte_aasinet(A.id_cuenta_cli,A.id_venta),'1132001','4',A.id_nivel_cont),A.id_venta),'') departamento,  
                                        to_char(abs(A.importe)*decode(a.DOCVNT,'07',-1,1),'99999999.99') valor,
                                        SUBSTR(decode(a.DOCVNT,'03','VNT','07','NOTA','08','NOTA')||': '||'(Doc:'||a.serie_vnt||'-'||to_number(a.numvnt)||')-'||substr(A.glosa,1,40),1,50) descripcion, A.id_mov_vnt memo 
                                FROM ARON.cu_regventas A, ARON.CU_REGDETALLE B 
                                WHERE A.ID_VENTA = B.ID_VENTA 
                                AND A.ID_MOV_VNT = B.ID_MOV_VNT 
                                AND B.ID_CUENTA_VNT <> '69.01.01' 
                                AND A.id_venta = '".$mai_id."'
                                AND B.ID_VENTA = '".$mai_id."' 
                                AND A.voucher_vnt = '".$voucher."'
                                AND A.id_nivel_cont like '".$id_nivel."%' 
                                AND A.tipo_mov in ('01','04')  
                                AND A.estado = 'V'  
                                GROUP BY A.id_venta,A.id_mov_vnt,A.id_cuenta_cli,A.id_nivel_cont,A.serie_vnt,A.numvnt,A.importe,A.DOCVNT,A.glosa 	
                                UNION ALL
                                SELECT A.serie_vnt,A.numvnt, 
                                        nvl(ARON.fc_cta_aasinet('20.01.14',A.id_venta),' ') cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(A.id_cuenta_cli,A.id_venta),' ') cta_cte,
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet('20.01.14',A.id_venta),' ')  restriccion,  
                                        nvl(ARON.equiv_nivel(decode(ARON.fc_cta_cte_aasinet(A.id_cuenta_cli,A.id_venta),'1132001','4',A.id_nivel_cont),A.id_venta),'') departamento,  
                                        to_char(abs(A.importe),'99999999.99') valor,
                                        SUBSTR(decode(a.DOCVNT,'03','VNT','07','NOTA','08','NOTA')||': '||'(Doc:'||a.serie_vnt||'-'||to_number(a.numvnt)||')-'||substr(A.glosa,1,40),1,50) descripcion, A.id_mov_vnt memo 						
                                FROM ARON.cu_regventas A, ARON.CU_REGDETALLE B 
                                WHERE A.ID_VENTA = B.ID_VENTA 
                                AND A.ID_MOV_VNT = B.ID_MOV_VNT 
                                AND B.ID_CUENTA_VNT ='69.01.01' 
                                AND A.id_venta = '".$mai_id."'
                                AND B.ID_VENTA = '".$mai_id."'
                                AND A.voucher_vnt = '".$voucher."'
                                AND A.id_nivel_cont like '".$id_nivel."%'                  
                                AND A.tipo_mov in ('01','04')  
                                AND A.estado = 'V' 
                                GROUP BY A.id_venta,A.id_mov_vnt,A.id_cuenta_cli,A.id_nivel_cont,A.serie_vnt,A.numvnt,A.importe,A.DOCVNT,A.glosa 
                                ORDER BY SERIE_VNT,NUMVNT,CUENTA
                    ) X ";
        $oQuery = DB::connection('oracleapp')->select($query);
        return $oQuery;
    }
    //TLC
    public static function listVoucherCWAasinetTLC($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        voucher, 
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.tlecrd_voucher 
                WHERE id_tlecrd = '".$id_compra."' 
                AND id_nivel = '".$id_depto."' 
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '13'
                AND ID_NIVEL = '".$id_depto."'
                AND TIPO = 'MB'
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetTLC($id_nivel,$id_anho,$voucher){
        $mai_id = "001-".$id_anho;
        $query = "SELECT 
                        nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_PAG ,A.ID_TLECRD),' ') AS cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(A.ID_CUENTA_PAG,A.ID_TLECRD),' ') AS cta_cte, 
                        '10' fondo,
                        nvl(ARON.equiv_nivel(a.id_nivel,a.id_tlecrd),' ') AS departamento,
                        nvl(ARON.fc_restriccion_aasinet(A.ID_CUENTA_PAG,A.ID_TLECRD),' ')  restriccion,
                        to_char(A.IMPORTE *decode(A.DC,'D',-1,1),'99999999.99') AS valor,   
                        decode(nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_PAG ,A.ID_TLECRD),' '),'1112025',decode(A.OTRO_IMP,0,'',to_char(A.OTRO_IMP *decode(A.DC,'D',-1,1),'99999999.99')),'') AS IMP,
                        a.glosa1 descripcion, 
                        '1' ORDEN,
                        ID_MOV_TLECRD memo,
                        A.NUM_OPERACION,
                        A.ID_CHEQUERA        
                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."' 
                AND A.ID_NIVEL like '".$id_nivel."%' 
                AND A.TIPO IN ('PC','TTL')     
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' ') AS cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ') AS cta_cte, 
                        '10' fondo,
                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,  
                        nvl(ARON.fc_restriccion_aasinet(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ')  restriccion,
                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',1,-1)),'99999999.99') AS valor, 
                        decode(nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                        substr(' ('||ARON.nombre_banco(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||'-'||A.FECHA||')',1,60) descripcion,         
                        '1.1' ORDEN,
                        '' memo,
                        A.NUM_OPERACION,
                        A.ID_CHEQUERA
                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."'
                AND A.ID_NIVEL like '".$id_nivel."%'
                AND A.TIPO IN ('PC','TTL')     
                GROUP BY A.ID_TLECRD,A.ID_CUENTA_EGRE,SUBSTR(A.ID_NIVEL,1,1),A.ID_CHEQUERA,A.NUM_OPERACION,A.FECHA
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' ') AS cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ') AS cta_cte,  
                        '10' fondo,                       
                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,
                        nvl(ARON.fc_restriccion_aasinet(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ')  restriccion,                          
                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',-1,1)),'99999999.99') AS valor,  
                        decode(nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                        substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,                                 
                        '2' ORDEN,
                        A.id_mov_tlecrd memo,
                        A.NOMBRE,
                        A.ID_CHEQUERA 
                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."'
                AND A.ID_NIVEL like '".$id_nivel."%'
                AND A.TIPO IN ('RT')     
                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.ID_CUENTA_EGRE,A.ID_NIVEL,A.NUM_OPERACION,A.id_chequera,A.num_operacion, A.VOUCHER,A.NOMBRE,A.FECHA
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_PAG ,A.ID_TLECRD),' ') AS cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(A.ID_CUENTA_PAG,A.ID_TLECRD),' ') AS cta_cte,  
                        '10' fondo,                       
                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento, 
                        nvl(ARON.fc_restriccion_aasinet(A.ID_CUENTA_PAG,A.ID_TLECRD),' ')  restriccion,                       
                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',1,-1)),'99999999.99') AS valor,   
                        decode(nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_PAG ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                        substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,         
                        '2.1' ORDEN,
                        A.id_mov_tlecrd memo ,
                        A.NOMBRE,
                        A.ID_CHEQUERA
                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."'
                AND A.ID_NIVEL like '".$id_nivel."%'
                AND A.TIPO IN ('RT')     
                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.ID_CUENTA_PAG,A.ID_NIVEL,A.NUM_OPERACION,A.FECHA,A.id_chequera,A.num_operacion,A.VOUCHER,A.NOMBRE
                UNION ALL                
                SELECT  
                        nvl(ARON.fc_cta_aasinet(id_cuenta_pag,A.ID_TLECRD),' ') AS cuenta,   
                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_pag,A.ID_TLECRD),' ') AS cta_cte,
                        '10' fondo,                          
                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,   
                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_pag,A.ID_TLECRD),' ')  restriccion,                    
                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',-1,1)),'99999999.99') AS valor,        
                        decode(nvl(ARON.fc_cta_aasinet(id_cuenta_pag,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99')))  IMP,                      
                        A.GLOSA1,         
                        '2' ORDEN,
                        A.id_mov_tlecrd memo,
                        A.NUM_OPERACION,
                        A.ID_CHEQUERA
                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND A.ID_NIVEL = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."'
                AND A.ID_NIVEL like '".$id_nivel."%'
                AND A.TIPO = 'DTL'   
                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.id_cuenta_pag,A.ID_NIVEL,A.NOMBRE,A.NUM_OPERACION,A.FECHA,A.id_chequera,A.num_operacion,A.VOUCHER,A.GLOSA1
                UNION ALL
                SELECT  
                        nvl(ARON.fc_cta_aasinet(A.id_cuenta_chq,a.id_tlecrd),' ') cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(A.id_cuenta_chq,a.id_tlecrd),' ') cta_cte,  
                        '10' fondo,
                        nvl(ARON.equiv_nivel(a.id_nivel,a.id_tlecrd),' ') departamento,  
                        nvl(ARON.fc_restriccion_aasinet(A.id_cuenta_chq,a.ID_TLECRD),' ')  restriccion,
                        to_char(sum(a.importe*decode(b.dc,'D',1,-1)),'99999999.99') valor, 
                        decode(nvl(ARON.fc_cta_aasinet(A.id_cuenta_chq,a.id_tlecrd),' '),'1112025',decode(sum(a.OTRO_IMP*decode(b.dc,'D',1,-1)),0,'',to_char(sum(a.OTRO_IMP*decode(b.dc,'D',1,-1)),'99999999.99'))) IMP,                        
                        'Nro. Oper. ' ||A.NUM_OPERACION||'-'||a.fecha,
                        '1' orden,
                        '' memo,
                        A.NUM_OPERACION,
                        A.ID_CHEQUERA
                FROM ARON.tlecrd_detrac a, ARON.tlecrd_mov_doc b, ARON.tlecrd_voucher c  
                WHERE a.id_tlecrd = b.id_tlecrd  
                and a.id_mov_tlecrd = b.id_mov_tlecrd 
                and a.id_tlecrd = c.id_tlecrd 
                and a.id_nivel = c.id_nivel  
                and a.voucher = c.voucher 
                and a.id_tlecrd = '".$mai_id."'
                and a.voucher = '".$voucher."'
                and a.id_nivel like '".$id_nivel."%' 
                group by a.id_tlecrd,A.id_cuenta_chq,a.id_nivel,A.ID_CHEQUERA,a.NUM_OPERACION,a.fecha
                ORDER BY ID_CHEQUERA,NUM_OPERACION,valor,ORDEN,CUENTA ";
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetTLC($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    RESTRICCION as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    imp as \"CurrencyAmount\", 
                                    DESCRIPCION as \"Description\", 
                                    MEMO as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_PAG ,A.ID_TLECRD),' ') AS cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(A.ID_CUENTA_PAG,A.ID_TLECRD),' ') AS cta_cte, 
                                        '10' fondo,
                                        nvl(ARON.equiv_nivel(a.id_nivel,a.id_tlecrd),' ') AS departamento,
                                        nvl(ARON.fc_restriccion_aasinet(A.ID_CUENTA_PAG,A.ID_TLECRD),' ')  restriccion,
                                        to_char(A.IMPORTE *decode(A.DC,'D',-1,1),'99999999.99') AS valor,  
                                        --to_char(A.OTRO_IMP *decode(A.DC,'D',-1,1),'99999999.99') AS IMP, 
                                        decode(nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_PAG ,A.ID_TLECRD),' '),'1112025',decode(A.OTRO_IMP,0,'',to_char(A.OTRO_IMP *decode(A.DC,'D',-1,1),'99999999.99')),'') AS IMP,
                                        a.glosa1 descripcion, 
                                        '1' ORDEN,
                                        ID_MOV_TLECRD memo,
                                        A.NUM_OPERACION,
                                        A.ID_CHEQUERA        
                                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                                WHERE A.ID_TLECRD = B.ID_TLECRD 
                                AND A.VOUCHER = B.VOUCHER  
                                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                                AND A.ID_TLECRD = '".$mai_id."'
                                AND A.VOUCHER = '".$voucher."' 
                                AND A.ID_NIVEL like '".$id_nivel."%' 
                                AND A.TIPO IN ('PC','TTL')     
                                UNION ALL
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' ') AS cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ') AS cta_cte, 
                                        '10' fondo,
                                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,  
                                        nvl(ARON.fc_restriccion_aasinet(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ')  restriccion,
                                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',1,-1)),'99999999.99') AS valor,  
                                        decode(nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                                        substr(' ('||ARON.nombre_banco(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||'-'||A.FECHA||')',1,60) descripcion,         
                                        '1.1' ORDEN,
                                        '' memo,
                                        A.NUM_OPERACION,
                                        A.ID_CHEQUERA
                                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                                WHERE A.ID_TLECRD = B.ID_TLECRD 
                                AND A.VOUCHER = B.VOUCHER  
                                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                                AND A.ID_TLECRD = '".$mai_id."'
                                AND A.VOUCHER = '".$voucher."'
                                AND A.ID_NIVEL like '".$id_nivel."%'
                                AND A.TIPO IN ('PC','TTL')     
                                GROUP BY A.ID_TLECRD,A.ID_CUENTA_EGRE,SUBSTR(A.ID_NIVEL,1,1),A.ID_CHEQUERA,A.NUM_OPERACION,A.FECHA
                                UNION ALL
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' ') AS cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ') AS cta_cte,  
                                        '10' fondo,                       
                                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,
                                        nvl(ARON.fc_restriccion_aasinet(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ')  restriccion,                          
                                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',-1,1)),'99999999.99') AS valor,  
                                        decode(nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                                        substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,                                 
                                        '2' ORDEN,
                                        A.id_mov_tlecrd memo,
                                        A.NOMBRE,
                                        A.ID_CHEQUERA 
                                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                                WHERE A.ID_TLECRD = B.ID_TLECRD 
                                AND A.VOUCHER = B.VOUCHER  
                                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                                AND A.ID_TLECRD = '".$mai_id."'
                                AND A.VOUCHER = '".$voucher."'
                                AND A.ID_NIVEL like '".$id_nivel."%'
                                AND A.TIPO IN ('RT')     
                                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.ID_CUENTA_EGRE,A.ID_NIVEL,A.NUM_OPERACION,A.id_chequera,A.num_operacion, A.VOUCHER,A.NOMBRE,A.FECHA
                                UNION ALL
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_PAG ,A.ID_TLECRD),' ') AS cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(A.ID_CUENTA_PAG,A.ID_TLECRD),' ') AS cta_cte,  
                                        '10' fondo,                       
                                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento, 
                                        nvl(ARON.fc_restriccion_aasinet(A.ID_CUENTA_PAG,A.ID_TLECRD),' ')  restriccion,                       
                                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',1,-1)),'99999999.99') AS valor, 
                                        decode(nvl(ARON.fc_cta_aasinet(A.ID_CUENTA_PAG ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                                        substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,         
                                        '2.1' ORDEN,
                                        A.id_mov_tlecrd memo ,
                                        A.NOMBRE,
                                        A.ID_CHEQUERA
                                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                                WHERE A.ID_TLECRD = B.ID_TLECRD 
                                AND A.VOUCHER = B.VOUCHER  
                                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                                AND A.ID_TLECRD = '".$mai_id."'
                                AND A.VOUCHER = '".$voucher."'
                                AND A.ID_NIVEL like '".$id_nivel."%'
                                AND A.TIPO IN ('RT')     
                                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.ID_CUENTA_PAG,A.ID_NIVEL,A.NUM_OPERACION,A.FECHA,A.id_chequera,A.num_operacion,A.VOUCHER,A.NOMBRE
                                UNION ALL                
                                SELECT  
                                        nvl(ARON.fc_cta_aasinet(id_cuenta_pag,A.ID_TLECRD),' ') AS cuenta,   
                                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_pag,A.ID_TLECRD),' ') AS cta_cte,
                                        '10' fondo,                          
                                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,   
                                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_pag,A.ID_TLECRD),' ')  restriccion,                    
                                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',-1,1)),'99999999.99') AS valor,       
                                        decode(nvl(ARON.fc_cta_aasinet(id_cuenta_pag,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99')))  IMP,
                                        --substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,
                                        A.GLOSA1,         
                                        '2' ORDEN,
                                        A.id_mov_tlecrd memo,
                                        A.NUM_OPERACION,
                                        A.ID_CHEQUERA
                                FROM ARON.TLECRD_DETALLE A, ARON.TLECRD_VOUCHER B 
                                WHERE A.ID_TLECRD = B.ID_TLECRD 
                                AND A.VOUCHER = B.VOUCHER  
                                AND A.ID_NIVEL = B.ID_NIVEL 
                                AND A.ID_TLECRD = '".$mai_id."'
                                AND A.VOUCHER = '".$voucher."'
                                AND A.ID_NIVEL like '".$id_nivel."%'
                                AND A.TIPO = 'DTL'   
                                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.id_cuenta_pag,A.ID_NIVEL,A.NOMBRE,A.NUM_OPERACION,A.FECHA,A.id_chequera,A.num_operacion,A.VOUCHER,A.GLOSA1
                                UNION ALL
                                SELECT  
                                        nvl(ARON.fc_cta_aasinet(A.id_cuenta_chq,a.id_tlecrd),' ') cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(A.id_cuenta_chq,a.id_tlecrd),' ') cta_cte,  
                                        '10' fondo,
                                        nvl(ARON.equiv_nivel(a.id_nivel,a.id_tlecrd),' ') departamento,  
                                        nvl(ARON.fc_restriccion_aasinet(A.id_cuenta_chq,a.ID_TLECRD),' ')  restriccion,
                                        to_char(sum(a.importe*decode(b.dc,'D',1,-1)),'99999999.99') valor, 
                                        decode(nvl(ARON.fc_cta_aasinet(A.id_cuenta_chq,a.id_tlecrd),' '),'1112025',decode(sum(a.OTRO_IMP*decode(b.dc,'D',1,-1)),0,'',to_char(sum(a.OTRO_IMP*decode(b.dc,'D',1,-1)),'99999999.99'))) IMP,
                                        --'Registro de Telecredito'||' 10 '||decode('12','01','Enero','02','Febrero','03','Marzo','04','Abril','05','Mayo','06','Junio','07','Julio','08','Agosto','09','Septiembre','10','Octubre','11','Noviembre','Diciembre') descripcion,
                                        'Nro. Oper. ' ||A.NUM_OPERACION||'-'||a.fecha,
                                        '1' orden,
                                        '' memo,
                                        A.NUM_OPERACION,
                                        A.ID_CHEQUERA
                                FROM ARON.tlecrd_detrac a, ARON.tlecrd_mov_doc b, ARON.tlecrd_voucher c  
                                WHERE a.id_tlecrd = b.id_tlecrd  
                                and a.id_mov_tlecrd = b.id_mov_tlecrd 
                                and a.id_tlecrd = c.id_tlecrd 
                                and a.id_nivel = c.id_nivel  
                                and a.voucher = c.voucher 
                                and a.id_tlecrd = '".$mai_id."'
                                and a.voucher = '".$voucher."'
                                and a.id_nivel like '".$id_nivel."%' 
                                group by a.id_tlecrd,A.id_cuenta_chq,a.id_nivel,A.ID_CHEQUERA,a.NUM_OPERACION,a.fecha
                                ORDER BY ID_CHEQUERA,NUM_OPERACION,VALOR,ORDEN,CUENTA
                    ) X ";
        $oQuery = DB::connection('oracleapp')->select($query);
        return $oQuery;
    }
    //COMPRAS
    public static function listVoucherCWAasinetPurchases($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        voucher, 
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.compras_voucher @DBL_JUL
                WHERE id_compras = '".$id_compra."' 
                AND id_nivel = '".$id_depto."' 
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS@DBL_JUL
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '03'
                AND ID_NIVEL = '".$id_depto."'
                AND TIPO = 'RC'
                )
                ORDER BY fecha_ord DESC  "; 
        $oQuery = DB::connection('oracle')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetPurchases($id_nivel,$id_anho,$voucher){
        $mai_id = "001-".$id_anho;
        $query = "SELECT 
                        '1' orden, 
                        nvl(ARON.fc_cta_aasinet(C.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(C.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel(C.id_nivel_gasto,A.id_compras),' ') departamento, 
                        to_char(nvl(C.importe+NVL(DECODE(C.TIPO_BI,'2',C.IGV,0),0),0),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(C.id_cta_gasto,A.id_compras),' ')  restriccion,
                        substr('(Doc: '||A.serie||'-'||A.numdoc||')-'||substr(C.detalle,1,25)||'-'||ARON.ruc2(A.id_proveedor),1,60) historico,
                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||substr(C.detalle,1,30)||'-'||ARON.ruc2(A.id_proveedor),1,60) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                FROM ARON.compras_registro A, ARON.compras_detalle C  
                WHERE A.id_mov_comp = C.id_mov_comp 
                and A.id_compras = C.id_compras 
                AND A.id_cont = '".$mai_id."' 
                and A.id_compras = '".$mai_id."'									
                and A.estado = 'P' 
                and A.id_nivel_cont like '".$id_nivel."%' 
                and A.voucher = '".$voucher."'
                UNION ALL
                SELECT  
                        '1.1' orden,   
                        nvl(ARON.fc_cta_aasinet(C.id_cta_igv,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(C.id_cta_igv,a.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel(A.id_nivel_cont,A.id_compras),' ') departamento, 
                        to_char(nvl(C.igv,0),'99,999,999.99') valor, 
                        ' ' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(C.id_cta_igv,A.id_compras),' ')  restriccion, 
                        substr('(IGV: Doc: '||A.serie||'-'||A.numdoc||')-'||ARON.ruc2(A.id_proveedor),1,60) historico,
                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||'IGV Compras Div.'||'-'||ARON.ruc2(A.id_proveedor),1,60) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                FROM ARON.compras_registro A, ARON.compras_detalle C 
                WHERE A.id_mov_comp = C.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."' 
                and A.estado = 'P' 
                and A.id_nivel_cont like '".$id_nivel."%' 
                and C.tipo_bi in ('1','5') 
                UNION ALL
                SELECT 
                        '1.2' orden,   
                        nvl(ARON.fc_cta_aasinet(b.id_cuenta_comp,a.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(b.id_cuenta_comp,a.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel(a.id_nivel_cont,a.id_compras),' ') departamento, 
                        to_char(nvl(sum(a.importe*-1),0),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(b.id_cuenta_comp,a.id_compras),' ')  restriccion, 
                        substr(a.tipo_doc||':(Doc: '||A.serie||'-'||A.numdoc||')-'||ARON.ruc2(A.id_proveedor),1,60) historico,
                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||'COMPRAS'||'-'||ARON.ruc2(A.id_proveedor),1,60) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                FROM ARON.compras_registro a, ARON.compras_main b 
                WHERE a.id_compras = b.id_compras 
                and a.id_cont = '".$mai_id."'
                and a.id_compras = '".$mai_id."'
                and a.voucher = '".$voucher."'
                and a.estado = 'P' 
                and a.id_nivel_cont like '".$id_nivel."%' 
                GROUP BY a.id_compras,b.id_cuenta_comp,a.id_nivel_cont,a.importe,a.tipo_doc,A.serie,A.numdoc,A.id_proveedor,A.id_mov_comp,A.tipo_doc,A.fecha_prov
                UNION ALL
                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor),'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,
                'C'||g.memo memo,g.memo id_mov 
                FROM ( 
                SELECT '1' orden, 
                        NVL(ARON.fc_cta_aasinet(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_COMP),b.id_compras),' ') cuenta,   --60.**.**
                        NVL(ARON.fc_cta_cte_aasinet(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_comp),b.id_compras),' ') cta_cte, 
                        NVL(ARON.equiv_nivel(a.id_nivel_alm,b.id_compras),' ') departamento, 
                        NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0) valor,  
                        nvl(ARON.fc_restriccion_aasinet(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_COMP),b.id_compras),' ')  restriccion, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-' ||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',ARON.ruc2(b.id_proveedor)||' COMPRA MERCADERIAS BAZAR',ARON.alm_articulo(a.id_almacen,a.id_articulo)||ARON.ruc2(b.id_proveedor)),1,60) historico,
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-' ||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',ARON.ruc2(b.id_proveedor)||' COMPRA MERCADERIAS BAZAR',ARON.alm_articulo(a.id_almacen,a.id_articulo)||ARON.ruc2(b.id_proveedor)),1,60) descripcion,
                        b.fecha_prov, 
                        b.id_mov_comp memo 
                FROM ARON.almacen_ing_com a,ARON.compras_registro b 
                WHERE a.id_mov_comp = b.id_mov_comp 
                and b.id_cont = '".$mai_id."'
                and b.id_compras = '".$mai_id."'
                and b.voucher = '".$voucher."'
                AND a.estado        = 'P' 
                and b.id_nivel_cont like '".$id_nivel."%' 
                )g 
                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                UNION ALL
                SELECT  
                        '1.1' orden,     
                        nvl(ARON.fc_cta_aasinet(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ') cuenta, 		
                        nvl(ARON.fc_cta_cte_aasinet(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ') cta_cte, 									
                        NVL(ARON.equiv_nivel(decode(SUBSTR(c.id_almacen,1,3),'051',a.id_nivel_cont,C.id_nivel_alm),A.id_compras),' ') departamento, 
                        to_char(nvl(sum(C.igv),0),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ')  restriccion, 
                        'IGV Comp Almacen '||'Doc: '||A.serie||'-'||A.numdoc historico,
                        A.tipo_doc||'(Doc:'||A.serie||'-'||A.numdoc||') IGV Comp Almacen ' descripcion,  
                        A.fecha_prov,
                        'C'||A.id_mov_comp memo, 
                        A.id_mov_comp id_mov 	
                FROM ARON.compras_registro A, ARON.almacen_ing_com C 
                WHERE A.id_mov_comp = C.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P'  
                and C.tipo_bi in ('1','5')  
                and A.id_nivel_cont like '".$id_nivel."%' 
                group by a.id_compras,A.id_mov_comp ,C.id_cuenta_igv,C.tipo_bi,C.id_nivel_alm,A.serie,A.numdoc,a.id_nivel_cont,c.id_almacen,A.tipo_doc,A.fecha_prov
                having sum(C.igv) <> 0
                UNION ALL
                SELECT '2' orden, 
                        NVL(ARON.fc_cta_aasinet(a.id_cuenta_alm,b.id_compras),' ') cuenta,  ----20.**.**
                        NVL(ARON.fc_cta_cte_aasinet(a.id_cuenta_alm,b.id_compras),' ') cta_cte, 
                        NVL(ARON.equiv_nivel(b.id_nivel_cont,b.id_compras),' ') departamento, 
                        TO_CHAR(NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_alm,b.id_compras),' ')  restriccion, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2(b.id_proveedor),1,60) historico, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2(b.id_proveedor),1,60) descripcion,
                        b.fecha_prov,
                        'K'||b.id_mov_comp memo,
                        b.id_mov_comp id_mov
                FROM ARON.almacen_ing_com a, ARON.compras_registro b 
                WHERE a.id_mov_comp = b.id_mov_comp 
                and b.id_cont = '".$mai_id."'
                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                and b.id_compras = '".$mai_id."'
                and b.voucher = '".$voucher."'
                AND a.estado = 'P' 
                and b.id_nivel_cont like '".$id_nivel."%' 
                UNION ALL
                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor)*-1,'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,'K'||g.memo memo,g.memo id_mov
                FROM ( 
                SELECT '2.1' orden, 
                        NVL(ARON.fc_cta_aasinet(a.id_cuenta_var,b.id_compras),' ') cuenta, ----61.**.**
                        NVL(ARON.fc_cta_cte_aasinet(a.id_cuenta_var,b.id_compras),' ') cta_cte, 
                        NVL(ARON.equiv_nivel(a.id_nivel_alm,b.id_compras),' ') departamento, 
                        NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0) valor,  
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_var,b.id_compras),' ')  restriccion, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||ARON.ruc2(b.id_proveedor)||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP'),1,60) historico, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063',' VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP')||'-'||ARON.ruc2(b.id_proveedor),1,60) descripcion,
                        b.fecha_prov,
                        b.id_mov_comp memo 
                FROM ARON.almacen_ing_com a, ARON.compras_registro b 
                WHERE a.id_mov_comp = b.id_mov_comp 
                and b.id_cont = '".$mai_id."'
                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                and b.id_compras = '".$mai_id."'
                and b.voucher = '".$voucher."'
                AND a.estado        = 'P' 
                and b.id_nivel_cont like '".$id_nivel."%' 
                )g 
                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                --ORDER BY FECHA_PROV,ID_MOV,ORDEN
                UNION ALL 																	
                SELECT 
                        '3.3' orden,   
                        nvl(ARON.fc_cta_aasinet(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 	
                        to_char(decode(A.tipo_doc,'08',A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'),'07',(A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(-1)),'99999999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov  
                FROM ARON.compras_notas A, ARON.compras_registro B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P' 
                and A.id_nivel_gasto like '".$id_nivel."%' 
                and A.tipo_bi = '5' 
                and A.id_cta_gasto >= '60.01.01' 
                UNION ALL
                SELECT  
                        '3.3' orden,   
                        nvl(ARON.fc_cta_aasinet(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                        to_char(decode(A.tipo_doc,'08',A.importe,A.importe*(-1)),'99999999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov  
                FROM ARON.compras_notas A, ARON.compras_registro B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P' 
                and A.id_nivel_gasto like '".$id_nivel."%' 
                and A.tipo_bi in ('3','4') 
                UNION ALL
                SELECT
                        '3.3' orden,   
                        nvl(ARON.fc_cta_aasinet(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                        to_char(decode(A.tipo_doc,'08',A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'),'07',(A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(-1)),'99999999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                FROM ARON.compras_notas A, ARON.compras_registro B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P' 
                and A.id_nivel_gasto like '".$id_nivel."%' 
                and A.tipo_bi = '1' 
                UNION ALL
                SELECT  
                        '3.3' orden,   
                        nvl(ARON.fc_cta_aasinet(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                        to_char(decode(A.tipo_doc,'08',A.importe,A.importe*(-1)),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                FROM ARON.compras_notas A, ARON.compras_registro B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                AND A.id_cont = '".$mai_id."'
                AND A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                AND A.estado = 'P' 
                AND A.id_nivel_gasto like '".$id_nivel."%' 
                AND A.tipo_bi = '2' 
                UNION ALL 								
                SELECT
                        '3.1' orden,   
                        nvl(ARON.fc_cta_aasinet(A.id_cta_igv,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_igv,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel('".$id_nivel."',A.id_compras),' ') departamento, 
                        to_char(decode(A.tipo_doc,'08',(A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')),((A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')))*(-1)),'99999999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_igv,A.id_compras),' ')  restriccion, 
                        'IGV Nota:	(Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                FROM ARON.compras_notas A, ARON.compras_registro B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                AND A.id_cont = '".$mai_id."'
                AND A.id_compras = '".$mai_id."'
                AND A.voucher = '".$voucher."'
                AND A.estado = 'P' 
                AND A.id_nivel_gasto like '".$id_nivel."%' 
                AND A.tipo_bi = '1'
                UNION ALL 
                SELECT 
                        '3.1' orden,   
                        nvl(ARON.fc_cta_aasinet('40.01.01',A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet('40.01.01',A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 						
                        to_char(decode(A.tipo_doc,'08',(A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')),((A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')))*(-1)),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet('40.01.01',A.id_compras),' ')  restriccion, 
                        'IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov   
                FROM ARON.compras_notas A, ARON.compras_registro B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P' 
                and A.id_nivel_gasto like '".$id_nivel."%' 
                and A.tipo_bi = '5' 
                and A.id_cta_gasto >= '60.01.01' 
                UNION ALL
                SELECT
                        '3' orden,   
                        nvl(ARON.fc_cta_aasinet('42.01.01',id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet('42.01.01',id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel('".$id_nivel."',id_compras),' ') departamento, 
                        to_char(nvl(sum(importe),0)*1,'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet('42.01.01',id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(serie,'0')||'-'||nvl(numdoc,'0')||')' historico, 
                        tipo_doc||': Nota: (Doc: '||nvl(serie,'0')||'-'||nvl(numdoc,'0')||')' descripcion,
                        fecha_prov,
                        'C'||id_mov_not memo , id_mov_not id_mov  
                FROM ARON.compras_notas 
                WHERE id_cont = '".$mai_id."'
                AND id_compras = '".$mai_id."'
                AND voucher = '".$voucher."'
                AND estado = 'P' 
                AND id_nivel_gasto like '".$id_nivel."%' 
                GROUP BY id_compras,importe,igv,id_mov_not,serie,numdoc,tipo_doc,fecha_prov 
                --ORDER BY FECHA_PROV,ID_MOV,ORDEN  								
                UNION ALL
                SELECT '4.1' orden, 
                        NVL(ARON.fc_cta_aasinet(a.id_cuenta_alm,b.id_compras),' ') cuenta,  ----20.**.**
                        NVL(ARON.fc_cta_cte_aasinet(a.id_cuenta_alm,b.id_compras),' ') cta_cte, 
                        NVL(ARON.equiv_nivel(a.id_nivel_alm,b.id_compras),' ') departamento, 
                        TO_CHAR(NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0)*-1,'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_alm,b.id_compras),' ')  restriccion,
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2(b.id_proveedor),1,60) historico, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2(b.id_proveedor),1,60) descripcion,
                        b.fecha_prov,
                        'K'||b.id_mov_comp memo,
                        b.id_mov_comp id_mov
                FROM ARON.almacen_ing_not a, ARON.compras_notas b 
                WHERE a.id_mov_not = b.id_mov_not
                and b.id_cont = '".$mai_id."'
                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                and b.id_compras = '".$mai_id."'
                and b.voucher = '".$voucher."'
                AND a.estado = 'P' 
                and b.id_nivel_gasto like '".$id_nivel."%' 
                UNION ALL
                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor)*-1,'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,'K'||g.memo memo,g.memo id_mov
                FROM ( 
                        SELECT '4' orden, 
                                NVL(ARON.fc_cta_aasinet(a.id_cuenta_var,b.id_compras),' ') cuenta, ----61.**.**
                                NVL(ARON.fc_cta_cte_aasinet(a.id_cuenta_var,b.id_compras),' ') cta_cte, 
                                NVL(ARON.equiv_nivel(b.id_nivel_gasto,b.id_compras),' ') departamento, 
                                NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0)*-1 valor,  
                                nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_var,b.id_compras),' ')  restriccion, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||ARON.ruc2(b.id_proveedor)||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP'),1,60) historico, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP')||'-'||ARON.ruc2(b.id_proveedor),1,60) descripcion,
                                b.fecha_prov,
                                b.id_mov_not memo 
                        FROM ARON.almacen_ing_not a, ARON.compras_notas b 
                        WHERE a.id_mov_not = b.id_mov_not 
                        and b.id_cont = '".$mai_id."'
                        AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                        and b.id_compras = '".$mai_id."'
                        and b.voucher = '".$voucher."'
                        AND a.estado        = 'P' 
                        and b.id_nivel_gasto like '".$id_nivel."%' 
                        )g 
                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                ORDER BY FECHA_PROV,ORDEN,ID_MOV ";
            $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetPurchases($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    RESTRICCION as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    DESCRIPCION as \"Description\", 
                                    MEMO as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                                SELECT 
                                        '1' orden, 
                                        nvl(ARON.fc_cta_aasinet(C.id_cta_gasto,A.id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(C.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel(C.id_nivel_gasto,A.id_compras),' ') departamento, 
                                        to_char(nvl(C.importe+NVL(DECODE(C.TIPO_BI,'2',C.IGV,0),0),0),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(C.id_cta_gasto,A.id_compras),' ')  restriccion,
                                        substr('(Doc: '||A.serie||'-'||A.numdoc||')-'||substr(C.detalle,1,25)||'-'||ARON.ruc2(A.id_proveedor),1,60) historico,
                                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||substr(C.detalle,1,30)||'-'||ARON.ruc2(A.id_proveedor),1,60) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                                FROM ARON.compras_registro A, ARON.compras_detalle C  
                                WHERE A.id_mov_comp = C.id_mov_comp 
                                and A.id_compras = C.id_compras 
                                AND A.id_cont = '".$mai_id."' 
                                and A.id_compras = '".$mai_id."'									
                                and A.estado = 'P' 
                                and A.id_nivel_cont like '".$id_nivel."%' 
                                and A.voucher = '".$voucher."'
                                UNION ALL
                                SELECT  
                                        '1.1' orden,   
                                        nvl(ARON.fc_cta_aasinet(C.id_cta_igv,A.id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(C.id_cta_igv,a.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel(A.id_nivel_cont,A.id_compras),' ') departamento, 
                                        to_char(nvl(C.igv,0),'99999999.99') valor, 
                                        ' ' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(C.id_cta_igv,A.id_compras),' ')  restriccion, 
                                        substr('(IGV: Doc: '||A.serie||'-'||A.numdoc||')-'||ARON.ruc2(A.id_proveedor),1,60) historico,
                                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||'IGV Compras Div.'||'-'||ARON.ruc2(A.id_proveedor),1,60) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                                FROM ARON.compras_registro A, ARON.compras_detalle C 
                                WHERE A.id_mov_comp = C.id_mov_comp 
                                and A.id_cont = '".$mai_id."'
                                and A.id_compras = '".$mai_id."'
                                and A.voucher = '".$voucher."' 
                                and A.estado = 'P' 
                                and A.id_nivel_cont like '".$id_nivel."%' 
                                and C.tipo_bi in ('1','5') 
                                UNION ALL
                                SELECT 
                                        '1.2' orden,   
                                        nvl(ARON.fc_cta_aasinet(b.id_cuenta_comp,a.id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(b.id_cuenta_comp,a.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel(a.id_nivel_cont,a.id_compras),' ') departamento, 
                                        to_char(nvl(sum(a.importe*-1),0),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(b.id_cuenta_comp,a.id_compras),' ')  restriccion, 
                                        substr(a.tipo_doc||':(Doc: '||A.serie||'-'||A.numdoc||')-'||ARON.ruc2(A.id_proveedor),1,60) historico,
                                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||'COMPRAS'||'-'||ARON.ruc2(A.id_proveedor),1,60) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                                FROM ARON.compras_registro a, ARON.compras_main b 
                                WHERE a.id_compras = b.id_compras 
                                and a.id_cont = '".$mai_id."'
                                and a.id_compras = '".$mai_id."'
                                and a.voucher = '".$voucher."'
                                and a.estado = 'P' 
                                and a.id_nivel_cont like '".$id_nivel."%' 
                                GROUP BY a.id_compras,b.id_cuenta_comp,a.id_nivel_cont,a.importe,a.tipo_doc,A.serie,A.numdoc,A.id_proveedor,A.id_mov_comp,A.tipo_doc,A.fecha_prov
                                UNION ALL
                                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor),'99999999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,
                                'C'||g.memo memo,g.memo id_mov 
                                FROM ( 
                                SELECT '1' orden, 
                                        NVL(ARON.fc_cta_aasinet(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_COMP),b.id_compras),' ') cuenta,   --60.**.**
                                        NVL(ARON.fc_cta_cte_aasinet(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_comp),b.id_compras),' ') cta_cte, 
                                        NVL(ARON.equiv_nivel(a.id_nivel_alm,b.id_compras),' ') departamento, 
                                        NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0) valor,  
                                        nvl(ARON.fc_restriccion_aasinet(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_COMP),b.id_compras),' ')  restriccion, 
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-' ||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',ARON.ruc2(b.id_proveedor)||' COMPRA MERCADERIAS BAZAR',ARON.alm_articulo(a.id_almacen,a.id_articulo)||ARON.ruc2(b.id_proveedor)),1,60) historico,
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-' ||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',ARON.ruc2(b.id_proveedor)||' COMPRA MERCADERIAS BAZAR',ARON.alm_articulo(a.id_almacen,a.id_articulo)||ARON.ruc2(b.id_proveedor)),1,60) descripcion,
                                        b.fecha_prov, 
                                        b.id_mov_comp memo 
                                FROM ARON.almacen_ing_com a,ARON.compras_registro b 
                                WHERE a.id_mov_comp = b.id_mov_comp 
                                and b.id_cont = '".$mai_id."'
                                and b.id_compras = '".$mai_id."'
                                and b.voucher = '".$voucher."'
                                AND a.estado        = 'P' 
                                and b.id_nivel_cont like '".$id_nivel."%' 
                                )g 
                                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                                UNION ALL
                                SELECT  
                                        '1.1' orden,     
                                        nvl(ARON.fc_cta_aasinet(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ') cuenta, 		
                                        nvl(ARON.fc_cta_cte_aasinet(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ') cta_cte, 									
                                        NVL(ARON.equiv_nivel(decode(SUBSTR(c.id_almacen,1,3),'051',a.id_nivel_cont,C.id_nivel_alm),A.id_compras),' ') departamento, 
                                        to_char(nvl(sum(C.igv),0),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ')  restriccion, 
                                        'IGV Comp Almacen '||'Doc: '||A.serie||'-'||A.numdoc historico,
                                        A.tipo_doc||'(Doc:'||A.serie||'-'||A.numdoc||') IGV Comp Almacen ' descripcion,  
                                        A.fecha_prov,
                                        'C'||A.id_mov_comp memo, 
                                        A.id_mov_comp id_mov 	
                                FROM ARON.compras_registro A, ARON.almacen_ing_com C 
                                WHERE A.id_mov_comp = C.id_mov_comp 
                                and A.id_cont = '".$mai_id."'
                                and A.id_compras = '".$mai_id."'
                                and A.voucher = '".$voucher."'
                                and A.estado = 'P'  
                                and C.tipo_bi in ('1','5')  /*--aumentado para ke solo separe el igv grabado */
                                and A.id_nivel_cont like '".$id_nivel."%' 
                                group by a.id_compras,A.id_mov_comp ,C.id_cuenta_igv,C.tipo_bi,C.id_nivel_alm,A.serie,A.numdoc,a.id_nivel_cont,c.id_almacen,A.tipo_doc,A.fecha_prov
                                having sum(C.igv) <> 0
                                UNION ALL
                                SELECT '2' orden, 
                                        NVL(ARON.fc_cta_aasinet(a.id_cuenta_alm,b.id_compras),' ') cuenta,  ----20.**.**
                                        NVL(ARON.fc_cta_cte_aasinet(a.id_cuenta_alm,b.id_compras),' ') cta_cte, 
                                        NVL(ARON.equiv_nivel(b.id_nivel_cont,b.id_compras),' ') departamento, 
                                        TO_CHAR(NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_alm,b.id_compras),' ')  restriccion, 
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2(b.id_proveedor),1,60) historico, 
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2(b.id_proveedor),1,60) descripcion,
                                        b.fecha_prov,
                                        'K'||b.id_mov_comp memo,
                                        b.id_mov_comp id_mov
                                FROM ARON.almacen_ing_com a, ARON.compras_registro b 
                                WHERE a.id_mov_comp = b.id_mov_comp 
                                and b.id_cont = '".$mai_id."'
                                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                                and b.id_compras = '".$mai_id."'
                                and b.voucher = '".$voucher."'
                                AND a.estado = 'P' 
                                and b.id_nivel_cont like '".$id_nivel."%' 
                                UNION ALL
                                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor)*-1,'99999999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,'K'||g.memo memo,g.memo id_mov
                                FROM ( 
                                SELECT '2.1' orden, 
                                        NVL(ARON.fc_cta_aasinet(a.id_cuenta_var,b.id_compras),' ') cuenta, ----61.**.**
                                        NVL(ARON.fc_cta_cte_aasinet(a.id_cuenta_var,b.id_compras),' ') cta_cte, 
                                        NVL(ARON.equiv_nivel(a.id_nivel_alm,b.id_compras),' ') departamento, 
                                        NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0) valor,  
                                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_var,b.id_compras),' ')  restriccion, 
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||ARON.ruc2(b.id_proveedor)||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP'),1,60) historico, 
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP')||'-'||ARON.ruc2(b.id_proveedor),1,60) descripcion,
                                        b.fecha_prov,
                                        b.id_mov_comp memo 
                                FROM ARON.almacen_ing_com a, ARON.compras_registro b 
                                WHERE a.id_mov_comp = b.id_mov_comp 
                                and b.id_cont = '".$mai_id."'
                                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                                and b.id_compras = '".$mai_id."'
                                and b.voucher = '".$voucher."'
                                AND a.estado        = 'P' 
                                and b.id_nivel_cont like '".$id_nivel."%' 
                                )g 
                                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                                --ORDER BY FECHA_PROV,ID_MOV,ORDEN
                                UNION ALL 																	
                                SELECT 
                                        '3.3' orden,   
                                        nvl(ARON.fc_cta_aasinet(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 	
                                        to_char(decode(A.tipo_doc,'08',A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'),'07',(A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(-1)),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_not memo , A.id_mov_not id_mov  
                                FROM ARON.compras_notas A, ARON.compras_registro B 
                                WHERE A.id_mov_comp = B.id_mov_comp 
                                and A.id_cont = '".$mai_id."'
                                and A.id_compras = '".$mai_id."'
                                and A.voucher = '".$voucher."'
                                and A.estado = 'P' 
                                and A.id_nivel_gasto like '".$id_nivel."%' 
                                and A.tipo_bi = '5' 
                                and A.id_cta_gasto >= '60.01.01' 
                                UNION ALL
                                SELECT  
                                        '3.3' orden,   
                                        nvl(ARON.fc_cta_aasinet(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                                        to_char(decode(A.tipo_doc,'08',A.importe,A.importe*(-1)),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_not memo , A.id_mov_not id_mov  
                                FROM ARON.compras_notas A, ARON.compras_registro B 
                                WHERE A.id_mov_comp = B.id_mov_comp 
                                and A.id_cont = '".$mai_id."'
                                and A.id_compras = '".$mai_id."'
                                and A.voucher = '".$voucher."'
                                and A.estado = 'P' 
                                and A.id_nivel_gasto like '".$id_nivel."%' 
                                and A.tipo_bi in ('3','4') 
                                UNION ALL
                                SELECT
                                        '3.3' orden,   
                                        nvl(ARON.fc_cta_aasinet(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                                        to_char(decode(A.tipo_doc,'08',A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'),'07',(A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(-1)),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                                FROM ARON.compras_notas A, ARON.compras_registro B 
                                WHERE A.id_mov_comp = B.id_mov_comp 
                                and A.id_cont = '".$mai_id."'
                                and A.id_compras = '".$mai_id."'
                                and A.voucher = '".$voucher."'
                                and A.estado = 'P' 
                                and A.id_nivel_gasto like '".$id_nivel."%' 
                                and A.tipo_bi = '1' 
                                UNION ALL
                                SELECT  
                                        '3.3' orden,   
                                        nvl(ARON.fc_cta_aasinet(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                                        to_char(decode(A.tipo_doc,'08',A.importe,A.importe*(-1)),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                                FROM ARON.compras_notas A, ARON.compras_registro B 
                                WHERE A.id_mov_comp = B.id_mov_comp 
                                AND A.id_cont = '".$mai_id."'
                                AND A.id_compras = '".$mai_id."'
                                and A.voucher = '".$voucher."'
                                AND A.estado = 'P' 
                                AND A.id_nivel_gasto like '".$id_nivel."%' 
                                AND A.tipo_bi = '2' 
                                UNION ALL 								
                                SELECT
                                        '3.2' orden,   
                                        nvl(ARON.fc_cta_aasinet(A.id_cta_igv,A.id_compras),' ') cuenta, 
                                        nvl(ARON.cta_cte(A.id_cta_igv,A.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel('".$id_nivel."',A.id_compras),' ') departamento, 
                                        to_char(decode(A.tipo_doc,'08',(A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')),((A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')))*(-1)),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(A.id_cta_igv,A.id_compras),' ')  restriccion, 
                                        'IGV Nota:	(Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                        A.tipo_doc||': IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                                FROM ARON.compras_notas A, ARON.compras_registro B 
                                WHERE A.id_mov_comp = B.id_mov_comp 
                                AND A.id_cont = '".$mai_id."'
                                AND A.id_compras = '".$mai_id."'
                                AND A.voucher = '".$voucher."'
                                AND A.estado = 'P' 
                                AND A.id_nivel_gasto like '".$id_nivel."%' 
                                AND A.tipo_bi = '1'
                                UNION ALL 
                                SELECT 
                                        '3.2' orden,   
                                        nvl(ARON.fc_cta_aasinet('40.01.01',A.id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet('40.01.01',A.id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel(A.id_nivel_gasto,A.id_compras),' ') departamento, 						
                                        to_char(decode(A.tipo_doc,'08',(A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')),((A.importe/to_number(1||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')))*(-1)),'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet('40.01.01',A.id_compras),' ')  restriccion, 
                                        'IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                        A.tipo_doc||': IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                        A.fecha_prov,
                                        'C'||A.id_mov_not memo , A.id_mov_not id_mov   
                                FROM ARON.compras_notas A, ARON.compras_registro B 
                                WHERE A.id_mov_comp = B.id_mov_comp 
                                and A.id_cont = '".$mai_id."'
                                and A.id_compras = '".$mai_id."'
                                and A.voucher = '".$voucher."'
                                and A.estado = 'P' 
                                and A.id_nivel_gasto like '".$id_nivel."%' 
                                and A.tipo_bi = '5' 
                                and A.id_cta_gasto >= '60.01.01' 
                                UNION ALL
                                SELECT
                                        '3' orden,   
                                        nvl(ARON.fc_cta_aasinet('42.01.01',id_compras),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet('42.01.01',id_compras),' ') cta_cte, 
                                        nvl(ARON.equiv_nivel('".$id_nivel."',id_compras),' ') departamento, 
                                        to_char(nvl(sum(importe),0)*1,'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet('42.01.01',id_compras),' ')  restriccion, 
                                        'Nota: (Doc: '||nvl(serie,'0')||'-'||nvl(numdoc,'0')||')' historico, 
                                        tipo_doc||': Nota: (Doc: '||nvl(serie,'0')||'-'||nvl(numdoc,'0')||')' descripcion,
                                        fecha_prov,
                                        'C'||id_mov_not memo , id_mov_not id_mov  
                                FROM ARON.compras_notas 
                                WHERE id_cont = '".$mai_id."'
                                AND id_compras = '".$mai_id."'
                                AND voucher = '".$voucher."'
                                AND estado = 'P' 
                                AND id_nivel_gasto like '".$id_nivel."%' 
                                GROUP BY id_compras,importe,igv,id_mov_not,serie,numdoc,tipo_doc,fecha_prov 
                                --ORDER BY FECHA_PROV,ID_MOV,ORDEN  								
                                UNION ALL
                                SELECT '4.1' orden, 
                                        NVL(ARON.fc_cta_aasinet(a.id_cuenta_alm,b.id_compras),' ') cuenta,  ----20.**.**
                                        NVL(ARON.fc_cta_cte_aasinet(a.id_cuenta_alm,b.id_compras),' ') cta_cte, 
                                        NVL(ARON.equiv_nivel(a.id_nivel_alm,b.id_compras),' ') departamento, 
                                        TO_CHAR(NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0)*-1,'99999999.99') valor, 
                                        '10' fondo, 
                                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_alm,b.id_compras),' ')  restriccion,
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2(b.id_proveedor),1,60) historico, 
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2(b.id_proveedor),1,60) descripcion,
                                        b.fecha_prov,
                                        'K'||b.id_mov_comp memo,
                                        b.id_mov_comp id_mov
                                FROM ARON.almacen_ing_not a, ARON.compras_notas b 
                                WHERE a.id_mov_not = b.id_mov_not
                                and b.id_cont = '".$mai_id."'
                                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                                and b.id_compras = '".$mai_id."'
                                and b.voucher = '".$voucher."'
                                AND a.estado = 'P' 
                                and b.id_nivel_gasto like '".$id_nivel."%' 
                                UNION ALL
                                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor)*-1,'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,'K'||g.memo memo,g.memo id_mov
                                FROM ( 
                                        SELECT '4' orden, 
                                                NVL(ARON.fc_cta_aasinet(a.id_cuenta_var,b.id_compras),' ') cuenta, ----61.**.**
                                                NVL(ARON.fc_cta_cte_aasinet(a.id_cuenta_var,b.id_compras),' ') cta_cte, 
                                                NVL(ARON.equiv_nivel(b.id_nivel_gasto,b.id_compras),' ') departamento, 
                                                NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0)*-1 valor,  
                                                nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_var,b.id_compras),' ')  restriccion, 
                                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||ARON.ruc2(b.id_proveedor)||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP'),1,60) historico, 
                                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP')||'-'||ARON.ruc2(b.id_proveedor),1,60) descripcion,
                                                b.fecha_prov,
                                                b.id_mov_not memo 
                                        FROM ARON.almacen_ing_not a, ARON.compras_notas b 
                                        WHERE a.id_mov_not = b.id_mov_not 
                                        and b.id_cont = '".$mai_id."'
                                        AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                                        and b.id_compras = '".$mai_id."'
                                        and b.voucher = '".$voucher."'
                                        AND a.estado        = 'P' 
                                        and b.id_nivel_gasto like '".$id_nivel."%' 
                                        )g 
                                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                                ORDER BY FECHA_PROV,ORDEN,ID_MOV
                    ) X ";
        $oQuery = DB::connection('oracleapp')->select($query);
        return $oQuery;
    }
    //CHEQUES
    public static function listVoucherCWAasinetCHQ($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        voucher, 
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.cheque_voucher 
                WHERE id_cheque = '".$id_compra."' 
                AND id_nivel = '".$id_depto."' 
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '18'
                AND ID_NIVEL = '".$id_depto."'
                AND TIPO = 'MB'
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetCHQ($id_nivel,$id_anho,$voucher){
        $mai_id = "001-".$id_anho;
        $query = "SELECT 
                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),' ') cta_cte,
                        '10' fondo,  					
                        nvl(decode(substr(a.id_nivel,1,1),
                        '1',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '2',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '3',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '4',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '5',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '6',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-11','61010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque))),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_cheque),' ')  restriccion, 					    
                        a.id_chequera, 					   
                        to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') valor, 
                                                to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99') imp,
                        substr(a.glosa_exp_ctr,1,30)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY') descripcion,
                        a.id_mov_efec MEMO,
                        '1' ORDEN 
                FROM ARON.cheque_detalle a, ARON.cheque_voucher b
                WHERE a.id_cheque = b.id_cheque 
                AND a.voucher = b.voucher 
                AND a.id_cheque = '".$mai_id."' 
                AND a.voucher = '".$voucher."' 
                AND a.id_nivel like '".$id_nivel."%' 
                AND b.id_nivel = '".$id_nivel."'  
                AND a.num_operacion like '%Cheq%' 
                UNION ALL
                SELECT
                        nvl(ARON.fc_cta_aasinet(id_cuenta_egre,a.id_cheque),' ') cuenta,
                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_egre,a.id_cheque),' ') cta_cte, 
                        '10' fondo,
                        nvl(ARON.equiv_nivel('".$id_nivel."',a.id_cheque),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,a.id_cheque),' ')  restriccion,
                        id_chequera,                  
                        to_char(sum(nvl(importe,0))*decode(dc,'D',-1,1),'999999999.99') importe,
                                                to_char(sum(nvl(otro_imp,0))*decode(dc,'D',-1,1),'999999999.99') imp,
                        'Total '||nvl(num_operacion,' ')||'-'||to_char(b.fecha,'DD/MM/YY') descripcion,
                        '' memo,
                        '1.1' ORDEN
                FROM ARON.cheque_detalle a, ARON.cheque_voucher b
                WHERE a.id_cheque = b.id_cheque
                AND a.voucher = b.voucher 
                AND substr(a.id_nivel,1,1) = b.id_nivel
                AND a.id_cheque = '".$mai_id."' 
                AND a.voucher = '".$voucher."'
                AND a.num_operacion like '%Cheq%' 
                AND a.id_nivel like '".$id_nivel."%' 
                GROUP BY a.id_cheque,a.id_cuenta_egre,a.id_chequera, a.num_operacion,b.fecha,a.dc
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),' ') cta_cte,  
                        '10' fondo,					
                        nvl(decode(substr(a.id_nivel,1,1),
                        '1',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),  
                        '2',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '3',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '4',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '5',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '6',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','61010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque))),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_cheque),' ')  restriccion,
                        a.id_chequera, 	 					    		                        
                        to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') importe,
                                                to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99') imp,
                        substr(a.glosa_exp_ctr,1,50) descripcion,
                        a.id_mov_efec MEMO,
                        '2' ORDEN      
                FROM ARON.cheque_detalle a, ARON.cheque_voucher b, ARON.egresos_chequera d 
                where a.id_cheque = b.id_cheque 
                and a.voucher = b.voucher 
                and a.id_cheque = d.id_compras 
                and a.id_chequera = d.id_chequera
                and a.id_cheque = '".$mai_id."' 
                and a.voucher = '".$voucher."'
                and a.id_nivel like '".$id_nivel."%' 
                and b.id_nivel = '".$id_nivel."' 
                and a.num_operacion like '%Oper%' 
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_cheque),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_egre,id_cheque),' ') cta_cte,
                        '10' fondo,
                        nvl(ARON.equiv_nivel('".$id_nivel."',id_cheque),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,id_cheque),' ')  restriccion,
                        id_chequera, 
                        to_char(sum(nvl(importe,0))*decode(dc,'C',1,-1),'999999999.99') importe,
                                                to_char(sum(nvl(otro_imp,0))*decode(dc,'C',1,-1),'999999999.99') imp,
                        num_operacion as descripcion, 
                        id_mov_efec MEMO,
                        '2.1' ORDEN 
                FROM ARON.cheque_detalle 
                WHERE id_cheque = '".$mai_id."' 
                AND voucher = '".$voucher."'
                AND num_operacion like '%Oper%' 
                AND id_nivel like '".$id_nivel."%' 
                group by id_cheque,id_mov_efec,id_cuenta_egre,id_chequera, num_operacion,dc
                --ORDER BY ORDEN,MEMO
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_egre,a.id_cheque),' ') cuenta, 
                        nvl(ARON.cta_cte(a.id_cuenta_egre,a.id_cheque),' ') cta_cte,  		
                        '10' fondo,			
                        nvl(decode(substr(a.id_nivel,1,1),
                        '1',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '2',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '3',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '4',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '5',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                        '6',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','61010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque))),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,id_cheque),' ')  restriccion, 
                        a.id_chequera,					                        
                        to_char(nvl(sum(a.importe),0)*decode(a.dc,'D',-1,1),'999999999.99') valor,
                                                to_char(nvl(sum(a.otro_imp),0)*decode(a.dc,'D',-1,1),'999999999.99') imp,
                        substr(ARON.prov_ret_xmov(a.id_mov_efec),1,12)||'-'||nvl(a.glosa,' ')||'-'||to_char(a.fecha,'dd/mm/yy') descripcion,
                        id_mov_efec MEMO,
                        '3' ORDEN 
                FROM ARON.cheque_detalle a, ARON.cont_plan b 
                WHERE a.id_cuenta_egre = b.id_cuenta 
                AND a.id_cheque = '".$mai_id."' 
                AND a.voucher = '".$voucher."'
                AND b.id_cont = '".$mai_id."' 
                AND a.id_nivel like '".$id_nivel."%' 
                AND a.tipo = 'RT' 
                GROUP BY id_cheque,id_cuenta_egre,a.id_cuenta_pag, dc,id_mov_efec,glosa,to_char(a.fecha,'dd/mm/yy'),id_chequera,a.id_nivel
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet(id_cuenta_pag,id_cheque),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_pag,id_cheque),' ') cta_cte,
                        '10' fondo,
                        nvl(ARON.equiv_nivel('".$id_nivel."',id_cheque),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_pag,id_cheque),' ')  restriccion,
                        id_chequera,
                        to_char(sum(nvl(importe,0))*decode(dc,'D',1,-1),'999999999.99') importe, 
                                                to_char(sum(nvl(otro_imp,0))*decode(dc,'D',1,-1),'999999999.99') imp,
                        substr(ARON.prov_ret_xmov(id_mov_efec),1,12)||'-'||nvl(glosa,' ')||'-'||to_char(fecha,'dd/mm/yy') historico,
                        id_mov_efec MEMO,
                        '3' ORDEN 
                FROM ARON.cheque_detalle 
                WHERE id_cheque = '".$mai_id."' 
                AND id_nivel like '".$id_nivel."%' 
                AND tipo = 'RT' 
                AND voucher = '".$voucher."'
                GROUP BY id_cheque,id_cuenta_pag, dc,id_mov_efec,glosa,fecha,id_chequera
                ORDER BY ORDEN,cuenta,MEMO,valor ";
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetCHQ($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    RESTRICCION as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    imp as \"CurrencyAmount\", 
                                    DESCRIPCION as \"Description\", 
                                    MEMO as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),' ') cta_cte,
                                        '10' fondo,  					
                                        nvl(decode(substr(a.id_nivel,1,1),
                                        '1',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '2',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '3',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '4',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '5',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '6',decode(ARON.equiv(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-11','61010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque))),' ') departamento,
                                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_cheque),' ')  restriccion, 					    
                                        a.id_chequera, 					   
                                        to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') valor, 
                                        decode(nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque),' '),'1112025',to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99'),'2162001',to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99'),'') imp,
                                        substr(a.glosa_exp_ctr,1,30)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY') descripcion,
                                        a.id_mov_efec MEMO,
                                        '1' ORDEN 
                                FROM ARON.cheque_detalle a, ARON.cheque_voucher b
                                WHERE a.id_cheque = b.id_cheque 
                                AND a.voucher = b.voucher 
                                AND a.id_cheque = '".$mai_id."' 
                                AND a.voucher = '".$voucher."' 
                                AND a.id_nivel like '".$id_nivel."%' 
                                AND b.id_nivel = '".$id_nivel."'  
                                AND a.num_operacion like '%Cheq%' 
                                UNION ALL
                                SELECT
                                        nvl(ARON.fc_cta_aasinet(id_cuenta_egre,a.id_cheque),' ') cuenta,
                                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_egre,a.id_cheque),' ') cta_cte, 
                                        '10' fondo,
                                        nvl(ARON.equiv_nivel('".$id_nivel."',a.id_cheque),' ') departamento,
                                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,a.id_cheque),' ')  restriccion,
                                        id_chequera,                  
                                        to_char(sum(nvl(importe,0))*decode(dc,'D',-1,1),'999999999.99') importe,
                                                                        decode(nvl(ARON.fc_cta_aasinet(id_cuenta_egre,a.id_cheque),' '),'1112025',to_char(sum(nvl(otro_imp,0))*decode(dc,'D',-1,1),'999999999.99'),'2162001',to_char(sum(nvl(otro_imp,0))*decode(dc,'D',-1,1),'999999999.99'),'') imp,
                                        'Total '||nvl(num_operacion,' ')||'-'||to_char(b.fecha,'DD/MM/YY') descripcion,
                                        '' memo,
                                        '1.1' ORDEN
                                FROM ARON.cheque_detalle a, ARON.cheque_voucher b
                                WHERE a.id_cheque = b.id_cheque
                                AND a.voucher = b.voucher 
                                AND substr(a.id_nivel,1,1) = b.id_nivel
                                AND a.id_cheque = '".$mai_id."' 
                                AND a.voucher = '".$voucher."'
                                AND a.num_operacion like '%Cheq%' 
                                AND a.id_nivel like '".$id_nivel."%' 
                                GROUP BY a.id_cheque,a.id_cuenta_egre,a.id_chequera, a.num_operacion,b.fecha,a.dc
                                UNION ALL
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),' ') cta_cte,  
                                        '10' fondo,					
                                        nvl(decode(substr(a.id_nivel,1,1),
                                        '1',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),  
                                        '2',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '3',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '4',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '5',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '6',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_pag,a.id_cheque),'1132001-2','61010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque))),' ') departamento,
                                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_pag,a.id_cheque),' ')  restriccion,
                                        a.id_chequera, 	 					    		                        
                                        to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') importe,
                                                                        decode(nvl(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque),' '),'1112025',to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99'),'2162001',to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99'),'') imp,
                                        substr(a.glosa_exp_ctr,1,50) descripcion,
                                        a.id_mov_efec MEMO,
                                        '2' ORDEN      
                                FROM ARON.cheque_detalle a, ARON.cheque_voucher b, ARON.egresos_chequera d 
                                where a.id_cheque = b.id_cheque 
                                and a.voucher = b.voucher 
                                and a.id_cheque = d.id_compras 
                                and a.id_chequera = d.id_chequera
                                and a.id_cheque = '".$mai_id."' 
                                and a.voucher = '".$voucher."'
                                and a.id_nivel like '".$id_nivel."%' 
                                and b.id_nivel = '".$id_nivel."' 
                                and a.num_operacion like '%Oper%' 
                                UNION ALL
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_cheque),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_egre,id_cheque),' ') cta_cte,
                                        '10' fondo,
                                        nvl(ARON.equiv_nivel('".$id_nivel."',id_cheque),' ') departamento,
                                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,id_cheque),' ')  restriccion,
                                        id_chequera, 
                                        to_char(sum(nvl(importe,0))*decode(dc,'C',1,-1),'999999999.99') importe,
                                                                        decode(nvl(ARON.fc_cta_aasinet(id_cuenta_egre,id_cheque),' '),'1112025',to_char(sum(nvl(otro_imp,0))*decode(dc,'C',1,-1),'999999999.99'),'2162001',to_char(sum(nvl(otro_imp,0))*decode(dc,'C',1,-1),'999999999.99'),'') imp,
                                        num_operacion, 
                                        id_mov_efec MEMO,
                                        '2.1' ORDEN 
                                FROM ARON.cheque_detalle 
                                WHERE id_cheque = '".$mai_id."' 
                                AND voucher = '".$voucher."'
                                AND num_operacion like '%Oper%' 
                                AND id_nivel like '".$id_nivel."%' 
                                group by id_cheque,id_mov_efec,id_cuenta_egre,id_chequera, num_operacion,dc
                                --ORDER BY ORDEN,MEMO
                                UNION ALL
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_egre,a.id_cheque),' ') cuenta, 
                                        nvl(ARON.cta_cte(a.id_cuenta_egre,a.id_cheque),' ') cta_cte,  		
                                        '10' fondo,			
                                        nvl(decode(substr(a.id_nivel,1,1),
                                        '1',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '2',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '3',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '4',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '5',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque)),
                                        '6',decode(ARON.fc_cta_aasinet(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte(a.id_cuenta_pag,a.id_cheque),'1132001-2','61010101',ARON.equiv_nivel(a.id_nivel,a.id_cheque))),' ') departamento,
                                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_egre,id_cheque),' ')  restriccion, 
                                        a.id_chequera,					                        
                                        to_char(nvl(sum(a.importe),0)*decode(a.dc,'D',-1,1),'999999999.99') valor,
                                                                        decode(nvl(ARON.fc_cta_aasinet(a.id_cuenta_egre,a.id_cheque),' '),'1112025',to_char(nvl(sum(a.otro_imp),0)*decode(a.dc,'D',-1,1),'999999999.99'),'2162001',to_char(nvl(sum(a.otro_imp),0)*decode(a.dc,'D',-1,1),'999999999.99'),'') imp,	
                                        substr(ARON.prov_ret_xmov(a.id_mov_efec),1,12)||'-'||nvl(a.glosa,' ')||'-'||to_char(a.fecha,'dd/mm/yy') descripcion,
                                        id_mov_efec MEMO,
                                        '3' ORDEN 
                                FROM ARON.cheque_detalle a, ARON.cont_plan b 
                                WHERE a.id_cuenta_egre = b.id_cuenta 
                                AND a.id_cheque = '".$mai_id."' 
                                AND a.voucher = '".$voucher."'
                                AND b.id_cont = '".$mai_id."' 
                                AND a.id_nivel like '".$id_nivel."%' 
                                AND a.tipo = 'RT' 
                                GROUP BY id_cheque,id_cuenta_egre,a.id_cuenta_pag, dc,id_mov_efec,glosa,to_char(a.fecha,'dd/mm/yy'),id_chequera,a.id_nivel
                                UNION ALL
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet(id_cuenta_pag,id_cheque),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_pag,id_cheque),' ') cta_cte,
                                        '10' fondo,
                                        nvl(ARON.equiv_nivel('".$id_nivel."',id_cheque),' ') departamento,
                                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_pag,id_cheque),' ')  restriccion,
                                        id_chequera,
                                        to_char(sum(nvl(importe,0))*decode(dc,'D',1,-1),'999999999.99') importe,
                                                                        decode(nvl(ARON.fc_cta_aasinet(id_cuenta_pag,id_cheque),' '),'1112025',to_char(sum(nvl(otro_imp,0))*decode(dc,'D',1,-1),'999999999.99'),'2162001',to_char(sum(nvl(otro_imp,0))*decode(dc,'D',1,-1),'999999999.99'),'') imp,
                                        substr(ARON.prov_ret_xmov(id_mov_efec),1,12)||'-'||nvl(glosa,' ')||'-'||to_char(fecha,'dd/mm/yy') historico,
                                        id_mov_efec MEMO,
                                        '3' ORDEN 
                                FROM ARON.cheque_detalle 
                                WHERE id_cheque = '".$mai_id."' 
                                AND id_nivel like '".$id_nivel."%' 
                                AND tipo = 'RT' 
                                AND voucher = '".$voucher."'
                                GROUP BY id_cheque,id_cuenta_pag, dc,id_mov_efec,glosa,fecha,id_chequera
                                ORDER BY ORDEN,cuenta,MEMO,valor
                    ) X ";
        $oQuery = DB::connection('oracleapp')->select($query);
        return $oQuery;
    }
    //VENTAS IMPRENTA
    public static function listVoucherCWAasinetSalesIMP($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        VOUCHER,
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.VENTAS_VOUCHER
                WHERE ID_VENTA = '".$id_compra."'
                AND ID_NIVEL = '3'
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '11'
                AND ID_NIVEL = '3'
                AND TIPO = 'RV' --RV: LOTE ASSINET
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetIMP($id_nivel,$id_anho,$voucher){
        $id_nivel = "3";
        $mai_id = "001-".$id_anho;

        $sql = "SELECT 
                        VOUCHER,
                        to_char(fecha,'MM') mes
                FROM ARON.VENTAS_VOUCHER
                WHERE ID_VENTA = '".$mai_id."'
                AND ID_NIVEL = '".$id_nivel."' 
                AND VOUCHER = '".$voucher."' ";
        $data = DB::connection('oracleapp')->select($sql);
        foreach ($data as $item){
                $mes_id = $item->mes;
        }

        $query = "SELECT 
                        '3151501' cuenta, 
                        decode(a.tipo_venta,'10','2','20','5',decode(a.tipodoc,'01','2','02','2','5')) cta_cte, 
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet('70.03.06',a.id_venta),' ')  restriccion,
                        decode(a.canal,'',decode(a.tipo_venta,'10','31010101','20','31010101',decode(a.serie,'14','31010101','31010101')),(select nivel from ARON.IMP_BLOCK d where d.id_venta=a.id_venta and d.id_block=a.CANAL)) departamento,  
                        to_char(nvl(a.base_imp,0)*-1,'99999999.99') valor,						
                        substr(decode(tipo_venta,'10','(Vta.Afec','20','(Vta.Exo',decode(tipodoc,'01','(Fac.Afec','02','(Bol.Afec','03','(Fac.Inaf','04','(Bol.Inaf'))||':'||a.serie||'-'||a.numdoc||')-'||substr(a.detalle,1,30)||'-'||ARON.ruc(id_personal),0,70) descripcion,  
                        a.serie,a.numdoc,'1' orden, 
                        a.id_mov_vnt memo
                from ARON.imp_regventas a  
                where a.id_venta = '".$mai_id."'
                and a.estado = 'V'  
                and a.id_nivel_cont like '".$id_nivel."%'
                and to_char(a.fecha, 'MM') = LPAD('".$mes_id."',2,0)
                and a.voucher = '".$voucher."'
                union all  
                SELECT   
                        '3151501' cuenta,   
                        decode(a.tipo_igv,'10','2','5') cta_cte, 
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet('70.03.06',a.id_venta),' ')  restriccion,
                        decode(b.canal,'',decode(a.serie,'14','31010101','31010101'),(select d.nivel from ARON.IMP_BLOCK d where d.id_venta=a.id_venta and d.id_block=b.CANAL)) departamento,  
                        to_char(a.importe-a.IGV*decode(a.dc,'D',-1,1),'999999999.99') valor,
                        substr(' (Doc:'||a.serie||'-'||nvl(a.numnot,' ')||')-'||substr(a.glosa,1,50),0,50) descripcion,  
                        a.serie,a.numnot,'1.1' orden, 
                        id_mov_not memo   
                FROM ARON.imp_notas a inner join  ARON.IMP_REGVENTAS b  
                on a.id_venta = b.id_venta  
                and a.ESTADO= b.ESTADO  
                and a.ID_MOV_VNT = B.ID_MOV_VNT  
                and a.id_venta = '".$mai_id."'
                and to_char(a.fecha_doc,'MM') = LPAD('".$mes_id."',2,0)
                and a.voucher = '".$voucher."'
                and a.estado = 'V' 
                union all  					
                SELECT  
                        nvl(ARON.fc_cta_aasinet(b.id_cuenta_igv,b.id_venta),' ') cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(b.id_cuenta_igv,b.id_venta),' ') cta_cte,  
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet(b.id_cuenta_igv,b.id_venta),' ')  restriccion,
                        nvl(ARON.equiv_nivel(b.id_nivel_cont,b.id_venta),' ') departamento,  	
                        to_char(nvl(sum(b.igv)*-1,0),'99999999.99') valor,  
                        '(Doc:'||b.serie||'-'||b.numdoc||')-IGV de ventas '||decode(LPAD('".$mes_id."',2,0),'01','Enero','02','Febrero','03','Marzo','04','Abril','05','Mayo','06','Junio','07','Julio','08','Agosto','09','Septiembre','10','Octubre','11','Noviembre','Diciembre') descripcion,  
                        '' serie,'' numdoc,	'2' orden, 
                        id_mov_vnt memo   
                from ARON.imp_regventas b  
                where b.id_venta = '".$mai_id."'
                and b.estado = 'V'  
                and to_char(b.fecha,'MM') = LPAD('".$mes_id."',2,0)
                and b.voucher = '".$voucher."'
                group by id_venta,id_cuenta_igv,id_nivel_cont,id_mov_vnt,tipodoc,serie,numdoc,detalle,tipo_venta 
                HAVING SUM(nvl(igv,0))+SUM(nvl(igv_me,0)) <> 0 
                union all  
                SELECT  
                        nvl(ARON.fc_cta_aasinet(id_cuenta_igv,id_venta),' ') cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_igv,id_venta),' ') cta_cte,  
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_igv,id_venta),' ')  restriccion,
                        nvl(ARON.equiv_nivel(id_nivel_cont,id_venta),' ') departamento,  
                        to_char(nvl(sum(igv),0),'99999999.99') valor,  
                        '(Doc:'||serie||'-'||numnot||')-''IGV de Notas '||decode(LPAD('".$mes_id."',2,0),'01','Enero','02','Febrero','03','Marzo','04','Abril','05','Mayo','06','Junio','07','Julio','08','Agosto','09','Septiembre','10','Octubre','11','Noviembre','Diciembre') descripcion,  
                        '' serie,'' numdoc,	'2.1' orden, 
                        id_mov_not memo  
                from ARON.imp_notas 
                where id_venta = '".$mai_id."'
                and estado = 'V'  
                and to_char(fecha_doc,'MM') = LPAD('".$mes_id."',2,0)
                and voucher = '".$voucher."'
                group by id_venta,id_cuenta_igv,id_nivel_cont,id_mov_not,serie,numnot,glosa   					
                union all 
                SELECT  
                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_cli,a.id_venta),' ') cuenta,  
                        nvl(ARON.cta_cte_imp(a.id_personal,a.id_subcli),' ') cta_cte, 
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_cli,a.id_venta),' ')  restriccion,
                        nvl(ARON.equiv_nivel(a.id_nivel_cont,a.id_venta),' ') departamento, 
                        to_char(nvl(a.importe,0),'99999999.99') valor,  
                        substr(decode(a.tipo_venta,'10','(Vta.Afec','20','(Vta.Exo',decode(a.tipodoc,'01','(Fac.Afec','02','(Bol.Afec','03','(Fac.Inaf','04','(Bol.Inaf'))||':'||a.serie||'-'||a.numdoc||')-'||substr(a.detalle,1,30)||'-'||ARON.ruc(a.id_personal),0,70) descripcion,  
                        a.serie,a.numdoc ,'3' orden,
                        id_mov_vnt memo  
                from ARON.imp_regventas a  
                where a.id_venta = '".$mai_id."'
                and a.estado = 'V' 
                and to_char(a.fecha,'MM') = LPAD('".$mes_id."',2,0)
                and a.voucher = '".$voucher."'
                union all  
                SELECT  
                        nvl(ARON.fc_cta_aasinet(A.id_cuenta_cli,A.id_venta),' ') cuenta,  
                        nvl(ARON.cta_cte_imp(B.id_personal,B.id_subcli),' ') cta_cte,  
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet(A.id_cuenta_cli,A.id_venta),' ')  restriccion,
                        --decode(b.canal,'',nvl(ARON.equiv_nivel(a.id_nivel_cont,a.id_venta),' '),(select nivel from ARON.IMP_BLOCK d where d.id_venta=a.id_venta and d.id_block=b.CANAL)) departamento,  
                        nvl(nvl(ARON.equiv_nivel(a.id_nivel_cont,a.id_venta),' '),(select nivel from ARON.IMP_BLOCK d where d.id_venta=a.id_venta and d.id_block=b.CANAL)) departamento,  
                        trim(to_char(A.importe*decode(A.dc,'D',1,-1),'99999999.99')) valor, 
                        substr(' (Doc:'||A.serie||'-'||nvl(A.numnot,' ')||')-'||substr(A.glosa,1,50),0,50) descripcion,  
                        A.serie,A.numnot,'3.1' orden,
                        A.id_mov_not memo   
                from ARON.imp_notas A, ARON.imp_regventas B   
                where A.id_mov_vnt = B.id_mov_vnt   
                and A.id_venta = '".$mai_id."'
                and to_char(A.fecha_doc,'MM') = LPAD('".$mes_id."',2,0)
                and A.voucher = '".$voucher."'
                and A.estado = 'V'  
                UNION ALL 
                SELECT  	
                        '3151501' cuenta, 
                        decode(b.tipo_venta,'10','2','20','5',decode(b.tipodoc,'01','2','02','2','5')) cta_cte, 
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet('70.03.06',B.id_venta),' ')  restriccion,
                        decode(b.canal,'',decode(b.tipo_venta,'10','31010101','20','31010101',decode(b.serie,'14','31010101','31010101')),(select nivel from ARON.IMP_BLOCK d where d.id_venta=b.id_venta and d.id_block=b.CANAL)) departamento,  
                        trim(to_char(B.BASE_IMP*decode(A.dc,'D',1,1),'99999999.99')) valor, 
                        substr(' (Doc:'||A.serie||'-'||nvl(A.numnot,' ')||')-'||substr(A.glosa,1,50),0,50) descripcion,  
                        A.serie,A.numnot,'3.1' orden,
                        A.id_mov_not memo   
                from ARON.imp_notas A, ARON.imp_regventas B   
                where A.id_mov_vnt = B.id_mov_vnt   
                and A.id_venta = '".$mai_id."'
                and to_char(A.fecha_doc,'MM') = LPAD('".$mes_id."',2,0)
                and A.voucher = '".$voucher."'
                and A.estado = 'V'  	
                order by memo,cuenta  desc ";
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetIMP($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    fondo as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    restriccion as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    descripcion as \"Description\", 
                                    memo as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                                SELECT 
                                        '3151501' cuenta, 
                                        decode(a.tipo_venta,'10','2','20','5',decode(a.tipodoc,'01','2','02','2','5')) cta_cte, 
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet('70.03.06',a.id_venta),' ')  restriccion,
                                        decode(a.canal,'',decode(a.tipo_venta,'10','31010101','20','31010101',decode(a.serie,'14','31010101','31010101')),(select nivel from ARON.IMP_BLOCK d where d.id_venta=a.id_venta and d.id_block=a.CANAL)) departamento,  
                                        to_char(nvl(a.base_imp,0)*-1,'99999999.99') valor,						
                                        substr(decode(tipo_venta,'10','(Vta.Afec','20','(Vta.Exo',decode(tipodoc,'01','(Fac.Afec','02','(Bol.Afec','03','(Fac.Inaf','04','(Bol.Inaf'))||':'||a.serie||'-'||a.numdoc||')-'||substr(a.detalle,1,30)||'-'||ARON.ruc(id_personal),0,70) descripcion,  
                                        a.serie,a.numdoc,'1' orden, 
                                        a.id_mov_vnt memo
                                from ARON.imp_regventas a  
                                where a.id_venta = '".$mai_id."'
                                and a.estado = 'V'  
                                and a.id_nivel_cont like '".$id_nivel."%'
                                and to_char(a.fecha, 'MM') = LPAD('".$mes_id."',2,0)
                                and a.voucher = '".$voucher."'
                                union all  
                                SELECT   
                                        '3151501' cuenta,   
                                        decode(a.tipo_igv,'10','2','5') cta_cte, 
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet('70.03.06',a.id_venta),' ')  restriccion,
                                        decode(b.canal,'',decode(a.serie,'14','31010101','31010101'),(select d.nivel from ARON.IMP_BLOCK d where d.id_venta=a.id_venta and d.id_block=b.CANAL)) departamento,  
                                        to_char(a.importe-a.IGV*decode(a.dc,'D',-1,1),'999999999.99') valor,
                                        substr(' (Doc:'||a.serie||'-'||nvl(a.numnot,' ')||')-'||substr(a.glosa,1,50),0,50) descripcion,  
                                        a.serie,a.numnot,'1.1' orden, 
                                        id_mov_not memo   
                                FROM ARON.imp_notas a inner join  ARON.IMP_REGVENTAS b  
                                on a.id_venta = b.id_venta  
                                and a.ESTADO= b.ESTADO  
                                and a.ID_MOV_VNT = B.ID_MOV_VNT  
                                and a.id_venta = '".$mai_id."'
                                and to_char(a.fecha_doc,'MM') = LPAD('".$mes_id."',2,0)
                                and a.voucher = '".$voucher."'
                                and a.estado = 'V' 
                                union all  					
                                SELECT  
                                        nvl(ARON.fc_cta_aasinet(b.id_cuenta_igv,b.id_venta),' ') cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(b.id_cuenta_igv,b.id_venta),' ') cta_cte,  
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet(b.id_cuenta_igv,b.id_venta),' ')  restriccion,
                                        nvl(ARON.equiv_nivel(b.id_nivel_cont,b.id_venta),' ') departamento,  	
                                        to_char(nvl(sum(b.igv)*-1,0),'99999999.99') valor,  
                                        '(Doc:'||b.serie||'-'||b.numdoc||')-IGV de ventas '||decode(LPAD('".$mes_id."',2,0),'01','Enero','02','Febrero','03','Marzo','04','Abril','05','Mayo','06','Junio','07','Julio','08','Agosto','09','Septiembre','10','Octubre','11','Noviembre','Diciembre') descripcion,  
                                        '' serie,'' numdoc,	'2' orden, 
                                        id_mov_vnt memo   
                                from ARON.imp_regventas b  
                                where b.id_venta = '".$mai_id."'
                                and b.estado = 'V'  
                                and to_char(b.fecha,'MM') = LPAD('".$mes_id."',2,0)
                                and b.voucher = '".$voucher."'
                                group by id_venta,id_cuenta_igv,id_nivel_cont,id_mov_vnt,tipodoc,serie,numdoc,detalle,tipo_venta 
                                HAVING SUM(nvl(igv,0))+SUM(nvl(igv_me,0)) <> 0 
                                union all  
                                SELECT  
                                        nvl(ARON.fc_cta_aasinet(id_cuenta_igv,id_venta),' ') cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet(id_cuenta_igv,id_venta),' ') cta_cte,  
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet(id_cuenta_igv,id_venta),' ')  restriccion,
                                        nvl(ARON.equiv_nivel(id_nivel_cont,id_venta),' ') departamento,  
                                        to_char(nvl(sum(igv),0),'99999999.99') valor,  
                                        '(Doc:'||serie||'-'||numnot||')-''IGV de Notas '||decode(LPAD('".$mes_id."',2,0),'01','Enero','02','Febrero','03','Marzo','04','Abril','05','Mayo','06','Junio','07','Julio','08','Agosto','09','Septiembre','10','Octubre','11','Noviembre','Diciembre') descripcion,  
                                        '' serie,'' numdoc,	'2.1' orden, 
                                        id_mov_not memo  
                                from ARON.imp_notas 
                                where id_venta = '".$mai_id."'
                                and estado = 'V'  
                                and to_char(fecha_doc,'MM') = LPAD('".$mes_id."',2,0)
                                and voucher = '".$voucher."'
                                group by id_venta,id_cuenta_igv,id_nivel_cont,id_mov_not,serie,numnot,glosa   					
                                union all 
                                SELECT  
                                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_cli,a.id_venta),' ') cuenta,  
                                        nvl(ARON.cta_cte_imp(a.id_personal,a.id_subcli),' ') cta_cte, 
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_cli,a.id_venta),' ')  restriccion,
                                        nvl(ARON.equiv_nivel(a.id_nivel_cont,a.id_venta),' ') departamento, 
                                        to_char(nvl(a.importe,0),'99999999.99') valor,  
                                        substr(decode(a.tipo_venta,'10','(Vta.Afec','20','(Vta.Exo',decode(a.tipodoc,'01','(Fac.Afec','02','(Bol.Afec','03','(Fac.Inaf','04','(Bol.Inaf'))||':'||a.serie||'-'||a.numdoc||')-'||substr(a.detalle,1,30)||'-'||ARON.ruc(a.id_personal),0,70) descripcion,  
                                        a.serie,a.numdoc ,'3' orden,
                                        id_mov_vnt memo  
                                from ARON.imp_regventas a  
                                where a.id_venta = '".$mai_id."'
                                and a.estado = 'V' 
                                and to_char(a.fecha,'MM') = LPAD('".$mes_id."',2,0)
                                and a.voucher = '".$voucher."'
                                union all  
                                SELECT  
                                        nvl(ARON.fc_cta_aasinet(A.id_cuenta_cli,A.id_venta),' ') cuenta,  
                                        nvl(ARON.cta_cte_imp(B.id_personal,B.id_subcli),' ') cta_cte,  
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet(A.id_cuenta_cli,A.id_venta),' ')  restriccion,
                                        --decode(b.canal,'',nvl(ARON.equiv_nivel(a.id_nivel_cont,a.id_venta),' '),(select nivel from ARON.IMP_BLOCK d where d.id_venta=a.id_venta and d.id_block=b.CANAL)) departamento,  
                                        nvl(nvl(ARON.equiv_nivel(a.id_nivel_cont,a.id_venta),' '),(select nivel from ARON.IMP_BLOCK d where d.id_venta=a.id_venta and d.id_block=b.CANAL)) departamento,  
                                        trim(to_char(A.importe*decode(A.dc,'D',1,-1),'99999999.99')) valor, 
                                        substr(' (Doc:'||A.serie||'-'||nvl(A.numnot,' ')||')-'||substr(A.glosa,1,50),0,50) descripcion,  
                                        A.serie,A.numnot,'3.1' orden,
                                        A.id_mov_not memo   
                                from ARON.imp_notas A, ARON.imp_regventas B   
                                where A.id_mov_vnt = B.id_mov_vnt   
                                and A.id_venta = '".$mai_id."'
                                and to_char(A.fecha_doc,'MM') = LPAD('".$mes_id."',2,0)
                                and A.voucher = '".$voucher."'
                                and A.estado = 'V'  
                                UNION ALL 
                                SELECT  	
                                        '3151501' cuenta, 
                                        decode(b.tipo_venta,'10','2','20','5',decode(b.tipodoc,'01','2','02','2','5')) cta_cte, 
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet('70.03.06',B.id_venta),' ')  restriccion,
                                        decode(b.canal,'',decode(b.tipo_venta,'10','31010101','20','31010101',decode(b.serie,'14','31010101','31010101')),(select nivel from ARON.IMP_BLOCK d where d.id_venta=b.id_venta and d.id_block=b.CANAL)) departamento,  
                                        trim(to_char(B.BASE_IMP*decode(A.dc,'D',1,1),'99999999.99')) valor, 
                                        substr(' (Doc:'||A.serie||'-'||nvl(A.numnot,' ')||')-'||substr(A.glosa,1,50),0,50) descripcion,  
                                        A.serie,A.numnot,'3.1' orden,
                                        A.id_mov_not memo   
                                from ARON.imp_notas A, ARON.imp_regventas B   
                                where A.id_mov_vnt = B.id_mov_vnt   
                                and A.id_venta = '".$mai_id."'
                                and to_char(A.fecha_doc,'MM') = LPAD('".$mes_id."',2,0)
                                and A.voucher = '".$voucher."'
                                and A.estado = 'V'  	
                                order by memo,cuenta  desc   
                    ) X ";
        $oQuery = DB::connection('oracleapp')->select($query);
        return $oQuery;
    }
    //OPTIMUS
    public static function listVoucherCWAasinetSalesOPT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        to_char(A.fecha,'DD/MM/YYYY') fecha,to_char(A.fecha,'DD') dia, 
                        A.voucher,nvl(A.lote,'X') lote_ctr,
                        A.ID_NIVEL
                FROM ARON.mkt_voucher A
                WHERE A.ID_VENTA = '".$id_compra."' 
                AND TO_CHAR(A.FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND A.ID_NIVEL LIKE '".$id_depto."%' 
                AND A.TIPO = '1' 
                AND A.VOUCHER NOT IN ( 
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS 
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '22' 
                AND ID_NIVEL = '".$id_depto."'
                AND TIPO = 'RV'
                )
                ORDER BY FECHA,VOUCHER "; 
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetOPT($id_nivel,$id_anho,$voucher){
        $id_nivel = "3";
        $mai_id = "001-".$id_anho;

        $query = "SELECT a.serie,a.NUMDOC,4 ord, 
                        nvl(ARON.fc_cta_aasinet(a.ID_CUENTA_VNT,a.id_venta),' ') as cuenta, 
                        DECODE(b.TIPO_IGV,'10',NVL((select ID_CTA_CTE from ARON.cont_equiv where ID_CONT = a.ID_VENTA and ID_CUENTA=ID_CUENTA_VNT group by ID_CTA_CTE),' '),'30','1','5') cta_cte, 
                        nvl(ARON.equiv(a.ID_NIVEL_VNT,a.id_venta),' ')  departamento, 
                        to_char(SUM(b.base+nvl(b.descuento,0))*-1,'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_VNT,a.id_venta),' ')  restriccion, 
                        decode(a.TIPODOC,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(    a.id_nivel_cont,'1.03.07','VENTA CAFETIN','1.03.02','VENTA BAZAR','1.02.10','VENTA GRASS SINTETICO','1.03.04','VENTA COMEDOR','1.02.09','VENTA PISCINA','1.01.24','FONDO EDITORIAL','1.03.09','VENTA TUNAS','3.03.09','VENTAS IMP',' ') descripcion 
                FROM ARON.mkt_regventas a, ARON.mkt_regprod b 
                WHERE a.id_mov_vnt = b.id_mov_vnt 
                AND a.ID_VENTA=b.ID_VENTA 
                AND a.id_venta = '".$mai_id."'
                AND a.voucher = '".$voucher."' 
                AND a.id_nivel_vnt like '".$id_nivel."%' 
                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                AND a.estado = 'V' 
                AND b.estado = 'V' 
                group by  a.serie,a.NUMDOC,a.id_venta,a.ID_CUENTA_VNT,a.ID_NIVEL_VNT,a.TIPODOC,a.id_nivel_cont,b.TIPO_IGV 
                UNION ALL
                --OTROS_CARGOS
                SELECT a.serie,a.NUMDOC,3 ord, 
                        nvl(ARON.fc_cta_aasinet(a.ID_CUENTA_ICBPER,b.id_venta),' ') as cuenta, 
                        nvl((select ID_CTA_CTE from ARON.cont_equiv where ID_CUENTA=ID_CUENTA_ICBPER group by ID_CTA_CTE),' ')  cta_cte, 
                        --nvl(ARON.equiv(substr(a.id_nivel_cont,1,1),b.id_venta),' ')  departamento, 
                        nvl(ARON.equiv(decode(substr(a.id_nivel_cont,1,1),1,'1.03.02',substr(a.id_nivel_cont,1,1)),b.id_venta),' ')  departamento,
                        to_char(nvl(SUM(b.OTROS_CARGOS),0)*-1,'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_ICBPER,b.id_venta),' ')  restriccion,
                        decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_cont,'1.03.07','ICBPER CAFETIN','1.03.02','ICBPER BAZAR','1.02.10','ICBPER GRASS SINTETICO','1.03.04','ICBPER COMEDOR','1.02.09','ICBPER PISCINA','1.01.24','ICBPER F.EDITORIAL','1.03.09','ICBPER TUNAS','3.03.09','ICBPER IMP',' ') descripcion 
                FROM ARON.mkt_regventas a, ARON.mkt_regprod b 
                WHERE a.id_mov_vnt = b.id_mov_vnt           
                AND a.ID_VENTA=b.ID_VENTA 
                AND a.id_venta = '".$mai_id."'      
                AND a.voucher = '".$voucher."'
                AND a.id_nivel_vnt like '".$id_nivel."%' 
                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                AND a.estado = 'V' 
                AND b.estado = 'V' 
                group by  a.id_mov_vnt,a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_ICBPER,a.TIPODOC,a.id_nivel_cont 
                HAVING SUM(b.OTROS_CARGOS) <> 0

                UNION ALL
                -- igv          
                SELECT a.serie,a.NUMDOC,3 ord, 
                        nvl(ARON.fc_cta_aasinet(a.ID_CUENTA_IGV,b.id_venta),' ') as cuenta, 
                        nvl((select ID_CTA_CTE from ARON.cont_equiv where ID_CUENTA=ID_CUENTA_IGV group by ID_CTA_CTE),' ')  cta_cte, 
                        --nvl(ARON.equiv(substr(a.id_nivel_cont,1,1),b.id_venta),' ')  departamento, 
                        nvl(ARON.equiv(decode(substr(a.id_nivel_cont,1,1),1,'1.03.02',substr(a.id_nivel_cont,1,1)),b.id_venta),' ')  departamento,
                        to_char(nvl(SUM(b.igv),0)*-1,'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_IGV,b.id_venta),' ')  restriccion,
                        decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_cont,'1.03.07','IGV CAFETIN','1.03.02','IGV BAZAR','1.02.10','IGV GRASS SINTETICO','1.03.04','IGV COMEDOR','1.02.09','IGV PISCINA','1.01.24','IGV F.EDITORIAL','1.03.09','IGV TUNAS','3.03.09','IGV IMP',' ') descripcion 
                FROM ARON.mkt_regventas a, ARON.mkt_regprod b 
                WHERE a.id_mov_vnt = b.id_mov_vnt           
                AND a.ID_VENTA=b.ID_VENTA 
                AND a.id_venta = '".$mai_id."'      
                AND a.voucher = '".$voucher."'
                AND a.id_nivel_vnt like '".$id_nivel."%' 
                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                AND a.estado = 'V' 
                AND b.estado = 'V' 
                group by  a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_IGV,a.TIPODOC,a.id_nivel_cont 
                HAVING SUM(b.igv) <> 0
                --dscto
                UNION ALL
                SELECT a.serie,a.NUMDOC,2 ord, 
                        '3163001' as cuenta, 
                        ' ' cta_cte, 
                        decode(substr(a.ID_NIVEL_CONT,1,1),3,nvl(ARON.equiv(a.id_nivel_vnt,b.id_venta),' '),nvl(ARON.equiv(a.ID_NIVEL_CONT,b.id_venta),' ')) departamento,                         
                        to_char(SUM(nvl(b.descuento,0)*1),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet('72.01.01',b.id_venta),' ')  restriccion, 
                        decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_vnt,'1.03.07','DSCTO CAFETIN','1.03.02','DSCTO BAZAR','1.03.04','DSCTO COMEDOR','1.02.09','DSCTO PISCINA','1.01.24','DSCTO EDITORIAL','1.03.09','DSCTO TUNAS','3.03.09','DSCTO IMP',' ') descripcion 
                FROM ARON.mkt_regventas a, ARON.mkt_regprod b , ARON.almacen_articulos c 
                WHERE a.id_mov_vnt = b.id_mov_vnt 
                and b.ID_ARTICULO=c.id_articulo 
                AND a.ID_VENTA=b.ID_VENTA 
                AND c.ID_ALMACEN=a.ID_ALMACEN 
                AND a.id_venta = '".$mai_id."'               
                AND a.voucher = '".$voucher."' 
                AND a.id_nivel_vnt like '".$id_nivel."%'  
                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                AND a.estado = 'V' 
                AND b.estado = 'V' 
                HAVING SUM(nvl(b.descuento,0)) > 0 
                group by  a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_CLI,a.ID_NIVEL_CONT,a.TIPODOC,a.id_nivel_vnt 
                --cliente
                UNION ALL
                SELECT a.serie,a.NUMDOC,1 ord, 
                        nvl(ARON.fc_cta_aasinet(a.ID_CUENTA_CLI,b.id_venta),' ') as cuenta, 
                        decode(substr(a.ID_NIVEL_CONT,1,1),3,'30025',nvl((select ID_CTA_CTE from ARON.cont_equiv where ID_CUENTA=ID_CUENTA_CLI group by ID_CTA_CTE),' ')) cta_cte, 
                        decode(substr(a.ID_NIVEL_CONT,1,1),3,nvl(ARON.equiv(substr(a.ID_NIVEL_CONT,1,1),b.id_venta),' '),nvl(ARON.equiv(a.ID_NIVEL_CONT,b.id_venta),' ')) departamento, 
                        --nvl(equiv(substr(a.ID_NIVEL_CONT,1,1),b.id_venta),' ')  departamento, 
                        to_char(SUM(b.importe*1),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_CLI,b.id_venta),' ')  restriccion, 
                        decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_cont,'1.03.07','VENTA CAFETIN','1.03.02','VENTA BAZAR','1.02.10','VENTA GRASS SINTETICO','1.03.04','VENTA COMEDOR','1.02.09','VENTA PISCINA','1.01.24','FONDO EDITORIAL','1.03.09','VENTA TUNAS','3.03.09','VENTAS IMP',' ') descripcion 
                FROM ARON.mkt_regventas a, ARON.mkt_regprod b , ARON.almacen_articulos c 
                WHERE a.id_mov_vnt = b.id_mov_vnt 
                and b.ID_ARTICULO=c.id_articulo 
                AND a.ID_VENTA=b.ID_VENTA 
                AND c.ID_ALMACEN = a.ID_ALMACEN 
                AND a.id_venta = '".$mai_id."'                
                AND a.voucher = '".$voucher."' 
                AND a.id_nivel_vnt like '".$id_nivel."%'  
                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                AND a.estado = 'V' 
                AND b.estado = 'V' 
                group by  a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_CLI,a.ID_NIVEL_CONT,a.TIPODOC     
                UNION ALL
                SELECT  a.serie,a.NUMDOC,6 ord, 
                        nvl(ARON.fc_cta_aasinet(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','20.01.01','021','X'),A.ID_VENTA),' ') CTA, 
                        DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','1','021','X','050','1')CTA_CTE, 
                        nvl(ARON.equiv(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','1.03.02','021','1.01.04'),A.ID_VENTA),' ') NIVEL, 
                        to_char(ROUND((B.CANTIDAD*B.PRECIO_ALM),2)*-1,'99,999,999.99') IMP,
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','20.01.01','021','X'),A.id_venta),' ')  restriccion, 
                        ARON.NOMBRE_ARTICULO(ID_ALMACEN,ID_ARTICULO)||'-'||'(Doc:'||a.serie||'-'||to_number(a.NUMDOC)||') '||'-'||TO_CHAR(A.FECHA_DOC,'DD/MM/YYYY') descripcion                           
                FROM ARON.MKT_REGVENTAS A, ARON.MKT_REGPROD B 
                WHERE A.ID_VENTA = B.ID_VENTA  
                AND A.ID_MOV_VNT = B.ID_MOV_VNT 
                AND A.ID_VENTA = '".$mai_id."' 
                AND SUBSTR(A.ID_ALMACEN,1,3) = '051'  
                AND a.voucher = '".$voucher."' 
                AND a.id_nivel_vnt like '".$id_nivel."%'  
                AND A.ESTADO = 'V' 
                AND B.ESTADO = 'V' 
                UNION ALL 
                SELECT  a.serie,a.NUMDOC,5 ord, 
                        nvl(ARON.fc_cta_aasinet(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','69.01.01','021','X'),A.ID_VENTA),' ') CTA, 
                        DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','1','021','X','050','1')CTA_CTE, 
                        nvl(ARON.equiv(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','1.03.02','021','1.01.04'),A.ID_VENTA),' ') NIVEL, 
                        to_char(SUM(ROUND((B.CANTIDAD*B.PRECIO_ALM),2)),'99,999,999.99') IMP,
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','69.01.01','021','X'),A.id_venta),' ')  restriccion, 
                        DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','Costo de Ventas Bazar','Ventas logistica')||' (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||') '||COL_UNION.FN_MESES_NOMBRE(TO_CHAR(A.FECHA_DOC,'MM')) descripcion                            
                FROM ARON.MKT_REGVENTAS A, ARON.MKT_REGPROD B 
                WHERE A.ID_VENTA = B.ID_VENTA  
                AND A.ID_MOV_VNT = B.ID_MOV_VNT 
                AND A.ID_VENTA = '".$mai_id."'
                AND SUBSTR(A.ID_ALMACEN,1,3) = '051'  
                AND a.voucher = '".$voucher."' 
                AND a.id_nivel_vnt like '".$id_nivel."%'  
                AND A.ESTADO = 'V' 
                AND B.ESTADO = 'V' 
                GROUP BY  a.serie,a.NUMDOC, A.ID_ALMACEN, TO_CHAR(A.FECHA_DOC,'MM'),A.ID_VENTA 
                ORDER BY NUMDOC, ord ";
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetOPT($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    fondo as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    restriccion as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    historico as \"Description\", 
                                    memo as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                                SELECT a.serie,a.NUMDOC,4 ord, 
                                                nvl(ARON.equiv(a.ID_CUENTA_VNT,a.id_venta),' ') as cuenta, 
                                                DECODE(b.TIPO_IGV,'10',NVL((select ID_CTA_CTE from ARON.cont_equiv where ID_CONT = a.ID_VENTA and ID_CUENTA=ID_CUENTA_VNT group by ID_CTA_CTE),' '),'30','1','5') cta_cte, 
                                                nvl(ARON.equiv(a.ID_NIVEL_VNT,a.id_venta),' ')  departamento, 
                                                to_char(SUM(b.base+nvl(b.descuento,0))*-1,'99999999.99') valor, 
                                                ' ' multi_moneda, 
                                                ' ' cod_moneda, 
                                                decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(    a.id_nivel_cont,'1.03.07','VENTA CAFETIN','1.03.02','VENTA BAZAR','1.02.10','VENTA GRASS SINTETICO','1.03.04','VENTA COMEDOR','1.02.09','VENTA PISCINA','1.01.24','FONDO EDITORIAL','1.03.09','VENTA TUNAS','3.03.09','VENTAS IMP',' ') historico, 
                                                'X' id_mov_vnt, a.id_mov_vnt memo,a.voucher,to_char(a.fecha_doc,'DD/MM/YYYY') fecha,a.fecha_doc,
                                                nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_VNT,a.id_venta),' ')  restriccion 
                                FROM ARON.mkt_regventas a, ARON.mkt_regprod b 
                                WHERE a.id_mov_vnt = b.id_mov_vnt 
                                AND a.ID_VENTA=b.ID_VENTA 
                                AND a.id_venta = '".$mai_id."'
                                AND a.voucher = '".$voucher."' 
                                AND a.id_nivel_vnt like '".$id_nivel."%'
                                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                                AND a.estado = 'V' 
                                AND b.estado = 'V' 
                                group by  a.serie,a.NUMDOC,a.id_venta,a.ID_CUENTA_VNT,a.ID_NIVEL_VNT,a.TIPODOC,a.id_nivel_cont,b.TIPO_IGV,a.id_mov_vnt,a.voucher,a.fecha_doc
                                UNION 
                                --OTROS_CARGOS
                                SELECT  a.serie,a.NUMDOC,3 ord, 
                                        nvl(ARON.fc_cta_aasinet(a.ID_CUENTA_ICBPER,b.id_venta),' ') as cuenta, 
                                        nvl((select ID_CTA_CTE from ARON.cont_equiv where ID_CUENTA=ID_CUENTA_ICBPER group by ID_CTA_CTE),' ')  cta_cte, 
                                        --nvl(ARON.equiv(substr(a.id_nivel_cont,1,1),b.id_venta),' ')  departamento, 
                                        nvl(ARON.equiv(decode(substr(a.id_nivel_cont,1,1),1,'1.03.02',substr(a.id_nivel_cont,1,1)),b.id_venta),' ')  departamento,
                                        to_char(nvl(SUM(b.OTROS_CARGOS),0)*-1,'99,999,999.99') valor, 
                                        --'10' fondo, 
                                        ' ' multi_moneda, 
                                        ' ' cod_moneda, 
                                        decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_cont,'1.03.07','ICBPER CAFETIN','1.03.02','ICBPER BAZAR','1.02.10','ICBPER GRASS SINTETICO','1.03.04','ICBPER COMEDOR','1.02.09','ICBPER PISCINA','1.01.24','ICBPER F.EDITORIAL','1.03.09','ICBPER TUNAS','3.03.09','ICBPER IMP',' ') historico,
                                        'X' id_mov_vnt,a.id_mov_vnt memo,a.voucher,to_char(a.fecha_doc,'DD/MM/YYYY') fecha,a.fecha_doc,
                                        nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_ICBPER,b.id_venta),' ')  restriccion    
                                FROM ARON.mkt_regventas a, ARON.mkt_regprod b 
                                WHERE a.id_mov_vnt = b.id_mov_vnt           
                                AND a.ID_VENTA=b.ID_VENTA 
                                AND a.id_venta = '".$mai_id."'      
                                AND a.voucher = '".$voucher."'
                                AND a.id_nivel_vnt like '".$id_nivel."%' 
                                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                                AND a.estado = 'V' 
                                AND b.estado = 'V' 
                                group by  a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_ICBPER,a.TIPODOC,a.id_nivel_cont,a.id_mov_vnt,a.voucher,a.fecha_doc
                                HAVING SUM(b.OTROS_CARGOS) <> 0
                                UNION ALL
                                -- igv          
                                SELECT a.serie,a.NUMDOC,3 ord, 
                                                nvl(ARON.equiv(a.ID_CUENTA_IGV,b.id_venta),' ') as cuenta, 
                                                nvl((select ID_CTA_CTE from ARON.cont_equiv where ID_CUENTA=ID_CUENTA_IGV group by ID_CTA_CTE),' ')  cta_cte, 
                                                --nvl(ARON.equiv(substr(a.id_nivel_cont,1,1),b.id_venta),' ')  departamento, 
                                                nvl(ARON.equiv(decode(substr(a.id_nivel_cont,1,1),1,'1.03.02',substr(a.id_nivel_cont,1,1)),b.id_venta),' ')  departamento,
                                                to_char(nvl(SUM(b.igv),0)*-1,'99999999.99') valor, 
                                                ' ' multi_moneda, 
                                                ' ' cod_moneda, 
                                                decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_cont,'1.03.07','IGV CAFETIN','1.03.02','IGV BAZAR','1.02.10','IGV GRASS SINTETICO','1.03.04','IGV COMEDOR','1.02.09','IGV PISCINA','1.01.24','IGV F.EDITORIAL','1.03.09','IGV TUNAS','3.03.09','IGV IMP',' ') historico, 
                                                'X' id_mov_vnt,a.id_mov_vnt memo,a.voucher,to_char(a.fecha_doc,'DD/MM/YYYY') fecha,a.fecha_doc,
                                                nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_IGV,b.id_venta),' ')  restriccion 
                                FROM ARON.mkt_regventas a, ARON.mkt_regprod b 
                                WHERE a.id_mov_vnt = b.id_mov_vnt           
                                AND a.ID_VENTA=b.ID_VENTA 
                                AND a.id_venta = '".$mai_id."'
                                AND a.voucher = '".$voucher."' 
                                AND a.id_nivel_vnt like '".$id_nivel."%'
                                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                                AND a.estado = 'V' 
                                AND b.estado = 'V' 
                                group by  a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_IGV,a.TIPODOC,a.id_nivel_cont,a.id_mov_vnt,a.voucher,a.fecha_doc
                                        HAVING SUM(b.igv) <> 0
                                --dscto
                                UNION 
                                SELECT a.serie,a.NUMDOC,2 ord, 
                                                '3163001' as cuenta, 
                                                ' ' cta_cte, 
                                                decode(substr(a.ID_NIVEL_CONT,1,1),3,nvl(ARON.equiv(a.id_nivel_vnt,b.id_venta),' '),nvl(ARON.equiv(a.ID_NIVEL_CONT,b.id_venta),' ')) departamento, 
                                                to_char(SUM(nvl(b.descuento,0)*1)*1,'99999999.99') valor, 
                                                ' ' multi_moneda, 
                                                ' ' cod_moneda, 
                                                decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_vnt,'1.03.07','DSCTO CAFETIN','1.03.02','DSCTO BAZAR','1.03.04','DSCTO COMEDOR','1.02.09','DSCTO PISCINA','1.01.24','DSCTO EDITORIAL','1.03.09','DSCTO TUNAS','3.03.09','DSCTO IMP',' ') historico, 
                                                'X' id_mov_vnt,a.id_mov_vnt memo,a.voucher,to_char(a.fecha_doc,'DD/MM/YYYY') fecha, a.fecha_doc, 
                                                nvl(ARON.fc_restriccion_aasinet('72.01.01',b.id_venta),' ')  restriccion 
                                FROM ARON.mkt_regventas a, ARON.mkt_regprod b , ARON.almacen_articulos c 
                                WHERE a.id_mov_vnt = b.id_mov_vnt 
                                and b.ID_ARTICULO=c.id_articulo 
                                AND a.ID_VENTA=b.ID_VENTA 
                                and c.ID_ALMACEN=a.ID_ALMACEN 
                                AND a.id_venta = '".$mai_id."'                            
                                AND a.voucher = '".$voucher."' 
                                AND a.id_nivel_vnt like '".$id_nivel."%'
                                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                                AND a.estado = 'V' 
                                AND b.estado = 'V' 
                                HAVING SUM(nvl(b.descuento,0)) > 0 
                                group by  a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_CLI,a.ID_NIVEL_CONT,a.TIPODOC,a.id_mov_vnt,a.voucher,a.fecha_doc,a.id_nivel_vnt
                                --cliente
                                UNION ALL
                                SELECT a.serie,a.NUMDOC,1 ord, 
                                                nvl(ARON.equiv(a.ID_CUENTA_CLI,b.id_venta),' ') as cuenta, 
                                                decode(substr(a.ID_NIVEL_CONT,1,1),3,'30025',nvl((select ID_CTA_CTE from ARON.cont_equiv where ID_CUENTA=ID_CUENTA_CLI group by ID_CTA_CTE),' ')) cta_cte, 
                                                decode(substr(a.ID_NIVEL_CONT,1,1),3,nvl(ARON.equiv(substr(a.ID_NIVEL_CONT,1,1),b.id_venta),' '),nvl(ARON.equiv(a.ID_NIVEL_CONT,b.id_venta),' ')) departamento, 
                                                --nvl(equiv(substr(a.ID_NIVEL_CONT,1,1),b.id_venta),' ')  departamento, 
                                                to_char(SUM(b.importe*1)*1,'99999999.99') valor, 
                                                ' ' multi_moneda, 
                                                ' ' cod_moneda, 
                                                decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_cont,'1.03.07','VENTA CAFETIN','1.03.02','VENTA BAZAR','1.02.10','VENTA GRASS SINTETICO','1.03.04','VENTA COMEDOR','1.02.09','VENTA PISCINA','1.01.24','FONDO EDITORIAL','1.03.09','VENTA TUNAS','3.03.09','VENTAS IMP',' ') historico, 
                                                a.id_mov_vnt,a.id_mov_vnt memo,a.voucher,to_char(a.fecha_doc,'DD/MM/YYYY') fecha,a.fecha_doc,
                                                nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_CLI,b.id_venta),' ')  restriccion 
                                FROM ARON.mkt_regventas a, ARON.mkt_regprod b , ARON.almacen_articulos c 
                                WHERE a.id_mov_vnt = b.id_mov_vnt 
                                and b.ID_ARTICULO=c.id_articulo 
                                AND a.ID_VENTA=b.ID_VENTA 
                                and c.ID_ALMACEN=a.ID_ALMACEN 
                                AND a.id_venta = '".$mai_id."'                            
                                AND a.voucher = '".$voucher."' 
                                AND a.id_nivel_vnt like '".$id_nivel."%'
                                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                                AND a.estado = 'V' 
                                AND b.estado = 'V' 
                                group by  a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_CLI,a.ID_NIVEL_CONT,a.TIPODOC,a.id_mov_vnt,a.voucher,a.fecha_doc                        
                        /* UNION ALL 
                                SELECT a.serie,a.NUMDOC,1 ord, 
                                                nvl(ARON.equiv(a.ID_CUENTA_CLI,b.id_venta),' ') as cuenta, 
                                                decode(substr(a.ID_NIVEL_CONT,1,1),3,'30025',nvl((select ID_CTA_CTE from ARON.cont_equiv where ID_CUENTA=ID_CUENTA_CLI group by ID_CTA_CTE),' ')) cta_cte, 
                                                decode(substr(a.ID_NIVEL_CONT,1,1),3,nvl(ARON.equiv(substr(a.ID_NIVEL_CONT,1,1),b.id_venta),' '),nvl(ARON.equiv(a.ID_NIVEL_CONT,b.id_venta),' ')) departamento, 
                                                --nvl(equiv(substr(a.ID_NIVEL_CONT,1,1),b.id_venta),' ')  departamento, 
                                                to_char(SUM(b.importe*1)*1,'99999999.99') valor, 
                                                ' ' multi_moneda, 
                                                ' ' cod_moneda, 
                                                decode(a.TIPODOC,'01','01','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA',a.TIPODOC)||': (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||')-'||DECODE(a.id_nivel_cont,'1.03.07','VENTA CAFETIN','1.03.02','VENTA BAZAR','1.02.10','VENTA GRASS SINTETICO','1.03.04','VENTA COMEDOR','1.02.09','VENTA PISCINA','1.03.09','VENTA TUNAS','VENTAS IMP') historico, 
                                                a.id_mov_vnt,a.id_mov_vnt memo,a.voucher,to_char(a.fecha_doc,'DD/MM/YYYY') fecha,a.fecha_doc,
                                                nvl(ARON.fc_restriccion_aasinet(a.ID_CUENTA_CLI,b.id_venta),' ')  restriccion 
                                FROM ARON.mkt_regventas a, ARON.mkt_regprod b
                                WHERE a.id_mov_vnt = b.id_mov_vnt 
                                AND a.ID_VENTA=b.ID_VENTA 
                                AND a.id_venta = '".$mai_id."'                            
                                AND a.voucher = '".$voucher."' 
                                AND a.id_nivel_vnt like '".$id_nivel."%'
                                AND a.TIPODOC in ('01','02','09','10','12','14','16','18','03','07') 
                                AND a.serie = 'B121'
                                AND a.estado = 'V' 
                                AND b.estado = 'V' 
                                group by  a.serie,a.NUMDOC,b.id_venta,a.ID_CUENTA_CLI,a.ID_NIVEL_CONT,a.TIPODOC,a.id_mov_vnt,a.voucher,a.fecha_doc */                        
                                UNION ALL
                                SELECT  a.serie,a.NUMDOC,6 ord, 
                                                nvl(ARON.equiv(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','20.01.01','021','X'),A.ID_VENTA),' ') CTA, 
                                                DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','1','021','X','050','1')CTA_CTE, 
                                                nvl(ARON.equiv(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','1.03.02','021','1.01.04'),A.ID_VENTA),' ') NIVEL, 
                                                to_char(ROUND((B.CANTIDAD*B.PRECIO_ALM),2)*-1,'99999999.99') IMP,' ' MONEDA,' ' CO_MONEDA, 
                                                ARON.NOMBRE_ARTICULO(ID_ALMACEN,ID_ARTICULO)||'-'||'(Doc:'||a.serie||'-'||to_number(a.NUMDOC)||') '||'-'||TO_CHAR(A.FECHA_DOC,'DD/MM/YYYY') HISTORICO, 
                                                'X' id_mov_vnt,a.id_mov_vnt memo,a.voucher,to_char(a.fecha_doc,'DD/MM/YYYY') fecha, a.fecha_doc,
                                                nvl(ARON.fc_restriccion_aasinet(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','20.01.01','021','X'),a.id_venta),' ')  restriccion
                                FROM ARON.MKT_REGVENTAS A, ARON.MKT_REGPROD B 
                                WHERE A.ID_VENTA = B.ID_VENTA  
                                AND A.ID_MOV_VNT = B.ID_MOV_VNT 
                                AND A.ID_VENTA = '".$mai_id."' 
                                AND SUBSTR(A.ID_ALMACEN,1,3) = '051'  
                                AND a.voucher = '".$voucher."' 
                                AND a.id_nivel_vnt like '".$id_nivel."%'
                                AND A.ESTADO = 'V' 
                                AND B.ESTADO = 'V' 
                                UNION ALL
                                SELECT  a.serie,a.NUMDOC,5 ord, 
                                                nvl(ARON.equiv(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','69.01.01','021','X'),A.ID_VENTA),' ') CTA, 
                                                DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','1','021','X','050','1')CTA_CTE, 
                                                nvl(ARON.equiv(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','1.03.02','021','1.01.04'),A.ID_VENTA),' ') NIVEL, 
                                                to_char(SUM(ROUND((B.CANTIDAD*B.PRECIO_ALM),2))*1,'99999999.99') IMP,' ' MONEDA,' ' CO_MONEDA, 
                                                DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','Costo de Ventas Bazar','Ventas logistica')||' (Doc:'||a.serie||'-'||to_number(a.NUMDOC)||') '||COL_UNION.FN_MESES_NOMBRE(TO_CHAR(A.FECHA_DOC,'MM')) HISTORICO, 
                                                'X' id_mov_vnt,a.id_mov_vnt memo,a.voucher,to_char(a.fecha_doc,'DD/MM/YYYY') fecha,a.fecha_doc,
                                                nvl(ARON.fc_restriccion_aasinet(DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051','69.01.01','021','X'),A.id_venta),' ')  restriccion
                                FROM ARON.MKT_REGVENTAS A, ARON.MKT_REGPROD B 
                                WHERE A.ID_VENTA = B.ID_VENTA  
                                AND A.ID_MOV_VNT = B.ID_MOV_VNT 
                                AND A.ID_VENTA = '".$mai_id."'
                                AND SUBSTR(A.ID_ALMACEN,1,3) = '051'  
                                AND a.voucher = '".$voucher."' 
                                AND a.id_nivel_vnt like '".$id_nivel."%'
                                AND A.ESTADO = 'V' 
                                AND B.ESTADO = 'V' 
                                GROUP BY  a.serie,a.NUMDOC, A.ID_ALMACEN, TO_CHAR(A.FECHA_DOC,'MM'),A.ID_VENTA,a.id_mov_vnt,a.voucher,a.fecha_doc
                                ORDER BY NUMDOC, ord 
                    ) X ";
        $oQuery = DB::connection('oracleapp')->select($query);
        return $oQuery;
    }
    //VENTAS  TARAPOTO
    public static function listVoucherCWAasinetSalesTPP($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        VOUCHER,
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.VENTAS_VOUCHER
                WHERE ID_VENTA = '".$id_compra."'
                AND ID_NIVEL = '".$id_depto."'
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '17'
                AND ID_NIVEL = '".$id_depto."'
                AND TIPO = 'RV' --RV: LOTE ASSINET
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function listCWSeatAaasinetTPP($id_nivel,$id_anho,$voucher){
        $id_nivel = "6";
        $mai_id = "001-".$id_anho;

        $query = "SELECT a.serie,a.numvnt, 
                        nvl(ARON.fc_cta_aasinet(b.id_cuenta_vnt,b.id_venta),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(b.id_cuenta_vnt,b.id_venta),' ') cta_cte, 							
                        nvl(decode(ARON.equiv(b.id_cuenta_vnt,b.id_venta)||'-'||ARON.fc_cta_cte_aasinet(b.id_cuenta_vnt,b.id_venta),'1132001-2','11010101',ARON.equiv_nivel(b.id_nivel_vnt,b.id_venta)),' ') departamento, 							
                        to_char((b.importe-nvl(b.igv,0))*decode(b.dc,'D',-1,1),'99999999.99') valor, 
                        decode(a.tipo_mov,'01','CA','02','FA','07','CA','09','PG','10','II','12','FA','14','FA','16','FA','18','BK','20','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,20)||' '||substr(ARON.apellido3(a.id_personal),1,20) descripcion,
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet(b.id_cuenta_vnt,b.id_venta),' ')  restriccion
                FROM ARON.tara_regventas a, ARON.tara_regdetalle b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."' 
                AND a.voucher_vnt = '".$voucher."'
                AND b.id_nivel_vnt like '".$id_nivel."%' 
                AND a.tipo_mov in ('01','02','07','09','10','12','14','16','18','20') 
                AND a.estado = 'V' 					
                UNION ALL 
                SELECT a.serie,a.numvnt, 
                        nvl(ARON.fc_cta_aasinet('40.01.01',b.id_venta),' ') cuenta, 
                        ' ' cta_cte, 
                        nvl(decode(ARON.equiv(a.id_cuenta_cli,b.id_venta)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_cli,b.id_venta),'1132001-3','61010101','1132001-11','61010101',ARON.equiv_nivel(a.id_nivel_cont,b.id_venta)),' ') departamento, 														
                        to_char(nvl(b.igv,0)*decode(b.dc,'D',-1,1),'99999999.99') valor,  
                        decode(a.tipo_mov,'01','CA','02','FA','07','CA','09','PG','10','II','12','FA','14','FA','16','FA','18','BK','20','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,20)||' '||substr(ARON.apellido3(a.id_personal),1,20) descripcion,
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet('40.01.01',b.id_venta),' ')  restriccion
                FROM ARON.tara_regventas a, ARON.tara_regdetalle b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."' 
                AND a.voucher_vnt = '".$voucher."'
                AND b.id_nivel_vnt like '".$id_nivel."%'
                AND a.tipo_mov in ('01','02','07','09','10','12','14','16','18','20') 
                AND a.estado = 'V' 	
                AND NVL(b.igv,0) <> 0 				
                UNION ALL 
                SELECT a.serie,a.numvnt, 
                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_cli,a.id_venta),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_cli,a.id_venta),' ') cta_cte, 							
                        nvl(decode(ARON.equiv(a.id_cuenta_cli,a.id_venta)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_cli,a.id_venta),'1132001-3','61010101','1132001-11','61010101',ARON.equiv_nivel(a.id_nivel_cont,a.id_venta)),' ') departamento, 							
                        to_char(a.importe,'99999999.99') valor, 
                        decode(a.tipo_mov,'01','CA','02','FA','07','CA','09','PG','10','II','12','FA','14','FA','16','FA','18','BK','20','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(a.glosa,1,20)||' '||substr(ARON.apellido3(a.id_personal),1,20) descripcion,
                        '10' fondo,
                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_cli,a.id_venta),' ')  restriccion
                FROM ARON.tara_regventas a, ARON.tara_regdetalle b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."'  
                AND a.voucher_vnt = '".$voucher."'
                AND b.id_nivel_vnt like '".$id_nivel."%'
                AND a.tipo_mov in ('01','02','07','09','10','12','14','16','18','20') 
                AND a.estado = 'V' 
                GROUP BY a.id_venta,a.id_mov_vnt,a.id_cuenta_cli,a.serie,a.numvnt,a.id_nivel_cont,a.importe,a.tipo_mov,a.id_personal,a.glosa 
                ORDER BY SERIE,NUMVNT,CUENTA ";
        $oQuery = DB::connection('oracleapp')->select($query);    
        return $oQuery;
    }
    public static function uploadSeatAaasinetTPP($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    fondo as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    restriccion as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    historico as \"Description\", 
                                    memo as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                                SELECT a.serie,a.numvnt, 
                                        nvl(ARON.fc_cta_aasinet(b.id_cuenta_vnt,b.id_venta),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(b.id_cuenta_vnt,b.id_venta),' ') cta_cte, 							
                                        nvl(decode(ARON.equiv(b.id_cuenta_vnt,b.id_venta)||'-'||ARON.fc_cta_cte_aasinet(b.id_cuenta_vnt,b.id_venta),'1132001-2','11010101',ARON.equiv_nivel(b.id_nivel_vnt,b.id_venta)),' ') departamento, 							
                                        to_char((b.importe-nvl(b.igv,0))*decode(b.dc,'D',-1,1),'99999999.99') valor, 
                                        decode(a.tipo_mov,'01','CA','02','FA','07','CA','09','PG','10','II','12','FA','14','FA','16','FA','18','BK','20','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,20)||' '||substr(ARON.apellido3(a.id_personal),1,20) historico,
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet(b.id_cuenta_vnt,b.id_venta),' ')  restriccion,
                                        a.id_mov_vnt memo
                                FROM ARON.tara_regventas a, ARON.tara_regdetalle b 
                                WHERE a.id_venta = b.id_venta 
                                AND a.id_mov_vnt = b.id_mov_vnt 
                                AND a.id_venta = '".$mai_id."'
                                AND a.voucher_vnt = '".$voucher."'
                                AND b.id_nivel_vnt like '".$id_nivel."%' 
                                AND a.tipo_mov in ('01','02','07','09','10','12','14','16','18','20') 
                                AND a.estado = 'V' 					
                                UNION ALL 
                                SELECT a.serie,a.numvnt, 
                                        nvl(ARON.fc_cta_aasinet('40.01.01',b.id_venta),' ') cuenta, 
                                        ' ' cta_cte, 
                                        nvl(decode(ARON.equiv(a.id_cuenta_cli,b.id_venta)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_cli,b.id_venta),'1132001-3','61010101','1132001-11','61010101',ARON.equiv_nivel(a.id_nivel_cont,b.id_venta)),' ') departamento, 														
                                        to_char(nvl(b.igv,0)*decode(b.dc,'D',-1,1),'99999999.99') valor,  
                                        decode(a.tipo_mov,'01','CA','02','FA','07','CA','09','PG','10','II','12','FA','14','FA','16','FA','18','BK','20','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,20)||' '||substr(ARON.apellido3(a.id_personal),1,20) historico,
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet('40.01.01',b.id_venta),' ')  restriccion,
                                        a.id_mov_vnt memo
                                FROM ARON.tara_regventas a, ARON.tara_regdetalle b 
                                WHERE a.id_venta = b.id_venta 
                                AND a.id_mov_vnt = b.id_mov_vnt 
                                AND a.id_venta = '".$mai_id."' 
                                AND a.voucher_vnt = '".$voucher."'
                                AND b.id_nivel_vnt like '".$id_nivel."%'
                                AND a.tipo_mov in ('01','02','07','09','10','12','14','16','18','20') 
                                AND a.estado = 'V' 	
                                AND NVL(b.igv,0) <> 0 				
                                UNION ALL 
                                SELECT a.serie,a.numvnt, 
                                        nvl(ARON.fc_cta_aasinet(a.id_cuenta_cli,a.id_venta),' ') cuenta, 
                                        nvl(ARON.fc_cta_cte_aasinet(a.id_cuenta_cli,a.id_venta),' ') cta_cte, 							
                                        nvl(decode(ARON.equiv(a.id_cuenta_cli,a.id_venta)||'-'||ARON.fc_cta_cte_aasinet(a.id_cuenta_cli,a.id_venta),'1132001-3','61010101','1132001-11','61010101',ARON.equiv_nivel(a.id_nivel_cont,a.id_venta)),' ') departamento, 							
                                        to_char(a.importe,'99999999.99') valor, 
                                        decode(a.tipo_mov,'01','CA','02','FA','07','CA','09','PG','10','II','12','FA','14','FA','16','FA','18','BK','20','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(a.glosa,1,20)||' '||substr(ARON.apellido3(a.id_personal),1,20) historico,
                                        '10' fondo,
                                        nvl(ARON.fc_restriccion_aasinet(a.id_cuenta_cli,a.id_venta),' ')  restriccion,
                                        a.id_mov_vnt memo
                                FROM ARON.tara_regventas a, ARON.tara_regdetalle b 
                                WHERE a.id_venta = b.id_venta 
                                AND a.id_mov_vnt = b.id_mov_vnt 
                                AND a.id_venta = '".$mai_id."' 
                                AND a.voucher_vnt = '".$voucher."'
                                AND b.id_nivel_vnt like '".$id_nivel."%'
                                AND a.tipo_mov in ('01','02','07','09','10','12','14','16','18','20') 
                                AND a.estado = 'V' 
                                GROUP BY a.id_venta,a.id_mov_vnt,a.id_cuenta_cli,a.serie,a.numvnt,a.id_nivel_cont,a.importe,a.tipo_mov,a.id_personal,a.glosa 
                                ORDER BY SERIE,NUMVNT,CUENTA  
                    ) X ";
        $oQuery = DB::connection('oracleapp')->select($query);
        return $oQuery;
    }
    //
    public static function insert_update_lote($id_voucher,$id_depto,$id_anho,$id_tipovoucher,$t_lote_aasi,$area,$tipo){
        $mai_id = "001-".$id_anho;
        $tipo_a = substr($tipo,0,2);
        $query = "INSERT INTO ARON.AASI_VOUCHERS (                                            
                                                ID_ANHO,
                                                ID_AREA,
                                                ID_NIVEL,
                                                TIPO,
                                                VOUCHER,
                                                FECHA,
                                                ID_USER
                                        )VALUES(
                                                '".$mai_id."',
                                                '".$area."',    
                                                '".$id_depto."',
                                                '".$tipo_a."',                                                   
                                                '".$id_voucher."',
                                                SYSDATE,
                                                '".$t_lote_aasi."'
                                                ) "; 
        //$oQuery = 
        DB::connection('oracleapp')->insert($query);    
        //return $oQuery;
    }
    public static function update_lote_cw($voucher,$id_nivel,$id_anho,$id_tipovoucher,$t_lote_aasi,$area,$tipo){
        $mai_id = "001-".$id_anho;
        
        $lote = $tipo." ".$t_lote_aasi;

        if($id_tipovoucher == "4"){
                $sql = "UPDATE ARON.TLECRD_VOUCHER_CTR SET LOTE_CTR = '".$lote."'
                        WHERE ID_TLECRD = '".$mai_id."'
                        AND ID_NIVEL = '".$id_nivel."'
                        AND VOUCHER = '".$voucher."' ";                      
        }
        if($id_tipovoucher == "5"){
                $sql = "UPDATE ARON.EGRESOS_VOUCHER_CTR SET LOTE_CTR = '".$lote."'
                        WHERE ID_COMPRAS = '".$mai_id."'
                        AND ID_NIVEL = '".$id_nivel."'
                        AND VOUCHER = '".$voucher."' ";               
        }
        if($id_tipovoucher == "3"){
                $sql = "UPDATE ARON.CHEQUE_VOUCHER_CTR SET LOTE_CTR = '".$lote."'
                        WHERE ID_CHEQUE = '".$mai_id."'
                        AND ID_NIVEL = '".$id_nivel."'
                        AND VOUCHER = '".$voucher."' ";               
        }
        if($id_tipovoucher == "2"){
                $sql = "UPDATE ARON.COMPRAS_VOUCHER_CTR SET LOTE_CTR = '".$lote."'
                        WHERE ID_COMPRAS = '".$mai_id."'
                        AND ID_NIVEL = '".$id_nivel."'
                        AND VOUCHER = '".$voucher."' ";               
        }
        if($id_tipovoucher == "1" || $id_tipovoucher == "14" || $id_tipovoucher == "15" || $id_tipovoucher == "17"){
                $sql = "UPDATE ARON.VENTAS_VOUCHER_CTR SET LOTE_CTR = '".$lote."'
                        WHERE ID_VENTA = '".$mai_id."'
                        AND ID_NIVEL = '".$id_nivel."'
                        AND VOUCHER = '".$voucher."' ";               
        }
        if($id_tipovoucher == "16"){
                $sql = "UPDATE ARON.mkt_voucher SET LOTE = '".$lote."'
                        WHERE ID_VENTA = '".$mai_id."'
                        AND ID_NIVEL  like '".$id_nivel."%'
                        AND VOUCHER = '".$voucher."' 
                        AND TIPO = '2' ";               
        }

        //$oQuery = DB::connection('oracleapp')->select($query);   
        DB::connection('oracleapp')->update($sql);
        //return $oQuery;
    }

       //Voucher Venta CAT
       public static function listVoucherCWAasinetSalesCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        VOUCHER,
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.VENTAS_VOUCHER@DBL_JUL
                WHERE ID_VENTA = '".$id_compra."'
                AND ID_NIVEL = '7'
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS@DBL_JUL
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '12'
                AND ID_NIVEL = '7'
                AND TIPO = 'RVJ' --RV: LOTE ASSINET
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracle')->select($query);    
        return $oQuery;
    }
    //ASSIENTO VENTA CAT
    public static function listCWSeatAaasinetSalesCAT($id_nivel,$id_anho,$voucher){
        $mai_id = "001-".$id_anho;
        $query = "SELECT a.serie,a.numvnt, 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ') cta_cte, 
                                '10' fondo,							
                                nvl(decode(ARON.equiv@DBL_JUL(b.id_cuenta_vnt,b.id_venta)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(b.id_nivel_vnt,b.id_venta)),' ') departamento, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ')  restriccion,
                                to_char((b.importe-nvl(b.igv,0))*decode(b.dc,'D',-1,1),'99999999.99') valor,
                                decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                                a.id_mov_vnt memo,
                                a.docvnt
                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."' 
                AND a.VOUCHER_VNT = '".$voucher."'
                AND b.id_nivel_vnt like '7%' 
                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                AND a.estado = 'V' 					
                UNION ALL 
                SELECT a.serie,a.numvnt, 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL('40.01.01',b.id_venta),' ') cuenta, 
                        ' ' cta_cte, 
                        '10' fondo,
                        '11010102' departamento, 							
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL('40.01.01',b.id_venta),' ')  restriccion,
                        to_char(nvl(b.igv,0)*decode(b.dc,'D',-1,1),'99999999.99') valor,  
                        decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                        a.id_mov_vnt memo,
                        a.docvnt
                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."' 
                AND a.VOUCHER_VNT = '".$voucher."'
                AND b.id_nivel_vnt like '7%'  
                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                AND a.estado = 'V' 	
                AND NVL(b.igv,0) <> 0 				
                UNION ALL 
                SELECT a.serie,a.numvnt, 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ') cta_cte,
                        '10' fondo, 							
                        nvl(decode(ARON.equiv@DBL_JUL(a.id_cuenta_cli,a.id_venta)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel_cont,a.id_venta)),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ')  restriccion, 							
                        to_char(a.importe*1,'99999999.99') valor, 
                        decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(a.glosa,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                        a.id_mov_vnt memo,
                        a.docvnt
                FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
                WHERE a.id_venta = b.id_venta 
                AND a.id_mov_vnt = b.id_mov_vnt 
                AND a.id_venta = '".$mai_id."' 
                AND a.VOUCHER_VNT = '".$voucher."'
                AND b.id_nivel_vnt like '7%'  
                AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
                AND a.estado = 'V' 
                GROUP BY a.id_venta,a.id_mov_vnt,a.id_cuenta_cli,a.serie,a.numvnt,a.id_nivel_cont,a.importe,a.tipo_mov,a.id_personal,a.glosa,a.docvnt 
                ORDER BY DOCVNT,SERIE,NUMVNT,VALOR,CUENTA  ";
        $oQuery = DB::connection('oracle')->select($query);    
        return $oQuery;
    }
    // COMPRAS CAT
    public static function listVoucherCWAasinetPurchasesCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        voucher, 
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.compras_voucher@DBL_JUL
                WHERE id_compras = '".$id_compra."' 
                AND id_nivel = '7' 
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS@DBL_JUL
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '03'
                AND ID_NIVEL = '7'
                AND TIPO = 'RC'
                )
                ORDER BY fecha_ord DESC  "; 
        $oQuery = DB::connection('oracle')->select($query);    
        return $oQuery;
    }
    //TELECREDITO CAT
    public static function listVoucherCWAasinetTLCCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        voucher, 
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.tlecrd_voucher@DBL_JUL 
                WHERE id_tlecrd = '".$id_compra."' 
                AND id_nivel = '7' 
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS@DBL_JUL
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '13'
                AND ID_NIVEL = '7'
                AND TIPO = 'MB'
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracle')->select($query);    
        return $oQuery;
    }
//CHEQUES CAT
    public static function listVoucherCWAasinetCHQCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
        $id_compra = "001-".$id_anho;
        $query = "SELECT 
                        voucher, 
                        to_char(fecha,'DD/MM/YYYY') FECHA,
                        voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                        to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                        estado 
                FROM ARON.cheque_voucher@DBL_JUL 
                WHERE id_cheque = '".$id_compra."' 
                AND id_nivel = '7' 
                AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                AND VOUCHER NOT IN (
                SELECT VOUCHER FROM ARON.AASI_VOUCHERS@DBL_JUL
                WHERE ID_ANHO = '".$id_compra."' 
                AND ID_AREA = '18'
                AND ID_NIVEL = '7'
                AND TIPO = 'MB'
                )
                ORDER BY fecha_ord DESC "; 
        $oQuery = DB::connection('oracle')->select($query);    
        return $oQuery;
    }
        //INGRESOS CAT
        public static function listVoucherCWAasinetIncomeCAT($id_user,$id_entidad,$id_depto,$id_anho,$id_mes){
                $id_compra = "001-".$id_anho;
                $query = "SELECT 
                                voucher, 
                                to_char(fecha,'DD/MM/YYYY') FECHA,
                                voucher||' - '||to_char(fecha,'DD/MM/YYYY') texto, 
                                to_char(fecha,'yyyymmdd hh24mi') fecha_ord, 
                                estado 
                        FROM ARON.egresos_voucher@DBL_JUL 
                        WHERE id_compras = '".$id_compra."' 
                        AND id_nivel = '7' 
                        AND TO_CHAR(FECHA,'MM') = LPAD('".$id_mes."',2,0)
                        AND VOUCHER NOT IN (
                        SELECT VOUCHER FROM ARON.AASI_VOUCHERS@DBL_JUL
                        WHERE ID_ANHO = '".$id_compra."' 
                        AND ID_AREA = '05'
                        AND ID_NIVEL = '7'
                        AND TIPO = 'MB'
                        )
                        ORDER BY fecha_ord DESC "; 
                $oQuery = DB::connection('oracle')->select($query);    
                return $oQuery;
            }
           
            
            public static function listCWSeatAaasinetPurchasesCAT($id_nivel,$id_anho,$voucher){
                $mai_id = "001-".$id_anho;
                $query = "SELECT 
                                '1' orden, 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(C.id_cta_gasto,A.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(C.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL(C.id_nivel_gasto,A.id_compras),' ') departamento, 
                                to_char(nvl(C.importe+NVL(DECODE(C.TIPO_BI,'2',C.IGV,0),0),0),'99,999,999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(C.id_cta_gasto,A.id_compras),' ')  restriccion,
                                substr('(Doc: '||A.serie||'-'||A.numdoc||')-'||substr(C.detalle,1,25)||'-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) historico,
                                A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||substr(C.detalle,1,30)||'-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                        FROM ARON.compras_registro@DBL_JUL A, ARON.compras_detalle@DBL_JUL C  
                        WHERE A.id_mov_comp = C.id_mov_comp 
                        and A.id_compras = C.id_compras 
                        AND A.id_cont = '".$mai_id."' 
                        and A.id_compras = '".$mai_id."'                                                                        
                        and A.estado = 'P' 
                        and A.id_nivel_cont like '7%' 
                        and A.voucher = '".$voucher."'
                        UNION ALL
                        SELECT  
                                '1.1' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(C.id_cta_igv,A.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(C.id_cta_igv,a.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_cont,A.id_compras),' ') departamento, 
                                to_char(nvl(C.igv,0),'99,999,999.99') valor, 
                                ' ' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(C.id_cta_igv,A.id_compras),' ')  restriccion, 
                                substr('(IGV: Doc: '||A.serie||'-'||A.numdoc||')-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) historico,
                                A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||'IGV Compras Div.'||'-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                        FROM ARON.compras_registro@DBL_JUL A, ARON.compras_detalle@DBL_JUL C 
                        WHERE A.id_mov_comp = C.id_mov_comp 
                        and A.id_cont = '".$mai_id."'
                        and A.id_compras = '".$mai_id."'
                        and A.voucher = '".$voucher."' 
                        and A.estado = 'P' 
                        and A.id_nivel_cont like '7%' 
                        and C.tipo_bi in ('1','5') 
                        UNION ALL
                        SELECT 
                                '1.2' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(b.id_cuenta_comp,a.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_comp,a.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL(a.id_nivel_cont,a.id_compras),' ') departamento, 
                                to_char(nvl(sum(a.importe*-1),0),'99,999,999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(b.id_cuenta_comp,a.id_compras),' ')  restriccion, 
                                substr(a.tipo_doc||':(Doc: '||A.serie||'-'||A.numdoc||')-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) historico,
                                A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||'COMPRAS'||'-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                        FROM ARON.compras_registro@DBL_JUL a, ARON.compras_main@DBL_JUL b 
                        WHERE a.id_compras = b.id_compras 
                        and a.id_cont = '".$mai_id."'
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."'
                        and a.estado = 'P' 
                        and a.id_nivel_cont like '7%' 
                        GROUP BY a.id_compras,b.id_cuenta_comp,a.id_nivel_cont,a.importe,a.tipo_doc,A.serie,A.numdoc,A.id_proveedor,A.id_mov_comp,A.tipo_doc,A.fecha_prov
                        UNION ALL
                        SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor),'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,
                        'C'||g.memo memo,g.memo id_mov 
                        FROM ( 
                        SELECT '1' orden, 
                                NVL(ARON.fc_cta_aasinet@DBL_JUL(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_COMP),b.id_compras),' ') cuenta,   --60.**.**
                                NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_comp),b.id_compras),' ') cta_cte, 
                                NVL(ARON.equiv_nivel@DBL_JUL(a.id_nivel_alm,b.id_compras),' ') departamento, 
                                NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0) valor,  
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_COMP),b.id_compras),' ')  restriccion, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-' ||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',ARON.ruc2@DBL_JUL(b.id_proveedor)||' COMPRA MERCADERIAS BAZAR',ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo)||ARON.ruc2@DBL_JUL(b.id_proveedor)),1,60) historico,
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-' ||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',ARON.ruc2@DBL_JUL(b.id_proveedor)||' COMPRA MERCADERIAS BAZAR',ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo)||ARON.ruc2@DBL_JUL(b.id_proveedor)),1,60) descripcion,
                                b.fecha_prov, 
                                b.id_mov_comp memo 
                        FROM ARON.almacen_ing_com@DBL_JUL a,ARON.compras_registro@DBL_JUL b 
                        WHERE a.id_mov_comp = b.id_mov_comp 
                        and b.id_cont = '".$mai_id."'
                        and b.id_compras = '".$mai_id."'
                        and b.voucher = '".$voucher."'
                        AND a.estado        = 'P' 
                        and b.id_nivel_cont like '7%' 
                        )g 
                        GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                        UNION ALL
                        SELECT  
                                '1.1' orden,     
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ') cuenta,          
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ') cta_cte,                                                                     
                                NVL(ARON.equiv_nivel@DBL_JUL(decode(SUBSTR(c.id_almacen,1,3),'051',a.id_nivel_cont,C.id_nivel_alm),A.id_compras),' ') departamento, 
                                to_char(nvl(sum(C.igv),0),'99,999,999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ')  restriccion, 
                                'IGV Comp Almacen '||'Doc: '||A.serie||'-'||A.numdoc historico,
                                A.tipo_doc||'(Doc:'||A.serie||'-'||A.numdoc||') IGV Comp Almacen ' descripcion,  
                                A.fecha_prov,
                                'C'||A.id_mov_comp memo, 
                                A.id_mov_comp id_mov    
                        FROM ARON.compras_registro@DBL_JUL A, ARON.almacen_ing_com@DBL_JUL C 
                        WHERE A.id_mov_comp = C.id_mov_comp 
                        and A.id_cont = '".$mai_id."'
                        and A.id_compras = '".$mai_id."'
                        and A.voucher = '".$voucher."'
                        and A.estado = 'P'  
                        and C.tipo_bi in ('1','5')  
                        and A.id_nivel_cont like '7%' 
                        group by a.id_compras,A.id_mov_comp ,C.id_cuenta_igv,C.tipo_bi,C.id_nivel_alm,A.serie,A.numdoc,a.id_nivel_cont,c.id_almacen,A.tipo_doc,A.fecha_prov
                        having sum(C.igv) <> 0
                        UNION ALL
                        SELECT '2' orden, 
                                NVL(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ') cuenta,  ----20.**.**
                                NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ') cta_cte, 
                                NVL(ARON.equiv_nivel@DBL_JUL(b.id_nivel_cont,b.id_compras),' ') departamento, 
                                TO_CHAR(NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0),'99,999,999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ')  restriccion, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) historico, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) descripcion,
                                b.fecha_prov,
                                'K'||b.id_mov_comp memo,
                                b.id_mov_comp id_mov
                        FROM ARON.almacen_ing_com@DBL_JUL a, ARON.compras_registro@DBL_JUL b 
                        WHERE a.id_mov_comp = b.id_mov_comp 
                        and b.id_cont = '".$mai_id."'
                        AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                        and b.id_compras = '".$mai_id."'
                        and b.voucher = '".$voucher."'
                        AND a.estado = 'P' 
                        and b.id_nivel_cont like '7%' 
                        UNION ALL
                        SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor)*-1,'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,'K'||g.memo memo,g.memo id_mov
                        FROM ( 
                        SELECT '2.1' orden, 
                                NVL(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ') cuenta, ----61.**.**
                                NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ') cta_cte, 
                                NVL(ARON.equiv_nivel@DBL_JUL(a.id_nivel_alm,b.id_compras),' ') departamento, 
                                NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0) valor,  
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ')  restriccion, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||ARON.ruc2@DBL_JUL(b.id_proveedor)||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP'),1,60) historico, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063',' VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP')||'-'||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) descripcion,
                                b.fecha_prov,
                                b.id_mov_comp memo 
                        FROM ARON.almacen_ing_com@DBL_JUL a, ARON.compras_registro@DBL_JUL b 
                        WHERE a.id_mov_comp = b.id_mov_comp 
                        and b.id_cont = '".$mai_id."'
                        AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                        and b.id_compras = '".$mai_id."'
                        and b.voucher = '".$voucher."'
                        AND a.estado        = 'P' 
                        and b.id_nivel_cont like '7%' 
                        )g 
                        GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                        --ORDER BY FECHA_PROV,ID_MOV,ORDEN
                        UNION ALL                                                                                                                                       
                        SELECT 
                                '3.3' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento,  
                                to_char(decode(A.tipo_doc,'08',A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'),'07',(A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(-1)),'99999999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                                'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_not memo , A.id_mov_not id_mov  
                        FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                        WHERE A.id_mov_comp = B.id_mov_comp 
                        and A.id_cont = '".$mai_id."'
                        and A.id_compras = '".$mai_id."'
                        and A.voucher = '".$voucher."'
                        and A.estado = 'P' 
                        and A.id_nivel_gasto like '7%' 
                        and A.tipo_bi = '5' 
                        and A.id_cta_gasto >= '60.01.01' 
                        UNION ALL
                        SELECT  
                                '3.3' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                                to_char(decode(A.tipo_doc,'08',A.importe,A.importe*(-1)),'99999999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                                'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_not memo , A.id_mov_not id_mov  
                        FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                        WHERE A.id_mov_comp = B.id_mov_comp 
                        and A.id_cont = '".$mai_id."'
                        and A.id_compras = '".$mai_id."'
                        and A.voucher = '".$voucher."'
                        and A.estado = 'P' 
                        and A.id_nivel_gasto like '7%' 
                        and A.tipo_bi in ('3','4') 
                        UNION ALL
                        SELECT
                                '3.3' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                                to_char(decode(A.tipo_doc,'08',A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'),'07',(A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(-1)),'99999999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                                'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                        FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                        WHERE A.id_mov_comp = B.id_mov_comp 
                        and A.id_cont = '".$mai_id."'
                        and A.id_compras = '".$mai_id."'
                        and A.voucher = '".$voucher."'
                        and A.estado = 'P' 
                        and A.id_nivel_gasto like '7%' 
                        and A.tipo_bi = '1' 
                        UNION ALL
                        SELECT  
                                '3.3' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                                to_char(decode(A.tipo_doc,'08',A.importe,A.importe*(-1)),'99,999,999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                                'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                        FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                        WHERE A.id_mov_comp = B.id_mov_comp 
                        AND A.id_cont = '".$mai_id."'
                        AND A.id_compras = '".$mai_id."'
                        and A.voucher = '".$voucher."'
                        AND A.estado = 'P' 
                        AND A.id_nivel_gasto like '7%' 
                        AND A.tipo_bi = '2' 
                        UNION ALL                                                               
                        SELECT
                                '3.1' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_igv,A.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_igv,A.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL('7',A.id_compras),' ') departamento, 
                                to_char(decode(A.tipo_doc,'08',(A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')),((A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')))*(-1)),'99999999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_igv,A.id_compras),' ')  restriccion, 
                                'IGV Nota:      (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                A.tipo_doc||': IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                        FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                        WHERE A.id_mov_comp = B.id_mov_comp 
                        AND A.id_cont = '".$mai_id."'
                        AND A.id_compras = '".$mai_id."'
                        AND A.voucher = '".$voucher."'
                        AND A.estado = 'P' 
                        AND A.id_nivel_gasto like '7%' 
                        AND A.tipo_bi = '1'
                        UNION ALL 
                        SELECT 
                                '3.1' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL('40.01.01',A.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL('40.01.01',A.id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento,                                          
                                to_char(decode(A.tipo_doc,'08',(A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')),((A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')))*(-1)),'99,999,999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL('40.01.01',A.id_compras),' ')  restriccion, 
                                'IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                                A.tipo_doc||': IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                                A.fecha_prov,
                                'C'||A.id_mov_not memo , A.id_mov_not id_mov   
                        FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                        WHERE A.id_mov_comp = B.id_mov_comp 
                        and A.id_cont = '".$mai_id."'
                        and A.id_compras = '".$mai_id."'
                        and A.voucher = '".$voucher."'
                        and A.estado = 'P' 
                        and A.id_nivel_gasto like '7%' 
                        and A.tipo_bi = '5' 
                        and A.id_cta_gasto >= '60.01.01' 
                        UNION ALL
                        SELECT
                                '3' orden,   
                                nvl(ARON.fc_cta_aasinet@DBL_JUL('42.01.01',id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL('42.01.01',id_compras),' ') cta_cte, 
                                nvl(ARON.equiv_nivel@DBL_JUL('7',id_compras),' ') departamento, 
                                to_char(nvl(sum(importe),0)*1,'99,999,999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL('42.01.01',id_compras),' ')  restriccion, 
                                'Nota: (Doc: '||nvl(serie,'0')||'-'||nvl(numdoc,'0')||')' historico, 
                                tipo_doc||': Nota: (Doc: '||nvl(serie,'0')||'-'||nvl(numdoc,'0')||')' descripcion,
                                fecha_prov,
                                'C'||id_mov_not memo , id_mov_not id_mov  
                        FROM ARON.compras_notas@DBL_JUL 
                        WHERE id_cont = '".$mai_id."'
                        AND id_compras = '".$mai_id."'
                        AND voucher = '".$voucher."'
                        AND estado = 'P' 
                        AND id_nivel_gasto like '7%' 
                        GROUP BY id_compras,importe,igv,id_mov_not,serie,numdoc,tipo_doc,fecha_prov 
                        --ORDER BY FECHA_PROV,ID_MOV,ORDEN                                                              
                        UNION ALL
                        SELECT '4.1' orden, 
                                NVL(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ') cuenta,  ----20.**.**
                                NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ') cta_cte, 
                                NVL(ARON.equiv_nivel@DBL_JUL(a.id_nivel_alm,b.id_compras),' ') departamento, 
                                TO_CHAR(NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0)*-1,'99,999,999.99') valor, 
                                '10' fondo, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ')  restriccion,
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) historico, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) descripcion,
                                b.fecha_prov,
                                'K'||b.id_mov_comp memo,
                                b.id_mov_comp id_mov
                        FROM ARON.almacen_ing_not@DBL_JUL a, ARON.compras_notas@DBL_JUL b 
                        WHERE a.id_mov_not = b.id_mov_not
                        and b.id_cont = '".$mai_id."'
                        AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                        and b.id_compras = '".$mai_id."'
                        and b.voucher = '".$voucher."'
                        AND a.estado = 'P' 
                        and b.id_nivel_gasto like '7%' 
                        UNION ALL
                        SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor)*-1,'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,'K'||g.memo memo,g.memo id_mov
                        FROM ( 
                                SELECT '4' orden, 
                                        NVL(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ') cuenta, ----61.**.**
                                        NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ') cta_cte, 
                                        NVL(ARON.equiv_nivel@DBL_JUL(b.id_nivel_gasto,b.id_compras),' ') departamento, 
                                        NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0)*-1 valor,  
                                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ')  restriccion, 
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||ARON.ruc2@DBL_JUL(b.id_proveedor)||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP'),1,60) historico, 
                                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP')||'-'||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) descripcion,
                                        b.fecha_prov,
                                        b.id_mov_not memo 
                                FROM ARON.almacen_ing_not@DBL_JUL a, ARON.compras_notas@DBL_JUL b 
                                WHERE a.id_mov_not = b.id_mov_not 
                                and b.id_cont = '".$mai_id."'
                                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                                and b.id_compras = '".$mai_id."'
                                and b.voucher = '".$voucher."'
                                AND a.estado        = 'P' 
                                and b.id_nivel_gasto like '7%' 
                                )g 
                        GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                        ORDER BY FECHA_PROV,ORDEN,ID_MOV ";
                    $oQuery = DB::connection('oracle')->select($query);    
                return $oQuery;
            }
            public static function listCWSeatAaasinetTLCCAT($id_nivel,$id_anho,$voucher){
                $mai_id = "001-".$id_anho;
                $query = "SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_PAG ,A.ID_TLECRD),' ') AS cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.ID_CUENTA_PAG,A.ID_TLECRD),' ') AS cta_cte, 
                                '10' fondo,
                                nvl(ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_tlecrd),' ') AS departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.ID_CUENTA_PAG,A.ID_TLECRD),' ')  restriccion,
                                to_char(A.IMPORTE *decode(A.DC,'D',-1,1),'99999999.99') AS valor,   
                                decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_PAG ,A.ID_TLECRD),' '),'1112025',decode(A.OTRO_IMP,0,'',to_char(A.OTRO_IMP *decode(A.DC,'D',-1,1),'99999999.99')),'') AS IMP,
                                a.glosa1 descripcion, 
                                '1' ORDEN,
                                ID_MOV_TLECRD memo,
                                A.NUM_OPERACION,
                                A.ID_CHEQUERA        
                        FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                        WHERE A.ID_TLECRD = B.ID_TLECRD 
                        AND A.VOUCHER = B.VOUCHER  
                        AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                        AND A.ID_TLECRD = '".$mai_id."'
                        AND A.VOUCHER = '".$voucher."' 
                        AND A.ID_NIVEL like '7%' 
                        AND A.TIPO IN ('PC','TTL')     
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' ') AS cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ') AS cta_cte, 
                                '10' fondo,
                                DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,  
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ')  restriccion,
                                to_char(SUM(A.IMPORTE *decode(A.DC,'D',1,-1)),'99999999.99') AS valor, 
                                decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                                substr(' ('||ARON.nombre_banco@DBL_JUL(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||'-'||A.FECHA||')',1,60) descripcion,         
                                '1.1' ORDEN,
                                '' memo,
                                A.NUM_OPERACION,
                                A.ID_CHEQUERA
                        FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                        WHERE A.ID_TLECRD = B.ID_TLECRD 
                        AND A.VOUCHER = B.VOUCHER  
                        AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                        AND A.ID_TLECRD = '".$mai_id."'
                        AND A.VOUCHER = '".$voucher."'
                        AND A.ID_NIVEL like '7%'
                        AND A.TIPO IN ('PC','TTL')     
                        --GROUP BY A.ID_TLECRD,A.ID_CUENTA_EGRE,SUBSTR(A.ID_NIVEL,1,1),A.ID_CHEQUERA,A.NUM_OPERACION,A.FECHA
                        GROUP BY A.ID_TLECRD,A.ID_CUENTA_EGRE,A.ID_NIVEL,A.ID_CHEQUERA,A.NUM_OPERACION,A.FECHA
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' ') AS cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ') AS cta_cte,  
                                '10' fondo,                       
                                DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ')  restriccion,                          
                                to_char(SUM(A.IMPORTE *decode(A.DC,'D',-1,1)),'99999999.99') AS valor,  
                                decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                                substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco@DBL_JUL(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,                                 
                                '2' ORDEN,
                                A.id_mov_tlecrd memo,
                                A.NOMBRE,
                                A.ID_CHEQUERA 
                        FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                        WHERE A.ID_TLECRD = B.ID_TLECRD 
                        AND A.VOUCHER = B.VOUCHER  
                        AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                        AND A.ID_TLECRD = '".$mai_id."'
                        AND A.VOUCHER = '".$voucher."'
                        AND A.ID_NIVEL like '7%'
                        AND A.TIPO IN ('RT')     
                        GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.ID_CUENTA_EGRE,A.ID_NIVEL,A.NUM_OPERACION,A.id_chequera,A.num_operacion, A.VOUCHER,A.NOMBRE,A.FECHA
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_PAG ,A.ID_TLECRD),' ') AS cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.ID_CUENTA_PAG,A.ID_TLECRD),' ') AS cta_cte,  
                                '10' fondo,                       
                                DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento, 
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.ID_CUENTA_PAG,A.ID_TLECRD),' ')  restriccion,                       
                                to_char(SUM(A.IMPORTE *decode(A.DC,'D',1,-1)),'99999999.99') AS valor,   
                                decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_PAG ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                                substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco@DBL_JUL(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,         
                                '2.1' ORDEN,
                                A.id_mov_tlecrd memo ,
                                A.NOMBRE,
                                A.ID_CHEQUERA
                        FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                        WHERE A.ID_TLECRD = B.ID_TLECRD 
                        AND A.VOUCHER = B.VOUCHER  
                        AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                        AND A.ID_TLECRD = '".$mai_id."'
                        AND A.VOUCHER = '".$voucher."'
                        AND A.ID_NIVEL like '7%'
                        AND A.TIPO IN ('RT')     
                        GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.ID_CUENTA_PAG,A.ID_NIVEL,A.NUM_OPERACION,A.FECHA,A.id_chequera,A.num_operacion,A.VOUCHER,A.NOMBRE
                        UNION ALL                
                        SELECT  
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_pag,A.ID_TLECRD),' ') AS cuenta,   
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_pag,A.ID_TLECRD),' ') AS cta_cte,
                                '10' fondo,                          
                                DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,   
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_pag,A.ID_TLECRD),' ')  restriccion,                    
                                to_char(SUM(A.IMPORTE *decode(A.DC,'D',-1,1)),'99999999.99') AS valor,        
                                decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_pag,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99')))  IMP,                      
                                A.GLOSA1,         
                                '2' ORDEN,
                                A.id_mov_tlecrd memo,
                                A.NUM_OPERACION,
                                A.ID_CHEQUERA
                        FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                        WHERE A.ID_TLECRD = B.ID_TLECRD 
                        AND A.VOUCHER = B.VOUCHER  
                        AND A.ID_NIVEL = B.ID_NIVEL 
                        AND A.ID_TLECRD = '".$mai_id."'
                        AND A.VOUCHER = '".$voucher."'
                        AND A.ID_NIVEL like '7%'
                        AND A.TIPO = 'DTL'   
                        GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.id_cuenta_pag,A.ID_NIVEL,A.NOMBRE,A.NUM_OPERACION,A.FECHA,A.id_chequera,A.num_operacion,A.VOUCHER,A.GLOSA1
                        UNION ALL
                        SELECT  
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cuenta_chq,a.id_tlecrd),' ') cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cuenta_chq,a.id_tlecrd),' ') cta_cte,  
                                '10' fondo,
                                nvl(ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_tlecrd),' ') departamento,  
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cuenta_chq,a.ID_TLECRD),' ')  restriccion,
                                to_char(sum(a.importe*decode(b.dc,'D',1,-1)),'99999999.99') valor, 
                                decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cuenta_chq,a.id_tlecrd),' '),'1112025',decode(sum(a.OTRO_IMP*decode(b.dc,'D',1,-1)),0,'',to_char(sum(a.OTRO_IMP*decode(b.dc,'D',1,-1)),'99999999.99'))) IMP,                        
                                'Nro. Oper. ' ||A.NUM_OPERACION||'-'||a.fecha,
                                '1' orden,
                                '' memo,
                                A.NUM_OPERACION,
                                A.ID_CHEQUERA
                        FROM ARON.tlecrd_detrac@DBL_JUL a, ARON.tlecrd_mov_doc@DBL_JUL b, ARON.tlecrd_voucher@DBL_JUL c  
                        WHERE a.id_tlecrd = b.id_tlecrd  
                        and a.id_mov_tlecrd = b.id_mov_tlecrd 
                        and a.id_tlecrd = c.id_tlecrd 
                        and a.id_nivel = c.id_nivel  
                        and a.voucher = c.voucher 
                        and a.id_tlecrd = '".$mai_id."'
                        and a.voucher = '".$voucher."'
                        and a.id_nivel like '7%' 
                        group by a.id_tlecrd,A.id_cuenta_chq,a.id_nivel,A.ID_CHEQUERA,a.NUM_OPERACION,a.fecha
                        ORDER BY ID_CHEQUERA,NUM_OPERACION,valor,ORDEN,CUENTA ";
                $oQuery = DB::connection('oracle')->select($query);    
                return $oQuery;
            }
            public static function listCWSeatAaasinetCHQCAT($id_nivel,$id_anho,$voucher){
                $mai_id = "001-".$id_anho;
                $query = "SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ') cta_cte,
                                '10' fondo,  					
                                nvl(decode(substr(a.id_nivel,1,1),
                                '1',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '2',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '3',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '4',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '5',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '6',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-11','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque))),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ')  restriccion, 					    
                                a.id_chequera, 					   
                                to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') valor, 
                                                        to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99') imp,
                                substr(a.glosa_exp_ctr,1,30)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY') descripcion,
                                a.id_mov_efec MEMO,
                                '1' ORDEN 
                        FROM ARON.cheque_detalle@DBL_JUL a, ARON.cheque_voucher@DBL_JUL b
                        WHERE a.id_cheque = b.id_cheque 
                        AND a.voucher = b.voucher 
                        AND a.id_cheque = '".$mai_id."' 
                        AND a.voucher = '".$voucher."' 
                        AND a.id_nivel like '7%' 
                        AND b.id_nivel = '7'  
                        AND a.num_operacion like '%Cheq%' 
                        UNION ALL
                        SELECT
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_egre,a.id_cheque),' ') cuenta,
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_egre,a.id_cheque),' ') cta_cte, 
                                '10' fondo,
                                nvl(ARON.equiv_nivel@DBL_JUL('7',a.id_cheque),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,a.id_cheque),' ')  restriccion,
                                id_chequera,                  
                                to_char(sum(nvl(importe,0))*decode(dc,'D',-1,1),'999999999.99') importe,
                                                        to_char(sum(nvl(otro_imp,0))*decode(dc,'D',-1,1),'999999999.99') imp,
                                'Total '||nvl(num_operacion,' ')||'-'||to_char(b.fecha,'DD/MM/YY') descripcion,
                                '' memo,
                                '1.1' ORDEN
                        FROM ARON.cheque_detalle@DBL_JUL a, ARON.cheque_voucher@DBL_JUL b
                        WHERE a.id_cheque = b.id_cheque
                        AND a.voucher = b.voucher 
                        AND substr(a.id_nivel,1,1) = b.id_nivel
                        AND a.id_cheque = '".$mai_id."' 
                        AND a.voucher = '".$voucher."'
                        AND a.num_operacion like '%Cheq%' 
                        AND a.id_nivel like '7%' 
                        GROUP BY a.id_cheque,a.id_cuenta_egre,a.id_chequera, a.num_operacion,b.fecha,a.dc
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ') cta_cte,  
                                '10' fondo,					
                                nvl(decode(substr(a.id_nivel,1,1),
                                '1',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),  
                                '2',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '3',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '4',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '5',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '6',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque))),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ')  restriccion,
                                a.id_chequera, 	 					    		                        
                                to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') importe,
                                                        to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99') imp,
                                substr(a.glosa_exp_ctr,1,50) descripcion,
                                a.id_mov_efec MEMO,
                                '2' ORDEN      
                        FROM ARON.cheque_detalle@DBL_JUL a, ARON.cheque_voucher@DBL_JUL b, ARON.egresos_chequera@DBL_JUL d 
                        where a.id_cheque = b.id_cheque 
                        and a.voucher = b.voucher 
                        and a.id_cheque = d.id_compras 
                        and a.id_chequera = d.id_chequera
                        and a.id_cheque = '".$mai_id."' 
                        and a.voucher = '".$voucher."'
                        and a.id_nivel like '7%' 
                        and b.id_nivel = '7' 
                        and a.num_operacion like '%Oper%' 
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_egre,id_cheque),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_egre,id_cheque),' ') cta_cte,
                                '10' fondo,
                                nvl(ARON.equiv_nivel@DBL_JUL('7',id_cheque),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,id_cheque),' ')  restriccion,
                                id_chequera, 
                                to_char(sum(nvl(importe,0))*decode(dc,'C',1,-1),'999999999.99') importe,
                                                        to_char(sum(nvl(otro_imp,0))*decode(dc,'C',1,-1),'999999999.99') imp,
                                num_operacion as descripcion, 
                                id_mov_efec MEMO,
                                '2.1' ORDEN 
                        FROM ARON.cheque_detalle@DBL_JUL 
                        WHERE id_cheque = '".$mai_id."' 
                        AND voucher = '".$voucher."'
                        AND num_operacion like '%Oper%' 
                        AND id_nivel like '7%' 
                        group by id_cheque,id_mov_efec,id_cuenta_egre,id_chequera, num_operacion,dc
                        --ORDER BY ORDEN,MEMO
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_egre,a.id_cheque),' ') cuenta, 
                                nvl(ARON.cta_cte@DBL_JUL(a.id_cuenta_egre,a.id_cheque),' ') cta_cte,  		
                                '10' fondo,			
                                nvl(decode(substr(a.id_nivel,1,1),
                                '1',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '2',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '3',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '4',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '5',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                                '6',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque))),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,id_cheque),' ')  restriccion, 
                                a.id_chequera,					                        
                                to_char(nvl(sum(a.importe),0)*decode(a.dc,'D',-1,1),'999999999.99') valor,
                                                        to_char(nvl(sum(a.otro_imp),0)*decode(a.dc,'D',-1,1),'999999999.99') imp,
                                substr(ARON.prov_ret_xmov@DBL_JUL(a.id_mov_efec),1,12)||'-'||nvl(a.glosa,' ')||'-'||to_char(a.fecha,'dd/mm/yy') descripcion,
                                id_mov_efec MEMO,
                                '3' ORDEN 
                        FROM ARON.cheque_detalle@DBL_JUL a, ARON.cont_plan@DBL_JUL b 
                        WHERE a.id_cuenta_egre = b.id_cuenta 
                        AND a.id_cheque = '".$mai_id."' 
                        AND a.voucher = '".$voucher."'
                        AND b.id_cont = '".$mai_id."' 
                        AND a.id_nivel like '7%' 
                        AND a.tipo = 'RT' 
                        GROUP BY id_cheque,id_cuenta_egre,a.id_cuenta_pag, dc,id_mov_efec,glosa,a.fecha,id_chequera,a.id_nivel
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_pag,id_cheque),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_pag,id_cheque),' ') cta_cte,
                                '10' fondo,
                                nvl(ARON.equiv_nivel@DBL_JUL('7',id_cheque),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_pag,id_cheque),' ')  restriccion,
                                id_chequera,
                                to_char(sum(nvl(importe,0))*decode(dc,'D',1,-1),'999999999.99') importe, 
                                                        to_char(sum(nvl(otro_imp,0))*decode(dc,'D',1,-1),'999999999.99') imp,
                                substr(ARON.prov_ret_xmov@DBL_JUL(id_mov_efec),1,12)||'-'||nvl(glosa,' ')||'-'||to_char(fecha,'dd/mm/yy') historico,
                                id_mov_efec MEMO,
                                '3' ORDEN 
                        FROM ARON.cheque_detalle@DBL_JUL 
                        WHERE id_cheque = '".$mai_id."' 
                        AND id_nivel like '7%' 
                        AND tipo = 'RT' 
                        AND voucher = '".$voucher."'
                        GROUP BY id_cheque,id_cuenta_pag, dc,id_mov_efec,glosa,fecha,id_chequera
                        ORDER BY ORDEN,cuenta,MEMO,valor ";
                $oQuery = DB::connection('oracle')->select($query);    
                return $oQuery;
            }

            
            
            public static function listCWSeatAaasinetIncomeCAT($id_nivel,$id_anho,$voucher){
                $mai_id = "001-".$id_anho;
                $query = "--QUERY 1
                        SELECT         
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ') cuenta,          
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ') cta_cte,
                                '10' fondo,                             
                                nvl(decode(substr(a.id_nivel,1,1),'1',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'2',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'3',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'4',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL('4',a.id_compras)),'5',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'6',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-11','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras))),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ')  restriccion,     
                                nvl(a.importe,0)*decode(a.dc,'D',1,-1) valor, 
                                                        nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1) imp,
                                NVL((select n.serie||'-'||n.NUMDOC from ARON.IMP_REGVENTAS@DBL_JUL n join ARON.IMP_INGVNT@DBL_JUL b
                                on n.ID_MOV_VNT=b.ID_MOV_VNT where n.ID_VENTA=b.ID_VENTA and n.ID_VENTA=a.ID_COMPRAS  
                                and b.ID_MOV_ING=a.ID_MOV_EFEC ),(select n.serie||'-'||n.NUMDOC from ARON.valm_REGVENTAS@DBL_JUL n join ARON.IMP_INGVNT@DBL_JUL b
                                on n.ID_MOV_VNT=b.ID_MOV_VNT where n.ID_VENTA=b.ID_VENTA and n.ID_VENTA=a.ID_COMPRAS  
                                and b.ID_MOV_ING=a.ID_MOV_EFEC ))||' '||decode(a.tipo,'IC',decode(a.tipo2,'XD',substr(a.glosa,1,50),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')) descripcion,
                                a.id_mov_efec memo,
                                '1.1' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  a, ARON.cont_plan@DBL_JUL  b
                        where a.id_compras = b.id_cont 
                        and a.id_cuenta_egre = b.id_cuenta 
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '7%' 
                        and a.tipo in ('IC','ID')
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ') cuenta,
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ') cta_cte, 
                                '10' fondo,             
                                nvl(ARON.equiv_nivel@DBL_JUL('7',id_compras),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ')  restriccion,
                                nvl(sum(importe),0)*decode(dc,'C',1,-1) valor, 
                                                        nvl(sum(otro_imp),0)*decode(dc,'C',1,-1) imp,
                                'Total Caja Operativa' descripcion,
                                '' memo,
                                '1' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  
                        WHERE id_compras = '".$mai_id."'
                        AND id_nivel like '7%'
                        AND tipo in ('IC','ID') 
                        AND voucher = '".$voucher."'
                        GROUP BY id_compras,id_cuenta_egre, dc
                        UNION ALL
                        --QUERY 2
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' ') cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' ') CTA_CTE, 
                                '10' fondo,     
                                nvl(ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras),' ') departamento,     
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ')  restriccion,   
                                nvl(a.importe,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1) valor,  
                                                        nvl(a.otro_imp,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1) imp,
                                decode(a.tipo,'ED',substr(a.glosa,1,50),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')) descripcion,
                                a.id_mov_efec memo,
                                '2' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  a, ARON.cont_plan@DBL_JUL  b 
                        where decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag) = b.id_cuenta 
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '7%'  
                        and not a.tipo in ('IC','ID','DC','PC','RT','DCH') 
                        --order by cuenta_e, fecha_ord, a.num_operacion, cuenta_p 
                        UNION ALL
                        SELECT 
                                X.CUENTA,
                                X.CTA_CTE,
                                '10' FONDO,
                                X.departamento,
                                X.RESTRICCION,
                                SUM(X.VALOR) VALOR,
                                                        SUM(X.IMP) IMP,
                                'Total Egresos y Depositos al Banco' descripcion,
                                '' MEMO,
                                '2.1' ORDEN
                        FROM (
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ') cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ') CTA_CTE,        
                                        nvl(ARON.equiv_nivel@DBL_JUL('7',a.id_compras),' ') departamento,     
                                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ')  restriccion,   
                                        nvl(sum(a.importe),0)*decode(decode(a.dc,'C','C',a.dc),'D',1,-1) valor,
                                        nvl(sum(a.otro_imp),0)*decode(decode(a.dc,'C','C',a.dc),'D',1,-1) imp
                                FROM ARON.egresos_detalle@DBL_JUL  a, ARON.cont_plan@DBL_JUL  b 
                                where decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag) = b.id_cuenta 
                                and a.id_compras = '".$mai_id."'
                                and a.voucher = '".$voucher."' 
                                and b.id_cont = '".$mai_id."'
                                and a.id_nivel like '7%' 
                                and not a.tipo in ('IC','ID','DC','PC','RT','DCH') 
                                GROUP BY a.id_compras,a.tipo,a.id_cuenta_egre,a.id_cuenta_pag,a.dc
                        ) X
                        GROUP BY X.CUENTA,X.CTA_CTE,X.departamento,X.RESTRICCION
                        --QUERY 5
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ') cta_cte, 
                                '10' fondo,                                     
                                nvl(decode(substr(a.id_nivel,1,1),'1',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'2',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'3',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'4',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'5',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'6',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras))),' ') departamento,                                                                         
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ')  restriccion, 
                                nvl(a.importe,0)*decode(a.dc,'D',1,-1) valor,  
                                                        nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1) imp,
                                substr(ARON.prov_ret_xmov@DBL_JUL(a.id_mov_efec),1,12)||'-'||nvl(a.glosa,' ')||'-'||to_char(a.fecha,'dd/mm/yy') historico,
                                a.id_mov_efec memo,
                                '5' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  a, ARON.cont_plan@DBL_JUL  b
                        where a.id_cuenta_egre = b.id_cuenta  
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '7%' 
                        and a.tipo = 'RT' 
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ') cta_cte, 
                                '10' fondo, 
                                nvl(ARON.equiv_nivel@DBL_JUL('7',id_compras),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ')  restriccion,  
                                sum(nvl(importe,0))*decode(dc,'C',1,-1) importe, 
                                                        sum(nvl(otro_imp,0))*decode(dc,'C',1,-1) imp,
                                'Total Rentenciones' descripcion,
                                '' memo,
                                '5.1' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  
                        where id_compras = '".$mai_id."'
                        and id_nivel like '7%' 
                        and tipo = 'RT' 
                        and voucher = '".$voucher."' 
                        GROUP BY id_compras,id_cuenta_egre, dc 
                        ORDER BY ORDEN,CUENTA,CTA_CTE ";
                $oQuery = DB::connection('oracle')->select($query);    
                return $oQuery;
            }



/**============================================================================== */

public static function uploadSeatAaasinetSalesCAT($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    restriccion as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    descripcion as \"Description\", 
                                    memo as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                        SELECT a.serie,a.numvnt, 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ') cta_cte, 
                        '10' fondo,                                                     
                        nvl(decode(ARON.equiv@DBL_JUL(b.id_cuenta_vnt,b.id_venta)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(b.id_nivel_vnt,b.id_venta)),' ') departamento, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(b.id_cuenta_vnt,b.id_venta),' ')  restriccion,
                        to_char((b.importe-nvl(b.igv,0))*decode(b.dc,'D',-1,1),'99999999.99') valor,
                        decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                        a.id_mov_vnt memo,
                        a.docvnt
        FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
        WHERE a.id_venta = b.id_venta 
        AND a.id_mov_vnt = b.id_mov_vnt 
        AND a.id_venta = '".$mai_id."' 
        AND a.VOUCHER_VNT = '".$voucher."'
        AND b.id_nivel_vnt like '7%' 
        AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
        AND a.estado = 'V'                                      
        UNION ALL 
        SELECT a.serie,a.numvnt, 
                nvl(ARON.fc_cta_aasinet@DBL_JUL('40.01.01',b.id_venta),' ') cuenta, 
                ' ' cta_cte, 
                '10' fondo,
                '11010102' departamento,                                                        
                nvl(ARON.fc_restriccion_aasinet@DBL_JUL('40.01.01',b.id_venta),' ')  restriccion,
                to_char(nvl(b.igv,0)*decode(b.dc,'D',-1,1),'99999999.99') valor,  
                decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(b.detalle,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                a.id_mov_vnt memo,
                a.docvnt
        FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
        WHERE a.id_venta = b.id_venta 
        AND a.id_mov_vnt = b.id_mov_vnt 
        AND a.id_venta = '".$mai_id."' 
        AND a.VOUCHER_VNT = '".$voucher."'
        AND b.id_nivel_vnt like '7%'  
        AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
        AND a.estado = 'V'      
        AND NVL(b.igv,0) <> 0                           
        UNION ALL 
        SELECT a.serie,a.numvnt, 
                nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ') cuenta, 
                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ') cta_cte,
                '10' fondo,                                                     
                nvl(decode(ARON.equiv@DBL_JUL(a.id_cuenta_cli,a.id_venta)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel_cont,a.id_venta)),' ') departamento,
                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_cli,a.id_venta),' ')  restriccion,                                                  
                to_char(a.importe*1,'99999999.99') valor, 
                decode(a.tipo_mov,'01','CA','02','FA','09','PG','10','FA','12','FA','14','FA','16','FA','18','FA')||': (Doc:'||serie||'-'||to_number(a.numvnt)||')-'||substr(a.glosa,1,30)||' '||substr(ARON.apellido3@DBL_JUL(a.id_personal),1,20) descripcion,
                a.id_mov_vnt memo,
                a.docvnt
        FROM ARON.upeuj_regventas@DBL_JUL a, ARON.upeuj_regdetalle@DBL_JUL b 
        WHERE a.id_venta = b.id_venta 
        AND a.id_mov_vnt = b.id_mov_vnt 
        AND a.id_venta = '".$mai_id."' 
        AND a.VOUCHER_VNT = '".$voucher."'
        AND b.id_nivel_vnt like '7%'  
        AND a.tipo_mov in ('01','02','09','10','12','14','16','18') 
        AND a.estado = 'V' 
        GROUP BY a.id_venta,a.id_mov_vnt,a.id_cuenta_cli,a.serie,a.numvnt,a.id_nivel_cont,a.importe,a.tipo_mov,a.id_personal,a.glosa,a.docvnt 
        ORDER BY DOCVNT,SERIE,NUMVNT,VALOR,CUENTA 
                    ) X ";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }



    public static function uploadSeatAaasinetPurchasesCAT($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    RESTRICCION as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    DESCRIPCION as \"Description\", 
                                    MEMO as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                        SELECT 
                        '1' orden, 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(C.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(C.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL(C.id_nivel_gasto,A.id_compras),' ') departamento, 
                        to_char(nvl(C.importe+NVL(DECODE(C.TIPO_BI,'2',C.IGV,0),0),0),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(C.id_cta_gasto,A.id_compras),' ')  restriccion,
                        substr('(Doc: '||A.serie||'-'||A.numdoc||')-'||substr(C.detalle,1,25)||'-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) historico,
                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||substr(C.detalle,1,30)||'-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                FROM ARON.compras_registro@DBL_JUL A, ARON.compras_detalle@DBL_JUL C  
                WHERE A.id_mov_comp = C.id_mov_comp 
                and A.id_compras = C.id_compras 
                AND A.id_cont = '".$mai_id."' 
                and A.id_compras = '".$mai_id."'                                                                        
                and A.estado = 'P' 
                and A.id_nivel_cont like '7%' 
                and A.voucher = '".$voucher."'
                UNION ALL
                SELECT  
                        '1.1' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(C.id_cta_igv,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(C.id_cta_igv,a.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_cont,A.id_compras),' ') departamento, 
                        to_char(nvl(C.igv,0),'99,999,999.99') valor, 
                        ' ' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(C.id_cta_igv,A.id_compras),' ')  restriccion, 
                        substr('(IGV: Doc: '||A.serie||'-'||A.numdoc||')-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) historico,
                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||'IGV Compras Div.'||'-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                FROM ARON.compras_registro@DBL_JUL A, ARON.compras_detalle@DBL_JUL C 
                WHERE A.id_mov_comp = C.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."' 
                and A.estado = 'P' 
                and A.id_nivel_cont like '7%' 
                and C.tipo_bi in ('1','5') 
                UNION ALL
                SELECT 
                        '1.2' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(b.id_cuenta_comp,a.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(b.id_cuenta_comp,a.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL(a.id_nivel_cont,a.id_compras),' ') departamento, 
                        to_char(nvl(sum(a.importe*-1),0),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(b.id_cuenta_comp,a.id_compras),' ')  restriccion, 
                        substr(a.tipo_doc||':(Doc: '||A.serie||'-'||A.numdoc||')-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) historico,
                        A.tipo_doc||substr(':(Doc: '||A.serie||'-'||A.numdoc||')-'||'COMPRAS'||'-'||ARON.ruc2@DBL_JUL(A.id_proveedor),1,60) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_comp memo, A.id_mov_comp id_mov 
                FROM ARON.compras_registro@DBL_JUL a, ARON.compras_main@DBL_JUL b 
                WHERE a.id_compras = b.id_compras 
                and a.id_cont = '".$mai_id."'
                and a.id_compras = '".$mai_id."'
                and a.voucher = '".$voucher."'
                and a.estado = 'P' 
                and a.id_nivel_cont like '7%' 
                GROUP BY a.id_compras,b.id_cuenta_comp,a.id_nivel_cont,a.importe,a.tipo_doc,A.serie,A.numdoc,A.id_proveedor,A.id_mov_comp,A.tipo_doc,A.fecha_prov
                UNION ALL
                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor),'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,
                'C'||g.memo memo,g.memo id_mov 
                FROM ( 
                SELECT '1' orden, 
                        NVL(ARON.fc_cta_aasinet@DBL_JUL(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_COMP),b.id_compras),' ') cuenta,   --60.**.**
                        NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_comp),b.id_compras),' ') cta_cte, 
                        NVL(ARON.equiv_nivel@DBL_JUL(a.id_nivel_alm,b.id_compras),' ') departamento, 
                        NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0) valor,  
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(DECODE(a.id_cuenta_alm,'20.04.01','60.01.02','20.04.02','60.02.02',a.id_cuenta_COMP),b.id_compras),' ')  restriccion, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-' ||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',ARON.ruc2@DBL_JUL(b.id_proveedor)||' COMPRA MERCADERIAS BAZAR',ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo)||ARON.ruc2@DBL_JUL(b.id_proveedor)),1,60) historico,
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-' ||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',ARON.ruc2@DBL_JUL(b.id_proveedor)||' COMPRA MERCADERIAS BAZAR',ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo)||ARON.ruc2@DBL_JUL(b.id_proveedor)),1,60) descripcion,
                        b.fecha_prov, 
                        b.id_mov_comp memo 
                FROM ARON.almacen_ing_com@DBL_JUL a,ARON.compras_registro@DBL_JUL b 
                WHERE a.id_mov_comp = b.id_mov_comp 
                and b.id_cont = '".$mai_id."'
                and b.id_compras = '".$mai_id."'
                and b.voucher = '".$voucher."'
                AND a.estado        = 'P' 
                and b.id_nivel_cont like '7%' 
                )g 
                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                UNION ALL
                SELECT  
                        '1.1' orden,     
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ') cuenta,          
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ') cta_cte,                                                                     
                        NVL(ARON.equiv_nivel@DBL_JUL(decode(SUBSTR(c.id_almacen,1,3),'051',a.id_nivel_cont,C.id_nivel_alm),A.id_compras),' ') departamento, 
                        to_char(nvl(sum(C.igv),0),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(decode(C.tipo_bi,'1',C.id_cuenta_igv,'2','64.01.01','3','64.01.01',C.id_cuenta_igv),a.id_compras),' ')  restriccion, 
                        'IGV Comp Almacen '||'Doc: '||A.serie||'-'||A.numdoc historico,
                        A.tipo_doc||'(Doc:'||A.serie||'-'||A.numdoc||') IGV Comp Almacen ' descripcion,  
                        A.fecha_prov,
                        'C'||A.id_mov_comp memo, 
                        A.id_mov_comp id_mov    
                FROM ARON.compras_registro@DBL_JUL A, ARON.almacen_ing_com@DBL_JUL C 
                WHERE A.id_mov_comp = C.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P'  
                and C.tipo_bi in ('1','5')  
                and A.id_nivel_cont like '7%' 
                group by a.id_compras,A.id_mov_comp ,C.id_cuenta_igv,C.tipo_bi,C.id_nivel_alm,A.serie,A.numdoc,a.id_nivel_cont,c.id_almacen,A.tipo_doc,A.fecha_prov
                having sum(C.igv) <> 0
                UNION ALL
                SELECT '2' orden, 
                        NVL(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ') cuenta,  ----20.**.**
                        NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ') cta_cte, 
                        NVL(ARON.equiv_nivel@DBL_JUL(b.id_nivel_cont,b.id_compras),' ') departamento, 
                        TO_CHAR(NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ')  restriccion, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) historico, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) descripcion,
                        b.fecha_prov,
                        'K'||b.id_mov_comp memo,
                        b.id_mov_comp id_mov
                FROM ARON.almacen_ing_com@DBL_JUL a, ARON.compras_registro@DBL_JUL b 
                WHERE a.id_mov_comp = b.id_mov_comp 
                and b.id_cont = '".$mai_id."'
                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                and b.id_compras = '".$mai_id."'
                and b.voucher = '".$voucher."'
                AND a.estado = 'P' 
                and b.id_nivel_cont like '7%' 
                UNION ALL
                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor)*-1,'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,'K'||g.memo memo,g.memo id_mov
                FROM ( 
                SELECT '2.1' orden, 
                        NVL(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ') cuenta, ----61.**.**
                        NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ') cta_cte, 
                        NVL(ARON.equiv_nivel@DBL_JUL(a.id_nivel_alm,b.id_compras),' ') departamento, 
                        NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0) valor,  
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ')  restriccion, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||ARON.ruc2@DBL_JUL(b.id_proveedor)||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP'),1,60) historico, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063',' VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP')||'-'||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) descripcion,
                        b.fecha_prov,
                        b.id_mov_comp memo 
                FROM ARON.almacen_ing_com@DBL_JUL a, ARON.compras_registro@DBL_JUL b 
                WHERE a.id_mov_comp = b.id_mov_comp 
                and b.id_cont = '".$mai_id."'
                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                and b.id_compras = '".$mai_id."'
                and b.voucher = '".$voucher."'
                AND a.estado        = 'P' 
                and b.id_nivel_cont like '7%' 
                )g 
                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                --ORDER BY FECHA_PROV,ID_MOV,ORDEN
                UNION ALL                                                                                                                                       
                SELECT 
                        '3.3' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento,  
                        to_char(decode(A.tipo_doc,'08',A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'),'07',(A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(-1)),'99999999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov  
                FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P' 
                and A.id_nivel_gasto like '7%' 
                and A.tipo_bi = '5' 
                and A.id_cta_gasto >= '60.01.01' 
                UNION ALL
                SELECT  
                        '3.3' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                        to_char(decode(A.tipo_doc,'08',A.importe,A.importe*(-1)),'99999999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov  
                FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P' 
                and A.id_nivel_gasto like '7%' 
                and A.tipo_bi in ('3','4') 
                UNION ALL
                SELECT
                        '3.3' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                        to_char(decode(A.tipo_doc,'08',A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'),'07',(A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(-1)),'99999999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P' 
                and A.id_nivel_gasto like '7%' 
                and A.tipo_bi = '1' 
                UNION ALL
                SELECT  
                        '3.3' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento, 
                        to_char(decode(A.tipo_doc,'08',A.importe,A.importe*(-1)),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_gasto,A.id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                AND A.id_cont = '".$mai_id."'
                AND A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                AND A.estado = 'P' 
                AND A.id_nivel_gasto like '7%' 
                AND A.tipo_bi = '2' 
                UNION ALL                                                               
                SELECT
                        '3.1' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cta_igv,A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cta_igv,A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL('7',A.id_compras),' ') departamento, 
                        to_char(decode(A.tipo_doc,'08',(A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')),((A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')))*(-1)),'99999999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cta_igv,A.id_compras),' ')  restriccion, 
                        'IGV Nota:      (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov 
                FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                AND A.id_cont = '".$mai_id."'
                AND A.id_compras = '".$mai_id."'
                AND A.voucher = '".$voucher."'
                AND A.estado = 'P' 
                AND A.id_nivel_gasto like '7%' 
                AND A.tipo_bi = '1'
                UNION ALL 
                SELECT 
                        '3.1' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL('40.01.01',A.id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL('40.01.01',A.id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL(A.id_nivel_gasto,A.id_compras),' ') departamento,                                          
                        to_char(decode(A.tipo_doc,'08',(A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')),((A.importe/to_number(1||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99'))*(to_number(0||'.'||ARON.IGV_VALOR@DBL_JUL(A.ID_COMPRAS,A.ID_MOV_COMP),'999.99')))*(-1)),'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL('40.01.01',A.id_compras),' ')  restriccion, 
                        'IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) historico, 
                        A.tipo_doc||': IGV Nota: (Doc: '||nvl(A.serie,'0')||'-'||nvl(A.numdoc,'0')||')...ref:'||nvl(B.serie,'0')||'-'||nvl(B.numdoc,B.guia) descripcion,
                        A.fecha_prov,
                        'C'||A.id_mov_not memo , A.id_mov_not id_mov   
                FROM ARON.compras_notas@DBL_JUL A, ARON.compras_registro@DBL_JUL B 
                WHERE A.id_mov_comp = B.id_mov_comp 
                and A.id_cont = '".$mai_id."'
                and A.id_compras = '".$mai_id."'
                and A.voucher = '".$voucher."'
                and A.estado = 'P' 
                and A.id_nivel_gasto like '7%' 
                and A.tipo_bi = '5' 
                and A.id_cta_gasto >= '60.01.01' 
                UNION ALL
                SELECT
                        '3' orden,   
                        nvl(ARON.fc_cta_aasinet@DBL_JUL('42.01.01',id_compras),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL('42.01.01',id_compras),' ') cta_cte, 
                        nvl(ARON.equiv_nivel@DBL_JUL('7',id_compras),' ') departamento, 
                        to_char(nvl(sum(importe),0)*1,'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL('42.01.01',id_compras),' ')  restriccion, 
                        'Nota: (Doc: '||nvl(serie,'0')||'-'||nvl(numdoc,'0')||')' historico, 
                        tipo_doc||': Nota: (Doc: '||nvl(serie,'0')||'-'||nvl(numdoc,'0')||')' descripcion,
                        fecha_prov,
                        'C'||id_mov_not memo , id_mov_not id_mov  
                FROM ARON.compras_notas@DBL_JUL 
                WHERE id_cont = '".$mai_id."'
                AND id_compras = '".$mai_id."'
                AND voucher = '".$voucher."'
                AND estado = 'P' 
                AND id_nivel_gasto like '7%' 
                GROUP BY id_compras,importe,igv,id_mov_not,serie,numdoc,tipo_doc,fecha_prov 
                --ORDER BY FECHA_PROV,ID_MOV,ORDEN                                                              
                UNION ALL
                SELECT '4.1' orden, 
                        NVL(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ') cuenta,  ----20.**.**
                        NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ') cta_cte, 
                        NVL(ARON.equiv_nivel@DBL_JUL(a.id_nivel_alm,b.id_compras),' ') departamento, 
                        TO_CHAR(NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0)*-1,'99,999,999.99') valor, 
                        '10' fondo, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_alm,b.id_compras),' ')  restriccion,
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) historico, 
                        SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||')'||'- '||SUBSTR(ARON.alm_articulo@DBL_JUL(a.id_almacen,a.id_articulo),1,25)||' - ' ||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) descripcion,
                        b.fecha_prov,
                        'K'||b.id_mov_comp memo,
                        b.id_mov_comp id_mov
                FROM ARON.almacen_ing_not@DBL_JUL a, ARON.compras_notas@DBL_JUL b 
                WHERE a.id_mov_not = b.id_mov_not
                and b.id_cont = '".$mai_id."'
                AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                and b.id_compras = '".$mai_id."'
                and b.voucher = '".$voucher."'
                AND a.estado = 'P' 
                and b.id_nivel_gasto like '7%' 
                UNION ALL
                SELECT g.orden,g.cuenta,g.cta_cte,g.departamento,TO_CHAR( sum(g.valor)*-1,'99,999,999.99') valor,'10' fondo,g.restriccion,g.historico,g.descripcion,g.fecha_prov,'K'||g.memo memo,g.memo id_mov
                FROM ( 
                        SELECT '4' orden, 
                                NVL(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ') cuenta, ----61.**.**
                                NVL(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ') cta_cte, 
                                NVL(ARON.equiv_nivel@DBL_JUL(b.id_nivel_gasto,b.id_compras),' ') departamento, 
                                NVL(DECODE(a.tipo_bi,'1',a.importe,'2',a.importe+a.igv,a.importe+a.igv),0)*-1 valor,  
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_var,b.id_compras),' ')  restriccion, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||ARON.ruc2@DBL_JUL(b.id_proveedor)||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP'),1,60) historico, 
                                SUBSTR(b.tipo_doc||':(Doc: '||b.serie||'-'||b.numdoc||') - '||DECODE(SUBSTR(A.ID_ALMACEN,1,3),'051',' VAR. DE EXISTENCIAS BAZAR','021',' VAR. DE INSUMOS LOGISTICA','062',' VAR. DE INSUMOS CAFETIN AMP','063','VAR. DE EXISTENCIAS CAFETIN APT','056',' VAR. DE EXISTENCIAS TUNAS APT','057',' VAR. DE INSUMOS TUNAS AMP')||'-'||ARON.ruc2@DBL_JUL(b.id_proveedor),1,60) descripcion,
                                b.fecha_prov,
                                b.id_mov_not memo 
                        FROM ARON.almacen_ing_not@DBL_JUL a, ARON.compras_notas@DBL_JUL b 
                        WHERE a.id_mov_not = b.id_mov_not 
                        and b.id_cont = '".$mai_id."'
                        AND SUBSTR(A.ID_ALMACEN,1,3) IN ('051','021','056','057','062','063') 
                        and b.id_compras = '".$mai_id."'
                        and b.voucher = '".$voucher."'
                        AND a.estado        = 'P' 
                        and b.id_nivel_gasto like '7%' 
                        )g 
                GROUP BY g.orden,g.cuenta, g.cta_cte,g.departamento,g.restriccion,g.historico,g.descripcion,g.fecha_prov,g.memo
                ORDER BY FECHA_PROV,ORDEN,ID_MOV
                    ) X ";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }


    public static function uploadSeatAaasinetTLCCAT($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    RESTRICCION as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    imp as \"CurrencyAmount\", 
                                    DESCRIPCION as \"Description\", 
                                    MEMO as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                        SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_PAG ,A.ID_TLECRD),' ') AS cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.ID_CUENTA_PAG,A.ID_TLECRD),' ') AS cta_cte, 
                        '10' fondo,
                        nvl(ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_tlecrd),' ') AS departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.ID_CUENTA_PAG,A.ID_TLECRD),' ')  restriccion,
                        to_char(A.IMPORTE *decode(A.DC,'D',-1,1),'99999999.99') AS valor,   
                        decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_PAG ,A.ID_TLECRD),' '),'1112025',decode(A.OTRO_IMP,0,'',to_char(A.OTRO_IMP *decode(A.DC,'D',-1,1),'99999999.99')),'') AS IMP,
                        a.glosa1 descripcion, 
                        '1' ORDEN,
                        ID_MOV_TLECRD memo,
                        A.NUM_OPERACION,
                        A.ID_CHEQUERA        
                FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."' 
                AND A.ID_NIVEL like '7%' 
                AND A.TIPO IN ('PC','TTL')     
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' ') AS cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ') AS cta_cte, 
                        '10' fondo,
                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,  
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ')  restriccion,
                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',1,-1)),'99999999.99') AS valor, 
                        decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                        substr(' ('||ARON.nombre_banco@DBL_JUL(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||'-'||A.FECHA||')',1,60) descripcion,         
                        '1.1' ORDEN,
                        '' memo,
                        A.NUM_OPERACION,
                        A.ID_CHEQUERA
                FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."'
                AND A.ID_NIVEL like '7%'
                AND A.TIPO IN ('PC','TTL')     
                --GROUP BY A.ID_TLECRD,A.ID_CUENTA_EGRE,SUBSTR(A.ID_NIVEL,1,1),A.ID_CHEQUERA,A.NUM_OPERACION,A.FECHA
                GROUP BY A.ID_TLECRD,A.ID_CUENTA_EGRE,A.ID_NIVEL,A.ID_CHEQUERA,A.NUM_OPERACION,A.FECHA
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' ') AS cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ') AS cta_cte,  
                        '10' fondo,                       
                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.ID_CUENTA_EGRE,A.ID_TLECRD),' ')  restriccion,                          
                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',-1,1)),'99999999.99') AS valor,  
                        decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_EGRE ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                        substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco@DBL_JUL(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,                                 
                        '2' ORDEN,
                        A.id_mov_tlecrd memo,
                        A.NOMBRE,
                        A.ID_CHEQUERA 
                FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."'
                AND A.ID_NIVEL like '7%'
                AND A.TIPO IN ('RT')     
                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.ID_CUENTA_EGRE,A.ID_NIVEL,A.NUM_OPERACION,A.id_chequera,A.num_operacion, A.VOUCHER,A.NOMBRE,A.FECHA
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_PAG ,A.ID_TLECRD),' ') AS cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.ID_CUENTA_PAG,A.ID_TLECRD),' ') AS cta_cte,  
                        '10' fondo,                       
                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento, 
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.ID_CUENTA_PAG,A.ID_TLECRD),' ')  restriccion,                       
                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',1,-1)),'99999999.99') AS valor,   
                        decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.ID_CUENTA_PAG ,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99'))) IMP,
                        substr(substr(A.NOMBRE,1,18)||' ('||ARON.nombre_banco@DBL_JUL(A.ID_TLECRD,A.ID_CHEQUERA)||' - Op:'||nvl(A.NUM_OPERACION,' ')||' - '||A.FECHA||')',1,50) descripcion,         
                        '2.1' ORDEN,
                        A.id_mov_tlecrd memo ,
                        A.NOMBRE,
                        A.ID_CHEQUERA
                FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND SUBSTR(A.ID_NIVEL,1,1) = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."'
                AND A.ID_NIVEL like '7%'
                AND A.TIPO IN ('RT')     
                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.ID_CUENTA_PAG,A.ID_NIVEL,A.NUM_OPERACION,A.FECHA,A.id_chequera,A.num_operacion,A.VOUCHER,A.NOMBRE
                UNION ALL                
                SELECT  
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_pag,A.ID_TLECRD),' ') AS cuenta,   
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_pag,A.ID_TLECRD),' ') AS cta_cte,
                        '10' fondo,                          
                        DECODE(SUBSTR(A.ID_NIVEL,1,1),1,'11010101',2,'21010101',3,'31010101',4,'41010101',5,'51010101',6,'61010101') AS departamento,   
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_pag,A.ID_TLECRD),' ')  restriccion,                    
                        to_char(SUM(A.IMPORTE *decode(A.DC,'D',-1,1)),'99999999.99') AS valor,        
                        decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_pag,A.ID_TLECRD),' '),'1112025',decode(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),0,'',to_char(sum(A.OTRO_IMP*decode(A.dc,'D',1,-1)),'99999999.99')))  IMP,                      
                        A.GLOSA1,         
                        '2' ORDEN,
                        A.id_mov_tlecrd memo,
                        A.NUM_OPERACION,
                        A.ID_CHEQUERA
                FROM ARON.TLECRD_DETALLE@DBL_JUL A, ARON.TLECRD_VOUCHER@DBL_JUL B 
                WHERE A.ID_TLECRD = B.ID_TLECRD 
                AND A.VOUCHER = B.VOUCHER  
                AND A.ID_NIVEL = B.ID_NIVEL 
                AND A.ID_TLECRD = '".$mai_id."'
                AND A.VOUCHER = '".$voucher."'
                AND A.ID_NIVEL like '7%'
                AND A.TIPO = 'DTL'   
                GROUP BY A.ID_TLECRD,A.id_mov_tlecrd,A.id_cuenta_pag,A.ID_NIVEL,A.NOMBRE,A.NUM_OPERACION,A.FECHA,A.id_chequera,A.num_operacion,A.VOUCHER,A.GLOSA1
                UNION ALL
                SELECT  
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cuenta_chq,a.id_tlecrd),' ') cuenta,  
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(A.id_cuenta_chq,a.id_tlecrd),' ') cta_cte,  
                        '10' fondo,
                        nvl(ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_tlecrd),' ') departamento,  
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(A.id_cuenta_chq,a.ID_TLECRD),' ')  restriccion,
                        to_char(sum(a.importe*decode(b.dc,'D',1,-1)),'99999999.99') valor, 
                        decode(nvl(ARON.fc_cta_aasinet@DBL_JUL(A.id_cuenta_chq,a.id_tlecrd),' '),'1112025',decode(sum(a.OTRO_IMP*decode(b.dc,'D',1,-1)),0,'',to_char(sum(a.OTRO_IMP*decode(b.dc,'D',1,-1)),'99999999.99'))) IMP,                        
                        'Nro. Oper. ' ||A.NUM_OPERACION||'-'||a.fecha,
                        '1' orden,
                        '' memo,
                        A.NUM_OPERACION,
                        A.ID_CHEQUERA
                FROM ARON.tlecrd_detrac@DBL_JUL a, ARON.tlecrd_mov_doc@DBL_JUL b, ARON.tlecrd_voucher@DBL_JUL c  
                WHERE a.id_tlecrd = b.id_tlecrd  
                and a.id_mov_tlecrd = b.id_mov_tlecrd 
                and a.id_tlecrd = c.id_tlecrd 
                and a.id_nivel = c.id_nivel  
                and a.voucher = c.voucher 
                and a.id_tlecrd = '".$mai_id."'
                and a.voucher = '".$voucher."'
                and a.id_nivel like '7%' 
                group by a.id_tlecrd,A.id_cuenta_chq,a.id_nivel,A.ID_CHEQUERA,a.NUM_OPERACION,a.fecha
                ORDER BY ID_CHEQUERA,NUM_OPERACION,valor,ORDEN,CUENTA
                    ) X ";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }

    public static function uploadSeatAaasinetCHQCAT($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    RESTRICCION as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    imp as \"CurrencyAmount\", 
                                    DESCRIPCION as \"Description\", 
                                    MEMO as \"Memo\" 
                                ) 
                        ) as items         
                    FROM (
                        SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ') cta_cte,
                        '10' fondo,  					
                        nvl(decode(substr(a.id_nivel,1,1),
                        '1',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '2',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '3',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '4',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '5',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '6',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-11','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque))),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ')  restriccion, 					    
                        a.id_chequera, 					   
                        to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') valor, 
                                                to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99') imp,
                        substr(a.glosa_exp_ctr,1,30)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY') descripcion,
                        a.id_mov_efec MEMO,
                        '1' ORDEN 
                FROM ARON.cheque_detalle@DBL_JUL a, ARON.cheque_voucher@DBL_JUL b
                WHERE a.id_cheque = b.id_cheque 
                AND a.voucher = b.voucher 
                AND a.id_cheque = '".$mai_id."' 
                AND a.voucher = '".$voucher."' 
                AND a.id_nivel like '7%' 
                AND b.id_nivel = '7'  
                AND a.num_operacion like '%Cheq%' 
                UNION ALL
                SELECT
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_egre,a.id_cheque),' ') cuenta,
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_egre,a.id_cheque),' ') cta_cte, 
                        '10' fondo,
                        nvl(ARON.equiv_nivel@DBL_JUL('7',a.id_cheque),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,a.id_cheque),' ')  restriccion,
                        id_chequera,                  
                        to_char(sum(nvl(importe,0))*decode(dc,'D',-1,1),'999999999.99') importe,
                                                to_char(sum(nvl(otro_imp,0))*decode(dc,'D',-1,1),'999999999.99') imp,
                        'Total '||nvl(num_operacion,' ')||'-'||to_char(b.fecha,'DD/MM/YY') descripcion,
                        '' memo,
                        '1.1' ORDEN
                FROM ARON.cheque_detalle@DBL_JUL a, ARON.cheque_voucher@DBL_JUL b
                WHERE a.id_cheque = b.id_cheque
                AND a.voucher = b.voucher 
                AND substr(a.id_nivel,1,1) = b.id_nivel
                AND a.id_cheque = '".$mai_id."' 
                AND a.voucher = '".$voucher."'
                AND a.num_operacion like '%Cheq%' 
                AND a.id_nivel like '7%' 
                GROUP BY a.id_cheque,a.id_cuenta_egre,a.id_chequera, a.num_operacion,b.fecha,a.dc
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ') cta_cte,  
                        '10' fondo,					
                        nvl(decode(substr(a.id_nivel,1,1),
                        '1',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),  
                        '2',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '3',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '4',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '5',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '6',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque))),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque),' ')  restriccion,
                        a.id_chequera, 	 					    		                        
                        to_char(nvl(a.importe,0)*decode(a.dc,'D',1,-1),'999999999.99') importe,
                                                to_char(nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1),'999999999.99') imp,
                        substr(a.glosa_exp_ctr,1,50) descripcion,
                        a.id_mov_efec MEMO,
                        '2' ORDEN      
                FROM ARON.cheque_detalle@DBL_JUL a, ARON.cheque_voucher@DBL_JUL b, ARON.egresos_chequera@DBL_JUL d 
                where a.id_cheque = b.id_cheque 
                and a.voucher = b.voucher 
                and a.id_cheque = d.id_compras 
                and a.id_chequera = d.id_chequera
                and a.id_cheque = '".$mai_id."' 
                and a.voucher = '".$voucher."'
                and a.id_nivel like '7%' 
                and b.id_nivel = '7' 
                and a.num_operacion like '%Oper%' 
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_egre,id_cheque),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_egre,id_cheque),' ') cta_cte,
                        '10' fondo,
                        nvl(ARON.equiv_nivel@DBL_JUL('7',id_cheque),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,id_cheque),' ')  restriccion,
                        id_chequera, 
                        to_char(sum(nvl(importe,0))*decode(dc,'C',1,-1),'999999999.99') importe,
                                                to_char(sum(nvl(otro_imp,0))*decode(dc,'C',1,-1),'999999999.99') imp,
                        num_operacion as descripcion, 
                        id_mov_efec MEMO,
                        '2.1' ORDEN 
                FROM ARON.cheque_detalle@DBL_JUL 
                WHERE id_cheque = '".$mai_id."' 
                AND voucher = '".$voucher."'
                AND num_operacion like '%Oper%' 
                AND id_nivel like '7%' 
                group by id_cheque,id_mov_efec,id_cuenta_egre,id_chequera, num_operacion,dc
                --ORDER BY ORDEN,MEMO
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_egre,a.id_cheque),' ') cuenta, 
                        nvl(ARON.cta_cte@DBL_JUL(a.id_cuenta_egre,a.id_cheque),' ') cta_cte,  		
                        '10' fondo,			
                        nvl(decode(substr(a.id_nivel,1,1),
                        '1',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '2',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '3',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '4',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '5',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque)),
                        '6',decode(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_cheque)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_cheque),'1132001-2','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_cheque))),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,id_cheque),' ')  restriccion, 
                        a.id_chequera,					                        
                        to_char(nvl(sum(a.importe),0)*decode(a.dc,'D',-1,1),'999999999.99') valor,
                                                to_char(nvl(sum(a.otro_imp),0)*decode(a.dc,'D',-1,1),'999999999.99') imp,
                        substr(ARON.prov_ret_xmov@DBL_JUL(a.id_mov_efec),1,12)||'-'||nvl(a.glosa,' ')||'-'||to_char(a.fecha,'dd/mm/yy') descripcion,
                        id_mov_efec MEMO,
                        '3' ORDEN 
                FROM ARON.cheque_detalle@DBL_JUL a, ARON.cont_plan@DBL_JUL b 
                WHERE a.id_cuenta_egre = b.id_cuenta 
                AND a.id_cheque = '".$mai_id."' 
                AND a.voucher = '".$voucher."'
                AND b.id_cont = '".$mai_id."' 
                AND a.id_nivel like '7%' 
                AND a.tipo = 'RT' 
                GROUP BY id_cheque,id_cuenta_egre,a.id_cuenta_pag, dc,id_mov_efec,glosa,a.fecha,id_chequera,a.id_nivel
                UNION ALL
                SELECT 
                        nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_pag,id_cheque),' ') cuenta, 
                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_pag,id_cheque),' ') cta_cte,
                        '10' fondo,
                        nvl(ARON.equiv_nivel@DBL_JUL('7',id_cheque),' ') departamento,
                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_pag,id_cheque),' ')  restriccion,
                        id_chequera,
                        to_char(sum(nvl(importe,0))*decode(dc,'D',1,-1),'999999999.99') importe, 
                                                to_char(sum(nvl(otro_imp,0))*decode(dc,'D',1,-1),'999999999.99') imp,
                        substr(ARON.prov_ret_xmov@DBL_JUL(id_mov_efec),1,12)||'-'||nvl(glosa,' ')||'-'||to_char(fecha,'dd/mm/yy') historico,
                        id_mov_efec MEMO,
                        '3' ORDEN 
                FROM ARON.cheque_detalle@DBL_JUL 
                WHERE id_cheque = '".$mai_id."' 
                AND id_nivel like '7%' 
                AND tipo = 'RT' 
                AND voucher = '".$voucher."'
                GROUP BY id_cheque,id_cuenta_pag, dc,id_mov_efec,glosa,fecha,id_chequera
                ORDER BY ORDEN,cuenta,MEMO,valor 
                    ) X ";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }

    public static function uploadSeatAaasinetIncomeCAT($id_entidad,$id_nivel,$id_anho,$voucher,$numero,$fecha,$codigo,$fecha_aasi,$periodo,$url_aasinet,$descripcion, $certificado=""){
        $mai_id = "001-".$id_anho;
        $query = "SELECT                                
                        '".$url_aasinet."' as URL,
                        xmlelement(name \"Context\", xmlelement(name \"AccountingEntity\",".$id_entidad."),xmlelement(name \"Certificate\",'".$certificado."')) context,  
                        xmlelement(name \"Component\",xmlelement(name \"Name\",'ExternalMultipleAccounting')) component,
                        xmlelement(name \"Parameters\",xmlelement(name \"ExternalMultipleAccountingParams\",xmlelement(name \"ExternalSystem\",
                        '".$codigo."'))) Parameters,
                        xmlelement(name \"ItemId\",'".$numero."')||
                        xmlelement(name \"PostedPeriod\",'".$periodo."')||
                        xmlelement(name \"JournalDate\",'".$fecha_aasi."')||
                        xmlelement(name \"Description\",'".$descripcion."'||'-'||'".$numero."'||'-'||'".$fecha."') Description,
                        xmlelement(name \"Item\", 
                                xmlforest( 
                                    rownum as \"ItemId\", 
                                    cuenta as \"AccountCode\", 
                                    cta_cte as \"SubAccountCode\", 
                                    FONDO as \"FundCode\", 
                                    departamento as \"FunctionCode\", 
                                    RESTRICCION as \"RestrictionCode\", 
                                    valor as \"EntityValue\", 
                                    DESCRIPCION as \"Description\", 
                                    MEMO as \"Memo\" 
                                ) 
                        ) as items         
                    FROM ( 

                        SELECT         
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ') cuenta,          
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ') cta_cte,
                                '10' fondo,                             
                                nvl(decode(substr(a.id_nivel,1,1),'1',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'2',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'3',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'4',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL('4',a.id_compras)),'5',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'6',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-11','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras))),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ')  restriccion,     
                                nvl(a.importe,0)*decode(a.dc,'D',1,-1) valor, 
                                                        nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1) imp,
                                NVL((select n.serie||'-'||n.NUMDOC from ARON.IMP_REGVENTAS@DBL_JUL n join ARON.IMP_INGVNT@DBL_JUL b
                                on n.ID_MOV_VNT=b.ID_MOV_VNT where n.ID_VENTA=b.ID_VENTA and n.ID_VENTA=a.ID_COMPRAS  
                                and b.ID_MOV_ING=a.ID_MOV_EFEC ),(select n.serie||'-'||n.NUMDOC from ARON.valm_REGVENTAS@DBL_JUL n join ARON.IMP_INGVNT@DBL_JUL b
                                on n.ID_MOV_VNT=b.ID_MOV_VNT where n.ID_VENTA=b.ID_VENTA and n.ID_VENTA=a.ID_COMPRAS  
                                and b.ID_MOV_ING=a.ID_MOV_EFEC ))||' '||decode(a.tipo,'IC',decode(a.tipo2,'XD',substr(a.glosa,1,50),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')) descripcion,
                                a.id_mov_efec memo,
                                '1.1' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  a, ARON.cont_plan@DBL_JUL  b
                        where a.id_compras = b.id_cont 
                        and a.id_cuenta_egre = b.id_cuenta 
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '7%' 
                        and a.tipo in ('IC','ID')
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ') cuenta,
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ') cta_cte, 
                                '10' fondo,             
                                nvl(ARON.equiv_nivel@DBL_JUL('7',id_compras),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ')  restriccion,
                                nvl(sum(importe),0)*decode(dc,'C',1,-1) valor, 
                                                        nvl(sum(otro_imp),0)*decode(dc,'C',1,-1) imp,
                                'Total Caja Operativa' descripcion,
                                '' memo,
                                '1' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  
                        WHERE id_compras = '".$mai_id."'
                        AND id_nivel like '7%'
                        AND tipo in ('IC','ID') 
                        AND voucher = '".$voucher."'
                        GROUP BY id_compras,id_cuenta_egre, dc
                        UNION ALL
                        --QUERY 2
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' ') cuenta,  
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_pag,'PE',a.id_cuenta_pag,'DE',a.id_cuenta_pag,a.id_cuenta_egre),a.id_compras),' ') CTA_CTE, 
                                '10' fondo,     
                                nvl(ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras),' ') departamento,     
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ')  restriccion,   
                                nvl(a.importe,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1) valor,  
                                                        nvl(a.otro_imp,0)*decode(decode(a.dc,'C','D',a.dc),'D',1,-1) imp,
                                decode(a.tipo,'ED',substr(a.glosa,1,50),substr(a.glosa,1,23)||'-'||nvl(a.num_operacion,' ')||'-'||to_char(a.fecha,'DD/MM/YY')) descripcion,
                                a.id_mov_efec memo,
                                '2' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  a, ARON.cont_plan@DBL_JUL  b 
                        where decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag) = b.id_cuenta 
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '7%'  
                        and not a.tipo in ('IC','ID','DC','PC','RT','DCH') 
                        --order by cuenta_e, fecha_ord, a.num_operacion, cuenta_p 
                        UNION ALL
                        SELECT 
                                X.CUENTA,
                                X.CTA_CTE,
                                '10' FONDO,
                                X.departamento,
                                X.RESTRICCION,
                                SUM(X.VALOR) VALOR,
                                                        SUM(X.IMP) IMP,
                                'Total Egresos y Depositos al Banco' descripcion,
                                '' MEMO,
                                '2.1' ORDEN
                        FROM (
                                SELECT 
                                        nvl(ARON.fc_cta_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ') cuenta,  
                                        nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ') CTA_CTE,        
                                        nvl(ARON.equiv_nivel@DBL_JUL('7',a.id_compras),' ') departamento,     
                                        nvl(ARON.fc_restriccion_aasinet@DBL_JUL(decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag),a.id_compras),' ')  restriccion,   
                                        nvl(sum(a.importe),0)*decode(decode(a.dc,'C','C',a.dc),'D',1,-1) valor,
                                        nvl(sum(a.otro_imp),0)*decode(decode(a.dc,'C','C',a.dc),'D',1,-1) imp
                                FROM ARON.egresos_detalle@DBL_JUL  a, ARON.cont_plan@DBL_JUL  b 
                                where decode(a.tipo,'ED',a.id_cuenta_egre,'PE',a.id_cuenta_egre,'DE',a.id_cuenta_egre,a.id_cuenta_pag) = b.id_cuenta 
                                and a.id_compras = '".$mai_id."'
                                and a.voucher = '".$voucher."' 
                                and b.id_cont = '".$mai_id."'
                                and a.id_nivel like '7%' 
                                and not a.tipo in ('IC','ID','DC','PC','RT','DCH') 
                                GROUP BY a.id_compras,a.tipo,a.id_cuenta_egre,a.id_cuenta_pag,a.dc
                        ) X
                        GROUP BY X.CUENTA,X.CTA_CTE,X.departamento,X.RESTRICCION
                        --QUERY 5
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ') cta_cte, 
                                '10' fondo,                                     
                                nvl(decode(substr(a.id_nivel,1,1),'1',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','11010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'2',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','21010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'3',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','31010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'4',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','41010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'5',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','51010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras)),'6',decode(ARON.equiv@DBL_JUL(a.id_cuenta_pag,a.id_compras)||'-'||ARON.cta_cte@DBL_JUL(a.id_cuenta_pag,a.id_compras),'1132001-2','61010101',ARON.equiv_nivel@DBL_JUL(a.id_nivel,a.id_compras))),' ') departamento,                                                                         
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(a.id_cuenta_pag,a.id_compras),' ')  restriccion, 
                                nvl(a.importe,0)*decode(a.dc,'D',1,-1) valor,  
                                                        nvl(a.otro_imp,0)*decode(a.dc,'D',1,-1) imp,
                                substr(ARON.prov_ret_xmov@DBL_JUL(a.id_mov_efec),1,12)||'-'||nvl(a.glosa,' ')||'-'||to_char(a.fecha,'dd/mm/yy') historico,
                                a.id_mov_efec memo,
                                '5' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  a, ARON.cont_plan@DBL_JUL  b
                        where a.id_cuenta_egre = b.id_cuenta  
                        and a.id_compras = '".$mai_id."'
                        and a.voucher = '".$voucher."' 
                        and b.id_cont = '".$mai_id."'
                        and a.id_nivel like '7%' 
                        and a.tipo = 'RT' 
                        UNION ALL
                        SELECT 
                                nvl(ARON.fc_cta_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ') cuenta, 
                                nvl(ARON.fc_cta_cte_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ') cta_cte, 
                                '10' fondo, 
                                nvl(ARON.equiv_nivel@DBL_JUL('7',id_compras),' ') departamento,
                                nvl(ARON.fc_restriccion_aasinet@DBL_JUL(id_cuenta_egre,id_compras),' ')  restriccion,  
                                sum(nvl(importe,0))*decode(dc,'C',1,-1) importe, 
                                                        sum(nvl(otro_imp,0))*decode(dc,'C',1,-1) imp,
                                'Total Rentenciones' descripcion,
                                '' memo,
                                '5.1' ORDEN
                        FROM ARON.egresos_detalle@DBL_JUL  
                        where id_compras = '".$mai_id."'
                        and id_nivel like '7%' 
                        and tipo = 'RT' 
                        and voucher = '".$voucher."' 
                        GROUP BY id_compras,id_cuenta_egre, dc 
                        ORDER BY ORDEN,CUENTA,CTA_CTE

                    ) X ";
        $oQuery = DB::connection('oracle')->select($query);
        return $oQuery;
    }
            /**========================================================== */

}