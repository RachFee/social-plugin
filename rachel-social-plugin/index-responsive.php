<?php 
/*
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

//get specifications from settings
	//get twitter settings
	//get Facebook settings
	//get map settings
	//get order (by time/random);
	//set map size?
	//set maptype -roadmap, satellite, hybrid, and terrain.
	//markercolor: {black, brown, green, purple, yellow, blue, gray, orange, red, white}.
	

//create an array to hold all Twitter and Facebook posts
$allPosts = array();
// type, text, timestamp, link

//call social media
//print_r(getTwitter("CityOfLdnOnt", false));
//keep separate now for sorting
$tweets = getTwitter('CityOfLdnOnt', true);
$fbposts = getFacebook("londonlibrary");
$map = getMap("1600 Amphitheatre Pkwy, Mountain View, CA 94043, United States");
//marge into one array
$allPosts = array_merge($tweets, $fbposts);

//TODO: Add sorting - do this in function?
shuffle($allPosts);

//displayGrid will handle output
displayGrid($allPosts);



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
		 //unix timestamp for easy sorting and formatting

		 //new tweet
		 $newTweet = array(
		 	"type" => "twitter",
		 	"text" => $tweetData->text, 
		 	"timestamp" => $tweetData->created_at, 
		 	"link" => "http://twitter.com" 
		 ); //TODO: fix link

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

	foreach ($feed['data'] AS $postNum => $postValue){
 		//unix timestamp for easy sorting and formatting


		//new tweet
		$newPost = array(
		 	"type" => "facebook",
		 	"text" => $postValue['message'], 
		 	"timestamp" => $postValue['created_time'], 
		 	"link" => "http://facebook.com" 
		); //TODO: fix link

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
function getMap($address){
	
	//static maps API key
	$key = "AIzaSyAdIcd8IhJiRMl7SKAeIwvFQIQr2OAnRIU";
	//get map, use encoded address
	//size 1000x1000 so we can adjust later
	//let's build this separately so we can adjust settings as we go
	//TODO: Put this in try-catch
	$mapString = "https://maps.googleapis.com/maps/api/staticmap?center=".urlencode($address);
	$mapString .= "&maptype=satellite"; //type
	$mapString .= "&size=1000x1000"; //size
	$mapString .= "&markers=size:mid|color:blue|".urlencode($address);
	$mapString .= "&key=".$key; //key is required.

	return $mapString;
}

function displayGrid(){
?>
	<div> <!-- full width grid to hold everything. 1200x1200? -->
		<!--top row-->
		<ul class="grid-row">
			<li></li> 
			<li></li>
			<li></li>
			<li></li>
		</ul>
		<!--left column-->
		<ul class="grid-col">
			<li></li>
			<li></li>
		</ul>	
		<!--center block-->
		<ul class="center-block">
			<li></li>
		</ul>
		<!--right column-->
		<ul class="grid-col">
			<li></li>
			<li></li>
		</ul>

		<!--bottom row
		<ul class="grid-row">
			<li></li> 
			<li></li>
			<li></li>
			<li></li>
		</ul>-->
	</div>
<?
}


?> 
<style>
 	/*div*/
	div{
		width: 1200px;
		height: 1200px; 
		padding:0px;
		margin:0px;
	}
	/*general ul & li*/
	ul{
		padding:0px;
		margin:0px;
	}
	li{
		list-style-type: none;
		width:25%;
		height:25%;
		min-width:100px;
		max-width:300px;
		padding:0px;
		margin:0px;
		display:inline-block;
	}

	/*grid-row*/
	ul.grid-row{
		width:100%;
		min-width:400px;
		max-width:1200px;
		padding:0px;
		margin:0px;
	}
	ul.grid-row li{
		list-style-type: none;
		float:left;
		display:inline-block;
		background-color:red;

	}

	/*grid-col*/
	ul.grid-col{
		width:25%;
		min-width:100px;
		max-width:300px;
		padding:0px;
		margin:0px;
		display:inline-block;

	}

	ul.grid-col li{
		list-style-type: none;
		width:100%;
		min-width:100px;
		max-width:300px;
		display:inline-block;
				background-color:blue;

	}

	/*center-block*/
	ul.center-block{
		width:50%;
		min-width:200px;
		max-width:600px;
		padding:0px;
		margin:0px;
		height:600px;
		display:inline-block;
	}
	/*
	ul.center-block{
        width:50%;
        min-width:200px;
        max-width:600px;
        padding:0px;
        margin:0px;
        min-height:600px;
		max-height:600px;
        border:1px dotted blue;
        display:inline-block;
    }*/
	ul.center-block li{
		width:100%;
		height:100%;
		min-width:200px;
		max-width:600px;
		padding:0px;
		margin:0px;
		display:inline-block;
		background-color:green;

	}


</style>
