<?php

if (! empty($_POST['text']))
{
	Base::addRow('chat', [
		'name' => $_SESSION['auth']['first_name'].' '.$_SESSION['auth']['last_name'],
		'text' => $_POST['text'],
		'time' => time(),
	]);
	
	exit;
}
