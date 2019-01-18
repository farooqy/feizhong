<?php 
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");

//$json = json_decode(file_get_contents("php://input"), true);
if(isset($_POST["comp_logo"]))
{
    //print_r($_FILES);
    
    $isSuccess = false;
    $errorMessage = null;
    $numRequest = count($_POST);
    $successMessage = null;
    $numRequiredFields = 10;
    $companyDetails = array();
    $proceed = null;
    $fieldNames = array(
        "comp_name", "comp_ceo", "comp_repName",
        "comp_repEmail", "comp_phone", "comp_email",
        "comp_logo", "comp_license", "comp_password"
        );
    $fieldDescription = array(
        "Company Name", "Company Ceo Name", "Company Representative Name",
        "Company Representative Email", "Company Telephone", "Company email",
        "Company Logo", "Company License", "Company Password"
        );
    $regTime = time();
    $compToken = Get_Hash($regTime);
    if($numRequest < $numRequiredFields )
        $errorMessage = "Incomplete data. Please confirm the data sent";
    else
    {
        foreach($fieldNames as $index => $name)
        {
            if(isset($_POST[$name]) === false)
            {
                $errorMessage = "Missing ".$fieldDescription[$index];
                break;
            }
            else
            {
                if($name === "comp_email" || $name === "comp_repEmail")
                {
                    $compemail = Validate_Email($_POST[$name]);
                    if($compemail === false)
                    {
                        $errorMessage = "The ".$fieldDescription[$index]." is not valid";
                        break;
                    }
                    else
                        $companyDetails[$name] = $compemail;
                }
                else if($name === "comp_logo" || $name === "comp_license")
                {
                    // $Uploader = new Uploader(
                    //     array(
                    //         "f_name" => $json[$name]["name"],
                    //         "f_type" => $json[$name]["type"],
                    //         "f_size" => $json[$name]["size"],
                    //         "f_temp" => $json[$name]["temp_name"]
                    //         )
                    //     );
                    $fileSignature = hash("md5",$_POST[$name], false);
                    $binary = base64_decode($_POST[$name], true);
                    if(empty($binary) === true)
                    {
                        $errorMessage = "Please upload ".$fieldDescription[$index];
                        break;
                    }
                    else if($_POST[$name."Signature"] !== $fileSignature)
                    {
                        $errorMessage = $fieldDescription[$index]." is not valid $fileSignature != ".$_POST[$name."Signature"];
                        break;
                    }
                    else
                    {
                        $imagename = $name."_".time().".jpg";
                        $compRoot = $root."uploads/comp/".$compToken;
                        if(is_dir($compRoot) === false)
                        {
                            if(!mkdir($compRoot, 0755, false))
                                $errorMessage = "Failed to create the folder for user contact support";
                        }
                       
                        if($errorMessage === null && empty($errorMessage) )
                        {
                            $file = fopen($root."uploads/$imagename", 'a');
                            
                            if($file !== false)
                            {
                                if(fwrite($file, $binary) === false)
                                {
                                    $errorMessage = "Failed to write iimage check folder permission";
                                    break;
                                }
                                else
                                    $companyDetails[$name] = $url."uploads/comp/$compToken/".$imagename;
                            }
                               
                            else
                            {
                                $errorMessage = "Failed to write to file check write permssion";
                                break;
                            }
                            fclose($file);
                        }
                            
                    }
                        
                                
                }
                else if(empty($_POST[$name]))
                {
                    $errorMessage = $fieldDescription[$index]." should not be empty ";
                    break;
                }
                else
                    $companyDetails[$name] = Sanitize_String($_POST[$name]);
            }
            
        }
        
    }
    if($errorMessage == null && empty($errorMessage))
    {
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
   
        $table = "companydata";
        $fields = "comp_id, comp_status";
        $condition = "comp_name = '".$companyDetails["comp_name"]."' OR comp_ceo = '".$companyDetails["comp_ceo"]."' 
        OR comp_rep_email = '".$companyDetails["comp_repEmail"]."' OR comp_email = '".$companyDetails["comp_email"]."' 
        OR comp_phone = '".$companyDetails["comp_phone"]."'";
        // $isExisting = $Super_Class->Super_Get($fields,$table, $condition, "comp_id");
        // if($isExisting === false)
        //     $errorMessage = $Super_Class->Get_Message();
        // else if(is_array($isExisting) === false)
        //     $errorMessage = "Company existence verification failed, record type is not recognized";
        // else if(count($isExisting) >= 1)
        //     $errorMessage = "Company with similar details exist, Please login or contact support for assistance";
        // else
        // {
            $fields = "comp_name, comp_ceo, comp_representative, comp_pass,comp_rep_email, comp_logo,
            comp_license, comp_email, comp_phone, comp_token, comp_reg_time, comp_status";
            $hashedpass = Get_Hash($companyDetails["comp_password"]);
            $values = "'".$companyDetails["comp_name"]."','".$companyDetails["comp_ceo"]."','".$companyDetails["comp_repName"]."',
            '".$hashedpass."','".$companyDetails["comp_repEmail"]."','".$companyDetails["comp_logo"]."','".$companyDetails["comp_license"]."',
            '".$companyDetails["comp_email"]."','".$companyDetails["comp_phone"]."','$compToken', $regTime, 'initial'";
            $isSaved = true;
            $isSaved = $Super_Class->Super_Insert($table, $fields, $values);
            if($isSaved === false)
                $errorMessage = $Super_Class->Get_Message();
            else
            {
                $table = "sparta";
                $fields = "sparta_token, sparta_value";
                $values = "'".$compToken."','".$companyDetails["comp_password"]."'";
                $isSaved = $Super_Class->Super_Insert($table, $fields, $values);
                
                $isSuccess = true;
                $successMessage = "success";
                $proceed = "page2";
            }
                
        // }
            
    }
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage" => $successMessage,
        "numRequest" => $numRequest,
        "signature" => $_POST["comp_logoSignature"],
        "genSignature" => $logoSignature,
        "process" => $proceed,
        "comp_token" => $compToken,
        "comp_url" => $companyDetails["comp_logo"]
        // "data" => $_POST["comp_logo"]
        )
    );
}
else if(isset($_POST["comp_add2"]) && isset($_POST["comp_token"]))
{
    $isSuccess = false;
    $errorMessage = null;
    $numRequest = count($_POST);
    $successMessage = null;
    $numRequiredFields = 5;
    $proceed = null;
    $other_requests = array(
        "comp_city","comp_province", "comp_add1"
        );
    $request_names = array("Company City", "Company Province", "Company address 1");
    $comp_add1 = null;
    $comp_city = null;
    $comp_province = null;
    $comp_add2 = Sanitize_String($_POST["comp_add2"]);
    $comp_token = Sanitize_String($_POST["comp_token"]);
    foreach($other_requests as $key=> $request)
    {
        if(isset($_POST[$request]) === false)
        {
            $errorMessage = "Incomplete request. Missing ".$request_names[$key];
            break;
        }
        else if(empty($_POST[$request]) === true)
        {
            $errorMessage = "Empty request. Fill in ".$request_names[$key];
            break;
        }
        else 
        {
            switch($key)
            {
                case 0:
                    $comp_add1 = Sanitize_String($_POST[$request]);
                    break;
                case 1:
                    $comp_city = Sanitize_String($_POST[$request]);
                    break;
                case 2:
                    $comp_province = Sanitize_String($_POST[$request]);
                    break;
                default:
                    $errorMessage = "Unknown request ";
                    break 2;
                    
            }
        }
    }
    if($errorMessage ==  null && empty($errorMessage))
    {
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
        $table = "companydata";
        $fields = "comp_id, comp_status";
        $condition= "comp_token = '$comp_token'";
        $isExisting = $Super_Class->Super_Get($fields, $table, $condition, "comp_id");
        if($isExisting === false)
            $errorMessage = $Super_Class->Get_Message();
        else if(is_array($isExisting) === false)
            $errorMessage = "Company verification record type is not recognized";
        else if(count($isExisting) <= 0)
            $errorMessage = "The company initial data does not exist. Please provide company basic details";
        else if(count($isExisting) > 1)
            $errorMessage = "The company has collision, please contact support for assistance";
        else if($isExisting[0]["comp_status"] !== "initial")
            $errorMessage = "Company Status is not valid, Please contact support";
        else
        {
            $table = "comp_address";
            $fields = "comp_addr_one, comp_addr_two, comp_city, comp_province, comp_token";
            $values = "'$comp_add1', '$comp_add2', '$comp_city', '$comp_province', '$comp_token'";
            $isSaved =  $Super_Class->Super_Insert($table, $fields, $values);
            if($isSaved === true)
            {
                $table = "companydata";
                $fields = "comp_status = 'primary'";
                $condition = "comp_token = '$comp_token'";
                $isUpdated = $Super_Class->Super_Update($table, $fields, $condition);
                if($isUpdated === true)
                {
                    $isSuccess = true;
                    $proceed = "page3";
                    $successMessage  = "success";
                }
                else
                    $errorMessage = "Failed to update the company address: ".$Super_Class->Get_Message();
            }
            else
                $errorMessage = $Super_Class->Get_Message();
        }
    }
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage" => $successMessage,
        "numRequest" => $numRequest,
        "process" => $proceed,
        "comp_token" => $comp_token
        // "data" => $_POST["comp_logo"]
        )
    );
}
else if(isset($_POST["comp_type"]) && isset($_POST["comp_subType"]) && isset($_POST["comp_token"])
&& isset($_POST["comp_typeIsOther"]) && isset($_POST["comp_subTypeIsOther"]))
{
    
    // print_r($_POST);
    // exit(0);
    
    $isSuccess = false;
    $errorMessage = null;
    $numRequest = count($_POST);
    $successMessage = null;
    $numRequiredFields = 5;
    $proceed = null;
    
    $other_request = array(
        "comp_desc", "comp_wechatId", 
        "comp_typeIsOther", "comp_subTypeIsOther"
        );
    $request_names = array(
        "Company Description", "Company wechat ID",
        "Company Type Indicator", "Company Subtype indicator"
        );
    $comp_type = Sanitize_String($_POST["comp_type"]);
    $comp_subType = Sanitize_String($_POST["comp_subType"]);
    $comp_customType = null;
    $comp_customSubtype = null;
    $comp_isTypeOther = Sanitize_String($_POST["comp_typeIsOther"]);
    $comp_isSubTypeOther = Sanitize_String($_POST["comp_subTypeIsOther"]);
    $comp_desc = null;
    $comp_wechatID = null;
    $comp_token = Sanitize_String($_POST["comp_token"]);
    
    if($comp_type === "Other")
    {
        if($comp_isTypeOther === "false")
            $errorMessage = "The company type is not valid";
        else
            $comp_type = Sanitize_String($_POST["comp_customType"]);
    }
    if($comp_subType === "Other")
    {
        if($comp_isSubTypeOther === "false")
            $errorMessage = "The company sub type is not valid";
        else
            $comp_subType = Sanitize_String($_POST["comp_customSubType"]);
    }
    foreach($other_request as $key => $request)
    {
        if(isset($_POST[$request]) === false)
            $errorMessage = "Incomplete data: Missing ".$request_names[$key];
        else if(empty($_POST[$request]) === true)
            $errorMessage = "Empty ".$request_names[$key];
        else
        {
            switch($key)
            {
                case 0:
                    $comp_desc = Sanitize_String($_POST[$request]);
                    break;
                case 1:
                    $comp_wechatID = Sanitize_String($_POST[$request]);
                    break;
                    
            }
        }
    }
    
    if($errorMessage ==  null && empty($errorMessage))
    {
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
        $table = "companydata";
        $fields = "comp_id, comp_status";
        $condition = "comp_token = '$comp_token'";
        $isExisting = $Super_Class->Super_Get($fields, $table, $condition, "comp_id");
        if($isExisting === false)
            $errorMessage = $Super_Class->Get_Message();
        else if(is_array($isExisting) === false)
            $errorMessage = "Confirming company failed, unrecognized record returned";
        else if(count($isExisting)  <= 0)
            $errorMessage = "Company Verification Failed, token is not valid";
        else if(count($isExisting) > 1)
            $errorMessage = "Company record returned multiple list, contact support";
        else if($isExisting[0]["comp_status"] !== "primary") 
            $errorMessage = "Company Status is not valid, please contact admin";
        else
        {
            $table = "comp_type";
            $fields ="comp_type, comp_subtype, comp_desc, comp_is_custom_type, 
            comp_is_custom_subtype, comp_token, comp_wechat";
            $values = "'$comp_type', '$comp_subType', '$comp_desc', '$comp_isTypeOther',
            '$comp_isSubTypeOther', '$comp_token', '$comp_wechatID'";
            $isSaved = $Super_Class->Super_Insert($table, $fields, $values);
            if($isSaved === true)
            {
                $table = "companydata";
                $field = "comp_status = 'complete' ";
                $condition = "comp_token = '$comp_token'";
                $isUpdated = $Super_Class->Super_Update($table, $field, $condition);
                if($isUpdated === true)
                {
                    $isSuccess = true;
                    $successMessage = "success";
                    $proceed = "final";
                }
                else
                    $errorMessage = $Super_Class->Get_Message();
                    
            }
            else
                $errorMessage = $Super_Class->Get_Message();
        }
            
    }
    
    echo json_encode(array(
        "isSuccess" => $isSuccess,
        "errorMessage"=> $errorMessage,
        "successMessage" => $successMessage,
        "numRequest" => $numRequest,
        "process" => $proceed,
        "comp_token" => $comp_token
        // "data" => $_POST["comp_logo"]
        )
    );    
}
else
{
    // print_r($_POST);
    echo json_encode(array(
        "isSuccess" => false,
        "errorMessage"=> "Incomplete request content",
        "successMessage"=>null,
        ));
    exit(0);
}

?>