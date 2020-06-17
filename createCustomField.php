<?php echo "<pre>";

require_once "vendor/autoload.php";
require_once "config/config.php";
require_once "classes/CCurl.php";

$curl = new Curl();
$subdomen = 'skomarov';
$link = "https://" . $subdomen . ".amocrm.ru/api/v4/contacts/custom_fields";
$headers = [
	'Authorization: Bearer ' . ACCESS_TOKEN,
	'Content-Type: application/json'
];

$data = [
	[
		'name' => 'test_field',
		'type' => 'multiselect',
		'enums' => [
			[
				'value' => 'first value',
				'sort' => 1
			],
			[
				'value' => 'second value',
				'sort' => 2
			]
		]
	]
];

$data = json_encode($data);
$response = $curl->postRequest($headers, $link, $data);
print_r($response);