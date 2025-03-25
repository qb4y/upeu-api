<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentCourses extends Model
{
    protected $table = 'david.acad_alumno_contrato_curso';
    protected $primaryKey = 'id_alumno_contrato_curso';
    protected $fillable = ['id_alumno_contrato_curso','id_alumno_contrato','id_curso_alumno','id_usuario_reg','fecha_registro','id_usuario_act','fecha_actualizacion','id_tipo_movimiento_var'];

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';
}
