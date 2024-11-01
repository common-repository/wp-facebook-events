<div class="wrap">
	<h2>Facebook Events Management</h2>
	<form method="POST" action="options.php">
		<?php
			if ( function_exists( 'settings_field' ) ) {
				settings_field( 'facebookevents-options' );
			} else {
				wp_nonce_field( 'update-options' );
		?>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="facebookevents-appid,facebookevents-appsecret" />
		<?php
			}
		?>
    <br />
    <a href="#appsettings" onclick="jQuery('#fbevents-settings').css('display', 'block'); return false;">App Settings</a>
    <div id="fbevents-settings" style="<?php if ( !$app_id || !$app_secret ) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
	<table width="50%">
		<tr>
			<td>
			    App ID
			</td>
                        <td><input type="text" id="appid" value="<?php echo $app_id; ?>" name="facebookevents-appid" /></td>
		</tr>
		<tr>
			<td>
                            App Secret
			</td>
                        <td><input type="text" id="appsecret" value="<?php echo $app_secret; ?>" name="facebookevents-appsecret" /></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align: center">
		<input type="submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>" />
		<input type="button" class="" value="<?php _e( 'Cancel' ); ?>" onclick="jQuery('#fbevents-settings').css('display','none'); return false;" />
			</td>
		</tr>
	</table>
	</form>
    </div>
    <?php if ( $session ) { ?>
    <div id="eventcreation">
        <h3>Create Event</h3>
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
		<input type="button" class="button-primary" value="<?php _e( 'Create Event' ); ?>" onclick="CreateFBEvent(0); return false;"/>
			</td>
		</tr>
	</table>
	</form>
    </div>
    <div id="eventslist">
        <table width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Shortcode</th>
                    <th>Facebook Link</th>
                </tr>
            </thead>
            <tbody id="eventlistbody">
            <?php $this->eventstable(); ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>
