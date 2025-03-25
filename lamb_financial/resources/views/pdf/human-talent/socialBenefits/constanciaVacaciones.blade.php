<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  
  <title>Constancia vacaciones</title>
  
  <style type="text/css">
	@page {
	  margin: 0px;
	}
	
	body {
	  margin-top: 3.5cm;
	  margin-bottom: -1cm;
		margin-left: 1.5cm;
		margin-right: 1cm;
	  font-family: sans-serif;
		text-align: justify;
	}
	
	div.header,
	div.footer {
	  position: fixed;
	  /*background: #ddd; */
	  width: 100%;
	  /* border: 0px solid #888; */
	  overflow: hidden;
	  padding: 0.1cm;
	}

    div.header_div{
        position: fixed;
	  background: #ddd;
	  width: 100%;
	  border: 1px solid #888;
	  overflow: hidden;
	  padding: 0.1cm; 
    }

    div.header_div {
	  top: 1.5cm;
		left: 0cm;

	}


    div.secction{
        position: fixed;
	    width: 100%;
	    overflow: hidden;
	    padding: 0.1cm; 
    }
    div.secction {
	  top: 18cm;
	    left: 0cm;
	}

    div.secction_footer{
        position: fixed;
	    width: 100%;
	    overflow: hidden;
	    padding: 0.1cm; 
    }
    div.secction_footer {
	  top: 20cm;
	    left: 0cm;
	}

    
    .base_text {
        border-bottom-width: 1px;
	  height: 0.2cm;
    }
	
	div.leftpane {
		position: fixed;
		background: #ddd;
		width: 3cm;
		border-right: 1px solid #888;
		top: 0cm;
	  left: 0cm;
		height: 30cm;
	}
	
	div.header {
	  top: 0.5cm;
		left: 1cm;
	  border-bottom-width: 1px;
	  height: 3.2cm;
	}
	
	div.footer {
	  bottom: 0cm;
		left: 0cm;
	  border-top-width: 1px;
	  height: 1cm;
	}
	
	div.footer table {
	  width: 100%;
	  text-align: center;
	}
	
	hr {
	  page-break-after: always;
	  border: 0;
	}

    .text-underline {
        text-decoration: underline;
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
    table{
    width: 100%;
}

    table,td,th{
        border: 1px solid black;
        border-collapse: collapse;
    }
    table,.no-border{
        border:0px;
        border-collapse: collapse;
    }
    table,.border-top{
        border-bottom: 0px;
        border-left: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }
    table,.border-bottom{
        border-top: 0px;
        border-left: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }

    table,.border-bottom-dotted{
        border-top: 0px;
        border-left: 0px;
        border-right: 0px;
        border-style: dotted;
        color: black;
    }

    

    table,.border-bottom-top{
        border-left: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }


    table,.border-left{
        border-bottom: 0px;
        border-top: 0px;
        border-right: 0px;
        border-collapse: collapse;
    }
    table,.border-right{
        border-bottom: 0px;
        border-left: 0px;
        border-top: 0px;
        border-collapse: collapse;
    }
    .bold{
        font-weight: bold;
    }
  </style>
  
</head>

<body>

    <div class="header" style="margin-right: 0.7cm; background-color:white;">
        <div  style="text-align: right;  margin-right: 2cm; margin-top: 0.5cm;">
                @if($data['datos']['id_empresa'] == 201)
                    <img width="100" height="100" src="{{url('/img/upeu.png')}}" style="margin-left: 0.5cm;">
                @endif
                @if($data['datos']['id_empresa'] == 207)
                    <img width="100" height="100" src="{{url('/img/logo_2.png')}}" style="margin-left: 0.5cm;" >
                @endif
        </div>
        <div style="text-align: left; margin-top: -4cm; width=20%">
            <h4 style="margin-top: 0.2cm">SEDE: {{$data['datos']['nom_ciudad']}}</h4>
            <h4 style="margin-top: -0.5cm" >{{$data['datos']['ruc']}}</h4>
        </div>
    </div>
    <main>
        <h4 style="font-weight:normal !important; text-align: right; margin-right: 0.7cm;"> {{$data['datos']['nom_ciudad']}}, {{$data['datos']['date_description']}}</h4>
        <br>
        <br>
        <table style="font-size: 0.80rem;">
            <thead></thead>
            <tbody>
                <tr>
                    <td width="14%" class="text-left no-border ">Estimado/a</td>
                    <td width="70%"  class="no-border text-left">{{$data['datos']['p_nombre']}}</td>
                    <td width="16%" class=" no-border">
                </tr>
                <tr>
                    <td width="14%" class="text-left no-border "></td>
                    <td width="70%" class="text-left no-border">{{$data['datos']['cargo']}}</td>
                    <td width="16%" class=" no-border">
                </tr>
                <tr>
                    <td width="14%" class="text-left no-border "></td>
                    <td width="70%" class="text-left no-border">{{$data['datos']['nom_ciudad']}} </td>
                    <td width="16%" class=" no-border">
                </tr>
            </tbody>
        </table>
        <br>
        <br>
    <?php if ($data['data']){?>
        <p  class="text-justify" style="font-size: 0.80rem;">Mediante el presente escrito, a efectos previstos en el Reglamento Eclesiastico y en consecuencia a su Liquidacion Legal 
        realizada por la Transferencia en Estatus Misionero, se le comunica que debiera usted hacer el deposito de los siguientes 
        conceptos: 
        </p>
        <br>
        <br>
        <div style="margin-left: 2.2cm; margin-right: 2cm; font-size: 0.80rem;">    
            <table >
                <thead></thead>
                <tbody style="border: 1px solid black">
                    <tr>
                        <td width="41%" colspan="2" class=" text-left text-de-under no-border"><b>VACACIONES TRUNCAS</b></td>
                        <td width="34%" class="text-right no-border"></td>
                        <td width="10%" class="text-right no-border"></td>
                        <td width="15%"  class="text-left no-border"></td>
                    </tr>
                    <tr>
                        <td width="35%"  class="no-border text-left"></td>
                        <td width="6%" class="text-right no-border"></td>
                        <td width="44%" colspan="2" class="text-center no-border">Equivalente a Vacaciones año {{$data['datos']['anho']}}</td>
                        <td width="15%"  class="text-right no-border ">{{number_format($data['data']->vac_trunc,2)}}</td>
                    </tr>
                    <tr>
                        <td width="35%"  class="no-border text-left"></td>
                        <td width="6%" class="text-right no-border"></td>
                        <td width="34%" class="text-right no-border">(-)</td>
                        <td width="10%" class="text-left no-border"> AFP/ONP</td>
                        <td width="15%"  class="text-right no-border ">{{number_format($data['data']->sist_pen_total,2)}}</td>
                    </tr>
                    <tr>
                        <td width="35%"  class="no-border text-left"></td>
                        <td width="6%" class="text-right no-border"></td>
                        <td width="34%" class="text-right no-border">(-)</td>
                        <td width="10%" class="text-left no-border"> 5ta categoría</td>
                        <td width="15%"  class="text-right no-border ">{{number_format($data['data']->ir_5ta_cat,2)}}</td>
                    </tr>
                    <tr>
                        <td width="35%"  class="no-border text-left"></td>
                        <td width="6%" class="text-right no-border"></td>
                        <td width="34%" class="text-right no-border">(-)</td>
                        <td width="10%" class="text-left no-border"> Diezmo</td>
                        <td width="15%"  class="text-right no-border ">{{number_format($data['data']->diezmo,2)}}</td>
                    </tr>
                    <tr>
                        <td width="35%"  class="no-border text-left"></td>
                        <td width="6%" class="text-right no-border"></td>
                        <td width="44%" colspan="2"class="text-right no-border">Total a depositar</td>
                        <td width="15%"  class="text-right border-bottom ">{{number_format($data['data']->total,2)}}</td>
                    </tr>
                    <tr>
                        <td width="35%"  class="no-border text-left"></td>
                        <td width="6%" class="text-right no-border"></td>
                        <td width="34%" class="text-right no-border"></td>
                        <td width="10%" class="text-right no-border"></td>
                        <td width="15%"  class="text-left no-border "></td>
                    </tr>
                    <tr>
                        <td width="41%" colspan="2" class="no-border text-de-under text-left"><b>ENTIDAD DE TRANSFERENCIA</b></td>
                        <td width="34%" class="text-right no-border"></td>
                        <td width="10%" class="text-right no-border"></td>
                        <td width="15%"  class="text-left no-border "></td>
                    </tr>
                    <tr>
                        <td width="35%"  class="no-border text-left"></td>
                        <td width="6%" class="text-right no-border"></td>
                        <td width="44%" colspan="2" class="text-right no-border">{{$data['datos']['entidad']}} Sede - {{$data['datos']['id_entidad']}}</td>
                        <td width="15%"  class="text-left no-border "></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="secction">
            <p style="font-size: 0.80rem; margin-left: 2cm; margin-right: 0.5cm;"> Agradecemos su apoyo <br> 
            Cordialmente
            </p>
            
        </div>
        <div class="secction_footer" style="margin-top:1.6cm; margin-left: 2cm; margin-right: 0.5cm;">
            <table  style="font-size: 0.60rem; ">
                <tbody>
                    <tr>
                        <td width="41%" class="border-top text-center"><p class="text-center" style="margin-top:0mm;"> 
                        <br>
                        <span class="bold">.........................................................</span> 
                         <br>
                          Director Gestión de Talento Humano 
                          <br>
                          {{$data['datos']['entidad']}}
                          </p></td>
                        <td width="8%" class="no-border" class="text-center "><p class="text-center "></p></td>
                        <td width="41%" class="border-top text-center"><p class="text-center" style="margin-top:0mm;"> <span class="bold">Recibido: ................................................................</span> <br> Fecha: ...................................</p></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php } else {?>
<h3 class="text-center bold">No se econtró información para mostrar</h3>
<?php } ?>
    </main>

</body>
</html>