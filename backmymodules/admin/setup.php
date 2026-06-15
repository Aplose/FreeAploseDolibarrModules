<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    backmymodules/admin/setup.php
 * \ingroup backmymodules
 * \brief   BackMyModules setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT."/core/lib/files.lib.php";
require_once '../lib/backmymodules.lib.php';
//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("backmymodules@backmymodules"));

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('backmymodulessetup', 'globalsetup'));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');	// Used by actions_setmoduleoptions.inc.php

$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$type = 'myobject';


$error = 0;
$setupnotempty = 0;

// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 1;

if (!class_exists('FormSetup')) {
	// For retrocompatibility Dolibarr < 16.0
	if (floatval(DOL_VERSION) < 16.0 && !class_exists('FormSetup')) {
		require_once __DIR__.'/../backport/v16/core/class/html.formsetup.class.php';
	} else {
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
	}
}

$formSetup = new FormSetup($db);


$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);


/*
 * Actions
 */

// For retrocompatibility Dolibarr < 15.0
if ( versioncompare(explode('.', DOL_VERSION), array(15)) < 0 && $action == 'update' && !empty($user->admin)) {
	$formSetup->saveConfFromPost();
}

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

$outputfile = null;
$filename = null;
$inputdir = DOL_DOCUMENT_ROOT.'/custom';
if ($action == 'createModulesBackupFile') {
    //ici faire le zip et créer le lien de téléchargement
    $mode = 'zip';
    $filename = 'backmymodules.'.$mode;
    $outputfile = DOL_DATA_ROOT.'/backmymodules/' . $filename;
    dol_compress_dir($inputdir, $outputfile, $mode, '', 'backmymodules');
}   




/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "BackMyModulesSetup";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = backmymodulesAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, "backmymodules@backmymodules");

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans("BackMyModulesSetupPage").'</span><br><br>';
if ($action == 'deleteModule'){
    $moduleName = GETPOST('moduleName', 'aZ09');
    print '<h2>'.$langs->trans('BackMyModulesAboutToDeleteMessage').$moduleName.'</h2>';
    print '<p>'.$langs->trans('BackMyModulesAboutToDeleteMessage2').'</p>';
    print '<a class="button" href="'.$_SERVER["PHP_SELF"].'?action=confirmDeleteModule&moduleName='.$moduleName.'&token='. newToken().'">'.$langs->trans('BackMyModulesConfirmButton').'</a>';
    print '<a class="button" href="'.$_SERVER["PHP_SELF"].'">'.$langs->trans('BackMyModulesCancelButton').'</a>';
}else if ($action == 'confirmDeleteModule'){
    $moduleName = GETPOST('moduleName', 'aZ09');
    //dernière vérif...
    $constName = 'MAIN_MODULE_'.strtoupper($moduleName); 
    if(!empty($moduleName)&& is_dir($inputdir.'/'.$moduleName)&&(empty($conf->global->$constName)||!$conf->global->$constName)){
        //bon bin quand faut y aller...
        dol_delete_dir_recursive($inputdir.'/'.$moduleName);
    }
}
 
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="createModulesBackupFile">';
print '<input class="button" type="submit" value="'.$langs->trans('BackMyModulesCreateZipButton').'">';
print '</form><br>';

if($outputfile!=null){
    print '<div>';
    print ' <a href="'.DOL_MAIN_URL_ROOT.'/document.php?modulepart=backmymodules&file='.$filename.'">'.$langs->trans('BackMyModulesDownloadZipLink').'</a>';
    print '</div>';
}

print '<h2>'.$langs->trans('BackMyModulesDeleteArrayTitle').'</h2>';
print '<p>'.$langs->trans('BackMyModulesDeleteArrayWarningMessage').'</p>';
print '<table>';
print '<tr>';
print '<th>'.$langs->trans('BackMyModulesDeleteArrayTh1').'</th>';
print '<th>'.$langs->trans('BackMyModulesDeleteArrayTh2').'</th>';
print '<th>'.$langs->trans('BackMyModulesDeleteArrayTh3').'</th>';
print '</tr>';
//on récupère la liste des répertoires de /custom/
$filesArray = scandir($inputdir);
$filesArrayInactive = array();
foreach ($filesArray as $filename) {
    if(!empty($filename)&&$filename!='.'&&$filename!='..'&&is_dir($inputdir.'/'.$filename)){
        print '<tr>';
        print '<td>'.$filename.'</td>';
        $constName = 'MAIN_MODULE_'.strtoupper($filename);        
        if (!empty($conf->global->$constName)&&$conf->global->$constName){
            print '<td>'.$langs->trans('BackMyModulesDeleteArrayTd1').'</td>';
            print '<td>'.$langs->trans('BackMyModulesDeleteArrayTd2').'</td>';
        }else{
            print '<td>'.$langs->trans('BackMyModulesDeleteArrayTd3').'</td>';
            print '<td><a href="'.$_SERVER["PHP_SELF"].'?action=deleteModule&moduleName='.$filename.'&token='. newToken().'">'.$langs->trans('BackMyModulesDeleteArrayTd4').'</a></td>';
        }
        print '</tr>';
    }
}

print '</table>';


// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
