<?php


class HomepageModel
{
	public static function findAllMovies() {

		return dibi::query('SELECT * FROM [movies] ORDER BY [title]')->fetchAll();
	}


	public static function findProjectionByMovieId($id) {
		
		return dibi::query('SELECT * FROM [projections] WHERE [id_movie] = %i', $id)->fetch();
	}

	public static function findPlaceById($id) {
		$row = dibi::query('SELECT * FROM [projection_places] LEFT JOIN [projections] ON (projection_places.[id] = projections.[id_projection_place])
					 WHERE projections.[id] = %i', $id)->fetch();
		return $row['place'];
	}


	public static function findAllProjection() {
		return dibi::query('SELECT * FROM [projections] ORDER BY [date], [time]')->fetchAll();
		
	}


	public static function findProjectionInfo($id) {
		return dibi::query('SELECT projections.[id], projections.[date], projections.[time], projections.[price], 
				projections.[number_of_tickets] AS tickets, projections.[locked],
				projection_places.[place], projection_places.[info] AS placeinfo,
				movies.[title], movies.[info] AS movieinfo, movies.[photo]
				FROM [projections]
				LEFT JOIN [projection_places] ON (projections.[id_projection_place] = projection_places.[id])
				LEFT JOIN [movies] ON (projections.[id_movie] = movies.[id])
		 		WHERE projections.[id] = %i', $id)->fetch();
	}

	
	public static function getCountOfFreeTickets($id) {
		$row = dibi::query('SELECT [number_of_tickets] FROM [projections] WHERE [id] = %i', $id)->fetch();	
		return (int) $row->number_of_tickets;
	}


	public static function setLocked($id) {
		
		dibi::query('UPDATE [projections] SET', array('locked' => 1), 'WHERE [id] = %i', $id);
	}


}
