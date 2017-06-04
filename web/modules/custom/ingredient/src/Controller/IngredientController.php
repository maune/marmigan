<?php

namespace Drupal\ingredient\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\ingredient\Entity\IngredientInterface;

/**
 * Class IngredientController.
 *
 *  Returns responses for Ingredient routes.
 *
 * @package Drupal\ingredient\Controller
 */
class IngredientController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Ingredient  revision.
   *
   * @param int $ingredient_revision
   *   The Ingredient  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($ingredient_revision) {
    $ingredient = $this->entityManager()->getStorage('ingredient')->loadRevision($ingredient_revision);
    $view_builder = $this->entityManager()->getViewBuilder('ingredient');

    return $view_builder->view($ingredient);
  }

  /**
   * Page title callback for a Ingredient  revision.
   *
   * @param int $ingredient_revision
   *   The Ingredient  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($ingredient_revision) {
    $ingredient = $this->entityManager()->getStorage('ingredient')->loadRevision($ingredient_revision);
    return $this->t('Revision of %title from %date', ['%title' => $ingredient->label(), '%date' => format_date($ingredient->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Ingredient .
   *
   * @param \Drupal\ingredient\Entity\IngredientInterface $ingredient
   *   A Ingredient  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(IngredientInterface $ingredient) {
    $account = $this->currentUser();
    $langcode = $ingredient->language()->getId();
    $langname = $ingredient->language()->getName();
    $languages = $ingredient->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $ingredient_storage = $this->entityManager()->getStorage('ingredient');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $ingredient->label()]) : $this->t('Revisions for %title', ['%title' => $ingredient->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all ingredient revisions") || $account->hasPermission('administer ingredient entities')));
    $delete_permission = (($account->hasPermission("delete all ingredient revisions") || $account->hasPermission('administer ingredient entities')));

    $rows = [];

    $vids = $ingredient_storage->revisionIds($ingredient);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\ingredient\IngredientInterface $revision */
      $revision = $ingredient_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $ingredient->getRevisionId()) {
          $link = $this->l($date, new Url('entity.ingredient.revision', ['ingredient' => $ingredient->id(), 'ingredient_revision' => $vid]));
        }
        else {
          $link = $ingredient->link($date);
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
              Url::fromRoute('entity.ingredient.translation_revert', ['ingredient' => $ingredient->id(), 'ingredient_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.ingredient.revision_revert', ['ingredient' => $ingredient->id(), 'ingredient_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.ingredient.revision_delete', ['ingredient' => $ingredient->id(), 'ingredient_revision' => $vid]),
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

    $build['ingredient_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
