<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>UPN - Voucher Cover Page</title>
    <link rel="stylesheet" href="css/voucher_cover_page.css" media="all" />
  </head>
  <body>
    <header class="clearfix">
      <div >
        <div class="cab_logo">
          <img class="cab_log_img" src="img/{{$data['datos']['logo']}}" alt="Adventist">
        </div>
        <div class="cab_logo">
          <div id="project">
            <div><span>{{$data['datos']['empresa']}}</span></div> 
            <div><span>{{$data['datos']['ruc']}}</span></div>
            <div><span>{{$data['datos']['entidad_dpto']}}</span></div>
            <div><span>{{$data['datos']['periodo']}}</span></div>
            @foreach($data['voucher'] as $voucher)
            <div class="aasinet">
              <span>
              {{ $voucher->iniciales }}  {{ $voucher->nombre_tipo_voucher }} {{ $voucher->numero }} - {{ $voucher->fecha }} Lote: {{ $voucher->lote }}  
              </span>
            </div>
            <div class="aasinet">
              <span>
                Contabilizado por : {{ $voucher->nom_contador }} - Fecha: {{ $voucher->fecha_contabilizado }} 
              </span>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </header>
    <main>
      <table>
        <thead class="bordes_cabecera">
          <tr>
                <th rowspan="2">#</th>
                <th class="codigo" rowspan="2">Código</th>
                <th rowspan="2">Fecha</th>
                <th colspan="2">Proveedor</th>
                <th colspan="3">Documento</th>

            </tr>
            <tr>
                <th >Ruc</th>
                <th class="razon_social">Razón Social
                </th>
                <th>Comprobante</th>
                <th>Serie/Nro</th>
                <th>Importe</th>
            </tr>      
        </thead>
        <tbody>
        {{$i=0}}
        @foreach($data['items'] as $item)
        <tr>
                <td class="centro">{{++$i}}</td>
                <td class="text-left">{{ $item->id_entidad }}-{{ $item->id_depto }}-{{ $item->numero_voucher }}-{{ $item->correlativo }}</td>
                <td class="centro">{{ $item->fecha_doc }}</td>
                <td class="centro">{{ $item->ruc_proveedor }}</td>
                <td class="text-left">{{ $item->nombre_proveedor }}</td>
                <td class="centro">{{ $item->nombre_comprobante }}</td>
                <td class="centro">{{ $item->serie }}-{{ $item->numero }}</td>
                <td class="text-right"> {{ number_format($item->importe,2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        @foreach($data['totales'] as $item_total)
          <tr>
                <th class="text-right" colspan="7">TOTAL</th>
                <th class="text-right"> {{ number_format($item_total->importe,2) }}</th>
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