<?php
/* Copyright (C) 2004-2018	Laurent Destailleur			<eldy@users.sourceforge.net>
	* Copyright (C) 2018-2019	Nicolas ZABOURI				<info@inovea-conseil.com>
	* Copyright (C) 2019-2024	Frédéric France				<frederic.france@free.fr>
	* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
	*
	* This program is free software; you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published by
	* the Free Software Foundation; either version 3 of the License, or
	* (at your option) any later version.
	*
	* This program is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	* GNU General Public License for more details.
	*
	* You should have received a copy of the GNU General Public License
	* along with this program. If not, see <https://www.gnu.org/licenses/>.
	*/

/**
	* 	\defgroup   mokodolidymo     Module MokoDoliDymo
	*  \brief      MokoDoliDymo module descriptor.
	*
	*  \file       htdocs/mokodolidymo/core/modules/modMokoDoliDymo.class.php
	*  \ingroup    mokodolidymo
	*  \brief      Description and activation file for module MokoDoliDymo
	*/
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';


/**
	*  Description and activation class for module MokoDoliDymo
	*/
class modMokoDoliDymo extends DolibarrModules
{
	/**
		* Constructor. Define names, constants, directories, boxes, permissions
		*
		* @param DoliDB $db Database handler
		*/
	public function __construct($db)
	{
		global $conf, $langs;

		$this->db = $db;

		$this->numero = 185072;
		$this->rights_class = 'mokodolidymo';
		$this->family = 'mokoconsulting';
		$this->module_position = '01';

		$this->familyinfo = array(
			'mokoconsulting' => array(
				'position' => '01',
				'label' => $langs->trans("Moko Consulting")
			)
		);

		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "ModuleMokoDoliDymoDesc";
		$this->descriptionlong = "ModuleMokoDoliDymoDescLong";

		$this->editor_name = 'Moko Consulting';
		$this->editor_url = 'https://mokoconsulting.tech';
		$this->editor_squarred_logo = '';

		$this->version = '01.00.00';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'fa-print';

		$this->module_parts = array(
			'triggers' => 0,
			'login' => 0,
			'substitutions' => 0,
			'menus' => 0,
			'tpl' => 0,
			'barcode' => 0,
			'models' => 0,
			'printing' => 0,
			'theme' => 0,
			'css' => array(),
			'js' => array(),
			/* BEGIN MODULEBUILDER HOOKSCONTEXTS */
			'hooks' => array(
				'data' => array(
					'productcard',
					'thirdpartycard',
					'contactcard',
					'expeditioncard',
					'deliverycard',
					'membercard',
					'stockcard',
				),
			),
			/* END MODULEBUILDER HOOKSCONTEXTS */
			'moduleforexternal' => 0,
			'websitetemplates' => 0,
			'captcha' => 0
		);

		$this->dirs = array("/mokodolidymo/temp");

		$this->depends = array();
		$this->requiredby = array();
		$this->conflictwith = array();

		$this->langfiles = array("mokodolidymo@mokodolidymo");

		$this->phpmin = array(7, 1);
		$this->need_dolibarr_version = array(19, -3);
		$this->need_javascript_ajax = 0;

		$this->warnings_activation = array();
		$this->warnings_activation_ext = array();

		$this->const = array();

		if (!isModEnabled("mokodolidymo")) {
			$conf->mokodolidymo = new stdClass();
			$conf->mokodolidymo->enabled = 0;
		}

		/* BEGIN MODULEBUILDER TABS */
		$this->tabs = array(
			'product:+mokodolidymo:Labels:mokodolidymo@mokodolidymo:$user->hasRight(\'mokodolidymo\', \'label\', \'read\'):/mokodolidymo/label_object_tab.php?object_type=product&fk_object=__ID__',
			'thirdparty:+mokodolidymo:Labels:mokodolidymo@mokodolidymo:$user->hasRight(\'mokodolidymo\', \'label\', \'read\'):/mokodolidymo/label_object_tab.php?object_type=thirdparty&fk_object=__ID__',
			'contact:+mokodolidymo:Labels:mokodolidymo@mokodolidymo:$user->hasRight(\'mokodolidymo\', \'label\', \'read\'):/mokodolidymo/label_object_tab.php?object_type=contact&fk_object=__ID__',
			'member:+mokodolidymo:Labels:mokodolidymo@mokodolidymo:$user->hasRight(\'mokodolidymo\', \'label\', \'read\'):/mokodolidymo/label_object_tab.php?object_type=member&fk_object=__ID__',
		);
		/* END MODULEBUILDER TABS */

		/* BEGIN MODULEBUILDER DICTIONARIES */
		$this->dictionaries = array();
		/* END MODULEBUILDER DICTIONARIES */

		/* BEGIN MODULEBUILDER WIDGETS */
		$this->boxes = array();
		/* END MODULEBUILDER WIDGETS */

		/* BEGIN MODULEBUILDER CRON */
		$this->cronjobs = array();
		/* END MODULEBUILDER CRON */

		// Permissions — granular access control
		$this->rights = array();
		$r = 0;
		/* BEGIN MODULEBUILDER PERMISSIONS */
		// Label template permissions
		$o = 1;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 1);
		$this->rights[$r][1] = 'Read label templates';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 2);
		$this->rights[$r][1] = 'Create label templates';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'create';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 3);
		$this->rights[$r][1] = 'Modify label templates';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 4);
		$this->rights[$r][1] = 'Delete label templates';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'delete';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 5);
		$this->rights[$r][1] = 'Use label designer';
		$this->rights[$r][4] = 'designer';
		$this->rights[$r][5] = 'use';
		$r++;
		// Print permissions
		$o = 2;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 1);
		$this->rights[$r][1] = 'Print labels';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'print';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 2);
		$this->rights[$r][1] = 'Print labels via DYMO';
		$this->rights[$r][4] = 'print';
		$this->rights[$r][5] = 'dymo';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 3);
		$this->rights[$r][1] = 'Export labels as PDF';
		$this->rights[$r][4] = 'print';
		$this->rights[$r][5] = 'pdf';
		$r++;
		// Import permissions
		$o = 3;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 1);
		$this->rights[$r][1] = 'Import DYMO label files';
		$this->rights[$r][4] = 'import';
		$this->rights[$r][5] = 'dymo';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 2);
		$this->rights[$r][1] = 'Import ODT label files';
		$this->rights[$r][4] = 'import';
		$this->rights[$r][5] = 'odt';
		$r++;
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries
		$this->menu = array();
		$r = 0;
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu' => '',
			'type' => 'top',
			'titre' => 'ModuleMokoDoliDymoName',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle"'),
			'mainmenu' => 'mokodolidymo',
			'leftmenu' => '',
			'url' => '/mokodolidymo/mokodolidymoindex.php',
			'langs' => 'mokodolidymo@mokodolidymo',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("mokodolidymo")',
			'perms' => '$user->hasRight("mokodolidymo", "label", "read")',
			'target' => '',
			'user' => 2,
		);
		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU LABEL */
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=mokodolidymo',
			'type' => 'left',
			'titre' => 'LabelTemplates',
			'prefix' => img_picto('', 'fa-tags', 'class="pictofixedwidth valignmiddle paddingright"'),
			'mainmenu' => 'mokodolidymo',
			'leftmenu' => 'mokodolidymo_labels',
			'url' => '/mokodolidymo/label_list.php',
			'langs' => 'mokodolidymo@mokodolidymo',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("mokodolidymo")',
			'perms' => '$user->hasRight("mokodolidymo", "label", "read")',
			'target' => '',
			'user' => 2,
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=mokodolidymo,fk_leftmenu=mokodolidymo_labels',
			'type' => 'left',
			'titre' => 'NewLabelTemplate',
			'mainmenu' => 'mokodolidymo',
			'leftmenu' => 'mokodolidymo_label_new',
			'url' => '/mokodolidymo/label_card.php?action=create',
			'langs' => 'mokodolidymo@mokodolidymo',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("mokodolidymo")',
			'perms' => '$user->hasRight("mokodolidymo", "label", "create")',
			'target' => '',
			'user' => 2,
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=mokodolidymo,fk_leftmenu=mokodolidymo_labels',
			'type' => 'left',
			'titre' => 'List',
			'mainmenu' => 'mokodolidymo',
			'leftmenu' => 'mokodolidymo_label_list',
			'url' => '/mokodolidymo/label_list.php',
			'langs' => 'mokodolidymo@mokodolidymo',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("mokodolidymo")',
			'perms' => '$user->hasRight("mokodolidymo", "label", "read")',
			'target' => '',
			'user' => 2,
		);
		/* END MODULEBUILDER LEFTMENU LABEL */

		/* BEGIN MODULEBUILDER EXPORT LABEL */
		/* END MODULEBUILDER EXPORT LABEL */

		/* BEGIN MODULEBUILDER IMPORT LABEL */
		/* END MODULEBUILDER IMPORT LABEL */
	}

	/**
		*  Function called when module is enabled.
		*
		*  @param      string  $options    Options when enabling module ('', 'noboxes')
		*  @return     int<-1,1>          	1 if OK, <=0 if KO
		*/
	public function init($options = '')
	{
		global $conf, $langs, $user;

		$result = $this->_load_tables('/mokodolidymo/sql/');
		if ($result < 0) {
			return -1;
		}

		$this->remove($options);

		$sql = array();

		$init_result = $this->_init($sql, $options);

		// Create extrafields on activation
		if ($init_result > 0) {
			include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
			$extrafields = new ExtraFields($this->db);
			// "Label Text" — paragraph-length field on products for label content (max 500 chars)
			$extrafields->addExtraField(
				'mokodolidymo_label_text',     // attr name
				'Label Text',                  // label
				'varchar',                     // type
				100,                           // position
				500,                           // size (max 500 chars — fits ~3-4 sentences on a label)
				'product',                     // element type
				0,                             // unique
				0,                             // required
				'',                            // default value
				'',                            // params
				1,                             // always editable
				'',                            // lang file
				0,                             // enabled condition (0 = always)
				0,                             // totalizable
				'',                            // help
				'',                            // computed
				'mokodolidymo@mokodolidymo',   // entity
				'isModEnabled("mokodolidymo")' // visible condition
			);

			// Install default label templates
			dol_include_once('/mokodolidymo/class/DefaultTemplates.class.php');
			if (class_exists('DefaultTemplates')) {
				$user_id = is_object($user) ? $user->id : 1;
				DefaultTemplates::install($this->db, $user_id);
			}
		}

		return $init_result;
	}

	/**
		*	Function called when module is disabled.
		*
		*	@param	string		$options	Options when enabling module ('', 'noboxes')
		*	@return	int<-1,1>				1 if OK, <=0 if KO
		*/
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
