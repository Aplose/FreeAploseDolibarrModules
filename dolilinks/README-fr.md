# DOLILINKS POUR [DOLIBARR ERP & CRM](https://www.dolibarr.org)

## Description du module

DoliLinks est un module pour Dolibarr qui permet de créer et gérer des liens hiérarchiques entre les sociétés (tiers). Il offre une visualisation claire des relations parent-enfant entre les entreprises et facilite la gestion des structures organisationnelles complexes.

## Fonctionnalités principales

### 1. Gestion des liens entre sociétés

Le module permet de créer des relations hiérarchiques entre les sociétés :
- **Liens parent-enfant** : Définir quelles sociétés sont parents ou enfants d'autres sociétés
- **Types de liens personnalisables** : Créer des types de liens spécifiques (filiale, succursale, partenaire, etc.)
- **Prévention des liens circulaires** : Le système empêche de lier une société à elle-même

![Interface de gestion des liens entre sociétés : ajout d'un parent](img/screenshot_dolilinks_links02.png)
![Interface de gestion des liens entre sociétés](img/screenshot_dolilinks_links03.png)


### 2. Visualisation hiérarchique

#### 2.1 Affichage dans la fiche société
Les liens sont automatiquement affichés dans la fiche de chaque société :
- **Section Parents** : Liste des sociétés parentes avec liens directs
- **Section Enfants** : Liste des sociétés filles avec liens directs
- **Boutons d'action** : Ajout rapide de nouveaux liens et accès au diagramme

![Affichage des liens dans la fiche société](img/screenshot_dolilinks_links01.png)

#### 2.2 Diagramme interactif
Visualisation graphique complète des relations :
- **Réseau hiérarchique** : Affichage de tous les parents, enfants et petits-enfants
- **Navigation interactive** : Clic sur les nœuds pour accéder aux fiches sociétés
- **Légende colorée** : Distinction visuelle entre parents (gris), société courante (vert) et enfants (bleu)
- **Types de liens** : Affichage des labels des types de liens sur les connexions

![Capture d'écran : Diagramme interactif des relations](img/screenshot_diagram_interactive.png)

### 3. Gestion des types de liens

#### 3.1 Configuration des types
- **Création de types personnalisés** : Définir des types de relations spécifiques à votre organisation
- **Gestion centralisée** : Interface d'administration pour créer et modifier les types
- **Dictionnaire intégré** : Les types sont stockés dans le dictionnaire Dolibarr

![Interface de gestion des types de liens : accès au dictionnaire](img/screenshot_link_types_management01.png)
![Interface de gestion des types de liens : ajout de type de liens](img/screenshot_link_types_management02.png)

### 4. Intégration avec l'écosystème Dolibarr

#### 4.1 Hooks et extensions
- **Intégration native** : Le module s'intègre parfaitement dans l'interface Dolibarr
- **Hooks personnalisés** : Extension des fonctionnalités via le système de hooks

#### 4.2 Filtrage des contacts de facturation
- **Filtrage intelligent** : Option pour ne proposer que les contacts de facturation lors de l'envoi d'emails (n'envoyez pas la facture aux clients de vos clients !!!)
- **Contacts de tiers enfants** : Affichage des contacts des sociétés liées dans les fiches de contact
- **Configuration flexible** : Activation/désactivation via les paramètres du module

![Filtrage des contacts enfants sur une commande](img/screenshot_contact_filtering_order.png)
![Filtrage des contacts de facturation sur envoi email](img/screenshot_contact_filtering_invoice_email.png)

#### 4.3 Compatibilité
- **Multi-entité** : Support complet du mode multi-entité Dolibarr
- **Sécurité** : Respect des droits d'accès et de la sécurité Dolibarr
- **Traductions** : Support multilingue (français, anglais, allemand, espagnol)

### 5. Fonctionnalités avancées

#### 5.1 Import de données
- **Migration depuis SocParent** : Outil d'import pour migrer les données du module SocParent

![Interface d'import de données](img/screenshot_admin01.png)

#### 5.2 Rapports et statistiques
- **Compteurs automatiques** : Affichage du nombre de parents/enfants pour chaque société
- **Navigation facilitée** : Liens directs vers les fiches des sociétés liées
- **Vue d'ensemble** : Accès rapide au diagramme complet des relations

## Installation

### Prérequis
- Dolibarr ERP & CRM installé
- Droits d'administration pour l'installation du module

### Installation via l'interface Dolibarr
1. Téléchargez le module depuis [Dolistore.com](https://www.dolistore.com)
2. Connectez-vous à Dolibarr en tant qu'administrateur
3. Allez dans `Accueil > Configuration > Modules > Déployer un module externe`
4. Uploadez le fichier ZIP du module
5. Activez le module dans la liste des modules disponibles

### Configuration initiale
1. Accédez à `Configuration > Modules > DoliLinks`
2. Configurez les paramètres selon vos besoins
3. Créez vos types de liens personnalisés si nécessaire

## Utilisation

### Créer un lien entre sociétés
1. Ouvrez la fiche de la société concernée
2. Dans la section "Parents" ou "Enfants", cliquez sur le bouton "+"
3. Sélectionnez la société à lier dans la liste déroulante
4. Choisissez le type de lien (optionnel)
5. Cliquez sur "Ajouter"

### Visualiser les relations
1. Depuis la fiche société, cliquez sur "Voir le diagramme"
2. Le diagramme interactif s'affiche avec toutes les relations
3. Cliquez sur n'importe quel nœud pour accéder à la fiche de la société

### Gérer les types de liens
1. Allez dans `Configuration > Dictionnaires > Type de lien entre sociétés`
2. Créez, modifiez ou supprimez les types selon vos besoins

![Dictionnaire type de lien](img/screenshot_link_types_management01.png)
![Dictionnaire type de lien : gestion](img/screenshot_link_types_management02.png)


## Configuration

### Paramètres disponibles
- **Filtrage des contacts** : Option pour ne proposer que les contacts de facturation lors de l'envoi d'emails

### Personnalisation
Le module peut être étendu via :
- Hooks personnalisés
- Templates modifiables
- Classes PHP extensibles

## Ma Gestion Cloud - Votre Solution Cloud

Découvrez la puissance de DoliLinks dans le cloud avec Ma Gestion Cloud, notre plateforme complète de gestion d'entreprise. Accédez instantanément à Dolibarr avec tous nos modules premium pré-installés et configurés.

### Plans Tarifaires

#### 🚀 Plan Solo
**5€/mois par utilisateur**
- Essai 45 jours
- Utilisateurs individuels
- Accès complet à Dolibarr
- Accès sécurisé en ligne
- Support professionnel
- Sauvegarde automatisée quotidienne
- Sauvegarde de base

#### 💼 Plan Pro
**14€/mois par instance**
- Essai 45 jours
- Équipes en croissance
- Support prioritaire
- Sauvegarde automatisée quotidienne

#### 🏢 Plan Entreprise : VPS dédié
**100€/mois + 300€ de frais d'installation**
- Grandes organisations
- Solutions personnalisées
- Tous les modules + développement sur mesure
- Support dédié 24/7
- Sauvegarde en temps réel
- Chef de compte dédié
- Accès API complet

### Avantages du Cloud
- ✅ Aucune installation requise
- ✅ Mises à jour automatiques
- ✅ Hébergement de données sécurisé
- ✅ Garantie de disponibilité 99,9%
- ✅ Accès mobile
- ✅ Infrastructure évolutive

### À Propos d'Aplose
Aplose est un fournisseur leader de solutions de gestion d'entreprise, spécialisé dans les implémentations ERP et CRM. Avec des années d'expérience dans l'écosystème Dolibarr, nous développons des modules de haute qualité qui étendent et améliorent les fonctionnalités de base de Dolibarr.

#### Notre Expertise
- Personnalisation et développement Dolibarr
- Optimisation des processus métier
- Solutions de gestion multi-entreprises
- Support et conseil professionnel

#### Pourquoi Choisir Aplose ?
- Compréhension approfondie des besoins métier
- Bilan prouvé dans les implémentations ERP
- Support et maintenance continus
- Engagement envers l'excellence open-source

## Support et développement

### Licence
- **Code principal** : GPLv3 ou version ultérieure
- **Documentation** : GFDL

### Support
- Documentation complète dans le module
- Compatible avec les versions récentes de Dolibarr

### Développement
Le module est développé en respectant les standards Dolibarr :
- Architecture MVC
- Système de hooks
- Gestion des traductions
- Sécurité intégrée

### Liens Utiles
- [Essayer Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)
- [Contacter les Ventes](mailto:contact@aplose.fr)
- [Visiter le Site Aplose](https://www.aplose.fr)
