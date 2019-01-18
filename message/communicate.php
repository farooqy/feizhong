<?php session_start();

$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
 require_once($root."classes/functions.php");
if(isset($_POST["hostToken"]) && isset($_POST["targetToken"]) && 
isset($_POST["plainText"]) && isset($_POST["timeInSec"]) &&
isset($_POST["hostType"]) && isset($_POST["targetType"]))
{
    $errorMessage = null;
    $successMessage = null;
    $isSuccess = false;
    
    $mhost = Sanitize_String($_POST["hostToken"]);
    $mTarget = Sanitize_String($_POST["targetToken"]);
    $pText = Sanitize_String($_POST["plainText"]);
    $mType = 'text';
    $hostType = Sanitize_String($_POST["hostType"]);
    $targetType = Sanitize_String($_POST["targetType"]);
    $mTime = Validate_Int($_POST["timeInSec"]);
    if($mTime === false)
    {
        $errorMessage = "The time of the message is not valid";
    }
    else if($hostType !== "user" && $hostType !== "company")
        $errorMessage = "Invalid host type. Please contact support";
    else if($targetType !== "user" && $targetType !== "company")
        $errorMessage = "Invalid target type. Please contact support";
    else
    {
        require_once($root."classes/SuperClass.php");
       
        $Super_Class = new Super_Class();
        $mtoken = Get_Hash(time());
        $table = "messages";
        $fields = "message_host, message_target, message_content, message_time, 
        message_status, message_type, message_token, message_host_type, message_target_type";
        $values = "'$mhost', '$mTarget', '$pText', $mTime, 'sent', '$mType', '$mtoken', '$hostType', '$targetType' ";
        $isSent = $Super_Class->Super_Insert($table, $fields, $values);
        if($isSent === false)
        {
            $errorMessage = "Failed to send the message ".$Super_Class->Get_Message();
        }
        else
        {
            $isSuccess = true;
            $successMessage = "success";
        }
        
    }
    
    
       
    echo json_encode(
        array(
            "errorMessage" => $errorMessage,
            "successMessage" => $successMessage,
            "isSuccess" => $isSuccess,
            // "data" => $_POST,
            )
        );
}
else
{
    echo json_encode(
        array(
            "errorMessage" => "Incomplete data request",
            "successMessage" => null,
            "isSuccess" => false,
            "data" => $_POST,
            )
        );
}
?>