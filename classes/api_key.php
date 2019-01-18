<?php 

$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root."/includes/randomcompat/lib/random.php");
require_once($root."/classes/SuperClass.php");
class Api_Key
{
    protected $api_id = 0;
    protected $api_key = null;
    protected $api_regdate = null;
    protected $api_expdate = null;
    protected $api_status = null;
    protected $api_keyspace ='0123456789abcdefghijklmnopqrstuv
    wxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected $api_keylength = 149;
    protected $error = null;
    protected $SuperUser= null;
    protected $table = "api_tokens";
    
    
    protected $error_status = false;
    protected $error_message = false;
    
    public function __construct()
    {
        $this->SuperUser = new Super_Class();
    }
    public function Generate_New_Api()
    {
        if($this->api_key !== null)
        {
            $this->Set_Error("The api has a data. Please reset
            the api key. ");
            return false;
        }
        try 
        {
            $this->api_key =  bin2hex(random_bytes($this->api_keylength));
        } 
        catch (TypeError $e) 
        {
            // Well, it's an integer, so this IS unexpected.
            $error = ("An unexpected type error has occurred"); 
        }
        catch (Error $e) 
        {
            // This is also unexpected because 32 is a reasonable integer.
            $error = ("An unexpected error has occurred");
        } 
        catch (Exception $e) 
        {
            // If you get this message, the CSPRNG failed hard.
            $error = ("Could not generate a random string. Is our OS secure?");
        }
        
        if($error !==  null)
        {
            $this->Set_Error($error);
            return false;
        }
        else
        {
            $is_saved = $this->Save_Key($this->api_key, "newapi");
            if($is_saved === true)
                return true;
            else
            {
                $this->Set_Error("Failed to save draft of the new api key::
                    ".$this->error_message);
                    return false;
            }
        }
            
    }
    
    public function Save_Key($apikey , $status='unverified')
    {
        //echo "something else";
        if($status === "unverified")
            $veriftype = "standard";
        else
            $veriftype = "existence";
            //echo "somethings";
        if($this->api_key === null && $status !== "unverified")
        {
            $this->error_status = true;
            $this->error_message = "Api key is null";
            return false;
        }
        else if(strlen($apikey) !== 298 )
        {
            $this->error_status = true;
            $this->error_message = "The api key generated is not valid ";
            return false;
        }
        else if($status === "unverified")
        {
            if($this->Verify_Key($apikey, $veriftype) === false )
            {
                return false;
            }
            $field= "api_status = 'active'";
            $condition = "api_key = '$apikey'";
            $is_saved = $this->SuperUser->Super_Update($this->table, $field, $condition);
            if($is_saved === true)
                return true;
            else
            {
                $this->Set_Error($this->SuperUser->Get_Message());
                return false;
            }
        }
        else if($status=='newapi')
        {
            $field = "api_key, api_regdate, api_expdate, api_status";
            $regtime = time();
            $exptime = $regtime+(60*60*24*14);//2 weeks
            $values = "'$apikey', $regtime, $exptime, 'unverified'";
            $is_saved = $this->SuperUser->Super_Insert($this->table, $field, $values);
            if($is_saved === true)
                return true;
            else
            {
                $this->Set_Error($this->SuperUser->Get_Message());
                return false;
            }
            
        }
        else
        {
            $this->Set_Error("Unknown status");
            return false;
        }
    }
    
    public function Verify_Key($apikey, $type="standard")
    {
        $fields = "*";
        $condition = "api_key = '$apikey'";
        $keyapi = $this->SuperUser->Super_Get($fields, $this->table, $condition, "api_id");
        if($keyapi === false)
        {
            $this->Set_Error("Failed to verify the api key ".$this->SuperUser->Get_Message());
            return false;
        }
        else if(is_array($keyapi) === false)
        {
            $this->Set_Error("The key to verify returned unknown data");
            return false;
        }
        else if(count($keyapi) > 1)
        {
            $this->Set_Error("Multiple keys exist");
            return false;
        }
        else if(count($keyapi) === 1 && $type === "standard")
        {
            if($keyapi[0]["api_status"] === "unverified")
                return true;
            else 
            {
                //print_r($keyapi);
                $this->Set_Error("Key status is unexpected type ".$keyapi[0]["api_status"]);
                return false;
            }
        }
        else if(count($keyapi) === 1 && $type === "existence" )
        {
            return true;
        }
        else if(count($keyapi) < 1)
        {
            $this->Set_Error("The api key does not exist");
            return false;
        }
        else
        {
            $this->Set_Error("Verifying type is not valid");
            return false;
        }
    }
    
    public function Set_Error($error)
    {
        $this->error_status = true;
        $this->error_message = $error;
    }
    public function Get_Message()
    {
        return $this->error_message ;
    }
    
    public function Get_Api_Key()
    {
        return $this->api_key;
    }
    
    public function Get_Keys()
    {
        $fields = "*";
        $condition = 1;
        $api_keys = $this->SuperUser->Super_Get($fields, $this->table, $condition, "api_id");
        if($api_Keys === false)
        {
            $this->Set_Error($this->SuperUser->Get_Message());
            return false;
        }
        else
            return $api_keys;
    }
    
}
?>