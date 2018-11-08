jQuery(document).ready( function (){

	jQuery('#form-registro').on('submit', function(e){
		e.preventDefault();
		var btn = jQuery('#form-registro button[type="submit"]');
		if( !btn.hasClass('disabled') ){
			btn.addClass('disabled');
			jQuery.post(
		        HOME+'procesos/clubPatitasFelices/registro-usuario.php',
		        jQuery(this).serialize(),
		        function(d){
		        	console.log(d);
					btn.removeClass('disabled');
					//location.reload();
		    });
		}
	});

});
