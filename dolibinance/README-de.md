# DOLIBINANCE FÜR DOLIBARR

![Screenshot dolibinance](img/screenshot_dolibinance.png?raw=true "DoliBinance")

## Funktionen

Dieses Modul verbindet Ihr Binance-Konto mit Ihrem Dolibarr. Wenn Sie noch keines haben, folgen Sie diesem Link: [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280).
Dadurch können Sie ganz einfach Zahlungen in Bitcoin und anderen von Binance akzeptierten Kryptowährungen hinzufügen.
Wenn ein Kunde in Kryptowährung bezahlen möchte, verwendet das Modul den aktuellen durchschnittlichen 24-Stunden-Kurs der Kryptowährung für die zu zahlende Rechnung sowie die Empfangsadresse Ihres Binance-Wallets. Der Kunde gibt dann die Transaktionsinformationen ein, die er durchgeführt hat: Transaktionskennung, Absenderadresse, Betrag. Sobald die Einzahlung auf Ihrem Binance-Konto eingeht, wird die Rechnung als "bezahlt" markiert.
Sie können den Saldo Ihrer Kryptowährungsanlagen in Ihrem Binance-Spot-Wallet direkt in Dolibarr verfolgen, ohne die Binance-App verwenden zu müssen.

## In Kürze
Benutzerberechtigungen sind in dieser Version noch nicht entwickelt und werden später hinzugefügt. Alle Funktionen sind für jeden Dolibarr-Benutzer frei zugänglich, jedoch ist keine wichtige Aktion ohne Administratorrechte möglich.
Bitte teilen Sie Ihre Kommentare zu diesem Thema mit [Olivier Andrade Sanchez](mailto:oandrade@aplose.fr?subject=[DoliBinanceRequest]-).

## Konfiguration

### Wörterbuch
Um Ihren Kunden die Zahlung in Kryptowährung anzubieten, müssen Sie die erforderlichen Informationen im "Verzeichnis der Binance-Zahlungsadressen" hinzufügen (Startseite->Konfiguration->Wörterbuch), die Empfangsadressen der Kryptowährungen, die Sie als Zahlung für Ihre Rechnungen akzeptieren möchten.
![Screenshot Wörterbuch 1](img/doc-010-dictionnary.png?raw=true "Wörterbuch 1")
In diesem Wörterbuch fügen Sie bitte die Informationen aus Ihren verschiedenen Empfangsadressen in Binance ein ([siehe die Binance-Dokumentation dazu](https://www.binance.com/fr/support/faq/comment-d%C3%A9poser-des-cryptos-sur-binance-115003764971)).
Beispielsweise wurden hier drei Adressen eingegeben (darunter zwei für Bitcoin auf zwei verschiedenen Netzwerken).
![Screenshot Wörterbuch 2](img/doc-011-dictionnary.png?raw=true "Wörterbuch 2")
Wie Sie sehen, können Sie Kryptowährungen aktivieren oder deaktivieren, ohne die Einstellungen zu löschen.

### Einstellungsseite
Die Verwendung dieses Moduls erfordert ein Binance-Konto. Warum Binance? Weil es die weltweit größte Kryptowährungsbörse ist. Achten Sie jedoch darauf, dass Sie nicht im Besitz der privaten und öffentlichen Schlüssel des entsprechenden Wallets sind. Ich empfehle daher, diese Plattform nur für Zahlungen oder Handel zu verwenden. Wenn Sie Ihre Kryptowährungen sicher aufbewahren möchten, sollten Sie ein "Hard Wallet" verwenden, wie z.B. ein Ledger Wallet ([weitere Informationen dazu finden Sie hier](https://www.cryptocolo.fr/2023/02/08/not-your-keys-not-your-coins/)).
Um Ihr Binance-Konto zu erstellen, können Sie gerne unseren Binance-Referral-Link verwenden: [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280)
Es gibt viele Vorteile, wenn Sie ein Teil meiner Referrals sind (unter anderem die Verwendung eines speziellen Handelsbots, kontaktieren Sie mich für weitere Informationen).
#### Konfiguration der API-Schlüssel
Erstellen Sie zuerst Ihre API-Schlüssel auf Binance, indem Sie dieser Anleitung folgen (vergeben Sie ihnen nur Leserechte): [Erstellen von API-Schlüsseln auf Binance](https://www.binance.com/fr/support/faq/comment-cr%C3%A9er-des-cl%C3%A9s-api-sur-binance-360002502072).
Tragen Sie dann Ihre API-Schlüssel auf der Modulseite ein:
![Screenshot Einrichtung](img/doc-020-setup.png?raw=true "Einrichtung")
Sie können sehen, dass das Modul Ihnen den aktuellen Preis von Bitcoin, der Referenzkryptowährung, anzeigt.

## Verwendung

### Rechnungsstellung
Ihre Standardrechnungen können jetzt in Kryptowährungen bezahlt werden!
Sie können den Zahlungslink verwenden, den Dolibarr auf Ihrer Rechnung anzeigt:
![Screenshot Verwendung 1](img/doc-030-use.png?raw=true "Verwendung 1")
Diese URL kann direkt in Ihre E-Mail-Vorlagen mit der Substitutionsvariable \__ONLINE_PAYMENT_TEXT_AND_URL__ integriert werden.

### Zahlungsseite: Schritt 1
Wenn Ihr Kunde diesem Link folgt, gelangt er zur Online-Zahlungsseite von Dolibarr. Im ersten Schritt wählt er das gewünschte Crypto/Netzwerk-Paar aus der angebotenen Liste aus:
![Screenshot Verwendung 2](img/doc-040-use.png?raw=true "Verwendung 2")

### Zahlungsseite: Schritt 2
Im zweiten Schritt wird ihm der aktuelle zu zahlende Betrag in der ausgewählten Kryptowährung angezeigt.
Er muss die Transaktionsinformationen eingeben, die er bereits durchgeführt hat (die Transaktionskennung ist optional und wird später automatisch auf Binance generiert).
![Screenshot Verwendung 3](img/doc-050-use.png?raw=true "Verwendung 3")

### Zahlungsseite: Schritt 3
Im letzten Schritt wird ihm die Bestätigung seiner Zahlung angezeigt.
![Screenshot Verwendung 4](img/doc-060-use.png?raw=true "Verwendung 4")

### Binance Wallet
Sie können den Saldo Ihrer Kryptowährungen in Ihrem Binance-Spot-Wallet über den Link "Binance Wallet" im linken Menü anzeigen:
![Screenshot Verwendung 5](img/doc-070-use.png?raw=true "Verwendung 5")

### Verlauf der Einzahlungen in Ihrem Wallet
Sie können den Einzahlungsverlauf der letzten 90 Tage auf den Einzahlungsadressen, die Sie in Binance erstellt haben, über den Menülink "Verlauf der Einzahlungen in Ihrem Wallet" anzeigen.
Der Status "1" zeigt eine bestätigte Transaktion an (und wird daher vom DoliBinance-Job berücksichtigt):
![Screenshot Verwendung 6](img/doc-080-use.png?raw=true "Verwendung 6")

### Transaktionen
Die Aufzeichnungen der Zahlungsseite, die von Ihren Kunden durchgeführt wurden, werden hier aufgelistet. Ein Status "1" zeigt eine abgeschlossene Transaktion an, die auf Binance eingegangen ist und deren zugehörige Rechnung als "bezahlt" markiert wurde:
![Screenshot Verwendung 7](img/doc-090-use.png?raw=true "Verwendung 7")

### Job zur Überprüfung der Transaktionen (automatisierte Aufgaben)
Transaktionen in der Blockchain können einige Minuten dauern, bis sie vom entsprechenden Netzwerk bestätigt werden. Daher ist es nicht möglich, Ihren Kunden vor der Zahlungsseite warten zu lassen.
Wir haben uns für eine verzögerte Verarbeitung der Transaktion entschieden, bei der regelmäßig Einzahlungen auf Ihren Empfangsadressen überprüft werden.
Die Verarbeitung erfolgt, wenn möglich, alle paar Minuten, wenn Ihre Einstellungen dies zulassen (überprüfen Sie, ob der Cron-Tab auf Ihrem Server aktiviert ist):
![Screenshot Verwendung 8](img/doc-100-use.png?raw=true "Verwendung 8")

Der Job kann wie jede automatisierte Aufgabe in Dolibarr bearbeitet werden, und Sie können ihn beispielsweise so einstellen, dass er nur einmal pro Stunde ausgeführt wird. Sie können ihn auch manuell starten:
![Screenshot Verwendung 9](img/doc-110-use.png?raw=true "Verwendung 9")

Sobald die Transaktion im Netzwerk bestätigt ist und der Job ausgeführt wurde, wird die entsprechende Rechnung automatisch als "bezahlt" markiert, und es wird eine private Notiz hinzugefügt, um die Aktion von DoliBinance anzuzeigen:
![Screenshot Verwendung 10](img/doc-120-use.png?raw=true "Verwendung 10")

## Bleiben Sie in Kontakt!
Dieses Modul ist ein erster Entwurf, der sich weiterentwickeln und verbessern wird, basierend auf Ihrem Feedback. Zögern Sie nicht, Ihre Ideen und Anmerkungen an [oandrade@aplose.fr](mailto:oandrade@aplose.fr) weiterzugeben.

## Übersetzungen

Übersetzungen können manuell bearbeitet werden, indem Sie die Dateien in den Verzeichnissen "langs" bearbeiten.

## Lizenzen

### Hauptcode

GPLv3.

### Dokumentation

Alle Texte und Readmes unterliegen der GFDL-Lizenz.
