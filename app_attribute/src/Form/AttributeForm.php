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

namespace Drupal\app_attribute\Form;

use Drupal\app_attribute\Entity\Attribute;
use Drupal\app_attribute\Entity\AttributeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the attribute edit forms.
 *
 * @internal
 */
class AttributeForm extends ContentEntityForm
{
    protected DateFormatterInterface $dateFormatter;

    protected AccountInterface $currentUser;

    /**
     * {@inheritDoc}
     */
    public static function create(ContainerInterface $container): self
    {
        $instance = parent::create($container);

        /** @var DateFormatterInterface $dateFormatter */
        $dateFormatter = $container->get('date.formatter');
        $instance->dateFormatter = $dateFormatter;

        /** @var AccountInterface $currentUser */
        $currentUser = $container->get('current_user');
        $instance->currentUser = $currentUser;

        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $form
     *
     * @return array<mixed>
     */
    public function form(array $form, FormStateInterface $form_state): array
    {
        /** @var AttributeInterface $attribute */
        $attribute = $this->entity;

        if ('edit' === $this->operation) {
            $form['#title'] = $this->t('<em>Edit @type</em> @title', [
                '@type' => $attribute->getEntityTypeId(),
                '@title' => $attribute->label(),
            ]);
        }

        // Changed must be sent to the client, for later overwrite error checking.
        $form['changed'] = [
            '#type' => 'hidden',
            '#default_value' => $attribute->getChangedTime(),
        ];

        $form = parent::form($form, $form_state);

        $form['advanced']['#attributes']['class'][] = 'entity-meta';

        $form['weight']['#default_value'] = 0;
        // Attribute author information for administrators.
        $form['author'] = [
            '#type' => 'details',
            '#title' => $this->t('Authoring information'),
            '#group' => 'advanced',
            '#attributes' => [
                'class' => ['attribute-form-author'],
            ],
            '#weight' => 90,
            '#optional' => true,
        ];

        if (isset($form['uid'])) {
            $form['uid']['#group'] = 'author';
        }

        if (isset($form['created'])) {
            $form['created']['#group'] = 'author';
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $form
     */
    public function save(array $form, FormStateInterface $form_state): ?int
    {
        /** @var AttributeInterface $attribute */
        $attribute = $this->entity;
        $insert = $attribute->isNew();
        $attribute->save();

        $context = ['@type' => $attribute->getEntityTypeId(), '%title' => $attribute->label(), 'link' => $attribute->toLink($this->t('View'))->toString()];
        $args = ['@type' => 'Entity', '%title' => $attribute->toLink()->toString()];

        if ($insert) {
            $this->logger('content')->info('@type: added %title.', $context);
            $this->messenger()->addStatus($this->t('@type %title has been created.', $args));
        } else {
            $this->logger('content')->info('@type: updated %title.', $context);
            $this->messenger()->addStatus($this->t('@type %title has been updated.', $args));
        }

        $form_state->setRedirect('view.attribute.page_1');

        return null;
    }
}
