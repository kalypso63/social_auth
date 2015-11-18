.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Users manual
============

- Install the extension using the Extension Manager
- Configure via Extension Manager and add key + appId for each social provider & set options for fe_users creation
- Add the static TS (typoscript) to your typoscript template

Extension manager: **Social auth**

.. figure:: ../Images/UserManual/ExtensionManagerView.png
	:width: 500px
	:alt: Backend view

	Configure social providers

**Frontend plugin integration**

Two ways exist to integrate social auth on FE

- Add Social auth plugin on your page. It create a link for each enabled providers
- Create links on your fluid template like this :
.. code-block:: xml

	<f:link.page pageType="1316773681" additionalParams="{tx_socialauth_pi1:{provider:'facebook'}}" noCacheHash="TRUE">
		Facebook
	</f:link.page>
