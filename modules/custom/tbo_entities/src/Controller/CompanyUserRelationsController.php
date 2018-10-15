<?php

namespace Drupal\tbo_entities\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\tbo_entities\Entity\CompanyUserRelationsInterface;

/**
 * Class CompanyUserRelationsController.
 *
 *  Returns responses for Company user relations routes.
 *
 * @package Drupal\tbo_entities\Controller
 */
class CompanyUserRelationsController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Company user relations  revision.
   *
   * @param int $company_user_relations_revision
   *   The Company user relations  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($company_user_relations_revision) {
    $company_user_relations = $this->entityManager()->getStorage('company_user_relations')->loadRevision($company_user_relations_revision);
    $view_builder = $this->entityManager()->getViewBuilder('company_user_relations');

    return $view_builder->view($company_user_relations);
  }

  /**
   * Page title callback for a Company user relations  revision.
   *
   * @param int $company_user_relations_revision
   *   The Company user relations  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($company_user_relations_revision) {
    $company_user_relations = $this->entityManager()->getStorage('company_user_relations')->loadRevision($company_user_relations_revision);
    return $this->t('Revision of %title from %date', ['%title' => $company_user_relations->label(), '%date' => format_date($company_user_relations->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Company user relations .
   *
   * @param \Drupal\tbo_entities\Entity\CompanyUserRelationsInterface $company_user_relations
   *   A Company user relations  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CompanyUserRelationsInterface $company_user_relations) {
    $account = $this->currentUser();
    $langcode = $company_user_relations->language()->getId();
    $langname = $company_user_relations->language()->getName();
    $languages = $company_user_relations->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $company_user_relations_storage = $this->entityManager()->getStorage('company_user_relations');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $company_user_relations->label()]) : $this->t('Revisions for %title', ['%title' => $company_user_relations->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all company user relations revisions") || $account->hasPermission('administer company user relations entities')));
    $delete_permission = (($account->hasPermission("delete all company user relations revisions") || $account->hasPermission('administer company user relations entities')));

    $rows = [];

    $vids = $company_user_relations_storage->revisionIds($company_user_relations);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\tbo_entities\CompanyUserRelationsInterface $revision */
      $revision = $company_user_relations_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->revision_timestamp->value, 'short');
        if ($vid != $company_user_relations->getRevisionId()) {
          $link = $this->l($date, new Url('entity.company_user_relations.revision', ['company_user_relations' => $company_user_relations->id(), 'company_user_relations_revision' => $vid]));
        }
        else {
          $link = $company_user_relations->link($date);
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
              Url::fromRoute('entity.company_user_relations.translation_revert', ['company_user_relations' => $company_user_relations->id(), 'company_user_relations_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.company_user_relations.revision_revert', ['company_user_relations' => $company_user_relations->id(), 'company_user_relations_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.company_user_relations.revision_delete', ['company_user_relations' => $company_user_relations->id(), 'company_user_relations_revision' => $vid]),
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

    $build['company_user_relations_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
