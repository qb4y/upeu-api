<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 5/29/19
 * Time: 7:12 PM
 */

namespace App\Http\Data\FinancesStudent;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Storage\StorageController;
use App\Http\Data\FinancesStudent\ComunData;
use App\Http\Data\Utils\SendEmail;
use App\Models\ProgramSemester;
use Illuminate\Support\Facades\DB;
use Exception;
use PDO;
use Illuminate\Http\Request;

class StudentData extends Controller {

    public static function listConfigVoucher($entity,$year,$opc){
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
    public static function getStudent($idStudent){
//        dd('hereeee', $idStudent);
        $query = "SELECT 
                ID_PERSONA,NOM_PERSONA,NUM_DOCUMENTO,CODIGO
                FROM MOISES.VW_PERSONA_NATURAL_ALUMNO
                WHERE CODIGO = $idStudent";
        $oQuery = DB::select($query);
//        dd($oQuery);
        return $oQuery;
    }
    public static function pagosDC($id_alumno_contrato, $dc) {
     /*$data = DB::table('eliseo.mat_alumno_contrato_det as a')
     ->join('eliseo.vw_mat_criterio_semestre as b', 'a.id_criterio_semestre', '=', 'b.id_criterio_semestre')
     ->where('a.id_alumno_contrato', $id_alumno_contrato)
    //  ->whereraw('a.dc', $dc)
     ->whereraw("a.dc like '%".$dc."%'")
     ->where('b.id_parent', null)
     ->select(
         'b.nombre',
         DB::raw("sum(a.importe) as importe"),
         'a.dc',
         'b.codigo',
         DB::raw("(case when a.dc = 'C' AND b.codigo in('DSCTOCP','DSCTOCPCA') then 'X' else a.dc end) as tipo"))
     ->groupBy('b.nombre', 'b.orden', 'a.dc', 'b.codigo')
     ->orderBy('b.orden')
     ->get();*/
 
     $bindings = [
        'p_id_alumno_contrato' => $id_alumno_contrato,
        'p_dc' => $dc
    ];
    

    $data = DB::executeProcedureWithCursor('eliseo.pkg_finances_students.sp_listar_detalle_contrato', $bindings);


     return $data;
    }
    public static function contratoAlumn($id_alumno_contrato) {
        $data = DB::table('david.acad_alumno_contrato')
            ->join("DAVID.ACAD_SEMESTRE_PROGRAMA", "ACAD_ALUMNO_CONTRATO.ID_SEMESTRE_PROGRAMA", "=", "ACAD_SEMESTRE_PROGRAMA.ID_SEMESTRE_PROGRAMA")
            ->join("DAVID.ACAD_PROGRAMA_ESTUDIO", "ACAD_SEMESTRE_PROGRAMA.ID_PROGRAMA_ESTUDIO", "=", "ACAD_PROGRAMA_ESTUDIO.ID_PROGRAMA_ESTUDIO")
            ->join("DAVID.TIPO_CONTRATO", function($join){
                $join->on("ACAD_ALUMNO_CONTRATO.ID_TIPO_CONTRATO", "=", "TIPO_CONTRATO.ID_TIPO_CONTRATO");
                $join->on("ACAD_ALUMNO_CONTRATO.ID_NIVEL_ENSENANZA", "=", "TIPO_CONTRATO.ID_NIVEL_ENSENANZA");
            })
        ->where('acad_alumno_contrato.id_alumno_contrato', $id_alumno_contrato)
        // ->where('acad_alumno_contrato.id_semestre_programa', $id_semestre_programa)
        ->select('acad_alumno_contrato.total_debito',
        'acad_alumno_contrato.total_credito',
        'acad_alumno_contrato.total',
        'acad_alumno_contrato.mensual',
        'acad_alumno_contrato.mensual_ens_resi',
        'acad_alumno_contrato.matricula',
        'acad_alumno_contrato.contado',
        'acad_alumno_contrato.pago',
        'acad_alumno_contrato.ciclo',
        'matricula1cuota',
        'acad_alumno_contrato.estado',
        'acad_alumno_contrato.id_persona',
        'acad_alumno_contrato.id_planpago_semestre',
        "TIPO_CONTRATO.PLANTILLA as CONTRATO_PLANTILLA",
        "ACAD_SEMESTRE_PROGRAMA.plan_pago_ciclo")
        ->get();
        return $data;
    }
    public static function planPagoSemestre($id_planpago_semestre) {
        $data = DB::table('eliseo.mat_planpago_semestre_det as a')
        ->where('a.id_planpago_semestre', $id_planpago_semestre)
        ->select('a.id_planpago_semestre',
        DB::raw("to_char(a.fecha_inicio, 'yyyy-MM-dd') as fecha_inicio"),
        DB::raw("to_char(a.fecha_fin, 'yyyy-MM-dd') as fecha_fin"),
        'a.orden')
        ->orderBy('a.orden', 'asc')
        ->get();
        return $data;
    }
    public static function planPagoSemestreDetail($contractAlumn) {

        $data = DB::table('MAT_PLANPAGO_SEMESTRE_DET')
        ->join('MAT_PLANPAGO_SEMESTRE', 'MAT_PLANPAGO_SEMESTRE_DET.ID_PLANPAGO_SEMESTRE', '=', 'MAT_PLANPAGO_SEMESTRE.ID_PLANPAGO_SEMESTRE')
        ->join('MAT_PLANPAGO', 'MAT_PLANPAGO_SEMESTRE.ID_PLANPAGO', '=', 'MAT_PLANPAGO.ID_PLANPAGO')
        ->where('MAT_PLANPAGO_SEMESTRE_DET.ID_PLANPAGO_SEMESTRE', $contractAlumn->id_planpago_semestre)
        ->select('MAT_PLANPAGO_SEMESTRE_DET.orden',
            DB::raw("to_char(MAT_PLANPAGO_SEMESTRE_DET.fecha_inicio, 'dd/MM/yyyy') as fecha_inicio,
                     to_char(MAT_PLANPAGO_SEMESTRE_DET.fecha_fin, 'dd/MM/yyyy') as fecha_fin, 
                     (DECODE(MAT_PLANPAGO.CUOTAS, MAT_PLANPAGO.CUOTA_CRO, ROWNUM, ROWNUM + 1)) nro_cuota"))
        ->orderBy('orden'); // nro_cuota

        if($contractAlumn and $contractAlumn->plan_pago_ciclo and $contractAlumn->plan_pago_ciclo == 'S') {
            $data = $data
                ->where('MAT_PLANPAGO_SEMESTRE_DET.ciclo', $contractAlumn->ciclo); // ? esto esta en ciclo
        }
        return $data->get();
    }
    public static function planPago($id_planpago_semestre) {
        $data = DB::table('eliseo.mat_planpago_semestre as a')
        ->join('eliseo.mat_planpago as b', 'a.id_planpago', '=', 'b.id_planpago')
        ->where('a.id_planpago_semestre', $id_planpago_semestre)
        ->select('a.id_planpago_semestre', 'b.nombre', 'b.descripcion', 'b.cuotas')
        ->get();
        return $data;
    }
    public static function addProrroga($request, $fecha_reg, $id_user_reg) {
        $id_anho        = $request->id_anho;
        $id_persona     = $request->id_persona;
        $motivo         = $request->motivo;
        $fecha_fin      = $request->fecha_fin;
        $estado         = 1;

        $count = DB::table('fin_llave')
        ->where('fecha_fin', $fecha_fin)
        ->where('id_persona', $id_persona)
        ->count();
        // dd($count);
        if($count == 0) {
        // dd($id_llave,  $id_anho, $numDoc, $id_persona, $motivo, $fecha_fin, $estado, $fecha_reg, $id_user_reg);
        $num = ['column' => 'id_anho', 'valor' =>  $id_anho];
        $numDoc = ComunData::correlativo('fin_llave', 'numdoc',  $num);
        $id_llave = ComunData::correlativo('fin_llave', 'id_llave');
        if($id_llave>0){
            $save = DB::table('fin_llave')->insert(
                [
                'id_llave'      => $id_llave,
                'id_anho'       => $id_anho,
                'numdoc'        => $numDoc,
                'id_persona'    => $id_persona,
                'motivo'        => $motivo,
                'fecha_ini'     => $fecha_reg,
                'fecha_fin'     => $fecha_fin,
                'estado'        => $estado,
                'id_user_reg'   => $id_user_reg,
                'fecha_reg'     => $fecha_reg,
                ]
            );
            if($save){
                $response=[
                    'success'=> true,
                    'message'=>'La prórroga se creo satisfactoriamente',
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede insertar',
                ];
            }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se ha generado correlativo',
            ];
        }  
    } else{
            $response=[
                'success'=> false,
                'message'=>'La fecha' .' ' .$fecha_fin. ' '. 'ya esta asignada',
            ];
        } 
        return $response;
}
    public static function validarCodigoProrroga($request) {

        // $codigo = [201410595, 201410594, 201410593, 201410592];
        $codigo = $request->codigo;
        $cod = json_decode($codigo);
        // dd($cod);
        $listaValid = array();
        // $listaValid = [];
        foreach($cod as $items) {
            // dd($items);
            $data = DB::table('eliseo.vw_persona_natural_alumno as a')
            ->where('codigo', $items)
            ->select('a.id_persona', 'a.codigo', 'a.nombre', 'a.paterno', 'a.materno', DB::raw("'S' as valida"))
            ->first();
            // array_push($listaValid);
            //   dd($data);
            if (empty($data)) {
                $dato = ['id_persona' => '', 'codigo'=> $items, 'nombre' => '', 'a.paterno' => '', 'a.materno' => '', 'valida' => 'N'];
                $listaValid[] =  $dato;
            } else {
                $listaValid[] =  $data;
            }
        }
        // dd($listaValid);
        return $listaValid;
    }


    public static function addProrrogaMasivo($request, $fecha_reg, $id_user_reg) {
        $id_anho        = $request->id_anho;
        $id_persona     = $request->id_persona;
        $motivo         = $request->motivo;
        $fecha_fin      = $request->fecha_fin;
        $estado         = 1;

    foreach ($id_persona as $datos) {
                $items=(object) $datos;

        $count = DB::table('fin_llave')
            ->where('fecha_fin', $fecha_fin)
            ->where('id_persona', $items->id_persona)
            ->count();
          
        $num = ['column' => 'id_anho', 'valor' =>  $id_anho];
        $numDoc = ComunData::correlativo('fin_llave', 'numdoc',  $num);
        $id_llave = ComunData::correlativo('fin_llave', 'id_llave');

        if($count == 0) {

        if($id_llave>0){

            $save = DB::table('fin_llave')->insert(
                [
                'id_llave'      => $id_llave,
                'id_anho'       => $id_anho,
                'numdoc'        => $numDoc,
                'id_persona'    => $items->id_persona,
                'motivo'        => $motivo,
                'fecha_ini'     => $fecha_reg,
                'fecha_fin'     => $fecha_fin,
                'estado'        => $estado,
                'id_user_reg'   => $id_user_reg,
                'fecha_reg'     => $fecha_reg,
                ]
            );

            if($save){
    
                $response=[
                    'success'=> true,
                    'message'=>'La prórroga se creo satisfactoriamente',
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede insertar',
                ];
            }
            // dd($save);
        } else{
            $response=[
                'success'=> false,
                'message'=>'No se ha generado correlativo',
            ];
        } 
    }   else{
            $response=[
                'success'=> false,
                'message'=>'La fecha' .' ' .$fecha_fin. ' '. 'ya esta asignada',
            ];
        } 

        }

        return $response;
}

public static function searchStudentGlobal($search) {
    $query = DB::table('moises.VW_PERSONA_NATURAL_ALUMNO')
        ->whereraw("codigo like '%".$search."%' or num_documento like '%".$search."%' 
        or upper(nombre|| ' '  || paterno|| ' ' ||materno) like upper('%".$search."%')
        or upper(paterno|| ' ' ||materno|| ' ' ||nombre) like upper('%".$search."%')")
        ->select('id_persona', 'codigo', 'nom_persona as nombres', 'num_documento')
        ->get();
        return $query;
}
public static function addDescVicerectorado($request, $id_usuario_reg, $fecha_registro) {
    // dd($fecha_registro);
    $descripcion        = $request->descripcion;
    $id_persona         = $request->id_persona;
    $ensenanza          = $request->ensenanza;
    $tipo_ense          = $request->tipo_ense;
    $matricula          = $request->matricula;
    $tipo_mat           = $request->tipo_mat;
    $tipo_dscto         = $request->tipo_dscto;
    $estado             = $request->estado;
    $id_semestre        = $request->id_semestre;

    $count = DB::table('fin_alumno_descuento_vice')
    ->where('id_persona', $id_persona)
    ->where('tipo_dscto', $tipo_dscto)
    ->where('id_semestre', $id_semestre)
    ->where('estado', $estado)
    ->count();
    // dd($count);
    if($count == 0) {

    $id_alumno_descuento_vice = ComunData::correlativo('fin_alumno_descuento_vice', 'id_alumno_descuento_vice'); 
    if($id_alumno_descuento_vice>0){
        $save = DB::table('fin_alumno_descuento_vice')->insert(
            [
            'id_alumno_descuento_vice'      => $id_alumno_descuento_vice,
            'descripcion'                   => $descripcion,
            'id_persona'                    => $id_persona,
            'ensenanza'                     => $ensenanza,
            'tipo_ense'                     => $tipo_ense,
            'matricula'                     => $matricula,
            'tipo_mat'                      => $tipo_mat,
            'tipo_dscto'                    => $tipo_dscto,
            'estado'                        => $estado,
            'id_semestre'                   => $id_semestre,
            'id_usuario_reg'                => $id_usuario_reg,
            'fecha_registro'                => $fecha_registro,
            ]
        );
        if($save){
            $response=[
                'success'=> true,
                'message'=>'La descuento se creo satisfactoriamente',
                'data' => $id_persona,
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede insertar',
                'data' => $id_persona,
            ];
        }
    }else{
        $response=[
            'success'=> false,
            'message'=>'No se ha generado correlativo',
            'data' => $id_persona,
        ];
    } 
} else{
    $response=[
        'success'=> false,
        'message'=>'El alumno ya se encuentra registrado',
        'data' => $id_persona,
    ];
} 
    return $response;
}
public static function listDescVicerectorado($student, $tipo_dscto,$id_semestre, $per_page, $id_sede,$export='N') {
    $q = DB::table('eliseo.fin_alumno_descuento_vice as a');
    $q->join('moises.vw_persona_natural_alumno as b', 'a.id_persona', '=' ,'b.id_persona');
    $q->join('fin_tipo_dscto t', 't.tipo_dscto', '=' ,'a.tipo_dscto');
    $q->leftjoin('users ui', 'ui.id', '=' ,'a.ID_USUARIO_REG');
    $q->leftjoin('users ue', 'ue.id', '=' ,'a.ID_USUARIO_ACT');
    $q->whereraw("(b.codigo like '%".$student."%' or b.num_documento like '%".$student."%'
    or ".ComunData::fnBuscar("b.nombre|| ' ' ||b.paterno")."  like ".ComunData::fnBuscar("'%".$student."%'")."
    or ".ComunData::fnBuscar("b.paterno|| ' ' ||b.materno")."  like ".ComunData::fnBuscar("'%".$student."%'").")");
    if(strlen($id_sede)>0){
        $q->whereraw("b.id_persona in (SELECT ID_PERSONA FROM DAVID.VW_ALUMNO_PLAN_PROGRAMA WHERE ID_SEDE in '".$id_sede."' AND ESTADO = '1')");
    }
    if(strlen($tipo_dscto)>0){
        $q->where("a.tipo_dscto",$tipo_dscto);
    }
    if(strlen($id_semestre)>0){
        $q->whereraw("case when t.consemestre='S' then a.id_semestre else ".$id_semestre." end =".$id_semestre);
    }
    $q->select('a.id_persona',
    DB::raw("FT_ALUMNO_EAP(a.id_persona) as EAP"),
    'a.descripcion',
    'a.id_alumno_descuento_vice',
    'a.ensenanza',
    'a.matricula',
    'a.estado',
    'b.nom_persona',
    'b.num_documento',
    'b.codigo', 
    'a.id_semestre',
    'ui.email as useri',
    'ue.email as usere',
    't.nombre as tipo',
    DB::raw("TO_CHAR(a.FECHA_REGISTRO, 'DD/MM/YYYY HH24:MI:SS') as freg,TO_CHAR(a.FECHA_ACTUALIZACION, 'DD/MM/YYYY HH24:MI:SS') as fedit"),
    DB::raw("(case when a.tipo_ense = 'P' then 'Porcentaje' when a.tipo_ense = 'I' then 'Importe'  ELSE '' END) as tipo_ense"),
    DB::raw("(case when a.tipo_mat = 'P' then 'Porcentaje' when a.tipo_mat = 'I' then 'Importe'  ELSE '' END) as tipo_mat")
    );
    // DB::raw("(case when a.tipo_dscto = 'V' then 'Vicerectorado' when a.tipo_dscto = 'P' then 'Descuento Hijo Personal'  ELSE '' END) as tipo_dscto"));
    // ->get();
    if ($export=='N'){
       $data = $q->paginate((int)$per_page); 
    }else{
            $data = $q->get();
    }
     
    return $data;
   
}
public static function deleteDescVicerectorado($id_alumno_descuento_vice){
    $query = DB::table('fin_alumno_descuento_vice')->where('id_alumno_descuento_vice', $id_alumno_descuento_vice)->delete();
    return $query;
}
public static function showDescVicerectorado($id_alumno_descuento_vice) {
    $data = DB::table('eliseo.fin_alumno_descuento_vice as a')
        ->join('moises.vw_persona_natural_alumno as b', 'a.id_persona', '=' ,'b.id_persona')
        ->join('fin_tipo_dscto t', 't.tipo_dscto', '=' ,'a.tipo_dscto')
        ->where('a.id_alumno_descuento_vice', $id_alumno_descuento_vice)
        ->select('a.id_alumno_descuento_vice',
        'a.descripcion',
        'a.tipo_ense',
        'a.id_persona',
        'a.ensenanza',
        'a.tipo_mat',
        'a.matricula',
        'a.estado',
        'b.nom_persona',
        'b.num_documento',
        'b.codigo',
        'a.id_semestre',
        'a.tipo_dscto',
        't.nombre as tipo',
        't.consemestre')
        ->get()->shift();
    return $data;
    // datitas
}
public static function updateDescVicerectorado($request, $id_alumno_descuento_vice, $id_usuario_reg, $fecha_registro) {
    // dd($fecha_registro);
    $descripcion        = $request->descripcion;
    $id_persona         = $request->id_persona;
    $ensenanza          = $request->ensenanza;
    $tipo_ense          = $request->tipo_ense;
    $matricula          = $request->matricula;
    $tipo_mat           = $request->tipo_mat;
    $tipo_dscto         = $request->tipo_dscto;
    $estado             = $request->estado;
    $id_semestre        = $request->id_semestre;
    
    $count = DB::table('fin_alumno_descuento_vice')
    ->where('id_persona', $id_persona)
    ->where('tipo_dscto', $tipo_dscto)
    ->where('id_alumno_descuento_vice','<>',$id_alumno_descuento_vice)->count();
    // dd($count);
    if($count == 0) {

        $result = DB::table('fin_alumno_descuento_vice')
        ->where('id_alumno_descuento_vice', $id_alumno_descuento_vice)
        ->update(
            [
            'descripcion'               => $descripcion,
            'id_persona'                => $id_persona,
            'ensenanza'                 => $ensenanza,
            'tipo_ense'                 => $tipo_ense,
            'matricula'                 => $matricula,
            'tipo_mat'                  => $tipo_mat,
            'tipo_dscto'                => $tipo_dscto,
            'id_semestre'                => $id_semestre,
            'estado'                    => $estado,
            'id_usuario_act'            => $id_usuario_reg,
            'fecha_actualizacion'       => $fecha_registro,
            ]
        );
        if($result){
            $response=[
                'success'=> true,
                'message'=>'La descuento se actualizo satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede insertar',
            ];
        }
    } else{
        $response=[
            'success'=> false,
            'message'=>'El alumno ya se encuentra registrado',
        ];
    } 
    return $response;
}

public static function situacionMatricula($request){
    $id_semestre = $request->id_semestre;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $id_sede = $request->id_sede;
    $ids = json_decode($id_tipo_contrato);
    $id_modalidad_estudio = $request->id_modalidad_estudio;
    $quitar_estado = $request->quitar_estado;

    $q = DB::table('david.acad_alumno_contrato as aac');
    $q->join('david.acad_semestre_programa as asp', 'Asp.Id_Semestre_Programa', '=', DB::raw("Aac.Id_Semestre_Programa and asp.id_semestre=".$id_semestre.""));
    $q->join('david.vw_acad_programa_estudio as ape', 'Ape.Id_Programa_Estudio', '=', 'Asp.Id_Programa_Estudio');
    $q->join('david.Acad_Matricula_Detalle amd', 'amd.Id_Matricula_Detalle', '=', 'aac.Id_Matricula_Detalle');
    $q->where('Amd.Id_Modo_Contrato', '1');
    $q->whereIn('Ape.Id_Tipo_Contrato', $ids);
    $q->where('Ape.id_Sede', $id_sede);
    if (!empty($quitar_estado) and $quitar_estado == 'S') { // cambiar el estado de confirmado = 'M' a En proceso y quitar el estado 0
        $q->whereNotIn('Aac.estado', ['0']);
        $q->select(DB::raw("decode(Aac.Estado,'1','Matriculados','M','En Proceso','3','Retirado') situacion,Aac.Estado,count(Aac.Estado) as cantidad"));
    } else {
        $q->select(DB::raw("decode(Aac.Estado,'1','Matriculados','0','En Proceso','M','Confirmado','3','Retirado') situacion,Aac.Estado,count(Aac.Estado) as cantidad"));
    }
    if ($id_modalidad_estudio) {$q->where('APE.ID_MODALIDAD_ESTUDIO', $id_modalidad_estudio);}
    $q->groupBy('Aac.Estado');
    $query = $q->get();
    return $query;

    // $query = "select decode(Aac.Estado,'1','Matriculados','0','En Proceso','M','Confirmado','3','Retirado') situacion,Aac.Estado,count(Aac.Estado) as cantidad
    // from david.acad_alumno_contrato aac
    // inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa
    //       and asp.id_semestre=".$id_semestre."
    // inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio
    // and Ape.Id_Tipo_Contrato in (".$id_tipo_contrato.")
    // and Ape.id_Sede='1'
    // group by Aac.Estado";
    // $oQuery = DB::select($query);
    // return $oQuery;
}

public static function getSituacionMatriculaExcel($request, $params){
    $data = collect(self::situacionMatriculaDetalle($request))->map(function($item) use ($params) {
        $item->total = self::mountTotalRecover($item, $params);
        return $item;
//        return collect($item)->merge(self::mountTotalRecover($item));
    });
    return \Excel::create('ArchivoExport', function($excel) use($data) {
        $excel->sheet('Hoja 1', function($sheet) use($data) {
            $sheet->loadView("excel.FinantialStudent.situacion-matricula")
                ->with('data', $data);
        });
    })->string('xls');;
}

public static function mountTotalRecover($item, $params) {
    return collect(self::saldoAlumno($item->id_persona, $params['id_entidad'], $params['id_depto'], date("Y")))
        ->pluck('total')->first();
}

/// se modifico el reporte agregando dos tablas por Carlo Magno
public static function situacionMatriculaDetalle($request){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $estado = $request->estado;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $id_facultad = $request->id_facultad;

    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);

    $query = "SELECT (AAC.ID_PLAN_PROGRAMA||'-'||AP.NOMBRE) AS PLAN, AAC.FECHA_REGISTRO, APE.ID_ESCUELA, APE.NOMBRE_ESCUELA, ape.nombre_escuela || ' - ' || ape.modalidad_estudio as nombre_escuela_2,  APE.NOMBRE_FACULTAD, decode(Aac.Estado, '1', 'Matriculados', '0', 'En Proceso', 'M', 'Confirmado') situacion, Aac.ESTADO,
    AAC.ID_PERSONA,
    PNA.CODIGO,
    P.NOMBRE || ' ' || P.PATERNO || ' ' || P.MATERNO AS NOMBRES, DAVID.FT_CALCULAR_CICLO_PROGRAMA(ASM.SEMESTRE,AAC.ID_PERSONA,AAC.ID_PLAN_PROGRAMA) CICLO,
    (SELECT Y.NOMBRE FROM MAT_PLANPAGO_SEMESTRE X JOIN MAT_PLANPAGO Y ON X.ID_PLANPAGO = Y.ID_PLANPAGO WHERE X.ID_PLANPAGO_SEMESTRE = AAC.ID_PLANPAGO_SEMESTRE) PLAN_PAGO,
    TO_CHAR((CASE WHEN AAC.CONTADO = 0 THEN AAC.MATRICULA1CUOTA ELSE AAC.CONTADO END),'999,999,999.99') AS IMP_MAT_ENS,
    (SELECT MAX(X.DIRECCION) FROM MOISES.PERSONA_VIRTUAL X WHERE X.ID_PERSONA = AAC.ID_PERSONA AND X.ID_TIPOVIRTUAL = 1 AND X.ES_aCTIVO = 1) AS CORREO,
    (SELECT MAX(X.NUM_TELEFONO) FROM MOISES.PERSONA_TELEFONO X WHERE X.ID_PERSONA = AAC.ID_PERSONA AND X.ID_TIPOTELEFONO = 5 AND X.ES_ACTIVO = 1) AS CELULAR,
    (SELECT COUNT(1) FROM FIN_EVIDENCIA X WHERE X.ID_CLIENTE = AAC.ID_PERSONA AND X.ID_TIPOEVIDENCIA = 1) LLAMADA
    -- (SELECT COUNT(1) FROM FIN_EVIDENCIA X WHERE X.ID_CLIENTE = AAC.ID_PERSONA AND X.ID_TIPOEVIDENCIA = 2) COMENTARIO,
    -- (SELECT DETALLE FROM FIN_EVIDENCIA X WHERE X.ID_CLIENTE = AAC.ID_PERSONA AND X.ID_TIPOEVIDENCIA = 2) COMENTARIO2
    FROM DAVID.ACAD_ALUMNO_CONTRATO AAC
    INNER JOIN DAVID.ACAD_SEMESTRE_PROGRAMA ASP
    ON ASP.ID_SEMESTRE_PROGRAMA = AAC.ID_SEMESTRE_PROGRAMA
    AND AAC.ESTADO = '".$estado."'
    AND ASP.ID_SEMESTRE = ".$id_semestre."
    INNER JOIN DAVID.ACAD_SEMESTRE ASM ON ASM.ID_SEMESTRE=ASP.ID_SEMESTRE
    INNER JOIN DAVID.VW_ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO = ASP.ID_PROGRAMA_ESTUDIO

    INNER JOIN DAVID.ACAD_PLAN_PROGRAMA APP ON APP.ID_PLAN_PROGRAMA=AAC.ID_PLAN_PROGRAMA
    INNER JOIN DAVID.ACAD_PLAN AP ON AP.ID_PLAN=APP.ID_PLAN

    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=Aac.Id_Matricula_Detalle
    INNER JOIN MOISES.PERSONA_NATURAL_ALUMNO PNA ON PNA.ID_PERSONA = AAC.ID_PERSONA
    INNER JOIN MOISES.PERSONA P ON P.ID_PERSONA = AAC.ID_PERSONA
    AND Amd.Id_Modo_Contrato='1'
    AND APE.ID_TIPO_CONTRATO IN (".$valor.")
    AND APE.ID_SEDE = '".$id_sede."'
    and ape.id_facultad = ".$id_facultad."
    order by AAC.FECHA_REGISTRO, APE.NOMBRE_ESCUELA, CICLO ASC ";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function facultadSituacionMatriculaDetalle($request) {
    $id_semestre = $request->id_semestre;
    $estado = $request->estado;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);

    $id_sede = $request->id_sede;
    if ($request->id_sede) {
        $id_sede = $request->id_sede;
    }

    // dd($id_tipo_contrato, $estado, $id_semestre);
    $query = "select decode(Aac.Estado, '1', 'Matriculados', '0', 'En Proceso', 'M', 'Confirmado') situacion, count(ape.nombre_facultad) as cantidad,
    Aac.Estado,
    Ape.Nombre_Facultad,
    Ape.id_facultad
    from david.acad_alumno_contrato aac
      inner join david.acad_semestre_programa asp
                 on Asp.Id_Semestre_Programa = Aac.Id_Semestre_Programa
                     and asp.id_semestre = ".$id_semestre."
      inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio = Asp.Id_Programa_Estudio
      inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=aac.Id_Matricula_Detalle
      and Amd.Id_Modo_Contrato= '1'
    and Ape.Id_Tipo_Contrato in (".$valor.")
    and Ape.id_Sede = ".$id_sede."
    and Aac.Estado = '".$estado."'
    group by ape.id_facultad,ape.nombre_facultad, aac.Estado";
    $oQuery = DB::select($query);
    return $oQuery;
}

public static function facultades($request){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);
    $id_modalidad_estudio = $request->id_modalidad_estudio;

    // $idss = [2,3,7];
    // dd($valor);
    $modalidad_estudio_query = ($id_modalidad_estudio)? "AND ape.ID_MODALIDAD_ESTUDIO=".$id_modalidad_estudio: "";
    
    $query = "select ape.id_facultad,ape.nombre_facultad,count(ape.nombre_facultad) as cantidad
    from david.acad_alumno_contrato aac
    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa and aac.estado='1'
          and asp.id_semestre=".$id_semestre."
    inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio $modalidad_estudio_query
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=aac.Id_Matricula_Detalle 
      and Amd.Id_Modo_Contrato= '1'
    and Ape.Id_Tipo_Contrato in (".$valor.")
    and Ape.id_Sede='".$id_sede."'
    group by ape.id_facultad,ape.nombre_facultad order by ape.nombre_facultad";

    $oQuery = DB::select($query);
    return $oQuery;
}
public static function facultadesDetalle($request){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $id_facultad = $request->id_facultad;
    $id_tipo_contrato = $request->id_tipo_contrato;

    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);


    $query = "select ape.id_facultad,ape.nombre_facultad,Aac.Id_Persona, pna.codigo, P.Nombre||' '||P.Paterno||' '||P.Materno as nombres,Ape.Nombre_Escuela,ape.nombre_escuela || ' - ' || ape.modalidad_estudio as nombre_escuela_2,Ape.Nombre_Facultad, 
    david.Ft_Calcular_Ciclo_Programa(asm.semestre,aac.id_persona,Aac.Id_Plan_Programa) ciclo
    from david.acad_alumno_contrato aac
    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa and aac.estado='1'
          and asp.id_semestre=".$id_semestre."
    inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=aac.Id_Matricula_Detalle
    inner join moises.persona_natural_alumno pna on Pna.Id_Persona=Aac.Id_Persona
    inner join moises.persona p on P.Id_Persona=Aac.Id_Persona
    inner join DAVID.acad_semestre asm on asm.id_semestre=asp.id_semestre
    and Amd.Id_Modo_Contrato= '1'
    and Ape.Id_Tipo_Contrato in (".$valor.")
    and Ape.id_Sede='".$id_sede."'
    and ape.id_facultad = ".$id_facultad."";
    // dd($id_semestre, $id_facultad, $valor, $query);
    $oQuery = DB::select($query);
    return $oQuery;
}

public static function escuelaEstadistica($request){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);
    $id_modalidad_estudio = $request->id_modalidad_estudio;

    $meta = '';
    $mostrarMeta = $request->agruparMeta;
    if ($mostrarMeta == 'S') {
        $meta = ", nvl((select distinct fme.cantidad_alumnos from 
        eliseo.fin_metas fme where fme.Id_Semestre_Programa=asp.Id_Semestre_Programa and fme.id_semestre=".$id_semestre." and fme.id_sede='".$id_sede."'), 0) as meta_alumnos";
    }
    // $query = "select ape.id_escuela,ape.nombre_escuela,count(ape.id_escuela) as cantidad, ape.nombre_facultad,
    // asp.Id_Semestre_Programa, ape.id_programa_estudio
    // from david.acad_alumno_contrato aac
    // inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa and aac.estado='1'
    //       and asp.id_semestre=".$id_semestre."
    // inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio
    // inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=aac.Id_Matricula_Detalle
    // and Amd.Id_Modo_Contrato= '1'
    // and Ape.Id_Tipo_Contrato in (".$valor.")
    // and Ape.id_Sede='".$id_sede."'
    // group by ape.id_escuela,ape.nombre_escuela, ape.nombre_facultad, asp.Id_Semestre_Programa, ape.id_programa_estudio order by ape.nombre_facultad";
    // $oQuery = DB::select($query);
    $modalidad_estudio_query = ($id_modalidad_estudio)? "AND ape.ID_MODALIDAD_ESTUDIO=".$id_modalidad_estudio: "";
    $query = "select ape.id_escuela,ape.nombre_escuela,count(ape.id_escuela) as cantidad, ape.nombre_facultad, ape.modalidad_estudio,
    asp.Id_Semestre_Programa $meta
    from david.acad_alumno_contrato aac
    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa and aac.estado='1'
          and asp.id_semestre=".$id_semestre."
    inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio $modalidad_estudio_query
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=aac.Id_Matricula_Detalle
    and Amd.Id_Modo_Contrato= '1'
    and Ape.Id_Tipo_Contrato in (".$valor.")
    and Ape.id_Sede='".$id_sede."'
    group by ape.id_escuela,ape.nombre_escuela, ape.nombre_facultad, asp.Id_Semestre_Programa, ape.modalidad_estudio order by ape.nombre_facultad";
    $oQuery = DB::select($query);
    return $oQuery;
}

public static function escuelaEstadisticaDetalle($request){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $id_escuela = $request->id_escuela;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $id_semestre_programa = $request->id_semestre_programa;
    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);

    $query = "select ape.id_escuela, ape.nombre_escuela, ape.nombre_escuela || ' - ' || ape.modalidad_estudio as nombre_escuela_2,  Ape.Nombre_Facultad,
    Aac.Id_Persona,
    pna.codigo,
    P.Nombre || ' ' || P.Paterno || ' ' || P.Materno as nombres, david.Ft_Calcular_Ciclo_Programa(asm.semestre,aac.id_persona,Aac.Id_Plan_Programa) ciclo,
    (SELECT MAX(X.DIRECCION) FROM MOISES.PERSONA_VIRTUAL X WHERE X.ID_PERSONA = AAC.ID_PERSONA AND X.ID_TIPOVIRTUAL = 1 AND X.ES_aCTIVO = 1) AS CORREO,
    (SELECT MAX(X.NUM_TELEFONO) FROM MOISES.PERSONA_TELEFONO X WHERE X.ID_PERSONA = AAC.ID_PERSONA AND X.ID_TIPOTELEFONO = 5 AND X.ES_ACTIVO = 1) AS CELULAR
from david.acad_alumno_contrato aac
      inner join david.acad_semestre_programa asp
                 on Asp.Id_Semestre_Programa = Aac.Id_Semestre_Programa and aac.estado = '1'
                     and asp.id_semestre = ".$id_semestre."
     inner join david.acad_semestre asm on asm.id_semestre=asp.id_semestre
      inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio = Asp.Id_Programa_Estudio
      inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=aac.Id_Matricula_Detalle
    
     inner join moises.persona_natural_alumno pna on Pna.Id_Persona = Aac.Id_Persona
      inner join moises.persona p on P.Id_Persona = Aac.Id_Persona
      and Amd.Id_Modo_Contrato= '1'
 and Ape.Id_Tipo_Contrato in (".$valor.")
 and Ape.id_Sede = '".$id_sede."'
 and ape.id_escuela = ".$id_escuela."
 and asp.id_Semestre_Programa = ".$id_semestre_programa."
 order by ciclo asc";
    $oQuery = DB::select($query);
    return $oQuery;
}


public static function vivienda(){

    $query = "select nvl(Aac.Id_Resid_Tipo_Habitacion,0) Id_Resid_Tipo_Habitacion,nvl(Rth.Nombre,'Externo') Nombre,count(Aac.Id_Resid_Tipo_Habitacion)
    from david.acad_alumno_contrato aac
    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa and aac.estado='1'
          and asp.id_semestre=107
    inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=aac.Id_Matricula_Detalle
    left join david.Residencia_Tipo_Habitacion rth on Rth.Id_Resid_Tipo_Habitacion=Aac.Id_Resid_Tipo_Habitacion
    and Amd.Id_Modo_Contrato= '1'
    and Ape.Id_Tipo_Contrato in (1)
    and Ape.id_Sede='1'
    group by Aac.Id_Resid_Tipo_Habitacion,Rth.Nombre";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function semestre() {
    $q =DB::table('david.acad_semestre_programa a')
    ->join('david.acad_semestre b' , 'a.id_semestre', '=', 'b.id_semestre')
    ->select('a.id_semestre', 'b.semestre')
    ->orderBy('semestre', 'DESC')
    ->distinct()
    ->get();
    return $q;
}


public static function listTransferDetails($id_voucher){
    /*$query = "SELECT DISTINCT
                    A.ID_TRANSFERENCIA,A.ID_VOUCHER,A.SERIE,A.NUMERO,TO_CHAR(A.FECHA,'DD/MM/YYYY') AS FECHA,
                    C.PATERNO||' '||C.MATERNO||' '||C.NOMBRE AS ALUMNO,D.CODIGO,
                    A.IMPORTE,A.GLOSA,
                    E.CUENTA,E.RESTRICCION,
                    E.CUENTA_CTE,E.DEPTO,
                    E.DESCRIPCION,
                    DECODE(SIGN(E.IMPORTE),1,E.IMPORTE,0) AS DEBITO, 
                    DECODE(SIGN(E.IMPORTE),-1,E.IMPORTE,0) AS CREDITO,
                    X.EMAIL
            FROM VENTA_TRANSFERENCIA A JOIN VENTA_TRANSFERENCIA_DETALLE B 
            ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
            JOIN MOISES.PERSONA C
            ON A.ID_CLIENTE = C.ID_PERSONA
            JOIN MOISES.PERSONA_NATURAL_ALUMNO  D
            ON A.ID_CLIENTE = D.ID_PERSONA
            JOIN CONTA_ASIENTO E
            ON A.ID_TRANSFERENCIA = E.ID_ORIGEN
            AND A.ID_VOUCHER = E.VOUCHER
            JOIN USERS X
            ON A.ID_PERSONA = X.ID
            WHERE E.ID_TIPOORIGEN = 2
            AND A.ESTADO = '1'
            AND A.ID_VOUCHER = $id_voucher
            ORDER BY A.ID_TRANSFERENCIA";*/
    $query = "SELECT  DISTINCT
                    B.ID_TRANSFERENCIA,B.ID_VOUCHER,B.SERIE,B.NUMERO,TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA,
                    D.PATERNO||' '||D.MATERNO||' '||D.NOMBRE AS ALUMNO,E.CODIGO,
                    B.IMPORTE,B.GLOSA,
                    A.CUENTA,A.RESTRICCION,
                    A.CUENTA_CTE,A.DEPTO,
                    A.DESCRIPCION,
                    DECODE(SIGN(A.IMPORTE),1,A.IMPORTE,0) AS DEBITO, 
                    DECODE(SIGN(A.IMPORTE),-1,A.IMPORTE,0) AS CREDITO,
                    X.EMAIL,
                    A.AGRUPA,
                    VF.NOMBRE AS NOMBRE_FILE,
                    VF.URL,
                    VF.FORMATO, VF.TIPO
                FROM (
                        SELECT 
                                ID_TIPOORIGEN,ID_ORIGEN,VOUCHER,CUENTA,RESTRICCION,CUENTA_CTE,DEPTO,DESCRIPCION,SUM(IMPORTE) AS IMPORTE,
                                (CASE WHEN AGRUPA = 'N' THEN ID_ASIENTO ELSE 0 END) AS AGRUPA
                        FROM CONTA_ASIENTO A
                        GROUP BY ID_TIPOORIGEN,ID_ORIGEN,VOUCHER,CUENTA,CUENTA,RESTRICCION,CUENTA_CTE,DEPTO,DESCRIPCION,(CASE WHEN AGRUPA = 'N' THEN ID_ASIENTO ELSE 0 END)
                ) A JOIN VENTA_TRANSFERENCIA B ON A.ID_ORIGEN = B.ID_TRANSFERENCIA AND A.VOUCHER = B.ID_VOUCHER
                JOIN VENTA_TRANSFERENCIA_DETALLE C ON C.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                JOIN MOISES.PERSONA D ON B.ID_CLIENTE = D.ID_PERSONA
                LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON B.ID_CLIENTE = E.ID_PERSONA
                JOIN USERS X ON B.ID_PERSONA = X.ID
                LEFT JOIN ELISEO.VENTA_FILE VF ON VF.ID_TRANSFERENCIA=C.ID_TRANSFERENCIA
                WHERE A.ID_TIPOORIGEN = 2
                AND B.ESTADO = '1'
                AND B.ID_VOUCHER = $id_voucher
                ORDER BY AGRUPA, DEBITO ASC ";
    $oQuery = DB::select($query);
    return $oQuery;
}

public static function listTransferDetailsTotal($id_voucher){

/*$query = "SELECT SUM(DEBITO) AS DEBITO, ABS(SUM(CREDITO))  AS CREDITO
FROM (
        SELECT DISTINCT A.ID_TRANSFERENCIA,
            (DECODE(SIGN(E.IMPORTE),1,E.IMPORTE,0)) AS DEBITO,
            ((DECODE(SIGN(E.IMPORTE),-1,E.IMPORTE,0))) AS CREDITO
        FROM VENTA_TRANSFERENCIA A JOIN VENTA_TRANSFERENCIA_DETALLE B
        ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
        JOIN MOISES.PERSONA C
        ON A.ID_CLIENTE = C.ID_PERSONA
        JOIN MOISES.PERSONA_NATURAL_ALUMNO  D
        ON A.ID_CLIENTE = D.ID_PERSONA
        JOIN CONTA_ASIENTO E
        ON A.ID_TRANSFERENCIA = E.ID_ORIGEN
        AND A.ID_VOUCHER = E.VOUCHER
        JOIN USERS X
        ON A.ID_PERSONA = X.ID
        WHERE E.ID_TIPOORIGEN = 2
        AND A.ESTADO = '1'
        AND A.ID_VOUCHER = $id_voucher)";*/
    $query = "SELECT SUM(DEBITO) AS DEBITO, ABS(SUM(CREDITO))  AS CREDITO 
            FROM (
                    SELECT  DISTINCT
                            B.ID_TRANSFERENCIA,B.ID_VOUCHER,B.SERIE,B.NUMERO,TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA,
                            D.PATERNO||' '||D.MATERNO||' '||D.NOMBRE AS ALUMNO,E.CODIGO,
                            B.IMPORTE,B.GLOSA,
                            A.CUENTA,A.RESTRICCION,
                            A.CUENTA_CTE,A.DEPTO,
                            A.DESCRIPCION,
                            DECODE(SIGN(A.IMPORTE),1,A.IMPORTE,0) AS DEBITO, 
                            DECODE(SIGN(A.IMPORTE),-1,A.IMPORTE,0) AS CREDITO,
                            X.EMAIL,
                            A.AGRUPA
                    FROM (
                            SELECT 
                                    ID_TIPOORIGEN,ID_ORIGEN,VOUCHER,CUENTA,RESTRICCION,CUENTA_CTE,DEPTO,DESCRIPCION,SUM(IMPORTE) AS IMPORTE,
                                    (CASE WHEN AGRUPA = 'N' THEN ID_ASIENTO ELSE 0 END) AS AGRUPA
                            FROM CONTA_ASIENTO A
                            GROUP BY ID_TIPOORIGEN,ID_ORIGEN,VOUCHER,CUENTA,CUENTA,RESTRICCION,CUENTA_CTE,DEPTO,DESCRIPCION,(CASE WHEN AGRUPA = 'N' THEN ID_ASIENTO ELSE 0 END)
                    ) A JOIN VENTA_TRANSFERENCIA B ON A.ID_ORIGEN = B.ID_TRANSFERENCIA AND A.VOUCHER = B.ID_VOUCHER
                    JOIN VENTA_TRANSFERENCIA_DETALLE C ON C.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                    JOIN MOISES.PERSONA D ON B.ID_CLIENTE = D.ID_PERSONA
                    LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON B.ID_CLIENTE = E.ID_PERSONA
                    JOIN USERS X ON B.ID_PERSONA = X.ID
                    WHERE A.ID_TIPOORIGEN = 2
                    AND B.ESTADO = '1'
                    AND B.ID_VOUCHER = $id_voucher
            ) ";
    $oQuery = DB::select($query);
    return $oQuery;
}

public static function listTransferResumen($id_voucher){
    /*$query = "SELECT 
                    CUENTA,NOMBRE_CUENTA,DEPTO,NOMBRE_DEPTO,TO_CHAR(SUM(DEBITO),'999,999,999.99') AS DEBITO,
                    TO_CHAR(SUM(CREDITO),'999,999,999.99') AS CREDITO
                FROM (
                SELECT DISTINCT A.ID_TRANSFERENCIA,
                    E.CUENTA,
                    (SELECT X.NOMBRE FROM CONTA_CTA_DENOMINACIONAL X WHERE X.ID_CUENTAAASI = E.CUENTA AND ID_TIPOPLAN = 1) AS NOMBRE_CUENTA,
                    E.DEPTO,
                    (SELECT X.NOMBRE FROM CONTA_ENTIDAD_DEPTO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = E.DEPTO) AS NOMBRE_DEPTO,
                    DECODE(SIGN(E.IMPORTE),1,E.IMPORTE,0) AS DEBITO, 
                    ABS(DECODE(SIGN(E.IMPORTE),-1,E.IMPORTE,0)) AS CREDITO
                    FROM VENTA_TRANSFERENCIA A JOIN VENTA_TRANSFERENCIA_DETALLE B 
                    ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                    JOIN MOISES.PERSONA C
                    ON A.ID_CLIENTE = C.ID_PERSONA
                    JOIN MOISES.PERSONA_NATURAL_ALUMNO  D
                    ON A.ID_CLIENTE = D.ID_PERSONA
                    JOIN CONTA_ASIENTO E
                    ON A.ID_TRANSFERENCIA = E.ID_ORIGEN
                    AND A.ID_TIPOORIGEN = E.ID_TIPOORIGEN
                    AND A.ID_VOUCHER = E.VOUCHER
                    JOIN USERS X
                    ON A.ID_PERSONA = X.ID
                    WHERE E.ID_TIPOORIGEN = 2
                    AND A.ESTADO = '1'
                    AND A.ID_VOUCHER = $id_voucher
                    )
                    GROUP BY CUENTA,NOMBRE_CUENTA,DEPTO,NOMBRE_DEPTO
                    ORDER BY CUENTA";*/
        $query = "SELECT 
                        CUENTA,NOMBRE_CUENTA,DEPTO,NOMBRE_DEPTO,TO_CHAR(SUM(DEBITO),'999,999,999.99') AS DEBITO,
                        TO_CHAR(SUM(CREDITO),'999,999,999.99') AS CREDITO
                FROM ( 
                        SELECT  DISTINCT
                                B.ID_TRANSFERENCIA,B.ID_VOUCHER,B.SERIE,B.NUMERO,TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA,
                                D.PATERNO||' '||D.MATERNO||' '||D.NOMBRE AS ALUMNO,E.CODIGO,
                                B.IMPORTE,B.GLOSA,
                                (SELECT X.NOMBRE FROM CONTA_CTA_DENOMINACIONAL X WHERE X.ID_CUENTAAASI = A.CUENTA AND ID_TIPOPLAN = 1) AS NOMBRE_CUENTA,
                                A.CUENTA,A.RESTRICCION,
                                (SELECT X.NOMBRE FROM CONTA_ENTIDAD_DEPTO X WHERE X.ID_ENTIDAD = B.ID_ENTIDAD AND X.ID_DEPTO = A.DEPTO) AS NOMBRE_DEPTO,
                                A.CUENTA_CTE,A.DEPTO,
                                A.DESCRIPCION,
                                DECODE(SIGN(A.IMPORTE),1,A.IMPORTE,0) AS DEBITO, 
                                DECODE(SIGN(A.IMPORTE),-1,A.IMPORTE,0) AS CREDITO,
                                X.EMAIL,
                                A.AGRUPA
                        FROM (
                                SELECT 
                                        ID_TIPOORIGEN,ID_ORIGEN,VOUCHER,CUENTA,RESTRICCION,CUENTA_CTE,DEPTO,DESCRIPCION,SUM(IMPORTE) AS IMPORTE,
                                        (CASE WHEN AGRUPA = 'N' THEN ID_ASIENTO ELSE 0 END) AS AGRUPA
                                FROM CONTA_ASIENTO A
                                GROUP BY ID_TIPOORIGEN,ID_ORIGEN,VOUCHER,CUENTA,CUENTA,RESTRICCION,CUENTA_CTE,DEPTO,DESCRIPCION,(CASE WHEN AGRUPA = 'N' THEN ID_ASIENTO ELSE 0 END)
                        ) A JOIN VENTA_TRANSFERENCIA B ON A.ID_ORIGEN = B.ID_TRANSFERENCIA AND A.VOUCHER = B.ID_VOUCHER
                        JOIN VENTA_TRANSFERENCIA_DETALLE C ON C.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                        JOIN MOISES.PERSONA D ON B.ID_CLIENTE = D.ID_PERSONA
                        LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON B.ID_CLIENTE = E.ID_PERSONA
                        JOIN USERS X ON B.ID_PERSONA = X.ID
                        WHERE A.ID_TIPOORIGEN = 2
                        AND B.ESTADO = '1'
                        AND B.ID_VOUCHER = $id_voucher
                )
                GROUP BY CUENTA,NOMBRE_CUENTA,DEPTO,NOMBRE_DEPTO
                ORDER BY CUENTA  ";
        $oQuery = DB::select($query);
        return $oQuery;
}

public static function listTransferResumenTotal($id_voucher){
    /*$query = "SELECT 
                TO_CHAR(SUM(DEBITO),'999,999,999.99') AS DEBITO,
                TO_CHAR(SUM(CREDITO),'999,999,999.99') AS CREDITO
            FROM (
            SELECT DISTINCT A.ID_TRANSFERENCIA,
                E.CUENTA,
                (SELECT X.NOMBRE FROM CONTA_CTA_DENOMINACIONAL X WHERE X.ID_CUENTAAASI = E.CUENTA AND ID_TIPOPLAN = 1) AS NOMBRE_CUENTA,
                E.DEPTO,
                (SELECT X.NOMBRE FROM CONTA_ENTIDAD_DEPTO X WHERE X.ID_ENTIDAD = A.ID_ENTIDAD AND X.ID_DEPTO = E.DEPTO) AS NOMBRE_DEPTO,
                DECODE(SIGN(E.IMPORTE),1,E.IMPORTE,0) AS DEBITO, 
                ABS(DECODE(SIGN(E.IMPORTE),-1,E.IMPORTE,0)) AS CREDITO
                FROM VENTA_TRANSFERENCIA A JOIN VENTA_TRANSFERENCIA_DETALLE B 
                ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                JOIN MOISES.PERSONA C
                ON A.ID_CLIENTE = C.ID_PERSONA
                JOIN MOISES.PERSONA_NATURAL_ALUMNO  D
                ON A.ID_CLIENTE = D.ID_PERSONA
                JOIN CONTA_ASIENTO E
                ON A.ID_TRANSFERENCIA = E.ID_ORIGEN
                AND A.ID_TIPOORIGEN = E.ID_TIPOORIGEN
                AND A.ID_VOUCHER = E.VOUCHER
                JOIN USERS X
                ON A.ID_PERSONA = X.ID
                WHERE E.ID_TIPOORIGEN = 2
                AND A.ESTADO = '1'
                AND A.ID_VOUCHER = $id_voucher
                )";*/
    $query = "SELECT 
                TO_CHAR(SUM(DEBITO),'999,999,999.99') AS DEBITO,
                TO_CHAR(SUM(CREDITO),'999,999,999.99') AS CREDITO
            FROM ( 
                SELECT  DISTINCT
                        B.ID_TRANSFERENCIA,B.ID_VOUCHER,B.SERIE,B.NUMERO,TO_CHAR(B.FECHA,'DD/MM/YYYY') AS FECHA,
                        D.PATERNO||' '||D.MATERNO||' '||D.NOMBRE AS ALUMNO,E.CODIGO,
                        B.IMPORTE,B.GLOSA,
                        (SELECT X.NOMBRE FROM CONTA_CTA_DENOMINACIONAL X WHERE X.ID_CUENTAAASI = A.CUENTA AND ID_TIPOPLAN = 1) AS NOMBRE_CUENTA,
                        A.CUENTA,A.RESTRICCION,
                        (SELECT X.NOMBRE FROM CONTA_ENTIDAD_DEPTO X WHERE X.ID_ENTIDAD = B.ID_ENTIDAD AND X.ID_DEPTO = A.DEPTO) AS NOMBRE_DEPTO,
                        A.CUENTA_CTE,A.DEPTO,
                        A.DESCRIPCION,
                        DECODE(SIGN(A.IMPORTE),1,A.IMPORTE,0) AS DEBITO, 
                        DECODE(SIGN(A.IMPORTE),-1,A.IMPORTE,0) AS CREDITO,
                        X.EMAIL,
                        A.AGRUPA
                FROM (
                        SELECT 
                                ID_TIPOORIGEN,ID_ORIGEN,VOUCHER,CUENTA,RESTRICCION,CUENTA_CTE,DEPTO,DESCRIPCION,SUM(IMPORTE) AS IMPORTE,
                                (CASE WHEN AGRUPA = 'N' THEN ID_ASIENTO ELSE 0 END) AS AGRUPA
                        FROM CONTA_ASIENTO A
                        GROUP BY ID_TIPOORIGEN,ID_ORIGEN,VOUCHER,CUENTA,CUENTA,RESTRICCION,CUENTA_CTE,DEPTO,DESCRIPCION,(CASE WHEN AGRUPA = 'N' THEN ID_ASIENTO ELSE 0 END)
                ) A JOIN VENTA_TRANSFERENCIA B ON A.ID_ORIGEN = B.ID_TRANSFERENCIA AND A.VOUCHER = B.ID_VOUCHER
                JOIN VENTA_TRANSFERENCIA_DETALLE C ON C.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                JOIN MOISES.PERSONA D ON B.ID_CLIENTE = D.ID_PERSONA
                LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO E ON B.ID_CLIENTE = E.ID_PERSONA
                JOIN USERS X ON B.ID_PERSONA = X.ID
                WHERE A.ID_TIPOORIGEN = 2
                AND B.ESTADO = '1'
                AND B.ID_VOUCHER = $id_voucher
            ) ";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function seguimientoAlumno($request, $id_entidad, $id_depto){

    $per_page = $request->per_page;
    $id_facultad = $request->id_facultad;
    $id_escuela = $request->id_escuela;
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $ciclo = $request->ciclo;
    $tipo = $request->tipo;
    $anio = $request->anio;
    // BasADO EN EL QUERY seguimiento alumno.

    $q =DB::table('moises.persona_natural pn');
    $q->join('david.Aatemp_Alumnos_Matriculados aam' , 'aam.id_persona', '=', 'pn.id_persona');
    $q->join('moises.persona p' , 'p.id_persona', '=', 'pn.id_persona');
    $q->join('david.acad_plan_programa app', 'App.Id_Plan_Programa', '=', 'Aam.Id_Plan_Programa');
    $q->join('david.Vw_Acad_Programa_Estudio ape', 'Ape.Id_Programa_Estudio', '=', 'App.Id_Programa_Estudio');
    $q->leftjoin('MOISES.PERSONA_NATURAL_ALUMNO X', 'X.ID_PERSONA', '=', 'pn.ID_PERSONA');
    $q->leftjoin('david.vw_acad_alumno_contrato vwaac', 'vwaac.id_persona', '=', DB::raw("pn.id_persona and vwaac.id_plan_programa=aam.id_plan_programa and vwaac.id_semestre=aam.id_semestre and vwaac.id_modo_contrato = 1"));
    if ($tipo == 'N') {
        $q->leftjoin('eliseo.fin_asignacion_docente fad', 'fad.id_cliente', '=', DB::raw("aam.id_persona and fad.id_semestre=aam.id_semestre and fad.id_escuela=aam.id_escuela and fad.estado = '1'"));
    }
    if($tipo == 'E') {
        $q->leftjoin('eliseo.fin_asignacion_docente fad', 'fad.id_cliente', '=', DB::raw("aam.id_persona and fad.id_anho=aam.anio and fad.id_escuela=aam.id_escuela and fad.estado = '1' and fad.id_semestre is null"));
    }
    $q->leftjoin('moises.persona pd', 'pd.id_persona', '=', 'Fad.Id_Docente');
    $q->select('Aam.Nombre_Facultad', 'Aam.Nombre_Escuela', 'p.nombre', 'p.paterno','p.materno','Pn.Celular','pn.correo','Aam.ciclo',
    'Aam.llamada', 'Aam.detalle', 'Aam.id_persona', 'Aam.id_plan_programa', 'x.codigo', DB::raw("decode(Aam.ciclo,0,'Si','No') Ingresante"),
    DB::raw("decode(pd.id_persona, null,'',pd.nombre||' '||pd.paterno||' '||pd.materno) docente_asignado"),  'aam.id_facultad',
    'aam.id_escuela', 'aam.tipo', 'aam.anio', DB::raw("decode(vwaac.estado,'1','MATRICULADO','M','CONFIRMADO','0','EN PROCESO','3','RETIRADO','NO INICIÓ PROCESO') as proceso"));
    if ($tipo == 'N') {
    $q->whereraw("Aam.ciclo <> decode(ape.id_escuela,83,15,11)");

    $q->whereraw("(aam.id_persona,aam.id_plan_programa) not in (select aac.id_persona,aac.id_plan_programa 
    from david.vw_acad_alumno_contrato aac where id_semestre = ".$id_semestre." and aac.id_modo_contrato = 1 and estado='1')");

    // $q->whereraw("aam.id_persona not in (select aac.id_persona from david.vw_acad_alumno_contrato aac where id_semestre=".$id_semestre." and Aac.Id_Modo_Contrato=1)");
    }
    $q->whereraw("aam.tipo = '".$tipo."'");

    $q->where('Ape.Id_Sede', $id_sede);

    if (strlen($anio)>0) {
        $q->where('aam.anio', $anio);
    }
    if (strlen($id_semestre)>0) {
        $q->where('aam.id_semestre', $id_semestre);
    }

    if (strlen($id_facultad)>0) {
        $q->where('aam.id_facultad', $id_facultad);
    }
    if (strlen($id_escuela)>0) {
        $q->where('aam.id_escuela', $id_escuela);
    }
    if (strlen($ciclo)>0) {
        $q->where('aam.ciclo', $ciclo);
    }
    $q->orderBy('Aam.Nombre_Facultad', 'desc');
    $q->orderBy('Aam.ciclo', 'asc');
    $q->orderBy('p.paterno', 'asc');
    $query =  $q->paginate((int)$per_page);
    return $query;
}

public static function seguimientoAlumnoExcel($request, $id_entidad, $id_depto){

    $id_facultad = $request->id_facultad;
    $id_escuela = $request->id_escuela;
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $ciclo = $request->ciclo;
    $tipo = $request->tipo;
    $anio = $request->anio;
    // BasADO EN EL QUERY seguimiento alumno.

    $q =DB::table('moises.persona_natural pn');
    $q->join('david.Aatemp_Alumnos_Matriculados aam' , 'aam.id_persona', '=', 'pn.id_persona');
    $q->join('moises.persona p' , 'p.id_persona', '=', 'pn.id_persona');
    $q->join('david.acad_plan_programa app', 'App.Id_Plan_Programa', '=', 'Aam.Id_Plan_Programa');
    $q->join('david.Vw_Acad_Programa_Estudio ape', 'Ape.Id_Programa_Estudio', '=', 'App.Id_Programa_Estudio');
    $q->leftjoin('MOISES.PERSONA_NATURAL_ALUMNO X', 'X.ID_PERSONA', '=', 'pn.ID_PERSONA');
    $q->leftjoin('david.acad_semestre asem', 'asem.id_semestre', '=', 'aam.id_semestre');
    $q->leftjoin('david.vw_acad_alumno_contrato vwaac', 'vwaac.id_persona', '=', DB::raw("pn.id_persona and vwaac.id_plan_programa=aam.id_plan_programa and vwaac.id_semestre=aam.id_semestre and vwaac.id_modo_contrato = 1"));
    if ($tipo == 'N') {
        $q->leftjoin('eliseo.fin_asignacion_docente fad', 'fad.id_cliente', '=', DB::raw("aam.id_persona and fad.id_semestre=aam.id_semestre and fad.id_escuela=aam.id_escuela and fad.estado = '1'"));
    }
    if($tipo == 'E') {
        $q->leftjoin('eliseo.fin_asignacion_docente fad', 'fad.id_cliente', '=', DB::raw("aam.id_persona and fad.id_anho=aam.anio and fad.id_escuela=aam.id_escuela and fad.estado = '1' and fad.id_semestre is null"));
    }
    $q->leftjoin('moises.persona pd', 'pd.id_persona', '=', 'Fad.Id_Docente');
    $q->select('asem.semestre', 'ape.sede','Aam.Nombre_Facultad', 'Aam.Nombre_Escuela', 'p.nombre', 'p.paterno','p.materno','Pn.Celular','pn.correo','Aam.ciclo',
    'Aam.llamada', 'Aam.detalle', 'Aam.id_persona', 'Aam.id_plan_programa', 'x.codigo', DB::raw("decode(Aam.ciclo,0,'Si','No') Ingresante"),
    DB::raw("decode(pd.id_persona, null,'',pd.nombre||' '||pd.paterno||' '||pd.materno) docente_asignado"),  'aam.id_facultad',
    'aam.id_escuela', 'aam.tipo', 'aam.anio', DB::raw("decode(vwaac.estado,'1','MATRICULADO','M','CONFIRMADO','0','EN PROCESO','3','RETIRADO','NO INICIÓ PROCESO') as proceso"));
    if ($tipo == 'N') {
    $q->whereraw("Aam.ciclo <> decode(ape.id_escuela,83,15,11)");
    $q->whereraw("(aam.id_persona,aam.id_plan_programa) not in (select aac.id_persona,aac.id_plan_programa 
    from david.vw_acad_alumno_contrato aac where id_semestre = ".$id_semestre." and aac.id_modo_contrato = 1 and estado='1')");
    }
    $q->whereraw("aam.tipo = '".$tipo."'");

    $q->where('Ape.Id_Sede', $id_sede);

    if (strlen($anio)>0) {
        $q->where('aam.anio', $anio);
    }
    if (strlen($id_semestre)>0) {
        $q->where('aam.id_semestre', $id_semestre);
    }

    if (strlen($id_facultad)>0) {
        $q->where('aam.id_facultad', $id_facultad);
    }
    if (strlen($id_escuela)>0) {
        $q->where('aam.id_escuela', $id_escuela);
    }
    if (strlen($ciclo)>0) {
        $q->where('aam.ciclo', $ciclo);
    }
    $q->orderBy('Aam.Nombre_Facultad', 'desc');
    $q->orderBy('Aam.ciclo', 'asc');
    $q->orderBy('p.paterno', 'asc');
    $query =  $q->get();
    return $query;
}
public static function llamadaAlumno($id_persona, $request) {
    $llamada            = $request->llamada;
    $id_plan_programa   = $request->id_plan_programa;

    $result = DB::table('david.Aatemp_Alumnos_Matriculados')
    ->where('id_persona', $id_persona)
    ->where('id_plan_programa', $id_plan_programa)
    ->update(
        [
        'llamada'                              => $llamada,
        ]
    );
    if($result){
        $response=[
            'success'=> true,
            'message'=>'La se actualizo satisfactoriamente',
        ];
    }else{
        $response=[
            'success'=> false,
            'message'=>'No se puede actualizar',
        ];
    }
    return $response;
    }
    public static function mensajeAlumno($id_persona, $request) {
        $detalle            = $request->detalle;
        $id_plan_programa   = $request->id_plan_programa;
    
        $result = DB::table('david.Aatemp_Alumnos_Matriculados')
        ->where('id_persona', $id_persona)
        ->where('id_plan_programa', $id_plan_programa)
        ->update(
            [
            'detalle'                              => $detalle,
            ]
        );
        if($result){
            $response=[
                'success'=> true,
                'message'=>'La se actualizo satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede actualizar',
            ];
        }
        return $response;
        }
        public static function getSemestreSegui($request){
            $tipo = $request->tipo;
            if ($tipo == 'N') {
                $query = "SELECT Distinct asm.id_semestre, asm.semestre nombre from david.Aatemp_Alumnos_Matriculados aam
                inner join david.acad_semestre asm on Asm.Id_Semestre=aam.id_semestre and aam.tipo='".$tipo."' order by asm.semestre desc";
            } elseif($tipo == 'E') {
                $query = "SELECT Distinct aam.anio from david.Aatemp_Alumnos_Matriculados aam where aam.tipo='".$tipo."' order by aam.anio desc";
            }
            $oQuery = DB::select($query);
            return $oQuery;
        }
        public static function getSedeSegui($request){
            $id_semestre = $request->id_semestre;
            $tipo = $request->tipo;
            $anio = $request->anio;
            if ($tipo == 'N') {
            $query = "SELECT Distinct Ape.Id_Sede, ape.sede nombre
            from david.Aatemp_Alumnos_Matriculados aam inner join david.Vw_Acad_Programa_Estudio ape on Ape.Id_Programa_Estudio=Aam.Id_Programa_Estudio
            where aam.id_semestre=".$id_semestre." and aam.tipo='".$tipo."' order by Ape.Id_Sede";
     
            } elseif($tipo == 'E') {
            $query = "SELECT Distinct Ape.Id_Sede, ape.sede nombre
            from david.Aatemp_Alumnos_Matriculados aam inner join david.Vw_Acad_Programa_Estudio ape on Ape.Id_Programa_Estudio=Aam.Id_Programa_Estudio
            where aam.anio=".$anio." and aam.tipo='".$tipo."' order by Ape.Id_Sede";
            }

            $oQuery = DB::select($query);
            return $oQuery;
        }
        public static function getFacultad($request){
            $id_semestre = $request->id_semestre;
            $id_sede = $request->id_sede;
            $tipo = $request->tipo;
            $anio = $request->anio;
            if ($tipo == 'N') {

                $query = "SELECT Distinct Ape.Id_facultad, Ape.Nombre_Facultad nombre
                from david.Aatemp_Alumnos_Matriculados aam inner join david.Vw_Acad_Programa_Estudio ape on Ape.Id_Programa_Estudio=Aam.Id_Programa_Estudio
                where aam.id_semestre=".$id_semestre." and ape.id_sede=".$id_sede." and aam.tipo='".$tipo."'
                order by Ape.Nombre_Facultad";
          
            } elseif($tipo == 'E') {

                $query = "SELECT Distinct Ape.Id_facultad, Ape.Nombre_Facultad nombre
                from david.Aatemp_Alumnos_Matriculados aam inner join david.Vw_Acad_Programa_Estudio ape on Ape.Id_Programa_Estudio=Aam.Id_Programa_Estudio
                where aam.anio=".$anio." and ape.id_sede=".$id_sede." and aam.tipo='".$tipo."'
                order by Ape.Nombre_Facultad";

            }
            $oQuery = DB::select($query);
            return $oQuery;
        }
        public static function getEscuela($request){
            $id_semestre = $request->id_semestre;
            $id_sede = $request->id_sede;
            $id_facultad = $request->id_facultad;
            $tipo = $request->tipo;
            $anio = $request->anio;
            if ($tipo == 'N') {
                $query ="SELECT Distinct Ape.Id_escuela, Ape.Nombre_escuela nombre
                from david.Aatemp_Alumnos_Matriculados aam inner join david.Vw_Acad_Programa_Estudio ape on Ape.Id_Programa_Estudio=Aam.Id_Programa_Estudio
                where aam.id_semestre=".$id_semestre." and ape.id_sede=".$id_sede." and ape.id_facultad=".$id_facultad." and aam.tipo='".$tipo."'
                order by Ape.Nombre_escuela";
            } elseif($tipo == 'E') {
                $query ="SELECT Distinct Ape.Id_escuela, Ape.Nombre_escuela nombre
                from david.Aatemp_Alumnos_Matriculados aam inner join david.Vw_Acad_Programa_Estudio ape on Ape.Id_Programa_Estudio=Aam.Id_Programa_Estudio
                where aam.anio=".$anio." and ape.id_sede=".$id_sede." and ape.id_facultad=".$id_facultad." and aam.tipo='".$tipo."'
                order by Ape.Nombre_escuela";
            }
            $oQuery = DB::select($query);
            return $oQuery;
        }
        public static function getCicloSegui($request){
            $id_semestre = $request->id_semestre;
            $id_sede = $request->id_sede;
            $id_facultad = $request->id_facultad;
            $id_escuela = $request->id_escuela;
            $tipo = $request->tipo;
            $query ="SELECT Distinct aam.ciclo, decode(aam.ciclo,0,'Ingresante',aam.ciclo) nombre
            from david.Aatemp_Alumnos_Matriculados aam inner join david.Vw_Acad_Programa_Estudio ape on Ape.Id_Programa_Estudio=Aam.Id_Programa_Estudio
            where aam.id_semestre=".$id_semestre." and ape.id_sede=".$id_sede." and ape.id_facultad=".$id_facultad." and ape.id_escuela=".$id_escuela."
            and aam.tipo='".$tipo."'
            order by aam.ciclo";
            $oQuery = DB::select($query);
            return $oQuery;
        }
        public static function getDocenteSegui($request){
            $id_semestre = $request->id_semestre;
            $id_sede = $request->id_sede;
            $id_facultad = $request->id_facultad;
            $id_escuela = $request->id_escuela;
            $anio = $request->anio;
            $tipo = $request->tipo;
            if ($tipo == 'N') {
                $query ="SELECT Distinct Aca.Id_escuela,accd.id_persona,p.nombre||' '||p.paterno||' '||p.materno docente
                from david.vw_acad_carga_academica aca inner join david.Acad_Carga_Curso_Docente accd on Accd.Id_Carga_Curso=Aca.Id_Carga_Curso
                inner join moises.persona p on p.id_persona = accd.id_persona
                where aca.id_semestre=".$id_semestre."
                and aca.id_facultad = ".$id_facultad."
                and aca.id_escuela = ".$id_escuela."";
            } elseif($tipo == 'E') {
                $query ="SELECT Distinct Aca.Id_escuela,accd.id_persona,p.nombre||' '||p.paterno||' '||p.materno docente, Aca.Nombre_Curso
                from david.vw_acad_carga_academica aca inner join david.Acad_Carga_Curso_Docente accd on Accd.Id_Carga_Curso=Aca.Id_Carga_Curso
                inner join moises.persona p on p.id_persona = accd.id_persona
                where substr(aca.semestre,0,4)='".$anio."'
                and aca.id_facultad = ".$id_facultad."
                and aca.id_escuela = ".$id_escuela."
                and (Aca.Nombre_Curso like '%T%sis%' or Aca.Nombre_Curso like '%Invest%')
                and Aca.Nombre_Curso not in david.sintilde(('Investigación Operativa'))";
            }

            $oQuery = DB::select($query);
            return $oQuery;
        }
        public static function llamadaAlumnoFinancial($id_user, $request, $fecha) {
            $id_tipoevidencia            = $request->id_tipoevidencia;
            $estado                      = $request->estado;
            $id_persona                  = $request->id_persona;
            $detalle                     = $request->detalle;
            $id_semestre                 = $request->id_semestre;

            $id_evidencia = ComunData::correlativo('eliseo.fin_evidencia', 'id_evidencia');
            if ($id_evidencia > 0) {
                $save = DB::table('eliseo.fin_evidencia')->insert(
                    [
                    'id_evidencia'           => $id_evidencia,
                    'id_cliente'             => $id_persona,
                    'id_tipoevidencia'       => $id_tipoevidencia,
                    'id_user'                => $id_user,
                    'estado'                 => $estado,
                    'detalle'                => $detalle,
                    'id_semestre'            => $id_semestre,
                    'fecha'                  =>$fecha,
                    ]
                );
         
            if($save){
                $response=[
                    'success'=> true,
                    'message'=>'La se creo satisfactoriamente',
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede crear',
                ];
            }
        } else {
            $response=[
                'success'=> false,
                'message'=>'No se pudo crear',
            ];
        }
            return $response;
    }
    public static function mensajeAlumnoFinancial($id_user, $request) {
        $id_tipoevidencia            = $request->id_tipoevidencia;
        $estado                      = 1;
        $id_persona                  = $request->id_persona;
        $detalle                       = $request->detalle;
        // dd($detalle);

        $id_evidencia = ComunData::correlativo('eliseo.fin_evidencia', 'id_evidencia');
        if ($id_evidencia > 0) {

            $save = DB::table('eliseo.fin_evidencia')->insert(
                [
                'id_evidencia'           => $id_evidencia,
                'id_cliente'             => $id_persona,
                'id_tipoevidencia'       => $id_tipoevidencia,
                'id_user'                => $id_user,
                'estado'                 => $estado,
                'detalle'                => $detalle,
                ]
            );
     
        if($save){
            $response=[
                'success'=> true,
                'message'=>'La se creo satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede crear',
            ];
        }
    } else {
        $response=[
            'success'=> false,
            'message'=>'No se pudo crear',
        ];
    }
        return $response;
}

public static function bloqueoAlumno($id_persona){
    $query = "SELECT TC.NOMBRE, C.DESCRIPCION,( P.NOMBRE || ' ' || P.PATERNO || ' ' || P.MATERNO) as NOMBRES, NOMBRE_ESCUELA ,
    C.ESTADO, S.NOMBRE AS SEMESTRE, (SELECT DISTINCT A.NOMBRE FROM ELISEO.ORG_SEDE A WHERE A.ID_SEDE = C.ID_SEDE) AS SEDE FROM
   DAVID.ACAD_CANDADO C INNER JOIN DAVID.TIPO_CANDADO TC ON C.ID_TIPO_CANDADO=TC.ID_TIPO_CANDADO
   INNER JOIN MOISES.PERSONA P ON P.ID_PERSONA=C.ID_PERSONA
   INNER JOIN DAVID.ACAD_SEMESTRE S ON S.ID_SEMESTRE=C.ID_SEMESTRE
   LEFT JOIN DAVID.VW_ACAD_PROGRAMA_ESTUDIO  VP ON VP.ID_PROGRAMA_ESTUDIO=C.ID_PROGRAMA_ESTUDIO
   WHERE C.ID_PERSONA=  $id_persona
   AND C.ESTADO= '1'";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function saldoAlumno($id_persona, $id_entidad, $id_depto, $id_anho){
    $query = "SELECT TO_CHAR(NVL(SUM(TOTAL),0) ,'999,999,999.99') AS TOTAL,SIGN(NVL(SUM(TOTAL),0)) AS SIGNO
    FROM (
            SELECT X.ID_CLIENTE, X.TOTAL
            FROM VW_SALES_MOV X WHERE X.ID_ENTIDAD = ".$id_entidad." AND X.ID_DEPTO = '".$id_depto."' AND X.ID_ANHO = ".$id_anho." AND X.ID_TIPOVENTA IN (1,2,3)
            UNION ALL
            SELECT X.ID_CLIENTE,SUM(X.IMPORTE)*DECODE(SIGN(SUM(X.IMPORTE)),1,-1,0) AS TOTAL
            FROM VW_SALES_ADVANCES X WHERE X.ID_ENTIDAD = ".$id_entidad." AND X.ID_DEPTO = '".$id_depto."' AND X.ID_ANHO = ".$id_anho." GROUP BY X.ID_CLIENTE
    ) X WHERE X.ID_CLIENTE = ".$id_persona."";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function tipoContrato($request){
    // dd($id_semestre);
    $id_sede = $request->id_sede;
    $id_semestre = $request->id_semestre;
    $id_nivel_ensenanza = $request->id_nivel_ensenanza;
    if ($request->id_sede) {
        $id_sede = $request->id_sede;
    }
    $whereNivelense = "";
    if ($id_nivel_ensenanza) {
        $whereNivelense = " and ape.id_nivel_ensenanza = $id_nivel_ensenanza";
    }
    // dd($id_sede);
    $query = "select Distinct Ape.Id_Tipo_Contrato, Tc.Nombre,Ape.id_nivel_ensenanza
    from david.acad_semestre_programa asp inner join david.vw_Acad_Programa_Estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio
    inner join david.Tipo_Contrato tc on Tc.Id_Tipo_Contrato=Ape.Id_Tipo_Contrato
    where asp.id_semestre=".$id_semestre." and ape.id_sede=".$id_sede." $whereNivelense
    order by Ape.id_nivel_ensenanza,Ape.Id_Tipo_Contrato";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function anticiposAlumno($request, $id_entidad, $id_depto) {
    $id_anho                = $request->id_anho;
    $id_cliente             = $request->id_cliente;
    // dd('holas',  $id_cliente,  $id_anho);
    // $query = "SELECT
    // SERIE,NUMERO,FECHA,GLOSA,
    // TO_CHAR((CASE WHEN DC = 'D' AND IMPORTE > 0 THEN 0  WHEN DC = 'C' AND IMPORTE < 0 THEN IMPORTE ELSE 0 END ),'999,999,999.99') AS DEBITO,
    // TO_CHAR((CASE WHEN DC = 'C' AND IMPORTE > 0 THEN IMPORTE WHEN DC = 'D' AND IMPORTE > 0 THEN IMPORTE ELSE 0 END ),'999,999,999.99') AS CREDITO
    // FROM VW_SALES_ADVANCES
    // WHERE ID_ENTIDAD = ".$id_entidad."
    // AND ID_DEPTO = '".$id_depto."'
    // AND ID_ANHO = ".$id_anho."
    // AND ID_CLIENTE = ".$id_cliente."
    // ORDER BY FECHA";
    // $oQuery = DB::select($query);
    // return $oQuery;
    $query = DB::table('eliseo.vw_sales_advances')
    ->where('id_entidad', '=', $id_entidad)
    ->where('id_depto', '=', $id_depto)
    ->where('id_anho', '=', $id_anho)
    ->where('id_cliente', '=', $id_cliente)
    ->select('serie', 'numero', 'fecha', 'glosa',
     DB::raw("TO_CHAR((CASE WHEN DC = 'D' AND IMPORTE > 0 THEN 0  WHEN DC = 'C' AND IMPORTE < 0 THEN IMPORTE ELSE 0 END ),'999,999,999.99') AS DEBITO"), 
     DB::raw("TO_CHAR((CASE WHEN DC = 'C' AND IMPORTE > 0 THEN IMPORTE WHEN DC = 'D' AND IMPORTE > 0 THEN IMPORTE ELSE 0 END ),'999,999,999.99') AS CREDITO"))
    ->orderBy('fecha')
    ->get();
    return $query;
}
public static function totalAnticiposAlumno($request, $id_entidad, $id_depto) {
    $id_anho                = $request->id_anho;
    $id_cliente             = $request->id_cliente;
    // dd('holas',  $id_cliente,  $id_anho);
    $query = DB::table('eliseo.vw_sales_advances')
    ->where('id_entidad', '=', $id_entidad)
    ->where('id_depto', '=', $id_depto)
    ->where('id_anho', '=', $id_anho)
    ->where('id_cliente', '=', $id_cliente)
    ->select(DB::raw("TO_CHAR(SUM(IMPORTE),'999,999,999.99') AS TOTAL"))
    ->orderBy('fecha')
    ->get()->shift();
    return $query;
}

public static function situacionCreditoMatricula($request){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $id_tipo_contrato = $request->id_tipo_contrato;
    // $id_tipo_contrato2 = $request->id_tipo_contrato2;
    $ids = json_decode($id_tipo_contrato);
    $quitar_estado = $request->quitar_estado;
    $id_modalidad_estudio = $request->id_modalidad_estudio;

// dd($ids);
    $q = DB::table('david.acad_alumno_contrato as aac');
    $q->join('david.acad_semestre_programa as asp', 'Asp.Id_Semestre_Programa', '=', DB::raw("Aac.Id_Semestre_Programa and asp.id_semestre=".$id_semestre.""));
    $q->join('david.vw_acad_programa_estudio as ape', 'Ape.Id_Programa_Estudio', '=', 'Asp.Id_Programa_Estudio');
    $q->join('david.Acad_Matricula_Detalle amd', 'amd.Id_Matricula_Detalle', '=', 'aac.Id_Matricula_Detalle');
    $q->where('Amd.Id_Modo_Contrato', '1');
    $q->whereIn('Ape.Id_Tipo_Contrato', $ids);
    $q->where('Ape.id_Sede',  $id_sede);
    if (!empty($quitar_estado) and $quitar_estado == 'S') { // cambiar el estado de confirmado = 'M' a En proceso y quitar el estado 0
        $q->whereNotIn('Aac.estado', ['0']);
        // $q->select(DB::raw("decode(Aac.Estado,'1','Matriculados','M','En Proceso','3','Retirado') situacion,Aac.Estado,
        // sum(nvl(david.ft_creditos_semestre_alumno(aac.id_persona,Aac.Id_Plan_Programa,Asp.Id_Semestre),0)) creditos"));
        $q->select(DB::raw("decode(Aac.Estado,'1','Matriculados','M','En Proceso','3','Retirado') situacion,Aac.Estado,
        sum(nvl(david.ft_credito_sem_alum_modo(aac.id_persona,Aac.Id_Plan_Programa,Asp.Id_Semestre,Amd.Id_Modo_Contrato,Aac.Estado),0)) creditos"));
    } else {
        // $q->select(DB::raw("decode(Aac.Estado,'1','Matriculados','0','En Proceso','M','Confirmado','3','Retirado') situacion,Aac.Estado,
        // sum(nvl(david.ft_creditos_semestre_alumno(aac.id_persona,Aac.Id_Plan_Programa,Asp.Id_Semestre),0)) creditos"));
        $q->select(DB::raw("decode(Aac.Estado,'1','Matriculados','0','En Proceso','M','Confirmado','3','Retirado') situacion, Aac.Estado,
        sum(nvl(david.ft_credito_sem_alum_modo(aac.id_persona,Aac.Id_Plan_Programa,Asp.Id_Semestre,Amd.Id_Modo_Contrato,Aac.Estado),0)) creditos"));
    }

    if ($id_modalidad_estudio) {$q->where('APE.ID_MODALIDAD_ESTUDIO', $id_modalidad_estudio);}

    $q->groupBy('Aac.Estado');
    $query = $q->get();
    return $query;
}

public static function facultadCreditos($request){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);
    $id_modalidad_estudio = $request->id_modalidad_estudio;

    // $idss = [2,3,7];
    // dd($valor);
    $modalidad_estudio_query = ($id_modalidad_estudio)? "AND ape.ID_MODALIDAD_ESTUDIO=".$id_modalidad_estudio: "";
    $query = "select ape.id_facultad,ape.nombre_facultad,sum(nvl(david.ft_credito_sem_alum_modo(aac.id_persona,Aac.Id_Plan_Programa,Asp.Id_Semestre,Amd.Id_Modo_Contrato,aac.estado),0)) creditos
    from david.acad_alumno_contrato aac
    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa and aac.estado='1'
    and asp.id_semestre=".$id_semestre."
    inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio $modalidad_estudio_query
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=Aac.Id_Matricula_Detalle
    and Amd.Id_Modo_Contrato='1'
    and Ape.Id_Tipo_Contrato in (".$valor.")
    and Ape.id_Sede='".$id_sede."'
    group by ape.id_facultad,ape.nombre_facultad order by ape.nombre_facultad";

    $oQuery = DB::select($query);
    return $oQuery;
}

public static function escuelaCreditos($request){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);
    $meta = '';
    $mostrarMeta = $request->agruparMetaCredito;
    $id_modalidad_estudio = $request->id_modalidad_estudio;

    if ($mostrarMeta == 'S') {
        $meta = ", nvl((select distinct fme.cantidad_creditos from 
        eliseo.fin_metas fme where fme.Id_Semestre_Programa=asp.Id_Semestre_Programa and fme.id_semestre=".$id_semestre." and fme.id_sede='".$id_sede."'), 0) as meta_creditos";
    }
    $modalidad_estudio_query = ($id_modalidad_estudio)? "AND ape.ID_MODALIDAD_ESTUDIO=".$id_modalidad_estudio: "";

    $query = "select ape.id_escuela,ape.nombre_escuela,sum(nvl(david.ft_credito_sem_alum_modo(aac.id_persona,Aac.Id_Plan_Programa,Asp.Id_Semestre,Amd.Id_Modo_Contrato,aac.estado),0)) creditos,
    ape.nombre_facultad, asp.Id_Semestre_Programa $meta
    from david.acad_alumno_contrato aac
    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa and aac.estado='1'
    and asp.id_semestre=".$id_semestre."
    inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio $modalidad_estudio_query
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=Aac.Id_Matricula_Detalle
    and Amd.Id_Modo_Contrato='1'
    and Ape.Id_Tipo_Contrato in (".$valor.")
    and Ape.id_Sede='".$id_sede."'
    group by ape.id_escuela,ape.nombre_escuela, ape.nombre_facultad, asp.Id_Semestre_Programa order by ape.nombre_facultad";

    $oQuery = DB::select($query);
    return $oQuery;
}
public static function studentCroussing($request){
    $id_semestre = $request->id_semestre;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $id_sede = $request->id_sede;
    $estado = $request->estado;
    // $per_page = 100;
    $query = "select Distinct Ape.Sede,Ape.Nombre_Facultad,Ape.Nombre_Escuela,
    david.Ft_Calcular_Ciclo_Programa(asm.semestre,aac.id_persona,aac.id_plan_programa) ciclo,
    aac.id_persona,P.Nombre||' '||P.Paterno||' '||P.Materno estudiante,pna.codigo,
    decode(Aac.Estado,'1','Matriculados','0','En Proceso','M','Confirmado','3','Retirados') situacion,
    david.ft_num_horas_cruce(aac.id_persona,asp.id_semestre) cruce
    from david.acad_alumno_contrato aac
    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa and aac.estado<>'3'
    inner join david.acad_semestre asm on asm.id_semestre=asp.id_semestre
    inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=Aac.Id_Matricula_Detalle
    inner join MOISES.Persona_Natural_Alumno pna on Pna.Id_Persona=Aac.Id_Persona
    inner join MOISES.Persona p on p.id_persona=pna.id_persona
    where Ape.Id_Tipo_Contrato = ".$id_tipo_contrato."
    and Amd.Id_Modo_Contrato='1'
    and Ape.id_Sede= ".$id_sede."
    and asp.id_semestre=".$id_semestre."
    and aac.estado like '%".$estado."%'
    and david.ft_num_horas_cruce(aac.id_persona,asp.id_semestre)>1
    order by 1,2,3,4";
 
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function detalleDeLlamada($request){
    // dd($id_semestre);
    $id_tipo_evidencia = $request->id_tipo_evidencia;
    $id_semestre = $request->id_semestre;
    $id_cliente = $request->id_cliente;

    $q = DB::table('eliseo.FIN_EVIDENCIA as a');
    $q->join('moises.PERSONA as c', 'a.ID_USER', '=', 'c.ID_PERSONA');
    $q->join('david.ACAD_SEMESTRE as b', 'a.ID_SEMESTRE', '=', 'b.ID_SEMESTRE');
    $q->join('ELISEO.TIPO_EVIDENCIA as te', 'te.ID_TIPOEVIDENCIA', '=', 'a.ID_TIPOEVIDENCIA');
    if(strlen($id_tipo_evidencia)>0) {
        $q->where('a.ID_TIPOEVIDENCIA', $id_tipo_evidencia);
    }
    $q->where('a.ID_SEMESTRE', $id_semestre);
    $q->where('a.ID_CLIENTE', $id_cliente);
    $q->select('a.ID_EVIDENCIA', 'a.ID_CLIENTE', 'a.ID_TIPOEVIDENCIA', 'a.detalle', 'a.estado', 'a.FECHA', 'b.semestre', 'te.nombre as evidencia',
    DB::raw("(c.NOMBRE|| ' ' ||c.PATERNO|| ' ' ||c.MATERNO) as nombres_user"),
    DB::raw("(select max(MOISES.PERSONA_TELEFONO.NUM_TELEFONO) from  MOISES.PERSONA_TELEFONO where MOISES.PERSONA_TELEFONO.id_persona = a.id_cliente) as numero")
        );
    $q->orderBy('a.fecha');
    $query = $q->get();
    return $query;
}
public static function listaDeEscuelas($request){
    $id_semestre = $request->id_semestre;
    $id_facultad = $request->id_facultad;
    $estado = $request->estado;
    $id_tipo_contrato = $request->id_tipo_contrato;

    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);

    $id_sede = '1';
    if ($request->id_sede) {
        $id_sede = $request->id_sede;
    }

    $query = "select distinct ape.id_facultad,ape.nombre_facultad,Ape.Nombre_Escuela, ape.id_escuela
    from david.acad_alumno_contrato aac
    inner join david.acad_semestre_programa asp on Asp.Id_Semestre_Programa=Aac.Id_Semestre_Programa
          and asp.id_semestre=".$id_semestre."
    inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio=Asp.Id_Programa_Estudio
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=aac.Id_Matricula_Detalle
    inner join DAVID.acad_semestre asm on asm.id_semestre=asp.id_semestre
    and Amd.Id_Modo_Contrato= '1'
    and Ape.Id_Tipo_Contrato in (".$valor.")
    and Ape.id_Sede= ".$id_sede."
    and ape.id_facultad = ".$id_facultad."
    and Aac.Estado = '".$estado."'";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function refinaciamientoEscuelaDetalle($request, $id_entidad){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $estado = $request->estado;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $id_facultad = $request->id_facultad;
    $id_escuela = $request->id_escuela;
    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);
    $anho = $request->anho;
    // dd( $id_escuela);
    $saldo = '';
    $deuda = $request->deuda; // con deuda
    if ($deuda == '1') {
        $saldo = "AND SIGN(X.SALDO) = 1";
    } else if ($deuda == '0') {
        $saldo = "AND SIGN(X.SALDO) <> 1";
    }
    if ($id_sede == "1"){
        $id_depto = "1";
    }elseif($id_sede == "2"){
        $id_depto = "5";
    }else{
        $id_depto = "6";
    }
    

    $query = "SELECT APE.ID_ESCUELA, APE.NOMBRE_ESCUELA,  APE.NOMBRE_FACULTAD, APE.nombre as programa,decode(Aac.Estado, '1', 'Matriculados', '0', 'En Proceso', 'M', 'Confirmado') situacion, Aac.ESTADO, ape.id_sedearea,
    ASP.ID_SEMESTRE,
    AAC.ID_PERSONA, AAC.ID_ALUMNO_CONTRATO, AAC.FECHA_REGISTRO,
    PNA.CODIGO,
    P.NOMBRE || ' ' || P.PATERNO || ' ' || P.MATERNO AS NOMBRES, DAVID.FT_CALCULAR_CICLO_PROGRAMA(ASM.SEMESTRE,AAC.ID_PERSONA,AAC.ID_PLAN_PROGRAMA) CICLO,
    (SELECT Y.NOMBRE FROM MAT_PLANPAGO_SEMESTRE X JOIN MAT_PLANPAGO Y ON X.ID_PLANPAGO = Y.ID_PLANPAGO WHERE X.ID_PLANPAGO_SEMESTRE = AAC.ID_PLANPAGO_SEMESTRE) PLAN_PAGO,
    TO_CHAR((CASE WHEN AAC.CONTADO = 0 THEN AAC.MATRICULA1CUOTA ELSE AAC.CONTADO END),'999,999,999.99') AS IMP_MAT_ENS,
    (SELECT MAX(X.DIRECCION) FROM MOISES.PERSONA_VIRTUAL X WHERE X.ID_PERSONA = AAC.ID_PERSONA AND X.ID_TIPOVIRTUAL = 1 AND X.ES_aCTIVO = 1) AS CORREO,
    (SELECT MAX(X.NUM_TELEFONO) FROM MOISES.PERSONA_TELEFONO X WHERE X.ID_PERSONA = AAC.ID_PERSONA AND X.ID_TIPOTELEFONO = 5 AND X.ES_ACTIVO = 1) AS CELULAR,
    (SELECT COUNT(1) FROM FIN_EVIDENCIA X WHERE X.ID_CLIENTE = AAC.ID_PERSONA AND X.ID_TIPOEVIDENCIA = 1) LLAMADA,
    (SELECT COUNT(1) FROM ELISEO.FIN_CONVENIO Y WHERE Y.ID_CLIENTE = AAC.ID_PERSONA AND Y.ID_SEMESTRE = $id_semestre) REFINANCIAMIENTO,
    (SELECT DISTINCT J.NOMBRE || ' ' || J.PATERNO || ' ' || J.MATERNO FROM ELISEO.FIN_ASIGNACION O, MOISES.PERSONA J 
    WHERE O.ID_FINANCISTA = J.ID_PERSONA AND O.ID_CLIENTE = AAC.ID_PERSONA AND O.ID_ESCUELA = $id_escuela AND O.ID_SEMESTRE = $id_semestre and O.estado = 1 ) FINANCISTA,
    (SELECT DISTINCT ID_FINANCISTA FROM ELISEO.FIN_ASIGNACION CAR WHERE CAR.ID_CLIENTE = AAC.ID_PERSONA AND CAR.ID_ESCUELA = $id_escuela AND CAR.ID_SEMESTRE = $id_semestre and CAR.estado = 1) ID_FINANCISTA,
    X.SALDO AS SALDO,
    (SELECT Q.CELULAR FROM  MOISES.PERSONA_NATURAL Q WHERE Q.ID_PERSONA = AAC.ID_RESP_FINANCIERO) celular_responsable,
    (SELECT R.NOMBRE || ' ' || R.PATERNO || ' ' || R.MATERNO AS NOMBRES_RESP FROM  MOISES.PERSONA R WHERE R.ID_PERSONA = AAC.ID_RESP_FINANCIERO) responsable, APE.ID_SEDE
    FROM DAVID.ACAD_ALUMNO_CONTRATO AAC
    INNER JOIN DAVID.ACAD_SEMESTRE_PROGRAMA ASP
    ON ASP.ID_SEMESTRE_PROGRAMA = AAC.ID_SEMESTRE_PROGRAMA
    AND AAC.ESTADO = '".$estado."'
    AND ASP.ID_SEMESTRE = ".$id_semestre."
    INNER JOIN DAVID.ACAD_SEMESTRE ASM ON ASM.ID_SEMESTRE=ASP.ID_SEMESTRE
    INNER JOIN DAVID.VW_ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO = ASP.ID_PROGRAMA_ESTUDIO
    inner join DAVID.Acad_Matricula_Detalle amd on Amd.Id_Matricula_Detalle=Aac.Id_Matricula_Detalle
    INNER JOIN MOISES.PERSONA_NATURAL_ALUMNO PNA ON PNA.ID_PERSONA = AAC.ID_PERSONA
    INNER JOIN MOISES.PERSONA P ON P.ID_PERSONA = AAC.ID_PERSONA
    INNER JOIN MOISES.PERSONA_NATURAL Q ON Q.ID_PERSONA = AAC.ID_PERSONA
    INNER JOIN VW_SALDO_ALUMNO X ON X.ID_CLIENTE = AAC.ID_PERSONA AND X.ID_ENTIDAD = ".$id_entidad." AND X.ID_DEPTO = '".$id_depto."' AND X.ID_ANHO = ".$anho."
    AND Amd.Id_Modo_Contrato='1'
    AND APE.ID_TIPO_CONTRATO IN (".$valor.")
    AND APE.ID_SEDE = '".$id_sede."'
    and ape.id_facultad = ".$id_facultad."
    and ape.id_escuela = ".$id_escuela."
    $saldo
    order by FINANCISTA,APE.NOMBRE_ESCUELA, CICLO ASC ";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function noMatriculadosRefinaciamientoEscuelaDetalle($request, $id_entidad){
    $id_semestre = $request->id_semestre;
    $id_sede = $request->id_sede;
    $estado = $request->estado;
    $id_tipo_contrato = $request->id_tipo_contrato;
    $id_facultad = $request->id_facultad;
    $id_escuela = $request->id_escuela;
    $ids = json_decode($id_tipo_contrato);
    $valor = implode(',', $ids);
    $anho = $request->anho;
    // dd( $id_escuela);
    $saldo = '';
    $deuda = $request->deuda; 
    if ($deuda == '1') {
        $saldo = "AND SIGN(X.SALDO) = 1"; // con deuda
    } else if ($deuda == '0') {
        $saldo = "AND SIGN(X.SALDO) <> 1"; // sin deuda
    }
    if ($id_sede == "1"){
        $id_depto = "1";
    }elseif($id_sede == "2"){
        $id_depto = "5";
    }else{
        $id_depto = "6";
    }
    $query = "SELECT Distinct ape.id_escuela,Ape.Nombre_Escuela,Ape.Nombre_Facultad,'No Matriculado' situacion,
    '0' estado, null id_sedearea, null id_semestre,p.id_persona,null id_alumno_contrato,
    pna.codigo,P.Nombre||' '||P.Paterno||' '||P.Materno nombres,
    --DAVID.FT_CALCULAR_CICLO_PROGRAMA(david.FT_MAX_SEMESTRE_ALUMNO(p.ID_PERSONA,vpp.ID_PLAN_PROGRAMA),p.ID_PERSONA,vpp.ID_PLAN_PROGRAMA)
    null ciclo,
    null plan_pago, null imp_mat_ens,pn.correo,pn.celular,
    (SELECT COUNT(1) FROM FIN_EVIDENCIA X WHERE X.ID_CLIENTE = p.ID_PERSONA AND X.ID_TIPOEVIDENCIA = 1) LLAMADA,
    (SELECT COUNT(1) FROM ELISEO.FIN_CONVENIO Y WHERE Y.ID_CLIENTE = p.ID_PERSONA AND Y.ID_SEMESTRE =  ".$id_semestre.") REFINANCIAMIENTO,
    (SELECT DISTINCT J.NOMBRE || ' ' || J.PATERNO || ' ' || J.MATERNO FROM ELISEO.FIN_ASIGNACION O, MOISES.PERSONA J 
    WHERE O.ID_FINANCISTA = J.ID_PERSONA AND O.ID_CLIENTE = p.ID_PERSONA AND O.ID_ESCUELA = $id_escuela AND O.ID_SEMESTRE = $id_semestre and O.estado = 1 ) FINANCISTA,
    (SELECT DISTINCT ID_FINANCISTA FROM ELISEO.FIN_ASIGNACION CAR WHERE CAR.ID_CLIENTE = P.ID_PERSONA AND CAR.ID_ESCUELA = $id_escuela AND CAR.ID_SEMESTRE = $id_semestre and CAR.estado = 1) ID_FINANCISTA,
    X.SALDO AS SALDO, APE.ID_SEDE
    from david.Vw_Alumno_Plan_Programa  vpp
    inner join moises.persona_natural_alumno pna on Pna.Id_Persona=vpp.id_persona
    inner join moises.persona_natural pn on Pn.Id_Persona=pna.id_persona
    inner join moises.persona p on P.Id_Persona=pn.id_persona
    INNER JOIN DAVID.VW_ACAD_PROGRAMA_ESTUDIO APE ON APE.ID_PROGRAMA_ESTUDIO = vpp.ID_PROGRAMA_ESTUDIO
    INNER JOIN VW_SALDO_ALUMNO X ON X.ID_CLIENTE = Pna.ID_PERSONA AND X.ID_ENTIDAD = ".$id_entidad." AND X.ID_DEPTO = '".$id_depto."' AND X.ID_ANHO = ".$anho."
    and vpp.estado='".$estado."'
    and APE.ID_TIPO_CONTRATO IN (".$valor.")
    AND APE.ID_SEDE = ".$id_sede."
    and ape.id_facultad = ".$id_facultad."
    and ape.id_escuela = ".$id_escuela."
    $saldo
    and p.id_persona
    not in (
            select aac.id_persona
            from david.acad_alumno_contrato aac
                     inner join david.acad_semestre_programa asp
                                on Asp.Id_Semestre_Programa = Aac.Id_Semestre_Programa and aac.estado in ('1')
                                    and asp.id_semestre =  ".$id_semestre."
                     inner join david.vw_acad_programa_estudio ape on Ape.Id_Programa_Estudio = Asp.Id_Programa_Estudio
                     inner join moises.persona_natural_alumno pna on Pna.Id_Persona = aac.id_persona
                     inner join moises.persona_natural pn on Pn.Id_Persona = pna.id_persona
                     inner join moises.persona p on P.Id_Persona = pn.id_persona
                     inner join david.Acad_Matricula_Detalle amd
                                on Amd.Id_Matricula_Detalle = Aac.Id_Matricula_Detalle and Amd.Id_Modo_Contrato = 1
            where APE.ID_TIPO_CONTRATO IN (".$valor.")
              AND APE.ID_SEDE = ".$id_sede."
              and ape.id_facultad = ".$id_facultad."
              and ape.id_escuela = ".$id_escuela."
        )
    order by nombres";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function alumnoRefinanciamiento($request){
    $id_persona = $request->id_persona;
    $objet = DB::table('moises.VW_PERSONA_NATURAL_ALUMNO as a')
    ->where('a.id_persona', $id_persona)
    ->select('a.nom_persona', 'a.codigo', 'a.num_documento', 'a.id_persona',
    DB::raw("(SELECT MAX(Y.DIRECCION) FROM MOISES.PERSONA_VIRTUAL Y WHERE Y.ID_PERSONA = a.ID_PERSONA AND Y.ID_TIPOVIRTUAL = 1 AND Y.ES_aCTIVO = 1) AS CORREO"))
    ->first();
    return $objet;
}

// cambios actualizados
public static function listaDocumentos($request){
    $id_anho = $request->id_anho;
    $id_cliente = $request->id_cliente;
    $id_sedearea = $request->id_sedearea;
    
    $idSedeArea = DB::table('eliseo.org_sede_area')
                            ->where('id_sedearea', $id_sedearea)
                            ->select('id_entidad', 'id_depto')
                            ->first();

                            
    $id_entidad =  $idSedeArea->id_entidad;


    $depto = $idSedeArea->id_depto[0];
    $id_depto = $depto;
    $query = "SELECT ID_VENTA,SERIE,NUMERO,SUM(TOTAL) AS TOTAL
    FROM VW_SALES_MOV
    WHERE ID_ENTIDAD = ".$id_entidad."
    AND ID_DEPTO = '".$id_depto."'
    AND ID_ANHO = ".$id_anho."
    AND ID_CLIENTE = ".$id_cliente." 
    GROUP BY ID_VENTA,SERIE,NUMERO
    HAVING SUM(TOTAL) <> 0";
    $oQuery = DB::select($query);
    return $oQuery;
}
public static function inserConvenio($request, $id_user_reg, $fecha_reg) {
    $id_anho            = $request->id_anho;
    $id_semestre        = $request->id_semestre;
    $id_cliente         = $request->id_cliente;
    $id_sedearea        = $request->id_sedearea;
    $num_cuotas         = $request->num_cuotas;
    $total              = $request->total;
    $observaciones      = $request->observaciones;
    $es_convenio        = $request->es_convenio;
    $id_empleado        = $request->id_empleado;
    $estado             = $request->estado;
    $details            = $request->details;
    // dd($request);
    $idSedeArea = DB::table('eliseo.org_sede_area')
                            ->where('id_sedearea', $id_sedearea)
                            ->select('id_entidad', 'id_depto')
                            ->first();

                            
    $id_entidad =  $idSedeArea->id_entidad;

    // $depto = $idSedeArea->id_depto[0];
    $id_depto = $idSedeArea->id_depto;
   
    $id_convenio = ComunData::correlativo('eliseo.fin_convenio', 'id_convenio');
    $numero = ComunData::correlativo('eliseo.fin_convenio', 'numero');
    // dd($numero);
    if ($id_convenio > 0) {
        $save = DB::table('eliseo.fin_convenio')->insert(
            [
            'id_convenio'            =>$id_convenio,
            'id_entidad'             =>$id_entidad,
            'id_depto'               =>$id_depto,
            'id_anho'                =>$id_anho,
            'id_semestre'            =>$id_semestre,
            'id_persona'             =>$id_user_reg,
            'id_cliente'             =>$id_cliente,
            'id_sedearea'            =>$id_sedearea,
            'numero'                 =>$numero,
            'nro_cuotas'             =>$num_cuotas,
            'total'                  =>$total,
            'fecha'                  =>$fecha_reg,
            'observaciones'          =>$observaciones,
            'es_convenio'            =>$es_convenio,
            'estado'                 =>$estado,
            'id_empleado'           =>$id_empleado,
            ]
        );
        if ($save and $id_convenio) {
                foreach($details as $datos) {
                    $id_cdetalle = ComunData::correlativo('eliseo.fin_convenio_detalle', 'id_cdetalle');
                    if ($id_cdetalle > 0) {
                    $items = (object)$datos;
                    $saveDetail = DB::table('eliseo.fin_convenio_detalle')->insert(
                                [
                                'id_cdetalle'               =>$id_cdetalle,
                                'id_convenio'               =>$id_convenio,
                                'fecha'                     =>$items->fecha_pago,
                                'importe'                   =>$items->monto,
                                'cumplio'                   =>$items->cumplio,
                                'cuota'                     =>$items->n_cuota,
                                ]
                    );
                }   
            } 
            if($saveDetail){
                $response=[
                    'success'=> true,
                    'message'=>'La se creo satisfactoriamente',
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede crear',
                ];
            }

        } else {
            $response=[
                'success'=> false,
                'message'=>'No se pudo crear',
            ];
        }
 
    } else {
            $response=[
                'success'=> false,
                'message'=>'No se pudo crear',
            ];
    }
    return $response;
}

public static function listConvenio($request){
    $id_semestre = $request->id_semestre;
    $id_sedearea = $request->id_sedearea;
    $id_cliente = $request->id_cliente;

     $idSedeArea = DB::table('eliseo.org_sede_area')
                            ->where('id_sedearea', $id_sedearea)
                            ->select('id_entidad', 'id_depto')
                            ->first();

                            
    $id_entidad =  $idSedeArea->id_entidad;

    // $depto = $idSedeArea->id_depto[0];
    $id_depto = $idSedeArea->id_depto;

    $query = DB::table('eliseo.fin_convenio as a')
    ->join('eliseo.fin_convenio_detalle as b', 'a.id_convenio', '=', 'b.id_convenio')
    ->where('a.id_entidad', $id_entidad)
    ->where('a.id_depto', $id_depto)
    ->where('a.id_semestre', $id_semestre)
    ->where('a.id_cliente', $id_cliente)
    ->select('a.id_convenio', 'b.id_cdetalle', 'a.nro_cuotas', 'a.total', 'a.es_convenio', 'a.estado', 'b.fecha', 'b.importe', 'b.cumplio')
    ->orderBy('b.fecha')
    ->get();
    return $query;
}
public static function updateConvenioCumplio($id_cdetalle, $request) {
    $cumplio   = $request->cumplio;
    $result = DB::table('eliseo.fin_convenio_detalle')
    ->where('id_cdetalle', $id_cdetalle)
    ->update(
        [
        'cumplio'                              => $cumplio,
        ]
    );
    if($result){
        $response=[
            'success'=> true,
            'message'=>'La se actualizo satisfactoriamente',
        ];
    }else{
        $response=[
            'success'=> false,
            'message'=>'No se puede actualizar',
        ];
    }
    return $response;
    }
    public static function listPrincipalConvenio($request){
        $id_semestre = $request->id_semestre;
        $id_sedearea = $request->id_sedearea;
        $id_cliente = $request->id_cliente;
    
         $idSedeArea = DB::table('eliseo.org_sede_area')
                                ->where('id_sedearea', $id_sedearea)
                                ->select('id_entidad', 'id_depto')
                                ->first();
               
        $id_entidad =  $idSedeArea->id_entidad;
    
        // $depto = $idSedeArea->id_depto[0];
        $id_depto = $idSedeArea->id_depto;
    
        $query = DB::table('eliseo.fin_convenio as a')
        ->where('a.id_entidad', $id_entidad)
        ->where('a.id_depto', $id_depto)
        ->where('a.id_semestre', $id_semestre)
        ->where('a.id_cliente', $id_cliente)
        ->select('a.id_convenio',  'a.nro_cuotas', 'a.total', 'a.es_convenio', 'a.estado', 'a.fecha')
        ->orderBy('a.fecha')
        ->get();
       
        $data = array();
        foreach ($query as $row) {
        $item = array();
        $item['id_convenio'] = $row->id_convenio;
        $item['nro_cuotas'] = $row->nro_cuotas;
        $item['total'] = $row->total;
        $item['es_convenio'] = $row->es_convenio;
        $item['estado'] = $row->estado;
        $item['details'] = StudentData::convenioChil($row->id_convenio);
        $data[] = $item;
    }
        return $data;
    }
    public static function listPrincipalConvenioStateCta($request){ 
        $data = array();  
        $id_cliente = $request->id_cliente; 
        $id_anho = $request->id_anho;  
        $carreras = DB::table('DAVID.vw_acad_alumno_contrato as a')
        ->join('DAVID.acad_programa_estudio as c', 'c.id_programa_estudio', '=', 'a.id_programa_estudio') 
        ->where('a.id_persona', $id_cliente)
        ->groupBy('c.id_sedearea', 'c.nombre','a.id_programa_estudio') 
        ->select('c.id_sedearea', 'c.nombre','a.id_programa_estudio')
        ->get(); 
        foreach($carreras as $carrera){ 
            $objCar = array();    
            $semestres = DB::table('DAVID.vw_acad_alumno_contrato as a')
            ->where('a.id_persona', $id_cliente)
            ->where('a.id_programa_estudio', $carrera->id_programa_estudio)
            ->where('a.semestre', 'like','%'.$id_anho.'%')
            ->select('a.id_semestre', 'a.semestre')
            ->get();
            $objCar['id_sedearea'] = $carrera->id_sedearea;
            $objCar['nombre'] = $carrera->nombre;
            $objCar['id_programa_estudio'] = $carrera->id_programa_estudio;
            $semeList = array();
            foreach($semestres as $semestre){
                $objSem =array();
                $objSem['id_semestre'] = $semestre->id_semestre;
                $objSem['semestre'] = $semestre->semestre; 
                $objSem['convenios'] =   self::listPrincipalConvenio(Request::create('/url-falsa', 'GET', [
                    'id_semestre' => $semestre->id_semestre,
                    'id_sedearea' => $carrera->id_sedearea,
                    'id_cliente' => $id_cliente
                ])); 
                $semeList[] =$objSem;
            } 
            $objCar['semestres'] = $semeList;
            $data[] = $objCar;
        } 
        return $data;
    }
    public static function convenioChil($id_convenio){
        // dd($id_convenio);
        $query = DB::table('eliseo.fin_convenio_detalle as a')
        ->where('a.id_convenio', $id_convenio)
        ->select('a.id_cdetalle',  'a.id_convenio', 'a.cuota', DB::raw("to_char(a.fecha, 'YYYY-MM-DD') as fecha"), 'a.importe', 'a.cumplio')
        ->orderBy('a.fecha')
        ->get();
        return $query;
     }
     public static function updateConvenioAnular($id_convenio, $request) {
        $estado   = $request->estado;
        $result = DB::table('eliseo.fin_convenio')
        ->where('id_convenio', $id_convenio)
        ->update(
            [
            'estado'                              => $estado,
            ]
        );
        if($result){
            $response=[
                'success'=> true,
                'message'=>'La se actualizo satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede actualizar',
            ];
        }
        return $response;
    }
    public static function updateDetalleConvenio($id_cdetalle, $request) {
        $fecha          = $request->fecha_pago;
        $cuota          = $request->cuota;
        $importe        = $request->importe;
        $id_convenio    = $request->id_convenio;

        $detalle = DB::table('eliseo.fin_convenio_detalle')
        ->where('id_cdetalle', $id_cdetalle)
        ->update(
            [
            'fecha'                              => $fecha,
            'cuota'                              => $cuota,
            'importe'                            => $importe,
            ]
        );

        if ($detalle) {
            $sum = DB::table('eliseo.fin_convenio_detalle')
            ->where('id_convenio', $id_convenio)
            ->select('importe')
            ->sum('importe');
    // dd($objet);
            $total = $sum;
    
            $cabecera = DB::table('eliseo.fin_convenio')
            ->where('id_convenio', $id_convenio)
            ->update(
                [
                    'total'               =>$total,
                    ]
                );
        }
        if($cabecera){
            $response=[
                'success'=> true,
                'message'=>'La se actualizo satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede actualizar',
            ];
        }
        return $response;
        }
    public static function deleteDetalleConvenio($id_convenio, $request) {
        $objet = DB::table('eliseo.fin_convenio')
        ->where('id_convenio', $id_convenio)
        ->select('nro_cuotas', 'total')
        ->first();

        $num_cuotas   = ((int)$objet->nro_cuotas) - 1;
        $total = ((int)$objet->total) - $request->importe;
        $id_cdetalle = $request->id_cdetalle;
        $cabecera = DB::table('eliseo.fin_convenio')
        ->where('id_convenio', $id_convenio)
        ->update(
            [
                'nro_cuotas'          =>$num_cuotas,
                'total'               =>$total,
                ]
            );
        if ($cabecera) {
            $detalle = DB::table('eliseo.fin_convenio_detalle')
            ->where('id_cdetalle', $id_cdetalle)
            ->delete();
        }
        if($detalle){
            $response=[
                'success'=> true,
                'message'=>'La se elimino satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar',
            ];
        }
        return $response;
    }
    public static function deleteConvenio($id_convenio) {
        $deleteDetalle = DB::table('eliseo.fin_convenio_detalle')
                                    ->where('id_convenio', $id_convenio)
                                    ->delete();
        if ($deleteDetalle) {
            $convenio = DB::table('eliseo.fin_convenio')
                                    ->where('id_convenio', $id_convenio)
                                    ->delete();
        }
        if($convenio){
            $response=[
                'success'=> true,
                'message'=>'La se elimino satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar',
            ];
        }
        return $response;
    }
    public static function nuevoDetalleConvenio($request) {

        $id_convenio = $request->id_convenio;
        $cuota = $request->cuota;
        $importe = $request->importe;
        $cumplio = $request->cumplio;
        $fecha = $request->fecha_pago;
        $id_cdetalle = ComunData::correlativo('eliseo.fin_convenio_detalle', 'id_cdetalle');
        if ($id_cdetalle > 0) {
            // dd($id_convenio);
        $insert = DB::table('eliseo.fin_convenio_detalle')
                                    ->where('id_convenio', $id_convenio)
                                    ->insert([
                                        'id_cdetalle'   =>  $id_cdetalle,
                                        'id_convenio'   =>  $id_convenio,
                                        'cuota'         =>  $cuota,
                                        'importe'       =>  $importe,
                                        'cumplio'       =>  $cumplio,
                                        'fecha'         =>  $fecha,
                                    ]);
        if ($insert) {
            $objet = DB::table('eliseo.fin_convenio')
                            ->where('id_convenio', $id_convenio)
                            ->select('nro_cuotas')
                            ->first();

            $sum = DB::table('eliseo.fin_convenio_detalle')
                       ->where('id_convenio', $id_convenio)
                       ->select('importe')
                       ->sum('importe');
               // dd($objet);
            $total = $sum;
            $num_cuotas   = ((int)$objet->nro_cuotas) + 1;
            $total = $sum;
          
            $cabecera = DB::table('eliseo.fin_convenio')
            ->where('id_convenio', $id_convenio)
            ->update(
                [
                    'nro_cuotas'          =>$num_cuotas,
                    'total'               =>$total,
                    ]
                );
        }

        } else {
            $response=[
                'success'=> false,
                'message'=>'No se pudo crear',
            ];
        }

        if($cabecera){
            $response=[
                'success'=> true,
                'message'=>'La se inserto y actualizo satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se insertar ni actualizar',
            ];
        }
        return $response;
        }
        public static function getSede($request) {
            $id_semestre = $request->id_semestre;
            $query = DB::table('david.acad_semestre_programa as asp')
                            ->join('david.vw_Acad_Programa_Estudio ape', 'Ape.Id_Programa_Estudio', '=', 'Asp.Id_Programa_Estudio')
                            ->where('asp.id_semestre', $id_semestre)
                            ->select('ape.id_sede', 'Ape.Sede', 'asp.ID_SEMESTRE')
                            ->distinct()
                            ->orderBy('ape.id_sede')
                            ->get();
            return $query;
        }
        public static function getResidencia($request) {
            $id_sede = $request->id_sede;
            // dd($id_sede);
            $query = DB::table('DAVID.ACAD_RESIDENCIA A')
                            ->join('DAVID.RESIDENCIA_TIPO_HABITACION B', 'A.ID_RESIDENCIA', '=', 'B.ID_RESIDENCIA')
                            ->where('A.ID_SEDE', $id_sede)
                            ->where('A.ESTADO', 1)
                            ->where('B.ESTADO', 1)
                            ->select('B.ID_RESID_TIPO_HABITACION', 'A.MODO', 'A.NOMBRE', 'A.CODIGO', 'B.NOMBRE', 'B.CODIGO', 'B.CUPO')
                            ->get();    
            return $query;
        }
        public static function getListaAlumnoInterno($request) {
            $id_nivel_ensenanza = $request->id_nivel_ensenanza;
            $id_sede = $request->id_sede;
            $id_semestre = $request->id_semestre;
            $id_modo_contrato = $request->id_modo_contrato;
            $id_modalidad_estudio = $request->id_modalidad_estudio;
            $id_programa_estudio = $request->id_programa_estudio;
            $id_resid_tipo_habitacion = $request->id_resid_tipo_habitacion;
            $per_page = $request->per_page;
           
            // dd($id_nivel_ensenanza, $id_sede, $id_semestre, $id_modo_contrato, $id_modalidad_estudio, $id_programa_estudio, $id_resid_tipo_habitacion, $ids);
            $q = DB::table('ELISEO.VW_MAT_MATRICULADOS A');
                            $q->join('DAVID.RESIDENCIA_TIPO_HABITACION B', 'A.ID_RESID_TIPO_HABITACION', '=', 'B.ID_RESID_TIPO_HABITACION');
                            $q->join('DAVID.ACAD_RESERVA_RESIDENCIA X', 'X.ID_PERSONA', '=', DB::raw("A.ID_PERSONA AND A.ID_SEMESTRE = X.ID_SEMESTRE "));
                            //$q->where('A.ID_SEMESTRE', '=', DB::raw("X.ID_SEMESTRE ");
                            $q->where('A.ID_NIVEL_ENSENANZA', $id_nivel_ensenanza);
                            $q->where('A.ID_SEDE', $id_sede);
                            $q->where('A.ID_SEMESTRE', $id_semestre);
                            $q->where('A.ID_MODO_CONTRATO', $id_modo_contrato);
                            $q->where('A.ID_MODALIDAD_ESTUDIO', $id_modalidad_estudio);
                            $q->where('A.ESTADO', '1');
                            $q->where('X.ESTADO', '6');
                            $q->whereraw("A.DIAS_RESIDENCIA > 0 ");
                            if (!empty($id_programa_estudio)) {
                                $ids = explode(',', $id_programa_estudio);
                                $q->whereIn('A.ID_PROGRAMA_ESTUDIO', $ids);
                            }
                            if (strlen($id_resid_tipo_habitacion)>0) {
                            $q->where('A.ID_RESID_TIPO_HABITACION', $id_resid_tipo_habitacion);
                            }
                            $q->select('A.ID_PROGRAMA_ESTUDIO', 'A.CODIGO',
                            DB::raw("(A.NOMBRE||' '||A.PATERNO||' '||A.MATERNO) AS NOMBRES"),
                            'A.NOMBRE_FACULTAD', 'A.NOMBRE_ESCUELA', 'A.PLAN_PAGO', 'A.DIAS_RESIDENCIA', 'B.NOMBRE AS NOMBRE_RESIDENCIA', 'B.CODIGO AS CODIGO_RESIDENCIA');
                            $q->orderBy('A.NOMBRE_FACULTAD');
                            $q->orderBy('A.NOMBRE_ESCUELA');
                        $query =  $q->paginate((int)$per_page); 
                        // dd($query);
            return $query;
        }

        public static function generarIndiceMorosidad($request, $id_user, $id_entidad, $id_depto) {
            $id_nivel_ensenanza = $request->id_nivel_ensenanza;
            $id_sede = $request->id_sede;
            $id_semestre = $request->id_semestre;
            $id_modo_contrato = $request->id_modo_contrato;
            $id_modalidad_estudio = $request->id_modalidad_estudio;
            $id_mes = $request->id_mes;
            $id_anho = $request->id_anho;
            $nerror = 0;
            $msgerror = '';
            for ($i = 1; $i <= 200; $i++) {
                $msgerror .= '';
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("BEGIN PKG_SALES_FINANCES.SP_MOROSIDAD(
                                    :P_ID_ENTIDAD,
                                    :P_ID_DEPTO,
                                    :P_ID_ANHO,
                                    :P_ID_MES,
                                    :P_ID_USER,
                                    :P_ID_SEMESTRE,
                                    :P_ID_NIVEL_ENSENANZA,
                                    :P_ID_MODO_CONTRATO,
                                    :P_ID_SEDE,
                                    :P_ID_MODALIDAD_ESTUDIO
                                  ); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_SEMESTRE', $id_semestre, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_NIVEL_ENSENANZA', $id_nivel_ensenanza, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MODO_CONTRATO', $id_modo_contrato, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_SEDE', $id_sede, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MODALIDAD_ESTUDIO', $id_modalidad_estudio, PDO::PARAM_INT);
            $stmt->execute();
            $return = [
                'nerror' => $nerror,
                'msgerror' => $msgerror,
            ];
            return $return;
            }

        public static function indiceMorosidad($request, $id_entidad, $id_depto,$id_user){

            $id_nivel_ensenanza = $request->id_nivel_ensenanza;
            $id_sede = $request->id_sede;
            $id_semestre = $request->id_semestre;
            $id_modo_contrato = $request->id_modo_contrato;
            $id_modalidad_estudio = $request->id_modalidad_estudio;
            $id_mes = $request->id_mes;
            $id_anho = $request->id_anho;

            if ($id_mes == 1) {
                $pivot = "1";
            } else if ($id_mes == 2) {
                $pivot = "1,2";
            } else if ($id_mes == 3) {
                $pivot = "1,2,3";
            }else if ($id_mes == 4) {
                $pivot = "1,2,3,4";
            }else if ($id_mes == 5) {
                $pivot = "1,2,3,4,5";
            }else if ($id_mes == 6) {
                $pivot = "1,2,3,4,5,6";
            }else if ($id_mes == 7) {
                $pivot = "1,2,3,4,5,6,7";
            }else if ($id_mes == 8) {
                $pivot = "1,2,3,4,5,6,7,8";
            }else if ($id_mes == 9) {
                $pivot = "1,2,3,4,5,6,7,8,9";
            }else if ($id_mes == 10) {
                $pivot = "1,2,3,4,5,6,7,8,9,10";
            }else if ($id_mes == 11) {
                $pivot = "1,2,3,4,5,6,7,8,9,10,11";
            }else if ($id_mes == 12) {
                $pivot = "1,2,3,4,5,6,7,8,9,10,11,12";
            }
            
            $query = "SELECT 
            *
            FROM (
            SELECT  
                   DENSE_RANK() OVER (ORDER BY FACULTAD) AS CONTADOR,
                    ID_MES,
                    COUNT(ID_CLIENTE) AS ALUMNOS,
                    ID_FACULTAD,--ID_ESCUELA,
                    NVL(FACULTAD,'TOTAL') AS FACULTAD,
                    ESCUELA,
                    SUM(INGRESO) AS INGRESO,SUM(DEBITO) AS DEBITO,SUM(SALDO) AS SALDO,
                    SUM(DEBITO) AS ACUMULADO,
                    --SUM(ACUMULADO) AS ACUMULADO,
                    ROUND((SUM(SALDO)/(CASE WHEN SUM(DEBITO) = 0 THEN 1 ELSE SUM(DEBITO) END))*100,2) AS MOROSIDAD
            FROM (
                    SELECT 
                            B.ID_CLIENTE,
                            A.ID_FACULTAD,
                            --A.ID_ESCUELA,
                            A.NOMBRE_FACULTAD AS FACULTAD,
                            A.NOMBRE_ESCUELA AS ESCUELA,
                            B.ID_MES,
                            B.INGRESO,
                            B.DEBITO,
                            B.CREDITO,
                            B.SALDO,
                            (SELECT SUM(R.INGRESO) FROM REP_MOROSIDAD R WHERE R.ID_CLIENTE=B.ID_CLIENTE AND R.ID_ANHO=B.ID_ANHO AND  R.ID_USER=B.ID_USER AND R.ID_ENTIDAD=B.ID_ENTIDAD AND R.ID_DEPTO=B.ID_DEPTO AND R.ID_MES<=B.ID_MES) AS ACUMULADO
                    FROM VW_MAT_MATRICULADOS A JOIN REP_MOROSIDAD B ON A.ID_PERSONA = B.ID_CLIENTE
                    WHERE A.ID_SEMESTRE = ".$id_semestre."
                    AND A.ID_NIVEL_ENSENANZA = ".$id_nivel_ensenanza."
                    AND A.ID_MODO_CONTRATO = ".$id_modo_contrato."
                    AND ID_SEDE = ".$id_sede."
                    AND A.ID_MODALIDAD_ESTUDIO = ".$id_modalidad_estudio."
                    AND B.ID_ENTIDAD = ".$id_entidad."
                    AND B.ID_DEPTO = '".$id_depto."'
                    AND B.ID_ANHO = ".$id_anho."
                    AND B.ID_MES <= ".$id_mes." 
                    AND B.ID_USER = ".$id_user."
                    AND A.CUOTAS <> 1        
                                                                              
            )
            GROUP BY ID_MES,ROLLUP(FACULTAD,ID_FACULTAD,ESCUELA)
            HAVING (CASE WHEN ID_FACULTAD IS NULL AND FACULTAD IS NULL THEN 0 ELSE ID_FACULTAD END) IS NOT NULL
            )
            PIVOT (SUM(INGRESO) AS INGRESO,SUM(DEBITO) AS DEBITO,SUM(SALDO) AS SALDO ,SUM(MOROSIDAD) AS MOROSIDAD,SUM(ACUMULADO) AS ACUMULADO
            FOR ID_MES IN(".$pivot.")
            )
            ORDER BY FACULTAD,ESCUELA";
                    $oQuery = DB::select($query);
                    return $oQuery;
        }
        public static function indiceMorosidadDetalle($request, $id_entidad, $id_depto){
            $id_nivel_ensenanza = $request->id_nivel_ensenanza;
            $id_sede = $request->id_sede;
            $id_semestre = $request->id_semestre;
            $id_modo_contrato = $request->id_modo_contrato;
            $id_modalidad_estudio = $request->id_modalidad_estudio;
            $id_mes = $request->id_mes;
            $id_anho = $request->id_anho;
            $id_facultad = $request->id_facultad;

            if ($id_mes == 1) {
                $pivot = "1";
            } else if ($id_mes == 2) {
                $pivot = "1,2";
            } else if ($id_mes == 3) {
                $pivot = "1,2,3";
            }else if ($id_mes == 4) {
                $pivot = "1,2,3,4";
            }else if ($id_mes == 5) {
                $pivot = "1,2,3,4,5";
            }else if ($id_mes == 6) {
                $pivot = "1,2,3,4,5,6";
            }else if ($id_mes == 7) {
                $pivot = "1,2,3,4,5,6,7";
            }else if ($id_mes == 8) {
                $pivot = "1,2,3,4,5,6,7,8";
            }else if ($id_mes == 9) {
                $pivot = "1,2,3,4,5,6,7,8,9";
            }else if ($id_mes == 10) {
                $pivot = "1,2,3,4,5,6,7,8,9,10";
            }else if ($id_mes == 11) {
                $pivot = "1,2,3,4,5,6,7,8,9,10,11";
            }else if ($id_mes == 12) {
                $pivot = "1,2,3,4,5,6,7,8,9,10,11,12";
            }

            $query = "SELECT
            *
            FROM (
            SELECT  DENSE_RANK() OVER (ORDER BY ESCUELA) AS CONTADOR, ID_MES,
                    ESCUELA,
                    --ID_CLIENTE,
                    CODIGO||' - '||ALUMNO AS ALUMNO,
                    SUM(INGRESO) AS INGRESO,
                    SUM(DEBITO) AS DEBITO,
                    SUM(SALDO) AS SALDO,
                    SUM(DEBITO) AS ACUMULADO,
                    --SUM(ACUMULADO) AS ACUMULADO,
                    ROUND((SUM(SALDO)/(CASE WHEN SUM(DEBITO) = 0 THEN 1 ELSE SUM(DEBITO) END))*100,2) AS MOROSIDAD
            FROM (
                    SELECT
                            B.ID_CLIENTE,
                            A.CODIGO,
                            A.PATERNO||' '||A.MATERNO||' '||A.NOMBRE AS ALUMNO,A.NOMBRE_ESCUELA AS ESCUELA,
                            B.ID_MES,
                            B.INGRESO,
                            B.DEBITO,
                            B.CREDITO,
                            B.SALDO,
                            B.MOROSIDAD,
                            (SELECT SUM(R.INGRESO) FROM REP_MOROSIDAD R WHERE R.ID_CLIENTE=B.ID_CLIENTE AND R.ID_ANHO=B.ID_ANHO AND  R.ID_USER=B.ID_USER AND R.ID_ENTIDAD=B.ID_ENTIDAD AND R.ID_DEPTO=B.ID_DEPTO AND R.ID_MES<=B.ID_MES) AS ACUMULADO
                    FROM VW_MAT_MATRICULADOS A JOIN REP_MOROSIDAD B ON A.ID_PERSONA = B.ID_CLIENTE
                    WHERE A.ID_SEMESTRE = ".$id_semestre."
                    AND A.ID_NIVEL_ENSENANZA = ".$id_nivel_ensenanza."
                    AND A.ID_MODO_CONTRATO = ".$id_modo_contrato."
                    AND ID_SEDE = ".$id_sede."
                    AND A.ID_MODALIDAD_ESTUDIO = ".$id_modalidad_estudio."
                    AND B.ID_ENTIDAD = ".$id_entidad."
                    AND B.ID_DEPTO = '".$id_depto."'
                    AND B.ID_ANHO = ".$id_anho."
                    AND B.ID_MES <= ".$id_mes." 
                    AND A.ID_FACULTAD = ".$id_facultad." 
                    --AND B.ID_CLIENTE = 7789 --IN (78583,198015,197004,69592,69412)
            )
            GROUP BY ROLLUP(ESCUELA,CODIGO||' - '||ALUMNO ),ID_MES
            )
            PIVOT (SUM(INGRESO) AS INGRESO,SUM(DEBITO) AS DEBITO,SUM(SALDO) AS SALDO ,SUM(MOROSIDAD) AS MOROSIDAD,SUM(ACUMULADO) AS ACUMULADO
            FOR ID_MES IN(".$pivot.")
            )
            ORDER BY ESCUELA,ALUMNO";
                    $oQuery = DB::select($query);
                    return $oQuery;
        }
        public static function addNotasFinancieras($request, $id_usuario_reg, $fecha_registro) {
            // dd($fecha_registro);
            $fecha        = $request->fecha;
            $detalle      = $request->detalle;
            $importe      = $request->importe;
            $estado       = $request->estado;
            $id_persona   = $request->id_persona;
            $id_anho      = $request->id_anho;
            // $count = DB::table('fin_alumno_descuento_vice')
            // ->where('id_persona', $id_persona)
            // ->where('tipo_dscto', $tipo_dscto)
            // ->count();
            // dd($id_persona);
            // if($count == 0) {
            $response=[
                'success'=> false,
                'message'=>'',
            ];
            $carpeta           = $request->carpeta;
            $archivo          = $request->file('file_archivo');
            $tamanho_file     = filesize($archivo);
            $ext_file         = $request->ext_file;

            // dd($capeta, $archivo, $tamanho_file, $ext_file,);

            if ($archivo) {
                // $return = StudentData::createFile($request);
                $storage = new StorageController(); 
                $fileAdjunto = $storage->postFile($archivo, $carpeta);
                $nombre = explode("/",$fileAdjunto['data'])[3];
                if ($fileAdjunto['success']) {
                    // $url_file = $capeta.'/'.$return['filename'];
                    $id_compromiso = ComunData::correlativo('eliseo.fin_compromiso', 'id_compromiso'); 
                    if($id_compromiso>0){
                        $save = DB::table('eliseo.fin_compromiso')->insert(
                            [
                            'id_compromiso'       => $id_compromiso,
                            'fecha'               => $fecha,
                            'detalle'             => $detalle,
                            'importe'             => $importe,
                            'estado'              => $estado,
                            'id_persona'          => $id_persona,
                            'id_anho'             => $id_anho,
                            'id_user'             => $id_usuario_reg,
                            'fecha_reg'           => $fecha_registro,
                            // 'name_file'           => $return['filename'],
                            'name_file'           => $nombre,
                            'ext_file'            => $ext_file,
                            // 'url_file'            => $url_file,
                            'url_file'            => $fileAdjunto['data'],
                            'tamanho_file'        => $tamanho_file,
                            ]
                        );
                        if($save){
                            $response=[
                                'success'=> true,
                                'message'=>'La nota financiera se creo satisfactoriamente',
                            ];
                        }else{
                            $response=[
                                'success'=> false,
                                'message'=>'No se puede insertar',
                            ];
                        }
                    }else{
                        $response=[
                            'success'=> false,
                            'message'=>'No se ha generado correlativo',
                        ];
                    }
                } else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se pudo crear el archivo',
                    ];
                }
            } else {
                $id_compromiso = ComunData::correlativo('eliseo.fin_compromiso', 'id_compromiso'); 
                    if($id_compromiso>0){
                        $save = DB::table('eliseo.fin_compromiso')->insert(
                            [
                            'id_compromiso'       => $id_compromiso,
                            'fecha'               => $fecha,
                            'detalle'             => $detalle,
                            'importe'             => $importe,
                            'estado'              => $estado,
                            'id_persona'          => $id_persona,
                            'id_anho'             => $id_anho,
                            'id_user'             => $id_usuario_reg,
                            'fecha_reg'           => $fecha_registro
                            ]
                        );
                        if($save){
                            $response=[
                                'success'=> true,
                                'message'=>'La nota financiera se creo satisfactoriamente',
                            ];
                        }else{
                            $response=[
                                'success'=> false,
                                'message'=>'No se puede insertar',
                            ];
                        }
                    }else{
                        $response=[
                            'success'=> false,
                            'message'=>'No se ha generado correlativo',
                        ];
                    }
            }

            return $response;
        }
        public static function createFile($request) { //'data_api-finances-students/transferencias'

            $archivo        = $request->file('file_archivo');
            $capeta         = $request->carpeta; //'data_api-finances-students/transferencias'
            $fileAdjunto['nerror']=1;
    
            if ($archivo) {
                $fileAdjunto = ComunData::uploadFile($archivo, $capeta); // servicio que crea la carpeta y guarda el archivo fisico
            } else {
                $fileAdjunto = [
                    'nerror' => 1,
                    'message' => 'No existe el archivo',
                ];
            }
    
            return $fileAdjunto;
        }
        public static function listNotasFinancieras($request) {
            $id_anho = $request->id_anho;
            $id_persona = $request->id_persona;
            // dd($id_persona);
            $query = DB::table('ELISEO.FIN_COMPROMISO A')
                            ->join('MOISES.PERSONA b', 'a.ID_USER', '=', 'b.ID_PERSONA')
                            ->where('A.id_anho', $id_anho)
                            ->where('A.id_persona', $id_persona)
                            ->select('a.ID_COMPROMISO', 'a.DETALLE', 'a.IMPORTE', 'a.ESTADO',
                                    DB::raw("(b.NOMBRE|| ' ' ||b.PATERNO|| ' ' ||b.MATERNO) as usuario"),
                                    DB::raw("to_char(a.FECHA, 'YYYY-MM-DD') as fecha"), 'a.name_file', 'a.ext_file', 'a.url_file')
                            ->orderBy('A.FECHA_REG', 'desc')
                            ->get();    
            return $query;
        }
        public static function updateNotasFinancieras($id_compromiso, $request) {
            $estado            = $request->estado;
        
            $result = DB::table('ELISEO.FIN_COMPROMISO')
            ->where('id_compromiso', $id_compromiso)
            ->update(
                [
                'estado'                              => $estado,
                ]
            );
            if($result){
                $response=[
                    'success'=> true,
                    'message'=>'Se actualizo satisfactoriamente',
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede actualizar',
                ];
            }
            return $response;
            }
            public static function listProrroga($request) {
                $id_anho = $request->id_anho;
                $id_persona = $request->id_persona;
                // dd($id_persona);
                $query = DB::table('ELISEO.FIN_LLAVE A')
                                ->join('MOISES.PERSONA b', 'a.ID_USER_REG', '=', 'b.ID_PERSONA')
                                ->where('A.id_anho', $id_anho)
                                ->where('A.id_persona', $id_persona)
                                ->select('a.ID_LLAVE', 'a.MOTIVO', 'a.ESTADO',
                                        DB::raw("(b.NOMBRE|| ' ' ||b.PATERNO|| ' ' ||b.MATERNO) as usuario"),
                                        'A.FECHA_INI', 'A.FECHA_FIN')
                                ->get();    
                return $query;
            }
            public static function listFinancista($id_entidad, $id_depto) {
                $query = DB::table('ELISEO.fin_financista A')
                                ->join('MOISES.PERSONA b', 'a.id_financista', '=', 'b.ID_PERSONA')
                                ->where('A.id_entidad', $id_entidad)
                                ->where('A.id_depto', $id_depto)
                                ->where('A.estado', 'S')
                                ->select('a.id_financista', 'a.ESTADO',
                                        DB::raw("(b.NOMBRE|| ' ' ||b.PATERNO|| ' ' ||b.MATERNO) as usuario"))
                                ->get();    
                return $query;
            }

            public static function addFinancistaMasivo($request, $id_entidad, $id_depto, $id_usuario_reg, $fecha_create) {
                // dd($fecha_registro);
                $id_anho        = $request->id_anho;
                $id_financista    = $request->id_financista;
                $estado           = $request->estado;
                $id_semestre       = $request->id_semestre;
                $detalle           = $request->details;
                $id_sede           = $request->id_sede;

                $depto = DB::table('eliseo.org_sede')->where('id_sede', $id_sede)->select('id_sede', 'id_depto')->first(); 

                foreach ($detalle as $datos) {
                    
                    $items = (object)$datos;

                    $count = DB::table('eliseo.FIN_ASIGNACION')
                            ->where('id_entidad', $id_entidad)
                            ->where('id_depto', $depto->id_depto)
                            ->where('id_anho', $id_anho)
                            ->where('id_escuela', $items->id_escuela)
                            ->where('id_semestre', $id_semestre)
                            ->where('id_cliente', $items->id_persona)
                            ->where('estado', '1')
                            ->count();
                if ($count > 0) {
                    $estados = '0';
                    $objet = DB::table('eliseo.FIN_ASIGNACION')
                            ->where('id_entidad', $id_entidad)
                            ->where('id_depto', $depto->id_depto)
                            ->where('id_anho', $id_anho)
                            ->where('id_escuela',  $items->id_escuela)
                            ->where('id_semestre', $id_semestre)
                            ->where('id_cliente', $items->id_persona)
                            ->where('estado', '1')
                            ->update(
                                [
                                'estado'               => $estados
                                ]);

                }
                    // dd($items);
                    $id_asignacion = ComunData::correlativo('eliseo.FIN_ASIGNACION', 'id_asignacion'); 
                    if($id_asignacion>0){
                        $save = DB::table('eliseo.FIN_ASIGNACION')->insert(
                            [
                            'id_asignacion'    => $id_asignacion,
                            'id_entidad'       => $id_entidad,
                            'id_depto'         => $depto->id_depto,
                            'id_anho'          => $id_anho,
                            'id_escuela'       => $items->id_escuela,
                            'id_cliente'       => $items->id_persona,
                            'id_financista'    => $id_financista,
                            'id_user'          => $id_usuario_reg,
                            'estado'           => $estado,
                            'id_semestre'      => $id_semestre,
                            'fecha_create'      => $fecha_create
                            ]
                        );
                    
                    }else{
                        $response=[
                            'success'=> false,
                            'message'=>'No se ha generado correlativo',
                        ];
                    } 

                }
                if($save){
                    $response=[
                        'success'=> true,
                        'message'=>'Se creo satisfactoriamente',
                    ];
                }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede insertar',
                    ];
                }
              
                return $response;
            }
             public static function generarRecuperacion($request, $id_user, $id_entidad, $id_depto) {
            $id_sede = $request->id_sede;
            $id_mes = $request->id_mes;
            $id_anho = $request->id_anho;
            $nerror = 0;
            $msgerror = '';
            for ($i = 1; $i <= 200; $i++) {
                $msgerror .= '';
            }
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("BEGIN PKG_SALES_FINANCES.SP_RECUPERACION(
                                    :P_ID_ENTIDAD,
                                    :P_ID_DEPTO,
                                    :P_ID_ANHO,
                                    :P_ID_MES,
                                    :P_ID_USER,
                                    :P_ID_SEDE
                                  ); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_MES', $id_mes, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_SEDE', $id_sede, PDO::PARAM_INT);
            $stmt->execute();
            $return = [
                'nerror' => $nerror,
                'msgerror' => $msgerror,
            ];
            return $return;
            }
            public static function indiceRecuperacion($request, $id_entidad, $id_depto){
                $id_sede = $request->id_sede;
                $id_mes = $request->id_mes;
                $id_anho = $request->id_anho;
    
                if ($id_mes == 1) {
                    $pivot = "1";
                } else if ($id_mes == 2) {
                    $pivot = "1,2";
                } else if ($id_mes == 3) {
                    $pivot = "1,2,3";
                }else if ($id_mes == 4) {
                    $pivot = "1,2,3,4";
                }else if ($id_mes == 5) {
                    $pivot = "1,2,3,4,5";
                }else if ($id_mes == 6) {
                    $pivot = "1,2,3,4,5,6";
                }else if ($id_mes == 7) {
                    $pivot = "1,2,3,4,5,6,7";
                }else if ($id_mes == 8) {
                    $pivot = "1,2,3,4,5,6,7,8";
                }else if ($id_mes == 9) {
                    $pivot = "1,2,3,4,5,6,7,8,9";
                }else if ($id_mes == 10) {
                    $pivot = "1,2,3,4,5,6,7,8,9,10";
                }else if ($id_mes == 11) {
                    $pivot = "1,2,3,4,5,6,7,8,9,10,11";
                }else if ($id_mes == 12) {
                    $pivot = "1,2,3,4,5,6,7,8,9,10,11,12";
                }
    
                $query = "SELECT 
                *
                FROM (
                SELECT  
                        DENSE_RANK() OVER (ORDER BY facultad) AS CONTADOR,
                        ID_MES,
                        COUNT(ID_CLIENTE) AS ALUMNOS,
                        ID_FACULTAD,--ID_ESCUELA,
                        FACULTAD,
                        --NVL(FACULTAD,'TOTAL') AS FACULTAD,
                        NVL(ESCUELA,'TOTAL') AS ESCUELA,
                        SUM(INGRESO) AS INGRESO,SUM(DEBITO) AS DEBITO,SUM(SALDO) AS SALDO,
                        ROUND((SUM(SALDO)/(CASE WHEN SUM(DEBITO) = 0 THEN 1 ELSE SUM(DEBITO) END))*100,2) AS MOROSIDAD
                FROM (
                        SELECT 
                                B.ID_CLIENTE,
                                DAVID.FT_FACULTAD_ALUMNO_ID(B.ID_CLIENTE) AS ID_FACULTAD,
                                --A.NOMBRE_FACULTAD AS FACULTAD,
                                --A.NOMBRE_ESCUELA AS ESCUELA,
                                DAVID.FT_FACULTAD_ALUMNO(B.ID_CLIENTE) AS FACULTAD,
                                DAVID.FT_ESCUELA_ALUMNO(B.ID_CLIENTE) AS ESCUELA,
                                B.ID_MES,
                                B.INGRESO,
                                B.DEBITO,
                                B.CREDITO,
                                B.SALDO 
                        FROM REP_RECUPERACION B 
                        WHERE B.ID_ENTIDAD = ".$id_entidad." 
                        AND B.ID_DEPTO = '".$id_depto."'
                        AND B.ID_ANHO = ".$id_anho."
                        AND B.ID_MES <= ".$id_mes."                                                                                                   
                )
                GROUP BY ID_MES,ROLLUP(FACULTAD,ID_FACULTAD,ESCUELA)
                HAVING (CASE WHEN ID_FACULTAD IS NULL AND ESCUELA IS NULL THEN NULL ELSE ID_FACULTAD END) IS NOT NULL
                --HAVING (CASE WHEN ID_FACULTAD IS NULL AND FACULTAD IS NULL THEN 0 ELSE ID_FACULTAD END) IS NOT NULL
                )
                PIVOT (SUM(INGRESO) AS INGRESO,SUM(DEBITO) AS DEBITO,SUM(SALDO) AS SALDO ,SUM(MOROSIDAD) AS MOROSIDAD
                FOR ID_MES IN(".$pivot.")
                )
                ORDER BY FACULTAD,ESCUELA";
                        $oQuery = DB::select($query);
                        return $oQuery;
            }
            public static function indiceRecuperacionDetalle($request, $id_entidad, $id_depto){
                $id_sede = $request->id_sede;
                $id_mes = $request->id_mes;
                $id_anho = $request->id_anho;
                $id_facultad = $request->id_facultad;
                if ($id_mes == 1) {
                    $pivot = "1";
                } else if ($id_mes == 2) {
                    $pivot = "1,2";
                } else if ($id_mes == 3) {
                    $pivot = "1,2,3";
                }else if ($id_mes == 4) {
                    $pivot = "1,2,3,4";
                }else if ($id_mes == 5) {
                    $pivot = "1,2,3,4,5";
                }else if ($id_mes == 6) {
                    $pivot = "1,2,3,4,5,6";
                }else if ($id_mes == 7) {
                    $pivot = "1,2,3,4,5,6,7";
                }else if ($id_mes == 8) {
                    $pivot = "1,2,3,4,5,6,7,8";
                }else if ($id_mes == 9) {
                    $pivot = "1,2,3,4,5,6,7,8,9";
                }else if ($id_mes == 10) {
                    $pivot = "1,2,3,4,5,6,7,8,9,10";
                }else if ($id_mes == 11) {
                    $pivot = "1,2,3,4,5,6,7,8,9,10,11";
                }else if ($id_mes == 12) {
                    $pivot = "1,2,3,4,5,6,7,8,9,10,11,12";
                }
    
                $query = "SELECT 
                *
                FROM (
                SELECT  DENSE_RANK() OVER (ORDER BY ESCUELA) AS CONTADOR,
                        ID_MES,
                        ESCUELA,
                        --ID_CLIENTE,
                        CODIGO||' - '||ALUMNO AS ALUMNO,
                        SUM(INGRESO) AS INGRESO,
                        SUM(DEBITO) AS DEBITO,
                        SUM(SALDO) AS SALDO,
                        ROUND((SUM(SALDO)/(CASE WHEN SUM(DEBITO) = 0 THEN 1 ELSE SUM(DEBITO) END))*100,2) AS MOROSIDAD
                FROM (
                        SELECT 
                                A.ID_CLIENTE,
                                C.CODIGO,
                                B.PATERNO||' '||B.MATERNO||' '||B.NOMBRE AS ALUMNO,
                                --A.NOMBRE_ESCUELA AS ESCUELA,
                                DAVID.FT_ESCUELA_ALUMNO(A.ID_CLIENTE) AS ESCUELA,
                                A.ID_MES,
                                A.INGRESO,
                                A.DEBITO,
                                A.CREDITO,
                                A.SALDO,
                                A.MOROSIDAD     
                        FROM REP_RECUPERACION A LEFT JOIN MOISES.PERSONA B ON A.ID_CLIENTE = B.ID_PERSONA LEFT JOIN MOISES.PERSONA_NATURAL_ALUMNO C ON A.ID_CLIENTE = C.ID_PERSONA
                        WHERE A.ID_ENTIDAD = ".$id_entidad." 
                        AND A.ID_DEPTO = '".$id_depto."'
                        AND A.ID_ANHO = ".$id_anho."
                        AND A.ID_MES <= ".$id_mes."
                        AND DAVID.FT_FACULTAD_ALUMNO_ID(A.ID_CLIENTE) = ".$id_facultad."                                                                                               
                )
                GROUP BY ROLLUP(ESCUELA,CODIGO||' - '||ALUMNO ),ID_MES
                )
                PIVOT (SUM(INGRESO) AS INGRESO,SUM(DEBITO) AS DEBITO,SUM(SALDO) AS SALDO ,SUM(MOROSIDAD) AS MOROSIDAD
                FOR ID_MES IN(".$pivot.")
                )
                ORDER BY ESCUELA--,ALUMNO";
                        $oQuery = DB::select($query);
                        return $oQuery;
            }
  
    public static function verificarExelAlumnos($lista){
        $listaValid = array();
        foreach ($lista as $datos) {
            $items = (object)$datos;
                $data = DB::table('moises.vw_persona_natural_alumno as a')
                ->where('a.codigo', $items->codigo)
                ->select(
                    'a.id_persona', DB::raw("(a.nombre|| ' ' ||a.paterno|| ' ' ||a.materno) as nombres"),
                    'a.codigo',
                    DB::raw("'S' as valida"),
                    DB::raw("$items->importe as importe"),
                    DB::raw("$items->importe_me as importe_me"),
                    DB::raw("'black' as color")
                )
                ->first();

            if (empty($data)) {
                $dato = ['id_persona' => '', 'codigo'=>$items->codigo, 'nombres' => 'Sin registro', 'valida' => 'N', 'importe' => $items->importe, 'importe_me' => $items->importe_me, 'color' => 'red'];
                $listaValid[] =  $dato;
            } else {
                $listaValid[] =  $data;
            }
        }
        return $listaValid;
    }
    public static function verificarExelAlumnosCredito($lista, $id_entidad, $id_depto, $id_anho){
        $listaValid = array();
        foreach ($lista as $datos) {
            $items = (object)$datos;
            $importe = $items->importe;
            $codigo = $items->codigo;
            $id_cliente = $items->id_cliente;
            $importe_me = $items->importe_me;


                $data = DB::table('eliseo.VW_SALES_SALDO as b')
                ->join('MOISES.VW_PERSONA_NATURAL_ALUMNO as a', 'b.ID_CLIENTE', '=', 'a.ID_PERSONA')
                ->where('b.id_entidad', $id_entidad)
                ->where('b.id_depto', $id_depto)
                ->where('b.id_anho', $id_anho)
                ->where('b.id_cliente', $id_cliente)
                //->where('a.codigo', $codigo)
                ->select(
                    'a.id_persona', DB::raw("(a.nombre|| ' ' ||a.paterno|| ' ' ||a.materno) as nombres"),
                    'a.codigo', DB::raw("SUM(TOTAL) AS TT_DEUDA"),
                    DB::raw("'S' as valida"),
                    DB::raw("$importe as importe"),
                    DB::raw("$importe_me as importe_me"),
                    DB::raw("'black' as color")
                )
                ->groupBy('a.ID_PERSONA','a.NOMBRE','a.PATERNO','a.MATERNO','a.CODIGO')
                ->havingraw("SUM(TOTAL) >= $importe")
                ->first();
            if (empty($data)) {
                $dato = ['id_persona' => '', 'codigo'=>$codigo, 'tt_deuda'=> '-', 'nombres' => 'Importes no coinciden', 'valida' => 'N', 'importe' => $importe, 'importe_me' => $importe_me, 'color' => 'red'];
                $listaValid[] =  $dato;
            } else {
                $listaValid[] =  $data;
            }
        }
    return $listaValid;
}
    public static function addSalesImports($id_entidad,$id_depto,$id_user,$id_comprobante,$id_tipoventa,$id_moneda,$glosa,$id_tiponota,$id_cliente,$importe,$importe_me,$id_parent,$id_comprobante_ref,$serie_ref,$numero_ref,$fecha_ref) {
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '';
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SALES_FINANCES.SP_IMPORTAR_VENTAS(
                                :P_ID_ENTIDAD,
                                :P_ID_DEPTO,
                                :P_ID_USER,
                                :P_ID_CLIENTE,
                                :P_ID_COMPROBANTE,
                                :P_ID_MONEDA,
                                :P_GLOSA,
                                :P_IMPORTE,
                                :P_ID_TIPOVENTA,
                                :P_ID_PARENT,
                                :P_ID_COMPROBANTE_REF,
                                :P_ID_TIPONOTA,
                                :P_SERIE_REF,
                                :P_NUMERO_REF,
                                :P_FECHA_REF,
                                :P_ID_VENTA,
                                :P_ID_VDETALLE,
                                :P_ERROR,
                                :P_MSGERROR
                              ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
        $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
        $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipoventa, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PARENT', $id_parent, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_COMPROBANTE_REF', $id_comprobante_ref, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPONOTA', $id_tiponota, PDO::PARAM_INT);
        $stmt->bindParam(':P_SERIE_REF', $serie_ref, PDO::PARAM_STR);
        $stmt->bindParam(':P_NUMERO_REF', $numero_ref, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_REF', $fecha_ref, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'id_venta' => $id_venta,
            'id_vdetalle' => $id_vdetalle,
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
    }
    public static function addSalesSeatsImports($tipo,$id_venta,$id_vdetalle,$id_cuentaaasi,$id_restriccion,$id_ctacte,$id_fondo,$id_depto,$dc,$es_eap,$porcentaje) {
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= "0";
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SALES_FINANCES.SP_IMPORTAR_VENTAS_ASIENTO(
                                :P_TIPO,
                                :P_ID_VENTA,
                                :P_ID_VDETALLE,
                                :P_ID_FONDO,
                                :P_CUENTA,
                                :P_CUENTA_CTE,
                                :P_RESTRICCION,
                                :P_DEPTO,
                                :P_DC,
                                :P_PORCENTAJE,
                                :P_ES_ESCUELA,
                                :P_ERROR,
                                :P_MSGERROR
                              ); end;");
        $stmt->bindParam(':P_TIPO', $tipo, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_VDETALLE', $id_vdetalle, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_FONDO', $id_fondo, PDO::PARAM_INT);
        $stmt->bindParam(':P_CUENTA', $id_cuentaaasi, PDO::PARAM_STR);
        $stmt->bindParam(':P_CUENTA_CTE', $id_ctacte, PDO::PARAM_STR);
        $stmt->bindParam(':P_RESTRICCION', $id_restriccion, PDO::PARAM_STR);
        $stmt->bindParam(':P_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
        $stmt->bindParam(':P_PORCENTAJE', $porcentaje, PDO::PARAM_STR);
        $stmt->bindParam(':P_ES_ESCUELA', $es_eap, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
    }
    public static function showSeatsSales($id_origen,$id_vdetalle){
        $total = null;
        $sql = "SELECT SUM(IMPORTE) AS IMPORTE  FROM CONTA_ASIENTO
                WHERE ID_TIPOORIGEN = ".$id_origen."
                AND ID_ORIGEN = ".$id_vdetalle." ";

        $query = DB::select($sql);
        foreach ($query as $item){
            $total = $item->importe;
        }
        return $total;
    }
    public static function addTransferImports($id_entidad,$id_depto,$id_user,$id_tipoventa,$id_moneda,$glosa,$id_cliente,$importe,$importe_me,$dc) {
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SALES_FINANCES.SP_IMPORTAR_TRANSFERENCIA(
                                :P_ID_ENTIDAD,
                                :P_ID_DEPTO,
                                :P_ID_USER,
                                :P_ID_CLIENTE,
                                :P_ID_MONEDA,
                                :P_GLOSA,
                                :P_IMPORTE,
                                :P_ID_TIPOVENTA,
                                :P_DC,
                                :P_ID_TRANSFERENCIA,
                                :P_ERROR,
                                :P_MSGERROR
                              ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_CLIENTE', $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_MONEDA', $id_moneda, PDO::PARAM_INT);
        $stmt->bindParam(':P_GLOSA', $glosa, PDO::PARAM_STR);
        $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipoventa, PDO::PARAM_INT);
        $stmt->bindParam(':P_DC', $dc, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TRANSFERENCIA', $id_transferencia, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'id_transferencia' => $id_transferencia,
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
    }
    public static function metaLlamadas($request){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $tipo = $request->tipo;
        $sqlTipo ="";
        if($tipo){  $sqlTipo = "and ID_TIPOEVIDENCIA = ".$tipo;  }
        $metaAsig = "SELECT NVL(COUNT(1), 0) AS META FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_ASIGNACION
                            WHERE ID_FINANCISTA = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '1'
                            AND TO_CHAR(FECHA_CREATE,'YYYY-MM-DD') = '".$fecha."')";
        $dato1 = DB::select($metaAsig);
        $llamadasContes = "SELECT NVL(COUNT(1), 0) AS LLAMADA FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_EVIDENCIA
                            WHERE ID_USER = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '1' ".$sqlTipo."
                            AND TO_CHAR(FECHA,'YYYY-MM-DD') = '".$fecha."')";
        $dato2 = DB::select($llamadasContes);
        $llamadasNoContes = "SELECT NVL(COUNT(1), 0) AS LLAMADA FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_EVIDENCIA
                            WHERE ID_USER = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '0' ".$sqlTipo."
                            AND TO_CHAR(FECHA,'YYYY-MM-DD') = '".$fecha."')";
        $dato3 = DB::select($llamadasNoContes);
        $data['asignados'] = $dato1[0]->meta;
        $data['llamadas_contestadas'] =  $dato2[0]->llamada;
        $data['llamadas_no_contestadas'] =  $dato3[0]->llamada;
        return [$data];
    }
    public static function metaLlamadasDetalle($request){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        
        $query = "SELECT  A.ID_ESCUELA, A.ID_CLIENTE, B.NOM_PERSONA, B.CODIGO, C.NOMBRE_ESCUELA
        FROM ELISEO.FIN_ASIGNACION A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_CLIENTE = B.ID_PERSONA
        JOIN DAVID.vw_acad_programa_estudio C ON A.ID_ESCUELA=C.ID_ESCUELA
        WHERE A.ID_FINANCISTA = ".$id_financista."
        AND A.ESTADO = '1'
        AND A.ID_SEMESTRE = ".$id_semestre."
        AND TO_CHAR(A.FECHA_CREATE,'YYYY-MM-DD') = '".$fecha."'
        AND C.ID_SEDE = '".$id_sede."'
        GROUP BY A.ID_ESCUELA, A.ID_CLIENTE, B.NOM_PERSONA, B.CODIGO, C.NOMBRE_ESCUELA
        ORDER BY C.NOMBRE_ESCUELA";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function respondioLlamadasDetalle($request){
        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $estado = $request->estado;
        $tipo = $request->tipo;
        $sqlTipo ="";
        if($tipo){  $sqlTipo = "AND A.ID_TIPOEVIDENCIA = ".$tipo;  }  

        $query = "SELECT A.ID_CLIENTE, B.NOM_PERSONA, B.CODIGO, NVL(COUNT(1), 0) AS CUANTAS_LLAMADAS
        FROM ELISEO.FIN_EVIDENCIA A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_CLIENTE=B.ID_PERSONA
        WHERE A.ID_USER = ".$id_financista."
        AND A.ESTADO = '".$estado."'
        AND A.ID_SEMESTRE = ".$id_semestre."
        AND TO_CHAR(A.FECHA,'YYYY-MM-DD') = '".$fecha."' ".$sqlTipo."
        GROUP BY A.ID_CLIENTE,  B.NOM_PERSONA, B.CODIGO
        ORDER BY NOM_PERSONA";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function metaLlamadasAcumulado($request){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $tipo = $request->tipo;
        $sqlTipo ="";
        if($tipo){  $sqlTipo = "and ID_TIPOEVIDENCIA = ".$tipo;  }  

        $metaAsig = "SELECT NVL(COUNT(1), 0) AS META FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_ASIGNACION
                            WHERE ID_FINANCISTA = ".$id_financista."
                              AND ID_SEMESTRE = ".$id_semestre."
                              AND ESTADO IN ('1','0')
                            AND TO_CHAR(FECHA_CREATE,'YYYY-MM') = '".$fecha."')";
        $dato1 = DB::select($metaAsig);

        $llamadasContes = "SELECT NVL(COUNT(1), 0) AS LLAMADA FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_EVIDENCIA
                            WHERE ID_USER = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '1' ".$sqlTipo."
                            AND TO_CHAR(FECHA,'YYYY-MM') = '".$fecha."')";
        $dato2 = DB::select($llamadasContes);
        $dato2 = DB::select($llamadasContes);

        $dato2 = DB::select($llamadasContes); 

        $llamadasNoContes = "SELECT NVL(COUNT(1), 0) AS LLAMADA FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_EVIDENCIA
                            WHERE ID_USER = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '0' ".$sqlTipo."
                            AND TO_CHAR(FECHA,'YYYY-MM') = '".$fecha."')";
        $dato3 = DB::select($llamadasNoContes);
       
        $data['asignados'] = $dato1[0]->meta;
        $data['llamadas_contestadas'] =  $dato2[0]->llamada;
        $data['llamadas_no_contestadas'] =  $dato3[0]->llamada;
        return [$data];
    }
    public static function metaLlamadasDetalleAcumulado($request){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        
        $query = "SELECT  A.ID_ESCUELA, A.ID_CLIENTE, B.NOM_PERSONA, B.CODIGO, C.NOMBRE_ESCUELA
        FROM ELISEO.FIN_ASIGNACION A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_CLIENTE = B.ID_PERSONA
        JOIN DAVID.vw_acad_programa_estudio C ON A.ID_ESCUELA=C.ID_ESCUELA
        WHERE A.ID_FINANCISTA = ".$id_financista."
        AND A.ID_SEMESTRE = ".$id_semestre."
        AND A.ESTADO IN ('1','0')
        AND TO_CHAR(A.FECHA_CREATE,'YYYY-MM') = '".$fecha."'
        AND C.ID_SEDE = '".$id_sede."'
        GROUP BY A.ID_ESCUELA, A.ID_CLIENTE, B.NOM_PERSONA, B.CODIGO, C.NOMBRE_ESCUELA
        ORDER BY C.NOMBRE_ESCUELA";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function respondioLlamadasDetalleAcumulado($request){
        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $estado = $request->estado;
        $tipo = $request->tipo;
        $sqlTipo ="";
        if($tipo){  $sqlTipo = "and A.ID_TIPOEVIDENCIA = ".$tipo;  }  
        $query = "SELECT A.ID_CLIENTE, B.NOM_PERSONA, B.CODIGO, NVL(COUNT(1), 0) AS CUANTAS_LLAMADAS
        FROM ELISEO.FIN_EVIDENCIA A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_CLIENTE=B.ID_PERSONA
        WHERE A.ID_USER = ".$id_financista."
        AND A.ESTADO = '".$estado."'
        AND A.ID_SEMESTRE = ".$id_semestre."
        AND TO_CHAR(A.FECHA,'YYYY-MM') = '".$fecha."' ".$sqlTipo."
        GROUP BY A.ID_CLIENTE,  B.NOM_PERSONA, B.CODIGO
        ORDER BY NOM_PERSONA";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function metaDeuda($request,  $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        $id_anho = $request->id_anho;
        // --DEUDA / META
        $query = "SELECT
        NVL(SUM(B.TOTAL), 0) AS DEUDA
        FROM FIN_ASIGNACION A JOIN (
        SELECT A.ID_CLIENTE, SUM(A.TOTAL) AS TOTAL
        FROM VW_SALES_MOV A
        WHERE A.ID_ENTIDAD = ".$id_entidad."
        AND A.ID_DEPTO = '".$id_sede."'
        AND A.ID_ANHO = ".$id_anho."
        AND A.ID_TIPOVENTA IN (1,2,3,4)
        GROUP BY ID_CLIENTE
        ) B ON A.ID_CLIENTE = B.ID_CLIENTE
        WHERE A.ID_FINANCISTA = ".$id_financista."
        AND TO_CHAR(A.FECHA_CREATE,'YYYY-MM-DD') = '".$fecha."'";
                $oQuery = DB::select($query);
        // --DEPOSITOS / RECAUDACION
        $query2 = "SELECT
        nvl(SUM(A.IMPORTE), 0) AS DEPOSITOS
        FROM CAJA_DEPOSITO A JOIN FIN_ASIGNACION B ON A.ID_CLIENTE = B.ID_CLIENTE
        WHERE A.ID_ENTIDAD = ".$id_entidad."
        AND A.ID_DEPTO = '".$id_sede."'
        AND A.ID_ANHO = ".$id_anho."
        AND B.ID_FINANCISTA = ".$id_financista."
        AND TO_CHAR(A.FECHA,'YYYY-MM-DD') = '".$fecha."'";
                $oQuery2 = DB::select($query2);
        
        $data['meta_deuda'] = $oQuery[0]->deuda;
        $data['deposito_recaudacion'] = $oQuery2[0]->depositos;

        return [$data];
    }
    public static function metaDeudaAcumulado($request, $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        $id_anho = $request->id_anho;
        // --DEUDA / META
        $query = "SELECT
        NVL(SUM(B.TOTAL), 0) AS DEUDA
        FROM FIN_ASIGNACION A JOIN (
        SELECT A.ID_CLIENTE, SUM(A.TOTAL) AS TOTAL
        FROM VW_SALES_MOV A
        WHERE A.ID_ENTIDAD = ".$id_entidad."
        AND A.ID_DEPTO = '".$id_sede."'
        AND A.ID_ANHO = ".$id_anho."
        AND A.ID_TIPOVENTA IN (1,2,3,4)
        GROUP BY ID_CLIENTE
        ) B ON A.ID_CLIENTE = B.ID_CLIENTE
        WHERE A.ID_FINANCISTA = ".$id_financista."
        AND TO_CHAR(A.FECHA_CREATE,'YYYY-MM') = '".$fecha."'";
                $oQuery = DB::select($query);
        // --DEPOSITOS / RECAUDACION
        $query2 = "SELECT
        nvl(SUM(A.IMPORTE), 0) AS DEPOSITOS
        FROM CAJA_DEPOSITO A JOIN FIN_ASIGNACION B ON A.ID_CLIENTE = B.ID_CLIENTE
        WHERE A.ID_ENTIDAD = ".$id_entidad."
        AND A.ID_DEPTO = '".$id_sede."'
        AND A.ID_ANHO = ".$id_anho."
        AND B.ID_FINANCISTA = ".$id_financista."
        AND TO_CHAR(A.FECHA,'YYYY-MM') = '".$fecha."'";
                $oQuery2 = DB::select($query2);
        
        $data['meta_deuda'] = $oQuery[0]->deuda;
        $data['deposito_recaudacion'] = $oQuery2[0]->depositos;

        return [$data];
    }
    public static function metaPromesadePago($request, $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        $id_anho = $request->id_anho;
         $llamadasContes = "SELECT NVL(COUNT(1), 0) AS LLAMADA FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_EVIDENCIA
                            WHERE ID_USER = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '1'
                            AND TO_CHAR(FECHA,'YYYY-MM-DD') = '".$fecha."')";
        $dato2 = DB::select($llamadasContes);

        $promesa = "SELECT nvl(count(1), 0) as compromiso from (
                     select distinct ID_PERSONA from fin_compromiso where id_user = ".$id_financista."
                     and TO_CHAR(fecha_reg,'YYYY-MM-DD') = '".$fecha."'
                     AND ESTADO IN ('1','0')
                     )";
        $dato1 = DB::select($promesa);

        $promesaCumplio = "SELECT NVL(count(1), 0) as cumplio
                            FROM ELISEO.FIN_COMPROMISO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_PERSONA = B.ID_PERSONA
                            JOIN CAJA_DEPOSITO C ON A.ID_PERSONA = C.ID_CLIENTE
                            WHERE A.ID_USER = ".$id_financista."
                            AND TO_CHAR(A.FECHA_REG,'YYYY-MM-DD') = '".$fecha."'
                            AND A.ESTADO IN ('1','0')
                            AND C.ID_ENTIDAD = ".$id_entidad."
                            AND C.ID_DEPTO = '".$id_sede."'
                            AND C.ID_ANHO = ".$id_anho."
                            AND TO_CHAR(C.FECHA,'YYYY-MM-DD') = '".$fecha."'";
        $dato3 = DB::select($promesaCumplio);

       
        $data['promesa'] = $dato1[0]->compromiso;
        $data['promesa_cumplida'] = $dato3[0]->cumplio;
        $data['llamada_contestada'] = $dato2[0]->llamada;
        return [$data];
    }
    public static function metaPromesadePagoDetalle($request){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $query = "SELECT A.ID_PERSONA, B.NOM_PERSONA, B.CODIGO FROM ELISEO.FIN_COMPROMISO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_PERSONA = B.ID_PERSONA WHERE A.ID_USER = ".$id_financista."
        AND TO_CHAR(A.FECHA_REG,'YYYY-MM-DD') = '".$fecha."'
        AND A.ESTADO IN ('1','0')
        GROUP BY A.ID_PERSONA,  B.NOM_PERSONA, B.CODIGO
        ORDER BY B.NOM_PERSONA";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function metaPromesadePagoDetalleCumplio($request,  $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        $id_anho = $request->id_anho;
        $query = "SELECT A.ID_PERSONA, B.NOM_PERSONA, B.CODIGO,SUM(C.IMPORTE) AS IMPORTE
        FROM ELISEO.FIN_COMPROMISO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_PERSONA = B.ID_PERSONA
        JOIN CAJA_DEPOSITO C ON A.ID_PERSONA = C.ID_CLIENTE
        WHERE A.ID_USER = 20145
        AND TO_CHAR(A.FECHA_REG,'YYYY-MM-DD') = '".$fecha."'
        AND A.ESTADO IN ('1','0')
        AND C.ID_ENTIDAD = ".$id_entidad."
        AND C.ID_DEPTO = '".$id_sede."'
        AND C.ID_ANHO = ".$id_anho."
        AND TO_CHAR(C.FECHA,'YYYY-MM-DD') = '".$fecha."'
        GROUP BY A.ID_PERSONA,  B.NOM_PERSONA, B.CODIGO
        ORDER BY B.NOM_PERSONA";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function metaPromesadePagoAcumulado($request, $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        $id_anho = $request->id_anho;
         $llamadasContes = "SELECT NVL(COUNT(1), 0) AS LLAMADA FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_EVIDENCIA
                            WHERE ID_USER = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '1'
                            AND TO_CHAR(FECHA,'YYYY-MM') = '".$fecha."')";
        $dato2 = DB::select($llamadasContes);

        $promesa = "SELECT nvl(count(1), 0) as compromiso from (
                     SELECT distinct ID_PERSONA from fin_compromiso where id_user = ".$id_financista."
                     AND TO_CHAR(fecha_reg,'YYYY-MM') = '".$fecha."'
                     AND ESTADO IN ('1','0')
                     )";
        $dato1 = DB::select($promesa);
        
        $promesaCumplioAcumulado = "SELECT NVL(count(1), 0) as cumplio
                            FROM ELISEO.FIN_COMPROMISO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_PERSONA = B.ID_PERSONA
                            JOIN CAJA_DEPOSITO C ON A.ID_PERSONA = C.ID_CLIENTE
                            WHERE A.ID_USER = ".$id_financista."
                            AND TO_CHAR(A.FECHA_REG,'YYYY-MM') = '".$fecha."'
                            AND A.ESTADO IN ('1','0')
                            AND C.ID_ENTIDAD = ".$id_entidad."
                            AND C.ID_DEPTO = '".$id_sede."'
                            AND C.ID_ANHO = ".$id_anho."
                            AND TO_CHAR(C.FECHA,'YYYY-MM') = '".$fecha."'";
        $dato3 = DB::select($promesaCumplioAcumulado);
       
        $data['promesa_acumulado'] = $dato1[0]->compromiso;
        $data['promesa_acumulado_cumplida'] = $dato3[0]->cumplio;
        $data['llamada_contestada'] = $dato2[0]->llamada;
        return [$data];
    }
    public static function metaPromesadePagoDetalleAcumulado($request){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $query = "SELECT A.ID_PERSONA, B.NOM_PERSONA, B.CODIGO FROM ELISEO.FIN_COMPROMISO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_PERSONA = B.ID_PERSONA WHERE A.ID_USER = ".$id_financista."
        AND TO_CHAR(A.FECHA_REG,'YYYY-MM') = '".$fecha."'
        AND A.ESTADO IN ('1','0')
        GROUP BY A.ID_PERSONA,  B.NOM_PERSONA, B.CODIGO
        ORDER BY B.NOM_PERSONA";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function metaPromesadePagoDetalleAcumuladoCumplido($request,  $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        $id_anho = $request->id_anho;
        $query = "SELECT A.ID_PERSONA, B.NOM_PERSONA, B.CODIGO,SUM(C.IMPORTE) AS IMPORTE
        FROM ELISEO.FIN_COMPROMISO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_PERSONA = B.ID_PERSONA
        JOIN CAJA_DEPOSITO C ON A.ID_PERSONA = C.ID_CLIENTE
        WHERE A.ID_USER = 20145
        AND TO_CHAR(A.FECHA_REG,'YYYY-MM') = '".$fecha."'
        AND A.ESTADO IN ('1','0')
        AND C.ID_ENTIDAD = ".$id_entidad."
        AND C.ID_DEPTO = '".$id_sede."'
        AND C.ID_ANHO = ".$id_anho."
        AND TO_CHAR(C.FECHA,'YYYY-MM') = '".$fecha."'
        GROUP BY A.ID_PERSONA,  B.NOM_PERSONA, B.CODIGO
        ORDER BY B.NOM_PERSONA";
                $oQuery = DB::select($query);
        return $oQuery;
    }


    public static function metaFinanciamiento($request, $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        
         $llamadasContes = "SELECT NVL(COUNT(1), 0) AS LLAMADA FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_EVIDENCIA
                            WHERE ID_USER = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '1'
                            AND TO_CHAR(FECHA,'YYYY-MM-DD') = '".$fecha."')";
        $dato2 = DB::select($llamadasContes);

        $financiamientp = "SELECT nvl(count(1), 0) as convenio from (
                    SELECT distinct ID_CLIENTE from FIN_CONVENIO where ID_PERSONA = ".$id_financista."
                     and ID_ENTIDAD =  ".$id_entidad." 
                     and ID_DEPTO like '".$id_sede."%'
                     and ID_SEMESTRE = ".$id_semestre."
                    and TO_CHAR(fecha,'YYYY-MM-DD') = '".$fecha."'
                    and estado = '1'
                    )";
        $dato1 = DB::select($financiamientp);

       
        $data['convenio'] = $dato1[0]->convenio;
        $data['llamada_contestada'] = $dato2[0]->llamada;
        return [$data];
    }
    public static function metaFinanciamientoDetalle($request, $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $id_sede = $request->id_sede;
        $fecha = $request->fecha;
        $query = "SELECT A.ID_PERSONA, B.NOM_PERSONA, B.CODIGO FROM ELISEO.FIN_CONVENIO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_CLIENTE = B.ID_PERSONA WHERE A.ID_PERSONA = ".$id_financista."
        AND TO_CHAR(A.FECHA,'YYYY-MM-DD') = '".$fecha."'
        and ID_ENTIDAD =  ".$id_entidad." 
        and ID_DEPTO like '".$id_sede."%'
        AND A.ESTADO = '1'
        GROUP BY A.ID_PERSONA,  B.NOM_PERSONA, B.CODIGO
        ORDER BY B.NOM_PERSONA";
                $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function metaFinanciamientoAcumulado($request, $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $fecha = $request->fecha;
        $id_sede = $request->id_sede;
        
         $llamadasContes = "SELECT NVL(COUNT(1), 0) AS LLAMADA FROM (
                            SELECT distinct ID_CLIENTE
                            FROM FIN_EVIDENCIA
                            WHERE ID_USER = ".$id_financista."
                              and ID_SEMESTRE = ".$id_semestre."
                              and ESTADO = '1'
                            AND TO_CHAR(FECHA,'YYYY-MM') = '".$fecha."')";
        $dato2 = DB::select($llamadasContes);

        $financiamientp = "SELECT nvl(count(1), 0) as convenio from (
                    SELECT distinct ID_CLIENTE from FIN_CONVENIO where ID_PERSONA = ".$id_financista."
                     and ID_ENTIDAD =  ".$id_entidad." 
                     and ID_DEPTO like '".$id_sede."%'
                     and ID_SEMESTRE = ".$id_semestre."
                    and TO_CHAR(fecha,'YYYY-MM') = '".$fecha."'
                    and estado = '1'
                    )";
        $dato1 = DB::select($financiamientp);

       
        $data['convenio'] = $dato1[0]->convenio;
        $data['llamada_contestada'] = $dato2[0]->llamada;
        return [$data];
    }
    public static function metaFinanciamientoDetalleAcumulado($request, $id_entidad){

        $id_financista = $request->id_financista;
        $id_semestre = $request->id_semestre;
        $id_sede = $request->id_sede;
        $fecha = $request->fecha;
        $query = "SELECT A.ID_PERSONA, B.NOM_PERSONA, B.CODIGO FROM ELISEO.FIN_CONVENIO A JOIN MOISES.VW_PERSONA_NATURAL_ALUMNO B ON A.ID_CLIENTE = B.ID_PERSONA WHERE A.ID_PERSONA = ".$id_financista."
        AND TO_CHAR(A.FECHA,'YYYY-MM') = '".$fecha."'
        and ID_ENTIDAD =  ".$id_entidad." 
        and ID_DEPTO like '".$id_sede."%'
        AND A.ESTADO = '1'
        GROUP BY A.ID_PERSONA,  B.NOM_PERSONA, B.CODIGO
        ORDER BY B.NOM_PERSONA";
                $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function reporteDescuentoBecas($request){
        $id_semestre = $request->id_semestre;
        $id_sede = $request->id_sede;
        $id_nivel_ensenanza = $request->id_nivel_ensenanza;
        $id_modo_contrato = $request->id_modo_contrato;
        $id_modalidad_estudio = $request->id_modalidad_estudio;
        $query = "SELECT
        a.id_alumno_contrato,
        a.id_persona,
        c.codigo,
        (b.nombre|| ' ' ||b.paterno|| ' ' ||b.materno) as nombres, ps.id_sede,
        ps.NOMBRE_FACULTAD,
        ps.NOMBRE_ESCUELA,
        david.ft_calcular_ciclo_programa(s.semestre,a.id_persona,a.id_plan_programa) as ciclo,
        pp.nombre as plan_apgo,
        dd.nombre as descripcion,
        case when dd.dc='D' then dd.importe else 0 end as cobranza,
        case when dd.dc='C' then dd.importe else 0 end as descuento
        from DAVID.ACAD_ALUMNO_CONTRATO a,
        VW_MAT_ALUMNO_CONTRATO_DET dd,
        moises.persona b,
        moises.persona_natural_alumno c,
        david.acad_semestre_programa d ,
        david.Acad_Matricula_Detalle md,
        david.vw_acad_programa_estudio ps,
        david.acad_semestre s,
        MAT_PLANPAGO_SEMESTRE pps,
        MAT_PLANPAGO pp
        where a.id_alumno_contrato=dd.id_alumno_contrato
        and a.id_persona=b.id_persona
        and b.id_persona=c.id_persona
        and a.id_semestre_programa=d.id_semestre_programa
        and a.Id_Matricula_Detalle=md.Id_Matricula_Detalle
        and ps.id_programa_estudio=d.id_programa_estudio
        and d.id_semestre=s.id_semestre
        and pps.ID_PLANPAGO_SEMESTRE=a.ID_PLANPAGO_SEMESTRE
        and pps.ID_PLANPAGO=pp.ID_PLANPAGO
        and d.id_semestre= ".$id_semestre."
        and ps.id_sede='".$id_sede."'
        and a.estado='1'
        and md.Id_Modo_Contrato=".$id_modo_contrato."
        and md.ID_NIVEL_ENSENANZA=".$id_nivel_ensenanza."
        and ps.ID_MODALIDAD_ESTUDIO=".$id_modalidad_estudio."
        and dd.tiene_hijo=0
        and a.id_alumno_contrato in(
            select x.id_alumno_contrato from mat_alumno_contrato_det x  where   x.dc='C'
        )
        order by a.id_alumno_contrato, dd.dc desc, dd.orden";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function searchPersons($person) {
        $query = DB::table('MOISES.PERSONA A')
                ->join('MOISES.PERSONA_DOCUMENTO B', 'A.ID_PERSONA', '=', DB::raw("B.ID_PERSONA AND B.ES_ACTIVO = 1"))
                ->whereraw("B.NUM_DOCUMENTO LIKE '%".$person."%' 
                or upper(A.NOMBRE|| ' '  || A.PATERNO|| ' ' ||A.MATERNO) LIKE upper('%".$person."%')
                or upper(A.PATERNO|| ' ' ||A.MATERNO|| ' ' ||A.NOMBRE) LIKE upper('%".$person."%')")
                ->select(DB::raw("(A.NOMBRE|| ' ' ||A.PATERNO|| ' ' ||A.MATERNO) AS NOMBRE"), 'B.NUM_DOCUMENTO', 'A.ID_PERSONA')
                ->get();
        return $query;
    }
    public static function anexo() {
        $query = DB::table('ELISEO.FIN_ANEXO A')
                ->where('A.ESTADO', '1')
                ->select('A.ID_ANEXO', 'A.XML', 'A.ESTADO')
                ->orderBy('A.id_anexo')
                ->get();
                return $query;
    }
    public static function AgregarFinancista($request) {
        // dd($fecha_registro);
        $id_entidad        = $request->id_entidad;
        $id_depto          = $request->id_depto;
        $id_persona        = $request->id_persona;
        $id_anexo          = $request->id_anexo;
        $estado            = $request->estado;
        if ($id_anexo) {
            $anexo = DB::table('eliseo.fin_financista')
            ->where('id_anexo', $id_anexo)
            ->count();
        } else {
            $anexo = 0;
        }
        if ($anexo == 0) {
        $count = DB::table('eliseo.fin_financista')
        ->where('id_financista', $id_persona)
        ->where('id_entidad', $id_entidad)
        ->where('id_depto', $id_depto)
        ->count();

        if($count == 0) {
    
        $id_financista = $id_persona; 
        if($id_financista>0){
            $save = DB::table('eliseo.fin_financista')->insert(
                [
                'id_financista'      => $id_financista,
                'id_entidad'         => $id_entidad,
                'id_depto'           => $id_depto,
                'estado'             => $estado,
                'id_anexo'           => $id_anexo,

                ]
            );
            if($save){
                $response=[
                    'success'=> true,
                    'message'=>'Se creo satisfactoriamente',
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede insertar',
                ];
            }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede asignar menor a cero',
            ];
        } 
    } else{
        $response=[
            'success'=> false,
            'message'=>'El financista ya se encuentra registrado',
        ];
    } 
} else{
    $response=[
        'success'=> false,
        'message'=>'El anexo ya se encuentra registrado',
    ];
}
    return $response;
    }
    public static function listFinancistaAnexo($id_entidad, $id_depto) {
        $query = DB::table('ELISEO.fin_financista a')
                        ->join('MOISES.PERSONA b', 'a.id_financista', '=', 'b.ID_PERSONA')
                        ->leftjoin('ELISEO.FIN_ANEXO c', 'a.id_anexo', '=', 'c.id_anexo')
                        ->join('MOISES.PERSONA_DOCUMENTO d', 'b.ID_PERSONA', '=', 'd.ID_PERSONA')
                        ->where('a.id_entidad', $id_entidad)
                        ->where('a.id_depto', $id_depto)
                        ->where('a.estado', 'S')
                        ->where('d.es_activo', '1')
                        ->select('a.id_financista', 'a.ESTADO', 'a.id_anexo', 'c.xml', 'a.id_entidad', 'a.id_depto', 'd.num_documento',
                                DB::raw("(b.NOMBRE|| ' ' ||b.PATERNO|| ' ' ||b.MATERNO) as usuario"))
                        ->orderBy('b.NOMBRE')
                        ->get();    
        return $query;
    }
    public static function saveAnexo($request) {
        // dd($fecha_registro);
        $id_anexo          = $request->id_anexo;
        $estado            = $request->estado;
        $xml               = 'upeu'.$id_anexo.'.'.'xml';
        $count = DB::table('eliseo.fin_anexo')
        ->where('id_anexo', $id_anexo)
        ->count();
        // dd($count);
        if($count == 0) {
    
        $id_anexo = $id_anexo; 
        if($id_anexo>0){
            $save = DB::table('eliseo.fin_anexo')->insert(
                [
                'estado'             => $estado,
                'id_anexo'           => $id_anexo,
                'xml'                => $xml,
                ]
            );
            if($save){
                $response=[
                    'success'=> true,
                    'message'=>'Se creo satisfactoriamente',
                    'data'=> DB::table('eliseo.fin_anexo')->where('id_anexo', $id_anexo)->select('id_anexo', 'xml')->first(),
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede insertar',
                    'data'=> '',
                ];
            }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede asignar menor a cero',
                'data'=> '',
            ];
        } 
    } else{
        $response=[
            'success'=> false,
            'message'=>'El anxo ya se encuentra registrado',
            'data'=> '',
        ];
    } 
        return $response;
    }
    public static function deleteAnexo($id_anexo) {
        $count = DB::table('eliseo.fin_financista')
                     ->where('id_anexo', $id_anexo)
                     ->count();
        // dd($count);
        if ($count == 0) {
            $delete = DB::table('eliseo.fin_anexo')
            ->where('id_anexo', $id_anexo)
            ->delete();

        if($delete){
            $response=[
                'success'=> true,
                'message'=>'La se elimino satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar',
            ];
        }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No puede eliminar, porque el anexo se esta usando',
            ];
        }   
        return $response;
    }
    public static function updateFinancista($id_financista, $request) {
        // dd($fecha_registro);
        $id_entidad        = $request->id_entidad;
        $id_depto          = $request->id_depto;
        $id_anexo          = $request->id_anexo;
        $estado            = $request->estado;
     if ($id_anexo) {
         $anexo = DB::table('eliseo.fin_financista')
         ->where('id_anexo', $id_anexo)
         ->count();
     } else {
        $anexo = 0;
     }

        if ($anexo == 0) {
        $count = DB::table('eliseo.fin_financista')
        ->where('id_financista', $id_financista)
        ->where('id_entidad', $id_entidad)
        ->where('id_depto', $id_depto)
        ->where('id_anexo', $id_anexo)
        ->count();

        if($count == 0) {
            $save = DB::table('eliseo.fin_financista')
                ->where('id_financista', $id_financista)
                ->update(
                [
                'id_entidad'         => $id_entidad,
                'id_depto'           => $id_depto,
                'estado'             => $estado,
                'id_anexo'           => $id_anexo,
                ]
            );
        if($save){
                $response=[
                    'success'=> true,
                    'message'=>'Se modifico satisfactoriamente',
                ];
        }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede modificar',
                ];
        }
        } else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede modificar al mismo financista',
                ];
        } 
        } else{
                $response=[
                    'success'=> false,
                    'message'=>'Ya existe el anexo',
                ];
            } 
        return $response;
    }
    public static function deleteFinancista($id_financista) {

        $delete = DB::table('eliseo.fin_financista')
            ->where('id_financista', $id_financista)
            ->delete();

        if($delete){
            $response=[
                'success'=> true,
                'message'=>'La se elimino satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar',
            ];
        }  
        return $response;
    }
    public static function showFinancistaAnexo($id_financista, $id_entidad, $id_depto) {
        // dd($id_financista, $id_entidad, $id_depto);
        $query = DB::table('ELISEO.fin_financista a')
                        ->leftjoin('ELISEO.FIN_ANEXO c', 'a.id_anexo', '=', 'c.id_anexo')
                        ->where('a.id_financista', $id_financista)
                        ->where('a.id_entidad', $id_entidad)
                        ->where('a.id_depto', '1')
                        ->where('a.estado', 'S')
                        ->select('a.id_financista', 'a.ESTADO', 'a.id_anexo', 'c.xml')
                        ->first();    
        return $query;
    }

    public static function getStudentFinancier($id_alumno){
 
        $query = "SELECT c.paterno||' ' ||c.materno  ||' ' ||c.nombre Fin_nombre, 
                        b.celular Fin_cell, 
                        a.id_semestre semestre_id,
                        d.nombre semestre_nombre,
                        d.codigo semestre_codigo,
                        a.FECHA_CREATE fecha_alta
                FROM   fin_asignacion a 
                        left join fin_financista b ON b.id_financista = a.id_financista 
                        left join moises.persona c ON c.id_persona = a.id_financista 
                        left join david.acad_semestre d on d.id_semestre = a.id_semestre
                WHERE  a.id_cliente = '".$id_alumno."' 
                        AND a.estado = '1'";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listSalesContract($id_cliente, $id_alumno_contrato){
        $query = "SELECT DISTINCT
                        A.ID_VENTA,DAVID.FT_CODIGO_UNIV(A.ID_CLIENTE) AS CODIGO,A.SERIE,A.NUMERO,A.GLOSA,A.FECHA,A.TOTAL,B.ID_ALUMNO_CONTRATO,
                        C.ESTADO,C.TIPO_ALUMNO,C.FECHA_REGISTRO,C.FECHA_ACTUALIZACION,C.ORIGEN,
                        X.LOTE,X.ACTIVO
                    FROM VENTA A JOIN VENTA_DETALLE B ON A.ID_VENTA = B.ID_VENTA 
                    JOIN DAVID.ACAD_ALUMNO_CONTRATO C ON A.ID_CLIENTE = C.ID_PERSONA 
                    AND B.ID_ALUMNO_CONTRATO = C.ID_ALUMNO_CONTRATO
                    JOIN CONTA_VOUCHER X ON A.ID_VOUCHER = X.ID_VOUCHER
                    WHERE ID_CLIENTE = ".$id_cliente."
                    AND B.ID_ALUMNO_CONTRATO = ".$id_alumno_contrato." 
                    AND A.TOTAL <> 0 ";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listSalesTransferContract($id_cliente, $id_alumno_contrato){
        $query = "SELECT 
                            A.ID_TRANSFERENCIA,DAVID.FT_CODIGO_UNIV(A.ID_CLIENTE) AS CODIGO,A.SERIE,A.NUMERO,A.GLOSA,A.FECHA,A.IMPORTE,B.ID_ALUMNO_CONTRATO,
                            C.ESTADO,C.TIPO_ALUMNO,C.FECHA_REGISTRO,C.FECHA_ACTUALIZACION,C.ORIGEN,
                            X.LOTE,X.ACTIVO
                    FROM VENTA_TRANSFERENCIA A JOIN VENTA_TRANSFERENCIA_DETALLE B ON A.ID_TRANSFERENCIA = B.ID_TRANSFERENCIA
                    JOIN DAVID.ACAD_ALUMNO_CONTRATO C ON A.ID_CLIENTE = C.ID_PERSONA 
                    AND B.ID_ALUMNO_CONTRATO = C.ID_ALUMNO_CONTRATO
                    JOIN CONTA_VOUCHER X ON A.ID_VOUCHER = X.ID_VOUCHER
                    WHERE ID_CLIENTE = ".$id_cliente."
                    AND B.ID_ALUMNO_CONTRATO = ".$id_alumno_contrato."  
                    AND A.IMPORTE <> 0 ";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showContractStatus($id_cliente, $id_alumno_contrato){
        $query = "SELECT ID_COMPROBANTE,ESTADO 
                FROM DAVID.ACAD_ALUMNO_CONTRATO 
                WHERE ID_ALUMNO_CONTRATO = ".$id_alumno_contrato." 
                AND ID_PERSONA = ".$id_cliente."  ";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showSalesDeposit($id_venta){
        $query = "SELECT ID_DEPOSITO FROM CAJA_DEPOSITO_DETALLE WHERE ID_VENTA = ".$id_venta."  ";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function CancelContract($id_alumno_contrato,$id_venta,$id_user,$oper) {
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("BEGIN PKG_SALES_FINANCES.SP_CANCEL_CONTRACT(
                                :P_ID_ALUMNO_CONTRATO,
                                :P_ID_VENTA,
                                :P_ID_USER,
                                :P_OPER,
                                :P_ERROR,
                                :P_MSGERROR
                              ); end;");
        $stmt->bindParam(':P_ID_ALUMNO_CONTRATO', $id_alumno_contrato, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_VENTA', $id_venta, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_OPER', $oper, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
    }
    public static function saveMetas($request, $id_user, $fecha_reg) {

        $detalle          = $request->detalle;
        $id_semestre          = $request->id_semestre;
        $id_sede          = $request->id_sede;
        $id_facultad      = $request->id_facultad;
        $estado           = '1';
        foreach ($detalle as $datos) {
            $items = (object)$datos;
            $id_meta = ComunData::correlativo('eliseo.fin_metas', 'id_meta'); 
            if($id_meta > 0){
            $save = DB::table('eliseo.fin_metas')
            ->insert(
            [
              'id_meta'                     => $id_meta,
              'id_semestre'                 => $id_semestre,
              'id_sede'                     => $id_sede,
              'id_semestre_programa'        => $items->id_semestre_programa,
              'id_programa_estudio'         => $items->id_programa_estudio,
              'id_facultad'                 => $id_facultad,
              'id_escuela'                  => $items->id_escuela,
              'cantidad_alumnos'            => 0,
              'cantidad_creditos'           => 0,
              'fecha'                       => $fecha_reg,
              'id_user'                     => $id_user,
              'estado'                      => $estado,

            ]);
            if($save){
                    $response=[
                        'success'=> true,
                        'message'=>'Se inserto satisfactoriamente',
                    ];
            }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede insertar',
                    ];
            }
        } else{
            $response=[
                'success'=> false,
                'message'=>'No se puede',
            ];
        }
        }

        return $response;
    }
    public static function getMetasSedes($request) {
        $query = DB::table('eliseo.fin_metas a')
                  ->join('david.VW_ACAD_PROGRAMA_ESTUDIO b', 'a.ID_PROGRAMA_ESTUDIO', '=', 'b.ID_PROGRAMA_ESTUDIO')
                  ->join('david.ACAD_SEMESTRE C', 'A.ID_SEMESTRE', '=', 'C.ID_SEMESTRE')
                  ->join('eliseo.ORG_SEDE D', 'A.ID_SEDE', '=', 'D.ID_SEDE')
                  ->where('a.ID_SEMESTRE', $request->id_semestre)
                  ->where('a.ID_SEDE', $request->id_sede)
                  ->where('a.ID_FACULTAD', $request->id_facultad)
                  ->where('b.ID_TIPO_CONTRATO', $request->id_tipo_contrato)
                  ->select('a.id_semestre_programa', 'a.id_meta', 'b.nombre_facultad', 'b.nombre_escuela',
                           'a.id_sede', 'a.cantidad_alumnos', 'a.cantidad_creditos', 'c.id_semestre', 'c.nombre as nombre_semestre',
                           'd.nombre as nombre_sede', 'b.id_programa_estudio')
                  ->get();
        return $query;
      }
      public static function deleteMetas($id_meta) {

        $convenio = DB::table('eliseo.fin_metas')
                                    ->where('id_meta', $id_meta)
                                    ->delete();
        if($convenio){
            $response=[
                'success'=> true,
                'message'=>'La se elimino satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar',
            ];
        }
        return $response;
    }
    public static function updateMetas($id_semestre, $request) {

        $detalle          = $request->detalle;
        foreach ($detalle as $datos) {
            $items = (object)$datos;
            $cantidad = $items->cantidad_alumnos;
            $creditos = $items->cantidad_creditos;
            if (!$items->cantidad_alumnos) {
                $cantidad = 0;
            }
            if (!$items->cantidad_creditos) {
                $creditos = 0;
            }
            // dd($cantidad, $creditos);
            $save = DB::table('eliseo.fin_metas')
            ->where('id_meta', $items->id_meta)
            ->update(
            [
              'cantidad_alumnos'                  => $cantidad,
              'cantidad_creditos'                 => $creditos,

            ]);
            if($save){
                    $response=[
                        'success'=> true,
                        'message'=>'Se modifico satisfactoriamente',
                    ];
            }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede modifico',
                    ];
            }
        }

        return $response;
    }
    public static function saveDocentes($request, $id_user, $fecha_reg, $id_entidad, $id_depto) {

        $detalle          = $request->detalle;
        $id_semestre      = $request->id_semestre;
        $id_escuela       = $request->id_escuela;
        $id_docente       = $request->id_docente;
        $id_anho          = $request->id_anho; //para los de tipo 'N'
        $estado           = '1';
        $tipo          = $request->tipo;
        $anio          = $request->anio; //para los de tipo 'E'

        if ($tipo == 'N') { //para los de tipo 'N'
                foreach ($detalle as $datos) {
                    $items = (object)$datos;

                    $count = DB::table('eliseo.fin_asignacion_docente')
                                    ->where('id_entidad', $id_entidad)
                                    ->where('id_depto', $id_depto)
                                    ->where('id_anho', $id_anho)
                                    ->where('id_escuela', $id_escuela)
                                    ->where('id_semestre', $id_semestre)
                                    ->where('id_cliente', $items->id_persona)
                                    ->where('estado', '1')
                                    ->count();
                                    // dd($count);
                        if ($count > 0) {
                            $estados = '0';
                            $objet = DB::table('eliseo.fin_asignacion_docente')
                                    ->where('id_entidad', $id_entidad)
                                    ->where('id_depto', $id_depto)
                                    ->where('id_anho', $id_anho)
                                    ->where('id_escuela',  $id_escuela)
                                    ->where('id_semestre', $id_semestre)
                                    ->where('id_cliente', $items->id_persona)
                                    ->where('estado', '1')
                                    ->update(
                                        [
                                        'estado'               => $estados
                                        ]);

                        }
                    $id_asign = ComunData::correlativo('eliseo.fin_asignacion_docente', 'id_asign'); 
                    if($id_asign > 0){
                    $save = DB::table('eliseo.fin_asignacion_docente')
                    ->insert(
                    [
                    'id_asign'                    => $id_asign,
                    'id_entidad'                  => $id_entidad,
                    'id_depto'                    => $id_depto,
                    'id_anho'                     => $id_anho,
                    'id_semestre'                 => $id_semestre,
                    'id_user'                     => $id_user,
                    'id_docente'                  => $id_docente,
                    'id_cliente'                  => $items->id_persona,
                    'id_escuela'                  => $id_escuela,
                    'fecha'                       => $fecha_reg,
                    'estado'                      => $estado,

                    ]);
                
                } else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede',
                    ];
                }
                }
                if($save){
                    $response=[
                        'success'=> true,
                        'message'=>'Se inserto satisfactoriamente',
                    ];
                }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede insertar',
                    ];
                }
                return $response;

        } elseif ($tipo == 'E') { //para los de tipo 'E'

                foreach ($detalle as $datos) {
                    $items = (object)$datos;

                    $count = DB::table('eliseo.fin_asignacion_docente')
                                ->where('id_entidad', $id_entidad)
                                ->where('id_depto', $id_depto)
                                ->where('id_anho', $anio)
                                ->where('id_escuela', $id_escuela)
                                ->where('id_semestre', null)
                                ->where('id_cliente', $items->id_persona)
                                ->where('estado', '1')
                                ->count();
                                // dd($count);
                    if ($count > 0) {
                        $estados = '0';
                        $objet = DB::table('eliseo.fin_asignacion_docente')
                                ->where('id_entidad', $id_entidad)
                                ->where('id_depto', $id_depto)
                                ->where('id_anho', $anio)
                                ->where('id_escuela',  $id_escuela)
                                ->where('id_semestre', null)
                                ->where('id_cliente', $items->id_persona)
                                ->where('estado', '1')
                                ->update(
                                    [
                                    'estado'               => $estados
                                    ]);

                    }
                    $id_asign = ComunData::correlativo('eliseo.fin_asignacion_docente', 'id_asign'); 
                    if($id_asign > 0){
                        $save = DB::table('eliseo.fin_asignacion_docente')
                        ->insert(
                        [
                        'id_asign'                    => $id_asign,
                        'id_entidad'                  => $id_entidad,
                        'id_depto'                    => $id_depto,
                        'id_anho'                     => $anio,
                        'id_user'                     => $id_user,
                        'id_docente'                  => $id_docente,
                        'id_cliente'                  => $items->id_persona,
                        'id_escuela'                  => $id_escuela,
                        'fecha'                       => $fecha_reg,
                        'estado'                      => $estado,

                        ]);
            
                    } else{
                        $response=[
                            'success'=> false,
                            'message'=>'No se puede',
                        ];
                    }
                }
                if($save){
                    $response=[
                        'success'=> true,
                        'message'=>'Se inserto satisfactoriamente',
                    ];
                }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede insertar',
                    ];
                }

            return $response;
        }
    }
    public static function situacionSedesMatricula($request){
        $id_semestre = $request->id_semestre;
        $id_tipo_contrato = $request->id_tipo_contrato;
        $idsTipoContrato = json_decode($id_tipo_contrato);
        $id_sede = $request->id_sede;
        $idsSedes = json_decode($id_sede);
        $credito_cantidad = $request->credito_cantidad;

        $id_modalidad_estudio = $request->id_modalidad_estudio;

        if (empty($credito_cantidad)) {
            $credito_cantidad = 'CANTIDAD';
        }

        $estadoM = '1';
        $estadoEP = '0';
        $estadoCon = 'M';
        $estadoR = '3';

        $sedes = DB::table('eliseo.org_sede')->select('id_sede', 'nombre')->whereNotIn('nombre', ['Otro'])->whereIn('id_sede', $idsSedes)->orderBy('id_sede')->get();
        // dd($sedes);
        $data = array();
        foreach ($sedes as $rows) {
            $row = (object)$rows;
            // dd($row->id_sede);
        $item = array();

        $matriculado = StudentData::grafoss($idsTipoContrato, $id_semestre, $estadoM,  $row->id_sede, $credito_cantidad, $id_modalidad_estudio);
        $en_proceso  = StudentData::grafoss($idsTipoContrato, $id_semestre, $estadoEP,  $row->id_sede, $credito_cantidad, $id_modalidad_estudio);
        $confirmado  = StudentData::grafoss($idsTipoContrato, $id_semestre, $estadoCon, $row->id_sede, $credito_cantidad, $id_modalidad_estudio);
        $retirado    = StudentData::grafoss($idsTipoContrato, $id_semestre, $estadoR,  $row->id_sede, $credito_cantidad, $id_modalidad_estudio);
        $meta    = StudentData::metaSedes($idsTipoContrato, $id_semestre,  $row->id_sede, $credito_cantidad, $id_modalidad_estudio);

        $item['sede'] = $row->nombre;
        $item['matriculado'] = $matriculado->total ? $matriculado->total : 0;
        $item['en_proceso']  = $en_proceso->total ? $en_proceso->total : 0;
        $item['confirmado']  = $confirmado->total ? $confirmado->total : 0;
        $item['retirado']    = $retirado->total ? $retirado->total : 0;
        $item['meta']    = $meta; 
        $data[] = $item;
        }
     
    return $data;
    }
    private static function grafoss($idsTipoContrato, $id_semestre, $estado, $sede, $credito_cantidad, $id_modalidad_estudio){ 
        $q = DB::table('david.acad_alumno_contrato as aac');
            $q->join('david.acad_semestre_programa as asp', 'Asp.Id_Semestre_Programa', '=', DB::raw("Aac.Id_Semestre_Programa and asp.id_semestre=".$id_semestre.""));
            $q->join('david.vw_acad_programa_estudio as ape', 'Ape.Id_Programa_Estudio', '=', 'Asp.Id_Programa_Estudio');
            $q->join('david.Acad_Matricula_Detalle amd', 'amd.Id_Matricula_Detalle', '=', 'aac.Id_Matricula_Detalle');
            $q->where('Amd.Id_Modo_Contrato', '1');
            if (!empty($id_modalidad_estudio)) {
                $q->where('Ape.id_modalidad_estudio', '=', $id_modalidad_estudio);
            }
            $q->whereIn('Ape.Id_Tipo_Contrato', $idsTipoContrato);
            $q->where('Ape.id_Sede', $sede);
            $q->where('aac.Estado', '=', $estado);

            if ($credito_cantidad == 'CANTIDAD') { //obtenemos cantidades de alumnos por estado
                $q->select(DB::raw("nvl(count(Aac.Estado), 10) as total"));
            } else { //obtenemos créditos de alumnos por estado
                // $q->select(DB::raw("sum(nvl(david.ft_creditos_semestre_alumno(aac.id_persona,Aac.Id_Plan_Programa,Asp.Id_Semestre),0)) as total"));
                $q->select(DB::raw("sum(nvl(david.ft_credito_sem_alum_modo(aac.id_persona,Aac.Id_Plan_Programa,Asp.Id_Semestre,Amd.Id_Modo_Contrato,aac.Estado),0)) as total"));
            }

            $query = $q->first();
            return $query;
    }
    private static function metaSedes($idsTipoContrato, $id_semestre, $sede, $credito_cantidad, $id_modalidad_estudio){ 
        $s = DB::table('eliseo.fin_metas as a');
            $s->leftJoin('david.vw_acad_programa_estudio as b', 'a.id_programa_estudio', '=', 'b.id_programa_estudio');
            $s->where('a.id_semestre', '=', $id_semestre);
            $s->whereIn('b.id_tipo_contrato', $idsTipoContrato);
            if (!empty($id_modalidad_estudio)) {
                $s->where('b.id_modalidad_estudio', '=', $id_modalidad_estudio);
            }
            $s->where('a.id_sede', '=', $sede);
            $s->select('a.cantidad_alumnos', 'a.cantidad_creditos');

            if ($credito_cantidad == 'CANTIDAD') { //obtenemos cantidades de alumnos por estado
                $suma = $s->sum('a.cantidad_alumnos');
            } else { //obtenemos créditos de alumnos por estado
                $suma = $s->sum('a.cantidad_creditos');
            }
            return $suma;
    }
   
    public static function listTipoEvidencia( ){
        $query = "SELECT ID_TIPOEVIDENCIA,NOMBRE
                    FROM   tipo_evidencia ";
                $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function listPlanAlumno($id_persona ){
        $query = "select 
        d.nombre_corto,
        d.nombre_certificado,
        d.nombre,
        d.sede,
        d.nombre_facultad,
        d.nombre_escuela,
        d.modalidad_estudio,
        d.id_modalidad_estudio
         from DAVID.acad_alumno_plan a
    inner join DAVID.acad_plan_programa b on b.id_plan_programa =a.id_plan_programa  
    inner join DAVID.vw_acad_programa_estudio d on  d.id_programa_estudio= b.id_programa_estudio
    where  a.id_persona  = '$id_persona' and a.estado = '1'
    order by d.id_nivel_ensenanza ";
                $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deptoProgramStudie($id_programa_estudio) {
        $query = DB::table('DAVID.ACAD_PROGRAMA_ESTUDIO A')
        ->join('ORG_SEDE_AREA B', 'A.ID_SEDEAREA', '=', 'B.ID_SEDEAREA')
        ->leftjoin('CONTA_ENTIDAD_DEPTO C', 'B.ID_ENTIDAD', '=', DB::raw("C.ID_ENTIDAD AND C.ID_DEPTO = B.ID_DEPTO"))
        ->where('A.ID_PROGRAMA_ESTUDIO', $id_programa_estudio)
        ->select('A.ID_PROGRAMA_ESTUDIO', 'B.ID_DEPTO', 'A.NOMBRE')
        ->first();
        return $query;
    }
    public static function saldoSedes($request, $id_entidad){
        $id_anho= $request->id_anho;
        $id_persona= $request->id_persona;
        $id_depto= $request->id_depto;

        $sede = DB::table('david.vw_alumno_plan_programa')->where('id_persona', '=', $id_persona)->where('estado', '=', '1')->select('id_sede', 'sede')->distinct()->first();
        if(!empty($sede)) {
            $id_depto = $sede->id_sede;
        }
        $query = "SELECT
        (CASE WHEN ID_DEPTO = '1' THEN 'SEDE LIMA' WHEN ID_DEPTO = '5' THEN 'SEDE JULIACA' WHEN ID_DEPTO = '6' THEN 'SEDE TARAPOTO' END) AS SEDE,
        NVL(ABS(SUM(TOTAL)),0) AS TOTAL, SIGN(NVL(SUM(TOTAL),0)) AS SIGNO ,
        CASE WHEN SUM(TOTAL) < 0 THEN ABS(SUM(TOTAL)) ELSE 0 END AS CREDITO,
        CASE WHEN SUM(TOTAL) > 0 THEN (SUM(TOTAL)) ELSE 0 END AS DEBITO
        FROM (
        SELECT ID_DEPTO, TOTAL
        FROM VW_SALES_MOV
        WHERE ID_ENTIDAD = ".$id_entidad." AND ID_DEPTO <> '".$id_depto."' AND ID_ANHO = ".$id_anho." AND ID_CLIENTE = ".$id_persona." AND ID_TIPOVENTA IN (1,2,3)
        UNION ALL
        SELECT
                ID_DEPTO,SUM(IMPORTE)*DECODE(SIGN(SUM(IMPORTE)),1,-1,0) AS TOTAL
        FROM VW_SALES_ADVANCES
        WHERE ID_ENTIDAD = ".$id_entidad." AND ID_DEPTO <> '".$id_depto."'  AND ID_ANHO = ".$id_anho." AND ID_CLIENTE = ".$id_persona." GROUP BY ID_DEPTO
        )GROUP BY ID_DEPTO";
                    $oQuery = DB::select($query);
            return $oQuery;
    }
    public static function finishTramiteRegistro($request, $id_user_reg, $id_entidad, $id_depto){
        $id_registro = $request->id_registro;
        $importe = $request->importe;
        $operacion = '123456';
        $ip ='0';
        $id_dinamica = 0;
        $cod_tarjeta = '4';
        $id_persona = $request->id_persona;
        $id_comprobante = '03';
        $id_tipo_venta = '6';
        $retorno = '000000000000000000000000000000';
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PAYONLINE.SP_DEPOSITO_ALUMNO_VISA_ACAD(
                                :P_ID_PERSONA,
                                :P_OPERACION,
                                :P_ID_ENTIDAD,
                                :P_ID_DEPTO, 
                                :P_IMPORTE,
                                :P_IP,
                                :P_ID_DINAMICA,
                                :P_COD_TARJETA,
                                :P_ID_ORIGEN,
                                :P_ID_CLIENTE,
                                :P_ID_COMPROBANTE,
                                :P_ID_TIPOVENTA,
                                :P_ID,
                                :P_ERROR,
                                :P_MSGERROR
                            ); end;");
        $stmt->bindParam(':P_ID_PERSONA', $id_user_reg, PDO::PARAM_INT);
        $stmt->bindParam(':P_OPERACION', $operacion, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_IMPORTE', $importe, PDO::PARAM_STR);
        $stmt->bindParam(':P_IP', $ip, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
        $stmt->bindParam(':P_COD_TARJETA', $cod_tarjeta, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ORIGEN', $id_registro, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_CLIENTE', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_COMPROBANTE', $id_comprobante, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_TIPOVENTA', $id_tipo_venta, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID', $retorno, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'message' => $msgerror,
        ];
        return $return;

    }
    public static function addFinancistaGlobal($request, $id_entidad, $id_depto, $id_usuario_reg, $fecha_create) {
        // dd($fecha_registro);
        $id_anho        = $request->id_anho;
        $id_financista    = $request->id_financista;
        $estado           = $request->estado;
        $id_semestre       = $request->id_semestre;
        $detalle           = $request->details;
        $id_sede           = $request->id_sede;

        $depto = DB::table('eliseo.org_sede')->where('id_sede', $id_sede)->select('id_sede', 'id_depto')->first(); 


        foreach ($detalle as $datos) {
            
            $items = (object)$datos;

            $count = DB::table('eliseo.FIN_ASIGNACION')
                    ->where('id_entidad', $id_entidad)
                    ->where('id_depto', $depto->id_depto)
                    ->where('id_anho', $id_anho)
                    ->where('id_escuela', $items->id_escuela)
                    ->where('id_semestre', $id_semestre)
                    ->where('id_cliente', $items->id_persona)
                    ->where('estado', '1')
                    ->count();
        if ($count > 0) {
            $estados = '0';
            $objet = DB::table('eliseo.FIN_ASIGNACION')
                    ->where('id_entidad', $id_entidad)
                    ->where('id_depto', $depto->id_depto)
                    ->where('id_anho', $id_anho)
                    ->where('id_escuela',  $items->id_escuela)
                    ->where('id_semestre', $id_semestre)
                    ->where('id_cliente', $items->id_persona)
                    ->where('estado', '1')
                    ->update(
                        [
                        'estado'               => $estados
                        ]);

        }
            // dd($items);
            $id_asignacion = ComunData::correlativo('eliseo.FIN_ASIGNACION', 'id_asignacion'); 
            if($id_asignacion>0){
                $save = DB::table('eliseo.FIN_ASIGNACION')->insert(
                    [
                    'id_asignacion'    => $id_asignacion,
                    'id_entidad'       => $id_entidad,
                    'id_depto'         => $depto->id_depto,
                    'id_anho'          => $id_anho,
                    'id_escuela'       => $items->id_escuela,
                    'id_cliente'       => $items->id_persona,
                    'id_financista'    => $id_financista,
                    'id_user'          => $id_usuario_reg,
                    'estado'           => $estado,
                    'id_semestre'      => $id_semestre,
                    'fecha_create'      => $fecha_create
                    ]
                );
            
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se ha generado correlativo',
                ];
            } 

        }
        if($save){
            $response=[
                'success'=> true,
                'message'=>'Se creo satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede insertar',
            ];
        }
      
        return $response;
    }
    static function checkStudentDiscountExcel($params) {
        $resp = array(
            'success' => true,
            'message' => 'ok',
            'data' => []
        );
        try{
            $datin =  \Excel::load($params['file-excel']);
            // dd($datin);
            $data = $datin->toArray();
            $listaValid = array();
            $i = 0;
            $fields = ['codigo', 'semestre', 'descripcion', 'mat_importe', 'mat_porcent', 'ensen_importe', 'ensen_porcent'];
            foreach($data as $d){
                if ($i>=0) {
                    $listaValid[] = self::validateParams($d, $fields);
                }
                $i++;
            }

            $resp['data'] = $listaValid;

        }catch(Exception $e){
            $resp['success'] = false;
            $resp['message'] = $e->getMessage();
            $resp['data'] = [];
        }
        return $resp;
    }
    static function validateParams($item, $fields) {
        $data = array();
        foreach ($fields as $fi){
            $data[$fi] = self::validValid($item, $fi);
            //$id_proyecto = $item->id_proyecto;
        }
        return (object)$data;
    }
    static function validValid($item, $field) {//// yep yeppp
        ///
        //dd($item);
        $resp = collect([
            'title' => $item[$field],
            'value' => $item[$field],
            'alternatives' => [],
            'valid' => false,
            'msg' => 'Entrada inválidad']
        );
        if ($field == 'codigo') {
         $result = DB::table('MOISES.PERSONA_NATURAL_ALUMNO')
             ->select('id_persona AS value', 'codigo AS title')
             ->where('codigo', $item[$field])->get();
         $resp['alternatives'] = $result;
         if($result->count() == 1) {
             $resp['valid'] = true;
             $resp['msg'] = 'ok';
             $resp = $resp->merge($resp['alternatives'][0]);
         } else if($result->count() > 1) {
             $resp['valid'] = false;
             $resp['msg'] = 'Existe mas de una referencia';
         } else if($result->count() == 0) {
             $resp['valid'] = false;
             $resp['msg'] = 'No existe información relacionado';// becas y solicitydes pendientes de aprobadcion
         }

        } else if ($field == 'semestre') {
            $result = DB::table('david.ACAD_SEMESTRE')
                ->select('id_semestre AS value', 'semestre AS title')
                ->where('semestre', $item[$field])->get();
            $resp['alternatives'] = $result;
            
            if($result->count() == 1) {
                $resp['valid'] = true;
                $resp['msg'] = 'ok';
                $resp = $resp->merge($resp['alternatives'][0]);
            } else if($result->count() > 1) {
                $resp['valid'] = false;
                $resp['msg'] = 'Existe mas de una referencia';
            }
        } else if ($field == 'descripcion') {
                $resp['msg'] = 'ok';
                $resp['valid'] = true;
        } else if ($field == 'mat_importe') {
            $oneElemen = count(array_filter([!!$item['mat_porcent'], !!$item[$field]], function ($item) {
                return !$item;
            })) == 1;
            if($oneElemen) {
                if(is_numeric($item[$field]) and ($item[$field] > 0) or !!$item['mat_porcent']) {
                    $resp['msg'] = 'ok';
                    $resp['valid'] = true;
                }
            }
        } else if ($field == 'mat_porcent') {
            $oneElemen = count(array_filter([!!$item['mat_importe'], !!$item[$field]], function ($item) {
                    return !$item;
                })) == 1;
            if($oneElemen) {
                if(is_numeric($item[$field]) and ($item[$field] > 0 and $item[$field] < 101) or !!$item['mat_importe']) {
                    $resp['msg'] = 'ok';
                    $resp['valid'] = true;
                }
            }
        } else if ($field == 'ensen_importe') {
            $oneElemen = count(array_filter([!!$item['ensen_porcent'], !!$item[$field]], function ($item) {
                    return !$item;
                })) == 1;
            if($oneElemen) {
                if(is_numeric($item[$field]) and ($item[$field] > 0) or !!$item['ensen_porcent']) {
                    $resp['msg'] = 'ok';
                    $resp['valid'] = true;
                }
            }
        } else if ($field == 'ensen_porcent') {
            $oneElemen = count(array_filter([!!$item['ensen_importe'], !!$item[$field]], function ($item) {
                    return !$item;
                })) == 1;
            if($oneElemen) {
                if(is_numeric($item[$field]) and ($item[$field] > 0) or !!$item['ensen_importe']) {
                    $resp['msg'] = 'ok';
                    $resp['valid'] = true;
                }
            }
        }
        return $resp;
    }

    public static function ShowIDAlumno($codigo) {
        $id_cliente = "";
        $query = "SELECT ID_PERSONA FROM MOISES.PERSONA_NATURAL_ALUMNO WHERE CODIGO = '".$codigo."' ";
        $oQuery = DB::select($query);
        foreach($oQuery as $row){
            $id_cliente = $row->id_persona;
        }
        return $id_cliente;
    }

    public static function sendMail($request) {


        /*$numDoc = count($data);
        for ($i = 0; $i < $numDoc; $i++) {
            $blade = $data[$i]['path'];
            $pdf = ComunData::generarPdf($blade, $dataPdf, 'base64');
            if ($pdf['success']) {
                $content = $pdf['data'];
            }
            $items = [
                'name' =>  'file' . strval($i + 1) . '.pdf',
                'type' => 'application/pdf',
                'content' => $content
            ];
            array_push($attachments, $items);
        }*/

        $obj = DB::table('aps_certificado')->where('id_certificado',1)->select('sms_username','sms_password')->first();

        $alumnos = $request->alumnos;
        $tipo = $request->tipo;
        $noenvia=0;
        $j=0;
        $enviado=0;
        foreach($alumnos as $x) {
            $r=(object) $x; 
            if($tipo=='ME') {
                
                $msg='UPeU: '.$r->nombre.' este '.$r->fecha_vencimiento.' vence tu '.$r->nombre_cuota.', monto S/ '.number_format($r->importe, 2, '.', ',').' puedes cancelarlo ingresando a tu portal https://upeu.ws/pagueaqui por pago efectivo o través de los bancos y/o agentes con tu código '.$r->codigo.', si ya canceló omita este mensaje';
                if($r->celular) {
                    StudentData::sendSMS($obj->sms_username, $obj->sms_password, $r->celular, $msg);
                    $enviado++;
                }else{
                    $noenvia++;
                }


 
            }else{
                if (filter_var($r->email, FILTER_VALIDATE_EMAIL)) {
                    $htm='UPeU:<br><b>'.$r->nombre.'</b> este <b>'.$r->fecha_vencimiento.'</b> vence tu <b>'.$r->nombre_cuota.'</b>, monto <b>S/ '.number_format($r->importe, 2, '.', ',').'</b> puedes cancelarlo ingresando a tu portal <a href="https://upeu.ws/pagueaqui" target="_blank">https://upeu.ws/pagueaqui</a> por pago efectivo o través de los bancos y/o agentes con tu código <b>'.$r->codigo.'</b>, si ya canceló omita este mensaje';
                    $data = [
                        'from_email' => 'finanzasalumnos@upeu.edu.pe',
                        'from_name' => 'Finanzas Alumnos - Universidad Peruana Unión',
                        'correo'=>$r->email,
                        'html'=> $htm,
                        'asunto'=>'UPEU - Aviso de cobranza ',
                        'attachments'=>'',
                    ];
                    $ret = SendEmail::send($data);
                    if(!$ret['success']){
                        $noenvia++;
                    }else{
                        $enviado++;
                    }
                }else{
                    $noenvia++;
                }
            }
            $j++;
        }
        if($j==0){
            $ret=[
                'success'=>false,
                'message'=>'No hay data'
            ];
            return $ret;
        }
        if($noenvia>0 and $enviado==0) {
            $ret=[
                'success'=>false,
                'message'=>'No se ha enviado ningun correo'
            ];
            return $ret;
        }else{
            if($noenvia>0 and $enviado>0) {
                $ret=[
                    'success'=>true,
                    'message'=>'Se ha enviado correctamente('.$enviado.'), no se ha enviado('.$noenvia.')'
                ];
                return $ret;
            }
        }
        $ret=[
            'success'=>true,
            'message'=>'Se ha enviado correctamente'
        ];
        return $ret;
    }
    public static function sendSMS($username, $password, $celular, $msg)
    {

        //$celular=$request->celular;
        //$msg= $request->msg;


        $values['app'] = 'webservices';
        // $values['u'] = 'upeuerp_sms';
        // $values['p'] = 'UP3UERP2018';
        $values['u'] = $username;
        $values['p'] = $password;
        $values['to'] = $celular;
        $values['msg'] = $msg;


        $data = http_build_query($values);

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            )
        );

        $rpta["mensaje_id"] = 0;
        $rpta["mensaje_estado_rpta"] = 'NO';
        $rpta["destino"] = $celular;

        $url = 'https://www.mensajesonline.pe/sendsms/';
        try {
            $context  = stream_context_create($options);
            $respuesta = file_get_contents($url, false, $context);
            return $respuesta;
        } catch (Exception $e) {
            $rpta["mensaje_id"] = 0;
            $rpta["mensaje_estado_rpta"] = $e->getMessage();
            $rpta["destino"] = $celular;
        }
        return $rpta;
    }
    public static function sendGroupMail() {
        $data = [
            'from_email' => 'noreply-gth@upeu.edu.pe',
            'from_name' => 'GTH-Universidad Peruana Unión',
            'data'=>[
                'sotil07@gmail.com',
                'sotil.yarasca@gmail.com'
            ],
            'html'=>'<b>Hola 3</b>',
            'asunto'=>'Prueba prueba grupo 1',
            'attachments'=>'',
        ];

        $ret = SendEmail::sendGroupMail($data);
        dd($ret);
    }
    public static function getDinamic($request, $id_entidad, $id_depto) {
        $query = DB::table('eliseo.conta_dinamica as a')
        ->where('a.id_entidad', '=', $id_entidad)
        ->where('a.id_depto', '=', $id_depto)
        ->where('a.id_anho', '=', $request->year)
        ->whereIn('a.codigo', ['CL0001','CL0002','CA0003','CM0004'])
        ->where('a.activo', '=', 'S')
        ->select('a.id_dinamica', 'a.nombre', 'a.importe')
        ->get();
        return $query;
    }
    public static function validaPago($id_persona) {
        $count = DB::table('david.vw_alumno_plan_programa as a')
        ->where('a.id_persona', '=', $id_persona)
        ->where('a.id_nivel_ensenanza', '=', 6)
        ->where('a.id_tipo_contrato', '=', 18)
        ->where('a.estado', '=', '1')
        ->count();
        return $count;
    }

}
