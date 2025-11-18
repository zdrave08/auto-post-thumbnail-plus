# Admin UI Design - Auto Featured Image Settings

## Minimalist & Clean Design ✨

### Location
**Settings → Media** (bottom of the page)

---

## UI Structure

```
┌─────────────────────────────────────────────────────────┐
│  Auto Featured Image Settings                           │
│  Automatically set featured images for posts            │
│  that don't have one.                                   │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ☐ Enable auto-featured-image                          │
│                                                          │
│      Mode:                                              │
│      ○ Use first image from post content                │
│      ○ Use default image                                │
│                                                          │
│      Default image:                                     │
│      ┌──────────────────┐                              │
│      │  [Image Preview] │  (when default mode selected)│
│      └──────────────────┘                              │
│      [Select Image] [Remove Image]                      │
│                                                          │
│      ▶ Advanced Options                                 │
│      ┌────────────────────────────────────────────────┐│
│      │ ☐ Skip pages (only apply to blog posts)       ││
│      │                                                 ││
│      │ ☐ Enable debug logging                         ││
│      │   Logs will be written to debug.log when       ││
│      │   WP_DEBUG is enabled.                         ││
│      │                                                 ││
│      │ ─────────────────────────────────────────────  ││
│      │                                                 ││
│      │ Bulk Apply:                                     ││
│      │ [Apply to Existing Posts Without Featured...] ││
│      │ Retroactively apply featured images to all     ││
│      │ existing posts that don't have one.            ││
│      └────────────────────────────────────────────────┘│
│                                                          │
│  [Save Changes]                                          │
└─────────────────────────────────────────────────────────┘
```

---

## Visual Hierarchy

### Level 1: Main Toggle
```
☑ Enable auto-featured-image
```
- **Weight:** Bold
- **Purpose:** Master on/off switch
- **Effect:** Enables/disables all other options

### Level 2: Core Options (Indented)
```
    Mode:
    ● Use first image from post content
    ○ Use default image
    
    Default image:
    [Thumbnail Preview - 150px max]
    [Select Image] [Remove Image]
```
- **Indentation:** 25px left margin
- **Disabled state:** Opacity 0.5 when main toggle is off
- **Dynamic:** Default image section only shows when "Use default image" is selected

### Level 3: Advanced Options (Collapsible)
```
    ▶ Advanced Options
    └─ (Collapsed by default)
       ├─ Skip pages checkbox
       ├─ Debug logging checkbox
       └─ Bulk apply button
```
- **Background:** Light gray (#f9f9f9)
- **Border:** 1px solid #ddd
- **Padding:** 10px
- **Rounded corners:** 3px

---

## Interactive Behaviors

### 1. Main Checkbox Toggle
```javascript
When UNCHECKED:
  → Mode settings become gray (opacity: 0.5)
  → All inputs disabled (pointer-events: none)

When CHECKED:
  → Mode settings become active (opacity: 1)
  → All inputs enabled (pointer-events: auto)
```

### 2. Mode Radio Buttons
```javascript
When "Use first image" selected:
  → Default image section HIDDEN (slideUp)

When "Use default image" selected:
  → Default image section SHOWN (slideDown)
```

### 3. Advanced Options (Details/Summary)
```javascript
Initially:
  → Collapsed (▶ Advanced Options)
  → Content hidden

When clicked:
  → Expands (▼ Advanced Options)
  → Content slides down
  → Triangle rotates 90° clockwise
```

### 4. Image Selector
```javascript
[Select Image] clicked:
  → Opens WordPress Media Library
  → User selects image
  → Preview appears (150px max-width)
  → [Remove Image] button appears

[Remove Image] clicked:
  → Preview hidden
  → Hidden input cleared
  → [Remove Image] button hidden
```

### 5. Bulk Apply Button
```javascript
[Apply to Existing...] clicked:
  → Confirmation dialog: "This will process all existing posts. Continue?"
  → If YES:
    - Button disabled
    - Status shows: "Processing..."
    - AJAX request sent
    - On success: "✓ Processed X posts: Y successful, Z skipped"
    - Button re-enabled after 3 seconds
```

---

## Color Scheme

| Element | Color | Purpose |
|---------|-------|---------|
| Success | `#46b450` | Bulk apply success |
| Error | `#dc3232` | Bulk apply error |
| Processing | `#0073aa` | Bulk apply in progress |
| Background (advanced) | `#f9f9f9` | Advanced options box |
| Border | `#ddd` | Borders and separators |
| Text | Default WP | Consistency with WordPress |

---

## Spacing

```css
Section padding:     0
Checkbox margin:     5px 0
Radio margin:        5px 0 15px 0
Image preview:       10px 0
Advanced box:        20px top, 10px all
Description:         Tight (WordPress default)
```

---

## Responsive Behavior

```css
Max-width: 600px
  → Keeps form compact and readable
  → Doesn't span entire screen on large monitors
  → Maintains WordPress admin style
```

---

## Accessibility

✅ **Keyboard Navigation:**
- Tab through all inputs
- Space to toggle checkboxes
- Enter to submit form

✅ **Screen Readers:**
- All inputs have labels
- `<details>` and `<summary>` are semantic HTML
- Description text properly associated

✅ **Visual Indicators:**
- Focus outlines maintained
- Disabled state clearly visible
- Success/error colors distinguishable

---

## WordPress Style Consistency

Uses standard WordPress admin classes:
- `.button` - Standard button
- `.button-secondary` - Secondary action button
- `.description` - Help text styling
- Native form table styles
- WordPress color palette

---

## Comparison: Before vs After

### Before (Multiple Sections)
```
┌───────────────────────────────┐
│ Automatically Set Featured... │
│ ☐ Checkbox                    │
├───────────────────────────────┤
│ Choose Mode                   │
│ ○ Radio 1                     │
│ ○ Radio 2                     │
├───────────────────────────────┤
│ Default Featured Image        │
│ [Button]                      │
├───────────────────────────────┤
│ Advanced Options              │
│ ☐ Checkbox 1                  │
│ ☐ Checkbox 2                  │
├───────────────────────────────┤
│ Bulk Apply                    │
│ [Button]                      │
└───────────────────────────────┘
```
- 5 separate rows
- Lots of visual noise
- Unclear hierarchy

### After (Single Compact Section)
```
┌───────────────────────────────┐
│ Auto Featured Image Settings  │
│                               │
│ ☐ Enable                      │
│   ├─ Mode                     │
│   ├─ Default image            │
│   └─ ▶ Advanced Options       │
│      └─ (hidden initially)    │
└───────────────────────────────┘
```
- 1 clean section
- Clear hierarchy
- Progressive disclosure
- Minimal visual footprint

---

**Design Principle:** "Start simple, reveal complexity on demand"

- **Essential options:** Immediately visible
- **Advanced options:** Tucked away but accessible
- **Power features:** Behind one click (Advanced Options)

This creates a **low barrier to entry** while maintaining **full functionality** for power users.
