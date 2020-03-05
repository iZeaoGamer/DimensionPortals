<?php

declare(strict_types=1);

namespace muqsit\dimensionportals\exoblock;

use Ds\Queue;
use muqsit\dimensionportals\utils\ArrayUtils;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\level\Level as World;

class NetherPortalFrameExoBlock implements ExoBlock{

	/** @var int */
	private $frame_block_id;

	/** @var int */
	private $lengthSquared;

	public function __construct(Block $frame_block, int $max_portal_height, int $max_portal_width){
		$this->frame_block_id = $frame_block;
		$this->lengthSquared = (new Vector2($max_portal_height, $max_portal_width))->lengthSquared();
	}

	public function interact(Block $wrapping, Player $player, Item $item, int $face) : bool{
		if($item->getId() === ItemIds::FLINT_AND_STEEL){
			$affectedBlock = $wrapping->getSide($face);
			if($affectedBlock->getId() === Block::AIR){
				$pos = $affectedBlock->getPos();
				/** @var World $world */
				$world = $pos->getLevel();

				$blocks = $this->fill($world, $pos, 10, Facing::WEST);
				if(count($blocks) === 0){
					$blocks = $this->fill($world, $pos, 10, Facing::NORTH);
				}
				if(count($blocks) > 0){
					foreach($blocks as $hash => $block){
						if($block->getId() === Block::PORTAL){
							World::getBlockXYZ($hash, $x, $y, $z);
							$world->setBlockAt($x, $y, $z, $block, false);
						}
					}
					return true;
				}
			}
		}
		return false;
	}

	public function update(Block $wrapping) : bool{
		return false;
	}

	public function onPlayerMoveInside(Player $player) : void{
	}

	public function onPlayerMoveOutside(Player $player) : void{
	}

	public function fill(World $world, Vector3 $origin, int $radius, int $direction) : array{
		$blocks = [];

		$visits = new Queue([$origin]);
		while(!$visits->isEmpty()){
			/** @var Vector3 $coordinates */
			$coordinates = $visits->pop();
			if($origin->distanceSquared($coordinates) >= $this->lengthSquared){
				return [];
			}

			if(
				$world->getBlockAt($coordinates->x, $coordinates->y, $coordinates->z)->getId() === Block::AIR &&
				ArrayUtils::firstOrDefault(
					$blocks,
					static function(int $hash, Block $block) use($coordinates) : bool{
						World::getBlockXYZ($hash, $x, $y, $z);
						return $coordinates->x === $x && $coordinates->y === $y && $coordinates->z === $z;
					}
				) === null
			){
				$this->visit($coordinates, $blocks, $direction);
				if($direction === Facing::WEST){
					$visits->push(
						$coordinates->getSide(Facing::NORTH),
						$coordinates->getSide(Facing::SOUTH)
					);
				}elseif($direction === Facing::NORTH){
					$visits->push(
						$coordinates->getSide(Facing::WEST),
						$coordinates->getSide(Facing::EAST)
					);
				}
				$visits->push(
					$coordinates->getSide(Facing::UP),
					$coordinates->getSide(Facing::DOWN)
				);
			}else{
				$block = $world->getBlockAt($coordinates->x, $coordinates->y, $coordinates->z);
				if(!$this->isValid($block, $coordinates, $blocks)){
					return [];
				}
			}
		}

		return $blocks;
	}

	public function visit(Vector3 $coordinates, array &$blocks, int $direction) : void{
		$blocks[World::blockHash($coordinates->x, $coordinates->y, $coordinates->z)] = BlockFactory::get(Block::PORTAL, $direction - 2);
	}

	private function isValid(Block $block, Vector3 $coordinates, array $portals) : bool{
		return $block->getId() === $this->frame_block_id ||
			ArrayUtils::firstOrDefault(
				$portals,
				static function(int $hash, Block $b) use($coordinates) : bool{
					if($b->getId() === Block::PORTAL){
						World::getBlockXYZ($hash, $x, $y, $z);
						return $coordinates->x === $x && $coordinates->y === $y && $coordinates->z === $z;
					}
					return false;
				}
			) !== null;
	}
}
