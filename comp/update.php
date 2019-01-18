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
    $companyId = Sanitize_String($_POST["companyId"]);
    $compToken = Sanitize_String($_POST["companyToken"]);
    $updateValue = Sanitize_String($_POST["updateValue"]);
    
    $allReq = array($reqType, $updateField, $companyId, $compToken, $updateValue);
    $reqNames = array("Request type", "update field", "company ID", "company token", "updated value");
    
    $updateAbleFields = array("companyType", "companySubType", "companyProvince", "companyCity", "companyPhone",
    "companyWechatID", "companyEmail", "companyCeo", "companyRepName", "companyRepEmail", "companyAddress1", "companyAddress2", 
    "companyDesc", "companyName");
    $fieldNames = array("company Type ", "company subtype", "company province", "company city", "company phone ", "company wechat ID",
    "company email", "company CEO", "company representative name", "company representative email", "company address 1",
    "company address 2", "company description", "company name");
    $tableFields = array("comp_type", "comp_subtype", "comp_province", "comp_city","comp_phone", "comp_wechat", "comp_email",
    "comp_ceo", "comp_representative","comp_rep_email","comp_addr_one", "comp_addr_two", "comp_desc", "comp_name");
    $sharedPreference = array("comp_type", "comp_subType", "comp_province", "comp_city", "comp_phone", "comp_wechat", "comp_email", "comp_ceo", 
    "comp_representative", "comp_representativeEmail", "comp_address1", "comp_address2", "comp_description", "comp_name");
    
    $tablesKeys= array(
        "companydata" => array(4,6,7,8,9,13),
        "comp_address" => array(2,3,10,11),
        "comp_type" => array(0,1,5,12)
        );
        
    if($reqType === "updateCompanyProfile")
    {
        foreach($allReq  as $reqKey => $reqValue)
        {
            if(empty($reqValue))
            {
                $errorMessage = "The request $reqNames[$reqKey] is missing/empty ";
                break;
            }
        }
        if($errorMessage === null && empty($errorMessage))
        {
            $fieldKey = array_search($updateField, $updateAbleFields, true);
            if( $fieldKey !== false)
            {
                $table = "companydata";
                $fields = "comp_id";
                $condition = "comp_token = '$compToken'";
                $sortby = "comp_id LIMIT 1";
                
                $Comp = $Super_Class->Super_Get($fields, $table, $condition, $sortby);
                if($Comp === false)
                    $errorMessage = $Super_Class->Get_Message();
                else if(is_array($Comp) === false)
                    $errorMessage = "The company verification data returned unknown data type";
                else if(count($Comp) !== 1)
                    $errorMessage = "The company account you are trying to update doesn't exist.";
                else if(strcmp($Comp[0]["comp_id"], $companyId) !== 0 )
                    $errorMessage = "Updating unauthorized company data is not allowed";
                else
                {
                    if(in_array($fieldKey, $tablesKeys["companydata"]))
                        $table ="companydata";
                    else if(in_array($fieldKey, $tablesKeys["comp_address"]))
                        $table = "comp_address";
                    else if(in_array($fieldKey, $tablesKeys["comp_type"]))
                        $table = "comp_type";
                    else
                        $errorMessage = "Invalid table key. Contact support.";
                }
                
                if($errorMessage === null && empty($errorMessage))
                {
                    $fields = "$tableFields[$fieldKey]";
                    $condition = "comp_token = '$compToken'";
                    $sortby = "comp_token LIMIT 1";
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
    $values = "'$updateField','$changeFrom', '$updateValue', 'company',$time, '$successChange', '$compToken', '$errorMessage'";
    
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