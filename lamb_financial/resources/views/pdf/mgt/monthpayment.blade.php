@extends('layouts.pdf')
@section('content')


<div class="text-center">
    <span style="font-size: 14px;"><b>{{$nombre}}</b></span><br>
    <span style="font-size: 12px;">Concepto: {{$concepto}}</span>
    @if($codigo=='BON_DEVE')
    <br>
        <span style="font-size: 10px;">
            @foreach($TypeMonth as $k)
                Cons.Comb: {{number_format($k->consumo_comb, 2, '.', ',')}} |
                Mant: {{number_format($k->mantenimiento, 4, '.', ',')}} |
                Seg: {{number_format($k->seguro, 4, '.', ',')}} |
                Dep. Veh: {{number_format($k->depreciacion, 4, '.', ',')}}
            @endforeach
        </span>
    @endif
</div>

<span style="font-size: 12px;">Periodo: {{$monthname}} - {{$id_anho}}</span>
<table class="table  table-sm table-bordered">
<thead>
    <tr>
        <th>#</th>
        <th>Codigo</th>
        <th>Apellidos y Nombres</th>
        <th>Area</th>
        <th>Condición</th>
        <th>Situación</th>
        @if($codigo=='BON_DEVE')
        <th>Kilometraje</th>
        <th>Combustible</th>
        @endif
        <th>Importe</th>
    </tr>
    
</thead>
<tbody>
    <?php
 
    $i=1;
    $total=0;
    ?>
    @foreach($data as $k)
    <tr>
        <td>{{$i}}</td>
        <td>{{$k->id_persona}}</td>
        <td>{{$k->nombrecompleto}}</td>
        <td>{{$k->area}}</td>
        <td>{{$k->condicion_laboral}}</td>
        <td>{{$k->situacion_trabajador}}</td>
        @if($codigo=='BON_DEVE')
        <td class="text-right">{{number_format($k->imp_kilometraje, 2, '.', ',')}}</td>
        <td class="text-right">{{number_format($k->imp_combustible, 2, '.', ',')}}</td>
        @endif
        <td class="text-right">{{number_format($k->importe, 2, '.', ',') }}</td>
    </tr>
    <?php
    $i++;
    $total=$total + $k->importe;
    ?>
    @endforeach

        <?php
        $cols = 6;
        ?>
        @if($codigo=='BON_DEVE')
        <?php
            $cols = 8;
        ?>
        @endif
    <tr>
        <td colspan="{{$cols}}" class="text-right"> Total:</td>
        <td  class="text-right">{{number_format($total, 2, '.', ',') }}</td>
    </tr>
</tbody>
</table>

@endsection
