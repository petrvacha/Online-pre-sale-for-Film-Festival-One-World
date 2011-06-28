<?php




class ProcessingModel
{

	public static function getOrder($id) {

		return dibi::query('SELECT * FROM [orders] WHERE [id] = %i', $id)->fetch();
	}
	

	public static function changeOrderFlag($MerchantOrderId) {

		$arr = array('paid' => 1);

		dibi::query('UPDATE [orders] SET', $arr, 'WHERE [id] = %i', $MerchantOrderId);
	}


	public static function insertTicket($hash, $idOrder, $projection) {

		$arr = array('order_hash' => $hash, 'id_order' => $idOrder, 'id_projection' => $projection);
		dibi::query('INSERT INTO [tickets]', $arr);
	}


	public static function cancelOrder($MerchantOrderId) {
		//noACID awww :`(
		$orderRow = dibi::query('SELECT * FROM [orders] WHERE [id] = %i', $MerchantOrderId)->fetch();
		if($orderRow) {
			$arrOrder = explode(',', $orderRow['basket']);
			foreach($arrOrder as $i => $value) {
				if($i % 2 == 0) {
					
					$projectionRow = dibi::query('SELECT * FROM [projections] WHERE [id] = %i', $value)->fetch();
					$newNumberOfTickets = $projectionRow['number_of_tickets'] + $arrOrder[$i+1];
					$arr = array('number_of_tickets' => $newNumberOfTickets);
					dibi::query('UPDATE [projections] SET', $arr, 'WHERE [id] = %i', $value);
					
				}
			}
			dibi::query('DELETE FROM [orders] WHERE [id] = %i', $MerchantOrderId);
			
		}
	
	}


	public static function injectionControl($idOrder) {

		if(dibi::query('SELECT * FROM [tickets] WHERE [id_order] = %i', $idOrder)->fetch())
			return False;
		else
			return True;
	}

}
