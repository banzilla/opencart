<?php if ($testmode) { ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $text_testmode; ?></div>
<?php } ?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/stylesheet/banzilla.css">


<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#cardsTab"><?php echo $text_credit_card; ?></a></li>
  <li><a data-toggle="tab" href="#oxxoTab">OXXO</a></li>
  <li><a data-toggle="tab" href="#speiTab">SPEI</a></li>
</ul

<div class="tab-content">
  <div id="cardsTab" class="tab-pane fade in active">
    <form action="<?php echo $action ?>" method="POST" id="payment-form" class="form-horizontal">
    
        <div class="content" id="payment">
            <div class="row" id="header">
                <div class="col-sm-6">
                    <h2><?php echo $text_credit_card; ?></h2>
                </div>
            </div>
    
            <div class="row mb20 mt20">
                <div class="col-md-6">
                    <h3>Credit Cards</h3>
                    <div class="row">
                    <?php for($i=1;$i<=4;$i++): ?>
                        <div class="col-md-2">
                            <img src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/credit_cards/<?php echo sprintf('%02d', $i) ?>.png" alt="Tarjetas" class="tiendas">
                        </div>
                    <?php endfor; ?>
                    </div>
                </div>
                
            </div>
    
            <div id="msgBoxCard" role="alert"><i></i><span style="margin-left:10px;"></span></div>
    
            <div class="form-group">
                <label class="col-sm-2 control-label" for='cc-owner'>
                    <?php echo $entry_cc_holder_name; ?>
                </label>
                <div class="col-sm-4">
                    <input type="text" name="HolderName" id="cc-owner" class="form-control" data-banzilla-card="holder_name" autocomplete="off" >
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for='cc-number'>
                    <?php echo $entry_cc_number; ?>
                </label>
                <div class="col-sm-4">
                    <input type="text" name="CardNumber" id="cc-number" class="form-control" data-banzilla-card="card_number" autocomplete="off" >
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="cc-month">
                    <?php echo $entry_cc_expire_date; ?>
                </label>
                <div class="col-sm-2">
                    <select id="cc-month" name="cc_expire_date_month" data-banzilla-card="expiration_month" class="form-control">
                        <?php foreach ($months as $month) : ?>
                        <option value="<?php echo $month['value']; ?>"><?php echo $month['text']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-2">
                    <select id="cc-year" name="cc_expire_date_year" data-banzilla-card="expiration_year" class="form-control">
                        <?php foreach ($year_expire as $year) : ?>
                        <option value="<?php echo $year['value']; ?>"><?php echo $year['text']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="cc-cvv">
                    <?php echo $entry_cc_cvv2; ?>
                </label>
                <div class="col-sm-2">
                    <input id="cc-cvv" type="text" name="SecurityCode" autocomplete="off" data-banzilla-card="cvv2" class="form-control" >
                </div>
                <div class="col-sm-2">
                    <img data-toggle="popover" data-content="<?php echo $help_cvc_back; ?>" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/cvc_back.png" alt="Tarjetas" class="cvv" style="cursor:pointer;">
                    <img data-toggle="popover" data-content="<?php echo $help_cvc_front; ?>" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/cvc_front.png" alt="Tarjetas" class="cvv" style="cursor:pointer;">
                </div>
            </div>
            
            <div class="pull-right">
                <button type="button" class="btn btn-primary" id="button-confirm" data-loading-text="Processing"><?php echo $button_confirm; ?></button>
            </div>
            
        </div>
    </form>
  </div>
  
  <div id="oxxoTab" class="tab-pane fade">
  
    <div id="msgBoxOxxo" role="alert"><i></i><span style="margin-left:10px;"></span></div>
    <div class="content" id="payment">
        
        <h2>Pago en Tiendas de Conveniencia</h2>
        <div class="mb20">
            <img style="max-width: 120px" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/banzilla_stores.png" alt="Tiendas" class="tiendas">
        </div>
        <div class="well">
            Una vez que des clic en el botón <strong>Confirmar Orden</strong>, tu pedido será puesto en <strong>Espera de pago</strong> y podrás imprimir tu recibo pago el cual podrás liquidar en cualquiera de las tiendas participantes.
        </div>
        
        <div class="pull-right">
                <button type="button" class="btn btn-primary" id="button-confirm-oxxo" data-loading-text="Processing"><?php echo $button_confirm; ?></button>
        </div>
        
    </div>
  </div>
  
  <div id="speiTab" class="tab-pane fade">
    <div id="msgBoxSpei" role="alert"><i></i><span style="margin-left:10px;"></span></div>
    <div class="content" id="payment">
        
        <h2>Pago con transferencia bancaria (SPEI)</h2>
        <div class="col-md-3 mb20">
            <img style="max-width: 120px" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/spei.png" alt="SPEI" class="tiendas">
        </div>
        <div class="col-md-12 well">
            Una vez que des clic en el botón <strong>Confirmar Orden</strong>, tu pedido será puesto en <strong>Espera de pago</strong> y podrás imprimir las instrucciones con las cuales podrás liquidar tu pedido.
        </div>
        
        <div class="pull-right">
                <button type="button" class="btn btn-primary" id="button-confirm-spei" data-loading-text="Processing"><?php echo $button_confirm; ?></button>
        </div>
        
    </div>
  </div>
</div>

<script>
'use strict';

    $(document).ready(function(){
      
      function addMsg( msg , type, method ){
            var $msgBox = $( '#msgBox'+method );

            $msgBox[ 0 ].className =  'alert alert-' + type;
            $msgBox.find( 'span' ).text( msg );

            var className = '';
            switch( type ){
                    case 'danger' :
                            className = 'fa-lg fa fa-exclamation-triangle';
                    break;
                    case 'warning' :
                            className = 'fa fa-cog fa-spin urgent-2x';
                    break;
                    case 'success' :
                            className = 'fa-2x fa fa-check';
                    break;
            }
            $msgBox.find( 'i' )[ 0 ].className = className;
	}
      
      
      $('body').on('click', '#button-confirm', function(){
		var data = $('#payment-form').serialize();
		
		$.ajax({
	            url: '<?php echo $action ?>',
	            type: 'POST',
                    data: data,
	            dataType: 'json',
	            beforeSend: function() {
	            	$("#button-confirm").button( 'loading' );
                        $("#button-confirm").prop('disabled', true);
	            },

	            success: function(json){
                      
                      if (json.error) {
				addMsg( json.error , 'danger', 'Card' );
                                $("#button-confirm").prop('disabled', false);
                                $("#button-confirm").removeClass('disabled');
                                $("#button-confirm").button( 'reset' );
			}

			if (json['success']) {
				location = json['success'];
			}

	            }
	            
	        });
      });
      
      $('body').on('click', '#button-confirm-oxxo', function(){
		
		$.ajax({
	            url: '<?php echo $confirmOxxo ?>',
	            type: 'POST',
	            dataType: 'json',
	            beforeSend: function() {
	            	$("#button-confirm-oxxo").button( 'loading' );
                        $("#button-confirm-oxxo").prop('disabled', true);
	            },

	            success: function(json){
                      
                      if (json.error) {
                        
			  addMsg( json.error , 'danger', 'Oxxo' );
                          $("#button-confirm-oxxo").prop('disabled', false);
                          $("#button-confirm-oxxo").removeClass('disabled');
                          $("#button-confirm-oxxo").button( 'reset' );
			}else{
                          
                          $('#content').empty();
                          $('#content').html(json['success']);
                          
                        }

	            }
	            
	        });
      });
      
      $('body').on('click', '#button-confirm-spei', function(){
		
		$.ajax({
	            url: '<?php echo $confirmSpei ?>',
	            type: 'POST',
	            dataType: 'json',
	            beforeSend: function() {
	            	$("#button-confirm-spei").button( 'loading' );
                        $("#button-confirm-spei").prop('disabled', true);
	            },

	            success: function(json){
                      
                      if (json.error) {
                        
			  addMsg( json.error , 'danger', 'Spei' );
                          $("#button-confirm-spei").prop('disabled', false);
                          $("#button-confirm-spei").removeClass('disabled');
                          $("#button-confirm-spei").button( 'reset' );
			}else{
                          
                          $('#content').empty();
                          $('#content').html(json['success']);
                          
                        }

	            }
	            
	        });
      });
    });

</script>