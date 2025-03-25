<?php
/**
 * Created by PhpStorm.
 * User: raul
 * Date: 5/29/19
 * Time: 7:12 PM
 */

namespace App\Http\Data\FinancesStudent;


use App\Http\Controllers\Controller;
use App\Http\Data\FinancesStudent\ComunData;
use Illuminate\Support\Facades\DB;
use PDO;

class StrategyData extends Controller {

        public static function saveStrategia($request) {
          // dd($fecha_registro);
          $id_entidad       = $request->id_entidad;
          $id_depto         = $request->id_depto;
          $nombre           = $request->nombre;
          $codigo           = $request->codigo;
          $fecha_inicio     = $request->fecha_inicio;
          $fecha_fin        = $request->fecha_fin;
          $estado           = $request->estado;
          $id_anho          = $request->id_anho;

          $id_estrategia = ComunData::correlativo('eliseo.fin_estrategia', 'id_estrategia'); 
          if($id_estrategia > 0){
              $save = DB::table('eliseo.fin_estrategia')->insert(
                  [
                  'id_estrategia'           => $id_estrategia,
                  'id_entidad'              => $id_entidad,
                  'id_depto'                => $id_depto,
                  'nombre'                  => $nombre,
                  'codigo'                  => $codigo,
                  'fecha_inicio'            => $fecha_inicio,
                  'fecha_fin'               => $fecha_fin,
                  'estado'                  => $estado,
                  'id_anho'                 => $id_anho,
                  ]
              );
              if($save){
                  $response=[
                      'success'=> true,
                      'message'=>'Se creo satisfactoriamente',
                      'data'=> '',
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
                  'message'=>'No se ha generado',
                  'data'=> '',
              ];
          } 
        
          return $response;
      }
      public static function listStrategia($request) {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $per_page = $request->per_page;

        $query = DB::table('eliseo.fin_estrategia')
                  ->where('id_entidad', $id_entidad)
                  ->where('id_depto', $id_depto)
                  ->where('id_anho', $id_anho)
                  ->select('id_estrategia', 'nombre', 'estado', DB::raw("to_char(fecha_inicio, 'YYYY-MM-DD') as fecha_inicio"), 'id_anho',
                  DB::raw("to_char(fecha_fin, 'YYYY-MM-DD') as fecha_fin"),  'codigo', DB::raw("'' as back"), DB::raw("'' as col"))
                  ->orderBy('fecha_inicio', 'desc')
                  ->paginate((int)$per_page);
        return $query;
      }
      public static function deleteStrategia($id_estrategia) {
        $count = DB::table('eliseo.fin_estrategia_alumno')
                     ->where('id_estrategia', $id_estrategia)
                     ->count();
        // dd($count);
        if ($count == 0) {
            $delete = DB::table('eliseo.fin_estrategia')
            ->where('id_estrategia', $id_estrategia)
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
                'message'=>'No puede eliminar, porque estrategia se esta usando',
            ];
        }   
        return $response;
      }
      public static function updateStrategia($id_estrategia, $request) {
        $id_entidad       = $request->id_entidad;
        $id_depto         = $request->id_depto;
        $nombre           = $request->nombre;
        $codigo           = $request->codigo;
        $fecha_inicio     = $request->fecha_inicio;
        $fecha_fin        = $request->fecha_fin;
        $estado           = $request->estado;
        $id_anho          = $request->id_anho;

        $save = DB::table('eliseo.fin_estrategia')
                ->where('id_estrategia', $id_estrategia)
                ->update(
                [
                  'id_entidad'              => $id_entidad,
                  'id_depto'                => $id_depto,
                  'nombre'                  => $nombre,
                  'codigo'                  => $codigo,
                  'fecha_inicio'            => $fecha_inicio,
                  'fecha_fin'               => $fecha_fin,
                  'estado'                  => $estado,
                  'id_anho'                 => $id_anho,
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
        return $response;
    }
    public static function saveAsignarStrategia($request, $id_financista) {
        $id_persona           = $request->id_persona;
        $ganador              = $request->ganador;
        $fecha                = $request->fecha;
        $id_estrategia        = $request->id_estrategia;

        $count = DB::table('eliseo.fin_estrategia_alumno')
                    ->where('id_persona', $id_persona)
                    ->where('id_estrategia', $id_estrategia)
                    ->count();
        if ($count == 0) {
// dd($id_financista);
        $id_estrategia_alumno = ComunData::correlativo('eliseo.fin_estrategia_alumno', 'id_estrategia_alumno'); 
        if($id_estrategia_alumno > 0){
            $save = DB::table('eliseo.fin_estrategia_alumno')->insert(
                [
                'id_estrategia_alumno'   => $id_estrategia_alumno,
                'id_persona'             => $id_persona,
                'ganador'                => $ganador,
                'fecha'                  => $fecha,
                'id_estrategia'          => $id_estrategia,
                'id_financista'          => $id_financista,
                ]
            );
            if($save){
                $response=[
                    'success'=> true,
                    'message'=>'Se creo satisfactoriamente',
                    'data'=> '',
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
                'message'=>'No se ha generado',
                'data'=> '',
            ];
        }   
    } else {
        $response=[
            'success'=> false,
            'message'=>'El alumno ya tiene asignado la estrategia',
            'data'=> '',
        ];
    }
      
        return $response;
    }
    public static function listStrategiaAsignada($request, $id_entidad, $id_depto) {
        $id_persona = $request->id_cliente;
        $id_anho = $request->id_anho;
        $query = DB::table('ELISEO.FIN_ESTRATEGIA_ALUMNO A')
                  ->join('ELISEO.FIN_ESTRATEGIA B', 'A.ID_ESTRATEGIA', '=', 'B.ID_ESTRATEGIA')
                  ->join('MOISES.PERSONA C', 'A.ID_FINANCISTA', '=', 'C.ID_PERSONA')
                  ->join('MOISES.VW_PERSONA_NATURAL_ALUMNO D', 'A.ID_PERSONA', '=', 'D.ID_PERSONA')
                  ->where('A.ID_PERSONA', $id_persona)
                  ->where('B.ID_ENTIDAD', $id_entidad)
                  ->where('B.ID_DEPTO', $id_depto)
                  ->where('B.ID_ANHO', $id_anho)
                  ->where('B.ESTADO', '1')
                  ->select('A.ID_ESTRATEGIA_ALUMNO', 'A.GANADOR', 
                  DB::raw("to_char(A.FECHA, 'YYYY-MM-DD') AS FECHA"),
                  'A.ID_FINANCISTA', 'A.ID_PERSONA', 'B.NOMBRE AS NOMBRE_ESTRATEGIA', 'B.CODIGO',
                   DB::raw("to_char(B.FECHA_INICIO, 'YYYY-MM-DD') AS FECHA_INICIO"), 
                  DB::raw("to_char(B.FECHA_FIN, 'YYYY-MM-DD') AS FECHA_FIN"), 'B.ESTADO', 
                  DB::raw("(C.NOMBRE|| ' ' ||C.PATERNO|| ' ' ||C.MATERNO) AS NOMBRE_FINANCISTA"), 
                  'D.NOM_PERSONA AS NOMBRE_ALUMNO')
                  ->orderBy('A.FECHA', 'desc')
                  ->get();
        return $query;
      }
      public static function deleteStrategiaAsignada($id_estrategia_alumno) {
            $delete = DB::table('eliseo.fin_estrategia_alumno')
            ->where('id_estrategia_alumno', $id_estrategia_alumno)
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
      public static function detailStrategiaAsignada($request) {
        $id_estrategia = $request->id_estrategia;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;

        $query = DB::table('ELISEO.FIN_ESTRATEGIA_ALUMNO A')
                  ->join('ELISEO.FIN_ESTRATEGIA B', 'A.ID_ESTRATEGIA', '=', 'B.ID_ESTRATEGIA')
                  ->join('MOISES.PERSONA C', 'A.ID_FINANCISTA', '=', 'C.ID_PERSONA')
                  ->join('MOISES.VW_PERSONA_NATURAL_ALUMNO D', 'A.ID_PERSONA', '=', 'D.ID_PERSONA')
                  ->where('A.ID_ESTRATEGIA', $id_estrategia)
                  ->where('B.ID_ENTIDAD', $id_entidad)
                  ->where('B.ID_DEPTO', $id_depto)
                  ->where('B.ID_ANHO', $id_anho)
                  ->select('A.ID_ESTRATEGIA_ALUMNO', 'A.GANADOR', 
                  DB::raw("to_char(A.FECHA, 'YYYY-MM-DD') AS FECHA"),
                  'A.ID_FINANCISTA', 'A.ID_PERSONA', 'B.NOMBRE AS NOMBRE_ESTRATEGIA', 'B.CODIGO',
                   DB::raw("to_char(B.FECHA_INICIO, 'YYYY-MM-DD') AS FECHA_INICIO"), 
                  DB::raw("to_char(B.FECHA_FIN, 'YYYY-MM-DD') AS FECHA_FIN"), 'B.ESTADO', 
                  DB::raw("(C.NOMBRE|| ' ' ||C.PATERNO|| ' ' ||C.MATERNO) AS NOMBRE_FINANCISTA"),
                  DB::raw("DAVID.FT_PLANP_ALUMNO_NAME(D.ID_PERSONA) as ESCUELA"),
                  DB::raw("D.CODIGO as CODIGO_UNIV"),
                  DB::raw("ELISEO.FC_SALDO_ALUMNO(D.ID_PERSONA, '".$id_anho."','".$id_depto."') as SALDO"), 
                  'D.NOM_PERSONA AS NOMBRE_ALUMNO')
                  ->orderBy('A.FECHA', 'desc')
                  ->get();
        return $query;
      }
      public static function updateGandor($ganador, $request) {
        $detail       = $request->details;
        foreach ($detail as $datos) {
            $items = (object)$datos;
            $save = DB::table('eliseo.fin_estrategia_alumno')
            ->where('id_estrategia_alumno', $items->id_estrategia_alumno)
            ->update(
            [
              'ganador'              => $ganador,
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
        }

        return $response;
    }
}
