<?php
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 14/01/20
 * Time: 06:07 PM
 */



namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{

    protected $table='ELISEO.MAT_PLANPAGO';
    protected $primaryKey='id_planpago';


    protected $fillable = [
        "id_planpago",
        "nombre",
        "codigo",
        "conmat1cuota",
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;



   // public function details()
    //{
    //    return $this->hasMany(PaymentPlanSem::class, 'id_planpago','id_planpago');
        //->join(
        //    'ELISEO.MAT_PLANPAGO_SEMESTRE_DET',
        //    'ELISEO.MAT_PLANPAGO_SEMESTRE.ID_PLANPAGO_SEMESTRE','=','ELISEO.MAT_PLANPAGO_SEMESTRE_DET.ID_PLANPAGO_SEMESTRE');
    //}

    public function countDetails()
    {
        return $this->hasMany(PaymentPlanSem::class, 'id_planpago')->join(
            'ELISEO.MAT_PLANPAGO_SEMESTRE_DET','ELISEO.MAT_PLANPAGO_SEMESTRE.ID_PLANPAGO_SEMESTRE','=','ELISEO.MAT_PLANPAGO_SEMESTRE_DET.ID_PLANPAGO_SEMESTRE')
            ->count();
    }





}