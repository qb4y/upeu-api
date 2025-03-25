@extends('layouts.pdf')
@section('content')




<table align="center" class="table table-sm table-striped table-bordered">
    <thead>
    <tr>
        <th colspan="18">
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
        <thead style="font-size: 8px" class="text-center">
            <tr>
                <th rowspan="3">#</th>
                <th rowspan="3"> Fecha</th>
                <th rowspan="3">Lote. CTR</th>
                <th colspan="3">Comprobante de pago</th>
                <th colspan="3">Información del cliente</th>
                <th rowspan="3">Base imponible
                    <br> de la operación
                    <br> Gravada</th>
                <th rowspan="2" colspan="2">Importe de la operación
                    <br> exonerada ó inafecta</th>
                <th rowspan="3">IGV
                    <br> Y/O
                    <br> IPM</th>
                <th rowspan="3">Descuento</th>
                <th rowspan="3">
                    <br>
                    <br> ICBPER</th>
                <th rowspan="3">Importe Total del
                    <br> comprobante de pago</th>
                <th colspan="4">Comprobante que modifica</th>
            </tr>
            <tr>
                <th rowspan="2">Tipo </th>
                <th rowspan="2">Serie</th>
                <th rowspan="2">Número</th>
                <th colspan="2">Documento</th>
                <th rowspan="2">Apellidos y Nombres
                    <br> Denominación o Razón
                    <br> social</th>
                <th rowspan="2">Fecha</th>
                <th rowspan="2">Tipo</th>
                <th rowspan="2">Serie</th>
                <th rowspan="2">Número</th>
            </tr>
            <tr>
                <th>Tipo</th>
                <th>Número</th>
                <th>Exonerada</th>
                <th>Inafecta</th>
            </tr>
        </thead>
        <tbody style="font-size: 8px">
            <?php
            $i=0;
            $tBg = 0;
            $tExo = 0;
            $tIna = 0;
            $tIpm = 0;
            $tIcbper = 0;
            $tImpT = 0;
            $tDesc = 0;
            ?>
            @foreach($data as $deta)
            <tr>
                <td>{{ $i +1 }}</td>
                <td>{{ $deta->fecha }}</td>
                <td>{{ $deta->lote }}</td>
                <td>{{ $deta->id_comprobante }}</td>
                <td>{{ $deta->serie }}</td>
                <td>{{ $deta->numero }}</td>
                <td>{{ $deta->id_tipodocumento }}</td>
                <td>{{ $deta->documento }}</td>
    
                <td>{{ $deta->cliente }}</td>
                <td class="text-right"> {{ number_format($deta->gravada, 2, '.', ',') }} </td>
                <td class="text-right"> {{ number_format($deta->exonerada, 2, '.', ',') }}</td>
                <td class="text-right"> {{ number_format($deta->inafecta, 2, '.', ',') }}</td>
                <td class="text-right"> {{ number_format($deta->igv, 2, '.', ',') }}</td>
                <td class="text-right"> {{ number_format($deta->descuento, 2, '.', ',') }}</td>
                <td class="text-right"> {{ number_format($deta->icbper, 2, '.', ',') }}</td>
                <td class="text-right"> {{ number_format($deta->total, 2, '.', ',') }}</td>
                <td>{{  $deta->fecha_v }}</td>
                <td>{{  $deta->tipo_v }}</td>
                <td>{{  $deta->serie_v }}</td>
                <td>{{  $deta->numero_v }}</td>
            </tr>
            <?php
            $tBg = $tBg + $deta->gravada;
            $tExo = $tExo + $deta->exonerada;
            $tIna = $tIna + $deta->inafecta;
            $tIpm = $tIpm + $deta->igv;
            $tIcbper = $tIcbper + $deta->icbper;
            $tImpT = $tImpT + $deta->total;
            $tDesc = $tDesc + $deta->descuento;
            $i++;
            ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"> <strong>{{ number_format($tBg, 2, '.', ',') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($tExo, 2, '.', ',') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($tIna, 2, '.', ',') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($tIpm, 2, '.', ',') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($tDesc, 2, '.', ',') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($tIcbper, 2, '.', ',') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($tImpT, 2, '.', ',') }}</strong></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

@endsection