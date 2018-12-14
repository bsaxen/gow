<?php
//=============================================
// File.......: gowDeviceManager.php
// Date.......: 2018-12-15
// Author.....: Benny Saxen
// Description: Glass Of Water Platform Device Manager
//=============================================
// Configuration
//=============================================
// No configuration needed
//=============================================
$date         = date_create();
$ts           = date_format($date, 'Y-m-d H:i:s');
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
function restApi($rest)
//=============================================
{
  echo("RestApi [$rest]<br>");
  $res = file_get_contents($rest);
}
//=============================================
// End of library
//=============================================


//=============================================
// Back-End
//=============================================


if (isset($_GET['do'])) {
  $do = $_GET['do'];
  if($do == 'select')
  {
    $sel_doc = $_GET['sel_doc'];
  }
  if($do == 'rest_api')
  {
    $rest = $_GET['rest'];
    restApi($rest);
  }
}

if (isset($_POST['do'])) {
  $do = $_POST['do'];
  if ($do == 'add_domain')
  {
    $dn = $_POST['domain'];
    if (strlen($dn) > 2)addDomain($dn);
  }

  /*
  $request = $furl;
  $request = $request."gowServer.php?topic=$ftopic&action=$faction";
  $res = file_get_contents($request);
 */
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
   echo ("<a href=#>refresh</a><br>");

   echo ("<iframe src=$sel_doc></iframe>");

   $do = 'ls *.domain > domain.list';
   system($do);
   $file = fopen('domain.list', "r");
   if ($file)
   {
     echo "<br><br>
     <table border=1>";

     while(!feof($file))
     {
       $line = fgets($file);

       if (strlen($line) > 2)
       {
           $line = trim($line);
           $url = str_replace(".domain", "", $line);
           $request = 'http://'.$url."/gowServer.php?do=list";
           //echo $request;
           $res = file_get_contents($request);

           echo "<tr><td>$url</td><td></td></tr>";
           $data = explode(":",$res);
           $num = count($data);

           for ($ii = 0; $ii < $num; $ii++)
           {
             $tmp = str_replace(".reg", "", $data[$ii]);
             if (strlen($tmp) > 2)
             {
               $topic = explode("_",$tmp);
               $topic_num = count($topic);
               //$link = 'http://'.$url;
               $link = "";
               for ($jj=0;$jj<$topic_num;$jj++)
                  $link = $link."/$topic[$jj]";
               $doc = 'http://'.$url.$link.'/doc.html';
               echo "<tr><td></td><td><a href=gowDeviceManager.php?do=select&sel_doc=$doc>$link</a></td>";
               $rest = 'http://'.$url.'?do=delete&topic='.$link;
               //echo "<tr><td></td><td><a href=gowDeviceManager.php?do=select&sel_doc=$doc>$link</a></td>";
               echo "<td><a href=gowDeviceManager.php?do=rest_api&rest=$rest>delete</a></td></tr>";
             }
           }
       }
     }
     echo("</table>");



   }


// Add domain - Remove domain -
// List domains
    echo "<br><br>
    <table border=1>
    <form action=\"#\" method=\"post\">
      <input type=\"hidden\" name=\"do\" value=\"add_domain\">
      <tr><td>Domain Url</td><td> <input type=\"text\" name=\"domain\" size=40></td></tr>
      <tr><td><input type= \"submit\" value=\"Add Domain\"></td><td></td></tr>
    </form></table>
    ";


echo "</body></html>";
// End of file
