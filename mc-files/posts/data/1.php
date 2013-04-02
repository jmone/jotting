<?php
$str = file_get_contents('./dcy230.dat');
$data = unserialize($str);
print_r($data);
?>
