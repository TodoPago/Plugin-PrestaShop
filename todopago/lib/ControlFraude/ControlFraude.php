<?php

require_once(dirname(__FILE__)."/../TodoPago/lib/Sdk.php");

abstract class ControlFraude {
  
	protected $datasources = array();
	
	public function __construct($customer = array(), $cart = array(), $config = array()){
		$this->datasources = array("cart" => $cart, "customer" => $customer, "config" => $config);
		
 		$address = $this->datasources['cart']->id_address_delivery;	
        $address = new Address($address);
		$country = new Country($address->id_country);
        $validOrders = Db::getInstance()->getValue('SELECT COUNT(`'.Order::$definition['primary'].'`) FROM '._DB_PREFIX_.Order::$definition['table'].' WHERE id_customer = '.$this->datasources['customer']->id.' AND valid = 1');
       	$extra = array("total" => $this->datasources['cart']->getOrderTotal(true, Cart::BOTH), "validOrders" => $validOrders, "ip" => Tools::getRemoteAddr(), "moneda" => "ARS");
		
		$this->datasources['address'] = $address;
		$this->datasources['country'] = $country;
		$this->datasources['extra'] = $extra;
	}
	
	public function getDataCS(){
		$datosCS = $this->completeCS();
		return array_merge($datosCS, $this->completeCSVertical());
	}	

	protected function completeCS(){
		$datosCS = array();
		$datosCS["CSBTCITY"] 			= substr($this->getField($this->datasources['address'],"city"),0,250);
		$datosCS["CSBTCOUNTRY"] 		= $this->getField($this->datasources['country'],"iso_code");
		$datosCS["CSBTCUSTOMERID"] 		= $this->getField($this->datasources['customer'],"id");
		$datosCS["CSBTIPADDRESS"] 		= $this->getField($this->datasources['extra'],"ip");
		$datosCS["CSBTEMAIL"] 			= $this->getField($this->datasources['customer'],"email");
		$datosCS["CSBTFIRSTNAME"] 		= $this->getField($this->datasources['customer'],"firstname");
		$datosCS["CSBTLASTNAME"] 		= $this->getField($this->datasources['customer'],"lastname");
		$datosCS["CSBTPHONENUMBER"] 	= $this->_getPhone($this->datasources,false);
		$datosCS["CSBTPOSTALCODE"] 		= $this->getField($this->datasources['address'],"postcode");
		$datosCS["CSBTSTATE"] 			= $this->_getStateIso($this->getField($this->datasources['address'],"id_state"));
		$datosCS["CSBTSTREET1"] 		= $this->getField($this->datasources['address'],"address1");
		$datosCS["CSPTCURRENCY"] 		= $this->getField($this->datasources['extra'],"moneda");
		$datosCS["CSPTGRANDTOTALAMOUNT"]= number_format($this->getField($this->datasources['extra'],"total"),2,".","");
		$datosCS["CSMDD7"] 				= $this->_getDateTimeDiff($this->getField($this->datasources['customer'],"date_add"));
		$datosCS["CSMDD10"] 			= $this->getField($this->datasources['extra'],"validOrders");
		$datosCS["CSMDD11"] 			= $this->_getPhone($this->datasources,true);
		
        if((bool)$this->getField($this->datasources['customer'],"is_guest"))
            $datosCS['CSMDD8'] = 'S';
        else {
			$datosCS['CSMDD8']= 'N';
            $datosCS['CSMDD9'] = $this->getField($this->datasources['customer'],"passwd");
        }
		
		return $datosCS;
	}
  
	protected abstract function completeCSVertical();
	protected abstract function getCategoryArray($productId);
	
	protected function getMultipleProductsInfo(){
		$productos = $this->datasources["cart"]->getProducts();
		
        $code =array();
        $description = array();
        $name = array();
        $sku = array();
        $total = array();
        $quantity = array();
        $unit = array();

        foreach ($productos as $item) {
            $code[]  = $this->getCategoryArray($item['id_product']);
            $desc = TodoPago\Sdk::sanitizeValue($item['description_short']);
            $desc = substr($desc,0,50);
            $description[]   = $desc;
            
            $name[]  = substr($item['name'],0,250);
            $sku[]  = substr($item['reference'],0,250);
            $total[]  = number_format($item['total_wt'],2,".","");
            $quantity[]  = $item['cart_quantity'];
            $unit[]  = number_format($item['price_wt'],2,".","");
        }
		
		$productsData = array (
            'CSITPRODUCTCODE' => join("#", $code),
            'CSITPRODUCTDESCRIPTION' => join("#", $description),
            'CSITPRODUCTNAME' => join("#", $name),
            'CSITPRODUCTSKU' => join("#", $sku),
            'CSITTOTALAMOUNT' => join("#", $total),
            'CSITQUANTITY' => join("#", $quantity),
            'CSITUNITPRICE' => join("#", $unit),
        );
	
		return $productsData;
	}
	
	protected function _getPhone($datasources, $mobile = false){
		if($mobile) {
			$data = $this->getField($datasources['address'],"phone_mobile");
			if (empty($data)) {
					return $this->_phoneSanitize($this->getField($datasources['address'],"phone"));
			}
			return $this->_phoneSanitize($this->getField($datasources['address'],"phone_mobile"));
		}
		$data = $this->getField($datasources['address'],"phone");
		if(empty($data)){
			return $this->_phoneSanitize($this->getField($datasources['address'],"phone_mobile"));
		}
		return $this->_phoneSanitize($this->getField($datasources['address'],"phone"));
	}
	
	protected function getField($datasource, $key){
		$return = "";
		try{
			if(is_array($datasource))
				$return = $datasource[$key];
			elseif(property_exists($datasource,$key))
				$return = $datasource->$key;
			else
				throw new Exception("No encontrado");
		}catch(Exception $e){
			$this->log("a ocurrido un error en el campo ". $key. " se toma el valor por defecto");
		}
		return $return;
	}

	protected function log($mensaje)
	{
		$nombre = 'CSlog';
		
		$archivo = fopen(dirname(__FILE__).'/../'.$nombre.'.txt', 'a+');
		fwrite($archivo, date('Y/m/d - H:i:s').' - '.$mensaje . PHP_EOL);
		fclose($archivo);
	}

	protected function _phoneSanitize($number){
		$number = str_replace(array(" ","(",")","-","+"),"",$number);
		
		if(substr($number,0,2)=="54") return $number;
		
		if(substr($number,0,2)=="15"){
			$number = substr($number,2,strlen($number));
		}
		if(strlen($number)==8) return "5411".$number;
		
		if(substr($number,0,1)=="0") return "54".substr($number,1,strlen($number));
		return "54".$number;
	}

    protected function _getStateIso($id)
    {
        $state = new State($id);
        return $state->iso_code;
    }
	
    protected function _getDateTimeDiff($fecha)
    {
        return date_diff(new DateTime($fecha), new DateTime())->format('%a');
    }
}
