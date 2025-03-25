<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Example 3</title>
    <link rel="stylesheet" href="css/purchases_test.css" media="all" />
  </head>
  <body>
    <header class="clearfix">
      <div id="logo">
      </div>
      <h1>REGISTRO DE HONORARIOS</h1>
      <div id="project">
        <div><span>{{$data['datos']['empresa']}}</span></div>
        <div><span>{{$data['datos']['periodo']}}</span></div>
      </div>
    </header>
    <main>
      <table>
        <thead>
          <tr>
            <th class="w_20">#</th>
            <th class="w_50">Entidad</th>
            <th class="w_50">Voucher</th>
            <th class="w_70">Fecha</th>
            <th class="w_50">Tipo</th>
            <th class="w_50">Serie</th>
            <th class="w_70">Número</th>
            <th class="w_70">Ruc</th>
            <th class="w_300">Razón Social</th>
            <th class="w_100">Importe</th>
            <th class="w_100">Retención</th>
            <th class="w_100">Neto</th>
          </tr>
        </thead>
        <tbody>
          {{$i=0}}
        @foreach($data['items'] as $item)
          <tr>
            <td class="centro">{{++$i}}</td>
            <td class="centro">{{$item->entidad}}</td>
            <td class="centro">{{$item->voucher}}</td>
            <td class="centro">{{$item->fecha_doc}}</td>
            <td class="centro">{{$item->tipo_doc}}</td>
            <td class="centro">{{$item->serie}}</td>
            <td class="centro">{{$item->numdoc}}</td>
            <td class="desc">{{$item->ruc}}</td>
            <td class="desc">{{$item->nombre}}</td>
            <td class="total">S/. {{$item->importe}}</td>
            <td class="total">S/. {{$item->renta}}</td>
            <td class="total">S/. {{$item->neto}}</td>
          </tr>
        @endforeach
        @foreach($data['totales'] as $item)
          <tr>
            <td colspan="9">TOTAL</td>
            <td class="total">S/. {{$item->importe}}</td>
            <td class="total">S/. {{$item->renta}}</td>
            <td class="total">S/. {{$item->neto}}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
      <div id="notices">
        <div>Nota:</div>
        <div class="notice">UPN.</div>
      </div>
    </main>
    <footer>
      UPN Desarrollo
    </footer>
  </body>
</html>