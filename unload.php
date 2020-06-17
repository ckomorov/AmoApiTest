<?php echo "<pre>";

require_once "vendor/autoload.php";
require_once "config/config.php";
require_once "classes/CCurl.php";

header('Content-Type: text/html; charset=utf-8');

$entityType = 'contacts';

$curl = new Curl();
$headers = [
	'Authorization: Bearer ' . ACCESS_TOKEN,
	'Content-Type: application/json'
];

$entities = getEntities($entityType, $curl, $headers);
$entities = updateEntities($entities, $headers, $curl);

?>


<table>
	<tr>
    <th>Номер</th>
    <th>Имя</th>
    <th>Ответственный</th>
    <th>Название поля</th>
   </tr>
	<?php 
	$i = 1;
	foreach($entities as $entity) : ?>
		<tr>
			<td>
				<?php echo $i; ?>
			</td>
			<td>
				<?php echo $entity->name; ?>
			</td>
			<td>
				<?php 
				echo $entity->responsible_user_id;
				// $link = "https://skomarov.amocrm.ru/api/v4/users/" . $contact->responsible_user_id;
				// $response = $curl->getRequest($headers, $link);
				// echo $response->name; 
				?>
			</td>
			<? if (isset($entity->custom_fields_values)) {
				foreach($entity->custom_fields_values as $custom) : ?>
				<td><?php echo $custom->field_name; ?></td>
			<? endforeach; 
			}	
			?>
		</tr>
	<?php
	$i++;
	endforeach; 
	?>
</table>


<?
/**
* Получаем массив объектов
* 
* @param string $type
* @param Curl $curl
* @param array $headers
*
* @return array
*/
function getEntities($type, $curl, $headers) {
	$link = "https://skomarov.amocrm.ru/api/v4/{$type}?limit=250&page=1";
	$result = [];
	$response = $curl->getRequest($headers, $link);
	do {
		$response = $curl->getRequest($headers, $link);
		if (empty($response))  break;
		$link = $response->_links->next->href;
		$entities = $response->_embedded->$type;
		foreach($entities as $entity) {
			$result[] = $entity;
		}
	} while(isset($response));

	return $result;
}

/**
* Заменяем user_id на user_name (первый вариант, когда показал не юзер-френдли)
* 
* @param array $array
* @param array $headers
* @param Curl $curl 
*/
function updateEntities($array, $headers, $curl) {
	$users = [];
	foreach ($array as &$object) {
		$ids['responsible'] = $object->responsible_user_id;
		$ids['created_by'] = $object->created_by;
		$ids['updated_by'] = $object->updated_by;
		$usersName = [];
		$userList = [];
		foreach ($ids as $key => $value) {
			if (!empty($userList)) {
				foreach ($userList as $id => $name) {
					$value == $id ? $userName = $name : '';
				}
			}
			if (isset($userName)) {
				$usersName[$key] = $userName;
			} else {
				$link = "https://skomarov.amocrm.ru/api/v4/users/" . $value;
				$response = $curl->getRequest($headers, $link);
				$usersName[$key] = $response->name;
				$userList[$value] = $response->name;
			}
		}
		
		$object->responsible_user_id = $usersName['responsible'];
		$object->created_by = $usersName['created_by'];
		$object->updated_by = $usersName['updated_by'];
	}
	return $array;
}
?>