# Prompt: Infrastruktur-Score

---

Baue fuer das WordPress-Plugin "dbw Immo Suite" (v1.10.0) einen visuellen Infrastruktur-Score als neue Komponente auf der Einzelansicht, idealerweise in der Lage-Sektion oder als eigene Sektion danach.

## Kontext

Das Plugin importiert aus OpenImmo-XML bereits **Entfernungsangaben zu Infrastruktur** und speichert sie als Post-Meta mit dem Prefix `distanz_`. Diese Daten werden aktuell als einfache Liste ("Kindergarten: 200m, Grundschule: 500m, ...") dargestellt.

Der Infrastruktur-Score soll diese Rohdaten in einen **visuellen, sofort verstaendlichen Score** umwandeln — aehnlich wie ein "Walkability Score" oder "Location Rating" bei ImmobilienScout24.

## Vorhandene Daten

Die Distanz-Felder kommen dynamisch aus dem Import. Typische Keys:
```
distanz_kindergarten, distanz_grundschule, distanz_hauptschule,
distanz_realschule, distanz_gymnasium, distanz_gesamtschule,
distanz_einkaufsmoeglichkeiten, distanz_oeffentliche_verkehrsmittel,
distanz_gaststaetten, distanz_zentrum, distanz_flughafen,
distanz_autobahn, distanz_us_bahn, distanz_bus
```

Werte sind Strings mit Einheit, z.B. "0.2" (km), "200" (m), oder "200 m". Das Parsing muss flexibel sein.

Aktueller Code in `single-immobilie.php` (~Zeile 365):
```php
$custom_fields = get_post_custom($id);
foreach ($custom_fields as $key => $val) {
    if (strpos($key, 'distanz_') === 0) { ... }
}
```

## Anforderungen

### 1. Score-Berechnung

**Kategorien mit Gewichtung:**

| Kategorie | Meta-Keys | Gewichtung | Max-Punkte |
|-----------|-----------|------------|------------|
| OEPNV | distanz_oeffentliche_verkehrsmittel, distanz_us_bahn, distanz_bus | 25% | 10 |
| Einkaufen | distanz_einkaufsmoeglichkeiten, distanz_zentrum | 20% | 10 |
| Bildung | distanz_kindergarten, distanz_grundschule, distanz_gymnasium, distanz_realschule, distanz_hauptschule, distanz_gesamtschule | 25% | 10 |
| Gastronomie | distanz_gaststaetten | 10% | 10 |
| Verkehr | distanz_autobahn, distanz_flughafen | 20% | 10 |

**Scoring pro Distanz-Wert:**
- ≤ 500m → 10/10
- ≤ 1km → 8/10
- ≤ 2km → 6/10
- ≤ 5km → 4/10
- ≤ 10km → 2/10
- > 10km → 1/10

**Gesamt-Score:** Gewichteter Durchschnitt aller verfuegbaren Kategorien (0–10, auf 1 Dezimale gerundet). Kategorien ohne Daten werden uebersprungen (Gewichtung wird auf vorhandene Kategorien umverteilt).

### 2. Anzeige

**Haupt-Score (Hero-Element):**
- Grosser Ring/Kreis mit Score-Zahl in der Mitte (z.B. "8.2")
- Ring-Farbe je nach Score:
  - 8-10: Gruen (#28a745)
  - 6-7.9: Blau (--dbw-accent)
  - 4-5.9: Orange (#f39c12)
  - 0-3.9: Rot (#e74c3c)
- Text darunter: "Sehr gut" / "Gut" / "Durchschnitt" / "Ausbaufaehig"
- SVG-basierter Kreis mit `stroke-dasharray` fuer animierten Fortschritt

**Kategorie-Breakdown:**
- 5 horizontale Balken (eine pro Kategorie)
- Jeder Balken zeigt: Icon + Label + Balken + Score
- Balken gefuellt je nach Score (0-100%)
- Farbe des Balkens passend zum Score der Kategorie

**Detail-Aufklappbar (optional):**
- Unter jeder Kategorie aufklappbar: Die einzelnen Distanzen
- z.B. "OEPNV (9/10)" → aufklappen → "Bus: 200m, U-Bahn: 800m"

### 3. Bedingungen fuer Anzeige
- Nur wenn mindestens 3 `distanz_*` Felder vorhanden sind (sonst zu wenig Daten fuer sinnvollen Score)
- Eigener Customizer-Toggle: `dbw_immo_single_show_infra_score` (default: true)
- Position: Nach der Lage-Sektion, vor oder nach der Karte

### 4. Technische Umsetzung

**PHP:** Neue Klasse `src/Frontend/InfrastructureScore.php`
- Methode `calculate($post_id)` → gibt Array mit Gesamt-Score + Kategorie-Scores zurueck
- Methode `render($post_id)` → gibt HTML aus
- Distanz-Parsing: Erkennt "200", "200m", "200 m", "0.2", "0.2 km", "0,2km" etc.
- Normalisierung auf Meter fuer einheitliches Scoring

**CSS:** In `assets/css/frontend.css` ergaenzen
- SVG-Ring-Animation (stroke-dashoffset transition)
- Balken-Animation (width transition, getriggert durch IntersectionObserver)
- Responsive: Ring + Balken untereinander auf Mobile
- `prefers-reduced-motion`: Keine Animationen, sofort sichtbar

**JS:** Minimal — nur fuer Ring-Animation (IntersectionObserver trigger) und optionales Aufklappen
- Kann in `assets/js/frontend.js` ergaenzt werden (kein eigenes File noetig)
- Oder eigene Datei `assets/js/infra-score.js` wenn umfangreicher

**Registrierung:**
- In `Plugin.php` registrieren
- Customizer-Toggle in `Customizer.php`
- JS nur auf Single-Immobilie laden
- ABSPATH Guard, namespace `DBW\ImmoSuite\Frontend`
- Du/Sie System beruecksichtigen
- Im Print-Expose **einschliessen** (Score ist wertvolle Info)

### 5. Design-Referenzen
- Walk Score (walkscore.com) — Ring + Kategorie-Balken
- ImmobilienScout24 — Standort-Bewertung mit Sternen
- Airbnb — Location Rating mit Aufschluesselung

### 6. Expose-Integration
Der Score soll auch im Expose (`templates/expose.php`) auf Seite 3 (Lage) erscheinen:
- Kompakte Version: Nur Ring + Score-Zahl + 5 Einzeiler (Kategorie: Score)
- Keine Animationen (Print)
- Farben mit `print-color-adjust: exact`
