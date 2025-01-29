<?php
header("Content-Type: application/json");

// Get UID and Region from URL parameters
$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
$region = isset($_GET['region']) ? $_GET['region'] : '';

if (!$uid || !$region) {
    echo json_encode(["error" => "Missing UID or Region"]);
    exit;
}

// Free Fire UID info API endpoint
$url = "https://tools.freefireinfo.in/profileinfo.php";

// Data to send
$data = [
    "uid" => $uid,
    "region" => $region
];

// Set request headers
$options = [
    "http" => [
        "header"  => "Content-Type: application/x-www-form-urlencoded\r\n" .
                     "User-Agent: Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36\r\n" .
                     "Referer: https://tools.freefireinfo.in/profileinfo.php\r\n",
        "method"  => "POST",
        "content" => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode(["error" => "Failed to fetch data"]);
    exit;
}

// Load response into DOM parser
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($response);
$xpath = new DOMXPath($dom);

// Extract Account Information
$resultDiv = $xpath->query("//div[@class='result']");
$account_info = $resultDiv->length > 0 ? trim($resultDiv->item(0)->textContent) : "Account information not found.";

// Extract Equipped Items (Images)
$images = [];
$equippedItems = $xpath->query("//div[@class='equipped-item']");
foreach ($equippedItems as $item) {
    $imgTag = $item->getElementsByTagName("img")->item(0);
    $imgName = $item->getElementsByTagName("p")->item(0);
    
    if ($imgTag && $imgName) {
        $images[trim($imgName->textContent)] = trim($imgTag->getAttribute("src"));
    }
}

// Return JSON response
$response_data = [
    "account_info" => $account_info,
    "images" => $images
];

echo json_encode($response_data, JSON_PRETTY_PRINT);
?>
