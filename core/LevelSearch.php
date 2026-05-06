<?php
    require_once __DIR__."/Level.php";
    require_once __DIR__."/Main.php";
    
    require_once __DIR__."/lib/Database.php";
    require_once __DIR__."/lib/generateHash.php";
    require_once __DIR__."/data/LevelSearchDTO.php";
    require_once __DIR__."/rep/SearchRepository.php";
    
    class LevelSearch {
        private PDO $connection; 
        private Main $Main;
        private SearchRepository $repo;

        public function __construct() {
            $db = new Database();
            $this->connection = $db->open_connection();
            $this->Main = new Main();
            $this->repo = new SearchRepository($this->connection);
        }

        public function search(LevelSearchDTO $data): string {
            $game_version = $data->gameVersion;
            if ($game_version == 20 && $data->binaryVersion > 27) $game_version++;
            if ($game_version == 0) $game_version = 30;

            $level_multi_string = [];
            $level_string = "";
            $song_string = "";
            $user_string = "";

            $level_result = $this->repo->search($data);
            $total_lvl_count = $this->repo->count($data);

            $is_search_ID = (is_numeric($data->string && !empty($data->string) && ($data->type == 0 || $data->type == 15)));
            
            foreach($level_result as $level) {
                if (!$level["levelID"]) continue;
                
                if ($level['unlisted'] == 1 && $is_search_ID) {
                    $accountID = $data->accountID;
                    if (!$this->Main->is_friends($accountID, $level["extID"]) && $accountID != $level["extID"]) continue;
                }

                $level_multi_string[] = [
                    "levelID" => $level["levelID"],
                    "stars" => $level["starStars"],
                    'coins' => $level["starCoins"]
                ];
                
                $gauntlet_prefix = !empty($data->gauntlet) ? "44:" . $data->gauntlet . ":" : "";
                
                $level_string .= $gauntlet_prefix . 
                    "1:" . $level["levelID"] . 
                    ":2:" . $level["levelName"] . 
                    ":5:" . $level["levelVersion"] . 
                    ":6:" . $level["userID"] . 
                    ":8:10:9:" . $level["starDifficulty"] . 
                    ":10:" . $level["downloads"] . 
                    ":12:" . $level["audioTrack"] . 
                    ":13:" . $level["gameVersion"] . 
                    ":14:" . $level["likes"] . 
                    ":17:" . $level["starDemon"] . 
                    ":43:" . $level["starDemonDiff"] . 
                    ":25:" . $level["starAuto"] . 
                    ":18:" . $level["starStars"] . 
                    ":19:" . $level["starFeatured"] . 
                    ":42:" . $level["starEpic"] . 
                    ":45:" . $level["objects"] . 
                    ":3:" . $level["levelDesc"] . 
                    ":15:" . $level["levelLength"] . 
                    ":30:" . $level["original"] . 
                    ":31:" . $level['twoPlayer'] . 
                    ":37:" . $level["coins"] . 
                    ":38:" . $level["starCoins"] . 
                    ":39:" . $level["requestedStars"] . 
                    ":46:1:47:2:40:" . $level["isLDM"] . 
                    ":35:" . $level["songID"] . "|";

                if ($level["songID"] != 0) {
                    $song = $this->Main->get_song_string($level);
                    if ($song) $song_string .= $song . "~:~";
                }
                
                $user_sring .= $this->Main->get_user_string($level) . "|";
            }

            $level_string   = substr($level_string, 0, -1);
            $user_sring     = substr($user_sring, 0, -1);
            $song_string    = substr($song_string, 0, -1);

            $e1 = "";
            if ($game_version > 18) $e1 = "#" . $song_string;

            return $level_string . "#" . $user_string . $e1 . "#" . $total_lvl_count . ":" . $data->page . ":10#" . GenerateHash::genMulti($level_multi_string);
        }
    }
