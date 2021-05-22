<?php
namespace Drupal\custom_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CustomForm extends FormBase{
	public function getFormId(){
		return 'custom_form_form';
	}
	public function buildForm(array $form,FormStateInterface $form_state){
		$form['name']=[
		'#type' => 'textfield',
		'#title' => $this->t(string:'name'),
		];
		$form['lastname']=[
		'#type' => 'textfield',
		'#title' => $this->t(string:'lastname'),
		];
		$form['submit']=[
		'#type' => 'submit',
		'#value' => $this->t(string:'Send'),
		];
		return $form;
	}
	public function submitForm(array &$form,FormStateInterface $form_state){
	$this->messenger()->addStatus(this->t(string:'Good Mornign @fullname',[
	'@fullname'=>$form_state->getValue(key:'name')
	.$form_state->getValue(key:'lastname')
	]));
	}
}