<?php
namespace _64FF00\PureChat\Tags;
interface CustomTagInterface
{
  public function getAPI();

  public function getPrefix():string;

  public function getAllTags():array;
}