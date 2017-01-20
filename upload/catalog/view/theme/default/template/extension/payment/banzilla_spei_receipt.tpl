<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/javascript/jquery-2.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/stylesheet/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/stylesheet/banzilla.css">
<div class="container">
    <div class="row">
        <div id="content" class="col-md-12">

            <div class="container container-receipt">
                <div class="row header" style="padding: 10px 0 0 0;">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <img style="max-width: 200px" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/banzilla-logo.png" alt="Banzilla" class="Banzilla">
                    </div>	
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
                        <p class="Yellow2">Esperamos tu pago</p>
                    </div>	
                </div>

                <div class="row">
                    <div class="col-xs-9 col-sm-8 col-md-8 col-lg-8">
                        <h1><strong>Total a pagar</strong></h1>
                        <h2 class="amount">$<?php echo $amount; ?><small> <?php echo $currency; ?></small></h2>
                        <h1><strong>Fecha límite de pago:</strong></h1>
                        <h1><?php echo $due_date; ?></h1>
                    </div>
                    <div class="col-xs-3 col-sm-4 col-md-4 col-lg-4">
                        <img class="img-responsive spei" src="<?php echo HTTP_SERVER; ?>catalog/view/theme/default/image/spei.png"  alt="SPEI">
                    </div>
                </div>

                <div class="row marketing">
                    <h1 style="padding-bottom:10px;"><strong>Datos para transferencia electrónica</strong></h1>
                    <div class="col-lg-12 datos_pago">
                        <table>
                            <tr>
                                <td style="width: 50%;">Banco:</td>
                                <td>STP</td>
                            </tr>    
                            <tr>
                                <td>CLABE:</td>
                                <td><?php echo $clabe; ?></td>
                            </tr>    
                            <tr>
                                <td>Referencia:</td>
                                <td><?php echo $reference ; ?></td>
                            </tr>    
                            <tr>
                                <td>Beneficiario:</td>
                                <td><?php echo $store_name; ?></td>
                            </tr>    
                        </table>
                    </div>	        



                    <div class="col-lg-12" style="text-align: center; margin-top: 20px;">
                        <p>¿Tienes alguna dudas o problema? Escríbenos a</p>
                        <h4><?php echo $store_email; ?></h4>
                    </div>
                        
                    <div class="col-md-6 col-sm-6 col-xs-12" style="text-align:center; margin-top:5px;">
                        <a href="<?php echo $clabeUrl; ?>" target="_blank" type="button" class="btn btn-info btn-lg btn btn-primary"><i class="fa fa-print"></i> Print</a>
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