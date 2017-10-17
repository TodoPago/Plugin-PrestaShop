<?php

namespace TodoPago\Test;

class OperationsDataProvider {

    public static function getStatusOptions() {

       $optionsGS = array( 
            'MERCHANT' => "41702",
            'OPERATIONID' => '185'
        );

        return $optionsGS;    
    }

    public static function getByRangeDateTimeOptions() {
        $date1 = date("Y-m-d", time()-60*60*24*30);
        $date2 = date("Y-m-d", time());
        $optionsRDT = array('MERCHANT'=>2658, "STARTDATE" => $date1, "ENDDATE" => $date2, "PAGENUMBER" => 1);
        return $optionsRDT;    
    }

    public static function getStatusOkResponse() {
        return '<OperationsColections xmlns="http://api.todopago.com.ar"><Operations><RESULTCODE>-1</RESULTCODE><RESULTMESSAGE>APROBADA</RESULTMESSAGE><DATETIME>2016-09-01T12:09:35.880-03:00</DATETIME><OPERATIONID>185</OPERATIONID><CURRENCYCODE>32</CURRENCYCODE><AMOUNT>3.00</AMOUNT><FEEAMOUNT>0.12</FEEAMOUNT><TAXAMOUNT>0.03</TAXAMOUNT><SERVICECHARGEAMOUNT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></SERVICECHARGEAMOUNT><CREDITEDAMOUNT>2.85</CREDITEDAMOUNT><AMOUNTBUYER>3.00</AMOUNTBUYER><FEEAMOUNTBUYER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></FEEAMOUNTBUYER><TAXAMOUNTBUYER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></TAXAMOUNTBUYER><CREDITEDAMOUNTBUYER>3.00</CREDITEDAMOUNTBUYER><BANKID>18</BANKID><PROMOTIONID>148047</PROMOTIONID><TYPE>compra_online</TYPE><INSTALLMENTPAYMENTS>1</INSTALLMENTPAYMENTS><CUSTOMEREMAIL>elenagaivironsky@hotmail.com</CUSTOMEREMAIL><IDENTIFICATIONTYPE>DNI</IDENTIFICATIONTYPE><IDENTIFICATION>13415824</IDENTIFICATION><CARDNUMBER>45097901XXXXXX5600</CARDNUMBER><CARDHOLDERNAME>Mauricio Ghiorzi</CARDHOLDERNAME><TICKETNUMBER>5190</TICKETNUMBER><AUTHORIZATIONCODE>004675</AUTHORIZATIONCODE><BARCODE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></BARCODE><COUPONEXPDATE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONEXPDATE><COUPONSECEXPDATE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONSECEXPDATE><COUPONSUBSCRIBER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONSUBSCRIBER><PAYMENTMETHODCODE>42</PAYMENTMETHODCODE><PAYMENTMETHODNAME>VISA</PAYMENTMETHODNAME><PAYMENTMETHODTYPE>Crédito</PAYMENTMETHODTYPE><REFUNDED>2016-09-01T15:29:28.500-03:00</REFUNDED><PUSHNOTIFYMETHOD xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYMETHOD><PUSHNOTIFYENDPOINT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYENDPOINT><PUSHNOTIFYSTATES xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYSTATES><IDCONTRACARGO>0</IDCONTRACARGO><FECHANOTIFICACIONCUENTA xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></FECHANOTIFICACIONCUENTA><ESTADOCONTRACARGO xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></ESTADOCONTRACARGO><COMISION>0.12</COMISION><REFUNDS><REFUND><ID>3788872</ID><AMOUNT>3.00</AMOUNT><DATETIME>2016-09-01T15:29:28.800-03:00</DATETIME></REFUND></REFUNDS></Operations></OperationsColections>';
    }

    public static function getStatusFailResponse() {
        return '<OperationsColections xmlns="http://api.todopago.com.ar"></OperationsColections>';
    }

    public static function getByRangeDateTimeOkResponse() {
        return '<OperationsColections xmlns="http://api.todopago.com.ar"><Operations><RESULTCODE>-1</RESULTCODE><RESULTMESSAGE>APROBADA</RESULTMESSAGE><DATETIME>2016-09-01T12:09:35.880-03:00</DATETIME><OPERATIONID>185</OPERATIONID><CURRENCYCODE>32</CURRENCYCODE><AMOUNT>3.00</AMOUNT><FEEAMOUNT>0.12</FEEAMOUNT><TAXAMOUNT>0.03</TAXAMOUNT><SERVICECHARGEAMOUNT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></SERVICECHARGEAMOUNT><CREDITEDAMOUNT>2.85</CREDITEDAMOUNT><AMOUNTBUYER>3.00</AMOUNTBUYER><FEEAMOUNTBUYER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></FEEAMOUNTBUYER><TAXAMOUNTBUYER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></TAXAMOUNTBUYER><CREDITEDAMOUNTBUYER>3.00</CREDITEDAMOUNTBUYER><BANKID>18</BANKID><PROMOTIONID>148047</PROMOTIONID><TYPE>compra_online</TYPE><INSTALLMENTPAYMENTS>1</INSTALLMENTPAYMENTS><CUSTOMEREMAIL>elenagaivironsky@hotmail.com</CUSTOMEREMAIL><IDENTIFICATIONTYPE>DNI</IDENTIFICATIONTYPE><IDENTIFICATION>13415824</IDENTIFICATION><CARDNUMBER>45097901XXXXXX5600</CARDNUMBER><CARDHOLDERNAME>Mauricio Ghiorzi</CARDHOLDERNAME><TICKETNUMBER>5190</TICKETNUMBER><AUTHORIZATIONCODE>004675</AUTHORIZATIONCODE><BARCODE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></BARCODE><COUPONEXPDATE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONEXPDATE><COUPONSECEXPDATE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONSECEXPDATE><COUPONSUBSCRIBER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONSUBSCRIBER><PAYMENTMETHODCODE>42</PAYMENTMETHODCODE><PAYMENTMETHODNAME>VISA</PAYMENTMETHODNAME><PAYMENTMETHODTYPE>Crédito</PAYMENTMETHODTYPE><REFUNDED>2016-09-01T15:29:28.500-03:00</REFUNDED><PUSHNOTIFYMETHOD xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYMETHOD><PUSHNOTIFYENDPOINT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYENDPOINT><PUSHNOTIFYSTATES xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYSTATES><IDCONTRACARGO>0</IDCONTRACARGO><FECHANOTIFICACIONCUENTA xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></FECHANOTIFICACIONCUENTA><ESTADOCONTRACARGO xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></ESTADOCONTRACARGO><COMISION>0.12</COMISION><REFUNDS><REFUND><ID>3788872</ID><AMOUNT>3.00</AMOUNT><DATETIME>2016-09-01T15:29:28.800-03:00</DATETIME></REFUND></REFUNDS></Operations><Operations><RESULTCODE>-1</RESULTCODE><RESULTMESSAGE>APROBADA</RESULTMESSAGE><DATETIME>2016-09-01T12:09:35.880-03:00</DATETIME><OPERATIONID>185</OPERATIONID><CURRENCYCODE>32</CURRENCYCODE><AMOUNT>3.00</AMOUNT><FEEAMOUNT>0.12</FEEAMOUNT><TAXAMOUNT>0.03</TAXAMOUNT><SERVICECHARGEAMOUNT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></SERVICECHARGEAMOUNT><CREDITEDAMOUNT>2.85</CREDITEDAMOUNT><AMOUNTBUYER>3.00</AMOUNTBUYER><FEEAMOUNTBUYER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></FEEAMOUNTBUYER><TAXAMOUNTBUYER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></TAXAMOUNTBUYER><CREDITEDAMOUNTBUYER>3.00</CREDITEDAMOUNTBUYER><BANKID>18</BANKID><PROMOTIONID>148047</PROMOTIONID><TYPE>compra_online</TYPE><INSTALLMENTPAYMENTS>1</INSTALLMENTPAYMENTS><CUSTOMEREMAIL>elenagaivironsky@hotmail.com</CUSTOMEREMAIL><IDENTIFICATIONTYPE>DNI</IDENTIFICATIONTYPE><IDENTIFICATION>13415824</IDENTIFICATION><CARDNUMBER>45097901XXXXXX5600</CARDNUMBER><CARDHOLDERNAME>Mauricio Ghiorzi</CARDHOLDERNAME><TICKETNUMBER>5190</TICKETNUMBER><AUTHORIZATIONCODE>004675</AUTHORIZATIONCODE><BARCODE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></BARCODE><COUPONEXPDATE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONEXPDATE><COUPONSECEXPDATE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONSECEXPDATE><COUPONSUBSCRIBER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONSUBSCRIBER><PAYMENTMETHODCODE>42</PAYMENTMETHODCODE><PAYMENTMETHODNAME>VISA</PAYMENTMETHODNAME><PAYMENTMETHODTYPE>Crédito</PAYMENTMETHODTYPE><REFUNDED>2016-09-01T15:29:28.500-03:00</REFUNDED><PUSHNOTIFYMETHOD xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYMETHOD><PUSHNOTIFYENDPOINT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYENDPOINT><PUSHNOTIFYSTATES xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYSTATES><IDCONTRACARGO>0</IDCONTRACARGO><FECHANOTIFICACIONCUENTA xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></FECHANOTIFICACIONCUENTA><ESTADOCONTRACARGO xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></ESTADOCONTRACARGO><COMISION>0.12</COMISION><REFUNDS><REFUND><ID>3788872</ID><AMOUNT>3.00</AMOUNT><DATETIME>2016-09-01T15:29:28.800-03:00</DATETIME></REFUND></REFUNDS></Operations><Operations><RESULTCODE>-1</RESULTCODE><RESULTMESSAGE>APROBADA</RESULTMESSAGE><DATETIME>2016-09-01T12:09:35.880-03:00</DATETIME><OPERATIONID>185</OPERATIONID><CURRENCYCODE>32</CURRENCYCODE><AMOUNT>3.00</AMOUNT><FEEAMOUNT>0.12</FEEAMOUNT><TAXAMOUNT>0.03</TAXAMOUNT><SERVICECHARGEAMOUNT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></SERVICECHARGEAMOUNT><CREDITEDAMOUNT>2.85</CREDITEDAMOUNT><AMOUNTBUYER>3.00</AMOUNTBUYER><FEEAMOUNTBUYER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></FEEAMOUNTBUYER><TAXAMOUNTBUYER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></TAXAMOUNTBUYER><CREDITEDAMOUNTBUYER>3.00</CREDITEDAMOUNTBUYER><BANKID>18</BANKID><PROMOTIONID>148047</PROMOTIONID><TYPE>compra_online</TYPE><INSTALLMENTPAYMENTS>1</INSTALLMENTPAYMENTS><CUSTOMEREMAIL>elenagaivironsky@hotmail.com</CUSTOMEREMAIL><IDENTIFICATIONTYPE>DNI</IDENTIFICATIONTYPE><IDENTIFICATION>13415824</IDENTIFICATION><CARDNUMBER>45097901XXXXXX5600</CARDNUMBER><CARDHOLDERNAME>Mauricio Ghiorzi</CARDHOLDERNAME><TICKETNUMBER>5190</TICKETNUMBER><AUTHORIZATIONCODE>004675</AUTHORIZATIONCODE><BARCODE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></BARCODE><COUPONEXPDATE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONEXPDATE><COUPONSECEXPDATE xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONSECEXPDATE><COUPONSUBSCRIBER xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></COUPONSUBSCRIBER><PAYMENTMETHODCODE>42</PAYMENTMETHODCODE><PAYMENTMETHODNAME>VISA</PAYMENTMETHODNAME><PAYMENTMETHODTYPE>Crédito</PAYMENTMETHODTYPE><REFUNDED>2016-09-01T15:29:28.500-03:00</REFUNDED><PUSHNOTIFYMETHOD xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYMETHOD><PUSHNOTIFYENDPOINT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYENDPOINT><PUSHNOTIFYSTATES xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></PUSHNOTIFYSTATES><IDCONTRACARGO>0</IDCONTRACARGO><FECHANOTIFICACIONCUENTA xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></FECHANOTIFICACIONCUENTA><ESTADOCONTRACARGO xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"></ESTADOCONTRACARGO><COMISION>0.12</COMISION><REFUNDS><REFUND><ID>3788872</ID><AMOUNT>3.00</AMOUNT><DATETIME>2016-09-01T15:29:28.800-03:00</DATETIME></REFUND></REFUNDS></Operations></OperationsColections>';    
    }
}