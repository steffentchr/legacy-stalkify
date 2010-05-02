<?
error_reporting(E_ALL);

// DB
require_once 'DB.php';
require_once 'config.php';
$db = false;
function db_open() {
  global $db;
  global $dsn;

  $options = array(
                   'debug'       => 2,
                   'portability' => DB_PORTABILITY_ALL,
                   );

  $db = DB::connect($dsn, $options);
  $db->setFetchMode(DB_FETCHMODE_ASSOC);
  if (PEAR::isError($db)) {
    error_log('DB ERROR: ' . $res->getMessage());
    die($db->getMessage());
  }
}
function db_close() {
  global $db;
  $db->disconnect();
}
function db_query($sql) {
  global $db;
  $res = $db->query($sql);

  // Always check that result is not an error
  if (PEAR::isError($res)) {
    error_log('DB ERROR: ' . $res->getMessage());
    die($res->getMessage());
  }
  return($res);
}
function db_insert($table, $array) {
  global $db;
  $res = $db->autoExecute($table, $array, DB_AUTOQUERY_INSERT);

  // Always check that result is not an error
  if (PEAR::isError($res)) {
    error_log('DB ERROR: ' . $res->getMessage());
    die($res->getMessage());
  }
  return($res);
}
function db_update($table, $array, $where) {
  global $db;
  $res = $db->autoExecute($table, $array, DB_AUTOQUERY_UPDATE, $where);

  // Always check that result is not an error
  if (PEAR::isError($res)) {
    error_log('DB ERROR: ' . $res->getMessage());
    die($res->getMessage());
  }
  return($res);
}
function db_quote($string) {
  global $db;
  return($db->quoteSmart($string));
}
function db_nextval($seq) {
  global $db;
  return($db->nextId($seq));
}
function random_string($length) {
  $pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
  for($i=0;$i<$length;$i++) {
   if(isset($key))
     $key .= $pattern{rand(0,35)};
   else
     $key = $pattern{rand(0,35)};
  }
  return $key;
}
?>