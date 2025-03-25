<?php


namespace App\Http\Controllers\Accounting\Setup;


use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Accounting\Setup\AccountingEntryData;
use App\Http\Data\GlobalMethods;
use Illuminate\Http\Request;

class AccountingEntryController
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getAccountingEntryBySale($idSale)
    {

        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = AccountingEntryData::getAccountingEntryBySale($idSale);
        }
        return response()->json($response, $response["code"]);
    }

    public function updateMultipleAccountingEntry(Request $request, $idArrangement)
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];

        if($valida=='SI'){
            $jResponse=[];
            $detalle = $request->input('detalle');
            try{
                $successfully = array();
                foreach ($detalle as $item) {
                    $id = $item['id_asiento'];
                    unset($item['id_asiento']);
                    echo($item['id_asiento']);
                    $rep = AccountingEntryData::updateAccountingEntry($id, $item);
                    array_push($successfully, $rep);
                }
                if (!empty($successfully)) {
                    $content = array('ESTADO' => '2');
                    $confirm = AccountingData::updateArrangement($idArrangement, $content);
                }

                $jResponse['success'] = true;
                $jResponse['message'] = "The item was update successfully";
                $jResponse['data'] = AccountingData::showArrangement($idArrangement);
                $code = "200";

            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            }
        }
        return response()->json($jResponse,$code);
    }
    public function  thesisPrices(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        $code = "202";
        try{
            $id_dinamica  = null;
            $cod_dinamica = null;
            $id_sede      = null;
            $id_entidad   = null;
            $id_anho      = date("Y");
            $id_persona   = $request->query('id_persona');
            $paso         = $request->query('paso');
            $tipo_proceso = $request->query('tipo_proceso'); //P3: PROCESO DE PASOS, P5: PROCESO DE 5 PASOs
            $nivel        = $request->query('nivel'); //DR: Doctorado, MG: Maestria, S: Especialidad
            $datos = AccountingEntryData::getPlanStudents($id_persona);
            foreach ($datos as $row){
                $id_sede = $row->id_sede;
                $id_nivel_ensenanza = $row->id_nivel_ensenanza;
                $codigo = $row->codigo; //DR: Doctorado, MG:Maestria, EP: Escuela Profesional (Pregrado)
                if($nivel == "MG" || $nivel == "DR"){
                    $id_sede = "1";
                }
            }
            if($id_sede == "1"){//LIMA
                $id_entidad = 7124;
                $id_depto = "1";
                $id_negocio = "1";
                $id_aplicacion = "5";
                if($nivel == "MG"){//Maestria
                    if($tipo_proceso == "P3"){ 
                        if($paso == "1"){
                            $id_dinamica = 2517;
                            $cod_dinamica = "DL0001";
                        }elseif($paso == "2"){
                            $id_dinamica = 2518;
                            $cod_dinamica = "DL0002";
                        }elseif($paso == "3"){
                            $id_dinamica = 2519;
                            $cod_dinamica = "DL0003";
                        }
                    }elseif($tipo_proceso == "P5"){
                        if($paso == "1"){
                            $id_dinamica = 2536;
                            $cod_dinamica = "DL0004";
                        }elseif($paso == "2"){
                            $id_dinamica = 2537;
                            $cod_dinamica = "DL0005";
                        }elseif($paso == "3"){
                            $id_dinamica = 2538;
                            $cod_dinamica = "DL0006";
                        }elseif($paso == "4"){
                            $id_dinamica = 2539;
                            $cod_dinamica = "DL0007";
                        }elseif($paso == "5"){
                            $id_dinamica = 2540;
                            $cod_dinamica = "DL0008";
                        }else{
                            $id_dinamica = null;
                        }
                    }
                }elseif($nivel == "DR"){//Doctorado
                    if($tipo_proceso == "P3"){
                        if($paso == "1"){
                            $id_dinamica = 2520;
                            $cod_dinamica = "DL0009";
                        }elseif($paso == "2"){
                            $id_dinamica = 2521;
                            $cod_dinamica = "DL0010";
                        }elseif($paso == "3"){
                            $id_dinamica = 2522;
                            $cod_dinamica = "DL0011";
                        }
                    }elseif($tipo_proceso == "P5"){
                        if($paso == "1"){
                            $id_dinamica = 2541;
                            $cod_dinamica = "DL0012";
                        }elseif($paso == "2"){
                            $id_dinamica = 2542;
                            $cod_dinamica = "DL0013";
                        }elseif($paso == "3"){
                            $id_dinamica = 2543;
                            $cod_dinamica = "DL0014";
                        }elseif($paso == "4"){
                            $id_dinamica = 2544;
                            $cod_dinamica = "DL0015";
                        }elseif($paso == "5"){
                            $id_dinamica = 2545;
                            $cod_dinamica = "DL0016";
                        }else{
                            $id_dinamica = null;
                        }
                    }
                }elseif($nivel == "ES"){//Especialidad ING
                    if($tipo_proceso == "P3"){
                        if($paso == "1"){
                            $id_dinamica = 2526;
                            $cod_dinamica = "DL0017";
                        }elseif($paso == "2"){
                            $id_dinamica = 2527;
                            $cod_dinamica = "DL0018";
                        }elseif($paso == "3"){
                            $id_dinamica = 2528;
                            $cod_dinamica = "DL0019";
                        }
                    }
                }else{//Pregado
                    if($paso == "1"){
                        $id_dinamica = 2552;
                        $cod_dinamica = "DL0020";
                    }elseif($paso == "2"){
                        $id_dinamica = 2553;
                        $cod_dinamica = "DL0021";
                    }elseif($paso == "3"){
                        $id_dinamica = 2554;
                        $cod_dinamica = "DL0022";
                    }else{
                        $id_dinamica = null;
                        $cod_dinamica = null;
                    }
                }
            }elseif($id_sede == "5"){//JULIACA...Pending
                $id_entidad = 7124;
                $id_depto = "5";
                $id_negocio = "1";
                $id_aplicacion = "8";
                $id_dinamica = null;
                $cod_dinamica = null;
            }elseif($id_sede == "6"){//TARAPOTO ...Pending
                $id_entidad = 7124;
                $id_depto = "6";
                $id_negocio = "1";
                $id_aplicacion = "8";
                $id_dinamica = null;
                $cod_dinamica = null;
            }
            $data = [];
            $datos = AccountingEntryData::thesisPrices($id_entidad,$id_anho,$id_dinamica,$cod_dinamica);
            foreach ($datos as $key => $value){
                $data[] = [
                            'id_negocio' => $id_negocio,
                            'id_aplicacion' => $id_aplicacion, 
                            'id' => $value->id, 
                            'nombre' => $value->nombre,
                            'glosa' => $value->glosa,
                            'precio' => $value->precio,
                            'moneda' => $value->moneda,
                            'simbolo' => $value->simbolo
                            //'tipo' => $value->tipo
                        ];            
            }
        
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $data;

        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
        return response()->json($jResponse,$code);
    }
    public function  accountStatus(Request $request){
        $jResponse = [
            'success' => false,
            'message' => 'ERROR',
            'data' => array()
        ];
        $code = "202";
        try{
            $id_entidad   = 7124;
            $id_anho      = date("Y");
            $id_persona   = $request->query('id_persona');
            $data = [];
            $datos = AccountingEntryData::accountStatus($id_entidad,$id_anho,$id_persona);
            foreach ($datos as $key => $value){
                $data[] = [
                            'sede' => $value->sede,
                            'total' => $value->total, 
                            'debito' => $value->debito, 
                            'credito' => $value->credito
                        ];            
            }
        
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = $data;

        }catch(Exception $e){
            $jResponse['success'] = false;
            $jResponse['message'] = $e->getMessage();
            $jResponse['data'] = [];
            $code = "202";
        }
        return response()->json($jResponse,$code);
    }
}