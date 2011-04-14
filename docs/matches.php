<?php
$sql = sprintf("SELECT * FROM users WHERE id = '%d'", $facebook->user);
$user = $db->select_one($sql);

if ($user['match_user_id']) {
  echo '<fb:redirect url="/voteswapcanada/status/" />';
  exit;
}

$sql = sprintf("SELECT * FROM ridings WHERE id = '%d'", $user['riding_id']);
$riding = $db->select_one($sql);

$sql = sprintf("SELECT * FROM parties WHERE id = '%d'", $user['want_party_id']);
$want_party = $db->select_one($sql);

$sql = sprintf("SELECT party_id FROM users_willing_parties WHERE user_id = '%d'", $facebook->user);
$tmp = $db->select($sql);
$willing_parties = array();
foreach ($tmp as $party) {
  $willing_parties[] = $party['party_id'];
}

$willing_parties_sql = implode("','", $willing_parties);

$sql = sprintf("
  SELECT u.*, r.name as riding_name, pr.name as province_name, p.name as want_party_name, ur1.request_status as received_request_status, ur2.request_status as sent_request_status
  FROM users u
    INNER JOIN users_willing_parties uwp ON uwp.user_id = u.id
    INNER JOIN ridings r ON u.riding_id = r.id
    INNER JOIN provinces pr ON pr.id = r.province_id
    INNER JOIN parties p ON u.want_party_id = p.id
    LEFT OUTER JOIN users_requests ur1 ON ur1.request_user_id = u.id AND ur1.user_id = %d
    LEFT OUTER JOIN users_requests ur2 ON ur2.request_user_id = %d AND ur2.user_id = u.id
  WHERE u.riding_id != %d
    AND uwp.party_id = %d
    AND u.want_party_id != %d
    AND u.want_party_id IN ('%s')
    AND u.is_installed = '1'
    AND u.match_user_id = 0
  ORDER BY rand()
  LIMIT 0,40
  ", $user['id'], $user['id'], $user['riding_id'], $user['want_party_id'], $user['want_party_id'], $willing_parties_sql);
$matches = $db->select($sql);
?>

<h2 class="first">Your Matches</h2>

<?php
if (count($matches) > 0) {
  if (count($matches) == 1) {
    $people = ' person';
    $are = ' is';
  }
  else {
    $people = ' people';
    $are = ' are';
  }
  ?>

  <p>
    We've found <?php echo count($matches), $people; ?> who <?php echo $are; ?> willing to vote
    <b><?php echo utf8_encode($want_party['name']); ?></b>
    who <?php echo $are; ?> <u>not</u> in your riding of <b><?php echo utf8_encode($riding['name']); ?></b>.
    <a href="/voteswapcanada/?update=1" class="edit-link">Update your match criteria</a>
  </p>

  <ul class="matches">

  <?php
  $i = 0;
  foreach ($matches as $match) {
    $sql = sprintf("
      SELECT p.*
      FROM users_willing_parties uwp
        INNER JOIN parties p ON p.id = uwp.party_id
      WHERE user_id = '%d'", $match['id']);
    $willing_parties = $db->select($sql);

    $willing_party_names = array();
    foreach ($willing_parties as $party) {
      $willing_party_names[] = $party['name'];
    }
    $willing_party_names = implode(', ', $willing_party_names);

    if ($i % 2 == 0) {
      $even = ' even';
    }
    else {
      $even = '';
    }
    ?>
    <li class="match<?php echo $even; ?>">
      <fb:profile_pic uid="<?php echo $match['id']; ?>" class="photo" linked="true" size="square" />
      <div class="details">
        <div class="left<?php echo (!$match['received_request_status'] && !in_array($match['sent_request_status'], array('requested', 'cancelled'))) ? ' wide' : ''; ?>">
          <h4><fb:name uid="<?php echo $match['id']; ?>" /></h4>
          <label>Riding:</label> <?php echo utf8_encode($match['riding_name']), ', ', utf8_encode($match['province_name']); ?><br />
          <label>Supports:</label> <?php echo utf8_encode($match['want_party_name']); ?><br />
          <label>Willing to vote for:</label> <?php echo utf8_encode($willing_party_names); ?><br />

          <?php if ($match['received_request_status'] != 'requested' && !in_array($match['sent_request_status'], array('requested', 'cancelled'))) : ?>

          <fb:request-form method="post"
            action="/voteswapcanada/request/" type="Vote Swap Canada"
            content="Would you like to vote <b><?php echo utf8_encode($want_party['name']); ?></b> for me and I'll vote <b><?php echo utf8_encode($match['want_party_name']); ?></b> for you? <?php echo htmlentities('<fb:req-choice url="http://apps.facebook.com/voteswapcanada/accept/?uid=' . $user['id'] . '" label="Yes" /><fb:req-choice url="http://apps.facebook.com/voteswapcanada/reject/?uid=' . $user['id'] . '" label="No" />'); ?>"
            <fb:request-form-submit uid="<?php echo $match['id']; ?>" label="Ask %n to swap votes" />
            <input type="hidden" name="request_user_id" value="<?php echo $match['id']; ?>" />
            <input type="hidden" name="user_party_id" value="<?php echo $user['want_party_id']; ?>" />
            <input type="hidden" name="request_party_id" value="<?php echo $match['want_party_id']; ?>" />
          </fb:request-form>
            
          <?php endif; ?>
        </div>

        <?php if ($match['sent_request_status'] == 'requested') : ?>
        
        <div class="right">
          <b><fb:name firstnameonly="true" linked="false" uid="<?php echo $match['id']; ?>" /> has already sent you a request to swap votes.</b>
          <input type="button" name="accept" class="inputbutton" value="Accept request" onclick="document.setLocation('/voteswapcanada/accept/?uid=<?php echo $match['id']; ?>')" />
        </div>

        <?php elseif ($match['sent_request_status'] == 'rejected') : ?>

        <div class="right">
          <b>You rejected <fb:name firstnameonly="true" linked="false" uid="<?php echo $match['id']; ?>" />'s last vote swap request.</b>
        </div>

        <?php elseif ($match['received_request_status'] == 'requested') : ?>
        
        <div class="right">
          <b>You've already sent a request to swap votes with <fb:name firstnameonly="true" linked="false" uid="<?php echo $match['id']; ?>" />.</b>
          <a href="/voteswapcanada/cancel-request/?uid=<?php echo $match['id']; ?>">Cancel request</a><br />
          <input type="button" name="cancel" class="inputbutton request_form_submit" value="Send a message" onclick="document.setLocation('http://www.facebook.com/inbox/?compose&id=<?php echo $match['id']; ?>')" />
        </div>

        <?php elseif ($match['received_request_status'] == 'rejected') : ?>

        <div class="right">
          <b><fb:name firstnameonly="true" linked="false" uid="<?php echo $match['id']; ?>" /> rejected your last vote swap request.</b>
        </div>

        <?php elseif ($match['received_request_status'] == 'cancelled') : ?>

        <div class="right">
          <b>You cancelled your last vote swap request with <fb:name firstnameonly="true" linked="false" uid="<?php echo $match['id']; ?>" />.</b>
        </div>

        <?php endif; ?>
      </div>
    </li>
    <?php
    $i++;
  }
  ?>

  </ul>
  
  <br /><br /><br /><br />

<?php
}
else {
  ?>
  
  <p>
    Currently we can't find anyone outside your riding of <b><?php echo utf8_encode($riding['name']); ?></b>
    who is willing to vote <b><?php echo utf8_encode($want_party['name']); ?></b>.
    <a href="/voteswapcanada/?update=1" class="edit-link">Update your match criteria</a>
  </p>
  
  <?php  
}

if (count($matches) < 5) {
  $fql = sprintf("
    SELECT uid FROM user
    WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=%d)
    AND is_app_user = 1
  ",$user['id']);
  $_friends = $facebook->api_client->fql_query($fql);
  
  $friends = array();
  if (is_array($_friends) && count($_friends)) {
    foreach ($_friends as $friend) {
      $friends[] = $friend['uid'];
    }
  }
  $friends = implode(',', $friends);
  
  ob_start();
  ?>
  <fb:name firstnameonly="true" linked="false" uid="<?php echo $user['id']; ?>" /> is using
  Vote Swap Canada to connect with people who want to vote strategically this
  election and thought you might want to give it a try.
  <fb:req-choice url="<?php echo $facebook->get_add_url(); ?>" label="Try Vote Swap Canada now"/>
  <?php
  $content = ob_get_clean();
  ?>

  <fb:request-form method="post" invite="true"
    action="/voteswapcanada/invite/" type="Vote Swap Canada"
    content="<?php echo htmlentities($content); ?>"
    <fb:multi-friend-selector max="10" cols="3" rows="4" showborder="true"
      actiontext="Maybe inviting your friends to use Vote Swap Canada will help get you a match."
      exclude_ids="<? echo $friends; ?>" />
  </fb:request-form>
  
  <?php
}
