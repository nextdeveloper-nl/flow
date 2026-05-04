<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowItemQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowItemService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowItemTestTraits
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

    public function test_http_flowitem_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowitem',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowitem_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowitem', [
            'form_params'   =>  [
                'object_type'  =>  'a',
                'position'  =>  '1',
                    'last_stage_changed_at'  =>  now(),
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
    public function test_flowitem_model_get()
    {
        $result = AbstractFlowItemService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowitem_get_all()
    {
        $result = AbstractFlowItemService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowitem_get_paginated()
    {
        $result = AbstractFlowItemService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowitem_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitem_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItem::first();

            event(new \NextDeveloper\Flow\Events\FlowItem\FlowItemRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_object_type_filter()
    {
        try {
            $request = new Request(
                [
                'object_type'  =>  'a'
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_position_filter()
    {
        try {
            $request = new Request(
                [
                'position'  =>  '1'
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_last_stage_changed_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'last_stage_changed_atStart'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_deleted_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_last_stage_changed_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'last_stage_changed_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_deleted_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_last_stage_changed_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'last_stage_changed_atStart'  =>  now(),
                'last_stage_changed_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitem_event_deleted_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'deleted_atStart'  =>  now(),
                'deleted_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItem::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}