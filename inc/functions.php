<?php

function redirect($url)
{
	header('Location: '.$url);
	exit;
}

function data_echo($timestamp = false)
{
	if (! $timestamp)
	{
		$timestamp = TIME;
	}
	
	$month_list = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
	
	list($day, $month, $year, $hour, $minute) = explode('-', date('j-n-Y-G-i', $timestamp));
	
	return $day.' '.$month_list[ $month - 1 ].' '.$year.' '.$hour.':'.$minute;
}
