<!DOCTYPE html>
<html>
<head lang="en">
    
    <style>
        .custombordertop {
            border-top: double ;
          }
    </style>
    <meta charset="UTF-8">
    <title></title>
    
</head>
<body>

<div class="card-block">
        <div class="row">
            <div class="col-lg-12">
                <h5 class="text-black"><strong>Mayor de Cuentas</strong></h5>
                
                <div class="mb-5">
                    <table class="table">
                        @foreach($data as $item)
                        <tbody>
                        <tr>
                            <th align="right">Fondo:</th>
                            <th colspan="2">{{ $item['id_fondo'] }} {{ $item['nombre_fondo'] }}</th>
                            <th align="right">Cuenta:</th>
                            <th colspan="2">{{ $item['id_cuentaaasi'] }} {{ $item['nombre_cuentaaasi'] }}</th>
                        </tr>
                        <tr>
                            <th align="right">Departamento:</th>
                            <th colspan="2">{{ $item['id_depto'] }} {{ $item['nombre_depto'] }}</th>
                            <th align="right">Tipo Sub-Cuenta:</th>
                            <th colspan="2">{{ $item['id_tipoctacte'] }} {{ $item['nombre_tipoctacte'] }}</th>
                        </tr>
                        <tr>
                            <th align="right">Restricción:</th>
                            <th colspan="2">{{ $item['id_restriccion'] }} {{ $item['nombre_restriccion'] }}</th>
                            <th align="right">Sub-Cuenta:</th>
                            <th colspan="2">{{ $item['id_ctacte'] }} {{ $item['nombre_cta_cte'] }}</th>
                        </tr>
                        <tr >
                            <th>Fecha</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th align="right">Débito</th>
                            <th align="right">Crédito</th>
                            <th align="right">Saldo</th>
                        </tr>
                        @foreach($item['items'] as $detalle)
                        <tr>
                            <td>{{ $detalle['fec_view'] }}</td>
                            <td>{{ $detalle['lote'] }}</td>
                            <td>{{ $detalle['descripcion'] }}</td>
                            
                            
                            @if ($detalle['descripcion'] == 'Saldo Final')
                                <td align="right" class="text-right custombordertop font-weight-bold">
                                    @if ($detalle['debe'] != 0)
                                        <span style="color:blue">
                                            <b>
                                                {{ $detalle['debe'] }}
                                            </b>
                                        </span>
                                    @endif
                                </td>                                
                                <td align="right" class="text-right custombordertop font-weight-bold">
                                    @if ($detalle['haber'] != 0)
                                        <span style="color:red">
                                            <b>
                                                {{ $detalle['haber'] }}
                                            </b>
                                        </span>
                                    @endif
                                </td>                                
                                <td align="right" class="text-right custombordertop font-weight-bold">
                                    @if ($detalle['saldo'] >= 0)
                                        <span style="color:blue">
                                            {{ $detalle['saldo'] }}
                                        </span>
                                    @else
                                        <span style="color:red">
                                            {{ $detalle['saldo'] }}
                                        </span>
                                    @endif
                                </td>                                
                            @else
                                <td align="right">
                                    @if ($detalle['debe'] != 0)
                                        <span style="color:blue">
                                            {{ $detalle['debe'] }}
                                        </span>
                                    @endif
                                </td>                                
                                <td align="right">
                                    @if ($detalle['haber'] != 0)
                                        <span style="color:red">
                                            {{ $detalle['haber'] }}
                                        </span>
                                    @endif
                                </td>                                
                                <td align="right">
                                    @if ($detalle['saldo'] >= 0)
                                        <span style="color:blue">
                                            {{ $detalle['saldo'] }}
                                        </span>
                                    @else
                                        <span style="color:red">
                                            {{ $detalle['saldo'] }}
                                        </span>
                                    @endif
                                </td>                                
                            @endif
                        </tr>
                        @endforeach
                        </tbody>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>