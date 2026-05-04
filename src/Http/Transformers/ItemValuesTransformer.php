<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\ItemValues;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractItemValuesTransformer;

/**
 * Class ItemValuesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class ItemValuesTransformer extends AbstractItemValuesTransformer
{

    /**
     * @param ItemValues $model
     *
     * @return array
     */
    public function transform(ItemValues $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ItemValues', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ItemValues', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
