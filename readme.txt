=== Auto Post Thumbnail Plus ===
Contributors: bitwisesoftware
Tags: featured image, auto thumbnail, first image, default image, post thumbnail
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically set featured images for posts - use first image from content or default image. Fix missing post thumbnails instantly!

== Description ==

**Auto Post Thumbnail Plus** automatically sets featured images for posts that don't have one. Perfect for fixing missing post thumbnails and ensuring all your content has beautiful featured images.

= 🎯 Key Features =

* **Automatic Featured Image** - Automatically assign featured images when posts are saved or published
* **First Image Detection** - Use the first image from post content as featured image
* **Default Image Option** - Set a fallback default image for posts without images
* **External Image Download** - Automatically downloads and imports external images to your Media Library
* **Bulk Apply Tool** - Retroactively fix all existing posts without featured images with one click
* **Smart & Safe** - Never overwrites existing featured images
* **Skip Pages Option** - Apply only to blog posts, not pages
* **Debug Logging** - Detailed logging for troubleshooting (optional)

= 💡 Perfect For =

* **Content Importers** - Automatically set featured images during import
* **Existing Sites** - Bulk fix all posts missing thumbnails
* **Blog Owners** - Never worry about missing featured images again
* **SEO Optimization** - Improve social media sharing with proper thumbnails
* **Theme Requirements** - Ensure all posts meet theme thumbnail requirements

= 🚀 How It Works =

1. Enable the plugin in **Settings → Media**
2. Choose your mode:
   * **First Image** - Automatically uses first image from post content
   * **Default Image** - Uses a default fallback image you select
3. Save and forget! Featured images are set automatically

= 🎨 Two Modes =

**First Image from Post Content**
* Scans post content for images
* Detects WordPress image classes
* Downloads external images automatically
* Imports them to your Media Library
* Sets as featured image

**Default Image**
* Select one image from Media Library
* Applied to all posts without featured images
* Perfect for consistent branding
* Quick and simple setup

= 🔧 Advanced Features =

* **Bulk Apply** - Fix all existing posts without featured images
* **Skip Pages** - Only apply to blog posts, not pages
* **Debug Mode** - Enable detailed logging for developers
* **Multiple Hooks** - Works with save_post, publish_post, and wp_insert_post
* **Safe Processing** - Validates post type, revisions, and existing thumbnails

= 📥 Import & Migration Friendly =

Works seamlessly with:
* WordPress Importer
* WP All Import
* REST API post creation
* Programmatic post creation (wp_insert_post)
* Any plugin that creates posts

= 🌐 External Image Support =

One of the few plugins that automatically downloads external images!
* Detects external image URLs
* Downloads images safely
* Uploads to WordPress Media Library
* Sets as featured image
* Cleans up temporary files

= 🎯 SEO & Social Media Benefits =

* **Better Social Sharing** - All posts have Open Graph images
* **Improved CTR** - Thumbnails make posts more clickable
* **Professional Look** - No missing image placeholders
* **Theme Compatibility** - Works with any WordPress theme
* **Grid Layouts** - Perfect for magazine and blog layouts

= 🛠️ Developer Friendly =

* Clean, well-documented code
* WordPress coding standards
* Hooks and filters available
* Debug logging support
* No external dependencies
* Lightweight and fast

== Installation ==

= Automatic Installation =

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Auto Post Thumbnail Plus"
3. Click **Install Now**
4. Click **Activate**
5. Go to **Settings → Media** and configure your preferences

= Manual Installation =

1. Download the plugin zip file
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the zip file and click **Install Now**
4. Click **Activate Plugin**
5. Go to **Settings → Media** to configure

= Configuration =

1. Navigate to **Settings → Media**
2. Scroll to "Auto Featured Image Settings"
3. Enable the feature with the checkbox
4. Choose your mode (First Image or Default Image)
5. If using Default Image mode, select an image from Media Library
6. Optionally configure advanced options
7. Click **Save Changes**

== Frequently Asked Questions ==

= Does this work with existing posts? =

Yes! Use the "Bulk Apply" button to retroactively set featured images for all existing posts that don't have one.

= Will it overwrite my existing featured images? =

No! The plugin is safe and will never overwrite featured images that are already set. It only processes posts without featured images.

= Does it work with external images? =

Yes! If you choose "First Image" mode, the plugin automatically downloads external images and adds them to your Media Library.

= Can I skip pages and only process posts? =

Yes! Enable the "Skip pages" option in Advanced Options to only apply to blog posts.

= What happens if a post has no images? =

If using "First Image" mode and the post has no images, nothing happens. If using "Default Image" mode, the default image will be applied.

= Does it work with custom post types? =

Currently, the plugin only works with standard WordPress posts and pages. Custom post type support may be added in future versions.

= Will it slow down my site? =

No! The plugin is lightweight and only runs when posts are saved or published. It has minimal impact on site performance.

= Can I see what the plugin is doing? =

Yes! Enable "Debug logging" in Advanced Options. Logs are written to wp-content/debug.log when WP_DEBUG is enabled.

= Does it work with Gutenberg? =

Yes! The plugin works with both the Classic Editor and Gutenberg block editor.

= What about imported posts? =

Perfect for imports! The plugin hooks into wp_insert_post, so it works with WordPress Importer, WP All Import, and other import tools.

= Can I use both modes together? =

No, you choose one mode at a time: either "First Image" or "Default Image."

= Is there a Pro version? =

Not yet! This plugin is completely free. We may add premium features in the future based on community feedback.

== Screenshots ==

1. Simple, clean settings interface in Settings → Media
2. Choose between First Image or Default Image mode
3. Select default image from WordPress Media Library
4. Advanced options with bulk apply feature
5. Bulk apply results showing processed posts
6. Before and after - posts with automatic featured images

== Changelog ==

= 1.1.0 - November 19, 2026 =
* Requires PHP 8.0 or newer (was 7.2) and WordPress 6.0 or newer
* Tested up to WordPress 7.1
* Added the full GPLv2 license text (LICENSE)

= 1.0.0 - November 18, 2025 =
* Initial release
* Automatic featured image assignment
* First image from content mode
* Default image mode
* External image download support
* Bulk apply for existing posts
* Skip pages option
* Debug logging
* WordPress 6.8 compatibility
* PHP 7.2+ support

== Upgrade Notice ==

= 1.1.0 =
Requires PHP 8.0+ and WordPress 6.0+. Check your server's PHP version before updating.

= 1.0.0 =
Initial release of Auto Post Thumbnail Plus. Install to start automatically setting featured images!

== Additional Info ==

= Support =

For support, bug reports, or feature requests, please visit:
* GitHub: https://github.com/Bitwise-SoftwarePR/AutoPostThumbnailPlus
* WordPress.org Support Forum

= Documentation =

Detailed documentation available on GitHub repository.

= Privacy =

This plugin does not collect or transmit any user data. All processing happens locally on your server.

= Credits =

Developed with ❤️ by Bitwise Software
Website: https://bitwisesoftware.rs

== Keywords for SEO ==

Auto featured image, automatic featured image, first image as featured image, default featured image, missing post thumbnail, fix missing thumbnails, bulk set featured images, auto thumbnail, post thumbnail, featured image generator, automatic thumbnail, set featured image automatically, missing featured image, featured image from content, external image download, import featured images, featured image plugin, thumbnail automation, post image automation, auto set thumbnail, featured image tool, missing image fix, post thumbnail generator, automatic post thumbnail, featured image bulk, social media thumbnail, open graph image, WordPress featured image, auto assign featured image, featured image importer
