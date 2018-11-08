jQuery( document ).ready( function(){

	jQuery("#btn_registrar_mascota").on("click", function(){
		jQuery(".popup-registrarte-final").css("display", "none");
		jQuery(".popup-registrarte-datos-mascota").css("display", "block");
	});
});

var mail_admin_registro = false;
var globalData = "";
 
jQuery(document).on("click", '[data-target="#popup-registrarte"]' ,function(e){
	e.preventDefault();

	jQuery('[data-error="auth"]').fadeOut("fast");

	jQuery("#popup-registrarte .modal-content > div").css("display", "none");
	jQuery(".popup-registrarte-1").css("display", 'block');
	jQuery(".popup-registrarte-nuevo-correo").css("display", 'none');
	jQuery(".popup-registrarte-datos-mascota").css('display', 'none');
	jQuery(".popup-registrarte-final").css('display', 'none');
	jQuery(".popup-registrarte-final-0").css('display', 'none');

	jQuery('#km-datos-foto-profile').css('background-image', 'url('+HOME+'/images/popups/registro-cuidador-foto.png)');
	jQuery("#km-datos-foto-profile").css("background-image", 'url('+HOME+'/images/popups/registro-cuidador-foto.png)');

	jQuery('#form_nuevo_cliente')[0].reset();
	jQuery( jQuery(this).data('target') ).modal('show');
});

jQuery("#popup-registrarte-datos-mascota").ready(function(){

    jQuery("#nombre").blur(function(){
		
		if(jQuery("#nombre").val().length < 3){
			jQuery("#nombre").parent('div').css('color','red');
			jQuery("#nombre").after('<span name="sp-nombre">Ingrese su Nombre</span>').css('color','red');
			jQuery("#nombre").focus(function() { jQuery("[name='sp-nombre']").remove(); });
		}else{
			jQuery("#nombre").css('color','green');
			jQuery("#nombre").parent('div').css('color','green');
			jQuery('[name="sp-nombre"]').remove();
		}
	});

	jQuery("#apellido").blur(function(){
		if(jQuery("#apellido").val().length < 3){
			jQuery("#apellido").parent('div').css('color','red');
			jQuery("#apellido").after('<span name="sp-apellido">Ingrese su apellido</span>').css('color','red');
			jQuery("#apellido").focus(function() { jQuery("[name='sp-apellido']").remove(); });
		}else{
			jQuery("#apellido").css('color','green');
			jQuery("#apellido").parent('div').css('color','green');
			jQuery('[name="sp-apellido"]').remove();
		}
		
	});

	jQuery("#ife").blur(function(){
		jQuery('[name="sp-ife"]').remove();
		switch(jQuery("#ife").val().length) {
		case 0:
			jQuery("#ife").parent('div').css('color','red');
			jQuery("#ife").after('<span name="sp-ife">Debe ingresar su IFE</span>').css('color','red');
			jQuery("#ife").focus(function() { jQuery('[name="sp-ife"]').remove(); });
			break;
		case 13:
				jQuery("#ife").css('color','green');
				jQuery("#ife").parent('div').css('color','green');
				jQuery('[name="sp-ife"]').remove();
			break;
		default:
			jQuery("#ife").parent('div').css('color','red');
			jQuery("#ife").after('<span name="sp-ife">Su IFE debe contener 13 dígitos</span>').css('color','red');
			jQuery("#ife").focus(function() { jQuery('[name="sp-ife"]').remove(); });
		}
	});

	jQuery(".verify_mail").blur(function(){
		var verify = jQuery(this).closest('.verify');
		var verify_mail = jQuery(this);
		var verify_result = jQuery(verify).find('.verify_result');
		var verify_data = verify_mail.data('verify');

		if(verify_mail.val().length == 0){
			verify_mail.parent('div').css('color','red');
			verify_mail.after('<span name="sp-email">Ingrese su email</span>').css('color','red');
			verify_mail.focus(function() { jQuery('[name="sp-email"]').remove(); });
			jQuery('.verify_mail').parent().find('.verify_result').html('');

		}else{
			verify_mail.css('color','green');
			verify_mail.parent('div').css('color','green');
			jQuery('[name="sp-email_1"]').remove();
			var email = verify_mail.val();
			var campo = {
				'email': email
			}

			jQuery.ajax({
	        data:  campo,
	        url:   HOME+'/procesos/login/main.php',
	        type:  'post',
	        beforeSend: function () {
				verify_result.html("Procesando, espere por favor...");
				verify_result.css('color','green');
	        },
	        success:  function (response) {
	                if (response == 'SI') {
						verify_result.html("Este E-mail ya esta en uso");
						verify_result.css('color','red');
						verify_mail.parent('div').css('color','red');
						verify_mail.css('color','red');
						verify_mail.removeClass('correctly');

						if(verify_data=='noactive'){
							verify_result.html("E-mail correcto!");
							verify_result.css('color','green');
							verify_mail.addClass('correctly');
						}

	                }else if (response == 'NO'){
						verify_result.html("E-mail disponible!");
						verify_result.css('color','green');
						verify_mail.addClass('correctly');

						if(verify_data=='noactive'){
							verify_result.html("Este E-mail no existe");
							verify_result.css('color','red');
							verify_mail.removeClass('correctly');
						}

	                }else if (response == 'NO_MAIL'){
						verify_result.html("E-mail no es correcto!");
						verify_result.css('color','red');
						verify_mail.parent('div').css('color','red');
						verify_mail.css('color','red');
						verify_mail.removeClass('correctly');
					}
	        }
	    }); 
		}
	});

	jQuery("#pass").blur(function(){
		
		if(jQuery("#pass").val().length == 0){		
			jQuery("#pass").parent('div').css('color','red');
			jQuery("#pass").after('<span name="sp-pass">Ingrese su Contraseña</span>').css('color','red');
			jQuery("#pass").focus(function() { jQuery('[name="sp-pass"]').remove(); });
		}else{
			jQuery("#pass").css('color','green');
			jQuery("#pass").parent('div').css('color','green');
			jQuery('[name="sp-pass"]').remove();
		}
	});

	jQuery("#movil").blur(function(){
		
		switch(jQuery("#movil").val().length) {
			case 0:
				jQuery("#movil").parent('div').css('color','red');
				jQuery("#movil").after('<span name="sp-movil">Debe ingresar su movil</span>').css('color','red');
				jQuery("#movil").focus(function() { jQuery('[name="sp-movil"]').remove(); });
				break;
			case 10:
					jQuery("#movil").css('color','green');
					jQuery("#movil").parent('div').css('color','green');
					jQuery('[name="sp-movil"]').remove();
				break;
			default:
				jQuery("#movil").parent('div').css('color','red');
				jQuery("#movil").after('<span name="sp-movil">Su movil debe contener 10 dígitos</span>').css('color','red');
				jQuery("#movil").focus(function() { jQuery('[name="sp-movil"]').remove(); });
		}
	});


	jQuery("#genero").blur(function(){
		
		if(jQuery("#genero").val().length == 0){		
			jQuery("#genero").parent('div').css('color','red');
			jQuery("#genero").after('<span name="sp-genero">Debe Seleccionar una opcion</span>').css('color','red');
			jQuery("#genero").focus(function() { jQuery('[name="sp-genero"]').remove(); });
		}else{
			jQuery("#genero").css('color','green');
			jQuery("#genero").parent('div').css('color','green');
			jQuery('[name="sp-genero"]').remove();
		}
	});

	jQuery("#edad").blur(function(){
		
		if(jQuery("#edad").val().length == 0){		
			jQuery("#edad").parent('div').css('color','red');
			jQuery("#edad").after('<span name="sp-edad">Debe Seleccionar una opcion</span>').css('color','red');
			jQuery("#edad").focus(function() { jQuery('[name="sp-edad"]').remove(); });
		}else{
			jQuery("#edad").css('color','green');
			jQuery("#edad").parent('div').css('color','green');
			jQuery('[name="sp-edad"]').remove();
		}
	});

	/*	
	jQuery("#fumador").blur(function(){
		
		if(jQuery("#fumador").val().length == 0){		
			jQuery("#fumador").parent('div').css('color','red');
			jQuery("#fumador").after('<span name="sp-fumador">Debe Seleccionar una opcion</span>').css('color','red');
			jQuery("#fumador").focus(function() { jQuery('[name="sp-fumador"]').remove(); });
		}else{
			jQuery("#fumador").css('color','green');
			jQuery("#fumador").parent('div').css('color','green');
			jQuery('[name="sp-fumador"]').remove();
		}
	});*/

	jQuery("#btn_si_acepto").on("click", function(e){
		if( !jQuery( "#btn_si_acepto" ).hasClass("btn_disable") || jQuery( "#btn_si_acepto" ).hasClass("btn_disable_2") ){
			if( !jQuery( "#btn_si_acepto" ).hasClass("btn_disable_2") ){

				jQuery( "#btn_si_acepto" ).addClass("btn_disable");
				jQuery( "#btn_si_acepto" ).addClass("btn_disable_2");

				jQuery(".popup-condiciones").css("display", "none");
				jQuery(".popup-registrarte-final").css("display", "block");

				var nombre = jQuery("#nombre").val();
					apellido = jQuery("#apellido").val(),
					ife = jQuery("#ife").val(),
				 	email = jQuery("#email_1").val(), 
				 	pass = jQuery("#pass").val(), 
				 	movil = jQuery("#movil").val(), 
				 	genero = jQuery("#genero").val(), 
				 	edad = jQuery("#edad").val(), 
				 	fumador = jQuery("#fumador").val(),
					referido = jQuery("#referido").val(),
					img_profile = jQuery("#img_profile").val();

			 	var campos = [nombre,apellido,ife,email,pass,movil,genero,edad,fumador,referido,img_profile];

				var datos = {
					'name': campos[0],
					'lastname': campos[1],
					'idn': campos[2],
					'email': campos[3],
					'password': campos[4],
					'movil': campos[5],
					'gender': campos[6],
					'age': campos[7],
					'smoker': campos[8],
					'referido': campos[9],
					'img_profile': campos[10],
					'social_facebook_id': jQuery('#facebook_cliente_id').val(),
					'social_google_id': jQuery('#google_cliente_id').val()
				};

				jQuery("#popup-registrarte").on('hidden.bs.modal', function () {
		            finalizar_proceso();
			    });

				jQuery.post( HOME+'/procesos/login/registro.php', datos, function( data ) {
					if( data > 0 ){
						globalData = data;
						jQuery("#km-datos-foto").css("background-image", "url("+jQuery("#km-datos-foto").attr("data-init-img")+")" );
						jQuery("#img_pet").val( "" );
						jQuery("body").scrollTop(0);

						evento_google("nuevo_registro_cliente");
						evento_fbq("track", "traking_code_nuevo_registro_cliente"); 

						if( wlabel == "petco" ){
							window.adf&&adf.ClickTrack(this,1453019,'MX_Kmimos_RegistoTYP_180907',{});

							//registro campaing monitor
							jQuery.post( 
			                    "https://www.kmimos.com.mx/campaing/suscribir.php", 
			                    {
			                        "email": datos['email'],
			                        "list": 'petco_registro'
			                    }, 
			                    function( data ) {
			                        console.log( data );
			                        console.log("Suscripción enviadas");
			                    }
			                );
						}
						
					}
					jQuery('.km-btn-popup-registrarte-nuevo-correo').html('SIGUIENTE');
				});

			}

		}else{
			alert("Debe leer los terminos y condiciones primero.");
		}
	});

	jQuery("#btn_no_acepto").on("click", function(e){
		location.reload();
	});

	jQuery( "#popup-registrarte .popup-condiciones .terminos_container" ).scroll(function() {
		if( jQuery( ".popup-condiciones .terminos_container" )[0].scrollHeight <= ( parseInt( jQuery( ".popup-condiciones .terminos_container" ).scrollTop() ) + 700  ) ){
			jQuery( "#btn_si_acepto" ).removeClass("btn_disable");
		}
	});

	jQuery(document).on("click", '.popup-registrarte-nuevo-correo .km-btn-popup-registrarte-nuevo-correo', function ( e ) {
		e.preventDefault();

		jQuery('#siguiente').html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> GUARDANDO');

		var nombre = jQuery("#nombre").val();
			apellido = jQuery("#apellido").val(),
			ife = jQuery("#ife").val(),
		 	email = jQuery("#email_1").val(), 
		 	pass = jQuery("#pass").val(), 
		 	movil = jQuery("#movil").val(), 
		 	genero = jQuery("#genero").val(), 
		 	edad = jQuery("#edad").val(), 
		 	fumador = jQuery("#fumador").val(),
			referido = jQuery("#referido").val(),
			img_profile = jQuery("#img_profile").val();

	 	var campos = [nombre,apellido,ife,email,pass,movil,genero,edad,fumador,referido,img_profile];
	 	
	 	var fields = [
			'nombre',
			'apellido',
			'ife',
			'email_1',
			'pass',
			'movil',
			'genero',
			'edad',
			'fumador',
			'referido'
	 	];
		km_cliente_validar(fields);
		
		jQuery("#km-datos-foto-profile").css('border', "0px solid transparent");

		if (nombre.length > 2 && 
			apellido.length > 2 && 
			ife.length > 2 && 
			email.length > 2 && 
			pass.length > 0 && 
			movil.length > 2 && 
			genero != "" && 
			edad != "" /* && 
			fumador !="" */
			) {

			jQuery("#popup-registrarte .modal-content > div").css("display", "none");
			jQuery(".popup-condiciones").css("display", "block");

		}else {
			jQuery('.km-btn-popup-registrarte-nuevo-correo').html('SIGUIENTE');
         	alert("Revise sus datos por favor, debe llenar todos los campos");
        }
	});
});


jQuery("#popup-registrarte-datos-mascota").ready(function(){
	
	var valid = [
		'nombre_mascota', 
		'tipo_mascota',
		'raza_mascota',
		'color_mascota',
		'datepets',
		'genero_mascota',
		'tamano_mascota'
	];

	var maxDatePets = new Date();
	jQuery('#datepets').datepick({
		dateFormat: 'dd/mm/yyyy',
		maxDate: maxDatePets,
		onSelect: function(xdate) {

			if( jQuery('#datepets').val() != '' ){
				jQuery('[name="sp-date_birth"]').remove();
				jQuery('#datepets').css('color', 'black');
			}
		},
		yearRange: (parseInt(maxDatePets.getFullYear())-30)+':'+maxDatePets.getFullYear(),
	});

	jQuery("#nombre_mascota").blur(function(){
		if(jQuery("#nombre_mascota").val().length == 0){		
			jQuery("#nombre_mascota").parent('div').css('color','red');
			jQuery("#nombre_mascota").after('<span name="sp-nombre_mascota">Ingrese el nombre de su mascota</span>').css('color','red');
			jQuery("#nombre_mascota").focus(function() { jQuery("[name='sp-nombre_mascota']").remove(); });
		}else{
			jQuery("#nombre_mascota").css('color','green');
			jQuery("#nombre_mascota").parent('div').css('color','green');
			jQuery("[name='sp-nombre_mascota']").remove();
		}
	});

	jQuery("#tipo_mascota").on('change',function(){
		jQuery('#raza_mascota').html('<option value="">Cargando razas</option>');
		switch(jQuery("#tipo_mascota").val()) {
			case "0":
				jQuery('#raza_mascota').html('<option value="">Cargando razas</option>');
				jQuery("#comportamiento_gatos").css("display", "none");
			break;
			case "2605":
				listarAjax();
				jQuery("#tamanios_mascota").css("display", "block");
				jQuery("#comportamiento_gatos").css("display", "none");
			break;
			case "2608":
				jQuery('#raza_mascota').html('<option value="1">Gatos</option>');
				jQuery("#tamanios_mascota").css("display", "none");
				jQuery("#comportamiento_gatos").css("display", "block");

				var valor='';
				if (jQuery("#select_1").hasClass("km-opcionactivo")) {
					valor = jQuery("#select_1").attr('value');
				}else if(jQuery("#select_2").hasClass("km-opcionactivo")){
					valor = jQuery("#select_2").attr('value');
				} else if (jQuery("#select_3").hasClass("km-opcionactivo")){
					valor = jQuery("#select_3").attr('value');
				}else if (jQuery("#select_4").hasClass("km-opcionactivo")){
					valor = jQuery("#select_4").attr('value');
				}

				if( valor == '' ){
					jQuery("#select_1").addClass("km-opcionactivo");
					jQuery("#select_1").attr('value', "1");
				}

			break;
		}
	});

	jQuery("#comportamiento_gatos").css("display", "none");

	jQuery("#tipo_mascota").blur(function(){
		jQuery("[name='sp-tipo_mascota']").remove();
		switch(jQuery("#tipo_mascota").val()) {
			case "0":
				jQuery("#tipo_mascota").parent('div').css('color','red');
				jQuery("#tipo_mascota").after('<span name="sp-tipo_mascota">Debe seleccionar un tipo</span>').css('color','red');
				jQuery("#tipo_mascota").focus(function() { jQuery("[name='sp-tipo_mascota']").remove(); });
				break;
			case "2605":
					jQuery("#tipo_mascota").css('color','green');
					jQuery("#tipo_mascota").focus(function() { jQuery("[name='sp-tipo_mascota']").remove(); });
					listarAjax();
				break;
			case "2608":
					jQuery("#tipo_mascota").css('color','green');
					jQuery("#tipo_mascota").focus(function() { jQuery("[name='sp-tipo_mascota']").remove(); });
				break;
		}
	});

	jQuery("#raza_mascota").blur(function(){
		if(jQuery("#raza_mascota").val() == 0){		
			jQuery("#raza_mascota").parent('div').css('color','red');
			jQuery("#raza_mascota").after('<span name="sp-raza_mascota">Seleccione la raza de su mascota</span>').css('color','red');
			jQuery("#raza_mascota").focus(function() { jQuery("[name='sp-raza_mascota']").remove(); });
		}else{
			jQuery("#raza_mascota").css('color','green');
			jQuery("#raza_mascota").parent('div').css('color','green');
			jQuery("[name='sp-raza_mascota']").remove();
		}
	});

	jQuery("#color_mascota").blur(function(){
		if(jQuery("#color_mascota").val().length == 0){		
			jQuery("#color_mascota").parent('div').css('color','red');
			jQuery("#color_mascota").after('<span name="sp-color_mascota">Ingrese el color de su mascota</span>').css('color','red');
			jQuery("#color_mascota").focus(function() { jQuery("[name='sp-color_mascota']").remove(); });
		}else{
			jQuery("#color_mascota").css('color','green');
			jQuery("#color_mascota").parent('div').css('color','green');
			jQuery("[name='sp-color_mascota']").remove();
		}
	});

	jQuery("#date_from").blur(function(){
		if(jQuery("#date_from").val() == 0){		
			jQuery("#date_from").parent('div').css('color','red');
			jQuery("#date_from").after('<span name="sp-date_from">Por favor ingrese una fecha</span>').css('color','red');
			jQuery("#date_from").focus(function() { jQuery("[name='sp-date_from']").remove(); });
		}else{
			jQuery("#date_from").css('color','green');
			jQuery("#date_from").parent('div').css('color','green');
			jQuery("[name='sp-date_from']").remove();
		}
	});

	jQuery("#genero_mascota").blur(function(){
		
		if(jQuery("#genero_mascota").val().length == 0){		
			jQuery("#genero_mascota").parent('div').css('color','red');
			jQuery("#genero_mascota").after('<span name="sp-genero_mascota">Seleccione una opcion por favor</span>').css('color','red');
			jQuery("#genero_mascota").focus(function() { jQuery("[name='sp-genero_mascota']").remove(); });
		}else{
			jQuery("#genero_mascota").css('color','green');
			jQuery("#genero_mascota").parent('div').css('color','green');
			jQuery("[name='sp-genero_mascota']").remove();
		}
	});


    jQuery('#nueva_mascota .km-opcion').on('click', function(e) {
    	jQuery('#nueva_mascota .km-opcion').removeClass("km-opcionactivo");
    	jQuery('#nueva_mascota .km-opcion').children("input").prop("checked", false);

    	jQuery(this).addClass("km-opcionactivo");
		jQuery(this).children("input").prop("checked", true);
    });


	jQuery("#km-check-1").on('click', function() {
		if(jQuery("#km-check-1").val() == "0"){
			jQuery("#km-check-1").attr('value','1');
		}else{
			jQuery("#km-check-1").attr('value','0');
		}
	});

	jQuery("#km-check-2").on('click', function() {
		if(jQuery("#km-check-2").val() == "0"){
			jQuery("#km-check-2").attr('value','1');
		}else{
			jQuery("#km-check-2").attr('value','0');
		}
	});

	jQuery("#km-check-3").on('click', function() {
		if(jQuery("#km-check-3").val() == "0"){
			jQuery("#km-check-3").attr('value','1');
		}else{
			jQuery("#km-check-3").attr('value','0');
		}
	});

	jQuery("#km-check-4").on('click', function() {
		if(jQuery("#km-check-4").val() == "0"){
			jQuery("#km-check-4").attr('value','1');
		}else{
			jQuery("#km-check-4").attr('value','0');
		}
	});


	jQuery(".km-check-gatos").on('click', function() {
		if(jQuery(this).val() == "0"){
			jQuery(this).attr('value','1');
		}else{
			jQuery(this).attr('value','0');
		}
	});


	jQuery(document).on("click", '.popup-registrarte-datos-mascota .km-btn-popup-registrarte-datos-mascota', function ( e ) {
		e.preventDefault();

		jQuery('.km-btn-popup-registrarte-datos-mascota').html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> GUARDANDO');

		var valor='';
    	
		if (jQuery("#select_1").hasClass("km-opcionactivo")) {
			valor = jQuery("#select_1").attr('value');
		}else if(jQuery("#select_2").hasClass("km-opcionactivo")){
			valor = jQuery("#select_2").attr('value');
		} else if (jQuery("#select_3").hasClass("km-opcionactivo")){
			valor = jQuery("#select_3").attr('value');
		}else if (jQuery("#select_4").hasClass("km-opcionactivo")){
			valor = jQuery("#select_4").attr('value');
		}

		var nombre_mascota = jQuery("#nombre_mascota").val(),
			tipo_mascota =jQuery("#tipo_mascota").val(),
			raza_mascota = jQuery("#raza_mascota").val(),
			color_mascota = jQuery("#color_mascota").val(),
			datepets = jQuery("#datepets").val(),
			genero_mascota = jQuery("#genero_mascota").val(),
			tamano_mascota = valor,
			pet_sterilized = jQuery("#km-check-1").val(),
			pet_sociable = jQuery("#km-check-2").val(),
			aggresive_humans = jQuery("#km-check-3").val(),
			aggresive_pets = jQuery("#km-check-4").val(),
			img_pet = jQuery("#img_pet").val();

			$fileupload = jQuery('#carga_foto');
			$fileupload.replaceWith($fileupload.clone(true));

		var campos_pet =[nombre_mascota,tipo_mascota,raza_mascota,color_mascota,
					datepets,genero_mascota,tamano_mascota,pet_sterilized,
					pet_sociable,aggresive_humans,aggresive_pets,img_pet, img_profile];

		
        if( tipo_mascota == '2608' ){
            var selecciono_comportamiento = false;
            jQuery("#comportamiento_gatos input").each(function(i, val){
                if( jQuery(val).val() == 1 ){
                    selecciono_comportamiento = true;
                }
            });
            if( selecciono_comportamiento == false ){
                jQuery(".error_seleccionar_uno").removeClass("no_error");
            }
        }else{
            var selecciono_comportamiento = true;
        }

        if( selecciono_comportamiento ){
            jQuery(".error_seleccionar_uno").addClass("no_error");
        }

 
		if (
			nombre_mascota != "" && 
			tipo_mascota != "" && 
			raza_mascota != "" && 
			color_mascota !="" && 
			datepets != "" && 
			genero_mascota != "" && 
			tamano_mascota >= 0 &&
			valor != ""
		) {
        		var datos = {
		      		'name_pet': campos_pet[0],
		            'tipo_mascota': campos_pet[1],
		            'raza_mascota': campos_pet[2],
		            'color_pet': campos_pet[3],
		            'date_birth': campos_pet[4],
					'gender_pet': campos_pet[5],
		            'size_pet': campos_pet[6],
		            'pet_sterilized': campos_pet[7],
		            'pet_sociable': campos_pet[8],
		            'aggresive_humans': campos_pet[9],
		            'aggresive_pets': campos_pet[10],
		            'img_pet': img_pet,
		            'userid': globalData.trim()
		        };

		        jQuery(".km-check-gatos").each(function(x, input){
					datos[ jQuery(input).attr("name") ] = jQuery(input).val();
				});

				jQuery.post( HOME+'/procesos/login/registro_pet.php', datos, function( data ) {

					if( data >= 1 ){
						jQuery("#btn_cerrar").on("click", function(e){
							finalizar_proceso();
						});
					}else{
						jQuery('.km-btn-popup-registrarte-datos-mascota').after('<div style="margin-bottom:15px;" class="col-xs-12">No se pudo registrar la mascota, verifique los datos y vuelva a intentarlo.</div>');
					}
					jQuery('.km-btn-popup-registrarte-datos-mascota').html('REGISTRARME');
				});

				jQuery(".popup-registrarte-datos-mascota").css("display", "none");
				jQuery(".popup-registrarte-final-0").css("display", "block");

				jQuery("body").scrollTop(0);
				jQuery(".modal").scrollTop(0);

        }else {
			jQuery('.km-btn-popup-registrarte-datos-mascota').html('REGISTRARME');
			km_cliente_validar([
				"nombre_mascota",
				"tipo_mascota",
				"raza_mascota",
				"color_mascota",
				"datepets",
				"genero_mascota",
				"km-check-1",
				"km-check-2",
				"km-check-3",
				"km-check-4",
				"img_pet",
			]);
        	alert("Revise sus datos por favor, debe llenar todos los campos");        	
        }
	});
});

function km_cliente_validar( fields ){
	var primer_error = '';
	var status = true;
	if( fields.length > 0 ){
		jQuery.each( fields, function(id, val){
			var m = '';
			var valor = '';
			/*validar vacio*/
			if( jQuery('#'+val).val() == '' ){
				valor = jQuery('#'+val).val();
				m = 'Este campo no puede estar vacio';
			}else if( val == 'tipo_mascota' && jQuery('#tipo_mascota').val() < 1){
				m = 'Este campo no puede estar vacio';
			}else if( val == 'raza_mascota'  && jQuery('#raza_mascota').val() < 1){
				m = 'Este campo no puede estar vacio';
			}else if( val == 'email' ){
				jQuery("#km-datos-foto-profile").css('border', "1px solid red");
			}else if( val == 'tamano_mascota' ){
				if (jQuery("#select_1").hasClass("km-opcionactivo")) {
					valor = jQuery("#select_1").attr('value');
				}else if(jQuery("#select_2").hasClass("km-opcionactivo")){
					valor = jQuery("#select_2").attr('value');
				} else if (jQuery("#select_3").hasClass("km-opcionactivo")){
					valor = jQuery("#select_3").attr('value');
				}else if (jQuery("#select_4").hasClass("km-opcionactivo")){
					valor = jQuery("#select_4").attr('value');
				}
				if( valor == '' ){
					m = 'Este campo no puede estar vacio';
				}
			}

			if( m == ''){
				status = true;
				jQuery('[name="sp-'+jQuery('#'+val).attr('name')+'"]').remove();
			}else{
				jQuery('#'+val).parent('div').css('color','red');
				jQuery('[name="sp-'+jQuery('#'+val).attr('name')+'"]').remove();
				jQuery('#'+val).after('<span name="sp-'+jQuery('#'+val).attr('name')+'">'+m+'</span>').css('color','red');
				status = false;
				if(primer_error==''){
					primer_error = val;
				}
			}
		});
	    jQuery('html, body').animate({ scrollTop: jQuery("#"+primer_error).offset().top-180 }, 2000);
	}
	return status;
}
function listarAjax() {
	__ajax(HOME+"/procesos/login/mascota.php", "")
	.done(function(info){
		jQuery('#raza_mascota').html(info);
	});
}

function __ajax(url, data){
	var ajax = jQuery.ajax({
		"method": "POST",
		"url": url,
		"data": data
	})
	return ajax;
}

function getGlobalData(url,method, datos){
	return jQuery.ajax({
		data: datos,
		type: method,
		url: HOME+url,
		async:false,
		success: function(data){
			return data;
		}
	}).responseText;
}


jQuery( document ).on('change', '[data-change]', function(){


    var tipo = jQuery(this).attr('data-change');
    var str = jQuery(this).val();

    if(tipo != 'undefined' || tipo != ''){
    	var cadena= '';
        if(tipo.indexOf('alf')>-1 ){ cadena = cadena + 'a-zA-ZáéíóúñüÁÉÍÓÚÑÜ' }
        if(tipo.indexOf('xlf')>-1 ){ cadena = cadena + 'a-zA-ZáéíóúñüÁÉÍÓÚÑÜ ' }
        if(tipo.indexOf('mlf')>-1 ){ cadena = cadena + 'a-zA-Z' }
        if(tipo.indexOf('ylf')>-1 ){ cadena = cadena + 'A-Z' }
        if(tipo.indexOf('num')>-1 ){ cadena = cadena + '0-9' } 
        if(tipo.indexOf('cur')>-1 ){ cadena = cadena + '0-9,.' } 
        if(tipo.indexOf('esp')>-1 ){ cadena = cadena + '-_.%&@,/()' }
        if(tipo.indexOf('cor')>-1 ){ cadena = cadena + '.-_@0-9a-zA-Z' }
        if(tipo.indexOf('rif')>-1 ){ cadena = cadena + 'vjegi' }
        if(tipo.indexOf('dir')>-1 ){ cadena = cadena + ',' }

        cadena = eval("/[^"+cadena+"]+/g");
    
	    str = str.replace( cadena ,'');
    }
    jQuery(this).val(str);

});

jQuery( document ).on('keypress', '[data-charset]', function(e){

    var tipo= jQuery(this).attr('data-charset');

    if(tipo!='undefined' || tipo!=''){
        var cadena = "";

        if(tipo.indexOf('alf')>-1 ){ cadena = cadena + "abcdefghijklmnopqrstuvwxyzáéíóúñüÁÉÍÓÚÑÜ"; }
        if(tipo.indexOf('xlf')>-1 ){ cadena = cadena + "abcdefghijklmnopqrstuvwxyzáéíóúñüÁÉÍÓÚÑÜ "; }
        if(tipo.indexOf('mlf')>-1 ){ cadena = cadena + "abcdefghijklmnopqrstuvwxyz"; }
        if(tipo.indexOf('num')>-1 ){ cadena = cadena + "1234567890"; }
        if(tipo.indexOf('cur')>-1 ){ cadena = cadena + "1234567890,."; }
        if(tipo.indexOf('esp')>-1 ){ cadena = cadena + "-_.$%&@,/()"; }
        if(tipo.indexOf('cor')>-1 ){ cadena = cadena + ".-_@"; }
        if(tipo.indexOf('rif')>-1 ){ cadena = cadena + "vjegi"; }
        if(tipo.indexOf('dir')>-1 ){ cadena = cadena + ","; }

        var key = e.which,
            keye = e.keyCode,
            tecla = String.fromCharCode(key).toLowerCase(),
            letras = cadena;

        if(letras.indexOf(tecla)==-1 && keye!=9&& (key==37 || keye!=37)&& (keye!=39 || key==39) && keye!=8 && (keye!=46 || key==46) || key==161){
            e.preventDefault();
        }
    }
   
});

function finalizar_proceso(){
	if( !mail_admin_registro ){	
		mail_admin_registro = true;
		jQuery.post(
			HOME+'procesos/login/enviar_mail_admin.php', 
			{
				'email' : jQuery("#email_1").val(),
			}, 
			function(_result){
				mail_admin_registro = false;
		    	location.href = jQuery("#btn_iniciar_sesion").attr("data-url");
			}
		);
	}
}

/*POPUP INICIAR SESIÓN*/
	jQuery(document).on("click", '.popup-iniciar-sesion-1 .km-btn-contraseña-olvidada', function ( e ) {
		e.preventDefault();
		jQuery(".popup-iniciar-sesion-1").css("display", "none");
		jQuery(".popup-olvidaste-contrasena").css("display", "block");
	});

	jQuery(document).on("click", '.popup-registrarte-1 .km-btn-popup-registrarte-1', function ( e ) {
		e.preventDefault();
		jQuery(".popup-registrarte-1").css("display", "none");
		jQuery(".popup-registrarte-nuevo-correo").css("display", "block");

		if( wlabel == "petco" ){
			adf.ClickTrack(this,1453019,'MX_Kmimos_Registo_180907',{});
		}
	});
/*FIN POPUP INICIAR SESIÓN*/

/* Cargar imagen de la mascota */
function vista_previa(evt) {

    jQuery("#loading-mascota").css("display", "block");

    var files = evt.target.files;
	getRealMime(this.files[0]).then(function(MIME){
        if( MIME.match("image.*") ){

        	jQuery("#loading-mascota").css("display", "block");

            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    redimencionar(e.target.result, function(img_reducida){
                        var img_pre = jQuery(".vlz_rotar_valor").attr("value");
                        jQuery.post( RUTA_IMGS+"/procesar.php", {img: img_reducida, previa: img_pre}, function( url ) {
                           
                        	jQuery("#km-datos-foto").css("background-image", "url("+RAIZ+"imgs/Temp/"+url+")");
							jQuery("#img_pet").val( url );
							jQuery("#loading-mascota").css("display", "none");

                            jQuery("#nueva_mascota .btn_rotar").css("display", "block");
                        });
                    });      
                };
           })(files[0]);
           reader.readAsDataURL(files[0]);
        }else{
        	padre.children('#carga_foto_profile').val("");
            alert("Solo se permiten imagenes");
        }
    }).catch(function(error){
        padre.children('#carga_foto_profile').val("");
        alert("Solo se permiten imagenes");
    }); 
}      
jQuery("#km-datos-foto").on('click', function(){
	jQuery("#carga_foto").trigger("click");
});
document.getElementById("carga_foto").addEventListener("change", vista_previa, false);
/* Cargar imagen de la mascota */


/* Cargar imagen de la perfil */
function vista_previa_perfil(evt) {

	jQuery('[name="sp-img_profile"]').parent().css('color', '#000');
	jQuery('#img_profile').parent().css('color', '#000');
	jQuery('[name="sp-img_profile"]').remove();

	var files = evt.target.files;
	getRealMime(this.files[0]).then(function(MIME){
        if( MIME.match("image.*") ){

        	jQuery("#loading-perfil").css("display", "block");

            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    redimencionar(e.target.result, function(img_reducida){
                        var img_pre = jQuery(".vlz_rotar_valor").attr("value");
                        jQuery.post( RUTA_IMGS+"/procesar.php", {img: img_reducida, previa: img_pre}, function( url ) {
                           
                        	jQuery("#km-datos-foto-profile").css("background-image", "url("+RAIZ+"imgs/Temp/"+url+")");
							jQuery("#img_profile").val( url );
							jQuery(".kmimos_cargando").css("visibility", "hidden");
							jQuery("#loading-perfil").css("display", "none");

                            jQuery("#form_nuevo_cliente .btn_rotar").css("display", "block");
                        });
                    });      
                };
           })(files[0]);
           reader.readAsDataURL(files[0]);
        }else{
        	padre.children('#carga_foto_profile').val("");
            alert("Solo se permiten imagenes");
        }
    }).catch(function(error){
        padre.children('#carga_foto_profile').val("");
        alert("Solo se permiten imagenes");
    }); 
}      

jQuery("#km-datos-foto-profile").on('click', function(){
	jQuery("#carga_foto_profile").trigger("click");
});
document.getElementById("carga_foto_profile").addEventListener("change", vista_previa_perfil, false);
/* Cargar imagen de la perfil */

jQuery("#recovery-clave").on('click',function(){
	
	recuperar_clave(jQuery("#form_recuperar"));
});
jQuery("#form_recuperar").submit(function(){
	recuperar_clave(jQuery(this));
	return false;
});

function recuperar_clave(_this){

	
	_this.find(".response").html('');
	var mail = _this.find("#usuario");
	var data_email = mail.val();
	var obj = _this.find(".verify_result");
	var err = '';

	jQuery('#recovery-clave').html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> RESTAURANDO CLAVE');
	if(data_email == ""){
		err = 'Este campo no puede estar vacio';
	}else if( data_email.length<3 ){
		err = 'Formato de email invalido';
	}else if(!mail.hasClass('correctly')){
		err = 'Este E-mail no existe';
	}else{
		var datos = {'email': data_email};
		jQuery.post(HOME+'/procesos/login/recuperar.php', datos, function(_result){
			var r = jQuery.parseJSON(_result);
			_this.find(".response").html(r.msg);
			jQuery('#recovery-clave').html('ENVIAR CONTRASEÑA');
			if( r.sts == 1 ){
				setTimeout(function(){ jQuery('.modal').modal('hide'); },3600);
			}
		});
	}
	if( err != '' ){
		obj.css('color', 'red');
		obj.html(err);
		jQuery('#recovery-clave').html('ENVIAR CONTRASEÑA');
	}else{
		obj.html('');
	}

}