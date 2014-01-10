<?php
/*
*	@Uso: Carga de Datos XML para productos iStore Pro
* 	@Version: Beta 3.0
*	@file: getdata.php
*/

/* LEE EL XML ********************************************************************************************************** 	*/
/* ---------------------- LECTURA DEL XML ------------------------------------------------------------------------------	*/
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
		if (!$fila[0]) /*Si el producto no existe, llama un instancia al funcion para crear el producto*/
		{
			array_push($nuevoproducto, $producto);
		}else /*Si el producto ya existe, llama un instancia al funcion para actualizar el producto*/
		{
			array_push($editarproducto, $producto);
		}
	}

    foreach($xml->producto as $producto){
	/*	Convierte en un String el valor de las etiquetas xml necesarios para la tabla wp_post 	*/
	/*	Se utiliza utf8_decode para validar las tíldes y otros caracteres en los textos donde
		puede que los lleve, Nombre, Descripción, Características, etc.. 						*/
		$productoArmado 					= array(
			'producto_upc'					=> ($producto_upc 						= (int)$producto->upc),
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
		/* 	Se crea un array con los nombres de las tiendas, pasadas por utf8_decode para validar tildes 	*/
		$nombre_tienda 						= array(
													utf8_decode('FONTABELLA'),
													utf8_decode('ZONA 10'),
													utf8_decode('PLAZA FUTECA'),
													utf8_decode('PRADERA XELA'),
													utf8_decode('PRADERA CONCEPCION')
											);
		/*	************************************************************************************	*/
		/*	Armado de la variable que contiene el HTML para el listado de existencias por tienda	*/
		$lista_existencias					= '
			<table class="sortable" cellpadding="0" cellspacing="0">
				<thead><tr class="alt first last">
					<th value="Tienda" rel="0">Tienda<span class="arrow"></span></th>
					<th value="Existencia" rel="1">Existencia</th>
				</tr></thead>
				<tbody><tr class="">
					<td value="'.$nombre_tienda[0].'">'.$nombre_tienda[0].'</td>
					<td value="'.$productoArmado['tienda_fontabella'].'">'.$productoArmado['tienda_fontabella'].'</td>
				</tr><tr class="alt">
					<td value="'.$nombre_tienda[1].'">'.$nombre_tienda[1].'</td>
					<td value="'.$productoArmado['tienda_zona10'].'">'.$productoArmado['tienda_zona10'].'</td>
				</tr><tr class="">
					<td value="'.$nombre_tienda[2].'">'.$nombre_tienda[2].'</td>
					<td value="'.$productoArmado['tienda_futeca'].'">'.$productoArmado['tienda_futeca'].'</td>
				</tr><tr class="">
					<td value="'.$nombre_tienda[3].'">'.$nombre_tienda[3].'</td>
					<td value="'.$productoArmado['tienda_xela'].'">'.$productoArmado['tienda_xela'].'</td>
				</tr><tr class="alt last">
					<td value="'.$nombre_tienda[4].'">'.$nombre_tienda[4].'</td>
					<td value="'.$productoArmado['tienda_concepcion'].'">'.$productoArmado['tienda_concepcion'].'</td>
				</tr></tbody>
			</table>
		';
		/*	************************************************************************************	*/
		/*	Se inserta la variable $lista_existencias al final del $productoArmado 					*/
		$productoArmado['lista_existencias'] = $lista_existencias;
		/*	************************************************************************************	*/
		/*	Se envía el ProductoArmado por parámetro a la función que valida el producto 			*/
		todosProductos($productoArmado);
		/*	************************************************************************************	*/
	}
	include_once('query.php');
	add_producto($nuevoproducto);
	edit_producto($editarproducto);
}
?>