<?php session_start();
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");

if(isset($_POST["action"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
    $Token = null;
    $action = Sanitize_String($_POST["action"]);
    $UserData = array();
    if($action === "user_register")
    {
        $requestsParams = array("user_firstName", "user_lastName", "user_email",
        "user_phone", "user_password", "user_confirmPassword");
        $paramNames = array(
            "User First Name", "User last Name", "User Email", "User Phone",
            "User password", "Confirmation Password"
            );
        foreach($requestsParams as $key => $request)
        {
            if(isset($request) ===false)
                $errorMessage = $paramNames[$key]." is not set";
            else if(empty($_POST[$request]))
                $errorMessage = $paramNames[$key]." should be filled";
            else if($key === 2)
            {
                $email = Validate_Email($_POST[$request]);
                if($email === false)
                    $errorMessage = "Invalid email address";
                else
                    $UserData[$request] = $email;
            }
            else if($key === 3)
            {
                if(Validate_Int($_POST[$request]) === false)
                    $errorMessage = $paramNames[$key]." is not valid";
                else
                    $UserData[$request] = Validate_Int($_POST[$request]);
            }
            else if($key === 5)
            {
                if($UserData["user_password"] !== Sanitize_String($_POST[$request]))
                    $errorMessage = "Passwords do not match";
            }
            else
                $UserData[$request] = Sanitize_String($_POST[$request]);
        }
        if($errorMessage ===  null && empty($errorMessage))
        {
            
            require_once($root."classes/SuperClass.php");
            $Super_Class = new Super_Class();
            $regTime = time();
            $token = Get_Hash($regTime);
            $hashPass = Get_Hash($UserData["user_password"]);
            $table = "normal_user";
            $fields = "user_id, user_token";
            $condition = "user_email = '".$UserData["user_email"]."' 
            OR user_phone = '".$UserData["user_phone"]."'";
            $isExisting = $Super_Class->Super_Get($fields, $table, $condition, "user_id");
            if($isExisting=== false)
                $errorMessage = $Super_Class->Get_Message();
            else if(is_array($isExisting) === false)
                $errorMessage = "The record list to verify user return unrecognized data";
            else if(count($isExisting) >= 1)
                $errorMessage = "The email and/or the phone is registered to another user";
            else if(count($isExisting) === 0)
            {
                $fields = "user_fname, user_sname, user_email, user_phone, user_password,
                user_regtime, user_token, user_status";
                $values = "'".$UserData["user_firstName"]."','".$UserData["user_lastName"]."',
                '".$UserData["user_email"]."','".$UserData["user_phone"]."','".$hashPass."',
                '".$regTime."','".$token."','active'";
                $isSaved = $Super_Class->Super_Insert($table, $fields, $values);
                if($isSaved === false)
                    $errorMessage = $Super_Class->Get_Message();
                else 
                {
                    $table = "sparta";
                    $fields = "sparta_type, sparta_token, sparta_value";
                    $values = "'user', '$token', '".$UserData["user_password"]."'";
                    $isSaved = $Super_Class->Super_Insert($table, $fields, $values);
                    $isSuccess = true;
                    $successMessage = "success";
                    $Token = $token;
                    $Token = array(
                        "userId" => -1,
                        "userToken" => $token,
                        "userFirstName" => $UserData["user_firstName"],
                        "userLastName" => $UserData["user_lastName"],
                        "userEmail" => $UserData["user_email"],
                        "userPhone" => $UserData["user_phone"],
                        "userProfileUrl" => null,
                        
                        );
                }
            }
            else
                $errorMessage = "The user record data is returned negative number";
        }
        
        echo json_encode(array(
            "isSuccess" => $isSuccess,
            "errorMessage"=> $errorMessage,
            "successMessage"=>$successMessage,
            "user_data" => $Token,
            ));
        exit(0);
    }
    else
    {
        echo json_encode(array(
            "isSuccess" => false,
            "errorMessage"=> "Unknown action type",
            "successMessage"=>null,
            ));
        exit(0);
    }
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