<?php
namespace App\Http\Controllers\Purchases\Utils;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Controllers\Purchases\Validations\PurchasesValidation;
use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Data\GlobalMethods;
use Carbon\Carbon;
use PDO;
use Response;
use Mail;

class PurchasesUtil extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function utilCall($function)
    {
        $resultCall = call_user_func(__NAMESPACE__ .'\PurchasesUtil::'.$function);
        return $resultCall;
    }
    public static function dataProject()
    {
        $result = new class{};
        $data = [];
        $data["nombre"] = Input::get('proj_nombre');
        $data["nro_acuerdo"] = Input::get('proj_nro_acuerdo');
        $data["fecha_acuerdo"] = Input::get('proj_fecha_acuerdo');
        $data["presupuesto"] = Input::get('proj_presupuesto');
        $data["cuenta"] = Input::get('proj_cuenta');
        $data["modalidad_ejecucion"] = Input::get('proj_modalidad_ejecucion');
        $data["tipo_financiamiento"] = Input::get('proj_tipo_financiamiento');
        $data["id_tipoproyecto"] = Input::get('proj_id_tipo_proyecto');
        $data["reg_cont"] = Input::get('proj_reg_cont');
        $data["cta_cte"] = Input::get('proj_cta_cte');
        $data["sustento"] = Input::get('proj_sustento');
        $data["extencion"] = Input::get('proj_extencion');
        
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* tipo_plantilla */
    public static function dataTypesTemplates()
    {
        $result = new class{};
        $data = [];
        $data["nombre"] = Input::get('nombre');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* COMPRA_DETALLE */
    public static function dataPurchasesDetails()
    {
        $validator = PurchasesValidation::validationByCallRules("rulesReceiptDetails");
        if($validator->invalid)
        {
            return $validator;
        }
        $result = new class{};
        $data = [];
        $data["id_ctipoigv"] = Input::get('id_ctipoigv');
        $data["detalle"] = Input::get('detalle');
        $data["cantidad"] = Input::get('cantidad');
        $data["precio"] = Input::get('precio');
        $data["importe"] = Input::get('importe');
        // $data["orden"] = Input::get('orden');
        $data["igv"] = Input::get('igv');
        $data["base"] = Input::get('base');
        $data["id_compra"] = Input::get('id_compra');
        
        $data["id_almacen"] = Input::get('id_almacen');
        $data["id_articulo"] = Input::get('id_articulo');
        // Solo Para Costo Vinculado
        $data["es_costo_vinculado"] = Input::get('es_costo_vinculado');
        $data["costo_vinculado"] = Input::get('costo_vinculado');
        
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* COMPRA_ASIENTO */
    public static function dataPurchasesSeats()
    {
        $validator = PurchasesValidation::validationByCallRules("rulesPurchasesSeats");
        if($validator->invalid)
        {
            return $validator;
        }
        $result = new class{};
        $data = [];
        $data["id_compra"] = Input::get('id_compra');
        $data["id_cuentaaasi"] = Input::get('id_cuentaaasi');
        $data["id_restriccion"] = Input::get('id_restriccion');
        $data["id_ctacte"] = Input::get('id_ctacte');
        $data["id_fondo"] = Input::get('id_fondo');
        $data["id_depto"] = Input::get('id_depto');
        $data["importe"] = Input::get('importe');
        $data["descripcion"] = Input::get('descripcion');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* compra_plantilla */
    public static function dataPurchasesTemplates()
    {
        $result = new class{};
        $data = [];
        $data["id_tipoplantilla"] = Input::get('id_tipoplantilla');
        $data["fecha"] = DB::raw('sysdate'); /* Input::get('fecha'); */
        $data["nombre"] = Input::get('nombre');
        $data["id_depto"] = Input::get('id_depto');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* .compra_plantilla_detalle */
    public static function dataPurchasesTemplateDetails()
    {
        $validTemplate = PurchasesValidation::validationPurchasesTemplateDetails();
        if($validTemplate->invalid)
        {
            return $validTemplate;
        }
        $result = new class{};
        $data = [];
        $data["id_plantilla"] = Input::get('id_plantilla');
        $data["id_depto"] = Input::get('id_depto');
        $data["id_tipoplan"] = Input::get('id_tipoplan');
        $data["id_cuentaaasi"] = Input::get('id_cuentaaasi');
        $data["id_restriccion"] = Input::get('id_restriccion');
        $data["detalle"] = Input::get('detalle');
        $data["porcentaje"] = Input::get('porcentaje');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* compra_plantilla_detalle. */
    /* .PEDIDO_COMPRA. */
    public static function dataOrdersPurchases()
    {
        $validOrdersPurchases = PurchasesValidation::validationOrdersPurchases();
        if($validOrdersPurchases->invalid)
        {
            return $validOrdersPurchases;
        }
        $result = new class{};
        $data = [];
        $data["id_pedido"] = Input::get('id_pedido');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["importe"] = Input::get('importe');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* PEDIDO_REGISTRO */
    public static function dataOrdersRegistriesFORP()
    {
        $validOrdersRegistries = PurchasesValidation::validationOrdersRegistriesFORP();
        if($validOrdersRegistries->invalid)
        {
            return $validOrdersRegistries;
        }
        $result = new class{};
        $data = [];
        // $data["id_tipopedido"] = Input::get('id_tipopedido');
        $data["motivo"] = Input::get('motivo');
        $data["id_areaorigen"] = Input::get('id_areaorigen');
        $data["id_areadestino"] = Input::get('id_areadestino');
        $data["fecha_pedido"] = Input::get('fecha_pedido');
        $data["fecha_entrega"] = Input::get('fecha_entrega');
        $data["id_tipopedido"] = Input::get('id_tipopedido');
        $dataPasos = Input::get('pasos');
        if(Input::get('id_areaorigen') == Input::get('id_areadestino')){
            $result->valid   = false;
            $result->invalid = false;
            $result->message = "Error: No se Puede Realizar pedidos a su misma Area";
        }else{
            $result->valid   = true;
            $result->message = "";
            $result->invalid = true;
        }
        
        $result->data = $data;
        $result->dataPasos = $dataPasos;
        return $result;
    }
    public static function dataOrdersRegistriesFPC() /* dataOrdersRegistriesF1 *//* RAMA: PEDIDO COMPRA */
    {
        $validOrdersRegistriesF1 = PurchasesValidation::validationOrdersRegistriesF1();
        if($validOrdersRegistriesF1->invalid)
        {
            return $validOrdersRegistriesF1;
        }
        $result = new class{};
        $data = [];
        // $data["id_tipopedido"] = Input::get('id_tipopedido');
        $data["motivo"] = Input::get('motivo');
        $data["id_actividad"] = Input::get('id_actividad');
        $data["id_areaorigen"] = Input::get('id_areaorigen');
        $data["id_tipopedido"] = Input::get('id_tipopedido');
        $dataDetalles = Input::get('detalles');
        // ADEMAS ARCHIVOS FILES --> nombre variable=files[array]
        $dataFiles              = [];
        if(Input::hasFile('archivo_img')) {
            $dataFiles[] = "archivo_img";
        }
        if(Input::hasFile('archivo_pdf')) {
            $dataFiles[] = "archivo_pdf";
        }
        $dataPasos = Input::get('pasos');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        $result->dataDetalles = $dataDetalles;
        $result->dataPasos = $dataPasos;
        $result->dataFiles = $dataFiles;
        $result->dataNext = true;
        return $result;
    }
    public static function dataOrdersRegistriesFRA()
    {
        // $result = self::dataOrdersRegistriesFPP2();
        // return $result;
        $validOrdersRegistriesFRA = PurchasesValidation::validationOrdersRegistriesFRA();
        if($validOrdersRegistriesFRA->invalid)
        {
            return $validOrdersRegistriesFRA;
        }
        $result = new class{};
        $data = [];
        $data["motivo"] = Input::get('motivo');
        $data["id_areaorigen"] = Input::get('id_areaorigen');
        $data["id_gasto"] = Input::get('id_gasto');
        // $data["id_areaorigen"] = Input::get('id_areaorigen');
        // $data["id_actividad"] = Input::get('id_actividad');
        $dataDetalles = Input::get('detalles');
        $dataPasos = Input::get('pasos');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        $result->dataDetalles = $dataDetalles;
        $result->dataPasos = $dataPasos;
        $result->dataNext = true;
        return $result;
    }
    public static function dataOrdersRegistriesFPP3() /* dataOrdersRegistriesF2 */
    {
        // $result = self::dataOrdersRegistriesFPP2();
        // return $result;
        $validOrdersRegistriesF3 = PurchasesValidation::validationOrdersRegistriesF3();
        if($validOrdersRegistriesF3->invalid)
        {
            return $validOrdersRegistriesF3;
        }
        $result = new class{};
        $data = [];
        $data["motivo"] = Input::get('motivo');
        $data["id_tipopedido"] = Input::get('id_tipopedido');
        //$data["id_areaorigen"] = Input::get('id_areaorigen');
        $msj = "";
        $dataPC = [];
        $pedidocompra = Input::get('pedidocompra');
        $data["id_areaorigen"] = $pedidocompra['id_areaorigen'];
        $dataPC["id_moneda"] = $pedidocompra['id_moneda'];
        $dataPC["importe"] = $pedidocompra['importe'];
        $dataPC["numero"] = $pedidocompra['numero'];
        $dataPC["serie"] = $pedidocompra['serie'];
        $dataPC["id_proveedor"] = $pedidocompra['id_proveedor'];
        $dataFiles = []; // ["archivo_img"] =
        if(Input::hasFile('archivo_img')) {
            $dataFiles[] = "archivo_img"; // Input::get('archivo_img'); // "archivo_img";
            // $msj = " si ";
        }
        // else {
        //     $msj = " no ";
        // }
        // if(Input::get('archivo_img')) {
        //     // $dataFiles[] = "archivo_img"; // Input::get('archivo_img'); // "archivo_img";
        //     $msj .= " 2si ";
        // }
        // else {
        //     $msj .= " 2no ";
        // }
        if(Input::hasFile('archivo_pdf')) $dataFiles[] = "archivo_pdf"; // Input::get('archivo_pdf'); // "archivo_pdf";
        if(Input::hasFile('archivo_xml')) $dataFiles[] = "archivo_xml";
        if(Input::hasFile('archivo_xlsx')) $dataFiles[] = "archivo_xlsx";
        if(Input::hasFile('archivo_sustento_pdf')) $dataFiles[] = "archivo_sustento_pdf";
        // $data["id_deptoorigen"] = Input::get('id_deptoorigen');
        // $data["id_evento"] = Input::get('id_evento');
        $dataPasos = Input::get('pasos');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        $result->dataMsj = $msj; //  borrar
        $result->dataPC = $dataPC;
        $result->dataFiles = $dataFiles;
        $result->dataPasos = $dataPasos;
        $result->dataNext = true;
        return $result;
        // $validOrdersRegistriesF2 = PurchasesValidation::validationOrdersRegistriesF2();
        // if($validOrdersRegistriesF2->invalid)
        // {
        //     return $validOrdersRegistriesF2;
        // }
        // $result = new class{};
        // $data = [];
        // $data["motivo"] = Input::get('motivo');
        // $data["id_areaorigen"] = Input::get('id_areaorigen');
        // $data["id_actividad"] = Input::get('id_actividad');
        // $dataDetalles = Input::get('detalles');
        // $dataPasos = Input::get('pasos');
        // $result->valid   = true;
        // $result->invalid = false;
        // $result->message = "";
        // $result->data = $data;
        // $result->dataDetalles = $dataDetalles;
        // $result->dataPasos = $dataPasos;
        // $result->dataNext = true;
        // return $result;
    }
    public static function dataOrdersRegistriesFPP2() /* dataOrdersRegistriesF3 *//* RAMA: SERVICIO */
    {
        $validOrdersRegistriesF3 = PurchasesValidation::validationOrdersRegistriesF3();
        if($validOrdersRegistriesF3->invalid)
        {
            return $validOrdersRegistriesF3;
        }
        $result = new class{};
        $data = [];
        $data["motivo"] = Input::get('motivo');
        $data["id_tipopedido"] = Input::get('id_tipopedido');
        //$data["id_areaorigen"] = Input::get('id_areaorigen');
        $msj = "";
        $dataPC = [];
        $pedidocompra = Input::get('pedidocompra');
        $data["id_areaorigen"] = $pedidocompra['id_areaorigen'];
        $dataPC["id_moneda"] = $pedidocompra['id_moneda'];
        $dataPC["importe"] = $pedidocompra['importe'];
        $dataPC["numero"] = $pedidocompra['numero'];
        $dataPC["serie"] = $pedidocompra['serie'];
        $dataPC["id_proveedor"] = $pedidocompra['id_proveedor'];
        $dataFiles = []; // ["archivo_img"] =
        if(Input::hasFile('archivo_img')) {
            $dataFiles[] = "archivo_img"; // Input::get('archivo_img'); // "archivo_img";
            $msj = " si ";
        }
        else {
            $msj = " no ";
        }
        if(Input::get('archivo_img')) {
            // $dataFiles[] = "archivo_img"; // Input::get('archivo_img'); // "archivo_img";
            $msj .= " 2si ";
        }
        else {
            $msj .= " 2no ";
        }
        if(Input::hasFile('archivo_pdf')) $dataFiles[] = "archivo_pdf"; // Input::get('archivo_pdf'); // "archivo_pdf";
        // $data["id_deptoorigen"] = Input::get('id_deptoorigen');
        // $data["id_evento"] = Input::get('id_evento');
        $dataPasos = Input::get('pasos');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        $result->dataMsj = $msj; //  borrar
        $result->dataPC = $dataPC;
        $result->dataFiles = $dataFiles;
        $result->dataPasos = $dataPasos;
        $result->dataNext = true;
        return $result;
    }
    public static function dataOrdersRegistriesFPPA() /* RAMA: Registrar Pre Provisión Almacen */
    {
        $validator = PurchasesValidation::validationByCallRules("rulesOrdersRegistriesFPPA");
        if($validator->invalid)
        {
            return $validator;
        }
        $result                 = new class{};
        $data                   = [];
        $data["id_areaorigen"]  = Input::get('id_areaorigen');
        $data["motivo"]         = Input::get('motivo');
        $data["id_tipopedido"]  = Input::get('id_tipopedido');
        $pedidocompra           = Input::get('pedidocompra');
        /*if($pedidocompra != null || $pedidocompra != ""){//Si Flujo es Pedido SIM
            $data["id_areaorigen"]  = Input::get('id_areaorigen');
            $dataPC["id_moneda"]    = $pedidocompra['id_moneda'];
            $dataPC["importe"]      = $pedidocompra['importe'];
            $dataPC["numero"]       = $pedidocompra['numero'];
            $dataPC["serie"]        = $pedidocompra['serie'];
            $dataPC["id_proveedor"] = $pedidocompra['id_proveedor'];
        }*/
        $dataFiles              = [];
        if(Input::hasFile('archivo_img')) {
            $dataFiles[] = "archivo_img";
        }
        if(Input::hasFile('archivo_pdf')) {
            $dataFiles[] = "archivo_pdf";
        }
        $dataPasos          = Input::get('pasos');
        $dataDetalles       = Input::get('detalles');
        $result->valid      = true;
        $result->invalid    = false;
        $result->message    = "";
        $result->data       = $data;
        $result->dataPC     = $pedidocompra;
        $result->dataFiles  = $dataFiles;
        $result->dataPasos  = $dataPasos;
        $result->dataDetalles = $dataDetalles;
        $result->dataNext   = true;
        return $result;
    }
    public static function dataOrdersRegistriesFPPI() /* RAMA: Registrar Pre Provisión Almacen */
    {
        $validator = PurchasesValidation::validationByCallRules("rulesOrdersRegistriesFPPA");
        if($validator->invalid)
        {
            return $validator;
        }
        $result                 = new class{};
        $data                   = [];
        $data["id_areaorigen"]  = Input::get('id_areaorigen');
        $data["motivo"]         = Input::get('motivo');
        $data["id_tipopedido"]  = Input::get('id_tipopedido');
        $pedidocompra           = Input::get('pedidocompra');
        $data["id_gasto"] = Input::get('id_gasto');
        /*if($pedidocompra != null || $pedidocompra != ""){//Si Flujo es Pedido SIM
            $data["id_areaorigen"]  = Input::get('id_areaorigen');
            $dataPC["id_moneda"]    = $pedidocompra['id_moneda'];
            $dataPC["importe"]      = $pedidocompra['importe'];
            $dataPC["numero"]       = $pedidocompra['numero'];
            $dataPC["serie"]        = $pedidocompra['serie'];
            $dataPC["id_proveedor"] = $pedidocompra['id_proveedor'];
        }*/
        $dataFiles              = [];
        if(Input::hasFile('archivo_img')) {
            $dataFiles[] = "archivo_img";
        }
        if(Input::hasFile('archivo_pdf')) {
            $dataFiles[] = "archivo_pdf";
        }
        $dataPasos          = Input::get('pasos');
        $dataDetalles       = Input::get('detalles');
        $result->valid      = true;
        $result->invalid    = false;
        $result->message    = "";
        $result->data       = $data;
        $result->dataPC     = $pedidocompra;
        $result->dataFiles  = $dataFiles;
        $result->dataPasos  = $dataPasos;
        $result->dataDetalles = $dataDetalles;
        $result->dataNext   = true;
        return $result;
    }
    public static function dataOrdersRegistriesUso($listPaso)
    {
        $llave = "";
        $cant = 0;
        $lista = array();
        if($listPaso)
        {
            $cant = count($listPaso);
            foreach($listPaso as $key => $value)
            {
                $llave = $value["llave_componente"];
                //$llave = $value->llave_componente;
            }
            $lista = $listPaso;
            // return array($listPaso,$cant,$llave);
        }
        // return array(array(),0,"");
        return [
            "list_paso" => $lista,
            "cant" => $cant,
            "llave" => $llave
        ];
    }
    /* .pedido_detalle. */
    public static function dataOrdersDetails()
    {
        // para el formulario por tipo de pedidodd
        if(Input::get('id_formulario')=='5') {
            $id_form = '5'; // hasta aqui 
        } else {
            $form = PurchasesData::getTypeForm(Input::get('id_pedido'));
            foreach($form as $id){
                $id_form = $id->id_formulario;
            }
        }
        $tipo = "D";//Detalle
        $result = new class{};
        $data = [];
        $data["id_pedido"] = Input::get('id_pedido');
        
        if($id_form == "1"){
            $validator = PurchasesValidation::validationByCallRules("rulesOrdersPurchasesOrdersDetailsGeneral");
            if($validator->invalid){
                return $validator;
            }
            $data["id_almacen"] = Input::get('id_almacen');
            $data["id_articulo"] = Input::get('id_articulo');
            $data["cantidad"] = Input::get('cantidad');
            $data["precio"] = Input::get('precio');
            $data["detalle"] = Input::get('detalle');
        }elseif($id_form == "2"){
            $validator = PurchasesValidation::validationByCallRules("rulesOrdersPurchasesOrdersDetailsImagen");
            if($validator->invalid){
                return $validator;
            }
            $data["id_almacen"] = Input::get('id_almacen');
            $data["id_articulo"] = Input::get('id_articulo');
            $data["detalle"] = Input::get('detalle');
            $data["cantidad"] = Input::get('cantidad');
            $data["precio"] = Input::get('precio');
            
            $data["fecha_inicio"] = Input::get('fecha_inicio');
            $data["fecha_fin"] = Input::get('fecha_fin');
            $data["hora_inicio"] = Input::get('hora_inicio');
            $data["hora_fin"] = Input::get('hora_fin');
        }elseif($id_form == "3"){
            $validator = PurchasesValidation::validationByCallRules("rulesOrdersPurchasesOrdersDetailsMovilidad");
            if($validator->invalid){
                return $validator;
            }
            //$data["id_vehiculo"] = Input::get('id_vehiculo');
            $data["detalle"] = Input::get('detalle');
            $data["tipo_viaje"] = Input::get('tipo_viaje');
            $data["origen"] = Input::get('origen');
            $data["destino"] = Input::get('destino');
            //$fecha = Input::get('fecha_p');
            //$fecha = $fecha["year"]."-".$fecha["month"]."-".$fecha["day"];
            $data["fecha_p"] = Input::get('fecha_p');
            $data["hora_p"] = Input::get('hora_p');
            $data["cantidad"] = Input::get('cantidad');
            $data["id_tipovehiculo"] = Input::get('id_tipovehiculo');
            $data["id_persona"] = Input::get('id_persona');
            $data["responsable"] = Input::get('responsable');
            $tipo = "M";//Movilidad
        }elseif($id_form == "4"){
            $validator = PurchasesValidation::validationByCallRules("rulesOrdersPurchasesOrdersDetailsServicios");
            if($validator->invalid){
                return $validator;
            }
            $data["id_almacen"] = Input::get('id_almacen');
            $data["id_articulo"] = Input::get('id_articulo');
            $data["detalle"] = Input::get('detalle');
            $data["cantidad"] = Input::get('cantidad');
            $data["precio"] = Input::get('precio');
            
            $data["fecha_inicio"] = Input::get('fecha_inicio');
            $data["fecha_fin"] = Input::get('fecha_fin');
            $data["hora_inicio"] = Input::get('hora_inicio');
            $data["hora_fin"] = Input::get('hora_fin');
            /// agregando nuevo formulario para canchas
        }elseif($id_form == "6"){
            $validator = PurchasesValidation::validationByCallRules("rulesOrdersPurchasesOrdersDetailsCanchas");
            if($validator->invalid){
                return $validator;
            }
            $data["id_almacen"] = Input::get('id_almacen');
            $data["id_articulo"] = Input::get('id_articulo');
            $data["detalle"] = Input::get('detalle');
            $data["cantidad"] = Input::get('cantidad');
            $data["precio"] = Input::get('precio');
            
            $data["fecha_inicio"] = Input::get('fecha_inicio');
            $data["hora_inicio"] = Input::get('hora_inicio');
            $data["hora_fin"] = Input::get('hora_fin');
            //////////////////////////////////////////////////
        }else{
            $validator = PurchasesValidation::validationByCallRules("rulesOrdersPurchasesOrdersDetailsInversiones");
            if($validator->invalid){
                return $validator;
            }
            $data["cantidad"] = Input::get('cantidad');
            $data["detalle"] = Input::get('detalle');
            $data["precio"] = Input::get('precio');
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->tipo = $tipo;
        $result->data = $data;
        return $result;
    }
    public static function dataOrdersMovilidad()
    {
        /*$form = PurchasesData::getTypeForm(Input::get('id_pedido'));
        foreach($form as $id){
            $id_form = $id->id_formulario;
        }*/
        $result = new class{};
        $data = [];
        $data["id_conductor"] = Input::get('id_conductor');
        $data["id_vehiculo"] = Input::get('id_vehiculo');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* PEDIDO_PLANTILLA_COMPRA */
    public static function dataOrdersTemplatesPurchases()
    {
        $validator = PurchasesValidation::validationByCallRules("rulesOrdersTemplatesPurchases");
        if($validator->invalid)
        {
            return $validator;
        }
        $result = new class{};
        $data = [];
        $data["id_pedido"] = Input::get('id_pedido');
        $data["id_fondo"] = Input::get('id_fondo');
        $data["id_tipoplan"] = Input::get('id_tipoplan');
        $data["id_cuentaaasi"] = Input::get('id_cuentaaasi');
        $data["id_restriccion"] = Input::get('id_restriccion');
        $data["id_ctacte"] = Input::get('id_ctacte');
        $data["detalle"] = Input::get('detalle');
        // $data["porcentaje"] = Input::get('porcentaje');
        $data["importe"] = Input::get('importe');
        // $data["cantidad"] = Input::get('cantidad');
        // $data["precio"] = Input::get('precio');
        // $data["importe"] = Input::get('importe');
        // $data["importe_me"] = Input::get('importe_me');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* .COMPRA_ENTIDAD_DEPTO_PLANTILLA */
    public static function dataPurchasesEntityDeptoTemplates()
    {
        $result = new class{};
        $data = [];
        // $data["id_proveedor"] = Input::get('id_proveedor');
        $data["id_plantilla"] = Input::get('id_plantilla');
        $data["id_depto"] = Input::get('id_depto');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* COMPRA_ENTIDAD_DEPTO_PLANTILLA. */
    /*  */
    /* COMPRA_ORDEN */
    public static function dataPurchasesOrders()
    {
        $validator = PurchasesValidation::validationByCallRules("rulesOrdersPurchasesOrders");
        if($validator->invalid)
        {
            return $validator;
        }
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["id_pedido"] = Input::get('id_pedido');
        $data["id_sedearea"] = Input::get('id_sedearea');
        $data["id_mediopago"] = Input::get('id_mediopago');
        // $data["numero"] = Input::get('numero');
        $data["fecha_pedido"] = Input::get('fecha_pedido');
        $data["fecha_entrega"] = Input::get('fecha_entrega');
        $data["lugar_entrega"] = Input::get('lugar_entrega');
        $data["observaciones"] = Input::get('observaciones');
        $data["con_igv"] = Input::get('con_igv');
        $data["dias_credito"] = Input::get('dias_credito');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["cuotas"] = Input::get('cuotas');
        $data["es_credito"] = Input::get('es_credito');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataPurchasesOrdersU(){
        $result = new class{};
        $data = [];
        $data["estado"] = "1";
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* COMPRA_ORDEN_DETALLE */
    public static function dataPurchasesOrdersDetails()
    {
        $validator = PurchasesValidation::validationByCallRules("rulesOrdersPurchasesOrdersDetails");
        if($validator->invalid)
        {
            return $validator;
        }
        $result = new class{};
        $data = [];
        $data["id_orden"] = Input::get('id_orden');
        $data["id_articulo"] = Input::get('id_articulo');
        $data["detalle"] = Input::get('detalle');
        $data["cantidad"] = Input::get('cantidad');
        $data["precio"] = Input::get('precio');
        // $data["total"] = Input::get('total');
        $data["total"] = DB::raw("(".Input::get('cantidad')."*".Input::get('precio').")");
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataPurchasesOrdersDetailsUP(){
        
        $result = new class{};
        $data = [];
        $data["cantidad"] = Input::get('cantidad');
        $data["precio"] = Input::get('precio');
        $data["total"] = DB::raw("(".Input::get('cantidad')."*".Input::get('precio').")");
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    /* .DOCUMENTO */
    public static function dataComprobante00()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));

            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante01()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        $data["base_inafecta"] = Input::get('base_inafecta');
        $data["otros"] = Input::get('otros');
        if($data["id_moneda"] == 9){
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            if(!$data2) {
                $result->valid   = false;
                $result->invalid = true;
                $result->message = "Alto. No existe tipo de cambio para la fecha: ".$fecha_doc2;
                $result->data = [];
                return $result;
            }
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_venta.")");
            $data["tipocambio"] = $dataMoneda->cos_venta;
        }else{
            $data["importe"] = Input::get('importe');
        }
        //Comentado por Marlo
        /*$otros = Input::get('otros');
        $importe = $data["importe"];
        $base_inafecta = $data["base_inafecta"];
        $total_sincredito = DB::raw('('.$importe.'-('.$base_inafecta.'+'.$otros.'))');
        $base_sincredito = DB::raw('('.$total_sincredito.'/1.18)');
        $data["base_sincredito"] = $base_sincredito;
        $data["base"] = $base_sincredito;
        $igv_sincredito = DB::raw($total_sincredito.'-'.$base_sincredito);
        $data["igv_sincredito"] = $igv_sincredito;
        $data["igv"] = $igv_sincredito;
        */

        /* $data["es_ret_det"] = Input::get('es_ret_det');
        if($data["es_ret_det"] == "")
        {
            $data["retencion_importe"] = Input::get('retencion_importe');
            $data["retencion_serie"] = Input::get('retencion_serie');
            $data["retencion_numero"] = Input::get('retencion_numero');
            $retencion_fecha_full = Input::get('retencion_fecha');
            $retencion_fecha = $retencion_fecha_full["year"]."/".$retencion_fecha_full["month"]."/".$retencion_fecha_full["day"];
            $data["retencion_fecha"] = $retencion_fecha;
        }
        else if($data["es_ret_det"] == "D")
        {
            $data["detraccion_numero"] = Input::get('detraccion_numero');
            $detraccion_fecha_full = Input::get('detraccion_fecha');
            $detraccion_fecha = $detraccion_fecha_full["year"]."/".$detraccion_fecha_full["month"]."/".$detraccion_fecha_full["day"];
            $data["detraccion_fecha"] = $detraccion_fecha;
            $data["detraccion_importe"] = Input::get('detraccion_importe');
            $data["detraccion_banco"] = Input::get('detraccion_banco');
        } */
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "Data Error. ok.";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante02()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["tiene_suspencion"] = Input::get('tiene_suspencion');
        if($data["tiene_suspencion"] == "N")
        {
            $retencion = DB::raw('(case when '.$data["importe"].' > 1500 then ('.$data["importe"].'*0.08) else 0 end)');
            $data["retencion"] = $retencion;
        }
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante03(){
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9){
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_venta.")");
            $data["tipocambio"] = $dataMoneda->cos_venta;
        }else{
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante04()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante05()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $tax_igv = Input::get('tax_igv');
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $dataAUX = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $dataAUX[0];
            $tax_igvsol = DB::raw("(".$tax_igv."*".$dataMoneda->cos_compra.")"); // Input::get('tax_igv');
            $importe_me = Input::get('importe_me');
            $importe = DB::raw("(".$importe_me."*".$dataMoneda->cos_venta.")");
            $igvsol = DB::raw("(".$importe."*0.18)");
            $base_inafecta = DB::raw("(".$tax_igvsol."-".$igvsol.")");
            $base_sincredito = DB::raw("(".$importe."-".$tax_igvsol.")");
            $data["tipocambio"] = $dataMoneda->cos_venta;
        }
        else
        {
            $tax_igvsol = Input::get('tax_igv');
            $importe = Input::get('importe');
            $importe_me = 0;
            $igvsol = DB::raw("(".$importe."*0.18)");
            $base_inafecta = DB::raw("(".$tax_igvsol."-".$igvsol.")");
            $base_sincredito = DB::raw("(".$importe."-".$tax_igvsol.")");
        }
        $data["importe"] = $importe;
        $data["importe_me"] = $importe_me;
        $data["igv"] = $igvsol;
        $data["base_inafecta"] = $base_inafecta;
        $data["base"] = $base_sincredito;
        $data["base_sincredito"] = $base_sincredito;
        $data["id_comprobante"] = Input::get('id_comprobante');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "Data Error. ok.";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante06()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante07()
    {
        $result = new class{};
        $data = [];
        $data["id_parent"] = Input::get('id_parent');
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_venta.")");
            $data["tipocambio"] = $dataMoneda->cos_venta;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante08()
    {
        $result = new class{};
        $data = [];
        $data["id_parent"] = Input::get('id_parent');
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante09()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante10()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante12()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante13()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante14()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante15()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));

            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];

            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante16()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));

            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante37()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));

            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante46()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));

            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante50()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));

            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante91()
    {
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));

            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_venta.")");
            $data["tipocambio"] = $dataMoneda->cos_venta;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
        // Mofica tc
    }
    /* DOCUMENTO. */
    /* .DATA FECHA */
    public static function dataAnhoMesActivo($id_entidad)
    {
        $result = new class{};
        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
        foreach ($data_anho as $item)
        {
            $id_anho = $item->id_anho;
            $id_anho_actual = $item->id_anho_actual;
        }
        $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
        foreach ($data_mes as $item)
        {
            $id_mes = $item->id_mes;
            $id_mes_actual = $item->id_mes_actual;                
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = array("id_anho"=>$id_anho, "id_mes"=>$id_mes);
        return $result;
    }
    public static function dataAnhoMesActivo2($id_entidad)
    {
        $id_anho = "";
        $id_mes = "";
        $result = new class{};
        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
        foreach ($data_anho as $item)
        {
            $id_anho = $item->id_anho;
            $id_anho_actual = $item->id_anho_actual;
        }
        $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
        foreach ($data_mes as $item)
        {
            $id_mes = $item->id_mes;
            $id_mes_actual = $item->id_mes_actual;                
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = array("id_anho"=>$id_anho, "id_mes"=>$id_mes);

        return array($id_anho, $id_mes);
    }
    /* DATA FECHA. */
    /* .SEND MAIL. */
    public static function sendEmail($email,$subject,$data,$files,$view,$from_address,$from_name)
    {
        $result = new class{};
        try
        {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
                $result->valid   = false;
                $result->invalid = true;
                $result->message = "Correo invalido.";
                $result->data = [];
                return $result;
            }
            Mail::send($view, $data, function($message) use($email,$subject,$files,$from_address,$from_name) {
                $message->from($from_address, $from_name);
                $message->subject($subject);
                $message->to($email);
                foreach($files as $file)
                {
                    $message->attach(
                        $file["path"]
                        , [
                            "as" => $file["name"]
                        ]
                    );
                }
            });
            $result->valid   = true;
            $result->invalid = false;
            $result->message = "";
            $result->data = [];
            return $result;
        }
        catch(Exception $e)
        {
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $e->getMessage();
            $result->data = [];
            return $result;
        }
    }
    /* .DOCUMENTOS ELECTRONICOS */
    public static function getComprobanteFromXML($file_name, $url)
    {
        try
        {
            $array_basico   = explode(".", $file_name);
            $doc_array      = explode("-", $array_basico[0]);
            $name_function_data  = "getDataComprobante".$doc_array[1]."FromXML";
            $path = public_path().DIRECTORY_SEPARATOR.$url;
            $xml = new \DOMDocument();
		    $xml->load($path);
            $xmlArray = self::getArrayFromXML($xml);
            if($xmlArray)
                return call_user_func_array(__NAMESPACE__ .'\PurchasesUtil::'.$name_function_data, array($xmlArray));
            else
                return null;
        }
        catch(Exception $e)
        {
            echo $e->getMessage();return null;
        }
    }
    public static function getDataComprobante01FromXML($xmlArray)
    {
        try
        {
            $argsInvoice = $xmlArray["Invoice"];
            $cabecera = [];
            $extra = [];
            $detalles = [];
            /* $cabecera["id_comprobante"] = $argsInvoice["cbc:InvoiceTypeCode"]; */
            $cabecera["id_comprobante"] = "01";
            $cabecera["es_electronica"] = "S";
            $array_serie_numero = explode("-", $argsInvoice["cbc:ID"]);
            $serie = $array_serie_numero[0];
            $numero = $array_serie_numero[1];
            $cabecera["serie"] = $serie;
            $cabecera["numero"] = $numero;
            $fecha_doc = str_replace("-","/",$argsInvoice["cbc:IssueDate"]);
            $cabecera["fecha_doc"] = $fecha_doc;
            $id_moneda = self::getIdMonedaByCode($argsInvoice["cbc:DocumentCurrencyCode"]);
            $cabecera["id_moneda"] = $id_moneda;
            $cabecera["importe"] = $argsInvoice["cac:LegalMonetaryTotal"]["cbc:PayableAmount"]["_value"];
            if(!isset($argsInvoice["cac:TaxTotal"]))
                $listaTaxTotal = array();
            else if(isset($argsInvoice["cac:TaxTotal"]["cbc:TaxAmount"]))
                $listaTaxTotal = array($argsInvoice["cac:TaxTotal"]);
            else
                $listaTaxTotal = $argsInvoice["cac:TaxTotal"];
            foreach($listaTaxTotal as $TaxTotal)
            {
                if($TaxTotal["cac:TaxSubtotal"]["cac:TaxCategory"]["cac:TaxScheme"]["cbc:Name"] == "IGV")
                    $cabecera["igv"] = $TaxTotal["cbc:TaxAmount"]["_value"];
            }
            $extra["proveedor_numero"] = $argsInvoice["cac:AccountingSupplierParty"]["cbc:CustomerAssignedAccountID"];
            $extra["proveedor_tipo"] = $argsInvoice["cac:AccountingSupplierParty"]["cbc:AdditionalAccountID"];
            if(!isset($argsInvoice["cac:InvoiceLine"]))
                $listaInvoiceLine = array();
            else if(isset($argsInvoice["cac:InvoiceLine"]["cbc:ID"]))
                $listaInvoiceLine = array($argsInvoice["cac:InvoiceLine"]);
            else
                $listaInvoiceLine = $argsInvoice["cac:InvoiceLine"];
            foreach($listaInvoiceLine as $item)
            {
                $detalle = [];
                $detalle["orden"] = $item["cbc:ID"];
                $detalle["detalle"] = $item["cac:Item"]["cbc:Description"];
                $detalle["cantidad"] = $item["cbc:InvoicedQuantity"]["_value"];
                $detalle["precio"] = $item["cac:Price"]["cbc:PriceAmount"]["_value"];
                $detalle["importe"] = $item["cbc:LineExtensionAmount"]["_value"];
                if(!isset($item["cac:TaxTotal"]))
                    $listaTaxTotalLine = array();
                else if(isset($item["cac:TaxTotal"]["cbc:TaxAmount"]))
                    $listaTaxTotalLine = array($item["cac:TaxTotal"]);
                else
                    $listaTaxTotalLine = $item["cac:TaxTotal"];
                foreach($listaTaxTotalLine as $TaxTotal)
                {
                    if($TaxTotal["cac:TaxSubtotal"]["cac:TaxCategory"]["cac:TaxScheme"]["cbc:Name"] == "IGV")
                        $detalle["igv"] = $TaxTotal["cbc:TaxAmount"]["_value"];
                }
                $detalles[] = $detalle;
            }
            $comprobante = array(
                "cabecera"  => $cabecera,
                "detalles"  => $detalles,
                "extra"     => $extra
            );
            return $comprobante;
        }
        catch(Exception $e)
        {
            /* echo "---".$e->getMessage(); */
            return null;
        }
    }
    public static function getDataComprobante03FromXML($xmlArray)
    {
        try
        {
            $argsInvoice = $xmlArray["Invoice"];
            $cabecera = [];
            $extra = [];
            $detalles = [];
            /* $cabecera["id_comprobante"] = $argsInvoice["cbc:InvoiceTypeCode"]; */
            $cabecera["id_comprobante"] = "03";
            $cabecera["es_electronica"] = "S";
            $array_serie_numero = explode("-", $argsInvoice["cbc:ID"]);
            $serie = $array_serie_numero[0];
            $numero = $array_serie_numero[1];
            $cabecera["serie"] = $serie;
            $cabecera["numero"] = $numero;
            $fecha_doc = str_replace("-","/",$argsInvoice["cbc:IssueDate"]);
            $cabecera["fecha_doc"] = $fecha_doc;
            $id_moneda = self::getIdMonedaByCode($argsInvoice["cbc:DocumentCurrencyCode"]);
            $cabecera["id_moneda"] = $id_moneda;
            $cabecera["importe"] = $argsInvoice["cac:LegalMonetaryTotal"]["cbc:PayableAmount"]["_value"];
            if(!isset($argsInvoice["cac:TaxTotal"]))
                $listaTaxTotal = array();
            else if(isset($argsInvoice["cac:TaxTotal"]["cbc:TaxAmount"]))
                $listaTaxTotal = array($argsInvoice["cac:TaxTotal"]);
            else
                $listaTaxTotal = $argsInvoice["cac:TaxTotal"];
            foreach($listaTaxTotal as $TaxTotal)
            {
                if($TaxTotal["cac:TaxSubtotal"]["cac:TaxCategory"]["cac:TaxScheme"]["cbc:Name"] == "IGV")
                    $cabecera["igv"] = $TaxTotal["cbc:TaxAmount"]["_value"];
            }
            $extra["proveedor_numero"] = $argsInvoice["cac:AccountingSupplierParty"]["cbc:CustomerAssignedAccountID"];
            $extra["proveedor_tipo"] = $argsInvoice["cac:AccountingSupplierParty"]["cbc:AdditionalAccountID"];
            if(!isset($argsInvoice["cac:InvoiceLine"]))
                $listaInvoiceLine = array();
            else if(isset($argsInvoice["cac:InvoiceLine"]["cbc:ID"]))
                $listaInvoiceLine = array($argsInvoice["cac:InvoiceLine"]);
            else
                $listaInvoiceLine = $argsInvoice["cac:InvoiceLine"];
            foreach($listaInvoiceLine as $item)
            {
                $detalle = [];
                $detalle["orden"] = $item["cbc:ID"];
                $detalle["detalle"] = $item["cac:Item"]["cbc:Description"]; /* implode("", $item["cac:Item"]["cbc:Description"]); /* $item["cac:Item"]["cbc:Description"]; */
                $detalle["cantidad"] = $item["cbc:InvoicedQuantity"]["_value"];
                $detalle["precio"] = $item["cac:Price"]["cbc:PriceAmount"]["_value"];
                $detalle["importe"] = $item["cbc:LineExtensionAmount"]["_value"];
                if(!isset($item["cac:TaxTotal"]))
                    $listaTaxTotalLine = array();
                else if(isset($item["cac:TaxTotal"]["cbc:TaxAmount"]))
                    $listaTaxTotalLine = array($item["cac:TaxTotal"]);
                else
                    $listaTaxTotalLine = $item["cac:TaxTotal"];
                foreach($listaTaxTotalLine as $TaxTotal)
                {
                    if($TaxTotal["cac:TaxSubtotal"]["cac:TaxCategory"]["cac:TaxScheme"]["cbc:Name"] == "IGV")
                        $detalle["igv"] = $TaxTotal["cbc:TaxAmount"]["_value"];
                }
                $detalles[] = $detalle;
            }
            $comprobante = array(
                "cabecera"  => $cabecera,
                "detalles"  => $detalles,
                "extra"     => $extra
            );
            return $comprobante;
        }
        catch(Exception $e)
        {
            /* echo "---".$e->getMessage(); */
            return null;
        }
    }
    public static function getDataComprobante07FromXML($xmlArray)
    {
        try
        {
            $argsCreditNote = $xmlArray["CreditNote"];
            $cabecera = [];
            $extra = [];
            $detalles = [];
            /* $cabecera["id_comprobante"] = $argsCreditNote["cbc:InvoiceTypeCode"]; */
            /* $cabecera["id_comprobante"] = $argsCreditNote["cac:DiscrepancyResponse"]["cbc:ResponseCode"]; */
            $cabecera["id_comprobante"] = "07";
            $cabecera["es_electronica"] = "S";
            $array_serie_numero = explode("-", $argsCreditNote["cbc:ID"]);
            $serie = $array_serie_numero[0];
            $numero = $array_serie_numero[1];
            $cabecera["serie"] = $serie;
            $cabecera["numero"] = $numero;
            $fecha_doc = str_replace("-","/",$argsCreditNote["cbc:IssueDate"]);
            $cabecera["fecha_doc"] = $fecha_doc;
            $id_moneda = self::getIdMonedaByCode($argsCreditNote["cbc:DocumentCurrencyCode"]);
            $cabecera["id_moneda"] = $id_moneda;
            $cabecera["importe"] = $argsCreditNote["cac:LegalMonetaryTotal"]["cbc:PayableAmount"]["_value"];
            if(!isset($argsCreditNote["cac:TaxTotal"]))
                $listaTaxTotal = array();
            else if(isset($argsCreditNote["cac:TaxTotal"]["cbc:TaxAmount"]))
                $listaTaxTotal = array($argsCreditNote["cac:TaxTotal"]);
            else
                $listaTaxTotal = $argsCreditNote["cac:TaxTotal"];
            foreach($listaTaxTotal as $TaxTotal)
            {
                if($TaxTotal["cac:TaxSubtotal"]["cac:TaxCategory"]["cac:TaxScheme"]["cbc:Name"] == "IGV")
                    $cabecera["igv"] = $TaxTotal["cbc:TaxAmount"]["_value"];
            }
            $extra["proveedor_numero"] = $argsCreditNote["cac:AccountingSupplierParty"]["cbc:CustomerAssignedAccountID"];
            $extra["proveedor_tipo"] = $argsCreditNote["cac:AccountingSupplierParty"]["cbc:AdditionalAccountID"];
            /* cac:BillingReference */
            $extra["documento_referencia_id"] = $argsCreditNote["cac:BillingReference"]["cac:InvoiceDocumentReference"]["cbc:ID"];
            $extra["documento_referencia_code"] = $argsCreditNote["cac:BillingReference"]["cac:InvoiceDocumentReference"]["cbc:DocumentTypeCode"];
            if(!isset($argsCreditNote["cac:CreditNoteLine"]))
                $listaCreditNoteLine = array();
            else if(isset($argsCreditNote["cac:CreditNoteLine"]["cbc:ID"]))
                $listaCreditNoteLine = array($argsCreditNote["cac:CreditNoteLine"]);
            else
                $listaCreditNoteLine = $argsCreditNote["cac:CreditNoteLine"];
            foreach($listaCreditNoteLine as $item)
            {
                $detalle = [];
                $detalle["orden"] = $item["cbc:ID"];
                $detalle["detalle"] = $item["cac:Item"]["cbc:Description"];
                $detalle["cantidad"] = $item["cbc:CreditedQuantity"]["_value"];
                $detalle["precio"] = $item["cac:Price"]["cbc:PriceAmount"]["_value"];
                $detalle["importe"] = $item["cbc:LineExtensionAmount"]["_value"];
                if(!isset($item["cac:TaxTotal"]))
                    $listaTaxTotalLine = array();
                else if(isset($item["cac:TaxTotal"]["cbc:TaxAmount"]))
                    $listaTaxTotalLine = array($item["cac:TaxTotal"]);
                else
                    $listaTaxTotalLine = $item["cac:TaxTotal"];
                foreach($listaTaxTotalLine as $TaxTotal)
                {
                    if($TaxTotal["cac:TaxSubtotal"]["cac:TaxCategory"]["cac:TaxScheme"]["cbc:Name"] == "IGV")
                        $detalle["igv"] = $TaxTotal["cbc:TaxAmount"]["_value"];
                }
                $detalles[] = $detalle;
            }
            $comprobante = array(
                "cabecera"  => $cabecera,
                "detalles"  => $detalles,
                "extra"     => $extra
            );
            return $comprobante;
        }
        catch(Exception $e)
        {
            echo "---".$e->getMessage();
            return null;
        }
    }
    public static function getDataComprobante08FromXML($xmlArray)
    {
        try
        {
            $argsDebitNote = $xmlArray["DebitNote"];
            $cabecera = [];
            $extra = [];
            $detalles = [];
            $cabecera["id_comprobante"] = "08";
            $cabecera["es_electronica"] = "S";
            $array_serie_numero = explode("-", $argsDebitNote["cbc:ID"]);
            $serie = $array_serie_numero[0];
            $numero = $array_serie_numero[1];
            $cabecera["serie"] = $serie;
            $cabecera["numero"] = $numero;
            $fecha_doc = str_replace("-","/",$argsDebitNote["cbc:IssueDate"]);
            $cabecera["fecha_doc"] = $fecha_doc;
            $id_moneda = self::getIdMonedaByCode($argsDebitNote["cbc:DocumentCurrencyCode"]);
            $cabecera["id_moneda"] = $id_moneda;
            $cabecera["importe"] = $argsDebitNote["cac:RequestedMonetaryTotal"]["cbc:PayableAmount"]["_value"];
            if(!isset($argsDebitNote["cac:TaxTotal"]))
                $listaTaxTotal = array();
            else if(isset($argsDebitNote["cac:TaxTotal"]["cbc:TaxAmount"]))
                $listaTaxTotal = array($argsDebitNote["cac:TaxTotal"]);
            else
                $listaTaxTotal = $argsDebitNote["cac:TaxTotal"];
            foreach($listaTaxTotal as $TaxTotal)
            {
                if($TaxTotal["cac:TaxSubtotal"]["cac:TaxCategory"]["cac:TaxScheme"]["cbc:Name"] == "IGV")
                    $cabecera["igv"] = $TaxTotal["cbc:TaxAmount"]["_value"];
            }
            $extra["proveedor_numero"] = $argsDebitNote["cac:AccountingSupplierParty"]["cbc:CustomerAssignedAccountID"];
            $extra["proveedor_tipo"] = $argsDebitNote["cac:AccountingSupplierParty"]["cbc:AdditionalAccountID"];
            /* cac:BillingReference./ */
            $extra["documento_referencia_id"] = $argsDebitNote["cac:BillingReference"]["cac:InvoiceDocumentReference"]["cbc:ID"];
            $extra["documento_referencia_code"] = $argsDebitNote["cac:BillingReference"]["cac:InvoiceDocumentReference"]["cbc:DocumentTypeCode"];
            if(!isset($argsDebitNote["cac:DebitNoteLine"]))
                $listaDebitNoteLine = array();
            else if(isset($argsDebitNote["cac:DebitNoteLine"]["cbc:ID"]))
                $listaDebitNoteLine = array($argsDebitNote["cac:DebitNoteLine"]);
            else
                $listaDebitNoteLine = $argsDebitNote["cac:DebitNoteLine"];
            foreach($listaDebitNoteLine as $item)
            {
                $detalle = [];
                $detalle["orden"] = $item["cbc:ID"];
                $detalle["detalle"] = $item["cac:Item"]["cbc:Description"];
                $detalle["cantidad"] = $item["cbc:DebitedQuantity"]["_value"];
                $detalle["precio"] = $item["cac:Price"]["cbc:PriceAmount"]["_value"];
                $detalle["importe"] = $item["cbc:LineExtensionAmount"]["_value"];
                if(!isset($item["cac:TaxTotal"]))
                    $listaTaxTotalLine = array();
                else if(isset($item["cac:TaxTotal"]["cbc:TaxAmount"]))
                    $listaTaxTotalLine = array($item["cac:TaxTotal"]);
                else
                    $listaTaxTotalLine = $item["cac:TaxTotal"];
                foreach($listaTaxTotalLine as $TaxTotal)
                {
                    if($TaxTotal["cac:TaxSubtotal"]["cac:TaxCategory"]["cac:TaxScheme"]["cbc:Name"] == "IGV")
                        $detalle["igv"] = $TaxTotal["cbc:TaxAmount"]["_value"];
                }
                $detalles[] = $detalle;
            }
            $comprobante = array(
                "cabecera"  => $cabecera,
                "detalles"  => $detalles,
                "extra"     => $extra
            );
            return $comprobante;
        }
        catch(Exception $e)
        {
            echo "---".$e->getMessage();
            return null;
        }
	}
    public static function getArrayFromXML($xml)
    {
        $result = array();
        if ($xml->hasAttributes()) {
            $attrs = $xml->attributes;
            foreach ($attrs as $attr)
            {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }
        if ($xml->hasChildNodes())
        {
            $children = $xml->childNodes;
            if ($children->length == 1)
            {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE)
                {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }
            }
            $groups = array();
            foreach ($children as $child)
            {
                if (!isset($result[$child->nodeName]))
                {
                    
                    $resultCData = self::getExistCDataChild($child);
                    if($resultCData)
                        $result[$child->nodeName] = $resultCData;
                    else
                        $result[$child->nodeName] = self::getArrayFromXML($child);
                }
                else
                {
                    if (!isset($groups[$child->nodeName]))
                    {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = self::getArrayFromXML($child);
                }
            }
        }
        return $result;
    }
    public static function getExistCDataChild($parent)
    {
        try
        {
            if (!$parent->hasChildNodes())
            {
                return false;
            }
            $children = $parent->childNodes;
            if ($children->length > 1)
            {
                return false;
            }
            $child = $children->item(0);
            if($child->nodeName == "#cdata-section")
            {
                return $child->nodeValue;
            }

            return false;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    /* .DOCUMENTOS ELECTRONICOS */
    /* .UTIL. */
	public static function getIdMonedaByCode($codeMoneda)
	{
		if($codeMoneda == "USD")
		{
			return "9";
		}
		else if($codeMoneda == "PEN")
		{
			return "7";
		}
    }
    public static function getGenereNameRandom($length)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $name = substr(str_shuffle($characters), 0, $length);
        return $name; 
    }
    public static function dataOrdersRegistriesFRH(){//-- FLUJO FORM RxH
        $validOrdersRegistriesF3 = PurchasesValidation::validationOrdersRegistriesFRH();
        if($validOrdersRegistriesF3->invalid){
            return $validOrdersRegistriesF3;
        }
        $result = new class{};
        $data = [];
        $data["motivo"] = Input::get('motivo');
        $data["id_tipopedido"] = Input::get('id_tipopedido');
        $data["id_areaorigen"] = Input::get('id_areaorigen');
        $data["id_pbancaria"] = Input::get('id_pbancaria');
        $msj = "";
        $dataPC = [];
        $pedidocompra = Input::get('pedidocompra');
        $dataPC["id_moneda"] = $pedidocompra['id_moneda'];
        $dataPC["importe"] = $pedidocompra['importe'];
        $dataPC["numero"] = $pedidocompra['numero'];
        $dataPC["serie"] = $pedidocompra['serie'];
        $dataPC["id_proveedor"] = $pedidocompra['id_proveedor'];
        $dataFiles = [];
        
        if(Input::hasFile('archivo_recibo')) $dataFiles[] = "archivo_recibo";
        if(Input::hasFile('archivo_sustento')) $dataFiles[] = "archivo_sustento";
        if(Input::hasFile('archivo_constancia')) $dataFiles[] = "archivo_constancia";
        $dataPasos = Input::get('pasos');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        $result->dataMsj = $msj; //  borrar
        $result->dataPC = $dataPC;
        $result->dataFiles = $dataFiles;
        $result->dataPasos = $dataPasos;
        $result->dataNext = true;
        return $result;
    }
    /* COMPRA_DETALLE => RECIBO X HONORARIOS*/
    public static function dataReceiptForFeesDetails($id_compra){
        $validator = PurchasesValidation::validationByCallRules("rulesReceiptForFeesDetails");
        if($validator->invalid){
            return $validator;
        }
        $result = new class{};
        $data = [];
        $data["detalle"] = Input::get('detalle');
        $data["cantidad"] = Input::get('cantidad');
        $data["precio"] = Input::get('precio');
        $data["base"] = Input::get('importe');
        $data["importe"] = Input::get('importe');
        $data["id_compra"] = $id_compra;
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataOrdersRegistriesFP() /* dataOrdersRegistriesF1 *//* RAMA: DIRECTO A PROVISION COMPRA */
    {
        /*$validOrdersRegistriesF1 = PurchasesValidation::validationOrdersRegistriesF1();
        if($validOrdersRegistriesF1->invalid){
            return $validOrdersRegistriesF1;
        }*/
        $result = new class{};
        $data = [];
        $data["id_areaorigen"] = Input::get('id_areaorigen');
        $data["motivo"] = "Provision";

        $dataFiles = []; // ["archivo_img"] =
        if(Input::hasFile('archivo_img')) {
            $dataFiles[] = "archivo_img"; // Input::get('archivo_img'); // "archivo_img";
        }
        if(Input::hasFile('archivo_pdf')) $dataFiles[] = "archivo_pdf"; // Input::get('archivo_pdf'); // "archivo_pdf";
        if(Input::hasFile('archivo_xml')) $dataFiles[] = "archivo_xml";
        if(Input::hasFile('archivo_xlsx')) $dataFiles[] = "archivo_xlsx";
        // if(Input::hasFile('archivo_sustento_pdf')) $dataFiles[] = "archivo_sustento_pdf";
        $dataDetalles = "";
        $dataPasos = Input::get('pasos');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        //$result->dataDetalles = $dataDetalles;
        $result->dataPasos = $dataPasos;
        $result->dataFiles = $dataFiles;
        $result->dataNext = true;
        return $result;
    }
    public static function dataComprobante87()
    {
        $result = new class{};
        $data = [];
        $data["id_parent"] = Input::get('id_parent');
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_venta.")");
            $data["tipocambio"] = $dataMoneda->cos_venta;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante88(){
        $result = new class{};
        $data = [];
        $data["id_parent"] = Input::get('id_parent');
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9)
        {
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_compra.")");
            $data["tipocambio"] = $dataMoneda->cos_compra;
        }
        else
        {
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');

        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
    public static function dataComprobante17(){
        $result = new class{};
        $data = [];
        $data["id_proveedor"] = Input::get('id_proveedor');
        $data["es_activo"] = Input::get('es_activo');
        $data["tiene_kardex"] = Input::get('tiene_kardex');
        $data["id_moneda"] = Input::get('id_moneda');
        $data["serie"] = Input::get('serie');
        $data["numero"] = Input::get('numero');
        if($data["id_moneda"] == 9){
            $fecha_doc_full2 = Input::get('fecha_doc');
            $fecha_doc2 = $fecha_doc_full2["year"]."-".$fecha_doc_full2["month"]."-".$fecha_doc_full2["day"];
            $objetoData = PurchasesData::validateDateFuture(date_create($fecha_doc2));
            $data2 = AccountingData::showTipoCambio($fecha_doc2);
            $dataMoneda = $data2[0];
            $data["importe_me"] = Input::get('importe_me');
            $data["importe"] = DB::raw("(".$data["importe_me"]."*".$dataMoneda->cos_venta.")");
            $data["tipocambio"] = $dataMoneda->cos_venta;
        }else{
            $data["importe"] = Input::get('importe');
        }
        $data["base_sincredito"] = $data["importe"];
        $data["base"] = $data["importe"];
        $data["id_comprobante"] = Input::get('id_comprobante');
        $data["es_electronica"] = Input::get('es_electronica');
        $fecha_doc_full = Input::get('fecha_doc');
        $fecha_doc = $fecha_doc_full["year"]."/".$fecha_doc_full["month"]."/".$fecha_doc_full["day"];
        $data["fecha_doc"] = date_create($fecha_doc);
        $data["fecha_provision"] = DB::raw('sysdate');
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        $result->data = $data;
        return $result;
    }
}
