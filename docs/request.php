<?php
$sql = sprintf("SELECT * FROM users WHERE id = '%d'", $facebook->user);
$user = $db->select_one($sql);

if ($user['match_user_id']) {
  echo '<fb:redirect url="/voteswapcanada/matches/" />';
  exit;
}

$sql = sprintf("SELECT * FROM users WHERE id = '%d'", $db->escape($_POST['request_user_id']));
$match_user = $db->select_one($sql);

$sql = sprintf("SELECT * FROM parties WHERE id = '%d'", $db->escape($_POST['user_party_id']));
$want_party = $db->select_one($sql);

$sql = sprintf("SELECT * FROM parties WHERE id = '%d'", $db->escape($_POST['request_party_id']));
$match_want_party = $db->select_one($sql);

$sql = sprintf("
  SELECT *
  FROM users_requests
  WHERE user_id = %d
    AND request_user_id = %d
  ", $facebook->user, $db->escape($match_user['id']));
$request = $db->select_one($sql);

$fields = array(
  'user_party_id' => $want_party['id'],
  'request_party_id' => $match_want_party['id'],
  'request_status' => 'requested',
  'updated' => $db->now()
);

if ($request) {
  $fields['count'] = $request['count']+1;
  $db->update('users_requests', $fields, "user_id = '" . $facebook->user . "' AND request_user_id = '" . $db->escape($match_user['id']) . "'");
}
else {
  $fields['user_id'] = $facebook->user;
  $fields['request_user_id'] = $match_user['id'];
  $fields['count'] = 1;
  $fields['created'] = $db->now();
  $data = array('users_requests' => $fields);
  $db->insert($data);
}
?>

<h2 class="article-title first">Your vote swap request has been sent</h2>

<p>
  Your vote swap request has been sent to <b><fb:name uid="<?php echo $match_user['id']; ?>" /></b>
  for <fb:pronoun objective="true" uid="<?php echo $match_user['id']; ?>" /> to vote
  <b><?php echo utf8_encode($want_party['name']); ?></b> for you and you to vote 
  <b><?php echo utf8_encode($match_want_party['name']); ?></b> for
  <fb:pronoun objective="true" uid="<?php echo $match_user['id']; ?>" />.
</p>

<h3>Follow-up with a personal message...</h3>

<p>
  Many people have application notifications disabled, so it may
  be a good idea to <b><a
  href="http://www.facebook.com/inbox/?compose&id=<?php echo $match_user['id']; ?>">send
  <fb:name linked="false" firstnameonly="true" uid="<?php echo $match_user['id']; ?>" />
  a more personal message</a></b> as well.
</p>

<p style="font-size: 1.1em; font-weight: bold;">
  <a href="/voteswapcanada/matches/">&laquo; Send some more requests</a>
</p>
