<?php

// Restrict
  if (php_sapi_name() == "cli") {
  }
  else if( empty($_SERVER['AUTH_TYPE']) ){
    header('HTTP/1.0 403 Forbidden');
    die('HTTP/1.0 403 Forbidden');
  }

// Prepare Outpu
  header('Content-type: text/plain');

// Define Base
  $base_path = getcwd();
  if( !file_exists($base_path . '/configuration.php') && isset($_SERVER['SCRIPT_FILENAME']) ){
    $base_path = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']), 0, -1));
  }
  if( !file_exists($base_path . '/configuration.php') ){
    header('HTTP/1.0 500 Internal Server Error');
    die('HTTP/1.0 500 Internal Server Error');
  }

// Load Joomla
  define('JOOMLA_MINIMUM_PHP', '5.3.10');
  if (version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '<')){
    die('Your host needs to use PHP ' . JOOMLA_MINIMUM_PHP . ' or higher to run this version of Joomla!');
  }
  define('_JEXEC', 1);
  if (file_exists($base_path . '/defines.php')){
    include_once $base_path . '/defines.php';
  }
  if (!defined('_JDEFINES')){
    define('JPATH_BASE', $base_path);
    require_once JPATH_BASE . '/includes/defines.php';
  }
  require_once JPATH_BASE . '/includes/framework.php';

// Initialize
  $app = JFactory::getApplication('site');

// Load Configuration
  $conf = JFactory::getConfig();

// Database Left
  $db1 = JDatabaseDriver::getInstance(array(
    'driver'   => $conf->get('dbtype'),
    'host'     => $conf->get('host'),
    'user'     => $conf->get('user'),
    'password' => $conf->get('password'),
    'database' => $conf->get('db'),
    'prefix'   => $conf->get('dbprefix')
    ));
  $db1_tableNames = array();
  $rows = $db1->setQuery("SHOW TABLES")->loadObjectList();
  foreach( $rows AS $row ){
    $db1_tableNames = array_merge( $db1_tableNames, array_values((array)$row) );
  }

// Database Right
  $db2 = JDatabaseDriver::getInstance(array(
    'driver'   => $conf->get('dbtype'),
    'host'     => $conf->get('host'),
    'user'     => $conf->get('user'),
    'password' => $conf->get('password'),
    'database' => $conf->get('db').'_compare',
    'prefix'   => $conf->get('dbprefix')
    ));
  $db2_tableNames = array();
  $rows = $db2->setQuery("SHOW TABLES")->loadObjectList();
  foreach( $rows AS $row ){
    $db2_tableNames = array_merge( $db2_tableNames, array_values((array)$row) );
  }

// Compare Key DB1
  echo str_pad('DB1 Name:', 15, ' ', STR_PAD_RIGHT) . $conf->get('db');
  echo "\n";
  echo str_pad('DB1 Tables:', 15, ' ', STR_PAD_RIGHT) . count($db1_tableNames);
  echo "\n\n";

// Compare Key DB1
  echo str_pad('DB2 Name:', 15, ' ', STR_PAD_RIGHT) . $conf->get('db').'_compare';
  echo "\n";
  echo str_pad('DB2 Tables:', 15, ' ', STR_PAD_RIGHT) . count($db2_tableNames);
  echo "\n\n";

// Compare Header
  echo str_pad('table_name', 50, ' ', STR_PAD_RIGHT);
  echo str_pad('db1_count', 15, ' ', STR_PAD_RIGHT);
  echo str_pad('db2_count', 15, ' ', STR_PAD_RIGHT);
  echo str_pad('diff', 10, ' ', STR_PAD_RIGHT);
  echo "\n";
  echo "\n";

// Compare Report
  foreach( $db1_tableNames AS $tableName ){
    if( in_array($tableName, $db2_tableNames) ){
      $db1_count = $db1->setQuery("SELECT COUNT(*) FROM " . $tableName)->loadResult();
      $db2_count = $db2->setQuery("SELECT COUNT(*) FROM " . $tableName)->loadResult();
      if( $db1_count != $db2_count ){
        echo str_pad(substr($tableName, strlen($conf->get('dbprefix'))), 50, ' ', STR_PAD_RIGHT);
        echo str_pad($db1_count, 15, ' ', STR_PAD_RIGHT);
        echo str_pad($db2_count, 15, ' ', STR_PAD_RIGHT);
        echo str_pad($db1_count - $db2_count, 10, ' ', STR_PAD_RIGHT);
        echo "\n";
      }
    }
  }
  echo "\n";

// Compare Header
  echo str_pad('table_errors', 50, ' ', STR_PAD_RIGHT);
  echo "\n";
  echo "\n";

  foreach( array_diff($db2_tableNames, $db1_tableNames) AS $tableName ){
    echo str_pad(substr($tableName, strlen($conf->get('dbprefix'))) . ' not present in DB1', 80, ' ', STR_PAD_RIGHT);
    echo "\n";
  }
  foreach( array_diff($db1_tableNames, $db2_tableNames) AS $tableName ){
    echo str_pad(substr($tableName, strlen($conf->get('dbprefix'))) . ' not present in DB2', 80, ' ', STR_PAD_RIGHT);
    echo "\n";
  }

// Close
  echo "\n";
  echo "complete" . "\n";
