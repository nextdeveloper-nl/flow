<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\ItemsPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractItemsPerspectiveTransformer;

/**
 * Class ItemsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class ItemsPerspectiveTransformer extends AbstractItemsPerspectiveTransformer
{

    /**
     * @param ItemsPerspective $model
     *
     * @return array
     */
    public function transform(ItemsPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ItemsPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ItemsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
