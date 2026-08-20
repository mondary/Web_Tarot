import os
import glob
import re
import json

# 1. Load card of the day data
with open('benchmarks/card_of_day_data.json', 'r', encoding='utf-8') as f:
    cod_data = json.load(f)

# Suite configurations
SUITE_MAP = {
    'b': {
        'key': 'batons',
        'name': 'Bâtons',
        'name_en': 'Wands',
        'element': 'Feu',
        'symbol': '🜂',
        'color': '#f97316',
        'bg_gradient': 'linear-gradient(135deg, #7c2d12 0%, #ea580c 50%, #f97316 100%)',
        'dots_color': 'rgba(251, 146, 60, 0.25)',
        'hero_bg': 'radial-gradient(circle at 60% 40%, #ff5722 0%, #c2410c 45%, #7c2d12 85%)',
        'desc': 'Le Feu créateur, la passion, la volonté, l’action et l’ambition'
    },
    'c': {
        'key': 'coupes',
        'name': 'Coupes',
        'name_en': 'Cups',
        'element': 'Eau',
        'symbol': '🜄',
        'color': '#0ea5e9',
        'bg_gradient': 'linear-gradient(135deg, #0c4a6e 0%, #0284c7 50%, #38bdf8 100%)',
        'dots_color': 'rgba(56, 189, 248, 0.25)',
        'hero_bg': 'radial-gradient(circle at 60% 40%, #0284c7 0%, #0369a1 45%, #082f49 85%)',
        'desc': 'L’Eau des sentiments, l’amour, l’intuition, la réceptivité et le cœur'
    },
    'e': {
        'key': 'epees',
        'name': 'Épées',
        'name_en': 'Swords',
        'element': 'Air',
        'symbol': '🜁',
        'color': '#a855f7',
        'bg_gradient': 'linear-gradient(135deg, #3b0764 0%, #7e22ce 50%, #c084fc 100%)',
        'dots_color': 'rgba(192, 132, 252, 0.25)',
        'hero_bg': 'radial-gradient(circle at 60% 40%, #7e22ce 0%, #581c87 45%, #2e1065 85%)',
        'desc': 'L’Air de l’esprit, la pensée lucide, la vérité, la parole et les épreuves'
    },
    'd': {
        'key': 'deniers',
        'name': 'Deniers',
        'name_en': 'Pentacles',
        'element': 'Terre',
        'symbol': '🜃',
        'color': '#10b981',
        'bg_gradient': 'linear-gradient(135deg, #064e3b 0%, #059669 50%, #34d399 100%)',
        'dots_color': 'rgba(52, 211, 153, 0.25)',
        'hero_bg': 'radial-gradient(circle at 60% 40%, #059669 0%, #047857 45%, #064e3b 85%)',
        'desc': 'La Terre matérielle, l’ancrage, la prospérité, le travail et le corps'
    }
}

MAJOR_META = {
    '00': {'name': 'Le Fou', 'name_en': 'The Fool', 'num_roman': '0', 'element': 'Air / Uranus', 'symbol': '🜁', 'astro': 'Uranus', 'crystal': 'Aventurine', 'color': '#f43f5e', 'hero_bg': 'radial-gradient(circle at 60% 40%, #e11d48 0%, #be123c 45%, #881337 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Un voyage de mille lieues commence toujours par un premier pas. » — Lao Tseu', 'key_word': 'ÉLAN', 'speech': '✨ ✦ 0 · Le Saut dans l\'Inconnu'},
    '01': {'name': 'Le Bateleur', 'name_en': 'The Magician', 'num_roman': 'I', 'element': 'Feu / Mercure', 'symbol': '✦', 'astro': 'Mercure', 'crystal': 'Agate', 'color': '#ef4444', 'hero_bg': 'radial-gradient(circle at 60% 40%, #dc2626 0%, #b91c1c 45%, #7f1d1d 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Tout ce dont vous avez besoin est déjà entre vos mains. »', 'key_word': 'VOLONTÉ', 'speech': '✨ ✦ I · L\'Alchimie de la Création'},
    '02': {'name': 'La Papesse', 'name_en': 'The High Priestess', 'num_roman': 'II', 'element': 'Eau / Lune', 'symbol': '🜄', 'astro': 'Lune', 'crystal': 'Pierre de lune', 'color': '#3b82f6', 'hero_bg': 'radial-gradient(circle at 60% 40%, #2563eb 0%, #1d4ed8 45%, #172554 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Le silence garde les secrets de l\'âme. »', 'key_word': 'INTUITION', 'speech': '✨ ✦ II · Gardienne du Temple Secret'},
    '03': {'name': 'L\'Impératrice', 'name_en': 'The Empress', 'num_roman': 'III', 'element': 'Terre / Vénus', 'symbol': '🜃', 'astro': 'Vénus', 'crystal': 'Quartz rose', 'color': '#ec4899', 'hero_bg': 'radial-gradient(circle at 60% 40%, #db2777 0%, #be185d 45%, #700736 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La créativité est l\'expression suprême de l\'amour. »', 'key_word': 'ABONDANCE', 'speech': '✨ ✦ III · Mère Fertile de l\'Univers'},
    '04': {'name': 'L\'Empereur', 'name_en': 'The Emperor', 'num_roman': 'IV', 'element': 'Feu / Mars', 'symbol': '🜂', 'astro': 'Bélier / Mars', 'crystal': 'Jaspe rouge', 'color': '#ea580c', 'hero_bg': 'radial-gradient(circle at 60% 40%, #c2410c 0%, #9a3412 45%, #431407 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La structure donne au pouvoir sa véritable grandeur. »', 'key_word': 'AUTORITÉ', 'speech': '✨ ✦ IV · Maître de la Structure'},
    '05': {'name': 'Le Pape', 'name_en': 'The Hierophant', 'num_roman': 'V', 'element': 'Terre / Taureau', 'symbol': '🜃', 'astro': 'Taureau', 'crystal': 'Lapis-lazuli', 'color': '#8b5cf6', 'hero_bg': 'radial-gradient(circle at 60% 40%, #7c3aed 0%, #6d28d9 45%, #2e1065 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La transmission élève l\'esprit au-dessus du temps. »', 'key_word': 'SAGESSE', 'speech': '✨ ✦ V · Guide des Traditions Sacrées'},
    '06': {'name': 'Les Amoureux', 'name_en': 'The Lovers', 'num_roman': 'VI', 'element': 'Air / Gémeaux', 'symbol': '🜁', 'astro': 'Gémeaux', 'crystal': 'Rhodonite', 'color': '#f43f5e', 'hero_bg': 'radial-gradient(circle at 60% 40%, #e11d48 0%, #be123c 45%, #881337 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Choisir avec le cœur, c\'est s\'aligner avec son âme. »', 'key_word': 'CHOIX', 'speech': '✨ ✦ VI · L\'Union des Âmes Sœurs'},
    '07': {'name': 'Le Chariot', 'name_en': 'The Chariot', 'num_roman': 'VII', 'element': 'Eau / Cancer', 'symbol': '🜄', 'astro': 'Cancer', 'crystal': 'Œil de tigre', 'color': '#eab308', 'hero_bg': 'radial-gradient(circle at 60% 40%, #ca8a04 0%, #a16207 45%, #451a03 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La maîtrise de ses dualités mène à la victoire. »', 'key_word': 'TRIOMPHE', 'speech': '✨ ✦ VII · La Marche Victorieuse'},
    '08': {'name': 'La Force', 'name_en': 'Strength', 'num_roman': 'VIII', 'element': 'Feu / Lion', 'symbol': '🜂', 'astro': 'Lion', 'crystal': 'Cornaline', 'color': '#f97316', 'hero_bg': 'radial-gradient(circle at 60% 40%, #ea580c 0%, #c2410c 45%, #7c2d12 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La vraie puissance s\'exprime avec tendresse et compassion. »', 'key_word': 'MAÎTRISE', 'speech': '✨ ✦ VIII · Douceur et Courage'},
    '09': {'name': 'L\'Ermite', 'name_en': 'The Hermit', 'num_roman': 'IX', 'element': 'Terre / Vierge', 'symbol': '🜃', 'astro': 'Vierge', 'crystal': 'Améthyste', 'color': '#6366f1', 'hero_bg': 'radial-gradient(circle at 60% 40%, #4f46e5 0%, #4338ca 45%, #1e1b4b 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La lumière intérieure ne s\'éteint jamais dans la solitude. »', 'key_word': 'INTROSPECTION', 'speech': '✨ ✦ IX · Porteur de la Lanterne'},
    '10': {'name': 'La Roue de Fortune', 'name_en': 'Wheel of Fortune', 'num_roman': 'X', 'element': 'Feu / Jupiter', 'symbol': '🜂', 'astro': 'Jupiter', 'crystal': 'Citrine', 'color': '#d97706', 'hero_bg': 'radial-gradient(circle at 60% 40%, #b45309 0%, #92400e 45%, #451a03 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Tout change, tout tourne, tout recommence. »', 'key_word': 'DESTIN', 'speech': '✨ ✦ X · Les Cycles du Grand Tout'},
    '11': {'name': 'La Justice', 'name_en': 'Justice', 'num_roman': 'XI', 'element': 'Air / Balance', 'symbol': '🜁', 'astro': 'Balance', 'crystal': 'Jade', 'color': '#06b6d4', 'hero_bg': 'radial-gradient(circle at 60% 40%, #0891b2 0%, #0e7490 45%, #164e63 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« L\'équité et la vérité dissipent les ombres du doute. »', 'key_word': 'ÉQUILIBRE', 'speech': '✨ ✦ XI · L\'Épée et le Fléau'},
    '12': {'name': 'Le Pendu', 'name_en': 'The Hanged Man', 'num_roman': 'XII', 'element': 'Eau / Neptune', 'symbol': '🜄', 'astro': 'Neptune', 'crystal': 'Aigue-marine', 'color': '#0284c7', 'hero_bg': 'radial-gradient(circle at 60% 40%, #0369a1 0%, #075985 45%, #082f49 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Regarder le monde à l\'envers pour enfin le voir à l\'endroit. »', 'key_word': 'LÂCHER-PRISE', 'speech': '✨ ✦ XII · L\'Éveil par le Renversement'},
    '13': {'name': 'La Mort', 'name_en': 'Death', 'num_roman': 'XIII', 'element': 'Eau / Scorpion', 'symbol': '🜄', 'astro': 'Scorpion', 'crystal': 'Obsidienne', 'color': '#64748b', 'hero_bg': 'radial-gradient(circle at 60% 40%, #475569 0%, #334155 45%, #0f172a 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La fin d\'un cycle est le berceau d\'une renaissance. »', 'key_word': 'MÉTAMORPHOSE', 'speech': '✨ ✦ XIII · La Porte de la Renaissance'},
    '14': {'name': 'Tempérance', 'name_en': 'Temperance', 'num_roman': 'XIV', 'element': 'Feu / Sagittaire', 'symbol': '🜂', 'astro': 'Sagittaire', 'crystal': 'Améthyste', 'color': '#14b8a6', 'hero_bg': 'radial-gradient(circle at 60% 40%, #0d9488 0%, #0f766e 45%, #134e4a 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« L\'harmonie naît de la juste mesure des opposés. »', 'key_word': 'FLUIDITÉ', 'speech': '✨ ✦ XIV · L\'Alchimie de la Guérison'},
    '15': {'name': 'Le Diable', 'name_en': 'The Devil', 'num_roman': 'XV', 'element': 'Terre / Capricorne', 'symbol': '🜃', 'astro': 'Capricorne', 'crystal': 'Onyx', 'color': '#b91c1c', 'hero_bg': 'radial-gradient(circle at 60% 40%, #991b1b 0%, #7f1d1d 45%, #450a0a 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La lucidité brise les chaînes invisibles du désir aveugle. »', 'key_word': 'LUCIDITÉ', 'speech': '✨ ✦ XV · Le Miroir des Attachements'},
    '16': {'name': 'La Tour', 'name_en': 'The Tower', 'num_roman': 'XVI', 'element': 'Feu / Mars', 'symbol': '🜂', 'astro': 'Mars', 'crystal': 'Hématite', 'color': '#ea580c', 'hero_bg': 'radial-gradient(circle at 60% 40%, #c2410c 0%, #9a3412 45%, #431407 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Les fondations fragiles s\'écroulent pour bâtir sur le roc. »', 'key_word': 'LIBÉRATION', 'speech': '✨ ✦ XVI · L\'Éclair Libérateur'},
    '17': {'name': 'L\'Étoile', 'name_en': 'The Star', 'num_roman': 'XVII', 'element': 'Air / Verseau', 'symbol': '🜁', 'astro': 'Verseau', 'crystal': 'Turquoise', 'color': '#0ea5e9', 'hero_bg': 'radial-gradient(circle at 60% 40%, #0284c7 0%, #0369a1 45%, #082f49 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Après la nuit la plus sombre brille la plus belle espérance. »', 'key_word': 'ESPÉRANCE', 'speech': '✨ ✦ XVII · La Guide des Étoiles'},
    '18': {'name': 'La Lune', 'name_en': 'The Moon', 'num_roman': 'XVIII', 'element': 'Eau / Poissons', 'symbol': '🜄', 'astro': 'Poissons', 'crystal': 'Sélénite', 'color': '#6366f1', 'hero_bg': 'radial-gradient(circle at 60% 40%, #4f46e5 0%, #4338ca 45%, #1e1b4b 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Traverser le miroir des songes pour embrasser sa vérité. »', 'key_word': 'MYSTÈRE', 'speech': '✨ ✦ XVIII · Le Labyrinthe Intérieur'},
    '19': {'name': 'Le Soleil', 'name_en': 'The Sun', 'num_roman': 'XIX', 'element': 'Feu / Soleil', 'symbol': '🜂', 'astro': 'Soleil', 'crystal': 'Citrine', 'color': '#f59e0b', 'hero_bg': 'radial-gradient(circle at 60% 40%, #d97706 0%, #b45309 45%, #451a03 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« La clarté radieuse apporte joie, vitalité et succès. »', 'key_word': 'RAYONNEMENT', 'speech': '✨ ✦ XIX · La Gloire Solaire'},
    '20': {'name': 'Le Jugement', 'name_en': 'Judgement', 'num_roman': 'XX', 'element': 'Feu / Pluton', 'symbol': '✦', 'astro': 'Pluton', 'crystal': 'Malachite', 'color': '#a855f7', 'hero_bg': 'radial-gradient(circle at 60% 40%, #9333ea 0%, #7e22ce 45%, #3b0764 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« Répondre à l\'appel de sa propre renaissance. »', 'key_word': 'RÉVEIL', 'speech': '✨ ✦ XX · L\'Appel de la Conscience'},
    '21': {'name': 'Le Monde', 'name_en': 'The World', 'num_roman': 'XXI', 'element': 'Terre / Univers', 'symbol': '🜃', 'astro': 'Saturne', 'crystal': 'Cristal de roche', 'color': '#10b981', 'hero_bg': 'radial-gradient(circle at 60% 40%, #059669 0%, #047857 45%, #064e3b 85%)', 'dots_color': 'rgba(255, 255, 255, 0.35)', 'quote': '« L\'accomplissement parfait d\'un voyage cosmique. »', 'key_word': 'TOTALITÉ', 'speech': '✨ ✦ XXI · L\'Harmonie Ultime'},
}

NUM_NAMES = {
    '01': ('As', 'Ace', 'I'),
    '02': ('Deux', 'Two', 'II'),
    '03': ('Trois', 'Three', 'III'),
    '04': ('Quatre', 'Four', 'IV'),
    '05': ('Cinq', 'Five', 'V'),
    '06': ('Six', 'Six', 'VI'),
    '07': ('Sept', 'Seven', 'VII'),
    '08': ('Huit', 'Eight', 'VIII'),
    '09': ('Neuf', 'Nine', 'IX'),
    '10': ('Dix', 'Ten', 'X'),
    '11': ('Valet', 'Page', 'Valet'),
    '12': ('Cavalier', 'Knight', 'Cavalier'),
    '13': ('Reine', 'Queen', 'Reine'),
    '14': ('Roi', 'King', 'Roi')
}

def parse_fiche(filepath):
    if not os.path.exists(filepath):
        return {}
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    data = {
        'love': '',
        'work': '',
        'finance': '',
        'guidance': '',
        'quote': '',
        'meaning_raw': '',
        'image_desc': ''
    }
    
    sections = re.split(r'\n(?=[❤️💼💰🧭💬🔮🖼️])', content)
    for sec in sections:
        sec = sec.strip()
        if not sec:
            continue
        first_line = sec.split('\n')[0]
        body = '\n'.join(sec.split('\n')[1:]).strip()
        
        if 'Amour' in first_line:
            data['love'] = body
        elif 'Travail' in first_line:
            data['work'] = body
        elif 'Finance' in first_line:
            data['finance'] = body
        elif 'Guidance' in first_line:
            data['guidance'] = body
        elif 'Citation' in first_line:
            data['quote'] = body
        elif 'Signification' in first_line:
            data['meaning_raw'] = body
        elif 'Description' in first_line:
            data['image_desc'] = body

    return data

def parse_essence(filepath):
    if not os.path.exists(filepath):
        return {'upright_keywords': [], 'reversed_keywords': [], 'idea': '', 'interpretation': ''}
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    data = {'upright_keywords': [], 'reversed_keywords': [], 'idea': '', 'interpretation': ''}
    
    upright_match = re.search(r'### À l\'endroit\s*\n((?:- .*\n?)+)', content)
    if upright_match:
        data['upright_keywords'] = [l.strip('- ').strip() for l in upright_match.group(1).strip().split('\n') if l.strip()]

    rev_match = re.search(r'### À l\'envers\s*\n((?:- .*\n?)+)', content)
    if rev_match:
        data['reversed_keywords'] = [l.strip('- ').strip() for l in rev_match.group(1).strip().split('\n') if l.strip()]

    idea_match = re.search(r'## Idée centrale\s*\n([^\n#]+)', content)
    if idea_match:
        data['idea'] = idea_match.group(1).strip()

    interp_match = re.search(r'## Interprétation\s*\n([\s\S]+?)(?=\n##|$)', content)
    if interp_match:
        data['interpretation'] = interp_match.group(1).strip()

    return data

def parse_reponse(filepath):
    if not os.path.exists(filepath):
        return {'reponse': 'PEUT-ÊTRE', 'affirmation': ''}
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    rep_match = re.search(r'\*\*RÉPONSE\s*:\*\*\s*([^\n*]+)', content)
    reponse = rep_match.group(1).strip() if rep_match else 'OUI'

    aff_match = re.search(r'>\s*([^\n]+)', content)
    affirmation = aff_match.group(1).strip() if aff_match else ''

    return {'reponse': reponse, 'affirmation': affirmation}

def parse_portrait(filepath):
    if not os.path.exists(filepath):
        return {'key_word': '', 'distinctive_phrase': ''}
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    kw_match = re.search(r'🔑 Mot-clé distinctif\s*:\s*([^\n]+)', content)
    ph_match = re.search(r'✨ L\'image me dit\s*:\s*([^\n]+)', content)

    return {
        'key_word': kw_match.group(1).strip() if kw_match else '',
        'distinctive_phrase': ph_match.group(1).strip() if ph_match else ''
    }

# Scan all cards
all_cards = []
fiches = sorted(glob.glob('benchmarks/cards_alt/*_fiche.md'))

for fpath in fiches:
    base = os.path.basename(fpath).replace('_fiche.md', '')
    prefix = base[0] # a, b, c, d, e
    num_str = base[2:4]

    fiche = parse_fiche(fpath)
    essence = parse_essence(f'benchmarks/cards_alt/{base}_essence.md')
    reponse = parse_reponse(f'benchmarks/cards_alt/{base}_reponse.md')
    portrait = parse_portrait(f'benchmarks/cards_alt/{base}_portrait.md')

    # COD text match
    cod_text = ''
    for k, v in cod_data.items():
        if k.startswith(f"{prefix}_{num_str}"):
            cod_text = v
            break

    # Images
    img_rws = f'benchmarks/cards_alt/{base}.jpg'
    img_fr = f'benchmarks/cards_alt/{base}_fr.jpg' if os.path.exists(f'benchmarks/cards_alt/{base}_fr.jpg') else ''
    img_marseille = f'benchmarks/cards_alt/{base}_marseille.jpg' if os.path.exists(f'benchmarks/cards_alt/{base}_marseille.jpg') else ''
    img_wiki = f'benchmarks/cards_alt/{base}_wiki.jpg' if os.path.exists(f'benchmarks/cards_alt/{base}_wiki.jpg') else ''

    # Borderless png mapping for majors
    img_borderless = ''
    if prefix == 'a':
        num_int = int(num_str)
        border_cand = f'clm_borderless/{num_int}.png'
        if os.path.exists(border_cand):
            img_borderless = border_cand

    if prefix == 'a':
        meta = MAJOR_META.get(num_str, {
            'name': base[5:].replace('_', ' '),
            'name_en': 'Major ' + num_str,
            'num_roman': num_str,
            'element': 'Éther',
            'symbol': '✦',
            'astro': 'Cosmos',
            'crystal': 'Quartz',
            'color': '#f59e0b',
            'hero_bg': 'radial-gradient(circle at 60% 40%, #e11d48 0%, #be123c 45%, #881337 85%)',
            'dots_color': 'rgba(255, 255, 255, 0.35)',
            'quote': fiche.get('quote') or '« Connais-toi toi-même. »',
            'key_word': portrait.get('key_word') or 'ARCANE',
            'speech': f'✦ {num_str} · Arcane Majeur'
        })
        category = 'Arcane Majeur'
        category_key = 'majors'
        card_name = meta['name']
        name_en = meta['name_en']
        num_display = meta['num_roman']
        element = meta['element']
        element_symbol = meta['symbol']
        color = meta['color']
        hero_bg = meta.get('hero_bg', 'radial-gradient(circle at 60% 40%, #e11d48 0%, #be123c 45%, #881337 85%)')
        dots_color = meta.get('dots_color', 'rgba(255,255,255,0.3)')
        quote = meta.get('quote') or fiche.get('quote') or ''
        key_word = portrait.get('key_word') or meta.get('key_word') or 'ÉVEIL'
        speech = meta.get('speech') or f'✨ ✦ {num_display} · {card_name}'
        astro = meta.get('astro', '')
        crystal = meta.get('crystal', '')
    else:
        suite = SUITE_MAP.get(prefix, SUITE_MAP['b'])
        num_info = NUM_NAMES.get(num_str, (f'Carte {num_str}', f'Card {num_str}', num_str))
        category = suite['name']
        category_key = suite['key']
        card_name = f"{num_info[0]} de {suite['name']}"
        name_en = f"{num_info[1]} of {suite['name_en']}"
        num_display = num_info[2]
        element = suite['element']
        element_symbol = suite['symbol']
        color = suite['color']
        hero_bg = suite['hero_bg']
        dots_color = suite['dots_color']
        quote = fiche.get('quote') or f'« L’énergie de la suite des {suite["name"]} guide votre volonté. »'
        key_word = portrait.get('key_word') or num_info[0].upper()
        speech = f'✨ ✦ {card_name} · {element}'
        astro = suite['name']
        crystal = 'Pierre élémentaire'

    card_obj = {
        'id': base,
        'prefix': prefix,
        'num': num_str,
        'num_display': num_display,
        'name': card_name,
        'name_en': name_en,
        'category': category,
        'category_key': category_key,
        'element': element,
        'element_symbol': element_symbol,
        'color': color,
        'hero_bg': hero_bg,
        'dots_color': dots_color,
        'quote': quote,
        'key_word': key_word,
        'speech': speech,
        'astro': astro,
        'crystal': crystal,
        'cod_text': cod_text,
        'idea': essence.get('idea', ''),
        'interpretation': essence.get('interpretation', ''),
        'upright_keywords': essence.get('upright_keywords', []),
        'reversed_keywords': essence.get('reversed_keywords', []),
        'reponse': reponse.get('reponse', 'OUI'),
        'affirmation': reponse.get('affirmation', ''),
        'love': fiche.get('love', ''),
        'work': fiche.get('work', ''),
        'finance': fiche.get('finance', ''),
        'guidance': fiche.get('guidance', ''),
        'image_desc': fiche.get('image_desc', ''),
        'img_rws': img_rws,
        'img_fr': img_fr,
        'img_marseille': img_marseille,
        'img_wiki': img_wiki,
        'img_borderless': img_borderless
    }
    all_cards.append(card_obj)

print(f'Successfully prepared {len(all_cards)} cards.')
with open('scripts/all_tarot_cards.json', 'w', encoding='utf-8') as f:
    json.dump(all_cards, f, ensure_ascii=False, indent=2)
