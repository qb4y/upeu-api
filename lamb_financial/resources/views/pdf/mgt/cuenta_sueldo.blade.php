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
    <label for=""><strong>AUTORIZACIÓN PARA REALIZAR GESTIONES PARA APERTURA DE CUENTA SUELDO</strong></label>
</div>
<br>
<br>
<?php
setlocale(LC_TIME, 'es_ES');
?>

<div style="text-align:justify">
<p>Yo {{$nombre_trabajador}} identificado(a) con DNI N° {{$num_doc}} con domicilio en {{$domicilio}} ,
    AUTORIZO a mi empleadora la Universidad Peruana Unión  a realizar, en mi nombre y representación, 
    las gestiones relativas a la APERTURA DE CUENTA SUELDO, que se gestiona a través del Banco : {{$banco}}
</p>
<p>La presente autorización, la realizo de manera voluntaria  y en pleno uso de mis facultades.</p>
</div>
<div style="text-align:left">
    <p>{{$fecha}}</p>
</div>

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