# dbw Immo Suite — Offene Punkte & Roadmap

Stand: 2026-06-03 (nach v1.13.0)

---

## Bekannte Altlasten (kein Launch-Blocker)

### Code Quality
- [ ] **Inline-Styles im Single-Template** — ~50+ `style="..."` Attribute in `single-immobilie.php` (Highlights-Card, Agent-Card, Sidebar, Gallery-Buttons). In CSS-Klassen in `frontend.css` auslagern.
- [ ] **i18n fehlend** — ~20 hardcoded deutsche Strings im Single-Template ohne `__()`: "Wohnflaeche", "Zimmer", "Grundstueck", "Beschreibung", "Ausstattung", "Lage", "Entfernungen", "Grundrisse", "Highlights", "Kaufpreis", "Kaltmiete" etc.
- [x] ~~Settings Callbacks — Escaping mit `esc_attr()`/`esc_html()`/`wp_kses_post()` ergaenzt~~ (v1.13.0)
- [ ] **jQuery in admin.js** — Einzige Datei mit jQuery, Rest ist Vanilla. Kein Blocker (WP Admin nutzt jQuery), aber Inkonsistenz.
- [ ] **CSS !important** — ~75x im Stylesheet (Grossteil Print-Styles — vertretbar, aber aufraeum-wuerdig).

### Performance
- [ ] **Importer: ~25 update_post_meta() pro Property** — `map_fields()` macht einzelne DB-Writes. Batch-Update waere effizienter bei grossen Imports (200+ Objekte).
- [x] ~~Leaflet lokal gebuendelt~~ (v1.12.1)
- [x] ~~Doppelter get_post_meta() fuer Energieklasse entfernt~~ (v1.13.0)

### Accessibility
- [ ] **Gallery-Slider Keyboard-Navigation** — Horizontal-Scroll hat keine Arrow-Key-Shortcuts. Nur Tab auf einzelne Slides moeglich.
- [ ] **Lightbox aria-labelledby** — Hat `aria-label` aber kein dynamisches Label mit Bildnummer. Besser: `aria-labelledby` auf Counter-Element.

### SEO
- [x] ~~og:url auf Archivseiten~~ (v1.13.0)
- [x] ~~og:image auf Archivseiten (Fallback: Site-Icon/Logo)~~ (v1.13.0)
- [x] ~~og:type auf Single-Seiten: `article` statt `website`~~ (v1.13.0)

### Sicherheit (Niedrig)
- [x] ~~Log-Schutz: index.php fuer Nginx-Kompatibilitaet~~ (v1.12.1)
- [ ] **@-Operator** — `@unlink()`, `@file_put_contents()` in Importer. Maskiert Fehlerursachen.

---

## Feature-Ideen (v2.0+)

- [x] ~~Kaufnebenkosten- & Finanzierungsrechner~~ (v1.9.0)
- [x] ~~PDF-Expose-Download~~ (v1.10.0 — standalone Expose-Seite mit Print/PDF)
- [x] ~~Energiekosten-Rechner~~ (v1.11.0 — inline in Energieausweis-Sektion mit Slider)
- [x] ~~Infrastruktur-Score~~ (v1.11.0 — Walk-Score-Style mit SVG-Ring + Kategorie-Bars)
- [x] ~~WhatsApp-Button~~ (v1.12.0 — Sidebar + Floating + Modal-Integration)
- [x] ~~Preis-pro-qm-Vergleich~~ (v1.12.0 — mit Bestandsdurchschnitt, positiv-only)
- [ ] REST API Endpoints
- [ ] Immobilien-Vergleich (Side-by-Side)
- [ ] Merkliste/Favoriten (localStorage)
- [ ] E-Mail-Alerts bei neuen passenden Objekten
