<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>UPN - Cálculo 5ta Categoría</title>
    <link rel="stylesheet" href="css/purchases_shopping_record.css" media="all" />
  </head>
  <body>
    <header class="clearfix">
      <div id="project">
        <div><span>{{$data['datos']['empresa']}}</span></div>
        <div><span>
          PROYECCION DE 5TA CATEGORÍA - {{$data['datos']['entidad']}}</span></div>
        <div><span>{{$data['datos']['ruc']}}</span></div>
        <div><span>{{$data['datos']['periodo']}}</span></div>
      </div>
    </header>
    <main>
      <table style="width: 100%">
        <thead class="bordes_cabecera">   
            <tr>
              <th rowspan="2" class="w_10">N°</th>
              <th rowspan="2" class="w_40">Codigo</th>
              <th rowspan="2" class="w_150">Apellidos y Nombres</th>
              <th colspan="2">Periodo Cálculo</th>
              <th rowspan="2">Meses</th>
              <th colspan="1">Básico <br>Anual</th>
              <th colspan="1">Asig. Fam. <br>Anual</th>
              <th colspan="1">Remun <br>Especie</th>
              <th colspan="1">Remun. <br> Variable</th>
              <th rowspan="2">Grati. <br> Anual</th>
              <th rowspan="1">Bon. Ext<br> Anual</th>
              <th rowspan="1">Asig. Ed.<br> Anual</th>
              <th rowspan="1">Bono por <br>Asig. Ed.<br> Anual</th>
              <th rowspan="1">Comis.</th>
              <th rowspan="1">Bono <br> Destaque</th>
              <th rowspan="1">Prest. <br> Alim.</th>
              <th rowspan="1">Asig. <br> UPeU</th>
              <th rowspan="1">V.L.D</th>
              <th rowspan="2">Renta bruta <br>Anual</th>
              <th rowspan="2">Deducción <br>de 7 UIT</th>
              <th rowspan="2">Renta <br>Neta</th>
              <th rowspan="2">Hasta <br>5 UIT</th>
              <th rowspan="2">5 UIT <br>- 20 UIT</th>
              <th rowspan="2">20 UIT <br>- UIT</th>
              <th rowspan="2">Total I.R. <br> 5ta Cat.</th>
          </tr>
          <tr>
              <th>Ingreso</th>
              <th>Salida</th>
              <th>1000</th>
              <th>1122/1121</th>
              <th>1222</th>
              <th>1086</th>
              <th>3100</th>
              <th>1118/1119</th>
              <th>-</th>
              <th>1151</th>
              <th>1215</th>
              <th>1147</th>
              <th>1138</th>
              <th>1145</th>
          </tr> 
        </thead>
        <tbody>
        {{$i=0}}
        @foreach($data['items'] as $item)
        <tr>
            <td class="centro">{{++$i}}</td>
            <td class="centro">{{ $item->num_documento }}</td>
            <td class="izquierda">{{ $item->nom_persona }}</td>
            <td class="centro">{{ date('d/m/Y', strtotime($item->ingreso)) }}</td>
            <td class="centro">{{ date('d/m/Y', strtotime($item->salida)) }}</td>
            <td class="centro">{{ $item->total_meses }}</td>
            <td>{{ number_format($item->basico_anual, 2) }}</td>
            <td>{{ number_format($item->primainf_anual, 2) }}</td>
            <td>{{ number_format($item->remunesp_anual, 2) }}</td>
            <td>{{ number_format($item->remunvar_anual, 2) }}</td>
            <td>{{ number_format($item->grat_anual, 2) }}</td>
            <td>{{ number_format($item->bextraord_anual, 2) }}</td>
            <td>{{ number_format($item->basiged_anual, 2) }}</td>
            <td>0</td>
            <td>{{ number_format($item->comisiones_anual, 2) }}</td>
            <td>{{ number_format($item->bdestaque_anual, 2) }}</td>
            <td>{{ number_format($item->bonprestalim_anual, 2) }}</td>
            <td>{{ number_format($item->asigupeu_anual, 2) }}</td>
            <td>{{ number_format($item->vld_anual, 2) }}</td>
            <td>{{ number_format($item->renta_bruta_anual, 2) }}</td>
            <td>{{ number_format($item->deduccion_7uit, 2) }}</td>
            <td>{{ number_format($item->renta_neta, 2) }}</td>
            <td>{{ number_format($item->hasta_5uit, 2) }}</td>
            <td>{{ number_format($item->hasta_20uit, 2) }}</td>
            <td>{{ number_format($item->hasta_35uit, 2) }}</td>
            <td>{{ number_format($item->hasta_35uit, 2) }}</td>
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