<?php

$json = file_get_contents('http://ulogin.ru/token.php?token='.$_POST['token'].'&host='.$_SERVER['HTTP_HOST']);
$user = json_decode($json, true);

if (! empty($user['identity']))
{
	$_SESSION['auth'] = $user;
}

redirect('?');
