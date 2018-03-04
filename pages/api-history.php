<?php

$array = Base::query('SELECT * FROM `chat` ORDER BY `id` DESC LIMIT 10', [], true);

$array = array_map(
	function($value) {
		return [
			htmlspecialchars($value['name']), 
			htmlspecialchars($value['text']), 
			data_echo($value['time'])
		];
	}, 
	$array
);

$array = array_reverse($array);

header('Content-Type: application/json');
echo json_encode($array, JSON_UNESCAPED_UNICODE);
exit;
