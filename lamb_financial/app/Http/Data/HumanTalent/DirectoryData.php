<?php
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

use Illuminate\Support\Facades\Input;

class DirectoryData extends Controller{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function listDirectory($request){
      $entity = $request->query('entity');
      $pageSize = $request->query('pageSize');
      $search = $request->query('search');
      $results= DB::table('APS_EMPLEADO as a')
      ->join('CONTA_ENTIDAD as b', 'a.ID_ENTIDAD', '=', 'b.ID_ENTIDAD')
      ->join('CONTA_ENTIDAD_DEPTO as c','c.ID_ENTIDAD', DB::raw('a.ID_ENTIDAD and a.ID_DEPTO=c.ID_DEPTO'))
      ->join('CONTA_EMPRESA as d', 'b.ID_EMPRESA', '=', 'd.ID_EMPRESA' )
      ->join("MOISES.VW_PERSONA_NATURAL_LIGHT as e", 'e.ID_PERSONA', DB::raw("a.ID_PERSONA and e.ID_TIPODOCUMENTO IN ('1', '4','7')"))
      ->leftJoin('MOISES.VW_PERSONA_NATURAL_PARIENTE as VM_PNP', 'VM_PNP.ID_PERSONA' ,DB::raw('a.ID_PERSONA and VM_PNP.ID_TIPOPARENTESCO=7'))
      ->select(
      DB::raw("DISTINCT a.ID_PERSONA"),
      'a.ESTADO',
      DB::raw('FC_OBTENER_CARGO(a.ID_CONTRATO, a.ID_PERSONA, a.ID_ENTIDAD) as cargo'),
      'e.NOM_PERSONA as e_name',
      'e.NUM_DOCUMENTO',
      DB::raw('FC_GTH_OBTENER_EMAIL(a.ID_PERSONA) as email'),
      DB::raw('FC_OBTENER_PHONO(a.ID_PERSONA) as telefono'),
      DB::raw("TO_CHAR(e.FEC_NACIMIENTO, 'DD/MM/YYYY') as e_f_na"),
      'd.ID_CORPORACION',
      'b.ID_EMPRESA',
      'a.ID_ENTIDAD' ,
      'b.NOMBRE as entidad',
      'a.FEC_INICIO',
      'a.FEC_TERMINO',
      'a.FEC_MISIONERO',
      'a.ID_DEPTO as DEPTO',
      'a.ID_CONTRATO',
      'c.NOMBRE as departamento',
      'VM_PNP.NOM_PERSONA as p_name',
      'VM_PNP.p_email',
      'VM_PNP.p_telefono',
      DB::raw("TO_CHAR(VM_PNP.FEC_NACIMIENTO, 'DD/MM/YYYY') as p_f_na")
      )
      ->where('a.ID_ENTIDAD', '=', $entity)
      ->where( function($q){
        $q->where('a.FEC_TERMINO', '>=', date("Y-m-d"))
        ->orWhereNull('a.FEC_TERMINO');})
      ->where(function($qu) use ($search){
        $qu->whereRaw("UPPER(e.NOM_PERSONA) LIKE UPPER('%{$search}%')")
        ->orWhere("e.NUM_DOCUMENTO", "LIKE", "%" . $search . "%");
      });
      if($pageSize) {
        $results = $results->paginate($pageSize);
      } else {
        $results = $results->get();
      }
      
      return $results;


    }

}