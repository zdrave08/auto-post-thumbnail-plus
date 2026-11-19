<?php
/**
 * Plugin Name: Auto Post Thumbnail Plus
 * Plugin URI: https://github.com/zdrave08/auto-post-thumbnail-plus
 * Description: Automatically sets featured images for posts that don't have one - uses first image from content or a default image.
 * Version: 1.1.0
 * Author: Zdravko Lukic
 * Author URI: https://github.com/zdrave08
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: auto-post-thumbnail-plus
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('APTP_VERSION', '1.1.0');
define('APTP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('APTP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('APTP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Auto_Post_Thumbnail_Plus {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add settings link on plugins page
        add_filter('plugin_action_links_' . APTP_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_settings_page'));
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            
            // AJAX handler for bulk apply
            add_action('wp_ajax_aptp_bulk_apply', array($this, 'ajax_bulk_apply'));
        }
        
        // Core functionality hooks
        add_action('save_post', array($this, 'auto_set_featured_image'), 10, 3);
        add_action('publish_post', array($this, 'auto_set_featured_image_on_publish'), 10, 2);
        add_action('wp_insert_post', array($this, 'auto_set_featured_image_on_insert'), 10, 3);
    }
    
    /**
     * Add settings link on plugins page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . esc_url(admin_url('options-media.php#aptp-settings')) . '">' . esc_html__('Settings', 'auto-post-thumbnail-plus') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Add settings page under Settings → Media
     */
    public function add_settings_page() {
        // We'll add our settings to the existing Media settings page
        // No need to create a separate page
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Register settings
        register_setting(
            'media',
            'aptp_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array(
                    'enabled' => false,
                    'mode' => 'first_image',
                    'default_image_id' => 0,
                    'skip_pages' => true,
                    'debug_mode' => false
                )
            )
        );
        
        // Add settings section
        add_settings_section(
            'aptp_section',
            esc_html__('Auto Featured Image Settings', 'auto-post-thumbnail-plus'),
            array($this, 'render_section_description'),
            'media'
        );
        
        // Add main settings field (all-in-one)
        add_settings_field(
            'aptp_main_settings',
            '',
            array($this, 'render_main_settings_field'),
            'media',
            'aptp_section'
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? (bool) $input['enabled'] : false;
        $sanitized['mode'] = isset($input['mode']) && in_array($input['mode'], array('first_image', 'default_image')) 
            ? $input['mode'] 
            : 'first_image';
        $sanitized['default_image_id'] = isset($input['default_image_id']) ? absint($input['default_image_id']) : 0;
        $sanitized['skip_pages'] = isset($input['skip_pages']) ? (bool) $input['skip_pages'] : true;
        $sanitized['debug_mode'] = isset($input['debug_mode']) ? (bool) $input['debug_mode'] : false;
        
        return $sanitized;
    }
    
    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p id="aptp-settings" style="margin-top: 0;">' . esc_html__('Automatically set featured images for posts that don\'t have one.', 'auto-post-thumbnail-plus') . '</p>';
    }
    
    /**
     * Render main settings field (all-in-one compact UI)
     */
    public function render_main_settings_field() {
        $settings = get_option('aptp_settings', array(
            'enabled' => false,
            'mode' => 'first_image',
            'default_image_id' => 0,
            'skip_pages' => true,
            'debug_mode' => false
        ));
        
        $enabled = isset($settings['enabled']) && $settings['enabled'];
        $mode = isset($settings['mode']) ? $settings['mode'] : 'first_image';
        $image_id = isset($settings['default_image_id']) ? absint($settings['default_image_id']) : 0;
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
        $skip_pages = isset($settings['skip_pages']) && $settings['skip_pages'];
        $debug_mode = isset($settings['debug_mode']) && $settings['debug_mode'];
        
        ?>
        <div class="aptp-settings-container">
            <!-- Enable checkbox -->
            <p style="margin-top: 0;">
                <label for="aptp_enabled" style="font-weight: 600;">
                    <input type="checkbox" name="aptp_settings[enabled]" id="aptp_enabled" value="1" <?php checked($enabled, true); ?>>
                    <?php esc_html_e('Enable auto-featured-image', 'auto-post-thumbnail-plus'); ?>
                </label>
            </p>
            
            <div id="aptp-mode-settings" style="margin-left: 25px; <?php echo $enabled ? '' : 'opacity: 0.5; pointer-events: none;'; ?>">
                <!-- Mode selection -->
                <p style="margin-bottom: 5px; font-weight: 600;">
                    <?php esc_html_e('Mode:', 'auto-post-thumbnail-plus'); ?>
                </p>
                <p style="margin: 5px 0;">
                    <label>
                        <input type="radio" name="aptp_settings[mode]" value="first_image" <?php checked($mode, 'first_image'); ?>>
                        <?php esc_html_e('Use first image from post content', 'auto-post-thumbnail-plus'); ?>
                    </label>
                </p>
                <p style="margin: 5px 0 15px 0;">
                    <label>
                        <input type="radio" name="aptp_settings[mode]" value="default_image" <?php checked($mode, 'default_image'); ?>>
                        <?php esc_html_e('Use default image', 'auto-post-thumbnail-plus'); ?>
                    </label>
                </p>
                
                <!-- Default image selector -->
                <div id="aptp-default-image-section" style="<?php echo ($mode === 'default_image') ? '' : 'display: none;'; ?>">
                    <p style="margin-bottom: 5px; font-weight: 600;">
                        <?php esc_html_e('Default image:', 'auto-post-thumbnail-plus'); ?>
                    </p>
                    <div id="aptp-default-image-container">
                        <?php if ($image_url): ?>
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 style="max-width: 150px; height: auto; display: block; margin: 10px 0; border: 1px solid #ddd; padding: 5px; background: #f9f9f9;" 
                                 id="aptp-default-image-preview">
                        <?php else: ?>
                            <img src="" 
                                 style="max-width: 150px; height: auto; display: none; margin: 10px 0; border: 1px solid #ddd; padding: 5px; background: #f9f9f9;" 
                                 id="aptp-default-image-preview">
                        <?php endif; ?>
                        
                        <input type="hidden" name="aptp_settings[default_image_id]" id="aptp_default_image_id" value="<?php echo esc_attr($image_id); ?>">
                        
                        <button type="button" class="button" id="aptp_select_image_button">
                            <?php esc_html_e('Select Image', 'auto-post-thumbnail-plus'); ?>
                        </button>
                        
                        <button type="button" class="button" id="aptp_remove_image_button" style="<?php echo $image_id ? '' : 'display: none;'; ?>">
                            <?php esc_html_e('Remove Image', 'auto-post-thumbnail-plus'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Advanced options (collapsible) -->
                <details style="margin-top: 20px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 3px;">
                    <summary style="cursor: pointer; font-weight: 600; user-select: none;">
                        <?php esc_html_e('Advanced Options', 'auto-post-thumbnail-plus'); ?>
                    </summary>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                        <p>
                            <label for="aptp_skip_pages">
                                <input type="checkbox" name="aptp_settings[skip_pages]" id="aptp_skip_pages" value="1" <?php checked($skip_pages, true); ?>>
                                <?php esc_html_e('Skip pages (only apply to blog posts)', 'auto-post-thumbnail-plus'); ?>
                            </label>
                        </p>
                        <p>
                            <label for="aptp_debug_mode">
                                <input type="checkbox" name="aptp_settings[debug_mode]" id="aptp_debug_mode" value="1" <?php checked($debug_mode, true); ?>>
                                <?php esc_html_e('Enable debug logging', 'auto-post-thumbnail-plus'); ?>
                            </label>
                            <br>
                            <span class="description" style="margin-left: 24px;">
                                <?php esc_html_e('Logs will be written to debug.log when WP_DEBUG is enabled.', 'auto-post-thumbnail-plus'); ?>
                            </span>
                        </p>
                        
                        <!-- Bulk apply -->
                        <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                        <p style="margin-bottom: 5px;">
                            <strong><?php esc_html_e('Bulk Apply:', 'auto-post-thumbnail-plus'); ?></strong>
                        </p>
                        <p>
                            <button type="button" class="button button-secondary" id="aptp_bulk_apply_button">
                                <?php esc_html_e('Apply to Existing Posts Without Featured Image', 'auto-post-thumbnail-plus'); ?>
                            </button>
                            <span id="aptp_bulk_apply_status"></span>
                        </p>
                        <p class="description">
                            <?php esc_html_e('Retroactively apply featured images to all existing posts that don\'t have one.', 'auto-post-thumbnail-plus'); ?>
                        </p>
                    </div>
                </details>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Enable/disable mode settings based on main checkbox
            $('#aptp_enabled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#aptp-mode-settings').css({'opacity': '1', 'pointer-events': 'auto'});
                } else {
                    $('#aptp-mode-settings').css({'opacity': '0.5', 'pointer-events': 'none'});
                }
            });
            
            // Show/hide default image section based on mode
            $('input[name="aptp_settings[mode]"]').on('change', function() {
                if ($(this).val() === 'default_image') {
                    $('#aptp-default-image-section').slideDown();
                } else {
                    $('#aptp-default-image-section').slideUp();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on media settings page
        if ('options-media.php' !== $hook) {
            return;
        }
        
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        
        // Enqueue custom admin script
        wp_enqueue_script(
            'aptp-admin',
            APTP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            APTP_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('aptp-admin', 'aptpAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aptp_bulk_apply_nonce'),
            'processing' => esc_html__('Processing...', 'auto-post-thumbnail-plus'),
            'completed' => esc_html__('Completed!', 'auto-post-thumbnail-plus'),
            'error' => esc_html__('Error occurred', 'auto-post-thumbnail-plus')
        ));
        
        // Enqueue custom admin styles
        wp_enqueue_style(
            'aptp-admin',
            APTP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            APTP_VERSION
        );
    }
    
    /**
     * Auto set featured image on save post
     */
    public function auto_set_featured_image($post_id, $post, $update) {
        $this->process_featured_image($post_id, $post);
    }
    
    /**
     * Auto set featured image on publish
     */
    public function auto_set_featured_image_on_publish($post_id, $post) {
        $this->process_featured_image($post_id, $post);
    }
    
    /**
     * Auto set featured image on wp_insert_post
     */
    public function auto_set_featured_image_on_insert($post_id, $post, $update) {
        $this->process_featured_image($post_id, $post);
    }
    
    /**
     * Process featured image setting with all validations
     */
    private function process_featured_image($post_id, $post) {
        // Check if auto-set is enabled
        $settings = get_option('aptp_settings', array(
            'enabled' => false,
            'skip_pages' => true,
            'debug_mode' => false
        ));
        
        if (!isset($settings['enabled']) || !$settings['enabled']) {
            $this->debug_log("Auto-set is disabled in settings", $settings);
            return;
        }
        
        // Get post type
        $post_type = get_post_type($post_id);
        
        // Skip pages if option is enabled
        if (isset($settings['skip_pages']) && $settings['skip_pages'] && 'page' === $post_type) {
            $this->debug_log("Skipping page (post ID: {$post_id})", $settings);
            return;
        }
        
        // Only for posts (not attachments, etc.)
        if ('post' !== $post_type && 'page' !== $post_type) {
            $this->debug_log("Skipping non-post/page type: {$post_type} (post ID: {$post_id})", $settings);
            return;
        }
        
        // Skip if already has featured image
        if (has_post_thumbnail($post_id)) {
            $this->debug_log("Post {$post_id} already has featured image", $settings);
            return;
        }
        
        // Skip autosaves and revisions
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            $this->debug_log("Skipping autosave/revision (post ID: {$post_id})", $settings);
            return;
        }
        
        // Skip attachments
        if ('attachment' === $post_type) {
            $this->debug_log("Skipping attachment (post ID: {$post_id})", $settings);
            return;
        }
        
        // Get post object if not provided
        if (!$post instanceof WP_Post) {
            $post = get_post($post_id);
        }
        
        if (!$post) {
            $this->debug_log("Could not get post object for post ID: {$post_id}", $settings);
            return;
        }
        
        // Get mode
        $mode = isset($settings['mode']) ? $settings['mode'] : 'first_image';
        
        $this->debug_log("Processing post {$post_id} with mode: {$mode}", $settings);
        
        // Check if content has images or default image is set
        if ('first_image' === $mode) {
            // Check if content has <img> tags
            if (!$post->post_content || false === strpos($post->post_content, '<img')) {
                return;
            }
        } elseif ('default_image' === $mode) {
            // Check if default image is set
            $default_image_id = isset($settings['default_image_id']) ? absint($settings['default_image_id']) : 0;
            if (!$default_image_id) {
                return;
            }
        }
        
        // Set featured image based on mode
        $this->set_featured_image($post_id, $post, $settings);
    }
    
    /**
     * Set featured image based on settings
     */
    private function set_featured_image($post_id, $post, $settings = null) {
        if (!$settings) {
            $settings = get_option('aptp_settings', array(
                'enabled' => false,
                'mode' => 'first_image',
                'default_image_id' => 0,
                'debug_mode' => false
            ));
        }
        
        $mode = isset($settings['mode']) ? $settings['mode'] : 'first_image';
        
        if ('first_image' === $mode) {
            // Get first image from post content
            $image_id = $this->get_first_image_from_content($post->post_content, $post_id);
            
            if ($image_id) {
                set_post_thumbnail($post_id, $image_id);
                $this->debug_log("Set featured image {$image_id} for post {$post_id} (first image mode)", $settings);
            } else {
                $this->debug_log("No suitable image found in content for post {$post_id}", $settings);
            }
        } elseif ('default_image' === $mode) {
            // Use default image - directly set it
            $default_image_id = isset($settings['default_image_id']) ? absint($settings['default_image_id']) : 0;
            
            if ($default_image_id && wp_attachment_is_image($default_image_id)) {
                set_post_thumbnail($post_id, $default_image_id);
                $this->debug_log("Set default featured image {$default_image_id} for post {$post_id}", $settings);
            } else {
                $this->debug_log("Default image not configured or invalid for post {$post_id}", $settings);
            }
        }
    }
    
    /**
     * Get first image attachment ID from post content
     */
    private function get_first_image_from_content($content, $post_id = 0) {
        $settings = get_option('aptp_settings', array('debug_mode' => false));
        
        if (empty($content)) {
            $this->debug_log("Empty content for post {$post_id}", $settings);
            return false;
        }
        
        // Try to find image attachment ID from wp-image-{id} class first
        if (preg_match('/wp-image-(\d+)/i', $content, $matches)) {
            $image_id = absint($matches[1]);
            if (wp_attachment_is_image($image_id)) {
                $this->debug_log("Found image via wp-image class: {$image_id} for post {$post_id}", $settings);
                return $image_id;
            }
        }
        
        // Use the specified regex to find first image URL
        if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $content, $matches)) {
            $image_url = $matches[1];
            $this->debug_log("Found image URL in content: {$image_url} for post {$post_id}", $settings);
            
            // Check if it's a local image (already in Media Library)
            $image_id = attachment_url_to_postid($image_url);
            
            if ($image_id && wp_attachment_is_image($image_id)) {
                $this->debug_log("Image URL is local, attachment ID: {$image_id} for post {$post_id}", $settings);
                return $image_id;
            }
            
            // If URL is external, download it to Media Library
            if ($this->is_external_url($image_url)) {
                $this->debug_log("Image URL is external, attempting download: {$image_url} for post {$post_id}", $settings);
                $image_id = $this->download_external_image($image_url, $post_id);
                
                if ($image_id && wp_attachment_is_image($image_id)) {
                    return $image_id;
                }
            } else {
                $this->debug_log("Image URL is local but not found in Media Library: {$image_url} for post {$post_id}", $settings);
            }
        } else {
            $this->debug_log("No image found in content for post {$post_id}", $settings);
        }
        
        return false;
    }
    
    /**
     * Check if URL is external (not from current site)
     */
    private function is_external_url($url) {
        $site_url = site_url();
        $parsed_site = wp_parse_url($site_url);
        $parsed_url = wp_parse_url($url);
        
        // If no host in URL, it's relative (not external)
        if (!isset($parsed_url['host'])) {
            return false;
        }
        
        // Compare hosts
        return $parsed_url['host'] !== $parsed_site['host'];
    }
    
    /**
     * Download external image to Media Library
     */
    private function download_external_image($url, $post_id = 0) {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Include required WordPress files
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Download image
        $tmp = download_url($url);
        
        if (is_wp_error($tmp)) {
            return false;
        }
        
        // Get filename from URL
        $file_array = array();
        $file_array['name'] = basename($url);
        
        // Remove query strings from filename
        $file_array['name'] = preg_replace('/\?.*/', '', $file_array['name']);
        
        // If no file extension, try to get from tmp file
        if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file_array['name'])) {
            $file_type = wp_check_filetype($tmp);
            if ($file_type['ext']) {
                $file_array['name'] .= '.' . $file_type['ext'];
            }
        }
        
        $file_array['tmp_name'] = $tmp;
        
        // Upload to Media Library
        $attachment_id = media_handle_sideload($file_array, $post_id);
        
        // Clean up temp file
        if (is_wp_error($attachment_id)) {
            wp_delete_file($file_array['tmp_name']);
            $this->debug_log("Failed to upload external image: {$url}", get_option('aptp_settings', array()));
            return false;
        }
        
        $this->debug_log("Successfully downloaded and uploaded external image: {$url} (attachment ID: {$attachment_id})", get_option('aptp_settings', array()));
        
        return $attachment_id;
    }
    
    /**
     * AJAX handler for bulk apply
     */
    public function ajax_bulk_apply() {
        // Check nonce
        check_ajax_referer('aptp_bulk_apply_nonce', 'nonce');
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'auto-post-thumbnail-plus')));
            return;
        }
        
        $settings = get_option('aptp_settings', array(
            'enabled' => false,
            'mode' => 'first_image',
            'default_image_id' => 0,
            'skip_pages' => true,
            'debug_mode' => false
        ));
        
        // Build query args
        // Note: meta_query is intentionally used here for bulk processing
        // This is a manual, one-time operation triggered by admin action
        // Performance impact is acceptable as it's not run on every page load
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key' => '_thumbnail_id',
                    'compare' => 'NOT EXISTS'
                )
            )
        );
        
        // If skip_pages is false, include pages too
        if (!isset($settings['skip_pages']) || !$settings['skip_pages']) {
            $args['post_type'] = array('post', 'page');
        }
        
        $query = new WP_Query($args);
        
        $processed = 0;
        $success = 0;
        $skipped = 0;
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post = get_post($post_id);
                
                $processed++;
                
                // Check if we should process this post
                $mode = isset($settings['mode']) ? $settings['mode'] : 'first_image';
                
                if ('first_image' === $mode) {
                    // Check if content has images
                    if (!$post->post_content || false === strpos($post->post_content, '<img')) {
                        $skipped++;
                        continue;
                    }
                } elseif ('default_image' === $mode) {
                    $default_image_id = isset($settings['default_image_id']) ? absint($settings['default_image_id']) : 0;
                    if (!$default_image_id) {
                        $skipped++;
                        continue;
                    }
                }
                
                // Process the featured image
                $this->set_featured_image($post_id, $post, $settings);
                
                // Check if it was successful
                if (has_post_thumbnail($post_id)) {
                    $success++;
                    $this->debug_log("Bulk apply: Successfully set featured image for post {$post_id}", $settings);
                } else {
                    $skipped++;
                }
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success(array(
            'message' => sprintf(
                /* translators: 1: number of processed posts, 2: number of successful posts, 3: number of skipped posts */
                esc_html__('Processed %1$d posts: %2$d successful, %3$d skipped', 'auto-post-thumbnail-plus'),
                $processed,
                $success,
                $skipped
            ),
            'processed' => $processed,
            'success' => $success,
            'skipped' => $skipped
        ));
    }
    
    /**
     * Debug logging function
     */
    private function debug_log($message, $settings = null) {
        if (!$settings) {
            $settings = get_option('aptp_settings', array('debug_mode' => false));
        }
        
        // Only log if debug mode is enabled and WP_DEBUG is true
        if (isset($settings['debug_mode']) && $settings['debug_mode'] && defined('WP_DEBUG') && WP_DEBUG) {
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging, only active when user enables debug mode and WP_DEBUG is true
                error_log('[Auto Post Thumbnail Plus] ' . $message);
            }
        }
    }
}

// Initialize plugin
function aptp_init() {
    return Auto_Post_Thumbnail_Plus::get_instance();
}

// Start plugin
add_action('plugins_loaded', 'aptp_init');
