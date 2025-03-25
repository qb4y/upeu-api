<?php
namespace App\Http\Controllers\HumanTalentMgt;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\HumanTalentMgt\ConceptData;
use Illuminate\Http\Request;
use App\Http\Data\GlobalMethods;
use Excel;
use Session;      

class ConceptController extends Controller{
    private $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function ListSunatConcept(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $nombre = $request->nombre;
                $id_tipo_concepto= $request->id_tipo_concepto;
                $id_tipo_concepto_sunat= $request->id_tipo_concepto_sunat;
                $perpage= $request->perpage;
                $data = ConceptData::ListSunatConcept($id_tipo_concepto,$id_tipo_concepto_sunat,$nombre,$perpage);  
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function ShowSunatConcept($id_concepto_planilla_sunat){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $objeto= ConceptData::ShowSunatConcept($id_concepto_planilla_sunat);  
                if(!empty($objeto)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['object' => $objeto];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function AddSunatConcept(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::AddSunatConcept($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function UpdateSunatConcept($id_concepto_planilla_sunat,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::UpdateSunatConcept($id_concepto_planilla_sunat,$request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function DeleteSunatConcept($id_concepto_planilla_sunat){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::DeleteSunatConcept($id_concepto_planilla_sunat);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    //aps
    public function ListApsConcept(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $nombre = $request->nombre;
                $id_tipo_concepto= $request->id_tipo_concepto;
                $id_tipoconceptoaps= $request->id_tipoconceptoaps;
                $perpage= $request->perpage;
                $data = ConceptData::ListApsConcept($id_tipo_concepto,$id_tipoconceptoaps,$nombre,$perpage);  
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function ListConcept(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $nombre = $request->nombre;
                $id_tipo_concepto= $request->id_tipo_concepto;
                $perpage= $request->perpage;
                $data = ConceptData::ListConcept($id_tipo_concepto,$nombre,$perpage);  
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
     public function ListConceptXlS(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        $mensaje = $jResponse["message"];
        $archivo = 'excel';
        $carpeta = 'excel';
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $nombre = $request->nombre;
                $id_tipo_concepto= $request->id_tipo_concepto;
                $perpage= 0;
                $data = ConceptData::ListConcept($id_tipo_concepto,$nombre,$perpage,false);  
                
                $excel = Excel::create($archivo, function($excel) use($data){

                    $excel->sheet('Concepto', function($sheet) use($data) {
                        $sheet->loadView("xls.mgt.concept")->with('data',$data);
                        $sheet->setOrientation('landscape');
                    });

                });
                
                $archivo= ($excel->string('xls'));

                $doc  = base64_encode($archivo);
                $jResponse = [
                             'success' => true,
                             'message' => "OK",
                             'data' => ['items'=>$doc]
                         ];

                return response()->json($jResponse);
            }catch(Exception $e){
                $mensaje= $e->getMessage().' file: '.$e->getFile().' line: '.$e->getLine();
            } 
        }        
        $excel = Excel::create($archivo, function($excel) use($mensaje){

            $excel->sheet('Error', function($sheet) use($mensaje) {
               $sheet->loadView("xls.error")->with('mensaje',$mensaje);
               $sheet->setOrientation('landscape');
           });

        });

        $file = $excel->string('xls');
        $doc  = base64_encode($file);

        $jResponse = [
                'success' => true,
                'message' => "OK",
                'data' => ['items'=>$doc]
            ];

        return response()->json($jResponse);
    }
    public function ListConceptByType(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_tipo_concepto= $request->id_tipo_concepto;
                $id_concepto_planilla= $request->id_concepto_planilla;
                $data = ConceptData::ListConceptByType($id_tipo_concepto,$id_concepto_planilla);  
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function ShowConcept($id_concepto_planilla){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $objeto= ConceptData::ShowConcept($id_concepto_planilla);  
                $detail= ConceptData::listConceptProc($id_concepto_planilla); 
                if(!empty($objeto)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['object' => $objeto,'detail'=>$detail];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function AddConcept(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::AddConcept($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function UpdateConcept($id_concepto_planilla,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::UpdateConcept($id_concepto_planilla,$request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was update successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function DeleteConcept($id_concepto_planilla){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::DeleteConcept($id_concepto_planilla);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function DeleteConceptProc($id_concepto_planilla_proc){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::DeleteConceptProc($id_concepto_planilla_proc);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    
    //asignacion de concepto a planilla
    public function ListConceptPayrollGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_planilla_entidad = $request->id_planilla_entidad;
                $id_tipo_concepto= $request->id_tipo_concepto;
                $perpage= $request->perpage;
                $data = ConceptData::ListConceptPayrollGroup($id_tipo_concepto,$id_planilla_entidad,$perpage);  
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function ListConceptPayrollGroupAssign(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_planilla_entidad= $request->id_planilla_entidad;
                $id_tipo_concepto= $request->id_tipo_concepto;
                $data = ConceptData::ListConceptPayrollGroupAssign($id_tipo_concepto,$id_planilla_entidad);  
                $jResponse['success'] = true;
                if(count($data)>0){
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['items' => $data];
                    $code = "200";
                }else{
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $jResponse['data'] = [];
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function showConceptPayrollGroupAssign(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $id_concepto_planilla=$request->id_concepto_planilla;
                $id_planilla_entidad=$request->id_planilla_entidad;
                $objeto= ConceptData::showConceptPayrollGroup($id_concepto_planilla,$id_planilla_entidad);  
                $detail= ConceptData::listConceptProc($id_concepto_planilla); 
                if(!empty($objeto)){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "Succes";                    
                    $jResponse['data'] = ['object' => $objeto,'detail'=>$detail];
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = "The item does not exist";                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function AddConceptPayrollGroup(Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::AddConceptPayrollGroup($request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
    public function UpdateConceptPayrollGroup($id_concepto_planilla,$id_planilla_entidad,Request $request){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::UpdateConceptPayrollGroup($id_concepto_planilla,$id_planilla_entidad,$request);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "The item was created successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
   
    public function DeleteConceptPayrollGroup($id_planilla_entidad,$id_concepto_planilla){
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        $valida = $jResponse["valida"];
        
        if($valida=='SI'){
            $jResponse=[];                        
            try{   
                $response = ConceptData::DeleteConceptPayrollGroup($id_planilla_entidad,$id_concepto_planilla);  
                if($response['success']){
                    $jResponse['success'] = true;
                    $jResponse['message'] = "he item was deleted successfully";                    
                    $code = "200";
                }else{
                    $jResponse['success'] = false;
                    $jResponse['message'] = $response['message'];                        
                    $jResponse['data'] = [];
                    $code = "202";
                }
            }catch(Exception $e){
                $jResponse['success'] = false;
                $jResponse['message'] = $e->getMessage();
                $code = "202";
            } 
        }        
        return response()->json($jResponse,$code);
    }
}

