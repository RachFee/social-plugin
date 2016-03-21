<?php 
/*
Plugin Name: Rachel Fee - Social Plugin
Plugin URI: http://rachel-fee.com/
Author: Rachel Fee
Started: March 2016
V1: 
Phone: (519)476-0239
Email: rachel.s.fee@gmail.com
*/

//TODO: Ensure all strings are wrapped in DOUBLE quoted (as more escape sequences are recognized this way)
//TODO: Echo vs Print - Let's go with Echo

//include APIS
require_once("APIs/twitter/TwitterAPIExchange.php");
require_once("APIs/facebook/src/Facebook/autoload.php");
require_once("rachel-social-plugin-admin.php");

?>
<?php 
//init scricpt
function addPortfolioScript() {
    wp_enqueue_style('portfolio_script', '/wp-content/plugins/rachel-social-plugin/css/style.css');
}
add_action('wp_head', 'addPortfolioScript');
//add shortcode 
add_shortcode('rachel-fee-social-stream', 'callSocialStream');

	
//main function to call from shortcode
function callSocialStream(){
	//get specifications from settings
	$adminOptions = getAllOptionsData();

	//create an array to hold all Twitter and Facebook posts
	$allPosts = array();
	// type, text, timestamp, link, image

	//call social media
	//keep separate now for later sorting addition
	$tweets = getTwitter($adminOptions['rsp-twHandle'], $adminOptions['rsp-twIncludeRTs'], $adminOptions['rsp-twIncludeReplies']); 
	$fbposts = getFacebook($adminOptions['rsp-fbPageLink']); 
	$map = getMap($adminOptions['rsp-gmAddress'], $adminOptions['rsp-gmMapType'], $adminOptions['rsp-gmMarkerColour']);
	//marge into one array
	$allPosts = array_merge($tweets, $fbposts);

	//TODO: Add sorting options
	shuffle($allPosts);

	//displayGrid will handle output
	displayGrid($allPosts, $map);
}



function getTwitter($username, $includeRTs = false, $includeReplies = false){
	//array to hold tweets
	$tweets = array();

	/* set Tokens */ 
	$settings = array(
	    "oauth_access_token" => "101379836-FzpztopZphZqSUIlO46bXYs5IBcY5Iv8a6B9qivk",
	    "oauth_access_token_secret" => "RXlbMBQ5GZEIkyblKd6l4DCKqjYYl4cAxXayKxPRETSyQ",
	    "consumer_key" => "DedmVZ0NQjKb0pJMwudXTiXPS",
	    "consumer_secret" => "y8rXQilLsMvj36RZXDW9YvSXoWwpl0m6uy5RdBrCxibrlPoIoX"
	);


	$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
	$method = "GET";
	/*$getfield = array(
	    'screen_name' => $username 
	);*/
	$getfield = "?screen_name=".$username;
	$getfield .= "&include_rts=".$includeRTs;
	//parameter for replies is based on EXCLUDE, so true/false needs to be inverted.
	//More user friendly to do this on the back end and just allow the user to answer yes/no for each one to be *included*.
	$excludeRepliesParam = ($includeReplies ? "false" : "true");
	$getfield .= "&exclude_replies=".$excludeRepliesParam;
	//twitter API limitation - RTs and replies are always included in the count, even if they're excluded. 
	//Therefore, let's only specify 6 if we're NOT including RTs and replies. If we ARE including RTs and replies,
	//let's pull the maximum (200) and perform our own count. Unfortunately there's no way to 
	//guarantee these 200 tweets have 6 non-RTs, so we'll have to include a fallback for <6 tweets.
	if ($includeRTs && $includeReplies){
		$getfield .= '&count=6';
	}else{
		//initialize counter
		$counter = 0; 
	}

	//TODO - put this in a try - catch block 
	$twitter = new TwitterAPIExchange($settings);
	$data = json_decode($twitter->setGetfield($getfield)
			->buildOauth($url, $method)
            ->performRequest());

	//check for errors (such as auth issues)
	if (isset($data->errors) && !empty($data->errors)){
		return false;
	}

	foreach($data AS $tweetNum => $tweetData){
		 //new tweet
		 $newTweet = array(
		 	"type" => "twitter",
		 	"text" => $tweetData->text, 
		 	"timestamp" => strtotime($tweetData->created_at),  //unix for easy sorting and formatting
		 	"link" => "http://twitter.com/".$username."/status/".$tweetData->id

		 );

		 //1 - add photo link from tweet
		 //if no photo, use profile picture
		 //If no profile picture, use a placeholder twitter image
		 if (isset($tweetData->entities->media[0]->media_url) && !empty($tweetData->entities->media[0]->media_url)){
		 	$newTweet['image'] = $tweetData->entities->media[0]->media_url;
		 }else if (isset($data[0]->user->profile_image_url) && !empty($data[0]->user->profile_image_url)){
		 	$newTweet['image'] = str_replace("normal", "400x400", $data[0]->user->profile_image_url);
		 }else{
		 	$newTweet['image'] = "/img/twitterdefault.png";
		 } 

		 //push onto stack
		 array_push($tweets, $newTweet);

		 //Perform count at end and exit if we hit 6
		 if (!$includeRTs || !$includeReplies){
		 	$counter++;
		 	if ($counter >= 6){
		 		return $tweets;
		 	}
		 }
		 
		 
	}
	//TODO: less than 6? call again with max_id of last tweet
	
	//TODO: Add fallback if less than 6
	/*
	while ($counter < 6){
		//add empty tweet?
	}
	*/

	//return stack
	return $tweets;

}

/*Use Facebook graph API (php-sdk) to return the first 6 posts from a specified page. */
function getFacebook($username){
	
	//array to hold posts
	$posts = array();

	//we'll use appID/appSecret method to prevent early expiry
	$access_token = "1705359549748418". "|" . "b309bd61f621d800df32d10814285cc5";
	
	//TODO: put this in a try-catch block
	$fb = new Facebook\Facebook([
	  	"app_id" => "1705359549748418",
	  	"app_secret" => "b309bd61f621d800df32d10814285cc5",
	  	"default_graph_version" => "v2.5",
	  	"default_access_token" => $access_token
	]);
	
	/** This block is the typically documented request method which is overly complicated, 
	 but I'm including for demonstration purposes**/// start ->
	/*	$fbApp = $fb->getApp();
		$request = new Facebook\FacebookRequest(
	  		$fbApp,
	  		$access_token,
	  		'GET',
	  		'/londonlibrary/feed'
		);
		 $response = $fb->getClient()->sendRequest($request);
		 $graphNode = $response->getGraphNode(); 
		 $pageID =  $graphNode['id'];   */
	/* <-end block... Surely there must be a simpler way? //---------- */

	//This block is the lesser documented and much simpler method which I'll be using. 
	//It returns a plain old array. What more do we need?
	//we can also directly specify the limit in the query here as it is calculated properly by facebook
	//TODO: Put this in a try-catch
	$feed = $fb->get('/'.$username.'/feed?limit=6')->getDecodedBody();
	
	//check for errors (such as auth issues or page missing)
	if (isset($feed->error) && !empty($feed->error)){
		return false;
	}

	//get profile img for backup. We'll just do this once before the loop as it's not included in the post object.
	//specify 300x300, though it still returns a 320
	//TODO: put in try - catch as this will throw a fatal error 
	$profilePhoto = $fb->get("/".$username."/picture?width=300&height=300")->getHeaders()['Location'];

	foreach ($feed['data'] AS $postNum => $postValue){
 		//unix timestamp for easy sorting and formatting
		$newPost = array(
		 	"type" => "facebook",
		 	"text" => $postValue['message'], 
		 	"timestamp" => strtotime($postValue['created_time']), //unit timestamp for easy sorting and formatting 
			"link" => "http://facebook.com/".$postValue['id']
		); //TODO: fix link

		//use the postID to get the post object so we can get the media attached to the post
		$postInfo = $fb->get("/".$postValue['id']."?fields=picture")->getDecodedBody();

		//add photo from post
		 //if no photo, use profile picture
		 //If no profile picture, use a placeholder fb image
		 if (isset($postInfo['picture']) && !empty($postInfo['picture'])){
		 	$newPost['image'] = $postInfo['picture'];
		 }else if (isset($profilePhoto) && !empty($profilePhoto)){
		 	$newPost['image'] = $profilePhoto;
		 }else{
		 	$newPost['image'] = "/img/twitterdefault.png";
		 }

		//push onto stack
		array_push($posts, $newPost);
	}

	//TODO: Add fallback if there are less than 6 posts in total
	/*
	while ($counter < 6){
		//add empty post?
	}
	*/

	//return stack
	return $posts;



}

/* Use google maps static API to display a customized map */
function getMap($address, $mapType, $markerColour){
	
	//static maps API key
	$key = "AIzaSyAdIcd8IhJiRMl7SKAeIwvFQIQr2OAnRIU";
	//get map, use encoded address
	//size 1000x1000 so we can adjust later
	//let's build this separately so we can adjust settings as we go
	//TODO: Put this in try-catch
	$mapString = "https://maps.googleapis.com/maps/api/staticmap?center=".urlencode($address);
	$mapString .= "&maptype=".$mapType; //type
	$mapString .= "&size=600x600"; //size
	$mapString .= "&markers=size:mid|color:".$markerColour."|".urlencode($address);
	$mapString .= "&key=".$key; //key is required.

	return $mapString;
}

function displayGrid($allPosts, $map){
?>
	<div class="rsocial" style="width:1000px; margin-left:-75px"> <!--style being overwritten by WP somewhere, use inline-->
		<!--top row-->
		<ul class="rsocial-ul grid-row">
			<li class="sm-grid-sq"><?php displayPost($allPosts[0]); ?></li> 
			<li class="sm-grid-sq"><?php displayPost($allPosts[1]); ?></li>
			<li class="sm-grid-sq"><?php displayPost($allPosts[2]); ?></li>
			<li class="sm-grid-sq"><?php displayPost($allPosts[3]); ?></li>
		</ul>
		<!--left column-->
		<ul class="rsocial-ul grid-col">
			<li class="sm-grid-sq"><?php displayPost($allPosts[4]); ?></li>
			<li class="sm-grid-sq"><?php displayPost($allPosts[5]); ?></li>
		</ul>	
		<!--center block-->
		<ul class="rsocial-ul center-block">
			<li><img src="<?php echo $map; ?>"></li>
		</ul>
		<!--right column-->
		<ul class="rsocial-ul grid-col">
			<li class="sm-grid-sq"><?php displayPost($allPosts[6]); ?></li>
			<li class="sm-grid-sq"><?php displayPost($allPosts[7]); ?></li>
		</ul>

		<!--bottom row-->
		<ul class="rsocial-ul grid-row" style="margin-top:-8px">
			<li class="sm-grid-sq"><?php displayPost($allPosts[8]); ?></li> 
			<li class="sm-grid-sq"><?php displayPost($allPosts[9]); ?></li>
			<li class="sm-grid-sq"><?php displayPost($allPosts[10]); ?></li>
			<li class="sm-grid-sq"><?php displayPost($allPosts[11]); ?></li>
		</ul>
	</div>
<?
}

function displayPost($post){
	//TODO: trim text appropriately
	//TODO: horizontally center images
	//$text = substr($post['text'], 0, 100);
	//if 100 characters or longer, trim at first space after the 100 and add "...";
	if(strlen($post['text']) > 100){
		$text = substr($post['text'], 0, strpos($post['text'], " ", 100)). "... [Read More]";
	}else{
		$text = $post['text'];
	}
	echo "<div><a href=\"".$post['link']."\"><img src=\"".$post['image']."\"/><p>".$text."</p></a></div>";
}


function getAllOptionsData(){
	$adminOptions = array();

	//get map options
	$adminOptions['rsp-gmAddress'] = get_option('rsp-gmAddress', '111 Wellington Street, Ottawa, ON, K1A 0A4' );
	$adminOptions['rsp-gmMapType'] = get_option('rsp-gmMapType', 'sat');
	$adminOptions['rsp-gmMarkerColour'] = get_option('rsp-gmMarkerColour', 'red');

	//get Facebook options
	$adminOptions['rsp-fbPageLink'] = get_option('rsp-fbPageLink', 'londonlibrary');

	//get Twitter options
	$adminOptions['rsp-twHandle'] = get_option('rsp-twHandle', 'CityOfLdnOnt');
	$adminOptions['rsp-twIncludeRTs'] = get_option('rsp-twIncludeRTs', 1);
	$adminOptions['rsp-twIncludeReplies'] = get_option('rsp-twIncludeReplies', 1);

	return $adminOptions;


}


?>
