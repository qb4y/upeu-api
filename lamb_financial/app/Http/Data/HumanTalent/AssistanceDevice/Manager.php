<?php
namespace App\Http\Data\HumanTalent\AssistanceDevice;

use Illuminate\Support\Facades\DB;

class Manager{



	// VALIDA SI EL POLIGONO TIENE RESTRICCION
	protected static function haveRestriction($id_mapa) {

    	return DB::table('APS_MAPA_POLIGONO_ENABLE_DAY')
    	->select(DB::raw("(CASE WHEN count(APS_MAPA_POLIGONO_ENABLE_DAY.ID_MAPA_POLIGONO_ENABLE_DAY) > 0 THEN 'true' ELSE 'false' END ) as have_restriction,
       (CASE WHEN count(CASE WHEN APS_MAPA_POLIGONO_ENABLE_DAY.RESTRINGE_HORA = 1 THEN 1 END) > 0 THEN 'true' ELSE 'false' END ) as have_restriction_time"))
    	->where('APS_MAPA_POLIGONO_ENABLE_DAY.ID_MAPA','=', $id_mapa)
    	->where('APS_MAPA_POLIGONO_ENABLE_DAY.ESTADO','=', 1)
    	->first();

    }


	// DIA Y HORA HABILITADO PARA POLIGONO
    protected static function restringeDiaHora($id_mapa) {

    	return DB::table('APS_MAPA_POLIGONO_ENABLE_DAY')
    	->whereRaw("TO_CHAR(SYSDATE, 'HH24:MI') >= to_char(to_date(APS_MAPA_POLIGONO_ENABLE_DAY.HORA_INICIO, 'HH24:MI'), 'HH24:MI')
    	 and TO_CHAR(SYSDATE, 'HH24:MI') <= to_char(to_date(APS_MAPA_POLIGONO_ENABLE_DAY.HORA_FIN, 'HH24:MI'), 'HH24:MI')")
    	->whereRaw("TO_NUMBER(APS_MAPA_POLIGONO_ENABLE_DAY.ID_DIA) = TO_NUMBER(TO_CHAR(SYSDATE, 'd'))")
    	->where('APS_MAPA_POLIGONO_ENABLE_DAY.ID_MAPA','=', $id_mapa)
		->where('APS_MAPA_POLIGONO_ENABLE_DAY.ESTADO','=', 1)
		->exists();		        
    }


	// DIA HABILITADO PARA POLIGONO
	protected static function restringeDia($id_mapa){

		return DB::table('APS_MAPA_POLIGONO_ENABLE_DAY')
		->whereRaw("TO_NUMBER(APS_MAPA_POLIGONO_ENABLE_DAY.ID_DIA) = TO_NUMBER(TO_CHAR(SYSDATE, 'd'))")
		->where('APS_MAPA_POLIGONO_ENABLE_DAY.ID_MAPA','=', $id_mapa)
		->where('APS_MAPA_POLIGONO_ENABLE_DAY.ESTADO','=', 1)
		->exists();

	}









	protected static function validPolygon($id_mapa) {

		$data = ['isValid' => false, 'message' => null];

		$valPol = self::haveRestriction($id_mapa);

		if ($valPol->have_restriction == 'true') {

			$isValidDay = self::restringeDia($id_mapa);

			if ($isValidDay) {

				if ($valPol->have_restriction_time == 'true') {

					$isValidDayTime = self::restringeDiaHora($id_mapa);

					if ($isValidDayTime) {

						$data['isValid'] = true;
						$data['message'] = 'dia y hora correcto !!';
						
					} else {

						$data['message'] = 'El área de marcación no esta disponible en este horario.';
					}

				} else {

					$data['isValid'] = true;
					$data['message'] = 'dia correcto !!';
				}

			} else {

				$data['message'] = "El área de marcación no esta disponible para este día.";
			}

		} else {
			$data['isValid'] = true;
			$data['message'] = 'polígono sin restricción';
		}

		return $data;
        
    }


    


	protected static function getPerson($dni) {

		// Obtiene el codigo marcación por dni

        return DB::connection('siscop')
        ->table('PERSONAL')
        ->select(
            DB::raw('TO_NUMBER(PERSONAL.FOTOCHECK) AS COD'),
            'PERSONAL.FOTOCHECK AS CODIGO',
            'PERSONAL.IDPERSONAL')
        ->where('PERSONAL.NDOCUMENTO','=',$dni)
        ->where('PERSONAL.ACTIVO','=',1)
        ->first();

    }


    


	protected static function canInsertAssist($person){

		/* Dependencia getPerson [idpersonal, codigo]
		Valida si puede marcar asistencia */

		$minutos = 45;

		return DB::connection('siscop')
		->table('ASISTENCIA')
		->select(DB::raw("CASE 
            WHEN (SYSDATE >= MAX(FECHAHORA)+".$minutos."/1440) THEN 'true'
            WHEN COUNT(FECHAHORA) = 0 THEN 'true'
            ELSE 'false'
            END AS status"))
		->where('ASISTENCIA.IDPERSONAL', '=', $person->idpersonal)
		->where('ASISTENCIA.FOTOCHECK', '=', $person->codigo)
		->whereRaw("TO_CHAR(ASISTENCIA.FECHAHORA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY')")
		->first();
	}


	protected static function getDevice($uuid){
		return DB::table('APS_USER_DEVICE')
        ->select(
            'APS_USER_DEVICE.ID_PERSONA',
            'APS_USER_DEVICE.ID_USERDEVICE',
            'APS_USER_DEVICE.UUID')
        ->where('APS_USER_DEVICE.UUID','=',$uuid)
        ->where('APS_USER_DEVICE.STATE','=',1)
        ->first();
	}


	protected static function getUserDevicesUUID($id_persona){
		return DB::table('APS_USER_DEVICE')        
        ->select('APS_USER_DEVICE.UUID')
        ->where('APS_USER_DEVICE.ID_PERSONA','=',$id_persona)
        ->where('APS_USER_DEVICE.STATE','=',1)
        ->get()
        ->map(function ($item, $key) { return $item->uuid;})
        ->toArray();
    }


	protected static function existsDevice($device, $devices, $id_persona_s){

		/* Logica que valida cuantos dispositivos una persona 
		puede usar para el control de su asistencia
		
		return 

			insertDevice: Indica si puede asociar un dispositivo
			insertAssistance: Indica si puede realizar su control de asistencia
			message: Muestra la indicacion de lógica
		
		*/

		$data['insertDevice'] = false;
		$data['insertAssistance'] = false;
		$data['deviceLost'] = false;
		

		if ($device && $device->id_persona == $id_persona_s) {

			if (in_array($device->uuid, $devices) && count($devices) == 1) {

				$data['insertAssistance'] = true;
				$data['message'] = 'Dispositivo válido y puede realizar su control de asistencia.';				

			} else {
				$data['message'] = 'Usted no puede tener más de un dispositivo asociado a su cuenta.';
			}
			

        } elseif ($device == null && count($devices) == 0) {
        	$data['insertDevice'] = true;
        	$data['message'] = '¿Desea asociar éste dispositivo a su cuenta?, recuerde solo puede usar un solo dispositivo para realizar su control de asistencia.';

        } elseif ($device && count($devices) == 0) {

        	$data['message'] = 'El dispositivo ya esta asociado ¿Desea asociar éste dispositivo a su cuenta?, Comuníquese con DIGETI.';

        } else {
        	$data['deviceLost'] = true;

        	if (count($devices) >= 1) {

        		$data['message'] = 'Usted ya tiene asociado un dispositivo, puede cambiar de dispositivo solicitando a DIGETI.';

        	} else {

        		$data['message'] = 'El dispositivo ya esta asociado, puede cambiar de dispositivo solicitando a DIGETI.';

        	}

        	
        }

        return $data; 

        
	}


	protected static function saveZKMarcaciones($iidusuario, $icodusuario, $now) {
		$data = [
		    'iidterminal' => '12',
            'inumero' => '4',
            'iidusuario' => $iidusuario,
            'icodusuario' => $icodusuario,
            'imodoverificacion' => '1',
            'imodoentradasalida' => '0',
            'fechahora' => $now,
            'grabadook' => '1',
            'registro' => $now,
            'cardnumber' => '0',
            'transferido' => '0',
            'intworkcode' => '0'
        ];

        return DB::transaction(function() use($data) {
        	DB::connection('siscop')->table('ZKMarcaciones')->insert($data);
        	return DB::connection('siscop')->getSequence()->currentValue('SQ_ZKMARCACIONES');

        });

	}

	protected static function saveAsistencia($idpersonal, $codigo, $now_date, $now) {
		$data = [
			'idpersonal' => $idpersonal,
            'fecha' => $now_date,
            'fechahora' => $now,
            'tipoingreso' => '1',
            'tipomarcacion' => '0',
            'num_marcador' => '37',
            'fotocheck' => $codigo,
            'fecharegistro' => $now
        ];

        return DB::transaction(function() use($data) {
        	DB::connection('siscop')->table('ASISTENCIA')->insert($data);
        	return DB::connection('siscop')->getSequence()->currentValue('SQ_ASISTENCIA');
        });

	}


	




	protected static function InsertAssist($person, $extra) {

		/*
		Dependencia getPerson
		Guarda control de asistencia desde movil
		*/

		$id_marcacion = null;
        $id_asistencia = null;
        $insertStatus = false;

        $user = DB::connection('siscop')
        ->table('ZKUSUARIOS')
        ->select('ZKUSUARIOS.IIDUSUARIO','ZKUSUARIOS.ICODUSUARIO')
        ->where('ZKUSUARIOS.ICODUSUARIO','=',$person->cod)
        ->first();

        if ($user) {

        	$now = DB::raw('sysdate');
        	$now_date = DB::raw("to_char(sysdate,'YYYY-MM-DD')");
        	$id_marcacion = self::saveZKMarcaciones($user->iidusuario, $user->icodusuario, $now);
        	$id_asistencia = self::saveAsistencia($person->idpersonal, $person->codigo, $now_date, $now);

        	if ($id_marcacion && $id_asistencia) {
        		$extra['id_marcacion'] = $id_marcacion;
        		$extra['id_asistencia'] = $id_asistencia;
        		$extra['fecha'] = $now;
        		$extra['tipo'] = 'M';
        		DB::table('APS_ASISTENCIA_POLIGONO')->insert($extra);
        		$insertStatus = true;
	        }
            

        }

        return $insertStatus;
    }



    protected static function validateInsertAssist($dni, $extra){

    	// Validaciones para realizar el control de asistencia via movil
    	// En esta funcion debe agregarse mas validacion si en caso aumenta

    	$data['insertAssistance'] = false;

		$person = self::getPerson($dni);

		if ($person) {

			$valPol = self::validPolygon($extra['id_mapa']);

			if ($valPol['isValid']) {

				$status = self::canInsertAssist($person);

				if ($status->status == 'true') {

					$data['insertAssistance'] = true;
					$data['message'] = 'Puede realizar su asistencia.';

				} else {
					$data['message'] = 'Ya tiene registrado su asistencia, espere su siguiente horario.';
				}
			} else {

				$data['message'] = $valPol['message'];
			}
			
		} else {
			$data['message'] = 'El documento de identidad es inválido.';
		}

		return $data;
	}


	protected static function purgeExtra($extra, $uuid){

		// tiene que estar registrado device

		$device = DB::table('APS_USER_DEVICE')
		->select('APS_USER_DEVICE.ID_USERDEVICE')
		->where('APS_USER_DEVICE.UUID','=',$uuid)
		->first();
		$extra['id_userdevice'] = $device->id_userdevice;
		return $extra;
	}







	// Interface
	public static function validateDevice($id_persona, $uuid){

		// VALIDA INSERT DE DISPOSITIVO

		$device = self::getDevice($uuid);
		$devices = self::getUserDevicesUUID($id_persona);
		return self::existsDevice($device, $devices, $id_persona);
	}

	
	// Interface
	public static function saveAssist($dni, $extra, $uuid){

		/*
		Requisitos:

		ValidateDevice() ctrl  validado "comprueba que el dispositivo es correcto"
		*/

		$validate = self::validateInsertAssist($dni, $extra);

		if ($validate['insertAssistance']) {

			$person = self::getPerson($dni);
			$extra = self::purgeExtra($extra, $uuid);
			$status = self::InsertAssist($person, $extra);

			if ($status) {
				$validate['message'] = 'Se registro correctamente su asistencia.';				
			}else{
				$validate['insertAssistance'] = false;
				$validate['message'] = 'Existe un problema al registrar su asistencia.';
			}

		}

		return $validate;


	}


	// Interface
	public static function resetDevice($id_persona, $uuid){

		// restaura asignando un dispositivo a una persona
		// id_persona: id de la persona que inicio sesion
		// uuid: id unico de celular actual

		$device = DB::table('APS_USER_DEVICE')
		->where('APS_USER_DEVICE.UUID','=',$uuid)
		->exists();

		if ($device) {

			DB::table('APS_USER_DEVICE')
			->where('APS_USER_DEVICE.ID_PERSONA','=',$id_persona)
			->update([
				'APS_USER_DEVICE.STATE'=>0,
				'APS_USER_DEVICE.CAN_RESET_TOUCH_ID'=>0,
				'APS_USER_DEVICE.RE_ASIGN'=>0]); 

			DB::table('APS_USER_DEVICE')
			->where('APS_USER_DEVICE.UUID','=',$uuid)
			->where('APS_USER_DEVICE.ID_USERDEVICE','=',
				DB::raw("(SELECT MAX(UD.ID_USERDEVICE) FROM APS_USER_DEVICE UD where UD.UUID='".$uuid."')"))
			->update([
				'APS_USER_DEVICE.ID_PERSONA'=>$id_persona,
				'APS_USER_DEVICE.STATE'=>1]);
			
		} else {

			DB::table('APS_USER_DEVICE')
			->where('APS_USER_DEVICE.ID_PERSONA','=',$id_persona)
			->update([
				'APS_USER_DEVICE.STATE'=>0,
				'APS_USER_DEVICE.CAN_RESET_TOUCH_ID'=>0,
				'APS_USER_DEVICE.RE_ASIGN'=>0]);

		}

		

	}





	 









}

?>