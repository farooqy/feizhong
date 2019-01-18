<?php 
$root = $_SERVER["DOCUMENT_ROOT"];
require_once($root."/classes/functions.php");
if(isset($_GET["company_name"]))
{
    $comp_name = Sanitize_String($_GET["company_name"]);
    $error_trigger = "Incomplete data";
    
    
    if(isset($error_trigger) === true)
    echo json_encode(array(false, "There was xyz error"));
    else
    echo json_encode(array(true, "success"));
}

?>