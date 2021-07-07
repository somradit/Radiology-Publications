<?php
/**
* Plugin Name: Radiology Publications
* Description: This plugin adds the publication content type, rebuilt to use Semantic Scholar instead of Scopus
* Updated: 7/2/2021
* Author: Zachary Eagle
*/


if ( ! function_exists('publications') ) {

// Register Custom Post Type
function publications() {

	$labels = array(
		'name'                => _x( 'Publications', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Publication', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Publications', 'text_domain' ),
		'name_admin_bar'      => __( 'Publication', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
		'all_items'           => __( 'All Items', 'text_domain' ),
		'add_new_item'        => __( 'Add New Publication', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'new_item'            => __( 'New Publication', 'text_domain' ),
		'edit_item'           => __( 'Edit Publication', 'text_domain' ),
		'update_item'         => __( 'Update Publication', 'text_domain' ),
		'view_item'           => __( 'View Publication', 'text_domain' ),
		'search_items'        => __( 'Search Publication', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'Publications', 'text_domain' ),
		'description'         => __( 'Publications', 'text_domain' ),
		'labels'              => $labels,
		'menu_icon'	      => 'dashicons-megaphone',
		'supports'            => array( 'title', 'editor', 'author', 'thumbnail' ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => false,		
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'post',
		'capabilities' => array(
		  'edit_post'          => 'edit_publication', 
		  'read_post'          => 'read_publication', 
		  'delete_post'        => 'delete_publication', 
		  'edit_posts'         => 'edit_publication', 
		  'edit_others_posts'  => 'edit_others_publication', 
		  'publish_posts'      => 'publish_publication',       
		  'read_private_posts' => 'read_private_publication', 
		  'create_posts'       => 'edit_publication', 
		),
		'rewrite' => array('slug' => 'publications', 'with_front' => false),
	);
	register_post_type( 'publications', $args );
	#flush_rewrite_rules();

}
add_action( 'init', 'publications', 0 );

}

/** Adding Admin Page */
add_action( 'admin_menu', 'radiology_publications_menu' );

function radiology_publications_menu() {
	add_options_page( 'Radiology Publications Options', 'Radiology Publications', 'manage_options', 'radiology-publications-options', 'radiology_publications_options' );
}

function radiology_publications_options() {
	wp_enqueue_script( 'Moment', plugins_url( '/moment.js', __FILE__ ), array('jquery'), '1.0', true );
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<button type="button" id="target2">';
	echo 'Update Publications';
	echo '</button><br>';
	echo 'Updating: <p id="status">Click Update Publications to begin</p>';
	$faculty21212 = array();
	$test = array();
	$ii = 0;
	$args = array(
	'numberposts'	=> -1,
	'nopaging' => true,
	'post_type'	=> 'person',

	'meta_key'	=> 'last_name',
	'orderby'	=> 'meta_value',
	'order'          => 'ASC',  
	);

	// query
	$the_query = new WP_Query( $args );
	$the_query;
		?>
		<script type="text/javascript">
		// pass PHP variable declared above to JavaScript variable
		var facultyIDs = [];
		</script>
		<?php
	while( $the_query->have_posts() ) : $the_query->the_post();
		$faculty[] = get_field('semantic_scholar_author_id');
	endwhile;
	$facultyValues = array_values($faculty);
	$facultyValuesEncoded = json_encode($facultyValues);
		?>
		<script type="text/javascript">
		// pass PHP variable declared above to JavaScript variable
		var facultyIDs2 = [];
		var facultyIDs2 = <?php echo $facultyValuesEncoded ?>;
		</script>
		<?php
	wp_reset_query();
}

add_action( 'admin_footer', 'create_radiology_publication' ); // Write our JS below here

function create_radiology_publication() { ?>
	<script type="text/javascript" >

	
	jQuery(document).ready( function($){
		var i = 1;
		function get_paper_details(author, paperid){
		jQuery.getJSON("https://api.semanticscholar.org/v1/paper/"+paperid,function( paperdetail ) {
			console.log(paperdetail);
			console.log(paperdetail.authors.length);
			authorList = new Array;
			jQuery.each(paperdetail.authors, function(index, authors){
					authorList.push(authors.name);
			});
			if(paperdetail.authors.length > 3){
				shortauthorlist = authorList.slice(0,3);
				shortauthorlist.push(" et al.");
				authorList = shortauthorlist;
			}
			authorNamesString = authorList.join(', ');
			console.log(authorNamesString);
			
			jQuery.ajax({
				method: "POST",
				url: ajaxurl,
				async: false,
				data: { 'action': 'dobsondev_ajax_tester_approal_action', 'title': paperdetail.title, 'abstract': paperdetail.abstract, 'paperId': paperdetail.paperId, 'author': author, 'source': paperdetail.venue.toUpperCase(), 'date': paperdetail.year, 'authorNamesString': authorNamesString}
			})
			.done(function( data ) {
				console.log('Successful AJAX Call! /// Return Data: ' + data);
				data = JSON.parse( data );
			})
			.fail(function( data ) {
				console.log('Failed AJAX Call :( /// Return Data: ' + data);
			});
			
		});
	}
		$( "#target2" ).click(function() {
			
			facultyIDs2.forEach(function(author){
				if (!(author == null || author == 'null')){
				console.log(author);
				
				jQuery.getJSON("https://api.semanticscholar.org/v1/author/"+author,function( data2 ) {
					var pubs = data2["papers"];
					console.log(pubs);
					jQuery.each(pubs, function(index, pub){
						
							console.log(pub.paperId);
						if(pub.year == '2021'  || pub.year == '2020'){
							setTimeout(function() { get_paper_details(author, pub.paperId) }, i*5000);
							i++;
						}
						
						});
						
					});
				};
				
				});
		setTimeout(function() {console.log("done.")}, i*5000)
		});
	});
					
	</script> <?php
}
function dobsondev_ajax_tester_ajax_handler() {
	$args = array(
		'post_type'	=> 'publications',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'relation' => 'AND',
				array(
				   'key' => 'paperid',
				   'value' => $_POST['paperId'],
				   'compare' => '=',
				),
				array(
				   'key' => 'semantic_scholar_author_id',
				   'value' => $_POST['author'],
				   'compare' => '=',
				),
			),
	   )
	);
	$articleQuery = new WP_Query($args);
	$articleQuery;
	if (!($articleQuery->have_posts())){
		// Create post object
		$my_post = array(
		  'post_title'    => wp_strip_all_tags( $_POST['title'] ),
		  'post_content'  => $_POST['abstract'],
		  'post_status'   => 'publish',
		  'post_author'   => 1,
		  'post_type'	  => 'publications',
		);

		$post_id = wp_insert_post($my_post);

		update_post_meta($post_id, 'paperid', $_POST['paperId']);
		
		//wp_query to find wp person page with the corresponding author ID
		$authorArgs = array(
			'post_type'	=> 'person',
			'meta_query' => array(
			   array(
				   'key' => 'semantic_scholar_author_id',
				   'value' => $_POST[author],
				   'compare' => 'LIKE',
			   )
		   )
		);
		$authorQuery = new WP_Query($authorArgs);
		$authorQuery;
		$page = $authorQuery->posts[0];
		//Add the person page to the uwauthors field. This is relationship field that will link the paper to the author.
		update_post_meta($post_id, 'uwauthors', $page->ID);
		update_post_meta($post_id, 'paperId', $_POST['paperId']);
		update_post_meta($post_id, 'post_content', $_POST['abstract']);
		update_post_meta($post_id, 'publication_date', $_POST['date']);
		update_post_meta($post_id, 'source', $_POST['source']);
		update_post_meta($post_id, 'authors', $_POST['authorNamesString']);
		update_post_meta($post_id, 'semantic_scholar_author_id', $_POST[author]);
	}

	
}
add_action( 'wp_ajax_dobsondev_ajax_tester_approal_action', 'dobsondev_ajax_tester_ajax_handler' );

function list_rad_publications_person( $atts ){
	$a = shortcode_atts( array(
		'person_id' => '',
		'section' => '',
		), $atts );

	
	$args = array(
	'posts_per_page'	=> 10,
	'post_type'	=> 'publications',
	'meta_query' =>array(
		'relation' => 'AND',
		array(
			'key' => 'uwauthors',
			'value' => $a['person_id'],
			'compare' => 'IN',
	   ),
	   	array(
			'key' => 'publication_date',
			'compare' => 'EXISTS',
	   )
	   ),
	'meta_key' => 'publication_date',
	'orderby' => 'meta_value_num',
	'order' => 'DESC'

	);
	$the_query = new WP_Query( $args );
	$the_query;
	
	
	if( $the_query->have_posts() ):
	$out .= "<h3 style='margin-bottom:0px'>Recent Publications (via Semantic Scholar)</h3>";
	$out .= "<a href='https://www.semanticscholar.org/author/".get_field('semantic_scholar_author_id')."'>See full author profile on Semantic Scholar</a></br></br>";
		while( $the_query->have_posts() ) : $the_query->the_post();
			$out .= '<a target="_blank" href="https://semanticscholar.org/paper/'. get_field('paperid') .'">'.get_the_title()."</a>";
			$out .= "<br>";
			$out .= get_field( 'authors' );
			if (get_field( 'publication_date' )){
				$out .= " - ";
				$out .= "Published " . get_field( 'publication_date' );
			}
			if (get_field( 'source' )){
				$out .= " - ";
				$out .= get_field( 'source' );
			}
			$out .= "<br/><br/>";
		endwhile;
		
		echo $out;
	endif;
	wp_reset_query();
};
add_shortcode( 'list-rad-publications-person', 'list_rad_publications_person');

//Section Publications Shortcode
function get_radiology_section_publications($atts){
	$sc = shortcode_atts( array(
	'section' => '',
	), $atts );
	
	//Get all of the faculty in the section
      $section_faculty = array();
	  $my_query = new WP_Query( array(
           'post_type' => 'person',
           'posts_per_page' => 20,
			'meta_query'	=> 	array(
		'relation' => 'AND',
			array(
				'key'		=> 'classification',
				'value'		=> 'faculty',
				'compare'	=> 'LIKE'
			),
			array(
                'key' => 'section',
                'value' => $sc['section'],
                'compare' => 'LIKE',
            ))
      ));
	//Add each faculty person id to an array so that we can filter papers by faculty in the specified section
	while( $my_query->have_posts() ) : $my_query->the_post();
			$section_faculty[] = get_the_ID();
	endwhile;
	//get the 20 most recent papers by the faculty of the specified section
	$paper_query = new WP_Query( array(
	   'post_type' => 'publications',
	   'posts_per_page' => 20,
		'meta_query'	=> 	array(
		'relation' => 'AND',
		'author_clause' => array(
			'key'		=> 'uwauthors',
			'value'		=> $section_faculty,
			'compare'	=> 'IN'
		),
		'date_clause' => array(
			'key' 		=> 'publication_date',
			'compare'	=> 'EXISTS'
			)),
		'orderby' => array(
			'date_clause' => 'DESC',
		),
      ));
	  	$pubs = "";
		$pubs .= '<h3 style="margin-bottom:0px">Recent '.$sc['section'].' Publications</h3>';
	//Create array to keep track of which papers have been displayed by paper ID, there are duplicate publication items for each faculty that is an author of that paper
	$unique_papers = array();
	while( $paper_query->have_posts() ) : $paper_query->the_post();
	$paperid = get_field('paperid', $the_post->ID);
	//If the paper has not already been added to the display add it now
	if( ! in_array($paperid, $unique_papers)){
			$pubs .= '<a target="_blank" href="https://semanticscholar.org/paper/'. get_field('paperid') .'">'.get_the_title()."</a>";
			$pubs .= "<br>";
			$pubs .= get_field( 'authors' );
			if (get_field( 'publication_date' )){
				$pubs .= " - ";
				$pubs .= "Published " . get_field( 'publication_date' );
			}
			if (get_field( 'source' )){
				$pubs .= " - ";
				$pubs .= get_field( 'source' );
			}
			$pubs .= "<br/><br/>";
				//Add the paper id to the array so that we know this paper has already been displayed and won't be again.
				$unique_papers[] = $paperid;
	};
		
		endwhile;
		$pubs .= $sc['section'];
		$pubs .= $section_faculty[1];
		echo $pubs;
	wp_reset_query();

}		
add_shortcode( 'radiology_section_publications', 'get_radiology_section_publications');