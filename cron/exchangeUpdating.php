<?php
/*
 * Cron Job using crontab
 * 0 * * * * /usr/bin/php7.0 -q /var/www/converter/cron/exchangeUpdating.php > /dev/null
 * */

$mysqli = new mysqli('localhost', 'root', '777', "currency");

if ($mysqli->connect_errno) {
    printf("Error: %s\n", $mysqli->connect_error);
    exit();
}

$endpoint = 'live';
$access_key = '2f60e5bb67cbd6f88cfb1cae350ed997';
$currencies = '&currencies=AUD,BTC,CAD,CHF,CNY,CZK,DKK,EEK,EUR,GBP,HKD,ILS,JPY,LVL,PLN,SAR,SEK,UAH,USD,XAU';

// Initialize CURL:
$ch = curl_init('http://apilayer.net/api/'.$endpoint.'?access_key=' . $access_key . $currencies);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
curl_close($ch);

// Decode JSON response:
$exchangeRates = json_decode($json, true);

if (
    isset($exchangeRates['success']) && ($exchangeRates['success'] == 1) &&
    isset($exchangeRates['quotes']) && is_array($exchangeRates['quotes'])
) {

    foreach ($exchangeRates['quotes'] as $quote => $rate) {
        // Cut 'USD' from beginning of every quote
        $quote = substr($quote, 3);

        $sql = "UPDATE `exchange_history` SET `rate`=$rate WHERE `quote`=$quote";
        if(mysqli_query($mysqli, $sql)){
            echo "Records were updated successfully.";
        } else {
            echo "ERROR: Could not able to execute $sql. " . mysqli_error($mysqli);
        }

    }

}

$mysqli->close();

?>