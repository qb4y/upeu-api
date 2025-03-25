<?php
/**
 * Created by PhpStorm.
 * User: amelio
 * Date: 25/05/$second_year
 * Time: 4:12 PM
 */

namespace App\Http\Data;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;



class MobileData extends Controller
{ 
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }



    public static function login($procedureName,$username,$password)
    {
        $bindings = [
            'p_username'  => $username,
            'p_password'  => $password
        ];

        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }

    public static function superProc($procedureName,$bindings)
    {
        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }

    public static function proc($procedureName,$entity,$year,$month,$cta_cte)
    {
        $bindings = [
            'p_id_entidad'  => $entity,
            'p_id_anho'  => $year,
            'p_id_mes'  => $month,
            'p_id_cta_cte'  => $cta_cte
        ];

        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }
    public static function st_proc($procedureName,$entity,$year,$month,$id_persona,$cta_cte)
    {
        $bindings = [
            'p_id_entidad'  => $entity,
            'p_id_anho'  => $year,
            'p_id_mes'  => $month,
            'p_id_persona'  => $id_persona,
            'p_id_cta_cte'  => $cta_cte
        ];

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
    public static function list_personal($entity, $year, $month)
    {
        $query = "
            select  distinct e.id_persona as id, 
                    e.NUM_DOCUMENTO as doc_number, 
                    e.NOM_PERSONA as name, 
                    e.id_entidad as entity
            from VW_APS_PLANILLA p, vw_aps_empleado e
            where p.id_entidad = e.id_entidad
            and p.id_contrato = e.id_contrato
            and p.id_persona = e.id_persona
            and p.id_entidad = $entity
            and p.id_anho = $year
            and p.id_mes = $month
            order by name 
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function list_personal_year($entity, $year)
    {
        $query = "
            select  distinct e.id_persona as id, 
                    e.NUM_DOCUMENTO as doc_number, 
                    'http://intranet.educacionadventista.org.pe/fotos_upn/'||e.NUM_DOCUMENTO||'.png' AS foto_url,
                    e.NOM_PERSONA as name, 
                    e.id_entidad as entity
            from VW_APS_PLANILLA p, vw_aps_empleado e
            where p.id_entidad = e.id_entidad
            and p.id_contrato = e.id_contrato
            and p.id_persona = e.id_persona
            and p.id_entidad = $entity
            and p.id_anho = $year
            order by name 
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function list_personal_year_search($entity, $year, $month, $search, $depto)
    {
      $sqlSearch = '';
      $addMes = '';
      $addDepto = '';
      if($search !==null and  $search !== '') {
        $sqlSearch = " and (UPPER(e.NOM_PERSONA) like UPPER('%" . $search . "%') OR e.NUM_DOCUMENTO like '%" . $search . "%')";
      }
      if($month!==null and $month !=='*'){
        $addMes =  " and  ID_MES = " . $month;
      }
      if($depto!==null and $depto !=='*'){
        $addDepto =  " and  p.ID_DEPTO = " . $depto;
      }
        $query = "SELECT distinct e.id_persona as ID_PERSONA, 
                    e.NUM_DOCUMENTO as doc_number, 
                    e.NOM_PERSONA as NOM_PERSONA, 
                    e.id_entidad as entity,
                    pn.fec_nacimiento as fecha_nac,
                    trunc(months_between(sysdate,pn.fec_nacimiento)/12) as edad, 
                    pn.tipo_estado_civil as estado_civil,
                    pn.tipo_pais as pais,
                    pn.tipo_sexo as sexo,
                    pn.telefono as telefono,
                    pn.correo_inst as email,
                    pn.direccion as direccion
            from VW_APS_PLANILLA p 
            INNER JOIN vw_aps_empleado e ON (p.id_entidad = e.id_entidad and p.id_contrato = e.id_contrato and p.id_persona = e.id_persona)
            INNER JOIN moises.vw_persona_natural_full pn ON (pn.id_persona = p.id_persona)
            where p.id_entidad = $entity
            and p.id_anho = $year
            $addMes
            $addDepto
            $sqlSearch
            order by e.NOM_PERSONA 
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    } 
    public static function list_personal_directory($entity)
    {
        $query = "
            select e.id_entidad, e.id_persona, e.NOM_PERSONA, e.PATERNO, e.MATERNO, e.NOMBRE, c.NOMBRE as entidad,e.NUM_DOCUMENTO as doc_number,
        ( select p.nom_cargo from APS_PLANILLA p
          where p.id_persona = e.id_persona
          and p.ID_ENTIDAD = e.id_entidad
          and p.id_contrato = e.id_contrato
          and to_date (p.id_anho||lpad(p.id_mes,2,'0'),'yyyymm') = (
          select max(to_date(pp.id_anho||lpad(pp.id_mes,2,'0'),'yyyymm')) from APS_PLANILLA pp
          where pp.id_persona = p.id_persona
          and pp.ID_ENTIDAD = p.id_entidad
          and pp.id_contrato = p.id_contrato
          )
        ) as nom_cargo,
        (select max(num_telefono) from moises.persona_telefono
          where id_persona = e.id_persona
        ) as telefono,
        (select max(DIRECCION) from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 1
        ) as email,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 5
        ) as facebook,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 4
        ) as twitter
      from vw_aps_empleado e inner join conta_entidad c on e.id_entidad = c.id_entidad
      where e.id_entidad = $entity
      and fec_termino is null
      order by e.nom_persona
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function list_personal_directory_search($text)
    {
        $query = "
            select e.id_entidad, e.id_persona, e.NOM_PERSONA, e.PATERNO, e.MATERNO, e.NOMBRE, c.NOMBRE as entidad,e.NUM_DOCUMENTO as doc_number,
        ( select p.nom_cargo from APS_PLANILLA p
          where p.id_persona = e.id_persona
          and p.ID_ENTIDAD = e.id_entidad
          and p.id_contrato = e.id_contrato
          and to_date (p.id_anho||lpad(p.id_mes,2,'0'),'yyyymm') = (
          select max(to_date(pp.id_anho||lpad(pp.id_mes,2,'0'),'yyyymm')) from APS_PLANILLA pp
          where pp.id_persona = p.id_persona
          and pp.ID_ENTIDAD = p.id_entidad
          and pp.id_contrato = p.id_contrato
          )
        ) as nom_cargo,
        (select max(num_telefono) from moises.persona_telefono
          where id_persona = e.id_persona
        ) as telefono,
        (select max(DIRECCION) from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 1
        ) as email,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 5
        ) as facebook,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 4
        ) as twitter
      from vw_aps_empleado e inner join conta_entidad c on e.id_entidad = c.id_entidad
      where fec_termino is null
      and upper(e.PATERNO||e.MATERNO||e.NOMBRE||e.PATERNO||e.MATERNO||e.NOMBRE||e.PATERNO||e.MATERNO||e.NOMBRE) like upper('%'||replace('$text',' ','%')||'%') 
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function list_personal_profile_worker_item($entity)
    {
        $query = "
            select e.id_persona, e.NOM_PERSONA, e.PATERNO, e.MATERNO, e.NOMBRE, c.NOMBRE as entidad,e.NUM_DOCUMENTO as doc_number,
        ( select p.nom_cargo from APS_PLANILLA p
          where p.id_persona = e.id_persona
          and p.ID_ENTIDAD = e.id_entidad
          and p.id_contrato = e.id_contrato
          and to_date (p.id_anho||lpad(p.id_mes,2,'0'),'yyyymm') = (
          select max(to_date(pp.id_anho||lpad(pp.id_mes,2,'0'),'yyyymm')) from APS_PLANILLA pp
          where pp.id_persona = p.id_persona
          and pp.ID_ENTIDAD = p.id_entidad
          and pp.id_contrato = p.id_contrato
          )
        ) as nom_cargo,
        (select max(num_telefono) from moises.persona_telefono
          where id_persona = e.id_persona
        ) as telefono,
        (select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 1
        ) as email,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 5
        ) as facebook,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 4
        ) as twitter
      from vw_aps_empleado e inner join conta_entidad c on e.id_entidad = c.id_entidad
      where e.id_entidad = $entity
      and fec_termino is null
      order by e.nom_persona
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function list_personal_profile_worker($entity)
    {
        $query = "
            select  e.id_persona, e.NOM_PERSONA, e.PATERNO, e.MATERNO, e.NOMBRE, c.NOMBRE as entidad,e.NUM_DOCUMENTO as doc_number,
        ( select p.nom_cargo from APS_PLANILLA p
          where p.id_persona = e.id_persona
          and p.ID_ENTIDAD = e.id_entidad
          and p.id_contrato = e.id_contrato
          and to_date (p.id_anho||lpad(p.id_mes,2,'0'),'yyyymm') = (
          select max(to_date(pp.id_anho||lpad(pp.id_mes,2,'0'),'yyyymm')) from APS_PLANILLA pp
          where pp.id_persona = p.id_persona
          and pp.ID_ENTIDAD = p.id_entidad
          and pp.id_contrato = p.id_contrato
          )
        ) as nom_cargo,
        (select max(num_telefono) from moises.persona_telefono
          where id_persona = e.id_persona
        ) as telefono,
        (select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 1
        ) as email,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 5
        ) as facebook,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 4
        ) as twitter
      from vw_aps_empleado e inner join conta_entidad c on e.id_entidad = c.id_entidad
      where e.id_entidad = $entity
      and fec_termino is null
      order by e.nom_persona
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function list_personal_profile_worker_search($text)
    {
        $query = "
            select  e.NOM_PERSONA, e.PATERNO, e.MATERNO, e.NOMBRE, c.NOMBRE as entidad,e.NUM_DOCUMENTO as doc_number,
        ( select p.nom_cargo from APS_PLANILLA p
          where p.id_persona = e.id_persona
          and p.ID_ENTIDAD = e.id_entidad
          and p.id_contrato = e.id_contrato
          and to_date (p.id_anho||lpad(p.id_mes,2,'0'),'yyyymm') = (
          select max(to_date(pp.id_anho||lpad(pp.id_mes,2,'0'),'yyyymm')) from APS_PLANILLA pp
          where pp.id_persona = p.id_persona
          and pp.ID_ENTIDAD = p.id_entidad
          and pp.id_contrato = p.id_contrato
          )
        ) as nom_cargo,
        (select max(num_telefono) from moises.persona_telefono
          where id_persona = e.id_persona
        ) as telefono,
        (select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 1
        ) as email,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 5
        ) as facebook,
        (
        select DIRECCION from moises.persona_virtual
          where id_persona = e.id_persona
          and ID_TIPOVIRTUAL = 4
        ) as twitter
      from vw_aps_empleado e inner join conta_entidad c on e.id_entidad = c.id_entidad
      where fec_termino is null
      and upper(e.PATERNO||e.MATERNO||e.NOMBRE||e.PATERNO||e.MATERNO||e.NOMBRE||e.PATERNO||e.MATERNO||e.NOMBRE) like upper('%'||replace('$text',' ','%')||'%') 
            ";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    
    
    public static function travels_detail($year, $month, $entity, $currentAccounts)
    {

        $query = "select
                    to_char(di.FEC_ASIENTO,'dd/mm/yy') AS Fecha,
                    di.COMENTARIO AS COMENTARIO, 
                    di.COS_VALOR AS VALOR
                from VW_CONTA_DIARIO di
                where di.ID_ENTIDAD = $entity
                and di.ID_ANHO = $year
                and di.ID_MES <= $month
                and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
                and di.ID_CTACTE = '$currentAccounts'";
        
        $oQuery = DB::select($query);

        return $oQuery;
    }

    public static function travels_detail_total($year, $month, $entity, $currentAccounts)
    {

        $query = "SELECT
                    sum(di.COS_VALOR) AS TOTAL
                from VW_CONTA_DIARIO di
                where di.ID_ENTIDAD = $entity
                and di.ID_ANHO = $year
                and di.ID_MES <= $month
                and di.ID_CUENTAAASI  in (4111022,4111023,4111070,4111071,4111073,4113023,4121029)
                and di.ID_CTACTE = '$currentAccounts'
                ";
        
        $oQuery = DB::select($query);

        return $oQuery;
    }
public static function dep_data($year, $month, $entity, $id_persona)
    {

        $query = "Select
                X.Dpto as id,
                ed.NOMBRE as name,
                ABS(sum(X.Saldo_Inicial)) saldo_inicial,
                ABS(sum(X.Previsto)) ppto,
                ABS(sum(X.Ingreso)) ingreso,
                ABS(sum(X.Realizado)) realizado,
                ABS(sum(X.Saldo_Inicial)) +ABS(sum(X.Previsto)) +ABS(sum(X.Ingreso))  as total_ppto,
                ABS(sum(X.Saldo_Inicial)) +ABS(sum(X.Previsto)) +ABS(sum(X.Ingreso)) - ABS(sum(X.Realizado)) as saldo,
                round((ABS(sum(X.Realizado))/
                decode((ABS(sum(X.Saldo_Inicial))+ABS(sum(X.Previsto))+ABS(sum(X.Ingreso))), 0 ,1,(ABS(sum(X.Saldo_Inicial))+ABS(sum(X.Previsto))+ABS(sum(X.Ingreso))))
                )*100,2) porc
            from (
            Select
                ID_DEPTO Dpto,
                case when ID_CUENTAAASI = '2317005' and ID_DEPTO = '910111' then sum(COS_VALOR) else sum(COS_VALOR) end Saldo_Inicial,
                0 Previsto,
                0 Ingreso,
                0 Realizado
            from VW_CONTA_DIARIO 
            where ID_ENTIDAD = $entity
            and ID_DEPTO in (select id_depto from CONTA_ENTIDAD_DEPTO_RESP where id_persona = $id_persona)
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and ID_CUENTAAASI in ('2317001','2317005')
            group by ID_DEPTO,ID_ENTIDAD,ID_CUENTAAASI
            UNION
            select
                    ID_DEPTO Dpto,
                    0 Saldo_Inicial,
                    sum(COS_VALOR) Previsto,
                    0 Ingreso,
                    0 Realizado
            from VW_CONTA_PRESUPUESTO
            where ID_ENTIDAD = $entity
            and ID_DEPTO in (select id_depto from CONTA_ENTIDAD_DEPTO_RESP where id_persona = $id_persona)
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and ID_CUENTAAASI like '6%'
            group by ID_DEPTO,ID_ENTIDAD
            Union
            select
                ID_DEPTO Dpto,
                0 Saldo_Inicial,
                0 Previsto,
                sum(COS_VALOR) Ingreso,
                0 Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_DEPTO in (select id_depto from CONTA_ENTIDAD_DEPTO_RESP where id_persona = $id_persona)
            and ID_ANHO = $year
            and ID_MES between 1 and 12
            and ID_CUENTAAASI like '3%'
            and ID_CUENTAAASI not like '3196%'
            group by ID_DEPTO,ID_ENTIDAD
            UNION
            select
                ID_DEPTO Dpto,
                0 Saldo_Inicial,
                0 Previsto,
                0 Ingreso,
                sum(COS_VALOR) Realizado
            from VW_CONTA_DIARIO
            where ID_ENTIDAD = $entity
            and ID_DEPTO in (select id_depto from CONTA_ENTIDAD_DEPTO_RESP where id_persona = $id_persona)
            and ID_ANHO = $year
            and (ID_CUENTAAASI like '3%' OR ID_CUENTAAASI like '4%')
            group by ID_DEPTO,ID_ENTIDAD
            ) X
        INNER JOIN Conta_Entidad_Depto ed ON 
        X.Dpto = ed.ID_DEPTO
        where ed.ID_ENTIDAD = $entity
        group by X.Dpto,ed.NOMBRE
        order by ed.NOMBRE";
        
        $oQuery = DB::select($query);

        return $oQuery;
    }    
public static function dep_data_detail($year, $month, $entity, $departamento)
    {

        $query = "SELECT
                    to_char(FEC_ASIENTO,'dd/mm/yy') AS FECHA,
                    COMENTARIO,
                    COS_VALOR as valor
                from VW_CONTA_DIARIO
                where ID_ENTIDAD = $entity
                and ID_ANHO = $year
                and ID_DEPTO in ('$departamento')
                and (ID_CUENTAAASI like '3%' OR ID_CUENTAAASI like '4%')";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function dep_data_detail_total($year, $month, $entity, $departamento)
    {

        $query = "SELECT
                    sum(di.COS_VALOR) AS total
                from VW_CONTA_DIARIO di
                where di.ID_ENTIDAD = $entity
                and di.ID_ANHO = $year
                and di.ID_DEPTO in ('$departamento')
                and (di.ID_CUENTAAASI like '3%' OR di.ID_CUENTAAASI like '4%')
                ";
        
        $oQuery = DB::select($query);

        return $oQuery;
    }    
public static function st_data_details($year, $month, $entity, $id_cuentaaasi,$cta_cte)
    {

        $query = "SELECT
                    to_char(FEC_ASIENTO,'dd/mm/yy') AS FECHA,
                    COMENTARIO,
                    COS_VALOR as valor
                from VW_CONTA_DIARIO
                where ID_ENTIDAD = $entity
                and ID_CUENTAAASI = $id_cuentaaasi
                and ID_CTACTE = $cta_cte
                and ID_ANHO = $year
                and ID_MES <= $month
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function st_proc_data_salary($procedureName,$year, $month, $entity, $id_persona, $tipo)
    {
        $bindings = [
            'p_id_entidad'  => $entity,
            'p_id_anho'  => $year,
            'p_id_mes'  => $month,
            'p_id_persona'  => $id_persona,
            'p_tipo'  => $tipo
        ];

        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }  
    public static function st_proc_data_salary_desc_cta($procedureName,$year, $month, $entity, $id_ctacte, $id_cuentaaasi)
    {
        $bindings = [
            'p_id_entidad'  => $entity,
            'p_id_anho'  => $year,
            'p_id_mes'  => $month,
            'p_id_ctacte'  => $id_ctacte,
            'p_id_cuentaaasi'  => $id_cuentaaasi
        ];

        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }  

    // agregado por Vitmar Aliaga
    public static function st_proc_data_salary_cta($procedureName,$year, $month, $entity, $id_persona,$tipo, $id_ctacte, $id_cuentaaasi, $dc)
    {
        $bindings = [
            'p_id_entidad'  => $entity,
            'p_id_anho'  => $year,
            'p_id_mes'  => $month,
            'p_id_persona'  => $id_persona,
            'p_tipo'  => $tipo,
            'p_id_ctacte'  => $id_ctacte,
            'p_id_cuentaaasi'  => $id_cuentaaasi,
            'p_dc'  => $dc
        ];

        $result = DB::executeProcedureWithCursor($procedureName, $bindings);
        return $result;
    }  
    
    public static function st_data_salary_ingresos($year, $month, $entity, $id_persona)
    {

        $query = "select 
                p.fec_pago as fecha,a.NOMBRE as comentario, p.COS_VALOR as valor
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                and p.ID_ENTIDAD=$entity
                and p.ID_ANHO=$year
                and p.ID_MES= $month
                and p.ID_PERSONA=$id_persona
                and a.ID_TIPOCONCEPTOAPS not in (100,200)
                and (
                  P.ID_CONCEPTOAPS like '10%'
                  or P.ID_CONCEPTOAPS like '11%'
                  or P.ID_CONCEPTOAPS like '12%'
                  or P.ID_CONCEPTOAPS like '13%'
                  or P.ID_CONCEPTOAPS like '14%'
                  or P.ID_CONCEPTOAPS like '2%'
                  or P.ID_CONCEPTOAPS like '3%'
                  or P.ID_CONCEPTOAPS like '7%'
                )
                and not P.ID_CONCEPTOAPS like '7600%'
                --AND P.ID_CONCEPTOAPS IN(1000,1079,1126,1212,1145,1530,1532,3000,7030)
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    
    public static function st_data_salary_ingresos_total($year, $month, $entity, $id_persona)
    {
        $query = "select 
                nvl(sum(p.COS_VALOR),0) as total
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                and p.ID_ENTIDAD=$entity
                and p.ID_ANHO=$year
                and p.ID_MES= $month
                and p.ID_PERSONA=$id_persona
                and (
                  P.ID_CONCEPTOAPS like '10%'
                  or P.ID_CONCEPTOAPS like '11%'
                  or P.ID_CONCEPTOAPS like '12%'
                  or P.ID_CONCEPTOAPS like '13%'
                  or P.ID_CONCEPTOAPS like '14%'
                  or P.ID_CONCEPTOAPS like '2%'
                  or P.ID_CONCEPTOAPS like '3%'
                  or P.ID_CONCEPTOAPS like '7%'
                )
                and not P.ID_CONCEPTOAPS like '7600%'
                --AND P.ID_CONCEPTOAPS IN(1000,1079,1126,1212,1145,1530,1532,3000,7030)
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    

    public static function st_data_salary_ingresos_tipo($year, $month, $entity, $id_persona,$tipo)
    {
        $query = "select 
                p.fec_pago as fecha,a.NOMBRE as comentario, p.COS_VALOR as valor
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                and p.ID_ENTIDAD=$entity
                and p.ID_ANHO=$year
                and p.ID_MES= $month
                and p.ID_PERSONA=$id_persona
                and a.ID_TIPOCONCEPTOAPS = $tipo
                and (
                  P.ID_CONCEPTOAPS like '10%'
                  or P.ID_CONCEPTOAPS like '11%'
                  or P.ID_CONCEPTOAPS like '12%'
                  or P.ID_CONCEPTOAPS like '13%'
                  or P.ID_CONCEPTOAPS like '14%'
                  or P.ID_CONCEPTOAPS like '2%'
                  or P.ID_CONCEPTOAPS like '3%'
                  or P.ID_CONCEPTOAPS like '7%'
                )
                and not P.ID_CONCEPTOAPS like '7600%'
                --AND P.ID_CONCEPTOAPS IN(1000,1079,1126,1212,1145,1530,1532,3000,7030)
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    
    public static function st_data_salary_ingresos_tipo_total($year, $month, $entity, $id_persona,$tipo)
    {
        $query = "select 
                nvl(sum(p.COS_VALOR),0) as total
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                and p.ID_ENTIDAD=$entity
                and p.ID_ANHO=$year
                and p.ID_MES= $month
                and p.ID_PERSONA=$id_persona
                and a.ID_TIPOCONCEPTOAPS = $tipo
                and (
                  P.ID_CONCEPTOAPS like '10%'
                  or P.ID_CONCEPTOAPS like '11%'
                  or P.ID_CONCEPTOAPS like '12%'
                  or P.ID_CONCEPTOAPS like '13%'
                  or P.ID_CONCEPTOAPS like '14%'
                  or P.ID_CONCEPTOAPS like '2%'
                  or P.ID_CONCEPTOAPS like '3%'
                  or P.ID_CONCEPTOAPS like '7%'
                )
                and not P.ID_CONCEPTOAPS like '7600%'
                --AND P.ID_CONCEPTOAPS IN(1000,1079,1126,1212,1145,1530,1532,3000,7030)
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    
    
    
public static function st_data_salary_descuentos($year, $month, $entity, $id_persona)
    {

        $query = "select 
                p.fec_pago as fecha,a.NOMBRE as comentario, p.COS_VALOR as valor
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                and p.ID_ENTIDAD=$entity
                and p.ID_ANHO=$year
                and p.ID_MES= $month
                and p.ID_PERSONA=$id_persona
                and (
                  P.ID_CONCEPTOAPS like '15%'
                  or P.ID_CONCEPTOAPS like '7600%'
                )
                and not P.ID_CONCEPTOAPS like '1552%'
                and not P.ID_CONCEPTOAPS like '1556%'
                and not P.ID_CONCEPTOAPS like '1557%'
                and not P.ID_CONCEPTOAPS like '1558%'
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    
    public static function st_data_salary_descuentos_total($year, $month, $entity, $id_persona)
    {
        $query = "select 
                nvl(sum(p.COS_VALOR),0) as total
                from
                VW_APS_PLANILLA p, APS_CONCEPTO_PLANILLA a
                where p.ID_CONCEPTOAPS=a.ID_CONCEPTOAPS
                and p.ID_ENTIDAD=$entity
                and p.ID_ANHO=$year
                and p.ID_MES= $month
                and p.ID_PERSONA=$id_persona
                and (
                  P.ID_CONCEPTOAPS like '15%'
                  or P.ID_CONCEPTOAPS like '7600%'
                )
                and not P.ID_CONCEPTOAPS like '1552%'
                and not P.ID_CONCEPTOAPS like '1556%'
                and not P.ID_CONCEPTOAPS like '1557%'
                and not P.ID_CONCEPTOAPS like '1558%'
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }  
public static function st_data_salary_descuentos_tipo($year, $month, $entity, $id_cta_cte, $id_cuentaaasi)
    {

        $query = "select 
                to_char(FEC_ASIENTO,'dd/mm/yy') AS FECHA,
                    COMENTARIO,
                    COS_VALOR as valor
                from VW_CONTA_DIARIO
                where id_entidad = $entity
                and ID_CUENTAAASI = $id_cuentaaasi
                and ID_CTACTE = $id_cta_cte
                and ID_MES = $month
                and ID_ANHO = $year
                and not ID_TIPOASIENTO = 'RH'
                and FEC_CONTABILIZADO < (
                select max(FEC_CONTABILIZADO) from VW_CONTA_DIARIO
                where id_entidad = $entity
                and ID_CUENTAAASI = $id_cuentaaasi
                and ID_CTACTE = $id_cta_cte
                and ID_MES = $month
                and ID_ANHO = $year
                and ID_TIPOASIENTO = 'RH')
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }    
    public static function st_data_salary_descuentos_total_tipo($year, $month, $entity, $id_cta_cte, $id_cuentaaasi)
    {
        $query = "select nvl(sum(COS_VALOR),0) as total from VW_CONTA_DIARIO
                where id_entidad = $entity
                and ID_CUENTAAASI = $id_cuentaaasi
                and ID_CTACTE = $id_cta_cte
                and ID_MES = $month
                and ID_ANHO = $year
                and not ID_TIPOASIENTO = 'RH'
                and FEC_CONTABILIZADO < (
                select max(FEC_CONTABILIZADO) from VW_CONTA_DIARIO
                where id_entidad = $entity
                and ID_CUENTAAASI = $id_cuentaaasi
                and ID_CTACTE = $id_cta_cte
                and ID_MES = $month
                and ID_ANHO = $year
                and ID_TIPOASIENTO = 'RH')
                ";
        $oQuery = DB::select($query);
        return $oQuery;
    }  
    
    public static function st_data_details_total($year, $month, $entity, $id_cuentaaasi,$cta_cte)
    {

        $query = "SELECT
                    sum(COS_VALOR) AS total
                from VW_CONTA_DIARIO
                where ID_ENTIDAD = $entity
                and ID_CUENTAAASI = $id_cuentaaasi
                and ID_CTACTE = $cta_cte
                and ID_ANHO = $year
                and ID_MES <= $month
                ";
        
        $oQuery = DB::select($query);

        return $oQuery;
    }
}
