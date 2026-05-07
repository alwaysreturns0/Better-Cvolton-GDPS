<?php
	chdir(dirname(__FILE__));

	require_once "../../core/Main.php";
	require_once "../../core/AccountComments.php";

	require_once "../../core/lib/GJPCheck.php";
	require_once "../../core/lib/exploitPatch.php";

	require_once "../../core/data/AccountCommentUploadDTO.php";

	
	$main 				= new Main();
	$AccountComment 	= new AccountComments();
	
	if (isset($_POST["userName"]) || isset( $_POST["comment"])) 
	{
		$accountID		= GJPCheck::getAccountIDOrDie();
		$data 			= AccountCommentUploadDTO::from_request($_POST, $accountID);

		$uploadComment = $AccountComment->upload_comment($data);

		exit($uploadComment);
	}
	
	exit("-1");
?>