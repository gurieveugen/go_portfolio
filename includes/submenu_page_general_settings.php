<?php
/**
 * Submenu page for in admin area
 * General Settings Page
 *
 * @package   Go - Portfolio
 * @author    Granth <granthweb@gmail.com>
 * @link      http://granthweb.com
 * @copyright 2013 Granth
 */

$screen = get_current_screen();

/* Get general settings db data */
$general_settings = get_option( self::$plugin_prefix . '_general_settings' );

/* Get cpts db data */
$custom_post_types = get_option( self::$plugin_prefix . '_cpts' );
if ( isset ( $custom_post_types ) && !empty( $custom_post_types ) ) {
	foreach ( $custom_post_types as $cpt_key => $custom_post_type ) {
		$portfolio_cpts[$cpt_key] = $custom_post_type['slug'];
	}
}

/* Handle post */
if ( !empty( $_POST ) && check_admin_referer( $this->plugin_slug . basename( __FILE__ ), $this->plugin_slug . '-nonce' ) ) {

	$reponse = array();
	$referrer=$_POST['_wp_http_referer'];
	
	/* Clean post fields */
	$_POST = go_portfolio_clean_input( $_POST, array(),
		array(
			'go-portfolio-nonce',
			'_wp_http_referer',
		)
	);

	$new_general_settings = $_POST;
			
	/* Save data to db */
	if ( !isset( $response['result'] ) || $response['result'] != 'error' ) {
		if ( $general_settings != $new_general_settings ) { 
			update_option ( self::$plugin_prefix . '_general_settings', $new_general_settings );
		}
		self::generate_styles();		

		/* Set the reponse message */
		$response['result'] = 'success';
		$response['message'][] = __( 'General settings has been successfully updated.', 'go_portfolio_textdomain' );
		set_transient( md5($screen->id . '-response' ), $response, 30 );
	}
	
	/* Redirect */
	wp_redirect( admin_url( 'admin.php?page=' . $_GET['page'] . '&updated=true' ) );
	exit;
}

/**
 *
 * Content
 *
 */

?>
<div id="go-portfolio-admin-wrap" class="wrap">
	<div id="go-portfolio-admin-icon" class="icon32"></div>
    <h2><?php _e( 'General Settings', 'go_portfolio_textdomain' ); ?></h2>	
	<p></p>
	<?php

	/* Print message */
	if ( isset( $_GET['updated'] ) && $_GET['updated'] == 'true' && $response = get_transient( md5( $screen->id . '-response' ) ) ) : 
	?>
	<div id="result" class="<?php echo $response['result'] == 'error' ? 'error' : 'updated'; ?>">
	<?php foreach ( $response['message'] as $error_msg ) : ?>
		<p><strong><?php echo $error_msg; ?></strong></p>
	<?php endforeach;  $response = array(); ?>
	</div>
	<?php 	
	delete_transient( md5( $screen->id . '-response' ) );
	endif;
	/* /Print message */

	?>

	<!-- form -->
	<form id="go-portfolio-settings-form" name="go-portfolio-settings-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>&noheader=true">
		<?php wp_nonce_field( $this->plugin_slug . basename( __FILE__ ), $this->plugin_slug . '-nonce' ); ?>

		<!-- postbox -->
		<div class="postbox">
			<h3 class="hndle"><?php _e( 'Enable Post Types', 'go_portfolio_textdomain' ); ?><span class="gwwpa-toggle"></span></h3>
			<div class="inside">
				<table class="form-table">
					<?php
					$args = array(
					   'public'   => true,
					   '_builtin' => true,
					   'capability_type' => 'post'   
					);
								
					$output = 'objects';
					$operator = 'and';
					$post_types = get_post_types( $args, $output, $operator ); 
					if ( !empty( $post_types ) ) {
						foreach ( $post_types  as $post_type_key => $post_type ) {
							if ( !post_type_supports( $post_type_key, 'thumbnail' ) ) {
								unset( $post_types[$post_type_key] );
							}
						}
					}
					if ( !empty( $post_types ) ) :
					?>
					<tr>
						<th class="gw-gopf-w150"><strong><?php _e( 'Built-in post types', 'go_portfolio_textdomain' ); ?></strong></th>
						<td class="gw-gopf-w300">
						<?php foreach ( $post_types  as $post_type_key => $post_type ) : ?>
							<label><input type="checkbox" name="enable_post_type[<?php echo $post_type_key; ?>]" value="<?php echo $post_type_key; ?>"<?php echo isset( $general_settings['enable_post_type'][$post_type_key] ) && $general_settings['enable_post_type'][$post_type_key] == $post_type_key ? ' checked="checked"' : ''; ?> /> <?php echo $post_type->labels->name; ?></label><br>
						<?php endforeach; ?>
						</td>
						<td>
							<p class="description"><?php _e( 'Select the Wordpress built-in post types to use in the plugin.', 'go_portfolio_textdomain' ); ?></p>
						</td>
					</tr>
					<?php endif; ?>

					<?php
					$args = array(
					   'public'   => true,
					   '_builtin' => false,  
					);
								
					$output = 'objects';
					$operator = 'and';
					$post_types = get_post_types( $args, $output, $operator ); 
					if ( !empty( $post_types ) ) {
						foreach ( $post_types  as $post_type_key => $post_type ) {
							if ( !post_type_supports( $post_type_key, 'thumbnail' ) ) {
								unset($post_types[$post_type_key]);
							}
							if ( in_array( $post_type_key, $portfolio_cpts ) ) {
								unset($post_types[$post_type_key]);
							}
						}
					}
					if ( !empty( $post_types ) ) :
					?>										
					<tr>
						<th class="gw-gopf-w150"><strong><?php _e( 'Custom post types', 'go_portfolio_textdomain' ); ?></strong></th>
						<td class="gw-gopf-w300">
						<?php foreach ( $post_types  as $post_type_key => $post_type ) : ?>
							<label><input type="checkbox" name="enable_post_type[<?php echo $post_type_key; ?>]" value="<?php echo $post_type_key; ?>"<?php echo isset( $general_settings['enable_post_type'][$post_type_key] ) && $general_settings['enable_post_type'][$post_type_key] == $post_type_key ? ' checked="checked"' : ''; ?> /> <?php echo $post_type->labels->name; ?></label><br>
						<?php endforeach; ?>
						</td>
						<td>
							<p class="description"><?php _e( 'Select the custom post types to use in the plugin.', 'go_portfolio_textdomain' ); ?></p>
							<p class="description"><?php _e( 'Enabling means adding meta boxes to post for extra features (video, audio, thumbnail).', 'go_portfolio_textdomain' ); ?></p>
							<p class="description"><?php _e( '<strong>Important:</strong> Custom post types defined by the plugin not listed here.', 'go_portfolio_textdomain' ); ?></p>
						</td>	
					</tr>
					<?php endif; ?>					                                               
				</table>				
				<div class="gw-go-portfolio-separator"></div>
				<table class="form-table">
					<tr>
						<th></th>
						<td colspan="2">
							<p class="description"><?php _e( '<strong>Important:</strong> You can use the plugin with any built-in post types and other (plugin or theme defined) custom post types.', 'go_portfolio_textdomain' ); ?>
							<p class="description"><?php _e( 'Enabling means adding "Go Portfolio Options" meta box to the selected post type posts for the extra features (e.g. video thumbnail). Post types can be used to create a portfolio without enabling them, but the features are limited.', 'go_portfolio_textdomain' ); ?></p>
						</td>
											
					</tr>                                               
				</table>						
			</div>
		</div> 
		<!-- /postbox --> 

		<!-- postbox -->
		<div class="postbox">
			<h3 class="hndle"><?php _e( 'General Settings', 'go_portfolio_textdomain' ); ?><span class="gwwpa-toggle"></span></h3>
			<div class="inside">
                    	<table class="form-table">
                            <tr>
                                <th class="gw-gopf-w150"><label for="go-portfolio-primary-font"><strong><?php _e( 'Primary font', 'go_portfolio_textdomain' ); ?></strong></label></th>
                                <td class="gw-gopf-w300"><input type="text" name="primary-font" id="go-portfolio-primary-font" value="<?php echo esc_attr( isset( $general_settings['primary-font'] ) ? $general_settings['primary-font'] : '' ); ?>" class="gw-gopf-w250" /></td>
                                <td colspan="3"><p class="description"><?php _e( 'Primary font family (e.g. Arial, Helvetica, sans-serif).', 'go_portfolio_textdomain' ); ?></p></td>
                            </tr>                        
                            <tr>
                                <th class="gw-gopf-w150"><label for="go-portfolio-primary-font-css"><strong><?php _e( 'Primary font CSS', 'go_portfolio_textdomain' ); ?></strong></label></th>
                                <td class="gw-gopf-w300"><input type="text" name="primary-font-css" id="go-portfolio-primary-font-css" value="<?php echo esc_attr( isset( $general_settings['primary-font-css'] ) ? $general_settings['primary-font-css'] : '' ); ?>" class="gw-gopf-w250" /></td>
                                <td colspan="3"><p class="description"><?php _e( 'Primary font external CSS file for Google (or other) fonts', 'go_portfolio_textdomain' ); ?></p></td>
                            </tr>                        
                            <tr>
                                <th class="gw-gopf-w150"><label for="go-portfolio-secondary-font"><strong><?php _e( 'Secondary font', 'go_portfolio_textdomain' ); ?></strong></label></th>
                                <td class="gw-gopf-w300"><input type="text" name="secondary-font" id="go-portfolio-secondary-font" value="<?php echo esc_attr( isset( $general_settings['secondary-font'] ) ? $general_settings['secondary-font'] : '' ); ?>" class="gw-gopf-w250" /></td>
                                <td colspan="3"><p class="description"><?php _e( 'Secondary font family (e.g. Verdana, Geneva, sans-serif).', 'go_portfolio_textdomain' ); ?></p></td>
                            </tr>                        
                            <tr>
                                <th class="gw-gopf-w150"><label for="go-portfolio-secondary-font-css"><strong><?php _e( 'Secondary font CSS', 'go_portfolio_textdomain' ); ?></strong></label></th>
                                <td class="gw-gopf-w300"><input type="text" name="secondary-font-css" id="go-portfolio-secondary-font-css" value="<?php echo esc_attr( isset( $general_settings['secondary-font-css'] ) ? $general_settings['secondary-font-css'] : '' ); ?>" class="gw-gopf-w250" /></td>
                                <td colspan="3"><p class="description"><?php _e( 'Secondary font external CSS file for Google (or other) fonts', 'go_portfolio_textdomain' ); ?></p></td>
                            </tr>                        
                        </table>			
				<div class="gw-go-portfolio-separator"></div>
				<table class="form-table">     
					<tr>
						<th class="gw-gopf-w150"><strong><?php _e( 'Enable responsivity', 'go_portfolio_textdomain' ); ?></strong></th>
						<td class="gw-gopf-w100" colspan="4"><label><input type="checkbox" name="responsivity" value="1"<?php echo isset( $general_settings['responsivity'] ) ? 'value="1" checked="checked"' : '' ; ?> /> <?php _e( 'Yes', 'go_portfolio_textdomain' ); ?></label></td>
					</tr>
					<tr>
						<th class="gw-gopf-w150"><label for="go-portfolio-max-width"><strong><?php _e( 'Maximum width in mobile view', 'go_portfolio_textdomain' ); ?></strong></label></th>
						<td class="gw-gopf-w100"><input type="text" name="max-width" id="go-portfolio-max-width" value="<?php echo esc_attr( isset( $general_settings['max-width'] ) ? $general_settings['max-width'] : '' ); ?>" class="gw-gopf-w80" /></td>
						<td colspan="3"><p class="description"><?php _e( 'Maximum with of portfolio in mobile view.', 'go_portfolio_textdomain' ); ?></p></td>
					</tr>					                       
					<tr>
						<th class="gw-gopf-w150"><strong><?php _e( 'Tablet (portrait) media query', 'go_portfolio_textdomain' ); ?></strong></th>
						<td class="gw-gopf-w100"><label for="go-portfolio-size1-min"><?php _e( 'Minimum width', 'go_portfolio_textdomain' ); ?></label></th>
						<td class="gw-gopf-w100"><input type="text" name="size1-min" id="go-portfolio-size1-min" value="<?php echo esc_attr( isset( $general_settings['size1-min'] ) ? $general_settings['size1-min'] : '' ); ?>" class="gw-gopf-w80" /></td>
						<td class="gw-gopf-w100"><label for="go-portfolio-size1-max"><?php _e( 'Maximum width', 'go_portfolio_textdomain' ); ?></label></td>
						<td colspan="2"><input type="text" name="size1-max" id="go-portfolio-size1-max" value="<?php echo esc_attr( isset( $general_settings['size1-max'] ) ? $general_settings['size1-max'] : '' ); ?>" class="gw-gopf-w80" /></td>
					</tr>
					<tr>
						<th class="gw-gopf-w100"><strong><?php _e( 'Mobile (portrait) media query', 'go_portfolio_textdomain' ); ?></strong></th>
						<td class="gw-gopf-w100"><label for="go-portfolio-size2-min"><?php _e( 'Minimum width', 'go_portfolio_textdomain' ); ?></label></th>
						<td class="gw-gopf-w100"><input type="text" name="size2-min" id="go-portfolio-size2-min" value="<?php echo esc_attr ( isset( $general_settings['size2-min'] ) ? $general_settings['size2-min'] : '' ); ?>" class="gw-gopf-w80" /></td>
						<td class="gw-gopf-w100"><label for="go-portfolio-size2-max"><?php _e( 'Maximum width', 'go_portfolio_textdomain' ); ?></label></td>
						<td colspan="2"><input type="text" name="size2-max" id="go-portfolio-size2-max" value="<?php echo esc_attr( isset( $general_settings['size2-max'] ) ? $general_settings['size2-max'] : '' ); ?>" class="gw-gopf-w80" /></td>
					</tr>
					<tr>
						<th class="gw-gopf-w100"><strong><?php _e( 'Mobile (landscape) media query', 'go_portfolio_textdomain' ); ?></strong></th>
						<td class="gw-gopf-w100"><label for="go-portfolio-size3-min"><?php _e( 'Minimum width', 'go_portfolio_textdomain' ); ?></label></th>
						<td class="gw-gopf-w100"><input type="text" name="size3-min" id="go-portfolio-size3-min" value="<?php echo esc_attr( isset( $general_settings['size3-min'] ) ? $general_settings['size3-min'] : '' ); ?>" class="gw-gopf-w80" /></td>
						<td class="gw-gopf-w100"><label for="go-portfolio-size3-max"><?php _e( 'Maximum width', 'go_portfolio_textdomain' ); ?></label></td>
						<td colspan="2"><input type="text" name="size3-max" id="go-portfolio-size3-max" value="<?php echo esc_attr( isset( $general_settings['size3-max'] ) ? $general_settings['size3-max'] : '' ); ?>" class="gw-gopf-w80" /></td>
					</tr>										                                                        
				</table>						
			</div>
		</div> 
		<!-- /postbox -->     

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save', 'go_portfolio_textdomain' ); ?>" />
		</p>

	</form>
	<!-- /form -->
	
</div>