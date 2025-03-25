<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 14/11/21
 * Time: 17:56
 */

namespace App\Http\Controllers\Setup\Organization;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\HeadquartersArea;

class HeadQuarterAreaController extends Controller
{

    public function index(Request $request)
    {
//        dd($_COOKIE, $_REQUEST, $_SERVER, $_GET, $_COOKIE, $_ENV, $_POST);
        return response()->json([
            'success' => true,
            'data' => HeadquartersArea::filter($request->all())->get(),
            'message' => 'ok',
        ], 200);
    }
}