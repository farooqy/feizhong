<?php session_start();
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");

if(isset($_POST["user_action"]) && isset($_POST["user_profile"]) && isset($_POST["user_token"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
    $Token = null;
    $action = Sanitize_String($_POST["user_action"]);
    $profile = Sanitize_String($_POST["user_profile"]);
    $user_token = Sanitize_String($_POST["user_token"]);
    $profileSignature = Get_Hash($profile);
    $sentSignature = Sanitize_String($_POST["user_profileSignature"]);
    $user_id = Sanitize_String($_POST["user_id"]);    
    $allreq = array($action, $profile, $user_token, $sentSignature, $user_id);
    $reqNames = array("Action type", "User Profile Picture",
    "User Token", "Profile Signature", "user ID");
    foreach($allreq as $key => $req)
    {    
        if(empty($req) || $req === null)
        {
            $errorMessage = "Empty request for ".$reqNames[$key];
            break;
        }
    }
    if(empty($errorMessage) && $errorMessage === null)
    {
        if(strcmp($profileSignature, $sentSignature) !== 0)
            $errorMessage = "Profile signature do not much";
        else 
        {
            $table = "normal_user";
            $fields = "user_id";
            $condition = "user_token = '$user_token'";
            $sortby = "user_id LIMIT 1";
            require_once($root."classes/SuperClass.php");
            $Super_Class = new Super_Class();
            $User = $Super_Class->Super_Get($fields, $table, $condition, $sortby);
            if($User === false)
                $errorMessage = $Super_Class->Get_Message();
            else if(is_array($User) === false)
                $errorMessage = "The user verification record returned unknown data type";
            else if(count($User) <= 0)
                $errorMessage = "You are not an existing user to change profile. Probably a threat";
            else if(count($User) > 1)
                $errorMessage = "User has token collision with another user";
            else if($User[0]["user_id"] !== $user_id)
                $errorMessage = "Updating profile for unauthorized user. probably a threat";
            else 
            {
                $userRoot = $root."uploads/users/".$user_token;
                if(is_dir($userRoot) === false)
                {
                    if(!mkdir($userRoot, 0755, false))
                        $errorMessage = "Failed to create the folder for user contact support";
                }
                if(empty($errorMessage) && $errorMessage === null)
                {
                    $profileName = "profile_".time()."jpg";
                    $profileDir = $userRoot."/".$profileName;
                    $fp = fopen($profileDir, "a");
                    $profile = base64_decode($profile, true);
                    if($fp)
                    {
                        if(fwrite($fp, $profile))
                        {
                            $profileUrl = $url."uploads/users/$user_token/$profileName";
                            $fields = "user_profile = '$profileUrl'";
                            $condition = "user_token = '$user_token' AND user_id = ".$User[0]["user_id"];
                            $isUpdated = $Super_Class->Super_Update($table, $fields, $condition);
                            if($isUpdated === true)
                            {
                                $isSuccess = true;
                                $successMessage = "success";
                                
                            }
                            else
                                $errorMessage = "Failed to update the user profile: ".$Super_Class->Get_Message();
                        }
                        else
                            $errorMessage = "Failed to write to the file profile, contact support";
                    }
                    else
                        $errorMessage = "Failed to open file for writing image contact support";
                }
            }
        }
    }
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage"=>$successMessage,
        "userProfileUrl" => $profileUrl
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