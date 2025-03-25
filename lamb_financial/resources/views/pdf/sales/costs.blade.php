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
    REPORTE DE SALIDAS - ALMACENES
</div>


<div  class="table-responsive">
    <table class="table table-sm table-bordered font-size-10" id="tblData">
        <thead>
        <tr  class="text-center">
            <th>#</th>
            <th>Serie</th>
            <th>Número</th>
            <th>Articulo</th>
            <th>Cantidad</th>
            <th>Costo Unitario</th>
            <th>Costo Total</th>
            <th>Fecha</th>
        </tr>
        </thead>
        <tbody >
        <?php $i=1;?>
        @foreach($data as $item)
        <tr >
            <td>{{$i++}}</td>
            <td>{{$item->serie}}</td>
            <td> {{$item->numero}}</td>
            <td>{{$item->detalle}}</td>
            <td  class="text-right">{{$item->cantidad}}</td>
            <td  class="text-right"> {{ number_format($item->costo_unitario, 2, '.', ',') }}</td>
            <td  class="text-right"> {{ number_format($item->costo_total, 2, '.', ',') }}</td>
            <td  class="text-right"> {{ $item->fecha}}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection