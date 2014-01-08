<?
date_default_timezone_set('America/Guatemala');
$ahora 					= date('Y-m-d H:i:s', time());
/*echo $ahora;*/

	/*Elimina los espacios en blanco y los reemplaza por "-" para crear el enlace permanente hacia el producto*/
	$simple_nom = str_replace(" ","-", $producto[$i]["producto_nombre"]);
	$simple_nom = str_replace("/","-", $simple_nom);
	$simple_nom = str_replace(".","-", $simple_nom);
	$simple_nom = str_replace("(","-", $simple_nom);
	$simple_nom = str_replace(")","-", $simple_nom);
	$simple_nom = str_replace(":","-", $simple_nom);
		$nom_producto = strtolower($simple_nom);

function add_producto($producto){
	for ($i = 0 ; $i < count($producto); $i++) {
		$ultimo_id = getID();

		/*Crea un Guid para la tabla wp_post con la URL del demonio, el tipo de post y el ID del producto*/
		$Guid = "http://localhost/istore/?post_type=product&#038;p=$ultimo_id";

		/*Inserta el producto en la tabla WP_POST || Si el producto es nuevo sera cargado en la base de datos*/
		$consulta = "INSERT INTO wp_posts VALUES (NULL, '1', NOW(), NOW(), '".$producto[$i]["producto_desc"].$producto[$i]["lista_existencias"]."', '".$producto[$i]["producto_nombre"]."', '".$producto[$i]["producto_desc_corta"]."', 'private', 'open', 'closed', '', '".$nom_producto."', '', '', NOW(), NOW(), '', '0', '".$Guid."', '0', 'product', '', '0')";
		$respuesta = mysql_query($consulta, CONEXION);

		/*Añade los metadatos necesarios para el producto*/
		$consulta_insert = "INSERT INTO wp_postmeta (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES (NULL, '$ultimo_id', '_manage_stock', 'yes'), (NULL, '$ultimo_id', '_backorders', 'no'), (NULL, '$ultimo_id', '_stock', '".$producto[$i]["producto_stock"]."'), (NULL, '$ultimo_id', '_sold_individually', ''), (NULL, '$ultimo_id', '_price', '".$producto[$i]["producto_precio"]."'), (NULL, '$ultimo_id', '_sale_price_dates_from', ''), (NULL, '$ultimo_id', '_sale_price_dates_to', ''), (NULL, '$ultimo_id', '_product_attributes', 'a:0:{}'), (NULL, '$ultimo_id', '_sku', ''), (NULL, '$ultimo_id', '_height', '".$producto[$i]["producto_alto"]."'), (NULL, '$ultimo_id', '_width', '".$producto[$i]["producto_ancho"]."'), (NULL, '$ultimo_id', '_length', '".$producto[$i]["producto_longitud"]."'), (NULL, '$ultimo_id', '_weight', '".$producto[$i]["producto_peso"]."'), (NULL, '$ultimo_id', '_featured', ''), (NULL, '$ultimo_id', 'purchase_note', ''), (NULL, '$ultimo_id', '_sale_price', '".$producto[$i]["producto_precio_oferta"]."'), (NULL, '$ultimo_id', '_regular_price', '".$producto[$i]["producto_precio"]."'), (NULL, '$ultimo_id', '_product_image_gallery', ''), (NULL, '$ultimo_id', '_virtual', 'no'), (NULL, '$ultimo_id', '_downloadable', 'no'), (NULL, '$ultimo_id', 'total_sales', '0'), (NULL, '$ultimo_id', '_stock_status', 'instock'), (NULL, '$ultimo_id', '_visibility', 'visible'), (NULL, '$ultimo_id', '_thumbnail_id', '')";
		$respuesta = mysql_query($consulta_insert, CONEXION);

		/*familia.*/
		$familia = $producto[$i]["producto_categorias"][0];
		$simple_familia = str_replace(" ","-", $familia);
		$simple_familia = strtolower($simple_familia);

		/*sub_familia:*/
		$sub_familia = $producto[$i]["producto_categorias"][1];
		$simple_sub_familia = str_replace(" ","-", $sub_familia);
		$simple_sub_familia = strtolower($simple_sub_familia);

		/*Categoria*/
		$categoria = $producto[$i]["producto_categorias"][2];
		$simple_categoria = str_replace(" ","-", $categoria);
		$simple_categoria = strtolower($simple_categoria);

		/*Busca en la tabla wp_terms si existe una familia con el mismo nombre:*/
		$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_familia'";
		$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
		$fila = mysql_fetch_array($resultado);
		if (!$fila[0])/*#Si no existe tal familia, entonces la crea:*/
		{
			$consulta = "INSERT INTO wp_terms VALUES (NULL, '$familia', '$simple_familia', 0)";
			$respuesta = mysql_query($consulta, CONEXION);

			/*Recupera el ID asignado a la familia recien creada:*/
			$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_familia'";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			$fila = mysql_fetch_array($resultado);
			$id_familia = $fila[0];/*#Guarda el ID de la familia recien creada.*/

			/*Le asigna una taxonomia (Categoria de producto). Ya que es una familia no tiene sub niveles.*/
			$consulta = "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_familia', 'product_cat','', 0, 0)";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());

			/*Relaciona al producto previamente agregado con la familia a la que pertenece.*/
			$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_familia', 0)";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());

			/*Busca en la tabla wp_terms si existe una sub-familia con el mismo nombre:*/
			$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			$fila = mysql_fetch_array($resultado);
			if (!$fila[0])/*#Si no existe tal sub-familia, entonces la crea:*/
			{
				$consulta = "INSERT INTO wp_terms VALUES (NULL, '$sub_familia', '$simple_sub_familia', 0)";
				$respuesta = mysql_query($consulta, CONEXION);

				/*Recupera el ID asignado a la sub-familia recien creada:*/
				$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				$id_sub_familia = $fila[0];/*#Guarda el ID de la sub-familia recien creada.*/

				/*Le asigna una taxonomia (Categoria de producto). Ya que es una sub-familia pone por Parent el ID de la familia*/
				$consulta = "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_sub_familia', 'product_cat','', $id_familia, 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());

				/*Relaciona al producto previamente agregado con la sub-familia a la que pertenece.*/
				$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_sub_familia', 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			} else
				{
				/*Recupera el ID asignado a la sub-familia recien creada:*/
				$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				$id_sub_familia = $fila[0];/*#Guarda el ID de la sub-familia recien creada.*/

				/*Relaciona al producto previamente agregado con la sub-familia a la que pertenece.*/
				$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_sub_familia', 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				}

			/*Busca en la tabla wp_terms si existe una sub-familia con el mismo nombre:*/
			$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			$fila = mysql_fetch_array($resultado);
			if (!$fila[0])/*#Si no existe tal sub-familia, entonces la crea:*/
				{
				$consulta = "INSERT INTO wp_terms VALUES (NULL, '$categoria', '$simple_categoria', 0)";
				$respuesta = mysql_query($consulta, CONEXION);

				/*Recupera el ID asignado a la categoria recien creada:*/
				$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				$id_categoria = $fila[0];

				/*Le asigna una taxonomia (Categoria de producto). Ya que es una categoria pone por Parent el ID de la sub-familia*/
				$consulta = "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_categoria', 'product_cat','', $id_sub_familia, 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());

				/*Relaciona al producto previamente agregado con la categoria a la que pertenece.*/
				$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_categoria', 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				}
				else
				{
				/*Recupera el ID asignado a la categoria recien creada:*/
				$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				$id_categoria = $fila[0];

				/*Relaciona al producto previamente agregado con la categoria a la que pertenece.*/
				$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_categoria', 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				}
		} else
		{
			/*Recupera el ID asignado a la familia recien creada:*/
			$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_familia'";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			$fila = mysql_fetch_array($resultado);
			$id_familia = $fila[0];/*#Guarda el ID de la familia recien creada.*/

			/*Relaciona al producto previamente agregado con la familia a la que pertenece.*/
			$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_familia', 0)";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());

			/*Busca en la tabla wp_terms si existe una sub-familia con el mismo nombre:*/
			$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			$fila = mysql_fetch_array($resultado);
			if (!$fila[0])/*#Si no existe tal sub-familia, entonces la crea:*/
			{
				$consulta = "INSERT INTO wp_terms VALUES (NULL, '$sub_familia', '$simple_sub_familia', 0)";
				$respuesta = mysql_query($consulta, CONEXION);

				/*Recupera el ID asignado a la sub-familia recien creada:*/
				$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				$id_sub_familia = $fila[0];/*#Guarda el ID de la sub-familia recien creada.*/

				/*Le asigna una taxonomia (Categoria de producto). Ya que es una sub-familia pone por Parent el ID de la familia*/
				$consulta = "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_sub_familia', 'product_cat','', $id_familia, 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());

				/*Relaciona al producto previamente agregado con la sub-familia a la que pertenece.*/
				$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_sub_familia', 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			} else
				{
				/*Recupera el ID asignado a la sub-familia recien creada:*/
				$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				$id_sub_familia = $fila[0];/*#Guarda el ID de la sub-familia recien creada.*/

				/*Relaciona al producto previamente agregado con la sub-familia a la que pertenece.*/
				$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_sub_familia', 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				}
				/*Busca en la tabla wp_terms si existe una sub-familia con el mismo nombre:*/
			$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			$fila = mysql_fetch_array($resultado);
			if (!$fila[0])/*#Si no existe tal sub-familia, entonces la crea:*/
				{
				$consulta = "INSERT INTO wp_terms VALUES (NULL, '$categoria', '$simple_categoria', 0)";
				$respuesta = mysql_query($consulta, CONEXION);

				/*Recupera el ID asignado a la categoria recien creada:*/
				$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				$id_categoria = $fila[0];

				/*Le asigna una taxonomia (Categoria de producto). Ya que es una categoria pone por Parent el ID de la sub-familia*/
				$consulta = "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_categoria', 'product_cat','', $id_sub_familia, 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());

				/*Relaciona al producto previamente agregado con la categoria a la que pertenece.*/
				$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_categoria', 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				}
				else
				{
				/*Recupera el ID asignado a la categoria recien creada:*/
				$consulta = "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				$id_categoria = $fila[0];

				/*Relaciona al producto previamente agregado con la categoria a la que pertenece.*/
				$consulta = "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_categoria', 0)";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				}
		}

	}
	mysql_close(CONEXION);
	echo count($producto) . ' Producto(s) Ingresados con exito!';
}

	function edit_producto($producto){
		for ($i = 0 ; $i < count($producto); $i++) {
			/*Consultas para actualizar el producto en la Tabla WP_POSTMETA
			Cantidad de productos en existencia*/
			$consulta_update = "UPDATE wp_postmeta "
				." set "
				." meta_value='".$producto[$i]["producto_stock"]."' "
				." where "
				." post_id=".$producto[$i]["id"];
			$respuesta = mysql_query($consulta_update,CONEXION);

			/*Precio*/
			$consulta_update = "UPDATE wp_postmeta "
				." set "
				." meta_value='".$producto[$i]["producto_precio"]."' "
				." where "
				." post_id=".$producto[$i]["id"];
			$respuesta = mysql_query($consulta_update,CONEXION);

			/*Precio de oferta*/
			$consulta_update = "UPDATE wp_postmeta "
				." set "
				." meta_value='".$producto[$i]["producto_precio_oferta"]."' "
				." where "
				." post_id=".$producto[$i]["id"];
			$respuesta = mysql_query($consulta_update,CONEXION);

			/*Precio Regular*/
			$consulta_update = "UPDATE wp_postmeta "
				." set "
				." meta_value='".$producto[$i]["producto_precio"]."' "
				." where "
				." post_id=".$producto[$i]["id"];
			$respuesta = mysql_query($consulta_update,CONEXION);

			/*Busca en la tabla wp_posts si existe un producto con el mismo nombre:*/
			$consulta = "SELECT * FROM wp_posts WHERE post_name = '$nom_producto'";
			$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
			$fila = mysql_fetch_array($resultado);
			if (!$fila[0])/*#Si no existe el producto, entonces muestra un error:*/
			{
				echo 'El producto a modificar no existe en la base de datos';
			}
			else
			{
				$consulta = "SELECT * FROM wp_posts WHERE post_name = '$nom_producto'";
				$resultado = mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila = mysql_fetch_array($resultado);
				while($fila = mysql_fetch_assoc($resultado)){
					/* 	Guarda el contenido del producto en una variable */
					$contenido 				= $fila['post_content'];
					/* 	Elimina el contenido de las tablas en HTML para sumarle el que viene en la actualización	 */
					$contenidolimpio 		= preg_replace('%<table\b[^>]*+>(?:(?R)|[^<]*+(?:(?!</?table\b)<[^<]*+)*+)*+</table>%i','',$contenido);
					/*echo $contenidolimpio;*/
				}
			}
		}
		echo count($producto) . ' Producto(s) Editados con exito!';
	}
?>