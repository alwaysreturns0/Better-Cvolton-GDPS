<?php
    class LevelSearchDTO {
        public int $accountID = 0;
        public int $page = 0;
        public int $type = 0;
        public int $gameVersion = 0;
        public int $binaryVersion = 0;
        public int $difficulty = 0;
        public int $demonFilter = 0;
        public int $starFeatured = 0;
        public int $original = 0;
        public int $coins = 0;
        public int $starEpic = 0;
        public int $uncompleted = 0;
        public int $onlyCompleted = 0;
        public string $completedLevels = '';
        public int $song = 0;
        public int $customSong = 0;
        public int $twoPlayer = 0;
        public int $star = 0;
        public int $noStar = 0;
        public int $gauntlet = 0;
        public string $len = '';
        public int $legendary = 0;
        public int $mythic = 0;
        public string $followed = '';
        public string $string = '';

        public static function from_request(array $params, int $accountID = 0): self {
            $dto = new self();

            $dto->accountID = $accountID;
            $dto->page = (int)($params['page'] ?? 0);
            $dto->type = (int)($params['type'] ?? 0);
            $dto->gameVersion = (int)($params['gameVersion'] ?? 30);
            $dto->binaryVersion = (int)($params['binaryVersion'] ?? 0);
            $dto->difficulty = $params['diff'] ?? $params['difficulty'] ?? 0;
            $dto->demonFilter = (int)($params['demonFilter'] ?? 0);
            $dto->starFeatured = (int)($params['starFeatured'] ?? 0);
            $dto->original = (int)($params['original'] ?? 0);
            $dto->coins = (int)($params['coins'] ?? 0);
            $dto->starEpic = (int)($params['starEpic'] ?? 0);
            $dto->uncompleted = (int)($params['uncompleted'] ?? 0);
            $dto->onlyCompleted = (int)($params['onlyCompleted'] ?? 0);
            $dto->completedLevels = $params['completedLevels'] ?? '';
            $dto->song = (int)($params['song'] ?? 0);
            $dto->customSong = (int)($params['customSong'] ?? 0);
            $dto->twoPlayer = (int)($params['twoPlayer'] ?? 0);
            $dto->star = (int)($params['star'] ?? 0);
            $dto->noStar = (int)($params['noStar'] ?? 0);
            $dto->gauntlet = (int)($params['gauntlet'] ?? 0);
            $dto->len = $params['len'] ?? '';
            $dto->legendary = (int)($params['legendary'] ?? 0);
            $dto->mythic = (int)($params['mythic'] ?? 0);
            $dto->followed = $params['followed'] ?? '';
            $dto->string = $params['string'] ?? '';

            return $dto;
        }
    }