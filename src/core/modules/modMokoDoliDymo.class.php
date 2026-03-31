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
			'hooks' => array(),
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
		$this->tabs = array();
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

		// Permissions
		$this->rights = array();
		$r = 0;
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$o = 1;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 1);
		$this->rights[$r][1] = 'Read label templates';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 2);
		$this->rights[$r][1] = 'Create/Update label templates';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 3);
		$this->rights[$r][1] = 'Delete label templates';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'delete';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", ($o * 10) + 4);
		$this->rights[$r][1] = 'Print labels';
		$this->rights[$r][4] = 'label';
		$this->rights[$r][5] = 'print';
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
		global $conf, $langs;

		$result = $this->_load_tables('/mokodolidymo/sql/');
		if ($result < 0) {
			return -1;
		}

		$this->remove($options);

		$sql = array();

		return $this->_init($sql, $options);
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
