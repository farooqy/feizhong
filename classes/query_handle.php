<?php
class Query_Handle {
	protected $query_type = array("fetch", "insert");
	protected $current_query = null;
	protected $query_history = array();
	protected $query_cursor = null;

	

	private $INVALID_QUERY_TYPE = "The query type is not recognized";
	protected $query_error = false;
	protected $query_message = "No Query Error";

	public function __construct($cursor)
  	{
    	$this->query_cursor = $cursor;
  	}

	public function Make_Query($query=null, $type="insert")
	{
		if(!in_array($type, $this->query_type))
		{
			$this->query_error = true;
			$this->query_message = $this->INVALID_QUERY_TYPE;

		}
		try
		{
			//$query = $this->query_cursor->quote($query);
			$q = $this->query_cursor->query($query);
			//echo "query sucess";
	    }
	    catch(PDOException $e)
	    {
	    	$this->query_error = true;
	    	$this->query_message = "Failed to $type : ".$e->getMessage();
	    	//echo "Query failed";
	    	return false;
	    }
			
	    if($type === "insert")
	    	return true;
	    else if($type === "fetch")
	    {
	    	$return_data = $q->fetchAll(PDO::FETCH_ASSOC);
	      	return $return_data;
	    }
	    else
	    {
	    	$this->query_message = $this->INVALID_QUERY_TYPE;
	      	return false;
	    }
	    
	 }

	 public function Query_Message()
	 {
	 	return $this->query_message;
	 }
	 public function Qeury_Error()
	 {
	 	return $this->query_error;
	 }


	 public function Quote_String($str)
	 {
	 	return $this->query_cursor->quote($str);
	 }
}
?>