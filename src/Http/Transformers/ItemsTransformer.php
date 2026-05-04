<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\Items;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractItemsTransformer;

/**
 * Class ItemsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class ItemsTransformer extends AbstractItemsTransformer
{

    /**
     * @param Items $model
     *
     * @return array
     */
    public function transform(Items $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Items', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Items', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
