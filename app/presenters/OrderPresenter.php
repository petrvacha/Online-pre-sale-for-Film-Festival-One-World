<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */


/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */


use Nette\Application\AppForm,
	Nette\Forms\Form,
	Nette\Environment;

class OrderPresenter extends BasePresenter
{

	public function renderDefault()  {
		/*$projectionArray = array();
		
		$movies = HomepageModel::findAllMovies();
		$projection = HomepageModel::findAllProjection();

		$namespace = Environment::getSession('basket');
		//dump($namespace->ord);die;
		if (is_array($namespace->ord))
			$this->template->basket = $namespace->ord;
		else
			$this->template->basket = array();

		foreach($projection as $p) {
			$projectionArray[$p->id] = array();
			foreach($movies as $m) {
				if ($m->id == $p->id_movie) {
					$projectionArray[$p->id]['title'] = $m->title;
					$projectionArray[$p->id]['photo'] = $m->photo;
					$projectionArray[$p->id]['info'] = $m->info;
				}
			}
			$projectionArray[$p->id]['place'] = HomepageModel::findPlaceById($p->id);
			$projectionArray[$p->id]['time'] = $p->time;
			$projectionArray[$p->id]['date'] = $p->date;
			$projectionArray[$p->id]['tickets'] = $p->number_of_tickets;
			$projectionArray[$p->id]['price'] = $p->price;
		}
	


		$this->template->movies = $movies;
		$this->template->projectionArray = $projectionArray;*/
	}


	protected function createComponentForm($name) {
		
		$PaySecAction = Environment::getVariable('PaySecAction');
		$MicroaccountNumber = Environment::getVariable('MicroaccountNumber');
		$Amount = $this->calculatePrice();
		$namespace = Environment::getSession('basket');
		$MerchantOrderId = $namespace->pejsekId;
		$MessageForTarget = $this->getPrettyOrder();


		$domain = Environment::getVariable('domain');

		$BackURL = $domain."processing/getResult/?MerchantOrderId=".$MerchantOrderId."&result={0}";
		$CancelURL = $domain."processing/cancelurl/?MerchantOrderId=".$MerchantOrderId;

		$form = new AppForm($this, $name);
		//$form->setMethod('post'); WTF! "Nette\Forms\Form::setMethod() must be called until the form is empty."
		$form->setAction($PaySecAction);
		$form->addSubmit('pay', 'Zaplatit');
		$form->addHidden('MicroaccountNumber')->setValue($MicroaccountNumber); // číslo konta I obchodu
		$form->addHidden('Amount')->setValue($Amount); //cena
		$form->addHidden('MerchantOrderId')->setValue($MerchantOrderId); //idéčko v Order
		$form->addHidden('MessageForTarget')->setValue($MessageForTarget); //Obsah košíku
		$form->addHidden('BackURL')->setValue($BackURL); // http://evstupenky.freexit.eu/new/www/processing/getResult/{0} {0} bude nahrazeno idéčkem z Order
																										 // 0 znamená FAIL
		$form->addHidden('CancelURL')->setValue($CancelURL); // stejné jako horní result s 0

		return $form;
	}

	protected function createComponentCancelform($name) {
		$form = new AppForm($this, $name);
		$form->addSubmit('cancel', 'Zrušit objednávku');
		$form->onSubmit[] = callback($this, 'cancelFormSubmitted');
		return $form;
	}

	public function cancelFormSubmitted(AppForm $form) {
		$namespace = Environment::getSession('basket');
		$MerchantOrderId = $namespace->pejsekId;
		if ($form['cancel']->isSubmittedBy()) {
			ProcessingModel::cancelOrder($MerchantOrderId);
			$this->flashMessage('Váš pokyn k zamítnutí platby byl proveden úspěšně.');	
			$this->emptyBasket();
			$this->redirect('homepage:');
			
		}
	}

	private function getPrettyOrder() {
		$info = array();
		$text = '';
 		$namespace = Environment::getSession('basket');

		foreach($namespace->ord as $num => $ord) {
			if ($num % 2 == 0) {
				$info[] = HomepageModel::findProjectionInfo($ord);
				$info[] = $namespace->ord[$num+1];
			}
		}


	$arrayLenght = count($info);
	foreach($info as $i => $ord) {
		if($i % 2 == 0) {
			$text .= $ord['title'].", ".date('j. n. Y',strtotime($ord['date'])).", ".date('H:i',strtotime($ord['time'])).", ".$ord['place']." ".$info[$i+1]."x";
			if ($i+2 < $arrayLenght)
				$text .= "\n";
		}
	}

	return $text;
		
	}

	private function emptyBasket() {
		$namespace = Environment::getSession('basket');
		$namespace->ord = array();
	}


}
