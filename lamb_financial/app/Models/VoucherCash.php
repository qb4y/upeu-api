<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan  ( @julnarot )
 * Date: 15/02/21
 * Time: 11:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class VoucherCash extends Model
{
    protected $table = 'CAJA_VALE';
    protected $primaryKey = 'ID_VALE';


    protected $fillable = [
        'ID_VALE',
        'ID_ENTIDAD',
        'ID_DEPTO',
        'ID_ANHO',
        'ID_MES',
        'ID_TIPOVALE',
        'ID_DINAMICA',
        'ID_PERSONA',
        'ID_EMPLEADO',
        'ID_MEDIOPAGO',
        'MEDIO_PAGO',
        'ID_CTABANCARIA',
        'ID_CHEQUERA',
        'ID_MONEDA',
        'FECHA',
        'FECHA_VENCIMIENTO',
        'IMPORTE',
        'IMPORTE_ME',
        'TIPO_CAMBIO',
        'DETALLE',
        'CTA_BANCARIA',
        'CELULAR',
        'EMAIL',
        'CODIGO_CONSEJO',
        'TERMINO_CONDICION',
        'ESTADO',
        'ID_VOUCHER',
        'ID_TIPOORIGEN',
        'NUMERO',
        'FECHA_REG', 'NRO_VALE',
        'ID_PERSONA_AUTO',
        'PAGADO',
        'MOTIVO'
    ];

    //const CREATED_AT = null;
    //const UPDATED_AT = null;

    public function files()
    {
        return $this->hasMany(VoucherCashFile::class, 'id_vale', 'id_vale')->whereNotIn('tipo', ['3','4'])->orderBy('tipo');
    }

    public function typeVoucherCash()
    {
        return $this->belongsTo(TypeVoucherCash::class, 'id_tipovale', 'id_tipovale');
    }

    public function register()
    {
        return $this->belongsTo(Person::class, 'id_persona', 'id_persona');
    }

    public function beneficiary()
    {
        return $this->belongsTo(Person::class, 'id_empleado', 'id_persona');
    }

    public static function boot()
    {
        parent::boot();
// mastering deleted init
        static::deleting(function ($voucherCash) {
            $voucherCash->files()->delete();
        });
    }
}