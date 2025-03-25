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
        <th class="no">FECHA</th>
        <th class="desc">LOTE</th>
        <th class="unit">DEPARTAMENTO</th>
        <th class="total">HISTORICO</th>
        <th class="unit">DEBITO</th>
        <th class="total">CREDITO</th>
        <th class="total">SALDO</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $value)
    <tr>
        <td class="no">{{ $value['fecha'] }}</td>
        <td class="desc">{{ $value['lote'] }}</td>
        <td class="unit">{{ $value['historico'] }}</td>
        <td class="total">{{ $value['dpto'] }} </td>
        <td class="total">{{ $value['debito'] }} </td>
        <td class="total">{{ $value['credito'] }} </td>
        <td class="total">{{ $value['saldo'] }} </td>
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