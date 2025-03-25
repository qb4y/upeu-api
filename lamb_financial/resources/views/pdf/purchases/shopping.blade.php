@extends('layouts.pdf')
@section('content')

<table align="center" style="table-layout: auto; width: 100%" class="table table-sm table-striped table-bordered">
        <thead style="font-size: 12px" class="text-center">
          <tr class="font-12">
            <th colspan="20">
                <div class="col-md-12 text-center ">
                    <p><strong>REGISTRO DE COMPRAS</strong></p>
                    <p style="font-size: 0.8rem;">(Expresado en soles)</p>
                  </div>
            </th>
            <th   colspan="2">
                <div style="font-size: 8px">
                Documento
                <p>{{ $n_vou }}</p>
                lote
                <p>{{ $lote}}</p>
                <p><em>{{ $v_fecha}}</em></p>
                </div>
            </th>
          </tr>
        </thead>
        <thead style="font-size: 12px" class="text-center">
        <tr>
          <th rowspan="3">Correlativo</th>
          <th rowspan="3">CUO</th>
          <th class="lamb-table-th-fecha-emision" rowspan="3">Fecha de emisión</th>
          <th rowspan="3">Fecha de vencimiento</th>
          <th colspan="3" rowspan="2">Comprobante de pago o documento</th>
          <th rowspan="3">N° del comprobante</th>
          <th colspan="3">Información del proveedor</th>
          <th class="lamb-table-th-gravadas" colspan="2" rowspan="2">Adquisiciones gravadas destinadas a
            operaciones gravadas y/o de exportación
          </th>
          <th class="lamb-table-th-grabadas-no-grabadas" colspan="2" rowspan="2">Adquisiciones gravadas destinadas
            a operaciones gravadas y/o de exportación
            y a operaciones no gravadas
          </th>
          <th class="lamb-table-th-no-grabadas" colspan="2" rowspan="2">Adquisiciones gravadas destinadas a
            operaciones no gravadas
          </th>
          <th rowspan="3">Valor de las adquisiciones no gravadas</th>
          <th rowspan="3">ISC</th>
          <th rowspan="3">Otros tributos y cargos</th>
          <th rowspan="3">Importe total</th>
          <th rowspan="3">Login</th>

          
          {{-- <th rowspan="3">N° de comprobante de pago emitido</th>
          <th rowspan="2" colspan="2">Constancia de depósito de detracción(3)</th>
          <th rowspan="3">Tipo cambio</th>
          <th rowspan="2" colspan="4">Referencia del comprobante de pago o documento original que se modifica</th>
          <th rowspan="3">RET</th> --}}
        </tr>
        <tr>
          <th colspan="2">Documento</th>
          <th class="lamb-table-th-apellidos-nombres" rowspan="2">Apellidos y Nombres, Denominación o razón social
          </th>
        </tr>
        <tr>
          <th class="text-center text-primary" >Tipo</th>
          <th class="text-center text-primary" >Série</th>
          <th class="text-center text-primary" >Año de emisión</th>
          <th class="text-center text-primary" >Tipo</th>
          <th class="text-center text-primary" >Número</th>
    
          <th class="text-center text-primary" >Base Imponible</th>
          <th class="text-center text-primary" >IGV</th>
    
          <th class="text-center text-primary" >Base Imponible</th>
          <th class="text-center text-primary" >IGV</th>
    
          <th class="text-center text-primary" >Base Imponible</th>
          <th class="text-center text-primary" >IGV</th>
    
          {{-- <th class="text-center text-primary" >Número</th>
          <th class="text-center text-primary" >Fecha emisión</th>
    
          <th class="text-center text-primary" >Fecha</th>
          <th class="text-center text-primary" >Tipo (Tabla 10)</th>
          <th class="text-center text-primary" >Série</th>
          <th class="lamb-table-th-nro-comprobante-pago">N° del comprobante de pago</th> --}}
        </tr>
        </thead>
        <tbody style="font-size: 12px">
        @foreach($items as $deta)
        <tr >
          <td class="text-center"> {{$deta->entidad}} - {{$deta->id_depto }} - {{$deta->lote_numero }} - {{ $deta->correlativo }}</td>
          <td class="text-center" >{{ $deta->cuo }}</td>
          <td class="text-center" >{{ $deta->fecha_emision }}</td>
          <td class="text-center" >{{ $deta->fecha_vto }}</td>
          <td class="text-center" >{{ $deta->comp_pago_tipo }}</td>
          <td class="text-center" >{{ $deta->comp_pago_serie }}</td>
          <td class="text-center" >{{ $deta->comp_pago_anho_emision }}</td>
          <td class="text-center" >{{ $deta->comp_pago_nro }}</td>
          <td class="text-center" >{{ $deta->infor_proveedor_tipo }}</td>
          <td class="text-center" >{{ $deta->infor_proveedor_numero }}</td>
          <td class="text-center" >{{ $deta->infor_proveedor_razon_social }}</td>
    
          <td class="text-right"> {{ number_format($deta->compra_gravada_bi, 2, '.', ',') }} </td>
          <td class="text-right"> {{ number_format($deta->compra_gravada_igv, 2, '.', ',') }} </td>
    
          <td class="text-right"> {{ number_format($deta->exportacion_bi, 2, '.', ',') }}</td>
          <td class="text-right"> {{ number_format($deta->exportacion_igv, 2, '.', ',') }}</td>
    
          <td class="text-right"> {{ number_format($deta->sincredito_bi, 2, '.', ',') }}</td>
          <td class="text-right"> {{ number_format($deta->sincredito_igv, 2, '.', ',') }}</td>
    
          <td class="text-right"> {{ number_format($deta->compras_no_grabadas, 2, '.', ',') }}</td>
    
          <td class="text-right"> {{ $deta->isc  }}</td>
          <td class="text-right"> {{ $deta->otros_tributos  }}</td>
          <td class="text-right">  {{ number_format($deta->importe_total, 2, '.', ',') }}</td>
          <td class="text-right">  {{ $deta->username }}</td>
          {{-- <td class="text-center" >{{ $deta->comprob_emit_sujet_no_domi  }}</td>
          <td class="text-center" >{{ $deta->const_depsi_detrac_numero  }}</td>
          <td class="text-center" >{{ $deta->const_depsi_detrac_fecha  }}</td>
    
          <td class="text-center" >{{ $deta->tc  }}</td>
          <td class="text-center" >{{ $deta->ref_comp_pago_doc_fecha  }}</td>
          <td class="text-center" >{{ $deta->ref_comp_pago_doc_tipo  }}</td>
          <td class="text-center" >{{ $deta->ref_comp_pago_doc_serie  }}</td>
          <td class="text-center" >{{ $deta->ref_comp_pago_doc_numero  }}</td>
          <td class="text-center" >{{ $deta->retencion  }}</td> --}}
        </tr>
        @endforeach
        @foreach($items_sum as $deta)
        <tr >
          <th colspan="11"  class="text-right">TOTAL</th>
          <th class="text-right"> {{ number_format($deta->compra_gravada_bi, 2, '.', ',') }}</th>
          <th class="text-right"> {{ number_format($deta->compra_gravada_igv, 2, '.', ',') }} </th>
    
          <th class="text-right"> {{ number_format($deta->exportacion_bi, 2, '.', ',') }} </th>
          <th class="text-right"> {{ number_format($deta->exportacion_igv, 2, '.', ',') }} </th>
    
          <th class="text-right"> {{ number_format($deta->sincredito_bi, 2, '.', ',') }} </th>
          <th class="text-right"> {{ number_format($deta->sincredito_igv, 2, '.', ',') }} </th>
    
          <th class="text-right"> {{ number_format($deta->compras_no_grabadas, 2, '.', ',') }} </th>
    
          <th class="text-right"> {{ number_format($deta->isc, 2, '.', ',') }} </th>
          <th class="text-right"> {{ number_format($deta->otros_tributos, 2, '.', ',') }} </th>
          <th class="text-right"> {{ number_format($deta->importe_total, 2, '.', ',') }} </th>
          {{-- <th></th>
          <th></th>
          <th></th>
    
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th>{{ $deta->retencion  }}</th> --}}
        </tr>
        @endforeach
        </tbody>
      </table>

      <style>
    
          p {
              margin-top: 0;
              margin-bottom: 0rem;
          }
      
          </style>
@endsection
