<?php

namespace App\Gateways\BigQuery;

use \Exception;
use App\Adapters\BigQueryResponseAdapter;
use App\Application\InputData\BigQueryRiskWordSql;
use App\Application\InputData\BigQuerySqlModel;
use App\Application\OutputData\InnerApiResponse\InnerApiResponse;
use App\Application\OutputData\InnerApiResponse\BigqueryResponse;
use App\Application\Repositories\BigQuery\BigQueryFormat;
use App\Application\Repositories\BigQuery\BigQueryRepository;
use App\Exceptions\BigQueryReqestErrorException;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Core\ExponentialBackoff;
use Illuminate\Support\Facades\Log;

class BigQueryRepositoryClient implements BigQueryRepository
{
    private BigQueryClient $client;

    /**
     * __construct
     *
     * @param  mixed $projectId
     * @return void
     */
    public function __construct(string $projectId)
    {
        $this->client = new BigQueryClient(
            [
                'projectId'   => $projectId,
                'location'    => 'asia-northeast1',
                'keyFilePath' => env('GCP_SERVICE_ACCOUNT'),
            ]
        );
    }

    /**
     * existsTable
     *
     * @param  mixed $datasetId
     * @param  mixed $tableId
     * @return bool
     */
    public function existsTable(string $datasetId, string $tableId): bool
    {
        Log::info(sprintf('BigQueryClient::existsTable (%s.%s)', $datasetId, $tableId) . PHP_EOL);

        $dataset = $this->client->dataset($datasetId);
        $table = $dataset->table($tableId);

        if ($dataset->exists() === false) {
            return false;
        }

        if ($table->exists() === false) {
            return false;
        }

        return true;
    }

    /**
     * loadFromCsv
     *
     * @param  mixed $datasetId
     * @param  mixed $tableId
     * @param  mixed $filename
     * @return InnerApiResponse
     */
    public function loadBigQuery(string $datasetId, string $tableId, string $filename): InnerApiResponse
    {
        Log::info(sprintf('BigQueryClient::loadFromCsv (%s.%s)', $datasetId, $tableId) . PHP_EOL);

        $dataset = $this->client->dataset($datasetId);
        if ($dataset->exists() === false) {
            $dataset = $this->client->created_ataset($datasetId);
        }
        $table = $dataset->table($tableId);
        if ($table->exists() === false) {
            $table = $dataset->createTable($tableId);
        }

        $loadConfig = $table
            ->load(fopen($filename, 'r'))
            ->schemaUpdateOptions(['ALLOW_FIELD_ADDITION'])
            ->autodetect(true)
            ->writeDisposition('WRITE_APPEND')
            ->sourceFormat(BigQueryFormat::CSV);

        try {
            $job = $table->runJob($loadConfig);
            // poll the job until it is complete
            $backoff = new ExponentialBackoff(10);
            $backoff->execute(function () use ($job) {
                Log::debug('Waiting for job to complete' . PHP_EOL);

                $job->reload();
                if (!$job->isComplete()) {
                    throw new BigQueryReqestErrorException(
                        new Exception('Job has not yet completed', 500),
                        'BigqueryRepositoryClient::loadFromCsv',
                        [
                            'datasetId' => $datasetId ?? '',
                            'tableId' => $tableId ?? '',
                            'filename' => $filename ?? '',
                        ]
                    );
                }
            });
            // check if the job has errors
            if (isset($job->info()['status']['errorResult'])) {
                Log::error('Error running job: %s' . PHP_EOL, $job->info());

                return new BigqueryResponse(500, $job->info());
            }
            Log::info('Data imported successfully' . PHP_EOL);
        } catch (Exception $e) {
            throw new BigQueryReqestErrorException(
                $e,
                'BigqueryRepositoryClient::loadFromCsv',
                [
                    'datasetId' => $datasetId ?? '',
                    'tableId' => $tableId ?? '',
                    'filename' => $filename ?? '',
                ]
            );
        }

        return BigQueryResponseAdapter::getBigQueryResponse(201, null);
    }

    /**
     * getData
     *
     * @param  mixed $query
     * @return InnerApiResponse
     */
    public function getData(BigQuerySqlModel $sqlModel): InnerApiResponse
    {
        Log::info('BigQueryClient::getData' . PHP_EOL);

        Log::info('SQL', [$sqlModel->getSql()]);

        $jobConfig = $this->client->query($sqlModel->getSql());

        if ($sqlModel->hasParameters() === true) {
            Log::info('SQL Parameters', $sqlModel->getParameters());
            $jobConfig->parameters($sqlModel->getParameters());
        }
        $job = $this->client->startQuery($jobConfig);

        $backoff = new ExponentialBackoff(10);
        $backoff->execute(function () use ($job) {
            Log::debug('Waiting for job to complete' . PHP_EOL);
            $job->reload();
            if (!$job->isComplete()) {
                throw new BigQueryReqestErrorException(
                    new Exception('Job has not yet completed', 500),
                    'BigqueryRepositoryClient::getData',
                    [
                        'datasetId' => $query ?? '',
                    ]
                );
            }
        });
        $queryResults = $job->queryResults();

        return BigQueryResponseAdapter::getBigQueryResponse(200, $queryResults);
    }

    public function insertBigQuery(
        string $datasetId,
        string $sorceTableId,
        string $destTableId,
        BigQueryRiskWordSql $sqlModel
    ): InnerApiResponse {
        Log::info('BigQueryClient::insertBigQuery' . PHP_EOL);

        $dataset = $this->client->dataset($datasetId);
        if ($dataset->exists() === false) {
            $dataset = $this->client->created_ataset($datasetId);
        }
        $table = $dataset->table($sorceTableId);
        if ($table->exists() === false) {
            $table = $dataset->createTable($sorceTableId);
        }

        $destinationTable = $dataset->table($destTableId);
        if ($destinationTable->exists() === false) {
            $destinationTable = $dataset->createTable($destTableId);
        }

        try {
            $queryJobConfig = $this->client
                ->query($sqlModel->getSql())
                ->destinationTable($destinationTable)
                ->schemaUpdateOptions(['ALLOW_FIELD_ADDITION'])
                ->timePartitioning([
                    'type'                     => 'DAY',
                    'field'                    => 'date',
                    'require_partition_filter' => true,
                ])
                ->writeDisposition('WRITE_APPEND');

            Log::info('SQL', [$sqlModel->getSql()]);

            if ($sqlModel->hasParameters() === true) {
                Log::info('SQL Parameters', $sqlModel->getParameters());
                $queryJobConfig->parameters($sqlModel->getParameters());
            }

            $this->client->runQuery($queryJobConfig, ['maxResults' => 0]);
        } catch (Exception $e) {
            throw new BigQueryReqestErrorException(
                $e,
                'BigqueryRepositoryClient::insertBigQuery',
                [
                    'datasetId' => $datasetId ?? '',
                    'sorceTableId' => $sorceTableId ?? '',
                    'destTableId' => $destTableId ?? '',
                    'query' => $sqlModel->getSql() ?? '',
                    'params' => $sqlModel->getParameters() ?? '',
                ]
            );
        }

        return BigQueryResponseAdapter::getBigQueryResponse(201, null);
    }

    public function exportToBucket(
        $datasetId,
        $tableId,
        $targetDate
    ): void {
        $bucketName = 'wwo-crawler-translation';

        $dataset = $this->client->dataset($datasetId);
        $targetTable = sprintf('%s$%s', $tableId, $targetDate);

        $table = $dataset->table($targetTable);
        $destinationUri = "gs://{$bucketName}/{$targetTable}.json";
        // Define the format to use. If the format is not specified, 'CSV' will be used.
        $format = 'NEWLINE_DELIMITED_JSON';
        // Create the extract job
        $extractConfig = $table->extract($destinationUri)->destinationFormat($format);
        // Run the job
        $job = $table->runJob($extractConfig);  // Waits for the job to complete
        printf('Exported %s to %s' . PHP_EOL, $table->id(), $destinationUri);
    }
}
