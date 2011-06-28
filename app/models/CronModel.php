<?php


class CronModel 
{

	/*
	 * Deletes all 15 minutes old unpaid orders
	 */
	public static function start() {

		$rows = dibi::query('SELECT * FROM [orders] WHERE [paid] = 0')->fetchAll();

		foreach($rows as $row) {
			$dtime = strtotime($row['time']) - strtotime('-15 minutes');
			$ddate = strtotime($row['date']) - strtotime(date("j-n-Y")); 
			if (($ddate == 0 && ($dtime > 900 || $dtime < 0)) || $ddate != 0) {
				$expBasket = explode(',',$row['basket']);
				foreach($expBasket as $i => $id) {
					if($i % 2 == 0) {
						$r = dibi::query('SELECT * FROM [projections] WHERE [id] = %i', $id)->fetch();
						$numberOfTickets = $r['number_of_tickets'] + $expBasket[$i+1];
						$arr = array( 'number_of_tickets' => $numberOfTickets);
						dibi::query('UPDATE [projections] SET', $arr, 'WHERE [id] = %i', $id);
					}
				}
				
				$arr = array('oldId' => $row['id'],
						'basket' => $row['basket'],
						'customer_email' =>$row['customer_email'],
						'price' =>$row['price'],
						'paid' =>$row['paid'],
						'date' =>$row['date'],
						'time' =>$row['time']);

				dibi::query('INSERT INTO [cronDeletes]',$arr);
				dibi::query('DELETE FROM [orders] WHERE [id] = %i', $row['id']);
			}
		}
	
	}
}
