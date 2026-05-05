<?php

Route::prefix('flow')->group(function () {
Route::prefix('pipelines')->group(
    function () {
        Route::get('/', 'Pipelines\PipelinesController@index');
        Route::get('/actions', 'Pipelines\PipelinesController@getActions');

        Route::get('{flow_pipelines}/tags ', 'Pipelines\PipelinesController@tags');
        Route::post('{flow_pipelines}/tags ', 'Pipelines\PipelinesController@saveTags');
        Route::get('{flow_pipelines}/addresses ', 'Pipelines\PipelinesController@addresses');
        Route::post('{flow_pipelines}/addresses ', 'Pipelines\PipelinesController@saveAddresses');

        Route::get('/{flow_pipelines}/{subObjects}', 'Pipelines\PipelinesController@relatedObjects');
        Route::get('/{flow_pipelines}', 'Pipelines\PipelinesController@show');

        Route::post('/', 'Pipelines\PipelinesController@store');
        Route::post('/{flow_pipelines}/do/{action}', 'Pipelines\PipelinesController@doAction');

        Route::patch('/{flow_pipelines}', 'Pipelines\PipelinesController@update');
        Route::delete('/{flow_pipelines}', 'Pipelines\PipelinesController@destroy');
    }
);

Route::prefix('stages')->group(
    function () {
        Route::get('/', 'Stages\StagesController@index');
        Route::get('/actions', 'Stages\StagesController@getActions');

        Route::get('{flow_stages}/tags ', 'Stages\StagesController@tags');
        Route::post('{flow_stages}/tags ', 'Stages\StagesController@saveTags');
        Route::get('{flow_stages}/addresses ', 'Stages\StagesController@addresses');
        Route::post('{flow_stages}/addresses ', 'Stages\StagesController@saveAddresses');

        Route::get('/{flow_stages}/{subObjects}', 'Stages\StagesController@relatedObjects');
        Route::get('/{flow_stages}', 'Stages\StagesController@show');

        Route::post('/', 'Stages\StagesController@store');
        Route::post('/{flow_stages}/do/{action}', 'Stages\StagesController@doAction');

        Route::patch('/{flow_stages}', 'Stages\StagesController@update');
        Route::delete('/{flow_stages}', 'Stages\StagesController@destroy');
    }
);

Route::prefix('items')->group(
    function () {
        Route::get('/', 'Items\ItemsController@index');
        Route::get('/actions', 'Items\ItemsController@getActions');

        Route::get('{flow_items}/tags ', 'Items\ItemsController@tags');
        Route::post('{flow_items}/tags ', 'Items\ItemsController@saveTags');
        Route::get('{flow_items}/addresses ', 'Items\ItemsController@addresses');
        Route::post('{flow_items}/addresses ', 'Items\ItemsController@saveAddresses');

        Route::get('/{flow_items}/{subObjects}', 'Items\ItemsController@relatedObjects');
        Route::get('/{flow_items}', 'Items\ItemsController@show');

        Route::post('/', 'Items\ItemsController@store');
        Route::post('/{flow_items}/do/{action}', 'Items\ItemsController@doAction');

        Route::patch('/{flow_items}', 'Items\ItemsController@update');
        Route::delete('/{flow_items}', 'Items\ItemsController@destroy');
    }
);

Route::prefix('columns')->group(
    function () {
        Route::get('/', 'Columns\ColumnsController@index');
        Route::get('/actions', 'Columns\ColumnsController@getActions');

        Route::get('{flow_columns}/tags ', 'Columns\ColumnsController@tags');
        Route::post('{flow_columns}/tags ', 'Columns\ColumnsController@saveTags');
        Route::get('{flow_columns}/addresses ', 'Columns\ColumnsController@addresses');
        Route::post('{flow_columns}/addresses ', 'Columns\ColumnsController@saveAddresses');

        Route::get('/{flow_columns}/{subObjects}', 'Columns\ColumnsController@relatedObjects');
        Route::get('/{flow_columns}', 'Columns\ColumnsController@show');

        Route::post('/', 'Columns\ColumnsController@store');
        Route::post('/{flow_columns}/do/{action}', 'Columns\ColumnsController@doAction');

        Route::patch('/{flow_columns}', 'Columns\ColumnsController@update');
        Route::delete('/{flow_columns}', 'Columns\ColumnsController@destroy');
    }
);

Route::prefix('stage-required-columns')->group(
    function () {
        Route::get('/', 'StageRequiredColumns\StageRequiredColumnsController@index');
        Route::get('/actions', 'StageRequiredColumns\StageRequiredColumnsController@getActions');

        Route::get('{flow_stage_required_columns}/tags ', 'StageRequiredColumns\StageRequiredColumnsController@tags');
        Route::post('{flow_stage_required_columns}/tags ', 'StageRequiredColumns\StageRequiredColumnsController@saveTags');
        Route::get('{flow_stage_required_columns}/addresses ', 'StageRequiredColumns\StageRequiredColumnsController@addresses');
        Route::post('{flow_stage_required_columns}/addresses ', 'StageRequiredColumns\StageRequiredColumnsController@saveAddresses');

        Route::get('/{flow_stage_required_columns}/{subObjects}', 'StageRequiredColumns\StageRequiredColumnsController@relatedObjects');
        Route::get('/{flow_stage_required_columns}', 'StageRequiredColumns\StageRequiredColumnsController@show');

        Route::post('/', 'StageRequiredColumns\StageRequiredColumnsController@store');
        Route::post('/{flow_stage_required_columns}/do/{action}', 'StageRequiredColumns\StageRequiredColumnsController@doAction');

        Route::patch('/{flow_stage_required_columns}', 'StageRequiredColumns\StageRequiredColumnsController@update');
        Route::delete('/{flow_stage_required_columns}', 'StageRequiredColumns\StageRequiredColumnsController@destroy');
    }
);

Route::prefix('item-values')->group(
    function () {
        Route::get('/', 'ItemValues\ItemValuesController@index');
        Route::get('/actions', 'ItemValues\ItemValuesController@getActions');

        Route::get('{flow_item_values}/tags ', 'ItemValues\ItemValuesController@tags');
        Route::post('{flow_item_values}/tags ', 'ItemValues\ItemValuesController@saveTags');
        Route::get('{flow_item_values}/addresses ', 'ItemValues\ItemValuesController@addresses');
        Route::post('{flow_item_values}/addresses ', 'ItemValues\ItemValuesController@saveAddresses');

        Route::get('/{flow_item_values}/{subObjects}', 'ItemValues\ItemValuesController@relatedObjects');
        Route::get('/{flow_item_values}', 'ItemValues\ItemValuesController@show');

        Route::post('/', 'ItemValues\ItemValuesController@store');
        Route::post('/{flow_item_values}/do/{action}', 'ItemValues\ItemValuesController@doAction');

        Route::patch('/{flow_item_values}', 'ItemValues\ItemValuesController@update');
        Route::delete('/{flow_item_values}', 'ItemValues\ItemValuesController@destroy');
    }
);

Route::prefix('stage-history')->group(
    function () {
        Route::get('/', 'StageHistory\StageHistoryController@index');
        Route::get('/actions', 'StageHistory\StageHistoryController@getActions');

        Route::get('{flow_stage_history}/tags ', 'StageHistory\StageHistoryController@tags');
        Route::post('{flow_stage_history}/tags ', 'StageHistory\StageHistoryController@saveTags');
        Route::get('{flow_stage_history}/addresses ', 'StageHistory\StageHistoryController@addresses');
        Route::post('{flow_stage_history}/addresses ', 'StageHistory\StageHistoryController@saveAddresses');

        Route::get('/{flow_stage_history}/{subObjects}', 'StageHistory\StageHistoryController@relatedObjects');
        Route::get('/{flow_stage_history}', 'StageHistory\StageHistoryController@show');

        Route::post('/', 'StageHistory\StageHistoryController@store');
        Route::post('/{flow_stage_history}/do/{action}', 'StageHistory\StageHistoryController@doAction');

        Route::patch('/{flow_stage_history}', 'StageHistory\StageHistoryController@update');
        Route::delete('/{flow_stage_history}', 'StageHistory\StageHistoryController@destroy');
    }
);

Route::prefix('automations')->group(
    function () {
        Route::get('/', 'Automations\AutomationsController@index');
        Route::get('/actions', 'Automations\AutomationsController@getActions');

        Route::get('{flow_automations}/tags ', 'Automations\AutomationsController@tags');
        Route::post('{flow_automations}/tags ', 'Automations\AutomationsController@saveTags');
        Route::get('{flow_automations}/addresses ', 'Automations\AutomationsController@addresses');
        Route::post('{flow_automations}/addresses ', 'Automations\AutomationsController@saveAddresses');

        Route::get('/{flow_automations}/{subObjects}', 'Automations\AutomationsController@relatedObjects');
        Route::get('/{flow_automations}', 'Automations\AutomationsController@show');

        Route::post('/', 'Automations\AutomationsController@store');
        Route::post('/{flow_automations}/do/{action}', 'Automations\AutomationsController@doAction');

        Route::patch('/{flow_automations}', 'Automations\AutomationsController@update');
        Route::delete('/{flow_automations}', 'Automations\AutomationsController@destroy');
    }
);

Route::prefix('item-watchers')->group(
    function () {
        Route::get('/', 'ItemWatchers\ItemWatchersController@index');
        Route::get('/actions', 'ItemWatchers\ItemWatchersController@getActions');

        Route::get('{flow_item_watchers}/tags ', 'ItemWatchers\ItemWatchersController@tags');
        Route::post('{flow_item_watchers}/tags ', 'ItemWatchers\ItemWatchersController@saveTags');
        Route::get('{flow_item_watchers}/addresses ', 'ItemWatchers\ItemWatchersController@addresses');
        Route::post('{flow_item_watchers}/addresses ', 'ItemWatchers\ItemWatchersController@saveAddresses');

        Route::get('/{flow_item_watchers}/{subObjects}', 'ItemWatchers\ItemWatchersController@relatedObjects');
        Route::get('/{flow_item_watchers}', 'ItemWatchers\ItemWatchersController@show');

        Route::post('/', 'ItemWatchers\ItemWatchersController@store');
        Route::post('/{flow_item_watchers}/do/{action}', 'ItemWatchers\ItemWatchersController@doAction');

        Route::patch('/{flow_item_watchers}', 'ItemWatchers\ItemWatchersController@update');
        Route::delete('/{flow_item_watchers}', 'ItemWatchers\ItemWatchersController@destroy');
    }
);

Route::prefix('stage-history-perspective')->group(
    function () {
        Route::get('/', 'StageHistoryPerspective\StageHistoryPerspectiveController@index');
        Route::get('/actions', 'StageHistoryPerspective\StageHistoryPerspectiveController@getActions');

        Route::get('{flow_stage_history_perspective}/tags ', 'StageHistoryPerspective\StageHistoryPerspectiveController@tags');
        Route::post('{flow_stage_history_perspective}/tags ', 'StageHistoryPerspective\StageHistoryPerspectiveController@saveTags');
        Route::get('{flow_stage_history_perspective}/addresses ', 'StageHistoryPerspective\StageHistoryPerspectiveController@addresses');
        Route::post('{flow_stage_history_perspective}/addresses ', 'StageHistoryPerspective\StageHistoryPerspectiveController@saveAddresses');

        Route::get('/{flow_stage_history_perspective}/{subObjects}', 'StageHistoryPerspective\StageHistoryPerspectiveController@relatedObjects');
        Route::get('/{flow_stage_history_perspective}', 'StageHistoryPerspective\StageHistoryPerspectiveController@show');

        Route::post('/', 'StageHistoryPerspective\StageHistoryPerspectiveController@store');
        Route::post('/{flow_stage_history_perspective}/do/{action}', 'StageHistoryPerspective\StageHistoryPerspectiveController@doAction');

        Route::patch('/{flow_stage_history_perspective}', 'StageHistoryPerspective\StageHistoryPerspectiveController@update');
        Route::delete('/{flow_stage_history_perspective}', 'StageHistoryPerspective\StageHistoryPerspectiveController@destroy');
    }
);

Route::prefix('pipelines-perspective')->group(
    function () {
        Route::get('/', 'PipelinesPerspective\PipelinesPerspectiveController@index');
        Route::get('/actions', 'PipelinesPerspective\PipelinesPerspectiveController@getActions');

        Route::get('{flow_pipelines_perspective}/tags ', 'PipelinesPerspective\PipelinesPerspectiveController@tags');
        Route::post('{flow_pipelines_perspective}/tags ', 'PipelinesPerspective\PipelinesPerspectiveController@saveTags');
        Route::get('{flow_pipelines_perspective}/addresses ', 'PipelinesPerspective\PipelinesPerspectiveController@addresses');
        Route::post('{flow_pipelines_perspective}/addresses ', 'PipelinesPerspective\PipelinesPerspectiveController@saveAddresses');

        Route::get('/{flow_pipelines_perspective}/{subObjects}', 'PipelinesPerspective\PipelinesPerspectiveController@relatedObjects');
        Route::get('/{flow_pipelines_perspective}', 'PipelinesPerspective\PipelinesPerspectiveController@show');

        Route::post('/', 'PipelinesPerspective\PipelinesPerspectiveController@store');
        Route::post('/{flow_pipelines_perspective}/do/{action}', 'PipelinesPerspective\PipelinesPerspectiveController@doAction');

        Route::patch('/{flow_pipelines_perspective}', 'PipelinesPerspective\PipelinesPerspectiveController@update');
        Route::delete('/{flow_pipelines_perspective}', 'PipelinesPerspective\PipelinesPerspectiveController@destroy');
    }
);

Route::prefix('items-perspective')->group(
    function () {
        Route::get('/', 'ItemsPerspective\ItemsPerspectiveController@index');
        Route::get('/actions', 'ItemsPerspective\ItemsPerspectiveController@getActions');

        Route::get('{flow_items_perspective}/tags ', 'ItemsPerspective\ItemsPerspectiveController@tags');
        Route::post('{flow_items_perspective}/tags ', 'ItemsPerspective\ItemsPerspectiveController@saveTags');
        Route::get('{flow_items_perspective}/addresses ', 'ItemsPerspective\ItemsPerspectiveController@addresses');
        Route::post('{flow_items_perspective}/addresses ', 'ItemsPerspective\ItemsPerspectiveController@saveAddresses');

        Route::get('/{flow_items_perspective}/{subObjects}', 'ItemsPerspective\ItemsPerspectiveController@relatedObjects');
        Route::get('/{flow_items_perspective}', 'ItemsPerspective\ItemsPerspectiveController@show');

        Route::post('/', 'ItemsPerspective\ItemsPerspectiveController@store');
        Route::post('/{flow_items_perspective}/do/{action}', 'ItemsPerspective\ItemsPerspectiveController@doAction');

        Route::patch('/{flow_items_perspective}', 'ItemsPerspective\ItemsPerspectiveController@update');
        Route::delete('/{flow_items_perspective}', 'ItemsPerspective\ItemsPerspectiveController@destroy');
    }
);

// EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE















