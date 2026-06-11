<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\ItemValuesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractItemValuesPerspectiveTransformer;

/**
 * Class ItemValuesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class ItemValuesPerspectiveTransformer extends AbstractItemValuesPerspectiveTransformer
{

    /**
     * @param ItemValuesPerspective $model
     *
     * @return array
     */
    public function transform(ItemValuesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ItemValuesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ItemValuesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
