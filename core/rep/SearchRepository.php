<?php
    require_once __DIR__ . '/../data/LevelSearchDTO.php';

    class SearchRepository {
        private PDO $db;

        public function __construct(PDO $connection) {
            $this->db = $connection;
        }

        public function search(LevelSearchDTO $data): array {
            $query = $this->build_query($data);
            $query .= " LIMIT 10 OFFSET " . $data->page;

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function count(LevelSearchDTO $data): int {
            $query = $this->build_count_query($data);
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        }

        private function build_query(LevelSearchDTO $data): string {
            $parts = $this->build_parts($data);

            $sql = "SELECT levels.*, songs.ID, songs.name, songs.authorID, songs.authorName, songs.size, songs.isDisabled, songs.download, users.userName, users.extID ";
            $sql .= "FROM levels LEFT JOIN songs ON levels.songID = songs.ID LEFT JOIN users ON levels.userID = users.userID ";
            $sql .= $parts['joins'];

            if (!empty($parts['where'])) {
                $sql .= " WHERE " . implode(" AND ", $parts['where']);
            }

            if (!empty($parts['order'])) {
                $sql .= " ORDER BY " . $parts['order'];
            }

            return $sql;
        }

        private function build_count_query(LevelSearchDTO $data): string {
            $parts = $this->build_parts($data);

            $sql = "SELECT count(*) FROM levels LEFT JOIN users ON levels.userID = users.userID ";
            $sql .= $parts['joins'];

            if (!empty($part['where'])) {
                $sql .= " WHERE " . implode(" AND ", $parts['where']);
            }

            return $sql;
        }

        private function build_parts(LevelSearchDTO $data): array {
            $params = [];
            $epic_params = [];
            $joins = '';
            $order = "uploadDate DESC";
            $is_gauntlet = false;
            $is_search_ID = false;

            $game_version = $data->gameVersion;
            if ($game_version == 20 && $data->binaryVersion > 27) {
                $game_version++;
            }

            $params[] = ($game_version == 0) ? "levels.gameVersion <= 18" : "levels.gameVersion <= '$game_version'";
            if (!empty($data->original)) $params[] = "original = 0";
            if (!empty($data->coins)) $params[] = "starCoins = 1 AND NOT levels.coins = 0";

            if (!empty($data->uncompleted) && !empty($data->completedLevels)) {
                $params[] = "NOT levelID IN (" . $data->completedLevels . ")";
            }
            if (!empty($data->onlyCompleted) && !empty($data->completedLevels)) {
                $params[] = "levelID IN (" . $data->completedLevels . ")";
            }

            if (!empty($data->song)) {
                if (empty($data->customSong)) {
                    $song = $data->song - 1;
                    $params[] = "audioTrack = '$song' AND songID = 0";
                } else {
                    $params[] = "songID = '" . $data->song . "'";
                }
            }

            if (!empty($data->twoPlayer) && $data->twoPlayer == 1) $params[] = "twoPlayer = 1";
            if (!empty($data->star)) $params[] = "NOT starStars = 0";
            if (!empty($data->noStar)) $params[] = "starStars = 0";

            if (!empty($data->gauntlet)) {
                $is_gauntlet = true;
                $order = "starStars";
                
                $gauntlet_result = $this->db->prepare("SELECT * FROM gauntlets WHERE ID = :gauntlet");
                $gauntlet_result->execute([':gauntlet' => $data->gauntlet]);
                $actualGauntlet = $gauntlet_result->fetch();
                
                if ($actualGauntlet) {
                    $str = $actualGauntlet["level1"] . "," . $actualGauntlet["level2"] . "," . $actualGauntlet["level3"] . "," . $actualGauntlet["level4"] . "," . $actualGauntlet["level5"];
                    $params[] = "levelID IN ($str)";
                }
            }

            $len = $data->len ?: '-';
            if ($len != "-") {
                $params[] = "levelLength IN ($len)";
            }

            if (!empty($data->starFeatured)) $epicParams[] = "starFeatured = 1";
            if (!empty($data->starEpic)) $epicParams[] = "starEpic = 1";
            if (!empty($data->mythic)) $epicParams[] = "starEpic = 2";
            if (!empty($data->legendary)) $epicParams[] = "starEpic = 3";

            if (!empty($epicParams)) {
                $params[] = "(" . implode(" OR ", $epicParams) . ")";
            }

            $params[] = $this->get_diff($data);
            $type_data = $this->get_type($data);

            $params[] = $type_data['params'];
            $order = $type_data['order'];
            $is_search_ID = $type_data['is_search_ID'];
            $unlisted = $type_data['unlisted'];

            if (is_numeric($data->string) && $is_search_ID && $unlisted != null) {
                $params[] = 'unlisted = ' . $unlisted;
            }

            if ($is_gauntlet) $order = "gauntletLevel ASC";

            return [
                'where' => $params,
                'joins' => $joins,
                'order' => $order
            ];
        }

        private function get_diff(LevelSearchDTO $data): array {
            $params = [];

            switch($data->difficulty) {
                case -1:
                    $params[] = "starDifficulty = '0'";
                    break;

                case -2: 
                    $params[] = "starDemon = 1";

                    switch($data->demonFilter) {
                        case 1: $params[] = "starDemonDiff = '3'"; break;
                        case 2: $params[] = "starDemonDiff = '4'"; break;
                        case 3: $params[] = "starDemonDiff = '0'"; break;
                        case 4: $params[] = "starDemonDiff = '5'"; break;
                        case 5: $params[] = "starDemonDiff = '6'"; break;
                    }

                    break;
                case -3: 
                    $params[] = "starAuto = '1'";

                
                case "-": break;

                default: 
                    $difficulty = str_replace(",", "0,", $data->difficulty) . "0";
                    $params[] = "starDifficulty IN ($difficulty) AND starAuto = '0' AND starDemon = '0'";
                    break;
            }

            return $params;
        }
        
        private function get_type(LevelSearchDTO $data): array {
            $params = [];
            $order = [];
            $is_search_ID = false;
            $unlisted = null;

            switch ($data->type) {
                case 0:
                case 15:
                    if (!empty($data->string)) {
                        if (is_numeric($data->string)) {
                            $params[] = "levelID = '" . $data->string . "'";
                            $is_search_ID = true;

                            $unlisted = $this->db->prepare("SELECT unlisted FROM levels WHERE levelID = " . (int)$data->string);
                            $unlisted->execute();
                            $unlisted = $unlisted->fetchColumn();
                        } else {
                            $params[] = "levelName LIKE '%" . $data->string . "%'";
                        }
                    }
                    $order = "likes DESC";
                    break;
                case 1:
                    $order = "downloads DESC";
                    break;
                case 2:
                    $order = "likes DESC";
                    break;
                case 3:
                    $uploadDate = time() - (7 * 24 * 60 * 60);
                    $params[] = "uploadDate > $uploadDate";
                    $order = "likes DESC";
                    break;
                case 5:
                    $params[] = ($data->string == 0) ? "levels.userID = '" . $data->accountID . "'" : "levels.userID = '" . $data->string . "'";
                    break;
                case 6:
                case 17:
                    $params[] = ($data->gameVersion > 21) ? "NOT starFeatured = 0 OR NOT starEpic = 0" : "NOT starFeatured = 0";
                    $order = "rateDate DESC, uploadDate";
                    break;
                case 16:
                    $params[] = "NOT starEpic = 0";
                    $order = "rateDate DESC, uploadDate";
                    break;
                case 7:
                    $params[] = "objects > 9999";
                    break;
                case 10:
                case 19:
                    $params[] = "levelID IN (" . $data->string . ")";
                    $order = false;
                    break;
                case 11:
                    $params[] = "NOT starStars = 0";
                    $order = "rateDate DESC, uploadDate";
                    break;
                case 12:
                    $params[] = "users.extID IN (" . $data->followed . ")";
                    break;
                case 13:
                    $params[] = "users.extID IN (" . $data->followed . ")";
                    break;
                case 21:
                    $joins = "INNER JOIN dailyfeatures ON levels.levelID = dailyfeatures.levelID";
                    $params[] = "dailyfeatures.type = 0";
                    $order = "dailyfeatures.feaID DESC";
                    break;
                case 22:
                    $joins = "INNER JOIN dailyfeatures ON levels.levelID = dailyfeatures.levelID";
                    $params[] = "dailyfeatures.type = 1";
                    $order = "dailyfeatures.feaID DESC";
                    break;
                case 23:
                    $joins = "INNER JOIN dailyfeatures ON levels.levelID = dailyfeatures.levelID";
                    $params[] = "dailyfeatures.type = 2";
                    $order = "dailyfeatures.feaID DESC";
                    break;
                case 25:
                    $params = ["levelID IN (" . $data->string . ")"];
                    break;
                case 27:
                    $joins = "INNER JOIN sendLevel ON levels.levelID = sendLevel.levelID";
                    $params[] = "sendLevel.isRated = 0";
                    $order = "sendLevel.timestamp DESC";
                    break;
            }

            return array('params' => $params, 'order' => $order, 'is_search_ID' => $is_search_ID, 'unlisted' => $unlisted);
        }

        
    }
?>