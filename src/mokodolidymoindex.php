<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 *	\file       mokodolidymo/mokodolidymoindex.php
 *	\ingroup    mokodolidymo
 *	\brief      Home page of MokoDoliDymo module
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

if (!isModEnabled('mokodolidymo')) {
	accessforbidden('Module not enabled');
}
if (!$user->hasRight('mokodolidymo', 'label', 'read')) {
	accessforbidden();
}

/*
 * View
 */

llxHeader("", $langs->trans("MokoDoliDymoArea"), '', '', 0, 0, '', '', '', 'mod-mokodolidymo page-index');

print load_fiche_titre($langs->trans("MokoDoliDymoArea"), '', 'fa-print');

print '<div class="fichecenter"><div class="fichethirdleft">';

// Quick actions
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><th colspan="2">Quick Actions</th></tr>';

if ($user->hasRight('mokodolidymo', 'label', 'create')) {
	print '<tr class="oddeven"><td colspan="2">';
	print '<a class="butAction" href="'.dol_buildpath('/mokodolidymo/label_card.php', 1).'?action=create">';
	print img_picto('', 'fa-plus', 'class="pictofixedwidth"').' '.$langs->trans("NewLabelTemplate");
	print '</a></td></tr>';
}

print '<tr class="oddeven"><td colspan="2">';
print '<a class="butAction" href="'.dol_buildpath('/mokodolidymo/label_list.php', 1).'">';
print img_picto('', 'fa-list', 'class="pictofixedwidth"').' '.$langs->trans("LabelTemplates");
print '</a></td></tr>';

print '</table></div>';

print '</div><div class="fichetwothirdright">';

// Recent label templates
$sql = "SELECT rowid, ref, label, label_size, status, date_creation";
$sql .= " FROM ".$db->prefix()."mokodolidymo_label";
$sql .= " WHERE entity = ".((int) $conf->entity);
$sql .= " ORDER BY tms DESC LIMIT 10";

$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<th colspan="4">Recent Label Templates';
	if ($num > 0) {
		print '<span class="badge marginleftonlyshort">'.$num.'</span>';
	}
	print '</th></tr>';

	if ($num > 0) {
		$label_obj = new LabelTemplate($db);
		while ($obj = $db->fetch_object($resql)) {
			$label_obj->id = $obj->rowid;
			$label_obj->ref = $obj->ref;
			$label_obj->status = $obj->status;

			print '<tr class="oddeven">';
			print '<td>'.$label_obj->getNomUrl(1).'</td>';
			print '<td>'.dol_escape_htmltag($obj->label).'</td>';
			print '<td>'.dol_escape_htmltag($obj->label_size).'</td>';
			print '<td>'.$label_obj->getLibStatut(0).'</td>';
			print '</tr>';
		}
	} else {
		print '<tr class="oddeven"><td colspan="4" class="opacitymedium">No label templates yet. Create your first one!</td></tr>';
	}

	print '</table></div>';
	$db->free($resql);
}

print '</div></div>';

llxFooter();
$db->close();
