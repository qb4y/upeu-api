<?php
/**
 * Created by PhpStorm.
 * Date: 5/29/19
 * Time: 7:12 PM
 */

namespace App\Http\Data\FinancesStudent;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Storage\StorageController;
use App\Http\Data\FinancesStudent\ComunData;
use Illuminate\Support\Facades\DB;
use PDO;
use PhpParser\Node\Expr\Print_;

class ParameterData extends Controller {

  public static function filterEstretegy($id_entidad, $id_depto, $id_anho) {
    $query = DB::table('eliseo.fin_estrategia')
              ->where('id_entidad', $id_entidad)
              ->where('id_depto', $id_depto)
              ->where('id_anho', $id_anho)
              ->select('id_estrategia', 'nombre', 'estado', DB::raw("to_char(fecha_inicio, 'YYYY-MM-DD') as fecha_inicio"), 'id_anho',
              DB::raw("to_char(fecha_fin, 'YYYY-MM-DD') as fecha_fin"),  'codigo')
              ->orderBy('fecha_inicio', 'desc')
              ->get();
    return $query;
  }
  public static function perfilEstadoCuenta($request) {
    $alumno = DB::table('MOISES.VW_PERSONA_NATURAL_ALUMNO A')
    ->where('A.ID_PERSONA', $request->id_persona)
    ->select('A.PATERNO', 'A.MATERNO', 'A.NOMBRE', 'A.CODIGO', 'A.ID_PERSONA')
    ->first();
    $correo = DB::table('MOISES.PERSONA_VIRTUAL X')
    ->where('X.ID_PERSONA', $request->id_persona)
    ->where('X.ID_TIPOVIRTUAL', 1)
    ->where('X.ES_aCTIVO', 1)
    ->select(DB::raw("MAX(X.DIRECCION) AS CORREO"))
    ->first();
    $foto = DB::table('MOISES.PERSONA_NATURAL')
    ->where('ID_PERSONA', $request->id_persona)
    ->select('FOTO',DB::raw("(TO_CHAR(FEC_NACIMIENTO,'DD/MM/YYYY')) AS FEC_NACIMIENTO"))
    ->first();

    $celular = DB::table('MOISES.PERSONA_NATURAL')
    ->where('ID_PERSONA', $request->id_persona)
    ->select('CELULAR')
    ->first();

    $direccion = DB::table('MOISES.PERSONA_DIRECCION')
    ->where('ID_PERSONA', $request->id_persona)
    ->where('ID_TIPODIRECCION', 5)
    ->where('ES_ACTIVO', 1)
    ->select('DIRECCION')
    ->first();


    $datos['alumno'] = $alumno;
    $datos['correo'] = $correo;
    $datos['foto'] = $foto;
    $datos['celular'] = $celular;
    $datos['direccion'] = $direccion;
    return $datos;
  }
  public static function filterFacultadMeta($request) {
 $query = DB::table('david.vw_acad_programa_estudio a')
              ->join('david.acad_semestre_programa b', 'a.id_programa_estudio', '=', DB::raw("b.id_programa_estudio  and b.id_semestre = $request->id_semestre"))
              ->select('a.id_sede', 'a.sede', 'a.id_facultad', 'a.nombre_facultad')
              ->where('a.ID_SEDE', $request->id_sede)
              ->where('a.ID_TIPO_CONTRATO', $request->id_tipo_contrato)
              ->orderBy('a.nombre_facultad')
              ->distinct()
              ->get();
    return $query;
  }
  public static function filterEscuelaMeta($request) {
    $asignados = DB::table('eliseo.fin_metas a')
              ->where('a.ID_SEMESTRE', $request->id_semestre)
              ->where('a.ID_SEDE', $request->id_sede)
              ->where('a.ID_FACULTAD', $request->id_facultad)
              ->pluck('a.id_semestre_programa');
              
    $query = DB::table('david.vw_acad_programa_estudio a')
              ->join('david.acad_semestre_programa b', 'a.id_programa_estudio', '=', DB::raw("b.id_programa_estudio  and b.id_semestre = $request->id_semestre"))
              ->select('a.id_sede', 'a.sede', 'a.id_escuela', 'a.nombre_escuela', 'a.id_programa_estudio', 'b.id_semestre_programa')
              ->where('a.ID_SEDE', $request->id_sede)
              ->where('a.ID_TIPO_CONTRATO', $request->id_tipo_contrato)
              ->where('a.ID_FACULTAD', $request->id_facultad)
              ->whereNotIn('b.id_semestre_programa', $asignados)
              ->orderBy('a.id_escuela')
              ->orderBy('a.nombre_escuela')
              ->distinct()
              ->get();
    return $query;
  }
  public static function searchStudent($search) {
    $query = DB::table('moises.VW_PERSONA_NATURAL_ALUMNO')
        ->whereraw("codigo like '%".$search."%' or num_documento like '%".$search."%' 
        or upper(nombre|| ' '  || paterno|| ' ' ||materno) like upper('%".$search."%')
        or upper(paterno|| ' ' ||materno|| ' ' ||nombre) like upper('%".$search."%')")
        ->select('id_persona', 'codigo', 'nom_persona as nombres', 'num_documento')
        ->get();
        return $query;
  }
  public static function filterSemesterAlumno($request) {
    $id_persona = $request->id_persona;
    $query = DB::table('DAVID.ACAD_ALUMNO_CONTRATO A')
    ->join('DAVID.ACAD_SEMESTRE_PROGRAMA B', 'A.ID_SEMESTRE_PROGRAMA', '=', 'B.ID_SEMESTRE_PROGRAMA')
    ->join('DAVID.ACAD_SEMESTRE C', 'B.ID_SEMESTRE', '=', 'C.ID_SEMESTRE')
    ->where('A.ID_PERSONA', $id_persona)
    ->whereIn('A.ESTADO', array('1','3'))
    ->select('B.ID_SEMESTRE', 'C.NOMBRE', 'C.SEMESTRE')
    ->orderBy('C.SEMESTRE', 'desc')
    ->distinct()
    ->get();
    return $query;
  }
  public static function filterProgramaAlumno($request) {
    $id_persona = $request->id_persona;
    $id_semestre = $request->id_semestre;
    $query = DB::table('DAVID.ACAD_ALUMNO_CONTRATO A')
    ->join('DAVID.ACAD_SEMESTRE_PROGRAMA B', 'A.ID_SEMESTRE_PROGRAMA', '=', 'B.ID_SEMESTRE_PROGRAMA')
    ->join('DAVID.ACAD_PROGRAMA_ESTUDIO C', 'B.ID_PROGRAMA_ESTUDIO', '=', 'C.ID_PROGRAMA_ESTUDIO')
    ->where('A.ID_PERSONA', $id_persona)
    ->where('B.ID_SEMESTRE', $id_semestre)
    ->whereIn('A.ESTADO', array('1','3'))
    ->select('C.ID_PROGRAMA_ESTUDIO', 'C.NOMBRE', 'C.NOMBRE_CORTO', 'B.ID_SEMESTRE_PROGRAMA')
    ->distinct()
    ->get();
    return $query;
  }
  public static function filterContractAlumno($request) {
    $id_persona = $request->id_persona;
    $id_semestre = $request->id_semestre;
    $id_programa_estudio = $request->id_programa_estudio;

    $query = DB::table('DAVID.ACAD_ALUMNO_CONTRATO AC')
    ->join('DAVID.ACAD_SEMESTRE_PROGRAMA SP', 'AC.ID_SEMESTRE_PROGRAMA', '=', 'SP.ID_SEMESTRE_PROGRAMA')
    ->join('DAVID.ACAD_MATRICULA_DETALLE MD', 'AC.ID_MATRICULA_DETALLE', '=', 'MD.ID_MATRICULA_DETALLE')
    ->join('DAVID.MODO_CONTRATO MC', 'MD.ID_MODO_CONTRATO', '=', 'MC.ID_MODO_CONTRATO')
    ->join('DAVID.TIPO_NIVEL_ENSENANZA NV', 'AC.ID_NIVEL_ENSENANZA', '=', 'NV.ID_NIVEL_ENSENANZA')
    ->select(
        'AC.ID_ALUMNO_CONTRATO',
        'MC.NOMBRE AS MODO',
        'AC.CODIGO AS CODIGO_CONTRATO_ALUMNO',
        'MC.CODIGO AS CODIGO_MODO',
        'AC.ESTADO', 'NV.NOMBRE AS NIVEL_ENSENANZA')
    ->where([
        ['AC.ID_PERSONA', '=', $id_persona],
        ['SP.ID_SEMESTRE', '=', $id_semestre],
        ['SP.ID_PROGRAMA_ESTUDIO', '=', $id_programa_estudio],
        //['AC.ESTADO', '=', '1'],
    ])->whereIn('AC.ESTADO',array('1','3'))
    ->get();
    return $query;
  }
  public static function responsableEstudent($id_persona) {
    $query = DB::table('DAVID.ACAD_ALUMNO_CONTRATO A')
        ->join('MOISES.PERSONA B', 'A.ID_PERSONA', '=', 'B.ID_PERSONA')
        ->where('A.ID_RESP_FINANCIERO', $id_persona)
        ->select('A.ID_PERSONA', DB::raw("(B.NOMBRE|| ' ' ||B.PATERNO|| ' ' ||B.MATERNO) AS NOMBRES"))
        ->distinct()
        ->get();
        return $query;
  }

  public static function sedeGraficFilial() {
    $sedes = DB::table('eliseo.org_sede')->select('id_sede', 'nombre')->whereNotIn('nombre', ['Otro'])->orderBy('id_sede')->get();
            return $sedes;
  }
  public static function tipoContratoFiliales($request){
    $id_semestre = $request->id_semestre;
    $idS = json_decode($request->id_sede);
    $idNE = json_decode($request->id_nivel_ensenanza);
    // dd($idNE);
    // dd($valor);
    $q = db::table("david.acad_semestre_programa asp");
    $q->join("david.vw_acad_programa_estudio ape", "ape.id_programa_estudio", "=", "asp.id_programa_estudio");
    $q->join("david.tipo_contrato tc", "tc.id_tipo_contrato", "=", "ape.id_tipo_contrato");
    $q->where('asp.id_semestre', $id_semestre);
    if (count($idS)>0) {
      $q->whereIn("ape.id_sede", $idS);
    }
    if (count($idNE)>0) {
      $q->whereIn('ape.id_nivel_ensenanza', $idNE);
    }
    $q->select('ape.id_tipo_contrato', 'tc.nombre','ape.id_nivel_ensenanza');
    $q->distinct();
    $q->orderBy('ape.id_tipo_contrato');
    $sql = $q->get();
    return $sql;
    }
    public static function tipoModalidadEstudioFiliales($request){
      $id_semestre = $request->id_semestre;
      $idS = json_decode($request->id_sede);
      $idNE = json_decode($request->id_nivel_ensenanza);
      $idTC = json_decode($request->id_tipo_contrato);
      $q = db::table("david.acad_semestre_programa asp");
      $q->join("david.vw_acad_programa_estudio ape", "ape.id_programa_estudio", "=", "asp.id_programa_estudio");
      $q->where('asp.id_semestre', $id_semestre);
      if (count($idS)>0) {
        $q->whereIn("ape.id_sede", $idS);
      }
      if (count($idNE)>0) {
        $q->whereIn('ape.id_nivel_ensenanza', $idNE);
      }
      if (count($idTC)>0) {
        $q->whereIn('ape.id_tipo_contrato', $idTC);
      }
      $q->select('ape.id_modalidad_estudio', 'ape.modalidad_estudio');
      $q->distinct();
      $q->orderBy('ape.id_modalidad_estudio');
      $sql = $q->get();
      return $sql;
    }
    public static function nivelEnsenanza($request){ 
      $id_sede = $request->id_sede;
      $idsSedes = json_decode($id_sede);
      $data = DB::table("david.VW_ACAD_PROGRAMA_ESTUDIO as a")
      ->select("b.*")
      ->distinct()
      ->join("david.TIPO_NIVEL_ENSENANZA as b", "b.ID_NIVEL_ENSENANZA", "=", "a.ID_NIVEL_ENSENANZA")
      ->whereIn("a.ID_SEDE", $idsSedes)
      ->orderBy('b.ID_NIVEL_ENSENANZA')
      ->get();
    return $data;
    }
    public static function postFile($request, $fecha) { //'data_api-finances-students/transferencias'

        $archivo        = $request->file('file_archivo');
        $carpeta         = $request->carpeta; //'data_api-finances-students/transferencias'
        // $fileAdjunto['nerror']=1;

        if ($archivo) {
            // $fileAdjunto = ComunData::uploadFile($archivo, $capeta); // servicio que crea la carpeta y guarda el archivo fisico
            $storage = new StorageController(); 
            $fileAdjunto = $storage->postFile($archivo, $carpeta); // servicio que crea la carpeta y guarda el archivo fisico
            $nombre = explode("/",$fileAdjunto['data'])[3];
            if ($fileAdjunto['success']) {
                $result = ParameterData::saveDatosFile($request, $fecha, $nombre, $fileAdjunto['data']); ///para guardar los datos en la tabla
                if($result['success']){
                    $response = [
                        'success' => $result['success'],
                        'message' => $result['message'],
                    ];
                } else {
                    $response = [
                        'success' => $result['success'],
                        'message' => $result['message'],
                    ];
                }
                
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se pudo crear carpeta del archivo',
                ];
            }
                
        } else {
            $response = [
                'success' => false,
                'message' => 'No existe el archivo',
            ];
        }

        return $response;
    }
    public static function saveDatosFile($request, $fecha, $filename, $url) {
        $archivo         = $request->file('file_archivo');
        $ext_file        = $request->ext_file;
        $id              = $request->id;
        $tipo            = $request->tipo;
        $tamanhio         = filesize($archivo);  
            if ($tipo == 'T') { // TRANSFERENCIA
               $count = DB::table('eliseo.venta_file')->where('id_transferencia', $id)->count();
               if ($count>0) {
                 $delete = DB::table('eliseo.venta_file')->where('id_transferencia', $id)->delete();
                }
                  $sa = DB::table('eliseo.venta_file');
                  $sa->insert(
                      [
                          // 'vfile' => $vfile,
                          'id_transferencia' => $id,
                          'nombre' => $filename,
                          'formato' => $ext_file,
                          'url' => $url,
                          'fecha' => $fecha,
                          'tipo' => $tipo,
                          'tamanho' => $tamanhio,
                          'estado' => '1',
                      ]);
                }

            if ($tipo == 'N') { // Notas de credito debito
              $count = DB::table('eliseo.venta_file')->where('id_venta', $id)->count();
              if ($count>0) {
                $array = DB::table('eliseo.venta_file')->select('url', 'nombre')->where('id_venta','=',$id)->get();
                $storage = new StorageController(); 
                $storage->destroyFile($array[0]->url);
                DB::table('eliseo.venta_file')->where('id_venta', $id)->delete();
              }
              $sa = DB::table('eliseo.venta_file');
              $sa->insert(
                  [
                      // 'vfile' => $vfile,
                      'id_venta' => $id,
                      'nombre' => $filename,
                      'formato' => $ext_file,
                      'url' => $url,
                      'fecha' => $fecha,
                      'tipo' => $tipo,
                      'tamanho' => $tamanhio,
                      'estado' => '1',
                  ]);
              }

            $save = $sa;
            if ($save) {
                $response = [
                    'success' => true,
                    'message' => 'Añadido ok',
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se puede insertar',
                ];
            }
            

        return $response;
    }

    public static function deleteFile($id) {
      return DB::table('eliseo.venta_file')
      ->select('url')
      ->where('id_venta','=',$id)
      ->get();
    }

    public static function codigoAlumnoValid($request, $id_entidad, $id_depto) {

      // $codigo = [201410595, 201410594, 201410593, 201410592];
      $codigo =           $request->codigos;
      $id_anho =          $request->id_anho;
      $id_semestre =      $request->id_semestre;
      $id_sede =          $request->id_sede;
      $id_financista =    $request->id_financista;
      $cod = json_decode($codigo);

      $depto = DB::table('eliseo.org_sede')->where('id_sede', $id_sede)->select('id_sede', 'id_depto')->first(); 

      $semestre_programa = DB::table('david.acad_semestre_programa')->where('id_semestre',$id_semestre)->pluck('id_semestre_programa');

      // dd($cod);
      $listaValid = array();
      // $listaValid = [];
      foreach($cod as $items) {
          // dd($items);
          $data = DB::table('eliseo.vw_persona_natural_alumno as a')
          ->where('codigo', $items)
          ->select('a.id_persona', 'a.codigo', DB::raw("(a.paterno|| ' ' ||a.materno|| ' ' ||a.nombre) as nombres") , DB::raw("'S' as valida"),
          DB::raw("(select count(x.id_asignacion) as existe from eliseo.fin_asignacion x where x.id_entidad=".$id_entidad."  and x.id_depto='".$depto->id_depto."'
                  and x.id_anho=".$id_anho." and x.id_cliente=a.id_persona
                  and x.id_financista=".$id_financista." and x.estado = '1' and x.id_semestre=".$id_semestre.") as asignado"))
          ->first();
          if (!empty($data)) {
            $contract = DB::table('david.acad_alumno_contrato')
                      ->where('id_persona', $data->id_persona)
                     // ->where('estado', '0')
                      ->whereIn('id_semestre_programa', $semestre_programa)
                      ->select('id_alumno_contrato', 'id_persona', 'id_semestre_programa', 'id_plan_programa')
                      ->get();

            if (count($contract)>0) {
              foreach($contract as $ite) {
                $object = (object)$ite;
                $plan_programa = DB::table('david.acad_plan_programa')->where('id_plan_programa', $object->id_plan_programa)->pluck('id_programa_estudio');
                $programa_estudio = DB::table('david.vw_acad_programa_estudio')->whereIn('id_programa_estudio', $plan_programa)->select('*')->first();

                $da = [
                  'id_persona' => $data->id_persona,
                  'codigo'=> $data->codigo,
                  'nombres' => $data->nombres,
                  'valida' => 'S',
                  'asignado' => $data->asignado,
                  'id_semestre' => $id_semestre,
                  'id_plan_programa' => $object->id_plan_programa,
                  'id_programa_estudio' => $programa_estudio->id_programa_estudio,
                  'nombre_facultad' => $programa_estudio->nombre_facultad,
                  'id_facultad' => $programa_estudio->id_facultad,
                  'nombre_escuela' => $programa_estudio->nombre_escuela,
                  'id_escuela' => $programa_estudio->id_escuela,
                  'semestre_valido' => 'S',
                  'nombre_sede' => $programa_estudio->sede,
                  'id_sede' => $programa_estudio->id_sede,
                ];
                $listaValid[] =  $da;
              }
            } else {
              $datos = [
                'id_persona' => $data->id_persona,
                'codigo'=> $data->codigo,
                'nombres' => $data->nombres,
                'valida' => 'S',
                'asignado' => $data->asignado,
                'id_semestre' => '',
                'id_plan_programa' => '',
                'id_programa_estudio' => '',
                'nombre_facultad' => '',
                'id_facultad' => '',
                'nombre_escuela' => '',
                'id_escuela' => '',
                'semestre_valido' => 'N',
                'nombre_sede' => '',
                'id_sede' => '',
              ];
              $listaValid[] =  $datos;
            }

           } else {
            $dato = [
              'id_persona' => '',
              'codigo'=> $items,
              'nombres' => 'No existe',
              'valida' => 'N',
              'asignado' => '0',
              'id_semestre' => '',
              'id_plan_programa' => '',
              'id_programa_estudio' => '',
              'nombre_facultad' => '',
              'id_facultad' => '',
              'nombre_escuela' => '',
              'id_escuela' => '',
              'semestre_valido' => 'N',
              'nombre_sede' => '',
              'id_sede' => '',
            ];
            $listaValid[] =  $dato;
          
          }
      }
      // dd($listaValid);
      return $listaValid;
  }
}