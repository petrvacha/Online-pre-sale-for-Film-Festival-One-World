<?php

use Nette\Application\AppForm,
	Nette\Forms\Form,
	Nette\Environment,
	Nette\Mail\Mail;


class ProcessingPresenter extends BasePresenter
{

	public function actionGetresult($MerchantOrderId, $result) {
		if ($result != 0) {
			$userName = Environment::getVariable('userName');
			$Password = Environment::getVariable('Password');		
			$orderRow = ProcessingModel::getOrder($MerchantOrderId);
			if($orderRow && ProcessingModel::injectionControl($MerchantOrderId)) {
					
					// $paysecMapi = new SoapClient("https://testgateway.paysec.csob.cz/testgateway/shoppingservice.svc?wsdl");

					// https://mapi.paysec.cz/?wsdl TODO: STÁHNOUT A ULOŽIT
					//$paysecMapi = new SoapClient(LIBS_DIR.'/shoppingservice.svc');
					$paysecMapi = new SoapClient('https://mapi.paysec.cz/?wsdl');
					$resultCode = $paysecMapi->VerifyTransactionIsPaid($userName, $Password, $MerchantOrderId, $orderRow['price']);


					switch($resultCode) {
						case 0:
							ProcessingModel::changeOrderFlag($MerchantOrderId);
							$ticketTextPage = $this->sendTicket($MerchantOrderId, $orderRow);
							$this->emptyBasket();
							$this->flashMessage('Platba prostřednictvím systému PaySec proběhla úspěšně. Děkujeme za Vaši objednávku a zájem o festival Jeden svět. Kód vstupenky najdete ve Vaší emalové schránce.');
							$namespace = Environment::getSession('basket');
							$namespace->tickets = $ticketTextPage;
			
							$this->redirect('homepage:');
							break;
						case 1:
							$this->flashMessage('Platbu se nepodařilo zrealizovat.<br>Váš pokyn k zamítnutí platby byl proveden úspěšně.');
							break;
						case 2:
							$this->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.2');
							break;
						case 3:
							$this->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.3');
							break;
						case 4:
							$this->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.4');
							break;
						case 5:
							$this->flashMessage('Platbu se nepodařilo zrealizovat.');
							break;
						case 6:
							$this->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.6');
							break;
						case 7:
							$this->flashMessage('');
							break;
						default:
							$this->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.D');
							break;
					}
				}
			else
				$this->flashMessage('Platba byla zamítnuta!');
			}
			$this->emptyBasket();
			$this->redirect('homepage:');
	}


	public function actionCancelurl($MerchantOrderId) {
			ProcessingModel::cancelOrder($MerchantOrderId);
			$this->flashMessage('Váš pokyn k zamítnutí platby byl proveden úspěšně. Pokud budete chtít objednat vstupenky, vložte je znovu do košíku.');	
			$this->emptyBasket();
			$this->redirect('homepage:');
	}

	private function emptyBasket() {
		$namespace = Environment::getSession('basket');
		$namespace->ord = array();
	}


	private function sendTicket($MerchantOrderId, $orderRow) {

		$basket = explode(',',$orderRow['basket']);
		$email = Environment::getVariable('email');
		$idOrder = $MerchantOrderId;

		$ticketText = "";
		$ticketTextPage = array();


		if(count($basket)>2 || $basket[1] > 1)
			$ticketText .= "Vážený diváku,\nděkujeme za Vaši objednávku a zájem o festival Jeden svět.\n\nSeznam zakoupených vstupenek:\n";
		else
			$ticketText .= "Vážený diváku,\nděkujeme za Vaši objednávku a zájem o festival Jeden svět.\n\nVaše vstupenka:\n";
		foreach($basket as $i => $b) {
			if ($i % 2 == 0) {
				$projection = HomepageModel::findProjectionInfo($b);
				

				for($c=0; $c<$basket[$i+1]; $c++) {

					$hash = $this->makeFreeHash();
					$tmp = $projection['title'].", kód vstupenky: ".$hash."\n".date("j. n. Y",strtotime($projection['date'])).", "
													.date("H:i",strtotime($projection['time'])).", ".$projection['place']."\n\n";
					$ticketTextPage[] = $tmp;
					$ticketText .= $tmp;

					ProcessingModel::insertTicket($hash, $idOrder, $projection['id']);
					
				}
					
			}
		}
		$ticketText .= "Kód vstupenky si vytiskněte nebo opište, před vstupem do promítacího sálu Vás vyzveme k předložení kódu. \n\nS přáním pěkného dne, organizační tým festivalu Jeden svět\n\nInformace o festivalu na http://www.jedensvet.cz/brno\nInternetový předprodej vstupenek na http://vstupenkybrno.jedensvet.cz";


		$mail = new Mail;
		$mail->setFrom($email, 'Jeden svět Brno');
		$mail->addTo($orderRow['customer_email']);
		$mail->setSubject('Objednávka vstupenek');
		$mail->setBody($ticketText);
		$mail->send();

		return $ticketTextPage;
	}


	private function makeFreeHash() {

		$busyHash = True;
		while($busyHash) {
			$hash = substr(sha1(time()+'Jeden svět'),0,7);
			$busyHash = dibi::query('SELECT * FROM [tickets] WHERE [order_hash] = %s', $hash)->fetch();
		}
		return $hash;
	}


}
