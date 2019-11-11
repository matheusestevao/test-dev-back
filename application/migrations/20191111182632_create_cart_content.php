<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_cart_content extends CI_Migration {

	public function up()
	{
		$fields = [
			'id'		=> ['type' => 'INT', 'auto_increment' => TRUE],
			'cart_id'  	=> ['type' => 'INT', 'constraint' => 10, 'default' => null],
			'amount'  	=> ['type' => 'INT', 'constraint' => '2', 'default' => null],
			'price' 	=> ['type' => 'FLOAT', 'default'   => 0],
			'total' 	=> ['type' => 'FLOAT', 'default'   => 0],

		];
		$this->dbforge->add_field($fields);

		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_field("`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
		$this->dbforge->add_field("`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

		$attributes = array('ENGINE' => 'InnoDB');
		$this->dbforge->create_table('cart_content', TRUE, $attributes);

		$this->dbforge->add_column('cart_content',[
			"CONSTRAINT fk_cart_cart_content FOREIGN KEY(cart_id) REFERENCES {$this->db->dbprefix}cart(id)",
		]);
	}

	public function down()
	{
		$this->dbforge->drop_table('cart_content');
	}
}
