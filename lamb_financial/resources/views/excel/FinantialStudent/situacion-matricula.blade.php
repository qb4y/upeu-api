
<table>
    <thead>
    <tr>
        <th>C&oacute;odigo</th>
        <th>Estudiante</th>
        <th>Email</th>
        <th>Escuela</th>
        <th>Ciclo</th>
        <th>Plan</th>
        <th>Fecha</th>
        <th>Plan pago</th>
        <th>Imp. mat</th>
        <th>Celular</th>
        <th>Total</th>
    </tr>

    </thead>
    <tbody>
    @foreach($data as $row)
    <tr>
        <td>{{$row->codigo}}</td>
        <td>{{$row->nombres}}</td>
        <td>{{$row->correo}}</td>
        <td>{{$row->nombre_escuela}}</td>
        <td>{{$row->ciclo}}</td>
        <td>{{$row->plan}}</td>
        <td>{{$row->fecha_registro}}</td>
        <td>{{$row->plan_pago}}</td>
        <td>{{$row->imp_mat_ens}}</td>
        <td>{{$row->celular}}</td>
        <td>{{$row->total}}</td>
    </tr>
    @endforeach
    </tbody>
</table>


