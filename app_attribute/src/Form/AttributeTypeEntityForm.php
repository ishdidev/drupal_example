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

use Drupal\app_attribute\Entity\AttributeTypeInterface;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;

class AttributeTypeEntityForm extends BundleEntityFormBase
{
    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $form
     *
     * @return array<mixed>
     */
    public function form(array $form, FormStateInterface $form_state): array
    {
        $form = parent::form($form, $form_state);

        /** @var AttributeTypeInterface $entityType */
        $entityType = $this->entity;

        $form['label'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Label'),
            '#maxlength' => 255,
            '#default_value' => $entityType->label(),
            '#description' => $this->t('Label for the %content_entity_id entity type (bundle).', ['%content_entity_id' => $entityType->getEntityType()->getBundleOf()]),
            '#required' => true,
        ];

        $form['description'] = [
            '#title' => $this->t('Description'),
            '#type' => 'textarea',
            '#default_value' => $entityType->getDescription(),
            '#description' => $this->t('This text will be displayed on the <em>Add new attribute</em>.'),
        ];

        $form['id'] = [
            '#type' => 'machine_name',
            '#default_value' => $entityType->id(),
            '#machine_name' => [
                'exists' => '\Drupal\app_attribute\Entity\AttributeType::load',
            ],
            '#disabled' => !$entityType->isNew(),
        ];

        if ($this->moduleHandler->moduleExists('language')) {
            $form['language'] = [
                '#type' => 'details',
                '#title' => $this->t('Language settings'),
                '#group' => 'additional_settings',
            ];

            $entityTypeId = (string) $entityType->id();

            $languageConfiguration = ContentLanguageSettings::loadByEntityTypeBundle('attribute', $entityTypeId);
            $form['language']['language_configuration'] = [
                '#type' => 'language_configuration',
                '#entity_information' => [
                    'entity_type' => 'attribute',
                    'bundle' => $entityTypeId,
                ],
                '#default_value' => $languageConfiguration,
            ];
        }

        return $this->protectBundleIdElement($form);
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $form
     */
    public function save(array $form, FormStateInterface $form_state): ?int
    {
        /** @var AttributeTypeInterface $entityType */
        $entityType = $this->entity;

        $status = $entityType->save();
        $messageParams = [
            '%label' => $entityType->label(),
            '%content_entity_id' => $entityType->getEntityType()->getBundleOf(),
        ];

        switch ($status) {
            case SAVED_NEW:
                $this->messenger()->addMessage($this->t('Created the %label %content_entity_id bundle.', $messageParams));
                break;

            default:
                $this->messenger()->addMessage($this->t('Saved the %label %content_entity_id bundle.', $messageParams));
        }

        $form_state->setRedirectUrl($entityType->toUrl('collection'));

        return null;
    }
}
