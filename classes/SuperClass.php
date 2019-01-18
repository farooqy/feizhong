<?php
require_once("database.php");
require_once("query_handle.php");
require_once("logger.php");
require_once("functions.php");

class Super_Class 
{
	protected $error_status = False; 
	protected $error_message = "No user Error";
	protected $error_file = "user_error";
	protected $Logger = null;

	private $SQL_ERROR = "The query to process your request contains an 
	error, please contact administrator";

	public function __construct()
  	{
  		$this->error_file = "superClass.error";
  		$this->Logger = new Logger($this->error_file);
    	$this->db = new connection();
	    $status = $this->db->connection_status();
	    if($status)
	    {
	      $this->conn = $this->db->connection();
	      $this->db_status = true; //db connection success
	      $this->Query_Handle =new Query_Handle($this->db->connection());
	    }
	    else
	    {
	      $this->error_status = true;
	      $this->error_message = "Connection failed";
	    }
  	}
	public function Db_Status()
	{
		return $this->db_status;
	}
	public function Get_Message()
	{
		return $this->error_message;
	}
	public function Set_Error($error)
	{
		$this->error_status = true;
		$this->error_message = $error;
	}
	public function Log_Error($error)
	{
		if($this->Logger->Initial_Error() === true)
		{
			$error = $this->error_message." Logging Intial failed: 
			".$this->Logger->Failed_Message()." MAIN ERROR: ".$error;
			$this->Set_Error($error);
		}
		else
		{
			$is_logged = $this->Logger->Write_Error($error);
			if($is_logged === false)
			{
				$error = $this->error_message." Logging failed: 
				".$this->Logger->Failed_Message()." MAIN ERROR: ".$error;
				$this->Set_Error($error);
			}
		}
	}

	public function Process_Error($additional_error ='')
	{
    	$error = $this->Query_Handle->Query_Message();
    	if(!empty($additional_error))
		    $this->Set_Error($additional_error.' & '.$this->SQL_ERROR);
		else
		    $this->Set_Error($this->SQL_ERROR);
		$this->Log_Error($error);

		$this->Log_Error($error);
	}
	public function Super_Get($field,$table, $condition=1, $order='', $debug = false)
	{
		if(is_array($table))
		{
			$tbs = "";
			foreach ($table as $key => $tvalue) {
				if(empty($tbs))
					$tbs = $tvalue;
				else
					$tbs .= ", `$tvalue` ";
			}
			$sql = "SELECT $field FROM $tbs WHERE $condition 
			ORDER BY $order";
			//echo $sql;
			//echo "<br><br>";
		}
		else
			$sql = "SELECT $field FROM `$table` WHERE $condition 
			ORDER BY $order";
		if($debug)
			echo "<br>".$sql."<br>";
		$query = $this->Query_Handle->Make_Query($sql,"fetch");
		if($query === false)
			$this->Process_Error("Query failed: ");
		else
			return $query;
		return false;
	}

	public function Super_Insert($table,$field, $values, $debug =false)
	{
		$sql = "INSERT INTO `$table` ($field) VALUES($values)";
		//echo $sql;
		if($debug === true)
			echo $debug;
		$is_saved = $this->Query_Handle->Make_Query($sql);
		if($is_saved === false)
			$this->Process_Error("Insertion Failed: ");
		else
			return $is_saved;
		return false;
	}

	public function Super_Update($table, $field, $condition)
	{
		$sql = "UPDATE `$table` SET $field WHERE $condition";
		$is_updated = $this->Query_Handle->Make_Query($sql);
		if($is_updated === false)
			$this->Process_Error("Update Failed: ");
		else
			return $is_updated;
		return false;
	}
}
?>