/* =========================================================
   tarot-features.js — Scan + Tirages (shared across all views)
   Auto-injects buttons, overlays, and logic.
   Requires: TAROT, ALL_CARDS, openCard(id) in global scope.
   ========================================================= */
(function(){
  'use strict';

  function ready(fn){
    if(document.readyState!=='loading') fn();
    else document.addEventListener('DOMContentLoaded',fn);
  }

  function waitForGlobals(cb){
    function check(){
      if(typeof TAROT!=='undefined' && typeof ALL_CARDS!=='undefined' && typeof openCard==='function'){
        cb();
      } else {
        setTimeout(check,100);
      }
    }
    ready(check);
  }

  function injectStyles(){
    if(document.getElementById('tf-styles')) return;
    const css = `
.tf-scan-btn{
  position:fixed;right:max(1.4rem,env(safe-area-inset-right));bottom:max(1.4rem,env(safe-area-inset-bottom));z-index:500;
  width:54px;height:54px;border-radius:50%;display:grid;place-items:center;background:var(--accent,#c9a227);color:#050505;
  box-shadow:0 12px 28px rgba(0,0,0,.42);transition:transform .2s ease-out,background .2s ease-out;border:none;cursor:pointer
}
.tf-scan-btn:hover{transform:translateY(-2px);background:var(--fg,#f1ede4)}
.tf-scan-btn svg{width:22px;height:22px}
.tf-draws-btn{
  position:fixed;left:max(1.4rem,env(safe-area-inset-left));bottom:max(4.6rem,calc(env(safe-area-inset-bottom) + 3.2rem));z-index:500;
  display:inline-flex;align-items:center;gap:.6rem;padding:.8rem 1.15rem;border-radius:50px;
  background:rgba(10,9,7,.72);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  border:1px solid rgba(241,237,228,.08);color:var(--fg,#f1ede4);
  font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.18em;text-transform:uppercase;cursor:pointer;
  box-shadow:0 12px 28px rgba(0,0,0,.42);transition:transform .2s,border-color .2s,color .2s
}
.tf-draws-btn:hover{transform:translateY(-2px);border-color:var(--accent,#c9a227);color:var(--accent,#c9a227)}
.tf-draws-btn svg{width:16px;height:16px}
.tf-draws-btn .dot{width:6px;height:6px;border-radius:50%;background:var(--accent,#c9a227);box-shadow:0 0 8px var(--accent,#c9a227)}

#tf-draws{position:fixed;inset:0;z-index:8400;display:none;align-items:center;justify-content:center;
  background:rgba(5,5,5,.78);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);opacity:0;transition:opacity .3s}
#tf-draws.open{display:flex;opacity:1}
.tf-draws-panel{width:min(92vw,540px);max-height:86vh;overflow-y:auto;background:#0a0907;border:1px solid rgba(241,237,228,.08);border-radius:1.2rem;padding:2rem}
.tf-draws-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.6rem;padding-bottom:1rem;border-bottom:1px solid rgba(241,237,228,.08)}
.tf-draws-head h2{font-family:'Cormorant Garamond',serif;font-weight:300;font-size:2rem;line-height:1;text-transform:uppercase;margin:0}
.tf-draws-head h2 em{font-style:italic;color:var(--accent,#c9a227);font-weight:400}
.tf-draws-close{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;border:1px solid rgba(241,237,228,.08);background:none;color:var(--muted,#8a8378);cursor:pointer;transition:.3s}
.tf-draws-close:hover{color:var(--fg,#f1ede4);border-color:var(--accent,#c9a227)}
.tf-draws-close svg{width:16px;height:16px}
.tf-draws-list{display:grid;gap:.7rem}
.tf-draw-item{display:flex;align-items:center;gap:1.1rem;padding:1.2rem 1.3rem;border:1px solid rgba(241,237,228,.08);border-radius:.9rem;cursor:pointer;transition:.3s;background:rgba(241,237,228,.02)}
.tf-draw-item:hover{border-color:var(--accent,#c9a227);background:rgba(241,237,228,.04);transform:translateX(4px)}
.tf-draw-item .icon{flex:0 0 auto;width:42px;height:42px;border-radius:50%;display:grid;place-items:center;border:1px solid rgba(241,237,228,.08);color:var(--accent,#c9a227)}
.tf-draw-item .icon svg{width:20px;height:20px}
.tf-draw-item .copy{flex:1;min-width:0}
.tf-draw-item .copy b{display:block;font-family:'Cormorant Garamond',serif;font-size:1.25rem;font-weight:400;line-height:1.1;color:var(--fg,#f1ede4);margin-bottom:.2rem}
.tf-draw-item .copy span{display:block;font-size:.82rem;color:var(--muted,#8a8378);line-height:1.4}
.tf-draw-item .arrow{flex:0 0 auto;color:var(--muted,#8a8378);transition:.3s}
.tf-draw-item:hover .arrow{color:var(--accent,#c9a227);transform:translateX(3px)}
.tf-draw-item .badge{flex:0 0 auto;font-family:'DM Mono',monospace;font-size:.55rem;letter-spacing:.16em;text-transform:uppercase;color:var(--accent,#c9a227);padding:.3rem .55rem;border:1px solid var(--accent,#c9a227);border-radius:50px}

#tf-reveal{position:fixed;inset:0;z-index:8600;display:none;align-items:center;justify-content:center;
  background:rgba(5,5,5,.94);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);opacity:0;transition:opacity .4s}
#tf-reveal.open{display:flex;opacity:1}
.tf-reveal-stage{display:flex;flex-direction:column;align-items:center;gap:1.6rem;padding:2rem}
.tf-reveal-label{font-family:'DM Mono',monospace;font-size:.68rem;letter-spacing:.28em;text-transform:uppercase;color:var(--accent,#c9a227);text-align:center}
.tf-reveal-card{width:min(62vw,300px);aspect-ratio:2/3;perspective:1200px;cursor:pointer}
.tf-reveal-inner{position:relative;width:100%;height:100%;transition:transform 1s cubic-bezier(.6,0,.3,1);transform-style:preserve-3d}
.tf-reveal-inner.flipped{transform:rotateY(180deg)}
.tf-reveal-face{position:absolute;inset:0;backface-visibility:hidden;-webkit-backface-visibility:hidden;border-radius:.8rem;overflow:hidden;display:flex;align-items:center;justify-content:center}
.tf-reveal-back{background:linear-gradient(135deg,#1a1612,#0a0907);border:1px solid rgba(241,237,228,.08);display:grid;place-items:center;color:var(--accent,#c9a227)}
.tf-reveal-back svg{width:50%;height:50%;opacity:.6}
.tf-reveal-front{transform:rotateY(180deg);background:#fff;padding:.5rem;box-shadow:0 24px 60px rgba(0,0,0,.5)}
.tf-reveal-front img{width:100%;height:100%;object-fit:contain}
.tf-reveal-hint{font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--muted,#8a8378);text-align:center}

#tf-scanner{position:fixed;inset:0;z-index:8500;display:none;align-items:center;justify-content:center;background:#050505}
#tf-scanner.open{display:flex}
.tf-scan-video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.tf-scan-shade{position:absolute;inset:0;background:rgba(0,0,0,.52);pointer-events:none}
.tf-scan-frame{position:relative;z-index:1;width:min(68vw,330px);aspect-ratio:2/3;border:2px solid var(--accent,#c9a227);border-radius:1rem;box-shadow:0 0 0 100vmax rgba(0,0,0,.42);pointer-events:none;transition:border-color .3s}
.tf-scan-frame.scanning{animation:tf-scan-pulse 1.8s ease-in-out infinite}
@keyframes tf-scan-pulse{0%,100%{border-color:var(--accent,#c9a227);box-shadow:0 0 0 100vmax rgba(0,0,0,.42)}50%{border-color:rgba(201,162,39,.35);box-shadow:0 0 0 100vmax rgba(0,0,0,.52)}}
.tf-scan-frame.found{border-color:#3ecf6e;box-shadow:0 0 0 100vmax rgba(0,0,0,.42),0 0 30px rgba(62,207,110,.35)}
.tf-scan-frame::before,.tf-scan-frame::after{content:'';position:absolute;width:26px;height:26px;border-color:var(--fg,#f1ede4)}
.tf-scan-frame::before{top:-2px;left:-2px;border-top:3px solid;border-left:3px solid;border-radius:.8rem 0 0 0}
.tf-scan-frame::after{right:-2px;bottom:-2px;border-right:3px solid;border-bottom:3px solid;border-radius:0 0 .8rem 0}
.tf-scan-ui{position:absolute;z-index:2;inset:0;display:flex;flex-direction:column;justify-content:space-between;padding:max(1.3rem,env(safe-area-inset-top)) max(1.3rem,env(safe-area-inset-right)) max(1.3rem,env(safe-area-inset-bottom)) max(1.3rem,env(safe-area-inset-left));pointer-events:none}
.tf-scan-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}
.tf-scan-copy{max-width:18rem;font-family:'DM Mono',monospace;font-size:.68rem;line-height:1.6;letter-spacing:.12em;text-transform:uppercase;color:var(--fg,#f1ede4);text-shadow:0 1px 10px #000}
.tf-scan-status{margin-top:.45rem;color:var(--accent,#c9a227)}
.tf-scan-status .tf-scan-card-name{display:block;font-family:'Cormorant Garamond',serif;font-size:1.3rem;letter-spacing:0;text-transform:none;color:var(--fg,#f1ede4);margin-top:.3rem}
.tf-scan-status .tf-scan-score{display:inline-block;font-family:'DM Mono',monospace;font-size:.6rem;letter-spacing:.14em;background:rgba(201,162,39,.18);padding:.15rem .45rem;border-radius:50px;margin-top:.25rem}
.tf-scan-close{pointer-events:auto;width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:rgba(5,5,5,.75);border:1px solid rgba(241,237,228,.08);color:var(--fg,#f1ede4);cursor:pointer}
.tf-scan-close svg{width:18px;height:18px}
.tf-scan-error{display:none;align-self:center;max-width:22rem;padding:1rem 1.2rem;background:#15130f;border:1px solid var(--accent,#c9a227);border-radius:.8rem;color:var(--fg,#f1ede4);font-size:.9rem;line-height:1.5;text-align:center;pointer-events:auto}
.tf-scan-error.visible{display:block}
/* voice reader button */
.tf-voice-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1rem;border-radius:50px;
  background:rgba(201,162,39,.12);border:1px solid rgba(201,162,39,.25);color:var(--accent,#c9a227);
  font-family:'DM Mono',monospace;font-size:.66rem;letter-spacing:.14em;text-transform:uppercase;
  cursor:pointer;transition:all .25s ease;margin-top:1rem}
.tf-voice-btn:hover{background:rgba(201,162,39,.22);border-color:var(--accent,#c9a227)}
.tf-voice-btn.speaking{background:rgba(201,162,39,.22);border-color:var(--accent,#c9a227);animation:tf-voice-pulse 1.4s ease-in-out infinite}
@keyframes tf-voice-pulse{0%,100%{opacity:1}50%{opacity:.55}}
.tf-voice-btn svg{width:16px;height:16px;flex:0 0 auto}

@media (max-width:900px){
  .tf-scan-btn{right:max(1rem,env(safe-area-inset-right));bottom:max(1rem,env(safe-area-inset-bottom));width:50px;height:50px}
  .tf-draws-btn{left:max(1rem,env(safe-area-inset-left));bottom:max(4.2rem,calc(env(safe-area-inset-bottom) + 3rem));padding:.7rem 1rem;font-size:.6rem}
  .tf-draws-btn svg{width:14px;height:14px}
}

/* === Miroir des Lames (associations) === */
.tf-mirror-trigger{
  display:inline-flex;align-items:center;gap:.55rem;padding:.55rem .95rem;border-radius:50px;
  border:1px solid var(--accent,#c9a227);background:rgba(201,162,39,.06);color:var(--accent,#c9a227);
  font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.22em;text-transform:uppercase;
  cursor:pointer;transition:.25s ease-out;margin:0 0 1rem
}
.tf-mirror-trigger .glyph{font-size:.85rem;letter-spacing:0}
.tf-mirror-trigger:hover{background:rgba(201,162,39,.16);transform:translateY(-1px)}

#tf-mirror{
  position:fixed;inset:0;z-index:8800;display:none;flex-direction:column;
  background:radial-gradient(ellipse at center,rgba(20,18,15,.96),rgba(5,5,5,.99));
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  opacity:0;transition:opacity .5s ease;--ac:var(--accent,#c9a227)
}
#tf-mirror.open{display:flex;opacity:1;animation:tf-mirror-in .5s ease-out}
@keyframes tf-mirror-in{from{opacity:0}to{opacity:1}}

.tf-mirror-top{
  display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;
  padding:1.4rem max(1.6rem,env(safe-area-inset-top)) 1rem max(1.6rem,env(safe-area-inset-left))
}
.tf-mirror-close{
  width:42px;height:42px;border-radius:50%;display:grid;place-items:center;
  border:1px solid rgba(241,237,228,.08);background:none;color:var(--muted,#8a8378);cursor:pointer;transition:.3s;flex:0 0 auto
}
.tf-mirror-close:hover{color:var(--ac);border-color:var(--ac)}
.tf-mirror-close svg{width:16px;height:16px}
.tf-mirror-title-wrap{text-align:center;flex:1;min-width:160px}
.tf-mirror-kicker{font-family:'DM Mono',monospace;font-size:.56rem;letter-spacing:.28em;text-transform:uppercase;color:var(--ac);margin-bottom:.25rem}
.tf-mirror-title{font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:300;font-size:1.4rem;color:var(--fg,#f1ede4);line-height:1.1}

.tf-mirror-filters{display:flex;gap:.3rem;flex-wrap:wrap;justify-content:center;padding:0 max(1.6rem,env(safe-area-inset-left)) 1rem}
.tf-mirror-filter{
  display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .7rem;border-radius:50px;
  border:1px solid rgba(241,237,228,.08);background:transparent;color:var(--muted,#8a8378);
  font-family:'DM Mono',monospace;font-size:.55rem;letter-spacing:.18em;text-transform:uppercase;cursor:pointer;transition:.3s
}
.tf-mirror-filter:hover{color:var(--fg,#f1ede4);border-color:rgba(241,237,228,.2)}
.tf-mirror-filter.active{border-color:var(--ac);color:var(--ac);background:rgba(201,162,39,.06)}
.tf-mirror-filter .glyph{font-size:.8rem;letter-spacing:0}

.tf-mirror-stage{
  flex:1;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;justify-items:center;
  gap:2.5rem;padding:1rem max(2rem,env(safe-area-inset-left)) 3rem;min-height:0
}
.tf-mirror-card{
  width:min(26vw,220px);aspect-ratio:2/3;border-radius:.7rem;overflow:hidden;display:grid;place-items:center;
  background:rgba(241,237,228,.04);border:1px solid rgba(241,237,228,.08);
  transition:opacity .3s ease,transform .3s ease;position:relative
}
.tf-mirror-card.left{justify-self:end}
.tf-mirror-card.right{justify-self:start}
.tf-mirror-card img{width:100%;height:100%;object-fit:cover;display:block}
.tf-mirror-card.swapping{opacity:0;transform:translateY(10px)}
.tf-mirror-card.empty{background:linear-gradient(135deg,#1a1612,#0a0907)}
.tf-mirror-card-back{color:var(--ac);font-size:2rem;opacity:.5}
.tf-mirror-card-tag{
  position:absolute;bottom:-1.5rem;left:50%;transform:translateX(-50%);
  font-family:'DM Mono',monospace;font-size:.54rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted,#8a8378);white-space:nowrap
}

.tf-mirror-divider{display:flex;flex-direction:column;align-items:center;gap:.7rem;color:var(--ac)}
.tf-mirror-divider .line{width:1px;height:28px;background:linear-gradient(180deg,transparent,var(--ac),transparent)}
.tf-mirror-divider .glyph{font-size:1.1rem}

.tf-mirror-text-wrap{text-align:center;padding:1rem max(2rem,env(safe-area-inset-left)) 1.5rem;max-width:720px;margin:0 auto}
.tf-mirror-text{
  font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:300;
  font-size:clamp(1.05rem,2vw,1.35rem);line-height:1.55;color:var(--fg,#f1ede4);transition:opacity .3s ease
}
.tf-mirror-text.swapping{opacity:0}
.tf-mirror-text::before{content:'';display:block;width:30px;height:1px;background:var(--ac);margin:0 auto 1rem;opacity:.6}

.tf-mirror-nav{
  display:flex;justify-content:center;align-items:center;gap:1.5rem;
  padding:1rem max(1.6rem,env(safe-area-inset-bottom)) max(1.6rem,env(safe-area-inset-bottom))
}
.tf-mirror-nav button{
  width:46px;height:46px;border-radius:50%;border:1px solid rgba(241,237,228,.1);
  background:rgba(10,9,7,.5);color:var(--fg,#f1ede4);display:grid;place-items:center;cursor:pointer;transition:.3s
}
.tf-mirror-nav button:hover{border-color:var(--ac);color:var(--ac);transform:scale(1.05)}
.tf-mirror-nav button svg{width:18px;height:18px}
.tf-mirror-counter{font-family:'DM Mono',monospace;font-size:.62rem;letter-spacing:.22em;color:var(--muted,#8a8378);min-width:80px;text-align:center}
.tf-mirror-counter b{color:var(--ac);font-weight:400}

@media (max-width:720px){
  .tf-mirror-top{padding:1rem max(1rem,env(safe-area-inset-top)) .8rem max(1rem,env(safe-area-inset-left))}
  .tf-mirror-title{font-size:1.05rem}
  .tf-mirror-stage{grid-template-columns:1fr 1fr;gap:.8rem;padding:.5rem 1rem 2.4rem}
  .tf-mirror-card{width:min(42vw,170px)}
  .tf-mirror-card-tag{bottom:-1.2rem;font-size:.5rem}
  .tf-mirror-divider{display:none}
  .tf-mirror-filter{padding:.3rem .5rem;font-size:.5rem;gap:.25rem}
  .tf-mirror-text-wrap{padding:.5rem 1.2rem 1rem}
  .tf-mirror-nav{gap:1rem;padding-bottom:max(1.2rem,env(safe-area-inset-bottom))}
  .tf-mirror-trigger{padding:.55rem .7rem}
}

/* Grille complète : toutes les associations restent dans une seule lecture. */
#tf-mirror{overflow:hidden;background:rgba(5,5,5,.97)}
.tf-mirror-top{flex:0 0 auto;border-bottom:1px solid rgba(241,237,228,.08)}
.tf-association-summary{flex:0 0 auto;padding:.85rem max(1rem,env(safe-area-inset-left));text-align:center;color:var(--muted,#8a8378);font-family:'DM Mono',monospace;font-size:.58rem;letter-spacing:.16em;text-transform:uppercase}
.tf-mirror-grid{flex:1;overflow-y:auto;overscroll-behavior:contain;padding:0 max(1.4rem,env(safe-area-inset-left)) 3rem;scroll-behavior:smooth}
.tf-assoc-section{max-width:1100px;margin:0 auto;padding:1.8rem 0;border-bottom:1px solid rgba(241,237,228,.08)}
.tf-assoc-section:last-child{border-bottom:0}
.tf-assoc-section h2{margin:0 0 1rem;color:var(--ac);font-family:'Cormorant Garamond',serif;font-size:clamp(1.45rem,2.4vw,2rem);font-weight:300;letter-spacing:.03em}
.tf-assoc-list{display:grid;gap:.7rem}
.tf-assoc-item{display:grid;grid-template-columns:148px minmax(0,1fr);gap:1rem;align-items:center;padding:.8rem;border:1px solid rgba(241,237,228,.08);background:rgba(241,237,228,.025);transition:border-color .18s ease,transform .18s ease}
.tf-assoc-item:hover,.tf-assoc-item:focus-within{border-color:var(--ac);transform:translateY(-2px)}
.tf-assoc-duo{display:flex;align-items:center;gap:.4rem}
.tf-assoc-duo img{width:70px;height:105px;object-fit:cover;border-radius:.25rem;background:#12100d}
.tf-assoc-pair{display:flex;flex-wrap:wrap;gap:.25rem .4rem;align-items:baseline;margin-bottom:.4rem;font-family:'DM Mono',monospace;font-size:.58rem;line-height:1.4;letter-spacing:.06em;text-transform:uppercase;color:var(--muted,#8a8378)}
.tf-assoc-pair strong{color:var(--fg,#f1ede4);font-weight:500}
.tf-assoc-item p{margin:0;color:rgba(241,237,228,.78);font-family:'Cormorant Garamond',serif;font-size:1.05rem;line-height:1.38}
@media (max-width:720px){
  .tf-mirror-grid{padding:0 1rem 2rem}
  .tf-association-summary{font-size:.52rem;letter-spacing:.1em}
  .tf-assoc-section{padding:1.35rem 0}
  .tf-assoc-item{grid-template-columns:96px minmax(0,1fr);gap:.7rem;padding:.7rem;align-items:start}
  .tf-assoc-duo{gap:.25rem}
  .tf-assoc-duo img{width:46px;height:69px}
  .tf-assoc-item p{font-size:1rem}
}
@media (prefers-reduced-motion:reduce){
  #tf-mirror,.tf-assoc-item{transition:none}
  .tf-mirror-grid{scroll-behavior:auto}
}
`;
    const style = document.createElement('style');
    style.id = 'tf-styles';
    style.textContent = css;
    document.head.appendChild(style);
  }

  function injectHTML(){
    if(document.getElementById('tf-draws')) return;

    document.body.insertAdjacentHTML('beforeend', `
<button class="tf-scan-btn" id="tf-scan-btn" aria-label="Scanner une lame" title="Scanner une lame">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3"/><path d="M8 12h8"/></svg>
</button>
<button class="tf-draws-btn" id="tf-draws-btn" aria-label="Tirages" title="Tirages">
  <span class="dot"></span>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="9" width="18" height="6" rx="1"/><path d="M3 12h18"/></svg>
  <span>Tirages</span>
</button>

<div id="tf-draws" role="dialog" aria-modal="true" aria-label="Modes de tirage">
  <div class="tf-draws-panel">
    <div class="tf-draws-head">
      <h2><em>Tirages</em></h2>
      <button class="tf-draws-close" id="tf-draws-close" aria-label="Fermer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
    </div>
    <div class="tf-draws-list">
      <div class="tf-draw-item" id="tf-draw-today" role="button" tabindex="0">
        <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/><circle cx="12" cy="15" r="2" fill="currentColor"/></svg></div>
        <div class="copy">
          <b>Carte du jour</b>
          <span>Une lame pour vous guider aujourd'hui. À découvrir chaque matin.</span>
        </div>
        <span class="arrow"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
      </div>
    </div>
  </div>
</div>

<div id="tf-reveal" role="dialog" aria-modal="true" aria-label="Révélation de la carte">
  <div class="tf-reveal-stage">
    <div class="tf-reveal-label" id="tf-reveal-label">Carte du jour</div>
    <div class="tf-reveal-card" id="tf-reveal-card">
      <div class="tf-reveal-inner" id="tf-reveal-inner">
        <div class="tf-reveal-face tf-reveal-back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="12" cy="12" r="10"/><path d="M12 2v20M2 12h20M5 5l14 14M19 5L5 19"/></svg>
        </div>
        <div class="tf-reveal-face tf-reveal-front">
          <img id="tf-reveal-img" src="" alt="">
        </div>
      </div>
    </div>
    <div class="tf-reveal-hint" id="tf-reveal-hint">Touchez la carte pour la révéler</div>
  </div>
</div>

<div id="tf-scanner" role="dialog" aria-modal="true" aria-label="Scanner une lame">
  <video class="tf-scan-video" id="tf-scan-video" playsinline muted></video>
  <div class="tf-scan-shade"></div>
  <div class="tf-scan-frame" id="tf-scan-frame"></div>
  <div class="tf-scan-ui">
    <div class="tf-scan-top">
      <div class="tf-scan-copy">Cadrez la lame dans le repère<div class="tf-scan-status" id="tf-scan-status" role="status">Préparation du scanner...</div></div>
      <button class="tf-scan-close" id="tf-scan-close" aria-label="Fermer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
    </div>
    <p class="tf-scan-error" id="tf-scan-error" role="alert"></p>
  </div>
</div>

<div id="tf-mirror" role="dialog" aria-modal="true" aria-label="Miroir des Lames — associations">
  <div class="tf-mirror-top">
    <button class="tf-mirror-close" id="tf-mirror-close" aria-label="Fermer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
    <div class="tf-mirror-title-wrap">
      <div class="tf-mirror-kicker">Grille des associations</div>
      <div class="tf-mirror-title" id="tf-mirror-title"></div>
    </div>
    <div style="width:42px"></div>
  </div>
  <div class="tf-association-summary" id="tf-association-summary"></div>
  <div class="tf-mirror-grid" id="tf-mirror-grid"></div>
</div>
`);
  }

  /* =========================================================
     Draw logic
     ========================================================= */
  function strSeed(str){
    let h = 1779033703 ^ str.length;
    for(let i=0;i<str.length;i++){
      h = Math.imul(h ^ str.charCodeAt(i), 3432918353);
      h = (h << 13) | (h >>> 19);
    }
    return () => {
      h = Math.imul(h ^ (h >>> 16), 2246822507);
      h = Math.imul(h ^ (h >>> 13), 3266489909);
      return ((h ^= h >>> 16) >>> 0) / 4294967296;
    };
  }

  function cardOfTheDay(){
    const today = localDateKey();
    const storageKey = 'tarot_cjd_'+today;
    const saved = localStorage.getItem(storageKey);
    const existing = saved && ALL_CARDS.find(card => card.id === saved);
    if(existing) return existing;
    const random = new Uint32Array(1);
    crypto.getRandomValues(random);
    const card = ALL_CARDS[random[0] % ALL_CARDS.length];
    localStorage.setItem(storageKey, card.id);
    return card;
  }

  function localDateKey(){
    const now = new Date();
    return [now.getFullYear(),String(now.getMonth()+1).padStart(2,'0'),String(now.getDate()).padStart(2,'0')].join('-');
  }

  const draws = { open:false };

  function openDraws(){
    draws.open = true;
    const d = document.getElementById('tf-draws');
    if(!d) return;
    d.classList.add('open');
    const today = localDateKey();
    const drawn = localStorage.getItem('tarot_cjd_'+today);
    const item = document.getElementById('tf-draw-today');
    if(!item) return;
    let badge = item.querySelector('.badge');
    if(drawn){
      if(!badge){ badge=document.createElement('span'); badge.className='badge'; item.appendChild(badge); }
      badge.textContent = 'Tirée';
    } else if(badge){ badge.remove(); }
  }
  function closeDraws(){ draws.open=false; const d=document.getElementById('tf-draws'); if(d) d.classList.remove('open'); }

  function revealCard(card, label){
    const reveal = document.getElementById('tf-reveal');
    if(!reveal) return;
    document.getElementById('tf-reveal-label').textContent = label || 'Votre lame';
    const img = document.getElementById('tf-reveal-img');
    img.src = card.file; img.alt = card.name;
    const inner = document.getElementById('tf-reveal-inner');
    inner.classList.remove('flipped');
    document.getElementById('tf-reveal-hint').style.display = 'block';
    reveal.classList.add('open');
    reveal._cardId = card.id;
  }

  function flipReveal(){
    const inner = document.getElementById('tf-reveal-inner');
    if(inner.classList.contains('flipped')) return;
    inner.classList.add('flipped');
    document.getElementById('tf-reveal-hint').style.display = 'none';
    const id = document.getElementById('tf-reveal')._cardId;
    setTimeout(()=>{
      const reveal = document.getElementById('tf-reveal');
      if(reveal) reveal.classList.remove('open');
      if(id){
        const card = ALL_CARDS.find(c=>c.id===id);
        if(card){
          const href = 'index_full.html#suite='+encodeURIComponent(card.family)+'&lame='+encodeURIComponent(id);
          const isFull = location.pathname.endsWith('index_full.html');
          if(isFull && typeof openCard==='function'){
            openCard(id);
          } else {
            location.href = href;
          }
        }
      }
    }, 1600);
  }

  function closeReveal(){ const r=document.getElementById('tf-reveal'); if(r) r.classList.remove('open'); }

  /* =========================================================
     Scan logic
     ========================================================= */
  const scanner = { stream:null, timer:null, refs:null, lastId:null, streak:0, trigger:null, active:false, canvas:null, ctx:null };
  const SCAN_WIDTH = 32, SCAN_HEIGHT = 48, SCAN_THRESHOLD = .72, SCAN_INTERVAL = 120;

  function scanVector(source, sx, sy, sw, sh, reverse){
    if(!scanner.canvas){
      scanner.canvas=document.createElement('canvas'); scanner.canvas.width=SCAN_WIDTH; scanner.canvas.height=SCAN_HEIGHT;
      scanner.ctx=scanner.canvas.getContext('2d',{willReadFrequently:true});
    }
    const ctx=scanner.ctx;
    ctx.setTransform(1,0,0,1,0,0); ctx.clearRect(0,0,SCAN_WIDTH,SCAN_HEIGHT);
    if(reverse){ctx.translate(SCAN_WIDTH,SCAN_HEIGHT);ctx.rotate(Math.PI);}
    ctx.drawImage(source,sx,sy,sw,sh,0,0,SCAN_WIDTH,SCAN_HEIGHT);
    const data=ctx.getImageData(0,0,SCAN_WIDTH,SCAN_HEIGHT).data;
    const values=new Float32Array(SCAN_WIDTH*SCAN_HEIGHT);
    let mean=0;
    for(let i=0;i<values.length;i++){const p=i*4;values[i]=data[p]*.299+data[p+1]*.587+data[p+2]*.114;mean+=values[i];}
    mean/=values.length;
    let norm=0;
    for(let i=0;i<values.length;i++){values[i]-=mean;norm+=values[i]*values[i];}
    norm=Math.sqrt(norm)||1;
    for(let i=0;i<values.length;i++)values[i]/=norm;
    return values;
  }
  function similarity(a,b){let s=0;for(let i=0;i<a.length;i++)s+=a[i]*b[i];return s;}

  function normalizeVector(values){
    let mean=0;
    for(let i=0;i<values.length;i++)mean+=values[i];
    mean/=values.length;
    let norm=0;
    for(let i=0;i<values.length;i++){values[i]-=mean;norm+=values[i]*values[i];}
    norm=Math.sqrt(norm)||1;
    for(let i=0;i<values.length;i++)values[i]/=norm;
    return values;
  }

  function decodeScanReference(encoded){
    const binary=atob(encoded);
    const vector=new Float32Array(binary.length);
    for(let i=0;i<binary.length;i++)vector[i]=binary.charCodeAt(i);
    return normalizeVector(vector);
  }

  function reverseVector(vector){
    const reversed=new Float32Array(vector.length);
    for(let y=0;y<SCAN_HEIGHT;y++)for(let x=0;x<SCAN_WIDTH;x++)reversed[(SCAN_HEIGHT-1-y)*SCAN_WIDTH+(SCAN_WIDTH-1-x)]=vector[y*SCAN_WIDTH+x];
    return reversed;
  }

  function prepareScanReferences(){
    if(scanner.refs) return scanner.refs;
    const refs=[];
    ALL_CARDS.forEach(card=>{
      if(!card.scan) return;
      const vector=decodeScanReference(card.scan);
      refs.push({id:card.id,vector});
      refs.push({id:card.id,vector:reverseVector(vector)});
    });
    scanner.refs=refs;
    return refs;
  }

  function scanCrop(video, frame){
    const videoBox=video.getBoundingClientRect();
    const frameBox=frame.getBoundingClientRect();
    const scale=Math.max(videoBox.width/video.videoWidth,videoBox.height/video.videoHeight);
    const renderedWidth=video.videoWidth*scale;
    const renderedHeight=video.videoHeight*scale;
    const renderedLeft=videoBox.left+(videoBox.width-renderedWidth)/2;
    const renderedTop=videoBox.top+(videoBox.height-renderedHeight)/2;
    return {
      sx:Math.max(0,(frameBox.left-renderedLeft)/scale),
      sy:Math.max(0,(frameBox.top-renderedTop)/scale),
      sw:Math.min(video.videoWidth,frameBox.width/scale),
      sh:Math.min(video.videoHeight,frameBox.height/scale),
    };
  }

  function scanFrame(){
    const video=document.getElementById('tf-scan-video');
    if(!scanner.active||video.readyState<2||!scanner.refs||!scanner.refs.length)return;
    const crop=scanCrop(video,document.getElementById('tf-scan-frame'));
    const vector=scanVector(video,crop.sx,crop.sy,crop.sw,crop.sh,false);
    let best=null;
    scanner.refs.forEach(ref=>{const score=similarity(vector,ref.vector);if(!best||score>best.score)best={...ref,score};});
    if(!best)return;
    const status=document.getElementById('tf-scan-status');
    const frame=document.getElementById('tf-scan-frame');
    const card = best.score>=SCAN_THRESHOLD ? ALL_CARDS.find(c=>c.id===best.id) : null;
    const pct = Math.round(best.score*100);
    if(status){
      if(best.score>=SCAN_THRESHOLD){
        status.innerHTML = 'Carte détectée, confirmation...<span class="tf-scan-card-name">'+(card?card.name:'')+'</span><span class="tf-scan-score">'+pct+'% · '+scanner.streak+'/2</span>';
      } else {
        status.innerHTML = 'Recherche de la lame...';
      }
    }
    if(frame){
      frame.classList.toggle('scanning', best.score<SCAN_THRESHOLD);
      frame.classList.toggle('found', best.score>=SCAN_THRESHOLD);
    }
    if(best.score>=SCAN_THRESHOLD&&best.id===scanner.lastId)scanner.streak++;
    else{scanner.lastId=best.id;scanner.streak=best.score>=SCAN_THRESHOLD?1:0;}
    if(scanner.streak>=2){closeScanner(); if(typeof openCard==='function') openCard(best.id);}
  }

  async function openScanner(){
    if(!navigator.mediaDevices||!navigator.mediaDevices.getUserMedia){
      showScanError('La caméra n\'est pas disponible dans ce navigateur.');return;
    }
    scanner.trigger=document.activeElement; scanner.active=true;
    const el=document.getElementById('tf-scanner');
    if(!el) return;
    el.classList.add('open');
    const closeBtn=document.getElementById('tf-scan-close'); if(closeBtn) closeBtn.focus();
    const err=document.getElementById('tf-scan-error'); if(err) err.classList.remove('visible');
    const frame=document.getElementById('tf-scan-frame'); if(frame){frame.classList.remove('found');frame.classList.add('scanning');}
    const status=document.getElementById('tf-scan-status'); if(status) status.textContent='Ouverture de la caméra...';
    try{
      const refs=prepareScanReferences();
      const stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'},width:{ideal:1280},height:{ideal:1920}},audio:false});
      if(!scanner.active){stream.getTracks().forEach(t=>t.stop());return;}
      scanner.refs=refs; scanner.stream=stream;
      const video=document.getElementById('tf-scan-video'); video.srcObject=stream; await video.play();
      const st=document.getElementById('tf-scan-status'); if(st) st.textContent='Cadrez la lame dans le repère';
      scanner.timer=window.setInterval(scanFrame,SCAN_INTERVAL);
    }catch(error){
      scanner.active=false;
      showScanError(error.name==='NotAllowedError'?'L\'accès à la caméra a été refusé. Autorisez-le puis réessayez.':'Impossible d\'ouvrir la caméra. Utilisez le site en HTTPS.');
    }
  }

  function showScanError(message){
    const err=document.getElementById('tf-scan-error');
    if(!err) return;
    err.textContent=message; err.classList.add('visible');
    const el=document.getElementById('tf-scanner');
    if(el && !el.classList.contains('open')) el.classList.add('open');
    const st=document.getElementById('tf-scan-status'); if(st) st.textContent='Scanner indisponible';
  }

  function closeScanner(){
    scanner.active=false;
    if(scanner.timer){window.clearInterval(scanner.timer);scanner.timer=null;}
    scanner.lastId=null; scanner.streak=0;
    if(scanner.stream){scanner.stream.getTracks().forEach(t=>t.stop());scanner.stream=null;}
    const video=document.getElementById('tf-scan-video'); if(video) video.srcObject=null;
    const el=document.getElementById('tf-scanner'); if(el) el.classList.remove('open');
    const frame=document.getElementById('tf-scan-frame'); if(frame){frame.classList.remove('scanning','found');}
    if(typeof window.stopVoice==='function') window.stopVoice();
    if(scanner.trigger){scanner.trigger.focus();scanner.trigger=null;}
  }

  /* =========================================================
     Voice reader — Web Speech API
     ========================================================= */
  const voice = { utterance:null, speaking:false };

  function extractCardText(card){
    if(!card||!card.md) return '';
    const md = card.md;
    // Extract interpretation sections (theme sections after Interprétation)
     const interp = mdSectionLocal(md,'Interprétation') || '';
     const amour = mdSectionLocal(md,/(Amour|Love)/i) || '';
     const travail = mdSectionLocal(md,/Travail/i) || '';
     const finances = mdSectionLocal(md,/Finance/i) || '';
     const guidance = mdSectionLocal(md,/Guidance/i) || '';
     const affirmation = mdSectionLocal(md,/Affirmation/i) || '';
     const description = mdSectionLocal(md,'Description') || '';
    // Build a clean reading text
    const parts = [];
    parts.push(card.name + '.');
    if(interp) parts.push('Interprétation. ' + interp);
    if(amour) parts.push('Amour. ' + amour);
    if(travail) parts.push('Travail. ' + travail);
    if(finances) parts.push('Finances. ' + finances);
    if(guidance) parts.push('Guidance. ' + guidance);
    if(affirmation) parts.push('Affirmation. ' + affirmation);
    if(description) parts.push('Description. ' + description);
    return parts.join(' ');
  }

  function mdSectionLocal(md, pattern){
    const re = typeof pattern==='string'
      ? new RegExp('^## '+pattern.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+'\\s*\\n([\\s\\S]*?)(?=^## |(?![\\s\\S]))','mi')
      : new RegExp('^## '+pattern.source+'\\s*\\n([\\s\\S]*?)(?=^## |(?![\\s\\S]))','mi');
    const match = md.match(re);
    return match ? match[1].replace(/^\*\*[^\n]+\*\*\s*$/gm,'').replace(/\n+/g,' ').replace(/\*|`/g,'').trim() : '';
  }

  function startVoice(card){
    if(!('speechSynthesis' in window)) return;
    stopVoice();
    const text = extractCardText(card);
    if(!text) return;
    const utt = new SpeechSynthesisUtterance(text);
    utt.lang = 'fr-FR';
    utt.rate = 0.92;
    // Try to find a French voice
    const voices = speechSynthesis.getVoices();
    const fr = voices.find(v=>v.lang.startsWith('fr'));
    if(fr) utt.voice = fr;
    utt.onend = ()=>{ voice.speaking=false; updateVoiceBtn(); };
    utt.onerror = ()=>{ voice.speaking=false; updateVoiceBtn(); };
    voice.utterance = utt;
    voice.speaking = true;
    speechSynthesis.speak(utt);
    updateVoiceBtn();
  }

  function stopVoice(){
    if('speechSynthesis' in window) speechSynthesis.cancel();
    voice.utterance = null;
    voice.speaking = false;
    updateVoiceBtn();
  }

  function toggleVoice(card){
    if(voice.speaking) stopVoice();
    else startVoice(card);
  }

  function updateVoiceBtn(){
    document.querySelectorAll('.tf-voice-btn').forEach(btn=>{
      btn.classList.toggle('speaking', voice.speaking);
      btn.setAttribute('aria-label', voice.speaking ? 'Arrêter la lecture' : 'Écouter la carte');
    });
  }

  function injectVoiceButton(container, card){
    if(!('speechSynthesis' in window)) return;
    const btn = document.createElement('button');
    btn.className = 'tf-voice-btn';
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 5L6 9H2v6h4l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a9 9 0 0 1 0 14"/></svg><span>Écouter la carte</span>';
    btn.addEventListener('click',()=>toggleVoice(card));
    container.prepend(btn);
  }

  /* =========================================================
     Miroir des Lames — associations cinématiques
     ========================================================= */

  // Noms composés des arcanes majeurs tels qu'utilisés dans les fichiers d'associations
  // (pour résoudre la carte partenaire depuis la paire « X + Y »).
  const MAJOR_COMPOUND = {
    '00': 'Le Fou / Le Mat',
    '01': 'Le Magicien / Le Bateleur',
    '02': 'La Grande Prêtresse / La Papesse',
    '05': 'Le Hiérophante / Le Pape',
    '09': "L'Ermite / L'Hermite",
    '13': "La Mort / L'Arcane sans nom",
    '14': 'La Tempérance',
    '16': 'La Tour / La Maison Dieu',
  };

  const SUITE_FILTERS = [
    { key: 'all',     glyph: '✦',  label: 'Toutes' },
    { key: 'majors',  glyph: '✦',  label: 'Majeurs' },
    { key: 'batons',  glyph: '🜂', label: 'Bâtons' },
    { key: 'coupes',  glyph: '🜄', label: 'Coupes' },
    { key: 'epees',   glyph: '🜁', label: 'Épées' },
    { key: 'deniers', glyph: '🜃', label: 'Deniers' },
  ];

  function normalizeName(s){
    return (s||'').toLowerCase()
      .replace(/[''´`’]/g,"'")
      .replace(/[àáâäã]/g,'a').replace(/[èéêë]/g,'e').replace(/[ìíîï]/g,'i')
      .replace(/[òóôöõ]/g,'o').replace(/[ùúûü]/g,'u').replace(/[ç]/g,'c')
      .replace(/\s+/g,' ').trim();
  }

  let _nameIndex = null;
  function getNameIndex(){
    if(_nameIndex) return _nameIndex;
    _nameIndex = new Map();
    for(const f of TAROT.families){
      for(const c of f.cards){
        const numStr = c.id.split('_')[1];
        const aliases = new Set([c.name]);
        if(MAJOR_COMPOUND[numStr]){
          aliases.add(MAJOR_COMPOUND[numStr]);
          MAJOR_COMPOUND[numStr].split(' / ').forEach(p => aliases.add(p));
        }
        // Variantes sans article (La/La/Les/L') — au cas où le texte source utiliserait l'une ou l'autre forme.
        const extras = [];
        for(const a of aliases){
          const stripped = a.replace(/^(La |Le |Les |L'|L')/i,'');
          if(stripped && stripped !== a) extras.push(stripped);
        }
        extras.forEach(e => aliases.add(e));
        for(const a of aliases) _nameIndex.set(normalizeName(a), c);
      }
    }
    return _nameIndex;
  }

  function parseAssociations(md){
    if(!md) return [];
    const titleMap = {
      'arcanes majeurs':'majors',
      'suite de bâtons':'batons','suite de batons':'batons',
      'suite de coupes':'coupes',
      "suite d'épées":'epees',"suite d'epees":'epees',"suite d’épées":'epees',
      'suite de deniers':'deniers'
    };
    const sections = [];
    const headerRe = /^## (.+)$/gm;
    const headers = [];
    let m;
    while((m = headerRe.exec(md)) !== null){
      headers.push({ start:m.index, end:m.index + m[0].length, title:m[1].trim() });
    }
    for(let i=0; i<headers.length; i++){
      const h = headers[i];
      const body = md.slice(h.end, i+1 < headers.length ? headers[i+1].start : md.length);
      const key = titleMap[h.title.toLowerCase()] || 'misc';
      const items = [];
      const itemRe = /^- \*\*([^*]+?)\*\*\s*:\s*(.+)$/gm;
      let im;
      while((im = itemRe.exec(body)) !== null){
        items.push({ pair: im[1].trim().replace(/[\s:]+$/, ''), text: im[2].trim() });
      }
      if(items.length) sections.push({ key, title:h.title, items });
    }
    return sections;
  }

  function countCombos(md){
    return parseAssociations(md).reduce((total,section) => total + section.items.length,0);
  }

  function resolvePartnerCard(pair){
    // « Carte courante + Carte partenaire »
    const parts = pair.split(' + ');
    if(parts.length < 2) return null;
    const partnerName = parts[parts.length - 1].trim();
    return getNameIndex().get(normalizeName(partnerName)) || null;
  }

  const mirror = { card:null, sections:[], filter:'all', idx:0 };

  function mirrorItems(){
    if(mirror.filter === 'all') return mirror.sections.flatMap(s => s.items);
    const sec = mirror.sections.find(s => s.key === mirror.filter);
    return sec ? sec.items : [];
  }

  function renderAssociationGrid(card, sections){
    const grid = document.getElementById('tf-mirror-grid');
    const summary = document.getElementById('tf-association-summary');
    if(!grid) return;
    grid.innerHTML = '';
    let total = 0;

    sections.forEach(section => {
      const visibleItems = section.items.filter(item => {
        const partner = resolvePartnerCard(item.pair);
        return !partner || partner.id !== card.id;
      });
      if(!visibleItems.length) return;
      total += visibleItems.length;
      const sectionEl = document.createElement('section');
      sectionEl.className = 'tf-assoc-section';
      const heading = document.createElement('h2');
      heading.textContent = section.title.replace(/^Associations avec les? /i,'');
      sectionEl.appendChild(heading);
      const list = document.createElement('div');
      list.className = 'tf-assoc-list';

      visibleItems.forEach(item => {
        const partner = resolvePartnerCard(item.pair);
        const article = document.createElement('article');
        article.className = 'tf-assoc-item';
        const duo = document.createElement('div');
        duo.className = 'tf-assoc-duo';
        const currentImage = document.createElement('img');
        currentImage.alt = card.name;
        currentImage.loading = 'lazy';
        currentImage.src = card.file;
        const partnerImage = document.createElement('img');
        partnerImage.alt = partner ? partner.name : 'Carte partenaire';
        partnerImage.loading = 'lazy';
        if(partner) partnerImage.src = partner.file;
        duo.append(currentImage,partnerImage);
        article.appendChild(duo);

        const content = document.createElement('div');
        const pair = document.createElement('div');
        pair.className = 'tf-assoc-pair';
        const names = item.pair.split(' + ');
        const currentName = document.createElement('span');
        currentName.textContent = names[0] || card.name;
        const plus = document.createElement('span');
        plus.textContent = '+';
        const partnerName = document.createElement('strong');
        partnerName.textContent = names.slice(1).join(' + ') || (partner ? partner.name : 'Carte partenaire');
        pair.append(currentName,plus,partnerName);
        const text = document.createElement('p');
        text.textContent = item.text;
        content.append(pair,text);
        article.appendChild(content);
        list.appendChild(article);
      });
      sectionEl.appendChild(list);
      grid.appendChild(sectionEl);
    });
    if(summary) summary.textContent = `${total} associations · une lecture complète en cinq familles`;
  }

  function openMirror(card){
    if(!card || !card.associations) return;
    const sections = parseAssociations(card.associations);
    if(!sections.length) return;
    mirror.card = card;
    mirror.sections = sections;
    mirror.filter = 'all';
    mirror.idx = 0;

    const family = TAROT.families.find(f => f.key === card.family);
    const overlay = document.getElementById('tf-mirror');
    if(family) overlay.style.setProperty('--ac', family.accent);

    const titleEl = document.getElementById('tf-mirror-title');
    if(titleEl) titleEl.textContent = card.name;

    renderAssociationGrid(card,sections);
    overlay.classList.add('open');
    document.addEventListener('keydown', mirrorKeyHandler);
  }

  function closeMirror(){
    const overlay = document.getElementById('tf-mirror');
    if(overlay) overlay.classList.remove('open');
    document.removeEventListener('keydown', mirrorKeyHandler);
  }

  function mirrorKeyHandler(e){
    if(e.key === 'Escape'){ closeMirror(); e.preventDefault(); }
    else if(e.key === 'ArrowRight'){ navigateMirror(1); e.preventDefault(); }
    else if(e.key === 'ArrowLeft'){ navigateMirror(-1); e.preventDefault(); }
  }

  function navigateMirror(delta){
    const items = mirrorItems();
    if(!items.length) return;
    mirror.idx = (mirror.idx + delta + items.length) % items.length;
    renderMirrorCombo();
  }

  function setMirrorFilter(key){
    mirror.filter = key;
    mirror.idx = 0;
    renderMirrorFilters();
    renderMirrorCombo(true);
  }

  function renderMirrorFilters(){
    const wrap = document.getElementById('tf-mirror-filters');
    if(!wrap) return;
    wrap.innerHTML = SUITE_FILTERS.map(s =>
      `<button class="tf-mirror-filter${s.key === mirror.filter ? ' active' : ''}" data-key="${s.key}">` +
      `<span class="glyph">${s.glyph}</span><span>${s.label}</span></button>`
    ).join('');
    wrap.querySelectorAll('.tf-mirror-filter').forEach(btn => {
      btn.addEventListener('click', () => setMirrorFilter(btn.dataset.key));
    });
  }

  function renderMirrorCombo(immediate){
    const items = mirrorItems();
    const item = items[mirror.idx];
    if(!item) return;
    const partner = resolvePartnerCard(item.pair);
    const partnerEl = document.getElementById('tf-mirror-partner');
    const textEl = document.getElementById('tf-mirror-text');
    const counterEl = document.getElementById('tf-mirror-counter');

    const apply = () => {
      if(partner){
        partnerEl.innerHTML = `<img src="${partner.file}" alt="${partner.name}"><div class="tf-mirror-card-tag">${partner.name}</div>`;
        partnerEl.classList.remove('empty');
      } else {
        partnerEl.innerHTML = '<div class="tf-mirror-card-back">✦</div>';
        partnerEl.classList.add('empty');
      }
      textEl.textContent = item.text;
      counterEl.innerHTML = `<b>${String(mirror.idx + 1).padStart(2,'0')}</b> / ${String(items.length).padStart(2,'0')}`;
      partnerEl.classList.remove('swapping');
      textEl.classList.remove('swapping');
    };

    if(immediate){
      apply();
    } else {
      partnerEl.classList.add('swapping');
      textEl.classList.add('swapping');
      setTimeout(apply, 220);
    }
  }

  function injectMirrorTrigger(container, card){
    if(!card || !card.associations) return;
    const old = container.querySelector('.tf-mirror-trigger');
    if(old) old.remove();
    const n = countCombos(card.associations);
    if(!n) return;
    const btn = document.createElement('button');
    btn.className = 'tf-mirror-trigger';
    btn.innerHTML = `<span class="glyph">✦</span><span>Associations · ${n}</span>`;
    btn.setAttribute('aria-label', `Voir les ${n} associations de cette carte`);
    btn.addEventListener('click', () => openMirror(card));
    container.prepend(btn);
  }

  /* =========================================================
     Wire up
     ========================================================= */
  function wireUp(){
    document.getElementById('tf-scan-btn').addEventListener('click',openScanner);
    document.getElementById('tf-scan-close').addEventListener('click',closeScanner);
    document.getElementById('tf-draws-btn').addEventListener('click',openDraws);
    document.getElementById('tf-draws-close').addEventListener('click',closeDraws);
    const drawsOverlay=document.getElementById('tf-draws');
    drawsOverlay.addEventListener('click',e=>{ if(e.target===drawsOverlay) closeDraws(); });
    document.getElementById('tf-draw-today').addEventListener('click',()=>{
      const card=cardOfTheDay();
      const today=localDateKey();
      localStorage.setItem('tarot_cjd_'+today,card.id);
      closeDraws();
      revealCard(card,'Carte du jour · '+today.split('-').reverse().join('/'));
    });
    document.getElementById('tf-reveal-card').addEventListener('click',flipReveal);

    // Grille des associations
    document.getElementById('tf-mirror-close').addEventListener('click',closeMirror);
    const mirrorOverlay=document.getElementById('tf-mirror');
    mirrorOverlay.addEventListener('click',e=>{ if(e.target===mirrorOverlay) closeMirror(); });

    document.addEventListener('keydown',e=>{
      const drawsOpen=document.getElementById('tf-draws');
      const revealOpen=document.getElementById('tf-reveal');
      const scanOpen=document.getElementById('tf-scanner');
      const mirOpen=document.getElementById('tf-mirror');
      if(e.key==='Escape'){
        if(mirOpen&&mirOpen.classList.contains('open')){closeMirror();e.preventDefault();return;}
        if(scanner.active){closeScanner();e.preventDefault();return;}
        if(revealOpen&&revealOpen.classList.contains('open')){closeReveal();e.preventDefault();return;}
        if(drawsOpen&&drawsOpen.classList.contains('open')){closeDraws();e.preventDefault();return;}
      }
    });
  }

  /* =========================================================
     Init
     ========================================================= */
  waitForGlobals(()=>{
    injectStyles();
    injectHTML();
    wireUp();
    // expose voice reader + mirror trigger for views
    window.injectVoiceButton = injectVoiceButton;
    window.injectMirrorTrigger = injectMirrorTrigger;
    window.stopVoice = stopVoice;
    // The compact fingerprints make this warm-up instantaneous before a scan.
    const warmScanner=()=>prepareScanReferences();
    if('requestIdleCallback' in window) window.requestIdleCallback(warmScanner,{timeout:500});
    else window.setTimeout(warmScanner,0);
  });

})();
