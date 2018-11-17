<?php
//=============================================
// File.......: gowServer.php
// Date.......: 2018-11-17
// Author.....: Benny Saxen
// Description: Glass Of Water Server
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
  $action_file = $topic.'/action.gow';
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
  else {
      $result = " ";
  }
  return $result;
}
//=============================================
function writeActionFile($topic, $action)
//=============================================
{
  $action_file = $topic.'/action.gow';
  $file = fopen($action_file, "w");
  if ($file)
  {
    fwrite($file,$action);
    fclose($file);
  }
  else {
      $result = " ";
  }
  return $result;
}
//=============================================
function registrationGPS($url,$topic,$type,$period,$hw)
//=============================================
{
  $request = 'http://gow.simuino.com/gowPlatformServer.php';
  $request = $request."?do=register&topic=$topic&type=$type&period=$period&url=$url&hw=$hw";
  $res = file_get_contents($request);
}
//=============================================
// End of library
//=============================================

$error = 1;

if (isset($_GET['topic'])) {
  $topic = $_GET['topic'];
  $error = 0;
}
else
{
  $error = 2;
  echo "error 2";
}

if($error == 0)
{

  if (isset($_GET['action'])) {
    $action = $_GET['action'];
    writeActionFile($topic, $action);
    exit;
  }


  if (isset($_GET['no'])) {
    $no = $_GET['no'];
  }
  if (isset($_GET['type'])) {
    $type = $_GET['type'];
  }
  if (isset($_GET['value'])) {
    $value = $_GET['value'];
  }
  if (isset($_GET['unit'])) {
    $unit = $_GET['unit'];
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
  if (isset($_GET['url'])) {
    $url = $_GET['url'];
  }
  if (isset($_GET['hw'])) {
    $hw = $_GET['hw'];
  }
  registrationGPS($url,$topic,$type,$period,$hw);

    //===========================================
    // HTML
    //===========================================

    if (!is_dir($topic)) {
    mkdir($topic, 0777, true);
    }

    $fdoc = $topic.'/doc.html';

    $doc = fopen($fdoc, "w");
    fwrite($doc, "<html>");
    fwrite($doc, "<body bgcolor=\"#9EB14A\">");
    fwrite($doc, "TOPIC       ".$topic);
    fwrite($doc, "<br>");
    fwrite($doc, "NO          ".$no);
    fwrite($doc, "<br>");
    fwrite($doc, "TYPE        ".$type);
    fwrite($doc, "<br>");
    fwrite($doc, "VALUE       ".$value);
    fwrite($doc, "<br>");
    fwrite($doc, "UNIT        ".$unit);
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
      fwrite($doc, "   \"type\":   \"$type\",\n");
      fwrite($doc, "   \"value\":  \"$value\",\n");
      fwrite($doc, "   \"unit\":   \"$unit\",\n");
      fwrite($doc, "   \"ts\":     \"$ts\",\n");
      fwrite($doc, "   \"period\": \"$period\",\n");
      fwrite($doc, "   \"gs_ts\":  \"$gs_ts\"\n");
      fwrite($doc, "   \"url\":    \"$url\"\n");
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
    fwrite($doc,   "TYPE         $type\n");
    fwrite($doc,   "VALUE        $value\n");
    fwrite($doc,   "UNIT         $unit\n");
    fwrite($doc,   "TS           $ts\n");
    fwrite($doc,   "PERIOD       $period\n");
    fwrite($doc,   "GS_TS        $gs_ts\n");
    fwrite($doc,   "URL          $url\n");
    fwrite($doc,   "HW           $hw\n");
    fclose($doc);

    writeSingle($topic,$value);

    echo readActionFile($topic);
  }

  ?>
