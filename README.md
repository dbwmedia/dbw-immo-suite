# DBW Immo Suite

**Professionelles Immobilien-Management & OpenImmo Import für WordPress**

Die **DBW Immo Suite** ist eine leistungsstarke, flexible Lösung für Immobilienmakler und Agenturen, die ihre Objekte professionell auf WordPress präsentieren möchten. Das Plugin bietet einen vollautomatisierten Import via OpenImmo-Schnittstelle, ein modernes Frontend mit fortschrittlichen Filtern und umfassende Anpassungsmöglichkeiten über den WordPress Customizer.

Entwickelt für den professionellen Einsatz und nahtlose Integration in jede WordPress-Website.

---

## 🔥 Features

### 🚀 Automatisierter Import (OpenImmo)
*   **Vollständiger OpenImmo XML Support**: Importiert automatisch Objekte aus gängiger Maklersoftware (OnOffice, FlowFact, JustImmo, etc.).
*   **Nahtloser Import:** Verarbeitet OpenImmo ZIP-Dateien direkt im WordPress Upload-Ordner (`/wp-content/uploads/openimmo/`).
*   **Intelligenter Abgleich**: Erstellt neue Objekte, aktualisiert bestehende und archiviert nicht mehr verfügbare Immobilien.
*   **Intelligenter Sync:** Erkennt Aktualisierungen, neue Objekte und Löschungen (Archive/Delete).
*   **Media Handling**: Automatischer Download und Zuordnung von Bildern, Grundrissen und Dokumenten.
*   **Batch Processing**: Stabil auch bei großen Datenmengen durch intelligente Stapelverarbeitung.
*   **Custom Post Type:** Speichert Immobilien als `immobilie` Post Type für maximale Flexibilität.
*   **Taxonomien:** Automatische Zuordnung zu Objektart, Vermarktungsart, Region etc.

### 🎨 Modernes Frontend & Design
*   **Premium Listenansicht**:
    *   **Grid View**: Elegante Kachelansicht für maximale Übersicht.
    *   **List View**: Detaillierte horizontale Ansicht für mehr Informationen.
    *   **Live Switcher**: Nutzer können ohne Neuladen zwischen den Ansichten wechseln.
*   **High-End Suche & Filter**:
    *   Kombinierte Suche (Ort, PLZ, Objektart).
    *   Erweiterte Filter (Preis, Fläche, Zimmer, Vermarktungsart).
    *   Sofortige Ergebnisse und intuitive Bedienung.
*   **Detailseiten**:
    *   Strukturierte Darstellung aller Immobiliendaten.
    *   Bildergalerie, Ausstattungsmerkmale, Energieausweis-Daten.
    *   Kontaktformular-Integration.
*   **Frontend-Darstellung:** Inklusive responsivem Archive-Template (Grid-View) und Detailansicht mit Galerie & Maps-Integration.
*   **NEU in 1.2.0: Flexibler Immobilien-Grid Block:** 
    * Eigener **Gutenberg-Block (`dbw/immo-grid`)** zum dynamischen Anzeigen aktueller Objekte (z.B. "Die 3 neuesten Angebote").
    * Filter nach Vermarktungsart (Kauf/Miete) und Objektart direkt im Block-Editor.
    * Steuerung von Anzahl und sichtbaren Daten (Preis, Einstellungsdatum).
*   **NEU in 1.1.0: Referenzen & Verkaufte Objekte:** 
    * Automatische Verwaltung von verkauften Immobilien und Status-Badges (Verkauft / Reserviert / Referenz).
    * Eigener **Gutenberg-Block (`dbw/immo-references`)** zur Darstellung vergangener Projekte inklusive Live-Vorschau.
    * Saubere, SEO-freundliche URL-Struktur (z.B. `/immobilien/referenzen/`).
    * Abwärtskompatibel zum Shortcode `[dbw_immo_references]`.
*   **SEO-Optimiert:** Saubere URLs, automatische Umleitung von isolierten Referenz-Seiten, Meta-Daten Vorbereitung.

### ⚙️ Anpassbar & Theme-Unabhängig
*   **Theme Integration**: Funktioniert mit jedem WordPress Theme (getestet mit GeneratePress, Astra, Hello Elementor).
*   **Customizer Support**:
    *   Passen Sie Farben (Primary/Secondary), Rahmen und Schriften direkt im WordPress Customizer an.
    *   Steuern Sie die Sichtbarkeit von Metadaten (Preis, Fläche, Baujahr) per Klick.
*   **Block-Editor Integration**: Native Inspector Controls im Editor für schnelles Anpassen von Listen und Referenzen.
*   **CSS Isolation**:
    *   "Shadow-Dom"-ähnliche Isolation (`#dbw-immo-suite`) verhindert, dass Theme-Styles das Layout zerschießen.
    *   Konsistenter Look & Feel garantiert.

---

## 🛠 Installation & Anforderungen

### Systemanforderungen
*   WordPress 6.0 oder höher
*   PHP 7.4 oder höher (Empfohlen: 8.1+)
*   FTP-Zugang oder Upload-Möglichkeit für OpenImmo XML Dateien

### Einrichtung
1.  Laden Sie das Plugin im WordPress Adminbereich hoch und aktivieren Sie es.
2.  Navigieren Sie zu **Design > Customizer > DBW Immo Suite**, um das Design an Ihr Branding anzupassen.
3.  Konfigurieren Sie Ihre Maklersoftware für den FTP-Upload in den Ordner `/wp-content/uploads/openimmo/import/`.
4.  Der Import läuft automatisch via WP-Cron oder kann manuell angestoßen werden.

---

## 📦 Changelog

### Version 1.3.0 (2026-04-02)
*   🎉 **Premium-Feature**: Detailseiten komplett überarbeitet mit nativen Glassmorphism "Floating Actions" im Galerie-Slider (Zurück, Teilen, Drucken, Grundrisse-Anker).
*   🎉 **Premium-Feature**: Komplettes Print-Exposé Layout integriert (`@media print`). Beim Drucken der Detailseite entsteht nun automatisch ein perfekt gesetztes Makler-Dokument im A4-Format ohne überflüssige Web-Elemente.
*   *   **Web Share API**: Native Teilen-Funktion für moderne Betriebssysteme (WhatsApp, AirDrop, etc.).
*   **Feature**: "Highlights"-Karte auf Detailseiten mit direktem Customizer-Farbzugriff integriert.
*   **Feature**: Erweitertes Energiepass-Rendering mit grafischer Pfeil-Skala und Detailwerten.
*   **Optimierung**: Immobilien-Grid verwendet nun durchgehend feine, Outline-SVGs für einen wesentlich hochwertigeren und moderneren Look (Icon-Fill & Hover-Color entfernt).
*   **Feature**: Ähnliche Objekte ("Das könnte Sie auch interessieren") am Fuß der Detailseite ergänzt (inkl. Customizer-Option).
*   **Fix**: Scroll/Sticky-Verhalten zwischen Agent-Card und Highlights optimiert.

### Version 1.2.0 (2026-02-25)
*   🎉 **Feature**: Eigener Gutenberg-Block `dbw/immo-references` für die Anzeige von Referenzen und verkauften Objekten hinzugefügt.
*   🎉 **Feature**: Eigener Gutenberg-Block `dbw/immo-grid` zum freien Anzeigen und Filtern aktueller Immobilien hinzugefügt.
*   **Feature**: Block-Einstellungen (Inspector Controls) im Editor (Filter nach Taxonomie, Preis-Ausblendung, Layout).
*   **Feature**: SEO-freundliche, automatisch untergeordnete URL-Struktur für Referenz-Seiten (`/immobilien/referenzen/`).
*   **Fix**: Weiterleitung (301) verhindert das Crawlen doppelter/isolierter Root-Seiten.
*   **Fix**: Korrektur des Block-Pfades, sodass Blöcke nun fehlerfrei im Editor registriert werden.
*   **Maintained**: Der bisherige Shortcode `[dbw_immo_references]` bleibt voll funktionsfähig.

### Version 1.1.0
*   **Feature**: Einführung des Referenz- und Verkauft-Systems via Shortcode.
*   **Feature**: Dynamische Status-Badges.

### Version 1.0.0 (2026-02-18)
*   🎉 **Initial Release**: Erste stabile Version der DBW Immo Suite.
*   **Feature**: OpenImmo XML Importer (Batch-Processing & Logging).
*   **Feature**: Responsive Grid & List View mit Umschalter.
*   **Feature**: Erweiterte Such- und Filterleiste (AJAX-ready).
*   **Feature**: WordPress Customizer Integration für Styling & Layout-Optionen.
*   **Optimierung**: Vollständige CSS Isolation (`#dbw-immo-suite`) für Theme-Kompatibilität.
*   **Optimierung**: Performance-Verbesserungen für große Objektbestände.

---

## 📄 Lizenz & Support

Die Nutzung dieses Plugins setzt eine gültige Lizenz voraus. 
Support-Anfragen richten Sie bitte direkt an den Entwickler.

**Entwickelt von:**

**Dennis Buchwald**
DBW Media
E-Mail: hallo@dbw-media.de
Web: [dbw-media.de](https://dbw-media.de)

*Professional Web Development & Digital Solutions.*
