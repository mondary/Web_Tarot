#!/usr/bin/env python3
"""tester-server.py — Tester de versions V2/V3/V4/V5.

Extrait la version demandée depuis la branche git `archive/legacy`
dans ~/tarot-testers/<version> et la lance sur un port local (php -S).
Ouvre le navigateur. Zéro manipulation git côté utilisateur.
"""
import http.server
import json
import os
import signal
import socket
import subprocess
import urllib.parse
import webbrowser
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
INSTALL_ROOT = Path.home() / 'tarot-testers'
UI_PORT_START = 9900
APP_PORT_START = 8765

VERSIONS = [
    {'ver': 'v2', 'name': 'V2', 'desc': 'HTML/JS autonome', 'static': True, 'branch': 'archive/legacy', 'src': 'website/v2'},
    {'ver': 'v3', 'name': 'V3', 'desc': 'PHP + tarot.sqlite relationnel', 'static': False, 'branch': 'archive/legacy', 'src': 'website/v3'},
    {'ver': 'v4', 'name': 'V4', 'desc': 'Coffre fort (sql.js + tarot.sqlite)', 'static': True, 'branch': 'archive/legacy', 'src': 'website/v4'},
    {'ver': 'v5', 'name': 'V5', 'desc': 'Vault SQLite côté serveur', 'static': False, 'branch': 'v5', 'src': 'src/website/v5'},
    {'ver': 'v6', 'name': 'V6', 'desc': 'Portraits des 78 lames', 'static': False, 'branch': 'v6', 'src': 'src/website/v6'},
    {'ver': 'v7', 'name': 'V7', 'desc': 'Essences des 78 lames', 'static': False, 'branch': 'v7', 'src': 'src/website/v7'},
    {'ver': 'v8', 'name': 'V8', 'desc': 'Refonte éditoriale', 'static': False, 'branch': 'v8', 'src': 'src/website/v8'},
]

running = {}

PAGE = """<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tarot — Tester de versions</title>
<style>
:root{--bg:#0a0907;--panel:#14120e;--line:#2a2620;--fg:#f1ede4;--muted:#8a8174;--ac:#c9a227}
*{box-sizing:border-box}
body{margin:0;padding:2rem 1.2rem;background:var(--bg);color:var(--fg);font-family:Georgia,'Times New Roman',serif}
h1{font-size:1.6rem;font-weight:400;letter-spacing:.04em;margin:0 0 .2rem}
h1 em{font-style:italic;color:var(--ac)}
.sub{color:var(--muted);font-family:monospace;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;margin:0 0 1.6rem}
.list{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1rem;max-width:900px}
.card{background:var(--panel);border:1px solid var(--line);border-radius:.8rem;padding:1.1rem 1.2rem;display:flex;flex-direction:column;gap:.6rem}
.card .row{display:flex;align-items:baseline;justify-content:space-between;gap:.6rem}
.card .ver{font-size:1.25rem}.card .ver b{color:var(--ac);font-weight:400;font-style:italic}
.card .desc{color:var(--muted);font-size:.85rem;line-height:1.4}
.card .status{font-family:monospace;font-size:.62rem;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
.card .status.on{color:#7fbf7f}
.btns{display:flex;gap:.5rem}
button{flex:1;padding:.6rem .9rem;border-radius:.5rem;border:1px solid var(--ac);background:transparent;color:var(--ac);font-family:monospace;font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;transition:background .2s,color .2s}
button:hover{background:rgba(201,162,39,.15)}
button.stop{border-color:#8a4a3a;color:#c47a5e}
button.stop:hover{background:rgba(196,122,94,.12)}
button:disabled{opacity:.35;cursor:default;pointer-events:none}
a.url{color:var(--ac);font-family:monospace;font-size:.72rem;word-break:break-all}
.msg{margin-top:1.4rem;color:var(--muted);font-size:.8rem;line-height:1.5}
</style>
</head>
<body>
<h1>Tarot <em>Divinatoire</em> — versions à tester</h1>
<p class="sub">Chaque version est installée à la demande depuis la branche archive/legacy</p>
<div class="list" id="list"></div>
<p class="msg" id="msg"></p>
<script>
const list=document.getElementById('list'),msg=document.getElementById('msg');
function show(m){msg.textContent=m;}
async function api(path,method){
  const r=await fetch(path,{method:method||'GET'});
  return r.json();
}
function card(v){
  const c=document.createElement('div');c.className='card';
  c.innerHTML=`<div class="row"><span class="ver"><b>${v.name}</b> ${v.ver}</span><span class="status ${v.running?'on':''}" id="st${v.ver}">${v.running?'● en cours':(v.installed?'installée':'disponible')}</span></div>
  <div class="desc">${v.desc}</div>
  <div class="btns">
    <button id="t${v.ver}">Tester</button>
    <button class="stop" id="s${v.ver}">Stop</button>
  </div>
  ${v.url?`<a class="url" href="${v.url}" target="_blank">${v.url}</a>`:''}`;
  c.querySelector('#t'+v.ver).onclick=async()=>{const b=c.querySelector('#t'+v.ver);b.disabled=true;show('Installation et lancement de '+v.ver+'…');try{const r=await api('/api/test?ver='+v.ver,'POST');show(r.ok?'OK — '+r.url:(r.error||'erreur'));}finally{b.disabled=false;refresh();}};
  c.querySelector('#s'+v.ver).onclick=async()=>{await api('/api/stop?ver='+v.ver,'POST');show(v.ver+' arrêtée.');refresh();};
  return c;
}
async function refresh(){
  const st=await api('/api/state');
  list.replaceChildren(...st.map(card));
}
refresh();setInterval(refresh,2000);
</script>
</body>
</html>
"""


def is_installed(ver):
    d = INSTALL_ROOT / ver
    return d.is_dir() and ((d / 'index.html').is_file() or (d / 'index.php').is_file())


def ensure_installed(ver):
    d = INSTALL_ROOT / ver
    if is_installed(ver):
        return d
    d.mkdir(parents=True, exist_ok=True)
    meta = next(v for v in VERSIONS if v['ver'] == ver)
    src = meta['src']
    with subprocess.Popen(['git', 'archive', meta['branch'], src], cwd=ROOT, stdout=subprocess.PIPE) as p, \
         subprocess.Popen(['tar', '-x', '-C', str(d), '--strip-components=' + str(len(Path(src).parts))], stdin=p.stdout) as t:
        p.stdout.close()
        t.wait()
        p.wait()
    if not is_installed(ver):
        raise RuntimeError(f"Extraction impossible depuis la branche {meta['branch']}")
    return d


def free_port(start):
    port = start
    while True:
        s = socket.socket()
        s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        try:
            s.bind(('127.0.0.1', port))
            s.close()
            return port
        except OSError:
            port += 1


def launch(ver):
    if ver in running:
        return running[ver]
    d = ensure_installed(ver)
    port = free_port(APP_PORT_START)
    meta = next(v for v in VERSIONS if v['ver'] == ver)
    cmd = ['php', '-n', '-d', 'auto_prepend_file=', '-S', f'127.0.0.1:{port}', '-t', '.']
    if not meta['static']:
        cmd.append('index.php')
    p = subprocess.Popen(cmd, cwd=str(d), start_new_session=True,
                         stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    running[ver] = {'pid': p.pid, 'port': port}
    webbrowser.open(f'http://127.0.0.1:{port}/')
    return running[ver]


def stop(ver):
    r = running.pop(ver, None)
    if r:
        try:
            os.killpg(r['pid'], signal.SIGTERM)
        except ProcessLookupError:
            pass


def state():
    out = []
    for v in VERSIONS:
        r = running.get(v['ver'])
        out.append({
            'ver': v['ver'], 'name': v['name'], 'desc': v['desc'],
            'installed': is_installed(v['ver']),
            'running': bool(r),
            'url': f"http://127.0.0.1:{r['port']}/" if r else None,
        })
    return out


class Handler(http.server.BaseHTTPRequestHandler):
    def _send(self, code, body, ctype='application/json'):
        data = body.encode() if isinstance(body, str) else body
        self.send_response(code)
        self.send_header('Content-Type', ctype)
        self.send_header('Content-Length', str(len(data)))
        self.end_headers()
        self.wfile.write(data)

    def do_GET(self):
        u = urllib.parse.urlparse(self.path)
        if u.path == '/':
            self._send(200, PAGE, 'text/html; charset=utf-8')
        elif u.path == '/api/state':
            self._send(200, json.dumps(state()))
        else:
            self._send(404, json.dumps({'error': 'not found'}))

    def do_POST(self):
        u = urllib.parse.urlparse(self.path)
        ver = urllib.parse.parse_qs(u.query).get('ver', [''])[0]
        if u.path == '/api/test' and ver in [v['ver'] for v in VERSIONS]:
            try:
                r = launch(ver)
                self._send(200, json.dumps({'ok': True, 'url': f"http://127.0.0.1:{r['port']}/"}))
            except Exception as e:
                self._send(500, json.dumps({'error': str(e)}))
            return
        if u.path == '/api/stop' and ver:
            stop(ver)
            self._send(200, json.dumps({'ok': True}))
            return
        self._send(404, json.dumps({'error': 'not found'}))

    def log_message(self, *a):
        pass


if __name__ == '__main__':
    port = free_port(UI_PORT_START)
    webbrowser.open(f'http://127.0.0.1:{port}/')
    print(f'  Tester : http://127.0.0.1:{port}/  (ferme cette fenêtre pour arrêter)')
    httpd = http.server.ThreadingHTTPServer(('127.0.0.1', port), Handler)
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        for v in VERSIONS:
            stop(v['ver'])
