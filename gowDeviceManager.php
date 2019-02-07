<?php
session_start();

$sel_url    = $_SESSION["url"];
$sel_path   = $_SESSION["path"];

$flag_show_static  = $_SESSION["flag_show_static"];
$flag_show_dynamic = $_SESSION["flag_show_dynamic"];
$flag_show_payload = $_SESSION["flag_show_payload"];
$flag_show_log     = $_SESSION["flag_show_log"];

//=============================================
// File.......: gowDeviceManager.php
// Date.......: 2019-02-07
// Author.....: Benny Saxen
// Description: Glass Of Water Platform Device Manager
$version = '2019-02-07';
//=============================================
// Configuration
//=============================================
// No configuration needed
//=============================================
$date         = date_create();
$ts           = date_format($date, 'Y-m-d H:i:s');
$now          = date_create('now')->format('Y-m-d H:i:s');
$g_rssi       = 0;
$g_action     = 0;
//echo "<br>$ts $now<br>";
//=============================================
// library
//=============================================

//=============================================
function addDomain ($domain)
//=============================================
{
  echo("[$domain]");
  $domain = $domain.'.domain';
  $fh = fopen($domain, 'w') or die("Can't add domain $domain");
}
//=============================================
function restApi($api,$url,$topic)
//=============================================
{
  echo("RestApi [$api] url=$url topic=$topic<br>");
  $call = 'http://'.$url.'/gowServer.php?do='.$api.'&topic='.$topic;
  $res = file_get_contents($call);
}

//=============================================
function getStatus($uri)
//=============================================
{
  global $g_action, $g_rssi;
  
  $url = $uri.'/static.json';
  //echo "$url<br>";
  $json = file_get_contents($url);
  $json = utf8_encode($json);
  $res = json_decode($json, TRUE);
  $period     = $res['gow']['period'];
  $g_action   = $res['gow']['action'];
  
  $url = $uri.'/dynamic.json';
  //echo "$url<br>";
  $json = file_get_contents($url);
  $json = utf8_encode($json);
  $res = json_decode($json, TRUE);
  $timestamp   = $res['gow']['sys_ts'];
  $g_rssi   = $res['gow']['rssi'];
  $now = date_create('now')->format('Y-m-d H:i:s');
  
  $diff = strtotime($now) - strtotime($timestamp);
  //echo "now=$now ts=$timestamp diff= $diff";
  
  if ($diff > $period) 
  {
    $res = $diff;
  }
  else {
    $res = 0;
  }
  return ($res);
}


//=============================================
function sendMessage($url,$topic,$msg,$tag)
//=============================================
{
  echo "Send message $msg tag=$tag to $url/$topic<br>";
  $call = 'http://'.$url.'/gowServer.php?do=order&topic='.$topic.'&msg='.$msg.'&tag='.$tag;
  $res = file_get_contents($call);
  //gowServer.php?do=action&topic=<topic>&order=<order>&tag=<tag>
}
//=============================================
// End of library
//=============================================


//=============================================
// Back-End
//=============================================

if (isset($_GET['flag'])) {
  $flag = $_GET['flag'];
  $status = $_GET['status'];
  if ($flag == "static")
  {
    $flag_show_static = $status;
    $_SESSION["flag_show_static"] = $status;
  }
  if ($flag == "dynamic")
  {
    $flag_show_dynamic = $status;
    $_SESSION["flag_show_dynamic"] = $status;
  }
  if ($flag == "payload")
  {
    $flag_show_payload = $status;
    $_SESSION["flag_show_payload"] = $status;
  }
  if ($flag == "log")
  {
    $flag_show_log = $status;
    $_SESSION["flag_show_log"] = $status;
  }
}

if (isset($_GET['do'])) {

  $do = $_GET['do'];

  if($do == 'select')
  {
    if (isset($_GET['sel_url']))
    {
      $sel_url = $_GET['sel_url'];
      $_SESSION["url"] = $sel_url;
    }
    if (isset($_GET['sel_path']))
    {
      $sel_path = $_GET['sel_path'];
      $_SESSION["path"]   = $sel_path;
    }
  }

  if($do == 'delete')
  {
    $domain   = $_GET['domain'];
    $fn = $domain.'.domain';
    if (file_exists($fn)) unlink($fn);
  }

  if($do == 'rest')
  {
    $api   = $_GET['api'];
    $url   = $_GET['url'];
    $topic = $_GET['topic'];
    restApi($api,$url,$topic);
  }
  if($do == 'form')
  {
    $api   = $_GET['api'];
    $url   = $_GET['url'];
    $topic = $_GET['topic'];
    $form_send = 1;
  }
}

if (isset($_POST['do'])) {
  $do = $_POST['do'];

  if ($do == 'add_domain')
  {
    $dn = $_POST['domain'];
    if (strlen($dn) > 2)addDomain($dn);
  }

  if ($do == 'send_message')
  {
    $url   = $_POST['url'];
    $topic = $_POST['topic'];
    $msg   = $_POST['message'];
    $tag   = $_POST['tag'];
    if (strlen($msg) > 2)sendMessage($url,$topic,$msg,$tag);
  }

}

//=============================================
// Front-End
//=============================================
$data = array();

echo "<html>
   <head>
      <title>GOW Device Manager</title>
   </head>
   <body> ";

//echo("<h1>GOW Device Manager $version</h1>");
//echo("url=$sel_url topic=$sel_path format=$sel_format<br>");
   //echo ("<a href=#>refresh</a><br>");
echo "<a href=\"http://gow.simuino.com/gowDtManager.php\" target=\"_blank\">Model Manager</a>";

if ($flag_show_static == 1)
{
  $doc = 'http://'.$sel_url.'/'.$sel_path.'/static.json';
  echo ("<iframe src=$doc width=\"400\" height=\"300\"></iframe>");
}

if ($flag_show_dynamic == 1)
{
  $doc = 'http://'.$sel_url.'/'.$sel_path.'/dynamic.json';
  echo ("<iframe src=$doc width=\"400\" height=\"300\"></iframe>");
}

if ($flag_show_payload == 1)
{
  $doc = 'http://'.$sel_url.'/'.$sel_path.'/payload.json';
  echo ("<iframe src=$doc width=\"400\" height=\"300\"></iframe>");
}

if ($flag_show_log == 1)
{
  $doc = 'http://'.$sel_url.'/'.$sel_path.'/log.json';
  echo ("<iframe src=$doc width=\"400\" height=\"300\"></iframe>");
}


if ($form_send == 1)
{
  echo "<br><br>
  <table border=0>";
  echo "
  <form action=\"#\" method=\"post\">
    <input type=\"hidden\" name=\"do\" value=\"send_message\">
    <tr><td>Url</td><td> <input type=\"text\" name=\"url\" value=$url></td>
    <tr><td>Topic</td><td> <input type=\"text\" name=\"topic\" value=$topic></td>
    <tr><td>Tag</td><td> <input type=\"text\" name=\"tag\" ></td>
    <tr><td>Message</td><td> <input type=\"text\" name=\"message\"></td>
    <td><input type= \"submit\" value=\"Send\"></td></tr>
  </form>
  </table>";
}


   $do = 'ls *.domain > domain.list';
   system($do);
   $file = fopen('domain.list', "r");
   if ($file)
   {
     echo "<br><br>
     <table border=0>";
     echo "
     <form action=\"#\" method=\"post\">
       <input type=\"hidden\" name=\"do\" value=\"add_domain\">
       <tr><td>Add Domain</td><td> <input type=\"text\" name=\"domain\"></td>
       <td><input type= \"submit\" value=\"Add Domain\"></td></tr>
     </form>
     ";
     echo "<tr bgcolor=\"#FF0000\"><td></td><td></td><td></td><td></td><td></td></tr>";
     echo "<tr bgcolor=\"#FFC300\"><td>Domain</td><td>RSSI</td><td>Status/Topic</td><td>Message</td><td>Edit</td></tr>";
     echo "<tr bgcolor=\"#FF0000\"><td></td><td></td><td></td><td></td><td></td></tr>";

     while(!feof($file))
     {
       $line = fgets($file);
       //echo "<tr><td>$line</td><td>benny</td></tr>";
       if (strlen($line) > 2)
       {
           $line = trim($line);
           $url = str_replace(".domain", "", $line);
           $request = 'http://'.$url."/gowServer.php?do=list_topics";
           //echo $request;
           $ctx = stream_context_create(array('http'=>
            array(
              'timeout' => 2,  //2 Seconds
                )
              ));
           $res = file_get_contents($request,false,$ctx);
           //echo "<tr><td>$res</td><td>b1</td></tr>";
           //echo "<tr><td>$url</td><td>b2</td></tr>";
           if ($res === false) {
             echo "<tr><td>$url</td><td></td><td bgcolor=\"red\">NO CONNECTION</td>";
           }
           else {
             echo "<tr><td>$url</td><td></td><td bgcolor=\"#DAF7A6\">CONNECTED</td>";
           }
           echo "<td></td>";
           echo "<td><a href=gowDeviceManager.php?do=delete&domain=$url>delete</a></td></tr>";

           $data = explode(":",$res);
           $num = count($data);

           for ($ii = 0; $ii < $num; $ii++)
           {
             $id = str_replace(".reg", "", $data[$ii]);
             if (strlen($id) > 2)
             {
               $topic = explode("_",$id);
               $topic_num = count($topic);
               //$link = 'http://'.$url;
               $link = $topic[0];
               for ($jj=1;$jj<$topic_num;$jj++)
                  $link = $link."/$topic[$jj]";
               $doc = 'http://'.$url.'/'.$link;
               $status = getStatus($doc);
               if ($status == 0)
               {
                 echo "<tr><td bgcolor=\"#DAF7A6\">ON-LINE</td>";
               }
               else {
                 echo "<tr><td bgcolor=\"yellow\">$status</td>";
               }
               echo "<td>$g_rssi</td>";
               echo "<td><a href=gowDeviceManager.php?do=select&sel_url=$url&sel_path=$link>$link</a></td>";
               $rest = 'http://'.$url.'?do=delete&topic='.$link;
               //echo "<tr><td></td><td><a href=gowDeviceManager.php?do=select&sel_doc=$doc>$link</a></td>";
               if ($g_action == 2) {
                  echo "<td><a href=gowDeviceManager.php?do=form&api=action&url=$url&topic=$link>send</a></td>";
              }
              else {
                  echo "<td></td>";
              }
               echo "<td><a href=gowDeviceManager.php?do=rest&api=delete&url=$url&topic=$link>remove</a></td></tr>";
           }
         }
       }
     }

     echo("</table>");

   }
if ($flag_show_static == 0)
  echo "<br><a href=\"gowDeviceManager.php?flag=static&status=1\" >Static On</a> ";
else
  echo "<br><a href=\"gowDeviceManager.php?flag=static&status=0\" >Static Off</a> ";

if ($flag_show_dynamic == 0)
  echo "<br><a href=\"gowDeviceManager.php?flag=dynamic&status=1\" >Dynamic On</a> ";
else
  echo "<br><a href=\"gowDeviceManager.php?flag=dynamic&status=0\" >Dynamic Off</a> ";

if ($flag_show_payload == 0)
  echo "<br><a href=\"gowDeviceManager.php?flag=payload&status=1\" >Payload On</a> ";
else
  echo "<br><a href=\"gowDeviceManager.php?flag=payload&status=0\" >Payload Off</a> ";

if ($flag_show_log == 0)
  echo "<br><a href=\"gowDeviceManager.php?flag=log&status=1\" >Log On</a> ";
else
  echo "<br><a href=\"gowDeviceManager.php?flag=log&status=0\" >log Off</a> ";

echo "</body></html>";
// End of file
