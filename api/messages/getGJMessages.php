<?php
    chdir(dirname(__FILE__));
    require_once "../../core/Communication.php";

    require_once "../../core/lib/GJPCheck.php";
    require_once "../../core/lib/exploitPatch.php";

    $Message = new Message();

    $accountID = GJPCheck::getAccountIDOrDie();
    
    $dto = MessageGetDTO::from_request($_POST, $accountID);
    $getMessages = $Message->getData($dto);

    exit($getMessages);