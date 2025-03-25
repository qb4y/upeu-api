<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 12/04/21
 * Time: 15:30
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AccountingVoucher extends Model
{

    protected $table = 'ELISEO.CONTA_VOUCHER';
    protected $primaryKey = 'id_voucher';
    public $incrementing = false;
    protected $fillable = [
        'id_voucher',
        'id_depto',
        'lote',
        'id_depto',
    ];
}