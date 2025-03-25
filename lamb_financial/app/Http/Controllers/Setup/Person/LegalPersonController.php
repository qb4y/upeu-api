<?php
/**
 * Created by PhpStorm.
 * User: Rau Jonatan ( @Julnarot )
 * Date: 8/23/21
 * Time: 3:49 PM
 */

namespace App\Http\Controllers\Setup\Person;

use App\Http\Data\GlobalMethods;
use App\Http\Data\Setup\LegalPersonData;
use App\Models\LegalPerson;
use Illuminate\Http\Request;

class LegalPersonController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function update($id)
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = LegalPersonData::updateDataById($id, $this->request->all());
        }
        return response()->json($response, $response["code"]);
    }
    public function show($id)
    {
        $response = GlobalMethods::authorizationLamb($this->request);
        if ($response["valida"] == 'SI') {
            $response["code"] = 200;
            $response['success'] = true;
            $response['message'] = 'OK';
            $response['data'] = LegalPerson::find($id);
        }
        return response()->json($response, $response["code"]);
    }
}