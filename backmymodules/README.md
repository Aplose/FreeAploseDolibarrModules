# BACKMYMODULES FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## Features

**Note:** This module is intended for administrators and does not offer any functionality to a user without an "admin" user on their Dolibarr.

This module was created to address the needs of a client who had lost contact with the provider who had purchased and installed extension modules on his Dolibarr for a significant amount. Wanting to regain control of his data (entirely possible thanks to Dolibarr backup) and these modules, it became essential to find a solution. Note that it was the provider who, of course, managed the hosting that was soon to expire... Thus, BackMyModules was born.

Have you noticed how difficult it is to manage add-on modules once installed on Dolibarr? It's easy to install them now, but not to back them up or delete them. However, when you update Dolibarr to a new version, it's not uncommon for an old, even deactivated module to cause problems and render your Dolibarr non-functional or unstable. BackMyModules allows you to back up all your add-on modules (great when you haven't kept a local copy of your module and your Dolistore download link has expired...) in a downloadable Zip archive.

Once your backup is done (and not before, please...), you can delete the inactive modules. The data from these modules is not deleted (Dolibarr "documents" directory).

## User Manual

### Installation and Activation
You can install and activate BackMyModules just like any other Dolibarr module. It will be installed in the add-on modules directory (/custom/).

### Creating the Backup Archive
BackMyModules features are only accessible via the module configuration page or the "BackMyModules" menu in "Administration Tools." To create a "Zip" archive of all modules in /custom/, simply click the designated button:  
![BackMyModules](img/backmymodules001.png?raw=true "BackMyModules")  

### Deleting a Deactivated Module
BackMyModules lists all add-on modules present on your Dolibarr. Only modules that are deactivated can be deleted. The data (tables and files), if not deleted by the module editor when deactivated, is preserved, and you can verify this by reinstalling the modules (via a compliant zip containing the module directory and named module_xxx-1.0.zip, for example).  
![Add-ons modules list](img/backmymodules002.png?raw=true "Add-ons modules list")  

If you click on the module deletion link, confirmation is requested. If you confirm, the module directory is permanently deleted.  
![Confirm](img/backmymodules003.png?raw=true "Confirm")  

## Stay in Touch!
Feel free to share your ideas and comments at [oandrade@aplose.fr](mailto:oandrade@aplose.fr).

## Licenses
Like Dolibarr and all the modules you can obtain on Dolistore, our module is under an open-source license. You can improve the code, make it your own, and share it, free or for a fee, but you must always retain the original license. Remember what open-source software is, and you'll understand why it's necessary to buy (at a reasonable price) modules from developers to allow them to make a living from their work.

### Main Code
GPLv3. See the COPYING file for more information.

### Documentation
All texts and readme are under the GFDL license.