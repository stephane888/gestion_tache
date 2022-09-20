<?php

namespace Drupal\gestion_tache\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\gestion_tache\Entity\AppPrimeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppPrimeController.
 *
 *  Returns responses for App prime routes.
 */
class AppPrimeController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a App prime revision.
   *
   * @param int $app_prime_revision
   *   The App prime revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($app_prime_revision) {
    $app_prime = $this->entityTypeManager()->getStorage('app_prime')
      ->loadRevision($app_prime_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('app_prime');

    return $view_builder->view($app_prime);
  }

  /**
   * Page title callback for a App prime revision.
   *
   * @param int $app_prime_revision
   *   The App prime revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($app_prime_revision) {
    $app_prime = $this->entityTypeManager()->getStorage('app_prime')
      ->loadRevision($app_prime_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $app_prime->label(),
      '%date' => $this->dateFormatter->format($app_prime->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a App prime.
   *
   * @param \Drupal\gestion_tache\Entity\AppPrimeInterface $app_prime
   *   A App prime object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AppPrimeInterface $app_prime) {
    $account = $this->currentUser();
    $app_prime_storage = $this->entityTypeManager()->getStorage('app_prime');

    $langcode = $app_prime->language()->getId();
    $langname = $app_prime->language()->getName();
    $languages = $app_prime->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $app_prime->label()]) : $this->t('Revisions for %title', ['%title' => $app_prime->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all app prime revisions") || $account->hasPermission('administer app prime entities')));
    $delete_permission = (($account->hasPermission("delete all app prime revisions") || $account->hasPermission('administer app prime entities')));

    $rows = [];

    $vids = $app_prime_storage->revisionIds($app_prime);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\gestion_tache\AppPrimeInterface $revision */
      $revision = $app_prime_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $app_prime->getRevisionId()) {
          $link = $this->l($date, new Url('entity.app_prime.revision', [
            'app_prime' => $app_prime->id(),
            'app_prime_revision' => $vid,
          ]));
        }
        else {
          $link = $app_prime->link($date);
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
              Url::fromRoute('entity.app_prime.translation_revert', [
                'app_prime' => $app_prime->id(),
                'app_prime_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.app_prime.revision_revert', [
                'app_prime' => $app_prime->id(),
                'app_prime_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.app_prime.revision_delete', [
                'app_prime' => $app_prime->id(),
                'app_prime_revision' => $vid,
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

    $build['app_prime_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
