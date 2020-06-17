<?php echo "<pre>";

require_once "vendor/autoload.php";
require_once "config/config.php";
require_once "classes/CCurl.php";

$curl = new Curl();
$subdomen = 'skomarov';
$link = "https://" . $subdomen . ".amocrm.ru/api/v4/contacts";
$headers = [
	'Authorization: Bearer ' . ACCESS_TOKEN,
	'Content-Type: application/json'
];

$contactsID = json_decode(file_get_contents('tmp/contacts_ids.json'));

foreach ($contactsID as $id) {
	$contact = [
		'id' => $id,
		'custom_fields_values' => [
			[
				'field_id' => 405021,
				'values' => [
					[
						'value' => 'first value'
					],
					[
						'value' => 'second value'
					]
				]
			]
		]
	];

	$data[] = $contact;
}

if (count($data) > 500) {
	$firstPack = [];
	$secondPack = [];
	foreach ($data as $key => $value) {
		$key < 500 ? $firstPack[] = $value : $secondPack[] = $value;
	}

	$firstPack = json_encode($firstPack);
	$secondPack = json_encode($secondPack);

	$response = $curl->patchRequest($headers, $link, $firstPack);
	$response = $curl->patchRequest($headers, $link, $secondPack);
} else {
	$data = json_encode($data);
	$response = $curl->patchRequest($headers, $link, $data);
}


