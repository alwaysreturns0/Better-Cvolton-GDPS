<?php
    chdir(dirname(__FILE__));
    
    require_once "../../core/Communication.php";

    require_once "../../core/lib/GJPCheck.php";
    require_once "../../core/lib/exploitPatch.php";

    $Message = new Message();

    if (isset($_POST['messageID']) || isset($_POST['messages'])) {
        $accountID = GJPCheck::getAccountIDOrDie();
        
        $dto = MessageDeleteDTO::from_request($_POST, $accountID);
        $deleteMessage = $Message->delete($dto);

        exit($deleteMessage);
    }
    
    exit("-1");