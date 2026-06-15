# DoliCarbon pour [Dolibarr ERP & CRM](https://www.dolibarr.org)

**DoliCarbon** est un module Dolibarr pour constituer et piloter des **bilans d’émissions de gaz à effet de serre (GES)** au sein de votre instance : bilans par exercice, lignes d’activité avec facteurs d’émission, actions de réduction, cadrage méthodologique, tableaux de bord, restitution, et liens optionnels avec les flux d’achat Dolibarr.

D’autres modules externes sont disponibles sur [Dolistore](https://www.dolistore.com).

## Fonctionnalités

### Bilans d’inventaire

- Créer un bilan par période de reporting ; chaque fiche reçoit une référence automatique **`CARBON-{année}-{séquence}`** (voir la classe `DoliCarbonBilan`).
- Définir les bornes de période (dates début/fin), un lien optionnel vers un tiers, les **totaux** et **objectifs** en tCO2e, des notes, et un statut de workflow (brouillon / validé / archivé).

### Lignes d’activité (écritures)

- Saisir les émissions par **scope** (1, 2 ou 3) et **catégorie** (nomenclature du module), avec quantités, unités et lien vers un **facteur d’émission** le cas échéant.
- Les lignes gèrent le workflow de validation, les **commentaires**, la traçabilité et la liaison à des objets sources (par exemple factures fournisseurs importées).

### Facteurs d’émission

- Maintenir une bibliothèque de **facteurs d’émission** (avec champs de versionnement dans le modèle de données) et activer ou désactiver les facteurs selon les règles prévues par le schéma.

### Actions de réduction

- Associer des **actions** à un bilan : gains estimés, coûts, scores et statut pour suivre le plan de réduction.

### Cadrage méthodologique

- Documenter le périmètre, les exclusions, la matérialité, les années de référence et de reporting, et les notes associées dans un objet de cadrage utilisé par la restitution.

### Tableau de bord

- Visualiser des indicateurs agrégés : totaux par scope, série temporelle et principales catégories à partir des données d’inventaire.

### Rapports

- Utiliser l’écran **Rapport** de l’application intégrée : synthèse exécutive, vue analyste, annexe méthodologique, texte de **garde-fou de communication** le cas échéant, plage d’**incertitude**, export **CSV** ou **JSON**, et **instantané figé** (snapshot) pour un état reproductible.

### Qualité des données et collaboration

- Utiliser l’écran **Qualité** pour les étapes de workflow, les **commentaires** et une traçabilité orientée audit sur les lignes.

### Plan de transition

- Utiliser l’écran **Transition** pour comparer les actions de réduction (synthèse type coût / bénéfice selon l’interface).

### Import depuis Dolibarr

- Lancer l’assistant **Import** pour rapatrier des données Dolibarr vers les lignes d’inventaire (voir `carbon_import.php` et les services AJAX associés).

### Déclencheurs optionnels (administration)

Dans **Accueil > Configuration > Modules > DoliCarbon > Configuration**, deux options sont proposées (constantes `DOLICARBON_TRIGGER_NOTIFY` et `DOLICARBON_AUTO_IMPORT_SUPPLIER_INVOICE`) :

1. **Messages à la validation**  
   Si l’option est activée, la validation d’une **facture fournisseur**, d’une **note de frais** ou d’une **expédition** peut afficher un message Dolibarr orientant vers DoliCarbon (avec un lien vers l’import pour les factures fournisseurs).

2. **Ligne automatique depuis une facture fournisseur**  
   Lorsque cette option et les messages sont activés, la validation d’une **facture fournisseur** peut **créer automatiquement** une ligne **Scope 3** en catégorie **`purchases_services`**, en utilisant le **total HT** en **EUR**, **uniquement s’il existe** un bilan en **brouillon** et un **facteur d’émission actif** adapté (scope 3, même catégorie). Les doublons sont évités par un **hash d’import**.

Si ces options sont désactivées, aucun message ni aucune ligne automatique n’est généré.

## Interface utilisateur

- **Application web intégrée** (menu **DoliCarbon** → `custom/dolicarbon/index.php`) : tableau de bord, bilans, cadrage, lignes, facteurs, actions, qualité, transition, import, rapport. Le droit **lire** le module est requis.
- Les **écrans PHP classiques** restent disponibles (liste des bilans, facteurs, etc. : `carbon_bilan_list.php`, `carbon_factors.php`, autres pages `carbon_*.php` fournies).

Le bundle Angular embarque les libellés d’interface en **français** (`assets/i18n/fr.json`). Les chaînes du module Dolibarr sont fournies en **français** (`fr_FR`) et **anglais** (`en_US`) sous `langs/`.

## Droits

Le module définit quatre droits : **lire**, **créer/modifier**, **supprimer**, et **valider** (validation / verrouillage du workflow d’inventaire). Affectez-les par groupe dans **Utilisateurs & groupes**.

## Prérequis

- **Dolibarr 17** ou version compatible ultérieure selon le descripteur du module (`need_dolibarr_version`).
- **PHP 8.1** minimum (`phpmin`).

Aucun autre module Dolibarr n’est déclaré comme dépendance obligatoire ; les fonctions optionnelles supposent que les objets Dolibarr concernés (ex. factures fournisseurs) existent lorsque vous les utilisez.

## Installation

Prérequis : une installation fonctionnelle de Dolibarr ERP & CRM. Téléchargement sur [dolibarr.org](https://www.dolibarr.org). Des offres hébergées existent également (voir ci-dessous).

### Depuis une archive ZIP

Si le module est distribué sous la forme `module_dolicarbon-x.y.z.zip` (par exemple depuis [Dolistore](https://www.dolistore.com)), utilisez **Accueil > Configuration > Modules > Déployer / installer un module externe** et importez l’archive.

### Étapes finales

1. Se connecter en administrateur.
2. Ouvrir **Configuration > Modules**, activer **DoliCarbon**.
3. Ouvrir **Configuration > Modules > DoliCarbon > Configuration** pour régler les deux options liées aux déclencheurs si besoin.

## Configuration

1. **Configuration du module** : **Accueil > Configuration > Modules > DoliCarbon > Configuration** — activer ou désactiver les messages à la validation et l’import automatique des factures fournisseurs comme décrit ci-dessus.
2. **Droits** : attribuer au minimum **lire** pour ouvrir l’application, puis **créer/modifier**, **supprimer** et **valider** selon les profils.

## Dolibarr dans le cloud (Ma Gestion Cloud)

Vous pouvez faire tourner Dolibarr avec les modules Aplose sur **[Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)** plutôt que sur votre propre serveur : hébergement, sauvegardes et assistance. Inscription / essai (suivi visiteurs Dolistore) :

**[Créer un compte — Ma Gestion Cloud](https://aplose.ma-gestion-cloud.fr/custom/sellyoursaas/myaccount/register.php?origin=dolistore)**

Contact : [contact@aplose.fr](mailto:contact@aplose.fr)

Les offres commerciales et les modules inclus dépendent de votre abonnement ; renseignez-vous auprès de Ma Gestion Cloud pour une offre adaptée.

## Assistance

- E-mail : [contact@aplose.fr](mailto:contact@aplose.fr)
- Éditeur : [Aplose](https://www.aplose.fr)

## Licences

### Code principal

GPLv3 ou, au choix, toute version ultérieure. Voir le fichier `COPYING`.

### Documentation

Cette documentation est sous licence [GFDL](https://www.gnu.org/licenses/fdl-1.3.en.html).

## Version

Version actuelle du module : **1.0.0** (voir `core/modules/modDoliCarbon.class.php`).
