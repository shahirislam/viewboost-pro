<?php
/*
Plugin Name: ViewBoost Pro
Description: Boost post engagement with configurable view multipliers - <a href="https://github.com/shahirislam/viewboost-pro">View on GitHub</a>
Version: 1.1
Author: Shahir Islam
Author URI: https://shahirislam.me
Donate link: https://ko-fi.com/shahirislam
Text Domain: viewboost-pro
*/

function get_post_views($post_id) {
    $count = get_post_meta($post_id, '_post_views_count', true);
    if (empty($count)) {
        delete_post_meta($post_id, '_post_views_count');
        add_post_meta($post_id, '_post_views_count', '0');
        return 0;
    }
    return $count;
}

function set_post_views($post_id, $count) {
    update_post_meta($post_id, '_post_views_count', $count);
}

function increment_post_views($post_id) {
    $count = get_post_views($post_id);
    $count++;
    set_post_views($post_id, $count);
}

function calculate_fake_views($post_id) {
    $original_views = get_post_views($post_id);
    $multiplier = get_option('fake_view_multiplier', 10);
    if (get_option('enable_fake_views', '1') == '1') {
        return $original_views * $multiplier;
    } else {
        return $original_views;
    }
}

function display_fake_views($content) {
    if (is_single()) {
        global $post;
        increment_post_views($post->ID);
        $fake_views = calculate_fake_views($post->ID);
        $content .= '<p>Viewed ' . $fake_views . ' times</p>';
    }
    return $content;
}
add_filter('the_content', 'display_fake_views');

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pvm_add_plugin_settings_link');
function pvm_add_plugin_settings_link($links) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url('options-general.php?page=fake-view-counter'),
        __('Settings')
    );

    $contribute_link = sprintf(
        '<a href="%s" target="_blank" class="contribute-link">%s</a>',
        esc_url('https://github.com/shahirislam/viewboost-pro'),
        __('Contribute')
    );

    array_splice($links, 1, 0, [$settings_link, $contribute_link]);
    
    return $links;
}

function fake_view_counter_menu() {
    add_options_page(
        'View Boost - Pro Settings',
        'View Boost - Pro',
        'manage_options',
        'fake-view-counter',
        'fake_view_counter_settings_page'
    );
}
add_action('admin_menu', 'fake_view_counter_menu');

function fake_view_counter_settings_page() {
    ?>
    <div class="wrap">
        <h2>View Boost - Pro Settings</h2>
        <form method="post" action="options.php">
            <?php 
            settings_fields('fake-view-counter-settings-group'); 
            do_settings_sections('fake-view-counter-settings-group'); 
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Enable View Boost</th>
                    <td>
                        <select name="enable_fake_views">
                            <option value="1" <?php selected(get_option('enable_fake_views', '1'), '1'); ?>>Enabled</option>
                            <option value="0" <?php selected(get_option('enable_fake_views', '1'), '0'); ?>>Disabled (Show Original)</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Multiplication Factor</th>
                    <td><input type="number" name="fake_view_multiplier" value="<?php echo esc_attr(get_option('fake_view_multiplier', 10)); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <p style="text-align: center; color: #666; margin-top: 20px;">
            Developed with ❤️ by <a href="https://shahirislam.me" target="_blank">Shahir Islam</a>
            <a href="https://github.com/shahirislam/viewboost-pro" target="_blank" class="button button-secondary">Contribute on GitHub</a>
        </p>

        <!-- Donation section -->
        <div class="postbox" style="margin-top: 20px; padding: 15px;">
            <h3>Support the Developer</h3>
            <p>If you find this plugin useful, please consider making a donation:</p>
            <a href="https://ko-fi.com/shahirislam" target="_blank" class="button button-primary">Donate to The Developer</a>
            <p style="font-size: 0.9em; color: #666;"></p>
        </div>
    </div>
    <?php
}

function fake_view_counter_register_settings() {
    register_setting('fake-view-counter-settings-group', 'enable_fake_views');
    register_setting('fake-view-counter-settings-group', 'fake_view_multiplier');
}
add_action('admin_init', 'fake_view_counter_register_settings');
?>