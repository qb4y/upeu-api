<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 14/11/21
 * Time: 21:05
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Headquarter extends Model
{
    protected $table = 'org_sede';
    protected $primaryKey = 'id_sede';
    public $timestamps = false;

    protected $fillable = [
        "id_sede",
        "nombre",
        "codigo",
        "id_depto",
    ];

}