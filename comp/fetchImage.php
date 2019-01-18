<?php session_start();
error_reporting(E_ALL & ~E_NOTICE);
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");

if(isset($_POST["requestType"]) && isset($_POST["logoUrl"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $successMessage = null;
    $Token = null;
    $reqType = Sanitize_String($_POST["requestType"]);
    $logoUrl = Sanitize_String($_POST["logoUrl"]);
    $compToken = Sanitize_String($_POST["compToken"]);
    $requestHost = Sanitize_String($_POST["host"]);
    
    if($reqType === "requestCompLogo")
    {
        //check if another user or another comp is requesting logo of a company
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
        if($requestHost !== "external")
        {
            $table = "companydata";
            $fields = "comp_logo";
            $condition = "comp_token = '$compToken' ";
            $sortby = "comp_logo LIMIT 1";
        
            $compLogo = $Super_Class->Super_Get($fields, $table, $condition, $sortby);
            if($compLogo === false)
                $errorMessage = $Super_Class->Get_Message();
            else if(is_array($compLogo) === false)
                $errorMessage = "The returned data for company logo is not recognized";
            else if(count($compLogo) <= 0)
                $errorMessage = "Company logo does not exist";
            else if(count($compLogo) === 1)
            {
                $dbLogoUrl = $compLogo[0]["comp_logo"];
                if(strcmp($dbLogoUrl,$logoUrl) === 0)
                {
                    $urls = explode("/",$dbLogoUrl);
                    if($urls === false)
                        $errorMessage = "The retrieved company logo is empty";
                    else if(count($urls) <= 1)
                        $errorMessage = "The retrieved company logo pattern is not correct";
                    else 
                    {
                        $logoImage = $root."uploads/".$urls[count($urls)-1];
                        $imageFile = base64_encode(file_get_contents($logoImage));
                        $imageHash = Get_Hash($imageFile);
                        $isSuccess = true;
                        $successMessage = "success";
                    }
                }
                else
                    $errorMessage = "The requested company logo is not valid ";
            }
            else
                $errorMessage = "The company logo retrieved has a collision";
        }
        else
        {
            $dbLogoUrl = $logoUrl;
            $urls = explode("/",$dbLogoUrl);
            if($urls === false)
                $errorMessage = "The retrieved company logo is empty";
            else if(count($urls) <= 1)
                $errorMessage = "The retrieved company logo pattern is not correct ".$dbLogoUrl;
            else 
            {
                $logoImage = $root."uploads/".$urls[count($urls)-1];
                $imageFile = base64_encode(file_get_contents($logoImage));
                $imageHash = Get_Hash($imageFile);
                $isSuccess = true;
                $successMessage = "success";
            } 
            
        }
            
        
    }
    else
        $errorMessage = "Unknown Request type";
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage"=>$successMessage,
        "user_token" => $Token,
        "logoImage" => $logoImage,
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