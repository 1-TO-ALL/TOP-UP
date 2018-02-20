<?php 
	//Merchant's account information
	$merchantID = "JT01";		//Get MerchantID when opening account with 2C2P
	$secretKey = "7jYcp4FxFdf0";	//Get SecretKey from 2C2P PGW Dashboard

	//Transaction Information
	$desc = "2 days 1 night hotel room";
	$uniqueTransactionCode = time();
	$currencyCode = "702";
	$amt  = "000000000010";
	$panCountry = "SG";

	//Customer Information
	$cardholderName = "John Doe";
 
	//Encrypted card data
	$encCardData = $_POST['encryptedCardInfo'];

	//Retrieve card information for merchant use if needed
	$maskedCardNo = $_POST['maskedCardInfo'];
	$expMonth = $_POST['expMonthCardInfo'];
	$expYear = $_POST['expYearCardInfo'];

	//Advance Payment Options
	$recurring = "Y";				//Enable / Disable RPP option
	$invoicePrefix = 'demo'.time();			//RPP transaction invoice prefix
	$recurringAmount = "000000000010";		//Recurring amount
	$allowAccumulate = "N";				//Allow failed authorization to be accumulated
	$maxAccumulateAmt = "";				//Maximum threshold of total accumulated amount
	$recurringInterval = "5";			//Recurring interval by no of days
	$recurringCount = "3";				//Number of Recurring occurance
	$chargeNextDate = (new DateTime('tomorrow'))->format("dmY");	//The date the first Recurring transaction should occur. format DDMMYYYY

	
	//Request Information 
	$version = "9.3";  
 
	//Construct signature string
	$stringToHash = $version.$merchantID.$uniqueTransactionCode.$desc.$amt.$currencyCode.$panCountry.$cardholderName.$recurring.$invoicePrefix.$recurringAmount.$allowAccumulate.$maxAccumulateAmt.$recurringInterval.$recurringCount.$chargeNextDate.$encCardData;
	$hash = strtoupper(hash_hmac('sha1', $stringToHash ,$secretKey, false));	//Compute hash value
  
  	
	//Construct payment request message
	$xml = "<PaymentRequest>
		<version>$version</version> 
		<merchantID>$merchantID</merchantID>
		<uniqueTransactionCode>$uniqueTransactionCode</uniqueTransactionCode>
		<desc>$desc</desc>
		<amt>$amt</amt>
		<currencyCode>$currencyCode</currencyCode>  
		<panCountry>$panCountry</panCountry> 
		<cardholderName>$cardholderName</cardholderName>
		<recurring>$recurring</recurring>
		<invoicePrefix>$invoicePrefix</invoicePrefix>
		<recurringAmount>$recurringAmount</recurringAmount>
		<allowAccumulate>$allowAccumulate</allowAccumulate>
		<maxAccumulateAmt>$maxAccumulateAmt</maxAccumulateAmt>
		<recurringInterval>$recurringInterval</recurringInterval>
		<recurringCount>$recurringCount</recurringCount>
		<chargeNextDate>$chargeNextDate</chargeNextDate>
		<encCardData>$encCardData</encCardData>
		<secureHash>$hash</secureHash>
		</PaymentRequest>";
	$data = base64_encode($xml);		//Convert payload to base64
	$payload = urlencode($data);		//encode with base64
 	
	include_once('HTTP.php');
	include_once('pkcs7.php');
	
	//Send authorization request
	$http = new HTTP();
	$response = $http->post("https://demo2.2c2p.com/2C2PFrontEnd/SecurePayment/Payment.aspx","paymentRequest=".$payload);
	
	//Decrypt response and display
	$pkcs7 = new pkcs7();
	$response = $pkcs7->decrypt($response,"./keys/demo2.crt","./keys/demo2.pem","2c2p");   
	echo "Response:<br/><textarea style='width:100%;height:80px'>". $response."</textarea>"; 	
?>
 