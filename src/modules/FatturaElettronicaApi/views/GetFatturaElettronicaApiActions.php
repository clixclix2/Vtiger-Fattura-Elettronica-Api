<?php
/**
 * FatturaElettronicaApi - Modulo vTiger per la creazione e la trasmissione delle fatture elettroniche al SDI
 *                         tramite il servizio Fattura Elettronica Api (https://fattura-elettronica-api.it/)
 * @author Claudio Castelpietra - www.itala.it
 * @license GPLv3 - https://opensource.org/licenses/GPL-3.0
 * Tratto da un lavoro originario di: https://github.com/TommasoBilotta/vtiger-fattura-elettronica
 */

class FatturaElettronicaApi_GetFatturaElettronicaApiActions_View extends Vtiger_BasicAjax_View {
	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->view("SideBar.tpl", 'FatturaElettronicaApi');
	}
}
