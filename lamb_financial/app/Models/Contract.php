<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 30/03/20
 * Time: 10:30 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'DAVID.ACAD_ALUMNO_CONTRATO';
    protected $primaryKey = 'id_alumno_contrato';
    protected $fillable = [
        'id_alumno_contrato',
        'id_tipo_contrato',
        'id_persona',
        'id_semestre_programa',
        'id_plan_pago_semestre',
        'id_resid_tipo_habitacion',
        'id_tipo_resp_financiero',
        'id_resp_financiero',
        'id_nivel_ensenanza',
        'id_usuario_reg',
        'fecha_registro',
        'id_usuario_act',
        'fecha_actualizacion',
        'id_plan_programa',
        'id_matricula_detalle',
        'estado',
        'dias_residencia',
        'misionero',
        'total_debito',
        'total_credito',
        'total',
        'matricula',
        'mensual',
        'contado',
        'pago',
        'matricula1cuota',
        'tipo_alumno',
        'id_cliente_legal',
        'id_comprobante',
        'codigo',
        'id_solicitud_mat_alum',
        'id_alumno_contrato_clon',
        'id_alumno_contrato_asociado',
        'origen',
        'id_sem_prog_pago', // eliminado en obs
        'id_residencia',// eliminado en obs
        'ciclo',
        'fecha_matricula' // add fecha_matricula
    ];

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';



}
