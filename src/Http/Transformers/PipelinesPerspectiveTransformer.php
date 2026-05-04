<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\PipelinesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractPipelinesPerspectiveTransformer;

/**
 * Class PipelinesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class PipelinesPerspectiveTransformer extends AbstractPipelinesPerspectiveTransformer
{

    /**
     * @param PipelinesPerspective $model
     *
     * @return array
     */
    public function transform(PipelinesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('PipelinesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('PipelinesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
