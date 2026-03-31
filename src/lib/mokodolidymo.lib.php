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
 * \file    mokodolidymo/lib/mokodolidymo.lib.php
 * \ingroup mokodolidymo
 * \brief   Library files with common functions for MokoDoliDymo
 */

/**
 * Prepare admin pages header
 *
 * @return array<array{string,string,string}>
 */
function mokodolidymoAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("mokodolidymo@mokodolidymo");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/mokodolidymo/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath("/mokodolidymo/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'mokodolidymo@mokodolidymo');
	complete_head_from_modules($conf, $langs, null, $head, $h, 'mokodolidymo@mokodolidymo', 'remove');

	return $head;
}

/**
 * Prepare label template card tabs
 *
 * @param  LabelTemplate $object Label template object
 * @return array                  Tabs array
 */
function mokodolidymoLabelPrepareHead($object)
{
	global $langs, $conf;

	$langs->load("mokodolidymo@mokodolidymo");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/mokodolidymo/label_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("LabelTemplate");
	$head[$h][2] = 'card';
	$h++;

	$head[$h][0] = dol_buildpath("/mokodolidymo/label_designer.php", 1).'?id='.$object->id;
	$head[$h][1] = 'Designer';
	$head[$h][2] = 'designer';
	$h++;

	$head[$h][0] = dol_buildpath("/mokodolidymo/label_print.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("PrintLabel");
	$head[$h][2] = 'print';
	$h++;

	return $head;
}
