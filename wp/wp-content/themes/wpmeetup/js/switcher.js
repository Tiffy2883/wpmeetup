jQuery.noConflict();
( function( $ ) {
	switcher = {
		init : function () {
			
			// Single Map
			if ( $( '#single_map' ) ) {
				if ( $( '#single_map' ).attr( 'data-geo-lat' ) && $( '#single_map' ).attr( 'data-geo-long' ) ) {
					var lat = $( '#single_map' ).attr( 'data-geo-lat' );
					var long = $( '#single_map' ).attr( 'data-geo-long' );
					var zoom = 10;
				}
				else {
					var lat = 51.133333;
					var long = 10.416667;
					var zoom = 6;
				}
				
				var germany = new google.maps.LatLng( 51.133333, 10.416667 );
				var map_options = {
					zoom: zoom,
					center: germany,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				
				var map = new google.maps.Map( $( '#single_map' )[ 0 ], map_options );
				var marker_point = new google.maps.LatLng( lat, long );
				map.panTo( marker_point );
				
				var current_marker = new google.maps.Marker( {
					position: marker_point,
					map: map,
					icon: switcher_vars.template_dir + '/images/pin.png'
				} );
			}
		},
	};
	$( document ).ready( function( $ ) { switcher.init(); } );
} )( jQuery );