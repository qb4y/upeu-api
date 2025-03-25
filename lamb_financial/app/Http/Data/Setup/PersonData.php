<?php
namespace App\Http\Data\Setup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listDocumentType(){                
        $query = "SELECT 
                    ID_TIPODOCUMENTO,NOMBRE,SIGLAS 
                    FROM MOISES.TIPO_DOCUMENTO
                    ORDER BY ID_TIPODOCUMENTO ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listCivilStatustType(){                
        $query = "SELECT ID_TIPOESTADOCIVIL,NOMBRE 
                FROM TIPO_ESTADO_CIVIL
                ORDER BY ID_TIPOESTADOCIVIL ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listCountry(){                
        $query = "SELECT 
                ID_TIPOPAIS,NOMBRE, ISO_A3,COD_SUNAT,DECODE(ISO_A3,'PER',1,0) AS SELECTED
                FROM TIPO_PAIS
                WHERE COD_SUNAT IS NOT NULL
                ORDER BY NOMBRE ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showStateType($nombre){                
        $query = "SELECT ID_TIPOESTADO,NOMBRE
                FROM MOISES.TIPO_ESTADO
                WHERE UPPER(NOMBRE) LIKE TRIM(UPPER('%".$nombre."%'))
                ORDER BY ID_TIPOESTADO ";        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listStateType(){                
        $query = "SELECT ID_TIPOESTADO,NOMBRE
                FROM MOISES.TIPO_ESTADO               
                ORDER BY ID_TIPOESTADO ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showConditionType($nombre){                
        $query = "SELECT ID_TIPOCONDICION,NOMBRE 
                FROM TIPO_CONDICION
                WHERE UPPER(NOMBRE) LIKE TRIM(UPPER('".$nombre."'))
                ORDER BY ID_TIPOCONDICION ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listConditionType(){                
        $query = "SELECT ID_TIPOCONDICION,NOMBRE 
                FROM TIPO_CONDICION                
                ORDER BY ID_TIPOCONDICION ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showTaxpayerType($nombre){                
        $query = "SELECT 
                ID_TIPOCONTRIBUYENTE,NOMBRE 
                FROM TIPO_CONTRIBUYENTE
                 WHERE UPPER(NOMBRE) LIKE TRIM(UPPER('".$nombre."')) OR  UPPER(NOMBRE_2) LIKE TRIM(UPPER('".$nombre."'))
                ORDER BY ID_TIPOCONTRIBUYENTE ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listTaxpayerType(){                
        $query = "SELECT 
                ID_TIPOCONTRIBUYENTE,NOMBRE 
                FROM TIPO_CONTRIBUYENTE                
                ORDER BY ID_TIPOCONTRIBUYENTE ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showEconomicActivityType($nombre){                
        $query = "SELECT 
                ID_TIPOACTIVIDADECONOMICA,NOMBRE,COD_SUNAT 
                FROM TIPO_ACTIVIDAD_ECONOMICA
                WHERE UPPER(NOMBRE) LIKE TRIM(UPPER('".$nombre."'))
                ORDER BY ID_TIPOACTIVIDADECONOMICA ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listEconomicActivityType(){                
        $query = "SELECT 
                ID_TIPOACTIVIDADECONOMICA,NOMBRE,COD_SUNAT 
                FROM TIPO_ACTIVIDAD_ECONOMICA
                ORDER BY ID_TIPOACTIVIDADECONOMICA ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showNaturalPerson($ruc){                
        $query = "SELECT 
                        ID_PERSONA,
                        NOM_PERSONA,
                        NUM_DOCUMENTO,
                        ID_TIPODOCUMENTO,
                        0 CANT
                FROM MOISES.VW_PERSONA_NATURAL
                WHERE NUM_DOCUMENTO = '".$ruc."' ";
                // WHERE ID_TIPODOCUMENTO = ".$id_tipodocumento."
                // AND NUM_DOCUMENTO LIKE '%".$dni."%' ";
               // AND UPPER(PATERNO) LIKE UPPER('%".$paterno."%') 
                //AND UPPER(MATERNO) LIKE UPPER('%".$materno."%') 
                //AND UPPER(NOMBRE) LIKE UPPER('%".$nombre."%')  ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showLegalPerson($razonsocial, $ruc){                
        $query = "SELECT 
                        A.ID_PERSONA,
                        A.NOMBRE AS NOM_PERSONA,
                        A.ID_RUC NUM_DOCUMENTO,
                        '1' as TIPO
                FROM MOISES.VW_PERSONA_JURIDICA A 
                WHERE A.ID_RUC LIKE '%".$ruc."%' 
                -- AND UPPER(A.NOMBRE) LIKE UPPER('%".$razonsocial."%')
                ORDER BY NOM_PERSONA  ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function maxPersonId(){                
        $query = "SELECT MAX(ID_PERSONA)+1 as id_persona FROM MOISES.PERSONA ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addNaturalPerson($id_persona,$nombre,$paterno,$materno,$dni,$id_tipodocumento,$id_tipoestadocivil,$id_tipopais,$sexo,$fec_nacimiento){                
        DB::table('MOISES.PERSONA')->insert(
                    array('ID_PERSONA' => $id_persona,
                        'NOMBRE'=> $nombre,
                        'PATERNO' => $paterno,
                        'MATERNO'=> $materno)
        );
        DB::table('MOISES.PERSONA_DOCUMENTO')->insert(
                    array('ID_PERSONA' => $id_persona,
                        'ID_TIPODOCUMENTO'=> $id_tipodocumento,
                        'NUM_DOCUMENTO' => $dni)
        );
        DB::table('MOISES.PERSONA_NATURAL')->insert(
                    array('ID_PERSONA' => $id_persona,
                        'ID_TIPODOCUMENTO'=> $id_tipodocumento,
                        'NUM_DOCUMENTO' => $dni,
                        'ID_TIPOTRATAMIENTO'=> 1,
                        'ID_TIPOESTADOCIVIL' => $id_tipoestadocivil,
                        'ID_TIPOPAIS' => $id_tipopais,
                        'SEXO' => $sexo)
                        //'FEC_NACIMIENTO'=>DB::raw($fec_nacimiento))
        );                
        $query = "SELECT 
                        ID_PERSONA,
                        NOM_PERSONA,
                        NUM_DOCUMENTO,
                        ID_TIPODOCUMENTO                        
                FROM MOISES.VW_PERSONA_NATURAL
                WHERE ID_PERSONA = ".$id_persona." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addNaturalPersonDocumento($id_persona,$ruc,$id_tipodocumento){                
        DB::table('MOISES.PERSONA_DOCUMENTO')->insert(
                    array('ID_PERSONA' => $id_persona,
                        'ID_TIPODOCUMENTO'=> $id_tipodocumento,
                        'NUM_DOCUMENTO' => $ruc)
        );
               
        $query = "SELECT 
                        ID_PERSONA,
                        NOM_PERSONA,
                        NUM_DOCUMENTO,
                        ID_TIPODOCUMENTO                        
                FROM MOISES.VW_PERSONA_NATURAL
                WHERE ID_PERSONA = ".$id_persona."
                AND ID_TIPODOCUMENTO = ".$id_tipodocumento."";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addLegalPerson($id_persona,$razonsocial,$nombrecomercial,$direccion,$ruc,$id_tipopais,$inscripcion,$id_tipocontribuyente,$id_tipoestado,$id_tipocondicion,$id_tipoactividadeconomica,$existe){
        if($existe == "N"){
            DB::table('MOISES.PERSONA')->insert(
                        array('ID_PERSONA' => $id_persona,
                            'NOMBRE'=> $razonsocial,
                            'PATERNO' => $nombrecomercial)
            );
        }
        DB::table('MOISES.PERSONA_JURIDICA')->insert(
                    array('ID_RUC' => $ruc,
                        'ID_PERSONA' => $id_persona,
                        'ID_TIPOESTADO'=> $id_tipoestado,
                        'ID_TIPOCONDICION' => $id_tipocondicion,
                        'ID_TIPOCONTRIBUYENTE' => $id_tipocontribuyente,
                        'ID_TIPOPAIS' => $id_tipopais,
                        'ID_TIPOACTIVIDADECONOMICA' => $id_tipoactividadeconomica)
                        //'FEC_NACIMIENTO'=>DB::raw($fec_nacimiento))
        );
        DB::table('MOISES.PERSONA_DIRECCION')->insert(
                    array('ID_PERSONA' => $id_persona,
                        'ID_TIPODIRECCION'=> 5, //DIRECCION FISCAL
                        'DIRECCION' => $direccion)
        );
                
        $query = "SELECT 
                        ID_PERSONA,
                        NOMBRE AS NOM_PERSONA,
                        ID_RUC NUM_DOCUMENTO                        
                FROM MOISES.VW_PERSONA_JURIDICA
                WHERE ID_PERSONA = ".$id_persona." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    public static function listNaturalPersons($size_page){        
        $query = DB::table('VW_PERSONA_NATURAL')
            ->select('ID_PERSONA','PATERNO','MATERNO','NOMBRE','NOM_PERSONA','SEXO','NUM_DOCUMENTO')
            ->orderby('NOM_PERSONA')->paginate($size_page);
        return $query;
    }
    public static function searchNaturalPersons($text_search){        
        $sql = "SELECT 
                    A.ID_PERSONA, A.NUM_DOCUMENTO AS RUC,
                    A.NOMBRE,
                    NVL(A.NOMBRE, '') || ' ' || NVL(A.PATERNO, '') || ' ' || NVL(A.MATERNO, '') AS RASONSOCIAL,

                    '' AS NOMBRECOMERCIAL,
                    A.FEC_NACIMIENTO,
                    '' AS ESTADO,
                    '' CONDICION
                    FROM MOISES.VW_PERSONA_NATURAL A
                    --,TIPO_ESTADO B, TIPO_CONDICION C
                    WHERE
                    --A.ID_TIPOESTADO = B.ID_TIPOESTADO AND 
                    --A.ID_TIPOCONDICION = C.ID_TIPOCONDICION AND
                    A.ID_TIPODOCUMENTO=6 -- Solo personas naturales con RUC
                    AND 
                    (   
                        UPPER(REPLACE(NVL(A.NOMBRE,'') || NVL(A.PATERNO,'') || NVL(A.MATERNO,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                        OR UPPER(REPLACE(NVL(A.PATERNO,'') || NVL(A.MATERNO,'') || NVL(A.NOMBRE,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                        OR UPPER(REPLACE(NVL(A.MATERNO,'') || NVL(A.NOMBRE,'') || NVL(A.PATERNO,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                        OR A.NUM_DOCUMENTO LIKE  '%$text_search%'
                        )
                    ORDER BY NOMBRE";
        $query = DB::select($sql);  
        return $query;
    }
    public static function listLegalPersonsAndNaturalWithRuc($text_search){  
            $sql = "SELECT 
            A.ID_PERSONA,
            A.ID_RUC AS RUC,
            A.NOMBRE AS RASONSOCIAL,
            A.NOM_COMERCIAL AS NOMBRECOMERCIAL,
            A.FEC_REGISTRO,
            B.NOMBRE AS ESTADO,
            C.NOMBRE AS CONDICION
            FROM MOISES.VW_PERSONA_JURIDICA A,MOISES.TIPO_ESTADO B, TIPO_CONDICION C
            WHERE A.ID_TIPOESTADO = B.ID_TIPOESTADO
            AND A.ID_TIPOCONDICION = C.ID_TIPOCONDICION 
            AND (
                UPPER(REPLACE(NVL(A.NOMBRE,'') || NVL(A.NOM_COMERCIAL,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR UPPER(REPLACE(NVL(A.NOM_COMERCIAL,'') || NVL(A.NOMBRE,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR NVL(A.ID_RUC,'') LIKE  '%$text_search%'
            )
            --ORDER BY RASONSOCIAL 
            UNION ALL
            SELECT 
            A.ID_PERSONA,
            A.NUM_DOCUMENTO AS RUC,
            NVL(A.NOMBRE, '') || ' ' || NVL(A.PATERNO, '') || ' ' || NVL(A.MATERNO, '') AS RASONSOCIAL,
            '' AS NOMBRECOMERCIAL,
            A.FEC_NACIMIENTO,
            '' AS ESTADO,
            '' CONDICION
            FROM MOISES.VW_PERSONA_NATURAL A
            --,TIPO_ESTADO B, TIPO_CONDICION C
            WHERE
            --A.ID_TIPOESTADO = B.ID_TIPOESTADO AND 
            --A.ID_TIPOCONDICION = C.ID_TIPOCONDICION AND
            A.ID_TIPODOCUMENTO=6 -- Solo personas naturales con RUC
            AND 
            (   
                UPPER(REPLACE(NVL(A.NOMBRE,'') || NVL(A.PATERNO,'') || NVL(A.MATERNO,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR UPPER(REPLACE(NVL(A.PATERNO,'') || NVL(A.MATERNO,'') || NVL(A.NOMBRE,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR UPPER(REPLACE(NVL(A.MATERNO,'') || NVL(A.NOMBRE,'') || NVL(A.PATERNO,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR A.NUM_DOCUMENTO LIKE  '%$text_search%'
                )
            AND ROWNUM <5    ";
            $query = DB::select($sql);  
        return $query;
    }
    public static function listLegalPersons($size_page,$text_search,$all){  
        if($all == "true"){
            $sql = "SELECT 
            A.ID_PERSONA, A.ID_RUC AS RUC, A.NOMBRE AS RASONSOCIAL,A.NOM_COMERCIAL AS NOMBRECOMERCIAL,A.FEC_REGISTRO,B.NOMBRE AS ESTADO, C.NOMBRE AS CONDICION
            FROM MOISES.VW_PERSONA_JURIDICA A,MOISES.TIPO_ESTADO B, TIPO_CONDICION C
             WHERE A.ID_TIPOESTADO = B.ID_TIPOESTADO
             AND A.ID_TIPOCONDICION = C.ID_TIPOCONDICION 
             AND (
                UPPER(REPLACE(NVL(A.NOMBRE,'') || NVL(A.NOM_COMERCIAL,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR UPPER(REPLACE(NVL(A.NOM_COMERCIAL,'') || NVL(A.NOMBRE,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR NVL(A.ID_RUC,'') LIKE  '%$text_search%'
             )
             UNION ALL
            SELECT 
            A.ID_PERSONA, A.NUM_DOCUMENTO AS RUC, A.NOM_PERSONA AS RASONSOCIAL,A.NOM_PERSONA AS NOMBRECOMERCIAL,A.FEC_NACIMIENTO,'' AS ESTADO, '' AS CONDICION
            FROM MOISES.VW_PERSONA_NATURAL A
            WHERE (   
                UPPER(REPLACE(NVL(A.NOMBRE,'') || NVL(A.PATERNO,'') || NVL(A.MATERNO,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR UPPER(REPLACE(NVL(A.PATERNO,'') || NVL(A.MATERNO,'') || NVL(A.NOMBRE,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR UPPER(REPLACE(NVL(A.MATERNO,'') || NVL(A.NOMBRE,'') || NVL(A.PATERNO,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
                OR A.NUM_DOCUMENTO LIKE  '%$text_search%'
                )
            ORDER BY RASONSOCIAL ";
            $query = DB::select($sql);  
        }else{
           $query = DB::table('MOISES.VW_PERSONA_JURIDICA A')
            ->join('MOISES.TIPO_ESTADO B', 'A.ID_TIPOESTADO', '=', 'B.ID_TIPOESTADO')
            ->join('TIPO_CONDICION C', 'A.ID_TIPOCONDICION', '=', 'C.ID_TIPOCONDICION')
            //->join('A.NOMBRE', 'LIKE', DB::raw( '%', $text_search, '%'))
            ->select('A.ID_PERSONA', 'A.ID_RUC AS RUC', 'A.NOMBRE AS RASONSOCIAL','A.NOM_COMERCIAL AS NOMBRECOMERCIAL','A.FEC_REGISTRO','B.NOMBRE AS ESTADO', 'C.NOMBRE AS CONDICION')
            ->orderBy('RASONSOCIAL')->paginate($size_page);
        }
        //$query = DB::table('VW_PERSONA_NATURAL')->select('ID_PERSONA','PATERNO','MATERNO','NOMBRE','NOM_PERSONA','SEXO','NUM_DOCUMENTO')->orderby('NOM_PERSONA')->paginate(100);        
        return $query;
    }
    public static function listStudentsPersons($dato){                
        $query = "SELECT 
                ID_PERSONA,NOM_PERSONA,NUM_DOCUMENTO,CODIGO
                FROM MOISES.VW_PERSONA_NATURAL_ALUMNO
                WHERE (UPPER(NOM_PERSONA) ||' '|| UPPER(NVL(SUBSTR(NOMBRE, 0, INSTR(NOMBRE, ' ')-1), NOMBRE)||' '||PATERNO) || NUM_DOCUMENTO ||' '||CODIGO) LIKE UPPER('%".$dato."%') AND NUM_DOCUMENTO IS NOT NULL";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showStudentsPersons($codigo){  
        $id_cliente = 0;
        $query = "SELECT 
                ID_PERSONA,NOM_PERSONA,NUM_DOCUMENTO,CODIGO
                FROM MOISES.VW_PERSONA_NATURAL_ALUMNO
                WHERE CODIGO = '".$codigo."' ";
        $oQuery = DB::select($query); 
        foreach($oQuery as $id){
            $id_cliente = $id->id_persona;
        }
        return $id_cliente;
    }
    public static function showStudentByUniversityCode($codigo){
        $id_cliente = 0;
        return DB::table('MOISES.VW_PERSONA_NATURAL_ALUMNO')
            ->where('MOISES.VW_PERSONA_NATURAL_ALUMNO.codigo', $codigo)->first();
        /*$query = "SELECT
                ID_PERSONA,NOM_PERSONA,NUM_DOCUMENTO,CODIGO
                FROM MOISES.VW_PERSONA_NATURAL_ALUMNO
                WHERE CODIGO = '".$codigo."' ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_cliente = $id->id_persona;
        }
        return $id_cliente;*/
    }
    public static function showWorkerPersons($id_entidad,$documento){     
        $documento = str_replace("'", '', $documento);
        $query = "SELECT 
                        ID_PERSONA,NOM_PERSONA,NUM_DOCUMENTO,DECODE(ESTADO,'A','ACTIVO') ESTADO
                FROM MOISES.VW_PERSONA_NATURAL_TRABAJADOR
                WHERE ID_ENTIDAD = ".$id_entidad."
                AND (NUM_DOCUMENTO LIKE '%".$documento."%'
                OR UPPER(REPLACE(NOM_PERSONA, ' ', '')) LIKE UPPER(REPLACE('%".$documento."%', ' ', ''))
                )
                AND ESTADO = 'A' ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addPersonsBankAccount($id_persona, $id_banco, $id_tipoctabanco, $cuenta,$cci){
        DB::table('MOISES.PERSONA_CUENTA_BANCARIA')->insert(
                    array('ID_PERSONA' => $id_persona,
                        'ID_BANCO'=> $id_banco,
                        'ID_TIPOCTABANCO' => $id_tipoctabanco,
                        'CUENTA' => $cuenta,
                        'CCI' => $cci,
                        'ACTIVO' => '1')
        ); 
        $query = "SELECT 
                        MAX(ID_PBANCARIA) ID_PBANCARIA
                FROM MOISES.PERSONA_CUENTA_BANCARIA ";
        $oQuery = DB::select($query);
        foreach($oQuery as $id){
            $id_pbancaria = $id->id_pbancaria;
        }
        $sql = PersonData::getPersonsBankAccount($id_pbancaria);
        return $sql;
    }
    public static function getPersonsBankAccount($id_pbancaria){        
        $query = "SELECT 
                        ID_PBANCARIA,ID_PERSONA,ID_BANCO,ID_TIPOCTABANCO,CUENTA,CCI,ACTIVO
                FROM MOISES.PERSONA_CUENTA_BANCARIA
                WHERE ID_PBANCARIA = ".$id_pbancaria." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showPersonsBankAccount($id_persona){        
        $query = "SELECT 
                        ID_PBANCARIA,ID_PERSONA,ID_BANCO,ID_TIPOCTABANCO,CUENTA,CCI,ACTIVO
                FROM MOISES.PERSONA_CUENTA_BANCARIA
                WHERE ID_PERSONA = ".$id_persona." 
                AND ACTIVO = '1' ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPersonsBankAccount($id_persona){        
        $query = "SELECT 
                A.ID_PBANCARIA,B.SIGLA AS BANCO,C.NOMBRE AS TIPO_CUENTA,A.CUENTA,A.CCI 
                FROM MOISES.PERSONA_CUENTA_BANCARIA A,CAJA_ENTIDAD_FINANCIERA B, TIPO_CTA_BANCO C
                WHERE A.ID_BANCO = B.ID_BANCO
                AND A.ID_TIPOCTABANCO = C.ID_TIPOCTABANCO
                AND A.ID_PERSONA = ".$id_persona." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPersonBankAccounts($idPerson){
        $query = DB::table("MOISES.PERSONA_CUENTA_BANCARIA")
            ->select(
                "MOISES.PERSONA_CUENTA_BANCARIA.ID_PBANCARIA",
                "MOISES.PERSONA_CUENTA_BANCARIA.CUENTA AS NRO_CUENTA",
                "CAJA_ENTIDAD_FINANCIERA.NOMBRE AS ENTIDAD",
                "TIPO_CTA_BANCO.NOMBRE AS TIPO_CUENTA"
                )
            ->join("CAJA_ENTIDAD_FINANCIERA", "MOISES.PERSONA_CUENTA_BANCARIA.ID_BANCO","CAJA_ENTIDAD_FINANCIERA.ID_BANCO")
            ->join("TIPO_CTA_BANCO", "MOISES.PERSONA_CUENTA_BANCARIA.ID_TIPOCTABANCO","TIPO_CTA_BANCO.ID_TIPOCTABANCO")
            ->where("MOISES.PERSONA_CUENTA_BANCARIA.ID_PERSONA", $idPerson)
            ->orderBy("CAJA_ENTIDAD_FINANCIERA.NOMBRE", "CAJA_ENTIDAD_FINANCIERA.ID_BANCO", "TIPO_CTA_BANCO.ID_TIPOCTABANCO")
            ->get();
        return $query;
    }
    public static function showNaturalPersonDNI($dni){                
        $query = "SELECT 
                        ID_PERSONA,
                        NOM_PERSONA,
                        NUM_DOCUMENTO,
                        ID_TIPODOCUMENTO,
                        0 CANT
                FROM MOISES.VW_PERSONA_NATURAL
                WHERE NUM_DOCUMENTO = '".$dni."' ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showPersonNatural($id_persona,$dir){                
        $query = "SELECT 
                        A.SEXO,DECODE(B.IMAGEN_URL,'','','".$dir."/'||B.IMAGEN_URL) as IMAGEN_URL,B.IMAGEN_URL AS IMAGEN
                FROM MOISES.PERSONA_NATURAL A, USERS B
                WHERE A.ID_PERSONA = B.ID
                AND A.ID_PERSONA = ".$id_persona." ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function updateUsersImage($id_user,$url){
        $sql = DB::table('USERS')
            ->where('ID', $id_user)
            ->update([
                'IMAGEN_URL' =>$url
            ]);
        return $sql;
    }
    public static function SearchGlobalPerson($search){        
        $sql = "SELECT
        MP.ID_PERSONA, (MP.PATERNO || ' '|| MP.MATERNO || ' ' || MP.NOMBRE ) NOMBRE, MPN.NUM_DOCUMENTO
        FROM MOISES.PERSONA MP
        join MOISES.PERSONA_NATURAL MPN ON MP.ID_PERSONA = MPN.ID_PERSONA
        WHERE UPPER(MP.NOMBRE ||' ' || MP.PATERNO ) LIKE UPPER('%$search%')
           OR UPPER(MP.NOMBRE ||' ' || MP.MATERNO ) LIKE UPPER('%$search%')
           OR UPPER(MP.PATERNO ||' ' || MP.MATERNO ) LIKE UPPER('%$search%')
           OR MPN.NUM_DOCUMENTO LIKE '%$search%' ORDER BY NOMBRE";
        $query = DB::select($sql);  
        return $query;
    }
    public static function lisLegalPersonsAndNatural($text_search){  
        $sql = "SELECT 
        A.ID_PERSONA,
        A.ID_RUC AS RUC,
        A.NOMBRE AS RASONSOCIAL,
        A.NOM_COMERCIAL AS NOMBRECOMERCIAL,
        A.FEC_REGISTRO,
        B.NOMBRE AS ESTADO,
        C.NOMBRE AS CONDICION
        FROM MOISES.VW_PERSONA_JURIDICA A,MOISES.TIPO_ESTADO B, TIPO_CONDICION C
        WHERE A.ID_TIPOESTADO = B.ID_TIPOESTADO
        AND A.ID_TIPOCONDICION = C.ID_TIPOCONDICION 
        AND (
            UPPER(REPLACE(NVL(A.NOMBRE,'') || NVL(A.NOM_COMERCIAL,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
            OR UPPER(REPLACE(NVL(A.NOM_COMERCIAL,'') || NVL(A.NOMBRE,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
            OR NVL(A.ID_RUC,'') LIKE  '%$text_search%'
        )
        UNION ALL
        SELECT 
        A.ID_PERSONA,
        A.NUM_DOCUMENTO AS RUC,
        NVL(A.NOMBRE, '') || ' ' || NVL(A.PATERNO, '') || ' ' || NVL(A.MATERNO, '') AS RASONSOCIAL,
        '' AS NOMBRECOMERCIAL,
        A.FEC_NACIMIENTO,
        '' AS ESTADO,
        '' CONDICION
        FROM MOISES.VW_PERSONA_NATURAL A
        WHERE
        (   
            UPPER(REPLACE(NVL(A.NOMBRE,'') || NVL(A.PATERNO,'') || NVL(A.MATERNO,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
            OR UPPER(REPLACE(NVL(A.PATERNO,'') || NVL(A.MATERNO,'') || NVL(A.NOMBRE,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
            OR UPPER(REPLACE(NVL(A.MATERNO,'') || NVL(A.NOMBRE,'') || NVL(A.PATERNO,''),' ', '')) LIKE UPPER(REPLACE('%$text_search%',' ',''))
            OR A.NUM_DOCUMENTO LIKE  '%$text_search%'
            )
        AND ROWNUM <5    ";
        $query = DB::select($sql);  
    return $query;
}
}