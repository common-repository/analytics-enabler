<?php
/*
Plugin Name: Analytics Enabler
Description: This plugin will include your Google Analytics tracking code immediately before the closing [head] tag of every page.
Version: 1.1.5
Author: Brian Staruk
Author URI: http://brian.staruk.me
*/

// Close the door on anyone trying to directly access the plugin
if (!function_exists('add_action'))
{
	echo 'You shouldn\'t be here.';
	exit;
}

// This is the class that builds the options page on the wp-admin panel
class analEnable
{
    public function __construct()
    {
        if(is_admin())
        {
		    add_action('admin_menu', array($this, 'add_plugin_page'));
		    add_action('admin_init', array($this, 'page_init'));
		}
    }
	
    public function add_plugin_page()
    {
        // This page will be under "Settings"
		add_options_page('Settings Admin', 'Analytics Enabler', 'manage_options', 'analytics_settings_admin', array($this, 'create_admin_page'));
    }

    public function create_admin_page()
    {
    	?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>Settings</h2>
	    <form method="post" action="options.php">
	        <?php
                    // This prints out all hidden setting fields
		    settings_fields('analytics_options');	
		    do_settings_sections('analytics_settings_admin');
		?>
	        <?php submit_button(); ?>
	        <p><small><em>Note:</em> Your UAID can be found on the top most dashboard of your Google Analytics account. It is written in small text next to each one of your domain names, and looks something like this: UA-74368483-3</small></p>
	    </form>
	</div>
	<?php
    }
	
    public function page_init()
    {		
		register_setting('analytics_options', 'ao_fields', array($this, 'validateForm'));
		
        add_settings_section(
			'setting_section_id',
			'Analytics Enabler Settings',
			array($this, 'print_section_info'),
			'analytics_settings_admin'
		);	
		
		add_settings_field(
		    'uaid',
		    'UAID',
		    array($this, 'create_varchar_field'),
		    'analytics_settings_admin',
		    'setting_section_id'			
		);		
    }
	
    public function validateForm($input)
    {
    	if (preg_match('#^[A-Za-z0-9-]{3,20}$#s', $input['uaid']) && strlen($input['uaid']) < 50)
        {
		    $mid = $input['uaid'];
		    if (get_option('analytics_uaid') === FALSE)
		    {
				add_option('analytics_uaid', $mid);
		    }
		    else
		    {
				update_option('analytics_uaid', $mid);
		    }
		}
		else
		{
		    $mid = '';
			add_settings_error(
				'form_errors',
				esc_attr('settings_updated'),
				__('Invalid UAID'),
				'error'
			);
		}

		return $mid;
    }
	
    public function print_section_info()
    {
		print '';
    }
	
    public function create_varchar_field()
    {
        ?><input type="text" id="uaid" name="ao_fields[uaid]" value="<?=get_option('analytics_uaid');?>" /><?php
    }
}

$analEnable = new analEnable(); // Call class that adds the options to the wp-admin panel

function showAnalyticsCode()
{
	$optionID = get_option( 'analytics_uaid' );
	if (!empty($optionID))
	{
		print '<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \'' . get_option( 'analytics_uaid' ) . '\']);
  _gaq.push([\'_trackPageview\']);

  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
';
	}
}

add_action('wp_head', 'showAnalyticsCode'); // Run the function to show the analytics code

?>