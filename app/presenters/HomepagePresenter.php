<?php


use Nette\Application\AppForm,
		Nette\Forms\Form,
		Nette\Environment;

	define("TICKETCOUNT", 10.0);

class HomepagePresenter extends BasePresenter
{

	public function actionDefault($page) {
		$this->template->page = $page;
	}

	public function renderDefault() {
		$projectionArray = array();
		$this->template->proInfo = array();
		$movies = HomepageModel::findAllMovies();
		$projection = HomepageModel::findAllProjection();

		$time = time() - 86400;

		foreach($projection as $k => $p) {
			if ($p['locked'] == 1 || $time > strtotime($p['end_of_presale']))
				unset($projection[$k]);


			if ($p['locked'] == 0 && $time > strtotime($p['end_of_presale']))
				HomepageModel::setLocked($p['id']);
		}

			

		$this->template->pages = (int) ceil(count($projection)/TICKETCOUNT);

		$namespace = Environment::getSession('basket');
		if (is_array($namespace->tickets))
			$this->template->tickets = $namespace->tickets;
		else
			$this->template->tickets = array();

		$namespace->tickets = array();
			
		if (is_array($namespace->ord))
			$this->template->basket = $namespace->ord;
		else
			$this->template->basket = array();

		foreach($this->template->basket as $num => $ord) {
			if ($num % 2 == 0) {
				$this->template->proInfo[] = HomepageModel::findProjectionInfo($ord);
				$this->template->proInfo[] = $this->template->basket[$num+1];
			}
		}
		

		$page = $this->template->page;
		if ($page == NULL || $page == 0 || $page > $this->template->pages)
			$this->template->page = $page = 1;


		if(isset($namespace->basket) && ($namespace->basket == True)) {
			$this->template->basket = True;
			unset($namespace->basket);
		}
		else
			$this->template->basket = False;		

		$i = 1;
		
		foreach($projection as $p) {
			
			if($p->locked == 1 || $time > strtotime($p['end_of_presale']))
				continue;

			if($i <= TICKETCOUNT*$page - TICKETCOUNT) {
				$i++;
				continue;
			}
			elseif($i > TICKETCOUNT*$page)
				break;
			$i++;
			
		


			$projectionArray[$p->id] = array();
			foreach($movies as $m) {
				if ($m->id == $p->id_movie) {
					$projectionArray[$p->id]['title'] = $m->title;
					$projectionArray[$p->id]['photo'] = $m->photo;
					$projectionArray[$p->id]['info'] = $m->info;
				}
			}
			$projectionArray[$p->id]['place'] = HomepageModel::findPlaceById($p->id);
			$projectionArray[$p->id]['time'] = date("H:i",strtotime($p->time));
			$projectionArray[$p->id]['date'] = date("j. n. Y",strtotime($p->date));
			$projectionArray[$p->id]['tickets'] = $p->number_of_tickets;
			$projectionArray[$p->id]['price'] = $p->price;
		}
	

		$this->template->movies = $movies;
		$this->template->projectionArray = $projectionArray;
	}




}
