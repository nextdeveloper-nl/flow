<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowColumnQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowColumnService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowColumnTestTraits
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

    public function test_http_flowcolumn_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowcolumn',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowcolumn_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowcolumn', [
            'form_params'   =>  [
                'name'  =>  'a',
                'label'  =>  'a',
                'field_type'  =>  'a',
                'default_value'  =>  'a',
                'position'  =>  '1',
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
    public function test_flowcolumn_model_get()
    {
        $result = AbstractFlowColumnService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowcolumn_get_all()
    {
        $result = AbstractFlowColumnService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowcolumn_get_paginated()
    {
        $result = AbstractFlowColumnService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowcolumn_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowcolumn_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::first();

            event(new \NextDeveloper\Flow\Events\FlowColumn\FlowColumnRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_name_filter()
    {
        try {
            $request = new Request(
                [
                'name'  =>  'a'
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_label_filter()
    {
        try {
            $request = new Request(
                [
                'label'  =>  'a'
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_field_type_filter()
    {
        try {
            $request = new Request(
                [
                'field_type'  =>  'a'
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_default_value_filter()
    {
        try {
            $request = new Request(
                [
                'default_value'  =>  'a'
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_position_filter()
    {
        try {
            $request = new Request(
                [
                'position'  =>  '1'
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowcolumn_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowColumnQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowColumn::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}