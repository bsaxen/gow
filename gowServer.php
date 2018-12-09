<?php
//=============================================
// File.......: gowServer.php
// Date.......: 2018-12-09
// Author.....: Benny Saxen
// Description: Glass Of Water Server
//=============================================
// Configuration
//=============================================
$conf_max_paramaters = 10;
//=============================================


//=============================================
// API
// gowServer.php?do=list
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
//              &hw     = 'python'
//              &p1     = 'value'
//              &v1     = 17.1
//              &p2     = 'unit'
//              &v2     = 'celcius'
//              ... max 10 p,v
//=============================================


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
  echo $do; 
  system($do);
  $list_file = $topic.'/action.work';
  $file = fopen($list_file, "r");
  if ($file)
  {
      // Read first line only
      $line = fgets($file);
      echo "line:".$line;
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
  
    if ($do == 'list')
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
        if (isset($_GET['type'])) {
          $type = $_GET['type'];
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
        
        $npar = 0;
        for ($ii = 1;$ii < $conf_max_paramaters; $ii++)
        {
          $ok = 0;
          $par = 'p'.$ii;
          if (isset($_GET[$par])) {
            ${$par} = $_GET[$par];
            $ok++;
          }
          $val = 'v'.$ii;
          if (isset($_GET[$val])) {
            ${$val} = $_GET[$val];
            $ok++;
          }
          if( $ok == 2) $npar++;
        }
        
        //===========================================
        // Registration
        //===========================================
        $filename = str_replace("/","_",$topic);
        $filename = $filename.".reg";
        //print $filename;
        $doc = fopen($filename, "w");
        fwrite($doc, "$gs_ts $ts $topic $url $type $period $hw");
        fclose($doc);
  
        //===========================================
        // HTML
        //===========================================

        $fdoc = $topic.'/doc.html';

        $doc = fopen($fdoc, "w");
        fwrite($doc, "<html>");
        fwrite($doc, "<body bgcolor=\"#9EB14A\">");
        fwrite($doc, "TOPIC       ".$topic);
        fwrite($doc, "<br>");
        fwrite($doc, "NO          ".$no);
        fwrite($doc, "<br>");
        fwrite($doc, "WRAP        ".$wrap);
        fwrite($doc, "<br>");
        fwrite($doc, "TYPE        ".$type);
        fwrite($doc, "<br>");
        fwrite($doc, "TS          ".$ts);
        fwrite($doc, "<br>");
        fwrite($doc, "PERIOD      ".$period);
        fwrite($doc, "<br>");
        fwrite($doc, "GS_TS       ".$gs_ts);
        fwrite($doc, "<br>");
        fwrite($doc, "URL         ".$url);
        fwrite($doc, "<br>");
        fwrite($doc, "HW          ".$hw);
        fwrite($doc, "<br>");
        fwrite($doc, "npar          ".$npar);
        fwrite($doc, "<br>");
        for ($ii = 1;$ii <= $npar; $ii++)
        {
          $par = 'p'.$ii;
          $val = 'v'.$ii;
          fwrite($doc, "${$par}        ".${$val});
          fwrite($doc, "<br>");
        }
        fwrite($doc, "</body></html>");
        fclose($doc);

        //===========================================
        // JSON
        //===========================================
        $fdoc = $topic.'/doc.json';
        $doc = fopen($fdoc, "w");
        fwrite($doc, "{\"gow\": {\n");
        fwrite($doc, "   \"topic\":  \"$topic\",\n");
        fwrite($doc, "   \"no\":     \"$no\",\n");
        fwrite($doc, "   \"wrap\":   \"$wrap\",\n");
        fwrite($doc, "   \"type\":   \"$type\",\n");
        fwrite($doc, "   \"ts\":     \"$ts\",\n");
        fwrite($doc, "   \"period\": \"$period\",\n");
        fwrite($doc, "   \"gs_ts\":  \"$gs_ts\",\n");
        fwrite($doc, "   \"url\":    \"$url\",\n");
        for ($ii = 1;$ii <= $npar; $ii++)
        {
          $par = 'p'.$ii;
          $val = 'v'.$ii;
          fwrite($doc, "   \"${$par}\":   \"${$val}\",\n");
        }
        fwrite($doc, "   \"hw\":     \"$hw\"\n");
        fwrite($doc, "}}\n ");
        fclose($doc);

        //===========================================
        // TXT
        //===========================================
        $fdoc = $topic.'/doc.txt';
        $doc = fopen($fdoc, "w");
        fwrite($doc,   "TOPIC        $topic\n");
        fwrite($doc,   "NO           $no\n");
        fwrite($doc,   "WRAP         $wrap\n");
        fwrite($doc,   "TYPE         $type\n");
        fwrite($doc,   "TS           $ts\n");
        fwrite($doc,   "PERIOD       $period\n");
        fwrite($doc,   "GS_TS        $gs_ts\n");
        fwrite($doc,   "URL          $url\n");
        fwrite($doc,   "HW           $hw\n");
        for ($ii = 1;$ii <= $npar; $ii++)
        {
          $par = 'p'.$ii;
          $val = 'v'.$ii;
          fwrite($doc,   "${$par}       ${$val}\n");
        }
        fclose($doc);

        //===========================================
        // Single value
        //===========================================
        //$fdoc = $topic.'/doc.single';
        //$doc = fopen($fdoc, "w");
        //fwrite($doc, "$value");
        //fclose($doc);

        // Check if any action is present for this client/topic
        echo readActionFileList($topic);
      } // data
 } // error
} // do
else
  echo "Server ok";
// End of file
?>
