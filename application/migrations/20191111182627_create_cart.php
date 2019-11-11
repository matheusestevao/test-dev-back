<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_cart extends CI_Migration {

	public function up()
	{
		$fields = [
			'id'		=> ['type' => 'INT', 'auto_increment' => TRUE],
			'user_id'  	=> ['type' => 'INT', 'constraint' => 10, 'default' => null],
			'total' 	=> ['type' => 'FLOAT', 'default'   => 0],
		];
		$this->dbforge->add_field($fields);

		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_field("`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
		$this->dbforge->add_field("`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

		$attributes = array('ENGINE' => 'InnoDB');
		$this->dbforge->create_table('cart', TRUE, $attributes);

		$this->dbforge->add_column('cart',[
			"CONSTRAINT fk_cart_user FOREIGN KEY(user_id) REFERENCES {$this->db->dbprefix}user(id)",
		]);
	}

	public function down()
	{
		$this->dbforge->drop_table('cart');
	}
}
