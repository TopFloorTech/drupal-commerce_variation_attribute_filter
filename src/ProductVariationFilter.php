<?php

namespace Drupal\commerce_variation_attribute_filter;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;

class ProductVariationFilter {
  /** @var ProductInterface */
  protected $product;

  public function __construct(ProductInterface $product)
  {
    $this->product = $product;
  }

  public function getValidVariations(array $attributes = NULL) {
    $variations = $this->product->getVariations();

    if (!is_null($attributes)) {
      foreach ($attributes as $attr_id => $field) {
        if (substr($attr_id, 0, 10) == 'attribute_') {
          if (!empty($field['#default_value'])) {
            foreach ($variations as $variationId => $variation) {
              if (!$this->variationHasValue($variation, $attr_id, $field['#default_value'])) {
                unset($variations[$variationId]);
              }
            }
          }
        }
      }
    }

    $this->validVariations = $variations;
    return $variations;
  }

  public function filterAttributes(array &$attributes = NULL) {
    if (!is_null($attributes)) {
      foreach ($attributes as $attr_id => $field) {
        if (substr($attr_id, 0, 10) == 'attribute_' && isset($field['#options'])) {
          $attr_copy = $attributes;
          unset($attr_copy[$attr_id]);
          $variations = $this->getValidVariations($attr_copy);
          $attributes[$attr_id]['#options'] = $this->filterAttributeOptions($variations, $attr_id, $field['#options']);

          if (!empty($field['#default_value']) && !array_key_exists($field['#default_value'], $field['#options'])) {
            $attributes[$attr_id]['#default_value'] = NULL;
          }
        }
      }
    }
  }

  protected function filterAttributeOptions(array $variations, $attr_id, array $options) {
    foreach ($options as $value_id => $title) {
      $removeOption = TRUE;

      foreach ($variations as $variation) {
        if ($this->variationHasValue($variation, $attr_id, $value_id)) {
          $removeOption = FALSE;
          break;
        }
      }

      if ($removeOption) {
        unset($options[$value_id]);
      }
    }

    return $options;
  }

  protected function variationHasValue(ProductVariationInterface $variation, $attr_id, $value_id) {
    if ($variation->hasField($attr_id) && !$variation->get($attr_id)->isEmpty()) {
      $values = $variation->get($attr_id)->getValue();

      foreach ($values as $delta => $item) {
        if ($item['target_id'] == $value_id) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }
}
