<?php
require_once(dirname(__FILE__)."/ControlFraudeRetail.php");
require_once(dirname(__FILE__)."/ControlFraudeService.php");
require_once(dirname(__FILE__)."/ControlFraudeTicketing.php");
require_once(dirname(__FILE__)."/ControlFraudeDigitalgoods.php");

class ControlFraudeFactory {

	const RETAIL = "Retail";
	const SERVICE = "Service";
	const DIGITAL_GOODS = "Digital Goods";
	const TICKETING = "Ticketing";

	public static function get_controlfraude_extractor($vertical, $customer, $cart, $config){
		$instance;
		switch ($vertical) {
			case ControlFraudeFactory::RETAIL:
				$instance = new ControlFraudeRetail($customer, $cart, $config);
			break;
			
			case ControlFraudeFactory::SERVICE:
				$instance = new ControlFraudeService($customer, $cart, $config);
			break;
			
			case ControlFraudeFactory::DIGITAL_GOODS:
				$instance = new ControlFraudeDigitalgoods($customer, $cart, $config);
			break;
			
			case ControlFraudeFactory::TICKETING:
				$instance = new ControlFraudeTicketing($customer, $cart, $config);
			break;
			
			default:
				$instance = new ControlFraudeRetail($customer, $cart, $config);
			break;
		}
		return $instance;
	}
}