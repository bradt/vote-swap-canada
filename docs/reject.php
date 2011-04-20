<?php
if (!isset($_GET['uid'])) {
  echo '<fb:redirect url="/voteswapcanada/matches/" />';
  exit;
}

$user_id = $_GET['uid'];

$sql = sprintf("SELECT * FROM users WHERE id = '%d'", $db->escape($user_id));
$match = $db->select_one($sql);

if (!$match) {
  echo '<fb:redirect url="/voteswapcanada/matches/" />';
  exit;
}

$sql = sprintf("SELECT * FROM users_requests
  WHERE user_id = '%d' AND request_user_id = %d",
  $match['id'], $facebook->user);
$request = $db->select_one($sql);

if (!$request) {
  echo '<fb:redirect url="/voteswapcanada/matches/" />';
  exit;
}
?>

<h2 class="first article-title">Thank you for responding</h2>

<?php
if ($request['request_status'] == 'requested') {
  $fields = array(
    'request_status' => 'rejected',
    'updated' => $db->now()
  );
  
  $db->update('users_requests', $fields, "user_id = '" . $match['id'] . "' AND request_user_id = '" . $facebook->user . "'");

  if ($match['is_installed']) {
    $facebook->api_client->notifications_sendEmail(array($facebook->user), 'Your Vote Swap Canada 2011 request has been declined', '', '<fb:name linked="false" uid="' . $match['id'] . '" /> has declined your request to swap votes on <a href="http://apps.facebook.com/voteswapcanada/">Vote Swap Canada</a>.');
    // Deprecated by Facebook: $facebook->api_client->notifications_send($match['id'], 'has declined your request to swap votes on <a href="http://apps.facebook.com/voteswapcanada/">Vote Swap Canada</a>.');
  }
  ?>
  
  <p>
    <b><fb:name firstnameonly="true" linked="false" uid="<?php echo $match['id']; ?>" /></b>
    has been notified that <fb:pronoun possessive="true" uid="<?php echo $match['id']; ?>" />
    request to swap votes with you has been declined.
  </p>
  
  <?php
}
elseif ($request['request_status'] == 'cancelled') {
  ?>
  
  <p>
    <b><fb:name linked="false" uid="<?php echo $match['id']; ?>" /></b>
    had already cancelled <fb:pronoun possessive="true" uid="<?php echo $match['id']; ?>" />
    vote swap request, but thanks anyways.
  </p>
  
  <?php
}
?>
<p>
  <a href="/voteswapcanada/matches/">View your vote swap matches &raquo;</a>
</p>
