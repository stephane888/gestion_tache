<?php

namespace Drupal\gestion_tache\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\gestion_tache\Entity\AppMemosInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppMemosController.
 *
 *  Returns responses for App memos routes.
 */
class AppMemosController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a App memos revision.
   *
   * @param int $app_memos_revision
   *   The App memos revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($app_memos_revision) {
    $app_memos = $this->entityTypeManager()->getStorage('app_memos')
      ->loadRevision($app_memos_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('app_memos');

    return $view_builder->view($app_memos);
  }

  /**
   * Page title callback for a App memos revision.
   *
   * @param int $app_memos_revision
   *   The App memos revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($app_memos_revision) {
    $app_memos = $this->entityTypeManager()->getStorage('app_memos')
      ->loadRevision($app_memos_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $app_memos->label(),
      '%date' => $this->dateFormatter->format($app_memos->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a App memos.
   *
   * @param \Drupal\gestion_tache\Entity\AppMemosInterface $app_memos
   *   A App memos object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AppMemosInterface $app_memos) {
    $account = $this->currentUser();
    $app_memos_storage = $this->entityTypeManager()->getStorage('app_memos');

    $langcode = $app_memos->language()->getId();
    $langname = $app_memos->language()->getName();
    $languages = $app_memos->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $app_memos->label()]) : $this->t('Revisions for %title', ['%title' => $app_memos->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all app memos revisions") || $account->hasPermission('administer app memos entities')));
    $delete_permission = (($account->hasPermission("delete all app memos revisions") || $account->hasPermission('administer app memos entities')));

    $rows = [];

    $vids = $app_memos_storage->revisionIds($app_memos);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\gestion_tache\AppMemosInterface $revision */
      $revision = $app_memos_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $app_memos->getRevisionId()) {
          $link = $this->l($date, new Url('entity.app_memos.revision', [
            'app_memos' => $app_memos->id(),
            'app_memos_revision' => $vid,
          ]));
        }
        else {
          $link = $app_memos->link($date);
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
              Url::fromRoute('entity.app_memos.translation_revert', [
                'app_memos' => $app_memos->id(),
                'app_memos_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.app_memos.revision_revert', [
                'app_memos' => $app_memos->id(),
                'app_memos_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.app_memos.revision_delete', [
                'app_memos' => $app_memos->id(),
                'app_memos_revision' => $vid,
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

    $build['app_memos_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
