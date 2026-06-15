# Fiche produit DoliStore — DoliCarbon

> Document interne pour la soumission sur [Dolistore](https://www.dolistore.com).  
> **Ne pas inventer** : les éléments ci-dessous sont alignés sur `modDoliCarbon.class.php`, les écrans `htdocs/custom/dolicarbon/*.php` et l’application intégrée `index.php` / routes Angular.

---

## Identification

| Champ | Valeur |
|--------|--------|
| **Nom commercial** | DoliCarbon |
| **Référence technique** | `dolicarbon` |
| **Éditeur** | Aplose — [https://www.aplose.fr](https://www.aplose.fr) |
| **Famille (module)** | Aplose - Ma Gestion Cloud |
| **Licence** | GPLv3+ (fichier `COPYING`) |
| **Version** | 1.0.0 (voir `modDoliCarbon.class.php`) |
| **Compatibilité annoncée** | Dolibarr **17.x minimum** (`need_dolibarr_version`), **PHP 8.1+** (`phpmin`) |

---

## Accroche courte (≤ 250 caractères suggérés)

**FR :**  
Pilotez vos bilans GES (scopes 1, 2 et 3) dans Dolibarr : inventaires par exercice, lignes d’activité, facteurs d’émission, actions de réduction, cadrage méthodologique, tableaux de bord et exports — avec une interface web intégrée et des écrans classiques Dolibarr.

**EN :**  
Manage GHG inventories (scopes 1, 2 and 3) in Dolibarr: year-based inventories, activity lines, emission factors, reduction actions, methodological framing, dashboards and exports — via an embedded web UI and standard Dolibarr screens.

---

## Description longue (Dolistore)

### FR

**DoliCarbon** est un module Dolibarr pour structurer et suivre un **inventaire d’émissions de gaz à effet de serre** au sein de votre instance.

**Fonctionnalités principales (implémentées) :**

- **Bilans** : périodes d’inventaire par année, référence type `CARBON-{année}-{séquence}`, totaux et objectifs.
- **Lignes d’activité** : saisie par **scope** (1, 2, 3) et **catégories** alignées sur la nomenclature du module, quantités, unités, lien avec des **facteurs d’émission**, calcul des émissions.
- **Facteurs d’émission** : bibliothèque de facteurs (dont champs de versionnement / gouvernance selon schéma base).
- **Actions de réduction** : actions liées à un bilan, gains estimés, coûts, scores, statuts (planifié / en cours / réalisé selon le modèle objet).
- **Cadrage méthodologique** : périmètre organisationnel et opérationnel, exclusions, notes, matérialité, années de référence / reporting (objet dédié).
- **Tableau de bord** : vue agrégée (KPI, scopes, série temporelle, catégories dominantes) branchée sur les données d’inventaire.
- **Rapports** : restitution multi-volets (exécutif / analyste / annexes méthodologiques) et **exports** (CSV / JSON selon l’implémentation actuelle).
- **Qualité & traçabilité** : suivi de workflow sur les lignes, **commentaires**, journal d’audit.
- **Plan de transition** : vue synthétique des actions (type coût / gain / roadmap).
- **Import** : assistant d’import depuis les données Dolibarr (`carbon_import.php` et flux AJAX associés).
- **Options système** (administration module) :  
  - rappels à la validation (**facture fournisseur**, note de frais, expédition) ;  
  - **import automatique optionnel** des totaux de **facture fournisseur validée** vers une ligne d’activité (sous conditions : bilan brouillon, facteur adapté — voir trigger).

**Deux modes d’accès :**

1. **Interface intégrée** (`/custom/dolicarbon/index.php`) pour les utilisateurs disposant du droit `dolicarbon->read` : navigation par menus (tableau de bord, bilans, cadrage, lignes, facteurs, actions, qualité, transition, import, rapport).  
2. **Écrans PHP historiques** (liste des bilans, fiches, listes d’écritures, facteurs, actions, import, rapport) accessibles depuis le menu module.

**Droits** : lecture, écriture, suppression, validation (workflow / verrouillage selon les règles du module).

**Langues fournies avec les chaînes Dolibarr du module** : français (`fr_FR`), anglais (`en_US`) — fichiers sous `langs/`.

---

### EN (short listing for international Dolistore field)

DoliCarbon provides year-based **GHG inventories**, **activity lines** (scopes 1–3, categories), an **emission factor** library, **reduction actions**, **methodological framing**, **dashboard**, **reporting with exports**, **import wizard** from Dolibarr data, optional **trigger-based hints** and **optional auto-import** of validated supplier invoices (when enabled in setup).  
Embedded UI plus legacy PHP screens. Permissions: read, write, delete, validate.

---

## Points forts (puces marketing factuelles)

- Centralise l’inventaire carbone **dans Dolibarr** (données et droits unifiés).
- Couvre **scopes 1, 2 et 3** avec catégories explicites.
- Relie **activités** et **facteurs** pour un calcul traçable.
- **Actions de réduction** et vue **transition** pour le pilotage.
- **Cadrage** pour documenter périmètre et méthode.
- **Exports** du rapport (**CSV** et **JSON**) et **instantané figé** (snapshot) depuis l’écran Rapport de l’interface intégrée.
- **Paramètres** clairs pour les comportements à la validation (factures fournisseurs, etc.).

---

## Prérequis techniques

- Dolibarr **≥ 17** (contrainte déclarée dans le descripteur du module).
- **PHP ≥ 8.1**.
- Déploiement sous `htdocs/custom/dolicarbon` (ZIP module standard).

---

## Promotion Ma Gestion Cloud (à reprendre sur la fiche publique)

Hébergez Dolibarr avec les modules Aplose sur **[Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)** : instance prête à l’emploi, sauvegardes et accompagnement.  
Inscription / essai : [https://aplose.ma-gestion-cloud.fr/custom/sellyoursaas/myaccount/register.php](https://aplose.ma-gestion-cloud.fr/custom/sellyoursaas/myaccount/register.php?origin=dolistore) (paramètre `origin=dolistore` pour le suivi).

Contact : [contact@aplose.fr](mailto:contact@aplose.fr)

> **Note :** Ne pas attribuer de code plan `MGCxxxx` sans validation commerciale ; l’URL ci-dessus suffit pour le tracking générique Dolistore.

---

## Captures d’écran (checklist)

- [ ] Menu principal DoliCarbon  
- [ ] Tableau de bord (interface intégrée)  
- [ ] Fiche bilan ou liste des bilans  
- [ ] Écran facteurs ou ligne d’activité  
- [ ] Page de configuration module (les deux options trigger / import auto)

---

## Après publication

Remplacer dans les README la mention « disponible sur Dolistore » par le **lien produit officiel** `https://www.dolistore.com/product.php?id=…` une fois l’ID connu.
