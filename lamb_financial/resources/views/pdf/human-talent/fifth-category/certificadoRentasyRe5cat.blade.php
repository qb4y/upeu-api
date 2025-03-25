<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">

  <title>Certificado de Rentas y Retenciones 5ta Categoría</title>

  <style type="text/css">

       @page {
	  margin: 0px;
	}
	
	body {
	  margin-top: 3.5cm;
	  margin-bottom: -1cm;
		margin-left: 1.5cm;
		margin-right: 1cm;
	  
       font-family: "Arial Narrow";
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

<body marginwidth="0" marginheight="0">

    <div class="header">
        <div style="text-align: center;">
            <h4 style="margin-top: 0.2cm">CERTIFICADO DE RENTAS Y RETENCIONES POR RENTAS DE</h4>
            <h4 style="margin-top: -0.5cm" >QUINTA CATEGORIA</h4>
            <p style="margin-top: -0.5cm" >( Art. 45 del D.S. N° 122-94-EF, Reglamento de la Ley de IR)</p>
        </div>
    </div>

    <div>
            <h3 style="text-align: center; margin-top: -0.1cm;" >EJERCICIO {{$data['datos']['id_anho']}}</h3>
    </div>

    <div class="footer">
        <table>
            <tbody>
                <tr>
                    <td width="41%" class="border-top "><p class="text-center" style="margin-top:0mm;"> <span class="bold">{{$data['datos']['representante']}}</span> <br> Representante Legal</p></td>
                    <td width="8%" class="no-border"><p class="text-center "></p></td>
                    <td width="41%"class="border-top" ><p class="text-center" style="margin-top:0mm;"> <span class="bold">{{$data['items'][0]->nom_persona}}</span>  <br> Trabajador</p></td>
                </tr>
            </tbody>
        </table>
    </div>



  <main >
    <h5  class="text-justify" style="font-weight:normal !important;">La {{$data['datos']['empresa']}} con {{$data['datos']['ruc']}}, domiciliada en
    {{$data['datos']['direccion_legal']}},  representada por el señor {{$data['datos']['representante']}} con  {{$data['datos']['documento']}}
    </h5>
    <h3 class="text-center text-de-under ">CERTIFICA</h3>
    <h5  class="text-justify" style="font-weight:normal !important;">Que a Don(ña) {{$data['items'][0]->nom_persona}}, identificado con
    @if($data['items'][0]->id_tipodocumento == 1) DNI Nº {{$data['items'][0]->num_documento}}
    @elseif($data['items'][0]->id_tipodocumento == 4) CarEx Nº {{$data['items'][0]->num_documento}}
    @elseif($data['items'][0]->id_tipodocumento == 7) Pass Nº {{$data['items'][0]->num_documento}} @endif,
    en calidad de Trabajador se le ha retenido el importe de: S/. {{number_format($data['items'][0]->rt_retenciones_anual,2)}} como pago a cuenta del Impuesto a la Renta correspondiente al Ejercicio
        gravable {{$data['datos']['id_anho']}}, calculado en base a las siguientes rentas:
    </h5>
    <br>
    <table style="font-size: 0.80rem; margin-top: 0.2cm;">
        <thead>
            <tr>
            <td width="5%" class="border-bottom"  >1.</td>
            <td  width="90%" colspan="3" class=" border-bottom bold" >
                RENTA BRUTA
            </td>
            <td width="5%"  class="text-right border-bottom"></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">a.- Sueldos o Salarios</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->a_basico_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">b.- Gratificaciones</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->b_greatif_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>REMUN_VARIABLE
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">c.- Gratificaciones Extraordinarias</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->c_greatifextra_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">d.- Bonificaciones, Asignaciones</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->d_bonifasign_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">e.- Otros conceptos remunerativos</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->e_otrconcepremun_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">f.- Asignación Familiar</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->f_asignfam_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">g.- Horas Extras</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->g_horasextras_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">h.- Remuneraciones Empresas Anteriores</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->h_remempresasant_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">i.- Vacaciones</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->i_vacaciones_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">j.- Prestaciones alimentarias</td>
                <td width="5%" class="text-right border-bottom bold">S/.</td>
                <td width="15%"  class="text-right border-bottom bold">{{number_format($data['items'][0]->j_presalim_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border bold">TOTAL RENTA BRUTA</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->total, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
        </tbody>
    </table>
    <br>
    <table style="font-size: 0.80rem; margin-top: 0.7cm;">
        <thead>
            <tr>
            <td width="5%" class="border-bottom"  >2.-</td>
            <td  width="90%" colspan="3" class="border-bottom bold" >
                DEDUCCIONES DE LA RENTA DE 5TA CATEGORIA
            </td>
            <td width="5%"  class="text-right border-bottom"></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">7 Unidades Impositivas Tributarias (UIT)</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->deduccion_7uit,2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>

        </tbody>
    </table>
    <br>
    <table style="font-size: 0.80rem; margin-top: -0.2cm;">
        <thead>
            <tr>
                <td width="5%" class="no-border"  ></td>
                <td  width="90%" colspan="3" class="no-border bold" >
                </td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="5%" class="no-border"  >3.-</td>
                <td  width="70%"  class="no-border bold" >
                    RENTA NETA
                </td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->descto_limite, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
        </tbody>
    </table>
    <br>
    <table style="font-size: 0.80rem; margin-top: -0.2cm;">
        <thead>
            <tr>
            <td width="5%" class="border-bottom"  >4.-</td>
            <td  width="95%" colspan="3" class="border-bottom bold" >IMPUESTO A LA RENTA</td>
            <td width="5%"  class="text-right border-bottom"></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="5%" class="text-center no-border "></td>
                <td width="70%"  class="no-border">Calculo en función de la escala vigente</td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->rt_retenciones_anual, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>

        </tbody>
    </table>
    <br>
    <table style="font-size: 0.80rem; margin-top: 0.2cm;">
        <thead>
            <tr>
                <td width="5%" class="no-border"  ></td>
                <td  width="90%" colspan="3" class="no-border bold" >
                </td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="5%" class="no-border"  >5.-</td>
                <td  width="70%"  class=" no-border bold" >
                    (-) TOTAL RETENCIONES EFECTUADAS
                </td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">{{number_format($data['items'][0]->dif_ajust_dic, 2)}}</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
        </tbody>
    </table>
    <br>
    <table style="font-size: 0.80rem; margin-top: 0.2cm;">
        <thead>
            <tr>
                <td width="5%" class="no-border"  ></td>
                <td  width="90%" colspan="3" class="no-border bold" >
                </td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="5%" class="no-border"  >5.-</td>
                <td  width="70%"  class=" no-border bold" >
                    SALDO A REGULARIZAR O SALDO A FAVOR
                </td>
                <td width="5%" class="text-right no-border bold">S/.</td>
                <td width="15%"  class="text-right no-border bold">-</td>
                <td width="5%"  class="text-right no-border"></td>
            </tr>
        </tbody>
    </table>

    <h5> {{$data['datos']['nom_ciudad']}}, 31 de diciembre del {{$data['datos']['id_anho']}}</h5>
  </main>
</body>
</html>
