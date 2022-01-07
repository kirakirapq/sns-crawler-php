<?php

namespace App\Entities\BigQuery;

use App\Exceptions\ObjectDefinitionErrorException;
use \ReflectionClass;

final class Colmun
{
    public function __construct(private string $name, private int|string|array $value, ?string $type = null)
    {
        if (is_null($type) === false) {
            $type = mb_strtoupper($type);
            $reflectionClass = new ReflectionClass(ColmunType::class);
            if (in_array($type, $reflectionClass->getConstants()) === false) {
                throw new ObjectDefinitionErrorException('App\Entities\BigQuery\Colmun::type error.', 500);
            }
        }

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
    public function getType(): ?string
    {
        return $this->type;
    }
}
