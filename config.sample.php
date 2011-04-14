<?php
$theme = 'default';

// MySQL settings
define('DB_NAME', '');     // The name of the database
define('DB_USER', '');     // Your MySQL username
define('DB_PASSWORD', ''); // ...and password
define('DB_HOST', 'localhost');     // 99% chance you won't need to change this value

define('DEBUG', 3);

define('FB_API_KEY', '');
define('FB_SECRET', '');

$config['name'] = 'Vote Swap Canada';
$config['siteurl'] = 'http://voteswap.sharurl.com/voteswapcanada';
$config['nobuffer'] = array();

// Filters
include('inc/filters.php');
?>
