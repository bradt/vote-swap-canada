<?php
$fields = array(
  'is_installed' => '0'
);

$db->update('users', $fields, "id = '" . $facebook->user . "'");
?>
