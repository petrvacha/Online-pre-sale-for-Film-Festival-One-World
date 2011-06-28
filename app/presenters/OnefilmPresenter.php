<?php


use Nette\Application\AppForm,
	Nette\Forms\Form,
	Nette\Environment;

class OnefilmPresenter extends BasePresenter
{

	public function actionDefault($id) {
		$this->template->idProjection = $id;
		$this->template->countTicket = HomepageModel::getCountOfFreeTickets($this->template->idProjection);
	}



	public function renderDefault() {
		$projection = HomepageModel::findProjectionInfo($this->template->idProjection);
		$projection['date'] = date("j. n. Y",strtotime($projection->date));
		$projection['time'] = date("H:i",strtotime($projection->time));
		$this->template->projection = $projection;
		//$this->template->proInfo = array();
		$namespace = Environment::getSession('basket');
		if (is_array($namespace->ord))
			$this->template->basket = $namespace->ord;
		else
			$this->template->basket = array();

		if (!Environment::getVariable('permitSale') || $projection->locked)
			$this->template->permitSale = False;
		else
			$this->template->permitSale = True;
	}

	
	protected function createComponentForm() {
		$freeTickets = $this->template->countTicket;
		$freeTs = array();
		if ($freeTickets >= 5)
			$freeTs = array(1,2,3,4,5);
		else if ($freeTickets >= 1) {
			for($i=1; $i<= $freeTickets; $i++)
				$freeTs[] = $i;
		}
			
		
		$form = new AppForm;
		$form->addSelect('tickets', 'Vložit do košíku: ', $freeTs);
		$form->addSubmit('order', 'Vložit');		
		$form->onSubmit[] = callback($this, 'ticketFormSubmitted');
		return $form;
	}


	public function ticketFormSubmitted(AppForm $form) {

		if ($form['order']->isSubmittedBy()) {
			$values = $form->getValues();

			$namespace = Environment::getSession('basket');	
			if (!is_array($namespace->ord))
				$namespace->ord = array();


			$change = False;
			$arr = $namespace->ord;
			foreach($arr as $i => $o) {
				if ($i % 2 == 0 && $this->template->idProjection == $o) {
					//$tmp = $namespace->ord[$i+1];
					$namespace->ord[$i+1] = $values['tickets']+1;
					/*if(!$this->isPossibleAddTickets()) {
					$namespace->ord[$i+1] = $tmp;
					$this->flashMessage('Vstupenka nebyla vložena do košíku. Můžete si objednat maximálně 5 lístků.');
					$this->redirect('homepage:');
					}*/
					$change = True;
				}
			}
			if (!$change) {
				$namespace->ord[] =(int) $this->template->idProjection;
				$namespace->ord[] = $values['tickets']+1;
				/*if(!$this->isPossibleAddTickets()) {
						array_pop($namespace->ord);
						array_pop($namespace->ord);
						$this->flashMessage('Vstupenka nebyla vložena do košíku. Můžete si objednat maximálně 5 lístků.');
						$this->redirect('homepage:');
					}*/
			}
		}
		$namespace->basket = True;
		$this->flashMessage('Vstupenka byla vložena do košíku.');
		$this->redirect('homepage:');
		
	}

	/*
	 * @deprecated
	 */ 
	public function isPossibleAddTickets() {
		$namespace = Environment::getSession('basket');
		if (!is_array($namespace->ord))
			return True;
		$count = 0;
		foreach($namespace->ord as $i => $o) {
			if ($i % 2 == 1)
				$count += $namespace->ord[$i];
		}
		return($count <= Environment::getVariable('maxTicketForDay'));

	}
}
