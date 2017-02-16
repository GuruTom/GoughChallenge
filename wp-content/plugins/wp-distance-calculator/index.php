<?php
/**
 * Plugin Name: WP Distance Calculator
 * Plugin URI: http://phpcodingschool.blogspot.com/
 * Description: This plugin claculates distance between two near by locations.
 * Version: 1.0.0
 * Author: Monika Yadav
 * Author URI: http://phpcodingschool.blogspot.com/
 * License: GPL2
 */

class DistanceWPCalculator
{
	public function __construct()
	{       //action definations
	        add_shortcode( 'distance_calculator',  array( &$this, 'distanceWPfrontend' ) ); 
		
			add_action( 'wp_ajax_nopriv_distancewpcalculator', array( &$this, 'distancewpcalculator_calculate' ) );
			add_action( 'wp_ajax_distancewpcalculator', array( &$this, 'distancewpcalculator_calculate' ) );
		
		    add_action( 'init', array( &$this, 'init' ) );
	}

	public function init()
	{
		wp_enqueue_script( 'distancewpcalculator', plugin_dir_url( __FILE__ ) . 'js/calculatedistance.js', array( 'jquery' ) );
		wp_localize_script( 'distancewpcalculator', 'DistanceCalculator', array(
		    'ajaxurl' => admin_url( 'admin-ajax.php' )
		) );
		?>
		<script>
		var ajaxurl =  "<?php echo admin_url('admin-ajax.php'); ?>";
		</script>
		<?php
		wp_enqueue_style( 'DistanceWPCalculator-Style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '0.1', 'screen' );
	}

	public function distancewpcalculator_calculate()
	{   
		// The $_POST contains all the data sent via ajax
		if ( isset($_POST) ) {
			 
		$from = urlencode($_POST['from']);
		$to = urlencode($_POST['to']);
		$data = file_get_contents("http://maps.googleapis.com/maps/api/distancematrix/json?origins=$from&destinations=$to&language=en-EN&sensor=false");
		$data = json_decode($data);
		$time = 0;
		$distance = 0;
			foreach($data->rows[0]->elements as $road) {
				$time += $road->duration->value;
				$distance += $road->distance->value;
			}
			$time =$time/60;
			$distance =round($distance/1000);
			//Output
			if($distance!=0){
			
			echo "<div id='result_generated'>";
			echo "From: ".$data->origin_addresses[0];
			echo "<br/>";
			echo "To: ".$data->destination_addresses[0];
			echo "<br/>";
			echo "Time: ".gmdate("H:i", ($time * 60))." hour(s)";
			echo "<br/>";
			echo "Distance: ".$distance." km(s)";
			echo "</div>";		   
			}else{
			echo "Sorry only nearby distance can be calculated."; 
			}				 
		   }
		 
    die();
	
	}
	
	//Function to display form on front-end
	public function distanceWPfrontend( $atts ) {
		
	?>  
		<form method = "post" id="calculator" >
			<div class="DC_title">Distance Calculator</div>
			<input type="text" id="from" name="from" placeholder="From.."></br>
			<input type="text" id="to" name="to" placeholder="To.."></br>
			<input type="button" id="calculate" name="calculate" value="Calculate">
		</form></br>
		<div id="result"></div> 
		<?php
	}
	
	}
	
	$distancewpcalculator = new DistanceWPCalculator();

?>


