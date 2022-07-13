<?php

namespace qpi\minigame\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use qpi\minigame\game\GameManager;

class MainEventListener implements Listener {

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $gameManager = GameManager::getInstance();
        $game = $gameManager->getLastEnteredGame($player);

        if ($game === null) {
            GameManager::teleportToSpawn($player);
            $player->sendMessage("Добро пожаловать на сервер Dragonestia!");
        } else {
            $gameManager->enterGame($player, $game, true);
        }


        $event->setJoinMessage("");
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        $gameManager = GameManager::getInstance();
        if ($gameManager->isInLobby($player)) $gameManager->leaveFromLobby($player);
        if ($gameManager->isInGame($player)) {
            $gameManager->leaveFromGame($player);
        }

        $event->setQuitMessage("");
    }
}