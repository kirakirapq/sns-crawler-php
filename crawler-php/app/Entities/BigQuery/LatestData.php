<?php

namespace App\Entities\BigQuery;

final class LatestData
{
  private string $name;
  private array $colmuns;

  public function __construct($name, array $colmuns)
  {
    $this->name = $name;
    $this->colmuns = $colmuns;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getColmuns(): array
  {
    return $this->colmuns;
  }

  /**
   * getColmun
   *
   * @param  string $colmunName
   * @return Colmun
   */
  public function getColmun(string $colmunName): Colmun
  {
    return $this->colmuns[$colmunName];
  }
}
