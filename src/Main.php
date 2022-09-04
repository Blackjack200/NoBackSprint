<?php

declare(strict_types=1);

namespace Blackjack200\NoBackSprint;

use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	protected function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, static function(DataPacketReceiveEvent $event) : void {
			$player = $event->getOrigin()->getPlayer();
			$packet = $event->getPacket();
			if ($packet instanceof PlayerAuthInputPacket && $player !== null && $player->isSprinting()) {
				$rawYaw = $packet->getYaw();
				$yaw = fmod($rawYaw, 360);
				if ($yaw < 0) {
					$yaw += 360;
				}
				$direction = (new Vector3(
					-sin(deg2rad($yaw)),
					0,
					cos(deg2rad($yaw)),
				))->normalize();
				$delta = $player->getPosition()->subtractVector($packet->getPosition()->round(4)->subtract(0, 1.62, 0))->normalize();
				$delta->y = 0;
				$angle = 180 - round(rad2deg(acos($direction->dot($delta))));
				//max is 45 in theory
				if ($angle > 50) {
					$player->setSprinting(false);
				}
			}
		}, EventPriority::NORMAL, $this);
	}
}
