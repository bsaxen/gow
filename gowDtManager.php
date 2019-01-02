<?php
session_start();
$sel_twin = $_SESSION["twin"];
$flag_new_twin = $_SESSION["flag_new_twin"];
$flag_update_twin = $_SESSION["flag_update_twin"];
$flag_window_size = $_SESSION["flag_window_size"];

//=============================================
// File.......: gowDtManager.php
// Date.......: 2019-01-01
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
function generateRandomString($length = 15)
{
    return substr(sha1(rand()), 0, $length);
}

function prettyPrint( $json )
{
    $result = '';
    $level = 0;
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
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
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
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }
    echo $result;
    $file = fopen('test.json', "w");
    if ($file)
    {
      fwrite($file,$result);
      fclose($file);
    }
    return $result;
}

function prettyTolk( $json )
{
    global $rank;
    $result = '';
    $level = 0;
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
            //echo "benny $level $word<br>";
            $rank[$word] = $level;
            //echo "level=$rank[$word]<br>";
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

    return $result;
}
//=============================================
function generateHtml($inp)
//=============================================
{
  global $rank;
  $jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($inp, TRUE)),RecursiveIteratorIterator::SELF_FIRST);
  echo("<table border=1>");
  $level = 0;
  //echo "<tr><td>KEY</td><td>PAR</td><td>level</td><td>count</td><td>next</td><tr>";
  foreach ($jsonIterator as $key => $val) {

    //$rank[$key]
    echo "<tr>";
    for($ii=1;$ii<$rank[$key];$ii++)echo "<td></td>";

    //echo "<td>$key</td><td>$val</td><tr>";
      if(is_array($val))
      {
        echo "<td bgcolor=\"#FD6969\">$key</td>";
        //echo "<tr bgcolor=\"#FFF000\"><td>$key</td><td></td><td >$level</td><td>$nn[$level]</td><td>$count</td></tr>";
      }
      else
      {
          echo "<td bgcolor=\"#C5FD69\">$key</td><td>$val</td><tr>";
      }
      echo "</tr>";
   }
   echo "</table>";
}
//=============================================
function generateForm($inp)
//=============================================
{
  global $rank;

  $id = 'void';


  $jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($inp, TRUE)),RecursiveIteratorIterator::SELF_FIRST);
  echo("<table border=0>");
  foreach ($jsonIterator as $key => $val) {

    if ($key == 'id') $id = $val;

    echo "<tr>";
    for($ii=1;$ii<$rank[$key];$ii++)echo "<td></td>";

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

   return $id;
}
//=============================================
function generateTwinUI($id)
//=============================================
{
  global $rank;

  $filename = $id.'.twin';
  $json   = file_get_contents($filename);

  $filename = $id.'.html';
  $file = fopen($filename, "w");

  if ($file)
  {
    fwrite($file,"<h1>Digital Twin $val</h1>");
    $jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($json, TRUE)),RecursiveIteratorIterator::SELF_FIRST);
    fwrite($file,"<html>");

    foreach ($jsonIterator as $key => $val)
    {

      if ($key == 'id')
      {
        fwrite($file,"<h1>1Digital Twin $val</h1>");
      }

      if(is_array($val))
      {
          fwrite($file,"<h1>2Digital Twin $val</h1>");
      }
      else
      {
          fwrite($file,"<h1>3Digital Twin $val</h1>");
      }

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
   </style>
      <title>GOW DT Manager</title>
   </head>
   <body > ";
//=============================================
echo("<h1>GOW Digital Twin Manager</h1>");
if ($flag_new_twin == 1) {
  echo ("<a href=gowDtManager.php?do=cancel_new_twin&id=$sel_twin>Cancel New Twin</a>");
}
else {
  echo ("<a href=gowDtManager.php?do=new_twin&id=$sel_twin>New Twin</a>");  // code...
}
if ($flag_update_twin == 1) {
  echo (" <a href=gowDtManager.php?do=cancel_update_twin&id=x>Cancel Edit Twin</a>");
}
else {
  echo (" <a href=gowDtManager.php?do=update_twin&id=$sel_twin>Edit Twin</a>");  // code...
}
echo (" <a href=gowDtManager.php?do=html_twin&id=$sel_twin>HTML Twin</a>");

echo "<h2>$sel_twin</h2>";
$ff = $sel_twin.'.twin';
$fcount = count(file($ff));
$json   = file_get_contents($ff);
//echo "count=$fcount<br>";
//=============================================
echo("Available Twins<br>");
$do = 'ls *.twin > twin.list';
system($do);
$file = fopen('twin.list', "r");
if ($file)
{
  echo("<table border=1>");
  while(!feof($file))
  {
    $line = fgets($file);
    if (strlen($line) > 2)
    {
        $line = trim($line);
        $twin = str_replace(".twin", "", $line);
        echo "<tr><td>";
        echo "<a href=gowDtManager.php?do=select_twin&id=$twin>$twin</a>";
        echo "</td><td>";
        echo "<a href=gowDtManager.php?do=delete_twin&id=$twin>delete</a>";
        echo "</td></tr>";
    }
  }
  echo("</table>");
}
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
  echo " <form action=\"#\" method=\"post\" name=\"jjss\">";
  echo " <input type=\"hidden\" name=\"do\" value=\"create_new_twin\">";
  if($flag_window_size == 1)echo" <textarea name=\"json\" rows=\"$fcount\" cols=\"100\" >$json</textarea>";
  if($flag_window_size == 0)echo" <textarea name=\"json\" rows=\"10\" cols=\"100\" >$json</textarea>";
  echo "<br><input type= \"submit\" value=\"Add New Twin\">";
  echo "</form>";
}
//=============================================

  $result = prettyTolk( $json);
  $id = generateForm($json);
  $hh = $fcount*16;
  //echo ("<iframe src=$ff width=\"600\" height=\"$hh\"></iframe>");


//=============================================
// End of file
//=============================================
echo "</body></html>";
?>
