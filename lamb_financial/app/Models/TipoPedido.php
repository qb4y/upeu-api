

<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 14/01/20
 * Time: 06:17 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPedido extends Model
{

    protected $table = 'eliseo.tipo_pedido';
    protected $primaryKey ='id_tipopedido';
    public $timestamps = false;
    
    protected $fillable = [
      'id_tipopedido','nombre','estado'
    ];

}
