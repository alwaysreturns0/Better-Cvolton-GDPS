<?php
    chdir(dirname(__FILE__));
    require_once "../../core/Communication.php";

    require_once "../../core/lib/GJPCheck.php";
    require_once "../../core/lib/exploitPatch.php";

    $Message = new Message();

    if (isset($_POST["messageID"])) {
        $accountID = GJPCheck::getAccountIDOrDie();
        
        $dto = MessageDownloadDTO::from_request($_POST, $accountID);
        $downloadMessage = $Message->download($dto);

        exit($downloadMessage);
    }

    exit("-1");