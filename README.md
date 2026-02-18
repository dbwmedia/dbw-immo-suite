# DBW Immo Suite

**Professionelles Immobilien-Management & OpenImmo Import für WordPress**

Die **DBW Immo Suite** ist eine leistungsstarke, flexible Lösung für Immobilienmakler und Agenturen, die ihre Objekte professionell auf WordPress präsentieren möchten. Das Plugin bietet einen vollautomatisierten Import via OpenImmo-Schnittstelle, ein modernes Frontend mit fortschrittlichen Filtern und umfassende Anpassungsmöglichkeiten über den WordPress Customizer.

Entwickelt für den professionellen Einsatz und nahtlose Integration in jede WordPress-Website.

---

## 🔥 Features

### 🚀 Automatisierter Import (OpenImmo)
*   **Vollständiger OpenImmo XML Support**: Importiert automatisch Objekte aus gängiger Maklersoftware (OnOffice, FlowFact, JustImmo, etc.).
*   **Intelligenter Abgleich**: Erstellt neue Objekte, aktualisiert bestehende und archiviert nicht mehr verfügbare Immobilien.
*   **Media Handling**: Automatischer Download und Zuordnung von Bildern, Grundrissen und Dokumenten.
*   **Batch Processing**: Stabil auch bei großen Datenmengen durch intelligente Stapelverarbeitung.

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

### ⚙️ Anpassbar & Theme-Unabhängig
*   **Theme Integration**: Funktioniert mit jedem WordPress Theme (getestet mit GeneratePress, Astra, Hello Elementor).
*   **Customizer Support**:
    *   Passen Sie Farben (Primary/Secondary), Rahmen und Schriften direkt im WordPress Customizer an.
    *   Steuern Sie die Sichtbarkeit von Metadaten (Preis, Fläche, Baujahr) per Klick.
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
