<?php
/**
Ajoute, modifie et supprime des options par défaut de WordPress
*/



/**
 * supprime les widgets inutiles sur la page d'accueil de l'admin
 */
function mbc_remove_dashboard_widgets()
{
  // colonne de gauche (main)
	//remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	remove_meta_box('dashboard_plugins', 'dashboard', 'normal');

  // colonne de droite (side)
	remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
	remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
	remove_meta_box('dashboard_primary', 'dashboard', 'side');
	remove_meta_box('dashboard_secondary', 'dashboard', 'side');
}
add_action('wp_dashboard_setup', 'mbc_remove_dashboard_widgets');



/**
 * supprime la balise indiquant la version de wordpress <meta name="generator" content="WordPress 3.x" />
 * supprime Ègalement le Really Simple Discovery (RSD), Windows Live Writer, et les Post Relational Links
 * http://www.themelab.com/2010/07/11/remove-code-wordpress-header/
 * http://wordpress.org/support/topic/remove-feed-from-wp_head
 */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'feed_links', 2);        // hide the links to the general feeds: Post and Comment Feed
remove_action('wp_head', 'feed_links_extra', 3);  // hide the links to the extra feeds such as category feeds






/**
 * supprime la version de wordpress dans flux rss
 */
function mbc_remove_wordpress_version() {
  return '';
}
add_filter('the_generator', 'mbc_remove_wordpress_version');





/**
 * Disable the revisions function
 */
if(!defined('WP_POST_REVISIONS')) {
	define('WP_POST_REVISIONS', 10);
}





function mbc_define_var()
{
	if(!defined('THEME')) {
		define('THEME', get_bloginfo('stylesheet_directory'));
	}
}
add_action('init', 'mbc_define_var');





/**
 * Supprime les caractères spéciaux du nom des fichiers
 */
add_filter('sanitize_file_name', 'remove_accents', 10, 1);

function mbc_sanitize_file_name_chars( $special_chars = array() )
{
	$special_chars = array_merge(array('’', '‘', '“', '”', '«', '»', '‹', '›', '—', 'æ', 'œ', '€', '@', '+', '%'), $special_chars);
	return $special_chars;
}
add_filter('sanitize_file_name_chars', 'mbc_sanitize_file_name_chars', 10, 1);



/**
 * Supprime TOUS les caractères spéciaux
 */
function mbc_sanitize_filename_on_upload($filename)
{
	$ext = pathinfo($filename, PATHINFO_EXTENSION);

	// replace all weird characters
	$sanitized = preg_replace('/[^a-zA-Z0-9\-_.]/','', substr($filename, 0, -(strlen($ext)+1)));

	// replace dots inside filename
	$sanitized = str_replace('.','-', $sanitized);

	// supprime les -- et le - final
	$sanitized = preg_replace('/[\-]{2,99}/', '-', $sanitized);
	$sanitized = preg_replace('/[\-]+$/', '', $sanitized);

	return $sanitized.'.'.$ext;
}
add_filter('sanitize_file_name', 'mbc_sanitize_filename_on_upload', 11);





/**
 * Ajoute le lien "Dupliquer" pour les post_type definie dans le tableau $mbc['duplicate_post']
 */
function mbc_add_duplicate_link($actions)
{
	global $post, $mbc;

	$check_post_type = (isset($mbc['duplicate_post'])) ? $mbc['duplicate_post'] : array();

	if(in_array($post->post_type, $check_post_type) && current_user_can('edit_post', $post->ID)) {
		$actions['mbc_duplicate'] = '<a href="'.admin_url('admin.php?action=mbc_duplicate_post&post='.$post->ID).'">Dupliquer</a>';
	}

	return $actions;
}
add_filter('post_row_actions', 'mbc_add_duplicate_link');


/**
 * Permet la duplication d'un post
 */
function mbc_duplicate_post_action()
{
	if (!isset($_GET['post'])) {
		wp_die(__('Aucun post à dupliquer!', 'mbc'));
	}

	$post_id = $_GET['post'];

	$new_post_id = mbc_duplicate_post($post_id);

	$redirect = (!$new_post_id) ? 'edit.php?post_type='.get_post_type($post_id) : 'post.php?action=edit&post='.$new_post_id;

	wp_redirect(admin_url($redirect)); exit;
}
add_action('admin_action_mbc_duplicate_post', 'mbc_duplicate_post_action');





/**
 * Permet de corriger le bug d'orientation des photos uploadé via la fonction mbc_updload_image()
 */
function mbc_fix_image_orientation($fileinfo)
{
	$file = $fileinfo['file'];

	if(!is_callable('exif_read_data')) return $fileinfo;

	$exif = @exif_read_data($file);

	if(!isset($exif) || !isset($exif['Orientation']) || $exif['Orientation'] <= 0) return $fileinfo;

	require_once ABSPATH.'wp-admin/includes/image-edit.php';

	switch($exif['Orientation'])
	{
		case 3: $orientation = -180; break;
		case 6:	$orientation = -90; break;
		case 8:	case 9: $orientation = -270; break;
		default: $orientation = 0; break;
	}

	if(!$orientation) return $fileinfo;

	$image = wp_get_image_editor($file);

	if(is_wp_error($image)) return $fileinfo;

	$image->rotate($orientation);
	$image->save($file);

	return $fileinfo;
}
