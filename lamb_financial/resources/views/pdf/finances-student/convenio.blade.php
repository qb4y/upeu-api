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
p {
  font-size: 10px;
  text-align : justify;
}
.title {
  font-size: 12px;
}
.subtitle {
  font-size: 11px;
}
.letra {
  font-size: 10px;
  text-align : justify;
}
</style>



  <div class="text-right">
  <strong class="subtitle">N°:&nbsp;&nbsp;{{$convenio->numero}}</strong>
  </div>
<div class="text-left">
<strong class="subtitle">Aréa / Departamento:&nbsp;&nbsp;</strong>
</div><br>
<div class="text-center">
  <strong class="title">CONVENIO DE DESCUENTO DE REMUNERACIONES Y/O GRATIFICACIONES POR PRESTACIÓN Y/O DEUDAS DE SERVICIOS EDUCATIVOS</strong>
</div>
<p>
Conste por el presente documento el Convenio de Descuento de Remuneraciones y/o Gratificaciones por Prestación y/o Deudas de Servicios Educativos que suscriben, de una parte,
la UNIVERSIDAD PERUANA UNIÓN, con RUC N° 20138122256, con domicilio legal en Villa Unión s/n., Ñaña, altura del Km. 19 de la Carretera Central, distrito de Lurigancho-Chosica,
provincia y departamento de Lima, a la que en adelante se le denominará LA UNIVERSIDAD, representada por su Gerente Financiero CPC Torres Nuñez, Mirtha Jeanette,
identificado con DNI N° 10600855, quien señala el mismo domicilio de su  representada; 
y de  la  otra parte  el(a) Srta., Sra., Sr. &nbsp;&nbsp;&nbsp;&nbsp;{{$datos_empleado->nombre_empleado}}&nbsp;&nbsp;&nbsp;&nbsp;, identificado(a) con  DNI N°&nbsp;&nbsp;{{$datos_empleado->num_documento}}&nbsp;&nbsp;,
con domicilio en …………………….……………………………………………………………………..…………..,  a quien en adelante se le denominará EL(A) TRABAJADOR(A), de acuerdo a los términos y condiciones siguientes:
</p>
<p>
PRIMERO: EL(A) TRABAJADOR(A) mantiene una relación laboral con LA UNIVERSIDAD, quien lo tiene en su Planilla de Remuneraciones.
</p>
<p>
SEGUNDO: EL(A) TRABAJADOR(A), con  Código de Alumno(a) N°&nbsp;&nbsp;&nbsp;&nbsp;{{$datos_alumno->codigo}}&nbsp;&nbsp;&nbsp;&nbsp;de la UPG……………………………………………………………………..Ciclo al que pasa………..……………………..…… actualmente en  LA UNIVERSIDAD
</p>
<p>
TERCERO: Por el presente documento, EL(A) TRABAJDOR(A) y LA UNIVERSIDAD convienen en que ésta última descontará mensualmente de la remuneración y/o gratificación de EL(A) TRABAJADOR(A) 
el costo de la matrícula y/o pensión mensual de enseñanza y otros costos conexos por los estudios como alumno(a) en LA UNIVERSIDAD. Motivo del descuento&nbsp;&nbsp;&nbsp;&nbsp;{{$convenio->observaciones}}&nbsp;&nbsp;&nbsp;&nbsp;
</p>
<p>
CUARTO: Dicho descuento será hasta por un total de S/. …{{number_format($convenio->total, 2)}}….SOLES), disgregado en cuotas mensuales conforme al detalle siguiente:
</p>
<div class="table table-responsive">
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>N° cuota</th>
        <th>Fecha de pago</th>
        <th class="text-right">Monto</th>
      </tr>
    </thead>
    <tbody>
    
    @foreach($convenio_detalle as $deta)
      <tr>
        <td>{{$deta->cuota}}</td>
        <td>{{$deta->fecha}}</td>
        <td class="text-right">{{number_format($deta->importe, 2)}}</td>
      </tr>
      @endforeach
      <tr>
        <td colspan="2" class="text-right">Total</td>
        <td class="text-right">{{number_format($convenio->total, 2)}}</td>
      </tr>
    </tbody>
  </table>
</div>
<p>
QUINTO: Ambas partes convienen en que si EL(A) TRABAJADOR(A) concluye su relación laboral con LA UNIVERSIDAD,
ésta última le descontará de sus beneficios sociales el monto o saldo pendiente de pago por el costo del servicio educativo.
</p>
<p>
SEXTO: Para todos los efectos relacionados con el presente convenio, las partes señalan como sus domicilios los que aparecen consignados en la parte introductoria de éste convenio,
los cuales se tendrán por válidos en tanto la variación no haya sido comunicada por escrito a la otra parte con una antelación no menor a tres (03) días hábiles.
</p>
<p>
Las partes, después de leído el presente convenio, se ratifican en su contenido, lo suscriben en señal de conformidad en dos ejemplares y EL(A) TRABAJADOR(A) consigna su huella dactilar,
 en la ciudad de Lima, a los………………………………..……… del año 2020 de lo que damos fe
</p><br>

<table>
          <tr class="text-center">
            <td >
            <div  class="text-center">

                <label for="">______________________________</label><br>
                <strong class="letra">CPC Torres Nuñez Mirtha Jeanette</strong><br>
                Gerente Financiero <br>
                UNIVERSIDAD PERUANA UNIÓN <br>
                LA UNIVERSIDAD
                </div>
            </td>
            <td>
            <div  class="text-center">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <label for="">______________________</label><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    <strong class="letra">EL(A) TRABAJADOR(A)</strong>
                </div>
            </td>
            <td class="text-right">
            <div  class="text-center">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="">__________</label><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong class="letra">Huella</strong>
                
                </div>
            </td>
          </tr>
        </table>

  @endsection