<?php
$sql = sprintf("SELECT * FROM users WHERE id = '%d'", $facebook->user);
$user = $db->select_one($sql);

if (!$user['match_user_id']) {
  echo '<fb:redirect url="/voteswapcanada/matches/" />';
  exit;
}
  
$sql = sprintf("
  SELECT u.*, r.name as riding_name, pr.name as province_name, p.name as want_party_name
  FROM users u
    INNER JOIN ridings r ON u.riding_id = r.id
    INNER JOIN provinces pr ON pr.id = r.province_id
    INNER JOIN parties p ON u.want_party_id = p.id
  WHERE u.id = '%d'", $user['match_user_id']);
$match_user = $db->select_one($sql);

$sql = sprintf("SELECT * FROM parties WHERE id = '%d'", $user['want_party_id']);
$want_party = $db->select_one($sql);

$sql = sprintf("
  SELECT p.*
  FROM users_willing_parties uwp
    INNER JOIN parties p ON p.id = uwp.party_id
  WHERE user_id = '%d'", $match_user['id']);
$willing_parties = $db->select($sql);

$willing_party_names = array();
foreach ($willing_parties as $party) {
  $willing_party_names[] = $party['name'];
}
$willing_party_names = implode(', ', $willing_party_names);
?>

<h2 class="article-title first">You've agreed to swap votes</h2>

<p>
  You and <b><fb:name uid="<?php echo $match_user['id']; ?>" /></b> have
  agreed to swap votes. <fb:name firstnameonly="true" linked="false" uid="<?php echo $match_user['id']; ?>" />
  will vote <b><?php echo utf8_encode($want_party['name']); ?></b> for you and you will vote
  <b><?php echo utf8_encode($match_user['want_party_name']); ?></b> for
  <fb:pronoun objective="true" uid="<?php echo $match_user['id']; ?>" />.
</p>

<p>So, on election day...</p>

<p style="font-size: 1.3em;">
  Don't forget, you're voting <b><?php echo utf8_encode($match_user['want_party_name']); ?></b>.
</p>

<div class="match-profile">
  <fb:profile_pic uid="<?php echo $match_user['id']; ?>" class="photo" linked="true" size="square" />
  <div class="info">
    <h4><fb:name uid="<?php echo $match_user['id']; ?>" /></h4>
    <label>Riding:</label> <?php echo utf8_encode($match_user['riding_name']), ', ', utf8_encode($match_user['province_name']); ?><br />
    <label>Supports:</label> <?php echo utf8_encode($match_user['want_party_name']); ?><br />
    <label>Voting:</label> <?php echo utf8_encode($want_party['name']); ?><br />
  
    <?php
    if ($user['match_status'] == 'cancel') {
      ?>
    
      <p style="font-size: 1.1em; font-weight: bold;">
        You've sent <fb:name linked="false" firstnameonly="true" uid="<?php echo $match_user['id']; ?>" />
        a request to cancel this arrangement but they have not accepted yet.
      </p>
    
      <p>
        <input type="button" name="cancel" class="inputbutton request_form_submit" value="Send a message" onclick="document.setLocation('http://www.facebook.com/inbox/?compose&id=<?php echo $match_user['id']; ?>')" />
      </p>
      <?php
    }
    elseif ($match_user['match_status'] == 'cancel') {
      ?>
    
      <p style="font-size: 1.1em; font-weight: bold;">
        <fb:name linked="false" firstnameonly="true" uid="<?php echo $match_user['id']; ?>" /> has
        requested to cancel this agreement.
      </p>
    
      <p>
        <input type="button" name="cancel" class="inputbutton" value="Yes, cancel our vote swap" onclick="document.setLocation('/voteswapcanada/cancel/')" />
      </p>
      
      <?php
    }
    else {
      ?>
    
      <p>
        <input type="button" name="cancel" class="inputbutton request_form_submit" value="Send a message" onclick="document.setLocation('http://www.facebook.com/inbox/?compose&id=<?php echo $match_user['id']; ?>')" />
      </p>
    
      <fb:request-form method="post"
        action="/voteswapcanada/cancel/" type="Vote Swap Canada"
        content="I'd like to cancel our vote swap arrangement. Do you agree? <?php echo htmlentities('<fb:req-choice url="http://apps.facebook.com/voteswapcanada/cancel/" label="Accept" />'); ?>"
        <fb:request-form-submit uid="<?php echo $match_user['id']; ?>" label="Ask %n to cancel our vote swap" />
      </fb:request-form>
    
      <?php
    }
    ?>
  </div>
</div>