<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CriterionSemester extends Model
{

    protected $table = 'ELISEO.MAT_CRITERIO_SEMESTRE';
    protected $primaryKey = 'ID_CRITERIO_SEMESTRE';


    protected $fillable = [
        "ID_CRITERIO_SEMESTRE",
        "ID_SEMESTRE_PROGRAMA",
        "ID_CRITERIO",
        "TIPO_PROCESO",
        "IMPORTE",
        "FORMULA"
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;


    public function program()
    {
        return $this->belongsTo(ProgramSemester::class, 'id_semestre_programa');
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class, 'id_semestre');
    }

}