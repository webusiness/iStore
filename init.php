<?php
/*	URL del xml que contiene los datos 										*/
define('ARCHIVO', '../xml/Productos.xml', true);
/*	****************************************************************		*/

/*	Script que carga la conexión a BD										*/
define('GET_DB', 'dbconexion.php', true);
require_once(GET_DB);
/*	****************************************************************		*/

/*	Script que Obtiene los ID's 											*/
define('GET_ID', 'getid.php', true);
require_once(GET_ID);
/*	****************************************************************		*/

/*	Script que carga los datos 												*/
define('GET_DATA', 'getdata_v3.php', true);
require_once(GET_DATA);
/*	****************************************************************		*/

/*	Script que carga el QUERY 												*/
define('GET_QUERY', 'query.php', true);
require_once(GET_QUERY);
/*	****************************************************************		*/
?>