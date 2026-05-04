<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\StageRequiredColumns;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractStageRequiredColumnsTransformer;

/**
 * Class StageRequiredColumnsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class StageRequiredColumnsTransformer extends AbstractStageRequiredColumnsTransformer
{

    /**
     * @param StageRequiredColumns $model
     *
     * @return array
     */
    public function transform(StageRequiredColumns $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StageRequiredColumns', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StageRequiredColumns', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
