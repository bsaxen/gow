<?php
session_start();
$sel_twin = $_SESSION["twin"];
$flag_new_twin = $_SESSION["flag_new_twin"];
$flag_update_twin = $_SESSION["flag_update_twin"];
$flag_window_size = $_SESSION["flag_window_size"];

$g_nn = 0;
$work_dir = 'work';
if ( !file_exists($work_dir) ) {
     mkdir ($work_dir, 0744);
}
//=============================================
// File.......: gowDtManager.php
// Date.......: 2019-02-15
// Author.....: Benny Saxen
// Description:
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
   if ($id == 'void') $id = generateRandomString(12);
   if ($g_nn != $nn)echo("ERROR: Key duplicate in JSON structure: $nn $g_nn<br>");
   return $id;
}
//=============================================
function generateTwinUI($id)
//=============================================
{
  global $rank;

  define("INFRASTRUCTURES", 1);
  define("CHANNELS", 2);
  define("ACTUATORS", 3);

  $filename = $id.'.twin';
  $json   = file_get_contents($filename);

  $filename = $id.'.html';
  $file = fopen($filename, "w");

  if ($file)
  {
    //fwrite($file,"<h1>Digital Twin $val</h1>");
    $jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($json, TRUE)),RecursiveIteratorIterator::SELF_FIRST);
    fwrite($file,"<html>");

    $nch = 0;
    $nin = 0;
    $nac = 0;
    foreach ($jsonIterator as $key => $val)
    {
      if ($key == 'id') $id = $val;
      if ($key == 'date') $date = $val;

      $hit = 0;
      if ($key == 'infrastructures')
      {
        $state = INFRASTRUCTURES;
        $hit = 1;
      }
      if ($key == 'channels')
      {
        $state = CHANNELS;
        $hit = 1;
      }
      if ($key == 'actuators')
      {
        $state = ACTUATORS;
        $hit = 1;
      }
      //fwrite($file,"$key : $val<br>");
      if(is_array($val) && $hit == 0)
      {
          if ($state == INFRASTRUCTURES)
          {
            $nin++;
            echo "IN $key <br>";
            $infrastructure[$nin] = $key;
          }
          if ($state == CHANNELS)
          {
            $nch++;
            echo "CH $key <br>";
            $channel[$nch] = $key;
          }
          if ($state == ACTUATORS)
          {
            $nac++;
            echo "AC $key <br>";
            $actuator[$nac] = $key;
          }


      }
      else if ($hit == 0)
      {
          echo "$state ---------$key = $val <br>";
          if ($state == INFRASTRUCTURES)
          {
            if ($key == 'protocol') $protocol[$nin] = $val;
            if ($key == 'broker')   $broker[$nin] = $val;
            if ($key == 'port')     $port[$nin] = $val;
          }
          if ($state == CHANNELS)
          {
            if ($key == 'source')   $channel_source[$nch] = $val;
            if ($key == 'type')     $channel_type[$nch] = $val;
            if ($key == 'stream')   $channel_stream[$nch] = $val;
            if ($key == 'payload')  $channel_payload[$nch] = $val;
          }
          if ($state == ACTUATORS)
          {
            if ($key == 'type')     $actuator_type[$nin] = $val;
            if ($key == 'stream')   $actuator_stream[$nin] = $val;
            if ($key == 'param1')   $actuator_param1[$nin] = $val;
            if ($key == 'param2')   $actuator_param2[$nin] = $val;
          }
          //fwrite($file,"<h1>3Digital Twin $val</h1>");
      }


   }

   for ($ii = 1; $ii <= $nin; $ii++)
   {
     echo "$ii $infrastructure[$ii]<br>";
   }

   for ($ii = 1; $ii <= $nch; $ii++)
   {
     echo "$ii $channel[$ii]<br>";
     for ($jj = 1; $jj <= $nin; $jj++)
     {
       if ($channel_source[$ii] == $infrastructure[$jj]) $match = $jj;
     }
     echo $match;
     $doc = 'http://'.$broker[$match].'/'.$channel_stream[$ii].'/payload.json';
     echo "$doc <br>";
     fwrite($file,"<br><br><iframe id= \"ilog\" style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"600\"></iframe>");
   }

   for ($ii = 1; $ii <= $nac; $ii++)
   {
     echo "$ii $actuator[$ii]<br>";
   }

  }
  fclose($file);

  return;
}
//=============================================
// End of library
//=============================================

$rank = array();
//=============================================
// Back-End
//=============================================

// GET
if (isset($_GET['do']))
{
$_SESSION["flag_window_size"];
  $do = $_GET['do'];

  if($do == 'html_twin')
  {
    $sel_twin = $_GET['id'];
    generateTwinUI($sel_twin);
  }
  if($do == 'select_twin')
  {
    $sel_twin = $_GET['id'];
    $_SESSION["twin"] = $sel_twin;
  }
  if($do == 'large_window')
  {
    $flag_window_size = 1;
    $_SESSION["flag_window_size"] = $flag_window_size;
  }
  if($do == 'small_window')
  {
    $flag_window_size = 0;
    $_SESSION["flag_window_size"] = $flag_window_size;
  }
  if($do == 'new_twin')
  {
    $flag_new_twin = 1;
    $_SESSION["flag_new_twin"] = $flag_new_twin;
  }
  if($do == 'update_twin')
  {
    $flag_update_twin = 1;
    $_SESSION["flag_update_twin"] = $flag_update_twin;
  }
  if($do == 'delete_twin')
  {
    $twin_id = $_GET['id'];
    $filename = $twin_id.".twin";
    if (file_exists($filename)) unlink($filename);
  }
  if($do == 'cancel_update_twin')
  {
    $flag_update_twin = 0;
    $_SESSION["flag_update_twin"] = $flag_update_twin;
  }
  if($do == 'cancel_new_twin')
  {
    $flag_new_twin = 0;
    $_SESSION["flag_new_twin"] = $flag_new_twin;
  }
  if($do == 'clear_window')
  {
    $flag_clear_window = 1;
  }
  if($do == 'api')
  {
    $flag_api = 1;
  }
}

// POST
if (isset($_POST['do']))
{
  $do = $_POST['do'];

  if ($do == 'create_new_twin')
  {
      $json = $_POST['json'];
      $ob = json_decode($json);
      if($ob === null) {
        $failure = 1;
        echo ("<h1>JSON Validation Error</h1>");
      }
      if ($failure != 1)
      {
        $result = prettyTolk( $json);
        $id = generateForm($json);
        $fname = $id.'.twin';
        $file = fopen($fname, "w");
        if ($file)
        {
          fwrite($file,$result);
          fclose($file);
        }
      }
  }
  if ($do == 'update_twin')
  {
      $twin_id = $_POST['twin_id'];
      $json = $_POST['json'];
      $ob = json_decode($json);
      if($ob === null) {
        $failure = 1;
        echo ("<h1>JSON Validation Error</h1>");
      }
      if ($failure != 1)
      {
        //$result = prettyTolk( $json);
        //$not_used = generateForm($json);
        $fname = $twin_id.'.twin';
        $file = fopen($fname, "w");
        if ($file)
        {
          fwrite($file,$json);
          fclose($file);
        }
        else {
          echo "Unable to write to file: $fname<br>";
        }
      }
  }
  //====================
}
//=============================================
// Front-End
//=============================================
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
      <title>GOW DT Manager</title>
   </head>
   <body > ";
//=============================================
echo("<h1>GOW Digital Twin Manager 2019-01-03</h1>");
echo "<div class=\"navbar\">
  <a href=\"$sel_twin.html\" target=\"_blank\">UI $sel_twin</a> ";

  if ($flag_new_twin == 1)
  {
    echo "<a href=\"gowDtManager.php?do=cancel_new_twin&id=$sel_twin\">Cancel New Twin</a>";
  }
  else {
    echo "<a href=\"gowDtManager.php?do=new_twin&id=$sel_twin\">New Twin</a>";
  }
  if ($flag_update_twin == 1) {
    echo "<a href=\"gowDtManager.php?do=cancel_update_twin&id=$sel_twin\">Cancel Edit Twin</a>";
  }
  else {
    echo "<a href=\"gowDtManager.php?do=update_twin&id=$sel_twin\">Edit Twin</a>";
  }

echo "  <div class=\"dropdown\">
    <button class=\"dropbtn\">Select Twin
      <i class=\"fa fa-caret-down\"></i>
    </button>
    <div class=\"dropdown-content\">
    ";

    //echo("Available Twins<br>");
    $do = 'ls *.twin > twin.list';
    system($do);
    $file = fopen('twin.list', "r");
    if ($file)
    {
      while(!feof($file))
      {
        $line = fgets($file);
        if (strlen($line) > 2)
        {
            $line = trim($line);
            $twin = str_replace(".twin", "", $line);
            echo "<a href=gowDtManager.php?do=select_twin&id=$twin>$twin</a>";
        }
      }
    }
    echo "</div></div>";
echo "<div class=\"dropdown\">
      <button class=\"dropbtn\">Delete Twin
        <i class=\"fa fa-caret-down\"></i>
      </button>
      <div class=\"dropdown-content\">
      ";

      //echo("Available Twins<br>");
      //$do = 'ls *.twin > twin.list';
      //system($do);
      $file = fopen('twin.list', "r");
      if ($file)
      {
        while(!feof($file))
        {
          $line = fgets($file);
          if (strlen($line) > 2)
          {
              $line = trim($line);
              $twin = str_replace(".twin", "", $line);
              echo "<a href=gowDtManager.php?do=delete_twin&id=$twin>$twin</a>";
          }
        }
      }
      echo "</div></div>";

      echo "<div class=\"dropdown\">
            <button class=\"dropbtn\">Generate
              <i class=\"fa fa-caret-down\"></i>
            </button>
            <div class=\"dropdown-content\">
            ";
    echo "<a href=\"gowDtManager.php?do=html_twin&id=$sel_twin\">HTML</a>";
    echo "</div></div>";
    echo "<a href=\"gowDtManager.php?do=run_twin&id=$sel_twin\">Run Twin</a>";
    echo "<a href=\"gowDtManager.php?do=api\">API</a>";
    echo "<a href=\"http://gow.simuino.com/gowDeviceManager.php\" target=\"_blank\">Device Manager</a>";
echo "</div>";

if ($flag_clear_window == 0)
{
 $ff = $sel_twin.'.twin';
 $fcount = count(file($ff));
 $json   = file_get_contents($ff);
}
//echo "count=$fcount<br>";

//=============================================
if ($flag_update_twin == 1)
{
  echo" <br>Edit Twin Model JSON Structure below<br>";
  if($flag_window_size == 0)echo "<a href=gowDtManager.php?do=large_window>Large Window</a>";
  if($flag_window_size == 1)echo "<a href=gowDtManager.php?do=small_window>Small Window</a>";
  echo" <form action=\"#\" method=\"post\"><input type=\"hidden\" name=\"do\" value=\"update_twin\">";
  echo" <input type=\"hidden\" name=\"twin_id\" value=\"$sel_twin\">";
  if($flag_window_size == 1)echo" <textarea name=\"json\" rows=\"$fcount\" cols=\"100\" >$json</textarea>";
  if($flag_window_size == 0)echo" <textarea name=\"json\" rows=\"10\" cols=\"100\" >$json</textarea>";
  echo" <br><input type= \"submit\" value=\"Update $sel_twin\">";
  echo "</form>";
}
//=============================================
if ($flag_new_twin == 1)
{
  echo "<br>Add New Twin Model JSON Structure below<br>";

  if($flag_window_size == 0)echo "<a href=gowDtManager.php?do=large_window>Large Window</a>";
  if($flag_window_size == 1)echo "<a href=gowDtManager.php?do=small_window>Small Window</a>";
  echo "<a href=gowDtManager.php?do=clear_window>Clear</a>";
  echo " <form action=\"#\" method=\"post\" name=\"jjss\">";
  echo " <input type=\"hidden\" name=\"do\" value=\"create_new_twin\">";
  if($flag_window_size == 1)echo" <textarea name=\"json\" rows=\"$fcount\" cols=\"100\" >$json</textarea>";
  if($flag_window_size == 0)echo" <textarea name=\"json\" rows=\"10\" cols=\"100\" >$json</textarea>";
  echo "<br><input type= \"submit\" value=\"Add New Twin\">";
  echo "</form>";
}
//=============================================
  $filename = $sel_twin.'.twin';
  $json   = file_get_contents($filename);
  $result = prettyTolk( $json);
  $id = generateForm($json);
  $hh = $fcount*16;
  //echo ("<iframe src=$ff width=\"600\" height=\"$hh\"></iframe>");


//=============================================
// End of file
//=============================================
echo "</body></html>";
?>

