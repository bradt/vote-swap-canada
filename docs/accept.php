<?php
if (!isset($_GET['uid'])) {
  echo '<fb:redirect url="/voteswapcanada/matches/" />';
  exit;
}

$match_user_id = $_GET['uid'];

$sql = sprintf("SELECT * FROM users WHERE id = '%d'", $db->escape($match_user_id));
$match = $db->select_one($sql);

if (!$match || $match['match_user_id'] == $facebook->user) {
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

$sql = sprintf("SELECT * FROM users WHERE id = '%d'", $db->escape($facebook->user));
$user = $db->select_one($sql);

$sql = sprintf("
  SELECT user_id
  FROM users_willing_parties
  WHERE user_id = %d
    AND party_id = %d
", $match['id'], $request['request_party_id']);
$match_unchanged = $db->select_one($sql);

$sql = sprintf("
  SELECT user_id
  FROM users_willing_parties
  WHERE user_id = %d
    AND party_id = %d
", $facebook->user, $request['user_party_id']);
$user_unchanged = $db->select_one($sql);

if ($match['want_party_id'] != $request['user_party_id'] || !$match_unchanged) {
  ?>
  
  <h2 class="first article-title">Sorry, <fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /> has updated <fb:pronoun possessive="true" uid="<?php echo $match['id']; ?>" /> party preferences</h2>
  
  <p>
    Unfortunately, it appears that <b><fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /></b> has
    updated <fb:pronoun possessive="true" uid="<?php echo $match['id']; ?>" /> party preferences and is no longer
    compatible to swap votes with you.
  </p>

  <?php
}
elseif ($user['want_party_id'] != $request['request_party_id'] || !$user_unchanged) {
  ?>
  
  <h2 class="first article-title">Sorry, you've changed your party preferences</h2>
  
  <p>
    Unfortunately, it appears that you've changed your party preferences since
    <b><fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /></b> sent you
    this vote swap request and you are no longer compatible to swap votes.
  </p>

  <?php
}
elseif ($request['request_status'] != 'requested') {
  if ($request['request_status'] == 'cancelled') {
    ?>
    
    <h2 class="first article-title">Sorry, <fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /> has cancelled this request</h2>
    
    <p>
      Unfortunately, it appears that <b><fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /></b> has
      cancelled <fb:pronoun possessive="true" uid="<?php echo $match['id']; ?>" /> request to swap votes with
      you.
    </p>
  
    <?php
  }
  else {
    ?>
    
    <h2 class="first article-title">You've already responded to this request</h2>
    
    <p>
      It appers you've already <?php echo $request['request_status']; ?> this request.
    </p>
    
    <?php
  }
}
else {
  $fields = array(
    'request_status' => 'accepted',
    'updated' => $db->now()
  );
  $db->update('users_requests', $fields, "user_id = '" . $match['id'] . "' AND request_user_id = '" . $facebook->user . "'");

  if (!$match['is_installed']) {
    ?>
    
    <h2 class="first article-title">Sorry, <fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /> has removed the application</h2>
    
    <p>
      Unfortunately, it appears that <b><fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /></b> has
      removed the Vote Swap Canada application from <fb:pronoun possessive="true" uid="<?php echo $match['id']; ?>" /> Facebook profile.
    </p>
  
    <?php
  }
  elseif ($match['match_user_id']) {
    ?>
    
    <h2 class="first article-title">Sorry, <fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /> has already found a match</h2>
    
    <p>
      Unfortunately, it appears that <b><fb:name linked="false" firstnameonly="true" uid="<?php echo $match['id']; ?>" /></b> has
      already agreed to swap votes with someone else.
    </p>

    
    <?php
  }
  else {
    $fields = array(
      'match_user_id' => $match['id'],
      'match_status' => 'accepted'
    );
    $db->update('users', $fields, "id = '" . $facebook->user . "'");
    
    $fields = array(
      'match_user_id' => $facebook->user,
      'match_status' => 'accepted'
    );
    $db->update('users', $fields, "id = '" . $match['id'] . "'");

    $facebook->api_client->notifications_send($match['id'], 'has accepted your request to swap votes on <a href="http://apps.facebook.com/voteswapcanada/">Vote Swap Canada</a>.');
    //$facebook->api_client->feed_publishActionOfUser($match['id'], '<fb:name firstnameonly="true" uid="' . $match['id'] . '" /> has agreed to swap votes with <fb:name linked="false" uid="' . $facebook->user . '" /> on <a href="http://apps.facebook.com/voteswapcanada/">Vote Swap Canada</a>.', '');
    
    echo '<fb:redirect url="/voteswapcanada/status/" />';
    exit;
  }
}
?>
<p>
  <a href="/voteswapcanada/matches/">View your vote swap matches &raquo;</a>
</p>
