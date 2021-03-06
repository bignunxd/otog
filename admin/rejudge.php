<?php
session_start();
include 'config.php';
if (isset($_SESSION['user_id'])) {
  $task_id = $_GET['task_id'];

  $dir = '../judge/upload';

  $list = array();
  // make the list
  if ($handle = opendir($dir)) {
    // search in the directory
    while (($entry = readdir($handle)) !== false) {
      if ($entry != '.' && $entry != '..') {
        $chunks = explode('-', $entry);
        // starts with given task id
        if ($chunks[0] == strval($task_id)) {
          // get the username
          $user = explode('.', $chunks[1])[0];

          // select only the latest file (either .c, .cpp)
          $mod_time = filemtime($dir . '/' . $entry);
          if (!isset($list[$user])
            || (isset($list[$user]) && $list[$user]['mod_time'] < $mod_time))
            $list[$user] = array('file' => $entry, 'mod_time' => $mod_time);
        }
      }
    }
  }
  // get from list
  foreach ($list as $user => $each) {
    $entry = $each['file'];
    // get the user_id via username
    $user_id = null;
    $query = "select `user_id` from `user` where `user` = '{$user}' limit 1";
    $result = mysql_query($query) or die(mysql_error());
    $user_info = mysql_fetch_array($result);
    $user_id = $user_info['user_id'];
    if (!$user_id) {
      continue;
    }

    //remove pass
    $query = "delete from `pass` where `task_id` = {$task_id}";
    $result = mysql_query($query) or die(mysql_error());

    //remove bests
    $query = "delete from `best` where `task_id` = {$task_id}";
    $result = mysql_query($query) or die(mysql_error());

    //enqueue
    $date = new DateTime();
    $now = $date->getTimestamp();
    $query = "
      INSERT INTO `queue` (
      `queue_id` ,
      `user_id` ,
      `task_id` ,
      `time` ,
      `file`
      )
      VALUES (NULL , {$user_id}, {$task_id}, {$now}, '{$entry}')";
    $result = mysql_query($query) or die(mysql_error());

    echo "added {$entry} to rejudge.<br>";
  }
} else {
  die("you are neither admin nor logged in.");
}
