<?php
namespace Blocks;

/**
 * Setup Controller
 */
class SetupController extends BaseController
{
	/**
	 * Init
	 */
	public function init()
	{
		// Return a 404 if Blocks is already setup
		if (!Blocks::app()->getConfig('devMode') && Blocks::app()->isSetup)
			throw new HttpException(404);
	}

	/**
	 * License Key form
	 */
	public function actionIndex()
	{
		// Is this a post request?
		if (Blocks::app()->request->requestType == 'POST')
		{
			$postLicenseKeyId = Blocks::app()->request->getPost('licensekey_id');

			if ($postLicenseKeyId)
				$licenseKey = LicenseKey::model()->findByPk($postLicenseKeyId);

			if (empty($licenseKey))
				$licenseKey = new LicenseKey;

			$licenseKey->key = Blocks::app()->request->getPost('licensekey');

			if ($licenseKey->save())
				$this->redirect('setup/site');
		}
		else
			// Does a license key already exist?
			$licenseKey = LicenseKey::model()->find();

		$this->loadTemplate('_special/setup', array(
			'licenseKey' => $licenseKey
		));
	}

	/**
	 * Site form
	 */
	public function actionSite()
	{
		// Is this a post request?
		if (Blocks::app()->request->requestType == 'POST')
		{
			$postSiteId = Blocks::app()->request->getPost('site_id');

			if ($postSiteId)
				$site = Site::model()->findByPk($postSiteId);

			if (empty($site))
				$site = new Site;

			$site->name = Blocks::app()->request->getPost('name');
			$site->handle = Blocks::app()->request->getPost('handle');
			$site->url = Blocks::app()->request->getPost('url');
			$site->enabled = true;

			if ($site->save())
			{
				if (Blocks::app()->request->getQuery('goback') === null)
					$this->redirect('setup/account');
				else
					$this->redirect('setup');
			}
		}
		else
			// Does a site already exist?
			$site = Site::model()->find('enabled=:enabled', array(':enabled'=>true));

		$this->loadTemplate('_special/setup/site', array(
			'site' => $site
		));
	}

	/**
	 * Account form
	 */
	public function actionAccount()
	{
		// Is this a post request?
		if (Blocks::app()->request->requestType == 'POST')
		{
			$postUserId = Blocks::app()->request->getPost('user_id');

			if ($postUserId)
				$user = User::model()->findByPk($postUserId);

			if (empty($user))
				$user = new User;

			$user->username = Blocks::app()->request->getPost('username');
			$user->email = Blocks::app()->request->getPost('email');
			$user->first_name = Blocks::app()->request->getPost('first_name');
			$user->last_name = Blocks::app()->request->getPost('last_name');
			$user->password_reset_required = false;
			$user->admin = true;

			$newUser = $user->isNewRecord;
			$password = Blocks::app()->request->getPost('password');

			if ($newUser || $password)
			{
				$hashAndType = Blocks::app()->security->hashPassword($password);
				$user->password = $hashAndType['hash'];
				$user->enc_type = $hashAndType['encType'];
			}

			if ($user->save())
			{
				if ($newUser)
					// Give them the default dashboard widgets
					Blocks::app()->dashboard->assignDefaultUserWidgets($user->id);

				if (Blocks::app()->request->getQuery('goback') === null)
					$this->redirect('dashboard');
				else
					$this->redirect('setup/site');
			}
		}
		else
			// Does an admin user already exist?
			$user = User::model()->find('admin=:admin', array(':admin'=>true));

		$this->loadTemplate('_special/setup/account', array(
			'user' => $user
		));
	}
}
