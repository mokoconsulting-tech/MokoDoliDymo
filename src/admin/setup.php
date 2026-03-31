<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
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
 * \file    mokodolidymo/admin/setup.php
 * \ingroup mokodolidymo
 * \brief   MokoDoliDymo setup page.
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
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/mokodolidymo.lib.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Translations
$langs->loadLangs(array("admin", "mokodolidymo@mokodolidymo"));

// Initialize a technical object to manage hooks of page.
$hookmanager->initHooks(array('mokodolidymosetup', 'globalsetup'));

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');

$error = 0;
$setupnotempty = 0;

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Set this to 1 to use the factory to manage constants.
$useFormSetup = 1;

if (!class_exists('FormSetup')) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
}
$formSetup = new FormSetup($db);


// ── DYMO Connect Settings ─────────────────────────────────

$formSetup->newItem('MOKODOLIDYMO_SECTION_DYMO')->setAsTitle();

// DYMO Connect service port
$item = $formSetup->newItem('MOKODOLIDYMO_DYMO_PORT');
$item->defaultFieldValue = '41951';
$item->fieldAttr['placeholder'] = '41951';
$item->helpText = 'Port number for the DYMO Connect local service (default: 41951)';
$item->cssClass = 'maxwidth150';

// Default printer name
$item = $formSetup->newItem('MOKODOLIDYMO_DEFAULT_PRINTER');
$item->defaultFieldValue = '';
$item->fieldAttr['placeholder'] = 'DYMO LabelWriter 450';
$item->helpText = 'Name of the default DYMO printer (leave empty to prompt each time)';
$item->cssClass = 'minwidth300';

// ── Label Defaults ────────────────────────────────────────

$formSetup->newItem('MOKODOLIDYMO_SECTION_DEFAULTS')->setAsTitle();

// Default label size for new templates
$label_sizes = array(
	'30252' => '30252 - Address (88.9x28.6mm)',
	'30256' => '30256 - Shipping (101.6x58.7mm)',
	'30334' => '30334 - Multi-purpose (57.2x25.4mm)',
	'30327' => '30327 - File Folder (87.3x14.3mm)',
	'30857' => '30857 - Name Badge (101.6x57.2mm)',
	'30336' => '30336 - Small Multi-purpose (25.4x25.4mm)',
	'99012' => '99012 - Large Address (89x36mm)',
	'custom' => 'Custom Size',
);
$item = $formSetup->newItem('MOKODOLIDYMO_DEFAULT_LABEL_SIZE');
$item->setAsSelect($label_sizes);
$item->helpText = 'Default label size when creating new templates';

// Default object type for new templates
$item = $formSetup->newItem('MOKODOLIDYMO_DEFAULT_OBJECT_TYPE');
$item->setAsSelect(array(
	'product' => 'Product',
	'thirdparty' => 'Third Party',
	'contact' => 'Contact',
	'shipment' => 'Shipment',
	'member' => 'Member',
	'warehouse' => 'Warehouse / Stock',
));
$item->helpText = 'Default data source when creating new templates';

// ── Printing Options ──────────────────────────────────────

$formSetup->newItem('MOKODOLIDYMO_SECTION_PRINT')->setAsTitle();

// Auto-print (skip preview)
$formSetup->newItem('MOKODOLIDYMO_AUTO_PRINT')->setAsYesNo();

// Show print button on product cards
$formSetup->newItem('MOKODOLIDYMO_SHOW_ON_PRODUCT')->setAsYesNo();

// Show print button on thirdparty cards
$formSetup->newItem('MOKODOLIDYMO_SHOW_ON_THIRDPARTY')->setAsYesNo();

// Show print button on contact cards
$formSetup->newItem('MOKODOLIDYMO_SHOW_ON_CONTACT')->setAsYesNo();

$setupnotempty += count($formSetup->items);

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

$action = 'edit';


/*
 * View
 */

$form = new Form($db);

$help_url = '';
$title = "MokoDoliDymoSetup";

llxHeader('', $langs->trans($title), $help_url, '', 0, 0, '', '', '', 'mod-mokodolidymo page-admin');

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

// Configuration header
$head = mokodolidymoAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($title), -1, "fa-print");

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans("MokoDoliDymoSetupPage").'</span><br><br>';

if (!empty($formSetup->items)) {
	print $formSetup->generateOutput(true);
	print '<br>';
}

if (empty($setupnotempty)) {
	print '<br>'.$langs->trans("NothingToSetup");
}

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
