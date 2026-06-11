<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\StagesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractStagesPerspectiveTransformer;

/**
 * Class StagesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class StagesPerspectiveTransformer extends AbstractStagesPerspectiveTransformer
{

    /**
     * @param StagesPerspective $model
     *
     * @return array
     */
    public function transform(StagesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StagesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StagesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
