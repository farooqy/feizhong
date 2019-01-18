<?php


class Logger {
	protected $log_file = null;
	protected $log_error = null;
	protected $log_user = null;
	protected $full_path = "/home/ganacsig/feizhong.ganacsigroup.com/errors/";
	protected $fp = null;

	//status
	protected $log_failed = false;
	protected $failed_message = "No Logger Error";
	protected $fp_is_open = false;
	protected $initial_error = false;

	public function __construct($log_file ="registerError.log")
	{
		//create the file if it doesnt exist
		$this->log_file = $this->full_path.$log_file;
		if(file_exists($this->log_file) === false)
		{	
			$is_created = fopen($this->log_file, "a");
			if(!$is_created)
			{
				$error = "Failed to open/create the file, check 
				permission or the path";
				$this->Set_Error($error);
				$this->initial_error = true;
			}
			else
			{
				$this->fp = $is_created;
				$this->fp_is_open = true;
				$this->Close_File();
			}
		}
	}
		

	protected function Set_Error($error)
	{
		$this->log_failed = true;
		$this->failed_message = $error;
	}
	public function Failed_Message()
	{
		return $this->failed_message;
	}

	public function Write_Error($error)
	{
		if($this->fp_is_open === false)
		{
			$is_reopened = $this->Reopen_file();
			if($is_reopened === false)
				return false;
			else if(fwrite($this->fp, $error."\n"));
		}
		else
		{
			if(!fwrite($this->fp, $error))
			{
				$error = " Failed to write to the file, check 
				permission .";
				$this->Set_Error($error);
				return false;
			}
		}
	}
	public function Reopen_file()
	{
		try
		{
			$this->fp = fopen($this->log_file, "a");
		}
		catch(Exception $e)
		{
			$this->Set_Error("Failed to reopen the file for logging");
			return false;
		}
		$this->fp_is_open = true;
	}
	public function Close_File()
	{
		if($this->fp_is_open)
		{
			fclose($this->fp);
		}
		$this->fp_is_open = false;
	}
	public function Initial_Error()
	{
		return $this->initial_error;
	}
}
?>