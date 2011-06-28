<?php


use Nette\Application\AppForm,
		Nette\Forms\Form,
		Nette\Environment, 
		Nette\Security\AuthenticationException;

class AdminPresenter extends BasePresenter
{


	public function renderDefault() {
		//phpinfo();die;
		$user = Environment::getUser();
//		$user->logout();

		if ($user->isLoggedIn()) {
				$this->redirect('Admin:logged');
		} else {
				$this->template->login = False;
				$this->template->text = '';
		}

	}




	protected function createComponentTicketsSelector() {
	
		$projections = AdminModel::getProjections();
		

		$selectArr= array();

		foreach($projections as $projection) {
			if (strtotime($projection['date']) >= strtotime(date('Y-m-d')))
				$prefix = '♥';
			else
				$prefix = '†';

			$selectArr[$projection['id']] = $prefix.' '.date("j. n. Y",strtotime($projection['date'])).' '.date("H:i",strtotime($projection['time'])).' '.$projection['title'].' '.$projection['place'];
		}


		$form = new AppForm;
		$form->addSelect('select', 'Projekce: ',$selectArr);
		$form->addSubmit('submit', 'Vybrat projekci');
		$form->onSubmit[] = callback($this, 'ticketsSelectorSubmitted');
		return $form;
	}

	public function ticketsSelectorSubmitted($form) {
		$selected = $form->values['select'];
		$this->redirect('Admin:tickets', $selected);
	
	}


	public function renderLogged() {
		$user = Environment::getUser();
		
		if ($user->isLoggedIn()) {
				$this->template->login = True;
				$this->template->text = 'Jste přihlášen jako: '. $user->getIdentity()->getId();
		} else {
				$this->template->login = False;
				$this->flashMessage('Byl jste odhlášen.');
				$this->redirect('Admin:');
		}		
	}

	public function renderTickets($id) {
		$user = Environment::getUser();
		$this->template->tickets = NULL;

		if ($user->isLoggedIn()) {
				$this->template->login = True;
				$this->template->text = 'Jste přihlášen jako: '. $user->getIdentity()->getId();

				$rows = AdminModel::getTicketsByIdProjection($id);
				$this->template->tickets = $rows;


		} else {
				$this->template->login = False;
				$this->flashMessage('Byl jste odhlášen.');
				$this->redirect('Admin:');
		}		
	}

	protected function createComponentForm() {
		$form = new AppForm;
		$form->addText('user', 'Přihlašovací jméno:')->addRule(Form::FILLED, 'Zadejte přihlašovací jméno.');
		$form->addPassword('password', 'Heslo:')->addRule(Form::FILLED, 'Zadejte heslo.');
		$form->addSubmit('login', 'Přihlásit se');
		$form->onSubmit[] = callback($this, 'loginFormSubmitted');
		return $form;
	}


	public function loginFormSubmitted($form) { 
		if ($form['login']->isSubmittedBy()) {
			$namespace = Environment::getSession('basket');

			$username = $form->values['user'];
			$password = $form->values['password'];

			$user = Environment::getUser();

			// nastavíme expiraci
			$user->setExpiration('+ 25 minutes');

			try {
					// pokusíme se přihlásit uživatele...
					$user->login($username, $password);
					// ...a v případě úspěchu presměrujeme na další stránku
					$this->redirect('Admin:logged');

			} catch (AuthenticationException $e) {
					$this->flashMessage('Chyba: Nesprávné přihlašovací údaje.');
				}
		}

 	}


	public function actionLogout() {
		$user = Environment::getUser();
		$user->logout();
		$this->flashMessage('Byl jste úspěšně odhlášen.');
		$this->redirect('Homepage:');
	}


}
