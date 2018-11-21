<?php
//=============================================
// File.......: gowServer.php
// Date.......: 2018-11-21
// Author.....: Benny Saxen
// Description: Glass Of Water Server
//=============================================
// Configuration
//=============================================
// No configuration
//=============================================

$conf_action_file_name = 'action.gow';
//=============================================
$date         = date_create();
$gs_ts        = date_format($date, 'Y-m-d H:i:s');

//=============================================
function writeSingle($topic,$value)
//=============================================
{
  $fdoc = $topic.'/doc.single';
  $doc = fopen($fdoc, "w");
  fwrite($doc, "$value");
  fclose($doc);
}
//=============================================
function readActionFile($topic)
//=============================================
{
  global $conf_action_file_name;
  $action_file = $topic.'/'.$conf_action_file_name;
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
function writeActionFile($topic, $action)
//=============================================
{
  global $conf_action_file_name;
  $action_file = $topic.'/'.$conf_action_file_name;
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
        writeActionFile($topic, $order);
      }
      if ($do == 'delete') 
      {
        deleteTopic($topic);
      }

      if ($do == 'data') 
      { 
        // Default values
        $no = 999;
        $wrap = 999
        $ts = 'no_device_timestamp';
        $period = 999;
        $url = 'no_url';
        $hw = 'no_hw';

        if (isset($_GET['no'])) {
          $no = $_GET['no'];
        }
        if (isset($_GET['wrap'])) {
          $no = $_GET['wrap'];
        }
        if (isset($_GET['type'])) {
          $ts = $_GET['type'];
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
        for ($ii = 1;$ii < 10; $ii++)
        {
          $ok = 0;
          $$par = p.$ii;
          if (isset($_GET[$par])) {
            $par = $_GET[$par];
            $ok++;
          }
          $$val = v.$ii;
          if (isset($_GET[$val])) {
            $var = $_GET[$var];
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
        for ($ii = 1;$ii <= $npar; $ii++)
        {
          $$par = p.$ii;
          $$par = v.$ii;
          fwrite($doc, "$par        ".$val);
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
        fwrite($doc, "   \"gs_ts\":  \"$gs_ts\"\n");
        fwrite($doc, "   \"url\":    \"$url\"\n");
        fwrite($doc, "   \"hw\":     \"$hw\"\n");
        for ($ii = 1;$ii <= $npar; $ii++)
        {
          $$par = p.$ii;
          $$par = v.$ii;
          fwrite($doc, "   \"$par\":   \"$$val\",\n");
        }
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
          $$par = p.$ii;
          $$par = v.$ii;
          fwrite($doc,   "$par       $val\n");
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
        echo readActionFile($topic);
      } // data
 } // error
} // do

// End of file
?>
