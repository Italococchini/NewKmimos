jQuery( document ).ready(function() {
	fixedHeader();
	jQuery(window).on('scroll', function () {
	  	fixedHeader();
	});
});

function fixedHeader() {
    if( !jQuery('nav').hasClass("nav_busqueda") ){
        let ww = jQuery(window).scrollTop();
        if (ww > 0) {
            jQuery('nav').addClass('nav_white');
        } else {
            jQuery('nav').removeClass('nav_white');
        }
    }
}