<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
	<title>Nuevo Pedido</title>
        <style>
            *{margin:0;padding:0;}:focus,:active {outline:0}ul,ol{list-style:none}h1,h2,h3,h4,h5,h6,pre,code {font-weight: normal; color: #000; font-size:1em;}a img{border:0} 
            body { font: .84em Arial, Sans-Serif; background: #fff; color: #000; }

            a { text-decoration: none; color: #2D648A; }
            a:hover, a:focus { color: #000; }
            p  { margin: 0 0 15px; line-height: 1.6em; }
            .x { clear: both; }
            .line { clear: both; padding: 15px 0 0; border-bottom: 1px solid #ddd; margin: 0 0 35px; }

            /* headings */
            h1 { font-size: 2em; font-weight: bolder; letter-spacing: -2px; margin: 0 0 14px; line-height: 1.3em; color: #333; }
            h1 span { font-size: 1.84em; color: #B00000; }
            h2 { font-size: 1.1em; margin: 0 0 10px; padding: 3px 0 5px; } 
            h3 { font-size: 1.6em; padding: 0 0 7px; }
            h4 { font-size: 1.1em; margin: 0 0 10px; padding: 3px 0 5px; } 
            h5 { margin: 0 0 10px; font-size: 1.1em; }

            #content { margin: 10px;  }

            /* main menu */
            #menu { margin: 0 0 50px; }
            #menu li  { display: inline; }
            #menu li a { float: left; text-transform: uppercase; font-size: .9em; color: #000; font-weight: bold; margin: 0 25px 0 0; }
            #menu li a:hover { color: #2D648A; }

            #pitch { border-bottom: 1px solid #ddd; margin: 0 0 40px; padding: 30px 400px 15px 0; }
            #pitch p { font-size: 1.04em; }

            /* columns */
            .col { float: left; width: 286px; margin: 0 35px 40px 0; }
            .col h3 { border-left: 4px solid #B00000; padding: 0 0 5px 12px; }
            .col a { font-weight: bold; }
            .col p { margin: 0 0 10px; }
            .ft { border-left: 1px solid #ddd; padding: 8px 0 0 15px; }
            .last { margin-right: 0; }

            /* news */
            .date { float: left; font-size: .8em; width: 25px; padding: 5px 0 0; margin: 0 0 3px; color: #999; }
            .date span { font-size: 1.5em; }
            .news { float: right; width: 250px; }
            .col h4 { padding: 0 0 0 35px; }

            /* section line */
            .section { clear: both; border-top: 1px solid #ddd; margin: 0 0 30px; }
            .section p { position: relative; margin: -8px 0 0 27px; padding: 0 8px; font-size: .74em; background: #fff; float: left; } 

            /* directory */
            #slider { float: left; width: 18px; height: 249px; border: 1px solid #ddd;  }
            #slider:hover { background-color: #f4f4f4; }
            #directory { float: right; width: 901px; }
            .fourth { float: left; width: 196px; padding: 8px; height: 100px; margin: 0 15px 15px 0; border: 1px solid #ddd; font-size: .84em; }
            .fourth p { margin: 0; }
            .fourth.last { margin: 0; }

            /* updates */
            #updates { float: right; border: 1px solid #ddd; height: 105px; padding: 20px 10px 0; width: 266px; }
            #updates p { font-size: .84em; }
            #updates h5 { margin: 0 0 15px; }
            label { display: block; font-size: .8em; color: #999; text-transform: uppercase; }
            input.textfield { padding: 4px; border: 1px solid #ccc; width: 200px; }
            input.submit { background: #fff; border: 0; font-weight: bold; font-size: .94em; }

            /* footer */
            #footer { clear: both; font-size: .9em; padding: 25px 0 10px; border-top: 1px solid #ddd; }
            #footer a { margin: 0 15px 0 0; border-bottom: 1px dotted #ccc; }
            #links { float: right; }
        </style>
</head>

<body>
    <div id="content">
        <div> 
                <BR/>
                <h3><span>UNIVERSIDAD PERUANA UNIÓN</span></h3>
        </div>
        <div class="section"></div>
        <div > 
        <p>
        Estimado: {{$nombres}} identificado con el documento {{$num_documento}}. <br>
        Colaborador del área de {{$area}}.<br/>
        En el puesto de {{$puesto}}.
        <br/><br/>
        Usted firmo su salida de vacaciones el dia {{$fecha_confirmacion_salida}}. Donde confirmo y aprobo su salida<br>
        en el periodo {{$fecha_ini}} al {{$fecha_fin}}.<br>
        mediante este correo le confirmamos que su salida fue aprobada por su jefe inmediato. <br>
        Esperamos que disfrute de sus vacaciones de {{$dias}} dias.<br><br>
        </p>
        </div>
        <div > 
        <br/>
        <p><b>Nota:</b> Por favor, no respondas a este correo electrónico</p>
        </div>

        <div id="footer">
                <p>Dirección de Talento Humano. (DTH)<br/>Universidad Peruana Unión</p><br><br>
                <p>Se adjunto su papeleta de salida</p>
        </div>
</div>	
</body>
</html>
