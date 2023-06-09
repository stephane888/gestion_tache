<?php

namespace Drupal\gestion_tache\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\gestion_tache\Entity\SubTacheInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SubTacheController.
 *
 *  Returns responses for Sub tache routes.
 */
class SubTacheController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a Sub tache revision.
   *
   * @param int $sub_tache_revision
   *   The Sub tache revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($sub_tache_revision) {
    $sub_tache = $this->entityTypeManager()->getStorage('sub_tache')
      ->loadRevision($sub_tache_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('sub_tache');

    return $view_builder->view($sub_tache);
  }

  /**
   * Page title callback for a Sub tache revision.
   *
   * @param int $sub_tache_revision
   *   The Sub tache revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($sub_tache_revision) {
    $sub_tache = $this->entityTypeManager()->getStorage('sub_tache')
      ->loadRevision($sub_tache_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $sub_tache->label(),
      '%date' => $this->dateFormatter->format($sub_tache->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Sub tache.
   *
   * @param \Drupal\gestion_tache\Entity\SubTacheInterface $sub_tache
   *   A Sub tache object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(SubTacheInterface $sub_tache) {
    $account = $this->currentUser();
    $sub_tache_storage = $this->entityTypeManager()->getStorage('sub_tache');

    $langcode = $sub_tache->language()->getId();
    $langname = $sub_tache->language()->getName();
    $languages = $sub_tache->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $sub_tache->label()]) : $this->t('Revisions for %title', ['%title' => $sub_tache->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all sub tache revisions") || $account->hasPermission('administer sub tache entities')));
    $delete_permission = (($account->hasPermission("delete all sub tache revisions") || $account->hasPermission('administer sub tache entities')));

    $rows = [];

    $vids = $sub_tache_storage->revisionIds($sub_tache);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\gestion_tache\Entity\SubTacheInterface $revision */
      $revision = $sub_tache_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $sub_tache->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.sub_tache.revision', [
            'sub_tache' => $sub_tache->id(),
            'sub_tache_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $sub_tache->toLink($date)->toString();
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
              Url::fromRoute('entity.sub_tache.translation_revert', [
                'sub_tache' => $sub_tache->id(),
                'sub_tache_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.sub_tache.revision_revert', [
                'sub_tache' => $sub_tache->id(),
                'sub_tache_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.sub_tache.revision_delete', [
                'sub_tache' => $sub_tache->id(),
                'sub_tache_revision' => $vid,
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

    $build['sub_tache_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
