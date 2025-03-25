<?php
namespace App\Http\Data\Accounting\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;
class TypeTransactionData{

    public static function addTypeTransaction($data){     
        $tipotransaction = DB::table('TIPO_TRANSACCION')->insert($data);                   
        return $tipotransaction;
    }
    public static function updateTypeTransaction($idtipotransaction, $data){     
        $tipotransaction = DB::table('TIPO_TRANSACCION')
            ->where('ID_TIPOTRANSACCION', $idtipotransaction)
            ->update($data);
        return $tipotransaction;
    }
    public static function deleteTypeTransaction($idtipotransaction){   
        $tipotransaction = DB::table('TIPO_TRANSACCION')
            ->where('ID_TIPOTRANSACCION', $idtipotransaction)
            ->delete();  
        return $tipotransaction;
    }
    public static function addContaEntidadTransactions($data){     
        $contaEntidadTransaction = DB::table('CONTA_ENTIDAD_TRANSACCION')->insert($data);                   
        return $contaEntidadTransaction;
    }
    public static function deleteContaEntidadTransactions($id_entidad, $id_tipotransaccion){     
        $contaEntidadTransaction = DB::table('CONTA_ENTIDAD_TRANSACCION')
                                    ->where('ID_ENTIDAD', $id_entidad)
                                    ->where('ID_TIPOTRANSACCION', $id_tipotransaccion)
                                    ->delete();             
        return $contaEntidadTransaction;
    }
}