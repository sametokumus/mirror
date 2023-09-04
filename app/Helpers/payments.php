<?php
use App\Models\CurrencyLog;
use Carbon\Carbon;

if (! function_exists('cancelPreauth')) {
    function cancelPreauth($payment_id)
    {


        return 1;
    }
}

if (! function_exists('cancelPreauthVakifbank')) {
    function cancelPreauthVakifbank($payment_id)
    {
        $PostUrl      = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx'; //Dokümanda yer alan Prod VPOS URL i. Testlerinizi test ortamýnda gerçekleþtiriyorsanýz dokümandaki test URL ini kullanmalýsýnýz.
        $IsyeriNo     = "000000000200014";
        $TerminalNo   = "VP201433";
        $IsyeriSifre  = "f0T7AdDw";
        $SiparID      = $payment_id;
        $IslemTipi    = "Cancel";
        $ClientIp     = "212.2.199.55"; // ödemeyi gerçekleþtiren kullanýcýnýn IP bilgisi alýnarak bu alanda gönderilmelidir.


        $PosXML = 'prmstr=<VposRequest><MerchantId>'.$IsyeriNo.'</MerchantId><Password>'.$IsyeriSifre.'</Password><TransactionType>'.$IslemTipi.'</TransactionType>';
        $PosXML = $PosXML.'<ReferenceTransactionId>'.$SiparID.'</ReferenceTransactionId><ClientIp>'.$ClientIp.'</ClientIp></VposRequest>';


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$PostUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$PosXML);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);

        $result = curl_exec($ch);


        curl_close($ch);

        $xml_snippet = simplexml_load_string( $result );

        return 1;
    }
}
