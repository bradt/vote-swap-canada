<?php
if (!isset($_POST['ids'])) {
  echo '<fb:redirect url="/voteswapcanada/" />';
  exit;
}

$ids = $_POST['ids'];

foreach ($ids as $uid) {
  $data = array(
    'users_invites' => array(
      'id' => $facebook->user,
      'invite_user_id' => $uid,
      'created' => $db->now()
    )
  );
  $db->insert_one($data);
}

if (count($ids) > 1) {
  $plural = 's have';
}
else {
  $plural = ' has';
}
?>

<h2 class="first article-title">Your invitation<?php echo $plural; ?> been sent!</h2>

<p>
  Thank you for inviting <?php echo count($ids); ?> of your friends to join
  Vote Swap Canada. Hopefully you will match up with one of them and
  be able to swap votes.
</p>  

<p>
  Many people have application notifications disabled, so it may also
  be a good idea to <b><a
  href="http://www.facebook.com/inbox/?compose">send a more personal
  message</a></b> as well.
</p>

<p>
  <a href="/voteswapcanada/matches/">&laquo; Back to Your Matches</a>
</p>
