<?php 
	chdir(dirname(__FILE__));
	require_once "../../core/Main.php";
    require_once "../../core/Level.php";
	
	require_once "../../core/lib/Lib.php";
	require_once "../../core/lib/GJPCheck.php";
	require_once "../../core/lib/exploitPatch.php";

	require_once '../../core/data/LevelDownloadDTO.php';

    $Main 		= new Main();
	$Lib 		= new Lib();
	$Level 		= new Level();

	if (!empty($_POST['levelID']) && $_POST['levelID'] != "") 
	{
		$accountID = GJPCheck::getAccountIDOrDie();
		$hostname = $Main->get_ip();

		$data = LevelDownloadDTO::from_request($_POST, $hostname);
		$download_level = $Level->download($data);

		exit((string) $download_level);
	} 
	
	exit("-1");
?>