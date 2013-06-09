<?php
/**
 * 系统初始化
 */
define('JOTTING_INC', dirname(__FILE__) . '/');
require JOTTING_INC . 'db.php';
$db_config = require JOTTING_INC . 'config.php';

$db = new db();
$db->connect($db_config);
?>
