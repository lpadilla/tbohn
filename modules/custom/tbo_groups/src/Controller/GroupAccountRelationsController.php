<?php

namespace Drupal\tbo_groups\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\tbo_groups\Entity\GroupAccountRelationsInterface;

/**
 * Class GroupAccountRelationsController.
 *
 *  Returns responses for Group account relations routes.
 *
 * @package Drupal\tbo_groups\Controller
 */
class GroupAccountRelationsController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Group account relations  revision.
   *
   * @param int $group_account_relations_revision
   *   The Group account relations  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($group_account_relations_revision) {
    $group_account_relations = $this->entityManager()->getStorage('group_account_relations')->loadRevision($group_account_relations_revision);
    $view_builder = $this->entityManager()->getViewBuilder('group_account_relations');

    return $view_builder->view($group_account_relations);
  }

  /**
   * Page title callback for a Group account relations  revision.
   *
   * @param int $group_account_relations_revision
   *   The Group account relations  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($group_account_relations_revision) {
    $group_account_relations = $this->entityManager()->getStorage('group_account_relations')->loadRevision($group_account_relations_revision);
    return $this->t('Revision of %title from %date', ['%title' => $group_account_relations->label(), '%date' => format_date($group_account_relations->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Group account relations .
   *
   * @param \Drupal\tbo_groups\Entity\GroupAccountRelationsInterface $group_account_relations
   *   A Group account relations  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(GroupAccountRelationsInterface $group_account_relations) {
    $account = $this->currentUser();
    $langcode = $group_account_relations->language()->getId();
    $langname = $group_account_relations->language()->getName();
    $languages = $group_account_relations->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $group_account_relations_storage = $this->entityManager()->getStorage('group_account_relations');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $group_account_relations->label()]) : $this->t('Revisions for %title', ['%title' => $group_account_relations->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all group account relations revisions") || $account->hasPermission('administer group account relations entities')));
    $delete_permission = (($account->hasPermission("delete all group account relations revisions") || $account->hasPermission('administer group account relations entities')));

    $rows = [];

    $vids = $group_account_relations_storage->revisionIds($group_account_relations);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\tbo_groups\GroupAccountRelationsInterface $revision */
      $revision = $group_account_relations_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->revision_timestamp->value, 'short');
        if ($vid != $group_account_relations->getRevisionId()) {
          $link = $this->l($date, new Url('entity.group_account_relations.revision', ['group_account_relations' => $group_account_relations->id(), 'group_account_relations_revision' => $vid]));
        }
        else {
          $link = $group_account_relations->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log_message->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.group_account_relations.translation_revert', ['group_account_relations' => $group_account_relations->id(), 'group_account_relations_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.group_account_relations.revision_revert', ['group_account_relations' => $group_account_relations->id(), 'group_account_relations_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.group_account_relations.revision_delete', ['group_account_relations' => $group_account_relations->id(), 'group_account_relations_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['group_account_relations_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
