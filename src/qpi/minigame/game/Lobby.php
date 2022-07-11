<?php

namespace qpi\minigame\game;

use pocketmine\player\Player;

class Lobby {

    public const STATUS_WAITING = 0;
    public const STATUS_PREPARING = 1;
    public const STATUS_PLAYING = 2;

    private string $id;
    private bool $custom;
    private Game $game;
    private ?Player $owner;
    private array $players = [];
    private int $status;
    private int $timeBeforeStart;

    public function __construct(string $id, bool $custom, Game $game) {
        $this->id = $id;
        $this->custom = $custom;
        $this->game = $game;
        $this->owner = null;
        $this->status = self::STATUS_WAITING;
        $this->timeBeforeStart = 0;
    }

    public function getId(): string {
        return $this->id;
    }

    public function isCustom(): bool {
        return $this->custom;
    }

    public function getGame(): Game {
        return $this->game;
    }

    public function getOwner(): ?Player {
        return $this->owner;
    }

    public function getStatus(): int {
        return $this->status;
    }

    public function getPlayers(): array {
        return $this->players;
    }

    public function setOwner(Player $player): void {
        $this->owner = $player;
        $player->sendMessage("§eВы назначаетесь лидером лобби. Настройка лобби через команду §6/lobby§e.");
    }

    public function join(Player $player): void {
        $this->players[] = $player;

        foreach ($this->players as $target) {
            $target->sendMessage("§eИгрок {$player->getName()} присоединился к лобби.");
        }
        $player->sendMessage("Чтобы посмотреть параметры лобби используйте команду §2/lobby§f.");
        $this->onJoin($player);

        if (!$this->custom && count($this->players) >= $this->game->getMaxPlayers()) $this->tryStartGame();
    }

    public function quit(Player $player): bool {
        foreach ($this->players as $key => $target) {
            if ($player === $target) {
                unset($this->players[$key]);
                continue;
            }

            $player->sendMessage("§eИгрок {$player->getName()} покинул лобби.");
            if ($this->status === self::STATUS_PREPARING) $player->sendMessage("§eОтмена старта игры. Ожидание игроков...");
        }

        if ($this->status === self::STATUS_PREPARING) $this->status = self::STATUS_WAITING;

        $this->onQuit($player);
        GameManager::teleportToSpawn($player);
        return empty($this->players);
    }

    protected function onJoin(Player $player): void {
        //TODO: Действие при входе игрока в лобби
    }

    protected function onQuit(Player $player): void {
        //TODO: Действие при выходи игрока из лобби
    }

    public function tryStartGame(): void {
        $this->timeBeforeStart = 10;
        $this->status = self::STATUS_PREPARING;
    }

    public function tick(): void {
        if ($this->status === self::STATUS_PLAYING) return;

        if ($this->status === self::STATUS_PREPARING) {
            if (--$this->timeBeforeStart) {
                if ($this->timeBeforeStart !== 0) {
                    foreach ($this->players as $player) {
                        $player->sendTitle("§l§g{$this->timeBeforeStart}§r", "Старт игры...");
                    }
                }
            } else {
                $this->status = self::STATUS_PLAYING;
                GameManager::getInstance()->startGame($this);
            }
            return;
        }

        if ($this->custom) {
            foreach ($this->players as $player) {
                $player->sendTip("Ожидание старта лидером лобби");
            }
            return;
        }

        foreach ($this->players as $player) {
            $player->sendTip("Ожидание игроков: ". count($this->players) ."/{$this->game->getMaxPlayers()}");
        }
    }
}