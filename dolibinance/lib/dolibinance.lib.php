<?php
/* Copyright (C) 2023 Olivier ANDRADE SANCHEZ <oandrade@aplose.fr>
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
 * \file    dolibinance/lib/dolibinance.lib.php
 * \ingroup dolibinance
 * \brief   Library files with common functions for DoliBTC
 */

require_once __DIR__ . '/../includes/autoload.php';

/**
 * Prepare admin pages header
 *
 * @return array
 */
function dolibinanceAdminPrepareHead()
{
	global $langs, $conf;

	// global $db;
	// $extrafields = new ExtraFields($db);
	// $extrafields->fetch_name_optionals_label('myobject');

	$langs->load("dolibinance@dolibinance");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/dolibinance/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/dolibinance/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$nbExtrafields = is_countable($extrafields->attributes['myobject']['label']) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/dolibinance/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@dolibinance:/dolibinance/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@dolibinance:/dolibinance/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'dolibinance@dolibinance');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'dolibinance@dolibinance', 'remove');

	return $head;
}

function getLastBtcPriceForCurrency($currency = 'EUR') {
    $symbol = 'BTC'.$currency;
    return getLastAveragePriceForSymbol($symbol);
}
function getLastAveragePriceForSymbol($symbol = 'BTCEUR') {
    $file = 'https://api.binance.com/api/v3/ticker/24hr?symbol='.$symbol;
    $json = file_get_contents($file);
    $jsonObject = json_decode($json);
    $averagePrice = $jsonObject->weightedAvgPrice;
    return $averagePrice;
}


function getBinanceAccount(){
    global $langs, $conf;
    $key = $conf->global->DOLIBINANCE_API_KEY;
    $secret = $conf->global->DOLIBINANCE_API_SECRET;
    $client = new \Binance\Spot(['key' => $key, 'secret' => $secret]);
    return $client->account();
}
function getUserAsset(){
    global $langs, $conf;
    $key = $conf->global->DOLIBINANCE_API_KEY;
    $secret = $conf->global->DOLIBINANCE_API_SECRET;
    $client = new \Binance\Spot(['key' => $key, 'secret' => $secret]);
    return $client->userAsset(['needBtcValuation'=>true]);
}
function getDepostitHistoryWithSource(){
    global $langs, $conf;
    $key = $conf->global->DOLIBINANCE_API_KEY;
    $secret = $conf->global->DOLIBINANCE_API_SECRET;
    $client = new \Binance\Spot(['key' => $key, 'secret' => $secret]);
    return $client->depositHistory(['includeSource' => true,'status'=> 1 ]);
}

