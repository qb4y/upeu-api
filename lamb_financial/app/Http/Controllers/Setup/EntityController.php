<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 19/10/21
 * Time: 10:57
 */

namespace App\Http\Controllers\Setup;

use App\Models\Entity;

class EntityController
{

    function index()
    {
        return array('data' => Entity::get());
    }
}