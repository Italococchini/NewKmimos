<?php
	
	include_once('includes/functions/kmimos_functions.php');

	/**
	 * BEGIN Seccion de ayuda Kmimos
 	 */
	function add_secciones_ayuda() {
		register_taxonomy('seccion','faq', array(
				'hierarchical' => true,
				'labels' => array(
				'name' => _x( 'Secciones de Ayuda', 'Secciones de Ayuda' ),
				'singular_name' => _x( 'Seccion de Ayuda', 'Secciones de Ayuda' ),
				'search_items' =>  __( 'Search Secciones' ),
				'all_items' => __( 'All Secciones' ),
				'parent_item' => __( 'Parent seccion' ),
				'parent_item_colon' => __( 'Parent seccion:' ),
				'edit_item' => __( 'Edit seccion' ),
				'update_item' => __( 'Update seccion' ),
				'add_new_item' => __( 'Add New seccion' ),
				'new_item_name' => __( 'New seccion Name' ),
				'menu_name' => __( 'Secciones' ),
			),
			'rewrite' => array(
				'slug' => 'secciones', 
				'with_front' => true, 
				'hierarchical' => true 
			),
		));
	}
	add_action( 'init', 'add_secciones_ayuda', 0 );
	function create_posts_type() {

		register_post_type( 'faq',
			array(
					'labels' => array(
					'name' => __( 'Ayuda Kmimos' ),
					'singular_name' => __( 'Ayuda Kmimos' )
				),
				'menu_position' => 3,
				'public' => true,
				'has_archive' => false,
				'rewrite' => array('slug' => 'faq'),
				'supports' => array( 'title', 'editor', 'thumbnail', 'seccion' ),
	            'taxonomies' => array( 'seccion' ),
	            'menu_icon' => '',
			)
		);		
	}
	add_action( 'init', 'create_posts_type' );
	/**
	 * END Seccion de ayuda Kmimos
 	 */

	if(!function_exists('italo_include_script')){
	    function italo_include_script(){
	        
	    }
	}

	if(!function_exists('italo_include_admin_script')){
	    function italo_include_admin_script(){
	        include_once('dashboard/assets/config_backpanel.php');
	    }
	}



	if(!function_exists('get_ayuda_categoria')){
		function get_ayuda_categoria( $post_id ){
			$result = '';
			$parents = wp_get_post_terms( $post_id, 'seccion' ); 
			foreach ($parents as $tax) {
				$ignore = [ 'destacados', 'sugeridos' ];
				if( !in_array( $tax->slug, $ignore ) ){
					$result = $tax->slug;
				}
			}
			return $result;
		}
	}

	/* Temas Sugeridos */
	if(!function_exists('get_ayuda_sugeridos')){
		function get_ayuda_sugeridos( $parent='sugeridos', $ID = 0, $echo = true ){

			$HTML= '';
			$sugeridos = get_posts(
			    array(
					'post_status' => 'publish', 
					'posts_per_page' => -1, 
			        'post_type' => 'faq',
			        'tax_query' => array(
				        array(
				            'taxonomy' => 'seccion',
				            'field'    => 'slug',
				            'terms'    => $parent
				        )
				    )
			    )
			);


			if( !empty($sugeridos) ) { 	
				$article = '';
				foreach ($sugeridos as $post) { 
					get_posts( $post->ID );
					if( get_the_ID() != $ID ){
						$article .= '
							<article>
								<a href="'.get_permalink().'">
									<h3>'.get_the_title().'</h3>
								</a>
							</article>
						';
					}
				}
				if( $article != '' ){
					$HTML = '
					<section class="temas-sugeridos">
						<span class="title">Temas sugeridos</span>
						<div class="sugeridos-content text-left">
							<div class="container">
							'.$article.'
							</div>
						</div>
					</section>
					';
				}
			}

			if($echo){
				print_r($HTML);
			}else{
				return $HTML;
			}

		}
	}

	if(!function_exists('validar_perfil_completo')){
	    function validar_perfil_completo(){
	    	global $current_user;
	    	$user_id = $current_user->ID;
	    	if( $user_id > 0 ){	    		
		    	$datos_perfil=[ 
					'user_mobile',
			    	'user_phone'
		    	]; 

		    	foreach( $datos_perfil as $key ){
			    	$value = get_user_meta( $user_id, $key, true );
			    	if( empty($value) ){
			    		echo $key.': '.$value;
						return false;
			    	}
		    	}
				return true;
	    	}
			return false;
	    }
	}

	if(!function_exists('servicios_en_session')){
	    function servicios_en_session( $opt_key = '', $arr, $sub="" ){
	    	$result = false;
	    	if( !empty($arr) ){
	    		if( array_key_exists($sub, $arr) ){
	    			if( in_array($opt_key, $arr[$sub]) ){
	    				$check = true;
	    			}
	    		}
	    	}
	    	return $check;
	    }	
	}

	if(!function_exists('get_user_slug')){
	    function get_user_slug( $cuidador_userID ){
	    	global $wpdb;
	    	if( $cuidador_userID > 0 ){
				$cuidador = $wpdb->get_row("
	                SELECT 
	                    cuidadores.id,
	                    cuidadores.id_post
	                from cuidadores 
	                where cuidadores.user_id = ".$cuidador_userID
	            );
	            $post_id = ( isset( $cuidador->id_post ) )? $cuidador->id_post : 0 ; 
		    	if( $post_id > 0 ){
		    		$user = get_post( $post_id );
		    		if( isset($user->post_name) ){
		    			return get_home_url()."/petsitters/".$user->post_name;
		    		}
		    	}
		    }
	    	return '';
	    }
	}

	if(!function_exists('get_attr_link_conocer_cuidador')){
	    function get_attr_link_conocer_cuidador( $cuidador_name, $post_id ){
	    	global $current_user;
	    	$user_id = $current_user->ID;
	    	$link = ' 
	    		href="#" 
	    		data-name="'.$cuidador_name.'" 
	    		data-id="'.$post_id.'" 
	    		data-target="#popup-conoce-cuidador"
	    	';

			if ( !is_user_logged_in() ){ 
				$link = ' 
					href="#popup-iniciar-sesion"
					data-toggle="modal"
				';
			}else{
				$mascotas = kmimos_get_my_pets($user_id);
				if ( count($mascotas) < 1 ){ 
					$link = ' href="'.get_home_url().'/perfil-usuario/mascotas"';
				}				
			}

			return $link;
	    }
	}
	
	if(!function_exists('add_wlabel')){ 
		function add_wlabel(){
            $wlabel = false;
            $title = '';
            if (!isset($_SESSION)) {
                session_start();
            }
			if(array_key_exists('wlabel',$_SESSION) || $referido=='Volaris' || $referido=='Vintermex'){

                if(array_key_exists('wlabel',$_SESSION)){
                	$title = $_SESSION['wlabel'];
                    $wlabel= true;
                }else if($referido=='Volaris'){
                	$title = 'volaris';
                    $wlabel= true;

                }else if($referido=='Vintermex'){
                	$title = 'vintermex';
                    $wlabel= true;
                }
            }

            if( $wlabel ){
				wp_enqueue_style( 'wlabel_css', getTema()."/css/wlabel-content.css", array(), "1.0.0" );
            }

            return $title;

		}
	}

	if(!function_exists('estados_municipios')){
		function estados_municipios(){
			global $wpdb;
		    $estados_municipios = $wpdb->get_results("
				select 
				  	s.`order` as o_state, 
					l.`order` as o_location,
					s.id as estado_id, 
					s.`name` as estado_name, 
					l.id as municipio_id,
					l.`name` as municipio_name
				from states as s 
					inner join locations as l on l.state_id = s.id
				where  s.country_id = 1
				order by o_state, o_location, estado_name, municipio_name ASC
	    	");
	    	return $estados_municipios;
		}
	}	

	if(!function_exists('get_tipo_servicios')){
	    function get_tipo_servicios(){
	    	return [
		        'hospedaje'      => ['name'=>'Hospedaje'], 
		        'guarderia'      => ['name'=>'Guardería'], 
		        'paseos'         => ['name'=>'Paseos'],
		        'adiestramiento' => ['name'=>'Entrenamiento']
	    	];
	    }
	}

 	if(!function_exists('italo_menus')){
	    function italo_menus($menus){
		 global $current_user;

	    	$menus[] = array(
                'title'=>'Control de Reservas',
                'short-title'=>'Control de Reservas',
                'parent'=>'kmimos',
                'slug'=>'bp_reservas',
                'access'=>'manage_options',
                'page'=>'backpanel_reservas',
                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

	        $menus[] = array(
	                'title'=>'Control Conocer a Cuidador',
	                'short-title'=>'Control Conocer a Cuidador',
	                'parent'=>'kmimos',
	                'slug'=>'bp_conocer_cuidador',
	                'access'=>'manage_options',
	                'page'=>'backpanel_conocer_cuidador',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

	        $menus[] = array(
	                'title'=>'Listado de Suscriptores',
	                'short-title'=>'Listado de Suscriptores',
	                'parent'=>'kmimos',
	                'slug'=>'bp_suscriptores',
	                'access'=>'manage_options',
	                'page'=>'backpanel_subscribe',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

	        $menus[] = array(
	                'title'=>'Listado de Clientes',
	                'short-title'=>'Listado de Clientes',
	                'parent'=>'kmimos',
	                'slug'=>'bp_clientes',
	                'access'=>'manage_options',
	                'page'=>'backpanel_clientes',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

	        $menus[] = array(
	                'title'=>'Cuidadores Detalles',
	                'short-title'=>'Cuidadores Detalles',
	                'parent'=>'kmimos',
	                'slug'=>'bp_cuidadores_detalle',
	                'access'=>'manage_options',
	                'page'=>'backpanel_cuidadores_detalle',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );


	        $menus[] = array(
	                'title'=>'Listado de Cuidadores',
	                'short-title'=>'Listado de Cuidadores',
	                'parent'=>'kmimos',
	                'slug'=>'bp_cuidadores',
	                'access'=>'manage_options',
	                'page'=>'backpanel_cuidadores',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

	        /* $menus[] = array(
	                 'title'=>'Control WhiteLabel',
	                 'short-title'=>'Control WhiteLabel',
	                 'parent'=>'kmimos',
	                 'slug'=>'bp_wlabel',
	                 'access'=>'manage_options',
	                 'page'=>'backpanel_wlabel',
	                 'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );*/

	        $menus[] = array(
	                'title'=>'Club Patitas Felices (Participantes)',
	                'short-title'=>'Club Patitas Felices (Participantes)',
	                'parent'=>'kmimos',
	                'slug'=>'bp_participantes_club_patitas_felices',
	                'access'=>'manage_options',
	                'page'=>'backpanel_ctr_participantes',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

	        $menus[] = array(
	                'title'=>'Control de Referidos (Club Patitas Felices)',
	                'short-title'=>'Control de Referidos Club Patitas Felices',
	                'parent'=>'kmimos',
	                'slug'=>'bp_referidos_club_patitas_felices',
	                'access'=>'manage_options',
	                'page'=>'backpanel_ctr_referidos',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

	        $menus[] = array(
	                'title'=>'Estados por Cuidador',
	                'short-title'=>'Estados por Cuidador',
	                'parent'=>'kmimos',
	                'slug'=>'bp_estados_cuidadores',
	                'access'=>'manage_options',
	                'page'=>'backpanel_estados_cuidadores',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );


	        $menus[] = array(
	                'title'=>'Listado Mascotas',
	                'short-title'=>'Listado Mascotas',
	                'parent'=>'kmimos',
	                'slug'=>'bp_mascotas',
	                'access'=>'manage_options',
	                'page'=>'backpanel_mascotas',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

			$menus[] = array(
				'title'=>'Listado Multinivel',
				'short-title'=>'Listado Multinivel',
				'parent'=>'kmimos',
				'slug'=>'bp_multinivel',
				'access'=>'manage_options',
				'page'=>'backpanel_multinivel',
				'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
			);

        
	        $menus[] = array(
	                'title'=>'Newsletter',
	                'short-title'=>'Newsletter',
	                'parent'=>'kmimos',
	                'slug'=>'bp_newsletter',
	                'access'=>'manage_options',
	                'page'=>'backpanel_newsletter',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );

	        $menus[] = array(
	                'title'=>'Reservas y Cupones',
	                'short-title'=>'Reservas y Cupones',
	                'parent'=>'kmimos',
	                'slug'=>'bp_cupones',
	                'access'=>'manage_options',
	                'page'=>'backpanel_cupones',
	                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
	        );


         /* Temporal ********************* */

          if ( 	 $current_user->user_email == 'a.pedroza@kmimos.la' ||
				 $current_user->user_email == 'r.cuevas@kmimos.la'  ||
				 $current_user->user_email == 'e.celli@kmimos.la' 	|| 
				 $current_user->user_email == 'soporte.kmimos@gmail.com'
		 	){

		        $menus[] = array(
		                'title'=>'Pago Cuidador',
		                'short-title'=>'Pago Cuidador',
		                'parent'=>'kmimos',
		                'slug'=>'bp_saldo_cuidadores',
		                'access'=>'manage_options',
		                'page'=>'backpanel_saldo_cuidador',
		                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
		        );
		        $menus[] = array(
		                'title'=>'Pago Cuidador Inicio Reserva',
		                'short-title'=>'Pago Cuidador Inicio Reserva',
		                'parent'=>'kmimos',
		                'slug'=>'bp_saldo_cuidadores_bookinkstart',
		                'access'=>'manage_options',
		                'page'=>'backpanel_saldo_cuidador_BookingStart',
		                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
		        );
		        $menus[] = array(
		                'title'=>'Pago Cuidador Detalle',
		                'short-title'=>'Pago Cuidador Detalle',
		                'parent'=>'kmimos',
		                'slug'=>'bp_saldo_cuidadores_detalle',
		                'access'=>'manage_options',
		                'page'=>'backpanel_saldo_cuidador_detalle',
		                'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
		        );

      			$menus[] = array(
      				'title'=>'Saldo de Cupones',
      				'short-title'=>'Saldo de Cupones',
      				'parent'=>'kmimos',
      				'slug'=>'bp_saldo_cupon',
      				'access'=>'manage_options',
      				'page'=>'backpanel_saldo_cupon',
      				'icon'=>plugins_url('/assets/images/icon.png', __FILE__)
      			);


		      }
         /* Temporal ********************* */


	        return $menus;

	    }
	}


    if(!function_exists('backpanel_saldo_cuidador_BookingStart')){
            function backpanel_saldo_cuidador_BookingStart(){
                include_once('dashboard/backpanel_saldo_cuidador_BookingStart.php');
            }
    }



	if(!function_exists('backpanel_cupones')){
	        function backpanel_cupones(){
	            include_once('dashboard/backpanel_cupones.php');
	        }
	}

	if(!function_exists('backpanel_saldo_cuidador')){
	        function backpanel_saldo_cuidador(){
	            include_once('dashboard/backpanel_saldo_cuidador.php');
	        }
	}

	if(!function_exists('backpanel_mascotas')){
	        function backpanel_mascotas(){
	            include_once('dashboard/backpanel_mascotas.php');
		}
	}

	if(!function_exists('backpanel_saldo_cuidador_detalle')){
        function backpanel_saldo_cuidador_detalle(){
            include_once('dashboard/backpanel_saldo_cuidador_detalle.php');
        }
    }

	if(!function_exists('backpanel_newsletter')){
        function backpanel_newsletter(){
            include_once('dashboard/backpanel_newsletter.php');
        }
    }

	if(!function_exists('backpanel_ctr_participantes')){
        function backpanel_ctr_participantes(){
            include_once('dashboard/backpanel_ctr_participantes.php');
        }
    }

    if(!function_exists('backpanel_ctr_referidos')){
        function backpanel_ctr_referidos(){
            include_once('dashboard/backpanel_ctr_referidos.php');
        }
    }

    if(!function_exists('backpanel_conocer_cuidador')){
        function backpanel_conocer_cuidador(){
            include_once('dashboard/backpanel_conocer_cuidador.php');
        }
    }

    if(!function_exists('backpanel_reservas')){
        function backpanel_reservas(){
            include_once('dashboard/backpanel_reservas.php');
        }
    }

    if(!function_exists('backpanel_subscribe')){
        function backpanel_subscribe(){
            include_once('dashboard/backpanel_subscribe.php');
        }
    }

    if(!function_exists('backpanel_clientes')){
        function backpanel_clientes(){
            include_once('dashboard/backpanel_clientes.php');
        }
    }

    if(!function_exists('backpanel_cuidadores')){
        function backpanel_cuidadores(){
            include_once('dashboard/backpanel_cuidadores.php');
        }
    }

    if(!function_exists('backpanel_estados_cuidadores')){
        function backpanel_estados_cuidadores(){
            include_once('dashboard/backpanel_estados_cuidadores.php');
        }
    }

	if(!function_exists('backpanel_wlabel')){
        function backpanel_wlabel(){
            include_once('wlabel/admin/backpanel.php');
        }
    }

	if(!function_exists('backpanel_saldo_cupon')){
		function backpanel_saldo_cupon(){
			include_once('dashboard/backpanel_saldo_cupon.php');
		}
	}

	if(!function_exists('backpanel_multinivel')){
		function backpanel_multinivel(){
			include_once('dashboard/backpanel_multinivel.php');
		}
	}

	if(!function_exists('backpanel_cuidadores_detalle')){
	        function backpanel_cuidadores_detalle(){
	            include_once('dashboard/backpanel_cuidadores_detalle.php');
	        }
	}

	
