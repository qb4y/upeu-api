<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 9/9/21
 * Time: 8:10 PM
 */

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use App\Http\Requests\PedidoRegistroRequest;
use App\Models\PedidoRegistro;
use Illuminate\Http\Request;

class OrderRegisterController  extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function update(PedidoRegistroRequest $request, $id)
    {

        $response = GlobalMethods::authorizationLamb($this->request);

        if ($response["valida"] == 'SI') {
            PedidoRegistro::where('id_pedido', $id)
                ->update($request->all());
            $response = array();
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = PedidoRegistro::find($id);
        }

        return response()->json($response, $response["code"]);
    }
    public function show($id)
    {

        $response = GlobalMethods::authorizationLamb($this->request);

        $params = $this->request->all();

        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = PedidoRegistro::find($id);
        }

        return response()->json($response, $response["code"]);
    }
}