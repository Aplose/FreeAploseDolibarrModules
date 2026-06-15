# DoliCarbon für [Dolibarr ERP & CRM](https://www.dolibarr.org)

**DoliCarbon** ist ein Dolibarr-Modul zur Erstellung und Verwaltung von **Treibhausgas-Inventaren** in Ihrer Instanz: jahresbezogene Bilanzierungen („Bilans“), Aktivitätszeilen mit Emissionsfaktoren, Minderungsmaßnahmen, methodische Einordnung, Dashboards, Berichte und optionale Anbindung an Dolibarr-Einkaufsprozesse.

Weitere externe Module finden Sie im [Dolistore](https://www.dolistore.com).

## Funktionen

### Inventare (Bilans)

- Pro Berichtszeitraum ein Inventar anlegen; jeder Datensatz erhält automatisch eine Referenz **`CARBON-{Jahr}-{laufende Nummer}`** (siehe Klasse `DoliCarbonBilan`).
- Zeitraum (Start-/Enddatum), optional Verknüpfung mit einem Geschäftspartner, **Gesamtwerte** und **Ziele** in tCO2e, Notizen sowie Workflow-Status (Entwurf / validiert / archiviert).

### Aktivitätszeilen (Einträge)

- Emissionen nach **Scope** (1, 2 oder 3) und **Kategorie** (Modul-Taxonomie) erfassen, mit Mengen, Einheiten und Verknüpfung mit einem **Emissionsfaktor** falls zutreffend.
- Zeilen unterstützen Validierungsworkflow, **Kommentare**, Nachvollziehbarkeit und Bezug zu Quellobjekten (z. B. Lieferantenrechnungen nach Import).

### Emissionsfaktoren

- Eine Bibliothek **Emissionsfaktoren** pflegen (inkl. Versionsfelder im Datenmodell) und Faktoren je nach Schema aktivieren oder deaktivieren.

### Minderungsmaßnahmen

- **Maßnahmen** an ein Inventar knüpfen: geschätzte Einsparungen, Kosten, Bewertungsfelder und Status zur Verfolgung des Reduktionsplans.

### Methodische Einordnung („cadrage“)

- Umfang, Ausschlüsse, Wesentlichkeit, Referenz- und Berichtsjahre sowie Notizen in einem dedizierten Objekt dokumentieren, das für die Berichterstattung genutzt wird.

### Dashboard

- Aggregierte Kennzahlen: Summen nach Scope, Zeitreihen und wichtigste Kategorien auf Basis der Inventardaten.

### Berichte

- Bereich **Bericht** in der integrierten App: Managementübersicht, Analysten-Ansicht, methodischer Anhang, optionaler **Kommunikations-Hinweistext**, **Unsicherheits**spanne, Export als **CSV** oder **JSON** sowie **Snapshot** (eingefrorener Stand) für einen reproduzierbaren Zeitpunkt.

### Datenqualität und Zusammenarbeit

- Bildschirm **Qualität** für Workflow-Schritte, **Kommentare** und auditnahe Nachvollziehbarkeit auf Zeilenebene.

### Transformationsplan

- Bildschirm **Transition** zum Vergleich von Minderungsmaßnahmen (Kosten-/Nutzen-Zusammenfassung gemäß UI).

### Import aus Dolibarr

- **Import**-Assistent starten, um relevante Dolibarr-Daten in Inventarzeilen zu übernehmen (siehe `carbon_import.php` und zugehörige AJAX-Dienste).

### Optionale Trigger (Administration)

Unter **Start → Einstellungen → Module → DoliCarbon → Einrichtung** stehen zwei Optionen zur Verfügung (`DOLICARBON_TRIGGER_NOTIFY` und `DOLICARBON_AUTO_IMPORT_SUPPLIER_INVOICE`):

1. **Hinweise bei Validierung**  
   Wenn aktiviert, können beim Validieren einer **Lieferantenrechnung**, eines **Spesenberichts** oder eines **Lieferscheins** Dolibarr-Hinweismeldungen zu DoliCarbon erscheinen (mit Link zum Import bei Lieferantenrechnungen).

2. **Automatische Zeile aus Lieferantenrechnung**  
   Wenn diese Option und die Hinweise aktiv sind, kann die Validierung einer **Lieferantenrechnung** automatisch eine **Scope-3**-Zeile in der Kategorie **`purchases_services`** anlegen, mit dem **Nettobetrag in EUR**, **nur wenn** ein **Entwurfs**-Inventar existiert und ein passender **aktiver** Emissionsfaktor für Scope und Kategorie vorliegt. Doppelimporte werden per Import-Hash vermieden.

Sind die Optionen deaktiviert, entstehen keine Meldungen und keine automatischen Zeilen.

## Benutzeroberfläche

- **Eingebettete Web-App** (Menü **DoliCarbon** → `custom/dolicarbon/index.php`): Dashboard, Bilans, Cadrage, Einträge, Faktoren, Aktionen, Qualität, Transition, Import, Bericht. Es wird mindestens das Recht **lesen** benötigt.
- **Klassische PHP-Seiten** bleiben verfügbar (z. B. `carbon_bilan_list.php`, `carbon_factors.php`, weitere `carbon_*.php`).

Das Angular-Bundle liefert die UI-Texte auf **Französisch** (`assets/i18n/fr.json`). Dolibarr-Modulzeichenketten existieren für **Französisch** (`fr_FR`) und **Englisch** (`en_US`) unter `langs/`.

## Berechtigungen

Vier Rechte: **lesen**, **schreiben**, **löschen** und **validieren** (Validierung / Sperre des Inventar-Workflows). Vergabe über **Benutzer & Gruppen**.

## Voraussetzungen

- **Dolibarr 17** oder kompatible neuere Version laut Modul-Deskriptor (`need_dolibarr_version`).
- **PHP 8.1** oder höher (`phpmin`).

Kein weiteres Dolibarr-Modul ist als harte Abhängigkeit deklariert; optionale Funktionen setzen voraus, dass die genutzten Dolibarr-Objekte (z. B. Lieferantenrechnungen) vorhanden sind.

## Installation

Voraussetzung: eine funktionierende Dolibarr-Installation. Download von [dolibarr.org](https://www.dolibarr.org). Gehostete Angebote siehe unten.

### Aus einer ZIP-Datei

Bei Verteilung als `module_dolicarbon-x.y.z.zip` (z. B. vom [Dolistore](https://www.dolistore.com)): **Start → Einstellungen → Module → Externes Modul bereitstellen/installieren** und Archiv hochladen.

### Abschluss

1. Als Administrator anmelden.
2. **Einstellungen → Module** öffnen, **DoliCarbon** aktivieren.
3. **Einstellungen → Module → DoliCarbon → Einrichtung** für die beiden Trigger-Optionen öffnen.

## Konfiguration

1. **Moduleinrichtung**: siehe oben — Hinweise und automatischen Import aktivieren oder deaktivieren.
2. **Rechte**: mindestens **lesen** für die App; je nach Rolle **schreiben**, **löschen**, **validieren**.

## Dolibarr in der Cloud (Ma Gestion Cloud)

Sie können Dolibarr mit Aplose-Modulen bei **[Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)** betreiben: Hosting, Backups, Support. Registrierung / Test (Tracking für Dolistore-Besucher):

**[Konto erstellen — Ma Gestion Cloud](https://aplose.ma-gestion-cloud.fr/custom/sellyoursaas/myaccount/register.php?origin=dolistore)**

Kontakt: [contact@aplose.fr](mailto:contact@aplose.fr)

Kommerzielle Pakete und gebündelte Module hängen vom Abonnement ab; erkundigen Sie sich bei Ma Gestion Cloud.

## Support

- E-Mail: [contact@aplose.fr](mailto:contact@aplose.fr)
- Herausgeber: [Aplose](https://www.aplose.fr)

## Lizenzen

### Hauptcode

GPLv3 oder (nach Wahl) jede spätere Version. Siehe Datei `COPYING`.

### Dokumentation

Diese Dokumentation steht unter [GFDL](https://www.gnu.org/licenses/fdl-1.3.en.html).

## Version

Aktuelle Modulversion: **1.0.0** (siehe `core/modules/modDoliCarbon.class.php`).
