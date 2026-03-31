<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/label_designer.php
 * \ingroup mokodolidymo
 * \brief   Visual label template designer
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
require_once __DIR__.'/lib/mokodolidymo.lib.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var User $user
 */

$langs->loadLangs(array("mokodolidymo@mokodolidymo"));

$id = GETPOSTINT('id');

if (!$user->hasRight('mokodolidymo', 'label', 'write')) {
	accessforbidden();
}

$object = new LabelTemplate($db);
$object->fetch($id);
if (!$object->id) {
	accessforbidden('Label template not found');
}

$layout = $object->getLayout();
$bindable_fields = $object->getBindableFields();

// Pass data to JavaScript as JSON — all values are from controlled DB sources
$js_data = array(
	'id' => $object->id,
	'token' => newToken(),
	'saveUrl' => dol_buildpath('/mokodolidymo/label_card.php', 1),
	'labelWidth' => (float) $object->label_width,
	'labelHeight' => (float) $object->label_height,
	'unit' => $object->unit,
	'layout' => $layout,
	'bindableFields' => $bindable_fields,
	'objectType' => $object->object_type,
);


/*
 * View
 */

$title = 'Label Designer - '.$object->ref;
llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-mokodolidymo page-label_designer');

$head = mokodolidymoLabelPrepareHead($object);
print dol_get_fiche_head($head, 'designer', $langs->trans("LabelTemplate"), -1, 'fa-print');

$linkback = '<a href="'.dol_buildpath('/mokodolidymo/label_list.php', 1).'">'.$langs->trans("BackToList").'</a>';
dol_banner_tab($object, 'id', $linkback, 1, 'rowid', 'ref');

print '<div class="underbanner clearboth"></div>';
?>
<style>
.mdd-designer-wrap{display:flex;gap:12px;margin-top:12px}
.mdd-toolbar{background:#f8f8f8;border:1px solid #ccc;border-radius:4px;padding:8px;display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:8px}
.mdd-toolbar button{padding:4px 10px;border:1px solid #999;background:#fff;border-radius:3px;cursor:pointer;font-size:13px}
.mdd-toolbar button:hover{background:#e8e8e8}
.mdd-toolbar .mdd-sep{width:1px;height:24px;background:#ccc;margin:0 4px}
.mdd-canvas-wrap{flex:1;min-width:0}
.mdd-canvas{position:relative;background:#fff;border:2px solid #333;box-shadow:2px 2px 8px rgba(0,0,0,.15);overflow:hidden;margin:0 auto}
.mdd-elem{position:absolute;border:1px dashed transparent;cursor:move;box-sizing:border-box;user-select:none;min-width:10px;min-height:8px}
.mdd-elem:hover{border-color:#4a90d9}
.mdd-elem.mdd-selected{border-color:#06c;border-style:solid;z-index:10}
.mdd-resize{position:absolute;width:8px;height:8px;background:#06c;border:1px solid #fff;right:-4px;bottom:-4px;cursor:nwse-resize;display:none}
.mdd-elem.mdd-selected .mdd-resize{display:block}
.mdd-elem-text{overflow:hidden;white-space:nowrap;text-overflow:ellipsis;padding:1px 2px;line-height:1.2}
.mdd-elem-barcode{display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:9px;color:#333;background:repeating-linear-gradient(90deg,#000 0,#000 2px,#fff 2px,#fff 4px);background-size:100% 70%;background-repeat:no-repeat;background-position:center top}
.mdd-elem-barcode span{margin-top:auto;background:#fff;padding:0 2px}
.mdd-elem-qrcode{display:flex;align-items:center;justify-content:center;background:#f0f0f0;font-size:9px;color:#666;border:1px solid #ccc}
.mdd-elem-image{display:flex;align-items:center;justify-content:center;background:#f5f5f5;font-size:10px;color:#999;overflow:hidden}
.mdd-elem-image img{max-width:100%;max-height:100%;object-fit:contain}
.mdd-elem-line{background:#000}
.mdd-props{width:260px;flex-shrink:0;background:#f8f8f8;border:1px solid #ccc;border-radius:4px;padding:10px;font-size:13px;max-height:600px;overflow-y:auto}
.mdd-props h4{margin:0 0 8px;font-size:14px;border-bottom:1px solid #ddd;padding-bottom:4px}
.mdd-props label{display:block;margin:6px 0 2px;font-weight:600;font-size:12px}
.mdd-props input,.mdd-props select,.mdd-props textarea{width:100%;padding:4px 6px;border:1px solid #bbb;border-radius:3px;font-size:12px;box-sizing:border-box}
.mdd-props input[type=number]{width:70px}
.mdd-props input[type=file]{border:none;padding:0}
.mdd-props .mdd-prop-row{display:flex;gap:8px}
.mdd-props .mdd-prop-row>div{flex:1}
.mdd-save-indicator{display:inline-block;margin-left:8px;font-size:12px;color:#888}
.mdd-save-indicator.saving{color:#c80}
.mdd-save-indicator.saved{color:#080}
</style>

<div class="mdd-toolbar" id="mdd-toolbar">
	<button type="button" id="btn-add-text">+ Text</button>
	<button type="button" id="btn-add-barcode">+ Barcode</button>
	<button type="button" id="btn-add-qrcode">+ QR Code</button>
	<button type="button" id="btn-add-image">+ Image</button>
	<button type="button" id="btn-add-line">+ Line</button>
	<div class="mdd-sep"></div>
	<button type="button" id="btn-duplicate">Duplicate</button>
	<button type="button" id="btn-delete">Delete</button>
	<div class="mdd-sep"></div>
	<button type="button" id="btn-save"><strong>Save</strong></button>
	<span class="mdd-save-indicator" id="mdd-save-status"></span>
	<div class="mdd-sep"></div>
	<span style="font-size:12px;color:#666">
		<?php echo dol_escape_htmltag($object->label_width.'mm x '.$object->label_height.'mm'); ?>
		(<?php echo dol_escape_htmltag($object->label_size); ?>)
	</span>
</div>

<div class="mdd-designer-wrap">
	<div class="mdd-canvas-wrap">
		<div class="mdd-canvas" id="mdd-canvas"></div>
	</div>
	<div class="mdd-props" id="mdd-props">
		<h4>Properties</h4>
		<div id="mdd-props-content"><p class="opacitymedium">Select an element to edit its properties.</p></div>
	</div>
</div>

<?php
// Load the designer JavaScript as a separate file
// The JS is inline here for simplicity but uses safe DOM methods
?>
<script src="<?php echo dol_buildpath('/mokodolidymo/js/label_designer.js.php', 1); ?>?v=<?php echo filemtime(__DIR__.'/js/label_designer.js.php') ?: '1'; ?>"></script>
<script>
// Initialize designer with server-side data
MDD.init(<?php echo json_encode($js_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
</script>

<?php
print dol_get_fiche_end();
llxFooter();
$db->close();
