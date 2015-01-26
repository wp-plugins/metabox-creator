<?php



function custom_metabox_admin()
{
	$type_data = array(
		'input' => array('input', 'Champ "Input" de type "text"'),
		'number' => array('number', 'Champ "Input" de type "number"'),
		'email' => array('email', 'Champ "Input" de type "email"'),
		'url' => array('url', 'Champ "Input" de type "url"'),
		'textarea' => array('textarea', 'Champ "Textarea" simple'),
		'wysiwyg' => array('wysiwyg', 'Champ "WYSIWYG" basé sur TinyMCE de WordPress. Utilise ob_start() : cela peut provoquer des problèmes sur certains serveurs. Bug connu : l\'éditeur se bloque en cas de drag&drop.'),
		'radio' => array('radio', 'Champs "Radio"'),
		'select' => array('select', 'Champ "Select" simple'),
		'select_multiple' => array('select_multiple', 'Champ "Select" multiple utilisant le script Select2'),
		'boolean' => array('boolean', 'Champ "Checkbox" unique. Utiliser le champ "description" pour écrire à droite de la checkbox. Enregistre en base la valeur 1 s\'il est coché.'),
		'date' => array('date', 'Champ "Datepicker" utilisant jQuery UI'),
		'color' => array('color', 'Champ "Colorpicker" utilisant minicolors.js'),
		'image' => array('image', 'Champ "Image"'),
		'file' => array('file', ''),
		'gallery' => array('gallery', 'Gallery'),
		'website' => array('website', 'Permet de saisir une URL ou de choisir parmis une liste de page'),
		'map' => array('map', 'Champ Map utilisant Google Map'),
		'subfield' => array('subfield', 'Une sorte de repeater simplifié'),
		'repeater' => array('repeater', 'Repeater. Si option ne contient qu\'un champ, et que le tableau et triable, alors les données s\'enregistre à plat dans le tableau'),
	);
	
	foreach($type_data as $key=>$val) {
		$type_data[$key] = $val[0];
	}
	$placeholder = array('type'=>'input', 'label'=>'Instructions', 'description'=>'Affichée à l\'intérieur du champ lorsqu\'il n\'est pas remplis');
	
	$field = array(
		'label' => array('type'=>'input', 'label'=>'Titre', 'description'=>'Affiché dans la colonne de gauche', 'repeater_title'=>true, 'required'=>true),
		'id' => array('type'=>'input', 'label'=>'Identifiant unique', 'description'=>'&nbsp;', 'pattern'=>'^[a-zA-Z0-9_\-]+$', 'required'=>true),
		'type' => array('type'=>'select', 'label'=>'Type de champ', 'option'=>$type_data, 'required'=>true),
		'description' => array('type'=>'input', 'label'=>'Description', 'description'=>'Affichée à droite ou sous le champ'),
		'required' => array('type'=>'boolean', 'label'=>'Obligatoire', 'description'=>'Champ obligatoire (ne fonctionne pas avec les type "wysiwyg" et "gallery")'),
		'default' => array('type'=>'input', 'label'=>'Valeur par défaut'),
		'multiple' => array('type'=>'boolean', 'label'=>'Champ multiple', 'description'=>'Permet de dupliquer ce champ'),
		'full_width' => array('type'=>'boolean', 'label'=>'Full width', 'description'=>'Le champ occupe toute la largeur du tableau. Label et description s\'affichent au dessus'),
		'class' => array('type'=>'input', 'label'=>'Class CSS', 'description'=>'Classe(s) à appliquer au champ'),
		'synchronize' => array('type'=>'radio', 'label'=>'Synchronize', 'option'=>array('Non', 'Synchroniser avec le post master', 'Synchroniser ce post_id avec le post_master')),
	
		// input
		'input_maxlength' => array('type'=>'number', 'label'=>'Maximum', 'description'=>'Longueur maximum du champ', 'filter'=>array(array('id'=>'type', 'value'=>'input'))),
		'input_pattern' => array('type'=>'input', 'label'=>'Pattern', 'description'=>'Expression régulière à laquelle doit correspondre la valeur du champ. Ex : [0-9]{5} pour un code postal', 'filter'=>array(array('id'=>'type', 'value'=>'input'))),
		'input_width' => array('type'=>'input', 'label'=>'Taille du champ', 'description'=>'Exprimé en taille CSS (ex : 150px ou 80%). Vaut 50% si description n\'est pas vide', 'width'=>'100px',  'placeholder'=>'100%', 'filter'=>array(array('id'=>'type', 'value'=>'input'))),
		'input_placeholder' => $placeholder + array('filter'=>array(array('id'=>'type', 'value'=>'input'))),
		
		// number
		'number_step' => array('type'=>'number', 'label'=>'Step', 'description'=>'Interval entre chaque option', 'placeholder'=>'1', 'filter'=>array(array('id'=>'type', 'value'=>'number'))),
		'number_min' => array('type'=>'number', 'label'=>'Valeur minimum', 'description'=>'Nombre minimum possible', 'placeholder'=>'0', 'filter'=>array(array('id'=>'type', 'value'=>'number'))),
		'number_max' => array('type'=>'number', 'label'=>'Valeur maximum', 'description'=>'Nombre maximum possible', 'min'=>'', 'max'=>'', 'step'=>'', 'filter'=>array(array('id'=>'type', 'value'=>'number'))),
		'number_width' => array('type'=>'input', 'label'=>'Taille du champ', 'description'=>'Exprimée en taille CSS. Ex : 150px ou 80%', 'width'=>'100px', 'placeholder'=>'75px', 'filter'=>array(array('id'=>'type', 'value'=>'number'))),
		'number_placeholder' => $placeholder + array('filter'=>array(array('id'=>'type', 'value'=>'number'))),

		// email
		'email_placeholder' => $placeholder + array('filter'=>array(array('id'=>'type', 'value'=>'email'))),
				
		// url
		'url_placeholder' => $placeholder + array('filter'=>array(array('id'=>'type', 'value'=>'url'))),
				
		// textarea
		'textarea_rows' => array('type'=>'number', 'label'=>'Nombre de ligne', 'placeholder'=>'5', 'filter'=>array(array('id'=>'type', 'value'=>'textarea'))),
		'textarea_width' => array('type'=>'input', 'label'=>'Taille du champ', 'width'=>'100px', 'description'=>'Taille du champ exprimée en taille CSS. Ex : 150px ou 80%', 'placeholder'=>'100%', 'filter'=>array(array('id'=>'type', 'value'=>'textarea'))),
		'textarea_placeholder' => $placeholder + array('filter'=>array(array('id'=>'type', 'value'=>'textarea'))),
		
		// wysiwyg
		'wysiwyg_rows' => array('type'=>'number', 'label'=>'Nombre de ligne', 'description'=>' ', 'placeholde'=>'5', 'filter'=>array(array('id'=>'type', 'value'=>'wysiwyg'))),
		'wysiwyg_media_button' => array('type'=>'boolean', 'label'=>'Bouton média', 'description'=>'Afficher le bouton "Ajouter un media"', 'filter'=>array(array('id'=>'type', 'value'=>'wysiwyg'))),
		'wysiwyg_full_editor' => array('type'=>'boolean', 'label'=>'Editeur complet', 'description'=>'Utiliser l\'editeur complet avec la deuxième ligne des boutons', 'false', 'filter'=>array(array('id'=>'type', 'value'=>'wysiwyg'))),

		// radio
		'radio_option' => array('type'=>'textarea', 'label'=>'Option', 'description'=>'Si option est une string, la liste du select contiendra toutes les enregistrement du post_type "option". Possibilité d\'utiliser un tableau à 2 niveau : le premier niveau sera utilisé comme regroupement.', 'None', 'filter'=>array(array('id'=>'type', 'value'=>'radio'))),
		'radio_post_type' => array('type'=>'select', 'label'=>'Post type', 'option'=>get_post_types(), 'filter'=>array(array('id'=>'type', 'value'=>'radio'))),
		
		// select
		'select_option' => array('type'=>'textarea', 'label'=>'Option', 'description'=>'Si option est une string, la liste du select contiendra toutes les enregistrement du post_type "option". Possibilité d\'utiliser un tableau à 2 niveau : le premier niveau sera utilisé comme regroupement.', 'None', 'filter'=>array(array('id'=>'type', 'value'=>'select'))),
		'select_select2' => array('type'=>'boolean', 'label'=>'Select2', 'description'=>'Activer le script "Select2" sur ce champ', 'filter'=>array(array('id'=>'type', 'value'=>'select'))),
	
		// select_multiple
		'select_multiple_option' => array('type'=>'textarea', 'label'=>'Option', 'description'=>'Si option est une string, la liste du select contiendra toutes les enregistrement du post_type "option". Possibilité d\'utiliser un tableau à 2 niveau : le premier niveau sera utilisé comme regroupement.', 'None', 'filter'=>array(array('id'=>'type', 'value'=>'select_multiple'))),
		'select_multiple_select2' => array('type'=>'boolean', 'label'=>'Select2', 'description'=>'Activer le script "Select2" sur ce champ (recommandé)', 'default'=>'1', 'filter'=>array(array('id'=>'type', 'value'=>'select_multiple'))),
	
		// boolean
	
		// date
		'date_placeholder' => $placeholder + array('filter'=>array(array('id'=>'type', 'value'=>'date'))),
	
		// color
		'color_placeholder' => $placeholder + array('filter'=>array(array('id'=>'type', 'value'=>'color'))),
	
		// image
		
		// file
	
		// gallery 
		
		// website
		'website_option' => array('type'=>'textarea', 'Si option est une string, la liste du select contiendra toutes les enregistrement du post_type "option". Possibilité d\'utiliser un tableau à 2 niveau : le premier niveau sera utilisé comme regroupement.', 'page', 'filter'=>array(array('id'=>'type', 'value'=>'website'))),
		'website_default_label' => array('type'=>'input', 'Label de l\'option par défaut.', '---', 'filter'=>array(array('id'=>'type', 'value'=>'website'))),
		'website_placeholder' => array('type'=>'input', 'Description affichée à l\'intérieur de l\'input "Titre"', 'Titre du lien', 'filter'=>array(array('id'=>'type', 'value'=>'website'))),
	
	
		// map
		'map_lat' => array('type'=>'number', 'label'=>'Latitude', 'description'=>'Latitude par défaut du marker', 'placeholder'=>'47', 'filter'=>array(array('id'=>'type', 'value'=>'map'))),
		'map_lng' => array('type'=>'number', 'label'=>'Longitude', 'description'=>'Longitude par défaut du marker', 'placeholder'=>'2', 'filter'=>array(array('id'=>'type', 'value'=>'map'))),
		'map_zoom' => array('type'=>'number', 'label'=>'Zoom', 'description'=>'Zoom par défaut de la carte', 'placeholder'=>'5', 'filter'=>array(array('id'=>'type', 'value'=>'amp'))),
		'map_related' => array('type'=>'input', 'label'=>'Related', 'description'=>'ID d\'un champ "address" : permet de remplir automatiquement les champs adresse, code postal, ville et pays à partir de la recherche Google Places. Il faut avoir créer au préalable un champ ID de type "address"', 'None', 'filter'=>array(array('id'=>'type', 'value'=>'map'))),
	
		// subfield
		'subfield_option' => array('type'=>'repeater', 'label'=>'Champs', 'description'=>'Tableau contenant la liste des champs du repeater', 'filter'=>array(array('id'=>'type', 'value'=>'subfield')), 'option'=>array()),
	
		// repeater
		'repeater_view_item' => array('type'=>'number', 'label'=>'Items visibles', 'description'=>'Nombre d\'item à afficher par défaut', 'placeholder'=>'1', 'filter'=>array(array('id'=>'type', 'value'=>'repeater'))),
		'repeater_max_item' => array('type'=>'number', 'label'=>'Max item', 'description'=>'Nombre d\'item maximum affichable', 'filter'=>array(array('id'=>'type', 'value'=>'repeater'))),
		'repeater_button_label' => array('type'=>'input', 'label'=>'Label du bouton', 'description'=>'', 'placeholder'=>'Ajouter', 'filter'=>array(array('id'=>'type', 'value'=>'repeater'))),
		'repeater_inline' => array('type'=>'boolean', 'label'=>'Inline', 'description'=>'Afficher tous les champs sur une ligne', 'filter'=>array(array('id'=>'type', 'value'=>'repeater'))),
		'repeater_option' => array('type'=>'repeater', 'label'=>'Blocs', 'filter'=>array(array('id'=>'type', 'value'=>'repeater')), 'option'=>array()),
	);
	
	$field['subfield_option']['option'] = $field['repeater_option']['option'] = $field;
	unset($field['subfield_option']['option']['type']['option']['subfield']);
	unset($field['subfield_option']['option']['type']['option']['repeater']);
	unset($field['repeater_option']['option']['type']['option']['subfield']);
	unset($field['repeater_option']['option']['type']['option']['repeater']);
	//$field['subfield_option']['option']['subfield_option']['option'] = $field['repeater_option']['option']['repeater_option']['option'] = $field;
	//unset($field_list_v2['subfield_option']['option']['subfield_option']['option']);
	$field = array('field'=>array('type'=>'repeater', 'label'=>'', 'toggle'=>true, 'full_width'=>true, 'option'=>$field));

	mbc_create($field, 'mbc', array('title'=>'Champs de la Meta Box'));
}
add_action('mbc_init', 'custom_metabox_admin');



