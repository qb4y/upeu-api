<?php
namespace App\Http\Data\Purchases;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use PDO;

class SuspencionsData extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public  static function getSuspencionListado() {

        $query = "SELECT s.ID_SUSPENSION,s.NRO_OPERACION,s.FECHA_EMISION,s.FECHA_PRESENTACION ,c.FECHA_INICIO,c.FECHA_FIN,c.ID_ANHO,d.NOMBRE AS departamento ,p.NOMBRE||''||p.MATERNO||''||p.PATERNO AS persona FROM COMPRA_SUSPENCION s,CONTA_ENTIDAD_ANHO_CONFIG c,CONTA_ENTIDAD_DEPTO d, moises.persona p
        WHERE s.ID_ENTIDAD=c.ID_ENTIDAD AND s.ID_ANHO=c.ID_ANHO AND s.ID_ENTIDAD=d.ID_ENTIDAD AND s.ID_DEPTO=d.ID_DEPTO  AND s.ID_PROVEEDOR=p.ID_PERSONA";
        $oQuery = DB::select($query);
        return $oQuery;
    }

}
