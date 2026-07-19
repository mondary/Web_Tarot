![Project icon](icon.png)

# Tarot Divinatoire

[🇫🇷 FR](README.md) · [🇬🇧 EN](README_en.md)

A static, immersive website to explore the 78 cards of the Rider-Waite-Smith Tarot: a family carousel landing page, a browsing grid, and a detailed sheet for each card.

## ✅ Features

- **78 cards**: 22 Major Arcana + 56 Minor Arcana (Wands, Swords, Cups, Pentacles)
- **Unified interface** in a single file: landing view, per-family grid, detail view
- **Rich sheets**: meaning, keywords (upright / reversed), interpretation, love, work, finances, guidance
- **Continuous navigation**: arrows loop through all 78 cards, crossing from one family to the next
- **Visual consistency**: a dedicated accent color per family, a white mat simulating the border of real cards
- **Offline**: content embedded in `website/data.js`, works by double-clicking `website/index.html`
- **Accessible**: keyboard navigation (Esc, arrows) and touch support

## 🧠 Usage

1. Open the landing page: a carousel presents the 5 families.
2. Click an entry card to open a family grid (e.g. The Fool → Major Arcana).
3. Click a card to display its detailed sheet.
4. In the sheet, navigate with the ← / → arrows or the loop bar at the bottom: the path chains through all families seamlessly.

## ⚙️ Settings

Per-family accent colors and the global palette are defined via CSS variables in `website/index.html` (the `:root` block). The card mat uses `--mat` (`#ffffff`).

## 🧾 Shortcuts

| Key | Action |
|-----|--------|
| `A-Z` | Opens instant search (type to filter) |
| `←` / `→` | Previous / next card (detail view) or previous/next family (grid) |
| `Esc` | Back to the previous view / close search |
| `Enter` | Open the first search result |
| `⌫` | Delete a search character |
| Wheel / drag | Horizontal scrolling on the landing carousel |

## 📦 Build & Package

The site is **self-contained**: all 78 images are embedded as WebP (data URIs) inside `website/data.js`. To deploy, simply drop `website/index.html` and `website/data.js` onto the server — no image folder to upload.

The sources (`website/cards/*.md` and `*.jpg`) produce `data.js` via a generator script.

```bash
node website/build_data.js
```

This reads `website/cards/*.md` and `*.jpg`, regenerates `website/data.js` (~7 MB, 420px WebP images included), and logs a confirmation. Requires [ImageMagick](https://imagemagick.org/) (`magick`). Run it after editing any `.md` file or image.

No bundling step: the site ships as vanilla HTML/CSS/JS.

## 🧪 Installation

```bash
git clone <repo-url>
cd Web_Tarot
# data.js is already committed (self-contained) — ready to deploy as is.
# To regenerate it after an edit:
node website/build_data.js
# then drop website/index.html + website/data.js on the FTP
open website/index.html
```

To run a local server:

```bash
python3 -m http.server 8000
# then open http://localhost:8000/website/
```

## 📋 See the [CHANGELOG](CHANGELOG.md) for full history.

Current version: `🔥v2.2026.4`

## 🔗 Links

- **Card content source**: [Vivre Intuitif](https://vivre-intuitif.com/apprendre-le-tarot/)
- **Illustrations**: Rider-Waite-Smith Tarot — public domain
- **Typography**: [Cormorant Garamond](https://fonts.google.com/specimen/Cormorant+Garamond), [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans), [DM Mono](https://fonts.google.com/specimen/DM+Mono)

---

## ⚖️ Attribution

The code and design of this project are MIT-licensed (see `LICENSE`).
The descriptive texts of the cards are adapted from [Vivre Intuitif](https://vivre-intuitif.com) and remain the property of their authors. The Rider-Waite-Smith Tarot illustrations are in the public domain.
