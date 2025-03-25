<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 15/11/21
 * Time: 09:05
 */

namespace App\Http\Controllers\Setup\Organization;

use App\Models\EntityArea;
use Illuminate\Http\Request;

class AreaController
{
    public function index(Request $request)
    {
        /// dd(Session) getting token by session
        return response()->json([
            'success' => true,
            'data' => EntityArea::filter($request->all())->get(),
            'message' => 'ok',
        ], 200);
    }
}