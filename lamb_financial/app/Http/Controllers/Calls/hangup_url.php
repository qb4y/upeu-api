<?php

function fnConnectplivo(){
$cn= mysql_connect ("localhost","syscall","R3n4w4r3Call");
if(!$cn) {return 0;}
$db=mysql_select_db("syscall",$cn);
if(!$db) {return 0;}
return $cn;
}
//try {
//$_REQUEST[''];
//include "init.php";
//include $apps_path['libs']."/function.php";
$CallUUID = trim($_REQUEST['CallUUID']);
$From = trim($_REQUEST['From']);
$To = trim($_REQUEST['To']);
$CallStatus = trim($_REQUEST['CallStatus']);
$Direction = trim($_REQUEST['Direction']);
$ALegUUID = trim($_REQUEST['ALegUUID']);
$ALegRequestUUID = trim($_REQUEST['ALegRequestUUID']);
$HangupCause = trim($_REQUEST['HangupCause']);
//$Duration = trim($_REQUEST['Duration']);
$Duration = trim($_REQUEST['variable_duration']);
$BillDuration = trim($_REQUEST['BillDuration']);
$RequestUUID = trim($_REQUEST['RequestUUID']);
$TotalCost = trim($_REQUEST['TotalCost']);
$BillRate = trim($_REQUEST['BillRate']);
$StartTime = trim($_REQUEST['StartTime']);
$EndTime = trim($_REQUEST['EndTime']);



switch ($HangupCause)
{
case 'RECOVERY_ON_TIMER_EXPIRE':
                                $CallStatus='failed';
                                //$CallStatus='completed';
                                break;
case 'USER_BUSY':
                                $CallStatus='busy';
				//$CallStatus='completed';
                                break;
case 'NO_ANSWER':
				//$CallStatus='completed';
                                $CallStatus='no-answer';
                                break;
case 'NO_USER_RESPONSE':
                                //$CallStatus='completed';
                                $CallStatus='no-answer';
                                break;

case 'UNALLOCATED_NUMBER':
                                $CallStatus='failed';
                                //$CallStatus='completed';
                                break;
case 'INVALID_NUMBER_FORMAT':
                                $CallStatus='failed';
				//$CallStatus='completed';
                                break;
case 'CALL_REJECTED':
                                $CallStatus='no-answer';
				//$CallStatus='completed';
                                break;
case 'NORMAL_CIRCUIT_CONGESTION':
                                $CallStatus='no-answer';
				//$CallStatus='completed';
                                break;
case 'SERVICE_NOT_IMPLEMENTED':
                                $CallStatus='failed';
                                //$CallStatus='completed';
                                break;
case 'NORMAL_TEMPORARY_FAILURE':
                                $CallStatus='busy';
                                //$CallStatus='completed';
                                break;
case 'ALLOTTED_TIMEOUT':
                                $CallStatus='no-answer';
                                //$CallStatus='completed';
                                break;
case 'GATEWAY_DOWN':
                                $CallStatus='no-answer';
                                //$CallStatus='completed';
                                break;
case 'ORIGINATOR_CANCEL':
                                $CallStatus='no-answer';
                                //$CallStatus='completed';
                                break;

//default:			
//				$CallStatus='no-answer';
                                //$CallStatus='completed';
                                //break;


}

$cn=fnConnectplivo();

$db_query = "UPDATE mensajesonline_tblCALLOutgoing set calluuid='$CallUUID',sender='$From',callstatus='$CallStatus',direction='$Direction',aleguuid='$ALegUUID',alegrequestuuid='$ALegRequestUUID',hangupcause='$HangupCause',duration='$Duration',billduration='$BillDuration',totalcost='$TotalCost',billrate='$BillRate',starttime='$StartTime',endtime='$EndTime' WHERE request_uuid='$RequestUUID';";

if ($db_result = mysql_query($db_query,$cn))
{
echo "Saved > $CallUUID";
}
else {
echo "Error with DB query";
echo "";
}
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
$db_query_syscall_get_callid = "select calllog_id from mensajesonline_tblCALLOutgoing where request_uuid='$RequestUUID';";
//$debug=fopen("debug_statusq.txt", "w");
//fwrite($debug, $db_query_syscall_get_callid);
//fclose($debug);

if ( $db_get_callid_result = mysql_query($db_query_syscall_get_callid,$cn )){
$callid_get = mysql_fetch_array($db_get_callid_result);
$callid = $callid_get['calllog_id'];
$db_query_syscall = "UPDATE llamada_contactos set estatus_api = '$status' where id_sms='$callid'";
//$debug=fopen("debug_status.txt", "w");
//fwrite($debug, $db_query_syscall);
//fclose($debug);
$db_result_syscall = mysql_query($db_query_syscall,$cn);
}
//} catch (Exception $e) {
//$f = fopen("file_pc21.txt", "a");
//        fwrite($f, $db_query);
//	fwrite($f, $db_result);
//	fwrite($f, "\n");
//        fclose($f);
//echo $e->getMessage();
//}
?>
