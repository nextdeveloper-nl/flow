<?php

namespace NextDeveloper\Flow\Http\Transformers\AbstractTransformers;

use NextDeveloper\Commons\Database\Models\Addresses;
use NextDeveloper\Commons\Database\Models\Comments;
use NextDeveloper\Commons\Database\Models\Meta;
use NextDeveloper\Commons\Database\Models\PhoneNumbers;
use NextDeveloper\Commons\Database\Models\SocialMedia;
use NextDeveloper\Commons\Database\Models\Votes;
use NextDeveloper\Commons\Database\Models\Media;
use NextDeveloper\Commons\Http\Transformers\MediaTransformer;
use NextDeveloper\Commons\Database\Models\AvailableActions;
use NextDeveloper\Commons\Http\Transformers\AvailableActionsTransformer;
use NextDeveloper\Commons\Database\Models\States;
use NextDeveloper\Commons\Http\Transformers\StatesTransformer;
use NextDeveloper\Commons\Http\Transformers\CommentsTransformer;
use NextDeveloper\Commons\Http\Transformers\SocialMediaTransformer;
use NextDeveloper\Commons\Http\Transformers\MetaTransformer;
use NextDeveloper\Commons\Http\Transformers\VotesTransformer;
use NextDeveloper\Commons\Http\Transformers\AddressesTransformer;
use NextDeveloper\Commons\Http\Transformers\PhoneNumbersTransformer;
use NextDeveloper\Flow\Database\Models\StageHistoryPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class StageHistoryPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class AbstractStageHistoryPerspectiveTransformer extends AbstractTransformer
{

    /**
     * @var array
     */
    protected array $availableIncludes = [
        'states',
        'actions',
        'media',
        'comments',
        'votes',
        'socialMedia',
        'phoneNumbers',
        'addresses',
        'meta'
    ];

    /**
     * @param StageHistoryPerspective $model
     *
     * @return array
     */
    public function transform(StageHistoryPerspective $model)
    {
                                                $flowItemId = \NextDeveloper\Flow\Database\Models\Items::where('id', $model->flow_item_id)->first();
                                                            $flowPipelineId = \NextDeveloper\Flow\Database\Models\Pipelines::where('id', $model->flow_pipeline_id)->first();
                                                            $fromStageId = \NextDeveloper\Flow\Database\Models\Stages::where('id', $model->from_stage_id)->first();
                                                            $toStageId = \NextDeveloper\Flow\Database\Models\Stages::where('id', $model->to_stage_id)->first();
                                                            $movedByIamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->moved_by_iam_user_id)->first();
                        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'flow_item_id'  =>  $flowItemId ? $flowItemId->uuid : null,
            'flow_pipeline_id'  =>  $flowPipelineId ? $flowPipelineId->uuid : null,
            'from_stage_id'  =>  $fromStageId ? $fromStageId->uuid : null,
            'to_stage_id'  =>  $toStageId ? $toStageId->uuid : null,
            'moved_by_iam_user_id'  =>  $movedByIamUserId ? $movedByIamUserId->uuid : null,
            'moved_at'  =>  $model->moved_at,
            'from_stage_name'  =>  $model->from_stage_name,
            'from_stage_color'  =>  $model->from_stage_color,
            'to_stage_name'  =>  $model->to_stage_name,
            'to_stage_color'  =>  $model->to_stage_color,
            'moved_by_name'  =>  $model->moved_by_name,
            ]
        );
    }

    public function includeStates(StageHistoryPerspective $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(StageHistoryPerspective $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(StageHistoryPerspective $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    public function includeSocialMedia(StageHistoryPerspective $model)
    {
        $socialMedia = SocialMedia::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($socialMedia, new SocialMediaTransformer());
    }

    public function includeComments(StageHistoryPerspective $model)
    {
        $comments = Comments::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($comments, new CommentsTransformer());
    }

    public function includeVotes(StageHistoryPerspective $model)
    {
        $votes = Votes::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($votes, new VotesTransformer());
    }

    public function includeMeta(StageHistoryPerspective $model)
    {
        $meta = Meta::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($meta, new MetaTransformer());
    }

    public function includePhoneNumbers(StageHistoryPerspective $model)
    {
        $phoneNumbers = PhoneNumbers::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($phoneNumbers, new PhoneNumbersTransformer());
    }

    public function includeAddresses(StageHistoryPerspective $model)
    {
        $addresses = Addresses::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($addresses, new AddressesTransformer());
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE








}
