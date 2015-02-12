<?php
/*
Plugin Name: MetaBox Creator
Plugin URI: http://www.metaboxcreator.com
Description: Advanced tool to easily create meta boxes.
Version: 1.0
Author: Paul-Henri Chelle


***** TODO *****
v1.1 : Prioritaire
 - BUG : plusieurs metabox / travailler sur la methode d'appel
 - BUG : modifier le type "number" : http://decorplanit.com/plugin/ (à cause du bug chrome)
 - BUG : le champ carte doit s'appeler "map"
 - TODO : renommer les fonctions
 - EDIT : utiliser un seul get_post_meta($post_id) pour simplifier le code (via la method field_value)
 - EDIT : mettre un margin-left:0 sur la class description et error
 - ADD : class half-width pour les input
 - ADD : Tri hierarchique des page dans le menu select de "website" et de post_type = page
 - ADD : class CSS jquery ui sortable http://blog.arnaud-k.fr/2010/09/29/tutorial-jquery-trier-une-liste-en-dragndrop-avec-jqueryui-sortable/ et http://www.hongkiat.com/blog/jquery-ui-sortable/ et
 - ADD : gestion de l'enregistrement d'une synchro si is_post_id = true && son equivalent n'existe pas dans la langue


v1.2 : Evolutions
 - BUG : verification de pattern avec jQuery Validate
 - BUG : alignement colone repeater inline
 - BUG : le param "default" ne fonctionne pas dans les repeaters en mode edition
 - TODO : s'inspirer d'ACF et de metabox : http://wordpress.org/plugins/meta-box/ and http://wordpress.org/plugins/custom-field-template/
 - EDIT : verifier le bon fonctionnement de image et gallery avec synchronize et WPML
 - EDIT : verifier le bon fonctionnement avec synchronize, record_id et multiple
 - ADD : checknonce & security
 - ADD : Installer oEmbedd
 - ADD : Faire fonctionner "lang" sans WPML
 - ADD : types de champs : checkbox / datetime / time / html / submit
 - ADD : affichage conditionnel d'un champ : acf.conditional_logic.items.push({"status":1,"rules":[{"field":"field_52fa4afeeaa51","operator":"==","value":"ezrrezrzer"},{"field":"field_52fa4afeeaa51","operator":"==","value":"zddssf"}],"allorany":"any","field":"field_53024d8b7eb0c"});
 - ADD : mediatheque : filtre par format
 - ADD : ajout dynamique de term depuis une fiche
 - ADD : masquer les term box
 - ADD : refaire la fonction d'instanciation de google map
 - ADD : ajouter la fonction de suppression d'une image directement après l'ajout
 - ADD : enregistrer le zoom dans le type map
 - ADD : ajouter une page d'options pour MBC (lang...)

 V2 : non prioritaire
 - TODO : gerer synchronize dans les subrepeater
 - ADD : required sur "gallery"
 - Gestion des conflits avec d'autres script : select2 et ACF5
*/

// TODO : à verifier
//defined('ABSPATH') or die('Direct acces not allowed!');

if(!defined('MBC_PLUGIN_URL')) {
	define('MBC_PLUGIN_URL', plugins_url('/', __FILE__));
}

require_once dirname(__FILE__) . '/utils.php';
require_once dirname(__FILE__) . '/add-action.php';

/**
 * Exemple de tous les champs possibles
 *
$common_field = array(
	'label' => array('string', 'Label affiché dans la colonne de gauche du tableau.', '$id'),
	'description' => array('string', 'Description affiché à droite du champ.', 'None'),
	'required' => array('boolean', 'Indique si un champ est requis. La vérification ne fonctionne pas avec les type image, file, wysiwyg et gallery', 'false'),
	'default' => array('string/array', 'Valeur(s) par défaut du champ.', 'None'),
	'full_width' => array('boolean', 'Le champ occupe toute la largeur du tableau. Le label et la description du champ s\'affiche au dessus', 'false'),
	'class' => array('string', 'Classe(s) à appliquer au champ', 'None'),
	'synchronize' => array('boolean/string', 'Synchronise ce meta lorsque WPML est activé. Ne fonctionne pas pour les repeater de repeater. Pour synchroniser ce meta lorsqu’il s’agit d’un post_id et WPML est activé, mettre ce param à "post_id"', 'false'),
	'repeater_title' => array('boolean', 'MAJ le titre du slide togle avec ce champ', 'false'),
	'lang' => array('boolean/array', 'Multiplie le champ par le nombre de langue. Doit être un tableau contenant la liste des langues (ex: array("fr", "en")) ou peut valoir "true" pour récupérer automatiquement les langues configurée dans WPML. Ne fonctionne pas avec le repeater et le subfield.', 'false'),
	'multiple' => array('boolean', 'Permet de duplique le champs. Enregistrement à plat. Ne fonctionne pas sur les repeater et les subfield', 'false'),
	'no_table' => array('boolean', 'Si multiple vaut true, il est possible d’afficher les champs sans tableau.', 'false'),
	'item' => array('integer', 'Permet de duplique le champs "item" fois. Enregistrement dans un tableau serialisé. Ne fonctionne pas sur les repeater et les subfield', 'false'),
	'flat_record' => array('boolean', 'Enregistre dans post_meta un champ avec l’option "multiple" à true sans le sérialiser. Ne fonctionne que pour les champs de niveau 1', 'true'),
	'filter' => array('array', 'Permet de n\'afficher ce champ que si un champ "radio", "select", "boolean" ou "boolean_checkbox" a une valeur donnée. Attention : pour ne pas avoir de bug, il faut que le champ dont dépent la condition possède un id unique', 'None'),
);

$field_list = array(
	'input ou text' => array(
		'type' => 'Champ "Input" de type "text"',
		'width' => array('string', 'Taille du champ exprimée soit en taille CSS (ex : 150px ou 80%). Si description n\'est pas vide, width vaut 50%', '100%'),
		'pattern' => array('string', 'Expression régulière à laquelle doit correspondre la valeur du champ. Ex : [0-9]{5} pour un code postal', 'None'),
		'maxlength' => array('integer', 'Longueur maximum du champ', 'None'),
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input', 'None'),
	),

	'number' => array(
		'type' => 'Champ "Input" de type "number"',
		'step' => array('integer', 'Interval entre chaque option', '1'),
		'min' => array('integer', 'Nombre minimum possible', '0'),
		'max' => array('integer', 'Nombre maximum possible', 'None'),
		'width' => array('string', 'Taille du champ exprimée soit en taille CSS. Ex : 150px ou 80%', '75px'),
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input', 'None'),
	),

	'email' => array(
		'type' => 'Champ "Input" de type "email"',
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input', 'None'),
	),

	'url' => array(
		'type' => 'Champ "Input" de type "url"',
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input', 'None'),
	),

	'hidden' => array(
		'type' => 'Champ "Input" de type "hidden"',
	),

	'textarea' => array(
		'type' => 'Champ "Textarea" sans WISIWYG',
		'rows' => array('integer', 'Nombre de ligne sur le textarea', '5'),
		'width' => array('string', 'Taille du champ exprimée soit en taille CSS. Ex : 150px ou 80%', '100%'),
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input. TODO : A vérifier', 'None'),
	),

	'wysiwyg' => array(
		'type' => 'Champ "WYSIWYG" basé sur TinyMCE de WordPress. Utilise ob_start() : cela peut provoquer des problèmes sur certains serveurs. Bug connu : l\'éditeur se bloque en cas de drag&drop.',
		'rows' => array('integer', 'Nombre de ligne sur le textarea', '5'),
		'media_button' => array('boolean', 'Afficher le bouton "Ajouter un média"', 'false'),
		'full_editor' => array('boolean', 'Utiliser l\'editeur complet avec la deuxième ligne des boutons', 'false'),
	),

	'radio' => array(
		'type' => 'Champs "Radio"',
		'option' => array('array', 'Tableau associtif clé / valeur. Possibilité d\'utiliser un tableau à 2 niveau : le premier niveau sera utilisé comme regroupement.', 'None'),
		'post_type' => array('string', 'Liste tous les éléments du post_type "post_type" trié par titre', 'None'),
		'taxonomy' => array('string', 'Liste tous les éléments de la taxonomy "post_type" trié par titre', 'None'),
		'option_id' => array('string', 'ID de l’option (multiple=>"option") défini dans les options du site.', 'None'),
	),

	'select' => array(
		'type' => 'Champ "Select" simple',
		'option' => array('array', 'Tableau associtif clé / valeur. Possibilité d\'utiliser un tableau à 2 niveau : le premier niveau sera utilisé comme regroupement.', 'None'),
		'post_type' => array('string', 'Liste tous les éléments du post_type "post_type" trié par titre', 'None'),
		'taxonomy' => array('string', 'Liste tous les éléments de la taxonomy "post_type" trié par titre', 'None'),
		'option_id' => array('string', 'ID de l’option (multiple=>"option") défini dans les options du site.', 'None'),
		'select2' => array('boolean', 'Permet d\'activer select2 sur le champ select', 'false'),
		'update_term' => array('string', 'Nom de la taxonomy. MAJ de la taxonomy', 'None'),
		'placeholder' => array('string', 'Première valeur du select. Pour ne pas l’avoir, mettre cette valeur à "false"', '---'),
	),

	'select_multiple' => array(
		'type' => 'Champ "Select" multiple utilisant le script Select2',
		'option' => array('array', 'Tableau associtif clé / valeur. Possibilité d\'utiliser un tableau à 2 niveau : le premier niveau sera utilisé comme regroupement.', 'None'),
		'post_type' => array('string', 'Liste tous les éléments du post_type "post_type" trié par titre', 'None'),
		'taxonomy' => array('string', 'Liste tous les éléments de la taxonomy "post_type" trié par titre', 'None'),
		'option_id' => array('string', 'ID de l’option (multiple=>"option") défini dans les options du site.', 'None'),
		'select2' => array('boolean', 'Permet de désactiver select2 sur le select multiple', 'true'),
		'update_term' => array('string', 'Nom de la taxonomy. Synchronise avec la taxonomy mentionnée', 'None'),
	),

	'boolean' => array(
		'type' => 'Champ "Checkbox" unique. Utiliser le champ "description" pour écrire à droite de la checkbox. Enregistre en base la valeur 1 s\'il est coché.',
	),

	'date' => array(
		'type' => 'Champ "Datepicker" utilisant jQuery UI',
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input.', 'None'),
	),

	'datetime' => array(
		'type' => 'Champ "Datepicker" avec l\'heure en plus utilisant jQuery UI',
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input.', 'None'),
	),

	'time' => array(
		'type' => 'Champ "Timepicker" utilisant jQuery UI',
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input.', 'None'),
	),

	'color' => array(
		'type' => 'Champ "Datepicker" utilisant minicolors.js',
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input.', 'None'),
	),

	'image' => array(
		'type' => 'Champ "Image"',
		'update_thumbnail' => array('boolean', 'Met à jour le post thumbnail avec cette image', 'false'),
	),

	'file' => array(
		'type' => 'Champ "Fichier"',
	),

	'gallery' => array(
		'type' => 'Gallery',
	),

	'website' => array(
		'type' => 'Permet de saisir une URL ou de choisir parmi une liste de page',
		'option' => array('array/string', 'Si option est une string, la liste du select contiendra toutes les enregistrement du post_type "option". Possibilité d\'utiliser un tableau à 2 niveau : le premier niveau sera utilisé comme regroupement.', 'page'),
		'default_label' => array('string', 'Label de l\'option par défaut.', '---'),
		'placeholder' => array('string', 'Description affichée à l\'intérieur de l\'input "Titre"', 'Titre du lien'),
	),

	'map' => array(
		'type' => 'Champ Map utilisant Google Map',
		'lat' => array('integer', 'Latitude par défaut du marker', '47'),
		'lng' => array('integer', 'Longitude par défaut du marker', '2'),
		'zoom' => array('integer', 'Zoom par défaut de la carte', '5'),
		'related' => array('string', 'ID d\'un champ "address" : permet de remplir automatiquement les champs adresse, code postal, ville et pays à partir de la recherche Google Places. Il faut avoir créer au préalable un champ ID de type "address"', 'None'),
	),

	'subfield' => array(
		'type' => 'Un sorte de repeater simplifié',
		'option' => array('array', 'Tableau contenant la liste des champs du repeater', 'None'),
	),

	'repeater' => array(
		'type' => 'Repeater. Si option ne contient qu\'un champ, et que le tableau et triable, alors les données s\'enregistre à plat dans le tableau',
		'option' => array('array', 'Tableau contenant la liste des champs du repeater. Si "option" ne contient qu’un champ, il sera enregistré à plat et non dans un tableau serialisé', 'None'),
		'view_item' => array('integer', 'Nombre d\'item à afficher par défaut', '1'),
		'max_item' => array('integer', 'Nombre d\'item maximum affichable', 'false'),
		'block_label' => array('string', 'Label utilisé pour les titre des blocs', 'None'),
		'button_label' => array('string', 'Label du bouton "Ajouter"', 'Ajouter'),
		'inline' => array('boolean', 'Affiche tous les champs du tableau sur une seule ligne', 'false'),
		'record_id' => array('boolean', 'Enregistre l\'identifiant du repeater. Vaut true si synchronize est à true. Ne fonctionne pas pour un subrepeater', 'false'),
		'toggle_closed' => array('boolean', 'Ferme les toggle au chargement du formulaire', 'false'),
	),
);
*/


function register_metabox_creator()
{
	do_action('mbc_init');
	add_action('admin_head', 'mbc_js_init');
}
/*
function execute_metabox_creator()
{
	//echo 'execute<br>';
}
*/
function register_post_type_mbc() {
	mbc_register_post_type('mbc', 'MetaBox Creator', array('labels'=>array('name'=>'Metabox', 'singular_name'=>'Metabox'), 'supports'=>array('title'), 'public'=>false, 'show_ui'=>false), true);
}
add_action('init', 'register_post_type_mbc', 0);


if(is_admin())
{
	add_action('load-post.php', 'register_metabox_creator');
	add_action('load-post-new.php', 'register_metabox_creator');
	//add_action('load-post-new.php', 'execute_metabox_creator', 20);
}

/**
 * Permet l'ajout de code JS dans admin.js
 */
function mbc_js_init()
{
	echo '
	<script type="text/javascript">
		var mbc_options = {};
		jQuery(document).ready(function($) {'.PHP_EOL;
		do_action('mbc_js_init');
		echo '
		});
	</script>';
}





/**
 * Affiche ou non la metabox
 * $field_list (array) : liste des champs à afficher. Voir la liste ci-dessus
 * $display_for (string / array / integer) : Restreint l'affichage à ce paramètre. Peut être un post_type, un tableau de post_type, un post_id, ou une page de template
 * $args (array) : liste des paramètres de la metabox :
 *		$id : identifiant de la metabox. Defaut : box_$post_type
 *		$title : titre de la metabox. Defaut : Options
 *		$context : Position de la metabox : normal / advanced / side. Defaut : advanced
 *		$priority : Ordre d'affichage : high / core / default / low. Defaut : high
 */
function mbc_create($field, $display_for, $args=array())
{
	$mbc = new metabox_creator();
	$mbc->field = $field;
	$mbc->display_for = $display_for;
	$mbc->args = $args;
	$mbc->init();
}


function mbc_create_option($field, $id='mbc_option', $title=null)
{
	$mbc = new metabox_creator();
	$mbc->field = $field;
	$mbc->option_title = (!$title) ? __('Options du site', 'mbc') : $title;
	$mbc->args = array('id'=>$id, 'context' => 'normal', 'priority' => 'core');
	$mbc->option_init();
}







if (!class_exists('metabox_creator'))
{
	class metabox_creator
	{
		public $field;
		public $display_for;
		public $arg = array();
		public $localize = array();


		function __construct() {
		}

		function init()
		{
			global $pagenow, $wpdb;


			if(!is_array($this->display_for)) {
				$this->display_for = array($this->display_for);
			}

			$display_box = false;

			// lors d'un ajout, on ne vérifie que le post_type
			if($pagenow == 'post-new.php')
			{
				$current_post_type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';

				foreach($this->display_for as $val)
				{
					if($current_post_type == $val) {
						$display_box = true;
					}
				}
			}
			// modification des options du site
			elseif($pagenow == 'admin.php' || $pagenow == 'admin-post.php')
			{
				$display_box = true;
				$current_post_type = $this->display_for[0];

				$current_post_id = get_option('mbc_option_post_id');

				// création d'un post type "mbc" pour stocker les valeurs de l'option
				if(empty($current_post_id))
				{
					$current_post_id = wp_insert_post(array('post_title' => $this->option_title, 'post_type'=>'mbc'));
					update_option('mbc_option_post_id', $current_post_id);
				}
			}
			// en cas d'édition
			else
			{
				$current_post_id = (isset($_POST['post_ID']) && isset($_POST['action'])) ? $_POST['post_ID'] : (isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $_GET['post'] : null);
				$current_post_type = get_post_type($current_post_id);

				foreach($this->display_for as $val)
				{
					// pour un post_id
					if(is_numeric($val) && $current_post_id == $val) {
						$display_box = true;
					}
					// pour un template de page
					elseif(substr($val, -4) == '.php' && get_post_meta($current_post_id, '_wp_page_template', true) == $val) {
						$display_box = true;
					}
					// pour un post_type
					elseif($current_post_id && $current_post_type == $val) {
						$display_box = true;
					}
				}
			}


			// aucune metabox à afficher
			if(!$display_box) return;

			// permet l'utilisation d'un add_action lié à un post_type en mode ajout ou edition
			//do_action('mbc_hide_box_'.$current_post_type);

			// paramètres de la metabox et de la page post.php
			$args_default = array(
				'id' => 'box_'.$current_post_type,
				'title' => __('Options', 'mbc'),
				'context' => 'advanced',
				'priority' => 'high',
			);
			$args = array_merge($args_default, $this->args);





			$this->post_id = (!empty($current_post_id)) ? $current_post_id : null;

			// si WPML est activé, on active la gestion du multilangue
			if(function_exists('icl_object_id'))
			{
		 		// lors de l'ajout d'un nouveau post, on récupère l'id du post à partir duquel on va dupliquer les champs
		 		if(isset($_GET['source_lang']) && isset($_GET['trid']) && function_exists('icl_object_id')) {
					$this->translate_master_post_id = $wpdb->get_var($wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type = %s AND trid = %d AND language_code = %s", 'post_'.$current_post_type, $_GET['trid'], $_GET['source_lang']));
				}
			}

			// enrichie la liste des champs
			$this->field_list = $this->field_list($this->field);

			// recupere les valeurs de tous les metas
			$this->field_value = $this->field_value($this->field_list);

			// afficage de la metabox
			add_meta_box($args['id'], $args['title'], array($this, 'display_html_meta_box'), $current_post_type, $args['context'], $args['priority']);

			// ajoute un classe CSS à la metabox
			add_filter('postbox_classes_'.$current_post_type.'_'.$args['id'], array($this, 'add_metabox_classes'));

			// enregistre les données de la metabox
			if($current_post_type == 'attachment') {
				add_action('edit_attachment', array($this, 'save_metabox_attachment'), 1);
			}
			else {
				add_action('save_post_'.$current_post_type, array($this, 'save_metabox'), 1	, 3);
			}

			// ajout de la CSS et du JS
			wp_enqueue_style('mbcreator', MBC_PLUGIN_URL.'/css/mbcreator.css');
			add_action('admin_footer', array($this, 'admin_footer'));
		}



		/**
		 * Affiche la métabox
		 */
		function display_html_meta_box($post)
		{
			// TODO add an nonce field so we can check for it later.
			//wp_nonce_field('myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce');

			// boucle sur la liste des champs
			echo '<table class="general"><tbody>';
			foreach($this->field_list as $field_id => $field_param) {
				echo $this->mbc_build_form($field_id, $field_param, $post);
			}
			echo '</tbody></table>';
		}



		/**
		 * Reformate le tableau de champs : permet de simplifier la déclaration de la metabox
		 */
		function field_list($field_list, $level=1)
		{
			foreach($field_list as $field_id => $field_param)
			{
				// field "type" mandatory
				if(!isset($field_param['type']) || $field_param['type'] == '') {
					unset($field_list[$field_id]);
				}
				// TODO : verifier qu'il ne faille pas ajouter type == subfield
				elseif($field_param['type'] == 'repeater')
				{
					$sublevel = $level++;
					$field_list[$field_id]['option'] = $this->field_list($field_param['option'], $sublevel);
				}
				else
				{
					// option "flat_record" => enregistre à plat les données (uniquement
					if(/*$field_param['type'] == 'select_multiple' &&*/ (!isset($field_param['flat_record']) || $field_param['flat_record'])) {
						$field_list[$field_id]['flat_record'] = true;
					}
					else {
						unset($field_param['flat_record']);
					}

					// option "lang"
					if(!empty($field_param['lang']) && $field_param['type'] != 'repeater' && $field_param['type'] != 'subfield')
					{
						$field_list[$field_id]['lang_list'] = $field_param['lang'];
						$field_list[$field_id]['lang_type'] = $field_param['type'];
						$field_list[$field_id]['type'] = 'lang';
						unset($field_list[$field_id]['lang']);
					}

					// option "item"
					if(!empty($field_param['item']) && $field_param['type'] != 'repeater' && $field_param['type'] != 'subfield')
					{
						$new_param = array(
							'type' => 'subfield',
							'label' => (!empty($field_param['label']) ? $field_param['label'] : ''),
							'description' => (!empty($field_param['description']) ? $field_param['description'] : ''),
							'required' => !empty($field_param['required']),
							'full_width' => (!empty($field_param['full_width']) ? $field_param['full_width'] : ''),
							'synchronize' => (!empty($field_param['synchronize']) ? $field_param['synchronize'] : ''),
							'filter' => (isset($field_param['filter']) ? $field_param['filter'] : array()),
							'option' => array(),
						);

						$replicate = $field_param;
						unset($replicate['label'], $replicate['description'], $replicate['item'], $replicate['full_width']);

						for($i=1; $i<=$field_param['item']; $i++) {
							$new_param['option'][] = $replicate;
						}
						$field_list[$field_id] = $new_param;
					}

					// option "multiple" ou "is_option"
					if(!empty($field_param['multiple']) && $field_param['type'] != 'repeater')
					{
						$is_option_type = ($field_param['multiple'] === 'option') ? true : false;

						$new_param = array(
							'type' => 'repeater',
							'label' => (!empty($field_param['label']) ? $field_param['label'] : ''),
							'inline' => true,
							'flat_record' => (!$is_option_type && (!empty($field_param['flat_record']) || !isset($field_param['flat_record']) && $level == 1)) ? true : false,
							'description' => (!empty($field_param['description']) ? $field_param['description'] : ''),
							'full_width' => (!empty($field_param['full_width']) ? $field_param['full_width'] : ''),
							'required' => !empty($field_param['required']),
							'synchronize' => (!empty($field_param['synchronize']) ? $field_param['synchronize'] : ''),
							'filter' => (isset($field_param['filter']) ? $field_param['filter'] : array()),
							'no_table' => (!empty($field_param['no_table']) ? $field_param['no_table'] : ($is_option_type ? true : false)),
						);
						unset($field_param['multiple'], $field_param['description'], $field_param['filter']);
						if(isset($field_param['filter'])) { $new_param['filter'] = $field_param['filter']; }
						if(isset($field_param['button_label'])) { $new_param['button_label'] = $field_param['button_label']; }
						$new_param['option'] = ($is_option_type)  ? array('key'=>array('type'=>'text', 'label'=>'Clé unique'), 'val'=>$field_param) : array($field_id=>$field_param);
						$field_list[$field_id] = $new_param;
					}

				}
			}

			return $field_list;
		}





		/**
		 * TODO : ajouter la gestion de synchronize
		 * verifier le besoin de repeater_key
		 */
		function field_value($option, $meta=array(), $level=1)
		{
			$field_value = array();

			if($level == 1)
			{
				// les meta_value du post si on est en mode edition, ou un tableau vide. Si la traduction existe, on recupère ses valeurs
				$meta = ($this->post_id) ? get_post_meta($this->post_id) : (isset($this->translate_master_post_id) ? get_post_meta($this->translate_master_post_id) : array());

				// unserialise le tableau
				foreach($meta as $key => $val)
				{
					foreach($val as $subkey => $subval) {
						$meta[$key][$subkey] = maybe_unserialize($subval);
					}
				}
			}


			foreach($option as $meta_key => $param)
			{
				if($param['type'] == 'repeater')
				{
					$value = (isset($meta[$meta_key]) && ($this->post_id || !empty($param['synchronize']))) ? $meta[$meta_key] : array(0=>null);
					$sublevel = $level+1;
					foreach($value as $key => $val)
					{
						// cas particulier : si le champ multiple est enregistré à plat
						if(!empty($param['flat_record'])) {
							$val = array($meta_key=>$val);
						}
						$field_value[$meta_key][$key] = $this->field_value($param['option'], $val, $sublevel);
					}
				}
				elseif($param['type'] == 'subfield')
				{
					$value = (isset($meta[$meta_key])) ? $meta[$meta_key][0] : null;
					$sublevel = $level+1;
					$field_value[$meta_key] = $this->field_value($param['option'], $value, $sublevel);
				}
				else
				{
					// il existe une valeur pour la meta_key
					if(isset($meta[$meta_key]) && ($this->post_id || !empty($param['synchronize']))) {
						$field_value[$meta_key] = (!empty($param['flat_record']) || $level > 1) ? $meta[$meta_key] : $meta[$meta_key][0];
					}
					// une valeur par défaut a été spécifié
					elseif(isset($param['default'])) {
						$field_value[$meta_key] = $param['default'];
					}
					// on remplis avec une valeur vide (array ou string)
					else {
						$field_value[$meta_key] = (in_array($param['type'], array('select_multiple', 'website', 'gallery', 'address'))) ? array() : '';
					}
				}
			}

			//if($level == 1) tt($field_value);

			return $field_value;
		}




		/**
		 * Construction du formulaire
		 */
		function mbc_build_form($id, $param, $post, $repeater_id=null, $repeater_value=null, $repeater_inline=false)
		{
			global $wpdb, $pagenow;

			$field_name = ($repeater_id) ? 'mbc['.implode('][', $repeater_id).']['.$id.']' : 'mbc['.$id.']';
			$field_id = str_replace(array('[', ']'), array('_', ''), $field_name); // contruit l'id basé sur le field_name. Ex : mbc[repeater][48][description] devient mbc_repeater_48_description
			$type = $param['type'];
			$label = (isset($param['label'])) ? $param['label'] : '';
			$description = (isset($param['description'])) ? $param['description'] : '';
			$option = (isset($param['option'])) ? $param['option'] : array();
			$description_html = ($description) ? '<span class="description">'.$description.'</span>' : '';
			$post_id = (isset($post->ID) ? $post->ID : 'option');

			$attribute = array(
				'id' => $field_id,
				'placeholder' => (isset($param['placeholder']) ? (is_array($param['placeholder']) ? $param['placeholder'] : htmlspecialchars($param['placeholder'])) : ''),
				'style' => (isset($param['width']) ? 'width:'.$param['width'] : ''),
				'class' => (isset($param['class']) ? explode(' ', $param['class']) : array()),
				'required' => (empty($param['required']) ? '' : 'required'),
			);

			if(!empty($param['repeater_title'])) { $attribute['class'][] = 'repeater_title'; }



			// on récupère l'éventuelle valeur par defaut si "Add new"
			if($pagenow == 'post-new.php')
			{
				if(!empty($param['synchronize']) && isset($this->translate_master_post_id))
				{
					if($repeater_value !== null) {
						$value = (isset($repeater_value[$id]) ? $repeater_value[$id] : ($type == 'select_multiple' ? array() : ''));
					}
					else {
						$value = ($type == 'select_multiple' || $type == 'repeater') ? get_post_meta($this->translate_master_post_id, $id) : get_post_meta($this->translate_master_post_id, $id, true);
					}
				}
				else {
					$value = (isset($param['default']) ? $param['default'] : ($type == 'select_multiple' || $type == 'repeater' ? array() : ''));
				}
			}
			// cas particulier : le repeater ou le subfield
			elseif($repeater_value !== null) {
				$value = (isset($repeater_value[$id]) ? $repeater_value[$id] : ($type == 'select_multiple' ? array() : ''));
			}
			// sinon, on récupère la valeur du post_meta
			else {
				$value = ($type == 'select_multiple' || $type == 'repeater') ? get_post_meta($this->post_id, $id) : get_post_meta($this->post_id, $id, true);
			}


			// options
			if(in_array($type, array('select', 'select_multiple', 'radio', 'website')))
			{
				global $wp_taxonomies;

				// TO DEPRECATE : "option" contient une chaine
				if(!is_array($option)) {
					$option_data = mbc_get_list($option);
				}
				elseif(is_array($option) && count($option) > 0) {
					$option_data = $option;
				}
				elseif((isset($param['post_type']) && $param['post_type'] == 'page' && $type == 'select') || ($type == 'website' && !isset($param['option']))) {
					$option_data = mbc_get_list('page');
				}
				elseif(isset($param['post_type']) && post_type_exists($param['post_type'])) {
					$option_data = mbc_get_list($param['post_type']);
				}
				elseif(isset($param['taxonomy']) && taxonomy_exists($param['taxonomy'])) {
					$option_data = mbc_get_terms_list($param['taxonomy']);
				}
				elseif(isset($param['option_id'])) {
					$option_data = mbc_get_option($param['option_id'], true);
				}
				else {
					$option_data = array();
				}
			}



			switch($type)
			{
				case 'input' :
				case 'text' :
					$attribute['pattern'] = (isset($param['pattern'])) ? $param['pattern'] : '';
					$attribute['maxlength'] = (isset($param['maxlength'])) ? $param['maxlength'] : '';
					if(!$attribute['style'] && $description) { $attribute['style'] = 'width:50%'; }
					$field_html = '<input type="text" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;

				case 'number' :
					$attribute['step'] = (isset($param['step'])) ? $param['step'] : '1';
					$attribute['min'] = (isset($param['min'])) ? $param['min'] : '0';
					$attribute['max'] = (isset($param['max'])) ? $param['max'] : '';
					$attribute['class'][] = 'number';
					$field_html = '<input type="number" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;

				case 'email' :
					if(!$attribute['style'] && $description) { $attribute['style'] = 'width:50%'; }
					$field_html = '<input type="email" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;

				case 'url' :
					if(!$attribute['style'] && $description) { $attribute['style'] = 'width:50%'; }
					if(!$attribute['placeholder']) { $attribute['placeholder'] = 'http://'; }
					$field_html = '<input type="url" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;

				case 'hidden' :
					$field_html = '<input type="hidden" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;

				case 'textarea' :
					$attribute['rows'] = (isset($param['rows'])) ? $param['rows'] : '5';
					$field_html = '<textarea name="'.$field_name.'" id="'.$field_id.'" '.$this->get_html_attribute($attribute).'>'.$value.'</textarea>';
					break;

				case 'wysiwyg' :
					$args = array(
						'textarea_name' => $field_name,
						'textarea_rows' => (isset($param['rows']) && in_numeric($param['rows']) ? $param['rows'] : '5'),
						'media_buttons' => (!empty($param['media_button']) ? true : false),
						'teeny' => (!empty($param['full_editor']) ? false : true),
					);
					ob_start();
					wp_editor($value, $field_id, $args);
					$field_html = ob_get_contents();
					ob_end_clean();

					if(!empty($param['required'])) {
						$field_html = str_replace('<textarea ', '<textarea required="required" ', $field_html);
					}
					break;

				case 'tag' :
					wp_enqueue_script('tagsinput.js', MBC_PLUGIN_URL.'/js/jquery.tagsinput.min.js', array('jquery'), '1.3.3.');
					if(!$attribute['style'] && $description) { $attribute['style'] = 'width:50%'; }
					$attribute['class'][] = 'taginput';
					$field_html = '<input type="text" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;

				case 'radio' :
					$field_html = '';
					$attribute['class'][] = 'filter';
					$attribute['filter'] = $id;
					foreach($option_data as $key => $val)
					{
						$attribute['id'] = $field_id.'_'.$key;
						$field_html .= '<input type="radio" name="'.$field_name.'" '.$this->get_html_attribute($attribute).' value="'.esc_attr($key).'"'.($value == $key ? ' checked="checked"' : '').'><label for="'.$attribute['id'].'">'.$val.'</label><br>';
					}
					break;

				case 'select' :
					$default_label = (isset($param['placeholder'])) ? ($param['placeholder'] === false ? null : $param['placeholder']) : '---';
					if(!empty($param['select2'])) {
						$attribute['data-placeholder'] = $default_label;
					}
					unset($attribute['placeholder']);
					$attribute['class'][] = 'filter';
					$attribute['filter'] = $id;

				case 'select_multiple' :
					if(!empty($param['select2']) || (!isset($param['select2']) && $type == 'select_multiple'))
					{
						wp_enqueue_script('select2.js', MBC_PLUGIN_URL.'/js/select2.min.js', array('jquery'), '3.4.6');
						wp_enqueue_style('select2', MBC_PLUGIN_URL.'/css/select2.css', array(), '3.4.6');
						$attribute['class'][] = 'select2';
						if(!$attribute['style'] && $type == 'select_multiple') { $attribute['style'] = ($description) ? 'width:50%' : 'width:100%'; }
						if(!$attribute['style'] && !empty($param['select2'])) { $attribute['style'] = 'width:100%'; }
						$default_label = ($type == 'select') ? '' : null;
					}
					$field_html = ($type == 'select') ?
					'<select name="'.$field_name.'" '.$this->get_html_attribute($attribute).'>'.mbc_get_select($option_data, $value, $default_label).'</select>' :
					'<select name="'.$field_name.'[]" multiple="multiple" size="1" '.$this->get_html_attribute($attribute).'>'.mbc_get_select($option_data, $value, $default_label).'</select>';
					break;

				case 'boolean' :
					$attribute['class'][] = 'filter';
					$attribute['filter'] = $id;
					$field_html = '
					<input type="radio" name="'.$field_name.'" '.$this->get_html_attribute(array('id'=>$attribute['id'].'_yes') + $attribute).' value="1"'.($value == 1 ? ' checked="checked"' : '').'><label for="'.$attribute['id'].'_yes">'.__('Oui', 'mbc').'</label>
					<input type="radio" name="'.$field_name.'" '.$this->get_html_attribute(array('id'=>$attribute['id'].'_no') + $attribute).' value="0"'.($value === '0' ? ' checked="checked"' : '').'><label for="'.$attribute['id'].'_no">'.__('Non', 'mbc').'</label>';
					break;

				case 'boolean_checkbox' :
					$attribute['class'][] = 'filter';
					$attribute['filter'] = $id;
					$description_html = ($description) ? '<label for="'.$field_id.'" class="description">'.$description.'</label>' : '';
					$field_html = '<input type="checkbox" name="'.$field_name.'" '.$this->get_html_attribute($attribute).' value="1"'.($value == 1 ? ' checked="checked"' : '').'>';
					break;

				case 'date' :
					wp_enqueue_script('jquery-ui-datepicker');
					wp_enqueue_style('jquery-ui.css', MBC_PLUGIN_URL.'/css/jquery-ui.css');
					$attribute['class'][] = 'datepicker';
					$field_html = '
					<input type="text" name="'.$field_name.'_localize" value="'.htmlspecialchars(mbc_revert_date($value, '/')).'" '.$this->get_html_attribute($attribute).' readonly="readonly">
					<input type="hidden" name="'.$field_name.'" value="'.htmlspecialchars($value).'">
					<img src="'.MBC_PLUGIN_URL.'/img/ico-delete.png" class="reset_datepicker" alt="">';
					break;

				case 'datetime' :
				case 'time' :
					wp_enqueue_script('timepicker.js', MBC_PLUGIN_URL.'/js/jquery.timepicker.js', array('jquery', 'jquery-ui-datepicker', 'jquery-ui-slider'), '1.4.4');
					wp_enqueue_style('jquery-ui.css', MBC_PLUGIN_URL.'/css/jquery-ui.css');

					$attribute['class'][] = $type.'picker';
					$field_html = '<input type="text" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;

				case 'color' :
					wp_enqueue_script('minicolors.js', MBC_PLUGIN_URL.'/js/minicolors.js', array('jquery'), '2.1');
					wp_enqueue_style('minicolors.css', MBC_PLUGIN_URL.'/css/minicolors.css', array(), '2.1');
					$attribute['class'][] = 'colorpicker';
					$field_html = '<input type="text" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;

				case 'button' :
					$attribute['class'][] = 'button';
					$attribute['class'][] = 'button_'.$id;
					$field_html = '<button type="button" '.$this->get_html_attribute($attribute).'>'.(isset($param['button_label']) ? $param['button_label'] : __('Update')).'</button>';
					break;

				/*
				case 'wpcolor' :
					wp_enqueue_script('wp-color-picker');
					wp_enqueue_style('wp-color-picker');
					$attribute['class'][] = 'wpcolorpicker';
					$field_html = '<input type="text" name="'.$field_name.'" value="'.htmlspecialchars($value).'" '.$this->get_html_attribute($attribute).'>';
					break;
				*/

				case 'image' :
				case 'file' :
					wp_enqueue_media();
					wp_enqueue_style('media');
					$img_src = wp_get_attachment_image($value, 'thumbnail', ($type == 'image' ? false : true));
					if(!$img_src) { $img_src = '<img src="" alt="">'; }
					$media_exist = (empty($value) || wp_get_attachment_url($value) == false) ? false : true;
					$field_html = '
					<input type="text" name="'.$field_name.'" id="'.$field_id.'" class="hidden-input hidden" value="'.$value.'"'.(!empty($param['required']) ? ' required="required"' : '').'>
					<div class="edit_file'.(!$media_exist ? ' hidden' : '').'">
						'.$img_src.'
						<div class="file-bar">
							<a href="'.admin_url('post.php?post='.$value.'&action=edit').'">'.__('Modifier', 'mbc').'</a>
							<a href="#" class="delete_file">×</a>
						</div>
					</div>
					<div class="add_file'.($media_exist ? ' hidden' : '').'">
						<span>'.($type == 'image' ? __('Aucune image.', 'mbc') : __('Aucun fichier', 'mbc')).'</span>
						<input type="button" class="button open_media_upload'.($type == 'image' ? ' image_only' : '').'" value="'.__('Add').'" />
						'.$description_html.'
					</div>';
					$description_html = '';
					break;

				case 'gallery' :
					wp_enqueue_media();
					wp_enqueue_style('media');
					wp_enqueue_script('jquery-ui-draggable');
					$field_html = '<ul class="gallery_preview sortable-gallery clearfix">';
					if($value) {
						foreach($value as $img_id)
						{
							$field_html .= '
							<li class="edit_file">
								<input type="hidden" name="'.$field_name.'[]" value="'.$img_id.'">
								'.wp_get_attachment_image($img_id, 'thumbnail').'
								<div class="file-bar">
									<a href="'.admin_url('post.php?post='.$img_id.'&action=edit').'" target="_blank">'.__('Modifier', 'mbc').'</a>
									<a href="#" class="delete_file_gallery">×</a>
								</div>
							</li>';
						}
					}
					$field_html .= '</ul>
					<input type="button" class="button button-primary button-large open_gallery_upload image_only" id="'.$field_name.'" value="'.__('Ajouter des images', 'mbc').'" />';
					break;

				case 'website' :
					$value = (!$value) ? array('url'=>'', 'id'=>'', 'title'=>'', 'blank'=>'') : $value;
					$default_label = (isset($param['default_label'])) ? $param['default_label'] : '---';
					$placeholder = (isset($param['placeholder'])) ? $param['placeholder'] : __('Titre de la page', 'mbc');
					$field_html = '
					<input type="url" name="'.$field_name.'[url]" value="'.htmlspecialchars($value['url']).'" id="'.$field_id.'_url" placeholder="http://" style="width:50%">
					ou <select name="'.$field_name.'[id]" id="'.$field_id.'_id">'.mbc_get_select($option_data, $value['id'], $default_label).'</select><br>
					<input type="text" name="'.$field_name.'[title]" value="'.htmlspecialchars($value['title']).'" id="'.$field_id.'_title" placeholder="'.htmlspecialchars($placeholder).'" style="width:50%"> &nbsp; &nbsp; &nbsp;
					<input type="checkbox" name="'.$field_name.'[blank]" value="1" id="'.$field_id.'_blank"> <label for="'.$field_id.'_blank">'.__('Nouvelle fenêtre', 'mbc').'</label>';
					break;

				case 'map' :
					$value = (!$value) ? array('lat'=>'', 'lng'=>'', 'full_address'=>'') : $value;
					$height = (isset($param['height'])) ? $param['height'] : '400px';
					$this->localize['google_map'][$field_id] = array('id'=>$field_id, 'lat'=>($value['lat'] ? $value['lat'] : 47), 'lng'=>($value['lng'] ? $value['lng'] : 2), 'zoom'=>($value['lat'] ? 17 : 5), 'related_id'=>(isset($param['related']) ? 'mbc_'.$param['related'] : ''));
					if(!$attribute['style']) { $attribute['style'] = 'width:100%'; }
					wp_enqueue_script('google.places', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places', array(), '3.0', false);
					$field_html = '
					<input type="text" name="'.$field_name.'[full_address]" id="'.$field_id.'_full_address" style="'.$attribute['style'].'" value="'.htmlspecialchars($value['full_address']).'"><br>
					<div id="'.$field_id.'_canvas" style="'.$attribute['style'].'; height:'.$height.'"></div>
					<div class="latlng">
						'.__('Latitude', 'mbc').' : <input type="text" name="'.$field_name.'[lat]" id="'.$field_id.'_lat" value="'.htmlspecialchars($value['lat']).'" '.$attribute['required'].'>
						'.__('Longitude', 'mbc').' : <input type="text" name="'.$field_name.'[lng]" id="'.$field_id.'_lng" value="'.htmlspecialchars($value['lng']).'" '.$attribute['required'].'>
					</div>';
					break;

				case 'address' :
					$value = (!$value) ? array('street'=>'', 'cp'=>'', 'city'=>'', 'country'=>'') : $value;
					$field_html = '
					<input type="text" name="'.$field_name.'[street]" value="'.htmlspecialchars($value['street']).'" id="'.$field_id.'_street" placeholder="Nom de la rue" style="width:100%">
					<input type="text" name="'.$field_name.'[cp]" value="'.htmlspecialchars($value['cp']).'" id="'.$field_id.'_cp" placeholder="Code Postal" style="width:90px">
					<input type="text" name="'.$field_name.'[city]" value="'.htmlspecialchars($value['city']).'" id="'.$field_id.'_city" placeholder="Ville" style="width:40%">
					<select name="'.$field_name.'[country]" id="'.$field_id.'_country" style="width:40%">'.mbc_get_select($this->get_country_list(), $value['country'], '-- Pays --').'</select>';
					break;

				case 'lang' :
					if(is_array($param['lang_list'])) {
						$languages = $param['lang_list'];
					}
					elseif(function_exists('icl_get_languages'))
					{
						$wpml_lang = icl_get_languages('skip_missing=0');
						foreach($wpml_lang as $lang_code=>$foo)	{
							$languages[] = $lang_code;
						}
					}
					else
					{
						$field_html = __('Vous devez utiliser un tableau PHP contenant la liste des langues à utiliser.', 'mbc');
						break;
					}

					wp_enqueue_style('flag.css', MBC_PLUGIN_URL.'/css/flags.css', array(), null);
					$subfield = array();
					$repeat_id = ($repeater_id) ? array_merge($repeater_id, array($id)) : array($id);
					foreach($languages as $lang_code)
					{
						$lang_param = array('type'=>$param['lang_type'], 'label'=>'<i class="flag-'.$lang_code.'"></i>', 'description'=>'') + $param;
						$subfield[] = $this->mbc_build_form($lang_code, $lang_param, $post, $repeat_id, $value);
					}
					$field_html = '<table class="repeater lang">'.implode($subfield).'</table>';
					break;

				case 'subfield' :
					$subfield = array();
					$repeat_id = ($repeater_id) ? array_merge($repeater_id, array($id)) : array($id);
					foreach($option as $subfield_id => $subfield_param) {
						$subfield[] = $this->mbc_build_form($subfield_id, $subfield_param, $post, $repeat_id, $value);
					}
					//$subfield = apply_filters($field_id.'_subfield', $subfield);
					$field_html = '<table class="repeater subfield">'.implode($subfield).'</table>';
					break;

				case 'repeater' :
					$field_repeater = $repeater_data = $header_inline = array();
					$view_item = (isset($param['view_item']) && is_numeric($param['view_item'])) ? $param['view_item'] : 1;
					$max_item = (isset($param['max_item']) && is_numeric($param['max_item'])) ? $param['max_item'] : false;
					$inline = (!empty($param['inline'])) ? true : false;
					$toggle = (!$inline) ? true : false;
					$is_unique = (!empty($param['flat_record']) && !$repeater_id) ? true : false;
					$record_id = (!empty($param['record_id'])) ? true : false;
					$repeater_key_max = 1;
					$no_table = (!empty($param['no_table'])) ? true : false;

					wp_enqueue_script('jquery-ui-draggable');


					if(count($value) > 0)
					{
						// on est dans un subrepeater
						if($repeater_id)
						{
							if(is_array($value))
							{
								foreach($value as $key=>$val)
								{
									if($max_item && count($repeater_data) >= $max_item) continue;
									// TODO : gerer les repeater_key (cf : 10 lignes en dessous)
									$repeater_data[($key+1)] = $val;
									$repeater_key_max++;
								}
							}
						}
						// on récupère les meta déjà enregistrée
						else
						{
							foreach($value as $i=>$val)
							{
								// calcul la clé unique du répéater
								if(!empty($val['repeater_key']) && $record_id)
								{
									// la clé est du type $post_id.'_'.$repeater_key. On enlève le post_id de la clé
									$repeater_key = preg_replace('/^[0-9]+_/', '', $val['repeater_key']);

									// on incremente la clé suivante si besoin
									if($repeater_key >= $repeater_key_max) { $repeater_key_max = $repeater_key+1; }
								}
								else
								{
									$repeater_key = $i+1;
									$repeater_key_max = $repeater_key+1;
								}

								if($max_item && count($repeater_data) >= $max_item) continue;

								// si le repeater n'a qu'un champ, on reconstitue le schéma de donnée
								if($is_unique)
								{
									reset($param['option']);
									$first_key = key($param['option']);
									$repeater_data[$repeater_key][$first_key] = $val;
								}
								else {
									$repeater_data[$repeater_key] = $val;
								}
							}
						}
					}

					// on complete éventuellement le tableau avec des valeurs vides, sans dépasser max_item
					for($nb_item=count($repeater_data); $nb_item<$view_item; $nb_item++)
					{
						if($max_item && $nb_item > $max_item) continue;
						$repeater_data[$repeater_key_max] = array();
						$repeater_key_max++;
					}

					$display_button = ($max_item && count($repeater_data) >= $max_item) ? false : true;


					// en mode ligne, on force la largeur des colonnes et on construit un tableau "header"
					if($inline)
					{
						$default_inline_width = ceil(93/count($option)).'%';
						foreach($option as $repeat_id => $repeat_param)
						{
							$option[$repeat_id]['inline_width'] = $default_inline_width;
							$header_inline[] = '<td><span>'.(isset($repeat_param['label']) ? $repeat_param['label'] : '&nbsp;').'</span></td>';
						}
					}

					// construction du modèle pour la réplication jQuery
					foreach($option as $repeat_id => $repeat_param)
					{

						$model_repeater_name = ($repeater_id) ? array($repeater_id[0], $repeater_id[1], $id, 'model_repeater2') : array($id, 'model_repeater1');
		 				$simple_field_repeater = $this->mbc_build_form($repeat_id, $repeat_param, $post, $model_repeater_name, array(), $inline);

						// si c'est un wysiwyg, le tinyMCE ne fonctionne pas sur le code généré par wp_editor. On crée donc un textarea après le wysiwyg, et dans le js, il y a du code jquery qui substitue le bloc wp-editor-container par le <textarea>
						if($repeat_param['type'] == 'wysiwyg')
						{
							$repeat_param['type'] = 'textarea';
							$simple_field_repeater .= $this->mbc_build_form($repeat_id, $repeat_param, $post, $model_repeater_name, array(), $inline);
						}

						$field_repeater[] = $simple_field_repeater;
					}

					$field_repeater_implode = ($inline) ? '<tr>'.implode($field_repeater).'</tr>' : implode($field_repeater);
					$field_html = '
					<div class="content_repeater sortable">
						'.($header_inline && !$no_table ? '<table class="general repeater inline header-inline"><tbody><tr>'.implode(PHP_EOL, $header_inline).'<td>&nbsp;</td></tr></tbody></table>' : '').'
						<table class="general repeater'.($inline ? ' inline' : ($toggle ? ' toggle' : '')).($no_table ? ' no-table' : '').' model_repeater hidden" rel="'.$repeater_key_max.'" data-repeaterkey="'.$post_id.'_'.$repeater_key_max.'">
							'.($toggle ? '<thead><tr><td colspan="2" class="repeater-title">'.__('Nouveau', 'cmf').'</td><td class="link"><div><br /></div></td></tr></thead>' : '').'
							<tbody>'.preg_replace('/<\/tr>/', '<td class="delete" rowspan="'.count($field_repeater).'"><a href="#" class="remove_repeater">×</a></td></tr>', $field_repeater_implode, 1).'</tbody>
						</table>';

					$i = 1;
					foreach($repeater_data as $repeater_key => $val)
					{
						$field_repeater = array();
						$label_repeater = '';
						foreach($option as $repeat_id => $repeat_param)
						{
							// permet de choisir un champ du repeater comme titre principal du bloc (affiché dans le bandeau bleu)
							if(!empty($repeat_param['repeater_title']) && isset($val[$repeat_id])) { $label_repeater = (is_array($val[$repeat_id]) ? reset($val[$repeat_id]) : $val[$repeat_id]); }

							$model_repeater_name = ($repeater_id) ? array($repeater_id[0], $repeater_id[1], $id, $repeater_key) : array($id, $repeater_key);
							$field_repeater[] = $this->mbc_build_form($repeat_id, $repeat_param, $post, $model_repeater_name, $val, $inline);
						}

						// titre du repeater : valeur du premier champs
						if(!$label_repeater) { $label_repeater = (isset($param['block_label']) ? $param['block_label'] : $label).' #'.$i; }

						$field_repeater_implode = ($inline) ? '<tr>'.implode($field_repeater).'</tr>' : implode($field_repeater);
						$field_html .= '
						<table class="general repeater'.($inline ? ' inline' : ($toggle ? ' toggle'.(!empty($param['toggle_closed']) ? ' closed' : '') : '')).($no_table ? ' no-table' : '').'" rel="'.$repeater_key.'" data-repeaterkey="'.$post_id.'_'.$repeater_key.'">
							'.($toggle ? '<thead><tr><td colspan="2" class="repeater-title">'.$label_repeater.'</td><td class="link"><div><br /></div></td></tr></thead>' : '').'
							<tbody>
								'.preg_replace('/<\/tr>/', '<td class="delete" rowspan="'.count($field_repeater).'"><a href="#" class="remove_repeater">×</a></td></tr>', $field_repeater_implode, 1).'
							</tbody>
						</table>';
						$i++;
					}

					$field_html .= '</div>'.($display_button ? '<a href="#" class="duplicate_repeater button button-primary button-large'.($repeater_id ? ' subrepeater' : '').'">'.(isset($param['button_label']) ? $param['button_label'] : __('Add')).'</a>' : '');
					break;


				default :
					$field_html = __('Ooops ! Le type de champ "'.$type.'" n\'existe pas.', 'mbc');
			}


			// gestion des filtres sur un select
			$filtered_field = '';
			if(isset($param['filter']) && count($param['filter']) > 0)
			{
				// TODO : verifier que le la valeur est remplis (à faire en jQuery ?)
				$filtered_field .= (1) ? ' hidden' : '';
				foreach($param['filter'] as $val) {
					$filtered_field .= ' filtered filter_'.$val['id'].' filter_show__'.$val['id'].'__'.$val['value'];
				}
			}


			// contruction de la ligne du tableau
			if($repeater_inline)
			{
				// TODO : améliorer la gestion du width
				//$html = '<td style="width:'.$param['inline_width'].'">'.$field_html.'</td>';
				$html = '<td>'.$field_html.'</td>';
			}
			elseif(!empty($param['full_width']))
			{
				$html = '
				<tr class="type_'.$type.' field_'.$id.$filtered_field.' full-width">
					<td colspan="2"><div class="first-line"><label for="'.$field_id.'">'.(!empty($param['required']) ? '<span class="required">*</span>' : '').''.$label.'</label>'.$description_html.'</div>'.$field_html.'</td>
				</tr>';
			}
			else
			{
				$html = '
				<tr class="type_'.$type.' field_'.$id.$filtered_field.'">
					<td><label for="'.$field_id.'">'.(!empty($param['required']) ? '<span class="required">*</span>' : '').''.$label.'</label></td>
					<td>'.$field_html.$description_html.'</td>
				</tr>';
			}

			return $html;
		}


		/**
		 * Enregistre les meta_post
		 */
		function save_metabox($post_id, $post, $update)
		{
			global $wpdb;

			// TODO : check nonce
			//if (function_exists($this->box_id . '_wpnonce') && !wp_verify_nonce($_POST[$this->box_id . '_wpnonce'], $this->box_id))
			//	return;

			// vérifier que l'utilisateur loggé à le droit d'éditer l'enregistrement
			if (!current_user_can('edit_post', $post_id))
				return;

			// ignore les révisions et enregistrement automatique
			if ($post->post_type == 'revision' || $post->post_status == 'auto-draft')
				return;

			// supprime les slashes ajouté lors de l'envoi des données
			$post_value = array_map('stripslashes_deep', $_POST['mbc']);

			// multilangue avec WPML ou Polylang s'il est installé : permet de copier les champs post_meta lorsque le parametre "synchronize"=>true
			if(function_exists('pll_get_post'))
			{
				global $polylang;
				$post_ids = $polylang->model->get_translations('post', $post_id);
				$this->synchronize_post = array();
				foreach($post_ids as $language_code => $element_id)
				{
					if($element_id != $post_id) {
						$this->synchronize_post[$language_code] = $element_id;
					}
				}
			}
			elseif(function_exists('icl_object_id'))
			{
				// récupère la liste de toutes les pages traduites de ce post_id
				$trid = $wpdb->get_var($wpdb->prepare("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_type = %s AND element_id = %d", 'post_'.$post->post_type, $post_id));
				$res = $wpdb->get_results($wpdb->prepare("SELECT element_id, language_code FROM {$wpdb->prefix}icl_translations WHERE trid = %d AND element_id != %d", $trid, $post_id));
				$this->synchronize_post = array();
				if($res)
				{
					foreach($res as $val) {
						$this->synchronize_post[$val->language_code] = $val->element_id;
					}
				}
			}

			$this->record_post_meta($post_id, $post_value);
		}



		/**
		 * Enregistre les post meta pour un attachment
		 */
		function save_metabox_attachment($post_id)
		{
			global $wpdb;

			// vérifier que l'utilisateur loggé à le droit d'éditer l'enregistrement
			if (!current_user_can('edit_post', $post_id))
				return;

			// supprime les slashes ajouté lors de l'envoi des données
			$post_value = array_map('stripslashes_deep', $_POST['mbc']);


			$this->record_post_meta($post_id, $post_value);
		}



		/**
		 * Enregistre les post meta pour chacun des champs du formulaire
		 */
		function record_post_meta($post_id, $post_value)
		{
			foreach ($this->field_list as $meta_key => $param)
			{
				/****** Champ REPEATER ******/
				if($param['type'] == 'repeater')
				{
					$current_value = (isset($post_value[$meta_key])) ? $post_value[$meta_key] : array();
					unset($current_value['model_repeater1']);
					$current_value_key = array_keys($current_value);
					$subrepeater = array();

					// detection des subrepeater
					foreach($param['option'] as $subkey=>$subval)	{
						if($subval['type'] == 'repeater') {
							$subrepeater[] = $subkey;
						}
					}

					// supprime tous les repeaters avant de les recréés : permet d'utiliser le tri par drag & drop
					delete_post_meta($post_id, $meta_key);

					// uniquement pour l'enregistrement non serializé
					if(!empty($param['flat_record'])) {
						$this->post_meta_synchronize('delete', $param, $meta_key);
					}


					if(isset($post_value[$meta_key]) && count($current_value) > 0)
					{
						$this->meta_value_lang = array();

						// pour chacun des blocks du repeater
						foreach($current_value as $repeater_key=>$meta_value)
						{
							// si tous les champs d'un bloc sont vide, alors on ne l'enregistre pas
							if(isset($post_value[$meta_key]['model_repeater1']) && serialize($post_value[$meta_key]['model_repeater1']) == serialize($meta_value)) {
								continue;
							}

							// pour chacun des subreapeater du repeater en cours
							foreach($subrepeater as $subkey)
							{
								$original_sub = $meta_value[$subkey];
								$meta_value[$subkey] = array();

								// pour chacune des ligne sur subrepeater
								foreach($original_sub as $key2 => $val2)
								{
									// on enregistre pas les valeurs vide. model_repeater2 étant vide, il ne s'enregistre pas également
									if(serialize($val2) != serialize($original_sub['model_repeater2'])) {
										$meta_value[$subkey][] = $val2;
									}
								}
							}

							// si le repeater n'a qu'un champ, on supprime la clé : permet d'enregistrer la valeur directement, sans être serializée
							if(!empty($param['flat_record']))
							{
								$new_meta_value = reset($meta_value);

								add_post_meta($post_id, $meta_key, $new_meta_value);
								$this->post_meta_synchronize('add', $param, $meta_key, $new_meta_value);
							}
							// insertion du tableau en base ; serializé par WordPress
							else
							{
								// on ajout un champ "repeater_key" si l'option est activée ou si la synchronisation est activée
								// la repeater_key est necessaire pour connaitre les correspondances entre les blocs des différentes langues (qui devront être mise à jour)
								if(!empty($param['record_id']) || !empty($param['synchronize'])) {
									$meta_value = array('repeater_key'=>$post_id.'_'.$repeater_key) + $meta_value;
								}

								add_post_meta($post_id, $meta_key, $meta_value);
								$this->post_meta_synchronize('repeater', $param, $meta_key, $meta_value, $repeater_key);
							}
						}

						// si des champs sont à synchroniser
						if($this->meta_value_lang)
						{
							foreach($this->meta_value_lang as $post_id_lang => $fields)
							{
								foreach($fields as $field=>$blocs)
								{
									delete_post_meta($post_id_lang, $field);
									foreach($blocs as $bloc) {
										add_post_meta($post_id_lang, $field, $bloc);
									}
								}
							}
						}
					}
				}
				/****** champs de type "tableau" => plusieurs valeur pour un même meta key ******/
				elseif($param['type'] == 'select_multiple')
				{
					// on supprime toutes les valeurs d'un champ multiple
					delete_post_meta($post_id, $meta_key);
					$this->post_meta_synchronize('delete', $param, $meta_key);

					// puis on insert les valeurs
					if(isset($post_value[$meta_key]) && count($post_value[$meta_key]) > 0)
					{
						foreach($post_value[$meta_key] as $meta_value) {
							add_post_meta($post_id, $meta_key, $meta_value);
							$this->post_meta_synchronize('add', $param, $meta_key, $meta_value);
						}
					}
				}
				/****** champs de type simple ******/
				else
				{
					// si le champ est vide, on le supprime
					if(!isset($post_value[$meta_key]) || $post_value[$meta_key] == '') {
						delete_post_meta($post_id, $meta_key);
						$this->post_meta_synchronize('delete', $param, $meta_key);
					}
					// sinon, on le met à jour
					else
					{
						$meta_value = (is_array($post_value[$meta_key])) ? $post_value[$meta_key] : trim($post_value[$meta_key]);

						update_post_meta($post_id, $meta_key, $meta_value);
						$this->post_meta_synchronize('update', $param, $meta_key, $meta_value);
					}
				}


				// update term
				if(!empty($param['update_term']))
				{
					$term_data = array();
					if(isset($post_value[$meta_key]))
					{
						$term_list = (!is_array($post_value[$meta_key])) ? array($post_value[$meta_key]) : $post_value[$meta_key];
						foreach($term_list as $term_id) {
						 	$term_data[] = (int)$term_id;
						}
					}
					wp_set_object_terms($post_id, $term_data, $param['update_term']);
				}


				// update thumbnail
				if(!empty($param['update_thumbnail']))
				{
					update_post_meta($post_id, '_thumbnail_id', $meta_value);
					$this->post_meta_synchronize('update', $param, '_thumbnail_id', $meta_value);
				}
			}

			// debug
			//if($_SERVER['HTTP_HOST'] == 'dev.goexplo.com') die();
		}




		/**
		 * Permet la synchronisation des meta
		 */
		function post_meta_synchronize($action, $field, $meta_key, $meta_value='', $repeater_key='')
		{
			// la synchronisation n'est pas active
			if(!isset($this->synchronize_post) || count($this->synchronize_post) == 0)
				return false;

			// si la synchronisation n'est pas activée pour ce champ (sauf s'il s'agit de l'update dans le repeater de niveau 1)
			if($action != 'repeater' && empty($field['synchronize']))
				return false;

			// pour chacune des langues disponible pour le post_id
			foreach($this->synchronize_post as $lang_code => $post_id_lang)
			{
				// on remplace la meta_value s'il s'agit d'un post_id traductible
				if($field['synchronize'] === 'post_id' && is_numeric($meta_value))
				{
					$meta_value = icl_object_id($meta_value, get_post_type($meta_value), false, $lang_code);

					if(!$meta_value) continue;
				}

				switch ($action)
				{
					case 'add' : add_post_meta($post_id_lang, $meta_key, $meta_value); break;
					case 'update' : update_post_meta($post_id_lang, $meta_key, $meta_value); break;
					case 'delete' : delete_post_meta($post_id_lang, $meta_key, $meta_value); break;
					case 'repeater' :
						// récupère la valeur X valeurs du repeater dans la langue donnée
						$meta_value_lang = mbc_get_post_meta_repeater($meta_key, $post_id_lang.'_'.$repeater_key);

						// s'il n'existe pas de correspondance dans la langue à synchroniser, on initialise un tableau avec le repeater_key
						$this->meta_value_lang[$post_id_lang][$meta_key][$repeater_key] = (!$meta_value_lang) ? array('repeater_key'=>$post_id_lang.'_'.$repeater_key) : $meta_value_lang;

						// boucle sur tous les champs du repeater de niveau 1
						foreach($field['option'] as $key => $val)
						{
							// si le champ n'est pas à traduire ou si c'est un subrepeater
							if($val['type'] == 'repeater' || empty($val['synchronize']))
								continue;

							// on remplace la meta_value s'il s'agit d'un post_id traductible
							if($val['synchronize'] === 'post_id' && is_numeric($meta_value[$key])) {
								$meta_value[$key] = icl_object_id($meta_value[$key], get_post_type($meta_value[$key]), false, $lang_code);
							}

							// champs de type tableau
							if($val['synchronize'] === 'post_id' && $field['type'] == 'select_multiple')
							{
								foreach($meta_value[$key] as $subkey => $subval) {
									$meta_value[$key][$subkey] = icl_object_id($subval, get_post_type($subval), false, $lang_code);
								}
							}

							// change la valeur directement dans le tableau
							$this->meta_value_lang[$post_id_lang][$meta_key][$repeater_key][$key] = $meta_value[$key];
						}
						break;
				}
			}

			return true;
		}






		/**
		 * Insère les fichiers CSS et JS, ainsi que les variables JS
		 */
		function admin_footer()
		{
			$this->localize['mbc_plugin_url'] = MBC_PLUGIN_URL;

			wp_enqueue_script('admin.js', MBC_PLUGIN_URL.'/js/admin.js', array('jquery'));
			wp_enqueue_script('jquery.validate.js', MBC_PLUGIN_URL.'/js/jquery.validate.min.js', array('jquery'));

			// ajoute le tableau $localize au JS
			wp_localize_script('admin.js', 'mbc', $this->localize);
		}


		/**
		 * Permet d'ajouter des classes CSS aux metabox
		 */
		function add_metabox_classes($classes)
		{
			array_push($classes,'mbc');
			return $classes;
		}


		function get_html_attribute($attribute)
		{
			$data = array();
			foreach($attribute as $key=>$val)
			{
				if($val != '') {
					$data[] = $key.'="'.(is_array($val) ? implode(' ', $val) : $val).'"';
				}
			}

			return implode(' ', $data);
		}



		function get_country_list()
		{
			return array(
			'AF'=>'Afghanistan', 'ZA'=>'Afrique du Sud', 'AL'=>'Albanie', 'DZ'=>'Algérie', 'DE'=>'Allemagne', 'AD'=>'Andorre', 'AO'=>'Angola', 'AI'=>'Anguilla',
			'AQ'=>'Antarctique', 'AG'=>'Antigua-et-Barbuda', 'AN'=>'Antilles néerlandaises', 'SA'=>'Arabie saoudite', 'AR'=>'Argentine', 'AM'=>'Arménie', 'AW'=>'Aruba',
			'AU'=>'Australie', 'AT'=>'Autriche', 'AZ'=>'Azerbaïdjan', 'BJ'=>'Bénin', 'BS'=>'Bahamas', 'BH'=>'Bahreïn', 'BD'=>'Bangladesh', 'BB'=>'Barbade', 'PW'=>'Belau',
			'BE'=>'Belgique', 'BZ'=>'Belize', 'BM'=>'Bermudes', 'BT'=>'Bhoutan', 'BY'=>'Biélorussie', 'MM'=>'Birmanie', 'BO'=>'Bolivie', 'BA'=>'Bosnie-Herzégovine',
			'BW'=>'Botswana', 'BR'=>'Brésil', 'BN'=>'Brunei', 'BG'=>'Bulgarie', 'BF'=>'Burkina Faso', 'BI'=>'Burundi', 'CI'=>'Côte d\'Ivoire', 'KH'=>'Cambodge',
			'CM'=>'Cameroun', 'CA'=>'Canada', 'CV'=>'Cap-Vert', 'CL'=>'Chili', 'CN'=>'Chine', 'CY'=>'Chypre', 'CO'=>'Colombie', 'KM'=>'Comores', 'CG'=>'Congo',
			'KP'=>'Corée du Nord', 'KR'=>'Corée du Sud', 'CR'=>'Costa Rica', 'HR'=>'Croatie', 'CU'=>'Cuba', 'DK'=>'Danemark', 'DJ'=>'Djibouti', 'DM'=>'Dominique',
			'EG'=>'Égypte', 'AE'=>'Émirats arabes unis', 'EC'=>'Équateur', 'ER'=>'Érythrée', 'ES'=>'Espagne', 'EE'=>'Estonie', 'US'=>'États-Unis', 'ET'=>'Éthiopie',
			'FI'=>'Finlande', 'FR'=>'France', 'GE'=>'Géorgie', 'GA'=>'Gabon', 'GM'=>'Gambie', 'GH'=>'Ghana', 'GI'=>'Gibraltar', 'GR'=>'Grèce', 'GD'=>'Grenade',
			'GL'=>'Groenland', 'GP'=>'Guadeloupe', 'GU'=>'Guam', 'GT'=>'Guatemala', 'GN'=>'Guinée', 'GQ'=>'Guinée équatoriale', 'GW'=>'Guinée-Bissao', 'GY'=>'Guyana',
			'GF'=>'Guyane française', 'HT'=>'Haïti', 'HN'=>'Honduras', 'HK'=>'Hong Kong', 'HU'=>'Hongrie', 'BV'=>'Ile Bouvet', 'CX'=>'Ile Christmas', 'NF'=>'Ile Norfolk',
			'KY'=>'Iles Cayman', 'CK'=>'Iles Cook', 'FO'=>'Iles Féroé', 'FK'=>'Iles Falkland', 'FJ'=>'Iles Fidji', 'GS'=>'Iles Géorgie du Sud et Sandwich du Sud',
			'HM'=>'Iles Heard et McDonald', 'MH'=>'Iles Marshall', 'PN'=>'Iles Pitcairn', 'SB'=>'Iles Salomon', 'SJ'=>'Iles Svalbard et Jan Mayen',
			'TC'=>'Iles Turks-et-Caicos', 'VI'=>'Iles Vierges américaines', 'VG'=>'Iles Vierges britanniques', 'CC'=>'Iles des Cocos (Keeling)',
			'UM'=>'Iles mineures éloignées des États-Unis', 'IN'=>'Inde', 'ID'=>'Indonésie', 'IR'=>'Iran', 'IQ'=>'Iraq', 'IE'=>'Irlande', 'IS'=>'Islande', 'IL'=>'Israël',
			'IT'=>'Italie', 'JM'=>'Jamaïque', 'JP'=>'Japon', 'JO'=>'Jordanie', 'KZ'=>'Kazakhstan', 'KE'=>'Kenya', 'KG'=>'Kirghizistan', 'KI'=>'Kiribati', 'KW'=>'Koweït',
			'LA'=>'Laos', 'LS'=>'Lesotho', 'LV'=>'Lettonie', 'LB'=>'Liban', 'LR'=>'Liberia', 'LY'=>'Libye', 'LI'=>'Liechtenstein', 'LT'=>'Lituanie', 'LU'=>'Luxembourg',
			'MO'=>'Macao', 'MG'=>'Madagascar', 'MY'=>'Malaisie', 'MW'=>'Malawi', 'MV'=>'Maldives', 'ML'=>'Mali', 'MT'=>'Malte', 'MP'=>'Mariannes du Nord', 'MA'=>'Maroc',
			'MQ'=>'Martinique', 'MU'=>'Maurice', 'MR'=>'Mauritanie', 'YT'=>'Mayotte', 'MX'=>'Mexique', 'FM'=>'Micronésie', 'MD'=>'Moldavie', 'MC'=>'Monaco', 'MN'=>'Mongolie',
			'MS'=>'Montserrat', 'MZ'=>'Mozambique', 'NP'=>'Népal', 'NA'=>'Namibie', 'NR'=>'Nauru', 'NI'=>'Nicaragua', 'NE'=>'Niger', 'NG'=>'Nigeria', 'NU'=>'Nioué',
			'NO'=>'Norvège', 'NC'=>'Nouvelle-Calédonie', 'NZ'=>'Nouvelle-Zélande', 'OM'=>'Oman', 'UG'=>'Ouganda', 'UZ'=>'Ouzbékistan', 'PE'=>'Pérou', 'PK'=>'Pakistan',
			'PA'=>'Panama', 'PG'=>'Papouasie-Nouvelle-Guinée', 'PY'=>'Paraguay', 'NL'=>'Pays-Bas', 'PH'=>'Philippines', 'PL'=>'Pologne', 'PF'=>'Polynésie française',
			'PR'=>'Porto Rico', 'PT'=>'Portugal', 'QA'=>'Qatar', 'CF'=>'République centrafricaine', 'CD'=>'République démocratique du Congo', 'DO'=>'République dominicaine',
			'CZ'=>'République tchèque', 'RE'=>'Réunion', 'RO'=>'Roumanie', 'GB'=>'Royaume-Uni', 'RU'=>'Russie', 'RW'=>'Rwanda', 'SN'=>'Sénégal', 'EH'=>'Sahara occidental',
			'KN'=>'Saint-Christophe-et-Niévès', 'SM'=>'Saint-Marin', 'PM'=>'Saint-Pierre-et-Miquelon', 'VA'=>'Saint-Siège ', 'VC'=>'Saint-Vincent-et-les-Grenadines',
			'SH'=>'Sainte-Hélène', 'LC'=>'Sainte-Lucie', 'SV'=>'Salvador', 'WS'=>'Samoa', 'AS'=>'Samoa américaines', 'ST'=>'Sao Tomé-et-Principe', 'SC'=>'Seychelles',
			'SL'=>'Sierra Leone', 'SG'=>'Singapour', 'SI'=>'Slovénie', 'SK'=>'Slovaquie', 'SO'=>'Somalie', 'SD'=>'Soudan', 'LK'=>'Sri Lanka', 'SE'=>'Suède', 'CH'=>'Suisse',
			'SR'=>'Suriname', 'SZ'=>'Swaziland', 'SY'=>'Syrie', 'TW'=>'Taïwan', 'TJ'=>'Tadjikistan', 'TZ'=>'Tanzanie', 'TD'=>'Tchad', 'TF'=>'Terres australes françaises',
			'IO'=>'Territoire britannique de l\'Océan Indien', 'TH'=>'Thaïlande', 'TL'=>'Timor Oriental', 'TG'=>'Togo', 'TK'=>'Tokélaou', 'TO'=>'Tonga',
			'TT'=>'Trinité-et-Tobago', 'TN'=>'Tunisie', 'TM'=>'Turkménistan', 'TR'=>'Turquie', 'TV'=>'Tuvalu', 'UA'=>'Ukraine', 'UY'=>'Uruguay', 'VU'=>'Vanuatu',
			'VE'=>'Venezuela', 'VN'=>'Viet Nam', 'WF'=>'Wallis-et-Futuna', 'YE'=>'Yémen', 'YU'=>'Yougoslavie', 'ZM'=>'Zambie', 'ZW'=>'Zimbabwe',	'MK'=>'ex-République yougoslave de Macédoine'
			);
		}





		function option_init()
		{
			$this->display_for = 'toplevel_page_mbc_options';

			$this->localize['is_option_page'] = true;

			add_action('admin_head', 'mbc_js_init');

			//register callback for admin menu setup
			add_action('admin_menu', array(&$this, 'on_admin_menu'));

			//register the callback been used if options of page been submitted and needs to be processed
			add_action('admin_post_save_howto_metaboxes_general', array(&$this, 'save_metabox_option'));
		}


		//extend the admin menu
		function on_admin_menu()
		{
			//add our own option page, you can also add it to different sections or use your own one
			$this->pagehook = add_menu_page($this->option_title, $this->option_title, 'manage_options', 'mbc_options', array(&$this, 'on_show_page'), '');

			//register callback gets call prior your own page gets rendered
			add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
		}


		//will be executed if wordpress core detects this page has to be rendered
		function on_load_page()
		{
			//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');

			$this->init();
		}



		function save_metabox_option()
		{
			global $wpdb;

			//cross check the given referer
			check_admin_referer('howto-metaboxes-general');

			$this->post_id = get_option('mbc_option_post_id');

			// enrichie la liste des champs
			$this->field_list = $this->field_list($this->field);

			// recupere les valeurs de tous les metas
			$this->field_value = $this->field_value($this->field_list);

			$this->save_metabox($this->post_id, get_post($this->post_id), '');

			$meta_values = mbc_get_option('', true);

			$delete_option = $option_list = array();

			foreach($meta_values as $key => $val)
			{
				// on boucle sur la liste des champs déclarés : permet de supprimé d'éventuel champs précédement créé puis supprimé
				if(isset($this->field_list[$key]))
				{
					$option_key = 'mbco_'.$key;
					$delete_option[] = esc_sql($option_key);
					update_option($option_key, $val);

					$option_list[$option_key] = $val;
				}
			}

			if(count($delete_option) > 0) {
				$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'mbco_' AND NOT IN ('".implode("','", $delete_option)."')");
			}

			do_action('mbc_save_option', $option_list);

			//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
			wp_redirect(add_query_arg('message', 1, $_POST['_wp_http_referer']));
		}



		function on_show_page()
		{
			echo '
			<div id="howto-metaboxes-general" class="wrap">
				<h2>'.get_admin_page_title().'</h2>

				<form action="admin-post.php" method="post" id="post">';
					wp_nonce_field('howto-metaboxes-general');
					wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
					wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);

					echo '
					<input type="hidden" name="action" value="save_howto_metaboxes_general" />

					<div id="poststuff" class="metabox-holder">
						<div id="post-body" class="has-sidebar">
							<div id="post-body-content" class="has-sidebar-content">';

								echo (!empty($_GET['message']) ? '<div id="message" class="updated below-h2"><p><strong>'.__('Settings saved.').'</strong></p></div>' : '');

								do_meta_boxes($this->pagehook, 'normal', '');

								echo '
								<p><input type="submit" value="'.__('Save').'" class="button-primary" name="Submit" /></p>
							</div>
						</div>
					</div>
				</form>
			</div>';
		}


	}
}

