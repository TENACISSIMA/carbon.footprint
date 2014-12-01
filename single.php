<?php get_header();

if ( have_posts() ) { while ( have_posts() ) : the_post();
	$project_id = $post->ID;

	// visibility switcher
	$visibility_switcher_out = hce_project_visibility_switcher();

	// edit project link
	if ( is_user_logged_in() && get_current_user_id() == $post->post_author ) {
		$edit_link_out = "<p><a class='btn btn-default btn-xs' href='/calculo-huella-carbono/?step=1&project_id=".$post->ID."'>Editar proyecto</a></p>";
	} else { $edit_link_out = ""; }

	// get project basic data
	$basic_fields_out = hce_project_display_basic_data($project_id);

	// calculate emissions
	$table_p = $wpdb->prefix . "hce_project_" .$project_id;
	$sql_query = "
	SELECT
	  material_name,
	  section_name,
	  emission,
	  emission_transport
	FROM $table_p
	WHERE emission != 0
	  OR emission_transport != 0
	";
	$query_results = $wpdb->get_results( $sql_query , ARRAY_A );
//echo "<pre>";
//print_r($query_results);
//echo "</pre>";
	$emissions = array();
	$emissions_total = 0;
	$e_max = 0;
	foreach ( $query_results as $material ) {
		if ( !array_key_exists($material['section_name'],$emissions) ) {
			$emissions[$material['section_name']]['emission'] = $material['emission'];
			$emissions[$material['section_name']]['emission_transport'] = $material['emission_transport'];
		} else {
			$emissions[$material['section_name']]['emission'] += $material['emission'];
			$emissions[$material['section_name']]['emission_transport'] += $material['emission_transport'];
		}
		// search for highest emission
		$max_candidate = $emissions[$material['section_name']]['emission'] + $emissions[$material['section_name']]['emission_transport'];
		if ( $e_max <= $max_candidate ) { $e_max = $max_candidate; }
		// total emission
		$emissions_total += $material['emission'] + $material['emission_transport'];
	}
//echo "<pre>";
//print_r($emissions);
//echo "</pre>";
//echo $emissions_total;
	$emission_per_section_out = "
	<div class='dossier-table-header row'>
		<div class='col-sm-3 col-sm-offset-2'><small>CAPÍTULO</small><div class='pull-right'><small>kg CO2 eq</small></div></div>
		<div class='col-sm-7'><span class='btn btn-info btn-xs' disabled='disabled'>Transporte</span> <span class='btn btn-primary btn-xs' disabled='disabled'>Intrínsecas</span></div>
	</div>
	";
//print_r($e_max);
	$e_max = $e_max + $e_max * 0.05;
	$e_min = $e_max/100;
	foreach ( $emissions as $section => $e ) {
		$intrinsic = round( $e['emission'], 1 );
		$transport = round( $e['emission_transport'], 1 );
		$total = $intrinsic+$transport;
		if ( $intrinsic <= $e_min && $intrinsic != 0 ) { $intrinsic_relative = 5; }
		else { $intrinsic_relative = round( $intrinsic * 100 / $e_max ); }
		if ( $transport <= $e_min && $transport != 0 ) { $transport_relative = 10; }
		else { $transport_relative = round( $transport * 100 / $e_max ); }
		$emission_per_section_out .= "
		<div class='row'>
			<div class='col-sm-2 col-sm-offset-2'><small>".$section."</small></div>
			<div class='col-sm-1'><div class='pull-right'><small>".$total."</small></div></div>
			<div class='col-sm-7'>
				<div class='progress'>
					<div class='progress-bar progress-bar-info' style='width: ".$transport_relative."%;'>
					".$transport."
					</div>
					<div class='progress-bar' style='width: ".$intrinsic_relative."%;'>
					".$intrinsic."
					</div>
				</div>
			</div>
		</div>
		";
	}

	// emissions circles
	$cfield_prefix = '_hce_project_';
	$emissions_total = round($emissions_total,1);
	$users = get_post_meta($project_id,$cfield_prefix."users",TRUE);
	$built_area = get_post_meta($project_id,$cfield_prefix."built-area",TRUE);
	$budget = get_post_meta($project_id,$cfield_prefix."budget",TRUE);
	$weight = get_post_meta($project_id,$cfield_prefix."mass_total",TRUE);
	$e_per_user = round($emissions_total/$users);
	$e_per_m2 = round($emissions_total/$built_area);
	$e_per_e = round($emissions_total/$budget);
	$e_per_kg = round($emissions_total/$weight);
	$circles[$e_per_user] = array("users","kg CO<sub>2</sub> eq emitidos por usuario. <strong><nobr>Usuarios: ".$users."</nobr></strong>");
	$circles[$e_per_m2] = array("built-area","kg CO<sub>2</sub> eq emitidos por m2 de superficie construida. <strong><nobr>Superficies construida: ".$built_area." m<sub>2</sub></nobr></strong>");
	$circles[$e_per_e] = array("budget","kg CO<sub>2</sub> eq emitidos por cada euro gastado. <strong><nobr>Presupuesto: ".$budget." €</nobr></strong>");
	$circles[$e_per_kg] = array("weight","kg CO<sub>2</sub> eq emitidos por cada kilogramo de edificio. <strong><nobr>Peso: ".$weight." kg</nobr></strong>");
	krsort($circles);
	$circles_out = array();
	$c_count = 0;
	foreach ( $circles as $e => $texts ) {
		$c_count++;
		if ( $c_count == 1 ) { $c_max = $e; }
		$c_relative = round( $e * 100 / $c_max );
		if ( $c_relative <= 5 ) { $c_relative = 5; }
		$c_margin = ( 100 - $c_relative ) / 2;
		if ( $c_relative == 100 ) { $c_styles = "";  $c_label_styles = " style='font-size: 3em;'"; }
		elseif ( $c_relative <= 25 ) { $c_styles = " text-indent: 110%; margin-top: ".$c_margin."%;"; $c_label_styles = " style='color: #000;'"; }
		else { $c_styles = " margin-top: ".$c_margin."%;"; $c_label_styles = " style='font-size: 1.5em;'"; }
		$circles_out[$texts[0]] = "
			<div class='dossier-circle' style='width: ".$c_relative."%;".$c_styles."'>
				<div class='dossier-circle-label bg-primary'$c_label_styles>".$e."</div>
			</div>
		";
		$circles_footer_out[$texts[0]] = "<div class='dossier-circle-text'><small>".$texts[1]."</small></div>";
	}
	$circles_row1 = "<div class='row'>";
	$circles_row2 = "<div class='row'>";
	foreach ( array('users','weight','budget','built-area') as $circle ) {
		$circles_row1 .= "<div class='col-sm-3'>".$circles_out[$circle]."</div>";
		$circles_row2 .= "<div class='col-sm-3'>".$circles_footer_out[$circle]."</div>";
	}
	$circles_row1 .= "</div>";
	$circles_row2 .= "</div>";

?>

<header class="row" role="banner">
	<h1 class="col-sm-12 page-header"><?php the_title(); ?> <small>Memoria de Cálculo de Huella de Carbono</small></h1>
</header>

<main role="main">
<div class="row">
	<div id="dossier-meta" class="col-sm-2">
		<?php echo $edit_link_out.$visibility_switcher_out; ?>
	</div>
	<section id="dossier-data" class="col-sm-10">
		<header><h2 class="dossier-section-header">Datos del proyecto</h2></header>
		<?php echo $basic_fields_out ?>
	</section>
</div>
<div class="row">
	<section id="dossier-emission" class="col-sm-12">
		<header class="row"><h2 class="col-sm-10 col-sm-offset-2">Emisiones <small>Total: <strong class="bg-info"><?php echo $emissions_total; ?></strong> kg de CO<sub>2</sub> equivalente</small></h2></header>
		<div class="row">
			<div class="col-sm-10 col-sm-offset-2"><?php echo $circles_row1.$circles_row2; ?></div>
		</div>
		<?php echo $emission_per_section_out ?>
	</section>
</div>
</main>

<?php endwhile;
} // end if have_posts
get_footer(); ?>
