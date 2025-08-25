<?php

namespace App\Presentation\Http\Controllers;

//!!!!!!!!!!!!!!!--DEBUG MODE--!!!!!!!!!!!!!!!!!!!//
use App\Domain\User\User;
use App\Domain\User\Entities\Profile;
use App\Domain\User\ValueObjects\ContactInfo;
use App\Domain\User\ValueObjects\Credentials;
use App\Domain\User\ValueObjects\Address;
use Debug\DebugHelper;
//!!!!!!!!!!!!!!!--DEBUG MODE--!!!!!!!!!!!!!!!!!!!//
class HomeController
{
	public function index()
	{
		//!!!!!!!!!!!!!!!--DEBUG MODE--!!!!!!!!!!!!!!!!!!!//
		$credentials = new Credentials('menen@gmail.com', 'my password');
		$contactInfo = new ContactInfo('menen@example.com', '09559653516');
		$profile = new Profile('Marynelle', 'Tesoro', 'Aban', 'https://profile.pic.com');
		$address = new Address('1','brgy 1', 'qc', 'ncr', '1505', '');
		$user1 = new User('Admin', $profile, $contactInfo, $credentials, $address);
		$user2 = new User('Admin', $profile, $contactInfo, new Credentials('aban@gmail.com', 'badingkasba'), $address);

		DebugHelper::debug($user1);
		DebugHelper::debug($user2);
		//!!!!!!!!!!!!!!!--DEBUG MODE--!!!!!!!!!!!!!!!!!!!//
		return "Welcome to GearFalcon API";
	}
}
