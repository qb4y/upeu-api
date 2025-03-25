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
        .table-signature {
            width: 100%;
            font-size: 10px !important;
        }

        .table-signature td {
            text-align: center;
        }
    </style>


    <div class="text-center head-info shadow">
        Resumen de arqueo voucher
    </div>

    <table class="table table-striped table-bordered table-sm font-size-10">
        <thead>
        <tr>
            <th colspan="6">
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
        <tr>
            <th>#</th>
            <th>Cuenta</th>
            <th>Nivel</th>
            <th>Nombre de cuenta</th>
            <th>D&eacute;bito</th>
            <th>Cr&eacute;dito</th>
            <th>ME. D&eacute;bito</th>
            <th>ME. Cr&eacute;dito</th>
        </tr>
        </thead>
        <tbody>
        @foreach($deposits as $key=>$item)
            <tr>
                <td>{{$key + 1}} </td>
                <td> {{ $item->cuenta}} </td>
                <td> {{ $item->depto}} - {{ $item->depto_n}} </td>
                <td> {{ $item->cuenta_n}}</td>
                <th class="text-right"> {{ number_format($item->debito, 2)  }} </th>
                <th class="text-right"> {{ number_format($item->credito, 2) }} </th>
                <th class="text-right"> {{ number_format(!empty($item->debito_me) ? $item->debito_me : 0, 2) }} </th>
                <th class="text-right"> {{ number_format(!empty($item->credito_me) ? $item->credito_me : 0, 2) }} </th>
            </tr>
        @endforeach
        </tbody>
        <tfoot *ngFor="let to of total">
        @foreach($total as $key=>$item)
            <tr>
                <th colspan="4" class="text-right">TOTAL</th>
                <th class="text-right"><strong>{{ number_format($item->debito, 2) }}</strong></th>
                <th class="text-right"><strong>{{ number_format($item->credito, 2)  }}</strong></th>
                <th class="text-right"><strong>{{ number_format(!empty($item->debito_me) ? $item->debito_me : 0, 2)  }}</strong></th>
                <th class="text-right"><strong>{{ number_format(!empty($item->credito_me) ? $item->credito_me : 0, 2)  }}</strong></th>
            </tr>
        @endforeach
        </tfoot>
    </table>
    <br>
    <br>
    <br>
    <table class="table-signature">
        <tr>
            <td>_______________________<br>
                Vo.Bo. Jefe de caja
            </td>
            @if ($depto === '1' )
            <td>_______________________________________________<br>
                Vo.Bo. Director General Financiero Contable
            </td>
            @else
            <td>_______________________________________________<br>
                Vo.Bo. Tesoreria
            </td>
            @endif
        </tr>
    </table>
@endsection