<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>UPN - Registro de Compras</title>
    <link rel="stylesheet" href="css/purchases_shopping_record.css" media="all" />
  </head>
  <body>
    <header class="clearfix">
      <div id="project">
        <div><span>{{$data['datos']['empresa']}}</span></div>
        <div><span>REGISTRO DE COMPRAS</span></div>
        <div><span>{{$data['datos']['ruc']}}</span></div>
        <div><span>{{$data['datos']['periodo']}}</span></div>
      </div>
    </header>
    <main>
      <table>
        <thead class="bordes_cabecera">
          <tr>
                <th rowspan="3">Correlativo</th>
                <th rowspan="3">CUO</th>
                <th rowspan="3">Usuario</th>
                <th rowspan="3">Fecha <br/>de <br/>emisión</th>
                <th rowspan="3">Fecha <br/>de <br/>venci-<br/>miento</th>
                <th colspan="3" rowspan="2">Comprobante de <br/>pago o documento</th>
                <th rowspan="3">N° del <br/>comprobante</th>
                <th colspan="3">Información del proveedor</th>
                <th class="lamb-table-th-gravadas" colspan="2" rowspan="2">
                    Adquisiciones<br/>
                    gravadas<br/>
                    destinadas a<br/>
                    operaciones <br/>
                    gravadas y/o <br/>
                    de exportación
                </th>
                <th class="lamb-table-th-grabadas-no-grabadas" colspan="2" rowspan="2">
                    Adquisiciones <br/>
                    gravadas <br/>
                    destinadas a <br/>
                    operaciones <br/>
                    gravadas y/o <br/>
                    de exportación<br/>
                    y a operaciones <br/>
                    no gravadas</th>
                <th class="lamb-table-th-no-grabadas" colspan="2" rowspan="2">
                    Adquisiciones<br/>
                    gravadas<br/>
                    destinadas a <br/>
                    operaciones<br/>
                    no gravadas</th>
                <th rowspan="3">
                    Valor<br/>
                    de las<br/>
                    adquisi-<br/>
                    ciones<br/>
                    no <br/>
                    gravadas</th>
                <th rowspan="3">ISC</th>
                <th rowspan="3">Otros<br/>tributos<br/>y cargos</th>
                <th rowspan="3">Importe<br/>total</th>
                <th rowspan="3">
                    N° de<br/>
                    compro-<br/>
                    bante<br/>
                    de pago<br/>
                    emitido </th>
                <th rowspan="2" colspan="2">
                    Constancia <br/>
                    de depósito de<br/>
                    detracción</th>
                <th rowspan="3">Tipo de <br/>cambio</th>
                <th rowspan="2" colspan="4">
                    Referencia del<br/>
                    comprobante de <br/>
                    pago o documento <br/>
                    original que se modifica</th>
                <th rowspan="3">RET</th>
            </tr>
            <tr>
                <th colspan="2">Documento</th>
                <th class="lamb-table-th-apellidos-nombres" rowspan="2">Apellidos y Nombres <br/>o razón social
                </th>
            </tr>
            <tr>
                <th>Tipo</th>
                <th>Serie</th>
                <th>Año</th>
                <!-- <th>Número</th> -->

                <!-- Información del proveedor -->
                <th>Tipo </th>
                <th>Número</th>

                <th>B. Imp.</th>
                <th>IGV</th>

                <th>B. Imp.</th>
                <th>IGV</th>

                <th>B. Imp.</th>
                <th>IGV</th>

                <th>Nro</th>
                <th>Fecha</th>

                <th>Fecha</th>
                <th>Tipo</th>
                <th>Serie</th>
                <th>Número</th>
            </tr>          
        </thead>
        <tbody>
        @foreach($data['items'] as $item)
        <tr>
                <td>{{ $item->entidad }}-{{ $item->id_depto }}-{{ $item->lote_numero }}-{{ $item->correlativo }}</td>
                <td>{{ $item->cuo }}</td>
                <td>{{ $item->username }}</td>
                <td>{{ $item->fecha_emision }}</td>
                <td>{{ $item->fecha_vto }}</td> 
                <td>{{ $item->comp_pago_tipo }}</td>
                <td>{{ $item->comp_pago_serie }}</td>
                <td>{{ $item->comp_pago_anho_emision }}</td>
                <td>{{ $item->comp_pago_nro }}</td>
                <td>{{ $item->infor_proveedor_tipo }}</td>
                <td>{{ $item->infor_proveedor_numero }}</td>
                <td class="izquierda">{{ $item->infor_proveedor_razon_social }}</td>

                <td class="text-right"> {{ number_format($item->compra_gravada_bi,2) }}</td>
                <td class="text-right"> {{ number_format($item->compra_gravada_igv,2) }}</td>

                <td class="text-right"> {{ number_format($item->exportacion_bi,2) }}</td>
                <td class="text-right"> {{ number_format($item->exportacion_igv,2) }}</td>

                <td class="text-right"> {{ number_format($item->sincredito_bi,2) }}</td>
                <td class="text-right"> {{ number_format($item->sincredito_igv,2) }}</td>

                <td class="text-right"> {{ number_format($item->compras_no_grabadas,2) }}</td>

                <td class="text-right"> {{ number_format($item->isc,2)  }}</td>
                <td class="text-right"> {{ number_format($item->otros_tributos,2)  }}</td>
                <td class="text-right"> {{ number_format($item->importe_total,2) }}</td>
                <td>{{ $item->comprob_emit_sujet_no_domi  }}</td>
                <td>{{ $item->const_depsi_detrac_numero  }}</td>
                <td>{{ $item->const_depsi_detrac_fecha  }}</td>

                <td>{{ $item->tc  }}</td>
                <td>{{ $item->ref_comp_pago_doc_fecha  }}</td>
                <td>{{ $item->ref_comp_pago_doc_tipo  }}</td>
                <td>{{ $item->ref_comp_pago_doc_serie  }}</td>
                <td>{{ $item->ref_comp_pago_doc_numero  }}</td>
                <td>{{ $item->retencion  }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        @foreach($data['totales'] as $item_total)
          <tr>
                <th class="text-right" colspan="11">TOTAL</th>
                <th class="text-right"> {{ number_format($item_total->compra_gravada_bi,2) }}</th>
                <th class="text-right"> {{ number_format($item_total->compra_gravada_igv,2) }}</th>

                <th class="text-right"> {{ number_format($item_total->exportacion_bi,2) }}</th>
                <th class="text-right"> {{ number_format($item_total->exportacion_igv,2) }}</th>

                <th class="text-right"> {{ number_format($item_total->sincredito_bi,2) }}</th>
                <th class="text-right"> {{ number_format($item_total->sincredito_igv,2) }}</th>

                <th class="text-right"> {{ number_format($item_total->compras_no_grabadas,2) }}</th>

                <th class="text-right"> {{ number_format($item_total->isc,2)  }}</th>
                <th class="text-right"> {{ number_format($item_total->otros_tributos,2)  }}</th>
                <th class="text-right"> {{ number_format($item_total->importe_total,2) }}</th>
                <th></th>
                <th></th>
                <th></th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="text-right">{{ $item_total->retencion  }}</th>
          </tr>
        @endforeach
        </tfoot>
      </table>
    </main>
    <footer>
      Impreso: {{$data['datos']['fechahora']}} - Usuario: {{$data['datos']['username']}} - LAMB UPN 2019 
    </footer>
  </body>
</html>
