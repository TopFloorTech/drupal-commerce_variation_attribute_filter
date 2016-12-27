<?php

namespace Drupal\commerce_variation_attribute_filter\EventSubscriber;

use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductVariationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommerceVariationAttributeFilterSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductEvents::PRODUCT_VARIATION_INSERT][] = ['onProductVariationChange'];
    $events[ProductEvents::PRODUCT_VARIATION_UPDATE][] = ['onProductVariationChange'];

    return $events;
  }

  public function onProductVariationChange(ProductVariationEvent $event) {
    $config = \Drupal::config('commerce_variation_attribute_filter.settings');
    $newLines = '/(\r\n|\r|\n)/';
    $filterValues = preg_split($newLines, $config->get('filter_values'));

    if (empty($filterValues)) {
      return;
    }

    $variation = $event->getProductVariation();
    $attributeValues = $variation->getAttributeValues();
    $title = $variation->getProduct()->getTitle();

    if ($attributeValues) {
      $attribute_labels = array_map(function ($attributeValue) {
        return $attributeValue->label();
      }, $attributeValues);

      $attribute_labels = array_filter($attribute_labels, function ($label) use ($filterValues) {
        return !in_array($label, $filterValues);
      });

      $title .= ' - ' . implode(', ', $attribute_labels);
    }

    $variation->setTitle($title);

    // TODO: Save this somehow.
  }
}
