<?php
    class MessageDTO {
        public int $messageID = 0;
        public int $accID = 0;
        public int $toAccountID = 0;
        public int $userID = 0;
        public string $userName = '';
        public string $subject = '';
        public string $body = '';
        public int $isNew = 0;
        public int $timestamp = 0;
        public string $uploadDate = '';

        public static function from_row(array $row, string $uploadDate): self {
            $dto = new self();

            $dto->messageID     = (int)$row['messageID'];
            $dto->accID         = (int)$row['accID'];
            $dto->toAccountID   = (int)$row['toAccountID'];
            $dto->userID        = (int)$row['userID'];
            $dto->userName      = (string) $row['userName'] ?? '';
            $dto->subject       = (string) $row['subject'] ?? '';
            $dto->body          = (string) $row['body'] ?? '';
            $dto->isNew         = (int)$row['isNew'];
            $dto->timestamp     = (int)$row['timestamp'];
            $dto->uploadDate    = $uploadDate;

            return $dto;
        }
        
        public function to_string(int $getSent, array $userData): string {
            return "6:" . $userData["userName"] . 
                   ":3:" . $userData["userID"] . 
                   ":2:" . $userData["extID"] . 
                   ":1:" . $this->messageID . 
                   ":4:" . $this->subject . 
                   ":8:" . $this->isNew . 
                   ":9:" . $getSent . 
                   ":7:" . $this->uploadDate;
        }

        public function to_download_string(array $userData, int $isSender): string {
            return "6:" . $userData["userName"] . 
                   ":3:" . $userData["userID"] . 
                   ":2:" . $userData["extID"] . 
                   ":1:" . $this->messageID . 
                   ":4:" . $this->subject . 
                   ":8:" . $this->isNew . 
                   ":9:" . $isSender . 
                   ":5:" . $this->body . 
                   ":7:" . $this->uploadDate;
        }
    }