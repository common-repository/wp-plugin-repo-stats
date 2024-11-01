<?php
/*
Plugin Name: WordPress Plugin Repo Stats
Plugin URI: http://www.jimmyscode.com/wordpress/wp-plugin-repo-stats/
Description: Plugin developers -- display the names and download counts for your WordPress plugins in a CSS-stylable table. Includes plugin ratings.
Version: 0.3.1
Author: Jimmy Pe&ntilde;a
Author URI: http://www.jimmyscode.com/
License: GPLv2 or later
*/

if (!defined('WPPRS_PLUGIN_NAME')) {
	// plugin constants
	define('WPPRS_PLUGIN_NAME', 'WordPress Plugin Repo Stats');
	define('WPPRS_VERSION', '0.3.1');
	define('WPPRS_SLUG', 'wp-plugin-repo-stats');
	define('WPPRS_LOCAL', 'wpprs');
	define('WPPRS_OPTION', 'wpprs');
	define('WPPRS_OPTIONS_NAME', 'wpprs_options');
	define('WPPRS_PERMISSIONS_LEVEL', 'manage_options');
	define('WPPRS_PATH', plugin_basename(dirname(__FILE__)));
	/* default values */
	define('WPPRS_DEFAULT_ENABLED', true);
	define('WPPRS_DEFAULT_NOFOLLOW', false);
	define('WPPRS_DEFAULT_SHOW_STARS', false);
	define('WPPRS_DEFAULT_CACHETIME', 43200);
	define('WPPRS_MIN_CACHE_TIME', 300);
	define('WPPRS_DEFAULT_UID', '');
	define('WPPRS_DEFAULT_ROUNDED', false);
	define('WPPRS_DEFAULT_SORT', '');
	define('WPPRS_DEFAULT_SHOW', false);
	define('WPPRS_DEFAULT_NEWWINDOW', false);
	define('WPPRS_DEFAULT_PLUGINNAME', '');
	define('WPPRS_AVAILABLE_SORT', 'ascending,descending');
	/* option array member names */
	define('WPPRS_DEFAULT_ENABLED_NAME', 'enabled');
	define('WPPRS_DEFAULT_NOFOLLOW_NAME', 'nofollow');
	define('WPPRS_DEFAULT_SHOW_STARS_NAME', 'showstars');
	define('WPPRS_DEFAULT_CACHETIME_NAME', 'cachetime');
	define('WPPRS_DEFAULT_UID_NAME', 'uid');
	define('WPPRS_DEFAULT_ROUNDED_NAME', 'rounded');
	define('WPPRS_DEFAULT_SORT_NAME', 'sortorder');
	define('WPPRS_DEFAULT_SHOW_NAME', 'show');
	define('WPPRS_DEFAULT_NEWWINDOW_NAME', 'opennewwindow');
	define('WPPRS_DEFAULT_PLUGINNAME_NAME', 'pluginname');
}
	// oh no you don't
	if (!defined('ABSPATH')) {
		wp_die(__('Do not access this file directly.', wpprs_get_local()));
	}

	// localization to allow for translations
	// also, register the plugin CSS file for later inclusion
	add_action('init', 'wpprs_translation_file');
	function wpprs_translation_file() {
		$plugin_path = wpprs_get_path() . '/translations';
		load_plugin_textdomain(wpprs_get_local(), '', $plugin_path);
		register_wpprs_style();
	}
	// tell WP that we are going to use new options
	// also, register the admin CSS file for later inclusion
	add_action('admin_init', 'wpprs_options_init');
	function wpprs_options_init() {
		register_setting(WPPRS_OPTIONS_NAME, wpprs_get_option(), 'wpprs_validation');
		register_wpprs_admin_style();
		register_wpprs_admin_script();
	}
	// validation function
	function wpprs_validation($input) {
		if (!empty($input)) {
			// validate all form fields
			$input[WPPRS_DEFAULT_UID_NAME] = sanitize_text_field($input[WPPRS_DEFAULT_UID_NAME]);
			$input[WPPRS_DEFAULT_PLUGINNAME_NAME] = sanitize_text_field($input[WPPRS_DEFAULT_PLUGINNAME_NAME]);
			$input[WPPRS_DEFAULT_ENABLED_NAME] = (bool)$input[WPPRS_DEFAULT_ENABLED_NAME];
			$input[WPPRS_DEFAULT_NOFOLLOW_NAME] = (bool)$input[WPPRS_DEFAULT_NOFOLLOW_NAME];
			$input[WPPRS_DEFAULT_SHOW_STARS_NAME] = (bool)$input[WPPRS_DEFAULT_SHOW_STARS_NAME];
			$input[WPPRS_DEFAULT_ROUNDED_NAME] = (bool)$input[WPPRS_DEFAULT_ROUNDED_NAME];
			$input[WPPRS_DEFAULT_NEWWINDOW_NAME] = (bool)$input[WPPRS_DEFAULT_NEWWINDOW_NAME];
			$input[WPPRS_DEFAULT_CACHETIME_NAME] = abs(intval($input[WPPRS_DEFAULT_CACHETIME_NAME]));
			$input[WPPRS_DEFAULT_SORT_NAME] = sanitize_text_field($input[WPPRS_DEFAULT_SORT_NAME]);
		}
		return $input;
	}
	// add Settings sub-menu
	add_action('admin_menu', 'wpprs_plugin_menu');
	function wpprs_plugin_menu() {
		add_options_page(WPPRS_PLUGIN_NAME, WPPRS_PLUGIN_NAME, WPPRS_PERMISSIONS_LEVEL, wpprs_get_slug(), 'wpprs_page');
	}
	// plugin settings page
	// http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/
	// http://www.onedesigns.com/tutorials/how-to-create-a-wordpress-theme-options-page
	function wpprs_page() {
		// check perms
		if (!current_user_can(WPPRS_PERMISSIONS_LEVEL)) {
			wp_die(__('You do not have sufficient permission to access this page', wpprs_get_local()));
		}
	?>
		<div class="wrap">
			<h2 id="plugintitle"><img src="<?php echo wpprs_getimagefilename('stats.png'); ?>" title="" alt="" height="64" width="64" align="absmiddle" /> <?php echo WPPRS_PLUGIN_NAME; ?> by <a href="http://www.jimmyscode.com/">Jimmy Pe&ntilde;a</a></h2>
			<div><?php _e('You are running plugin version', wpprs_get_local()); ?> <strong><?php echo WPPRS_VERSION; ?></strong>.</div>

			<?php /* http://code.tutsplus.com/tutorials/the-complete-guide-to-the-wordpress-settings-api-part-5-tabbed-navigation-for-your-settings-page--wp-24971 */ ?>
			<?php $active_tab = (isset($_GET['tab']) ? $_GET['tab'] : 'settings'); ?>
			<h2 class="nav-tab-wrapper">
			  <a href="?page=<?php echo wpprs_get_slug(); ?>&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', wpprs_get_local()); ?></a>
				<a href="?page=<?php echo wpprs_get_slug(); ?>&tab=parameters" class="nav-tab <?php echo $active_tab == 'parameters' ? 'nav-tab-active' : ''; ?>"><?php _e('Parameters', wpprs_get_local()); ?></a>
				<a href="?page=<?php echo wpprs_get_slug(); ?>&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>"><?php _e('Support', wpprs_get_local()); ?></a>
			</h2>
			
			<form method="post" action="options.php">
			<?php settings_fields(WPPRS_OPTIONS_NAME); ?>
			<?php $options = wpprs_getpluginoptions(); ?>
			<?php update_option(wpprs_get_option(), $options); ?>
			<?php if ($active_tab == 'settings') { ?>
			<h3 id="settings"><img src="<?php echo wpprs_getimagefilename('settings.png'); ?>" title="" alt="" height="61" width="64" align="absmiddle" /> <?php _e('Plugin Settings', wpprs_get_local()); ?></h3>
				<table class="form-table" id="theme-options-wrap">
					<tr valign="top"><th scope="row"><strong><label title="<?php _e('Is plugin enabled? Uncheck this to turn it off temporarily.', wpprs_get_local()); ?>" for="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_ENABLED_NAME; ?>]"><?php _e('Plugin enabled?', wpprs_get_local()); ?></label></strong></th>
						<td><input type="checkbox" id="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_ENABLED_NAME; ?>]" name="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_ENABLED_NAME; ?>]" value="1" <?php checked('1', wpprs_checkifset(WPPRS_DEFAULT_ENABLED_NAME, WPPRS_DEFAULT_ENABLED, $options)); ?> /></td>
					</tr>
					<?php wpprs_explanationrow(__('Is plugin enabled? Uncheck this to turn it off temporarily.', wpprs_get_local())); ?>
					<?php wpprs_getlinebreak(); ?>
			<tr valign="top"><th scope="row"><strong><label title="<?php _e('Enter your wordpress.org userid.', wpprs_get_local()); ?>" for="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_UID_NAME; ?>]"><?php _e('WordPress.org Userid', wpprs_get_local()); ?></label></strong></th>
				<td><input type="text" id="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_UID_NAME; ?>]" name="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_UID_NAME; ?>]" value="<?php echo wpprs_checkifset(WPPRS_DEFAULT_UID_NAME, WPPRS_DEFAULT_UID, $options); ?>" /></td>
					</tr>
			<?php wpprs_explanationrow(__('Enter your wordpress.org userid.', wpprs_get_local())); ?>
			<?php wpprs_getlinebreak(); ?>
					<tr valign="top"><th scope="row"><strong><label title="<?php _e('Check this box to add rel=nofollow to WP plugin repo links.', wpprs_get_local()); ?>" for="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_NOFOLLOW_NAME; ?>]"><?php _e('Nofollow plugin link(s)?', wpprs_get_local()); ?></label></strong></th>
				<td><input type="checkbox" id="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_NOFOLLOW_NAME; ?>]" name="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_NOFOLLOW_NAME; ?>]" value="1" <?php checked('1', wpprs_checkifset(WPPRS_DEFAULT_NOFOLLOW_NAME, WPPRS_DEFAULT_NOFOLLOW, $options)); ?> /></td>
					</tr>
			<?php wpprs_explanationrow(__('Check this box to add rel="nofollow" to WP plugin repo links.', wpprs_get_local())); ?>
			<?php wpprs_getlinebreak(); ?>
			<tr valign="top"><th scope="row"><strong><label title="<?php _e('Check this box to use rounded corner CSS on the table header.', wpprs_get_local()); ?>" for="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_ROUNDED_NAME; ?>]"><?php _e('Rounded corner CSS?', wpprs_get_local()); ?></label></strong></th>
				<td><input type="checkbox" id="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_ROUNDED_NAME; ?>]" name="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_ROUNDED_NAME; ?>]" value="1" <?php checked('1', wpprs_checkifset(WPPRS_DEFAULT_ROUNDED_NAME, WPPRS_DEFAULT_ROUNDED, $options)); ?> /></td>
					</tr>
					<?php wpprs_explanationrow(__('Check this box to use rounded corner CSS on the table header.', wpprs_get_local())); ?>
					<?php wpprs_getlinebreak(); ?>
					<tr valign="top"><th scope="row"><strong><label title="<?php _e('Check this box to show plugin star ratings.', wpprs_get_local()); ?>" for="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_SHOW_STARS_NAME; ?>]"><?php _e('Show plugin ratings?', wpprs_get_local()); ?></label></strong></th>
				<td><input type="checkbox" id="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_SHOW_STARS_NAME; ?>]" name="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_SHOW_STARS_NAME; ?>]" value="1" <?php checked('1', wpprs_checkifset(WPPRS_DEFAULT_SHOW_STARS_NAME, WPPRS_DEFAULT_SHOW_STARS, $options)); ?> /></td>
					</tr>
			<?php wpprs_explanationrow(__('Check this box to show plugin star ratings.', wpprs_get_local())); ?>
			<?php wpprs_getlinebreak(); ?>
			<tr valign="top"><th scope="row"><strong><label title="<?php _e('Enter time in seconds between cache refreshes. Default is ' . WPPRS_DEFAULT_CACHETIME . ' seconds, minimum is ' . WPPRS_MIN_CACHE_TIME . ' seconds.', wpprs_get_local()); ?>" for="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_CACHETIME_NAME; ?>]"><?php _e('Cache time (seconds)', wpprs_get_local()); ?></label></strong></th>
				<td><input type="number" min="<?php echo WPPRS_MIN_CACHE_TIME; ?>" max="604800" step="1" id="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_CACHETIME_NAME; ?>]" name="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_CACHETIME_NAME; ?>]" value="<?php echo wpprs_checkifset(WPPRS_DEFAULT_CACHETIME_NAME, WPPRS_DEFAULT_CACHETIME, $options); ?>" /></td>
			</tr>
			<?php wpprs_explanationrow(__('Enter time in seconds between cache refreshes. Default is <strong>' . WPPRS_DEFAULT_CACHETIME . '</strong> seconds, minimum is <strong>' . WPPRS_MIN_CACHE_TIME . '</strong> seconds.', wpprs_get_local())); ?>
			<?php wpprs_getlinebreak(); ?>
					<tr valign="top"><th scope="row"><strong><label title="<?php _e('Select the sort order. Default is ascending (by plugin name).', wpprs_get_local()); ?>" for="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_SORT_NAME; ?>]"><?php _e('Default sort order', wpprs_get_local()); ?></label></strong></th>
				<td><select id="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_SORT_NAME; ?>]" name="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_SORT_NAME; ?>]">
			<?php $orders = explode(",", WPPRS_AVAILABLE_SORT);
						foreach($orders as $order) {
			echo '<option value="' . $order . '"' . selected($order, wpprs_checkifset(WPPRS_DEFAULT_SORT_NAME, WPPRS_DEFAULT_SORT, $options), false) . '>' . $order . '</option>';
						} ?>
			</select></td>
			</tr>
			<?php wpprs_explanationrow(__('Select the sort order. Default is ascending (by plugin name).', wpprs_get_local())); ?>
			<?php wpprs_getlinebreak(); ?>
					<tr valign="top"><th scope="row"><strong><label title="<?php _e('Check this box to open links in a new window.', wpprs_get_local()); ?>" for="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_NEWWINDOW_NAME; ?>]"><?php _e('Open links in new window?', wpprs_get_local()); ?></label></strong></th>
				<td><input type="checkbox" id="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_NEWWINDOW_NAME; ?>]" name="<?php echo wpprs_get_option(); ?>[<?php echo WPPRS_DEFAULT_NEWWINDOW_NAME; ?>]" value="1" <?php checked('1', wpprs_checkifset(WPPRS_DEFAULT_NEWWINDOW_NAME, WPPRS_DEFAULT_NEWWINDOW, $options)); ?> /></td>
					</tr>
					<?php wpprs_explanationrow(__('Check this box to open links in a new window.', wpprs_get_local())); ?>
				</table>
				<?php submit_button(); ?>
			<?php } elseif ($active_tab == 'parameters') { ?>
			<h3 id="parameters"><img src="<?php echo wpprs_getimagefilename('parameters.png'); ?>" title="" alt="" height="64" width="64" align="absmiddle" /> <?php _e('Plugin Parameters and Default Values', wpprs_get_local()); ?></h3>
			These are the parameters for using the shortcode, or calling the plugin from your PHP code.

			<?php echo wpprs_parameters_table(wpprs_get_local(), wpprs_shortcode_defaults(), wpprs_required_parameters()); ?>			

			<h3 id="examples"><img src="<?php echo wpprs_getimagefilename('examples.png'); ?>" title="" alt="" height="64" width="64" align="absmiddle" /> <?php _e('Shortcode and PHP Examples', wpprs_get_local()); ?></h3>
			<h4><?php _e('Shortcode Format:', wpprs_get_local()); ?></h4>
			<?php echo wpprs_get_example_shortcode('plugin-repo-stats', wpprs_shortcode_defaults(), wpprs_get_local()); ?>

			<h4><?php _e('PHP Format:', wpprs_get_local()); ?></h4>
			<?php echo wpprs_get_example_php_code('plugin-repo-stats', 'wpprs', wpprs_shortcode_defaults()); ?>
			<?php _e('<small>Note: \'show\' is false by default; set it to <strong>true</strong> echo the output, or <strong>false</strong> to return the output to your PHP code.</small>', wpprs_get_local()); ?>
			<?php } else { ?>
			<h3 id="support"><img src="<?php echo wpprs_getimagefilename('support.png'); ?>" title="" alt="" height="64" width="64" align="absmiddle" /> <?php _e('Support', wpprs_get_local()); ?></h3>
				<div class="support">
				<?php echo wpprs_getsupportinfo(wpprs_get_slug(), wpprs_get_local()); ?>
				<small><?php _e('Disclaimer: This plugin is not affiliated with or endorsed by WordPress.', wpprs_get_local()); ?></small>
				</div>
			<?php } ?>
			</form>
		</div>
		<?php }
	// shortcode/function for plugin output
	add_shortcode('plugin-repo-stats', 'wpprs');
	add_shortcode('wp-plugin-repo-stats', 'wpprs');
	function wpprs($atts) {
		// get parameters
		extract(shortcode_atts(wpprs_shortcode_defaults(), $atts));
		// plugin is enabled/disabled from settings page only
		$options = wpprs_getpluginoptions();
		if (!empty($options)) {
			$enabled = (bool)$options[WPPRS_DEFAULT_ENABLED_NAME];
		} else {
			$enabled = WPPRS_DEFAULT_ENABLED;
		}

		$output = '';
		// ******************************
		// derive shortcode values from constants
		// ******************************
		if ($enabled) {
			$temp_nofollow = constant('WPPRS_DEFAULT_NOFOLLOW_NAME');
			$nofollow = $$temp_nofollow;
			$temp_window = constant('WPPRS_DEFAULT_NEWWINDOW_NAME');
			$opennewwindow = $$temp_window;
			$temp_show = constant('WPPRS_DEFAULT_SHOW_NAME');
			$show = $$temp_show;
			$temp_rounded = constant('WPPRS_DEFAULT_ROUNDED_NAME');
			$rounded = $$temp_rounded;
			$temp_uid = constant('WPPRS_DEFAULT_UID_NAME');
			$uid = $$temp_uid;
			$temp_cachetime = constant('WPPRS_DEFAULT_CACHETIME_NAME');
			$cachetime = $$temp_cachetime;
			$temp_stars = constant('WPPRS_DEFAULT_SHOW_STARS_NAME');
			$showstars = $$temp_stars;
			$temp_sort = constant('WPPRS_DEFAULT_SORT_NAME');
			$sortorder = $$temp_sort;
			$temp_pluginname = constant('WPPRS_DEFAULT_PLUGINNAME_NAME');
			$pluginname = $$temp_pluginname;
		}
		// ******************************
		// sanitize user input
		// ******************************
		if ($enabled) {
			$uid = sanitize_text_field($uid);
			$nofollow = (bool)$nofollow;
			$rounded = (bool)$rounded;
			$cachetime = abs(intval($cachetime));
			$showstars = (bool)$showstars;
			$sortorder = sanitize_text_field($sortorder);
			$opennewwindow = (bool)$opennewwindow;
			$show = (bool)$show;
			$pluginname = sanitize_text_field($pluginname); 

			// allow alternate parameter names for uid
			if (!empty($atts['userid'])) {
				$uid = sanitize_text_field($atts['userid']);
			}
		}
		// ******************************
		// check for parameters, then settings, then defaults
		// ******************************
		if ($enabled) {
			if ($uid === WPPRS_DEFAULT_UID) { // no user id passed to function, try settings page
				$uid = $options[WPPRS_DEFAULT_UID_NAME];
				if (!$uid) { // no userid on settings page either
					$enabled = false;
					$output = '<!-- ' . WPPRS_PLUGIN_NAME . ': ';
					$output .= __('plugin is disabled. Either you did not pass a necessary setting to the plugin, or did not configure a default. Check Settings page.', wpprs_get_local());
					$output .= ' -->';
				}
			}
			if ($enabled) { // save some cycles if the plugin was disabled above
				$nofollow = wpprs_setupvar($nofollow, WPPRS_DEFAULT_NOFOLLOW, WPPRS_DEFAULT_NOFOLLOW_NAME, $options);
				$rounded = wpprs_setupvar($rounded, WPPRS_DEFAULT_ROUNDED, WPPRS_DEFAULT_ROUNDED_NAME, $options);

				// is cache time numeric? also, convert to positive integer
				if (!is_numeric(abs(intval($cachetime)))) {
					$cachetime = WPPRS_DEFAULT_CACHETIME;
				} else { // it's numeric
					$cachetime = wpprs_setupvar($cachetime, WPPRS_DEFAULT_CACHETIME, WPPRS_DEFAULT_CACHETIME_NAME, $options);
				}
				// cache time should not be less than WPPRS_MIN_CACHE_TIME seconds, to avoid overtaxing wp.org
				$cachetime = max(WPPRS_MIN_CACHE_TIME, $cachetime);

				$showstars = wpprs_setupvar($showstars, WPPRS_DEFAULT_SHOW_STARS, WPPRS_DEFAULT_SHOW_STARS_NAME, $options);
				$sortorder = wpprs_setupvar($sortorder, WPPRS_DEFAULT_SORT, WPPRS_DEFAULT_SORT_NAME, $options);
				$pluginname = wpprs_setupvar($pluginname, WPPRS_DEFAULT_PLUGINNAME, WPPRS_DEFAULT_PLUGINNAME_NAME, $options);
				$opennewwindow = wpprs_setupvar($opennewwindow, WPPRS_DEFAULT_NEWWINDOW, WPPRS_DEFAULT_NEWWINDOW_NAME, $options);
			}
		} // end enabled check

		// ******************************
		// do some actual work
		// ******************************
		if ($enabled) {
			$orders = explode(",", WPPRS_AVAILABLE_SORT);
			if (!in_array($sortorder, $orders)) {
				$sortorder = $options[WPPRS_DEFAULT_SORT_NAME];
				if ($sortorder === false) {
					$sortorder = WPPRS_DEFAULT_SORT;
				}
			}
			wpprs_styles();
			$querypath = '//div[@class="info-group plugin-theme main-plugins inactive"]//';
			if ($pluginname == WPPRS_DEFAULT_PLUGINNAME) { // we want the full table
				$transient_name = 'wpprs_count_' . $uid;
			} else { // we want it for one plugin only
				$transient_name = 'wpprs_count_' . $pluginname;
			}
			$response = get_transient($transient_name);
			if (!$response) { // regenerate and cache
				// get wordpress plugin stats page html
				$response = wp_remote_retrieve_body(wp_remote_get('http://profiles.wordpress.org/' . $uid . '/'));
				if (is_wp_error($response)) {
					exit();
				}
				// parse HTML response
				$dom = new DOMDocument();
				@$dom->loadHTML($response);
				$xpath = new DOMXPath($dom);

				// get count of plugins
				$pContent = $xpath->query($querypath . 'p');
				for ($i = 0; $i < $pContent->length; $i++) {
					if ($pContent->item($i)->getAttribute('class') === 'downloads') {
						$plugincount++;
					}
				}
				// get individual download counts into array
				$indivcounts = array();
				for ($i = 0; $i < $plugincount; $i++) {
					$strippedcount = str_replace(" downloads", "", $pContent->item($i)->nodeValue);
					array_push($indivcounts, $strippedcount);
					if ($pluginname == WPPRS_DEFAULT_PLUGINNAME) { // we need the full sum
						$sum = $sum + (int)str_replace(",", "", $strippedcount);
					}
				}
				// get plugin names into array
				$h3Content = $xpath->query($querypath . 'h3');
				$indivnames = array();
				for ($i = 0; $i < $plugincount; $i++) {
					array_push($indivnames, trim($h3Content->item($i)->nodeValue));
				}
				// get plugin URLs into array
				$indivurls = array();
				$aContent = $xpath->query($querypath . 'a');
				for ($i = 0; $i < $plugincount; $i++) {
					array_push($indivurls, 'http:' . $aContent->item($i)->getAttribute('href'));
				}
				
				// get indiv plugin info (if needed)
				if ($pluginname !== WPPRS_DEFAULT_PLUGINNAME) { // we want the individual plugin info
					for ($i = 0; $i < $plugincount; $i++) {
						if ($indivnames[$i] == $pluginname) {
							$this_plugin_dl_count = $indivcounts[$i];
							$this_plugin_url = $indivurls[$i];
							break;
						}
					}
				}
				// format total downloads with thousands separator
				// only run this code if necessary
				if ($pluginname == WPPRS_DEFAULT_PLUGINNAME) { // we want the sum, so correct it
					$sum = number_format($sum);
				}

				// do we want plugin ratings?
				if ($showstars) {
					// visit each plugin URL and get star count
					$starcounts = array();
					$querypath = '//div[@class="star-holder"]//';
					for ($i = 0; $i < $plugincount; $i++) {
						if ($pluginname) { // get rating of the plugin we want only
							if ($indivurls[$i] == $this_plugin_url) {
								$response = wp_remote_retrieve_body(wp_remote_get($indivurls[$i]));
								if (is_wp_error($response)) {
									array_push($starcounts, 'width: 0px');
								} else {
									// parse HTML response
									$dom = new DOMDocument();
									@$dom->loadHTML($response);
									$xpath = new DOMXPath($dom);
									$starholder = $xpath->query($querypath . 'div');
									array_push($starcounts, $starholder->item(0)->getAttribute('style'));
								}
							}
						} else { // get em all
							$response = wp_remote_retrieve_body(wp_remote_get($indivurls[$i]));
							if (is_wp_error($response)) {
								array_push($starcounts, 'width: 0px');
							} else {
								// parse HTML response
								$dom = new DOMDocument();
								@$dom->loadHTML($response);
								$xpath = new DOMXPath($dom);
								$starholder = $xpath->query($querypath . 'div');
								array_push($starcounts, $starholder->item(0)->getAttribute('style'));
							}
						}
					}
				}
				if ($pluginname !== WPPRS_DEFAULT_PLUGINNAME) { // get plugin rating
					$this_plugin_rating = $starcounts[0];
				}

				if ($pluginname !== WPPRS_DEFAULT_PLUGINNAME) { // we want only one plugin's info 
					$output = '<div class="wpprs-single-plugin' . ($rounded ? ' wpprs-rounded-corners' : '') . '">';
					$output .= '<div class="wpprs-single-body">';
					$output .= '<div class="wpprs-single-dl-name-count">' . __('Downloads of ', wpprs_get_local());
					$output .= '<a' . ($opennewwindow ? ' onclick="window.open(this.href); return false;" onkeypress="window.open(this.href); return false;" ' : ' ');
					$output .= ($nofollow ? ' rel="nofollow" ' : ' ') . 'href="' . esc_url($this_plugin_url) . '">' . $pluginname . '</a>';
					$output	.= __(' as of ', wpprs_get_local()) . date_i18n(get_option('date_format'), date()) . ':</div>';
					$output .= '<div class="wpprs-single-dl-count"><strong>' . $this_plugin_dl_count . '</strong></div><br />';

					if ($showstars) {
						// get stars width from 'width: ##px'
						$starswidth = explode(":", $this_plugin_rating);
						$starswidth = $starswidth[1];
						$starswidth = explode("px", $starswidth);
						$starswidth = $starswidth[0];
						if ($starswidth > 0) {
							$starswidth = round($starswidth / 18.4, 2);
						} else {
							$starswidth = 0;
						}
						$output .= '<div class="wpprs-single-rating">' . __('Current Rating: ', wpprs_get_local());
						$output .= '<div title="' . $starswidth . __(' out of 5 stars', wpprs_get_local()) . ((bool)$starswidth ? '' : __(' or rating not available', wpprs_get_local())) .'" class="star-holder"><div class="star-rating" style="' . $starcounts[0] . '"></div></div>';
						$output .= '</div>';
					}
					$output .= '</div></div>';
				} else { // we want the whole table
					// start formatting output
					$output = '<div class="wpprs">';
					$output .= '<div class="wpprs-top' . ($rounded ? ' wpprs-rounded-corners' : '') . '">';
					$output .= '<h2>' . $sum . '</h2>';
					$output .= '<div class="wpprs-dl-count">' . __('The number of times my ', wpprs_get_local()) . '<span class="wpprs-plugincount">' . $plugincount . '</span> ' . __('WordPress', wpprs_get_local()) . __(($plugincount == 1) ? ' plugin has ' : ' plugins have ', wpprs_get_local()) . __('been downloaded according to the official ', wpprs_get_local()) . '<a' . ($opennewwindow ? ' onclick="window.open(this.href); return false;" onkeypress="window.open(this.href); return false;" ' : ' ') . ($nofollow ? ' rel="nofollow" ' : ' ') . 'href="http://wordpress.org/extend/plugins/">' . __('WordPress Plugins Repository', wpprs_get_local()) . '</a>.</div>';
					$output .= '</div> <!-- end wpprs-top -->';
					$output .= '<div class="wpprs-body">';
					$output .= '<h3 class="wp-logo">' . __('My WordPress Plugins', wpprs_get_local()) . '</h3>';
					$output .= '<table class="wpprs-table">';
					$output .= '<thead>';
					$output .= '<tr><th class="wpprs-headindex">#</th><th class="wpprs-headname">' . __('Plugin Name', wpprs_get_local()) . '</th><th class="wpprs-headcount">' . __('Download Count', wpprs_get_local()) . '</th>';
					if ($showstars) {
						$output .= '<th class="wpprs-headrating">' . __('Rating', wpprs_get_local()) . '</th>';
					}
					$output .= '</tr></thead>';
					$output .= '<tbody>';
					// combine records into a single array for sorting
					$outputarray = array();
					if ($showstars) { // include star counts
						for ($i = 0; $i < $plugincount; $i++) {
							$outputarray[$i] = array($indivnames[$i], $indivurls[$i], $indivcounts[$i], $starcounts[$i]);
						}
					} else {
						for ($i = 0; $i < $plugincount; $i++) {
							$outputarray[$i] = array($indivnames[$i], $indivurls[$i], $indivcounts[$i]);
						}
					}
					// sort array ascending?
					if ($sortorder === 'ascending') {
						array_multisort($outputarray, SORT_ASC);
					} else {
						array_multisort($outputarray, SORT_DESC);
					}
					// loop through arrays and print URL, name and count for each plugin
					for ($i = 0; $i < $plugincount; $i++) {
						$output .= '<tr' . ($i % 2 != 0 ? ' class="wpprs-evenrow" ' : ' class="wpprs-oddrow" ') . '>';
						$output .= '<td class="wpprs-index">' . ($i + 1) . '</td>';
						// plugin URL and name
						$pluginurl = '<a' . ($opennewwindow ? ' onclick="window.open(this.href); return false;" onkeypress="window.open(this.href); return false;" ' : ' ') . ($nofollow ? ' rel="nofollow" ' : ' ') . 'href="' . esc_url($outputarray[$i][1]) . '">' . $outputarray[$i][0] . '</a>';
						$output .= '<td class="wpprs-name">' . $pluginurl . '</td>';
						// download count for that plugin
						$output .= '<td class="wpprs-count">' . $outputarray[$i][2] . '</td>';
						// star rating
						if ($showstars) {
							// get stars width from 'width: ##px'
							$starswidth = explode(":", $outputarray[$i][3]);
							$starswidth = $starswidth[1];
							$starswidth = explode("px", $starswidth);
							$starswidth = $starswidth[0];
							if ($starswidth > 0) {
								$starswidth = round($starswidth / 18.4, 2);
							} else {
								$starswidth = 0;
							}
							$output .= '<td class="wpprs-rating"><div title="' . $starswidth . __(' out of 5 stars', wpprs_get_local()) . ((bool)$starswidth ? '' : __(' or rating not available', wpprs_get_local())) .'" class="star-holder"><div class="star-rating" style="' . $outputarray[$i][3] . '"></div></div></td>';
						}
						// end row
						$output .= '</tr>';
					}
					// finish output
					$output .= '</tbody>';
					$output .= '</table>';
					$output .= '</div> <!-- end wpprs body -->';
					$output .= '</div> <!-- end wpprs -->';
				}
				// cache output
				set_transient($transient_name, $output, $cachetime);
			 } else { // cache exists
				$output = $response;
			 }
		} else { // plugin disabled
			$output = '<!-- ' . WPPRS_PLUGIN_NAME . ': ' . __('plugin is disabled. Either you did not pass a necessary setting to the plugin, or did not configure a default. Check Settings page.', wpprs_get_local()) . ' -->';
		}
		// do we want to return or echo output? default is 'return'
		if ($show) {
			echo $output;
		} else {
			return $output;
		}
	} // end shortcode function
	// show admin messages to plugin user
	add_action('admin_notices', 'wpprs_showAdminMessages');
	function wpprs_showAdminMessages() {
		// http://wptheming.com/2011/08/admin-notices-in-wordpress/
		global $pagenow;
		if (current_user_can(WPPRS_PERMISSIONS_LEVEL)) { // user has privilege
			if ($pagenow == 'options-general.php') { // we are on Settings page
				if (isset($_GET['page'])) {
					if ($_GET['page'] == wpprs_get_slug()) { // we are on this plugin's settings page
						$options = wpprs_getpluginoptions();
						if (!empty($options)) {
							$enabled = (bool)$options[WPPRS_DEFAULT_ENABLED_NAME];
							$uid = $options[WPPRS_DEFAULT_UID_NAME];
							if (!$enabled) {
								echo '<div id="message" class="error">' . WPPRS_PLUGIN_NAME . ' ' . __('is currently disabled.', wpprs_get_local()) . '</div>';
							}
							if (($uid === WPPRS_DEFAULT_UID) || ($uid === false)) {
								echo '<div id="message" class="updated">' . __('No userid entered. If you do not set userid here, you must pass it to the plugin via shortcode or function call.', wpprs_get_local()) . '</div>';
							}
						}
					}
				}
			} // end page check
		} // end privilege check
	} // end admin msgs function
	// enqueue admin CSS if we are on the plugin options page
	add_action('admin_head', 'insert_wpprs_admin_css');
	function insert_wpprs_admin_css() {
		global $pagenow;
		if (current_user_can(WPPRS_PERMISSIONS_LEVEL)) {
			if ($pagenow == 'options-general.php') {
				if (isset($_GET['page'])) {
					if ($_GET['page'] == wpprs_get_slug()) { // we are on this plugin's settings page
						wpprs_admin_styles();
					}
				}
			}
		}
	}
	// add helpful links to plugin page next to plugin name
	// http://bavotasan.com/2009/a-settings-link-for-your-wordpress-plugins/
	// http://wpengineer.com/1295/meta-links-for-wordpress-plugins/
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpprs_plugin_settings_link');
	add_filter('plugin_row_meta', 'wpprs_meta_links', 10, 2);
	
	function wpprs_plugin_settings_link($links) {
		return wpprs_settingslink($links, wpprs_get_slug(), wpprs_get_local());
	}
	function wpprs_meta_links($links, $file) {
		if ($file == plugin_basename(__FILE__)) {
			$links = array_merge($links,
			array(
				sprintf(__('<a href="http://wordpress.org/support/plugin/%s">Support</a>', wpprs_get_local()), wpprs_get_slug()),
				sprintf(__('<a href="http://wordpress.org/extend/plugins/%s/">Documentation</a>', wpprs_get_local()), wpprs_get_slug()),
				sprintf(__('<a href="http://wordpress.org/plugins/%s/faq/">FAQ</a>', wpprs_get_local()), wpprs_get_slug())
			));
		}
		return $links;	
	}
	// enqueue/register the plugin CSS file
	function wpprs_styles() {
		wp_enqueue_style('wpprs_style');
	}
	function register_wpprs_style() {
		wp_register_style( 'wpprs_style', 
			plugins_url(wpprs_get_path() . '/css/wp_plugin_repo_stats.css'), 
			array(), 
			WPPRS_VERSION . "_" . date('njYHis', filemtime(dirname(__FILE__) . '/css/wp_plugin_repo_stats.css')),
			'all' );
	}
	// enqueue/register the admin CSS file
	function wpprs_admin_styles() {
		wp_enqueue_style('wpprs_admin_style');
	}
	function register_wpprs_admin_style() {
		wp_register_style( 'wpprs_admin_style',
			plugins_url(wpprs_get_path() . '/css/admin.css'),
			array(),
			WPPRS_VERSION . "_" . date('njYHis', filemtime(dirname(__FILE__) . '/css/admin.css')),
			'all' );
	}
	// enqueue/register the admin JS file
	add_action('admin_enqueue_scripts', 'wpprs_ed_buttons');
	function wpprs_ed_buttons($hook) {
		if (($hook == 'post-new.php') || ($hook == 'post.php')) {
			wp_enqueue_script('wpprs_add_editor_button');
		}
	}
	function register_wpprs_admin_script() {
		wp_register_script('wpprs_add_editor_button',
			plugins_url(wpprs_get_path() . '/js/editor_button.js'), 
			array('quicktags'), 
			WPPRS_VERSION . "_" . date('njYHis', filemtime(dirname(__FILE__) . '/js/editor_button.js')), 
			true);
	}
	// when plugin is activated, create options array and populate with defaults
	register_activation_hook(__FILE__, 'wpprs_activate');
	function wpprs_activate() {
		$options = wpprs_getpluginoptions();
		update_option(wpprs_get_option(), $options);
		
		// delete option when plugin is uninstalled
		register_uninstall_hook(__FILE__, 'uninstall_wpprs_plugin');
	}
	function uninstall_wpprs_plugin() {
		delete_option(wpprs_get_option());
	}

	// generic function that returns plugin options from DB
	// if option does not exist, returns plugin defaults
	function wpprs_getpluginoptions() {
		return get_option(wpprs_get_option(), array(
			WPPRS_DEFAULT_ENABLED_NAME => WPPRS_DEFAULT_ENABLED, 
			WPPRS_DEFAULT_NOFOLLOW_NAME => WPPRS_DEFAULT_NOFOLLOW, 
			WPPRS_DEFAULT_SHOW_STARS_NAME => WPPRS_DEFAULT_SHOW_STARS, 
			WPPRS_DEFAULT_CACHETIME_NAME => WPPRS_DEFAULT_CACHETIME, 
			WPPRS_DEFAULT_UID_NAME => WPPRS_DEFAULT_UID, 
			WPPRS_DEFAULT_ROUNDED_NAME => WPPRS_DEFAULT_ROUNDED, 
			WPPRS_DEFAULT_SORT_NAME => WPPRS_DEFAULT_SORT, 
			WPPRS_DEFAULT_NEWWINDOW_NAME => WPPRS_DEFAULT_NEWWINDOW,
			WPPRS_DEFAULT_PLUGINNAME_NAME => WPPRS_DEFAULT_PLUGINNAME
			));
	}
	// function to return shortcode defaults
	function wpprs_shortcode_defaults() {
		return array(
		WPPRS_DEFAULT_UID_NAME => WPPRS_DEFAULT_UID, 
		WPPRS_DEFAULT_NOFOLLOW_NAME => WPPRS_DEFAULT_NOFOLLOW, 
		WPPRS_DEFAULT_ROUNDED_NAME => WPPRS_DEFAULT_ROUNDED, 
		WPPRS_DEFAULT_CACHETIME_NAME => WPPRS_DEFAULT_CACHETIME, 
		WPPRS_DEFAULT_SHOW_STARS_NAME => WPPRS_DEFAULT_SHOW_STARS, 
		WPPRS_DEFAULT_SORT_NAME => WPPRS_DEFAULT_SORT, 
		WPPRS_DEFAULT_NEWWINDOW_NAME => WPPRS_DEFAULT_NEWWINDOW, 
		WPPRS_DEFAULT_SHOW_NAME => WPPRS_DEFAULT_SHOW, 
		WPPRS_DEFAULT_PLUGINNAME_NAME => WPPRS_DEFAULT_PLUGINNAME
		);
	}
	// function to return parameter status (required or not)
	function wpprs_required_parameters() {
		return array(
			true,
			false,
			false,
			false,
			false,
			false,
			false,
			false,
			false
		);
	}
	
	// encapsulate these and call them throughout the plugin instead of hardcoding the constants everywhere
	function wpprs_get_slug() { return WPPRS_SLUG; }
	function wpprs_get_local() { return WPPRS_LOCAL; }
	function wpprs_get_option() { return WPPRS_OPTION; }
	function wpprs_get_path() { return WPPRS_PATH; }
	
	function wpprs_settingslink($linklist, $slugname = '', $localname = '') {
		$settings_link = sprintf( __('<a href="options-general.php?page=%s">Settings</a>', $localname), $slugname);
		array_unshift($linklist, $settings_link);
		return $linklist;
	}
	function wpprs_setupvar($var, $defaultvalue, $defaultvarname, $optionsarr) {
		if ($var == $defaultvalue) {
			$var = $optionsarr[$defaultvarname];
			if (!$var) {
				$var = $defaultvalue;
			}
		}
		return $var;
	}
	function wpprs_getsupportinfo($slugname = '', $localname = '') {
		$output = __('Do you need help with this plugin? Check out the following resources:', $localname);
		$output .= '<ol>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/extend/plugins/%s/">Documentation</a>', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/plugins/%s/faq/">FAQ</a><br />', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/support/plugin/%s">Support Forum</a><br />', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://www.jimmyscode.com/wordpress/%s">Plugin Homepage / Demo</a><br />', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/extend/plugins/%s/developers/">Development</a><br />', $localname), $slugname) . '</li>';
		$output .= '<li>' . sprintf( __('<a href="http://wordpress.org/plugins/%s/changelog/">Changelog</a><br />', $localname), $slugname) . '</li>';
		$output .= '</ol>';
		
		$output .= sprintf( __('If you like this plugin, please <a href="http://wordpress.org/support/view/plugin-reviews/%s/">rate it on WordPress.org</a>', $localname), $slugname);
		$output .= sprintf( __(' and click the <a href="http://wordpress.org/plugins/%s/#compatibility">Works</a> button. ', $localname), $slugname);
		$output .= '<br /><br /><br />';
		$output .= __('Your donations encourage further development and support. ', $localname);
		$output .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7EX9NB9TLFHVW"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="Donate with PayPal" title="Support this plugin" width="92" height="26" /></a>';
		$output .= '<br /><br />';
		return $output;
	}
	
	function wpprs_parameters_table($localname = '', $sc_defaults, $reqparms) {
	  $output = '<table class="widefat">';
		$output .= '<thead><tr>';
		$output .= '<th title="' . __('The name of the parameter', $localname) . '"><strong>' . __('Parameter Name', $localname) . '</strong></th>';
		$output .= '<th title="' . __('Is this parameter required?', $localname) . '"><strong>' . __('Is Required?', $localname) . '</strong></th>';
		$output .= '<th title="' . __('What data type this parameter accepts', $localname) . '"><strong>' . __('Data Type', $localname) . '</strong></th>';
		$output .= '<th title="' . __('What, if any, is the default if no value is specified', $localname) . '"><strong>' . __('Default Value', $localname) . '</strong></th>';
		$output .= '</tr></thead>';
		$output .= '<tbody>';
		
		$plugin_defaults_keys = array_keys($sc_defaults);
		$plugin_defaults_values = array_values($sc_defaults);
		$required = $reqparms;
		for($i = 0; $i < count($plugin_defaults_keys); $i++) {
			$output .= '<tr>';
			$output .= '<td><strong>' . $plugin_defaults_keys[$i] . '</strong></td>';
			$output .= '<td>';
			
			if ($required[$i] === true) {
				$output .= '<strong>';
				$output .= __('Yes', $localname);
				$output .= '</strong>';
			} else {
				$output .= __('No', $localname);
			}
			
			$output .= '</td>';
			$output .= '<td>' . gettype($plugin_defaults_values[$i]) . '</td>';
			$output .= '<td>';
			
			if ($plugin_defaults_values[$i] === true) {
				$output .= '<strong>';
				$output .= __('true', $localname);
				$output .= '</strong>';
			} elseif ($plugin_defaults_values[$i] === false) {
				$output .= __('false', $localname);
			} elseif ($plugin_defaults_values[$i] === '') {
				$output .= '<em>';
				$output .= __('this value is blank by default', $localname);
				$output .= '</em>';
			} elseif (is_numeric($plugin_defaults_values[$i])) {
				$output .= $plugin_defaults_values[$i];
			} else { 
				$output .= '"' . $plugin_defaults_values[$i] . '"';
			} 
			$output .= '</td>';
			$output .= '</tr>';
		}
		$output .= '</tbody>';
		$output .= '</table>';
		
		return $output;
	}
	function wpprs_get_example_shortcode($shortcodename = '', $sc_defaults, $localname = '') {
		$output = '<pre style="background:#FFF">[' . $shortcodename . ' ';
		
		$plugin_defaults_keys = array_keys($sc_defaults);
		$plugin_defaults_values = array_values($sc_defaults);
		
		for($i = 0; $i < count($plugin_defaults_keys); $i++) {
			if ($plugin_defaults_keys[$i] !== 'show') {
				if (gettype($plugin_defaults_values[$i]) === 'string') {
					$output .= '<strong>' . $plugin_defaults_keys[$i] . '</strong>=\'' . $plugin_defaults_values[$i] . '\'';
				} elseif (gettype($plugin_defaults_values[$i]) === 'boolean') {
					$output .= '<strong>' . $plugin_defaults_keys[$i] . '</strong>=' . ($plugin_defaults_values[$i] == false ? 'false' : 'true');
				} else {
					$output .= '<strong>' . $plugin_defaults_keys[$i] . '</strong>=' . $plugin_defaults_values[$i];
				}
				if ($i < count($plugin_defaults_keys) - 2) {
					$output .= ' ';
				}
			}
		}
		$output .= ']</pre>';
		
		return $output;
	}
	
	function wpprs_get_example_php_code($shortcodename = '', $internalfunctionname = '', $sc_defaults) {
		
		$plugin_defaults_keys = array_keys($sc_defaults);
		$plugin_defaults_values = array_values($sc_defaults);
		
		$output = '<pre style="background:#FFF">';
		$output .= 'if (shortcode_exists(\'' . $shortcodename . '\')) {<br />';
		$output .= '  $atts = array(<br />';
		for($i = 0; $i < count($plugin_defaults_keys); $i++) {
			$output .= '    \'' . $plugin_defaults_keys[$i] . '\' => ';
			if (gettype($plugin_defaults_values[$i]) === 'string') {
				$output .= '\'' . $plugin_defaults_values[$i] . '\'';
			} elseif (gettype($plugin_defaults_values[$i]) === 'boolean') {
				$output .= ($plugin_defaults_values[$i] == false ? 'false' : 'true');
			} else {
				$output .= $plugin_defaults_values[$i];
			}
			if ($i < count($plugin_defaults_keys) - 1) {
				$output .= ', <br />';
			}
		}
		$output .= '<br />  );<br />';
		$output .= '   echo ' . $internalfunctionname . '($atts);';
		$output .= '<br />}';
		$output .= '</pre>';
		return $output;	
	}
	function wpprs_checkifset($optionname, $optiondefault, $optionsarr) {
		return (isset($optionsarr[$optionname]) ? $optionsarr[$optionname] : $optiondefault);
	}
	function wpprs_getlinebreak() {
	  echo '<tr valign="top"><td colspan="2"></td></tr>';
	}
	function wpprs_explanationrow($msg = '') {
		echo '<tr valign="top"><td></td><td><em>' . $msg . '</em></td></tr>';
	}
	function wpprs_getimagefilename($fname = '') {
		return plugins_url(wpprs_get_path() . '/images/' . $fname);
	}
?>