# 📝 Spickzettel: Conventional Commits

Das System der Conventional Commits sorgt für eine saubere, maschinenlesbare und für Menschen leicht verständliche Git-Historie.

## 🏗️ 1. Die Grundstruktur

Jede Commit-Message ist nach folgendem Muster aufgebaut:

```text
<typ>(<bereich>): <kurze beschreibung>

<optionaler detaillierter text (body)>

<optionaler fußnoten-bereich (footer, z.B. für Breaking Changes oder Ticket-Nummern)>
```

* **Typ:**  Was für eine Art von Änderung ist das? (Zwingend erforderlich)

* **Bereich (Scope):**  Wo genau im Projekt wurde die Änderung gemacht? (Optional, aber empfohlen)

* **Beschreibung:**  Zusammenfassung in einem Satz. Beginnt meist klein geschrieben und hat keinen Punkt am Ende.


## 🛠️ 2. Die wichtigsten Typen (Types)

Hier sind die Standard-Kategorien, in die du deine Arbeit einsortierst:

### 🌟 Die großen Zwei (Feature & Bugfix)
* **`feat`** (Feature): Du fügst der Anwendung eine **neue Funktion** hinzu.
  * *Beispiel:* `feat(routing): add route for about page`
* **`fix`** (Bugfix): Du **reparierst einen Fehler** im Code.
  * *Beispiel:* `fix(helper): correct typo in GuruWisdom generator`

### 🧹 Aufräumen & Werkzeuge
* **`chore`** (Routine): **Hausarbeit**. Änderungen an Build-Prozessen, Hilfsskripten oder Werkzeugen, die den echten App-Code nicht berühren.
  * *Beispiel:* `chore(scripts): add docker login script`
* **`refactor`** (Umbau): Du schreibst den **Code sauberer oder strukturierst ihn um**, ohne dass sich das Verhalten für den Endnutzer ändert. Es ist weder ein Bugfix noch ein neues Feature.
  * *Beispiel:* `refactor(docker): switch base image to debian slim`

### 📖 Dokumentation & Tests
* **`docs`** (Dokumentation): Du änderst **nur Textdateien** (wie die `README.md`, `PHPDoc`-Kommentare im Code oder Markdown-Dateien).
  * *Beispiel:* `docs: update setup instructions in readme`
* **`test`** (Tests): Du fügst **neue Tests** hinzu (z. B. PHPUnit) oder korrigierst bestehende.
  * *Beispiel:* `test(guru): add unit test for getWisdom method`

### ⚙️ Infrastruktur & Performance
* **`build`** (System): Änderungen, die das **Build-System oder externe Abhängigkeiten** betreffen (z. B. Composer, npm, Makefile).
  * *Beispiel:* `build(composer): update yiisoft/yii-web to latest version`
* **`ci`** (Continuous Integration): Änderungen an deinen **Automatisierungs-Pipelines** (z. B. GitHub Actions, GitLab CI).
  * *Beispiel:* `ci: add github action for phpstan code analysis`
* **`perf`** (Performance): Eine Code-Änderung, die ausschließlich die **Leistung/Geschwindigkeit** verbessert.
  * *Beispiel:* `perf(db): add database index for faster user lookup`

### 🎨 Kosmetik
* **`style`** (Stil): Änderungen, die den Code-Sinn nicht verändern (z. B. Leerzeichen, Formatierung, fehlende Semikolons ergänzen, PHP-CS-Fixer laufen lassen). *Achtung: Dies ist NICHT für CSS/HTML-Styling gedacht (das wäre ein `feat` oder `fix`)!*
  * *Beispiel:* `style: format all controllers to PSR-12 standard`

### ⏪ Rückgängig machen
* **`revert`** (Rolle rückwärts): Wenn du einen **früheren Commit komplett rückgängig** machst.
  * *Beispiel:* `revert: "feat: add experimental dark mode"`

---

## 🎯 3. Der Bereich (Scope)

Der Bereich in den Klammern `(...)` ist optional, hilft aber extrem, um sofort zu sehen, welches Modul betroffen ist. Du kannst dir die Bereiche für dein Yii3-Projekt selbst ausdenken.

Gute Beispiele für Scopes:
* `(docker)` - Alles rund um Container.
* `(helper)` - Deine Utility- und Guru-Klassen.
* `(views)` - Deine HTML/Twig-Templates.
* `(config)` - Änderungen in der `config/routes.php` etc.

---

## 🧨 4. Breaking Changes (Achtung, Einsturzgefahr!)

Wenn du etwas änderst, das dazu führt, dass alter Code nicht mehr funktioniert (z. B. du änderst den Namen einer wichtigen Methode in `BaseGuruWisdom`, die überall aufgerufen wird), musst du das markieren!

Dafür gibt es zwei Wege:
1. Ein **Ausrufezeichen `!`** hinter dem Typ/Scope:
   * `refactor(helper)!: rename getWisdom to generateWisdom`
2. Das Wort **BREAKING CHANGE:** im Text (Body/Footer):
   ```text
   refactor(helper): rename core wisdom method

   BREAKING CHANGE: The method `getWisdom()` was renamed to `generateWisdom()`. All controllers must be updated.
   ```

### 5. Git Commit-Nachricht nachträglich ändern (Lokal)

Wenn du einen Commit lokal durchgeführt hast, aber noch nicht gepusht hast, kannst du die Nachricht im Nachhinein anpassen. Hier sind die zwei Methoden dafür:

Wenn du nur die Nachricht des **letzten** Commits ändern willst, nutzt du den `--amend` Befehl.

* **Direkt in der Konsole**
  ```bash
  git commit --amend -m "Deine neue, bessere Commit-Message" or for long text git commit --amend 

* **etwas älterer Commit ist (Interactive Rebase)**
Falls du schon zwei oder drei Commits gemacht hast und die Nachricht von einem weiter hinten liegenden ändern willst, brauchst du das "Präzisionswerkzeug":
  ```bash
  git rebase -i HEAD~3 (Die 3 steht für die Anzahl der letzten Commits, die du sehen willst).
  Es öffnet sich eine Liste. Ändere vor dem entsprechenden Commit das Wort pick in reword (oder einfach nur r).
  Speichere und schließe den Editor.
  Git wird dich nun nacheinander fragen, wie die neuen Nachrichten für diese Commits lauten sollen.