<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\Pipelines;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractPipelinesTransformer;

/**
 * Class PipelinesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class PipelinesTransformer extends AbstractPipelinesTransformer
{

    /**
     * @param Pipelines $model
     *
     * @return array
     */
    public function transform(Pipelines $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Pipelines', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Pipelines', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
