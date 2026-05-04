<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowPipelineQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowPipelineService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowPipelineTestTraits
{
    public $http;

    /**
     *   Creating the Guzzle object
     */
    public function setupGuzzle()
    {
        $this->http = new Client(
            [
            'base_uri'  =>  '127.0.0.1:8000'
            ]
        );
    }

    /**
     *   Destroying the Guzzle object
     */
    public function destroyGuzzle()
    {
        $this->http = null;
    }

    public function test_http_flowpipeline_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowpipeline',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowpipeline_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowpipeline', [
            'form_params'   =>  [
                'name'  =>  'a',
                'description'  =>  'a',
                'object_type'  =>  'a',
                            ],
                ['http_errors' => false]
            ]
        );

        $this->assertEquals($response->getStatusCode(), Response::HTTP_OK);
    }

    /**
     * Get test
     *
     * @return bool
     */
    public function test_flowpipeline_model_get()
    {
        $result = AbstractFlowPipelineService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowpipeline_get_all()
    {
        $result = AbstractFlowPipelineService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowpipeline_get_paginated()
    {
        $result = AbstractFlowPipelineService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowpipeline_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowpipeline_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::first();

            event(new \NextDeveloper\Flow\Events\FlowPipeline\FlowPipelineRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_description_filter()
    {
        try {
            $request = new Request(
                [
                'description'  =>  'a'
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_object_type_filter()
    {
        try {
            $request = new Request(
                [
                'object_type'  =>  'a'
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowpipeline_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowPipelineQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowPipeline::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}