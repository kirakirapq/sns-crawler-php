<?php

namespace App\Application\InputData;

use App\Adapters\TargetDateAdapter;
use App\Entities\BigQuery\Colmun;

final class RiskCommentListSql implements BigQuerySqlModel
{
    const SQL = 'SELECT
        *
        FROM  `%1$s.%2$s.%3$s` AS comments
        %4$s';

    const PARTITION_CLAUSE = 'date >= ?';
    const CREATED_AT_CLAUSE = 'FORMAT_TIMESTAMP("%%Y-%%m-%%d %%H:%%M:%%S", created_at,"Asia/Tokyo") > ?';
    const TITLE_CLAUSE = 'app_name = ?';
    const LANGUAGE_CLAUSE = 'language = ?';

    private array $parameters = [];
    private string $sql;

    public function __construct(
        string $projectId,
        string $datasetId,
        string $tableId,
        ?string $title = null,
        ?string $language = null,
        ?Colmun $createdAt = null
    ) {
        $clause = '';
        if (is_null($createdAt) === false && empty($createdAt) === false) {
            $targetDate = TargetDateAdapter::getTargetDate($createdAt->getValue());
            // $createdAColumnName = sprintf(self::CREATED_AT_CLAUSE, 'created_at');

            $clause .= sprintf(' %s %s', $this->getClause($clause), self::PARTITION_CLAUSE);
            $clause .= sprintf(' %s %s', $this->getClause($clause), self::CREATED_AT_CLAUSE);
            $this->parameters[] = $targetDate->getTargetDate('Y-m-d');
            $this->parameters[] = $targetDate->getTargetDate('Y-m-d H:i:s');
        }

        if (is_null($title) === false) {
            $clause .= sprintf(' %s %s', $this->getClause($clause), self::TITLE_CLAUSE);
            $this->parameters[] = $title;
        }

        if (is_null($language) === false) {
            $clause .= sprintf(' %s %s', $this->getClause($clause), self::LANGUAGE_CLAUSE);
            $this->parameters[] = $language;
        }

        $this->sql = sprintf(
            self::SQL,
            $projectId,
            $datasetId,
            $tableId,
            $clause
        );
    }

    private function getClause($clause): string
    {
        return empty($clause) === true ? 'WHERE' : 'AND';
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
        return $this->parameters ?? [];
    }

    /**
     * hasParameters
     *
     * @return array
     */
    public function hasParameters(): bool
    {
        return isset($this->parameters) && empty($this->parameters ?? []) === false;
    }
}
