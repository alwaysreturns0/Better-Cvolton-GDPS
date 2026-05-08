<?php
    require_once __DIR__ . '/../data/MessageDTO.php';
    require_once __DIR__ . '/../data/MessageUploadDTO.php';
    require_once __DIR__ . '/../data/MessageDownloadDTO.php';
    require_once __DIR__ . '/../data/MessageDeleteDTO.php';
    require_once __DIR__ . '/../data/MessageGetDTO.php';

    class MessageRepository {
        private PDO $db;

        public function __construct(PDO $connection) {
            $this->db = $connection;
        }

        public function get_user_by_extid(int $accountID): ?array {
            $stmt = $this->db->prepare("
                SELECT userName, userID, extID 
                FROM users 
                WHERE extID = :accountID
            ");
            $stmt->execute([':accountID' => $accountID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        }

        public function get_user_name(int $accountID): ?string {
            $stmt = $this->db->prepare("
                SELECT userName 
                FROM users 
                WHERE extID = :accountID 
                ORDER BY userName DESC
            ");
            $stmt->execute([':accountID' => $accountID]);
            return $stmt->fetchColumn() ?: null;
        }

        public function is_blocked(int $accountID, int $toAccountID): bool {
            $stmt = $this->db->prepare("
                SELECT ID FROM blocks 
                WHERE person1 = :toAccountID AND person2 = :accountID
            ");
            $stmt->execute([':toAccountID' => $toAccountID, ':accountID' => $accountID]);
            return $stmt->fetch() !== false;
        }

        public function get_message_setting(int $accountID): ?int {
            $stmt = $this->db->prepare("
                SELECT mS FROM accounts 
                WHERE accountID = :accountID AND mS > 0
            ");
            $stmt->execute([':accountID' => $accountID]);
            $result = $stmt->fetchColumn();
            return $result !== false ? (int) $result : null;
        }

        public function is_friend(int $accountID, int $toAccountID): bool {
            $stmt = $this->db->prepare("
                SELECT ID FROM friendships 
                WHERE (person1 = :accountID AND person2 = :toAccountID) 
                   OR (person2 = :accountID AND person1 = :toAccountID)
            ");
            $stmt->execute([':accountID' => $accountID, ':toAccountID' => $toAccountID]);
            return $stmt->fetch() !== false;
        }

        public function create(MessageUploadDTO $dto): bool {
            $stmt = $this->db->prepare("
                INSERT INTO messages (subject, body, accID, userID, userName, toAccountID, secret, timestamp) 
                VALUES (:subject, :body, :accID, :userID, :userName, :toAccountID, :secret, :uploadDate)
            ");
            
            return $stmt->execute([
                ':subject' => $dto->subject,
                ':body' => $dto->body,
                ':accID' => $dto->accountID,
                ':userID' => $dto->userID,
                ':userName' => $dto->userName,
                ':toAccountID' => $dto->toAccountID,
                ':secret' => $dto->secret,
                ':uploadDate' => $dto->uploadDate
            ]);
        }

        public function get_messages(int $accountID, int $page, int $getSent): array {
            $offset = $page * 10;
            
            if ($getSent != 1) {
                $stmt = $this->db->prepare("
                    SELECT * FROM messages 
                    WHERE toAccountID = :accountID 
                    ORDER BY messageID DESC 
                    LIMIT 10 OFFSET $offset
                ");
            } else {
                $stmt = $this->db->prepare("
                    SELECT * FROM messages 
                    WHERE accID = :accountID 
                    ORDER BY messageID DESC 
                    LIMIT 10 OFFSET $offset
                ");
            }
            
            $stmt->execute([':accountID' => $accountID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function get_messages_count(int $accountID, int $getSent): int {
            if ($getSent != 1) {
                $stmt = $this->db->prepare("
                    SELECT count(*) FROM messages WHERE toAccountID = :accountID
                ");
            } else {
                $stmt = $this->db->prepare("
                    SELECT count(*) FROM messages WHERE accID = :accountID
                ");
            }
            
            $stmt->execute([':accountID' => $accountID]);
            return (int) $stmt->fetchColumn();
        }

        public function get_message(int $messageID, int $accountID): ?array {
            $stmt = $this->db->prepare("
                SELECT accID, toAccountID, timestamp, userName, messageID, subject, isNew, body 
                FROM messages 
                WHERE messageID = :messageID AND (accID = :accountID OR toAccountID = :accountID) 
                LIMIT 1
            ");
            $stmt->execute([':messageID' => $messageID, ':accountID' => $accountID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        }

        public function mark_as_read(int $messageID, int $accountID): void {
            $stmt = $this->db->prepare("
                UPDATE messages SET isNew = 1 
                WHERE messageID = :messageID AND toAccountID = :accountID
            ");
            $stmt->execute([':messageID' => $messageID, ':accountID' => $accountID]);
        }

        public function delete_by_ids(string $messageIDs, int $accountID): void {
            $stmt = $this->db->prepare("
                DELETE FROM messages 
                WHERE messageID IN ($messageIDs) AND accID = :accountID 
                LIMIT 10
            ");
            $stmt->execute([':accountID' => $accountID]);
            
            $stmt = $this->db->prepare("
                DELETE FROM messages 
                WHERE messageID IN ($messageIDs) AND toAccountID = :accountID 
                LIMIT 10
            ");
            $stmt->execute([':accountID' => $accountID]);
        }

        public function delete_by_id(int $messageID, int $accountID): void {
            $stmt = $this->db->prepare("
                DELETE FROM messages 
                WHERE messageID = :messageID AND accID = :accountID 
                LIMIT 1
            ");
            $stmt->execute([':messageID' => $messageID, ':accountID' => $accountID]);
            
            $stmt = $this->db->prepare("
                DELETE FROM messages 
                WHERE messageID = :messageID AND toAccountID = :accountID 
                LIMIT 1
            ");
            $stmt->execute([':messageID' => $messageID, ':accountID' => $accountID]);
        }
    }