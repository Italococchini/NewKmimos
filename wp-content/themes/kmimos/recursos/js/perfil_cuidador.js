jQuery( document ).ready(function() {
	GetComments();
	jQuery(".km-btn-comentario").on("click", function(e){
		jQuery('.modal_info_comentar').css("display", "block");
		jQuery('.modal_comentario_enviado').css("display", "none");
		jQuery('.comments').css("display", "none");
		jQuery('.BoxComment').slideDown();
	});
	jQuery("#comentar").on("click", function(e){
		jQuery('.modal_info_comentar').css("display", "none");
		jQuery('.comments').slideDown();
	});
	jQuery(".servicio_item_box").on("click", function(e){
		jQuery(".servicio_item_box").removeClass("servicio_visible");
		jQuery(this).addClass("servicio_visible");
	});
});

var map_cuidador;
function initMap() {
	var latitud = lat;
	var longitud = lng;
	map_cuidador = new google.maps.Map(document.getElementById('mapa'), {
		zoom: 15,
		center:  new google.maps.LatLng(latitud, longitud), 
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		scrollwheel: false
	});
	marker = new google.maps.Marker({
		map: map_cuidador,
		draggable: false,
		animation: google.maps.Animation.DROP,
		position: new google.maps.LatLng(latitud, longitud),
		icon: "https://www.kmimos.com.mx/wp-content/themes/kmimos/js/images/n1.png"
	});

	map_cuidador_movil = new google.maps.Map(document.getElementById('mapa_movil'), {
		zoom: 15,
		center:  new google.maps.LatLng(latitud, longitud), 
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		scrollwheel: false
	});
	marker = new google.maps.Marker({
		map: map_cuidador_movil,
		draggable: false,
		animation: google.maps.Animation.DROP,
		position: new google.maps.LatLng(latitud, longitud),
		icon: "https://www.kmimos.com.mx/wp-content/themes/kmimos/js/images/n1.png"
	});
}

(function(d, s){
	map = d.createElement(s), e = d.getElementsByTagName(s)[0];
	map.async=!0;
	map.setAttribute("charset","utf-8");
	map.src="//maps.googleapis.com/maps/api/js?v=3&key=AIzaSyD-xrN3-wUMmJ6u2pY_QEQtpMYquGc70F8&callback=initMap";
	map.type="text/javascript";
	e.parentNode.insertBefore(map, e);
})(document,"script");

(function(d, s){
    $ = d.createElement(s), e = d.getElementsByTagName(s)[0];
    $.async=!0;
    $.setAttribute('charset','utf-8');
    $.src='//www.google.com/recaptcha/api.js?hl=es';
    $.type='text/javascript';
    e.parentNode.insertBefore($, e)
})(document, 'script');

var comentarios_cuidador = [];
function comentarios(pagina = 0){
	var bond_total=0;
	var bond_porcent=0;
	var comentario = '';
	var cantidad_valoraciones = 0;
	jQuery.each(comentarios_cuidador, function( pagina, cuidador ) {
		var bond_testimony = 0;
		if(
			comentarios_cuidador[pagina]["cuidado"] 		> 0 &&
			comentarios_cuidador[pagina]["puntualidad"] 	> 0 &&
			comentarios_cuidador[pagina]["limpieza"] 		> 0 &&
			comentarios_cuidador[pagina]["confianza"] 		> 0
		){
			comentario += '	<div class="km-comentario">';
			comentario += '		<div class="row">';
			comentario += '			<div class="col-xs-2">';
			comentario += '				<div class="km-foto-comentario-cuidador" style="background-image: url('+comentarios_cuidador[pagina]["img"]+');"></div>';
			comentario += '			</div>';
			comentario += '			<div class="col-xs-9 pull-right">';
			comentario += '				<p class="km-tit-ficha">'+comentarios_cuidador[pagina]["cliente"]+'</p>';
			comentario += '				<p class="km-fecha-comentario">'+comentarios_cuidador[pagina]["fecha"]+'</p>';
			comentario += '			</div>';
			comentario += '		</div>';
			comentario += '		<div class="row">';
			comentario += '			<div class="col-md-12"><p>'+ comentarios_cuidador[pagina]["contenido"]+'</p></div>';
			comentario += '		</div>';
			comentario += '		<div class="row km-review-categoria">';
			comentario += '			<div class="col-xs-6 col-md-3">';
			comentario += '				<p>CUIDADO</p>';
			comentario += '				<div class="km-ranking">';
			comentario += 					get_huesitos(comentarios_cuidador[pagina]["cuidado"]);
			comentario += '				</div>';
			comentario += '			</div>';
			comentario += '			<div class="col-xs-6 col-md-3">';
			comentario += '				<p>PUNTUALIDAD</p>';
			comentario += '				<div class="km-ranking">';
			comentario += 					get_huesitos(comentarios_cuidador[pagina]["puntualidad"]);
			comentario += '				</div>';
			comentario += '			</div>';
			comentario += '			<div class="col-xs-6 col-md-3">';
			comentario += '				<p>LIMPIEZA</p>';
			comentario += '				<div class="km-ranking">';
			comentario += 					get_huesitos(comentarios_cuidador[pagina]["limpieza"]);
			comentario += '				</div>';
			comentario += '			</div>';
			comentario += '			<div class="col-xs-6 col-md-3">';
			comentario += '				<p>CONFIANZA</p>';
			comentario += '				<div class="km-ranking">';
			comentario += 					get_huesitos(comentarios_cuidador[pagina]["confianza"]);
			comentario += '				</div>';
			comentario += '			</div>';
			comentario += '		</div>';
			comentario += '	</div>';
			bond_testimony = bond_testimony+parseFloat(comentarios_cuidador[pagina]["confianza"]);
			bond_testimony = bond_testimony+parseFloat(comentarios_cuidador[pagina]["limpieza"]);
			bond_testimony = bond_testimony+parseFloat(comentarios_cuidador[pagina]["puntualidad"]);
			bond_testimony = bond_testimony+parseFloat(comentarios_cuidador[pagina]["cuidado"]);
			cantidad_valoraciones++;
			bond_total = bond_total+bond_testimony;
		}else{
			comentario += '	<div class="km-comentario">';
			comentario += '		<div class="row">';
			comentario += '			<div class="col-xs-2">';
			comentario += '				<div class="km-foto-comentario-cuidador" style="background-image: url('+comentarios_cuidador[pagina]["img"]+');"></div>';
			comentario += '			</div>';
			comentario += '			<div class="col-xs-9 pull-right">';
			comentario += '				<p class="km-tit-ficha">'+comentarios_cuidador[pagina]["cliente"]+'</p>';
			comentario += '				<p class="km-fecha-comentario">'+comentarios_cuidador[pagina]["fecha"]+'</p>';
			comentario += '			</div>';
			comentario += '		</div>';
			comentario += '		<div class="row">';
			comentario += '			<div class="col-md-12"><p>'+ comentarios_cuidador[pagina]["contenido"]+'</p></div>';
			comentario += '		</div>';
			comentario += '	</div>';
		}

	});

	if( bond_total > 0 ){
		bond_total=bond_total/(cantidad_valoraciones*4);
		bond_porcent=bond_total*(100/5);

		var bond = '<div class="km-ranking">';
			bond += get_huesitos(bond_total);
			bond += '</div>';

		jQuery("#comentarios_box").html( comentario );
		jQuery(".km-review .km-calificacion").html( comentarios_cuidador.length );
		jQuery(".km-review .km-calificacion-icono p").html( parseInt(bond_porcent)+'% Lo recomienda');
		jQuery(".km-review .km-calificacion-bond").html(bond);
	}else{
		var bond = '<div class="km-ranking">';
			bond += get_huesitos(bond_total);
			bond += '</div>';

		jQuery("#comentarios_box").html( comentario );
		jQuery(".km-review .km-calificacion").html( comentarios_cuidador.length );
		jQuery(".km-review .km-calificacion-icono p").html( bond_total+'% Lo recomienda');
		// jQuery(".km-review .km-calificacion-bond").html(bond);
	}
	
}

function get_huesitos(valor){
	var huesos = "";
	for (var i = 0; i < valor; i++) {
		huesos += '<a href="#" class="active"></a>';
	}
	for (var i = valor; i < 5; i++) {
		huesos += '<a href="#"></a>';
	}
	return huesos;
}