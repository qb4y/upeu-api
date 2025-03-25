@extends('layouts.pdf')
@section('content')

<style type="text/css" media="screen">
.lamb-table-compras {
        thead {
            text-transform: uppercase;
        }
    }

    .dec {
        color: #f00;
        text-align: right;
    }
    .inc {
        color: blue;
        text-align: right;
    }

    .lamb-card {
        top: .4rem;
    }

    .lamb-icon-expand {
        color: #7f264a;
        cursor: pointer;
    }
    h5 {
            color: #7f264a;
            margin-bottom: 0px;
        }
        p {
            margin-bottom: 0px;
        }

    .table > thead > tr > th {
        vertical-align: middle;
   }

   .lamb-table-responsive {
       max-height: 400px;
   }

   .lamb-table-th-fecha-emision {
        min-width: 100px;
    }
   .lamb-table-th-gravadas {
       min-width: 300px;
   }
   .lamb-table-th-grabadas-no-grabadas {
        min-width: 300px;
    }
    .lamb-table-th-no-grabadas {
        min-width: 300px;
    }
    .lamb-table-th-apellidos-nombres {
        min-width: 250px;
    }
    .lamb-table-th-nro-comprobante-pago {
        min-width: 200px;
    }
</style>

<div class="card lamb-card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 text-center ">
                <h5 style="font-size: 1.2rem"> Estado de Cuenta </h5>
                <span style="font-style: 0.2rem !important;"> <em>(Expresado en soles)</em></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-left">
                <div class="container">
                    <table style="font-size: 0.87rem !import;">
                        <tr>
                            <td> <strong> Periodo </strong></td>
                            <td>: {{ $periodo }}</td>
                        </tr>
                        <tr>
                            <td> <strong> Ruc </strong> </td>
                            <td>: {{$ruc }}</td>
                        </tr>
                        <tr>
                            <td> <strong>Razón Social </strong></td>
                            <td>: {{ $razon_social }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <div class="container">
                    <span></span>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="table-responsive lamb-table-responsive" >
    <table class="table table-sm table-bordered table-hover lamb-table-compras">
        <thead class="text-center">
            <tr>
                <th>Nro</th>
                <th>Entidad/Depto</th>
                <th>Mes</th>
                <th>Correlativo</th>
                <th>Lote</th>
                <th>Operacion</th>
                <th>Tipo</th>
                <th>Serie</th>
                <th>Num Doc</th>
                <th>Fecha Doc</th>
                <th>Fecha Prov</th>
                <th>Importe</th>
            </tr>
        </thead>
        <tbody>
            {{$i=0}}
            @foreach($items as $dt)
            <tr>
                <td class="text-center">{{++$i}}</td>
                <td class="text-center">{{ $dt->id_entidad }}/{{ $dt->id_depto }}</td>
                <td class="text-center">{{ $dt->mes_nombre }}</td>
                <td class="text-left">
                    Vo {{ $dt->numero_voucher }} {{$dt->id_tipoorigen==3 ? ' - ' : ''}}
                    {{$dt->id_tipoorigen==3 ? $dt->correlativo : '' }}</td>
                <td>{{ $dt->lote }}</td>
                <td>{{ $dt->oper }}</td>
                <td class="text-center">{{ $dt->nombre_corto }}</td>
                <td class="text-right">{{ $dt->serie }}</td>
                <td class="text-right">{{ $dt->numero }}</td>
                <td class="text-center">{{ $dt->fecha_doc }}</td>
                <td class="text-center">
                    @if ($dt->id_tipoorigen === '8' or $dt->id_tipoorigen === '12')
                     {{ $dt->fecha_lote }} 
                    @else
                    <strong >{{ $dt->fecha_provision }}</strong>
                    @endif
                </td>
                <td class="text-right">{{  number_format($dt->importe, 2, '.', ',') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="11" class="text-right">TOTAL</td>
                <td class="text-right"> {{  number_format($items_sum, 2, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>
</div>

@endsection