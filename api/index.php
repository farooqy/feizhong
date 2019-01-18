<?php session_start();
$root = $_SERVER['DOCUMENT_ROOT'];
$_SESSION["admin"] = true;
if(isset($_SESSION["admin"]) === false)
{
    echo json_encode(array(false, "You have no permission to do this"));
    exit(0);
}
else if(isset($_POST["genapi"]))
{
    require_once($root."/classes/api_key.php");
    $Api = new Api_Key();
    //$is_success =$Api->Generate_New_Api();
    //print_r($is_success);
    $is_success = $Api->Generate_New_Api();
    //print_r($is_success);
    //echo $Api->Get_Api_Key();
    if($is_success === false)
    {
        echo json_encode(array(false, $Api->Get_Message()));
        exit(0);
    }
    else
    {
        
        echo json_encode(array(true, ($Api->Get_Api_Key())));
        exit(0);
    }
    
}
else if(isset($_POST["saveapi"]) && isset($_POST["api"]))
{
    require_once($root."/classes/api_key.php");
    require_once($root."/classes/functions.php");
    
    $Api = new Api_Key();
    $key = Sanitize_String($_POST["api"]);
    //echo "somethinss";
    $is_saved = $Api->Save_Key($key);
    
    if($is_saved === false)
    {
        echo json_encode(array(false, $Api->Get_Message()));
        exit(0);
    }
    else
    {
        echo json_encode(array(true, "success"));
        exit(0);
    }
}
else
{
    echo json_encode(array(false, 'You are starving'));
    exit(0);
}
?>