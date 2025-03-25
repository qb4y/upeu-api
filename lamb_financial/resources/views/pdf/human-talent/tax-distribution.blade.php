<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>UPN - AFPNET</title>
    <link rel="stylesheet" href="css/purchases_shopping_record.css" media="all" />
  </head>
  <body>
    <header class="clearfix">
      <div id="project">
        <div><span>{{$data['datos']['empresa']}}</span></div>
        <div><span>
          REPORTE AFPNET - {{$data['datos']['entidad']}}</span></div>
        <div><span>{{$data['datos']['ruc']}}</span></div>
        <br>
        <div><span>DISTRIBUCIÓN DE IMPUESTOS DE PDT PLAME</span></div>
        <div><span>{{$data['datos']['periodo']}}</span></div>
      </div>
    </header>
    <main>
      <table style="width: 100%">
        <thead class="bordes_cabecera">
            <tr>
                <th rowspan="2" class="w_25">#</th>
                <th rowspan="2" class="w_70">ENTIDAD</th>
                <th rowspan="2" class="w_50">RENTA 4TA</th>
                <th rowspan="2">IMP. RENTA 5TA</th>
                <th rowspan="2">DEV 5TA CAT</th>
                <th rowspan="2">ONP</th>
                <th rowspan="2">ESSALUD</th>
                <th rowspan="2">EPS</th>
                <th colspan="2">ESSALUD</th>
                <th rowspan="2">CALCULADO</th>
                <th rowspan="2">TOTAL</th>
            </tr>  
            <tr>
                <th>APORTE</th>
                <th>CANT</th>
            </tr>     
        </thead>
        <tbody>
        {{$i=0}}
        @foreach($data['items'] as $item)
        <tr>
            <td class="centro">{{++$i}}</td>
            <td class="centro">{{ $item->entidad }}</td>
            <td class="centro">{{ $item->renta_cuarta }}</td>
            <td class="centro">{{ $item->imp_renta_quinta }}</td>
            <td class="centro">{{ $item->dev_quinta_cat }}</td>
            <td class="centro">{{ $item->onp }}</td> 
            <td class="centro">{{ $item->essalud }}</td> 
            <td class="centro">{{ $item->eps }}</td> 
            <td class="centro">{{ $item->essalud_vida }}</td>
            <td class="centro">{{ $item->essalud_vida_cant }}</td>
            <td class="centro">{{ number_format($item->calculado, 2) }}</td>
            <td>{{ number_format($item->calculado, 2) }}</td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </main>
    <footer>
      Impreso: {{$data['datos']['fechahora']}} - Usuario: {{$data['datos']['username']}} - LAMB UPN 2019 
    </footer>
  </body>
</html>