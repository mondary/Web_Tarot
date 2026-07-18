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
- **Offline**: content embedded in `data.js`, works by double-clicking `index.html`
- **Accessible**: keyboard navigation (Esc, arrows) and touch support

## 🧠 Usage

1. Open the landing page: a carousel presents the 5 families.
2. Click an entry card to open a family grid (e.g. The Fool → Major Arcana).
3. Click a card to display its detailed sheet.
4. In the sheet, navigate with the ← / → arrows or the loop bar at the bottom: the path chains through all families seamlessly.

## ⚙️ Settings

Per-family accent colors and the global palette are defined via CSS variables in `index.html` (the `:root` block). The card mat uses `--mat` (`#ffffff`).

## 🧾 Shortcuts

| Key | Action |
|-----|--------|
| `←` / `→` | Previous / next card (detail view) |
| `Esc` | Back to the previous view |
| Wheel / drag | Horizontal scrolling on the landing carousel |

## 📦 Build & Package

The sheet content (`.md`) is embedded into `data.js` via a generator script.

```bash
node build_data.js
```

This reads `website/cards/*.md`, generates `data.js` (~515 KB), and logs a confirmation. Run it after editing any `.md` file.

No bundling step: the site ships as vanilla HTML/CSS/JS.

## 🧪 Installation

```bash
git clone <repo-url>
cd Web_Tarot
node build_data.js      # regenerate data.js (optional, already committed)
open index.html         # or serve via a local server
```

To run a local server:

```bash
python3 -m http.server 8000
# then open http://localhost:8000
```

## 📋 See the [CHANGELOG](CHANGELOG.md) for full history.

Current version: `🔥v1.2026.1`

## 🔗 Links

- **Card content source**: [Vivre Intuitif](https://vivre-intuitif.com/apprendre-le-tarot/)
- **Illustrations**: Rider-Waite-Smith Tarot — public domain
- **Typography**: [Cormorant Garamond](https://fonts.google.com/specimen/Cormorant+Garamond), [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans), [DM Mono](https://fonts.google.com/specimen/DM+Mono)

---

## ⚖️ Attribution

The code and design of this project are MIT-licensed (see `LICENSE`).
The descriptive texts of the cards are adapted from [Vivre Intuitif](https://vivre-intuitif.com) and remain the property of their authors. The Rider-Waite-Smith Tarot illustrations are in the public domain.
