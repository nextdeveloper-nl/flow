<?php

namespace NextDeveloper\Flow\Services;

use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\Flow\Services\AbstractServices\AbstractAutomationsService;

/**
 * This class is responsible from managing the data for Automations
 *
 * Class AutomationsService.
 *
 * @package NextDeveloper\Flow\Database\Models
 */
class AutomationsService extends AbstractAutomationsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function create(array $data)
    {
        $data = self::resolvePusherId($data);

        return parent::create($data);
    }

    public static function update($id, array $data)
    {
        $data = self::resolvePusherId($data);

        return parent::update($id, $data);
    }

    private static function resolvePusherId(array $data): array
    {
        if (array_key_exists('common_pusher_id', $data)) {
            $data['common_pusher_id'] = DatabaseHelper::uuidToId(
                '\NextDeveloper\Commons\Database\Models\Pushers',
                $data['common_pusher_id']
            );
        }

        return $data;
    }
}