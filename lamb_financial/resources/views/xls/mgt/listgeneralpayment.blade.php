@extends('layouts.xls')
@section('content')
<table>
<thead>
    <tr style="background-color: #ccc">
        <td>#</td>
        <td>Tipo</td>
        <th>Codigo</th>
        <td>Apellidos_Nombres</td>
        <td>Area</td>
        <td>Condicion</td>
        <td>Situacion</td>
        @if($codigo=='BON_DEVE')
        <th>Tipo_kilometraje</th>
        <td>Nom_kilometraje</td>
        <td>Kilometraje</td>
        <td>Combustible</td>
        <td>Importe</td>
        @else
        <th>
            @if($tipo=='P')
                Porcentaje
            @else
                Importe
            @endif
        </th>
        @endif
        
    </tr>
    
</thead>
<tbody>
    <?php
 
    $i=1;
    ?>
    @foreach($data as $k)
    <tr>
        <td>{{$i}}</td>
        <td>{{$nombre}}</td>
        <th>{{$k->id_persona}}</th>
        <td>{{$k->nombrecompleto}}</td>
        <td>{{$k->area}}</td>
        <td>{{$k->condicion_laboral}}</td>
        <td>{{$k->situacion_trabajador}}</td>
        @if($codigo=='BON_DEVE')
        <th>{{$k->id_tipo_kilometraje}}</th>
        <td>{{$k->nombrecorto}}</td>
        <td>{{$k->imp_kilometraje}}</td>
        <td>{{$k->imp_combustible}}</td>
        <td>{{$k->importe}}</td>
        @else
        <th>{{$k->importe}}</th>
        @endif
        
    </tr>
    <?php
    $i++;
    ?>
    @endforeach
   
</tbody>
</table>
@endsection

