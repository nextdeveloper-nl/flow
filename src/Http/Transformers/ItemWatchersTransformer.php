<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\ItemWatchers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractItemWatchersTransformer;

/**
 * Class ItemWatchersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class ItemWatchersTransformer extends AbstractItemWatchersTransformer
{

    /**
     * @param ItemWatchers $model
     *
     * @return array
     */
    public function transform(ItemWatchers $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ItemWatchers', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ItemWatchers', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
