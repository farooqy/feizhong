<?php session_start();
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");

if(isset($_POST["user_username"]) && isset($_POST["user_password"]) && 
isset($_POST["action"]) && isset($_POST["user_usernameType"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
    $Token = null;
    $action = Sanitize_String($_POST["action"]);
    $userType = Sanitize_String($_POST["user_usernameType"]);
    $userPass = Sanitize_String($_POST["user_password"]);
    $userName = Sanitize_String($_POST["user_username"]);
    $searchField = null;
    if($action === "user_login")
    {
        if($userType === "isEmail")
        {
            $userName = Validate_Email($userName);
            if($userName === false)
                $errorMessage = "Invalid email";
            else
                $searchField = "user_email";
        }
        else if($userType === "isPhone")
        {
            $userName = Validate_Int($userName);
            if($userName === false)
                $errorMessage = "Invalid phone";
            else
                $searchField = "user_phone";
        }
        else
            $errorMessage = "Invalid user type";
    }
    else
        $errorMessage = "Unknown action type";
    
    if($errorMessage === null && $searchField !== null)
    {
        $time = time();
        $device = "UNKNOWN";
        $ip = $_SERVER["REMOTE_ADDR"];
        $table = "normal_user";
        $hashedPass = Get_Hash($userPass);
        $fields = "*";
        $conditions =  "$searchField = '$userName' AND user_password = '$hashedPass'";
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
        $sort = "user_id LIMIT 1";
        $User = $Super_Class->Super_Get($fields, $table, $conditions, $sort);
        if($User === false)
            $errorMessage = $Super_Class->Get_Message();
        else if(is_array($User) === false)
            $errorMessage = "User login record returned unknown data type";
        else if(count($User) === 1)
        {
            $table = "login_tracker";
            $fields = "target_id, login_time, ip_address, login_type, login_device";
            $values = "'".$User[0]["user_id"]."', $time, '$ip', 'user', '$device'";
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
            $values = "'$userName', '$userPass','user', $time, '$ip', '$device'";
            $isTracked = $Super_Class->Super_Insert($table, $fields, $values);
            if($isTracked === false)
                $errorMessage = "Code: 11 - Invalid user credentials";
            else
                $errorMessage = "Invalid user email/phone or password";
        }
        else 
            $errorMessage = "Account has a colllision, plz contact support for assistance";
    }
    // else
    //     $errorMessage = "Some shit is swrong with hre";
        
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage"=>$successMessage,
        "userData" => $User[0]
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