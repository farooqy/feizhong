<?php session_start();
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");

if(isset($_POST["comp_representativeEmail"]) && isset($_POST["comp_password"]) && 
isset($_POST["action"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
    $Token = null;
    $action = Sanitize_String($_POST["action"]);
    $userPass = Sanitize_String($_POST["comp_password"]);
    $userName = Sanitize_String($_POST["comp_representativeEmail"]);
    $searchField = null;
    if($action === "comp_login")
    {
        $time = time();
        $device = "UNKNOWN";
        $ip = $_SERVER["REMOTE_ADDR"];
        $table = array("companydata", "comp_type", "comp_address");
        $hashedPass = Get_Hash($userPass);
        $fields = "
        `companydata`.`comp_id`, `companydata`.`comp_name`, 
        `companydata`.`comp_representative`, `companydata`.`comp_rep_email`,
        `companydata`.`comp_ceo`,`companydata`.`comp_logo`, `companydata`.`comp_license`,
        `companydata`.`comp_phone`, `companydata`.`comp_email`,`companydata`.`comp_token`,
        `companydata`.`comp_reg_time`, `companydata`.`comp_status`, 
        `comp_address`.`comp_addr_one`, `comp_address`.`comp_addr_two`, 
        `comp_address`.`comp_city`, `comp_address`.`comp_province`, 
        `comp_type`.`comp_type`, `comp_type`.`comp_subtype`,
        `comp_type`.`comp_desc`, `comp_type`.`comp_wechat`
        ";
        $conditions =  "
        `companydata`.`comp_token` = `comp_type`.`comp_token` AND 
        `companydata`.`comp_token` = `comp_address`.`comp_token` AND 
        `companydata`.`comp_rep_email` = '$userName' AND
        `companydata`.`comp_pass` = '$hashedPass'
        ";
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
        $sort = "comp_id LIMIT 1";
        $Comp = $Super_Class->Super_Get($fields, $table, $conditions, $sort);
        if($Comp === false)
            $errorMessage = $Super_Class->Get_Message();
        else if(is_array($Comp) === false)
            $errorMessage = "Company login record returned unknown data type";
        else if(count($Comp) === 1)
        {
            $table = "login_tracker";
            $fields = "target_id, login_time, ip_address, login_type, login_device";
            $values = "'".$Comp[0]["user_id"]."', $time, '$ip', 'comp', '$device'";
            $isTracked = $Super_Class->Super_Insert($table, $fields, $values);
            if($isTracked === false)
                $errorMessage = "System error. Please contact support for assistance";
            else
            {
                $isSuccess = true;
                $successMessage = "success";
            } 
        }
        else if(count($User) <= 0)
        {
            $table = "failed_tries";
            $fields = "try_username, try_password, try_type, try_time, try_ip, try_device";
            $values = "'$userName', '$userPass','comp', $time, '$ip', '$device'";
            $isTracked = $Super_Class->Super_Insert($table, $fields, $values);
            if($isTracked === false)
                $errorMessage = "Code: 11 - Invalid company credentials";
            else
                $errorMessage = "Invalid company email/phone or password ".$userName." ".$hashedPass;
        }
        else 
            $errorMessage = "Account has a colllision, plz contact support for assistance";
    }
    else
        $errorMessage = "Unknown action type";
        
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage"=>$successMessage,
        "compData" => $Comp[0]
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