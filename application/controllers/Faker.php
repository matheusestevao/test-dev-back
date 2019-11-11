<?php


class Faker extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->user();
		$this->cart();
		$this->cart_content();
	}

	public function user()
	{
		$faker = Faker\Factory::create();

		for ($i = 0; $i<30;$i++ ) {
			$insert = [
				'name' => $faker->name,
				'email' => $faker->email,
			];
			$this->db->insert('user', $insert);
		}
	}

	public function cart()
	{
		$faker = Faker\Factory::create();
		for ($i=0;$i<60;$i++){
			$insert = [
				'user_id' => $faker->numberBetween(1, 30),
			];
			$this->db->insert('cart', $insert);
		}
	}

	public function cart_content()
	{
		$faker = Faker\Factory::create();
		$result = $this->db->from('cart')->get();
		if ($result->num_rows() > 0 ) {
			foreach ($result->result() as $item) {
				for ($i=0;$i<$faker->numberBetween(1, 15);$i++){
					$amount = $faker->numberBetween(1, 5);
					$price = $faker->numberBetween(10, 99);
					$total = $amount*$price;
					$totalCart[] = $total;
					$insert = [
						'cart_id' => $item->id,
						'amount' => $amount,
						'price' => $price,
						'total' => $total
					];
					$this->db->insert('cart_content', $insert);

				}
				$this->db->update('cart', ['total' => array_sum($totalCart)], ['id' => $item->id]);
			}

		}

	}
}
