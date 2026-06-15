# DOLIBINANCE POUR DOLIBARR

![Screenshot dolibinance](img/screenshot_dolibinance.png?raw=true "DoliBinance")  

## Fonctionnalités

Ce module connecte votre compte Binance à votre Dolibarr.
Si vous n'en avez pas encore un, suivez ce lien : [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280).  
Cela vous permettra d'ajouter simplement le paiement en Bitcoin mais aussi d'accepter toute autre cryptomonnaie acceptée par Binance.  
Quand un client souhaite régler en cryptomonnaie, le module utilise le cours moyen actuel lissé sur 24h de la cryptomonnaie pour le paiement de la facture à payer ainsi que l'adresse de réception de votre portefeuille binance. Le client saisit ensuite les informations de la transaction qu'il a réalisées : identifiant de transaction, adresse d'envoi, montant. Aussitôt que le dépôt arrive sur votre compte binance, la facture passe à "payée".  
Vous pouvez suivre les soldes de vos actifs cryptomonnaies de votre portefeuille spot Binance directement dans Dolibarr sans avoir besoin d'utiliser l'application Binance.  

## A venir
Les permissions utilisateurs ne sont pas encore développées dans cette version et viendront plus tard. Toutes les fonctionnalités sont accessibles en accès libre à tout utilisateur de Dolibarr, cependant, aucune action importante n'est possible sans être administrateur.  
Selon les besoins en comptabilité remontés lors de l'utilisation du module, les éléments correspondant seront automatiquement créés dans Dolibarr.
N'hésitez pas à remonter vos remarques à ce sujet à [Olivier Andrade Sanchez](mailto:oandrade@aplose.fr?subject=[DoliBinanceRequest]-).

## Configuration

### Dictionnaire
Afin de proposer à vos clients un règlement en cryptomonnaie, vous devez ajouter les informations nécessaires dans le dictionnaire "Répertoire des adresses de paiement Binance" (Accueil->Configuration->Dictionnaire), les adresses de réception des cryptomonnaies que vous souhaitez accepter en paiement de vos factures.  
![Screenshot dictionnary 1](img/doc-010-dictionnary.png?raw=true "Dictionnary 1")  
Dans ce dictionnaire, veuillez ajouter les informations issues de vos différentes adresses de réception créées dans Binance ([voir la documentation Binance pour cela](https://www.binance.com/fr/support/faq/comment-d%C3%A9poser-des-cryptos-sur-binance-115003764971)).  
Par exemple, ici, trois adresses ont été saisies (dont deux pour Bitcoin sur deux réseaux différents).  
![Screenshot dictionnary 2](img/doc-011-dictionnary.png?raw=true "Dictionnary 2")  
Comme vous le voyez, vous pouvez activer ou désactiver des cryptomonnaies sans supprimer le paramétrage réalisé.  

### Page de paramètres
L'utilisation de ce module impose l'existence d'un compte Binance.  
Pourquoi Binance ? Parce qu'il s'agit de la place mondiale d'échange de cryptomonnaies la plus importante. Cependant attention, vous ne détenez pas les clés privée et publique du portefeuille correspondant, donc je vous conseille de n'utiliser cette plateforme que pour envoyer et recevoir des paiements ou pour faire du trading.  
Si vous souhaitez sécuriser vos cryptos alors préférez un portefeuille de type "Hard wallet" comme une clé Ledger ([lire l'article à ce sujet, ici](https://www.cryptocolo.fr/2023/02/08/not-your-keys-not-your-coins/)).  
Pour créer votre compte Binance, n'hésitez pas à utiliser notre lien de parrainnage Binance : [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280)  
Les avantages à faire partie de mes filleuls sont nombreux (utilisation d'un bot de trading spécifique entre autres, contactez moi pour plus d'informations).  
#### Paramétrage des clés d'API
Dans un premier temps, veuillez créer vos clés d'API sur Binance en suivant cette documentation (ne donnez que des permissions en lecture à celles-ci) : [Créez vos clés d'API sur Binance](https://www.binance.com/fr/support/faq/comment-cr%C3%A9er-des-cl%C3%A9s-api-sur-binance-360002502072)  
Dans un second temps, veuillez reporter vos clés d'API dans la page de paramètrage du module :  
![Screenshot setup](img/doc-020-setup.png?raw=true "Setup")  
Vous pouvez noter que le module vous donne le prix courant du Bitcoin, cryptomonnaie de référence.  

## Utilisation

###Facturation
Vos factures standards peuvent maintenant être réglées en Cryptomonnaies !  
Vous pouvez utiliser le lien de paiement fourni par Dolibarr et affiché sur votre facture :  
![Screenshot use 1](img/doc-030-use.png?raw=true "Use 1")  
Cette url peut être directement intégrée dans vos modèles d'email en utilisant la variable de substitution : \__ONLINE_PAYMENT_TEXT_AND_URL__.  

###Page de paiement : étape 1
En suivant ce lien, votre client arrive sur la page de paiement en ligne de Dolibarr. Lors de la première étape, il choisit le couple Crypto/Réseau de son choix parmi la liste proposée :  
![Screenshot use 2](img/doc-040-use.png?raw=true "Use 2")  

###Page de paiement : étape 2
Lors de la deuxième étape, on lui présente le montant actuel à payer, dans la cryptomonnaie choisie.  
Il va devoir saisir les informations de la transaction qu'il a ou va réaliser (l'identifiant de transaction n'est pas obligatoire et sera obtenu plus tard sur Binance automatiquement).  
![Screenshot use 3](img/doc-050-use.png?raw=true "Use 3")  

###Page de paiement : étape 3
Lors de la dernière étape, on lui présente l'enregistrement de son règlement.  
![Screenshot use 4](img/doc-060-use.png?raw=true "Use 4")  

###Portefeuille Binance
Vous pouvez visualiser l'état des soldes de vos cryptomonnaies dans votre portefeuille Spot Binance en utilisant le lien "Portefeuille Binance" du menu gauche :  
![Screenshot use 5](img/doc-070-use.png?raw=true "Use 5")  

###Historique des dépôts sur votre portefeuille
Vous pouvez visualiser l'historique à 90 jours des dépôts réalisés sur les adresses de dépôts que vous avez créées sur Binance en utilisant le lien de menu "Historique des dépôts sur votre portefeuille".  
L'état à "1" indique une transaction validée (et donc prise en compte par le job de DoliBinance) :  
![Screenshot use 6](img/doc-080-use.png?raw=true "Use 6")  

###Transactions
Les enregistrements de la page de paiement réalisés par vos clients sont listés ici, un statut à "1" indique une transaction terminée, reçue sur Binance et dont la facture correspondante est passée à "Payée" :
![Screenshot use 7](img/doc-090-use.png?raw=true "Use 7")  

###Job de validation des transaction (travaux automatisés)
Les transactions blockchain peuvent mettre plusieurs minutes avant d'être validées par le réseau correspondant, il n'est donc pas possible de laisser attendre votre client devant la page de paiement.  
Nous avons choisi un traitement différé de la transcation en validant régulièrement les dépôts arrivant sur vos adresses de réception.  
Le traitement est lancé si possible toutes les minutes si votre paramétrage le permet (vérfiez que la crontab est activée sur votre serveur) :
![Screenshot use 8](img/doc-100-use.png?raw=true "Use 8")  
  
Le job est modifiable comme tout traitement automatisé dans Dolibarr et vous pouvez l'éditer afin de ne l'éxécuter qu'une fois par heure par exemple. Vous pouvez aussi choisir de le lancer manuellement :  
![Screenshot use 9](img/doc-110-use.png?raw=true "Use 9")  
  
Une fois la transaction validée sur le réseau et le job exécuté, la facture correspondante passe automatiquement à "Payée" et une note privée est ajoutée pour indiqué l'action de DoliBinance :
![Screenshot use 10](img/doc-120-use.png?raw=true "Use 10")  
  
##Restons en contact !
Ce module est un premier jet qui va évoluer et progresser en fonction de vos retours. N'hésitez pas à me faire part de vos idées et remarques à [oandrade@aplose.fr](mailto:oandrade@aplose.fr).  

## Translations

Les traductions peuvent être complétées manuellement en éditant les fichiers des répertoires *langs*.

## Licenses

### Code principal

GPLv3.

### Documentation

Tous les textes et readmes sont sous licence GFDL.
