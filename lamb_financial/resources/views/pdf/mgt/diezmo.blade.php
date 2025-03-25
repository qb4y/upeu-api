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
    <label for=""><strong>AUTORIZACIÓN DE DESCUENTO DE REMUNERACIONES</strong></label>
</div>
<br>
<br>
<?php
setlocale(LC_TIME, 'es_ES');
?>
<div style="text-align:justify">
    <p>Yo {{$nombre_trabajador}} identificado(a) con DNI N° {{$num_doc}} con domicilio en {{$domicilio}} , por medio de la presente AUTORIZO a mi empleadora, la Universidad Peruana Unión; descontar mensualmente 
    el 10% de mi remuneración (incluyendo gratificaciones), como diezmo o donación voluntaria e incondicional, para la Iglesia Adventista del Séptimo Día, en la consecución de sus fines.</p>
<p>Descuento, que autorizo desde la fecha y mientras dure mi relación laboral, de forma libre, espontánea y conforme a mis convicciones religiosas, como 
    feligrés de la Iglesia Adventista del Séptimo Día, y sobre los que no pediré devolución o reembolso por ningún motivo y bajo ninguna circunstancia.</p>
</div>

<div style="text-align:right">
    <p>{{$fecha}}</p>
</div>

<br>
<div class="text-center">
    @if($firma_trabajador)
    <img src="{{$firma_trabajador}}"   width="100px" height="100px"><br>
    @endif
    @if(!$firma_trabajador)
    <br><br><br><br><br>
    @endif
    <label for="">________________________________</label><br>
    <p><strong>{{$nombre_trabajador}}</strong></p>
    <p><strong>{{$num_doc}}</strong></p>
    </div>

    @endsection