<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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

        $transformed['object_id'] = $this->resolveObjectUuid(
            $model->object_type,
            $model->object_id
        );

        Cache::set(
            CacheHelper::getKey('ItemsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }

    private function resolveObjectUuid(string $objectType, int $objectId): ?string
    {
        $parts      = array_values(array_filter(explode('\\', $objectType)));
        $className  = array_pop($parts);
        $modelClass = '\\' . implode('\\', $parts) . '\\Database\\Models\\' . $className;

        if (!class_exists($modelClass)) {
            return null;
        }

        $object = $modelClass::withoutGlobalScopes()->where('id', $objectId)->first();

        return $object?->uuid;
    }
}
