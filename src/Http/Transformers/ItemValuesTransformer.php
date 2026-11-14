<?php

namespace NextDeveloper\Flow\Http\Transformers;

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
        // flow_item_values has no uuid, so the usual CacheHelper key ("ItemValues" + uuid) is the same for
        // every row and each list served the first cached row over and over. A value row is cheap to
        // transform: serve it uncached (leo4#1243).
        return parent::transform($model);
    }
}
