<?php
    require_once __DIR__."/Main.php";
    
    require_once __DIR__."/lib/Database.php";
    require_once __DIR__."/lib/Lib.php";
    require_once __DIR__."/rep/MessageRepository.php";
    require_once __DIR__."/data/MessageDTO.php";
    require_once __DIR__."/data/MessageUploadDTO.php";
    require_once __DIR__."/data/MessageDownloadDTO.php";
    require_once __DIR__."/data/MessageDeleteDTO.php";
    require_once __DIR__."/data/MessageGetDTO.php";

    class Message {
        private MessageRepository $repo;
        private Lib $Lib;

        public function __construct() {
            $db = new Database();
            $this->repo = new MessageRepository($db->open_connection());
            $this->Lib = new Lib();
        }

        public function download(MessageDownloadDTO $dto): string {
            $messageData = $this->repo->get_message($dto->messageID, $dto->accountID);
    
            if (!$messageData) return "-1";

            if (empty($dto->isSender)) {
                $this->repo->mark_as_read($dto->messageID, $dto->accountID);
                $accountID = $messageData['accID'];
                $isSender = 0;
            } else {
                $accountID = $messageData['toAccountID'];
                $isSender = 1;
            }

            $userData = $this->repo->get_user_by_extid($accountID);
            if (!$userData) return "-1";
            
            $uploadDate = $this->Lib->make_time($messageData["timestamp"]);
            $message = MessageDTO::from_row($messageData, $uploadDate);

            return $message->to_download_string($userData, $isSender);
        }

        public function upload(MessageUploadDTO $dto): string {
            if ($dto->accountID == $dto->toAccountID) return "-1";

            $userName = $this->repo->get_user_name($dto->accountID);
            if (!$userName) return "-1";
            
            $dto->userName = $userName;

            $messageMs = $this->repo->get_message_setting($dto->accountID);
            if ($messageMs === 2) return "-1";

            $isBlocked = $this->repo->is_blocked($dto->accountID, $dto->toAccountID);
            $isFriend = $this->repo->is_friend($dto->accountID, $dto->toAccountID);

            if (!$isBlocked && ($messageMs === null || $isFriend)) {
                $this->repo->create($dto);
                return "1";
            }

            return "-1";
        }
        
        public function getData(MessageGetDTO $dto): string {
            $messageFetchAll = $this->repo->get_messages($dto->accountID, $dto->page, $dto->getSent);
            $messageCount = $this->repo->get_messages_count($dto->accountID, $dto->getSent);

            if ($messageCount == 0) return "-2"; 

            $messageString = "";

            foreach ($messageFetchAll as $messages) {
                if ($messages["messageID"] != 0) {
                    $uploadDate = $this->Lib->make_time($messages["timestamp"]);
                    $message = MessageDTO::from_row($messages, $uploadDate);
                    
                    $accountID = ($dto->getSent == 1) ? $messages["toAccountID"] : $messages["accID"];
                    $userData = $this->repo->get_user_by_extid($accountID);
                    
                    if ($userData) {
                        $messageString .= $message->to_string($dto->getSent, $userData) . "|";
                    }
                }
            }

            $messageString = substr($messageString, 0, -1);
            return $messageString . "#" . $messageCount . ":" . ($dto->page * 10) . ":10";
        }

        public function delete(MessageDeleteDTO $dto): string {
            if (!empty($dto->messages)) {
                $this->repo->delete_by_ids($dto->messages, $dto->accountID);
            } else {
                $this->repo->delete_by_id($dto->messageID, $dto->accountID);
            }

            return "1";
        }
    }