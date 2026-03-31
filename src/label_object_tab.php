<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/label_object_tab.php
 * \ingroup mokodolidymo
 * \brief   Labels tab on product/thirdparty/contact/member cards
 */

// Load Dolibarr environment
$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once __DIR__.'/class/LabelTemplate.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var User $user
 */

$langs->loadLangs(array("mokodolidymo@mokodolidymo"));

$object_type = GETPOST('object_type', 'aZ09');
$fk_object = GETPOSTINT('fk_object');

if (!$user->hasRight('mokodolidymo', 'label', 'read')) {
	accessforbidden();
}

// Load the parent object and display its standard card header + tabs
$source_object = null;
$head = array();

switch ($object_type) {
	case 'product':
		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
		$source_object = new Product($db);
		$source_object->fetch($fk_object);
		$source_object->fetch_optionals();
		$head = product_prepare_head($source_object);
		$picto = $source_object->type ? 'service' : 'product';
		break;

	case 'thirdparty':
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
		$source_object = new Societe($db);
		$source_object->fetch($fk_object);
		$head = societe_prepare_head($source_object);
		$picto = 'company';
		break;

	case 'contact':
		require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/contact.lib.php';
		$source_object = new Contact($db);
		$source_object->fetch($fk_object);
		$head = contact_prepare_head($source_object);
		$picto = 'contact';
		break;

	case 'member':
		require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/member.lib.php';
		$source_object = new Adherent($db);
		$source_object->fetch($fk_object);
		$head = member_prepare_head($source_object);
		$picto = 'member';
		break;

	default:
		accessforbidden('Unknown object type');
}

if (!$source_object || !$source_object->id) {
	accessforbidden('Object not found');
}

// Load label templates for this object type
$templates = array();
$sql = "SELECT rowid, ref, label, label_size, label_width, label_height, description";
$sql .= " FROM ".$db->prefix()."mokodolidymo_label";
$sql .= " WHERE object_type = '".$db->escape($object_type)."'";
$sql .= " AND status = 1";
$sql .= " AND entity IN (".getEntity('mokodolidymo_label').")";
$sql .= " ORDER BY ref ASC";
$resql = $db->query($sql);
if ($resql) {
	while ($obj = $db->fetch_object($resql)) {
		$templates[] = $obj;
	}
}


/*
 * View
 */

$title = $langs->trans("Labels").' - '.(method_exists($source_object, 'getNomUrl') ? strip_tags($source_object->getNomUrl(0)) : $source_object->ref);
llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-mokodolidymo page-label_object_tab');

print dol_get_fiche_head($head, 'mokodolidymo', '', -1, $picto);

// Show object banner
if ($object_type === 'product') {
	$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
	dol_banner_tab($source_object, 'ref', $linkback, 1, 'ref');
} elseif ($object_type === 'thirdparty') {
	$linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
	dol_banner_tab($source_object, 'socid', $linkback, 1, 'rowid', 'nom');
} elseif ($object_type === 'contact') {
	$linkback = '<a href="'.DOL_URL_ROOT.'/contact/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
	dol_banner_tab($source_object, 'id', $linkback, 1, 'rowid', 'nom');
} elseif ($object_type === 'member') {
	$linkback = '<a href="'.DOL_URL_ROOT.'/adherents/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
	dol_banner_tab($source_object, 'id', $linkback, 1, 'rowid', 'ref');
}

print '<div class="underbanner clearboth"></div>';

// Labels section
print '<div class="fichecenter" style="margin-top:16px;">';

if (empty($templates)) {
	print '<div class="opacitymedium" style="padding:20px;">No active label templates for '.dol_escape_htmltag(ucfirst($object_type)).' objects. ';
	if ($user->hasRight('mokodolidymo', 'label', 'create')) {
		print '<a href="'.dol_buildpath('/mokodolidymo/label_card.php', 1).'?action=create">Create one</a>.';
	}
	print '</div>';
} else {
	print '<h3 style="margin-bottom:12px;">'.$langs->trans("PrintLabel").'</h3>';
	print '<p class="opacitymedium">Select a label template to print for this '.dol_escape_htmltag($object_type).':</p>';

	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>Template</td><td>Label Size</td><td>Dimensions</td><td></td>';
	print '</tr>';

	$label_tpl = new LabelTemplate($db);
	foreach ($templates as $tpl) {
		$label_tpl->id = $tpl->rowid;
		$label_tpl->ref = $tpl->ref;
		$label_tpl->status = 1;

		$size_desc = $tpl->label_size;
		if (isset(LabelTemplate::LABEL_SIZES[$tpl->label_size])) {
			$size_desc .= ' - '.LabelTemplate::LABEL_SIZES[$tpl->label_size][2];
		}

		$print_url = dol_buildpath('/mokodolidymo/label_print.php', 1);
		$print_url .= '?id='.$tpl->rowid.'&selected_ids[]='.$fk_object;

		print '<tr class="oddeven">';
		print '<td>'.$label_tpl->getNomUrl(1).' - '.dol_escape_htmltag($tpl->label).'</td>';
		print '<td>'.dol_escape_htmltag($size_desc).'</td>';
		print '<td>'.dol_escape_htmltag($tpl->label_width.'mm x '.$tpl->label_height.'mm').'</td>';
		print '<td class="right">';
		if ($user->hasRight('mokodolidymo', 'label', 'print')) {
			print '<a class="butAction butActionSmall" href="'.$print_url.'">';
			print img_picto('', 'fa-print', 'class="pictofixedwidth"').$langs->trans("PrintLabel");
			print '</a>';
		}
		print '</td>';
		print '</tr>';
	}

	print '</table>';
}

print '</div>';

print dol_get_fiche_end();
llxFooter();
$db->close();
