<?php

namespace Drupal\recipe\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\recipe\Entity\RecipeInterface;

/**
 * Class RecipeController.
 *
 *  Returns responses for Recipe routes.
 *
 * @package Drupal\recipe\Controller
 */
class RecipeController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Recipe  revision.
   *
   * @param int $recipe_revision
   *   The Recipe  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($recipe_revision) {
    $recipe = $this->entityManager()->getStorage('recipe')->loadRevision($recipe_revision);
    $view_builder = $this->entityManager()->getViewBuilder('recipe');

    return $view_builder->view($recipe);
  }

  /**
   * Page title callback for a Recipe  revision.
   *
   * @param int $recipe_revision
   *   The Recipe  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($recipe_revision) {
    $recipe = $this->entityManager()->getStorage('recipe')->loadRevision($recipe_revision);
    return $this->t('Revision of %title from %date', ['%title' => $recipe->label(), '%date' => format_date($recipe->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Recipe .
   *
   * @param \Drupal\recipe\Entity\RecipeInterface $recipe
   *   A Recipe  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(RecipeInterface $recipe) {
    $account = $this->currentUser();
    $langcode = $recipe->language()->getId();
    $langname = $recipe->language()->getName();
    $languages = $recipe->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $recipe_storage = $this->entityManager()->getStorage('recipe');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $recipe->label()]) : $this->t('Revisions for %title', ['%title' => $recipe->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all recipe revisions") || $account->hasPermission('administer recipe entities')));
    $delete_permission = (($account->hasPermission("delete all recipe revisions") || $account->hasPermission('administer recipe entities')));

    $rows = [];

    $vids = $recipe_storage->revisionIds($recipe);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\recipe\RecipeInterface $revision */
      $revision = $recipe_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $recipe->getRevisionId()) {
          $link = $this->l($date, new Url('entity.recipe.revision', ['recipe' => $recipe->id(), 'recipe_revision' => $vid]));
        }
        else {
          $link = $recipe->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
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
              Url::fromRoute('entity.recipe.translation_revert', ['recipe' => $recipe->id(), 'recipe_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.recipe.revision_revert', ['recipe' => $recipe->id(), 'recipe_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.recipe.revision_delete', ['recipe' => $recipe->id(), 'recipe_revision' => $vid]),
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

    $build['recipe_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
