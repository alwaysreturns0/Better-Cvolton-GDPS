<?php
    require_once __DIR__ . '/../lib/ExploitPatch.php';

    class MessageDownloadDTO {
        public int $accountID = 0;
        public int $messageID = 0;
        public int $isSender = 0;

        public static function from_request(array $post, int $accountID): self {
            $dto = new self();
            
            $dto->accountID = $accountID;
            $dto->messageID = (int) ExploitPatch::remove($post['messageID'] ?? 0);
            $dto->isSender = isset($post['isSender']) ? 1 : 0;

            return $dto;
        }
    }