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
// Date.......: 2019-03-30
// Author.....: Benny Saxen
// Description: Glass Of Water Platform Device Manager
$version = '2019-03-30';
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
function generateRandomString($length = 15)
//=============================================
{
    return substr(sha1(rand()), 0, $length);
}
//=============================================
function prettyTolk( $json )
//=============================================
{
    global $rank,$g_nn;
    $result = '';
    $level = 0;
    $nn = 1;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' )
        {
            $in_quotes = !$in_quotes;
        }
        else if( ! $in_quotes )
        {
            if($word)
            {
              $tmp = $rank[$nn];
              //echo ("$word nn=$nn level=$level<br>");
              //if($tmp > 0 && $tmp != $level) echo "JSON Error: $word<br>";
              $rank[$nn] = $level;
            }

            $word = '';
            switch( $char )
            {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;

                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $nn++;
                    //echo "nn=$nn<br>";
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        else {
          $word .= $char;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
        //echo "$level $char<br>";
    }
    $g_nn = $nn-1;
    return $result;
}

//=============================================
function generateForm($inp)
//=============================================
{
  global $rank,$g_nn;

  $id = 'void';

  $jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($inp, TRUE)),RecursiveIteratorIterator::SELF_FIRST);
  echo("<table border=0>");
  $nn = 0;
  foreach ($jsonIterator as $key => $val) {
    $nn++;
    if ($key == 'id') $id = $val;
    echo "<tr>";
    for($ii=1;$ii<$rank[$nn];$ii++)echo "<td></td>";

      if(is_array($val))
      {
        echo "<td color=\"#C5FD69\">$key</td>";
      }
      else
      {
          echo "<td>$key</td><td bgcolor=\"#C5FD69\">$val</td><tr>";
      }
      echo "</tr>";
   }
   echo "</table>";
   //if ($id == 'void') $id = generateRandomString(12);
   if ($g_nn != $nn)echo("ERROR: Key duplicate in JSON structure: $nn $g_nn<br>");
   return $id;
}
//=============================================
function addDomain ($domain)
//=============================================
{
  echo("[$domain]");
  $domain = $domain.'.domain';
  $fh = fopen($domain, 'w') or die("Can't add domain $domain");
}
//=============================================
function restApi($api,$domain,$device)
//=============================================
{
  //echo("RestApi [$api] domain=$domain device=$device<br>");
  $call = 'http://'.$domain.'/gowServer.php?do='.$api.'&topic='.$device;
  //echo $call;
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
    if ($what == 'log')
    {
      restApi('clearlog',$sel_domain,$sel_device);
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
      <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
      <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js\"></script>
      <style>

      #container {
      color: #336600;
      background-color: cornsilk;
      float: left;
      width: 1000px;
      height: 900px;
      }

      #status {
      color: #336600;
      //background-color: grey;
      float: left;
      width: 300px;

      }

      #static {
      color: #336600;
      //background-color: grey;
      float: left;
      width: 250px;

      }

      #dynamic {
      color: #336600;
      //background-color: red;
      float: left;
      width: 250px;

      }

      #payload {
      color: #336600;
      //background-color: blue;
      float: left;
      width: 400px;

      }


      #log {
      color: #336600;
      //background-color: yellow;
      float: left;
      width: 600px;

      }

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

//=========================================================================
// body
//=========================================================================
?>
<script type="text/javascript">



window.onload = function(){

    var tid = setInterval(getData, 3000);
    function getData() {
        console.log("Getting  data");
        $.ajax({
            url:		'gowDmAjax.php',
            /*dataType:	'json',*/
            dataType:	'text',
            success:	setData,
            type:		'GET',
            data:		{
                domain: '<?php echo("$sel_domain")?>',
                device: '<?php echo("$sel_device")?>'
            }
        });
    }
    function setData(result)
    {
        console.log("data!");
        console.log(result);
        var resArray = result.split("=");
        var n = resArray.length;
        console.log(n);

        var i;
        var input;
        for (i = 1; i <= n;i++)
        {
          console.log(resArray[i]);
          var id = 'no';
          id = id.concat(i.toString());
          input = document.getElementById(id);

          if (resArray[i] == 0)
            input.style.background = "green";
          if (resArray[i] > 0)
            input.style.background = "red";
        }
    }
}
</script>


<?php
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
      echo "<a href=gowDeviceManager.php?do=delete&what=log>clear log $sel_device</a>";
      echo "</div></div>";

      echo "</div>";

      // Ajax fields
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

      $nn = 0;
      echo "<div id=\"status\">";
      echo "<table>";
      for ($ii = 0; $ii < $num; $ii++)
      {
        echo "<tr>";
        $id = str_replace(".reg", "", $data[$ii]);
        if (strlen($id) > 2)
        {
          $nn += 1;
          echo "<td>$nn</td>";
          echo "<td>$id</td>";
          $topic = explode("_",$id);
          $topic_num = count($topic);
          //$link = 'http://'.$url;
          $device = $topic[0];
          for ($jj=1;$jj<=$topic_num;$jj++)
             $device = $device."/$topic[$jj]";
          $doc = 'http://'.$sel_domain.'/'.$device;
          $status = getStatus($doc);
          $temp = $device;
          if ($g_action == 2) $temp = '.'.$temp;
          if ($status == 0)
          {
            echo("<td><input style=\"background: #2FBC63;\" id=\"no$nn\" type=\"text\" name=\"n_no\" size=8 /></td>");
          }
          else {
            echo("<td><input style=\"background: #F5E50F;\" id=\"no$nn\" type=\"text\" name=\"n_no\" size=8 /></td>");
          }
        }
          echo ("</tr>");
      }
     echo "</table>";
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
if ($form_add_domain == 1)
{
  echo "
  <form action=\"#\" method=\"post\">
    <input type=\"hidden\" name=\"do\" value=\"add_domain\">
    Add Domain<input type=\"text\" name=\"domain\">
    <input type= \"submit\" value=\"Add Domain\">
    </form> ";
}

//  echo "<div id=\"container\">";
if ($flag_show_static == 1)
{
  echo "<div id=\"static\">";
  $doc = 'http://'.$sel_domain.'/'.$sel_device.'/static.json';
  $json   = file_get_contents($doc);
  $result = prettyTolk( $json);
  $id = generateForm($json);
  //echo ("<br>static<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
  echo "</div>";
}

if ($flag_show_dynamic == 1)
{
    echo "<div id=\"dynamic\">";
  $doc = 'http://'.$sel_domain.'/'.$sel_device.'/dynamic.json';
  $json   = file_get_contents($doc);
  $result = prettyTolk( $json);
  $id = generateForm($json);
  //echo ("<br>dynamic<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
    echo "</div>";
}

if ($flag_show_payload == 1)
{
    echo "<div id=\"payload\">";
  $doc = 'http://'.$sel_domain.'/'.$sel_device.'/payload.json';
  $json   = file_get_contents($doc);
  $result = prettyTolk( $json);
  $id = generateForm($json);
  //echo ("<br>payload<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
    echo "</div>";
}

if ($flag_show_log == 1)
{
  $rnd = generateRandomString(3);
    echo "<div id=\"log\">";
  $doc = 'http://'.$sel_domain.'/'.$sel_device.'/log.gow?ignore='.$rnd;
  echo ("<br>log<br><iframe id= \"ilog\" style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"600\"></iframe>");
    echo "</div>";
}
//  echo "</div";


echo "</body></html>";
// End of file
