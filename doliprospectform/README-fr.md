# DoliProspectForm — Guide utilisateur

Ce document présente le module **DoliProspectForm** du point de vue de l’utilisateur final : à quoi il sert, comment l’utiliser au quotidien et où trouver les principales actions dans Dolibarr.

---

## À quoi sert ce module ?

**DoliProspectForm** permet de proposer à vos prospects des **formulaires publics** accessibles sans connexion à Dolibarr : ils peuvent saisir leurs coordonnées et joindre des documents (par exemple des factures énergie au format PDF), directement depuis un lien que vous leur envoyez.

Le module couvre deux parcours :

- **Particulier** : personne physique, avec identité, adresse et pièces jointes.
- **Professionnel** : entreprise (recherche par raison sociale ou identifiants lorsque disponibles), adresse du siège, personne de contact et pièces jointes.

Une **page d’aiguillage** peut aussi être proposée : le prospect choisit le type de formulaire adapté à sa situation avant de continuer.

Les données saisies alimentent votre Dolibarr : création ou mise à jour d’une fiche **tiers**, d’un **contact** et dépôt des fichiers sur la fiche, selon le scénario (nouveau prospect ou dossier déjà lié à un tiers existant).

---

## Qui fait quoi dans l’organisation ?

| Rôle | Usage typique |
|------|-----------------|
| **Administrateur** | Active le module, configure les formulaires, les textes affichés aux visiteurs, les notifications et les liens « anonymes » si besoin. |
| **Commercial / utilisateur métier** | Utilise les **liens signés** ou les **invitations** pour faire compléter un dossier par le prospect, consulte les **soumissions** reçues. |
| **Prospect (externe)** | Ouvre le lien reçu, remplit le formulaire et envoie les pièces demandées, sans compte Dolibarr. |

---

## Premiers pas après activation

Une fois le module activé par l’administrateur :

1. Les **textes par défaut** des pages publiques (titres, introductions, aide sur les documents) sont prêts à l’emploi ; ils peuvent être **personnalisés** dans la configuration du module pour correspondre à votre ton et à vos consignes métier.
2. Des **modèles d’e-mail** utiles au module peuvent être créés automatiquement ; l’administrateur peut les retrouver dans la zone d’administration des modèles Dolibarr et les adapter.
3. Il est recommandé de vérifier avec l’administrateur que l’**envoi d’e-mails** Dolibarr et les **notifications** de nouvelles soumissions sont bien paramétrés pour que les bons interlocuteurs soient prévenus.

---

## Où trouver le module dans Dolibarr ?

Le module apparaît dans le **menu principal** sous une entrée dédiée (libellé du type « DoliProspectForm » ou équivalent selon votre traduction).

Depuis ce menu, vous accédez notamment à :

- un **tableau de bord** ou vue d’ensemble utile au suivi ;
- la **création d’invitations** (parcours guidé pour ouvrir un e-mail vers un prospect avec un contexte adapté) ;
- la **liste des formulaires renseignés** (historique des soumissions publiques traitées par le module).

Les **réglages avancés** (activation des formulaires particulier / professionnel, durée des liens, utilisateur technique, textes des pages, modèle de notification, e-mail de secours si aucun commercial n’est identifié, etc.) se trouvent dans la **configuration du module**, réservée aux profils autorisés (en général les administrateurs).

---

## Faire compléter un dossier par un prospect

### Liens depuis un e-mail ou un courrier Dolibarr

Lorsque vous rédigez un message depuis Dolibarr (par exemple depuis une fiche **tiers**), des **balises de substitution** peuvent insérer automatiquement des liens vers :

- la **page d’aiguillage** (choix particulier / professionnel) ;
- le **formulaire particulier** ;
- le **formulaire professionnel**.

Selon le contexte (utilisateur connecté, tiers concerné), le lien généré associe le bon **commercial** au dossier, ce qui facilite le suivi lorsque le prospect envoie le formulaire.

Votre administrateur peut vous indiquer quelles balises utiliser et comment les retrouver depuis l’écran d’aide ou de configuration du module.

### Lien « sans commercial » dans la configuration

Pour un usage sur votre **site web** (bouton, iframe, campagne générique), un **lien public stable** peut être affiché dans la configuration : il ne désigne pas de commercial dans le lien lui-même. Sur la page d’aiguillage, le visiteur peut éventuellement **indiquer l’e-mail d’un consultant** Dolibarr pour rattacher la demande au bon interlocuteur, lorsque cette option est proposée.

### Invitations depuis le menu du module

Le menu **Invitation** permet de créer rapidement un **tiers prospect** avec un libellé provisoire, d’associer un **e-mail** si besoin, puis d’ouvrir l’écran d’envoi de message Dolibarr avec les bons modèles et substitutions. Lorsque le prospect utilise le lien reçu, les données complètent la fiche tiers créée à l’invitation.

---

## Côté prospect : déroulement du formulaire

1. Le prospect ouvre le **lien** (e-mail, SMS, site web, etc.).
2. S’il arrive sur une **page d’aiguillage**, il choisit **Particulier** ou **Professionnel**.
3. Il remplit les **champs obligatoires** (identité, adresse, e-mail, etc.) et joint les **PDF** demandés (plusieurs fichiers possibles selon votre paramétrage).
4. Sur le formulaire **professionnel**, une **recherche d’entreprise** peut préremplir les informations publiques lorsque le service est disponible ; le prospect doit toutefois **valider** les informations et compléter le contact.
5. Un **contrôle visuel** (captcha) peut être demandé pour limiter les envois automatisés, selon la politique de sécurité de votre instance.
6. Après envoi, un **message de remerciement** confirme la prise en charge.

En cas de lien expiré ou de formulaire désactivé, un message clair invite le prospect à **contacter** votre entreprise.

---

## Suivi des demandes reçues

La **liste des formulaires renseignés** permet de consulter les soumissions enregistrées pour votre périmètre (selon vos droits Dolibarr) : type de formulaire, date, tiers associé, nombre de pièces, etc.

Les **notifications par e-mail** peuvent prévenir automatiquement :

- le **commercial** associé au lien ou au dossier, lorsqu’il est identifié ;
- sinon, une **adresse de secours** définie par l’administrateur (souvent l’e-mail général de la société), pour ne pas manquer une nouvelle demande.

Le contenu exact du message dépend du **modèle d’e-mail** choisi par l’administrateur ; il peut inclure un rappel des informations saisies et un lien vers la fiche tiers dans Dolibarr.

---

## Bonnes pratiques

- **Communiquez clairement** aux prospects quels documents joindre (factures, RIB, Kbis, etc.) : adaptez les textes d’aide dans la configuration du module.
- **Testez** un parcours complet (lien reçu, envoi, notification, fiche tiers) après tout changement important de configuration.
- **Protégez les liens** comme des accès sensibles : ils donnent accès à des formulaires qui modifient ou enrichissent vos données ; ne les publiez que sur des canaux de confiance.
- En cas de doute sur un **lien expiré**, demandez à l’administrateur la **durée de validité** configurée et renvoyez une **nouvelle invitation** si nécessaire.

---

## Besoin d’aide ?

Pour toute question sur le **fonctionnement métier** du module ou sur les **habilitations** dans Dolibarr, adressez-vous à l’**administrateur** de votre instance ou à votre **intégrateur**.

---

## Découvrir Ma Gestion Cloud

Vous souhaitez un **Dolibarr prêt à l’emploi**, hébergé et accompagné par des équipes françaises, avec des offres adaptées aux indépendants comme aux structures plus exigeantes ?

**Ma Gestion Cloud** propose des abonnements tout-en-un : installation, sauvegardes, infogérance et support, pour vous concentrer sur votre activité plutôt que sur l’infrastructure.

- **Site officiel** : [https://www.ma-gestion-cloud.fr/](https://www.ma-gestion-cloud.fr/)  
- **Essai gratuit**, tarifs transparents, utilisateurs illimités sur les formules concernées — tout est présenté sur le site pour comparer les offres et **démarrer** en quelques clics.

Faire héberger votre Dolibarr chez un **Preferred Partner** France, c’est aussi bénéficier d’un environnement adapté aux modules métiers et aux usages professionnels du quotidien.

---

## Modules Aplose sur Dolistore

**Aplose** développe une gamme de **modules Dolibarr** pensés pour la prospection, l’automatisation et l’efficacité métier (CRM, formulaires, intégrations, etc.). Les extensions certifiées ou commercialisées sont en général référencées sur la place de marché officielle Dolibarr.

- **Dolistore** (boutique des modules Dolibarr) : [https://www.dolistore.com](https://www.dolistore.com)  

Recherchez-y les contributions **Aplose** ou **Ma Gestion Cloud** pour étendre votre ERP avec des fonctionnalités éprouvées et documentées, en complément de **DoliProspectForm** et du reste de votre écosystème Dolibarr.

---

*Document à destination des utilisateurs métier et fonctionnels. Il ne remplace pas la documentation administrateur ni les consignes internes de votre organisation.*
