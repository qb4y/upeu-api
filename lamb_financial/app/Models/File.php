<?php


use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use Filterable;
    protected $table = 'acad_archivo';

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $primaryKey = 'id_archivo';
    protected $fillable = [
        'id_archivo',
        'ruta',
        'id_usuario_act',
        'id_usuario_reg',
        'estado'
    ];
    protected $visible = [
        'id_archivo',
        'ruta',
        'id_usuario_act',
        'id_usuario_reg',
        'estado',
        'url'
    ];
    public $incrementing = true;

    public $appends = array('url');


    public function getUrlAttribute()
    {
        return  \Storage::cloud()->temporaryUrl($this->ruta, \Carbon\Carbon::now()->addMinutes(1));
    }
}

