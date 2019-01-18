<?php 
class fileHandler
{
  protected $files = "";
  protected $error_status = false;
  protected $error_message = "NO ERROR";
  protected $currentFile = "";
  protected $currentTemp = "";
  protected $quarantines = array();
  protected $fileCounter = 0;
  protected $totalFiles = 0;
  protected $targetDir = "";//defaul is current as the script using the class object
  protected $urlPath = "";
  protected $sizeLimit = 100000000;
  protected $allowedFormats = array("image/jpg", "image/png", "image/jpeg");
  protected $id = "";
  protected $name= "";
  public $type = ""; //type, whether its floorplan or normal photos
  protected $quarantineCalled = false;
  protected $totalCheck = 0;
  public function __construct($files,$type="", $id=0, $name="")
  {
      //echo "<br>files: <br>";
      $this->files = $files;
      $this->totalFiles = count($this->files);
      $this->id = $id; //such as apartment id
      $this->name = $name; //such as apartment name
      $this->type = $type;
     //print_r($this->files);
    //echo "size: ".$this->totalFiles."<br>";
  }
  public function setDirectory($path)
  {
      $this->targetDir = $path;
  }
  public function setUrlPath($url)
  {
    $this->urlPath = $url;
  }
  public function getType()
  {
      return $this->type;
  }
  public function getCount()
  {
      return $this->totalFiles;
  }
  public function currentIndex()
  {
      return $this->fileCounter;
  }
  public function qStatus()
  {
      return $this->quarantineCalled;
  }
  public function getChecked()
  {
      return $this->totalCheck;
  }
  public function setAllowed($formats)
  {
    $this->allowedFormats = $formats;
  }
  public function setSizeLimit($size)
  {
    $this->sizeLimit = $size;
  }
  public function hasNonImage($file, $type)
  {
      //echo "<br> checking nonImage ".$file;
      if(getimagesize($file) == false && in_array($type, $this->allowedFormats))
      {
          $this->error_status = true;
          $this->error_message = "The file with the name ".$file." is not valid ";
          return true;
      }
      return false;
  }

  public function fileExists($file)
  {
     // echo "<br> Checking file existing: ".$file;
      if(file_exists($this->targetDir."/".$file))
      {
          $this->error_status = true;
          $this->error_message = "The file with the name ".$this->currentFile." already exist";
          return true;
      }
      else 
      return false;
  }
  public function exceedLimitSize($size)
  {
      //echo "<br> checking size: ".$size;
      if($size > $this->sizeLimit)//accept only less than 1MB files
      {
          $this->error_status = true;
          $this->error_message = "The file with the name ".$this->currentFile." exceeds size limit: ";
          return true;
      }
      else 
      return false;
  }
  public function isInValidFormat($type)
  {
     // echo "<br> checking validity: ".$type;
      if(!in_array($type, $this->allowedFormats) && $this->name !== "cover")
      {
          $this->error_status = true;
          $this->error_message = "The file with the name ".$this->currentFile." is invalid format";
          return true;
      }
      else 
      return false;
  }
  public function uploadFile($fileTemp)
  {
      if(move_uploaded_file($fileTemp, $this->currentFile))
      {
         // echo "<br> SUCCESSFULL UPLOAD ".$this->fileCounter;
          $this->fileCounter = $this->fileCounter + 1;
          array_push($this->quarantines, $this->currentFile);
          return true;
      }
      else
      {
          //an error has occured, so remove the files in quarantine
          //echo "<br> quarantine initiated: <br>";
          if(count($this->quarantines) > 0)
          {
              $quarantine_status = $this->initiateQuarantine();
              $this->quarantineCalled = true;
              if($quarantine_status === "failed")
              return "failed"; //quarantine itself failed
              else
              return false;
          }
      }
  }
  public function unlinkFile($filepath)
  {
      try
      {
          $q = unlink($filepath);
      }
      catch(Exception $e)
      {
          $this->error_status = true;
          $this->error_message = "failed to unlink ".$e->getMessage();
          return false;
      }
      return true;
  }
  protected function initiateQuarantine()
  {
      $quarantineCount = count($this->quarantines);
      //print_r($this->quarantines);
      //echo "<br> I am executed <br>";
      for($i=0; $i<$quarantineCount; $i++)
      {
          $unl = $this->unlinkFile($this->quarantines[$i]);
          if(!$unl)
          {
              $this->error_status = true;
              $this->error_message = $this->error_message." failed to unlink file ".$this->quarantines[$i];
              return "failed";
          }
      }
      return true;
  }
  public function checkRequirements()
  {
      //echo "files checked: <br>";
    //print_r($this->files);
    $name = $this->files["name"];
    $tmp_name = $this->files["tmp_name"];
    $type = $this->files["type"];
    $size = $this->files["size"];
    //echo "checking: ".$name."<br>";
    $this->currentFile = $name;
    if($this->hasNonImage($tmp_name, $type) || $this->fileExists($name) || $this->exceedLimitSize($size))
    return false;
    if($this->isInValidFormat($type))
    return false;

    $this->totalCheck = $this->totalCheck +1;
    return true;
  }
  public function initiateUpload()
  {
      $name = $this->files["name"];
      $tmp_name = $this->files["tmp_name"];
      $upload_status_error = false;
      //echo "<br> about to upload $this->totalFiles files <br>";
      $this->currentFile = $this->targetDir."/". $this->files["name"];
      $this->currentTemp = $this->files["tmp_name"];
      $upload_status = $this->uploadFile($this->currentTemp);
      //echo "<br> RETURN STATUS FOR EACH UPLAOD:: <br>";
      //print_r($upload_status);
      //echo "<br>";
      if($upload_status === false)
      {
          $upload_status_error = true;
          return array($upload_status_error,false);
      }
      else if((string)$upload_status === "failed")
      {
          $upload_status_error = true;
          return array($upload_status_error, $upload_status);
      }
      return array($upload_status_error,true);

  }
  public function getPhotoDetails()
  {
    $photo = array(
        "name"=>$this->name,
        "id" =>$this->id,
        "url" => "https://noor-saifullahh78719901.codeanyapp.com/jtox/".$this->urlPath.$this->files["name"],
        "type" => $this->type
        );

      //array_push($this->$photos, $photo);
      return $photo;
  }
  public function get_message()
  {
      return $this->error_message;
  }
}
 ///@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 ///@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 ///@@@@@@@@@@@@@@@@@@@@@@@@@ READY FOR SECOND VERSION @@@@@@@@@@@@@@@@@@@@@@@@
 ///@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 ///@@@@@@@@@@@@@@@@@@@@@@@@@   BY MOHAMED NOOR ABDI   @@@@@@@@@@@@@@@@@@@@@@@@
 ///@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 ///@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
 ///@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
class fileHandlerV2
{
  
  protected $files = "";
  protected $error_status = false;
  protected $error_message = "NO ERROR";
  protected $currentFile = "";
  protected $currentTemp = "";
  protected $quarantines = array();
  protected $fileCounter = 0;
  protected $totalFiles = 0;
  protected $targetDir = "";//defaul is current as the script using the class object
  protected $urlPath = "";
  protected $allowedFormats = array("image/jpg", "image/png", "image/jpeg");
  protected $id = "";
  protected $name= "";
  public $type = ""; //type, whether its floorplan or normal photos
  protected $quarantineCalled = false;
  protected $totalCheck = 0;
  public function __construct($files,$type="", $id=0, $name="")
  {
      //echo "<br>files: <br>";
      $this->files = $files;
      $this->totalFiles = count($this->files["name"]);
      $this->id = $id; //such as apartment id
      $this->name = $name; //such as apartment name
      $this->type = $type;
     //print_r($this->files);
    //echo "size: ".$this->totalFiles."<br>";
  }
  public function setDirectory($path)
  {
      $this->targetDir = $path;
  }
  public function setUrlPath($url)
  {
    $this->urlPath = $url;
  }
  public function getType()
  {
      return $this->type;
  }
  public function getCount()
  {
      return $this->totalFiles;
  }
  public function currentIndex()
  {
      return $this->fileCounter;
  }
  public function qStatus()
  {
      return $this->quarantineCalled;
  }
  public function getChecked()
  {
      return $this->totalCheck;
  }
  public function hasNonImage($file)
  {
      //echo "<br> checking nonImage ".$file;
      if(getimagesize($file) == false)
      {
          $this->error_status = true;
          $this->error_message = "The file with the name ".$file." is not valid image";
          return true;
      }
      return false;
  }

  public function fileExists($file)
  {
     // echo "<br> Checking file existing: ".$file;
      if(file_exists($this->targetDir."/".$file))
      {
          $this->error_status = true;
          $this->error_message = "The file with the name ".$this->currentFile." already exist";
          return true;
      }
      else 
      return false;
  }
  public function exceedLimitSize($size)
  {
      //echo "<br> checking size: ".$size;
      if($size > 5000000)//accept only less than 1MB files
      {
          $this->error_status = true;
          $this->error_message = "The file with the name ".$this->currentFile." exceeds size limit";
          return true;
      }
      else 
      return false;
  }
  public function isInValidFormat($type)
  {
     // echo "<br> checking validity: ".$type;
      if(!in_array($type, $this->allowedFormats))
      {
          $this->error_status = true;
          $this->error_message = "The file with the name ".$this->currentFile." is invalid format";
          return true;
      }
      else 
      return false;
  }
  public function uploadFile($fileTemp)
  {
      if(move_uploaded_file($fileTemp, $this->currentFile))
      {
         // echo "<br> SUCCESSFULL UPLOAD ".$this->fileCounter;
          $this->fileCounter = $this->fileCounter + 1;
          array_push($this->quarantines, $this->currentFile);
          return true;
      }
      else
      {
          //an error has occured, so remove the files in quarantine
          //echo "<br> quarantine initiated: <br>";
          if(count($this->quarantines) > 0)
          {
              $quarantine_status = $this->initiateQuarantine();
              $this->quarantineCalled = true;
              if($quarantine_status === "failed")
              return "failed"; //quarantine itself failed
              else
              return false;
          }
      }
  }
  public function unlinkFile($filepath)
  {
      try
      {
          $q = unlink($filepath);
      }
      catch(Exception $e)
      {
          $this->error_status = true;
          $this->error_message = "failed to unlink ".$e->getMessage();
          return false;
      }
      return true;
  }
  protected function initiateQuarantine()
  {
      $quarantineCount = count($this->quarantines);
      //print_r($this->quarantines);
      //echo "<br> I am executed <br>";
      for($i=0; $i<$quarantineCount; $i++)
      {
          $unl = $this->unlinkFile($this->quarantines[$i]);
          if(!$unl)
          {
              $this->error_status = true;
              $this->error_message = $this->error_message." failed to unlink file ".$this->quarantines[$i];
              return "failed";
          }
      }
      return true;
  }
  public function checkRequirements()
  {
      //echo "files checked: <br>";
    //print_r($this->files);
      for($i=0; $i<$this->totalFiles; $i++)
      {
          $name = $this->files["name"][$i];
          $tmp_name = $this->files["tmp_name"][$i];
          $type = $this->files["type"][$i];
          $size = $this->files["size"][$i];
          //echo "checking: ".$name."<br>";
          $this->currentFile = $name;
          if($this->hasNonImage($tmp_name) || $this->fileExists($name) || $this->exceedLimitSize($size))
          return false;
          if($this->isInValidFormat($type))
          return false;

          $this->totalCheck = $this->totalCheck +1;
      }
      return true;
  }
  public function initiateUpload()
  {
      $name = $this->files["name"];
      $tmp_name = $this->files["tmp_name"];
      $upload_status_error = false;
      //echo "<br> about to upload $this->totalFiles files <br>";
      for($i=0; $i<$this->totalFiles; $i++)
      {

          $this->currentFile = $this->targetDir."/". $this->files["name"][$i];
          $this->currentTemp = $this->files["tmp_name"][$i];
          $upload_status = $this->uploadFile($this->currentTemp);
          //echo "<br> RETURN STATUS FOR EACH UPLAOD:: <br>";
          //print_r($upload_status);
          //echo "<br>";
          if($upload_status == false)
          {
              $upload_status_error = true;
              return array($upload_status_error,false);
          }
          else if((string)$upload_status === "failed")
          {
              $upload_status_error = true;
              return array($upload_status_error, $upload_status);
          }
         return array($upload_status_error,true);
      }

  }
  public function getPhotoDetails()
  {
      $photos = array();
      for($i=0; $i<$this->totalFiles; $i++)
      {
          $photo = array(
              "name"=>$this->name,
              "id" =>$this->id,
              "url" => "https://noor-saifullahh78719901.codeanyapp.com/jtox/".$this->urlPath.$this->files["name"][$i],
              "type" => $this->type
              );

          array_push($photos, $photo);
      }
      return $photos;
  }
  public function get_message()
  {
      return $this->error_message;
  }
}
?>