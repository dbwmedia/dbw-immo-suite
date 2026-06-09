# Changelog

Alle wesentlichen Aenderungen an der DBW Immo Suite werden hier dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.1.0/)
und dieses Projekt verwendet [Semantic Versioning](https://semver.org/lang/de/).

---

## [1.16.1] — 2026-06-09

### Sicherheit
- **Zip-Slip Protection** — ZIP-Extraktion prueft jetzt alle Eintrags-Pfade auf `..` und fuehrende `/` bevor extrahiert wird. Manipulierte ZIPs koennen keine Dateien mehr ausserhalb des Zielverzeichnisses schreiben.
- **XSS via Post-Titel** — `the_title()` durch `esc_html(get_the_title())` ersetzt in der Detailseite.
- **Lizenzschluessel gehasht in DB** — Aktivierte Keys werden als SHA256-Hash statt Klartext in `wp_options` gespeichert.
- **CC-Email Sanitisierung** — `contact_cc_email` wird jetzt im `sanitize()`-Callback mit `sanitize_email()` bereinigt.

### Behoben
- **Accessibility: Focus-Indikatoren** — `outline:none` nur noch auf `:focus:not(:focus-visible)`. Keyboard-Navigation zeigt jetzt sichtbare Fokus-Ringe (`focus-visible`).
- **Doppel-Escaping in Preis/m²** — Ortsnamen mit Sonderzeichen wurden doppelt escaped (`&amp;amp;`). `esc_html()` nur noch bei der Ausgabe, nicht innerhalb von `sprintf()`.
- **PDF-Expose Nonce** — `get_the_ID()` durch `get_queried_object_id()` ersetzt fuer zuverlaessige Nonce-Validierung.
- **localStorage Safari** — `setItem()`/`getItem()` in try/catch gewrapped (crashed in Safari Private Browsing).
- **PLZ 11xxx Berlin** — Fehlende PLZ-Zuordnung im Finanzrechner ergaenzt (Grunderwerbsteuer war 5.0% statt 6.0%).
- **Uninstall Cleanup** — 11 fehlende Customizer-Settings, License-Options und Import-Transient in `uninstall.php` ergaenzt.

---

## [1.16.0] — 2026-06-09

### Hinzugefuegt
- **CC-Adresse fuer Kontaktanfragen** — Neues Feld "CC-Adresse (optional)" unter Darstellung → Kontaktanfragen in den Plugin-Einstellungen. Jede Kontaktanfrage wird zusaetzlich als Kopie an diese Adresse gesendet (z.B. info@maklerfirma.de). Die primaere Mail geht weiterhin an die Kontaktperson der Immobilie (oder den WP-Admin als Fallback).

---

## [1.15.6] — 2026-06-09

### Behoben
- **Listenansicht: Meta-Grid** — Meta-Informationen (Wohnflaeche, Zimmer, Schlafzimmer) wurden in der Listenansicht abgeschnitten. Statt 4 Spalten in einer Reihe zu quetschen jetzt konsistentes 2x2-Grid wie in der Kachelansicht.

---

## [1.15.4] — 2026-06-09

### Geaendert
- **MPA View Transition entfernt** — Cross-Document View Transition (Archiv → Detail) verursachte sichtbares Flackern beim Seitenwechsel und wurde entfernt. Die same-page View Transition beim Grid/List-Wechsel bleibt erhalten.
- **Zurueck-Button** nutzt jetzt `history.back()` statt hartem Archiv-Link — navigiert zur tatsaechlichen Herkunftsseite (Homepage, Grid-Block, Archiv Seite 2 etc.). Archiv-Link bleibt als Fallback fuer Direktzugriffe.

---

## [1.15.2] — 2026-06-09

### Hinzugefuegt
- **View Transition beim Grid/List-Wechsel** — Sanfter Crossfade (0.25s) via View Transition API beim Umschalten zwischen Kachel- und Listenansicht. Fallback fuer aeltere Browser. `prefers-reduced-motion` wird respektiert.

### Behoben
- **Leaflet 404 auf dem Server** — `.gitignore` hatte `vendor/` ohne fuehrenden Slash, was auch `assets/vendor/leaflet/` ausschloss. Leaflet JS/CSS und Marker-Images waren nicht im Repo → 404 → `L is not defined`. Fix: `/vendor/` (nur Root) + Vendor-Dateien getrackt.

---

## [1.15.1] — 2026-06-09

### Hinzugefuegt
- **CTA-Button im Immo Grid** — Neuer optionaler "Zu allen Immobilien"-Button unter dem Grid. Im Gutenberg-Block ueber Sidebar-Panel "CTA-Button" aktivierbar mit anpassbarem Text und URL (Default: Immobilien-Archiv). Im Shortcode: `[dbw_immo_grid cta="yes" cta_text="Alle Objekte" cta_url="/immobilien/"]`.

### Behoben
- **Pagination im Grid-Block** — Pagination nutzte `paginate_links()` ohne Wrapper-Markup, sodass die gestylten `.dbw-page-item` CSS-Selektoren nicht griffen. Jetzt identische Markup-Struktur wie im Archiv (Liste mit `<li class="dbw-page-item">`).

---

## [1.15.0] — 2026-06-09

### Hinzugefuegt
- **Karten-Consent Toggle** — Neuer Customizer-Schalter "Karten-Consent anzeigen (fuer Cookie-Tools wie Borlabs)". Standardmaessig **deaktiviert**: Karte wird direkt geladen ohne Consent-Placeholder. Aktivieren fuer Instanzen mit Borlabs Cookie o.ae., damit der bisherige "Karte laden"-Placeholder mit Borlabs-Integration erscheint.

### Behoben
- **Karte laedt nicht ohne Cookie-Tool** — Auf sauberen WP-Instanzen ohne Borlabs wurde die Karte nie sichtbar, da der Consent-Placeholder immer angezeigt wurde, aber kein Cookie-Tool den Consent-Flow ausloeste. Jetzt wird die Karte standardmaessig direkt geladen.
- **Infra-Score Hover-Effekt** — Dunkler Hintergrund beim Hover/Focus auf Kategorie-Balken (Einkaufen, Bildung, Verkehr) entfernt. Theme-Styles ueberschrieben den Button-Style — jetzt explizit zurueckgesetzt.
- **WhatsApp-Button unterstrichen** — Text-Unterstreichung im Sidebar-CTA entfernt. `text-decoration: none` im Base-Style `.dbw-cta` ergaenzt, damit alle CTA-Buttons konsistent sind (egal ob `<a>` oder `<button>`).

---

## [1.14.4] — 2026-06-05

### Behoben
- **503-Crash beim Speichern der Einstellungen** — Endlosrekursion im `sanitize()`-Callback behoben. `PageGenerator::create_reference_page()` rief `update_option()` auf dieselbe Option auf, was `sanitize()` erneut triggerte und eine Endlosschleife ausloeste, die PHP-FPM zum Absturz brachte.
- **XML-Import auf PHP 8.2** — `libxml_disable_entity_loader(true)` blockierte auf PHP 8.2 das Laden lokaler XML-Dateien komplett ("failed to load external entity"). Aufruf wird auf PHP 8.0+ uebersprungen (dort standardmaessig deaktiviert).
- **ZIP mit Unterordnern** — AJAX-Import suchte XMLs nur im Root des entpackten Verzeichnisses. ZIPs mit Unterordner-Struktur (z.B. immonex Demo-Daten) wurden nicht verarbeitet. Jetzt rekursive Suche wie im Cron-Import.
- **PHP 8.2 Warnungen** — `Undefined array key "anrede"` und `Automatic conversion of false to array` in PageGenerator behoben.

---

## [1.14.0] — 2026-06-05

### Hinzugefuegt
- **Lizenz-System** — 30 vorgenierte Lizenzschluessel (SHA256-gehasht im Code, Klartext-Keys in `LICENSE-KEYS.txt`). Ohne gueltige Lizenz wird nur der Admin-Bereich geladen (Settings + Lizenz-Tab). Frontend, Import und Bloecke sind ohne Key deaktiviert.
- **Lizenz-Tab** in den Plugin-Einstellungen mit Aktivierungsformular und Status-Anzeige.
- **GitHub Auto-Updater** — Plugin-Updates direkt ueber das WordPress-Dashboard via GitHub (plugin-update-checker v5.7). Kein wordpress.org noetig.
- **Media Cleanup** — Beim endgueltigen Loeschen einer Immobilie werden alle zugehoerigen Mediathek-Eintraege automatisch mit entfernt. Bilder bleiben erhalten solange der Post im Papierkorb liegt.

---

## [1.13.0] — 2026-06-03

Umfassendes Audit-Hardening: Sicherheit, Accessibility, SEO und Performance.

### Sicherheit
- **Escaping Best Practices** — `esc_html()` auf Preis-Labels in ContactModal (Sticky-Bar + Quickfacts), HTML-Entities durch UTF-8-Zeichen ersetzt. `esc_attr()`/`esc_html()` in Settings-Callbacks (`checkbox_callback`, `text_callback`).
- **Inline-JS entfernt** — `onmouseover`/`onmouseout` auf Lightbox-Buttons durch CSS `:hover`/`:focus-visible` ersetzt.

### Accessibility
- **WCAG AA Kontrast** — Meta-Label-Farbe von `#95a5a6` (2.8:1) auf `#767676` (4.5:1) angehoben (7 Stellen).
- **Lightbox alt-Text** — Bildbeschreibungen aus Gallery-Daten werden jetzt an die Lightbox durchgereicht und als `alt`-Attribut gesetzt.
- **ARIA-Attribute** — `aria-label`, `aria-expanded`, `aria-controls` auf Filter-Toggle. `aria-label`, `aria-pressed` auf View-Switcher (Grid/Liste). Agent-Bild in Success-View hat jetzt `alt="Name"`.
- **Lightbox Focus-Visible** — Buttons haben jetzt sichtbaren Fokus-Ring per CSS statt JS-Hover.

### SEO
- **Archiv-OG-Tags** — `og:url`, `og:image` (Fallback: Site-Icon/Logo) und `og:site_name` auf Archiv- und Taxonomie-Seiten.
- **og:type** — Single-Seiten nutzen jetzt `article` statt `website`.
- **Title-Filter** — `document_title_parts` greift jetzt auch fuer Archiv- und Taxonomie-Seiten.
- **JSON-LD @id** — `@id` mit `#listing`-Fragment fuer Entity-Deduplizierung im Schema.

### DSGVO
- **Privacy-API** — `wp_privacy_personal_data_exporter`/`_eraser` Stub-Hooks registriert (neue `Privacy.php`). Plugin speichert kein PII, aber die Hooks signalisieren DSGVO-Konformitaet.

### Performance
- **Admin-JS Scope** — `admin.js` wird nur noch auf Plugin-eigenen Seiten geladen (Import, Settings, Property-Edit) statt auf allen Admin-Seiten.
- **Redundanter Meta-Call** — Doppelter `get_post_meta()` fuer Energieklasse im Single-Template entfernt.
- **Kontaktperson-Bild** — `width`/`height`-Attribute und `loading="lazy"` ergaenzt.

### Code Quality
- **Status-Labels i18n** — Fallback-Strings `Referenz`, `Verkauft`, `Reserviert` in `__()` gewrapped.

---

## [1.12.2] — 2026-06-03

Customizer-Erweiterungen und Kleinigkeiten.

### Hinzugefuegt
- **Customizer: Abstand nach oben** — separater Regler fuer Archiv (Default: 6rem) und Detailseite (Default: 2rem) unter "Archiv & Suche" bzw. "Detailansicht". Erlaubt individuelle Anpassung an Theme-Header-Hoehe.

### Geaendert
- **Archiv-Template** — Hardcodiertes `padding: 6rem 0rem` entfernt, wird jetzt vom Customizer gesteuert.
- **frontend.css** — Hardcodiertes `padding-top: 6rem` auf `#dbw-immo-suite` entfernt.

### Behoben
- **Doppelte Telefonnummer** — Redundanter Telefon-Link unterhalb der CTA-Buttons (WhatsApp/Anfragen) in der Sidebar entfernt. Nummer bleibt nur beim Ansprechpartner.
- **Karten-Platzhalter** — Pin-Icon und Text vertikal+horizontal zentriert (flexbox).

---

## [1.12.1] — 2026-06-03

DSGVO-Konformitaet und kritische Bugfixes aus dem Audit.

### DSGVO / Datenschutz
- **Leaflet lokal gebuendelt** — CSS, JS und Marker-Images in `assets/vendor/leaflet/` statt vom externen CDN (unpkg.com). Keine IP-Uebertragung an Dritte mehr beim Laden der Library.
- **Karten-Consent-Platzhalter** — OpenStreetMap-Karte laedt nicht mehr automatisch. Stattdessen "Karte laden"-Platzhalter mit Hinweis "Dabei werden Daten an OpenStreetMap uebertragen." Karte wird erst nach explizitem Klick initialisiert.
- **Borlabs Cookie Integration** — `data-borlabs-cookie-type` und `data-borlabs-cookie-id="openstreetmap"` Attribute auf dem Platzhalter. Automatische Karten-Initialisierung wenn Consent bereits ueber Borlabs erteilt wurde. Event-Listener fuer nachtraegliche Einwilligung (`borlabs-cookie-consent-saved`).

### Behoben
- **Privacy-Link im Kontakt-Modal** — `esc_html__()` escaped die `<a>`-Tags des Datenschutz-Links weg, sodass der Link nicht klickbar war. Gefixt: `__()` mit separatem `esc_url()` auf dem href-Attribut.
- **Undefinierte Variable `$xml_path_raw`** — PHP Notice im Importer-Log behoben (Variable wurde nach Refactoring auf `resolve_import_path()` nicht entfernt).
- **ABSPATH-Guard** in `Settings.php` ergaenzt (einzige Datei die ihn noch nicht hatte).

---

## [1.12.0] — 2026-06-03

Zwei neue Features: WhatsApp-Kontakt-Button als zusaetzlicher Kommunikationskanal und Preis-pro-Quadratmeter-Vergleich mit Durchschnittswerten pro Standort.

### Hinzugefuegt
- **WhatsApp-Kontakt-Button** — vollstaendige Integration an 4 Stellen:
  - **Sidebar CTA-Stack** — gruener Button (#25D366) zwischen "Immobilie anfragen" und Telefon-Link, offizielles WhatsApp-SVG-Icon
  - **Floating-Button** — 56x56px Kreis (fixed bottom-right), Puls-Animation, verschwindet bei offenem Modal (`body:has(dialog[open])`), Mobile-Position ueber Sticky-Bar
  - **Mobile Sticky-CTA-Bar** — WhatsApp-Icon-Button neben "Anfragen"
  - **Modal Success-Screen** — "Oder direkt per WhatsApp schreiben"-Link mit Icon
- **WhatsApp-URL-Generierung** — `https://wa.me/{nummer}?text={nachricht}` mit `rawurlencode()`, Nummer-Normalisierung (nur Ziffern, kein +)
- **Vorbefuellte Nachricht** — Platzhalter `{ansprechpartner}`, `{titel}`, `{url}`, `{name}`, beruecksichtigt Du/Sie-System via `dbw_anrede()`
- **Nummer-Logik** — Prioritaet: globale Override-Nummer → Kontaktperson des Objekts → Button wird ausgeblendet
- **Neue Datei `WhatsAppButton.php`** (`src/Frontend/`) — zentrale Klasse mit `get_whatsapp_url()`, `render_cta_button()`, `render_floating_button()`, `render_success_link()`
- **Backend-Einstellungen** (Tab "Darstellung", Sektion "WhatsApp"):
  - `whatsapp_enabled` — WhatsApp-Button global aktivieren (Checkbox)
  - `whatsapp_floating` — Floating-Button anzeigen (Checkbox)
  - `whatsapp_number_override` — globale WhatsApp-Business-Nummer (Tel-Input, ueberschreibt Kontaktperson)
  - `whatsapp_cta_text` — Button-Beschriftung (Text, Default: "Per WhatsApp anfragen")
  - `whatsapp_message_template` — Nachrichtenvorlage mit Platzhaltern (Textarea)
- **Customizer-Toggles** — `dbw_immo_single_show_whatsapp` (Default: an), `dbw_immo_whatsapp_floating` (Default: aus)
- **Preis-pro-Quadratmeter-Vergleich** — automatische Berechnung und Vergleich mit Standort-Durchschnitt:
  - **Neue Datei `PriceComparison.php`** (`src/Frontend/`) — Berechnung, Transient-Caching, Rendering
  - Abweichungs-Indikator (ueber/unter/im Durchschnitt) mit Badge
  - Optional auf Archiv-Karten als Badge
  - Backend-Einstellungen: Show/Hide-Toggles, Min. Vergleichsobjekte, Cache-Dauer
  - Customizer-Toggles fuer Einzelansicht und Archiv

### Geaendert
- **ContactModal.php** — WhatsApp-Integration an 3 Stellen (CTA-Stack, Success-Screen, Sticky-Bar)
- **Plugin.php** — `WhatsAppButton` und `PriceComparison` im Loader registriert
- **Customizer.php** — 4 neue Toggles (WhatsApp anzeigen, WhatsApp Floating, Preis/m² Single, Preis/m² Archiv)
- **Settings.php** — 2 neue Sektionen "WhatsApp" und "Preis pro m²" mit Sanitization
- **frontend.css** — ~100 Zeilen neue Styles (WhatsApp-Button, Floating, Pulse-Animation, Print-Hide, Success-Link)
- **Version** auf 1.12.0 aktualisiert

### Technische Details
- Kein JavaScript noetig — reine `<a href>` Links (Desktop: WhatsApp Web/App, Mobile: WhatsApp App)
- `rel="noopener"` und `aria-label` auf allen externen Links
- Eigene CSS-Klassen fuer Event-Tracking (Google Analytics etc.)
- Floating-Button wird bei `dialog[open]` via CSS `:has()` ausgeblendet (kein JS)
- `@media (prefers-reduced-motion)` deaktiviert Pulse-Animation
- Transient-basierter Cache fuer Preis/m²-Durchschnittswerte

---

## [1.11.0] — 2026-06-03

Zwei neue Analyse-Features: Visueller Infrastruktur-Score und Energiekosten-Rechner auf der Detailseite.

### Hinzugefuegt
- **Infrastruktur-Score** — visueller Score (0–10) basierend auf `distanz_*` Meta-Feldern, aehnlich Walk Score
  - SVG-Ring-Animation mit Score-Zahl, Farbe (Gruen 8+, Blau 6-7.9, Orange 4-5.9, Rot 0-3.9)
  - 5 gewichtete Kategorien: OEPNV (25%), Einkaufen (20%), Bildung (25%), Gastronomie (10%), Verkehr (20%)
  - Horizontale Fortschrittsbalken mit SVG-Icons und aufklappbaren Distanz-Details
  - Gewichtungs-Umverteilung bei fehlenden Kategorien, Mindest-Datenbasis (3 Felder)
  - Expose-Integration (Seite 3 Lage)
- **Neue Datei `InfrastructureScore.php`** (`src/Frontend/`)
- **Neue Datei `infra-score.js`** (`assets/js/`) — Ring- und Balken-Animation via IntersectionObserver
- **Energiekosten-Rechner** — geschaetzte monatliche/jaehrliche Heizkosten inline im Energieausweis-Bereich:
  - Berechnung: Endenergieverbrauch × Wohnflaeche × Preis/kWh
  - Interaktiver Energiepreis-Slider pro kWh
  - Automatische Erkennung des Energietraegers (9 Typen: Gas, Oel, Strom, Fernwaerme, Holz, Pellet, Waermepumpe, Fluessiggas, Solar)
  - 9 konfigurierbare Energiepreise in den Einstellungen (Tab "Rechner")
  - Aktivierbar per Setting `energy_show_costs`

### Geaendert
- **single-immobilie.php** — alte Distanz-Liste ersetzt durch `InfrastructureScore::render()`
- **expose.php** — `InfrastructureScore::render_expose()` auf Seite 3
- **EnergyRenderer.php** — Energiekosten-Rechner integriert
- **Plugin.php** — `InfrastructureScore` im Loader registriert
- **Customizer.php** — Toggle "Infrastruktur-Score anzeigen"
- **Settings.php** — Sektion "Energiekosten-Schaetzung" im Tab "Rechner" mit 9 Energiepreisfeldern + Toggle
- **frontend.css** — ~150 Zeilen neue Styles (Ring, Balken, Kategorien, Details, Energiekosten)

---

## [1.10.0] — 2026-06-02

Professionelle Expose-Ansicht ersetzt den bisherigen Drucken-Button. Standalone-Seite ohne Theme-Header/Footer, optimiert fuer A4-Druck und PDF-Export.

### Hinzugefuegt
- **Expose-Seite** (`?expose=1`) — standalone HTML-Seite ohne Theme, optimiert fuer A4-Druck/PDF-Export via Browser-Dialog "Als PDF speichern"
- **Neue Datei `PdfExpose.php`** (`src/Frontend/`) — Controller-Klasse mit `template_redirect`-Hook, Nonce-Schutz, Datensammlung aus allen Meta-Feldern
- **Neue Datei `expose.php`** (`templates/`) — 5-seitiges Expose-Template:
  - Seite 1 (Cover): Hero-Bild volle Breite, Objektart-Badge, Titel, Adresse, Key Facts Strip (Wohnflaeche, Zimmer, Grundstueck, Baujahr, Preis), Makler-Branding Footer
  - Seite 2 (Details): Zweispaltiges Layout mit Beschreibung + Ausstattung (Features als Badges) links, Eckdaten-Tabelle (alle Flaechen + Preise) rechts
  - Seite 3 (Lage & Energie): Lagebeschreibung, Entfernungen-Tabelle, Energiedaten mit farbiger A+–H Skala
  - Seite 4 (Bilder): 2-spaltiges Bildergrid (bis zu 6 Bilder) + Grundrisse
  - Letzte Seite (Kontakt): Ansprechpartner-Card mit Foto, Firmendaten aus Makler-SEO-Einstellungen, rechtlicher Disclaimer
- **Auto-Print-Dialog** — Print-Dialog oeffnet sich automatisch nach Seitenladen (600ms Delay fuer Bildladen)
- **Screen-Vorschau** — Info-Bar mit manuellem "PDF speichern / Drucken"-Button
- **Nonce-geschuetzte URLs** — Bot-Schutz fuer Expose-Links via `wp_nonce_url()`
- **`noindex, nofollow`** auf Expose-Seiten gegen Duplicate Content
- **Du/Sie System** — Ansprechpartner-Ueberschrift und Info-Bar nutzen `dbw_anrede()`
- **Customizer-Farben** — Accent und Primary aus Customizer-Settings uebernommen
- **`print-color-adjust: exact`** fuer korrekte Energieskala-Farben im Druck
- **`@page :first`** mit randlosen Margins fuer Cover-Hero-Bild

### Geaendert
- **single-immobilie.php** — Drucken-Button (`<button onclick="window.print()">`) ersetzt durch Expose-Link (`<a target="_blank">`), neues Download-SVG-Icon statt Drucker-Icon
- **Customizer.php** — Toggle-Label von "Drucken-Button im Slider anzeigen" auf "Expose/PDF-Button im Slider anzeigen"
- **Plugin.php** — `PdfExpose` im Loader registriert
- **Version** auf 1.10.0 aktualisiert

### Technische Details
- Null externe Abhaengigkeiten (kein mPDF, kein Composer, kein JavaScript-Build)
- Standalone-HTML mit eingebettetem CSS, `@page`-Regeln fuer A4-Seitenumbrueche
- Wiederverwendet bestehende Helper-Funktionen (`dbw_format_number()`, `dbw_format_phone()`, `dbw_anrede()`)
- Org-Daten aus `dbw_immo_suite_settings` (Makler-SEO Tab)
- Bilder via `wp_get_attachment_image_url($id, 'large')` fuer Druckqualitaet
- `update_meta_cache()` vor Gallery-Loop gegen N+1

---

## [1.9.0] — 2026-06-02

Neuer interaktiver Kaufnebenkosten- & Finanzierungsrechner auf der Detailseite fuer Kaufobjekte.

### Hinzugefuegt
- **Kaufnebenkosten-Rechner** — automatische Aufschluesselung: Kaufpreis, Grunderwerbsteuer (PLZ-basiert nach Bundesland), Notarkosten, Grundbuchamt, Maklerprovision, Gesamtkosten
- **Finanzierungsrechner** — 3 interaktive Slider (Eigenkapital, Zinssatz, Tilgung) mit Live-Berechnung von Darlehenssumme, monatlicher Rate und Zinskosten nach 10 Jahren (monatsgenaue Amortisation)
- **PLZ-zu-Bundesland-Mapping** — alle 16 Bundeslaender mit korrekten Grunderwerbsteuersaetzen (3,5%–6,5%)
- **Backend-Settings** — 5 neue Felder im Tab "Darstellung" unter "Finanzierungsrechner": Notarkosten %, Grundbuchamt %, Default-Zinssatz, Default-Tilgung, Grunderwerbsteuer Override
- **Customizer-Toggle** — `dbw_immo_single_show_calculator` (Standard: an)
- **Neue Datei `FinanceCalculator.php`** (`src/Frontend/`) — PHP-Klasse mit `init()` und statischem `render()`
- **Neue Datei `finance-calculator.js`** (`assets/js/`) — Vanilla JS, kein jQuery
- **Zwei-Spalten Card-Layout** — Kaufnebenkosten-Card links, Finanzierung-Card rechts mit Icon-Headern und Accent-Unterstreichung
- **Provision-Anzeige** — exakter Prozentwert aus Meta-Feld (z.B. "3,57 %") statt gerundeter Wert
- **Du/Sie System** — alle User-facing Strings nutzen `dbw_anrede()` via `wp_localize_script()`
- **Print** — Rechner wird beim Drucken ausgeblendet
- **Reduced Motion** — Slider-Animationen deaktiviert bei `prefers-reduced-motion`
- **Responsive** — Cards stapeln ab 900px, optimiertes Mobile-Layout ab 600px

### Geaendert
- **Plugin.php** — `FinanceCalculator` im Loader registriert
- **Customizer.php** — Toggle fuer Finanzierungsrechner hinzugefuegt
- **Settings.php** — Sektion "Finanzierungsrechner" mit 5 Number-Feldern, Sanitization mit min/max Clamp
- **single-immobilie.php** — Render-Aufruf zwischen Lage und Energie eingefuegt
- **frontend.css** — ~180 Zeilen neue Styles (Cards, Rows, Sliders, Result-Box, Print-Hide)
- **Version** auf 1.9.0 aktualisiert

---

## [1.8.0] — 2026-06-02

Umfassendes Security-Hardening, Performance-Optimierung, Accessibility-Verbesserungen und UI/UX-Modernisierung. Ergebnis eines 3-Runden-Audits.

### Sicherheit
- **XXE-Schutz** — Neue `safe_load_xml()` Methode mit `LIBXML_NONET` und `libxml_disable_entity_loader()` fuer alle XML-Parsing-Stellen
- **Path Traversal** — `realpath()` Validierung in `upload_image()`, `ajax_process_batch()` und `ajax_validate_path()`
- **Upload-Whitelist** — Nur jpg/jpeg/png/gif/webp/pdf erlaubt (kein SVG wegen Stored XSS)
- **Email Header Injection** — Newlines werden aus `$name` im Reply-To Header entfernt
- **XSS im Admin** — Debug-Tab in PropertyDetails nutzt `esc_html()` statt `print_r()`
- **Admin XSS** — `showError()` in admin.js nutzt `.text()` statt `.html()` fuer Fehlermeldungen
- **Nonce-Fix** — JS und PHP verwenden konsistent `dbw_immo_validate_path` als Nonce-Action
- **SQL Injection** — `$wpdb->prepare()` in uninstall.php LIKE-Query und Filter.php meta_key Joins
- **Rate Limiting** — 2-Minuten-Cooldown pro Email+IP auf Kontaktformular
- **Post-Validierung** — ContactForm prueft ob Property existiert und korrekten Post-Type hat
- **ABSPATH Guards** — Alle 25 PHP-Dateien in src/ (nach namespace-Deklaration)
- **Log-Schutz** — Import-Log in `plugin/logs/` mit `.htaccess` Deny-All statt oeffentlichem wp-content
- **Sichere Temp-Files** — `wp_tempnam()` mit Cleanup bei Fehler statt `sys_get_temp_dir()`
- **Error Leaking** — Exception-Messages gehen ins Log statt an den User
- **Path Traversal Settings** — Custom Import-Pfad wird per `realpath()` gegen ABSPATH geprueft
- **Pfad-Konsolidierung** — Duplizierte Pfad-Aufloesung in `resolve_import_path()` zusammengefuehrt

### Performance
- **DB-Queries** — `get_post_custom()` statt 42 einzelner `get_post_meta()` Calls auf Single-Page (~100 → ~20 Queries)
- **CardRenderer** — `get_post_custom()` statt 8 einzelner Calls (×12 Karten = ~84 Queries gespart pro Archiv)
- **Attachment Meta Cache** — `update_meta_cache()` vor Gallery-Loop verhindert N+1
- **Importer Queries** — Direkte `$wpdb->prepare()` statt `WP_Query` fuer openimmo_id und Attachment-Lookups
- **Garbage Collection** — `array_flip()` statt `in_array()` fuer O(1) statt O(N) Lookups
- **Responsive Images** — `srcset`, `sizes`, `width`/`height` Attribute auf allen Bildern
- **Lazy Loading** — `loading="lazy"` + `decoding="async"` auf Galerie- und Kartenbildern
- **Fetch Priority** — Erstes Galerie-Bild mit `fetchpriority="high"` fuer LCP
- **Thumbnails** — `wp_get_attachment_image()` mit `thumbnail` Size statt `large` fuer 80px Thumbs
- **Similar Properties** — `orderby date DESC` statt `orderby rand` (kein Full Table Scan mehr)
- **Leaflet** — `wp_enqueue_style/script` statt inline `<link>`/`<script>` Tags
- **Memory** — `wp_raise_memory_limit('admin')` statt `@ini_set('memory_limit', '2048M')`

### Accessibility
- **Keyboard Navigation** — Gallery-Slides und Grundrisse sind jetzt `<button>` statt `<div onclick>`
- **Focus Trap** — Lightbox haelt Tab-Focus innerhalb des Overlays
- **Focus Return** — Lightbox gibt Focus zurueck an Trigger-Element beim Schliessen
- **Focus Visible** — Globale `:focus-visible` Styles fuer alle interaktiven Elemente
- **aria-labels** — Zurueck-Link, Teilen-Button, Drucken-Button, Gallery-Thumbs, Lightbox-Buttons
- **Farbkontraste** — `--dbw-accent` auf #2573a7 (4.6:1), `--dbw-gray` auf #5f6b6d (5.0:1) fuer WCAG AA
- **prefers-reduced-motion** — Alle Animationen deaktiviert (Cards, Sections, Modal, Filter, Lightbox, Intents)

### UI/UX
- **Multi-Step Contact Modal** — Typeform-Style mit 2 Steps: Intent-Auswahl (4 animierte SVG-Cards) → Kontaktdaten
- **Progress Bar** — Visueller Fortschritt (50%/100%) im Modal-Header
- **SVG Icons** — Emoji-Icons durch konsistente SVG Line-Icons ersetzt (Modal + CTA-Button)
- **Entrance Animations** — Staggered Card-Reveal beim Scrollen (80ms Versatz, IntersectionObserver)
- **Section Fade-Up** — Sektionen auf Single-Page faden beim Scrollen ein
- **Shimmer Loading** — Skeleton-Effekt auf Kartenbildern waehrend Laden
- **Smooth Filter Toggle** — CSS max-height Transition statt hartes display:none
- **Card Image Zoom** — Hover-Zoom-Effekt auf Kartenbildern (CSS transform)
- **Grayscale Fix** — `$use_grayscale` statt undefiniertem `$is_inactive` im CardRenderer
- **CSS Variables** — `--dbw-border`, `--dbw-border-light`, `--dbw-bg-muted`, `--dbw-shadow-hover` eingefuehrt
- **Hardcoded Farben** — 15+ Stellen durch CSS Custom Properties ersetzt

### SEO
- **Schema Agent** — `agent` Property auf RealEstateListing (Kontaktperson oder Org-Fallback)
- **Schema dateModified** — Aenderungsdatum im JSON-LD
- **Schema priceSpecification** — `UnitPriceSpecification` mit `MONTH` fuer Mietobjekte
- **robots noindex** — Verkaufte/Referenz-Objekte werden nicht indexiert
- **Sitemap-Filter** — Verkaufte/Referenz-Objekte aus WordPress-Sitemap ausgeschlossen
- **SEO Title** — `document_title_parts` Filter mit Objektart + Stadt
- **Archive Meta** — description, OG und Twitter Cards auf Archiv-/Taxonomieseiten
- **og:image Dimensionen** — width/height auf Single-Seiten
- **og:locale** — `de_DE` auf allen Seiten
- **twitter:card Fallback** — `summary` wenn kein Bild vorhanden

### Code Quality
- **Lightbox extrahiert** — 79 Zeilen Inline-JS in `assets/js/lightbox.js` (cachebar, CSP-kompatibel)
- **Inline CSS extrahiert** — Gallery-Button und Similar-Properties Styles in frontend.css
- **Similar Properties** — Nutzt CardRenderer::render() statt 150 Zeilen Inline-HTML
- **Template reduziert** — single-immobilie.php um ~200 Zeilen gekuerzt
- **Beschreibungstexte** — `wp_kses_post()` statt `esc_html()` (erhalt HTML-Formatierung aus OpenImmo)
- **Version** — Synchronisiert auf 1.8.0 (Plugin-Header, Konstante, package.json)
- **@-Operator** — `set_time_limit()` mit `function_exists()` Guard statt `@set_time_limit()`
- **require_once** — Guard mit `function_exists('media_handle_sideload')` statt Mehrfach-Include
- **Sticky Sidebar** — `overflow: hidden` → `overflow: visible` auf Container (war der Grund warum Sticky nie funktionierte)

### Dokumentation
- **docs/AUDIT-PROMPT.md** — Wiederverwendbarer Audit-Prompt
- **docs/TODO.md** — Bekannte Altlasten und Feature-Roadmap
- **docs/PROMPT-KAUFNEBENKOSTEN.md** — Feature-Prompt fuer Finanzierungsrechner
- **docs/PROMPT-PDF-EXPOSE.md** — Feature-Prompt fuer PDF-Expose-Generator

---

## [1.7.0] — 2026-05-28

Kontaktformular komplett neu: Single-Step-Modal mit Intent-basierter Lead-Qualifizierung ersetzt das alte Inline-Formular.

### Hinzugefuegt
- **Neue Datei `ContactModal.php`** — natives `<dialog>`-Modal mit CTA-Buttons in der Sidebar, Quick-Facts im Header, Intent-Radio-Pills, kontextuelle Zusatzfelder je nach Intent, Datenschutz-Checkbox, Honeypot-Spamschutz, animierter Erfolgs-Screen mit Makler-Kontaktkarte und "Was passiert jetzt?"-Block
- **Neue Datei `contact-modal.js`** — Modal-Steuerung (Open/Close/Backdrop), Intent-basierte Feld-Enthuellung, AJAX-Submit mit personalisierter Erfolgsanzeige, Mobile-Sticky-CTA-Bar mit IntersectionObserver
- **Mobile Sticky-CTA-Bar** — zeigt Preis + "Anfragen"-Button wenn Sidebar aus dem Viewport scrollt
- **Datenschutz-Checkbox** mit automatischem Link zur WordPress-Datenschutzseite (required)
- **Privacy-Check** im AJAX-Handler (`ContactForm.php`)

### Geaendert
- **ContactForm.php** — AJAX-Handler erweitert um Intent-Felder (Besichtigung/Info/Preis/Rueckruf), strukturierte E-Mails mit Intent-Prefix im Betreff, Honeypot-Erkennung
- **single-immobilie.php** — Inline-Kontaktformular ersetzt durch `ContactModal::render_cta_buttons()`
- **Plugin.php** — `ContactModal` registriert, `contact-modal.js` mit `wp_localize_script` eingebunden
- **frontend.css** — ~500 Zeilen neue Modal-Styles (Dialog, Intent-Pills mit `:has()`, Context-Fields, Privacy, Success-Animation, Bottom-Sheet, Sticky-Bar)

### Entfernt
- Altes Inline-Kontaktformular (4-Feld-Form mit Inline-Script in der Sidebar)
- Multi-Step-Logik (Progress-Bar, Step-Navigation, localStorage-Persist, Property-Hook-Karte) — zugunsten des einfacheren Single-Step-Ansatzes

---

## [1.6.0] — 2026-05-28

Globale Du/Sie-Anrede: Plugin-Betreiber koennen zwischen foermlicher ("Sie") und persoenlicher ("Du") Ansprache umschalten.

### Hinzugefuegt
- **Du/Sie-Toggle** in den Plugin-Einstellungen (Sektion "Darstellung") mit Radio-Buttons und Live-Vorschau
- **Neue Helper-Klasse `Anrede.php`** (`src/Core/Anrede.php`) mit Methoden: `pick()`, `mode()`, `ihre()`, `ihnen()`, `sie_pronoun()`
- **Globale Funktion `dbw_anrede($sie, $du)`** fuer einfache Nutzung in Templates und Erweiterungen
- **Admin-Notice** mit Cache-Hinweis nach Umschaltung der Anrede

### Geaendert
- **ContactModal.php** — 10 Strings auf `dbw_anrede()` umgestellt (Hook-Frage, Kontaktdaten-Labels, Erfolgsseite, Agent-Hint, Naechste-Schritte)
- **ContactForm.php** — E-Mail-Antwort-Texte (Erfolg/Fehler) auf `dbw_anrede()` umgestellt
- **single-immobilie.php** — "Ihr Ansprechpartner" und "Das koennte Sie auch interessieren" auf `dbw_anrede()` umgestellt
- **Plugin.php** — `wp_localize_script` um `i18n`-Array erweitert fuer JS-seitige Strings
- **contact-modal.js** — "Senden..." und "Netzwerkfehler" nutzen lokalisierte Strings aus `dbwContactModal.i18n`
- **Settings.php** — Sanitization fuer `anrede`-Feld (Whitelist: sie/du), Event-Handler fuer Admin-Notice
- **.pot-Datei** regeneriert mit allen neuen Du/Sie-String-Paaren

---

## [1.5.0] — 2026-05-28

SEO-Optimierung: Strukturierte Daten (Schema.org JSON-LD) fuer Google Rich Results, AI Overviews und Sprachassistenten.

### Hinzugefuegt
- **Schema.org RealEstateListing** auf jeder Detailseite als JSON-LD — Preis, Waehrung, Flaeche (MTK), Zimmer, Badezimmer, Schlafzimmer, Baujahr, Geo-Koordinaten, Adresse, Energieklasse, Ausstattungs-Features, Verfuegbarkeit (InStock/LimitedAvailability/SoldOut), Geschaeftsfunktion (Sell/LeaseOut)
- **Schema.org BreadcrumbList** auf Archiv- und Detailseiten (Start → Immobilien → Objekt)
- **Schema.org RealEstateAgent** sitewide — Firmenname, URL, Logo, Telefon, E-Mail, Adresse aus Plugin-Settings
- **Settings-Sektion "Maklerfirma (SEO)"** mit 8 Feldern (Firmenname, URL, Logo, Telefon, E-Mail, Strasse, PLZ, Stadt)
- **Canonical-Tag** auf gefilterten Archiv-Seiten (`/immobilien/?marketing=Miete` → `/immobilien/`) gegen Duplicate Content
- **Neue Datei `SchemaOutput.php`** — saubere Trennung von Meta-Tags (SeoMeta) und strukturierten Daten (SchemaOutput)

### Geaendert
- **Conditional Asset Loading** — CSS/JS werden nur noch auf Immobilien-Seiten geladen (CPT, Archiv, Taxonomie) oder on-demand wenn ein Block/Shortcode gerendert wird; nicht mehr sitewide
- **Block/Shortcode Render-Callbacks** enqueuen CSS/JS selbstaendig (GridBlock, ReferencesBlock, Shortcode)
- **enqueue_block_assets** laedt CSS nur noch im Editor, nicht mehr auf jeder Frontend-Seite

### Behoben
- HTML-Entity-Bug im RealEstateAgent-Schema — `&amp;` statt `&` im Firmennamen (get_bloginfo gibt HTML-encoded aus)

---

## [1.4.0] — 2026-05-28

Grosses Produktionsreife-Release mit Security-Fixes, neuen Features und umfassender Code-Konsolidierung.

### Sicherheit
- AJAX Nonce-Verifizierung fuer alle 4 Import-Endpoints (prepare, batch, finalize, run)
- Path-Traversal-Schutz in `ajax_finalize_import` — loose_files werden gegen den konfigurierten Import-Pfad validiert
- Nonce wird via `wp_localize_script` an admin.js uebergeben

### Hinzugefuegt
- **Geo-Landing-Pages**: Ort-Filter in beiden Gutenberg-Blocks (`dbw/immo-grid`, `dbw/immo-references`)
- **Neuer Shortcode `[dbw_immo_grid]`** mit Parametern: count, columns, marketing, type, location, highlights, hide_price, show_date
- **Erweiterter Shortcode `[dbw_immo_references]`** mit location, columns, status (kommasepariert)
- **Spalten-Steuerung** (1-4) in beiden Blocks und Shortcodes
- **OpenStreetMap-Karte** auf Detailseiten via Leaflet.js (kein API-Key noetig)
- **Kontaktformular** auf Detailseiten — AJAX-basiert mit wp_mail, Nonce-Schutz, Validierung
- **SEO Meta-Tags** — Open Graph (og:title, og:description, og:image) + Twitter Cards, automatisch aus Objektdaten generiert, kompatibel mit Yoast/RankMath
- **Ausstattungs-Features** — OpenImmo `<ausstattung>` Parser fuer Balkon, Terrasse, Keller, Garage, Aufzug, Pool, Kamin etc.
- **Ausstattungs-Tab** im Property-Editor mit editierbarer Komma-Liste und Badge-Vorschau
- **Ausstattungs-Badges** auf Detailseiten als Pill-Tags
- **Status-Dropdown** im Property-Editor (Aktiv/Reserviert/Verkauft/Referenz)
- **Status-Lock** — verhindert Ueberschreibung durch OpenImmo-Import
- **Reserviert-Status** — oranges Badge + Grayscale-Bild, wird aus Archiv gefiltert
- **PHP Extension Checks** — Admin-Warnung wenn ZipArchive oder simplexml fehlt
- **Aktivierungs-Hook** — flush_rewrite_rules beim Aktivieren
- **Deaktivierungs-Hook** — Cron-Cleanup beim Deaktivieren
- **Uninstall-Routine** (uninstall.php) — raumt Options, Transients, Cron, Theme Mods auf
- **Shortcode-Dokumentation** als Tabelle in den Plugin-Einstellungen
- **Lazy Loading** fuer Galerie-Bilder (erstes Bild eager, Rest lazy)
- **Alt-Text Fallback** — "Objekttitel — Bild N" fuer Bilder ohne Alt
- **.pot Datei** fuer Uebersetzungen (987 Strings)
- **.distignore** fuer sauberes ZIP-Packaging
- **Referenz-Seite Auto-Recovery** — wird automatisch neu erstellt wenn geloescht
- **Admin-Notice** wenn Referenz-Seite nicht erstellt werden kann
- **Editor-Support** im CPT fuer manuelle Beschreibungen

### Geaendert
- **CardRenderer.php** — zentrale Card-Komponente ersetzt 5 duplizierte Implementierungen (-475 Zeilen)
- **Preislabel** zeigt jetzt korrekt "Kaufpreis" oder "Kaltmiete" (war vorher immer "Kaufpreis")
- **Preissortierung** nutzt COALESCE(kaufpreis, kaltmiete) — mischt Kauf- und Mietobjekte korrekt
- **CPT-Slug** wird jetzt aus den Plugin-Einstellungen angewendet (war vorher hardcoded)
- **jQuery entfernt** — frontend.js komplett in Vanilla JS umgeschrieben
- **filter_sold_from_main** zeigt nur noch Status=aktiv (vorher konnten reservierte Objekte durchrutschen)
- **Filter-Dropdowns** nutzen sanitize_title() fuer case-insensitive Slug-Matching
- **Energietraeger** wird als "Fluessiggas" statt "FLUESSIGGAS" angezeigt
- **EnergyRenderer** komplett auf CSS-Klassen umgestellt (inline Styles entfernt)
- **Galerie-Slider**, Navigation, Thumbnails nutzen CSS-Klassen statt inline Styles
- **Sidebar** nutzt CSS-Klasse fuer Sticky-Verhalten
- **Infrastruktur-Entfernungen** nutzen CSS-Klassen und escaped Output
- **Print-Expose** komplett ueberarbeitet — blendet Karte, Formular, Aehnliche Objekte aus, limitiert auf 5 Bilder
- **Aehnliche Objekte** — 3-stufiger Fallback (Typ+Vermarktung → nur Typ → neueste 3), nutzt $id statt get_the_ID()
- **Block-Build-Artefakte** werden im Git mitgefuehrt (Plugin funktioniert ohne npm)
- **Version** auf 1.4.0 aktualisiert (Header, Konstante, package.json)

### Behoben
- Fatal Error: `new WP_Query()` ohne Namespace-Prefix in single-immobilie.php
- Doppelte Registrierung von `register_block_categories` in Plugin.php
- Dreifaches CSS-Enqueuing (Plugin.php x2 + TemplateLoader.php)
- Aehnliche Objekte leer weil get_the_ID() nach endwhile null zurueckgibt
- ReferencesBlock ignorierte hide_price_sold Plugin-Setting (war hardcoded true)
- Referenz-Seite konnte nicht wiederhergestellt werden wenn manuell geloescht
- Trashed Referenz-Seiten wurden nicht als geloescht erkannt

### Accessibility
- `aria-label` auf Galerie-Navigationspfeilen
- `aria-label` auf Lightbox Close/Prev/Next Buttons
- `role="dialog"` und `aria-modal="true"` auf Lightbox-Overlay
- Alt-Text Fallback fuer alle Galerie- und Thumbnail-Bilder

---

## [1.3.0] — 2026-04-02

### Hinzugefuegt
- Glassmorphism Floating Actions im Galerie-Slider (Zurueck, Teilen, Drucken, Grundrisse)
- Print-Expose Layout (`@media print`) — A4-Format ohne Web-Elemente
- Web Share API fuer natives Teilen (WhatsApp, AirDrop etc.)
- Highlights-Box auf Detailseiten mit Customizer-Farbsteuerung
- Erweitertes Energiepass-Rendering mit grafischer Pfeil-Skala
- Aehnliche Objekte am Fuss der Detailseite
- Native Lightbox mit Keyboard-Navigation und Touch-Swipe
- Import-Pfad Einstellungsseite mit Dropdown, AJAX-Validierung und Server-Info

### Geaendert
- Immobilien-Grid nutzt durchgehend Outline-SVGs statt gefuellter Icons

### Behoben
- Scroll/Sticky-Verhalten zwischen Agent-Card und Highlights
- printf durch echo ersetzt (PHP 8.x ArgumentCountError)
- ModSecurity WAF 503 durch key-basierte Pfad-Auswahl vermieden

---

## [1.2.0] — 2026-02-25

### Hinzugefuegt
- Gutenberg-Block `dbw/immo-references` fuer Referenzen und verkaufte Objekte
- Gutenberg-Block `dbw/immo-grid` zum Anzeigen und Filtern aktueller Immobilien
- Inspector Controls im Block-Editor (Taxonomie-Filter, Preis-Ausblendung, Layout)
- SEO-freundliche URL-Struktur fuer Referenz-Seiten (`/immobilien/referenzen/`)

### Behoben
- 301-Redirect verhindert Crawlen doppelter Root-Seiten
- Block-Pfad Korrektur fuer fehlerfreie Registrierung im Editor

### Kompatibilitaet
- Shortcode `[dbw_immo_references]` bleibt voll funktionsfaehig

---

## [1.1.0] — 2026-02-20

### Hinzugefuegt
- Referenz- und Verkauft-System via Shortcode
- Dynamische Status-Badges (Verkauft, Reserviert, Referenz)

---

## [1.0.0] — 2026-02-18

### Hinzugefuegt
- Erste stabile Version der DBW Immo Suite
- OpenImmo XML Importer mit Batch-Processing und Logging
- Responsive Grid & List View mit Umschalter
- Erweiterte Such- und Filterleiste (AJAX-ready)
- WordPress Customizer Integration fuer Styling und Layout
- Vollstaendige CSS Isolation (`#dbw-immo-suite`)
- Performance-Optimierungen fuer grosse Objektbestaende
