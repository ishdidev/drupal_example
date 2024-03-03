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

namespace Drupal\app_attribute\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AttributeController.
 */
class AttributeController extends ControllerBase implements ContainerInjectionInterface
{
    protected DateFormatterInterface $dateFormatter;
    protected RendererInterface $renderer;
    protected EntityRepositoryInterface $entityRepository;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container): self
    {
        $instance = parent::create($container);

        /** @var DateFormatterInterface $dateFormatter */
        $dateFormatter = $container->get('date.formatter');
        $instance->dateFormatter = $dateFormatter;

        /** @var RendererInterface $renderer */
        $renderer = $container->get('renderer');
        $instance->renderer = $renderer;

        /** @var EntityRepositoryInterface $entityRepository */
        $entityRepository = $container->get('entity.repository');
        $instance->entityRepository = $entityRepository;

        return $instance;
    }

    /**
     * @return RedirectResponse|array<mixed>
     */
    public function addPage(): RedirectResponse|array
    {
        /** @var EntityTypeInterface $definition */
        $definition = $this->entityTypeManager()->getDefinition('attribute_type');

        $build = [
            '#theme' => 'attribute_add_list',
            '#cache' => [
                'tags' => $this->entityTypeManager()->getDefinition('attribute_type')?->getListCacheTags(),
            ],
        ];
        $content = [];

        $types = $this->entityTypeManager()->getStorage('attribute_type')->loadMultiple();
        uasort($types, [$definition->getClass(), 'sort']); /* @phpstan-ignore-line */

        foreach ($types as $type) {
            $access = $this->entityTypeManager()->getAccessControlHandler('attribute')->createAccess((string) $type->id(), null, [], true);
            if ($access->isAllowed()) {
                $content[$type->id()] = $type->toArray();
            }
        }

        if (1 === \count($content)) {
            $type = array_key_first($content);

            return $this->redirect('attribute.add', ['attribute_type' => $type]);
        }

        $build['#content'] = $content;

        return $build;
    }
}
