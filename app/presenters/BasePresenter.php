<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */

use Nette\Environment;
/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\Presenter
{



	public function calculatePrice() {
		$price = 0;
 		$namespace = Environment::getSession('basket');
		if (is_array($namespace->ord))
			foreach($namespace->ord as $i => $ord) {
				if($i % 2 == 0) {
					$projectionI = HomepageModel::findProjectionInfo($ord);
					$price = $price + $projectionI['price'] * $namespace->ord[$i+1];					
				}
			}

		return $price;
	}

}
