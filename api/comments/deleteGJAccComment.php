<?php
    chdir(dirname(__FILE__));

    require_once "../../core/Main.php";
    require_once "../../core/AccountComments.php";
    require_once "../../core/data/AccountCommentDeleteDTO.php";

    require_once "../../core/lib/GJPCheck.php";
    require_once "../../core/lib/exploitPatch.php";

    $main = new Main();
    $AccountComment = new AccountComments();

    if (isset($_POST["commentID"])) {
        $accountID = GJPCheck::getAccountIDOrDie();
        $hasPermission = $main->getRolePermission($accountID, "actionDeleteComment");
        
        $data = AccountCommentDeleteDTO::from_request($_POST, $accountID, $hasPermission);
        $deleteAccountComment = $AccountComment->delete_comment($data);

        exit($deleteAccountComment);
    } 
    
    exit("-1");