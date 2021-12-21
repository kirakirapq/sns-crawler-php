<?php

namespace App\Entities\BigQuery;

use App\Exceptions\ObjectDefinitionErrorException;
use \ReflectionClass;

final class Colmun
{
    private string $name;
    private mixed $value;
    private ?string $type;

    public function __construct(string $name, mixed $value, ?string $type = null)
    {
        if (is_null($type) === false) {
            $type = mb_strtoupper($type);
            $reflectionClass = new ReflectionClass(ColmunType::class);
            if (in_array($type, $reflectionClass->getConstants()) === false) {
                throw new ObjectDefinitionErrorException('App\Entities\BigQuery\Colmun::type error.', 500);
            }
        }

        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * getValue
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * getType
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
