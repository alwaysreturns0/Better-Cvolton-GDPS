<?php
    require_once __DIR__."/Main.php";
    
    require_once __DIR__."/lib/Database.php";
    require_once __DIR__."/lib/Lib.php";
    require_once __DIR__."/data/AccountCommentDTO.php";
    require_once __DIR__."/data/AccountCommentUploadDTO.php";
    require_once __DIR__."/data/AccountCommentDeleteDTO.php";
    require_once __DIR__."/rep/AccountCommentRepository.php";

    class AccountComments { 
        private AccountCommentRepository $repo;
        private Lib $lib;

        public function __construct() {
            $database = new Database();
            $this->repo = new AccountCommentRepository($database->open_connection());
            $this->lib = new Lib();
        }

        public function get_data(int $account_id, int $user_id, int $page): string {
            $comments = $this->repo->get_comments($user_id, $page);
            $comments_count = $this->repo->get_count($user_id);

            if ($comments == 0) return '#0:0:0';

            foreach ($comments as $comment) {
                if ($comment['commentID'] != 0) 
                {
                    $upload_date = $this->lib->make_time($comment['timestamp']);
                    $data = AccountCommentDTO::from_row($comment);
                    $comment_string .= $data->to_response() . "|";
                }
            }

            $comment_string = substr($comment_string, 0, -1);
            return $comment_string . "#" . $comments_count . ":" . ($page * 10) . ":10";
        }

        public function upload_comment(AccountCommentUploadDTO  $data): string {
            $this->repo->create($data);
            return "1";
        }

        public function delete_Comment(AccountCommentDeleteDTO $data): string {
            if ($data->hasPermission) {
                $this->repo->delete_own($data);
            } else { 
                $this->repo->delete_any($data);
            }
            
            return "1";
        }
    }
?>