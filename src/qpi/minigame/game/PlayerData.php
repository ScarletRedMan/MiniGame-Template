<?php

namespace qpi\minigame\game;

use pocketmine\player\Player;
use pocketmine\world\Position;

class PlayerData {

    private string $name;
    private ?Player $player;
    private array $inventory;
    private Position $position;
    //TODO: Реализация сохранения эффектов

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setPlayer(?Player $player): void {
        $this->player = $player;
    }

    public function saveStats(): void {
        $inv = $this->player->getInventory();
        $this->inventory = $inv->getContents();

        $this->position = $this->player->getPosition();
    }

    public function loadStats(): void {
        $inv = $this->player->getInventory();
        $inv->setContents($this->inventory);

        $this->player->teleport($this->position);
    }
}