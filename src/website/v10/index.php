<?php
declare(strict_types=1);

final class Vault {
    private static ?PDO $db = null;
    static function db(): PDO {
        return self::$db ??= new PDO('sqlite:' . __DIR__ . '/../v9/vault.sqlite', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    static function json(string $path): ?array {
        $s = self::db()->prepare('SELECT data FROM vault WHERE path=?');
        $s->execute([$path]);
        $r = $s->fetch();
        return $r ? json_decode((string)$r['data'], true) : null;
    }
    static function image(string $path): never {
        $s = self::db()->prepare('SELECT mime,data FROM vault WHERE path=?');
        $s->execute([$path]);
        $r = $s->fetch();
        if (!$r) { http_response_code(404); exit('Not found'); }
        header('Content-Type: ' . $r['mime']);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Length: ' . strlen($r['data']));
        echo $r['data'];
        exit;
    }
}

function base_path(): string {
    if ($base = $_SERVER['TAROT_LOCAL_BASE_PATH'] ?? '') return $base;
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return $dir === '/' || $dir === '.' ? '' : $dir;
}

$base = base_path();
$path = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if ($base !== '' && str_starts_with($path, $base)) $path = substr($path, strlen($base));
$path = '/' . trim($path, '/');

if (isset($_GET['img'])) Vault::image('/img/' . urldecode((string)$_GET['img']));
if ($path !== '/') { http_response_code(404); exit('404'); }

$data = Vault::json('/app-data.json');
if (!$data) { http_response_code(500); exit('Vault incomplet'); }

$portraits = json_decode((string) @file_get_contents(__DIR__ . '/portraits.json'), true) ?: [];

function portrait_field(?string $md, string $emoji, string $label): string {
    if (!$md) return '';
    foreach (explode("\n", $md) as $line) {
        $line = trim($line);
        if ($line === '' || !str_starts_with($line, $emoji)) continue;
        $rest = trim(mb_substr($line, mb_strlen($emoji)));
        $rest = preg_replace('/^' . preg_quote($label, '/') . '\s*:\s*/iu', '', $rest);
        return trim($rest);
    }
    return '';
}

$lames = [];
foreach ($data['cards'] as $c) {
    $md = $portraits[$c['id']] ?? null;
    $lames[] = [
        'id' => $c['id'],
        'n'  => $c['name'],
        'f'  => $c['fam'],
        'num' => $c['num'],
        'up' => $c['keywords_up'] ?? '',
        'dn' => $c['keywords_down'] ?? '',
        'rep' => trim((string)($data['es'][$c['id']]['rep'] ?? '')),
        'key' => portrait_field($md, '🔑', 'Mot-clé distinctif'),
        'idee' => portrait_field($md, '🧠', 'Idée centrale'),
        'real' => portrait_field($md, '💭', 'Ce qui se passe réellement'),
    ];
}
$fams = [];
foreach ($data['families'] as $f) $fams[$f['key']] = ['n' => $f['name'], 'ac' => $f['ac'] ?? '#c9a227'];

$lamesJson = json_encode($lames, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$famsJson = json_encode($fams, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$baseJson = json_encode($base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$v9Json = json_encode($base ? $base . '/../v9/' : '../v9/', JSON_UNESCAPED_SLASHES);
$ver = '2026.09.02';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>Arcana Orbital — Tarot v<?= $ver ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🜃</text></svg>">
<style>
:root{--bg:#050408;--fg:#e9e4d8;--muted:#7d7668;--ac:#d8b24a;--ac-soft:rgba(216,178,74,.16);--line:rgba(216,178,74,.22);--glass:rgba(10,8,14,.72)}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden;background:var(--bg);color:var(--fg)}
body{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:.08em;text-transform:uppercase;font-size:.68rem;-webkit-font-smoothing:antialiased}
canvas{display:block}
#scene{position:fixed;inset:0;cursor:grab}
#scene.drag{cursor:grabbing}
.hud{position:fixed;z-index:10;pointer-events:none}
.hud button,.hud a{pointer-events:auto}
#brand{top:1.4rem;left:1.6rem;line-height:1.9}
#brand h1{font-size:.8rem;font-weight:500;letter-spacing:.34em;color:var(--fg)}
#brand h1 b{color:var(--ac);font-weight:500}
#brand p{font-size:.56rem;letter-spacing:.22em;color:var(--muted)}
#modes{top:1.3rem;right:1.6rem;display:flex;gap:.45rem}
#modes button{background:var(--glass);backdrop-filter:blur(10px);border:1px solid var(--line);color:var(--muted);font:inherit;letter-spacing:.18em;padding:.62rem 1.05rem;border-radius:40px;cursor:pointer;transition:.3s}
#modes button:hover{color:var(--fg);border-color:var(--ac)}
#modes button.on{color:var(--ac);border-color:var(--ac);background:var(--ac-soft)}
#hint{bottom:1.5rem;left:50%;transform:translateX(-50%);font-size:.56rem;letter-spacing:.28em;color:var(--muted);transition:opacity .8s;white-space:nowrap}
#hint.gone{opacity:0}
#loader{position:fixed;inset:0;z-index:50;background:var(--bg);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.3rem;transition:opacity .9s}
#loader.gone{opacity:0;pointer-events:none}
#loader .sigil{font-size:2.6rem;color:var(--ac);animation:spin 7s linear infinite}
#loader .txt{font-size:.6rem;letter-spacing:.4em;color:var(--muted)}
#loader .bar{width:180px;height:1px;background:rgba(216,178,74,.15)}
#loader .bar i{display:block;height:100%;width:0;background:var(--ac);transition:width .4s}
@keyframes spin{to{transform:rotate(360deg)}}
#panel{position:fixed;z-index:20;top:0;right:0;bottom:0;width:min(400px,92vw);background:var(--glass);backdrop-filter:blur(18px);border-left:1px solid var(--line);padding:4.4rem 2rem 2rem;transform:translateX(102%);transition:transform .55s cubic-bezier(.22,1,.36,1);overflow-y:auto;pointer-events:auto}
#panel.open{transform:none}
#panel .close{position:absolute;top:1.2rem;right:1.3rem;background:none;border:1px solid var(--line);color:var(--muted);width:34px;height:34px;border-radius:50%;font-size:1rem;cursor:pointer;transition:.3s}
#panel .close:hover{color:var(--ac);border-color:var(--ac)}
#pFam{font-size:.56rem;letter-spacing:.3em;color:var(--muted)}
#pFam b{color:var(--ac);font-weight:500}
#pName{font-family:Georgia,'Times New Roman',serif;font-style:italic;font-weight:400;font-size:clamp(1.9rem,4vw,2.5rem);letter-spacing:0;text-transform:none;color:var(--fg);margin:.7rem 0 1.4rem;line-height:1.05}
#pKey{display:inline-block;border:1px solid var(--ac);border-radius:40px;padding:.42rem .85rem;color:var(--ac);font-size:.6rem;letter-spacing:.16em;margin-bottom:1.5rem}
#pRep{display:inline-block;border-radius:40px;padding:.42rem .85rem;font-size:.58rem;letter-spacing:.16em;border:1px solid;margin-left:.4rem}
#pRep.oui{color:#81c784;border-color:rgba(129,199,132,.5)} #pRep.non{color:#e57373;border-color:rgba(229,115,115,.5)} #pRep.peut-être{color:var(--ac);border-color:var(--line)} #pRep.pas-encore{color:#ffb74d;border-color:rgba(255,167,38,.5)}
#pIdee{font-family:Georgia,serif;font-style:italic;font-size:1.02rem;letter-spacing:0;text-transform:none;line-height:1.5;color:var(--fg);border-left:2px solid var(--ac);padding-left:1rem;margin-bottom:1.2rem}
#pReal{font-size:.72rem;letter-spacing:.02em;text-transform:none;line-height:1.7;color:var(--muted);margin-bottom:1.8rem}
.pk{margin-bottom:1.6rem}
.pk h3{font-size:.55rem;letter-spacing:.26em;color:var(--muted);font-weight:400;margin-bottom:.6rem}
.pk div{display:flex;flex-wrap:wrap;gap:.35rem}
.pk span{border:1px solid var(--line);border-radius:40px;padding:.3rem .65rem;font-size:.56rem;color:#bdb5a4}
.pk.up span{color:#cde8ce;border-color:rgba(129,199,132,.3)}
.pk.dn span{color:#f0c3c3;border-color:rgba(229,115,115,.3)}
#pLink{display:inline-block;margin-top:.4rem;border:1px solid var(--line);border-radius:40px;padding:.6rem 1.1rem;color:var(--muted);font-size:.56rem;letter-spacing:.2em;text-decoration:none;transition:.3s}
#pLink:hover{color:var(--ac);border-color:var(--ac)}
@media(max-width:640px){
  #panel{top:auto;left:0;right:0;width:100%;height:62dvh;border-left:none;border-top:1px solid var(--line);border-radius:18px 18px 0 0;transform:translateY(105%);padding:3.2rem 1.4rem 2rem}
  #panel.open{transform:none}
  #modes{top:4.35rem;left:.8rem;right:.8rem;justify-content:flex-end}
  #modes button,#modes a{padding:.5rem .72rem;font-size:.56rem}
  #brand h1{font-size:.66rem;letter-spacing:.24em}
  #hint{bottom:1rem;width:calc(100vw - 1.6rem);font-size:.5rem;letter-spacing:.16em;line-height:1.5;text-align:center;white-space:normal}
}
</style>
</head>
<body>
<div id="scene"></div>

<div class="hud" id="brand"><h1>ARCANA <b>ORBITAL</b></h1><p>78 LAMES · V<?= $ver ?></p></div>
<div class="hud" id="modes">
  <button id="mOrbit" class="on">Orbite</button>
  <button id="mSpread">Tirage</button>
  <a id="mV9" href="<?= htmlspecialchars($base ? $base . '/../v9/' : '../v9/') ?>" style="display:inline-flex;align-items:center;background:var(--glass);backdrop-filter:blur(10px);border:1px solid var(--line);color:var(--muted);text-decoration:none;letter-spacing:.18em;padding:.62rem 1.05rem;border-radius:40px;transition:.3s" onmouseover="this.style.color=this.style.borderColor='#d8b24a'" onmouseout="this.style.color=''">V9</a>
</div>
<div class="hud" id="hint">GLISSER POUR ORBITER · MOLETTE POUR APPROCHER · CLIQUER UNE LAME</div>

<div id="loader"><div class="sigil">🜃</div><div class="txt">ALIGNEMENT DES ARCHIVES</div><div class="bar"><i id="lbar"></i></div></div>

<aside id="panel">
  <button class="close" id="pClose" aria-label="Fermer">✕</button>
  <div id="pFam"></div>
  <h2 id="pName"></h2>
  <div><span id="pKey"></span><span id="pRep"></span></div>
  <p id="pIdee"></p>
  <p id="pReal"></p>
  <div class="pk up" id="pUp"><h3>Endroit</h3><div></div></div>
  <div class="pk dn" id="pDn"><h3>Envers</h3><div></div></div>
  <a id="pLink" target="_blank" rel="noopener">FICHE COMPLÈTE · V9 ↗</a>
</aside>

<script type="importmap">
{"imports":{"three":"https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js","three/addons/":"https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"}}
</script>
<script type="module">
import * as THREE from 'three';
import {EffectComposer} from 'three/addons/postprocessing/EffectComposer.js';
import {RenderPass} from 'three/addons/postprocessing/RenderPass.js';
import {UnrealBloomPass} from 'three/addons/postprocessing/UnrealBloomPass.js';
import {OutputPass} from 'three/addons/postprocessing/OutputPass.js';

const B=<?= $baseJson ?>;
const LAMES=<?= $lamesJson ?>;
const FAMS=<?= $famsJson ?>;
const V9=<?= $v9Json ?>;

const sceneEl=document.getElementById('scene');
const renderer=new THREE.WebGLRenderer({antialias:true,alpha:false,powerPreference:'high-performance'});
renderer.setPixelRatio(Math.min(devicePixelRatio,2));
renderer.setSize(innerWidth,innerHeight);
renderer.setClearColor(0x050408,1);
sceneEl.appendChild(renderer.domElement);

const scene=new THREE.Scene();
const camera=new THREE.PerspectiveCamera(55,innerWidth/innerHeight,.1,300);
let camDist=22,camDistT=22;
const camEl=0.78;
function placeCamera(){camera.position.set(0,Math.sin(camEl)*camDist,Math.cos(camEl)*camDist);camera.lookAt(0,0,0)}
placeCamera();

const composer=new EffectComposer(renderer);
composer.addPass(new RenderPass(scene,camera));
const bloom=new UnrealBloomPass(new THREE.Vector2(innerWidth,innerHeight),.5,.85,.8);
composer.addPass(bloom);
composer.addPass(new OutputPass());

const nebula=new THREE.Mesh(
  new THREE.SphereGeometry(140,48,32),
  new THREE.ShaderMaterial({side:THREE.BackSide,depthWrite:false,uniforms:{uT:{value:0}},
  vertexShader:`varying vec3 vP;void main(){vP=position;gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);}`,
  fragmentShader:`
    varying vec3 vP;uniform float uT;
    float h(vec2 p){return fract(sin(dot(p,vec2(127.1,311.7)))*43758.5453);}
    float n2(vec2 p){vec2 i=floor(p),f=fract(p);f=f*f*(3.-2.*f);
      return mix(mix(h(i),h(i+vec2(1,0)),f.x),mix(h(i+vec2(0,1)),h(i+vec2(1,1)),f.x),f.y);}
    float fbm(vec2 p){float v=0.,a=.5;for(int i=0;i<5;i++){v+=a*n2(p);p*=2.03;a*=.5;}return v;}
    void main(){
      vec3 d=normalize(vP);
      vec2 u=vec2(atan(d.z,d.x),d.y)*2.6;
      float t=uT*.015;
      float w=fbm(u*.8+t);
      float n=fbm(u*1.4+w*2.2-t*.6);
      vec3 deep=vec3(.012,.01,.03);
      vec3 violet=vec3(.10,.05,.20);
      vec3 gold=vec3(.45,.33,.10);
      vec3 c=deep+violet*smoothstep(.45,.85,n)*.55+gold*smoothstep(.68,.98,w)*.30;
      c*=1.-.45*length(d.xy);
      gl_FragColor=vec4(c,1.);
    }`}));
scene.add(nebula);

{const N=1600,pos=new Float32Array(N*3),ph=new Float32Array(N),sz=new Float32Array(N);
for(let i=0;i<N;i++){const r=60+Math.random()*70,th=Math.random()*Math.PI*2,phE=Math.acos(2*Math.random()-1);
pos[i*3]=r*Math.sin(phE)*Math.cos(th);pos[i*3+1]=r*Math.cos(phE)*.7;pos[i*3+2]=r*Math.sin(phE)*Math.sin(th);
ph[i]=Math.random()*Math.PI*2;sz[i]=.5+Math.random()*1.6;}
const g=new THREE.BufferGeometry();
g.setAttribute('position',new THREE.BufferAttribute(pos,3));
g.setAttribute('aPh',new THREE.BufferAttribute(ph,1));
g.setAttribute('aSz',new THREE.BufferAttribute(sz,1));
const stars=new THREE.Points(g,new THREE.ShaderMaterial({transparent:true,depthWrite:false,blending:THREE.AdditiveBlending,
uniforms:{uT:{value:0}},
vertexShader:`attribute float aPh;attribute float aSz;uniform float uT;varying float vA;
void main(){vA=.35+.65*(.5+.5*sin(uT*1.4+aPh));
vec4 mv=modelViewMatrix*vec4(position,1.);gl_PointSize=aSz*(160./-mv.z);gl_Position=projectionMatrix*mv;}`,
fragmentShader:`varying float vA;void main(){float d=length(gl_PointCoord-.5);if(d>.5)discard;
float a=smoothstep(.5,.05,d)*vA;gl_FragColor=vec4(.85,.78,.58,a);}`}));
scene.add(stars);}

const galaxy=new THREE.Group();
scene.add(galaxy);

function backTexture(){
  const c=document.createElement('canvas');c.width=256;c.height=384;const x=c.getContext('2d');
  x.fillStyle='#0c0a10';x.fillRect(0,0,256,384);
  x.strokeStyle='rgba(216,178,74,.85)';x.lineWidth=3;x.strokeRect(10,10,236,364);
  x.lineWidth=1;x.strokeRect(18,18,220,348);
  const cx=128,cy=192;
  x.save();x.translate(cx,cy);
  x.strokeStyle='rgba(216,178,74,.9)';
  for(let i=0;i<24;i++){x.rotate(Math.PI/12);x.beginPath();x.moveTo(0,-58);x.lineTo(0,-92);x.stroke();}
  x.restore();
  x.beginPath();x.arc(cx,cy,58,0,Math.PI*2);x.stroke();
  x.beginPath();x.arc(cx,cy,44,0,Math.PI*2);x.stroke();
  x.beginPath();x.arc(cx,cy,7,0,Math.PI*2);x.fillStyle='rgba(216,178,74,.95)';x.fill();
  x.beginPath();x.arc(cx,cy,20,0,Math.PI*2);x.stroke();
  x.fillStyle='rgba(216,178,74,.5)';
  for(let i=0;i<40;i++){const a=i*.82,r=105+18*Math.sin(i*1.7);const px=cx+Math.cos(a)*r,py=cy+Math.sin(a)*r*.82;
    if(px>26&&px<230&&py>26&&py<358){x.beginPath();x.arc(px,py,1.1,0,Math.PI*2);x.fill();}}
  const t=new THREE.CanvasTexture(c);t.anisotropy=renderer.capabilities.getMaxAnisotropy();t.colorSpace=THREE.SRGBColorSpace;
  return t;
}
const backTex=backTexture();
const backMat=new THREE.MeshBasicMaterial({map:backTex});
const CW=.72,CH=1.15;

const loaderEl=document.getElementById('loader'),lbar=document.getElementById('lbar');
let loaded=0;
function done(){loaded++;lbar.style.width=Math.min(100,loaded/LAMES.length*100)+'%';
  if(loaded>=LAMES.length)setTimeout(()=>loaderEl.classList.add('gone'),400)}
setTimeout(()=>loaderEl.classList.add('gone'),6000);

const cards=[],fronts=[];
const GA=Math.PI*(3-Math.sqrt(5));
LAMES.forEach((l,i)=>{
  const r=1.9*Math.sqrt(i+1),a=i*GA;
  const tex=new THREE.TextureLoader().load(B+'/index.php?img='+encodeURIComponent(l.id+'.jpg'),()=>done(),undefined,()=>done());
  tex.anisotropy=renderer.capabilities.getMaxAnisotropy();tex.colorSpace=THREE.SRGBColorSpace;
  const front=new THREE.Mesh(new THREE.PlaneGeometry(CW,CH),new THREE.MeshBasicMaterial({map:tex}));
  front.userData.lame=l;
  const back=new THREE.Mesh(new THREE.PlaneGeometry(CW,CH),backMat);
  back.rotation.y=Math.PI;
  const grp=new THREE.Group();
  grp.add(front);grp.add(back);
  grp.position.set(Math.cos(a)*r,(Math.random()-.5)*.7,Math.sin(a)*r);
  grp.rotation.set(-Math.PI/2+(Math.random()-.5)*.14,0,(Math.random()-.5)*.22);
  grp.userData={home:grp.position.clone(),homeQ:grp.quaternion.clone(),lift:0,ph:Math.random()*Math.PI*2,lame:l,front};
  fronts.push(front);
  galaxy.add(grp);
  cards.push(grp);
});

const ring=new THREE.Mesh(new THREE.RingGeometry(CW*.78,CW*.95,40),
  new THREE.MeshBasicMaterial({color:0xd8b24a,transparent:true,opacity:0,side:THREE.DoubleSide,depthWrite:false}));
ring.rotation.x=-Math.PI/2;scene.add(ring);

const ray=new THREE.Raycaster(),ptr=new THREE.Vector2();
let mode='orbit',hover=null,focus=null,down=null,dragged=false,rotV=0,spinV=0;
const spin=new THREE.Group();scene.add(spin);

function setPtr(e){ptr.x=(e.clientX/innerWidth)*2-1;ptr.y=-(e.clientY/innerHeight)*2+1}
sceneEl.addEventListener('pointerdown',e=>{down={x:e.clientX,y:e.clientY};dragged=false;sceneEl.classList.add('drag')});
addEventListener('pointermove',e=>{
  if(down){const dx=e.clientX-down.x;down.x=e.clientX;down.y=e.clientY;
    if(Math.abs(dx)>.5){rotV+=dx*.00028;dragged=true;hideHint()}}
  else if(mode!=='focus'){setPtr(e);ray.setFromCamera(ptr,camera);
    const hit=ray.intersectObjects(fronts,false)[0];
    const g=hit?hit.object.parent:null;
    if(g!==hover){if(hover)hover.userData.lift=0;hover=g;if(g)g.userData.lift=1;}
    ring.material.opacity+= ((hover?.55:0)-ring.material.opacity)*.2;
    sceneEl.style.cursor=hover?'pointer':'grab';
  }
});
addEventListener('pointerup',e=>{
  sceneEl.classList.remove('drag');
  const wasDrag=dragged;down=null;
  if(wasDrag)return;
  setPtr(e);ray.setFromCamera(ptr,camera);
  const hit=ray.intersectObjects(fronts,false)[0];
  if(!hit){if(mode==='focus')closeFocus();return}
  const g=hit.object.parent;
  if(mode==='spread'&&g.userData.spread){toggleSpreadCard(g);return}
  if(mode==='orbit')openFocus(g);
});
addEventListener('wheel',e=>{if(mode==='focus')return;camDistT=THREE.MathUtils.clamp(camDistT+e.deltaY*.012,9,42);hideHint()},{passive:true});

const panel=document.getElementById('panel');
function fillPanel(l){
  const f=FAMS[l.f]||{};
  document.getElementById('pFam').innerHTML=(l.num?l.num+' · ':'')+'<b>'+((f&&f.n)||'')+'</b>';
  document.getElementById('pName').textContent=l.n;
  document.getElementById('pKey').textContent=l.key||'—';
  const rep=document.getElementById('pRep'),r=(l.rep||'').toUpperCase();
  rep.style.display=r?'':'none';
  if(r){rep.textContent=r;rep.className=r.toLowerCase().replace(/\s+/g,'-')}
  document.getElementById('pIdee').textContent=l.idee||'';
  document.getElementById('pReal').textContent=l.real||'';
  const up=document.querySelector('#pUp div'),dn=document.querySelector('#pDn div');
  const ups=(l.up||'').split(',').map(s=>s.trim()).filter(Boolean),dns=(l.dn||'').split(',').map(s=>s.trim()).filter(Boolean);
  document.getElementById('pUp').style.display=ups.length?'':'none';
  document.getElementById('pDn').style.display=dns.length?'':'none';
  up.innerHTML=ups.map(k=>'<span>'+k+'</span>').join('');
  dn.innerHTML=dns.map(k=>'<span>'+k+'</span>').join('');
  document.getElementById('pLink').href=V9+'?carte='+encodeURIComponent(l.id);
}
function openFocus(g){
  if(focus)restore(focus);
  mode='focus';focus=g;hover=null;
  const l=g.userData.lame;fillPanel(l);
  syncCardUrl(l.id);
  document.getElementById('hint').classList.add('gone');
  panel.classList.add('open');
  g.userData.t0=performance.now();
}
function restore(g){g.userData.lerp={p0:g.position.clone(),p1:g.userData.home,r0:g.quaternion.clone(),r1:g.userData.homeQ,t0:performance.now(),d:600}}
function closeFocus(){if(!focus)return;restore(focus);focus=null;mode='orbit';panel.classList.remove('open');syncCardUrl()}
function syncCardUrl(id=''){const u=new URL(location.href);if(id)u.searchParams.set('carte',id);else u.searchParams.delete('carte');history.replaceState(null,'',u.pathname+(u.search?u.search:''))}
document.getElementById('pClose').onclick=closeFocus;
addEventListener('keydown',e=>{if(e.key==='Escape')closeFocus()});

let spreadCards=[];
const mOrbit=document.getElementById('mOrbit'),mSpread=document.getElementById('mSpread');
function setMode(m){
  if(m===mode)return;
  if(mode==='focus')closeFocus();
  mode=m;
  mOrbit.classList.toggle('on',m==='orbit');
  mSpread.classList.toggle('on',m==='spread');
  if(m==='spread')enterSpread();else exitSpread();
}
mOrbit.onclick=()=>setMode('orbit');
mSpread.onclick=()=>setMode('spread');

function enterSpread(){
  const pool=[...cards].sort(()=>Math.random()-.5).slice(0,3);
  spreadCards=pool;
  camDistT=13;
  pool.forEach((g,i)=>{
    g.userData.spread={i,flipped:false};
  });
  document.getElementById('hint').textContent='CLIQUER UNE LAME POUR LA RÉVÉLER';
  document.getElementById('hint').classList.remove('gone');
}
function exitSpread(){
  spreadCards.forEach(g=>{g.userData.spread=null;restore(g)});
  spreadCards=[];
  document.getElementById('hint').textContent='GLISSER POUR ORBITER · MOLETTE POUR APPROCHER · CLIQUER UNE LAME';
}
function toggleSpreadCard(g){
  g.userData.spread.flipped=!g.userData.spread.flipped;
  if(g.userData.spread.flipped){fillPanel(g.userData.lame);panel.classList.add('open')}
  else panel.classList.remove('open');
}

const tmpQ=new THREE.Quaternion(),camDir=new THREE.Vector3(),camRight=new THREE.Vector3(),camUp=new THREE.Vector3();
function ease(t){return t<.5?4*t*t*t:1-Math.pow(-2*t+2,3)/2}
function lerpAnim(g,now){
  const a=g.userData.lerp;if(!a)return false;
  const t=Math.min(1,(now-a.t0)/a.d),e=ease(t);
  g.position.lerpVectors(a.p0,a.p1,e);
  g.quaternion.slerpQuaternions(a.r0,a.r1,e);
  if(t>=1)g.userData.lerp=null;
  return true;
}

function frame(now){
  requestAnimationFrame(frame);
  const t=now*.001;
  nebula.material.uniforms.uT.value=t;
  nebula.position.copy(camera.position);
  rotV*=.94;
  if(mode!=='focus')galaxy.rotation.y+=rotV+.0004;
  camDist+=(camDistT-camDist)*.06;
  placeCamera();

  camera.getWorldDirection(camDir);
  camRight.crossVectors(camDir,camera.up).normalize();
  camUp.crossVectors(camRight,camDir).normalize();

  cards.forEach((g,idx)=>{
    const u=g.userData;
    if(lerpAnim(g,now))return;
    if(mode==='orbit'){
      u.lift+=( (g===hover?1:0) -u.lift)*.12;
      g.position.y=u.home.y+u.lift*.45+Math.sin(t*.8+u.ph)*.09;
      g.position.x=u.home.x*(1+u.lift*.06);
      g.position.z=u.home.z*(1+u.lift*.06);
      const s=1+u.lift*.14;g.scale.setScalar(s);
      g.quaternion.slerp(u.homeQ,.08);
    }
  });

  if(mode==='focus'&&focus){
    const g=focus;
    const off=innerWidth>820?camRight.clone().multiplyScalar(-1.9):camUp.clone().multiplyScalar(.85);
    const target=camera.position.clone().add(camDir.clone().multiplyScalar(5)).add(off);
    const fly=Math.min(1,(now-g.userData.t0)/900),e=ease(fly);
    g.position.lerp(target,.12);
    tmpQ.copy(camera.quaternion);
    tmpQ.multiply(new THREE.Quaternion().setFromEuler(new THREE.Euler(0,(1-e)*Math.PI*2,0)));
    g.quaternion.slerp(tmpQ,.16);
    g.scale.setScalar(1.22);
  }

  if(mode==='spread'){
    camDistT+=(13-camDistT)*.04;
    spreadCards.forEach((g,i)=>{
      const u=g.userData;
      const off=camRight.clone().multiplyScalar((i-1)*2.1*(innerWidth>640?1:.62)).add(camUp.clone().multiplyScalar(innerWidth>640?0:.7));
      const target=camera.position.clone().add(camDir.clone().multiplyScalar(6)).add(off);
      const fly=Math.min(1,(now-(u.spread.t0??(u.spread.t0=now+350*i)))/900);
      if(now>u.spread.t0){const e=ease(fly);
        g.position.lerp(target,.10);
        tmpQ.copy(camera.quaternion).multiply(new THREE.Quaternion().setFromEuler(new THREE.Euler(0,u.spread.flipped?0:Math.PI,0)));
        g.quaternion.slerp(tmpQ,.14);
        g.scale.setScalar(1.15);
      }
    });
  }

  if(hover&&mode==='orbit'){
    ring.position.copy(hover.position);ring.position.y-=.62;
    ring.rotation.z=galaxy.rotation.y*-1;
    ring.scale.setScalar(hover.scale.x);
  }
  ring.material.opacity+=(((mode==='orbit'&&hover)?.5:0)-ring.material.opacity)*.18;

  composer.render();
}
requestAnimationFrame(frame);

let hinted=false;
function hideHint(){if(hinted||mode!=='orbit')return;hinted=true;setTimeout(()=>document.getElementById('hint').classList.add('gone'),2500)}

addEventListener('resize',()=>{
  camera.aspect=innerWidth/innerHeight;camera.updateProjectionMatrix();
  renderer.setSize(innerWidth,innerHeight);composer.setSize(innerWidth,innerHeight);
});
const initialCard=new URLSearchParams(location.search).get('carte');
if(initialCard){const initial=cards.find(g=>g.userData.lame.id===initialCard);if(initial)setTimeout(()=>openFocus(initial),900)}
</script>
</body>
</html>
