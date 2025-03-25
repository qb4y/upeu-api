@extends('layouts.pdf')
@section('content')

    <style type="text/css" media="screen">
        .head-info {
            background-color: #7f264a;
            color: white;
            padding: 4px;
            text-transform: uppercase;
            font-weight: 600;
            font-size: .80rem;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }
        .font-size-10 {
            font-size: 10px !important;
        }
    </style>


    <div class="text-center head-info shadow">
        Detalle depositos
    </div>

    <table class="table table-striped table-bordered table-sm font-size-10">
        <thead>
        <tr>
            <th colspan="6">
                <div class="col-md-12 text-center  lamb-title-table-report">
                    
                    <div style="padding: 4px">
                        <span>N&uacute;mero voucher:&nbsp;{{$cierre->numero}}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span>Fecha: {{$cierre->fecha}}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span>Saldo total: {{number_format($cierre->importe, 2)}}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span>Saldo a depositar: {{number_format($cierre->importe - $cierre->imp_bank, 2)}}</span>
                    </div>
                </div>
            </th>
            {{--<th colspan="2">
                <div class="col-md-12 text-center lamb-title-table-report">
                    VOUCHER:<span>  {{$voucher->numero}}</span><br>
                    LOTE: <span>  {{$voucher->lote}}</span><br>
                    {{$voucher->fecha}}
                </div>
            </th>--}}
        </tr>
        </thead>
        <thead>
        <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Operaci&oacute;n</th>
            <th>Glosa</th>
            <th>Importe</th>
            <th>Importe M.E.</th>
        </tr>
        </thead>
        <tbody>
        @foreach($closebox as $key=>$item)
            <tr>
                <td>{{$key + 1}} </td>
                <td> {{ $item->fecha}} </td>
                <td> {{ $item->operacion}}</td>
                <td> {{ $item->glosa}}</td>
                <th class="text-right"> {{ number_format($item->importe, 2)  }} </th>
                <th class="text-right"> {{ number_format($item->importe_me, 2) }} </th>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        {{--        @foreach($total as $key=>$item)--}}
        <tr>
            <th colspan="4" class="text-right">TOTAL</th>
            <th class="text-right"><strong>{{ number_format($total->importe, 2) }}</strong></th>
            <th class="text-right"><strong>{{ number_format($total->importe_me, 2)  }}</strong></th>
        </tr>
        {{--        @endforeach--}}
        </tfoot>
    </table>

@endsection