jQuery.noConflict();
( function( $ ) {
	switcher = {
		init : function () {

		    // Map
			if ( $( '#map' ).length ) {
				
				var germany = new google.maps.LatLng( 51.133333, 10.416667 );
				var map_options = {
					zoom: 6,
					center: germany,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				var marker_count = $( '#map_data .meetup_event' ).length;
				
				var map = new google.maps.Map( $( '#map' )[ 0 ], map_options );
				$( '#map_data .meetup_event' ).each( function( index, value ) {
					var meetup = $( value );
					var meetup_data = meetup.data();
					if ( meetup_data.geoLat != undefined && meetup_data.geoLng  != undefined ) {
						var lat = meetup_data.geoLat;
						var lng = meetup_data.geoLng;
						var zoom = 10;
					}
					else {
						var lat = 51.133333;
						var lng = 10.416667;
						var zoom = 6;
					}
					
					var marker_point = new google.maps.LatLng( lat, lng );
					
					// only focus on this marker if there is only one
					if ( marker_count === 1 ) {
						map.panTo( marker_point );
						map.setZoom( zoom );
					}
					
					var location_url = (meetup_data.location_url) ? "<a href=\"" + meetup_data.location_url + "\">" + meetup_data.location_url + "</a>" : "";
					var info_window_content = ''
						+ meetup_data.location + "</br>"
						+ location_url + "</br>"
						+ meetup_data.street + " " + meetup_data.number + "</br>"
						+ meetup_data.plz + " " + meetup_data.town + "</br>"
						+ "<strong>" + meetup_data.date + " " + meetup_data.time + "</strong>" + "</br>"
						+ "<a href=\"" + meetup_data.permalink + "\">Details ...</a>"
					;

					$("#marker_address").hide();
					var infowindow = new google.maps.InfoWindow({
				        content: info_window_content
				    });

					var marker = new google.maps.Marker( {
						position: marker_point,
						map: map,
						icon: switcher_vars.template_dir + '/images/pin.png'
					} );

					google.maps.event.addListener(marker, 'click', function() {
				      infowindow.open( map, marker );
				    } );
				} );
			}
		},
	};
	$( document ).ready( function( $ ) { switcher.init(); } );
} )( jQuery );