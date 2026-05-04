<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowItemWatcherQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowItemWatcherService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowItemWatcherTestTraits
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

    public function test_http_flowitemwatcher_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowitemwatcher',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowitemwatcher_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowitemwatcher', [
            'form_params'   =>  [
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
    public function test_flowitemwatcher_model_get()
    {
        $result = AbstractFlowItemWatcherService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowitemwatcher_get_all()
    {
        $result = AbstractFlowItemWatcherService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowitemwatcher_get_paginated()
    {
        $result = AbstractFlowItemWatcherService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowitemwatcher_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemwatcher_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemwatcher_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::first();

            event(new \NextDeveloper\Flow\Events\FlowItemWatcher\FlowItemWatcherRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemwatcher_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new FlowItemWatcherQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemwatcher_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemWatcherQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemwatcher_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemWatcherQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemWatcher::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}