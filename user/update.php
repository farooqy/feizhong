<?php session_start();
error_reporting(E_ALL & ~E_NOTICE);
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");
    
if(isset($_POST["requestType"]) && isset($_POST["updateField"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
    $successChange = "failed";
    
    require_once($root."classes/SuperClass.php");
    $Super_Class = new Super_Class();
    
    $reqType = Sanitize_String($_POST["requestType"]);
    $updateField = Sanitize_String($_POST["updateField"]);
    $userId = Sanitize_String($_POST["userId"]);
    $userToken = Sanitize_String($_POST["userToken"]);
    $updateValue = Sanitize_String($_POST["updateValue"]);
    
    $allReq = array($reqType, $updateField, $userId, $userToken, $updateValue);
    $reqNames = array("Request type", "update field", "user ID", "user token", "updated value");
    
    $updateAbleFields = array("userFirstName", "userLastName", "userPhoneNumber", "userEmail", "userPassword");
    $fieldNames = array("User first name ", "User Last name", "User phone number", "user email", "user password ");
    
    $tableFields = array("user_fname", "user_sname", "user_phone", "user_email","user_password");
    
    $sharedPreference = array("user_firstName", "user_lastName", "user_phone", "user_email", "user_password");
    
    
    if($reqType === "updateUserProfile")
    {
        foreach($allReq  as $reqKey => $reqValue)
        {
            if(empty($reqValue))
            {
                $errorMessage = "The request $reqNames[$reqKey] is  missing/empty ";
                break;
            }
        }
        if($errorMessage === null && empty($errorMessage))
        {
            $fieldKey = array_search($updateField, $updateAbleFields, true);
            if( $fieldKey !== false)
            {
                $table = "normal_user";
                $fields = "user_id";
                $condition = "user_token = '$userToken'";
                $sortby = "user_id LIMIT 1";
                
                $User = $Super_Class->Super_Get($fields, $table, $condition, $sortby);
                if($User === false)
                    $errorMessage = $Super_Class->Get_Message();
                else if(is_array($User) === false)
                    $errorMessage = "The user verification data returned unknown data type";
                else if(count($User) !== 1)
                    $errorMessage = "The user account you are trying to update doesn't exist.";
                else if(strcmp($User[0]["user_id"], $userId) !== 0 )
                    $errorMessage = "Updating unauthorized user data is not allowed";
                else
                {
                    $table = "normal_user";
                    $fields = "$tableFields[$fieldKey]";
                    $condition = "user_token = '$userToken'";
                    $sortby = "user_token LIMIT 1";
                    $currentValue = $Super_Class->Super_Get($fields, $table, $condition, $sortby);
                    if($currentValue === false)
                        $errorMessage = "Failed to get current value ".$Super_Class->Get_Message();
                    else if(is_array($currentValue) === false)
                        $errorMessage = "current value data type returned unknown type";
                    else if(count($currentValue) !== 1)
                        $errorMessage = "The field to update contains no value. ";
                    else
                    {
                        $fields = "$tableFields[$fieldKey] = '$updateValue'";
                        
                        $isUpdated = $Super_Class->Super_Update($table, $fields, $condition);
                        if($isUpdated === false)
                            $errorMessage = $Super_Class->Get_Message();
                        else
                        {
                            $isSuccess = true;
                            $successMessage = "success";
                            $successChange = "success";
                        }
                    }
                }
            }
            else
                $errorMessage = "The field $updateField cannot be updated";
        }
    }
    else
        $errorMessage = "Invalid request type";
    
    $time = time();
    $table = "changes_tracker";
    $changeFrom = $currentValue[0][$tableFields[$fieldKey]];
    $fields = "change_field, change_from, change_value, change_type, change_time, change_status, change_token, failed_reason";
    $values = "'$updateField','$changeFrom', '$updateValue', 'user',$time, '$successChange', '$userToken', '$errorMessage'";
    
    $isTracked = $Super_Class->Super_Insert($table, $fields, $values);
    
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage"=>$successMessage,
        "fieldName" => $sharedPreference[$fieldKey],
        "fieldValue" => $updateValue
        ));
    exit(0);
    
}
else
{
    echo json_encode(array(
        "isSuccess" => false,
        "errorMessage"=> "Incomplete request content",
        "successMessage"=>null,
        "data"=> $_POST,
        ));
    exit(0);
}


?>