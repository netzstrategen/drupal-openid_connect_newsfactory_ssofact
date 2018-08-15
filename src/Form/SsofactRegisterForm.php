<?php

namespace Drupal\ssofact\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\openid_connect\OpenIDConnectClaims;
use Drupal\openid_connect\Plugin\OpenIDConnectClientManager;
use Drupal\ssofact\Plugin\OpenIDConnectClient\Ssofact;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RegisterForm.
 *
 * @package Drupal\ssofact\Form
 */
class SsofactRegisterForm extends FormBase implements ContainerInjectionInterface {

  /**
   * Drupal\openid_connect\Plugin\OpenIDConnectClientManager definition.
   *
   * @var \Drupal\openid_connect\Plugin\OpenIDConnectClientManager
   */
  protected $pluginManager;

  /**
   * The OpenID Connect claims.
   *
   * @var \Drupal\openid_connect\OpenIDConnectClaims
   */
  protected $claims;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatch
   */
  protected $routeMatch;

  /**
   * The constructor.
   *
   * @param \Drupal\openid_connect\Plugin\OpenIDConnectClientManager $plugin_manager
   *   The plugin manager.
   * @param \Drupal\openid_connect\OpenIDConnectClaims $claims
   *   The OpenID Connect claims.
   */
  public function __construct(
      OpenIDConnectClientManager $plugin_manager,
      OpenIDConnectClaims $claims,
      CurrentRouteMatch $route_match
  ) {

    $this->pluginManager = $plugin_manager;
    $this->claims = $claims;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.openid_connect_client.processor'),
      $container->get('openid_connect.claims'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ssofact_register_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $client_config = $this->config('openid_connect.settings.ssofact');
    if (!$client_config->get('enabled')) {
      return $form;
    }

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email address'),
      '#size' => 60,
      '#maxlength' => USERNAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => [
        'autocorrect' => 'none',
        'autocapitalize' => 'none',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
      ],
      '#ajax' => [
        'callback' => 'self::validateEmail',
        'effect' => 'fade',
        'event' => 'keyup',
        'progress' => [
          'message' => 'progessssss.....',
        ],
      ],
    ];

    $form['article_test'] = [
      '#type' => 'hidden',
      '#value' => $this->routeMatch->getRawParameter('node'),
    ];

    $form['privacy'] = [
      '#type' => 'checkbox',
      '#value' => $this->t('I accept terms and conditions'),
      '#return_value' => '1',
    ];

    // Hidden field with value "1" to trigger special registration form behavior for 1-article-test.
    $form['_qf__registerForm'] = [
      '#type' => 'hidden',
      '#value' => '1',
    ];

    $form['#action'] = 'https://' . $client_config->get('settings.server_domain') . '/registrieren.html?' . http_build_query([
      'next' => \Drupal::Request()->getSchemeAndHttpHost() . Url::fromRoute('<current>')->toString(),
    ]);

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sign up'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  public function validateEmail($form, FormStateInterface $form_state) {
   $ajax_response = new AjaxResponse();
   $text = 'a dummy text';
   $ajax_response->addCommand(new HtmlCommand('#edit-email', $text));

error_log('adsfadfadsfa');
self::isEmalRegistred('adsf@example.com');
   return $ajax_response;

  }

  private function isEmailRegistred($email) {
    $client_config = $this->config('openid_connect.settings.ssofact')->get('settings');
    $ssofact_client = $this->pluginManager->createInstance('ssofact', $client_config);
    $rfbe_key = $ssofact_client['rfbe_key'];
    $rfbe_secret = $ssofact_client['rfbe_secret'];
    $server_domain = $clinet_config['server_domain'];
    $api_url = 'https://' . SSOFACT_SERVER_DOMAIN . SsoFact::ENDPOINT_IS_EMAIL_REGISTERED;
    $client = \Drupal::httpClient();
    $request = $client->post($api_url, [
      'body' => [
        'email' => $email
      ],
      'headers' => [
        'Accept' => 'application/json',
        'rfbe-key' => $rfbe_key,
        'rfbe-secret' => $rfbe_secret,
      ],
    ]);
    $response = json_decode($request->getBody());
    //var_dump($response);

  }

}
