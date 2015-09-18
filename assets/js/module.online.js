jQuery(document).ready(function($) {
	//wp.heartbeat.debug = true;
	//wp.heartbeat.stop();
	wp.heartbeat.interval( 15 );
	wp.heartbeat.enqueue( 'gmember-online', true, false );
	$(document).on( 'heartbeat-tick.gmember-online', function( event, data, textStatus, jqXHR ) {
		if ( data.hasOwnProperty( 'gmember-online' ) ) {
			//console.log(data['gmember-online']);
			$('#gmember-online').html(data['gmember-online']);
		};
		//wp.heartbeat.interval( 60 );
		wp.heartbeat.enqueue( 'gmember-online', true, false );
	});
}(jQuery));

// much better : http://msankhala.wordpress.com/2012/08/29/loading-image-through-ajax/
//jQuery.fn.image = function(src, f) {return this.each(function() {var i = new Image();i.src = src;i.onload = f;this.appendChild(i); });}
//jQuery("div#container").image("oganges.jpg",function(){alert("The image is loaded now");});


/**
// http://stackoverflow.com/a/4285068
// You can create a new image element, set its source attribute and place it somewhere in the document once it has finished loading:
var img = $("<img />").attr('src', 'http://somedomain.com/image.jpg')
	.load(function() {
		if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
			alert('broken image!');
		} else {
			$("#something").append(img);
		}
	});

**/
