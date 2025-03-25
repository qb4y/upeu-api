<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<table border="1" cellspacing="1" cellpadding="1">
    <thead>
    <tr>
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
        <td>{{ $value['nombre_ctacte'] }}</td>
        <td>{{ $value['previsto_mes'] }}</td>
        <td>{{ $value['realizado_mes'] }}</td>
        <td>{{ $value['total_mes'] }} </td>
        <td>{{ $value['saldo_inicial'] }} </td>
        <td>{{ $value['previsto'] }} </td>
        <td>{{ $value['realizado'] }} </td>
        <td>{{ $value['total'] }} </td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2"></td>
        <td >TOTAL</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    </tfoot>
</table>
</body>
</html>