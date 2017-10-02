function menu(){
	var w = jQuery(window).width();

	if(jQuery(this).scrollTop() > 10) {

		jQuery('.bg-transparent').addClass('bg-white');
		jQuery('.navbar-brand img').attr('src', HOME+'images/new/km-logos/km-logo-negro.png');

	} else {

		if( !jQuery(".navbar").hasClass("bg-white-secondary") ){
			jQuery('.bg-transparent').removeClass('bg-white');
			jQuery('.navbar-brand img').attr('src', HOME+'/images/new/km-logos/km-logo.png');
		}
		
	}

}

jQuery(window).resize(function() {
	menu();
});

jQuery(window).scroll(function() {
	menu();
});

var fecha = new Date();
jQuery(document).ready(function(){
	menu();

	jQuery(document).on("focus", "input.input-label-placeholder", function(){
		jQuery(this).parent().addClass("focus");
	}).on("blur", "input.input-label-placeholder", function(){
		let i = jQuery(this);
		if ( i.val() !== "" ) jQuery(this).parent().addClass("focused");
		else jQuery(this).parent().removeClass("focused");

		jQuery(this).parent().removeClass("focus");
	});

});
