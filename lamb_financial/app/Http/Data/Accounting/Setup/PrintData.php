<?php
namespace App\Http\Data\Accounting\Setup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function addTemporal($id_user,$fila,$texto){                
        DB::table('CONTA_DOCUMENTO_TEMPORAL')->insert(
                    array('ID_PERSONA' => $id_user,
                        'FILA'=> $fila,
                        'TEXTO' => $texto)
        );  
    }
    public static function listTemporal($id_user){                
        $query = "SELECT 
                ID_PERSONA,FILA,TEXTO
                FROM CONTA_DOCUMENTO_TEMPORAL
                WHERE ID_PERSONA = $id_user ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function updateTemporal($id_user,$fila,$texto){
        $query = "UPDATE CONTA_DOCUMENTO_TEMPORAL SET FILA = $fila,TEXTO = '".$texto."'
                    WHERE ID_PERSONA = $id_user ";
        DB::update($query);
    }
    public static function deleteTemporal($id_user){
        DB::table('CONTA_DOCUMENTO_TEMPORAL')->where('ID_PERSONA', '=', $id_user)->delete();
    }    
    public static function listPrint($id_user){                
        $query = "SELECT 
                ID_PERSONA,FILA,TEXTO
                FROM CONTA_DOCUMENTO_TEMPORAL
                WHERE ID_PERSONA = $id_user ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addPrint($id_user,$id_documento,$fila,$texto){                
        PrintData::addDocumentsPrints($id_user,$fila,$texto);
        $query = "SELECT 
                         CONTENIDO
                FROM CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCUMENTO = ".$id_documento." ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item){            
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'".$item->contenido."','".$item->contenido."',$fila)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);            
        }
    }
    public static function updatePrint($id_user,$fila,$texto){
        $query = "UPDATE CONTA_DOCUMENTO_TEMPORAL SET FILA = $fila,TEXTO = '".$texto."'
                    WHERE ID_PERSONA = $id_user ";
        DB::update($query);
    }
    public static function deletePrint($id_user){
        DB::table('CONTA_DOCUMENTO_PRINT')->where('ID_PERSONA', '=', $id_user)->delete();
    }  
    public static function addDocumentsPrints($id_user,$fila,$texto){                
        DB::table('CONTA_DOCUMENTO_PRINT')->insert(
                    array('ID_PERSONA' => $id_user,
                        'FILA'=> $fila,
                        'TEXTO' => $texto)
        ); 
    }
    public static function listar_contenido($id_user,$id_documento,$fila){        
        $query = "SELECT 
                            FC_IMPRIME_DOCUMENTO($id_user,$id_documento,CONTENIDO,CONTENIDO,$fila)' as dato
                FROM CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCUMENTO = ".$id_documento." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listDocumentsPrints($id_user){
        $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIMIR($id_user)
                WHERE ID_PERSONA = $id_user ";
        DB::update($sql);
        $query = DB::table('CONTA_DOCUMENTO_PRINT')->select('TEXTO')->where('ID_PERSONA', $id_user)->get();
        foreach($query as $row){
            $texto = $row->texto;
        }
        return $texto;
    }
    public static function addDocumentsPrintsFixedParameters($id_user,$id_documento,$tipo,$cont){
        $query = "SELECT 
                         CONTENIDO
                FROM CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCUMENTO = ".$id_documento." 
                AND MODO = 'T' 
                AND TIPO = '".$tipo."' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item){            
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'".$item->contenido."','".$item->contenido."',$cont)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);            
        }
    }
    public static function addDocumentsPrintsBody($id_documento,$fila){                
        $query = "SELECT 
                         CONTENIDO
                FROM CONTA_DOCUMENTO_DETALLE
                WHERE ID_DOCUMENTO = ".$id_documento." 
                AND MODO = 'T' 
                AND TIPO = 'B' ";
        $oQuery = DB::select($query);
        foreach ($oQuery as $item){            
            $sql = "UPDATE CONTA_DOCUMENTO_PRINT SET TEXTO = FC_IMPRIME_DOCUMENTO($id_user,$id_documento,'".$item->contenido."','".$item->contenido."',$fila)
                    WHERE ID_PERSONA = $id_user ";
            DB::update($sql);            
        }
    }
    public static function listDocumentsPointsPrints($id_entidad,$id_depto,$id_user){        
        $query = "SELECT 
                        A.ID_DOCIP,A.ID_DOCUMENTO,A.NOMBRE,B.NOMBRE AS DOCUMENTO,B.SERIE,A.IP,A.ESTADO,
                        (SELECT COUNT(X.ID_DOCIP) FROM CONTA_DOCUMENTO_IP_USER X WHERE x.id_docip = A.ID_DOCIP AND X.ID = ".$id_user.") ASSIGNED
                FROM CONTA_DOCUMENTO_IP A, CONTA_DOCUMENTO B
                WHERE A.ID_DOCUMENTO = B.ID_DOCUMENTO
                AND B.ID_ENTIDAD = ".$id_entidad."
                AND B.ID_DEPTO = '".$id_depto."'
                ORDER BY B.SERIE ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showDocumentsPointsPrints($id_docip){
        $query = DB::table('CONTA_DOCUMENTO_IP')
        ->select('ID_DOCIP','ID_DOCUMENTO','NOMBRE','IP','ESTADO','ID_TIPOTRANSACCION','ID_DINAMICA')
        ->where('ID_DOCIP', $id_docip)->get();     
        return $query;
    }

    public static function showDataPuntoDeImpresion($id_entidad, $id_depto, $id_docip){
        $query = "SELECT A.ID_DOCIP, A.NOMBRE, A.IP, B.ID_DOCUMENTO, B.ID_COMPROBANTE, B.SERIE FROM CONTA_DOCUMENTO_IP A 
		INNER JOIN CONTA_DOCUMENTO B ON A.ID_DOCUMENTO=B.ID_DOCUMENTO
		WHERE A.ID_DOCIP =$id_docip
        AND B.ID_ENTIDAD = $id_entidad
        AND B.ID_DEPTO = '$id_depto'";

        $oQuery = DB::select($query);        
        return $oQuery;
    }

    public static function addDocumentsPointsPrints($id_documento,$nombre,$ip,$estado,$id_tipotransaccion, $id_dinamica){
        DB::table('CONTA_DOCUMENTO_IP')->insert(
                        array('ID_DOCUMENTO' => $id_documento,
                        'NOMBRE' => $nombre,
                        'IP' => $ip,
                        'ESTADO' =>$estado,
                        'ID_TIPOTRANSACCION' =>$id_tipotransaccion,
                        'ID_DINAMICA' =>$id_dinamica,
                        )
                    );
        $query = "SELECT 
                        MAX(ID_DOCIP) ID_DOCIP
                FROM CONTA_DOCUMENTO_IP ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_docip = $id->id_docip;
        }
        $sql = PrintData::showDocumentsPointsPrints($id_docip);
        return $sql;
    }
    public static function updateDocumentsPointsPrints($id_docip,$id_documento,$nombre,$ip,$estado,$id_tipotransaccion, $id_dinamica){
        DB::table('CONTA_DOCUMENTO_IP')
            ->where('ID_DOCIP', $id_docip)
            ->update([
                'ID_DOCUMENTO' => $id_documento,
                'NOMBRE' => $nombre,
                'IP' => $ip,
                'ESTADO' => $estado,
                'ID_TIPOTRANSACCION' => $id_tipotransaccion,
                'ID_DINAMICA' => $id_dinamica
            ]);
        $sql = PrintData::showDocumentsPointsPrints($id_docip);
        return $sql;
    }
    public static function deleteDocumentsPointsPrints($id_docip){
        DB::table('CONTA_DOCUMENTO_IP')->where('ID_DOCIP', '=', $id_docip)->delete();
    }
    public static function listDocumentsPointsPrintsUsers($id_docip,$id_user,$id_entidad,$id_depto){
        $id_comprobante = 0;
        $sql = "SELECT 
                        C.ID_COMPROBANTE
                FROM CONTA_DOCUMENTO_IP B, CONTA_DOCUMENTO C
                WHERE B.ID_DOCUMENTO = C.ID_DOCUMENTO
                AND B.ID_DOCIP = ".$id_docip."
                AND C.ID_ENTIDAD = ".$id_entidad."
                AND C.ID_DEPTO = '".$id_depto."' ";
        $oQuery = DB::select($sql);
        foreach($oQuery as $id){
            $id_comprobante = $id->id_comprobante;
        }
        $sql = "SELECT 
                        A.ID_DOCIP,C.ID_COMPROBANTE
                FROM CONTA_DOCUMENTO_IP_USER A, CONTA_DOCUMENTO_IP B, CONTA_DOCUMENTO C
                WHERE A.ID_DOCIP = B.ID_DOCIP
                AND B.ID_DOCUMENTO = C.ID_DOCUMENTO
                AND A.ID = ".$id_user."
                AND C.ID_ENTIDAD = ".$id_entidad."
                AND C.ID_DEPTO = '".$id_depto."' 
                AND C.ID_COMPROBANTE = '".$id_comprobante."' ";
        $oQuery = DB::select($sql);
        return $oQuery;
    }
    // Para notas de credito o debito
    public static function listDocumentsPointsPrintsUsersNCD($id_docip,$id_user,$id_entidad,$id_depto){
        $id_comprobante = '';
        $id_comprobante_afecto = '';
        $sql = "SELECT 
                        C.ID_COMPROBANTE, C.ID_COMPROBANTE_AFECTO
                FROM CONTA_DOCUMENTO_IP B, CONTA_DOCUMENTO C
                WHERE B.ID_DOCUMENTO = C.ID_DOCUMENTO
                AND B.ID_DOCIP = ".$id_docip."
                AND C.ID_ENTIDAD = ".$id_entidad."
                AND C.ID_DEPTO = '".$id_depto."' ";
        $oQuery = DB::select($sql);
        foreach($oQuery as $id){
            $id_comprobante = $id->id_comprobante;
            $id_comprobante_afecto = $id->id_comprobante_afecto;
        }
        $sql = "SELECT 
                        A.ID_DOCIP,C.ID_COMPROBANTE
                FROM CONTA_DOCUMENTO_IP_USER A, CONTA_DOCUMENTO_IP B, CONTA_DOCUMENTO C
                WHERE A.ID_DOCIP = B.ID_DOCIP
                AND B.ID_DOCUMENTO = C.ID_DOCUMENTO
                AND A.ID = ".$id_user."
                AND C.ID_ENTIDAD = ".$id_entidad."
                AND C.ID_DEPTO = '".$id_depto."' 
                AND C.ID_COMPROBANTE = '".$id_comprobante."'
                AND C.ID_COMPROBANTE_AFECTO = '".$id_comprobante_afecto."' ";
        $oQuery = DB::select($sql);
        return $oQuery;
    }
    public static function addDocumentsPointsPrintsUsers($id_docip,$id_user){
        DB::table('CONTA_DOCUMENTO_IP_USER')->insert(
                        array('ID_DOCIP' => $id_docip,'ID' => $id_user,'FECHA' => DB::raw('SYSDATE'))
                    );
        $sql = PrintData::showDocumentsPointsPrints($id_docip);
    }
    public static function updateDocumentsPointsPrintsUsers($id_docip,$id_user,$id_docip_old){ 
        if ($id_docip == $id_docip_old) {
            DB::table('CONTA_DOCUMENTO_IP_USER')
                ->where('ID', $id_user)
                ->where('ID_DOCIP', $id_docip_old)
                ->delete();
        } else {
            DB::table('CONTA_DOCUMENTO_IP_USER')
                ->where('ID', $id_user)
                ->where('ID_DOCIP', $id_docip_old)
                ->update([
                    'ID_DOCIP' => $id_docip,
                    'FECHA' => DB::raw('SYSDATE')
                ]);
    
            $sql = PrintData::showDocumentsPointsPrints($id_docip);
        }
    }
    public static function showIPDocumentUser($id_user,$id_docip){        
        $query = "SELECT B.ID_DOCUMENTO,B.IP 
                FROM CONTA_DOCUMENTO_IP_USER A, CONTA_DOCUMENTO_IP B
                WHERE A.ID_DOCIP = B.ID_DOCIP
                AND A.ID_DOCIP = ".$id_docip."
                AND A.ID = ".$id_user." ";
        $oQuery = DB::select($query);  
        return $oQuery;
    }
    public static function showIPDocumentUserPrint($id_entidad, $id_depto, $id_user,$id_comprobante, $id_comprobante_afecto){   
        // Cuando es nota de cradito o nota de debito.
        $add_query = '';
        if($id_comprobante === '07' || $id_comprobante === '08' || $id_comprobante === '87' || $id_comprobante === '88') {
            $add_query = $id_comprobante_afecto ? "AND C.ID_COMPROBANTE_AFECTO = '$id_comprobante_afecto'" : "";
        }

        $query = "SELECT 
                        C.ID_DOCUMENTO,C.NOMBRE,C.PUERTO,C.SERIE ,B.IP,B.IMPRIMIR
                FROM CONTA_DOCUMENTO_IP_USER A, CONTA_DOCUMENTO_IP B, CONTA_DOCUMENTO C
                WHERE A.ID_DOCIP = B.ID_DOCIP
                AND B.ID_DOCUMENTO = C.ID_DOCUMENTO
                AND A.ID = $id_user
                AND C.ID_ENTIDAD = $id_entidad
                AND C.ID_DEPTO = '$id_depto' 
                AND C.ID_COMPROBANTE = '$id_comprobante'
                $add_query
                ";
        $oQuery = DB::select($query);  
        return $oQuery;
    }
}