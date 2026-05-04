<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\Columns;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractColumnsTransformer;

/**
 * Class ColumnsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class ColumnsTransformer extends AbstractColumnsTransformer
{

    /**
     * @param Columns $model
     *
     * @return array
     */
    public function transform(Columns $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Columns', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Columns', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
