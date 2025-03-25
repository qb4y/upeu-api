@extends('layouts.pdf')
@section('content')
<style type="text/css" media="screen">
  .text-right {
    text-align: right !important;
  }

  .text-center {
    text-align: center !important;
  }

  .refinancial {
    width: 100%;
    border-collapse: collapse;
    /* Borde de una sola línea */
  }

  .refinancial,
  .refinancial th,
  .refinancial td {
    border: 0.5px solid #333;
    /* Estilo y color del borde */
  }

  .refinancial thead {
    background-color: #ccc;
  }

  .refinancial th,
  .refinancial td {
    padding: 1px;
    text-align: center;
  }

  .refinancial tbody tr td {
    border: 0.5px solid black;
  }


  .parrafo01 {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    /* Borde de una sola línea */
  }

  .parrafo01,
  .parrafo01 th,
  .parrafo01 td {
    border: 0px solid #333;
    padding: 5.5px;
    text-align: center;
  }

  .bordered-paragraph {
    border: 1px solid #333;
    /* Borde de una sola línea */
    padding: 20px;
    text-align: center;
    /* Centrar el texto */
    margin: 0 auto;
    /* Centrar el borde en el td */
    display: inline-block;
  }

  .salto-pagina {
    page-break-after: always;
  }
</style>
<?php
setlocale(LC_TIME, 'es_ES');

$fecha = explode('-', $convenio->fecha);
$anhio = $fecha[0];
$mes = $fecha[1];
$dia = $fecha[2];

$month = DateTime::createFromFormat('!m', $mes);
$mes = strftime("%B", $month->getTimestamp());
$mes_registrado = ucfirst($mes);
?>
<div class="text-center ">
  <strong style="font-size: 12px;">SOLICITUD DE REFINANCIAMIENTO 2024-1</strong>
</div>
<table class="parrafo01">
  <tbody>
    <tr>
      <td style="text-align: left; ">
        <!-- Señores de la Comision Financiera de la {{$escuela->nombre }}. -->
        <!-- Señor (es): Comision financiare Sede Lima. -->
        @if($datos_contrato->id_sede === '1')
        Señor (es): Comision financiare Sede Lima.
        @elseif($datos_contrato->id_sede === '2')
        Señor (es): Comision financiare Sede Juliaca.
        @elseif($datos_contrato->id_sede === '3')
        Señor (es): Comision financiare Sede Tarapoto.
        @endif
      </td>
    </tr>
    <tr>
      <td style="text-align: justify; line-height: 2;">
        Yo,{{$datos_resp_fin->nombre }} identificado(a) con DNI {{$datos_resp_fin->num_documento }}, en mi calidad de responsable financiero, domiciliado(a) en {{$datos_resp_fin->direccion }} distrito de {{$datos_resp_fin->distrito }}, provincia de {{$datos_resp_fin->provincia }}, con codigo de estudiante {{$datos_contrato->codigo}}, y numero de celular de {{$datos_resp_fin->num_telefono }} de la {{$escuela->nombre }} ciclo {{$datos_contrato->ciclo2}}.
      </td>
    </tr>
  </tbody>
</table>
<table class="parrafo01">
  <tbody>
    <tr>
      <td style="text-align: left; line-height: 0.5;">
        Ante ustedes con el debido respeto me presento y expongo:
      </td>
    </tr>
    <tr>
      <td style="text-align: left; line-height: 0.5;">
        Que en el presente ciclo de han presentado invonvenientes:
      </td>
    </tr>
    <tr>
      <td style="text-align: left; line-height: 1;">
        ( ) Financieros <br>
        ( ) Salud <br>
        ( ) Otro (especificar) ____________________________________. <br>
      </td>
    </tr>
    <tr>
      <td style="text-align: justify; line-height: 1.6;">
        <b> Por lo cual he adquirido una deuda al no cumplir mi compromiso financiero con la UNIVERSIDAD dentro del tiempo estipulado. Es por ello que</b> solicito a su despacho, el refinanciamiento de la deuda pendiente que se mantiene con la universidad por los servicios educativos prestados.
      </td>
    </tr>
  </tbody>
</table>
<table class="parrafo01">
  <tbody>
    <tr>
      <td style="text-align: left;">
        <b>1.- Formulación de pago </b>
      </td>
    </tr>
    <tr>
      <td style="width: 60%;">

        <table class="refinancial">
          <thead>
            <tr>
              <td>N°</td>
              <td>Fecha de pago</td>
              <td>Monto</td>
            </tr>
          </thead>
          <tbody>
            @foreach($convenio_detalle as $item)
            <tr>
              <td>{{ $item->cuota }}</td>
              <td>{{ $item->fecha }}</td>
              <td>{{ $item->importe }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2">Total</td>
              <td>{{$convenio->total}}</td>
            </tr>
          </tfoot>
        </table>
        <div style="text-align: left;">*** (colocar su planteamiento de pago) </div>
      </td>
      <td style="width: 40%;">
        <p class="bordered-paragraph">
          Indicaciones: colocar fecha y monto. <br>
          Recuerde cumplir su compromiso. Enviar <br>
          voucher según fecha a su financista. <br>
        </p>
      </td>
    </tr>
  </tbody>
</table>
<table class="parrafo01">
  <tbody>
    <tr>
      <td style="text-align: left;">
        <b>Recordar.</b>
      </td>
    </tr>
    <tr>
      <td style="text-align: justify; line-height: 2;">
        En caso de que EL(A) RESPONSABLE FINANCIERO dejara de cancelar dos (02) pensiones consecutivas del compromiso, LA UNIVERSIDAD le(la) citará a una reunión para dar tratamiento al problema, en la cual se podrá suscribir un acuerdo (TRANSACCIÓN EXTRAJUDICIAL), que establezca la re-programación del pago de las cuotas dentro de las fechas indicadas, culminando el mismo en la última fecha del cronograma de pago. De incumplirse dicho acuerdo o de no lograrse ningún entendimiento, EL(A) ESTUDIANTE faculta a LA UNIVERSIDAD a INICIAR UN PROCESO EJECUTIVO, por el monto total más los cargos, comisiones, intereses e indemnizaciones, así como los costos y costes que genere el proceso. Dicho Proceso Judicial se dará de conformidad con lo estipulado en los artículos 1219° (inciso 1) y siguientes del Código Civil y según lo previsto en el numeral 5 del artículo 693° del Código Procesal Civil.
      </td>
    </tr>
  </tbody>
</table>
<div class="parrafo01 text-right ">


  @if($datos_contrato->id_sede === '1')
  Ñaña,&nbsp;&nbsp;&nbsp;{{$dia}}&nbsp;&nbsp;&nbsp;Lima,&nbsp;&nbsp;Del&nbsp;&nbsp;mes&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;{{$mes_registrado}}&nbsp;&nbsp;&nbsp;del&nbsp;&nbsp;&nbsp;{{$anhio}}

  @elseif($datos_contrato->id_sede === '2')
  Chullunquiani,&nbsp;&nbsp;&nbsp; {{$dia}}&nbsp;&nbsp;&nbsp;Juliaca,&nbsp;&nbsp;Del&nbsp;&nbsp;mes&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;{{$mes_registrado}}&nbsp;&nbsp;&nbsp;del&nbsp;&nbsp;&nbsp;{{$anhio}}

  @elseif($datos_contrato->id_sede === '3')
  Morales,&nbsp;&nbsp;&nbsp; {{$dia}}&nbsp;&nbsp;&nbsp;Tarapoto,&nbsp;&nbsp;Del&nbsp;&nbsp;mes&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;{{$mes_registrado}}&nbsp;&nbsp;&nbsp;del&nbsp;&nbsp;&nbsp;{{$anhio}}
  @endif
</div>
<br><br><br><br>
<table class="parrafo01">
  <tr>
    <td class="text-left">
      <div class="text-center">

        <label for="">_ _ _ _ _ __ _ _ _ _ __ _ _ _ _ __ _ _ _ _ _</label><br>
        <strong>Responsable financiero(DNI/Firma) </strong>
      </div>
    </td>
    <td>
      <div class="text-center">
        <label for="">_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _</label><br>
        <strong>Estudiante (DNI/Firma)</strong>
      </div>
    </td>
  </tr>
</table>

<div class="salto-pagina"></div>

<br>
<div>Indicaciones</div>
<br>
<div>
  <b>
    Documentos que deberá presentar para que la solicitud ingrese a evaluación.
  </b>
</div>
<div>
  <ul>
    <li> Documentos que acrediten la situación en la que se encuentra </li>
    <li> DNI del estudiante </li>
  </ul>
</div>
<div>
  <b>
    De no presentar los requisitos expuestos en la presente no se procederá con la evaluación.
  </b>
</div>
<div>
  <ol>
    <li>El documento debe ser llenado a mano, firmado y escaneado. </li>
    <li>La respuesta a esta solicitud será vía correo en un plazo no mayor a 72 horas </li>
    <li>Los documentos enviados deben ser actuales al año de estudio. </li>
    <li>En caso sea autosostén debe evidenciarlo (boletas de pago, recibos por honorario entre otros) </li>
    <li>De ser persona jurídica adjuntar su ficha RUC </li>
  </ol>
</div>
<div>
  <b> Correo al que debe enviar: comisionfinanciera@upeu.edu.pe </b>
</div>




@endsection