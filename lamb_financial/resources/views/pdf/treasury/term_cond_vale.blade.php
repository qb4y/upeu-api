@extends('layouts.pdf')
@section('content')

<style type="text/css" media="screen">

.font-size-10 {
  font-size: 10px !important;
}

.head-info {
	background-color: #7f264a;
	color: white;
	padding: 4px;
	text-transform: uppercase;
	font-weight: 600;
	font-size: .80rem;
	
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.text-left {
  text-align: left !important;
}

.text-right {
  text-align: right !important;
}

.text-center {
  text-align: center !important;
}

.page-break {
    page-break-after: always;
}
.tabla_fima {
  widows: 100%;
}

</style>



  <div class="text-center head-info font-size-10">
          TERMINOS Y CONDICIONES
  </div>
  
  <div>
  contenido bla bla bla
  </div>
  <br>
  <br>
  <table class="tabla_fima">
      <tr>

          <td class="text-center">
            @if ($firma_trabajador)
            <img src="{{$firma_trabajador}}"    height="80"/>
            @endif
            -------------------------------------------
            <br>
            {{$trabajdor}}<br>
            {{$documento}}<br>
            Trabajador
          </td>
          <td class="text-center">
            @if ($firma_responsable)
            <img src="{{$firma_responsable}}"   height="80"/>
            @endif
            -------------------------------------------
            <br>
            {{$responsable}}<br>
            {{$documentoresp}}<br>
            Gerente Financiero
          </td>

      </tr>
  </table>

@endsection