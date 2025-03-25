@extends('layouts.xls')
@section('content')
<table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Doc.</th>
              <th>Codigo</th>
              <th>EAP</th>
              <th>Descripción</th>
              <th>T. Enseñanza</th>
              <th>Enseñanza</th>
              <th>T. Matricula</th>
              <th>Matricula</th>
              <th>Estado</th>
              <th>Insertado</th>
              <th>Modificado</th>
            </tr>
          </thead>
          <tbody>
          	@foreach($data as $item)
            <tr>
              <td>{{$item->nom_persona}}</td>
              <td>{{$item->tipo}}</td>
              <td>{{$item->num_documento}}</td>
              <td>{{$item->codigo}}</td>
              <td>{{$item->eap}}</td>
              <td>{{$item->descripcion}}</td>
              <td>{{$item->tipo_ense}}</td>
              <td>{{$item->ensenanza}}</td>
              <td>{{$item->tipo_mat}}</td>
              <td>{{$item->matricula}}</td>
              <td>
              	<?php
              	if($item->estado == '1'){
              		echo 'Activo';
              	}else{
              		echo 'Inactivo';
              	}
              	?>
              	</td>
               <td> {{$item->useri}} - {{$item->freg}}
               </td>
               <td> {{$item->usere}} - {{$item->fedit}}
               </td>
            </tr>
            @endforeach
          </tbody>

        </table>
@endsection