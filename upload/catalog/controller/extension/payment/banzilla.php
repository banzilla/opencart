<?php
class ControllerExtensionPaymentBanzilla extends BanzillaController {
	public function index() {
	
	$this->load->language('extension/payment/banzilla');
		
		$data['text_credit_card'] = $this->language->get('text_credit_card');
		$data['text_wait'] = $this->language->get('text_wait');
	
		$data['help_cvc_front'] = $this->language->get('help_cvc_front');
		$data['help_cvc_back'] = $this->language->get('help_cvc_back');
	
		$data['entry_cc_holder_name'] = $this->language->get('entry_cc_holder_name');
		$data['entry_cc_number'] = $this->language->get('entry_cc_number');
		$data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
		$data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');
		$data['error_error'] = $this->language->get('error_error');
		$data['text_success_payment'] = $this->language->get('text_success_payment');
	
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['button_back'] = $this->language->get('button_back');
		$data['confirmOxxo'] = $this->url->link('extension/payment/banzilla/confirmOxxo');
		$data['confirmSpei'] = $this->url->link('extension/payment/banzilla/confirmSpei');
	
		/*$data['merchant_id'] = $this->getMerchantId();
		$data['public_key'] = $this->getPublicApiKey();
		$data['test_mode'] = $this->isTestMode();*/

		$data['testmode'] = $this->config->get('Banzilla_test');

		$data['action'] = $this->url->link('extension/payment/banzilla/confirm', '', '');
		
		
		$data['months'] = array();

		$now = new dateTime( '2000-01-01' );
		for( $i = $now->format( 'n' ) , $interval = new DateInterval( 'P1M' ); $i <= 12 ; $i++ , $now->add( $interval ) ){
			$data['months'][] = array(
				'text'  => $now->format( 'm' ),
				'value' => $now->format( 'm' ),
			);
		}
	
		$data['year_expire'] = array();
	
		$now = new dateTime;
		for( $i = $now->format( 'y' ) , $interval = new DateInterval( 'P1Y' ) , $stop = $i + 10 ; $i <= $stop ; $i++ , $now->add( $interval ) ){
			$data['year_expire'][] = array(
				'text'  => $now->format( 'y' ),
				'value' => $now->format( 'y' ),
			);
		}
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {
			$data['business'] = $this->config->get('pp_standard_email');
			$data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
						
						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$data['products'][] = array(
					'name'     => htmlspecialchars($product['name']),
					'model'    => htmlspecialchars($product['model']),
					'price'    => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
					'quantity' => $product['quantity'],
					'option'   => $option_data,
					'weight'   => $product['weight']
				);
			}

			$data['discount_amount_cart'] = 0;

			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

			if ($total > 0) {
				$data['products'][] = array(
					'name'     => $this->language->get('text_total'),
					'model'    => '',
					'price'    => $total,
					'quantity' => 1,
					'option'   => array(),
					'weight'   => 0
				);
			} else {
				$data['discount_amount_cart'] -= $total;
			}

			$data['currency_code'] = $order_info['currency_code'];
			$data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
			$data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
			$data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$data['country'] = $order_info['payment_iso_code_2'];
			$data['email'] = $order_info['email'];
			$data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['lc'] = $this->session->data['language'];
			$data['return'] = $this->url->link('checkout/success');
			$data['notify_url'] = $this->url->link('extension/payment/pp_standard/callback', '', true);
			$data['cancel_return'] = $this->url->link('checkout/checkout', '', true);

			if (!$this->config->get('pp_standard_transaction')) {
				$data['paymentaction'] = 'authorization';
			} else {
				$data['paymentaction'] = 'sale';
			}

			$data['custom'] = $this->session->data['order_id'];

			return $this->load->view('extension/payment/banzilla', $data);
		}
		
    }

    public function confirm(){
        $json = array();
	
       

        if (empty($this->session->data['order_id']))
        {
            $json['error'] = 'Missing order ID';
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model('checkout/order');
        $this->language->load('extension/payment/banzilla');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($this->currency->convert($order_info['total'], $order_info['currency_code'], $this->config->get('sp_total_currency')) < (float) $this->config->get('sp_total'))
        {
            $json['error'] = $this->language->get('error_min_total');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $amount = round($order_info['total'], 2);
	
	$streetArray =  explode(' ', $order_info['payment_address_1']);
	$number = array_pop($streetArray);
	$street = implode(' ', $streetArray);
	
	$card = array (
                "HolderName"   =>  $this->request->post['HolderName'],
                "CardNumber"   => $this->request->post['CardNumber'],
                "SecurityCode" => $this->request->post['SecurityCode'],
                "ExpMonth"     => $this->request->post['cc_expire_date_month'],
                "ExpYear"      => $this->request->post['cc_expire_date_year'],
                'Address' => array (
			'Street' => $street,
			'Number' => $number,
			'City'   => $order_info['payment_city'],
			'State'  => $order_info['payment_zone_code'],
			'Country'=> $order_info['payment_iso_code_3'],
			'ZipCode'=> $order_info['payment_postcode']
			)
                );
	
	$order = array(
		"Reference" => $order_info['order_id'],
		"Amount"    => $amount,
		"Currency"  => $order_info['currency_code']
		);

	$customer = array(
	    'FirstName' => $order_info['payment_firstname'],
	    'MiddleName' => $order_info['payment_lastname'],
	    'Email' => $order_info['email'],
	    'Address' => array (
                'Street' => $street,
                'Number' => $number,
                'City'   => $order_info['payment_city'],
                'State'  => $order_info['payment_zone_code'],
		'Country'=> $order_info['payment_iso_code_3'],
                'ZipCode'=> $order_info['payment_postcode']
		)
	);
	    

        $charge_request = array(
            'Card' => $card,
            'Order' => $order,
            'Customer' => $customer
        );
	
        $chargeCard = $this->createChargeCard($charge_request);
	
        if (isset($chargeCard->error)) {
            $json['error'] = $chargeCard->error;
            $this->response->setOutput(json_encode($json));
            return;
        } else {
		
		if ($chargeCard->status == 'denied'){
			$json['error'] = 'Denied charge';
			$this->response->setOutput(json_encode($json));
			return;
		}
		
		$response = '<p>Id transaction: ' . $chargeCard->idtransaction . '</p>' . '<p>Creation date: ' . $chargeCard->creationdate . '</p>' .'<p>Id request: ' . $chargeCard->idrequest . '</p>' . '<p>Method: ' . $chargeCard->method . '</p>' .'<p>Gateway: ' . $chargeCard->gateway . '</p>' .'<p>Status: ' . $chargeCard->status . '</p>' .'<p>Card Payment: ' . $chargeCard->cardpayment->BrandCard . '</p>' .'<p>Order: ' . $chargeCard->order->Reference . '</p>';

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('banzilla_completed_status_id'), $response);

            $this->debugLog->write("Order #" . $chargeCard->order_id . " confirmed");
        }

        $json['success'] = $this->url->link('checkout/success', '');
        $this->response->setOutput(json_encode($json));
    }
    
    
    public function confirmOxxo() {

        $this->document->addScript('catalog/view/javascript/jquery/jquery-2.1.1.min.js');
        $this->document->addStyle('catalog/view/javascript/bootstrap/css/bootstrap.min.css');

        if ($this->session->data['payment_method']['code'] == 'banzilla') {

		
            $this->document->setTitle('Imprimir Recibo de Pago');

            $json = array();

            $this->load->model('checkout/order');
            $this->language->load('extension/payment/banzilla');

	    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

                
                if ($this->currency->convert($order_info['total'], $order_info['currency_code'], $this->config->get('sp_total_currency')) < (float) $this->config->get('sp_total')) {
                    $json['error'] = $this->language->get('error_min_total');
                    $this->response->setOutput(json_encode($json));
                    return;
                }

                $amount = round($order_info['total'], 2);
		
                $deadline = $this->config->get('banzilla_oxxo_deadline');

                if($deadline > 0){
                    $due_date = date('m/d/Y', strtotime('+' . $deadline . ' days'));
                }else{
                    $due_date = date('m/d/Y', strtotime('+10 days'));
                }
		
		
		
		$order = array(
		"Reference" => $order_info['order_id'],
		"Amount"    => $amount,
		"Currency"  => $order_info['currency_code']
		);
		
		$streetArray =  explode(' ', $order_info['payment_address_1']);
		$number = array_pop($streetArray);
		$street = implode(' ', $streetArray);
        
		$customer = array(
		    'FirstName' => $order_info['payment_firstname'],
		    'MiddleName' => $order_info['payment_lastname'],
		    'Email' => $order_info['email'],
		    'Address' => array (
			'Street' => $street,
			'Number' => $number,
			'City'   => $order_info['payment_city'],
			'State'  => $order_info['payment_zone_code'],
			'Country'=> $order_info['payment_iso_code_3'],
			'ZipCode'=> $order_info['payment_postcode']
			)
		);
		
		$charge_request = array(
		     'DueDate' => $due_date,
		     'Order' => $order,
		     'Customer' => $customer
		);
		
                $chargeOxxo = $this->createChargeOxxo($charge_request);

		if (isset($chargeOxxo->error)) {
			$json['error'] = $chargeOxxo->error;
			$this->response->setOutput(json_encode($json));
			return;
		} else {
			    
			$response = '<p>Id transaction: ' . $chargeOxxo->idtransaction . '</p>' . '<p>Creation date: ' . $chargeOxxo->creationdate . '</p>' .'<p>Id request: ' . $chargeOxxo->idrequest . '</p>' . '<p>Method: ' . $chargeOxxo->method . '</p>' .'<p>Gateway: ' . $chargeOxxo->gateway . '</p>' .'<p>Status: ' . $chargeOxxo->status . '</p>' .'<p>Bar Code: ' . $chargeOxxo->storepayment->BarCode . '</p>' .'<p>Url Bar Code: <a href="' . $chargeOxxo->storepayment->UrlDocument . '">' . $chargeOxxo->storepayment->UrlDocument .'</a></p>';

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('banzilla_pending_status_id'), $response, true);
	    
			$this->debugLog->write("Order #" . $chargeOxxo->order_id . " confirmed");
		}
		

                $this->clearCart();

            

            $data['barcode_url'] = $chargeOxxo->storepayment->UrlDocument;
            $data['reference'] = $chargeOxxo->storepayment->BarCode;
            $data['due_date'] = $this->getLongGlobalDateFormat($chargeOxxo->storepayment->DueDate);
            $data['creation_date'] = $chargeOxxo->creationDate;
            $data['currency'] = $chargeOxxo->order->Currency;
            $data['amount'] = number_format($chargeOxxo->order->Amount, 2);
            $data['order_id'] = $chargeOxxo->Reference;
            $data['email'] = $this->customer->getEmail();
            $data['logo'] = $this->config->get('config_ssl') . 'image/' . $this->config->get('config_logo');

            $this->load->language('checkout/success');

            $data['continue'] = $this->url->link('common/home');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/banzilla_oxxo_receipt.tpl')) {
		$json['success'] = $this->load->view($this->config->get('config_template') . '/extension/payment/banzilla_oxxo_receipt.tpl', $data);
                $this->response->setOutput(json_encode($json));
            } else {
		$json['success'] = $this->load->view('extension/payment/banzilla_oxxo_receipt.tpl', $data);
                $this->response->setOutput(json_encode($json));
            }
        }else{
            header('Location: '.$this->url->link('common/home', '', 'SSL'));
        }
    }
    
    
    public function confirmSpei() {

        $this->document->addScript('catalog/view/javascript/jquery/jquery-2.1.1.min.js');
        $this->document->addStyle('catalog/view/javascript/bootstrap/css/bootstrap.min.css');

        if ($this->session->data['payment_method']['code'] == 'banzilla') {

		
            $this->document->setTitle('Imprimir Recibo de Pago');

            $json = array();

            $this->load->model('checkout/order');
            $this->language->load('extension/payment/banzilla');

	    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

                
                if ($this->currency->convert($order_info['total'], $order_info['currency_code'], $this->config->get('sp_total_currency')) < (float) $this->config->get('sp_total')) {
                    $json['error'] = $this->language->get('error_min_total');
                    $this->response->setOutput(json_encode($json));
                    return;
                }

                $amount = round($order_info['total'], 2);
		
                $deadline = $this->config->get('banzilla_spei_deadline');

                if($deadline > 0){
                    $due_date = date('m/d/Y', strtotime('+' . $deadline . ' days'));
                }else{
                    $due_date = date('m/d/Y', strtotime('+10 days'));
                }
		
		
		
		$order = array(
		"Reference" => $order_info['order_id'],
		"Amount"    => $amount,
		"Currency"  => $order_info['currency_code']
		);
		
		$streetArray =  explode(' ', $order_info['payment_address_1']);
		$number = array_pop($streetArray);
		$street = implode(' ', $streetArray);
        
		$customer = array(
		    'FirstName' => $order_info['payment_firstname'],
		    'MiddleName' => $order_info['payment_lastname'],
		    'Email' => $order_info['email'],
		    'Address' => array (
			'Street' => $street,
			'Number' => $number,
			'City'   => $order_info['payment_city'],
			'State'  => $order_info['payment_zone_code'],
			'Country'=> $order_info['payment_iso_code_3'],
			'ZipCode'=> $order_info['payment_postcode']
			)
		);
		
		$charge_request = array(
		     'DueDate' => $due_date,
		     'Order' => $order,
		     'Customer' => $customer
		);

                $chargeSpei= $this->createChargeSpei($charge_request);

		if (isset($chargeSpei->error)) {
			$json['error'] = $chargeSpei->error;
			$this->response->setOutput(json_encode($json));
			return;
		} else {
			    
			$response = '<p>Id transaction: ' . $chargeSpei->idtransaction . '</p>' . '<p>Creation date: ' . $chargeSpei->creationdate . '</p>' .'<p>Id request: ' . $chargeSpei->idrequest . '</p>' . '<p>Method: ' . $chargeSpei->method . '</p>' .'<p>Gateway: ' . $chargeSpei->gateway . '</p>' .'<p>Status: ' . $chargeSpei->status . '</p>'.'<p>Referece: ' . $chargeSpei->transferpayment->Reference . '</p>' .'<p>CLABE: ' . $chargeSpei->transferpayment->Clabe . '</p>' .'<p>Url CLABE: <a href="' . $chargeSpei->transferpayment->UrlDocument . '">' . $chargeSpei->transferpayment->UrlDocument .'</a></p>';

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('banzilla_pending_status_id'), $response, true);
	    
			$this->debugLog->write("Order #" . $chargeSpei->order_id . " confirmed");
		}
		

                $this->clearCart();

            $data['clabeUrl'] = $chargeSpei->transferpayment->UrlDocument;
	    $data['name'] = $chargeSpei->Method . ' ' . $chargeSpei->Gateway;
            $data['clabe'] = $chargeSpei->transferpayment->Clabe;
            $data['due_date'] = $this->getLongGlobalDateFormat($chargeSpei->transferpayment->DueDate);
            $data['creation_date'] = $chargeSpei->creationDate;
            $data['currency'] = $chargeSpei->order->Currency;
            $data['amount'] = number_format($chargeSpei->order->Amount, 2);
            $data['order_id'] = $chargeSpei->Reference;
	    $data['reference'] = $chargeSpei->transferpayment->Reference;
            $data['email'] = $this->customer->getEmail();
	    $data['store_email'] = $this->config->get('config_email');
            $data['store_name'] = $chargeSpei->transferpayment->Bank;
            $data['logo'] = $this->config->get('config_ssl') . 'image/' . $this->config->get('config_logo');

            $this->load->language('checkout/success');

            $data['continue'] = $this->url->link('common/home');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/banzilla_spei_receipt.tpl')) {
                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/extension/payment/banzilla_spei_receipt.tpl', $data));
            } else {
                $this->response->setOutput($this->load->view('extension/payment/banzilla_spei_receipt.tpl', $data));
            }
        }else{
            header('Location: '.$this->url->link('common/home', '', 'SSL'));
        }
    }
    

    public function clearCart() {

        if (isset($this->session->data['order_id'])) {
            $this->cart->clear();

            // Add to activity log
            $this->load->model('account/activity');

            if ($this->customer->isLogged()) {
                $activity_data = array(
                    'customer_id' => $this->customer->getId(),
                    'name' => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
                    'order_id' => $this->session->data['order_id']
                );

                $this->model_account_activity->addActivity('order_account', $activity_data);
            } else {
                $activity_data = array(
                    'name' => $this->session->data['guest']['firstname'] . ' ' . $this->session->data['guest']['lastname'],
                    'order_id' => $this->session->data['order_id']
                );

                $this->model_account_activity->addActivity('order_guest', $activity_data);
            }

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            //unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['totals']);
        }

        return;

    }

    public function webhookOxxo(){
        $objeto = file_get_contents('php://input');
        $json = json_decode($objeto);

        if(!count($json)>0)
            return true;

        if ($json->type == 'charge.succeeded' && $json->transaction->method == 'store') {
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($json->transaction->order_id, $this->config->get('banzilla_completed_status_id'), '', true);
        }
    }
    public function webhookSpei(){
        $objeto = file_get_contents('php://input');
        $json = json_decode($objeto);

        if(!count($json)>0)
            return true;

        if ($json->type == 'charge.succeeded' && $json->transaction->method == 'store') {
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($json->transaction->order_id, $this->config->get('banzilla_completed_status_id'), '', true);
        }
    }
}