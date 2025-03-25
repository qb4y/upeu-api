<?php
namespace App\Http\Data\Accounting\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
class TipoGrupoContaData{

    public static function listTipoGrupoContas(){                        
        $query = "SELECT ID_TIPOGRUPOCONTA, CODIGO, NOMBRE, DESCRIPCION, ESTADO FROM TIPO_GRUPO_CONTA";
        $oQuery = DB::select($query);
        return $oQuery;
    }
}