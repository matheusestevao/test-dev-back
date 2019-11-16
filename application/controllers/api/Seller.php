<?php

require APPPATH . 'libraries/REST_Controller.php';

class Seller extends REST_Controller 
{
	const COMMISSION = 0.85;

	public function __construct()
	{
		parent::__construct();
        $this->load->database();
	}

	public function save()
	{
		$input = $this->input->post();

		$validation = $this->validation($input);

		if (is_array($validation)) {
			$this->response([$validation], REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function validation(array $input): array
	{
		$validation = array();

		$userId = isset($input['user_id']) ? $input['user_id'] : NULL;
		$cartId = isset($input['cart_id']) ? $input['cart_id'] : NULL;
		$amount  = isset($input['amount']) ? $input['amount'] : NULL;
		$price   = isset($input['price']) ? $input['price'] : NULL;
		var_dump($price);exit;
		if (is_null($userId) && is_null($cartId) && is_null($amount) && is_null($price)) {
			return [
				'message' => 'All fields are required.',
				'status'  => 'error'
			];
		}

		if (!is_null($userId)) {
			$validationUser = $this->validatingRelationships($userId, 'user');
			
			if(!$validationUser) {
				$validation['user_id'] = [
					'message' => 'Unable to find user. Check the user id.',
					'status' => 'error'
				];
			}
		} else if (is_null($userId)) {
			$validation['user_id'] = [
				'message' => 'The user_id field are required.',
				'status'  => 'error'
			];
		}

		if (!is_null($cartId)) {
			$validationCart = $this->validatingRelationships($cartId, 'cart');
			
			if(!$validationCart) {
				$validation['cart_id'] = [
					'message' => 'Unable to find cart. Check the user id.',
					'status' => 'error'
				];
			}
		} else if (is_null($cartId)) {
			$validation['cart_id'] = [
				'message' => 'The cart_id field are required.',
				'status'  => 'error'
			];
		}

		if (is_null($amount)) {
			$validation['amount'] = [
				'message' => 'The amount field are required.',
				'status'  => 'error'
			];
		}

		if (is_null($price)) {
			$validation['price'] = [
				'message' => 'The price field are required.',
				'status'  => 'error'
			];
		}

		return $validation;
	}

	public function validatingRelationships(?string $id, string $table)
	{
		$data = $this->db->get_where($table, ['id' => $id])->row_array();

		if (is_null($data)) {
			return false;
		}

		return true;
	}
}