<?php

namespace NextDeveloper\Flow\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Flow\Database\Models\Automations;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Flow\Http\Transformers\AbstractTransformers\AbstractAutomationsTransformer;

/**
 * Class AutomationsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\Flow\Http\Transformers
 */
class AutomationsTransformer extends AbstractAutomationsTransformer
{

    /**
     * @param Automations $model
     *
     * @return array
     */
    public function transform(Automations $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Automations', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Automations', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
