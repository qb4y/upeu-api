<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 15/01/20
 * Time: 06:38 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{

    protected $table='DAVID.VW_ACAD_PROGRAMA_ESTUDIO';
    protected $primaryKey='id_programa_estudio';


    protected $fillable = [
        "id_programa_estudio",
        "id_sede",
        "id_sedearea",
        "sede",
        "id_facultad",
        "nombre_facultad",
        "id_escuela",
        "nombre_escuela"
    ];


    public function comments()
    {
        return $this->hasMany(ProgramSemester::class, 'id_programa_estudio', 'id_programa_estudio');
    }

    public function semesters() {
        return $this->belongsToMany(Semester::class, 'DAVID.ACAD_SEMESTRE_PROGRAMA', 'id_programa_estudio', 'id_semestre');
    }

}