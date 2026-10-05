// Exporte vault.sqlite vers un dossier statique www/ :
// data/app-data.json, data/portraits.json, img/, decks/, fonts/, svg/, assocs/<id>.json
use rusqlite::Connection;
use std::fs;
use std::path::Path;

fn main() {
    let mut args = std::env::args().skip(1);
    let vault = args.next().expect("usage: exporter <vault.sqlite> <out-dir>");
    let out = args.next().expect("usage: exporter <vault.sqlite> <out-dir>");
    let out = Path::new(&out);

    let db = Connection::open(&vault).expect("open vault");
    let mut stmt = db
        .prepare("SELECT path, mime, data FROM vault")
        .expect("prepare");

    let rows: Vec<(String, String, Vec<u8>)> = stmt
        .query_map([], |r| {
            // data peut être TEXT (binaire avec NULs) ou BLOB selon l'import : lire les octets bruts
            let bytes = match r.get_ref(2)? {
                rusqlite::types::ValueRef::Blob(b) => b.to_vec(),
                rusqlite::types::ValueRef::Text(b) => b.to_vec(),
                rusqlite::types::ValueRef::Integer(i) => i.to_le_bytes().to_vec(),
                rusqlite::types::ValueRef::Real(f) => f.to_le_bytes().to_vec(),
                rusqlite::types::ValueRef::Null => Vec::new(),
            };
            Ok((r.get(0)?, r.get(1)?, bytes))
        })
        .expect("query")
        .filter_map(Result::ok)
        .collect();

    let mut n = 0usize;
    for (path, mime, data) in rows {
        let dest = if path == "/app-data.json" {
            Some(out.join("data/app-data.json"))
        } else if let Some(p) = path.strip_prefix("/img/") {
            Some(out.join("img").join(p))
        } else if let Some(p) = path.strip_prefix("/decks/") {
            Some(out.join("decks").join(p))
        } else if let Some(p) = path.strip_prefix("/fonts/") {
            Some(out.join("fonts").join(p))
        } else if let Some(p) = path.strip_prefix("/svg/") {
            Some(out.join("svg").join(p))
        } else if let Some(rest) = path.strip_prefix("/cards/") {
            if let Some(id) = rest.strip_suffix("/associations.json") {
                Some(out.join("assocs").join(id).with_extension("json"))
            } else {
                None
            }
        } else {
            None
        };
        if let Some(dest) = dest {
            if let Some(dir) = dest.parent() {
                fs::create_dir_all(dir).expect("mkdir");
            }
            fs::write(&dest, &data).expect("write");
            let _ = mime;
            n += 1;
        }
    }
    println!("exporté : {n} fichiers vers {}", out.display());
}
