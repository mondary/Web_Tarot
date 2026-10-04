/* ============================================================
   TAROT SPREADS — Système de tirages autonome
   Dépendance : data.js (const TAROT)
   Auto-injection : overlay HTML + CSS + FAB button
   ============================================================ */
(function(){
  'use strict';

  let ALL_CARDS = [];
  const $ = s=>document.querySelector(s);
  const esc = s=>String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  const LOCAL_DATE_KEY = ()=>{const n=new Date();return[n.getFullYear(),String(n.getMonth()+1).padStart(2,'0'),String(n.getDate()).padStart(2,'0')].join('-');};

  /* ---- Mode de tirage : piocher (défaut) / rapide ---- */
  let DRAW_MODE='manual';
  try{ const s=localStorage.getItem('tarotDrawMode'); if(s==='quick'||s==='manual') DRAW_MODE=s; }catch(e){}
  function getDrawMode(){ return DRAW_MODE; }
  function setDrawMode(m){
    DRAW_MODE = m==='manual' ? 'manual' : 'quick';
    try{ localStorage.setItem('tarotDrawMode',DRAW_MODE); }catch(e){}
    updateDrawModeUI();
  }
  function cycleDrawMode(){ setDrawMode(DRAW_MODE==='manual'?'quick':'manual'); }

  /* ---- Ordre de tirage : prédéterminé (défaut) / libre ---- */
  let DRAW_ORDER='fixed';
  try{ if(localStorage.getItem('tarotDrawOrder')==='free') DRAW_ORDER='free'; }catch(e){}
  function getDrawOrder(){ return DRAW_ORDER; }
  function setDrawOrder(o){
    DRAW_ORDER = o==='free' ? 'free' : 'fixed';
    try{ localStorage.setItem('tarotDrawOrder',DRAW_ORDER); }catch(e){}
    updateDrawModeUI();
    updateOrderBadges();
    if(typeof toast==='function') toast(DRAW_ORDER==='free'?'Ordre libre':'Ordre prédéterminé');
  }

  function updateDrawModeUI(){
    document.querySelectorAll('#sp-mode-toggle button').forEach(b=>b.classList.toggle('on',b.dataset.m===DRAW_MODE));
    const d=$('#sp-mode-desc');
    if(d) d.textContent = DRAW_MODE==='manual'
      ? 'Coupez le paquet, puis choisissez vous-même chaque carte dans l\u2019éventail.'
      : 'Les cartes sont distribuées automatiquement, au hasard.';
    document.querySelectorAll('#sp-order-toggle button').forEach(b=>b.classList.toggle('on',b.dataset.o===DRAW_ORDER));
    const od=$('#sp-order-desc');
    if(od) od.textContent = DRAW_ORDER==='free'
      ? 'Piochez et révélez dans l\u2019ordre que vous voulez.'
      : 'Les positions se remplissent, puis se révèlent, dans l\u2019ordre numéroté.';
    const l=document.getElementById('drawModeLbl'); if(l) l.textContent = DRAW_MODE==='manual'?'Piocher':'Rapide';
    const b=document.getElementById('drawModeBtn'); if(b) b.setAttribute('aria-label','Mode de tirage : '+(DRAW_MODE==='manual'?'Piocher':'Rapide'));
  }

  /* ---- Extraction mots-clés depuis le md ---- */
  function extractKeywords(md){
    if(!md) return [];
    const m=md.match(/### À l['']endroit\s*\n([\s\S]*?)(?=^### |^## |(?![\s\S]))/i);
    if(!m) return [];
    return[...m[1].matchAll(/^-\s+(.+)$/gm)].map(([,v])=>v.trim());
  }

  /* ---- Définitions des 7 tirages ---- */
  const SPREADS = [
    {
      id:'jour', name:'Carte du Jour',
      icon:'<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 124 126" style="height:1.4rem;width:auto;display:block;margin:0 auto"><path d="M9 7.6c-1.2 1.3-.2 2.6 6.6 9.5Q27.1 28.6 27 23.2C27 21.6 12.3 6 10.9 6q-.7.1-1.9 1.6m52.2.1a2.4 2.4 0 0 0 4.6 0q.6-1.8-2.3-1.7-2.9-.1-2.3 1.7m46.5 6.5a41 41 0 0 0-7.4 9.2c1 3 4.3 1.4 10.8-5 5.2-5.2 6.9-7.5 6.9-9.6 0-1.6-.6-2.8-1.2-2.8s-4.8 3.7-9.1 8.2M53 22.4a43.3 43.3 0 0 0-23.9 66 44 44 0 0 0 41 17.7 42.7 42.7 0 0 0 31.5-60.7 42 42 0 0 0-48.6-23m21.9 5.5a37.7 37.7 0 0 1 15.7 61.9 36 36 0 0 1-44.9 6.9A37.5 37.5 0 0 1 27 57.8c4.5-23.3 26-36.7 47.9-29.9M6 64c0 3.4.7 3.7 3.6 1.5 1.9-1.5 1.9-1.5 0-3C6.7 60.3 6 60.6 6 64m111.2 0q0 2.2.5 1.2c.2-.6.2-1.8 0-2.5q-.5-.8-.5 1.3M101 103.6c-1.2 1.3-.3 2.6 6.1 9q11 11 10.9 5.6a76 76 0 0 0-15.3-16.2q-.5.1-1.7 1.6m-85.3 7.2a68 68 0 0 0-7.7 8.5q.2.7 2.8.7c2.1 0 4.4-1.6 9.5-6.8a29 29 0 0 0 6.7-8.5q0-5.3-11.3 6.1m45.8 8.2q-.4 1 2 1t2-1q-.6-1-2-1t-2 1"/></svg>',
      desc:'Une lame pour vous guider aujourdu2019hui',
      positions:[{label:'Carte du jour',desc:"L'énergie du jour"}],
      layout:'single'
    },
    {
      id:'trois', name:'Passé · Présent · Futur',
      icon:'<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 132 199" style="height:1.4rem;width:auto;display:block;margin:0 auto"><path d="M9.1 12.6c-4.1 5.2-1.8 11.8 4.4 13l3.5.6v10.2c0 12.2 1.8 20.2 6.6 29.9 3 5.9 5.8 9.4 16.5 19.8A85 85 0 0 1 53 100.5c0 .9-2.6 4-5.7 6.8A362 362 0 0 0 34 120.1a55 55 0 0 0-17 43.1v9.8h-2.5c-3.6 0-7.5 4.2-7.5 8q0 3 2.5 5.5l2.4 2.5h108.2l2.4-2.5c5-4.9 1.7-13.5-5.1-13.5-2.4 0-2.4-.2-2.4-10 0-22.3-6.1-34.6-25.8-51.6-8.8-7.6-10.8-10.5-9.3-13.3.6-1 4.3-4.7 8.3-8.1 14.1-12.2 20.4-20.7 24.3-33.2 2.1-6.5 2.5-9.9 2.5-19.4V26h2.5c6.5 0 9.9-9.4 5.2-13.8l-2.3-2.2H11.1zM118.4 14c2.8 1 3.5 4.7 1.4 6.8-.9.9-14.2 1.2-53.9 1.2-56.9 0-55.5.1-54.7-5.2l.3-2.3 52-.5c28.6-.3 52.1-.6 52.2-.8.1-.1 1.3.2 2.7.8m-7.5 25.2a58 58 0 0 1-2.8 19 67 67 0 0 1-19.5 26C76.9 94.6 76 95.7 76 100.4c0 3.4.7 4.5 4.9 8.5L93.6 121a51 51 0 0 1 17.4 41.7c0 10.1 0 10.3-2.3 10.3-1.9 0-2.6-.8-3.7-4.2-2.6-8.9-7.7-14-26.9-27l-10.5-7.1c-1.4-1-26.5 15.5-32.5 21.3a37 37 0 0 0-9.1 15.7c0 .7-1.2 1.3-2.6 1.3h-2.7l.6-12.3c.9-20.4 3.9-26.5 22.3-44.2C55.8 104.6 56 104.4 56 100c0-4.2-.4-4.8-6.2-10-22.9-20.2-27.7-28.9-28.6-51.3L20.8 26h90.7zM78.1 146.6c13.2 8.8 17 12.2 21 18.3 5.6 8.6 7.7 8.1-33.1 8.1-27.1 0-36-.3-36-1.2 0-2.6 4.2-9.6 8-13.3a236 236 0 0 1 28.1-19.4c.3-.1 5.7 3.3 12 7.5m42.4 34.4v3.5l-53.4.3c-55 .2-56.1.2-56.1-3.7 0-4.1.3-4.1 56-3.9l53.5.3z"/><path d="M35.5 64c-.9 1.5 4.2 7.5 16.9 20 8.4 8.3 10.7 11.1 11.2 14 .9 5.5 1.5 7 2.5 7 1.1 0 1.5-1 2.4-6.5.6-3.3 2.3-5.6 11.5-14.7 18.9-18.7 19.9-21.3 7.8-19.2a72 72 0 0 1-27.3-.1c-10.4-1.7-24.1-2-25-.5M62 68.7a75 75 0 0 0 26.8-.4c.9-.3-3.7 5-10.3 11.9l-12 12.4-12.7-12.5A151 151 0 0 1 41 66.7c0-.9 4.1-.5 21 2m2.6 44.1c-.8 2.4.3 5.4 1.8 4.9s1.4-5.9-.1-6.4q-1-.2-1.7 1.5M65 127q0 3.1 1.3 2.7c.6-.2 1.2-1.4 1.2-2.7s-.6-2.5-1.2-2.8q-1.3-.3-1.3 2.8"/></svg>',
      desc:'Le tirage classique à 3 cartes',
      positions:[
        {label:'Le Passé',desc:'Les racines de la situation'},
        {label:'Le Présent',desc:"L'état actuel"},
        {label:'Le Futur',desc:'La tendance, vers quoi on se dirige'}
      ],
      layout:'trois'
    },
    {
      id:'decision', name:'Tirage de Décision',
      icon:'<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 153 96" style="height:1.4rem;width:auto;display:block;margin:0 auto"><path d="M61.8 6.9a41 41 0 0 0-25.6 21.9 27 27 0 0 0-2.7 14.3c0 7.6.3 9.2 3.1 14.5l3 5.9-14.5-.3c-11.5-.2-15 0-16.6 1.1-1.6 1.3-1.7 1.7-.6 3.1S12 69 28 69c15.1 0 19-.3 19.9-1.4 1.4-1.7.4-4.3-2.5-6.7-3.5-2.8-6.4-11-6.4-17.9 0-12.2 7.1-22.5 19.7-28.7 7-3.5 8.2-3.7 16.7-3.8 8.3 0 9.7.3 15.8 3.3 14.9 7.3 22.8 23 18.8 37.4-.6 2-2.6 6-4.6 8.9-5.7 8.6-6 8.4 18.1 8.4 19.7 0 21-.1 21.3-1.9.2-1-.2-2.2-1-2.7a93 93 0 0 0-17-.9h-15.7l2.7-5.8a27 27 0 0 0 2.7-14.2c0-7.3-.4-9.3-2.8-14.2A41 41 0 0 0 94.3 9.3a30 30 0 0 0-17.2-4 39 39 0 0 0-15.3 1.6m-6.2 79.9c-47.9.2-50.3.4-47.4 3.9 1.6 2 135.1 1.9 136.7-.1.9-1.1.8-1.7-.3-2.9s-5.2-1.4-21.8-1.3z"/></svg>',
      desc:'Pour éclairer un choix ou une situation précise',
      positions:[
        {label:'La situation',desc:'La situation actuelle'},
        {label:'Les forces',desc:'Ce qui pousse à agir'},
        {label:'Les obstacles',desc:'Ce qui freine'},
        {label:'Le conseil',desc:'Le conseil de la carte'},
        {label:'Le résultat',desc:'Le résultat probable'}
      ],
      layout:'cross'
    },
    {
      id:'prenom', name:'Tirage du Prénom',
      icon:'<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 178 168" style="height:1.4rem;width:auto;display:block;margin:0 auto"><path d="M140.2 9.2a240 240 0 0 0-26 33.2 51 51 0 0 1-9.2 10.7q-5 3.1-3.6 9.9c.5 2.6 0 3.4-4 6.7a77 77 0 0 1-9.7 6.9C82 80 82.4 78.9 78.9 97c-1.2 6.3-3.9 17.6-6.1 25-4.5 15.8-4.6 17.8-1.5 18.9 2.7 1.1 4.3.2 12.7-6.4a144 144 0 0 1 36.9-22.3c.7-.4 2.3-4.8 3.6-9.7 2.8-10.8 5.2-16.8 6.5-16 1.4.8 6.8-2.2 8-4.4.5-1.1 1-2.7 1-3.7s1.7-4 3.8-6.8c5.6-7.5 13.8-18.9 20.4-28.5 4.8-7 5.8-9.2 5.8-12.6 0-7.7-8.8-17.4-19.1-21-4.8-1.7-7.5-1.8-10.7-.3m13.7 7.4c6.4 4 11.1 10.1 11.1 14.2 0 1.9-8.5 14.8-20.7 31.4l-6.4 8.8-2.7-3.3a48 48 0 0 0-20.1-14c-2.1-.5-2-.9 3-7.9 12.4-17.4 23.2-31 25.2-31.7 2.5-.9 6.8.1 10.6 2.5m-35.2 43.9c5.9 3 13.5 10.2 15.3 14.5 1.1 2.7 1.1 3.4-.4 4.8-1.6 1.6-1.8 1.6-2.8-.3a49 49 0 0 0-18.3-15.1c-6-2.6-7.5-4.1-5.8-5.7 1.4-1.5 7.1-.6 12 1.8M113 70a37 37 0 0 1 12.1 10.1c.7 1.4.3 3.4-1.4 7.6-1.3 3.2-3.2 9-4.1 13l-1.8 7.1-6.1 2.6c-7.1 3-17.9 9.8-25 15.8-2.7 2.2-5 3.9-5.2 3.7s3.4-6 8-12.9c5.8-8.7 9.4-13 11.6-14.1 5.9-3 7.5-7.3 3.9-10.9-4.1-4.1-10-.9-10 5.5 0 2.7-1.9 6.4-8.7 16.5-4.7 7.2-8.8 12.8-9 12.6s.7-4.4 2.1-9.2c2.4-8.6 5.7-22.9 7-30.5.5-3.1 1.5-4.2 6.4-7.2a71 71 0 0 0 9.7-7.6c2.1-2.3 4.5-4.1 5.2-4.1s3.1.9 5.3 2"/><path d="M23.3 141C16.5 144.4 8 151.4 8 153.6s1.6 1.7 6.7-2.2a46 46 0 0 1 18.8-9.1c5.3-.7 5.5 0 2.8 7.9l-1.8 5 2.9 2.5c3.4 2.9 7.1 2.7 15.1-.7 3.3-1.4 6.9-2 12.5-2q7.8 0 16.2-2.4c11.2-3.3 19.8-6.6 19.8-7.6 0-1.9-3.2-1.5-14 1.8a70 70 0 0 1-21.5 3.7c-7.6.2-11.2.8-14.8 2.4-5.3 2.3-10.7 2.8-10.7 1 0-.6.8-3.3 1.7-5.9 1.6-4.5 1.6-4.9 0-7.4-2.6-3.8-10.2-3.7-18.4.4"/></svg>',
      desc:'Une carte par lettre du prénom',
      positions:[],
      layout:'name',
      needsInput:true,
      inputLabel:'Tapez un prénom',
      inputPlaceholder:'Ex: ALICE'
    }
  ];

  /* ---- Injection CSS ---- */
  const CSS = `
/* FAB Tirages — injecté par tarot-spreads.js */
.sp-fab{position:fixed;bottom:1.8rem;right:2.2rem;z-index:300;display:flex;align-items:center;gap:.55rem;
  padding:.7rem 1.2rem;border:1px solid rgba(241,237,228,.18);border-radius:50px;cursor:pointer;
  background:rgba(10,9,7,.8);backdrop-filter:blur(12px);color:#f1ede4;
  font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.14em;text-transform:uppercase;
  transition:.4s cubic-bezier(.16,1,.3,1)}
.sp-fab:hover{border-color:#c9a227;color:#c9a227;transform:translateY(-2px)}
.sp-fab .dot{width:6px;height:6px;border-radius:50%;background:#c9a227;box-shadow:0 0 8px #c9a227}
.sp-fab svg{width:16px;height:16px}

/* Overlay tirages (menu) */
#sp-menu{position:fixed;inset:0;z-index:8000;display:none;flex-direction:column;align-items:center;justify-content:center;
  background:rgba(5,5,5,.92);backdrop-filter:blur(12px);opacity:0;transition:opacity .3s}
#sp-menu.open{display:flex;opacity:1}
.sp-menu-panel{max-width:580px;width:90%;max-height:80vh;overflow-y:auto;
  background:linear-gradient(180deg,#0a0907,#050505);border:1px solid rgba(241,237,228,.1);border-radius:1.4rem;padding:2rem}
.sp-menu-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.8rem}
.sp-menu-head h2{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:2.4rem;color:#f1ede4}
.sp-menu-head h2 em{font-style:italic;color:#c9a227;font-weight:400}
.sp-menu-close{background:none;border:none;color:#8a8378;cursor:pointer;padding:.4rem;transition:color .3s}
.sp-menu-close:hover{color:#f1ede4}
.sp-menu-close svg{width:22px;height:22px}
.sp-menu-list{display:flex;flex-direction:column;gap:.8rem}
.sp-item{display:flex;align-items:center;gap:1rem;padding:1.1rem 1.2rem;border-radius:1rem;cursor:pointer;
  background:rgba(241,237,228,.03);border:1px solid rgba(241,237,228,.06);transition:.35s}
.sp-item:hover{background:rgba(241,237,228,.06);border-color:rgba(201,162,39,.3);transform:translateX(4px)}
.sp-item .sp-icon{flex:0 0 auto;width:44px;height:44px;border-radius:50%;display:grid;place-items:center;
  background:rgba(201,162,39,.08);border:1px solid rgba(201,162,39,.15);color:#c9a227;font-size:1.2rem}
.sp-item .sp-copy{flex:1;min-width:0}
.sp-item .sp-copy b{display:block;font-family:'Cormorant Garamond',serif;font-size:1.25rem;font-weight:500;color:#f1ede4;margin-bottom:.15rem}
.sp-item .sp-copy span{display:block;font-size:.8rem;color:#8a8378;line-height:1.4}
.sp-item .sp-count{flex:0 0 auto;font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.12em;
  text-transform:uppercase;color:#8a8378;padding:.25rem .55rem;border:1px solid rgba(241,237,228,.1);border-radius:50px}

/* Overlay input prénom */
#sp-menu .sp-input-wrap{display:none;margin-bottom:1rem}
#sp-menu .sp-input-wrap.show{display:block}
.sp-input-wrap label{display:block;font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.18em;
  text-transform:uppercase;color:#8a8378;margin-bottom:.5rem}
.sp-input-wrap input{width:100%;padding:.8rem 1rem;border-radius:.8rem;background:rgba(241,237,228,.05);
  border:1px solid rgba(241,237,228,.12);color:#f1ede4;font-family:'Cormorant Garamond',serif;font-size:1.5rem;
  text-transform:uppercase;letter-spacing:.1em;outline:none;transition:border-color .3s}
.sp-input-wrap input:focus{border-color:#c9a227}
.sp-input-go{margin-top:.6rem;width:100%;padding:.7rem;border:none;border-radius:.7rem;background:#c9a227;
  color:#050505;font-family:'DM Mono',monospace;font-size:.7rem;font-weight:600;letter-spacing:.16em;
  text-transform:uppercase;cursor:pointer;transition:.3s}
.sp-input-go:hover{filter:brightness(1.1)}

/* Overlay tirage (spread) */
#sp-spread{position:fixed;inset:0;z-index:8100;display:none;flex-direction:column;
  background:#050505;opacity:0;transition:opacity .4s}
#sp-spread.open{display:flex;opacity:1}
.sp-spread-bar{flex:0 0 auto;display:flex;align-items:center;justify-content:space-between;
  padding:1.5rem 2rem;border-bottom:1px solid rgba(241,237,228,.06)}
.sp-spread-bar h2{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:1.6rem;color:#f1ede4}
.sp-spread-bar h2 em{font-style:italic;color:#c9a227}
.sp-spread-bar .sp-bar-right{display:flex;gap:.8rem}
.sp-spread-bar button{background:none;border:1px solid rgba(241,237,228,.12);border-radius:50px;
  padding:.5rem 1rem;color:#8a8378;font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.14em;
  text-transform:uppercase;cursor:pointer;transition:.3s}
.sp-spread-bar button:hover{border-color:#c9a227;color:#c9a227}

.sp-stage{flex:1;overflow-y:auto;display:flex;flex-direction:column;align-items:center;
  padding:2rem 2rem 4rem;gap:1.5rem}

/* Carte retournée */
.sp-card{position:relative;border-radius:1.1rem;overflow:hidden;cursor:pointer;
  aspect-ratio:2/3;height:var(--sp-card-h,80vh);width:auto;perspective:1200px;flex-shrink:0;box-shadow:0 24px 60px rgba(0,0,0,.55)}
.sp-card-inner{position:relative;width:100%;height:100%;transition:transform .7s cubic-bezier(.16,1,.3,1);transform-style:preserve-3d}
.sp-card.revealed .sp-card-inner{transform:rotateY(180deg)}
.sp-card-face{position:absolute;inset:0;border-radius:1.1rem;overflow:hidden;backface-visibility:hidden;-webkit-backface-visibility:hidden}
.sp-card-back{background:linear-gradient(135deg,#0a0907,#15110d) var(--tarot-back,none) center/cover no-repeat;border:1px solid rgba(201,162,39,.12)}
.sp-card-front{transform:rotateY(180deg);background:#fff;display:flex;flex-direction:column}
.sp-card-front .sp-card-imgwrap{flex:1;display:flex;align-items:center;justify-content:center;padding:.5rem;overflow:hidden}
.sp-card-front img{height:100%;width:auto;object-fit:contain}
.sp-card-front .sp-card-info{padding:.7rem .8rem .8rem;background:#fff;border-top:1px solid rgba(0,0,0,.06);text-align:center}
.sp-card-front .sp-card-info .nm{font-family:'Cormorant Garamond',serif;font-size:1.15rem;font-weight:600;color:#1c1814;line-height:1.05;display:block}
.sp-card-front .sp-card-info .no{font-family:'DM Mono',monospace;font-size:.68rem;color:#a59c8e;letter-spacing:.1em}
.sp-card-front .sp-card-info .kw{margin-top:.3rem;font-size:.72rem;color:#6f6a5f;line-height:1.25;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

/* Position label */
.sp-pos{position:relative;display:flex;flex-direction:column;align-items:center;gap:.5rem}
.sp-pos-label{font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.16em;text-transform:uppercase;
  color:#8a8378;text-align:center;max-width:140px;line-height:1.3}
.sp-pos-desc{font-size:.68rem;color:rgba(138,131,120,.6);text-align:center;max-width:140px;line-height:1.3}
.sp-card:hover .sp-card-back{border-color:rgba(201,162,39,.4)}

/* Badge d'ordre de tirage */
.sp-order{position:absolute;top:-9px;left:-9px;z-index:9;width:23px;height:23px;border-radius:50%;
  background:#0a0907;border:1px solid rgba(201,162,39,.45);color:#c9a227;display:grid;place-items:center;
  font-family:'DM Mono',monospace;font-size:.6rem;box-shadow:0 4px 14px rgba(0,0,0,.45);pointer-events:none}
.sp-order.now{background:#c9a227;color:#0a0907;border-color:#c9a227;box-shadow:0 0 18px rgba(201,162,39,.55)}
.sp-layout-name .sp-order{top:-7px;left:calc(50% - 46px)}
@keyframes spShake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
.sp-shake{animation:spShake .32s ease}
/* carte à révéler : halo doré pulsé */
@keyframes spNextReveal{0%,100%{box-shadow:0 0 0 0 rgba(201,162,39,.12),0 24px 60px rgba(0,0,0,.55)}50%{box-shadow:0 0 0 6px rgba(201,162,39,.3),0 0 38px rgba(201,162,39,.4),0 24px 60px rgba(0,0,0,.55)}}
.sp-card.next-reveal{animation:spNextReveal 1.9s ease-in-out infinite}

/* Layouts */
.sp-layout-single{--sp-card-h:80vh;display:flex;justify-content:center}
.sp-layout-row{--sp-card-h:55vh;display:flex;flex-wrap:wrap;justify-content:center;gap:1.5rem}
.sp-layout-trois{display:flex;flex-wrap:nowrap;justify-content:center;align-items:flex-start;gap:1.4rem;width:100%;max-width:920px}
.sp-layout-trois .sp-pos{flex:1 1 0;min-width:0;max-width:300px}
.sp-layout-trois .sp-card{height:auto;width:100%}

/* Croix (Décision) — forme fixe à toute résolution */
.sp-layout-cross{display:grid;grid-template-columns:repeat(3,var(--cc));grid-template-rows:repeat(3,auto);gap:.8rem;place-items:center;--cc:clamp(84px,22vmin,160px)}
.sp-layout-cross .sp-pos:nth-child(1){grid-area:2/2}
.sp-layout-cross .sp-pos:nth-child(2){grid-area:2/3}
.sp-layout-cross .sp-pos:nth-child(3){grid-area:2/1}
.sp-layout-cross .sp-pos:nth-child(4){grid-area:3/2}
.sp-layout-cross .sp-pos:nth-child(5){grid-area:1/2}
.sp-layout-cross .sp-card{width:var(--cc);height:calc(var(--cc)*1.5);max-width:none}
.sp-layout-cross .sp-pos-label{font-size:.56rem;max-width:110px}
/* croix (et celtic) : la carte avant tout — image plein cadre, nom en voile discret,
   pas de bandeau numéro/famille qui écrasait les petites cartes.
   NB : l'image garde la mise en page flex d'origine (position absolute ici = carte noire). */
.sp-layout-cross .sp-card-info,.sp-layout-celtic .sp-card-info{position:absolute;left:0;right:0;bottom:0;padding:1.2rem .3rem .3rem;
  background:linear-gradient(transparent,rgba(10,9,7,.85));border-top:none;text-align:center;z-index:2}
.sp-layout-cross .sp-card-info .nm,.sp-layout-celtic .sp-card-info .nm{font-size:.74rem;color:#f1ede4}
.sp-layout-cross .sp-card-info .no,.sp-layout-cross .sp-card-info .kw,.sp-layout-celtic .sp-card-info .no,.sp-layout-celtic .sp-card-info .kw{display:none}
@media(max-width:600px){
  .sp-layout-trois{flex-direction:column;align-items:center;gap:1rem}
  .sp-layout-trois .sp-pos{max-width:240px}
}
.sp-layout-celtic{position:relative;width:100%;max-width:520px;height:auto;display:grid;
  grid-template-columns:repeat(6,1fr);grid-template-rows:repeat(6,1fr);gap:.5rem;place-items:center}
.sp-layout-celtic .sp-pos:nth-child(1){grid-area:3/3/5/5}
.sp-layout-celtic .sp-pos:nth-child(2){grid-area:3/3/5/5;transform:rotate(90deg) translateY(0);z-index:2}
.sp-layout-celtic .sp-pos:nth-child(3){grid-area:5/3/7/5}
.sp-layout-celtic .sp-pos:nth-child(4){grid-area:3/1/5/3}
.sp-layout-celtic .sp-pos:nth-child(5){grid-area:1/3/3/5}
.sp-layout-celtic .sp-pos:nth-child(6){grid-area:3/5/5/7}
.sp-layout-celtic .sp-pos:nth-child(7){grid-area:2/7}
.sp-layout-celtic .sp-pos:nth-child(8){grid-area:4/7}
.sp-layout-celtic .sp-pos:nth-child(9){grid-area:6/7}
.sp-layout-celtic .sp-pos:nth-child(10){grid-area:8/7}
.sp-layout-celtic .sp-card{width:80px;height:auto}
.sp-layout-celtic .sp-pos-label{font-size:.5rem;max-width:90px}

.sp-layout-hexagram{position:relative;width:100%;max-width:500px;display:flex;flex-direction:column;align-items:center;gap:1rem}
.sp-hex-row{display:flex;gap:1rem}
.sp-hex-triangle-down,.sp-hex-triangle-up{display:flex;flex-direction:column;align-items:center;gap:.8rem}
.sp-hex-center{margin:0 auto}
.sp-hex-label{font-family:'DM Mono',monospace;font-size:.56rem;letter-spacing:.14em;text-transform:uppercase;
  color:rgba(201,162,39,.5);margin-bottom:.4rem;text-align:center}

.sp-layout-name{--sp-card-h:42vh;display:flex;flex-wrap:wrap;justify-content:center;gap:1rem}
.sp-layout-name .sp-pos{gap:.3rem}

/* Drawer 3-significations (Passé / Présent / Futur) */
.sp-trois-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1.5rem;padding:1rem 2rem 3rem}
.sp-trois-col{display:flex;flex-direction:column;align-items:center;text-align:center;gap:.5rem}
.sp-trois-col img{width:100%;max-width:150px;border-radius:.6rem}
.sp-trois-pos{font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.16em;text-transform:uppercase;color:var(--ac,#c9a227)}
.sp-trois-name{font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:#f1ede4;line-height:1.1}
.sp-trois-name em{font-style:italic;color:var(--ac,#c9a227);font-weight:400}
.sp-trois-rep{display:inline-block;padding:.3rem .8rem;border-radius:50px;font-size:.72rem;font-weight:700;text-transform:uppercase;background:var(--ac,#c9a227);color:#fff}
.sp-trois-kw{font-size:.78rem;color:#b8b0a2;line-height:1.4}
@media(max-width:700px){.sp-trois-grid{grid-template-columns:1fr;gap:1.4rem}}
.sp-name-letter{font-family:'Cormorant Garamond',serif;font-size:1.8rem;color:#c9a227;text-align:center;font-style:italic}

/* Carte révélée — contenu */
.sp-reveal-info{margin-top:.4rem;text-align:center;display:none}
.sp-card.revealed+.sp-reveal-info{display:block}
.sp-reveal-name{font-family:'Cormorant Garamond',serif;font-size:.92rem;font-weight:500;color:#f1ede4}
.sp-reveal-num{font-family:'DM Mono',monospace;font-size:.52rem;color:#8a8378;letter-spacing:.1em}

/* Hint */
.sp-hint{text-align:center;color:#8a8378;font-family:'DM Mono',monospace;font-size:.62rem;
  letter-spacing:.16em;text-transform:uppercase;margin-top:1rem}

/* Reveal all button */
.sp-reveal-all{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:50;
  padding:.6rem 1.4rem;border:1px solid rgba(201,162,39,.3);border-radius:50px;background:rgba(10,9,7,.8);
  backdrop-filter:blur(10px);color:#c9a227;font-family:'DM Mono',monospace;font-size:.6rem;
  letter-spacing:.16em;text-transform:uppercase;cursor:pointer;transition:.3s}
.sp-reveal-all:hover{background:rgba(201,162,39,.1);border-color:#c9a227}

@media(max-width:600px){
  .sp-layout-single{--sp-card-h:70vh}
  .sp-layout-row{--sp-card-h:42vh;gap:1rem}
  .sp-layout-name{--sp-card-h:35vh}
  .sp-layout-celtic .sp-card{width:65px}
  .sp-layout-celtic{transform:scale(.8)}
}

/* Drawer (monte du bas) — vue rapide */
#sp-drawer{position:fixed;inset:0;z-index:8200;display:none}
#sp-drawer.open{display:block}
.sp-drawer-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.6);opacity:0;transition:opacity .4s}
#sp-drawer.open .sp-drawer-backdrop{opacity:1}
.sp-drawer-panel{position:absolute;bottom:0;left:0;right:0;max-height:88vh;background:#0a0907;
  border-radius:1.4rem 1.4rem 0 0;border-top:1px solid rgba(241,237,228,.1);
  transform:translateY(100%);transition:transform .45s cubic-bezier(.16,1,.3,1);
  overflow-y:auto;overscroll-behavior:contain;display:flex;flex-direction:column}
#sp-drawer.open .sp-drawer-panel{transform:translateY(0)}
.sp-drawer-handle{flex:0 0 auto;width:40px;height:4px;background:rgba(241,237,228,.15);border-radius:50px;margin:.8rem auto .4rem}
.sp-drawer-close{position:absolute;top:1rem;right:1.2rem;z-index:5;background:none;border:none;color:#8a8378;cursor:pointer;padding:.3rem;transition:color .3s}
.sp-drawer-close:hover{color:#f1ede4}
.sp-drawer-close svg{width:20px;height:20px}
.sp-drawer-body{padding:1rem 2rem 3rem;display:flex;gap:2rem;align-items:flex-start}
.sp-drawer-card{flex:0 0 auto;width:200px}
.sp-drawer-card img{width:100%;border-radius:.8rem}
.sp-drawer-card .sp-drawer-num{font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.16em;
  text-transform:uppercase;color:#8a8378;margin-top:.5rem;text-align:center}
.sp-drawer-content{flex:1;min-width:0}
.sp-drawer-content h3{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:clamp(1.8rem,3vw,2.8rem);
  line-height:.95;text-transform:uppercase;letter-spacing:-.01em;margin-bottom:.5rem}
.sp-drawer-content h3 em{font-style:italic;color:var(--ac,#c9a227);font-weight:400}
.sp-drawer-meta{font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;
  color:#8a8378;margin-bottom:.8rem}
.sp-drawer-reponse{display:inline-block;padding:.4rem 1rem;border-radius:50px;font-size:.85rem;font-weight:700;
  letter-spacing:.03em;text-transform:uppercase;background:var(--ac,#c9a227);color:#fff;margin-bottom:.6rem}
.sp-drawer-affirm{font-family:'Cormorant Garamond',serif;font-weight:400;font-size:clamp(1.1rem,2vw,1.6rem);
  line-height:1.2;color:#f1ede4;font-style:italic;border-left:3px solid var(--ac,#c9a227);
  padding-left:1rem;margin-bottom:1.2rem}
.sp-drawer-kw{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1rem}
.sp-drawer-kw h4{font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;
  color:var(--ac,#c9a227);margin-bottom:.5rem;padding-bottom:.4rem;border-bottom:1px solid rgba(241,237,228,.08)}
.sp-drawer-kw ul{list-style:none;display:grid;gap:.35rem}
.sp-drawer-kw li{position:relative;padding-left:.8rem;color:#d8d2c5;font-size:.82rem;line-height:1.3}
.sp-drawer-kw li::before{content:'◆';position:absolute;left:0;top:.3rem;color:var(--ac,#c9a227);font-size:.35rem}
@media(max-width:700px){
  .sp-drawer-body{flex-direction:column;align-items:center;text-align:center}
  .sp-drawer-card{width:160px}
  .sp-drawer-affirm{text-align:left}
  .sp-drawer-kw{text-align:left}
}

/* Mode de tirage (menu Tirages) */
.sp-mode-row{margin-top:1.4rem;padding-top:1.1rem;border-top:1px solid rgba(241,237,228,.08)}
.sp-mode-lbl{font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.18em;text-transform:uppercase;color:#8a8378;margin-bottom:.55rem}
.sp-mode-toggle{display:flex;gap:.4rem}
.sp-mode-toggle button{flex:1;display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.7rem .5rem;border-radius:.7rem;cursor:pointer;
  background:rgba(241,237,228,.03);border:1px solid rgba(241,237,228,.08);color:#8a8378;
  font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.12em;text-transform:uppercase;transition:.3s}
.sp-mode-toggle button:hover{border-color:rgba(201,162,39,.35);color:#f1ede4}
.sp-mode-toggle button.on{background:rgba(201,162,39,.12);border-color:#c9a227;color:#c9a227}
.sp-mode-desc{margin-top:.55rem;font-size:.74rem;color:#8a8378;line-height:1.45}

/* Emplacements vides (mode piocher) */
.sp-slot-empty{aspect-ratio:2/3;height:var(--sp-card-h,55vh);width:auto;border-radius:1.1rem;cursor:pointer;
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;flex-shrink:0;
  border:1.5px dashed rgba(201,162,39,.28);background:radial-gradient(120% 100% at 50% 0%,rgba(201,162,39,.05),transparent 60%);
  transition:border-color .3s,background .3s}
.sp-slot-empty:hover,.sp-slot-empty:focus-visible{border-color:rgba(201,162,39,.6);background:rgba(201,162,39,.07);outline:none}
.sp-slot-plus{font-size:1.8rem;color:rgba(201,162,39,.55);line-height:1;font-weight:300}
.sp-slot-txt{font-family:'DM Mono',monospace;font-size:.56rem;letter-spacing:.16em;text-transform:uppercase;color:#8a8378}
@keyframes spSlotPulse{0%,100%{box-shadow:0 0 0 0 rgba(201,162,39,0)}50%{box-shadow:0 0 0 5px rgba(201,162,39,.10),0 0 28px rgba(201,162,39,.22)}}
.sp-slot-empty.next{border-color:rgba(201,162,39,.65);animation:spSlotPulse 1.7s ease-in-out infinite}
.sp-layout-trois .sp-slot-empty{height:auto;width:100%}
.sp-layout-cross .sp-slot-empty{width:var(--cc);height:calc(var(--cc)*1.5);max-width:none}
.sp-layout-celtic .sp-slot-empty{width:80px;height:auto}
.sp-layout-name .sp-slot-empty{--sp-card-h:42vh}
@keyframes spDealIn{from{opacity:0;transform:translateY(-34px) rotate(-5deg) scale(.86)}to{opacity:1;transform:none}}
.sp-card.dealt{animation:spDealIn .5s cubic-bezier(.16,1,.3,1)}

/* Coupe du paquet */
#sp-cut{position:fixed;inset:0;z-index:8300;display:none;flex-direction:column;align-items:center;justify-content:center;
  background:rgba(5,5,5,.96);backdrop-filter:blur(12px);opacity:0;transition:opacity .3s}
#sp-cut.open{display:flex;opacity:1}
.sp-cut-panel{max-width:420px;width:90%;text-align:center}
.sp-cut-panel h2{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:2.2rem;color:#f1ede4;margin:0 0 .6rem}
.sp-cut-panel h2 em{font-style:italic;color:#c9a227}
.sp-cut-sub{font-size:.86rem;color:#8a8378;line-height:1.5;margin:0 0 1.8rem}
.sp-cut-stage{position:relative;touch-action:none;cursor:pointer;padding:0 26px}
.sp-cut-stack{display:flex;flex-direction:column;align-items:center;width:min(220px,56vw);margin:2.6rem auto 0;position:relative}
.sp-cut-layer{width:82%;height:var(--layer-h,6px);border-radius:2px;flex:0 0 auto;
  background:linear-gradient(180deg,#241d15,#171208);border-left:1px solid rgba(201,162,39,.10);border-right:1px solid rgba(201,162,39,.06);
  transition:transform .75s cubic-bezier(.16,1,.3,1)}
.sp-cut-layer.top{background:linear-gradient(180deg,#332916,#1c150b);border-color:rgba(201,162,39,.22);border-left-width:1px}
@keyframes spCutLift{0%{transform:none}32%{transform:translate(30px,-34px)}58%{transform:translate(34px,-26px)}100%{transform:translate(0,var(--dy))}}
@keyframes spCutRise{0%{transform:none}100%{transform:translate(0,var(--dy))}}
.sp-cut-layer.cut-top{animation:spCutLift .8s cubic-bezier(.5,.06,.3,1) both;animation-delay:calc(var(--k)*.009s)}
.sp-cut-layer.cut-bot{animation:spCutRise .72s cubic-bezier(.5,.06,.3,1) both;animation-delay:calc(var(--k)*.006s)}
.sp-cut-line{position:absolute;left:-44px;right:-44px;top:50%;pointer-events:none;z-index:3;transition:opacity .3s}
.sp-cut-line::before{content:'';position:absolute;left:0;right:0;top:-1px;border-top:2px dashed #c9a227;box-shadow:0 0 12px rgba(201,162,39,.4)}
.sp-cut-n{position:absolute;right:-12px;top:-2.2rem;padding:.3rem .7rem;border-radius:50px;background:rgba(10,9,7,.92);
  border:1px solid rgba(201,162,39,.35);color:#c9a227;font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.1em;white-space:nowrap}
.sp-cut-go{margin-top:1.8rem;padding:.75rem 2rem;border:none;border-radius:50px;background:#c9a227;color:#050505;
  font-family:'DM Mono',monospace;font-size:.66rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;cursor:pointer;transition:.3s}
.sp-cut-go:hover{filter:brightness(1.1);transform:translateY(-1px)}

/* Éventail (mode piocher) */
#sp-fan{position:fixed;inset:0;z-index:8300;display:none;flex-direction:column;background:rgba(5,5,5,.97);opacity:0;transition:opacity .3s}
#sp-fan.open{display:flex;opacity:1}
.sp-fan-bar{flex:0 0 auto;display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.3rem 2rem .6rem}
.sp-fan-title{font-family:'Cormorant Garamond',serif;font-size:1.5rem;color:#f1ede4;line-height:1.1}
.sp-fan-title em{font-style:italic;color:#c9a227}
.sp-fan-title .sp-fan-pos{display:block;font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.16em;text-transform:uppercase;color:#8a8378;margin-top:.3rem}
.sp-fan-count{font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.14em;text-transform:uppercase;color:#8a8378;white-space:nowrap}
.sp-fan-close{background:none;border:1px solid rgba(241,237,228,.14);border-radius:50px;padding:.45rem .9rem;color:#8a8378;
  font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;transition:.3s}
.sp-fan-close:hover{border-color:#c9a227;color:#c9a227}
.sp-fan-scroll{flex:1;overflow-x:auto;overflow-y:hidden;position:relative;scrollbar-width:thin}
.sp-fan-scroll.vmode{overflow-x:hidden;overflow-y:auto;scroll-snap-type:y proximity}
.sp-fan-stage{position:relative;height:min(64vh,600px);width:100%}
.sp-fan-card{position:absolute;top:50%;left:0;width:var(--w);margin-top:calc(var(--w)*-.75);padding:0;border:none;background:none;cursor:pointer;
  transform:translate(var(--x),var(--y)) rotate(var(--r)) scale(var(--s,1));transform-origin:50% 130%;
  transition:transform .2s cubic-bezier(.16,1,.3,1)}
/* variante verticale (mobile) : éventail radial — pivot à gauche, arc bombé à droite */
.sp-fan-card.v{top:0;left:50%;margin-top:0;margin-left:calc(var(--w)*-.5);transform-origin:50% 50%;scroll-snap-align:center;transition:transform .12s linear}
.sp-fan-card.rad{top:var(--py);left:var(--px);margin-top:calc(var(--w)*-.75);margin-left:calc(var(--w)*-.5);
  transform-origin:50% 50%;transition:none;
  transform:rotate(var(--a)) translateX(var(--R)) rotate(90deg) scale(var(--s,1));opacity:var(--o,1)}
.sp-fan-card.rad.picked{transform:rotate(var(--a)) translateX(calc(var(--R) + 16px)) rotate(90deg) scale(calc(var(--s,1)*1.12))}
.sp-fan-back{display:flex;align-items:center;justify-content:center;width:100%;aspect-ratio:2/3;border-radius:.65rem;
  background:#15110d var(--tarot-back,none) center/cover no-repeat;border:1px solid rgba(201,162,39,.15);
  box-shadow:0 10px 26px rgba(0,0,0,.55);transition:border-color .2s,box-shadow .2s}
/* survol piloté en JS (pas de :hover CSS) : évite le clignotement des cartes superposées */
.sp-fan-card.lift{z-index:999;transform:translate(var(--x),calc(var(--y) - 26px)) rotate(var(--r)) scale(calc(var(--s,1)*1.04))}
.sp-fan-card.lift .sp-fan-back{border-color:rgba(201,162,39,.6);box-shadow:0 16px 34px rgba(0,0,0,.6)}
.sp-fan-card:focus-visible{outline:none}
.sp-fan-card:focus-visible .sp-fan-back{border-color:rgba(201,162,39,.6)}
.sp-fan-card:active .sp-fan-back{border-color:rgba(201,162,39,.6)}
.sp-fan-hint{flex:0 0 auto;text-align:center;color:#8a8378;font-family:'DM Mono',monospace;font-size:.6rem;
  letter-spacing:.16em;text-transform:uppercase;padding:1rem 1rem calc(1.2rem + env(safe-area-inset-bottom))}
@media(max-width:640px){
  .sp-fan-bar{padding:1rem 1rem .4rem}
  .sp-fan-title{font-size:1.2rem}
  .sp-fan-stage{height:min(56vh,520px)}
}
`;

  /* ---- Injection HTML ---- */
  function inject(){
    // CSS
    const style=document.createElement('style');
    style.textContent=CSS;
    document.head.appendChild(style);

    // Menu overlay
    if(!document.getElementById('sp-menu')){
      const menu=document.createElement('div');
      menu.id='sp-menu';
      menu.innerHTML=`
        <div class="sp-menu-panel">
          <div class="sp-menu-head">
            <h2><em>Tirages</em></h2>
            <button class="sp-menu-close" id="sp-menu-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
          </div>
          <div class="sp-input-wrap" id="sp-input-wrap">
            <label id="sp-input-label">Tapez un prénom</label>
            <input type="text" id="sp-name-input" placeholder="ALICE" maxlength="20" autocomplete="off">
            <button class="sp-input-go" id="sp-input-go">Tirer les cartes</button>
          </div>
          <div class="sp-menu-list" id="sp-menu-list"></div>
          <div class="sp-mode-row">
            <div class="sp-mode-lbl">Mode de tirage</div>
            <div class="sp-mode-toggle" id="sp-mode-toggle">
              <button type="button" data-m="manual">✋ Piocher</button>
              <button type="button" data-m="quick">⚡ Rapide</button>
            </div>
            <div class="sp-mode-desc" id="sp-mode-desc"></div>
          </div>
          <div class="sp-mode-row" style="margin-top:.9rem">
            <div class="sp-mode-lbl">Ordre de tirage</div>
            <div class="sp-mode-toggle" id="sp-order-toggle">
              <button type="button" data-o="fixed">①②③ Prédéterminé</button>
              <button type="button" data-o="free">Libre</button>
            </div>
            <div class="sp-mode-desc" id="sp-order-desc"></div>
          </div>
        </div>`;
      document.body.appendChild(menu);
      $('#sp-menu-close').addEventListener('click',closeMenu);
      menu.addEventListener('click',e=>{if(e.target===menu)closeMenu();});
      $('#sp-mode-toggle').addEventListener('click',e=>{
        const b=e.target.closest('button'); if(!b)return;
        setDrawMode(b.dataset.m);
      });
      $('#sp-order-toggle').addEventListener('click',e=>{
        const b=e.target.closest('button'); if(!b)return;
        setDrawOrder(b.dataset.o);
      });
    }

    // Spread overlay
    if(!document.getElementById('sp-spread')){
      const spread=document.createElement('div');
      spread.id='sp-spread';
      document.body.appendChild(spread);
    }

    // Éventail (mode piocher)
    if(!document.getElementById('sp-fan')){
      const fan=document.createElement('div');
      fan.id='sp-fan';
      fan.innerHTML=`
        <div class="sp-fan-bar">
          <div class="sp-fan-title"><em>Choisissez votre carte</em><span class="sp-fan-pos" id="sp-fan-pos"></span></div>
          <span class="sp-fan-count" id="sp-fan-count"></span>
          <button class="sp-fan-close" id="sp-fan-x">✕ Fermer</button>
        </div>
        <div class="sp-fan-scroll" id="sp-fan-scroll"><div class="sp-fan-stage" id="sp-fan-stage"></div></div>
        <div class="sp-fan-hint" id="sp-fan-hint">Cartes faces cachées — laissez-vous guider</div>`;
      document.body.appendChild(fan);
      $('#sp-fan-x').addEventListener('click',closeFan);
    }

    // Coupe du paquet (mode piocher)
    if(!document.getElementById('sp-cut')){
      const cut=document.createElement('div');
      cut.id='sp-cut';
      cut.innerHTML=`
        <div class="sp-cut-panel">
          <h2><em>Coupez le paquet</em></h2>
          <p class="sp-cut-sub">Le paquet de 78 cartes est mélangé.<br>Choisissez la hauteur de votre coupe : la partie soulevée passe dessous.</p>
          <div class="sp-cut-stage" id="sp-cut-stage">
            <div class="sp-cut-stack" id="sp-cut-stack">
              <div class="sp-cut-line" id="sp-cut-line"><span class="sp-cut-n" id="sp-cut-n"></span></div>
            </div>
          </div>
          <button class="sp-cut-go" id="sp-cut-go">✂ Couper ici</button>
        </div>`;
      document.body.appendChild(cut);
    }

    // Build menu items
    renderMenuItems();
    updateDrawModeUI();
  }

  function renderMenuItems(){
    const list=$('#sp-menu-list');
    if(!list) return;
    list.innerHTML='';
    SPREADS.forEach(sp=>{
      const count = sp.layout==='name' ? 'N' : sp.positions.length;
      const item=document.createElement('div');
      item.className='sp-item';
      item.innerHTML=`
        <div class="sp-icon">${sp.icon}</div>
        <div class="sp-copy">
          <b>${sp.name}</b>
          <span>${sp.desc}</span>
        </div>
        <span class="sp-count">${count} cartes</span>`;
      item.addEventListener('click',()=>{
        if(sp.needsInput){
          const wrap=$('#sp-input-wrap');
          const label=$('#sp-input-label');
          const input=$('#sp-name-input');
          const go=$('#sp-input-go');
          wrap.classList.add('show');
          if(sp.inputLabel) label.textContent=sp.inputLabel;
          if(sp.inputPlaceholder) input.placeholder=sp.inputPlaceholder;
          input.focus();
          go.onclick=()=>{
            const name=input.value.trim().toUpperCase();
            if(name.length<2) return;
            const positions=name.split('').map(ch=>({label:ch,desc:''}));
            closeMenu();
            startSpread({...sp,positions,name});
            wrap.classList.remove('show');
            input.value='';
          };
          input.onkeydown=e=>{if(e.key==='Enter')go.click();};
        } else {
          closeMenu();
          startSpread(sp);
        }
      });
      list.appendChild(item);
    });
  }

  /* ---- Menu ---- */
  function openMenu(){
    const m=$('#sp-menu');if(m)m.classList.add('open');
    const wrap=$('#sp-input-wrap');if(wrap)wrap.classList.remove('show');
    // Carte du jour badge
    const today=LOCAL_DATE_KEY();
    const drawn=localStorage.getItem('tarot_cjd_'+today);
    const first=$('#sp-menu-list .sp-item');
    if(first){
      let badge=first.querySelector('.sp-badge-draw');
      if(drawn){
        if(!badge){badge=document.createElement('span');badge.className='sp-count sp-badge-draw';first.appendChild(badge);}
        badge.textContent='Tirée';
      } else if(badge){badge.remove();}
    }
  }
  function closeMenu(){const m=$('#sp-menu');if(m)m.classList.remove('open');}

  /* ---- Tirage aléatoire ---- */
  function shuffleDeck(pool){
    for(let i=pool.length-1;i>0;i--){
      const j=Math.floor(Math.random()*(i+1));
      [pool[i],pool[j]]=[pool[j],pool[i]];
    }
    return pool;
  }
  function shuffleAndDraw(count){
    return shuffleDeck([...ALL_CARDS]).slice(0,count);
  }

  /* ---- Carte du jour (déterministe) ---- */
  function cardOfTheDay(){
    const today=LOCAL_DATE_KEY();
    const key='tarot_cjd_'+today;
    const saved=localStorage.getItem(key);
    const existing=saved&&ALL_CARDS.find(c=>c.id===saved);
    if(existing) return existing;
    const r=new Uint32Array(1);crypto.getRandomValues(r);
    const card=ALL_CARDS[r[0]%ALL_CARDS.length];
    localStorage.setItem(key,card.id);
    return card;
  }

  /* ---- Démarrer un tirage ---- */
  let currentSpread=null;
  let drawnCards=[];
  let manualModeActive=false;
  let manualDeck=[];
  let manualSlots=[];
  let fanSlot=-1;

  function startSpread(spreadDef,force){
    currentSpread=spreadDef;

    // Mode piocher : couper le paquet puis choisir ses cartes
    if(DRAW_MODE==='manual'){
      startSpreadManual(spreadDef,force);
      return;
    }

    // Cas spécial : Carte du jour
    if(spreadDef.id==='jour'){
      const card=cardOfTheDay();
      drawnCards=[card];
      renderSpread(spreadDef,[card]);
      openSpread();
      return;
    }

    // Tirage aléatoire
    const count=spreadDef.positions.length;
    drawnCards=shuffleAndDraw(count);
    renderSpread(spreadDef,drawnCards);
    openSpread();
  }

  /* ---- Mode piocher : mélange → coupe → éventail ---- */
  function startSpreadManual(spreadDef,force){
    // Carte du jour déjà tirée aujourd'hui ? On la remontre telle quelle.
    // (force = « Refaire » : on ignore la carte mémorisée, qui n'est écrasée
    //  qu'une fois la nouvelle carte réellement piochée — annuler ne perd rien)
    if(spreadDef.id==='jour'&&!force){
      const saved=localStorage.getItem('tarot_cjd_'+LOCAL_DATE_KEY());
      const existing=saved&&ALL_CARDS.find(c=>c.id===saved);
      if(existing){
        drawnCards=[existing];
        renderSpread(spreadDef,[existing]);
        openSpread();
        return;
      }
    }
    drawnCards=[];
    manualModeActive=true;
    manualDeck=shuffleDeck([...ALL_CARDS]);
    manualSlots=new Array(spreadDef.positions.length).fill(null);
    showCut(manualDeck,(cutDeck)=>{
      manualDeck=cutDeck;
      renderSpreadSlots(spreadDef);
      openSpread();
      // Première pioche proposée d'office, les suivantes au clic sur un emplacement vide
      setTimeout(()=>{ const nx=nextEmptySlot(); if(nx>=0&&manualModeActive){ pulseSlot(nx); openFan(nx); } },650);
    });
  }

  /* ---- Coupe du paquet ---- */
  let cutValidate=null, cutCancel=null;
  function showCut(deck,onDone){
    const ov=$('#sp-cut');
    if(!ov){ onDone(deck); return; }
    const stage=$('#sp-cut-stage'), stack=$('#sp-cut-stack'), line=$('#sp-cut-line'),
          nEl=$('#sp-cut-n'), go=$('#sp-cut-go');
    const n=deck.length;
    let layerH=window.innerWidth<640?5:6;
    let cutIdx=Math.floor(n/2);
    let busy=false;
    const ctrl=new AbortController();

    // recadrage si la fenêtre change pendant la coupe (layers en flux, hauteur pilotée par --layer-h)
    window.addEventListener('resize',()=>{
      if(!ov.classList.contains('open')||busy)return;
      layerH=window.innerWidth<640?5:6;
      stack.style.setProperty('--layer-h',layerH+'px');
      setCut(cutIdx);
    },{signal:ctrl.signal});

    let html='';
    for(let i=0;i<n;i++) html+=`<div class="sp-cut-layer${i===0?' top':''}" data-i="${i}"></div>`;
    stack.querySelectorAll('.sp-cut-layer').forEach(el=>el.remove());
    stack.insertAdjacentHTML('beforeend',html);
    stack.style.setProperty('--layer-h',layerH+'px');

    function setCut(idx){
      cutIdx=Math.max(3,Math.min(n-3,Math.round(idx)));
      line.style.top=(cutIdx*layerH)+'px';
      line.style.opacity='1';
      nEl.textContent=cutIdx+' carte'+(cutIdx>1?'s':'')+' soulevée'+(cutIdx>1?'s':'');
    }
    setCut(Math.floor(n/2));

    ov.classList.add('open');
    document.body.style.overflow='hidden';

    function idxFromY(clientY){
      const r=stack.getBoundingClientRect();
      return (clientY-r.top)/layerH;
    }
    function move(clientY){ setCut(idxFromY(clientY)); }

    stage.onpointermove=e=>{ if(e.pointerType==='mouse'||e.buttons) move(e.clientY); };
    stage.onpointerdown=e=>{ move(e.clientY); };
    stage.onpointerup=e=>{ if(e.pointerType==='mouse') doCut(); };

    function doCut(){
      if(busy||!ov.classList.contains('open'))return;
      busy=true;
      line.style.opacity='0';
      stage.onpointermove=stage.onpointerdown=stage.onpointerup=null;
      const topH=cutIdx*layerH, stackH=n*layerH;
      stack.querySelectorAll('.sp-cut-layer').forEach(el=>{
        const i=+el.getAttribute('data-i');
        if(i<cutIdx){
          // moitié soulevée : décolle, cascade depuis la coupe, puis se glisse dessous
          el.style.setProperty('--dy',(stackH-topH)+'px');
          el.style.setProperty('--k',String(cutIdx-1-i));
          el.classList.add('cut-top');
        }else{
          // moitié dessous : remonte à la place libérée
          el.style.setProperty('--dy',(-topH)+'px');
          el.style.setProperty('--k',String(Math.max(0,i-cutIdx)));
          el.classList.add('cut-bot');
        }
      });
      const newDeck=deck.slice(cutIdx).concat(deck.slice(0,cutIdx));
      setTimeout(()=>{
        ov.classList.remove('open');
        stack.querySelectorAll('.sp-cut-layer').forEach(el=>{
          el.classList.remove('cut-top','cut-bot');
          el.style.removeProperty('--dy'); el.style.removeProperty('--k');
        });
        stage.onpointermove=stage.onpointerdown=stage.onpointerup=null;
        cutValidate=cutCancel=null;
        ctrl.abort();
        onDone(newDeck);
      },1250);
    }
    function cancel(){
      if(busy||!ov.classList.contains('open'))return;
      ov.classList.remove('open');
      stage.onpointermove=stage.onpointerdown=stage.onpointerup=null;
      cutValidate=cutCancel=null;
      ctrl.abort();
      manualModeActive=false;
      document.body.style.overflow='';
    }
    cutValidate=doCut; cutCancel=cancel;
    go.onclick=doCut;
  }

  /* ---- Rendu du tirage en mode piocher (emplacements vides) ---- */
  function renderSpreadSlots(spreadDef){
    const stage=$('#sp-spread');
    const layoutClass=`sp-layout-${spreadDef.layout}`;
    const n=spreadDef.positions.length;
    let positionsHtml='';
    for(let i=0;i<n;i++){
      const pos=spreadDef.positions[i]||{label:'',desc:''};
      positionsHtml+=`
        <div class="sp-pos" data-idx="${i}">
          ${n>1?`<span class="sp-order" aria-hidden="true">${i+1}</span>`:''}
          ${spreadDef.layout==='name'?`<div class="sp-name-letter">${esc(pos.label)}</div>`:''}
          <div class="sp-slot-empty" data-idx="${i}" role="button" tabindex="0" aria-label="Piocher une carte : ${esc(pos.label||('position '+(i+1)))}">
            <span class="sp-slot-plus">＋</span>
            <span class="sp-slot-txt">Piocher</span>
          </div>
          ${spreadDef.layout!=='name'?`<div class="sp-pos-label">${esc(pos.label)}</div>`:''}
          ${pos.desc&&spreadDef.layout!=='name'?`<div class="sp-pos-desc">${esc(pos.desc)}</div>`:''}
        </div>`;
    }

    stage.innerHTML=`
      <div class="sp-spread-bar">
        <h2><em>${esc(spreadDef.name)}</em></h2>
        <div class="sp-bar-right">
          <button id="sp-redraw">↻ Refaire</button>
          <button id="sp-close-spread">✕ Fermer</button>
        </div>
      </div>
      <div class="sp-stage">
        <div class="${layoutClass}">${positionsHtml}</div>
        <div class="sp-hint" id="sp-hint">Touchez un emplacement vide pour ouvrir l'éventail</div>
      </div>
      <button class="sp-reveal-all" id="sp-reveal-all" style="display:none">Tout révéler</button>`;

    stage.querySelectorAll('.sp-slot-empty').forEach(el=>{
      const go=()=>{
        // Ordre prédéterminé : seul l'emplacement suivant est piochable
        if(DRAW_ORDER==='fixed'){
          const nx=nextEmptySlot();
          if(nx>=0&&+el.dataset.idx!==nx){
            shakeEl(el);
            if(typeof toast==='function') toast('Piochez d\u2019abord la position '+(nx+1));
            return;
          }
        }
        openFan(+el.dataset.idx);
      };
      el.addEventListener('click',go);
      el.addEventListener('keydown',e=>{ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); go(); } });
    });

    const closeBtn=$('#sp-close-spread');
    if(closeBtn) closeBtn.addEventListener('click',closeSpread);
    const redraw=$('#sp-redraw');
    if(redraw) redraw.addEventListener('click',()=>{
      startSpread(spreadDef,true);
    });
    const revAll=$('#sp-reveal-all');
    if(revAll) revAll.addEventListener('click',revealAll);
    updateOrderBadges();
  }

  function buildSlotCard(card,idx){
    const kw=extractKeywords(card.md).slice(0,3);
    const div=document.createElement('div');
    div.className='sp-card';
    div.dataset.idx=String(idx);
    div.dataset.id=card.id;
    div.innerHTML=`
      <div class="sp-card-inner">
        <div class="sp-card-face sp-card-back"></div>
        <div class="sp-card-face sp-card-front">
          <div class="sp-card-imgwrap"><img src="${card.file}" alt="${esc(card.name)}" loading="lazy"></div>
          <div class="sp-card-info">
            <span class="nm">${esc(card.name)}</span>
            <span class="no">${String(card.num).padStart(2,'0')} · ${esc(card.familyName)}</span>
            ${kw.length?`<div class="kw">${kw.map(k=>esc(k)).join(' · ')}</div>`:''}
          </div>
        </div>
      </div>`;
    return div;
  }

  function nextEmptySlot(){ return manualSlots.findIndex(s=>!s); }

  function nextRevealIdx(){
    let next=-1;
    document.querySelectorAll('#sp-spread .sp-card').forEach(el=>{
      const i=+el.dataset.idx;
      if(!el.classList.contains('revealed')&&(next<0||i<next)) next=i;
    });
    return next;
  }

  function updateOrderBadges(){
    const positions=[...document.querySelectorAll('#sp-spread .sp-pos')];
    const cards=[...document.querySelectorAll('#sp-spread .sp-card')];
    if(!positions.length) return;
    let next = manualModeActive ? nextEmptySlot() : -1;
    if(next<0) next=nextRevealIdx();
    positions.forEach(p=>{
      const b=p.querySelector('.sp-order');
      if(b) b.classList.toggle('now',+p.dataset.idx===next);
    });
    // carte à révéler : halo doré pulsé (sinon le dos noir paraît cassé)
    cards.forEach(c=>c.classList.toggle('next-reveal',next>=0&&+c.dataset.idx===next&&!c.classList.contains('revealed')));
  }

  function shakeEl(el){
    if(!el) return;
    el.classList.remove('sp-shake'); void el.offsetWidth; el.classList.add('sp-shake');
    setTimeout(()=>el.classList.remove('sp-shake'),380);
  }

  function pulseSlot(idx){
    document.querySelectorAll('#sp-spread .sp-slot-empty').forEach(el=>{
      el.classList.toggle('next',+el.dataset.idx===idx);
    });
    updateOrderBadges();
  }

  function setHint(t){
    const h=$('#sp-hint');
    if(h){ h.textContent=t; h.style.display=''; }
  }

  function showRevealAll(){
    const b=$('#sp-reveal-all');
    if(b) b.style.display='';
  }

  function fillSlot(idx,card){
    manualSlots[idx]=card;
    const posEl=document.querySelector(`#sp-spread .sp-pos[data-idx="${idx}"]`);
    let cardEl=null;
    if(posEl){
      const empty=posEl.querySelector('.sp-slot-empty');
      cardEl=buildSlotCard(card,idx);
      if(empty) empty.replaceWith(cardEl); else posEl.insertBefore(cardEl,posEl.firstChild);
      cardEl.classList.add('dealt');
      cardEl.addEventListener('click',()=>revealCard(cardEl));
    }
    // Carte du jour manuelle : elle devient la carte du jour (mémorisée)
    if(currentSpread&&currentSpread.id==='jour'){
      try{ localStorage.setItem('tarot_cjd_'+LOCAL_DATE_KEY(),card.id); }catch(e){}
      setTimeout(()=>{ if(cardEl&&!cardEl.classList.contains('revealed')) revealCard(cardEl); },500);
    }
    drawnCards=manualSlots.filter(Boolean);
    const nx=nextEmptySlot();
    if(nx>=0){
      pulseSlot(nx);
      setHint('Choisissez votre carte dans l\u2019éventail');
      // Ordre prédéterminé : l'éventail s'enchaîne tout seul sur la position suivante
      if(DRAW_ORDER==='fixed'){
        setTimeout(()=>{
          const f=$('#sp-fan');
          if(manualModeActive&&nextEmptySlot()>=0&&!(f&&f.classList.contains('open'))) openFan(nextEmptySlot());
        },420);
      }
    }else{
      setHint('Touchez une carte pour la révéler');
      showRevealAll();
    }
  }

  /* ---- Éventail ---- */
  function openFan(slotIdx){
    if(!manualDeck.length) return;
    fanSlot=slotIdx;
    const pos=currentSpread&&currentSpread.positions[slotIdx];
    const t=$('#sp-fan-pos');
    if(t) t.textContent=pos?('Position : '+pos.label):'';
    renderFan();
    const ov=$('#sp-fan');
    if(ov) ov.classList.add('open');
  }
  function closeFan(){
    const ov=$('#sp-fan'); if(ov) ov.classList.remove('open');
    // L'emplacement à piocher continue de pulser pour guider
    if(manualModeActive){ const nx=nextEmptySlot(); if(nx>=0) pulseSlot(nx); }
  }

  const FAN_BACK='<span class="sp-fan-back"></span>';
  let fanAbort=null;

  function renderFan(keepScroll){
    const stage=$('#sp-fan-stage');
    const scroll=$('#sp-fan-scroll');
    const cnt=$('#sp-fan-count');
    const hint=$('#sp-fan-hint');
    if(!stage) return;
    if(fanAbort){ fanAbort.abort(); fanAbort=null; }
    const n=manualDeck.length;
    const vw=window.innerWidth, vh=window.innerHeight;
    const mobile=vw<700;
    let fanPad=0, fanStep=0, fanX0=0, fanW=0;
    let h='';

    if(mobile){
      /* éventail radial — l'arc occupe toute la hauteur de l'écran (rayon = ~48 % de la
         hauteur), pivot hors champ à gauche ; rotation au doigt + molette, avec inertie et snap. */
      const cardW=Math.round(Math.min(118,Math.max(88,vw*.24)));
      const R=Math.round(vh*.48);
      const px=Math.round(vw-cardW*.75-R), py=Math.round(vh*.5);
      for(let i=0;i<n;i++){
        h+=`<button type="button" class="sp-fan-card rad" data-i="${i}" style="--px:${px}px;--py:${py}px;--R:${R}px;--a:0deg;--w:${cardW}px" aria-label="Piocher la carte ${i+1}">${FAN_BACK}</button>`;
      }
      stage.style.width='100%';
      stage.style.height='';
      stage.style.transform='';
      if(hint) hint.textContent='Faites tourner l\u2019éventail puis touchez une carte';
    }else{
      /* éventail horizontal : arc léger étalé de droite à gauche.
         Le survol est piloté en JS (cible calculée depuis l'abscisse) : les cartes se
         chevauchant, un survol CSS provoquerait un clignotement de cible. */
      const cardW=Math.round(Math.min(110,Math.max(72,vw*.075)));
      const pad=Math.round(vw*.035);
      const step=Math.max(6,Math.floor((vw-2*pad-cardW)/Math.max(1,n-1)));
      fanPad=pad; fanStep=step; fanX0=vw-pad-cardW; fanW=cardW;
      for(let i=0;i<n;i++){
        const x=vw-pad-cardW-i*step;
        const t=n>1?((i/(n-1))*2-1):0;
        // extrémités en fondu : l'éventail semble continuer au-delà du cadre
        const fade=Math.max(.12,Math.min(1,1-Math.max(0,(Math.abs(t)-.78)/.22)*.9));
        h+=`<button type="button" class="sp-fan-card" data-i="${i}" style="--x:${Math.round(x)}px;--y:${(Math.pow(Math.abs(t),2)*10).toFixed(1)}px;--r:${(t*-4.5).toFixed(2)}deg;--w:${cardW}px;opacity:${fade.toFixed(2)}" aria-label="Piocher la carte ${i+1}">${FAN_BACK}</button>`;
      }
      stage.style.width='100%';
      stage.style.height='';
      stage.style.transform='';
      if(scroll){ scroll.classList.remove('vmode'); scroll.style.touchAction=''; scroll.style.overflow=''; }
      if(hint) hint.textContent='Cartes faces cachées — laissez-vous guider';
    }

    stage.innerHTML=h;
    if(cnt) cnt.textContent=n+' carte'+(n>1?'s':'');
    stage.querySelectorAll('.sp-fan-card').forEach(el=>{
      el.addEventListener('click',()=>pickFanCard(+el.dataset.i,el));
    });

    if(mobile){
      if(scroll){ scroll.style.touchAction='none'; scroll.style.overflow='hidden'; }
      if(!keepScroll) fanRot=(n-1)/2; // ouvert au centre : demi-cercle complet
      fanVel=0; fanDragPid=null; fanMovedFar=false;
      if(fanRaf){ cancelAnimationFrame(fanRaf); fanRaf=0; }
      bindFanRotation();
      updateFanRot();
    }else if(scroll){
      scroll.scrollLeft=0; scroll.scrollTop=0;
      // survol piloté : la carte survolée est déduite de l'abscisse du pointeur
      if(fanAbort)fanAbort.abort();
      fanAbort=new AbortController();
      const x0=fanX0;
      let lift=-1;
      const cards=[...stage.querySelectorAll('.sp-fan-card')];
      const onMove=e=>{
        if(e.pointerType&&e.pointerType!=='mouse')return;
        const r=stage.getBoundingClientRect();
        // la carte survolée = celle qui recevrait le clic (celle du dessus à cette abscisse)
        const idx=Math.max(0,Math.min(n-1,Math.floor((fanX0-(e.clientX-r.left))/fanStep + fanW/fanStep)));
        if(idx===lift)return;
        lift=idx;
        cards.forEach((el,j)=>el.classList.toggle('lift',j===idx));
      };
      const onLeave=()=>{ lift=-1; cards.forEach(el=>el.classList.remove('lift')); };
      scroll.addEventListener('pointermove',onMove,{signal:fanAbort.signal});
      scroll.addEventListener('pointerleave',onLeave,{signal:fanAbort.signal});
    }
  }

  /* ---- Rotation de l'éventail radial (mobile) : doigt + inertie + snap ---- */
  const FAN_PX_PER_CARD=44, FAN_DEG_PER_CARD=3.6;
  let fanRot=0, fanVel=0, fanRaf=0, fanDragPid=null, fanMovedFar=false;

  function updateFanRot(){
    const stage=$('#sp-fan-stage'); if(!stage)return;
    const cards=stage.querySelectorAll('.sp-fan-card');
    if(!cards.length)return;
    const n=manualDeck.length, cnt=$('#sp-fan-count');
    cards.forEach(el=>{
      const a=((+el.dataset.i)-fanRot)*FAN_DEG_PER_CARD;
      const ad=Math.abs(a);
      if(ad>92){ el.style.visibility='hidden'; return; }
      el.style.visibility='';
      const k=Math.max(0,1-ad/16);
      el.style.setProperty('--a',a.toFixed(2)+'deg');
      el.style.setProperty('--s',(1+.24*k).toFixed(3));
      el.style.setProperty('--o',Math.max(0,Math.min(1,1-(ad-68)/22)).toFixed(3));
      el.style.zIndex=el.classList.contains('picked')?'200':String(100-Math.round(ad));
    });
    if(cnt){
      const apex=Math.max(0,Math.min(n-1,Math.round(fanRot)));
      cnt.textContent='carte '+(apex+1)+' / '+n;
    }
  }

  function fanLoop(){
    const n=manualDeck.length;
    if(fanDragPid!==null){ fanRaf=0; return; }
    if(Math.abs(fanVel)>.0015){
      fanRot+=fanVel*16;
      fanVel*=.94;
      if(fanRot<-.8){ fanRot=-.8; fanVel=0; }
      if(fanRot>n-1+.8){ fanRot=n-1+.8; fanVel=0; }
      updateFanRot();
      fanRaf=requestAnimationFrame(fanLoop);
      return;
    }
    const target=Math.max(0,Math.min(n-1,Math.round(fanRot)));
    const d=target-fanRot;
    if(Math.abs(d)>.002){
      fanRot+=d*.16;
      updateFanRot();
      fanRaf=requestAnimationFrame(fanLoop);
    }else{
      fanRot=target;
      updateFanRot();
      fanRaf=0;
    }
  }

  function bindFanRotation(){
    const surf=$('#sp-fan-scroll'); if(!surf)return;
    if(fanAbort){ fanAbort.abort(); }
    fanAbort=new AbortController();
    const sig={signal:fanAbort.signal};
    let lastY=0,lastT=0,vel=0,moved=0;
    surf.addEventListener('pointerdown',e=>{
      if(fanDragPid!==null)return;
      fanDragPid=e.pointerId;
      lastY=e.clientY; lastT=performance.now();
      vel=0; moved=0; fanVel=0; fanMovedFar=false;
      if(fanRaf){ cancelAnimationFrame(fanRaf); fanRaf=0; }
    },sig);
    window.addEventListener('pointermove',e=>{
      if(e.pointerId!==fanDragPid)return;
      const t=performance.now(), dt=Math.max(1,t-lastT);
      const dy=e.clientY-lastY;
      moved+=Math.abs(dy);
      if(moved>10) fanMovedFar=true;
      fanRot+=(-dy/FAN_PX_PER_CARD);
      vel=(-dy/FAN_PX_PER_CARD)/dt;
      lastY=e.clientY; lastT=t;
      updateFanRot();
    },sig);
    const up=e=>{
      if(e.pointerId!==fanDragPid)return;
      fanDragPid=null;
      fanVel=Math.max(-.05,Math.min(.05,vel));
      fanRaf=requestAnimationFrame(fanLoop);
    };
    window.addEventListener('pointerup',up,sig);
    window.addEventListener('pointercancel',up,sig);
    // molette : fait tourner l'éventail comme un doigt
    const onWheel=e=>{
      e.preventDefault();
      if(fanDragPid!==null)return;
      if(fanRaf){ cancelAnimationFrame(fanRaf); fanRaf=0; }
      const d=(Math.abs(e.deltaY)>=Math.abs(e.deltaX)?e.deltaY:e.deltaX);
      fanRot+=d/180;
      fanRot=Math.max(-1,Math.min(manualDeck.length,fanRot));
      updateFanRot();
      clearTimeout(wheelSnap);
      wheelSnap=setTimeout(()=>{ fanVel=0; fanRaf=requestAnimationFrame(fanLoop); },140);
    };
    let wheelSnap=0;
    surf.addEventListener('wheel',onWheel,{passive:false,signal:fanAbort.signal});
  }

  function pickFanCard(i,el){
    if(fanMovedFar) return; // le doigt a tourné l'éventail : ce n'était pas une pioche
    const card=manualDeck[i];
    if(!card) return;
    el.classList.add('picked');
    setTimeout(()=>{
      closeFan();
      manualDeck.splice(i,1);
      fillSlot(fanSlot,card);
    },280);
  }

  /* ---- Rendu du tirage ---- */
  function renderSpread(spreadDef,cards){
    const stage=$('#sp-spread');
    const layoutClass=`sp-layout-${spreadDef.layout}`;
    let positionsHtml='';

    cards.forEach((card,i)=>{
      const pos=spreadDef.positions[i]||{label:'',desc:''};
      const kw=extractKeywords(card.md).slice(0,3);
      positionsHtml+=`
        <div class="sp-pos" data-idx="${i}">
          ${spreadDef.positions.length>1?`<span class="sp-order" aria-hidden="true">${i+1}</span>`:''}
          ${spreadDef.layout==='name'?`<div class="sp-name-letter">${pos.label}</div>`:''}
          <div class="sp-card" data-idx="${i}" data-id="${card.id}">
            <div class="sp-card-inner">
              <div class="sp-card-face sp-card-back"></div>
              <div class="sp-card-face sp-card-front">
                <div class="sp-card-imgwrap"><img src="${card.file}" alt="${esc(card.name)}" loading="lazy"></div>
                <div class="sp-card-info">
                  <span class="nm">${esc(card.name)}</span>
                  <span class="no">${String(card.num).padStart(2,'0')} · ${esc(card.familyName)}</span>
                  ${kw.length?`<div class="kw">${kw.map(k=>esc(k)).join(' · ')}</div>`:''}
                </div>
              </div>
            </div>
          </div>
          ${spreadDef.layout!=='name'?`<div class="sp-pos-label">${esc(pos.label)}</div>`:''}
          ${pos.desc&&spreadDef.layout!=='name'?`<div class="sp-pos-desc">${esc(pos.desc)}</div>`:''}
        </div>`;
    });

    let layoutInner=positionsHtml;
    if(spreadDef.layout==='hexagram'){
      // Restructurer en triangles
      const ext=positionsHtml.match(/<div class="sp-pos"[^>]*>[\s\S]*?<\/div>(?=\s*<div class="sp-pos"|<\/div>$)/g);
      layoutInner=`
        <div class="sp-hex-label">Influences extérieures</div>
        <div class="sp-hex-triangle-down">
          <div class="sp-hex-row">${positionsHtml.match(/<div class="sp-pos" data-idx="0"[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/)?'<div class="sp-pos" data-idx="0">ext1</div>':''}</div>
        </div>`;
      // Simplifié : on garde le row pour l'instant
      layoutInner=positionsHtml;
    }

    stage.innerHTML=`
      <div class="sp-spread-bar">
        <h2><em>${esc(spreadDef.name)}</em></h2>
        <div class="sp-bar-right">
          <button id="sp-redraw">↻ Refaire</button>
          <button id="sp-close-spread">✕ Fermer</button>
        </div>
      </div>
      <div class="sp-stage">
        ${spreadDef.layout==='hexagram'?'<div class="sp-hex-label" style="margin-bottom:-.5rem">▼ Influences extérieures</div>':''}
        <div class="${layoutClass}">${layoutInner}</div>
        ${spreadDef.layout==='hexagram'?'<div class="sp-hex-label" style="margin-top:-.5rem">▲ Influences personnelles</div>':''}
        ${spreadDef.layout==='hexagram'?'<div class="sp-hex-label">Résultat</div>':''}
        <div class="sp-hint" id="sp-hint">Touchez une carte pour la révéler</div>
      </div>
      <button class="sp-reveal-all" id="sp-reveal-all">Tout révéler</button>`;

    // Wire cards
    stage.querySelectorAll('.sp-card').forEach(el=>{
      el.addEventListener('click',()=>revealCard(el));
    });
    updateOrderBadges();

    // Wire buttons
    const closeBtn=$('#sp-close-spread');
    if(closeBtn) closeBtn.addEventListener('click',closeSpread);
    const redraw=$('#sp-redraw');
    if(redraw) redraw.addEventListener('click',()=>{
      if(spreadDef.id==='jour'){localStorage.removeItem('tarot_cjd_'+LOCAL_DATE_KEY());}
      startSpread(spreadDef);
    });
    const revAll=$('#sp-reveal-all');
    if(revAll) revAll.addEventListener('click',revealAll);

    // Auto-reveal pour la carte du jour
    if(spreadDef.id==='jour'){
      setTimeout(()=>{
        const card=stage.querySelector('.sp-card');
        if(card&&!card.classList.contains('revealed')) revealCard(card);
      },400);
    }
  }

  function revealCard(el){
    if(el.classList.contains('revealed')) {
      // Si déjà révélée → ouvrir le détail
      const id=el.dataset.id;
      const card=ALL_CARDS.find(c=>c.id===id);
      if(card){
        if(currentSpread&&currentSpread.id==='jour'&&typeof window.tarotOpenCard==='function'){window.tarotOpenCard(card);return;}
        openDrawer(card,el.dataset.idx);
      }
      return;
    }
    // Ordre prédéterminé : on révèle la prochaine carte, pas une autre
    if(DRAW_ORDER==='fixed'){
      const nx=nextRevealIdx();
      if(nx>=0&&+el.dataset.idx!==nx){
        shakeEl(el);
        if(typeof toast==='function') toast('Révélez d\u2019abord la position '+(nx+1));
        return;
      }
    }
    el.classList.add('revealed');
    const hint=$('#sp-hint');
    if(hint){
      const remaining=document.querySelectorAll('#sp-spread .sp-card:not(.revealed)').length;
      if(remaining===0) hint.style.display='none';
    }
    updateOrderBadges();
  }

  function revealAll(){
    document.querySelectorAll('#sp-spread .sp-card').forEach(el=>{
      el.classList.add('revealed');
    });
    const hint=$('#sp-hint');if(hint)hint.style.display='none';
    updateOrderBadges();
  }

  function openSpread(){const s=$('#sp-spread');if(s)s.classList.add('open');document.body.style.overflow='hidden';}
  function closeSpread(){
    const s=$('#sp-spread');if(s){s.classList.remove('open');s.innerHTML='';}
    closeFan();
    const c=$('#sp-cut');if(c)c.classList.remove('open');
    manualModeActive=false;manualDeck=[];manualSlots=[];
    closeDrawer();document.body.style.overflow='';
  }

  /* ---- Drawer (vue rapide) ---- */
  function openDrawer(card,idx){
    if(currentSpread && currentSpread.id==='trois'){openDrawerTrois();return;}
    const posLabel = currentSpread && currentSpread.positions[idx] ? currentSpread.positions[idx].label : '';
    const kw=extractKeywords(card.md);
    const kwEndroit=kw.slice(0,4);
    const kwEnvers=extractKeywordsReversed(card.md).slice(0,4);
    const es=card.es||{};
    const fam=TAROT.families.find(f=>f.key===card.family);
    const ac=fam?fam.accent:'#c9a227';

    let drawer=$('#sp-drawer');
    if(!drawer){
      drawer=document.createElement('div');
      drawer.id='sp-drawer';
      document.body.appendChild(drawer);
    }
    drawer.innerHTML=`
      <div class="sp-drawer-backdrop" id="sp-drawer-bd"></div>
      <button class="sp-drawer-close" id="sp-drawer-x"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
      <div class="sp-drawer-panel">
        <div class="sp-drawer-handle"></div>
        <div class="sp-drawer-body" style="--ac:${ac}">
          <div class="sp-drawer-card">
            <img src="${card.file}" alt="${esc(card.name)}">
            <div class="sp-drawer-num">${String(card.num).padStart(2,'0')} / 78 · ${esc(card.familyName)}</div>
          </div>
          <div class="sp-drawer-content">
            ${posLabel?`<div class="sp-drawer-meta">${esc(posLabel)}</div>`:''}
            <h3><em>${esc(card.name)}</em></h3>
            <div class="sp-drawer-meta">${esc(card.element)} · ${esc(card.familyName)}</div>
            ${es.reponse?`<div class="sp-drawer-reponse">${esc(es.reponse)}</div>`:''}
            ${es.affirmation?`<div class="sp-drawer-affirm">« ${esc(es.affirmation)} »</div>`:''}
            ${(kwEndroit.length||kwEnvers.length)?`<div class="sp-drawer-kw">
              ${kwEndroit.length?`<div><h4>À l'endroit</h4><ul>${kwEndroit.map(k=>`<li>${esc(k)}</li>`).join('')}</ul></div>`:''}
              ${kwEnvers.length?`<div><h4>À l'envers</h4><ul>${kwEnvers.map(k=>`<li>${esc(k)}</li>`).join('')}</ul></div>`:''}
            </div>`:''}
          </div>
        </div>
      </div>`;

    drawer.classList.add('open');
    $('#sp-drawer-bd').addEventListener('click',closeDrawer);
    $('#sp-drawer-x').addEventListener('click',closeDrawer);
  }

  function openDrawerTrois(){
    const cols=drawnCards.map((card,i)=>{
      const el=document.querySelector(`#sp-spread .sp-card[data-idx="${i}"]`);
      if(!el||!el.classList.contains('revealed')) return '';
      const pos=currentSpread.positions[i]||{label:''};
      const kw=extractKeywords(card.md).slice(0,3);
      const es=card.es||{};
      const fam=TAROT.families.find(f=>f.key===card.family);
      const ac=fam?fam.accent:'#c9a227';
      return `<div class="sp-trois-col" style="--ac:${ac}">
        <div class="sp-trois-pos">${esc(pos.label)}</div>
        <img src="${card.file}" alt="${esc(card.name)}">
        <div class="sp-trois-name"><em>${esc(card.name)}</em></div>
        ${es.reponse?`<div class="sp-trois-rep">${esc(es.reponse)}</div>`:''}
        ${kw.length?`<div class="sp-trois-kw">${kw.map(k=>esc(k)).join(' · ')}</div>`:''}
      </div>`;
    }).join('');
    let drawer=$('#sp-drawer');
    if(!drawer){drawer=document.createElement('div');drawer.id='sp-drawer';document.body.appendChild(drawer);}
    drawer.innerHTML=`
      <div class="sp-drawer-backdrop" id="sp-drawer-bd"></div>
      <button class="sp-drawer-close" id="sp-drawer-x"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
      <div class="sp-drawer-panel">
        <div class="sp-drawer-handle"></div>
        <div class="sp-trois-grid">${cols}</div>
      </div>`;
    drawer.classList.add('open');
    $('#sp-drawer-bd').addEventListener('click',closeDrawer);
    $('#sp-drawer-x').addEventListener('click',closeDrawer);
  }

  function closeDrawer(){
    const d=$('#sp-drawer');
    if(d){d.classList.remove('open');}
  }

  function extractKeywordsReversed(md){
    if(!md) return [];
    const m=md.match(/### À l['']envers\s*\n([\s\S]*?)(?=^### |^## |(?![\s\S]))/i);
    if(!m) return [];
    return[...m[1].matchAll(/^-\s+(.+)$/gm)].map(([,v])=>v.trim());
  }

  /* ---- Init (appelé par l'hôte une fois TAROT prêt) ---- */
  function init(){
    if(typeof TAROT==='undefined'||!TAROT.families){console.warn('tarot-spreads: TAROT non prêt');return;}
    ALL_CARDS=TAROT.families.flatMap(f=>f.cards);
    // dos de carte RWS (Pamela Colman Smith) fourni par l'hôte
    if(window.TAROT_BACK) document.documentElement.style.setProperty('--tarot-back','url("'+window.TAROT_BACK+'")');
    inject();
    // éventail responsive : recalcul en direct quand la fenêtre change de taille
    if(!window.__spFanResize){
      window.__spFanResize=true;
      let raf=0;
      window.addEventListener('resize',()=>{
        const f=$('#sp-fan');
        if(!f||!f.classList.contains('open'))return;
        if(!raf)raf=requestAnimationFrame(()=>{raf=0;renderFan(true);});
      },{passive:true});
    }
    if(!window.__spEsc){window.__spEsc=true;document.addEventListener('keydown',e=>{
      const f=$('#sp-fan'),c=$('#sp-cut'),d=$('#sp-drawer'),s=$('#sp-spread'),m=$('#sp-menu');
      if(e.key==='Enter'&&cutValidate&&c&&c.classList.contains('open')){cutValidate();e.stopPropagation();return;}
      if(e.key!=='Escape')return;
      if(d&&d.classList.contains('open')){closeDrawer();e.stopPropagation();return;}
      if(f&&f.classList.contains('open')){closeFan();e.stopPropagation();return;}
      if(c&&c.classList.contains('open')){if(cutCancel)cutCancel();e.stopPropagation();return;}
      if(s&&s.classList.contains('open')){closeSpread();e.stopPropagation();return;}
      if(m&&m.classList.contains('open')){closeMenu();e.stopPropagation();return;}
    });}
  }

  // API publique
  window.TarotSpreads={init:init,open:openMenu,close:closeMenu,closeSpread:closeSpread,
    getDrawMode:getDrawMode,setDrawMode:setDrawMode,cycleDrawMode:cycleDrawMode,
    getDrawOrder:getDrawOrder,setDrawOrder:setDrawOrder};

})();
