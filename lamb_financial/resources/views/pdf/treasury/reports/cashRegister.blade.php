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

</style>


<div class="text-center head-info shadow font-size-10">
        Detalle de pagos
<!-- -----------------------------------------
        @php
            var_dump($data);
        @endphp
----------------------------------------- -->
</div>
<table class="table table-sm table-bordered font-size-10">
    <thead>
    <tr>
        <th colspan="8">
            <div class="col-md-12 text-center  lamb-title-table-report">
                <p><em> Fecha Impresi&oacute;n: </em> <strong> <?php echo date('d/m/Y'); ?></strong></p>
            </div>
        </th>
        <th colspan="2">
            <div class="col-md-12 text-center lamb-title-table-report">
                VOUCHER:<span>  {{$voucher->numero}}</span><br>
                LOTE: <span>  {{$voucher->lote}}</span><br>
                {{$voucher->fecha}}
            </div>
        </th>
    </tr>
    </thead>
  <thead>
    <tr class="font-weight-bold">
      <th>Nro</th>
      <th class="text-center">Serie y número</th>
      <th class="text-center">Glosa</th>
      <th>Núm. Venta</th>
      <th class="text-center">Importe</th>
      <th class="text-center">Cliente</th>
      <th class="text-center">Cajero</th>
      <th class="text-center">Medio pago</th>
      <th class="text-right">Fecha</th>
      <th class="text-right">Hora</th>

    </tr>
  </thead>
  <tbody>
    <?php $i=1; $total = 0?>
  	 @foreach($data as $item)
  	<tr>
  		<td>{{$i++}}</td>
  		<td>{{$item->serie}} - {{$item->numero}}</td>
      <td>{{$item->glosa}}</td>
      <td>{{$item->numero_venta}}</td>
  		<td class="text-right">{{$item->importe}}</td>
  		<td>{{$item->cliente}}</td>
  		<td>{{$item->email}}</td>
  		<td>{{$item->medio_pago}}</td>
  		<td>{{$item->fecha}}</td>
  		<td>{{$item->hora}} </td>
        <?php $total = $total + $item->imp_pdf?>
  	</tr>
  	@endforeach
    <tr>
        <td colspan="4" class="text-right"> <strong> Total </strong></td>
        <td class="text-right">{{number_format($total, 2)}}</td>
        <td colspan="5"></td>

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
    </tr>
  </thead>
  <tbody>
  	@foreach($summary as $item)
  	<tr>
  		<td class="text-right">{{$item->cajero}}</td>
  		<td class="text-right">{{number_format(str_replace(',','',$item->importe ? $item->importe: '0' ), 2, '.', ',') }}</td>
  	</tr>
  	@endforeach
  	
  </tbody>

</table>



@endsection