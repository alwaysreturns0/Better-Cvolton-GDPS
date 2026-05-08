<?php
    require_once __DIR__ . '/../lib/ExploitPatch.php';

    class MessageDeleteDTO {
        public int $accountID = 0;
        public int $messageID = 0;
        public string $messages = '';

        public static function from_request(array $post, int $accountID): self {
            $dto = new self();
            
            $dto->accountID = $accountID;
            $dto->messageID = (int) ExploitPatch::remove($post['messageID'] ?? 0);
            $dto->messages = ExploitPatch::numbercolon($post['messages'] ?? '');

            return $dto;
        }
    }