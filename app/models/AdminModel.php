<?php



class AdminModel
{

	public static function getProjections() {

		return dibi::query('SELECT projections.[id] AS id, projections.[date] AS date, 
					projections.[time] AS time, movies.[title] AS title, projection_places.[place] AS place
					FROM [projections] 
					LEFT JOIN [movies] ON (projections.[id_movie] = movies.[id])
					LEFT JOIN [projection_places] ON (projections.[id_projection_place] = projection_places.[id])')->fetchAll();
	}


	public static function getTicketsByIdProjection($IdProjection) {

		return dibi::query('SELECT tickets.[id] AS id, tickets.[order_hash] AS hash, movies.[title] AS title, projection_places.[place] AS place,
 					projections.[date] AS date, projections.[time] AS time
					FROM [tickets] 
					LEFT JOIN [orders] ON (tickets.[id_order] = orders.[id])
					LEFT JOIN [projections] ON (tickets.[id_projection] = projections.[id])
					LEFT JOIN [movies] ON (projections.[id_movie] = movies.[id])
					LEFT JOIN [projection_places] ON (projections.[id_projection_place] = projection_places.[id])
				WHERE tickets.[id_projection] = %i', $IdProjection)->fetchAll();
	}



}
