<?php
    chdir(dirname(__FILE__));
    require_once "../../core/Communication.php";
    
    require_once "../../core/lib/GJPCheck.php";
    require_once "../../core/lib/exploitPatch.php";

    $Message = new Message();

    if (isset($_POST["secret"])) {
        $accountID = GJPCheck::getAccountIDOrDie();
        
        $dto = MessageUploadDTO::from_request($_POST, $accountID);
        $uploadMessage = $Message->upload($dto);

        exit($uploadMessage);
    }
    
    exit("-1");