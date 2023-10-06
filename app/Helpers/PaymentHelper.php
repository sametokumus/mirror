<?php

namespace App\Helpers;

use App\Models\BankRequest;
use App\Models\Payment;
use Carbon\Carbon;

class PaymentHelper
{

    public static function cancelPreauth($payment_id)
    {
        $payment = Payment::query()->where('payment_id', $payment_id)->first();
        $payment_member_bank = $payment->bank_id;
        $payment_installment_count = $payment->installment;
        if ($payment->type == 1){

            switch ($payment_member_bank) {
                case 15:
                    //vakıf
                    return PaymentHelper::cancelPreauthVakifbank($payment_id);
                    break;
                case 46:
                    //akbank
                    return PaymentHelper::cancelPreauthAkbank($payment_id);
                    break;
                case 111:
                    //finans
                    return PaymentHelper::cancelPreauthFinansbank($payment_id);
                    break;
                case 12:
                    //halk
                    return PaymentHelper::cancelPreauthHalkbank($payment_id);
                    break;
                case 64:
                    //is
                    return PaymentHelper::cancelPreauthIsbank($payment_id);
                    break;
                case 32:
                    //teb
                    return PaymentHelper::cancelPreauthTeb($payment_id);
                    break;
                default:
                    return PaymentHelper::cancelPreauthVakifbank($payment_id);
            }

        }else{
            return false;
        }

    }

    public static function cancelPreauthVakifbank($payment_id)
    {
        $PostUrl = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx';
        $IsyeriNo = "000000000200014";
        $TerminalNo = "VP201433";
        $IsyeriSifre = "f0T7AdDw";
        $SiparID = $payment_id;
        $IslemTipi = "Cancel";
        $ClientIp = "212.2.199.55"; // ödemeyi gerçekleþtiren kullanýcýnýn IP bilgisi alýnarak bu alanda gönderilmelidir.


        $PosXML = 'prmstr=<VposRequest><MerchantId>' . $IsyeriNo . '</MerchantId><Password>' . $IsyeriSifre . '</Password><TransactionType>' . $IslemTipi . '</TransactionType>';
        $PosXML = $PosXML . '<ReferenceTransactionId>' . $SiparID . '</ReferenceTransactionId><ClientIp>' . $ClientIp . '</ClientIp></VposRequest>';


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $PostUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $PosXML);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);

        $result = curl_exec($ch);


        curl_close($ch);

        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $PosXML,
            'pos_response' => $result,
            'type' => 2
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ResultCode) ? (string)$xml_snippet->ResultCode : '';
        if ($result_code == '0000') {
            $transaction_id = isset($xml_snippet->TransactionId) ? (string)$xml_snippet->TransactionId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPreauthAkbank($payment_id){
        $id = $payment_id;

        $url = "https://www.sanalakpos.com/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>102029041</ClientId>
<Type>Void</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 2
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            $transaction_id = isset($xml_snippet->TransId) ? (string)$xml_snippet->TransId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }

//    public static function cancelPreauthFinansbank($payment_id){
//        $PostUrl      = 'https://vpos.qnbfinansbank.com/Gateway/XMLGate.aspx';
//        $IsyeriNo     = "006600000014134";
//        $TerminalNo   = "VP201433";
//        $IsyeriSifre  = "YRBD0";
//        $SiparID = $payment_id;
//        $IslemTipi    = "Void";
//        $ClientIp     = "212.2.199.55";
//
//
//        $PosXML = '<PayforRequest><MbrId>5</MbrId><MerchantID>'.$IsyeriNo.'</MerchantID><UserCode>aktemapi3</UserCode><UserPass>'.$IsyeriSifre.'</UserPass><OrgOrderId>'.$SiparID.'</OrgOrderId>';
//        $PosXML .= '<SecureType>NonSecure</SecureType><TxnType>'.$IslemTipi.'</TxnType><Currency>949</Currency><Lang>TR</Lang></PayforRequest>';
//
//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $PostUrl);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $PosXML);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
//        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
//
//        $result = curl_exec($ch);
//
//
//        curl_close($ch);
//
//        $request_id = BankRequest::query()->insertGetId([
//            'payment_id' => $payment_id,
//            'pos_request' => $PosXML,
//            'pos_response' => $result,
//            'type' => 2
//        ]);
//
//        $xml_snippet = simplexml_load_string($result);
//        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
//        if ($result_code == '00') {
//            $transaction_id = isset($xml_snippet->OrderId) ? (string)$xml_snippet->OrderId : '';
//            BankRequest::query()->where('id', $request_id)->update([
//                'transaction_id' => $transaction_id,
//                'success' => 1
//            ]);
//            return true;
//        } else {
//            return false;
//        }
//    }

    public static function cancelPreauthFinansbank($payment_id){

        $data = "".
            "MbrId=5&".                                                                         //Kurum Kodu
            "MerchantID=006600000014134&".                                                               //Language_MerchantID
            "UserCode=aktemadmin&".                                                                   //Kullanici Kodu
            "UserPass=xxxxxxxxxxxx&".                                                                   //Kullanici Sifre
            "OrgOrderId=".$payment_id."&".                                                   //Orijinal Islem Siparis Numarasi
            "SecureType=NonSecure&".                                                                  //Language_SecureType
            "TxnType=Void&".                                                                          //Islem Tipi
            "Currency=949&".                                                                   //Para Birimi
            "Lang=TR&".                                                                           //Language_Lang
            $url = "https://vpos.qnbfinansbank.com/Gateway/Default.aspx";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
        curl_setopt($ch, CURLOPT_SSLVERSION, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        echo "<br>";
        if (curl_errno($ch)) {
            print curl_error($ch);
        } else {
            curl_close($ch);
        }

        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 2
        ]);

        $resultValues = explode(";;", $result);
        $result_array = array();
        foreach($resultValues as $resultt)
        {
            list($key,$value)= explode("=", $resultt);
            $result_array[$key] = $value;
        }

        if ($result_array['ProcReturnCode'] == '00') {
            $transaction_id = isset($result_array['OrderId']) ? (string)$result_array['OrderId'] : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPreauthHalkbank($payment_id){
        $id = $payment_id;

        $url = "https://sanalpos.halkbank.com.tr/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>500247951</ClientId>
<Type>Void</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 2
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            $transaction_id = isset($xml_snippet->TransId) ? (string)$xml_snippet->TransId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPreauthIsbank($payment_id){
        $id = $payment_id;

        $url = "https://spos.isbank.com.tr/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>700667123467</ClientId>
<Type>Void</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 2
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            $transaction_id = isset($xml_snippet->TransId) ? (string)$xml_snippet->TransId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPreauthTeb($payment_id){
        $id = $payment_id;

        $url = "https://sanalpos.teb.com.tr/servlet/cc5ApiServer";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>400933746</ClientId>
<Type>Void</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 2
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            $transaction_id = isset($xml_snippet->TransId) ? (string)$xml_snippet->TransId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }



    public static function confirmPayment($payment_id)
    {
        $payment = Payment::query()->where('payment_id', $payment_id)->first();
        $payment_member_bank = $payment->bank_id;
        $payment_installment_count = $payment->installment;
        if ($payment->type == 1){

            switch ($payment_member_bank) {
                case 15:
                    //vakıf
                    return PaymentHelper::confirmPaymentVakifbank($payment_id);
                    break;
                case 46:
                    //akbank
                    return PaymentHelper::confirmPaymentAkbank($payment_id);
                    break;
                case 111:
                    //finans
                    return PaymentHelper::confirmPaymentFinansbank($payment_id);
                    break;
                case 12:
                    //halk
                    return PaymentHelper::confirmPaymentHalkbank($payment_id);
                    break;
                case 64:
                    //is
                    return PaymentHelper::confirmPaymentIsbank($payment_id);
                    break;
                case 32:
                    //teb
                    return PaymentHelper::confirmPaymentTeb($payment_id);
                    break;
                default:
                    return PaymentHelper::confirmPaymentVakifbank($payment_id);
            }

        }else{
            return false;
        }

    }

    public static function confirmPaymentVakifbank($payment_id)
    {
        $payment = Payment::query()->where('payment_id', $payment_id)->first();

        $PostUrl = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx'; //Dokümanda yer alan Prod VPOS URL i. Testlerinizi test ortamýnda gerçekleþtiriyorsanýz dokümandaki test URL ini kullanmalýsýnýz.
        $IsyeriNo = "000000000200014";
        $TerminalNo = "VP201433";
        $IsyeriSifre = "f0T7AdDw";
        $SiparID = $payment_id;
        $IslemTipi = "Capture";
        $ClientIp = "212.2.199.55"; // ödemeyi gerçekleþtiren kullanýcýnýn IP bilgisi alýnarak bu alanda gönderilmelidir.
        $amount = $payment->paid_price;

        $PosXML = 'prmstr=<VposRequest>';
        $PosXML .= '<MerchantId>' . $IsyeriNo . '</MerchantId>';
        $PosXML .= '<Password>' . $IsyeriSifre . '</Password>';
        $PosXML .= '<TerminalNo>' . $TerminalNo . '</TerminalNo>';
        $PosXML .= '<TransactionType>' . $IslemTipi . '</TransactionType>';
        $PosXML .= '<CurrencyAmount>' . $amount . '</CurrencyAmount>';
        $PosXML .= '<CurrencyCode>949</CurrencyCode>';
        $PosXML .= '<ClientIp>' . $ClientIp . '</ClientIp>';
        $PosXML .= '<ReferenceTransactionId>' . $SiparID . '</ReferenceTransactionId>';
        $PosXML .= '</VposRequest>';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $PostUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $PosXML);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);

        $result = curl_exec($ch);


        curl_close($ch);

        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $PosXML,
            'pos_response' => $result,
            'type' => 3
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ResultCode) ? (string)$xml_snippet->ResultCode : '';
        if ($result_code == '0000') {
            $transaction_id = isset($xml_snippet->TransactionId) ? (string)$xml_snippet->TransactionId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            Payment::query()->where('payment_id', $payment_id)->update([
                'is_paid' => 1
            ]);

            return true;
        } else {
            return false;
        }
    }

    public static function confirmPaymentAkbank($payment_id){
        $id = $payment_id;

        $url = "https://www.sanalakpos.com/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>102029041</ClientId>
<Type>PostAuth</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 3
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            $transaction_id = isset($xml_snippet->TransId) ? (string)$xml_snippet->TransId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            Payment::query()->where('payment_id', $payment_id)->update([
                'is_paid' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }

    public static function confirmPaymentFinansbank($payment_id){

    }

    public static function confirmPaymentHalkbank($payment_id){
//        $id = BankRequest::query()
//            ->where('payment_id', $payment_id)
//            ->where('success', 1)
//            ->where('active', 1)
//            ->where('type', 1)
//            ->orderByDesc('id')
//            ->first()->transaction_id;
        $id = $payment_id;

        $url = "https://sanalpos.halkbank.com.tr/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>500247951</ClientId>
<Type>PostAuth</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 3
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            $transaction_id = isset($xml_snippet->TransId) ? (string)$xml_snippet->TransId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            Payment::query()->where('payment_id', $payment_id)->update([
                'is_paid' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }

    public static function confirmPaymentIsbank($payment_id){
        $id = $payment_id;

        $url = "https://spos.isbank.com.tr/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>700667123467</ClientId>
<Type>PostAuth</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 3
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            $transaction_id = isset($xml_snippet->TransId) ? (string)$xml_snippet->TransId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            Payment::query()->where('payment_id', $payment_id)->update([
                'is_paid' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }

    public static function confirmPaymentTeb($payment_id){
        $id = $payment_id;

        $url = "https://sanalpos.teb.com.tr/servlet/cc5ApiServer";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>400933746</ClientId>
<Type>PostAuth</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result,
            'type' => 3
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            $transaction_id = isset($xml_snippet->TransId) ? (string)$xml_snippet->TransId : '';
            BankRequest::query()->where('id', $request_id)->update([
                'transaction_id' => $transaction_id,
                'success' => 1
            ]);
            Payment::query()->where('payment_id', $payment_id)->update([
                'is_paid' => 1
            ]);
            return true;
        } else {
            return false;
        }
    }



    public static function cancelPayment($payment_id)
    {
        $payment = Payment::query()->where('payment_id', $payment_id)->first();
        $payment_member_bank = $payment->bank_id;
        $payment_installment_count = $payment->installment;
        if ($payment->type == 1){

            switch ($payment_member_bank) {
                case 15:
                    //vakıf
                    return PaymentHelper::cancelPaymentVakifbank($payment_id);
                    break;
                case 46:
                    //akbank
                    return PaymentHelper::cancelPaymentAkbank($payment_id);
                    break;
                case 111:
                    //finans
                    return PaymentHelper::cancelPaymentFinansbank($payment_id);
                    break;
                case 12:
                    //halk
                    return PaymentHelper::cancelPaymentHalkbank($payment_id);
                    break;
                case 64:
                    //is
                    return PaymentHelper::cancelPaymentIsbank($payment_id);
                    break;
                case 32:
                    //teb
                    return PaymentHelper::cancelPaymentTeb($payment_id);
                    break;
                default:
                    return PaymentHelper::cancelPaymentVakifbank($payment_id);
            }

        }else{
            return false;
        }

    }

    public static function cancelPaymentVakifbank($payment_id)
    {
        $payment = Payment::query()->where('payment_id', $payment_id)->first();

        $PostUrl = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx'; //Dokümanda yer alan Prod VPOS URL i. Testlerinizi test ortamýnda gerçekleþtiriyorsanýz dokümandaki test URL ini kullanmalýsýnýz.
        $IsyeriNo = "000000000200014";
        $TerminalNo = "VP201433";
        $IsyeriSifre = "f0T7AdDw";
        $SiparID = $payment_id;
        $IslemTipi = "Refund";
        $ClientIp = "212.2.199.55"; // ödemeyi gerçekleþtiren kullanýcýnýn IP bilgisi alýnarak bu alanda gönderilmelidir.
        $amount = $payment->paid_price;

        $PosXML = 'prmstr=<VposRequest>';
        $PosXML .= '<MerchantId>' . $IsyeriNo . '</MerchantId>';
        $PosXML .= '<Password>' . $IsyeriSifre . '</Password>';
        $PosXML .= '<TransactionType>' . $IslemTipi . '</TransactionType>';
        $PosXML .= '<CurrencyAmount>' . $amount . '</CurrencyAmount>';
        $PosXML .= '<ReferenceTransactionId>' . $SiparID . '</ReferenceTransactionId>';
        $PosXML .= '<ClientIp>' . $ClientIp . '</ClientIp>';
        $PosXML .= '</VposRequest>';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $PostUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $PosXML);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);

        $result = curl_exec($ch);


        curl_close($ch);

        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $PosXML,
            'pos_response' => $result,
            'type' => 4
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ResultCode) ? (string)$xml_snippet->ResultCode : '';
        if ($result_code == '0000') {
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPaymentAkbank($payment_id){
        $id = $payment_id;

        $url = "https://www.sanalakpos.com/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>102029041</ClientId>
<Type>Void</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPaymentFinansbank($payment_id){

    }

    public static function cancelPaymentHalkbank($payment_id){
        $id = $payment_id;

        $url = "https://sanalpos.halkbank.com.tr/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>500247951</ClientId>
<Type>Void</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPaymentIsbank($payment_id){
        $id = $payment_id;

        $url = "https://spos.isbank.com.tr/fim/api";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>700667123467</ClientId>
<Type>Void</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPaymentTeb($payment_id){
        $id = $payment_id;

        $url = "https://sanalpos.teb.com.tr/servlet/cc5ApiServer";  //TEST

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/xml",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = "<CC5Request>
<Name>tsoftapi</Name>
<Password>Api.Tsoft123</Password>
<ClientId>400933746</ClientId>
<Type>Void</Type>
<OrderId>".$id."</OrderId>
</CC5Request>
";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        $request_id = BankRequest::query()->insertGetId([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ProcReturnCode) ? (string)$xml_snippet->ProcReturnCode : '';
        if ($result_code == '00') {
            return true;
        } else {
            return false;
        }
    }



}
