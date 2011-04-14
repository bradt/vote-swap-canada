<?php
$sql = sprintf("SELECT * FROM users WHERE id = '%d'", $facebook->user);
$user = $db->select_one($sql);

$match = 0;
if ($user['match_user_id']) {
  $sql = sprintf("SELECT * FROM users WHERE id = '%d'", $user['match_user_id']);
  $match = $db->select_one($sql);
}

if (!$match) {
  echo '<fb:redirect url="/voteswapcanada/matches/" />';
  exit;
}

if ($match['match_status'] == 'cancel' || !$match['is_installed']) {
  $fields = array(
    'match_user_id' => 0,
    'match_status' => ''
  );
  $db->update('users', $fields, "id = '" . $user['id'] . "'");
  $db->update('users', $fields, "id = '" . $match['id'] . "'");
}
else {
  $fields = array('match_status' => 'cancel');
  $db->update('users', $fields, "id = '" . $user['id'] . "'");
}
?>
<fb:redirect url="/voteswapcanada/status/" />
