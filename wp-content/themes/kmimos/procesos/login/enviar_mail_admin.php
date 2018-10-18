<pre><?php
	include("../../../../../wp-load.php");
	global $wpdb;

	extract($_POST);

	$content = '';

	$ID = $wpdb->get_var("SELECT ID FROM wp_users WHERE user_email = '".$email."'");

	$userdata = get_user_meta($ID);
	$mascotas = kmimos_get_my_pets($ID);

print_r($ID);

	// Datos del Cliente
	$content = '<h2>Datos del Cliente</h2>';
	$content .= '<div>Nombre: '. $userdata['first_name'][0] .'</div>';
	$content .= '<div>Apellido: '. $userdata['last_name'][0] . '</div>';
	$content .= '<div>Correo: '. $email .'</div>';
	$content .= '<div>Telefono: '. $userdata['user_phone'][0].' / '.$userdata['user_mobile'][0] .'</div>';
	$content .= '<div>Genero: '. $userdata['user_gender'][0] .'</div>';
	$content .= '<div>Edad: '. $userdata['user_age'][0] .'</div>';
	$content .= '<div>Donde nos conocio: '. $userdata['user_referred'][0] .'</div>';

	$content .= '<h2>Datos de la Mascota</h2>';
	if( !empty($mascotas) ){
		foreach($mascotas as $pet){
	            $pet_detail = get_post_meta($pet->ID);
	            $current_pet = kmimos_get_pet_info($pet->ID);

	            $photo_pet = (!empty($current_pet['photo']))? "/".$current_pet['photo']: "/wp-content/themes/kmimos/images/noimg.png";

			    $current_pet['type'] = explode(",", $current_pet['type']);
			    $current_pet['type'] = $current_pet['type'][0];

			    $tipos = kmimos_get_types_of_pets();
			    $tipos_str = "";
			    foreach ( $tipos as $tipo ) {
			        if($tipo->ID == $current_pet['type']){ 
			        	$tipos_str = $tipo->name;
			        }
			    }

			    global $wpdb;
			    $razas = $wpdb->get_results("SELECT * FROM razas ORDER BY nombre ASC");
			    foreach ($razas as $value) {
			        if($value->id == $current_pet['breed']) $razas_nombre = $value->nombre;
			    }
			    $razas_str_gatos = "<option value=1>Gato</option>";

			    $generos = kmimos_get_genders_of_pets();
			    $generos_str = "";
			    foreach ( $generos as $genero ) {
			        if($genero['ID'] == $current_pet['gender']) $generos_str = $genero['singular'];
			    }

			    
			    $tamanos = kmimos_get_sizes_of_pets();
			    $tamanos_str = "";
			    foreach ( $tamanos as $tamano ) {
			        if( $current_pet['type'] != 2608 ){ 
			        	if($tamano['ID'] == $current_pet['size']) { 
			        		$tamanos_str = esc_html( $tamano['name'].' ('.$tamano['desc'].')' ); 
			        	}
			        }
			    }

			    $si_no = array('no','si');
			    $esterilizado_str = "";
			    for ( $i=0; $i<2; $i++ ) {
			        if( isset($current_pet['strerilized']) ){
			            if($i == (int)$current_pet['strerilized']) $esterilizado_str = strtoupper($si_no[$i]);
			        }else{
			            if($i == (int)$current_pet['sterilized']) $esterilizado_str = strtoupper($si_no[$i]);
			        }
			    }

			    $sociable_str = "";
			    for ( $i=0; $i<count($si_no); $i++ ) {
			        if($i == (int)$current_pet['sociable']) $sociable_str = strtoupper($si_no[$i]);
			    }

			    $aggresive_humans_str = "";
			    for ( $i=0; $i<count($si_no); $i++ ) {
			        if($i == (int)$current_pet['aggresive_humans']) $aggresive_humans_str = strtoupper($si_no[$i]);
			    }

			    $aggresive_pets_str = "";
			    for ( $i=0; $i<count($si_no); $i++ ) {
			        if($i == (int)$current_pet['aggresive_pets']) $aggresive_pets_str = strtoupper($si_no[$i]);
			    }

			    $razas = $razas_str;
			    if( $current_pet['type'] == 2608 ){
			        $razas = $razas_str_gatos;
			    }

			    $_comportamiento_gatos = get_post_meta($pet_id, 'comportamiento_gatos', true);
			    $_comportamiento_gatos = (array) json_decode($_comportamiento_gatos);

			    $comportamientos_db = $wpdb->get_results("SELECT * FROM comportamientos_mascotas");
			    foreach ($comportamientos_db as $value) {
			        $comportamientos[ $value->slug ] =  $value->nombre;
			    }

			    $comportamientos_str = "";
			    foreach ($comportamientos as $key => $value) {
			        if($_comportamiento_gatos[ $key ] == 1){ $comportamientos_str = $comportamientos[$key]; }
			    }

			    $ocultar_tamanios = '';
			    if( $current_pet['type'] == 2608 ){
			        $ocultar_tamanios = ' style="display: none;" ';
			    }

			    
			    $content .= '<h2>Nombre de la Mascota: '.$current_pet['name'].'</h2>';
		    	$content .= '<ul>';
			    $content .= '<li><div>Fecha de nacimiento: '.date('d/m/Y', strtotime( str_replace("/", "-", $current_pet['birthdate']))).'</div></li>';
				$content .= '<li><div>Tipo de Mascota: '.$tipos_str.'</div></li>';		    
				$content .= '<li><div>Raza de la Mascota: '.$razas_nombre.'</div></li>';
				$content .= '<li><div>Colores de la Mascota: '.$current_pet['colors'].'</div></li>';
				$content .= '<li><div>Género de la mascota: '.$generos_str.'</div></li>';
				$content .= '<li><div>Tamaño de la Mascota: '.$tamanos_str.'</div></li>';
				$content .= '<li><div>Mascota Esterilizada:'.$esterilizado_str.'</div></li>';
				$content .= '<li><div>Mascota Sociable: '.$sociable_str.'</div></li>';
				$content .= '<li><div>Agresiva con Humanos: '.$aggresive_humans_str.'</div></li>';
				$content .= '<li><div>Agresiva c/otras Mascotas: '.$aggresive_pets_str.'</div></li>';
				$content .= '</ul>';
		}
	}else{
		$content .= '<div>No agreg&oacute; mascota en el registro</div>';
	}
	kmimos_mails_administradores_new( "Registro de Nuevo Cliente", $content );
	//wp_mail( 'italococchini@gmail.com', "Registro de Nuevo Cliente", $content);