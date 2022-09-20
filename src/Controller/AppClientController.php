<?php

namespace Drupal\gestion_tache\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\gestion_tache\Entity\AppClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppClientController.
 *
 *  Returns responses for App client routes.
 */
class AppClientController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a App client revision.
   *
   * @param int $app_client_revision
   *   The App client revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($app_client_revision) {
    $app_client = $this->entityTypeManager()->getStorage('app_client')
      ->loadRevision($app_client_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('app_client');

    return $view_builder->view($app_client);
  }

  /**
   * Page title callback for a App client revision.
   *
   * @param int $app_client_revision
   *   The App client revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($app_client_revision) {
    $app_client = $this->entityTypeManager()->getStorage('app_client')
      ->loadRevision($app_client_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $app_client->label(),
      '%date' => $this->dateFormatter->format($app_client->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a App client.
   *
   * @param \Drupal\gestion_tache\Entity\AppClientInterface $app_client
   *   A App client object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AppClientInterface $app_client) {
    $account = $this->currentUser();
    $app_client_storage = $this->entityTypeManager()->getStorage('app_client');

    $langcode = $app_client->language()->getId();
    $langname = $app_client->language()->getName();
    $languages = $app_client->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $app_client->label()]) : $this->t('Revisions for %title', ['%title' => $app_client->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all app client revisions") || $account->hasPermission('administer app client entities')));
    $delete_permission = (($account->hasPermission("delete all app client revisions") || $account->hasPermission('administer app client entities')));

    $rows = [];

    $vids = $app_client_storage->revisionIds($app_client);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\gestion_tache\AppClientInterface $revision */
      $revision = $app_client_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $app_client->getRevisionId()) {
          $link = $this->l($date, new Url('entity.app_client.revision', [
            'app_client' => $app_client->id(),
            'app_client_revision' => $vid,
          ]));
        }
        else {
          $link = $app_client->link($date);
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
              Url::fromRoute('entity.app_client.translation_revert', [
                'app_client' => $app_client->id(),
                'app_client_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.app_client.revision_revert', [
                'app_client' => $app_client->id(),
                'app_client_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.app_client.revision_delete', [
                'app_client' => $app_client->id(),
                'app_client_revision' => $vid,
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

    $build['app_client_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
