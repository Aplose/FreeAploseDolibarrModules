<?php
/* Copyright (C) 2025		Florian TOCCO <ftocco@aplose.fr>
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
 * \file    dolilinks/lib/dolilinks.lib.php
 * \ingroup dolilinks
 * \brief   Library files with common functions for DoliLinks
 */

/**
 * Prepare admin pages header
 *
 * @return array<array{string,string,string}>
 */
function dolilinksAdminPrepareHead()
{
	global $langs, $conf;

	// global $db;
	// $extrafields = new ExtraFields($db);
	// $extrafields->fetch_name_optionals_label('myobject');

	$langs->load("dolilinks@dolilinks");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/dolilinks/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/dolilinks/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$nbExtrafields = is_countable($extrafields->attributes['myobject']['label']) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/dolilinks/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@dolilinks:/dolilinks/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@dolilinks:/dolilinks/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'dolilinks@dolilinks');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'dolilinks@dolilinks', 'remove');

	return $head;
}



function displayParentsAndChilds(int $socId): void
{
	global $langs;
	dol_include_once('custom/dolilinks/class/SocieteLink.class.php');
	$societeLink = new SocieteLink($GLOBALS['db']);

	// Nombre d'elements a afficher (parents et/ou enfants)
	$maxParent = GETPOSTISSET('maxParent') ? GETPOSTINT('maxParent') : 5;
	$maxChild = GETPOSTISSET('maxChild') ? GETPOSTINT('maxChild') : 5;

	// Récupérer les parents et les enfants
	$parents = $societeLink->getParents($socId, $maxParent);
	$childs = $societeLink->getChilds($socId, $maxChild);


	// Afficher les parents et les enfants
?>

	<!-- Parents -->
	<tr>
		<td style="width: min-content;">
			<div>

				<?php echo $langs->trans("ParentsSocieties"); ?>
				<a style="margin-left:15px" href="<?php echo dol_buildpath('/dolilinks/links.php?id=' . $socId, 1); ?>">
					<?php echo img_picto($langs->trans("Add"), 'add', '', false, 0); ?>
				</a>
			</div>
		</td>
		<td>
			<ul style="list-style-type: none; padding-left: 0;">
				<?php foreach ($parents as $parent) { ?>
					<li style=""><?php echo $parent->getNomUrl(1); ?></li>
				<?php }
				if (empty($parents)) { ?>
					<li style=""><?php echo $langs->trans("NoParentSocieties"); ?></li>
				<?php } ?>
			</ul>
			<?php if ($maxParent < $societeLink->getParentsCount($socId)) { ?>
				<a href="<?php print dol_buildpath('/societe/card.php?maxParent=' . ($maxParent + 5) . '&socid=' . $socId, 1); ?>" style="text-decoration: underline;margin-right: 20px;">
					<?php print $langs->trans('ShowMore'); ?>
				</a>
			<?php } ?>
			<a href="<?php print dol_buildpath('/dolilinks/diagram.php?socid=' . $socId, 1); ?>" style="text-decoration: underline;">
				<?php print $langs->trans('ShowDiagram'); ?>
			</a>
		</td>
	</tr>


	<!-- Enfants -->
	<tr>
		<td>
			<?php echo $langs->trans("ChildsSocieties"); ?>
			<a style="margin-left:15px" href="<?php echo dol_buildpath('/dolilinks/links.php?id=' . $socId, 1); ?>">
				<?php echo img_picto($langs->trans("Add"), 'add', '', false, 0); ?>
			</a>
		</td>
		<td>
			<ul style="list-style-type: none; padding-left: 0;">
				<?php foreach ($childs as $child) { ?>
					<li style=""><?php echo $child->getNomUrl(1); ?></li>
				<?php }
				if (empty($childs)) { ?>
					<li style=""><?php echo $langs->trans("NoChildSocieties"); ?></li>
				<?php } ?>
			</ul>
			<?php if ($maxChild < $societeLink->getChildsCount($socId)) { ?>
				<a href="<?php print dol_buildpath('/societe/card.php?maxChild=' . ($maxChild + 5) . '&socid=' . $socId, 1); ?>" style="text-decoration: underline;margin-right: 20px;">
					<?php print $langs->trans('ShowMore'); ?>
				</a>
			<?php } ?>
			<a href="<?php print dol_buildpath('/dolilinks/diagram.php?socid=' . $socId, 1); ?>" style="text-decoration: underline;">
				<?php print $langs->trans('ShowDiagram'); ?>
			</a>
		</td>
	</tr>
<?php
}
