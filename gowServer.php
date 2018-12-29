<?php
//=============================================
// File.......: gowServer.php
// Date.......: 2018-12-29
// Author.....: Benny Saxen
// Description: Glass Of Water Server
//=============================================
// Configuration
//=============================================

//=============================================


//=============================================
// API
// gowServer.php?do=list_topics               list topics
// gowServer.php?do=list_datastreams&topic    list data streams
// gowServer.php?do=search&search=<string>
// gowServer.php?do=action&topic=<topic>&order=<order>&tag=<tag>
// gowServer.php?do=delete&topic=<topic>
// gowServer.php?do=data&topic=<topic>
//              &no     = 3
//              &wrap   = 999999
//              &type   = 'temperature'
//              &ts     = '2018-12-01 23:12:31'
//              &period = 10
//              &url    = http://gow.zimuino.com
//              &devtyp = 1
//              &message = 0
//              &hw     = 'python'
//              + payload (json struct)
//=============================================
// devtyp:   1=sensor, 2=actuator, 3=sensor/actuator 4= none
// message:  1=no_support, 2=support
//=============================================
// Library
//=============================================
$conf_action_file_name = 'action.gow';
//=============================================
$date         = date_create();
$gs_ts        = date_format($date, 'Y-m-d H:i:s');

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
    $devtyp = 0;
    if (isset($_GET['devtyp'])) $devtyp = $_GET['devtyp'];

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
      $topic = $_GET['topic'];
      $error = 0;
      if (!is_dir($topic))
      {
         mkdir($topic, 0777, true);
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
      if ($do == 'action')
      {
        $order = $_GET['order'];
        $tag   = $_GET['tag'];
        writeActionFile($topic, $order, $tag);
      }
      if ($do == 'delete')
      {
        deleteTopic($topic);
      }

      if ($do == 'data')
      {
        // Default values
        $no = 999;
        $wrap = 999;
        $ts = 'no_device_timestamp';
        $period = 999;
        $url = 'no_url';
        $hw = 'no_hw';

        if (isset($_GET['no'])) {
          $no = $_GET['no'];
        }
        if (isset($_GET['wrap'])) {
          $wrap = $_GET['wrap'];
        }
        if (isset($_GET['ts'])) {
          $ts = $_GET['ts'];
        }
        if (isset($_GET['period'])) {
          $period = $_GET['period'];
        }
        if (isset($_GET['url'])) {
          $url = $_GET['url'];
        }
        if (isset($_GET['hw'])) {
          $hw = $_GET['hw'];
        }
        if (isset($_GET['message'])) {
          $message = $_GET['message'];
        }
        else
        {
          $message = 1;
        }
        if (isset($_GET['payload'])) {
          $payload = $_GET['payload'];
        }
      

        //===========================================
        // Registration
        //===========================================
        $filename = str_replace("/","_",$topic);
        $filename = $filename.".reg";
        //print $filename;
        $doc = fopen($filename, "w");
        fwrite($doc, "$gs_ts $ts $topic $url $period $hw");
        fclose($doc);


        //===========================================
        // JSON
        //===========================================
        $fdoc = $topic.'/device.json';
        $doc = fopen($fdoc, "w");
        fwrite($doc, "{\"gow\": {\n");
        fwrite($doc, "   \"topic\":  \"$topic\",\n");
        fwrite($doc, "   \"no\":     \"$no\",\n");
        fwrite($doc, "   \"wrap\":   \"$wrap\",\n");
        fwrite($doc, "   \"ts\":     \"$ts\",\n");
        fwrite($doc, "   \"period\": \"$period\",\n");
        fwrite($doc, "   \"gs_ts\":  \"$gs_ts\",\n");
        fwrite($doc, "   \"url\":    \"$url\",\n");
        fwrite($doc, "   \"hw\":     \"$hw\",\n");
        fwrite($doc, "   \"message\":\"$message\",\n");
        fwrite($doc, "   $payload");
        fwrite($doc, "}}\n ");
        fclose($doc);
    

        // Check if any action is present for this client/topic
        echo readActionFileList($topic);
     
      } // data
 } // error
} // do
else
  echo "Server ok";
// End of file
?>
