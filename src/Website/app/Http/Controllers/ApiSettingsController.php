<?php namespace RaspiSurveillance\Http\Controllers;

use RaspiSurveillance\Http\Requests;

use Illuminate\Http\Request;
use Input;
use Response;
use Exception;

use RaspiSurveillance\Settings;

/**
* REST API controller for settings.
*/
class ApiSettingsController extends ApiControllerBase {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		try {
			// Get properties
			$cameraMode = self::getCameraMode();
			
			// Instantiate object
			$settings = new Settings();
			$settings->camera_mode = $cameraMode;
			
			return self::JsonEncode($settings);
		}
		catch (Exception $exception) {
			return Response($exception, 500);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		try {
			$input = Input::json();

			$settings = new Settings();
			$settings->camera_mode = $input->get('camera')['mode'];

			// Validate input
			$validator = $settings->getValidator();
			if ($validator->fails()) {
				return Response($validator->messages(), 400);
			}

			// Save properties
			self::saveCameraMode($settings->camera_mode);
		}
		catch (Exception $exception) {
			return Response($exception, 500);
		}
		
		return Response("", 200);
	}


	// Methods
	protected static function JsonEncode($settings) {
		return array(
			'camera' => array(
				'mode' => $settings->camera_mode)
		);
	}


	// TODO: Test
	protected static function getCameraMode() {
		if (strpos(strtolower(self::getVideostreamStatus()), 'is running') !== FALSE) {
			// Videostream is running
			return 1;
		}
		else if (strpos(strtolower(self::getMotionDetectionStatus()), 'is running') !== FALSE) {
			// Motion detection is running
			return 2;
		}
		
		// Neither videostream nor motion detection is running
		return 0;
	}

	protected static function saveCameraMode($mode) {
		switch ($mode) {
			case 0:
				self::stopVideostream();
				self::stopMotionDetection();

				break;
			case 1:
				self::stopMotionDetection();
				self::startVideostream();
				
				break;
			case 2:
				self::stopVideostream();
				self::startMotionDetection();

				break;
			default:
				throw new Exception('Unknown camera mode (' . $mode . ')');
		}
	}


	protected static function executeScript($path) {
		$file = realpath($path);

		if (!file_exists($file)) {
			throw new Exception('Script does not exist: ' . $file);
		}

		$output = array();
		$status = -1;
		exec($file, $output, $status);

		if ($status !== 0) {
			throw new Exception('Error executing script "' . $file . '" (' . $status . '): ' . implode($output));
		}

		return end($output);
	}

	protected static function startVideostream() {
		self::executeScript('../../scripts/videostream-start-http.sh');
	}

	protected static function stopVideostream() {
		self::executeScript('../../scripts/videostream-stop.sh');
	}
	
	protected static function getVideostreamStatus() {
		return self::executeScript('../../scripts/videostream-status.sh');
	}

	protected static function startMotionDetection() {
		self::executeScript('../../scripts/motion-detection-start.sh');
	}

	protected static function stopMotionDetection() {
		self::executeScript('../../scripts/motion-detection-stop.sh');
	}
	
	protected static function getMotionDetectionStatus() {
		return self::executeScript('../../scripts/motion-detection-status.sh');
	}

}