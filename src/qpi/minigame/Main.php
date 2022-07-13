<?php

namespace qpi\minigame;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use qpi\minigame\command\LobbyCommand;
use qpi\minigame\command\PlayCommand;
use qpi\minigame\listener\MainEventListener;
use qpi\minigame\task\GameTickerTask;
use qpi\minigame\task\LobbyTickerTask;

class Main extends PluginBase implements Listener {

    protected function onEnable(): void {
        $this->getServer()->getCommandMap()->registerAll("Invasion", [
            new LobbyCommand(),
            new PlayCommand(),
        ]);

        $pluginManager = $this->getServer()->getPluginManager();
        $pluginManager->registerEvents(new MainEventListener(), $this);

        $this->getScheduler()->scheduleRepeatingTask(new LobbyTickerTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new GameTickerTask(), 20);
    }
}