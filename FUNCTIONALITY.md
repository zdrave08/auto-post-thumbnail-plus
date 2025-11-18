# Auto Post Thumbnail Plus - Funkcionalnost

## B. Funkcionalnost na save_post + wp_insert_post ✅

### Hookovi
Plugin se aktivira na sledećim WordPress hook-ovima:
- `save_post` - kada se post sačuva
- `publish_post` - kada se post objavi
- `wp_insert_post` - kada se post kreira programski (npr. preko importa)

### Validacija - Plugin radi SAMO ako:

#### ✅ Nema featured image-a
```php
if (has_post_thumbnail($post_id)) {
    return; // Preskače ako već ima featured image
}
```

#### ✅ Nije revision, nije attachment
```php
// Skip autosaves and revisions
if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
    return;
}

// Skip attachments
if ('attachment' === get_post_type($post_id)) {
    return;
}
```

#### ✅ Content ima `<img>` ili default image je podešen
```php
// Za "First image" mod - proverava da li ima <img> tag
if ('first_image' === $mode) {
    if (!$post->post_content || false === strpos($post->post_content, '<img')) {
        return;
    }
}

// Za "Default image" mod - proverava da li je podešena default slika
elseif ('default_image' === $mode) {
    $default_image_id = isset($settings['default_image_id']) ? absint($settings['default_image_id']) : 0;
    if (!$default_image_id) {
        return;
    }
}
```

## Ako je izabran First Image mod:

### 1. Skeniranje post_content
```php
$content = $post->post_content;
```

### 2. Regex za pronalaženje slike
```php
// Koristi tačno navedeni regex pattern
preg_match('/<img[^>]+src=["\']([^"\']+)/i', $content, $matches)
```

### 3. Uzima prvu sliku
Plugin prvo proverava:
1. **wp-image-{id} klasu** - ako postoji, direktno uzima attachment ID
```php
if (preg_match('/wp-image-(\d+)/i', $content, $matches)) {
    $image_id = absint($matches[1]);
    if (wp_attachment_is_image($image_id)) {
        return $image_id;
    }
}
```

2. **src URL iz <img> taga**
```php
if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $content, $matches)) {
    $image_url = $matches[1];
    // Dalja obrada...
}
```

### 4. Provera da li je URL eksterni
```php
private function is_external_url($url) {
    $site_url = site_url();
    $parsed_site = parse_url($site_url);
    $parsed_url = parse_url($url);
    
    if (!isset($parsed_url['host'])) {
        return false; // Relativni URL
    }
    
    return $parsed_url['host'] !== $parsed_site['host'];
}
```

### 5a. Ako je URL lokalni (sa istog sajta)
```php
$image_id = attachment_url_to_postid($image_url);

if ($image_id && wp_attachment_is_image($image_id)) {
    set_post_thumbnail($post_id, $image_id);
}
```

### 5b. Ako je URL eksterni → Download u Media Library
```php
private function download_external_image($url, $post_id = 0) {
    // 1. Validacija URL-a
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // 2. Uključivanje potrebnih WordPress fajlova
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // 3. Download slike u privremeni fajl
    $tmp = download_url($url);
    
    if (is_wp_error($tmp)) {
        return false;
    }
    
    // 4. Priprema fajla
    $file_array = array();
    $file_array['name'] = basename($url);
    
    // Uklanjanje query string-ova iz imena
    $file_array['name'] = preg_replace('/\?.*/', '', $file_array['name']);
    
    // Provera ekstenzije
    if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file_array['name'])) {
        $file_type = wp_check_filetype($tmp);
        if ($file_type['ext']) {
            $file_array['name'] .= '.' . $file_type['ext'];
        }
    }
    
    $file_array['tmp_name'] = $tmp;
    
    // 5. Upload u Media Library
    $attachment_id = media_handle_sideload($file_array, $post_id);
    
    // 6. Čišćenje privremenog fajla
    if (is_wp_error($attachment_id)) {
        @unlink($file_array['tmp_name']);
        return false;
    }
    
    return $attachment_id; // Vraća ID novog attachmenta
}
```

### 6. Postavlja je kao featured image
```php
if ($image_id) {
    set_post_thumbnail($post_id, $image_id);
}
```

## Ako je izabran Default Image mod:

### Odmah setuje featured image
```php
// Direktno setovanje bez dodatne obrade
$default_image_id = isset($settings['default_image_id']) ? absint($settings['default_image_id']) : 0;

if ($default_image_id && wp_attachment_is_image($default_image_id)) {
    set_post_thumbnail($post_id, $default_image_id);
}
```

## Sačuvavanje podešavanja

Koristi WordPress Options API:
```php
update_option('aptp_settings', [
    'enabled' => bool,              // true/false
    'mode' => string,               // 'first_image' ili 'default_image'
    'default_image_id' => int       // Attachment ID (NE URL!)
]);
```

## Tok izvršavanja

```
1. WordPress hook aktivira plugin (save_post/publish_post/wp_insert_post)
   ↓
2. Provera: Da li je plugin uključen?
   ↓
3. Provera: Post type = 'post'?
   ↓
4. Provera: Nema featured image?
   ↓
5. Provera: Nije revision/autosave/attachment?
   ↓
6. Provera sadržaja:
   - First Image mod: Ima li <img> u content?
   - Default Image mod: Da li je default image podešen?
   ↓
7a. First Image mod:
    → Skenira content
    → Pronalazi prvi <img> tag (regex)
    → Izvlači URL
    → Provera: Lokalni ili eksterni URL?
    → Ako lokalni: attachment_url_to_postid()
    → Ako eksterni: download_external_image()
    → set_post_thumbnail()
    
7b. Default Image mod:
    → Uzima default_image_id iz settings
    → set_post_thumbnail()
   ↓
8. ✅ Featured image postavljen!
```

## Korišćene WordPress funkcije

| Funkcija | Namena |
|----------|--------|
| `has_post_thumbnail($post_id)` | Proverava da li post ima featured image |
| `set_post_thumbnail($post_id, $attachment_id)` | Postavlja featured image |
| `wp_is_post_revision($post_id)` | Proverava da li je revision |
| `wp_is_post_autosave($post_id)` | Proverava da li je autosave |
| `attachment_url_to_postid($url)` | Konvertuje URL u attachment ID |
| `wp_attachment_is_image($id)` | Proverava da li je attachment slika |
| `download_url($url)` | Download eksternog fajla |
| `media_handle_sideload($file, $post_id)` | Upload fajla u Media Library |
| `get_option('aptp_settings')` | Čita podešavanja |
| `update_option('aptp_settings', $value)` | Čuva podešavanja |

## Prednosti implementacije

✅ **Podrška za eksterne slike** - Automatski download i upload u Media Library  
✅ **Tri hook pointa** - Pokriva sve scenarije kreiranja posta  
✅ **Detaljna validacija** - Sprečava greške i nepotrebno procesiranje  
✅ **Čisti kod** - Lako održavanje i proširivanje  
✅ **WordPress standard** - Koristi core WordPress funkcije  
✅ **Error handling** - Bezbedno upravljanje greškama  

---

## C. Napredne korisne ali low-effort opcije ✅

### 1. Skip if post type is page

**Implementacija:**
```php
// Nova opcija u settings
$settings['skip_pages'] = true; // Default: true

// Provera u process_featured_image funkciji
if (isset($settings['skip_pages']) && $settings['skip_pages'] && 'page' === $post_type) {
    $this->debug_log("Skipping page (post ID: {$post_id})", $settings);
    return;
}
```

**Korisnost:** Mnogi korisnici žele automatsko postavljanje featured image-a samo za Blog (post), ne i za stranice (page).

---

### 2. Skip if post already has thumbnail

**Status:** ✅ Već implementirano kao default behavior od samog početka!

```php
// Provera u process_featured_image funkciji
if (has_post_thumbnail($post_id)) {
    $this->debug_log("Post {$post_id} already has featured image", $settings);
    return;
}
```

**Logika:** Plugin nikada neće prepisati postojeći featured image. Ovo je sigurnosna mera i očekivano ponašanje.

---

### 3. Retro-active bulk apply

**Dugme u admin panelu:**
```
→ "Apply auto-featured-image to existing posts without featured image"
```

**Implementacija:**

#### Frontend (admin.php)
```php
public function render_bulk_apply_field() {
    echo '<button type="button" class="button button-secondary" id="aptp_bulk_apply_button">';
    echo __('Apply to Existing Posts Without Featured Image', 'auto-post-thumbnail-plus');
    echo '</button>';
    echo ' <span id="aptp_bulk_apply_status"></span>';
}
```

#### Backend (AJAX handler)
```php
public function ajax_bulk_apply() {
    // Provera nonce i permissions
    check_ajax_referer('aptp_bulk_apply_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        return;
    }
    
    // WP_Query za postove bez featured image
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_thumbnail_id',
                'compare' => 'NOT EXISTS'
            )
        )
    );
    
    // Uključi pages ako je skip_pages = false
    if (!isset($settings['skip_pages']) || !$settings['skip_pages']) {
        $args['post_type'] = array('post', 'page');
    }
    
    $query = new WP_Query($args);
    
    // Procesiranje
    $processed = 0;
    $success = 0;
    $skipped = 0;
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $post = get_post($post_id);
            
            // Procesiranje svakog posta
            $this->set_featured_image($post_id, $post, $settings);
            
            if (has_post_thumbnail($post_id)) {
                $success++;
            } else {
                $skipped++;
            }
        }
        wp_reset_postdata();
    }
    
    // Vraćanje rezultata
    wp_send_json_success(array(
        'message' => sprintf('Processed %d posts: %d successful, %d skipped', 
                            $processed, $success, $skipped),
        'processed' => $processed,
        'success' => $success,
        'skipped' => $skipped
    ));
}
```

#### JavaScript (admin.js)
```javascript
$('#aptp_bulk_apply_button').on('click', function(e) {
    e.preventDefault();
    
    // Potvrda
    if (!confirm('This will process all existing posts. Continue?')) {
        return;
    }
    
    // AJAX poziv
    $.ajax({
        url: aptpAjax.ajaxurl,
        type: 'POST',
        data: {
            action: 'aptp_bulk_apply',
            nonce: aptpAjax.nonce
        },
        success: function(response) {
            if (response.success) {
                $status.html('✓ ' + response.data.message);
            }
        }
    });
});
```

**Korisnost:**
- Omogućava retroaktivnu primenu na postojeće postove
- Koristan prilikom aktivacije plugina na sajtu sa postojećim sadržajem
- Jednim klikom procesira sve postove bez featured image-a

---

### 4. Log u debug modu (toggle)

**Implementacija:**

#### Opcija u settings
```php
$settings['debug_mode'] = false; // Default: false

// Checkbox u admin panelu
echo '<label for="aptp_debug_mode">';
echo '<input type="checkbox" name="aptp_settings[debug_mode]" id="aptp_debug_mode" value="1">';
echo ' Enable debug logging';
echo '</label>';
```

#### Debug log funkcija
```php
private function debug_log($message, $settings = null) {
    if (!$settings) {
        $settings = get_option('aptp_settings', array('debug_mode' => false));
    }
    
    // Log samo ako je debug_mode uključen I WP_DEBUG je true
    if (isset($settings['debug_mode']) && $settings['debug_mode'] 
        && defined('WP_DEBUG') && WP_DEBUG) {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[Auto Post Thumbnail Plus] ' . $message);
        }
    }
}
```

#### Primeri logovanja
```php
// U process_featured_image
$this->debug_log("Processing post {$post_id} with mode: {$mode}", $settings);
$this->debug_log("Skipping page (post ID: {$post_id})", $settings);

// U set_featured_image
$this->debug_log("Set featured image {$image_id} for post {$post_id}", $settings);

// U get_first_image_from_content
$this->debug_log("Found image URL: {$image_url} for post {$post_id}", $settings);
$this->debug_log("Image URL is external, attempting download", $settings);

// U download_external_image
$this->debug_log("Successfully downloaded external image: {$url}", $settings);
```

**Korisnost:**
- Za WordPress tim i testere koji žele da prate šta plugin radi
- Lako debugovanje problema
- Detaljno logovanje svakog koraka procesiranja
- Ne utiče na performanse kada je isključeno

**Gde se logovi nalaze:**
```
wp-content/debug.log
```

**Potrebni uslovi za logovanje:**
1. `debug_mode` mora biti `true` u plugin settings
2. `WP_DEBUG` mora biti `true` u `wp-config.php`
3. `WP_DEBUG_LOG` mora biti `true` u `wp-config.php`

---

## Sažetak novih opcija

| Opcija | Default | Tip | Opis |
|--------|---------|-----|------|
| **skip_pages** | `true` | checkbox | Preskače stranice (page), procesira samo blog postove |
| **debug_mode** | `false` | checkbox | Omogućava detaljno logovanje u debug.log |
| **Bulk Apply** | - | button | Retroaktivno primenjuje featured images na sve postojeće postove |
| **Skip existing** | ✅ | built-in | Automatski preskače postove koji već imaju featured image |

---

**Implementirano:** 18. novembar 2025.  
**Status:** ✅ Sve tražene funkcionalnosti su implementirane i testirane
