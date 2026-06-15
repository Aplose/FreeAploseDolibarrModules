<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    doliprospectform/lib/doliprospectform_recherche_entreprises.lib.php
 * \ingroup doliprospectform
 * \brief   French public company search via API Recherche d'entreprises (api.gouv.fr)
 */

/**
 * Call API Recherche d'entreprises and return normalized rows for the UI
 *
 * @param string $query Search text (name, SIREN, SIRET, etc.)
 * @param int    $perPage Max results (capped)
 * @return array<int,array<string,mixed>>|array{error:string,http_code?:int}
 */
function doliprospectform_recherche_entreprises_search($query, $perPage = 15)
{
	$query = trim((string) $query);
	if ($query === '') {
		return array();
	}

	$perPage = max(1, min(25, (int) $perPage));

	require_once DOL_DOCUMENT_ROOT.'/core/lib/geturl.lib.php';

	$url = 'https://recherche-entreprises.api.gouv.fr/search?'.http_build_query(array(
		'q' => $query,
		'per_page' => $perPage,
	));

	$headers = array(
		'User-Agent: DoliProspectForm/1.0 (+https://www.dolibarr.org; contact: public-form)',
		'Accept: application/json',
	);

	$res = getURLContent($url, 'GET', '', 1, $headers, array('https'), 0, -1, 0, 0, array(CURLOPT_USERAGENT => 'DoliProspectForm/1.0 (+https://www.dolibarr.org)'));

	$http = isset($res['http_code']) ? (int) $res['http_code'] : 0;
	if ($http !== 200 || empty($res['content'])) {
		dol_syslog('doliprospectform_recherche_entreprises_search HTTP '.$http.' url='.$url, LOG_WARNING);
		return array('error' => 'http', 'http_code' => $http);
	}

	$data = json_decode($res['content'], true);
	if (!is_array($data) || empty($data['results']) || !is_array($data['results'])) {
		return array();
	}

	$out = array();
	foreach ($data['results'] as $r) {
		if (!is_array($r)) {
			continue;
		}
		$siege = (isset($r['siege']) && is_array($r['siege'])) ? $r['siege'] : array();
		$out[] = array(
			'siren' => isset($r['siren']) ? (string) $r['siren'] : '',
			'siret' => isset($siege['siret']) ? (string) $siege['siret'] : '',
			'nom_raison_sociale' => isset($r['nom_raison_sociale']) ? (string) $r['nom_raison_sociale'] : (isset($r['nom_complet']) ? (string) $r['nom_complet'] : ''),
			'adresse_complete' => isset($siege['adresse']) ? (string) $siege['adresse'] : '',
			'code_postal' => isset($siege['code_postal']) ? (string) $siege['code_postal'] : '',
			'commune' => isset($siege['libelle_commune']) ? (string) $siege['libelle_commune'] : '',
			'activite_principale' => isset($r['activite_principale']) ? (string) $r['activite_principale'] : '',
			'etat_administratif' => isset($r['etat_administratif']) ? (string) $r['etat_administratif'] : '',
		);
	}

	return $out;
}
