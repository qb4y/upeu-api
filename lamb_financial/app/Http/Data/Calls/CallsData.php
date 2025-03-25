<?php
namespace App\Http\Data\Calls;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class CallsData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public static function  hangup($request){

    	$CallUUID = trim($request->CallUUID);
		$From = trim($request->From);
		$To = trim($request->To);
		$CallStatus = trim($request->CallStatus);
		$Direction = trim($request->Direction);
		$ALegUUID = trim($request->ALegUUID);
		$ALegRequestUUID = trim($request->ALegRequestUUID);
		$HangupCause = trim($$request->HangupCause);
		//$Duration = trim($_REQUEST['Duration']);
		$Duration = trim($request->variable_duration);
		$BillDuration = trim($request->BillDuration);
		$RequestUUID = trim($request->RequestUUID);
		$TotalCost = trim($request->TotalCost);
		$BillRate = trim($request->BillRate);
		$StartTime = trim($request->StartTime);
		$EndTime = trim($request->EndTime);

		switch ($HangupCause){
			case 'RECOVERY_ON_TIMER_EXPIRE':
		                                $CallStatus='failed';
		                                
		                                break;
			case 'USER_BUSY':
			                            $CallStatus='busy';
							
			                            break;
			case 'NO_ANSWER':
							
			                            $CallStatus='no-answer';
			                            break;
			case 'NO_USER_RESPONSE':
			                            
			                            $CallStatus='no-answer';
			                            break;

			case 'UNALLOCATED_NUMBER':
			                            $CallStatus='failed';
			                                
			                            break;
			case 'INVALID_NUMBER_FORMAT':
			                            $CallStatus='failed';
			                            break;
			case 'CALL_REJECTED':
			                            $CallStatus='no-answer';

			                            break;
			case 'NORMAL_CIRCUIT_CONGESTION':
			                            $CallStatus='no-answer';

			                            break;
			case 'SERVICE_NOT_IMPLEMENTED':
			                            $CallStatus='failed';
			                            break;
			case 'NORMAL_TEMPORARY_FAILURE':
			                            $CallStatus='busy';
			                            break;
			case 'ALLOTTED_TIMEOUT':
			                            $CallStatus='no-answer';
			                            break;
			case 'GATEWAY_DOWN':
			                            $CallStatus='no-answer';
			                            break;
			case 'ORIGINATOR_CANCEL':
			                            $CallStatus='no-answer';
			                            break;

		}
		$db_query = "UPDATE mensajesonline_tblCALLOutgoing set 
		sender='$From'
		WHERE request_uuid='$RequestUUID';";

		$query = "UPDATE FIN_CALL SET 
                    CALL_UUID = '".$CallUUID."',
                    CALL_STATUS = '".$CallStatus."',
                    CALL_DIRECTION = '".$Direction."',
                    CALL_ALEGUUID = '".$ALegUUID."',
                    CALL_ALEGREQUEST = '".$ALegRequestUUID."',
                    CALL_HANGUPCAUSE = '".$HangupCause."',
                    CALL_DURATION = ".$Duration.",
                    CALL_BILLDURATION = '".$BillDuration."',
                    CALL_COST = ".$TotalCost.",
                    CALL_BILLRATE = '".$BillRate."',
                    CALL_STARTTIME = '".$StartTime."',
                    CALL_ENDTIME = '".$EndTime."'
              WHERE ID_PRESUPUESTO = ".$id_presupuesto;
        DB::update($query);

        ID_CALL	NUMBER(38,0)
ID_CLIENTE	NUMBER
ID_FINANCISTA	NUMBER
ID_TIPOEVIDENCIA	NUMBER(38,0)
NUMBER_FROM	VARCHAR2(30 BYTE)
NUMBER_TO	VARCHAR2(30 BYTE)

		switch ($CallStatus) 
			{

			case 'failed':
						$status='3';
						break;

			case 'no-answer':
						$status='6';
						break;

			case 'busy':		
						$status='6';
						break;

			case 'completed':	
						$status='4';
						break;
		}

		//$db_query_syscall_get_callid = "select calllog_id from mensajesonline_tblCALLOutgoing where request_uuid='$RequestUUID';

		$objeto = DB::table('fin_call')->select('calllog_id')->where('id_call',$id_call)->first();

		$callid = $objeto->calllog_id;

		$query = "UPDATE llamada_contactos SET 
                    estatus_api = '".$status."'
              WHERE id_sms = ".$callid;
        DB::update($query);

//$db_query_syscall = "UPDATE llamada_contactos set estatus_api = '$status' where id_sms='$callid'";

		


    }
}
?>
