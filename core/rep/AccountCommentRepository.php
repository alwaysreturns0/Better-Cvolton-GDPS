<?php
    require_once __DIR__ . '/../data/AccountCommentDTO.php';
    require_once __DIR__ . '/../data/AccountCommentUploadDTO.php';
    require_once __DIR__ . '/../data/AccountCommentDeleteDTO.php';

    class AccountCommentRepository {
        private PDO $db;

        public function __construct(PDO $db) {
            $this->db = $db;
        }

       public function delete_any(AccountCommentDeleteDTO $data): bool {
            $stmt = $this->db->prepare("
                DELETE FROM acccomments 
                WHERE commentID = :commentID 
                LIMIT 1
            ");
            
            return $stmt->execute([':commentID' => $data->commentID]);
        }

        public function delete_own(AccountCommentDeleteDTO $data): bool {
           $stmt = $this->db->prepare("
                DELETE FROM acccomments 
                WHERE commentID = :commentID AND userID = :userID 
                LIMIT 1
            ");
            
            return $stmt->execute([
                ':commentID' => $data->commentID,
                ':userID' => $data->userID
            ]);
        }

        public function create(AccountCommentUploadDTO $data): bool {
            $stmt = $this->db->prepare("
                INSERT INTO acccomments (userName, comment, userID, timeStamp) 
                VALUES (:userName, :comment, :userID, :uploadDate)
            ");
            
            return $stmt->execute([
                ':userName' => $data->userName,
                ':comment' => $data->comment,
                ':userID' => $data->userID,
                ':uploadDate' => $data->uploadDate
            ]);
        }

        public function get_count(int $userID): int {
            $stmt = $this->db->prepare("
                SELECT count(*) FROM acccomments WHERE userID = :userID
            ");
            $stmt->execute([':userID' => $userID]);
            
            return (int) $stmt->fetchColumn();
        }

        public function get_comments(int $userID, int $page): array {
            $offset = $page * 10;
            
            $stmt = $this->db->prepare("
                SELECT userID, commentID, comment, likes, isSpam, timestamp 
                FROM acccomments 
                WHERE userID = :userID 
                ORDER BY timeStamp DESC 
                LIMIT 10 OFFSET $offset
            ");
            $stmt->execute([':userID' => $userID]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
