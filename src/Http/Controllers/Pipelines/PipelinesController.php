<?php

namespace NextDeveloper\Flow\Http\Controllers\Pipelines;

use Illuminate\Http\Request;
use NextDeveloper\Flow\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\Flow\Http\Requests\Pipelines\PipelinesUpdateRequest;
use NextDeveloper\Flow\Http\Requests\Pipelines\PipelinesFromTemplateRequest;
use NextDeveloper\Flow\Http\Requests\Pipelines\PipelinesCreateTaskRequest;
use NextDeveloper\Flow\Services\ItemsService;
use NextDeveloper\Flow\Database\Filters\PipelinesQueryFilter;
use NextDeveloper\Flow\Database\Models\Pipelines;
use NextDeveloper\Flow\Services\PipelinesService;
use NextDeveloper\Flow\Http\Requests\Pipelines\PipelinesCreateRequest;
use NextDeveloper\Commons\Http\Traits\Tags as TagsTrait;use NextDeveloper\Commons\Http\Traits\Addresses as AddressesTrait;
class PipelinesController extends AbstractController
{
    private $model = Pipelines::class;

    use TagsTrait;
    use AddressesTrait;
    /**
     * This method returns the list of pipelines.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  PipelinesQueryFilter $filter  An object that builds search query
     * @param  Request              $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(PipelinesQueryFilter $filter, Request $request)
    {
        $data = PipelinesService::get($filter, $request->all());

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * This function returns the list of actions that can be performed on this object.
     *
     * @return void
     */
    public function getActions()
    {
        $data = PipelinesService::getActions();

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * Makes the related action to the object
     *
     * @param  $objectId
     * @param  $action
     * @return array
     */
    public function doAction($objectId, $action)
    {
        $actionId = PipelinesService::doAction($objectId, $action, request()->all());

        return $this->withArray(
            [
            'action_id' =>  $actionId
            ]
        );
    }

    /**
     * This method receives ID for the related model and returns the item to the client.
     *
     * @param  $pipelinesId
     * @return mixed|null
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public function show($ref)
    {
        //  Here we are not using Laravel Route Model Binding. Please check routeBinding.md file
        //  in NextDeveloper Platform Project
        $model = PipelinesService::getByRef($ref);

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method returns the list of sub objects the related object. Sub object means an object which is preowned by
     * this object.
     *
     * It can be tags, addresses, states etc.
     *
     * @param  $ref
     * @param  $subObject
     * @return void
     */
    public function relatedObjects($ref, $subObject)
    {
        $objects = PipelinesService::relatedObjects($ref, $subObject);

        return ResponsableFactory::makeResponse($this, $objects);
    }

    /**
     * This method created Pipelines object on database.
     *
     * @param  PipelinesCreateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function store(PipelinesCreateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = PipelinesService::create($request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates Pipelines object on database.
     *
     * @param  $pipelinesId
     * @param  PipelinesUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function update($pipelinesId, PipelinesUpdateRequest $request)
    {
        if($request->has('validateOnly') && $request->get('validateOnly') == true) {
            return [
                'validation'    =>  'success'
            ];
        }

        $model = PipelinesService::update($pipelinesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * This method updates Pipelines object on database.
     *
     * @param  $pipelinesId
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function destroy($pipelinesId)
    {
        $model = PipelinesService::delete($pipelinesId);

        return $this->noContent();
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    /**
     * Returns the catalog of pipeline templates (config-defined) for the "start with a
     * template" picker UI.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function templates()
    {
        $data = PipelinesService::listTemplates();

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * Returns the whole board payload (pipeline, stages, all items with owners, campaign) in one
     * call. The work and the Redis snapshot live in PipelinesService::board(); ?refresh=1 rebuilds it.
     *
     * @param  Request $request
     * @param  string  $pipelinesId
     * @return \Illuminate\Http\JsonResponse
     */
    public function board(Request $request, $pipelinesId)
    {
        $data = PipelinesService::board($pipelinesId, $request->boolean('refresh'));

        return ResponsableFactory::makeResponse($this, $data);
    }

    /**
     * Creates a pipeline + ordered stages without a campaign: from a config-defined template,
     * or blank for the given kind. Body: kind?, template_id?, name? (one of kind/template_id).
     *
     * @param  PipelinesFromTemplateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFromTemplate(PipelinesFromTemplateRequest $request)
    {
        $data = $request->validated();

        $model = PipelinesService::createFromTemplate(
            $data['template_id'] ?? null,
            $data['name'] ?? null,
            $data['kind'] ?? null
        );

        return ResponsableFactory::makeResponse($this, $model);
    }

    /**
     * Adds a task card to a project pipeline: creates the CRM task and the flow item together.
     *
     * @param  PipelinesCreateTaskRequest $request
     * @param  string $pipelinesId
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTask(PipelinesCreateTaskRequest $request, $pipelinesId)
    {
        $item = ItemsService::createTaskItem($pipelinesId, $request->validated());

        return ResponsableFactory::makeResponse($this, $item);
    }

    /**
     * Tasks of a project pipeline with their schedules, subtasks and links (leo4#1245).
     */
    public function gantt($pipelinesId)
    {
        return ResponsableFactory::makeResponse($this, PipelinesService::gantt($pipelinesId));
    }
}
