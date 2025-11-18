# Auto Post Thumbnail Plus

A WordPress plugin that automatically sets featured images for posts that don't have one.

## Features

✅ **Automatically Set Featured Images** - No more posts without featured images

🎯 **Two Modes:**
- Use first image from post content
- Use a default fallback image

🎨 **Easy Configuration** - Simple settings in WordPress Media settings page

📦 **Media Library Integration** - Select default image using built-in WordPress media picker

## Installation

1. Download or clone this repository
2. Upload the `auto-post-thumbnail-plus` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **Settings → Media** and scroll to "Auto Post Thumbnail Plus" section
5. Configure your preferences

## Configuration

### Settings Location
Navigate to **Settings → Media** in your WordPress admin panel. Scroll down to find the "Auto Post Thumbnail Plus" section.

### Available Options

#### 1. Enable/Disable
- **Checkbox:** Automatically set featured image if missing
- When enabled, the plugin will automatically assign a featured image to posts that don't have one

#### 2. Choose Mode
Select how you want featured images to be assigned:

**Option A: Use first image from post content**
- Plugin searches for the first image in the post content using regex: `/<img[^>]+src=["']([^"']+)/i`
- First checks for `wp-image-{id}` class to get attachment ID directly
- If not found, extracts the image URL from the `src` attribute
- **External images:** If the image URL is external (not from your site), it automatically downloads the image to your Media Library
- Automatically assigns the image (or downloaded image) as the featured image

**Option B: Use default image**
- Uses a predefined default image for all posts
- You must select a default image using the media picker

#### 3. Default Featured Image Selector
- **Button:** Select Image
- Opens WordPress Media Library
- Choose any image from your media library
- The plugin stores the attachment ID (not the URL)
- Preview of selected image is shown
- **Remove Image** button to clear the selection

#### 4. Advanced Options
**Skip pages (only apply to blog posts)**
- Checkbox option to skip pages and only process blog posts
- Default: **Enabled** (only processes posts, not pages)
- Useful when you only want featured images on your blog

**Enable debug logging**
- Checkbox option to enable detailed logging
- Logs are written to `wp-content/debug.log` when `WP_DEBUG` is enabled
- Useful for troubleshooting and development
- Default: **Disabled**

#### 5. Bulk Apply
- **Button:** Apply to Existing Posts Without Featured Image
- Retroactively processes all existing posts that don't have a featured image
- One-click solution for sites with existing content
- Shows progress: "Processed X posts: Y successful, Z skipped"
- Respects all plugin settings (mode, skip pages, etc.)

## How It Works

### Activation Triggers
The plugin activates on these WordPress hooks:
- **`save_post`** - When a post is manually saved in the editor
- **`publish_post`** - When a post is published
- **`wp_insert_post`** - When a post is created programmatically (e.g., via import, REST API, etc.)

### Validation Checks
The plugin only processes a post if ALL of these conditions are met:

✅ Auto-set feature is **enabled** in settings  
✅ Post does **NOT** already have a featured image (`!has_post_thumbnail($post_id)`) - **Built-in safety**  
✅ Post type is **'post'** or **'page'** (respects skip_pages setting)  
✅ Post is **NOT a revision** (`!wp_is_post_revision($post_id)`)  
✅ Post is **NOT an attachment** (`post_type !== 'attachment'`)  
✅ Post is **NOT an autosave** (`!wp_is_post_autosave($post_id)`)  
✅ **Content validation:**
   - **First Image mode:** Post content contains `<img>` tags
   - **Default Image mode:** A default image is configured in settings

**Note:** The plugin will NEVER overwrite an existing featured image. This is a core safety feature.

### Processing Logic

#### Mode: First Image from Content
1. Scans `$post->post_content` for images
2. Uses regex pattern: `/<img[^>]+src=["']([^"']+)/i` to extract the first image
3. Tries to get attachment ID from `wp-image-{id}` class (if present)
4. Extracts the `src` URL from the first `<img>` tag
5. **If URL is local (same domain):**
   - Uses `attachment_url_to_postid()` to get the attachment ID
   - Sets it as featured image with `set_post_thumbnail()`
6. **If URL is external (different domain):**
   - Downloads the image using `download_url()`
   - Uploads it to Media Library with `media_handle_sideload()`
   - Sets the newly uploaded image as featured image
7. If successful, calls `set_post_thumbnail($post_id, $attachment_id)`

#### Mode: Default Image
1. Retrieves the default image attachment ID from settings
2. Validates that the attachment exists and is an image
3. Directly calls `set_post_thumbnail($post_id, $attachment_id)`
4. ✅ Done - quick and simple!

### External Image Download Process
When an external image is detected:
1. Validates the URL with `filter_var($url, FILTER_VALIDATE_URL)`
2. Downloads the file to a temporary location
3. Extracts filename from URL (removes query strings)
4. Determines file type from extension or MIME type
5. Uploads to WordPress Media Library and associates with the post
6. Returns the new attachment ID for use as featured image

## Technical Details

### Settings Storage
Settings are stored in the WordPress options table as an array:
```php
array(
    'enabled' => bool,              // Enable/disable auto-set
    'mode' => string,               // 'first_image' or 'default_image'
    'default_image_id' => int,      // Attachment ID of default image
    'skip_pages' => bool,           // Skip pages (only apply to posts)
    'debug_mode' => bool            // Enable debug logging
)
```

### Hooks Used
- **`save_post`** - Triggers when a post is manually saved
- **`publish_post`** - Triggers when a post is published
- **`wp_insert_post`** - Triggers when a post is created programmatically
- **`wp_ajax_aptp_bulk_apply`** - AJAX handler for bulk apply feature
- **`admin_menu`** - Adds settings to Media page
- **`admin_init`** - Registers settings
- **`admin_enqueue_scripts`** - Loads admin assets

### Key Functions
- **`set_post_thumbnail($post_id, $attachment_id)`** - Sets the featured image
- **`attachment_url_to_postid($url)`** - Converts local image URL to attachment ID
- **`wp_attachment_is_image($id)`** - Verifies attachment is an image
- **`download_url($url)`** - Downloads external image to temp file
- **`media_handle_sideload($file_array, $post_id)`** - Uploads file to Media Library
- **`has_post_thumbnail($post_id)`** - Checks if post already has featured image
- **`wp_is_post_revision($post_id)`** - Checks if post is a revision
- **`wp_is_post_autosave($post_id)`** - Checks if post is an autosave
- **`error_log($message)`** - Writes debug logs when debug mode is enabled

### Debug Logging
To enable debug logging:
1. Enable **"Enable debug logging"** in plugin settings
2. Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```
3. Logs will be written to `wp-content/debug.log`

## File Structure

```
auto-post-thumbnail-plus/
├── auto-post-thumbnail-plus.php    # Main plugin file
├── assets/
│   ├── css/
│   │   └── admin.css               # Admin styles
│   └── js/
│       └── admin.js                # Media picker & bulk apply
├── README.md                       # Documentation
└── FUNCTIONALITY.md                # Technical documentation (Serbian)
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher

## Support

For issues, questions, or contributions, visit: [GitHub Repository](https://github.com/Bitwise-SoftwarePR/AutoPostThumbnailPlus)

## License

This plugin is licensed under the GPL v2 or later.

## Credits

**Made with ❤️ for the WordPress community by Bitwise Software**

---

**Version:** 1.0.0  
**Last Updated:** November 2025
