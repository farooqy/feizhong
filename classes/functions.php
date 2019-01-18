<?php //session_start();

//format time
function format_time($time=0, $format="d-m-Y")
{
    return gmdate($format,$time);
} 
 //hash functions
 function Get_Hash($str)
 {
    $hash = hash('md5', $str, false);
    return $hash;
 }
 
 //validate email
 function Validate_Email($email='')
 {
    $is_valid = filter_var($email, FILTER_VALIDATE_EMAIL);
    return $is_valid;
 }
 
 //sanitieze string
 function Sanitize_String($str)
 {
    return filter_var($str, FILTER_SANITIZE_STRING);
 }
 
 //validate integer
 function Validate_Int($int='')
 {
    return filter_var($int, FILTER_VALIDATE_INT);
 }

 function Initialize()
 {
 	$_SESSION['home_url'] = 'http://localhost/home/';
 	$_SESSION['dir_root'] = '/home/farooqy/Desktop/devs/home/';
 }
 
function CheckInjection($str)
{
	$injections = array("onclick", "click", "hover", "onhover");
	foreach ($injections as $key => $value) {
		if(stripos($str, $value))
		{
			//echo "found in jection at".stripos($str, $value);
			return true;
		}
	}
}

//if default key is used, hard to decrypt 
function EncryptOrDecrypt_This($str, $key=null, $action="encrypt", $custompath='')
{
	$keysPaths = "/home/farooqy/Desktop/devs/home/.keys";
	if(empty($custompath))
		$path = $keysPaths;
	else
		$path = $keysPaths.'/'.$custompath;
	putenv("GNUPGHOME=$path");
	$Res = gnupg_init();
	//echo "key: ".$key." path: ".$path;
	gnupg_seterrormode($Res,  GNUPG_ERROR_EXCEPTION);
	$details = array(
		"is_success" => false,
		"cipher" => '',
		"message" => 'NO ERROR',
	);
	try
	{
		if($action === "encrypt")
		{
			gnupg_addencryptkey($Res, $key);
			$endectext = gnupg_encrypt($Res, $str);
		}
		else 
		{
			gnupg_adddecryptkey($Res, $key, "");
			//echo "<br> key added succes <br>";
			$endectext = gnupg_decrypt($Res, $str);
		}
		$details["is_success"] = true;
		$details["cipher"] = $endectext;	
	}
	catch(Exception $e)
	{
		$details["message"] =  "$action Failed: ".$e->getMessage()." ".gnupg_geterror($Res)."<br>";
		return $details;
	}

	return $details;
}


//function from niCUpload.php 
function nicupload_error($msg) {
    echo nicupload_output(array('error' => $msg)); 
}

function nicupload_output($status, $showLoadingMsg = false) {
    $script = '
        try {
            '.(($_SERVER['REQUEST_METHOD']=='POST') ? 'top.' : '').'nicUploadButton.statusCb('.json_encode($status).');
        } catch(e) { alert(e.message); }
    ';
    
    if($_SERVER['REQUEST_METHOD']=='POST') {
        echo json_encode($status);
        //echo '<script>'.$script.'</script>';
    } else {
        echo json_encode($status);
        //echo $script;
    }
    
    if($_SERVER['REQUEST_METHOD']=='POST' && $showLoadingMsg) {      

echo <<<END
    <html><body>
        <div id="uploadingMessage" style="text-align: center; font-size: 14px;">
            <img src="http://js.nicedit.com/ajax-loader.gif" style="float: right; margin-right: 40px;" />
            <strong>Uploading...</strong><br />
            Please wait
        </div>
    </body></html>
END;

    }
    
    exit;
}

function nicupload_file_uri($filename) {
    return NICUPLOAD_URI.'/'.$filename;
}

function ini_max_upload_size() 
{
    $post_size = ini_get('post_max_size');
    $upload_size = ini_get('upload_max_filesize');
    if(!$post_size) $post_size = '8M';
    if(!$upload_size) $upload_size = '2M';
    
    return min( ini_bytes_from_string($post_size), ini_bytes_from_string($upload_size) );
}

function ini_bytes_from_string($val) 
{
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

function bytes_to_readable( $bytes ) 
{
    if ($bytes<=0)
        return '0 Byte';
   
    $convention=1000; //[1000->10^x|1024->2^x]
    $s=array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB');
    $e=floor(log($bytes,$convention));
    return round($bytes/pow($convention,$e),2).' '.$s[$e];
}

function Check_Data_From_Table($data, $required_fields=1)
{
    $status = array(
        "error" => false,
        "message"=> null
    );
    if($data === false)
    {
        $status["error"] = true;
        $status["message"] = "Query to get data failed";
    }
    else if(is_array($data) === false)
    {
        $status["error"] = true;
        $status["message"] = "Returned uknown data type";
    }
    else if(count($data) === 0)
    {
        $status["error"] = true;
        $status["message"] = "The data does not exist";
    }
    else if(count($data) !== $required_fields)
    {
        $status["error"] = true;
        $status["message"] = "Returned unequivalent numbers than required";
    }

    return $status;

}

function Curl_Request($url, $options=array(), $type="gif")
{
    $Curl = curl_init($url);
    $status = array(
        "result" => null,
        "error" => false,
        "message" => null
    );
    foreach ($options as $key => $value) {
        $opt = $value["option"];
        $bool = $value["bool"];
        curl_setopt($Curl, $opt, $bool);
    }
    $result = json_decode(curl_exec($Curl), true);
    if($result === false)
    {
        $status["error"] = true;
        $status["message"] = "Failed to get curl request";
    }    
    else if($type === "gif")
    {
        //$result = json_decode($result, true);
        if(!isset($result["meta"]) || !isset($result["pagination"]) || !isset($result["data"]))
        {
            $status["error"] = true;
            $status["message"] = "Request returned unknown data";
        }
        else
            $status["result"] = $result;
    }
    else if($type ===  "gifid")
    {
        //print_r($result);
        //$result = json_decode($result, true);
        if(!isset($result["meta"]) || !isset($result["data"]))
        {
            $status["error"] = true;
            $status["message"] = "Request to verify gif id returned unknown data";
        }
        else
            $status["result"] = $result;
    }

    return $status;

}

function Format_Postcontent($content)
{
    $length = strlen($content);
    if($length < 500)
        return $content;
    //secho $length;
    $toshow = substr($content, 0, 500);
    //echo ' ::: '.$toshow.PHP_EOL;
    $tohide = substr($content, 501);
    //echo '::::'.$tohide.PHP_EOL;
    $div = "<div class='toshow'> $toshow </div> ";
    $div = $div."<div class='hidePost'> $tohide </div> ";
    $div = $div."<div class='hidetrigger' target='show'> Read More </div> ";

    return $div;
}
?>