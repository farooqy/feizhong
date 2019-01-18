<?php session_start();
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = "http://feizhong.ganacsigroup.com/";
require_once($root."classes/functions.php");
if(isset($_POST["reqType"]) && isset($_POST["hostToken"]) && isset($_POST["hostType"]))
{
    $errorMessage = null;
    $isSuccess = false;
    $errorMessage = null;
    $reqType = Sanitize_String($_POST["reqType"]);
    $hostToken = Sanitize_String($_POST["hostToken"]);
    $hostType = Sanitize_String($_POST["hostType"]);
    $communicationPartners = array();
    $messageDetails = array();
    if($reqType === "history")
    {
        require_once($root."classes/SuperClass.php");
        $Super_Class = new Super_Class();
        if($hostType === "user")
        {
            $table = "normal_user";
            $fields = "user_status";
            $condition = "user_token = '$hostToken'" ;
        }
        else
        {
            $table = "companydate";
            $fields = "comp_status";
            $condition = "comp_token = '$hostToken'";
        }
        $hostData = $Super_Class->Super_Get($fields, $table, $condition, $fields);
        if($hostData === false)
        {
            $errorMessage = "Failed to verify your account. ".$Super_Class->Get_Message();
        }
        else if(is_array($hostData) === false)
            $errorMessage = "The data returned is not recognized type";
        else if(count($hostData) <= 0)
            $errorMessage = "You seem to be logged out. Please check your login status";
        else if($hostData[0][$fields] === "active" || $hostData[0][$fields] === "complete")
        {
            $table ="messages";
            $fields = "*";
            $condition = "message_target = '$hostToken' || message_host = '$hostToken' AND message_status NOT LIKE '%deleted%'";
            $sortby = "message_time";
            $message = $Super_Class->Super_Get($fields, $table, $condition, $sortby);
            if($message === false)
            {
                $errorMessage = "Failed to get message. Please contact support";
            }
            else if(is_array($message) === false)
                $errorMessage = "The messages retrieved is unknown type. Please contact support";
            else
            {
                foreach($message as $msgKey => $msg)
                {
                    $target = $msg["message_target"]; //receiver token
                    $host = $msg["message_host"]; //sender token
                    $hostType = $msg["message_host_type"];
                    $targetType = $msg["message_target_type"];
                    
                    $allPartners = array_column($communicationPartners, "partnerToken");
                    if($target !== $hostToken)
                    {
                        $partnerToCheck = $target;
                    }
                    else
                        $partnerToCheck = $host;
                    if(in_array($partnerToCheck, $communicationPartners))
                    {
                        $partnerIndex = array_search($partnerToCheck, $communicationPartners);
                        if($partnerIndex === false)
                        {
                            $messageData = array(
                                "partnerError" => true,
                                "partner_errorMessage" =>  "Failed to get partner index. Please contact support",
                                );
                                array_push($messageDetails, $messageData);
                        }
                        else
                        {
                            $messageData = array(
                                "messageId" => $msg["message_id"],
                                "messageToken" => $msg["message_token"],
                                "messageType" => $msg["message_type"],
                                "messageContent" => $msg["message_content"],
                                "messageTime" => format_time($msg["message_time"]),
                                "messageStatus" => $msg["message_status"],
                                "message_target" => $partnerToCheck,
                                "message_error" => false,
                                "message_errorMessage" =>  null,
                                );
                                array_push($messageDetails[$partnerIndex]["messages"], $messageData);
                        }
                    }
                    else
                    {
                        if($targetType === "company")
                        {
                            $table = "companydata";
                            $field = "comp_name, comp_logo, comp_email";
                            $condition = "comp_token = '$partnerToCheck'";
                            $sortby = "comp_name";
                        }
                        else
                        {
                            $table = "normal_user";
                            $field = "user_name, user_profile, user_email";
                            $condition = "user_token = '$partnerToCheck'";
                            $sortby = "user_name";
                        }
                        
                        $targetDetails = $Super_Class->Super_Get($field, $table, $condition, $sortby);
                        if($targetDetails && count($targetDetails) === 1)
                        {
                            $messageData = array(
                                "messageId" => $msg["message_id"],
                                "messageToken" => $msg["message_token"],
                                "messageType" => $msg["message_type"],
                                "messageContent" => $msg["message_content"],
                                "messageTime" => format_time($msg["message_time"]),
                                "messageStatus" => $msg["message_status"],
                                "message_target" => $partnerToCheck,
                                "message_error" => false,
                                "message_errorMessage" =>  null,
                                );
                                if($targetType === "company")
                                {
                                    $pName = $targetDetails[0]["comp_name"];
                                    $pLogo = $targetDetails[0]["comp_logo"];
                                    $pemail = $target[0]["comp_email"];
                                }
                                else
                                {
                                    $pName = $targetDetails[0]["user_name"];
                                    $pLogo = $targetDetails[0]["user_profile"];
                                    $pemail = $target[0]["user_email"];
                                }
                                $partnerDetails = array(
                                    "id" => 0,
                                    "partnerToken" => $partnerToCheck,
                                    "partnerEmail" => $pemail,
                                    "partnerName" => $pName,
                                    "partnerProfile" => $pLog,
                                    "messages" => array($messageData),
                                    );
                            array_push($messageDetails, $partnerDetails);
                            array_push($communicationPartners, $partnerToCheck);
                        }
                        else if($targetDetails === false)
                        {
                            $messageData = array(
                                "partnerError" => true,
                                "partner_errorMessage" =>  "Failed to get partner details. Please contact support",
                                );
                            array_push($messageDetails, $messageData);    
                        }
                        else if(is_array($targetDetails) === false)
                        {
                            $messageData = array(
                                "partnerError" => true,
                                "partner_errorMessage" =>  " Partner details are invalid type. Please contact support",
                                );
                                array_push($messageDetails, $messageData);
                        }
                        else
                        {
                            $messageData = array(
                                "partnerError" => true,
                                "partner_errorMessage" =>  " Partner details contains a collision. Please contact support",
                                );
                                array_push($messageDetails, $messageData);
                        }
                    }
                    
                }
                $successMessage = "success";
                $isSuccess = true;
            }
        }
        else
        {
            if($hostType === "company")
            {
                $st = $hostData[0][$fields];
                if($st === "primary" || $st === "initial")
                {
                    $errorMessage = "Please complete your registration before you could recieve or send messages";
                }
                else
                    $errorMessage = "you seem to be logged out. Please check your login status";
            }
            else
                    $errorMessage = "you seem to be logged out. Please check your login status";
                
        }
           
    }
    else
        $errorMessage = "Invalid request type";
    
    // echo json_encode(array(
    // "isSuccess" => $isSuccess,
    // "successMessage" => $successMessage,
    // "errorMessage" => $errorMessage,
    // "messages" => $message
    // ));
    // exit(0);
    echo json_encode(array(
    "isSuccess" => $isSuccess,
    "successMessage" =>$successMessage,
    "errorMessage" => $errorMessage,
    "messages" => $messageDetails
    // array(
        
    //     array(
    //         "id" => 3,
    //         "partnerToken" => hash("md5", time(), false),
    //         "partnerName" => "Noor Abdi",
    //         "partnerError" => false,
    //         "partner_errorMessage" => null,
    //         "messages" => array(
    //                 array(
    //                 "messageId" => 55,
    //                 "messageToken" => "4354fw34f4f345",
    //                 "messageType" => "text",
    //                 "messageContent" => "Hi there",
    //                 "messageTime" => "2018-12-21 14:12",
    //                 "messageStatus" => "read"
    //                 ),
    //                 array(
    //                 "messageId" => 55,
    //                 "messageToken" => "4354fw34f4sfdf345",
    //                 "messageType" => "text",
    //                 "messageContent" => "I am also good thanks",
    //                 "messageTime" => "2018-12-21 14:13",
    //                 "messageStatus" => "sent"
    //                 ),
    //                 array(
    //                 "messageId" => 55,
    //                 "messageToken" => "4354fasfsw34f4f345",
    //                 "messageType" => "text",
    //                 "messageContent" => "Yea I found it",
    //                 "messageTime" => "2018-12-21 14:14",
    //                 "messageStatus" => "sent"
    //                 ),
    //                 array(
    //                 "messageId" => 55,
    //                 "messageToken" => "4354fw34f4f34sdfsd5",
    //                 "messageType" => "text",
    //                 "messageContent" => "I wasnt kidding when I said that",
    //                 "messageTime" => "2018-12-21 14:18",
    //                 "messageStatus" => "sent"
    //                 ),
    //             ),
    //         ),
        
    //     ),
        
        )
    );
exit(0);
}
else
{
    echo json_encode(array(
    "isSuccess" => true,
    "successMessage" => "success",
    "errorMessage" => null,
    "messages" => null
    ));
    exit(0);
}
echo json_encode(array(
    "isSuccess" => true,
    "successMessage" => "success",
    "errorMessage" => null,
    "messages" =>
    array(
        
        array(
            "id" => 3,
            "userToken" => hash("md5", time(), false),
            "senderName" => "Noor Abdi",
            "messages" => array(
                    array(
                    "messageId" => 55,
                    "messageToken" => "4354fw34f4f345",
                    "messageType" => "text",
                    "messageContent" => "Hi there",
                    "messageTime" => "2018-12-21 14:12",
                    "messageStatus" => "read"
                    ),
                    array(
                    "messageId" => 55,
                    "messageToken" => "4354fw34f4sfdf345",
                    "messageType" => "text",
                    "messageContent" => "I am also good thanks",
                    "messageTime" => "2018-12-21 14:13",
                    "messageStatus" => "read"
                    ),
                    array(
                    "messageId" => 55,
                    "messageToken" => "4354fasfsw34f4f345",
                    "messageType" => "text",
                    "messageContent" => "Yea I found it",
                    "messageTime" => "2018-12-21 14:14",
                    "messageStatus" => "read"
                    ),
                    array(
                    "messageId" => 55,
                    "messageToken" => "4354fw34f4f34sdfsd5",
                    "messageType" => "text",
                    "messageContent" => "I wasnt kidding when I said that",
                    "messageTime" => "2018-12-21 14:18",
                    "messageStatus" => "read"
                    ),
                ),
            ),
        
        ),
        
        )
    );
exit(0);
?>