<?php
/**
 * Plugin Name: Location Distance Calculator
 * Plugin URI: https://www.tomwithers.me
 * Description: calculate the distance between two given user points
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */
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

function locationData() {
	/**
	* Pull the form fields
	*/

	$currentLoc = urlencode( $_POST['']);
	$destLoc = urlencode( $_POST['']);;
	$data = file_get_contents( "http://maps.googleapis.com/maps/api/distancematrix/json?origins=$currentLoc&destinations=$destLoc&language=en-EN&sensor=false" );
	$data = json_decode( $data );
	$time = 0;
	$distace = 0;

	/**
	* Check If form has had data entered
	*/
	if ( empty( $currentLoc ) OR empty( $destLoc) ) {
		http_respone_code(400);
		echo "Please fill out all fields.";
		die;
	}

	/**
	* Calculate the disatnce
	*/
	foreach ( $data->rows[0]->elements as $road ) {
		$time += $road->duration->value;
		$distance += $road->distance->text;
	}

	$time =$time/60;
	//$distance = round( $distnace / 1000 );

	/**
	* Output the vaules
	*/

	if ( $distance != 0 ) {
		echo "<div id='result-generated'>";
		echo "From: " . $data->origin_addresses[0];
		echo "<br/>";
		echo "To: ". $data->destination_addresses[0];
		echo "<br/>";
		echo "Time: ".gmdate("H:i", ($time * 60))." hour(s)";
		echo "<br/>";
		echo "Distance: " . $distance . " Miles";
		echo "<br/>";
		echo "</div>";
		die;
	} else {
		die;
	}
}

?>
