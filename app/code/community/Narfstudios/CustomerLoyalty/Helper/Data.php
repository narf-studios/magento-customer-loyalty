<?php
class Narfstudios_CustomerLoyalty_Helper_Data extends Mage_Checkout_Helper_Data
{
	/**
	 * This overwritten function send after the mail to the admin an email to the customer as well
	 * @author Manuel Neukum m.neukum@narf-studios.de
	 */
    public function sendPaymentFailedEmail($checkout, $message, $checkoutType = 'onepage')
    {
		// Send first the email to the admin
		parent::sendPaymentFailedEmail($checkout, $message, $checkoutType = 'onepage');
		
		// Load the HTML template from the Backend
		$template = Mage::getStoreConfig('checkout/payment_failed/template_customer', $checkout->getStoreId());
        
		
		// Check coupon configuration
		$isCoupon = Mage::getStoreConfig('checkout/payment_failed/coupon');
		Mage::log($isCoupon . '_'.Mage::getStoreConfig('checkout/payment_failed/coupon_value'), null, 'system.log');
		if($isCoupon === 1 || $isCoupon === '1') {
			$isCoupon = 1;
			// Generate Groupon code
			$coupon = $this->generateCouponCode($checkout->getCustomerLastname());
		}

		// no coupon or valid coupon otherwise error
		if($isCoupon !== 1 || (is_object($coupon) && $coupon !== false)) {
			// prepare variables for mail
		 	$array = array('reason' => $message,
	                        'dateAndTime' => Mage::app()->getLocale()->date(),
	                        'customer' => $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname(),
	                        'billingAddress' => $checkout->getBillingAddress(),
	                        'shippingAddress' => $checkout->getShippingAddress());
		 	
			// add the coupon variables
		 	if($isCoupon === 1) {
            	$array['couponValue'] = $coupon->getDiscountAmount();
				$array['couponCode'] = $coupon->getCouponCode();
				$array['currency'] = Mage::app()->getStore()-> getCurrentCurrencyCode(); 
			}
			
			$mailTemplate = Mage::getModel('core/email_template');
	        $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$checkout->getStoreId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig('checkout/payment_failed/identity', $checkout->getStoreId()),
                    $checkout->getCustomerEmail(),
                    $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname(),
                    $array
                );
        } else {
        Mage::log($isCoupon, null, 'system.log');
           Mage::log('No mail send to customer because coupon could not be initialized', null, 'system.log');  	
        }
	}

	/**
	 * Creates the coupon code if it is activated
	 * @author Manuel Neukum
	 */
	public function generateCouponCode($customername){
		$name = "Transaktion Coupon";
		$websiteId = 1;
		$actionType = 'cart_fixed'; // fixed discount for whole cart
		
		// generate random coupon code
		$code = 'T'.strtoupper(substr($customername, 0, 3)).rand(100000000, 999999999);
		
		// get value (default 5)
		$discount = Mage::getStoreConfig('checkout/payment_failed/coupon_value');
		if($discount <= 0) {
			$discount = 5;
		}
		
		// generate the groupon as sales rule
		$shoppingCartPriceRule = Mage::getModel('salesrule/rule');
		$shoppingCartPriceRule
		    ->setName($name)
		    ->setDescription('')
		    ->setIsActive(1)
		    ->setWebsiteIds(array($websiteId))
		    ->setCustomerGroupIds(array(0,1,2,3)) // for all valid
			->setCouponType(2) // specific Coupon
			->setCouponCode($code)
			->setUsesPerCoupon(1) // only one time valid
		    ->setFromDate('') // from now on
		    ->setSortOrder('')
		    ->setSimpleAction($actionType)
		    ->setDiscountAmount($discount)
		    ->setStopRulesProcessing(0);
		 
		 // Check if there is a end date for the coupon
		 $date = Mage::getStoreConfig('checkout/payment_failed/coupon_valid');
		 if($date) {
			$shoppingCartPriceRule->setToDate($date);
		 } else {
		 	$shoppingCartPriceRule->setToDate('');
		 }
		 
		try {
			// save the groupon code
		    $shoppingCartPriceRule->save();
			return $shoppingCartPriceRule;
		} catch (Exception $e) {
			Mage::log($e->getMessage(), null, 'system.log'); 
		    Mage::logException($e);
		    return false;
		}
	}
}
?>