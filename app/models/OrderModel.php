<?php

use Nette\Environment;

class OrderModel
{

	public static function isFree($id, $count) {
		$row = dibi::query('SELECT * FROM [projections] WHERE [id] = %i', $id)->fetch();
		return ($row['number_of_tickets'] >= $count);
	}


	public static function isPossibleToMakeThisOrder($email, $projectionId, $count) {

		$rows = dibi::query('SELECT * FROM [orders] WHERE [customer_email] = %s', $email)->fetchAll();

		foreach($rows as $row) {
			$basketArr = explode(',', $row['basket']);
			foreach($basketArr as $i => $id) {
				if($i % 2 == 0 && $id == $projectionId) 
					$count += $basketArr[$i+1];
			}
		}
		//dump("test", $projectionId, $count);
		return ($count <= Environment::getVariable('maxTicketForDay'));
	}


	public static function makeOrder($basket, $email, $price) {

		$commaSeparatedBasket = implode(",", $basket);
		$arr = array(
			'basket' => $commaSeparatedBasket,
			'customer_email' => $email,
			'price' => $price,
			'paid' => 0,
			'date'=> date('Y-m-d'),
			'time'=> date('H:i')
			);
		$namespace = Environment::getSession('basket');
		dibi::query('INSERT INTO [orders]', $arr);
		$namespace->pejsekId = dibi::insertId();
	}


	public static function decrementNumberTickets($id, $count) {
		
		$row = dibi::query('SELECT * FROM [projections] WHERE [id] = %i', $id)->fetch();
		if ($row) {
			$number = $row['number_of_tickets'] - $count;

			$arr = array('number_of_tickets' => $number);
			dibi::query('UPDATE [projections] SET ', $arr, 'WHERE [id] = %i', $id);
		}
	}
}
