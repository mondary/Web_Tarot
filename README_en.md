![Project icon](icon.png)

# Tarot Divinatoire

[🇫🇷 FR](README.md) · [🇬🇧 EN](README_en.md)

An immersive website to explore the 78 cards of the Rider-Waite-Smith Tarot: a continuous grid with family intercalary cards, and a detailed sheet for each card.

## ✅ Features

- **78 cards**: 22 Major Arcana + 56 Minor Arcana (Wands, Swords, Cups, Pentacles)
- **V3 PHP**: PHP+SQLite architecture, arrow navigation, instant search
- **Flat grid**: all 78 cards in one continuous grid with SVG family intercalary cards
- **Intercalary cards**: 5 vertical tiles (original SVGs) presenting each family, clickable to the first card
- **4 views**: Classic, Immersive (slideshow), Detailed (editorial), Quick (express reading)
- **Quick Reading**: fullscreen card + Answer (Yes / No / Maybe) + Affirmation + positive keywords (FR + ES)
- **Bilingual badges**: ANSWER and Affirmation displayed on all 4 views, in French
- **Rich sheets**: meaning, keywords (upright / reversed), interpretation, love, work, finances, guidance
- **Mobile scanner**: local recognition of a Rider-Waite-Smith card through the camera, then direct opening of its page
- **Fullscreen search**: instant filtering by card name (keys A-Z or F)
- **Keyboard navigation**: `↑↓←→` in grid, `Enter` to open, `Escape` to go back, arrows in card sheet
- **Visual consistency**: dedicated accent color per family, white mat simulating real card border
- **Accessible**: keyboard navigation (Esc, arrows) and touch support

## 🧠 Usage

1. The grid displays all 78 cards with 5 intercalary cards (one per family).
2. Click an intercalary card to open the first card of that family.
3. Click a card to display its detailed sheet.
4. Navigate with `↑↓←→` arrows in the grid, `←`/`→` in the sheet.
5. Search: type a letter (A-Z) or `F` to open fullscreen search.

## ⚙️ Settings

Per-family accent colors and the global palette are defined via CSS variables in `website/index.html` (`:root` block). The card mat uses `--mat` (`#ffffff`).

## 🧾 Shortcuts

| Key | Action |
|-----|--------|
| `↑↓←→` | Select a card in the grid |
| `Enter` | Open the selected card |
| `A-Z` | Opens instant search (type to filter) |
| `←` / `→` | Previous / next card (card sheet) |
| `Esc` | Back to grid from a card sheet |
| `F` | Opens fullscreen search |
| Wheel / drag | Horizontal scrolling on the landing carousel |

## 📦 Build & Package

V3 is **PHP+SQLite**: `website/v3/index.php` + `website/v3/tarot.sqlite`.

V1/V2 is **self-contained**: all 78 images are embedded as WebP (data URIs) inside `website/assets/js/data.js`.

```bash
node scripts/build_data.js
```

This reads `website/assets/md/*.md` and `website/assets/img/cards/*.jpg`, regenerates `website/assets/js/data.js` (~7 MB, 420px WebP images included), and logs a confirmation. Requires [ImageMagick](https://imagemagick.org/) (`magick`). Run after editing any `.md` file or image.

## 🧪 Local test

The site (V6) runs from `src/website/v6/` with `launch.command` (macOS
double-click: free port, PHP, opens the browser), or from the CLI:

```bash
php -S 127.0.0.1:8765 -t src/website/v6 src/website/v6/index.php
```

To test the archived versions (V2/V3/V4/V5, extracted from the git branches), use
`scripts/tester-server.py`. Do not open an `index.html` via `file://`: the
browser blocks `fetch()` to SQLite and WebAssembly loading.

## 📋 See the [CHANGELOG](CHANGELOG.md) for full history.

Current version: `v2026.08.04`

## 🔗 Links

- **Live V3**: [mondary.design/pk/tarot3](https://mondary.design/pk/tarot3/)
- **Live V6**: [mondary.design/pk/-Games-cards/tarot6](https://mondary.design/pk/-Games-cards/tarot6/)
- **Live V1/V2**: [mondary.design/pk/tarot](https://mondary.design/pk/tarot/)
- **Card content source**: [Vivre Intuitif](https://vivre-intuitif.com/apprendre-le-tarot/)
- **Illustrations**: Rider-Waite-Smith Tarot — public domain
- **Typography**: [Cormorant Garamond](https://fonts.google.com/specimen/Cormorant+Garamond), [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans), [DM Mono](https://fonts.google.com/specimen/DM+Mono)

---

## ⚖️ Attribution

The code and design of this project are MIT-licensed (see `LICENSE`).
The descriptive texts of the cards are adapted from [Vivre Intuitif](https://vivre-intuitif.com) and remain the property of their authors. The Rider-Waite-Smith Tarot illustrations are in the public domain.
