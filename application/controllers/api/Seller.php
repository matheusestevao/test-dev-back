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

	public function save(): void
	{
		$input = $this->input->post();

		$validation = $this->validation($input);

		if($validation['error']) {
			$this->response([$validation], REST_Controller::HTTP_NOT_FOUND);
		}

		$cartContent = $this->proccessData($input);

		if($cartContent) {
			$user = $this->getUser($input['user_id']);
			$commision = $this->commisionCalculator($input);

			$response = [
				'name' => $user['name'],
				'email' => $user['email'],
				'commision' => $commision
			];

			$this->response([$response], REST_Controller::HTTP_OK);
		} else {
			$this->response(['Unable to save sale.'], REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function validation(array $input): array
	{
		$validation = array();
		$validation['error'] = false;

		$userId = isset($input['user_id']) ? $input['user_id'] : NULL;
		$amount = isset($input['amount']) ? $input['amount'] : NULL;
		$price  = isset($input['price']) ? $input['price'] : NULL;

		if(is_null($userId) && is_null($amount) && is_null($price)) {
			$validation['error'] = true;

			$validation[] = [
				'message' => 'All fields are required.',
				'status'  => 'error'
			];

			return $validation;
		}

		if(!is_null($userId)) {
			$validationUser = $this->validatingRelationships($userId, 'user');
			
			if(!$validationUser) {
				$validation['error'] = true;
				$validation['user_id'] = [
					'message' => 'Unable to find user. Check the user id.',
					'status' => 'error'
				];
			}
		} else if(is_null($userId)) {
			$validation['error'] = true;
			$validation['user_id'] = [
				'message' => 'The user_id field are required.',
				'status'  => 'error'
			];
		}

		if(is_null($amount)) {
			$validation['error'] = true;
			$validation['amount'] = [
				'message' => 'The amount field are required.',
				'status'  => 'error'
			];
		}

		if(is_null($price)) {
			$validation['error'] = true;
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

		if(is_null($data)) {
			return false;
		}

		return true;
	}

	public function proccessData(array $data): int
	{
		$data['cart_id'] = $this->saveCart($data);

		if($data['cart_id']) {
			$cartContentId = $this->saveCartContent($data);	
		
			if($cartContentId) {
				return $cartContentId;
			} else {
				$this->db->delete('cart', array('id' => $data['cart_id']));

				return 0;
			}
		}

		return 0;
	}

	public function saveCart(array $data): int
	{
		$cart['user_id'] = $data['user_id'];
		$cart['total'] = $data['amount'] * $data['price'];

		$save = $this->db->insert('cart', $cart);

		if($save) {
			$cartId = $this->db->insert_id();

			return $cartId;
		}

		return 0;
	}

	public function saveCartContent(array $data): int
	{
		$cartContent['cart_id'] = $data['cart_id'];
		$cartContent['amount'] = $data['amount'];
		$cartContent['price'] = $data['price'];
		$cartContent['total'] = $data['amount'] * $data['price'];

		$save = $this->db->insert('cart_content', $cartContent);

		if($save) {
			$cartContentId = $this->db->insert_id();

			return $cartContentId;
		}

		return 0;
	}

	public function getUser(int $id): array
	{
		$user = $this->db->get_where('user', ['id' => $id])->row_array();

		return $user;
	}

	public function commisionCalculator(array $data): ?float
	{
		$total = $data['price'] * $data['amount'];

		$commision = ($total * self::COMMISSION) / 100;

		return $commision;
	}
}