<?php
	chdir(dirname(__FILE__));
	require_once "../../core/LevelSearch.php";

	require_once "../../core/lib/GJPCheck.php";
	require_once "../../core/lib/exploitPatch.php";

	require_once __DIR__."/data/LevelSearchDTO.php";
    require_once __DIR__."/rep/SearchRepository.php";
	
	$LevelSearch		= new LevelSearch();
	$accointID 			= GJPCheck::getAccountIDOrDie();
	$data 				= LevelSearchDTO::from_request($_POST, $accointID);
    $result				= $LevelSearch->search($data);

	exit($result);
?>