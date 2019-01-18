<?php 
error_reporting(E_ALL & ~E_NOTICE);
require_once("classes/api_key.php");
require_once("classes/functions.php");
$Api = new Api_Key();
$apikeys = $Api->Get_Keys();
if($apikeys === false)
    $error_trigger = "Failed to get api keys: ".$Api->Get_Message();
else if(is_array($apikeys) === false)
    $error_trigger = "api keys returned unknown data";
else if(count($apikeys) <= 0)
    $error_trigger = "Api keys are empty";
else
    //api keys are thre
    
?>

<html>
    <head>
        <title>API CONTROL</title>
        <script type="text/javascript" src="js/jQuery.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/gens.css">
        <link rel="stylesheet" href="css/home.css">
    </head>
    <body class="container">
        <div class="row"> <h3> API CONTROL </h3></div>
        <?php
        if(isset($error_trigger) === true)
        {
            unset($apikeys);
            ?>
        <div class="row Error_Holder">
            ERROR: <?php echo $error_trigger; ?>
        </div>
            <?php
        }
        ?>
        <table class="row table">
            <thead>
                <th>#</th>
                <th>Api ID</th>
                <th>Register Date</th>
                <th>Expiry Date</th>
                <th>Status</th>
            </thead>
            <?php
            if(isset($apikeys) === true)
            {
                ?>
            <tbody>
                <?php
            
                for($num=0; $num<count($apikeys); $num++)
                {
                    $apinum = $num+1;
                    $api_id = $apikeys[$num]["api_id"];
                    $api_regdate = format_time($apikeys[$num]["api_regdate"],"d-M-Y H:i:s");
                    $api_expdate = format_time($apikeys[$num]["api_expdate"],"d-M-Y H:i:s");
                    $api_status = $apikeys[$num]["api_status"];
                    ?>
                <tr class="tr">
                    <td><?php echo $apinum ?></td>
                    <td><?php echo $api_id ?></td>
                    <td><?php echo $api_regdate ?></td>
                    <td><?php echo $api_expdate ?></td>
                    <td><?php echo $api_status ?></td>
                </tr>
                    <?php
                }
                ?>
                
            </tbody>
                
                <?php
            }
            ?>
           
        </table>
        <div class="row">
            <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
                    
                <button class="btn btn-primary mar-t-10 gen-api">Generate Key</button>
                <div class="row">
                    <textarea disabled name="apikey" placeholder="Api Key" rows="5"
                    class="row api-text"></textarea>
                </div>
                <button class="btn btn-primary mar-t-10 save-api">Save Key</button>
            </div>
        </div>
        
        
        <div class="container-fluid">
        
            <div class="row loader">
              <div class="col-xs-0 col-sm-0 col-md-4 col-lg-4">
    
              </div>
              <div class="col-xs-0 col-sm-0 col-md-4 col-lg-4 text-center">
                <div class="loader-load row">
                </div>
                <div class="row text-load">
                  Loading...
                </div>
              </div>
              <div class="col-xs-0 col-sm-0 col-md-4 col-lg-4">
    
              </div>
            </div>
        </div>
    </body>
</html>