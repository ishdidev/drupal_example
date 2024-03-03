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

use Drupal\app_attribute\Entity\AttributeTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

class AttributeTypeListBuilder extends ConfigEntityListBuilder
{
    /**
     * {@inheritdoc}
     *
     * @return array<mixed>
     */
    public function buildHeader(): array
    {
        $header['title'] = t('Name');
        $header['description'] = [
            'data' => t('Description'),
            'class' => [RESPONSIVE_PRIORITY_MEDIUM],
        ];

        return $header + parent::buildHeader();
    }

    /**
     * {@inheritdoc}
     *
     * @return array<mixed>
     */
    public function buildRow(EntityInterface $entity): array
    {
        $row = [];

        if ($entity instanceof AttributeTypeInterface) {
            $row['title'] = [
                'data' => $entity->label(),
                'class' => ['menu-label'],
            ];

            $row['description']['data'] = ['#markup' => $entity->getDescription()];
        }

        return $row + parent::buildRow($entity);
    }

    /**
     * {@inheritdoc}
     *
     * @return array<mixed>
     */
    public function getDefaultOperations(EntityInterface $entity): array
    {
        $operations = parent::getDefaultOperations($entity);

        if (isset($operations['edit'])) {
            $operations['edit']['weight'] = 30;
        }

        return $operations;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<mixed>
     */
    public function render(): array
    {
        $build = parent::render();

        $build['table']['#empty'] = $this->t('No attribute types available. <a href=":link">Add attribute type</a>.', [
            ':link' => Url::fromRoute('attribute.attribute_type_add')->toString(),
        ]);

        return $build;
    }
}
