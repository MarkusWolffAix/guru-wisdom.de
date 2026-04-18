# 🐳 Docker Cheat Sheet: Alles im Griff (inkl. Speicher-Optimierung)

Diese Übersicht hilft dir, deine Docker-Umgebung sauber zu halten und die berüchtigten "900MB-Images" zu analysieren.

---

## 📦 1. Images (Die Vorlagen)
*Images sind schreibgeschützt. Wenn du sie änderst, entsteht ein neues Image oder ein neuer Layer.*

| Befehl | Beschreibung |
| :--- | :--- |
| `docker images` | Listet alle lokalen Images auf (inkl. Repository, Tag und Größe). |
| `docker build -t name:tag .` | Baut ein Image aus dem aktuellen Verzeichnis. |
| `docker build --no-cache -t name .` | Erzwingt einen kompletten Neubau ohne Nutzung alter Layer. |
| `docker rmi <image_id>` | Löscht ein spezifisches lokales Image. |
| `docker history --human <id>` | **WICHTIG:** Zeigt, welcher Befehl im Dockerfile wie viel Platz frisst. |
| `docker pull <image>` | Lädt die neueste Version eines Images (z.B. von DockerHub) herunter. |

---

## 🚀 2. Container (Die Instanzen)
*Ein Container ist ein laufender Prozess basierend auf einem Image.*

| Befehl | Beschreibung |
| :--- | :--- |
| `docker ps` | Zeigt alle aktuell laufenden Container an. |
| `docker ps -a` | Zeigt alle Container an (auch gestoppte/abgestürzte). |
| `docker run -d --name app -p 80:80 img` | Startet Image `img` im Hintergrund, benennt es `app` und mappt Ports. |
| `docker stop <id/name>` | Hält einen laufenden Container an. |
| `docker rm <id/name>` | Löscht einen gestoppten Container. |
| `docker rm -f $(docker ps -aq)` | **Radikal:** Löscht absolut alle Container auf dem System. |

---

## 🔍 3. Analyse & Deep Dive
*Befehle, um herauszufinden, was im Container passiert oder warum er so groß ist.*

* **Speicherplatz-Check:**
    `docker system df`
    Zeigt die globale Belegung von Images, Containern und Build-Cache.
* **Interaktive Shell:**
    `docker exec -it <container_id> sh` (oder `bash`)
    Springt direkt in den laufenden Container.
* **Größen-Check im Container:**
    `docker run --rm <image_id> sh -c "du -sh /app/* | sort -h"`
    Listet die Größe aller Dateien innerhalb des `/app`-Ordners auf.
* **Logs einsehen:**
    `docker logs -f <container_id>`
    Folgt den Live-Ausgaben deiner App.
    `docker compose logs -f app`  
    Zeigt die Logs für die Entwicklung gut an 

---

## 🛠️ 4. Docker Compose
*Für Multi-Container-Setups (PHP + MySQL + Nginx).*

| Befehl | Beschreibung |
| :--- | :--- |
| `docker compose up -d` | Startet alle Dienste im Hintergrund. |
| `docker compose build --pull` | Baut Images neu und sucht nach Updates der Basis-Images. |
| `docker compose down` | Stoppt und entfernt alle Container und Netzwerke des Projekts. |
| `docker compose logs -f <service>` | Zeigt Logs für einen spezifischen Dienst (z.B. `app`). |

---

## 🧹 5. Cleanup (Platz schaffen)
*Docker löscht standardmäßig nichts von selbst. Das hier hilft gegen volle Festplatten.*

* **Ungenutzte Daten löschen (Sicher):**
    `docker system prune`
    Löscht gestoppte Container, ungenutzte Netzwerke und "Dangling Images" (ohne Namen).
* **Den großen Frühjahrsputz (Achtung!):**
    `docker system prune -a --volumes`
    Löscht ALLES, was nicht gerade aktiv von einem Container genutzt wird.
* **Build-Cache leeren:**
    `docker builder prune`
    Löscht nur den Cache, der beim Bauen der Images entstanden ist.

---

## 💡 Profi-Tipps für kleine Images
1.  **Multi-Stage Builds:** Baue deine App in einem Image (mit Compilern) und kopiere nur das Ergebnis in ein minimales Final-Image.
2.  **`.dockerignore`:** Erstelle immer eine Whitelist, um `.git`, `node_modules` oder Caches gar nicht erst hochzuladen.
3.  **Layer-Combining:** Verbinde `apt-get update && apt-get install && rm -rf ...` in einem einzigen `RUN`-Befehl.

## DevOps Verantwortung 
| Rolle | Verantwortungsbereich | Werkzeug |
| :--- | :--- | :--- |
| **Dev** | **Inhalt des Pakets:** Code, Runtime, App-Logik. | `Dockerfile` |
| **Ops** | **Transport des Pakets:** Server-Hardware, Netzwerk, Skalierung. | `docker-compose.yml`, CI/CD |