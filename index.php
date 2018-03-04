<?php

include 'config.php';
include PATH.'/inc/begin.php';

$page = @$_GET['page'];

if (isset($_SESSION['auth']))
{
	if ($page == 'api-send') $path = 'api-send.php';
	elseif ($page == 'api-history') $path = 'api-history.php';
	elseif ($page == 'outlogin') $path = 'outlogin.php';
	else $path = 'chat.php';
}
else
{
	if ($page == 'auth') $path = 'auth.php';
	else $path = 'index.php';
}

include PATH.'/pages/'.$path;
