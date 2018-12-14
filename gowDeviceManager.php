<?php
//=============================================
// File.......: gowDeviceManager.php
// Date.......: 2018-12-14
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
// End of library
//=============================================


//=============================================
// Back-End
//=============================================


if (isset($_GET['do'])) {
  $do = $_GET['do'];
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
echo "<html>
   <head>
      <title>GOW Device Manager</title>
   </head>
   <body> ";
   $do = 'ls *.domain > domain.list';
   system($do);
   $file = fopen('domain.list', "r");
   if ($file)
   {
     while(!feof($file))
     {
       $line = fgets($file);

       if (strlen($line) > 2)
       {
           $line = trim($line);
           $url = str_replace(".domain", "", $line);
           $request = 'http://'.$url."/gowServer.php?do=list";
           echo $request;
           $res = file_get_contents($request);
           echo $res."<br>";
       }
     }
   }


// Add domain - Remove domain -
// List domains
    echo "
    <table border=1>
    <form action=\"#\" method=\"post\">
      <input type=\"hidden\" name=\"do\" value=\"add_domain\">
      <tr><td>Domain Url</td><td> <input type=\"text\" name=\"domain\" size=40></td></tr>
      <tr><td><input type= \"submit\" value=\"Add Domain\"></td><td></td></tr>
    </form></table>
    ";


echo "</body></html>";
// End of file
