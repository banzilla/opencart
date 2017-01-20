<?php
class ControllerExtensionPaymentBanzilla extends Controller {
	private $error = array();
	
	public function __construct($registry) {
		$this->registry = $registry;
		$registry->set('BanzillaController', new BanzillaController($registry));
	    }

	public function index() {
		$setting = $this->request->post;
		
		$this->load->language('extension/payment/banzilla');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
						
			$mode = $this->request->post['banzilla_sandbox'] ? 'test' : 'live';
			$apiKey = $this->request->post['banzilla_api_key'];
			$secretKey = $this->request->post['banzilla_secret_key'];
			$events = array('StorePaymentSuccess', 'TransferPaymentSuccess');
			
			foreach($events as $event){
				$webhook = $this->createWebhook($mode, $event, $apiKey, $secretKey);
				
				if($webhook != false){
					if(!$webhook->error && $webhook != false){
						$setting['banzilla_'.$mode.'_'.$event.'_webhook'] = $webhook->idwebhook;
					}
				}else{
					$setting['banzilla_'.$mode.'_'.$event.'_webhook'] = $this->config->get('banzilla_'.$mode.'_'.$event.'_webhook');
				}
			}
			
			$this->model_setting_setting->editSetting('banzilla', $setting);
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_authorization'] = $this->language->get('text_authorization');
		$data['text_sale'] = $this->language->get('text_sale');

		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_transaction'] = $this->language->get('entry_transaction');
		$data['entry_debug'] = $this->language->get('entry_debug');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_canceled_reversal_status'] = $this->language->get('entry_canceled_reversal_status');
		$data['entry_completed_status'] = $this->language->get('entry_completed_status');
		$data['entry_denied_status'] = $this->language->get('entry_denied_status');
		$data['entry_expired_status'] = $this->language->get('entry_expired_status');
		$data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$data['entry_processed_status'] = $this->language->get('entry_processed_status');
		$data['entry_refunded_status'] = $this->language->get('entry_refunded_status');
		$data['entry_reversed_status'] = $this->language->get('entry_reversed_status');
		$data['entry_voided_status'] = $this->language->get('entry_voided_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$data['entry_api_key'] = $this->language->get('entry_api_key');
		$data['entry_secret_key'] = $this->language->get('entry_secret_key');
		$data['entry_oxxo_deadline'] = $this->language->get('entry_oxxo_deadline');
		$data['entry_spei_deadline'] = $this->language->get('entry_spei_deadline');

		$data['help_test'] = $this->language->get('help_test');
		$data['help_debug'] = $this->language->get('help_debug');
		$data['help_total'] = $this->language->get('help_total');
		$data['help_email'] = $this->language->get('help_email');
		$data['help_secret_key'] = $this->language->get('help_secret_key');
		$data['help_api_key'] = $this->language->get('help_api_key');
		$data['help_oxxo_deadline'] = $this->language->get('help_oxxo_deadline');
		$data['help_spei_deadline'] = $this->language->get('help_spei_deadline');
		$data['help_geo_zone'] = $this->language->get('help_geo_zone');
		$data['help_status'] = $this->language->get('help_status');
		$data['help_sort_order'] = $this->language->get('help_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_order_status'] = $this->language->get('tab_order_status');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/banzilla', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/banzilla', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		if (isset($this->request->post['banzilla_email'])) {
			$data['banzilla_email'] = $this->request->post['banzilla_email'];
		} else {
			$data['banzilla_email'] = $this->config->get('banzilla_email');
		}
		
		if (isset($this->request->post['banzilla_api_key'])) {
			$data['banzilla_api_key'] = $this->request->post['banzilla_api_key'];
		} else {
			$data['banzilla_api_key'] = $this->config->get('banzilla_api_key');
		}
		
		if (isset($this->request->post['banzilla_secret_key'])) {
			$data['banzilla_secret_key'] = $this->request->post['banzilla_secret_key'];
		} else {
			$data['banzilla_secret_key'] = $this->config->get('banzilla_secret_key');
		}
		
		if (isset($this->request->post['banzilla_oxxo_deadline'])) {
			$data['banzilla_oxxo_deadline'] = $this->request->post['banzilla_oxxo_deadline'];
		} else {
			$data['banzilla_oxxo_deadline'] = $this->config->get('banzilla_oxxo_deadline');
		}
		
		if (isset($this->request->post['banzilla_spei_deadline'])) {
			$data['banzilla_spei_deadline'] = $this->request->post['banzilla_spei_deadline'];
		} else {
			$data['banzilla_spei_deadline'] = $this->config->get('banzilla_spei_deadline');
		}


		if (isset($this->request->post['banzilla_sandbox'])) {
			$data['banzilla_sandbox'] = $this->request->post['banzilla_sandbox'];
		} else {
			$data['banzilla_sandbox'] = $this->config->get('banzilla_sandbox');
		}

		if (isset($this->request->post['banzilla_transaction'])) {
			$data['banzilla_transaction'] = $this->request->post['banzilla_transaction'];
		} else {
			$data['banzilla_transaction'] = $this->config->get('banzilla_transaction');
		}

		if (isset($this->request->post['banzilla_debug'])) {
			$data['banzilla_debug'] = $this->request->post['banzilla_debug'];
		} else {
			$data['banzilla_debug'] = $this->config->get('banzilla_debug');
		}

		if (isset($this->request->post['banzilla_total'])) {
			$data['banzilla_total'] = $this->request->post['banzilla_total'];
		} else {
			$data['banzilla_total'] = $this->config->get('banzilla_total');
		}

		if (isset($this->request->post['banzilla_canceled_reversal_status_id'])) {
			$data['banzilla_canceled_reversal_status_id'] = $this->request->post['banzilla_canceled_reversal_status_id'];
		} else {
			$data['banzilla_canceled_reversal_status_id'] = $this->config->get('banzilla_canceled_reversal_status_id');
		}

		if (isset($this->request->post['banzilla_completed_status_id'])) {
			$data['banzilla_completed_status_id'] = $this->request->post['banzilla_completed_status_id'];
		} else {
			$data['banzilla_completed_status_id'] = $this->config->get('banzilla_completed_status_id');
		}

		if (isset($this->request->post['banzilla_denied_status_id'])) {
			$data['banzilla_denied_status_id'] = $this->request->post['banzilla_denied_status_id'];
		} else {
			$data['banzilla_denied_status_id'] = $this->config->get('banzilla_denied_status_id');
		}

		if (isset($this->request->post['banzilla_expired_status_id'])) {
			$data['banzilla_expired_status_id'] = $this->request->post['banzilla_expired_status_id'];
		} else {
			$data['banzilla_expired_status_id'] = $this->config->get('banzilla_expired_status_id');
		}

		if (isset($this->request->post['banzilla_failed_status_id'])) {
			$data['banzilla_failed_status_id'] = $this->request->post['banzilla_failed_status_id'];
		} else {
			$data['banzilla_failed_status_id'] = $this->config->get('banzilla_failed_status_id');
		}

		if (isset($this->request->post['banzilla_pending_status_id'])) {
			$data['banzilla_pending_status_id'] = $this->request->post['banzilla_pending_status_id'];
		} else {
			$data['banzilla_pending_status_id'] = $this->config->get('banzilla_pending_status_id');
		}

		if (isset($this->request->post['banzilla_processed_status_id'])) {
			$data['banzilla_processed_status_id'] = $this->request->post['banzilla_processed_status_id'];
		} else {
			$data['banzilla_processed_status_id'] = $this->config->get('banzilla_processed_status_id');
		}

		if (isset($this->request->post['banzilla_refunded_status_id'])) {
			$data['banzilla_refunded_status_id'] = $this->request->post['banzilla_refunded_status_id'];
		} else {
			$data['banzilla_refunded_status_id'] = $this->config->get('banzilla_refunded_status_id');
		}

		if (isset($this->request->post['banzilla_reversed_status_id'])) {
			$data['banzilla_reversed_status_id'] = $this->request->post['banzilla_reversed_status_id'];
		} else {
			$data['banzilla_reversed_status_id'] = $this->config->get('banzilla_reversed_status_id');
		}

		if (isset($this->request->post['banzilla_voided_status_id'])) {
			$data['banzilla_voided_status_id'] = $this->request->post['banzilla_voided_status_id'];
		} else {
			$data['banzilla_voided_status_id'] = $this->config->get('banzilla_voided_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['banzilla_geo_zone_id'])) {
			$data['banzilla_geo_zone_id'] = $this->request->post['banzilla_geo_zone_id'];
		} else {
			$data['banzilla_geo_zone_id'] = $this->config->get('banzilla_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['banzilla_status'])) {
			$data['banzilla_status'] = $this->request->post['banzilla_status'];
		} else {
			$data['banzilla_status'] = $this->config->get('banzilla_status');
		}

		if (isset($this->request->post['banzilla_sort_order'])) {
			$data['banzilla_sort_order'] = $this->request->post['banzilla_sort_order'];
		} else {
			$data['banzilla_sort_order'] = $this->config->get('banzilla_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/banzilla', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/banzilla')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['banzilla_email']) {
			$this->error['email'] = $this->language->get('error_email');
		}

		return !$this->error;
	}
	protected function createWebhook($mode, $event, $apiKey, $secretKey){
		
		if($event == 'StorePaymentSuccess'){
			$hook = 'Oxxo';
		}else if($event == 'TransferPaymentSuccess'){
			$hook = 'Spei';
		}
		
		if(strlen($this->config->get('banzilla_'.$mode.'_'.$event.'_webhook')) == 0){
		    $webhook_data = array(
			'Url' =>  HTTP_SERVER .'index.php?route=extension/payment/banzilla/webhook'.$hook,
			'Event' => $event,
			'Method'=>'POST',
		    );
		    
		    return $this->BanzillaController->createBanzillaWebhook($webhook_data, $apiKey, $secretKey, $mode);
		    
		}
		
		return false;
	}
}