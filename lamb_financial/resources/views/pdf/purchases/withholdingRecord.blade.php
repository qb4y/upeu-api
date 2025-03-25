<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>UPN - Registro de Retenciones</title>
    <link rel="stylesheet" href="css/purchases_shopping_record.css" media="all" />
  </head>
  <body>
    <header class="clearfix">
    <div id="project">
        <div><span>{{$data['datos']['empresa']}}</span></div>
        <div><span>REGISTRO DE RETENCIONES</span></div>
        <div><span>{{$data['datos']['ruc']}}</span></div>
        <div><span>{{$data['datos']['periodo']}}</span></div>
      </div>
    </header>
    <main>
      <table>
        <thead>
          <tr>
            <th class="w_20">#</th>
            <th class="w_50">Usuario</th>
            <th class="w_50">Entidad/Depto</th>
            <th class="w_50">Voucher</th>
            <th class="w_50">Lote</th>
            <th class="w_50">Ruc</th>
            <th class="w_300">Razón Social</th>
            <th class="w_25">Serie Ret</th>
            <th class="w_50">Número Ret</th>
            <th class="w_50">Fecha Ret</th>
            <th class="w_50">Importe Pagado</th>
            <th class="w_50">Importe Retenido</th>
            <th class="w_25">Tipo</th>
            <th class="w_50">Serie</th>
            <th class="w_50">Número</th>
            <th class="w_50">Fecha</th>
          </tr>
        </thead>
        <tbody>
          {{$i=0}}
        @foreach($data['items'] as $item)
          <tr>
            <td class="centro">{{++$i}}</td>
            <td class="centro">{{$item->username}}</td>
            <td class="centro">{{$item->id_entidad}} - {{$item->id_dpto}}</td>
            <td>{{ $item->lote_numero }}</td>
            <td>{{ $item->cuo }}</td>
            <td>{{ $item->id_ruc }}</td>
            <td class="desc">{{ $item->nombre }}</td>
            <td>{{ $item->serie_retencion }}</td>
            <td>{{ $item->nro_retencion }}</td>
            <td>{{ $item->fecha_retencion }}</td>
            <td class="text-right">S/. {{ $item->importe_pagado  }}</td>
            <td class="text-right">S/. {{ $item->importe_retenido }}</td>
            <td>{{ $item->tipo_doc_comprobante }}</td>
            <td>{{ $item->serie_comprobante }}</td>
            <td>{{ $item->nro_comprobante }}</td>
            <td>{{ $item->fecha_comprobante }}</td>
          </tr>
        @endforeach
        </tbody>
        <tfoot>        
        @foreach($data['totales'] as $item)
          <tr>
            <th class="text-right" colspan="10">TOTAL</th>
            <th class="text-right">S/. {{$item->importe_pagado }}</th>
            <th class="text-right">S/. {{$item->importe_retenido  }}</th>
            <th colspan="4"></th>
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