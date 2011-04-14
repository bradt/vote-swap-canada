<style type="text/css">
h2 {
	color:#444444;
	font-size:14px;
  margin: 1em 0;
}

h3 {
    margin: 0.5em 0;
}

ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
}

label {
    cursor: auto;
}

.cols {
	overflow: hidden;
  color: #666;
	clear: both;
	border-top: 1px solid #eee;
	padding-top: 10px;	
}

.col {
	float: left;
	width: 240px;
	margin-right: 20px;
}

.col-3 {
	margin: 0;
}

#header {
    position: relative;
    margin-bottom: 20px;
}

#header img.icon {
  float: left;
  margin-right: 5px;
}

#header ul {
  width: 300px;
  position: absolute;
  right: 0;
  top: 0;
  padding-right: 10px;
}

#header ul li {
  float: right;
}

#left {
	width: 240px;
	float: left;
  margin-right: 30px;
	margin-bottom: 100px;
}

#left ol {
  margin: 0 0 1em 0;
  padding-left: 20px;
}

#left ol li {
  margin-bottom: 0.6em;
}

#right {
	width: 480px;
	float: left;
	margin-bottom: 100px;
}

.error {
    color: #cc0000;
    margin: 0;
}

p.error-notice {
    font-weight: bold;
    color: #cc0000;
}

a.edit-link {
    font-size: 0.8em;
}

.first {
    margin-top: 0;
}

div.field {
    margin-bottom: 1.5em;
}

div.field p.note {
  margin: 0;
}

div.field label {
	font-weight: bold;
	color: #666;
	display: block;
}

div.field-postal-code {
  overflow: hidden;
}

div.field-postal-code label {
  float: left;
  width: 80px;
}

div.field-postal-code input {
  float: left;
}

div.field-postal-code p.note {
  float: left;
  width: 200px;
  margin-left: 40px;
}

div.checklist ul li label {
    display: inline;
    font-weight: normal;
    color: #000;
}

div.match-profile,
ul.matches li.match {
    overflow: hidden;
    padding: 10px;
    margin-bottom: 10px;
}

div.match-profile,
ul.matches li.even {
    background-color: #f3f3f3;
}

div.match-profile img.photo,
ul.matches li.match img.photo {
    float: left;
}

div.match-profile div.info,
ul.matches li.match div.details {
    float: left;
    margin-left: 10px;
    overflow: hidden;
}

ul.matches li.match div.details div.left {
  float: left;
  width: 230px;
  margin-right: 10px;
}

ul.matches li.match div.details div.wide {
  width: 390px;
}

ul.matches li.match div.details div.right {
  float: left;
  width: 160px;
}

div.match-profile div.info input,
ul.matches li.match div.details input {
    margin-top: 0.5em;
}

div.match-profile div.info p {
  margin: 0;
}

ul.related {
	margin-bottom: 10px;
}
</style>

<div id="header">
  <img src="http://photos-e.ak.facebook.com/photos-ak-sf2p/v43/156/39589050468/app_2_39589050468_7723.gif" class="icon" alt="" />
  <h2 class="first">Vote Swap Canada 2011</h2>
  <ul class="nav">
    <li><a href="http://www.facebook.com/apps/application.php?id=39589050468">About this Application</a></li>
  </ul>
</div>

<?php
$images = array(
  array('http://farm1.static.flickr.com/129/352382915_74cef79fe6_m.jpg', 'http://www.flickr.com/photos/95572727@N00/352382915'),
  array('http://farm3.static.flickr.com/2242/2533522140_0dc0c72acb_m.jpg', 'http://www.flickr.com/photos/57945291@N00/2533522140'),
  array('http://farm1.static.flickr.com/233/514232370_24ed141af2_m.jpg', 'http://www.flickr.com/photos/68729041@N00/514232370'),
  array('http://farm1.static.flickr.com/148/377969496_f50099f67b_m.jpg', 'http://www.flickr.com/photos/68729041@N00/377969496'),
  array('http://farm1.static.flickr.com/104/362894898_63eb5bdf4c_m.jpg', 'http://www.flickr.com/photos/68729041@N00/362894898'),
  array('http://farm1.static.flickr.com/124/323491922_26782e9584_m.jpg', 'http://www.flickr.com/photos/68729041@N00/323491922'),
  array('http://farm1.static.flickr.com/112/257507349_a50de84371_m.jpg', 'http://www.flickr.com/photos/68729041@N00/257507349'),
  array('http://farm1.static.flickr.com/54/144384899_9885d7d975_m.jpg', 'http://www.flickr.com/photos/36543076@N00/144384899'),
);
$rand = rand(0, count($images)-1);
$image = $images[$rand];
?>

<div id="left">
  <a href="<?php echo $image[1]; ?>" target="_blank" title="View this photo on Flickr">
    <img src="<?php echo $image[0]; ?>" alt="" border="0" /></a>

  <h3>Welcome to Vote Swap Canada!</h3>

  <p>An app allowing you to connect with people across
  Canada who want to increase the effectiveness of their vote and minimize <a
  href="http://en.wikipedia.org/wiki/Vote_splitting" target="_blank">vote
  splitting</a>.</p>
    
  <h3>How it Works</h3>
    
	<p>Charlie lives in the Edmonton Centre riding. She is a strong supporter of
	the NDP, but the NDP don't have a chance at winning in her riding. On
	the other hand, David is a Liberal supporter but lives in the Central Nova
	riding where the Liberals don't even have a candidate running.</p>
	
	<p>Charlie fills out the Vote Swap Canada form with her postal code, chooses
	the NDP as the party she wants to vote for, checks off Liberal and Green Party
	as the parties she's willing to vote for, and submits. David had already
	done this a day earlier, but had no matches. Charlie however is matched up with
	David and a few others. She submits a request to swap votes with David. David
	receives Charlie's request and accepts. On election day, Charlie votes Liberal
	and David votes NDP.</p>

	<h3>Instructions</h3>
  
  <ol>
    <li>
      Submit your riding, party you support, and parties you're
      willing to vote for.
    </li>
    <li>
      Review your matches.
    </li>
    <li>
      Submit requests to swap votes with the people you've matched with.
      You can submit as many requests as you like.
    </li>
    <li>
      Once someone accepts your request, you vote for their supported party on
			election day and they vote for yours
    </li>
  </ol>

  <h3>Related Links</h3>
  
  <ul class="related">
    <li><a href="http://www.elections.ca/" target="_blank">Elections Canada</a></li>
    <li><a href="http://www.votepair.ca/" target="_blank">Pair Vote</a></li>
    <li><a href="http://www.cbc.ca/news/politics/canadavotes2011/">CBC - Canada Votes 2011</a></li>
  </ul>
</div>

<div id="right">
