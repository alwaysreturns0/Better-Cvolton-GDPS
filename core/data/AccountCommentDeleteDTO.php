<?php
    require_once __DIR__ . '/../lib/ExploitPatch.php';
    require_once __DIR__ . '/../Main.php';

    class AccountCommentDeleteDTO {
        public int $accountID = 0;
        public int $commentID = 0;
        public int $userID = 0;
        public bool $hasPermission = false;

        public static function from_request(array $post, int $accountID, bool $hasPermission): self {
            $dto = new self();
            
            $main = new Main();
            
            $dto->accountID = $accountID;
            $dto->commentID = (int) ExploitPatch::remove($post['commentID'] ?? 0);
            $dto->userID = $main->get_user_id($accountID);
            $dto->hasPermission = $hasPermission;

            return $dto;
        }
    }