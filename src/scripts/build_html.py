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
  <title>TarotSpace — RWS Tarot Wallpaper & Oracle Experience</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;800;900&family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet" />
  
  <style>
    /* ==========================================================================
       RESET & BASE CSS
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

      /* Themes */
      --bg-desktop: #08090d;
      --window-bg: rgba(18, 19, 26, 0.88);
      --window-border: rgba(255, 255, 255, 0.12);
      --window-radius: 28px;
      
      --accent-hero: #e11d48;
      --accent-hero-glow: rgba(225, 29, 72, 0.35);
      --accent-gold: #eab308;
      --accent-amber: #f59e0b;
      
      --glass-bg: rgba(255, 255, 255, 0.07);
      --glass-bg-hover: rgba(255, 255, 255, 0.12);
      --glass-border: rgba(255, 255, 255, 0.12);
      --glass-border-hover: rgba(255, 255, 255, 0.25);
      
      --text-main: #f8fafc;
      --text-muted: rgba(248, 250, 252, 0.65);
      --text-dim: rgba(248, 250, 252, 0.4);
      
      --card-bg: rgba(25, 27, 38, 0.7);
      --card-border: rgba(255, 255, 255, 0.08);

      --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
      --ease-smooth: cubic-bezier(0.16, 1, 0.3, 1);
    }}

    /* THEME PRESETS */
    body[data-theme="crimson"] {{
      --accent-hero: #e11d48;
      --accent-hero-glow: rgba(225, 29, 72, 0.4);
    }}
    body[data-theme="space"] {{
      --accent-hero: #6366f1;
      --accent-hero-glow: rgba(99, 102, 241, 0.4);
    }}
    body[data-theme="gold"] {{
      --accent-hero: #d97706;
      --accent-hero-glow: rgba(217, 119, 6, 0.4);
    }}
    body[data-theme="azure"] {{
      --accent-hero: #0284c7;
      --accent-hero-glow: rgba(2, 132, 199, 0.4);
    }}
    body[data-theme="emerald"] {{
      --accent-hero: #059669;
      --accent-hero-glow: rgba(5, 150, 105, 0.4);
    }}
    body[data-theme="amethyst"] {{
      --accent-hero: #7e22ce;
      --accent-hero-glow: rgba(126, 34, 206, 0.4);
    }}

    html, body {{
      width: 100%;
      min-height: 100vh;
      background: var(--bg-desktop);
      color: var(--text-main);
      font-family: var(--font-sans);
      overflow-x: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
    }}

    /* DESKTOP WALLPAPER BACKGROUND (Earth atmosphere / cosmic stars) */
    .desktop-environment {{
      position: fixed;
      inset: 0;
      z-index: 0;
      background: radial-gradient(circle at 10% 90%, rgba(30, 64, 175, 0.35) 0%, transparent 50%),
                  radial-gradient(circle at 90% 10%, rgba(147, 51, 234, 0.25) 0%, transparent 45%),
                  radial-gradient(circle at 50% 110%, rgba(56, 189, 248, 0.3) 0%, rgba(15, 23, 42, 0.8) 40%, #030712 90%);
      overflow: hidden;
      pointer-events: none;
    }}

    .desktop-stars {{
      position: absolute;
      inset: 0;
      background-image: 
        radial-gradient(1.5px 1.5px at 40px 60px, rgba(255,255,255,0.7), rgba(0,0,0,0)),
        radial-gradient(1px 1px at 150px 220px, rgba(255,255,255,0.8), rgba(0,0,0,0)),
        radial-gradient(1.5px 1.5px at 300px 120px, rgba(255,255,255,0.6), rgba(0,0,0,0)),
        radial-gradient(2px 2px at 500px 350px, rgba(255,255,255,0.85), rgba(0,0,0,0)),
        radial-gradient(1px 1px at 720px 180px, rgba(255,255,255,0.5), rgba(0,0,0,0)),
        radial-gradient(1.5px 1.5px at 900px 420px, rgba(255,255,255,0.75), rgba(0,0,0,0)),
        radial-gradient(2px 2px at 1100px 150px, rgba(255,255,255,0.9), rgba(0,0,0,0)),
        radial-gradient(1px 1px at 1300px 380px, rgba(255,255,255,0.6), rgba(0,0,0,0));
      background-size: 800px 600px;
      opacity: 0.6;
    }}

    .desktop-earth-glow {{
      position: absolute;
      bottom: -400px;
      left: -20%;
      right: -20%;
      height: 700px;
      border-radius: 50%;
      background: radial-gradient(ellipse at top, rgba(96, 165, 250, 0.45) 0%, rgba(30, 58, 138, 0.2) 40%, transparent 75%);
      filter: blur(50px);
      transform: rotate(-3deg);
    }}

    /* ==========================================================================
       MAIN APP WINDOW (WALLSPACE MACOS WINDOW)
       ========================================================================== */
    .app-window {{
      position: relative;
      z-index: 10;
      width: min(1520px, 96vw);
      height: min(920px, 94vh);
      background: var(--window-bg);
      backdrop-filter: blur(45px) saturate(190%);
      -webkit-backdrop-filter: blur(45px) saturate(190%);
      border: 1px solid var(--window-border);
      border-radius: var(--window-radius);
      box-shadow: 
        0 35px 100px -15px rgba(0, 0, 0, 0.85),
        0 0 0 1px rgba(255, 255, 255, 0.08),
        0 0 80px rgba(0, 0, 0, 0.5);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: all 0.4s var(--ease-smooth);
    }}

    .app-window.is-fullscreen {{
      width: 100vw;
      height: 100vh;
      border-radius: 0;
      border: none;
    }}

    /* ==========================================================================
       APP HEADER / NAVBAR
       ========================================================================== */
    .app-header {{
      position: relative;
      z-index: 50;
      height: 68px;
      padding: 0 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.03) 0%, transparent 100%);
      flex-shrink: 0;
    }}

    /* Window Controls (Traffic lights) & Logo */
    .header-left {{
      display: flex;
      align-items: center;
      gap: 20px;
    }}

    .window-traffic-lights {{
      display: flex;
      align-items: center;
      gap: 8px;
    }}

    .traffic-dot {{
      width: 12px;
      height: 12px;
      border-radius: 50%;
      position: relative;
      cursor: pointer;
      box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.25);
      transition: transform 0.2s, filter 0.2s;
    }}
    .traffic-dot:hover {{
      transform: scale(1.15);
      filter: brightness(1.2);
    }}
    .dot-close {{ background: #ff5f56; border: 1px solid #e0443e; }}
    .dot-minimize {{ background: #ffbd2e; border: 1px solid #dea123; }}
    .dot-fullscreen {{ background: #27c93f; border: 1px solid #1aab29; }}

    .brand-logo {{
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      color: var(--text-main);
      font-weight: 700;
      font-size: 19px;
      letter-spacing: -0.02em;
      cursor: pointer;
    }}

    .brand-icon-box {{
      width: 32px;
      height: 32px;
      background: #000;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(255, 255, 255, 0.15);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
      color: #fff;
      font-size: 16px;
    }}

    /* Center Pill Navigation */
    .header-nav-pill {{
      display: flex;
      align-items: center;
      background: rgba(0, 0, 0, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 4px;
      border-radius: 100px;
      gap: 4px;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
    }}

    .nav-pill-btn {{
      background: transparent;
      border: none;
      color: var(--text-muted);
      font-family: var(--font-sans);
      font-weight: 600;
      font-size: 13.5px;
      padding: 7px 20px;
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.25s var(--ease-smooth);
      display: flex;
      align-items: center;
      gap: 6px;
    }}

    .nav-pill-btn:hover {{
      color: var(--text-main);
    }}

    .nav-pill-btn.active {{
      background: #ffffff;
      color: #090a0f;
      font-weight: 700;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
    }}

    /* Right Action Buttons */
    .header-right {{
      display: flex;
      align-items: center;
      gap: 10px;
    }}

    .header-action-btn {{
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--text-main);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.2s var(--ease-smooth);
      position: relative;
    }}

    .header-action-btn:hover {{
      background: var(--glass-bg-hover);
      border-color: var(--glass-border-hover);
      transform: scale(1.05);
    }}

    .pro-badge-pill {{
      background: linear-gradient(135deg, #78350f 0%, #b45309 50%, #d97706 100%);
      color: #fef3c7;
      border: 1px solid rgba(245, 158, 11, 0.4);
      padding: 5px 15px;
      border-radius: 100px;
      font-weight: 800;
      font-size: 12px;
      letter-spacing: 0.05em;
      cursor: pointer;
      box-shadow: 0 2px 12px rgba(217, 119, 6, 0.35);
      transition: all 0.2s var(--ease-smooth);
      display: flex;
      align-items: center;
      gap: 5px;
    }}

    .pro-badge-pill:hover {{
      transform: scale(1.05);
      box-shadow: 0 4px 18px rgba(217, 119, 6, 0.55);
      border-color: rgba(254, 243, 199, 0.6);
    }}

    .header-round-btn {{
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: rgba(127, 29, 29, 0.7);
      border: 1px solid rgba(239, 68, 68, 0.35);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.2s;
    }}
    .header-round-btn:hover {{
      background: rgba(185, 28, 28, 0.9);
      transform: scale(1.06);
    }}

    /* ==========================================================================
       APP BODY / SCROLL CONTENT
       ========================================================================== */
    .app-content-viewport {{
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
      position: relative;
      scroll-behavior: smooth;
      padding-bottom: 60px;
    }}

    .app-content-viewport::-webkit-scrollbar {{
      width: 6px;
    }}
    .app-content-viewport::-webkit-scrollbar-track {{
      background: transparent;
    }}
    .app-content-viewport::-webkit-scrollbar-thumb {{
      background: rgba(255, 255, 255, 0.15);
      border-radius: 10px;
    }}

    /* TAB VIEWS */
    .tab-view {{
      display: none;
      animation: fadeInView 0.35s var(--ease-smooth) forwards;
    }}
    .tab-view.active {{
      display: block;
    }}

    @keyframes fadeInView {{
      from {{ opacity: 0; transform: translateY(8px); }}
      to {{ opacity: 1; transform: translateY(0); }}
    }}

    /* ==========================================================================
       HERO / FEATURED SECTION (WALLSPACE REPLICA)
       ========================================================================== */
    .hero-container {{
      padding: 20px 28px 24px 28px;
    }}

    .hero-card {{
      position: relative;
      width: 100%;
      height: 520px;
      border-radius: 26px;
      background: var(--hero-bg, radial-gradient(circle at 60% 40%, #e11d48 0%, #be123c 45%, #881337 85%));
      border: 1px solid rgba(255, 255, 255, 0.18);
      box-shadow: 0 20px 50px -10px var(--accent-hero-glow, rgba(225, 29, 72, 0.3));
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: background 0.6s var(--ease-smooth), box-shadow 0.6s var(--ease-smooth);
    }}

    /* Halftone Polka Dot Pattern (Wallspace signature style) */
    .hero-dots-pattern {{
      position: absolute;
      inset: 0;
      background-image: radial-gradient(var(--dots-color, rgba(255, 255, 255, 0.3)) 2.2px, transparent 2.2px);
      background-size: 26px 26px;
      opacity: 0.85;
      pointer-events: none;
    }}

    /* Dynamic cosmic subtle swirl */
    .hero-glow-overlay {{
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 80% 30%, rgba(255, 255, 255, 0.18) 0%, transparent 60%),
                  linear-gradient(180deg, transparent 40%, rgba(0, 0, 0, 0.75) 100%);
      pointer-events: none;
    }}

    /* Hero Center Card / Character Artwork Display */
    .hero-artwork-wrap {{
      position: absolute;
      right: 18%;
      top: 50%;
      transform: translateY(-52%);
      width: 320px;
      height: 470px;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 5;
      perspective: 1200px;
    }}

    .hero-tarot-card {{
      width: 250px;
      height: 410px;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.8),
        0 0 0 1px rgba(255, 255, 255, 0.25),
        0 0 35px rgba(255, 255, 255, 0.15);
      position: relative;
      cursor: pointer;
      transition: transform 0.4s var(--ease-smooth), box-shadow 0.4s var(--ease-smooth);
      background: #111;
    }}

    .hero-tarot-card:hover {{
      transform: translateY(-10px) rotate(-1deg) scale(1.03);
      box-shadow: 
        0 35px 70px -15px rgba(0, 0, 0, 0.9),
        0 0 0 2px rgba(255, 255, 255, 0.5),
        0 0 50px var(--accent-hero-glow, rgba(225, 29, 72, 0.5));
    }}

    .hero-tarot-card img {{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.5s var(--ease-smooth);
    }}

    .hero-tarot-card:hover img {{
      transform: scale(1.04);
    }}

    /* Card foil shine */
    .hero-tarot-card::after {{
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, transparent 40%, rgba(255,255,255,0.1) 80%, transparent 100%);
      pointer-events: none;
    }}

    /* Speech Bubble / Badge on the Hero (like Dream Chaser speech bubble ✨) */
    .hero-speech-bubble {{
      position: absolute;
      right: 10%;
      top: 32%;
      background: #ffffff;
      color: #0f172a;
      padding: 10px 18px;
      border-radius: 100px;
      font-size: 14px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
      z-index: 10;
      animation: floatBadge 3.5s ease-in-out infinite alternate;
    }}
    .hero-speech-bubble::before {{
      content: '';
      position: absolute;
      left: -8px;
      top: 50%;
      transform: translateY(-50%);
      border-width: 6px 8px 6px 0;
      border-style: solid;
      border-color: transparent #ffffff transparent transparent;
    }}

    @keyframes floatBadge {{
      from {{ transform: translateY(0); }}
      to {{ transform: translateY(-8px); }}
    }}

    /* Hero Left Information Box */
    .hero-info-box {{
      position: relative;
      z-index: 10;
      padding: 42px 42px 0 42px;
      max-width: 580px;
    }}

    .hero-badge-tag {{
      display: inline-block;
      font-size: 11.5px;
      font-weight: 800;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 10px;
    }}

    .hero-card-title {{
      font-size: clamp(34px, 4vw, 48px);
      font-weight: 800;
      line-height: 1.1;
      color: #ffffff;
      margin-bottom: 12px;
      letter-spacing: -0.03em;
      text-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }}

    .hero-meta-row {{
      display: flex;
      align-items: center;
      gap: 14px;
      font-size: 14px;
      font-weight: 500;
      color: rgba(255, 255, 255, 0.75);
      margin-bottom: 24px;
    }}

    .hero-meta-dot {{
      width: 4px;
      height: 4px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.4);
    }}

    /* Hero Action Buttons */
    .hero-buttons-row {{
      display: flex;
      align-items: center;
      gap: 14px;
    }}

    .hero-primary-btn {{
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border: 1px solid rgba(255, 255, 255, 0.4);
      color: #ffffff;
      font-family: var(--font-sans);
      font-size: 14.5px;
      font-weight: 700;
      padding: 12px 26px;
      border-radius: 100px;
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
      transition: all 0.25s var(--ease-smooth);
    }}

    .hero-primary-btn:hover {{
      background: #ffffff;
      color: #0f172a;
      border-color: #ffffff;
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
    }}

    .hero-icon-pill-btn {{
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      cursor: pointer;
      transition: all 0.25s var(--ease-smooth);
    }}

    .hero-icon-pill-btn:hover {{
      background: rgba(255, 255, 255, 0.3);
      transform: scale(1.08);
    }}

    .hero-icon-pill-btn.is-fav {{
      color: #f43f5e;
      background: rgba(255, 255, 255, 0.9);
    }}

    /* ==========================================================================
       HERO THUMBNAILS CAROUSEL (BOTTOM OF HERO BAR)
       ========================================================================== */
    .hero-thumbnails-bar {{
      position: relative;
      z-index: 20;
      padding: 0 32px 28px 32px;
      display: flex;
      align-items: center;
      gap: 14px;
      overflow-x: auto;
      scroll-behavior: smooth;
    }}

    .hero-thumbnails-bar::-webkit-scrollbar {{
      display: none;
    }}

    .hero-thumb-card {{
      flex: 0 0 100px;
      height: 64px;
      border-radius: 12px;
      overflow: hidden;
      position: relative;
      cursor: pointer;
      border: 2px solid transparent;
      opacity: 0.6;
      transition: all 0.25s var(--ease-smooth);
      background: #111;
    }}

    .hero-thumb-card:hover {{
      opacity: 0.9;
      transform: translateY(-3px) scale(1.03);
    }}

    .hero-thumb-card.active {{
      opacity: 1;
      border-color: #ffffff;
      box-shadow: 0 0 0 2px var(--accent-hero, #e11d48), 0 4px 16px rgba(0, 0, 0, 0.6);
      transform: translateY(-4px) scale(1.05);
    }}

    .hero-thumb-card img {{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }}

    /* ==========================================================================
       CURATED SECTIONS (WALLSPACE'S PICK & COLLECTIONS)
       ========================================================================== */
    .curated-section {{
      padding: 10px 28px 36px 28px;
    }}

    .section-header-row {{
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 18px;
    }}

    .section-title-wrap h2 {{
      font-size: 22px;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: -0.02em;
      margin-bottom: 4px;
    }}

    .section-title-wrap p {{
      font-size: 13.5px;
      color: var(--text-muted);
    }}

    .section-nav-arrows {{
      display: flex;
      align-items: center;
      gap: 8px;
    }}

    .section-arrow-btn {{
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--text-main);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
    }}

    .section-arrow-btn:hover {{
      background: var(--glass-bg-hover);
      border-color: var(--glass-border-hover);
    }}

    /* Horizontal Cards Carousel */
    .cards-horizontal-strip {{
      display: flex;
      gap: 18px;
      overflow-x: auto;
      padding-bottom: 8px;
      scroll-behavior: smooth;
    }}

    .cards-horizontal-strip::-webkit-scrollbar {{
      height: 4px;
    }}
    .cards-horizontal-strip::-webkit-scrollbar-track {{
      background: transparent;
    }}
    .cards-horizontal-strip::-webkit-scrollbar-thumb {{
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
    }}

    .wallspace-item-card {{
      flex: 0 0 280px;
      height: 175px;
      border-radius: 18px;
      overflow: hidden;
      position: relative;
      cursor: pointer;
      border: 1px solid var(--card-border);
      background: var(--card-bg);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
      transition: all 0.3s var(--ease-smooth);
    }}

    .wallspace-item-card:hover {{
      transform: translateY(-6px) scale(1.02);
      border-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 16px 35px rgba(0, 0, 0, 0.5), 0 0 20px var(--accent-hero-glow);
    }}

    .wallspace-item-card img {{
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s var(--ease-smooth);
    }}

    .wallspace-item-card:hover img {{
      transform: scale(1.08);
    }}

    .wallspace-card-overlay {{
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.85) 100%);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 14px;
      z-index: 2;
    }}

    .card-top-badges {{
      display: flex;
      justify-content: space-between;
      align-items: center;
    }}

    .item-pro-tag {{
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(8px);
      color: #fbbf24;
      border: 1px solid rgba(251, 191, 36, 0.4);
      font-size: 10px;
      font-weight: 800;
      padding: 3px 8px;
      border-radius: 6px;
      letter-spacing: 0.05em;
    }}

    .item-element-tag {{
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(8px);
      color: #ffffff;
      font-size: 10.5px;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 6px;
    }}

    .card-bottom-info h3 {{
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 2px;
      letter-spacing: -0.01em;
    }}

    .card-bottom-info p {{
      font-size: 12px;
      color: var(--text-muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }}

    /* Suite Tabs */
    .suite-nav-pills {{
      display: flex;
      gap: 10px;
      margin-bottom: 18px;
      flex-wrap: wrap;
    }}

    .suite-tab-btn {{
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      font-family: var(--font-sans);
      font-size: 13px;
      font-weight: 600;
      padding: 8px 18px;
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 6px;
    }}

    .suite-tab-btn:hover {{
      color: var(--text-main);
      background: var(--glass-bg-hover);
    }}

    .suite-tab-btn.active {{
      background: var(--text-main);
      color: #0f172a;
      font-weight: 700;
      border-color: #ffffff;
    }}

    /* Spread Cards Grid */
    .spread-cards-grid {{
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 18px;
    }}

    .spread-promo-card {{
      background: linear-gradient(145deg, rgba(30, 32, 48, 0.8) 0%, rgba(18, 19, 28, 0.95) 100%);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 24px;
      cursor: pointer;
      transition: all 0.3s var(--ease-smooth);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 180px;
    }}

    .spread-promo-card:hover {{
      transform: translateY(-5px);
      border-color: rgba(255, 255, 255, 0.25);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
    }}

    .spread-icon-circle {{
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-bottom: 14px;
    }}

    .spread-promo-card h4 {{
      font-size: 17px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 6px;
    }}

    .spread-promo-card p {{
      font-size: 13px;
      color: var(--text-muted);
      line-height: 1.4;
    }}

    .spread-cta {{
      margin-top: 16px;
      font-size: 13px;
      font-weight: 700;
      color: #fbbf24;
      display: flex;
      align-items: center;
      gap: 4px;
    }}

    /* ==========================================================================
       EXPLORE TAB (FULL 78 CARDS GALLERY)
       ========================================================================== */
    .explore-container {{
      padding: 28px;
    }}

    .explore-toolbar {{
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 24px;
    }}

    .explore-search-input-wrap {{
      position: relative;
      flex: 1;
      min-width: 260px;
      max-width: 480px;
    }}

    .explore-search-input {{
      width: 100%;
      background: rgba(0, 0, 0, 0.4);
      border: 1px solid var(--glass-border);
      border-radius: 100px;
      padding: 11px 20px 11px 44px;
      color: #fff;
      font-family: var(--font-sans);
      font-size: 14px;
      outline: none;
      transition: all 0.2s;
    }}

    .explore-search-input:focus {{
      border-color: rgba(255, 255, 255, 0.4);
      background: rgba(0, 0, 0, 0.6);
      box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
    }}

    .explore-search-icon {{
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-dim);
      font-size: 15px;
    }}

    .explore-filter-chips {{
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }}

    .filter-chip {{
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      font-family: var(--font-sans);
      font-size: 13px;
      font-weight: 600;
      padding: 7px 16px;
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.2s;
    }}

    .filter-chip:hover {{
      color: #fff;
      background: var(--glass-bg-hover);
    }}

    .filter-chip.active {{
      background: #fff;
      color: #0f172a;
      font-weight: 700;
    }}

    .cards-grid-78 {{
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 20px;
    }}

    .grid-tarot-card {{
      background: rgba(20, 22, 32, 0.85);
      border: 1px solid var(--card-border);
      border-radius: 16px;
      overflow: hidden;
      cursor: pointer;
      transition: all 0.3s var(--ease-smooth);
      position: relative;
      display: flex;
      flex-direction: column;
    }}

    .grid-tarot-card:hover {{
      transform: translateY(-6px) scale(1.02);
      border-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.6);
    }}

    .grid-card-img-wrap {{
      width: 100%;
      aspect-ratio: 2/3.3;
      overflow: hidden;
      position: relative;
      background: #090a0f;
    }}

    .grid-card-img-wrap img {{
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s var(--ease-smooth);
    }}

    .grid-tarot-card:hover .grid-card-img-wrap img {{
      transform: scale(1.06);
    }}

    .grid-card-badge {{
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(8px);
      color: #fbbf24;
      font-size: 10px;
      font-weight: 800;
      padding: 3px 8px;
      border-radius: 6px;
      border: 1px solid rgba(251, 191, 36, 0.3);
    }}

    .grid-card-info {{
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }}

    .grid-card-name {{
      font-size: 14.5px;
      font-weight: 700;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }}

    .grid-card-sub {{
      font-size: 11.5px;
      color: var(--text-muted);
      display: flex;
      justify-content: space-between;
    }}

    /* ==========================================================================
       LIBRARY TAB (FAVORITES & HISTORY)
       ========================================================================== */
    .library-container {{
      padding: 28px;
    }}

    .library-section-title {{
      font-size: 20px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 16px;
    }}

    .empty-library-state {{
      text-align: center;
      padding: 60px 20px;
      color: var(--text-muted);
    }}

    .empty-library-state .empty-icon {{
      font-size: 48px;
      margin-bottom: 16px;
      opacity: 0.5;
    }}

    /* ==========================================================================
       FULL CARD DETAIL & WALLPAPER MODAL
       ========================================================================== */
    .modal-backdrop {{
      position: fixed;
      inset: 0;
      z-index: 100;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s var(--ease-smooth);
    }}

    .modal-backdrop.active {{
      opacity: 1;
      pointer-events: auto;
    }}

    .modal-window {{
      width: min(1180px, 95vw);
      max-height: min(840px, 90vh);
      background: #12141e;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 24px;
      box-shadow: 0 30px 90px rgba(0, 0, 0, 0.9), 0 0 0 1px rgba(255, 255, 255, 0.08);
      display: flex;
      overflow: hidden;
      position: relative;
      transform: scale(0.95);
      transition: transform 0.3s var(--ease-smooth);
    }}

    .modal-backdrop.active .modal-window {{
      transform: scale(1);
    }}

    .modal-close-btn {{
      position: absolute;
      top: 18px;
      right: 18px;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      cursor: pointer;
      z-index: 10;
      transition: all 0.2s;
    }}

    .modal-close-btn:hover {{
      background: rgba(255, 255, 255, 0.25);
      transform: scale(1.08);
    }}

    /* Modal Left: 3D Card Viewer */
    .modal-card-stage {{
      flex: 0 0 420px;
      background: linear-gradient(180deg, rgba(25, 28, 42, 0.8) 0%, rgba(10, 11, 18, 0.95) 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.08);
      padding: 36px 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      position: relative;
    }}

    .modal-tarot-3d-wrap {{
      perspective: 1000px;
      width: 240px;
      height: 390px;
      margin-top: 10px;
    }}

    .modal-tarot-3d-card {{
      width: 100%;
      height: 100%;
      position: relative;
      transform-style: preserve-3d;
      transition: transform 0.6s var(--ease-smooth);
      border-radius: 16px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.3);
    }}

    .modal-tarot-3d-card.is-flipped {{
      transform: rotateY(180deg);
    }}

    .card-face {{
      position: absolute;
      inset: 0;
      backface-visibility: hidden;
      border-radius: 16px;
      overflow: hidden;
    }}

    .card-face-front img {{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }}

    .card-face-back {{
      transform: rotateY(180deg);
      background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #090a0f 100%);
      border: 3px solid #d4af37;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #d4af37;
      font-size: 48px;
    }}

    .modal-stage-controls {{
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }}

    .variant-selector-row {{
      display: flex;
      justify-content: center;
      gap: 6px;
    }}

    .variant-pill {{
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: var(--text-muted);
      font-size: 11.5px;
      font-weight: 600;
      padding: 5px 12px;
      border-radius: 100px;
      cursor: pointer;
      transition: all 0.2s;
    }}

    .variant-pill:hover {{
      color: #fff;
      background: rgba(255, 255, 255, 0.15);
    }}

    .variant-pill.active {{
      background: #fff;
      color: #0f172a;
      font-weight: 700;
    }}

    .modal-stage-btn-row {{
      display: flex;
      gap: 8px;
      width: 100%;
    }}

    .modal-stage-btn {{
      flex: 1;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: #fff;
      font-family: var(--font-sans);
      font-size: 13px;
      font-weight: 600;
      padding: 10px;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.2s;
    }}

    .modal-stage-btn:hover {{
      background: var(--glass-bg-hover);
      border-color: rgba(255, 255, 255, 0.3);
    }}

    /* Modal Right: Interpretation Knowledge Base */
    .modal-reading-body {{
      flex: 1;
      overflow-y: auto;
      padding: 36px 36px 40px 36px;
      display: flex;
      flex-direction: column;
    }}

    .modal-card-header {{
      margin-bottom: 24px;
    }}

    .modal-tag-row {{
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
    }}

    .modal-category-badge {{
      font-size: 11.5px;
      font-weight: 800;
      color: #fbbf24;
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }}

    .modal-card-title {{
      font-size: 32px;
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.02em;
      margin-bottom: 6px;
    }}

    .modal-card-quote {{
      font-family: var(--font-prose);
      font-style: italic;
      font-size: 18px;
      color: #e2e8f0;
      line-height: 1.5;
      border-left: 3px solid #fbbf24;
      padding-left: 14px;
      margin: 12px 0 20px 0;
    }}

    /* Tabs inside reading modal */
    .modal-reading-tabs {{
      display: flex;
      gap: 6px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding-bottom: 12px;
      margin-bottom: 20px;
      overflow-x: auto;
    }}

    .modal-tab-link {{
      background: transparent;
      border: none;
      color: var(--text-muted);
      font-family: var(--font-sans);
      font-size: 13.5px;
      font-weight: 600;
      padding: 6px 14px;
      border-radius: 8px;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s;
    }}

    .modal-tab-link:hover {{
      color: #fff;
      background: rgba(255, 255, 255, 0.05);
    }}

    .modal-tab-link.active {{
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
      font-weight: 700;
    }}

    .modal-tab-pane {{
      display: none;
      line-height: 1.7;
      font-size: 15px;
      color: #cbd5e1;
    }}

    .modal-tab-pane.active {{
      display: block;
      animation: fadeInTab 0.25s ease forwards;
    }}

    @keyframes fadeInTab {{
      from {{ opacity: 0; transform: translateY(4px); }}
      to {{ opacity: 1; transform: translateY(0); }}
    }}

    .keywords-pills-wrap {{
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin: 12px 0 20px 0;
    }}

    .keyword-pill {{
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.15);
      padding: 4px 12px;
      border-radius: 100px;
      font-size: 12.5px;
      color: #f1f5f9;
    }}

    .keyword-pill.reversed {{
      border-color: rgba(239, 68, 68, 0.3);
      color: #fca5a5;
    }}

    /* ==========================================================================
       TIRAGE INTERACTIVE ENGINE MODAL
       ========================================================================== */
    .spread-arena {{
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 30px;
      padding: 20px;
    }}

    .spread-slots-row {{
      display: flex;
      justify-content: center;
      gap: 24px;
      flex-wrap: wrap;
    }}

    .spread-slot-item {{
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }}

    .spread-slot-label {{
      font-size: 13px;
      font-weight: 700;
      color: #fbbf24;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }}

    .spread-card-placeholder {{
      width: 150px;
      height: 240px;
      border-radius: 14px;
      border: 2px dashed rgba(255, 255, 255, 0.2);
      background: rgba(0, 0, 0, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: all 0.3s;
    }}

    .spread-card-placeholder.revealed {{
      border: 1px solid rgba(255, 255, 255, 0.4);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
    }}

    .spread-card-placeholder img {{
      width: 100%;
      height: 100%;
      object-fit: cover;
    }}

    .spread-controls-bar {{
      display: flex;
      gap: 14px;
    }}

    .spread-action-btn {{
      background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
      color: #fff;
      font-family: var(--font-sans);
      font-size: 15px;
      font-weight: 700;
      padding: 12px 30px;
      border-radius: 100px;
      border: none;
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(225, 29, 72, 0.4);
      transition: all 0.2s;
    }}

    .spread-action-btn:hover {{
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(225, 29, 72, 0.6);
    }}

    /* ==========================================================================
       SETTINGS & SEARCH OVERLAYS
       ========================================================================== */
    .quick-search-overlay {{
      position: fixed;
      top: 90px;
      left: 50%;
      transform: translateX(-50%) translateY(-10px);
      width: min(650px, 92vw);
      background: rgba(18, 20, 30, 0.95);
      backdrop-filter: blur(30px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85);
      z-index: 120;
      padding: 16px;
      display: none;
    }}

    .quick-search-overlay.active {{
      display: block;
      animation: fadeInView 0.2s ease forwards;
    }}

    .search-result-item {{
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 10px 14px;
      border-radius: 10px;
      cursor: pointer;
      transition: background 0.15s;
    }}

    .search-result-item:hover {{
      background: rgba(255, 255, 255, 0.08);
    }}

    .search-result-thumb {{
      width: 36px;
      height: 56px;
      border-radius: 6px;
      object-fit: cover;
    }}

    /* Responsive Queries */
    @media (max-width: 960px) {{
      .app-window {{
        width: 100vw;
        height: 100vh;
        border-radius: 0;
      }}
      .hero-artwork-wrap {{
        display: none;
      }}
      .modal-window {{
        flex-direction: column;
        max-height: 94vh;
      }}
      .modal-card-stage {{
        flex: 0 0 auto;
        padding: 20px;
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      }}
      .modal-tarot-3d-wrap {{
        width: 150px;
        height: 240px;
      }}
    }}
  </style>
</head>
<body data-theme="crimson">

  <!-- Desktop Orbit Atmosphere -->
  <div class="desktop-environment">
    <div class="desktop-stars"></div>
    <div class="desktop-earth-glow"></div>
  </div>

  <!-- macOS WallSpace Application Window -->
  <div class="app-window" id="appWindow">
    
    <!-- Top Floating Header Bar -->
    <header class="app-header">
      
      <!-- Left: macOS Traffic Lights & Brand -->
      <div class="header-left">
        <div class="window-traffic-lights">
          <div class="traffic-dot dot-close" title="Fermer" onclick="closeWindowNotify()"></div>
          <div class="traffic-dot dot-minimize" title="Réduire" onclick="toggleWindowFullscreen()"></div>
          <div class="traffic-dot dot-fullscreen" title="Plein écran" onclick="toggleWindowFullscreen()"></div>
        </div>

        <div class="brand-logo" onclick="switchNavTab('home')">
          <div class="brand-icon-box">✦</div>
          <span>TarotSpace</span>
        </div>
      </div>

      <!-- Center: Pill Navigation -->
      <nav class="header-nav-pill">
        <button class="nav-pill-btn active" id="pillHome" onclick="switchNavTab('home')">Home</button>
        <button class="nav-pill-btn" id="pillExplore" onclick="switchNavTab('explore')">Explore</button>
        <button class="nav-pill-btn" id="pillLibrary" onclick="switchNavTab('library')">Library</button>
        <button class="nav-pill-btn" id="pillTirage" onclick="switchNavTab('tirage')">Tirages</button>
      </nav>

      <!-- Right: Action Controls -->
      <div class="header-right">
        <button class="header-action-btn" title="Oracle Mystère Express (Cadeau)" onclick="openMysteryGift()">🎁</button>
        <button class="header-action-btn" title="Recherche rapide (⌘K)" onclick="toggleSearchOverlay()">🔍</button>
        <div class="pro-badge-pill" onclick="openProModal()">PRO</div>
        <button class="header-round-btn" title="Nouveau Tirage" onclick="switchNavTab('tirage')">+</button>
        <button class="header-round-btn" title="Thèmes & Paramètres" onclick="openSettingsModal()">⚙</button>
      </div>
    </header>

    <!-- Scrollable Content Viewport -->
    <div class="app-content-viewport" id="contentViewport">
      
      <!-- =========================================================================
           TAB 1: HOME VIEW (WALLSPACE EXACT REPLICA)
           ========================================================================= -->
      <div class="tab-view active" id="viewHome">
        
        <!-- HERO / FEATURED BANNER SECTION -->
        <section class="hero-container">
          <div class="hero-card" id="heroCardBanner">
            <div class="hero-dots-pattern" id="heroDotsPattern"></div>
            <div class="hero-glow-overlay"></div>

            <!-- Speech bubble badge like Wallspace -->
            <div class="hero-speech-bubble" id="heroSpeechBubble">
              ✨ ✦ 0 · Le Saut dans l'Inconnu
            </div>

            <!-- 3D Card Artwork in Center/Right -->
            <div class="hero-artwork-wrap">
              <div class="hero-tarot-card" id="heroTarotCard" onclick="openActiveHeroDetail()">
                <img src="benchmarks/cards_alt/a_00_Fou.jpg" id="heroCardImg" alt="Featured Tarot Card" />
              </div>
            </div>

            <!-- Left Info Box -->
            <div class="hero-info-box">
              <span class="hero-badge-tag" id="heroCategoryBadge">FEATURED</span>
              <h1 class="hero-card-title" id="heroCardTitle">Le Fou</h1>
              
              <div class="hero-meta-row">
                <span id="heroMetaSuit">Arcane Majeur 0</span>
                <span class="hero-meta-dot"></span>
                <span id="heroMetaAstro">Air · Uranus</span>
                <span class="hero-meta-dot"></span>
                <span>RWS 1909 Classic</span>
                <span class="hero-meta-dot"></span>
                <span>HD 4K</span>
              </div>

              <div class="hero-buttons-row">
                <button class="hero-primary-btn" onclick="openActiveHeroDetail()">
                  <span>Découvrir l'Arcane</span>
                  <span>↗</span>
                </button>
                <button class="hero-icon-pill-btn" id="heroFavBtn" title="Ajouter aux favoris" onclick="toggleActiveHeroFav()">
                  🤍
                </button>
                <button class="hero-primary-btn" style="background: rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.2);" onclick="drawActiveHeroInSpread()">
                  <span>🎴 Tirer cette lame</span>
                </button>
              </div>
            </div>

            <!-- Bottom Floating Thumbnails Bar (Wallspace Replica) -->
            <div class="hero-thumbnails-bar" id="heroThumbnailsBar">
              <!-- Dynamically populated -->
            </div>
          </div>
        </section>

        <!-- SECTION 1: WallSpace's Pick (TarotSpace's Pick) -->
        <section class="curated-section">
          <div class="section-header-row">
            <div class="section-title-wrap">
              <h2>TarotSpace's Pick</h2>
              <p>Sélection curatée des plus puissantes lames du Rider-Waite-Smith</p>
            </div>
            <div class="section-nav-arrows">
              <button class="section-arrow-btn" onclick="scrollCarousel('picksStrip', -300)">❮</button>
              <button class="section-arrow-btn" onclick="scrollCarousel('picksStrip', 300)">❯</button>
            </div>
          </div>

          <div class="cards-horizontal-strip" id="picksStrip">
            <!-- Populated via JS -->
          </div>
        </section>

        <!-- SECTION 2: Les 22 Arcanes Majeurs -->
        <section class="curated-section">
          <div class="section-header-row">
            <div class="section-title-wrap">
              <h2>Les 22 Arcanes Majeurs</h2>
              <p>Le voyage initiatique de l'Âme, du Fou (0) au Monde (XXI)</p>
            </div>
            <div class="section-nav-arrows">
              <button class="section-arrow-btn" onclick="scrollCarousel('majorsStrip', -300)">❮</button>
              <button class="section-arrow-btn" onclick="scrollCarousel('majorsStrip', 300)">❯</button>
            </div>
          </div>

          <div class="cards-horizontal-strip" id="majorsStrip">
            <!-- Populated via JS -->
          </div>
        </section>

        <!-- SECTION 3: Les 4 Suites Élémentaires -->
        <section class="curated-section">
          <div class="section-header-row">
            <div class="section-title-wrap">
              <h2>Les Suites Élémentaires (Arcanes Mineurs)</h2>
              <p>Feu, Eau, Air et Terre : l'incarnation concrète du quotidien</p>
            </div>
          </div>

          <div class="suite-nav-pills">
            <button class="suite-tab-btn active" onclick="switchSuiteStrip('batons', this)">🔥 Bâtons (Feu)</button>
            <button class="suite-tab-btn" onclick="switchSuiteStrip('coupes', this)">💧 Coupes (Eau)</button>
            <button class="suite-tab-btn" onclick="switchSuiteStrip('epees', this)">⚔️ Épées (Air)</button>
            <button class="suite-tab-btn" onclick="switchSuiteStrip('deniers', this)">🪙 Deniers (Terre)</button>
          </div>

          <div class="cards-horizontal-strip" id="suiteStrip">
            <!-- Populated via JS -->
          </div>
        </section>

        <!-- SECTION 4: Tirages & Oracles Divinatoires -->
        <section class="curated-section">
          <div class="section-header-row">
            <div class="section-title-wrap">
              <h2>Tirages & Pratiques Divinatoires</h2>
              <p>Expérimentez la sagesse du Tarot à travers nos oracles interactifs</p>
            </div>
          </div>

          <div class="spread-cards-grid">
            <div class="spread-promo-card" onclick="startSpreadMode('single')">
              <div>
                <div class="spread-icon-circle">🔮</div>
                <h4>Carte du Jour</h4>
                <p>Recevez l’énergie directrice et le conseil de votre journée.</p>
              </div>
              <div class="spread-cta">Lancer le tirage →</div>
            </div>

            <div class="spread-promo-card" onclick="startSpreadMode('three')">
              <div>
                <div class="spread-icon-circle">⏳</div>
                <h4>Passé · Présent · Futur</h4>
                <p>Explorez la dynamique temporelle et l’évolution de votre situation.</p>
              </div>
              <div class="spread-cta">Lancer le tirage →</div>
            </div>

            <div class="spread-promo-card" onclick="startSpreadMode('yesno')">
              <div>
                <div class="spread-icon-circle">⚡</div>
                <h4>Oracle Oui / Non</h4>
                <p>Une réponse claire et une affirmation guidée pour trancher un doute.</p>
              </div>
              <div class="spread-cta">Poser une question →</div>
            </div>

            <div class="spread-promo-card" onclick="startSpreadMode('cross')">
              <div>
                <div class="spread-icon-circle">✝️</div>
                <h4>Tirage en Croix (5 Lames)</h4>
                <p>Analyse complète : Atout, Obstacle, Conseil, Issue et Synthèse.</p>
              </div>
              <div class="spread-cta">Consulter l'oracle →</div>
            </div>
          </div>
        </section>

      </div>

      <!-- =========================================================================
           TAB 2: EXPLORE VIEW (FULL 78 CARDS BROWSER)
           ========================================================================= -->
      <div class="tab-view" id="viewExplore">
        <div class="explore-container">
          
          <div class="explore-toolbar">
            <div class="explore-search-input-wrap">
              <span class="explore-search-icon">🔍</span>
              <input type="text" class="explore-search-input" id="exploreSearchInput" placeholder="Rechercher une lame (ex: Bateleur, Amour, Étoile, Feu)..." oninput="filterExploreCards()" />
            </div>

            <div class="explore-filter-chips">
              <button class="filter-chip active" onclick="setExploreFilter('all', this)">Tous (78)</button>
              <button class="filter-chip" onclick="setExploreFilter('majors', this)">Majeurs (22)</button>
              <button class="filter-chip" onclick="setExploreFilter('batons', this)">Bâtons (14)</button>
              <button class="filter-chip" onclick="setExploreFilter('coupes', this)">Coupes (14)</button>
              <button class="filter-chip" onclick="setExploreFilter('epees', this)">Épées (14)</button>
              <button class="filter-chip" onclick="setExploreFilter('deniers', this)">Deniers (14)</button>
            </div>
          </div>

          <div class="cards-grid-78" id="exploreCardsGrid">
            <!-- Populated via JS -->
          </div>
        </div>
      </div>

      <!-- =========================================================================
           TAB 3: LIBRARY VIEW (SAVED CARDS & JOURNAL)
           ========================================================================= -->
      <div class="tab-view" id="viewLibrary">
        <div class="library-container">
          <div class="library-section-title">Mes Cartes Favorites ❤️</div>
          <div class="cards-grid-78" id="libraryFavsGrid">
            <!-- Populated via JS -->
          </div>

          <div class="library-section-title" style="margin-top: 40px;">Historique de mes Tirages 📜</div>
          <div id="libraryHistoryList">
            <!-- Populated via JS -->
          </div>
        </div>
      </div>

      <!-- =========================================================================
           TAB 4: TIRAGE INTERACTIVE ENGINE
           ========================================================================= -->
      <div class="tab-view" id="viewTirage">
        <div class="explore-container">
          <div class="section-title-wrap" style="text-align: center; margin-bottom: 24px;">
            <h2 id="tirageModeTitle">Tirage Divinatoire Interactif</h2>
            <p id="tirageModeDesc">Mélangez le jeu et tirez vos cartes avec guidance instantanée</p>
          </div>

          <div class="spread-arena">
            <div class="spread-slots-row" id="spreadSlotsRow">
              <!-- Dynamically populated slots -->
            </div>

            <div class="spread-controls-bar">
              <button class="spread-action-btn" onclick="shuffleAndDealSpread()">🔀 Battre & Tirer</button>
              <button class="hero-primary-btn" onclick="revealAllSpreadCards()">✨ Révéler Tout</button>
            </div>

            <div id="spreadInterpretationBox" style="width: 100%; max-width: 800px; display: none;">
              <!-- Reading Synthesis Card -->
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- =========================================================================
       FULL CARD DETAIL & WALLPAPER MODAL
       ========================================================================= -->
  <div class="modal-backdrop" id="cardDetailModal" onclick="handleBackdropClick(event)">
    <div class="modal-window" id="modalWindow">
      <button class="modal-close-btn" onclick="closeCardDetailModal()">✕</button>

      <!-- Modal Left: 3D Stage -->
      <div class="modal-card-stage">
        <div class="modal-tarot-3d-wrap" onclick="toggleCard3DFlip()">
          <div class="modal-tarot-3d-card" id="modal3DCard">
            <div class="card-face card-face-front">
              <img src="" id="modalCardImg" alt="Tarot Card Front" />
            </div>
            <div class="card-face card-face-back">
              ✦
            </div>
          </div>
        </div>

        <div class="modal-stage-controls">
          <div class="variant-selector-row">
            <button class="variant-pill active" id="pillRws" onclick="switchModalVariant('rws')">RWS 1909</button>
            <button class="variant-pill" id="pillBorderless" onclick="switchModalVariant('borderless')">Borderless</button>
            <button class="variant-pill" id="pillMarseille" onclick="switchModalVariant('marseille')">Marseille</button>
            <button class="variant-pill" id="pillFr" onclick="switchModalVariant('fr')">Français</button>
          </div>

          <div class="modal-stage-btn-row">
            <button class="modal-stage-btn" onclick="toggleModalFav()" id="modalFavBtn">❤️ Favoris</button>
            <button class="modal-stage-btn" onclick="downloadCardWallpaper()">📥 Fond HD</button>
            <button class="modal-stage-btn" onclick="toggleCard3DFlip()">🔄 Retourner</button>
          </div>
        </div>
      </div>

      <!-- Modal Right: Comprehensive Reading Tabs -->
      <div class="modal-reading-body">
        <div class="modal-card-header">
          <div class="modal-tag-row">
            <span class="modal-category-badge" id="modalCategoryBadge">ARCANE MAJEUR 0</span>
            <span class="hero-meta-dot"></span>
            <span id="modalElementAstro" style="font-size: 12px; color: var(--text-muted);">Air · Uranus</span>
          </div>
          <h2 class="modal-card-title" id="modalCardTitle">Le Fou</h2>
          <div class="modal-card-quote" id="modalCardQuote">« Un voyage de mille lieues commence toujours par un premier pas. »</div>
        </div>

        <div class="modal-reading-tabs">
          <button class="modal-tab-link active" onclick="switchModalReadingTab('guidance', this)">🧭 Guidance & Essence</button>
          <button class="modal-tab-link" onclick="switchModalReadingTab('love', this)">❤️ Amour</button>
          <button class="modal-tab-link" onclick="switchModalReadingTab('work', this)">💼 Travail</button>
          <button class="modal-tab-link" onclick="switchModalReadingTab('finance', this)">💰 Finances</button>
          <button class="modal-tab-link" onclick="switchModalReadingTab('cod', this)">🔮 Carte du Jour</button>
          <button class="modal-tab-link" onclick="switchModalReadingTab('visual', this)">🖼️ Symboles</button>
          <button class="modal-tab-link" onclick="switchModalReadingTab('keywords', this)">✨ Mots-clés</button>
        </div>

        <div class="modal-tab-pane active" id="tabPaneGuidance">
          <p id="modalGuidanceText"></p>
        </div>

        <div class="modal-tab-pane" id="tabPaneLove">
          <p id="modalLoveText"></p>
        </div>

        <div class="modal-tab-pane" id="tabPaneWork">
          <p id="modalWorkText"></p>
        </div>

        <div class="modal-tab-pane" id="tabPaneFinance">
          <p id="modalFinanceText"></p>
        </div>

        <div class="modal-tab-pane" id="tabPaneCod">
          <p id="modalCodText"></p>
        </div>

        <div class="modal-tab-pane" id="tabPaneVisual">
          <p id="modalVisualText"></p>
        </div>

        <div class="modal-tab-pane" id="tabPaneKeywords">
          <h4 style="color:#fff; margin-bottom: 6px;">À l'endroit (Énergie lumineuse) :</h4>
          <div class="keywords-pills-wrap" id="modalUprightKeywords"></div>
          
          <h4 style="color:#fff; margin: 16px 0 6px 0;">À l'envers (Défi ou blocage) :</h4>
          <div class="keywords-pills-wrap" id="modalReversedKeywords"></div>

          <div style="margin-top: 20px; padding: 14px; background: rgba(255,255,255,0.05); border-radius: 12px;">
            <strong>Affirmation :</strong> <span id="modalAffirmationText"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       QUICK SEARCH OVERLAY (COMMAND+K)
       ========================================================================= -->
  <div class="quick-search-overlay" id="quickSearchOverlay">
    <div style="position: relative; margin-bottom: 12px;">
      <input type="text" class="explore-search-input" id="quickSearchInput" placeholder="Rechercher parmi les 78 arcanes..." oninput="handleQuickSearch()" />
    </div>
    <div id="quickSearchResults" style="max-height: 380px; overflow-y: auto;"></div>
  </div>

  <!-- =========================================================================
       SETTINGS MODAL
       ========================================================================= -->
  <div class="modal-backdrop" id="settingsModal" onclick="if(event.target === this) closeSettingsModal()">
    <div class="modal-window" style="max-width: 540px; padding: 30px; flex-direction: column;">
      <h3 style="font-size: 22px; color: #fff; margin-bottom: 20px;">⚙️ Paramètres & Personnalisation</h3>
      
      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px;">THÈME DE COULEUR</label>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <button class="filter-chip" onclick="setTheme('crimson')">🔴 WallSpace Crimson</button>
          <button class="filter-chip" onclick="setTheme('space')">🌌 Deep Cosmic</button>
          <button class="filter-chip" onclick="setTheme('gold')">☀️ Solar Gold</button>
          <button class="filter-chip" onclick="setTheme('azure')">🌊 Arcana Azure</button>
          <button class="filter-chip" onclick="setTheme('emerald')">🌿 Mystic Emerald</button>
          <button class="filter-chip" onclick="setTheme('amethyst')">🔮 Void Amethyst</button>
        </div>
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px;">EFFETS SONORES MYSTIQUES</label>
        <button class="hero-primary-btn" id="audioToggleBtn" onclick="toggleAudioFx()">🔊 Audio activé</button>
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
        <button class="hero-primary-btn" onclick="closeSettingsModal()">Fermer</button>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       PRO PREVIEW MODAL
       ========================================================================= -->
  <div class="modal-backdrop" id="proModal" onclick="if(event.target === this) closeProModal()">
    <div class="modal-window" style="max-width: 580px; padding: 36px; flex-direction: column; text-align: center;">
      <div style="font-size: 40px; margin-bottom: 10px;">✨ ✦ 👑</div>
      <h3 style="font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 8px;">TarotSpace PRO</h3>
      <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 14.5px;">Accédez à l'expérience ésotérique ultime et personnalisée</p>
      
      <div style="text-align: left; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 16px; margin-bottom: 24px; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
        <div>✦ <strong>Pack Fonds d'Écran Ultra HD 4K</strong> (78 cartes avec métadonnées)</div>
        <div>✦ <strong>Lectures astrologiques complètes</strong> avec heures planétaires</div>
        <div>✦ <strong>Export PDF du Journal</strong> de tirages & synthèses</div>
        <div>✦ <strong>Variantes de Decks rares</strong> (Golden Dawn, Marseille 1760, CLM Borderless)</div>
      </div>

      <button class="hero-primary-btn" style="background: linear-gradient(135deg, #d97706, #b45309); width: 100%; justify-content: center;" onclick="closeProModal()">Activer l'accès illimité</button>
    </div>
  </div>

  <!-- =========================================================================
       JAVASCRIPT APPLICATION ENGINE
       ========================================================================= -->
  <script>
    const TAROT_CARDS = {cards_json_str};

    // State
    let currentHeroIndex = 0;
    let activeCardForModal = null;
    let activeModalVariant = 'rws';
    let isAudioEnabled = true;
    let audioCtx = null;
    let favorites = JSON.parse(localStorage.getItem('tarotspace_favs') || '["a_00_Fou", "a_01_Bateleur", "a_17_Etoile", "a_19_Soleil", "a_21_Monde"]');
    let drawHistory = JSON.parse(localStorage.getItem('tarotspace_history') || '[]');

    // Audio synthesizer for crystal clicks & mystic chimes
    function playTone(freq, type = 'sine', duration = 0.15, gainVal = 0.1) {{
      if (!isAudioEnabled) return;
      try {{
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = type;
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
        gain.gain.setValueAtTime(gainVal, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + duration);
      }} catch(e) {{}}
    }}

    function playMysticChime() {{
      [523.25, 659.25, 783.99, 1046.50].forEach((f, idx) => {{
        setTimeout(() => playTone(f, 'sine', 0.4, 0.08), idx * 70);
      }});
    }}

    // App Initialization
    document.addEventListener('DOMContentLoaded', () => {{
      initHeroThumbnails();
      updateHeroCard(0);
      populateCuratedPicks();
      populateMajorsStrip();
      populateSuiteStrip('batons');
      populateExploreGrid();
      updateLibraryView();

      // Keyboard shortcuts
      document.addEventListener('keydown', (e) => {{
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {{
          e.preventDefault();
          toggleSearchOverlay();
        }}
        if (e.key === 'Escape') {{
          closeCardDetailModal();
          closeSettingsModal();
          closeProModal();
          document.getElementById('quickSearchOverlay').classList.remove('active');
        }}
      }});
    }});

    // =========================================================================
    // HERO LOGIC
    // =========================================================================
    const FEATURED_HERO_IDS = [
      'a_00_Fou', 'a_01_Bateleur', 'a_02_Papesse', 'a_03_Impératrice',
      'a_06_Amoureux', 'a_08_Force', 'a_13_Mort', 'a_17_Etoile',
      'a_18_Lune', 'a_19_Soleil', 'a_21_Monde', 'c_01_As', 'b_01_As'
    ];

    function initHeroThumbnails() {{
      const bar = document.getElementById('heroThumbnailsBar');
      bar.innerHTML = '';
      
      FEATURED_HERO_IDS.forEach((cardId, index) => {{
        const card = TAROT_CARDS.find(c => c.id === cardId) || TAROT_CARDS[0];
        const thumb = document.createElement('div');
        thumb.className = `hero-thumb-card ${{index === 0 ? 'active' : ''}}`;
        thumb.id = `heroThumb_${{index}}`;
        thumb.title = card.name;
        thumb.onclick = () => {{
          playTone(440 + index * 30);
          updateHeroCard(index);
        }};
        thumb.innerHTML = `<img src="${{card.img_rws}}" alt="${{card.name}}" />`;
        bar.appendChild(thumb);
      }});
    }}

    function updateHeroCard(index) {{
      currentHeroIndex = index;
      const cardId = FEATURED_HERO_IDS[index] || FEATURED_HERO_IDS[0];
      const card = TAROT_CARDS.find(c => c.id === cardId) || TAROT_CARDS[0];

      // Update thumb active styles
      document.querySelectorAll('.hero-thumb-card').forEach((th, idx) => {{
        th.classList.toggle('active', idx === index);
      }});

      // Update Hero Elements
      const heroBanner = document.getElementById('heroCardBanner');
      heroBanner.style.background = card.hero_bg || 'radial-gradient(circle at 60% 40%, #e11d48 0%, #be123c 45%, #881337 85%)';
      heroBanner.style.setProperty('--accent-hero-glow', (card.color || '#e11d48') + '55');

      document.getElementById('heroDotsPattern').style.setProperty('--dots-color', card.dots_color || 'rgba(255,255,255,0.3)');
      document.getElementById('heroCardImg').src = card.img_rws;
      document.getElementById('heroCardTitle').innerText = card.name;
      document.getElementById('heroCategoryBadge').innerText = card.category.toUpperCase();
      document.getElementById('heroMetaSuit').innerText = card.category + (card.num_display ? ' ' + card.num_display : '');
      document.getElementById('heroMetaAstro').innerText = card.astro || card.element;
      document.getElementById('heroSpeechBubble').innerText = card.speech || `✨ ✦ ${{card.name}}`;

      const favBtn = document.getElementById('heroFavBtn');
      const isFav = favorites.includes(card.id);
      favBtn.innerText = isFav ? '❤️' : '🤍';
      favBtn.classList.toggle('is-fav', isFav);
    }}

    function openActiveHeroDetail() {{
      const cardId = FEATURED_HERO_IDS[currentHeroIndex] || FEATURED_HERO_IDS[0];
      const card = TAROT_CARDS.find(c => c.id === cardId);
      if (card) openCardDetailModal(card);
    }}

    function toggleActiveHeroFav() {{
      const cardId = FEATURED_HERO_IDS[currentHeroIndex];
      toggleFavorite(cardId);
      updateHeroCard(currentHeroIndex);
    }}

    function drawActiveHeroInSpread() {{
      switchNavTab('tirage');
      const cardId = FEATURED_HERO_IDS[currentHeroIndex];
      const card = TAROT_CARDS.find(c => c.id === cardId);
      if (card) {{
        startSingleDrawWithCard(card);
      }}
    }}

    // =========================================================================
    // CURATED SECTIONS POPULATION
    // =========================================================================
    function populateCuratedPicks() {{
      const strip = document.getElementById('picksStrip');
      strip.innerHTML = '';
      const picks = ['a_01_Bateleur', 'a_02_Papesse', 'a_03_Impératrice', 'a_17_Etoile', 'a_19_Soleil', 'a_21_Monde', 'c_01_As', 'd_01_As'];
      
      picks.forEach(id => {{
        const card = TAROT_CARDS.find(c => c.id === id);
        if (!card) return;
        strip.appendChild(createLandscapeCard(card));
      }});
    }}

    function populateMajorsStrip() {{
      const strip = document.getElementById('majorsStrip');
      strip.innerHTML = '';
      const majors = TAROT_CARDS.filter(c => c.prefix === 'a');
      majors.forEach(card => {{
        strip.appendChild(createLandscapeCard(card));
      }});
    }}

    function switchSuiteStrip(suiteKey, btn) {{
      playTone(500);
      document.querySelectorAll('.suite-tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      populateSuiteStrip(suiteKey);
    }}

    function populateSuiteStrip(suiteKey) {{
      const strip = document.getElementById('suiteStrip');
      strip.innerHTML = '';
      const suiteCards = TAROT_CARDS.filter(c => c.category_key === suiteKey);
      suiteCards.forEach(card => {{
        strip.appendChild(createLandscapeCard(card));
      }});
    }}

    function createLandscapeCard(card) {{
      const div = document.createElement('div');
      div.className = 'wallspace-item-card';
      div.onclick = () => openCardDetailModal(card);
      div.innerHTML = `
        <img src="${{card.img_rws}}" alt="${{card.name}}" loading="lazy" />
        <div class="wallspace-card-overlay">
          <div class="card-top-badges">
            <span class="item-pro-tag">PRO</span>
            <span class="item-element-tag">${{card.element_symbol}} ${{card.element}}</span>
          </div>
          <div class="card-bottom-info">
            <h3>${{card.name}}</h3>
            <p>${{card.key_word}} · ${{card.num_display}}</p>
          </div>
        </div>
      `;
      return div;
    }}

    function scrollCarousel(id, amount) {{
      playTone(600, 'triangle', 0.08);
      const el = document.getElementById(id);
      if (el) el.scrollBy({{ left: amount, behavior: 'smooth' }});
    }}

    // =========================================================================
    // EXPLORE GALLERY
    // =========================================================================
    let currentExploreFilter = 'all';

    function populateExploreGrid(filteredCards = null) {{
      const grid = document.getElementById('exploreCardsGrid');
      grid.innerHTML = '';
      const list = filteredCards || (currentExploreFilter === 'all' 
        ? TAROT_CARDS 
        : TAROT_CARDS.filter(c => c.category_key === currentExploreFilter));

      list.forEach(card => {{
        const el = document.createElement('div');
        el.className = 'grid-tarot-card';
        el.onclick = () => openCardDetailModal(card);
        el.innerHTML = `
          <div class="grid-card-img-wrap">
            <img src="${{card.img_rws}}" alt="${{card.name}}" loading="lazy" />
            <div class="grid-card-badge">${{card.num_display}}</div>
          </div>
          <div class="grid-card-info">
            <div class="grid-card-name">${{card.name}}</div>
            <div class="grid-card-sub">
              <span>${{card.element}}</span>
              <span>${{card.element_symbol}}</span>
            </div>
          </div>
        `;
        grid.appendChild(el);
      }});
    }}

    function setExploreFilter(categoryKey, btn) {{
      playTone(450);
      currentExploreFilter = categoryKey;
      document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      filterExploreCards();
    }}

    function filterExploreCards() {{
      const query = document.getElementById('exploreSearchInput').value.toLowerCase().trim();
      let list = currentExploreFilter === 'all' ? TAROT_CARDS : TAROT_CARDS.filter(c => c.category_key === currentExploreFilter);
      
      if (query) {{
        list = list.filter(c => 
          c.name.toLowerCase().includes(query) ||
          c.name_en.toLowerCase().includes(query) ||
          c.key_word.toLowerCase().includes(query) ||
          c.element.toLowerCase().includes(query) ||
          c.astro.toLowerCase().includes(query) ||
          c.idea.toLowerCase().includes(query)
        );
      }}
      populateExploreGrid(list);
    }}

    // =========================================================================
    // MODAL CARD DETAIL
    // =========================================================================
    function openCardDetailModal(card) {{
      activeCardForModal = card;
      activeModalVariant = 'rws';
      playMysticChime();

      document.getElementById('cardDetailModal').classList.add('active');
      document.getElementById('modalCardImg').src = card.img_rws;
      document.getElementById('modalCardTitle').innerText = card.name;
      document.getElementById('modalCategoryBadge').innerText = `${{card.category.toUpperCase()}} ${{card.num_display}}`;
      document.getElementById('modalElementAstro').innerText = `${{card.element_symbol}} ${{card.element}} · ${{card.astro || 'Tradition'}}`;
      document.getElementById('modalCardQuote').innerText = card.quote || `« ${{card.idea}} »`;

      // Texts
      document.getElementById('modalGuidanceText').innerHTML = card.guidance ? card.guidance.replace(/\\n/g, '<br/><br/>') : (card.interpretation || card.idea);
      document.getElementById('modalLoveText').innerHTML = card.love ? card.love.replace(/\\n/g, '<br/><br/>') : 'En amour, cette carte invite à l’harmonie et à la clarté des sentiments.';
      document.getElementById('modalWorkText').innerHTML = card.work ? card.work.replace(/\\n/g, '<br/><br/>') : 'Sur le plan professionnel, cette énergie favorise la réalisation de vos ambitions.';
      document.getElementById('modalFinanceText').innerHTML = card.finance ? card.finance.replace(/\\n/g, '<br/><br/>') : 'Financièrement, la lucidité et la prudence mènent à la prospérité.';
      document.getElementById('modalCodText').innerHTML = card.cod_text ? card.cod_text.replace(/\\n/g, '<br/><br/>') : card.idea;
      document.getElementById('modalVisualText').innerHTML = card.image_desc ? card.image_desc.replace(/\\n/g, '<br/><br/>') : `Description symbolique de la lame ${{card.name}}.`;
      document.getElementById('modalAffirmationText').innerText = card.affirmation || card.idea || `Je m'aligne avec la sagesse de ${{card.name}}.`;

      // Keywords pills
      const uprightWrap = document.getElementById('modalUprightKeywords');
      uprightWrap.innerHTML = '';
      (card.upright_keywords || [card.key_word, 'Éveil', 'Harmonie']).forEach(kw => {{
        uprightWrap.innerHTML += `<span class="keyword-pill">${{kw}}</span>`;
      }});

      const revWrap = document.getElementById('modalReversedKeywords');
      revWrap.innerHTML = '';
      (card.reversed_keywords || ['Doute', 'Blocage', 'Retard']).forEach(kw => {{
        revWrap.innerHTML += `<span class="keyword-pill reversed">${{kw}}</span>`;
      }});

      // Variant buttons availability
      document.getElementById('pillBorderless').style.display = card.img_borderless ? 'inline-block' : 'none';
      document.getElementById('pillMarseille').style.display = card.img_marseille ? 'inline-block' : 'none';
      document.getElementById('pillFr').style.display = card.img_fr ? 'inline-block' : 'none';

      document.querySelectorAll('.variant-pill').forEach(p => p.classList.remove('active'));
      document.getElementById('pillRws').classList.add('active');

      updateModalFavButton();
    }}

    function closeCardDetailModal() {{
      document.getElementById('cardDetailModal').classList.remove('active');
      document.getElementById('modal3DCard').classList.remove('is-flipped');
    }}

    function handleBackdropClick(e) {{
      if (e.target.id === 'cardDetailModal') closeCardDetailModal();
    }}

    function toggleCard3DFlip() {{
      playTone(520, 'sine', 0.15);
      document.getElementById('modal3DCard').classList.toggle('is-flipped');
    }}

    function switchModalVariant(variantKey) {{
      playTone(480);
      activeModalVariant = variantKey;
      document.querySelectorAll('.variant-pill').forEach(p => p.classList.remove('active'));
      
      let imgPath = activeCardForModal.img_rws;
      if (variantKey === 'borderless' && activeCardForModal.img_borderless) {{
        imgPath = activeCardForModal.img_borderless;
        document.getElementById('pillBorderless').classList.add('active');
      }} else if (variantKey === 'marseille' && activeCardForModal.img_marseille) {{
        imgPath = activeCardForModal.img_marseille;
        document.getElementById('pillMarseille').classList.add('active');
      }} else if (variantKey === 'fr' && activeCardForModal.img_fr) {{
        imgPath = activeCardForModal.img_fr;
        document.getElementById('pillFr').classList.add('active');
      }} else {{
        document.getElementById('pillRws').classList.add('active');
      }}
      document.getElementById('modalCardImg').src = imgPath;
    }}

    function switchModalReadingTab(tabKey, btn) {{
      playTone(550, 'triangle', 0.08);
      document.querySelectorAll('.modal-tab-link').forEach(l => l.classList.remove('active'));
      document.querySelectorAll('.modal-tab-pane').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      
      const paneId = 'tabPane' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1);
      const target = document.getElementById(paneId);
      if (target) target.classList.add('active');
    }}

    function toggleModalFav() {{
      if (!activeCardForModal) return;
      toggleFavorite(activeCardForModal.id);
      updateModalFavButton();
    }}

    function updateModalFavButton() {{
      if (!activeCardForModal) return;
      const isFav = favorites.includes(activeCardForModal.id);
      document.getElementById('modalFavBtn').innerHTML = isFav ? '❤️ Retirer des favoris' : '🤍 Ajouter aux favoris';
    }}

    function downloadCardWallpaper() {{
      if (!activeCardForModal) return;
      const a = document.createElement('a');
      a.href = document.getElementById('modalCardImg').src;
      a.download = `TarotSpace_${{activeCardForModal.id}}.jpg`;
      a.click();
    }}

    // =========================================================================
    // FAVORITES & LIBRARY
    // =========================================================================
    function toggleFavorite(cardId) {{
      playTone(600, 'sine', 0.2);
      if (favorites.includes(cardId)) {{
        favorites = favorites.filter(id => id !== cardId);
      }} else {{
        favorites.push(cardId);
      }}
      localStorage.setItem('tarotspace_favs', JSON.stringify(favorites));
      updateLibraryView();
    }}

    function updateLibraryView() {{
      const grid = document.getElementById('libraryFavsGrid');
      grid.innerHTML = '';
      
      const favCards = TAROT_CARDS.filter(c => favorites.includes(c.id));
      if (favCards.length === 0) {{
        grid.innerHTML = `
          <div class="empty-library-state" style="grid-column: 1 / -1;">
            <div class="empty-icon">🤍</div>
            <p>Aucune carte enregistrée dans vos favoris pour le moment.</p>
          </div>
        `;
      }} else {{
        favCards.forEach(card => {{
          const el = document.createElement('div');
          el.className = 'grid-tarot-card';
          el.onclick = () => openCardDetailModal(card);
          el.innerHTML = `
            <div class="grid-card-img-wrap">
              <img src="${{card.img_rws}}" alt="${{card.name}}" />
              <div class="grid-card-badge">${{card.num_display}}</div>
            </div>
            <div class="grid-card-info">
              <div class="grid-card-name">${{card.name}}</div>
              <div class="grid-card-sub">
                <span>${{card.element}}</span>
                <span>${{card.element_symbol}}</span>
              </div>
            </div>
          `;
          grid.appendChild(el);
        }});
      }}
    }}

    // =========================================================================
    // NAVIGATION TABS
    // =========================================================================
    function switchNavTab(tabKey) {{
      playTone(480);
      document.querySelectorAll('.nav-pill-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-view').forEach(v => v.classList.remove('active'));

      const btnId = 'pill' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1);
      const viewId = 'view' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1);

      const btn = document.getElementById(btnId);
      const view = document.getElementById(viewId);
      if (btn) btn.classList.add('active');
      if (view) view.classList.add('active');

      document.getElementById('contentViewport').scrollTop = 0;
    }}

    // =========================================================================
    // TIRAGE ENGINE
    // =========================================================================
    let currentSpreadMode = 'single';
    let currentSpreadCards = [];

    function startSpreadMode(mode) {{
      currentSpreadMode = mode;
      switchNavTab('tirage');
      
      const titles = {{
        single: ['Carte du Jour (1 Lame)', 'Recevez la guidance essentielle pour éclairer vos pas aujourd\\'hui.'],
        three: ['Passé · Présent · Futur (3 Lames)', 'Une vision complète de votre trajectoire et de la tendance à venir.'],
        yesno: ['Oracle Oui / Non (1 Lame)', 'Posez votre question et découvrez la réponse des symboles.'],
        cross: ['Tirage en Croix (5 Lames)', 'Atout, Obstacle, Conseil, Issue et Synthèse alchimique.']
      }};

      const info = titles[mode] || titles.single;
      document.getElementById('tirageModeTitle').innerText = info[0];
      document.getElementById('tirageModeDesc').innerText = info[1];

      setupSpreadSlots(mode);
    }}

    function setupSpreadSlots(mode) {{
      const container = document.getElementById('spreadSlotsRow');
      container.innerHTML = '';
      document.getElementById('spreadInterpretationBox').style.display = 'none';

      const slotConfigs = {{
        single: ['✦ Guidance du Jour'],
        three: ['1. Le Passé (Racines)', '2. Le Présent (État actuel)', '3. Le Futur (Développement)'],
        yesno: ['⚡ Réponse & Oracle'],
        cross: ['1. Atout (Ce qui aide)', '2. Défi (Ce qui pèse)', '3. Conseil (Action)', '4. Issue (Résultat)', '5. Synthèse']
      }};

      const labels = slotConfigs[mode] || slotConfigs.single;
      currentSpreadCards = new Array(labels.length).fill(null);

      labels.forEach((label, idx) => {{
        const slot = document.createElement('div');
        slot.className = 'spread-slot-item';
        slot.innerHTML = `
          <span class="spread-slot-label">${{label}}</span>
          <div class="spread-card-placeholder" id="spreadSlot_${{idx}}" onclick="drawSlotCard(${{idx}})">
            <span style="color: var(--text-dim); font-size: 32px;">✦</span>
          </div>
        `;
        container.appendChild(slot);
      }});
    }}

    function shuffleAndDealSpread() {{
      playMysticChime();
      const count = currentSpreadCards.length;
      const shuffled = [...TAROT_CARDS].sort(() => 0.5 - Math.random());
      
      for (let i = 0; i < count; i++) {{
        const card = shuffled[i];
        currentSpreadCards[i] = card;
        revealSlotCard(i, card);
      }}

      synthesizeSpreadReading();
    }}

    function drawSlotCard(idx) {{
      if (currentSpreadCards[idx]) {{
        openCardDetailModal(currentSpreadCards[idx]);
        return;
      }}
      playTone(600, 'sine', 0.2);
      const randomCard = TAROT_CARDS[Math.floor(Math.random() * TAROT_CARDS.length)];
      currentSpreadCards[idx] = randomCard;
      revealSlotCard(idx, randomCard);

      if (currentSpreadCards.every(c => c !== null)) {{
        synthesizeSpreadReading();
      }}
    }}

    function revealSlotCard(idx, card) {{
      const slotEl = document.getElementById(`spreadSlot_${{idx}}`);
      if (!slotEl) return;
      slotEl.classList.add('revealed');
      slotEl.innerHTML = `<img src="${{card.img_rws}}" alt="${{card.name}}" />`;
    }}

    function revealAllSpreadCards() {{
      shuffleAndDealSpread();
    }}

    function synthesizeSpreadReading() {{
      const box = document.getElementById('spreadInterpretationBox');
      box.style.display = 'block';

      if (currentSpreadMode === 'yesno' && currentSpreadCards[0]) {{
        const c = currentSpreadCards[0];
        box.innerHTML = `
          <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; padding: 24px;">
            <div style="font-size: 14px; font-weight: 800; color: #fbbf24; margin-bottom: 6px;">RÉPONSE DE L'ORACLE :</div>
            <h3 style="font-size: 28px; font-weight: 800; color: #fff; margin-bottom: 12px;">${{c.reponse || 'OUI'}} — ${{c.name}}</h3>
            <p style="color: #cbd5e1; line-height: 1.6; margin-bottom: 16px;">${{c.idea}}</p>
            <div style="background: rgba(0,0,0,0.3); padding: 12px 16px; border-radius: 12px; font-style: italic; color: #fef08a;">
              « ${{c.affirmation || c.quote}} »
            </div>
          </div>
        `;
        return;
      }}

      let html = `
        <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; padding: 24px;">
          <h3 style="font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 14px;">Synthèse de votre Tirage</h3>
          <div style="display: flex; flex-direction: column; gap: 12px; color: #cbd5e1;">
      `;

      currentSpreadCards.forEach((c, idx) => {{
        if (!c) return;
        html += `
          <div style="border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 8px;">
            <strong style="color:#fbbf24;">${{c.name}} :</strong> ${{c.key_word}} — ${{c.idea || c.guidance.slice(0, 140) + '...'}}
          </div>
        `;
      }});

      html += `
          </div>
        </div>
      `;
      box.innerHTML = html;
    }}

    function startSingleDrawWithCard(card) {{
      startSpreadMode('single');
      currentSpreadCards[0] = card;
      revealSlotCard(0, card);
      synthesizeSpreadReading();
    }}

    // =========================================================================
    // SEARCH & UTILS
    // =========================================================================
    function toggleSearchOverlay() {{
      playTone(500);
      const overlay = document.getElementById('quickSearchOverlay');
      overlay.classList.toggle('active');
      if (overlay.classList.contains('active')) {{
        const input = document.getElementById('quickSearchInput');
        input.focus();
        handleQuickSearch();
      }}
    }}

    function handleQuickSearch() {{
      const query = document.getElementById('quickSearchInput').value.toLowerCase().trim();
      const resContainer = document.getElementById('quickSearchResults');
      resContainer.innerHTML = '';

      const matches = TAROT_CARDS.filter(c => 
        !query || 
        c.name.toLowerCase().includes(query) ||
        c.name_en.toLowerCase().includes(query) ||
        c.key_word.toLowerCase().includes(query) ||
        c.element.toLowerCase().includes(query)
      ).slice(0, 8);

      matches.forEach(card => {{
        const row = document.createElement('div');
        row.className = 'search-result-item';
        row.onclick = () => {{
          toggleSearchOverlay();
          openCardDetailModal(card);
        }};
        row.innerHTML = `
          <img src="${{card.img_rws}}" class="search-result-thumb" alt="${{card.name}}" />
          <div>
            <div style="color:#fff; font-weight:700; font-size:14.5px;">${{card.name}}</div>
            <div style="color:var(--text-muted); font-size:12px;">${{card.category}} · ${{card.element}}</div>
          </div>
        `;
        resContainer.appendChild(row);
      }});
    }}

    function openMysteryGift() {{
      playMysticChime();
      const randomCard = TAROT_CARDS[Math.floor(Math.random() * TAROT_CARDS.length)];
      openCardDetailModal(randomCard);
    }}

    function openSettingsModal() {{
      playTone(400);
      document.getElementById('settingsModal').classList.add('active');
    }}

    function closeSettingsModal() {{
      document.getElementById('settingsModal').classList.remove('active');
    }}

    function openProModal() {{
      playMysticChime();
      document.getElementById('proModal').classList.add('active');
    }}

    function closeProModal() {{
      document.getElementById('proModal').classList.remove('active');
    }}

    function setTheme(themeKey) {{
      playTone(600);
      document.body.setAttribute('data-theme', themeKey);
    }}

    function toggleAudioFx() {{
      isAudioEnabled = !isAudioEnabled;
      document.getElementById('audioToggleBtn').innerText = isAudioEnabled ? '🔊 Audio activé' : '🔇 Audio désactivé';
      if (isAudioEnabled) playMysticChime();
    }}

    function toggleWindowFullscreen() {{
      playTone(550);
      document.getElementById('appWindow').classList.toggle('is-fullscreen');
    }}

    function closeWindowNotify() {{
      playTone(300, 'sawtooth', 0.15);
      alert("TarotSpace restera toujours ouvert pour éclairer votre chemin ésotérique ! ✦");
    }}
  </script>
</body>
</html>
'''

with open('index.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

print(f"Successfully generated index.html ({len(html_content)} bytes)")
