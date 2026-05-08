<?php
    require_once __DIR__ . '/../lib/ExploitPatch.php';

    class MessageGetDTO {
        public int $accountID = 0;
        public int $page = 0;
        public int $getSent = 0;

        public static function from_request(array $post, int $accountID): self {
            $dto = new self();
            
            $dto->accountID = $accountID;
            $dto->page = (int) ExploitPatch::remove($post['page'] ?? 0);
            $dto->getSent = isset($post['getSent']) ? 1 : 0;

            return $dto;
        }
    }