<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 14/11/21
 * Time: 17:50
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EntityArea extends Model
{
    protected $table = 'org_area';
    protected $primaryKey = 'id_area';
    public $timestamps = false;

    protected $fillable = [
        "id_area",
        "nombre",
    ];

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('exclude_deleted', function (Builder $builder) {
            $builder->whereHas('entity');
        });
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'id_entidad');
    }

    public function scopeFilter($query, $params)
    {
        if (isset($params['id_entidad']) && trim($params['id_entidad'] !== '')) {
            $query->where('id_entidad', $params['id_entidad']);
        }

        if (isset($params['q']) && trim($params['q'] !== '')) {
            $query->whereRaw("UPPER(nombre)  like UPPER('%{$params['q']}%')");
        }
        return $query;
    }
}