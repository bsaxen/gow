<?php
//=============================================
// File.......: gowServer.php
// Date.......: 2019-02-15
// Author.....: Benny Saxen
// Description: Glass Of Water Server
//=============================================

//=============================================
// Library
class gowDoc {
    public $sys_ts;
    
    public $action;
    public $topic;
    public $wrap;
    public $period;
    public $url;
    public $platform;
    public $ssid;
    public $tags;
    public $desc;
    
    public $no;
    public $dev_ts;
    public $rssi;
    public $payload;
    public $fail;
    public $log;
}

$obj = new gowDoc();

//=============================================
$conf_action_file_name = 'action.gow';
//=============================================
$date         = date_create();
$obj->sys_ts  = date_format($date, 'Y-m-d H:i:s');

//=============================================
function contains($needle, $haystack)
// returns true if $needle is a substring of $haystack
//=============================================
{
    return strpos($haystack, $needle) !== false;
}
//=============================================
function saveStaticData($obj)
//=============================================
{
  $f_file = $obj->topic.'/static.json';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "{\"gow\": {\n");
        fwrite($doc, "   \"sys_ts\":   \"$obj->sys_ts\",\n");
        fwrite($doc, "   \"desc\":     \"$obj->desc\",\n");
        fwrite($doc, "   \"tags\":     \"$obj->tags\",\n");
        fwrite($doc, "   \"action\":   \"$obj->action\",\n");
        fwrite($doc, "   \"topic\":    \"$obj->topic\",\n");
        fwrite($doc, "   \"wrap\":     \"$obj->wrap\",\n");
        fwrite($doc, "   \"period\":   \"$obj->period\",\n");
        fwrite($doc, "   \"url\":      \"$obj->url\",\n");
        fwrite($doc, "   \"platform\": \"$obj->platform\",\n");
        fwrite($doc, "   \"ssid\":     \"$obj->ssid\"\n");
        fwrite($doc, "}}\n ");
        fclose($doc);
  }
  return;
}
//=============================================
function saveDynamicData($obj)
//=============================================
{
  $f_file = $obj->topic.'/dynamic.json';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "{\"gow\": {\n");
        fwrite($doc, "   \"sys_ts\":    \"$obj->sys_ts\",\n");
        fwrite($doc, "   \"dev_ts\":    \"$obj->dev_ts\",\n");
        fwrite($doc, "   \"no\":        \"$obj->no\",\n");
        fwrite($doc, "   \"rssi\":      \"$obj->rssi\",\n");
        fwrite($doc, "   \"fail\":      \"$obj->fail\"\n");
        fwrite($doc, "}}\n ");
        fclose($doc);
  }
  return;
}
//=============================================
function savePayloadData($obj)
//=============================================
{
  $f_file = $obj->topic.'/payload.json';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "{\"gow\": {\n");
        fwrite($doc, "   \"payload\":     $obj->payload\n");
        fwrite($doc, "}}\n ");
        fclose($doc);
  }
  return;
}
//=============================================
function saveLog($obj)
//=============================================
{
  $f_file = $obj->topic.'/log.gow';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->sys_ts $obj->log\n");
        fclose($doc);
  }
  return;
}
//=============================================
function initLog($obj)
//=============================================
{
  $f_file = $obj->topic.'/log.gow';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "$obj->sys_ts Created\n");
        fclose($doc);
  }
  return;
}
//=============================================
function readActionFile($action_file)
//=============================================
{
  $file = fopen($action_file, "r");
  if ($file)
  {
      $result = ":";
      while(! feof($file))
      {
        $line = fgets($file);
        //sscanf($line,"%s",$work);
        $result = $result.$line;
      }
      fclose($file);
      $result = $result.":";
      // Delete file
      if (file_exists($action_file)) unlink($action_file);
  }
  else
  {
      $result = ":void:";
  }
  return $result;
}
//=============================================
function readActionFileList($topic)
//=============================================
{
  $result = ' ';
  $do = 'ls '.$topic.'/*_gow.action > '.$topic.'/action.work';
  //echo $do;
  system($do);
  $list_file = $topic.'/action.work';
  $no_of_lines = count(file($list_file));
  $file = fopen($list_file, "r");
  if ($file)
  {
      // Read first line only
      $line = fgets($file);
      //echo "line:".$line;
      if (strlen($line) > 2)
      {
          $line = trim($line);
          $result = readActionFile($line);
      }
  }
  $result = "[$no_of_lines]".$result;
  return $result;
}
//=============================================
function writeActionFile($topic, $action, $tag)
//=============================================
{
  if (is_null($topic))  return;
  if (is_null($action)) return;
  if (is_null($tag)) $tag = 'notag';

  $action_file = $topic.'/'.$tag.'_gow.action';
  $file = fopen($action_file, "w");
  if ($file)
  {
    fwrite($file,$action);
    fclose($file);
  }
  else
  {
      $result = " ";
  }
  return $result;
}

//=============================================
function deleteTopic($topic)
//=============================================
{
  // remove reg file
  $filename = str_replace("/","_",$topic);
  $filename = $filename.".reg";
  if (file_exists($filename)) unlink($filename);

  // remove directory content
  $dirname = $topic;
  array_map('unlink', glob("$dirname/*.*"));
  // remove directory
  rmdir($dirname);
}

//=============================================
function listAllTopics()
//=============================================
{
  system("ls *.reg > register.work");
  $file = fopen('register.work', "r");
  if ($file)
  {
    while(!feof($file))
    {
      $line = fgets($file);
      if (strlen($line) > 2)
      {
          $line = trim($line);
          echo $line.':';
      }
    }
  }
}

//=============================================
function searchTopics($search)
//=============================================
{
  system("ls *.reg > register.work");
  $file = fopen('register.work', "r");
  if ($file)
  {
    while(!feof($file))
    {
      $line = fgets($file);

      if (strlen($line) > 2)
      {
          $line = trim($line);
          $pos = strpos($line, $search);
          if ($pos !== false) echo $line.':';
      }
    }
  }
}

//=============================================
// End of library
//=============================================

if (isset($_GET['do']))
{
    $do = $_GET['do'];
    if ($do == 'list_topics')
    {
      listAllTopics();
      exit;
    }

    if ($do == 'search')
    {
      $search = 'void';
      $search = $_GET['search'];
      searchTopics($search);
      exit;
    }

    // Check if topic is given
    $error = 1;
    if (isset($_GET['topic']))
    {
      $obj->topic = $_GET['topic'];
      $error = 0;
      if (!is_dir($obj->topic))
      {
         mkdir($obj->topic, 0777, true);
        //===========================================
        // Registration
        //===========================================
        $filename = str_replace("/","_",$obj->topic);
        $filename = $filename.".reg";
        $doc = fopen($filename, "w");
        fwrite($doc, "$gs_ts $ts $obj->topic");
        fclose($doc);
      }
    }
    else
    {
      $error = 2;
      echo "Error: no topic specified";
    }

    // API when topic is available
    if($error == 0)
    {
      if ($do == 'order')
      {
        $msg   = $_GET['msg'];
        $tag   = $_GET['tag'];
        writeActionFile($obj->topic, $msg, $tag);
      }
      if ($do == 'clearlog')
      {
        initLog($obj);
      }
      if ($do == 'log')
      {
        $obj->log   = $_GET['log'];
        saveLog($obj);
      }
      if ($do == 'delete')
      {
        deleteTopic($obj->topic);
      }
      
 
      if ($do == 'stat')
      {
  
        if (isset($_GET['wrap'])) {
          $obj->wrap = $_GET['wrap'];
        }
        if (isset($_GET['platform'])) {
          $obj->platform = $_GET['platform'];
        }
        if (isset($_GET['action'])) {
          $obj->action = $_GET['action'];
        }
        else
        {
          $obj->action = 1;
        }
        if (isset($_GET['ssid'])) {
          $obj->ssid = $_GET['ssid'];
        }
        if (isset($_GET['url'])) {
          $obj->url = $_GET['url'];
        }
        if (isset($_GET['tags'])) {
          $obj->tags = $_GET['tags'];
        }
        if (isset($_GET['desc'])) {
          $obj->desc = $_GET['desc'];
        }
        if (isset($_GET['period'])) {
          $obj->period = $_GET['period'];
        }
        
        saveStaticData($obj);
        echo readActionFileList($obj->topic);

      } // stat


      if ($do == 'dyn')
      {
  
        if (isset($_GET['no'])) {
          $obj->no = $_GET['no'];
        }
        if (isset($_GET['dev_ts'])) {
          $obj->dev_ts = $_GET['dev_ts'];
        }
        if (isset($_GET['rssi'])) {
          $obj->rssi = $_GET['rssi'];
        }
        if (isset($_GET['fail'])) {
          $obj->fail = $_GET['fail'];
        }
        if (isset($_GET['payload'])) {
          $obj->payload = $_GET['payload'];
        }

        saveDynamicData($obj);
        savePayloadData($obj);
        echo readActionFileList($obj->topic);

      } // dyn
 } // error
} // do
else
  echo "Server ok";
// End of file
?>
