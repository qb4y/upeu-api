<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 14/11/21
 * Time: 17:48
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HeadquartersArea extends Model
{
    protected $table = 'eliseo.org_sede_area';
    protected $primaryKey = 'id_sedearea';
    public $timestamps = false;

    protected $fillable = [
        "id_sedearea",
        "id_entidad",
        "id_depto",
        "id_sede",
        "id_area",
        "codigo",
    ];
    protected $casts = [
        'id_entidad' => 'integer',
        'id_sede' => 'integer',
        'id_area' => 'integer',
    ];
    protected $appends = ['sede_area'];
    protected $with = [
        'area:id_area,nombre'
    ];
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('exclude_deleted', function (Builder $builder) {
            $builder->whereHas('area');
        });
    }

    public function getSedeAreaAttribute()
    {
        return $this->id_depto." - ".$this->area->nombre.", ".$this->headQuarter->nombre;
    }

    public function area()
    {
        return $this->belongsTo(EntityArea::class, 'id_area');
    }

    public function headQuarter()
    {
        return $this->belongsTo(Headquarter::class, 'id_sede');
    }

    public function scopeFilter($query, $params)
    {
        if (isset($params['id_entidad']) && trim($params['id_entidad'] !== '')) {
            $query->where('id_entidad', $params['id_entidad']);
        }
        if (isset($params['id_depto']) && trim($params['id_depto'] !== '')) {
            $query->where('id_depto', $params['id_depto']);
        }
        if (isset($params['id_sede']) && trim($params['id_sede'] !== '')) {
            $query->where('id_sede', $params['id_sede']);
        }
        if (isset($params['id_area']) && trim($params['id_area'] !== '')) {
            $query->where('id_area', $params['id_area']);
        }
        if (isset($params['q']) && trim($params['q'] !== '')) {
            //$query->where('org_area.id_area', 'LIKE', '%' . $params['q'] . '%');
            //->whereRaw("upper(codigo) || upper(id_depto) like to_char(upper('%?%'))", [$params['q']]);
            $query->whereHas('area', function ($query) use ($params) {
                //$query->where('org_area.nombre', 'LIKE', '%' . $params['q'] . '%'); // uppercase does not work
                //$query->whereRaw("upper(org_area.nombre) like to_char(upper('%?%'))", [$params['q']]);
                $query->whereRaw("UPPER(org_area.nombre)  like UPPER('%{$params['q']}%')");
            });
        }
        return $query;
    }
}