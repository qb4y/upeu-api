<?php
namespace App\Http\Controllers\Services;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Sales\SalesData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use PDO;

class ServicesController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function TipoCambio($anho="",$mes=""){
        
        if($anho==""){
            $anho=date("Y");
        }
        if($mes==""){
            $mes=date("m");
        }
        $ur = "http://www.sunat.gob.pe/cl-at-ittipcam/tcS01Alias?anho=".$anho."&mes=".$mes ;
        $file = fopen($ur,"r");
        $n=0 ; 
        $acum=""; 
        while (!feof($file)) {  
            $fila = trim(utf8_encode(fgets($file)) ); 
            if($n>=72){
                $acum = $acum.$fila ;
                if(trim($fila)=="</table>"){
                    break;
                 }
            }
            $n++; 
        }
        fclose($file) ;
        $acum=str_replace('class=class="form-table"',' id="form-table" ', $acum);
        $acum=str_replace('<strong>','', $acum);
        $acum=str_replace('</strong>','', $acum);
        $acum=str_replace("'",'"', $acum);
        
        $contar= strlen($acum);
        $dato="";
        $ant="";
        $entra="";
        $d="";
        $w=0;
        for($g=0;$g<$contar;$g++){
            $car=substr($acum,$g,1);
            if(substr($acum,$g,3)=="<td"){
                $entra=substr($acum,$g,3);
            }else{
                if($entra=="<td"){
                    if($ant==">"){
                        $d=$ant;
                        if($w==0){
                            $dato.=$car;
                        }else{
                            $dato.="|".$car;
                        }
                        $w++;
                    }else{
                        if($car=="<"){
                            $entra="";
                            $d="";
                        }
                        if($d==">"){
                            $dato.=$car;
                        }
                    }
                }
            }
            $ant=$car;
        }
       
        $datas = explode("|", $dato);
        $k=0;
        $m=1;
        $adia=array();
        $acompra=array();
        $aventa=array();
        $index=1;
        foreach($datas as $r){
            if($k>11){
                
                if($m==1){
                   $adia[$r]=$r; 
                   $index=$r;
                }
                if($m==2){
                   $acompra[$index]=$r;
                }
                if($m==3){
                    $aventa[$index]=$r;
                    
                }
                
                
            }
            if($m==3){
                $m=0;
            }
            $m++;
            $k++;
        }
        $dia=date("d");
        $fecha=$anho."-".$mes."-".$dia;
        
        $diasact=array();
        for($i = 1; $i <= date("t", strtotime($fecha)); $i++) { 
            $m=$mes;
            if($mes<10){
                $m='0'.$mes;
            }
            $d=$i;
            if($i<10){
                $d='0'.$i;
            }
            if(date("Ymd")==$anho.$m.$d ){
                $diasact[]=$i;
                break;
            }else{
                $diasact[]=$i;
            }
        } 
      
       SalesData::actualizarTipcoCambio($anho,$mes,$adia,$acompra,$aventa,$diasact);
       
       return 'echo "hola"';
    }
    public function uploadImage(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        if($valida=='SI'){
            $jResponse=[];
            try{
                $file = $request->file('file');
                $ext    = $file->getClientOriginalExtension();
                $fileName = $file->getClientOriginalName();
                $foto = $fileName;
                $path = 'foto';
                $file->move($path,$foto);
            
                $jResponse['success'] = true;
                $jResponse['message'] = "The image was saved successfully";
                $jResponse['data'] = $fileName;
                $code = "200";
            }catch(Exception $e){                    
                $jResponse['success'] = false;
                $jResponse['message'] = "ORA-".$e->getCode();
                $jResponse['data'] = [];
                $code = "400";
            }            
        }        
        return response()->json($jResponse,$code);
    }
    public function createFile(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $id_user = $jResponse["id_user"];

        if($valida=='SI') {
            $jResponse =[];
            try{
                $anexo = $request->anexo;
                $file = $request->name;
                $cab = "<?xml version=".'"1.0"'." encoding=".'"utf-8"?>';
                $data = SalesData::anexoXML($anexo);
                foreach ($data as $datum) {
                    $xml = $datum->data;
                }
                $destinationPath = 'public/xml';
                $content = $cab."".$xml;
                //$archivo = fopen("foto/".$file,"w+");
                $archivo = fopen("/u01/vhosts/lamb-call-dev.upeu.edu.pe/httpdocs/".$file,"w+");
                fwrite($archivo,$content);
                $jResponse['success'] = true;
                $jResponse['message'] = 'XML file created successfully';
                $jResponse['data']    = $content;
                $code = "200";
            }catch(Exception $e){
                return ["success"=>false,"message"=>$e->getMessage()];
            }
        }
        return response()->json($jResponse,$code);
    }
}