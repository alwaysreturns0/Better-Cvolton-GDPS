<?php
    class AccountCommentDTO {
        public int $commentID = 0;
        public int $userID = 0;
        public string $userName = '';
        public string $comment = '';
        public int $likes = 0;
        public int $isSpam = 0;
        public int $timestamp = 0;
        public string $uploadDate = '';

        public static function from_row($row): self {
            $dto = new self();

            $dto->commentID = (int)$row['commentID'];
            $dto->userID = (int)$row['userID'];
            $dto->userName = $row['userName'];
            $dto->comment = $row['comment'];
            $dto->likes = (int)$row['likes'];
            $dto->isSpam = (int)$row['isSpam'];
            $dto->timestamp = (int)$row['timestamp'];
            $dto->uploadDate = date("Y-m-d H:i:s", $dto->timestamp);

            return $dto;
        }

        public function to_response(): string {
            return "2~" . $this->comment . 
                   "~3~" . $this->userID . 
                   "~4~" . $this->likes . 
                   "~5~0~7~" . $this->isSpam . 
                   "~9~" . $this->uploadDate . 
                   "~6~" . $this->commentID;
        }
    }