<?php
    require_once 'LevelDTO.php';
    require_once '../lib/XORCipher.php';
    require_once '../lib/generateHash.php';
    require_once '../lib/exploitPatch.php';

    class LevelDownloadDTO extends LevelDTO {
        public string $levelDesc            = '';
        public string $levelString          = '';
        public string $extraString          = '';
        public string $settingsString       = '';
        
        public string $songIDs              = '';
        public string $sfxIDs               = '';
        public string $levelInfo            = '';
        
        public int $wt                      = 0;
        public int $wt2                     = 0;
        public int $ts                      = 0;
        public int $secret                  = 0;
        public int $password                = 0;

        public bool $daily                  = false;
        public int $feaID                   = 0;
        public bool $extras                 = false;
        public bool $inc                    = false;
        public string $hostname             = '';

        public static function from_request(array $post, string $hostname): self {
            $dto = new self();

            $dto->levelID = (int) ExploitPatch::remove($post['levelID'] ?? 0);
            $dto->inc = !empty($post['inc']) && $post['inc'];
            $dto->extras = !empty($post['extras']) && $post['extras'];
            $dto->hostname = $hostname;
            $dto->gameVersion = !empty($post['gameVersion']) ? (int) ExploitPatch::remove($post['gameVersion']) : 1;
            $dto->binaryVersion = !empty($post['binaryVersion']) ? (int) ExploitPatch::remove($post['binaryVersion']) : 0;
            
            return $dto;
        }

        public static function download(array $row): self {
            $dto = new self();

            $base = LevelDTO::get_levels($row);
            foreach(get_object_vars($base) as $key => $value) {
                $dto->$key = $value;
            }

            $fields = [
                'levelDesc', 'levelString', 'extraString', 'levelInfo', 'settingsString', 'songIDs', 'sfxIDs',
                'wt', 'wt2', 'ts', 'secret', 'password'
            ];
            foreach($fields as $field) {
                if (isset($row[$field])) {
                    $dto->$field = is_numeric($row[$field]) ? (int) $row[$field] : $row[$field];
                };
            }
            
            return $dto;
        }

        public function set_daily_data(int $feaID, int $dailyType): void {
            $this->daily = true;

            switch($dailyType) {
                case -1: 
                    $this->feaID = $feaID;
                    break;

                case -2:
                    $this->feaID = $feaID + 100001;
                    break;

                case -3:
                    $this->feaID = $feaID + 200001;
                    break;
            }
        }

        public function to_response(): string {
            $uploadDate = date('d/m/Y', $this->uploadDate);
            $updateDate = date('d/m/Y', $this->updateDate);

            $password = $this->password ?: 1;
            $xor = $password;
            if ($this->gameVersion > 19 && $password != 0) {
                $xor = base64_encode(XORCipher::cipher($password, 26364));
            }

            $levelDesc = $this->levelDesc;
            if ($this->gameVersion <= 18) {
                $levelDesc = base64_decode($levelDesc);
            }

            $levelString = $this->levelString;
            if ($this->gameVersion > 18 && str_starts_with($levelString, 'kS1')) {
                $levelString = base64_encode(gzcompress($levelString));
                $levelString = str_replace(['/', '+'], ['_', '-'], $levelString);
            }

            $response = "1:{$this->levelID}" .
                    ":2:{$this->levelName}" .
                    ":3:{$levelDesc}" .
                    ":4:{$levelString}" .
                    ":5:{$this->levelVersion}" .
                    ":6:{$this->userID}" .
                    ":8:10" .
                    ":9:{$this->starDifficulty}" .
                    ":10:{$this->downloads}" .
                    ":11:1" .
                    ":12:{$this->audioTrack}" .
                    ":13:{$this->gameVersion}" .
                    ":14:{$this->likes}" .
                    ":17:{$this->starDemon}" .
                    ":43:{$this->starDemonDiff}" .
                    ":25:{$this->starAuto}" .
                    ":18:{$this->starStars}" .
                    ":19:{$this->starFeatured}" .
                    ":42:{$this->starEpic}" .
                    ":45:{$this->objects}" .
                    ":15:{$this->levelLength}" .
                    ":30:{$this->original}" .
                    ":31:{$this->twoPlayer}" .
                    ":28:{$uploadDate}" .
                    ":29:{$updateDate}" .
                    ":35:{$this->songID}" .
                    ":36:{$this->extraString}" .
                    ":37:{$this->coins}" .
                    ":38:{$this->starCoins}" .
                    ":39:{$this->requestedStars}" .
                    ":46:{$this->wt}" .
                    ":47:{$this->wt2}" .
                    ":48:{$this->settingsString}" .
                    ":40:{$this->isLDM}" .
                    ":27:{$xor}" .
                    ":52:{$this->songIDs}" .
                    ":53:{$this->sfxIDs}" .
                    ":57:{$this->ts}";

            if ($this->daily) $response .= ":41:{$this->feaID}";
            if ($this->extras) $response .= ":26:{$this->levelInfo}";

            $response .= "#" . GenerateHash::genSolo($levelString) . "#";
            $hashString = "{$this->userID},{$this->starStars},{$this->starDemon},{$this->levelID},{$this->starCoins},{$this->starFeatured},{$password},{$this->feaID}";
            $response .= GenerateHash::genSolo2($hashString);

            if ($this->daily) $response .= "#{$this->userID}:{$this->userName}:{$this->extID}";
            if ($this->binaryVersion == 30) $response .= "#{$hashString}";

            return $response;
        }
    }
?>