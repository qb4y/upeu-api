<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 19/10/21
 * Time: 11:13
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $table = 'conta_entidad';
    protected $primaryKey = 'id_entidad';
    public $timestamps = false;

    // boot filter by Session::getId()


    protected $fillable = [
        "id_entidad",
        "nombre",
    ];
}