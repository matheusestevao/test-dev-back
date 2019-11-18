<?php

require APPPATH . 'libraries/REST_Controller.php';

class Sale extends REST_Controller 
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

			$total = $input['price'] * $input['amount'];
			$commision = $this->commisionCalculator($total);

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

	public function commisionCalculator(?float $total): ?float
	{
		$commision = ($total * self::COMMISSION) / 100;

		return $commision;
	}

	public function viewSales(): void
	{
		$input = $this->input->get();

		$validationSearch = $this->validationSearch($input);

		if($validationSearch['error']) {
			$this->response([$validationSearch], REST_Controller::HTTP_NOT_FOUND);
		} else {
			$user = $input['user_id'];
			$from = $this->proccessDate($input['from'], 'from');
			$to   = isset($input['to']) ? $this->proccessDate($input['to'], 'to') : date('Y-m-d').' 23:59:59';

			$this->db->select("user.name as name_user, user.email as email_user, COUNT(cart.id) as amount_sale, SUM(cart.total) as sale_total");
			$this->db->from("user");
			$this->db->join("cart", "cart.user_id = user.id");
			$this->db->where("user.id", $user);
			$this->db->where("cart.created_at BETWEEN '$from' AND '$to'");

			$query = $this->db->get();
			$data['records'] = $query->result_array();

			$data['records'][0]['commision'] = $this->commisionCalculator($data['records'][0]['sale_total']);
			$data['records'][0]['period'] = 'From: '.$from.' - To: '.$to;

			$this->response([$data['records'][0]], REST_Controller::HTTP_OK);
		}
	}

	public function validationSearch(array $input): array
	{
		$from = isset($input['from']) ? $input['from'] : NULL;
		$to   = isset($input['to']) ? $input['to'] : NULL;
		$user = isset($input['user_id']) ? $input['user_id'] : NULL;

		$validation['error'] = false;

		if(is_null($from) && is_null($user)) {
			$validation['error'] = true;

			$validation[] = [
				'message' => 'The user_id and from fields are required.',
				'status' => 'error'
			];
		} else if(!is_null($from) && is_null($user)) {
			$validation['error'] = true;

			$validation['user_id'] = [
				'message' => 'The user_id field is required.',
				'status' => 'error'
			];
		} else if(is_null($from) && !is_null($user)) {
			$validation['error'] = true;

			$validation['from'] = [
				'message'=> 'The from field is required.',
				'status' => 'error',
			];
		}

		if(!is_null($to)) {
			$validation['to'] = $this->validateDate($to);

			if($validation['to']['error']) {
				$validation['error'] = true;
			}
		}

		if(!is_null($from)) {
			$validation['from'] = $this->validateDate($from);

			if($validation['from']['error']) {
				$validation['error'] = true;
			}
		}

		if(!is_null($from) && 
			!is_null($to) && 
			isset($validation['from']['error']) && 
			$validation['from']['error'] == false && 
			isset($validation['to']['error']) && 
			$validation['to']['error'] == false) {

			$validation['checkDates'] = $this->checkDates($from, $to);

			if($validation['checkDates']['error']) {
				$validation['error'] = true;
			}
		}

		if(!$validation['from']['error']) {
			unset($validation['from']);
		}

		if(!is_null($to) && !$validation['to']['error']) {
			unset($validation['to']);
		}

		return $validation;
	}

	public function validateDate(string $date): array
	{
		$validation['error'] = false;

		$validate = DateTime::createFromFormat('Y-m-d', $date);

		if(!$validate) {
			$validation['error'] = true;
			$validation[] = [
				'message' => "The from field must be a date in the pattern 'Y-m-d'",
				'status' => 'error'
			];

			return $validation;
		}
		
		return $validation;
	}

	public function checkDates(string $from, string $to): array
	{
		$dateFrom = DateTime::createFromFormat('Y-m-d', $from);
		$dateTo = DateTime::createFromFormat('Y-m-d', $to);

		$validation['error'] = false;

		if($dateTo < $dateFrom) {
			$validation['error'] = true;
			$validation[] = [
				'message' => 'TO field date cannot be greater than FROM field date',
				'status' => 'error',
			];
		}

		return $validation;
	}

	public function proccessDate(string $date, string $field): string
	{
		$date = DateTime::createFromFormat('Y-m-d', $date);

		if($field == 'from') {
			$newDate = $date->format('Y-m-d').' 00:00:00';
		} else if($field == 'to') {	
			$newDate = $date->format('Y-m-d').' 23:59:59';
		}

		return $newDate;
	}
}