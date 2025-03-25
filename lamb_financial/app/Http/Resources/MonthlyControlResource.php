<?php
 
namespace App\Http\Resources;
 
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
 
class MonthlyControlResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id_grupoarchivo' => $this->id_grupoarchivo,
            'grupoarchivo' => $this->grupoarchivo,
            'id_tipoarchivo' => $this->id_tipoarchivo,
            'tipoarchivo' => $this->tipoarchivo,
            'id_archivo_mensual' => $this->id_archivo_mensual,
            'id_empresa' => $this->id_empresa,
            'id_entidad' => $this->id_entidad,
            'id_depto' => $this->id_depto,
            'id_anho' => $this->id_anho,
            'id_mes' => $this->id_mes,
            'fecha_limite' => $this->fecha_limite,
            'tiene_puntaje' => $this->tiene_puntaje,
            'id_detalle' => $this->id_detalle,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_modificacion' => $this->fecha_modificacion,
            'file_url' => $this->file_url,
            'file_nombre' => $this->file_nombre,
            'formato' => $this->formato,
            'tamanho' => $this->tamanho,
            'id_user' => $this->id_user,
            'user_name' => $this->user_name,
            
        ];
    }
}