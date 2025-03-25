<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Registro de Honorarios</title>
    <link rel="stylesheet" href="css/purchases_shopping_record.css" media="all" />
  </head>
  <body>
    <header class="clearfix">
    <div id="project">
        <div><span>{{$data['datos']['empresa']}}</span></div>
        <div><span>REGISTRO DE HONORARIOS</span></div>
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
            <th class="w_50">Entidad/Dpto</th>
            <th class="w_50">Correlativo</th>
            <th class="w_50">CUO</th>
            <th class="w_50">Fecha</th>
            <th class="w_50">Tipo</th>
            <th class="w_50">Serie</th>
            <th class="w_50">Número</th>
            <th class="w_50">Ruc</th>
            <th class="w_150">Razón Social</th>
            <th class="w_150">Glosa</th>
            <th class="w_70">Importe</th>
            <th class="w_70">Retención</th>
            <th class="w_70">Neto</th>
          </tr>
        </thead>
        <tbody>
          {{$i=0}}
        @foreach($data['items'] as $item)
          <tr>
            <td class="centro">{{++$i}}</td>
            <td class="centro">{{$item->username}}</td>
            <td class="centro">{{$item->entidad}}/{{$item->id_depto}}</td>
            <td class="centro">{{$item->lote_numero}} - {{$item->correlativo}}</td>
            <td class="centro">{{$item->cuo}}</td>
            <td class="centro">{{$item->fecha_doc}}</td>
            <td class="centro">{{$item->tipo_doc}}</td>
            <td class="centro">{{$item->serie}}</td>
            <td class="centro">{{$item->numdoc}}</td>
            <td class="desc">{{$item->ruc}}</td>
            <td class="desc">{{$item->nombre}}</td>
            <td class="desc">{{$item->asiento_glosa}}</td>
            <td class="text-right">S/. {{$item->importe}}</td>
            <td class="text-right">S/. {{$item->renta}}</td>
            <td class="text-right">S/. {{$item->neto}}</td>
          </tr>
        @endforeach
        </tbody>
        <tfoot>
        @foreach($data['totales'] as $item)
          <tr>
            <th class="text-right" colspan="12">TOTAL</th>
            <th class="text-right">S/. {{$item->importe}}</th>
            <th class="text-right">S/. {{$item->renta}}</th>
            <th class="text-right">S/. {{$item->neto}}</th>
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