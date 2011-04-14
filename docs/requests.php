<?php
$sql = sprintf("
  SELECT u.*, r.name as riding_name, pr.name as province_name, p.name as want_party_name, ur.request_status
  FROM users_requests ur
    INNER JOIN users u ON ur.user_id = u.id
    INNER JOIN ridings r ON u.riding_id = r.id
    INNER JOIN provinces pr ON pr.id = r.province_id
    INNER JOIN parties p ON u.want_party_id = p.id
  WHERE ur.request_user_id = %d
    AND u.is_installed = '1'
    AND u.match_user_id = 0
  ", $facebook->user);
$matches = $db->select($sql);
?>

<h2 class="first">Your Received Requests</h2>

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
      <div class="left<?php echo (!$match['request_status']) ? ' wide' : ''; ?>">
        <h4><fb:name uid="<?php echo $match['id']; ?>" /></h4>
        <label>Riding:</label> <?php echo utf8_encode($match['riding_name']), ', ', utf8_encode($match['province_name']); ?><br />
        <label>Supports:</label> <?php echo utf8_encode($match['want_party_name']); ?><br />
        <label>Willing to vote for:</label> <?php echo utf8_encode($willing_party_names); ?><br />

        <?php if ($match['request_status'] != 'requested') : ?>

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

      <?php if ($match['request_status'] == 'requested') : ?>
      
      <div class="right">
        <b>You've already sent a request to swap votes with <fb:name firstnameonly="true" linked="false" uid="<?php echo $match['id']; ?>" />.</b>
        <a href="/voteswapcanada/cancel-request/?uid=<?php echo $match['id']; ?>">Cancel request</a><br />
        <input type="button" name="cancel" class="inputbutton request_form_submit" value="Send a message" onclick="document.setLocation('http://www.facebook.com/inbox/?compose&id=<?php echo $match['id']; ?>')" />
      </div>

      <?php elseif ($match['request_status'] == 'rejected') : ?>

      <div class="right">
        <b><fb:name firstnameonly="true" linked="false" uid="<?php echo $match['id']; ?>" /> rejected your last vote swap request.</b>
      </div>

      <?php elseif ($match['request_status'] == 'cancelled') : ?>

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
