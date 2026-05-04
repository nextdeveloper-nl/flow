<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowAutomationQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowAutomationService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowAutomationTestTraits
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

    public function test_http_flowautomation_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowautomation',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowautomation_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowautomation', [
            'form_params'   =>  [
                'trigger'  =>  'a',
                'event_name'  =>  'a',
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
    public function test_flowautomation_model_get()
    {
        $result = AbstractFlowAutomationService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowautomation_get_all()
    {
        $result = AbstractFlowAutomationService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowautomation_get_paginated()
    {
        $result = AbstractFlowAutomationService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowautomation_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowautomation_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::first();

            event(new \NextDeveloper\Flow\Events\FlowAutomation\FlowAutomationRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_trigger_filter()
    {
        try {
            $request = new Request(
                [
                'trigger'  =>  'a'
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'event_name'  =>  'a'
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowautomation_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowAutomationQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowAutomation::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}