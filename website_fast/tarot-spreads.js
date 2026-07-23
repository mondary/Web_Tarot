/* ============================================================
   TAROT SPREADS — Système de tirages autonome
   Dépendance : data.js (const TAROT)
   Auto-injection : overlay HTML + CSS + FAB button
   ============================================================ */
(function(){
  'use strict';
  if(typeof TAROT==='undefined'){console.warn('tarot-spreads.js: TAROT non trouvé');return;}

  const ALL_CARDS = TAROT.families.flatMap(f=>f.cards);
  const $ = s=>document.querySelector(s);
  const esc = s=>String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  const LOCAL_DATE_KEY = ()=>{const n=new Date();return[n.getFullYear(),String(n.getMonth()+1).padStart(2,'0'),String(n.getDate()).padStart(2,'0')].join('-');};

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
      id:'jour', name:'Carte du Jour', icon:'☀',
      desc:'Une lame pour vous guider aujourdu2019hui',
      positions:[{label:'Carte du jour',desc:"L'énergie du jour"}],
      layout:'single'
    },
    {
      id:'trois', name:'Passé · Présent · Futur', icon:'⏳',
      desc:'Le tirage classique à 3 cartes',
      positions:[
        {label:'Le Passé',desc:'Les racines de la situation'},
        {label:'Le Présent',desc:"L'état actuel"},
        {label:'Le Futur',desc:'La tendance, vers quoi on se dirige'}
      ],
      layout:'row'
    },
    {
      id:'decision', name:'Tirage de Décision', icon:'⚖',
      desc:'Pour éclairer un choix ou une situation précise',
      positions:[
        {label:'La situation',desc:'La situation actuelle'},
        {label:'Les forces',desc:'Ce qui pousse à agir'},
        {label:'Les obstacles',desc:'Ce qui freine'},
        {label:'Le conseil',desc:'Le conseil de la carte'},
        {label:'Le résultat',desc:'Le résultat probable'}
      ],
      layout:'row'
    },
    {
      id:'celtique', name:'Croix Celtique', icon:'✚',
      desc:'Le tirage de référence, le plus complet (10 cartes)',
      positions:[
        {label:'Le Présent',desc:'La situation centrale'},
        {label:'Le Défi',desc:"L'obstacle croisé"},
        {label:'Le Fondement',desc:"L'inconscient, les racines"},
        {label:'Le Passé récent',desc:''},
        {label:'Le But conscient',desc:''},
        {label:'Le Futur proche',desc:''},
        {label:'Votre Attitude',desc:'Votre position'},
        {label:'Les Influences',desc:'Extérieures'},
        {label:'Espiours',desc:'Espoirs et craintes'},
        {label:'Le Résultat',desc:'Le résultat final'}
      ],
      layout:'celtic'
    },
    {
      id:'relation', name:'Tirage de Relation', icon:'♥',
      desc:'Analyser un lien (amour, amitié, famille, travail)',
      positions:[
        {label:'Vous',desc:'Vous dans la relation'},
        {label:"L'autre",desc:"L'autre personne"},
        {label:'Ce qui unit',desc:'Ce qui vous unit'},
        {label:'Ce qui sépare',desc:'Ce qui vous sépare'},
        {label:'La dynamique',desc:'La dynamique actuelle'},
        {label:"L'évolution",desc:"L'évolution probable"}
      ],
      layout:'row'
    },
    {
      id:'prenom', name:'Tirage du Prénom', icon:'A',
      desc:'Une carte par lettre du prénom',
      positions:[],
      layout:'name',
      needsInput:true,
      inputLabel:'Tapez un prénom',
      inputPlaceholder:'Ex: ALICE'
    },
    {
      id:'hexagramme', name:"L'Hexagramme", icon:'◈',
      desc:'Influences extérieures vs personnelles (7 cartes)',
      positions:[
        {label:'Force ext. 1',desc:''},
        {label:'Force ext. 2',desc:''},
        {label:'Conseil ext.',desc:''},
        {label:'Force int. 1',desc:''},
        {label:'Force int. 2',desc:''},
        {label:'Conseil int.',desc:''},
        {label:'Résultat',desc:'Le résultat final'}
      ],
      layout:'hexagram'
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
.sp-card{position:relative;border-radius:.9rem;overflow:hidden;cursor:pointer;
  width:170px;aspect-ratio:2/3;perspective:800px;flex-shrink:0}
.sp-card-inner{position:relative;width:100%;height:100%;transition:transform .7s cubic-bezier(.16,1,.3,1);transform-style:preserve-3d}
.sp-card.revealed .sp-card-inner{transform:rotateY(180deg)}
.sp-card-face{position:absolute;inset:0;border-radius:.9rem;overflow:hidden;backface-visibility:hidden;-webkit-backface-visibility:hidden}
.sp-card-back{background:linear-gradient(135deg,#0a0907,#15110d);border:1px solid rgba(201,162,39,.12);
  display:grid;place-items:center}
.sp-card-back svg{width:40%;height:40%;color:rgba(201,162,39,.15)}
.sp-card-front{transform:rotateY(180deg);background:#fff;display:flex;flex-direction:column}
.sp-card-front .sp-card-imgwrap{flex:1;display:flex;align-items:center;justify-content:center;padding:.4rem;overflow:hidden}
.sp-card-front img{height:100%;width:auto;object-fit:contain}
.sp-card-front .sp-card-info{padding:.4rem .5rem .45rem;background:#fff;border-top:1px solid rgba(0,0,0,.06);text-align:center}
.sp-card-front .sp-card-info .nm{font-family:'Cormorant Garamond',serif;font-size:.82rem;font-weight:600;color:#1c1814;line-height:1;display:block}
.sp-card-front .sp-card-info .no{font-family:'DM Mono',monospace;font-size:.5rem;color:#a59c8e;letter-spacing:.08em}
.sp-card-front .sp-card-info .kw{margin-top:.2rem;font-size:.55rem;color:#6f6a5f;line-height:1.2;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

/* Position label */
.sp-pos{display:flex;flex-direction:column;align-items:center;gap:.5rem}
.sp-pos-label{font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.16em;text-transform:uppercase;
  color:#8a8378;text-align:center;max-width:140px;line-height:1.3}
.sp-pos-desc{font-size:.68rem;color:rgba(138,131,120,.6);text-align:center;max-width:140px;line-height:1.3}
.sp-card:hover .sp-card-back{border-color:rgba(201,162,39,.4)}

/* Layouts */
.sp-layout-single{display:flex;justify-content:center}
.sp-layout-row{display:flex;flex-wrap:wrap;justify-content:center;gap:1.2rem}
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
.sp-layout-celtic .sp-card{width:80px}
.sp-layout-celtic .sp-pos-label{font-size:.5rem;max-width:90px}

.sp-layout-hexagram{position:relative;width:100%;max-width:500px;display:flex;flex-direction:column;align-items:center;gap:1rem}
.sp-hex-row{display:flex;gap:1rem}
.sp-hex-triangle-down,.sp-hex-triangle-up{display:flex;flex-direction:column;align-items:center;gap:.8rem}
.sp-hex-center{margin:0 auto}
.sp-hex-label{font-family:'DM Mono',monospace;font-size:.56rem;letter-spacing:.14em;text-transform:uppercase;
  color:rgba(201,162,39,.5);margin-bottom:.4rem;text-align:center}

.sp-layout-name{display:flex;flex-wrap:wrap;justify-content:center;gap:1rem}
.sp-layout-name .sp-pos{gap:.3rem}
.sp-layout-name .sp-card{width:90px}
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
  .sp-card{width:130px}
  .sp-layout-row{gap:.8rem}
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
`;

  /* ---- Injection HTML ---- */
  function inject(){
    // CSS
    const style=document.createElement('style');
    style.textContent=CSS;
    document.head.appendChild(style);

    // FAB button
    if(!document.getElementById('sp-fab')){
      const fab=document.createElement('button');
      fab.id='sp-fab';
      fab.className='sp-fab';
      fab.innerHTML=`<span class="dot"></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="9" width="18" height="6" rx="1"/><path d="M3 12h18"/></svg><span>Tirages</span>`;
      fab.addEventListener('click',openMenu);
      document.body.appendChild(fab);
    }

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
        </div>`;
      document.body.appendChild(menu);
      $('#sp-menu-close').addEventListener('click',closeMenu);
      menu.addEventListener('click',e=>{if(e.target===menu)closeMenu();});
    }

    // Spread overlay
    if(!document.getElementById('sp-spread')){
      const spread=document.createElement('div');
      spread.id='sp-spread';
      document.body.appendChild(spread);
    }

    // Build menu items
    renderMenuItems();
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
  function shuffleAndDraw(count){
    const pool=[...ALL_CARDS];
    for(let i=pool.length-1;i>0;i--){
      const j=Math.floor(Math.random()*(i+1));
      [pool[i],pool[j]]=[pool[j],pool[i]];
    }
    return pool.slice(0,count);
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

  function startSpread(spreadDef){
    currentSpread=spreadDef;

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
          ${spreadDef.layout==='name'?`<div class="sp-name-letter">${pos.label}</div>`:''}
          <div class="sp-card" data-idx="${i}" data-id="${card.id}">
            <div class="sp-card-inner">
              <div class="sp-card-face sp-card-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width=".5"><circle cx="12" cy="12" r="11"/><path d="M12 1v22M1 12h22M4 4l16 16M20 4 4 20"/></svg>
              </div>
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
      // Si déjà révélée → ouvrir le drawer
      const id=el.dataset.id;
      const card=ALL_CARDS.find(c=>c.id===id);
      if(card) openDrawer(card,el.dataset.idx);
      return;
    }
    el.classList.add('revealed');
    const hint=$('#sp-hint');
    if(hint){
      const remaining=$('#sp-spread .sp-card:not(.revealed)').length;
      if(remaining===0) hint.style.display='none';
    }
  }

  function revealAll(){
    document.querySelectorAll('#sp-spread .sp-card').forEach(el=>{
      el.classList.add('revealed');
    });
    const hint=$('#sp-hint');if(hint)hint.style.display='none';
  }

  function openSpread(){const s=$('#sp-spread');if(s)s.classList.add('open');document.body.style.overflow='hidden';}
  function closeSpread(){const s=$('#sp-spread');if(s){s.classList.remove('open');s.innerHTML='';}closeDrawer();document.body.style.overflow='';}

  /* ---- Drawer (vue rapide) ---- */
  function openDrawer(card,idx){
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

  /* ---- Init ---- */
  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',inject);
  } else {
    inject();
  }

  // API publique
  window.TarotSpreads={open:openMenu,close:closeMenu};

})();
