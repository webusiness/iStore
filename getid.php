<?php
	//CONSULTA **********************************************************************************************************

	function getID(){
		#Busca el ultimo ID y le suma 1, este sera el nombre de la nueva imagen: imagen+ID
		$consulta_ID = "SELECT ID as ultimo_id FROM wp_posts ORDER BY ID DESC LIMIT 1";
		$respuesta_ID = mysql_query($consulta_ID, CONEXION);
		if (!$respuesta_ID) {
		    echo "No se pudo ejecutar con exito la consulta ($consulta_ID) en la BD: " . mysql_error();
		    exit;
		}
		if (mysql_num_rows($respuesta_ID) == 0) {
		    echo "No se han encontrado filas";
		    exit;
		}
		while ($fila = mysql_fetch_assoc($respuesta_ID)) {
		$ultimo_id = $fila["ultimo_id"];
		$ultimo_id = $ultimo_id + 1;
		}
		return $ultimo_id;
	}
?>