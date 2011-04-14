<?php

function init_facebook() {
    global $facebook;
    
    require_once ROOT . DS . '/lib/facebook.php';
    
    $facebook = new Facebook(FB_API_KEY, FB_SECRET);
    $user_id = $facebook->require_login();
}

add_filter('libraries_loaded', 'init_facebook');

?>