<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
{{--<h1>{{ $title }}</h1>--}}
{{--<h4>{{ $date }}</h4>--}}

<table border="1" cellspacing="1" cellpadding="1">
    <thead>
    <tr>
        <th>CODIGO</th>
        <th>NOMBRE</th>
        <th>RESPONSABLE</th>
        <th>PREVISTO MES</th>
        <th>REALIZADO MES</th>
        <th>TOTAL MES</th>
        <th>SALDO ANTERIOR</th>
        <th>PREVISTO</th>
        <th>REALIZADO</th>
        <th>TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $value)
    <tr>
        <td class="no">{{ $value['codigo'] }}</td>
        <td class="desc">{{ $value['nombre'] }}</td>
        <td class="unit">{{ $value['responsable'] }}</td>
        <td class="total">{{ $value['previsto_mes'] }} </td>
        <td class="total">{{ $value['realizado_mes'] }} </td>
        <td class="total">{{ $value['total_mes'] }} </td>
        <td class="total">{{ $value['saldo_inicial'] }} </td>
        <td class="total">{{ $value['previsto'] }} </td>
        <td class="total">{{ $value['ingreso'] }} </td>
        <td class="total">{{ $value['realizado'] }} </td>
        <td class="total">{{ $value['total'] }} </td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2"></td>
        <td >TOTAL</td>
    </tr>
    </tfoot>
</table>
</body>
</html>