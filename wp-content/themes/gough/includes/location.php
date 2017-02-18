<?php
/**
 * Plugin Name: Location Distance Calculator
 * Plugin URI: https://tomwithers.me
 * Description: calculate the distance between two given user points
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Author: Thomas WIthers
 * Author URI: https://tomwithers.me
 */

function locationData() {
	/**
	* Pull the form fields, $_POST contains the data.
	*/
	if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
		$currentLoc = urlencode( $_POST['currentLocation']);
		$destLoc = urlencode( $_POST['destLocation']);;
		$data = file_get_contents( "http://maps.googleapis.com/maps/api/distancematrix/json?origins=$currentLoc&destinations=$destLoc&language=en-EN&sensor=false" );
		$data = json_decode( $data );
		$time = 0;
		$distace = 0;

		/**
		* Check If form has had data entered
		*/
		if ( empty( $currentLoc ) OR empty( $destLoc) ) {
			http_response_code(400);
			echo "Please fill out all fields.";
			die;
		}

		/**
		* Get values from JSON Resoonse
		*/
		foreach ( $data->rows[0]->elements as $road ) {
			$time += $road->duration->value;
			$distance += $road->distance->value;
		}
		/**
		* Calculate time in Hours and Minutes
		*/
		$time =$time/60;
		$distance = round( $distance / 1000 );

		/**
		* Output the vaules
		*/

		if ( $distance != 0 ) {
			echo "<div id='result-generated'>";
			echo "Distance: " . $distance . " km(s)";
			echo "<br/>";
			echo "From: " . $data->origin_addresses[0];
			echo "<br/>";
			echo "To: ". $data->destination_addresses[0];
			echo "<br/>";
			echo "Time: ".gmdate("H:i", ($time * 60))." hour(s)";
			echo "<br/>";
			echo "</div>";
			die;
		} else {
			http_response_code(500);
			echo 'This is not working';
			die;
		}
	}
}
add_action( 'wp_ajax_locationCalculate', 'locationData' );
add_action( 'wp_ajax_nopriv_locationCalculate', 'locationData' );


function distanceCalculator() {
	/**
	* Input form for user
	*/
	?>
	<form id="locationForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="locationForm">
		<div class="form-group">
			<label for="currentLocation">Current Location: </label>
			<input id="currentLoc" type="text" name="currentLocation" class="required" />
		</div>
		<div class="form-group">
			<label for="destLocation">Destination Location: </label>
			<input id="destLoc" type="text" name="destLocation" class="required" />
		</div>
		<div class="form-group">
			<input type="hidden" name="action" value="locationCalculate">
			<button type="submit" class="btn btn-submit">Calculate Distance</button>
		</div>
		<div id="result"></div>
		<div class="form-message"></div>
	</form>

	<?php
}
add_shortcode( 'Calculator', 'distanceCalculator');
