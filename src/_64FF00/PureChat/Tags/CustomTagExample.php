<?php
namespace _64FF00\PureChat\Tags;

use _64FF00\PureChat\Errors\ErrorHelper;
use pocketmine\Player;

class CustomTagExample implements CustomTagInterface
{
  static $count = 1;

  public function onAdd() { echo "Added"; }

  public function onRemove() { echo "Removed"; }

  public function onError($code) { echo "\n\nFail Added $code, " . ErrorHelper::getDetails($code) . "\n\n"; }

  public function getAPI()
  {

  }

  public function getPrefix(): string
  {
    return "test"; //This indicates the prefix of the said tag this would be test_(stffix)
  }

  public function getAllTags(): array
  {
    return [
      "test" => "testing",
      "count" => "countIt",
      "rand" => "randIt"
      //"suffix" => "callable fucntion"
    ];
  }

  public function testing(Player $player)
  {
    return "test!";//do something here with player
  }

  public function countIt(Player $player)
  {
    self::$count++;
    return self::$count;
  }

  public function randIt(Player $player)
  {
    return md5(mt_rand() + self::$count + time());
  }
}