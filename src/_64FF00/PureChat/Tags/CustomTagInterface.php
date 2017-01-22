<?php
namespace _64FF00\PureChat\Tags;
interface CustomTagInterface
{
  public function onAdd();//calls when it is added

  public function onError($code);//calls when fails to be added OR when error is detected while processing with reason

  public function onRemove($code = 0);//calls when it is removed by internal reasons

  public function getPrefix():string;

  public function getAllTags():array;
}