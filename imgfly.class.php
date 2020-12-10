<?php

class Imgfly
{
    public static function activate()
    {
        add_option('imgfly', [
            'url' => 'https://imgfly.io/<your-code>',
            'active' => '',
        ]);
    }

    public static function uninstall()
    {
        delete_option('imgfly');
    }

    public static function rewrite()
    {
        $options = get_option('imgfly');
        if (!isset($options['active']) || $options['active'] !== '1') {
            return;
        }

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

    public static function addSettingsLink($data)
    {
        if (!current_user_can('manage_options')) {
            return $data;
        }

        return array_merge($data, [
            sprintf('<a href="%s">%s</a>', add_query_arg(['page' => 'imgfly'], admin_url('options-general.php')), __("Settings")),
        ]);
    }

    public function validateSettings($data)
    {
        return [
            'url' => rtrim(esc_url($data['url']), "/"),
            'active' => $data['active'] === '1' ? '1' : '0',
        ];
    }

    public function registerSettings()
    {
        register_setting('imgfly', 'imgfly', ['Imgfly', 'validateSettings']);
    }

    public static function addSettingsPage()
    {
        add_options_page('imgfly', 'Imgfly', 'manage_options', 'imgfly', ['Imgfly', 'settingsPage']);
    }

    public static function settingsPage()
    {
        $options = wp_parse_args(
            get_option('imgfly'), ['url' => '', 'active' => '']
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
                            <?php _e("Enable image optimization", "imgfly");?>
                        </th>
                        <td>
                            <fieldset>
                                <select name="imgfly[active]" id="imgfly_active">
                                    <option value="0" <?php if (isset($options['active']) && $options['active'] !== '1') {echo "selected";}?>>Deactivate</option>
                                    <option value="1" <?php if (isset($options['active']) && $options['active'] === '1') {echo "selected";}?>>Activate</option>
                                </select>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <?php _e("Imgfly URL", "imgfly");?>
                        </th>
                        <td>
                            <fieldset>
                                <label for="imgfly_url">
                                    <input type="text" name="imgfly[url]" id="imgfly_url" value="<?php echo (isset($options['url']) ? $options['url'] : '') ?>" size="64" class="regular-text code" />
                                </label>

                                <p class="description">
                                    Example: <code>https://imgfly.io/&lt;your-code&gt;</code>
                                </p>
                                <p class="description" style="margin-top: 8px;"><br />
                                    You can find this URL in your <a target="_blank" href="https://imgfly.co/dashboard">Imgfly dashboard</a>.<br />
                                </p>
                                <p class="description" style="margin-top: 8px;">
                                    If you don't have an Imgfly account yet, visit <a target="_blank" href="https://imgfly.co/">imgfly.co</a> to create one.
                                </p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <?php _e("Settings", "imgfly");?>
                        </th>
                        <td>
                            <fieldset>
                                <a target="_blank" class="button button-secondary" href="https://imgfly.co/dashboard">Open Imgfly Dashboard</a>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <?php submit_button()?>
            </form>
        </div>
        <?php
}
}
