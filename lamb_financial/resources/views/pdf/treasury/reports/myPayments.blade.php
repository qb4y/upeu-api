@extends('layouts.pdf')
@section('content')
<style type="text/css" media="screen">

.font-size-10 {
  font-size: 8px !important;
}
.font-size-7 {
  font-size: 7px !important;
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


</style>
<div class="head-info shadow font-size-10">
<div class="row">
    <table class="tablacab">
        <tr>
            <td class="text-center">Detalle de pagos</td>
            <td class="text-right" style="width: 80px;">
                <b>VOUCHER &nbsp;&nbsp; <span> {{$numero}}</span>&nbsp;&nbsp;</b><br>
                <b>LOTE&nbsp;&nbsp;  <span>  {{ $lote }}</span>&nbsp;&nbsp;</b><br>
                <b>{{$fecha}}&nbsp;&nbsp;</b>
            </td>
        </tr>
    </table>
    
  </div>
</div>
<table class="table table-sm table-bordered font-size-10">
  <thead>
    <tr class="font-weight-bold">
      <th>Nro</th>
      <th class="text-center">Cuenta bancaria</th>
      <th class="text-center">Cheque</th>
      <th class="text-center">Cuenta</th>
      <th class="text-center">Nivel</th>
      <th class="text-center">Fecha</th>
      <th class="text-center">Detalle</th>
      <th class="text-right">Importe</th>
      <th class="text-right">Importe Me</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $i=1;
    $j=1;
    $id_ctabancarianumero='';

    $total=0;
    $total_me=0;
    $subtotales=0;
    $subtotales_me=0;
    $id_ctabancaria='';
    ?>
  	 @foreach($payments as $item)
       
        @if($id_ctabancarianumero!=$item->id_ctabancaria.$item->numero)
            @if($i>1)
                <tr>
                    <td colspan="7" class="text-right">Sub Total:</td>
                    <td class="text-right">{{number_format($subtotales, 2, '.', ',') }}</td>
                    <td class="text-right">{{number_format($subtotales_me, 2, '.', ',')}}</td>
                </tr>
                <?php
                $subtotales=0;
                $subtotales_me=0;
                ?>
            @endif
        @endif
            @if($id_ctabancaria!=$item->id_ctabancaria)
            <tr>
                    <td>{{$j}}</td>
                    <td class="font-size-7">{{$item->cuenta_bancaria}}</td>
                    <td>{{$item->numero}}</td>
                    <td>{{$item->cuenta}}</td>
                    <td>{{$item->nivel}}</td>
                    <td>{{$item->fecha}}</td>
                    <td class="font-size-7">{{$item->detalle}}</td>
                    <td class="text-right">{{number_format(str_replace(',','',$item->importe_pdf ? $item->importe_pdf: '0' ), 2, '.', ',') }}</td>
                    <td class="text-right">{{number_format(str_replace(',','',$item->importe_me_pdf ? $item->importe_me_pdf: '0' ), 2, '.', ',')}}</td>
            </tr>
            <?php
            $j++;
            ?>
            @else
                @if($id_ctabancarianumero!=$item->id_ctabancaria.$item->numero)
                <tr>
                        <td></td>
                        <td class="font-size-7"></td>
                        <td>{{$item->numero}}</td>
                        <td>{{$item->cuenta}}</td>
                        <td>{{$item->nivel}}</td>
                        <td>{{$item->fecha}}</td>
                        <td class="font-size-7">{{$item->detalle}}</td>
                        <td class="text-right">{{number_format(str_replace(',','',$item->importe_pdf ? $item->importe_pdf: '0' ), 2, '.', ',') }}</td>
                        <td class="text-right">{{number_format(str_replace(',','',$item->importe_me_pdf ? $item->importe_me_pdf: '0' ), 2, '.', ',')}}</td>
                </tr>
                @else
                <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{$item->cuenta}}</td>
                        <td>{{$item->nivel}}</td>
                        <td>{{$item->fecha}}</td>
                        <td class="font-size-7">{{$item->detalle}}</td>
                        <td class="text-right">{{number_format(str_replace(',','',$item->importe_pdf ? $item->importe_pdf: '0' ), 2, '.', ',') }}</td>
                        <td class="text-right">{{number_format(str_replace(',','',$item->importe_me_pdf ? $item->importe_me_pdf: '0' ), 2, '.', ',')}}</td>
                </tr>
                @endif
            @endif
        <?php
            $id_ctabancarianumero = $item->id_ctabancaria.$item->numero;
            
            $id_ctabancaria=$item->id_ctabancaria;

            $total=$total + $item->importe_pdf;
            $total_me=$total_me + $item->importe_me_pdf ;
            $subtotales=$subtotales + $item->importe_pdf;
            $subtotales_me=$subtotales_me + $item->importe_me_pdf ;
            $i++;
        ?>
  	@endforeach
        @if($i>1)
            <tr>
                <td colspan="7" class="text-right">Sub Total:</td>
  		<td class="text-right">{{number_format($subtotales, 2, '.', ',') }}</td>
  		<td class="text-right">{{number_format($subtotales_me, 2, '.', ',')}}</td>
            </tr>
        @endif
        <tr>
                <td colspan="7" class="text-right">Total General:</td>
  		<td class="text-right">{{number_format($total, 2, '.', ',') }}</td>
  		<td class="text-right">{{number_format($total_me, 2, '.', ',')}}</td>
  	</tr>
  </tbody>
</table>

<div class="text-center head-info font-size-10">
        RESUMEN POR CAJERO
</div>

<table class="table table-sm table-bordered font-size-10">
  <thead>
    <tr class="font-weight-bold">
      <th class="text-right">Cajero</th>
      <th class="text-right">Importe</th>
      <th class="text-right">Importe Me</th>
    </tr>
  </thead>
  <tbody>
      <?php
      $t=0;
      $te=0;
      ?>
  	@foreach($subtotal as $item)
  	<tr>
  		<td class="text-right">{{$item->cajero}}</td>
  		<td class="text-right">{{number_format(str_replace(',','',$item->importe ? $item->importe: '0' ), 2, '.', ',') }}</td>
      <td class="text-right">{{number_format(str_replace(',','',$item->importe_me ? $item->importe_me: '0' ), 2, '.', ',')}}</td>
  	</tr>
        <?php
      $t=$t + $item->importe_pdf;
      $te=$te + $item->importe_me_pdf;
      ?>
  	@endforeach
  	<tr>
  		<td></td>
  		<td class="text-right"><strong>{{number_format($t, 2, '.', ',') }}</strong></td>
      <td class="text-right"><strong>{{number_format($te, 2, '.', ',')}}</strong></td>
  	</tr>
  </tbody>

</table>


<div class="text-center head-info font-size-10">
        DETRACCIONES
</div>

<table class="table table-sm table-bordered font-size-10">
  <thead>
    <tr class="font-weight-bold">
      <th>Nro</th>
      <th class="text-center">Cuenta bancaria</th>
      <th class="text-center">Nro operacion</th>
      <th class="text-center">Cuenta</th>
      <th class="text-center">Nivel</th>
      <th class="text-center">Fecha</th>
      <th class="text-center">Detalle</th>
      <th class="text-right">Importe</th>
      <th class="text-right">Importe Me</th>

    </tr>
  </thead>
  <tbody>
    <?php $d=1; ?>
  	@foreach($detractions as $item)
  	<tr>
  		<td>{{$d++}}</td>
  		<td>{{$item->cuenta_bancaria}}</td>
  		<td>{{$item->nro_operacion}}</td>
        <td>{{$item->cuenta}}</td>
        <td>{{$item->nivel}}</td>
        <td>{{$item->fecha_emision}}</td>
        <td>{{$item->detalle_ref}}</td>
        <td class="text-right">{{number_format(str_replace(',','',$item->importe ? $item->importe: '0' ), 2, '.', ',') }}</td>
        <td class="text-right">{{number_format(str_replace(',','',$item->importe_me ? $item->importe_me: '0' ), 2, '.', ',')}}</td>
  	</tr>
  	@endforeach
  </tbody>

</table>

<div class="text-center head-info font-size-10">
        RETENCIONES
</div>

<table class="table table-sm table-bordered font-size-10">
  <thead style="font-size: 12px">
    <tr class="font-weight-bold">
      <th>Nro</th>
      <th class="text-center">Cuenta bancaria</th>
      <th class="text-center">Nro retencion</th>
      <th class="text-center">Cuenta</th>
      <th class="text-center">Nivel</th>
      <th class="text-center">Fecha</th>
      <th class="text-center">Detalle</th>
      <th class="text-right">Importe</th>
      <th class="text-right">Importe Me</th>

    </tr>
  </thead>
  <tbody>
    <?php $r=1; ?>
  	@foreach($retentions as $item)
  	<tr>
  		<td>{{$r++}}</td>
  		<td>{{$item->cuenta_bancaria}}</td>
  		<td>{{$item->nro_retencion_ref}}</td>
        <td>{{$item->cuenta}}</td>
        <td>{{$item->nivel}}</td>
        <td>{{$item->fecha_emision}}</td>
        <td>{{$item->detalle_ref}}</td>
        <td class="text-right">{{number_format(str_replace(',','',$item->importe ? $item->importe: '0' ), 2, '.', ',') }}</td>
        <td class="text-right">{{number_format(str_replace(',','',$item->importe_me ? $item->importe_me: '0' ), 2, '.', ',')}}</td>
  	</tr>
  	@endforeach
  </tbody>

</table>

@endsection
