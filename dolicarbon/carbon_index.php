<?php
/* Copyright (C) 2026 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr> */

/**
 * \file        carbon_index.php
 * \ingroup     dolicarbon
 * \brief       DoliCarbon dashboard
 */

$res = 0;
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once __DIR__.'/class/dolicarbonbilan.class.php';
require_once __DIR__.'/lib/dolicarbon.lib.php';

$langs->loadLangs(array('dolicarbon@dolicarbon'));

if (!isModEnabled('dolicarbon') || !$user->hasRight('dolicarbon', 'read')) {
	accessforbidden();
}

$form = new Form($db);

$bilanid = GETPOSTINT('bilanid');
if ($bilanid > 0) {
	dolicarbon_set_session_bilan_id($bilanid);
} elseif (dolicarbon_get_session_bilan_id() > 0) {
	$bilanid = dolicarbon_get_session_bilan_id();
}

$bilan = new DoliCarbonBilan($db);
if ($bilanid <= 0 || $bilan->fetch($bilanid) <= 0) {
	$sql = "SELECT rowid FROM ".$db->prefix()."dolicarbon_bilan";
	$sql .= " WHERE entity IN (".getEntity('societe').") ORDER BY year DESC, rowid DESC LIMIT 1";
	$resql = $db->query($sql);
	if ($resql && $db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$bilanid = (int) $obj->rowid;
		dolicarbon_set_session_bilan_id($bilanid);
		$bilan->fetch($bilanid);
	}
}

$scopes = array(1 => 0.0, 2 => 0.0, 3 => 0.0);
$by_month = array();
$by_cat = array();
$total = 0.0;

if ($bilanid > 0) {
	$sql = "SELECT scope, SUM(tco2e_computed) as s FROM ".$db->prefix()."dolicarbon_entry";
	$sql .= " WHERE fk_bilan = ".$bilanid." GROUP BY scope";
	$resql = $db->query($sql);
	if ($resql) {
		while ($r = $db->fetch_object($resql)) {
			$scopes[(int) $r->scope] = (float) $r->s;
			$total += (float) $r->s;
		}
	}

	$sql = "SELECT DATE_FORMAT(date_creation, '%Y-%m') as ym, SUM(tco2e_computed) as s";
	$sql .= " FROM ".$db->prefix()."dolicarbon_entry WHERE fk_bilan = ".$bilanid;
	$sql .= " GROUP BY ym ORDER BY ym";
	$resql = $db->query($sql);
	if ($resql) {
		while ($r = $db->fetch_object($resql)) {
			$by_month[$r->ym] = (float) $r->s;
		}
	}

	$sql = "SELECT category, SUM(tco2e_computed) as s FROM ".$db->prefix()."dolicarbon_entry";
	$sql .= " WHERE fk_bilan = ".$bilanid." GROUP BY category ORDER BY s DESC LIMIT 5";
	$resql = $db->query($sql);
	if ($resql) {
		while ($r = $db->fetch_object($resql)) {
			$by_cat[$r->category] = (float) $r->s;
		}
	}
}

$prev_total = null;
if ($bilanid > 0 && !empty($bilan->year)) {
	$py = ((int) $bilan->year) - 1;
	$sql = "SELECT rowid, total_tco2e FROM ".$db->prefix()."dolicarbon_bilan";
	$sql .= " WHERE entity IN (".getEntity('societe').") AND year = ".$py." AND status = ".DoliCarbonBilan::STATUS_VALIDATED;
	$sql .= " ORDER BY rowid DESC LIMIT 1";
	$resql = $db->query($sql);
	if ($resql && $db->num_rows($resql)) {
		$pr = $db->fetch_object($resql);
		$prev_total = (float) $pr->total_tco2e;
	}
}

$catmap = dolicarbon_get_category_map();

llxHeader('', $langs->trans('DoliCarbonArea'), '', '', 0, 0, '', '', '', 'mod-dolicarbon page-dashboard');

print load_fiche_titre($langs->trans('DoliCarbonArea'), '', 'fa-leaf');

print '<form method="GET" action="'.$_SERVER['PHP_SELF'].'" class="inline-block">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print $langs->trans('Bilan').': ';
$sqlb = "SELECT rowid, ref, year FROM ".$db->prefix()."dolicarbon_bilan WHERE entity IN (".getEntity('societe').") ORDER BY year DESC, ref DESC";
$resb = $db->query($sqlb);
$bilopts = array();
if ($resb) {
	while ($b = $db->fetch_object($resb)) {
		$bilopts[$b->rowid] = $b->ref.' ('.$b->year.')';
	}
}
print $form->selectarray('bilanid', $bilopts, $bilanid, 0, 0, 0, '', 0, 0, '', '', 1);
print ' <input type="submit" class="button" value="'.$langs->trans('View').'">';
print '</form>';

print '<div class="fichecenter" style="margin-top:20px">';
print '<div class="fichethirdleft">';

print '<div class="info_box boxflexitem boxflexitemgrow">';
print '<div class="info_box_title">'.$langs->trans('TotalTco2e').'</div>';
print '<div class="info_box_content" style="font-size:1.8em;font-weight:bold">'.price2num($total, 'MT', 3).'</div>';
print '</div>';

print '</div><div class="fichethirdleft">';
foreach (array(1 => 'Scope1', 2 => 'Scope2', 3 => 'Scope3') as $sc => $lk) {
	print '<div class="info_box boxflexitem boxflexitemgrow">';
	print '<div class="info_box_title">'.$langs->trans($lk).'</div>';
	print '<div class="info_box_content" style="font-size:1.3em">'.price2num($scopes[$sc], 'MT', 3).'</div>';
	print '</div>';
}
print '</div>';

if ($bilanid > 0 && $bilan->target_tco2e > 0) {
	$pct = min(100, ($total / (float) $bilan->target_tco2e) * 100);
	print '<div class="fichehalfleft">';
	print '<div class="info_box">';
	print '<div class="info_box_title">'.$langs->trans('TargetProgress').'</div>';
	print '<div class="info_box_content">';
	print '<div style="background:#eee;border-radius:4px;height:24px;width:100%;max-width:400px"><div style="background:#2e7d32;height:100%;width:'.price2num($pct, 'MT', 2).'%;border-radius:4px"></div></div>';
	print '<span class="opacitymedium">'.$langs->trans('TotalTco2e').': '.price2num($total, 'MT', 3).' / '.$langs->trans('TargetTco2e').': '.price2num($bilan->target_tco2e, 'MT', 3).'</span>';
	print '</div></div></div>';
}

if ($prev_total !== null && $prev_total > 0) {
	$delta = $total - $prev_total;
	print '<div class="fichehalfleft"><div class="info_box"><div class="info_box_title">'.$langs->trans('ComparePreviousYear').'</div>';
	print '<div class="info_box_content">N-1: '.price2num($prev_total, 'MT', 3).' &rarr; Δ '.price2num($delta, 'MT', 3).'</div></div></div>';
}

print '</div>';

print '<div class="fichecenter"><div class="fichehalfleft">';
print '<h3>'.$langs->trans('ChartByScope').'</h3>';
print '<canvas id="chart_scope" style="max-width:480px;max-height:320px"></canvas>';
print '</div><div class="fichehalfright">';
print '<h3>'.$langs->trans('ChartByMonth').'</h3>';
print '<canvas id="chart_month" style="max-width:520px;max-height:320px"></canvas>';
print '</div></div>';

print '<h3>'.$langs->trans('TopEmittingCategories').'</h3>';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td>'.$langs->trans('Category').'</td><td class="right">'.$langs->trans('TotalTco2e').'</td></tr>';
foreach ($by_cat as $ck => $cv) {
	$lab = $ck;
	foreach ($catmap as $sc => $arr) {
		if (isset($arr[$ck])) {
			$lab = $langs->trans($arr[$ck]);
			break;
		}
	}
	print '<tr class="oddeven"><td>'.dol_escape_htmltag($lab).'</td><td class="right">'.price2num($cv, 'MT', 3).'</td></tr>';
}
if (empty($by_cat)) {
	print '<tr class="oddeven"><td colspan="2"><span class="opacitymedium">'.$langs->trans('NoRecordFound').'</span></td></tr>';
}
print '</table>';

print '<div class="tabsAction" style="margin-top:20px">';
print dolGetButtonTitle($langs->trans('NewEntry'), '', 'fa fa-plus', dol_buildpath('/dolicarbon/carbon_entry_card.php?action=create&fk_bilan='.$bilanid, 1), '', $user->hasRight('dolicarbon', 'write') && $bilanid > 0 ? 1 : 0);
print dolGetButtonTitle($langs->trans('ImportFromDolibarr'), '', 'fa fa-download', dol_buildpath('/dolicarbon/carbon_import.php?fk_bilan='.$bilanid, 1), '', $user->hasRight('dolicarbon', 'write') && $bilanid > 0 ? 1 : 0);
print dolGetButtonTitle($langs->trans('GenerateReport'), '', 'fa fa-file-pdf', dol_buildpath('/dolicarbon/carbon_report.php?id='.$bilanid, 1), '', $bilanid > 0 ? 1 : 0);
print '</div>';

$scope_json = json_encode(array_values($scopes));
$scope_lbl = json_encode(array($langs->trans('Scope1'), $langs->trans('Scope2'), $langs->trans('Scope3')));
$month_lbl = json_encode(array_keys($by_month));
$month_val = json_encode(array_values($by_month));

print '<script nonce="'.getNonce().'">';
print '(function() {';
print 'if (typeof Chart === "undefined") return;';
print 'var ctx1 = document.getElementById("chart_scope");';
print 'if (ctx1) { new Chart(ctx1, { type: "pie", data: { labels: '.$scope_lbl.', datasets: [{ data: '.$scope_json.', backgroundColor: ["#c62828","#1565c0","#2e7d32"] }] }, options: { plugins: { legend: { position: "bottom" } } } }); }';
print 'var ctx2 = document.getElementById("chart_month");';
print 'if (ctx2 && '.$month_lbl.'.length) { new Chart(ctx2, { type: "bar", data: { labels: '.$month_lbl.', datasets: [{ label: "tCO2e", data: '.$month_val.', backgroundColor: "#3949ab" }] }, options: { scales: { y: { beginAtZero: true } } } }); }';
print '})();';
print '</script>';

llxFooter();
$db->close();
