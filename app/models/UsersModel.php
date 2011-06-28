<?php


use Nette\Security\AuthenticationException;


class UsersModel extends Nette\Object implements Nette\Security\IAuthenticator
{

	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		// :)
		if ($username !== :) || $password !== :)) {
			throw new AuthenticationException("Nesprávné přihlašovací údaje.", self::INVALID_CREDENTIAL);
		}

		return new Nette\Security\Identity($username, NULL, NULL);
	}


}
