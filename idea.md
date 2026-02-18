# IDEA: DBW IMMO SUITE – Zentrales Lizenz- & Update-System

## Vision

Die DBW Immo Suite soll nicht nur ein WordPress-Plugin sein,  
sondern ein professionell verwaltetes Produkt mit:

- Zentraler Lizenzverwaltung  
- Eigener Update-Infrastruktur  
- Installationsübersicht  
- Release-Management  
- Klarem Systemstatus  

Kein WordPress.org Store.  
Keine Drittanbieter-Abhängigkeit.  
Volle Kontrolle.

---

# Zielbild

Ein eigenes „DBW Control Center“ auf einer Subdomain, z. B.:

updates.dbw-media.de

Dort läuft ein zentrales System zur Verwaltung von:

- Lizenzen
- Domains
- Versionen
- Releases
- Update-Checks

---

# Funktionsumfang Control Center

## 1. Lizenzverwaltung

- Lizenzkey erstellen
- Lizenz aktiv / deaktiviert
- Ablaufdatum
- Max. Aktivierungen
- Verknüpfte Domains
- Aktivierungs-Logs
- Manuelles Sperren

---

## 2. Installationsübersicht

Anzeige aller registrierten Installationen:

- Domain
- Aktive Version
- Lizenzstatus
- Letzter Update-Check
- Erstaktivierung
- IP optional
- Status (aktiv / inaktiv)

Ziel: Volle Transparenz.

---

## 3. Release-Management

- Neue Version hochladen (ZIP)
- Version definieren
- Changelog pflegen
- Mindest-WP-Version
- Mindest-PHP-Version
- Download-URL automatisch generieren
- Optional: Release-Status (stable / beta)

---

## 4. Update-Flow

Ablauf:

1. Plugin auf Kundenseite fragt Version ab
2. Lizenz wird geprüft
3. Server liefert Update-Information
4. WordPress zeigt Update-Hinweis
5. Download nur mit gültiger Lizenz

Optional:
- Signed Download URL
- Temporärer Token

---

## 5. Import-Transparenz (optional Erweiterung)

Später denkbar:

- Import-Logs pro Installation
- Letzter erfolgreicher Import
- Fehlerprotokolle

---

# Technische Architektur

## Variante empfohlen für Start

- Subdomain auf bestehendem Plesk
- Eigenes WordPress als Control Center
- Custom Plugin „DBW License Manager“
- REST API Endpoints

Vorteile:
- Schnell umsetzbar
- Wartbar
- Erweiterbar
- Kein zusätzliches Framework

---

# API Endpunkte (Konzept)

## Lizenzprüfung

POST /api/license/validate

Antwort:
- gültig / ungültig
- Ablaufdatum
- Aktivierungen
- Update erlaubt

---

## Versionsprüfung

GET /api/update/dbw-immo-suite

Antwort:
- aktuelle Version
- Download-URL
- Requirements
- Changelog

---

# Sicherheit

- Domainbindung
- Keine öffentlichen ZIPs
- Download nur mit gültigem Key
- Import-Lock im Plugin
- Grace Period bei Server-Ausfall

---

# Langfristige Perspektive

Dieses System soll:

- Für weitere DBW-Plugins nutzbar sein
- Wartungsverträge unterstützen
- Feature-Freischaltungen ermöglichen
- Versionskontrolle zentralisieren

Die DBW Immo Suite wird damit Teil eines eigenen Plugin-Ökosystems.

---

# Strategischer Gedanke

Das Ziel ist nicht nur:
„Updates verteilen“

Sondern:

- Produktqualität sichern
- Kontrolle behalten
- Skalierbarkeit ermöglichen
- Professionelle Infrastruktur aufbauen

Kein Bastel-FTP-Release-System.

Sondern:
Ein sauberes, eigenes Software-Modell.
