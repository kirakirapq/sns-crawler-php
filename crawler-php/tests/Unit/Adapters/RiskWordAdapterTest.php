<?php

namespace Unit\Adapters;

use App\Adapters\RiskWordAdapter;
use App\Application\InputData\SubRedditRequestData;
use App\Application\InputData\SubRedditThreadCommentRequestData;
use App\Entities\Reddit\SubReddit;
use App\Entities\Reddit\Thread;
use App\Application\OutputData\InnerApiResponse\BigQueryResponse;
use App\Entities\ResponseData\BigQuery\BigQueryData;
use App\Entities\RiskWord\RiskCommentList;
use Illuminate\Http\Request;
use Tests\TestCase;
use \Mockery;

class RiskWordAdapterTest extends TestCase
{
    /**
     * getRiskCommentList
     * @test
     *
     * @return void
     */
    public function getRiskCommentList(): void
    {
        $data = [
            [
                'app_name' => '',
                'language' => '',
                'text' => '',
                'translated' => '',
                'id' => '',
                'created_at' => '',
            ],
        ];
        $bigQueryData = Mockery::mock(BigQueryData::class);
        $bigQueryData->shouldReceive('getStatusCode')->andReturn(200)->once();
        $bigQueryData->shouldReceive('getErrorMessage')->andReturn('')->once();
        $bigQueryData->shouldReceive('getDataList')->andReturn(collect($data))->once();

        $actual = RiskWordAdapter::getRiskCommentList($bigQueryData);

        $this->assertInstanceOf(RiskCommentList::class, $actual);
    }

    /**
     * getRiskCommentListByRequestData
     * @test
     *
     * @return void
     */
    public function getRiskCommentListByRequestData(): void
    {
        $data['messages'] = [
            [
                'app_name' => '',
                'language' => '',
                'text' => '',
                'translated' => '',
                'id' => '',
                'created_at' => '',
            ],
        ];
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')->andReturn($data)->once();

        $actual = RiskWordAdapter::getRiskCommentListByRequestData($request);

        $this->assertInstanceOf(RiskCommentList::class, $actual);
    }
}
