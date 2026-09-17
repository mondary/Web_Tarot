//! Tarot Divinatoire — v12, portage Rust → WASM de la v9.
//! Toute la logique applicative tourne dans le WASM ; le DOM est manipulé
//! via web-sys. 100 % statique et offline : les données viennent de
//! data/app-data.json (exporté du vault par tarot-exporter).

use serde::Deserialize;
use std::cell::{Cell, RefCell};
use std::collections::HashMap;
use std::rc::Rc;
use wasm_bindgen::prelude::*;
use wasm_bindgen::JsCast;
use web_sys::{Document, Element, Event, HtmlElement, TouchEvent, Window};

const VER: &str = "2026.09.17";
const THEMES: [(&str, &str, &str); 3] = [
    ("", "Nuit", "#0a0907"),
    ("ivoire", "Ivoire", "#efe9dc"),
    ("sylve", "Sylve", "#0a0f0b"),
];
const DECKS: [(&str, &str); 3] = [("rws", "RWS"), ("clm", "CLM"), ("marseille", "Marseille")];
const DOMAINS: [(&str, &str); 4] = [
    ("amour", "Amour"),
    ("travail", "Travail"),
    ("finance", "Finances"),
    ("guidance", "Guidance"),
];
const BASELINES: [&str; 5] = [
    "Entrez dans la lumière",
    "Ce que vous cherchez vous cherche",
    "Laissez la lumière parler",
    "La réponse est déjà en vous",
    "Éclairez ce qui est voilé",
];
const AUTO_SPEEDS: [f64; 3] = [5.0, 8.0, 12.0];
const PICTOS: [(&str, &str); 6] = [
    ("amour", "<svg viewBox=\"0 0 24 24\"><path d=\"M20.8 4.8a5.5 5.5 0 0 0-7.8 0L12 5.9l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.4a5.5 5.5 0 0 0-.1-7.8Z\"/></svg>"),
    ("travail", "<svg viewBox=\"0 0 24 24\"><rect x=\"3\" y=\"7\" width=\"18\" height=\"13\" rx=\"1\"/><path d=\"M8 7V4h8v3M3 12h18M10 12v2h4v-2\"/></svg>"),
    ("finance", "<svg viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"12\" r=\"8.5\"/><path d=\"M14.5 9.5c-.5-.7-1.4-1.1-2.5-1.1-1.5 0-2.5.8-2.5 1.9 0 2.9 5 1.3 5 4.1 0 1.1-1 1.9-2.5 1.9-1.1 0-2.1-.4-2.7-1.2M12 6.8v10.4\"/></svg>"),
    ("guidance", "<svg viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"12\" r=\"8.5\"/><path d=\"m15.8 8.2-2.2 5.4-5.4 2.2 2.2-5.4 5.4-2.2Z\"/></svg>"),
    ("signification", "<svg viewBox=\"0 0 24 24\"><path d=\"m12 3 1.7 5.3H19l-4.3 3.1 1.7 5.3-4.4-3.2-4.4 3.2 1.7-5.3L5 8.3h5.3L12 3Z\"/></svg>"),
    ("description", "<svg viewBox=\"0 0 24 24\"><path d=\"M3 5.5c3.7-1.4 6.8-.8 9 1.2 2.2-2 5.3-2.6 9-1.2v13c-3.7-1.4-6.8-.8-9 1.2-2.2-2-5.3-2.6-9-1.2v-13Z\"/><path d=\"M12 6.7v13\"/></svg>"),
];
const NUANCES_JSON: &str = r#"[
{"e":"🚶","t":"Partir / changer / aller ailleurs","i":[
 {"id":"e_06_Six","c":"⚔️ 6 Épées","k":"TRANSITION","d":"je quitte une difficulté pour aller vers plus calme."},
 {"id":"c_08_Huit","c":"🏆 8 Coupes","k":"RENONCEMENT","d":"je quitte volontairement quelque chose qui ne me satisfait plus."},
 {"id":"b_03_Trois","c":"🪾 3 Bâtons","k":"EXPANSION","d":"je m'ouvre à de nouveaux horizons."},
 {"id":"a_10_Roue_de_Fortune","c":"🎡 Roue","k":"CHANGEMENT","d":"les circonstances changent, indépendamment de moi."},
 {"id":"a_13_Mort","c":"💀 Mort","k":"FIN","d":"quelque chose doit réellement se terminer pour laisser place à autre chose."}]},
{"e":"🛡️","t":"Difficulté / lutte / tenir","i":[
 {"id":"b_05_Cinq","c":"🪾 5 Bâtons","k":"COMPÉTITION","d":"plusieurs volontés s'affrontent."},
 {"id":"b_07_Sept","c":"🪾 7 Bâtons","k":"DÉFENSE","d":"ma position est attaquée, je la défends."},
 {"id":"b_09_Neuf","c":"🪾 9 Bâtons","k":"RÉSISTANCE","d":"j'ai déjà pris des coups, mais je tiens."},
 {"id":"a_08_Force","c":"🦁 Force","k":"MAÎTRISE","d":"je domine une difficulté sans brutalité."},
 {"id":"e_05_Cinq","c":"⚔️ 5 Épées","k":"VICTOIRE AMÈRE","d":"je gagne le conflit mais j'y laisse quelque chose."}]},
{"e":"😣","t":"Souffrance / difficulté","i":[
 {"id":"e_08_Huit","c":"⚔️ 8 Épées","k":"ENFERMEMENT","d":"je me crois sans issue."},
 {"id":"e_09_Neuf","c":"⚔️ 9 Épées","k":"ANGOISSE","d":"je me torture avec mes pensées."},
 {"id":"e_10_Dix","c":"⚔️ 10 Épées","k":"FOND","d":"le pire est arrivé."},
 {"id":"d_05_Cinq","c":"🪙 5 Deniers","k":"MANQUE","d":"je suis dans le besoin et me sens laissé dehors."},
 {"id":"b_10_Dix","c":"🪾 10 Bâtons","k":"SURCHARGE","d":"j'en porte tellement que je m'épuise."},
 {"id":"c_05_Cinq","c":"🏆 5 Coupes","k":"REGRET","d":"je souffre de ce que j'ai perdu."}]},
{"e":"🎉","t":"Bonheur / réussite / accomplissement","i":[
 {"id":"c_03_Trois","c":"🏆 3 Coupes","k":"AMITIÉ","d":"je profite d'être avec mes proches."},
 {"id":"b_04_Quatre","c":"🪾 4 Bâtons","k":"JALON","d":"une étape est franchie."},
 {"id":"b_06_Six","c":"🪾 6 Bâtons","k":"RECONNAISSANCE","d":"ma réussite est reconnue par les autres."},
 {"id":"c_09_Neuf","c":"🏆 9 Coupes","k":"SATISFACTION","d":"j'ai obtenu ce que je désirais."},
 {"id":"c_10_Dix","c":"🏆 10 Coupes","k":"BONHEUR PARTAGÉ","d":"nous sommes heureux ensemble."},
 {"id":"d_09_Neuf","c":"🪙 9 Deniers","k":"INDÉPENDANCE","d":"je profite personnellement de mes acquis."},
 {"id":"d_10_Dix","c":"🪙 10 Deniers","k":"HÉRITAGE","d":"ma réussite devient durable et transmissible."},
 {"id":"a_19_Soleil","c":"☀️ Soleil","k":"CLARTÉ","d":"tout est ouvert, lumineux, évident."},
 {"id":"a_21_Monde","c":"🌍 Monde","k":"ACCOMPLISSEMENT","d":"le parcours est arrivé à complétude."}]},
{"e":"👁️","t":"Comprendre / voir / savoir","i":[
 {"id":"e_01_As","c":"⚔️ As Épées","k":"RÉVÉLATION","d":"je comprends soudainement."},
 {"id":"e_13_Reine","c":"⚔️ Reine Épées","k":"LUCIDITÉ","d":"je vois la situation telle qu'elle est."},
 {"id":"e_14_Roi","c":"⚔️ Roi Épées","k":"JUGEMENT","d":"je tranche à partir de ce que je sais."},
 {"id":"a_02_Papesse","c":"📖 Papesse","k":"SAVOIR CACHÉ","d":"quelque chose est là mais n'est pas encore révélé."},
 {"id":"a_18_Lune","c":"🌕 Lune","k":"CONFUSION","d":"je ne sais pas distinguer clairement ce qui est réel."},
 {"id":"a_19_Soleil","c":"☀️ Soleil","k":"CLARTÉ","d":"tout est visible, il n'y a plus d'ambiguïté."},
 {"id":"a_09_Hermite","c":"🕯️ Hermite","k":"RECHERCHE","d":"je cherche moi-même la réponse."}]},
{"e":"💞","t":"Lien / relation aux autres","i":[
 {"id":"c_02_Deux","c":"🏆 2 Coupes","k":"RÉCIPROCITÉ","d":"toi et moi échangeons quelque chose mutuellement."},
 {"id":"a_06_Amoureux","c":"❤️ Amoureux","k":"UNION","d":"deux êtres s'unissent."},
 {"id":"c_03_Trois","c":"🏆 3 Coupes","k":"AMITIÉ","d":"j'appartiens à un cercle affectif."},
 {"id":"c_10_Dix","c":"🏆 10 Coupes","k":"BONHEUR PARTAGÉ","d":"le lien devient foyer/bonheur collectif."},
 {"id":"d_03_Trois","c":"🪙 3 Deniers","k":"COLLABORATION","d":"nous réunissons nos compétences."},
 {"id":"d_06_Six","c":"🪙 6 Deniers","k":"AIDE","d":"l'un donne ce dont l'autre a besoin."}]},
{"e":"🧱","t":"Construire / avoir / sécuriser","i":[
 {"id":"d_01_As","c":"🪙 As Deniers","k":"OPPORTUNITÉ","d":"une possibilité concrète apparaît."},
 {"id":"d_04_Quatre","c":"🪙 4 Deniers","k":"RÉTENTION","d":"je m'accroche à ce que j'ai."},
 {"id":"d_07_Sept","c":"🪙 7 Deniers","k":"PATIENCE","d":"j'ai semé, j'attends que ça mûrisse."},
 {"id":"d_08_Huit","c":"🪙 8 Deniers","k":"PERFECTIONNEMENT","d":"je développe mon savoir-faire."},
 {"id":"d_09_Neuf","c":"🪙 9 Deniers","k":"INDÉPENDANCE","d":"je profite personnellement de mes acquis."},
 {"id":"d_10_Dix","c":"🪙 10 Deniers","k":"HÉRITAGE","d":"mes acquis deviennent patrimoine."},
 {"id":"d_13_Reine","c":"🪙 Reine Deniers","k":"ENTRETIEN","d":"je prends soin de mes ressources."},
 {"id":"d_14_Roi","c":"🪙 Roi Deniers","k":"PROSPÉRITÉ","d":"mes ressources sont solidement établies."}]},
{"e":"🔥","t":"Se lancer / vouloir / entreprendre","i":[
 {"id":"b_01_As","c":"🪾 As Bâtons","k":"IMPULSION","d":"l'envie surgit."},
 {"id":"b_11_Valet","c":"🪾 Valet Bâtons","k":"CURIOSITÉ","d":"ça m'intéresse, je veux découvrir."},
 {"id":"b_12_Cavalier","c":"🪾 Cavalier Bâtons","k":"AVENTURE","d":"je veux le vivre, j'y vais."},
 {"id":"b_02_Deux","c":"🪾 2 Bâtons","k":"PLANIFICATION","d":"j'envisage ce que je pourrais faire."},
 {"id":"b_03_Trois","c":"🪾 3 Bâtons","k":"EXPANSION","d":"je veux aller plus loin."},
 {"id":"b_13_Reine","c":"🪾 Reine Bâtons","k":"CHARISME","d":"je sais qui je suis et ça se voit."},
 {"id":"b_14_Roi","c":"🪾 Roi Bâtons","k":"LEADERSHIP","d":"je veux accomplir et j'embarque les autres."},
 {"id":"a_07_Chariot","c":"🛒 Chariot","k":"CONQUÊTE","d":"je prends les rênes et avance vers mon objectif."}]}
]"#;

#[derive(Clone, Deserialize)]
struct Card {
    id: String,
    fam: String,
    name: String,
    num: f64,
    sort: usize,
    #[serde(default)]
    html: String,
    #[serde(default)]
    keywords_up: Option<String>,
    #[serde(default)]
    keywords_down: Option<String>,
    #[serde(default)]
    cod: Option<String>,
}

#[derive(Clone, Deserialize)]
struct Family {
    key: String,
    name: String,
    short: String,
    #[serde(default)]
    el: Option<String>,
    #[serde(default)]
    sym: Option<String>,
    #[serde(default)]
    ac: Option<String>,
}

#[derive(Clone, Deserialize)]
struct Es {
    #[serde(default)]
    rep: Option<String>,
    #[serde(default)]
    aff: Option<String>,
}

#[derive(Deserialize)]
struct AppData {
    cards: Vec<Card>,
    families: Vec<Family>,
    #[serde(default)]
    es: HashMap<String, Es>,
}

#[derive(Clone, Deserialize)]
struct Assoc {
    #[serde(default)]
    pair: Option<String>,
    #[serde(default)]
    descr: Option<String>,
    #[serde(default)]
    section: Option<String>,
}

#[derive(Clone, Deserialize)]
struct NuanceItem {
    id: String,
    c: String,
    k: String,
    d: String,
}
#[derive(Deserialize)]
struct NuanceCat {
    e: String,
    t: String,
    i: Vec<NuanceItem>,
}

struct Portrait {
    key: String,
    idee: String,
    realite: String,
}
fn parse_portrait(md: &str) -> Portrait {
    let mut p = Portrait { key: String::new(), idee: String::new(), realite: String::new() };
    for line in md.lines() {
        let l = line.trim();
        if l.is_empty() { continue; }
        if let Some(rest) = l.strip_prefix('🧠') {
            p.idee = rest
                .trim()
                .trim_start_matches(|c| c == ' ')
                .trim_start_matches("Idée centrale :")
                .trim_start_matches("Idée centrale : ")
                .trim()
                .to_string();
        } else if let Some(rest) = l.strip_prefix('💭') {
            p.realite = rest
                .trim()
                .trim_start_matches("Ce qui se passe réellement :")
                .trim()
                .to_string();
        } else if let Some(rest) = l.strip_prefix('🔑') {
            p.key = rest
                .trim()
                .trim_start_matches("Mot-clé distinctif :")
                .trim_start_matches("Mot-clé :")
                .trim()
                .to_string();
        }
    }
    p
}

struct LearnItem {
    card_id: usize, // index dans cards
    kw: String,
    phrase: String,
    lvl: i32,
}
impl Clone for LearnItem {
    fn clone(&self) -> Self {
        LearnItem { card_id: self.card_id, kw: self.kw.clone(), phrase: self.phrase.clone(), lvl: self.lvl }
    }
}
struct Learn {
    queue: Vec<LearnItem>,
    total: usize,
    hits: u32,
    missed: HashMap<String, (usize, String)>, // id -> (card idx, answer)
    seen: HashMap<String, bool>,
    cur: Option<LearnItem>,
    cur_opts: Vec<LearnItem>,
    answered: bool,
}

struct State {
    cards: Vec<Card>,
    fams: Vec<Family>,
    es: HashMap<String, Es>,
    portraits: HashMap<String, String>,
    nuances: Vec<NuanceCat>,
    assocs: RefCell<HashMap<String, Vec<Assoc>>>,
    cur: Cell<i32>,
    deck: RefCell<String>,
    theme: Cell<usize>,
    kw_on: Cell<bool>,
    s_fam: RefCell<String>,
    s_q: RefCell<String>,
    s_sel: Cell<usize>,
    dur: Cell<usize>, // index dans AUTO_SPEEDS
    learn_mode: RefCell<String>,
    learn: RefCell<Option<Learn>>,
    booted: Cell<bool>,
}

thread_local! {
    static APP: RefCell<Option<Rc<State>>> = RefCell::new(None);
    static KEEP: RefCell<Vec<Closure<dyn FnMut()>>> = RefCell::new(Vec::new());
    static KEEP_EVT: RefCell<Vec<Closure<dyn FnMut(Event)>>> = RefCell::new(Vec::new());
    static KEEP_TOUCH: RefCell<Vec<Closure<dyn FnMut(TouchEvent)>>> = RefCell::new(Vec::new());
    static KEEP_TMPS: RefCell<Vec<Closure<dyn FnMut()>>> = RefCell::new(Vec::new());
    static AUTO_CLOSURE: RefCell<Option<Closure<dyn FnMut()>>> = const { RefCell::new(None) };
    static AUTO_ID: Cell<i32> = const { Cell::new(0) };
    static AUTO_TICK: Cell<f64> = const { Cell::new(0.0) };
    static TOUCH_X: Cell<f64> = const { Cell::new(0.0) };
    static TOUCH_Y: Cell<f64> = const { Cell::new(0.0) };
    static TOUCH_T: Cell<f64> = const { Cell::new(0.0) };
}

fn win() -> Window { web_sys::window().expect("window") }
fn doc() -> Document { win().document().expect("document") }
fn el(id: &str) -> Option<Element> { doc().get_element_by_id(id) }
fn stl(e: &Element) -> web_sys::CssStyleDeclaration {
    e.clone().unchecked_into::<HtmlElement>().style()
}
fn cls_toggle(e: &Element, cls: &str, on: bool) {
    if on { e.class_list().add_1(cls).ok(); } else { e.class_list().remove_1(cls).ok(); }
}
fn children(e: &Element) -> Vec<Element> {
    let mut out = Vec::new();
    let mut cur = e.first_element_child();
    while let Some(c) = cur {
        out.push(c.clone());
        cur = c.next_element_sibling();
    }
    out
}

fn esc(s: &str) -> String {
    s.replace('&', "&amp;").replace('<', "&lt;").replace('>', "&gt;")
        .replace('"', "&quot;").replace('\'', "&#39;")
}

fn store_get(k: &str) -> Option<String> {
    win().local_storage().ok().flatten().and_then(|s| s.get_item(k).ok().flatten())
}
fn store_set(k: &str, v: &str) {
    if let Some(s) = win().local_storage().ok().flatten() {
        let _ = s.set_item(k, v);
    }
}

fn pic(kw: &str) -> &'static str {
    PICTOS.iter().find(|(k, _)| *k == kw).map(|(_, v)| *v).unwrap_or("")
}

fn split_kw(s: &str) -> Vec<String> {
    s.split(',').map(|x| x.trim().to_string()).filter(|x| !x.is_empty()).collect()
}

impl State {
    fn fam(&self, key: &str) -> Option<&Family> { self.fams.iter().find(|f| f.key == key) }
    fn card_by_sort(&self, sort: usize) -> Option<&Card> { self.cards.iter().find(|c| c.sort == sort) }
    fn card_by_id(&self, id: &str) -> Option<(usize, &Card)> {
        self.cards.iter().enumerate().find(|(_, c)| c.id == id)
    }

    fn deck_url(&self, id: &str) -> String {
        match self.deck.borrow().as_str() {
            "rws" | "" => format!("img/{}.jpg", id),
            d => format!("decks/{}/{}.jpg", d, id),
        }
    }

    fn kw_of(&self, id: &str) -> String { parse_portrait(self.portraits.get(id).map(|s| s.as_str()).unwrap_or("")).key }

    fn sync_url(&self) {
        if !self.booted.get() { return; }
        let mut q: Vec<String> = Vec::new();
        let cur = self.cur.get();
        if cur >= 0 {
            if let Some(c) = self.cards.get(cur as usize) { q.push(format!("carte={}", c.id)); }
        }
        let deck = self.deck.borrow().clone();
        if deck != "rws" && !deck.is_empty() { q.push(format!("deck={}", deck)); }
        let tk = THEMES[self.theme.get()].0;
        if !tk.is_empty() { q.push(format!("theme={}", tk)); }
        if self.kw_on.get() { q.push("kw=1".into()); }
        let url = if q.is_empty() {
            win().location().pathname().unwrap_or_default()
        } else {
            format!("{}?{}", win().location().pathname().unwrap_or_default(), q.join("&"))
        };
        let _ = win().history().map(|h| h.replace_state_with_url(&JsValue::NULL, "", Some(&url)));
    }

    // ---- grille ----
    fn mini(&self, c: &Card) -> String {
        let kw = self.kw_of(&c.id);
        let kw_html = if kw.is_empty() { String::new() } else {
            format!(
                "<span class=\"kw-overlay\"><span class=\"kw\">{}</span><span class=\"kn\">{}</span></span>",
                esc(&kw), esc(&c.name)
            )
        };
        format!(
            "<button type=\"button\" class=\"mini\" data-act=\"detail\" data-sort=\"{}\"><span class=\"ph\"><img src=\"{}\" alt=\"{}\" loading=\"lazy\"></span><span class=\"cap\"><span class=\"nm\">{}</span><span class=\"no\">{:02}</span></span>{}</button>",
            c.sort, esc(&self.deck_url(&c.id)), esc(&c.name), esc(&c.name), c.sort + 1, kw_html
        )
    }

    fn family_intro(&self, f: &Family) -> String {
        let ac = f.ac.clone().unwrap_or_else(|| "#c9a227".into());
        let glyph = if f.key != "majors" {
            format!("<img class=\"family-glyph\" src=\"svg/{}.svg\" alt=\"\">", esc(&f.key))
        } else {
            format!("<span class=\"family-glyph\">{}</span>", esc(f.sym.as_deref().unwrap_or("✦")))
        };
        format!(
            "<button type=\"button\" class=\"mini family-intro\" style=\"--family:{ac}\" data-act=\"open-family\" data-fam=\"{k}\"><span class=\"ph\">{glyph}<span class=\"family-name\">{name}</span><span class=\"family-element\">{el}</span></span><span class=\"cap\"><span class=\"nm\">Famille</span><span class=\"no\">00</span></span></button>",
            ac = esc(&ac), k = esc(&f.key), glyph = glyph, name = esc(&f.name),
            el = esc(f.el.as_deref().unwrap_or(""))
        )
    }

    fn render_grid(&self) {
        let mut h = String::new();
        let mut last = String::new();
        for c in &self.cards {
            if c.fam != last {
                if let Some(f) = self.fam(&c.fam) {
                    let n = self.cards.iter().filter(|x| x.fam == c.fam).count();
                    h.push_str(&format!(
                        "<div class=\"fam-card\"><span class=\"g\">{}</span><span class=\"fn\">{}</span><span class=\"fc\">{} lames</span></div>",
                        esc(f.sym.as_deref().unwrap_or("✦")), esc(&f.name), n
                    ));
                    h.push_str(&self.family_intro(f));
                }
                last = c.fam.clone();
            }
            h.push_str(&self.mini(c));
        }
        if let Some(g) = el("grid") { g.set_inner_html(&h); }
    }

    // ---- recherche ----
    fn search_filter(&self) -> Vec<&Card> {
        let q = self.s_q.borrow().clone();
        let f = self.s_fam.borrow().clone();
        self.cards
            .iter()
            .filter(|c| (f.is_empty() || c.fam == f) && (q.is_empty()
                || c.name.to_lowercase().contains(&q)
                || format!("{:02}", c.num).contains(&q)))
            .collect()
    }

    fn render_chips(&self) {
        let Some(elc) = el("pickerChips2") else { return };
        let cur = self.s_fam.borrow().clone();
        let mut h = format!(
            "<button class=\"chip {}\" data-act=\"set-fam\" data-fam=\"\">Tout<span class=\"n\">{}</span></button>",
            if cur.is_empty() { "active" } else { "" }, self.cards.len()
        );
        for f in &self.fams {
            let n = self.cards.iter().filter(|c| c.fam == f.key).count();
            h.push_str(&format!(
                "<button class=\"chip {}\" data-act=\"set-fam\" data-fam=\"{}\" style=\"--ac:{}\"><span class=\"sym\">{}</span>{}<span class=\"n\">{}</span></button>",
                if cur == f.key { "active" } else { "" }, esc(&f.key), esc(f.ac.as_deref().unwrap_or("#c9a227")),
                esc(f.sym.as_deref().unwrap_or("")), esc(&f.short), n
            ));
        }
        elc.set_inner_html(&h);
    }

    fn search_render(&self) {
        let Some(grid) = el("sGrid") else { return };
        let out = self.search_filter();
        if out.is_empty() {
            grid.set_inner_html("<div class=\"s-empty\">Aucune lame</div>");
            return;
        }
        let sel = self.s_sel.get();
        let mut h = String::new();
        for (i, c) in out.iter().enumerate() {
            let kw = self.kw_of(&c.id);
            let kw_html = if kw.is_empty() { String::new() } else {
                format!("<span class=\"kw-overlay\"><span class=\"kw\">{}</span><span class=\"kn\">{}</span></span>", esc(&kw), esc(&c.name))
            };
            h.push_str(&format!(
                "<div class=\"mini {}\" data-act=\"detail\" data-sort=\"{}\"><div class=\"ph\"><img src=\"{}\"></div><div class=\"cap\"><span class=\"nm\">{}</span><span class=\"no\">{:02}</span></div>{}</div>",
                if i == sel { "sel" } else { "" }, c.sort, esc(&self.deck_url(&c.id)), esc(&c.name), c.sort + 1, kw_html
            ));
        }
        grid.set_inner_html(&h);
    }

    fn sync_query(&self) {
        let Some(q) = el("sQuery") else { return };
        let s = self.s_q.borrow().clone();
        if s.is_empty() {
            q.set_inner_html("<span class=\"ph\">Tapez une lame…</span>");
        } else {
            q.set_inner_html(&format!("<span>{}</span>", esc(&s)));
        }
    }

    fn open_search(&self, initial: &str) {
        if !initial.is_empty() {
            *self.s_q.borrow_mut() = initial.to_lowercase();
            self.s_sel.set(0);
            if let Some(i) = el("sInput") {
                if let Ok(input) = i.dyn_into::<web_sys::HtmlInputElement>() { input.set_value(initial); }
            }
        }
        if let Some(s) = el("search") { s.class_list().add_1("open").ok(); }
        doc().body().unwrap().style().set_property("overflow", "hidden").ok();
        self.sync_query();
        self.render_chips();
        self.search_render();
        if let Some(input) = el("sInput") {
            let _ = input.dyn_into::<web_sys::HtmlInputElement>().map(|i| i.focus());
        }
    }

    fn close_search(&self) {
        if let Some(s) = el("search") { s.class_list().remove_1("open").ok(); }
        doc().body().unwrap().style().set_property("overflow", "").ok();
        if let Some(i) = el("sInput") {
            if let Ok(input) = i.dyn_into::<web_sys::HtmlInputElement>() { input.set_value(""); }
        }
        *self.s_q.borrow_mut() = String::new();
        *self.s_fam.borrow_mut() = String::new();
        self.s_sel.set(0);
        self.sync_query();
        self.render_chips();
    }

    // ---- nuances ----
    fn open_nuances(&self) {
        if let Some(n) = el("nuances") {
            if let Some(body) = n.query_selector(".nuances-body").ok().flatten() {
                if body.inner_html().is_empty() { body.set_inner_html(&self.render_nuances()); }
            }
            n.class_list().add_1("open").ok();
        }
        doc().body().unwrap().style().set_property("overflow", "hidden").ok();
    }
    fn close_nuances(&self) {
        if let Some(n) = el("nuances") { n.class_list().remove_1("open").ok(); }
        doc().body().unwrap().style().set_property("overflow", "").ok();
    }
    fn render_nuances(&self) -> String {
        let mut h = String::new();
        for cat in &self.nuances {
            h.push_str(&format!("<div class=\"nuc-cat\"><h3><span style=\"margin-right:.5rem\">{}</span>{}</h3><ul>", esc(&cat.e), esc(&cat.t)));
            for it in &cat.i {
                h.push_str(&format!(
                    "<li><a class=\"nuc-thumb\" data-act=\"detail-id\" data-id=\"{}\"><img src=\"{}\"></a><div><span class=\"nuc-card\">{}</span> = <span class=\"nuc-key\">{}</span> → <span class=\"nuc-desc\">{}</span></div></li>",
                    esc(&it.id), esc(&self.deck_url(&it.id)), esc(&it.c), esc(&it.k), esc(&it.d)
                ));
            }
            h.push_str("</ul></div>");
        }
        h
    }

    // ---- détail ----
    fn open_detail(&self, sort: usize, dir: i32) {
        let Some(idx) = self.cards.iter().position(|c| c.sort == sort) else { return };
        self.close_search();
        self.close_nuances();
        let was_open = el("detail").map(|d| d.class_list().contains("open")).unwrap_or(false);
        self.cur.set(idx as i32);
        let c = self.cards[idx].clone();
        let f = self.fam(&c.fam).cloned().unwrap_or(Family {
            key: String::new(), name: String::new(), short: String::new(), el: None, sym: None, ac: None,
        });
        let es = self.es.get(&c.id).cloned().unwrap_or(Es { rep: None, aff: None });
        let in_fam: Vec<&Card> = self.cards.iter().filter(|x| x.fam == c.fam).collect();
        let fi = in_fam.iter().position(|x| x.id == c.id).unwrap_or(0);
        let num = format!("{:02}", idx + 1);
        let rep = es.rep.unwrap_or_default().trim().to_uppercase();
        let answer = if ["OUI", "NON", "PEUT-ÊTRE", "PAS ENCORE"].contains(&rep.as_str()) { rep } else { String::new() };
        let p = parse_portrait(self.portraits.get(&c.id).map(|s| s.as_str()).unwrap_or(""));

        if let Some(hero) = el("heroImg") {
            let _ = hero.set_attribute("src", &self.deck_url(&c.id));
            let _ = hero.set_attribute("data-card", &c.id);
        }

        // sections du html (pas de parseur DOM : extraction par <section>…</section> avec h2)
        let sec = |kw: &str| -> Option<(String, String)> {
            let mut rest = c.html.as_str();
            while let Some(pos) = rest.find("<section") {
                let end = rest[pos..].find("</section>")? + pos;
                let block = &rest[pos..end];
                let h2s = block.find("<h2>")? + 4;
                let h2e = block[h2s..].find("</h2>")? + h2s;
                let title = block[h2s..h2e].to_lowercase();
                if title.contains(kw) { return Some((title, block.to_string())); }
                rest = &rest[end + 10..];
            }
            None
        };
        let sec_paras = |block: &str| -> String {
            let mut out = String::new();
            let mut rest = block;
            while let Some(a) = rest.find("<p>") {
                let Some(r) = rest[a..].find("</p>") else { break };
                let b = a + r + 4;
                out.push_str(&rest[a..b]);
                rest = &rest[b..];
            }
            out
        };
        let sec_text = |block: &str| -> String {
            let (Some(a), Some(rb)) = (block.find("<p>"), block.find("</p>")) else { return String::new() };
            block[a + 3..rb].to_string()
        };

        // bandeau identité
        let mut ident = String::from("<section class=\"d-identite\">");
        if !p.idee.is_empty() { ident.push_str(&format!("<p class=\"d-idee\">{}</p>", esc(&p.idee))); }
        if !p.realite.is_empty() { ident.push_str(&format!("<p class=\"d-realite\">{}</p>", esc(&p.realite))); }
        if !p.key.is_empty() || !answer.is_empty() {
            ident.push_str("<div class=\"d-badges\">");
            if !p.key.is_empty() { ident.push_str(&format!("<span class=\"d-key\">{}</span>", esc(&p.key))); }
            if !answer.is_empty() {
                let cls = format!("ans-{}", answer.to_lowercase().replace(' ', "-"));
                ident.push_str(&format!("<span class=\"d-answer {}\">{}</span>", cls, esc(&answer)));
            }
            ident.push_str("</div>");
        }
        let up = split_kw(c.keywords_up.as_deref().unwrap_or(""));
        let dn = split_kw(c.keywords_down.as_deref().unwrap_or(""));
        if !up.is_empty() || !dn.is_empty() {
            ident.push_str("<div class=\"d-keys\">");
            if !up.is_empty() {
                ident.push_str("<span class=\"kl\">Endroit</span><span class=\"kv up\">");
                for k in &up { ident.push_str(&format!("<span class=\"kt\">{}</span>", esc(k))); }
                ident.push_str("</span>");
            }
            if !dn.is_empty() {
                ident.push_str("<span class=\"kl\">Envers</span><span class=\"kv dn\">");
                for k in &dn { ident.push_str(&format!("<span class=\"kt\">{}</span>", esc(k))); }
                ident.push_str("</span>");
            }
            ident.push_str("</div>");
        }
        if let Some(cod) = &c.cod {
            if !cod.is_empty() { ident.push_str(&format!("<div class=\"d-cod\"><span class=\"d-cod-ic\">☀</span><p>{}</p></div>", esc(cod))); }
        }
        ident.push_str("</section>");

        // 4 domaines
        let mut doms = String::from("<div class=\"d-section-label\">Les quatre domaines</div><div class=\"d-domains\">");
        for (kw, label) in DOMAINS {
            if let Some((_, block)) = sec(kw) {
                let body = sec_paras(&block);
                if !body.is_empty() {
                    doms.push_str(&format!("<div class=\"d-domain\"><h3><span class=\"content-icon\">{}</span>{}</h3>{}</div>", pic(kw), label, body));
                }
            }
        }
        doms.push_str("</div>");

        // colonnes signification + description
        let mut cols = String::new();
        let sig = sec("signification");
        let desc = sec("description");
        if sig.is_some() || desc.is_some() {
            cols.push_str("<div class=\"d-section-label\">Repères</div><div class=\"d-cols\">");
            if let Some((_, b)) = &sig {
                cols.push_str(&format!("<div class=\"d-col\"><h3><span class=\"content-icon\">{}</span>Signification</h3>{}</div>", pic("signification"), sec_paras(b)));
            }
            if let Some((_, b)) = &desc {
                cols.push_str(&format!("<div class=\"d-col\"><h3><span class=\"content-icon\">{}</span>Description</h3>{}</div>", pic("description"), sec_paras(b)));
            }
            cols.push_str("</div>");
        }

        // citation
        let mut citation = String::new();
        if let Some((_, b)) = sec("citation") {
            let cit = sec_text(&b).trim().trim_start_matches('«').trim_end_matches('»').trim().to_string();
            if !cit.is_empty() { citation = format!("<div class=\"d-citation\">« {} »</div>", esc(&cit)); }
        }

        // associations tiroir
        let have = self.assocs.borrow().contains_key(&c.id);
        let assocs_body = if have {
            self.render_associations(&c.id)
        } else {
            "<p class=\"assoc-loading\">Chargement…</p>".to_string()
        };
        let assocs_block = format!(
            "<div class=\"d-assocs\" id=\"dAssocs\" data-card=\"{}\"><button class=\"d-assocs-toggle\" data-act=\"toggle-assocs\"><span id=\"assocsCount\">Associations</span><span class=\"arr\">▾</span></button><div class=\"d-assocs-body\">{}</div></div>",
            esc(&c.id), assocs_body
        );

        let mut thumbs = String::new();
        for x in &in_fam {
            thumbs.push_str(&format!(
                "<div class=\"d-thumb{}\" data-act=\"detail\" data-sort=\"{}\"><img src=\"{}\"></div>",
                if x.id == c.id { " current" } else { "" }, x.sort, esc(&self.deck_url(&x.id))
            ));
        }

        let meta = format!(
            "<div class=\"d-meta\"><b>{}</b> / {} <span style=\"opacity:.4\">·</span> {} {}/{}{}</div>",
            num, self.cards.len(), esc(&f.name), fi + 1, in_fam.len(),
            match &f.el { Some(e) if !e.is_empty() => format!(" <span style=\"opacity:.4\">·</span> {}", esc(e)), _ => String::new() }
        );
        let inner = format!(
            "{}<h1 class=\"d-title\"><em>{}</em></h1>{}{}{}{}{}<div class=\"d-thumbs\">{}</div>",
            meta, esc(&c.name), ident, doms, cols, citation, assocs_block, thumbs
        );
        if let Some(d) = el("dInner") { d.set_inner_html(&inner); }

        if have {
            let n = self.assocs.borrow().get(&c.id).map(|v| v.len()).unwrap_or(0);
            if let Some(cnt) = el("assocsCount") {
                cnt.set_text_content(Some(&format!("{} combinaison{}", n, if n > 1 { "s" } else { "" })));
            }
        } else {
            self.load_associations(&c.id);
        }

        let prev = &self.cards[(idx + self.cards.len() - 1) % self.cards.len()];
        let next = &self.cards[(idx + 1) % self.cards.len()];
        if let Some(loopbar) = el("loopBar") {
            loopbar.set_inner_html(&format!(
                "<a data-act=\"detail\" data-sort=\"{}\">← {}</a><span class=\"pos\"><b>{}</b> / {}</span><a data-act=\"detail\" data-sort=\"{}\">{} →</a>",
                prev.sort, esc(&prev.name), num, self.cards.len(), next.sort, esc(&next.name)
            ));
        }
        if let Some(d) = el("detail") {
            let d2 = d.clone();
            d.class_list().add_1("open").ok();
            let _: web_sys::HtmlElement = d.clone().unchecked_into();
            let hd: web_sys::HtmlElement = d.unchecked_into();
            hd.set_scroll_top(0);
            if dir != 0 && was_open {
                d2.class_list().remove_1("slide-next").ok();
                d2.class_list().remove_1("slide-prev").ok();
                let _ = d2.class_list().add_1(if dir > 0 { "slide-next" } else { "slide-prev" });
                let d3 = d2.clone();
                after_ms(340, move || { d3.class_list().remove_1("slide-next").ok(); d3.class_list().remove_1("slide-prev").ok(); });
            }
        }
        let _ = doc().set_title(&format!("{} — Tarot Divinatoire", c.name));
        self.sync_url();
    }

    fn close_detail(&self) {
        if let Some(d) = el("detail") { d.class_list().remove_1("open").ok(); }
        self.cur.set(-1);
        self.stop_auto();
        let _ = doc().set_title("Tarot Divinatoire");
        self.sync_url();
    }

    fn render_associations(&self, id: &str) -> String {
        let rows = self.assocs.borrow().get(id).cloned().unwrap_or_default();
        if rows.is_empty() { return String::new(); }
        let mut h = String::new();
        let mut done: Vec<String> = Vec::new();
        for r in &rows {
            let secname = r.section.clone().unwrap_or_default();
            if done.contains(&secname) { continue; }
            done.push(secname.clone());
            h.push_str(&format!("<div class=\"association-section\"><h3>{}</h3><ul>", esc(&secname)));
            for r2 in rows.iter().filter(|x| x.section.clone().unwrap_or_default() == secname) {
                let pair = r2.pair.clone().unwrap_or_default();
                let target = if pair.contains(" + ") {
                    pair.split(" + ").nth(1).unwrap_or("").trim().to_string()
                } else { pair.trim().to_string() };
                let card = self.assoc_card(&target);
                let (thumb, link) = match &card {
                    Some((_, cc)) => (
                        format!("<a class=\"assoc-thumb\" data-act=\"detail\" data-sort=\"{}\"><img src=\"{}\"></a>", cc.sort, esc(&self.deck_url(&cc.id))),
                        format!("<a class=\"assoc-link\" data-act=\"detail\" data-sort=\"{}\">{}</a>", cc.sort, esc(&target)),
                    ),
                    None => (String::new(), format!("<span class=\"assoc-link\">{}</span>", esc(&target))),
                };
                h.push_str(&format!(
                    "<li>{}<div class=\"assoc-text\">{}<p>{}</p></div></li>",
                    thumb, link, esc(r2.descr.as_deref().unwrap_or(""))
                ));
            }
            h.push_str("</ul></div>");
        }
        h
    }

    fn assoc_card(&self, t: &str) -> Option<(usize, &Card)> {
        if t.is_empty() { return None; }
        let mut cands = vec![t.to_string()];
        if t.contains('/') {
            cands.extend(t.split('/').map(|s| s.trim().to_string()));
        }
        for cand in cands {
            let n = cand.to_lowercase();
            for (i, c) in self.cards.iter().enumerate() {
                if c.name.to_lowercase() == n { return Some((i, c)); }
            }
        }
        None
    }

    fn load_associations(&self, id: &str) {
        let id = id.to_string();
        wasm_bindgen_futures::spawn_local(async move {
            let json = match fetch_json(&format!("assocs/{}.json", id)).await {
                Ok(v) => v,
                Err(_) => {
                    if el("dAssocs").map(|b| b.get_attribute("data-card") == Some(id.clone())).unwrap_or(false) {
                        if let Some(body) = el("dAssocs").and_then(|b| b.query_selector(".d-assocs-body").ok().flatten()) {
                            body.set_inner_html("<p class=\"assoc-loading\">Associations indisponibles.</p>");
                        }
                    }
                    return;
                }
            };
            let rows: Vec<Assoc> = serde_json::from_str(&js_str(&json)).unwrap_or_default();
            let n = rows.len();
            APP.with(|a| {
                if let Some(app) = a.borrow().as_ref() {
                    app.assocs.borrow_mut().insert(id.clone(), rows);
                    if el("dAssocs").map(|b| b.get_attribute("data-card") == Some(id.clone())).unwrap_or(false) {
                        if let Some(body) = el("dAssocs").and_then(|b| b.query_selector(".d-assocs-body").ok().flatten()) {
                            let html = app.render_associations(&id);
                            body.set_inner_html(if html.is_empty() { "<p class=\"assoc-loading\">Aucune association.</p>" } else { &html });
                        }
                        if let Some(cnt) = el("assocsCount") {
                            cnt.set_text_content(Some(&format!("{} combinaison{}", n, if n > 1 { "s" } else { "" })));
                        }
                    }
                }
            });
        });
    }

    // ---- réglages ----
    fn apply_theme(&self, idx: usize, save: bool) {
        let (k, l, c) = THEMES[idx];
        doc().document_element().unwrap().set_attribute("data-theme", k).ok();
        if let Some(lbl) = el("themeLbl") { lbl.set_text_content(Some(l)); }
        if let Some(m) = doc().query_selector("meta[name=theme-color]").ok().flatten() {
            m.set_attribute("content", c).ok();
        }
        self.theme.set(idx);
        if save { store_set("tarotTheme", k); self.sync_url(); }
    }
    fn cycle_theme(&self) { self.apply_theme((self.theme.get() + 1) % THEMES.len(), true); }

    fn apply_deck(&self) {
        let d = self.deck.borrow().clone();
        let l = DECKS.iter().find(|(k, _)| *k == d).map(|(_, l)| *l).unwrap_or("RWS");
        if let Some(lbl) = el("deckLbl") { lbl.set_text_content(Some(l)); }
        self.render_grid();
        let cur = self.cur.get();
        if cur >= 0 {
            if let Some(c) = self.cards.get(cur as usize) { self.open_detail(c.sort, 0); }
        }
    }
    fn cycle_deck(&self) {
        let cur = self.deck.borrow().clone();
        let i = DECKS.iter().position(|(k, _)| *k == cur).unwrap_or(0);
        let next = DECKS[(i + 1) % DECKS.len()].0.to_string();
        *self.deck.borrow_mut() = next.clone();
        store_set("tarotDeck", &next);
        self.apply_deck();
        self.sync_url();
    }

    fn toggle_kw(&self) {
        let on = !self.kw_on.get();
        self.kw_on.set(on);
        doc().body().unwrap().class_list().toggle_with_force("kw", on).ok();
        if let Some(b) = el("kwFab") {
            let _ = b.set_attribute("aria-pressed", if on { "true" } else { "false" });
            b.class_list().toggle_with_force("on", on).ok();
        }
        store_set("tarotKw", if on { "1" } else { "0" });
        self.sync_url();
    }

    // ---- diaporama ----
    fn auto_ring(&self, p: f64) {
        if let Some(root) = doc().document_element() { stl(&root).set_property("--auto-p", &format!("{:.1}%", p * 100.0)).ok(); }
    }
    fn start_auto(&self) {
        AUTO_TICK.set(0.0);
        if self.cur.get() < 0 {
            if let Some(c) = self.cards.first() { self.open_detail(c.sort, 1); }
        }
        doc().body().unwrap().class_list().add_1("auto-running").ok();
        if let Some(b) = el("autoFab") {
            b.class_list().add_1("on").ok();
            let _ = b.set_attribute("aria-pressed", "true");
        }
        if let Some(r) = el("autoRing") { r.class_list().add_1("on").ok(); }
        AUTO_ID.set(spawn_interval(120, || APP.with(|a| {
            if let Some(app) = a.borrow().as_ref() { app.auto_tick(); }
        })));
    }
    fn stop_auto(&self) {
        let id = AUTO_ID.get();
        if id != 0 { win().clear_interval_with_handle(id); AUTO_ID.set(0); }
        AUTO_TICK.set(0.0);
        doc().body().unwrap().class_list().remove_1("auto-running").ok();
        if let Some(b) = el("autoFab") {
            b.class_list().remove_1("on").ok();
            let _ = b.set_attribute("aria-pressed", "false");
        }
        if let Some(r) = el("autoRing") { r.class_list().remove_1("on").ok(); }
        self.auto_ring(0.0);
    }
    fn auto_tick(&self) {
        let tick = AUTO_TICK.get() + 0.12;
        AUTO_TICK.set(tick);
        let dur = AUTO_SPEEDS[self.dur.get()];
        self.auto_ring((tick / dur).min(1.0));
        if tick >= dur {
            AUTO_TICK.set(0.0);
            self.auto_ring(0.0);
            let cur = self.cur.get();
            if cur >= 0 {
                let n = (cur as usize + 1) % self.cards.len();
                let s = self.cards[n].sort;
                self.open_detail(s, 1);
            }
        }
    }

    // ---- apprentissage ----
    fn learn_items(&self, mode: &str) -> Vec<LearnItem> {
        self.cards.iter().enumerate().filter_map(|(i, c)| {
            let p = parse_portrait(self.portraits.get(&c.id).map(|s| s.as_str()).unwrap_or(""));
            let kw = p.key.trim().to_string();
            let phrase = if !p.idee.trim().is_empty() { p.idee.trim().to_string() } else { p.realite.trim().to_string() };
            let answer = if mode == "phrase" { phrase.clone() } else { kw.clone() };
            if answer.is_empty() { None } else { Some(LearnItem { card_id: i, kw, phrase, lvl: 0 }) }
        }).collect()
    }
    fn learn_lvls(&self) -> HashMap<String, i32> {
        store_get("tarotLearnV1").and_then(|s| serde_json::from_str(&s).ok()).unwrap_or_default()
    }
    fn learn_save(&self, lv: &HashMap<String, i32>) {
        store_set("tarotLearnV1", &serde_json::to_string(lv).unwrap_or_default());
    }
    fn open_learn(&self) {
        let mode = self.learn_mode.borrow().clone();
        let mut q = self.learn_items(&mode);
        let lv = self.learn_lvls();
        shuffle(&mut q);
        for it in q.iter_mut() { it.lvl = lv.get(&self.cards[it.card_id].id).copied().unwrap_or(0); }
        q.sort_by_key(|it| it.lvl);
        let total = q.len();
        *self.learn.borrow_mut() = Some(Learn {
            queue: q, total, hits: 0, missed: HashMap::new(), seen: HashMap::new(),
            cur: None, cur_opts: Vec::new(), answered: false,
        });
        if let Some(t) = el("learnTotal") { t.set_text_content(Some(&total.to_string())); }
        if let Some(s) = el("learnStage") { s.set_attribute("style", "").ok(); }
        if let Some(d) = el("learnDock") { d.set_attribute("style", "").ok(); }
        if let Some(e) = el("learnEnd") { e.class_list().remove_1("show").ok(); }
        self.render_learn_mode();
        if let Some(l) = el("learn") { l.class_list().add_1("open").ok(); }
        doc().body().unwrap().style().set_property("overflow", "hidden").ok();
        self.learn_next();
    }
    fn close_learn(&self) {
        if let Some(l) = el("learn") { l.class_list().remove_1("open").ok(); }
        doc().body().unwrap().style().set_property("overflow", "").ok();
        *self.learn.borrow_mut() = None;
    }
    fn set_learn_mode(&self, mode: &str) {
        *self.learn_mode.borrow_mut() = mode.to_string();
        self.render_learn_mode();
        if self.learn.borrow().is_some() { self.open_learn(); }
    }
    fn render_learn_mode(&self) {
        let mode = self.learn_mode.borrow().clone();
        if let Some(b) = el("learnModeKey") { let _ = b.set_attribute("aria-pressed", if mode == "key" { "true" } else { "false" }); }
        if let Some(b) = el("learnModePhrase") { let _ = b.set_attribute("aria-pressed", if mode == "phrase" { "true" } else { "false" }); }
    }
    fn learn_progress(&self) {
        let learn = self.learn.borrow();
        let Some(l) = learn.as_ref() else { return };
        if let Some(d) = el("learnDone") { d.set_text_content(Some(&l.seen.len().to_string())); }
        if let Some(h) = el("learnHits") { h.set_text_content(Some(&l.hits.to_string())); }
        if let Some(b) = el("learnBar") {
            stl(&b).set_property("width", &format!("{}%", 100 * l.seen.len() / l.total.max(1))).ok();
        }
    }
    fn learn_next(&self) {
        let mut learn = self.learn.borrow_mut();
        let Some(l) = learn.as_mut() else { return };
        if l.queue.is_empty() {
            let lv = self.learn_lvls();
            let mode = self.learn_mode.borrow().clone();
            let total_items = self.learn_items(&mode);
            let mastered = total_items.iter().filter(|x| lv.get(&self.cards[x.card_id].id).copied().unwrap_or(0) >= 3).count();
            let miss: Vec<(usize, String)> = l.missed.values().cloned().collect();
            if let Some(d) = el("learnDrawer") { d.class_list().remove_1("open").ok(); }
            if let Some(s) = el("learnStage") { s.set_attribute("style", "display:none").ok(); }
            if let Some(d) = el("learnDock") { d.set_attribute("style", "display:none").ok(); }
            if let Some(e) = el("learnEnd") {
                e.class_list().add_1("show").ok();
                let mut html = format!(
                    "<h3>Session terminée</h3><div class=\"score\">{} ✓ · {} ratée{} · {}/{} maîtrisées (niveau ≥ 3)</div>",
                    l.hits, miss.len(), if miss.len() > 1 { "s" } else { "" }, mastered, l.total
                );
                if !miss.is_empty() {
                    let names: Vec<String> = miss.iter()
                        .map(|(i, a)| format!("{} ({})", esc(&self.cards[*i].name), esc(a)))
                        .collect();
                    html.push_str(&format!("<div class=\"missed\"><b>À revoir :</b> {}</div>", names.join(" · ")));
                }
                html.push_str("<button class=\"learn-next\" data-act=\"open-learn\">Recommencer</button> <button class=\"learn-next\" style=\"background:none;border-color:var(--line);color:var(--muted)\" data-act=\"learn-reset\">Réinitialiser la progression</button>");
                e.set_inner_html(&html);
            }
            return;
        }
        let cur = l.queue[0].clone();
        l.answered = false;
        let cur_id = self.cards[cur.card_id].id.clone();
        if let Some(s) = el("learnStage") { s.set_attribute("style", "").ok(); }
        if let Some(d) = el("learnDock") { d.set_attribute("style", "").ok(); }
        if let Some(e) = el("learnEnd") { e.class_list().remove_1("show").ok(); }
        if let Some(img) = el("learnImg") { let _ = img.set_attribute("src", &self.deck_url(&cur_id)); }
        // 4 distracteurs, même famille en priorité
        let fam = self.cards[cur.card_id].fam.clone();
        let pool: Vec<LearnItem> = self.learn_items(&self.learn_mode.borrow().clone())
            .into_iter().filter(|x| x.card_id != cur.card_id).collect();
        let mut same: Vec<LearnItem> = pool.iter().filter(|x| self.cards[x.card_id].fam == fam).cloned().collect();
        let mut other: Vec<LearnItem> = pool.iter().filter(|x| self.cards[x.card_id].fam != fam).cloned().collect();
        shuffle(&mut same);
        shuffle(&mut other);
        let mut dis: Vec<LearnItem> = same.into_iter().take(4).collect();
        if dis.len() < 4 { dis.extend(other.into_iter().take(4 - dis.len())); }
        dis.push(cur.clone());
        shuffle(&mut dis);
        l.cur = Some(cur.clone());
        l.cur_opts = dis.clone();
        let mut html = String::new();
        let cur_id = self.cards[cur.card_id].id.clone();
        for (i, o) in dis.iter().enumerate() {
            let answer = if *self.learn_mode.borrow() == "phrase" { o.phrase.clone() } else { o.kw.clone() };
            let ok = self.cards[o.card_id].id == cur_id;
            html.push_str(&format!(
                "<button class=\"learn-opt\" data-act=\"learn-ans\" data-i=\"{}\"{}><span class=\"n\">{}</span>{}</button>",
                i, if ok { " data-ok=\"1\"" } else { "" }, i + 1, esc(&answer)
            ));
        }
        if let Some(opts) = el("learnOpts") { opts.set_inner_html(&html); }
        if let Some(d) = el("learnDrawer") { d.class_list().remove_1("open").ok(); }
        drop(learn);
        self.learn_progress();
    }
    fn learn_answer(&self, i: usize) {
        let ok;
        {
            let mut learn = self.learn.borrow_mut();
            let Some(l) = learn.as_mut() else { return };
            if l.answered { return; }
            l.answered = true;
            let cur = l.cur.clone().expect("cur");
            ok = l.cur_opts.get(i).map(|o| o.card_id == cur.card_id).unwrap_or(false);
            let cur_id = self.cards[cur.card_id].id.clone();
            let mut lv = self.learn_lvls();
            l.seen.insert(cur_id.clone(), true);
            l.queue.remove(0);
            if ok {
                l.hits += 1;
                lv.insert(cur_id.clone(), (lv.get(&cur_id).copied().unwrap_or(0) + 1).min(5));
            } else {
                l.missed.insert(cur_id.clone(), (cur.card_id, if *self.learn_mode.borrow() == "phrase" { cur.phrase.clone() } else { cur.kw.clone() }));
                lv.insert(cur_id.clone(), lv.get(&cur_id).copied().unwrap_or(0).saturating_sub(1));
                let pos = l.queue.len().min(4);
                l.queue.insert(pos, cur.clone());
            }
            self.learn_save(&lv);
            let p = parse_portrait(self.portraits.get(&cur_id).map(|s| s.as_str()).unwrap_or(""));
            if let Some(d) = el("learnDrawer") {
                let mut html = format!("<h3>{} — <em>{}</em></h3>", esc(&self.cards[cur.card_id].name), esc(&cur.kw));
                if !p.idee.is_empty() { html.push_str(&format!("<p>{}</p>", esc(&p.idee))); }
                if !p.realite.is_empty() { html.push_str(&format!("<p>{}</p>", esc(&p.realite))); }
                html.push_str("<button class=\"learn-next\" data-act=\"learn-next\">Suivante →</button>");
                d.set_inner_html(&html);
                d.class_list().add_1("open").ok();
            }
            // feedback visuel sur les options
            if let Some(opts) = el("learnOpts") {
                for (j, b) in children(&opts).into_iter().enumerate() {
                    let b: web_sys::HtmlElement = b.unchecked_into();
                    let _ = b.set_attribute("disabled", "disabled");
                    let right = b.get_attribute("data-ok").is_some();
                    if ok {
                        if right { b.class_list().add_1("right").ok(); }
                        else { b.set_attribute("style", "display:none").ok(); }
                    } else if right {
                        b.class_list().add_1("right").ok();
                    } else if j == i {
                        b.class_list().add_1("wrong").ok();
                    } else {
                        b.set_attribute("style", "display:none").ok();
                    }
                }
            }
        }
        let _ = ok;
        self.learn_progress();
    }
    fn learn_reset(&self) {
        store_set("tarotLearnV1", "{}");
        self.open_learn();
    }

    // ---- divers ----
    fn toast(&self, msg: &str) {
        let t = match el("toast") {
            Some(t) => t,
            None => {
                let t = doc().create_element("div").expect("div");
                let _ = t.set_attribute("id", "toast");
                doc().body().unwrap().append_child(&t).ok();
                t
            }
        };
        t.set_text_content(Some(msg));
        t.class_list().add_1("show").ok();
        let t2 = t.clone();
        after_ms(2400, move || { t2.class_list().remove_1("show").ok(); });
    }

    fn share_current(&self) {
        self.sync_url();
        let cur = self.cur.get();
        let Some(c) = (cur >= 0).then(|| self.cards[cur as usize].clone()) else { return };
        let href = win().location().href().unwrap_or_default();
        let nav: JsValue = win().into();
        let d = js_sys::Object::new();
        js_sys::Reflect::set(&d, &"title".into(), &format!("{} — Tarot Divinatoire", c.name).into()).ok();
        js_sys::Reflect::set(&d, &"text".into(), &format!("{} — signification et mots-clés", c.name).into()).ok();
        js_sys::Reflect::set(&d, &"url".into(), &href.clone().into()).ok();
        if let Ok(f) = js_sys::Reflect::get(&nav, &"share".into()) {
            if f.is_function() {
                let _ = f.dyn_into::<js_sys::Function>().and_then(|f| f.call1(&nav, &d));
                return;
            }
        }
        let ok = js_sys::Reflect::get(&nav, &"clipboard".into()).ok()
            .and_then(|c| js_sys::Reflect::get(&c, &"writeText".into()).ok())
            .map(|f| f.is_function()).unwrap_or(false);
        if ok {
            let _ = js_sys::Reflect::get(&nav, &"clipboard".into()).unwrap()
                .dyn_into::<js_sys::Object>().and_then(|c| {
                    js_sys::Reflect::get(&c, &"writeText".into()).unwrap()
                        .dyn_into::<js_sys::Function>()
                        .and_then(|f| f.call1(&c, &JsValue::from_str(&href)))
                });
            self.toast("Lien copié");
        } else {
            self.toast(&href);
        }
    }
}

fn app_rc() -> Rc<State> {
    APP.with(|a| a.borrow().as_ref().expect("app").clone())
}
#[allow(dead_code)]
fn _keep_app_rc() { let _ = app_rc; }

// ---- utilitaires DOM/temps ----

fn after_ms(ms: u32, f: impl FnMut() + 'static) {
    let c = Closure::wrap(Box::new(f) as Box<dyn FnMut()>);
    win().set_timeout_with_callback_and_timeout_and_arguments_0(c.as_ref().unchecked_ref(), ms as i32).ok();
    // one-shot : on garde la closure vivante jusqu'à la fin de la page (quelques octets)
    KEEP_TMPS.with(|k| k.borrow_mut().push(c));
}

fn spawn_interval(ms: u32, mut f: impl FnMut() + 'static) -> i32 {
    let c = Closure::wrap(Box::new(move || f()) as Box<dyn FnMut()>);
    let id = win().set_interval_with_callback_and_timeout_and_arguments_0(c.as_ref().unchecked_ref(), ms as i32).unwrap_or(0);
    AUTO_CLOSURE.with(|t| *t.borrow_mut() = Some(c));
    id
}

fn shuffle<T>(a: &mut [T]) {
    let n = a.len();
    for i in (1..n).rev() {
        let j = (js_sys::Math::random() * ((i + 1) as f64)) as usize % (i + 1);
        a.swap(i, j);
    }
}

async fn fetch_json(url: &str) -> Result<JsValue, JsValue> {
    let resp = win().fetch_with_str(url);
    let resp: web_sys::Response = wasm_bindgen_futures::JsFuture::from(resp).await?.dyn_into()?;
    let p = resp.json()?;
    wasm_bindgen_futures::JsFuture::from(p).await
}

fn js_str(v: &JsValue) -> String {
    js_sys::JSON::stringify(v).map(|s| s.as_string().unwrap_or_default()).unwrap_or_default()
}

// ---- boot ----

#[wasm_bindgen]
pub fn run() {
    wasm_bindgen_futures::spawn_local(async {
        let data = match fetch_json("data/app-data.json").await {
            Ok(v) => v,
            Err(_) => {
                web_sys::console::error_1(&"app-data.json introuvable".into());
                return;
            }
        };
        let portraits = fetch_json("data/portraits.json").await.unwrap_or(JsValue::NULL);
        let app_data: AppData = serde_json::from_str(&js_str(&data)).expect("parse app-data");
        let portraits: HashMap<String, String> = if portraits.is_null() {
            HashMap::new()
        } else {
            serde_json::from_str(&js_str(&portraits)).unwrap_or_default()
        };
        let nuances: Vec<NuanceCat> = serde_json::from_str(NUANCES_JSON).expect("nuances");
        let deck = store_get("tarotDeck").filter(|d| DECKS.iter().any(|(k, _)| k == d)).unwrap_or_else(|| "rws".into());
        let state = Rc::new(State {
            cards: app_data.cards,
            fams: app_data.families,
            es: app_data.es,
            portraits,
            nuances,
            assocs: RefCell::new(HashMap::new()),
            cur: Cell::new(-1),
            deck: RefCell::new(deck),
            theme: Cell::new(0),
            kw_on: Cell::new(false),
            s_fam: RefCell::new(String::new()),
            s_q: RefCell::new(String::new()),
            s_sel: Cell::new(0),
            dur: Cell::new(1),
            learn_mode: RefCell::new("key".into()),
            learn: RefCell::new(None),
            booted: Cell::new(false),
        });
        APP.with(|a| *a.borrow_mut() = Some(state.clone()));

        install_events(&state);

        // réglages persistés
        if let Some(i) = THEMES.iter().position(|(k, _, _)| *k == store_get("tarotTheme").unwrap_or_default().as_str()) {
            state.apply_theme(i, false);
        } else {
            state.apply_theme(0, false);
        }
        state.apply_deck();
        if store_get("tarotKw").as_deref() == Some("1") { state.toggle_kw(); }

        // loader
        if let Some(t) = el("loader").and_then(|l| l.query_selector(".loader-text").ok().flatten()) {
            let i = (js_sys::Math::random() * BASELINES.len() as f64) as usize % BASELINES.len();
            t.set_text_content(Some(BASELINES[i]));
        }
        if let Some(l) = el("loader") { l.class_list().add_1("gone").ok(); }

        // restauration URL
        state.booted.set(true);
        let search = win().location().search().unwrap_or_default();
        let params = parse_query(&search);
        if let Some(dk) = params.get("deck") {
            if DECKS.iter().any(|(k, _)| k == dk) && *dk != state.deck.borrow().as_str() {
                *state.deck.borrow_mut() = dk.clone();
                state.apply_deck();
            }
        }
        if let Some(tk) = params.get("theme") {
            if let Some(i) = THEMES.iter().position(|(k, _, _)| k == tk) { state.apply_theme(i, false); }
        }
        if params.get("kw").map(|v| v == "1").unwrap_or(false) && !state.kw_on.get() { state.toggle_kw(); }
        if let Some(id) = params.get("carte") {
            if let Some((_, c)) = state.card_by_id(id) { state.open_detail(c.sort, 0); }
        }
    });
}

fn parse_query(s: &str) -> HashMap<String, String> {
    let mut out = HashMap::new();
    for pair in s.trim_start_matches('?').split('&') {
        if pair.is_empty() { continue; }
        let mut it = pair.splitn(2, '=');
        let k = it.next().unwrap_or("").to_string();
        let v = it.next().unwrap_or("").to_string();
        out.insert(k, v);
    }
    out
}

fn install_events(state: &Rc<State>) {
    // clic global (délégation)
    {
        let st = state.clone();
        let c = Closure::wrap(Box::new(move |e: Event| {
            let st = &st;
            let Ok(target) = e.target().unwrap().dyn_into::<Element>() else { return };
            // fermeture du panneau réglages au clic extérieur
            let in_settings = target.closest(".settings-wrap").ok().flatten().is_some();
            if !in_settings {
                if let Some(p) = el("setPanel") { p.class_list().remove_1("open").ok(); }
            }
            // trouver l'élément porteur de data-act
            let mut node: Option<Element> = Some(target.clone());
            while let Some(n) = node {
                if let Some(act) = n.get_attribute("data-act") {
                    e.stop_propagation();
                    dispatch(st, &act, &n);
                    return;
                }
                node = n.parent_element();
            }
            // clic sur fond d'overlay = fermer
            match target.get_attribute("id").as_deref() {
                Some("search") => st.close_search(),
                Some("nuances") => st.close_nuances(),
                _ => {}
            }
        }) as Box<dyn FnMut(Event)>);
        doc().add_event_listener_with_callback("click", c.as_ref().unchecked_ref()).ok();
        KEEP_EVT.with(|k| k.borrow_mut().push(c));
    }
    // input recherche
    if let Some(i) = el("sInput") {
        let st = state.clone();
        let c = Closure::wrap(Box::new(move |e: Event| {
            if let Ok(input) = e.target().unwrap().dyn_into::<web_sys::HtmlInputElement>() {
                *st.s_q.borrow_mut() = input.value().trim().to_lowercase();
                st.s_sel.set(0);
                st.sync_query();
                st.search_render();
            }
        }) as Box<dyn FnMut(Event)>);
        i.add_event_listener_with_callback("input", c.as_ref().unchecked_ref()).ok();
        KEEP_EVT.with(|k| k.borrow_mut().push(c));
    }
    // clavier
    {
        let st = state.clone();
        let c = Closure::wrap(Box::new(move |e: Event| {
            let kev: &web_sys::KeyboardEvent = e.dyn_ref().expect("keydown");
            if kev.meta_key() || kev.ctrl_key() || kev.alt_key() { return; }
            let key = kev.key();
            let tag = doc().active_element().map(|a| a.tag_name()).unwrap_or_default();
            let sg = el("search").map(|s| s.class_list().contains("open")).unwrap_or(false);
            let lg = el("learn").map(|s| s.class_list().contains("open")).unwrap_or(false);
            if key == "Escape" {
                if lg { st.close_learn(); }
                else if sg { st.close_search(); }
                else if el("nuances").map(|s| s.class_list().contains("open")).unwrap_or(false) { st.close_nuances(); }
                else { st.stop_auto(); st.close_detail(); }
                return;
            }
            if lg {
                let answered = st.learn.borrow().as_ref().map(|l| l.answered).unwrap_or(false);
                if ["1", "2", "3", "4", "5"].contains(&key.as_str()) {
                    st.learn_answer(key.parse::<usize>().unwrap_or(1) - 1);
                } else if answered && (key == "Enter" || key == " " || key == "ArrowRight") {
                    e.prevent_default();
                    st.learn_next();
                }
                return;
            }
            if tag == "INPUT" || tag == "TEXTAREA" || tag == "SELECT" { return; }
            if sg {
                match key.as_str() {
                    "ArrowDown" => { let n = st.search_filter().len(); st.s_sel.set((st.s_sel.get() + 1).min(n.max(1) - 1)); st.search_render(); }
                    "ArrowUp" => { st.s_sel.set(st.s_sel.get().saturating_sub(1)); st.search_render(); }
                    "Enter" => {
                        if let Some(m) = el("sGrid").and_then(|g| g.query_selector(".mini.sel").ok().flatten()) {
                            m.set_attribute("data-act", "detail").ok();
                            if let Some(sort) = m.get_attribute("data-sort") { st.close_search(); st.open_detail(sort.parse().unwrap_or(0), 0); }
                        }
                    }
                    _ => {}
                }
                return;
            }
            if key.chars().count() == 1 && key.chars().next().map(|c| c.is_alphanumeric()).unwrap_or(false) {
                e.prevent_default();
                st.open_search(&key);
                return;
            }
            let cur = st.cur.get();
            if cur >= 0 {
                let n = st.cards.len();
                if key == "ArrowLeft" { let i = (cur as usize + n - 1) % n; st.open_detail(st.cards[i].sort, -1); }
                if key == "ArrowRight" { let i = (cur as usize + 1) % n; st.open_detail(st.cards[i].sort, 1); }
            }
        }) as Box<dyn FnMut(Event)>);
        doc().add_event_listener_with_callback("keydown", c.as_ref().unchecked_ref()).ok();
        KEEP_EVT.with(|k| k.borrow_mut().push(c));
    }
    // swipe horizontal mobile
    if let Some(d) = el("detail") {
        let c = Closure::wrap(Box::new(move |e: TouchEvent| {
            if let Some(t) = e.changed_touches().get(0) {
                TOUCH_X.set(t.client_x() as f64);
                TOUCH_Y.set(t.client_y() as f64);
                TOUCH_T.set(js_sys::Date::now());
            }
        }) as Box<dyn FnMut(TouchEvent)>);
        d.add_event_listener_with_callback_and_add_event_listener_options(
            "touchstart", c.as_ref().unchecked_ref(),
            web_sys::AddEventListenerOptions::new().passive(true),
        ).ok();
        KEEP_TOUCH.with(|k| k.borrow_mut().push(c));

        let st = state.clone();
        let c = Closure::wrap(Box::new(move |e: TouchEvent| {
            if st.cur.get() < 0 { return; }
            if let Some(t) = e.changed_touches().get(0) {
                let dx = t.client_x() as f64 - TOUCH_X.get();
                let dy = t.client_y() as f64 - TOUCH_Y.get();
                let dt = js_sys::Date::now() - TOUCH_T.get();
                if let Ok(tg) = e.target().unwrap().dyn_into::<Element>() {
                    if tg.closest(".d-thumbs,.d-assocs-toggle,a,button").ok().flatten().is_some() { return; }
                }
                if dx.abs() > 60.0 && dx.abs() > dy.abs() * 1.5 && dt < 800.0 {
                    e.prevent_default();
                    let n = st.cards.len();
                    let cur = st.cur.get() as usize;
                    let next = if dx < 0.0 { (cur + 1) % n } else { (cur + n - 1) % n };
                    st.open_detail(st.cards[next].sort, if dx < 0.0 { 1 } else { -1 });
                }
            }
        }) as Box<dyn FnMut(TouchEvent)>);
        d.add_event_listener_with_callback("touchend", c.as_ref().unchecked_ref()).ok();
        KEEP_TOUCH.with(|k| k.borrow_mut().push(c));
    }
}

fn dispatch(st: &State, act: &str, node: &Element) {
    let attr = |n: &str| node.get_attribute(n).unwrap_or_default();
    match act {
        "detail" => st.open_detail(attr("data-sort").parse().unwrap_or(0), 0),
        "detail-id" => {
            if let Some((_, c)) = st.card_by_id(&attr("data-id")) { st.open_detail(c.sort, 0); }
        }
        "close-detail" => st.close_detail(),
        "open-search" => st.open_search(""),
        "close-search" => st.close_search(),
        "open-nuances" => st.open_nuances(),
        "close-nuances" => st.close_nuances(),
        "open-learn" => st.open_learn(),
        "close-learn" => st.close_learn(),
        "learn-reset" => st.learn_reset(),
        "learn-next" => st.learn_next(),
        "learn-ans" => st.learn_answer(attr("data-i").parse().unwrap_or(0)),
        "learn-mode" => st.set_learn_mode(&attr("data-mode")),
        "set-fam" => {
            *st.s_fam.borrow_mut() = attr("data-fam");
            st.s_sel.set(0);
            st.render_chips();
            st.search_render();
        }
        "open-family" => {
            *st.s_fam.borrow_mut() = attr("data-fam");
            *st.s_q.borrow_mut() = String::new();
            st.s_sel.set(0);
            if let Some(i) = el("sInput") {
                if let Ok(input) = i.dyn_into::<web_sys::HtmlInputElement>() { input.set_value(""); }
            }
            st.open_search("");
        }
        "cycle-theme" => st.cycle_theme(),
        "cycle-deck" => st.cycle_deck(),
        "toggle-kw" => st.toggle_kw(),
        "toggle-auto" => {
            if AUTO_ID.get() != 0 { st.stop_auto(); } else { st.start_auto(); }
            if let Some(p) = el("setPanel") { p.class_list().remove_1("open").ok(); }
        }
        "auto-spd" => {
            if let Some(i) = AUTO_SPEEDS.iter().position(|s| s.to_string() == attr("data-s")) {
                st.dur.set(i);
                AUTO_TICK.set(0.0);
                if let Some(spd) = el("autoSpd") {
                    for (j, b) in children(&spd).into_iter().enumerate() {
                        cls_toggle(&b, "cur", j == i);
                    }
                }
            }
        }
        "share" => st.share_current(),
        "toggle-settings" => {
            if let Some(p) = el("setPanel") {
                let open = p.class_list().contains("open");
                cls_toggle(&p, "open", !open);
            }
            if let Some(f) = el("setFab") {
                let open = el("setPanel").map(|p| p.class_list().contains("open")).unwrap_or(false);
                let _ = f.set_attribute("aria-expanded", if open { "true" } else { "false" });
            }
        }
        "toggle-assocs" => {
            if let Some(p) = node.parent_element() {
                let open = p.class_list().contains("open");
                cls_toggle(&p, "open", !open);
            }
        }
        _ => {}
    }
}
