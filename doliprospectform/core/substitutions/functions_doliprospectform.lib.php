<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    doliprospectform/core/substitutions/functions_doliprospectform.lib.php
 * \ingroup doliprospectform
 * \brief   Email / ODT substitution keys for DoliProspectForm
 */

/**
 * Complete substitution array (Dolibarr calls {module}_completesubstitutionarray)
 *
 * @param array<string,string|float|null> $substitutionarray Substitution array
 * @param Translate                        $outputlangs      Lang object
 * @param CommonObject|null                  $object           Related object (optional)
 * @param mixed|null                         $parameters       Extra parameters
 * @return void
 */
function doliprospectform_completesubstitutionarray(&$substitutionarray, $outputlangs, $object, $parameters = null)
{
	global $conf, $db, $user;

	if (!isModEnabled('doliprospectform')) {
		return;
	}

	if (!function_exists('doliprospectform_publicform_url_individual')) {
		dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');
	}

	if (is_array($parameters) && !empty($parameters['doliprospectform_submission']['tags']) && is_array($parameters['doliprospectform_submission']['tags'])) {
		foreach ($parameters['doliprospectform_submission']['tags'] as $k => $v) {
			$substitutionarray[$k] = (string) $v;
		}
	}

	$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_PARTICULIER__'] = '';
	$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_INDIVIDUAL__'] = '';
	$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_PROFESSIONNEL__'] = '';
	$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_PROFESSIONAL__'] = '';
	$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__'] = '';

	$socidForUrl = 0;
	if (is_object($object)) {
		$element = isset($object->element) ? (string) $object->element : '';
		if ($element === 'societe' && !empty($object->id)) {
			$socidForUrl = (int) $object->id;
		} elseif (!empty($object->socid)) {
			$socidForUrl = (int) $object->socid;
		}
	}

	$commercialId = 0;
	if (is_array($parameters) && isset($parameters['doliprospectform_submission']['commercial_for_links'])) {
		$commercialId = (int) $parameters['doliprospectform_submission']['commercial_for_links'];
	} elseif (is_object($user) && !empty($user->id) && (int) $user->id > 0) {
		$commercialId = (int) $user->id;
	}

	$enableInd = doliprospectform_publicform_is_public_individual_enabled();
	$enablePro = doliprospectform_publicform_is_public_professional_enabled();
	if ($enableInd || $enablePro) {
		$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_HUB__'] = doliprospectform_publicform_url_hub($db, (int) $conf->entity, $commercialId, $socidForUrl);
	}

	if ($commercialId <= 0) {
		return;
	}

	if ($enableInd) {
		$urlIndividual = doliprospectform_publicform_url_individual($db, (int) $conf->entity, $commercialId, $socidForUrl);
		$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_PARTICULIER__'] = $urlIndividual;
		$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_INDIVIDUAL__'] = $urlIndividual;
	}

	if ($enablePro) {
		$urlProfessional = doliprospectform_publicform_url_professional($db, (int) $conf->entity, $commercialId, $socidForUrl);
		$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_PROFESSIONNEL__'] = $urlProfessional;
		$substitutionarray['__DOLIPROSPECTFORM_PUBLIC_LINK_PROFESSIONAL__'] = $urlProfessional;
	}
}
