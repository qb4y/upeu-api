<?php
namespace App\Http\Data\cw;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class CWData extends Controller {
    protected $connection = 'oracleapp';
    private $request;
    public function __construct(Request $request) {
        $this->request = $request;
    }
    public static function anho_cw() {
        $query = "SELECT ID_VENTA 
                    FROM UPEU_MAIN
                    WHERE ESTADO = '1' ";
        $id_venta = DB::connection('oracleapp')->select($query);
        return $id_venta;
    }
    public static function estadoCuenta($id_venta,$per_id) {
        $sql = "SELECT
                NUMDOC,TO_CHAR(FECHA,'DD/MM/YYYY') FECHA,CONVERT(GLOSA,'US7ASCII','WE8ISO8859P1') GLOSA,
                IMPORTE,DECODE(DC,'D',1,'C',-1) SIG
                FROM ARON.UPEU_MOVI
                WHERE ID_VENTA = '".$id_venta."'
                AND ID_PERSONAL = '".$per_id."'
                AND IMPORTE <> 0
                ORDER BY FECHA ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function estadoCuentaTPP($id_venta,$per_id) {
        $sql = "SELECT
                NUMDOC,TO_CHAR(FECHA,'DD/MM/YYYY') FECHA,CONVERT(GLOSA,'US7ASCII','WE8ISO8859P1') GLOSA,
                IMPORTE,DECODE(DC,'D',1,'C',-1) SIG
                FROM ARON.TARA_MOV_DOC
                WHERE ID_VENTA = '".$id_venta."'
                AND ID_PERSONAL = '".$per_id."'
                AND IMPORTE <> 0
                ORDER BY FECHA ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function estadoCuentaJU($id_venta,$per_id) {
        $sql = "SELECT
                NUMDOC,TO_CHAR(FECHA,'DD/MM/YYYY') FECHA,CONVERT(GLOSA,'US7ASCII','WE8ISO8859P1') GLOSA,
                IMPORTE,DECODE(DC,'D',1,'C',-1) SIG
                FROM ARON.CHULLU_MOV_DOC@DBLCONTABLE_JULIACA
                WHERE ID_VENTA = '".$id_venta."'
                AND ID_PERSONAL = '".$per_id."'
                AND IMPORTE <> 0
                AND TO_CHAR(fecha, 'MM') <= '12'
                AND ( tipo_mov NOT IN (
                '10',
                '11'
                )
                OR tipo_mov IS NULL )
                ORDER BY FECHA ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function saldoAlumno($id_venta,$per_id) {
        $sql = "SELECT
                    SUM(DECODE(DC,'D',IMPORTE,0))  DEBITO,SUM(DECODE(DC,'C',IMPORTE,0)) CREDITO,
                    ABS(UPEU_SALDO(ID_PERSONAL,ID_VENTA)) SALDO,SIGN(UPEU_SALDO(ID_PERSONAL,ID_VENTA)) SIGNO
                FROM ARON.UPEU_MOVI
                WHERE ID_VENTA = '".$id_venta."'
                AND ID_PERSONAL = '".$per_id."'
                AND IMPORTE <> 0
                GROUP BY ID_VENTA,ID_PERSONAL ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }

    public static function saldoAlumnoTPP($id_venta,$per_id) {
        $sql = "SELECT
                    SUM(DECODE(DC,'D',IMPORTE,0))  DEBITO,SUM(DECODE(DC,'C',IMPORTE,0)) CREDITO,
                    ABS(tara_saldo_new(ID_VENTA,ID_PERSONAL)) SALDO,SIGN(tara_saldo_new(ID_VENTA,ID_PERSONAL)) SIGNO
                FROM ARON.TARA_MOV_DOC
                WHERE ID_VENTA = '".$id_venta."'
                AND ID_PERSONAL = '".$per_id."'
                AND IMPORTE <> 0
                GROUP BY ID_VENTA,ID_PERSONAL ";
      $oQuery = DB::connection('oracleapp')->select($sql);      
      return $oQuery;
    }
    public static function saldoAlumnoJU($id_venta,$per_id) {
        $sql = "SELECT
                    SUM(DECODE(dc, 'D', importe, 0)) debito,
                    SUM(DECODE(dc, 'C', importe, 0)) credito,
                    TO_CHAR(abs(nvl(SUM(importe), 0)), '99999,999.99') saldo,
                    sign(nvl(SUM(importe), 0)) signo
                FROM ARON.CHULLU_MOV_DOC@DBLCONTABLE_JULIACA
                WHERE id_venta = '".$id_venta."'
                AND id_area = '14'
                AND TO_CHAR(fecha, 'MM') <= '12'
                AND ( tipo_mov NOT IN (
                '10',
                '11'
                )
                OR tipo_mov IS NULL )
                AND id_personal = '".$per_id."'  ";
      $oQuery = DB::connection('oracleapp')->select($sql);      
      return $oQuery;
    }
    public static function prorrogaAlumno($id_venta,$per_id) {
        $sql = "SELECT 
                        importe,'Deuda' DETALLE, 'S' PRORROGA
                FROM ( 
                    SELECT 
                            id_personal, 
                            sum(importe) importe 
                    FROM mat_lock 
                    WHERE id_venta = '".$id_venta."'
                    AND id_personal = '".$per_id."' 
                    GROUP BY id_personal 
                    UNION 
                    SELECT 
                            id_personal, 
                            sum(importe) importe 
                    FROM mat_lock_filial 
                    WHERE id_venta = '".$id_venta."'
                    AND id_personal = '".$per_id."' 
                    GROUP BY id_personal 
                    UNION 
                    SELECT 
                            id_personal, 
                            sum(importe) importe 
                    FROM mat_lock_tara 
                    WHERE id_venta = '".$id_venta."'
                    AND id_personal = '".$per_id."' 
                    GROUP BY id_personal 
                ) 
                WHERE importe > 0 ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function planAlumno($per_id) {
        $sql = "SELECT 
                        plan_id, 
                        estado   
                FROM NOE.ALUM_PLAN   
                WHERE codigo_personal = '".$per_id."' 
                --AND substr(plan_id,0,1) not  in ('1','5','2') 
                AND estado = '1' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function planAcademico($per_id,$id_plan,$ciclo) {
        $sql = "SELECT                        
                        conv_char(replace(CONVERT(trim(A.nombre),'US7ASCII','WE8ISO8859P1'),'?','n')) nombre,
                        A.Creditos, 
                        nvl(a.HT,'0') HT, nvl(a.HP,'0') HP, nvl(a.HNP,'0') HNP, 
                        nvl(a.hnp,0) + nvl(a.hp,0) as hp_np, 
                        (nvl(A.HT,0)+nvl(A.HP,0)+nvl(A.HNP,0)) total_horas, 
                        to_char(nvl(B.Nota,0),'90') nota
                FROM noe.acad_plan_academico A, 
                    ( 
                        SELECT 
                            curso_id,area_id, 
                            curso_carga_id, 
                            modo, 
                            nota, 
                            tipo_curso,tipo  
                        FROM noe.alum_curso_disc 
                        WHERE codigo_personal = '".$per_id."' 
                        AND condicion = '1' 
                    )B 
                WHERE A.curso_id = B.curso_id (+)
                AND A.plan_id = '".$id_plan."'  
                AND A.CICLO = ".$ciclo."
                ORDER BY A.ciclo ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function ciclosAlumno($per_id,$id_plan) {
        $sql = "SELECT 
                        A.Ciclo
                FROM noe.acad_plan_academico A, 
                    ( 
                        SELECT 
                            curso_id,area_id, 
                            curso_carga_id, 
                            modo, 
                            nota, 
                            tipo_curso,tipo  
                        FROM noe.alum_curso_disc 
                        WHERE codigo_personal = '".$per_id."' 
                        AND condicion = '1' 
                    )B 
                WHERE A.curso_id = B.curso_id (+)
                AND A.plan_id = '".$id_plan."'   
                GROUP BY A.Ciclo
                ORDER BY A.Ciclo ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function cargaAlumno($per_id) {
        $sql = "SELECT 
                        carga_id 
                FROM alum_curso 
                WHERE codigo_personal = '".$per_id."'   
                AND carga_id not in( '0000-0')
                GROUP BY carga_id 
                ORDER BY carga_id DESC ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function cargaAlumnoMax($per_id) {
        $sql = "SELECT 
                        max(carga_id) as utlimo
                FROM alum_curso 
                WHERE codigo_personal = '".$per_id."'   
                AND carga_id not in( '0000-0') ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function userAcad($codigo,$user) {
        $sql = "SELECT 
                        CODIGO_PERSONAL PER_ID, 
                        CONVERT(DATO_NOMBRES||' '||DATO_APELLIDO_PATERNO||', '||DATO_APELLIDO_MATERNO,'US7ASCII','WE8ISO8859P1') NOMBRES,
                        DOCUMENTOS_CODUNIV CODIGO,
                        LOGIN,
                        'http://images.upeu.edu.pe/fotodb/'||decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem'))||'.jpg' url_foto,
                        decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem')) as foto
                FROM NOE.DATOS_PERSONALES
                WHERE LOGIN = '".$user."' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;

    }
    public static function datosAlumno($per_id) {
        $sql = "SELECT 
                        CODIGO_PERSONAL PER_ID, 
                        conv_char(replace(CONVERT(trim(DATO_NOMBRES||' '||DATO_APELLIDO_PATERNO||', '||DATO_APELLIDO_MATERNO),'US7ASCII','WE8ISO8859P1'),'?','n')) NOMBRES,
                        DOCUMENTOS_CODUNIV CODIGO,
                        LOGIN,
                        decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem')) as foto
                FROM NOE.DATOS_PERSONALES
                WHERE CODIGO_PERSONAL = '".$per_id."'  ";

      $oQuery = DB::connection('oracleapp')->select($sql);      
      return $oQuery;
    }
    
    public static function cargaLima() {
        $sql = "SELECT CARGA_ID FROM NOE.CONFIGURACION_SIST  ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function cargaEPG() {
        $sql = "SELECT CARGA_ID FROM NOE.CONFIGURACION_SIST_UPG  ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function cargaFilial() {
        $sql = "SELECT CARGA_ID FROM NOE.CONFIGURACION_SIST_FILIAL  ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosMensaje($per_id,$id_venta,$ciclo,$eap) {
        $sql = "SELECT          
                        DECODE(CUO_NRO_CUOTA,2,'2da Armada',3,'3ra Armada',4,'4ta Armada',5,'5ta Armada','Al Contado')||': '||TO_CHAR(CUO_FECHA,'DD.MM.YYYY') AS FECHA, 
                        CUO_SISTEMA_CUOTA,
                        CUO_FECHA,
                        --to_char(trunc(to_date(CUO_FECHA,'DD/MM/YYYY hh24:mi:ss'))-trunc(to_date(SYSDATE,'DD/MM/YYYY hh24:mi:ss'))) DIAS,
                        --SIGN(trunc(to_date(CUO_FECHA,'DD/MM/YYYY hh24:mi:ss'))-trunc(to_date(SYSDATE,'DD/MM/YYYY hh24:mi:ss'))) S_VEN,
                        DECODE(SIGN(UPEU_SALDO('".$per_id."','".$id_venta."')),1,'Dias vencidos ','') msn,
                        DECODE(SIGN(UPEU_SALDO('".$per_id."','".$id_venta."')),1,'Por favor Regularizar sus Compromisos Financieros','Gracias Por estar al dia con sus pagos') mensaje 
                FROM UPEU_CUOTAS 
                WHERE CUO_CONTRATO = '".$ciclo."' 
                AND SUBSTR(CUO_EAP,1,5) = '".$eap."'
                AND CUO_MOSTRAR = '1' 
                AND CUO_ESTADO = '1' 
                AND CUO_NRO_CUOTA = '4'
                GROUP BY CUO_NRO_CUOTA,CUO_FECHA, CUO_SISTEMA_CUOTA 
                ORDER BY CUO_NRO_CUOTA  ";
        dd($sql);
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function dataMensaje($tipo) {
        $sql = "SELECT ID_MENSAJE,MENSAJE, SYSDATE AS FECHA 
            FROM ARON.UPEU_MENSAJE
            WHERE TIPO = '".$tipo."'
            AND ESTADO = '1' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    
    public static function dataMensajeReg($per_id,$id_mensaje) {
        $sql = "INSERT INTO ARON.UPEU_MENSAJE_ALUMNO (ID_PERSONAL,ID_MENSAJE,FECHA) VALUES ('$per_id',$id_mensaje,sysdate) ";    
        DB::connection('oracleapp')->insert($sql);
    }
    public static function dataMensajeValida($per_id) {
        $sql = "SELECT ID_PERSONAL FROM ARON.UPEU_MENSAJE_ALUMNO
                WHERE ID_PERSONAL = '".$per_id."'
                AND TO_CHAR(FECHA,'DD/MM/YYYY') = TO_CHAR(SYSDATE,'DD/MM/YYYY') ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosAlumnoVisa($per_id,$id_venta) {
        $sql = "SELECT 
                        CODIGO_PERSONAL PER_ID, 
                        conv_char(replace(CONVERT(trim(DATO_NOMBRES),'US7ASCII','WE8ISO8859P1'),'?','n')) NOMBRES,
                        conv_char(replace(CONVERT(trim(DATO_APELLIDO_PATERNO),'US7ASCII','WE8ISO8859P1'),'?','n')) PATERNO,
                        conv_char(replace(CONVERT(trim(DATO_APELLIDO_MATERNO),'US7ASCII','WE8ISO8859P1'),'?','n')) MATERNO,
                        DOCUMENTOS_CODUNIV CODIGO,
                        DOCUMENTOS_DNI DNI,
                       --'https://webapp.upeu.edu.pe/fotodb/'||decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem'))||'.jpg' url_foto,
                        'http://images.upeu.edu.pe/fotodb/'||decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem'))||'.jpg' url_foto,
                        conv_char(replace(CONVERT(trim(SUBSTR(ALUMNO_EAP(codigo_personal),7)),'US7ASCII','WE8ISO8859P1'),'?','n')) ep, 
                        decode(nvl((select decode(substr(plan_id,1,5),'30109','5','30114','5','30129','5',substr(plan_id,1,1)) from NOE.ALUM_PLAN where NOE.ALUM_PLAN.codigo_personal = datos_personales.codigo_personal and estado = '1'),'1'),        
                        5,to_char(nvl(aron.tara_saldo(codigo_personal),0),'999,999,999.99'),
                        to_char(nvl(aron.upeu_saldo(codigo_personal,'".$id_venta."'),0),'999,999,999.99')
                        ) saldo,                       
                        nvl((select decode(substr(plan_id,1,5),'30109','5','30114','5','30129','5',substr(plan_id,1,1)) from NOE.ALUM_PLAN where NOE.ALUM_PLAN.codigo_personal = datos_personales.codigo_personal and estado = '1'),'1') plam,
                        decode(nvl((select decode(substr(plan_id,1,5),'30109','5','30114','5','30129','5',substr(plan_id,1,1)) from NOE.ALUM_PLAN where NOE.ALUM_PLAN.codigo_personal = datos_personales.codigo_personal and estado = '1'),'1'),'1','Lima','5','Tarapoto','Juliaca') sede,
                        decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem')) as foto
                FROM NOE.DATOS_PERSONALES
                WHERE CODIGO_PERSONAL = '".$per_id."'  ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function dataAlumnoVisa($per_id) {
        $sql = "SELECT 
                        id_personal,
                        plan,
                        decode(plan,'5','001','2','001','007')  as id_operacion,
                        decode(plan,'5','3','2','2','1') as id_negocio,
                        decode(plan,'5','3','2','2','1') as id_aplicacion
                FROM (
                        SELECT
                                codigo_personal as id_personal,
                                nvl((select decode(substr(plan_id,1,5),'30109','5','30114','5','30129','5','30108','2','30115','2','30135','2','30107','2',substr(plan_id,1,1)) 
                        FROM NOE.ALUM_PLAN where NOE.ALUM_PLAN.codigo_personal = datos_personales.codigo_personal and estado = '1'),'1') plan
                        FROM datos_personales
                        WHERE codigo_personal = '".$per_id."'
                )  ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function acceso($usuario, $clave) {
        $sql = "SELECT users.username as usuario, users.nombres as nombres, users.apepat as apellido_paterno, users.apemat as apellido_materno, 
                users.codalu as codigo 
                FROM users 
                WHERE sha1(users.username) = sha1('$usuario')
                AND users.passwd = sha1('$clave') ";
        $oQuery = DB::connection('mysql')->select($sql);
        return $oQuery;
    }
    public static function cursosAlumno($per_id,$carga_id) {
        $sql = "SELECT  a.curso_carga_id,
                        apellido (a.codigo_personal) nombre,
                        replace(CONVERT(trim(NVL (apellido (a.codigo_profesor), '- - -')),'US7ASCII','WE8ISO8859P1'),'?','n') profesor,
                        a.codigo_profesor,
                        a.ciclo,
                        TRIM (TO_CHAR (NVL (a.nota, 0), '00')) nota,
                        nombre_sector (a.sector_id, '2') sector,
                        replace(CONVERT(trim(APELLIDO2(a.nombre_curso)),'US7ASCII','WE8ISO8859P1'),'?','n') nombre_curso,
                        NVL (a.grupo, ' ') grupo,
                        a.modo modo_n,
                        case when LENGTH(coalesce(horario_t,' '))<>112 then lpad('0',112,'0')  else a.horario_t end as horario_t
                FROM noe.alum_curso_disc a
                WHERE     a.codigo_personal = '".$per_id."'
                AND a.carga_id = '".$carga_id."'
                AND NOT a.area_id = '6'
                AND a.estado IN ('1', '2', 'I')
                --AND a.area_id <> '3'
                ORDER BY a.area_id, a.tipo DESC, a.nombre_curso ";

          

        /*SELECT
                a.curso_carga_id,
                apellido(a.codigo_personal) nombre,
                nvl(apellido(a.codigo_profesor),' ') profesor,
                 ( select nvl(apellido(x.docente_adjunto),'X') from acad_carga_cursos x
                 where x.curso_carga_id=a.curso_carga_id ) adjunto,
                a.ciclo,
                a.salon_t,
                a.salon_p,
                trim(to_char(nvl(a.nota,0),'00')) nota,
                nombre_sector(a.sector_id,'2') sector,
                conv_char(replace(CONVERT(trim(a.nombre_curso),'US7ASCII','WE8ISO8859P1'),'?','n')) nombre_curso,
                nvl(a.grupo,' ') grupo,
                nvl(a.tipo_prac,'X') prac,
                a.condicion ,nvl(a.tutoria,'0') tutoria ,a.unidad_id, a.sector_id,a.modo modo_n
        FROM NOE.alum_curso_disc a
        WHERE a.codigo_personal = '".$per_id."'
        AND a.carga_id =  '".$carga_id."' and not a.area_id='6'
        AND a.estado in ('1','2','I')
        AND a.area_id<>'3'
        ORDER BY a.area_id,a.tipo desc,a.nombre_curso*/
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function cursosNotasAlumno($per_id,$curso_id) {
        $sql = "Select 
                        A.evaluacion_id, 
                        to_char(A.fecha, 'DD/MM/YYYY') fecha,                           
                        conv_char(replace(CONVERT(trim(decode(nvl(C.nombre,'X'),'X', A.descripcion, C.nombre )),'US7ASCII','WE8ISO8859P1'),'?','n')) estrategia,
                        nvl(A.descripcion,' ') descripcion, 
                        to_char(A.ponderado,'99.99')||'%' ponderado, 
                        to_char(nvl(B.nota,0),'90.99') nota 
                from NOE.acad_carga_evaluacion A, NOE.academico_estrategias C, 
                ( select 
                  * 
                  from NOE.alum_evaluacion 
                  where curso_carga_id = '".$curso_id."' 
                  and codigo_personal = '".$per_id."' ) B 
                where A.evaluacion_id = B.evaluacion_id (+) 
                and A.estrategia = C.codigo_estrategia 
                and A.curso_carga_id = '".$curso_id."' 
                order by A.fecha  ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function promedioCursoAlumno($per_id,$curso_id) {
        $sql = "Select 
                        to_char(nvl(nota,0),'90.99') nota 
                from NOE.alum_curso_disc 
                where curso_carga_id = '".$curso_id."' 
                and codigo_personal = '".$per_id."' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }

    public static function datosContrato($per_id,$ciclo) {
        $sql = "SELECT 
                        A.dato_nombres||' '||A.dato_apellido_paterno NOMBRE,
                        A.documentos_coduniv,
                        decode(A.dato_sexo,'M',nvl(A.foto,'sinfoto_man'),nvl(A.foto,'sinfoto_fem')) FOTO,
                        'https://webapp.upeu.edu.pe/fotodb/'||decode(A.dato_sexo,'M',nvl(A.foto,'sinfoto_man'),nvl(A.foto,'sinfoto_fem'))||'.jpg' url_foto,
                        conv_char(replace(CONVERT(trim(nombre_sector(decode(substr(B.codigo_contrato,6,1),'A','8','B','8','1')||B.codigo_eap,'1')),'US7ASCII','WE8ISO8859P1'),'?','n')) NOMBRE_EAP,
                        nvl(B.res_fin_trabajo,' ') RES_FIN_TRABAJO,
                        nvl(B.res_fin_telef,' ') RES_FIN_TELEF,
                        B.horas_academicas CREDITOS,
                        B.documento_fiscal CONTRATO,
                        B.FECHA,
                        decode(decode(vivienda_tipo,'E',vivienda_tipo,substr(vivienda_tipo,1,2)),'I1','Interno Pabellon 1','I2','Interno Pabellon 2','I3','Interno Pabellon 3','I4','Interno Pabellon 4','I5','Interno Pabellon 5','Externo') INTERNO,
                        DECODE (B.tipo_pago,  '5', '1',  '1', '2',  '2', '3',  '3', '4',  '5') tipo_pago,
                        B.codigo_eap
                FROM DATOS_PERSONALES A, ALUMNO_CONTRATO B
                WHERE A.CODIGO_PERSONAL = B.CODIGO_PERSONAL
                AND A.CODIGO_PERSONAL = '".$per_id."'
                AND B.CODIGO_CONTRATO = '".$ciclo."' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosContratoEPG($per_id,$ciclo) {
        $sql = "SELECT 
                        A.dato_nombres||' '||A.dato_apellido_paterno NOMBRE,
                        A.documentos_coduniv,
                        decode(A.dato_sexo,'M',nvl(A.foto,'sinfoto_man'),nvl(A.foto,'sinfoto_fem')) FOTO,
                        'https://webapp.upeu.edu.pe/fotodb/'||decode(A.dato_sexo,'M',nvl(A.foto,'sinfoto_man'),nvl(A.foto,'sinfoto_fem'))||'.jpg' url_foto,
                        conv_char(replace(CONVERT(trim(nombre_sector(decode(substr(B.codigo_contrato,6,1),'A','8','B','8','1')||B.codigo_eap,'1')),'US7ASCII','WE8ISO8859P1'),'?','n')) NOMBRE_EAP,
                        nvl(B.res_fin_trabajo,' ') RES_FIN_TRABAJO,
                        nvl(B.res_fin_telef,' ') RES_FIN_TELEF,
                        B.horas_academicas CREDITOS,
                        B.documento_fiscal CONTRATO,
                        B.FECHA,
                        decode(decode(vivienda_tipo,'E',vivienda_tipo,substr(vivienda_tipo,1,2)),'I1','Interno Pabellon 1','I2','Interno Pabellon 2','I3','Interno Pabellon 3','I4','Interno Pabellon 4','I5','Interno Pabellon 5','Externo') INTERNO,
                        DECODE (B.tipo_pago,  '5', '1',  '1', '2',  '2', '3',  '3', '4',  '5') tipo_pago,
                        B.codigo_eap
                FROM DATOS_PERSONALES A, ALUMNO_CONTRATO_UPG B
                WHERE A.CODIGO_PERSONAL = B.CODIGO_PERSONAL
                AND A.CODIGO_PERSONAL = '".$per_id."'
                AND B.CODIGO_CONTRATO = '".$ciclo."' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosContratoTPP($per_id,$ciclo) {
        $sql = "SELECT 
                        A.dato_nombres||' '||A.dato_apellido_paterno NOMBRE,
                        A.documentos_coduniv,
                        decode(A.dato_sexo,'M',nvl(A.foto,'sinfoto_man'),nvl(A.foto,'sinfoto_fem')) FOTO,
                        'https://webapp.upeu.edu.pe/fotodb/'||decode(A.dato_sexo,'M',nvl(A.foto,'sinfoto_man'),nvl(A.foto,'sinfoto_fem'))||'.jpg' url_foto,
                        conv_char(replace(CONVERT(trim(nombre_sector(decode(substr(B.codigo_contrato,6,1),'A','8','B','8','1')||B.codigo_eap,'1')),'US7ASCII','WE8ISO8859P1'),'?','n')) NOMBRE_EAP,
                        nvl(B.res_fin_trabajo,' ') RES_FIN_TRABAJO,
                        nvl(B.res_fin_telef,' ') RES_FIN_TELEF,
                        B.horas_academicas CREDITOS,
                        B.documento_fiscal CONTRATO,
                        B.FECHA,
                        decode(decode(vivienda_tipo,'E',vivienda_tipo,substr(vivienda_tipo,1,2)),'I1','Interno Pabellon 1','I2','Interno Pabellon 2','I3','Interno Pabellon 3','I4','Interno Pabellon 4','I5','Interno Pabellon 5','Externo') INTERNO,
                        DECODE (B.tipo_pago,  '5', '1',  '1', '2',  '2', '3',  '3', '4',  '5') tipo_pago,
                        B.codigo_eap
                FROM DATOS_PERSONALES A, ALUMNO_CONTRATO_FILIAL B
                WHERE A.CODIGO_PERSONAL = B.CODIGO_PERSONAL
                AND A.CODIGO_PERSONAL = '".$per_id."'
                AND B.CODIGO_CONTRATO = '".$ciclo."' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosCobros($per_id,$ciclo) {
        $sql = "SELECT 
                        decode(B.tipo_cobro,'M','1.Matrícula','E','2.Ensenanza','I','3.Internado','P','4.Mora','Otros cobros') nombre, 
                        to_char(sum(A.importe),'99,999.99') importe 
                FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND A.importe > 0 
                AND not B.tipo_Cobro = 'X'  
                AND B.nombre NOT LIKE '%Aplazados%'
                GROUP BY decode(B.tipo_cobro,'M','1.Matrícula','E','2.Ensenanza','I','3.Internado','P','4.Mora','Otros cobros')
                UNION ALL
                SELECT 
                        decode(B.tipo_cobro,'M','1.Matrícula','E','3.Ensenanza Cursos Aplazados','I','3.Internado','P','4.Mora','Otros cobros') nombre, 
                        to_char(sum(A.importe),'99,999.99') importe 
                FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND A.importe > 0 
                AND not B.tipo_Cobro = 'X'  
                AND B.nombre LIKE '%Aplazados%' 
                GROUP BY decode(B.tipo_cobro,'M','1.Matrícula','E','3.Ensenanza Cursos Aplazados','I','3.Internado','P','4.Mora','Otros cobros')
                UNION ALL
                SELECT 'Total Cobros' nombre,
                        to_char(nvl(sum(A.importe),0),'99,999.99') importe 
                FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 			
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND A.importe>0 
                AND NOT B.tipo_Cobro = 'X'
                ORDER BY nombre ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosCobrosEPG($per_id,$ciclo) {
        $sql = "SELECT 
                        B.NOMBRE, 
                        to_char(sum(A.importe),'99,999.99') importe 
                FROM NOE.alumno_contrato_upg_detalle A, NOE.contrato_criterio_pg B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND A.importe > 0 
                AND not B.tipo_Cobro = 'X' 
                GROUP BY B.NOMBRE,B.ORDEN 
                ORDER BY B.ORDEN ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosCobrosProesad($per_id,$ciclo) {
        $sql = "SELECT 
                        decode(B.tipo_cobro,'M','1.Matrícula','E','2.Ensenanza','P','3.Mora','Otros cobros') nombre, 
                        to_char(sum(A.importe),'99,999.99') importe 
                FROM NOE.alumno_contrato_filial_detalle A, NOE.contrato_criterio_filial B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND A.importe > 0 
                AND not B.tipo_Cobro = 'X' 
                GROUP BY decode(B.tipo_cobro,'M','1.Matrícula','E','2.Ensenanza','P','3.Mora','Otros cobros') ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosCobrosTPP($per_id,$ciclo) {
        $sql = "SELECT 
                        decode(B.tipo_cobro,'M','1.Matrícula','E','2.Ensenanza','P','3.Mora','Otros cobros') nombre, 
                        to_char(sum(A.importe),'99,999.99') importe 
                FROM NOE.alumno_contrato_filial_detalle A, NOE.contrato_criterio_filial B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND A.importe > 0 
                AND not B.tipo_Cobro = 'X' 
                GROUP BY decode(B.tipo_cobro,'M','1.Matrícula','E','2.Ensenanza','P','3.Mora','Otros cobros') ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function totalCobros($per_id,$ciclo) {
        $sql = "SELECT 
                        to_char(nvl(sum(A.importe),0),'99,999.99') importe 
                FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 			
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND A.importe>0 
                AND NOT B.tipo_Cobro = 'X' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosDscto($per_id,$ciclo) {
        $sql = "SELECT nombre,importe 
                FROM (
                        SELECT B.orden,
                                B.nombre,
                                NVL (TO_CHAR (SUM (A.importe) * -1, '99,999.99'), ' ') importe
                        FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B
                        WHERE     A.codigo_criterio = B.codigo_criterio
                        AND A.codigo_contrato = B.codigo_contrato
                        AND A.codigo_personal = '".$per_id."'
                        AND A.codigo_contrato = '".$ciclo."'
                        AND B.nombre NOT LIKE '%Uso Lab%'
                        AND A.importe < 0
                        AND NOT B.tipo_Cobro = 'X'
                        GROUP BY B.orden, B.nombre
                        UNION ALL
                        SELECT MAX (B.orden) orden,
                                DECODE (SUM (NVL2 (B.nombre, '0', '1')), '0', 'Descto. Uso Lab.', ' ')
                                 nombre,
                                NVL (TO_CHAR (SUM (A.importe) * -1, '99,999.99'), ' ') importe
                        FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B
                        WHERE     A.codigo_criterio = B.codigo_criterio
                        AND A.codigo_contrato = B.codigo_contrato
                        AND A.codigo_personal = '".$per_id."'
                        AND A.codigo_contrato = '".$ciclo."'
                        AND B.nombre LIKE '%Uso Lab.'
                        AND A.importe < 0
                        AND NOT B.tipo_Cobro = 'X'
                        UNION ALL
                        SELECT MAX (B.orden) orden,
                        DECODE (SUM (NVL2 (B.nombre, '0', '1')),
                              '0', 'Descto. Uso LabEsp.',
                              ' ')
                         nombre,
                        NVL (TO_CHAR (SUM (A.importe) * -1, '99,999.99'), ' ') importe
                        FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B
                        WHERE     A.codigo_criterio = B.codigo_criterio
                        AND A.codigo_contrato = B.codigo_contrato
                        AND A.codigo_personal = '".$per_id."'
                        AND A.codigo_contrato = '".$ciclo."'
                        AND B.nombre LIKE '%Uso LabEsp.'
                        AND A.importe < 0
                        AND NOT B.tipo_Cobro = 'X'
                        UNION ALL
                        SELECT MAX (B.orden) orden,
                        DECODE (SUM (NVL2 (B.nombre, '0', '1')),
                              '0', 'Descto. Uso LabIng.',
                              ' ')
                         nombre,
                        NVL (TO_CHAR (SUM (A.importe) * -1, '99,999.99'), ' ') importe
                        FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B
                        WHERE     A.codigo_criterio = B.codigo_criterio
                        AND A.codigo_contrato = B.codigo_contrato
                        AND A.codigo_personal = '".$per_id."'
                        AND A.codigo_contrato = '".$ciclo."'
                        AND B.nombre LIKE '%Uso LabIng.'
                        AND A.importe < 0
                        AND NOT B.tipo_Cobro = 'X'
                        UNION ALL
                        SELECT MAX (B.orden) orden,
                        DECODE (SUM (NVL2 (B.nombre, '0', '1')),
                              '0', 'Descto. Uso LabMus.',
                              ' ')
                         nombre,
                        NVL (TO_CHAR (SUM (A.importe) * -1, '99,999.99'), ' ') importe
                        FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B
                        WHERE     A.codigo_criterio = B.codigo_criterio
                        AND A.codigo_contrato = B.codigo_contrato
                        AND A.codigo_personal = '".$per_id."'
                        AND A.codigo_contrato = '".$ciclo."'
                        AND B.nombre LIKE '%Uso LabMus.'
                        AND A.importe < 0
                        AND NOT B.tipo_Cobro = 'X'
                        UNION ALL
                        SELECT 
                                99 orden,
                                'Total Descuentos' NOMBRE,
                                TO_CHAR (NVL (SUM (A.importe * -1), 0), '99,999.99') importe
                        FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B
                        WHERE     A.codigo_criterio = B.Codigo_criterio
                        AND A.codigo_contrato = B.Codigo_contrato
                        AND A.codigo_personal = '".$per_id."'
                        AND A.codigo_contrato = '".$ciclo."'
                        AND A.importe < 0
                        AND NOT B.tipo_Cobro = 'X'
                        ORDER BY orden
                ) WHERE ORDEN IS NOT NULL ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function totalDscto($per_id,$ciclo) {
        $sql = "SELECT TO_CHAR (NVL (SUM (A.importe * -1), 0), '99,999.99') importe
                FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B
                WHERE     A.codigo_criterio = B.Codigo_criterio
                AND A.codigo_contrato = B.Codigo_contrato
                AND A.codigo_personal = '".$per_id."'
                AND A.codigo_contrato = '".$ciclo."'
                AND A.importe < 0
                AND NOT B.tipo_Cobro = 'X'
                ORDER BY B.orden ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosDsctoProesad($per_id,$ciclo) {
        $sql = "SELECT 
                        B.nombre,
                        NVL (TO_CHAR (SUM (A.importe) * -1, '99,999.99'), ' ') importe
                FROM NOE.alumno_contrato_filial_detalle A, NOE.contrato_criterio_filial B
                WHERE A.codigo_criterio = B.codigo_criterio
                AND A.codigo_contrato = B.codigo_contrato
                AND A.codigo_personal = '".$per_id."'
                AND A.codigo_contrato = '".$ciclo."'
                AND A.importe < 0
                AND NOT B.tipo_Cobro = 'X'
                GROUP BY B.orden, B.nombre ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosDsctoEPG($per_id,$ciclo) {
        $sql = "SELECT 
                        B.nombre,
                        NVL (TO_CHAR (SUM (A.importe) * -1, '99,999.99'), ' ') importe
                FROM NOE.alumno_contrato_upg_detalle A, NOE.contrato_criterio_pg B
                WHERE A.codigo_criterio = B.codigo_criterio
                AND A.codigo_contrato = B.codigo_contrato
                AND A.codigo_personal = '".$per_id."'
                AND A.codigo_contrato = '".$ciclo."'
                AND A.importe < 0
                AND NOT B.tipo_Cobro = 'X'
                GROUP BY B.orden, B.nombre ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function datosDsctoTPP($per_id,$ciclo) {
        $sql = "SELECT 
                        B.nombre,
                        NVL (TO_CHAR (SUM (A.importe) * -1, '99,999.99'), ' ') importe
                FROM NOE.alumno_contrato_filial_detalle A, NOE.contrato_criterio_filial B
                WHERE A.codigo_criterio = B.codigo_criterio
                AND A.codigo_contrato = B.codigo_contrato
                AND A.codigo_personal = '".$per_id."'
                AND A.codigo_contrato = '".$ciclo."'
                AND A.importe < 0
                AND NOT B.tipo_Cobro = 'X'
                GROUP BY B.orden, B.nombre ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function totalContrato($per_id,$ciclo) {
        $sql = "SELECT 'Importe Total de Contrato Academico' glosa,
                    to_char(nvl(sum(A.importe),0),'99,999.99') importe 
                FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 			
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND NOT B.tipo_Cobro = 'X' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function totalContratoProesad($per_id,$ciclo) {
        $sql = "SELECT 'Importe Total de Contrato Academico' glosa,
                    to_char(nvl(sum(A.importe),0),'99,999.99') importe 
                FROM NOE.alumno_contrato_filial_detalle A, NOE.contrato_criterio_filial B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 			
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND NOT B.tipo_Cobro = 'X' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function totalContratoEPG($per_id,$ciclo) {
        $sql = "SELECT 'Importe Total de Contrato Academico' glosa,
                    to_char(nvl(sum(A.importe),0),'99,999.99') importe 
                FROM NOE.alumno_contrato_upg_detalle A, NOE.contrato_criterio_pg B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 			
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND NOT B.tipo_Cobro = 'X' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function totalContratoTPP($per_id,$ciclo) {
        $sql = "SELECT 'Importe Total de Contrato Academico' glosa,
                    to_char(nvl(sum(A.importe),0),'99,999.99') importe 
                FROM NOE.alumno_contrato_filial_detalle A, NOE.contrato_criterio_filial B 
                WHERE A.codigo_criterio = B.Codigo_criterio 
                AND A.codigo_contrato = B.Codigo_contrato 			
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                AND NOT B.tipo_Cobro = 'X' ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function contratoDebitado($per_id,$ciclo) {
        $sql = "SELECT 
                        decode(C.tipo_pago,
                        '5','Debitado en Matricula por pago al Contado - Plan 1',
                        '1','Debitado en Matricula por pago en Armadas - Plan 2',
                        'Debitado en Matricula por pago en Armadas - Plan 5') glosa,
                        decode(C.tipo_pago,'5','1','1','2','2','3','3','4','5') tipo_pago, 
                        to_char(nvl(sum(decode(B.tipo_cobro, 
                                                        'M',A.importe, 
                                                        'P',A.importe, 
                                                        'E',decode(C.tipo_pago,'1',A.importe/2,'2',A.importe/3,'3',A.importe/4,A.importe/5,'5',A.importe/1), 
                                                        'I',decode(C.tipo_pago,'1',A.importe/2,'2',A.importe/3,'3',A.importe/4,A.importe/5,A.importe,'5',A.importe/1),0)),0),'99,999.99') importe 
                FROM NOE.alumno_contrato_detalle A, NOE.contrato_criterio B, NOE.alumno_contrato C 
                WHERE A.codigo_criterio = B.codigo_criterio 
                AND A.codigo_contrato = B.codigo_contrato 		
                AND A.codigo_contrato = C.codigo_contrato  
                AND A.codigo_personal = C.codigo_personal  
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                GROUP BY C.tipo_pago ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function contratoDebitadoEPG($per_id,$ciclo) {
        $sql = "SELECT 
                        decode(C.tipo_pago,
                        '1','Debitado en Matricula por pago al Contado - Plan 1',
                        'Debitado en Matricula por pago en Cuotas - Plan '||C.tipo_pago) glosa,
                        C.tipo_pago, 
                        to_char(sum(decode(B.tipo_cobro,'M',A.importe,A.importe/C.tipo_pago)),'99,999.99') importe 
                FROM NOE.alumno_contrato_upg_detalle A, NOE.contrato_criterio_pg B, NOE.alumno_contrato_upg C 
                WHERE A.codigo_criterio = B.codigo_criterio 
                AND A.codigo_contrato = B.codigo_contrato 		
                AND A.codigo_contrato = C.codigo_contrato  
                AND A.codigo_personal = C.codigo_personal  
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                GROUP BY C.tipo_pago ";

        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function contratoDebitadoProesad($per_id,$ciclo) {
        $sql = "SELECT 
                        decode(C.tipo_pago,
                        '5','Debitado en Matricula por pago al Contado - Plan 1',
                        'Debitado en Matricula por pago en Armadas - Plan 4') glosa,
                        decode(C.tipo_pago,'5','1','1','2','2','3','3','4','4') tipo_pago, 
                        to_char(sum(decode(B.tipo_cobro,'M',A.importe,'P',A.importe,'E',decode(C.tipo_pago,'5',A.importe,A.importe/4),0)),'99,999.99') importe 
                FROM NOE.alumno_contrato_filial_detalle A, NOE.contrato_criterio_filial B, NOE.alumno_contrato_filial C 
                WHERE A.codigo_criterio = B.codigo_criterio 
                AND A.codigo_contrato = B.codigo_contrato 		
                AND A.codigo_contrato = C.codigo_contrato  
                AND A.codigo_personal = C.codigo_personal  
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                GROUP BY C.tipo_pago ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function contratoDebitadoTPP($per_id,$ciclo) {
        $sql = "SELECT 
                        decode(C.tipo_pago,
                        '1','Debitado en Matricula por pago al Contado - Plan 1',
                        'Debitado en Matricula por pago en Armadas - Plan 5') glosa,
                        decode(C.tipo_pago,'1','1','5') tipo_pago, 
                        to_char(sum(decode(B.tipo_cobro,'M',A.importe,'P',A.importe,'E',decode(C.tipo_pago,'1',A.importe,A.importe/5),0)),'99,999.99') importe 
                FROM NOE.alumno_contrato_filial_detalle A, NOE.contrato_criterio_filial B, NOE.alumno_contrato_filial C 
                WHERE A.codigo_criterio = B.codigo_criterio 
                AND A.codigo_contrato = B.codigo_contrato 		
                AND A.codigo_contrato = C.codigo_contrato  
                AND A.codigo_personal = C.codigo_personal  
                AND A.codigo_personal = '".$per_id."' 
                AND A.codigo_contrato = '".$ciclo."' 
                GROUP BY C.tipo_pago ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function contratoCuotas($ciclo,$pago,$eap) {
        $sql = "SELECT
                        DECODE(CUO_NRO_CUOTA,2,'2da Armada',3,'3ra Armada',4,'4ta Armada',5,'5ta Armada','Al Contado')||': '||TO_CHAR(CUO_FECHA,'DD.MM.YYYY') AS FECHA,
                        CUO_SISTEMA_CUOTA
                FROM UPEU_CUOTAS
                WHERE CUO_CONTRATO = '".$ciclo."'
                AND SUBSTR(CUO_EAP,1,5) = '1".$eap."'
                AND CUO_MOSTRAR = '1'
                AND CUO_ESTADO = '1'
                AND CUO_SISTEMA_CUOTA <= $pago
                GROUP BY CUO_NRO_CUOTA,CUO_FECHA, CUO_SISTEMA_CUOTA
                ORDER BY CUO_NRO_CUOTA ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function validaSede($per_id) {
        $sql = "SELECT 
                        CODIGO_PERSONAL PER_ID,                       
                        nvl((select decode(substr(plan_id,1,5),'30109','5','30114','5','30129','5',decode(substr(plan_id,1,3),'106','6',substr(plan_id,1,1))) from NOE.ALUM_PLAN where NOE.ALUM_PLAN.codigo_personal = datos_personales.codigo_personal and estado = '1'),'1') sede,
                        decode(nvl((select decode(substr(plan_id,1,5),'30109','5','30114','5','30129','5',substr(plan_id,1,1)) from NOE.ALUM_PLAN where NOE.ALUM_PLAN.codigo_personal = datos_personales.codigo_personal and estado = '1'),'1'),'1','Lima','5','Tarapoto','Juliaca') nombe
                FROM NOE.DATOS_PERSONALES
                WHERE CODIGO_PERSONAL = '".$per_id."' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function validaContrato($per_id,$ciclo) {
        $sql = "SELECT CODIGO_CONTRATO FROM ALUMNO_CONTRATO
                WHERE CODIGO_PERSONAL = '".$per_id."'
                AND CODIGO_CONTRATO = '".$ciclo."'
                UNION ALL
                SELECT CODIGO_CONTRATO FROM ALUMNO_CONTRATO_FILIAL
                WHERE CODIGO_PERSONAL = '".$per_id."'
                AND CODIGO_CONTRATO = '".$ciclo."'
                UNION ALL
                SELECT CODIGO_CONTRATO FROM ALUMNO_CONTRATO_UPG
                WHERE CODIGO_PERSONAL = '".$per_id."'
                AND CODIGO_CONTRATO = '".$ciclo."' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function periodoHorario() {
        $sql = "SELECT
			periodo, 
			decode(turno,01,'Mañana',02,'Tarde',03,'Noche') nom_turno, 
			turno, 
			horas 
			from noe.academico_horario order by turno,periodo ";

        $oQuery = DB::connection('oracleapp')->select($sql);      
        return $oQuery;
    }
    public static function accesoEstadoCta($per_id) {
        $dato = "N";
        $sql = "SELECT 'S' dato FROM ACCESOS_ROL_USER
                WHERE ID_ROL = '004'
                AND ID_PERSONAL = '$per_id' ";
        $oQuery = DB::connection('oracleapp')->select($sql);   
        foreach ($oQuery as $key => $item){
            $dato = $item->dato;                
        }
        return $dato;
    }
    public static function showAlumno($codigo) {  
        $alu_id = "";
        $sql = "SELECT MAX(CODIGO_PERSONAL) as codigo_personal FROM DATOS_PERSONALES 
                WHERE (DOCUMENTOS_CODUNIV = '".$codigo."' OR DOCUMENTOS_DNI = '".$codigo."' OR 
                UPPER(DATO_NOMBRES||' '||DATO_APELLIDO_PATERNO||' '||DATO_APELLIDO_MATERNO) LIKE UPPER('%".$codigo."%') )
                AND DOCUMENTOS_CODUNIV IS NOT NULL ";
        $oQuery = DB::connection('oracleapp')->select($sql);   
        foreach ($oQuery as $key => $item){
            $alu_id = $item->codigo_personal;                
        }
        return $alu_id;
    }
    public static function datosPlanilla($id_anho, $id_mes,$id_nivel) { 
        $sql = "SELECT
                        B.name_full AS NOMBRE,
                        name_first as NOMBRES,
                        name_midle as PATERNO,
                        name_last as MATERNO,
                        case B.id_gender when 122 then 'M' else 'F' end SEXO,
                        B.job_title_name AS CARGO,
                        COALESCE(B.e_char_3,'') AS ESSALUD_COD,
                        COALESCE(B.np_char_3,'') AS CUSSP,
                        CONVERT(VARCHAR(10), B.date_birth, 103) AS NACIMIENTO,
                        COALESCE(B.code_national,'') AS DNI,
                        CONVERT(VARCHAR(10), B.date_hired, 103)AS INGRESO,
                        COALESCE(CONVERT(VARCHAR(10), B.ee_datetime_6, 103),'')AS CESE,
                        case when B.status_active = '1' then 'Activo' else 'INACTIVO' end ACTIVO,
                        X.CODIGO,
                        X.MES
                FROM (
                        SELECT
                                        Codigo,
                                        Nombre,
                                        Departamento,
                                        mes,
                                        anho
                        FROM (
                                SELECT
                                        Department_code Departamento,
                                        Enrollment_code Codigo,
                                        Name_full Nombre,Allowance_code Concepto,
                                        case when allowance_code in (1500 ,1522)then convert(int, item_money_1) end Cod_AFP,
                                        case when allowance_code in (1500 ,1501, 1508,1522) then item_money_1 end AFP,
                                        Value Valor,
                                        case when post_date is null then 0 else 1 end Pagado, Date_hired Inicio,
                                        period mes,
                                        year anho
                                FROM APS..v_payment_union
                                WHERE year = ".$id_anho."
                                AND period = ".$id_mes."
                                AND id_type_payment = '98626'
                        ) A LEFT JOIN APS..v_virtual_entity_row B
                        ON B.code = Cod_AFP
                        AND B.entity_object = 'TYPE_RETIREMENT'
                        GROUP BY Codigo, Nombre, Departamento,mes,anho
                        HAVING Departamento LIKE '1%'
                ) X, APS..v_employee B
                WHERE X.CODIGO = B.enrollment_code
                AND B.status_active = '".$id_nivel."%'
                AND B.id_enrollment
                IN (
                        SELECT
                                max(id_enrollment) as id
                        FROM APS..v_employee
                        GROUP BY id_enrollment
                )
                ORDER BY Nombre ";
        $oQuery = DB::connection('sqlsrv')->select($sql); 
        return $oQuery;
    }
    
     public static function cargaDocente($per_id) {
         
        $sql = " SELECT COALESCE(MAX(a.CARGA_ID),'-') AS  CARGA_ID 
                    FROM ( 
                      SELECT CARGA_ID, SUBSTR(carga_id,0,4) SEMESTRE 
                      FROM acad_carga_academica  
                      WHERE (codigo_personal='".$per_id."' 
                      or docente_adjunto= '".$per_id."')
                      UNION 
                      select b.semestre carga, SUBSTR(b.semestre,1,4) SEMESTRE  from profesor_cargo_detalle a, 
                      profesor_cargo b where a.id_procarg=B.ID_PROCAR 
                      and (a.aux_curso like '%P%' or a.nro_bloque<>'0') 
                      and B.CODIGO_PERSONAL='".$per_id."'
                    ) a ";

        $oQuery = DB::connection('oracleaacad')->select($sql);
        $carga_id='';
        foreach ($oQuery as $row){
            $carga_id=$row->carga_id;
        }
        $sql = "SELECT 
                    carga_id,
                    case when carga_id='".$carga_id."' then 1 else 0 end as estado
                FROM acad_carga_cursos 
                WHERE (codigo_personal = '".$per_id."'   
                or docente_adjunto = '".$per_id."')  
                GROUP BY carga_id 
                ORDER BY carga_id DESC ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function cargaCursoDocente($carga_id,$per_id) {
        /*$sql = "select 
                    a.curso_carga_id,
                    a.area_id,
                    a.unidad_id,
                    a.sector_id,
                    a.plan_id,
                    replace(CONVERT(a.nombre_curso,'US7ASCII','WE8ISO8859P1'),'?','n') as nombre_curso ,
                    a.ciclo,
                    a.creditos,
                    a.ht,
                    a.hp,
                    a.grupo,
                    a.hnp,
                    a.horario_t,
                    replace(CONVERT(u.nombre_corto,'US7ASCII','WE8ISO8859P1'),'?','n') as fac,
                    replace(CONVERT(s.nombre_corto,'US7ASCII','WE8ISO8859P1'),'?','n') as eap  
                from acad_carga_academica a, univ_unidad u,univ_sector s
                where a.unidad_id=u.unidad_id
                and a.sector_id=s.sector_id
                and u.unidad_id=s.unidad_id
                and (a.codigo_personal = '".$per_id."'   
                or a.docente_adjunto = '".$per_id."') 
                and a.carga_id='".$carga_id."'
                order by u.unidad_id,s.sector_id,a.curso_carga_id ";*/
        
            $sqlacad = "select 
                        distinct 
                        a.curso_carga_id,
                        --base_urls.encrypt(a.curso_carga_id ) curso_carga_id_x , 
                        a.plan_id, 
                        substr(a.plan_id,7,4) anio_plan, 
                        a.es_teoria,
                        ( CASE WHEN ( CASE WHEN NVL( a.codigo_personal ,'X')!='".$per_id."' THEN 'A' ELSE'T' END)='T' THEN a.codigo_personal ELSE  a.docente_Adjunto END) codigo_personal, 
                        ( CASE WHEN NVL( a.codigo_personal ,'X')!='".$per_id."'  THEN 'A' ELSE 'T' END) tipo_doc  ,  
                        ( CASE WHEN NVL(apellido(a.docente_adjunto),'X')='X' THEN 'N' ELSE ( CASE WHEN a.codigo_personal=a.docente_adjunto THEN 'N' ELSE 'S' END) END)  tiene_doc_adj, 
                        nvl(a.grupo,'--') grupo, 
                        a.ciclo, 
                        a.tipo, 
                       (CASE WHEN a.sector_id='10309' then  horario_prof_cur(a.curso_carga_id,'".$per_id."')  else horario_curso_profesor(a.curso_carga_id,a.codigo_personal) end) horario_t ,
                        a.horario_p, 
                        nvl(TO_CHAR((Select wm_concat(ach.tipo || ': ' || decode(substr(ach.salon,1,5),'-TPP-',substr(ach.salon,6),ach.salon)|| '('|| DIA_HORA_CURSO(ach.horario) || ')') from acad_curso_horario ach where ach.curso_carga_id=a.curso_carga_id)),'NA') salon,
                        a.salon_t, 
                        a.salon_p, 
                        a.area_id, 
                        a.unidad_id, 
                        a.sector_id, 
                        ( SELECT x.filial FROM UNIV_SECTOR x WHERE  a.sector_id=x.SECTOR_ID ) filial , 
                        replace(CONVERT(nombre_sector( a.sector_id,1),'US7ASCII','WE8ISO8859P1'),'?','n')  sector, 
                        decode(a.modo,'R','Regul.','E','Extra.','D','Dirig.',modo) modo,a.modo modo_n, 
                        replace(CONVERT(a.nombre_curso,'US7ASCII','WE8ISO8859P1'),'?','n') as nombre_curso, 
                        nvl(a.tipo_curso,'2') tipo_curso,nvl(a.tutoria,'0') tutoria  
                        from ( 
                           select 
                               a.curso_carga_id, 
                               a.plan_id, 
                               b.codigo_personal,
                               a.grupo ,
                               a.ciclo, 
                               (case when (select g.codigo_personal from acad_carga_academica g where g.curso_carga_id=b.curso_carga_id and g.origen='O')='".$per_id."' then 'T' else 'P' end) es_teoria,
                               a.horario_p, a.salon_t, 
                               a.salon_p, 
                               a.area_id,
                               a.origen, 
                               a.carga_id ,
                               a.docente_adjunto, 
                               a.unidad_id, 
                               a.sector_id,
                               a.modo,
                               a.nombre_curso,
                               a.tipo_curso,
                               a.tutoria,
                               b.horario,
                               a.tipo 
                           from acad_carga_academica a , acad_curso_horario b 
                           where a.curso_carga_id=b.curso_carga_id and a.carga_id='".$carga_id."'
                           union 
                           select  y.curso_carga_id, 
                           y.plan_id, 
                           (SELECT c.codigo_personal FROM profesor_cargo c where c.semestre='".$carga_id."' and c.id_procar=x.id_procarg) codigo_personal, 
                           y.grupo ,
                           y.ciclo,
                           (case when (select g.codigo_personal from acad_carga_academica g where g.curso_carga_id=x.aux_curso)='".$per_id."' then 'T' else 'P' end) es_teoria,
                           y.horario_p, 
                           y.salon_t, 
                           y.salon_p, 
                           y.area_id,
                           y.origen, 
                           y.carga_id ,
                           y.docente_adjunto, 
                           y.unidad_id, 
                           y.sector_id,
                           y.modo,
                           y.nombre_curso,
                           y.tipo_curso,
                           y.tutoria,
                           LPAD(NVL(x.horario,'0'),112,'0') horario,
                           y.tipo 
                           from PROFESOR_CARGO_DETALLE x, acad_carga_academica y 
                           where x.aux_curso=y.curso_Carga_id 
                           and y.codigo_personal not in ('".$per_id."') 
                           and  x.semestre='".$carga_id."' and x.nro_bloque<>0 
                           and x.id_procarg=(SELECT h.ID_PROCAR FROM profesor_cargo h where h.semestre='".$carga_id."' and h.codigo_personal='".$per_id."') 
                         ) a 
                         where a.codigo_personal = '".$per_id."' 
                         and a.carga_id =  '".$carga_id."' 
                         and a.origen = 'O' 
                         and not a.plan_id like ('6%') 
                         AND (a.area_id<>'3' OR a.docente_adjunto = '".$per_id."') 
                         AND a.origen = 'O' 
                         AND NOT a.plan_id LIKE ('6%') 
                         AND a.area_id<>'3' 
                         union 
                         select 
                         b.ID_PCD||'-11' curso_carga_id, 
                         --base_urls.encrypt(b.ID_PCD||'-11') curso_carga_id_x, 
                         a.ID_PROCAR||'9-'||a.ID_PROCAR||'-2' plan_id, 
                         '000' anio_plan, 
                         (case when (select g.codigo_personal from acad_carga_academica g where g.curso_carga_id=b.aux_curso)='".$per_id."' then 'T' else 'P' end) es_teoria,
                         a.CODIGO_PERSONAL, 
                         'T' tipo_doc, 
                         'N' tiene_doc_adj,
                         '--' grupo, 
                         0 ciclo,
                         'Z' tipo, 
                         nvl(b.HORARIO,'0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000') horario_t,
                         '0' horario_p,
                         '' salon, 
                         '' salon_t, 
                         '' salon_p, 
                         substr(a.UNIDAD_ID,1,1) area_id, 
                         a.unidad_id, 
                         '00000' sector_id, 
                         substr(a.UNIDAD_ID,1,1) filial, 
                         replace(CONVERT(nombre_unidad(a.unidad_id),'US7ASCII','WE8ISO8859P1'),'?','n') sector, 
                         'R' modo, 
                         'R' modo_n, 
                         ( select replace(CONVERT(x.CARG_DESCRIPCION,'US7ASCII','WE8ISO8859P1'),'?','n')  from cargo x where x.CARG_ID=b.CARG_ID) nombre_curso, 
                         '3' tipo_curso, 
                         0 tutoria 
                         from profesor_cargo a , profesor_cargo_Detalle b 
                         where a.ID_PROCAR=b.ID_PROCARG 
                         and a.codigo_personal='".$per_id."' 
                         and a.SEMESTRE='".$carga_id."' 
                         and B.CARG_ID not in ('0132','0237','0238','0246')
                         ORDER BY tipo ASC,unidad_id ,sector_id,curso_carga_id,grupo ";
            
            //echo $sql;
     $sql = "select 
                    a.curso_carga_id,
                    a.area_id,
                    a.unidad_id,
                    a.sector_id,
                    a.plan_id,
                    replace(CONVERT(a.nombre_curso,'US7ASCII','WE8ISO8859P1'),'?','n') as nombre_curso ,
                    a.ciclo,
                    a.creditos,
                    a.ht,
                    a.hp,
                    a.grupo,
                    a.hnp,
                    a.horario_t,
                    replace(CONVERT(u.nombre_corto,'US7ASCII','WE8ISO8859P1'),'?','n') as fac,
                    replace(CONVERT(s.nombre_corto,'US7ASCII','WE8ISO8859P1'),'?','n') as eap,
                    a.tipo_curso
                from acad_carga_academica a, univ_unidad u,univ_sector s
                where a.unidad_id=u.unidad_id
                and a.sector_id=s.sector_id
                and u.unidad_id=s.unidad_id
                and (a.codigo_personal = '".$per_id."'   
                or a.docente_adjunto = '".$per_id."') 
                and a.carga_id='".$carga_id."'
                and a.origen='O'
                order by u.unidad_id,s.sector_id,a.curso_carga_id ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function showCargaCursoDocente($curso_carga_id) {
        $sql = "select 
                    a.curso_carga_id,
                    a.area_id,
                    a.unidad_id,
                    a.sector_id,
                    a.plan_id,
                    replace(CONVERT(a.nombre_curso,'US7ASCII','WE8ISO8859P1'),'?','n') as nombre_curso ,
                    a.ciclo,
                    a.creditos,
                    a.ht,
                    a.hp,
                    a.grupo,
                    a.hnp,
                    a.horario_t, 
                    a.inicio_dt,
                    a.final_dt
                from acad_carga_academica a 
                where a.curso_carga_id = '".$curso_carga_id."' ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function listRubroEvaluacion($curso_carga_id) {
        
        $sql= " and  to_char(SYSDATE,'yyyy-mm-dd') > to_char(fecha+22,'yyyy-mm-dd') ";
        $sql2= " and to_char(fecha+22,'yyyy-mm-dd') >= to_char(SYSDATE,'yyyy-mm-dd')  ";

        $comando=" SELECT AREA_ID FROM acad_carga_academica where curso_carga_id='".$curso_carga_id."' and origen='O' ";
        $oQuery = DB::connection('oracleaacad')->select($comando);
        foreach($oQuery as $row){
		if($row->area_id=="2"){
			$sql= " and  to_char(SYSDATE,'yyyy-mm-dd') > to_char(fecha+11,'yyyy-mm-dd') ";
			$sql2= " and to_char(fecha+11,'yyyy-mm-dd') >= to_char(SYSDATE,'yyyy-mm-dd') ";
		}
        }

        $comando="UPDATE acad_carga_evaluacion SET restriccion = '0' where curso_carga_id = '".$curso_carga_id."' and restriccion='1' ".$sql2." ";
        DB::connection('oracleaacad')->update($comando);
        
        $comando=" select a.evaluacion_id,'N' tipo from acad_carga_evaluacion a where a.curso_carga_id='".$curso_carga_id."' and a.restriccion='0' ".$sql." 
    		and a.evaluacion_id not in ( select x.evaluacion_id from califica_apertura x where x.CURSO_CARGA_ID = a.curso_carga_id ) 
    		union 
    		select x.evaluacion_id,'S' tipo from califica_apertura x where x.CURSO_CARGA_ID='".$curso_carga_id."'
                and to_char(SYSDATE,'yyyy-mm-dd')>to_char(x.fecha,'yyyy-mm-dd') ";
        $oQuery = DB::connection('oracleaacad')->select($comando);
        foreach($oQuery as $row){
            $tipo        = $row->tipo;
            $evaluacion_id  = $row->evaluacion_id;

            if($tipo=="S"){
                            //ub: eliminamos el registro en califica_apertura
                $comando = "delete CALIFICA_APERTURA 
                            where curso_carga_id = '".$curso_carga_id."'  
                            and evaluacion_id='".$evaluacion_id."' ";
                DB::connection('oracleaacad')->delete($comando);
             }
             $comando =	"update acad_carga_evaluacion set 
                           restriccion = '1' 
                           where curso_carga_id = '".$curso_carga_id."'  
                           and evaluacion_id = '".$evaluacion_id."' ";
             DB::connection('oracleaacad')->update($comando);
        }
        
       
    
        $sql = "select 
                a.evaluacion_id,
                a.curso_carga_id,
                a.fecha,
                to_char(a.fecha,'DD/MM/YYYY') as fecha_eva,
                replace(CONVERT(a.descripcion,'US7ASCII','WE8ISO8859P1'),'?','n') as descripcion,
                a.ponderado,
                a.restriccion,
                replace(CONVERT(decode(nvl(b.nombre,'X'),'X', a.descripcion, b.nombre ),'US7ASCII','WE8ISO8859P1'),'?','n')   as estrategia,
                (to_char(a.fecha+11, 'yyyymmdd') - to_char(sysdate, 'yyyymmdd')) as fecha_resta,
                decode(B.tipo,'T','Conocimiento','P','Desempeno','F','Actitudinal','') as tipo
              from acad_carga_evaluacion a ,academico_estrategias b
            where a.estrategia = b.codigo_estrategia
            and a.curso_carga_id = '".$curso_carga_id."'
            order by a.fecha,a.evaluacion_id ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function showRubroEvaluacion($evaluacion_id) {
        $sql = "select 
                evaluacion_id,
                curso_carga_id,
                fecha,
                to_char(fecha,'DD/MM/YYYY') as fecha_eva,
                replace(CONVERT(descripcion,'US7ASCII','WE8ISO8859P1'),'?','n') as descripcion,
                ponderado,
                restriccion
            from acad_carga_evaluacion 
            where curso_carga_id = '".$evaluacion_id."'";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function listAlumnoEvaluacion($evaluacion_id) {
        
        
        $sql="select 
                SUBSTR(C.plan_id,1,1) as area_id,
                SUBSTR(C.plan_id,1,3) as unidad_id,
                SUBSTR(C.plan_id,1,5) AS SECTOr_ID,
                c.nota_aprobatoria
            FROM  ACAD_CARGA_CURSOS A,
            ACAD_CARGA_APLICABLE B,ACAD_PLAN_CURSOS C
            WHERE A.curso_carga_id = B.curso_carga_id
            AND B.curso_plan_id  = C.curso_plan_id
            AND B.ORIGEN='O' 
            AND A.curso_carga_id IN(
                SELECT curso_carga_id FROM acad_carga_evaluacion
                WHERE evaluacion_id='".$evaluacion_id."'
            ) ";

        $oQuery = DB::connection('oracleaacad')->select($sql);
        $sector_id='';
        $area_id='1';
        $unidad_id='1';
        
        $nota_apro=0;
        foreach($oQuery as $row){
            $sector_id=$row->sector_id;
            $area_id=$row->area_id;
            $unidad_id=$row->unidad_id;
            $nota_apro=$row->nota_aprobatoria;
        }
        $tabla="alumno_contrato";
        if(($area_id=="5") or ($area_id=="3")){
            $tabla="alumno_contrato_filial";
        }
        if($area_id=="106"){
            $tabla="alumno_contrato_upg";
        }
        $sql = "select 
                    a.codigo_personal,
                    B.evaluacion_id,
                    b.curso_carga_id,
                    replace(CONVERT(c.dato_apellido_paterno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_paterno,
                    replace(CONVERT(c.dato_apellido_materno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_materno,
                    replace(CONVERT(c.dato_nombres,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_nombres,
                    coalesce(e.nota,0) as nota,
                    coalesce(e.nota,0) as actual,
                    B.restriccion,
                    decode(A.condicion, '0','Desaprobado','1', 'Aprobado', '2', 'Desaprobado', '3', 'Retirado', '4', 'LiFal.', '5', 'Aband.', '6', 'Sancionado', A.condicion) condicionx,
                    A.condicion
                from ALUM_CURSO_ACT A INNER JOIN acad_carga_evaluacion B
                ON a.curso_carga_id=b.curso_carga_id
                INNER JOIN DATOS_PERSONALES C
                ON A.codigo_personal=C.codigo_personal
                LEFT JOIN ALUM_EVALUACION e
                ON e.evaluacion_id=b.evaluacion_id
                AND e.codigo_personal=c.codigo_personal
                AND e.codigo_personal=a.codigo_personal
                AND  e.curso_carga_id=a.curso_carga_id
                AND  e.curso_carga_id=b.curso_carga_id
                WHERE  B.evaluacion_id='".$evaluacion_id."'
                AND A.estado in ('1','I','2')
                AND  nvl(( select distinct x.estado  from ".$tabla." x where x.codigo_contrato=substr(A.curso_carga_id,0,6) and x.codigo_personal=A.codigo_personal and substr(x.plan_id,1,5)='".$sector_id."' ),'1')='1'
                ORDER BY c.dato_apellido_paterno,
                c.dato_apellido_materno,
                c.dato_nombres,
                A.codigo_personal";
       //dd($sql);
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function listaAlumnoNota($curso_carga_id) {
        $sql = "SELECT 
                    EVALUACION_ID,
                    CURSO_CARGA_ID,
                    FECHA,
                    ESTRATEGIA,
                    replace(CONVERT(DESCRIPCION,'US7ASCII','WE8ISO8859P1'),'?','n') as DESCRIPCION,
                    PONDERADO,
                    RESTRICCION,
                    ID_EVIDENCIA_RESULTADO
                FROM acad_carga_evaluacion 
                WHERE CURSO_CARGA_ID='".$curso_carga_id."'
                ORDER BY fecha,evaluacion_id";
        $dataeva = DB::connection('oracleaacad')->select($sql);
       
        
        $sql = "select 
                area_id
            FROM  ACAD_CARGA_ACADEMICA 
            WHERE CURSO_CARGA_ID='".$curso_carga_id."'
            AND ORIGEN='O' ";
        $data = DB::connection('oracleaacad')->select($sql);
        $area_id='';
        foreach($data as $row){
            $area_id=$row->area_id;
        }
                
        $tabla="alumno_contrato";
        if(($area_id=="5") or ($area_id=="3")){
            $tabla="alumno_contrato_filial";
        }
        if($area_id=="106"){
            $tabla="alumno_contrato_upg";
        }
        
        $sql = "select 
                    a.codigo_personal,
                    a.curso_carga_id,
                    replace(CONVERT(c.dato_apellido_paterno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_paterno,
                    replace(CONVERT(c.dato_apellido_materno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_materno,
                    replace(CONVERT(c.dato_nombres,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_nombres,
                    decode(A.condicion,'M','Mat.', '0','Desaprobado','1', 'Aprobado', '2', 'Desaprobado', '3', 'Retirado', '4', 'LiFal.', '5', 'Aband.', '6', 'Sancionado', A.condicion) condicion_desc,
                    nvl(alum_nota_cursomed(A.codigo_personal,A.curso_carga_id), 0) promedio,
                    A.condicion
                from ALUM_CURSO_ACT A 
                INNER JOIN DATOS_PERSONALES C
                ON A.codigo_personal=C.codigo_personal
                WHERE  A.curso_carga_id='".$curso_carga_id."'
                AND A.estado in ('1','I','2')
                AND  nvl(( select distinct x.estado  from ".$tabla." x where x.codigo_contrato=substr(A.curso_carga_id,0,6) and x.codigo_personal=A.codigo_personal and substr(x.plan_id,1,5)='103' ),'1')='1'
                ORDER BY c.dato_apellido_paterno,
                c.dato_apellido_materno,
                c.dato_nombres,a.codigo_personal";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        $data=array();
        foreach($oQuery as $row){
            $reg=array();
            $reg["condicion_desc"]=$row->condicion_desc;
            $reg["curso_carga_id"]=$row->curso_carga_id;
            $reg["codigo_personal"]=$row->codigo_personal;
            $reg["dato_apellido_paterno"]=$row->dato_apellido_paterno;
            $reg["dato_apellido_materno"]=$row->dato_apellido_materno;
            $reg["dato_nombres"]=$row->dato_nombres;
            $reg["promedio"]=$row->promedio;
            $reg["condicion"]=$row->condicion;
            $sql="SELECT  
                        A.CURSO_CARGA_ID,
                        A.evaluacion_id,
                        B.CODIGO_PERSONAL,
                        coalesce(B.NOTA,0) as NOTA
                    FROM ACAD_CARGA_EVALUACION A LEFT JOIN ALUM_EVALUACION B
                    ON A.evaluacion_id=B.evaluacion_id
                    AND A.CURSO_CARGA_ID=B.CURSO_CARGA_ID
                    AND B.CODIGO_PERSONAL='".$row->codigo_personal."'
                    WHERE A.CURSO_CARGA_ID='".$curso_carga_id."'
                    ORDER BY A.fecha,A.evaluacion_id";
            $sqldata = DB::connection('oracleaacad')->select($sql);
            $reg["details"]=$sqldata;
            $data[]=$reg;
            
        }
        
        $ret=[
            'evaluaciones'=>$dataeva,
            'lista'=>$data
        ];
        return $ret;
        
    }
    public static function addAlumnoEvaluacion($profesor,$login,$evaluacion_id,$curso_carga_id,$details) {
        
        $response=[
            'nerror'=>0,
            'msgerror'=>''
        ];
        
        $sql="Select 
                a.codigo_personal  
                from acad_carga_cursos a, datos_personales b  
                where a.codigo_personal = b.codigo_personal 
                and a.curso_carga_id = '".$curso_carga_id."' 
                and a.codigo_personal = '".$profesor."' 
                and b.login = '".$login."' 
                union
                Select 
                a.codigo_personal  
                from acad_carga_cursos a, datos_personales b  
                where a.docente_adjunto = b.codigo_personal 
                and a.curso_carga_id = '".$curso_carga_id."' 
                and a.codigo_personal = '".$profesor."'
                and b.login = '".$login."'";
        
        $oQuery = DB::connection('oracleaacad')->select($sql);
        if(count($oQuery)>0){
            foreach($details as $dato){
                $codigo_personal	= $dato->codigo_personal;
                $nota 	        = $dato->nota;
                $actual	        = $dato->actual;


                if($nota==""){ 
                   $nota=0; 
                }
                if($actual==""){ 
                   $actual=0; 
                }

                if($nota==0){   
                    $query="Delete from alum_evaluacion 
                           where codigo_personal = '".$codigo_personal."' 
                           and curso_carga_id = '".$curso_carga_id."' 
                           and evaluacion_id = '".$evaluacion_id."' ";
                    DB::connection('oracleaacad')->delete($query);
                }

                if(($nota==0) and ($nota!=$actual)){

                    $data= DB::connection('oracleaacad')->table('alum_evaluacion')->insert(
                        array('CODIGO_PERSONAL'=>$codigo_personal,
                            'CURSO_CARGA_ID' => $curso_carga_id,
                            'EVALUACION_ID' => $evaluacion_id,
                            'NOTA'=> $actual
                            )
                    );

                }

                if(($nota!=0) and ($actual!=0) and ($nota!=$actual)){

                        $query ="Update alum_evaluacion set 
                                                nota = ".$actual." 
                                                where codigo_personal = '".$codigo_personal."' 
                                                and curso_carga_id = '".$curso_carga_id."' 
                                                and evaluacion_id = '".$evaluacion_id."' ";
                        DB::connection('oracleaacad')->update($query);
                }

                if(($actual==0) and  ($nota!=$actual)){

                        $query="Delete from alum_evaluacion 
                           where codigo_personal = '".$codigo_personal."' 
                           and curso_carga_id = '".$curso_carga_id."' 
                           and evaluacion_id = '".$evaluacion_id."' ";
                        DB::connection('oracleaacad')->delete($query);
                }


                if($nota!=$actual){
                    $fecha=date("Y-m-d H:i:s");
                    $data= DB::connection('oracleaacad')->table('alum_evaluacion_rastreo')->insert(
                        array('CODIGO_PERSONAL'=>$profesor,
                            'COD_PERSONAL_ALUMNO'=>$codigo_personal,
                            'CURSO_CARGA_ID' =>$curso_carga_id,
                            'EVALUACION_ID' =>$evaluacion_id,
                            'NOTA_ANT'=>$nota,
                            'NOTA_ACT'=>$actual,
                            'FECHA_ACT'=>$fecha,
                            'IP'=> 'MOVIL',
                            'LOGIN'=> $login
                            )
                    );
                }
                //calcula nota promedio por curso

                $query =	"Update alum_curso_act set 
                                                nota_promedio = (alum_nota_cursomed(codigo_personal,curso_carga_id))
                                                where codigo_personal = '".$codigo_personal."' 
                                                and curso_carga_id = '".$curso_carga_id."' ";
                DB::connection('oracleaacad')->update($query);

            }
        }else{
            $fecha=date("Y-m-d H:i:s");
            $detalle = "Profesor tratando de ingresar al curso ".$curso_carga_id." el cual no le pertenece";
            $data= DB::connection('oracleaacad')->table('intruso')->insert(
                        array('ID_PERSONAL'=>$profesor,
                            'IP'=> 'MOVIL',
                            'FECHA'=>$fecha,
                            'DETALLE' => $detalle,
                            'ID_JSP'=>'PPR',
                            'LOGIN'=> $login
                            )
                    );
            $response=[
                'nerror'=>1,
                'msgerror'=>'El curso no esta asignado a su persona'
            ];
        }
        return $response;
    }    
    
    public static function listaDiaHorario($curso_carga_id) {
        $sql = "select 
                to_char(inicio_dt,'DD/MM/YYYY') as inicio,
                to_char(final_dt,'DD/MM/YYYY') as final,
                FC_DIAS_HORARIO('".$curso_carga_id."') as dias
            from acad_carga 
            where carga_id = substr('".$curso_carga_id."',1,6)";
        $oQuery = DB::connection('oracleaacad')->select($sql);
       
        
        $ret=[];
        foreach($oQuery as $row){
            $ret["curso_carga_id"]=$curso_carga_id;
            $ret["inicio"]=$row->inicio;
            $ret["final"]=$row->final;
            $dias=$row->dias;
            $adias=[];
            if($dias!='-'){
                $array = explode(",", $dias);
                foreach($array as $item){
                    $a["dia"]=$item;
                    $adias[]=$a;
                }
            }
            $ret["dias"]=$adias;
            
        }
        return $ret;
        
    }
    public static function listaAsistencia($curso_carga_id,$fecha) {
        $sql = "SELECT 
                    B.NUM_VECES,
                    A.ID_ASISTENCIA
                FROM ACAD_ALUM_ASIST_CAB A, 
                ACAD_ALUM_ASISTENCIA B,DATOS_PERSONALES P
                WHERE A.ID_ASISTENCIA=B.ID_ASISTENCIA
                AND B.CODIGO_PERSONAL=P.CODIGO_PERSONAL
                AND A.CURSO_CARGA_ID='".$curso_carga_id."'
                AND A.FECHA_CLASE='".$fecha."'
                GROUP BY B.NUM_VECES,A.ID_ASISTENCIA
                ORDER BY B.NUM_VECES";
        $datasi = DB::connection('oracleaacad')->select($sql);
       
        
        
        $sql = "SELECT 
                    B.ID_ASISTENCIA,
                    A.FECHA_CLASE,
                    A.CURSO_CARGA_ID,
                    B.CODIGO_PERSONAL,
                    replace(CONVERT(p.dato_apellido_paterno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_paterno,
                    replace(CONVERT(p.dato_apellido_materno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_materno,
                    replace(CONVERT(p.dato_nombres,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_nombres
                FROM ACAD_ALUM_ASIST_CAB A, 
                ACAD_ALUM_ASISTENCIA B,DATOS_PERSONALES P
                WHERE A.ID_ASISTENCIA=B.ID_ASISTENCIA
                AND B.CODIGO_PERSONAL=P.CODIGO_PERSONAL
                AND A.CURSO_CARGA_ID='".$curso_carga_id."'
                AND A.FECHA_CLASE='".$fecha."'
                GROUP BY B.ID_ASISTENCIA,
                A.FECHA_CLASE,
                A.CURSO_CARGA_ID,
                B.CODIGO_PERSONAL,
                replace(CONVERT(p.dato_apellido_paterno,'US7ASCII','WE8ISO8859P1'),'?','n'),
                replace(CONVERT(p.dato_apellido_materno,'US7ASCII','WE8ISO8859P1'),'?','n'),
                replace(CONVERT(p.dato_nombres,'US7ASCII','WE8ISO8859P1'),'?','n')
                ORDER BY DATO_APELLIDO_PATERNO,
                DATO_APELLIDO_MATERNO,
                DATO_NOMBRES";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        $data=array();
        foreach($oQuery as $row){
            $reg=array();
            $reg["id_asistencia"]=$row->id_asistencia;
            $reg["fecha_clase"]=$row->fecha_clase;
            $reg["curso_carga_id"]=$row->curso_carga_id;
            $reg["codigo_personal"]=$row->codigo_personal;
            $reg["dato_apellido_paterno"]=$row->dato_apellido_paterno;
            $reg["dato_apellido_materno"]=$row->dato_apellido_materno;
            $reg["dato_nombres"]=$row->dato_nombres;
            
            $sql="SELECT 
                        B.ASIS_TIPO,
                        B.NUM_VECES,
                        B.FECHA_REG,
                        B.FECHA_CONTROL,
                        B.FECHA_JUSTIFICACION,
                        B.DETALLE_JUSTIFI
                    FROM ACAD_ALUM_ASIST_CAB A, 
                    ACAD_ALUM_ASISTENCIA B,DATOS_PERSONALES P
                    WHERE A.ID_ASISTENCIA=B.ID_ASISTENCIA
                    AND B.CODIGO_PERSONAL=P.CODIGO_PERSONAL
                    AND A.CURSO_CARGA_ID='".$curso_carga_id."'
                    AND A.FECHA_CLASE='".$fecha."'
                    AND B.CODIGO_PERSONAL='".$row->codigo_personal."'
                    ORDER BY  B.NUM_VECES";
            $sqldata = DB::connection('oracleaacad')->select($sql);
            $reg["details"]=$sqldata;
            $data[]=$reg;
            
        }
        
        $ret=[
            'asistencias'=>$datasi,
            'lista'=>$data
        ];
        return $ret;
        
    }
    public static function listaAsistenciaNew($curso_carga_id) {
        $sql = "SELECT 
                    replace(CONVERT(c.dato_apellido_paterno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_paterno,
                    replace(CONVERT(c.dato_apellido_materno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_materno,
                    replace(CONVERT(c.dato_nombres,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_nombres,
                    c.documentos_coduniv, 
                    c.codigo_personal
                FROM ALUM_CURSO_ACT b 
                INNER JOIN datos_personales c 
                ON b.codigo_personal = c.codigo_personal 
                and b.estado in ('1','I','2') 
                and b.condicion<>'3' 
                and b.curso_carga_id='".$curso_carga_id."' 
                order by dato_apellido_paterno,dato_apellido_materno,dato_nombres";
        $oQuery = DB::connection('oracleaacad')->select($sql);

        return $oQuery;
        
    }
    public static function listaAsistenciaEdit($id_asisetncia,$num_veces) {
        $sql = "SELECT 
                    B.ID_ASISTENCIA,
                    A.FECHA_CLASE,
                    A.CURSO_CARGA_ID,
                    B.CODIGO_PERSONAL,
                    replace(CONVERT(P.dato_apellido_paterno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_paterno,
                    replace(CONVERT(P.dato_apellido_materno,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_apellido_materno,
                    replace(CONVERT(P.dato_nombres,'US7ASCII','WE8ISO8859P1'),'?','n') as dato_nombres,
                    B.ASIS_TIPO,
                    B.NUM_VECES,
                    B.FECHA_REG,
                    B.FECHA_CONTROL,
                    B.FECHA_JUSTIFICACION,
                    B.DETALLE_JUSTIFI
                FROM ACAD_ALUM_ASIST_CAB A, 
                ACAD_ALUM_ASISTENCIA B,DATOS_PERSONALES P
                WHERE A.ID_ASISTENCIA=B.ID_ASISTENCIA
                AND B.CODIGO_PERSONAL=P.CODIGO_PERSONAL
                AND A.ID_ASISTENCIA='".$id_asisetncia."'
                AND B.NUM_VECES='".$num_veces."'
                ORDER BY DATO_APELLIDO_PATERNO,
                DATO_APELLIDO_MATERNO,
                DATO_NOMBRES";
        $oQuery = DB::connection('oracleaacad')->select($sql);

        return $oQuery;
        
    }
    public static function procAsistencia($curso_carga_id,$fecha,$num_veces,$details) {
        
        $comando = "SELECT 
                        ID_ASISTENCIA , 
                        NUM_VECES_ASIS  
                    FROM ACAD_ALUM_ASIST_CAB 
                    WHERE CURSO_CARGA_ID = '".$curso_carga_id."' 
                    AND FECHA_CLASE = '".$fecha."' ";

        $oQuery = DB::connection('oracleaacad')->select($comando);
        $num_vec=0;
        $id_asistencia='';
        foreach($oQuery as $row){
            $num_vec = $row->num_veces_asis;
            $id_asistencia   = $row->id_asistencia;
        }
        $fecha_sys=date("Y-m-d H:i:s");
        if($num_vec==0){
            $comando  = "SELECT nvl(ACAD_ALUM_ASIST_CAB_FUN('".$curso_carga_id."'),'0001') COD FROM DUAL ";
            $oQuery = DB::connection('oracleaacad')->select($comando);
            foreach($oQuery as $row){
                $cod_ais = $row->cod;
            }
            $num_vec=1;
            $id_asistencia=$curso_carga_id.'-'.$cod_ais;
            $data= DB::connection('oracleaacad')->table('ACAD_ALUM_ASIST_CAB')->insert(
                array('ID_ASISTENCIA'=>$id_asistencia,
                    'FECHA_CLASE'=>$fecha,
                    'NUM_VECES_ASIS'=>'1',
                    'FECHA_CONTROL' => $fecha_sys,
                    'CURSO_CARGA_ID'=>$curso_carga_id
                    )
            );
        }else{
            if($num_veces==0){
                $num_vec = $num_vec+1;
            }else{
                $num_vec = $num_veces;
            }
            
        }
        
        foreach($details as $items){
            
             $comando = "SELECT
                            id_asistencia,
                            codigo_personal
                        FROM  ACAD_ALUM_ASISTENCIA
			WHERE id_asistencia ='".$id_asistencia."' 
                        AND codigo_personal = '".$items->codigo_personal."'
                        AND asis_fecha_just = '".$fecha."'  
                        AND num_veces  ='".$num_vec."' ";
             
            $data = DB::connection('oracleaacad')->select($comando);
            
            
            
            if(count($data)==0){
                
                $fecha_jus='';
                if($items->asis_tipo=="J"){
                    $fecha_jus=$fecha_sys;
                }
                
                $data= DB::connection('oracleaacad')->table('ACAD_ALUM_ASISTENCIA')->insert(
                    array('ID_ASISTENCIA'=>$id_asistencia,
                        'CODIGO_PERSONAL'=>$items->codigo_personal,
                        'ASIS_FECHA_JUST'=>$fecha,
                        'ASIS_TIPO'=>$items->asis_tipo,
                        'DETALLE_JUSTIFI'=>$items->detalle_justifi,
                        'FECHA_JUSTIFICACION'=>$fecha_jus,
                        'NUM_VECES' => $num_vec,
                        'FECHA_REG'=>$fecha_sys,
                        'FECHA_CONTROL'=>$fecha_sys
                        )
                );

            }else{
                $fecha_jus='NULL';
                if($items->asis_tipo=="J"){
                    $fecha_jus="'".$fecha_sys."'";
                }
                $comando = " UPDATE ACAD_ALUM_ASISTENCIA SET 
                                ASIS_TIPO = '".$items->asis_tipo."', 
                                DETALLE_JUSTIFI='".$items->detalle_justifi."',
                                FECHA_JUSTIFICACION=".$fecha_jus.",
                                FECHA_REG = sysdate 
                            WHERE ID_ASISTENCIA ='".$id_asistencia."'  
                            AND CODIGO_PERSONAL = '".$items->codigo_personal."' 
                            AND ASIS_FECHA_JUST = '".$fecha."'   
                            AND NUM_VECES  ='".$num_vec."' ";
                
                DB::connection('oracleaacad')->update($comando);
            }
        }
            
        $comando = " UPDATE ACAD_ALUM_ASIST_CAB SET 
                                NUM_VECES_ASIS = '".$num_vec."'
                      WHERE ID_ASISTENCIA='".$id_asistencia."'
                      AND CURSO_CARGA_ID = '".$curso_carga_id."' 
                      AND FECHA_CLASE = '".$fecha."' ";
                
         DB::connection('oracleaacad')->update($comando);
       
        
    }
    
    public static function deleteAsistencia($id_asistencia){
        $comando = "SELECT MAX(NUM_VECES) as nveces FROM ACAD_ALUM_ASISTENCIA WHERE ID_ASISTENCIA='".$id_asistencia."' ";

        $oQuery = DB::connection('oracleaacad')->select($comando);
        $num_vec=0;
        foreach($oQuery as $row){
            $num_vec = $row->nveces;
        }
        $comando = "DELETE FROM ACAD_ALUM_ASISTENCIA WHERE ID_ASISTENCIA='".$id_asistencia."'  AND NUM_VECES='".$num_vec."'";
        DB::connection('oracleaacad')->delete($comando);
        
        $comando = "SELECT coalesce(MAX(NUM_VECES),'0') as nveces FROM ACAD_ALUM_ASISTENCIA WHERE ID_ASISTENCIA='".$id_asistencia."' ";

        $oQuery = DB::connection('oracleaacad')->select($comando);
        $num_vec=0;
        foreach($oQuery as $row){
            $num_vec = $row->nveces;
        }
        if($num_vec==0){
            $comando = "DELETE FROM ACAD_ALUM_ASIST_CAB WHERE ID_ASISTENCIA='".$id_asistencia."'";
            DB::connection('oracleaacad')->delete($comando);
        }else{
            $comando = " UPDATE ACAD_ALUM_ASIST_CAB SET 
                                NUM_VECES_ASIS = '".$num_vec."'
                      WHERE ID_ASISTENCIA='".$id_asistencia."'";
                
            DB::connection('oracleaacad')->update($comando);
        }
        
    }
    public static function showAsistenciaTrabajador($documento) {
        $sql = "SELECT 
                B.FECHAHORA
                FROM ASIST.personal A, ASIST.ASISTENCIA B
                where A.idpersonal = B.idpersonal
                and A.ndocumento = '".$documento."'
                AND TO_CHAR(B.FECHA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY')
                AND TO_CHAR(B.FECHAHORA,'HH') BETWEEN '06' AND '08' ";
        $oQuery = DB::connection('siscop')->select($sql);
        
        /*$contar = 0;
        foreach($oQuery as $row){
            $contar = $row->nregistro;
        }*/
        return $oQuery;
    }
    
    public static function precioAdmision($sucursal,$programa,$m_estudio,$m_ingreso,$nivel,$anho,$tipo,$nacionalidad,$sucursal_ofiseg,$nro_postulacion,$descuento,$cambio_carrera){
      
   
        //$id_venta = "001-".$anho;
        $id_venta = "001-2020";
        $id_opcion = "";
        $anho = 2021;
  
        $sede = "";
        $sql    =    "SELECT
                    NVL(sede,'LIMA') AS sede
                    FROM ARON.UPEU_OF_ENLACE
                    WHERE ID = '".$sucursal_ofiseg."' ";

        $oQuery = DB::connection('oracleapp')->select($sql);

        foreach ($oQuery as $row){
            $sede = $row->sede;
        }

        //if($sucursal == "0c0e180c1bba42e795752bfa4e74cd2f"){//JULIACA
        if($sede == "JULIACA"){//JULIACA

            if($nivel == "PR"){//PREGRADO                
                if($programa == "8c2e9ae4914b4d38bd3b3451f279e860"){//MEDICINA
                    $id_opcion = "1";
                }else{//OTRAS EAPs

                    /*if($nro_postulacion==1){
                        if($descuento == 0){
                            $id_opcion = "1";
                        }elseif($descuento == 1){
                            $id_opcion = "";
                        }elseif($descuento == 2){
                            $id_opcion = "";
                        }else{
                            $id_opcion = "1";
                        }
                    }else{*/
                        
                    //}
                        //PRESENCIAL
                    if($m_estudio == "a4a73ba863524193babae533b077538d"){
                        $id_opcion = "1";
                    }else /*PROESAD*/if($m_estudio == "13f8edfd0c664c65889f6304c2479832"){
                        $id_opcion = "2";
                    }



                }
            }else{//POSGRADO
                if($tipo == "DOCTO"){
                    $id_opcion = "2";
                }else{
                    $id_opcion = "2";
                }        
            }

            $sql = "SELECT
                decode(id_opcion,'032','1','018','1','127','2') ID,
                '".$sede." - ADM-J-Derecho de Admision & Prospecto' nombre,
                '".$sede." - ADM-J-Derecho de Admision & Prospecto'    GLOSA,
                sum(decode('".$id_opcion."','1',precio,'2',125))PRECIO,
                'Soles' moneda,
                'S/.' simbolo,
                '".$sede."' SEDE
                FROM
                ARON.chullu_opciones
                WHERE id_venta = '".$id_venta."'
                AND id_opcion in (decode('".$id_opcion."','1','032','2','127'),decode('".$id_opcion."','1','018'))
                group by decode(id_opcion,'032','1','018','1','127','2')";

            //}elseif ($sucursal == "f39d11744b7b47ceae0c3cdd1ffd17dd"){//TARAPOTO
        }elseif ($sede == "TARAPOTO"){//TARAPOTO
            //if($nro_postulacion==1){
                /*if($descuento == 0){
                    $id_opcion = "115";
                }elseif($descuento == 1){
                    $id_opcion = "";
                }elseif($descuento == 2){
                    $id_opcion = "";
                }else{*/
                    $id_opcion = "115";
                //}
            /*}else{
                  $id_opcion = "115";
            }*/


            $sql = "SELECT ID_OPCION, NOMBRE,GLOSA, PRECIO,'Soles' MONEDA,'S/.' SIMBOLO,'".$sede."' SEDE
                    FROM ARON.TARA_OPCIONES
                    WHERE ID_VENTA = '".$id_venta."'
                    AND ID_OPCION = '".$id_opcion."'
                    ORDER BY ID_OPCION ";

        }elseif ($sede == "LIMA"){

            if($nivel == "PR"){//PREGRADO                
                if($programa == "8c2e9ae4914b4d38bd3b3451f279e860"){//MEDICINA
                  if($nacionalidad == "NACIONAL"){
                      $id_opcion = "083"; //120 Soles
                      $id_dinamica = 2533;//2289; // 220 Soles
                  }else{
                        if($descuento == 1){
                            $id_opcion = "152"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }elseif($descuento == 2){
                            $id_opcion = "152"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }else{
                            $id_opcion = "470"; //330 Soles
                            $id_dinamica = 2515;//2241;
                        }
                  }

                }else{//OTRAS EAPs

                  if($nacionalidad == "NACIONAL"){
                      if($nro_postulacion==1){
                        if($descuento == 0){
                            $id_opcion = "018"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }elseif($descuento == 1){
                            $id_opcion = "152"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }elseif($descuento == 2){
                            $id_opcion = "151"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }elseif($descuento == 3){
                            $id_opcion = "616"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }elseif($descuento == 5){
                            $id_opcion = "616"; //120 Soles final de campaña
                            $id_dinamica = 2534;//2294;
                        }elseif($descuento == 6){
                            $id_opcion = "728"; //120 Soles ADM -Derecho de Admi - Sue?a en Grande
                            $id_dinamica = 2563;//2415;
                        }elseif($descuento == 7){
                            $id_opcion = "729"; //110 Soles ADM -Derecho de Admi - 50% Dscto
                            $id_dinamica = 2562;//2414;
                        }elseif($descuento == 8){
                            $id_opcion = "729"; //110 Soles ADM -Derecho de Admi - 50% Dscto
                            $id_dinamica = 4722;//2414;
                        }else{
                            $id_opcion = "018"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }
                      } else{
                          $id_opcion = "018"; //120 Soles
                          $id_dinamica = 2514;//2240;
                      }
                  }else{ 
                        if($descuento == 1){
                            $id_opcion = "152"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }elseif($descuento == 2){
                            $id_opcion = "152"; //120 Soles
                            $id_dinamica = 2514;//2240;
                        }elseif($descuento == 5){
                            $id_opcion = "152"; //120 Soles final de campaña
                            $id_dinamica = 2534;//2294;
                        }else{
                            $id_opcion = "470"; //330 Soles
                            $id_dinamica = 2515;//2241;
                        }
                  }

                }
                if($m_ingreso == "df09781afac2cb1c3a2065d64796973a" || $m_ingreso == "5135cd8924e04f2eae9bfbf08dda8897"){
                    $id_opcion = "593"; //200 Soles
                    $id_dinamica = 2516;//2242;
                }
                if($cambio_carrera == "1"){
                    $id_opcion = "460"; //44 Soles
                    $id_dinamica = 2512;//2234;
                }else{
                    if($cambio_carrera == "2"){
                        $id_opcion = "600"; //66 Soles
                        $id_dinamica = 2513;//2239;
                    }
                }
            }else{//POSGRADO
                if($tipo == "DOCTO"){
                    $id_opcion = "256";
                    $id_dinamica = 2530;//2262; //225 Soles
                }else{
                    if($programa == "d65b87d0701d45d0867d6d3f729ad26f"){//MAESTRIA ENFERMERIA
                        $id_opcion = "141"; //320 Soles
                        $id_dinamica = 2529;//2261; //160 Soles
                    }elseif($programa == "cd24692f7f1246febdbad0cf34dc2bbe"){// Seg.Esp. en Estadística Aplicada para Investigación
                        $id_opcion = "617"; //150 Soles
                        $id_dinamica = 2531;//2263; //150 Soles
                    }elseif($programa == "d4fd78f668e34ce0b2a9a6a746e6c0a4"){
                        $id_opcion = "665"; //75 Soles
                        $id_dinamica = 2529;//2261; //160 Soles
                    }elseif($programa == "46f95885ffcf456b957cce78140a213e"){
                        $id_opcion = "255"; //150 Soles
                    }else{//Seg.Esp. en Enfermería
                        $id_opcion = "141"; //320 Soles
                        $id_dinamica = 2532;//2264; // 160 Soles
                    }
                    //082-SALUD
                    //617-ING
                }        
            }
            
            /*$sql = "SELECT ID_OPCION AS ID, NOMBRE,GLOSA, PRECIO,'Soles' MONEDA,'S/.' SIMBOLO,'".$sede."' SEDE
                FROM ARON.UPEU_OPCIONES
                WHERE ID_VENTA = '".$id_venta."'
                --AND UPPER(GLOSA) LIKE '%ADMIS%'
                --AND ID_OPCION NOT IN ('127','130')
                AND ID_OPCION = '".$id_opcion."'
                ORDER BY ID_OPCION ";*/
            
            $sql = "SELECT ID_DINAMICA AS ID,NOMBRE, NOMBRE AS GLOSA, IMPORTE AS PRECIO,'Soles' as MONEDA,'S/.' AS SIMBOLO,'".$sede."' AS SEDE, NVL(IMPORTE_ME,0) AS PRECIO_ME
                    FROM CONTA_DINAMICA
                    WHERE ID_ENTIDAD = 7124
                    AND ID_ANHO = ".$anho."
                    AND ID_DINAMICA = ".$id_dinamica." ";
        }
        if($sede == "LIMA"){
            $oQuery = DB::select($sql);
            //$oQuery = DB::connection('oracleapp')->select($sql);
        }else{
            $oQuery = DB::connection('oracleapp')->select($sql);  
        }

        return $oQuery;

        
    }
    public static function ListStudentSede($id_persona) {
        $sql = "SELECT
                CODIGO_PERSONAL,
                NOE.ALUMN_PLAN(CODIGO_PERSONAL) PLAN,
                NOE.NOMBRE_SECTOR(SUBSTR(NOE.ALUMN_PLAN(CODIGO_PERSONAL),0,5),1) EP,
                SUBSTR(NOE.ALUMN_PLAN(CODIGO_PERSONAL),0,5) SECTOR_ID,
                (CASE WHEN substr(NOE.ALUMN_PLAN(CODIGO_PERSONAL),1,1) =1 THEN '1'
                     when substr(NOE.ALUMN_PLAN(CODIGO_PERSONAL),1,1) =2 THEN '2' 
                     when substr(NOE.ALUMN_PLAN(CODIGO_PERSONAL),1,1) =5 THEN '5'
                     ELSE (CASE WHEN substr(NOE.ALUMN_PLAN(CODIGO_PERSONAL),1,5) IN (select SECTOR_ID from univ_sector WHERE FILIAL=1 AND SUBSTR(SECTOR_ID,1,1)='3') THEN '1' 
                                WHEN substr(NOE.ALUMN_PLAN(CODIGO_PERSONAL),1,5) IN (select SECTOR_ID from univ_sector WHERE FILIAL=2 AND SUBSTR(SECTOR_ID,1,1)='3') THEN '2'
                                WHEN substr(NOE.ALUMN_PLAN(CODIGO_PERSONAL),1,5) IN (select SECTOR_ID from univ_sector WHERE FILIAL=5 AND SUBSTR(SECTOR_ID,1,1)='3') THEN '5' END)
                     END) CAMPUS
                FROM DATOS_PERSONALES
                WHERE CODIGO_PERSONAL = '".$id_persona."' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }
    public static function thesisAdmision($paso,$sector_id){
        $id_opcion = "";
        $tipo = "";
        $sql = "SELECT ID_VENTA 
                FROM ARON.UPEU_MAIN
                WHERE ESTADO = '1' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        foreach ($oQuery as $row){
            $id_venta = $row->id_venta;
        }
        
        $sql = "SELECT UPG_ID,NOMBRE_DOC_EAP,TIPO,
                DECODE(UPG_ID,'1064','1.07.22','1060','1.07.10','1062','1.07.13','1066','1.07.27','1063','1.07.15','1061','1.07.21','1065','1.07.08') NIVEL
                FROM UNIV_SECTOR
                WHERE SECTOR_ID = '".$sector_id."' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        foreach ($oQuery as $row){
            $tipo = $row->tipo;
        }
        if($tipo == "M"){//Maestria
            if($paso == "1"){
                $id_opcion = "291";
            }elseif($paso == "2"){
                $id_opcion = "397";
            }elseif($paso == "3"){
                $id_opcion = "310";
            }elseif($paso == "4"){
                $id_opcion = "399";
            }elseif($paso == "5"){
                $id_opcion = "400";
            }else{
                $id_opcion = "";
            }
            
        }elseif($tipo == "D"){//Doctorado
            if($paso == "1"){
                $id_opcion = "120";
            }elseif($paso == "2"){
                $id_opcion = "211";
            }elseif($paso == "3"){
                $id_opcion = "077";
            }elseif($paso == "4"){
                $id_opcion = "204";
            }elseif($paso == "5"){
                $id_opcion = "135";
            }else{
                $id_opcion = "";
            }
        }else{//Pregado
            
        }
        $sql = "SELECT ID_OPCION AS ID, NOMBRE,GLOSA, PRECIO,'Soles' MONEDA,'S/.' SIMBOLO,'".$tipo."' TIPO
            FROM ARON.UPEU_OPCIONES
            WHERE ID_VENTA = '".$id_venta."'
            AND ID_OPCION = '".$id_opcion."'
            ORDER BY ID_OPCION ";
        $oQuery = DB::connection('oracleapp')->select($sql);    
        return $oQuery;
    }
    public static function thesisAdmisionJuliaca($paso,$sector_id){
        $id_opcion = "";
        $sql = "SELECT ID_VENTA 
                FROM ARON.UPEU_MAIN
                WHERE ESTADO = '1' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        foreach ($oQuery as $row){
            $id_venta = $row->id_venta;
        }
        
        $sql = "SELECT UPG_ID,NOMBRE_DOC_EAP,TIPO,
                DECODE(UPG_ID,'1064','1.07.22','1060','1.07.10','1062','1.07.13','1066','1.07.27','1063','1.07.15','1061','1.07.21','1065','1.07.08') NIVEL
                FROM UNIV_SECTOR
                WHERE SECTOR_ID = '".$sector_id."' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        foreach ($oQuery as $row){
            $tipo = $row->tipo;
        }
        
        if($paso == "1"){
            $id_opcion = "291";
        }elseif($paso == "2"){
            $id_opcion = "397";
        }elseif($paso == "3"){
            $id_opcion = "310";
        }elseif($paso == "4"){
            $id_opcion = "399";
        }elseif($paso == "5"){
            $id_opcion = "400";
        }else{
            $id_opcion = "";
        }
        $sql = "SELECT ID_OPCION AS ID, NOMBRE,GLOSA, PRECIO,'Soles' MONEDA,'S/.' SIMBOLO,'".$tipo."' TIPO
            FROM ARON.UPEU_OPCIONES
            WHERE ID_VENTA = '".$id_venta."'
            AND ID_OPCION = '".$id_opcion."'
            ORDER BY ID_OPCION ";
        $oQuery = DB::connection('oracleapp')->select($sql);    
        return $oQuery;
    }
    public static function thesisAdmisionTPP($paso,$sector_id){
        $id_opcion = "";
        $sql = "SELECT ID_VENTA 
                FROM ARON.TARA_MAIN
                WHERE ESTADO = '1' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        foreach ($oQuery as $row){
            $id_venta = $row->id_venta;
        }
        
        $sql = "SELECT UPG_ID,NOMBRE_DOC_EAP,TIPO,
                DECODE(UPG_ID,'1064','1.07.22','1060','1.07.10','1062','1.07.13','1066','1.07.27','1063','1.07.15','1061','1.07.21','1065','1.07.08') NIVEL
                FROM UNIV_SECTOR
                WHERE SECTOR_ID = '".$sector_id."' ";
        $oQuery = DB::connection('oracleapp')->select($sql);
        foreach ($oQuery as $row){
            $tipo = $row->tipo;
        }
        
        if($paso == "1"){
            $id_opcion = "291";
        }elseif($paso == "2"){
            $id_opcion = "397";
        }elseif($paso == "3"){
            $id_opcion = "310";
        }elseif($paso == "4"){
            $id_opcion = "399";
        }elseif($paso == "5"){
            $id_opcion = "400";
        }else{
            $id_opcion = "";
        }
        $sql = "SELECT ID_OPCION AS ID, NOMBRE,GLOSA, PRECIO,'Soles' MONEDA,'S/.' SIMBOLO,'".$tipo."' TIPO
            FROM ARON.UPEU_OPCIONES
            WHERE ID_VENTA = '".$id_venta."'
            AND ID_OPCION = '".$id_opcion."'
            ORDER BY ID_OPCION ";
        $oQuery = DB::connection('oracleapp')->select($sql);    
        return $oQuery;
    }
    public static function listEscuelas($sede,$ciclo) {
        $sql = "SELECT 
                SECTOR_ID as ID_ESCUELA,NOMBRE_SECTOR AS NOMBRE_ESCUELA
                FROM noe.univ_sector WHERE sector_id LIKE '".$sede."%'
                AND substr(sector_id,1,3)<>'106'
                AND sector_id in ( SELECT sector_id FROM noe.acad_carga_Academica WHERE carga_id='".$ciclo."')
                ORDER BY SECTOR_ID,NOMBRE_SECTOR ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function listStudentsAssistControl($ciclo,$id_escuela,$fecha,$de,$a) {
        $sql = "SELECT * FROM (
                                SELECT  ROWNUM AS CONT, 
                                        A.CODIGO_PERSONAL PER_ID, 
                                        A.CODIGO_CONTRATO AS CICLO,
                                        TO_CHAR(A.FECHA,'YYYY-MM-DD') FECHA,
                                        conv_char(replace(CONVERT(trim(B.DATO_NOMBRES),'US7ASCII','WE8ISO8859P1'),'?','n')) NOMBRES,
                                        conv_char(replace(CONVERT(trim(B.DATO_APELLIDO_PATERNO),'US7ASCII','WE8ISO8859P1'),'?','n')) PATERNO,
                                        conv_char(replace(CONVERT(trim(B.DATO_APELLIDO_MATERNO),'US7ASCII','WE8ISO8859P1'),'?','n')) MATERNO,
                                        B.DOCUMENTOS_CODUNIV CODIGO,
                                        B.DOCUMENTOS_DNI DNI,
                                        'http://images.upeu.edu.pe/fotodb/'||decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem'))||'.jpg' url_foto,
                                        conv_char(replace(CONVERT(trim(SUBSTR(ALUMNO_EAP(A.codigo_personal),7)),'US7ASCII','WE8ISO8859P1'),'?','n')) ep, 
                                        decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem')) as foto,
                                        A.FECHA AS FECHA_HORA_ASISTENCIA  
                                FROM NOE.ALUMNOS_CULTURA_ASISTENCIA A, NOE.DATOS_PERSONALES B
                                WHERE A.CODIGO_PERSONAL = B.CODIGO_PERSONAL
                                AND A.CODIGO_CONTRATO = '".$ciclo."'
                                AND SUBSTR(NOE.ALUMN_PLAN(A.CODIGO_PERSONAL),1,5) = '".$id_escuela."'
                                AND TO_CHAR(A.FECHA,'DD/MM/YYYY') = '".$fecha."'
                                AND ROWNUM <= ".$a."
                ) WHERE CONT >= $de
                ORDER BY PATERNO,MATERNO,NOMBRES ";
        
        /*
        $oQuery = DB::connection('oracleaacad')->table('NOE.ALUMNOS_CULTURA_ASISTENCIA as A')
                ->join('NOE.DATOS_PERSONALES AS B', 'A.CODIGO_PERSONAL', '=', 'B.CODIGO_PERSONAL')
                ->select('A.CODIGO_PERSONAL AS PER_ID','A.CODIGO_CONTRATO AS CICLO',
                        DB::raw("TO_CHAR(A.FECHA,'YYYY-MM-DD') AS FECHA"),
                        DB::raw("conv_char(replace(CONVERT(trim(B.DATO_NOMBRES),'US7ASCII','WE8ISO8859P1'),'?','n')) AS NOMBRES"),
                        DB::raw("conv_char(replace(CONVERT(trim(B.DATO_APELLIDO_PATERNO),'US7ASCII','WE8ISO8859P1'),'?','n')) as PATERNO"),
                        DB::raw("conv_char(replace(CONVERT(trim(B.DATO_APELLIDO_MATERNO),'US7ASCII','WE8ISO8859P1'),'?','n')) as MATERNO"),
                        DB::raw('B.DOCUMENTOS_CODUNIV as CODIGO'),
                        DB::raw('B.DOCUMENTOS_DNI as DNI'),
                        DB::raw("'http://images.upeu.edu.pe/fotodb/'||decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem'))||'.jpg' as url_foto"),
                        DB::raw("conv_char(replace(CONVERT(trim(SUBSTR(ALUMNO_EAP(A.codigo_personal),7)),'US7ASCII','WE8ISO8859P1'),'?','n')) as ep"),
                        DB::raw("decode(dato_sexo,'M',nvl(foto,'sinfoto_man'),nvl(foto,'sinfoto_fem')) as foto"),
                        DB::raw('A.FECHA AS FECHA_HORA_ASISTENCIA')
                        )
                ->where('A.CODIGO_CONTRATO', "'".$ciclo."'")
                ->where(DB::raw('SUBSTR(NOE.ALUMN_PLAN(A.CODIGO_PERSONAL),1,5)'), "'".$id_escuela."'")
                ->where(DB::raw("TO_CHAR(A.FECHA,'DD/MM/YYYY')"), "'".$fecha."'")
                ->orderby('PATERNO');
                //->paginate(2);*/
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function showStudentsAssistControl($per_id,$ciclo,$id_tipo) {
        $sql = "SELECT 
                CODIGO_PERSONAL
                FROM NOE.ALUMNOS_CULTURA_ASISTENCIA
                WHERE CODIGO_PERSONAL = '".$per_id."'
                AND CODIGO_CONTRATO = '".$ciclo."'
                AND TO_CHAR(FECHA,'DD/MM/YYYY') = TO_CHAR(SYSDATE,'DD/MM/YYYY') 
                AND ID_TIPO = ".$id_tipo." ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function addStudentsAssistControl($ciclo,$per_id,$id_tipo) {
        $sql = "INSERT INTO NOE.ALUMNOS_CULTURA_ASISTENCIA(CODIGO_CONTRATO,CODIGO_PERSONAL,FECHA,ID_TIPO) VALUES('".$ciclo."','".$per_id."',sysdate,'".$id_tipo."') ";    
        DB::connection('oracleapp')->insert($sql);
    }
    public static function deleteStudentsAssistControl($per_id,$ciclo,$fecha) {
        $sql = "DELETE NOE.ALUMNOS_CULTURA_ASISTENCIA WHERE CODIGO_CONTRATO = '".$ciclo."' AND CODIGO_PERSONAL = '".$per_id."'  AND TO_CHAR(FECHA,'YYYY-MM-DD') = '".$fecha."' ";    
        DB::connection('oracleapp')->insert($sql);
    }
    public static function showDNIPerson($dni) {
        $sql = "SELECT 
                CODIGO_PERSONAL AS PER_ID, DATO_APELLIDO_PATERNO AS PATERNO,DATO_APELLIDO_MATERNO AS MATERNO, DATO_NOMBRES AS NOMBRES,DOCUMENTOS_DNI AS DNI
                FROM DATOS_PERSONALES
                WHERE DOCUMENTOS_DNI = '".$dni."' ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function listTypesAssistance() {
        $sql = "SELECT ID_TIPO,NOMBRE 
                FROM NOE.TIPO_ASISTENCIA
                WHERE ESTADO = '1' ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function showMyAssists($per_id,$curso_id,$fecha) {
        $sql = "SELECT 
                DECODE(COUNT(B.CODIGO_PERSONAL),0,'0','1') AS ASISTIO
                FROM NOE.ACAD_ALUM_ASIST_CAB A, NOE.ACAD_ALUM_ASISTENCIA B
                WHERE A.ID_ASISTENCIA = B.ID_ASISTENCIA
                AND B.CODIGO_PERSONAL = '".$per_id."'
                AND CURSO_CARGA_ID = '".$curso_id."'
                AND TO_CHAR(FECHA_CLASE,'YYYY-MM-DD') = '".$fecha."' ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function showStudentsEPG($nro_documento) {
        $sql = "SELECT 
                COUNT(A.CODIGO_PERSONAL) CANT 
                FROM NOE.ALUMNO_CONTRATO_UPG A, DATOS_PERSONALES B
                WHERE A.CODIGO_PERSONAL = B.CODIGO_PERSONAL
                AND (B.DOCUMENTOS_DNI LIKE '%".$nro_documento."%' OR DOCUMENTOS_CE LIKE '%".$nro_documento."%' OR PASAPORTE LIKE '%".$nro_documento."%')
                AND A.ESTADO = '1' ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function showStudentsSemi($nro_documento) {
        $sql = "SELECT 
                COUNT(A.CODIGO_PERSONAL) CANT 
                FROM NOE.ALUMNO_CONTRATO_FILIAL A, DATOS_PERSONALES B
                WHERE A.CODIGO_PERSONAL = B.CODIGO_PERSONAL
                AND A.CODIGO_CONTRATO = '2019-1'
                AND (B.DOCUMENTOS_DNI LIKE '%".$nro_documento."%' OR DOCUMENTOS_CE LIKE '%".$nro_documento."%' OR PASAPORTE LIKE '%".$nro_documento."%')
                AND A.ESTADO = '1' ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function showStudents($nro_documento) {
        $sql = "SELECT NOE.nombre_area(substr(plan_id,0,1)) NOMBRE,SUBSTR(NOE.ALUMNO_EAP_ID(dp.CODIGO_PERSONAL),1,1) SEDE
                FROM NOE.alumno_contrato_upeu ac, datos_personales dp 
                WHERE ac.codigo_personal=dp.codigo_personal
                AND CODIGO_CONTRATO = '2019-1'
                AND (dp.DOCUMENTOS_DNI LIKE '%".$nro_documento."%' OR dp.DOCUMENTOS_CE LIKE '%".$nro_documento."%' OR dp.PASAPORTE LIKE '%".$nro_documento."%')
                AND codigo_contrato=(SELECT max(codigo_contrato) FROM NOE.alumno_contrato_upeu acd WHERE acd.codigo_personal=ac.codigo_personal) ";
        $oQuery = DB::connection('oracleaacad')->select($sql);
        return $oQuery;
    }
    public static function addRegistroDatos($nombre,$paterno,$materno,$tipo,$doc,$pais) {
        $codigo = "";
        $sql = "UPDATE ARON.UPEU_RECIBO SET NUMDEP = ARON.FC_REGISTRA('".$nombre."','".$paterno."','".$materno."','".$tipo."','".$doc."','".$pais."') 
                WHERE ID_USER = '2001RO20010709094550' ";
        DB::connection('oracleapp')->update($sql);
        
        $sql = "select NUMDEP as CODIGO from ARON.UPEU_RECIBO WHERE ID_USER = '2001RO20010709094550' ";
        $oQuery = DB::connection('oracleapp')->select($sql);   
        foreach ($oQuery as $key => $item){
            $codigo = $item->codigo;                
        }
        
        $sql = "SELECT codigo_personal as per_id,documentos_coduniv as codigo FROM DATOS_PERSONALES WHERE DOCUMENTOS_CODUNIV = '".$codigo."' ";    
        $oQuery = DB::connection('oracleapp')->select($sql);
        return $oQuery;
    }

    public static function saldoAlumnoCU($alu_cod_modular){
        $sql="select alu_id from col_union.cu_alumno where alu_cod_modular='".$alu_cod_modular."'";
        $oQuery = DB::connection('oracleapp')->select($sql);
        $alu_id='';
        foreach ($oQuery as $row) {
            $alu_id=$row->alu_id;
        }

        $sql="select  
        cfg_clave,
        cfg_valor 
        from col_union.ma_configuracion 
        where cfg_clave in('ID_VENTA','ID_AREA') 
        and ins_id='001'";
        $oQuery = DB::connection('oracleapp')->select($sql);
        $id_venta='';
        $id_area='';
        foreach($oQuery as $row){
            if($row->cfg_clave=="ID_VENTA"){
                $id_venta=$row->cfg_valor;
            }else{
                $id_area=$row->cfg_valor;
            }
        }

        ///
        $sql="Select id_venta  from aron.cu_main where estado='1'";
        $oQuery = DB::connection('oracleapp')->select($sql);
        foreach($oQuery as $row){
            $id_venta = $row->id_venta;
        }
        //
        $sql="Select x.id_personal as id_alumno,
        coalesce(sum(x.importe),0) saldo
        from aron.cu_mov_doc  x
        where x.id_venta = '".$id_venta."'
        and x.id_area = '".$id_area."'
        and x.id_personal='".$alu_id."'
        group by x.id_personal";

        $oQuery = DB::connection('oracleapp')->select($sql);

        $return =['nerror'=>1,'mensaje'=>'No hay datos','data'=>[]];
        if(count($oQuery)>0){
            foreach($oQuery as $row){
                $datos=['id_alumno'=>$row->id_alumno,'saldo'=>$row->saldo];
                $return =['nerror'=>0,'mensaje'=>'','data' => $datos];
            }
        }
        return $return;
    }
    public static function depSalesEda($request)
    {
        $id_dinamica = $request->id_dinamica;
        $operacion = $request->operacion;
        $importe = $request->importe;
        $cod_tarjeta = $request->cod_tarjeta;
        $id_tipodocumento = $request->id_tipodocumento;
        $num_documento = $request->num_documento;
        $paterno = $request->paterno;
        $materno = $request->materno;
        $nombre = $request->nombre;
        $sexo = $request->sexo;
        $correo = $request->correo;
        $ip = $request->ip;

        $error = 0;
        $id = '';
        $msg_error  = '';

        for($x=1 ; $x <= 200 ; $x++){
            $msg_error .= "0";
        }
        for($xy=1 ; $xy <= 20 ; $xy++){
            $id .= "0";
        }

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_PAY.SP_DEPOSITO_VENTA_EDA(
            :P_ID_DINAMICA,
            :P_OPERACION,
            :P_IMPORTE,
            :P_COD_TARJETA,
            :P_ID_TIPODOCUMENTO,
            :P_NUM_DOCUMENTO,
            :P_PATERNO,
            :P_MATERNO,
            :P_NOMBRE,
            :P_SEXO,
            :P_CORREO,
            :P_IP,
            :P_ID,
            :P_ERROR,
            :P_MSGERROR
            );
            END;");
            $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
            $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
            $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
            $stmt->bindParam(':P_COD_TARJETA', $cod_tarjeta, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_TIPODOCUMENTO', $id_tipodocumento, PDO::PARAM_INT);
            $stmt->bindParam(':P_NUM_DOCUMENTO', $num_documento, PDO::PARAM_STR);
            $stmt->bindParam(':P_PATERNO', $paterno, PDO::PARAM_STR);
            $stmt->bindParam(':P_MATERNO', $materno, PDO::PARAM_STR);
            $stmt->bindParam(':P_NOMBRE', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':P_SEXO', $sexo, PDO::PARAM_STR);
            $stmt->bindParam(':P_CORREO', $correo, PDO::PARAM_STR);
            $stmt->bindParam(':P_IP', $ip, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID', $id, PDO::PARAM_STR);
            $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();

        if ($error == 0) {
            $response = [
                'success' => true,
                'message' => $msg_error,
                'data' => $id
            ];
        } else {
            $response = [
                'success' => false,
                'message' => $msg_error,
                'data' => $id
            ];
        }
        return $response;
    }
}

