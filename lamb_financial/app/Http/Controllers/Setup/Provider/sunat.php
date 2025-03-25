<?php	
        namespace App\Http\Controllers\Setup\Provider;
        use App\Http\Controllers\Setup\Provider\curl;
	class sunat{
		var $cc;
		var $_legal=false;
		var $_trabs=false;
		function __construct( $representantes_legales=false, $cantidad_trabajadores=false )
		{
			$this->_legal = $representantes_legales;
			$this->_trabs = $cantidad_trabajadores;
			
			//$this->cc = new \Sunat\cURL();
                        $this->cc = new curl();	
			$this->cc->setReferer( "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/frameCriterioBusqueda.jsp" );
			$this->cc->useCookie( true );
			$this->cc->setCookiFileLocation( __DIR__ . "/cookie.txt" );
		}
		
		function getNumRand()
		{
			// version 1
			// $url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=random";
			// $numRand = $this->cc->send($url);
			// return $numRand;

			// version 2
			$url = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRazonSoc&razSoc=MTS";
			$numRand = 0;
			$response= $this->cc->send($url);
			if($this->cc->getHttpStatus()==200&& $response!=null) {
				$patron = '/<input type="hidden" name="numRnd" value="(.*)">/';
				$output = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
				if(isset($matches[0])) {
					$RS= utf8_encode(str_replace('"', '', ($matches[0][1])));
					$numRand= $RS;
				}
				return $numRand;
			}
			return false;
			
			// version 3
			// $numRand = $this->cc->get("https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=random");
			// if ($this->cc->getHttpStatusCode() == 200 && $numRand != "") {
			// 	return $numRand;
			// }
			// return false;


		}
		function getDataRUC( $ruc )
		{
			$numRand = $this->getNumRand();
			// dd($numRand);
			$rtn = array();
			if($ruc != "" && $numRand!=false)
			{
				$data = array(
					"nroRuc" => $ruc,
					"accion" => "consPorRuc",
					"numRnd" => $numRand
				);

				$url = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
				$Page = $this->cc->send( $url, $data );
				// dd($Page);
				//RazonSocial
				// $patron='/<input type="hidden" name="desRuc" value="(.*)">/';
				$patron = '/<td  class="bg" colspan=3>(.*)<\/td>/';
				$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if(isset($matches[0]))
				{
					$RS = utf8_encode(str_replace('"','', ($matches[0][1])));
					$RS = substr(trim($RS), 13, strlen(trim($RS)));
					$rtn = array("RUC"=>$ruc,"RazonSocial"=>trim($RS));
				}

				//Telefono
				$patron='/<td class="bgn" colspan=1>Tel&eacute;fono\(s\):<\/td>[ ]*-->\r\n<!--\t[ ]*<td class="bg" colspan=1>(.*)<\/td>/';
				$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if( isset($matches[0]) )
				{
					$rtn["Telefono"] = trim($matches[0][1]);
				}

				// Condicion Contribuyente
				// $patron='/<td class="bgn"[ ]*colspan=1[ ]*>Condici&oacute;n del Contribuyente:[ ]*<\/td>\r\n[\t]*[ ]+<td class="bg" colspan=[1|3]+>[\r\n\t[ ]+]*(.*)[\r\n\t[ ]+]*<\/td>/';
				$patron='/<td class="bgn"[ ]*colspan=1[ ]*>Condici&oacute;n:[ ]*<\/td>\r\n[\t]*[ ]+<td class="bg" colspan=[1|3]+>[\r\n\t[ ]+]*(.*)[\r\n\t[ ]+]*<\/td>/';
				$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if( isset($matches[0]) )
				{
                                    $rtn["Condicion"] = strip_tags(trim($matches[0][1]));
                                    $rtn["success"] = true;
				}

				$busca=array(
                                    "NombreComercial" 	=> "Nombre Comercial",
                                    "Tipo" 			=> "Tipo Contribuyente",
                                    "Inscripcion" 		=> "Fecha de Inscripci&oacute;n",
                                    // "Estado" 		=> "Estado del Contribuyente",
                                    "Estado" 		=> "Estado",
                                    // "Direccion" 		=> "Direcci&oacute;n del Domicilio Fiscal",
                                    "Direccion" 		=> "Domicilio Fiscal",
                                    // "SistemaEmision" 	=> "Sistema de Emisi&oacute;n de Comprobante",
                                    "SistemaEmision" 	=> "Sistema de Emisi&oacute;n Electr&oacute;nica",
                                    "ActividadExterior"	=> "Actividad de Comercio Exterior",
                                    "SistemaContabilidad" 	=> "Sistema de Contabilidad",
                                    "Oficio" 		=> "Profesi&oacute;n u Oficio",
                                    "ActividadEconomica" 	=> "Actividad\(es\) Econ&oacute;mica\(s\)",
                                    "EmisionElectronica" 	=> "Emisor electr&oacute;nico desde",
                                    // "comprobante_electronico" 	=> "Comprobantes Electr&oacute;nicos",
                                    "PLE" 			=> "Afiliado al PLE desde"                                        
				);
				foreach($busca as $i=>$v)
				{
					//$patron='/<td class="bgn"[ ]*colspan=1[ ]*>'.$v.':[ ]*<\/td>\r\n[\t]*[ ]+<td class="bg" colspan=[1|3]+>(.*)<\/td>/';
					$patron='/<td class="bgn"[ ]*colspan=1[ ]*>'.$v.':[ ]*<\/td>[ ]*\r\n[\t]*[ ]+<td class="bg" colspan=[1|3]+>(.*)<\/td>/';
                                        $output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
					if(isset($matches[0]))
					{
						$rtn[$i] = trim(utf8_encode( preg_replace( "[\s+]"," ", ($matches[0][1]) ) ) );
					}
				}
				// if( isset($rtn["comprobante_electronico"]) )
				// {
				// 		$nuevo = explode(',', $rtn["comprobante_electronico"]);
				// 		if( is_array($nuevo))
				// 		{
				// 				$rtn["comprobante_electronico"] = $nuevo;
				// 		}
				// 		else
				// 		{
				// 				$rtn["comprobante_electronico"] = array( $rtn["comprobante_electronico"]);
				// 		}
				// }
				// Actividad Economica
				$patron='/<option value="00" > (.*) - (.*) <\/option>\r\n/';
				$rpta = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if( !empty($matches) )
				{
						$ae = array();
						foreach ($matches as $key => $value) 
						{
								$ae[] = array(
										'ciiu' 	=> utf8_encode(trim($value[1])),
										'descripcion' 	=> utf8_encode(trim($value[2]))
								);
						}
						$rtn["ActividadEconomica"] = $ae;
				}
				// documentos
				$patron='/<option value="00" >(.*) (.*)<\/option>\r\n/';
				//$patron='/(.*) select name "select" (.*)\r\n/';
				$rpta = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if( !empty($matches) )
				{
						$ae = array();
						foreach ($matches as $key => $value) 
						{

										$d= utf8_encode(trim($value[0]));
										$dd = explode(">", $d);
										$dato = explode("<", $dd[1]);
										$dat = trim($dato[0]);
										$dig = substr($dat, 0, 1);
										if(!is_numeric($dig)){
												$uno = strpos($dat, 'DESDE');
												$dos = strpos($dat, 'Incorporado');
												if(($uno===false) and ($dos===false)){
														$ae[] = array(
																$d=
																'doc' 	=> $dat//utf8_encode(trim($value[0]))
																//'descripcion' 	=> utf8_encode(trim($value[2]))
														);
												}
										}

						}
						$rtn["Comprobantes"] = $ae;
				}
			}
			if( count($rtn) > 2 )
			{
				$legal = array();
				if($this->_legal)
				{
					$legal = $this->RepresentanteLegal( $ruc );
				}
				$rtn["representantes_legales"] = $legal;
				
				$trabs = array();
				if($this->_trabs)
				{
					$trabs = $this->numTrabajadores( $ruc );
				}
				$rtn["cantidad_trabajadores"] = $trabs;
				
				return $rtn;
			}
			return false;
		}
		function numTrabajadores( $ruc )
		{
			$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
			$data = array(
				"accion" 	=> "getCantTrab",
				"nroRuc" 	=> $ruc,
				"desRuc" 	=> ""
			);
			$rtn = $this->cc->send( $url, $data );
			if( $rtn!="" && $this->cc->getHttpStatus()==200 )
			{
				$patron = "/<td align='center'>(.*)-(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>/";
				$output = preg_match_all($patron, $rtn, $matches, PREG_SET_ORDER);
				if( count($matches) > 0 )
				{
					$cantidad_trabajadores = array();
					//foreach( array_reverse($matches) as $obj )
					foreach( $matches as $obj )
					{
						$cantidad_trabajadores[]=array(
							"periodo" 				=> $obj[1]."-".$obj[2],
							"anio" 					=> $obj[1],
							"mes" 					=> $obj[2],
							"total_trabajadores" 	=> $obj[3],
							"pensionista" 			=> $obj[4],
							"prestador_servicio" 	=> $obj[5]
						);
					}
					return $cantidad_trabajadores;
				}
			}
			return array();
		}
		function RepresentanteLegal( $ruc )
		{
			$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
			$data = array(
				"accion" 	=> "getRepLeg",
				"nroRuc" 	=> $ruc,
				"desRuc" 	=> ""
			);
			$rtn = $this->cc->send( $url, $data );
			if( $rtn!="" && $this->cc->getHttpStatus()==200 )
			{
				$patron = '/<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="center">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>/';
				$output = preg_match_all($patron, $rtn, $matches, PREG_SET_ORDER);
				if( count($matches) > 0 )
				{
					$representantes_legales = array();
					foreach( $matches as $obj )
					{
						$representantes_legales[]=array(
							"tipodoc" 				=> trim($obj[1]),
							"numdoc" 				=> trim($obj[2]),
							"nombre" 				=> utf8_encode(trim($obj[3])),
							"cargo" 				=> utf8_encode(trim($obj[4])),
							"desde" 				=> trim($obj[5]),
						);
					}
					return $representantes_legales;
				}
			}
			return array();
		}
		function dnitoruc($dni)
		{
			if ($dni!="" || strlen($dni) == 8)
			{
				$suma = 0;
				$hash = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
				$suma = 5; // 10[NRO_DNI]X (1*5)+(0*4)
				for( $i=2; $i<10; $i++ )
				{
					$suma += ( $dni[$i-2] * $hash[$i] ); //3,2,7,6,5,4,3,2
				}
				$entero = (int)($suma/11);

				$digito = 11 - ( $suma - $entero*11);

				if ($digito == 10)
				{
					$digito = 0;
				}
				else if ($digito == 11)
				{
					$digito = 1;
				}
				return "10".$dni.$digito;
			}
			return false;
		}
		function valid($valor) // Script SUNAT
		{
			$valor = trim($valor);
			if ( $valor )
			{
				if ( strlen($valor) == 11 ) // RUC
				{
					$suma = 0;
					$x = 6;
					for ( $i=0; $i<strlen($valor)-1; $i++ )
					{
						if ( $i == 4 )
						{
							$x = 8;
						}
						$digito = $valor[$i];
						$x--;
						if ( $i==0 )
						{
							$suma += ($digito*$x);
						}
						else
						{
							$suma += ($digito*$x);
						}
					}
					$resto = $suma % 11;
					$resto = 11 - $resto;
					if ( $resto >= 10)
					{
						$resto = $resto - 10;
					}
					if ( $resto == $valor[strlen($valor)-1] )
					{
						return true;
					}
				}
			}
			return false;
		}
		function search($ruc_dni, $inJSON = false )
		{
			if( strlen(trim($ruc_dni))==8 )
			{
				$ruc_dni = $this->dnitoruc($ruc_dni);
			}
			
			if( strlen($ruc_dni)==11 && $this->valid($ruc_dni) )
			{
				$rtn = $this->getDataRUC($ruc_dni);
				if(!$rtn){
					$rtn = [
						'success' => false,
						'msg' => 'No se ha encontrado resultados.'
					];
				}
				return $rtn;
			}
			$rtn = [
				'success' => false,
				'msg' => 'Nro de DNI no valido.'
			];
			return $rtn;
		}

		// function search( $ruc_dni, $inJSON = false )
		// {
		// 	if( strlen(trim($ruc_dni))==8 )
		// 	{
		// 		$ruc_dni = $this->dnitoruc($ruc_dni);
		// 	}
		// 	if( strlen($ruc_dni)==11 && $this->valid($ruc_dni) )
		// 	{
		// 		// $info = $this->getDataRUC($ruc_dni);
		// 		$rtn = $this->getDataRUC($ruc_dni);
        //                         //if( $rtn!=false ){
		// 		if( $rtn!=false ){
        //                             /*$rtn = array(
        //                                 "success" 	=> true,
        //                                 "result" 	=> $info
		// 							);*/
		// 							// $rtn['success']=true;
		// 		}else{
        //                             /*$rtn = array(
        //                                     "success" 	=> false,
        //                                     "msg" 		=> "No se ha encontrado resultados."
        //                             );*/
        //                             $rtn = [
        //                                 'success' => false,
        //                                 'msg' => 'No se ha encontrado resultados.'
        //                             ];
		// 		}
		// 		//return ($inJSON==true)?json_encode($rtn, JSON_PRETTY_PRINT):$rtn;
        //                         return $rtn;
		// 	}

		// 	/*$rtn = array(
		// 		"success" 	=> false,
		// 		"msg" 		=> "Nro de RUC o DNI no valido."
		// 	);*/
        //                 $rtn = [
        //                     'success' => false,
        //                     'msg' => 'Nro de DNI no valido.'
        //                 ];
		// 	//return ($inJSON==true)?json_encode($rtn, JSON_PRETTY_PRINT):$rtn;
        //                 return $rtn;
		// }
	}
