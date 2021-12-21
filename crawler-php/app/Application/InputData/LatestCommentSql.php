<?php

namespace App\Application\InputData;

final class LatestCommentSql implements BigQuerySqlModel
{
    const SQL = 'SELECT created_at FROM `%s.%s.%s` order by created_at desc limit 1';

    private string $sql;

    public function __construct(string $projectId, string $datasetId, string $tableId)
    {
        $this->sql = sprintf(
            self::SQL,
            $projectId,
            $datasetId,
            $tableId
        );
    }

    /**
     * getSql
     *
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * getParameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        return [];
    }

    /**
     * hasParameters
     *
     * @return array
     */
    public function hasParameters(): bool
    {
        return false;
    }
}
