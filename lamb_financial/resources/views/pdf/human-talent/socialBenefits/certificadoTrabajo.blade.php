<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  
  <title>Certificado de Trabajo</title>
  <style type="text/css">
	@page {
	  margin: 0;
	}
	
	body {
	  font-family: 'Bookman Old Style', sans-serif;
	}
	

	div.footer {
	  position: fixed;
	  /background: #ddd;/
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
    background: #6DB3F2;
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
	  /height: 3cm;/
    width: 100%;
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
    margin-right: 2.5cm;
		margin-left: 2.5cm;
    text-align: justify;
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
    div.absolute {
      /*border: 2px dotted green; */
      position: absolute;
      padding: 0.5em;
      text-align: center;
      vertical-align: middle;
    }
  </style>
  
</head>

<body marginwidth="0" marginheight="0">

<div class="header">
  <div class="absolute" style="top: 45px; left: 80px;">
    @if($data['datos']['id_empresa'] == 201)
        <img class="logo"  width="120" height="100" src="{{url('/img/upeu.png')}}" >
    @endif
    @if($data['datos']['id_empresa'] == 207)
        <img class="logo"  width="120" height="80" src="{{url('/img/logoIASD.png')}}">
    @endif
  </div>
  <div class="absolute" style="top: 35px; right: 20px;">
    <table style="width: 40%;">
        <thead>
        </thead>
        <tbody>
            <tr>
                <td width="49%" class="no-border " style="color: #848484;"><h5 class="text-center">{{$data['datos']['empresa']}}</h5></td>
                <td width="2%" class="border-right" style="border-right:1px solid black"></td>
                <td width="49%"class="no-border" style="color: #848484;">
                  <h6 class="text-center" >{{$data['datos']['direccion_legal']}}</h6>
                  <h6 class="text-center" style="margin-top:-20px;">Telefono: {{$data['datos']['telefono']}}</h6>
                </td>
            </tr>
        </tbody>
        <tfoot>
        <tfoot>
    </table>
  </div>
</div>
<main>
<h2 class="text-center">CERTIFICADO DE TRABAJO</h2>
    <p class="text-justify" style="line-height:1.8em;">El que suscribe, {{$data['datos']['representante']}}; representante legal de la "<i><b>{{$data['datos']['empresa']}}</b></i>", 
    con {{$data['datos']['ruc']}}, a través de la presente <b>CERTIFICA</b> que:</p>
    <h2 class="text-center">{{$data['datos']['p_nombre']}}</h2>
    <p class="text-center" style="margin-top:-20px;">({{$data['datos']['num_documento']}})</p>
      <p  class="text-justify" style="line-height:1.8em;">
      Ha laborado en nuestra institución, por un espacio de <b>{{$data['datos']['total_elapsed_time']}}</b> comprendidos desde el {{$data['datos']['date_text_ini']}} al {{$data['datos']['date_description']}}, desempeñándose como {{$data['datos']['cargo']}} .
      <br>
      <br>
      Se expide el presente certificado a solicitud del interesado para los fines 
      que estime conveniente.
        </p>
      <br>
      <br>
      <p class="text-center">{{$data['datos']['nom_ciudad']}}, {{$data['datos']['date_description']}}</p>
      
      
    </main>
    </body>
</html>