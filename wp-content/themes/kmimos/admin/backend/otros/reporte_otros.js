var table = ""; var CTX = "";
jQuery(document).ready(function() {
    
} );

function abrir_link(e){
	init_modal({
		"titulo": e.attr("data-titulo"),
		"modulo": "saldos",
		"modal": e.attr("data-modal"),
		"info": {
			"ID": e.attr("data-id")
		}
	});
}

function getStatus(){
	if( !jQuery("#consultar").hasClass("disable") ){
		jQuery("#consultar").addClass("disable");
		jQuery("#consultar").val("Procesando...");
		jQuery.post(
			TEMA+"/admin/backend/otros/ajax/getStatus.php",
			{
				reserva: jQuery("#reserva").val()
			},
			function(HTML){
				jQuery("#info_user").html(HTML);

				jQuery("#info_user").css("display", "block");
				jQuery(".confirmaciones").css("display", "block");

	            jQuery("#consultar").removeClass("disable");
	            jQuery("#consultar").val("Actualizar");
	        }
	    ); 
	}
}

function updateStatus(){
	if( !jQuery("#confirmar").hasClass("disable") ){
		jQuery("#confirmar").addClass("disable");
		jQuery("#confirmar").val("Procesando...");
		jQuery.post(
			TEMA+"/admin/backend/otros/ajax/updateStatus.php",
			{
				ORDEN_ID: jQuery("#orden").val(),
				status: jQuery("#status").val()
			},
			function(HTML){

	            jQuery("#confirmar").removeClass("disable");
	            jQuery("#confirmar").val("Confirmar");

				getStatus();
	        }
	    ); 
	}
}

function cerrarInfo(){
	jQuery("#saldo").val("");
	jQuery("#email").val("");
	jQuery("#info_user").css("display", "none");
	jQuery(".confirmaciones").css("display", "none");
}	