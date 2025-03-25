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

.lamb-td {
  vertical-align: inherit;
}

.text-center {
  text-align: center !important;
}

.page-break {
    page-break-after: always;
}

</style>


<div class="text-center head-info shadow font-size-10">
  Resumen por Cajero <br>
  <span> Caja UPeU | Arqueo de caja </span>
</div>
<table class="table table-sm table-bordered font-size-10">
    <thead>
    <tr>
        <th colspan="9">
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
      <th width="5" size="5px">Nro</th>
      <th class="text-center">Dep.</th>
      <th width="5" size="5px" class="text-center">Cod Comercio</th>
      <th width="5" size="5px" class="text-center">Fecha</th>
      <th class="text-center">Glosa</th>
      <th width="5" size="5px" class="text-center">Carnet</th>
      <th width="5" size="5px" class="text-center">Imp.</th>
      <th width="5" size="5px" class="text-center">Nombre</th>
      <th width="5" size="5px" width="10" class="text-center">Referencia / voucher/ N°Pedido</th>
      <th width="5" size="5px" class="text-right">Cuenta</th>
      <th width="5" size="5px" class="text-right">Hora</th>

    </tr>
  </thead>
  <tbody>
    <?php 
    // $i=1;
    $j=1;
    $total = 0;
    $id_deposito = '';
    $deposito='';

    ?>
     @foreach($data as $item)
     @if($id_deposito!=$item->id_deposito)
      <tr>
        <td style="vertical-align: inherit;">{{$j}}</td>
        <td style="vertical-align: inherit; width: 10px !important;">{{$item->deposito}}</td>
        <td style="vertical-align: inherit;">{{$item->cod_comercio}}</td>
        <td style="vertical-align: inherit;">{{$item->fecha}}</td>
        <td style="vertical-align: inherit;">{{$item->glosa}}</td>
        <td style="vertical-align: inherit;" class="text-right">{{$item->codigo}}</td>
        <td style="vertical-align: inherit;" class="text-right">{{$item->importe}}</td>
        <td style="vertical-align: inherit;">{{$item->nombre}}</td>
        <td style="vertical-align: inherit; width: 10px !important;" class="text-center">{{$item->nro_operacion}}</td>
        <td style="vertical-align: inherit;">{{$item->cuenta}}</td>
        <td style="vertical-align: inherit;"><code> {{$item->hora}} </code> </td>
      </tr>
      <?php $total = $total + $item->imp_pdf; ?>

      <?php
        $j++;
       ?>
     @else
      @if($deposito!=$item->id_deposito.$item->deposito)
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td style="vertical-align: inherit;">{{$item->fecha}}</td>
          <td style="vertical-align: inherit;">{{$item->glosa}}</td>
          <td style="vertical-align: inherit;" class="text-right">{{$item->codigo}}</td>
          <td style="vertical-align: inherit;" class="text-right">{{$item->importe}}</td>
          <td style="vertical-align: inherit;">{{$item->nombre}}</td>
          <td style="vertical-align: inherit;">{{$item->nro_operacion}}</td>
          <td style="vertical-align: inherit;">{{$item->cuenta}}</td>
          <td style="vertical-align: inherit;"><code> {{$item->hora}} </code> </td>
        </tr>
      @else
        <tr>
          <td colspan="9"></td>
          <td style="vertical-align: inherit;">{{$item->cuenta}}</td>
          <td></td>
        </tr>
      @endif
     @endif
     <?php
      $id_deposito=$item->id_deposito;
      $deposito = $item->id_deposito.$item->deposito;
      // $total = $total + $item->imp_pdf; 
      // $i++;
     ?>
    @endforeach
    

    <tr>
        <td colspan="6" class="text-right"> <strong> Total </strong></td>
        <td class="text-right"> <strong> {{number_format($total, 2)}} </strong></td>
        {{-- <td class="text-right"> <strong>150 </strong></td> --}}
        <td colspan="5"></td>
    </tr>
    <tr>
      <td colspan="3">Cajero:</td> 
      <td colspan="8" class="text-left"><strong>{{$cajero}}</strong></td>
    </tr>
    <tr>
      <td colspan="3">Valido</td>
      <td colspan="8"></td>
    </tr>
  </tbody>
</table>

@endsection