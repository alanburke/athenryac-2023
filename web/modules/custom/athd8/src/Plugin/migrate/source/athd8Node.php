<?php

namespace Drupal\athd8\Plugin\migrate\source;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal_d8\Plugin\migrate\source\d8\ContentEntity;
use Drupal\migrate\Row;
use Drupal\filter\Plugin\Filter\FilterAutoP;

/**
 * Drupal 8 node source from database.
 *
 * @MigrateSource(
 *   id = "ath_d8_node",
 *   source_provider = "athd8"
 * )
 */
class athd8Node extends ContentEntity {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $configuration['entity_type'] = 'node';
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager, $entity_field_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get Alias
    $prepared_row = parent::prepareRow($row);
    $source_values = $row->getSource();
    $query = $this->select('path_alias', 'pa')
        ->fields('pa', ['alias'])
        ->condition('path', '/node/' . $source_values['nid']);
    $alias = $query->execute()->fetchField();    
    $row->setSourceProperty('alias', $alias);

    // Get Paragraph details
    $legacy_paras = $source_values['field_content'];
    $content_value = '';
    foreach ($legacy_paras as $i => $legacy_para) {
        $query = $this->select('paragraphs_item', 'pi')
          ->fields('pi', ['type'])
          ->condition('id', $legacy_para['target_id'])
          ->condition('revision_id', $legacy_para['target_revision_id']);
        $type = $query->execute()->fetchField();
        $legacy_paras[$i]['type'] = $type;

        switch($type){
            case 'para_text':
                $table = 'paragraph__field_para_text';
                $column = 'field_para_text_value';
                $embed = 'text';
                break;
            case '20_questions':
                $table  = 'paragraph__field_para_questions';
                $column = 'field_para_questions_value';
                $embed = 'text';
                break;                
            case 'para_image':
                $table = 'paragraph__field_para_image';
                $column = 'field_para_image_target_id';
                $embed = 'media';
                break;
            case 'para_files':
                $table = 'paragraph__field_para_files';
                $column = 'field_para_files_target_id';
                $embed = 'file';
                break;
            case 'images':
                $table = 'paragraph__field_gallery_images';
                $column = 'field_gallery_images_target_id';
                $embed = 'gallery';
                break;            
        }
        $query = $this->select($table)
            ->fields($table, [$column])
            ->condition('entity_id', $legacy_para['target_id'])
            ->condition('revision_id', $legacy_para['target_revision_id']);
        $value = $query->execute()->fetchField();
        $legacy_paras[$i]['value'] = $value;

        switch ($embed){
            case 'text':
                // Replace old embeds
                $content_value = str_replace('drupal-entity', 'drupal-media', $value);
                break;
            case 'media':
                $connection = \Drupal\Core\Database\Database::getConnection('default', 'default');
                $query = $connection->select('media')
                    ->fields('media', ['uuid'])
                    ->condition('mid', $value);
                $uuid = $query->execute()->fetchField();
                $content_value .= '<drupal-media data-entity-type="media" data-entity-uuid="' . $uuid . '"></drupal-media>';
                break;
            case 'file':
                $connection = \Drupal\Core\Database\Database::getConnection('default', 'default');
                $query = $connection->select('media', 'm')
                    ->fields('m', ['uuid']);
                $query->join('media__field_media_document', 'mfmd', 'm.mid = mfmd.entity_id');
                $query->condition('field_media_document_target_id', $value);  
                $uuid = $query->execute()->fetchField();   
                $content_value .= '<drupal-media data-entity-type="media" data-entity-uuid="' . $uuid . '"></drupal-media>'; 
                break;            
            case 'gallery':
                $connection = \Drupal\Core\Database\Database::getConnection('default', 'default');
                $query = $connection->select('migrate_map_d8_media_galleries', 'mmdmg');
                $query->join('media', 'm', 'm.mid = mmdmg.destid1');
                $query->fields('m',['uuid']);
                $query->condition('sourceid1', $legacy_para['target_id']);  
                $uuid = $query->execute()->fetchField();   
                $content_value .= '<drupal-media data-entity-type="media" data-entity-uuid="' . $uuid . '"></drupal-media>'; 
                break;  
            }                
    }
    $content_value = _filter_autop($content_value, 'en');
    $row->setSourceProperty('legacy_content', $content_value);

    return $prepared_row;
  }

}
