<?php

namespace qpi\minigame\game;

use pocketmine\player\Player;

class Game {

    public const STATUS_STARTED = 0;
    public const STATUS_ENDING = 1;
    public const STATUS_ENDED = 2;

    private int $maxPlayers;
    private bool $custom;
    private array $players = [];
    private int $status;
    private int $endingTime = 10;
    private array $playerProfiles = [];
    private int $j = 10; //Убрать

    public function __construct(int $maxPlayers, bool $custom) {
        $this->maxPlayers = $maxPlayers;
        $this->custom = $custom;
        $this->status = self::STATUS_STARTED;
    }

    public function getMaxPlayers(): int {
        return $this->maxPlayers;
    }

    public function isCustom(): bool {
        return $this->custom;
    }

    public function join(Player $player, bool $resume = false): void {
        $this->players[] = $player;

        if (!$resume) {
            $playerData = new PlayerData($player->getName());
            $this->playerProfiles[$player->getXuid()] = $playerData;
        } else {
            $playerData = $this->getPlayerData($player);
            $playerData->setName($player->getName());

            foreach ($this->players as $target) {
                $target->sendMessage("§eИгрок {$player->getName()} вернулся к игре.");
            }
        }
        $playerData->setPlayer($player);
        if ($resume) $playerData->loadStats();

        $this->onJoin($player, !$resume);
    }

    public function quit(Player $player): void {
        $playerData = $this->getPlayerData($player);

        foreach ($this->players as $key => $target) {
            if ($player === $target) {
                unset($this->players[$key]);
                continue;
            }

            $target->sendMessage("§eИгрок {$player->getName()} покинул игру.");
        }
        $this->onQuit($player);
        $playerData->saveStats();
        $player->getInventory()->clearAll();
        $playerData->setPlayer(null);

        if (empty($this->players)) $this->status = self::STATUS_ENDED;
    }

    public function isPlayerEntered(Player $player): bool {
        return isset($this->playerProfiles[$player->getXuid()]);
    }

    public function getPlayerData(Player $player): PlayerData {
        return $this->playerProfiles[$player->getXuid()];
    }

    public function start(): void {
        $this->onStart();
    }

    public function stop(): void {
        $this->status = self::STATUS_ENDING;
        $this->onEnd();
    }

    protected function onJoin(Player $player, bool $firstTime): void {
        //TODO: Дейсвие при входе в игру/возобновлении игры
        //ps: про возообновление игры имеется в виду, что если игрок вышел с сервера и зашел обратно
    }

    protected function onQuit(Player $player): void {
        //TODO: Действие при выходе игрока из сервера во время игры
    }

    protected function onStart(): void {
        foreach ($this->players as $player) {
            $player->sendTitle("Игра началась!");
        }

        //TODO: Действие при начале игры
    }

    protected function onEnd(): void {
        //TODO: Действие при окончании игры
    }

    protected function onDestroy(): void {
        $this->sendMessage("Game over!");
        //TODO: Действие при уничтожении игры
        //ps: Телепортация на спавн производится автоматически
    }

    public function tick(): bool {
        if ($this->status === self::STATUS_ENDED) {
            foreach ($this->players as $player) {
                GameManager::teleportToSpawn($player);
                GameManager::getInstance()->leaveFromGame($player, true);
            }
            $this->onDestroy();
            return true;
        }

        if ($this->status === self::STATUS_ENDING) {
            if ($this->endingTime-- <= 0) $this->status = self::STATUS_ENDED;
            return false;
        }

        $this->tickGame();
        return false;
    }

    protected function tickGame(): void {
        if ($this->j-- <= 0) $this->stop();

        //TODO: Цикил логики игры
    }

    public function sendMessage(string $message): void {
        foreach ($this->players as $player) $player->sendMessage($message);
    }
}