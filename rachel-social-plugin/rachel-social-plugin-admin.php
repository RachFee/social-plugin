<?php 
//TODO: Move styles to style sheet

function createMenuSection(){
    add_menu_page('Rachel Social Plugin', 'Rachel Social Plugin Settings', 'manage_options', 'rachel-social-plugin', 'displayOptions');

}
add_action('admin_menu', 'createMenuSection');

//TODO: form-table styles get overwritten by a WP stylesheet. For now, styles are being included in-line. 
//Update styles and replace this at a later time.
/*function addAdminScript() {
    wp_enqueue_style('custom_admin_script', '/wp-content/plugins/rachel-social-plugin/css/adminstyle.css');
}
add_action('admin_enqueue_scripts', 'addAdminScript');*/

/* This function display the Form */
/* Dependencies: allOptionsData() to retrieve the form options */
function displayOptions(){

	if ($_SERVER['REQUEST_METHOD'] == "POST"){ //on save
		$adminOptions = saveOptionsData();
		echo '<div id="message" class="updated fade"><p><strong>Settings Updated!</strong></p></div>';
	}else{ //onload
    	$adminOptions = getAllOptionsData();
    }
	?>
    <div id="updateSocialOptions" class="wrap">
        <h1>Rachel Social Plugin Cusomization</h1>
        <?php 
        	if (isset($_GET['updated'])) {
                echo '<div id="message" class="error fade"><p><strong>Options Sucessfully Updated</strong></p></div>';
            }
        ?>

        <form id="socialOptionsForm" enctype="multipart/form-data" method="POST" action="">  
        
        	<!--Universal Settings-->
        	<!-- Currently None-->

        	<!--Map Settings-->
        	<table class="form-table" frame="box">
                <tr>
                	<th colspan="2" style="text-align:center; background-color:#7fbf7f; padding-top:5px; padding-bottom:5px;"> 
                		<h2>Google Maps</h2>
                	</th>
                </tr>
                <tr>
                    <th style="text-align:right">
                        <label for="rsp-gmAddress">Address</label>
                    </th>
                    <td>
                        <input type="text" name="rsp-gmAddress" id="rsp-gmAddress" class="textField" maxlength="100" size="100" value="<?php echo stripslashes($adminOptions['rsp-gmAddress']); ?>" />
                        <br/>eg. 111 Wellington Street, Ottawa, ON, K1A 0A4
                    </td>
                </tr>
                <!-- TODO: later, add the option for a profile picture upload instead of a map -->
               <!-- <tr> 
                    <th>
                        <label for="image">Image</label>
                    </th>
                    <td>
                        <input type="radio" name="newImage" value="no" id="keepExistingFile" checked ><label for="keepExistingFile">Keep Existing Image</label>
                        <br />
                        <input type="radio" name="newImage" value="yes" id="addNewImageCheck"><label for="addNewImageCheck" >Add New Image</label>
                        <br />
                        <input type="file" name="image" id="image" class="editAddImage" id="addNewImage" />
                        <em>
						  <br/>Small images appear at 241x241px, large images will appear at 482x482px.
						  <br/>Photos must be square.
						</em>
                    </td>
                </tr> -->
				<tr>
                    <th style="text-align:right">
                        <label for="rsp-gmMapType">Map Type</label>
                    </th>
                    <td>
                        <input type="radio" name="rsp-gmMapType" id="rsp-gmMapTypeRoad" class="textField" value="roadmap" <?php if ($adminOptions['rsp-gmMapType'] == "roadmap"){ echo " checked";} ?>/><label for="rsp-gmMapTypeRoad">Roadmap</label><br/>
                        <input type="radio" name="rsp-gmMapType" id="rsp-gmMapTypeSat" class="textField" value="satellite" <?php if ($adminOptions['rsp-gmMapType'] == "satellite"){ echo " checked";} ?>/><label for="rsp-gmMapTypeSat">Satellite</label><br/>
                        <input type="radio" name="rsp-gmMapType" id="rsp-gmMapTypeHybrid" class="textField" value="hybrid" <?php if ($adminOptions['rsp-gmMapType'] == "hybrid"){ echo " checked";} ?>/><label for="grsp-mMapTypeHybrid">Hybrid</label><br/>
                        <input type="radio" name="rsp-gmMapType" id="rsp-gmMapTypeTerrain" class="textField" value="terrain" <?php if ($adminOptions['rsp-gmMapType'] == "terrain"){ echo " checked";} ?>/><label for="rsp-gmMapTypeTerrain">Terrain</label><br/>
                    </td>
                </tr>
				<tr>
                    <th style="text-align:right">
                        <label for="rsp-gmMarkerColour">Marker Colour</label>
                    </th>
                    <td>
                        <input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourRed" class="textField" value="red" <?php if ($adminOptions['rsp-gmMarkerColour'] == "red"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourRed">Red</label><br/>
                        <input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourBlue" class="textField" value="blue" <?php if ($adminOptions['rsp-gmMarkerColour'] == "blue"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourBlue">Blue</label><br/>
                    	<input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourGreen" class="textField" value="green" <?php if ($adminOptions['rsp-gmMarkerColour'] == "green"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourGreen">Green</label><br/>
                        <input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourYellow" class="textField" value="yellow" <?php if ($adminOptions['rsp-gmMarkerColour'] == "yellow"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourYellow">Yellow</label><br/>
                        <input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourOrange" class="textField" value="orange" <?php if ($adminOptions['rsp-gmMarkerColour'] == "orange"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourOrange">Orange</label><br/>
                        <input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourPurple" class="textField" value="purple" <?php if ($adminOptions['rsp-gmMarkerColour'] == "purple"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourPurple">Purple</label><br/>
                        <input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourWhite" class="textField" value="white" <?php if ($adminOptions['rsp-gmMarkerColour'] == "white"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourWhite">White</label><br/>
                        <input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourBlack" class="textField" value="black" <?php if ($adminOptions['rsp-gmMarkerColour'] == "black"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourBlack">Black</label><br/>
                        <input type="radio" name="rsp-gmMarkerColour" id="rsp-gmMarkerColourGrey" class="textField" value="grey" <?php if ($adminOptions['rsp-gmMarkerColour'] == "grey"){ echo " checked";} ?>/><label for="rsp-gmMarkerColourGrey">Grey</label><br/>
                       
                    </td>
                </tr>
            </table>


        	<!--Facebook Settings-->   
            <table class="form-table" frame="box">
                <tr>
                	<th colspan="2" style="text-align:center; background-color:#c4c4ff; padding-top:5px; padding-bottom:5px;"> 
                		<h2>Facebook</h2>
                	</th>
                </tr>
                <tr>
                    <th style="text-align:right">
                        <label for="rsp-fbPageLink">Facebook Page Link</label>
                    </th>
                    <td>
                        http://www.facebook.com/<input type="text" name="rsp-fbPageLink" id="rsp-fbPageLink" class="textField" value="<?php echo stripslashes($adminOptions['rsp-fbPageLink']); ?>" />
                        eg. londonlibrary
                        <br/><em>*Please note that Facebook only supports Page Feeds.
                    </td>
                </tr>
            </table>

            <!--Twitter Settings-->
            <table class="form-table" frame="box">
                 <tr>
                	<th colspan="2" style="text-align:center; background-color:#55ffff; padding-top:5px; padding-bottom:5px;"> 
                		<h2>Twitter</h2>
                	</th>
                </tr>
                <tr>
                    <th style="text-align:right">
                        <label for="rsp-twHandle">Twitter Handle</label>
                    </th>
                    <td>
                         http://www.twitter.com/<input type="text" name="rsp-twHandle" id="rsp-twHandle" class="textField" value="<?php echo stripslashes($adminOptions['rsp-twHandle']); ?>" />
                        eg. CityOfLdnOnt
                    </td>
                </tr>
				<tr>
                    <th style="text-align:right">
                        <label for="rsp-twIncludeRTs">Include Retweets?</label>
                    </th>
                    <td>
                        <input type="radio" name="rsp-twIncludeRTs" id="rsp-twIncludeRTs1" class="textField" value="1" <?php if ($adminOptions['rsp-twIncludeRTs'] == "1"){ echo " checked";} ?>/><label for="rsp-twIncludeRTs1">Yes</label><br/>
                        <input type="radio" name="rsp-twIncludeRTs" id="rsp-twIncludeRTs0" class="textField" value="0" <?php if ($adminOptions['rsp-twIncludeRTs'] == "0"){ echo " checked";} ?>/><label for="rsp-twIncludeRTs0">No</label><br/>
                    </td>
                </tr>
             	<tr>
                    <th style="text-align:right">
                        <label for="rsp-twIncludeReplies">Include Replies?</label>
                    </th>
                    <td>
                        <input type="radio" name="rsp-twIncludeReplies" id="rsp-twIncludeReplies1" class="textField" value="1" <?php if ($adminOptions['rsp-twIncludeReplies'] == "1"){ echo " checked";} ?>/><label for="rsp-twIncludeReplies1">Yes</label><br/>
                        <input type="radio" name="rsp-twIncludeReplies" id="rsp-twIncludeReplies0" class="textField" value="0" <?php if ($adminOptions['rsp-twIncludeReplies'] == "0"){ echo " checked";} ?>/><label for="rsp-twIncludeReplies0">No</label><br/>
                    </td>
                </tr>

 
 
            </table>

            <!--save button-->
            <input type="submit" id="update-options" class="button" value="Update Options" style="margin-top:10px" />

        </form>
    </div>

<?php
}

/*Retrieve the settings from the DB and return them in an array*/
/*Moved to main plugin file as it is used in both places*/
/*function getAllOptionsData(){
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
}*/

/*This function saves the settings from the DB and returns them in an array for display (so we don't have to save then retrieve) */
function saveOptionsData(){
	$adminOptions = array();

	//save map options
	update_option('rsp-gmAddress', $_POST['rsp-gmAddress'] );
	update_option('rsp-gmMapType', $_POST['rsp-gmMapType']);
	update_option('rsp-gmMarkerColour', $_POST['rsp-gmMarkerColour']);
	//save Facebook options - test before saving
	if(empty(getFacebook($_POST['rsp-fbPageLink']))){
        echo '<div id="message" class="updated fade"><p><strong>Facebook page does not exist and was not updated.</strong></p></div>';
    
    }else{
        update_option('rsp-fbPageLink', $_POST['rsp-fbPageLink']);  
    }
	//save Twitter options
	update_option('rsp-twHandle', $_POST['rsp-twHandle']);
	update_option('rsp-twIncludeRTs', $_POST['rsp-twIncludeRTs']);
	update_option('rsp-twIncludeReplies', $_POST['rsp-twIncludeReplies']);


	//add to array
	//add map vars
	$adminOptions['rsp-gmAddress'] = $_POST['rsp-gmAddress'];
	$adminOptions['rsp-gmMapType'] = $_POST['rsp-gmMapType'];
	$adminOptions['rsp-gmMarkerColour'] = $_POST['rsp-gmMarkerColour'];
	//add facebook vars - get because we're not sure if it was updated
	//$adminOptions['rsp-fbPageLink'] = $_POST['rsp-fbPageLink'];
    $adminOptions['rsp-fbPageLink'] = get_option('rsp-fbPageLink', 'londonlibrary');
	//add twitter vars
	$adminOptions['rsp-twHandle'] = $_POST['rsp-twHandle'];
	$adminOptions['rsp-twIncludeRTs'] = $_POST['rsp-twIncludeRTs'];
	$adminOptions['rsp-twIncludeReplies'] = $_POST['rsp-twIncludeReplies'];

	return $adminOptions;


}
