<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\StageHistoryPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractStageHistoryPerspectiveTransformer;

/**
 * Class StageHistoryPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class StageHistoryPerspectiveTransformer extends AbstractStageHistoryPerspectiveTransformer
{

    /**
     * @param StageHistoryPerspective $model
     *
     * @return array
     */
    public function transform(StageHistoryPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StageHistoryPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StageHistoryPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
