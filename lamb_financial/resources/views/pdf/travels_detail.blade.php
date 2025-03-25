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
        <th>FECHA</th>
        <th>LOTE</th>
        <th>DESCRIPCION</th>
        <th>IMPORTE</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $value)
    <tr>
        <td>{{ $value['fecha'] }}</td>
        <td>{{ $value['codigo'] }}</td>
        <td>{{ $value['comentario'] }}</td>
        <td>{{ $value['cos_valor'] }} </td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2"></td>
        <td >TOTAL</td>
        <td></td>
    </tr>
    </tfoot>
</table>
</body>
</html>