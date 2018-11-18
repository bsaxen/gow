<?php
//=============================================
// File.......: gowPlatformServer.php
// Date.......: 2018-11-18
// Author.....: Benny Saxen
// Description: Glass Of Water Platform Server
//=============================================
// Configuration
//=============================================
// No configuration needed
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
          echo "<table border=1>";
          echo "<tr><td>Date</td> <td>Time</td> <td>Topic</td> <td>Domain</td>
          <td>Type</td> <td>Period</td> <td>hw</td><td>Links</td></tr>";
          echo "<tr><td>$p1</td> <td>$p2</td> <td>$p3</td> <td>$p4</td> <td>$p5</td> <td>$p6</td> <td>$p7</td>
          <td><a href=gowPlatformServer.php?do=action&topic=$p3&url=$p4>action</a></td></tr>";
          echo "</table>";
          echo "<a href=/$p3/doc.html> html</a>";
          echo "<a href=/$p3/doc.json> json</a>";
          echo "<a href=/$p3/doc.txt> txt</a>";
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
echo "<html>
   <head>
      <title>GOW</title>
   </head>
   <body> ";

$error = 1;
if (isset($_GET['do'])) {
  $do = $_GET['do'];
}
echo "do=$do<br>";
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
    if (isset($_GET['topic'])) {
      $topic = $_GET['topic'];
      $ok++;
    }
    if (isset($_GET['url'])) {
      $url = $_GET['url'];
      $ok++;
    }

    echo "<br><br><h2>Send action to topic</h2>
    <table border=1>
    <form action=\"#\" method=\"get\">
      <input type=\"hidden\" name=\"do\" value=\"create_action\">
      <tr><td>Url</td><td> <input type=\"text\" name=\"furl\" size=40 value=\"$url\"></td></tr>
      <tr><td>Topic</td><td> <input type=\"text\" name=\"ftopic\" size=40 value=\"$topic\"></td></tr>
      <tr><td>Action</td><td> <input type=\"text\" name=\"faction\" size=40 value=\"\"></td></tr>
      <tr><td><input type= \"submit\" value=\"Submit\"></td><td></td></tr>
    </form></table>
    ";
  }
  if ($do == 'create_action')
  {
    if (isset($_GET['furl'])) {
      $furl = $_GET['furl'];
    }
    if (isset($_GET['ftopic'])) {
      $ftopic = $_GET['ftopic'];
    }
    if (isset($_GET['faction'])) {
      $faction = $_GET['faction'];
    }
    $request = $furl;
    $request = $request."gowServer.php?topic=$ftopic&action=$faction";
    //echo "$request";
    $res = file_get_contents($request);
    //echo $res;
  }
readRegister();


echo "</body></html>";
// End of file
