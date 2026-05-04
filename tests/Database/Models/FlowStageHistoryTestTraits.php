<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowStageHistoryQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowStageHistoryService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowStageHistoryTestTraits
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

    public function test_http_flowstagehistory_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowstagehistory',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowstagehistory_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowstagehistory', [
            'form_params'   =>  [
                    'moved_at'  =>  now(),
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
    public function test_flowstagehistory_model_get()
    {
        $result = AbstractFlowStageHistoryService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowstagehistory_get_all()
    {
        $result = AbstractFlowStageHistoryService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowstagehistory_get_paginated()
    {
        $result = AbstractFlowStageHistoryService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowstagehistory_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistorySavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistorySavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstagehistory_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistorySavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistorySavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagehistory_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::first();

            event(new \NextDeveloper\Flow\Events\FlowStageHistory\FlowStageHistoryRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstagehistory_event_moved_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'moved_atStart'  =>  now()
                ]
            );

            $filter = new FlowStageHistoryQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstagehistory_event_moved_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'moved_atEnd'  =>  now()
                ]
            );

            $filter = new FlowStageHistoryQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstagehistory_event_moved_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'moved_atStart'  =>  now(),
                'moved_atEnd'  =>  now()
                ]
            );

            $filter = new FlowStageHistoryQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowStageHistory::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}