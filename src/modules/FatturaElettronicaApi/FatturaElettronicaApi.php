<?php
/**
 * FatturaElettronicaApi - Modulo vTiger per la creazione e la trasmissione delle fatture elettroniche al SDI
 *                         tramite il servizio Fattura Elettronica Api (https://fattura-elettronica-api.it/)
 * @author Claudio Castelpietra - www.itala.it
 * @license GPLv3 - https://opensource.org/licenses/GPL-3.0
 * Tratto da un lavoro originario di: https://github.com/TommasoBilotta/vtiger-fattura-elettronica
 */

require_once("modules/FatturaElettronicaApi/resources/installa.php");

class FatturaElettronicaApi {
	var $log;
	var $db;

	function __construct() {
		//$this->log = LoggerManager::getLogger('FatturaElettronicaApi');
		$this->db = PearDatabase::getInstance();

		// array of modules that are allowed for basic version type
		$this->basicModules = array("6", "20", "21", "22", "23");

		// array of action names used in profiles permissions
		$this->profilesActions = array(
			"EDIT" => "EditView", // Create/Edit
			"DETAIL" => "DetailView", // View
			"DELETE" => "Delete", // Delete
		);

		$this->profilesPermissions = array();

		$this->name = "FatturaElettronicaApi";
		$this->id = getTabId("FatturaElettronicaApi");
	}


	function vtlib_handler($moduleName, $eventType) {
		if ($eventType == 'module.postinstall') {
			installa_modulo();
		}

		if ($eventType == 'module.preuninstall') {
			elimina_modulo();
		}
		if ($eventType == 'module.disabled') {
			disabilita_modulo();
		}
		if ($eventType == 'module.enabled') {
			abilita_modulo();
		}

	}


	function getNonAdminAccessControlQuery(){
		return '';
	}

	/**
	 * Metodo per Link->getAllByType
	 * @var Vtiger_LinkData $linkData
	 */
	static function checkLinkInviaFattura($linkData) {
		$recordId = $linkData->getInputParameter('record');
		if ($recordId) {
			//$link = $linkData->getLink();
			$db = PearDatabase::getInstance();
			$res = $db->pquery("SELECT fe_sdi_stato FROM vtiger_invoice WHERE invoiceid = ?", array($recordId));
			$line = $db->fetch_array($res);
			if ($line['fe_sdi_stato'] != '' && $line['fe_sdi_stato'] != 'Errore') {
				return false;
			} else {
				return true;
			}
		}
		return false;
	}
}
