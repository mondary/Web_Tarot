#!/bin/bash

# Tarot Card Downloader Script
# Downloads all 78 tarot cards from thetarotlady.com

OUTPUT_DIR="/Users/clm/Documents/GitHub/PROJECTS/Web_Tarot/website/images_thetarotlady/"
mkdir -p "$OUTPUT_DIR"

# Function to download a card
download_card() {
    local url="$1"
    local filename="$2"
    
    echo "Downloading: $filename"
    
    if curl -s -L -o "$OUTPUT_DIR/$filename" "$url" && [ -s "$OUTPUT_DIR/$filename" ]; then
        echo "✓ Success: $filename"
        return 0
    else
        echo "✗ Failed: $filename"
        rm -f "$OUTPUT_DIR/$filename"
        return 1
    fi
}

# Download Major Arcana (22 cards)
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/fool.jpg" "a_00_Fou.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/magician.jpg" "a_01_Magicien.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/high-priestess.jpg" "a_02_Papesse.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/empress.jpg" "a_03_Impératrice.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/emperor.jpg" "a_04_Empereur.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/hierophant.jpg" "a_05_Pape.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/lovers.jpg" "a_06_Amoureux.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/chariot.jpg" "a_07_Chariot.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/strength.jpg" "a_08_Force.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/hermit.jpg" "a_09_Ermite.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/wheel-of-fortune.jpg" "a_10_Roue_de_Fortune.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/justice.jpg" "a_11_Justice.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/hanged-man.jpg" "a_12_Pendu.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/death.jpg" "a_13_Mort.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/temperance.jpg" "a_14_Tempérance.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/devil.jpg" "a_15_Diable.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/tower.jpg" "a_16_Tour.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/star.jpg" "a_17_Étoile.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/moon.jpg" "a_18_Lune.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/sun.jpg" "a_19_Soleil.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/judgment.jpg" "a_20_Jugement.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/world.jpg" "a_21_Monde.jpg"

echo "Major Arcana download complete!"

# Download Wands (14 cards)
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/ace-of-wands.jpg" "b_01_As_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/two-of-wands.jpg" "b_02_Deux_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/three-of-wands.jpg" "b_03_Trois_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/four-of-wands.jpg" "b_04_Quatre_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/five-of-wands.jpg" "b_05_Cinq_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/six-of-wands.jpg" "b_06_Six_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/seven-of-wands.jpg" "b_07_Sept_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/eight-of-wands.jpg" "b_08_Huit_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/nine-of-wands.jpg" "b_09_Neuf_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/ten-of-wands.jpg" "b_10_Dix_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/page-of-wands.jpg" "b_11_Valet_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/knight-of-wands.jpg" "b_12_Cavalier_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/queen-of-wands.jpg" "b_13_Reine_de_Bâton.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/king-of-wands.jpg" "b_14_Roi_de_Bâton.jpg"

echo "Wands download complete!"

# Download Cups (14 cards)
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/ace-of-cups.jpg" "c_01_As_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/two-of-cups.jpg" "c_02_Deux_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/three-of-cups.jpg" "c_03_Trois_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/four-of-cups.jpg" "c_04_Quatre_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/five-of-cups.jpg" "c_05_Cinq_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/six-of-cups.jpg" "c_06_Six_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/seven-of-cups.jpg" "c_07_Sept_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/eight-of-cups.jpg" "c_08_Huit_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/nine-of-cups.jpg" "c_09_Neuf_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/ten-of-cups.jpg" "c_10_Dix_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/page-of-cups.jpg" "c_11_Valet_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/knight-of-cups.jpg" "c_12_Cavalier_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/queen-of-cups.jpg" "c_13_Reine_de_Coupe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/king-of-cups.jpg" "c_14_Roi_de_Coupe.jpg"

echo "Cups download complete!"

# Download Pentacles (14 cards)
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/ace-of-pentacles.jpg" "d_01_As_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/two-of-pentacles.jpg" "d_02_Deux_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/three-of-pentacles.jpg" "d_03_Trois_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/four-of-pentacles.jpg" "d_04_Quatre_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/five-of-pentacles.jpg" "d_05_Cinq_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/six-of-pentacles.jpg" "d_06_Six_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/seven-of-pentacles.jpg" "d_07_Sept_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/eight-of-pentacles.jpg" "d_08_Huit_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/nine-of-pentacles.jpg" "d_09_Neuf_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/ten-of-pentacles.jpg" "d_10_Dix_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/page-of-pentacles.jpg" "d_11_Valet_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/knight-of-pentacles.jpg" "d_12_Cavalier_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/queen-of-pentacles.jpg" "d_13_Reine_de_Denier.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/king-of-pentacles.jpg" "d_14_Roi_de_Denier.jpg"

echo "Pentacles download complete!"

# Download Swords (14 cards)
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/ace-of-swords.jpg" "e_01_As_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/two-of-swords.jpg" "e_02_Deux_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/three-of-swords.jpg" "e_03_Trois_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/four-of-swords.jpg" "e_04_Quatre_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/five-of-swords.jpg" "e_05_Cinq_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/six-of-swords.jpg" "e_06_Six_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/seven-of-swords.jpg" "e_07_Sept_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/eight-of-swords.jpg" "e_08_Huit_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/nine-of-swords.jpg" "e_09_Neuf_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/ten-of-swords.jpg" "e_10_Dix_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/page-of-swords.jpg" "e_11_Valet_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/knight-of-swords.jpg" "e_12_Cavalier_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/queen-of-swords.jpg" "e_13_Reine_de_Déepe.jpg"
download_card "https://www.thetarotlady.com/wp-content/uploads/2013/02/king-of-swords.jpg" "e_14_Roi_de_Déepe.jpg"

echo "Swords download complete!"

# Count downloaded files
downloaded_count=$(ls -1 "$OUTPUT_DIR"/*.jpg 2>/dev/null | wc -l)
echo "=========================================="
echo "Download complete: $downloaded_count/78 cards downloaded"
echo "Files saved to: $OUTPUT_DIR"