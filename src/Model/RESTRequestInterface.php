<?php

namespace App\Model;

	if(!interface_exists('RESTRequestInterface')){
		interface RESTRequestInterface{

			public function getTransactionType();
		}
	}