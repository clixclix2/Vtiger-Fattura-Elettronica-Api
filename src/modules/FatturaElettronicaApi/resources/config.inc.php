<?php
/**
 * FatturaElettronicaApi - Modulo vTiger per la creazione e la trasmissione delle fatture elettroniche al SDI
 *                         tramite il servizio Fattura Elettronica Api (https://fattura-elettronica-api.it/)
 * @author Claudio Castelpietra - www.itala.it
 * @license GPLv3 - https://opensource.org/licenses/GPL-3.0
 * Tratto da un lavoro originario di: https://github.com/TommasoBilotta/vtiger-fattura-elettronica
 */

$config = Array();

// Inserire credenziali fattura-elettronica-api:
$config['username'] = 'xxxx';
$config['password'] = 'xxxx';


$config['send_test'] = true; // invio a Fattura Elettronica Api come test - impostare a false per modalità produzione

$config['save_pdf'] = false; // dopo la trasmissione, salva in locale, tra i documenti correlati, la fattura di cortesia formato pdf della fattura elettronica
$config['save_xml'] = true; // dopo la trasmissione, salva in locale, tra i documenti correlati, la fattura elettronica generata in formato xml

// In fase di installazione, vengono creati i campi Partita Iva (vat_number) e Codice Fiscale (fiscal_code) nei moduli Account e Contact
// Se, per partita iva e codice fiscale si vogliono utilizzare campi differenti, magari già esistenti,
// indicare qui sotto il nome dei campi da cercare sul database al posto di quello di default

// per ogni campo: elenco dei campi vtiger da provare da estrarre
$config['fields'] = [
	"fiscal_code" => [
		//'cf_891', // per esempio: elencare un campo cf_xxx del modulo Account ed un campo cf_xxx del modulo Contact
		"fiscal_code" // modificare con nome campo (oppure, se si tratta di campi cf_ sia su Account sia su Contact, elencarli)
	],
	"vat_number" => [
		//'cf_751', // per esempio: elencare un campo cf_xxx del modulo Account ed un campo cf_xxx del modulo Contact
		"vat_number" // modificare con nome campo (oppure, se si tratta di campi cf_ sia su Account sia su Contact, elencarli)
	],
	
	"name" => [
		"accountname",
		"firstname"
	],
	"surname" => [
		"lastname"
	],
	"street" => [
		"bill_street",
		"mailingstreet",
	],
	"ship_street" => [
		"ship_steet"
	],
	"postal_code" => [
		"bill_code",
		"mailingzip",
	],
	"ship_postal_code" => [
		"ship_code"
	],
	"city" => [
		"bill_city",
		"mailingcity",
	],
	"ship_city" => [
		"ship_city"
	],
	"state" => [
		"bill_state",
		"mailingstate",
	],
	"country" => [
		"bill_country",
		"mailingcountry",
	],
	"ship_country" => [
		"ship_country"
	],
	"phone" => [
		"phone",
		"mobile",
		"homephone",
		"otherphone"
	],
	"email" => [
		"email",
		"email1",
		"secondaryemail"
	],
	"vat_type" => [
		"vattype"
	],
	"fe_customer_pec" => [
		"fecustomerpec"
	],
	"fe_destination_code" => [
		"fedestinationcode"
	]
];