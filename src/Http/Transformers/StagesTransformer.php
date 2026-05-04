<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractStagesTransformer;

/**
 * Class StagesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class StagesTransformer extends AbstractStagesTransformer
{

    /**
     * @param Stages $model
     *
     * @return array
     */
    public function transform(Stages $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Stages', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Stages', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
