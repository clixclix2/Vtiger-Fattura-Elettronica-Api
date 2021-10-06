<?php
/**
 * FatturaElettronicaApi - Modulo vTiger per la creazione e la trasmissione delle fatture elettroniche al SDI
 *                         tramite il servizio Fattura Elettronica Api (https://fattura-elettronica-api.it/)
 * @author Claudio Castelpietra - www.itala.it
 * @license GPLv3 - https://opensource.org/licenses/GPL-3.0
 * Tratto da un lavoro originario di: https://github.com/TommasoBilotta/vtiger-fattura-elettronica
 */

function getConfig() {
	include "modules/FatturaElettronicaApi/resources/config.inc.php";
	return $config;
}

function retriveEntity($id, $module) {
	try {
		$focus = CRMEntity::getInstance($module);
		$focus->id = $id;
		$focus->apply_field_security();
		$focus->retrieve_entity_info($id, $module);
		return $focus;
	} catch(Exception $e) {
		echo $e->getMessage();
		return "";
	}
}

function getFatturaElettronicaApiInstance() {
	static $feac = null;
	
	if (!$feac) {
		$config = getConfig();
		require_once __DIR__ . '/../libs/FatturaElettronicaApiClient.class.php';
		$feac = new FatturaElettronicaApiClient($config['username'], $config['password']);
	}
	
	return $feac;
}

function testApiKey() {
	try {
		
		$faec = getFatturaElettronicaApiInstance();
		
		echo "Verifico utenza ....</BR>";

		$ret = $faec->verificaAutenticazione();
		echo "Risultato: ".$ret; //."</BR>";
		if (!$ret) {
			echo $faec->ultimoErrore();
		}
		echo "</br>";

		return $ret;


	} catch(Exception $e) {
		echo $e->getMessage();
	}
}


function retriveInvoice($id) {
	try {
		$invoice = retriveEntity($id, "Invoice");
		$accountid=$invoice->column_fields["account_id"];
		$account = retriveEntity($accountid, "Accounts");
		$contactid=$invoice->column_fields["contact_id"];
		$contact = retriveEntity($contactid, "Contacts");
		$related_products = getAssociatedProducts("Invoice",$invoice);

		$ret = [
			"invoice" => $invoice,
			"account" => $account,
			"contact" => $contact,
			"products" => $related_products
		];

		return $ret;
	} catch (WebServiceException $ex) {
		echo $ex->getMessage();
	}
}

function get_columnname($label) {
	try {
		$adb = PearDatabase::getInstance();
		$query = "select columnname from vtiger_field where fieldlabel = \"".$label."\" and tablename like \"vtiger_account%\"";
		$ids = $adb->pquery($query);
		if ($adb->num_rows($ids) > 0) {
			$data = $adb->fetch_array($ids);
			return $data['columnname'];
		} else
			return null;
	} catch(Exception $ex) {
		echo $ex->getMessage();
	}
}

function saveDocument($id, $type, $app) {
	try {
		
        $config = getConfig();
		
		$feac = getFatturaElettronicaApiInstance();
		
		
		

		$invoiceElements = retriveInvoice($id);
		
		$invoice = $invoiceElements['invoice'];
		// prima verifichiamo se è possibile procedere
		$sdiStato = $invoice->column_fields['fe_sdi_stato'];
		if ($sdiStato != '' && $sdiStato != 'Errore') {
			?>
			<script>
			alert("ATTENZIONE: non e' possibile trasmettere nuovamente il documento");
            history.back();
			</script>
			<?php
			die();
		}
		
		
		list($datiDestinatario, $datiDocumento, $righeDocumento) = getDatiDocumentoFEAPI($invoiceElements, $type);
		
		$res = $feac->inviaConDati($datiDestinatario, $datiDocumento, $righeDocumento, null, $config['send_test']);
		
		
		if ($res['ack'] == 'OK') {
			$identificativoSDI = $res['data']['sdi_identificativo'];
			$fatturaXml = $res['data']['sdi_fattura'];
			$messaggio = $res['data']['sdi_messaggio'];
			$nomeFile = $res['data']['sdi_nome_file'];
			
			$invoice_number = preg_replace("/[^0-9]/", '', $invoice->column_fields["invoice_no"]);
            
            if ($config['save_pdf']) {
				$resPdf = $feac->ottieniPDF($identificativoSDI);
				$pdf = base64_decode($resPdf['data']['pdf']);
				$fileName = 'Invoice_' . sprintf('%06d', $invoice_number) . '_' . $type . '.pdf';
				$document = save_file($pdf, $fileName, 'Fattura ' . $invoice_number . ' - copia di cortesia');
	
				$invoice->save_related_module("Invoice",$invoice->id,"Documents",$document->id);
			}
			
            if ($config['save_xml']) {
				$fileName = $nomeFile;
				$document = save_file($fatturaXml, $fileName, 'Fattura elettronica ' . $invoice_number);
	
				$invoice->save_related_module("Invoice",$invoice->id,"Documents",$document->id);
			}
   
			
			$stato = 'Inviato';
			
		} else {
			
			$stato = 'Errore';
			$messaggio = $res['error'];
			$identificativoSDI = '';
			$fatturaXml = '';
			$nomeFile = '';
			
		}
		
		$db = PearDatabase::getInstance();
		$db->pquery("UPDATE vtiger_invoice SET fe_sdi_stato = ?, fe_sdi_messaggio = ?, fe_sdi_identificativo = ? WHERE invoiceid = ?",
			Array($stato, $messaggio, $identificativoSDI, $id));
		

        if (true) {
            header("Location: index.php?module=Invoice&relatedModule=Documents&view=Detail&record=".$invoice->id."&mode=showRelatedList&relationId=78&tab_label=Documents&app=".$app."");
        }

	} catch(Exception $e) {
		echo $e->getMessage();
	}
}


function get_field_data($data, $name) {
	$config = getConfig();
	foreach ($config["fields"][$name] as $field) {
		try {
			$value = htmlspecialchars($data[$field]);
			if ($value) {
				return $value;
			}
		} catch(Exception $e) {
			echo $e;
		}
	}

	return "";
}


function getDatiDocumentoFEAPI($fattura, $type) {
	$config = getConfig();


	$invoice_vet = $fattura["invoice"]->column_fields;
	$account_vet = $fattura["account"];
	$contact_vet = $fattura["contact"];
	
	
# Invoice
	$subject = htmlspecialchars($invoice_vet["subject"]);
    $sub_totale = htmlspecialchars($invoice_vet["hdnSubTotal"]);
	$totale = htmlspecialchars($invoice_vet["hdnGrandTotal"]);
	$iva_amount = htmlspecialchars($invoice_vet["tax1"]);
    if (!$iva_amount) {
        $iva_amount = htmlspecialchars($invoice_vet["tax8"]);
    }
	$tasse = $sub_totale * ($iva_amount / 100);
	$termini = htmlspecialchars($invoice_vet["terms_conditions"]);
	$data_pagamento = $invoice_vet['duedate'];

    if ($type == "FE") {
        $fepaymentcode = explode(" -", $invoice_vet["fepaymentcode"])[0];
    }

# Customer
	if ($account_vet) {
		$entity_fields = $account_vet->column_fields;
	} else {
		$entity_fields = $contact_vet->column_fields;
	}

	$name = get_field_data($entity_fields, "name");
	$surname = get_field_data($entity_fields, "surname");
	$phone = get_field_data($entity_fields, "phone");
	$vat_number = get_field_data($entity_fields, "vat_number");
	$fiscal_code = get_field_data($entity_fields, "fiscal_code");

	if ($type == "FE") {
		$vat_type = get_field_data($entity_fields, "vat_type");
		$fe_customer_pec = get_field_data($entity_fields, "fe_customer_pec");
		$fe_destination_code = get_field_data($entity_fields, "fe_destination_code");
	}

	$email = get_field_data($entity_fields, "email");
	$street = get_field_data($entity_fields, "street");
	$city = get_field_data($entity_fields, "city");
	$state = get_field_data($entity_fields, "state");
	$postal_code = get_field_data($entity_fields, "postal_code");
	$country = get_field_data($entity_fields, "country");
	// $bill_pobox = htmlspecialchars($entity_fields["bill_pobox"]);
	/*
		$ship_street = get_field_data($entity_fields, "ship_street");
		$ship_city = get_field_data($entity_fields, "ship_city");
		$ship_state = get_field_data($entity_fields, "ship_state");
		$ship_postal_code = get_field_data($entity_fields, "ship_postal_code");
		$ship_country = get_field_data($entity_fields, "ship_country");
		// $ship_pobox = htmlspecialchars($entity_fields["ship_pobox"]);
*/
	//$country = $country ?: $state;

	# FE
	/*
	if ($type == "FE") {
		if ($vat_type) {
			$vat_type = explode(" -", $vat_type)[0];
			$vat_type = "<VatType>".$vat_type."</VatType>\n";
		}
	
		if ($fepaymentcode == "MP05") {
			$paymentdescript = "<PaymentMethodDescription>".$iban."</PaymentMethodDescription>\n";
		}
	}
	*/
	
	# Products
	$products = $fattura["products"];

	
	
	$datiDestinatario = [
		'PartitaIVA' => $vat_number,
		'CodiceFiscale' => $fiscal_code,
		'CodiceSDI' => $fe_destination_code,
		'Denominazione' => ($surname ? ($name." ".$surname) : $name),
		'Indirizzo' => $street,
		'CAP' => $postal_code,
		'Comune' => $city,
		'Provincia' => $state,
		'Nazione' => $country
	];
	
	$datiDocumento = [
		'tipo' => 'FATT', // FATT,NDC
		'Data' => $invoice_vet['invoicedate'],
		'Numero' => preg_replace("/[^0-9]/", '', $invoice_vet['invoice_no']),
		'Causale' => $subject,
		'DatiPagamento' => [
			'ModalitaPagamento' => $fepaymentcode,
			'DataScadenzaPagamento' => $data_pagamento,
			'ImportoPagamento' => number_format($totale,2,'.','')
		]
	];
	
	$righeDocumento = [];
	
	$count = 1;
	foreach($products as $ps) {
		
		$productid = $ps["hdnProductcode$count"];
		$quantity = $ps["qty$count"];
		$price = $ps["listPrice$count"];
		$comment = $ps["comment$count"];
		// $iva = number_format($ps["taxes"][0]["percentage"],0,"","");
		// $iva = number_format($ps["taxTotal$count"][0]["percentage"],0,"","");
		// $iva = number_format($ps["taxes"][0]["percentage"],0,"","");
		
		$iva = number_format($iva_amount,0,"","");
		
		
		$naturaIva = '';
		
		if ($type == "FE") {
			$adb = PearDatabase::getInstance();
			$sql = "select fevatnature from vtiger_products where product_no = \"".$productid."\"";
			$res = $adb->pquery($sql, array());
			for ($i = 0; $i < $adb->num_rows($res); $i++) {
				$fevatnature = $adb->query_result($res, $i, "fevatnature");
				$naturaIva = explode(" -", $fevatnature)[0];
			}
			
		}
		
		if ($productid == null || $productid == "")
			continue;
		
		//if ($comment == "")
		//	$comment = "NESSUN COMMENTO";
		
		$righeDocumento[] = [
			'Descrizione' => $comment,
			'PrezzoUnitario' => number_format($price, 2, ".", ""),
			'Quantita' => $quantity,
			'AliquotaIVA' => $iva,
			'Natura' => $naturaIva,
			'CodiceArticolo' => [
				'CodiceTipo' => 'COD',
				'CodiceValore' => $productid
			]
		];
		
		$count++;
		
	}
	
	
	return [$datiDestinatario, $datiDocumento, $righeDocumento];
}

function getFolders() {

	$fieldvalue= array();

	$adb = PearDatabase::getInstance();
	$sql = "select foldername,folderid from vtiger_attachmentsfolder order by foldername";
        $res = $adb->pquery($sql, array());
        for ($i = 0; $i < $adb->num_rows($res); $i++) {
            $fid = $adb->query_result($res, $i, "folderid");
            $fname = $adb->query_result($res, $i, "foldername");
            $fieldvalue[$fid] =  $fname;
        }
        return $fieldvalue;
}

function save_file($doc, $fileName, $description = null) {
	try {
		$docsize = strlen($doc);
		$p = strrpos($fileName, '.');
		$ext = substr($fileName, $p+1);
		if ($ext == 'pdf') {
			$mimetype  = "application/pdf";
		} elseif ($ext == 'xml') {
			$mimetype  = "text/xml";
		} else {
			$mimetype = 'application/octet-stream'; //unknown
		}
		
		if ($description === NULL) {
			$description = $fileName;
		}
		
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$uploadPath = decideFilePath();

		$attachid = $db->getUniqueId('vtiger_crmentity');
		$binFile = sanitizeUploadFileName($fileName, vglobal('upload_badext'));
		$fileName = ltrim(basename(" ".$binFile));

		
		if (file_put_contents($uploadPath.$attachid."_".$fileName, $doc) > 0) {
			$date_var = $db->formatDate(date('YmdHis'), true);
			$usetime = $db->formatDate($date_var, true);

			$db->pquery("INSERT INTO vtiger_crmentity(crmid, smcreatorid, smownerid,
				modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				Array($attachid, $currentUserModel->getId(), $currentUserModel->getId(), $currentUserModel->getId(), "Documents Attachment", $description, $usetime, $usetime, 1, 0));

			

			echo "$mimetype";

			$db->pquery("INSERT INTO vtiger_attachments SET attachmentsid=?, name=?, description=?, type=?, path=?",
				Array($attachid, $fileName, $description, $mimetype, $uploadPath));

			$document = CRMEntity::getInstance("Documents");
			$document->column_fields['notes_title'] = $description;
			$document->column_fields['filename'] = $fileName;
			//$document->column_fields['notecontent'] = "DA CLI";
			$document->column_fields['filesize'] = $docsize; //filesize($fileName);
			$document->column_fields['filetype'] = $mimetype;
			$document->column_fields['fileversion'] = '';
			$document->column_fields['filestatus'] = 1;
			$document->column_fields['filelocationtype'] = 'I';
			$document->column_fields['folderid'] = 1; // Default Folder
			$document->column_fields['assigned_user_id'] = 1;
			$document->column_fields['filestatus'] = 1;
			$document->save('Documents');

			$db->pquery("INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)",
				Array($document->id, $attachid));
			return $document;

		}
		return null;
	} catch (Exception $e) {
		echo $e->getMessage();
	}
}
