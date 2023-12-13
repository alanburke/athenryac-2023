<?php

namespace Drupal\athd8\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Drupal\migrate\Row;


/**
 * Source for Media Documents.
 *
 * @MigrateSource(
 *   id = "athd8_media_document",
 * )
 */
class athd8MediaDocument extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Source data is queried from 'paragraph__field_para_files' table.
    $query = $this->select('paragraph__field_para_files', 'pfpf')
      ->fields('pfpf', [
          'entity_id',
          'delta',
          'field_para_files_target_id',
        ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'entity_id' => $this->t('entity_id' ),
      'delta'   => $this->t('delta' ),
      'field_para_files_target_id'    => $this->t('field_para_files_target_id'),
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
      ],
      'delta' => [
        'type' => 'integer'
      ]
    ];
  }
}
