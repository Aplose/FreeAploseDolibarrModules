# DOLILINKS FÜR [DOLIBARR ERP & CRM](https://www.dolibarr.org)

## Modulbeschreibung

DoliLinks ist ein Modul für Dolibarr, das die Erstellung und Verwaltung hierarchischer Verknüpfungen zwischen Unternehmen (Drittparteien) ermöglicht. Es bietet eine klare Visualisierung der Eltern-Kind-Beziehungen zwischen Unternehmen und erleichtert die Verwaltung komplexer Organisationsstrukturen.

## Hauptfunktionen

### 1. Verwaltung von Unternehmensverknüpfungen

Das Modul ermöglicht die Erstellung hierarchischer Beziehungen zwischen Unternehmen:
- **Eltern-Kind-Verknüpfungen**: Definieren, welche Unternehmen Eltern oder Kinder anderer Unternehmen sind
- **Anpassbare Verknüpfungstypen**: Erstellen spezifischer Verknüpfungstypen (Tochtergesellschaft, Zweigstelle, Partner, etc.)
- **Verhinderung zirkulärer Verknüpfungen**: Das System verhindert, dass ein Unternehmen mit sich selbst verknüpft wird

![Unternehmensverknüpfungsverwaltungsoberfläche: Hinzufügen eines Elternteils](img/screenshot_dolilinks_links02.png)
![Unternehmensverknüpfungsverwaltungsoberfläche](img/screenshot_dolilinks_links03.png)


### 2. Hierarchische Visualisierung

#### 2.1 Anzeige in der Unternehmenskarte
Verknüpfungen werden automatisch in der Karte jedes Unternehmens angezeigt:
- **Eltern-Bereich**: Liste der Mutterunternehmen mit direkten Verknüpfungen
- **Kinder-Bereich**: Liste der Tochterunternehmen mit direkten Verknüpfungen
- **Aktionsschaltflächen**: Schnelles Hinzufügen neuer Verknüpfungen und Zugang zum Diagramm

![Verknüpfungsanzeige in der Unternehmenskarte](img/screenshot_dolilinks_links01.png)

#### 2.2 Interaktives Diagramm
Vollständige grafische Visualisierung der Beziehungen:
- **Hierarchisches Netzwerk**: Anzeige aller Eltern, Kinder und Enkelkinder
- **Interaktive Navigation**: Klick auf Knoten, um auf Unternehmenskarten zuzugreifen
- **Farbige Legende**: Visuelle Unterscheidung zwischen Eltern (grau), aktuellem Unternehmen (grün) und Kindern (blau)
- **Verknüpfungstypen**: Anzeige der Verknüpfungstyp-Labels auf den Verbindungen

![Interaktives Beziehungsdiagramm](img/screenshot_diagram_interactive.png)

### 3. Verwaltung von Verknüpfungstypen

#### 3.1 Typkonfiguration
- **Erstellung benutzerdefinierter Typen**: Definieren von Beziehungstypen, die für Ihre Organisation spezifisch sind
- **Zentralisierte Verwaltung**: Administrationsoberfläche zum Erstellen und Ändern von Typen
- **Integriertes Wörterbuch**: Typen werden im Dolibarr-Wörterbuch gespeichert

![Verknüpfungstypverwaltungsoberfläche: Wörterbuchzugang](img/screenshot_link_types_management01.png)
![Verknüpfungstypverwaltungsoberfläche: Hinzufügen von Verknüpfungstypen](img/screenshot_link_types_management02.png)

### 4. Integration in das Dolibarr-Ökosystem

#### 4.1 Hooks und Erweiterungen
- **Native Integration**: Das Modul integriert sich perfekt in die Dolibarr-Oberfläche
- **Benutzerdefinierte Hooks**: Erweiterung der Funktionalitäten über das Hook-System

#### 4.2 Filterung von Rechnungsadressen
- **Intelligente Filterung**: Option, nur Rechnungsadressen beim Versenden von E-Mails anzubieten (senden Sie keine Rechnungen an die Kunden Ihrer Kunden!!!)
- **Kinder-Drittparteien-Kontakte**: Anzeige von Kontakten verknüpfter Unternehmen in Kontaktkarten
- **Flexible Konfiguration**: Aktivierung/Deaktivierung über Modulparameter

![Kinderkontaktfilterung bei einer Bestellung](img/screenshot_contact_filtering_order.png)
![Rechnungsadressenfilterung beim E-Mail-Versand](img/screenshot_contact_filtering_invoice_email.png)

#### 4.3 Kompatibilität
- **Multi-Entity**: Vollständige Unterstützung des Dolibarr Multi-Entity-Modus
- **Sicherheit**: Respektierung der Zugriffsrechte und Dolibarr-Sicherheit
- **Übersetzungen**: Mehrsprachige Unterstützung (Französisch, Englisch, Deutsch, Spanisch)

### 5. Erweiterte Funktionen

#### 5.1 Datenimport
- **SocParent-Migration**: Import-Tool zur Migration von Daten aus dem SocParent-Modul

![Datenimportoberfläche](img/screenshot_admin01.png)

#### 5.2 Berichte und Statistiken
- **Automatische Zähler**: Anzeige der Anzahl von Eltern/Kindern für jedes Unternehmen
- **Erleichterte Navigation**: Direkte Verknüpfungen zu verknüpften Unternehmenskarten
- **Übersicht**: Schneller Zugang zum vollständigen Beziehungsdiagramm

## Installation

### Voraussetzungen
- Dolibarr ERP & CRM installiert
- Administratorrechte für die Modulinstallation

### Installation über Dolibarr-Oberfläche
1. Laden Sie das Modul von [Dolistore.com](https://www.dolistore.com) herunter
2. Melden Sie sich als Administrator bei Dolibarr an
3. Gehen Sie zu `Startseite > Konfiguration > Module > Externes Modul bereitstellen`
4. Laden Sie die Modul-ZIP-Datei hoch
5. Aktivieren Sie das Modul in der Liste der verfügbaren Module

### Erste Konfiguration
1. Greifen Sie auf `Konfiguration > Module > DoliLinks` zu
2. Konfigurieren Sie die Parameter nach Ihren Bedürfnissen
3. Erstellen Sie Ihre benutzerdefinierten Verknüpfungstypen bei Bedarf

## Verwendung

### Verknüpfung zwischen Unternehmen erstellen
1. Öffnen Sie die Karte des betreffenden Unternehmens
2. Klicken Sie im Bereich "Eltern" oder "Kinder" auf die Schaltfläche "+"
3. Wählen Sie das zu verknüpfende Unternehmen aus der Dropdown-Liste aus
4. Wählen Sie den Verknüpfungstyp (optional)
5. Klicken Sie auf "Hinzufügen"

### Beziehungen anzeigen
1. Klicken Sie von der Unternehmenskarte aus auf "Diagramm anzeigen"
2. Das interaktive Diagramm wird mit allen Beziehungen angezeigt
3. Klicken Sie auf einen beliebigen Knoten, um auf die Unternehmenskarte zuzugreifen

### Verknüpfungstypen verwalten
1. Gehen Sie zu `Konfiguration > Wörterbücher > Unternehmensverknüpfungstyp`
2. Erstellen, ändern oder löschen Sie Typen nach Ihren Bedürfnissen

![Verknüpfungstyp-Wörterbuch](img/screenshot_link_types_management01.png)
![Verknüpfungstyp-Wörterbuch: Verwaltung](img/screenshot_link_types_management02.png)


## Konfiguration

### Verfügbare Parameter
- **Kontaktfilterung**: Option, nur Rechnungsadressen beim Versenden von E-Mails anzubieten

### Anpassung
Das Modul kann erweitert werden über:
- Benutzerdefinierte Hooks
- Änderbare Vorlagen
- Erweiterbare PHP-Klassen

## Support und Entwicklung

### Lizenz
- **Hauptcode**: GPLv3 oder spätere Version
- **Dokumentation**: GFDL

### Support
- Vollständige Dokumentation im Modul
- Kompatibel mit neueren Dolibarr-Versionen

### Entwicklung
Das Modul wird entwickelt unter Einhaltung der Dolibarr-Standards:
- MVC-Architektur
- Hook-System
- Übersetzungsverwaltung
- Integrierte Sicherheit
