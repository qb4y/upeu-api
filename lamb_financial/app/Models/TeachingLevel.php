<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 08/01/20
 * Time: 05:25 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingLevel extends Model {

    protected $table='DAVID.TIPO_NIVEL_ENSENANZA';
    protected $primaryKey='id_nivel_ensenanza';

    protected $fillable = [
        "id_nivel_ensenanza",
        "nombre",
        "codigo",
        "estado"
    ];

}