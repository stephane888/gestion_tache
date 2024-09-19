<?php

namespace Drupal\gestion_tache\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\gestion_tache\Entity\AppProjectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppProjectController.
 *
 *  Returns responses for App project routes.
 */
class AppProjectController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a App project revision.
   *
   * @param int $app_project_revision
   *   The App project revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($app_project_revision) {
    $app_project = $this->entityTypeManager()->getStorage('app_project')
      ->loadRevision($app_project_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('app_project');

    return $view_builder->view($app_project);
  }

  /**
   * Page title callback for a App project revision.
   *
   * @param int $app_project_revision
   *   The App project revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($app_project_revision) {
    $app_project = $this->entityTypeManager()->getStorage('app_project')
      ->loadRevision($app_project_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $app_project->label(),
      '%date' => $this->dateFormatter->format($app_project->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a App project.
   *
   * @param \Drupal\gestion_tache\Entity\AppProjectInterface $app_project
   *   A App project object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AppProjectInterface $app_project) {
    $account = $this->currentUser();
    $app_project_storage = $this->entityTypeManager()->getStorage('app_project');

    $langcode = $app_project->language()->getId();
    $langname = $app_project->language()->getName();
    $languages = $app_project->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $app_project->label()]) : $this->t('Revisions for %title', ['%title' => $app_project->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all app project revisions") || $account->hasPermission('administer app project entities')));
    $delete_permission = (($account->hasPermission("delete all app project revisions") || $account->hasPermission('administer app project entities')));

    $rows = [];

    $vids = $app_project_storage->revisionIds($app_project);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\gestion_tache\AppProjectInterface $revision */
      $revision = $app_project_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $app_project->getRevisionId()) {
          $link = $this->l($date, new Url('entity.app_project.revision', [
            'app_project' => $app_project->id(),
            'app_project_revision' => $vid,
          ]));
        }
        else {
          $link = $app_project->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
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
              Url::fromRoute('entity.app_project.translation_revert', [
                'app_project' => $app_project->id(),
                'app_project_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.app_project.revision_revert', [
                'app_project' => $app_project->id(),
                'app_project_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.app_project.revision_delete', [
                'app_project' => $app_project->id(),
                'app_project_revision' => $vid,
              ]),
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

    $build['app_project_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
