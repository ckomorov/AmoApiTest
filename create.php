<?php echo "<pre>";

require_once "vendor/autoload.php";
require_once "config/config.php";
require_once "classes/CCurl.php";


$leadsID = createEntities('Lead', 100, 'leads');
$companiesID = createEntities('Company', 100, 'companies');
$contactsID = createEntities('Contact', 100, 'contacts');

linkEntities('leads', ['companies', 'contacts'], $leadsID, [$companiesID, $contactsID]);
linkEntities('companies', ['contacts'], $companiesID, [$contactsID]);

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

	$n = round(count($data) / 500);

	if ($n == 0) {
		
		$data = json_encode($data);
		$response = $curl->postRequest($headers, $link, $data);
		$ids = [];
		foreach ($response->_embedded->$method as $entity) {
			$ids[] = $entity->id;
		}
		
		return $ids;

	} else {
		
		$packs = array_chunk($data, 500);
		
		foreach ($packs as $pack) {
			$pack = json_encode($pack);
			$response = $curl->postRequest($headers, $link, $pack);
			foreach ($response->_embedded->$method as $entity) {
				$ids[] = $entity->id;
			}
		}

		return $ids;
	}
}

/**
* Линкование экземпляров сущности
* 
* @param string $master
* @param array $slaves
* @param array $masterIds
* @param array $slavesIds
*
*/
function linkEntities($master, $slaves, $masterIds, $slavesIds) {
	$curl = new Curl();
	$headers = [
		'Authorization: Bearer ' . ACCESS_TOKEN,
		'Content-Type: application/json'
	];
	$j = 0;
	
	foreach ($masterIds as $id) {
		$data = [];
		$link = "https://skomarov.amocrm.ru/api/v4/{$master}/{$id}/link";
		$i = 0;

		foreach ($slaves as $slave) {
			$embedded = [
				'to_entity_id' => $slavesIds[$i][$j],
				'to_entity_type' => $slaves[$i],
			];

			$data[] = $embedded;
			$i++;
		}
		$j++;

		$data = json_encode($data);
		$response = $curl->postRequest($headers, $link, $data);
	}
}


