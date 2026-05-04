<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\StageHistories;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractStageHistoriesTransformer;

/**
 * Class StageHistoriesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class StageHistoriesTransformer extends AbstractStageHistoriesTransformer
{

    /**
     * @param StageHistories $model
     *
     * @return array
     */
    public function transform(StageHistories $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StageHistories', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StageHistories', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
