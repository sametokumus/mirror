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

            if ($payment_installment_count == "1") {
                $payment_member_bank = 15;
            }

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
        $PostUrl = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx'; //Dokümanda yer alan Prod VPOS URL i. Testlerinizi test ortamýnda gerçekleþtiriyorsanýz dokümandaki test URL ini kullanmalýsýnýz.
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

        BankRequest::query()->insert([
            'payment_id' => $payment_id,
            'pos_request' => $PosXML,
            'pos_response' => $result
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->ResultCode) ? (string)$xml_snippet->ResultCode : '';
        if ($result_code == '0000') {
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPreauthAkbank($payment_id){

    }

    public static function cancelPreauthFinansbank($payment_id){

    }

    public static function cancelPreauthHalkbank($payment_id){

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

//ORDER-22158HloH01015371
//c68f2561-21d3-3618-b62f-d75b52345213
//2968dc5c-e3af-3e3a-8ac6-29351d97e80d
//0b7a958f-4f64-3781-9ec0-604b400fe2f0


        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);


        BankRequest::query()->insert([
            'payment_id' => $payment_id,
            'pos_request' => $data,
            'pos_response' => $result
        ]);

        $xml_snippet = simplexml_load_string($result);
        $result_code = isset($xml_snippet->procreturncode) ? (string)$xml_snippet->procreturncode : '';
        if ($result_code == '00') {
            return true;
        } else {
            return false;
        }
    }

    public static function cancelPreauthTeb($payment_id){

    }

}
