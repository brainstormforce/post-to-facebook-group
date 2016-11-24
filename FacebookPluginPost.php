<?php session_start();
/*
Plugin Name: FacebookPluginPost
Plugin URI: http://www.example.com
Description: Add Account to Facebook Group And post data in group
Author: Uttam Sharma
Version: 1.0
Author URI: http://www.example.com
*/
?>
<?php
require_once __DIR__ . '/lib/Facebook/autoload.php';
	use Facebook\FacebookSession;
	use Facebook\FacebookRedirectLoginHelper;
	use Facebook\FacebookRequest;
	use Facebook\FacebookResponse;
	use Facebook\FacebookSDKException;
	use Facebook\FacebookRequestException;
	use Facebook\FacebookAuthorizationException;
	use Facebook\GraphObject;
	use Facebook\GraphUser;
	use Facebook\GraphSessionInfo;
	use Facebook\FacebookHttpable;
	use Facebook\FacebookCurlHttpClient;
	use Facebook\FacebookCurl;
//Adding plugin on dashboard
add_action('admin_menu', 'FacebookPlugin_admin_actions');
function FacebookPlugin_admin_actions() {
	add_menu_page('FacebookPluginPost', 
				'FacebookPluginPost', 
				'administrator', 
				'FacebookPluginPost', 
				'FacebookPluginPost_plugin_settings',
				'dashicons-facebook-alt',
				'90'
				 );
add_action( 'admin_init', 'register_my_cool_plugin_settings' );
}
//include css file
function xyz_fbp_add_admin_scripts()
{
	wp_register_style('xyz_fbp_style', plugins_url('FacebookPluginPost/Assets/css/style.css'));
	wp_enqueue_style('xyz_fbp_style');
}
add_action("init","xyz_fbp_add_admin_scripts");
//register our settings
function register_my_cool_plugin_settings() {
	register_setting( 'my-cool-plugin-settings-group', 'fpp_app_id' );
	register_setting( 'my-cool-plugin-settings-group', 'fpp_app_secret' );
	register_setting( 'my-cool-plugin-settings-group', 'fpp_group_id' );
}
//Backend design admin area
function FacebookPluginPost_plugin_settings(){
?>
<div class="container2">
<img src="http://localhost/projects/wp-content/plugins/FacebookPluginPost/Assets/images/fpp-icon.png" class="iconDetails"><h1>Facebook Plugin Post</h1></div>
<div class="wrap">
<!--Include Shortcode table-->
<h1>Facebook Plugin Post</h1>
<h4>Shortcode for Facebook Plugin Post</h4>
		<table class="widefat">
			<thead>
				<tr>
					<th>Title</th>
					<th>ShortCode</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Title</th>
					<th>ShortCode</th>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>Facebook Plugin Post Shortcode</td>
					<td>[Facebook-Plugin-Post-1]</td>
				</tr>
			</tbody>
		</table>
<!--Application Settings Form-->
<h4>Application Settings</h4>
<form method="post" action="options.php" class="container">
    <?php settings_fields( 'my-cool-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'my-cool-plugin-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        	<th scope="row">Application ID</th>
        	<td><input type="text" name="fpp_app_id" id="fpp_app_id" required />
        	&nbsp;
        	<a href="https://developers.facebook.com/docs/apps/register" target="_blank">For create Facebook app, Click here</a>
        	</td>
        </tr>   
        <tr valign="top">
        	<th scope="row">Application Secret</th>
        	<td><input type="text" name="fpp_app_secret" id="fpp_app_secret" required />
        	</td>
        </tr>
        <tr valign="top">
        	<th scope="row">Group ID</th>
        	<td><input type="text" name="fpp_group_id" required/>&nbsp;
        	<a href="https://www.slickremix.com/how-to-get-your-facebook-group-id/" target="_blank">Find your Facebook Group Id, Click here</a>
        	</td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
</div>
<?php
}
function FacebookPluginPost_testimonial_form() {

	if(!empty (get_option('fpp_app_id') ) )
	{
		$app_id = esc_attr( get_option('fpp_app_id') );
	}
	if(!empty (get_option('fpp_app_secret') ) )
	{
		$app_secret = esc_attr( get_option('fpp_app_secret') );
	}
	$redirect_url = get_permalink();
	//Initialize application, create helper object and get fb sess
	 $loggedin = false;
	 FacebookSession::setDefaultApplication($app_id,$app_secret);
	 $helper = new FacebookRedirectLoginHelper($redirect_url);
	 $sess = $helper->getSessionFromRedirect();
	if(isset($_SESSION['fb_token']) && !isset($_GET['logout'])){
		
		$sess = new FacebookSession($_SESSION['fb_token']);
		try{
			$sess->Validate($app_id,$app_secret);
		}catch(FacebookAuthorizationException $e){
			print_r($e);
		}
	}
	if($_GET['logout'])
	    	{
	    		if(isset($_SESSION['fb_token']))
	    		{
	    			unset($sess); 
	    		unset($_SESSION['fb_token']);
	    		}	
	    	}
	$loggedin = false;
	//get email as well with user permission
	$login_url = $helper->getLoginUrl(array('scope' => 'email','publish_actions','user_managed_groups'));
	// if fb sess exists echo name 
	 	if(isset($sess)){
	 		//store the token in the php session
	 		$_SESSION['fb_token'] = $sess->getToken();
	 		//create request object,execute and capture response
	 		$request = new FacebookRequest($sess,'GET','/me?fields=id,name,email');
			// from response get graph object
			$response = $request->execute();
			$graph = $response->getGraphObject(GraphUser::classname());
			// use graph object methods to get user details
			$id = $graph->getId();
			$name= $graph->getName();
			$email = $graph->getProperty('email');
			$image = 'https://graph.facebook.com/'.$id.'/picture?width=300';
			$loggedin  = true;
			$headers = 'From: Uttam sharma <uttams@bsf.io>';
			mail( $email, 'Successfully Login', 'Thank you for login.', $headers );
	}
?>
		<?php if(!$loggedin){?>
	    <p>Welcome, Please login to your facebook account.</p>
	    <a href="<?php echo $login_url; ?>"><button class="btn btn-primary">Login with facebook</button>
	    </a>
    	<?php }else {
				$data = array($id);
				add_option('facebook_users', NULL);
				$storedata =  get_option( 'facebook_users' );
				if(in_array($id, $storedata))
				{
					echo 'already exits';
				}
				else {
					$final = array_merge((array)$storedata,(array)$data);
				    update_option('facebook_users', $final);
				    echo 'successfully registration!!';
				}
			//code for post content on facebook page
			if(isset($_POST['fb_submit']))
			{
			$group_id = get_option('fpp_group_id');
			$content = $_POST['content'];
			$postRequest = new FacebookRequest($sess, 'POST', '/'.$group_id.'/feed', array('message' => $content));
			$postResponse = $postRequest->execute();
			$posting = $postResponse->getGraphObject();
			if($posting->getProperty('id'))
			{
				echo '<span class="info">Post successfully!</span>';
			}
			else {
				echo "error occur!!";
			}
		}
		$appSession = FacebookSession::newAppSession();
		if(isset($_POST['test_submit']))
			{
				$request = new FacebookRequest(
				  $sess,
				  'POST',
				  '/'.$app_id.'/roles',
				  array (
				    'user' => $id,
				    'role' => 'administrators',
				  )
				);
				$response = $request->execute();
				$graphObject = $response->getGraphObject();
				print_r($graphObject);
			}$location = "C:/xampp/htdocs/Projects/wp-content/plugins/FacebookPluginPost/logout.php";
		?>
		<form action="#" method="POST">
			<label>Your Message</label>
			<textarea name="content"></textarea>
			<input type="submit" name="fb_submit" value="Post on Facebook">
		</form>
		<form action="#" method="POST">
			<input type="submit" name="test_submit" value="test">
		</form>
    	<h1>Successfully login!</h1>
    	<img src="<?php echo $image; ?>" alt="<?php echo $name; ?>" class="img-thumbnail">
	    <h1>hi <b><?php echo $name; ?></b></h1>
	    <p>you have successfully logged in via facebook :) and your email is <br/>
	    	<code><?php echo $email ; ?></code>
	    </p><br>
	    	<?php $logout = get_permalink(); ?>
	    	<a href='<?php $logout .= '?logout=1'; 
			$log =  $helper->getLogoutUrl($sess,$logout);   echo $log; ?>'>
	    	<button class="btn btn-primary">Logout</button>
	    	</a>		    
	    	<?php
			}
		}
add_shortcode('Facebook-Plugin-Post-1', 'FacebookPluginPost_testimonial_form');
?>