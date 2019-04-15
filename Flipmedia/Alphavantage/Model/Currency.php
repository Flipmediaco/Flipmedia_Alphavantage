<?php
/**
* Flipmedia
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com so we can send you a copy immediately.
*
* @category   Flipmedia
* @package    Flipmedia
* @copyright  Copyright (c) 2019 LSB
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
 
/**
* Currency rate import model (From google.com)
*
* @category   Flipmedia
* @package    Flipmedia
* @author     Flipmedia Collaboration
*/
class Flipmedia_Alphavantage_Model_Currency extends Mage_Directory_Model_Currency_Import_Abstract
{    
    protected $_url = 'https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency={{CURRENCY_FROM}}&to_currency={{CURRENCY_TO}}&apikey={{APIKEY}}';
    
    protected $_messages = array();
 
    protected function _convert($currencyFrom, $currencyTo, $retry=0)
    {
		$url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);
        $url = str_replace('{{APIKEY}}', Mage::getStoreConfig('alphavantage/general/apikey'), $url);

        try {
            sleep(1); // Lets play nice...
			
			$ch = curl_init();

			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			// grab URL and pass it to the browser
			$res = curl_exec($ch);
			curl_close($ch);
			
			// Decode json response
			$result = json_decode($res, true);
			
			// Set exchange_rate from result
			$exchange_rate = $result['Realtime Currency Exchange Rate']['5. Exchange Rate'];
			
			if( !$exchange_rate ) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $this->_url);
                return null;
            }
			
            return (float) $exchange_rate * 1.0; // change 1.0 to influence rate;
        }
        catch (Exception $e) {
            if( $retry == 0 ) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
            }
        }
    }
}