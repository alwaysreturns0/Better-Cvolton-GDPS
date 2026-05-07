<?php
    require_once __DIR__ . '/../lib/ExploitPatch.php';
    require_once __DIR__ . '/../Main.php';

    class AccountCommentUploadDTO {
        public int $accountID = 0;
        public int $userID = 0;
        public string $userName = '';
        public string $comment = '';
        public int $uploadDate = 0;

        public static function from_request(array $post, int $accountID): self {
            $dto = new self();
            
            $main = new Main();
            
            $dto->accountID = $accountID;
            $dto->userName = ExploitPatch::remove($post['userName'] ?? '');
            $dto->userID = $main->get_user_id($accountID, $dto->userName);
            $dto->comment = ExploitPatch::remove($post['comment'] ?? '');
            $dto->uploadDate = time();

            return $dto;
        }
    }