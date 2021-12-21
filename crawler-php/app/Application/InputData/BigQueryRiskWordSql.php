<?php

namespace App\Application\InputData;

use App\Adapters\TargetDateAdapter;
use Illuminate\Support\Collection;

final class BigQueryRiskWordSql implements BigQuerySqlModel
{
    const SQL = 'SELECT
        "%6$s" AS `app_name`,
        "%7$s" AS `language`,
        `created_at`,
        `id`,
        `%8$s`,
        `date`,
        `translated`
        FROM  `%1$s.%2$s.%3$s` AS comments
        WHERE ( %5$s )
        AND NOT EXISTS (
        SELECT 1
        FROM `%1$s.%2$s.%4$s` AS risk_comments
        WHERE
            comments.id = risk_comments.id
        AND
            comments.created_at = risk_comments.created_at
        )
        %9$s';

    const CONDITIONAL_CLAUSE = '%sCONTAINS_SUBSTR((%s, translated), %s)';
    const PARTITION_CLAUSE = ' AND date >= ?';

    private array $parameters = [];
    private string $sql;

    public function __construct(
        string $projectId,
        string $datasetId,
        string $tableId,
        string $riskManageTable,
        Collection $riskwords,
        string $appName,
        string $language,
        string $targetField,
        ?string $createdAt = null
    ) {
        $conditionalClause = '';
        if (0 < $riskwords->count()) {
            foreach ($riskwords->all() as $value) {
                if (empty($conditionalClause) === true) {
                    $conditionalClause = sprintf(self::CONDITIONAL_CLAUSE, '', $targetField, '?');
                } else {
                    $conditionalClause .= sprintf(self::CONDITIONAL_CLAUSE, ' OR ', $targetField, '?');
                }

                $this->parameters[] = $value['word'];
            }
        }

        $partitionClause = '';
        if (is_null($createdAt) === false) {
            $targetDate = TargetDateAdapter::getTargetDate($createdAt);
            $partitionClause = self::PARTITION_CLAUSE;

            $this->parameters[] = $targetDate->getTargetDate('Y-m-d');
        }

        $this->sql = sprintf(
            self::SQL,
            $projectId,
            $datasetId,
            $tableId,
            $riskManageTable,
            $conditionalClause,
            $appName,
            $language,
            $targetField,
            $partitionClause
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
