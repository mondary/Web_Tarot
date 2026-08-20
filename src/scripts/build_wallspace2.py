import json
import os

with open('src/scripts/all_tarot_cards.json', 'r', encoding='utf-8') as f:
    cards = json.load(f)

cards_json_str = json.dumps(cards, ensure_ascii=False)

html_content = f'''<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>TarotSpace V2 — Plein Pot & Cartes Intégrales</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Cinzel:wght@600;700;800;900&family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet" />
  
  <style>
    /* ==========================================================================
       RESET & BASE CSS — FULLSCREEN NETFLIX / WALLSPACE STYLE
       ========================================================================== */
    *, *::before, *::after {{
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }}

    :root {{
      --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', Roboto, sans-serif;
      --font-serif: 'Cinzel', serif;
      --font-prose: 'Cormorant Garamond', Georgia, serif;

      /* Standard Tarot RWS exact aspect ratio (413 x 709) */
      --tarot-ratio: 413 / 709;

      --bg-main: #090a0f;
      --bg-surface: #11121a;
      --bg-surface-elevated: #181a26;

      --accent-hero: #e11d48;
      --accent-hero-glow: rgba(225, 29, 72, 0.45);
      --accent-gold: #f59e0b;
      --accent-amber: #d97706;

      --text-main: #ffffff;
      --text-muted: rgba(255, 255, 255, 0.7);
      --text-dim: rgba(255, 255, 255, 0.4);

      --glass-bg: rgba(255, 255, 255, 0.08);
      --glass-bg-hover: rgba(255, 255, 255, 0.16);
      --glass-border: rgba(255, 255, 255, 0.12);
      --glass-border-hover: rgba(255, 255, 255, 0.28);

      --ease-smooth: cubic-bezier(0.16, 1, 0.3, 1);
      --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    }}

    /* THEMES */
    body[data-theme="crimson"] {{ --accent-hero: #e11d48; --accent-hero-glow: rgba(225, 29, 72, 0.45); }}
    body[data-theme="space"] {{ --accent-hero: #6366f1; --accent-hero-glow: rgba(99, 102, 241, 0.45); }}
    body[data-theme="gold"] {{ --accent-hero: #f59e0b; --accent-hero-glow: rgba(245, 158, 11, 0.45); }}
    body[data-theme="azure"] {{ --accent-hero: #0ea5e9; --accent-hero-glow: rgba(14, 165, 233, 0.45); }}
    body[data-theme="emerald"] {{ --accent-hero: #10b981; --accent-hero-glow: rgba(16, 185, 129, 0.45); }}
    body[data-theme="amethyst"] {{ --accent-hero: #a855f7; --accent-hero-glow: rgba(168, 85, 247, 0.45); }}

    html, body {{
      width: 100%;
      min-height: 100vh;
      background: var(--bg-main);
      color: var(--text-main);
      font-family: var(--font-sans);
      overflow-x: hidden;
    }}

    /* Global scrollbar */
    ::-webkit-scrollbar {{
      width: 8px;
      height: 8px;
    }}
    ::-webkit-scrollbar-track {{
      background: #090a0f;
    }}
    ::-webkit-scrollbar-thumb {{
      background: rgba(255, 255, 255, 0.2);
      border-radius: 4px;
    }}
    ::-webkit-scrollbar-thumb:hover {{
      background: rgba(255, 255, 255, 0.35);
    }}

    /* ==========================================================================
       STICKY FULL-WIDTH NAVBAR (NETFLIX / WALLSPACE STYLE)
       ========================================================================== */
    .app-navbar {{
      position: sticky;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      height: 72px;
      padding: 0 44px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(9, 10, 15, 0.8);
      backdrop-filter: blur(25px) saturate(180%);
      -webkit-backdrop-filter: blur(25px) saturate(180%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      transition: background 0.3s, box-shadow 0.3s;
    }}

    .app-navbar.scrolled {{
      background: rgba(9, 10, 15, 0.96);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
    }}

    /* Brand Logo */
    .nav-brand {{
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: #ffffff;
      font-size: 22px;
      font-weight: 900;
      letter-spacing: -0.03em;
      cursor: pointer;
      user-select: none;
    }}

    .nav-brand-icon {{
      width: 38px;
      height: 38px;
      border-radius: 11px;
      background: linear-gradient(135deg, #2a0815 0%, #000000 100%);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 19px;
    }}

    /* Center Pill Navigation */
    .nav-pill-box {{
      display: flex;
      align-items: center;
      background: rgba(0, 0, 0, 0.55);
      border: 1px solid rgba(255, 255, 255, 0.12);
      padding: 5px;
      border-radius: 100px;
      gap: 4px;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);
    }}

    .nav-tab-btn {{
      background: transparent;
      border: none;
      color: var(--text-muted);
      font-family: var(--font-sans);
      font-weight: 600;
      font-size: 14px;
      padding: 8px 24px;
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.25s var(--ease-smooth);
      display: flex;
      align-items: center;
      gap: 6px;
      user-select: none;
    }}

    .nav-tab-btn:hover {{
      color: #ffffff;
    }}

    .nav-tab-btn.active {{
      background: #ffffff;
      color: #090a0f;
      font-weight: 800;
      box-shadow: 0 2px 14px rgba(0, 0, 0, 0.4);
    }}

    /* Right Action Controls */
    .nav-actions-right {{
      display: flex;
      align-items: center;
      gap: 12px;
    }}

    .nav-btn-icon {{
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.2s var(--ease-smooth);
    }}

    .nav-btn-icon:hover {{
      background: var(--glass-bg-hover);
      border-color: var(--glass-border-hover);
      transform: scale(1.08);
    }}

    .nav-pro-pill {{
      background: linear-gradient(135deg, #78350f 0%, #b45309 50%, #f59e0b 100%);
      color: #fef3c7;
      border: 1px solid rgba(245, 158, 11, 0.5);
      padding: 6px 18px;
      border-radius: 100px;
      font-weight: 800;
      font-size: 12.5px;
      letter-spacing: 0.06em;
      cursor: pointer;
      box-shadow: 0 2px 15px rgba(245, 158, 11, 0.4);
      transition: all 0.2s var(--ease-smooth);
      display: flex;
      align-items: center;
      gap: 6px;
    }}

    .nav-pro-pill:hover {{
      transform: scale(1.06);
      box-shadow: 0 4px 22px rgba(245, 158, 11, 0.65);
      border-color: #ffffff;
    }}

    .nav-btn-accent {{
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(185, 28, 28, 0.85);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      cursor: pointer;
      transition: all 0.2s;
    }}
    .nav-btn-accent:hover {{
      background: rgba(220, 38, 38, 1);
      transform: scale(1.08);
    }}

    /* ==========================================================================
       TAB SECTIONS
       ========================================================================== */
    .tab-content {{
      display: none;
      animation: fadeInTab 0.35s var(--ease-smooth) forwards;
    }}
    .tab-content.active {{
      display: block;
    }}

    @keyframes fadeInTab {{
      from {{ opacity: 0; transform: translateY(10px); }}
      to {{ opacity: 1; transform: translateY(0); }}
    }}

    /* ==========================================================================
       HERO BANNER "PLEIN POT" — CARTE COMPLÈTE SANS AUCUN CROP
       ========================================================================== */
    .hero-fullscreen-wrapper {{
      position: relative;
      width: 100%;
      min-height: 740px;
      background: var(--hero-bg, radial-gradient(circle at 60% 40%, #e11d48 0%, #be123c 45%, #881337 85%));
      transition: background 0.7s var(--ease-smooth);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 44px 60px 28px 60px;
      overflow: hidden;
    }}

    /* Halftone Polka Dot Pattern (Wallspace Signature) */
    .hero-dots-texture {{
      position: absolute;
      inset: 0;
      background-image: radial-gradient(var(--dots-color, rgba(255, 255, 255, 0.32)) 2.6px, transparent 2.6px);
      background-size: 28px 28px;
      opacity: 0.85;
      pointer-events: none;
      z-index: 1;
    }}

    /* Cinematic ambient overlays */
    .hero-cinematic-overlay {{
      position: absolute;
      inset: 0;
      background: 
        radial-gradient(circle at 75% 35%, rgba(255, 255, 255, 0.22) 0%, transparent 65%),
        linear-gradient(180deg, rgba(9, 10, 15, 0.35) 0%, transparent 40%, rgba(9, 10, 15, 0.95) 100%),
        linear-gradient(90deg, rgba(9, 10, 15, 0.75) 0%, rgba(9, 10, 15, 0.15) 50%, transparent 100%);
      pointer-events: none;
      z-index: 2;
    }}

    /* Hero Main Row (Content Left + Giant Uncropped Card Right) */
    .hero-main-row {{
      position: relative;
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 50px;
      flex: 1;
    }}

    /* Left Info Column */
    .hero-content-col {{
      max-width: 650px;
    }}

    .hero-badge-pill {{
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.9);
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(12px);
      padding: 6px 18px;
      border-radius: 100px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      margin-bottom: 18px;
    }}

    .hero-title-giant {{
      font-size: clamp(42px, 5vw, 72px);
      font-weight: 900;
      line-height: 1.05;
      color: #ffffff;
      margin-bottom: 14px;
      letter-spacing: -0.035em;
      text-shadow: 0 4px 25px rgba(0, 0, 0, 0.5);
    }}

    .hero-meta-strip {{
      display: flex;
      align-items: center;
      gap: 16px;
      font-size: 15px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.85);
      margin-bottom: 20px;
      flex-wrap: wrap;
    }}

    .hero-meta-dot {{
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.4);
    }}

    .hero-quote-catchphrase {{
      font-family: var(--font-prose);
      font-size: 21px;
      font-style: italic;
      line-height: 1.5;
      color: #f8fafc;
      margin-bottom: 30px;
      border-left: 3px solid #ffffff;
      padding-left: 18px;
      max-width: 580px;
    }}

    .hero-actions-group {{
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }}

    .hero-btn-primary {{
      background: #ffffff;
      color: #090a0f;
      font-family: var(--font-sans);
      font-size: 16px;
      font-weight: 800;
      padding: 14px 34px;
      border-radius: 100px;
      border: none;
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
      transition: all 0.25s var(--ease-smooth);
    }}

    .hero-btn-primary:hover {{
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
      background: #f8fafc;
    }}

    .hero-btn-secondary {{
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(15px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #ffffff;
      font-family: var(--font-sans);
      font-size: 15px;
      font-weight: 700;
      padding: 14px 28px;
      border-radius: 100px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.25s var(--ease-smooth);
    }}

    .hero-btn-secondary:hover {{
      background: rgba(255, 255, 255, 0.2);
      border-color: #ffffff;
      transform: translateY(-2px);
    }}

    .hero-fav-btn {{
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(15px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      cursor: pointer;
      transition: all 0.25s var(--ease-smooth);
    }}

    .hero-fav-btn:hover {{
      background: rgba(255, 255, 255, 0.3);
      transform: scale(1.1);
    }}

    .hero-fav-btn.is-fav {{
      background: #ffffff;
      color: #f43f5e;
      border-color: #ffffff;
    }}

    /* RIGHT COLUMN: GIANT UNCROPPED TAROT CARD (100% ARTWORK VISIBLE) */
    .hero-art-col {{
      position: relative;
      perspective: 1200px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 30px;
    }}

    .hero-tarot-uncropped-frame {{
      width: 320px;
      aspect-ratio: var(--tarot-ratio);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 
        0 35px 80px -15px rgba(0, 0, 0, 0.95),
        0 0 0 2px rgba(255, 255, 255, 0.35),
        0 0 70px var(--accent-hero-glow, rgba(225, 29, 72, 0.6));
      position: relative;
      cursor: pointer;
      background: #000000;
      transition: transform 0.4s var(--ease-smooth), box-shadow 0.4s var(--ease-smooth);
    }}

    .hero-tarot-uncropped-frame:hover {{
      transform: translateY(-12px) rotate(-1.5deg) scale(1.03);
      box-shadow: 
        0 45px 100px -20px rgba(0, 0, 0, 0.95),
        0 0 0 3px rgba(255, 255, 255, 0.6),
        0 0 90px var(--accent-hero-glow, rgba(225, 29, 72, 0.85));
    }}

    .hero-tarot-uncropped-frame img {{
      width: 100%;
      height: 100%;
      object-fit: contain; /* Exact uncropped fit! */
      display: block;
      background: #000;
    }}

    /* Metallic Foil Shimmer */
    .hero-tarot-uncropped-frame::after {{
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, transparent 45%, rgba(255,255,255,0.12) 80%, transparent 100%);
      pointer-events: none;
    }}

    /* Floating Speech Bubble (Wallspace replica ✨) */
    .hero-floating-bubble {{
      position: absolute;
      top: 10%;
      right: -35px;
      background: #ffffff;
      color: #0f172a;
      padding: 12px 22px;
      border-radius: 100px;
      font-size: 15px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
      z-index: 20;
      animation: floatBadgeAnim 3.5s ease-in-out infinite alternate;
    }}
    .hero-floating-bubble::before {{
      content: '';
      position: absolute;
      left: -10px;
      top: 50%;
      transform: translateY(-50%);
      border-width: 8px 10px 8px 0;
      border-style: solid;
      border-color: transparent #ffffff transparent transparent;
    }}

    @keyframes floatBadgeAnim {{
      from {{ transform: translateY(0); }}
      to {{ transform: translateY(-10px); }}
    }}

    /* ==========================================================================
       HERO BOTTOM FLOATING THUMBNAILS (UNCROPPED VERTICAL CARDS)
       ========================================================================== */
    .hero-thumb-strip-wrapper {{
      position: relative;
      z-index: 20;
      width: 100%;
      padding-top: 24px;
    }}

    .hero-thumb-strip {{
      display: flex;
      align-items: center;
      gap: 16px;
      overflow-x: auto;
      scroll-behavior: smooth;
      padding: 8px 4px 16px 4px;
    }}

    .hero-thumb-strip::-webkit-scrollbar {{
      display: none;
    }}

    /* Uncropped Vertical Thumbnail Cards */
    .hero-thumb-item {{
      flex: 0 0 76px;
      aspect-ratio: var(--tarot-ratio);
      border-radius: 12px;
      overflow: hidden;
      position: relative;
      cursor: pointer;
      border: 2px solid transparent;
      opacity: 0.65;
      transition: all 0.25s var(--ease-smooth);
      background: #000;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
    }}

    .hero-thumb-item:hover {{
      opacity: 0.95;
      transform: translateY(-5px) scale(1.08);
    }}

    .hero-thumb-item.active {{
      opacity: 1;
      border-color: #ffffff;
      box-shadow: 0 0 0 3px var(--accent-hero, #e11d48), 0 10px 25px rgba(0, 0, 0, 0.8);
      transform: translateY(-8px) scale(1.12);
    }}

    .hero-thumb-item img {{
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
      background: #000;
    }}

    /* ==========================================================================
       NETFLIX-STYLE FULL-BLEED CAROUSEL SECTIONS (UNCROPPED CARDS)
       ========================================================================== */
    .netflix-section {{
      padding: 44px 60px;
      position: relative;
    }}

    .section-head-bar {{
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 24px;
    }}

    .section-title-box h2 {{
      font-size: 28px;
      font-weight: 900;
      color: #ffffff;
      letter-spacing: -0.025em;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 10px;
    }}

    .section-title-box p {{
      font-size: 15px;
      color: var(--text-muted);
    }}

    .carousel-arrows-box {{
      display: flex;
      align-items: center;
      gap: 10px;
    }}

    .carousel-arrow-btn {{
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.2s var(--ease-smooth);
    }}

    .carousel-arrow-btn:hover {{
      background: var(--glass-bg-hover);
      border-color: var(--glass-border-hover);
      transform: scale(1.1);
    }}

    /* CAROUSEL ROW */
    .netflix-carousel-row {{
      display: flex;
      gap: 24px;
      overflow-x: auto;
      scroll-behavior: smooth;
      padding: 10px 6px 28px 6px;
    }}

    .netflix-carousel-row::-webkit-scrollbar {{
      height: 6px;
    }}
    .netflix-carousel-row::-webkit-scrollbar-track {{
      background: rgba(255, 255, 255, 0.03);
    }}
    .netflix-carousel-row::-webkit-scrollbar-thumb {{
      background: rgba(255, 255, 255, 0.18);
      border-radius: 10px;
    }}

    /* GRAND FORMAT UNCROPPED TAROT CARD (WALLSPACE'S PICK & MAJORS) */
    .grand-tarot-card {{
      flex: 0 0 240px;
      aspect-ratio: var(--tarot-ratio);
      border-radius: 18px;
      overflow: hidden;
      position: relative;
      cursor: pointer;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: #090a0f;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
      transition: all 0.35s var(--ease-smooth);
    }}

    .grand-tarot-card:hover {{
      transform: translateY(-10px) scale(1.06);
      border-color: rgba(255, 255, 255, 0.45);
      box-shadow: 0 25px 55px rgba(0, 0, 0, 0.85), 0 0 35px var(--accent-hero-glow);
      z-index: 10;
    }}

    .grand-tarot-card img {{
      width: 100%;
      height: 100%;
      object-fit: contain; /* Never cropped! */
      display: block;
      background: #000;
      transition: transform 0.4s var(--ease-smooth);
    }}

    .grand-tarot-card:hover img {{
      transform: scale(1.03);
    }}

    /* Floating subtle info card on hover/overlay */
    .card-floating-badge-top {{
      position: absolute;
      top: 10px;
      left: 10px;
      right: 10px;
      display: flex;
      justify-content: space-between;
      pointer-events: none;
      z-index: 2;
    }}

    .tag-pro-gold {{
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(10px);
      color: #fbbf24;
      border: 1px solid rgba(251, 191, 36, 0.4);
      font-size: 11px;
      font-weight: 900;
      padding: 4px 10px;
      border-radius: 8px;
      letter-spacing: 0.05em;
    }}

    .tag-element-badge {{
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(10px);
      color: #ffffff;
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }}

    .card-subtle-hover-info {{
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 16px 14px 12px 14px;
      background: linear-gradient(180deg, transparent 0%, rgba(9, 10, 15, 0.85) 40%, rgba(9, 10, 15, 0.98) 100%);
      display: flex;
      flex-direction: column;
      gap: 2px;
      z-index: 2;
      opacity: 0.95;
      transition: opacity 0.2s;
    }}

    .card-subtle-hover-info h3 {{
      font-size: 16px;
      font-weight: 800;
      color: #ffffff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }}

    .card-subtle-hover-info p {{
      font-size: 12.5px;
      color: #fbbf24;
      font-weight: 700;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }}

    /* SUITE FILTER TABS */
    .suite-filter-bar {{
      display: flex;
      gap: 12px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }}

    .suite-pill-btn {{
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      font-family: var(--font-sans);
      font-size: 14px;
      font-weight: 700;
      padding: 10px 24px;
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
    }}

    .suite-pill-btn:hover {{
      color: #ffffff;
      background: var(--glass-bg-hover);
    }}

    .suite-pill-btn.active {{
      background: #ffffff;
      color: #090a0f;
      font-weight: 800;
      border-color: #ffffff;
    }}

    /* SPREADS NETFLIX PROMO CARDS */
    .spreads-promo-grid {{
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
    }}

    .spread-banner-card {{
      background: linear-gradient(145deg, #1a1c29 0%, #0f1017 100%);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 24px;
      padding: 32px;
      cursor: pointer;
      transition: all 0.35s var(--ease-smooth);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 220px;
    }}

    .spread-banner-card:hover {{
      transform: translateY(-8px);
      border-color: rgba(255, 255, 255, 0.35);
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6), 0 0 30px rgba(225, 29, 72, 0.2);
    }}

    .spread-banner-icon {{
      width: 52px;
      height: 52px;
      border-radius: 14px;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      margin-bottom: 16px;
    }}

    .spread-banner-card h4 {{
      font-size: 21px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 8px;
    }}

    .spread-banner-card p {{
      font-size: 14.5px;
      color: var(--text-muted);
      line-height: 1.5;
    }}

    .spread-banner-cta {{
      margin-top: 20px;
      font-size: 14.5px;
      font-weight: 800;
      color: #fbbf24;
      display: flex;
      align-items: center;
      gap: 6px;
    }}

    /* ==========================================================================
       EXPLORE TAB (FULL 78 CARDS FULLSCREEN GRID — 100% UNCROPPED)
       ========================================================================== */
    .explore-view-wrap {{
      padding: 44px 60px;
    }}

    .explore-top-bar {{
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 32px;
    }}

    .explore-search-box {{
      position: relative;
      flex: 1;
      min-width: 280px;
      max-width: 520px;
    }}

    .explore-search-input {{
      width: 100%;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid var(--glass-border);
      border-radius: 100px;
      padding: 13px 22px 13px 48px;
      color: #ffffff;
      font-family: var(--font-sans);
      font-size: 15px;
      outline: none;
      transition: all 0.2s;
    }}

    .explore-search-input:focus {{
      border-color: rgba(255, 255, 255, 0.5);
      background: rgba(255, 255, 255, 0.1);
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
    }}

    .explore-search-icon {{
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-dim);
      font-size: 17px;
    }}

    .explore-filters-group {{
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }}

    .explore-filter-btn {{
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      font-family: var(--font-sans);
      font-size: 13.5px;
      font-weight: 700;
      padding: 9px 18px;
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.2s;
    }}

    .explore-filter-btn:hover {{
      color: #fff;
      background: var(--glass-bg-hover);
    }}

    .explore-filter-btn.active {{
      background: #ffffff;
      color: #090a0f;
      font-weight: 800;
    }}

    /* 78 CARDS FULLSCREEN GRID — EXACT ASPECT RATIO (NO CROP) */
    .grid-cards-78-plein-pot {{
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
      gap: 26px;
    }}

    .grid-card-item {{
      aspect-ratio: var(--tarot-ratio);
      border-radius: 18px;
      overflow: hidden;
      cursor: pointer;
      transition: all 0.35s var(--ease-smooth);
      position: relative;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #000000;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
    }}

    .grid-card-item:hover {{
      transform: translateY(-8px) scale(1.04);
      border-color: rgba(255, 255, 255, 0.4);
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.8), 0 0 25px var(--accent-hero-glow);
      z-index: 10;
    }}

    .grid-card-item img {{
      width: 100%;
      height: 100%;
      object-fit: contain; /* Exact uncropped image! */
      display: block;
      background: #000;
    }}

    /* ==========================================================================
       LIBRARY & TIRAGE FULLSCREEN VIEWS
       ========================================================================== */
    .library-view-wrap {{
      padding: 44px 60px;
    }}

    .tirage-view-wrap {{
      padding: 44px 60px;
    }}

    /* ==========================================================================
       GIANT IMMERSIVE CARD DETAIL MODAL (UNCROPPED 3D STAGE)
       ========================================================================== */
    .modal-fullscreen-backdrop {{
      position: fixed;
      inset: 0;
      z-index: 1000;
      background: rgba(0, 0, 0, 0.9);
      backdrop-filter: blur(35px);
      -webkit-backdrop-filter: blur(35px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s var(--ease-smooth);
    }}

    .modal-fullscreen-backdrop.active {{
      opacity: 1;
      pointer-events: auto;
    }}

    .modal-dialog-box {{
      width: min(1300px, 95vw);
      max-height: min(900px, 94vh);
      background: #11121a;
      border: 1px solid rgba(255, 255, 255, 0.16);
      border-radius: 28px;
      box-shadow: 0 35px 100px rgba(0, 0, 0, 0.95);
      display: flex;
      overflow: hidden;
      position: relative;
      transform: scale(0.96);
      transition: transform 0.3s var(--ease-smooth);
    }}

    .modal-fullscreen-backdrop.active .modal-dialog-box {{
      transform: scale(1);
    }}

    .modal-btn-close {{
      position: absolute;
      top: 22px;
      right: 22px;
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      cursor: pointer;
      z-index: 10;
      transition: all 0.2s;
    }}

    .modal-btn-close:hover {{
      background: rgba(255, 255, 255, 0.25);
      transform: scale(1.1);
    }}

    /* Modal Left: 3D Giant Stage (Uncropped) */
    .modal-left-art-stage {{
      flex: 0 0 460px;
      background: linear-gradient(180deg, #181a26 0%, #08090e 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.1);
      padding: 36px 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
    }}

    .modal-card-3d-flipper {{
      perspective: 1200px;
      width: 270px;
      aspect-ratio: var(--tarot-ratio);
      cursor: pointer;
    }}

    .modal-card-3d-body {{
      width: 100%;
      height: 100%;
      position: relative;
      transform-style: preserve-3d;
      transition: transform 0.6s var(--ease-smooth);
      border-radius: 18px;
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.9), 0 0 0 2px rgba(255, 255, 255, 0.3);
      background: #000;
    }}

    .modal-card-3d-body.is-flipped {{
      transform: rotateY(180deg);
    }}

    .modal-face {{
      position: absolute;
      inset: 0;
      backface-visibility: hidden;
      border-radius: 18px;
      overflow: hidden;
      background: #000;
    }}

    .modal-face-front img {{
      width: 100%;
      height: 100%;
      object-fit: contain; /* Exact uncropped! */
      display: block;
      background: #000;
    }}

    .modal-face-back {{
      transform: rotateY(180deg);
      background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #090a0f 100%);
      border: 3px solid #d4af37;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #d4af37;
      font-size: 54px;
    }}

    /* Modal Right: Interpretation Knowledge Base */
    .modal-right-reading {{
      flex: 1;
      overflow-y: auto;
      padding: 44px 44px 48px 44px;
      display: flex;
      flex-direction: column;
    }}

    .modal-header-info {{
      margin-bottom: 24px;
    }}

    .modal-title-main {{
      font-size: 38px;
      font-weight: 900;
      color: #ffffff;
      letter-spacing: -0.025em;
      margin-bottom: 8px;
    }}

    .modal-quote-box {{
      font-family: var(--font-prose);
      font-style: italic;
      font-size: 21px;
      color: #f1f5f9;
      line-height: 1.5;
      border-left: 3px solid #fbbf24;
      padding-left: 18px;
      margin: 14px 0 24px 0;
    }}

    .modal-tabs-nav {{
      display: flex;
      gap: 8px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.12);
      padding-bottom: 14px;
      margin-bottom: 24px;
      overflow-x: auto;
    }}

    .modal-tab-button {{
      background: transparent;
      border: none;
      color: var(--text-muted);
      font-family: var(--font-sans);
      font-size: 14px;
      font-weight: 700;
      padding: 8px 16px;
      border-radius: 10px;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s;
    }}

    .modal-tab-button:hover {{
      color: #ffffff;
      background: rgba(255, 255, 255, 0.06);
    }}

    .modal-tab-button.active {{
      background: rgba(255, 255, 255, 0.14);
      color: #ffffff;
      font-weight: 800;
    }}

    .modal-tab-pane-content {{
      display: none;
      line-height: 1.8;
      font-size: 16px;
      color: #cbd5e1;
    }}

    .modal-tab-pane-content.active {{
      display: block;
      animation: fadeInTab 0.25s ease forwards;
    }}

    /* SEARCH OVERLAY */
    .search-modal-wrap {{
      position: fixed;
      top: 90px;
      left: 50%;
      transform: translateX(-50%);
      width: min(720px, 94vw);
      background: rgba(18, 19, 28, 0.96);
      backdrop-filter: blur(35px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 24px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.9);
      z-index: 2000;
      padding: 20px;
      display: none;
    }}

    .search-modal-wrap.active {{
      display: block;
      animation: fadeInTab 0.2s ease forwards;
    }}

    /* RESPONSIVE */
    @media (max-width: 990px) {{
      .app-navbar {{ padding: 0 20px; }}
      .hero-fullscreen-wrapper {{ padding: 30px 20px; min-height: auto; }}
      .hero-main-row {{ flex-direction: column; text-align: center; }}
      .hero-actions-group {{ justify-content: center; }}
      .hero-art-col {{ margin-right: 0; }}
      .netflix-section {{ padding: 30px 20px; }}
      .explore-view-wrap {{ padding: 30px 20px; }}
      .modal-dialog-box {{ flex-direction: column; max-height: 94vh; }}
      .modal-left-art-stage {{ flex: 0 0 auto; padding: 24px; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.1); }}
      .modal-card-3d-flipper {{ width: 180px; }}
    }}
  </style>
</head>
<body data-theme="crimson">

  <!-- =========================================================================
       STICKY FULL-WIDTH FLOATING NAVBAR
       ========================================================================== -->
  <header class="app-navbar" id="appNavbar">
    
    <!-- Left: Brand Logo -->
    <div class="nav-brand" onclick="switchMainTab('home')">
      <div class="nav-brand-icon">✦</div>
      <span>TarotSpace</span>
    </div>

    <!-- Center: Pill Navigation -->
    <nav class="nav-pill-box">
      <button class="nav-tab-btn active" id="tabBtnHome" onclick="switchMainTab('home')">Home</button>
      <button class="nav-tab-btn" id="tabBtnExplore" onclick="switchMainTab('explore')">Explore</button>
      <button class="nav-tab-btn" id="tabBtnLibrary" onclick="switchMainTab('library')">Library</button>
      <button class="nav-tab-btn" id="tabBtnTirages" onclick="switchMainTab('tirages')">Tirages</button>
    </nav>

    <!-- Right: Controls -->
    <div class="nav-actions-right">
      <button class="nav-btn-icon" title="Oracle Mystère Express (🎁)" onclick="openMysteryCard()">🎁</button>
      <button class="nav-btn-icon" title="Recherche rapide (⌘K)" onclick="toggleSearchModal()">🔍</button>
      <div class="nav-pro-pill" onclick="openProModal()">PRO</div>
      <button class="nav-btn-accent" title="Nouveau Tirage" onclick="switchMainTab('tirages')">+</button>
      <button class="nav-btn-accent" title="Thèmes & Paramètres" onclick="openSettingsModal()">⚙</button>
    </div>
  </header>

  <!-- =========================================================================
       TAB 1: HOME VIEW (PLEIN POT & CARTES INTÉGRALES SANS AUCUN CROP)
       ========================================================================= -->
  <main class="tab-content active" id="tabContentHome">
    
    <!-- HERO BANNER "PLEIN POT" (WALLSPACE REPLICA) -->
    <section class="hero-fullscreen-wrapper" id="heroFullscreenWrap">
      <div class="hero-dots-texture" id="heroDotsTexture"></div>
      <div class="hero-cinematic-overlay"></div>

      <!-- Main Info Row -->
      <div class="hero-main-row">
        
        <!-- Left Content -->
        <div class="hero-content-col">
          <div class="hero-badge-pill" id="heroBadgePill">FEATURED · ARCANE MAJEUR</div>
          <h1 class="hero-title-giant" id="heroTitleGiant">Le Fou</h1>
          
          <div class="hero-meta-strip">
            <span id="heroMetaCategory">Arcane Majeur 0</span>
            <span class="hero-meta-dot"></span>
            <span id="heroMetaAstroElem">Air · Uranus</span>
            <span class="hero-meta-dot"></span>
            <span>RWS 1909 Classic</span>
            <span class="hero-meta-dot"></span>
            <span>Image Intégrale HD</span>
          </div>

          <div class="hero-quote-catchphrase" id="heroQuoteCatchphrase">
            « Un voyage de mille lieues commence toujours par un premier pas. »
          </div>

          <div class="hero-actions-group">
            <button class="hero-btn-primary" onclick="openActiveHeroModal()">
              <span>▶ Découvrir l'Arcane</span>
            </button>
            <button class="hero-btn-secondary" onclick="drawActiveHeroInSpread()">
              <span>🎴 Tirer cette lame</span>
            </button>
            <button class="hero-fav-btn" id="heroFavBtn" title="Ajouter aux favoris" onclick="toggleActiveHeroFav()">
              🤍
            </button>
          </div>
        </div>

        <!-- Right Giant Uncropped Artwork -->
        <div class="hero-art-col">
          <div class="hero-floating-bubble" id="heroFloatingBubble">
            ✨ ✦ 0 · Le Saut dans l'Inconnu
          </div>
          
          <div class="hero-tarot-uncropped-frame" onclick="openActiveHeroModal()">
            <img src="cards_alt/a_00_Fou.jpg" id="heroDisplayImg" alt="Featured Tarot Card" />
          </div>
        </div>

      </div>

      <!-- Bottom Floating Thumbnails Strip (Uncropped Cards) -->
      <div class="hero-thumb-strip-wrapper">
        <div class="hero-thumb-strip" id="heroThumbStrip">
          <!-- Populated by JS -->
        </div>
      </div>
    </section>

    <!-- SECTION 1: WallSpace's Pick (Grand Format Uncropped) -->
    <section class="netflix-section">
      <div class="section-head-bar">
        <div class="section-title-box">
          <h2>✦ Wallspace's Pick</h2>
          <p>Sélection curatée des plus puissantes lames du Tarot Rider-Waite-Smith</p>
        </div>
        <div class="carousel-arrows-box">
          <button class="carousel-arrow-btn" onclick="scrollSection('picksRow', -400)">❮</button>
          <button class="carousel-arrow-btn" onclick="scrollSection('picksRow', 400)">❯</button>
        </div>
      </div>

      <div class="netflix-carousel-row" id="picksRow">
        <!-- Populated via JS -->
      </div>
    </section>

    <!-- SECTION 2: Les 22 Arcanes Majeurs (Uncropped Grand Format) -->
    <section class="netflix-section">
      <div class="section-head-bar">
        <div class="section-title-box">
          <h2>👑 Les 22 Arcanes Majeurs</h2>
          <p>Le voyage initiatique de l'Âme, du Fou (0) au Monde (XXI)</p>
        </div>
        <div class="carousel-arrows-box">
          <button class="carousel-arrow-btn" onclick="scrollSection('majorsRow', -400)">❮</button>
          <button class="carousel-arrow-btn" onclick="scrollSection('majorsRow', 400)">❯</button>
        </div>
      </div>

      <div class="netflix-carousel-row" id="majorsRow">
        <!-- Populated via JS -->
      </div>
    </section>

    <!-- SECTION 3: Les 4 Suites Élémentaires (Mineurs) -->
    <section class="netflix-section">
      <div class="section-head-bar">
        <div class="section-title-box">
          <h2>⚡ Les Quatre Suites Élémentaires</h2>
          <p>Feu, Eau, Air et Terre : l'incarnation concrète de votre quotidien</p>
        </div>
      </div>

      <div class="suite-filter-bar">
        <button class="suite-pill-btn active" onclick="switchSuiteDisplay('batons', this)">🔥 Bâtons (Feu)</button>
        <button class="suite-pill-btn" onclick="switchSuiteDisplay('coupes', this)">💧 Coupes (Eau)</button>
        <button class="suite-pill-btn" onclick="switchSuiteDisplay('epees', this)">⚔️ Épées (Air)</button>
        <button class="suite-pill-btn" onclick="switchSuiteDisplay('deniers', this)">🪙 Deniers (Terre)</button>
      </div>

      <div class="netflix-carousel-row" id="suiteRow">
        <!-- Populated via JS -->
      </div>
    </section>

    <!-- SECTION 4: Oracles & Tirages Netflix Mode -->
    <section class="netflix-section">
      <div class="section-head-bar">
        <div class="section-title-box">
          <h2>🔮 Tirages & Oracles Divinatoires</h2>
          <p>Expérimentez la sagesse du Tarot à travers nos modules interactifs</p>
        </div>
      </div>

      <div class="spreads-promo-grid">
        <div class="spread-banner-card" onclick="startSpread('single')">
          <div>
            <div class="spread-banner-icon">🔮</div>
            <h4>Carte du Jour</h4>
            <p>Recevez l’énergie directrice et le conseil de votre journée.</p>
          </div>
          <div class="spread-banner-cta">Lancer le tirage →</div>
        </div>

        <div class="spread-banner-card" onclick="startSpread('three')">
          <div>
            <div class="spread-banner-icon">⏳</div>
            <h4>Passé · Présent · Futur</h4>
            <p>Explorez la dynamique temporelle et l’évolution de votre situation.</p>
          </div>
          <div class="spread-banner-cta">Lancer le tirage →</div>
        </div>

        <div class="spread-banner-card" onclick="startSpread('yesno')">
          <div>
            <div class="spread-banner-icon">⚡</div>
            <h4>Oracle Oui / Non</h4>
            <p>Une réponse claire et une affirmation guidée pour trancher un doute.</p>
          </div>
          <div class="spread-banner-cta">Poser une question →</div>
        </div>

        <div class="spread-banner-card" onclick="startSpread('cross')">
          <div>
            <div class="spread-banner-icon">✝️</div>
            <h4>Tirage en Croix (5 Lames)</h4>
            <p>Analyse complète : Atout, Obstacle, Conseil, Issue et Synthèse.</p>
          </div>
          <div class="spread-banner-cta">Consulter l'oracle →</div>
        </div>
      </div>
    </section>

  </main>

  <!-- =========================================================================
       TAB 2: EXPLORE VIEW (FULL 78 CARDS FULLSCREEN GRID — 100% UNCROPPED)
       ========================================================================= -->
  <section class="tab-content" id="tabContentExplore">
    <div class="explore-view-wrap">
      
      <div class="explore-top-bar">
        <div class="explore-search-box">
          <span class="explore-search-icon">🔍</span>
          <input type="text" class="explore-search-input" id="exploreInput" placeholder="Rechercher une lame (ex: Bateleur, Amour, Soleil, Feu)..." oninput="filterExploreCards()" />
        </div>

        <div class="explore-filters-group">
          <button class="explore-filter-btn active" onclick="setExploreFilter('all', this)">Tous (78)</button>
          <button class="explore-filter-btn" onclick="setExploreFilter('majors', this)">Majeurs (22)</button>
          <button class="explore-filter-btn" onclick="setExploreFilter('batons', this)">Bâtons (14)</button>
          <button class="explore-filter-btn" onclick="setExploreFilter('coupes', this)">Coupes (14)</button>
          <button class="explore-filter-btn" onclick="setExploreFilter('epees', this)">Épées (14)</button>
          <button class="explore-filter-btn" onclick="setExploreFilter('deniers', this)">Deniers (14)</button>
        </div>
      </div>

      <div class="grid-cards-78-plein-pot" id="exploreGridPleinPot">
        <!-- Populated via JS -->
      </div>

    </div>
  </section>

  <!-- =========================================================================
       TAB 3: LIBRARY VIEW (SAVED CARDS & JOURNAL)
       ========================================================================= -->
  <section class="tab-content" id="tabContentLibrary">
    <div class="library-view-wrap">
      <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 24px;">Mes Lames Favorites ❤️</h2>
      <div class="grid-cards-78-plein-pot" id="libraryFavsGrid">
        <!-- Populated via JS -->
      </div>
    </div>
  </section>

  <!-- =========================================================================
       TAB 4: TIRAGES ENGINE VIEW
       ========================================================================= -->
  <section class="tab-content" id="tabContentTirages">
    <div class="tirage-view-wrap">
      <div style="text-align: center; max-width: 700px; margin: 0 auto 30px auto;">
        <h2 id="tirageTitleText" style="font-size: 32px; font-weight: 900; margin-bottom: 8px;">Tirage Divinatoire Interactif</h2>
        <p id="tirageDescText" style="color: var(--text-muted); font-size: 16px;">Mélangez le jeu et tirez vos cartes avec guidance instantanée</p>
      </div>

      <div style="display: flex; flex-direction: column; align-items: center; gap: 30px;">
        <div style="display: flex; justify-content: center; gap: 24px; flex-wrap: wrap;" id="tirageSlotsRow">
          <!-- Slots populated by JS -->
        </div>

        <div style="display: flex; gap: 16px;">
          <button class="hero-btn-primary" onclick="dealSpreadAnimation()">🔀 Battre & Tirer</button>
        </div>

        <div id="tirageInterpretationBox" style="width: 100%; max-width: 850px; display: none;"></div>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       GIANT IMMERSIVE CARD DETAIL MODAL (100% UNCROPPED)
       ========================================================================= -->
  <div class="modal-fullscreen-backdrop" id="cardDetailModal" onclick="if(event.target === this) closeCardModal()">
    <div class="modal-dialog-box">
      <button class="modal-btn-close" onclick="closeCardModal()">✕</button>

      <!-- Left Art Stage -->
      <div class="modal-left-art-stage">
        <div class="modal-card-3d-flipper" onclick="toggleModalFlip()">
          <div class="modal-card-3d-body" id="modalCard3DBody">
            <div class="modal-face modal-face-front">
              <img src="" id="modalImgFront" alt="Tarot Card" />
            </div>
            <div class="modal-face modal-face-back">
              ✦
            </div>
          </div>
        </div>

        <div style="width: 100%; display: flex; flex-direction: column; gap: 12px; margin-top: 20px;">
          <div style="display: flex; justify-content: center; gap: 8px;">
            <button class="explore-filter-btn active" id="modalPillRws" onclick="switchCardVariant('rws')">RWS 1909</button>
            <button class="explore-filter-btn" id="modalPillBorderless" onclick="switchCardVariant('borderless')">Borderless</button>
            <button class="explore-filter-btn" id="modalPillMarseille" onclick="switchCardVariant('marseille')">Marseille</button>
          </div>

          <div style="display: flex; gap: 10px;">
            <button class="hero-btn-secondary" style="flex: 1; justify-content: center;" onclick="toggleModalFav()" id="modalFavBtnText">❤️ Favoris</button>
            <button class="hero-btn-secondary" style="flex: 1; justify-content: center;" onclick="downloadCardHD()">📥 Fond HD</button>
          </div>
        </div>
      </div>

      <!-- Right Reading Content -->
      <div class="modal-right-reading">
        <div class="modal-header-info">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span class="tag-pro-gold" id="modalCatTag">ARCANE MAJEUR 0</span>
            <span class="hero-meta-dot"></span>
            <span id="modalMetaAstro" style="font-size: 13px; color: var(--text-muted);">Air · Uranus</span>
          </div>
          <h2 class="modal-title-main" id="modalTitleText">Le Fou</h2>
          <div class="modal-quote-box" id="modalQuoteText">« Un voyage de mille lieues commence toujours par un premier pas. »</div>
        </div>

        <div class="modal-tabs-nav">
          <button class="modal-tab-button active" onclick="switchReadingTab('guidance', this)">🧭 Guidance & Essence</button>
          <button class="modal-tab-button" onclick="switchReadingTab('love', this)">❤️ Amour</button>
          <button class="modal-tab-button" onclick="switchReadingTab('work', this)">💼 Travail</button>
          <button class="modal-tab-button" onclick="switchReadingTab('finance', this)">💰 Finances</button>
          <button class="modal-tab-button" onclick="switchReadingTab('cod', this)">🔮 Carte du Jour</button>
          <button class="modal-tab-button" onclick="switchReadingTab('visual', this)">🖼️ Symboles</button>
          <button class="modal-tab-button" onclick="switchReadingTab('keywords', this)">✨ Mots-clés</button>
        </div>

        <div class="modal-tab-pane-content active" id="paneGuidance"><p id="modalGuidanceText"></p></div>
        <div class="modal-tab-pane-content" id="paneLove"><p id="modalLoveText"></p></div>
        <div class="modal-tab-pane-content" id="paneWork"><p id="modalWorkText"></p></div>
        <div class="modal-tab-pane-content" id="paneFinance"><p id="modalFinanceText"></p></div>
        <div class="modal-tab-pane-content" id="paneCod"><p id="modalCodText"></p></div>
        <div class="modal-tab-pane-content" id="paneVisual"><p id="modalVisualText"></p></div>
        <div class="modal-tab-pane-content" id="paneKeywords">
          <h4 style="color:#fff; margin-bottom: 8px;">À l'endroit (Énergie lumineuse) :</h4>
          <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px;" id="modalUprightKws"></div>
          <h4 style="color:#fff; margin-bottom: 8px;">À l'envers (Défi ou blocage) :</h4>
          <div style="display:flex; flex-wrap:wrap; gap:8px;" id="modalReversedKws"></div>
        </div>

      </div>

    </div>
  </div>

  <!-- SEARCH OVERLAY -->
  <div class="search-modal-wrap" id="searchModalWrap">
    <input type="text" class="explore-search-input" id="quickSearchInput" placeholder="Rechercher une carte..." oninput="handleQuickSearchLive()" />
    <div id="quickSearchResultsList" style="max-height: 400px; overflow-y: auto; margin-top: 14px;"></div>
  </div>

  <!-- SETTINGS MODAL -->
  <div class="modal-fullscreen-backdrop" id="settingsModal" onclick="if(event.target === this) closeSettingsModal()">
    <div class="modal-dialog-box" style="max-width: 560px; padding: 36px; flex-direction: column;">
      <h3 style="font-size: 24px; font-weight: 800; color: #fff; margin-bottom: 20px;">⚙️ Paramètres & Thèmes</h3>
      
      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 12px;">THÈME DE COULEUR HERO</label>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <button class="explore-filter-btn" onclick="setTheme('crimson')">🔴 WallSpace Crimson</button>
          <button class="explore-filter-btn" onclick="setTheme('space')">🌌 Deep Cosmic</button>
          <button class="explore-filter-btn" onclick="setTheme('gold')">☀️ Solar Gold</button>
          <button class="explore-filter-btn" onclick="setTheme('azure')">🌊 Arcana Azure</button>
          <button class="explore-filter-btn" onclick="setTheme('emerald')">🌿 Mystic Emerald</button>
          <button class="explore-filter-btn" onclick="setTheme('amethyst')">🔮 Void Amethyst</button>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end;">
        <button class="hero-btn-primary" onclick="closeSettingsModal()">Fermer</button>
      </div>
    </div>
  </div>

  <!-- PRO MODAL -->
  <div class="modal-fullscreen-backdrop" id="proModal" onclick="if(event.target === this) closeProModal()">
    <div class="modal-dialog-box" style="max-width: 600px; padding: 40px; flex-direction: column; text-align: center;">
      <div style="font-size: 48px; margin-bottom: 12px;">✨ ✦ 👑</div>
      <h3 style="font-size: 28px; font-weight: 900; color: #fff; margin-bottom: 8px;">TarotSpace PRO</h3>
      <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 15px;">Fonds d'écran 4K UHD, variantes historiques & oracles exclusifs</p>
      
      <button class="hero-btn-primary" style="background: linear-gradient(135deg, #d97706, #b45309); width: 100%; justify-content: center; color: #fff;" onclick="closeProModal()">Activer l'expérience PRO</button>
    </div>
  </div>

  <!-- =========================================================================
       JAVASCRIPT APPLICATION ENGINE
       ========================================================================= -->
  <script>
    const TAROT_CARDS = {cards_json_str};

    let currentHeroIndex = 0;
    let activeCardModal = null;
    let activeCardVariant = 'rws';
    let favorites = JSON.parse(localStorage.getItem('tarotspace_favs') || '["a_00_Fou", "a_01_Bateleur", "a_17_Etoile", "a_19_Soleil", "a_21_Monde"]');

    const FEATURED_HERO_IDS = [
      'a_00_Fou', 'a_01_Bateleur', 'a_02_Papesse', 'a_03_Impératrice',
      'a_06_Amoureux', 'a_08_Force', 'a_13_Mort', 'a_17_Etoile',
      'a_18_Lune', 'a_19_Soleil', 'a_21_Monde', 'c_01_As', 'b_01_As'
    ];

    document.addEventListener('DOMContentLoaded', () => {{
      initHeroThumbs();
      updateHeroDisplay(0);
      populatePicksRow();
      populateMajorsRow();
      populateSuiteRow('batons');
      populateExploreGrid();
      updateLibraryGrid();

      window.addEventListener('scroll', () => {{
        const nav = document.getElementById('appNavbar');
        if (window.scrollY > 40) nav.classList.add('scrolled');
        else nav.classList.remove('scrolled');
      }});

      document.addEventListener('keydown', (e) => {{
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {{
          e.preventDefault();
          toggleSearchModal();
        }}
        if (e.key === 'Escape') {{
          closeCardModal();
          closeSettingsModal();
          closeProModal();
          document.getElementById('searchModalWrap').classList.remove('active');
        }}
      }});
    }});

    // HERO LOGIC
    function initHeroThumbs() {{
      const strip = document.getElementById('heroThumbStrip');
      strip.innerHTML = '';
      
      FEATURED_HERO_IDS.forEach((id, index) => {{
        const card = TAROT_CARDS.find(c => c.id === id) || TAROT_CARDS[0];
        const div = document.createElement('div');
        div.className = `hero-thumb-item ${{index === 0 ? 'active' : ''}}`;
        div.title = card.name;
        div.onclick = () => updateHeroDisplay(index);
        div.innerHTML = `
          <img src="${{card.img_rws}}" alt="${{card.name}}" />
        `;
        strip.appendChild(div);
      }});
    }}

    function updateHeroDisplay(index) {{
      currentHeroIndex = index;
      const cardId = FEATURED_HERO_IDS[index] || FEATURED_HERO_IDS[0];
      const card = TAROT_CARDS.find(c => c.id === cardId) || TAROT_CARDS[0];

      document.querySelectorAll('.hero-thumb-item').forEach((th, idx) => {{
        th.classList.toggle('active', idx === index);
      }});

      const wrap = document.getElementById('heroFullscreenWrap');
      wrap.style.background = card.hero_bg || 'radial-gradient(circle at 60% 40%, #e11d48 0%, #be123c 45%, #881337 85%)';
      wrap.style.setProperty('--accent-hero-glow', (card.color || '#e11d48') + '66');

      document.getElementById('heroDotsTexture').style.setProperty('--dots-color', card.dots_color || 'rgba(255,255,255,0.32)');
      document.getElementById('heroDisplayImg').src = card.img_rws;
      document.getElementById('heroTitleGiant').innerText = card.name;
      document.getElementById('heroBadgePill').innerText = `FEATURED · ${{card.category.toUpperCase()}}`;
      document.getElementById('heroMetaCategory').innerText = card.category + (card.num_display ? ' ' + card.num_display : '');
      document.getElementById('heroMetaAstroElem').innerText = `${{card.element_symbol}} ${{card.element}} · ${{card.astro || 'Tradition'}}`;
      document.getElementById('heroQuoteCatchphrase').innerText = card.quote || `« ${{card.idea}} »`;
      document.getElementById('heroFloatingBubble').innerText = card.speech || `✨ ✦ ${{card.name}}`;

      const favBtn = document.getElementById('heroFavBtn');
      const isFav = favorites.includes(card.id);
      favBtn.innerText = isFav ? '❤️' : '🤍';
      favBtn.classList.toggle('is-fav', isFav);
    }}

    function openActiveHeroModal() {{
      const cardId = FEATURED_HERO_IDS[currentHeroIndex];
      const card = TAROT_CARDS.find(c => c.id === cardId);
      if (card) openCardModal(card);
    }}

    function toggleActiveHeroFav() {{
      const cardId = FEATURED_HERO_IDS[currentHeroIndex];
      toggleFavorite(cardId);
      updateHeroDisplay(currentHeroIndex);
    }}

    function drawActiveHeroInSpread() {{
      switchMainTab('tirages');
      const cardId = FEATURED_HERO_IDS[currentHeroIndex];
      const card = TAROT_CARDS.find(c => c.id === cardId);
      if (card) {{
        startSpread('single');
        setTimeout(() => {{
          revealSlot(0, card);
          synthesizeSpread();
        }}, 300);
      }}
    }}

    // CAROUSEL POPULATIONS (UNCROPPED)
    function populatePicksRow() {{
      const row = document.getElementById('picksRow');
      row.innerHTML = '';
      const picks = ['a_01_Bateleur', 'a_02_Papesse', 'a_03_Impératrice', 'a_17_Etoile', 'a_19_Soleil', 'a_21_Monde', 'c_01_As', 'd_01_As'];
      picks.forEach(id => {{
        const card = TAROT_CARDS.find(c => c.id === id);
        if (!card) return;
        row.appendChild(createGrandCard(card, true));
      }});
    }}

    function populateMajorsRow() {{
      const row = document.getElementById('majorsRow');
      row.innerHTML = '';
      const majors = TAROT_CARDS.filter(c => c.prefix === 'a');
      majors.forEach(card => {{
        row.appendChild(createGrandCard(card, false));
      }});
    }}

    function switchSuiteDisplay(suiteKey, btn) {{
      document.querySelectorAll('.suite-pill-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      populateSuiteRow(suiteKey);
    }}

    function populateSuiteRow(suiteKey) {{
      const row = document.getElementById('suiteRow');
      row.innerHTML = '';
      const list = TAROT_CARDS.filter(c => c.category_key === suiteKey);
      list.forEach(card => {{
        row.appendChild(createGrandCard(card, false));
      }});
    }}

    function createGrandCard(card, isPro = false) {{
      const div = document.createElement('div');
      div.className = 'grand-tarot-card';
      div.onclick = () => openCardModal(card);
      div.innerHTML = `
        <img src="${{card.img_rws}}" alt="${{card.name}}" loading="lazy" />
        <div class="card-floating-badge-top">
          <span class="tag-element-badge">${{card.num_display || ''}}</span>
          ${{isPro ? '<span class="tag-pro-gold">PRO</span>' : `<span class="tag-element-badge">${{card.element_symbol}}</span>`}}
        </div>
        <div class="card-subtle-hover-info">
          <h3>${{card.name}}</h3>
          <p>${{card.key_word}} · ${{card.element}}</p>
        </div>
      `;
      return div;
    }}

    function scrollSection(id, val) {{
      const el = document.getElementById(id);
      if (el) el.scrollBy({{ left: val, behavior: 'smooth' }});
    }}

    // EXPLORE GRID
    let exploreFilterKey = 'all';

    function populateExploreGrid(list = null) {{
      const grid = document.getElementById('exploreGridPleinPot');
      grid.innerHTML = '';
      const cardsToShow = list || (exploreFilterKey === 'all' ? TAROT_CARDS : TAROT_CARDS.filter(c => c.category_key === exploreFilterKey));

      cardsToShow.forEach(card => {{
        const div = document.createElement('div');
        div.className = 'grid-card-item';
        div.onclick = () => openCardModal(card);
        div.innerHTML = `
          <img src="${{card.img_rws}}" alt="${{card.name}}" loading="lazy" />
          <div class="card-floating-badge-top">
            <span class="tag-element-badge">${{card.num_display || ''}}</span>
            <span class="tag-element-badge">${{card.element_symbol}}</span>
          </div>
          <div class="card-subtle-hover-info">
            <h3>${{card.name}}</h3>
            <p>${{card.key_word}}</p>
          </div>
        `;
        grid.appendChild(div);
      }});
    }}

    function setExploreFilter(key, btn) {{
      exploreFilterKey = key;
      document.querySelectorAll('.explore-filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filterExploreCards();
    }}

    function filterExploreCards() {{
      const q = document.getElementById('exploreInput').value.toLowerCase().trim();
      let list = exploreFilterKey === 'all' ? TAROT_CARDS : TAROT_CARDS.filter(c => c.category_key === exploreFilterKey);
      if (q) {{
        list = list.filter(c => 
          c.name.toLowerCase().includes(q) ||
          c.name_en.toLowerCase().includes(q) ||
          c.key_word.toLowerCase().includes(q) ||
          c.element.toLowerCase().includes(q) ||
          c.idea.toLowerCase().includes(q)
        );
      }}
      populateExploreGrid(list);
    }}

    // MODAL CARD
    function openCardModal(card) {{
      activeCardModal = card;
      activeCardVariant = 'rws';

      document.getElementById('cardDetailModal').classList.add('active');
      document.getElementById('modalImgFront').src = card.img_rws;
      document.getElementById('modalTitleText').innerText = card.name;
      document.getElementById('modalCatTag').innerText = `${{card.category.toUpperCase()}} ${{card.num_display}}`;
      document.getElementById('modalMetaAstro').innerText = `${{card.element_symbol}} ${{card.element}} · ${{card.astro || 'Tradition'}}`;
      document.getElementById('modalQuoteText').innerText = card.quote || `« ${{card.idea}} »`;

      document.getElementById('modalGuidanceText').innerHTML = card.guidance ? card.guidance.replace(/\\n/g, '<br/><br/>') : (card.interpretation || card.idea);
      document.getElementById('modalLoveText').innerHTML = card.love ? card.love.replace(/\\n/g, '<br/><br/>') : 'En amour, cette carte apporte clarté et bienveillance.';
      document.getElementById('modalWorkText').innerHTML = card.work ? card.work.replace(/\\n/g, '<br/><br/>') : 'Sur le plan professionnel, la volonté et la maîtrise portent leurs fruits.';
      document.getElementById('modalFinanceText').innerHTML = card.finance ? card.finance.replace(/\\n/g, '<br/><br/>') : 'Financièrement, la lucidité assure la sécurité matérielle.';
      document.getElementById('modalCodText').innerHTML = card.cod_text ? card.cod_text.replace(/\\n/g, '<br/><br/>') : card.idea;
      document.getElementById('modalVisualText').innerHTML = card.image_desc ? card.image_desc.replace(/\\n/g, '<br/><br/>') : `Description symbolique de la lame ${{card.name}}.`;

      const upWrap = document.getElementById('modalUprightKws');
      upWrap.innerHTML = '';
      (card.upright_keywords || [card.key_word, 'Éveil', 'Harmonie']).forEach(kw => {{
        upWrap.innerHTML += `<span class="explore-filter-btn" style="cursor:default;">${{kw}}</span>`;
      }});

      const revWrap = document.getElementById('modalReversedKws');
      revWrap.innerHTML = '';
      (card.reversed_keywords || ['Doute', 'Blocage']).forEach(kw => {{
        revWrap.innerHTML += `<span class="explore-filter-btn" style="cursor:default; border-color:rgba(239,68,68,0.4); color:#fca5a5;">${{kw}}</span>`;
      }});

      document.getElementById('modalPillBorderless').style.display = card.img_borderless ? 'inline-block' : 'none';
      document.getElementById('modalPillMarseille').style.display = card.img_marseille ? 'inline-block' : 'none';

      document.getElementById('modalFavBtnText').innerText = favorites.includes(card.id) ? '❤️ Retirer' : '🤍 Favoris';
    }}

    function closeCardModal() {{
      document.getElementById('cardDetailModal').classList.remove('active');
      document.getElementById('modalCard3DBody').classList.remove('is-flipped');
    }}

    function toggleModalFlip() {{
      document.getElementById('modalCard3DBody').classList.toggle('is-flipped');
    }}

    function switchCardVariant(key) {{
      activeCardVariant = key;
      document.querySelectorAll('#modalPillRws, #modalPillBorderless, #modalPillMarseille').forEach(b => b.classList.remove('active'));

      let path = activeCardModal.img_rws;
      if (key === 'borderless' && activeCardModal.img_borderless) {{
        path = activeCardModal.img_borderless;
        document.getElementById('modalPillBorderless').classList.add('active');
      }} else if (key === 'marseille' && activeCardModal.img_marseille) {{
        path = activeCardModal.img_marseille;
        document.getElementById('modalPillMarseille').classList.add('active');
      }} else {{
        document.getElementById('modalPillRws').classList.add('active');
      }}
      document.getElementById('modalImgFront').src = path;
    }}

    function switchReadingTab(tabKey, btn) {{
      document.querySelectorAll('.modal-tab-button').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.modal-tab-pane-content').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      
      const paneId = 'pane' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1);
      const el = document.getElementById(paneId);
      if (el) el.classList.add('active');
    }}

    function toggleModalFav() {{
      if (!activeCardModal) return;
      toggleFavorite(activeCardModal.id);
      document.getElementById('modalFavBtnText').innerText = favorites.includes(activeCardModal.id) ? '❤️ Retirer' : '🤍 Favoris';
    }}

    function downloadCardHD() {{
      if (!activeCardModal) return;
      const a = document.createElement('a');
      a.href = document.getElementById('modalImgFront').src;
      a.download = `TarotSpace_${{activeCardModal.id}}.jpg`;
      a.click();
    }}

    // FAVORITES & LIBRARY
    function toggleFavorite(id) {{
      if (favorites.includes(id)) favorites = favorites.filter(x => x !== id);
      else favorites.push(id);
      localStorage.setItem('tarotspace_favs', JSON.stringify(favorites));
      updateLibraryGrid();
    }}

    function updateLibraryGrid() {{
      const grid = document.getElementById('libraryFavsGrid');
      grid.innerHTML = '';
      const list = TAROT_CARDS.filter(c => favorites.includes(c.id));
      if (list.length === 0) {{
        grid.innerHTML = '<p style="color:var(--text-muted); grid-column:1/-1;">Aucune carte enregistrée dans vos favoris.</p>';
      }} else {{
        list.forEach(card => {{
          const div = document.createElement('div');
          div.className = 'grid-card-item';
          div.onclick = () => openCardModal(card);
          div.innerHTML = `
            <img src="${{card.img_rws}}" alt="${{card.name}}" />
            <div class="card-floating-badge-top">
              <span class="tag-element-badge">${{card.num_display || ''}}</span>
              <span class="tag-element-badge">${{card.element_symbol}}</span>
            </div>
            <div class="card-subtle-hover-info">
              <h3>${{card.name}}</h3>
              <p>${{card.element}}</p>
            </div>
          `;
          grid.appendChild(div);
        }});
      }}
    }}

    // TIRAGES
    let spreadMode = 'single';
    let currentSpreadList = [];

    function startSpread(mode) {{
      spreadMode = mode;
      switchMainTab('tirages');

      const meta = {{
        single: ['Carte du Jour', 'Recevez la guidance essentielle pour éclairer votre journée.'],
        three: ['Passé · Présent · Futur', 'L\\'évolution temporelle de votre questionnement.'],
        yesno: ['Oracle Oui / Non', 'Une réponse directe pour trancher un doute.'],
        cross: ['Tirage en Croix', 'Atout, Obstacle, Conseil, Issue et Synthèse.']
      }};

      const info = meta[mode] || meta.single;
      document.getElementById('tirageTitleText').innerText = info[0];
      document.getElementById('tirageDescText').innerText = info[1];

      setupSlots(mode);
    }}

    function setupSlots(mode) {{
      const row = document.getElementById('tirageSlotsRow');
      row.innerHTML = '';
      document.getElementById('tirageInterpretationBox').style.display = 'none';

      const labelsMap = {{
        single: ['✦ Guidance du Jour'],
        three: ['1. Le Passé', '2. Le Présent', '3. Le Futur'],
        yesno: ['⚡ Réponse de l\\'Oracle'],
        cross: ['1. Atout', '2. Défi', '3. Conseil', '4. Issue', '5. Synthèse']
      }};

      const labels = labelsMap[mode] || labelsMap.single;
      currentSpreadList = new Array(labels.length).fill(null);

      labels.forEach((label, idx) => {{
        const div = document.createElement('div');
        div.style.cssText = 'display:flex; flex-direction:column; align-items:center; gap:10px;';
        div.innerHTML = `
          <span style="font-size:13px; font-weight:800; color:#fbbf24; text-transform:uppercase;">${{label}}</span>
          <div style="width:160px; aspect-ratio:var(--tarot-ratio); border-radius:18px; border:2px dashed rgba(255,255,255,0.25); background:#000; display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden;" id="slot_${{idx}}" onclick="drawOneSlot(${{idx}})">
            <span style="font-size:32px; color:rgba(255,255,255,0.3);">✦</span>
          </div>
        `;
        row.appendChild(div);
      }});
    }}

    function dealSpreadAnimation() {{
      const count = currentSpreadList.length;
      const shuffled = [...TAROT_CARDS].sort(() => 0.5 - Math.random());
      for (let i = 0; i < count; i++) {{
        currentSpreadList[i] = shuffled[i];
        revealSlot(i, shuffled[i]);
      }}
      synthesizeSpread();
    }}

    function drawOneSlot(idx) {{
      if (currentSpreadList[idx]) {{
        openCardModal(currentSpreadList[idx]);
        return;
      }}
      const random = TAROT_CARDS[Math.floor(Math.random() * TAROT_CARDS.length)];
      currentSpreadList[idx] = random;
      revealSlot(idx, random);
      if (currentSpreadList.every(x => x !== null)) synthesizeSpread();
    }}

    function revealSlot(idx, card) {{
      const el = document.getElementById(`slot_${{idx}}`);
      if (!el) return;
      el.style.border = '1px solid rgba(255,255,255,0.4)';
      el.innerHTML = `<img src="${{card.img_rws}}" style="width:100%; height:100%; object-fit:contain; background:#000;" alt="${{card.name}}" />`;
    }}

    function synthesizeSpread() {{
      const box = document.getElementById('tirageInterpretationBox');
      box.style.display = 'block';

      let html = `<div style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); border-radius:24px; padding:28px;">
        <h3 style="font-size:24px; font-weight:900; color:#fff; margin-bottom:16px;">Synthèse du Tirage</h3>
        <div style="display:flex; flex-direction:column; gap:14px; color:#cbd5e1; font-size:15.5px;">
      `;

      currentSpreadList.forEach((c) => {{
        if (!c) return;
        html += `<div><strong style="color:#fbbf24;">${{c.name}} :</strong> ${{c.key_word}} — ${{c.idea || c.guidance.slice(0, 140)}}</div>`;
      }});

      html += `</div></div>`;
      box.innerHTML = html;
    }}

    // NAVIGATION
    function switchMainTab(tabKey) {{
      document.querySelectorAll('.nav-tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

      const btn = document.getElementById('tabBtn' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1));
      const content = document.getElementById('tabContent' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1));
      if (btn) btn.classList.add('active');
      if (content) content.classList.add('active');

      window.scrollTo({{ top: 0, behavior: 'smooth' }});
    }}

    // MODALS UTILS
    function toggleSearchModal() {{
      const el = document.getElementById('searchModalWrap');
      el.classList.toggle('active');
      if (el.classList.contains('active')) document.getElementById('quickSearchInput').focus();
    }}

    function handleQuickSearchLive() {{
      const q = document.getElementById('quickSearchInput').value.toLowerCase().trim();
      const listEl = document.getElementById('quickSearchResultsList');
      listEl.innerHTML = '';

      const matches = TAROT_CARDS.filter(c => 
        !q || c.name.toLowerCase().includes(q) || c.key_word.toLowerCase().includes(q) || c.element.toLowerCase().includes(q)
      ).slice(0, 8);

      matches.forEach(card => {{
        const div = document.createElement('div');
        div.style.cssText = 'display:flex; align-items:center; gap:14px; padding:10px 14px; border-radius:12px; cursor:pointer;';
        div.onmouseover = () => div.style.background = 'rgba(255,255,255,0.08)';
        div.onmouseout = () => div.style.background = 'transparent';
        div.onclick = () => {{
          toggleSearchModal();
          openCardModal(card);
        }};
        div.innerHTML = `
          <img src="${{card.img_rws}}" style="width:40px; aspect-ratio:var(--tarot-ratio); border-radius:6px; object-fit:contain; background:#000;" />
          <div>
            <div style="color:#fff; font-weight:800; font-size:15px;">${{card.name}}</div>
            <div style="color:var(--text-muted); font-size:12.5px;">${{card.category}} · ${{card.element}}</div>
          </div>
        `;
        listEl.appendChild(div);
      }});
    }}

    function openMysteryCard() {{
      const rand = TAROT_CARDS[Math.floor(Math.random() * TAROT_CARDS.length)];
      openCardModal(rand);
    }}

    function openSettingsModal() {{
      document.getElementById('settingsModal').classList.add('active');
    }}

    function closeSettingsModal() {{
      document.getElementById('settingsModal').classList.remove('active');
    }}

    function openProModal() {{
      document.getElementById('proModal').classList.add('active');
    }}

    function closeProModal() {{
      document.getElementById('proModal').classList.remove('active');
    }}

    function setTheme(theme) {{
      document.body.setAttribute('data-theme', theme);
    }}
  </script>
</body>
</html>
'''

with open('src/archives/wallspace2.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

print(f"Successfully generated uncropped wallspace2.html ({len(html_content)} bytes)")
