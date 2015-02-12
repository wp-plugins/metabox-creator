<?php


/**
 * Fonction générique qui récupère les post meta
 *
 * $post_type (string) : le post_type
 * $args (array) : paramètres identique de WP_Query (http://codex.wordpress.org/Class_Reference/WP_Query)
 * $count_result contient le nombre de résultats trouvés
 *
 * Retourne un tableau contenant les résultats de la requete
 *
 * Notes :
 * - $mbc['meta_multiple'] est un array contenant l'id des custom field multiple
 *   il doit être défini dans functions.php
 */

function mbc_get_posts($post_type, $args = array(), &$count_result=0, $field = array())
{
  global $mbc;

	$args_default = array(
	  'posts_per_page' => -1,
	  'orderby' => 'title',
	  'order' => 'ASC',
	  'post_type' => $post_type,
	  'post_status' => 'publish',
	  'ignore_sticky_posts' => 1
	);

  $args = array_merge($args_default, $args);

  $query = new WP_Query($args);

  $count_result = $query->found_posts;



  $list_detail = array();
  $i=0;

  if($query->have_posts())
  {
    while ($query->have_posts())
    {
      $query->the_post();

      $content = get_the_content();

      $list_detail[$i] = array(
        'id' => get_the_id(),
        'title' => get_the_title(),
        'content' => str_replace(']]>', ']]&gt;', apply_filters('the_content', $content)),
        'excerpt' => get_the_excerpt(),
        'url' => get_permalink(),
        'date' => get_the_date("j F Y"),
      );

			if($field)
			{
				foreach($field as $field_name)
				{
					switch($field_name)
					{
						case 'post_author' : $list_detail[$i]['post_author'] = $query->post->post_author; break;
						case 'post_status' : $list_detail[$i]['post_status'] = $query->post->post_status; break;
						case 'slug' : $list_detail[$i]['slug'] = $query->post->post_name; break;
						case 'post_type' : $list_detail[$i]['post_type'] = $query->post->post_type; break;
						case 'post_parent' : $list_detail[$i]['post_parent'] = $query->post->post_parent; break;
						case 'get_the_content' : $list_detail[$i]['get_the_content'] = $content; break;
						case 'date_update' : $list_detail[$i]['date_update'] = the_modified_date("j F Y", '', '', false); break;
						case 'hour' : $list_detail[$i]['hour'] = get_the_time(); break;
						case 'time' : $list_detail[$i]['time'] = get_the_date("U"); break;
						case 'time_update' : $list_detail[$i]['time_update'] = get_the_modified_date("U"); break;
						case 'nb_comment' : $list_detail[$i]['nb_comment'] = get_comments_number();  break;
					}
				}
			}


			// récupère les metas
      $custom = get_post_custom();

      foreach($custom as $key => $val)
      {
        // on ignore les meta automatiques de WordPress
        if(substr($key, 0, 1) != '_' || $key == '_thumbnail_id')
        {
          // unserialize les champs qui le sont
          foreach($val as $j => $val2) {
            $val[$j] = maybe_unserialize($val2);
          }

          $val_ok = (count($val) != 1 || (is_array($mbc['meta_multiple']) && in_array($key, $mbc['meta_multiple']))) ? $val : $val[0];

          $list_detail[$i][$key] = $val_ok;
        }
      }

      // renomme _thumbnail_id
      $list_detail[$i]['thumbnail_id'] = (!empty($list_detail[$i]['_thumbnail_id'])) ? $list_detail[$i]['_thumbnail_id'] : null;
	    unset($list_detail[$i]['_thumbnail_id']);

      $i++;
    }
  }

	// restaure $wp_query et le global post data par leur valeur d'origine
	wp_reset_query();

  return $list_detail;
}


/**
 * Récupere un seul enregistrement spécifique
 */
function mbc_get_post($the_post_id, $field=array('post_author', 'post_type', 'post_parent', 'date_update', 'hour'))
{
  $args = array(
    'post__in' => array($the_post_id),
    'post_status' => 'any',
  );
	$data = mbc_get_posts('any', $args, $total, $field);

	return (count($data) == 1 ? $data[0] : array());
}



/**
 * Retourne un tableau associatif post_id => title
 */
function mbc_get_list($post_type, $args = array(), $apply_filter = false)
{
	// paramètre par défaut
  $args_default = array(
    'no_found_rows' => true,
		'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_type' => $post_type,
    'post_status' => 'publish',
    'ignore_sticky_posts' => 1
  );

  $args = array_merge($args_default, $args);

	// execution de la requête
  $query = new WP_Query($args);


  $list_simple = array();
  if(count($query->posts) > 0)
  {
    foreach($query->posts as $val) {
      $list_simple[$val->ID] = ($apply_filter !== false) ? apply_filters('the_title', $val->post_title) : $val->post_title;
    }
  }

	// restaure $wp_query et le global post data à leur valeur d'origine
	wp_reset_query();

  return $list_simple;
}



function mbc_get_option($option='', $return_key_val=false)
{
	global $mbc;

	$option_post_id = get_option('mbc_option_post_id');


	if($option == '')
	{
    $custom = get_post_meta($option_post_id);

		$value = array();

    foreach($custom as $key => $val)
    {
      // on ignore les meta automatiques de WordPress
      if(substr($key, 0, 1) != '_')
      {
        // unserialize les champs qui le sont
        foreach($val as $j => $val2) {
          $val[$j] = maybe_unserialize($val2);
        }


				// gere les champs "multiple"=>"option" créées dans les options du site
				if(isset($val[0]) && is_array($val[0]) && array_key_exists('key', $val[0]) && array_key_exists('val', $val[0]))
				{
					$val_ok = array();
					foreach($val as $loop)
					{
						if($return_key_val || count($loop) == 2) {
							$val_ok[$loop['key']] = $loop['val'];
						}
						// permet de gerer une option avec plusieurs parametres (autres que key et val)
						else
						{
							$loopkey = $loop['key'];
							$val_ok[$loopkey] = array('id'=>$loopkey, 'title'=>$loop['val']);
							unset($loop['key'], $loop['val']);
							$val_ok[$loopkey] += $loop;
						}
					}
				}
				else {
	        $val_ok = (count($val) != 1 || (is_array($mbc['meta_multiple']) && in_array($key, $mbc['meta_multiple']))) ? $val : $val[0];
				}
        $value[$key] = $val_ok;
      }
    }
	}
	else
	{
		$meta = get_post_meta($option_post_id, $option);

		// gere les champs "multiple"=>"option" créées dans les options du site
		if(isset($meta[0]) && is_array($meta[0]) && array_key_exists('key', $meta[0]) && array_key_exists('val', $meta[0]))
		{
			$value = array();
			foreach($meta as $val)
			{
				if($return_key_val || count($val) == 2) {
					$value[$val['key']] = $val['val'];
				}
				// permet de gerer une option avec plusieurs parametres (autres que key et val)
				else
				{
					$key = $val['key'];
					$value[$key] = array('id'=>$key, 'title'=>$val['val']);
					unset($val['key'], $val['val']);
					$value[$key] += $val;
				}
			}
		}
		else {
			$value = (count($meta) != 1 || (is_array($mbc['meta_multiple']) && in_array($option, $mbc['meta_multiple']))) ? $meta : $meta[0];
		}

	}

	return $value;
}






/**
 * Permet de récuper l'ID, le titre ou l'URL d'un post_type à partir de son nom passé en paramètre dans $mbc['page_list']
 * Compatible WPML et Polylang
 */
function mbc_get_id($page_name, $lang_code='')
{
	global $mbc;

	if(!isset($mbc['page_list'][$page_name])) {
		return false;
	}

	// plugin "Polylang"
	if(function_exists('pll_get_post')) {
		return pll_get_post($mbc['page_list'][$page_name], $lang_code);
	}
	// plugin "WPML"
	elseif(function_exists('icl_object_id')) {
		return icl_object_id($mbc['page_list'][$page_name], 'any', true);
	}
	else {
		return $mbc['page_list'][$page_name];
	}
}


function mbc_get_url($page_name) {
	return get_permalink(mbc_get_id($page_name));
}

function mbc_get_title($page_name) {
	return get_the_title(mbc_get_id($page_name));
}


function mbc_is_page($page_name, $post_id=null)
{
	global $post;

	if($post_id && mbc_get_id($page_name) == $post_id) {
		return true;
	}
	elseif(!empty($post->ID) && mbc_get_id($page_name) == $post->ID) {
		return true;
	}
	else {
		return false;
	}
}





/**
 * retourne un tableau associatif term_id => label pour une taxonomy donnée
 */
function mbc_get_terms_list($taxonomy, $args=array(), $return_key='term_id')
{
	$terms = array();

  $args_default = array(
    'hide_empty'=>false,
  );

  $args = array_merge($args_default, $args);

  $term_data = get_terms($taxonomy, $args);
  foreach($term_data as $val) {
    $terms[$val->$return_key] = $val->name;
  }

  return $terms;
}





function mbc_get_html_element($type, $args, $inside=null)
{
	foreach($args as $key => $val)
	{
		if(!preg_match('/[a-z0-9\-_]+/i', $key)) {
			continue;
		}

		$attr[$key] = esc_attr($key).'="'.str_replace('"', '&quot;', $val).'"';
	}

	return '<'.$type.' '.implode(' ', $attr).'>'.($inside ? $inside.'</'.$type.'>' : '');
}




function mbc_get_image_html($id, $size='thumbnail', $default_image=false, $args=array(), $link=null, $args_link=array())
{
	// no image
	if(!($args['src'] = mbc_get_image_url($id, $size, $default_image))) {	return ''; }

	// alt empty by default
	if(!isset($args['alt'])) { $args['alt'] = ''; }

	$html = mbc_get_html_element('img', $args);

	if($link)
	{
		$args_link['href'] = (in_array($link, get_intermediate_image_sizes() + array(1000 => 'thumbnail', 'medium', 'large', 'full'))) ? mbc_get_image_url($id, $link, $default_image) : $link;

		$html = mbc_get_html_element('a', $args_link, $html);
	}

	return $html;
}


/**
 * Récupere l'url des images uploadées via WordPress
 */
function mbc_get_image_url($id, $size='thumbnail', $default_image=false)
{
	if(!empty($id) && is_numeric($id))
	{
	  $img = wp_get_attachment_image_src($id, $size);
	  if($img !== false) {
			return $img[0];
	  }
	}

	if($default_image === false) {
		return false;
	}
	elseif(preg_match('/(jpg|jpeg|gif|png)/i', pathinfo($default_image, PATHINFO_EXTENSION))) {
		return $default_image;
	}
	else
	{
		global $_wp_additional_image_sizes;

		if($size == 'full')
		{
			$width = '1280';
			$height = '768';
		}
		// check custom image size (registred via add_image_size)
		elseif(isset($_wp_additional_image_sizes[$size]))
		{
			$width = $_wp_additional_image_sizes[$size]['width'];
			$height = $_wp_additional_image_sizes[$size]['height'];
		}
		else
		{
			$width = get_option($size.'_size_w');
			$height = get_option($size.'_size_h');
		}

		if(!$width && !$height) {
			return false;
		}
		elseif(!$width) {
			$width = $height;
		}
		elseif(!$height) {
			$height = $width;
		}

		return 'http://fakeimg.pl/'.$width.'x'.$height.'/999/fff'.($default_image !== true ? '?text='.$default_image : '');
	}

	return false;
}




/**
 * Inverse le jour et l'année dans une date (pour passer d'une date MySQL à une date europeene)
 */
function mbc_revert_date($date, $separator = '-')
{
	if(!preg_match('#^([0-9]{2,4})[\-/]+([0-9]{2})[\-/]+([0-9]{2,4})$#', $date, $matches)) {
		return $date;
	}

	$new_date = $matches[3].$separator.$matches[2].$separator.$matches[1];

  return $new_date;
}






/**
 * Construit une liste d'option pour un <select> simple ou multiple. Usage :
 * $list : array simple ou multidimensionnel (pour optgroup)
 *         si un clé commence par '_', elle sera desactivée (disable)
 * $selected : string ou array. Mettre vide '' pour désactiver la selection automatique
 * $default : première valeur. Si "null", elle ne s'affiche pas
 *
 * Bug potentiel : si $selected == '', alors valeur 0 sera selectionnée car 0 == ''
 */
function mbc_get_select($list, $selected='', $default='---')
{
	// première valeur
  $ret = ($default !== null) ? '<option value="">'.$default.'</option>' : '';

  // option(s) selectionnée(s) / ajouter (string) permet d'éviter les bugs lié au 0
  if(is_array($selected))
  {
		foreach($selected as $key=>$val) {
			$selected[$key] = (string)$val;
		}
  }
  // si $selected est une chaine de caratere, on la transforme en tableau
  elseif($selected !== '') {
		$selected = array((string)$selected);
  }



	foreach($list as $key => $val)
	{
		// tableau à 2 dimensions : on utilise optgroup
		if(is_array($val))
		{
			$ret .= '<optgroup label="'.esc_attr($key).'">';
			foreach($val as $subkey=>$subval)
			{
		    // désactivation des champs dont la clé commence par '_'
		    $option = (substr($subkey, 0, 1) == '_') ? ' disabled="disabled"' : '';

		    // selection du champ selectionné
		    $option .= ($selected !== '' && in_array($subkey, $selected)) ? ' selected="selected"' : '';

    		$ret .= '<option value="'.$subkey.'"'.$option.'>'.$subval.'</option>';
			}
			$ret .= '</optgroup>';
		}
		else
		{
	    // désactivation des champs dont la clé commence par '_'
	    $option = (substr($key, 0, 1) == '_') ? ' disabled="disabled"' : '';

	    // selection du champ selectionné
	    $option .= ($selected !== '' && in_array($key, $selected)) ? ' selected="selected"' : '';

    	$ret .= '<option value="'.$key.'"'.$option.'>'.$val.'</option>';
		}
	}

  return $ret;
}






/**
 *
 */
function mbc_get_html_link($label, $url, $target='', $title='', $aAttribut=array())
{
  if(!empty($url))
  {
    // corrige l'URL s'il manque le http://
    $url = (substr($url, 0, 4) == 'www.') ? 'http://'.$url : $url;

    // cible du lien
    $attribut = (!empty($target)) ? ' target="'.$target.'"' : '';

    // title du lien
    $attribut .= (!empty($title)) ? ' title="'.str_replace('"', '&quot;', $title).'"' : '';

    // attributs du lien
    foreach($aAttribut as $key=>$val) {
      $attribut .= ' '.$key.'="'.str_replace('"', '&quot;', $val).'"';
    }

    $return = '<a href="'.$url.'"'.$attribut.'>'.$label.'</a>';
  }
  else
  {
    $return = $label;
  }

  return $return;
}






/**
 * Récupère le post_id à partir d'un repeater_key
 */
function mbc_get_post_id_from_key($repeater_key)
{
	// vérifie le repeater_key
	if(!preg_match('/^([0-9]+)_([0-9]+)$/', $repeater_key, $matches)) {
		return null;
	}

	return $matches[1];
}



/**
 * Récupère la valeur d'un repeater basé sur sa clé (repeater_key)
 * Retourne un tableau php ou null si aucun résultat n'est trouvé
 */
function mbc_get_post_meta_repeater($meta_key, $repeater_key)
{
	global $wpdb;

	$post_id = mbc_get_post_id_from_key($repeater_key);

	if(!$post_id)	{
		return null;
	}

	$data = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '$post_id' AND meta_key = '".esc_sql($meta_key)."' AND meta_value LIKE '%s:12:\"repeater_key\";s:".strlen($repeater_key).":\"".$repeater_key."\";%'");

	if($data) {
		$data = unserialize($data);
	}

	return $data;
}



/**
 * Modifie la valeur d'un champ à l'intérieur d'un repeater
 */
function mbc_update_post_meta_repeater($meta_key, $repeater_key, $field_key, $field_value)
{
	global $wpdb;

	$data = mbc_get_post_meta_repeater($meta_key, $repeater_key);

	if(!$data) {
		return false;
	}

	$post_id = mbc_get_post_id_from_key($repeater_key);

	$meta_id = $wpdb->get_var("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = '$post_id' AND meta_key = '".esc_sql($meta_key)."' AND meta_value LIKE '%s:12:\"repeater_key\";s:".strlen($repeater_key).":\"".$repeater_key."\";%'");

	if(!$meta_id) {
		return false;
	}

	// remplace les données
	$data[$field_key] = $field_value;

	// maj de la base
	$affected_row = $wpdb->query($wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_id = %d", serialize($data), $meta_id));

	return $affected_row;
}





/**
 * Déclare un post_type avec des paramètres par défaut
 */
function mbc_register_post_type($post_type, $label_plural, $args, $is_female=false)
{
	global $pagenow;

	// on verifie que le post_type n'existe pas déjà
	if (post_type_exists($post_type) === true) {
		return false;
	}

	// label du post_type au singulier
	$label = (isset($args['labels']['singular_name'])) ? $args['labels']['singular_name'] : substr($label_plural, 0, -1);

	// paramètres par défaut
	$default_labels = array(
		'name' => $label_plural,
		'singular_name' => $label,
		'menu_name' => $label_plural,
		'all_items' => 'Liste',
		'add_new' => 'Nouveau',
		'add_new_item' => 'Ajouter un nouveau '.strtolower($label),
		'edit_item' => 'Modifier un '.strtolower($label), // the edit item text. Default is Edit Post/Edit Page
		'new_item' => 'Nouveau '.strtolower($label),
		'view_item' => 'Voir',
		'search_items' => 'Chercher un '.strtolower($label),
		'not_found' => 'Aucun '.strtolower($label).' trouvé.',
		'not_found_in_trash' => 'Aucun '.strtolower($label).' trouvé dans la corbeille.', // the not found in trash text. Default is No posts found in Trash/No pages found in Trash
		//'parent_item_colon' => '', // the parent text. This string isn't used on non-hierarchical types. In hierarchical ones the default is Parent Page
	);

	// met au feminin
	if($is_female)
	{
		foreach($default_labels as $key => $val) {
			$default_labels[$key] = str_replace(array(' un ', ' nouveau', 'Nouveau ', 'Aucun ', ' trouvé'), array(' une ', ' nouvelle', 'Nouvelle ', 'Aucune ', ' trouvée'), $val);
		}
	}

	// surcharge les paramètres de lable par défaut avec ceux spécifiés
	if(isset($args['labels']))
	{
		foreach ($args['labels'] as $key => $val) {
			$default_labels[$key] = $val;
		}
	}

	unset($args['labels']);


	$default_args = array(
		'labels' => $default_labels,
		'public' => true,
		'show_ui' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'rewrite' => true,
		'query_var' => false,
		'supports' => array('title', 'editor', 'author', 'thumbnail'),
		//'hide_editor' => false,
	);

	// surcharge les paramètres par défaut avec ceux spécifiés
	foreach ($args as $key => $val) {
		$default_args[$key] = $val;
	}

	// A SUPPRIMER : masque l'éditeur WYSIWYG en CSS : il ne faut pas le desactiver via register_post_type, sinon la mediatheque dans metabox ne fonctionne plus
	//if($default_args['hide_editor'] ) {
	//	add_action('mbc_hide_box_'.$post_type, function() { add_action('admin_head', function() { echo '<style type="text/css">#postdivrich { display:none; }</style>'.PHP_EOL; }); });
	//}

	// enregistre le nouveau type
	return register_post_type($post_type, $default_args);
}






/**
 * Génére un pager avec plus d'option
 */
function mbc_get_html_pager($nb_result, $nb_per_page, $current_page, $args=array())
{
  $pager = '';

	// paramètre par défaut
  $args_default = array(
		'prev' => '<li class="previous"><a href="%URL%">«</a></li>',
		'next' => '<li class="next"><a href="%URL%">»</a></li>',
		'current' => '<li class="current">%PAGE_NUMBER%</li>',
		'max_item' => 0, // nombre d'item à afficher avant et apres la page courante
		'first_last' => 0, // nombre d'item à afficher apres "Précedent" et avant "Suivant"
		'param_name' => 'page', // "page" ou "paged"
	);
  $args = array_merge($args_default, $args);


  // calcul le nombre de page total
  $nb_page = ceil($nb_result / $nb_per_page);

	// permet de faire un pager avec x*2+1 nombre affiché
	$start = ($args['max_item'] == 0 || $current_page <= $args['max_item']) ? 1 : ($current_page - $args['max_item']);
	$stop = ($args['max_item'] == 0 || $current_page + $args['max_item'] >= $nb_page) ? $nb_page : ($current_page + $args['max_item']);

	// si pas de pager
  if($nb_page <= 1) return;



	// construction du pager
  $pager = ($current_page > 1) ? str_replace('%URL%', mbc_update_param_url(array($args['param_name']=>$current_page-1)), $args['prev']) : '';

	if($args['max_item'] != 0 && $start != 1)
	{
	  // affiche x page juste apres "Précédent"
		if($args['first_last'] != 0 && ($current_page - $args['max_item'] - 1) > $args['first_last'])
		{
			for($i=1; $i<=$args['first_last']; $i++) {
    		$pager .= '<li><a href="'.mbc_update_param_url(array($args['param_name']=>$i)).'">'.$i.'</a></li>';
			}
		}

		// ajout les ...
	  $pager .= '<li class="pager_dot">...</li>';
	}

  for($i=$start; $i<=$stop; $i++) {
    $pager .= ($current_page == $i) ? str_replace('%PAGE_NUMBER%', $i, $args['current']) : '<li><a href="'.mbc_update_param_url(array($args['param_name']=>$i)).'">'.$i.'</a></li>';
  }

	if($args['max_item'] != 0 && $stop != $nb_page)
	{
	  $pager .= '<li class="pager_dot">...</li>';

	  // affiche x page juste avant "Suivant"
		if($args['first_last'] != 0 && ($current_page + $args['max_item'] + $args['first_last']) < $nb_page)
		{
			for($i=($nb_page-$args['first_last']+1); $i<=$nb_page; $i++) {
    		$pager .= '<li><a href="'.mbc_update_param_url(array($args['param_name']=>$i)).'">'.$i.'</a></li>';
			}
		}

	}

  $pager .= ($current_page != $nb_page) ?  str_replace('%URL%', mbc_update_param_url(array($args['param_name']=>$current_page+1)), $args['next']) : '';


  return $pager;
}


/**
 * Fonction de debug, equivalente à print_r (la couleur en plus)
 */
if(!function_exists('tt'))
{
	function tt($array, $simple=false)
	{
	  $debug = print_r($array, 1);

	  if($simple !== false) {
	    return $debug;
	  }
	  else
	  {
			// ajoute la couleur
			$debug = str_replace('Object', '<font color="red">Object</font>', $debug);
			$debug = str_replace('Array', '<font color="#FFCC33">Array</font>', $debug);
			$debug = str_replace('[', '[<font color="blue">', $debug);
			$debug = str_replace(']', '</font>]',$debug);

			// ajoute une police avec largeur fixe
	    $debug = '<pre style="background-color:#fff; font-size:11px;">'.$debug.'</pre>';

			echo $debug;
	  }
	}
}




/**
 * Tronque un chaine à $max caractères sans couper le dernier mot
 */
function mbc_str_truncate($str, $max, $continue_str='...')
{
  if (strlen($str)>$max)
  {
    $str = substr(strip_tags($str), 0, $max);
    $i = max(strrpos($str, ' '), strrpos($str, '-'));
    if ($i>0) {
      $str = substr($str, 0, $i);
    }
    $str .= ' '.$continue_str;
  }
  return $str;
}



/**
 * Ajoute, remplace ou supprime des paramètres dans une URL
 *
 * $param => array des couples "key"=>"val" a mettre dans l'URL. Si val est vide, key est supprimé de l'url
 * $url => URL à mettre à jour. Si vide, utilise $_SERVER['REQUEST_URI']
 *
 * A NE PLUS UTILISER : à remplacer par la fonction wordpress add_query_arg() ?
 */
function mbc_update_param_url($new_param, $url = '')
{
  // essaye de récupèrer l'url courante si $url est vide
  $url = (empty($url) && !empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : $url;

  // récupere la QUERY_STRING
  $explode = explode('?', $url);

  $new_url = $explode[0]; // url sans la query string
  $query_string = (!empty($explode[1])) ? $explode[1] : '';


  // scinde les paramètres de l'url dans le tableau $current_param
  parse_str($query_string, $current_param);

  // on écrase les parametres courant par ceux du tableau
  $merge = array_merge($current_param, $new_param);

  foreach ($merge as $key => $p) {
    if ($p===null || $p == '') unset($merge[$key]);
  }
  // on ajoute la nouvelle query string (si elle existe) à la fin de l'url
  $new_url .= (count($merge) > 0) ? '?'.http_build_query($merge) : '';

  return esc_url($new_url);
}









function mbc_get_term_label($id, $taxonomy)
{
	$term = get_term_by('id', $id, $taxonomy);

	return (isset($term->name)) ? $term->name : '';
}





function mbc_get_user_list($args=array(), $field='display_name')
{
	$args_default = array(
		//'number' => -1,
		'fields' => array('ID', $field),
	);

  $args = array_merge($args_default, $args);

	$user_query = new WP_User_Query($args);

	$list_user = array();
	if(!empty($user_query->results))
	{
		foreach($user_query->results as $user) {
			$list_user[$user->ID] = $user->$field;
		}
	}

	return $list_user;
}





/**
 * Duplique un post
 */
function mbc_duplicate_post($post_id, $status='draft')
{
	global $wpdb;

	$post = get_post($post_id);

	if(!isset($post) || $post == null || !current_user_can('edit_posts'))
		return false;

	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;

	$my_post = array(
		'post_author' => $new_post_author,
		'post_excerpt' => $post->post_excerpt,
		'post_name' => $post->post_name.'-1',
		'post_status' => $status,
		'post_title' => $post->post_title.' - copie',
		'post_type' => $post->post_type,
	);
	$new_post_id = wp_insert_post($my_post);


	// récupère tous les posts meta
	$post_meta_data = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d ORDER BY meta_id ASC", $post_id));

	if(count($post_meta_data) != 0)
	{
		foreach ($post_meta_data as $meta_info)
		{
			$meta_value = maybe_unserialize($meta_info->meta_value);

			// on change les repeater_key avec le nouveau post_id
			if(isset($meta_value['repeater_key']))
			{
				$meta_value['repeater_key'] = str_replace($post_id.'_', $new_post_id.'_', $meta_value['repeater_key']);
			}

			$meta_value = apply_filters('mbc_change_value_duplicate_post', $meta_value, $meta_info->meta_key, $new_post_id, $post_id);

			add_post_meta($new_post_id, $meta_info->meta_key, $meta_value);
		}
	}

	return $new_post_id;
}



/**
 * Permet l'upload d'une image depuis le front-office
 */
function mbc_upload_image($upload, $post_id=null)
{
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$uploads = wp_upload_dir();

	// check if upload dir is writable
	if(!is_writable($uploads['path']))
		return false;

	if(!isset($_FILES[$upload]))
		return false;

	// check if uploaded image is not empty
	if(empty($_FILES[$upload]['tmp_name']))
		return false;

	// error append with file uplaod
	if($_FILES[$upload]['error'] != 0)
		return false;

	// check if is image
	if(!file_is_displayable_image($_FILES[$upload]['tmp_name']))
		return false;

	add_filter('wp_handle_upload', 'mbc_fix_image_orientation', 1, 3);

	// upload the image
	$file = media_handle_upload($upload, $post_id);

	return $file;
}
