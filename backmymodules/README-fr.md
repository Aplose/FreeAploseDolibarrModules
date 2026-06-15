# BACKMYMODULES POUR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## Fonctionnalités

Attention : ce module est destiné aux administrateurs et n'offre aucune fonctionnalité à un utilisateur n'ayant pas d'utilisateur "admin" sur son Dolibarr.  
  
Ce module a été créé pour répondre au besoin d’un client qui n’avait plus aucune nouvelle du prestataire ayant acheté sur Dolistore et installé pour lui des modules d’extension sur son Dolibarr pour un montant assez élevé. 
Voulant reprendre possession de ses données (tout à fait possible grâce à la sauvegarde Dolibarr) et de ces modules, il est apparu indispensable de trouver une solution. 
Notez que c’est le prestataire qui, évidemment, gérait l’hébergement qui allait bientôt expirer… 
BackMyodules est ainsi né.  
  
Avez vous remarqué qu'il est difficile de gérer les modules complémentaires une fois qu'on les a installé sur Dolibarr ?  
Il est facile désormais de les installer mais pas de les sauvegarder ni de les supprimer... 
Or, lorsque vous mettez à jour Dolibarr das une nouvelle version, il n'est pas rare qu'un ancien module même désactivé pose problème et rende alors votre Dolibarr non fonctionnel ou instable.  
BackMyModules vous permet de sauvegarder l'ensemble de vos modules complémentaires (génial quand vous n'avez pas conservé une copie locale de votre module et que votre lien de téléchargement Dolistore est expiré...) dans une archive Zip téléchargeable.  
  
Une fois votre sauvegarde réalisée (et pas avant siouplais...) vous pourrez supprimer les modules non actifs.  
Les données de ces modules ne sont pas supprimées (répertoire "documents" de Dolibarr).  

## Manuel utilisateur

### Installation et activation
Vous pouvez installer et activer BackMyModules simplement comme n’importe quel module Dolibarr. Il s’installera dans le répertoire des modules complémentaires (/custom/).  

### Création de l’archive de sauvegarde
Les fonctionnalités de BackMyModules sont uniquement accessible via la page de configuration du module ou par le menu « BackMyModules » présent dans « Outils d’administration ».  
Pour créer une archive « Zip » de tous les modules présents dans /custom/ il vous suffit de cliquer sur le bouton prévu à cet effet :  
![BackMyModules](img/backmymodules001_fr.png?raw=true "BackMyModules")  

### Supression d’un module désactivé
BackMyModules liste l’ensemble des modules complémentaires présents sur votre Dolibarr. Seuls les modules qui sont désactivés peuvent être supprimés. Les données (tables et fichiers), si elles ne sont pas supprimées par l’éditeur du module lorsqu’il est désactivé, sont conservées et vous pourrez le vérifier en réinstallant les modules (via un zip conforme contenant le répertoire du module concerné et se nommant module_xxx-1.0.zip par exemple).  
![Liste des modules complémentaires](img/backmymodules002_fr.png?raw=true "Liste des modules complémentaires")  
 
Si vous cliquez sur le lien de suppression du module, une confirmation vous est demandée. Si vous confirmer, le répertoire du module est supprimé définitivement  
![Confirmation](img/backmymodules003_fr.png?raw=true "Confirmation")  

##Restons en contact !
N'hésitez pas à me faire part de vos idées et remarques à [oandrade@aplose.fr](mailto:oandrade@aplose.fr).  

## Licences
Comme Dolibarr et tous les modules que vous pouvez obtenir sur le Dolistore, notre moule est sous licence libre. Vous pouvez améliorer le code, vous l’approprier et le partager, gratuitement ou moyennant rémunération mais en conservant obligatoirement la licence d’origine. Rappelez vous ce qu’est un logiciel libre et vous comprendrez pourquoi il est nécessaire d’acheter (à un prix modéré) les modules des développeurs pour leur permettre de vivre du fruit de leur travail. 

### Code principal
GPLv3. Voir le fichier COPYING pour plus d’information.

### Documentation
Tous les textes et readme sont sous licence GFDL.