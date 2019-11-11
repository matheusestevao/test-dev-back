<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_user extends CI_Migration {

	public function up()
	{
		$fields = [
			'id'	=> ['type' => 'INT', 'auto_increment' => TRUE],
			'name'  => ['type' => 'VARCHAR', 'constraint' => '100', 'default' => null],
			'email' => ['type' => 'VARCHAR', 'constraint' => '100', 'default'   => 0],
		];
		$this->dbforge->add_field($fields);

		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_field("`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
		$this->dbforge->add_field("`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

		$attributes = array('ENGINE' => 'InnoDB');
		$this->dbforge->create_table('user', TRUE, $attributes);
	}

	public function down()
	{
		$this->dbforge->drop_table('user');
	}
}
