<?php
//=============================================
// File.......: gowPlatformServer.php
// Date.......: 2018-11-16
// Author.....: Benny Saxen
// Description: Glass Of Water Platform Server
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
 }
