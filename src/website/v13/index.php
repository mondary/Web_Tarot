<?php
declare(strict_types=1);
final class Vault {
    private static ?PDO $db = null;
    static function db(): PDO { return self::$db ??= new PDO('sqlite:' . __DIR__ . '/../v9/vault.sqlite', null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }
    static function json(string $path): ?array { $s=self::db()->prepare('SELECT data FROM vault WHERE path=?');$s->execute([$path]);$r=$s->fetch();return $r?json_decode((string)$r['data'],true):null; }
    static function image(string $path): never { $s=self::db()->prepare('SELECT mime,data FROM vault WHERE path=?');$s->execute([$path]);$r=$s->fetch();if(!$r){http_response_code(404);exit('Not found');}header('Content-Type: '.$r['mime']);header('Content-Control: public,max-age=31536000,immutable');header('Cache-Control: public,max-age=31536000,immutable');echo $r['data'];exit; }
}
function base_path(): string { if($base=$_SERVER['TAROT_LOCAL_BASE_PATH']??'')return $base;$dir=str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/'));return $dir==='/'||$dir==='.'?'':$dir; }
$base=base_path();$path=urldecode(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/');if($base!==''&&str_starts_with($path,$base))$path=substr($path,strlen($base));$path='/'.trim($path,'/');if(isset($_GET['img']))Vault::image('/img/'.urldecode((string)$_GET['img']));if($path!=='/'){http_response_code(404);exit('404');}
$data=Vault::json('/app-data.json');if(!$data){http_response_code(500);exit('Vault incomplet');}
$lames=[];foreach($data['cards'] as $c)$lames[]=['id'=>$c['id'],'n'=>$c['name'],'f'=>$c['fam'],'num'=>$c['num'],'up'=>$c['keywords_up']??'','dn'=>$c['keywords_down']??'','aff'=>trim((string)($data['es'][$c['id']]['aff']??'')),'rep'=>trim((string)($data['es'][$c['id']]['rep']??''))];
$fams=[];foreach($data['families'] as $f)$fams[$f['key']]=['n'=>$f['name'],'short'=>$f['short']??$f['name'],'sym'=>$f['sym']??'✦','line'=>$f['line']??'','ac'=>$f['ac']??'#c3a47b'];
$lj=json_encode($lames,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);$fj=json_encode($fams,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);$ver='2026.09.03';
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Soixante-Dix-Huit — Tarot v<?= $ver ?></title><meta name="description" content="L'étagère animée des 78 lames du tarot — une collection de livres rares en 3D.">
<style>
:root{--paper:#f4eee1;--ink:#241d12;--gold:#c3a47b;--cream:#e9dcc2;--soft:rgba(233,220,194,.62);--line:rgba(233,220,194,.22)}
*{box-sizing:border-box}html,body{margin:0;height:100%}
body{background:#17110a;color:var(--cream);font:400 17px/1.55 "Iowan Old Style",Georgia,"Times New Roman",serif;overflow:hidden;-webkit-font-smoothing:antialiased}
canvas{display:block;position:fixed;inset:0;cursor:grab;touch-action:none}canvas.drag{cursor:grabbing}
/* ——— HUD ——— */
header{position:fixed;z-index:10;top:0;left:0;right:0;display:flex;justify-content:space-between;align-items:baseline;padding:24px 34px;pointer-events:none}
header a,header button{pointer-events:auto}
.brand{font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:var(--cream)}
.brand em{font-style:normal;color:var(--gold)}
.nav{display:flex;gap:22px;font-size:11px;letter-spacing:.22em;text-transform:uppercase}
.nav a,.nav button{color:var(--soft);text-decoration:none;background:none;border:0;font:inherit;padding:0;border-bottom:1px solid transparent;cursor:pointer}
.nav a:hover,.nav button:hover{color:var(--cream);border-color:var(--gold)}
.hero{position:fixed;z-index:5;left:34px;top:86px;pointer-events:none;opacity:0}
.kicker{font-size:11px;letter-spacing:.32em;text-transform:uppercase;color:var(--soft);margin:0 0 10px}
.kicker b{font-weight:500;color:var(--gold)}
h1{font-weight:500;font-size:clamp(54px,10.5vw,150px);line-height:.94;letter-spacing:-.085em;margin:0;color:var(--paper)}
h1 .alt{font-style:italic;color:var(--gold)}
.lede{max-width:36ch;color:var(--soft);font-style:italic;margin:14px 0 0;font-size:15px}
footer{position:fixed;z-index:10;left:34px;right:34px;bottom:22px;display:flex;justify-content:space-between;align-items:flex-end;gap:16px;pointer-events:none}
.families{display:flex;gap:8px;flex-wrap:wrap;max-width:66vw;pointer-events:auto}
.chip{background:rgba(23,17,10,.55);backdrop-filter:blur(8px);border:1px solid var(--line);border-radius:999px;color:var(--soft);font:inherit;font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;padding:8px 14px;cursor:pointer;transition:.25s}
.chip i{font-style:normal;margin-right:6px;color:var(--gold)}
.chip:hover{border-color:var(--gold);color:var(--cream)}
.chip.on{background:var(--gold);border-color:var(--gold);color:#17110a}
.chip.on i{color:#17110a}
.meta{text-align:right;font-size:10.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--soft);white-space:nowrap}
.meta b{font-weight:500;color:var(--gold);font-variant-numeric:tabular-nums}
.arrows{display:flex;gap:8px;margin-top:10px;justify-content:flex-end}
.arrows button{width:40px;height:40px;border-radius:50%;border:1px solid var(--line);background:rgba(23,17,10,.55);backdrop-filter:blur(8px);color:var(--cream);font-size:16px;cursor:pointer;transition:.25s;pointer-events:auto}
.arrows button:hover{border-color:var(--gold);background:var(--gold);color:#17110a}
/* ——— fiche livre ——— */
#veil{position:fixed;inset:0;z-index:20;background:rgba(10,7,3,.45);opacity:0;pointer-events:none;transition:opacity .4s}
#veil.open{opacity:1;pointer-events:auto}
.page{position:fixed;z-index:21;top:0;right:0;bottom:0;width:min(470px,94vw);overflow:auto;background:var(--paper);color:var(--ink);border-left:1px solid var(--gold);box-shadow:-40px 0 80px -30px rgba(0,0,0,.6);padding:52px 38px 36px;transform:translateX(105%);transition:transform .5s cubic-bezier(.22,.8,.24,1)}
#veil.open .page{transform:none}
.page .close{position:absolute;top:16px;right:16px;width:38px;height:38px;border-radius:50%;border:1px solid rgba(36,29,18,.25);background:none;color:var(--soft);color:#8a7a5e;font-size:17px;cursor:pointer}
.page .close:hover{border-color:var(--gold);color:var(--ink)}
.eyebrow{font-size:10.5px;letter-spacing:.3em;text-transform:uppercase;color:#a08050;margin:0 0 8px}
.page h2{font-weight:500;font-style:italic;font-size:clamp(32px,4vw,46px);letter-spacing:-.04em;line-height:1.04;margin:0 0 20px;color:var(--ink)}
.aff{font-style:italic;color:var(--ink);border-left:2px solid var(--gold);padding-left:14px;margin:0 0 18px;font-size:16.5px}
.tag{display:inline-block;border:1px solid #b08d5b;border-radius:999px;color:#5c4a2e;padding:5px 12px;font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;margin:0 6px 8px 0}
.tag.oui{background:var(--gold);border-color:var(--gold);color:#fff}
.lbl{font-size:9.5px;letter-spacing:.3em;text-transform:uppercase;color:#8a7a5e;margin:18px 0 8px}
.kw{display:flex;flex-wrap:wrap;gap:6px}
.kw span{border:1px solid rgba(36,29,18,.2);padding:5px 10px;font-size:12.5px;color:#5f5237}
.kw.dn span{color:#8d5a4e;border-color:rgba(141,90,78,.35)}
.more{display:inline-block;margin-top:24px;color:var(--ink);text-decoration:none;border-bottom:1px solid var(--gold);font-size:11px;letter-spacing:.22em;text-transform:uppercase;padding-bottom:4px}
.more:hover{background:var(--gold);color:#fff}
/* ——— mobile ——— */
@media(max-width:700px){
 header{padding:14px 16px}.nav{display:none}
 .hero{left:16px;right:16px;top:58px}.lede{display:none}
 h1{font-size:16.5vw}
 footer{left:12px;right:12px;bottom:12px;flex-direction:column;align-items:stretch;gap:10px}
 .families{max-width:none;overflow:auto;flex-wrap:nowrap}
 .meta{display:flex;justify-content:space-between;align-items:center}.arrows{margin:0}
 .page{top:auto;height:72dvh;border-left:0;border-top:1px solid var(--gold);padding:42px 22px 26px;transform:translateY(105%)}
}
</style></head><body>
<header><div class="brand">TAROT <em>·</em> CLM <em>— V<?= $ver ?></em></div><nav class="nav"><a href="../v9/">Collection V9</a><a href="../v11/">Arcana Index V11</a><button id="rand">Au hasard</button></nav></header>
<section class="hero" id="hero">
 <p class="kicker">L'étagère des <b>best-sellers</b> · édition MMXXVI</p>
 <h1>Soixante-<br>Dix-<span class="alt">Huit</span></h1>
 <p class="lede">L'intégrale des lames du tarot de Marseille, reliée comme une collection de livres rares.</p>
</section>
<footer>
 <div class="families" id="families"></div>
 <div class="meta"><span><b id="pos">01</b> / <span id="tot">78</span></span> · molette · glisser · ← →<div class="arrows"><button id="prev" aria-label="Précédente">←</button><button id="next" aria-label="Suivante">→</button></div></div>
</footer>
<div id="veil"><article class="page">
 <button class="close" id="close">×</button>
 <p class="eyebrow" id="pFam"></p><h2 id="pName"></h2>
 <p class="aff" id="pAff"></p><div id="pTags"></div>
 <div class="lbl">Endroit</div><div class="kw" id="pUp"></div>
 <div class="lbl">Envers</div><div class="kw dn" id="pDn"></div>
 <a class="more" id="pLink" target="_blank" rel="noopener">Lire la fiche complète — V9 ↗</a>
</article></div>
<script type="importmap">{"imports":{"three":"https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js"}}</script>
<script type="module">
import * as THREE from 'three';
const LAMES=<?= $lj ?>,FAMS=<?= $fj ?>,B=<?= json_encode($base,JSON_UNESCAPED_SLASHES) ?>;
const pad=n=>String(n).padStart(2,'0');
const words=s=>String(s||'').split(',').map(x=>x.trim()).filter(Boolean);
const img=l=>B+'/index.php?img='+encodeURIComponent(l.id+'.jpg');
const famKeys=Object.keys(FAMS);

/* ——— scène ——— */
const scene=new THREE.Scene();
scene.fog=new THREE.Fog(0x17110a,6.5,26);
const camera=new THREE.PerspectiveCamera(38,innerWidth/innerHeight,.1,120);
const renderer=new THREE.WebGLRenderer({antialias:true});
renderer.setPixelRatio(Math.min(devicePixelRatio,2));
renderer.setSize(innerWidth,innerHeight);
renderer.setClearColor(0x17110a,1);
document.body.prepend(renderer.domElement);

/* dimensions : lames 413×709 → ratio 0.583 ; bord doré cuit dans la texture (1 mesh = 0 glitch de transparence) */
const CARD_H=1.5,CARD_W=CARD_H*0.583,SHELF_Y=-0.78;
const imgLoader=new THREE.ImageLoader(),maxAniso=renderer.capabilities.getMaxAnisotropy();
let x=0;const cards=[],famRange={},byFam={};
LAMES.forEach((l,i)=>{
 if(i>0)x+=(l.f!==LAMES[i-1].f)?1.0:0.92; /* livres serrés, respiration entre familles */
 if(!famRange[l.f])famRange[l.f]=[x,x];
 famRange[l.f][1]=x;(byFam[l.f]??=[]).push(i);
 const mat=new THREE.MeshBasicMaterial({color:0x17110a,transparent:true,opacity:0,depthWrite:true});
 const m=new THREE.Mesh(new THREE.PlaneGeometry(CARD_W,CARD_H),mat);
 m.position.set(x,SHELF_Y-1.7,0.02+(Math.random()-.5)*.02);
 const rot=(Math.random()-.5)*.05;m.rotation.z=rot;
 const u={l,i,home:new THREE.Vector3(x,SHELF_Y+CARD_H/2,0),rot,born:performance.now()+400+i*22,op:0,out:false,ready:false};
 m.userData=u;m.visible=false;scene.add(m);cards.push(m);
 imgLoader.load(img(l),im=>{
  const cv=document.createElement('canvas');cv.width=429;cv.height=725;
  const g=cv.getContext('2d');g.fillStyle='#9a7b4f';g.fillRect(0,0,429,725);g.drawImage(im,8,8,413,709);
  const tex=new THREE.CanvasTexture(cv);tex.colorSpace=THREE.SRGBColorSpace;tex.anisotropy=maxAniso;
  mat.map=tex;mat.color.set(0xffffff);mat.needsUpdate=true;u.ready=true;
 });
});
const first=cards[0].userData.home.x,last=cards[cards.length-1].userData.home.x;
const span=last-first;

/* ——— bibliothèque : étagère, liseré, séparateurs, plaques ——— */
const shelf=new THREE.Mesh(new THREE.BoxGeometry(span+3,.1,.62),new THREE.MeshBasicMaterial({color:0x2e2214}));
shelf.position.set((first+last)/2,SHELF_Y-.05,0);scene.add(shelf);
const trim=new THREE.Mesh(new THREE.BoxGeometry(span+3,.014,.05),new THREE.MeshBasicMaterial({color:0xc3a47b}));
trim.position.set((first+last)/2,SHELF_Y+.007,.29);scene.add(trim);
const floor=new THREE.Mesh(new THREE.PlaneGeometry(220,60),new THREE.MeshBasicMaterial({color:0x100b06}));
floor.rotation.x=-Math.PI/2;floor.position.set((first+last)/2,SHELF_Y-1.6,0);scene.add(floor);
famKeys.forEach(k=>{
 const [a,b]=famRange[k];
 const sep=new THREE.Mesh(new THREE.BoxGeometry(.014,CARD_H+.24,.014),new THREE.MeshBasicMaterial({color:0xc3a47b}));
 sep.position.set(a-.47,SHELF_Y+(CARD_H+.24)/2,0);scene.add(sep);
 const cv=document.createElement('canvas');cv.width=640;cv.height=140;
 const cx=cv.getContext('2d');cx.font='italic 500 52px Georgia,serif';cx.textAlign='center';cx.textBaseline='middle';
 cx.fillStyle='rgba(195,164,123,.92)';cx.fillText((FAMS[k].short||'').toUpperCase().split('').join('\u2009'),320,72);
 const pl=new THREE.Mesh(new THREE.PlaneGeometry(2.2,.48),new THREE.MeshBasicMaterial({map:new THREE.CanvasTexture(cv),transparent:true,opacity:.9}));
 pl.position.set((a+b)/2,SHELF_Y+CARD_H+.34,0);scene.add(pl);
});

/* ——— poussière d'or ——— */
const dustGeo=new THREE.BufferGeometry(),dustPos=[];
for(let i=0;i<300;i++)dustPos.push(first-2+Math.random()*(span+4),SHELF_Y-1+Math.random()*3.4,-1.6+Math.random()*2.8);
dustGeo.setAttribute('position',new THREE.Float32BufferAttribute(dustPos,3));
const dust=new THREE.Points(dustGeo,new THREE.PointsMaterial({color:0xd8b47c,size:.02,transparent:true,opacity:.5,blending:THREE.AdditiveBlending,depthWrite:false}));
scene.add(dust);

/* ——— caméra : glisse le long de l'étagère ——— */
let tx=cards[0].userData.home.x; /* départ : Le Fou */
let camX=tx,baseZ=4.4;
const clampX=v=>Math.max(first-.4,Math.min(last+.4,v));
function fitCamera(){const a=innerWidth/innerHeight;baseZ=a<.75?6.4:(a<1.1?5.2:4.4)}
fitCamera();camera.position.set(tx,1.02,baseZ+4.5);camera.lookAt(tx,.32,0);

/* ——— interactions ——— */
const ray=new THREE.Raycaster(),pointer=new THREE.Vector2();
let hovered=null,selected=null,drag=null,vel=0;
const canvas=renderer.domElement;
canvas.addEventListener('pointermove',e=>{
 pointer.x=e.clientX/innerWidth*2-1;pointer.y=-(e.clientY/innerHeight)*2+1;
 if(drag){
  drag.moved+=Math.abs(e.clientX-drag.px);
  vel=(drag.px-e.clientX)*.012;drag.px=e.clientX;
  tx=clampX(drag.tx-(e.clientX-drag.sx)*.012);return;
 }
 if(!selected){ray.setFromCamera(pointer,camera);const hit=ray.intersectObjects(cards)[0];hovered=(hit&&!hit.object.userData.out)?hit.object:null;canvas.style.cursor=hovered?'pointer':'grab'}
});
function pick(cx,cy){pointer.x=cx/innerWidth*2-1;pointer.y=-(cy/innerHeight)*2+1;ray.setFromCamera(pointer,camera);const h=ray.intersectObjects(cards)[0];return(h&&!h.object.userData.out)?h.object:null}
canvas.addEventListener('pointerdown',e=>{drag={sx:e.clientX,px:e.clientX,tx,moved:0};vel=0;canvas.classList.add('drag')});
addEventListener('pointerup',e=>{
 if(!drag)return;canvas.classList.remove('drag');
 if(drag.moved<7&&e.target===canvas&&!selected){const m=pick(e.clientX,e.clientY);if(m)openCard(m)}
 drag=null;
});
canvas.addEventListener('wheel',e=>{e.preventDefault();if(!selected)tx=clampX(tx+(Math.abs(e.deltaY)>Math.abs(e.deltaX)?e.deltaY:e.deltaX)*.0035)},{passive:false});

/* ——— fiche ——— */
const veil=document.getElementById('veil');
function openCard(m){
 selected=m;hovered=null;
 const l=m.userData.l,f=FAMS[l.f]||{};
 document.getElementById('pFam').textContent='N°'+pad(l.num)+' · '+(f.n||'')+(f.line?' — '+f.line:'');
 document.getElementById('pName').textContent=l.n;
 const pa=document.getElementById('pAff');pa.textContent=l.aff?'« '+l.aff+' »':'';pa.style.display=l.aff?'':'none';
 document.getElementById('pTags').innerHTML=(l.rep?'<span class="tag'+(l.rep==='OUI'?' oui':'')+'">'+l.rep+'</span>':'')+words(l.up).slice(0,2).map(w=>'<span class="tag">'+w+'</span>').join('');
 document.getElementById('pUp').innerHTML=words(l.up).map(x=>'<span>'+x+'</span>').join('');
 document.getElementById('pDn').innerHTML=words(l.dn).map(x=>'<span>'+x+'</span>').join('');
 document.getElementById('pLink').href='../v9/?carte='+encodeURIComponent(l.id);
 veil.classList.add('open');
 history.replaceState(null,'',location.pathname+'?carte='+encodeURIComponent(l.id));
}
function closeCard(){if(!selected)return;selected=null;veil.classList.remove('open');history.replaceState(null,'',location.pathname)}
document.getElementById('close').onclick=closeCard;
veil.addEventListener('click',e=>{if(e.target===veil)closeCard()});
document.getElementById('rand').onclick=()=>openCard(cards[Math.floor(Math.random()*cards.length)]);

/* ——— navigation & filtres ——— */
let famOn='';
const visibleIdx=()=>!famOn?cards.map((c,i)=>i):byFam[famOn];
function nearest(){const vis=visibleIdx();let best=vis[0],bd=1e9;vis.forEach(i=>{const d=Math.abs(cards[i].userData.home.x-camX);if(d<bd){bd=d;best=i}});return best}
function step(d){const vis=visibleIdx();let k=vis.indexOf(nearest());k=Math.max(0,Math.min(vis.length-1,k+d));tx=cards[vis[k]].userData.home.x}
document.getElementById('prev').onclick=()=>step(-1);
document.getElementById('next').onclick=()=>step(1);
function renderChips(){
 document.getElementById('families').innerHTML='<button class="chip '+(!famOn?'on':'')+'" data-f=""><i>✦</i>Toutes</button>'+famKeys.map(k=>'<button class="chip '+(famOn===k?'on':'')+'" data-f="'+k+'"><i>'+FAMS[k].sym+'</i>'+FAMS[k].short+'</button>').join('');
}
document.getElementById('families').addEventListener('click',e=>{const b=e.target.closest('[data-f]');if(!b)return;famOn=b.dataset.f;renderChips();applyFam()});
function applyFam(){
 cards.forEach(c=>c.userData.out=famOn!==''&&c.userData.l.f!==famOn);
 if(famOn){const [a,b]=famRange[famOn];tx=(a+b)/2}else tx=cards[Math.max(0,nearest())].userData.home.x;
 document.getElementById('tot').textContent=visibleIdx().length;
}
addEventListener('keydown',e=>{
 if(e.key==='ArrowLeft')step(-1);
 else if(e.key==='ArrowRight')step(1);
 else if(e.key==='Enter'&&!selected&&!e.target.closest('input'))openCard(cards[nearest()]);
 else if(e.key==='Escape')closeCard();
});

/* ——— boucle ——— */
const target=new THREE.Vector3(),tscale=new THREE.Vector3(1,1,1);
document.getElementById('tot').textContent=cards.length;
function tick(t){
 requestAnimationFrame(tick);
 const now=performance.now();
 if(!drag&&Math.abs(vel)>.0004){tx=clampX(tx+vel);vel*=.94}
 camX+=(tx-camX)*.07;
 camera.position.x+=(camX-camera.position.x)*.09;
 camera.position.y+=(1.02-camera.position.y)*.05;
 camera.position.z+=((selected?baseZ+.4:baseZ)-camera.position.z)*.04;
 camera.lookAt(camera.position.x,.32,0);
 /* cadrage de la lame sélectionnée : à gauche du panneau (paysage) ou en haut (portrait) */
 let fr={gx:0,gy:0,gz:0,rs:1};
 if(selected){
  const dist=5.7,hh=Math.tan(camera.fov*Math.PI/360)*dist,hw=hh*camera.aspect,portrait=camera.aspect<1;
  fr={gx:camera.position.x-(portrait?0:hw*.44),gy:portrait?hh*.34:-.02,gz:camera.position.z-dist,rs:portrait?1.5:2};
 }
 const disp=Math.abs(camera.position.x-cards[0].userData.home.x)/Math.max(1,span);
 hero.style.opacity=String(Math.max(0,1-disp*2.6)*heroIn);
 hero.style.transform='translateX('+(-disp*130)+'px)';
 dust.rotation.z=Math.sin(t*.00006)*.05;
 const vis=visibleIdx();let best=vis[0],bd=1e9;
 cards.forEach(m=>{
  const u=m.userData;
  if(!m.visible&&u.ready&&now>=u.born){m.visible=true;u.op=.02}
  let gx=u.home.x,gy=u.home.y+Math.sin(t*.0011+u.i*.63)*.012,gz=u.home.z,rs=1,ry=0,op=u.out?.06:1;
  if(m===hovered&&m!==selected){gy+=.3;gz+=.12;rs=1.08;ry=Math.max(-.22,Math.min(.22,(camera.position.x-u.home.x)*-.09))}
  if(m===selected){gx=fr.gx;gy=fr.gy;gz=fr.gz;rs=fr.rs;ry=0;op=1}
  if(u.out)gy-=.55;
  target.set(gx,gy,gz);
  m.position.lerp(target,.11);
  m.rotation.z+=((m===selected?0:u.rot)-m.rotation.z)*.1;
  m.rotation.y+=(ry-m.rotation.y)*.12;
  m.scale.lerp(tscale.setScalar(rs),.12);
  u.op+=(op-u.op)*.08;
  m.material.opacity=u.op;
  const d=Math.abs(u.home.x-camera.position.x);if(d<bd){bd=d;best=u.i}
 });
 document.getElementById('pos').textContent=pad(vis.indexOf(best)+1);
 renderer.render(scene,camera);
}
/* entrée du titre */
const hero=document.getElementById('hero');let heroIn=0;
setTimeout(()=>{const t0=performance.now();(function fade(){heroIn=Math.min(1,(performance.now()-t0)/900);if(heroIn<1)requestAnimationFrame(fade)})()},400);
/* état initial */
renderChips();
const init=new URLSearchParams(location.search).get('carte');
if(init){const k=LAMES.findIndex(l=>l.id===init);if(k>=0)setTimeout(()=>openCard(cards[k]),1100)}
addEventListener('resize',()=>{camera.aspect=innerWidth/innerHeight;camera.updateProjectionMatrix();fitCamera();renderer.setSize(innerWidth,innerHeight)});
tick(0);
</script>
</body></html>
