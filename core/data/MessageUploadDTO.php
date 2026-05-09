<?php
    require_once __DIR__ . '/../lib/exploitPatch.php';
    require_once __DIR__ . '/../Main.php';

    class MessageUploadDTO {
        public int $accountID = 0;
        public int $userID = 0;
        public int $toAccountID = 0;
        public string $userName = '';
        public string $subject = '';
        public string $body = '';
        public string $secret = '';
        public int $gameVersion = 0;
        public int $binaryVersion = 0;
        public int $uploadDate = 0;

        public static function from_request(array $post, int $accountID): self {
            $dto = new self();
            
            $main = new Main();
            
            $dto->accountID = $accountID;
            $dto->userID = $main->get_user_id($accountID);
            $dto->toAccountID = (int) ExploitPatch::number($post['toAccountID'] ?? 0);
            $dto->subject = ExploitPatch::remove($post['subject'] ?? '');
            $dto->body = ExploitPatch::remove($post['body'] ?? '');
            $dto->secret = ExploitPatch::remove($post['secret'] ?? '');
            $dto->gameVersion = (int) ExploitPatch::remove($post['gameVersion'] ?? 0);
            $dto->binaryVersion = (int) ExploitPatch::remove($post['binaryVersion'] ?? 0);
            $dto->uploadDate = time();

            return $dto;
        }
    }