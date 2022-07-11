<?php

namespace qpi\minigame;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use qpi\minigame\command\LobbyCommand;
use qpi\minigame\command\PlayCommand;
use qpi\minigame\game\GameManager;
use qpi\minigame\task\GameTickerTask;
use qpi\minigame\task\LobbyTickerTask;

class Main extends PluginBase implements Listener {

    protected function onEnable(): void {
        $this->getServer()->getCommandMap()->registerAll("Invasion", [
            new LobbyCommand(),
            new PlayCommand(),
        ]);

        $pluginManager = $this->getServer()->getPluginManager();
        $pluginManager->registerEvents($this, $this);

        $this->getScheduler()->scheduleRepeatingTask(new LobbyTickerTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new GameTickerTask(), 20);
    }

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