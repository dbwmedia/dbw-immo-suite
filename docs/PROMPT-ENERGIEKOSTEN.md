# Prompt: Energiekosten-Rechner

---

Baue fuer das WordPress-Plugin "dbw Immo Suite" (v1.10.0) einen Energiekosten-Rechner als neue Komponente auf der Einzelansicht, direkt unterhalb oder innerhalb der bestehenden Energie-Sektion.

## Kontext

Das Plugin hat bereits einen Energieausweis-Bereich (`EnergyRenderer::render_single_scale()`) der Baujahr, Ausweistyp, Endenergieverbrauch, Energietraeger, Gueltigkeit und Effizienzklasse (A+ bis H mit Farbskala) anzeigt. Der Energiekosten-Rechner soll diese Daten nutzen um dem Besucher eine **geschaetzte monatliche Heizkostenberechnung** zu liefern.

## Vorhandene Meta-Felder

- `energiepass_endenergie` — Endenergieverbrauch in kWh/(m²·a) als Float
- `energiepass_traeger` — Energietraeger als String (z.B. "GAS", "OEL", "FERNWAERME", "STROM", "HOLZ", "PELLET", "WAERMEPUMPE", "FLUESSIGGAS", "SOLAR")
- `energiepass_art` — "verbrauch" oder "bedarf"
- `energiepass_wertklasse` — Effizienzklasse A+ bis H
- `wohnflaeche` — Wohnflaeche in m² als Float

## Anforderungen

### 1. Berechnung

**Formel:**
```
Jahresverbrauch = Endenergieverbrauch (kWh/m²a) × Wohnflaeche (m²)
Jahreskosten = Jahresverbrauch × Energiepreis pro kWh
Monatskosten = Jahreskosten / 12
```

**Energiepreise pro kWh (Stand 2026, als Defaults in Settings konfigurierbar):**
- Gas: 0,12 €/kWh
- Oel: 0,10 €/kWh
- Fernwaerme: 0,14 €/kWh
- Strom (Direktheizung): 0,35 €/kWh
- Holz/Pellet: 0,06 €/kWh
- Waermepumpe: 0,12 €/kWh (Strom × COP ~3)
- Fluessiggas: 0,09 €/kWh
- Solar: 0,00 €/kWh (Hinweis: "Primaerenergie durch Solaranlage")

**Mapping:** Das OpenImmo-Feld `energiepass_traeger` kommt in verschiedenen Schreibweisen. Baue ein flexibles Mapping (case-insensitive, Umlaute, Unterstriche) auf die obigen Kategorien.

### 2. Anzeige

**Variante A — Inline in bestehender Energie-Sektion (empfohlen):**
Erweitere `EnergyRenderer::render_single_scale()` um einen Block unterhalb der Farbskala:

- **Ergebnis-Box** mit:
  - "Geschaetzte Heizkosten" als Ueberschrift
  - Monatskosten gross und prominent (z.B. "~120 €/Monat")
  - Jahreskosten kleiner darunter
  - Energietraeger + Preis/kWh als Info-Zeile
  - Vergleichs-Indikator: "Das ist X% [unter/ueber] dem Durchschnitt fuer diese Groesse"
    - Durchschnitt: ~100 kWh/m²a × Wohnflaeche × Energiepreis
  - Disclaimer: "Schaetzung basierend auf Energieausweis-Daten. Tatsaechliche Kosten koennen abweichen."

**Interaktivitaet (optional, nice-to-have):**
- Slider fuer Energiepreis (damit Besucher mit eigenen Tarifen rechnen kann)
- Ergebnis aktualisiert live (wie beim Finanzierungsrechner)

### 3. Bedingungen fuer Anzeige
- Nur wenn `energiepass_endenergie > 0` UND `wohnflaeche > 0`
- Nur wenn Customizer-Toggle aktiv: `dbw_immo_single_show_energy` (existiert bereits)
- Wenn `energiepass_traeger` leer: Default-Preis fuer Gas verwenden, Hinweis anzeigen

### 4. Backend-Settings
Im bestehenden Settings-Tab "Darstellung" (oder eigener Tab "Rechner" falls der Finanzierungsrechner schon einen hat):
- Energiepreise pro Traeger (8 Number-Felder mit Defaults)
- Toggle: "Energiekosten-Schaetzung anzeigen" (default: an)

### 5. Technische Umsetzung
- **Kein neues JS noetig** wenn keine Interaktivitaet — reine PHP-Berechnung + HTML-Output
- Falls Slider gewuenscht: Neue Datei `assets/js/energy-calculator.js` (Vanilla JS)
- Styles in `assets/css/frontend.css` ergaenzen (konsistent mit Finanzierungsrechner-Styles)
- Namespace: `DBW\ImmoSuite\Frontend`
- ABSPATH Guard nach namespace
- Alle Texte mit `dbw_anrede()` wo relevant
- `prefers-reduced-motion` respektieren falls Slider
- Im Print/Expose ausblenden

### 6. Design
- Visuell als "Info-Card" innerhalb der Energie-Sektion
- Accent-Farbe fuer den Monatsbetrag
- Icon: Flamme oder Thermometer (SVG, konsistent mit Plugin-Icons)
- Vergleichs-Balken: Gruen (unter Durchschnitt) / Orange (Durchschnitt) / Rot (ueber Durchschnitt)
