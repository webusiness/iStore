<?php
/* 	CONEXION A LA BASE DE DATOS ****************************************************************************************** */
/*	Trae los datos de la base de datos para la conexion y los almacena en un array $datosConexion	*/
include_once('../../wp-config.php');
$conexion = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("No se pudo realizar la conexion");
define('CONEXION', $conexion);
// mysql_select_db(DB_NAME, $conexion) or die("ERROR con la base de datos");

/* 	Cerrar conexion de DB	 */
// mysql_close($conexion);
/* 	LLEVAR ESTO A ARCHIVO QUE CIERRA LA CONEXIÓN!!	 */
?>