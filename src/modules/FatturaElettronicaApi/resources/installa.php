<?php
/**
 * FatturaElettronicaApi - Modulo vTiger per la creazione e la trasmissione delle fatture elettroniche al SDI
 *                         tramite il servizio Fattura Elettronica Api (https://fattura-elettronica-api.it/)
 * @author Claudio Castelpietra - www.itala.it
 * @license GPLv3 - https://opensource.org/licenses/GPL-3.0
 * Tratto da un lavoro originario di: https://github.com/TommasoBilotta/vtiger-fattura-elettronica
 */

include_once('vtlib/Vtiger/Menu.php');
include_once 'vtlib/Vtiger/Module.php';
$Vtiger_Utils_Log = true;


function add_codice_fiscale($_modulo, $block) {
        $module = Vtiger_Module::getInstance($_modulo);
        $block = Vtiger_Block::getInstance($block, $module);

        $codicef = Vtiger_Field::getInstance('fiscal_code', $module);
        if ($codicef)
                $codicef->delete();

        $codicef  = new Vtiger_Field();
        $codicef->name = 'fiscal_code';
        $codicef->label= 'LBL_FISCAL_CODE';
        $codicef->uitype= 2;
        $codicef->column = $codicef->name;
        $codicef->columntype = 'VARCHAR(255)';
        $codicef->typeofdata = 'V~M';
        $block->addField($codicef);

}

function add_partita_iva($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);

	$partitaiva = Vtiger_Field::getInstance('vat_number', $module);
	if ($partitaiva)
		$partitaiva->delete();

        $partitaiva  = new Vtiger_Field();
        $partitaiva->name = 'vat_number';
        $partitaiva->label = 'LBL_VAT_NUMBER';
        $partitaiva->uitype= 2;
        $partitaiva->column = $partitaiva->name;
        $partitaiva->columntype = 'VARCHAR(255)';
        $partitaiva->typeofdata = 'V~M';
        $block->addField($partitaiva);
}

function add_fe_vat_nature($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);

	$fieldInstance = Vtiger_Field::getInstance('fevatnature', $module);
	if ($fieldInstance)
		$fieldInstance->delete();

    $fieldInstance  = new Vtiger_Field();
    $fieldInstance->name = 'fevatnature';
    $fieldInstance->label= 'Natura IVA';
    $fieldInstance->uitype= 16;
    $fieldInstance->column = $fieldInstance->name;
    $fieldInstance->columntype = 'VARCHAR(255)';
    $fieldInstance->typeofdata = 'V~M';
    $fieldInstance->setPicklistValues( Array ('N1 - escluse ex art. 15', 'N2 - non soggette', 'N3 - non imponibili', 'N4 - esenti', 'N5 - regime del margine', 'N6 - inversione contabile (reverse charge)') );
    $block->addField($fieldInstance);
}

function add_fe_payment_code($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);

	$fieldInstance = Vtiger_Field::getInstance('fepaymentcode', $module);
	if ($fieldInstance)
		$fieldInstance->delete();

    $fieldInstance  = new Vtiger_Field();
    $fieldInstance->name = 'fepaymentcode';
    $fieldInstance->label= 'Metodo di pagamento';
    $fieldInstance->uitype= 16;
    $fieldInstance->column = $fieldInstance->name;
    $fieldInstance->columntype = 'VARCHAR(255)';
    $fieldInstance->typeofdata = 'V~M';
    $fieldInstance->setPicklistValues( Array ('MP01 - pagamenti in contanti', 'MP05 - pagamenti tramite bonifico', 'MP08 - pagamenti tramite carta di credito', 'MP12 - pagamenti tramite RiBa') );
    $block->addField($fieldInstance);
}

function add_vat_type($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);

	$fieldInstance = Vtiger_Field::getInstance('vattype', $module);
	if ($fieldInstance)
		$fieldInstance->delete();

    $fieldInstance  = new Vtiger_Field();
    $fieldInstance->name = 'vattype';
    $fieldInstance->label= 'Tipo di IVA';
    $fieldInstance->uitype= 16;
    $fieldInstance->column = $fieldInstance->name;
    $fieldInstance->columntype = 'VARCHAR(255)';
    $fieldInstance->typeofdata = 'V~O';
    $fieldInstance->setPicklistValues( Array ('I - Immediata', 'S - Pagamenti separati (split payment)') );
    $block->addField($fieldInstance);
}

function add_fe_customer_pec($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);

	$fieldInstance = Vtiger_Field::getInstance('fecustomerpec', $module);
	if ($fieldInstance)
		$fieldInstance->delete();

        $fieldInstance  = new Vtiger_Field();
        $fieldInstance->name = 'fecustomerpec';
        $fieldInstance->label= 'Email PEC';
        $fieldInstance->uitype= 2;
        $fieldInstance->column = $fieldInstance->name;
        $fieldInstance->columntype = 'VARCHAR(14)';
        $fieldInstance->typeofdata = 'V~M';
        $block->addField($fieldInstance);
}

function add_fe_destination_code($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);

	$fieldInstance = Vtiger_Field::getInstance('fedestinationcode', $module);
	if ($fieldInstance)
		$fieldInstance->delete();

        $fieldInstance  = new Vtiger_Field();
        $fieldInstance->name = 'fedestinationcode';
        $fieldInstance->label= 'Codice destinatario SDI';
        $fieldInstance->uitype= 2;
        $fieldInstance->column = $fieldInstance->name;
        $fieldInstance->columntype = 'VARCHAR(255)';
        $fieldInstance->typeofdata = 'V~M';
        $block->addField($fieldInstance);
}


function add_fe_sdi_stato($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);
	
	$field = Vtiger_Field::getInstance('fe_sdi_stato', $module);
	if ($field)
		$field->delete();
	
	$field = new Vtiger_Field();
	$field->name = 'fe_sdi_stato';
	$field->label = 'Stato SDI';
	//$field->uitype = 2;
	$field->uitype= 16; // picklist
	$field->column = $field->name;
	$field->columntype = 'VARCHAR(18)';
	$field->typeofdata = 'V~O'; // varchar opzionale
	$field->displaytype = 2; // readonly / solo detail view
	$field->setPicklistValues( Array ('Inviato', 'Errore', 'Non consegnato', 'Consegnato', 'Accettato', 'Rifiutato', 'Decorrenza Termini', 'Ricevuto') );
	$block->addField($field);
}
function add_fe_sdi_messaggio($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);
	
	$field = Vtiger_Field::getInstance('fe_sdi_messaggio', $module);
	if ($field)
		$field->delete();
	
	$field = new Vtiger_Field();
	$field->name = 'fe_sdi_messaggio';
	$field->label = 'Messaggio SDI';
	$field->uitype = 21; // 19 - textarea con colspan=2 - 21 textarea
	$field->column = $field->name;
	$field->columntype = 'TEXT';
	$field->typeofdata = 'V~O'; // varchar opzionale
	$field->displaytype = 2; // readonly / solo detail view
	$block->addField($field);
}
function add_fe_sdi_identificativo($_modulo, $block) {
	$module = Vtiger_Module::getInstance($_modulo);
	$block = Vtiger_Block::getInstance($block, $module);
	
	$field = Vtiger_Field::getInstance('fe_sdi_identificativo', $module);
	if ($field)
		$field->delete();
	
	$field = new Vtiger_Field();
	$field->name = 'fe_sdi_identificativo';
	$field->label = 'Identificativo SDI';
	$field->uitype= 2;
	$field->column = $field->name;
	$field->columntype = 'VARCHAR(36)';
	$field->typeofdata = 'V~O'; // varchar opzionale
	$field->displaytype = 2; // readonly / solo detail view
	$block->addField($field);
}



function installa_modulo() {
	// se esistono già i campi
	add_codice_fiscale("Accounts", "LBL_ACCOUNT_INFORMATION");
	add_codice_fiscale("Contacts", "LBL_CONTACT_INFORMATION");
	add_partita_iva("Accounts", "LBL_ACCOUNT_INFORMATION");
	add_partita_iva("Contacts", "LBL_CONTACT_INFORMATION");
    add_fe_vat_nature("Products", "LBL_PRICING_INFORMATION");
    add_fe_payment_code("Invoice", "LBL_INVOICE_INFORMATION");
    add_vat_type("Accounts", "LBL_ACCOUNT_INFORMATION");
    add_vat_type("Contacts", "LBL_CONTACT_INFORMATION");
    add_fe_customer_pec("Accounts", "LBL_ADDRESS_INFORMATION");
    add_fe_customer_pec("Contacts", "LBL_ADDRESS_INFORMATION");
    add_fe_destination_code("Accounts", "LBL_ADDRESS_INFORMATION");
    add_fe_destination_code("Contacts", "LBL_ADDRESS_INFORMATION");
	
	add_fe_sdi_stato("Invoice", "LBL_INVOICE_INFORMATION");
	add_fe_sdi_messaggio("Invoice", "LBL_INVOICE_INFORMATION");
	add_fe_sdi_identificativo("Invoice", "LBL_INVOICE_INFORMATION");
	
	abilita_modulo();
	/*
	$mod_invoice = Vtiger_Module::getInstance('Invoice');
	//$mod_invoice->deleteLink('DETAILVIEWBASIC', 'Invia a FatturaElettronicaApi');
	//$mod_invoice->addLink('DETAILVIEWBASIC', 'Invia a FatturaElettronicaApi', 'index.php?module=FatturaElettronicaApi&action=InvoiceSync&recordid=$RECORD$');
    $mod_invoice->deleteLink('DETAILVIEWBASIC', 'Invia Fattura Elettronica');
	$mod_invoice->addLink('DETAILVIEWBASIC', 'Invia Fattura Elettronica', 'index.php?module=FatturaElettronicaApi&action=FatturaElettronicaSync&recordid=$RECORD$');
	*/

	/*
    $modules = array("Accounts", "Contacts");
    foreach ($modules as $mod) {
        $v_mod = Vtiger_Module::getInstance($mod);
        $v_mod->deleteLink('LISTVIEW', 'Sincronizza con FatturaElettronicaApi');
        $v_mod->addLink('LISTVIEW', 'Sincronizza con FatturaElettronicaApi', 'index.php?module=FatturaElettronicaApi&action=AllCustomersSync&srcmodule=$MODULE$&app=INVENTORY');
        $v_mod->deleteLink('DETAILVIEWBASIC', 'Sincronizza con FatturaElettronicaApi');
        $v_mod->addLink('DETAILVIEWBASIC', 'Sincronizza con FatturaElettronicaApi', 'index.php?module=FatturaElettronicaApi&action=CustomerSync&recordid=$RECORD$&srcmodule=$MODULE$&app=INVENTORY');
    }
	*/
	
	// Installazione CRON JOB
	$cronName = 'FatturaElettronicaApi - CheckSDI';
	$adb = PearDatabase::getInstance();
	$res = $adb->pquery("
		SELECT id FROM vtiger_cron_task WHERE name = ?
	", [$cronName]);
	if ($adb->num_rows($res) == 0) {
		$adb->pquery("INSERT INTO vtiger_cron_task SET
								name = ?,
								handler_file = 'modules/FatturaElettronicaApi/cron/CheckSDI.service',
                                 frequency = 900,
                                 status = 1,
                                 module = 'FatturaElettronicaApi',
                                 sequence = (select m from (select max(sequence)+1 as m from vtiger_cron_task) s),
                                 description = 'Recommended frequency is 15 mins'
", [$cronName]);
	}
}

function elimina_modulo() {
	
	disabilita_modulo();
	/*
	$mod_invoice = Vtiger_Module::getInstance('Invoice');
	//$mod_invoice->deleteLink('DETAILVIEWBASIC', 'Invia a FatturaElettronicaApi');
    $mod_invoice->deleteLink('DETAILVIEWBASIC', 'Invia Fattura Elettronica');
	*/

	/*
    $modules = array("Accounts", "Contacts");
    foreach ($modules as $mod) {
        $v_mod = Vtiger_Module::getInstance($mod);
        $v_mod->deleteLink('LISTVIEW', 'Sincronizza con FatturaElettronicaApi');
        $v_mod->deleteLink('DETAILVIEWBASIC', 'Sincronizza con FatturaElettronicaApi');
    }
	*/
	$mod_acc = Vtiger_Module::getInstance('Accounts');
	
	$codicefiscale = Vtiger_Field::getInstance ( 'fiscal_code', $mod_acc );
	if ($codicefiscale)
		$codicefiscale->delete();

	$partita_iva = Vtiger_Field::getInstance ( 'vat_number', $mod_acc );
	if ($partita_iva)
		$partita_iva->delete();

    $vat_type = Vtiger_Field::getInstance ( 'vattype', $mod_acc );
	if ($vat_type)
		$vat_type->delete();

    $fe_customer_pec = Vtiger_Field::getInstance ( 'fecustomerpec', $mod_acc );
	if ($fe_customer_pec)
		$fe_customer_pec->delete();

    $fe_destination_code = Vtiger_Field::getInstance ( 'fedestinationcode', $mod_acc );
	if ($fe_destination_code)
		$fe_destination_code->delete();

	
	$mod_con = Vtiger_Module::getInstance('Contacts');
	
	$codicefiscale = Vtiger_Field::getInstance ( 'fiscal_code', $mod_con );
	if ($codicefiscale)
		$codicefiscale->delete();

	$partita_iva = Vtiger_Field::getInstance ( 'vat_number', $mod_con );
	if ($partita_iva)
		$partita_iva->delete();

    $vat_type = Vtiger_Field::getInstance ( 'vattype', $mod_con );
	if ($vat_type)
		$vat_type->delete();

    $fe_customer_pec = Vtiger_Field::getInstance ( 'fecustomerpec', $mod_con );
	if ($fe_customer_pec)
		$fe_customer_pec->delete();

    $fe_destination_code = Vtiger_Field::getInstance ( 'fedestinationcode', $mod_con );
	if ($fe_destination_code)
		$fe_destination_code->delete();

    $mod_prod = Vtiger_Module::getInstance('Products');
	$fevatnature = Vtiger_Field::getInstance ( 'fevatnature', $mod_prod );
	if ($fevatnature)
		$fevatnature->delete();

    $fepaymentcode = Vtiger_Field::getInstance ( 'fepaymentcode', $mod_prod );
	if ($fepaymentcode)
		$fepaymentcode->delete();
}

function update_modulo() {
    add_fe_vat_nature("Products", "LBL_PRICING_INFORMATION");
    add_fe_payment_code("Invoice", "LBL_INVOICE_INFORMATION");
    add_vat_type("Accounts", "LBL_ACCOUNT_INFORMATION");
    add_vat_type("Contacts", "LBL_CONTACT_INFORMATION");
    add_fe_customer_pec("Accounts", "LBL_ADDRESS_INFORMATION");
    add_fe_customer_pec("Contacts", "LBL_ADDRESS_INFORMATION");
    add_fe_destination_code("Accounts", "LBL_ADDRESS_INFORMATION");
    add_fe_destination_code("Contacts", "LBL_ADDRESS_INFORMATION");
	
	add_fe_sdi_stato("Invoice", "LBL_INVOICE_INFORMATION");
	add_fe_sdi_messaggio("Invoice", "LBL_INVOICE_INFORMATION");
	add_fe_sdi_identificativo("Invoice", "LBL_INVOICE_INFORMATION");
	
	abilita_modulo();
	/*
	$mod_invoice = Vtiger_Module::getInstance('Invoice');
	//$mod_invoice->deleteLink('DETAILVIEWBASIC', 'Invia a FatturaElettronicaApi');
	//$mod_invoice->addLink('DETAILVIEWBASIC', 'Invia a FatturaElettronicaApi', 'index.php?module=FatturaElettronicaApi&action=InvoiceSync&recordid=$RECORD$');
    $mod_invoice->deleteLink('DETAILVIEWBASIC', 'Invia Fattura elettronica');
	$mod_invoice->addLink('DETAILVIEWBASIC', 'Invia Fattura elettronica', 'index.php?module=FatturaElettronicaApi&action=FatturaElettronicaSync&recordid=$RECORD$');
	*/

	/*
    $modules = array("Accounts", "Contacts");
    foreach ($modules as $mod) {
        $v_mod = Vtiger_Module::getInstance($mod);
        $v_mod->deleteLink('LISTVIEW', 'Sincronizza con FatturaElettronicaApi');
        $v_mod->addLink('LISTVIEW', 'Sincronizza con FatturaElettronicaApi', 'index.php?module=FatturaElettronicaApi&action=AllCustomersSync&srcmodule=$MODULE$&app=INVENTORY');
        $v_mod->deleteLink('DETAILVIEWBASIC', 'Sincronizza con FatturaElettronicaApi');
        $v_mod->addLink('DETAILVIEWBASIC', 'Sincronizza con FatturaElettronicaApi', 'index.php?module=FatturaElettronicaApi&action=CustomerSync&recordid=$RECORD$&srcmodule=$MODULE$&app=INVENTORY');
    }
	*/
}


function disabilita_modulo() {
	$mod_invoice = Vtiger_Module::getInstance('Invoice');
	$mod_invoice->deleteLink('DETAILVIEWBASIC', 'Invia Fattura Elettronica');
}
function abilita_modulo() {
	$mod_invoice = Vtiger_Module::getInstance('Invoice');
	$mod_invoice->addLink('DETAILVIEWBASIC', 'Invia Fattura elettronica', 'index.php?module=FatturaElettronicaApi&action=FatturaElettronicaSync&recordid=$RECORD$',
	'', 0, array(
			'path' => 'modules/FatturaElettronicaApi/FatturaElettronicaApi.php',
			'class' => 'FatturaElettronicaApi',
			'method' => 'checkLinkInviaFattura'
		));
}