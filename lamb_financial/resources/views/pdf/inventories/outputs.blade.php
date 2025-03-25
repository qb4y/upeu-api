@extends('layouts.pdf')
@section('content')
<div class="table-responsive lamb-table-responsive" >
    <table class="table table-striped table-sm table-bordered" id="tblData" >
        
        <thead class="text-center">
            <tr>
            <th colspan="9">
                <div style="font-size: 14px">
                    <h5>
                        @if ($tipo === 'S')
                        <strong> SALIDAS DIVERSAS</strong>
                       @else
                       <strong >INGRESOS DIVERSOS</strong>
                       @endif
                    </h5>
                </div>
            </th>
            <th   colspan="1">
                <div style="font-size: 8px">
                    <p class="m-0">VOUCHER &nbsp;&nbsp; <span>  {{ $numero }}</span>&nbsp;&nbsp;</p>
                    <p class="m-0">LOTE&nbsp;&nbsp;<span>  {{ $lote or '-' }}</span>&nbsp;&nbsp;</p>
                    <p class="m-0">{{ $fecha }}&nbsp;&nbsp;</p>
                </div>
            </th>
            </tr>
    </thead>
        <thead>
            <tr>
                <th>#</th>
                <th>Guia</th>
                <th>Serie</th>
                <th>Número</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Costo</th>
                <th>Importe</th>
                <th class="text-center"># Voucher</th>
            </tr>
        </thead>
        <tbody >
            {{$i=0}}
            @foreach($items as $dt)
            <tr>
                <td>{{++$i}}</td>
                <td> <em class="lamb-tag">{{$dt->guia}}</em></td>
                <td> <em class="lamb-tag">{{$dt->serie}}</em></td>
                <td>{{$dt->numero}}</td>
                <td>{{$dt->nombre}}</td>
                <td>{{$dt->fecha}}</td>
                <td class="text-right">{{$dt->cantidad }}</td>
                <td class="text-right">{{  number_format( $dt->costo, 2, '.', ',') }}</td>
                <td class="text-right">{{  number_format( $dt->importe, 2, '.', ',') }}</td>
                <td class="text-center lamb-tag"> <em>{{$dt->voucher}}</em></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot >
          <tr >
            <td colspan="6" class="text-right"> TOTAL</td>
            <td  class="text-right" style="font-size: 0.8rem"> {{ $total->cantidad }}</td>
            <td></td>
            <td  class="text-right" style="font-size: 0.8rem"> {{  number_format( $total->importe, 2, '.', ',')}}</td>
            <td > </td>
          </tr>
        </tfoot>
        </table>
</div>

@endsection