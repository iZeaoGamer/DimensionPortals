<?php

declare(strict_types=1);

namespace muqsit\dimensionportals\world;

use muqsit\dimensionportals\player\PlayerManager;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\level\WorldUnloadEvent;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\Player;

final class WorldListener implements Listener{

	/**
	 * @param WorldUnloadEvent $event
	 * @priority MONITOR
	 */
	public function onWorldUnload(WorldUnloadEvent $event) : void{
		WorldManager::destroy($event->getWorld());
	}

	/**
	 * @param ChunkLoadEvent $event
	 * @priority MONITOR
	 *//*
	public function onChunkLoad(ChunkLoadEvent $event) : void{
		$chunk = $event->getChunk();
		$world = WorldManager::get($event->getWorld());
		if($world !== null){
			$world->onChunkLoad($chunk->getX(), $chunk->getZ());
		}
	}*/

	/**
	 * @param ChunkUnloadEvent $event
	 * @priority MONITOR
	 *//*
	public function onChunkUnload(ChunkUnloadEvent $event) : void{
		$chunk = $event->getChunk();
		$world = WorldManager::get($event->getWorld());
		if($world !== null){
			$world->onChunkUnload($chunk->getX(), $chunk->getZ());
		}
	}*/

	/**
	 * @param EntityTeleportEvent $event
	 * @priority MONITOR
	 */
	public function onEntityTeleport(EntityTeleportEvent $event) : void{
		$player = $event->getEntity();
		if($player instanceof Player){
			$from_world = WorldManager::get($event->getFrom()->getWorld()) ?? WorldManager::getOverworld();
			$to = $event->getTo();
			$to_world = WorldManager::get($to->getWorld()) ?? WorldManager::getOverworld();
			if($from_world->getNetworkDimensionId() !== $to_world->getNetworkDimensionId()){
				$packet = new ChangeDimensionPacket();
				$packet->dimension = $to_world->getNetworkDimensionId();
				$packet->position = $to->asVector3();
				$packet->respawn = !$player->isAlive();
				$player->getNetworkSession()->sendDataPacket($packet);
				PlayerManager::get($player)->onBeginDimensionChange();
			}
		}
	}
}
