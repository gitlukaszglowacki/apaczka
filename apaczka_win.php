<?php

include('apaczkaSoapClient.php');

date_default_timezone_set('Europe/Warsaw');

$apaczka = new apaczkaApi('email@gmail.com', '', '');
$apaczka->setVerboseMode();
//$apaczka->setTestMode();
$apaczka->setProductionMode();

if (!$resp = $apaczka->validateAuthData()){
	var_dump($resp);
	die('validateAuthData ERROR'."\n");
}

$pliki = array_diff(scandir("C:\\apaczka\\pliki\\"), array('.', '..'));

foreach ($pliki as &$plik){
	$klienci = file("C:\\apaczka\\pliki\\" . $plik, FILE_SKIP_EMPTY_LINES);

	foreach ($klienci as &$klient){
		$order = new ApaczkaOrder();

		$kawalki = explode(";", $klient);
		$Odb_Nazwa = $kawalki[7];
		$Odb_Adres = $kawalki[8];
		$Odb_KodPocztowy = $kawalki[9];
		$Odb_Miasto = $kawalki[10];
		$Odb_Telefon = $kawalki[11];
		$Odb_Email = $kawalki[12];
		$Odb_Osoba_Kontakt = $kawalki[13];
		$Odb_Nick = $kawalki[14];
		$Nr_Paczki = $kawalki[15];
		$KwotaPobrania = $kawalki[16];
		$Waluta = $kawalki[17];
		$LiczbaPaczek = $kawalki[18];
		$WagiPaczki = $kawalki[19];
		$DlugosciPaczki = $kawalki[20];
		$SzerokosciPaczki =$kawalki[21];
		$WysokosciPaczki =$kawalki[22];
		$Zawartosc =$kawalki[23];

		if ($LiczbaPaczek > 0){
			$order->notificationDelivered = $order->createNotification(false, false, true, false);
			$order->notificationException = $order->createNotification(false, false, true, false);
			$order->notificationNew = $order->createNotification(false, false, true, false);
			$order->notificationSent = $order->createNotification(false, false, true, false);
			$order->contents = $Zawartosc;
			$order->setServiceCode('DPD_CLASSIC');
			$order->referenceNumber = $Nr_Paczki;
			$order->setReceiverAddress($Odb_Nazwa, $Odb_Osoba_Kontakt, $Odb_Adres, '.', $Odb_Miasto, '0', $Odb_KodPocztowy, '', $Odb_Email, $Odb_Telefon);
			$order->setSenderAddress('', '', '', '.', '', '', '', '', '', '');
			$order->setPobranie('46114020170000440205125010', $KwotaPobrania);
			$WagaPaczki = explode("|", $WagiPaczki);
			$DlugoscPaczki = explode("|", $DlugosciPaczki);
			$SzerokoscPaczki = explode("|", $SzerokosciPaczki);
			$WysokoscPaczki = explode("|", $WysokosciPaczki);
			print_r("Zlecenie: " . $Nr_Paczki . "Liczba paczek: " . $LiczbaPaczek . "\n");

			for ($i = 0; $i < $LiczbaPaczek; $i++){
				$order_shipment = new ApaczkaOrderShipment('PACZ', $DlugoscPaczki[$i], $SzerokoscPaczki[$i], $WysokoscPaczki[$i], $WagaPaczki[$i]);
				$order->addShipment($order_shipment);
			}
			$order->createShipment();
			//var_dump($order);
			$resp = $apaczka->placeOrder($order);

			if($resp !== false && $resp->return->order){
				$orderId = $resp->return->order->id;
				print_r("Zlecenie: " . $Nr_Paczki . "wyslane, nadany numer: " . $orderId . "\n");
			}else{
				//print_r($order);
				//var_dump($resp);
				die('Błąd podczas wysylania zamowienia'."\n");
			}

			//r_export($order);
			unset($order);
		}
	}
	print_r("Przenosze plik: " . $plik . " do katalogu wyslane" . "\n");
	rename("C:\\apaczka\\pliki\\" . $plik, "C:\\apaczka\\wyslane\\" . $plik);
}

unset($apaczka);

