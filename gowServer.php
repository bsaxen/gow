<?php
//=============================================
// File.......: gowServer.php
// Date.......: 2018-11-13
// Author.....: Benny Saxen
// Description: Glass Of Water Server
//=============================================

$date         = date_create();
$sc_timestamp = date_format($date, 'Y-m-d H:i:s');
//=============================================
function readLatestPage($lbl,$prm)
//=============================================
{
    $spco_page = $lbl.'.txt';
    $file = fopen($spco_page,"r");
    if ($file)
    {
      while(! feof($file))
      {
        $line = fgets($file);
        sscanf($line,"%s",$work);
        if($work == 'LABEL')sscanf($line,"%s %s",$work,$label);
        if($work == 'VALUE')sscanf($line,"%s %f",$work,$value);
        if($work == 'DELTA')sscanf($line,"%s %f",$work,$delta);
        if($work == 'UNIT')sscanf($line,"%s %s",$work,$unit);
        if($work == 'DATETIME')sscanf($line,"%s %s",$work,$datetime);
        if($work == 'PERIOD')sscanf($line,"%s %d",$work,$period);
        if($work == 'POSITION')sscanf($line,"%s %s",$work,$position);
        if($work == 'DESCRIPTION')sscanf($line,"%s %s",$work,$description);
        if($work == 'SC_SERVER_ID')sscanf($line,"%s %s",$work,$sc_server_id);
        if($work == 'SC_HOST')sscanf($line,"%s %s",$work,$sc_host);
        if($work == 'SC_TIMESTAMP')sscanf($line,"%s %s",$work,$sc_timestamp);
      }
      fclose($file);
    }
    else {
      echo("Error");
    }

    //echo "$param : ${$param} <br>";
    return "${$prm}";
}
//=============================================
function writeSingle($lbl,$val)
//=============================================
{
  $spco_page = $lbl.'.single';
  $spcoFile = fopen($spco_page, "w");
  fwrite($spcoFile, "$val");
  fclose($spcoFile);
}
//=============================================
function readActionFile($lbl)
//=============================================
{
  $action_file = $lbl.'.action';
  $file = fopen($action_file, "r");
  if ($file)
  {
      $result = ":";
      while(! feof($file))
      {
        $line = fgets($file);
        //sscanf($line,"%s",$work);
        $result = $result.$line;
      }
      fclose($file);
      // Delete file
      if (file_exists($action_file)) unlink($action_file);
  }
  else {
      $result = "no_action";
  }
  return $result;
}
//=============================================
// End of library
//=============================================

if (isset($_GET['no'])) {
  $no = $_GET['no'];
}
else
{
  $error = 1;
}

if($operation == $store)
{
  $ok = 1;
  $delta = 0;
  $prev = 0;
  $cond = 0;

  if (isset($_GET['type'])) {
    $type = $_GET['type'];
  }

  if (isset($_GET['label'])) {
    $label = $_GET['label'];
    $cond++;
    $action = readActionFile($label);
    if($action != "no_action")echo("action=$action");
  }

  if (isset($_GET['value'])) {
    $value = $_GET['value'];
    $cond++;
  }

  if($cond == 2)
  {
    writeSingle($label, $value);
  }

  if (isset($_GET['unit'])) {
    $unit = $_GET['unit'];
  }

  // RunStepperMotorRaw
  if (isset($_GET['direction'])) {
    $direction = $_GET['direction'];
  }
  if (isset($_GET['steps'])) {
    $steps = $_GET['steps'];
  }
  if (isset($_GET['step_size'])) {
    $steps_size = $_GET['step_size'];
  }

  // RunStepperMotorRaw
  if (isset($_GET['extra'])) {
    $extra = $_GET['extra'];
  }

  // General
  if (isset($_GET['datetime'])) {
    $datetime = $_GET['datetime'];
    $cond++;
  } else {
    $datetime = "-";
  }

  if (isset($_GET['period'])) {
    $period = $_GET['period'];
  } else {
    $period = "-";
  }

  if (isset($_GET['position'])) {
    $position = $_GET['position'];
  } else {
    $position = "-";
  }

  if (isset($_GET['creator'])) {
    $creator = $_GET['creator'];
  } else {
    $creator = "-";
  }

  if (isset($_GET['description'])) {
    $description = $_GET['description'];
  } else {
    $description = "-";
  }

  //===========================================
  if($ok == 1)
  {
    //===========================================
    // HTML
    //===========================================
    $spco_page = $label.'.html';
    $spcoFile = fopen($spco_page, "w");
    fwrite($spcoFile, "<html>");
    fwrite($spcoFile, "<body bgcolor=\"#9EB14A\">");
    fwrite($spcoFile, "TYPE       ".$type);
    fwrite($spcoFile, "<br>");
    fwrite($spcoFile, "LABEL       ".$label);
    fwrite($spcoFile, "<br>");

    if ($type == 'RunStepperMotorRaw')
    {
      fwrite($spcoFile, "DIRECTION       ".$direction);
      fwrite($spcoFile, "<br>");
      fwrite($spcoFile, "STEPS       ".$steps);
      fwrite($spcoFile, "<br>");
      fwrite($spcoFile, "STEPS_SIZE       ".$step_size);
      fwrite($spcoFile, "<br>");
    }
    else if ($type == 'Trigger'){
      fwrite($spcoFile, "EXTRA       ".$extra);
      fwrite($spcoFile, "<br>");
    }
    else {
      fwrite($spcoFile, "VALUE       ".$value);
      fwrite($spcoFile, "<br>");
      fwrite($spcoFile, "DELTA       ".$delta);
      fwrite($spcoFile, "<br>");
    }


    fwrite($spcoFile, "UNIT        ".$unit);
    fwrite($spcoFile, "<br>");
    fwrite($spcoFile, "DATETIME    ".$datetime);
    fwrite($spcoFile, "<br>");
    fwrite($spcoFile, "PERIOD      ".$period);
    fwrite($spcoFile, "<br>");
    fwrite($spcoFile, "POSITION    ".$position);
    fwrite($spcoFile, "<br>");
    fwrite($spcoFile, "CREATOR     ".$creator);
    fwrite($spcoFile, "<br>");
    fwrite($spcoFile, "DESCRIPTION ".$description);
    fwrite($spcoFile, "<br>");
    //fwrite($spcoFile, "# SpaceCollapse Server Information<br>");
    fwrite($spcoFile, "SC_SERVER_ID ".$sc_server_id);
    fwrite($spcoFile, "<br>");
    fwrite($spcoFile, "SC_HOST      ".$sc_host);
    fwrite($spcoFile, "<br>");
    fwrite($spcoFile, "SC_TIMESTAMP ".$sc_timestamp);
    fwrite($spcoFile, "</body></html>");
    fclose($spcoFile);

    //===========================================
    // JSON
    //===========================================
    $spco_page = $label.'.json';
    $spcoFile = fopen($spco_page, "w");
    fwrite($spcoFile, "{\"spacecollapse\": {\n");
      fwrite($spcoFile, "   \"type\": \"$type\",\n");
      fwrite($spcoFile, "   \"label\": \"$label\",\n");

      if ($type == 'RunStepperMotorRaw')
      {
        fwrite($spcoFile, "   \"direction\": \"$direction\",\n");
        fwrite($spcoFile, "   \"steps\": \"$steps\",\n");
        fwrite($spcoFile, "   \"step_size\": \"$step_size\",\n");
      }
      else if ($type == 'Trigger'){
        fwrite($spcoFile, "   \"extra\": \"$extra\",\n");
      }
      else {
        fwrite($spcoFile, "   \"value\": \"$value\",\n");
        fwrite($spcoFile, "   \"delta\": \"$delta\",\n");
      }

      fwrite($spcoFile, "   \"unit\": \"$unit\",\n");
      fwrite($spcoFile, "   \"datetime\": \"$datetime\",\n");
      fwrite($spcoFile, "   \"period\": \"$period\",\n");
      fwrite($spcoFile, "   \"position\": \"$position\",\n");
      fwrite($spcoFile, "   \"creator\": \"$creator\",\n");
      fwrite($spcoFile, "   \"description\": \"$description\",\n");
      fwrite($spcoFile, "   \"sc_server_id\": \"$sc_server_id\",\n");
      fwrite($spcoFile, "   \"sc_host\": \"$sc_host\",\n");
      fwrite($spcoFile, "   \"sc_timestamp\": \"$sc_timestamp\"\n");
      fwrite($spcoFile, "}}\n ");
      fclose($spcoFile);

      //===========================================
      // TXT
      //===========================================
      $spco_page = $label.'.txt';
      $spcoFile = fopen($spco_page, "w");
      fwrite($spcoFile,   "TYPE         $type\n");
      fwrite($spcoFile,   "LABEL        $label\n");
      if ($type == 'RunStepperMotorRaw')
      {
        fwrite($spcoFile, "DIRECTION    $direction\n");
        fwrite($spcoFile, "STEPS        $steps\n");
        fwrite($spcoFile, "STEPS_SIZE   $step_size\n");
      }
      else if ($type == 'Trigger'){
        fwrite($spcoFile, "EXTRA        $extra\n");
      }
      else {
        fwrite($spcoFile, "VALUE        $value\n");
        fwrite($spcoFile, "DELTA        $delta\n");
      }
      fwrite($spcoFile,   "UNIT         $unit\n");
      fwrite($spcoFile,   "DATETIME     $datetime\n");
      fwrite($spcoFile,   "PERIOD       $period\n");
      fwrite($spcoFile,   "POSITION     $position\n");
      fwrite($spcoFile,   "CREATOR      $creator\n");
      fwrite($spcoFile,   "DESCRIPTION  $description\n");
      //fwrite($spcoFile, "# SpaceCollapse Server Information<br>");
      fwrite($spcoFile,   "SC_SERVER_ID $sc_server_id\n");
      fwrite($spcoFile,   "SC_HOST      $sc_host\n");
      fwrite($spcoFile,   "SC_TIMESTAMP $sc_timestamp\n");
      fclose($spcoFile);

    }
  }

  ?>
