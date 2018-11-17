<?php
//=============================================
// File.......: gowPlatformServer.php
// Date.......: 2018-11-16
// Author.....: Benny Saxen
// Description: Glass Of Water Platform Server
//=============================================

$date         = date_create();
$ts           = date_format($date, 'Y-m-d H:i:s');

//=============================================
function registerTopic($topic,$url,$type,$period,$ts,$hw)
//=============================================
{
  $fdoc = 'gowPlatformServer_register.gow';
  $doc = fopen($fdoc, "a+");
  fwrite($doc, "$ts $topic $url $type $period $hw\n");
  fclose($doc);
}
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
function readRegister()
//=============================================
{
  $fdoc = 'gowPlatformServer_register.gow';
  $file = fopen($fdoc, "r");
  if ($file)
  {
      while(! feof($file))
      {
        $line = fgets($file);
        if (strlen($line) > 2)
        {
          // 2018-11-16 22:12:09 kvv32/temperature/outdoor/0 http://127.0.0.1/git/gow/ TEMPERATURE 10 python
          sscanf($line,"%s %s %s %s %s %s %s",$p1,$p2,$p3,$p4,$p5,$p6,$p7);
          //echo $line;
          echo "1 $p1<br>";
          echo "2 $p2<br>";
          echo "3 $p3<br>";
          echo "4 $p4<br>";
          echo "5 $p5<br>";
          echo "6 $p6<br>";
          echo "7 $p7<br>";
          //echo "$p1<br>";echo "$p2<br>";echo "$p3<br>";echo "$p4<br>";echo "$p5<br>";echo "$p6<br>";echo "$p7<br>";
          echo "$p1 $p2 <a href=$p4/$p3>$p3</a> $p5 $p6 $p7";
          echo "<a href=$p4/$p3/doc.html> html</a>";
          echo "<a href=$p4/$p3/doc.json> json</a>";
          echo "<a href=$p4/$p3/doc.txt> txt</a>";
          echo "<br>";
        }
      }
      fclose($file);
  }
  else {
      echo("No clients<br>");
  }
}
//=============================================
function checkTopic($topic)
//=============================================
{
  $fdoc = 'gowPlatformServer_register.gow';
  $file = fopen($fdoc, "r");
  $res = 'new';
  if ($file)
  {
      while(! feof($file))
      {
        $line = fgets($file);
        if (strpos($line, $topic) !== false) {
          $res = 'old';
        }
      }
      fclose($file);
  }
  else {
      echo("Error open GPS Register file<br>");
  }
  return $res;
}
//=============================================
// End of library
//=============================================

$error = 1;
if (isset($_GET['do'])) {
  $do = $_GET['do'];
}



  if ($do == 'register')
  {
    $ok = 0;
    if (isset($_GET['topic'])) {
      $topic = $_GET['topic'];
      $ok++;
    }
    if (isset($_GET['type'])) {
      $type = $_GET['type'];
      $ok++;
    }
    if (isset($_GET['url'])) {
      $url = $_GET['url'];
      $ok++;
    }
    if (isset($_GET['period'])) {
      $period = $_GET['period'];
      $ok++;
    }
    if (isset($_GET['hw'])) {
      $hw = $_GET['hw'];
      $ok++;
    }
    if ($ok == 5)
    {
      $res = checkTopic($topic);
      if ($res == 'new')
      {
        registerTopic($topic,$url,$type,$period,$ts,$hw);
      }
    }
  }
  if ($do == 'action')
  {

  }

readRegister();


// End of file
