@extends('layouts.xls')
@section('content')
<table>
<thead>
    <tr>
        <th class="text-center"  rowspan="2">#</th>
        <th class="text-center" colspan="5" >VENTA.</th>
        <th class="text-center" colspan="5">PEDIDO</th>
    </tr>
    <tr>
        <th></th>
        <th>Serie</th>
        <th>N° Doc</th>
        <th>Cliente</th>
        <th>Fecha</th>
        <th>Importe</th>

        <th>N° Pedido</th>
        <th>Fecha pedido</th>
        <th>Area origen</th>
    </tr>
</thead>
<tbody>
    <?php
    $total = 0;
    $i=1;
    ?>
    @foreach($data as $k)
    <tr>
        <td>{{$i}}</td>
        <td>{{$k->serie}}</td>
        <td>{{$k->numero_venta}}</td>
        <td>{{$k->cliente}}</td>
        <td>{{$k->fecha_venta}}</td>
        <td class="text-right">{{$k->total}}</td>

        <td>{{$k->numero}}</td>
        <td>{{$k->fecha_pedido}}</td>
        <td>{{$k->nombre_areaorigen}}</td>
       
    </tr>
    <?php
    $i++;
    $total = $total + $k->importe;
    ?>
    @endforeach
    <tr>
        <td colspan="5" class="text-center">Total</td>
        <td class="text-right">{{$total}}</td>
    </tr>
</tbody>
</table>
@endsection

