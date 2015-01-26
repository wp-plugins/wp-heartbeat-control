<?php 
/*
  Plugin Name: WP Heartbeat Control
  Plugin URI: http://www.mindstien.com/
  Version: 1.1
  Author: Mindstien Technologies
  Author URI: http://www.mindstien.com/
  Description: To control Wordpress Heartbeat API to reduce load on CPU. Useful for heavy traffic site on shared or poor hosting platform and for who is facing CPU Usage issues from their host.
  Text Domain: wp-heartbeat-control
*/

add_action('admin_menu', 'wphc_insert_menu');
add_action('init', 'wphc_process_options');

function wphc_insert_menu()
{
	add_options_page( "WP Heartbeat Control", "WP Heartbeat Control", "manage_options", "wp_heartbeat_control", "wp_heartbeat_control_func");
}	


function wphc_process_options()
{
	global $wphc_options;
	$wphc_options = get_option("wphc_options");
	//echo "<pre>".print_r($wphc_options,true)."</pre>";
	if(!is_array($wphc_options))
	{
		
		$wphc_options = array(
						"wphc_radio"=>"none",
						"wphc_seconds"=>60,
						"wphc_roles"=>array("all")
					);
	}
	if(isset($_POST['wphc_secret']) AND $_POST['wphc_secret'] == "B7pTeIvctilzQYp6zQQi")
	{
		
		$wphc_options = array(
						"wphc_radio"=>$_POST['wphc_radio'],
						"wphc_seconds"=>intval($_POST['wphc_seconds']),
						"wphc_roles"=>$_POST['wphc_roles']
					);
		update_option("wphc_options",$wphc_options);
		
		echo "<div class='updated'><p>Options saved!</p></div>";
	}
}


function wp_heartbeat_control_func()
{
	global $wphc_options;
	extract($wphc_options);
	
	switch($wphc_radio)
		{
			case "none":
				$api_status = "Active Everywhere!";
				break;
			case "all":
				$api_status = "Disabled Everywhere!";
				break;
			case "post":
				$api_status = "Disable everywhere except for post editor";
				break;
			case "dashboard":
				$api_status = "Disable for WP Dashboard Only";
				break;
			case "intval":
				$api_status = "Running with ".$wphc_seconds." Seconds interval";
				break;
		}
		
		echo "<div class='updated'><p>WP  Heartbeat API is : <strong>$api_status</strong></p></div>";
		
	$wphc_active = wphc_is_activated();
	?>
		<div class='wrap'>
			<h2>WP Heartbeat Control</h2>
			<form action="" method="POST">
				<input type='hidden' name='wphc_secret' value='B7pTeIvctilzQYp6zQQi' />
				<p class='<?php echo ('none' == $wphc_radio)?"checked":""; ?>'>
					<input type='radio' name='wphc_radio' <?php checked('none',$wphc_radio); ?> id='none' value='none' />
					<label for="none">Enable everywhere (Plugin OFF!)</label>
				</p>
				<p class='<?php echo ('all' == $wphc_radio)?"checked":""; ?>'>
					<input type='radio' name='wphc_radio' <?php checked('all',$wphc_radio); ?> id='all' value='all' />
					<label for="all">Disable everywhere</label>
				</p>
				<p class='<?php echo ('dashboard' == $wphc_radio)?"checked":""; ?>'>
					<input type='radio' name='wphc_radio' id='dashboard' value='dashboard' <?php checked('dashboard',$wphc_radio); ?> />
					<label for="dashboard">Disable for WP Dashboard Only</label>
				</p>
				<p class='<?php echo ('post' == $wphc_radio)?"checked":""; ?>'>
					<input <?php echo (!$wphc_active)?"disabled='disabled'":""; ?> type='radio' name='wphc_radio' id='post' value='post' <?php checked('post',$wphc_radio); ?> />
					<label for="post">Disable everywhere except for post editor</label> <?php echo (!$wphc_active)?"<a href='#wphc_activation_anchor'>Unlock (it's Free!)</a>":""; ?> 
				</p>
				<p class='<?php echo ('intval' == $wphc_radio)?"checked":""; ?>'>
					<input <?php echo (!$wphc_active)?"disabled='disabled'":""; ?> type='radio' name='wphc_radio' id='intval' value='intval' <?php checked('intval',$wphc_radio); ?> />
					<label for="intval">Change Heartbeat Interval to <input <?php echo (!$wphc_active)?"disabled='disabled'":""; ?>  type='number' name='wphc_seconds' value='<?php echo $wphc_seconds; ?>' style='width:50px;' /> Seconds (Default is 15 Seconds)</label> <?php echo (!$wphc_active)?"<a href='#wphc_activation_anchor'>Unlock (it's Free!)</a>":""; ?> 
				</p>
				<p>
					<label for="users">Apply above settings to following user roles only. (Use Cntrl+Click to select multiple user roles)</label><br>
					<select multiple name='wphc_roles[]' style="height: 180px;" >
						<option <?php if(in_array('all',$wphc_roles))
								echo "selected='selected'";
							 ?> value='all'>All Users</a>
					<?php 
						global $wp_roles;
						$roles = $wp_roles->get_names();
						foreach($roles as $k=>$v)
						{
							echo "<option value='$k' ";
							if(in_array($k,$wphc_roles))
								echo "selected='selected'";
							echo ">$v</option>";
						}
					?>
					</select>
				</p>
				<?php /*
				<p>
					<label for='license'>Unlock Code:</label>
					<input type='text' name='license' value='<?php echo $license; ?>' />
					<?php
						if($wphc_active)
							echo "<span class='description' style='color:green;'>Plugin activated!</span>";
						elseif($license == "")
							echo "<span class='description'>Please enter valid activation code</span>";
						else
							echo "<span class='description' style='color:red;'>Invalid activation code.</span>";
					?>
				</p> */ ?>
				
				<p>
					<input type='submit' name='wphc_submit' value='Save Options' class='button-primary' />
				</p>
			</form>
				
			<h3>What is WordPress Heartbeat API?</h3>
			<p>The Heartbeat API, included in the release of WordPress 3.6, provides fluid updates between your browser and the server. This allows for autosaving, post locking, login expiration warnings, and much more. The API works in the background and involves routine communication between the browser and the server that is repeated at regular intervals to ensure they are completely in sync with one another.</p>
			<p>However, constantly sending information in such a manner can cause high CPU usage on the server. In fact, even if you were to simply login to your WordPress Dashboard and minimize your browser while you worked on other tasks, the API would still be updating at regular intervals to maintain the features it provides. If you have experienced high CPU loads using the aforementioned WordPress version, you can follow the steps below to disable the WordPress Heartbeat API in different areas of your site.</p>
			
			<h3 id='wphc_activation_anchor'>Who should use this plugin</h3>
			<p>Although this is the great wordpress feature, but some time the performance of site is more concerned than this feature. You should only use this plugin If you are going through <b>HIGH CPU USAGE</b> issues, if your host has sent you an alert/warning about <b>HIGH CPU USAGE</b>, or you can also use this just to improve performance when you have hight traffic website.</p> 
			
			<?php if(!$wphc_active) { ?>
			<div id='wphc_activation'>
				<h2>Signup to unlock (it's Free!)</h2>
				<p>&nbsp;</p>
				<form action='http://mindstien.com/pro-plugins/activation.php' method='post' target='_blank'>
					<input type='hidden' name='verification' value='gLpu%A1C738le1zY@3yQ'>
					<input type='hidden' name='plugin' value='WP Heartbeat Control'>
					<p><label for='name'>Name:</label><input type='text' name='name' id='name' /></p>
					<p><label for='email'>Email:</label><input type='text' name='email' id='email' /></p>
					<p><input type='submit' name='submit' value='Unlock Plugin' class='button-primary' ></p>
				</form>
			</div>
			<?php } ?>
		</div>
		<style>
			.checked > label {
				font-weight: bold;
			}
			#wphc_activation {
				background: none repeat scroll 0 0 white;
				border: 1px solid grey;
				border-radius: 20px;
				box-shadow: 2px 2px 5px gray;
				padding: 10px;
				text-align: center;
				width: 300px;
				margin-bottom:800px;
			}
			#wphc_activation > h2 {
				background: none repeat scroll 0 0 brown;
				border-radius: 20px 20px 0 0;
				color: white;
				font-weight: bold;
				margin: -10px;
				padding: 5px;
			}
			#wphc_activation label {
				font-size: large;
				padding: 10px;
				vertical-align: top;
			}
			#wphc_activation .button-primary {
				font-size: large;
				font-weight: bold;
				height: 40px;
				line-height: 40px;
			}
		</style>
	<?php
	
}





//wphc in action
add_filter( 'heartbeat_send', 'wphc_heartbeat_interval' );

function wphc_heartbeat_interval( $response ) 
{
	global $wphc_options;	
	$wphc_options = get_option("wphc_options");
	
	if(!is_array($wphc_options))
		return $response;
		
	extract($wphc_options);
		
	if ( $_POST['interval'] != $wphc_seconds AND $wphc_radio == 'intval' AND wphc_is_role_allowed() AND wphc_is_activated() ) {
			$response['heartbeat_interval'] = $wphc_seconds;
	}
	return $response;
}

    
add_action( 'init', 'wphc_stop_heartbeat', 1 ); 
function wphc_stop_heartbeat() 
{ 
	global $pagenow,$wphc_options; 
	$wphc_options = get_option("wphc_options");
	if ( $wphc_options['wphc_radio']=="post" AND $pagenow != 'post.php' AND $pagenow != 'post-new.php' AND wphc_is_role_allowed() AND wphc_is_activated() )
	{
		wp_deregister_script('heartbeat'); 
	}
	elseif($wphc_options['wphc_radio']=="dashboard" AND wphc_is_role_allowed() AND is_admin() )
	{
		wp_deregister_script('heartbeat'); 
	}
	elseif($wphc_options['wphc_radio']=="all" AND wphc_is_role_allowed() )
	{
		wp_deregister_script('heartbeat'); 
	}
	
}


function wphc_is_role_allowed()
{
	global $wphc_options,$current_user;	
	$wphc_options = get_option("wphc_options");
	
	$temp = array_intersect($current_user->roles,$wphc_options['wphc_roles']);
	
	if(is_array($temp) AND count($temp)>0 )
		return true;
	elseif(in_array("all",$wphc_options['wphc_roles']))
		return true;
	return false;
	
}

function wphc_is_activated()
{
	/*
	global $wphc_options;
	if(isset($wphc_options['license']) AND strlen($wphc_options['license'])==53)
	{
		$seeds = array("1Exrk^","6gmOhY","20MZjHW2","8YV0","9w","0#!g","4YQn%i9","3vq","5%vJWF","7f&IVGo");
		$temp = array();
		foreach ($seeds as $k=>$v)
		{
			$temp[substr($v,0,1)] = $v;
		}
		ksort($temp);
		if( trim(implode("",$temp)) == $wphc_options['license'] )
			return true;
		return false;
	}
	return false;
	*/
	return true;
}