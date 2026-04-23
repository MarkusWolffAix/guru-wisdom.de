#!/bin/zsh 

# S3 Konfiguration
S3_ENDPOINT="https://fsn1.your-objectstorage.com"
S3_BUCKET="s3://guru-wisdom"

BASE_DIR="/Users/markuswolff/Documents/Arbeit/GuruWisdom/guru-wisdom.de/web"
DIR_MD="$BASE_DIR/wisdoms"

# Hilfsfunktion zum Abrufen von S3-Dateilisten
fetch_s3_files() {
    local folder=$1
    aws s3 ls "$S3_BUCKET/$folder/" --endpoint-url "$S3_ENDPOINT" | grep -v " PRE " | awk '{print $4}'
}

echo "📡 Rufe Dateilisten von S3 ab..."
# Dateilisten von S3 abrufen und in assoziative Arrays speichern
typeset -A s3_audio s3_images s3_images_org s3_video

for f in $(fetch_s3_files "audio"); do s3_audio[$f]=1; done
for f in $(fetch_s3_files "images"); do s3_images[$f]=1; done
for f in $(fetch_s3_files "images/org"); do s3_images_org[$f]=1; done
for f in $(fetch_s3_files "video"); do s3_video[$f]=1; done

# Zwei Arrays: Eins für die eigentlichen Artikel, eins für die sekundären Bilder
typeset -A items
typeset -A secondary_images

# 1. PRE-SCAN: Alle lokalen MD-Dateien nach referenzierten Zweit-Bildern durchsuchen
for md_file in "$DIR_MD"/*.md(N); do
    base_md=${md_file:t:r}
    # Finde z.B. BigBang2.jpg (Basisname + mindestens eine Zahl + .jpg)
    refs=($(grep -oE "${base_md}[0-9]+\.jpg" "$md_file" 2>/dev/null | sort -u))
    
    for r in $refs; do
        ref_base=${r:t:r} # Macht aus "BigBang2.jpg" -> "BigBang2"
        secondary_images[$ref_base]=1
    done
done

# 2. ALLE Basisnamen sammeln (Lokal MD + S3)
# Lokale MDs
for f in "$DIR_MD"/*.md(N); do
    item_name=${f:t:r}
    [[ "$item_name" == _* ]] && continue
    [[ -n "${secondary_images[$item_name]}" ]] && continue
    items[$item_name]=1
done

# S3 Audio
for f in ${(k)s3_audio}; do
    item_name=${f:t:r}
    [[ "$item_name" == _* ]] && continue
    [[ -n "${secondary_images[$item_name]}" ]] && continue
    items[$item_name]=1
done

# S3 Images
for f in ${(k)s3_images}; do
    item_name=${f:t:r}
    [[ "$item_name" == _* ]] && continue
    [[ -n "${secondary_images[$item_name]}" ]] && continue
    items[$item_name]=1
done

# S3 Images Org
for f in ${(k)s3_images_org}; do
    item_name=${f:t:r}
    [[ "$item_name" == _* ]] && continue
    [[ -n "${secondary_images[$item_name]}" ]] && continue
    items[$item_name]=1
done

# S3 Video
for f in ${(k)s3_video}; do
    item_name=${f:t:r}
    [[ "$item_name" == _* ]] && continue
    [[ -n "${secondary_images[$item_name]}" ]] && continue
    items[$item_name]=1
done


echo "\n🔍 Überprüfung gestartet... Hier sind die fehlenden Dateien:\n"
printf "%-25s | %-4s | %-25s | %-4s | %-4s | %-4s\n" "Basisname" "MD" "JPG (Basis + Refs)" "JPG" "MP3" "VID"
echo "--------------------------+------+---------------------------+------+------+------"

has_missing=false

# 3. Überprüfung starten
for item_name in ${(ok)items}; do
    st_md="❌" st_jpg="❌" st_mp3="❌" st_video="❌"
    st_jpg_display=""
    missing_count=0

    # Basis-Dateien checken
    [[ -f "$DIR_MD/$item_name.md" ]] && st_md="✅" || ((missing_count++))
    [[ -n "${s3_audio[$item_name.mp3]}" ]] && st_mp3="✅" || ((missing_count++))
    [[ -n "${s3_images[$item_name.jpg]}" ]] && st_jpg="✅" || ((missing_count++))
    
    # Video Check (verschiedene Formate möglich, meist mp4)
    if [[ -n "${s3_video[$item_name.mp4]}" || -n "${s3_video[$item_name.mov]}" || -n "${s3_video[$item_name.webm]}" || -n "${s3_video[$item_name.avi]}" || -n "${s3_video[$item_name.mkv]}" ]]; then
        st_video="✅"
    else
        ((missing_count++))
    fi

    # JPG Check (Basis + in MD erwähnte)
    required_jpgs=("$item_name.jpg") 

    if [[ "$st_md" == "✅" ]]; then
        # Hier suchen wir nach Basis + Nummern (z.B. BigBang.jpg UND BigBang2.jpg)
        refs=($(grep -oE "${item_name}[0-9]*\.jpg" "$DIR_MD/$item_name.md" 2>/dev/null | sort -u))
        for r in $refs; do
            if [[ "$r" != "$item_name.jpg" ]]; then
                required_jpgs+=("$r")
            fi
        done
    fi

    missing_jpgs=()
    for jpg in $required_jpgs; do
        if [[ -z "${s3_images[$jpg]}" ]]; then
            missing_jpgs+=("$jpg")
        fi
    done

    if [[ ${#missing_jpgs[@]} -gt 0 ]]; then
        st_jpg_display="❌ (Fehlt: ${(j:, :)missing_jpgs})"
        ((missing_count++))
    else
        if [[ ${#required_jpgs[@]} -gt 1 ]]; then
            st_jpg_display="✅ (${#required_jpgs[@]} Bilder)"
        else
            st_jpg_display="✅"
        fi
    fi

    # Nur ausgeben, wenn etwas fehlt
    if (( missing_count > 0 )); then
        printf "%-25s |  %-1s  | %-24s |  %-2s  |  %-2s  |  %-2s \n" "$item_name" "$st_md" "$st_jpg_display" "$st_jpg" "$st_mp3" "$st_video"
        has_missing=true
    fi
done

echo ""
if [[ "$has_missing" == false ]]; then
    echo "🎉 Alles perfekt! Zu jedem Artikel gibt es alle Basisdateien und alle in der MD verlinkten Bilder auf S3."
else
    echo "⚠️  Es gibt Differenzen! Bitte überprüfe die roten ❌ Einträge."
fi
echo ""
