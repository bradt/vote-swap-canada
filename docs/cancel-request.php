<?php
$request_user_id = $_GET['uid'];
$fields = array(
  'request_status' => 'cancelled',
  'updated' => $db->now()
);

$db->update('users_requests', $fields, "user_id = '" . $facebook->user . "' AND request_user_id = '" . $db->escape($request_user_id) . "'");
?>
<fb:redirect url="/voteswapcanada/matches/" />