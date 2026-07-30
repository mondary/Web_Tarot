/* deck-mode.js — Bascule Rider-Waite / Marseille, partagé entre toutes les vues.
   Auto-injecte le bouton réglages + panneau + CSS. Se charge APRÈS data.js. */
(function(){
  'use strict';
  window.DECK_MODE = localStorage.getItem('tarot_deck') === 'marseille' ? 'marseille' : 'rider';
  window.cardImg  = function(c){ return (window.DECK_MODE === 'marseille' && c.marseille) ? c.marseille : c.file; };
  window.cardName = function(c){ return (window.DECK_MODE === 'marseille' && c.nameM) ? c.nameM : c.name; };

  // ---- CSS injecté une seule fois ----
  const css = `
.top-right{position:fixed;top:1.5rem;right:2.2rem;z-index:250;display:flex;align-items:center;gap:.6rem}
.settings-btn{width:34px;height:34px;display:grid;place-items:center;border:1px solid var(--line);border-radius:50%;
  background:var(--bg);color:var(--muted);transition:color .2s,border-color .2s,transform .4s var(--ease);flex-shrink:0;cursor:pointer}
.settings-btn:hover{color:var(--accent);border-color:var(--accent)}
.settings-btn svg{width:16px;height:16px}
.settings-btn[aria-expanded="true"]{transform:rotate(60deg);color:var(--accent);border-color:var(--accent)}
.settings-pop{position:fixed;top:4.4rem;right:2.2rem;z-index:240;min-width:260px;padding:1.4rem 1.4rem 1.2rem;
  background:var(--bg-2);border:1px solid var(--line);border-radius:18px;box-shadow:0 20px 50px rgba(0,0,0,.5);
  opacity:0;visibility:hidden;transform:translateY(-8px);transition:opacity .2s,transform .2s,visibility .2s}
.settings-pop.open{opacity:1;visibility:visible;transform:none}
.settings-pop h3{font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted);margin:0 0 .9rem}
.deck-options{display:flex;flex-direction:column;gap:.5rem}
.deck-opt{display:flex;align-items:center;gap:.8rem;padding:.7rem .85rem;border:1px solid var(--line);border-radius:12px;
  cursor:pointer;transition:border-color .2s,background .2s}
.deck-opt:hover{border-color:var(--accent)}
.deck-opt.active{border-color:var(--accent);background:rgba(201,162,39,.08)}
.deck-opt .swatch{width:30px;height:42px;border-radius:4px;object-fit:cover;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.4)}
.deck-opt .lab{display:flex;flex-direction:column;gap:.15rem}
.deck-opt .lab b{font-weight:500;font-size:.85rem;color:var(--fg)}
.deck-opt .lab span{font-size:.68rem;color:var(--muted)}
.deck-opt .chk{margin-left:auto;color:var(--accent);opacity:0}
.deck-opt.active .chk{opacity:1}
@media (max-width:900px){
  .top-right{right:1.2rem;top:.9rem;gap:.4rem}
  .settings-pop{right:1.2rem;min-width:240px}
}`;
  const style = document.createElement('style');
  style.textContent = css;
  document.head.appendChild(style);

  // ---- Injection du bouton + panneau ----
  function buildUI(){
    if(document.getElementById('settings-btn')) return; // déjà présent (index.html)
    // Carte échantillon pour les aperçus (La Mort XIII)
    const sample = (window.TAROT && window.TAROT.families || [])
      .flatMap(f => f.cards).find(c => c.id && c.id.startsWith('a_13_'));
    const riderSrc = sample ? sample.file : '';
    const marsSrc  = sample ? (sample.marseille || sample.file) : '';

    const wrap = document.createElement('div');
    wrap.className = 'top-right';
    wrap.innerHTML = `
      <button class="settings-btn" id="settings-btn" aria-label="Réglages du deck" aria-expanded="false" title="Réglages du deck">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      </button>`;
    // On insère le wrapper en début de body, et on y déplace aussi le mode-switch existant
    document.body.insertBefore(wrap, document.body.firstChild);
    const existingSwitch = document.getElementById('mode-switch');
    if(existingSwitch){
      existingSwitch.style.position = 'static';
      existingSwitch.style.top = 'auto';
      existingSwitch.style.right = 'auto';
      existingSwitch.style.zIndex = 'auto';
      wrap.appendChild(existingSwitch);
    }

    const pop = document.createElement('div');
    pop.className = 'settings-pop';
    pop.id = 'settings-pop';
    pop.setAttribute('role','dialog');
    pop.setAttribute('aria-label','Réglages du deck');
    pop.innerHTML = `
      <h3>Deck · Illustrations</h3>
      <div class="deck-options" id="deck-options">
        <div class="deck-opt ${window.DECK_MODE==='rider'?'active':''}" data-deck="rider" role="button" tabindex="0">
          <img class="swatch" id="swatch-rider" alt="" src="${riderSrc}">
          <div class="lab"><b>Rider-Waite-Smith</b><span>Illustrations modernes</span></div>
          <span class="chk"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg></span>
        </div>
        <div class="deck-opt ${window.DECK_MODE==='marseille'?'active':''}" data-deck="marseille" role="button" tabindex="0">
          <img class="swatch" id="swatch-marseille" alt="" src="${marsSrc}">
          <div class="lab"><b>Tarot de Marseille</b><span>Tradition ancienne</span></div>
          <span class="chk"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg></span>
        </div>
      </div>`;
    document.body.appendChild(pop);

    wire(pop);
  }

  function wire(pop){
    const btn = document.getElementById('settings-btn');
    const opts = document.querySelectorAll('#deck-options .deck-opt');
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const open = pop.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    opts.forEach(o => {
      const handler = () => {
        if(o.classList.contains('active')) return;
        localStorage.setItem('tarot_deck', o.dataset.deck);
        // Recharge la page pour appliquer le deck partout (simple et fiable).
        location.reload();
      };
      o.addEventListener('click', handler);
      o.addEventListener('keydown', e => { if(e.key==='Enter'||e.key===' '){ e.preventDefault(); handler(); } });
    });
    document.addEventListener('click', e => {
      if(pop.classList.contains('open') && !pop.contains(e.target) && !btn.contains(e.target)){
        pop.classList.remove('open');
        btn.setAttribute('aria-expanded','false');
      }
    });
  }

  // Init quand le DOM est prêt
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', buildUI);
  } else {
    buildUI();
  }
})();
