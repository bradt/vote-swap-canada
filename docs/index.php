<script type="text/javascript">
function party_want_change(obj) {
  var parties = document.getElementById('sel_party_want');
  parties = parties.getOptions();

  for (var i = 0; i < parties.getLength(); i++) {
    //new Dialog().showMessage('test', parties[i].getValue());
    var party_id = parties[i].getValue();
    var li = document.getElementById('willing-party-' + party_id);
    if (li) {
      li.setStyle('display', 'block');
    }
  }

  var want_party = obj.getValue();
  if (want_party) {
    document.getElementById('chk-willing-party-' + want_party).setChecked(false);
    document.getElementById('willing-party-' + want_party).setStyle('display', 'none');
  }
}

function other_party_checked(obj) {
  document.getElementById('willing-party-other').setStyle('display', 'none');
  var ul = document.getElementById('secondary-willing-parties');
  if (obj.getChecked()) {
    ul.setStyle('display', 'block');
  }
  else {
    ul.setStyle('display', 'none');
  }
}
</script>

<h2 class="first">Your Information</h2>

<form method="post" action="/voteswapcanada/">

  <?php
  $sql = sprintf("SELECT * FROM users WHERE id = '%d'", $facebook->user);
  $user = $db->select_one($sql);

  if (!$user) {
    $user_details = $facebook->api_client->users_getInfo($facebook->user, array('current_location'));

    $postal_code = $user_details[0]['current_location']['zip'];

    if ($postal_code) {
      $user = array(
        'id' => $facebook->user,
        'postal_code' => $postal_code
      );
    }
    else {
      $user = array(
        'id' => $facebook->user
      );
    }
    $user['created'] = $db->now();
    $data = array('users' => $user);
    $db->insert_one($data);

    $sql = sprintf("SELECT * FROM users WHERE id = '%d'", $facebook->user);
    $user = $db->select_one($sql);
  }

  extract($user);
  $error = array();
  $fields = array();
  
  if (!$is_installed) {
    $fields = array('is_installed' => '1');
    $db->update('users', $fields, "id = '" . $facebook->user . "'");
  }

  if ($match_user_id) {
    echo '<fb:redirect url="/voteswapcanada/status/" />';
    exit;
  }

  $sql = sprintf("SELECT party_id FROM users_willing_parties WHERE user_id = '%d'", $facebook->user);
  $tmp = $db->select($sql);
  $willing_parties = array();
  foreach ($tmp as $party) {
    $willing_parties[] = $party['party_id'];
  }

  if (isset($_POST['postal_code'])) {
    $postal_code = $_POST['postal_code'];

    if (!preg_match('@^[a-zA-Z]\d[a-zA-Z][ ]?\d[a-zA-Z]\d$@', $postal_code)) {
      $error['postal_code'] = 'Please enter a valid postal code.';
    }
    else {
      $fields['postal_code'] = $postal_code;
    }
  }

  if (isset($_POST['riding_id']) && $_POST['riding_id']) {
    $riding_id = $_POST['riding_id'];

    if (!$riding_id) {
      $error['riding_id'] = 'Please select a riding.';
    }
    else {
      $fields['riding_id'] = $riding_id;;
    }
  }

  if (isset($_POST['want_party_id']) && $_POST['want_party_id']) {
      $want_party_id = $_POST['want_party_id'];
      $fields['want_party_id'] = $want_party_id;
  }
  elseif (isset($_POST['find'])) {
    $want_party_id = 0;
    $error['want_party'] = 'Please choose the party you support.';
  }

  if (isset($_POST['willing_parties'])) {
      $willing_parties = array_keys($_POST['willing_parties']);

      $db->delete('users_willing_parties', "`user_id` = '" . $facebook->user . "'");
      foreach ($willing_parties as $party_id) {
        $data = array(
          'users_willing_parties' => array(
            'user_id' => $facebook->user,
            'party_id' => $party_id
          )
        );
        $db->insert($data);
      }
  }
  elseif (isset($_POST['find'])) {
    $willing_parties = array();
    $error['willing_parties'] = 'Please check the parties you\'re willing to vote for.';
  }

  if (!empty($fields) && !$match_user_id) {
    $db->update('users', $fields, "id = '" . $facebook->user . "'");
  }

  if (isset($_GET['change_riding']) || isset($error['postal_code']) || isset($error['riding_id'])) {
    $change_riding = true;
  }
  else {
    $change_riding = false;
  }

  if (isset($_GET['update'])) {
    $update = true;
  }
  else {
    $update = false;
  }

  if (($postal_code && !$riding_id) || (isset($_POST['postal_code']) && !isset($error['postal_code']))) {
    $fp = fsockopen("www.elections.ca", 80, $errno, $errstr, 30);
    if (!$fp) {
      echo "$errstr ($errno)<br />\n";
    }
    else {
      $out = sprintf("GET /scripts/pss/FindED.aspx?PC=%s HTTP/1.1\r\n", urlencode(str_replace(' ', '', $postal_code)));
      $out .= "Host: www.elections.ca\r\n";
      $out .= "Connection: Close\r\n\r\n";

      fwrite($fp, $out);
      $headers = '';
      while (!feof($fp)) {
        $headers .= fgets($fp, 128);
      }
      fclose($fp);
    }

    $riding_id = 0;
    if (preg_match('@Location\: /scripts/pss/InfovoteMain\.aspx\?L=e\&ED=([0-9]+)@', $headers, $matches)) {
      $riding_id = $matches[1];
    }
    else {
      $error['riding_detection'] = 'Could not find the riding for that postal code. Please choose your riding manually.';
    }

    $sql = sprintf("SELECT * FROM ridings WHERE id = '%d'", $riding_id);
    $riding = $db->select_one($sql);

    if ($riding) {
      $fields = array(
        'riding_id' => $riding['id']
      );

      $db->update('users', $fields, "id = '" . $facebook->user . "'");
    }
  }

  if (!$change_riding && !$update && empty($error)) {
    if ($riding_id && $want_party_id && !empty($willing_parties)) {
      echo '<fb:redirect url="/voteswapcanada/matches/" />';
      exit;
    }
  }

  if (!empty($error)) {
    ?>
    <p class="error-notice">Please correct the errors below and try submitting again.</p>
    <?php
  }

  if ($riding_id && !$change_riding) {
    $sql = sprintf("SELECT * FROM ridings WHERE id = '%d'", $riding_id);
    $riding = $db->select_one($sql);
    ?>
    <p style="font-size: 1.2em;">
      <label>Your riding is:</label> <?php echo utf8_encode($riding['name']) ?> (<a class="edit-link" href="?change_riding=1">Edit</a>)
    </p>
    <?php
  }
  else {

    if (isset($error['riding_detection']) || isset($error['riding_id'])) {

      $user_details = $facebook->api_client->users_getInfo($facebook->user, array('current_location'));
      $fb_province = $user_details[0]['current_location']['state'];

      $sql = "
        SELECT r.id, r.name, p.name as province
        FROM provinces p
          INNER JOIN ridings r ON p.id = r.province_id
        ORDER BY p.name, r.name";
      $ridings = $db->select($sql);
      ?>
      <div class="field select">
        <label for="sel_riding">Your Riding:</label>
        <select name="riding_id" id="sel_riding">
        <option value="">---</option>
        <?php
        $province = $ridings[0]['province'];
        printf('<optgroup label="%s">', utf8_encode($province));
        foreach ($ridings as $riding) {
          if ($riding['province'] != $province) {
            printf('</optgroup><optgroup label="%s">', utf8_encode($riding['province']));
            $province = $riding['province'];
          }

          printf('<option value="%d">%s</option>', $riding['id'], utf8_encode($riding['name']));
        }
        ?>
        </optgroup>
        </select>
        <?php
        if (isset($error['riding_id'])) {
          echo '<p class="error">' . $error['riding_id'] . '</p>';
        }
        else {
          echo '<p class="error">' . $error['riding_detection'] . '</p>';
        }
        ?>
      </div>
      <?php
    }
    else {
      ?>

      <div class="field text field-postal-code">
        <label for="txt_postal_code">Postal Code:</label>
        <input type="text" name="postal_code" id="txt_postal_code" value="<?php echo htmlentities($postal_code); ?>" size="6" />
        <p class="note">Privacy Notice: We promise to only use your postal code for looking
        up your riding and nothing more.</p>
        <?php
        if (isset($error['postal_code'])) {
          echo '<p class="error">' . $error['postal_code'] . '</p>';
        }
        ?>
      </div>

      <?php
    }
  }

  $sql = "SELECT * FROM parties ORDER BY sort,name";
  $parties = $db->select($sql);
  ?>

  <div class="field select field-question">
    <label for="sel_party_want">Which party do you support?</label>
    <?php
    if (isset($error['want_party'])) {
      echo '<p class="error">' . $error['want_party'] . '</p>';
    }
    ?>
    <select name="want_party_id" id="sel_party_want" onchange="party_want_change(this);">
    <?php
    $sort = 0;
    foreach ($parties as $party) {
      if ($sort != $party['sort']) {
        echo '<option value="">---</option>';
        $sort = $party['sort'];
      }
      
      if ($party['id'] == $want_party_id) {
        printf('<option value="%d" selected="selected">%s</option>', $party['id'], utf8_encode($party['name']));
      }
      else {
        printf('<option value="%d">%s</option>', $party['id'], utf8_encode($party['name']));
      }
    }
    ?>
    </select>
  </div>

  <div class="field checklist field-question">
    <label>
      Which parties are you willing to strategically vote for in your riding?
    </label>
    <p class="note">To be effective, the parties you select below should
    have a chance at winning in your riding.</p>
    <?php
    if (isset($error['willing_parties'])) {
      echo '<p class="error">' . $error['willing_parties'] . '</p>';
    }
    $sql = "SELECT * FROM parties WHERE sort = 1 ORDER BY name";
    $parties = $db->select($sql);
    ?>
    <ul id="primary-willing-parties">
    <?php
    foreach ($parties as $party) {
      $selected = (in_array($party['id'], $willing_parties)) ? ' checked="checked"' : '';
      $hidden = ($want_party_id == $party['id']) ? ' style="display:none;"' : '';
      printf('
        <li id="willing-party-%d"%s><input type="checkbox" name="willing_parties[%d]" id="chk-willing-party-%d" value="1"%s />
        <label for="chk-willing-party-%d">%s</label></li>',
        $party['id'], $hidden, $party['id'], $party['id'], $selected, $party['id'], utf8_encode($party['name']));
    }

    $sql = "SELECT * FROM parties WHERE sort = 2 ORDER BY name";
    $parties = $db->select($sql);

    $secondary_selected = false;
    foreach ($parties as $party) {
      if (in_array($party['id'], $willing_parties)) {
        $secondary_selected = true;
      }
    }
    ?>
      <?php if (!$secondary_selected)  { ?>
      <li id="willing-party-other"><input type="checkbox" name="willing_parties[0]" id="chk-willing-party-other" value="1" onclick="other_party_checked(this)" />
      <label for="chk-willing-party-other">Other</label></li>
      <?php } ?>
    </ul>
    <ul id="secondary-willing-parties"<?php echo (!$secondary_selected) ? ' style="display:none;"' : ''; ?>>
    <?php
    foreach ($parties as $party) {
      $selected = (in_array($party['id'], $willing_parties)) ? ' checked="checked"' : '';
      $hidden = ($want_party_id == $party['id']) ? ' style="display:none;"' : '';
      printf('
        <li id="willing-party-%d"%s><input type="checkbox" name="willing_parties[%d]" id="chk-willing-party-%d" value="1"%s />
        <label for="chk-willing-party-%d">%s</label></li>',
        $party['id'], $hidden, $party['id'], $party['id'], $selected, $party['id'], utf8_encode($party['name']));
    }
    ?>
    </ul>
  </div>

  <p><input type="submit" name="find" class="inputsubmit" value="Find a vote swap partner!" /></p>

</form>
