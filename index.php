<?php 

require_once 'jdb/jdb.php';

$jdb = new Jdb([
	'dbFile' => __DIR__.'/database.json',
]);


//Create table


// $jdb->createTable([
// 	'tableName' => 'users',
// 	'columns' => [
// 		'id',
// 		'username',
// 		'password',
// 		'email',
// 		'description'
// 	]
// ]);


//Insert

// $jdb->insert('users', ['username' => 'Kostya', 'email' => 'test@email.com']);


//Select

// print_r($jdb->select('users', ['username[==]' => 'Kostya']));


//Delete

// $jdb->delete('users', ['email[==]' => 'test@email.com']);


//Update

// $jdb->update('users', ['email' => 'user@email.com'], ['id[==]' => 1]);

