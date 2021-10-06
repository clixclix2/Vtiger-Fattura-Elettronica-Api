<?php
/**
 * FatturaElettronicaApi - Modulo vTiger per la creazione e la trasmissione delle fatture elettroniche al SDI
 *                         tramite il servizio Fattura Elettronica Api (https://fattura-elettronica-api.it/)
 * @author Claudio Castelpietra - www.itala.it
 * @license GPLv3 - https://opensource.org/licenses/GPL-3.0
 * Tratto da un lavoro originario di: https://github.com/TommasoBilotta/vtiger-fattura-elettronica
 */

require_once("modules/FatturaElettronicaApi/resources/functions/fatturaelettronicaapi.php");

class FatturaElettronicaApi_FatturaElettronicaSync_Action extends Vtiger_Action_Controller {
	function __construct() {
                parent::__construct();
        }

	public function checkPermission(Vtiger_Request $request) {
		return true;
		//return parent::checkPermission($request);
	}

	public function process(Vtiger_Request $request) {
		
		try {
			$recordid = $request->get('recordid');
            $app = $request->get('app');
			saveDocument($recordid, "FE", $app);
		} catch (WebServiceException $ex) {
			echo $ex->getMessage();
		}
	}
}
