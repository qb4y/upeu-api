<?php


namespace App\Http\Data\Financial;


use App\Models\Criterion;
use App\Models\CriterionSemester;
use Illuminate\Support\Facades\DB;
use PDO;


class CriterionSemesterData
{
    public static function index($id_semestre_programa, $id_nivel_ensenanza, $dc)
    {
        return self::criterionSemester($id_semestre_programa, $id_nivel_ensenanza, $dc);
    }

    protected static function criterionSemester($id_semestre_programa, $id_nivel_ensenanza, $dc)
    {
        $respo = DB::table('MAT_CRITERIO as A')->select(
            'A.id_criterio',
            'A.nombre',
            'A.codigo',
            'A.id_parent',
            'A.orden',
            'A.dc',
            'A.estado',
            'B.nombre as afecta',
            'C.nombre as tipo_beca',
            'D.id_criterio_semestre',
            'D.formula',
            'D.importe',
            'A.comentario',
            'a.ver_hijo',
            DB::raw("
            DECODE(A.tipo, 'E', 'Enseñanza', 'M', 'Matricula','R', 'Residencia') AS tipo,
            DECODE(A.tipo_cobro, 'M', 'Mensual', 'U', 'Único') AS tipo_cobro, 
            DECODE(A.tipo_alumno, 'RE', 'Regular', 'B18', 'Beca 18') AS tipo_alumno, 
            DECODE(D.tipo_proceso, 'P', 'Procedimiento', 'PF', 'Procedimiento y Fijo', 'F', 'Fijo') AS tipo_proceso, 
            DECODE(D.tipo_valor, 'I', 'Importe', 'P', 'Porcentaje') AS tipo_valor,
            case when A.ver_hijo = 'S' then 'Si' when A.ver_hijo = 'N' then 'No' else '' end AS ver_hijo_desc")
        ) // left join ELISEO.MAT_CRITERIO_SEMESTRE D on A.ID_CRITERIO = D.ID_CRITERIO
            ->leftJoin('MAT_CRITERIO as B', 'B.ID_CRITERIO', '=', 'A.ID_AFECTA')
            ->leftJoin('DAVID.TIPO_REQUISITO_BECA as C', 'A.ID_TIPO_REQUISITO_BECA', '=', 'C.ID_TIPO_REQUISITO_BECA')
            ->Join('MAT_CRITERIO_SEMESTRE D', 'A.ID_CRITERIO', '=', 'D.ID_CRITERIO')
            ->where('D.ID_SEMESTRE_PROGRAMA', $id_semestre_programa)
            ->whereraw("a.dc like '%".$dc."%'")
            ->orderBy('A.dc', 'desc')
            ->orderBy('A.ORDEN', 'asc');

        /*$respo = DB::table('MAT_CRITERIO_SEMESTRE')
            ->select('MAT_CRITERIO_SEMESTRE.ID_CRITERIO_SEMESTRE',
                'MAT_CRITERIO_SEMESTRE.ID_CRITERIO',
                'MAT_CRITERIO_SEMESTRE.FORMULA',
                'MAT_CRITERIO_SEMESTRE.IMPORTE',
                'MAT_CRITERIO_SEMESTRE.TIPO_PROCESO',
                'MAT_CRITERIO.DC',
                'MAT_CRITERIO.NOMBRE')
            ->join('MAT_CRITERIO', 'MAT_CRITERIO_SEMESTRE.ID_CRITERIO', '=', 'MAT_CRITERIO.ID_CRITERIO');*/

//        if ($id_semestre_programa) {
//            $respo->where('D.ID_SEMESTRE_PROGRAMA', $id_semestre_programa);
//        }

        return $respo->get();
//        return $respo->paginate(10);
    }


    public static function add($data)
    {
        $register = [];
        $programs = $data['id_semestre_programa'];
        $criteries = $data['criteries'];
        foreach ($programs as $id) {
            $nitem['id_semestre_programa'] = $id;
            
            foreach ($criteries as $obj) {
                $nitem['id_criterio'] = $obj['id_criterio'];
                $nitem['tipo_proceso'] = $obj['tipo_proceso'];
                $nitem['id_procedure'] = $obj['id_procedure'];
                $nitem['tipo_valor'] = $obj['tipo_valor'];
                $nitem['importe'] = $obj['importe'];
                $nitem['formula'] = $obj['formula'];
                $nitem['estado'] = '1';

                $contar = CriterionSemester::where('id_criterio',$obj['id_criterio'])->where('id_semestre_programa',$id)->count();
                if ($contar==0){
                    array_push($register, $nitem);
                }
                
            }

        }
        $create_criterio_semestre_id = DB::transaction(function () use ($register) {
            CriterionSemester::insert($register);
            return DB::getSequence()->currentValue('SQ_MAT_CRITERIO_SEMESTRE_ID');
        });
        return CriterionSemester::find($create_criterio_semestre_id);
    }

    public static function show($id_criterio_semestre)
    {
        return CriterionSemester::find($id_criterio_semestre);
    }


    public static function update($data, $id)
    {
        CriterionSemester::where('id_criterio_semestre', $id)->update($data);
        return CriterionSemester::findOrFail($id);
    }

    public static function delete($id)
    {
        // dd($id);
          $nerror = 0;
          $msgerror = '';
          for ($i = 1; $i <= 200; $i++) {
              $msgerror .= '0';
          }
          $pdo = DB::getPdo();
          $stmt = $pdo->prepare("begin PKG_FINANCES_STUDENTS.SP_ELIMINAR_CRITERIO_SEMESTRE(
                                  :P_ID_CRITERIO_SEMESTRE,
                                  :P_ERROR,
                                  :P_MSGERROR
                                       ); end;");
          $stmt->bindParam(':P_ID_CRITERIO_SEMESTRE', $id, PDO::PARAM_INT);
          $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
          $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
          $stmt->execute();
          $return = [
              'nerror' => $nerror,
              'msgerror' => $msgerror,
          ];
          return $return;
    }


    public static function addCopyCriterioMatricula($request)
    {
        // dd('hhh', $id_anho, $id_entidad);
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $id_semestre_programa_ori = $request->id_semestre_programa_ori;
        $id_semestre_programa_des = $request->id_semestre_programa_des;
        $indicador = $request->indicador;

        $id_semes_ori = json_decode($id_semestre_programa_ori);
        $id_semestre_dest = json_decode($id_semestre_programa_des);
       $id_semesDestino = implode("|", $id_semestre_dest);

       $id_semestre_progra_ori = $id_semes_ori[0];
        // dd(  $id_semestre_progra_ori, $indicador, $id_semesDestino);
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_FINANCES_STUDENTS.SP_COPIAR_CRITERIO_SEMESTRE(
                                :P_ID_SEMESTRE_PROGRAMA_ORI,
                                :P_ID_SEMESTRE_PROGRAMA_DES,
                                :P_INDICADOR,
                                :P_ERROR,
                                :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_SEMESTRE_PROGRAMA_ORI', $id_semestre_progra_ori, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_SEMESTRE_PROGRAMA_DES', $id_semesDestino, PDO::PARAM_STR);
        $stmt->bindParam(':P_INDICADOR', $indicador, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
    }
}