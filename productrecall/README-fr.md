# RAPPEL PRODUIT POUR DOLIBARR

Pensez à la sécurité de vos clients en surveillant les alertes conso !  

![Screenshot ProductRecall](img/productrecall-screenshot-001.png?raw=true "ProductRecall")  

## Fonctionnalités

Ce module permet de suivre les rappels de produits de consommation officiels et d'être alerté par email.  
Il est possible de paramétrer un filtre sur les catégories de produits et/ou les sous catégories.  
Un widget est fourni en page d'accueil avec les derniers rappels.  
Un onglet dédié dans les fiches produits permet de consulter directement les rappels liés à chaque produit.  
Un travail planifié est fourni et doit être activé. Lors du premier lancement, tous les rappels présents sur le site RappelConso officiel sont rapatriés.

> **⚠️ Prérequis important** : Ce module nécessite l'installation préalable du module **AploseFramework** (gratuit) disponible sur [DoliStore](https://www.dolistore.com). L'AploseFramework est indispensable au bon fonctionnement de l'interface moderne.  

### Interface moderne

Le module inclut une interface moderne et intuitive pour la consultation des rappels :
- **Recherche instantanée** : Filtrage en temps réel sur les données
- **Navigation fluide** : Déplacement rapide entre les pages
- **Affichage visuel** : Présentation des rappels sous forme de cartes avec images
- **Onglet Produit** : Consultation directe des rappels liés à chaque produit dans sa fiche
- **Détails complets** : Consultation de toutes les informations de rappel
- **Téléchargement PDF** : Accès direct aux documents officiels

## Configuration

### Email
Afin de recevoir les emails d'alerte conso, il est nécessaire d'avoir paramétré l'envoi d'email dans Dolibarr au préalable puis d'ajouter une adresse mail (ou plusieurs séparées par des virgules) dans le champ prévu à cet effet.  
![Screenshot email](img/productrecall-screenshot-002.png?raw=true "Email")  

### Filtre Catégories et Sous-catégories
Vous pouvez sélectionner autant de filtres de catégories et/ou sous-catégories que souhaité.  
![Screenshot filtres](img/productrecall-screenshot-003.png?raw=true "Filtres")  
Les filtres s'ajoutent donc inutile de sélectionner la catégorie "Alimentation" et la sous-catégorie "Viandes" car celle-ci est déjà contenue dans "Alimentation". Par contre vous pouvez sélectionner la sous-catégorie "Viandes" uniquement et vous n'aurez que les alertes de ces produits.  
Si vous souhaitez tous les rappels produits, ne mettez aucun filtre.  
![Screenshot filtres 2](img/productrecall-screenshot-004.png?raw=true "Filtres 2")  

## Utilisation

### Job de vérification des rappels produits (travaux automatisés)
Un job (programme lancé automatiquement ou manuellement) est fourni afin de charger manuellement la première fois votre base de données de rappels produits (pensez à paramétrer vos filtres selon vos préférences sinon vous chargerez tous les rappels produits).  
Dans "Outils d'administration -> Travaux planifiés", activez le job "Vérification des rappels produits" puis lancez le !  
![Screenshot job](img/productrecall-screenshot-005.png?raw=true "Job")  
La première fois il est nécessaire d'attendre quelques minutes...  
![Screenshot job 2](img/productrecall-screenshot-006.png?raw=true "Job 2")  

Ensuite, toutes les dix minutes, si vous avez correctement configuré votre Dolibarr afin qu'il exécute régulièrement les tâches planifiées, le job sera exécuté automatiquement et vous recevrez un email si vous avez des rappels vous concernant (et que l'email est bien paramétré...).  

Lorsque des rappels produits correspondent à vos filtres et que vous avez paramétré au moins une adresse et l'envoi d'email, vous recevrez un mail ainsi constitué :  
![Screenshot mail sent](img/productrecall-screenshot-007.png?raw=true "Mail sent")  
Vous remarquerez que de nombreuses informations sont à votre disposition comme le téléchargement de l'affichette PDF à afficher près de la caisse.  

### Interface moderne
L'interface moderne offre une expérience utilisateur améliorée avec :
- **Liste des rappels** : Affichage en cartes avec images produits  
![Liste des rappels](img/productrecall-screenshot-001.png?raw=true "Liste des rappels")  
- **Recherche instantanée** : Filtrage en temps réel sans rechargement  
![Filtre de recherche](img/productrecall-screenshot-001.png?raw=true "Filtre de recherche")  
- **Pagination** : Déplacement fluide entre les pages  
![Pagination](img/productrecall-pagination.png?raw=true "Pagination")  
- **Détails complets** : Fenêtre détaillée avec toutes les informations  
![Détails](img/productrecall-detail.png?raw=true "Détails")
- **Onglet Produit** : Consultation des rappels directement dans la fiche produit  
![Onglet Produit](img/productrecall-product-tab.png?raw=true "Onglet Produit")  

## 🚀 Services Aplose

### 💼 Développement et Intégration

L'équipe **Aplose** propose des services professionnels pour optimiser votre utilisation de Dolibarr :

- **🔧 Développement sur mesure** : Modules personnalisés adaptés à vos besoins spécifiques
- **🔗 Intégrations avancées** : Connexion avec vos outils existants (CRM, ERP, e-commerce)
- **📱 Applications mobiles** : Solutions pour vos équipes terrain
- **🔄 Migration de données** : Transfert sécurisé depuis vos anciens systèmes
- **⚡ Optimisation de performance** : Amélioration de la vitesse et de l'efficacité

### 🌐 Hébergement Ma Gestion Cloud

**Ma Gestion Cloud** est la solution d'hébergement Dolibarr portée par Aplose :

#### 🏆 Avantages de Ma Gestion Cloud
- **⚡ Performance optimisée** : Serveurs dédiés pour Dolibarr
- **🔒 Sécurité renforcée** : Sauvegardes automatiques conservées 30 jours et protection avancée
- **📞 Support technique** : Assistance prioritaire pour les clients Aplose
- **🔄 Mises à jour automatiques** : Dolibarr toujours à jour
- **📊 Monitoring 24/7** : Surveillance continue de vos services

#### 💰 Formules disponibles
- **🚀 Solo** : Parfait pour débuter avec Dolibarr à moindre coût 5€HT / mois / utilisateur
- **💼 Pro** : Solution professionnelle avec support prioritaire 14€HT / mois / utilisateurs illimités
- **🏢 VPS sur mesure** : Serveur virtuel dédié pour les grandes entreprises, configuration personnalisée selon vos besoins à partir de 100€HT / mois / serveur

#### Essai 45 jours gratuits sur [Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)


### 📞 Contact et Support

- **🌐 Site web** : [www.aplose.fr](https://www.aplose.fr)
- **📧 Email** : [contact@aplose.fr](mailto:contact@aplose.fr)

## Licenses

### Code principal
GPLv3.

### Documentation
Tous les textes et readmes sont sous licence GFDL.