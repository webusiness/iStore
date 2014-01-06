<?php
/*
*	@Uso: Carga de Datos XML para productos iStore Pro
* 	@Version: Beta 3.0
*/

//LEE EL XML **********************************************************************************************************
//---------------------- LECTURA DEL XML ------------------------------------------------------------------------------
/*	Declaración de formatos de codificación antes de traer los datos 		*/
mb_internal_encoding('UTF-8');
stream_filter_register('xmlutf8', 'ValidUTF8XMLFilter');
/*	****************************************************************		*/

/*	Revisión de la existencia del archivo antes de ejecutar cualquier cosa	*/
if (!file_exists(ARCHIVO)) {
    echo "<strong>El archivo XML no se ha encontrado</strong>";
} else {
/*	Si el archivo es encontrado, entonces procede a obtener los datos 		*/

/*	Se utiliza el file_get_contents con códificación UTF-8 para obtener
	todos el XML en un sólo string, para hacer la validación de caracteres	*/
	$xml = utf8_decode(file_get_contents(ARCHIVO));
/*	****************************************************************		*/

/*	Filtros para limpiar el archivo XML de caracteres no válidos	****	*/
	$xml = preg_replace('~\s*(<([^>]*)>[^<]*</\2>|<[^>]*>)\s*~','$1',$xml);
	$xml = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $xml);

/*	Se validan los signos &		****************************************	*/
	$xml = preg_replace('/&/', '&amp;', $xml);
/*	****************************************************************	*/

/*	En esta línea se convierten los datos de UTF-8 a ISO-8859-1 para obtener
	lectura de las comillas	utilizadas para determinar las Pulgadas en
	algunas características y nombres de productos 	********************	*/
	$xml = mb_convert_encoding($xml, 'UTF-8', 'ISO-8859-1');
/*	****************************************************************		*/
/*	Teniendo la variable ya limpia y codificada, se utiliza esta función
	para convertir el string completo a un Objeto XML.

	Se utiliza LIBXML_NOCDATA como argumento, para que en caso de que el
	XML traiga CDATA, estos no se pierdan 									*/
	$xml = @simplexml_load_string(utf8_decode($xml),'SimpleXMLElement', LIBXML_NOCDATA);
/*	****************************************************************		*/

	/* 	Función preparada para armar la galería con el array que contiene las URL de las fotos	*/
	function armarGaleria($fotos){
		$totales 							= count($fotos);
		for($i = 0; $i < $totales; $i++){
			echo urldecode($fotos[$i]);
		}
	}

	/*	Función que devuelve el último ID 		********************************************	*/
//	echo getID();
	/*	************************************************************************************	*/
	$nuevoproducto							= array();
	$editarproducto 						= array();

	function todosProductos($producto){
		global $nuevoproducto;
		global $editarproducto;
		$pn 	= $producto['producto_nombre'];
		$consulta = "SELECT * FROM wp_posts WHERE post_title='$pn'";
		$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
		$fila = mysql_fetch_array($resultado);
		if (!$fila[0]) //Si el producto no existe, llama un instancia al funcion para crear el producto
		{
			array_push($nuevoproducto, $producto);
		}else //Si el producto ya existe, llama un instancia al funcion para actualizar el producto
		{
			array_push($editarproducto, $producto);
		}
	}

    foreach($xml->producto as $producto){
	/*	Obteniendo las fotos para la galería
			Se crea una variable para marcar el índice de la galería, según el xml 				*/
		$producto_galeria 					= $producto->galeria;
		/*	********************************************************************************	*/

		/*	Se crea el array() vacío que contendrá todas las URL de las imagenes de la
			galería por producto 																*/
		$fotos_galeria 						= array();
		/*	********************************************************************************	*/

		/*	Se hace el ciclo que llena el array() anterior con las URL de las fotos, las
			cuales estan codificadas para el uso correcto dentro de las funciones 				*/
		foreach ($producto_galeria->children() as $imagen_galeria) {
			array_push($fotos_galeria, urlencode((string)$imagen_galeria['url']));
		}
		/*	********************************************************************************	*/
	/*	Convierte en un String el valor de las etiquetas xml necesarios para la tabla wp_post 	*/
	/*	Se utiliza utf8_decode para validar las tíldes y otros caracteres en los textos donde
		puede que los lleve, Nombre, Descripción, Características, etc.. 						*/
		$productoArmado 					= array(
			'producto_nombre'				=> ($producto_nombre 					= utf8_decode((string)$producto->nombre)),
			'producto_desc'					=> ($producto_desc 						= utf8_decode((string)$producto->descripcion)),
			'producto_desc_corta'			=> ($producto_desc_corta 				= utf8_decode((string)$producto->descripcion_corta)),
			'producto_categorias' 			=> ($producto_categorias 				= array(
																							(string)$producto->familia,
																							(string)$producto->sub_familia,
																							(string)$producto->categoria)
																					),
			'producto_stock'				=> ($producto_stock 					= (int)$producto->stock),
			'tienda_fontabella'				=> ($producto_tienda_fontabella 		= (int)$producto->tienda_fontabella),
			'tienda_zona10'					=> ($producto_tienda_zona10 			= (int)$producto->tienda_zona10),
			'tienda_futeca'					=> ($producto_tienda_futeca 			= (int)$producto->tienda_futeca),
			'tienda_xela'					=> ($producto_tienda_xela 				= (int)$producto->tienda_xela),
			'tienda_concepcion'				=> ($producto_tienda_concepcion 		= (int)$producto->tienda_concepcion),
			'producto_precio'				=> ($producto_precio 					= (float)$producto->precio),
			'producto_precio_oferta'		=> ($producto_precio_oferta 			= (float)$producto->precio_oferta),
			'producto_alto'					=> ($producto_alto						= (float)$producto->alto),
			'producto_ancho'				=> ($producto_ancho 					= (float)$producto->ancho),
			'producto_longitud'				=> ($producto_longitud					= (float)$producto->longitud),
			'producto_peso'					=> ($producto_peso						= (float)$producto->peso),
			'producto_caracteristicas'		=> ($producto_caracteristicas 			= array(
																							utf8_decode((string)$producto->tab_1),
																							utf8_decode((string)$producto->tab_2),
																							utf8_decode((string)$producto->tab_3),
																							utf8_decode((string)$producto->tab_4),
																							utf8_decode((string)$producto->tab_5))
																					),
			'producto_imagen_destacada'		=> ($producto_imagen_destacada			= urlencode((string)$producto->imagen_destacada->imagen['url'])),
			'fotos_galeria'					=> ($fotos_galeria)
		);
		/*	Se hace un llamado a la función que arma la galería por producto armarGaleria()
			y se envía como parametro el array lleno con las URL de las imagenes 				*/
		armarGaleria($fotos_galeria);
		todosProductos($productoArmado);
		echo $productoArmado['tienda_fontabella'];
	}
	include_once('query.php');
	/*
	add_producto($nuevoproducto);
	edit_producto($editarproducto);
	*/
	// var_dump($nuevoproducto);
	// var_dump($editarproducto);
}
?>