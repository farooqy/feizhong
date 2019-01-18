<?php session_start();
error_reporting(E_ALL & ~E_NOTICE);
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");

if(isset($_POST["action"]) && isset($_POST["user_interests"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
    $Token = null;
    $Action = Sanitize_String($_POST["action"]);
    $user_interest = explode(",",Sanitize_String($_POST["user_interests"]));
    $userToken = Sanitize_String($_POST["user_token"]);//"837fdb9fceeb37a358617db51bde81ae";
    
    if(is_array($user_interest) === false)
        $errorMessage = "User interest type is not valid ";
    else if($Action === "user_interests")
    {
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
        $table = "normal_user";
        $field = "user_id";
        $condition = "user_token = '$userToken'";
        $isExistingUser = $Super_Class->Super_Get($field, $table, $condition, "$field LIMIT 1");
        if($isExistingUser === false)
            $errorMessage = $Super_Class->Get_Message();
        else if(is_array($isExistingUser) === false)
            $errorMessage = "The data returned for verifying the user is not recognized";
        else if(count($isExistingUser) !== 1)
            $errorMessage = "The user you are setting for is not valid. Please check if you are logged in";
        else
        {
            $numInsterest = count($user_interest);
            $interestIndex = 0;
            $table = "user_interests";
            $fields = "interest_user_token, interest_cat, interest_value, interest_time";
            $time = time();
            
            $successCounter =0;
            while($interestIndex < $numInsterest)
            {
                $subInterest = explode("|", $user_interest[$interestIndex]);
                if(is_array($subInterest) === false)
                {
                    $errorMessage = "Interests data contain unrecognized format";
                    break;
                }
                else
                {
                    for($i=1; $i<count($subInterest); $i++)
                    {
                        $value = "'$userToken', '".$subInterest[0]."', '".$subInterest[$i]."', $time";
                        $isSaved = $Super_Class->Super_Insert($table, $fields, $value);
                        if($isSaved === false)
                        {
                            $errorMessage = $Super_Class->Get_Message();
                            break 2;
                        }
                        
                    }
                }
                $interestIndex++;
                $successCounter++;
            }
            if($successCounter === $numInsterest)
            {
                $isSuccess = true;
                $successMessage = true;
            }
            else if($errorMessage === null && empty($errorMessage))
                $errorMessage .= "Uncaught error $interestIndex and $numInsterest";
        }
            
    }
    else
        $errorMessage = "Unknown Request type";
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage"=>$successMessage,
        "data"=> $_POST,
        "interests" => $user_interest[0],
        "length" => count($user_interest)
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