# Auto Post Thumbnail Plus

**Minimal WordPress plugin that automatically sets featured images for posts that don't have one.**

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.1.0-blue.svg)](https://github.com/zdrave08/auto-post-thumbnail-plus)
[![WordPress Compatibility](https://img.shields.io/badge/wordpress-6.0%2B-brightgreen.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/php-8.0%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPLv2-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

---

## 📋 Description

**Auto Post Thumbnail Plus** is a lightweight plugin that automatically assigns featured images to posts that don't have one. Choose between extracting the first image from content or using a default fallback image.

### Why do you need this plugin?

Featured images are **essential** for modern WordPress sites. This plugin:

✅ **Improves SEO** - Every post has a featured image for social sharing  
✅ **Saves time** - No manual featured image assignment needed  
✅ **Consistent design** - All posts have thumbnails in archives and grids  
✅ **Smart detection** - Automatically finds and downloads external images  

---

## ✨ Features

### 🎯 Core Features

- **Automatic featured image assignment** - Works on post save/publish
- **Two intelligent modes:**
  - **First Image Mode** - Extracts first image from post content (with external image download)
  - **Default Image Mode** - Uses a pre-selected fallback image
- **Theme-agnostic** - Works with **any** WordPress theme
- **Simple ON/OFF toggle** - Easy control in Settings → Media
- **External image support** - Downloads and imports external images to Media Library

### 🎁 Advanced Features

- **Skip pages** - Option to only apply to blog posts (not pages)
- **Bulk apply** - Retroactively set featured images for existing posts
- **Debug logging** - Detailed logs when WP_DEBUG is enabled
- **Smart validation** - Prevents duplicate processing and validates images
- **Media Library integration** - Seamless image picker interface

---

## 🚀 Installation

### Automatic installation (WordPress.org)

1. Go to **Plugins → Add New**
2. Search for **"Auto Post Thumbnail Plus"**
3. Click **"Install Now"** and then **"Activate"**
4. Configure in **Settings → Media**

### Manual installation

1. Download plugin files
2. Upload folder to `/wp-content/plugins/`
3. Activate plugin through **Plugins** menu
4. Go to **Settings → Media** to configure

---

## 📖 Usage

### Admin Panel

1. Go to **Settings → Media**
2. Find **"Auto Featured Image Settings"** section
3. Configure options:
   - ☑ **Enable auto-featured-image** - Main toggle
   - **Mode:** Choose between first image or default image
   - **Default image:** Select fallback image (for default mode)
4. Expand **Advanced Options** for:
   - ☑ **Skip pages** - Only apply to posts
   - ☑ **Enable debug logging** - Troubleshooting logs
   - **Bulk Apply** - Process existing posts
5. Save changes

### First Image Mode

When enabled, plugin will:

1. Check post content for `<img>` tags
2. Look for `wp-image-{id}` class (WordPress Media Library images)
3. Extract image URL from `src` attribute
4. If image is external - **download and import** to Media Library
5. Set as featured image

**Example:**

```html
<!-- This image will be automatically set as featured image -->
<img src="https://example.com/image.jpg" alt="Post image">
```

### Default Image Mode

When enabled, plugin will:

1. Use pre-selected image from Media Library
2. Apply to all posts without featured images
3. Perfect for blogs with consistent branding

### Bulk Apply

To apply featured images to **existing posts**:

1. Go to **Settings → Media**
2. Expand **"Advanced Options"**
3. Click **"Apply to Existing Posts Without Featured Image"**
4. Wait for processing (shows success/skip count)

**Bulk apply will:**
- Only process published posts without featured images
- Respect your chosen mode (first image or default)
- Skip posts without images (in first image mode)
- Process in background with AJAX

---

## 🔧 Technical Details

### WordPress Hooks

Plugin uses the following hooks:

- `save_post` - Triggers on post save
- `publish_post` - Triggers when post is published
- `wp_insert_post` - Triggers on new post creation
- `admin_init` - Registers settings
- `admin_menu` - Adds settings link
- `wp_ajax_aptp_bulk_apply` - AJAX bulk processing

### What happens on post save?

1. Plugin checks if featured image already exists
2. Validates post type (post/page)
3. Checks if plugin is enabled
4. Processes based on selected mode:
   - **First Image:** Extracts from content, downloads if external
   - **Default Image:** Applies pre-selected image
5. Sets featured image with `set_post_thumbnail()`

### External Image Download

Plugin automatically downloads external images:

1. Validates URL format
2. Downloads to temporary location
3. Uploads to WordPress Media Library
4. Attaches to post
5. Cleans up temporary files

**Supported formats:** JPG, PNG, GIF, WebP, SVG (any WordPress-supported image)

---

## 📦 File Structure

```
auto-post-thumbnail-plus/
├── auto-post-thumbnail-plus.php  # Main plugin file (~724 lines)
├── assets/
│   ├── js/
│   │   └── admin.js              # Media picker + AJAX
│   └── css/
│       └── admin.css             # Minimalist admin styling
├── languages/
│   ├── auto-post-thumbnail-plus.pot  # Translation template
│   └── README.md                 # Translation guide
├── readme.txt                    # WordPress.org readme
├── README.md                     # This documentation
├── FEATURES.md                   # Feature summary
├── FUNCTIONALITY.md              # Technical docs (Serbian)
└── UI-DESIGN.md                  # Admin UI design
```

---

## 🎨 Performance & SEO

- ⚡ **Fast** - Minimal overhead, efficient hooks
- 🪶 **Lightweight** - ~724 lines of clean code
- 🔍 **SEO friendly** - Every post has Open Graph images
- 🎯 **Smart processing** - Only processes when needed
- 🌐 **Theme-agnostic** - Works with any theme
- 📱 **Social sharing ready** - Featured images for Facebook, Twitter, LinkedIn

---

## 📋 Requirements

- **WordPress:** 6.0 or newer (tested up to 7.1)
- **PHP:** 8.0 or newer
- **MySQL:** 5.6 or newer (WordPress standard)

---

## ❓ FAQ

### Does it work with external images?

**Yes!** Plugin automatically downloads external images from post content and imports them to your Media Library.

### What happens if post already has a featured image?

Plugin **skips** posts that already have featured images. It never overwrites existing ones.

### Does it work with Gutenberg?

**Yes!** Plugin is editor-agnostic and works with Classic Editor, Gutenberg, and page builders.

### Can I bulk apply to existing posts?

**Yes!** Use the "Apply to Existing Posts Without Featured Image" button in Advanced Options.

### Does it affect pages?

By default **yes**, but you can enable "Skip pages" option to only process blog posts.

### Does it slow down the site?

**No.** Plugin only runs on post save/publish (not on page load). Minimal performance impact.

### What if post has no images?

In **first image mode**, post is skipped. In **default image mode**, default image is applied.

### Can I see what's happening?

**Yes!** Enable "Debug logging" in Advanced Options. Logs are written to `wp-content/debug.log` when `WP_DEBUG` is enabled.

### Does it work with any theme?

**Yes!** Plugin uses standard WordPress functions (`set_post_thumbnail`) which work with all themes.

### What about custom post types?

Currently only supports `post` and `page` post types (configurable with "Skip pages" option).

---

## 🌍 Translations

Plugin is translation-ready with `.pot` file included in `languages/` folder.

**Currently available:**
- English (default)

**Want to translate?**
- Use [Poedit](https://poedit.net/) to create `.po` file
- Submit translation via [GitHub](https://github.com/zdrave08/auto-post-thumbnail-plus)
- Or via [WordPress.org GlotPress](https://translate.wordpress.org/)

---

## 📄 License

GPL v2 or later - [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

## 👨‍💻 Author

**Zdravko Lukic**  
GitHub: [@zdrave08](https://github.com/zdrave08)

---

## 🐛 Support

For help:

- **GitHub Issues:** [github.com/zdrave08/auto-post-thumbnail-plus/issues](https://github.com/zdrave08/auto-post-thumbnail-plus/issues)
- **WordPress.org Support:** [wordpress.org/support/plugin/auto-post-thumbnail-plus/](https://wordpress.org/support/plugin/auto-post-thumbnail-plus/)

---

## 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first.

### Development

```bash
# Clone repository
git clone https://github.com/zdrave08/auto-post-thumbnail-plus.git

# Install in WordPress
cp -r AutoPostThumbnailPlus /path/to/wordpress/wp-content/plugins/
```

---

## 📝 Changelog

### 1.1.0 - 2026-11-19
- Requires PHP 8.0+ and WordPress 6.0+
- Tested up to WordPress 7.1
- Added the full GPLv2 license text

### 1.0.0 - 2025-11-18
- 🎉 Initial release
- ✅ Automatic featured image assignment
- ✅ Two modes: first image or default image
- ✅ External image download and import
- ✅ Bulk apply to existing posts
- ✅ Skip pages option
- ✅ Debug logging support
- ✅ Minimalist all-in-one admin UI
- ✅ WordPress 6.8 compatible
- ✅ Translation-ready with .pot file

---

**Made with ❤️ for the WordPress community**
