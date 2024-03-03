<?php

declare(strict_types=1);

/**
 * This file is a part of Drupal Headless App.
 *
 * @author Muhammed Naushad <muhammed.naushad@ekino.com>
 * @copyright Copyright (c) Ekino. All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drupal\app_attribute;

use Drupal\app_attribute\Entity\AttributeType;
use Drupal\app_attribute\Entity\AttributeTypeInterface;
use Drupal\Core\Entity\BundlePermissionHandlerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides dynamic permissions for custom entities.
 */
class AttributePermissions
{
    use BundlePermissionHandlerTrait;
    use StringTranslationTrait;

    /**
     * @return array<mixed>
     */
    public function attributeTypePermissions(): array
    {
        return $this->generatePermissions(AttributeType::loadMultiple(), [$this, 'buildAttributePermissions']);
    }

    /**
     * @return array<mixed>
     */
    protected function buildAttributePermissions(AttributeTypeInterface $type): array
    {
        $typeId = $type->id();

        return $typeId ? $this->getPermissions($typeId, ['%type_name' => $type->bundle()]) : [];
    }

    /**
     * @param array<mixed> $typeParams
     *
     * @return array<mixed>
     */
    private function getPermissions(string $typeId, array $typeParams): array
    {
        return [
            "create $typeId attribute" => [
                'title' => $this->t('%type_name: Create new attribute', $typeParams),
            ],
            "edit own $typeId attribute" => [
                'title' => $this->t('%type_name: Edit own attribute', $typeParams),
                'description' => $this->t('Note that anonymous users with this permission are able to edit any attribute created by any anonymous user.'),
            ],
            "edit any $typeId attribute" => [
                'title' => $this->t('%type_name: Edit any attribute', $typeParams),
            ],
            "delete own $typeId attribute" => [
                'title' => $this->t('%type_name: Delete own attribute', $typeParams),
                'description' => $this->t('Note that anonymous users with this permission are able to delete any attribute created by any anonymous user.'),
            ],
            "delete any $typeId attribute" => [
                'title' => $this->t('%type_name: Delete any attribute', $typeParams),
            ],
        ];
    }
}
