#!/bin/sh
# Build v12-rust : vault.sqlite + Rust -> www/ (statique, déployable tel quel)
set -e
cd "$(dirname "$0")"
export PATH="$HOME/.cargo/bin:$PATH"

rm -rf www
cargo run -q -p tarot-exporter -- ../v9/vault.sqlite www
wasm-pack build crates/app --release --target web --out-dir ../../www/pkg --no-pack
cp static/index.html static/manifest.json www/ 2>/dev/null || cp static/index.html www/
cp static/icon-512.png static/icon-192.png www/ 2>/dev/null || true
echo "OK -> www/ ($(du -sh www | cut -f1))"
