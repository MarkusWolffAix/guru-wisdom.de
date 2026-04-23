#!/bin/zsh

# Ins Verzeichnis wechseln. Wenn das fehlschlägt (|| exit), bricht das Skript ab.
cd /Users/markuswolff/guru-wisdom.de || exit

# Alle Änderungen stagen
git add .
DIFF="$(git diff --staged)"

# Sicherheitsprüfung: Ist überhaupt etwas passiert?
if [ -z "$DIFF" ]; then
    echo "⚠️ No Changes found.Abbort."
    exit 0
fi

echo "🧠 Gemini is analysing for changings..."

PROMPT="Generate a strict Conventional Commit message for the following git diff. Respond ONLY with the final commit message. No markdown, no yapping, no explanations. Diff: $DIFF"

# API Aufruf (Fehler werden ins Nichts umgeleitet)
MSG=$(gemini -p "$PROMPT" 2>/dev/null)

# Sicherheitsprüfung: Hat Gemini überhaupt geantwortet?
if [ -z "$MSG" ]; then
    echo "❌ Errors. Abbort"
    exit 1
fi

# Der finale Commit
git commit -m "$MSG"

echo "✅ Finish!"

