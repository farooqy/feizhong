<?php 

//Connecting to the database
class connection {
  private $servername = "custsql-ipg60.eigbox.net";
  private $dsn ="mysql:host=localhost;dbname=ganacsig_feizhong";
  private $username = "ganacsig_noor";
  private $password ="no1@all01";
  private $conn;
  protected $error ="";
  protected $connection_status = false;
  public function __construct(){
      try
      {
          $conn = new PDO($this->dsn, $this->username, $this->password);
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          
      }
      catch(PDOException $e)
      {
          echo ("connection failed "). $e->getMessage();
          
      }
      //echo "success";
      $this->conn = $conn;
    $this->connection_status = true;
  }
  public function connection (){
      return $this->conn;
  }
  public function connection_status()
  {
    return $this->connection_status;
  }
  protected function authenticate($user)
  {
      try
      {
          $s = $this->conn->query("SELECT password FROM `users` WHERE email = '$user'");
      }
      catch(PDOException $e)
      {
          $this->error = "Unable to to authentication ".$e->getMessage();
          return false;
      }
      $p = $s->fetchALL(PDO::FETCH_ASSOC);
      if(count($p) == 0)
      {
          $this->error = "user session is not authenticated and existing user";
          return false;
      }
      return $p[0]["password"];
  }
  public function is_authentic($user)
  {
      if(empty($user))
      {
          echo "empty user ";
          exit(0);
      }
      $p =$this->authenticate($user) ;
      if($p)
      return $p;
      else
      return false;
  }
  public function get_error()
  {
      return $this->error;
  }
}
?>
