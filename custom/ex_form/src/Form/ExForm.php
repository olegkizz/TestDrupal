<?php

namespace Drupal\ex_form\Form;

use Drupal\Core\Form\FormBase;																			// Базовый класс Form API
use Drupal\Core\Form\FormStateInterface;

// Класс отвечает за обработку данных
/**
 * Наследуемся от базового класса Form API
 * @see \Drupal\Core\Form\FormBase
 */

class ExForm extends FormBase {

	// метод, который отвечает за саму форму - кнопки, поля
	public function buildForm(array $form, FormStateInterface $form_state) {

		$form['first'] = [
			'#type' => 'textfield',
			'#title' => $this->t('First name'),
//			'#description' => $this->t('Имя не должно содержать цифр'),
			'#required' => TRUE,
		];

    $form['last'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
//      '#description' => $this->t('Имя не должно содержать цифр'),
      '#required' => TRUE,
    ];

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
//      '#description' => $this->t('Имя не должно содержать цифр'),
      '#required' => TRUE,
    ];


    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
//      '#description' => $this->t('Имя не должно содержать цифр'),
      '#required' => TRUE,
    ];


    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('e-mail'),
//      '#description' => $this->t('Имя не должно содержать цифр'),
      '#required' => TRUE,
    ];



    // Add a submit button that handles the submission of the form.
		$form['actions']['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Отправить форму'),
		];

		return $form;
	}

	// метод, который будет возвращать название формы
	public function getFormId() {
		return 'ex_form_exform_form';
	}

	// ф-я валидации
	public function validateForm(array &$form, FormStateInterface $form_state) {

		if(!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)){
      $form_state->setErrorByName('email', $this->t('Неверный формат email'));
    }

	}

	// действия по сабмиту
	public function submitForm(array &$form, FormStateInterface $form_state) {


    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'ex_form';
    $key = 'important_event';
    $to = $form_state->getValue('email');
    $params['message'] = 'Hello! This is a test message from my site!';
    $params['updated_nodes'] = 10;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;

//Try to send letter:
    $result = $mailManager->mail('system', $key, $to, $langcode, array(
      'context' => array(
        'subject' => 'Some subject',
        'message' => 'Some message',
      )), NULL, $send);

    if ($result['result'] !== true) {
      drupal_set_message('not sent');

    }
    else {
      //Email sent successfully.
      \Drupal::logger('ex_form')->notice('Email sent to'. $to);
      drupal_set_message('sent');
    }

		$resultContact = $this->createContact($form_state->getValue('first'),$form_state->getValue('last'),
      $form_state->getValue('email'), '555-1212');

    if($resultContact == 409){
        drupal_set_message("Contact with this email already exist");
      }
    else drupal_set_message('Create Contact');
	}

	public function createContact($first, $last, $email, $phone)
  {
    $apikey = '4903544f-731e-4e94-8f14-72e8d0611000';
    $contacts = array(
      'properties' => array(
        array(
          'property' => 'email',
          'value' => $email
        ),
        array(
          'property' => 'firstname',
          'value' => $first
        ),
        array(
          'property' => 'lastname',
          'value' => $last
        ),
        array(
          'property' => 'phone',
          'value' => $phone
        )
      )
    );

    $post_json = json_encode($contacts);
    $endpoint = 'https://api.hubapi.com/contacts/v1/contact?hapikey=' . $apikey;
    $ch = @curl_init();
    @curl_setopt($ch, CURLOPT_POST, true);
    @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
    @curl_setopt($ch, CURLOPT_URL, $endpoint);
    @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = @curl_exec($ch);
    $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errors = curl_error($ch);
    @curl_close($ch);
  }

}
