<?php
session_start();

$sel_domain = $_SESSION["domain"];
$sel_device = $_SESSION["device"];

$flag_show_static  = $_SESSION["flag_show_static"];
$flag_show_dynamic = $_SESSION["flag_show_dynamic"];
$flag_show_payload = $_SESSION["flag_show_payload"];
$flag_show_log     = $_SESSION["flag_show_log"];

//=============================================
// File.......: gowDeviceManager.php
// Date.......: 2019-02-09
// Author.....: Benny Saxen
// Description: Glass Of Water Platform Device Manager
$version = '2019-02-09';
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

  if($do == 'add_domain')
  {
    $form_add_domain = 1;
  }

  if($do == 'send_action')
  {
    $form_send_action = 1;
  }

  if($do == 'select')
  {
    if (isset($_GET['domain']))
    {
      $sel_domain = $_GET['domain'];
      $_SESSION["domain"] = $sel_domain;
    }
    if (isset($_GET['device']))
    {
      $sel_device = $_GET['device'];
      $_SESSION["device"]   = $sel_device;
    }
  }

  if($do == 'info')
  {
    if (isset($_GET['what']))
    {
      $temp = $_GET['what'];

      if ($temp == 'static')
      {
        $flag_show_static += 1;
        if ($flag_show_static > 1)$flag_show_static = 0;
        $_SESSION["flag_show_static"] = $flag_show_static;
      }

      if ($temp == 'dynamic')
      {
        $flag_show_dynamic += 1;
        if ($flag_show_dynamic > 1)$flag_show_dynamic = 0;
        $_SESSION["flag_show_dynamic"] = $flag_show_dynamic;
      }

      if ($temp == 'payload')
      {
        $flag_show_payload += 1;
        if ($flag_show_payload > 1)$flag_show_payload = 0;
        $_SESSION["flag_show_payload"] = $flag_show_payload;
      }

      if ($temp == 'log')
      {
        $flag_show_log += 1;
        if ($flag_show_log > 1)$flag_show_log = 0;
        $_SESSION["flag_show_log"] = $flag_show_log;
      }


    }
  }

  if($do == 'delete')
  {
    $what   = $_GET['what'];
    if ($what == 'domain')
    {
      $fn = $sel_domain.'.domain';
      if (file_exists($fn)) unlink($fn);
    }
    if ($what == 'device')
    {
      restApi('delete',$sel_domain,$sel_device);
    }
  }

  if($do == 'rest')
  {
    $api   = $_GET['api'];
    $url   = $_GET['url'];
    $topic = $_GET['topic'];
    restApi($api,$url,$topic);
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
    $domain   = $_POST['domain'];
    $device   = $_POST['device'];
    $msg      = $_POST['message'];
    $tag      = $_POST['tag'];
    if (strlen($msg) > 2)sendMessage($domain,$device,$msg,$tag);
  }

}

//=============================================
// Front-End
//=============================================
$data = array();

   echo "<html>
      <head>
      <style>
      html {
          min-height: 100%;
      }

      body {
          background: -webkit-linear-gradient(left, #93B874, #C9DCB9);
          background: -o-linear-gradient(right, #93B874, #C9DCB9);
          background: -moz-linear-gradient(right, #93B874, #C9DCB9);
          background: linear-gradient(to right, #93B874, #C9DCB9);
          background-color: #93B874;
      }
      /* Navbar container */
   .navbar {
     overflow: hidden;
     background-color: #333;
     font-family: Arial;
   }

   /* Links inside the navbar */
   .navbar a {
     float: left;
     font-size: 16px;
     color: white;
     text-align: center;
     padding: 14px 16px;
     text-decoration: none;
   }

   /* The dropdown container */
   .dropdown {
     float: left;
     overflow: hidden;
   }

   /* Dropdown button */
   .dropdown .dropbtn {
     font-size: 16px;
     border: none;
     outline: none;
     color: white;
     padding: 14px 16px;
     background-color: inherit;
     font-family: inherit; /* Important for vertical align on mobile phones */
     margin: 0; /* Important for vertical align on mobile phones */
   }

   /* Add a red background color to navbar links on hover */
   .navbar a:hover, .dropdown:hover .dropbtn {
     background-color: red;
   }

   /* Dropdown content (hidden by default) */
   .dropdown-content {
     display: none;
     position: absolute;
     background-color: #f9f9f9;
     min-width: 160px;
     box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
     z-index: 1;
   }

   /* Links inside the dropdown */
   .dropdown-content a {
     float: none;
     color: black;
     padding: 12px 16px;
     text-decoration: none;
     display: block;
     text-align: left;
   }

   /* Add a grey background color to dropdown links on hover */
   .dropdown-content a:hover {
     background-color: #ddd;
   }

   /* Show the dropdown menu on hover */
   .dropdown:hover .dropdown-content {
     display: block;
   }
      </style>
         <title>GOW Device Manager</title>
      </head>
      <body > ";

      echo("<h1>GOW Device Manager $sel_domain $sel_device</h1>");
      echo "<div class=\"navbar\">";

      echo "<a href=\"gowDeviceManager.php?do=add_domain\">Add Domain</a>";

      echo "  <div class=\"dropdown\">
          <button class=\"dropbtn\">Select Information
            <i class=\"fa fa-caret-down\"></i>
          </button>
          <div class=\"dropdown-content\">
           ";
          echo "<a href=gowDeviceManager.php?do=info&what=static>static</a>";
          echo "<a href=gowDeviceManager.php?do=info&what=dynamic>dynamic</a>";
          echo "<a href=gowDeviceManager.php?do=info&what=payload>payload</a>";
          echo "<a href=gowDeviceManager.php?do=info&what=log>log</a>";
          echo "</div></div>";

        echo "<div class=\"dropdown\">
            <button class=\"dropbtn\">Select Domain
              <i class=\"fa fa-caret-down\"></i>
            </button>
            <div class=\"dropdown-content\">
            ";

            $do = 'ls *.domain > domain.list';
            system($do);
            $file = fopen('domain.list', "r");
            if ($file)
            {
              {
                $line = fgets($file);
                if (strlen($line) > 2)
                {
                    $line = trim($line);
                    $domain = str_replace(".domain", "", $line);
                    echo "<a href=gowDeviceManager.php?do=select&domain=$domain>$domain</a>";
                }
              }
            }
            echo "</div></div>";

            echo "<div class=\"dropdown\">
                  <button class=\"dropbtn\">Select Device
                    <i class=\"fa fa-caret-down\"></i>
                  </button>
                  <div class=\"dropdown-content\">
                  ";

                  $request = 'http://'.$sel_domain."/gowServer.php?do=list_topics";
                  //echo $request;
                  $ctx = stream_context_create(array('http'=>
                   array(
                     'timeout' => 2,  //2 Seconds
                       )
                     ));
                  $res = file_get_contents($request,false,$ctx);
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
                      $device = $topic[0];
                      for ($jj=1;$jj<$topic_num;$jj++)
                         $device = $device."/$topic[$jj]";
                      $doc = 'http://'.$sel_domain.'/'.$device;
                      $status = getStatus($doc);
                      $temp = $device;
                      if ($g_action == 2) $temp = '.'.$temp;
                      if ($status == 0)
                      {
                        echo "<a style=\"background: #2FBC63;\" href=gowDeviceManager.php?do=select&device=$device>$temp</a>";
                      }
                      else {
                        echo "<a style=\"background: #F5E50F;\" href=gowDeviceManager.php?do=select&device=$device>$temp $status</a>";
                      }
                      //echo " $g_rssi ";
                  }
                }
          echo "</div></div>";

      echo "<a href=\"gowDeviceManager.php?do=send_action\">Send Action</a>";

      echo "<div class=\"dropdown\">
            <button class=\"dropbtn\">Delete
              <i class=\"fa fa-caret-down\"></i>
            </button>
            <div class=\"dropdown-content\">
            ";
      echo "<a href=gowDeviceManager.php?do=delete&what=domain>$sel_domain</a>";
      echo "<a href=gowDeviceManager.php?do=delete&what=device>$sel_device</a>";
      echo "</div></div>";

      echo "</div>";
   //=============================================
//echo("<h1>GOW Device Manager $version</h1>");
//echo("url=$sel_url topic=$sel_path format=$sel_format<br>");
   //echo ("<a href=#>refresh</a><br>");
//echo "<a href=\"http://gow.simuino.com/gowDtManager.php\" target=\"_blank\">Model Manager</a>";

if ($form_send_action == 1)
{
  $doc = 'http://'.$sel_domain.'/'.$sel_device;
  $status = getStatus($doc);
  if ($g_action == 2)
  {
  echo "<br><br>
  <table border=0>";
  echo "
  <form action=\"#\" method=\"post\">
    <input type=\"hidden\" name=\"do\" value=\"send_message\">
    <tr><td>Domain</td><td> <input type=\"text\" name=\"domain\" value=$sel_domain></td>
    <tr><td>Device</td><td> <input type=\"text\" name=\"device\" value=$sel_device></td>
    <tr><td>Tag</td><td> <input type=\"text\" name=\"tag\" ></td>
    <tr><td>Message</td><td> <input type=\"text\" name=\"message\"></td>
    <td><input type= \"submit\" value=\"Send\"></td></tr>
  </form>
  </table>";
  }
  else {
    echo("<br>Device not able to receive orders<br>");
  }
}
if ($form_add_domain == 1)
{
  echo "
  <form action=\"#\" method=\"post\">
    <input type=\"hidden\" name=\"do\" value=\"add_domain\">
    Add Domain<input type=\"text\" name=\"domain\">
    <input type= \"submit\" value=\"Add Domain\">
    </form> ";
}

if ($flag_show_static == 1)
{
  $doc = 'http://'.$sel_domain.'/'.$sel_device.'/static.json';
  echo ("<br>static<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
}

if ($flag_show_dynamic == 1)
{
  $doc = 'http://'.$sel_domain.'/'.$sel_device.'/dynamic.json';
  echo ("<br>dynamic<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
}

if ($flag_show_payload == 1)
{
  $doc = 'http://'.$sel_domain.'/'.$sel_device.'/payload.json';
  echo ("<br>payload<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
}

if ($flag_show_log == 1)
{
  $doc = 'http://'.$sel_domain.'/'.$sel_device.'/log.gow';
  echo ("<br>log<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
}

echo "</body></html>";
// End of file

