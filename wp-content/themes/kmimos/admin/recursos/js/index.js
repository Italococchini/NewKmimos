function init_modal(data){

    console.log( data );

	jQuery(".modal > div > span").html(data["titulo"]);

	jQuery.ajax({
        async:true, cache:false, type: 'POST', url: TEMA+"/admin/backend/"+data["modulo"]+"/modales/"+data["modal"]+".php",
        data: data["info"], 
        success:  function(HTML){
            jQuery(".modal > div > div").html( HTML );

            jQuery(".modal").css("display", "block");

        },
        beforeSend:function(){},
        error:function(e){
        	console.log(e);
        }
    });

    jQuery("#close_modal").on("click", function(e){
        jQuery(".modal").css("display", "none");
    });

}