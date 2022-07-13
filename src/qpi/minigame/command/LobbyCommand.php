<?php

namespace qpi\minigame\command;

use form\CustomForm;
use form\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use qpi\minigame\game\GameManager;
use qpi\minigame\lobby\WaitingLobby;

class LobbyCommand extends Command {

    public function __construct() {
        parent::__construct("lobby", "Информация о текущем лобби", "/lobby", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) return;

        $gameManager = GameManager::getInstance();
        if ($gameManager->isInGame($sender)) {
            $sender->sendMessage("§cПросмотр и редактирование параметров лобби недоступно во время игры.");
            return;
        }

        if (!$gameManager->isInLobby($sender)) {
            $sender->sendMessage("§cВы не находитесь в лобби.");
            return;
        }

        $this->sendUserForm($sender, $gameManager->getLobbyByPlayer($sender));
    }

    private function sendAdminForm(Player $player, WaitingLobby $lobby): void {
        $form = new CustomForm(function(Player $player, array $data) use($lobby) {
            //TODO: Применение настроек лобби
        });

        $form->setTitle("Изменение настроек лобби");

        //TODO: Параметры настроек лобби

        $form->sendToPlayer($player);
    }

    private function sendUserForm(Player $player, WaitingLobby $lobby): void {
        $form = new SimpleForm();
        $form->setTitle("Просмотр информации о лобби");

        //TODO: Показ информации о лобби

        if ($lobby->getOwner() === $player) {
            $form->addButton("Настройки лобби", SimpleForm::IMAGE_TYPE_PATH, "", function(Player $player) use($lobby) {
                $this->sendAdminForm($player, $lobby);
            });

            $form->addButton("Начать игру", SimpleForm::IMAGE_TYPE_PATH, "", function(Player $player) use($lobby) {
                if ($lobby->getStatus() !== WaitingLobby::STATUS_WAITING) {
                    $player->sendMessage("§cВы уже начали игру.");
                    return;
                }

                $lobby->tryStartGame();
            });
        }
        $form->sendToPlayer($player);
    }
}