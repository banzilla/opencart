<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/javascript/jquery-2.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/stylesheet/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/stylesheet/banzilla.css">

<div class="container">
    <div class="row">
        <div id="content" class="col-md-12  mb30">
            <div class="container container-receipt">
                <div class="row" style="padding: 10px 0 0 0;">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <img style="max-width: 200px" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/banzilla-logo.png" alt="Banzilla" class="Banzilla">
                    </div>	
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
                        <p class="Logo_paynet">Service to pay</p>
                        <img style="max-width: 120px" class="img-responsive center-block Logo_paynet" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/banzilla_stores.png" alt="banzilla">
                    </div>	
                </div>

                <div class="row data">

                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="Big_Bullet">
                            <span></span>
                        </div>
                        <h1><strong>Payment deadline</strong></h1> 
                        <strong><?php echo $due_date; ?></strong>
                        <div class="col-lg-12 datos_pago">
                            <p>Print your bar code <a href="<?php echo $barcode_url; ?>">here</a></p>
                            <span style="font-size: 14px">Reference :<?php echo $reference; ?></span>
                            <br/>
                            <p>In case the scanner is not able to read the barcode, write the reference as shown.</p>
                        </div>

                    </div>

                    <div class="col-xs-12 col-sm-1 col-md-1 col-lg-1"></div>

                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
                        <div class="data_amount"> 
                            <h2>Total to pay</h2>
                            <h2 class="amount">$<?php echo $amount; ?><small> <?php echo $currency; ?></small></h2>
                            <h2 class="S-margin">+8 pesos commission</h2>
                        </div>
                    </div>


                </div>

                <div class="row data">

                    <div class="col-xs-12 col-sm-11 col-md-11 col-lg-11">
                        <div class="Big_Bullet">
                            <span></span>
                        </div>
                        <h1><strong>Purchase Details</strong></h1> 
                        <div class="col-lg-12 datos_tiendas">
                            <table>
                                <tr>
                                    <td width="40%">Order:</td>
                                    <td width="60%"><?php echo $order_id; ?></td>
                                </tr>    
                                <tr>
                                    <td>Date and Time:</td>
                                    <td><?php echo $creation_date; ?></td>
                                </tr>    
                                <tr>
                                    <td>Email:</td>
                                    <td><?php echo $email; ?></td>
                                </tr>    
                            </table>
                        </div>			
                    </div>
                </div>

                <div class="row data">

                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="Big_Bullet">
                            <span></span>
                        </div>
                        <h1><strong>How to make payment</strong></h1> 
                        <ol style="margin-left: 30px;">
                            <li>Go to any OXXO store</li>
                            <li>Send the bar code to the cashier and mention that you will make a Banzilla service payment</li>
                            <li>Make the payment in cash for $<?php echo $amount; ?> <?php echo $currency; ?> (más $8 pesos for commission)</li>
                            <li>Keep the ticket for any clarification</li>
                        </ol>	
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <h1><strong>Instructions for the cashier</strong></h1> 
                        <ol>
                            <li>Enter the Services Payment menu</li>
                            <li>Scan the barcode or enter the reference number</li>
                            <li>Enter the total amount to be paid</li>
                            <li>Charge the customer the total amount plus the commission of $ 8 pesoss</li>
                            <li>Confirm the transaction and deliver the ticket to the customer</li>
                        </ol>
                    </div>
                </div>


                <div class="row marketing">

                    <div class="col-lg-12" style="text-align:center;">
                        <img style="max-width: 200px" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/banzilla-logo.png" alt="Banzilla" class="Banzilla">
                    </div>
                    
                    <div class="col-md-6 col-sm-6 col-xs-12" style="text-align:center; margin-top:5px;">
                        <a href="<?php echo $barcode_url; ?>" target="_blank" type="button" class="btn btn-info btn-lg btn btn-primary"><i class="fa fa-print"></i> Print</a>
                    </div>	  
                    <div class="col-md-6 col-sm-6 col-xs-12" style="text-align:center; margin-top:5px;">
                        <a type="button" class="btn btn-lg btn btn-primary" id="button-confirm"><i class="fa fa-check"></i> Continue</a>
                    </div>
                    
                </div>
            </div> 
            
        </div>
        
    </div>
</div>

<script type="text/javascript"><!--
  $(document).ready(function() {
        $('#button-confirm').click(function() {
            location = '<?php echo $continue ?>';
        });
    });
</script>

