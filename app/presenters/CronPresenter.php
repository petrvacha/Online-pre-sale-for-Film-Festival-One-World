<?php

class CronPresenter extends BasePresenter
{



	public function actionStart() {
		if (CronModel::start())
			
			$this->template->status = "Nezaplacené lísky byly smazány.";
		else
			$this->template->status = "Nastala chyba!";

		dump($this->template->status);die;

	}


	public function renderStart() {
	//$this->template->status = "Nastala chyba!";
	}
}

