@extends('layouts.xls')
@section('content')
<table>
<thead>
    <tr>
        <th rowspan="2"></th>
        <th rowspan="2">ID</th>
        <th rowspan="2">Tipo</th>
        <th rowspan="2">Código</th>
        <th rowspan="2">Nombre</th>
        <th rowspan="2">Sunat</th>
        <th rowspan="2">APS</th>
        <th rowspan="2">Orden</th>
        <th rowspan="2">Desc Asoc.</th>
        <th rowspan="2">No afecto a Diezmo</th>
        <th>Por Empleador</th>
        <th colspan="3">Por Trabajador</th>
        <th colspan="4">Beneficios Sociales</th>
        <th rowspan="2">Vigencia</th>
    </tr>
    <tr>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th>Essalud</th>
        <th>SNP</th>
        <th>SPP</th>
        <th>5Ta Cat.</th>
        <th>Afecta</th>
        <th>Grat.</th>
        <th>CTS.</th
        <th>Vac.</th
        <th></th>
    </tr>
</thead>
<tbody>
    <?php
 
    $i=1;
    ?>
    @foreach($data as $k)
    <tr>
        <td>{{$i}}</td>
        <td>{{$k->id_concepto_planilla}}</td>
        <td>{{$k->tipo_concepto_planilla}}</td>
        <td>{{$k->codigo}}</td>
        <td>{{$k->nombre}}</td>
        <td>{{$k->codsunat}}-{{$k->sunat}}</td>
        <td>{{$k->id_conceptoaps}}-{{$k->aps}}</td>
        <td>{{$k->orden}}</td>
        <td>{{$k->descuento}}</td>
        <td>{{$k->nodiezmo}}</td>
        <td>{{$k->emp_essalud}}</td>
        <td>{{$k->tra_snp}}</td>
        <td>{{$k->tra_spp}}</td>
        <td>{{$k->tra_retntaqcat}}</td>
        <td>{{$k->tipo}}</td>
        <td>{{$k->gratificacion}}</td>
        <td>{{$k->cts}}</td>
        <td>{{$k->vacaciones}}</td>
        <td>{{$k->vigencia}}</td>
    </tr>
    <?php
    $i++;
    ?>
    @endforeach
   
</tbody>
</table>
@endsection

