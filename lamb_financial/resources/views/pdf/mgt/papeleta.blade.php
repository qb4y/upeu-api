@extends('layouts.pdf')
@section('content')
<style type="text/css" media="screen">

.font-size-10 {
  font-size: 10px !important;
}

.head-info {
	background-color: #7f264a;
	color: white;
	padding: 4px;
	text-transform: uppercase;
	font-weight: 600;
	font-size: .80rem;
	
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.text-left {
  text-align: left !important;
}

.text-right {
  text-align: right !important;
}

.text-center {
  text-align: center !important;
}

.page-break {
    page-break-after: always;
}

.izq {
      text-align: left;
      
    }
    .der {
      text-align: right;
      
    }
</style>
<div class="text-center"><br>
<label for=""><strong>PAPELETA DE VACACIONES</strong></label>
</div>
<br>
<br>
<?php
setlocale(LC_TIME, 'es_ES');

  $fecha_ini = explode('-', $fecha_inicio_programacion);
  $anhio_ini = $fecha_ini[0];
  $mes_ini = $fecha_ini[1];
  $dia_ini = $fecha_ini[2];

  $fecha_fin = explode('-', $fecha_fin_programacion);
  $anhio_fin = $fecha_fin[0];
  $mes_fin = $fecha_fin[1];
  $dia_fin = $fecha_fin[2];

  
  $month = DateTime::createFromFormat('!m', $mes_ini);
  $mes = strftime("%B", $month->getTimestamp());
  $mes_programado_ini = ucfirst($mes);


  $month_fin = DateTime::createFromFormat('!m', $mes_fin);
  $mesF = strftime("%B", $month_fin->getTimestamp());
  $mes_programado_fin = ucfirst($mesF);

  $mes_programado =  $mes_programado_ini;

  if ($mes_programado_ini !== $mes_programado_fin) {
    $mes_programado = $mes_programado_ini.' - '.$mes_programado_fin;
    if ($anhio_ini !== $anhio_fin) {
      $mes_programado =  $mes_programado_ini.'   -   '.$anhio_ini.'   '.'   a   '.'   '.$mes_programado_fin.'    -    '.$anhio_fin;
    }
  }

  $fecha_confir  = explode('-', $fecha_confirmacion);
  $anhio_confir = $fecha_confir[0];
  $mes_confir = $fecha_confir[1];
  $dia_confir = $fecha_confir[2];

  $monthConfir = DateTime::createFromFormat('!m', $mes_confir);
  $mesConf = strftime("%B", $monthConfir->getTimestamp());
  $mes_confirmado = ucfirst($mesConf); 


?>
<div>
<Label><strong> Nombre y apellidos del servidor:</strong>&nbsp;&nbsp;&nbsp;&nbsp;{{$nombre_trabajador}}</Label>
</div><br>
<div>
<Label><strong>Facultad/Área:</strong>&nbsp;&nbsp;&nbsp;&nbsp; {{$area_trabajador}}</Label>
</div><br>
<div>
<Label><strong>Cargo: </strong>&nbsp;&nbsp;&nbsp;&nbsp; {{$puesto}}</Label>
</div><br>
<div>
<Label><strong>Mes programado: </strong>&nbsp;&nbsp;&nbsp;&nbsp;{{$mes_programado}}</Label>
</div><br>

<div>
<Label><strong>Duración:</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>del</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$dia_ini}}/{{$mes_ini}}/{{$anhio_ini}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 <strong>al</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$dia_fin}}/{{$mes_fin}}/{{$anhio_fin}}</Label>
</div><br>
<div>
<Label><strong>Total de días:</strong> &nbsp;&nbsp;&nbsp;&nbsp;{{$dias_programadas}}&nbsp;&nbsp;&nbsp;&nbsp;.días</Label>
</div><br><br><br><br><br>
<div>
<label class="d-flex align-items-center justify-content-between">
<strong> AUTORIZACION  </strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Lima, &nbsp;&nbsp;{{$dia_confir}} &nbsp;&nbsp;de &nbsp;&nbsp;{{$mes_confirmado}} &nbsp;&nbsp;del &nbsp;&nbsp;{{$anhio_confir}}</strong> 
</label></div>
<br><br>

<div class="text-center">
@if($firma_trabajador)
<img src="{{$firma_trabajador}}"   width="100px" height="100px"><br>
@endif
@if(!$firma_trabajador)
<br><br><br><br><br>
@endif
<label for="">________________________________</label><br>
<strong>Trabajador</strong>
</div>
<br><br>
<table>
          <tr class="text-center">
            <td  class="text-left">
            <div  class="text-center">
                @if($firma_gthr)
                <img src="{{$firma_gthr}}"   width="100px" height="100px"><br>
                @endif
                @if(!$firma_gthr)
                <br><br><br><br><br>
                @endif
                <label for="">________________________________</label><br>
                <strong class="text-center">{{$nombre_jefe_dth}}</strong><br>
                <strong>Gerente del Recursos Humanos</strong>
                </div>
            </td>
            <td>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td  class="text-right">
            <div  class="text-center">
                @if($firma_jefe)
                <img src="{{$firma_jefe}}"   width="100px" height="100px"><br>
                @endif
                @if(!$firma_jefe)
                <br><br><br><br><br>
                @endif
                <label for="">________________________________</label><br>
                <strong class="text-center">{{$nombre_jefe}}</strong><br>
                <strong>V° B° de Jefe del área </strong>
                </div>
            </td>
          </tr>
        </table>

  @endsection