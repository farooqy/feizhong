<?php session_start();
$time = time();
error_reporting(E_ALL & ~E_NOTICE);
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");
if(isset($_POST["action"]) && isset($_POST["is_loggedIn"]) && 
  isset($_POST["token"]) && isset($_POST["host"]) )
{
	
	$isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
	
	$action = $_POST["action"];
	$isLoggedIn = $_POST["is_loggedIn"];
	$hostToken = $_POST["token"];
	$hostType = $_POST["host"];
	
	if(count($_POST) !== 19)
		$errorMessage = "Incomplete request params. Contact support for the error";
	else if ($action !== "tinView")
		$errorMessage = "Invalid view type";
	else if(empty($isLoggedIn) || empty($hostType))
		$errorMessage = "Missing data for validation";
	else
	{
		$phoneSerialNum = $_POST["phone_serial_number"];
		$phoneModelNum = $_POST["phone_model_number"];
		$phoneIdNumber = $_POST["phone_id_number"];
		$phoneManufacturer = $_POST["phone_manufacturer"];
		$phoneBrand = $_POST["phone_brand"];
		$phoneType = $_POST["phone_type"];
		$phoneUser = $_POST["phone_user"];
		$phoneBase = $_POST["phone_base"];
		$phoneSdkVersion = $_POST["phone_sdk_version"];
		$phoneHost = $_POST["phone_host"];
		$phoneFingerPrint = $_POST["phone_fingerprint"];
		$phoneRelease = $_POST["phone_release"];
		$phoneIpAddr = $_POST["phone_ip_address"];
		$phoneMacAddr = $_POST["phone_mac_address"];
		$phoneServerIpAddr = $_SERVER["REMOTE_ADDR"];
		$startPosition = $_POST["startPosition"];
		$endPosition = $startPosition+5;
		$requestTime = time();
		
		if($hostType === "company" || $hostType === "user" || $hostType === "guest")
		{	
			$table = "feedRequests";
			$fields = "phone_serial_number, phone_model_number, phone_id_number, phone_manufacturer, 
			phone_brand, phone_type,  phone_user, phone_base, phone_sdk_version, phone_host, phone_fingerprint,
			phone_release, feed_isLoggedIn, feed_userType, feed_userToken , feed_userIp_server ,
			feed_userIp_phone, feed_userMac, feed_reqTime";
			
			$values = "'$phoneSerialNum', '$phoneModelNum', '$phoneIdNumber', '$phoneManufacturer', 
			'$phoneBrand', '$phoneType', '$phoneUser', '$phoneBase','$phoneSdkVersion', '$phoneHost', '$phoneFingerPrint',
			'$phoneRelease', '$isLoggedIn', '$hostType','$hostToken','$phoneServerIpAddr',
			'$phoneIpAddr', '$phoneMacAddr', $requestTime";
			
			require_once($root."classes/SuperClass.php");
			$SuperClass = new Super_Class();
			$isFed = $SuperClass->Super_Insert($table, $fields, $values);
			if($isFed === true)
			{
			    $table = array("companydata", "comp_type");
			    $fields = "comp_id, `companydata`.`comp_token` ,comp_name, comp_logo, `comp_type`.`comp_type`, `comp_type`.`comp_subtype`";
			    $condition = "`comp_type`.`comp_token` = `companydata`.`comp_token`";
			    $sortby = "comp_id DESC LIMIT $startPosition, $endPosition";
			    
			    $compData = $SuperClass->Super_Get($fields, $table, $condition, $sortby);
			    
			    if($compData === false)
			        $errorMessage = $Super_Class->Get_Message();
			    else if(is_array($compData) === false)
			        $errorMessage = "Company data returned unknown record type";
			    else
			    {
			        $time2 = time() - $time;
			        foreach($compData as $key => $compVal)
			        {
			            $urls = explode("/",$compVal["comp_logo"]);
                        if($urls === false)
                            $errorMessage = "The retrieved company logo is empty";
                        else if(count($urls) <= 1)
                            $errorMessage = "The retrieved company logo pattern is not correct";
                        else 
                        {
                            $logoImage = $root."uploads/".$urls[count($urls)-1];
                            $imageFile = base64_encode(file_get_contents($logoImage));
                            $imageHash = Get_Hash($imageFile);
                            $compData[$key]["logo_val"] = $imageFile;
                            $compData[$key]["logo_hash"] = $imageHash;
                        }
			        }
			        $isSuccess = true;
    				$successMessage = "success";
			    }
    				
				
			}
			else
				$errorMessage = $SuperClass->Get_Message();
		}
		else
			$errorMessage = "Unknown host";
			
	}
	$Titime = time() - $time;
	echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage"=>$successMessage,
        "compData" => $compData,
        "time" => $Titime,
        "time2" => $time2
        
        ));
    exit(0);
}
else
{
    echo json_encode(array(
        "isSuccess" => false,
        "errorMessage"=> "Incomplete request content",
        "successMessage"=>null,
        ));
    exit(0);
	
}

?>