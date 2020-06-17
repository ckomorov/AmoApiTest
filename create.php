<?php echo "<pre>";

require_once "vendor/autoload.php";
require_once "config/config.php";
require_once "classes/CCurl.php";


createEntities('Lead', 100, 'leads');
createEntities('Company', 100, 'companies');
createEntities('Contact', 100, 'contacts');

linkEntities('leads', 'companies');
linkEntities('leads', 'contacts');
linkEntities('companies', 'contacts');

/**
* Cоздание экземпляров сущности
* 
* @param string $type
* @param int $count
* @param string $method
*
*/
function createEntities($type, $count, $method) {
	$curl = new Curl();
	$subdomen = 'skomarov';
	$link = "https://" . $subdomen . ".amocrm.ru/api/v4/" . $method;
	$headers = [
		'Authorization: Bearer ' . ACCESS_TOKEN,
		'Content-Type: application/json'
	];
	$data = [];
	for ($i = 1; $i < $count + 1; $i++) { 
		$name = $type .  ' #' . $i;
		$data[] = ['name' => $name];
	}
	if ($count > 500) {
		$firstPack = [];
		$secondPack = [];
		foreach ($data as $key => $value) {
			$key < 500 ? $firstPack[] = $value : $secondPack[] = $value;
		}
		$firstPack = json_encode($firstPack);
		$secondPack = json_encode($secondPack);

		$responseOfFirst = $curl->postRequest($headers, $link, $firstPack);
		$responseOfSecond = $curl->postRequest($headers, $link, $secondPack);
	} else {
		$data = json_encode($data);
		$response = $curl->postRequest($headers, $link, $data);
		$ids = [];
		foreach ($response->_embedded->$method as $entity) {
			$ids[] = $entity->id;
		}
		$json_ids = json_encode($ids);
		file_put_contents("tmp/{$method}_ids.json", $json_ids);
	}
}

/**
* Линкование экземпляров сущности
* 
* @param string $master
* @param string $slave
*
*/
function linkEntities($master, $slave) {
	$curl = new Curl();
	$headers = [
		'Authorization: Bearer ' . ACCESS_TOKEN,
		'Content-Type: application/json'
	];
	$masterIds = json_decode(file_get_contents("tmp/{$master}_ids.json"));
	$slaveIds = json_decode(file_get_contents("tmp/{$slave}_ids.json"));
	$i = 0;
	foreach ($masterIds as $id) {
		$link = "https://skomarov.amocrm.ru/api/v4/{$master}/{$id}/link";
		$data = [
			[
				'to_entity_id' => $slaveIds[$i],
				'to_entity_type' => $slave,
			]
		];

		$data = json_encode($data);
		$response = $curl->postRequest($headers, $link, $data);
		print_r($response); 
		$i++;
	}
}


