<?php 
class Uploader
{
  protected $_name = null;
  protected $_type = null;
  protected $_size = null;
  protected $_temp = null;
  
  //formats
  protected $_default_size = 10000000;
  protected $_default_type = array("image/jpg", "image/png",
                                    "image/jpeg");
  //to set
  protected $_given_type = null;
  protected $_upload_dir = null;
  protected $_url_path = null;
  
  //errors
  protected $error_status = false;
  protected $error_message = "NO ERROR";
  
  //holder
  protected $_to_unlink = array();
  
  
  public function __construct($file_array)
  {
    $this->_name = $file_array["f_name"];
    $this->_type = $file_array["f_type"];
    $this->_size = $file_array["f_size"];
    $this->_temp = $file_array["f_temp"];
  }
  //setters
  public function _set_format($format)
  {
    $this->_given_type = $format;
  }
  public function _set_upload_dir($dir)
  {
    $this->_upload_dir = $dir;
  }
  public function _set_url_path($path)
  {
    $this->_url_path = $path;
  }
  
  //checkers
  protected function _is_valid_type()
  {
    if($this->_given_type !== null)
      $in_array = in_array($this->_type, $this->_given_type);
    else
      $in_array=in_array($this->_type,$this->_default_type);
    if($in_array === false)
    {
      $this->error_message = " The file".$this->_name." 
       is not a valid format. ";
      $this->error_status = true;
      return false;
    }
    else
      return true;
  }
  protected function _is_allowed_size()
  {
    if($this->_size > $this->_default_size)
    {
      $this->error_message ="The size of  ".$this->_name."
       exceeds the limited size";
      $this->error_status = true;
      return false;
    }
    else
      return true;
  }
  protected function _not_existing()
  {
    $file = $this->_upload_dir.$this->_name;
    if(!file_exists($file))
      return true;
    else
    {
      $this->error_message = "The file ".$this->_name." 
      already exist.";
      return false;
    }
  }
  public function check_validity()
  {
    $is_valid = $this->_is_valid_type();
    if($is_valid === false)
      return false;   
    else
    {
      $is_valid = $this->_is_allowed_size();
      if($is_valid === false)
        return false;
      else
      {
        $is_valid = $this->_not_existing();
        if($is_valid === false)
          return false;
        else
          return true;
      }
    }
  }
  
  
  //getters
  public function Get_Message()
  {
    return $this->error_message;
  }
  public function get_error_status()
  {
    return $this->error_status;
  }
  public function get_file_url()
  {
    $file = array(
      "url" => $this->_url_path.$this->_name,
      "type" => $this->_type,
      "size" =>$this->_size,
      "dir"=>$this->_upload_dir.$this->_name
    );
    return $file;
  }
  
  //main functions
  
  public function _unlink_files()
  {
    //print_r($this->_to_unlink);
    $num_files = count($this->_to_unlink);
    for($i=0; $i<$num_files; $i++)
    {
      unlink($this->_to_unlink[$i]);
    }
  }
  
  public function upload()
  {
    $dir = $this->_upload_dir.$this->_name;
    $upload_status = move_uploaded_file($this->_temp,$dir);
    if($upload_status === true)
    {
      //echo "pushiing";
      array_push($this->_to_unlink, $dir);
      //print_r($this->_to_unlink);
      return true;
    }
    else
    {
      $this->error_message = "file upload has failed";
      $this->error_status = true;
      $this->_unlink_files();
      return false;
    }
  }
  
  public function Get_Dir()
  {
      return $this->_upload_dir.$this->_name;
  }
}
?>