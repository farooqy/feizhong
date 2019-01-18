<?php session_start();
error_reporting(E_ALL & ~E_NOTICE);
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");

if(isset($_POST["requestType"]) && isset($_POST["profilePictureUrl"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
    $Token = null;
    $reqType = Sanitize_String($_POST["requestType"]);
    $userProfileUrl = Sanitize_String($_POST["profilePictureUrl"]);
    $userToken = Sanitize_String($_POST["userToken"]);
    
    if($reqType === "requestUserPicture")
    {
        $table = "normal_user";
        $fields = "user_profile";
        $condition = "user_token = '$userToken' ";
        $sortby = "user_profile LIMIT 1";
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
        $userProfilePicture = $Super_Class->Super_Get($fields, $table, $condition, $sortby);
        if($userProfilePicture === false)
            $errorMessage = $Super_Class->Get_Message();
        else if(is_array($userProfilePicture) === false)
            $errorMessage = "The returned data for user profile picture is not recognized";
        else if(count($userProfilePicture) <= 0)
            $errorMessage = "user profile picture does not exist";
        else if(count($userProfilePicture) === 1)
        {
            $dbUserProfileUrl = $userProfilePicture[0]["user_profile"];
            if(strcmp($dbUserProfileUrl,$userProfileUrl) === 0)
            {
                $urls = explode("/",$dbUserProfileUrl);
                if($urls === false)
                    $errorMessage = "The retrieved user profile picture is empty";
                else if(count($urls) <= 1)
                    $errorMessage = "The retrieved user profile picture pattern is not correct";
                else 
                {
                    $profileImage = $root."uploads/users/$userToken/".$urls[count($urls)-1];
                    $imageFile = base64_encode(file_get_contents($profileImage));
                    $imageHash = Get_Hash($imageFile);
                    $isSuccess = true;
                    $successMessage = "success";
                }
            }
            else
                $errorMessage = "The requested user profile picture is not authorized ";
        }
        else
            $errorMessage = "The user profile picture retrieved has a collision";
        
    }
    else
        $errorMessage = "Unknown Request type";
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage"=>$successMessage,
        "user_token" => $userToken,
        "profilePicture" => $profileImage,
        "imageFile" => $imageFile,
        "imageHash"=>$imageHash
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