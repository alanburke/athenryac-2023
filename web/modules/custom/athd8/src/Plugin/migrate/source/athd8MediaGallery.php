<?php

namespace Drupal\athd8\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Drupal\migrate\Row;


/**
 * Source for Media Gallery.
 *
 * @MigrateSource(
 *   id = "athd8_media_gallery",
 * )
 */
class athd8MediaGallery extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Source data is queried from 'paragraph__field_gallery_images' table.
    $query = $this->select('paragraph__field_gallery_images', 'pfgi')
      ->fields('pfgi', [
          'entity_id',
          'field_gallery_images_target_id',
        ]);
    $query->groupBy('pfgi.entity_id');
    $query->addExpression('GROUP_CONCAT(DISTINCT pfgi.field_gallery_images_target_id)', 'gallery_images');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'entity_id' => $this->t('entity_id' ),
      'field_gallery_images_target_id'   => $this->t('field_gallery_images_target_id' ),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'entity_id' => [
        'type' => 'integer',
        'alias' => 'eid',
      ]
    ];
  }

  public function prepareRow(Row $row) {

    $source_values = $row->getSource();
    $query = $this->select('media__field_media_image', 'mfmi')
      ->fields('mfmi', [
        'field_media_image_target_id',
      ])
      ->condition('entity_id', $source_values['field_gallery_images_target_id']);

    $result = $query->execute()->fetchField(0);
    $row->setSourceProperty('cover_image', $result);

    return parent::prepareRow($row);
  }


}
