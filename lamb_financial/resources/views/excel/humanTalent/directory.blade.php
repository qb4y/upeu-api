
  <table>
    <thead class="text-center">
        <tr>
            <th colspan="12">DIRECTORIO </th>
        </tr>
        <tr>
            <th colspan="7">Colaboradores</th>
            <th colspan="5">Esposas</th>
        </tr>
        <tr>
            <th>ÁREA</th>
            <th>N°</th>
            <th>CARGO</th>
            <th>RESPONSABLE</th>
            <th>N° Documento</th>
            <th>E-MAIL</th>
            <th>F.Nac.</th>
            <th>Cel.</th>
            <th>Nombres Completo</th>
            <th>Celular</th>
            <th>E-MAIL</th>
            <th>F.Nac.</th>
        </tr>
    </thead>
    <tbody>
    @foreach($data as $row)  
        <tr>
            <td>{{$row->departamento}}</td>
            <td>20</td>
            <td>{{$row->cargo}}</td>
            <td>{{$row->e_name}}</td>
            <td>{{$row->num_documento}}</td>
            <td>{{$row->email}}</td>
            <td>{{$row->e_f_na}}</td>
            <td>{{$row->telefono}}</td>
            <td>{{$row->p_name}}</td>
            <td>{{$row->p_telefono}}</td>
            <td>{{$row->p_email}}</td>
            <td>{{$row->p_f_na}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
