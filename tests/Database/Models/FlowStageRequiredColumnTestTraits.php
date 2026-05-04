<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowStageRequiredColumnQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowStageRequiredColumnService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowStageRequiredColumnTestTraits
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

    public function test_http_flowstagerequiredcolumn_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowstagerequiredcolumn',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowstagerequiredcolumn_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowstagerequiredcolumn', [
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
    public function test_flowstagerequiredcolumn_model_get()
    {
        $result = AbstractFlowStageRequiredColumnService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowstagerequiredcolumn_get_all()
    {
        $result = AbstractFlowStageRequiredColumnService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowstagerequiredcolumn_get_paginated()
    {
        $result = AbstractFlowStageRequiredColumnService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowstagerequiredcolumn_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowstagerequiredcolumn_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowstagerequiredcolumn_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowStageRequiredColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowStageRequiredColumn\FlowStageRequiredColumnRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}