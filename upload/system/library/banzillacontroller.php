<?php

//Banzilla All Controller
class BanzillaController extends MainController
{

    protected $rebilling_periods;
    protected $available_ps;
    protected $decimalZero;
    protected $stripePlans;

    public function __construct($registry)
    {

        parent::__construct($registry);

        $this->file = $this->sanitizePath(DIR_SYSTEM.'../vendor/banzilla/banzilla.php');
        //$minTotal = $this->currency->convert(1, 'USD', $this->currency->getCode());
    
        $this->decimalZero = array('BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',);

        
    }

    protected function getSecretKey()
    {
        if ($this->config->get('banzilla_secret_key')) {
            return $this->config->get('banzilla_secret_key');
        }
    }

    
    protected function isTestMode()
    {
        if ($this->config->get('banzilla_sandbox') == 1) {
            return true;
        } else {
            return false;
        }
    }

    protected function getApiKey()
    {
        if ($this->config->get('banzilla_api_key')) {
            return $this->config->get('banzilla_api_key');
        }
        
    }


    public function createChargeCard($chargeRequest)
    {
        $result = new stdClass();

        $file = $this->file;
        if (file_exists($file)) {
            require_once( $file );
        } else {
            $result->error = 'Banzilla library is missing';
            return $result;
        }

        $api_key = $this->getApiKey();
        $secret_key = $this->getSecretKey();
        $banzilla = Banzilla::getInstance($api_key, $secret_key, $this->isTestMode());


        try {
            $charge = $banzilla->charges->createCard($chargeRequest);

            return $charge;
        } catch (BanzillaAPITransactionError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIRequestError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIConnectionError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIAuthError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIError $e) {
            $result->error = $this->error($e);
        } catch (Exception $e) {
            $result->error = $this->error($e);
        }

        return $result;
    }
    
    public function createChargeOxxo($chargeRequest)
    {
        $result = new stdClass();

        $file = $this->file;
        if (file_exists($file)) {
            require_once( $file );
        } else {
            $result->error = 'Banzilla library is missing';
            return $result;
        }

        $api_key = $this->getApiKey();
        $secret_key = $this->getSecretKey();
        $banzilla = Banzilla::getInstance($api_key, $secret_key, $this->isTestMode());
        

        try {
            $charge = $banzilla->charges->createOxxo($chargeRequest);

            return $charge;
        } catch (BanzillaAPITransactionError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIRequestError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIConnectionError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIAuthError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIError $e) {
            $result->error = $this->error($e);
        } catch (Exception $e) {
            $result->error = $this->error($e);
        }

        return $result;
    }
    
    
    public function createChargeSpei($chargeRequest)
    {
        $result = new stdClass();

        $file = $this->file;
        if (file_exists($file)) {
            require_once( $file );
        } else {
            $result->error = 'Banzilla library is missing';
            return $result;
        }

        $api_key = $this->getApiKey();
        $secret_key = $this->getSecretKey();
        $banzilla = Banzilla::getInstance($api_key, $secret_key, $this->isTestMode());
        

        try {
            $charge = $banzilla->charges->createSpei($chargeRequest);

            return $charge;
        } catch (BanzillaAPITransactionError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIRequestError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIConnectionError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIAuthError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIError $e) {
            $result->error = $this->error($e);
        } catch (Exception $e) {
            $result->error = $this->error($e);
        }

        return $result;
    }
    

    public function createBanzillaWebhook($webhook_data, $apiKey, $secretKey, $mode)
    {

        $result = new stdClass();

        $file = $this->file;
        if (file_exists($file)) {
            require_once( $file );
        } else {
            $result->error = 'Banzilla library is missing';
            return $result;
        }
        
        if(!empty($this->getApiKey())){
            $api_key = $this->getApiKey();
        }else{
            $api_key = $apiKey;
        }
        
        if(!empty($this->getSecretKey())){
            $secret_key = $this->getSecretKey();
        }else{
            $secret_key = $secretKey;
        }
        
        
        $banzilla = Banzilla::getInstance($api_key, $secret_key, $mode);

        try {
            $webhook = $banzilla->webhookss->create($webhook_data);
            return $webhook;
        } catch (BanzillaAPITransactionError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIRequestError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIConnectionError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIAuthError $e) {
            $result->error = $this->error($e);
        } catch (BanzillaAPIError $e) {
            $result->error = $this->error($e);
        } catch (Exception $e) {
            $result->error = $this->error($e);
        }

        return $result;
    }


    public function error($e, $backend = false)
    {

        //6001 el webhook ya existe
            //echo '<pre> <br> entro a eeror y este es: <br>'; print_r($e); echo '</pre>'; exit();
        switch ($e->getErrorCode()) {

            //ERRORES GENERALES

            case "-1001":
                $msg = $e->getMessage();
                break;

            case "-1201":
                $msg = "Method not found.";
                break;

            case "-1202":
                $msg = "Initializing instrument error.";
                break;
            
            case "-1203":
                $msg = "Gateway not found.";
                break;
            
            case "-1204":
                $msg = "Instrument not found.";
                break;
            
            case "-1205":
                $msg = "Amex communication failure.";
                break;
            
            //ERRORES ALMACENAMIENTO
            case "-2001":
                $msg = "Unable tokenizing card, the card has expired.";
                break;
            
            case "-2002":
                $msg = "Card is already tokenized.";
                break;
            
            case "-2003":
                $msg = "The specified card does not exist or you are not allowed to see its detail.";
                break;
            
            case "-2004":
                $msg = "ThereÕs no one card tokenized.";
                break;

            case "-2005":
                $msg = "You must provide the card token.";
                break;

            case "-2006":
                $msg = "CardNumber Card number is invalid.";
                break;
            
            case "-2008":
                $msg = "Card not found in Blacklist.";
                break;

            //ERRORES TARJETA
            case "-3001":
                $msg = "Invalid card";
                break;

            case "-3002":
                $msg = "La tarjeta ha expirado.";
                break;

            case "-3003":
                $msg = "La tarjeta no tiene fondos suficientes.";
                break;

            case "3004":
                $msg = "La tarjeta fue rechazada.";
                break;

            case "3005":
                $msg = "La tarjeta fue rechazada.";
                break;

            case "3006":
                $msg = "La operaci—n no esta permitida para este cliente o esta transacci—n.";
                break;

            case "3007":
                $msg = "Deprecado. La tarjeta fue declinada.";
                break;

            case "3008":
                $msg = "La tarjeta no es soportada en transacciones en l’nea.";
                break;

            case "3009":
                $msg = "La tarjeta fue reportada como perdida.";
                break;

            case "3010":
                $msg = "El banco ha restringido la tarjeta.";
                break;

            case "3011":
                $msg = "El banco ha solicitado que la tarjeta sea retenida. Contacte al banco.";
                break;

            case "3012":
                $msg = "Se requiere solicitar al banco autorizaci—n para realizar este pago.";
                break;

            case "6002":
                $msg = "Ha ocurrido un error al crear el webhook. Verifica en tu panel de banzilla que este haya sido creado, es necesario instalarlo para recibir notificaciones de pago.";
                break;

            default: //Dem‡s errores 400
                $msg = "La petici—n no pudo ser procesada.";
                break;
        }
        
        $error = 'ERROR '.$e->getErrorCode().'. '.$msg;
        return $error;
    }

    public function getLongGlobalDateFormat($input)
    {
        $time = strtotime($input);

        $string_month = $this->getLongStringForMonth(date('n', $time));

        // Formato "12 de Julio de 2014, a las 6:36 PM"
        return date('j', $time).' de '.$string_month.' de '.date('Y', $time).', a las '.date('g:i A', $time);
    }

    public function getLongStringForMonth($month_number)
    {
        $months_array = array(
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        );

        return isset($months_array[$month_number]) ? $months_array[$month_number] : '';
    }

}

?>
