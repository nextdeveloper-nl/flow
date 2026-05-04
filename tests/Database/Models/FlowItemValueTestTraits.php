<?php

namespace NextDeveloper\Flow\Tests\Database\Models;

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use NextDeveloper\Flow\Database\Filters\FlowItemValueQueryFilter;
use NextDeveloper\Flow\Services\AbstractServices\AbstractFlowItemValueService;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;

trait FlowItemValueTestTraits
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

    public function test_http_flowitemvalue_get()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'GET',
            '/flow/flowitemvalue',
            ['http_errors' => false]
        );

        $this->assertContains(
            $response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_NOT_FOUND
            ]
        );
    }

    public function test_http_flowitemvalue_post()
    {
        $this->setupGuzzle();
        $response = $this->http->request(
            'POST', '/flow/flowitemvalue', [
            'form_params'   =>  [
                'value'  =>  'a',
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
    public function test_flowitemvalue_model_get()
    {
        $result = AbstractFlowItemValueService::get();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowitemvalue_get_all()
    {
        $result = AbstractFlowItemValueService::getAll();

        $this->assertIsObject($result, Collection::class);
    }

    public function test_flowitemvalue_get_paginated()
    {
        $result = AbstractFlowItemValueService::get(
            null, [
            'paginated' =>  'true'
            ]
        );

        $this->assertIsObject($result, LengthAwarePaginator::class);
    }

    public function test_flowitemvalue_event_retrieved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueRetrievedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_created_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueCreatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_creating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueCreatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_saving_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueSavingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_saved_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueSavedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_updating_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueUpdatingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_updated_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueUpdatedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_deleting_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueDeletingEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_deleted_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueDeletedEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_restoring_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueRestoringEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_restored_without_object()
    {
        try {
            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueRestoredEvent());
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemvalue_event_retrieved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueRetrievedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_created_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueCreatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_creating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueCreatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_saving_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueSavingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_saved_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueSavedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_updating_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueUpdatingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_updated_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueUpdatedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_deleting_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueDeletingEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_deleted_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueDeletedEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_restoring_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueRestoringEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    public function test_flowitemvalue_event_restored_with_object()
    {
        try {
            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::first();

            event(new \NextDeveloper\Flow\Events\FlowItemValue\FlowItemValueRestoredEvent($model));
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemvalue_event_value_filter()
    {
        try {
            $request = new Request(
                [
                'value'  =>  'a'
                ]
            );

            $filter = new FlowItemValueQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemvalue_event_created_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now()
                ]
            );

            $filter = new FlowItemValueQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemvalue_event_updated_at_filter_start()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now()
                ]
            );

            $filter = new FlowItemValueQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemvalue_event_created_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemValueQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemvalue_event_updated_at_filter_end()
    {
        try {
            $request = new Request(
                [
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemValueQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemvalue_event_created_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'created_atStart'  =>  now(),
                'created_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemValueQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_flowitemvalue_event_updated_at_filter_start_and_end()
    {
        try {
            $request = new Request(
                [
                'updated_atStart'  =>  now(),
                'updated_atEnd'  =>  now()
                ]
            );

            $filter = new FlowItemValueQueryFilter($request);

            $model = \NextDeveloper\Flow\Database\Models\FlowItemValue::filter($filter)->first();
        } catch (\Exception $e) {
            $this->assertFalse(false, $e->getMessage());
        }

        $this->assertTrue(true);
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}