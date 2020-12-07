<?php
/*
Plugin Name: Imgfly
Text Domain: imgfly
Description: Optimizing images has never been easier.
Author: Imgfly
Author URI: https://imgfly.co
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Version: 1.0.0
 */

defined('ABSPATH') or exit;

/*
 * Activate Imgfly Plugin
 */
function imgfly_activation_hook()
{
    add_option('imgfly', ['url' => 'https://imgfly.io/<your-code>']);
}
register_activation_hook(__FILE__, 'imgfly_activation_hook');

/*
 * Uninstall Imgfly Plugin
 */
function imgfly_uninstall_hook()
{
    delete_option('imgfly');
}
register_uninstall_hook(__FILE__, 'imgfly_uninstall_hook');

/*
 * Render settings page
 */
function imgfly_settings_page()
{
    $options = wp_parse_args(
        get_option('imgfly'), ['url' => '']
    );

    ?>
    <div class="wrap">
        <h2>
            Imgfly <?php echo __('Settings') ?>
        </h2>

        <form method="post" action="options.php">
            <?php settings_fields('imgfly')?>

            <table class="form-table">

                <tr valign="top">
                    <th scope="row">
                        <?php _e("Your imgfly.io URL", "imgfly");?>
                    </th>
                    <td>
                        <fieldset>
                            <label for="imgfly_url">
                                <input type="text" name="imgfly[url]" id="imgfly_url" value="<?php echo (isset($options['url']) ? $options['url'] : '') ?>" size="64" class="regular-text code" />
                            </label>

                            <p class="description">
                                You can find this URL in your <a target="_blank" href="https://imgfly.co/dashboard">Imgfly Dashboard</a>.
                            </p>
                            <p class="description">
                                Example, of how it looks like: <code>https://imgfly.io/&lt;your-code&gt;</code>
                            </p>
                        </fieldset>
                    </td>
                </tr>

            </table>

            <?php submit_button();?>
        </form>
    </div>
    <?php
}
function imgfly_add_settings_page()
{
    add_options_page('imgfly', 'imgfly', 'manage_options', 'imgfly', 'imgfly_settings_page');
}
add_action('admin_menu', 'imgfly_add_settings_page');

/*
 * Validate settings submission
 */
function imgfly_validate_settings($data)
{
    return ['url' => rtrim(esc_url($data['url']), "/")];
}
function imgfly_register_settings()
{
    register_setting('imgfly', 'imgfly', 'imgfly_validate_settings');
}
add_action('admin_init', 'imgfly_register_settings');

/*
 * Add settings link
 */
function imgfly_add_action_link($data)
{
    if (!current_user_can('manage_options')) {
        return $data;
    }

    return array_merge($data, [
        sprintf('<a href="%s">%s</a>', add_query_arg(['page' => 'imgfly'], admin_url('options-general.php')), __("Settings")),
    ]);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'imgfly_add_action_link');

/*
 * Rewrite URLs on the website
 */
function imgfly_rewrite()
{
    $options = get_option('imgfly');
    $replaceurl = isset($options['url']) ? $options['url'] : '';

    if (!$replaceurl || get_option('home') == $replaceurl) {
        return;
    }

    $url = get_site_url();
    $url = str_replace('https', '', $url);
    $url = str_replace('http', '', $url);
    $url = str_replace('://', '', $url);
    $regex = '/(?<=[\("\'\s])(?:(?:https?:|)\/\/' . $url . ')?(\/wp-content\/[^\("\'\s]+?[^\/]\.(?:png|jpg|jpeg))(?=["\'\)\s])/i';

    ob_start(function ($html) use ($regex, $replaceurl) {
        return preg_replace_callback($regex, function ($matches) use ($replaceurl) {
            return $replaceurl . $matches[1];
        }, $html);
    });
}
add_filter('template_redirect', 'imgfly_rewrite', 100);

spl_autoload_register('imgfly_autoload');
function imgfly_autoload($class)
{
    if (in_array($class, ['Imgfly'])) {
        require_once sprintf('%s/%s.class.php', dirname(__FILE__), strtolower($class));
    }
}
