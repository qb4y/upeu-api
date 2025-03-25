<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 15/04/20
 * Time: 11:15 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StudentCourses extends Model
{
    protected $table = 'david.acad_curso_alumno';
    protected $primaryKey = 'id_curso_alumno';
    protected $fillable = ['id_curso_alumno','id_persona','id_plan_programa','id_carga_curso','id_curso_detalle','id_horario_practica','promedio','logro','alcanzado','id_tipo_tramite','id_tipo_condicion','id_plan_curso','id_usuario_reg','fecha_registro','id_usuario_act','fecha_actualizacion','codigo','estado', 'id_tipo_movimiento_var'];

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';
}
