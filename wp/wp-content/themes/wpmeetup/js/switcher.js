jQuery.noConflict();
( function( $ ) {
	switcher = {
		init : function () {
			
			// Single Map
			if ( $( '#single_map' ) ) {
				if ( $( '#single_map' ).data( 'geo-lat' ) != undefined && $( '#single_map' ).data( 'geo-lng' ) != undefined ) {
					var lat = $( '#single_map' ).data( 'geo-lat' );
					var lng = $( '#single_map' ).data( 'geo-lng' );
					var zoom = 10;
				}
				else {
					var lat = 51.133333;
					var lng = 10.416667;
					var zoom = 6;
				}
				
				var germany = new google.maps.LatLng( 51.133333, 10.416667 );
				var map_options = {
					zoom: zoom,
					center: germany,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				
				var map = new google.maps.Map( $( '#single_map' )[ 0 ], map_options );
				var marker_point = new google.maps.LatLng( lat, lng );
				map.panTo( marker_point );
				
				$("#marker_address").hide();
				var infowindow = new google.maps.InfoWindow({
			        content: $("#marker_address").html()
			    });
				
				var marker = new google.maps.Marker( {
					position: marker_point,
					map: map,
					icon: switcher_vars.template_dir + '/images/pin.png',
					title: "Hello World"
				} );
				
				google.maps.event.addListener(marker, 'click', function() {
			      infowindow.open( map, marker );
			    } );
			}
		},
	};
	$( document ).ready( function( $ ) { switcher.init(); } );
} )( jQuery );