<?php


use Nette\Application\AppForm,
		Nette\Forms\Form,
		Nette\Environment;

class BasketPresenter extends BasePresenter
{


	public function renderDefault() {
		$projectionArray = array();
		$this->template->proInfo = array();
		$namespace = Environment::getSession('basket');

		if (is_array($namespace->ord))
			$this->template->basket = $namespace->ord;
		else
			$this->template->basket = array();

		foreach($this->template->basket as $num => $ord) {
			if ($num % 2 == 0) {//date("j. n. Y",strtotime($p->date))
				$row = HomepageModel::findProjectionInfo($ord);
				$row['date'] = date("j. n. Y",strtotime($row['date']));
				$row['time'] = date("H:i",strtotime($row['time']));
				$this->template->proInfo[] = $row;
				$this->template->proInfo[] = $this->template->basket[$num+1];
			}
		}
	

		$this->template->price = $this->calculatePrice();
	}


	protected function createComponentForm() {
		$form = new AppForm;
		$form->addText('email', 'Email:')->setEmptyValue('@')->addRule($form::FILLED, 'Vyplňte svůj email')->addRule($form::EMAIL, 'Vyplňte platný email');
		//->addRule(callback($this, 'isEmailAvailable'), 'Tento e-mail je již registrován');
		$form->addSubmit('pay', 'Objednat');		
		$form->onSubmit[] = callback($this, 'orderFormSubmitted');
		return $form;
	}


	public function orderFormSubmitted($form) { 
		if ($form['pay']->isSubmittedBy()) {
			$namespace = Environment::getSession('basket');
			$resultOfMakeOrder = $this->makeOrder($namespace->ord, $form->values['email'], $this->calculatePrice());
			if ($resultOfMakeOrder == 'OK') {
				$this->redirect("Order:");
			}				
			elseif ($resultOfMakeOrder == 'FULL') 
				$this->flashMessage('Vážený diváku, omlouváme se, ale Vámi požadovaný počet vstupenek již není k dispozici. Prosíme, zkuste je objednat znovu v aktuálně dostupném počtu. Děkujeme za pochopení.');
			else
				$this->flashMessage('Omlouváme se, ale na jednu projekci si smíte zakoupit maximálně 5 lístků.');

			$this->emptyBasket();
			$this->redirect('Homepage:');
		}

 	}


	private function makeOrder($basket, $email, $price) {

		$want = 0;
		foreach($basket as $i => $id) {
			if($i % 2 == 0)
				if(!OrderModel::isPossibleToMakeThisOrder($email, $basket[$i], $basket[$i+1]))
					return 'NOOPTIONS';
		}
		

		foreach($basket as $i => $id) {
			if($i % 2 == 0)
				if (!OrderModel::isFree($basket[$i], $basket[$i+1]))
					return 'FULL';
		}

		foreach($basket as $i => $id) {
			if($i % 2 == 0)
				OrderModel::decrementNumberTickets($basket[$i], $basket[$i+1]);
		}

		OrderModel::makeOrder($basket, $email, $price);
		return "OK";
	}


	private function emptyBasket() {
		$namespace = Environment::getSession('basket');
		$namespace->ord = array();
	}

	/*
	 * @deprecated
	 */
	public function isEmailAvailable($email) {
		$row = dibi::query("SELECT * FROM [orders] WHERE [customer_email] = %s", $email->value)->fetch();
		if ($row)
			return False;
		else
			return True;
	}


	public function actionDeleteOrder($id) {
		$namespace = Environment::getSession('basket');
		if (is_array($namespace->ord))
			foreach($namespace->ord as $i => $ord) {
				if($id == $ord and $i % 2 == 0) {
					unset($namespace->ord[$i]);
					unset($namespace->ord[$i+1]);
				}
			}
		$this->redirect('Basket:');
	}




}
