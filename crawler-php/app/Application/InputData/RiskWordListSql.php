<?php

namespace App\Application\InputData;

final class RiskWordListSql implements BigQuerySqlModel
{
    const SQL = 'SELECT DISTINCT word FROM `%s.%s.risk_word_master`';

    private string $sql;

    public function __construct(string $projectId, string $datasetId)
    {
        $this->sql = sprintf(
            self::SQL,
            $projectId,
            $datasetId
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
