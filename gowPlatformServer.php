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
  $file_name = str_replace("/","_",$topic);
  $file_name = $file_name.".reg";
  print $file_name;
  $doc = fopen($file_name, "w");
  fwrite($doc, "$ts $topic $url $type $period $hw\n");
  fclose($doc);
}
//=============================================
function deleteRegistration($topic)
//=============================================
{
  $file_name = str_replace("/","_",$topic);
  $file_name = $file_name.".reg";
  if (file_exists($file_name)) unlink($file_name);
}

//=============================================
function readRegister()
//=============================================
{
  $file = fopen('register.work', "r");
  if ($file)
  {
    echo "<table border=1 bgcolor=\"#00FF00\">";
    echo "<tr><td>Date</td> <td>Time</td> <td>Topic</td> <td>Domain</td>
    <td>Type</td> <td>Period</td> <td>hw</td><td>Links</td><td>Delete</td></tr>";
      while(!feof($file))
      {
        $line = fgets($file);
        if (strlen($line) > 2)
        {
          $line = trim($line);
          $file2 = fopen($line, "r");
          if ($file2)
          {
            while(!feof($file2))
            {
              $line2 = fgets($file2);
              if (strlen($line2) > 2)
              {
                // 2018-11-16 22:12:09 kvv32/temperature/outdoor/0 http://127.0.0.1/git/gow/ TEMPERATURE 10 python
                sscanf($line2,"%s %s %s %s %s %s %s",$p1,$p2,$p3,$p4,$p5,$p6,$p7);
                echo "<tr><td>$p1</td> <td>$p2</td> <td>$p3</td> <td>$p4</td> <td>$p5</td> <td>$p6</td> <td>$p7</td>
                <td><a href=gowPlatformServer.php?do=action&topic=$p3&url=$p4>action</a></td>
                <td><a href=gowPlatformServer.php?do=delete&topic=$p3>delete</a></td></tr>";
              }
            }
            fclose($file2);
          }
        }
      }
      echo "</table>";
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
  $file_name = str_replace("/","_",$topic);
  $file_name = $file_name.".reg";

  if (file_exists($file_name))
  {
      $res = 'exist';
  } else
  {
      $res = 'new';
  }
  return $res;
}
//=============================================
// End of library
//=============================================

system("ls *.reg > register.work");
echo "<html>
   <head>
      <title>GOW</title>
   </head>
   <body> ";

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
    if (isset($_GET['topic'])) {
      $topic = $_GET['topic'];
      $ok++;
    }
    if (isset($_GET['url'])) {
      $url = $_GET['url'];
      $ok++;
    }
    echo "
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
if ($do == 'delete')
{
    if (isset($_GET['topic'])) {
      $topic = $_GET['topic'];
      deleteRegistration($topic);
    }
}
readRegister();


echo "</body></html>";
// End of file
