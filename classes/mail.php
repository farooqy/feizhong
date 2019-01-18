<?php
require_once("database.php");
class Mail {
  
  protected $to =null;
  protected $from=null;
  protected $message = null;
  protected $headers = null;
  protected $params = null;//extra paramaters
  protected $is_sent = 'failed';
  protected $sent_date = null;
  
  protected $data_isset = false;
  protected $sent_status = false;
  protected $error_status = false;
  protected $error_message = null;
  protected $conn = ""; //hold the db query arrow 
  protected $db = "";//holds the database object
  protected $db_status = false; //not conencted initially
  
  
  public function __construct()
  {
    $this->db = new connection();
    $status = $this->db->connection_status();
    if($status)
    {
      $this->conn = $this->db->connection();
      $this->db_status = true; //db connection success
    }
    else
    {
      $this->error_status = true;
      $this->error_message = "Connection failed 
      ".$this->db->get_error();
      echo $this->error_message;
      exit(0);
    }
  }
  public function db_status()
  {
    return $this->db_status;
  }
  public function get_message()
  {
    return $this->error_message;
  }
  //main function that interacts with the database
  private function make_query($query, $type="insert")
  {
    try
    {
      $q = $this->conn->query($query);
    }
    catch(PDOException $e)
    {
      $this->error_status = true;
      $this->error_message = "Failed to insert new
      sheekh: ".$e->getMessage();
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
      $this->error_message = "Unknown query type";
      return false;
    }
    
  }
  //mail sender
  public function send_mail()
  {
    if(!$this->data_isset)
    {
      $this->error_status = true;
      $this->error_message = "The details for the mail
      are not set.";
      return false;
    }
    try
    {
      $m = mail($this->to, $this->subject, $this->message,
                $this->headers, $this->params);
    }
    catch(Exception $e)
    {
      $this->error_status= true;
      $this->error_message = "Failed sending email:
      ".$e->getMessage();
      //echo $this->error_message;
      $this->is_sent = 'failed';
      return false;
    }
    $this->is_sent = 'success';
    return true;
  }
  
  //database setters
  public function set_mail_data($to, $subject, $message, 
                                $headers=null, $params = null)
  {
    $this->to = $to;
    $this->subject = $subject;
    $this->message = $message;
    $this->headers = $headers;
    $this->params = $params;
    $this->m_date = time();
    $this->data_isset= true;
  }
  //database log
  public function log_email()
  {
    $sql = "INSERT INTO `mails` 
    (m_to, m_subject, m_message, 
    m_headers, m_params, m_status, m_date) 
    VALUES('$this->to', '$this->subject', '$this->message',
    '$this->headers', '$this->params',
    '$this->is_sent', $this->m_date)";
    
    $query = $this->make_query($sql);
    return $query;
  }
  
}
?>