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

use Drupal\app_attribute\Entity\AttributeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AttributeListBuilder extends EntityListBuilder
{
    protected DateFormatterInterface $dateFormatter;

    protected LanguageManagerInterface $languageManager;

    /**
     * {@inheritdoc}
     */
    public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): self
    {
        $instance = parent::createInstance($container, $entity_type);

        /** @var DateFormatterInterface $dateFormatter */
        $dateFormatter = $container->get('date.formatter');
        $instance->dateFormatter = $dateFormatter;

        /** @var LanguageManagerInterface $languageManager */
        $languageManager = $container->get('language_manager');
        $instance->languageManager = $languageManager;

        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<mixed>
     */
    public function buildHeader(): array
    {
        // Enable language column and filter if multiple languages are added.
        $header = [
            'title' => $this->t('Title'),
            'type' => [
                'data' => $this->t('Attribute Type'),
                'class' => [RESPONSIVE_PRIORITY_MEDIUM],
            ],
            'author' => [
                'data' => $this->t('Author'),
                'class' => [RESPONSIVE_PRIORITY_LOW],
            ],
            'status' => $this->t('Status'),
            'changed' => [
                'data' => $this->t('Updated'),
                'class' => [RESPONSIVE_PRIORITY_LOW],
            ],
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

        if ($entity instanceof AttributeInterface) {
            $row['title']['data'] = [
                '#type' => 'link',
                '#title' => $entity->label(),
                '#url' => $entity->toUrl(),
            ];
            $row['author']['data'] = [
                '#theme' => 'username',
                '#account' => $entity->getOwner(),
            ];

            $row['status'] = $entity->isPublished() ? $this->t('published') : $this->t('not published');
            $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');
            $row['operations']['data'] = $this->buildOperations($entity);
        }

        return $row + parent::buildRow($entity);
    }
}
