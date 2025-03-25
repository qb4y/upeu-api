<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  
  <title>Carta CTS</title>
  
  <style type="text/css">
	@page {
	  margin: 0;
	}
	
	body {

		margin-right: 3.5cm;
		margin-left: 2.5cm;
	  font-family: sans-serif;
		text-align: justify;
	}
	

	div.footer {
	  position: fixed;
	  /*background: #ddd;*/
	  width: 100%;
	  border: 0px solid #888;
	  overflow: hidden;
    padding-bottom: 1cm;
    padding-right: 3.5cm;
    padding-left: 2.5cm;
    text-align: justify;
	}
	
	div.rightpane {
		position: fixed;
    background: rgb(0,83,127);
		width: 3cm;
		border-right: 1px solid #888;
		bottom: 0cm;
	  right: 0cm;
		height: 30cm;
  }
  .logo{
    bottom:0cm !important;
  }
	
	div.header {
	  top: 0cm;
		left: 0cm;
	  border-bottom-width: 1px;
	  /*height: 3cm;*/
	}
	
	div.footer {
	  bottom: 0.5cm;
		left: 0cm;
	  height: 1cm;
	}
	
	div.footer table {
	  width: 100%;
	  text-align: center;
	}
	main{
    padding-top:5cm;
  }
	hr {
	  page-break-after: always;
	  border: 0;
  }
  
    .text-center{
        text-align: center;
    }
    .text-left{
        text-align: left;
    }
    .text-right{
        text-align: right;
    }
    .text-de-under {
        text-decoration: underline;
    }

    .text-justify{
        text-align: justify;
        text-justify: inter-word;
    }
  </style>
  
</head>

<body marginwidth="0" marginheight="0">

<div class="header">
  <div style="text-align: right;">
   
  </div>
</div>

<div class="footer">
<table>
          <thead>
          </thead>
          <tbody>
              <tr>
                  <td width="49%" class="no-border "><h5>{{$data['datos']['empresa']}}</h5></td>
                  <td width="2%" class="no-border"><p class="text-center "></td>
                  <td width="49%"class="no-border" ><h6>{{$data['datos']['direccion_legal']}} <br>Telefono: {{$data['datos']['telefono']}}</h6></td>
              </tr>
          </tbody>
          <tfoot>
          <tfoot>
      </table>
</div>

<div class="rightpane">

  @if($data['datos']['id_empresa'] == 201)
      <div style="text-align: center;"><img class="logo" style="margin-top: 27cm;" width="100" height="100" src="{{url('/img/upeu.png')}}" style="margin-left: 0.5cm;"></div>
  @endif
  @if($data['datos']['id_empresa'] == 207)
      <div style="text-align: center;"><img class="logo" style="margin-top: 27cm;"  width="100" height="100" src="{{url('/img/logo_2.png')}}" style="margin-left: 0.5cm;" ></div>
  @endif
</div>
  <main >
  @if($data['message'])
        <h3 class="text-center bold" style="color:red;text-decoration:line-through">{{$data['message']}}</h3>
     @endif
    <h4 style="font-weight:normal !important;"> {{$data['datos']['nom_ciudad']}}, {{$data['datos']['date_description']}}</h4>
    <br>
        <br>
    <h4 class="text-left" style="margin-bottom:-14px; font-weight:normal !important;">Señores</h4>
        <h4 class="text-left" style="text-transform:uppercase;">{{$data['datos']['nombre_banco']}}</h4>
        <h4 class="text-left" style="margin-top:-14px; font-weight:normal !important;">Presente.-</h4>
        <br>
        <h4 style="font-weight:normal !important;">Apreciados señores:</h4>
        @if($data['datos']['sexo'] == 1)
          <p  class="text-justify">Por la presente certificamos que el señor <b>{{$data['datos']['p_nombre']}},</b> 
          identificado con <b>{{$data['datos']['num_documento']}}</b> ha cesado sus labores en mi representada, 
          {{$data['datos']['empresa']}}, con {{$data['datos']['ruc']}} hasta el {{$data['datos']['date_description']}}; por tal motivo y conforme la ley lo indica, está autorizado el retiro de su Compensación por 
          Tiempo de Servicios depositado en la cuenta <b>CTS {{$data['datos']['cta_bancaria']}} </b> en {{$data['datos']['moneda']}}, de vuestra institución bancaria. </p>
          @endif
        @if($data['datos']['sexo'] != 1)
          <p  class="text-justify">Por la presente certificamos que la señorita <b>{{$data['datos']['p_nombre']}},</b> 
          identificado con <b>{{$data['datos']['num_documento']}}</b> ha cesado sus labores en mi representada, 
          {{$data['datos']['empresa']}}, con {{$data['datos']['ruc']}} hasta el {{$data['datos']['date_description']}}; por tal motivo y conforme la ley lo indica, está autorizado el retiro de su Compensación por 
          Tiempo de Servicios depositado en la cuenta <b>CTS {{$data['datos']['cta_bancaria']}}</b> en {{$data['datos']['moneda']}}, de vuestra institución bancaria. </p>
        @endif
          <br>
        <h3 class="text-center" style="font-weight:normal !important;">Atentamente</h3>
          
  </main>
<!--  <hr> -->
</body></html>