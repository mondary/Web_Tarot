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
  position:fixed;left:max(1.4rem,env(safe-area-inset-left));bottom:max(1.4rem,env(safe-area-inset-bottom));z-index:500;
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
.tf-scan-frame{position:relative;z-index:1;width:min(68vw,330px);aspect-ratio:2/3;border:2px solid var(--accent,#c9a227);border-radius:1rem;box-shadow:0 0 0 100vmax rgba(0,0,0,.42);pointer-events:none}
.tf-scan-frame::before,.tf-scan-frame::after{content:'';position:absolute;width:26px;height:26px;border-color:var(--fg,#f1ede4)}
.tf-scan-frame::before{top:-2px;left:-2px;border-top:3px solid;border-left:3px solid;border-radius:.8rem 0 0 0}
.tf-scan-frame::after{right:-2px;bottom:-2px;border-right:3px solid;border-bottom:3px solid;border-radius:0 0 .8rem 0}
.tf-scan-ui{position:absolute;z-index:2;inset:0;display:flex;flex-direction:column;justify-content:space-between;padding:max(1.3rem,env(safe-area-inset-top)) max(1.3rem,env(safe-area-inset-right)) max(1.3rem,env(safe-area-inset-bottom)) max(1.3rem,env(safe-area-inset-left));pointer-events:none}
.tf-scan-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}
.tf-scan-copy{max-width:18rem;font-family:'DM Mono',monospace;font-size:.68rem;line-height:1.6;letter-spacing:.12em;text-transform:uppercase;color:var(--fg,#f1ede4);text-shadow:0 1px 10px #000}
.tf-scan-status{margin-top:.45rem;color:var(--accent,#c9a227)}
.tf-scan-close{pointer-events:auto;width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:rgba(5,5,5,.75);border:1px solid rgba(241,237,228,.08);color:var(--fg,#f1ede4);cursor:pointer}
.tf-scan-close svg{width:18px;height:18px}
.tf-scan-error{display:none;align-self:center;max-width:22rem;padding:1rem 1.2rem;background:#15130f;border:1px solid var(--accent,#c9a227);border-radius:.8rem;color:var(--fg,#f1ede4);font-size:.9rem;line-height:1.5;text-align:center;pointer-events:auto}
.tf-scan-error.visible{display:block}

@media (max-width:900px){
  .tf-scan-btn{right:max(1rem,env(safe-area-inset-right));bottom:max(1rem,env(safe-area-inset-bottom));width:50px;height:50px}
  .tf-draws-btn{left:max(1rem,env(safe-area-inset-left));bottom:max(1rem,env(safe-area-inset-bottom));padding:.7rem 1rem;font-size:.6rem}
  .tf-draws-btn svg{width:14px;height:14px}
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
  <div class="tf-scan-frame"></div>
  <div class="tf-scan-ui">
    <div class="tf-scan-top">
      <div class="tf-scan-copy">Cadrez la lame dans le repère<div class="tf-scan-status" id="tf-scan-status" role="status">Préparation du scanner...</div></div>
      <button class="tf-scan-close" id="tf-scan-close" aria-label="Fermer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
    </div>
    <p class="tf-scan-error" id="tf-scan-error" role="alert"></p>
  </div>
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
    const today = new Date().toISOString().slice(0,10);
    const rng = strSeed('cjd|'+today);
    return ALL_CARDS[Math.floor(rng()*ALL_CARDS.length)];
  }

  const draws = { open:false };

  function openDraws(){
    draws.open = true;
    const d = document.getElementById('tf-draws');
    if(!d) return;
    d.classList.add('open');
    const today = new Date().toISOString().slice(0,10);
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
  const scanner = { stream:null, timer:null, refs:null, lastId:null, streak:0, trigger:null, active:false };
  const SCAN_WIDTH = 48, SCAN_HEIGHT = 72, SCAN_THRESHOLD = .78;

  function scanVector(source, sx, sy, sw, sh, reverse){
    const canvas=document.createElement('canvas'); canvas.width=SCAN_WIDTH; canvas.height=SCAN_HEIGHT;
    const ctx=canvas.getContext('2d',{willReadFrequently:true});
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

  function prepareScanReferences(){
    if(scanner.refs) return Promise.resolve(scanner.refs);
    const refs=[];
    return Promise.all(ALL_CARDS.map(card=>new Promise(resolve=>{
      const image=new Image();
      image.onload=()=>{
        refs.push({id:card.id,vector:scanVector(image,0,0,image.width,image.height,false)});
        refs.push({id:card.id,vector:scanVector(image,0,0,image.width,image.height,true)});
        resolve();
      };
      image.onerror=resolve; image.src=card.file;
    }))).then(()=>{ scanner.refs=refs; return refs; });
  }

  function scanFrame(){
    const video=document.getElementById('tf-scan-video');
    if(!scanner.active||video.readyState<2||!scanner.refs||!scanner.refs.length)return;
    const cardRatio=2/3, videoRatio=video.videoWidth/video.videoHeight;
    let sw,sh,sx,sy;
    if(videoRatio>cardRatio){sh=video.videoHeight;sw=sh*cardRatio;sx=(video.videoWidth-sw)/2;sy=0;}
    else{sw=video.videoWidth;sh=sw/cardRatio;sx=0;sy=(video.videoHeight-sh)/2;}
    const vector=scanVector(video,sx,sy,sw,sh,false);
    let best=null;
    scanner.refs.forEach(ref=>{const score=similarity(vector,ref.vector);if(!best||score>best.score)best={...ref,score};});
    if(!best)return;
    const status=document.getElementById('tf-scan-status');
    if(status) status.textContent = best.score>=SCAN_THRESHOLD ? 'Carte détectée, confirmation...' : 'Recherche de la lame...';
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
    const status=document.getElementById('tf-scan-status'); if(status) status.textContent='Chargement des références...';
    try{
      const [refs,stream]=await Promise.all([
        prepareScanReferences(),
        navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'},width:{ideal:1280},height:{ideal:1920}},audio:false})
      ]);
      if(!scanner.active){stream.getTracks().forEach(t=>t.stop());return;}
      scanner.refs=refs; scanner.stream=stream;
      const video=document.getElementById('tf-scan-video'); video.srcObject=stream; await video.play();
      const st=document.getElementById('tf-scan-status'); if(st) st.textContent='Cadrez la lame dans le repère';
      scanner.timer=window.setInterval(scanFrame,450);
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
    if(scanner.trigger){scanner.trigger.focus();scanner.trigger=null;}
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
      const today=new Date().toISOString().slice(0,10);
      localStorage.setItem('tarot_cjd_'+today,card.id);
      closeDraws();
      revealCard(card,'Carte du jour · '+today.split('-').reverse().join('/'));
    });
    document.getElementById('tf-reveal-card').addEventListener('click',flipReveal);
    document.addEventListener('keydown',e=>{
      const drawsOpen=document.getElementById('tf-draws');
      const revealOpen=document.getElementById('tf-reveal');
      const scanOpen=document.getElementById('tf-scanner');
      if(e.key==='Escape'){
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
  });

})();
