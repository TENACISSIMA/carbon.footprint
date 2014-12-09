<?php
// theme setup main function
add_action( 'after_setup_theme', 'hce_theme_setup' );
function hce_theme_setup() {

	// theme global vars
	if (!defined('HCE_BLOGNAME'))
	    define('HCE_BLOGNAME', get_bloginfo('name'));

	if (!defined('HCE_BLOGDESC'))
	    define('HCE_BLOGDESC', get_bloginfo('description','display'));

	if (!defined('HCE_BLOGURL'))
	    define('HCE_BLOGURL', get_bloginfo('url'));

	if (!defined('HCE_BLOGTHEME'))
	    define('HCE_BLOGTHEME', get_bloginfo('template_directory'));

	/* Set up media options: sizes, featured images... */
//	add_action( 'init', 'hce_media_options' );

	/* Add your nav menus function to the 'init' action hook. */
	add_action( 'init', 'hce_register_menus' );

	/* Load JavaScript files on the 'wp_enqueue_scripts' action hook. */
	add_action( 'wp_enqueue_scripts', 'hce_load_scripts' );

	/* Load scripts for IE compatibility */
	add_action('wp_head','hce_ie_scripts');

	// Custom post types
	add_action( 'init', 'hce_create_post_type', 0 );

	// Extra meta boxes in editor
	//add_filter( 'cmb_meta_boxes', 'hce_metaboxes' );
	// Initialize the metabox class
	//add_action( 'init', 'hce_init_metaboxes', 9999 );

	// excerpt support in pages
	add_post_type_support( 'page', 'excerpt' );

	// remove unused items from dashboard
	add_action( 'admin_menu', 'hce_remove_dashboard_item' );

	// disable admin bar in front end
	add_filter('show_admin_bar', '__return_false');

	// filter loops
	add_filter( 'pre_get_posts', 'hce_filter_loops' );

	// create custom content
	add_action('after_switch_theme', 'hce_create_custom_content');

	// config custom options
	add_action('after_switch_theme', 'hce_custom_configuration');

	// create custom tables in DB
//	add_action('after_switch_theme', 'hce_db_materials_table');
//	add_action('after_switch_theme', 'hce_db_emissions_table');

	// update custom tables structure in DB
	add_action( 'init', 'hce_db_custom_tables_update', 99 );

	// populate emissions and materials table
	add_action( 'init', 'hce_db_emissions_table_populate', 100 );
	add_action( 'init', 'hce_db_materials_table_populate', 100 );

} // end hce theme setup function

// USER functions
// display login form
function hce_login_form( $redirect_url = '' ) {	
	$login_action = HCE_BLOGURL."/wp-login.php";
	$form_out = "
	<form class='row' id='loginform' name='loginform' method='post' action='" .$login_action. "' role='form'>
		<div class='form-horizontal col-md-12'>
		<fieldset class='form-group'>
			<label for='user_login' class='col-sm-3 control-label'>Nombre de usuario</label>
			<div class='col-sm-5'>
				<input id='user_login' class='form-control' type='text' value='' name='log' />
			</div>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_pass' class='col-sm-3 control-label'>Contraseña</label>
			<div class='col-sm-5'>
				<input id='user_pass' class='form-control' type='password' size='20' value='' name='pwd' />
			</div>
		</fieldset>
		<fieldset class='form-group'>
			<div class='col-sm-offset-3 col-sm-3 checkbox'>
				<label>
					<input id='rememberme' type='checkbox' value='forever' name='rememberme' /> Recuérdame
				</label>
			</div>
			<div class='col-sm-2'>
				<div class='pull-right'>
					<input id='wp-submit' class='btn btn-primary' type='submit' value='Iniciar sesión' name='wp-submit' />
					<input type='hidden' value='".$redirect_url."' name='redirect_to' />
				</div>
    			</div>
		</fieldset>
		</div>
	</form>
	";
	return $form_out;

} // end display login form

// set up media options
function hce_media_options() {
	/* Add theme support for post thumbnails (featured images). */
	add_theme_support( 'post-thumbnails', array( 'project') );
	set_post_thumbnail_size( 231, 0 ); // default Post Thumbnail dimensions
	/* set up image sizes*/
	update_option('thumbnail_size_w', 231);
	update_option('thumbnail_size_h', 0);
	update_option('medium_size_w', 474);
	update_option('medium_size_h', 0);
	update_option('large_size_w', 717);
	update_option('large_size_h', 0);
} // end set up media options

// register custom menus
function hce_register_menus() {
        if ( function_exists( 'register_nav_menus' ) ) {
                register_nav_menus(
                array(
                        'header-menu' => 'Menú de cabecera',
                        'footer-menu' => 'Menú del pie de página',
                )
                );
        }
} // end register custom menus

// load js scripts to avoid conflicts
function hce_load_scripts() {
	wp_register_style( 'bootstrap-css', get_template_directory_uri() . '/bootstrap/css/bootstrap.min.css' );
	wp_register_style( 'hce-css', get_stylesheet_uri(), array('bootstrap-css') );
	wp_enqueue_style('hce-css');
	wp_enqueue_script('jquery');
//	wp_enqueue_script(
//		'bootstrap-js',
//		get_template_directory_uri() . '/bootstrap/js/bootstrap.min.js',
//		array( 'jquery' ),
//		'3.3.0',
//		FALSE
//	);

} // end load js scripts to avoid conflicts

// load scripts for IE compatibility
function hce_ie_scripts() {
	echo "
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src='https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js'></script>
	<script src='https://oss.maxcdn.com/respond/1.4.2/respond.min.js'></script>
	<![endif]-->
	";
}

// register post types
function hce_create_post_type() {
	// Documento custom post type
	register_post_type( 'project', array(
		'labels' => array(
			'name' => __( 'Projects' ),
			'singular_name' => __( 'Project' ),
			'add_new_item' => __( 'Add a project' ),
			'edit' => __( 'Edit' ),
			'edit_item' => __( 'Edit this project' ),
			'new_item' => __( 'New project' ),
			'view' => __( 'View project' ),
			'view_item' => __( 'View this project' ),
			'search_items' => __( 'Search project' ),
			'not_found' => __( 'No project found' ),
			'not_found_in_trash' => __( 'No projects in trash' ),
			'parent' => __( 'Parent' )
		),
		'has_archive' => true,
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'menu_position' => 5,
		//'menu_icon' => get_template_directory_uri() . '/images/icon-post.type-integrantes.png',
		'hierarchical' => false, // if true this post type will be as pages
		'query_var' => true,
		'supports' => array('title', 'editor','excerpt','author','thumbnail' ),
		'rewrite' => array('slug'=>'project','with_front'=>false),
		'can_export' => true,
		'_builtin' => false,
		'_edit_link' => 'post.php?post=%d',
	));
} // end register post types

// remove item from wordpress dashboard
function hce_remove_dashboard_item() {
	remove_menu_page('edit.php');	
}

// insert project basic data from form
function hce_project_insert_basic_data() {

	$location = get_permalink();
	$user_ID = get_current_user_id();
	if ( $user_ID != 0 ) { // if user is logged in

		// check if update or new insert
		if ( array_key_exists('project_id',$_GET) ) {
			$update_id = sanitize_text_field($_GET['project_id']);
			$project = get_post($update_id);
			if ( $project->post_author != $user_ID ) { // if current user is not the author
				$location .= "?step=1&feedback=wrong_user";
				wp_redirect($location);
				exit;

			} elseif ( $project->ID != $update_id ) { // if project does not exist yet
				$update_id = 0;
			}
		}
		else { $update_id = 0; }

		$field_prefix = "hce-form-step1-";
		$title = sanitize_text_field($_POST[$field_prefix.'name']);
		$content = sanitize_text_field($_POST[$field_prefix.'desc']);
		$cfield_prefix = '_hce_project_';
		// check if required fields exist
		if ( $title == '' ) { // if project name is empty
			$location .= "?step=1&feedback=required_field";
			wp_redirect($location);
			exit;
		}
		$req_cfields = array("built-area","useful-area","adjusted-area","users","budget");
		foreach ( $req_cfields as $req_cfield ) {
			$field = sanitize_text_field($_POST[$field_prefix.$req_cfield]);
			if ( $field == '' ) { // if any custom field is empty
				$location .= "?step=1&feedback=required_field";
				wp_redirect($location);
				exit;
			} else { $cfields[$cfield_prefix.$req_cfield] = $field; }
		}
		// end check if required fields exist

		// no required fields
		$notreq_cfields = array("address","city","state","cp","use","energy-label","energy-consumption","co2-emission");
		foreach ( $notreq_cfields as $notreq_cfield ) {
			$field = sanitize_text_field($_POST[$field_prefix.$notreq_cfield]);
			if ( $field != '' ) { $cfields[$cfield_prefix.$notreq_cfield] = $field; }
		}
		// end no required fields

		if ( $update_id != 0 ) { // if project exists, then update it
			$args = array(
				'ID' => $update_id,
				'post_title' => $title,
				'post_content' => $content,
			);
			// update project
			$project_id = wp_update_post($args);
			$location_feedback = "&feedback=project_updated";

		} else { // if project does not exist, then create it
			$args = array(
				'post_type' => 'project',
				'post_status' => 'draft',
				'post_author' => $user_ID,
				'post_title' => $title,
				'post_content' => $content,
			);
			// insert project
			$project_id = wp_insert_post($args);
			$location_feedback = "&feedback=project_inserted";

		} // end if project exists

			if ( $project_id != 0 ) { // if project has been created
				// insert custom fields
				reset($cfields);
				foreach ( $cfields as $key => $value ) {
					update_post_meta($project_id, $key, $value);
				}
				// create custom table for project in DB
				hce_project_create_table($project_id);
				$location .= "?step=2&project_id=".$project_id.$location_feedback;
			} // end if project has been created

		// redirect to prevent resend
		wp_redirect( $location );
		exit;

	} else { // if user is not logged in
		$location .= "?step=1&feedback=user";
		wp_redirect($location);
		exit;

	} // end if user is logged in

} // end insert project basic data from form

// create project table in DB
function hce_project_create_table($project_id) {
	global $wpdb;

	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}
	$table_name = $wpdb->prefix . "hce_project_" .$project_id; 

	$sql = "
	CREATE TABLE $table_name (
	  id bigint(20) unsigned NOT NULL auto_increment,
	  material_code varchar(12) NOT NULL default '',
	  material_name varchar(100) NOT NULL default '',
	  material_amount float(10,3) NOT NULL default 0,
	  material_unit varchar(10) NOT NULL default '',
	  construction_unit_code varchar(12) NOT NULL default '',
	  construction_unit_name varchar(100) NOT NULL default '',
	  construction_unit_amount float(10,3) NOT NULL default 0,
	  construction_unit_unit varchar(10) NOT NULL default '',
	  section_code varchar(12) NOT NULL default '',
	  section_name varchar(100) NOT NULL default '',
	  subsection_code varchar(12) NOT NULL default '',
	  subsection_name varchar(100) NOT NULL default '',
	  emission float(10,5) NOT NULL default 0,
	  emission_transport float(10,5) NOT NULL default 0,
	  PRIMARY KEY  (id)
	) $charset_collate;
	";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

} // end create project table in DB

// populate project table in DB
function hce_project_populate_table($project_id,$csv_file_id) {

	global $wpdb;
	$cfield_prefix = '_hce_project_';
	$location = get_permalink();

	// data file
	$filename = wp_get_attachment_url($csv_file_id); // relative path to data filename
	$line_length = "4096"; // max line lengh (increase in case you have longer lines than 1024 characters)
	// separator character
	if ( array_key_exists('hce-form-step2-delimiter', $_POST) ) { $delimiter = sanitize_text_field($_POST['hce-form-step2-delimiter']); }
	else { $delimiter = ""; }
	if ( $delimiter == '' ) { // if delimiter is not defined, error
		// if there was a problem with CSV file, we try to delete it
		if ( false === wp_delete_attachment( get_post_meta($project_id,$cfield_prefix.'csv_file',true), true ) ) {
		} else { delete_post_meta($project_id,$cfield_prefix.'csv_file'); }
		$location .= "?step=2&project_id=".$project_id."&feedback=csv_delimiter";
		wp_redirect($location);
		exit;
	}
	// enclosure character
	if ( array_key_exists('hce-form-step2-enclosure', $_POST) ) {
		$enclosure = sanitize_text_field($_POST['hce-form-step2-enclosure']);
		if ( $enclosure == "" ) { $enclosure = '"'; }
	}
	else { $enclosure = '"'; }
	
	// open the data file
	$fp = fopen($filename,'r');

	if ( $fp !== FALSE ) { // if the file exists and is readable
	
		$table = $wpdb->prefix . "hce_project_" .$project_id; 
		$format = array(
			//'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'
		);

		$line = 0;
		while ( ($fp_csv = fgetcsv($fp,$line_length,$delimiter,$enclosure)) !== FALSE ) { // begin main loop
			if ( $line == 0 ) { /* csv file headers */ }
			else {
				// check  empty lines
				if ( $fp_csv[0] == '' ) { /* empty or not valid line */ }
				else {
					// preparing data to insert
					$material_amount = str_replace(",",".",$fp_csv[3]);
					$material_amount = round($material_amount,3);
					$construction_unit_amount = str_replace(",",".",$fp_csv[7]);
					$construction_unit_amount = round($construction_unit_amount,3);
					$data = array(
						//'id' => is autoincrement
						'material_code' => mb_convert_encoding($fp_csv[0], "UTF-8"),
						'material_name' => mb_convert_encoding($fp_csv[2], "UTF-8"),
						'material_amount' => mb_convert_encoding($material_amount, "UTF-8"),
						'material_unit' => mb_convert_encoding($fp_csv[1], "UTF-8"),
						'construction_unit_code' => mb_convert_encoding($fp_csv[4], "UTF-8"),
						'construction_unit_name' => mb_convert_encoding($fp_csv[6], "UTF-8"),
						'construction_unit_amount' => mb_convert_encoding($construction_unit_amount, "UTF-8"),
						'construction_unit_unit' => mb_convert_encoding($fp_csv[5], "UTF-8"),
						'section_code' => mb_convert_encoding($fp_csv[10], "UTF-8"),
						'section_name' => mb_convert_encoding($fp_csv[11], "UTF-8"),
						'subsection_code' => mb_convert_encoding($fp_csv[8], "UTF-8"),
						'subsection_name' => mb_convert_encoding($fp_csv[9], "UTF-8")
					);
					/* create row */ $wpdb->insert( $table, $data, $format );
	
				} // end if valid line

			} // end if not line 0
			$line++;

		} // end main loop
		fclose($fp);

	} else { // if data file do not exist
		// if there was a problem with CSV file, we try to delete it
		if ( false === wp_delete_attachment( get_post_meta($project_id,$cfield_prefix.'csv_file',true), true ) ) {
		} else { delete_post_meta($project_id,$cfield_prefix.'csv_file'); }

		$location .= "?step=2&project_id=".$project_id."&feedback=populate_table";
		wp_redirect($location);
		exit;

	} // end if file exist and is readable

} // end populate project table in DB

// calculate CO2 emission for each material in the project table
// insert emission in project table AND
// insert top ten heaviest subtypes in postmeta table
function hce_project_calculate_emissions($project_id,$emission_type) {
	global $wpdb;
	$cfield_prefix = '_hce_project_';
	$building_total_emission = array();
		$building_total_emission['value'] = 0;
	$table_p = $wpdb->prefix . "hce_project_" .$project_id;
	$table_m = $wpdb->prefix . "hce_materials";
	$table_e = $wpdb->prefix . "hce_emissions";
	if ( $emission_type == 'intrinsic' ) {
		$col_to_update = "emission";
		$building_total_emission['key'] = 'emission_total';
		$sql_query = "
			SELECT
			  p.id,
			  p.material_amount,
			  m.material_mass,
			  m.component_1,
			  m.component_1_mass,
			  m.dap_factor,
			  e.emission_factor
			FROM $table_p p
			LEFT JOIN $table_m m
			  ON p.material_code = m.material_code
			LEFT JOIN $table_e e
			  ON m.component_1 = e.subtype
			WHERE p.material_amount != 0
			  AND m.component_1_mass != 0
		UNION ALL
			SELECT
			  p.id,
			  p.material_amount,
			  m.material_mass,
			  m.component_2,
			  m.component_2_mass,
			  m.dap_factor,
			  e.emission_factor
			FROM $table_p p
			LEFT JOIN $table_m m
			  ON p.material_code = m.material_code
			LEFT JOIN $table_e e
			  ON m.component_2 = e.subtype
			WHERE p.material_amount != 0
			  AND m.component_2_mass != 0
		UNION ALL
			SELECT
			  p.id,
			  p.material_amount,
			  m.material_mass,
			  m.component_3,
			  m.component_3_mass,
			  m.dap_factor,
			  e.emission_factor
			FROM $table_p p
			LEFT JOIN $table_m m
			  ON p.material_code = m.material_code
			LEFT JOIN $table_e e
			  ON m.component_3 = e.subtype
			WHERE p.material_amount != 0
			  AND m.component_3_mass != 0
		";

	} elseif ( $emission_type == 'transport' ) {
		$col_to_update = "emission_transport";
		$building_total_emission['key'] = 'emission_transport_total';
		$topten = get_post_meta($project_id,$cfield_prefix."mass_topten");
		$select_where = "";
		foreach ( $topten as $tt ) { $select_where .= "'".$tt."', "; }
		$select_where = substr($select_where,0,-2);
		$sql_query = "
			SELECT
			  p.id,
			  p.material_amount,
			  m.material_mass,
			  m.component_1,
			  m.component_1_mass,
			  m.dap_factor
			FROM $table_p p
			LEFT JOIN $table_m m
			  ON p.material_code = m.material_code
			WHERE p.material_amount != 0
			  AND m.component_1_mass != 0
			  AND m.component_1 IN ($select_where)
		UNION ALL
			SELECT
			  p.id,
			  p.material_amount,
			  m.material_mass,
			  m.component_2,
			  m.component_2_mass,
			  m.dap_factor
			FROM $table_p p
			LEFT JOIN $table_m m
			  ON p.material_code = m.material_code
			WHERE p.material_amount != 0
			  AND m.component_2_mass != 0
			  AND m.component_2 IN ($select_where)
		UNION ALL
			SELECT
			  p.id,
			  p.material_amount,
			  m.material_mass,
			  m.component_3,
			  m.component_3_mass,
			  m.dap_factor
			FROM $table_p p
			LEFT JOIN $table_m m
			  ON p.material_code = m.material_code
			WHERE p.material_amount != 0
			  AND m.component_3_mass != 0
			  AND m.component_3 IN ($select_where)
		";

		// prepare topten $_POST data
		$topten_fields = array('subtype','distance','type');
		$topten_data = array();
		$w_count = 1;
		while ( $w_count <= 10 ) {
			$key = sanitize_text_field($_POST['hce-form-step3-transport-'.$topten_fields[0]."-".$w_count]);
			$distance = sanitize_text_field($_POST['hce-form-step3-transport-'.$topten_fields[1]."-".$w_count]);
			$type = sanitize_text_field($_POST['hce-form-step3-transport-'.$topten_fields[2]."-".$w_count]);
			$topten_data[$key] = array(
				'distance' => $distance,
				'emission' => $type
			);
			$w_count++;
		}

	} // end if emission type
	$query_results = $wpdb->get_results( $sql_query , ARRAY_A );

	$count = 0;
	$emissions = array();
	$weight = array();
	$building_total_weight = 0;
	foreach ( $query_results as $material ) {
		$count++;
		$material_id = $material['id'];

		if ( $emission_type == 'intrinsic' ) { // if intrinsic emissions
			// emission maths
			if ( !array_key_exists($material_id,$emissions) ) {
				$emissions[$material_id][] = $material['material_amount'] * $material['material_mass'] * $material['dap_factor'];
			}
			$this_weight = $material['material_amount'] * $material['component_1_mass'];
			$emissions[$material_id][] = $this_weight * $material['emission_factor'];

			// total weight of building
			$building_total_weight += $this_weight;

			// weight of subtypes array
			if ( !array_key_exists($material['component_1'],$weight) ) {
				$weight[$material['component_1']] = $material['material_amount'] * $material['component_1_mass'];
			} else {
				$weight[$material['component_1']] += $material['material_amount'] * $material['material_mass'];
			}
			
		} elseif ( $emission_type == 'transport' ) { // if transport emissions
			$material_subtype = $material['component_1'];
			// emission maths
			if ( !array_key_exists($material_id,$emissions) ) { // if first subtype of this material
				if ( $material['dap_factor'] == 0 ) { $emissions[$material_id][] = 0; }
				else { $emissions[$material_id][] = $material['material_amount'] * $material['material_mass'] * $topten_data[$material_subtype]['emission'] * $topten_data[$material_subtype]['distance']; }
			} // end if first subtype
				$emissions[$material_id][] = $material['material_amount'] * $material['component_1_mass'] * $topten_data[$material_subtype]['emission'] * $topten_data[$material_subtype]['distance'];

		} // end if emission type

	}

	if ( $emission_type == 'intrinsic' ) {
		// sort subtypes: heaviest to lightest
		arsort($weight);
		// select top ten
		$weight_topten = array_slice($weight, 0, 10, true);
		foreach ( $weight_topten as $subtype => $kg ) {
			add_post_meta($project_id, $cfield_prefix.'mass_topten', $subtype, false);
		}
		// save total weight of building
		update_post_meta($project_id, $cfield_prefix.'mass_total', $building_total_weight);
	}

	$update_cases = array();
	$update_where = array();
	$update_ids = array();
	foreach ( $emissions as $id => $emission ) {
		if ( $emission[0] == 0 ) { // if there is no DAP, then take three subtypes
			if ( !array_key_exists(2,$emission) ) { $emission[2] = 0; }
			if ( !array_key_exists(3,$emission) ) { $emission[3] = 0; }
			$material_emission = ( $emission[1] + $emission[2] + $emission[3] );

		} else { /* if there is DAP, then ignore three subtypes */ $material_emission = $emission[0]; }

		if ( $material_emission != 0 ) {
			array_push($update_cases,"WHEN ".$id." THEN '".$material_emission."'");
			array_push($update_where,"%s");
			array_push($update_ids,$id);

			// total emission of building
			$building_total_emission['value'] += $material_emission;
		}
	}
	$update_cases = implode(" ", $update_cases);
	$update_where = implode(", ", $update_where);
	$query_update = "
		UPDATE $table_p
		SET $col_to_update = CASE id
		  $update_cases
		  END
		WHERE id IN ($update_where)
	";
	$wpdb->query( $wpdb->prepare($query_update, $update_ids) );

	// save total emissions of building
	update_post_meta($project_id, $cfield_prefix.$building_total_emission['key'], $building_total_emission['value']);

} // end calculate CO2 emission for each material in the project table

// upload project file
function hce_project_upload_file() {
	global $wpdb;
	$location = get_permalink();
	$cfield_prefix = '_hce_project_';
	$user_ID = get_current_user_id();

	if ( $user_ID != 0 ) { // if user is logged in

		if ( array_key_exists('project_id', $_GET) ) { $project_id = sanitize_text_field($_GET['project_id']); }
		else { $project_id = 0; }
		$project = get_post($project_id);
		if ( $project->post_author != $user_ID ) { // if current user is not the author
				$location .= "?step=1&feedback=wrong_user";
				wp_redirect($location);
				exit;

		} elseif ( $project->ID != $project_id ) { // if project does not exist yet
				$location .= "?step=1&feedback=project";
				wp_redirect($location);
				exit;
		}

		// if 'Delete file' button has been click, then delete file
		if ( sanitize_text_field($_POST['hce-form-step-submit']) == 'Sustituir archivo' ) {
			if ( false === wp_delete_attachment( get_post_meta($project_id,$cfield_prefix.'csv_file',true), true ) ) {
				$location .= "?step=2&project_id=".$project_id."&feedback=file_not_deleted";
			} else {
				$cfields_to_delete = array('csv_file','mass_topten','mass_total','emission_total','emission_transport_total');
				foreach ( $cfields_to_delete as $cfield_name ) { delete_post_meta($project_id,$cfield_prefix.$cfield_name); }
				foreach ( array('transport_distance','transport_type') as $cfield_name) {
					for ( $c = 1; $c <= 10; $c++ ) {
						delete_post_meta($project_id,$cfield_prefix.$cfield_name."-".$c); 
					}
				}
				$table = $wpdb->prefix. "hce_project_" .$project_id;
				/* empty project table */ $wpdb->query( "TRUNCATE TABLE `$table`" ); 
				$args = array(
					'ID' => $project_id,
					'post_status' => 'draft',
				);
				// update project
				$updated_id = wp_update_post($args);
				$location .= "?step=2&project_id=".$project_id."&feedback=file_deleted";
			}
			wp_redirect($location);
			exit;

		}
		// end delete file

		// check if file has been added with form
		if ( array_key_exists('hce-form-step2-csv', $_FILES) ) {
			$file = $_FILES['hce-form-step2-csv'];
			// recheck if file has been added with form
			if ( $file['name'] != '' ) {
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$mime = $finfo->file($file['tmp_name']); 
				// checking if csv file has the right format and size
				if ( $mime == 'text/plain' || $mime == 'text/csv' ) { /* if is text plain file */ }
				else {
					$location .= "?step=2&project_id=".$project_id."&feedback=fileformat";
					wp_redirect($location);
					exit;
				}
				if ( $file['size'] >= '40000' ) {
					$location .= "?step=2&project_id=".$project_id."&feedback=filesize";
					wp_redirect($location);
					exit;
				}

			} else {
				// if filename is empty: no file uploaded
				$location .= "?step=2&project_id=".$project_id."&feedback=nofile";
				wp_redirect($location);
				exit;

			} // end recheck if file has been added with form

		} else {
			// if no file uploaded
			$location .= "?step=2&project_id=".$project_id."&feedback=nofile";
			wp_redirect($location);
			exit;

		} // end check if file has been added with form

		// file insert
		$upload_dir_vars = wp_upload_dir();
		$upload_dir = $upload_dir_vars['path']; // absolute path to uploads folder
		$uploaddir = realpath($upload_dir);

		$filename = basename($file['name']); // file name in client machine
		$filename = trim($filename); // removing spaces at the begining and end
		$filename = str_replace(" ", "-", $filename); // removing spaces inside the name

		$typefile = $file['type']; // file type
		$uploadfile = $uploaddir.'/'.$filename;

		$slugname = preg_replace('/\.[^.]+$/', '', basename($uploadfile));

		// if filename already associated to other file
		if ( file_exists($uploadfile) ) {
			$count = "a";
			while ( file_exists($uploadfile) ) {
				$count++;
				$uploadfile = $uploaddir.'/'.$slugname.'-'.$count.'.csv';
			}
		} // end if filename already associated to other file

		// if the file is correctly uploaded, then do the insert
		if ( move_uploaded_file($file['tmp_name'], $uploadfile) ) {
			$attachment = array(
				'post_mime_type' => $typefile,
				'post_title' => "Materiales del proyecto " .$project->post_title,
				'post_content' => '',
				'post_status' => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $uploadfile, $project_id );

			if ( $attach_id != 0 ) { // if CSV has been inserted
				// you must first include the image.php file
				// for the function wp_generate_attachment_metadata() to work
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');

				$attach_data = wp_generate_attachment_metadata( $attach_id, $uploadfile );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
		
				update_post_meta($project_id, $cfield_prefix.'csv_file', $attach_id);
				//$img_url = wp_get_attachment_url($attach_id);

				// populate project table with file data
				hce_project_populate_table($project_id,$attach_id);

				// calculate CO2 emission for each material in the project table
				hce_project_calculate_emissions($project_id,'intrinsic');

			} // end if CSV has been inserted

		} // end if the file is correctly uploaded

		// redirect to prevent resend
		$location .= "?step=3&project_id=".$project_id."&feedback=file_uploaded";
		wp_redirect( $location );
		exit;

	} else { // if user is not logged in
			$location .= "?step=1&feedback=user";
			wp_redirect($location);
			exit;

	} // end if user is logged in

} // end upload project file

// calculate transport emission for top ten subtypes
function hce_project_emission_transport() {
	global $wpdb;
	$location = get_permalink();
	$cfield_prefix = '_hce_project_';
	$user_ID = get_current_user_id();

	if ( $user_ID != 0 ) { // if user is logged in
		if ( array_key_exists('project_id', $_GET) ) { $project_id = sanitize_text_field($_GET['project_id']); }
		else { $project_id = 0; }
		$project = get_post($project_id);
		if ( $project->post_author != $user_ID ) { // if current user is not the author
				$location .= "?step=1&feedback=wrong_user";
				wp_redirect($location);
				exit;

		} elseif ( $project->ID != $project_id ) { // if project does not exist yet
				$location .= "?step=1&feedback=project";
				wp_redirect($location);
				exit;
		}

		// check if all required fields are not empty
		// save form values as project custom fields
		$fields_to_save = array('distance','type');
		foreach ( $fields_to_save as $field ) {
			$save_count = 1;
			while ( $save_count <= 10 ) {
				$value = sanitize_text_field($_POST['hce-form-step3-transport-'.$field."-".$save_count]);
				if ( $value == '' ) {
					$location .= "?step=3&project_id=".$project_id."&feedback=required_field";
					wp_redirect($location);
					exit;
				}
				update_post_meta($project_id, $cfield_prefix.'transport_'.$field.'-'.$save_count, $value);
				$save_count++;
			}
		}

		// calculate transport CO2 emissions for each material in the top ten
		hce_project_calculate_emissions($project_id,'transport');
		$args = array(
			'ID' => $post->ID,
			'post_status' => 'private',
		);
		// update project
		$updated_id = wp_update_post($args);
		$location .= "?step=4&project_id=".$project_id."feedback=eval_complete";
		wp_redirect($location);
		exit;

	} else { // if user is not logged in
			$location .= "?step=1&feedback=user";
			wp_redirect($location);
			exit;

	} // end if user is logged in

} // end calculate transport emission for top ten subtypes

// display HCE form to evaluate a project
function hce_form() {
	$cfield_prefix = '_hce_project_';

	if ( !is_user_logged_in() ) { // if user is logged in, then hce form
		if ( array_key_exists('redirect_to', $_GET) ) { $redirect_url = sanitize_text_field($_GET['redirect_to']); }
		else { $redirect_url = site_url( $_SERVER['REQUEST_URI'] ); }
		$login_form = hce_login_form($redirect_url);
		return $login_form;
	}

	// form step
	if ( array_key_exists('step', $_GET) ) { $step = sanitize_text_field($_GET['step']); }
	else { $step = 1; }

	// actions depending on step
	if ( $step == 2 && array_key_exists('hce-form-step-submit',$_POST) ) {
		// insert project basic data
		// create project table
		hce_project_insert_basic_data();

	} // end step 2 actions
	elseif ( $step == 3 && array_key_exists('hce-form-step-submit',$_POST ) ) {
		// upload project file
		// populate project table
		// do emissions maths and insert emissions in project table
		hce_project_upload_file();
	} // end step 3 actions
	elseif ( $step == 4 && array_key_exists('hce-form-step-submit',$_POST ) ) {
		// do transport emissions maths and insert emissions in project table
		hce_project_emission_transport();
	} // end step 4 actions

	$last_step = 4;
	$location = get_permalink();
	$user_ID = get_current_user_id();

	// form step and current project id
	if ( array_key_exists('project_id', $_GET) ) {
		$project_id = sanitize_text_field($_GET['project_id']);
		$project = get_post($project_id,ARRAY_A);
		if ( $project['ID'] != $project_id ) { // if project does not exists
			$location .= "?step=1&feedback=project";
			wp_redirect( $location );
			exit;
		}
		elseif ( $user_ID != $project['post_author'] ) { // if user is not the author
			$location .= "?step=1&feedback=wrong_user";
			wp_redirect( $location );
			exit;
		}

	} else { $project_id = 0; }
	// form feedback
	if ( array_key_exists('feedback',$_GET) ) { $form_feedback = sanitize_text_field($_GET['feedback']); }
	else { $form_feedback = '';  }

	if ( $step >> $last_step ) {
		wp_redirect( $location );
		exit;
	}

	// prev and next steps links
	if ( $step == $last_step ) { $action_next = get_permalink($project_id)."?referer=form"; } else {
		$next_step = $step + 1;
		$action_next = $location."?step=".$next_step;
		if ( $project_id != 0 ) { $action_next .= "&project_id=".$project_id; }
	}
	if ( $step != 1 ) {
		$prev_step = $step - 1;
		$action_prev = $location."?step=".$prev_step;
		if ( $project_id != 0 ) { $action_prev .= "&project_id=".$project_id; }
		$prev_step_out = "<span class='glyphicon glyphicon-chevron-left'></span> <a href='".$action_prev."' class='btn btn-default'>Volver al paso ".$prev_step."</a>";
	} else { $action_prev = ""; $prev_step_out = ""; }

	// FORM FEEDBACK: error and success
	if ( $form_feedback != '' ) {
		if ( $form_feedback == 'required_field' ) { $feedback_type = "danger"; $feedback_text = "Alguno de los campos requeridos para enviar el formulario están vacíos. Por favor, revisalos y vuelve a intentarlo."; }
		elseif ( $form_feedback == 'fileformat' ) { $feedback_type = "danger"; $feedback_text = "El archivo debe ser CSV. Parece que el que has intentado subir no lo es. Puedes intentarlo de nuevo en el formulario de abajo."; }
		elseif ( $form_feedback == 'filesize' ) { $feedback_type = "danger"; $feedback_text = "El archivo debe ser menor de 40kB. Parece que el que has intentado subir pesa más. Puedes intentarlo de nuevo en el formulario de abajo."; }
		elseif ( $form_feedback == 'nofile' ) { $feedback_type = "danger"; $feedback_text = "No has añadido ningún archivo. Sin archivo no puedes continuar con el proceso de evaluación de tu proyecto."; }
		elseif ( $form_feedback == 'project' ) { $feedback_type = "danger"; $feedback_text = "Algo no encaja: el proyecto que intentas evaluar parece que no existe. Vuelve a empezar."; }
		elseif ( $form_feedback == 'wrong_user' ) { $feedback_type = "danger"; $feedback_text = "Algo no encaja: parece que tú no eres el autor del proyecto que intentas editar. Vuelve a empezar."; }
		elseif ( $form_feedback == 'file_not_deleted' ) { $feedback_type = "danger"; $feedback_text = "Algo falló: el archivo de mediciones no se ha eliminado. Quizás quieras volver a intentarlo."; }
		elseif ( $form_feedback == 'populate_table' ) { $feedback_type = "danger"; $feedback_text = "La información del archivo de mediciones no pudo incorporarse a la base de datos. Revisa la estructura del archivo e intenta volver a subirlo al servidor."; }
		elseif ( $form_feedback == 'csv_delimiter' ) { $feedback_type = "danger"; $feedback_text = "Para poder subir el archivo CSV con las mediciones tienes que rellenar el campo separador, para que podamos procesar los datos del archivo."; }
		elseif ( $form_feedback == 'user' ) { $feedback_type = "success"; $feedback_text = "Debes iniciar sesión para evaluar un proyecto."; }
		elseif ( $form_feedback == 'project_inserted' ) { $feedback_type = "success"; $feedback_text = "Los datos del proyecto han sido guardados."; }
		elseif ( $form_feedback == 'project_updated' ) { $feedback_type = "success"; $feedback_text = "Los datos del proyecto han sido actualizados."; }
		elseif ( $form_feedback == 'file_uploaded' ) { $feedback_type = "success"; $feedback_text = "El archivo de mediciones ha sido guardado correctamente."; }
		elseif ( $form_feedback == 'file_deleted' ) { $feedback_type = "success"; $feedback_text = "El archivo de mediciones ha sido eliminado. Ahora puedes añadir uno nuevo."; }
		$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";
	} else { $feedback_out = ""; }
	// end ERROR FEEDBACK

	// WHAT TO SHOW
	// in step 1
	if ( $step == 1 ) {
		$field_names = array("address","city","state","cp","use","built-area","useful-area","adjusted-area","users","budget","energy-label","energy-consumption","co2-emission");
		if ( $project_id != 0 ) { // if project_id is defined
			$value['name'] = get_the_title($project_id);
			$value_desc = $project['post_content'];
			foreach ( $field_names as $field_name ) {
				$value[$field_name] = get_post_meta($project_id,$cfield_prefix.$field_name,TRUE);
			}

		} else { // if project_id is not defined
			$value['name'] = '';
			$value_desc = '';
			foreach ( $field_names as $field_name ) {
				$value[$field_name] = '';
			}

		} // end if project_id is defined

		$enctype_out = "";
		$submit_out = 'Guardar e ir al paso '.$next_step;
		$next_step_out = "<input class='btn btn-primary ' type='submit' value='".$submit_out."' name='hce-form-step-submit' /> <span class='glyphicon glyphicon-chevron-right'></span>";
		// fields
		$fields = array(
			array(
				'label' => 'Nombre del proyecto',
				'name' => 'name',
				'required' => 1,
				'unit' => '',
				'comment' => '<span class="glyphicon glyphicon-asterisk"></span> Campos requeridos.',
				'value' => $value['name']
			),
			array(
				'label' => 'Calle',
				'name' => 'address',
				'required' => 0,
				'unit' => '',
				'comment' => '',
				'value' => $value['address']
			),
			array(
				'label' => 'Localidad',
				'name' => 'city',
				'required' => 0,
				'unit' => '',
				'comment' => '',
				'value' => $value['city']
			),
			array(
				'label' => 'Provincia',
				'name' => 'state',
				'required' => 0,
				'unit' => '',
				'comment' => '',
				'value' => $value['state']
			),
			array(
				'label' => 'Código postal',
				'name' => 'cp',
				'required' => 0,
				'unit' => '',
				'comment' => '',
				'value' => $value['cp']
			),
			array(
				'label' => 'Uso',
				'name' => 'use',
				'required' => 0,
				'unit' => '',
				'comment' => '',
				'value' => $value['use']
			),
			array(
				'label' => 'Superficie construida',
				'name' => 'built-area',
				'required' => 1,
				'unit' => 'm2',
				'comment' => '',
				'value' => $value['built-area']
			),
			array(
				'label' => 'Superficie útil',
				'name' => 'useful-area',
				'required' => 1,
				'unit' => 'm2',
				'comment' => '',
				'value' => $value['useful-area']
			),
			array(
				'label' => 'Superficie computable',
				'name' => 'adjusted-area',
				'required' => 1,
				'unit' => 'm2',
				'comment' => '',
				'value' => $value['adjusted-area']
			),
			array(
				'label' => 'Número de usuarios',
				'name' => 'users',
				'required' => 1,
				'unit' => '',
				'comment' => '',
				'value' => $value['users']
			),
			array(
				'label' => 'Presupuesto',
				'name' => 'budget',
				'required' => 1,
				'unit' => '€',
				'comment' => '',
				'value' => $value['budget']
			),
			array(
				'label' => 'Calificación energética',
				'name' => 'energy-label',
				'required' => 0,
				'unit' => '',
				'comment' => '',
				'value' => $value['energy-label']
			),
			array(
				'label' => 'Consumo energético anual',
				'name' => 'energy-consumption',
				'required' => 0,
				'unit' => 'kWh/m2 año',
				'comment' => '',
				'value' => $value['energy-consumption']
			),
			array(
				'label' => 'Emisión anual de CO2',
				'name' => 'co2-emission',
				'required' => 0,
				'unit' => 'Kg CO2/m2 año',
				'comment' => '',
				'value' => $value['co2-emission']
			),
		);
	
		$fields_out = "";
		foreach ( $fields as $field ) {
			if ( $field['required'] == 1 ) { $req_class = " <span class='glyphicon glyphicon-asterisk'></span>"; } else { $req_class = ""; }
			if ( $field['unit'] != '' ) {
				$feedback_class = " has-feedback";
				$feedback = "<span class='form-control-feedback'>".$field['unit']."</span>";
			} else { $feedback_class = ""; $feedback = ""; }
			if ( $field['comment'] != '' ) {
	    			$help = "<p class='help-block col-sm-4'><small>".$field['comment']."</small></p>";
			} else { $help = ""; }
			$fields_out .= "
			<fieldset class='form-group".$feedback_class."'>
				<label for='hce-form-step".$step."-".$field['name']."' class='col-sm-3 control-label'>".$field['label'].$req_class."</label>
				<div class='col-sm-5'>
					<input class='form-control' type='text' value='".$field['value']."' name='hce-form-step".$step."-".$field['name']."' />
					".$feedback."
				</div>
				".$help."
			</fieldset>
			";
		}
		$fields_out .= "
		<fieldset class='form-group'>
				<label for='hce-form-step".$step."-desc' class='col-sm-3 control-label'>Descripción del proyecto</label>
				<div class='col-sm-5'>
					<textarea class='form-control' rows='3' name='hce-form-step".$step."-desc'>".$value_desc."</textarea>
				</div>
		</fieldset>
		";

	}
	// in step 2
	elseif ( $step == 2 ) {
		// check if project has already a csv file uploaded
		$csv_file_id = get_post_meta($project_id,'_hce_project_csv_file',true);
		if ( $csv_file_id != '' ) {
			$enctype_out = "";
			$link_out = 'Ir al paso '.$next_step;
			$submit_out = 'Sustituir archivo';
			$next_step_out = "<input class='btn btn-danger' type='submit' value='".$submit_out."' name='hce-form-step-submit' /> <span class='glyphicon glyphicon-warning-sign'></span> <a class='second-submit-button btn btn-primary' href='".$location."?step=".$next_step."&project_id=".$project_id."'>".$link_out."</a> <span class='glyphicon glyphicon-chevron-right'></span>";
			$fields_out = "
			<fieldset class='form-group'>
				<label for='hce-form-step".$step."-csv' class='col-sm-3 control-label'>Archivo de mediciones</label>
				<div class='col-sm-5'>
					<div class='alert alert-info' role='alert'>
					<p><strong>El archivo de mediciones de este proyecto ya fue añadido</strong> en algún momento, y sus datos procesados.</p>
					<p><strong>Si quieres actualizar esos datos</strong> utiliza el botón rojo 'Sustituir archivo'. Ten en cuenta que esta opción eliminará los datos guardados actualmente, con excepción de los datos básicos del proyecto introducidos en el paso 1.</p>
					</div>
				</div>
			</fieldset>
			";		

		} else {
			$enctype_out = " enctype='multipart/form-data'";
			$submit_out = 'Subir archivo e ir al paso '.$next_step;
			$next_step_out = "<input class='btn btn-primary ' type='submit' value='".$submit_out."' name='hce-form-step-submit' /> <span class='glyphicon glyphicon-chevron-right'></span>";
			$fields_out = "
			<fieldset class='form-group'>
				<label for='hce-form-step".$step."-csv' class='col-sm-3 control-label'>Archivo CSV de mediciones <span class='glyphicon glyphicon-asterisk'></span></label>
				<div class='col-sm-4'>
					<input type='file' name='hce-form-step".$step."-csv' />
					<input type='hidden' name='MAX_FILE_SIZE' value='40000' />
				</div>
				<p class='col-sm-5 help-block'><small><span class='glyphicon glyphicon-asterisk'></span> Campos requeridos.<br />Formato <abbr title='Comma Separated Values'>CSV</abbr>. Tamaño máximo 40kB.</small></p>
			</fieldset>
			<fieldset class='form-group'>
				<label for='hce-form-step".$step."-delimiter' class='col-sm-3 control-label'>Caracter separador de campos del archivo CSV <span class='glyphicon glyphicon-asterisk'></span></label>
				<div class='col-sm-1'>
					<input maxlength='1' class='form-control' type='text' value='' name='hce-form-step".$step."-delimiter' />
				</div>
				<p class='col-sm-5 col-sm-offset-3 help-block'><small><strong>Indica dónde acaba un campo y empieza el siguiente</strong> en una línea del archivo CSV. Suele ser la coma, el punto y coma o el tabulador, pero puede ser cualquiera.</small></p>
			</fieldset>
			<fieldset class='form-group'>
				<label for='hce-form-step".$step."-enclosure' class='col-sm-3 control-label'>Caracter delimitador de campos del archivo CSV</label>
				<div class='col-sm-1'>
					<input maxlength='1' class='form-control' type='text' value='' name='hce-form-step".$step."-enclosure' />
				</div>
				<p class='col-sm-5 col-sm-offset-3 help-block'><small><strong>Rodea cada campo, delimitándolo</strong>. Suele ser la comilla o la doble comilla, pero puede ser cualquiera. <strong>Hay archivos CSV que no utilizan caracter de cercado</strong> de los campos: si éste es el caso del archivo que vas a subir, deja este campo vacío.<br /><br /><strong>Por ejemplo</strong>, línea con separador <strong>punto y coma</strong>, y delimitador <strong>comilla simple</strong>:<br /><code>'Contenido del primer campo';'Contenido del segundo campo'</code></small></p>
			</fieldset>
			";

		}

	}
	// in step 3
	elseif ( $step == 3 ) {
		global $wpdb;
		$table_e = $wpdb->prefix . "hce_emissions";
		$select_query = "SELECT emission_factor,subtype FROM $table_e WHERE type='TRANSPORTE'";
		$types = $wpdb->get_results($select_query,OBJECT_K);
		$topten = get_post_meta($project_id,$cfield_prefix."mass_topten");
		$enctype_out = "";
		$submit_out = "Calcular emisiones";
		$next_step_out = "<input class='btn btn-primary' type='submit' value='".$submit_out."' name='hce-form-step-submit' /> <span class='glyphicon glyphicon-chevron-right'></span>";
		$distances = array(
			'200' => 'Local (200 km)',
			'800' => 'Nacional (800 km)',
			'2500' => 'Europea (2500 km)',
			'8000' => 'Internacional (8000 km)'
		);
		$tt_count = 0;
		$fields_out = "
		<fieldset class='form-group'>
			<div class='col-sm-3 textr'><strong>Material</strong></div>
			<div class='col-sm-2'><strong>Distancia</strong></div>
			<div class='col-sm-3'><strong>Medio</strong></div>
		</fieldset>
		";
		foreach ( $topten as $tt ) {
			$tt_count++;
			if ( $tt_count == 1 ) {
			$help = "<p class='col-sm-4 help-block'><small><span class='glyphicon glyphicon-asterisk'></span> Campos requeridos.</small></p>";
			} else { $help = ""; }
			$current_distance = get_post_meta($project_id,$cfield_prefix."transport_distance-".$tt_count,TRUE);
			$distances_out = "<option value=''></option>";
			foreach ( $distances as $value => $text ) {
				if ( $value == $current_distance ) { $selected = " selected"; }
				else { $selected = "";}
				$distances_out .= "<option value='".$value."'".$selected.">".$text."</option>";
			}
			$current_type = get_post_meta($project_id,$cfield_prefix."transport_type-".$tt_count,TRUE);
			$types_out = "<option value=''></option>";
			foreach ( $types as $value => $text ) {
				if ( $value == $current_type ) { $selected = " selected"; }
				else { $selected = "";}
				$types_out .= "<option value='".$text->emission_factor."'".$selected.">".$text->subtype."</option>";
			}
			$fields_out .= "
			<fieldset class='form-group'>
				<div class='col-sm-3 textr'>
					".$tt." <span class='glyphicon glyphicon-asterisk'></span>
					<input type='hidden' name='hce-form-step".$step."-transport-subtype-".$tt_count."' value='".$tt."' />
				</div>
				<div class='col-sm-2'>
					<select class='form-control' name='hce-form-step".$step."-transport-distance-".$tt_count."'>".$distances_out."</select>
				</div>
				<div class='col-sm-3'>
					<select class='form-control' name='hce-form-step".$step."-transport-type-".$tt_count."'>".$types_out."</select>
				</div>
				".$help."
			</fieldset>
			";
		}
	}
	// in step 4
	elseif ( $step == 4 ) {
		$submit_out = "Ver informe de emisiones";
		$next_step_out = "<a class='btn btn-primary' href='".$action_next."'>".$submit_out."</a>";
	}
	// END WHAT TO SHOW

	// steps nav menu
	$btns = array(
		array(
			'step' => 1,
			'status' => " btn-default",
			'text' => "Proyecto",
			'after' => " <span class='glyphicon glyphicon-chevron-right'></span> "
		),
		array(
			'step' => 2,
			'status' => " btn-default",
			'text' => "Materiales",
			'after' => " <span class='glyphicon glyphicon-chevron-right'></span> "
		),
		array(
			'step' => 3,
			'status' => " btn-default",
			'text' => "Transporte",
			'after' => " <span class='glyphicon glyphicon-chevron-right'></span> "
		),
		array(
			'step' => 4,
			'status' => " btn-default",
			'text' => "Resultado",
			'after' => ""
		),

	);
	$nav_btns_out = "<strong>Pasos:</strong> ";
	reset($btns);
	foreach ( $btns as $btn ) {
		if ( $step == $btn['step'] ) { $btn['status'] = " btn-primary"; }
		$nav_btns_out .= "<button type='button' class='btn btn-sm".$btn['status']."' disabled='disabled'>".$btn['step'].". ".$btn['text']."</button>".$btn['after'];	
	}

	// form output
	if ( $step == 4 ) {
		$form_out = "
		<div class='row'>
			<div id='form-steps' class='col-sm-5'>".$nav_btns_out."</div>
		</div>
	
		<div class='row'>
			<div class='col-sm-offset-3 col-sm-5'>
				<div class='alert alert-info' role='alert'>
					<p><strong>El proceso de cálculo de las emisiones de CO2 de tu proyecto se ha completado.</strong></p>
					<p>¡Enhorabuena! Y gracias por usar la herramienta arCO2.</p>
				</div>
			</div>
		</div>
		<div class='row'>
			<div class='col-sm-offset-3 col-sm-5'>
					".$prev_step_out."
					<div class='pull-right'>".$next_step_out."</div>
	    			</div>
			</div>
		</div>
		";

	} else {
		$form_out = "
		<div class='row'>
			<div id='form-steps' class='col-sm-5'>".$nav_btns_out."</div>
			<div class='col-sm-3'>".$feedback_out."</div>
		</div>
	
		<form class='row' id='hce-form-step".$step."' method='post' action='" .$action_next. "'" .$enctype_out. ">
			<div class='form-horizontal col-sm-12'>
			".$fields_out."
			<fieldset class='form-group'>
				<div class='col-sm-offset-3 col-sm-5'>
					".$prev_step_out."
					<div class='pull-right'>".$next_step_out."</div>
	    			</div>
			</fieldset>
			</div>
		</form>
		";
	}
	return $form_out;
} // end display HCE form to evaluate a project

// create or update emissions table in DB
global $emissions_ver;
$emissions_ver = "0.2"; 
function hce_db_emissions_table() {
	global $wpdb;
	global $emissions_ver;

	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}
	$table_name = $wpdb->prefix . "hce_emissions"; 

	$sql = "
	CREATE TABLE $table_name (
	  id bigint(20) unsigned NOT NULL auto_increment,
	  opendap_code char(7) NOT NULL default '0000000',
	  type varchar(200) NOT NULL default '',
	  subtype varchar(200) NOT NULL default '',
	  emission_factor float(10,5) NOT NULL default 0,
	  PRIMARY KEY  (id)
	) $charset_collate;
	";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	update_option( 'hce_emissions_version', $emissions_ver );

} // end create emissions table in DB

// create materials table in DB
global $materials_ver;
$materials_ver = "0.2";
function hce_db_materials_table() {
	global $wpdb;
	global $materials_ver;

	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}
	$table_name = $wpdb->prefix . "hce_materials"; 
	$sql = "
	CREATE TABLE $table_name (
	  id bigint(20) unsigned NOT NULL auto_increment,
	  material_code varchar(12) NOT NULL default '',
	  material_name varchar(100) NOT NULL default '',
	  material_unit varchar(10) NOT NULL default '',
	  material_mass float(10,5) NOT NULL default 0,
	  component_1 varchar(100) NOT NULL default '',
	  component_1_mass float(10,5) NOT NULL default 0,
	  component_2 varchar(100) NOT NULL default '',
	  component_2_mass float(10,5) NOT NULL default 0,
	  component_3 varchar(100) NOT NULL default '',
	  component_3_mass float(10,5) NOT NULL default 0,
	  dap_factor float(10,5) NOT NULL default 0,
	  PRIMARY KEY  (id)
	) $charset_collate;
	";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	update_option( 'hce_materials_version', $materials_ver );

} // end create materials table in DB

// update custom tables in DB
function hce_db_custom_tables_update() {
	global $wpdb;
	global $emissions_ver;
	global $materials_ver;

	$emissions_installed_ver = get_option( "hce_emissions_version" );
	$materials_installed_ver = get_option( "hce_materials_version" );

	if ( $emissions_installed_ver != $emissions_ver ) {
		hce_db_emissions_table();
	}
	if ( $materials_installed_ver != $materials_ver ) {
		hce_db_materials_table();
	}
} // update custom tables in DB

// populate emissions table
function hce_db_emissions_table_populate() {

	global $wpdb;

	$emissions_data_current_ver = get_option( "hce_emissions_data_version" );
	// data file
	$filename = HCE_BLOGTHEME. "/data/opendap.csv"; // relative path to data filename
	$line_length = "4096"; // max line lengh (increase in case you have longer lines than 1024 characters)
	$delimiter = ","; // field delimiter character
	$enclosure = '"'; // field enclosure character
	
	// open the data file
	$fp = fopen($filename,'r');

	if ( $fp !== FALSE ) { // if the file exists and is readable
	
		$table = $wpdb->prefix . "hce_emissions"; 
		$format = array(
			//'%d',
			'%s',
			'%s',
			'%s',
			'%s'
		);

		$line = 0;
		while ( ($fp_csv = fgetcsv($fp,$line_length,$delimiter,$enclosure)) !== FALSE ) { // begin main loop
			if ( $line == 0 ) { // check version
				$emissions_data_new_ver = $fp_csv[0];
				if ( $emissions_data_current_ver == $emissions_data_new_ver ) { return; /* stop: current version is up to date */ }
				else {  /* empty table */ $wpdb->query( "TRUNCATE TABLE `$table`" ); }
			} elseif ( $line == 1 ) { /* csv file headers */ }

			else {
				// preparing data to insert
				$opendap_code = $fp_csv[2];
				$emission_factor = round($fp_csv[3],5);
				$data = array(
					//'id' => is autoincrement
					'opendap_code' => $opendap_code,
					'type' => $fp_csv[0],
					'subtype' => $fp_csv[1],
					'emission_factor' => $emission_factor
				);
				/* create row */ $wpdb->insert( $table, $data, $format );

			} // end if not line 0
			$line++;

		} // end main loop
		fclose($fp);
		update_option( 'hce_emissions_data_version', $emissions_data_new_ver );

	} else { // if data file do not exist
		echo "<h2>Error</h2>
			<p>File with contents not found or not accesible.</p>
			<p>Check the path: " .$csv_filename. ". Maybe it has to be absolute...</p>";
	} // end if file exist and is readable

} // end populate emissions table

// populate materials table
function hce_db_materials_table_populate() {

	global $wpdb;

	$materials_data_current_ver = get_option( "hce_materials_data_version" );
	// data file
	$filename = HCE_BLOGTHEME. "/data/materiales.simples.csv"; // relative path to data filename
	$line_length = "4096"; // max line lengh (increase in case you have longer lines than 1024 characters)
	$delimiter = ","; // field delimiter character
	$enclosure = '"'; // field enclosure character
	
	// open the data file
	$fp = fopen($filename,'r');

	if ( $fp !== FALSE ) { // if the file exists and is readable
	
		$table = $wpdb->prefix . "hce_materials";
		$format = array(
			//'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'
		);

		$line = 0;
		while ( ($fp_csv = fgetcsv($fp,$line_length,$delimiter,$enclosure)) !== FALSE ) { // begin main loop
			if ( $line == 0 ) { // check version
				$materials_data_new_ver = $fp_csv[0];
				if ( $materials_data_current_ver == $materials_data_new_ver ) { return; /* stop: current version is up to date */ }
				else { /* empty table */ $wpdb->query( "TRUNCATE TABLE `$table`" ); }

			} elseif ( $line == 1 ) { /* csv file headers */ }

			else {
				// preparing data to insert
				$material_code = $fp_csv[0];
				$material_mass = round($fp_csv[9],5);
				$component1_mass = round($fp_csv[6],5);
				$component2_mass = round($fp_csv[7],5);
				$component3_mass = round($fp_csv[8],5);
				$dap_factor = round($fp_csv[10],5);
				$data = array(
					//'id' => is autoincrement
					'material_code' => $material_code,
					'material_name' => $fp_csv[2],
					'material_unit' => $fp_csv[1],
					'material_mass' => $material_mass,
					'component_1' => $fp_csv[3],
					'component_1_mass' => $component1_mass,
					'component_2' => $fp_csv[4],
					'component_2_mass' => $component2_mass,
					'component_3' => $fp_csv[5],
					'component_3_mass' => $component3_mass,
					'dap_factor' => $dap_factor
				);
				/* create row */ $wpdb->insert( $table, $data, $format );

			} // end if not line 0
			$line++;

		} // end main loop
		fclose($fp);
		update_option( 'hce_materials_data_version', $materials_data_new_ver );

	} else { // if data file do not exist
		echo "<h2>Error</h2>
			<p>File with contents not found or not accesible.</p>
			<p>Check the path: " .$csv_filename. ". Maybe it has to be absolute...</p>";
	} // end if file exist and is readable

} // end populate emissions table

// filter loops
function hce_filter_loops( $query ) {
//	false == $query->query_vars['suppress_filters'] 
	if ( !is_admin() && is_author() && $query->is_main_query() ) {
		$query->set('post_type',array('project')); 
		global $current_user;
		get_currentuserinfo();
		if ( is_user_logged_in() && $current_user->user_login == $query->query_vars['author_name'] ) {
			$query->set( 'post_status','any' );
		} else { $query->set( 'post_status','publish' ); }

	}
	return $query;

} // end filter loops

// change visibility of a project: public to private or viceversa
function hce_project_visibility_switcher() {
	global $post;
	if ( is_user_logged_in() && get_current_user_id() == $post->post_author ) {
		$location = get_permalink();
		$action = $location;
		if ( array_key_exists('visibility',$_GET) ) {
			$status = sanitize_text_field($_GET['visibility']);
			$args = array(
				'ID' => $post->ID,
				'post_status' => $status,
			);
			// update project
			$updated_id = wp_update_post($args);
			$location .= "?feedback=visibility_updated";
			wp_redirect($location);
			exit;
		}
		if ( array_key_exists('feedback',$_GET) ) {
			$feedback = sanitize_text_field($_GET['feedback']);
			if ( $feedback == 'visibility_updated') { $feedback_message = "La visibilidad del proyecto se ha actualizado correctamente."; }
			$feedback_out = "<div class='alert alert-success' role='alert'>".$feedback_message."</div>
			";
		} else { $feedback_out = ""; }

		if ( $post->post_status == 'publish' ) {
			$action .= "?visibility=private";
			$current_status = "público";
			$current_class = "success";
			$change_status = "privado";
		} else {
			$action .= "?visibility=publish";
			$current_status = "privado";
			$current_class = "danger";
			$change_status = "público";
		}
		$visibility_switcher = $feedback_out."
		<ul id='dossier-visibility' class='list-unstyled dossier-group'>
			<li><strong>Visibilidad</strong><br /> <span class='btn btn-".$current_class." btn-xs' disabled='disabled'>Proyecto ".$current_status."</span></li>
			<li><a class='btn btn-default btn-xs' href='".$action."'>Hacer proyecto ".$change_status."</a></li>
		</ul>
		";
	
	} else { $visibility_switcher = ""; }
	return $visibility_switcher;

} // end change visibility of a project

// display project basic data in dossier
function hce_project_display_basic_data($project_id) {
	global $post;
	$cfield_prefix = '_hce_project_';
	$cfields_basic = array("address","city","state","cp","use","built-area","useful-area","adjusted-area","users","budget","energy-label","energy-consumption","co2-emission");
	foreach ( $cfields_basic as $field ) {
		$value[$field] = get_post_meta($project_id,$cfield_prefix.$field,TRUE);
	}
	$value['desc'] = get_the_content();
	$basic_fields = array(
		array(
			'label' => 'Localización',
			'unit' => '',
			'group' => 1,
			'value' => $value['address']. ", " .$value['city']. ". " .$value['cp']. " " .$value['state']
		),
		array(
			'label' => 'Uso',
			'unit' => '',
			'group' => 2,
			'value' => $value['use']
		),
		array(
			'label' => 'Superficie construida',
			'unit' => 'm2',
			'group' => 3,
			'value' => $value['built-area']
		),
		array(
			'label' => 'Superficie útil',
			'unit' => 'm2',
			'group' => 3,
			'value' => $value['useful-area']
		),
		array(
			'label' => 'Superficie computable',
			'unit' => 'm2',
			'group' => 3,
			'value' => $value['adjusted-area']
		),
		array(
			'label' => 'Número de usuarios',
			'unit' => '',
			'group' => 2,
			'value' => $value['users']
		),
		array(
			'label' => 'Presupuesto',
			'unit' => '€',
			'group' => 2,
			'value' => $value['budget']
		),
		array(
			'label' => 'Calificación energética',
			'unit' => '',
			'group' => 4,
			'value' => $value['energy-label']
		),
		array(
			'label' => 'Consumo energético anual',
			'unit' => 'kWh/m2 año',
			'group' => 4,
			'value' => $value['energy-consumption']
		),
		array(
			'label' => 'Emisión anual de CO2',
			'unit' => 'Kg CO2/m2 año',
			'group' => 4,
			'value' => $value['co2-emission']
		),
		array(
			'label' => 'Descripción',
			'unit' => '',
			'group' => 1,
			'value' => $value['desc']
		)
	);
	$basic_fields_cols = array();
	foreach ( $basic_fields as $field ) {
		if ( !array_key_exists($field['group'],$basic_fields_cols) ) {
			$basic_fields_cols[$field['group']] = "<dl class='col-sm-3 dossier-group'>";
		} $basic_fields_cols[$field['group']] .= "<dt>".$field['label']."</dt><dd>".$field['value']." ".$field['unit']."</dd>";
	}
	$basic_fields_out = "<div class='row'>";
	foreach ( $basic_fields_cols as $col ) {
		$basic_fields_out .= $col."</dl>";
	}
	$basic_fields_out .= "</div>";

	return $basic_fields_out;

} // end display project basic data

// create theme custom content
function hce_create_custom_content() {

	$custom_contents = array(
		array(
			'title' => 'Evaluar un proyecto',
			'slug' => 'calculo-huella-carbono',
			'content' => '',
			'template' => 'page-hce-form.php',
		),
	);
	foreach ( $custom_contents as $cc ) {
		$page_exists = get_page_by_path($cc['slug'],'ARRAY_N');
		if ( !is_array($page_exists) ) {
			// insert contents
			$page_id = wp_insert_post(array(
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_title' => $cc['title'],
				'post_name' => $cc['slug'],
				'page_template' => $cc['template']
			));

		} // end if this content doesn't exist
	} // end foreach contents array

} // END create theme custom content

// custom configuration option
function hce_custom_configuration() {
	update_option('can_user_register', 1);
	update_option('default_role', 'suscriber');
} // END custom configuration options
?>
