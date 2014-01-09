<?php
/*
*	@Uso: Carga de Datos XML para productos iStore Pro
* 	@Version: Beta 3.0
*	@file: query.php
*/
	function add_producto($producto){
		for ($i = 0 ; $i < count($producto); $i++) {
			/*Elimina los espacios en blanco y los reemplaza por "-" para crear el enlace permanente hacia el producto*/
			$simple_nom 						= str_replace(" ","-", $producto[$i]["producto_nombre"]);
			$simple_nom 						= str_replace("/","-", $simple_nom);
			$simple_nom 						= str_replace(".","-", $simple_nom);
			$simple_nom 						= str_replace("(","-", $simple_nom);
			$simple_nom 						= str_replace(")","-", $simple_nom);
			$simple_nom 						= str_replace(":","-", $simple_nom);
			$nom_producto 						= strtolower($simple_nom);

			$ultimo_id 							= getID();

			/*Crea un Guid para la tabla wp_post con la URL del demonio, el tipo de post y el ID del producto*/
			$Guid 								= "http://localhost/istore/?post_type=product&#038;p=$ultimo_id";

			/*Inserta el producto en la tabla WP_POST || Si el producto es nuevo sera cargado en la base de datos*/
			$consulta 							= "INSERT INTO wp_posts VALUES (NULL, '1', NOW(), NOW(), '".$producto[$i]["producto_desc"].$producto[$i]["lista_existencias"]."', '".$producto[$i]["producto_nombre"]."', '".$producto[$i]["producto_desc_corta"]."', 'private', 'open', 'closed', '', '".$nom_producto."', '', '', NOW(), NOW(), '', '0', '".$Guid."', '0', 'product', '', '0')";
			$respuesta 							= mysql_query($consulta, CONEXION) or die (mysql_error());

			/*Añade los metadatos necesarios para el producto*/
			$consulta_insert 					= "INSERT INTO wp_postmeta (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES (NULL, '$ultimo_id', '_manage_stock', 'yes'), (NULL, '$ultimo_id', '_backorders', 'no'), (NULL, '$ultimo_id', '_stock', '".$producto[$i]["producto_stock"]."'), (NULL, '$ultimo_id', '_sold_individually', ''), (NULL, '$ultimo_id', '_price', '".$producto[$i]["producto_precio"]."'), (NULL, '$ultimo_id', '_sale_price_dates_from', ''), (NULL, '$ultimo_id', '_sale_price_dates_to', ''), (NULL, '$ultimo_id', '_product_attributes', 'a:0:{}'), (NULL, '$ultimo_id', '_sku', ''), (NULL, '$ultimo_id', '_height', '".$producto[$i]["producto_alto"]."'), (NULL, '$ultimo_id', '_width', '".$producto[$i]["producto_ancho"]."'), (NULL, '$ultimo_id', '_length', '".$producto[$i]["producto_longitud"]."'), (NULL, '$ultimo_id', '_weight', '".$producto[$i]["producto_peso"]."'), (NULL, '$ultimo_id', '_featured', ''), (NULL, '$ultimo_id', 'purchase_note', ''), (NULL, '$ultimo_id', '_sale_price', '".$producto[$i]["producto_precio_oferta"]."'), (NULL, '$ultimo_id', '_regular_price', '".$producto[$i]["producto_precio"]."'), (NULL, '$ultimo_id', '_product_image_gallery', ''), (NULL, '$ultimo_id', '_virtual', 'no'), (NULL, '$ultimo_id', '_downloadable', 'no'), (NULL, '$ultimo_id', 'total_sales', '0'), (NULL, '$ultimo_id', '_stock_status', 'instock'), (NULL, '$ultimo_id', '_visibility', 'visible'), (NULL, '$ultimo_id', '_thumbnail_id', '')";
			$respuesta 							= mysql_query($consulta_insert, CONEXION) or die (mysql_error());

			/*familia:*/
			$simple_familia 					= strtolower(str_replace(" ","-", $producto[$i]["producto_categorias"][0]));

			/*sub_familia:*/
			$simple_sub_familia 				= strtolower(str_replace(" ","-", $producto[$i]["producto_categorias"][1]));

			/*Categoria*/
			$simple_categoria 					= strtolower(str_replace(" ","-", $producto[$i]["producto_categorias"][2]));

			/*Busca en la tabla wp_terms si existe una familia con el mismo nombre:*/
			$consulta 							= "SELECT * FROM wp_terms WHERE slug='$simple_familia'";
			$resultado 							= mysql_query($consulta, CONEXION) or die (mysql_error());
			$fila 								= mysql_fetch_array($resultado);
			if (!$fila[0])/*#Si no existe tal familia, entonces la crea:*/
			{
				$consulta 						= "INSERT INTO wp_terms VALUES (NULL, '$familia', '$simple_familia', 0)";
				$respuesta 						= mysql_query($consulta, CONEXION) or die (mysql_error());

				/*Recupera el ID asignado a la familia recien creada:*/
				$consulta 						= "SELECT * FROM wp_terms WHERE slug='$simple_familia'";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila 							= mysql_fetch_array($resultado);
				$id_familia 					= $fila[0];/*#Guarda el ID de la familia recien creada.*/

				/*Le asigna una taxonomia (Categoria de producto). Ya que es una familia no tiene sub niveles.*/
				$consulta 						= "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_familia', 'product_cat','', 0, 0)";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());

				/*Relaciona al producto previamente agregado con la familia a la que pertenece.*/
				$consulta 						= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_familia', 0)";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());

				/*Busca en la tabla wp_terms si existe una sub-familia con el mismo nombre:*/
				$consulta 						= "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila 							= mysql_fetch_array($resultado);
				if (!$fila[0])/*#Si no existe tal sub-familia, entonces la crea:*/
				{
					$consulta 					= "INSERT INTO wp_terms VALUES (NULL, '$sub_familia', '$simple_sub_familia', 0)";
					$respuesta 					= mysql_query($consulta, CONEXION);

					/*Recupera el ID asignado a la sub-familia recien creada:*/
					$consulta 					= "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					$fila 						= mysql_fetch_array($resultado);
					$id_sub_familia 			= $fila[0];/*#Guarda el ID de la sub-familia recien creada.*/

					/*Le asigna una taxonomia (Categoria de producto). Ya que es una sub-familia pone por Parent el ID de la familia*/
					$consulta 					= "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_sub_familia', 'product_cat','', $id_familia, 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());

					/*Relaciona al producto previamente agregado con la sub-familia a la que pertenece.*/
					$consulta 					= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_sub_familia', 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
				} else {
					/*Recupera el ID asignado a la sub-familia recien creada:*/
					$consulta 					= "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					$fila 						= mysql_fetch_array($resultado);
					$id_sub_familia 			= $fila[0];/*#Guarda el ID de la sub-familia recien creada.*/

					/*Relaciona al producto previamente agregado con la sub-familia a la que pertenece.*/
					$consulta 					= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_sub_familia', 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
				}

				/*Busca en la tabla wp_terms si existe una sub-familia con el mismo nombre:*/
				$consulta 						= "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila 							= mysql_fetch_array($resultado);
				if (!$fila[0])/*#Si no existe tal sub-familia, entonces la crea:*/
					{
					$consulta 					= "INSERT INTO wp_terms VALUES (NULL, '$categoria', '$simple_categoria', 0)";
					$respuesta 					= mysql_query($consulta, CONEXION);

					/*Recupera el ID asignado a la categoria recien creada:*/
					$consulta 					= "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					$fila 						= mysql_fetch_array($resultado);
					$id_categoria				= $fila[0];

					/*Le asigna una taxonomia (Categoria de producto). Ya que es una categoria pone por Parent el ID de la sub-familia*/
					$consulta 					= "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_categoria', 'product_cat','', $id_sub_familia, 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());

					/*Relaciona al producto previamente agregado con la categoria a la que pertenece.*/
					$consulta 					= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_categoria', 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					} else {
					/*Recupera el ID asignado a la categoria recien creada:*/
					$consulta 					= "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					$fila 						= mysql_fetch_array($resultado);
					$id_categoria 				= $fila[0];

					/*Relaciona al producto previamente agregado con la categoria a la que pertenece.*/
					$consulta 					= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_categoria', 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					}
			} else {
				/*Recupera el ID asignado a la familia recien creada:*/
				$consulta 						= "SELECT * FROM wp_terms WHERE slug='$simple_familia'";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila 							= mysql_fetch_array($resultado);
				$id_familia 					= $fila[0];/*#Guarda el ID de la familia recien creada.*/

				/*Relaciona al producto previamente agregado con la familia a la que pertenece.*/
				$consulta 						= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_familia', 0)";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());

				/*Busca en la tabla wp_terms si existe una sub-familia con el mismo nombre:*/
				$consulta 						= "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila 							= mysql_fetch_array($resultado);
				if (!$fila[0])/*#Si no existe tal sub-familia, entonces la crea:*/
				{
					$consulta 					= "INSERT INTO wp_terms VALUES (NULL, '$sub_familia', '$simple_sub_familia', 0)";
					$respuesta 					= mysql_query($consulta, CONEXION);

					/*Recupera el ID asignado a la sub-familia recien creada:*/
					$consulta 					= "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					$fila 						= mysql_fetch_array($resultado);
					$id_sub_familia 			= $fila[0];/*#Guarda el ID de la sub-familia recien creada.*/

					/*Le asigna una taxonomia (Categoria de producto). Ya que es una sub-familia pone por Parent el ID de la familia*/
					$consulta 					= "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_sub_familia', 'product_cat','', $id_familia, 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());

					/*Relaciona al producto previamente agregado con la sub-familia a la que pertenece.*/
					$consulta 					= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_sub_familia', 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
				} else {
					/*Recupera el ID asignado a la sub-familia recien creada:*/
					$consulta 					= "SELECT * FROM wp_terms WHERE slug='$simple_sub_familia'";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					$fila 						= mysql_fetch_array($resultado);
					$id_sub_familia 			= $fila[0];/*#Guarda el ID de la sub-familia recien creada.*/

					/*Relaciona al producto previamente agregado con la sub-familia a la que pertenece.*/
					$consulta 					= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_sub_familia', 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
				}
					/*Busca en la tabla wp_terms si existe una sub-familia con el mismo nombre:*/
				$consulta 						= "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
				$resultado 						= mysql_query($consulta, CONEXION) or die (mysql_error());
				$fila 							= mysql_fetch_array($resultado);
				if (!$fila[0])/*#Si no existe tal sub-familia, entonces la crea:*/
					{
					$consulta 					= "INSERT INTO wp_terms VALUES (NULL, '$categoria', '$simple_categoria', 0)";
					$respuesta 					= mysql_query($consulta, CONEXION);

					/*Recupera el ID asignado a la categoria recien creada:*/
					$consulta 					= "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					$fila 						= mysql_fetch_array($resultado);
					$id_categoria 				= $fila[0];

					/*Le asigna una taxonomia (Categoria de producto). Ya que es una categoria pone por Parent el ID de la sub-familia*/
					$consulta 					= "INSERT INTO wp_term_taxonomy VALUES (NULL, '$id_categoria', 'product_cat','', $id_sub_familia, 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());

					/*Relaciona al producto previamente agregado con la categoria a la que pertenece.*/
					$consulta 					= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_categoria', 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					} else {
					/*Recupera el ID asignado a la categoria recien creada:*/
					$consulta 					= "SELECT * FROM wp_terms WHERE slug='$simple_categoria'";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					$fila 						= mysql_fetch_array($resultado);
					$id_categoria 				= $fila[0];

					/*Relaciona al producto previamente agregado con la categoria a la que pertenece.*/
					$consulta 					= "INSERT INTO wp_term_relationships VALUES ($ultimo_id, '$id_categoria', 0)";
					$resultado 					= mysql_query($consulta, CONEXION) or die (mysql_error());
					}
			}

		}
		mysql_close(CONEXION);
		if(count($producto)<1){
			echo "<strong>No</strong> se encontraron <strong>Productos Nuevos</strong> para ingresar! <br/><br/>";
		}else{
			echo count($producto) . ' <strong>Producto(s) Nuevos</strong> Ingresados con exito! <br/><br/>';
		}
	}

	function edit_producto($producto){
		for ($i = 0 ; $i < count($producto); $i++) {
			$simple_nom 						= str_replace(" ","-", $producto[$i]["producto_nombre"]);
			$simple_nom 						= str_replace("/","-", $simple_nom);
			$simple_nom 						= str_replace(".","-", $simple_nom);
			$simple_nom 						= str_replace("(","-", $simple_nom);
			$simple_nom 						= str_replace(")","-", $simple_nom);
			$simple_nom 						= str_replace(":","-", $simple_nom);
			$nom_producto 						= strtolower($simple_nom);

			/*Busca en la tabla wp_posts si existe un producto con el mismo nombre:*/
			$consulta 							= "SELECT * FROM wp_posts WHERE post_name = '$nom_producto'";
			$resultado 							= mysql_query($consulta, CONEXION) or die (mysql_error());
			while($fila 						= mysql_fetch_array($resultado, MYSQL_BOTH)){
				/* 	Guarda el contenido del producto en una variable */
				$contenido 						= $fila['post_content'];
				/* 	Elimina el contenido de las tablas en HTML para sumarle el que viene en la actualización	 */
				$contenidolimpio 				= preg_replace('%<table\b[^>]*+>(?:(?R)|[^<]*+(?:(?!</?table\b)<[^<]*+)*+)*+</table>%i','',$contenido);
				$contCompleto 					= $contenidolimpio.$producto[$i]["lista_existencias"];

				$elID 							= $fila["ID"];
				$elPrecio 						= $producto[$i]["producto_precio"];
				$elPrecioOferta 				= $producto[$i]["producto_precio_oferta"];
				$elStock 						= $producto[$i]["producto_stock"];

				$update_contenido				= "UPDATE `wp_posts` SET `post_content` ='$contCompleto' WHERE `ID`='$elID';";
				$inyeccion 						= mysql_query($update_contenido, CONEXION)or die(mysql_error());

				$update_postmeta_price 			= "UPDATE `wp_postmeta` SET `meta_value`='$elPrecio' WHERE `post_id`='$elID' AND `meta_key`='_price';";
				$inyeccion_price				= mysql_query($update_postmeta_price, CONEXION)or die(mysql_error());

				$update_postmeta_rprice 		= "UPDATE `wp_postmeta` SET `meta_value`='$elPrecio' WHERE `post_id`='$elID' AND `meta_key`='_regular_price';";
				$inyeccion_rprice				= mysql_query($update_postmeta_rprice, CONEXION)or die(mysql_error());

				$update_postmeta_sale_price		= "UPDATE `wp_postmeta` SET `meta_value`='$elPrecioOferta' WHERE `post_id`='$elID' AND `meta_key`='_sale_price';";
				$inyeccion_sale_price			= mysql_query($update_postmeta_sale_price, CONEXION)or die(mysql_error());

				$update_postmeta_stock 			= "UPDATE `wp_postmeta` SET `meta_value`='$elStock' WHERE `post_id`='$elID' AND `meta_key`='_stock';";
				$inyeccion_stock				= mysql_query($update_postmeta_stock, CONEXION)or die(mysql_error());
			}
		}
		echo '<strong>' . count($producto) . ' Producto(s) Editados</strong> con exito!';
	}
?>