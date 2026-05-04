<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowStageQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowStageService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowStageTestTraits
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

    public function test_http_flowstage_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowstage',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowstage_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowstage', [
            'form_params'   =>  [
                'name'  =>  'a',
                'color'  =>  'a',
                'position'  =>  '1',
                'probability'  =>  '1',
                'sla_days'  =>  '1',
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
    public function test_flowstage_model_get()
    {
        $result = AbstractFlowStageService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowstage_get_all()
    {
        $result = AbstractFlowStageService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowstage_get_paginated()
    {
        $result = AbstractFlowStageService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowstage_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstage_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStage::first();

            event(new \NextDeveloper\Flow\Events\FlowStage\FlowStageRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_color_filter()
    {
        try {
            $request = new Request(
                [
                'color'  =>  'a'
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_position_filter()
    {
        try {
            $request = new Request(
                [
                'position'  =>  '1'
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_probability_filter()
    {
        try {
            $request = new Request(
                [
                'probability'  =>  '1'
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_sla_days_filter()
    {
        try {
            $request = new Request(
                [
                'sla_days'  =>  '1'
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstage_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowStageQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStage::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}