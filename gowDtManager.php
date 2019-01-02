<?php
session_start();
$sel_twin = $_SESSION["twin"];
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
  echo "
     <form action=\"#\" method=\"post\">
       <input type=\"hidden\" name=\"do\" value=\"abcd\">
       ";

  $jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($inp, TRUE)),RecursiveIteratorIterator::SELF_FIRST);
  echo("<table border=0>");
  $level = 0;
  //echo "<tr><td>KEY</td><td>PAR</td><td>level</td><td>count</td><td>next</td><tr>";
  foreach ($jsonIterator as $key => $val) {

    if ($key == 'id') $id = $val;
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
          echo "<td bgcolor=\"#C5FD69\">$key</td><td><input type=\"text\" name=\"$key\" value=\"$val\"></td><tr>";
      }
      echo "</tr>";
   }
   echo"<tr><td><input type= \"submit\" value=\"Update\"></td></tr>";
   echo "</table>";
   if ($id == 'void') $id = generateRandomString(12);
   return $id;
}

//=============================================
// End of library
//=============================================

$rank = array();
//=============================================
// Back-End
//=============================================
if (isset($_GET['do']))
{

  $do = $_GET['do'];

  if($do == 'select_twin')
  {
    $sel_twin = $_GET['id'];
    $_SESSION["twin"] = $sel_twin;
  }
}


if (isset($_POST['do']))
{
  $do = $_POST['do'];

  if ($do == 'generateHtml')
  {
      $json = $_POST['json'];
      //echo("$json");
      $d = $_POST['d'];
      //echo("$d");
      $result = prettyTolk( $json);
      //generateWebPage($json);
      //generateHtml($json);
      $id = generateForm($json);
      $fname = $id.'.twin';
      $file = fopen($fname, "w");
      if ($file)
      {
        fwrite($file,$result);
        fclose($file);
      }
  }
  if ($do == 'abcd')
  {
    echo("b1");
      $a = $_POST['a'];
      echo("$a");
  }
}
//=============================================
// Front-End
//=============================================
echo "<html>
   <head>
      <title>InfoModel</title>
   </head>
   <body> ";

echo("<h1>Web Template</h1>");
echo ("<br><a href=gowDtManager.php?do=some&a=x>test_link</a>");

echo "<br>$sel_twin<br>
   <table border=0>";
/*echo "
   <form action=\"#\" method=\"post\">
     <input type=\"hidden\" name=\"do\" value=\"abcd\">
     <tr><td>A</td><td> <input type=\"text\" name=\"a\"></td>
     <tr><td>B</td><td> <input type=\"text\" name=\"b\"></td>
     <tr><td>C</td><td> <input type=\"text\" name=\"c\" ></td>
     <tr><td>D</td><td> <input type=\"text\" name=\"d\"></td>
     <td><input type= \"submit\" value=\"Send\"></td></tr>
   </form>
   </table>";
*/
$do = 'ls *.twin > twin.list';
system($do);
$file = fopen('twin.list', "r");
if ($file)
{
  while(!feof($file))
  {
    $line = fgets($file);
    //echo "<tr><td>$line</td><td>benny</td></tr>";
    if (strlen($line) > 2)
    {
        $line = trim($line);
        $twin = str_replace(".twin", "", $line);
        echo "<a href=gowDtManager.php?do=select_twin&id=$twin>$twin</a><br>";
    }
  }
}
echo "<br><br>
      <table border=1>";
echo "
      <form action=\"#\" method=\"post\" name=\"jjss\">
        <input type=\"hidden\" name=\"do\" value=\"generateHtml\">
        <tr><td>A</td><td> <textarea name=\"json\" rows=\"60\" cols=\"50\" >$json</textarea></td></tr>
        <tr><td>D</td><td> <input type=\"text\" name=\"d\"></td>
        <td><input type= \"submit\" value=\"Send\"></td></tr>
      </form>
      </table>";
$ff = $sel_twin.'.twin';
echo $ff;
echo ("<iframe src=$ff width=\"800\" height=\"800\"></iframe>");
//=============================================
// End of file
//=============================================
echo "</body></html>";
?>
