<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 8/26/21
 * Time: 4:24 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'ELISEO.COMPRA';
    protected $primaryKey = 'id_compra';

    protected $fillable = [
        'es_ret_det'
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;
}