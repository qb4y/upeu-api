<?php
namespace App\Http\Controllers\Purchases\Validations;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Data\Purchases\PurchasesData;
use App\Http\Data\Accounting\Setup\AccountingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Data\GlobalMethods;
use Carbon\Carbon;
use PDO;
use Response;

class PurchasesValidation extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function collectionValidation($collection)
    {
        $result         = new class{};
        $result->valid  = true;
        $result->invalid= false;
        $result->message = "";
        $data = [];
        foreach($collection as $key => $value)
        {  
            if($value["data"])
            {
                $resultCall = call_user_func(__NAMESPACE__ .'\PurchasesValidation::'.$value["function"], $value["data"]);
            }
            else
            {
                $resultCall = call_user_func(__NAMESPACE__ .'\PurchasesValidation::'.$value["function"]);
            }
            if($value["type_function"] == "validation")
            {
                if($resultCall->invalid)
                {
                    $result->valid   = false;
                    $result->invalid = true;
                    $result->message = $resultCall->message;
                    return $result;
                }
                $data[$value["data_name"]] = $resultCall->data;
                
            }
            else if($value["type_function"] == "exist")
            {
                if(!$resultCall)
                {
                    $result->valid   = false;
                    $result->invalid = true;
                    $result->message = "No existe ".$value["name"];
                    break 1;
                }
            }
        }
        $result->data = $data;
        return $result;
    }
    public static function collectionValidation2($collection)
    {
        $result         = new class{};
        $result->valid  = true;
        $result->invalid= false;
        $result->message = "";
        $data = [];
        foreach($collection as $key => $value)
        {  
            if($value["data"])
            {
                $resultCall = call_user_func(__NAMESPACE__ .'\PurchasesValidation::'.$value["function"], $value["data"]);
            }
            else
            {
                $resultCall = call_user_func(__NAMESPACE__ .'\PurchasesValidation::'.$value["function"]);
            }
            if($value["type_function"] == "validation")
            {
                if($resultCall->invalid)
                {
                    $result->valid   = false;
                    $result->invalid = true;
                    $result->message = $resultCall->message;
                    return $result;
                }
                $data[$value["data_name"]] = $resultCall->data;
                
            }
            else if($value["type_function"] == "exist")
            {
                if(!$resultCall)
                {
                    $result->valid   = false;
                    $result->invalid = true;
                    $result->message = "No existe ".$value["name"];
                    break 1;
                }
            }
        }
        $result->data = $data;
        return $result;
    }
    public static function validationCall($function)
    {
        $resultCall = call_user_func(__NAMESPACE__ .'\PurchasesValidation::'.$function);
        return $resultCall;
    }
    /* EXISTS */
    public static function typeRequestExist($id_tipopedido)
    {
        $existBool = PurchasesData::existTypeRequest($id_tipopedido);
        return $existBool;
    }
    public static function deptoExist($id_depto)
    {
        $existBool = PurchasesData::existDepto($id_depto);
        return $existBool;
    }
    /* .PASOS */
    public static function receiptStepAValidation()
    {
        $rules          = self::receiptStepARules();
        $customMessages = self::receiptStepACustomMessages();
        $validator      = Validator::make(Input::all(), $rules, $customMessages);
        $result         = new class{};
        if ($validator->fails())
        {
            $result->valid   = false;
            $result->invalid = true;
            $errorString = implode(",",$validator->messages()->all());
            $result->message = $errorString;
            return $result;
        }
        else
        {
            $result->valid   = true;
            $result->invalid = false;
            $data = array(
                'id_tipopedido' => Input::get('id_tipopedido'),
                'motivo'        => Input::get('motivo'),
                'id_deptoorigen'=> Input::get('id_deptoorigen')

            );
            $result->data    = $data;
            return $result;
        }
    }
    private static function receiptStepARules()
    {
        return [
            'id_tipopedido' => 'required',
            'motivo'        => 'required',
            'id_deptoorigen'=> 'required',
        ];
    }
    private static function receiptStepACustomMessages()
    {
        return [
            'required' => 'Casilla [:attribute] es requerido.',
        ];
    }
    /* PASOS. */
    /* COMPRA_DETALLE */
    public static function validationCompraDetalle()
    {
        $result = new class{};
        $rules = self::rulesReceiptDetails();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesReceiptDetails()
    {
        return [
            'detalle' => 'required',
            'cantidad' => 'required|numeric',
            'precio' => 'required|numeric',
            'importe' => 'required|numeric',
            'id_compra' => 'required'
        ];
    }
    private static function rulesReceiptForFeesDetails(){
        return [
            'detalle' => 'required',
            'cantidad' => 'required|numeric',
            'precio' => 'required|numeric',
            'importe' => 'required|numeric'
        ];
    }
    /* COMPRA_ASIENTO */
    private static function rulesPurchasesSeats()
    {
        return [
            'id_compra' => 'required',
            // 'xx' => 'required|numeric',
            'id_cuentaaasi' => 'required',
            'id_restriccion' => 'required',
            //'id_ctacte' => 'required',
            'id_fondo' => 'required',
            'id_depto' => 'required',
            'importe' => 'required',
            'descripcion' => 'required'
            // 'editable' => 'required',
            // 'id_tiporegistro' => 'required',
        ];
    }
    /* .tipo_plantilla */
    public static function validationTypesTemplates()
    {
        $result = new class{};
        $rules = self::rulesTypesTemplates();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesTypesTemplates()
    {
        return [
            'nombre' => 'required'
        ];
    }
    /* tipo_plantilla. */
    /* COMPRA_ORDEN */
    public static function validationByCallRules($function)
    {
        $result = new class{};
        $rules = self::$function();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersPurchasesOrders()
    {
        return [
            'id_proveedor' => 'required',
            'id_pedido' => 'required',
            'id_sedearea' => 'required',
            'id_mediopago' => 'required',
            // 'numero' => 'required',
            'fecha_pedido' => 'required',
            'fecha_entrega' => 'required',
            'lugar_entrega' => 'required',
            // 'observaciones' => 'required',
            'con_igv' => 'required',
            // 'dias_credito' => 'required'
        ];
    }
    /* COMPRA_ORDEN_DETALLE */
    private static function rulesOrdersPurchasesOrdersDetails()
    {
        return [
            'id_orden' => 'required',
            // 'id_articulo' => 'required',
            // 'detalle' => 'required',
            'cantidad' => 'required',
            'precio' => 'required',
            // 'total' => 'required'
        ];
    }
    private static function rulesOrdersPurchasesOrdersDetailsGeneral(){
        return [
            'id_pedido' => 'required',
            // 'id_articulo' => 'required',
            // 'id_almacen' => 'required',
            'cantidad' => 'required|numeric|not_in:0',
            'precio' => 'required|numeric'
        ];
    }
    private static function rulesOrdersPurchasesOrdersDetailsImagen(){
        return [
            'id_pedido' => 'required',
            'id_articulo' => 'required',
            'id_almacen' => 'required',
            'detalle' => 'required',
            'cantidad' => 'required|numeric|not_in:0',
            'precio' => 'required|numeric'
        ];
    }
    private static function rulesOrdersPurchasesOrdersDetailsMovilidad(){
        return [
            'id_pedido' => 'required',
            //'id_vehiculo' => 'required',
            'tipo_viaje' => 'required',
            'origen' => 'required',
            'destino' => 'required',
            'cantidad' => 'required|numeric|not_in:0',
            // 'id_persona' => 'required',
            // 'id_tipovehiculo' => 'required',
        ];
    }
    private static function rulesOrdersPurchasesOrdersDetailsServicios(){
        return [
            'id_pedido' => 'required',
            'id_articulo' => 'required',
            'id_almacen' => 'required',
            'cantidad' => 'required|numeric|not_in:0',
            //'precio' => 'required|numeric|not_in:0'
            'precio' => 'required|numeric'
        ];
    }
    private static function rulesOrdersPurchasesOrdersDetailsInversiones(){
        return [
            'id_pedido' => 'required',
            'detalle' => 'required',
            'cantidad' => 'required|numeric|not_in:0',
            'precio' => 'required|numeric'
        ];
    }
    private static function rulesOrdersPurchasesOrdersDetailsCanchas(){
        return [
            'id_pedido' => 'required',
            'id_articulo' => 'required',
            'id_almacen' => 'required',
            'detalle' => 'required',
            'cantidad' => 'required|numeric|not_in:0',
            'precio' => 'required|numeric'
        ];
    }
    /* .compra_plantilla */
    public static function validationPurchasesTemplates()
    {
        $result = new class{};
        $rules = self::rulesPurchasesTemplates();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesPurchasesTemplates()
    {
        return [
            'id_tipoplantilla' => 'required',
            /* 'fecha' => 'required', */
            'nombre' => 'required',
            'id_depto' => 'required'
        ];
    }
    /* compra_plantilla. */
    /* .compra_plantilla_detalle */
    public static function validationPurchasesTemplateDetails()
    {
        $result = new class{};
        $rules = self::rulesPurchasesTemplateDetails();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesPurchasesTemplateDetails()
    {
        return [
            'id_plantilla' => 'required',
            'id_depto' => 'required',
            'id_tipoplan' => 'required',
            'id_cuentaaasi' => 'required',
            'id_restriccion' => 'required',
            'detalle' => 'required',
            'porcentaje' => 'required|numeric|not_in:0'
        ];
    }
    /* compra_plantilla_detalle. */
    /* .PEDIDO_COMPRA. */
    public static function validationOrdersPurchases()
    {
        $result = new class{};
        $rules = self::rulesOrdersPurchases();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersPurchases()
    {
        return [
            'id_pedido' => 'required',
            'id_moneda' => 'required',
            'id_proveedor' => 'required',
            'importe' => 'required'
        ];
    }
    /* PEDIDO_REGISTRO */
    public static function validationOrdersRegistriesFORP()
    {
        $result = new class{};
        $rules = self::rulesOrdersRegistriesFORP();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersRegistriesFORP()
    {
        return [
            // 'id_tipopedido' => 'required',
            'motivo' => 'required',
            'id_areaorigen' => 'required',
            'id_areadestino' => 'required',
            'fecha_pedido' => 'required',
            'fecha_entrega' => 'required',
            'id_tipopedido' => 'required',
            'pasos' => 'required',
            'codigo' => 'required'
        ];
    }
    public static function validationOrdersRegistriesF1()
    {
        $result = new class{};
        $rules = self::rulesOrdersRegistriesF1();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersRegistriesF1()
    {
        return [
            // 'id_tipopedido' => 'required',
            'motivo' => 'required',
            'id_areaorigen' => 'required',
            'pasos' => 'required',
            'codigo' => 'required'
        ];
    }
    /* 000 */
    public static function validationOrdersRegistriesFRA()
    {
        $result = new class{};
        $rules = self::rulesOrdersRegistriesFRA();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersRegistriesFRA()
    {
        return [
            // 'id_tipopedido' => 'required',
            'motivo' => 'required',
            'id_areaorigen' => 'required',
            // 'id_evento' => 'required',
            'detalles' => 'required',
            'pasos' => 'required',
            'codigo' => 'required'
        ];
    }
    /* 000 */
    public static function validationOrdersRegistriesF2()
    {
        $result = new class{};
        $rules = self::rulesOrdersRegistriesF2();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersRegistriesF2()
    {
        return [
            // 'id_tipopedido' => 'required',
            'motivo' => 'required',
            'id_areaorigen' => 'required',
            // 'id_evento' => 'required',
            'detalles' => 'required',
            'pasos' => 'required',
            'codigo' => 'required'
        ];
    }
    public static function validationOrdersRegistriesF3()
    {
        $result = new class{};
        $rules = self::rulesOrdersRegistriesF3();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersRegistriesF3()
    {
        return [
            // 'id_tipopedido' => 'required',
            'motivo' => 'required',
            // 'id_areaorigen' => 'required',
            // 'id_deptoorigen' => 'required',
            // 'id_evento' => 'required',
            // 'detalles' => 'required',
            'pedidocompra' => 'required',
            'pasos' => 'required',
            'codigo' => 'required'
        ];
    }
    /* xxxxx */
    private static function rulesOrdersRegistriesFPPA()
    {
        return [
            'motivo' => 'required',
            'pedidocompra' => 'required',
            'pasos' => 'required',
            'codigo' => 'required'
        ];
    }
    /* .pedido_detalle */
    public static function validationOrdersDetails()
    {
        $result = new class{};
        $rules = self::rulesOrdersDetails();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersDetails()
    {
        return [
            'id_pedido' => 'required',
            'id_articulo' => 'required',
            'id_almacen' => 'required',
            'cantidad' => 'required|numeric|not_in:0',
            'precio' => 'required|numeric|not_in:0'
        ];
    }
    /* pedido_detalle. */
    /* PEDIDO_PLANTILLA_COMPRA */
    private static function rulesOrdersTemplatesPurchases()
    {
        return [
            // 'detalle' => 'required',
            // 'porcentaje' => 'required|numeric',
            // // 'cantidad' => 'required|numeric',
            // // 'precio' => 'required|numeric',
            // 'importe' => 'nullable|numeric',
            // 'importe_me' => 'nullable|numeric'
            'id_pedido' => 'required',
            'id_fondo' => 'required',
            'id_tipoplan' => 'required',
            'id_cuentaaasi' => 'required',
            'id_restriccion' => 'required',
            // 'id_ctacte' => 'required',
            'detalle' => 'required',
            // 'porcentaje' => 'required',
            // 'cantidad' => 'required',
            // 'precio' => 'required',
            'importe' => 'required'
            // 'importe_me' => 'required',
        ];
    }
    /* .COMPRA_ENTIDAD_DEPTO_PLANTILLA */
    public static function validationPurchasesEntityDeptoTemplates()
    {
        $result = new class{};
        $rules = self::rulesPurchasesEntityDeptoTemplates();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesPurchasesEntityDeptoTemplates()
    {
        return [
            'id_plantilla' => 'required',
            'id_depto' => 'required'
        ];
    }
    /* COMPRA_ENTIDAD_DEPTO_PLANTILLA. */
    /* .COMPROBANTES */
    public static function validationComprobante00()
    {
        $result = new class{};
        $rules = self::rulesComprobante00();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante00()
    {
        return [
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|min:1|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante01()
    {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = self::rulesComprobante01E();
        }
        else
        {
            $rules = self::rulesComprobante01();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        /* $es_ret_det = Input::get("es_ret_det");
        if($es_ret_det == "R")
        {
            $rules2 = [
                'retencion_importe' => 'required',
                'retencion_serie' => 'required',
                'retencion_numero' => 'required',
                'retencion_fecha' => 'required'
            ];
            $validator2 = Validator::make(Input::all(), $rules2);
            if ($validator2->fails())
            {
                $errorString = implode(",",$validator2->messages()->all());
                $result->valid   = false;
                $result->invalid = true;
                $result->message = $errorString;
                return $result;
            }
        }
        else if($es_ret_det == "D")
        {
            $rules2 = [
                'detraccion_numero' => 'required',
                'detraccion_fecha' => 'required',
                'detraccion_importe' => 'required',
                'detraccion_banco' => 'required'
            ];
            $validator2 = Validator::make(Input::all(), $rules2);
            if ($validator2->fails())
            {
                $errorString = implode(",",$validator2->messages()->all());
                $result->valid   = false;
                $result->invalid = true;
                $result->message = $errorString;
                return $result;
            }
        } */
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante01E()
    {
        return [
            //'serie' => 'required|min:4|max:4|regex:/^[F]{1,3}[0-9]{3}/u',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'es_activo' => 'required',
            'tiene_kardex' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            // 'base_inafecta' => 'required',
            'otros' => 'required',
            // 'es_ret_det' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    private static function rulesComprobante01()
    {
        return [
            //'serie' => 'required|min:4|max:4|regex:/^[0-9]+$/u',
            'serie' => 'required|min:4|max:4',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'es_activo' => 'required',
            'tiene_kardex' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            // 'base_inafecta' => 'required',
            'otros' => 'required',
            // 'es_ret_det' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante02()
    {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = self::rulesComprobante02E();
        }
        else
        {
            $rules = self::rulesComprobante02();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante02E()
    {
        return [
            'serie' => 'required|min:4|max:4|regex:/^[E]{1}[0]{1}[0]{1}[1]{1}/u',
            'numero' => 'required|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'tiene_suspencion' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    private static function rulesComprobante02()
    {
        return [
            'serie' => 'required|min:3|max:3|regex:/^[0-9]{3}/u',
            'numero' => 'required|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'tiene_suspencion' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante03()
    {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = self::rulesComprobante03E();
        }
        else
        {
            $rules = self::rulesComprobante03();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante03E()
    {
        return [
            'serie' => 'required|min:4|max:4', // required|min:4|max:4|regex:/^[B]{1}[0-9]{3}/u
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'es_activo' => 'required',
            'tiene_kardex' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    private static function rulesComprobante03()
    {
        return [
            'serie' => 'required|min:4|max:4|regex:/^[0-9]{3}/u',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'es_activo' => 'required',
            'tiene_kardex' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante04()
    {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = self::rulesComprobante04E();
        }
        else
        {
            $rules = self::rulesComprobante04();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante04E()
    {
        return [
            'serie' => 'required|max:4|regex:/^[E]{1}[0]{1}[0]{1}[1]{1}/u',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    private static function rulesComprobante04()
    {
        return [
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/',
            'numero' => 'required|max:7|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante05()
    {
        $result = new class{};
        $rules = self::rulesComprobante05();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante05()
    {
        return [
            'serie' => 'required|min:4|max:4',
            // 'serie' => 'required|max:1|regex:/^[1-5]{1}/u',
            'numero' => 'required|max:11|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'tax_igv' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'es_activo' => 'required',
            'tiene_kardex' => 'required',
            'id_moneda' => 'required',
            'es_ret_det' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante06()
    {
        $result = new class{};
        $rules = self::rulesComprobante06();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante06()
    {
        return [
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/|not_in:0',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante07()
    {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = self::rulesComprobante07E();
        }
        else
        {
            $rules = self::rulesComprobante07();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante07E()
    {
        return [
            //'serie' => ['required', 'min:4', 'max:7', 'regex:/^((E001)|(EB01)|([F]{1}[0-9]{3})|([B]{1}[0-9]{3}))\d{0}$/i', 'not_in:F000,B000'],
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_parent' => 'required',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    private static function rulesComprobante07()
    {
        return [
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_parent' => 'required',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante08()
    {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S")
        {
            $rules = self::rulesComprobante08E();
        }
        else
        {
            $rules = self::rulesComprobante08();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante08E()
    {
        return [
            //'serie' => ['required', 'min:4', 'max:7', 'regex:/^((E001)|(EB01)|([F]{1}[0-9]{3})|([B]{1}[0-9]{3}))\d{0}$/i', 'not_in:F000,B000'],
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_parent' => 'required',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    private static function rulesComprobante08()
    {
        return [
            'serie' => 'required|min:4|max:4|regex:/^[0-9\s]+$/',
            'numero' => 'required|max:10|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_parent' => 'required',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante09()
    {
        $result = new class{};
        $rules = self::rulesComprobante09();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante09()
    {
        return [
            'serie' => 'max:20',
            'numero' => 'max:20',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante10()
    {
        $result = new class{};
        $rules = self::rulesComprobante10();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante10()
    {
        return [
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante12()
    {
        $result = new class{};
        $rules = self::rulesComprobante12();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante12()
    {
        return [
            'serie' => 'required|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|max:20|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante13()
    {
        $result = new class{};
        $rules = self::rulesComprobante13();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante13()
    {
        return [
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|max:20|regex:/^[a-zA-Z0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante14()
    {
        $result = new class{};
        $rules = self::rulesComprobante14();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante14()
    {
        return [
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|min:1|max:20|regex:/^[a-zA-Z0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante15()
    {
        $result = new class{};
        $rules = self::rulesComprobante15();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante15()
    {
        return [
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|min:1|max:20|regex:/^[a-zA-Z0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante16()
    {
        $result = new class{};
        $rules = self::rulesComprobante16();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante16()
    {
        return [
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|min:1|max:20|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante37()
    {
        $result = new class{};
        $rules = self::rulesComprobante37();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante37()
    {
        return [
            'serie' => 'nullable|max:20|regex:/^[a-zA-Z0-9\s]+$/',
            'numero' => 'required|max:20|regex:/^[0-9\s]+$/|not_in:0',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante46()
    {
        $result = new class{};
        $rules = self::rulesComprobante46();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante46()
    {
        return [
            'serie' => 'max:20',
            'numero' => 'max:20',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante50()
    {
        $result = new class{};
        $rules = self::rulesComprobante50();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante50()
    {
        return [
            'serie' => 'max:20',
            'numero' => 'max:20',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    public static function validationComprobante91()
    {
        $result = new class{};
        $rules = self::rulesComprobante91();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesComprobante91()
    {
        return [
            'serie' => 'max:20',
            'numero' => 'max:20',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'id_moneda' => 'required',
            'fecha_doc' => 'required'
        ];
    }
    /* COMPROBANTES. */
    public static function validationAnhoMes($id_entidad)
    {
        $result = new class{};
        $data_anho = AccountingData::showPeriodoActivo($id_entidad);
        foreach ($data_anho as $item)
        {
            $id_anho = $item->id_anho;
            $id_anho_actual = $item->id_anho_actual;
        }
        if($id_anho != $id_anho_actual)
        {
            $result->valid   = false;
            $result->invalid = true;
            $result->message = "No Existe Año Activo.";
            return $result;
        }
        $data_mes = AccountingData::showMesActivo($id_entidad, $id_anho);
        foreach ($data_mes as $item)
        {
            $id_mes = $item->id_mes;
            $id_mes_actual = $item->id_mes_actual;                
        }
        if($id_mes == $id_mes_actual)
        {
            $result->valid   = false;
            $result->invalid = true;
            $result->message = "No Existe Mes Activo.";
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    public static function validationOrdersRegistriesFRH(){
        $result = new class{};
        $rules = self::rulesOrdersRegistriesFRH();
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesOrdersRegistriesFRH(){
        return [
            // 'id_tipopedido' => 'required',
            'motivo' => 'required',
            // 'id_areaorigen' => 'required',
            // 'id_deptoorigen' => 'required',
            // 'id_evento' => 'required',
            // 'detalles' => 'required',
            'pedidocompra' => 'required',
            'pasos' => 'required',
            'codigo' => 'required'
        ];
    }
    public static function validationComprobante87()
    {
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S"){
            $rules = self::rulesComprobante07E();
        }else{
            $rules = self::rulesComprobante07();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    public static function validationComprobante88(){
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S"){
            $rules = self::rulesComprobante08E();
        }else{
            $rules = self::rulesComprobante08();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    public static function validationComprobante17(){
        $result = new class{};
        $es_electronica = Input::get("es_electronica");
        if($es_electronica == "S"){
            $rules = self::rulesDocument17();
        }else{
            $rules = self::rulesDocument17();
        }
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()){
            $errorString = implode(",",$validator->messages()->all());
            $result->valid   = false;
            $result->invalid = true;
            $result->message = $errorString;
            return $result;
        }
        $result->valid   = true;
        $result->invalid = false;
        $result->message = "";
        return $result;
    }
    private static function rulesDocument17()
    {
        return [
            'serie' => 'required|min:4|max:4|regex:/^[0-9]{3}/u',
            'numero' => 'required|min:1|max:10|regex:/^[0-9]+$/u',
            'importe' => 'required|numeric',
            'id_pedido' => 'required',
            'id_pcompra' => 'required',
            'id_proveedor' => 'required',
            'es_activo' => 'required',
            'tiene_kardex' => 'required',
            'id_moneda' => 'required',
            'es_electronica' => 'required',
            'fecha_doc' => 'required'
        ];
    }
}