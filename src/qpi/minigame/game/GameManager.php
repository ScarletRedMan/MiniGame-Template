<?php

namespace qpi\minigame\game;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class GameManager {
    use SingletonTrait;

    private array $games = [];
    private array $lobbies = [];
    private array $officialLobbies = [];
    private array $players = [];
    private array $playersAndGames = [];

    private function __construct() {

    }

    public function createNewLobby(Player $player, bool $custom): void {
        $game = new Game($custom? -1 : 10, $custom);
        $lobbyId = $this->generateLobbyId();
        $lobby = new Lobby($lobbyId, $custom, $game);
        $this->lobbies[$lobbyId] = $lobby;
        $this->joinToLobby($player, $lobbyId);
        if ($custom) $lobby->setOwner($player);
        else $this->officialLobbies[$lobbyId] = $lobby;
    }

    public function pickLobby(Player $player): void {
        foreach ($this->officialLobbies as $lobby) {
            if ($lobby->getStatus() !== Lobby::STATUS_WAITING) continue;

            $this->joinToLobby($player, $lobby->getId());
            return;
        }

        $this->createNewLobby($player, false);
    }

    public function findLobby(string $lobbyId): ?Lobby {
        if (!isset($this->lobbies[$lobbyId])) return null;
        $lobby = $this->lobbies[$lobbyId];
        return $lobby->isCustom()? $lobby : null;
    }

    public function joinToLobby(Player $player, string $lobbyId): void {
        $this->players[$player->getXuid()] = $lobbyId;
        $this->lobbies[$lobbyId]->join($player);
    }

    public function leaveFromLobby(Player $player): void {
        $lobbyId = $this->players[$player->getXuid()];
        unset($this->players[$player->getXuid()]);

        $lobby = $this->lobbies[$lobbyId];
        if ($lobby->quit($player)) {
            unset($this->lobbies[$lobbyId]);
            if (!$lobby->isCustom()) unset($this->officialLobbies[$lobbyId]);
        }
    }

    public function isInLobby(Player $player): bool {
        return isset($this->players[$player->getXuid()]);
    }

    public function getLobbyByPlayer(Player $player): Lobby {
        return $this->lobbies[$this->players[$player->getXuid()]];
    }

    public function startGame(Lobby $lobby): void {
        unset($this->lobbies[$lobby->getId()]);
        if (!$lobby->isCustom()) unset($this->officialLobbies[$lobby->getId()]);

        $game = $lobby->getGame();
        foreach ($lobby->getPlayers() as $player) {
            unset($this->players[$player->getXuid()]);
            $this->enterGame($player, $game);
        }
        $this->games[] = $game;
        $game->start();
    }

    public function enterGame(Player $player, Game $game, bool $resume = false): void {
        $game->join($player, $resume);
        $this->playersAndGames[$player->getXuid()] = $game;
    }

    public function leaveFromGame(Player $player, bool $end = false): void {
        $game = $this->playersAndGames[$player->getXuid()];
        unset($this->playersAndGames[$player->getXuid()]);

        if ($end) return;
        $game->quit($player);
    }

    public function isInGame(Player $player): bool {
        return isset($this->playersAndGames[$player->getXuid()]);
    }

    public function tickLobbies(): void {
        foreach ($this->lobbies as $lobby) {
            $lobby->tick();
        }
    }

    public function tickGames(): void {
        foreach ($this->games as $key => $game) {
            if ($game->tick()) unset($this->games[$key]);
        }
    }

    private function generateLobbyId(): string {
        while (true) {
            $result = mt_rand(1000000, 9999999);
            if (!isset($this->lobbies[(string) $result])) return (string) $result;
        }
    }

    public function getLastEnteredGame(Player $player): ?Game {
        foreach ($this->games as $game) {
            if ($game->isPlayerEntered($player)) return $game;
        }

        return null;
    }

    public static function teleportToSpawn(Player $player): void {
        $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
    }
}