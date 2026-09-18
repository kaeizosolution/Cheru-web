<?php
include_once("/home1/kaeizner/tarzanmall/cron/Classes/Config.php");
$conf = new Config;
$constants = $conf->constants();
$dbh_master = $conf->DB_MASTER;
$sql = "INSERT INTO ec_country SET country_id = ?, code = ?, name = ?, phone_code = ?, capital = ?, currency = ?, currency_name = ?, currency_symbol = ?;";
$sth = $dbh_master->prepare($sql);

// Path to your JSON file
$jsonFilePath = 'countries.json';
// Check if the file exists
if (file_exists($jsonFilePath)) {
    // Read the JSON file contents
    $jsonContent = file_get_contents($jsonFilePath);

    // Decode the JSON content into an associative array
    $data = json_decode($jsonContent, true);

    // Check if decoding was successful
    if (json_last_error() === JSON_ERROR_NONE) {
        // Access the data
        #print_r($data); // Display the decoded data
        if($data){
            foreach($data as $r){
                unset($r['timezones']);
                unset($r['translations']);
                unset($r['tld']);
                unset($r['native']);
                unset($r['region']);
                unset($r['subregion']);
                unset($r['latitude']);
                unset($r['longitude']);
                unset($r['emoji']);
                unset($r['emojiU']);
                unset($r['iso3']);
                unset($r['numeric_code']);

                #if($r['id'] == 101){
                    #print_r($r);
                    $result = $conf->insert((object) ['DBH' => $dbh_master, 'STH' => $sth, 'QRY' => $sql, 'PARAMS' => [$r['id'],$r['iso2'],$r['name'],$r['phone_code'],$r['capital'],$r['currency'],$r['currency_name'],$r['currency_symbol']]]);
                #}
            }
        }
    } else {
        // Handle JSON decoding errors
        echo "Error decoding JSON: " . json_last_error_msg();
    }
} else {
    // Handle file not found
    echo "File not found: $jsonFilePath";
}
?>
