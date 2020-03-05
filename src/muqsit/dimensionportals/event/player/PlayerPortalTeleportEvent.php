<?php

declare(strict_types=1);

namespace muqsit\dimensionportals\event\player;

use muqsit\dimensionportals\event\DimensionPortalsEvent;
use muqsit\dimensionportals\exoblock\PortalExoBlock;
use pocketmine\entity\Location;
use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerPortalTeleportEvent extends DimensionPortalsEvent implements Cancellable{

	/** @var Player */
	private $player;

	/** @var PortalExoBlock */
	private $block;

	/** @var Location */
	private $target;

	public function __construct(Player $player, PortalExoBlock $block, Location $target){
		$this->player = $player;
		$this->block = $block;
		$this->target = $target;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getBlock() : PortalExoBlock{
		return $this->block;
	}

	public function getTarget() : Location{
		return $this->target->asLocation();
	}

	public function setTarget(Location $target) : void{
		$this->target = $target->asLocation();
	}
}
